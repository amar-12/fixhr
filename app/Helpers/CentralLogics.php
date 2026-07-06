<?php

namespace App\Helpers;

use App\Models\Employee;
use App\Models\MasterTable;
use App\Models\PolicyLeave;
use App\Models\LeaveRequest;
use App\Models\MailTemplate;
use Illuminate\Http\Request;
use App\Models\AttendanceLog;
use Illuminate\Support\Carbon;
use App\Models\PolicyLeaveType;
use App\Models\AttendanceRecord;
use App\Models\PolicyHolidayList;
use App\Models\AttendanceException;
use App\Models\CompOff;
use App\Models\CompOffBalance;
use App\Models\CompOffPolicy;
use Illuminate\Support\Facades\Log;
use App\Models\PolicyTadaTravelType;
use App\Models\RuleCriterion;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Session;
use ChandraHemant\HtkcUtils\CommonUtils;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Illuminate\Support\Facades\Crypt;
use App\Models\PolicyShiftTiming;
use App\Models\PayrollPeriod;
use App\Models\EmployeeApprovalStatus;
use App\Models\EmployeeManagerLog;
use App\Helpers\ApprovalHelper;
use App\Models\OvertimePolicy;
use App\Models\OtApprovalStatus;
use DateTime;
use App\Models\EmailConfiguration;

class CentralLogics
{
    /**
     * Laeravel Custom Helpers
     *
     * @package     Laravel Helpers
     * @subpackage  Custom Helpers
     * @category    Helpers
     * @author      Hemant Chandra
     */

    public static function timezone_configure()
    {
        return Carbon::now()->timezone('Asia/Kolkata')->toDateTimeString();
    }


    public static function send_sms($phone, $message)
    {
        // Your SMS API integration here
    }

    public static function getAttendanceRecord($employeeId, $date)
    {
        // Initialize default values
        $user =   Auth::user();
        $status = 0;
        $leaveName = null;
        $holidayName = null;

        $leaveRequest = LeaveRequest::where('lvr_emp_id', $employeeId)->whereDate('lvr_start_date', '<=', $date)->whereDate('lvr_end_date', '>=', $date)->where('lvr_status', 'APPROVED')->first();

        if ($leaveRequest) {
            switch (true) {
                case ($leaveRequest->lvr_status === 'approved' && $leaveRequest->lvr_leave_day_type_id == 201):
                    $leaveName = PolicyLeave::where('m_id',  $user->emp_b_id)->where('pl_id', $leaveRequest->lvr_pl_id)->pluck('pl_name');
                    $status = 201; // Full-day leave
                    break;

                case ($leaveRequest->lvr_status === 'approved' && $leaveRequest->lvr_leave_day_type_id == 202):
                    $leaveName = PolicyLeave::where('m_id',  $user->emp_b_id)->where('pl_id', $leaveRequest->lvr_pl_id)->pluck('pl_name');
                    $status = 202; // Half-day leave
                    break;

                case ($leaveRequest->lvr_status === 'pending' && $leaveRequest->lvr_leave_day_type_id == 202 || $leaveRequest->lvr_leave_day_type_id == 201):
                    $status = 203; // Leave not approved (absent)
                    break;
            }
        }

        $attendanceException = AttendanceException::whereHas('fh_attendance_policy', function ($query) {
            $query->whereColumn('ap_id', 'ae_ap_id');
        })
            ->where('ae_emp_id', $employeeId)
            ->whereDate('ae_date', $date)
            ->first();

        if ($attendanceException) {
            if ($attendanceException->ae_status === 'approved') {
                $status = 251; // Mispunch approved (present)
            } elseif (is_null($attendanceException->ae_status)) {
                $status = 228; // Mispunch not approved
            }
        }

        $holiday = PolicyHolidayList::whereHas('fh_attendance_policy', function ($query) {
            $query->whereColumn('ap_id', 'phl_ap_id');
        })
            ->where('phl_b_id', $user->emp_b_id)
            ->where('phl_start_date', '<=', $date)
            ->where('phl_end_date', '>=', $date)
            ->first();

        if ($holiday) {
            if ($holiday->phl_type_id == 205) {
                $status = 205; // Public holiday
            } elseif ($holiday->phl_type_id == 206) {
                $status = 206; // Company-specific holiday
            }
            $holidayName = $holiday->phl_name;
        }

        return [
            'status' => $status,
            'leave_name' => $leaveName,
            'holiday_name' => $holidayName,
        ];
    }

    public static function dynamicDelete($modelClass, $id)
    {
        try {
            // Ensure the model class exists
            if (!class_exists($modelClass)) {
                return ['error' => 'Invalid model specified.'];
            }

            $record = $modelClass::find($id);

            if (!$record) {
                return ['error' => 'Record not found.'];
            }

            $record->delete();
            if (class_basename($modelClass) == 'PolicyShiftTiming') {
                $modelClass = 'Policy Shift';
                return ['success' => $modelClass . ' deleted successfully.'];
            }
            return ['success' => class_basename($modelClass) . ' deleted successfully.'];
        } catch (QueryException $e) {
            if ($e->getCode() == 23000) { // Integrity constraint violation
                // Extract the referenced table name from the error message
                $errorMessage = $e->getMessage();
                preg_match("/`(\w+?)`\.\`(\w+?)`/", $errorMessage, $matches);
                $referencedTable = $matches[2] ?? 'an unknown table';
                if ($referencedTable == 'fh_employees') {
                    $referencedTable  = 'Employee';
                }
                // return ['error' => "Can't delete this record because it is referenced by the '{$referencedTable}' table."];
                return ['error' => "Unable to delete. This record is linked to the '{$referencedTable}' records and must be removed first"];
            }

            // Log unexpected errors for debugging
            Log::error('Delete Error: ' . $e->getMessage());
            return ['error' => 'An unexpected error occurred.'];
        }
    }

    public static function getMonthlyAttendanceSummary($employee, $monthFilter, $weekOfDates)
    {
        $user = Auth::user();
        [$year, $month] = explode('-', $monthFilter);

        $presentCount = 0;
        $weekOffPresentCount = 0;
        $leaveCount = 0;
        $approvedLeaveCount = 0;
        $holidayCount = 0;

        $absentCount = 0;
        $halfDayCount = 0;
        $missedPunchCount = 0;
        $approvedMissedPunchCount = 0;
        $overtimeCount = 0;
        $lateCount = 0;
        $earlyExitCount = 0;

        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();


        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $dateRange = $startDate->toPeriod($endDate);

        $holiday_record_exits = PolicyHolidayList::where('phl_b_id', $user->emp_b_id)->where('phl_day_type_id', 201)->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('phl_start_date', [$startDate, $endDate])
              ->orWhereBetween('phl_end_date', [$startDate, $endDate]);
        })->get();
        $holidaysByDate = collect();
        foreach ($holiday_record_exits as $holiday) {
            $start = Carbon::parse($holiday->phl_start_date);
            $end = Carbon::parse($holiday->phl_end_date);

            while ($start->lte($end)) {
                $holidaysByDate->put($start->toDateString(), $holiday);
                $start->addDay();
            }
        }


            $monthDetails =  ['date' => $startDate->format('Y-m-d'), 'user_friendly_date' => $startDate->format('d-M-Y'), 'day' => $startDate->format('l')];

            $attendanceData = self::newGetMonthlyAttendanceDetails($employee, $month, $year, $holidaysByDate, $weekOfDates);

            // $presentCount = $presentCount + $attendanceData['presentCount'];
            // $leaveCount = $leaveCount + $attendanceData['leaveCount'];

            // $absentCount = $absentCount + $attendanceData['absentCount'];
            // $halfDayCount = $halfDayCount + $attendanceData['halfDayCount'];
            // $missedPunchCount = $missedPunchCount + $attendanceData['missedPunchCount'];
            // $overtimeCount = $overtimeCount + $attendanceData['overtimeCount'];
            // $lateCount = $lateCount + $attendanceData['lateCount'];
            // $earlyExitCount = $earlyExitCount + $attendanceData['earlyExitCount'];
            // $holidayCount = $holidayCount + $attendanceData['holidayCount'];
            // $approvedLeaveCount = $approvedLeaveCount + $attendanceData['approvedLeaveCount'];
            // $approvedMissedPunchCount = $approvedMissedPunchCount + $attendanceData['approvedMissedPunchCount'];



            $presentCount = collect($attendanceData)->sum('presentCount');
            $weekOffPresentCount = collect($attendanceData)->sum('weekOffPresentCount');
            $leaveCount = collect($attendanceData)->sum('leaveCount');
            $holidayCount = collect($attendanceData)->sum('holidayCount');
            $weekOffCount = collect($attendanceData)->sum('weekOffCount');
            $absentCount = collect($attendanceData)->sum('absentCount');
            $halfDayCount = collect($attendanceData)->sum('halfDayCount');
            $missedPunchCount = collect($attendanceData)->sum('missedPunchCount');
            $overtimeCount = collect($attendanceData)->sum('overtimeCount');
            $lateCount = collect($attendanceData)->sum('lateCount');
            $earlyExitCount = collect($attendanceData)->sum('earlyExitCount');
            $approvedLeaveCount = collect($attendanceData)->sum('approvedLeaveCount');
            $approvedMissedPunchCount = collect($attendanceData)->sum('approvedMissedPunchCount');






        return [
            'presentCount' => $presentCount,
            'weekOffPresentCount' => $weekOffPresentCount,
            'leaveCount' => $leaveCount,
            'holidayCount' => $holidayCount,
            'weekOffCount' => count($weekOfDates),
            'absentCount' => $absentCount,
            'halfDayCount' => $halfDayCount,
            'missedPunchCount' => $missedPunchCount,
            'overtimeCount' => $overtimeCount,
            'lateCount' => $lateCount,
            'earlyExitCount' => $earlyExitCount,
            'approvedLeaveCount' => $approvedLeaveCount,
            'approvedMissedPunchCount' => $approvedMissedPunchCount,
        ];
    }

    public static function getMonthlyAttendanceDetails($employee, $date, $weekOfDates)
    {

        $absentStatus = MasterTable::where('m_id', 203)->first();

        $rowData = [
            'status' => '-',
            'status_id' => '-',
            'status_code' => '-',
            'statusColor' => '#BDBDBD',
            'checkInTime' => null,
            'checkOutTime' => null,
            'updatedBy' => null,
            'workingHour' => null,
            'OT' => null,
            'earlyExit' => null,
            'late' => null,
            'attendance_remark' => '--',
            'checkInLocation' => '--',
            'checkOutLocation' => '--',
            'checkInPhoto' => [],
            'checkOutPhoto' => [],
            'atd_segments' => [],
            'presentCount' => 0,
            'leaveCount' => 0,
            'holidayCount' => 0,
            'weekOffCount' => 0,
            'absentCount' => 0,
            'halfDayCount' => 0,
            'missedPunchCount' => 0,
            'overtimeCount' => 0,
            'lateCount' => 0,
            'earlyExitCount' => 0,
            'approvedLeaveCount' => 0,
            'approvedMissedPunchCount' => 0,
            'isApproved' => null,
            'previousCheckInTime' => null,
            'previousCheckOutTime' => null,
            'previousLate' => null,
            'previousExit' => null,
            'UPL' => 0,
        ];

        if ($employee->emp_date_of_joining <= Carbon::parse($date)->format('Y-m-d')) {

            $is_found = null;
            $attendance_record_exit = $employee->attendance_record()->whereDate('atd_date', $date)->first();
            $attendance_log = AttendanceLog::where('al_emp_id', $employee->emp_id)->whereDate('al_date', $date)->orderBy('al_id', 'desc')->first();

            if ($attendance_record_exit && $attendance_record_exit->fh_attendance_status) {

                if ($attendance_record_exit->atd_attendance_status == 252) { //Half Day
                    $rowData['halfDayCount'] = 1;
                    $rowData['presentCount']  = 0.5;
                    $rowData['status_id'] = $attendance_record_exit->fh_attendance_status->m_id;
                    $rowData['status_code'] = $attendance_record_exit->fh_attendance_status->m_type;
                    $is_found = 1;
                } else {
                    $missedPunch =  AttendanceException::where('ae_b_id', $employee->emp_b_id)->where('ae_emp_id', $employee->emp_id)->where('ae_date', '=', $date)->first();
                    if (!$missedPunch) {
                        $rowData['status_id'] = $attendance_record_exit->fh_attendance_status->m_id;
                        $rowData['status_code'] = $attendance_record_exit->fh_attendance_status->m_type;
                        if ($attendance_record_exit->atd_attendance_status == 251) {
                            $rowData['presentCount'] = 1; //Present
                        }
                    }
                    $is_found = 1;
                }

                if ($attendance_record_exit->atd_is_overtime == 1) {
                    $rowData['overtimeCount'] = 1;
                }

                if ($attendance_record_exit->atd_is_late == 1) {
                    $rowData['lateCount'] = 1;
                }

                if ($attendance_record_exit->atd_is_early_exit == 1) {
                    $rowData['earlyExitCount'] = 1;
                }

                $rowData['status'] = $attendance_record_exit->fh_attendance_status->m_name;
                $rowData['statusColor'] = json_decode($attendance_record_exit->fh_attendance_status->m_other, true)['color'];
                $rowData['checkInTime'] = $attendance_record_exit->atd_check_in_time ? Carbon::parse($attendance_record_exit->atd_check_in_time)->format('h:i A') : '-';
                $rowData['checkOutTime'] = $attendance_record_exit->atd_check_out_time ? Carbon::parse($attendance_record_exit->atd_check_out_time)->format('h:i A') :  '-';
                $rowData['workingHour'] = $attendance_record_exit->atd_total_worked_hours ? number_format($attendance_record_exit->atd_total_worked_hours, 2) : '-';
                $rowData['earlyExit'] = ($attendance_record_exit->atd_is_early_exit && $attendance_record_exit->atd_early_exit_duration) ? number_format($attendance_record_exit->atd_early_exit_duration, 2) :  null;
                $rowData['late'] = ($attendance_record_exit->atd_is_late && $attendance_record_exit->atd_late_duration) ? number_format($attendance_record_exit->atd_late_duration, 2) :  null;
                $rowData['OT'] = ($attendance_record_exit->atd_is_overtime && $attendance_record_exit->atd_overtime_hours) ? number_format($attendance_record_exit->atd_overtime_hours, 2) :  null;
                $rowData['attendance_remark'] = $attendance_record_exit->atd_remark  ? $attendance_record_exit->atd_remark :  '-';
                $rowData['checkInLocation'] = $attendance_record_exit && $attendance_record_exit->atd_punchin_location ? $attendance_record_exit->atd_punchin_location : '--';
                $rowData['checkOutLocation'] =  $attendance_record_exit && $attendance_record_exit->atd_punchout_location ? $attendance_record_exit->atd_punchout_location : '--';
                $rowData['checkInPhoto'] = $attendance_record_exit && $attendance_record_exit->atd_punchin_photo ? json_decode($attendance_record_exit->atd_punchin_photo) : [];
                $rowData['checkOutPhoto'] =  $attendance_record_exit && $attendance_record_exit->atd_punchout_photo ? json_decode($attendance_record_exit->atd_punchout_photo) : [];
                $rowData['atd_segments'] =  $attendance_record_exit && $attendance_record_exit->atd_segments ? [json_decode($attendance_record_exit->atd_segments)] : [];
                $rowData['updatedBy'] = $attendance_record_exit->updated_by  ? $attendance_record_exit->updated_by->emp_full_name :  '-';
                if ($attendance_log) {
                    $rowData['previousCheckInTime'] = $attendance_log->al_check_in_time ? Carbon::parse($attendance_log->al_check_in_time)->format('h:i A') : '-';
                    $rowData['previousCheckOutTime'] = $attendance_log->al_check_out_time ? Carbon::parse($attendance_log->al_check_out_time)->format('h:i A') :  '-';
                    $rowData['previousLate'] = ($attendance_log->al_is_late && $attendance_log->al_late_duration) ? number_format($attendance_log->al_late_duration, 2) : null;
                    $rowData['previousExit'] = ($attendance_log->al_is_early_exit && $attendance_log->al_early_exit_duration) ? number_format($attendance_log->al_early_exit_duration, 2) : null;
                }
            }


            $half_day_present = ($attendance_record_exit && $attendance_record_exit->atd_attendance_status == 252); //252==Half Day
            if (is_null($is_found) || $half_day_present) {

                $leave_record_exit = $employee->leave_requests()->where('lvr_start_date', '<=', Carbon::parse($date)->format('Y-m-d'))->where('lvr_end_date', '>=', Carbon::parse($date)->format('Y-m-d'))->first();

                if ($leave_record_exit && $leave_record_exit->fh_leave_cat_type) {
                    if ($leave_record_exit->fh_leave_day_type->m_id == 201) {
                        $rowData['leaveCount'] = 1;
                        if ($leave_record_exit->lvr_status != 170 && $leave_record_exit->lvr_stage_completed) {
                            $rowData['approvedLeaveCount'] = 1;
                        }
                    } else {
                        $rowData['leaveCount'] = 0.5;
                        if ($leave_record_exit->lvr_status != 170 && $leave_record_exit->lvr_stage_completed) {
                            $rowData['approvedLeaveCount'] = 0.5;
                        }
                    }

                    $rowData['isApproved'] = ($leave_record_exit->lvr_status != 170 && $leave_record_exit->lvr_stage_completed);
                    $leave_category_ids = MasterTable::where('m_group', 'LEAVE_CATEGORY')->pluck('m_id')->toArray();
                    if (in_array($leave_record_exit->fh_leave_cat_type->m_id, $leave_category_ids) && $rowData['isApproved']) {
                        $is_found = 1;
                        if ($half_day_present) { // For half-day leave and half-day present
                            if ($leave_record_exit->fh_leave_day_segment->m_id == 236) {
                                $rowData['status_id'] = $attendance_record_exit->fh_attendance_status->m_id . '/' . $leave_record_exit->fh_leave_cat_type->m_id;
                                $rowData['status'] = $attendance_record_exit->fh_attendance_status->m_name . '/' . $leave_record_exit->fh_leave_cat_type->m_name;
                                $rowData['status_code'] = $attendance_record_exit->fh_attendance_status->m_type . '/' . $leave_record_exit->fh_leave_cat_type->m_type;
                                $rowData['statusColor'] = json_decode($attendance_record_exit->fh_attendance_status->m_other, true)['color'] . '/' . json_decode($leave_record_exit->fh_leave_cat_type->m_other, true)['color'];
                            } elseif ($leave_record_exit->fh_leave_day_segment->m_id == 235) {
                                $rowData['status_id'] = $leave_record_exit->fh_leave_cat_type->m_id . '/' . $attendance_record_exit->fh_attendance_status->m_id;
                                $rowData['status'] = $leave_record_exit->fh_leave_cat_type->m_name . '/' . $attendance_record_exit->fh_attendance_status->m_name;
                                $rowData['status_code'] =  $leave_record_exit->fh_leave_cat_type->m_type . '/' . $attendance_record_exit->fh_attendance_status->m_type;
                                $rowData['statusColor'] = json_decode($leave_record_exit->fh_leave_cat_type->m_other, true)['color'] . '/' . json_decode($attendance_record_exit->fh_attendance_status->m_other, true)['color'];
                            }
                            $rowData['presentCount']  = $rowData['presentCount'] + 0.5; //if half day leave & approved count as half present
                        } elseif ($leave_record_exit->lvr_total_leave_days == 0.5) { //This case addresses situations where the leave balance is 0.5, but the leave applied exceeds 0.5.
                            if (Carbon::parse($date)->format('Y-m-d') < now()->format('Y-m-d')) {
                                $upLeave = MasterTable::where('m_id', 215)->first(); //215==Unpaid Leave - (UPL)
                                $rowData['status_id'] = $leave_record_exit->fh_leave_cat_type->m_id . '/' . $upLeave?->m_id;
                                $rowData['status'] = $leave_record_exit->fh_leave_cat_type->m_name . '/' . $upLeave?->m_name;
                                $rowData['status_code'] =  $leave_record_exit->fh_leave_cat_type->m_type . '/' . $upLeave?->m_type;
                                $rowData['statusColor'] =  json_decode($leave_record_exit->fh_leave_cat_type->m_other, true)['color'] . '/' . json_decode($upLeave->m_other, true)['color'];
                                $rowData['presentCount']  = 0.5;
                                $rowData['absentCount'] = 0.5;
                            } else {
                                if (optional($leave_record_exit->fh_leave_day_segment)->m_id == 236) {
                                    $rowData['status_id'] = '-/' . $leave_record_exit->fh_leave_cat_type->m_id;
                                    $rowData['status'] = '-/' . $leave_record_exit->fh_leave_cat_type->m_name;
                                    $rowData['status_code'] =  '-/' . $leave_record_exit->fh_leave_cat_type->m_type;
                                    $rowData['statusColor'] =  '#BDBDBD/' . json_decode($leave_record_exit->fh_leave_cat_type->m_other, true)['color'];
                                    $rowData['presentCount']  = 0.5;
                                    $rowData['absentCount'] = 0.5;
                                }
                                if (optional($leave_record_exit->fh_leave_day_segment)->m_id == 235) {
                                    $rowData['status_id'] = $leave_record_exit->fh_leave_cat_type->m_id . '/-';
                                    $rowData['status'] = $leave_record_exit->fh_leave_cat_type->m_name . '/-';
                                    $rowData['status_code'] =  $leave_record_exit->fh_leave_cat_type->m_type . '/-';
                                    $rowData['statusColor'] =  json_decode($leave_record_exit->fh_leave_cat_type->m_other, true)['color'] . '/#BDBDBD';
                                    $rowData['presentCount']  = 0.5;
                                    $rowData['absentCount'] = 0.5;
                                }
                            }
                        } else {
                            $rowData['presentCount']  = 1; //if leave is full day & approved count as present
                            $rowData['status_id'] = $leave_record_exit->fh_leave_cat_type->m_id;
                            $rowData['status'] = $leave_record_exit->fh_leave_cat_type->m_name;
                            $rowData['status_code'] =  $leave_record_exit->fh_leave_cat_type->m_type;
                            $rowData['statusColor'] =  json_decode($leave_record_exit->fh_leave_cat_type->m_other, true)['color'];
                        }
                    } elseif (in_array($leave_record_exit->fh_leave_cat_type->m_id, [215]) && $rowData['isApproved']) { //215==Unpaid Leave - (UPL)
                        $is_found = 1;
                        if ($half_day_present) { // For half-day leave and half-day present
                            if ($leave_record_exit->fh_leave_day_segment->m_id == 236) {
                                $rowData['status_id'] = $attendance_record_exit->fh_attendance_status->m_id . '/' . $leave_record_exit->fh_leave_cat_type->m_id;
                                $rowData['status'] = $attendance_record_exit->fh_attendance_status->m_name . '/' . $leave_record_exit->fh_leave_cat_type->m_name;
                                $rowData['status_code'] = $attendance_record_exit->fh_attendance_status->m_type . '/' . $leave_record_exit->fh_leave_cat_type->m_type;
                                $rowData['statusColor'] = json_decode($attendance_record_exit->fh_attendance_status->m_other, true)['color'] . '/' . json_decode($leave_record_exit->fh_leave_cat_type->m_other, true)['color'];
                            } elseif ($leave_record_exit->fh_leave_day_segment->m_id == 235) {
                                $rowData['status_id'] = $leave_record_exit->fh_leave_cat_type->m_id . '/' . $attendance_record_exit->fh_attendance_status->m_id;
                                $rowData['status'] = $leave_record_exit->fh_leave_cat_type->m_name . '/' . $attendance_record_exit->fh_attendance_status->m_name;
                                $rowData['status_code'] =  $leave_record_exit->fh_leave_cat_type->m_type . '/' . $attendance_record_exit->fh_attendance_status->m_type;
                                $rowData['statusColor'] = json_decode($leave_record_exit->fh_leave_cat_type->m_other, true)['color'] . '/' . json_decode($attendance_record_exit->fh_attendance_status->m_other, true)['color'];
                            }
                            $rowData['absentCount'] = $leave_record_exit->fh_leave_day_type->m_id == 201 ? 1 : 0.5; //if UPL is full day & approved count as absent
                        } else {
                            $upLeave = MasterTable::where('m_id', 215)->first();
                            $rowData['status_id'] = $upLeave->m_id;
                            $rowData['status'] = $upLeave->m_name;
                            $rowData['status_code'] =  $upLeave->m_type;
                            $rowData['statusColor'] =  json_decode($upLeave->m_other, true)['color'];
                            $rowData['absentCount'] = $leave_record_exit->fh_leave_day_type->m_id == 201 ? 1 : 0.5; //if UPL is full day & approved count as absent
                        }
                    }

                    //Bifurcation of leave by leave type
                    $rowData[$leave_record_exit->fh_leave_cat_type->m_type] = $rowData['leaveCount'];
                    $rowData['UPL'] = $rowData['absentCount'];
                }
            }

            $attendance_exist = $attendance_record_exit && ($attendance_record_exit->atd_check_in_time || $attendance_record_exit->atd_check_out_time);

            if (is_null($is_found) || $attendance_exist) {
                $missedPunch =  AttendanceException::where('ae_b_id', $employee->emp_b_id)->where('ae_emp_id', $employee->emp_id)->where('ae_date', '=', $date)->first();
                if ($missedPunch) {
                    $missedPunchStatus = MasterTable::where('m_id', 228)->first(); //Missedpunch
                    $is_found = 1;
                    $rowData['missedPunchCount'] =  1;
                    $rowData['checkInTime'] = Carbon::createFromFormat('H:i:s', $missedPunch->ae_in_time)->format('h:i A');
                    $rowData['checkOutTime'] =  Carbon::createFromFormat('H:i:s', $missedPunch->ae_out_time)->format('h:i A');
                    $rowData['workingHour'] = number_format($missedPunch->ae_total_working, 2);
                    if ($missedPunch->ae_status != 170 && $missedPunch->ae_stage_completed) {

                        if ($attendance_record_exit && ($missedPunch->ae_out_time < optional($employee->fh_shift_type)->pst_end_time)) {
                            $rowData['presentCount']  = 0.5; //if missed punch  approved count as present
                            $rowData['status_id'] = $missedPunchStatus->m_id . '/' . $attendance_record_exit->fh_attendance_status->m_id;
                            $rowData['status'] =  $missedPunchStatus->m_name . '/' . $attendance_record_exit->fh_attendance_status->m_name;
                            $rowData['status_code'] =  $missedPunchStatus->m_type . '/' . $attendance_record_exit->fh_attendance_status->m_type;
                            $rowData['statusColor'] = json_decode($missedPunchStatus->m_other, true)['color'] . '/' . json_decode($attendance_record_exit->fh_attendance_status->m_other, true)['color'];
                            $rowData['approvedMissedPunchCount'] = 0.5;
                        } else {
                            $rowData['presentCount']  = 1; //if missed punch  approved count as present
                            $presentData = MasterTable::where('m_id', 251)->select('m_id', 'm_name', 'm_type', 'm_other')->first();
                            $rowData['status_id'] = $presentData->m_id;
                            $rowData['status'] = $presentData->m_name;
                            $rowData['status_code'] = $presentData->m_type;
                            $rowData['statusColor'] = json_decode($presentData->m_other, true)['color'];
                            $rowData['approvedMissedPunchCount'] = 1;
                        }
                    } else {
                        $rowData['status_id'] = $missedPunchStatus->m_id;
                        $rowData['status'] =   $missedPunchStatus->m_name;
                        $rowData['status_code'] = $missedPunchStatus->m_type;
                        $rowData['statusColor'] = json_decode($missedPunchStatus->m_other, true)['color'];
                    }
                }
            }

            if (is_null($is_found)) {

                $holiday_record_exit =  PolicyHolidayList::where('phl_b_id', $employee->emp_b_id)->where('phl_start_date', '<=', $date)
                    ->where('phl_end_date', '>=', $date)
                    ->first();

                if ($holiday_record_exit) {
                    $holidayStatus = MasterTable::where('m_id', 321)->first();
                    $rowData['holidayCount'] = 1;
                    $is_found = 1;
                    $rowData['status'] = $holidayStatus->m_name;
                    $rowData['status_id'] = $holidayStatus->m_id;
                    $rowData['status_code'] = $holidayStatus->m_type;
                    $rowData['statusColor'] = json_decode($holidayStatus->m_other, true)['color'];
                    $rowData['checkInTime'] = '-';
                    $rowData['checkOutTime'] = '-';
                    $rowData['workingHour'] = '-';
                }
            }
            if (is_null($is_found)) {

                if (in_array($date, $weekOfDates)) {
                    $weekOffStatus = MasterTable::where('m_id', 322)->first();
                    $rowData['weekOffCount'] = 1;
                    $is_found = 1;
                    $rowData['status'] = $weekOffStatus->m_name;
                    $rowData['status_id'] = $weekOffStatus->m_id;
                    $rowData['status_code'] = $weekOffStatus->m_type;
                    $rowData['statusColor'] = json_decode($weekOffStatus->m_other, true)['color'];
                    $rowData['checkInTime'] = '-';
                    $rowData['checkOutTime'] = '-';
                    $rowData['workingHour'] = '-';
                }
            }
            if (is_null($is_found) && Carbon::parse($date)->isPast()) {
                $rowData['absentCount'] = 1;
                $rowData['status'] = $absentStatus->m_name;
                $rowData['status_id'] = $absentStatus->m_id;
                $rowData['status_code'] = $absentStatus->m_type;
                $rowData['statusColor'] = json_decode($absentStatus->m_other, true)['color'];
                $rowData['checkInTime'] = '-';
                $rowData['checkOutTime'] = '-';
                $rowData['workingHour'] = '-';
            }
            if ($rowData['status_id']) {
                $rowData['status_id'] = (string)$rowData['status_id'];
            }
        }
        return $rowData;
    }

    public static function getMonthlyAttendanceCount($employee, $year, $month, $weekOfDates)
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $dateRange = $startDate->toPeriod($endDate);

        // Step 1: Bulk Fetch All Data
        $attendanceRecords = $employee->attendance_record()
            ->whereBetween('atd_date', [$startDate, $endDate])
            ->with('fh_attendance_status')
            ->get()
            ->keyBy(function ($record) {
                return Carbon::parse($record->atd_date)->toDateString();
            });

        $attendanceLogs = AttendanceLog::whereBetween('al_date', [$startDate, $endDate])
            ->where('al_emp_id', $employee->emp_id)
            ->with('fh_attendance_status')
            ->get()
            ->keyBy(function ($record) {
                return Carbon::parse($record->al_date)->toDateString();
            });

        $missedPunches = AttendanceException::where('ae_emp_id', $employee->emp_id)
            ->whereBetween('ae_emp_id', [$startDate, $endDate])
            ->get()
            ->keyBy(function ($row) {
                return Carbon::parse($row->ae_date)->toDateString();
            });

        $leaveRequests = $employee->leave_requests()
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('lvr_start_date', [$startDate, $endDate])
                    ->orWhereBetween('lvr_end_date', [$startDate, $endDate]);
            })
            ->with(['fh_leave_cat_type', 'fh_leave_day_type', 'fh_leave_day_segment'])
            ->get();

        $leaveMap = [];
        foreach ($leaveRequests as $leave) {
            $from = Carbon::parse($leave->lvr_start_date);
            $to = Carbon::parse($leave->lvr_end_date);
            foreach ($from->toPeriod($to) as $d) {
                $dateStr = $d->toDateString();
                if (!isset($leaveMap[$dateStr])) {
                    $leaveMap[$dateStr] = [];
                }
                $leaveMap[$dateStr][] = $leave;
            }
        }

        $holidayRecords = PolicyHolidayList::where('phl_b_id', $employee->emp_b_id)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('phl_start_date', [$startDate, $endDate])
                  ->orWhereBetween('phl_end_date', [$startDate, $endDate])
                  ->orWhere(function ($q) use ($startDate, $endDate) {
                      $q->where('phl_start_date', '<=', $startDate)
                        ->where('phl_end_date', '>=', $endDate);
                  });
            })
            ->get();

        $holidays = $holidayRecords->flatMap(function ($holiday) {
            try {
                $start = Carbon::parse($holiday->phl_start_date);
                $end = Carbon::parse($holiday->phl_end_date);
                
                if ($end->lt($start)) {
                    return [];
                }
                
                if ($start->eq($end)) {
                    return [$start->format('Y-m-d')];
                }
                
                $dates = [];
                $current = $start->copy();
                
                while ($current->lte($end)) {
                    $dates[] = $current->format('Y-m-d');
                    $current->addDay();
                }
                
                return $dates;
                
            } catch (\Exception $e) {
                return [];
            }
        })
        ->filter()
        ->unique()
        ->values()
        ->toArray();

        // Step 2: Initialize Count
        $presentCount = 0;
        $absentCount = 0;
        $leaveCount = 0;
        $lateCount = 0;
        $uplCount = 0;

        // Get all dates as array for sandwich rule processing
        $allDates = [];
        foreach ($dateRange as $dateObj) {
            $allDates[] = $dateObj->toDateString();
        }

        // Helper function to check if a date has ABS/SL/CL/UPL
        $isAbsentOrLeave = function($date) use ($attendanceRecords, $attendanceLogs, $leaveMap, $holidays, $weekOfDates, $employee) {
            if ($employee->emp_date_of_joining > $date) {
                return false;
            }

            $attendance_record_exit = isset($attendanceLogs[$date]) ? $attendanceLogs[$date] : ($attendanceRecords[$date] ?? null);
            $leave_record_exit = $leaveMap[$date] ?? null;
            $holiday_record_exit = in_array($date, $holidays);

            // Check for UPL status first
            if ($attendance_record_exit && $attendance_record_exit->atd_attendance_status == 215) {
                return 215;
            }

            // Check for approved leaves (SL/CL/UPL)
            if ($leave_record_exit && count($leave_record_exit) >= 1) {
                $lvr_exit = $leave_record_exit[0];
                $is_approved = $lvr_exit->lvr_status != 170 && $lvr_exit->lvr_stage_completed;
                if ($is_approved) {
                    if ($lvr_exit->fh_leave_cat_type->m_type == 'CL') return 207;
                    if ($lvr_exit->fh_leave_cat_type->m_type == 'SL') return 208;  
                    if ($lvr_exit->fh_leave_cat_type->m_type == 'UPL') return 215;
                    return (int)$lvr_exit->fh_leave_cat_type->m_id;
                }
            }

            // Check for Absent (not WO, not HO, not present, and date is past)
            if (!$attendance_record_exit && !$leave_record_exit && !$holiday_record_exit && 
                !in_array($date, $weekOfDates) && Carbon::parse($date)->isPast()) {
                return 203; // ABS
            }

            return false;
        };

        // Apply sandwich rule
        $sandwichRuleApplied = [];
        foreach ($allDates as $index => $date) {
            $isInWeekOfDates = in_array($date, $weekOfDates);
            $isInHolidayRecord = in_array($date, $holidays);
            $isCurrentWOOrHO = $isInWeekOfDates || $isInHolidayRecord;
            
            if ($isCurrentWOOrHO && !isset($sandwichRuleApplied[$date])) {
                $prevDate = $index > 0 ? $allDates[$index - 1] : null;
                $nextDate = $index < count($allDates) - 1 ? $allDates[$index + 1] : null;
                
                $prevStatus = $prevDate ? $isAbsentOrLeave($prevDate) : false;
                $nextStatus = $nextDate ? $isAbsentOrLeave($nextDate) : false;
                
                if ($prevStatus && $nextStatus) {
                    $sandwichRuleApplied[$date] = true;
                }
            }
        }

        // Step 3: Loop through date range (in-memory operations only)
        foreach ($dateRange as $dateObj) {
            $date = $dateObj->toDateString();
            $attendance = isset($attendanceLogs[$date]) ? $attendanceLogs[$date] : ($attendanceRecords[$date] ?? null);
            $missedPunch = $missedPunches[$date] ?? null;
            $leaves = $leaveMap[$date] ?? [];
            $isHoliday = in_array($date, $holidays);
            $isWeekOff = in_array($date, $weekOfDates);
            $isSandwichApplied = isset($sandwichRuleApplied[$date]);
            
            // Skip dates before joining or after last working day
            if ($employee->emp_date_of_joining && $dateObj->lt(Carbon::parse($employee->emp_date_of_joining))) {
                continue;
            }
            if ($employee->emp_last_working_day && $dateObj->gt(Carbon::parse($employee->emp_last_working_day))) {
                continue;
            }

            // Check if there's any approved UPL leave for this date
            $hasUPL = false;
            $hasOtherLeave = false;
            $leaveDays = 0;
            
            foreach ($leaves as $leave) {
                if ($leave->lvr_status != 170 && $leave->lvr_stage_completed) {
                    if ($leave->fh_leave_cat_type->m_id == 215) { // UPL
                        $hasUPL = true;
                        $leaveDays = ($leave->fh_leave_day_type->m_id == 201 ? 1 : 0.5);
                    } else { // Other leaves (CL, SL, etc.)
                        $hasOtherLeave = true;
                        $leaveDays = ($leave->fh_leave_day_type->m_id == 201 ? 1 : 0.5);
                    }
                }
            }

            // Handle sandwich rule first
            if ($isSandwichApplied) {
                $absentCount++; // Sandwich rule converts WO/HO to absent
                continue;
            }

            if ($attendance) {
                if ($attendance->atd_attendance_status == 251 || $attendance->al_attendance_status == 251) { // Present
                    $presentCount++;
                } elseif ($attendance->atd_attendance_status == 252 || $attendance->al_attendance_status == 252) { // Half Day
                    $presentCount += 0.5;
                } elseif ($attendance->atd_attendance_status == 215) { // UPL
                    $uplCount++;
                }
                
                if ($attendance->atd_is_late || $attendance->al_is_late) {
                    $lateCount++;
                }
            } elseif ($missedPunch && $missedPunch->ae_status != 170 && $missedPunch->ae_stage_completed) {
                if ($missedPunch->ae_out_time < optional($employee->fh_shift_type)->pst_end_time) {
                    $presentCount += 0.5;
                } else {
                    $presentCount++;
                }
            } elseif ($hasUPL) {
                // UPL leave - count in UPL, not in leaveCount
                $uplCount += $leaveDays;
            } elseif ($hasOtherLeave) {
                // Other approved leaves (CL, SL)
                $leaveCount += $leaveDays;
            } elseif ($isHoliday || $isWeekOff) {
                // Skip holidays and week offs
                continue;
            } else {
                if ($dateObj->isPast()) {
                    $absentCount++;
                }
            }
        }

        return [
            'presentCount' => $presentCount,
            'absentCount' => $absentCount,
            'leaveCount' => $leaveCount,
            'lateCount' => $lateCount,
            'uplCount' => $uplCount,
        ];
    }

    public static function getWeekOffDatesReport($employee, $year = NULL, $month = NULL, $startDate = NULL, $endDate = NULL)
    {
        $instance = new self();
        $weekOfDates = [];
        if (isset($employee->fh_week_off_policy) && $employee->fh_week_off_policy) {
            $weekDays = $employee->fh_week_off_policy->getWeek(
                json_decode($employee->fh_week_off_policy->pwo_recurrence_day_ids)
            );

            // If year and month are provided, process only that month
            if ($year && $month) {
                $occurrences = $instance->getOccurrencesOfDaysInMonthReport($year, $month);
                foreach ($occurrences as $key => $dates) {
                    if (isset($weekDays[$key])) {
                        foreach ($weekDays[$key] as $wd) {
                            $nthDay = (int) filter_var($wd, FILTER_SANITIZE_NUMBER_INT);
                            if (isset($dates[$nthDay - 1])) {
                                $dateToCheck = $dates[$nthDay - 1];
                                $leave_exist = LeaveRequest::where('lvr_emp_id', $employee->emp_id)
                                ->where('lvr_stage_completed', 1)
                                ->where('lvr_status', '!=', 170)
                                ->where(function ($q) use ($dateToCheck) {
                                    $q->whereDate('lvr_start_date', '<=', $dateToCheck)
                                    ->whereDate('lvr_end_date', '>=', $dateToCheck);
                                })
                                ->exists();
                                if (!$leave_exist)
                                    $weekOfDates[] = $dates[$nthDay - 1];
                            }
                        }
                    }
                }
            }
            // If startDate and endDate are provided, process each month in the range
            elseif ($startDate && $endDate) {
                $start = \Carbon\Carbon::parse($startDate)->startOfMonth();
                $end = \Carbon\Carbon::parse($endDate)->endOfMonth();
                while ($start->lte($end)) {
                    $year = $start->year;
                    $month = $start->month;
                    $occurrences = $instance->getOccurrencesOfDaysInMonthReport($year, $month);
                    foreach ($occurrences as $key => $dates) {
                        if (isset($weekDays[$key])) {
                            foreach ($weekDays[$key] as $wd) {
                                $nthDay = (int) filter_var($wd, FILTER_SANITIZE_NUMBER_INT);
                                if (isset($dates[$nthDay - 1])) {
                                    $dateToCheck = $dates[$nthDay - 1];
                                    $leave_exist = LeaveRequest::where('lvr_emp_id', $employee->emp_id)
                                    ->where('lvr_stage_completed', 1)
                                    ->where('lvr_status', '!=', 170)
                                    ->where(function ($q) use ($dateToCheck) {
                                        $q->whereDate('lvr_start_date', '<=', $dateToCheck)
                                        ->whereDate('lvr_end_date', '>=', $dateToCheck);
                                    })
                                    ->exists();
                                    if (!$leave_exist)
                                        $weekOfDates[] = $dates[$nthDay - 1];
                                }
                            }
                        }
                    }
                    $start->addMonth();
                }
                // Filter to only include dates within the original range
                $weekOfDates = array_filter($weekOfDates, function($date) use ($startDate, $endDate) {
                    return $date >= $startDate && $date <= $endDate;
                });
                $weekOfDates = array_values($weekOfDates);
            }
        }
        return $weekOfDates;
    }

    private function getOccurrencesOfDaysInMonthReport(int $year, int $month): array
    {
        $daysInMonth = \Carbon\Carbon::create($year, $month)->daysInMonth;
        $occurrences = [];

        // Initialize array for each day of the week
        $weekDays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        foreach ($weekDays as $day) {
            $occurrences[$day] = [];
        }

        // Iterate through all days in the month
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = \Carbon\Carbon::create($year, $month, $day);
            $dayName = $date->format('l'); // Get the full day name (e.g., Sunday, Monday)
            $occurrences[$dayName][] = $date->toDateString();
        }

        return $occurrences;
    }

    public static function getWeekOffDates($employee, $year = NULL, $month = NULL, $startDate = NULL, $endDate = NULL)
    {

        $instance = new self();
        $weekOfDates = [];
        if (isset($employee->fh_week_off_policy) && $employee->fh_week_off_policy) {


            if ($startDate && $endDate) {
                $occurrences = $instance->getWeekDaysInDateRange($startDate, $endDate);
            } else {
                $occurrences = $instance->getOccurrencesOfDaysInMonth($year, $month);
            }
            $weekDays = $employee->fh_week_off_policy->getWeek(
                json_decode($employee->fh_week_off_policy->pwo_recurrence_day_ids)
            );

            foreach ($occurrences as $key => $dates) {
                if (isset($weekDays[$key])) {
                    foreach ($weekDays[$key] as $wd) {
                        $nthDay = (int) filter_var($wd, FILTER_SANITIZE_NUMBER_INT);
                        if (isset($dates[$nthDay - 1])) {
                            $dateToCheck = $dates[$nthDay - 1];
                            $leave_exist = LeaveRequest::where('lvr_emp_id', $employee->emp_id)
                            ->where('lvr_stage_completed', 1)
                            ->where('lvr_status', '!=', 170)
                            ->where(function ($q) use ($dateToCheck) {
                                $q->whereDate('lvr_start_date', '<=', $dateToCheck)
                                ->whereDate('lvr_end_date', '>=', $dateToCheck);
                            })
                            ->exists();
                            if (!$leave_exist)
                                $weekOfDates[] = $dates[$nthDay - 1];
                        }
                    }
                }
            }
        }
        return $weekOfDates;
    }

    public static function getWeekOffDatesInRange($employee, $startDate, $endDate)
    {
        $instance = new self();
        $weekOffDates = [];

        if (isset($employee->fh_week_off_policy) && $employee->fh_week_off_policy) {
            // Convert start and end dates to Carbon instances
            $startDate = Carbon::parse($startDate);
            $endDate = Carbon::parse($endDate);

            // Get all occurrences of days between the start and end dates
            $occurrences = $instance->getOccurrencesOfDaysInRange($startDate, $endDate);

            // Get the week-off days from the employee's policy
            $weekDays = $employee->fh_week_off_policy->getWeek(
                json_decode($employee->fh_week_off_policy->pwo_recurrence_day_ids)
            );

            // dd($weekDays);

            foreach ($occurrences as $dayOfWeek => $dates) {
                if (isset($weekDays[$dayOfWeek])) {
                    foreach ($weekDays[$dayOfWeek] as $wd) {
                        $nthDay = (int) filter_var($wd, FILTER_SANITIZE_NUMBER_INT);
                        if (isset($dates[$nthDay - 1])) {
                            $weekOffDates[] = $dates[$nthDay - 1];
                        }
                    }
                }
            }
        }

        return $weekOffDates;
    }

    private function getOccurrencesOfDaysInRange(string $startDate, string $endDate): array
    {
        $occurrences = [];

        // Initialize array for each day of the week
        $weekDays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        foreach ($weekDays as $day) {
            $occurrences[$day] = [];
        }

        // Convert start and end dates to Carbon instances
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $currentDate = $start->copy();

        // Iterate through all dates in the range
        while ($currentDate->lte($end)) {
            $dayName = $currentDate->format('l'); // Get the full day name (e.g., Sunday, Monday)
            $occurrences[$dayName][] = $currentDate->toDateString();
            $currentDate->addDay(); // Move to the next day
        }

        return $occurrences;
    }

    private function getOccurrencesOfDaysInMonth(int $year, int $month): array
    {
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;
        $occurrences = [];

        // Initialize array for each day of the week
        $weekDays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        foreach ($weekDays as $day) {
            $occurrences[$day] = [];
        }

        // Iterate through all days in the month
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($year, $month, $day);
            $dayName = $date->format('l'); // Get the full day name (e.g., Sunday, Monday)
            $occurrences[$dayName][] = $date->toDateString();
        }

        return $occurrences;
    }

    private function getWeekDaysInDateRange($startDate, $endDate)
    {
        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);
        $occurrences = [];

        // Initialize array for each day of the week
        $weekDays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        foreach ($weekDays as $day) {
            $occurrences[$day] = [];
        }
        while ($startDate->lte($endDate)) {
            $dayName = $startDate->format('l'); // Get the full day name (e.g., Sunday, Monday)
            $occurrences[$dayName][] = $startDate->toDateString();
            $startDate->addDay(); // Properly increment Carbon date
        }
        return $occurrences;
    }

    public static function getBreadcrumbs()
    {
        $segments = FacadesRequest::segments(); // Get URL segments
        $breadcrumbs = [];

        // Add "Dashboard" as the first breadcrumb
        $breadcrumbs[] = [
            'title' => 'Dashboard',
            'url' => url('/dashboard'),
        ];

        // Loop through URL segments and create breadcrumbs
        $path = '';
        foreach ($segments as $segment) {
            $path .= '/' . $segment;
            $breadcrumbs[] = [
                'title' => ucfirst(str_replace('-', ' ', $segment)), // Format title
                'url' => url($path),
            ];
        }

        return $breadcrumbs;
    }

    public static function alpha_numeric_generator($num, $const = '', $prefix_id = '', $ref_id = '', $prefix_char = '')
    {
        if ($ref_id != '' || $ref_id != null || !isset($ref_id)) {
            if ($const != '')
                $ref_id = explode($const, $ref_id)[1];
            $match = preg_split('/([A-Za-z]+)/', $ref_id, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            $match[1] = substr($match[1], -$num);
            if ($match[1] != str_repeat(9, $num)) {
                $match[1]++;
            } else {
                $match[0]++;
                $match[1] = 1;
            }
            $result = $const . $match[0] . $prefix_id . str_pad($match[1], $num, 0, STR_PAD_LEFT);
        } else {
            if ($prefix_char == '') {
                $result = $const . 'AA' . $prefix_id . str_pad(1, $num, 0, STR_PAD_LEFT);
            } else {
                $result = $const . $prefix_char . $prefix_id . str_pad(1, $num, 0, STR_PAD_LEFT);
            }
        }

        return $result;
    }

    public static function send_mail($email, $mailable)
    {
        // return true; //this is temporary because mail is not working currently remove this line when resolved to execute the below code
        return Mail::to($email)->send($mailable);
    }

    // public static function sendCustomEmail($templateType, array $placeholders, $recipientEmail, $businessId, $attachment = null)
    // {
    //     return true; //this is temporary because mail is not working currently remove this line when resolved to execute the below code

    //     // Retrieve the mail template based on type and business ID
    //     $template = MailTemplate::where('mt_mail_type', $templateType)->where('mt_b_id', $businessId)->first();
    //     if (!$template) {
    //         $template = MailTemplate::where('mt_mail_type', $templateType)->first();
    //     }

    //     // If no template found, return false
    //     if (!$template) {
    //         return false;
    //     }

    //     // Replace placeholders in the email body and subject
    //     $mailBody = str_replace(array_keys($placeholders), array_values($placeholders), $template->mt_body);
    //     $mailSubject = str_replace(array_keys($placeholders), array_values($placeholders), $template->mt_title);

    //     // Send the email
    //     Mail::send([], [], function ($message) use ($recipientEmail, $mailSubject, $mailBody, $attachment) {
    //         $message->to($recipientEmail)
    //             ->subject($mailSubject)
    //             ->html($mailBody);
    //         if ($attachment) {
    //             // If it's a file path (e.g., from storage), attach the file
    //             $message->attach($attachment['path'], [
    //                 'as' => $attachment['name'], // Optional: specify a custom file name for the attachment
    //                 'mime' => $attachment['mime'], // Optional: specify MIME type, e.g., 'application/pdf'
    //             ]);
    //         }
    //     });

    //     return true;
    // }


    public static function sendCustomEmail($templateType, array $placeholders, $recipientEmail, $businessId, $attachment = null)
    {
        //   dd($templateType, $placeholders, $recipientEmail, $businessId,);

        try {
            $approvalMap = [
                250 => [251, 252],
                229 => [231, 232],
                145 => [146, 147],
                146 => [148, 149],
                199 => [200, 201],
                339 => [340, 341],
                442 => [443, 444],
            ];

            $parentModuleId = $templateType;
            $mailType = 'submission';

            foreach ($approvalMap as $parent => $children) {
                if (in_array($templateType, $children)) {
                    $parentModuleId = $parent;
                    $mailType = ($templateType === $children[0]) ? 'approved' : 'rejected';
                    break;
                }
            }

            $template = MailTemplate::where('mt_b_id', $businessId)
                ->where('mt_module_id', $parentModuleId)
                ->where('mt_mail_type', $mailType)
                ->where('mt_is_enabled', 1)
                ->first();


            if (!$template) {
                Log::warning("Mail template not found for type: $templateType, business: $businessId");
                return false;
            }

            $finalBody = str_replace(array_keys($placeholders), array_values($placeholders), $template->mt_body);

            Mail::send([], [], function ($message) use ($recipientEmail, $finalBody, $template, $attachment) {
                $message->to($recipientEmail)
                    ->subject($template->mt_title)
                    ->html($finalBody);

                if ($attachment) {
                    $message->attach($attachment);
                }
            });
            return true; // indicate success
        } catch (Exception $e) {
            Log::error("Email Error: " . $e->getMessage());
            return false; // indicate failure
        }
    }

    public static function encrypt_or_decrypt($key, $type = 'encrypt')
    {
        $value = '';
        if ($type == 'decrypt') {
            $value = Crypt::decryptString($key);
        } else {
            $value =  Crypt::encryptString($key);
        }
        return $value;
    }

    public static function newGetMonthlyAttendanceDetails($employee, $month, $year, $holiday_record_exits, $weekOfDates)
    {
        $salaryDay = $totalOTHrs = 0;
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        $dateRange = CarbonPeriod::create($startDate, $endDate);
        $isAbsCheck = $employee->fh_attendance_policy?->ap_mark_absent_check;

        $masterIds = [157, 170, 201, 203, 215, 228, 235, 236, 251, 252, 319, 320, 321, 322];
        $masters = MasterTable::whereIn('m_id', $masterIds)->get()->keyBy('m_id');

        $getMaster = function ($id) use ($masters) {
            if (!is_scalar($id)) {
                return null; // safety
            }
            $id = (int) $id;
            $m = $masters->get($id);
            if (!$m) return null;
            $other = json_decode($m->m_other ?? '{}', true);
            return (object)[
                'm_id'   => $m->m_id,
                'm_name' => $m->m_name ?? '',
                'm_type' => $m->m_type ?? '',
                'color'  => $other['color'] ?? '#000000',
            ];
        };

        $approveStatus   = $getMaster(157);
        $rejectStatus    = $getMaster(170);
        $fullDayLeave    = $getMaster(201);
        $absentStatus    = $getMaster(203);
        $uplStatus       = $getMaster(215);
        $mspStatus       = $getMaster(228);
        $firstHalfLeave  = $getMaster(235);
        $secondHalfLeave = $getMaster(236);
        $presentStatus   = $getMaster(251);
        $halfDayStatus   = $getMaster(252);
        $hoPresentStatus = $getMaster(319);
        $woPresentStatus = $getMaster(320);
        $holidayStatus   = $getMaster(321);
        $weekOffStatus   = $getMaster(322);

        // Preload everything to avoid repeated DB hits
        $attendanceRecords = $employee->attendance_record()
            ->whereBetween('atd_date', [$startDate, $endDate])
            ->orderBy('atd_id', 'desc')
            ->get()
            ->keyBy(fn($row) => Carbon::parse($row->atd_date)->toDateString());

        $attendanceLogs = AttendanceLog::where('al_emp_id', $employee->emp_id)
            ->whereBetween('al_date', [$startDate, $endDate])
            ->get()
            ->keyBy(fn($row) => Carbon::parse($row->al_date)->toDateString());

        $missedPunches = AttendanceException::where('ae_b_id', $employee->emp_b_id)
            ->where('ae_emp_id', $employee->emp_id)
            ->where('ae_stage_completed', 1)
            ->where('ae_status', '!=', $rejectStatus->m_id)
            ->whereBetween('ae_date', [$startDate, $endDate])
            ->get()
            ->keyBy(fn($row) => Carbon::parse($row->ae_date)->toDateString());

        $leave_record_exits = $employee->leave_requests()->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('lvr_start_date', [$startDate, $endDate])
                ->orWhereBetween('lvr_end_date', [$startDate, $endDate]);
        })->where('lvr_status', '!=', $rejectStatus->m_id)
            ->where('lvr_stage_completed', 1)
            ->get();

        $leaveByDate = collect();

        foreach ($leave_record_exits as $leave) {
            $start = Carbon::parse($leave->lvr_start_date);
            $end = Carbon::parse($leave->lvr_end_date);

            while ($start->lte($end)) {
                $dateStr = $start->toDateString();
                if (!$leaveByDate->has($dateStr)) {
                    $leaveByDate->put($dateStr, collect());
                }
                $leaveByDate->get($dateStr)->push($leave);
                $start->addDay();
            }
        }

        $leave_record_exits = $leaveByDate;
        $attendanceData = [];

        // unpaid list → JSON array → convert into PHP array
        $unpaidWeekOffDays = isset($employee->fh_week_off_policy?->pwo_is_unpaid)
            ? json_decode($employee->fh_week_off_policy->pwo_is_unpaid, true)
            : null;

        $leave_category_ids = MasterTable::where('m_group', 'LEAVE_CATEGORY')->pluck('m_id')->toArray();
        $weekDayMasters = MasterTable::where('m_group', 'WEEK_DAY')
            ->get()
            ->keyBy(fn($m) => strtoupper($m->m_type));

        // Second pass: Generate attendance data
        foreach ($dateRange as $dateObj) {
            $date = $dateObj->toDateString();
            $is_found = null;
            $rowData = [
                'status' => '-',
                'status_id' => '-',
                'status_code' => '-',
                'statusColor' => '#BDBDBD',
                'checkingMethodId' => null,
                'checkInTime' => null,
                'checkOutTime' => null,
                'updatedBy' => null,
                'workingHour' => null,
                'OT' => null,
                'earlyExit' => null,
                'late' => null,
                'attendance_remark' => '--',
                'mark_as_absent' => '--',
                'checkInLocation' => '--',
                'checkOutLocation' => '--',
                'checkInPhoto' => [],
                'checkOutPhoto' => [],
                'atd_segments' => [],
                'ODCount' => 0,
                'presentCount' => 0,
                'holidayPresentCount' => 0,
                'weekOffPresentCount' => 0,
                'leaveCount' => 0,
                'holidayCount' => 0,
                'weekOffCount' => 0,
                'absentCount' => 0,
                'halfDayCount' => 0,
                'missedPunchCount' => 0,
                'overtimeCount' => 0,
                'lateCount' => 0,
                'earlyExitCount' => 0,
                'approvedLeaveCount' => 0,
                'approvedMissedPunchCount' => 0,
                'isApproved' => null,
                'previousCheckInTime' => null,
                'previousCheckOutTime' => null,
                'previousLate' => null,
                'previousExit' => null,
                'UPL' => 0,
                'totalSalariedDays' => 0,
                'totalOTHrs' => 0,
            ];

            // Skip dates before joining or after last working day
            $shouldCountDate = true;
            if ($employee->emp_date_of_joining && $dateObj->lt(Carbon::parse($employee->emp_date_of_joining))) {
                $shouldCountDate = false;
            }
            if ($employee->emp_last_working_day && $dateObj->gt(Carbon::parse($employee->emp_last_working_day))) {
                $shouldCountDate = false;
            }

            if ($shouldCountDate && $employee->emp_date_of_joining <= $date) {
                // Original logic continues here...
                $attendance_record_exit = $attendanceRecords[$date] ?? null;
                $attendance_log = $attendanceLogs[$date] ?? null;
                $missedPunch = $missedPunches[$date] ?? null;
                $holiday_record_exit = $holiday_record_exits[$date] ?? null;
                $leave_record_exit = $leave_record_exits->get($date) ?? null;
                $is_found = null;

                // current date ka weekday master id
                $weekKey = strtoupper($dateObj->format('D'));
                $currentWeekDayMasterId = $weekDayMasters[$weekKey]->m_id ?? null;

                // check unpaid or paid
                if (is_array($unpaidWeekOffDays) && !is_null($unpaidWeekOffDays)) {
                    $isUnpaid = in_array($currentWeekDayMasterId, $unpaidWeekOffDays);
                } else {
                    $isUnpaid = false;
                }

                // Priority 1: Check AttendanceException if found
                if (isset($missedPunch) && !empty($missedPunch) && !$is_found) {
                    $rowData['missedPunchCount'] =  1;

                    // Check if missed punch is approved
                    $isApprovedMSP = ($missedPunch->ae_status == $approveStatus->m_id && $missedPunch->ae_stage_completed == 1);
                    if ($isApprovedMSP) {
                        // Show approved missed punch times
                        $is_found = 1;
                        $rowData['checkInTime'] = Carbon::createFromFormat('H:i:s', $missedPunch->ae_in_time)->format('H:i');
                        $rowData['checkOutTime'] = Carbon::createFromFormat('H:i:s', $missedPunch->ae_out_time)->format('H:i');
                        $rowData['workingHour'] = number_format($missedPunch->ae_total_working, 2, '.', '');
                        $rowData['attendance_remark'] = $missedPunch->ae_reason_id ? $missedPunch?->fh_mispunch_reason->m_name : ($missedPunch->ae_custom_reason ?? 'N/A');

                        $resolvedShift = ShiftResolver::resolveEmployeeShift($employee, $date);
                        $shift = $resolvedShift->shift ?? PolicyShiftTiming::find($employee->emp_shift_type_id);

                        $pst_start_time = Carbon::parse($shift->pst_start_time ?? '09:00:00');
                        $pst_end_time = Carbon::parse($shift->pst_end_time ?? '18:00:00');
                        $pst_exit_time = Carbon::parse($shift->pst_min_work_hour);

                        $dailyWorkingHours = $pst_start_time->diffInMinutes($pst_end_time);
                        $dailyExitHours = $pst_start_time->diffInMinutes($pst_exit_time);
                        $checkInTime = Carbon::parse($date . ' ' . $missedPunch->ae_in_time);
                        $checkOutTime =  Carbon::parse($date . ' ' . $missedPunch->ae_out_time);
                        $workedDuration = $checkInTime->diffInMinutes($checkOutTime);
                        $fullDayThreshold = $dailyWorkingHours;
                        $halfDayThreshold = $dailyWorkingHours / 2;
                        $halfDayExitThreshold = isset($dailyExitHours) ? $dailyExitHours / 2 : $halfDayThreshold;

                        if ($workedDuration >= $dailyExitHours || $workedDuration >= $fullDayThreshold) {
                            $isWeekOff = in_array($date, $weekOfDates);
                            $master    = $isWeekOff ? $woPresentStatus : $presentStatus;

                            $rowData['status_id']              = $master->m_id;
                            $rowData['status']                 = $master->m_name;
                            $rowData['status_code']            = $master->m_type;
                            $rowData['statusColor']            = $master->color;
                            $rowData['approvedMissedPunchCount'] = 1;
                            if ($isWeekOff) {
                                $rowData['weekOffPresentCount'] = 1;
                                $rowData['weekOffCount'] = $isUnpaid ? 0 : 1;
                            } else {
                                $rowData['presentCount'] = 1;
                            }
                        } else if ($workedDuration >= $halfDayExitThreshold || $workedDuration >= $halfDayThreshold) {
                            //half day present & half day leave case
                            $halfDayPresent = $getMaster($missedPunch->ae_attendance_status ?? $halfDayStatus->m_id);
                            if ($leave_record_exit && $leave_record_exit->count() == 1) {
                                $leaveExit = $leave_record_exit->first();
                                $segmentId = $leaveExit?->fh_leave_day_segment->m_id;
                                $leaveType = $leaveExit->fh_leave_cat_type;
                                if ($segmentId == $secondHalfLeave->m_id) {
                                    $rowData['status_id'] = $halfDayPresent->m_id.'/'.$leaveType->m_id;
                                    $rowData['status'] = $halfDayPresent->m_name.'/'.$leaveType->m_name;
                                    $rowData['status_code'] = $halfDayPresent->m_type.'/'.$leaveType->m_type;
                                    $rowData['statusColor'] = $halfDayPresent->color.'/'.$leaveType->color;
                                } elseif ($segmentId == $firstHalfLeave->m_id) {
                                    $rowData['status_id'] = $leaveType->m_id.'/'.$halfDayPresent->m_id;
                                    $rowData['status'] = $leaveType->m_name.'/'.$halfDayPresent->m_name;
                                    $rowData['status_code'] = $leaveType->m_type.'/'.$halfDayPresent->m_type;
                                    $rowData['statusColor'] = $leaveType->color.'/'.$halfDayPresent->color;
                                }
                            } else {
                                $rowData['status_id']   = $halfDayPresent->m_id;
                                $rowData['status']      = $halfDayPresent->m_name;
                                $rowData['status_code'] = $halfDayPresent->m_type;
                                $rowData['statusColor'] = $halfDayPresent->color;
                            }
                            $rowData['presentCount'] = 0.5;
                            $rowData['approvedMissedPunchCount'] = 0.5;
                        } else {
                            $rowData['checkInTime'] = Carbon::createFromFormat('H:i:s', $missedPunch->ae_in_time)->format('H:i');
                            $rowData['checkOutTime'] =  Carbon::createFromFormat('H:i:s', $missedPunch->ae_out_time)->format('H:i');
                            $rowData['workingHour'] = number_format($missedPunch->ae_total_working, 2, '.', '');

                            $rowData['status_id'] = $absentStatus->m_id;
                            $rowData['status'] =   $absentStatus->m_name;
                            $rowData['status_code'] = $absentStatus->m_type;
                            $rowData['statusColor'] = $absentStatus->color;
                            $rowData['absentCount'] = 1;
                        }
                    }
                }

                // Priority 2: Approved FULL DAY Leave → attendance को override करे
                if (is_null($is_found) && $leave_record_exit && $leave_record_exit->count() >= 1) {

                    $fullDayLeaveFound = false;
                    $selectedLeave = null;
                    foreach ($leave_record_exit as $leave) {
                        $dayTypeId = optional($leave->fh_leave_day_type)->m_id ?? $fullDayLeave->m_id;
                        $approved = $leave->lvr_status != $rejectStatus->m_id && $leave->lvr_stage_completed;

                        if ($approved && $dayTypeId == $fullDayLeave->m_id) {
                            $selectedLeave = $leave;
                            $fullDayLeaveFound = true;
                            break;
                        }
                    }

                    if ($fullDayLeaveFound && $selectedLeave) {
                        $lvrCatTypeId = $selectedLeave->fh_leave_cat_type;
                        $is_unpaid = $lvrCatTypeId->m_id == $uplStatus->m_id;

                        $rowData['status']      = $lvrCatTypeId->m_name;
                        $rowData['status_id']   = (string)$lvrCatTypeId->m_id;
                        $rowData['status_code'] = $lvrCatTypeId->m_type;
                        $rowData['statusColor'] = json_decode($lvrCatTypeId->m_other ?? '{}', true)['color'] ?? '#000000';

                        $rowData['checkInTime']   = '';
                        $rowData['checkOutTime']  = '';
                        $rowData['workingHour']   = '';
                        $rowData['OT']            = null;
                        $rowData['late']          = null;
                        $rowData['earlyExit']     = null;

                        $rowData['presentCount']       = 0;
                        $rowData['halfDayCount']       = 0;
                        $rowData['missedPunchCount']   = 0;
                        $rowData['UPL']                = $is_unpaid ? 1 : 0;
                        $rowData['leaveCount']         = $is_unpaid ? 0 : 1;
                        $rowData['approvedLeaveCount'] = $is_unpaid ? 0 : 1;

                        $is_found = 1;
                    }
                }

                // Priority 3: Check AttendanceLog if no AttendanceException found
                if (isset($attendance_log) && !empty($attendance_log) && !isset($is_found)) {
                    $is_found = 1;

                    // Set basic attendance data from log
                    $rowData['checkInTime'] = $attendance_log->al_check_in_time ? Carbon::parse($attendance_log->al_check_in_time)->format('H:i') : '';
                    $rowData['checkOutTime'] = $attendance_log->al_check_out_time ? Carbon::parse($attendance_log->al_check_out_time)->format('H:i') :  '';
                    $rowData['workingHour'] = $attendance_log->al_total_worked_hours ? number_format($attendance_log->al_total_worked_hours, 2, '.', '') : '';
                    $rowData['attendance_remark'] = $attendance_log->al_reason ? $attendance_log->al_reason : ($attendance_record_exit?->atd_remark ?? null);
                    $rowData['mark_as_absent'] = $attendance_log->al_is_absent ? $attendance_log->al_is_absent : ($attendance_record_exit?->atd_is_absent ?? null);

                    $rowData['late'] = ($attendance_log->al_is_late && $attendance_log->al_late_duration) ? number_format($attendance_log->al_late_duration, 2, '.', '') :  null;
                    $rowData['earlyExit'] = ($attendance_log->al_is_early_exit && $attendance_log->al_early_exit_duration) ? number_format($attendance_log->al_early_exit_duration, 2, '.', '') :  null;

                    $rowData['updatedBy'] = optional($attendance_log->fh_employee_data)->emp_full_name ?? '-';
                    $rowData['previousCheckInTime'] = isset($attendance_record_exit->atd_check_in_time) && !empty($attendance_record_exit->atd_check_in_time)
                        ? Carbon::parse($attendance_record_exit->atd_check_in_time)->format('H:i')
                        : '-';

                    $rowData['previousCheckOutTime'] = isset($attendance_record_exit->atd_check_out_time) && !empty($attendance_record_exit->atd_check_out_time)
                        ? Carbon::parse($attendance_record_exit->atd_check_out_time)->format('H:i')
                        : '-';

                    $rowData['previousLate'] = isset($attendance_record_exit->atd_is_late) && !empty($attendance_record_exit->atd_late_duration) ? number_format($attendance_record_exit->atd_late_duration, 2, '.', '') : null;
                    $rowData['previousExit'] = isset($attendance_record_exit->atd_is_early_exit) && !empty($attendance_record_exit->atd_early_exit_duration) ? number_format($attendance_record_exit->atd_early_exit_duration, 2, '.', '') : null;

                    // Determine status based on log data
                    if ($attendance_log->al_attendance_status) {
                        $statusData = $getMaster($attendance_log->al_attendance_status);
                        if ($statusData) {
                            $rowData['status_id'] = $statusData->m_id;
                            $rowData['status'] = $statusData->m_name;
                            $rowData['status_code'] = $statusData->m_type;
                            $rowData['statusColor'] = $statusData->color;

                            if ($statusData->m_id == 251) { // Present
                                $rowData['presentCount'] = 1;
                            } elseif ($statusData->m_id == 252) { // Half Day
                                $rowData['halfDayCount'] = 1;
                                $rowData["presentCount"] = 0.5;
                            } elseif ($statusData->m_id == 319) { // Holiday Present
                                $rowData["holidayPresentCount"] = 1;
                            } elseif ($statusData->m_id == 320) { // Week Off Present
                                $rowData["weekOffPresentCount"] = 1;
                                $rowData['weekOffCount'] = $isUnpaid ? 0 : 1;
                            }
                        }
                    } else {
                        // Default to present if no specific status but has attendance log
                        $rowData['status_id'] = $presentStatus->m_id;
                        $rowData['status'] = $presentStatus->m_name;
                        $rowData['status_code'] = $presentStatus->m_type;
                        $rowData['statusColor'] = $presentStatus->color;
                        $rowData['presentCount'] = 1;
                    }

                    if ($attendance_log->al_is_absent == 1) {

                        $rowData['status_id']   = $absentStatus->m_id;
                        $rowData['status']      = $absentStatus->m_name;
                        $rowData['status_code'] = $absentStatus->m_type;
                        $rowData['statusColor'] = $absentStatus->color;

                        $rowData['absentCount'] = 1;
                        $rowData['presentCount'] = 0;
                    }

                    if ($attendance_log->al_is_overtime == 1) {
                        $rowData['overtimeCount'] = 1;
                        $rowData['OT'] = $attendance_log->al_overtime_hours ? (string)(round($attendance_log->al_overtime_hours, 2)) : null;
                        // $rowData['OT'] = $attendance_log->al_overtime_hours ? '1' : '0';
                    }

                    if ($attendance_log->al_is_late == 1) {
                        $rowData['lateCount'] = 1;
                    }

                    if ($attendance_log->al_is_early_exit == 1) {
                        $rowData['earlyExitCount'] = 1;
                    }
                }

                // if (isset($attendance_log) && !empty($attendance_log) && !isset($is_found)) {

                //     $is_found = 1;
                //     $logCheckIn  = $attendance_log->al_check_in_time ?? null;
                //     $logCheckOut = $attendance_log->al_check_out_time ?? null;

                //     $recCheckIn  = $attendance_record_exit->atd_check_in_time ?? null;
                //     $recCheckOut = $attendance_record_exit->atd_check_out_time ?? null;

                //     $checkIn  = $logCheckIn  ?: $recCheckIn;
                //     $checkOut = $logCheckOut ?: $recCheckOut;
                //     $workingMinutes = 0;
                //     if ($checkIn && $checkOut) {
                //         $workingMinutes = Carbon::parse($checkIn)->diffInMinutes(Carbon::parse($checkOut));
                //     }
                //     $workingHours = $workingMinutes / 60;

                //     $rowData['checkInTime']  = $checkIn ? Carbon::parse($checkIn)->format('H:i') : '';
                //     $rowData['checkOutTime'] = $checkOut ? Carbon::parse($checkOut)->format('H:i') : '';
                //     $rowData['workingHour']  = $workingHours ? number_format($workingHours, 2, '.', '') : '';

                //     // ================================
                //     // ✅ Other Fields
                //     // ================================
                //     $rowData['attendance_remark'] = $attendance_log->al_reason ?? ($attendance_record_exit?->atd_remark ?? null);
                //     $rowData['mark_as_absent']    = $attendance_log->al_is_absent ?? ($attendance_record_exit?->atd_is_absent ?? null);

                //     $rowData['late'] = ($attendance_log->al_is_late && $attendance_log->al_late_duration)
                //         ? number_format($attendance_log->al_late_duration, 2, '.', '')
                //         : ($attendance_record_exit?->atd_late_duration ?? null);

                //     $rowData['earlyExit'] = ($attendance_log->al_is_early_exit && $attendance_log->al_early_exit_duration)
                //         ? number_format($attendance_log->al_early_exit_duration, 2, '.', '')
                //         : ($attendance_record_exit?->atd_early_exit_duration ?? null);

                //     $rowData['updatedBy'] = optional($attendance_log->fh_employee_data)->emp_full_name ?? '-';

                //     // ================================
                //     // ✅ 🔥 DYNAMIC SHIFT LOGIC
                //     // ================================
                //     $resolvedShift = ShiftResolver::resolveEmployeeShift($employee, $date);
                //     $shift = $resolvedShift->shift ?? PolicyShiftTiming::find($employee->emp_shift_type_id);

                //     $pst_start_time = Carbon::parse($shift->pst_start_time ?? '09:00:00');
                //     $pst_end_time   = Carbon::parse($shift->pst_end_time ?? '18:00:00');
                //     $pst_min_work   = Carbon::parse($shift->pst_min_work_hour ?? $shift->pst_end_time);

                //     $fullDayMinutes = $pst_start_time->diffInMinutes($pst_end_time);
                //     $minFullDayMinutes = $pst_start_time->diffInMinutes($pst_min_work);

                //     if (!$minFullDayMinutes || $minFullDayMinutes <= 0) {
                //         $minFullDayMinutes = $fullDayMinutes;
                //     }

                //     $minHalfDayMinutes = $minFullDayMinutes / 2;

                //     $minFullDay = $minFullDayMinutes / 60;
                //     $minHalfDay = $minHalfDayMinutes / 60;

                //     // ================================
                //     // ✅ Holiday / WeekOff Check
                //     // ================================
                //     $isHoliday = !empty($holiday_record_exit);
                //     $isWeekOff = in_array($date, $weekOfDates);

                //     // ================================
                //     // ✅ 🔥 STATUS LOGIC (FINAL)
                //     // ================================
                //     $hasFullLogData = $logCheckIn && $logCheckOut;

                //     if ($attendance_log->al_is_absent == 1) {

                //         // Force Absent
                //         $rowData['status_id']   = $absentStatus->m_id;
                //         $rowData['status']      = $absentStatus->m_name;
                //         $rowData['status_code'] = $absentStatus->m_type;
                //         $rowData['statusColor'] = $absentStatus->color;

                //         $rowData['absentCount'] = 1;
                //         $rowData['presentCount'] = 0;

                //     } elseif ($hasFullLogData && $attendance_log->al_attendance_status) {

                //         // Use Log Status only if full data
                //         $statusData = $getMaster($attendance_log->al_attendance_status);

                //         if ($statusData) {
                //             $rowData['status_id']   = $statusData->m_id;
                //             $rowData['status']      = $statusData->m_name;
                //             $rowData['status_code'] = $statusData->m_type;
                //             $rowData['statusColor'] = $statusData->color;

                //             if ($statusData->m_id == 251) {
                //                 $rowData['presentCount'] = 1;
                //             } elseif ($statusData->m_id == 252) {
                //                 $rowData['presentCount'] = 0.5;
                //                 $rowData['halfDayCount'] = 1;
                //             } elseif ($statusData->m_id == 319) {
                //                 $rowData['holidayPresentCount'] = 1;
                //             } elseif ($statusData->m_id == 320) {
                //                 $rowData['weekOffPresentCount'] = 1;
                //                 $rowData['weekOffCount'] = $isUnpaid ? 0 : 1;
                //             }
                //         }

                //     } else {

                //         // Derive from working hours
                //         if ($workingHours >= $minFullDay) {

                //             if ($isHoliday) {
                //                 $rowData['status_id']   = $hoPresentStatus->m_id;
                //                 $rowData['status']      = $hoPresentStatus->m_name;
                //                 $rowData['status_code'] = $hoPresentStatus->m_type;
                //                 $rowData['statusColor'] = $hoPresentStatus->color;
                //                 $rowData['holidayPresentCount'] = 1;
                //             } elseif ($isWeekOff) {
                //                 $rowData['status_id']   = $woPresentStatus->m_id;
                //                 $rowData['status']      = $woPresentStatus->m_name;
                //                 $rowData['status_code'] = $woPresentStatus->m_type;
                //                 $rowData['statusColor'] = $woPresentStatus->color;
                //                 $rowData['weekOffPresentCount'] = 1;
                //                 $rowData['weekOffCount'] = $isUnpaid ? 0 : 1;
                //             } else {
                //                 $rowData['status_id']   = $presentStatus->m_id;
                //                 $rowData['status']      = $presentStatus->m_name;
                //                 $rowData['status_code'] = $presentStatus->m_type;
                //                 $rowData['statusColor'] = $presentStatus->color;
                //                 $rowData['presentCount'] = 1;
                //             }

                //         } elseif ($workingHours >= $minHalfDay) {
                //             $rowData['status_id']   = $halfDayStatus->m_id;
                //             $rowData['status']      = $halfDayStatus->m_name;
                //             $rowData['status_code'] = $halfDayStatus->m_type;
                //             $rowData['statusColor'] = $halfDayStatus->color;
                //             $rowData['presentCount'] = 0.5;
                //             $rowData['halfDayCount'] = 1;
                //         } else {
                //             $rowData['status_id']   = $absentStatus->m_id;
                //             $rowData['status']      = $absentStatus->m_name;
                //             $rowData['status_code'] = $absentStatus->m_type;
                //             $rowData['statusColor'] = $absentStatus->color;
                //             $rowData['absentCount'] = 1;
                //         }
                //     }

                //     // ================================
                //     // ✅ Extra Flags
                //     // ================================
                //     if ($attendance_log->al_is_overtime == 1) {
                //         $rowData['overtimeCount'] = 1;
                //         $rowData['OT'] = $attendance_log->al_overtime_hours
                //             ? (string)(round($attendance_log->al_overtime_hours, 2))
                //             : null;
                //     }

                //     if ($attendance_log->al_is_late == 1) {
                //         $rowData['lateCount'] = 1;
                //     }

                //     if ($attendance_log->al_is_early_exit == 1) {
                //         $rowData['earlyExitCount'] = 1;
                //     }
                // }

                // Priority 4: Check AttendanceRecord if no AttendanceException or AttendanceLog found
                if (!isset($is_found) && $attendance_record_exit) {
                    $atdStatusData = $attendance_record_exit->fh_attendance_status;
                    $atdStatusID = $attendance_record_exit->atd_attendance_status;
                    $rowData['status_id'] = $atdStatusData->m_id;
                    $rowData['status_code'] = $atdStatusData->m_type;
                    $rowData['status'] = $atdStatusData->m_name;
                    $rowData['statusColor'] = json_decode($atdStatusData->m_other, true)['color'];

                    if ($atdStatusID === 251) {
                        $rowData['presentCount'] = 1; //Present
                    } else if ($atdStatusID == 320) {
                        $rowData["weekOffPresentCount"] = 1;
                        $rowData['weekOffCount'] = $isUnpaid ? 0 : 1;
                    } else if ($atdStatusID == 319) {
                        $rowData["holidayPresentCount"] = 1;
                    } else if ($atdStatusID == 252) {
                        $rowData["presentCount"] = 0.5;
                    } else if ($atdStatusID == 203) {
                        $rowData["absentCount"] = 1;
                    } else if ($atdStatusID === 591) {
                        $rowData["ODCount"] = 1;
                    }
                    $is_found = 1;

                    if ($attendance_record_exit->atd_is_overtime == 1) {
                        $rowData['overtimeCount'] = 1;
                    }

                    if ($attendance_record_exit->atd_is_late == 1) {
                        $rowData['lateCount'] = 1;
                    }

                    if ($attendance_record_exit->atd_is_early_exit == 1) {
                        $rowData['earlyExitCount'] = 1;
                    }

                    
                    $rowData['checkingMethodId'] = $attendance_record_exit->atd_checkin_method_id;
                    $rowData['checkInTime'] = $attendance_record_exit->atd_check_in_time ? Carbon::parse($attendance_record_exit->atd_check_in_time)->format('H:i') : '';
                    $rowData['checkOutTime'] = $attendance_record_exit->atd_check_out_time ? Carbon::parse($attendance_record_exit->atd_check_out_time)->format('H:i') :  '';
                    $rowData['workingHour'] = $attendance_record_exit->atd_total_worked_hours ? number_format($attendance_record_exit->atd_total_worked_hours, 2) : '';
                    $rowData['earlyExit'] = ($attendance_record_exit->atd_is_early_exit && $attendance_record_exit->atd_early_exit_duration) ? number_format($attendance_record_exit->atd_early_exit_duration, 2, '.', '') :  null;
                    $rowData['late'] = ($attendance_record_exit->atd_is_late && $attendance_record_exit->atd_late_duration) ? number_format($attendance_record_exit->atd_late_duration, 2, '.', '') :  null;
                    $rowData['OT'] = ($attendance_record_exit->atd_is_overtime && $attendance_record_exit->atd_overtime_hours) ? (string)(round($attendance_record_exit->atd_overtime_hours, 2)) :  null;
                    $rowData['attendance_remark'] = $attendance_record_exit->atd_remark  ? $attendance_record_exit->atd_remark :  '';
                    $rowData['checkInLocation'] = $attendance_record_exit && $attendance_record_exit->atd_punchin_location ? $attendance_record_exit->atd_punchin_location : '--';
                    $rowData['checkOutLocation'] =  $attendance_record_exit && $attendance_record_exit->atd_punchout_location ? $attendance_record_exit->atd_punchout_location : '--';
                    $rowData['checkInPhoto'] = $attendance_record_exit && $attendance_record_exit->atd_punchin_photo ? json_decode($attendance_record_exit->atd_punchin_photo) : [];
                    $rowData['checkOutPhoto'] =  $attendance_record_exit && $attendance_record_exit->atd_punchout_photo ? json_decode($attendance_record_exit->atd_punchout_photo) : [];
                    $rowData['atd_segments'] =  $attendance_record_exit && $attendance_record_exit->atd_segments ? [json_decode($attendance_record_exit->atd_segments)] : [];
                    $rowData['updatedBy'] = optional($attendance_record_exit->updated_by)->emp_full_name ?? '-';
                }

                $half_day_present = ($attendance_log && (int)$attendance_log->al_attendance_status === $halfDayStatus->m_id) || ($attendance_record_exit && (int)$attendance_record_exit->atd_attendance_status === $halfDayStatus->m_id);
                
                $half_day_absent = ($attendance_log && (int)$attendance_log->al_attendance_status === $absentStatus->m_id) || ($attendance_record_exit && (int)$attendance_record_exit->atd_attendance_status === $absentStatus->m_id);

                if (is_null($is_found) || $half_day_present || $half_day_absent) {
                    if ($leave_record_exit && $leave_record_exit->count() == 1) {
                        $lvr_exit = $leave_record_exit->first();
                        $lvrCatTypeId = $lvr_exit?->fh_leave_cat_type;
                        $is_unpaid = $lvrCatTypeId->m_id == $uplStatus->m_id;
                        $is_approved = $lvr_exit->lvr_status != $rejectStatus->m_id && $lvr_exit->lvr_stage_completed;
                        $is_half_day_leave = optional($lvr_exit->fh_leave_day_type)->m_id != $fullDayLeave->m_id;
                        $segment_id = $lvr_exit->fh_leave_day_segment->m_id ?? null;

                        if (in_array($lvrCatTypeId->m_id, $leave_category_ids) && $is_approved) {
                            $is_found = 1;

                            // Case: Half-day present + Half-day leave
                            if ($half_day_present && $is_half_day_leave) {
                                $hdPreStatus = $attendance_log?->fh_attendance_status ?? $attendance_record_exit?->fh_attendance_status;
                                $hdPreColor = json_decode($hdPreStatus->m_other, true)['color'];
                                $lvrCatTypeColor = json_decode($lvrCatTypeId->m_other, true)['color'];

                                if ($segment_id == $secondHalfLeave->m_id) {
                                    $rowData['status_id'] = "{$hdPreStatus->m_id}/{$lvrCatTypeId->m_id}";
                                    $rowData['status'] = "{$hdPreStatus->m_name}/{$lvrCatTypeId->m_name}";
                                    $rowData['status_code'] = "{$hdPreStatus->m_type}/{$lvrCatTypeId->m_type}";
                                    $rowData['statusColor'] = "{$hdPreColor}/{$lvrCatTypeColor}";
                                } else {
                                    $rowData['status_id'] = "{$lvrCatTypeId->m_id}/{$hdPreStatus->m_id}";
                                    $rowData['status'] = "{$lvrCatTypeId->m_name}/{$hdPreStatus->m_name}";
                                    $rowData['status_code'] = "{$lvrCatTypeId->m_type}/{$hdPreStatus->m_type}";
                                    $rowData['statusColor'] = "{$lvrCatTypeColor}/{$hdPreColor}";
                                }

                                // FIXED COUNTS - Half day present + Half day leave
                                $rowData['presentCount'] = 0.5;
                                $rowData['UPL']        = $is_unpaid ? 0.5 : 0;
                                $rowData['leaveCount'] = $is_unpaid ? 0   : 0.5;
                                $rowData['approvedLeaveCount'] = $is_unpaid ? 0 : 0.5;
                            }

                            // NEW CASE: Half-day absent + Half-day leave
                            elseif ($half_day_absent && $is_half_day_leave) {
                                $hdAbsStatus = $absentStatus; // ABS status object
                                $hdAbsColor = $hdAbsStatus->color;
                                $lvrCatTypeColor = json_decode($lvrCatTypeId->m_other, true)['color'];

                                if ($segment_id == $secondHalfLeave->m_id) {
                                    // ABS/CL format (if leave is second half)
                                    $rowData['status_id'] = "{$hdAbsStatus->m_id}/{$lvrCatTypeId->m_id}";
                                    $rowData['status'] = "{$hdAbsStatus->m_name}/{$lvrCatTypeId->m_name}";
                                    $rowData['status_code'] = "{$hdAbsStatus->m_type}/{$lvrCatTypeId->m_type}";
                                    $rowData['statusColor'] = "{$hdAbsColor}/{$lvrCatTypeColor}";
                                } else {
                                    // CL/ABS format (if leave is first half)
                                    $rowData['status_id'] = "{$lvrCatTypeId->m_id}/{$hdAbsStatus->m_id}";
                                    $rowData['status'] = "{$lvrCatTypeId->m_name}/{$hdAbsStatus->m_name}";
                                    $rowData['status_code'] = "{$lvrCatTypeId->m_type}/{$hdAbsStatus->m_type}";
                                    $rowData['statusColor'] = "{$lvrCatTypeColor}/{$hdAbsColor}";
                                }

                                // Counts for half-day absent + half-day leave
                                $rowData['presentCount'] = 0;
                                $rowData['absentCount'] = 0.5; // Half day absent
                                $rowData['UPL'] = $is_unpaid ? 0.5 : 0;
                                $rowData['leaveCount'] = $is_unpaid ? 0 : 0.5;
                                $rowData['approvedLeaveCount'] = $is_unpaid ? 0 : 0.5;
                            }

                            // Case: Only single full/half leave (no half-day present)
                            elseif (!$half_day_present && !$half_day_absent) {
                                // dd($lvrCatTypeId);
                                $rowData['status_id'] = $lvrCatTypeId->m_id;
                                $rowData['status'] = $lvrCatTypeId->m_name;
                                $rowData['status_code'] = $lvrCatTypeId->m_type;
                                $rowData['statusColor'] = json_decode($lvrCatTypeId->m_other, true)['color'];

                                $rowData['presentCount'] = 0;
                                $rowData['UPL'] = $is_unpaid ? ($is_half_day_leave ? 0.5 : 1) : 0;
                                $rowData['leaveCount'] = $is_unpaid ? 0 : ($is_half_day_leave ? 0.5 : 1);
                                $rowData['approvedLeaveCount'] = $is_unpaid ? 0 : ($is_half_day_leave ? 0.5 : 1);

                                if ($rowData['leaveCount'] == 0.5) {
                                    $rowData['status'] = 'HD_'.$lvrCatTypeId->m_name;
                                }
                            }
                        }
                    }

                    // Case: Two half-day leaves like CL/SL or CL/UPL etc.
                    elseif ($leave_record_exit && $leave_record_exit->count() == 2) {
                        $leaves = $leave_record_exit->sortBy(function ($l) {
                            return optional($l->fh_leave_day_segment)->m_id ?? 0;
                        })->values();

                        $l1 = $leaves[0];
                        $l2 = $leaves[1];

                        $is_approved1 = $l1->lvr_status != $rejectStatus->m_id && $l1->lvr_stage_completed;
                        $is_approved2 = $l2->lvr_status != $rejectStatus->m_id && $l2->lvr_stage_completed;

                        if ($is_approved1 || $is_approved2) {
                            $l1CatType = $l1?->fh_leave_cat_type;
                            $l2CatType = $l2?->fh_leave_cat_type;
                            $rowData['status_id'] = "{$l1CatType->m_id}/{$l2CatType->m_id}";
                            $rowData['status'] = "{$l1CatType->m_name}/{$l2CatType->m_name}";
                            $rowData['status_code']  = "{$l1CatType->m_type}/{$l2CatType->m_type}";
                            $rowData['statusColor'] = json_decode($l1CatType->m_other, true)['color'] . '/' . json_decode($l2CatType->m_other, true)['color'];
                            $is_found = 1;

                            // FIXED COUNTS - Two half day leaves
                            $rowData['presentCount'] = 0; // No present count for leaves
                            $rowData['leaveCount'] = 0;
                            $rowData['UPL'] = 0;
                            $rowData['approvedLeaveCount'] = 0;

                            // Count first half day leave
                            if ($is_approved1) {
                                $isUpl = $l1CatType->m_id === $uplStatus->m_id;
                                $rowData['UPL']                += $isUpl ? 0.5 : 0;
                                $rowData['leaveCount']         += $isUpl ? 0   : 0.5;
                                $rowData['approvedLeaveCount'] += $isUpl ? 0   : 0.5;
                            }

                            // Count second half day leave  
                            if ($is_approved2) {
                                $isUpl2 = $l2CatType->m_id === $uplStatus->m_id;
                                $rowData['UPL']                += $isUpl2 ? 0.5 : 0;
                                $rowData['leaveCount']         += $isUpl2 ? 0   : 0.5;
                                $rowData['approvedLeaveCount'] += $isUpl2 ? 0   : 0.5;
                            }

                            $rowData['isApproved'] = $is_approved1 && $is_approved2;
                        }
                    }
                }

                if (is_null($is_found)) {
                    if ($holiday_record_exit) {
                        $hoPrevDate = Carbon::parse($date)->subDay()->toDateString();
                        $hoNextDate = Carbon::parse($date)->addDay()->toDateString();
                        $prevAbsent = self::isAbsentCheck($hoPrevDate, $absentStatus->m_id, $attendanceRecords, $attendanceLogs);
                        $nextAbsent = self::isAbsentCheck($hoNextDate, $absentStatus->m_id, $attendanceRecords, $attendanceLogs);
                        if ($prevAbsent && $nextAbsent && ($isAbsCheck == 1)) {
                            $rowData['absentCount'] = 1;
                            $rowData['status']       = $absentStatus->m_name;
                            $rowData['status_id']    = $absentStatus->m_id;
                            $rowData['status_code']  = $absentStatus->m_type;
                            $rowData['statusColor']  = $absentStatus->color;
                        } else {
                            $rowData['holidayCount'] = 1;
                            $rowData['status']       = $holidayStatus->m_name;
                            $rowData['status_id']    = $holidayStatus->m_id;
                            $rowData['status_code']  = $holidayStatus->m_type;
                            $rowData['statusColor']  = $holidayStatus->color;
                        }
                        $rowData['checkInTime']  = '';
                        $rowData['checkOutTime'] = '';
                        $rowData['workingHour']  = '';
                        $is_found = 1;
                    }
                }

                if (is_null($is_found)) {
                    if (in_array($date, $weekOfDates)) {
                        if (is_array($unpaidWeekOffDays) && !is_null($unpaidWeekOffDays)) {
                            $isUnpaid = in_array($currentWeekDayMasterId, $unpaidWeekOffDays);
                        } else {
                            $isUnpaid = false;
                        }
                        $woPrevDate = Carbon::parse($date)->subDay()->toDateString();
                        $woNextDate = Carbon::parse($date)->addDay()->toDateString();
                        $prevAbsent = self::isAbsentCheck($woPrevDate, $absentStatus->m_id, $attendanceRecords, $attendanceLogs);
                        $nextAbsent = self::isAbsentCheck($woNextDate, $absentStatus->m_id, $attendanceRecords, $attendanceLogs);
                        if ($prevAbsent && $nextAbsent && ($isAbsCheck == 1)) {
                            $rowData['absentCount'] = 1;
                            $rowData['status']       = $absentStatus->m_name;
                            $rowData['status_id']    = $absentStatus->m_id;
                            $rowData['status_code']  = $absentStatus->m_type;
                            $rowData['statusColor']  = $absentStatus->color;
                        } else {
                            $rowData['weekOffCount'] = $isUnpaid ? 0 : 1;
                            $rowData['status']       = $weekOffStatus->m_name;
                            $rowData['status_id']    = $weekOffStatus->m_id;
                            $rowData['status_code']  = $weekOffStatus->m_type;
                            $rowData['statusColor']  = $weekOffStatus->color;
                        }
                        $rowData['checkInTime']  = '';
                        $rowData['checkOutTime'] = '';
                        $rowData['workingHour']  = '';
                        $is_found = 1;
                    }
                }

                // Check for approved leaves (including UPL) for all dates
                if (is_null($is_found)) {
                    // if ($leave_record_exit && $leave_record_exit->count() >= 1) {
                    //     $lvr_exit = $leave_record_exit->first();
                    //     $lvrCatTypeId = $lvr_exit?->fh_leave_cat_type;
                    //     $is_unpaid = $lvrCatTypeId->m_id == $uplStatus->m_id;
                    //     $is_approved = $lvr_exit->lvr_status != $rejectStatus->m_id && $lvr_exit->lvr_stage_completed;
                    //     $is_half_day_leave = optional($lvr_exit->fh_leave_day_type)->m_id != $fullDayLeave->m_id;

                    //     if ($is_approved) {
                    //         $rowData['status'] = $lvrCatTypeId->m_name;
                    //         $rowData['status_id'] = $lvrCatTypeId->m_id;
                    //         $rowData['status_code'] = $lvrCatTypeId->m_type;
                    //         $rowData['statusColor'] = json_decode($lvrCatTypeId->m_other, true)['color'];
                    //         $rowData['checkInTime'] = '';
                    //         $rowData['checkOutTime'] = '';
                    //         $rowData['workingHour'] = '';
                    //         $is_found = 1;

                    //         // Set counts based on leave type
                    //         $rowData['UPL']                = $is_unpaid ? ($is_half_day_leave ? 0.5 : 1) : 0;
                    //         $rowData['leaveCount']         = $is_unpaid ? 0 : ($is_half_day_leave ? 0.5 : 1);
                    //         $rowData['approvedLeaveCount'] = $is_unpaid ? 0 : ($is_half_day_leave ? 0.5 : 1);
                    //     }
                    // }

                    // Only mark as absent for past dates with no leave or other status
                    if (is_null($is_found) && Carbon::parse($date)->isPast()) {
                        $rowData['absentCount'] = 1;
                        $rowData['status'] = $absentStatus->m_name;
                        $rowData['status_id'] = $absentStatus->m_id;
                        $rowData['status_code'] = $absentStatus->m_type;
                        $rowData['statusColor'] = $absentStatus->color;
                        $rowData['checkInTime'] = '';
                        $rowData['checkOutTime'] = '';
                        $rowData['workingHour'] = '';
                    }
                }

                if ($rowData['status_id']) {
                    $rowData['status_id'] = (string)$rowData['status_id'];
                }
                $rowData['date'] = $date;
                $attendanceData[] = $rowData;
            }
        }

        $totalOTMinutes = 0;
        foreach ($attendanceData as $key => $value) {
            // $totalOTHrs += $value['OT'];
            if (!empty($value['OT'])) {
                $hours = floor($value['OT']);
                $minutes = ($value['OT'] - $hours) * 100;
                $totalOTMinutes += ($hours * 60) + $minutes;
            }
            // 1. Present days
            $salaryDay += $value['presentCount'] ?? 0;

            // 2. Handle Half Day cases
            if (($value['halfDayCount'] ?? 0) == 1 && ($value['approvedLeaveCount'] ?? 0) == 0.5) {
                // Half-day + half leave = 1 full day
                $salaryDay += 1;
            } elseif (($value['halfDayCount'] ?? 0) == 1) {
                // Only half day present, no leave matched
                $salaryDay += 0.5;
            } else {
                // 3. Approved Leave (if not already paired with half-day)
                $salaryDay += $value['approvedLeaveCount'] ?? 0;
            }

            // 4. Week Off & Holiday
            $salaryDay += $value['weekOffCount'] ?? 0;
            $salaryDay += $value['holidayCount'] ?? 0;
        }

        $lastIndex = count($attendanceData) - 1;
        $hours = floor($totalOTMinutes / 60);
        $minutes = $totalOTMinutes % 60;
        $totalOTHrs = sprintf('%d hrs %d min', $hours, $minutes);
        if (isset($attendanceData[$lastIndex])) {
            $attendanceData[$lastIndex]['totalSalariedDays'] = $salaryDay;
            $attendanceData[$lastIndex]['totalOTHrs'] = $totalOTHrs;
        }

        return $attendanceData;
    }

    public static function isAbsentCheck(
        string $date,
        int $absentStatusId,
        $attendanceRecords,
        $attendanceLogs
    ): bool {

        // Step 1: Check AttendanceRecord
        if (isset($attendanceRecords[$date])) {
            return $attendanceRecords[$date]->atd_attendance_status == $absentStatusId;
        }

        // Step 2: Check AttendanceLog
        if (isset($attendanceLogs[$date])) {
            return $attendanceLogs[$date]->al_attendance_status == $absentStatusId;
        }

        // Step 3: No record = absent
        return true;
    }

        public static function getWeekOffAttendanceSummary($emp, array $attendanceData, int $month, int $year)
    {
        $policy = $emp->fh_week_off_policy2;

        if (!$policy) {
            return ['weekOffSummary' => []];
        }

        // Decode fields safely
        $dayIds = json_decode($policy->pwo_day_ids, true) ?: [];
        $recurrenceDayIds = json_decode($policy->pwo_recurrence_day_ids, true) ?: [];
        $unpaidIds = json_decode($policy->pwo_is_unpaid, true) ?: [];

        // Map all master IDs to names
        $allIds = array_unique(array_merge($dayIds, array_keys($recurrenceDayIds), ...array_values($recurrenceDayIds)));
        $masterNames = MasterTable::whereIn('m_id', $allIds)->pluck('m_name', 'm_id')->toArray();

        // Prepare shift details
        $shift = $emp->fh_shift_type;
        $shiftStartTime = Carbon::parse($shift->pst_start_time);
        $shiftEndTime   = Carbon::parse($shift->pst_end_time);
        $graceMins      = $shift->pst_allow_grace_time ? $shift->pst_grace_time : 0;
        $shiftStartWithGrace = $shiftStartTime->copy()->subMinutes($graceMins);
        $dailyWorkingMinutes = $shiftStartWithGrace->diffInMinutes($shiftEndTime);
        $minWorkHrs = $shift->pst_min_work_hour
            ? $shiftStartTime->diffInMinutes(Carbon::parse($shift->pst_min_work_hour))
            : $dailyWorkingMinutes;
        $halfDayThreshold = $dailyWorkingMinutes / 2;

        // Date range for the month
        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth   = $startOfMonth->copy()->endOfMonth();

        $summary = [];

        // Loop through each configured week-off day
        foreach ($recurrenceDayIds as $dayId => $weekIds) {
            $dayName = $masterNames[$dayId] ?? null;
            if (!$dayName) continue;

            $isUnpaid = in_array($dayId, $unpaidIds);

            $fullDayPresent = 0;
            $halfDayPresent = 0;
            $dates = [];

            // Loop all weeks for that day
            foreach ($weekIds as $weekId) {
                $weekName = $masterNames[$weekId] ?? null;
                if (!$weekName) continue;

                // Derive week number from name, e.g. "1st Week" → 1
                $weekNum = (int) filter_var($weekName, FILTER_SANITIZE_NUMBER_INT);
                if ($weekNum <= 0) continue;

                // Get all days of month for this weekday
                $date = $startOfMonth->copy()->startOfMonth()->next($dayName);
                while ($date->month == $month) {
                    $weekOfMonth = ceil($date->day / 7);
                    if ($weekOfMonth == $weekNum) {
                        $currentDate = $date->toDateString();
                        $record = collect($attendanceData)->firstWhere('date', $currentDate);

                        if (
                            $record && !empty($record['checkInTime']) && !empty($record['checkOutTime'])
                            && $record['checkInTime'] != '-' && $record['checkOutTime'] != '-'
                        ) {

                            $checkIn  = Carbon::parse($currentDate . ' ' . $record['checkInTime']);
                            $checkOut = Carbon::parse($currentDate . ' ' . $record['checkOutTime']);
                            $workedMinutes = $checkIn->diffInMinutes($checkOut);

                            if ($workedMinutes >= $dailyWorkingMinutes || $workedMinutes >= $minWorkHrs) {
                                $statusId = 251; // Full day present
                                $fullDayPresent++;
                            } elseif ($workedMinutes >= $halfDayThreshold) {
                                $statusId = 252; // Half day present
                                $halfDayPresent++;
                            } else {
                                $statusId = 203; // Absent
                            }
                        } else {
                            $statusId = 203;
                        }

                        $dates[] = [
                            'date' => $currentDate,
                            'status_id' => $statusId,
                        ];
                    }
                    $date->addWeek();
                }
            }

            $summary[] = [
                'day_name' => $dayName,
                'is_unpaid' => $isUnpaid,
                'full_day_present_count' => $fullDayPresent,
                'half_day_present_count' => $halfDayPresent,
                'dates' => $dates,
            ];
        }

        return ['weekOffSummary' => $summary];
    }

    /**
     * Generate attendance for an arbitrary date range (inclusive)
     * Returns array of per-day rows similar to your original function
     */
    public static function getAttendanceDetailsForRange($employee, string $fromDate, string $toDate, $holiday_record_exits, array $weekOfDates)
    {
        // Normalize dates
        $startDate = Carbon::parse($fromDate)->startOfDay();
        $endDate = Carbon::parse($toDate)->endOfDay();
        $period = Carbon::parse($startDate)->toPeriod($endDate);

        // Master status ids used across logic — preload once
        $masterIds = [203, 215, 228, 251, 252, 319, 320, 321, 322, 139, 140, 141, 156, 157, 170, 171, 172, 174, 175, 192, 200, 412, 451, 452];
        $statuses = MasterTable::whereIn('m_id', $masterIds)->get()->keyBy('m_id');

        $getStatus = function($id) use ($statuses) {
            return $statuses[$id] ?? null;
        };

        // Preload leave category ids (LEAVE_CATEGORY group)
        $leaveCategoryIds = MasterTable::where('m_group', 'LEAVE_CATEGORY')->pluck('m_id')->toArray();

        // Preload attendance-related records for the range and key by date string
        $attendanceRecords = $employee->attendance_record()
            ->whereBetween('atd_date', [$startDate, $endDate])
            ->get()
            ->keyBy(fn($r) => Carbon::parse($r->atd_date)->toDateString());

        $attendanceLogs = AttendanceLog::where('al_emp_id', $employee->emp_id)
            ->whereBetween('al_date', [$startDate, $endDate])
            ->get()
            ->keyBy(fn($r) => Carbon::parse($r->al_date)->toDateString());

        $missedPunches = AttendanceException::where('ae_b_id', $employee->emp_b_id)
            ->where('ae_emp_id', $employee->emp_id)
            ->whereBetween('ae_date', [$startDate, $endDate])
            ->where('ae_status', '!=', 170)
            ->get()
            ->keyBy(fn($r) => Carbon::parse($r->ae_date)->toDateString());

        // Preload leave requests intersecting the range (we will map per-date)
        $leaveRequests = $employee->leave_requests()
            ->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('lvr_start_date', [$startDate, $endDate])
                  ->orWhereBetween('lvr_end_date', [$startDate, $endDate])
                  ->orWhere(function($q2) use ($startDate, $endDate) {
                      $q2->where('lvr_start_date', '<', $startDate)
                         ->where('lvr_end_date', '>', $endDate);
                  });
            })
            ->where('lvr_status', '!=', 170)
            ->get();

        // Map leaves by date (only within requested range)
        $leavesByDate = [];
        foreach ($leaveRequests as $l) {
            $ls = Carbon::parse($l->lvr_start_date)->startOfDay();
            $le = Carbon::parse($l->lvr_end_date)->endOfDay();
            $from = $ls->gt($startDate) ? $ls : $startDate;
            $to = $le->lt($endDate) ? $le : $endDate;
            for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
                $ds = $d->toDateString();
                $leavesByDate[$ds][] = $l;
            }
        }

        // Build a simple list of all date strings for sandwich computation
        $allDates = [];
        foreach ($period as $d) $allDates[] = $d->toDateString();

        // Sandwich rule: if HO/WO is between two ABS/Approved-absent-like days, convert to ABS
        // We'll compute a map of sandwich-applied dates -> status id (203)
        $sandwichApplied = [];

        // Helper to check if a date is 'absent-like' (no present/leave/holiday/weekoff and is past)
        $isAbsentLike = function($dateStr) use ($attendanceRecords, $attendanceLogs, $leavesByDate, $holiday_record_exits, $weekOfDates, $employee) {
            // Skip if before joining
            if ($employee->emp_date_of_joining && Carbon::parse($dateStr)->lt(Carbon::parse($employee->emp_date_of_joining))) return false;
            // if attendance record/log exists -> not absent
            if (isset($attendanceRecords[$dateStr]) || isset($attendanceLogs[$dateStr])) return false;
            // if approved leave exists -> not absent
            $leaves = $leavesByDate[$dateStr] ?? [];
            foreach ($leaves as $lv) {
                if ($lv->lvr_status != 170 && ($lv->lvr_stage_completed ?? 0)) return false;
            }
            // if holiday or weekoff -> not absent
            if (isset($holiday_record_exits[$dateStr]) || in_array($dateStr, $weekOfDates)) return false;
            // Only past dates count as absent
            if (!Carbon::parse($dateStr)->isPast()) return false;
            return true;
        };

        // Compute sandwich: for each date that is holiday or weekoff, check prev and next are absent-like
        for ($i = 0; $i < count($allDates); $i++) {
            $ds = $allDates[$i];
            $isHOorWO = isset($holiday_record_exits[$ds]) || in_array($ds, $weekOfDates);
            if (!$isHOorWO) continue;
            $prev = $allDates[$i-1] ?? null;
            $next = $allDates[$i+1] ?? null;
            if ($prev && $next) {
                if ($isAbsentLike($prev) && $isAbsentLike($next)) {
                    $sandwichApplied[$ds] = 203; // mark as absent
                }
            }
        }

        // Now iterate each day and build row using helpers (we will implement helpers below)
        $attendanceData = [];

        foreach ($period as $dateObj) {
            $date = $dateObj->toDateString();

            // Skip before joining or after leaving
            if ($employee->emp_date_of_joining && $dateObj->lt(Carbon::parse($employee->emp_date_of_joining))) continue;
            if ($employee->emp_last_working_day && $dateObj->gt(Carbon::parse($employee->emp_last_working_day))) continue;

            $attendance_record_exit = $attendanceRecords[$date] ?? null;
            $attendance_log = $attendanceLogs[$date] ?? null;
            $missedPunch = $missedPunches[$date] ?? null;
            $holiday_record_exit = $holiday_record_exits[$date] ?? null;
            $leave_record_exit = $leavesByDate[$date] ?? null;

            $row = self::buildDefaultRow($date);

            // Attach approval meta for each source if present
            if ($attendance_record_exit) {
                // attach punch lat/long & photos from attendance record
                $row['checkInLatitude'] = $attendance_record_exit->atd_latitude_punchin ?? null;
                $row['checkInLongitude'] = $attendance_record_exit->atd_longitude_punchin ?? null ?? null; // defensive
                $row['checkOutLatitude'] = $attendance_record_exit->atd_latitude_punchout ?? null ?? null;
                $row['checkOutLongitude'] = $attendance_record_exit->atd_longitude_punchout ?? null ?? null;
                $row['checkInPhoto'] = $attendance_record_exit->atd_punchin_photo ? json_decode($attendance_record_exit->atd_punchin_photo) : [];
                $row['checkOutPhoto'] = $attendance_record_exit->atd_punchout_photo ? json_decode($attendance_record_exit->atd_punchout_photo) : [];
            }

            if ($attendance_log) {
                $row['attendance_log_approval'] = [
                    'status' => $attendance_log->al_approval_status ?? null,
                    'stage_completed' => $attendance_log->al_stage_completed ?? null,
                ];
            }

            if ($missedPunch) {
                $row['attendance_exception_approval'] = [
                    'status' => $missedPunch->ae_status ?? null,
                    'stage_completed' => $missedPunch->ae_stage_completed ?? null,
                ];
            }

            if (!empty($leave_record_exit)) {
                // if multiple leaves, attach aggregated approval info
                $row['leave_approvals'] = array_map(fn($l) => [
                    'id' => $l->lvr_id ?? null,
                    'status' => $l->lvr_status ?? null,
                    'stage_completed' => $l->lvr_stage_completed ?? null,
                ], $leave_record_exit);
            }

            // Sandwich rule override
            if (isset($sandwichApplied[$date])) {
                $abs = $getStatus(203);
                if ($abs) {
                    $row['status'] = $abs->m_name;
                    $row['status_id'] = (string)$abs->m_id;
                    $row['status_code'] = $abs->m_type;
                    $row['statusColor'] = json_decode($abs->m_other, true)['color'] ?? null;
                    $row['absentCount'] = 1;
                    $row['checkInTime'] = '-';
                    $row['checkOutTime'] = '-';
                    $row['workingHour'] = '-';
                }
                $attendanceData[] = $row;
                continue; // sandwich applied — skip further processing for this date
            }

            // UPL check from attendance record
            $uplStatus = $getStatus(215);
            if ($attendance_record_exit && $attendance_record_exit->atd_attendance_status == 215) {
                if ($uplStatus) {
                    $row['status'] = $uplStatus->m_name;
                    $row['status_id'] = (string)$uplStatus->m_id;
                    $row['status_code'] = $uplStatus->m_type;
                    $row['statusColor'] = json_decode($uplStatus->m_other, true)['color'] ?? null;
                    $row['UPL'] = 1;
                }
                $attendanceData[] = $row;
                continue;
            }

            $is_found = null;

            // Priority 1: Missed Punch / AttendanceException (if approved)
            if ($missedPunch && ($missedPunch->ae_status == 157) && ($missedPunch->ae_stage_completed ?? 0) == 1) {
                $is_found = true;
                self::applyMissedPunchToRow($row, $missedPunch, $attendance_record_exit, $getStatus, $employee, $leave_record_exit);
            }

            // Priority 2: Attendance Log
            if (!$is_found && $attendance_log) {
                $is_found = true;
                self::applyAttendanceLogToRow($row, $attendance_log, $attendance_record_exit, $getStatus);
            }

            // Priority 3: Attendance Record
            if (!$is_found && $attendance_record_exit && $attendance_record_exit->fh_attendance_status) {
                $is_found = true;
                self::applyAttendanceRecordToRow($row, $attendance_record_exit, $getStatus);
            }

            // Leaves handling: if still not found or half-day present
            $half_day_present = ($attendance_log && $attendance_log->al_attendance_status == 252) || ($attendance_record_exit && $attendance_record_exit->atd_attendance_status == 252);

            if ((is_null($is_found) || $half_day_present) && !empty($leave_record_exit)) {
                // filter leave list to approved ones
                $approvedLeaves = array_filter($leave_record_exit, fn($l) => ($l->lvr_status != 170) && ($l->lvr_stage_completed ?? 0));
                if (!empty($approvedLeaves)) {
                    self::applyLeavesToRow($row, $approvedLeaves, $getStatus);
                    $is_found = true;
                }
            }

            // Holiday
            if (!$is_found && isset($holiday_record_exit)) {
                $hStatus = $getStatus(321);
                if ($hStatus) {
                    $row['holidayCount'] = 1;
                    $row['status'] = $hStatus->m_name;
                    $row['status_id'] = (string)$hStatus->m_id;
                    $row['status_code'] = $hStatus->m_type;
                    $row['statusColor'] = json_decode($hStatus->m_other, true)['color'] ?? null;
                    $row['checkInTime'] = '-';
                    $row['checkOutTime'] = '-';
                    $row['workingHour'] = '-';
                    $is_found = true;
                }
            }

            // Week Off
            if (!$is_found && in_array($date, $weekOfDates)) {
                $wStatus = $getStatus(322);
                if ($wStatus) {
                    $row['weekOffCount'] = 1;
                    $row['status'] = $wStatus->m_name;
                    $row['status_id'] = (string)$wStatus->m_id;
                    $row['status_code'] = $wStatus->m_type;
                    $row['statusColor'] = json_decode($wStatus->m_other, true)['color'] ?? null;
                    $row['checkInTime'] = '-';
                    $row['checkOutTime'] = '-';
                    $row['workingHour'] = '-';
                    $is_found = true;
                }
            }

            // Fallback: approved leave (if not handled earlier)
            if (!$is_found && !empty($leave_record_exit)) {
                $approvedLeaves = array_filter($leave_record_exit, fn($l) => ($l->lvr_status != 170) && ($l->lvr_stage_completed ?? 0));
                if (!empty($approvedLeaves)) {
                    self::applyLeavesToRow($row, $approvedLeaves, $getStatus);
                    $is_found = true;
                }
            }

            // Final fallback: mark absent for past dates
            if (!$is_found && Carbon::parse($date)->isPast()) {
                $abs = $getStatus(203);
                if ($abs) {
                    $row['absentCount'] = 1;
                    $row['status'] = $abs->m_name;
                    $row['status_id'] = (string)$abs->m_id;
                    $row['status_code'] = $abs->m_type;
                    $row['statusColor'] = json_decode($abs->m_other, true)['color'] ?? null;
                    $row['checkInTime'] = '-';
                    $row['checkOutTime'] = '-';
                    $row['workingHour'] = '-';
                }
            }

            // Ensure status_id string
            if ($row['status_id'] && !is_string($row['status_id'])) $row['status_id'] = (string)$row['status_id'];

            $attendanceData[] = $row;
        }

        // Salary day calculation
        $salaryDay = 0;
        foreach ($attendanceData as $key => $value) {
            $salaryDay += $value['presentCount'] ?? 0;
            if (($value['halfDayCount'] ?? 0) == 1 && ($value['approvedLeaveCount'] ?? 0) == 0.5) {
                $salaryDay += 1;
            } elseif (($value['halfDayCount'] ?? 0) == 1) {
                $salaryDay += 0.5;
            } else {
                $salaryDay += $value['approvedLeaveCount'] ?? 0;
            }
            $salaryDay += $value['weekOffCount'] ?? 0;
            $salaryDay += $value['holidayCount'] ?? 0;
        }

        $lastIndex = count($attendanceData) - 1;
        if (isset($attendanceData[$lastIndex])) $attendanceData[$lastIndex]['totalSalariedDays'] = $salaryDay;

        return $attendanceData;
    }

    // -------------------- Helpers --------------------

    private static function buildDefaultRow($date)
    {
        return [
            'status' => '-',
            'status_id' => '-',
            'status_code' => '-',
            'statusColor' => '#BDBDBD',
            'approvalStatus' => null,
            'checkInTime' => null,
            'checkOutTime' => null,
            'checkInLatitude' => null,
            'checkInLongitude' => null,
            'checkOutLatitude' => null,
            'checkOutLongitude' => null,
            'updatedBy' => null,
            'workingHour' => null,
            'OT' => null,
            'earlyExit' => null,
            'late' => null,
            'attendance_remark' => '--',
            'mark_as_absent' => '--',
            'checkInLocation' => '--',
            'checkOutLocation' => '--',
            'checkInPhoto' => [],
            'checkOutPhoto' => [],
            'atd_segments' => [],
            'presentCount' => 0,
            'holidayPresentCount' => 0,
            'weekOffPresentCount' => 0,
            'leaveCount' => 0,
            'holidayCount' => 0,
            'weekOffCount' => 0,
            'absentCount' => 0,
            'halfDayCount' => 0,
            'missedPunchCount' => 0,
            'overtimeCount' => 0,
            'lateCount' => 0,
            'earlyExitCount' => 0,
            'approvedLeaveCount' => 0,
            'approvedMissedPunchCount' => 0,
            'isApproved' => null,
            'previousCheckInTime' => null,
            'previousCheckOutTime' => null,
            'previousLate' => null,
            'previousExit' => null,
            'UPL' => 0,
            'totalSalariedDays' => 0,
            'atd_id' => 0,
            'atd_am_id' => 0,
            'date' => $date,
        ];
    }

    private static function applyMissedPunchToRow(array &$row, $missedPunch, $attendance_record_exit, $getStatus, $employee, $leave_record_exit)
    {
        // Missed punch basic metadata
        $row['missedPunchCount'] = 1;
        $row['attendance_remark'] = $missedPunch->ae_reason_id ? optional($missedPunch->fh_mispunch_reason)->m_name : ($missedPunch->ae_custom_reason ?? 'N/A');

        // approvalStatus set from missed punch
        $row['approvalStatus'] = [
            'source' => 'missed_punch',
            'status' => $getStatus($missedPunch->ae_status) ?? null,
            'stage_completed' => $missedPunch->ae_stage_completed ?? null,
        ];

        // derive shift minutes from employee shift
        $shiftStartTime = $employee->fh_shift_type->pst_start_time ?? '09:00:00';
        $shiftEndTime = $employee->fh_shift_type->pst_end_time ?? '18:00:00';
        $shiftExitTime = $employee->fh_shift_type->pst_min_work_hour ?? $shiftEndTime;
        $pst_start_time = Carbon::parse($shiftStartTime);
        $pst_end_time = Carbon::parse($shiftEndTime);
        $pst_exit_time = Carbon::parse($shiftExitTime);
        $dailyWorkingMinutes = $pst_start_time->diffInMinutes($pst_end_time);
        $dailyExitMinutes = $pst_start_time->diffInMinutes($pst_exit_time);

        $checkInTime = Carbon::parse(Carbon::parse($missedPunch->ae_date)->toDateString().' '.$missedPunch->ae_in_time);
        $checkOutTime = Carbon::parse(Carbon::parse($missedPunch->ae_date)->toDateString().' '.$missedPunch->ae_out_time);
        $workedMinutes = $checkInTime->diffInMinutes($checkOutTime);

        // thresholds
        $fullDayThreshold = $dailyWorkingMinutes;
        $halfDayThreshold = $dailyWorkingMinutes / 2;
        $halfDayExitThreshold = isset($dailyExitMinutes) ? $dailyExitMinutes / 2 : $halfDayThreshold;

        // set times & working hours
        $row['checkInTime'] = Carbon::createFromFormat('H:i:s', $missedPunch->ae_in_time)->format('h:i A');
        $row['checkOutTime'] = Carbon::createFromFormat('H:i:s', $missedPunch->ae_out_time)->format('h:i A');
        $row['workingHour'] = number_format($missedPunch->ae_total_working ?? ($workedMinutes/60), 2);

        // present/halfday/other logic
        if ($workedMinutes >= $dailyExitMinutes || $workedMinutes >= $fullDayThreshold) {
            $presentData = $getStatus(251);
            if ($presentData) {
                $row['presentCount'] = 1;
                $row['status_id'] = (string)$presentData->m_id;
                $row['status'] = $presentData->m_name;
                $row['status_code'] = $presentData->m_type;
                $row['statusColor'] = json_decode($presentData->m_other, true)['color'] ?? null;
                $row['approvedMissedPunchCount'] = 1;
            }
        } elseif ($workedMinutes >= $halfDayExitThreshold || $workedMinutes >= $halfDayThreshold) {
            // half day present
            $halfDayPresentData = $getStatus(252);
            if ($halfDayPresentData) {
                $row['presentCount'] = 0.5;
                $row['approvedMissedPunchCount'] = 0.5;
                // if there's a single leave, combine statuses
                if (!empty($leave_record_exit) && count($leave_record_exit) == 1) {
                    $l = $leave_record_exit[0];
                    $segment = optional($l->fh_leave_day_segment)->m_id ?? null;
                    if ($segment == 236) { // 2nd half leave
                        $row['status_id'] = $halfDayPresentData->m_id . '/' . $l->fh_leave_cat_type->m_id;
                        $row['status'] = $halfDayPresentData->m_name . '/' . $l->fh_leave_cat_type->m_name;
                        $row['status_code'] = $halfDayPresentData->m_type . '/' . $l->fh_leave_cat_type->m_type;
                        $row['statusColor'] = json_decode($halfDayPresentData->m_other, true)['color'] . '/' . json_decode($l->fh_leave_cat_type->m_other, true)['color'];
                    } else {
                        $row['status_id'] = $l->fh_leave_cat_type->m_id . '/' . $halfDayPresentData->m_id;
                        $row['status'] = $l->fh_leave_cat_type->m_name . '/' . $halfDayPresentData->m_name;
                        $row['status_code'] = $l->fh_leave_cat_type->m_type . '/' . $halfDayPresentData->m_type;
                        $row['statusColor'] = json_decode($l->fh_leave_cat_type->m_other, true)['color'] . '/' . json_decode($halfDayPresentData->m_other, true)['color'];
                    }
                } else {
                    $row['status_id'] = $halfDayPresentData->m_id;
                    $row['status'] = $halfDayPresentData->m_name;
                    $row['status_code'] = $halfDayPresentData->m_type;
                    $row['statusColor'] = json_decode($halfDayPresentData->m_other, true)['color'] ?? null;
                }
            }
        } else {
            // Not enough minutes — treat as missedPunch status
            $missedStatus = $getStatus(228);
            if ($missedStatus) {
                $row['status_id'] = (string)$missedStatus->m_id;
                $row['status'] = $missedStatus->m_name;
                $row['status_code'] = $missedStatus->m_type;
                $row['statusColor'] = json_decode($missedStatus->m_other, true)['color'] ?? null;
            }
            // if old attendance record exists, show previous times
            if ($attendance_record_exit) {
                $row['previousCheckInTime'] = $attendance_record_exit->atd_check_in_time ? Carbon::parse($attendance_record_exit->atd_check_in_time)->format('H:i') : '-';
                $row['previousCheckOutTime'] = $attendance_record_exit->atd_check_out_time ? Carbon::parse($attendance_record_exit->atd_check_out_time)->format('H:i') : '-';
            }
        }
    }

    private static function applyAttendanceLogToRow(array &$row, $attendanceLog, $attendance_record_exit, $getStatus)
    {
        // approvalStatus set from missed punch
        $row['approvalStatus'] = [
            'source' => 'missed_punch',
            'status' => $getStatus($attendanceLog->al_status) ?? null,
            'stage_completed' => $attendanceLog->al_stage_completed ?? null,
        ];

        $row['checkInTime'] = $attendanceLog->al_check_in_time ? Carbon::parse($attendanceLog->al_check_in_time)->format('H:i') : '-';
        $row['checkOutTime'] = $attendanceLog->al_check_out_time ? Carbon::parse($attendanceLog->al_check_out_time)->format('H:i') : '-';
        $row['workingHour'] = $attendanceLog->al_total_worked_hours ? number_format($attendanceLog->al_total_worked_hours, 2) : '-';
        $row['attendance_remark'] = $attendanceLog->al_reason ?: ($attendance_record_exit?->atd_remark ?? $row['attendance_remark']);
        $row['mark_as_absent'] = $attendanceLog->al_is_absent ?? ($attendance_record_exit?->atd_is_absent ?? $row['mark_as_absent']);

        $row['late'] = ($attendanceLog->al_is_late && $attendanceLog->al_late_duration) ? number_format($attendanceLog->al_late_duration, 2) : null;
        $row['earlyExit'] = ($attendanceLog->al_is_early_exit && $attendanceLog->al_early_exit_duration) ? number_format($attendanceLog->al_early_exit_duration, 2) : null;

        $row['updatedBy'] = optional($attendanceLog->fh_employee_data)->emp_full_name ?? '-';

        $row['previousCheckInTime'] = isset($attendance_record_exit->atd_check_in_time) && !empty($attendance_record_exit->atd_check_in_time) ? Carbon::parse($attendance_record_exit->atd_check_in_time)->format('H:i') : '-';
        $row['previousCheckOutTime'] = isset($attendance_record_exit->atd_check_out_time) && !empty($attendance_record_exit->atd_check_out_time) ? Carbon::parse($attendance_record_exit->atd_check_out_time)->format('H:i') : '-';

        $row['previousLate'] = isset($attendance_record_exit->atd_is_late) && !empty($attendance_record_exit->atd_late_duration) ? number_format($attendance_record_exit->atd_late_duration, 2) : null;
        $row['previousExit'] = isset($attendance_record_exit->atd_is_early_exit) && !empty($attendance_record_exit->atd_early_exit_duration) ? number_format($attendance_record_exit->atd_early_exit_duration, 2) : null;

        if ($attendanceLog->al_attendance_status) {
            $s = $getStatus($attendanceLog->al_attendance_status);
            if ($s) {
                $row['status_id'] = (string)$s->m_id;
                $row['status'] = $s->m_name;
                $row['status_code'] = $s->m_type;
                $row['statusColor'] = json_decode($s->m_other, true)['color'] ?? null;
                if ($s->m_id == 251) $row['presentCount'] = 1;
                if ($s->m_id == 252) $row['halfDayCount'] = 1;
                if ($s->m_id == 319) $row['holidayPresentCount'] = 1;
                if ($s->m_id == 320) $row['weekOffPresentCount'] = 1;
            }
        } else {
            $present = $getStatus(251);
            if ($present) {
                $row['status_id'] = (string)$present->m_id;
                $row['status'] = $present->m_name;
                $row['status_code'] = $present->m_type;
                $row['statusColor'] = json_decode($present->m_other, true)['color'] ?? null;
                $row['presentCount'] = 1;
            }
        }

        if ($attendanceLog->al_is_overtime == 1) {
            $row['overtimeCount'] = 1;
            $row['OT'] = $attendanceLog->al_overtime_hours ?? 0;
        }
        if ($attendanceLog->al_is_late == 1) $row['lateCount'] = 1;
        if ($attendanceLog->al_is_early_exit == 1) $row['earlyExitCount'] = 1;
    }

    private static function applyAttendanceRecordToRow(array &$row, $attendanceRecord, $getStatus)
    {
        // approvalStatus from attendance record
        $row['approvalStatus'] = [
            'source' => 'attendance_record',
            'status' => $getStatus($attendanceRecord->atd_approval_status) ?? null,
            'stage_completed' => $attendanceRecord->atd_stage_completed ?? null,
        ];

        $status = $attendanceRecord->fh_attendance_status ?? null;
        if ($status) {
            $row['status_id'] = $status->m_id;
            $row['status'] = $status->m_name;
            $row['status_code'] = $status->m_type;
            $row['statusColor'] = json_decode($status->m_other, true)['color'] ?? null;
        }

        if ($attendanceRecord->atd_attendance_status == 251) $row['presentCount'] = 1;
        if ($attendanceRecord->atd_attendance_status == 252) {
            $row['halfDayCount'] = 1;
            $row['presentCount'] = 0.5;
        }
        if ($attendanceRecord->atd_is_overtime) $row['overtimeCount'] = 1;
        if ($attendanceRecord->atd_is_late) $row['lateCount'] = 1;
        if ($attendanceRecord->atd_is_early_exit) $row['earlyExitCount'] = 1;

        $row['checkInTime'] = $attendanceRecord->atd_check_in_time ? Carbon::parse($attendanceRecord->atd_check_in_time)->format('H:i') : '-';
        $row['checkOutTime'] = $attendanceRecord->atd_check_out_time ? Carbon::parse($attendanceRecord->atd_check_out_time)->format('H:i') : '-';
        $row['workingHour'] = $attendanceRecord->atd_total_worked_hours ? number_format($attendanceRecord->atd_total_worked_hours, 2) : '-';
        $row['earlyExit'] = ($attendanceRecord->atd_is_early_exit && $attendanceRecord->atd_early_exit_duration) ? number_format($attendanceRecord->atd_early_exit_duration, 2) : null;
        $row['late'] = ($attendanceRecord->atd_is_late && $attendanceRecord->atd_late_duration) ? number_format($attendanceRecord->atd_late_duration, 2) : null;
        $row['OT'] = $attendanceRecord->atd_is_overtime ?? 0;
        $row['attendance_remark'] = $attendanceRecord->atd_remark ?: $row['attendance_remark'];
        $row['checkInLocation'] = $attendanceRecord->atd_punchin_location ?: $row['checkInLocation'];
        $row['checkOutLocation'] = $attendanceRecord->atd_punchout_location ?: $row['checkOutLocation'];
        $row['checkInPhoto'] = $attendanceRecord->atd_punchin_photo ? json_decode($attendanceRecord->atd_punchin_photo) : $row['checkInPhoto'];
        $row['checkOutPhoto'] = $attendanceRecord->atd_punchout_photo ? json_decode($attendanceRecord->atd_punchout_photo) : $row['checkOutPhoto'];
        $row['atd_segments'] = $attendanceRecord->atd_segments ? [json_decode($attendanceRecord->atd_segments)] : $row['atd_segments'];
        $row['updatedBy'] = $attendanceRecord->updated_by ? $attendanceRecord->updated_by->emp_full_name : $row['updatedBy'];

        // attach lat/long if present on record (defensive)
        $row['checkInLatitude'] = $attendanceRecord->atd_latitude_punchin ?? $row['checkInLatitude'];
        $row['checkInLongitude'] = $attendanceRecord->atd_longitude_punchin ?? $row['checkInLongitude'];
        $row['checkOutLatitude'] = $attendanceRecord->atd_latitude_punchout ?? $row['checkOutLatitude'];
        $row['checkOutLongitude'] = $attendanceRecord->atd_longitude_punchout ?? $row['checkOutLongitude'];
        $row['atd_id'] = $attendanceRecord->atd_longitude_punchout ?? $row['atd_id'];
        $row['atd_am_id'] = $attendanceRecord->atd_longitude_punchout ?? $row['atd_am_id'];
    }

    private static function applyLeavesToRow(array &$row, array $leaves, $getStatus)
    {
        // take first leave
        $l = array_values($leaves)[0];

        // approvalStatus assigned from leave
        $row['approvalStatus'] = [
            'source' => 'leave',
            'status' => $l->lvr_status ?? null,
            'stage_completed' => $l->lvr_stage_completed ?? null,
        ];

        $leaveStatus      = $l->fh_leave_cat_type;      // master record
        $leaveDayType     = $l->fh_leave_day_type;      // full / half
        $leaveSegmentType = $l->fh_leave_day_segment;   // 1st half / 2nd half
        $isHalfLeave      = optional($leaveDayType)->m_id != 201; // 201 = full day
        $isUnpaid         = ($leaveStatus->m_id ?? null) == 215;

        // ----------------------------------------------------------
        // CASE 1 — FULL DAY LEAVE
        // ----------------------------------------------------------
        if (!$isHalfLeave) {

            // overwrite status fully
            $row['status']      = $leaveStatus->m_name;
            $row['status_id']   = $leaveStatus->m_id;
            $row['status_code'] = $leaveStatus->m_type;
            $row['statusColor'] = json_decode($leaveStatus->m_other, true)['color'] ?? null;

            $row['checkInTime']  = '-';
            $row['checkOutTime'] = '-';
            $row['workingHour']  = '-';

            // unpaid leave logic
            if ($isUnpaid) {
                $row['UPL']        = 1;
                $row['leaveCount'] = 0;
            } else {
                $row['leaveCount']        = 1;
                $row['approvedLeaveCount'] = 1;
            }

            return;
        }

        // ----------------------------------------------------------
        // CASE 2 — HALF DAY LEAVE
        // We need to MERGE with existing half-day present/missed punch/etc.
        // ----------------------------------------------------------

        // existing half?
        $existingIsHalf = isset($row['halfDayCount']) && $row['halfDayCount'] == 1;
        // $row['date'] == '2025-10-15' ? dd($row, $existingIsHalf, $isHalfLeave) : null;

        // get present status master (ID=251)
        $halfDayStatus = $getStatus(252);

        // status parts for combination
        $leavePart = [
            'id'    => $leaveStatus->m_id,
            'name'  => $leaveStatus->m_name,
            'code'  => $leaveStatus->m_type,
            'color' => json_decode($leaveStatus->m_other, true)['color'] ?? null,
        ];

        $presentPart = [
            'id'    => $halfDayStatus->m_id,
            'name'  => $halfDayStatus->m_name,
            'code'  => $halfDayStatus->m_type,
            'color' => json_decode($halfDayStatus->m_other, true)['color'] ?? null,
        ];

        // -------------------------------------------------------------
        // Decide ORDER based on leave segment
        // First Half Leave  →  "Leave / Present"
        // Second Half Leave →  "Present / Leave"
        // -------------------------------------------------------------
        if ($existingIsHalf) {

            if (optional($leaveSegmentType)->m_id == 235) { 
                // 235 → First half leave
                $row['status_id']   = $leavePart['id']  . '/' . $presentPart['id'];
                $row['status']      = $leavePart['name'] . '/' . $presentPart['name'];
                $row['status_code'] = $leavePart['code'] . '/' . $presentPart['code'];
                $row['statusColor'] = $leavePart['color'] . '/' . $presentPart['color'];
            } else { 
                // 236 → Second half leave
                $row['status_id']   = $presentPart['id']  . '/' . $leavePart['id'];
                $row['status']      = $presentPart['name'] . '/' . $leavePart['name'];
                $row['status_code'] = $presentPart['code'] . '/' . $leavePart['code'];
                $row['statusColor'] = $presentPart['color'] . '/' . $leavePart['color'];
            }

            // update counters
            if ($isUnpaid) {
                $row['UPL'] = ($row['UPL'] ?? 0) + 0.5;
            } else {
                $row['leaveCount']        = ($row['leaveCount'] ?? 0) + 0.5;
                $row['approvedLeaveCount'] = ($row['approvedLeaveCount'] ?? 0) + 0.5;
            }

            return;
        }

        // -------------------------------------------------------------
        // CASE 3 — No existing half-day → simple half leave only
        // -------------------------------------------------------------
        $row['status_id']   = $leaveStatus->m_id;
        $row['status']      = $leaveStatus->m_name;
        $row['status_code'] = $leaveStatus->m_type;
        $row['statusColor'] = json_decode($leaveStatus->m_other, true)['color'] ?? null;

        $row['checkInTime']  = '-';
        $row['checkOutTime'] = '-';
        $row['workingHour']  = '-';

        if ($isUnpaid) {
            $row['UPL'] = 0.5;
        } else {
            $row['leaveCount']        = 0.5;
            $row['approvedLeaveCount'] = 0.5;
        }
    }

    //Update Attendance Common Function
    public static function processAttendanceCustom(Request $request)
    {
        try {
            $user = Auth::user();
            $employeeId = $request->id;
            $date = $request->punch_date;
            $markAsAbsent = $request->has('mark_as_absent') ? 1 : 0;

            $employee = Employee::where('emp_id', $employeeId)->first();
            if (!$employee)
                return ['status' => false, 'message' => "Employee not found."];

            if ($employee->emp_status == 72)
                return ['status' => false, 'message' => "Employee is inactive."];

            if (Carbon::parse($date) >= Carbon::now())
                return ['status' => false, 'message' => "Can't update Future Date's Attendance"];

            $frozen = PayrollPeriod::where('pp_b_id', $employee->emp_b_id)
                ->where('pp_start_date', '<=', $date)
                ->where('pp_end_date', '>=', $date)
                ->where('pp_is_freezed', 120)
                ->exists();
            if ($frozen)
                return ['status' => false, 'message' => "Frozen attendance records cannot be updated."];

            $resolvedShift = ShiftResolver::resolveEmployeeShift($employee, $date);
            $shift = $resolvedShift->shift ?? PolicyShiftTiming::find($employee->emp_shift_type_id);
            if (!$shift)
                return ['status' => false, 'message' => "Please assign shift to this employee."];

            $inTime = self::normalizeTime($request->in_time);
            $outTime = self::normalizeTime($request->out_time);

            // USER CHECK-IN WITH DATE
            if ($markAsAbsent != 1 && $shift->pst_type_id != 245) {
                $startParts = preg_split('/\s+/', $shift->pst_start_time);
                $endParts   = preg_split('/\s+/', $shift->pst_end_time);
                $shiftStart1 = Carbon::parse("$date {$startParts[1]}");
                $shiftEnd1   = Carbon::parse("$date {$endParts[1]}");
                if (!empty($inTime) && $shift->pst_allow_punch_begin_before == 1) {
                    $beginBeforeHours = $shift->pst_mins_punch_begin_before / 60;
                    $allowedCheckIn = $shiftStart1->copy()->subHours($beginBeforeHours);
                    $userCheckIn1 = Carbon::parse("$date {$inTime}");
                    if ($beginBeforeHours > 0 && $userCheckIn1->lt($allowedCheckIn)) {
                        return [
                            'status'  => false,
                            'message' => "Check-in allowed after " . $allowedCheckIn->format('H:i')
                        ];
                    }
                }

                if (!empty($outTime) && $shift->pst_allow_punch_end_after == 1) {
                    $endAfterHours = $shift->pst_mins_punch_end_after / 60;
                    $allowedCheckOut = $shiftEnd1->copy()->addHours($endAfterHours);
                    $userCheckOut1 = Carbon::parse("$date {$outTime}");
                    if ($endAfterHours > 0 && $userCheckOut1->gt($allowedCheckOut)) {
                        return [
                            'status'  => false,
                            'message' => "Check-out allowed before " . $allowedCheckOut->format('H:i')
                        ];
                    }
                }
            }

            $leaveApplied =  LeaveRequest::where('lvr_emp_id', $employee->emp_id)
                ->whereDate('lvr_start_date', '<=', $date)
                ->whereDate('lvr_end_date', '>=', $date)
                ->where('lvr_status', '!=', 170)->first();
            if ($leaveApplied && $leaveApplied->fh_leave_day_type->m_id == 201) { //201=='Full Day'
                return response()->json(['status' => false, 'result' => [], 'message' => "Unable to update attendance because you have applied full-day leave for today. Contact HR or your manager for assistance."]);
            } elseif ($leaveApplied && $leaveApplied->fh_leave_day_segment->m_id == 235) { //235==First Half
                $allowedCheckIn = $shiftStart1->copy()->addHours(2);
                $userCheckIn = Carbon::parse("{$request->in_time}");
                if ($userCheckIn->lt($allowedCheckIn)) {
                    return response()->json([
                        'status' => false,
                        'result' => [],
                        'message' => "You are on First-Half leave. You can check-in only after " . $allowedCheckIn->format('H:i')
                    ]);
                }
            } elseif ($leaveApplied && $leaveApplied->fh_leave_day_segment->m_id == 236) { //236==Second Half
                $allowedCheckOut = $shiftEnd1->copy()->subHours(3);
                $userCheckOut = Carbon::parse("{$request->out_time}");
                if ($userCheckOut->gt($allowedCheckOut)) {
                    return response()->json([
                        'status' => false,
                        'result' => [],
                        'message' => "You are on Second-Half leave. You can check-out only before " . $allowedCheckOut->format('H:i')
                    ]);
                }
            }

            $isWeeklyOff = self::getWeekOffDates($employee, null, null, $date, $date);

            $isHoliday = PolicyHolidayList::where('phl_b_id', $user->emp_b_id)
                ->whereDate('phl_start_date', '<=', $date)
                ->whereDate('phl_end_date', '>=', $date)
                ->first();

            if ($isHoliday && $isHoliday->phl_type_id == 205 && (int)$isHoliday->phl_day_type_id === 201) {
                return ['status' => false, 'message' => "You can't update attendance on a public holiday."];
            }

            $attendance = AttendanceRecord::where('atd_emp_id', $employeeId)
                ->whereDate('atd_date', $date)
                ->first();

            $checkInTime = (!empty($inTime)) ? Carbon::parse($date . ' ' . $inTime)->format('Y-m-d H:i:s') : null;
            $checkOutTime = (!empty($outTime)) ? Carbon::parse($date . ' ' . $outTime)->format('Y-m-d H:i:s') : null;

            $shiftStartTime = $date . ' ' . Carbon::parse($shift->pst_start_time)->format('H:i:s');
            $shiftEndTime = $date . ' ' . Carbon::parse($shift->pst_end_time)->format('H:i:s');
            $shiftMinEndTime = $date . ' ' . Carbon::parse($shift->pst_min_work_hour)->format('H:i:s');

            $dayName = Carbon::parse($date)->format('l');

            if ($shift->pst_allow_partial_day == 1 && $shift->fh_master_table_week->m_name == $dayName) {
                $shiftStartTime = $date . ' ' . Carbon::parse($shift->pst_partial_day_begin_time)->format('H:i:s');
                $shiftEndTime = $date . ' ' . Carbon::parse($shift->pst_partial_day_end_time)->format('H:i:s');
            }

            if (isset($isHoliday) && (int)$isHoliday->phl_day_type_id === 202) {
                if (!empty($shift->pst_break_begin_time1) && !empty($shift->pst_break_end_time1)) {
                    $breakStart = Carbon::parse($shift->pst_break_begin_time1);
                    $breakEnd = Carbon::parse($shift->pst_break_end_time1);
                    // Normalize break times to the same date as shift for comparison
                    $breakStart = Carbon::parse($date . ' ' . $breakStart->format('H:i:s'));
                    $breakEnd = Carbon::parse($date . ' ' . $breakEnd->format('H:i:s'));
                    if ($isHoliday->phl_day_segment_id == 235) { // First Half
                        $shiftEndTime = $breakStart;
                    } elseif ($isHoliday->phl_day_segment_id == 236) { // Second Half
                        $shiftStartTime = $breakEnd;
                    }
                } else {
                    if ($isHoliday->phl_day_segment_id == 235) { // First Half
                        $shiftEndTime = $shift->pst_start_time->addHours(4);
                    } elseif ($isHoliday->phl_day_segment_id == 236) { // Second Half
                        $shiftStartTime = $shift->pst_end_time->subHours(4);
                    }
                }
            }

            $isLate = 0;
            $lateDuration = 0;
            $isEarlyExit = 0;
            $earlyExitDuration = 0;
            $isOvertime = 0;
            $overtimeHours = 0;
            $totalWorkedHours = 0;
            $attendanceStatus = 228;

            if ($checkInTime && $checkOutTime) {
                $checkIn = Carbon::parse($checkInTime);
                $checkOut = Carbon::parse($checkOutTime);
                $shiftStart = Carbon::parse($shiftStartTime);
                $shiftEnd = Carbon::parse($shiftEndTime);
                $shiftMinEnd = Carbon::parse($shiftMinEndTime);

                $totalWorkedMinutes = abs($checkIn->diffInMinutes($checkOut));
                $totalWorkedHours = abs(number_format($totalWorkedMinutes / 60, 2));

                if ($shift->pst_type_id == 245) {
                    if ($shiftStart->greaterThan($shiftEnd)) {
                        $shiftEnd = $shiftEnd->copy()->addDay();
                        if ($checkIn->format('H:i:s') <= $shiftEnd->copy()->subDay()->format('H:i:s')) {
                            $checkIn->addDay();
                        }

                        if ($checkOut->format('H:i:s') <= $shiftEnd->copy()->subDay()->format('H:i:s')) {
                            $checkOut->addDay();
                        }
                    }
                    
                    $shiftGraceTime = $shiftStart->copy();
                    if ($shift->pst_allow_grace_time == 1) {
                        $shiftGraceTime->addMinutes($shift->pst_grace_time);
                    }

                    if ($checkIn->gt($shiftGraceTime)) {
                        $isLate = 1;
                        $lateDuration = $shiftGraceTime->diffInMinutes($checkIn);
                    }

                    if ($checkOut->lt($shiftEnd)) {
                        $isEarlyExit = 1;
                        $earlyExitDuration = $checkOut->diffInMinutes($shiftEnd);
                    }

                    $isHoliday = PolicyHolidayList::where('phl_b_id', $employee->emp_b_id)->where('phl_day_type_id', 201)
                        ->whereDate('phl_start_date', '<=', $date)
                        ->whereDate('phl_end_date', '>=', $date)
                        ->exists();

                    if ($isWeeklyOff || $isHoliday) {
                        $attendanceStatus = $isWeeklyOff ? 320 : 319;
                    } else {
                        if ($totalWorkedMinutes >= 480) {
                            $attendanceStatus = 251;
                        } elseif ($totalWorkedMinutes >= 240) {
                            $attendanceStatus = 252;
                        } else {
                            $attendanceStatus = 203;
                        }
                    }
                } else {
                    $shiftGraceTime = $shiftStart->copy();
                    if ($shift->pst_allow_grace_time == 1) {
                        $shiftGraceTime->addMinutes($shift->pst_grace_time);
                    }

                    if ($checkIn->gt($shiftGraceTime)) {
                        $isLate = 1;
                        $lateDuration = number_format($checkIn->diffInMinutes($shiftGraceTime), 2);
                    }

                    if ($checkOut->lt($shiftEnd)) {
                        $isEarlyExit = 1;
                        $earlyExitDuration = (float) abs($shiftEnd->diffInMinutes($checkOut));
                    }

                    $isHoliday = PolicyHolidayList::where('phl_b_id', $employee->emp_b_id)->where('phl_day_type_id', 201)
                        ->whereDate('phl_start_date', '<=', $date)
                        ->whereDate('phl_end_date', '>=', $date)
                        ->exists();

                    if ($isWeeklyOff || $isHoliday) {
                        $attendanceStatus = $isWeeklyOff ? 320 : 319;
                    } else {
                        $minWorkHour = $shiftStart ? abs(Carbon::parse($shiftStart)->diffInMinutes(Carbon::parse($shiftMinEnd))) : 0;
                        $halfMinWorkHour = $minWorkHour / 2;

                        if ($totalWorkedMinutes >= $minWorkHour) {
                            $attendanceStatus = 251;
                        } elseif ($totalWorkedMinutes >= $halfMinWorkHour) {
                            $attendanceStatus = 252;
                        } else {
                            $attendanceStatus = 203;
                        }
                    }
                }
            } elseif ($checkInTime || $checkOutTime) {
                $attendanceStatus = 228;
            }

            if (!$markAsAbsent) {
                $attendanceLog = AttendanceLog::create([
                    'al_b_id' => $user->emp_b_id,
                    'al_emp_id' => $employeeId,
                    'al_atd_id' => $attendance->atd_id ?? null,
                    'al_pst_id' => $shift->pst_id ?? null,
                    'al_check_in_time' => $checkInTime,
                    'al_check_out_time' => $checkOutTime,
                    'al_date' => $date,
                    'al_is_late' => $isLate,
                    'al_late_duration' => $lateDuration,
                    'al_is_early_exit' => $isEarlyExit,
                    'al_early_exit_duration' => $earlyExitDuration,
                    // 'al_overtime_hours' => $overtimeHours,
                    'al_total_worked_hours' => $totalWorkedHours,
                    'al_attendance_status' => $attendanceStatus,
                    'al_reason' => $request->reason,
                    'al_updated_by' => $user->emp_id,
                ]);

                if ($attendanceStatus == 320 || $attendanceStatus == 319) {
                    CentralLogics::generateCompOff($employee, $date, 1);
                }
            } else {
                $attendanceStatus = 203;
                $attendanceLog = AttendanceLog::create([
                    'al_b_id' => $user->emp_b_id,
                    'al_emp_id' => $employeeId,
                    'al_atd_id' => $attendance->atd_id ?? null,
                    'al_pst_id' => $shift->pst_id ?? null,
                    'al_check_in_time' => null,
                    'al_check_out_time' => null,
                    'al_date' => $date,
                    'al_is_late' => 0,
                    'al_late_duration' => 0,
                    'al_is_early_exit' => 0,
                    'al_early_exit_duration' => 0,
                    'al_is_overtime' => 0,
                    'al_overtime_hours' => 0,
                    'al_total_worked_hours' => 0,
                    'al_attendance_status' => $attendanceStatus,
                    'al_is_absent' => $markAsAbsent,
                    'al_reason' => $request->reason,
                    'al_updated_by' => $user->emp_id,
                ]);
            }

            if ($attendanceLog) {
                $attendanceLog->al_code = 'AL' . Carbon::parse($attendanceLog->al_date)->format('Ymd') . '-' . $attendanceLog->al_id;
                $ot_enabled = OvertimePolicy::where('ot_b_id', $user->emp_b_id)->where('ot_is_enabled', 1)->exists();
                $attendanceLog->save();
                if ($ot_enabled) {
                    $ruleCriteria = RuleCriterion::with('fh_approval_module')
                        ->where('rc_b_id', $user->emp_b_id)
                        ->where('rc_condition_option_id', 140)
                        ->whereHas('fh_approval_module', function ($query) {
                            $query->where('am_module_id', 562)
                                ->where('am_status', 1);
                        })->first();
                    $processApprovers = [];
                    $emp_d_id = $user->emp_d_id;
                    $amId = null;
                    // Ensure $ruleCriteria exists before accessing the relationship
                    if ($ruleCriteria && $ruleCriteria->fh_approval_module) {
                        $processApprovers = $ruleCriteria->fh_approval_module
                            ->filteredProcessApprovers($emp_d_id)
                            ->get();
                    }
                    $approvalEmpIds = [];

                    if (empty($processApprovers)) {
                        $approvalMapping = ApprovalHelper::getApprovalMapping($employee->emp_b_id, $employee->emp_id, 562);
                        if (!$approvalMapping) {
                            return [
                                'status' => false,
                                'message' => 'Attendance updated successfully, But not created approval for overtime.',
                                'logData' => [],
                            ];
                        }
                        $amId = $approvalMapping->eam_am_id ?? null;
                        $approvalEmpIds = ApprovalHelper::getApprovalArray($approvalMapping);
                    } else {
                        $amId = $ruleCriteria->rc_am_id;
                        foreach ($processApprovers as $pa) {
                            if ($pa->pa_emp_id) {
                                $approvalEmpIds[] = $pa->pa_emp_id;
                            }
                        }
                    }

                    $attendanceLog->al_is_overtime = 1;
                    $attendanceLog->al_overtime_hours = CentralLogics::calculateOT($attendanceLog) ?? 0.00;
                    $attendanceLog->save();
                    OtApprovalStatus::updateOrCreate(
                        [
                            'ot_emp_id' => $attendanceLog->al_emp_id,
                            'ot_b_id'   => $user->emp_b_id,
                            'ot_date'   => $attendanceLog->al_date,
                        ],
                        [
                            'ot_atd_id' => $attendanceLog->al_id,
                            'ot_emp_id'          => $attendanceLog->al_emp_id,
                            'ot_atd_type'        => 'log',
                            'ot_module_id'       => 562,
                            'ot_next_approver'   => $approvalEmpIds[0] ?? null,
                            'ot_requested_status'=> 140,
                            'ot_am_id'           => $amId,
                            'ot_stage_completed' => 0,
                        ]
                    );
                }
                $masterStatusData = MasterTable::where('m_id', $attendanceStatus)->first();
            }

            return [
                'status' => true,
                'message' => 'Attendance updated successfully.',
                'logData' => $attendanceLog,
                'statusData' => $masterStatusData ?? null
            ];
        } catch (\Exception $e) {
            return ['status' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public static function getMacAddress($ip) {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows
            $output = shell_exec("arp -a $ip");
            preg_match('/[0-9a-fA-F]{2}[-:][0-9a-fA-F]{2}[-:][0-9a-fA-F]{2}[-:][0-9a-fA-F]{2}[-:][0-9a-fA-F]{2}[-:][0-9a-fA-F]{2}/', $output, $matches);
            return $matches[0] ?? null;
        } else {
            // Linux
            $output = shell_exec("arp -n $ip");
            preg_match('/[0-9a-fA-F]{2}:[0-9a-fA-F]{2}:[0-9a-fA-F]{2}:[0-9a-fA-F]{2}:[0-9a-fA-F]{2}:[0-9a-fA-F]{2}/', $output, $matches);
            return $matches[0] ?? null;
        }
    }

    public static function processAttendance(Request $request)
    {
        // dd($request->all());
        // dd($_SERVER, $request->all(), $request->ip(), $request->userAgent(), Self::getMacAddress($request->ip()));
        try {
            $user = Auth::user();
            $employeeId = $request->id;
            $date = $request->punch_date;
            $in_date = $request->in_date;
            $out_date = $request->out_date;
            $markAsAbsent = $request->has('mark_as_absent') ? 1 : 0;

            // Updated By Jagriti on 22 June 2026
            $menuId = 136;
            $permissions = RolePermissionLogics::get_admin_role_permission($user->emp_role_id);
            $roleHasPermission = $permissions[array_search($menuId, array_column($permissions, 0))];
            $canUpdate = isset($roleHasPermission[1]->update) ? $roleHasPermission[1]->update == 'on' || $user->emp_role_id == 1 : false;

            if (!$canUpdate) {
                return [
                    'status' => false,
                    'message' => "You do not have Permissions to update attendace!"
                ];
            }

            $logExist = AttendanceLog::where('al_emp_id', $employeeId)
                ->where('al_date', $date)
                ->count();

            if ($logExist >= 3) {
                return [
                    'status' => false,
                    'message' => "Can't update attendance because a log already exists. Remove the existing log to proceed."
                ];
            }

            // Early validation
            $employee = Employee::where('emp_id', $employeeId)->first();
            if (!$employee) {
                return ['status' => false, 'message' => "Employee not found."];
            }

            if ($employee->emp_status == 72) {
                return ['status' => false, 'message' => "Employee is inactive."];
            }

            // Check future date early
            if (Carbon::parse($date) >= Carbon::now()) {
                return ['status' => false, 'message' => "Can't update Future Date's Attendance"];
            }

            // Check frozen period early
            $frozen = PayrollPeriod::where('pp_b_id', $employee->emp_b_id)
                ->where('pp_start_date', '<=', $date)
                ->where('pp_end_date', '>=', $date)
                ->where('pp_is_freezed', 120)
                ->exists();

            if ($frozen) {
                return ['status' => false, 'message' => "Frozen attendance records cannot be updated."];
            }

            // Resolve shift
            $resolvedShift = ShiftResolver::resolveEmployeeShift($employee, $date);
            $shift = $resolvedShift->shift ?? PolicyShiftTiming::find($employee->emp_shift_type_id);
            
            if (!$shift) {
                return ['status' => false, 'message' => "Please assign shift to this employee."];
            }

            // Normalize times
            $inTime = self::normalizeTime($request->in_time);
            $outTime = self::normalizeTime($request->out_time);

            $checkInCheck  = $inTime ? Carbon::createFromFormat('Y-m-d H:i', $request->in_date.' '.$inTime) : null;

            $checkOutCheck = $outTime ? Carbon::createFromFormat('Y-m-d H:i', $request->out_date.' '.$outTime) : null;

            if ($markAsAbsent == 0 && $checkInCheck && $checkOutCheck) {
                if ($checkOutCheck->lt($checkInCheck)) {
                    return ['status' => false, 'message' => "Check out time must be greater than Check in time."];
                }
            }

            // Parse shift times once
            $dayName = Carbon::parse($date)->format('l');
            $shiftTimes = self::calculateShiftTimes($shift, $date, $dayName, $employee, $user);

            // Validate punch times
            if ($markAsAbsent != 1 && $shift->pst_type_id != 245 && $inTime) {
                $validation = self::validatePunchTimes($inTime, $outTime, $shift, $date, $shiftTimes);
                if (!$validation['status']) {
                    return $validation;
                }
            }

            // Check leave applied
            // $leaveValidation = self::checkLeaveRestrictions($employee, $date, $inTime, $outTime, $shiftTimes);
            // if (!$leaveValidation['status']) {
            //     return $leaveValidation;
            // }

            // Check holiday
            $isHoliday = self::getHoliday($user->emp_b_id, $date);
            if ($isHoliday && $isHoliday->phl_type_id == 205 && (int)$isHoliday->phl_day_type_id === 201) {
                return ['status' => false, 'message' => "You can't update attendance on a public holiday."];
            }

            // Get weekly off status
            $isWeeklyOff = self::getWeekOffDates($employee, null, null, $date, $date);

            // Get existing attendance
            $attendance = AttendanceRecord::where('atd_emp_id', $employeeId)
                ->whereDate('atd_date', $date)
                ->first();

            // Calculate attendance metrics
            $attendanceData = self::calculateAttendanceMetrics(
                $inTime,
                $outTime,
                $shift,
                $date,
                $in_date,
                $out_date,
                $shiftTimes,
                $isWeeklyOff,
                $employee,
                $markAsAbsent
            );

            // Create attendance log
            $attendanceLog = self::createAttendanceLog(
                $user,
                $employeeId,
                $attendance,
                $shift,
                $date,
                $attendanceData,
                $request->reason,
                $markAsAbsent
            );

            if (!$attendanceLog) {
                return ['status' => false, 'message' => 'Failed to create attendance log.'];
            }

            // Generate comp-off if needed
            if (!$markAsAbsent && ($attendanceData['status'] == 320 || $attendanceData['status'] == 319)) {
                CentralLogics::generateCompOff($employee, $date, 1);
            }

            // Update attendance log code
            $attendanceLog->al_code = 'AL' . Carbon::parse($attendanceLog->al_date)->format('Ymd') . '-' . $attendanceLog->al_id;

            // Handle overtime approval
            $otEnabled = OvertimePolicy::where('ot_b_id', $user->emp_b_id)
                ->where('ot_is_enabled', 1)
                ->exists();

            if ($otEnabled) {
                if($attendanceLog->al_attendance_status !== 228) {
                    $otResult = self::processOvertimeApproval($user, $employee, $attendanceLog, $shift);
                }
                $attendanceLog->save();
                
                if (!$otResult['status']) {
                    return [
                        'status' => false,
                        'message' => $otResult['message'],
                        'logData' => [],
                    ];
                }
            } else {
                $attendanceLog->save();
            }

            $masterStatusData = MasterTable::where('m_id', $attendanceLog->al_attendance_status)->first();

            return [
                'status' => true,
                'message' => 'Attendance updated successfully.',
                'logData' => $attendanceLog,
                'statusData' => $masterStatusData ?? null
            ];

        } catch (\Exception $e) {
            return ['status' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    private static function normalizeTime($time)
    {
        return (trim($time) === '-' || trim($time) === '') ? '' : trim($time);
    }

    private static function calculateShiftTimes($shift, $date, $dayName, $employee, $user)
    {
        $startParts = preg_split('/\s+/', $shift->pst_start_time);
        $endParts = preg_split('/\s+/', $shift->pst_end_time);
        
        $shiftStart = Carbon::parse("$date {$startParts[1]}");
        $shiftEnd = Carbon::parse("$date {$endParts[1]}");
        $shiftMinEnd = $date . ' ' . Carbon::parse($shift->pst_min_work_hour)->format('H:i:s');

        // Partial day adjustment
        if ($shift->pst_allow_partial_day == 1 && $shift->fh_master_table_week->m_name == $dayName) {
            $shiftStart = $date . ' ' . Carbon::parse($shift->pst_partial_day_begin_time)->format('H:i:s');
            $shiftEnd = $date . ' ' . Carbon::parse($shift->pst_partial_day_end_time)->format('H:i:s');
        }

        // Holiday adjustment
        $isHoliday = self::getHoliday($user->emp_b_id, $date);
        if (isset($isHoliday) && (int)$isHoliday->phl_day_type_id === 202) {
            if (!empty($shift->pst_break_begin_time1) && !empty($shift->pst_break_end_time1)) {
                $breakStart = Carbon::parse($date . ' ' . Carbon::parse($shift->pst_break_begin_time1)->format('H:i:s'));
                $breakEnd = Carbon::parse($date . ' ' . Carbon::parse($shift->pst_break_end_time1)->format('H:i:s'));
                
                if ($isHoliday->phl_day_segment_id == 235) {
                    $shiftEnd = $breakStart;
                } elseif ($isHoliday->phl_day_segment_id == 236) {
                    $shiftStart = $breakEnd;
                }
            } else {
                if ($isHoliday->phl_day_segment_id == 235) {
                    $shiftEnd = $shift->pst_start_time->addHours(4);
                } elseif ($isHoliday->phl_day_segment_id == 236) {
                    $shiftStart = $shift->pst_end_time->subHours(4);
                }
            }
        }

        return [
            'start' => $shiftStart,
            'end' => $shiftEnd,
            'minEnd' => $shiftMinEnd,
        ];
    }

    private static function validatePunchTimes($inTime, $outTime, $shift, $date, $shiftTimes)
    {
        if (!empty($inTime) && $shift->pst_allow_punch_begin_before == 1) {
            $beginBeforeHours = $shift->pst_mins_punch_begin_before / 60;
            $allowedCheckIn = $shiftTimes['start']->copy()->subHours($beginBeforeHours);
            $userCheckIn = Carbon::parse("$date {$inTime}");
            
            if ($beginBeforeHours > 0 && $userCheckIn->lt($allowedCheckIn)) {
                return [
                    'status' => false,
                    'message' => "Check-in allowed after " . $allowedCheckIn->format('H:i')
                ];
            }
        }

        if (!empty($outTime) && $shift->pst_allow_punch_end_after == 1) {
            $endAfterHours = $shift->pst_mins_punch_end_after / 60;
            $allowedCheckOut = $shiftTimes['end']->copy()->addHours($endAfterHours);
            $userCheckOut = Carbon::parse("$date {$outTime}");

            $in  = Carbon::createFromFormat('H:i', $inTime);
            $out = Carbon::createFromFormat('H:i', $outTime);
            if ($shift->pst_type_id != 245 && $out->lessThanOrEqualTo($in)) {
                return [
                    'status'  => false,
                    'message' => 'Out time must be greater than in time for Fixed Shift.'
                ];
            }
            
            if ($endAfterHours > 0 && $userCheckOut->gt($allowedCheckOut)) {
                return [
                    'status' => false,
                    'message' => "Check-out allowed before " . $allowedCheckOut->format('H:i')
                ];
            }
        }

        return ['status' => true];
    }

    private static function checkLeaveRestrictions($employee, $date, $inTime, $outTime, $shiftTimes)
    {
        $leaveApplied = LeaveRequest::where('lvr_emp_id', $employee->emp_id)
            ->whereDate('lvr_start_date', '<=', $date)
            ->whereDate('lvr_end_date', '>=', $date)
            ->where('lvr_status', '!=', 170)
            ->first();

        if (!$leaveApplied) {
            return ['status' => true];
        }

        if ($leaveApplied->fh_leave_day_type->m_id == 201) {
            return [
                'status' => false,
                'result' => [],
                'message' => "Unable to update attendance because you have applied full-day leave for today. Contact HR or your manager for assistance."
            ];
        }

        if ($leaveApplied->fh_leave_day_segment->m_id == 235) {
            $allowedCheckIn = $shiftTimes['start']->copy()->addHours(2);
            $userCheckIn = Carbon::parse($inTime);
            
            if ($userCheckIn->lt($allowedCheckIn)) {
                return [
                    'status' => false,
                    'result' => [],
                    'message' => "You are on First-Half leave. You can check-in only after " . $allowedCheckIn->format('H:i')
                ];
            }
        }

        if ($leaveApplied->fh_leave_day_segment->m_id == 236) {
            $allowedCheckOut = $shiftTimes['end']->copy()->subHours(3);
            $userCheckOut = Carbon::parse($outTime);
            
            if ($userCheckOut->gt($allowedCheckOut)) {
                return [
                    'status' => false,
                    'result' => [],
                    'message' => "You are on Second-Half leave. You can check-out only before " . $allowedCheckOut->format('H:i')
                ];
            }
        }

        return ['status' => true];
    }

    private static function getHoliday($businessId, $date)
    {
        return PolicyHolidayList::where('phl_b_id', $businessId)
            ->whereDate('phl_start_date', '<=', $date)
            ->whereDate('phl_end_date', '>=', $date)
            ->first();
    }

    private static function calculateAttendanceMetrics($inTime, $outTime, $shift, $date, $in_date, $out_date, $shiftTimes, $isWeeklyOff, $employee, $markAsAbsent)
    {
        $checkInTime = (!empty($inTime)) ? Carbon::parse($in_date . ' ' . $inTime)->format('Y-m-d H:i:s') : null;
        $checkOutTime = (!empty($outTime)) ? Carbon::parse($out_date . ' ' . $outTime)->format('Y-m-d H:i:s') : null;

        $metrics = [
            'checkIn' => $checkInTime,
            'checkOut' => $checkOutTime,
            'isLate' => 0,
            'lateDuration' => 0,
            'isEarlyExit' => 0,
            'earlyExitDuration' => 0,
            'totalWorkedHours' => 0,
            'status' => 228,
        ];

        if (!$checkInTime && !$checkOutTime) {
            return $metrics;
        }

        // Status calculation
        $isHoliday = PolicyHolidayList::where('phl_b_id', $employee->emp_b_id)
            ->where('phl_day_type_id', 201)
            ->whereDate('phl_start_date', '<=', $date)
            ->whereDate('phl_end_date', '>=', $date)
            ->exists();

        $is_check = 0;
        if($employee->emp_attendance_preference == 368 && $checkInTime) {
            $is_check = 1;
            $checkIn = Carbon::parse($checkInTime);
            $shiftStart = Carbon::parse($shiftTimes['start']);
            $status = $metrics['status'] ?? 228;
            
            // Calculate grace time
            $shiftGraceTime = $shiftStart->copy();
            if ($shift->pst_allow_grace_time == 1) {
                $shiftGraceTime->addMinutes($shift->pst_grace_time);
            }

            // Late calculation
            if ($checkIn->gt($shiftGraceTime) && !in_array($status, [319, 320])) {
                $metrics['isLate'] = 1;
                $metrics['lateDuration'] = $shift->pst_type_id == 245
                    ? $shiftGraceTime->diffInMinutes($checkIn) 
                    : number_format($checkIn->diffInMinutes($shiftGraceTime), 2);
            }

            if ($isWeeklyOff || $isHoliday) {
                $metrics['status'] = $isWeeklyOff ? 320 : 319;
            } else {
                $metrics['status'] = 251;
            }
        }

        if($employee->emp_attendance_preference == 369 && $checkOutTime && !$is_check) {
            $is_check = 1;
            $checkOut = Carbon::parse($checkOutTime);
            $shiftEnd = Carbon::parse($shiftTimes['end']);
            $status = $metrics['status'] ?? 228;

            // Early exit calculation
            if ($checkOut->lt($shiftEnd) && !in_array($status, [319, 320])) {
                $metrics['isEarlyExit'] = 1;
                $metrics['earlyExitDuration'] = abs($shiftEnd->diffInMinutes($checkOut));
            }

            if ($isWeeklyOff || $isHoliday) {
                $metrics['status'] = $isWeeklyOff ? 320 : 319;
            } else {
                $metrics['status'] = 251;
            }
        }

        if ($checkInTime && $checkOutTime && !$is_check) {
            // dd('sd', $is_check);
            $checkIn = Carbon::parse($checkInTime);
            $checkOut = Carbon::parse($checkOutTime);
            $shiftStart = Carbon::parse($shiftTimes['start']);
            $shiftEnd = Carbon::parse($shiftTimes['end']);
            $shiftMinEnd = Carbon::parse($shiftTimes['minEnd']);
            if ($shiftStart->gt($shiftEnd) && $shiftStart->gt($shiftMinEnd)) {
                $shiftEnd->addDay();
                $shiftMinEnd->addDay();
            }
            $minWorkHour = abs($shiftStart->diffInMinutes($shiftMinEnd));
            $halfMinWorkHour = $minWorkHour / 2;

            $totalWorkedMinutes = abs($checkIn->diffInMinutes($checkOut));
            // unpaid break subtract karo
            if ($shift->pst_allow_break1 == 1 && $shift->pst_is_break_paid == 0) {

                $breakMinutes = (int) $shift->pst_break1_duration;
                $totalWorkedMinutes = $totalWorkedMinutes - $breakMinutes;

                // negative na ho
                if ($totalWorkedMinutes < 0) {
                    $totalWorkedMinutes = 0;
                }
            }
            $metrics['totalWorkedHours'] = number_format($totalWorkedMinutes / 60, 2);

            // Calculate grace time
            $shiftGraceTime = $shiftStart->copy();
            if ($shift->pst_allow_grace_time == 1) {
                $shiftGraceTime->addMinutes($shift->pst_grace_time);
            }

            // Late calculation
            if ($checkIn->gt($shiftGraceTime) && $metrics['status'] != 319 && $metrics['status'] != 320) {
                $metrics['isLate'] = 1;
                $metrics['lateDuration'] = $shift->pst_type_id == 245
                    ? $shiftGraceTime->diffInMinutes($checkIn) 
                    : number_format($checkIn->diffInMinutes($shiftGraceTime), 2);
            }

            // Early exit calculation
            if ($checkOut->lt($shiftEnd) && $metrics['status'] != 319 && $metrics['status'] != 320) {
                $metrics['isEarlyExit'] = 1;
                $metrics['earlyExitDuration'] = abs($shiftEnd->diffInMinutes($checkOut));
            }

            if ($isWeeklyOff || $isHoliday) {
                $metrics['status'] = $isWeeklyOff ? 320 : 319;
            } else {
                if ($shift->pst_type_id == 245) {
                    if ($totalWorkedMinutes >= $minWorkHour) {
                        $metrics['status'] = 251;
                    } elseif ($totalWorkedMinutes >= $halfMinWorkHour) {
                        $metrics['status'] = 252;
                        $metrics['isLate'] = 0;
                        $metrics['isEarlyExit'] = 0;
                    } else {
                        $metrics['status'] = 203;
                        $metrics['isLate'] = 0;
                        $metrics['isEarlyExit'] = 0;
                    }
                } else {
                    if ($totalWorkedMinutes >= $minWorkHour) {
                        $metrics['status'] = 251;
                    } elseif ($totalWorkedMinutes >= $halfMinWorkHour) {
                        $metrics['status'] = 252;
                        $metrics['isLate'] = 0;
                        $metrics['isEarlyExit'] = 0;
                    } else {
                        $metrics['status'] = 203;
                        $metrics['isLate'] = 0;
                        $metrics['isEarlyExit'] = 0;
                    }
                }
            }
        }

        if (empty($checkInTime) || empty($checkOutTime)) {
            $metrics['status'] = 228;
        }

        return $metrics;
    }

    private static function createAttendanceLog($user, $employeeId, $attendance, $shift, $date, $attendanceData, $reason, $markAsAbsent)
    {
        if (!$markAsAbsent) {
            return AttendanceLog::create([
                'al_b_id' => $user->emp_b_id,
                'al_emp_id' => $employeeId,
                'al_atd_id' => $attendance->atd_id ?? null,
                'al_pst_id' => $shift->pst_id ?? null,
                'al_check_in_time' => $attendanceData['checkIn'],
                'al_check_out_time' => $attendanceData['checkOut'],
                'al_date' => $date,
                'al_is_late' => $attendanceData['isLate'],
                'al_late_duration' => $attendanceData['lateDuration'],
                'al_is_early_exit' => $attendanceData['isEarlyExit'],
                'al_early_exit_duration' => $attendanceData['earlyExitDuration'],
                'al_total_worked_hours' => $attendanceData['totalWorkedHours'],
                'al_attendance_status' => $attendanceData['status'],
                'al_reason' => $reason,
                'al_updated_by' => $user->emp_id,
            ]);
        }

        return AttendanceLog::create([
            'al_b_id' => $user->emp_b_id,
            'al_emp_id' => $employeeId,
            'al_atd_id' => $attendance->atd_id ?? null,
            'al_pst_id' => $shift->pst_id ?? null,
            'al_check_in_time' => null,
            'al_check_out_time' => null,
            'al_date' => $date,
            'al_is_late' => 0,
            'al_late_duration' => 0,
            'al_is_early_exit' => 0,
            'al_early_exit_duration' => 0,
            'al_is_overtime' => 0,
            'al_overtime_hours' => 0,
            'al_total_worked_hours' => 0,
            'al_attendance_status' => 203,
            'al_is_absent' => 1,
            'al_reason' => $reason,
            'al_updated_by' => $user->emp_id,
        ]);
    }

    public static function processOvertimeApproval($user, $employee, $attendanceLog, $shift)
    {
        $ruleCriteria = RuleCriterion::with('fh_approval_module')
            ->where('rc_b_id', $user->emp_b_id)
            ->where('rc_condition_option_id', 140)
            ->whereHas('fh_approval_module', function ($query) {
                $query->where('am_module_id', 562)->where('am_status', 1);
            })
            ->first();

        $processApprovers = [];
        $approvalEmpIds = [];
        $amId = null;

        if ($ruleCriteria && $ruleCriteria->fh_approval_module) {
            $processApprovers = $ruleCriteria->fh_approval_module
                ->filteredProcessApprovers($user->emp_d_id)
                ->get();
        }

        if (empty($processApprovers)) {
            $approvalMapping = ApprovalHelper::getApprovalMapping($employee->emp_b_id, $employee->emp_id, 562);
            
            // if (!$approvalMapping) {
            //     return [
            //         'status' => false,
            //         'message' => 'Attendance updated successfully, But not created approval for overtime.',
            //     ];
            // }

            $amId = $approvalMapping->eam_am_id ?? null;
            $approvalEmpIds = ApprovalHelper::getApprovalArray($approvalMapping);
        } else {
            $amId = $ruleCriteria->rc_am_id;
            foreach ($processApprovers as $pa) {
                if ($pa->pa_emp_id) {
                    $approvalEmpIds[] = $pa->pa_emp_id;
                }
            }
        }

        if ($shift->pst_type_id == 245) {
            $attendanceLog->al_overtime_hours = CentralLogics::calculateOTRoster($attendanceLog) ?? 0.00;
        } else {
            $attendanceLog->al_overtime_hours = CentralLogics::calculateOT($attendanceLog) ?? 0.00;
        }

        // $attendanceLog->save();

        if ($attendanceLog->al_overtime_hours > 0) {
            $attendanceLog->al_is_overtime = 1;
            $attendanceLog->save();
            OtApprovalStatus::updateOrCreate(
                [
                    'ot_emp_id' => $attendanceLog->al_emp_id,
                    'ot_b_id' => $user->emp_b_id,
                    'ot_date' => $attendanceLog->al_date,
                ],
                [
                    'ot_atd_id' => $attendanceLog->al_id,
                    'ot_emp_id' => $attendanceLog->al_emp_id,
                    'ot_atd_type' => 'log',
                    'ot_module_id' => 562,
                    'ot_next_approver' => $approvalEmpIds[0] ?? null,
                    'ot_requested_status' => 140,
                    'ot_am_id' => $amId,
                    'ot_stage_completed' => 0,
                ]
            );
        }

        return ['status' => true];
    }

    public static function getMonthlyAttendanceData($employee, $month, $year, $holiday_record_exits, $weekOfDates)
    {
        $cacheKey = "attendance_details_{$employee->emp_id}_{$year}_{$month}";
        
        // Return cached data if exists
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $dateRange = $startDate->toPeriod($endDate);
        
        // Preload all required MasterTable data with caching
        $absentStatus = Cache::remember('mastertable_203', 86400, function () {
            return MasterTable::where('m_id', 203)->first();
        });
        
        $uplStatus = Cache::remember('mastertable_215', 86400, function () {
            return MasterTable::where('m_id', 215)->first();
        });
        
        $missedPunchStatus = Cache::remember('mastertable_228', 86400, function () {
            return MasterTable::where('m_id', 228)->first();
        });
        
        $holidayStatus = Cache::remember('mastertable_321', 86400, function () {
            return MasterTable::where('m_id', 321)->first();
        });
        
        $weekOffStatus = Cache::remember('mastertable_322', 86400, function () {
            return MasterTable::where('m_id', 322)->first();
        });
        
        $presentStatus = Cache::remember('mastertable_251', 86400, function () {
            return MasterTable::where('m_id', 251)->first();
        });
        
        $halfDayStatus = Cache::remember('mastertable_252', 86400, function () {
            return MasterTable::where('m_id', 252)->first();
        });
        
        // Cache leave categories for 24 hours
        $leaveCategories = Cache::remember('leave_categories', 86400, function () {
            return MasterTable::where('m_group', 'LEAVE_CATEGORY')
                ->get()
                ->keyBy('m_id');
        });
        
        // Preload date strings
        $dateStrings = [];
        foreach ($dateRange as $dateObj) {
            $dateStrings[] = $dateObj->toDateString();
        }
        
        // Cache employee-specific data for 1 hour
        $empDataCacheKey = "emp_data_{$employee->emp_id}_{$year}_{$month}";
        $cachedEmpData = Cache::remember($empDataCacheKey, 3600, function () use ($employee, $startDate, $endDate) {
            return [
                'attendanceRecords' => $employee->attendance_record()
                    ->whereBetween('atd_date', [$startDate, $endDate])
                    ->with('fh_attendance_status')
                    ->get()
                    ->keyBy(function($row) {
                        return Carbon::parse($row->atd_date)->toDateString();
                    }),
                    
                'attendanceLogs' => AttendanceLog::where('al_emp_id', $employee->emp_id)
                    ->whereBetween('al_date', [$startDate, $endDate])
                    ->get()
                    ->keyBy(function($row) {
                        return Carbon::parse($row->al_date)->toDateString();
                    }),
                    
                'missedPunches' => AttendanceException::where('ae_b_id', $employee->emp_b_id)
                    ->where('ae_emp_id', $employee->emp_id)
                    ->whereBetween('ae_date', [$startDate, $endDate])
                    ->get()
                    ->keyBy(function($row) {
                        return Carbon::parse($row->ae_date)->toDateString();
                    }),
                    
                'leaveRequests' => $employee->leave_requests()
                    ->where(function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('lvr_start_date', [$startDate, $endDate])
                          ->orWhereBetween('lvr_end_date', [$startDate, $endDate]);
                    })
                    ->with(['fh_leave_cat_type', 'fh_leave_day_type', 'fh_leave_day_segment'])
                    ->get()
            ];
        });
        
        $attendanceRecords = $cachedEmpData['attendanceRecords'];
        $attendanceLogs = $cachedEmpData['attendanceLogs'];
        $missedPunches = $cachedEmpData['missedPunches'];
        $leaveRequests = $cachedEmpData['leaveRequests'];
        
        // Preprocess leave data by date
        $leaveByDate = [];
        foreach ($leaveRequests as $leave) {
            $start = Carbon::parse($leave->lvr_start_date);
            $end = Carbon::parse($leave->lvr_end_date);
            
            while ($start->lte($end)) {
                $dateStr = $start->toDateString();
                if (!isset($leaveByDate[$dateStr])) {
                    $leaveByDate[$dateStr] = [];
                }
                $leaveByDate[$dateStr][] = $leave;
                $start->addDay();
            }
        }
        
        // Prepare data structures
        $attendanceData = [];
        $sandwichRuleApplied = [];
        $allDates = $dateStrings;
        
        // Helper function to check absence or leave
        $isAbsentOrLeave = function($date) use ($attendanceRecords, $leaveByDate, $holiday_record_exits, $weekOfDates, $employee, $uplStatus) {
            if ($employee->emp_date_of_joining > $date) {
                return false;
            }
            
            $attendanceRecord = $attendanceRecords[$date] ?? null;
            $leaveRecord = $leaveByDate[$date] ?? null;
            $holidayRecord = $holiday_record_exits[$date] ?? null;
            
            // Check for UPL
            if ($attendanceRecord && $attendanceRecord->atd_attendance_status == 215) {
                return 'UPL';
            }
            
            // Check for Absent
            if (!$attendanceRecord && !$leaveRecord && !$holidayRecord && !in_array($date, $weekOfDates) && Carbon::parse($date)->isPast()) {
                return 'ABS';
            }
            
            // Check for approved leaves
            if ($leaveRecord && count($leaveRecord) >= 1) {
                $leave = $leaveRecord[0];
                $isApproved = $leave->lvr_status != 170 && $leave->lvr_stage_completed;
                if ($isApproved) {
                    return $leave->fh_leave_cat_type->m_type; // SL/CL/etc
                }
            }
            
            return false;
        };
        
        // Apply sandwich rule logic
        foreach ($allDates as $index => $date) {
            $isCurrentWOOrHO = in_array($date, $weekOfDates) || isset($holiday_record_exits[$date]);
            
            if ($isCurrentWOOrHO) {
                $prevDate = $index > 0 ? $allDates[$index - 1] : null;
                $nextDate = $index < count($allDates) - 1 ? $allDates[$index + 1] : null;
                
                $prevStatus = $prevDate ? $isAbsentOrLeave($prevDate) : false;
                $nextStatus = $nextDate ? $isAbsentOrLeave($nextDate) : false;
                
                // Apply sandwich rule if both previous and next day have ABS/SL/CL/UPL
                if ($prevStatus && $nextStatus) {
                    // Determine which status to apply
                    $statusToApply = 'ABS'; // default
                    
                    if ($prevStatus === 'UPL' || $nextStatus === 'UPL') {
                        $statusToApply = 'UPL';
                    } elseif ($prevStatus === 'ABS' || $nextStatus === 'ABS') {
                        $statusToApply = 'ABS';
                    } elseif (in_array($prevStatus, ['SL', 'CL']) || in_array($nextStatus, ['SL', 'CL'])) {
                        $statusToApply = in_array($prevStatus, ['SL', 'CL']) ? $prevStatus : $nextStatus;
                    }
                    
                    $sandwichRuleApplied[$date] = $statusToApply;
                }
            }
        }
        
        // Process each date
        foreach ($dateRange as $dateObj) {
            $date = $dateObj->toDateString();
            $isFound = null;
            
            // Initialize default row data
            $rowData = [
                'status' => '-',
                'status_id' => '-',
                'status_code' => '-',
                'statusColor' => '#BDBDBD',
                'checkInTime' => null,
                'checkOutTime' => null,
                'updatedBy' => null,
                'workingHour' => null,
                'OT' => null,
                'earlyExit' => null,
                'late' => null,
                'attendance_remark' => '--',
                'checkInLocation' => '--',
                'checkOutLocation' => '--',
                'checkInPhoto' => [],
                'checkOutPhoto' => [],
                'atd_segments' => [],
                'presentCount' => 0,
                'weekOffPresentCount' => 0,
                'leaveCount' => 0,
                'holidayCount' => 0,
                'weekOffCount' => 0,
                'absentCount' => 0,
                'halfDayCount' => 0,
                'missedPunchCount' => 0,
                'overtimeCount' => 0,
                'lateCount' => 0,
                'earlyExitCount' => 0,
                'approvedLeaveCount' => 0,
                'approvedMissedPunchCount' => 0,
                'isApproved' => null,
                'previousCheckInTime' => null,
                'previousCheckOutTime' => null,
                'previousLate' => null,
                'previousExit' => null,
                'UPL' => 0,
            ];
            
            if ($employee->emp_date_of_joining > $date) {
                $rowData['date'] = $date;
                $attendanceData[] = $rowData;
                continue;
            }
            
            // Check if sandwich rule applies
            if (isset($sandwichRuleApplied[$date])) {
                $sandwichStatus = $sandwichRuleApplied[$date];
                $isFound = 1;
                
                if ($sandwichStatus === 'UPL') {
                    $rowData['status'] = $uplStatus->m_name;
                    $rowData['status_id'] = $uplStatus->m_id;
                    $rowData['status_code'] = $uplStatus->m_type;
                    $rowData['statusColor'] = json_decode($uplStatus->m_other, true)['color'];
                    $rowData['UPL'] = 1;
                } elseif ($sandwichStatus === 'ABS') {
                    $rowData['absentCount'] = 1;
                    $rowData['status'] = $absentStatus->m_name;
                    $rowData['status_id'] = $absentStatus->m_id;
                    $rowData['status_code'] = $absentStatus->m_type;
                    $rowData['statusColor'] = json_decode($absentStatus->m_other, true)['color'];
                } else {
                    // Apply leave status (SL/CL)
                    $leaveStatus = $leaveCategories->firstWhere('m_type', $sandwichStatus);
                    if ($leaveStatus) {
                        $rowData['leaveCount'] = 1;
                        $rowData['status'] = $leaveStatus->m_name;
                        $rowData['status_id'] = $leaveStatus->m_id;
                        $rowData['status_code'] = $leaveStatus->m_type;
                        $rowData['statusColor'] = json_decode($leaveStatus->m_other, true)['color'];
                    }
                }
                
                $rowData['checkInTime'] = '-';
                $rowData['checkOutTime'] = '-';
                $rowData['workingHour'] = '-';
            } else {
                // Get all relevant records for this date
                $attendanceRecord = $attendanceRecords[$date] ?? null;
                $attendanceLog = $attendanceLogs[$date] ?? null;
                $missedPunch = $missedPunches[$date] ?? null;
                $holidayRecord = $holiday_record_exits[$date] ?? null;
                $leaveRecords = $leaveByDate[$date] ?? null;
                
                // Check for UPL status first
                if ($attendanceRecord && $attendanceRecord->atd_attendance_status == 215) {
                    $rowData['status'] = $uplStatus->m_name;
                    $rowData['status_id'] = $uplStatus->m_id;
                    $rowData['status_code'] = $uplStatus->m_type;
                    $rowData['statusColor'] = json_decode($uplStatus->m_other, true)['color'];
                    $rowData['UPL'] = 1;
                    $isFound = 1;
                }
                
                // Process attendance record if exists
                if ($attendanceRecord && $attendanceRecord->fh_attendance_status && !$isFound) {
                    self::processAttendanceRecord($rowData, $attendanceRecord, $missedPunch, $attendanceLog);
                    $isFound = 1;
                }
                
                // Process leave records if no attendance found or half day
                if (is_null($isFound) || ($attendanceRecord && $attendanceRecord->atd_attendance_status == 252)) {
                    self::processLeaveRecords($rowData, $leaveRecords, $attendanceRecord, $leaveCategories);
                    $isFound = 1;
                }
                
                // Process missed punch
                if (is_null($isFound) || ($attendanceRecord && ($attendanceRecord->atd_check_in_time || $attendanceRecord->atd_check_out_time))) {
                    self::processMissedPunch($rowData, $missedPunch, $leaveRecords, $employee, $date, $halfDayStatus, $presentStatus);
                    $isFound = 1;
                }
                
                // Process holiday
                if (is_null($isFound) && $holidayRecord) {
                    $rowData['holidayCount'] = 1;
                    $rowData['status'] = $holidayStatus->m_name;
                    $rowData['status_id'] = $holidayStatus->m_id;
                    $rowData['status_code'] = $holidayStatus->m_type;
                    $rowData['statusColor'] = json_decode($holidayStatus->m_other, true)['color'];
                    $rowData['checkInTime'] = '-';
                    $rowData['checkOutTime'] = '-';
                    $rowData['workingHour'] = '-';
                    $isFound = 1;
                }
                
                // Process week off
                if (is_null($isFound) && in_array($date, $weekOfDates)) {
                    $rowData['weekOffCount'] = 1;
                    $rowData['status'] = $weekOffStatus->m_name;
                    $rowData['status_id'] = $weekOffStatus->m_id;
                    $rowData['status_code'] = $weekOffStatus->m_type;
                    $rowData['statusColor'] = json_decode($weekOffStatus->m_other, true)['color'];
                    $rowData['checkInTime'] = '-';
                    $rowData['checkOutTime'] = '-';
                    $rowData['workingHour'] = '-';
                    $isFound = 1;
                }
                
                // Process absent
                if (is_null($isFound) && Carbon::parse($date)->isPast()) {
                    $rowData['absentCount'] = 1;
                    $rowData['status'] = $absentStatus->m_name;
                    $rowData['status_id'] = $absentStatus->m_id;
                    $rowData['status_code'] = $absentStatus->m_type;
                    $rowData['statusColor'] = json_decode($absentStatus->m_other, true)['color'];
                    $rowData['checkInTime'] = '-';
                    $rowData['checkOutTime'] = '-';
                    $rowData['workingHour'] = '-';
                    $isFound = 1;
                }
            }
            
            if ($rowData['status_id']) {
                $rowData['status_id'] = (string)$rowData['status_id'];
            }
            
            $rowData['date'] = $date;
            $attendanceData[] = $rowData;
        }
        
        // Cache the final result for 30 minutes
        Cache::put($cacheKey, $attendanceData, 1800);
        
        return $attendanceData;
    }

    // Helper methods to process different record types
    private static function processAttendanceRecord(&$rowData, $attendanceRecord, $missedPunch, $attendanceLog)
    {
        if ($attendanceRecord->atd_attendance_status == 252) { // Half Day
            $rowData['halfDayCount'] = 1;
            $rowData['presentCount'] = 0.5;
            $rowData['status_id'] = $attendanceRecord->fh_attendance_status->m_id;
            $rowData['status_code'] = $attendanceRecord->fh_attendance_status->m_type;
        } else {
            $rowData['status_id'] = $attendanceRecord->fh_attendance_status->m_id;
            $rowData['status_code'] = $attendanceRecord->fh_attendance_status->m_type;
            
            if ($attendanceRecord->atd_attendance_status == 251) {
                $rowData['presentCount'] = 1; // Present
            }
            
            if (!$missedPunch && $attendanceRecord->atd_attendance_status == 251) {
                $rowData['presentCount'] = 1;
            } else if ($attendanceRecord->atd_attendance_status == 320) {
                $rowData["weekOffPresentCount"] = 1;
            }
        }
        
        if ($attendanceRecord->atd_is_overtime == 1) {
            $rowData['overtimeCount'] = 1;
        }
        
        if ($attendanceRecord->atd_is_late == 1) {
            $rowData['lateCount'] = 1;
        }
        
        if ($attendanceRecord->atd_is_early_exit == 1) {
            $rowData['earlyExitCount'] = 1;
        }
        
        $rowData['status'] = $attendanceRecord->fh_attendance_status->m_name;
        $rowData['statusColor'] = json_decode($attendanceRecord->fh_attendance_status->m_other, true)['color'];
        $rowData['checkInTime'] = $attendanceRecord->atd_check_in_time ? Carbon::parse($attendanceRecord->atd_check_in_time)->format('H:i') : '-';
        $rowData['checkOutTime'] = $attendanceRecord->atd_check_out_time ? Carbon::parse($attendanceRecord->atd_check_out_time)->format('H:i') : '-';
        $rowData['workingHour'] = $attendanceRecord->atd_total_worked_hours ? number_format($attendanceRecord->atd_total_worked_hours, 2) : '-';
        $rowData['earlyExit'] = ($attendanceRecord->atd_is_early_exit && $attendanceRecord->atd_early_exit_duration) ? number_format($attendanceRecord->atd_early_exit_duration, 2) : null;
        $rowData['late'] = ($attendanceRecord->atd_is_late && $attendanceRecord->atd_late_duration) ? number_format($attendanceRecord->atd_late_duration, 2) : null;
        $rowData['OT'] = ($attendanceRecord->atd_is_overtime && $attendanceRecord->atd_overtime_hours) ? number_format($attendanceRecord->atd_overtime_hours, 2) : null;
        $rowData['attendance_remark'] = $attendanceRecord->atd_remark ? $attendanceRecord->atd_remark : '-';
        $rowData['checkInLocation'] = $attendanceRecord->atd_punchin_location ? $attendanceRecord->atd_punchin_location : '--';
        $rowData['checkOutLocation'] = $attendanceRecord->atd_punchout_location ? $attendanceRecord->atd_punchout_location : '--';
        $rowData['checkInPhoto'] = $attendanceRecord->atd_punchin_photo ? json_decode($attendanceRecord->atd_punchin_photo) : [];
        $rowData['checkOutPhoto'] = $attendanceRecord->atd_punchout_photo ? json_decode($attendanceRecord->atd_punchout_photo) : [];
        $rowData['atd_segments'] = $attendanceRecord->atd_segments ? [json_decode($attendanceRecord->atd_segments)] : [];
        $rowData['updatedBy'] = $attendanceRecord->updated_by ? $attendanceRecord->updated_by->emp_full_name : '-';
        
        if ($attendanceLog) {
            $rowData['previousCheckInTime'] = $attendanceLog->al_check_in_time ? Carbon::parse($attendanceLog->al_check_in_time)->format('H:i') : '-';
            $rowData['previousCheckOutTime'] = $attendanceLog->al_check_out_time ? Carbon::parse($attendanceLog->al_check_out_time)->format('H:i') : '-';
            $rowData['previousLate'] = ($attendanceLog->al_is_late && $attendanceLog->al_late_duration) ? number_format($attendanceLog->al_late_duration, 2) : null;
            $rowData['previousExit'] = ($attendanceLog->al_is_early_exit && $attendanceLog->al_early_exit_duration) ? number_format($attendanceLog->al_early_exit_duration, 2) : null;
        }
    }

    private static function processLeaveRecords(&$rowData, $leaveRecords, $attendanceRecord, $leaveCategories)
    {
        if (!$leaveRecords || count($leaveRecords) === 0) {
            return;
        }
        
        $halfDayPresent = ($attendanceRecord && $attendanceRecord->atd_attendance_status == 252);
        $leaveCount = count($leaveRecords);
        
        if ($leaveCount == 1) {
            $leave = $leaveRecords[0];
            $isApproved = $leave->lvr_status != 170 && $leave->lvr_stage_completed;
            $isUnpaid = $leave->fh_leave_cat_type->m_id == 215;
            $isHalfDayLeave = optional($leave->fh_leave_day_type)->m_id != 201;
            $segmentId = $leave->fh_leave_day_segment->m_id ?? null;
            
            $rowData['leaveCount'] = $isHalfDayLeave ? 0.5 : 1;
            $rowData['approvedLeaveCount'] = ($isApproved && !$isUnpaid) ? $rowData['leaveCount'] : 0;
            $rowData['isApproved'] = $isApproved;
            
            if (isset($leaveCategories[$leave->fh_leave_cat_type->m_id]) && $isApproved) {
                // Half-day present + Half-day leave case
                if ($halfDayPresent && $isHalfDayLeave) {
                    self::processHalfDayPresentLeave($rowData, $attendanceRecord, $leave, $segmentId);
                }
                // Half-day SL/UPL or CL/UPL case
                elseif ($leave->lvr_total_leave_days == 0.5 && Carbon::now()->format('Y-m-d') > $rowData['date']) {
                    self::processHalfDayUnpaidLeave($rowData, $leave, $leaveCategories);
                }
                // Only single full/half leave case
                elseif (!$halfDayPresent) {
                    self::processSingleLeave($rowData, $leave);
                }
            }
        } elseif ($leaveCount == 2) {
            self::processTwoLeaves($rowData, $leaveRecords);
        }
    }

    private static function processHalfDayPresentLeave(&$rowData, $attendanceRecord, $leave, $segmentId)
    {
        $presentStatus = $attendanceRecord->fh_attendance_status;
        
        if ($segmentId == 236) { // 2nd Half Leave
            $rowData['status_id'] = $presentStatus->m_id . '/' . $leave->fh_leave_cat_type->m_id;
            $rowData['status'] = $presentStatus->m_name . '/' . $leave->fh_leave_cat_type->m_name;
            $rowData['status_code'] = $presentStatus->m_type . '/' . $leave->fh_leave_cat_type->m_type;
            $rowData['statusColor'] = json_decode($presentStatus->m_other, true)['color'] . '/' . json_decode($leave->fh_leave_cat_type->m_other, true)['color'];
        } elseif ($segmentId == 235) { // 1st Half Leave
            $rowData['status_id'] = $leave->fh_leave_cat_type->m_id . '/' . $presentStatus->m_id;
            $rowData['status'] = $leave->fh_leave_cat_type->m_name . '/' . $presentStatus->m_name;
            $rowData['status_code'] = $leave->fh_leave_cat_type->m_type . '/' . $presentStatus->m_type;
            $rowData['statusColor'] = json_decode($leave->fh_leave_cat_type->m_other, true)['color'] . '/' . json_decode($presentStatus->m_other, true)['color'];
        }
        
        $isUnpaid = $leave->fh_leave_cat_type->m_id == 215;
        $rowData['presentCount'] = $isUnpaid ? 0.5 : 1;
        $rowData['leaveCount'] = 0.5;
        $rowData['approvedLeaveCount'] = $isUnpaid ? 0 : 0.5;
    }

    private static function processHalfDayUnpaidLeave(&$rowData, $leave, $leaveCategories)
    {
        $uplStatus = $leaveCategories[215] ?? null;
        if ($uplStatus) {
            $rowData['status_id'] = $leave->fh_leave_cat_type->m_id . '/' . $uplStatus->m_id;
            $rowData['status'] = $leave->fh_leave_cat_type->m_name . '/' . $uplStatus->m_name;
            $rowData['status_code'] = $leave->fh_leave_cat_type->m_type . '/' . $uplStatus->m_type;
            $rowData['statusColor'] = json_decode($leave->fh_leave_cat_type->m_other, true)['color'] . '/' . json_decode($uplStatus->m_other, true)['color'];
        }
        
        $rowData['presentCount'] = 0.5;
        $rowData['leaveCount'] = 1;
        $rowData['approvedLeaveCount'] = $leave->fh_leave_cat_type->m_id != 215 ? 0.5 : 0;
    }

    private static function processSingleLeave(&$rowData, $leave)
    {
        $rowData['status_id'] = $leave->fh_leave_cat_type->m_id;
        $rowData['status'] = $leave->fh_leave_cat_type->m_name;
        $rowData['status_code'] = $leave->fh_leave_cat_type->m_type;
        $rowData['statusColor'] = json_decode($leave->fh_leave_cat_type->m_other, true)['color'];
        
        $isUnpaid = $leave->fh_leave_cat_type->m_id == 215;
        $rowData['presentCount'] = $isUnpaid ? 0 : $rowData['leaveCount'];
    }

    private static function processTwoLeaves(&$rowData, $leaveRecords)
    {
        $leaves = collect($leaveRecords)->sortBy(function ($l) {
            return optional($l->fh_leave_day_segment)->m_id ?? 0;
        })->values();
        
        $l1 = $leaves[0];
        $l2 = $leaves[1];
        
        $isApproved1 = $l1->lvr_status != 170 && $l1->lvr_stage_completed;
        $isApproved2 = $l2->lvr_status != 170 && $l2->lvr_stage_completed;
        
        if ($isApproved1 || $isApproved2) {
            $rowData['status_id'] = $l1->fh_leave_cat_type->m_id . '/' . $l2->fh_leave_cat_type->m_id;
            $rowData['status'] = $l1->fh_leave_cat_type->m_name . '/' . $l2->fh_leave_cat_type->m_name;
            $rowData['status_code'] = $l1->fh_leave_cat_type->m_type . '/' . $l2->fh_leave_cat_type->m_type;
            $rowData['statusColor'] = json_decode($l1->fh_leave_cat_type->m_other, true)['color'] . '/' . json_decode($l2->fh_leave_cat_type->m_other, true)['color'];
            
            $rowData['leaveCount'] = 1;
            $rowData['approvedLeaveCount'] = 0;
            $rowData['presentCount'] = 0;
            
            if ($isApproved1 && $l1->fh_leave_cat_type->m_id != 215) {
                $rowData['presentCount'] += 0.5;
                $rowData['approvedLeaveCount'] += 0.5;
            }
            
            if ($isApproved2 && $l2->fh_leave_cat_type->m_id != 215) {
                $rowData['presentCount'] += 0.5;
                $rowData['approvedLeaveCount'] += 0.5;
            }
            
            $rowData['isApproved'] = $isApproved1 && $isApproved2;
        }
    }

    private static function processMissedPunch(&$rowData, $missedPunch, $leaveRecords, $employee, $date, $halfDayStatus, $presentStatus)
    {
        if (!$missedPunch) {
            return;
        }
        
        $rowData['missedPunchCount'] = 1;
        $rowData['checkInTime'] = Carbon::createFromFormat('H:i:s', $missedPunch->ae_in_time)->format('h:i A');
        $rowData['checkOutTime'] = Carbon::createFromFormat('H:i:s', $missedPunch->ae_out_time)->format('h:i A');
        $rowData['workingHour'] = number_format($missedPunch->ae_total_working, 2);
        
        if ($missedPunch->ae_status != 170 && $missedPunch->ae_stage_completed) {
            $shiftStartTime = $employee->fh_shift_type->pst_start_time ?? '09:00:00';
            $shiftEndTime = $employee->fh_shift_type->pst_end_time ?? '18:00:00';
            
            $pstStartTime = Carbon::parse($date . ' ' . $shiftStartTime);
            $pstEndTime = Carbon::parse($date . ' ' . $shiftEndTime);
            $dailyWorkingHours = $pstStartTime->diffInMinutes($pstEndTime);
            
            $checkInTime = Carbon::parse($date . ' ' . $missedPunch->ae_in_time);
            $checkOutTime = Carbon::parse($date . ' ' . $missedPunch->ae_out_time);
            $workedDuration = $checkInTime->diffInMinutes($checkOutTime);
            
            $fullDayThreshold = $dailyWorkingHours;
            $halfDayThreshold = $dailyWorkingHours / 2;
            
            if ($workedDuration >= $fullDayThreshold) {
                $rowData['presentCount'] = 1;
                $rowData['status_id'] = $presentStatus->m_id;
                $rowData['status'] = $presentStatus->m_name;
                $rowData['status_code'] = $presentStatus->m_type;
                $rowData['statusColor'] = json_decode($presentStatus->m_other, true)['color'];
                $rowData['approvedMissedPunchCount'] = 0.5;
            } elseif ($workedDuration >= $halfDayThreshold) {
                self::processHalfDayMissedPunch($rowData, $leaveRecords, $halfDayStatus);
            }
        } else {
            $missedPunchStatus = MasterTable::where('m_id', 228)->first();
            $rowData['status_id'] = $missedPunchStatus->m_id;
            $rowData['status'] = $missedPunchStatus->m_name;
            $rowData['status_code'] = $missedPunchStatus->m_type;
            $rowData['statusColor'] = json_decode($missedPunchStatus->m_other, true)['color'];
        }
    }

    private static function processHalfDayMissedPunch(&$rowData, $leaveRecords, $halfDayStatus)
    {
        if ($leaveRecords && count($leaveRecords) == 1) {
            $leave = $leaveRecords[0];
            if (optional($leave->fh_leave_day_segment)->m_id == 236) {
                $rowData['status_id'] = $halfDayStatus->m_id . '/' . $leave->fh_leave_cat_type->m_id;
                $rowData['status'] = $halfDayStatus->m_name . '/' . $leave->fh_leave_cat_type->m_name;
                $rowData['status_code'] = $halfDayStatus->m_type . '/' . $leave->fh_leave_cat_type->m_type;
                $rowData['statusColor'] = json_decode($halfDayStatus->m_other, true)['color'] . '/' . json_decode($leave->fh_leave_cat_type->m_other, true)['color'];
            } elseif (optional($leave->fh_leave_day_segment)->m_id == 235) {
                $rowData['status_id'] = $leave->fh_leave_cat_type->m_id . '/' . $halfDayStatus->m_id;
                $rowData['status'] = $leave->fh_leave_cat_type->m_name . '/' . $halfDayStatus->m_name;
                $rowData['status_code'] = $leave->fh_leave_cat_type->m_type . '/' . $halfDayStatus->m_type;
                $rowData['statusColor'] = json_decode($leave->fh_leave_cat_type->m_other, true)['color'] . '/' . json_decode($halfDayStatus->m_other, true)['color'];
            }
        }
    }

    // Cache clearing utility methods
    public static function clearAttendanceCache($employeeId, $year, $month)
    {
        $cacheKey = "attendance_details_{$employeeId}_{$year}_{$month}";
        $empDataCacheKey = "emp_data_{$employeeId}_{$year}_{$month}";
        
        Cache::forget($cacheKey);
        Cache::forget($empDataCacheKey);
        
        return true;
    }

    public static function clearAllAttendanceCache()
    {
        // You might want to implement more specific cache clearing logic
        // depending on your cache driver
        Cache::flush();
        
        return true;
    }

    public static function sendemailcandidate($candidateTemplate, array $candidatePlaceholders, $candidate, $businessId, $attachment = null)
    {
        if (!$candidateTemplate) return false;

        $mailBody = str_replace(array_keys($candidatePlaceholders), array_values($candidatePlaceholders), $candidateTemplate->mt_body);
        $mailSubject = str_replace(array_keys($candidatePlaceholders), array_values($candidatePlaceholders), $candidateTemplate->mt_title);

        try {
            Mail::send([], [], function ($message) use ($candidate, $mailSubject, $mailBody, $attachment) {
                $message->to($candidate)
                    ->subject($mailSubject)
                    ->html($mailBody);
                if ($attachment) {
                    $message->attach($attachment['path'], [
                        'as' => $attachment['name'],
                        'mime' => $attachment['mime'],
                    ]);
                }
            });
        } catch (\Exception $e) {
            \Log::error("Candidate Email Sending Failed: " . $e->getMessage());
            return false;
        }

        return true;
    }

    public static function sendemailinteviver($interviewerTemplate, array $interviewerPlaceholders, $manager_email, $businessId, $attachment = null)
    {
        if (!$interviewerTemplate) return false;

        $mailBody = str_replace(array_keys($interviewerPlaceholders), array_values($interviewerPlaceholders), $interviewerTemplate->mt_body);
        $mailSubject = str_replace(array_keys($interviewerPlaceholders), array_values($interviewerPlaceholders), $interviewerTemplate->mt_title);

        try {
            Mail::send([], [], function ($message) use ($manager_email, $mailSubject, $mailBody, $attachment) {
                $message->to($manager_email)
                    ->subject($mailSubject)
                    ->html($mailBody);
                if ($attachment) {
                    $message->attach($attachment['path'], [
                        'as' => $attachment['name'],
                        'mime' => $attachment['mime'],
                    ]);
                }
            });
        } catch (\Exception $e) {
            \Log::error("Candidate Email Sending Failed: " . $e->getMessage());
            return false;
        }

        return true;
    }
    
    public static function getCompOffQuantity($workHrs, $conditions) {
        foreach ($conditions as $condition) {
            $work_duration = (float)$condition['work_duration'];
            $operator = $condition['operator'];
            $co_quantity = (float)$condition['co_quantity'];

            $match = false;
            switch ($operator) {
                case '=':
                    $match = $workHrs == $work_duration;
                    break;
                case '>':
                    $match = $workHrs > $work_duration;
                    break;
                case '<':
                    $match = $workHrs < $work_duration;
                    break;
                case '>=':
                    $match = $workHrs >= $work_duration;
                    break;
                case '<=':
                    $match = $workHrs <= $work_duration;
                    break;
            }

            if ($match) {
                return $co_quantity;
            }
        }
        return 0; // or null if no condition matches
    }

    public static function generateCompOff($employee, $date, $canUpdate = 0)
    {
        $user = Auth::user();
        $in_time = $out_time = $p_id = null;
        $exception_data = AttendanceException::where([['ae_b_id', $employee->emp_b_id], ['ae_date', $date], ['ae_emp_id', $employee->emp_id]])->orderByDesc('ae_id')->first();
        $log_data = AttendanceLog::where([['al_b_id', $employee->emp_b_id], ['al_date', $date], ['al_emp_id', $employee->emp_id]])->orderByDesc('al_id')->first();
        $atd_data = AttendanceRecord::where([['atd_emp_id', $employee->emp_id], ['atd_date', $date], ['atd_b_id', $employee->emp_b_id]])->orderByDesc('atd_id')->first();
        if ($exception_data) {
            $in_time = $exception_data->ae_in_time;
            $out_time = $exception_data->ae_out_time;
            $p_id = $exception_data->ae_id;
        } else if ($log_data) {
            $in_time = $log_data->al_check_in_time;
            $out_time = $log_data->al_check_out_time;
            $p_id = $log_data->al_id;
        } else if ($atd_data) {
            $in_time = $atd_data->atd_check_in_time;
            $out_time = $atd_data->atd_check_out_time;
            $p_id = $atd_data->atd_id;
        } else {
            return; // No attendance data available
        }

        $in_time = $in_time ? Carbon::parse($in_time) : null;
        $out_time = $out_time ? Carbon::parse($out_time) : null;

        // Bail out early if either timestamp is unavailable to avoid invalid diffs
        if (!$in_time || !$out_time) {
            return;
        }

        $compOffPolicy = CompOffPolicy::with("duration_conditions")
            ->where('cop_b_id', $employee->emp_b_id)
            ->where('cop_effective_date', '<=', $date)
            ->where('cop_status', 1)
            ->orderBy('cop_effective_date', 'desc')
            ->first();

        if (isset($compOffPolicy) && $compOffPolicy) {
            $workedHrs = $in_time->diffInHours($out_time);
            // Apply Comp Off Policy Logic
            if ($workedHrs) {
                $comp_off_quantity = CentralLogics::getCompOffQuantity($workedHrs, $compOffPolicy->duration_conditions);

                if ($comp_off_quantity) {
                    $credit_date = Carbon::now()->format('Y-m-d');
                    // Check for existing CompOff entry before creating
                    $existingCompOff = CompOff::where([['co_request_date', $date], ['co_emp_id', $employee->emp_id], ['co_b_id', $employee->emp_b_id]])->orderByDesc('co_id')->first();

                    if (!$existingCompOff) {
                        try {
                            $co_request = CompOff::create(
                                [
                                    'co_b_id' => $employee->emp_b_id,
                                    'cop_id' => $compOffPolicy->cop_id,
                                    'co_emp_id' => $employee->emp_id,
                                    'co_record_id' => $p_id,
                                    'co_request_date' => $date,
                                    'co_credit_date' => $credit_date,
                                    'co_is_expiry' => 0,
                                    'co_alloted' => $comp_off_quantity,
                                ]
                            );

                            // ✅ Generate and update co_code after creation
                            if ($co_request) {
                                $co_request->co_code = 'CO' . Carbon::parse($co_request->co_request_date)->format('Ymd') . '-' . $co_request->co_id;
                                $co_request->update();
                            }
                        } catch (\Exception $e) {
                            Log::error('CompOff creation failed: ' . $e->getMessage());
                        }
                    }

                    $remaining = CompOffBalance::where('cb_emp_id', $employee->emp_id)->where('cb_year', date('Y', strtotime($date)))->where('cb_month', date('m', strtotime($date)))->orderBy('cb_id', 'desc')->first();
                    $co_balance = $remaining ? (float) $remaining->cb_alloted : 0;
                    $co_remaining_balance = $remaining ? (float) $remaining->cb_balance_remaining : 0;
                    if ($existingCompOff && $canUpdate) {
                        $co_balance -= $existingCompOff->co_alloted;
                        $co_remaining_balance -= $existingCompOff->co_alloted;
                    }

                    $requestMonthStart = Carbon::parse($date)->startOfMonth();
                    $creditMonthStart = Carbon::parse($credit_date)->startOfMonth();
                    $baseAlloted = $comp_off_quantity + $co_balance;
                    $baseRemaining = $comp_off_quantity + $co_remaining_balance;

                    // Always update the month of the attendance date
                    CompOffBalance::updateOrCreate(
                        [
                            'cb_b_id' => $employee->emp_b_id,
                            'cb_emp_id' => $employee->emp_id,
                            'cb_year' => $requestMonthStart->format('Y'),
                            'cb_month' => $requestMonthStart->format('m'),
                        ],
                        [
                            'cb_alloted' => $baseAlloted,
                            'cb_balance_remaining' => $baseRemaining,
                        ]
                    );

                    // If crediting in a different month, ensure subsequent months have an entry
                    $monthCursor = $requestMonthStart->copy()->addMonth();
                    while ($monthCursor->lte($creditMonthStart)) {
                        $cuMonthBalance = CompOffBalance::where([
                            'cb_b_id' => $employee->emp_b_id,
                            'cb_emp_id' => $employee->emp_id,
                            'cb_year' => $monthCursor->format('Y'),
                            'cb_month' => $monthCursor->format('m'),
                        ])->first();
                        if ($cuMonthBalance) {
                            $coAlloted = $cuMonthBalance->cb_alloted;
                            $coBalReim = $cuMonthBalance->cb_balance_remaining;
                            $cuMonthBalance->update(['cb_alloted' => $coAlloted + $comp_off_quantity, 'cb_balance_remaining' => $coBalReim + $comp_off_quantity]);
                        } else {
                            CompOffBalance::create(
                                    [
                                        'cb_b_id' => $employee->emp_b_id,
                                        'cb_emp_id' => $employee->emp_id,
                                        'cb_year' => $monthCursor->format('Y'),
                                        'cb_month' => $monthCursor->format('m'),
                                        'cb_alloted' => $comp_off_quantity,
                                        'cb_balance_remaining' => $comp_off_quantity,
                                    ]
                                );
                            }
                        $monthCursor->addMonth();
                    }

                    if ($existingCompOff && $canUpdate) {
                        $existingCompOff->update(['co_alloted' => $comp_off_quantity]);
                    }
                }
            }
        }
    }

    public static function newGetMonthlyAttendanceReport($employee, $month, $year, $holidayRecords, $weekOffDates)
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $dateRange = $startDate->toPeriod($endDate);
        $currentMonth = (int) date('m');
        $absentStatus = MasterTable::where('m_id', 203)->first();
        $uplStatus = MasterTable::where('m_id', 215)->first(); // UPL status

        // Preload everything to avoid repeated DB hits
        $attendanceRecords = $employee->attendance_record()
            ->whereBetween('atd_date', [$startDate, $endDate])
            ->get()
            ->keyBy(fn($row) => Carbon::parse($row->atd_date)->toDateString());

        $attendanceLogs = AttendanceLog::where('al_emp_id', $employee->emp_id)
            ->whereBetween('al_date', [$startDate, $endDate])
            ->get()
            ->keyBy(fn($row) => Carbon::parse($row->al_date)->toDateString());

        $missedPunches = AttendanceException::where('ae_b_id', $employee->emp_b_id)
            ->where('ae_emp_id', $employee->emp_id)
            ->whereBetween('ae_date', [$startDate, $endDate])
            ->get()
            ->keyBy(fn($row) => Carbon::parse($row->ae_date)->toDateString());

        $leave_record_exits = $employee->leave_requests()->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('lvr_start_date', [$startDate, $endDate])
            ->orWhereBetween('lvr_end_date', [$startDate, $endDate]);
        })->get();

        $leaveByDate = collect();

        foreach ($leave_record_exits as $leave) {
            $start = Carbon::parse($leave->lvr_start_date);
            $end = Carbon::parse($leave->lvr_end_date);

            while ($start->lte($end)) {
                $dateStr = $start->toDateString();
                if (!$leaveByDate->has($dateStr)) {
                    $leaveByDate->put($dateStr, collect());
                }
                $leaveByDate->get($dateStr)->push($leave);
                $start->addDay();
            }
        }

        $leave_record_exits = $leaveByDate;

        $attendanceData = [];
        $sandwichRuleApplied = []; // Track which WO/HO are converted due to sandwich rule

        // First pass: Collect all dates and identify sandwich situations
        $allDates = [];
        foreach ($dateRange as $dateObj) {
            $allDates[] = $dateObj->toDateString();
        }

        // Helper function to check if a date has ABS
        $isAbsent = function($date) use ($attendanceRecords, $leave_record_exits, $holidayRecords, $weekOffDates, $employee, $absentStatus) {
            if ($employee->emp_date_of_joining > $date) {
                return false;
            }

            $attendance_record_exit = $attendanceRecords[$date] ?? null;
            $leave_record_exit = $leave_record_exits->get($date) ?? null;
            $holiday_record_exit = $holidayRecords[$date] ?? null;

            // Check for Absent (not WO, not HO, not present, and date is past)
            if (!$attendance_record_exit && !$leave_record_exit && !$holiday_record_exit && 
                !in_array($date, $weekOffDates) && Carbon::parse($date)->isPast()) {
                return 203; // ABS
            }

            return false;
        };

        foreach ($allDates as $index => $date) {
            $normalizedDate = Carbon::parse($date)->toDateString(); 
            
            $isInWeekOfDates = in_array($normalizedDate, $weekOffDates);
            $isInHolidayRecord = array_key_exists($normalizedDate, $holidayRecords->toArray());
            $isCurrentWOOrHO = $isInWeekOfDates || $isInHolidayRecord;
            
            // Only proceed if the date is WO/HO and not already processed
            if ($isCurrentWOOrHO && !isset($sandwichRuleApplied[$date])) {
                $prevDate = $index > 0 ? $allDates[$index - 1] : null;
                $nextDate = $index < count($allDates) - 1 ? $allDates[$index + 1] : null;
                
                // Check if both adjacent days are absents AND they are consecutive
                $prevStatus = $prevDate ? $isAbsent($prevDate) : false;
                $nextStatus = $nextDate ? $isAbsent($nextDate) : false;
                
                // Only apply sandwich rule if BOTH adjacent days are absents
                if ($prevStatus && $nextStatus && $prevStatus == 203 && $nextStatus == 203) {
                    $sandwichRuleApplied[$date] = 203;
                }
            }
        }

        // Second pass: Generate attendance data
        foreach ($dateRange as $dateObj) {
            $date = $dateObj->toDateString();
            $is_found = null;
            $rowData = [
                'status' => '-',
                'status_id' => '-',
                'status_code' => '-',
                'statusColor' => '#BDBDBD',
                'checkInTime' => null,
                'checkOutTime' => null,
                'updatedBy' => null,
                'workingHour' => null,
                'OT' => null,
                'earlyExit' => null,
                'late' => null,
                'attendance_remark' => '--',
                'mark_as_absent' => '--',
                'checkInLocation' => '--',
                'checkOutLocation' => '--',
                'checkInPhoto' => [],
                'checkOutPhoto' => [],
                'atd_segments' => [],
                'presentCount' => 0,
                'holidayPresentCount' => 0,
                'weekOffPresentCount' => 0,
                'leaveCount' => 0,
                'holidayCount' => 0,
                'weekOffCount' => 0,
                'absentCount' => 0,
                'halfDayCount' => 0,
                'missedPunchCount' => 0,
                'overtimeCount' => 0,
                'lateCount' => 0,
                'earlyExitCount' => 0,
                'approvedLeaveCount' => 0,
                'approvedMissedPunchCount' => 0,
                'isApproved' => null,
                'previousCheckInTime' => null,
                'previousCheckOutTime' => null,
                'previousLate' => null,
                'previousExit' => null,
                'UPL' => 0,
            ];
            
            // Skip dates before joining or after last working day
            $shouldCountDate = true;
            if ($employee->emp_date_of_joining && $dateObj->lt(Carbon::parse($employee->emp_date_of_joining))) {
                $shouldCountDate = false;
            }
            if ($employee->emp_last_working_day && $dateObj->gt(Carbon::parse($employee->emp_last_working_day))) {
                $shouldCountDate = false;
            }

            if ($shouldCountDate && $employee->emp_date_of_joining <= $date) {
                // Check if sandwich rule applies to this date
                if (isset($sandwichRuleApplied[$date])) {
                    $sandwichStatus = $sandwichRuleApplied[$date];
                    $is_found = 1;

                    if ($sandwichStatus === 203) {
                        $rowData['absentCount'] = 1;
                        $rowData['status'] = $absentStatus->m_name;
                        $rowData['status_id'] = $absentStatus->m_id;
                        $rowData['status_code'] = $absentStatus->m_type;
                        $rowData['statusColor'] = json_decode($absentStatus->m_other, true)['color'];
                    }

                    $rowData['checkInTime'] = '-';
                    $rowData['checkOutTime'] = '-';
                    $rowData['workingHour'] = '-';
                } else {
                    // Original logic continues here...
                    $attendance_record_exit = $attendanceRecords[$date] ?? null;
                    $attendance_log = $attendanceLogs[$date] ?? null;
                    $missedPunch = $missedPunches[$date] ?? null;
                    $holiday_record_exit = $holidayRecords[$date] ?? null;
                    $leave_record_exit = $leave_record_exits->get($date) ?? null;

                    $is_found = null;

                    // Check for UPL status first (atd_attendance_status == 215)
                    if ($attendance_record_exit && $attendance_record_exit->atd_attendance_status == 215) {
                        $rowData['status'] = $uplStatus->m_name;
                        $rowData['status_id'] = $uplStatus->m_id;
                        $rowData['status_code'] = $uplStatus->m_type;
                        $rowData['statusColor'] = json_decode($uplStatus->m_other, true)['color'];
                        $rowData['UPL'] = 1;
                        $is_found = 1;
                    }

                    // Priority 1: Check AttendanceException first
                    if (isset($missedPunch) && !empty($missedPunch) && !$is_found) {
                        $missedPunchStatus = MasterTable::where('m_id', 228)->first(); //Missedpunch
                        $is_found = 1;
                        $rowData['missedPunchCount'] =  1;

                        // Check if missed punch is approved
                        $isApprovedMissedPunch = ($missedPunch->ae_status == 157 && $missedPunch->ae_stage_completed == 1);
                        if ($isApprovedMissedPunch) {
                            // Show approved missed punch times
                            $rowData['checkInTime'] = Carbon::createFromFormat('H:i:s', $missedPunch->ae_in_time)->format('H:i');
                            $rowData['checkOutTime'] =  Carbon::createFromFormat('H:i:s', $missedPunch->ae_out_time)->format('H:i');
                            $rowData['workingHour'] = number_format($missedPunch->ae_total_working, 2);
                            $rowData['attendance_remark'] = $missedPunch->ae_reason_id ? optional($missedPunch->fh_mispunch_reason)->m_name : ($missedPunch->ae_custom_reason ?? 'N/A');

                            $shiftStartTime = $employee->fh_shift_type->pst_start_time ?? '09:00:00';
                            $shiftEndTime = $employee->fh_shift_type->pst_end_time ?? '18:00:00';
                            $pst_start_time = Carbon::parse($shiftStartTime);
                            $pst_end_time = Carbon::parse($shiftEndTime);
                            $dailyWorkingHours = $pst_start_time->diffInMinutes($pst_end_time);
                            $checkInTime = Carbon::parse($date . ' ' . $missedPunch->ae_in_time);
                            $checkOutTime =  Carbon::parse($date . ' ' . $missedPunch->ae_out_time);
                            $workedDuration = $checkInTime->diffInMinutes($checkOutTime);
                            $fullDayThreshold = $dailyWorkingHours;
                            $halfDayThreshold = $dailyWorkingHours / 2;
                            if ($workedDuration >= $fullDayThreshold) {
                                $rowData['presentCount']  = 1; //if missed punch approved count as present
                                $presentData = MasterTable::where('m_id', $missedPunch->ae_attendance_status ?? 251)->select('m_id', 'm_name', 'm_type', 'm_other')->first();
                                $rowData['status_id'] = $presentData->m_id;
                                $rowData['status'] = $presentData->m_name;
                                $rowData['status_code'] = $presentData->m_type;
                                $rowData['statusColor'] = json_decode($presentData->m_other, true)['color'];
                                $rowData['approvedMissedPunchCount'] = 1;
                            } else if ($workedDuration >= $halfDayThreshold) { //half day present & half day leave case
                                $halfDayPresentData = MasterTable::where('m_id', $missedPunch->ae_attendance_status ?? 252)->select('m_id', 'm_name', 'm_type', 'm_other')->first();
                                if ($leave_record_exit && $leave_record_exit->count() == 1) {
                                    $lvr_exit = $leave_record_exit->first();
                                    if (optional($lvr_exit->fh_leave_day_segment)->m_id == 236) {
                                        $rowData['status_id'] = $halfDayPresentData->m_id . '/' . $lvr_exit->fh_leave_cat_type->m_id;
                                        $rowData['status'] = $halfDayPresentData->m_name . '/' . $lvr_exit->fh_leave_cat_type->m_name;
                                        $rowData['status_code'] = $halfDayPresentData->m_type . '/' . $lvr_exit->fh_leave_cat_type->m_type;
                                        $rowData['statusColor'] = json_decode($halfDayPresentData->m_other, true)['color'] . '/' . json_decode($lvr_exit->fh_leave_cat_type->m_other, true)['color'];
                                    } elseif (optional($lvr_exit->fh_leave_day_segment)->m_id == 235) {
                                        $rowData['status_id'] = $lvr_exit->fh_leave_cat_type->m_id . '/' . $halfDayPresentData->m_id;
                                        $rowData['status'] = $lvr_exit->fh_leave_cat_type->m_name . '/' . $halfDayPresentData->m_name;
                                        $rowData['status_code'] =  $lvr_exit->fh_leave_cat_type->m_type . '/' . $halfDayPresentData->m_type;
                                        $rowData['statusColor'] = json_decode($lvr_exit->fh_leave_cat_type->m_other, true)['color'] . '/' . json_decode($halfDayPresentData->m_other, true)['color'];
                                    }
                                }
                                $rowData['presentCount'] = 0.5;
                                $rowData['approvedMissedPunchCount'] = 0.5;
                            }
                        }
                    }

                    // Priority 2: Check AttendanceLog if no AttendanceException found
                    if (isset($attendance_log) && !empty($attendance_log) && !$is_found) {
                        $is_found = 1;
                        
                        // Set basic attendance data from log
                        $rowData['checkInTime'] = $attendance_log->al_check_in_time ? Carbon::parse($attendance_log->al_check_in_time)->format('H:i') : '-';
                        $rowData['checkOutTime'] = $attendance_log->al_check_out_time ? Carbon::parse($attendance_log->al_check_out_time)->format('H:i') :  '-';
                        $rowData['workingHour'] = $attendance_log->al_total_worked_hours ? number_format($attendance_log->al_total_worked_hours, 2) : '-';
                        $rowData['previousLate'] = ($attendance_log->al_is_late && $attendance_log->al_late_duration) ? number_format($attendance_log->al_late_duration, 2) : null;
                        $rowData['previousExit'] = ($attendance_log->al_is_early_exit && $attendance_log->al_early_exit_duration) ? number_format($attendance_log->al_early_exit_duration, 2) : null;
                        $rowData['attendance_remark'] = $attendance_log->al_reason ? $attendance_log->al_reason : ($attendance_record_exit?->atd_remark ?? null);
                        $rowData['mark_as_absent'] = $attendance_log->al_is_absent ? $attendance_log->al_is_absent : ($attendance_record_exit?->atd_is_absent ?? null);

                        $rowData['updatedBy'] = optional($attendance_log->fh_employee_data)->emp_full_name ?? '-';
                        $rowData['previousCheckInTime'] = isset($attendance_record_exit->atd_check_in_time) && !empty($attendance_record_exit->atd_check_in_time) 
                            ? Carbon::parse($attendance_record_exit->atd_check_in_time)->format('H:i') 
                            : '-';

                        $rowData['previousCheckOutTime'] = isset($attendance_record_exit->atd_check_out_time) && !empty($attendance_record_exit->atd_check_out_time) 
                            ? Carbon::parse($attendance_record_exit->atd_check_out_time)->format('H:i') 
                            : '-';

                        // Determine status based on log data
                        if ($attendance_log->al_attendance_status) {
                            $statusData = MasterTable::where('m_id', $attendance_log->al_attendance_status)->first();
                            if ($statusData) {
                                $rowData['status_id'] = $statusData->m_id;
                                $rowData['status'] = $statusData->m_name;
                                $rowData['status_code'] = $statusData->m_type;
                                $rowData['statusColor'] = json_decode($statusData->m_other, true)['color'];
                                
                                if ($statusData->m_id == 251) { // Present
                                    $rowData['presentCount'] = 1;
                                } elseif ($statusData->m_id == 252) { // Half Day
                                    $rowData['halfDayCount'] = 1;
                                } elseif ($statusData->m_id == 319) { // Holiday Present
                                    $rowData["holidayPresentCount"] = 1;
                                } elseif ($statusData->m_id == 320) { // Week Off Present
                                    $rowData["weekOffPresentCount"] = 1;
                                } 
                            }
                        } else {
                            // Default to present if no specific status but has attendance log
                            $presentData = MasterTable::where('m_id', 251)->first();
                            $rowData['status_id'] = $presentData->m_id;
                            $rowData['status'] = $presentData->m_name;
                            $rowData['status_code'] = $presentData->m_type;
                            $rowData['statusColor'] = json_decode($presentData->m_other, true)['color'];
                            $rowData['presentCount'] = 1;
                        }

                        if ($attendance_log->al_is_overtime == 1) {
                            $rowData['overtimeCount'] = 1;
                            $rowData['OT'] = $attendance_log->al_overtime_hours ? number_format($attendance_log->al_overtime_hours, 2) : null;
                        }

                        if ($attendance_log->al_is_late == 1) {
                            $rowData['lateCount'] = 1;
                        }

                        if ($attendance_log->al_is_early_exit == 1) {
                            $rowData['earlyExitCount'] = 1;
                        }
                    }

                    // Priority 3: Check AttendanceRecord if no AttendanceException or AttendanceLog found
                    if (!$is_found && $attendance_record_exit && $attendance_record_exit->fh_attendance_status) {
                        if ($attendance_record_exit->atd_attendance_status == 252) { //Half Day
                            $rowData['halfDayCount'] = 1;
                            $rowData['status_id'] = $attendance_record_exit->fh_attendance_status->m_id;
                            $rowData['status_code'] = $attendance_record_exit->fh_attendance_status->m_type;
                            $is_found = 1;
                        } else {
                            // Always set status_id and status_code from attendance record
                            $rowData['status_id'] = $attendance_record_exit->fh_attendance_status->m_id;
                            $rowData['status_code'] = $attendance_record_exit->fh_attendance_status->m_type;
                            
                            if ($attendance_record_exit->atd_attendance_status == 251) {
                                $rowData['presentCount'] = 1; //Present
                            }
                            if (!$missedPunch) {
                                $rowData['status_id'] = $attendance_record_exit->fh_attendance_status->m_id;
                                $rowData['status_code'] = $attendance_record_exit->fh_attendance_status->m_type;
                                if ($attendance_record_exit->atd_attendance_status == 251) {
                                    $rowData['presentCount'] = 1; //Present
                                } elseif ($attendance_record_exit->atd_attendance_status == 320) {
                                    $rowData["weekOffPresentCount"] = 1;
                                } elseif ($attendance_record_exit->atd_attendance_status == 319) {
                                    $rowData["holidayPresentCount"] = 1;
                                }
                            }
                            $is_found = 1;
                        }

                        if ($attendance_record_exit->atd_is_overtime == 1) {
                            $rowData['overtimeCount'] = 1;
                        }

                        if ($attendance_record_exit->atd_is_late == 1) {
                            $rowData['lateCount'] = 1;
                        }

                        if ($attendance_record_exit->atd_is_early_exit == 1) {
                            $rowData['earlyExitCount'] = 1;
                        }

                        $rowData['status'] = $attendance_record_exit->fh_attendance_status->m_name;
                        $rowData['statusColor'] = json_decode($attendance_record_exit->fh_attendance_status->m_other, true)['color'];
                        $rowData['checkInTime'] = $attendance_record_exit->atd_check_in_time ? Carbon::parse($attendance_record_exit->atd_check_in_time)->format('H:i') : '-';
                        $rowData['checkOutTime'] = $attendance_record_exit->atd_check_out_time ? Carbon::parse($attendance_record_exit->atd_check_out_time)->format('H:i') :  '-';
                        $rowData['workingHour'] = $attendance_record_exit->atd_total_worked_hours ? number_format($attendance_record_exit->atd_total_worked_hours, 2) : '-';
                        $rowData['earlyExit'] = ($attendance_record_exit->atd_is_early_exit && $attendance_record_exit->atd_early_exit_duration) ? number_format($attendance_record_exit->atd_early_exit_duration, 2) :  null;
                        $rowData['late'] = ($attendance_record_exit->atd_is_late && $attendance_record_exit->atd_late_duration) ? number_format($attendance_record_exit->atd_late_duration, 2) :  null;
                        $rowData['OT'] = ($attendance_record_exit->atd_is_overtime && $attendance_record_exit->atd_overtime_hours) ? number_format($attendance_record_exit->atd_overtime_hours, 2) :  null;
                        $rowData['attendance_remark'] = $attendance_record_exit->atd_remark  ? $attendance_record_exit->atd_remark :  '-';
                        $rowData['checkInLocation'] = $attendance_record_exit && $attendance_record_exit->atd_punchin_location ? $attendance_record_exit->atd_punchin_location : '--';
                        $rowData['checkOutLocation'] =  $attendance_record_exit && $attendance_record_exit->atd_punchout_location ? $attendance_record_exit->atd_punchout_location : '--';
                        $rowData['checkInPhoto'] = $attendance_record_exit && $attendance_record_exit->atd_punchin_photo ? json_decode($attendance_record_exit->atd_punchin_photo) : [];
                        $rowData['checkOutPhoto'] =  $attendance_record_exit && $attendance_record_exit->atd_punchout_photo ? json_decode($attendance_record_exit->atd_punchout_photo) : [];
                        $rowData['atd_segments'] =  $attendance_record_exit && $attendance_record_exit->atd_segments ? [json_decode($attendance_record_exit->atd_segments)] : [];
                        $rowData['updatedBy'] = $attendance_record_exit->updated_by  ? $attendance_record_exit->updated_by->emp_full_name :  '-';
                    }

                    $half_day_present = ($attendance_record_exit && $attendance_record_exit->atd_attendance_status == 252); //Half Day

                    if (is_null($is_found) || $half_day_present) {
                        if ($leave_record_exit && $leave_record_exit->count() == 1) {
                            $lvr_exit = $leave_record_exit->first();
                            $is_unpaid = $lvr_exit->fh_leave_cat_type->m_id == 215; // UPL
                            $is_approved = $lvr_exit->lvr_status != 170 && $lvr_exit->lvr_stage_completed;
                            $is_half_day_leave = optional($lvr_exit->fh_leave_day_type)->m_id != 201;
                            $segment_id = $lvr_exit->fh_leave_day_segment->m_id ?? null;
                            $leave_category_ids = MasterTable::where('m_group', 'LEAVE_CATEGORY')->pluck('m_id')->toArray();

                            if (in_array($lvr_exit->fh_leave_cat_type->m_id, $leave_category_ids) && $is_approved) {
                                $is_found = 1;

                                // Case: Half-day present + Half-day leave
                                if ($half_day_present && $is_half_day_leave) {
                                    $presentStatus = $attendance_record_exit->fh_attendance_status;

                                    if ($segment_id == 236) { // 2nd Half Leave
                                        $rowData['status_id'] = $presentStatus->m_id . '/' . $lvr_exit->fh_leave_cat_type->m_id;
                                        $rowData['status'] = $presentStatus->m_name . '/' . $lvr_exit->fh_leave_cat_type->m_name;
                                        $rowData['status_code'] = $presentStatus->m_type . '/' . $lvr_exit->fh_leave_cat_type->m_type;
                                        $rowData['statusColor'] = json_decode($presentStatus->m_other, true)['color'] . '/' . json_decode($lvr_exit->fh_leave_cat_type->m_other, true)['color'];
                                    } elseif ($segment_id == 235) { // 1st Half Leave
                                        $rowData['status_id'] = $lvr_exit->fh_leave_cat_type->m_id . '/' . $presentStatus->m_id;
                                        $rowData['status'] = $lvr_exit->fh_leave_cat_type->m_name . '/' . $presentStatus->m_name;
                                        $rowData['status_code'] = $lvr_exit->fh_leave_cat_type->m_type . '/' . $presentStatus->m_type;
                                        $rowData['statusColor'] = json_decode($lvr_exit->fh_leave_cat_type->m_other, true)['color'] . '/' . json_decode($presentStatus->m_other, true)['color'];
                                    }

                                    // FIXED COUNTS - Half day present + Half day leave
                                    $rowData['presentCount'] = 0.5;
                                    if ($is_unpaid) {
                                        $rowData['UPL'] = 0.5; // UPL counts in UPL field
                                        $rowData['leaveCount'] = 0; // UPL doesn't count as leave
                                    } else {
                                        $rowData['leaveCount'] = 0.5; // SL/CL counts as leave
                                        $rowData['UPL'] = 0;
                                    }
                                    $rowData['approvedLeaveCount'] = $is_unpaid ? 0 : 0.5;
                                }

                                // Case: Only single full/half leave (no half-day present)
                                elseif (!$half_day_present) {
                                    $rowData['status_id'] = $lvr_exit->fh_leave_cat_type->m_id;
                                    $rowData['status'] = $lvr_exit->fh_leave_cat_type->m_name;
                                    $rowData['status_code'] = $lvr_exit->fh_leave_cat_type->m_type;
                                    $rowData['statusColor'] = json_decode($lvr_exit->fh_leave_cat_type->m_other, true)['color'];
                                    
                                    // FIXED COUNTS - Full day or half day leave only
                                    $rowData['presentCount'] = 0; // No present count for leaves
                                    if ($is_unpaid) {
                                        $rowData['UPL'] = $is_half_day_leave ? 0.5 : 1; // UPL counts in UPL field
                                        $rowData['leaveCount'] = 0; // UPL doesn't count as leave
                                    } else {
                                        $rowData['leaveCount'] = $is_half_day_leave ? 0.5 : 1; // SL/CL counts as leave
                                        $rowData['approvedLeaveCount'] = $is_half_day_leave ? 0.5 : 1;
                                    }
                                }
                            }
                        }

                        // Case: Two half-day leaves like CL/SL or CL/UPL etc.
                        elseif ($leave_record_exit && $leave_record_exit->count() == 2) {
                            $leaves = $leave_record_exit->sortBy(function ($l) {
                                return optional($l->fh_leave_day_segment)->m_id ?? 0;
                            })->values();

                            $l1 = $leaves[0];
                            $l2 = $leaves[1];

                            $is_approved1 = $l1->lvr_status != 170 && $l1->lvr_stage_completed;
                            $is_approved2 = $l2->lvr_status != 170 && $l2->lvr_stage_completed;

                            if ($is_approved1 || $is_approved2) {
                                $rowData['status_id'] = $l1->fh_leave_cat_type->m_id . '/' . $l2->fh_leave_cat_type->m_id;
                                $rowData['status'] = $l1->fh_leave_cat_type->m_name . '/' . $l2->fh_leave_cat_type->m_name;
                                $rowData['status_code'] = $l1->fh_leave_cat_type->m_type . '/' . $l2->fh_leave_cat_type->m_type;
                                $rowData['statusColor'] = json_decode($l1->fh_leave_cat_type->m_other, true)['color'] . '/' . json_decode($l2->fh_leave_cat_type->m_other, true)['color'];

                                $is_found = 1;
                                
                                // FIXED COUNTS - Two half day leaves
                                $rowData['presentCount'] = 0; // No present count for leaves
                                $rowData['leaveCount'] = 0;
                                $rowData['UPL'] = 0;
                                $rowData['approvedLeaveCount'] = 0;

                                // Count first half day leave
                                if ($is_approved1) {
                                    if ($l1->fh_leave_cat_type->m_id == 215) { // UPL
                                        $rowData['UPL'] += 0.5; // UPL counts in UPL field
                                    } else { // SL/CL
                                        $rowData['leaveCount'] += 0.5; // SL/CL counts as leave
                                        $rowData['approvedLeaveCount'] += 0.5;
                                    }
                                }

                                // Count second half day leave  
                                if ($is_approved2) {
                                    if ($l2->fh_leave_cat_type->m_id == 215) { // UPL
                                        $rowData['UPL'] += 0.5; // UPL counts in UPL field
                                    } else { // SL/CL
                                        $rowData['leaveCount'] += 0.5; // SL/CL counts as leave
                                        $rowData['approvedLeaveCount'] += 0.5;
                                    }
                                }

                                $rowData['isApproved'] = $is_approved1 && $is_approved2;
                            }
                        }
                    }

                    if (is_null($is_found)) {
                        if ($holiday_record_exit) {
                            $holidayStatus = MasterTable::where('m_id', 321)->first();
                            $rowData['holidayCount'] = 1;
                            $is_found = 1;
                            $rowData['status'] = $holidayStatus->m_name;
                            $rowData['status_id'] = $holidayStatus->m_id;
                            $rowData['status_code'] = $holidayStatus->m_type;
                            $rowData['statusColor'] = json_decode($holidayStatus->m_other, true)['color'];
                            $rowData['checkInTime'] = '-';
                            $rowData['checkOutTime'] = '-';
                            $rowData['workingHour'] = '-';
                        }
                    }
                    
                    if (is_null($is_found)) {
                        if (in_array($date, $weekOffDates)) {
                            $weekOffStatus = MasterTable::where('m_id', 322)->first();
                            $rowData['weekOffCount'] = 1;
                            $is_found = 1;
                            $rowData['status'] = $weekOffStatus->m_name;
                            $rowData['status_id'] = $weekOffStatus->m_id;
                            $rowData['status_code'] = $weekOffStatus->m_type;
                            $rowData['statusColor'] = json_decode($weekOffStatus->m_other, true)['color'];
                            $rowData['checkInTime'] = '-';
                            $rowData['checkOutTime'] = '-';
                            $rowData['workingHour'] = '-';
                        }
                    }
                    
                    // Check for approved leaves (including UPL) for all dates
                    if (is_null($is_found)) {
                        $leave_record_exit = $leave_record_exits->get($date) ?? null;
                        
                        if ($leave_record_exit && $leave_record_exit->count() >= 1) {
                            $lvr_exit = $leave_record_exit->first();
                            $is_unpaid = $lvr_exit->fh_leave_cat_type->m_id == 215; // UPL
                            $is_approved = $lvr_exit->lvr_status != 170 && $lvr_exit->lvr_stage_completed;
                            $is_half_day_leave = optional($lvr_exit->fh_leave_day_type)->m_id != 201;
                            
                            if ($is_approved) {
                                $rowData['status'] = $lvr_exit->fh_leave_cat_type->m_name;
                                $rowData['status_id'] = $lvr_exit->fh_leave_cat_type->m_id;
                                $rowData['status_code'] = $lvr_exit->fh_leave_cat_type->m_type;
                                $rowData['statusColor'] = json_decode($lvr_exit->fh_leave_cat_type->m_other, true)['color'];
                                $rowData['checkInTime'] = '-';
                                $rowData['checkOutTime'] = '-';
                                $rowData['workingHour'] = '-';
                                $is_found = 1;

                                // Set counts based on leave type
                                if ($is_unpaid) {
                                    $rowData['UPL'] = $is_half_day_leave ? 0.5 : 1; // UPL counts in UPL field
                                    $rowData['leaveCount'] = 0; // UPL doesn't count as leave
                                } else {
                                    $rowData['leaveCount'] = $is_half_day_leave ? 0.5 : 1; // SL/CL counts as leave
                                    $rowData['approvedLeaveCount'] = $is_half_day_leave ? 0.5 : 1;
                                }
                            }
                        }
                        
                        // Only mark as absent for past dates with no leave or other status
                        if (is_null($is_found) && Carbon::parse($date)->isPast()) {
                            $rowData['absentCount'] = 1;
                            $rowData['status'] = $absentStatus->m_name;
                            $rowData['status_id'] = $absentStatus->m_id;
                            $rowData['status_code'] = $absentStatus->m_type;
                            $rowData['statusColor'] = json_decode($absentStatus->m_other, true)['color'];
                            $rowData['checkInTime'] = '-';
                            $rowData['checkOutTime'] = '-';
                            $rowData['workingHour'] = '-';
                        }
                    }
                }

                if ($rowData['status_id']) {
                    $rowData['status_id'] = (string)$rowData['status_id'];
                }
                $rowData['date'] = $date;
                $attendanceData[] = $rowData;
            }
        }

        return $attendanceData;
    }

    public static function newGetMonthlyAttendanceReportAuxiliary($employee, $month = null, $year = null, $holidayRecords = null, $weekOffDates = null, $fromDate = null, $toDate = null)
    {
        
        // Determine the date range based on provided fromDate and toDate, or fallback to month/year
        if ($fromDate && $toDate) {
            $startDate = Carbon::parse($fromDate)->startOfDay();
            $endDate = Carbon::parse($toDate)->endOfDay();
        } else {
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
        }
        $dateRange = $startDate->toPeriod($endDate);
        $currentMonth = (int) date('m');
        $absentStatus = MasterTable::where('m_id', 203)->first();
        $uplStatus = MasterTable::where('m_id', 215)->first(); // UPL status

        // Preload everything to avoid repeated DB hits
        $attendanceRecords = $employee->attendance_record()
            ->whereBetween('atd_date', [$startDate, $endDate])
            ->get()
            ->keyBy(fn($row) => Carbon::parse($row->atd_date)->toDateString());

        $attendanceLogs = AttendanceLog::where('al_emp_id', $employee->emp_id)
            ->whereBetween('al_date', [$startDate, $endDate])
            ->get()
            ->keyBy(fn($row) => Carbon::parse($row->al_date)->toDateString());

        $missedPunches = AttendanceException::where('ae_b_id', $employee->emp_b_id)
            ->where('ae_emp_id', $employee->emp_id)
            ->whereBetween('ae_date', [$startDate, $endDate])
            ->get()
            ->keyBy(fn($row) => Carbon::parse($row->ae_date)->toDateString());

        $leave_record_exits = $employee->leave_requests()->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('lvr_start_date', [$startDate, $endDate])
            ->orWhereBetween('lvr_end_date', [$startDate, $endDate]);
        })->get();

        $leaveByDate = collect();

        foreach ($leave_record_exits as $leave) {
            $start = Carbon::parse($leave->lvr_start_date);
            $end = Carbon::parse($leave->lvr_end_date);

            while ($start->lte($end)) {
                $dateStr = $start->toDateString();
                if (!$leaveByDate->has($dateStr)) {
                    $leaveByDate->put($dateStr, collect());
                }
                $leaveByDate->get($dateStr)->push($leave);
                $start->addDay();
            }
        }

        $leave_record_exits = $leaveByDate;

        $attendanceData = [];
        $sandwichRuleApplied = []; // Track which WO/HO are converted due to sandwich rule

        // First pass: Collect all dates and identify sandwich situations
        $allDates = [];
        foreach ($dateRange as $dateObj) {
            $allDates[] = $dateObj->toDateString();
        }

        // Helper function to check if a date has ABS
        $isAbsent = function($date) use ($attendanceRecords, $leave_record_exits, $holidayRecords, $weekOffDates, $employee, $absentStatus) {
            if ($employee->emp_date_of_joining > $date) {
                return false;
            }

            $attendance_record_exit = $attendanceRecords[$date] ?? null;
            $leave_record_exit = $leave_record_exits->get($date) ?? null;
            $holiday_record_exit = $holidayRecords[$date] ?? null;

            // Check for Absent (not WO, not HO, not present, and date is past)
            if (!$attendance_record_exit && !$leave_record_exit && !$holiday_record_exit && 
                !in_array($date, $weekOffDates) && Carbon::parse($date)->isPast()) {
                return 203; // ABS
            }

            return false;
        };

        // foreach ($allDates as $index => $date) {
        //     $normalizedDate = Carbon::parse($date)->toDateString(); 
            
        //     $isInWeekOfDates = in_array($normalizedDate, $weekOffDates);
        //     $isInHolidayRecord = array_key_exists($normalizedDate, $holidayRecords->toArray());
        //     $isCurrentWOOrHO = $isInWeekOfDates || $isInHolidayRecord;
            
        //     // Only proceed if the date is WO/HO and not already processed
        //     if ($isCurrentWOOrHO && !isset($sandwichRuleApplied[$date])) {
        //         $prevDate = $index > 0 ? $allDates[$index - 1] : null;
        //         $nextDate = $index < count($allDates) - 1 ? $allDates[$index + 1] : null;
                
        //         // Check if both adjacent days are absents AND they are consecutive
        //         $prevStatus = $prevDate ? $isAbsent($prevDate) : false;
        //         $nextStatus = $nextDate ? $isAbsent($nextDate) : false;
                
        //         // Only apply sandwich rule if BOTH adjacent days are absents
        //         if ($prevStatus && $nextStatus && $prevStatus == 203 && $nextStatus == 203) {
        //             $sandwichRuleApplied[$date] = 203;
        //         }
        //     }
        // }

        // Second pass: Generate attendance data
        foreach ($dateRange as $dateObj) {
            $date = $dateObj->toDateString();
            $is_found = null;
            $rowData = [
                'status' => '-',
                'status_id' => '-',
                'status_code' => '-',
                'statusColor' => '#BDBDBD',
                'checkingMethodId' => null,
                'checkInTime' => null,
                'checkOutTime' => null,
                'updatedBy' => null,
                'workingHour' => null,
                'OT' => null,
                'earlyExit' => null,
                'late' => null,
                'attendance_remark' => '--',
                'mark_as_absent' => '--',
                'checkInLocation' => '--',
                'checkOutLocation' => '--',
                'checkInPhoto' => [],
                'checkOutPhoto' => [],
                'atd_segments' => [],
                'presentCount' => 0,
                'holidayPresentCount' => 0,
                'weekOffPresentCount' => 0,
                'leaveCount' => 0,
                'holidayCount' => 0,
                'weekOffCount' => 0,
                'absentCount' => 0,
                'halfDayCount' => 0,
                'missedPunchCount' => 0,
                'overtimeCount' => 0,
                'lateCount' => 0,
                'earlyExitCount' => 0,
                'approvedLeaveCount' => 0,
                'approvedMissedPunchCount' => 0,
                'isApproved' => null,
                'previousCheckInTime' => null,
                'previousCheckOutTime' => null,
                'previousLate' => null,
                'previousExit' => null,
                'UPL' => 0,
            ];
            
            // Skip dates before joining or after last working day
            $shouldCountDate = true;
            if ($employee->emp_date_of_joining && $dateObj->lt(Carbon::parse($employee->emp_date_of_joining))) {
                $shouldCountDate = false;
            }
            if ($employee->emp_last_working_day && $dateObj->gt(Carbon::parse($employee->emp_last_working_day))) {
                $shouldCountDate = false;
            }

            if ($shouldCountDate && $employee->emp_date_of_joining <= $date) {
                // Check if sandwich rule applies to this date
                if (isset($sandwichRuleApplied[$date])) {
                    $sandwichStatus = $sandwichRuleApplied[$date];
                    $is_found = 1;

                    if ($sandwichStatus === 203) {
                        $rowData['absentCount'] = 1;
                        $rowData['status'] = $absentStatus->m_name;
                        $rowData['status_id'] = $absentStatus->m_id;
                        $rowData['status_code'] = $absentStatus->m_type;
                        $rowData['statusColor'] = json_decode($absentStatus->m_other, true)['color'];
                    }

                    $rowData['checkInTime'] = '-';
                    $rowData['checkOutTime'] = '-';
                    $rowData['workingHour'] = '-';
                } else {
                    // Original logic continues here...
                    $attendance_record_exit = $attendanceRecords[$date] ?? null;
                    $attendance_log = $attendanceLogs[$date] ?? null;
                    $missedPunch = $missedPunches[$date] ?? null;
                    $holiday_record_exit = $holidayRecords[$date] ?? null;
                    $leave_record_exit = $leave_record_exits->get($date) ?? null;

                    $is_found = null;

                    // Check for UPL status first (atd_attendance_status == 215)
                    if ($attendance_record_exit && $attendance_record_exit->atd_attendance_status == 215) {
                        $rowData['status'] = $uplStatus->m_name;
                        $rowData['status_id'] = $uplStatus->m_id;
                        $rowData['status_code'] = $uplStatus->m_type;
                        $rowData['statusColor'] = json_decode($uplStatus->m_other, true)['color'];
                        $rowData['UPL'] = 1;
                        $is_found = 1;
                    }

                    // Priority 1: Check AttendanceException first
                    if (isset($missedPunch) && !empty($missedPunch) && !$is_found) {
                        $missedPunchStatus = MasterTable::where('m_id', 228)->first(); //Missedpunch
                        $is_found = 1;
                        $rowData['missedPunchCount'] =  1;

                        // Check if missed punch is approved
                        $isApprovedMissedPunch = ($missedPunch->ae_status == 157 && $missedPunch->ae_stage_completed == 1);
                        if ($isApprovedMissedPunch) {
                            // Show approved missed punch times
                            $rowData['checkInTime'] = Carbon::createFromFormat('H:i:s', $missedPunch->ae_in_time)->format('h:i A');
                            $rowData['checkOutTime'] =  Carbon::createFromFormat('H:i:s', $missedPunch->ae_out_time)->format('h:i A');
                            $rowData['workingHour'] = number_format($missedPunch->ae_total_working, 2);
                            $rowData['attendance_remark'] = $missedPunch->ae_reason_id ? optional($missedPunch->fh_mispunch_reason)->m_name : ($missedPunch->ae_custom_reason ?? 'N/A');

                            $shiftStartTime = $employee->fh_shift_type->pst_start_time ?? '09:00:00';
                            $shiftEndTime = $employee->fh_shift_type->pst_end_time ?? '18:00:00';
                            $pst_start_time = Carbon::parse($shiftStartTime);
                            $pst_end_time = Carbon::parse($shiftEndTime);
                            $dailyWorkingHours = $pst_start_time->diffInMinutes($pst_end_time);
                            $checkInTime = Carbon::parse($date . ' ' . $missedPunch->ae_in_time);
                            $checkOutTime =  Carbon::parse($date . ' ' . $missedPunch->ae_out_time);
                            $workedDuration = $checkInTime->diffInMinutes($checkOutTime);
                            $fullDayThreshold = $dailyWorkingHours;
                            $halfDayThreshold = $dailyWorkingHours / 2;
                            if ($workedDuration >= $fullDayThreshold) {
                                $rowData['presentCount']  = 1; //if missed punch approved count as present
                                $presentData = MasterTable::where('m_id', $missedPunch->ae_attendance_status ?? 251)->select('m_id', 'm_name', 'm_type', 'm_other')->first();
                                $rowData['status_id'] = $presentData->m_id;
                                $rowData['status'] = $presentData->m_name;
                                $rowData['status_code'] = $presentData->m_type;
                                $rowData['statusColor'] = json_decode($presentData->m_other, true)['color'];
                                $rowData['approvedMissedPunchCount'] = 1;
                            } else if ($workedDuration >= $halfDayThreshold) { //half day present & half day leave case
                                $halfDayPresentData = MasterTable::where('m_id', $missedPunch->ae_attendance_status ?? 252)->select('m_id', 'm_name', 'm_type', 'm_other')->first();
                                if ($leave_record_exit && $leave_record_exit->count() == 1) {
                                    $lvr_exit = $leave_record_exit->first();
                                    if (optional($lvr_exit->fh_leave_day_segment)->m_id == 236) {
                                        $rowData['status_id'] = $halfDayPresentData->m_id . '/' . $lvr_exit->fh_leave_cat_type->m_id;
                                        $rowData['status'] = $halfDayPresentData->m_name . '/' . $lvr_exit->fh_leave_cat_type->m_name;
                                        $rowData['status_code'] = $halfDayPresentData->m_type . '/' . $lvr_exit->fh_leave_cat_type->m_type;
                                        $rowData['statusColor'] = json_decode($halfDayPresentData->m_other, true)['color'] . '/' . json_decode($lvr_exit->fh_leave_cat_type->m_other, true)['color'];
                                    } elseif (optional($lvr_exit->fh_leave_day_segment)->m_id == 235) {
                                        $rowData['status_id'] = $lvr_exit->fh_leave_cat_type->m_id . '/' . $halfDayPresentData->m_id;
                                        $rowData['status'] = $lvr_exit->fh_leave_cat_type->m_name . '/' . $halfDayPresentData->m_name;
                                        $rowData['status_code'] =  $lvr_exit->fh_leave_cat_type->m_type . '/' . $halfDayPresentData->m_type;
                                        $rowData['statusColor'] = json_decode($lvr_exit->fh_leave_cat_type->m_other, true)['color'] . '/' . json_decode($halfDayPresentData->m_other, true)['color'];
                                    }
                                }
                                $rowData['presentCount'] = 0.5;
                                $rowData['approvedMissedPunchCount'] = 0.5;
                            }
                        }
                    }

                    // Priority 2: Check AttendanceLog if no AttendanceException found
                    if (isset($attendance_log) && !empty($attendance_log) && !$is_found) {
                        $is_found = 1;
                        
                        // Set basic attendance data from log
                        $rowData['checkInTime'] = $attendance_log->al_check_in_time ? Carbon::parse($attendance_log->al_check_in_time)->format('H:i') : '-';
                        $rowData['checkOutTime'] = $attendance_log->al_check_out_time ? Carbon::parse($attendance_log->al_check_out_time)->format('H:i') :  '-';
                        $rowData['workingHour'] = $attendance_log->al_total_worked_hours ? number_format($attendance_log->al_total_worked_hours, 2) : '-';
                        $rowData['previousLate'] = ($attendance_log->al_is_late && $attendance_log->al_late_duration) ? number_format($attendance_log->al_late_duration, 2) : null;
                        $rowData['previousExit'] = ($attendance_log->al_is_early_exit && $attendance_log->al_early_exit_duration) ? number_format($attendance_log->al_early_exit_duration, 2) : null;
                        $rowData['attendance_remark'] = $attendance_log->al_reason ? $attendance_log->al_reason : ($attendance_record_exit?->atd_remark ?? null);
                        $rowData['mark_as_absent'] = $attendance_log->al_is_absent ? $attendance_log->al_is_absent : ($attendance_record_exit?->atd_is_absent ?? null);

                        $rowData['updatedBy'] = optional($attendance_log->fh_employee_data)->emp_full_name ?? '-';
                        $rowData['previousCheckInTime'] = isset($attendance_record_exit->atd_check_in_time) && !empty($attendance_record_exit->atd_check_in_time) 
                            ? Carbon::parse($attendance_record_exit->atd_check_in_time)->format('H:i') 
                            : '-';

                        $rowData['previousCheckOutTime'] = isset($attendance_record_exit->atd_check_out_time) && !empty($attendance_record_exit->atd_check_out_time) 
                            ? Carbon::parse($attendance_record_exit->atd_check_out_time)->format('H:i') 
                            : '-';

                        // Determine status based on log data
                        if ($attendance_log->al_attendance_status) {
                            $statusData = MasterTable::where('m_id', $attendance_log->al_attendance_status)->first();
                            if ($statusData) {
                                $rowData['status_id'] = $statusData->m_id;
                                $rowData['status'] = $statusData->m_name;
                                $rowData['status_code'] = $statusData->m_type;
                                $rowData['statusColor'] = json_decode($statusData->m_other, true)['color'];
                                
                                if ($statusData->m_id == 251) { // Present
                                    $rowData['presentCount'] = 1;
                                } elseif ($statusData->m_id == 252) { // Half Day
                                    $rowData['halfDayCount'] = 1;
                                } elseif ($statusData->m_id == 319) { // Holiday Present
                                    $rowData["holidayPresentCount"] = 1;
                                } elseif ($statusData->m_id == 320) { // Week Off Present
                                    $rowData["weekOffPresentCount"] = 1;
                                } 
                            }
                        } else {
                            // Default to present if no specific status but has attendance log
                            $presentData = MasterTable::where('m_id', 251)->first();
                            $rowData['status_id'] = $presentData->m_id;
                            $rowData['status'] = $presentData->m_name;
                            $rowData['status_code'] = $presentData->m_type;
                            $rowData['statusColor'] = json_decode($presentData->m_other, true)['color'];
                            $rowData['presentCount'] = 1;
                        }

                        if ($attendance_log->al_is_overtime == 1) {
                            $rowData['overtimeCount'] = 1;
                            $rowData['OT'] = $attendance_log->al_overtime_hours ? number_format($attendance_log->al_overtime_hours, 2) : null;
                        }

                        if ($attendance_log->al_is_late == 1) {
                            $rowData['lateCount'] = 1;
                        }

                        if ($attendance_log->al_is_early_exit == 1) {
                            $rowData['earlyExitCount'] = 1;
                        }
                    }

                    // Priority 3: Check AttendanceRecord if no AttendanceException or AttendanceLog found
                    if (!$is_found && $attendance_record_exit && $attendance_record_exit->fh_attendance_status) {
                        if ($attendance_record_exit->atd_attendance_status == 252) { //Half Day
                            $rowData['halfDayCount'] = 1;
                            $rowData['status_id'] = $attendance_record_exit->fh_attendance_status->m_id;
                            $rowData['status_code'] = $attendance_record_exit->fh_attendance_status->m_type;
                            $is_found = 1;
                        } else {
                            // Always set status_id and status_code from attendance record
                            $rowData['status_id'] = $attendance_record_exit->fh_attendance_status->m_id;
                            $rowData['status_code'] = $attendance_record_exit->fh_attendance_status->m_type;
                            
                            if ($attendance_record_exit->atd_attendance_status == 251) {
                                $rowData['presentCount'] = 1; //Present
                            }
                            if (!$missedPunch) {
                                $rowData['status_id'] = $attendance_record_exit->fh_attendance_status->m_id;
                                $rowData['status_code'] = $attendance_record_exit->fh_attendance_status->m_type;
                                if ($attendance_record_exit->atd_attendance_status == 251) {
                                    $rowData['presentCount'] = 1; //Present
                                } elseif ($attendance_record_exit->atd_attendance_status == 320) {
                                    $rowData["weekOffPresentCount"] = 1;
                                } elseif ($attendance_record_exit->atd_attendance_status == 319) {
                                    $rowData["holidayPresentCount"] = 1;
                                }
                            }
                            $is_found = 1;
                        }

                        
                        if ($attendance_record_exit->atd_is_overtime == 1) {
                            $rowData['overtimeCount'] = 1;
                        }

                        if ($attendance_record_exit->atd_is_late == 1) {
                            $rowData['lateCount'] = 1;
                        }

                        if ($attendance_record_exit->atd_is_early_exit == 1) {
                            $rowData['earlyExitCount'] = 1;
                        }
                        $rowData['status'] = $attendance_record_exit->fh_attendance_status->m_name;
                        $rowData['statusColor'] = json_decode($attendance_record_exit->fh_attendance_status->m_other, true)['color'];
                        $rowData['checkingMethodId'] = $attendance_record_exit->atd_checkin_method_id;
                        $rowData['checkInTime'] = $attendance_record_exit->atd_check_in_time ? Carbon::parse($attendance_record_exit->atd_check_in_time)->format('H:i') : '-';
                        $rowData['checkOutTime'] = $attendance_record_exit->atd_check_out_time ? Carbon::parse($attendance_record_exit->atd_check_out_time)->format('H:i') :  '-';
                        $rowData['workingHour'] = $attendance_record_exit->atd_total_worked_hours ? number_format($attendance_record_exit->atd_total_worked_hours, 2) : '-';
                        $rowData['earlyExit'] = ($attendance_record_exit->atd_is_early_exit && $attendance_record_exit->atd_early_exit_duration) ? number_format($attendance_record_exit->atd_early_exit_duration, 2) :  null;
                        $rowData['late'] = ($attendance_record_exit->atd_is_late && $attendance_record_exit->atd_late_duration) ? number_format($attendance_record_exit->atd_late_duration, 2) :  null;
                        $rowData['OT'] = ($attendance_record_exit->atd_is_overtime && $attendance_record_exit->atd_overtime_hours) ? number_format($attendance_record_exit->atd_overtime_hours, 2) :  null;
                        $rowData['attendance_remark'] = $attendance_record_exit->atd_remark  ? $attendance_record_exit->atd_remark :  '-';
                        $rowData['checkInLocation'] = $attendance_record_exit && $attendance_record_exit->atd_punchin_location ? $attendance_record_exit->atd_punchin_location : '--';
                        $rowData['checkOutLocation'] =  $attendance_record_exit && $attendance_record_exit->atd_punchout_location ? $attendance_record_exit->atd_punchout_location : '--';
                        $rowData['checkInPhoto'] = $attendance_record_exit && $attendance_record_exit->atd_punchin_photo ? json_decode($attendance_record_exit->atd_punchin_photo) : [];
                        $rowData['checkOutPhoto'] =  $attendance_record_exit && $attendance_record_exit->atd_punchout_photo ? json_decode($attendance_record_exit->atd_punchout_photo) : [];
                        $rowData['atd_segments'] =  $attendance_record_exit && $attendance_record_exit->atd_segments ? [json_decode($attendance_record_exit->atd_segments)] : [];
                        $rowData['updatedBy'] = $attendance_record_exit->updated_by  ? $attendance_record_exit->updated_by->emp_full_name :  '-';
                    }

                    $half_day_present = ($attendance_record_exit && $attendance_record_exit->atd_attendance_status == 252); //Half Day

                    if (is_null($is_found) || $half_day_present) {
                        if ($leave_record_exit && $leave_record_exit->count() == 1) {
                            $lvr_exit = $leave_record_exit->first();
                            $is_unpaid = $lvr_exit->fh_leave_cat_type->m_id == 215; // UPL
                            $is_approved = $lvr_exit->lvr_status != 170 && $lvr_exit->lvr_stage_completed;
                            $is_half_day_leave = optional($lvr_exit->fh_leave_day_type)->m_id != 201;
                            $segment_id = $lvr_exit->fh_leave_day_segment->m_id ?? null;
                            $leave_category_ids = MasterTable::where('m_group', 'LEAVE_CATEGORY')->pluck('m_id')->toArray();

                            if (in_array($lvr_exit->fh_leave_cat_type->m_id, $leave_category_ids) && $is_approved) {
                                $is_found = 1;

                                // Case: Half-day present + Half-day leave
                                if ($half_day_present && $is_half_day_leave) {
                                    $presentStatus = $attendance_record_exit->fh_attendance_status;

                                    if ($segment_id == 236) { // 2nd Half Leave
                                        $rowData['status_id'] = $presentStatus->m_id . '/' . $lvr_exit->fh_leave_cat_type->m_id;
                                        $rowData['status'] = $presentStatus->m_name . '/' . $lvr_exit->fh_leave_cat_type->m_name;
                                        $rowData['status_code'] = $presentStatus->m_type . '/' . $lvr_exit->fh_leave_cat_type->m_type;
                                        $rowData['statusColor'] = json_decode($presentStatus->m_other, true)['color'] . '/' . json_decode($lvr_exit->fh_leave_cat_type->m_other, true)['color'];
                                    } elseif ($segment_id == 235) { // 1st Half Leave
                                        $rowData['status_id'] = $lvr_exit->fh_leave_cat_type->m_id . '/' . $presentStatus->m_id;
                                        $rowData['status'] = $lvr_exit->fh_leave_cat_type->m_name . '/' . $presentStatus->m_name;
                                        $rowData['status_code'] = $lvr_exit->fh_leave_cat_type->m_type . '/' . $presentStatus->m_type;
                                        $rowData['statusColor'] = json_decode($lvr_exit->fh_leave_cat_type->m_other, true)['color'] . '/' . json_decode($presentStatus->m_other, true)['color'];
                                    }

                                    // FIXED COUNTS - Half day present + Half day leave
                                    $rowData['presentCount'] = 0.5;
                                    if ($is_unpaid) {
                                        $rowData['UPL'] = 0.5; // UPL counts in UPL field
                                        $rowData['leaveCount'] = 0; // UPL doesn't count as leave
                                    } else {
                                        $rowData['leaveCount'] = 0.5; // SL/CL counts as leave
                                        $rowData['UPL'] = 0;
                                    }
                                    $rowData['approvedLeaveCount'] = $is_unpaid ? 0 : 0.5;
                                }

                                // Case: Only single full/half leave (no half-day present)
                                elseif (!$half_day_present) {
                                    $rowData['status_id'] = $lvr_exit->fh_leave_cat_type->m_id;
                                    $rowData['status'] = $lvr_exit->fh_leave_cat_type->m_name;
                                    $rowData['status_code'] = $lvr_exit->fh_leave_cat_type->m_type;
                                    $rowData['statusColor'] = json_decode($lvr_exit->fh_leave_cat_type->m_other, true)['color'];
                                    
                                    // FIXED COUNTS - Full day or half day leave only
                                    $rowData['presentCount'] = 0; // No present count for leaves
                                    if ($is_unpaid) {
                                        $rowData['UPL'] = $is_half_day_leave ? 0.5 : 1; // UPL counts in UPL field
                                        $rowData['leaveCount'] = 0; // UPL doesn't count as leave
                                    } else {
                                        $rowData['leaveCount'] = $is_half_day_leave ? 0.5 : 1; // SL/CL counts as leave
                                        $rowData['approvedLeaveCount'] = $is_half_day_leave ? 0.5 : 1;
                                    }
                                }
                            }
                        }

                        // Case: Two half-day leaves like CL/SL or CL/UPL etc.
                        elseif ($leave_record_exit && $leave_record_exit->count() == 2) {
                            $leaves = $leave_record_exit->sortBy(function ($l) {
                                return optional($l->fh_leave_day_segment)->m_id ?? 0;
                            })->values();

                            $l1 = $leaves[0];
                            $l2 = $leaves[1];

                            $is_approved1 = $l1->lvr_status != 170 && $l1->lvr_stage_completed;
                            $is_approved2 = $l2->lvr_status != 170 && $l2->lvr_stage_completed;

                            if ($is_approved1 || $is_approved2) {
                                $rowData['status_id'] = $l1->fh_leave_cat_type->m_id . '/' . $l2->fh_leave_cat_type->m_id;
                                $rowData['status'] = $l1->fh_leave_cat_type->m_name . '/' . $l2->fh_leave_cat_type->m_name;
                                $rowData['status_code'] = $l1->fh_leave_cat_type->m_type . '/' . $l2->fh_leave_cat_type->m_type;
                                $rowData['statusColor'] = json_decode($l1->fh_leave_cat_type->m_other, true)['color'] . '/' . json_decode($l2->fh_leave_cat_type->m_other, true)['color'];

                                $is_found = 1;
                                
                                // FIXED COUNTS - Two half day leaves
                                $rowData['presentCount'] = 0; // No present count for leaves
                                $rowData['leaveCount'] = 0;
                                $rowData['UPL'] = 0;
                                $rowData['approvedLeaveCount'] = 0;

                                // Count first half day leave
                                if ($is_approved1) {
                                    if ($l1->fh_leave_cat_type->m_id == 215) { // UPL
                                        $rowData['UPL'] += 0.5; // UPL counts in UPL field
                                    } else { // SL/CL
                                        $rowData['leaveCount'] += 0.5; // SL/CL counts as leave
                                        $rowData['approvedLeaveCount'] += 0.5;
                                    }
                                }

                                // Count second half day leave  
                                if ($is_approved2) {
                                    if ($l2->fh_leave_cat_type->m_id == 215) { // UPL
                                        $rowData['UPL'] += 0.5; // UPL counts in UPL field
                                    } else { // SL/CL
                                        $rowData['leaveCount'] += 0.5; // SL/CL counts as leave
                                        $rowData['approvedLeaveCount'] += 0.5;
                                    }
                                }

                                $rowData['isApproved'] = $is_approved1 && $is_approved2;
                            }
                        }
                    }

                    if (is_null($is_found)) {
                        if ($holiday_record_exit) {
                            $holidayStatus = MasterTable::where('m_id', 321)->first();
                            $rowData['holidayCount'] = 1;
                            $is_found = 1;
                            $rowData['status'] = $holidayStatus->m_name;
                            $rowData['status_id'] = $holidayStatus->m_id;
                            $rowData['status_code'] = $holidayStatus->m_type;
                            $rowData['statusColor'] = json_decode($holidayStatus->m_other, true)['color'];
                            $rowData['checkInTime'] = '-';
                            $rowData['checkOutTime'] = '-';
                            $rowData['workingHour'] = '-';
                        }
                    }
                    
                    // if (is_null($is_found)) {
                    //     if (in_array($date, $weekOffDates)) {
                    //         $weekOffStatus = MasterTable::where('m_id', 322)->first();
                    //         $rowData['weekOffCount'] = 1;
                    //         $is_found = 1;
                    //         $rowData['status'] = $weekOffStatus->m_name;
                    //         $rowData['status_id'] = $weekOffStatus->m_id;
                    //         $rowData['status_code'] = $weekOffStatus->m_type;
                    //         $rowData['statusColor'] = json_decode($weekOffStatus->m_other, true)['color'];
                    //         $rowData['checkInTime'] = '-';
                    //         $rowData['checkOutTime'] = '-';
                    //         $rowData['workingHour'] = '-';
                    //     }
                    // }
                    
                    // Check for approved leaves (including UPL) for all dates
                    if (is_null($is_found)) {
                        $leave_record_exit = $leave_record_exits->get($date) ?? null;
                        
                        if ($leave_record_exit && $leave_record_exit->count() >= 1) {
                            $lvr_exit = $leave_record_exit->first();
                            $is_unpaid = $lvr_exit->fh_leave_cat_type->m_id == 215; // UPL
                            $is_approved = $lvr_exit->lvr_status != 170 && $lvr_exit->lvr_stage_completed;
                            $is_half_day_leave = optional($lvr_exit->fh_leave_day_type)->m_id != 201;
                            
                            if ($is_approved) {
                                $rowData['status'] = $lvr_exit->fh_leave_cat_type->m_name;
                                $rowData['status_id'] = $lvr_exit->fh_leave_cat_type->m_id;
                                $rowData['status_code'] = $lvr_exit->fh_leave_cat_type->m_type;
                                $rowData['statusColor'] = json_decode($lvr_exit->fh_leave_cat_type->m_other, true)['color'];
                                $rowData['checkInTime'] = '-';
                                $rowData['checkOutTime'] = '-';
                                $rowData['workingHour'] = '-';
                                $is_found = 1;

                                // Set counts based on leave type
                                if ($is_unpaid) {
                                    $rowData['UPL'] = $is_half_day_leave ? 0.5 : 1; // UPL counts in UPL field
                                    $rowData['leaveCount'] = 0; // UPL doesn't count as leave
                                } else {
                                    $rowData['leaveCount'] = $is_half_day_leave ? 0.5 : 1; // SL/CL counts as leave
                                    $rowData['approvedLeaveCount'] = $is_half_day_leave ? 0.5 : 1;
                                }
                            }
                        }
                        
                        // Only mark as absent for past dates with no leave or other status
                        if (is_null($is_found) && Carbon::parse($date)->isPast()) {
                            $rowData['absentCount'] = 1;
                            $rowData['status'] = $absentStatus->m_name;
                            $rowData['status_id'] = $absentStatus->m_id;
                            $rowData['status_code'] = $absentStatus->m_type;
                            $rowData['statusColor'] = json_decode($absentStatus->m_other, true)['color'];
                            $rowData['checkInTime'] = '-';
                            $rowData['checkOutTime'] = '-';
                            $rowData['workingHour'] = '-';
                        }
                    }
                }

                if ($rowData['status_id']) {
                    $rowData['status_id'] = (string)$rowData['status_id'];
                }
                $rowData['date'] = $date;
                $attendanceData[] = $rowData;
            }
        }

        // foreach ($attendanceData as $key => $value) {
        //     // 1. Present days
        //     $salaryDay += $value['presentCount'] ?? 0;
            
        //     // 2. Handle Half Day cases
        //     if (($value['halfDayCount'] ?? 0) == 1 && ($value['approvedLeaveCount'] ?? 0) == 0.5) {
        //         // Half-day + half leave = 1 full day
        //         $salaryDay += 1;
        //     } elseif (($value['halfDayCount'] ?? 0) == 1) {
        //         // Only half day present, no leave matched
        //         $salaryDay += 0.5;
        //     } else {
        //         // 3. Approved Leave (if not already paired with half-day)
        //         $salaryDay += $value['approvedLeaveCount'] ?? 0;
        //     }

        //     // 4. Week Off & Holiday
        //     $salaryDay += $value['weekOffCount'] ?? 0;
        //     $salaryDay += $value['holidayCount'] ?? 0;
        // }

        // $lastIndex = count($attendanceData) - 1;
        // if (isset($attendanceData[$lastIndex])) {
        //     $attendanceData[$lastIndex]['totalSalariedDays'] = $salaryDay;
        // }

        return $attendanceData;
    }

    public static function checkAndApplyManagerChange()
    {
        $today = Carbon::today();
        DB::transaction(function () use ($today) {
            $logs = EmployeeManagerLog::where('eml_applied', 0)
                ->whereDate('eml_wef_date', $today)   // ⭐ ONLY TODAY
                ->orderBy('eml_id')
                ->lockForUpdate()
                ->get();

            if ($logs->isEmpty()) {
                \Log::info('No manager change logs for today.');
                return;
            }

            foreach ($logs as $log) {
                if ($log->eml_form_type === 'REPORTING_MANAGER') {
                    Employee::where('emp_supervisor_id', $log->eml_old_manager_id)
                        ->update([
                            'emp_supervisor_id' => $log->eml_new_manager_id
                        ]);
                    \Log::info('Reporting Manager updated successfully.');
                }

                if ($log->eml_form_type === 'APPROVAL_MANAGER') {
                    EmployeeApprovalStatus::where('eas_approvel_id', $log->eml_old_manager_id)
                        ->update([
                            'eas_approvel_id' => $log->eml_new_manager_id
                        ]);
                        \Log::info('Approval Manager updated successfully.');
                }
                
                $log->update([
                    'eml_applied' => 1,
                    'eml_applied_at' => now()
                ]);
            }
        });
    }

    /**
     * Calculate eligible OT hours and minutes based on attendance & OT rule.
     *
     * @param  array  $attendance_data   Attendance details (checkInTime, checkOutTime, workingHour, status_id)
     * @param  object $otRule            OT rule model or object
     * @param  object|null $assignShift  Shift details (pst_start_time, pst_end_time, pst_type_id)
     * @return array                     ['eligible' => bool, 'minutes' => int, 'formatted' => string, 'label' => string]
     */
    public static function calculateOverTime(array $attendance_data, object $otRule, ?object $assignShift = null): array
    {
        // --- Local helper
        $getDiffInMinutes = function ($time1, $time2) {
            if (empty($time1) || empty($time2)) return 0;
            $t1 = new DateTime($time1);
            $t2 = new DateTime($time2);
            $diff = $t1->diff($t2);
            return ($diff->h * 60) + $diff->i;
        };

        // --- Extract base data
        $statusId       = $attendance_data['status_id'] ?? null;
        $checkInTime    = $attendance_data['checkInTime'] ?? null;
        $checkOutTime   = $attendance_data['checkOutTime'] ?? null;
        $shiftStartTime = $assignShift->pst_start_time ?? null;
        $shiftEndTime   = $assignShift->pst_end_time ?? null;
        $workingHour    = (float) ($attendance_data['workingHour'] ?? 0);
        $workedMinutes  = round($workingHour * 60);

        // --- Identify day type
        $isWorkingDay     = !in_array($statusId, [319, 320]); // 319=Holiday, 320=Weekoff
        $isNonWorkingDay  = in_array($statusId, [319, 320]);
        $canApplyOT       = $otRule->ot_is_enabled == 1 && (!isset($assignShift->pst_type_id) || $otRule->ot_shift_type == $assignShift->pst_type_id);

        if (!$canApplyOT) {
            return ['eligible' => false, 'minutes' => 0, 'formatted' => 'No OT', 'label' => 'OT disabled'];
        }

        // --- Set rule parameters
        if ($isWorkingDay && $otRule->ot_working_day == 1) {
            $minWorkMinutes = ($otRule->ot_min_work_per_day ?? 0) * 60;
        } elseif ($isNonWorkingDay && $otRule->ot_non_working_day == 1) {
            $minWorkMinutes = ($otRule->ot_max_co_per_day ?? 0) * 60;
        } else {
            return ['eligible' => false, 'minutes' => 0, 'formatted' => 'No OT', 'label' => 'Day not eligible'];
        }

        $minOTMinutes   = $otRule->ot_min_work_required ?? 0;
        $maxOTPerDay    = ($otRule->ot_max_work_per_day ?? 0) * 60;
        $bufferMinutes  = $otRule->ot_buffer_mins_per_day ?? 0;
        $eligibleOTMinutes = 0;

        // --- Start OT Calculation
        if ($workedMinutes >= $minWorkMinutes) {
            switch ($otRule->ot_calculation_method) {
                case 'in_time':
                    $diff = $getDiffInMinutes($checkInTime, $shiftStartTime);
                    $eligibleOTMinutes = (strtotime($checkInTime) < strtotime($shiftStartTime)) ? $diff : 0;
                    break;

                case 'out_time':
                    $diff = $getDiffInMinutes($checkOutTime, $shiftEndTime);
                    $eligibleOTMinutes = (strtotime($checkOutTime) > strtotime($shiftEndTime))
                        ? max(0, $diff - $bufferMinutes)
                        : 0;
                    break;

                case 'both_in_out_time':
                case 'both':
                    $inDiff = (strtotime($checkInTime) < strtotime($shiftStartTime))
                        ? $getDiffInMinutes($checkInTime, $shiftStartTime)
                        : 0;

                    $outDiff = (strtotime($checkOutTime) > strtotime($shiftEndTime))
                        ? max(0, $getDiffInMinutes($checkOutTime, $shiftEndTime) - $bufferMinutes)
                        : 0;

                    $eligibleOTMinutes = $inDiff + $outDiff;
                    break;
            }

            // --- Limitations
            if ($eligibleOTMinutes < $minOTMinutes) $eligibleOTMinutes = 0;
            if ($eligibleOTMinutes > $maxOTPerDay) $eligibleOTMinutes = $maxOTPerDay;
        }

        // --- Format result
        $otHours = floor($eligibleOTMinutes / 60);
        $remainingMinutes = $eligibleOTMinutes % 60;

        $formatted = $eligibleOTMinutes > 0
            ? trim(($otHours > 0 ? "{$otHours} Hour " : '') . ($remainingMinutes > 0 ? "{$remainingMinutes} Min" : ''))
            : 'No OT';

        $calcMethod = match($otRule->ot_calculation_method) {
            'in_time' => 'In-time only',
            'out_time' => 'Out-time only',
            'both_in_out_time', 'both' => 'Both In & Out',
            default => 'N/A',
        };

        $label = ($isWorkingDay ? 'Working Day' : 'Non-working Day') . " | Method: {$calcMethod} | Buffer: {$bufferMinutes} min";

        return [
            'eligible'  => $eligibleOTMinutes > 0,
            'minutes'   => $eligibleOTMinutes,
            'formatted' => $formatted,
            'label'     => $label,
        ];
    }

    public static function calculateOT(object $attendance_data): float
    {
        $user = Auth::user();
        // --- Local helper
        $getDiffInMinutes = function ($time1, $time2) {
            if (empty($time1) || empty($time2)) return 0;
            $t1 = new DateTime($time1);
            $t2 = new DateTime($time2);
            $diff = $t1->diff($t2);
            return ($diff->h * 60) + $diff->i;
        };

        if ($attendance_data->al_pst_id) {
            $assignShift = PolicyShiftTiming::where('pst_id', $attendance_data->al_pst_id ?? null)->first();
        } else {
            $assignShift = PolicyShiftTiming::where('pst_id', $attendance_data->atd_pst_id ?? null)->first();
        }
        
        if (!$assignShift) return 0;

        //OT Rule get data
        $otRule = OvertimePolicy::where('ot_b_id', $user->emp_b_id)->first();

        // --- Extract base data
        $statusId = $attendance_data->al_attendance_status ? $attendance_data->al_attendance_status : ($attendance_data->atd_attendance_status ?? null);

        $checkInRaw  = $attendance_data->al_check_in_time ?? $attendance_data->atd_check_in_time ?? null;
        $checkOutRaw = $attendance_data->al_check_out_time ?? $attendance_data->atd_check_out_time ?? null;

        $checkInTime = $checkInRaw instanceof Carbon ? $checkInRaw->format('H:i:s') : (!empty($checkInRaw) ? date('H:i:s', strtotime($checkInRaw)) : null);
        $checkOutTime = $checkOutRaw instanceof Carbon ? $checkOutRaw->format('H:i:s') : (!empty($checkOutRaw) ? date('H:i:s', strtotime($checkOutRaw)) : null);

        $shiftStartTime = $assignShift->pst_start_time instanceof Carbon ? $assignShift->pst_start_time->format('H:i:s') : date('H:i:s', strtotime($assignShift->pst_start_time));

        $shiftEndTime = $assignShift->pst_end_time instanceof Carbon ? $assignShift->pst_end_time->format('H:i:s') : date('H:i:s', strtotime($assignShift->pst_end_time));

        $workingHour = $attendance_data->al_total_worked_hours ? $attendance_data->al_total_worked_hours : ($attendance_data->atd_total_worked_hours ?? null);
        $workedMinutes  = round($workingHour * 60);

        // --- Identify day type
        $isWorkingDay    = !in_array($statusId, [319, 320]);
        $isNonWorkingDay = in_array($statusId, [319, 320]);
        $eligibleOTMinutes = 0;

        // $canApplyOT = (
        //     $otRule->ot_is_enabled == 1 &&
        //     (!isset($assignShift->pst_type_id) || $otRule->ot_shift_type == $assignShift->pst_type_id)
        // );

        // if (!$canApplyOT) return 0;

        // --- Set rule parameters
        // if ($isWorkingDay && $otRule->ot_working_day == 1) {
        //     $minWorkMinutes = ($otRule->ot_min_work_per_day ?? 0) * 60;
        // } elseif ($isNonWorkingDay && $otRule->ot_non_working_day == 1) {
        //     $minWorkMinutes = ($otRule->ot_max_co_per_day ?? 0) * 60;
        // } else {
        //     return 0;
        // }

        $minWorkMinutes = ($otRule->ot_min_work_per_day ?? 0) * 60 ?? 0;

        $minOTMinutes   = $otRule->ot_min_work_required ?? 0;
        $maxOTPerDay    = ($otRule->ot_max_work_per_day ?? 0) * 60;
        $bufferMinutes  = $otRule->ot_buffer_mins_per_day ?? 0;

        // --- Start OT Calculation
        if ($workedMinutes >= $minWorkMinutes) {

            switch ($otRule->ot_calculation_method) {
                case 'in_time':
                    $diff = $getDiffInMinutes($checkInTime, $shiftStartTime);
                    $eligibleOTMinutes = (strtotime($checkInTime) < strtotime($shiftStartTime)) ? $diff : 0;
                    break;

                case 'out_time':
                    $diff = $getDiffInMinutes($checkOutTime, $shiftEndTime);
                    $eligibleOTMinutes = (strtotime($checkOutTime) > strtotime($shiftEndTime))
                        ? max(0, $diff - $bufferMinutes)
                        : 0;
                    break;

                case 'both_in_out_time':
                case 'both':
                    $inDiff = (strtotime($checkInTime) < strtotime($shiftStartTime))
                        ? $getDiffInMinutes($checkInTime, $shiftStartTime)
                        : 0;

                    $outDiff = (strtotime($checkOutTime) > strtotime($shiftEndTime))
                        ? max(0, $getDiffInMinutes($checkOutTime, $shiftEndTime) - $bufferMinutes)
                        : 0;

                    $eligibleOTMinutes = $inDiff + $outDiff;
                    break;
            }

            if ($eligibleOTMinutes < $minOTMinutes) $eligibleOTMinutes = 0;
            if ($eligibleOTMinutes > $maxOTPerDay) $eligibleOTMinutes = $maxOTPerDay;
        }

        // Convert minutes to decimal hours
        $otHours = floor($eligibleOTMinutes / 60);
        $otMinutes = $eligibleOTMinutes % 60;
        $decimalOT = round($otHours + ($otMinutes / 100), 2);
        return $decimalOT;
    }

    public static function convertHourMins($workHrs)
    {
        // Handle null, empty, or non-numeric values
        if (!is_numeric($workHrs)) {
            return '--';
        }

        $workHrs = (float) $workHrs; // ensure it's numeric
        $totalMinutes = round($workHrs * 60);
        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        return $hours . '.' . str_pad($minutes, 2, '0', STR_PAD_LEFT);
    }

    public function handleOvertimeRequest($attendanceLog, $user)
    {
        // ✅ Calculate overtime once and reuse the result
        $overtimeHours = CentralLogics::calculateOT($attendanceLog);

        if (!$overtimeHours) {
            // No overtime, nothing to process
            return response()->json([
                'result' => [],
                'status' => false,
                'message' => 'No overtime calculated for this attendance log.'
            ]);
        }

        // ✅ Update attendance log
        $attendanceLog->al_is_overtime = 1;
        $attendanceLog->al_overtime_hours = $overtimeHours;
        $attendanceLog->save();

        // ✅ Fetch rule criteria for approval settings
        $ruleCriteria = RuleCriterion::with('fh_approval_module')
            ->where('rc_b_id', $user->emp_b_id)
            ->where('rc_condition_option_id', 140)
            ->whereHas('fh_approval_module', function ($query) {
                $query->where('am_module_id', 229)
                      ->where('am_status', 1);
            })
            ->first();

        $processApprovers = [];
        $approvalEmpIds = [];
        $amId = null;

        // ✅ Get approvers from rule criteria if available
        if ($ruleCriteria && $ruleCriteria->fh_approval_module) {
            $processApprovers = $ruleCriteria->fh_approval_module
                ->filteredProcessApprovers($user->emp_d_id)
                ->get();
        }

        // ✅ Determine approvers or fallback to default mapping
        if (empty($processApprovers)) {
            // Fallback: use ApprovalHelper to find mappings
            $approvalMapping = ApprovalHelper::getApprovalMapping($user->emp_b_id, $user->emp_id, 229);

            if (!$approvalMapping) {
                return response()->json([
                    'result' => [],
                    'status' => false,
                    'message' => 'Sorry! No approval settings found for MIP-punch module. Contact administration.'
                ]);
            }

            $amId = $approvalMapping->eam_am_id ?? null;
            $approvalEmpIds = ApprovalHelper::getApprovalArray($approvalMapping);
        } else {
            // Use rule-based approval module and approvers
            $amId = $ruleCriteria->rc_am_id;
            foreach ($processApprovers as $pa) {
                if (!empty($pa->pa_emp_id)) {
                    $approvalEmpIds[] = $pa->pa_emp_id;
                }
            }
        }

        // ✅ Create or update the overtime approval status record
        OtApprovalStatus::createOrupdate(
            [
                'ot_atd_id' => $attendanceLog->al_id,
                'ot_b_id'   => $user->emp_b_id,
                'ot_date'   => $attendanceLog->al_date,
            ],
            [
                'ot_atd_id'          => $attendanceLog->al_id,
                'ot_b_id'            => $user->emp_b_id,
                'ot_date'            => $attendanceLog->al_date,
                'ot_module_id'       => 562,
                'ot_next_approver'   => $approvalEmpIds[0] ?? null, // first approver, if available
                'ot_requested_status'=> 140,
                'ot_am_id'           => $amId,
                'ot_stage_completed' => 0,
            ]
        );

        return response()->json([
            'result' => [
                'attendance_id' => $attendanceLog->al_id,
                'overtime_hours' => $overtimeHours,
                'approvers' => $approvalEmpIds,
            ],
            'status' => true,
            'message' => 'Overtime request created successfully and sent for approval.'
        ]);
    }

    public static function getResolverData($employee, $date){
        $resolvedShift = ShiftResolver::resolveEmployeeShift($employee, $date);
        $shift = $resolvedShift->shift ?? PolicyShiftTiming::find($employee->emp_shift_type_id);
        return [
            'shift_name'  => $shift->pst_name ?? '--',
            'shift_start' => $shift->pst_start_time
                ? Carbon::parse($shift->pst_start_time)->format('Y-m-d H:i')
                : '--',
            'shift_end'   => $shift->pst_end_time
                ? Carbon::parse($shift->pst_end_time)->format('Y-m-d H:i')
                : '--',
        ];
    }

    public static function getResolverDataMultiple($employee, $date)
    {
        $resolvedShift = ShiftResolver::resolveEmployeeShift($employee, $date);

        // ================= DEPARTMENT MULTIPLE SHIFTS =================
        if (
            $resolvedShift &&
            $resolvedShift->resolved_from === 'department' &&
            $employee->fh_department &&
            $employee->fh_department->d_pst_id
        ) {
            $shiftIds = is_array($employee->fh_department->d_pst_id)
                ? $employee->fh_department->d_pst_id
                : explode(',', $employee->fh_department->d_pst_id);

            $shiftIds = array_filter(array_map('intval', $shiftIds));

            $shifts = PolicyShiftTiming::whereIn('pst_id', $shiftIds)
                ->orderByRaw('FIELD(pst_id, ' . implode(',', $shiftIds) . ')')
                ->get();

            return [
                'resolved_from' => 'department',
                'shifts' => $shifts->map(function ($shift) {
                    return [
                        'shift_id'   => $shift->pst_id,
                        'shift_name' => $shift->pst_name ?? '--',
                        'shift_start'=> $shift->pst_start_time
                            ? $shift->pst_start_time->format('H:i')
                            : '--',
                        'shift_end'  => $shift->pst_end_time
                            ? $shift->pst_end_time->format('H:i')
                            : '--',
                    ];
                })->values(),
            ];
        }

        // ================= SINGLE SHIFT =================
        $shift = $resolvedShift->shift
            ?? PolicyShiftTiming::find($employee->emp_shift_type_id);

        if (!$shift) {
            return [
                'resolved_from' => 'none',
                'shifts' => [],
            ];
        }

        return [
            'resolved_from' => $resolvedShift->resolved_from ?? 'employee',
            'shifts' => [[
                'shift_id'   => $shift->pst_id,
                'shift_name' => $shift->pst_name ?? '--',
                'shift_start'=> $shift->pst_start_time
                    ? $shift->pst_start_time->format('H:i')
                    : '--',
                'shift_end'  => $shift->pst_end_time
                    ? $shift->pst_end_time->format('H:i')
                    : '--',
            ]],
        ];
    }

    public static function calculateOTRoster(object $attendance_data): float
    {
        $user = Auth::user();
        $emp_id = $attendance_data->al_emp_id ?? $attendance_data->atd_emp_id;
        $employee = Employee::where('emp_id', $emp_id)->first();

        $date = $attendance_data->al_date ? $attendance_data->al_date?->format('Y-m-d') : $attendance_data->atd_date?->format('Y-m-d');
        $checkInTime = $attendance_data->al_check_in_time ? $attendance_data->al_check_in_time?->format('H:i:s') : $attendance_data->atd_check_in_time?->format('H:i:s');
        $checkOutTime = $attendance_data->al_check_out_time ? $attendance_data->al_check_out_time?->format('H:i:s') : $attendance_data->atd_check_out_time?->format('H:i:s');

        $resolvedShift = ShiftResolver::resolveEmployeeShift($employee, $date, $checkInTime, $checkOutTime);
        $assignShift = $resolvedShift->shift ?? PolicyShiftTiming::find($employee->emp_shift_type_id);

        if (!$assignShift) return 0;

        if (!$checkInTime || !$checkOutTime) return 0;

        // --------------------
        // OT policy
        // --------------------
        $otRule = OvertimePolicy::where('ot_b_id', $user->emp_b_id)->first();
        if (!$otRule || $otRule->ot_is_enabled != 1) return 0;

        // --------------------
        // Attendance times
        // --------------------
        $checkIn  = Carbon::parse($attendance_data->al_check_in_time ?? $attendance_data->atd_check_in_time);
        $checkOut = Carbon::parse($attendance_data->al_check_out_time ?? $attendance_data->atd_check_out_time);
        $startTime = Carbon::parse($assignShift->pst_start_time)->format('H:i:s');
        $endTime = Carbon::parse($assignShift->pst_end_time)->format('H:i:s');

        // --------------------
        // Anchor shift times to check-in date (CRITICAL FIX)
        // --------------------
        $shiftStart = Carbon::parse($checkIn->format('Y-m-d') . ' ' . $startTime);
        $shiftEnd = Carbon::parse($checkIn->format('Y-m-d') . ' ' . $endTime);

        // Checkout crossed midnight
        if ($checkOut->lte($checkIn)) {
            $checkOut->addDay();
        }

        // --------------------
        // Worked minutes validation
        // --------------------
        $workingHour2 = $attendance_data->al_total_worked_hours ?? $attendance_data->atd_total_worked_hours ?? 0;

        $workingHour = ($checkIn && $checkOut)
                    ? $checkIn->diffInMinutes($checkOut)
                    : 0;

        $workedMinutes = round($workingHour * 60);

        $statusId = $attendance_data->al_attendance_status
            ?? $attendance_data->atd_attendance_status;

        $isWorkingDay    = !in_array($statusId, [319, 320]);
        $isNonWorkingDay = in_array($statusId, [319, 320]);

        if (
            !(
                ($isWorkingDay && $otRule->ot_working_day == 1) ||
                ($isNonWorkingDay && $otRule->ot_non_working_day == 1)
            )
        ) {
            return 0;
        }

        $minWorkMinutes = ($otRule->ot_min_work_per_day ?? 0) * 60;
        if ($workedMinutes < $minWorkMinutes) return 0;

        // --------------------
        // OT parameters
        // --------------------
        $bufferMinutes = $otRule->ot_buffer_mins_per_day ?? 0;
        $minOTMinutes  = $otRule->ot_min_work_required ?? 0;
        $maxOTPerDay   = ($otRule->ot_max_work_per_day ?? 0) * 60;

        $eligibleOTMinutes = 0;
        // --------------------
        // OT calculation
        // --------------------
        switch ($otRule->ot_calculation_method) {

            case 'in_time':
                if ($checkIn->lt($shiftStart)) {
                    $eligibleOTMinutes = $checkIn->diffInMinutes($shiftStart);
                }
                break;

            case 'out_time':

                // Calculate shift duration in minutes
                $shiftDurationMinutes = $shiftStart->diffInMinutes($shiftEnd);

                // OT should start only after full shift duration is completed
                $shiftCompletionTime = $checkIn->copy()->addMinutes($shiftDurationMinutes);

                if ($checkOut->gt($shiftCompletionTime)) {
                    $eligibleOTMinutes = max(
                        0,
                        $shiftCompletionTime->diffInMinutes($checkOut) - $bufferMinutes
                    );
                }
                break;

            case 'both':
            case 'both_in_out_time':

                $inOT = $checkIn->lt($shiftStart)
                    ? $checkIn->diffInMinutes($shiftStart)
                    : 0;

                $outOT = $checkOut->gt($shiftEnd)
                    ? max(0, $shiftEnd->diffInMinutes($checkOut) - $bufferMinutes)
                    : 0;

                $eligibleOTMinutes = $inOT + $outOT;
                break;
        }

        // --------------------
        // OT limits
        // --------------------
        if ($eligibleOTMinutes < $minOTMinutes) $eligibleOTMinutes = 0;
        // if ($eligibleOTMinutes > $maxOTPerDay) $eligibleOTMinutes = $maxOTPerDay;

        // --------------------
        // Convert to decimal hours
        // --------------------
        $hours   = floor($eligibleOTMinutes / 60);
        $minutes = $eligibleOTMinutes % 60;

        return round($hours + ($minutes / 100), 2);
    }

    public static function newGetMonthlyAttendanceDetailsGPT($employee, $month, $year, $holiday_record_exits, $weekOfDates)
    {
        $salaryDay = $totalOTHrs = 0;
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        $dateRange = CarbonPeriod::create($startDate, $endDate);
        $isAbsCheck = $employee->fh_attendance_policy?->ap_mark_absent_check;

        $masterIds = [157, 170, 201, 203, 215, 228, 235, 236, 251, 252, 319, 320, 321, 322];
        $masters = MasterTable::whereIn('m_id', $masterIds)->get()->keyBy('m_id');

        $getMaster = function ($id) use ($masters) {
            if (!is_scalar($id)) {
                return null; // safety
            }
            $id = (int) $id;
            $m = $masters->get($id);
            if (!$m) return null;
            $other = json_decode($m->m_other ?? '{}', true);
            return (object)[
                'm_id'   => $m->m_id,
                'm_name' => $m->m_name ?? '',
                'm_type' => $m->m_type ?? '',
                'color'  => $other['color'] ?? '#000000',
            ];
        };

        $approveStatus   = $getMaster(157);
        $rejectStatus    = $getMaster(170);
        $fullDayLeave    = $getMaster(201);
        $absentStatus    = $getMaster(203);
        $uplStatus       = $getMaster(215);
        $mspStatus       = $getMaster(228);
        $firstHalfLeave  = $getMaster(235);
        $secondHalfLeave = $getMaster(236);
        $presentStatus   = $getMaster(251);
        $halfDayStatus   = $getMaster(252);
        $hoPresentStatus = $getMaster(319);
        $woPresentStatus = $getMaster(320);
        $holidayStatus   = $getMaster(321);
        $weekOffStatus   = $getMaster(322);

        // Preload everything to avoid repeated DB hits
        $attendanceRecords = $employee->attendance_record()
            ->whereBetween('atd_date', [$startDate, $endDate])
            ->orderBy('atd_id', 'desc')
            ->get()
            ->keyBy(fn($row) => Carbon::parse($row->atd_date)->toDateString());

        $attendanceLogs = AttendanceLog::where('al_emp_id', $employee->emp_id)
            ->whereBetween('al_date', [$startDate, $endDate])
            ->get()
            ->keyBy(fn($row) => Carbon::parse($row->al_date)->toDateString());

        $missedPunches = AttendanceException::where('ae_b_id', $employee->emp_b_id)
            ->where('ae_emp_id', $employee->emp_id)
            ->where('ae_stage_completed', 1)
            ->where('ae_status', '!=', $rejectStatus->m_id)
            ->whereBetween('ae_date', [$startDate, $endDate])
            ->get()
            ->keyBy(fn($row) => Carbon::parse($row->ae_date)->toDateString());

        $leave_record_exits = $employee->leave_requests()->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('lvr_start_date', [$startDate, $endDate])
                ->orWhereBetween('lvr_end_date', [$startDate, $endDate]);
        })->where('lvr_status', '!=', $rejectStatus->m_id)
            ->where('lvr_stage_completed', 1)
            ->get();

        $leaveByDate = collect();

        foreach ($leave_record_exits as $leave) {
            $start = Carbon::parse($leave->lvr_start_date);
            $end = Carbon::parse($leave->lvr_end_date);

            while ($start->lte($end)) {
                $dateStr = $start->toDateString();
                if (!$leaveByDate->has($dateStr)) {
                    $leaveByDate->put($dateStr, collect());
                }
                $leaveByDate->get($dateStr)->push($leave);
                $start->addDay();
            }
        }

        $leave_record_exits = $leaveByDate;
        $attendanceData = [];

        // unpaid list → JSON array → convert into PHP array
        $unpaidWeekOffDays = isset($employee->fh_week_off_policy?->pwo_is_unpaid)
            ? json_decode($employee->fh_week_off_policy->pwo_is_unpaid, true)
            : null;

        $leave_category_ids = MasterTable::where('m_group', 'LEAVE_CATEGORY')->pluck('m_id')->toArray();
        $weekDayMasters = MasterTable::where('m_group', 'WEEK_DAY')
            ->get()
            ->keyBy(fn($m) => strtoupper($m->m_type));

        // Second pass: Generate attendance data
        foreach ($dateRange as $dateObj) {
            $date = $dateObj->toDateString();
            $is_found = null;
            $rowData = [
                'status' => '-',
                'status_id' => '-',
                'status_code' => '-',
                'statusColor' => '#BDBDBD',
                'checkingMethodId' => null,
                'checkInTime' => null,
                'checkOutTime' => null,
                'updatedBy' => null,
                'workingHour' => null,
                'OT' => null,
                'earlyExit' => null,
                'late' => null,
                'attendance_remark' => '--',
                'mark_as_absent' => '--',
                'checkInLocation' => '--',
                'checkOutLocation' => '--',
                'checkInPhoto' => [],
                'checkOutPhoto' => [],
                'atd_segments' => [],
                'presentCount' => 0,
                'holidayPresentCount' => 0,
                'weekOffPresentCount' => 0,
                'leaveCount' => 0,
                'holidayCount' => 0,
                'weekOffCount' => 0,
                'absentCount' => 0,
                'halfDayCount' => 0,
                'missedPunchCount' => 0,
                'overtimeCount' => 0,
                'lateCount' => 0,
                'earlyExitCount' => 0,
                'approvedLeaveCount' => 0,
                'approvedMissedPunchCount' => 0,
                'isApproved' => null,
                'previousCheckInTime' => null,
                'previousCheckOutTime' => null,
                'previousLate' => null,
                'previousExit' => null,
                'UPL' => 0,
                'totalSalariedDays' => 0,
                'totalOTHrs' => 0,
            ];

            // Skip dates before joining or after last working day
            $shouldCountDate = true;
            if ($employee->emp_date_of_joining && $dateObj->lt(Carbon::parse($employee->emp_date_of_joining))) {
                $shouldCountDate = false;
            }
            if ($employee->emp_last_working_day && $dateObj->gt(Carbon::parse($employee->emp_last_working_day))) {
                $shouldCountDate = false;
            }

            if ($shouldCountDate && $employee->emp_date_of_joining <= $date) {
                // Original logic continues here...
                $attendance_record_exit = $attendanceRecords[$date] ?? null;
                $attendance_log = $attendanceLogs[$date] ?? null;
                $missedPunch = $missedPunches[$date] ?? null;
                $holiday_record_exit = $holiday_record_exits[$date] ?? null;
                $leave_record_exit = $leave_record_exits->get($date) ?? null;
                $is_found = null;

                // current date ka weekday master id
                $weekKey = strtoupper($dateObj->format('D'));
                $currentWeekDayMasterId = $weekDayMasters[$weekKey]->m_id ?? null;

                // check unpaid or paid
                if (is_array($unpaidWeekOffDays) && !is_null($unpaidWeekOffDays)) {
                    $isUnpaid = in_array($currentWeekDayMasterId, $unpaidWeekOffDays);
                } else {
                    $isUnpaid = false;
                }

                // Priority 1: Check AttendanceException if found
                if (isset($missedPunch) && !empty($missedPunch) && !$is_found) {
                    $rowData['missedPunchCount'] =  1;

                    // Check if missed punch is approved
                    $isApprovedMSP = ($missedPunch->ae_status == $approveStatus->m_id && $missedPunch->ae_stage_completed == 1);
                    if ($isApprovedMSP) {
                        // Show approved missed punch times
                        $is_found = 1;
                        $rowData['checkInTime'] = Carbon::createFromFormat('H:i:s', $missedPunch->ae_in_time)->format('H:i');
                        $rowData['checkOutTime'] = Carbon::createFromFormat('H:i:s', $missedPunch->ae_out_time)->format('H:i');
                        $rowData['workingHour'] = number_format($missedPunch->ae_total_working, 2, '.', '');
                        $rowData['attendance_remark'] = $missedPunch->ae_reason_id ? $missedPunch?->fh_mispunch_reason->m_name : ($missedPunch->ae_custom_reason ?? 'N/A');

                        $resolvedShift = ShiftResolver::resolveEmployeeShift($employee, $date);
                        $shift = $resolvedShift->shift ?? PolicyShiftTiming::find($employee->emp_shift_type_id);

                        $pst_start_time = Carbon::parse($shift->pst_start_time ?? '09:00:00');
                        $pst_end_time = Carbon::parse($shift->pst_end_time ?? '18:00:00');
                        $pst_exit_time = Carbon::parse($shift->pst_min_work_hour);

                        $dailyWorkingHours = $pst_start_time->diffInMinutes($pst_end_time);
                        $dailyExitHours = $pst_start_time->diffInMinutes($pst_exit_time);
                        $checkInTime = Carbon::parse($date . ' ' . $missedPunch->ae_in_time);
                        $checkOutTime =  Carbon::parse($date . ' ' . $missedPunch->ae_out_time);
                        $workedDuration = $checkInTime->diffInMinutes($checkOutTime);
                        $fullDayThreshold = $dailyWorkingHours;
                        $halfDayThreshold = $dailyWorkingHours / 2;
                        $halfDayExitThreshold = isset($dailyExitHours) ? $dailyExitHours / 2 : $halfDayThreshold;

                        if ($workedDuration >= $dailyExitHours || $workedDuration >= $fullDayThreshold) {
                            $isWeekOff = in_array($date, $weekOfDates);
                            $master    = $isWeekOff ? $woPresentStatus : $presentStatus;

                            $rowData['status_id']              = $master->m_id;
                            $rowData['status']                 = $master->m_name;
                            $rowData['status_code']            = $master->m_type;
                            $rowData['statusColor']            = $master->color;
                            $rowData['approvedMissedPunchCount'] = 1;
                            if ($isWeekOff) {
                                $rowData['weekOffPresentCount'] = 1;
                                $rowData['weekOffCount'] = $isUnpaid ? 0 : 1;
                            } else {
                                $rowData['presentCount'] = 1;
                            }
                        } else if ($workedDuration >= $halfDayExitThreshold || $workedDuration >= $halfDayThreshold) {
                            //half day present & half day leave case
                            $halfDayPresent = $getMaster($missedPunch->ae_attendance_status ?? $halfDayStatus->m_id);
                            if ($leave_record_exit && $leave_record_exit->count() == 1) {
                                $leaveExit = $leave_record_exit->first();
                                $segmentId = $leaveExit?->fh_leave_day_segment->m_id;
                                $leaveType = $leaveExit->fh_leave_cat_type;
                                if ($segmentId == $secondHalfLeave->m_id) {
                                    $rowData['status_id'] = $halfDayPresent->m_id.'/'.$leaveType->m_id;
                                    $rowData['status'] = $halfDayPresent->m_name.'/'.$leaveType->m_name;
                                    $rowData['status_code'] = $halfDayPresent->m_type.'/'.$leaveType->m_type;
                                    $rowData['statusColor'] = $halfDayPresent->color.'/'.$leaveType->color;
                                } elseif ($segmentId == $firstHalfLeave->m_id) {
                                    $rowData['status_id'] = $leaveType->m_id.'/'.$halfDayPresent->m_id;
                                    $rowData['status'] = $leaveType->m_name.'/'.$halfDayPresent->m_name;
                                    $rowData['status_code'] = $leaveType->m_type.'/'.$halfDayPresent->m_type;
                                    $rowData['statusColor'] = $leaveType->color.'/'.$halfDayPresent->color;
                                }
                            } else {
                                $rowData['status_id']   = $halfDayPresent->m_id;
                                $rowData['status']      = $halfDayPresent->m_name;
                                $rowData['status_code'] = $halfDayPresent->m_type;
                                $rowData['statusColor'] = $halfDayPresent->color;
                            }
                            $rowData['presentCount'] = 0.5;
                            $rowData['approvedMissedPunchCount'] = 0.5;
                        } else {
                            $rowData['checkInTime'] = Carbon::createFromFormat('H:i:s', $missedPunch->ae_in_time)->format('H:i');
                            $rowData['checkOutTime'] =  Carbon::createFromFormat('H:i:s', $missedPunch->ae_out_time)->format('H:i');
                            $rowData['workingHour'] = number_format($missedPunch->ae_total_working, 2, '.', '');

                            $rowData['status_id'] = $absentStatus->m_id;
                            $rowData['status'] =   $absentStatus->m_name;
                            $rowData['status_code'] = $absentStatus->m_type;
                            $rowData['statusColor'] = $absentStatus->color;
                            $rowData['absentCount'] = 1;
                        }
                    }
                }

                // Priority 2: Check AttendanceLog if no AttendanceException found
                if (isset($attendance_log) && !empty($attendance_log) && !isset($is_found)) {
                    $is_found = 1;

                    // Set basic attendance data from log
                    $rowData['checkInTime'] = $attendance_log->al_check_in_time ? Carbon::parse($attendance_log->al_check_in_time)->format('H:i') : '';
                    $rowData['checkOutTime'] = $attendance_log->al_check_out_time ? Carbon::parse($attendance_log->al_check_out_time)->format('H:i') :  '';
                    $rowData['workingHour'] = $attendance_log->al_total_worked_hours ? number_format($attendance_log->al_total_worked_hours, 2, '.', '') : '';
                    $rowData['attendance_remark'] = $attendance_log->al_reason ? $attendance_log->al_reason : ($attendance_record_exit?->atd_remark ?? null);
                    $rowData['mark_as_absent'] = $attendance_log->al_is_absent ? $attendance_log->al_is_absent : ($attendance_record_exit?->atd_is_absent ?? null);

                    $rowData['late'] = ($attendance_log->al_is_late && $attendance_log->al_late_duration) ? number_format($attendance_log->al_late_duration, 2, '.', '') :  null;
                    $rowData['earlyExit'] = ($attendance_log->al_is_early_exit && $attendance_log->al_early_exit_duration) ? number_format($attendance_log->al_early_exit_duration, 2, '.', '') :  null;

                    $rowData['updatedBy'] = optional($attendance_log->fh_employee_data)->emp_full_name ?? '-';
                    $rowData['previousCheckInTime'] = isset($attendance_record_exit->atd_check_in_time) && !empty($attendance_record_exit->atd_check_in_time)
                        ? Carbon::parse($attendance_record_exit->atd_check_in_time)->format('H:i')
                        : '-';

                    $rowData['previousCheckOutTime'] = isset($attendance_record_exit->atd_check_out_time) && !empty($attendance_record_exit->atd_check_out_time)
                        ? Carbon::parse($attendance_record_exit->atd_check_out_time)->format('H:i')
                        : '-';

                    $rowData['previousLate'] = isset($attendance_record_exit->atd_is_late) && !empty($attendance_record_exit->atd_late_duration) ? number_format($attendance_record_exit->atd_late_duration, 2, '.', '') : null;
                    $rowData['previousExit'] = isset($attendance_record_exit->atd_is_early_exit) && !empty($attendance_record_exit->atd_early_exit_duration) ? number_format($attendance_record_exit->atd_early_exit_duration, 2, '.', '') : null;

                    // Determine status based on log data
                    if ($attendance_log->al_attendance_status) {
                        $statusData = $getMaster($attendance_log->al_attendance_status);
                        if ($statusData) {
                            $rowData['status_id'] = $statusData->m_id;
                            $rowData['status'] = $statusData->m_name;
                            $rowData['status_code'] = $statusData->m_type;
                            $rowData['statusColor'] = $statusData->color;

                            if ($statusData->m_id == 251) { // Present
                                $rowData['presentCount'] = 1;
                            } elseif ($statusData->m_id == 252) { // Half Day
                                $rowData['halfDayCount'] = 1;
                                $rowData["presentCount"] = 0.5;
                            } elseif ($statusData->m_id == 319) { // Holiday Present
                                $rowData["holidayPresentCount"] = 1;
                            } elseif ($statusData->m_id == 320) { // Week Off Present
                                $rowData["weekOffPresentCount"] = 1;
                                $rowData['weekOffCount'] = $isUnpaid ? 0 : 1;
                            }
                        }
                    } else {
                        // Default to present if no specific status but has attendance log
                        $rowData['status_id'] = $presentStatus->m_id;
                        $rowData['status'] = $presentStatus->m_name;
                        $rowData['status_code'] = $presentStatus->m_type;
                        $rowData['statusColor'] = $presentStatus->color;
                        $rowData['presentCount'] = 1;
                    }

                    if ($attendance_log->al_is_overtime == 1) {
                        $rowData['overtimeCount'] = 1;
                        $rowData['OT'] = $attendance_log->al_overtime_hours ? (string)(round($attendance_log->al_overtime_hours, 2)) : null;
                        // $rowData['OT'] = $attendance_log->al_overtime_hours ? '1' : '0';
                    }

                    if ($attendance_log->al_is_late == 1) {
                        $rowData['lateCount'] = 1;
                    }

                    if ($attendance_log->al_is_early_exit == 1) {
                        $rowData['earlyExitCount'] = 1;
                    }
                }

                // Priority 3: Check AttendanceRecord if no AttendanceException or AttendanceLog found
                if (!isset($is_found) && $attendance_record_exit) {
                    $atdStatusData = $attendance_record_exit->fh_attendance_status;
                    $atdStatusID = $attendance_record_exit->atd_attendance_status;
                    $rowData['status_id'] = $atdStatusData->m_id;
                    $rowData['status_code'] = $atdStatusData->m_type;
                    $rowData['status'] = $atdStatusData->m_name;
                    $rowData['statusColor'] = json_decode($atdStatusData->m_other, true)['color'];

                    if ($atdStatusID === 251) {
                        $rowData['presentCount'] = 1; //Present
                    } else if ($atdStatusID == 320) {
                        $rowData["weekOffPresentCount"] = 1;
                        $rowData['weekOffCount'] = $isUnpaid ? 0 : 1;
                    } else if ($atdStatusID == 319) {
                        $rowData["holidayPresentCount"] = 1;
                    } else if ($atdStatusID == 252) {
                        $rowData["presentCount"] = 0.5;
                    } else if ($atdStatusID == 203) {
                        $rowData["absentCount"] = 1;
                    }
                    $is_found = 1;

                    if ($attendance_record_exit->atd_is_overtime == 1) {
                        $rowData['overtimeCount'] = 1;
                    }

                    if ($attendance_record_exit->atd_is_late == 1) {
                        $rowData['lateCount'] = 1;
                    }

                    if ($attendance_record_exit->atd_is_early_exit == 1) {
                        $rowData['earlyExitCount'] = 1;
                    }

                    
                    $rowData['checkingMethodId'] = $attendance_record_exit->atd_checkin_method_id;
                    $rowData['checkInTime'] = $attendance_record_exit->atd_check_in_time ? Carbon::parse($attendance_record_exit->atd_check_in_time)->format('H:i') : '';
                    $rowData['checkOutTime'] = $attendance_record_exit->atd_check_out_time ? Carbon::parse($attendance_record_exit->atd_check_out_time)->format('H:i') :  '';
                    $rowData['workingHour'] = $attendance_record_exit->atd_total_worked_hours ? number_format($attendance_record_exit->atd_total_worked_hours, 2) : '';
                    $rowData['earlyExit'] = ($attendance_record_exit->atd_is_early_exit && $attendance_record_exit->atd_early_exit_duration) ? number_format($attendance_record_exit->atd_early_exit_duration, 2, '.', '') :  null;
                    $rowData['late'] = ($attendance_record_exit->atd_is_late && $attendance_record_exit->atd_late_duration) ? number_format($attendance_record_exit->atd_late_duration, 2, '.', '') :  null;
                    $rowData['OT'] = ($attendance_record_exit->atd_is_overtime && $attendance_record_exit->atd_overtime_hours) ? (string)(round($attendance_record_exit->atd_overtime_hours, 2)) :  null;
                    $rowData['attendance_remark'] = $attendance_record_exit->atd_remark  ? $attendance_record_exit->atd_remark :  '';
                    $rowData['checkInLocation'] = $attendance_record_exit && $attendance_record_exit->atd_punchin_location ? $attendance_record_exit->atd_punchin_location : '--';
                    $rowData['checkOutLocation'] =  $attendance_record_exit && $attendance_record_exit->atd_punchout_location ? $attendance_record_exit->atd_punchout_location : '--';
                    $rowData['checkInPhoto'] = $attendance_record_exit && $attendance_record_exit->atd_punchin_photo ? json_decode($attendance_record_exit->atd_punchin_photo) : [];
                    $rowData['checkOutPhoto'] =  $attendance_record_exit && $attendance_record_exit->atd_punchout_photo ? json_decode($attendance_record_exit->atd_punchout_photo) : [];
                    $rowData['atd_segments'] =  $attendance_record_exit && $attendance_record_exit->atd_segments ? [json_decode($attendance_record_exit->atd_segments)] : [];
                    $rowData['updatedBy'] = optional($attendance_record_exit->updated_by)->emp_full_name ?? '-';
                }

                $half_day_present = ($attendance_log && (int)$attendance_log->al_attendance_status === $halfDayStatus->m_id) || ($attendance_record_exit && (int)$attendance_record_exit->atd_attendance_status === $halfDayStatus->m_id);

                if (is_null($is_found) || $half_day_present) {
                    if ($leave_record_exit && $leave_record_exit->count() == 1) {
                        $lvr_exit = $leave_record_exit->first();
                        $lvrCatTypeId = $lvr_exit?->fh_leave_cat_type;
                        $is_unpaid = $lvrCatTypeId->m_id == $uplStatus->m_id;
                        $is_approved = $lvr_exit->lvr_status != $rejectStatus->m_id && $lvr_exit->lvr_stage_completed;
                        $is_half_day_leave = optional($lvr_exit->fh_leave_day_type)->m_id != $fullDayLeave->m_id;
                        $segment_id = $lvr_exit->fh_leave_day_segment->m_id ?? null;

                        if (in_array($lvrCatTypeId->m_id, $leave_category_ids) && $is_approved) {
                            $is_found = 1;

                            // Case: Half-day present + Half-day leave
                            if ($half_day_present && $is_half_day_leave) {
                                $hdPreStatus = $attendance_log?->fh_attendance_status ?? $attendance_record_exit?->fh_attendance_status;
                                $hdPreColor = json_decode($hdPreStatus->m_other, true)['color'];
                                $lvrCatTypeColor = json_decode($lvrCatTypeId->m_other, true)['color'];

                                if ($segment_id == $secondHalfLeave->m_id) {
                                    $rowData['status_id'] = "{$hdPreStatus->m_id}/{$lvrCatTypeId->m_id}";
                                    $rowData['status'] = "{$hdPreStatus->m_name}/{$lvrCatTypeId->m_name}";
                                    $rowData['status_code'] = "{$hdPreStatus->m_type}/{$lvrCatTypeId->m_type}";
                                    $rowData['statusColor'] = "{$hdPreColor}/{$lvrCatTypeColor}";
                                } else {
                                    $rowData['status_id'] = "{$lvrCatTypeId->m_id}/{$hdPreStatus->m_id}";
                                    $rowData['status'] = "{$lvrCatTypeId->m_name}/{$hdPreStatus->m_name}";
                                    $rowData['status_code'] = "{$lvrCatTypeId->m_type}/{$hdPreStatus->m_type}";
                                    $rowData['statusColor'] = "{$lvrCatTypeColor}/{$hdPreColor}";
                                }

                                // FIXED COUNTS - Half day present + Half day leave
                                $rowData['presentCount'] = 0.5;
                                $rowData['UPL']        = $is_unpaid ? 0.5 : 0;
                                $rowData['leaveCount'] = $is_unpaid ? 0   : 0.5;
                                $rowData['approvedLeaveCount'] = $is_unpaid ? 0 : 0.5;
                            }

                            // Case: Only single full/half leave (no half-day present)
                            elseif (!$half_day_present) {
                                // dd($lvrCatTypeId);
                                $rowData['status_id'] = $lvrCatTypeId->m_id;
                                $rowData['status'] = $lvrCatTypeId->m_name;
                                $rowData['status_code'] = $lvrCatTypeId->m_type;
                                $rowData['statusColor'] = json_decode($lvrCatTypeId->m_other, true)['color'];

                                $rowData['presentCount'] = 0;
                                $rowData['UPL'] = $is_unpaid ? ($is_half_day_leave ? 0.5 : 1) : 0;
                                $rowData['leaveCount'] = $is_unpaid ? 0 : ($is_half_day_leave ? 0.5 : 1);
                                $rowData['approvedLeaveCount'] = $is_unpaid ? 0 : ($is_half_day_leave ? 0.5 : 1);

                                if ($rowData['leaveCount'] == 0.5) {
                                    $rowData['status'] = 'HD_'.$lvrCatTypeId->m_name;
                                }
                            }
                        }
                    }

                    // Case: Two half-day leaves like CL/SL or CL/UPL etc.
                    elseif ($leave_record_exit && $leave_record_exit->count() == 2) {
                        $leaves = $leave_record_exit->sortBy(function ($l) {
                            return optional($l->fh_leave_day_segment)->m_id ?? 0;
                        })->values();

                        $l1 = $leaves[0];
                        $l2 = $leaves[1];

                        $is_approved1 = $l1->lvr_status != $rejectStatus->m_id && $l1->lvr_stage_completed;
                        $is_approved2 = $l2->lvr_status != $rejectStatus->m_id && $l2->lvr_stage_completed;

                        if ($is_approved1 || $is_approved2) {
                            $l1CatType = $l1?->fh_leave_cat_type;
                            $l2CatType = $l2?->fh_leave_cat_type;
                            $rowData['status_id'] = "{$l1CatType->m_id}/{$l2CatType->m_id}";
                            $rowData['status'] = "{$l1CatType->m_name}/{$l2CatType->m_name}";
                            $rowData['status_code']  = "{$l1CatType->m_type}/{$l2CatType->m_type}";
                            $rowData['statusColor'] = json_decode($l1CatType->m_other, true)['color'] . '/' . json_decode($l2CatType->m_other, true)['color'];
                            $is_found = 1;

                            // FIXED COUNTS - Two half day leaves
                            $rowData['presentCount'] = 0; // No present count for leaves
                            $rowData['leaveCount'] = 0;
                            $rowData['UPL'] = 0;
                            $rowData['approvedLeaveCount'] = 0;

                            // Count first half day leave
                            if ($is_approved1) {
                                $isUpl = $l1CatType->m_id === $uplStatus->m_id;
                                $rowData['UPL']                += $isUpl ? 0.5 : 0;
                                $rowData['leaveCount']         += $isUpl ? 0   : 0.5;
                                $rowData['approvedLeaveCount'] += $isUpl ? 0   : 0.5;
                            }

                            // Count second half day leave  
                            if ($is_approved2) {
                                $isUpl2 = $l2CatType->m_id === $uplStatus->m_id;
                                $rowData['UPL']                += $isUpl2 ? 0.5 : 0;
                                $rowData['leaveCount']         += $isUpl2 ? 0   : 0.5;
                                $rowData['approvedLeaveCount'] += $isUpl2 ? 0   : 0.5;
                            }

                            $rowData['isApproved'] = $is_approved1 && $is_approved2;
                        }
                    }
                }

                if (is_null($is_found)) {
                    if ($holiday_record_exit) {
                        $hoPrevDate = Carbon::parse($date)->subDay()->toDateString();
                        $hoNextDate = Carbon::parse($date)->addDay()->toDateString();
                        $prevAbsent = self::isAbsentCheck($hoPrevDate, $absentStatus->m_id, $attendanceRecords, $attendanceLogs);
                        $nextAbsent = self::isAbsentCheck($hoNextDate, $absentStatus->m_id, $attendanceRecords, $attendanceLogs);
                        if ($prevAbsent && $nextAbsent && ($isAbsCheck == 1)) {
                            $rowData['absentCount'] = 1;
                            $rowData['status']       = $absentStatus->m_name;
                            $rowData['status_id']    = $absentStatus->m_id;
                            $rowData['status_code']  = $absentStatus->m_type;
                            $rowData['statusColor']  = $absentStatus->color;
                        } else {
                            $rowData['holidayCount'] = 1;
                            $rowData['status']       = $holidayStatus->m_name;
                            $rowData['status_id']    = $holidayStatus->m_id;
                            $rowData['status_code']  = $holidayStatus->m_type;
                            $rowData['statusColor']  = $holidayStatus->color;
                        }
                        $rowData['checkInTime']  = '';
                        $rowData['checkOutTime'] = '';
                        $rowData['workingHour']  = '';
                        $is_found = 1;
                    }
                }

                if (is_null($is_found)) {
                    if (in_array($date, $weekOfDates)) {
                        if (is_array($unpaidWeekOffDays) && !is_null($unpaidWeekOffDays)) {
                            $isUnpaid = in_array($currentWeekDayMasterId, $unpaidWeekOffDays);
                        } else {
                            $isUnpaid = false;
                        }
                        $woPrevDate = Carbon::parse($date)->subDay()->toDateString();
                        $woNextDate = Carbon::parse($date)->addDay()->toDateString();
                        $prevAbsent = self::isAbsentCheck($woPrevDate, $absentStatus->m_id, $attendanceRecords, $attendanceLogs);
                        $nextAbsent = self::isAbsentCheck($woNextDate, $absentStatus->m_id, $attendanceRecords, $attendanceLogs);
                        if ($prevAbsent && $nextAbsent && ($isAbsCheck == 1)) {
                            $rowData['absentCount'] = 1;
                            $rowData['status']       = $absentStatus->m_name;
                            $rowData['status_id']    = $absentStatus->m_id;
                            $rowData['status_code']  = $absentStatus->m_type;
                            $rowData['statusColor']  = $absentStatus->color;
                        } else {
                            $rowData['weekOffCount'] = $isUnpaid ? 0 : 1;
                            $rowData['status']       = $weekOffStatus->m_name;
                            $rowData['status_id']    = $weekOffStatus->m_id;
                            $rowData['status_code']  = $weekOffStatus->m_type;
                            $rowData['statusColor']  = $weekOffStatus->color;
                        }
                        $rowData['checkInTime']  = '';
                        $rowData['checkOutTime'] = '';
                        $rowData['workingHour']  = '';
                        $is_found = 1;
                    }
                }

                // Check for approved leaves (including UPL) for all dates
                if (is_null($is_found)) {
                    if ($leave_record_exit && $leave_record_exit->count() >= 1) {
                        $lvr_exit = $leave_record_exit->first();
                        $lvrCatTypeId = $lvr_exit?->fh_leave_cat_type;
                        $is_unpaid = $lvrCatTypeId->m_id == $uplStatus->m_id;
                        $is_approved = $lvr_exit->lvr_status != $rejectStatus->m_id && $lvr_exit->lvr_stage_completed;
                        $is_half_day_leave = optional($lvr_exit->fh_leave_day_type)->m_id != $fullDayLeave->m_id;

                        if ($is_approved) {
                            $rowData['status'] = $lvrCatTypeId->m_name;
                            $rowData['status_id'] = $lvrCatTypeId->m_id;
                            $rowData['status_code'] = $lvrCatTypeId->m_type;
                            $rowData['statusColor'] = json_decode($lvrCatTypeId->m_other, true)['color'];
                            $rowData['checkInTime'] = '';
                            $rowData['checkOutTime'] = '';
                            $rowData['workingHour'] = '';
                            $is_found = 1;

                            // Set counts based on leave type
                            $rowData['UPL']                = $is_unpaid ? ($is_half_day_leave ? 0.5 : 1) : 0;
                            $rowData['leaveCount']         = $is_unpaid ? 0 : ($is_half_day_leave ? 0.5 : 1);
                            $rowData['approvedLeaveCount'] = $is_unpaid ? 0 : ($is_half_day_leave ? 0.5 : 1);
                        }
                    }

                    // Only mark as absent for past dates with no leave or other status
                    if (is_null($is_found) && Carbon::parse($date)->isPast()) {
                        $rowData['absentCount'] = 1;
                        $rowData['status'] = $absentStatus->m_name;
                        $rowData['status_id'] = $absentStatus->m_id;
                        $rowData['status_code'] = $absentStatus->m_type;
                        $rowData['statusColor'] = $absentStatus->color;
                        $rowData['checkInTime'] = '';
                        $rowData['checkOutTime'] = '';
                        $rowData['workingHour'] = '';
                    }
                }

                if ($rowData['status_id']) {
                    $rowData['status_id'] = (string)$rowData['status_id'];
                }
                $rowData['date'] = $date;
                $attendanceData[] = $rowData;
            }
        }

        foreach ($attendanceData as $key => $value) {
            $totalOTHrs += $value['OT'];
            // 1. Present days
            $salaryDay += $value['presentCount'] ?? 0;

            // 2. Handle Half Day cases
            if (($value['halfDayCount'] ?? 0) == 1 && ($value['approvedLeaveCount'] ?? 0) == 0.5) {
                // Half-day + half leave = 1 full day
                $salaryDay += 1;
            } elseif (($value['halfDayCount'] ?? 0) == 1) {
                // Only half day present, no leave matched
                $salaryDay += 0.5;
            } else {
                // 3. Approved Leave (if not already paired with half-day)
                $salaryDay += $value['approvedLeaveCount'] ?? 0;
            }

            // 4. Week Off & Holiday
            $salaryDay += $value['weekOffCount'] ?? 0;
            $salaryDay += $value['holidayCount'] ?? 0;
        }

        $lastIndex = count($attendanceData) - 1;
        if (isset($attendanceData[$lastIndex])) {
            $attendanceData[$lastIndex]['totalSalariedDays'] = $salaryDay;
            $attendanceData[$lastIndex]['totalOTHrs'] = $totalOTHrs;
        }

        return $attendanceData;
    }

    public static function setMailConfiguration($bId)
    {
        $emailConfig = EmailConfiguration::where('b_id', $bId)
            ->where('is_active', 1)
            ->first();
    
        if (!$emailConfig) {
            return false;
        }
    
        config([
            'mail.default' => $emailConfig->mailer,
        ]);
    
        switch ($emailConfig->mailer) {
    
            case 'smtp':
                config([
                    'mail.mailers.smtp.transport'  => 'smtp',
                    'mail.mailers.smtp.host'       => $emailConfig->host,
                    'mail.mailers.smtp.port'       => $emailConfig->port,
                    'mail.mailers.smtp.encryption' => $emailConfig->encryption,
                    'mail.mailers.smtp.username'   => $emailConfig->username,
                    'mail.mailers.smtp.password'   => !empty($emailConfig->password)
                        ? self::encrypt_or_decrypt($emailConfig->password, 'decrypt')
                        : null,
                ]);
                break;
    
            case 'sendmail':
                config([
                    'mail.mailers.sendmail.path' => $emailConfig->sendmail_path,
                ]);
                break;
    
            case 'mailgun':
                config([
                    'services.mailgun.domain' => $emailConfig->mailgun_domain,
                    'services.mailgun.secret' => !empty($emailConfig->mailgun_secret)
                        ? self::encrypt_or_decrypt($emailConfig->mailgun_secret, 'decrypt')
                        : null,
                ]);
                break;
    
            case 'ses':
                config([
                    'services.ses.key' => $emailConfig->ses_key,
                    'services.ses.secret' => !empty($emailConfig->ses_secret)
                        ? self::encrypt_or_decrypt($emailConfig->ses_secret, 'decrypt')
                        : null,
    
                    'services.ses.region' => $emailConfig->ses_region,
                ]);
                break;
    
            case 'postmark':
                config([
                    'services.postmark.token' => !empty($emailConfig->postmark_token)
                        ? self::encrypt_or_decrypt($emailConfig->postmark_token, 'decrypt')
                        : null,
                ]);
                break;
        }
    
        config([
            'mail.from.address' => $emailConfig->from_address,
            'mail.from.name'    => $emailConfig->from_name,
        ]);

        app()->forgetInstance('mail.manager');
        app()->forgetInstance('mailer');
    
        return true;
    }

    // public static function sendDynamicMail(
    //     $businessId,
    //     $moduleId,
    //     $mailType,
    //     $toEmail,
    //     array $variables = []
    // ) {
    //     try {
    
    //         // Business mail configuration load
    //         $configured = self::setMailConfiguration($businessId);

    //         if (!$configured) {
    //             \Log::warning(
    //                 "Mail skipped. Email configuration not found for Business ID: {$businessId}"
    //             );
    //             return false;
    //         }
    
    //         // Template fetch
    //         $template = MailTemplate::where('mt_b_id', $businessId)
    //             ->where('mt_module_id', $moduleId)
    //             ->where('mt_mail_type', $mailType)
    //             ->where('mt_is_enabled', 1)
    //             ->first();
    
    //         if (!$template) {
    //             \Log::warning(
    //                 "Mail template not found. Module: {$moduleId}, Type: {$mailType}"
    //             );
    //             return false;
    //         }
    
    //         // Subject
    //         $subject = $template->mt_title;
    
    //         foreach ($variables as $key => $value) {
    //             $subject = str_replace(
    //                 "@{{ {$key} }}",
    //                 $value,
    //                 $subject
    //             );
    //         }
    
    //         // Body
    //         $body = $template->mt_body;
    
    //         foreach ($variables as $key => $value) {
    //             $body = str_replace(
    //                 "@{{ {$key} }}",
    //                 $value,
    //                 $body
    //             );
    //         }
    
    //         // Send Mail
    //         Mail::html($body, function ($message) use ($toEmail, $subject) {
    //             $message->to($toEmail)
    //                 ->subject($subject);
    //         });
    
    //         return true;
    
    //     } catch (\Throwable $e) {
    
    //         \Log::error(
    //             'Dynamic Mail Failed : '.$e->getMessage()
    //         );
    
    //         // Application flow break nahi hoga
    //         return false;
    //     }
    // }

    public static function sendDynamicMail(
        $businessId,
        $moduleId,
        $mailType,
        $toEmail,
        array $variables = []
    ) {
        try {
            // Business mail configuration load
            $configured = self::setMailConfiguration($businessId);

            if (!$configured) {
                Log::warning(
                    "Mail skipped. Email configuration not found for Business ID: {$businessId}"
                );
                return false;
            }

            // Template fetch
            $template = MailTemplate::where('mt_b_id', $businessId)
                ->where('mt_module_id', $moduleId)
                ->where('mt_mail_type', $mailType)
                ->where('mt_is_enabled', 1)
                ->first();

            if (!$template) {
                Log::warning(
                    "Mail template not found. Module: {$moduleId}, Type: {$mailType}, Business: {$businessId}"
                );
                return false;
            }

            // ✅ FIX: Subject - Replace variables (Correct syntax)
            $subject = $template->mt_title;
            foreach ($variables as $key => $value) {
                // Replace BOTH formats: {{ variable }} and {{variable}}
                $subject = str_replace(
                    "{{ {$key} }}",  // ✅ Remove @ symbol
                    $value,
                    $subject
                );
                $subject = str_replace(
                    "{{{$key}}}",    // ✅ Without space
                    $value,
                    $subject
                );
            }

            // ✅ FIX: Body - Replace variables (Correct syntax)
            $body = $template->mt_body;
            foreach ($variables as $key => $value) {
                // Replace BOTH formats: {{ variable }} and {{variable}}
                $body = str_replace(
                    "{{ {$key} }}",  // ✅ Remove @ symbol
                    $value,
                    $body
                );
                $body = str_replace(
                    "{{{$key}}}",    // ✅ Without space
                    $value,
                    $body
                );
            }

            // ✅ Debug: Log final content
            Log::info('📧 Final email content:', [
                'to' => $toEmail,
                'subject' => $subject,
                'body_preview' => substr($body, 0, 300) . '...'
            ]);

            // Send Mail
            Mail::html($body, function ($message) use ($toEmail, $subject) {
                $message->to($toEmail)
                    ->subject($subject);
            });

            Log::info('✅ Email sent successfully to: ' . $toEmail);
            return true;

        } catch (\Throwable $e) {
            Log::error(
                '❌ Dynamic Mail Failed: ' . $e->getMessage(),
                [
                    'business_id' => $businessId,
                    'module_id' => $moduleId,
                    'to_email' => $toEmail,
                    'trace' => $e->getTraceAsString()
                ]
            );

            return false;
        }
    }

}
