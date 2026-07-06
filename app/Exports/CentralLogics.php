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
use App\Helpers\ApprovalHelper;

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

        $holiday_record_exits = PolicyHolidayList::where('phl_b_id', $user->emp_b_id)->where(function ($q) use ($startDate, $endDate) {
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

        $missedPunches = AttendanceException::where('ae_emp_id', $employee->emp_id)
            ->whereBetween('ae_date', [$startDate, $endDate])
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
        $isAbsentOrLeave = function($date) use ($attendanceRecords, $leaveMap, $holidays, $weekOfDates, $employee) {
            if ($employee->emp_date_of_joining > $date) {
                return false;
            }

            $attendance_record_exit = $attendanceRecords[$date] ?? null;
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
            $attendance = $attendanceRecords[$date] ?? null;
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
                if ($attendance->atd_attendance_status == 251) { // Present
                    $presentCount++;
                } elseif ($attendance->atd_attendance_status == 252) { // Half Day
                    $presentCount += 0.5;
                } elseif ($attendance->atd_attendance_status == 215) { // UPL
                    $uplCount++;
                }
                
                if ($attendance->atd_is_late) {
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

        \Log::info('Test: ' . $daysInMonth);

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
        // dd('inside this 2',$startDate, $endDate,$occurrences);
        while ($startDate->lte($endDate)) {
            \Log::info('inside this 1' . $startDate . ' || ' . $endDate);
            $dayName = $startDate->format('l'); // Get the full day name (e.g., Sunday, Monday)
            $occurrences[$dayName][] = $startDate->toDateString();
            $startDate->addDay(); // Properly increment Carbon date
            \Log::info('inside this 2' . $startDate . ' || ' . $endDate);
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

    public static function sendCustomEmail($templateType, array $placeholders, $recipientEmail, $businessId, $attachment = null)
    {
        return true; //this is temporary because mail is not working currently remove this line when resolved to execute the below code

        // Retrieve the mail template based on type and business ID
        $template = MailTemplate::where('mt_mail_type', $templateType)->where('mt_b_id', $businessId)->first();
        if (!$template) {
            $template = MailTemplate::where('mt_mail_type', $templateType)->first();
        }

        // If no template found, return false
        if (!$template) {
            return false;
        }

        // Replace placeholders in the email body and subject
        $mailBody = str_replace(array_keys($placeholders), array_values($placeholders), $template->mt_body);
        $mailSubject = str_replace(array_keys($placeholders), array_values($placeholders), $template->mt_title);

        // Send the email
        Mail::send([], [], function ($message) use ($recipientEmail, $mailSubject, $mailBody, $attachment) {
            $message->to($recipientEmail)
                ->subject($mailSubject)
                ->html($mailBody);
            if ($attachment) {
                // If it's a file path (e.g., from storage), attach the file
                $message->attach($attachment['path'], [
                    'as' => $attachment['name'], // Optional: specify a custom file name for the attachment
                    'mime' => $attachment['mime'], // Optional: specify MIME type, e.g., 'application/pdf'
                ]);
            }
        });

        return true;
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

    /*public static function newGetMonthlyAttendanceDetails($employee, $month, $year, $holiday_record_exits, $weekOfDates)
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $dateRange = $startDate->toPeriod($endDate);

        $absentStatus = MasterTable::where('m_id', 203)->first();


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
                    // $leaveByDate->put($start->toDateString(), $leave);
                    // $start->addDay();

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
            if ($employee->emp_date_of_joining <= $date) {


                $attendance_record_exit = $attendanceRecords[$date] ?? null;
                $attendance_log = $attendanceLogs[$date] ?? null;
                $missedPunch = $missedPunches[$date] ?? null;
                $holiday_record_exit = $holiday_record_exits[$date] ?? null;

                // $leave_record_exit = $leave_record_exits[$date] ?? null;
                $leave_record_exit = $leave_record_exits->get($date) ?? null;


                $is_found = null;

                if ($attendance_record_exit && $attendance_record_exit->fh_attendance_status) {

                    if ($attendance_record_exit->atd_attendance_status == 252) { //Half Day
                        $rowData['halfDayCount'] = 1;
                        $rowData['presentCount']  = 0.5;
                        $rowData['status_id'] = $attendance_record_exit->fh_attendance_status->m_id;
                        $rowData['status_code'] = $attendance_record_exit->fh_attendance_status->m_type;
                        $is_found = 1;
                    } else {
                        if (!$missedPunch) {
                            $rowData['status_id'] = $attendance_record_exit->fh_attendance_status->m_id;
                            $rowData['status_code'] = $attendance_record_exit->fh_attendance_status->m_type;
                            if ($attendance_record_exit->atd_attendance_status == 251) {
                                $rowData['presentCount'] = 1; //Present
                            } else if ($attendance_record_exit->atd_attendance_status == 320) {
                                $rowData["weekOffPresentCount"] = 1;
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
                    if ($attendance_log) {
                        $rowData['previousCheckInTime'] = $attendance_log->al_check_in_time ? Carbon::parse($attendance_log->al_check_in_time)->format('H:i') : '-';
                        $rowData['previousCheckOutTime'] = $attendance_log->al_check_out_time ? Carbon::parse($attendance_log->al_check_out_time)->format('H:i') :  '-';
                        $rowData['previousLate'] = ($attendance_log->al_is_late && $attendance_log->al_late_duration) ? number_format($attendance_log->al_late_duration, 2) : null;
                        $rowData['previousExit'] = ($attendance_log->al_is_early_exit && $attendance_log->al_early_exit_duration) ? number_format($attendance_log->al_early_exit_duration, 2) : null;
                    }
                }

                $half_day_present = ($attendance_record_exit && $attendance_record_exit->atd_attendance_status == 252); // 252 == Half Day

                if (is_null($is_found) || $half_day_present) {
                    if ($leave_record_exit && $leave_record_exit->count() == 1) {
                        $lvr_exit = $leave_record_exit->first();

                        $is_approved = $lvr_exit->lvr_status != 170 && $lvr_exit->lvr_stage_completed;
                        $is_unpaid = $lvr_exit->fh_leave_cat_type->m_id == 215;
                        $is_half_day_leave = $lvr_exit->fh_leave_day_type->m_id != 201;
                        $segment_id = $lvr_exit->fh_leave_day_segment->m_id ?? null;

                        $leave_category_ids = MasterTable::where('m_group', 'LEAVE_CATEGORY')->pluck('m_id')->toArray();

                        // Default leave count
                        $rowData['leaveCount'] = $is_half_day_leave ? 0.5 : 1;
                        $rowData['approvedLeaveCount'] = ($is_approved && !$is_unpaid) ? $rowData['leaveCount'] : 0;
                        $rowData['isApproved'] = $is_approved;

                        if (in_array($lvr_exit->fh_leave_cat_type->m_id, $leave_category_ids) && $is_approved) {
                            $is_found = 1;

                            // ✅ Case: Half-day present + Half-day leave
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

                                // Counts
                                $rowData['presentCount'] = $is_unpaid ? 0.5 : 1;
                                $rowData['leaveCount'] = 0.5;
                                $rowData['approvedLeaveCount'] = $is_unpaid ? 0 : 0.5;
                            }

                            // ✅ Case: SL/UPL or CL/UPL (0.5 count only)
                            elseif ($lvr_exit->lvr_total_leave_days == 0.5 && Carbon::parse($date)->format('Y-m-d') < now()->format('Y-m-d')) {
                                $upLeave = MasterTable::where('m_id', 215)->first();

                                $rowData['status_id'] = $lvr_exit->fh_leave_cat_type->m_id . '/' . $upLeave?->m_id;
                                $rowData['status'] = $lvr_exit->fh_leave_cat_type->m_name . '/' . $upLeave?->m_name;
                                $rowData['status_code'] = $lvr_exit->fh_leave_cat_type->m_type . '/' . $upLeave?->m_type;
                                $rowData['statusColor'] = json_decode($lvr_exit->fh_leave_cat_type->m_other, true)['color'] . '/' . json_decode($upLeave->m_other, true)['color'];

                                $rowData['presentCount'] = 0.5;
                                $rowData['leaveCount'] = 1;
                                $rowData['approvedLeaveCount'] = !$is_unpaid ? 0.5 : 0;
                            }

                            // ✅ Case: Only single full/half leave (no half-day present)
                            elseif (!$half_day_present) {
                                $rowData['status_id'] = $lvr_exit->fh_leave_cat_type->m_id;
                                $rowData['status'] = $lvr_exit->fh_leave_cat_type->m_name;
                                $rowData['status_code'] = $lvr_exit->fh_leave_cat_type->m_type;
                                $rowData['statusColor'] = json_decode($lvr_exit->fh_leave_cat_type->m_other, true)['color'];

                                $rowData['presentCount'] = $is_unpaid ? 0 : $rowData['leaveCount'];
                            }
                        }
                    }

                    // ✅ Case: Two half-day leaves like CL/SL or CL/UPL etc.
                    elseif ($leave_record_exit && $leave_record_exit->count() == 2) {
                        $leaves = $leave_record_exit->sortBy(function ($l) {
                            return optional($l->fh_leave_day_segment)->m_id ?? 0; // fallback to 0 if null
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
                            $rowData['leaveCount'] = 1;
                            $rowData['approvedLeaveCount'] = 0;
                            $rowData['presentCount'] = 0;

                            if ($is_approved1 && $l1->fh_leave_cat_type->m_id != 215) {
                                $rowData['presentCount'] += 0.5;
                                $rowData['approvedLeaveCount'] += 0.5;
                            }

                            if ($is_approved2 && $l2->fh_leave_cat_type->m_id != 215) {
                                $rowData['presentCount'] += 0.5;
                                $rowData['approvedLeaveCount'] += 0.5;
                            }

                            $rowData['isApproved'] = $is_approved1 && $is_approved2;
                        }
                    }
                }

                $attendance_exist = $attendance_record_exit && ($attendance_record_exit->atd_check_in_time || $attendance_record_exit->atd_check_out_time);

                if (is_null($is_found) || $attendance_exist) {
                    if ($missedPunch) {
                        $missedPunchStatus = MasterTable::where('m_id', 228)->first(); //Missedpunch
                        $is_found = 1;
                        $rowData['missedPunchCount'] =  1;
                        $rowData['checkInTime'] = Carbon::createFromFormat('H:i:s', $missedPunch->ae_in_time)->format('h:i A');
                        $rowData['checkOutTime'] =  Carbon::createFromFormat('H:i:s', $missedPunch->ae_out_time)->format('h:i A');
                        $rowData['workingHour'] = number_format($missedPunch->ae_total_working, 2);
                        if ($missedPunch->ae_status != 170 && $missedPunch->ae_stage_completed) {
                            $shiftStartTime = date('Y-m-d', strtotime($date)) . ' ' . $employee->fh_shift_type->pst_start_time ?? '09:00:00';;
                            $shiftEndTime = date('Y-m-d', strtotime($date)) . ' ' . $employee->fh_shift_type->pst_end_time ?? '18:00:00';

                            $pst_start_time = Carbon::parse($shiftStartTime);
                            $pst_end_time = Carbon::parse($shiftEndTime);
                            $dailyWorkingHours = $pst_start_time->diffInMinutes($pst_end_time);

                            $checkInTime = Carbon::parse($date . ' ' . $missedPunch->ae_in_time);
                            $checkOutTime =  Carbon::parse($date . ' ' . $missedPunch->ae_out_time);
                            $workedDuration = $checkInTime->diffInMinutes($checkOutTime);
                            $fullDayThreshold = $dailyWorkingHours;
                            $halfDayThreshold = $dailyWorkingHours / 2;
                            if ($workedDuration >= $fullDayThreshold) {
                                $rowData['presentCount']  = 1; //if missed punch  approved count as present
                                $presentData = MasterTable::where('m_id', 251)->select('m_id', 'm_name', 'm_type', 'm_other')->first();
                                $rowData['status_id'] = $presentData->m_id;
                                $rowData['status'] = $presentData->m_name;
                                $rowData['status_code'] = $presentData->m_type;
                                $rowData['statusColor'] = json_decode($presentData->m_other, true)['color'];
                                $rowData['approvedMissedPunchCount'] = 0.5;
                            } else if ($workedDuration >= $halfDayThreshold) { //half day present & half day leave case
                                $halfDayPresentData = MasterTable::where('m_id', 252)->select('m_id', 'm_name', 'm_type', 'm_other')->first();
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
                $rowData['date'] = $date;
                $attendanceData[] = $rowData;
            }
        }
        return $attendanceData;
    }*/

    public static function newGetMonthlyAttendanceDetails2($employee, $month, $year, $holiday_record_exits, $weekOfDates)
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $dateRange = $startDate->toPeriod($endDate);
    
        $absentStatus = MasterTable::where('m_id', 203)->first();
    
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
    
            // Helper function to check if a date has ABS/SL/CL/UPL
            $isAbsentOrLeave = function($date) use ($attendanceRecords, $leave_record_exits, $holiday_record_exits, $weekOfDates, $employee) {
                if ($employee->emp_date_of_joining > $date) {
                    return false;
                }
    
                $attendance_record_exit = $attendanceRecords[$date] ?? null;
                $leave_record_exit = $leave_record_exits->get($date) ?? null;
                $holiday_record_exit = $holiday_record_exits[$date] ?? null;
    
                // Check for UPL (atd_attendance_status == 215)
                if ($attendance_record_exit && $attendance_record_exit->atd_attendance_status == 215) {
                    return 'UPL';
                }
    
                // Check for Absent
                if (!$attendance_record_exit && !$leave_record_exit && !$holiday_record_exit && !in_array($date, $weekOfDates) && Carbon::parse($date)->isPast()) {
                    return 'ABS';
                }
    
                // Check for approved leaves (SL/CL)
                if ($leave_record_exit && $leave_record_exit->count() >= 1) {
                    $lvr_exit = $leave_record_exit->first();
                    $is_approved = $lvr_exit->lvr_status != 170 && $lvr_exit->lvr_stage_completed;
                    if ($is_approved) {
                        return $lvr_exit->fh_leave_cat_type->m_type; // SL/CL/etc
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
                        // Determine which status to apply (priority: UPL > ABS > Leaves)
                        $statusToApply = 'ABS'; // default
                        
                        if ($prevStatus === 'UPL' || $nextStatus === 'UPL') {
                            $statusToApply = 'UPL';
                        } elseif ($prevStatus === 'ABS' || $nextStatus === 'ABS') {
                            $statusToApply = 'ABS';
                        } elseif (in_array($prevStatus, ['SL', 'CL']) || in_array($nextStatus, ['SL', 'CL'])) {
                            // Use the leave type from adjacent day
                            $statusToApply = in_array($prevStatus, ['SL', 'CL']) ? $prevStatus : $nextStatus;
                        }
    
                        $sandwichRuleApplied[$date] = $statusToApply;
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
            
            // Skip dates before joining or after last working day
            $shouldCountDate = true;
            if ($employee->emp_date_of_joining && $dateObj->lt(Carbon::parse($employee->emp_date_of_joining))) {
                $shouldCountDate = false;
            }
            if ($employee->emp_last_working_day && $dateObj->gt(Carbon::parse($employee->emp_last_working_day))) {
                $shouldCountDate = false;
            }

            if ($shouldCountDate && $employee->emp_date_of_joining <= $date) {
            // if ($employee->emp_date_of_joining <= $date) {
    
                // Check if sandwich rule applies to this date
                if (isset($sandwichRuleApplied[$date])) {
                    $sandwichStatus = $sandwichRuleApplied[$date];
                    $is_found = 1;
    
                    if ($sandwichStatus === 'UPL') {
                        $uplStatus = MasterTable::where('m_id', 215)->first(); // UPL status
                        $rowData['status'] = $uplStatus->m_name;
                        $rowData['status_id'] = $uplStatus->m_id;
                        $rowData['status_code'] = $uplStatus->m_type;
                        $rowData['statusColor'] = json_decode($uplStatus->m_other, true)['color'];
                        $rowData['UPL'] = 1;
                        $rowData['absentCount'] = 1; // Count sandwich UPL as absent
                    } elseif ($sandwichStatus === 'ABS') {
                        $rowData['absentCount'] = 1;
                        $rowData['status'] = $absentStatus->m_name;
                        $rowData['status_id'] = $absentStatus->m_id;
                        $rowData['status_code'] = $absentStatus->m_type;
                        $rowData['statusColor'] = json_decode($absentStatus->m_other, true)['color'];
                    } else {
                        // Apply leave status (SL/CL)
                        $leaveStatus = MasterTable::where('m_type', $sandwichStatus)->first();
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
                    // Original logic continues here...
                    $attendance_record_exit = $attendanceRecords[$date] ?? null;
                    $attendance_log = $attendanceLogs[$date] ?? null;
                    $missedPunch = $missedPunches[$date] ?? null;
                    $holiday_record_exit = $holiday_record_exits[$date] ?? null;
                    $leave_record_exit = $leave_record_exits->get($date) ?? null;
    
                    $is_found = null;
    
                    // Check for UPL status first (atd_attendance_status == 215)
                    if ($attendance_record_exit && $attendance_record_exit->atd_attendance_status == 215) {
                        $uplStatus = MasterTable::where('m_id', 215)->first(); // UPL status
                        $rowData['status'] = $uplStatus->m_name;
                        $rowData['status_id'] = $uplStatus->m_id;
                        $rowData['status_code'] = $uplStatus->m_type;
                        $rowData['statusColor'] = json_decode($uplStatus->m_other, true)['color'];
                        $rowData['UPL'] = 1;
                        $rowData['absentCount'] = 1; // Count UPL as absent
                        $is_found = 1;
                    }
    
                    // if ($attendance_record_exit && $attendance_record_exit->fh_attendance_status && !$is_found) {
                    if ($attendance_record_exit && $attendance_record_exit->fh_attendance_status && !isset($sandwichRuleApplied[$date])) {
    
                        if ($attendance_record_exit->atd_attendance_status == 252) { //Half Day
                            $rowData['halfDayCount'] = 1;
                            // $rowData['presentCount']  = 0.5;
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
                                } else if ($attendance_record_exit->atd_attendance_status == 320) {
                                    $rowData["weekOffPresentCount"] = 1;
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
                        if ($attendance_log) {
                            $rowData['previousCheckInTime'] = $attendance_log->al_check_in_time ? Carbon::parse($attendance_log->al_check_in_time)->format('H:i') : '-';
                            $rowData['previousCheckOutTime'] = $attendance_log->al_check_out_time ? Carbon::parse($attendance_log->al_check_out_time)->format('H:i') :  '-';
                            $rowData['previousLate'] = ($attendance_log->al_is_late && $attendance_log->al_late_duration) ? number_format($attendance_log->al_late_duration, 2) : null;
                            $rowData['previousExit'] = ($attendance_log->al_is_early_exit && $attendance_log->al_early_exit_duration) ? number_format($attendance_log->al_early_exit_duration, 2) : null;
                        }
                    }
    
                    $half_day_present = ($attendance_record_exit && $attendance_record_exit->atd_attendance_status == 252); // 252 == Half Day
    
                    if (is_null($is_found) || $half_day_present) {
                        if ($leave_record_exit && $leave_record_exit->count() == 1) {
                            $lvr_exit = $leave_record_exit->first();
    
                            $is_approved = $lvr_exit->lvr_status != 170 && $lvr_exit->lvr_stage_completed;
                            $is_unpaid = $lvr_exit->fh_leave_cat_type->m_id == 215;
                            // $is_half_day_leave = $lvr_exit->fh_leave_day_type->m_id != 201;
                            $is_half_day_leave = optional($lvr_exit->fh_leave_day_type)->m_id != 201;
                            $segment_id = $lvr_exit->fh_leave_day_segment->m_id ?? null;
    
                            $leave_category_ids = MasterTable::where('m_group', 'LEAVE_CATEGORY')->pluck('m_id')->toArray();
    
                            // Default leave count
                            $rowData['leaveCount'] = $is_half_day_leave ? 0.5 : 1;
                            $rowData['approvedLeaveCount'] = ($is_approved && !$is_unpaid) ? $rowData['leaveCount'] : 0;
                            $rowData['isApproved'] = $is_approved;
    
                            if (in_array($lvr_exit->fh_leave_cat_type->m_id, $leave_category_ids) && $is_approved) {
                                $is_found = 1;
    
                                // ✅ Case: Half-day present + Half-day leave
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
    
                                    // Counts
                                    // $rowData['presentCount'] = $is_unpaid ? 0.5 : 1;
                                    $rowData['leaveCount'] = 0.5;
                                    $rowData['approvedLeaveCount'] = $is_unpaid ? 0 : 0.5;
                                }
    
                                // ✅ Case: SL/UPL or CL/UPL (0.5 count only)
                                elseif ($lvr_exit->lvr_total_leave_days == 0.5 && Carbon::parse($date)->format('Y-m-d') < now()->format('Y-m-d')) {
                                    $upLeave = MasterTable::where('m_id', 215)->first();
    
                                    $rowData['status_id'] = $lvr_exit->fh_leave_cat_type->m_id . '/' . $upLeave?->m_id;
                                    $rowData['status'] = $lvr_exit->fh_leave_cat_type->m_name . '/' . $upLeave?->m_name;
                                    $rowData['status_code'] = $lvr_exit->fh_leave_cat_type->m_type . '/' . $upLeave?->m_type;
                                    $rowData['statusColor'] = json_decode($lvr_exit->fh_leave_cat_type->m_other, true)['color'] . '/' . json_decode($upLeave->m_other, true)['color'];
    
                                    // $rowData['presentCount'] = 0.5;
                                    $rowData['leaveCount'] = 1;
                                    $rowData['approvedLeaveCount'] = !$is_unpaid ? 0.5 : 0;
                                }
    
                                // ✅ Case: Only single full/half leave (no half-day present)
                                elseif (!$half_day_present) {
                                    $rowData['status_id'] = $lvr_exit->fh_leave_cat_type->m_id;
                                    $rowData['status'] = $lvr_exit->fh_leave_cat_type->m_name;
                                    $rowData['status_code'] = $lvr_exit->fh_leave_cat_type->m_type;
                                    $rowData['statusColor'] = json_decode($lvr_exit->fh_leave_cat_type->m_other, true)['color'];
    
                                    // $rowData['presentCount'] = $is_unpaid ? 0 : $rowData['leaveCount'];
                                }
                            }
                        }
    
                        // ✅ Case: Two half-day leaves like CL/SL or CL/UPL etc.
                        elseif ($leave_record_exit && $leave_record_exit->count() == 2) {
                            $leaves = $leave_record_exit->sortBy(function ($l) {
                                return optional($l->fh_leave_day_segment)->m_id ?? 0; // fallback to 0 if null
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
                                $rowData['leaveCount'] = 1;
                                $rowData['approvedLeaveCount'] = 0;
                                $rowData['presentCount'] = 0;
    
                                if ($is_approved1 && $l1->fh_leave_cat_type->m_id != 215) {
                                    $rowData['presentCount'] += 0.5;
                                    $rowData['approvedLeaveCount'] += 0.5;
                                }
    
                                if ($is_approved2 && $l2->fh_leave_cat_type->m_id != 215) {
                                    $rowData['presentCount'] += 0.5;
                                    $rowData['approvedLeaveCount'] += 0.5;
                                }
    
                                $rowData['isApproved'] = $is_approved1 && $is_approved2;
                            }
                        }
                    }
    
                    $attendance_exist = $attendance_record_exit && ($attendance_record_exit->atd_check_in_time || $attendance_record_exit->atd_check_out_time);
    
                    if (is_null($is_found) || $attendance_exist) {
                        if ($missedPunch) {
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
                                $shiftStartTime = date('Y-m-d', strtotime($date)) . ' ' . $employee->fh_shift_type->pst_start_time ?? '09:00:00';
                                $shiftEndTime = date('Y-m-d', strtotime($date)) . ' ' . $employee->fh_shift_type->pst_end_time ?? '18:00:00';
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
                                    $presentData = MasterTable::where('m_id', 251)->select('m_id', 'm_name', 'm_type', 'm_other')->first();
                                    $rowData['status_id'] = $presentData->m_id;
                                    $rowData['status'] = $presentData->m_name;
                                    $rowData['status_code'] = $presentData->m_type;
                                    $rowData['statusColor'] = json_decode($presentData->m_other, true)['color'];
                                    $rowData['approvedMissedPunchCount'] = 1;
                                } else if ($workedDuration >= $halfDayThreshold) { //half day present & half day leave case
                                    $halfDayPresentData = MasterTable::where('m_id', 252)->select('m_id', 'm_name', 'm_type', 'm_other')->first();
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
                            } else {
                                // Show old attendance data if available, otherwise show missed punch data
                                if ($attendance_record_exit) {
                                    $rowData['checkInTime'] = $attendance_record_exit->atd_check_in_time ? Carbon::parse($attendance_record_exit->atd_check_in_time)->format('H:i') : '-';
                                    $rowData['checkOutTime'] = $attendance_record_exit->atd_check_out_time ? Carbon::parse($attendance_record_exit->atd_check_out_time)->format('H:i') :  '-';
                                    $rowData['workingHour'] = $attendance_record_exit->atd_total_worked_hours ? number_format($attendance_record_exit->atd_total_worked_hours, 2) : '-';
                                } else {
                                    $rowData['checkInTime'] = Carbon::createFromFormat('H:i:s', $missedPunch->ae_in_time)->format('h:i A');
                                    $rowData['checkOutTime'] =  Carbon::createFromFormat('H:i:s', $missedPunch->ae_out_time)->format('h:i A');
                                    $rowData['workingHour'] = number_format($missedPunch->ae_total_working, 2);
                                }
                                
                                $rowData['status_id'] = $missedPunchStatus->m_id;
                                $rowData['status'] =   $missedPunchStatus->m_name;
                                $rowData['status_code'] = $missedPunchStatus->m_type;
                                $rowData['statusColor'] = json_decode($missedPunchStatus->m_other, true)['color'];
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

    //with sandwich
    public static function newGetMonthlyAttendanceDetails18sep($employee, $month, $year, $holiday_record_exits, $weekOfDates)
    {
        $salaryDay = 0;
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
            ->where('ae_stage_completed', 1)
            ->where('ae_status', '!=', 170)
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

        // Helper function to check if a date has ABS/SL/CL/UPL
        $isAbsentOrLeave = function($date) use ($attendanceRecords, $leave_record_exits, $holiday_record_exits, $weekOfDates, $employee, $absentStatus) {
            if ($employee->emp_date_of_joining > $date) {
                return false;
            }

            $attendance_record_exit = $attendanceRecords[$date] ?? null;
            $leave_record_exit = $leave_record_exits->get($date) ?? null;
            $holiday_record_exit = $holiday_record_exits[$date] ?? null;

            // Check for UPL status first
            if ($attendance_record_exit && $attendance_record_exit->atd_attendance_status == 215) {
                return 215;
            }

            // Check for approved leaves (SL/CL/UPL)
            if ($leave_record_exit && $leave_record_exit->count() >= 1) {
                $lvr_exit = $leave_record_exit->first();
                $is_approved = $lvr_exit->lvr_status != 170 && $lvr_exit->lvr_stage_completed;
                if ($is_approved) {
                    // Return the actual status ID based on leave category
                    if ($lvr_exit->fh_leave_cat_type->m_type == 'CL') return 207;
                    if ($lvr_exit->fh_leave_cat_type->m_type == 'SL') return 208;  
                    if ($lvr_exit->fh_leave_cat_type->m_type == 'UPL') return 215;
                    return (int)$lvr_exit->fh_leave_cat_type->m_id; // Fallback to m_id
                }
            }

            // Check for Absent (not WO, not HO, not present, and date is past)
            if (!$attendance_record_exit && !$leave_record_exit && !$holiday_record_exit && 
                !in_array($date, $weekOfDates) && Carbon::parse($date)->isPast()) {
                return 203; // ABS
            }

            return false;
        };

        foreach ($allDates as $index => $date) {
            $normalizedDate = Carbon::parse($date)->toDateString(); 
            
            $isInWeekOfDates = in_array($normalizedDate, $weekOfDates);
            $isInHolidayRecord = array_key_exists($normalizedDate, $holiday_record_exits->toArray());
            $isCurrentWOOrHO = $isInWeekOfDates || $isInHolidayRecord;
            
            // Only proceed if the date is WO/HO and not already processed
            if ($isCurrentWOOrHO && !isset($sandwichRuleApplied[$date])) {
                $prevDate = $index > 0 ? $allDates[$index - 1] : null;
                $nextDate = $index < count($allDates) - 1 ? $allDates[$index + 1] : null;
                
                // Check if both adjacent days are leaves/absents AND they are consecutive
                $prevStatus = $prevDate ? $isAbsentOrLeave($prevDate) : false;
                $nextStatus = $nextDate ? $isAbsentOrLeave($nextDate) : false;
                
                // Only apply sandwich rule if BOTH adjacent days are leaves/absents
                // AND they are directly adjacent (no gaps)
                if ($prevStatus && $nextStatus) {
                    $statusToApply = null;
                    
                    // Case 1: Both days have the same status (CL+CL, SL+SL, ABS+ABS, UPL+UPL)
                    if ($prevStatus === $nextStatus) {
                        if ($prevStatus == 203) { // ABS + ABS = ABS
                            $statusToApply = 203;
                        } elseif ($prevStatus == 215) { // UPL + UPL = UPL  
                            $statusToApply = 215;
                        } elseif (in_array($prevStatus, [207, 208])) { // CL+CL or SL+SL
                            $userLeaveBalance = self::getLeaveBalanceData($employee->emp_id, $prevStatus, $year, $currentMonth);
                            if ($userLeaveBalance && $userLeaveBalance->lb_balance_remaining_leave >= 1) {
                                $statusToApply = $prevStatus;
                            } else {
                                $statusToApply = 215; // No balance = UPL
                            }
                        }
                    } 
                    // Case 2: Mixed scenarios
                    else {
                        // Priority logic: CL > SL > ABS > UPL
                        $leaveTypes = array_filter([$prevStatus, $nextStatus], function($status) {
                            return in_array($status, [207, 208]); // Only CL/SL
                        });
                        
                        if (!empty($leaveTypes)) {
                            // Check CL first (207), then SL (208)
                            if (in_array(207, [$prevStatus, $nextStatus])) {
                                $clBalance = self::getLeaveBalanceData($employee->emp_id, 207, $year, $currentMonth);
                                if ($clBalance && $clBalance->lb_balance_remaining_leave >= 1) {
                                    $statusToApply = 207;
                                }
                            }
                            
                            // If no CL balance, try SL
                            if (!$statusToApply && in_array(208, [$prevStatus, $nextStatus])) {
                                $slBalance = self::getLeaveBalanceData($employee->emp_id, 208, $year, $currentMonth);
                                if ($slBalance && $slBalance->lb_balance_remaining_leave >= 1) {
                                    $statusToApply = 208;
                                }
                            }
                            
                            // If no leave balance available, apply UPL
                            if (!$statusToApply) {
                                $statusToApply = 215;
                            }
                        } else {
                            // No leave types involved, check for ABS priority
                            if (in_array(203, [$prevStatus, $nextStatus])) {
                                $statusToApply = 203;
                            } else {
                                $statusToApply = 215; // Default to UPL
                            }
                        }
                    }
                    
                    if ($statusToApply) {
                        $sandwichRuleApplied[$date] = $statusToApply;
                        
                        // Deduct leave balance only for leave types (CL/SL)
                        if (in_array($statusToApply, [207, 208])) {
                            self::deductLeaveBalance($employee->emp_id, $statusToApply, $year, $currentMonth, 1);
                        }
                    }
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
                'totalSalariedDays' => 0
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

                    if ($sandwichStatus === 215) {
                        $rowData['status'] = $uplStatus->m_name;
                        $rowData['status_id'] = $uplStatus->m_id;
                        $rowData['status_code'] = $uplStatus->m_type;
                        $rowData['statusColor'] = json_decode($uplStatus->m_other, true)['color'];
                        $rowData['UPL'] = 1;
                        // REMOVED: $rowData['absentCount'] = 1; // UPL should not count as absent
                    } elseif ($sandwichStatus === 203) {
                        $rowData['absentCount'] = 1;
                        $rowData['status'] = $absentStatus->m_name;
                        $rowData['status_id'] = $absentStatus->m_id;
                        $rowData['status_code'] = $absentStatus->m_type;
                        $rowData['statusColor'] = json_decode($absentStatus->m_other, true)['color'];
                    } else {
                        // Apply leave status (SL/CL)
                        $leaveStatus = MasterTable::where('m_id', $sandwichStatus)->first();
                        if ($leaveStatus) {
                            $rowData['leaveCount'] = 1;
                            $rowData['status'] = $leaveStatus->m_name;
                            $rowData['status_id'] = $leaveStatus->m_id;
                            $rowData['status_code'] = $leaveStatus->m_type;
                            $rowData['statusColor'] = json_decode($leaveStatus->m_other, true)['color'];
                            $rowData['approvedLeaveCount'] = 1;
                        }
                    }

                    $rowData['checkInTime'] = '-';
                    $rowData['checkOutTime'] = '-';
                    $rowData['workingHour'] = '-';
                } else {
                    // Original logic continues here...
                    $attendance_record_exit = $attendanceRecords[$date] ?? null;
                    $attendance_log = $attendanceLogs[$date] ?? null;
                    $missedPunch = $missedPunches[$date] ?? null;
                    $holiday_record_exit = $holiday_record_exits[$date] ?? null;
                    $leave_record_exit = $leave_record_exits->get($date) ?? null;

                    $is_found = null;

                    // Check for UPL status first (atd_attendance_status == 215)
                    if ($attendance_record_exit && $attendance_record_exit->atd_attendance_status == 215) {
                        $rowData['status'] = $uplStatus->m_name;
                        $rowData['status_id'] = $uplStatus->m_id;
                        $rowData['status_code'] = $uplStatus->m_type;
                        $rowData['statusColor'] = json_decode($uplStatus->m_other, true)['color'];
                        $rowData['UPL'] = 1;
                        // REMOVED: $rowData['absentCount'] = 1; // UPL should not count as absent
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
                                $presentData = MasterTable::where('m_id', 251)->select('m_id', 'm_name', 'm_type', 'm_other')->first();
                                $rowData['status_id'] = $presentData->m_id;
                                $rowData['status'] = $presentData->m_name;
                                $rowData['status_code'] = $presentData->m_type;
                                $rowData['statusColor'] = json_decode($presentData->m_other, true)['color'];
                                $rowData['approvedMissedPunchCount'] = 1;
                            } else if ($workedDuration >= $halfDayThreshold) { //half day present & half day leave case
                                $halfDayPresentData = MasterTable::where('m_id', 252)->select('m_id', 'm_name', 'm_type', 'm_other')->first();
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
                                }  elseif ($statusData->m_id == 319) { // Holiday Present
                                    $rowData["holidayPresentCount"] = 1;
                                }  elseif ($statusData->m_id == 320) { // Week Off Present
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
                                } else if ($attendance_record_exit->atd_attendance_status == 320) {
                                    $rowData["weekOffPresentCount"] = 1;
                                } else if ($attendance_record_exit->atd_attendance_status == 319) {
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

                                // ✅ Case: Half-day present + Half-day leave
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

                                // ✅ Case: Only single full/half leave (no half-day present)
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
                                        // REMOVED: $rowData['absentCount'] increment for UPL
                                    } else {
                                        $rowData['leaveCount'] = $is_half_day_leave ? 0.5 : 1; // SL/CL counts as leave
                                        $rowData['UPL'] = 0;
                                    }
                                    $rowData['approvedLeaveCount'] = $is_unpaid ? 0 : ($is_half_day_leave ? 0.5 : 1);
                                }
                            }
                        }

                        // ✅ Case: Two half-day leaves like CL/SL or CL/UPL etc.
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
                    
                    // Check for UPL leave application before marking as absent
                    if (is_null($is_found) && Carbon::parse($date)->isPast()) {
                        $leave_record_exit = $leave_record_exits->get($date) ?? null;
                        
                        if ($leave_record_exit && $leave_record_exit->count() >= 1) {
                            $lvr_exit = $leave_record_exit->first();
                            $is_unpaid = $lvr_exit->fh_leave_cat_type->m_id == 215; // UPL
                            $is_approved = $lvr_exit->lvr_status != 170 && $lvr_exit->lvr_stage_completed;
                            
                            if ($is_approved && $is_unpaid) {
                                // This is an approved UPL leave
                                $rowData['status'] = $uplStatus->m_name;
                                $rowData['status_id'] = $uplStatus->m_id;
                                $rowData['status_code'] = $uplStatus->m_type;
                                $rowData['statusColor'] = json_decode($uplStatus->m_other, true)['color'];
                                $rowData['UPL'] = 1;
                                $rowData['checkInTime'] = '-';
                                $rowData['checkOutTime'] = '-';
                                $rowData['workingHour'] = '-';
                                $is_found = 1;
                            }
                        }
                        
                        // Only mark as absent if no UPL leave was found
                        if (is_null($is_found)) {
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

        foreach ($attendanceData as $key => $value) {
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

            // Log::info("Date: {$value['date']} | Employee: {$employee->emp_full_name} | Present: {$value['presentCount']}, Half Day: {$value['halfDayCount']}, Approved Leave: {$value['approvedLeaveCount']}, Week Off: {$value['weekOffCount']}, Holiday: {$value['holidayCount']} | Cumulative Salary Days: {$salaryDay}");
        }
        $lastIndex = count($attendanceData) - 1;
        if (isset($attendanceData[$lastIndex])) {
            $attendanceData[$lastIndex]['totalSalariedDays'] = $salaryDay;
        }
        return $attendanceData;
    }

    //without sandwich
    public static function newGetMonthlyAttendanceDetails($employee, $month, $year, $holiday_record_exits, $weekOfDates)
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
        $isAbsent = function($date) use ($attendanceRecords, $leave_record_exits, $holiday_record_exits, $weekOfDates, $employee, $absentStatus) {
            if ($employee->emp_date_of_joining > $date) {
                return false;
            }

            $attendance_record_exit = $attendanceRecords[$date] ?? null;
            $leave_record_exit = $leave_record_exits->get($date) ?? null;
            $holiday_record_exit = $holiday_record_exits[$date] ?? null;

            // Check for Absent (not WO, not HO, not present, and date is past)
            if (!$attendance_record_exit && !$leave_record_exit && !$holiday_record_exit && 
                !in_array($date, $weekOfDates) && Carbon::parse($date)->isPast()) {
                return 203; // ABS
            }

            return false;
        };

        foreach ($allDates as $index => $date) {
            $normalizedDate = Carbon::parse($date)->toDateString(); 
            
            $isInWeekOfDates = in_array($normalizedDate, $weekOfDates);
            $isInHolidayRecord = array_key_exists($normalizedDate, $holiday_record_exits->toArray());
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
                    $holiday_record_exit = $holiday_record_exits[$date] ?? null;
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
                                $presentData = MasterTable::where('m_id', 251)->select('m_id', 'm_name', 'm_type', 'm_other')->first();
                                $rowData['status_id'] = $presentData->m_id;
                                $rowData['status'] = $presentData->m_name;
                                $rowData['status_code'] = $presentData->m_type;
                                $rowData['statusColor'] = json_decode($presentData->m_other, true)['color'];
                                $rowData['approvedMissedPunchCount'] = 1;
                            } else if ($workedDuration >= $halfDayThreshold) { //half day present & half day leave case
                                $halfDayPresentData = MasterTable::where('m_id', 252)->select('m_id', 'm_name', 'm_type', 'm_other')->first();
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
                                }  elseif ($statusData->m_id == 319) { // Holiday Present
                                    $rowData["holidayPresentCount"] = 1;
                                }  elseif ($statusData->m_id == 320) { // Week Off Present
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
                                } else if ($attendance_record_exit->atd_attendance_status == 320) {
                                    $rowData["weekOffPresentCount"] = 1;
                                } else if ($attendance_record_exit->atd_attendance_status == 319) {
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
                                        $rowData['UPL'] = 0;
                                    }
                                    $rowData['approvedLeaveCount'] = $is_unpaid ? 0 : ($is_half_day_leave ? 0.5 : 1);
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

    //Update Attendance Common Function
    public static function processAttendance(Request $request)
    {
        try {
            $user = Auth::user();
            $employeeId = $request->id;
            $employee = Employee::where('emp_id', $employeeId)->first();
            $markAsAbsent = $request->has('mark_as_absent') ? 1 : 0;

            if (!$employee) {
                return ['status' => false, 'message' => "Employee not found."];
            }

            $shift = PolicyShiftTiming::find($employee->emp_shift_type_id);
            if (!$shift) {
                return ['status' => false, 'message' => "Please assign shift to this employee."];
            }

            if (Carbon::parse($request->punch_date) >= Carbon::now()) {
                return ['status' => false, 'message' => "Can't update Future Date's Attendance"];
            }

            $frozen = PayrollPeriod::where('pp_b_id', $employee->emp_b_id)
                ->where('pp_start_date', '<=', $request->punch_date)
                ->where('pp_end_date', '>=', $request->punch_date)
                ->where('pp_is_freezed', 120)
                ->exists();

            if ($frozen) {
                return ['status' => false, 'message' => "Frozen attendance records cannot be updated."];
            }

            $isWeeklyOff = self::getWeekOffDates($employee, null, null, $request->punch_date, $request->punch_date);

            $isHoliday = PolicyHolidayList::where('phl_b_id', $user->emp_b_id)
                ->whereDate('phl_start_date', '<=', $request->punch_date)
                ->whereDate('phl_end_date', '>=', $request->punch_date)
                ->first();

            if ($isHoliday && $isHoliday->phl_type_id == 205) {
                return ['status' => false, 'message' => "You can't update attendance on a public holiday."];
            } elseif (($isHoliday && $isHoliday->phl_type_id == 206) || $isWeeklyOff) {
                $co_request = CompOff::where([
                    ['co_b_id', $user->emp_b_id],
                    ['co_emp_id', $employeeId],
                    ['co_request_date', $request->punch_date]
                ])->whereNotIn('co_status', [140, 171, 170])->first();

                if ($co_request) {
                    return ['status' => false, 'message' => "There is already a comp off request for this date, you will not be able to update."];
                }

                $ruleCriteria = RuleCriterion::with('fh_approval_module')
                    ->where('rc_b_id', $user->emp_b_id)
                    ->where('rc_condition_option_id', 140)
                    ->whereHas('fh_approval_module', function ($query) {
                        $query->where('am_module_id', 562)
                            ->where('am_status', 1);
                    })->first();

                $processApprovers = [];

                if ($ruleCriteria && $ruleCriteria->fh_approval_module) {
                    $processApprovers = $ruleCriteria->fh_approval_module
                        ->filteredProcessApprovers($user->emp_b_id)
                        ->get();
                }

                if (count($processApprovers)) {
                    $comp_off_amId = $ruleCriteria->rc_am_id;
                    $request->merge([
                        'co_am_id' => $comp_off_amId,
                    ]);
                } else {
                    $approvalMapping = ApprovalHelper::getApprovalMapping($user->emp_b_id, $user->emp_id, 562);
                    if (!$approvalMapping) {
                        return ['status' => false, 'message' => "Sorry! not found any approval settings for comp off module, contact administration."];
                    }
                }
            }

            $attendance = AttendanceRecord::where('atd_emp_id', $employeeId)
                ->whereDate('atd_date', $request->punch_date)
                ->first();

            $checkInTime = (!empty($request->in_time)) ? Carbon::parse($request->punch_date . ' ' . $request->in_time)->format('Y-m-d H:i:s') : null;
            $checkOutTime = (!empty($request->out_time)) ? Carbon::parse($request->punch_date . ' ' . $request->out_time)->format('Y-m-d H:i:s') : null;

            $shiftStartTime = $request->punch_date . ' ' . Carbon::parse($shift->pst_start_time)->format('H:i:s');
            $shiftEndTime = $request->punch_date . ' ' . Carbon::parse($shift->pst_end_time)->format('H:i:s');

            $dayName = Carbon::parse($request->punch_date)->format('l');

            if ($shift->pst_allow_partial_day == 1 && $shift->fh_master_table_week->m_name == $dayName) {
                $shiftStartTime = $request->punch_date . ' ' . Carbon::parse($shift->pst_partial_day_begin_time)->format('H:i:s');
                $shiftEndTime = $request->punch_date . ' ' . Carbon::parse($shift->pst_partial_day_end_time)->format('H:i:s');
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

                $totalWorkedMinutes = $checkIn->diffInMinutes($checkOut);
                $totalWorkedHours = number_format($totalWorkedMinutes / 60, 2);

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
                    $earlyExitDuration = number_format($shiftEnd->diffInMinutes($checkOut), 2);
                }

                if ($checkOut->gt($shiftEnd)) {
                    $isOvertime = 1;
                    $overtimeMinutes = $checkOut->diffInMinutes($shiftEnd);
                    $overtimeHours = number_format(abs($overtimeMinutes) / 60, 2);
                }

                $isHoliday = PolicyHolidayList::where('phl_b_id', $employee->emp_b_id)
                    ->whereDate('phl_start_date', '<=', $request->punch_date)
                    ->whereDate('phl_end_date', '>=', $request->punch_date)
                    ->exists();

                if ($isWeeklyOff || $isHoliday) {
                    $attendanceStatus = $isWeeklyOff ? 320 : 319;
                } else {
                    $minWorkHour = $shift->pst_min_work_hour
                        ? Carbon::parse($shift->pst_start_time)->diffInMinutes(
                            Carbon::parse($shift->pst_min_work_hour)
                        )
                        : 0;

                    $halfMinWorkHour = $minWorkHour / 2;

                    if ($totalWorkedMinutes >= $minWorkHour) {
                        $attendanceStatus = 251;
                    } elseif ($totalWorkedMinutes >= $halfMinWorkHour) {
                        $attendanceStatus = 252;
                    } else {
                        $attendanceStatus = 203;
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
                    'al_check_in_time' => $checkInTime,
                    'al_check_out_time' => $checkOutTime,
                    'al_date' => $request->punch_date,
                    'al_is_late' => $isLate,
                    'al_late_duration' => $lateDuration,
                    'al_is_early_exit' => $isEarlyExit,
                    'al_early_exit_duration' => $earlyExitDuration,
                    'al_is_overtime' => $isOvertime,
                    'al_overtime_hours' => $overtimeHours,
                    'al_total_worked_hours' => $totalWorkedHours,
                    'al_attendance_status' => $attendanceStatus,
                    'al_reason' => $request->reason,
                    'al_updated_by' => $user->emp_id,
                ]);

                if ($attendanceStatus == 320 || $attendanceStatus == 319) {
                    CentralLogics::generateCompOffRequest($employee, $request->punch_date);
                }
            } else {
                $attendanceStatus = 203;
                $attendanceLog = AttendanceLog::create([
                    'al_b_id' => $user->emp_b_id,
                    'al_emp_id' => $employeeId,
                    'al_atd_id' => $attendance->atd_id ?? null,
                    'al_check_in_time' => null,
                    'al_check_out_time' => null,
                    'al_date' => $request->punch_date,
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
                $attendanceLog->save();
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

    public static function newGetMonthlyAttendanceDetailsData($employee, $month, $year, $holiday_record_exits, $weekOfDates)
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        // Check if employee has left and if the month is after their last working date
        if ($employee->emp_last_working_date) {
            $lastWorkingDate = Carbon::parse($employee->emp_last_working_date);
            
            // If the requested month starts after the last working date, return empty array
            if ($startDate->isAfter($lastWorkingDate)) {
                return [];
            }
        }
    
        // Check if employee joined after the requested month
        if ($employee->emp_date_of_joining) {
            $joiningDate = Carbon::parse($employee->emp_date_of_joining);
            
            // If the requested month ends before the joining date, return empty array
            if ($endDate->isBefore($joiningDate)) {
                return [];
            }
        }
    
        $dateRange = $startDate->toPeriod($endDate);
    
        $absentStatus = MasterTable::where('m_id', 203)->first();
        $uplStatus = MasterTable::where('m_id', 215)->first();
        $missedPunchStatus = MasterTable::where('m_id', 228)->first();
        $holidayStatus = MasterTable::where('m_id', 321)->first();
        $weekOffStatus = MasterTable::where('m_id', 322)->first();
        $presentStatus = MasterTable::where('m_id', 251)->first();
        $halfDayStatus = MasterTable::where('m_id', 252)->first();
    
        // Preload everything to avoid repeated DB hits - EXACTLY like original
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
        $sandwichRuleApplied = [];
    
        // First pass: Collect all dates and identify sandwich situations - EXACTLY like original
        $allDates = [];
        foreach ($dateRange as $dateObj) {
            $allDates[] = $dateObj->toDateString();
        }
    
        // Helper function to check if a date has ABS/SL/CL/UPL - EXACTLY like original
        $isAbsentOrLeave = function($date) use ($attendanceRecords, $leave_record_exits, $holiday_record_exits, $weekOfDates, $employee) {
            // Check if date is before joining date or after last working date
            if ($employee->emp_date_of_joining && Carbon::parse($date)->lt(Carbon::parse($employee->emp_date_of_joining))) {
                return false;
            }
            
            if ($employee->emp_last_working_date && Carbon::parse($date)->gt(Carbon::parse($employee->emp_last_working_date))) {
                return false;
            }
    
            $attendance_record_exit = $attendanceRecords[$date] ?? null;
            $leave_record_exit = $leave_record_exits->get($date) ?? null;
            $holiday_record_exit = $holiday_record_exits[$date] ?? null;
    
            // Check for UPL (atd_attendance_status == 215)
            if ($attendance_record_exit && $attendance_record_exit->atd_attendance_status == 215) {
                return 'UPL';
            }
    
            // Check for Absent
            if (!$attendance_record_exit && !$leave_record_exit && !$holiday_record_exit && !in_array($date, $weekOfDates) && Carbon::parse($date)->isPast()) {
                return 'ABS';
            }
    
            // Check for approved leaves (SL/CL)
            if ($leave_record_exit && $leave_record_exit->count() >= 1) {
                $lvr_exit = $leave_record_exit->first();
                $is_approved = $lvr_exit->lvr_status != 170 && $lvr_exit->lvr_stage_completed;
                if ($is_approved) {
                    return $lvr_exit->fh_leave_cat_type->m_type;
                }
            }
    
            return false;
        };
    
        // Apply sandwich rule logic - EXACTLY like original
        foreach ($allDates as $index => $date) {
            $isCurrentWOOrHO = in_array($date, $weekOfDates) || isset($holiday_record_exits[$date]);
            
            if ($isCurrentWOOrHO) {
                $prevDate = $index > 0 ? $allDates[$index - 1] : null;
                $nextDate = $index < count($allDates) - 1 ? $allDates[$index + 1] : null;
    
                $prevStatus = $prevDate ? $isAbsentOrLeave($prevDate) : false;
                $nextStatus = $nextDate ? $isAbsentOrLeave($nextDate) : false;
    
                if ($prevStatus && $nextStatus) {
                    $statusToApply = 'ABS';
                    
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
    
        // Second pass: Generate attendance data - EXACTLY like original but optimized
        foreach ($dateRange as $dateObj) {
            $date = $dateObj->toDateString();
            $is_found = null;
            $rowData = [
                'status' => '-', 'status_id' => '-', 'status_code' => '-', 'statusColor' => '#BDBDBD',
                'checkInTime' => null, 'checkOutTime' => null, 'updatedBy' => null, 'workingHour' => null,
                'OT' => null, 'earlyExit' => null, 'late' => null, 'attendance_remark' => '--',
                'checkInLocation' => '--', 'checkOutLocation' => '--', 'checkInPhoto' => [], 'checkOutPhoto' => [],
                'atd_segments' => [], 'presentCount' => 0, 'weekOffPresentCount' => 0, 'leaveCount' => 0,
                'holidayCount' => 0, 'weekOffCount' => 0, 'absentCount' => 0, 'halfDayCount' => 0,
                'missedPunchCount' => 0, 'overtimeCount' => 0, 'lateCount' => 0, 'earlyExitCount' => 0,
                'approvedLeaveCount' => 0, 'approvedMissedPunchCount' => 0, 'isApproved' => null,
                'previousCheckInTime' => null, 'previousCheckOutTime' => null, 'previousLate' => null,
                'previousExit' => null, 'UPL' => 0
            ];
            
            // Check if date is before joining date or after last working date
            $isBeforeJoining = $employee->emp_date_of_joining && Carbon::parse($date)->lt(Carbon::parse($employee->emp_date_of_joining));
            $isAfterLastWorking = $employee->emp_last_working_date && Carbon::parse($date)->gt(Carbon::parse($employee->emp_last_working_date));
            
            if ($isBeforeJoining || $isAfterLastWorking) {
                // Show "--" for dates before joining or after last working date
                $rowData['status'] = '--';
                $rowData['status_id'] = '--';
                $rowData['status_code'] = '--';
                $rowData['checkInTime'] = '--';
                $rowData['checkOutTime'] = '--';
                $rowData['workingHour'] = '--';
                $rowData['attendance_remark'] = '--';
                $is_found = 1;
            } elseif ($employee->emp_date_of_joining <= $date) {
                // Check if sandwich rule applies to this date
                if (isset($sandwichRuleApplied[$date])) {
                    $sandwichStatus = $sandwichRuleApplied[$date];
                    $is_found = 1;
    
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
                        $leaveStatus = MasterTable::where('m_type', $sandwichStatus)->first();
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
                    // Original logic continues here...
                    $attendance_record_exit = $attendanceRecords[$date] ?? null;
                    $attendance_log = $attendanceLogs[$date] ?? null;
                    $missedPunch = $missedPunches[$date] ?? null;
                    $holiday_record_exit = $holiday_record_exits[$date] ?? null;
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
    
                    if ($attendance_record_exit && $attendance_record_exit->fh_attendance_status && !isset($sandwichRuleApplied[$date])) {
                        if ($attendance_record_exit->atd_attendance_status == 252) {
                            $rowData['halfDayCount'] = 1;
                            $rowData['presentCount']  = 0.5;
                            $rowData['status_id'] = $attendance_record_exit->fh_attendance_status->m_id;
                            $rowData['status_code'] = $attendance_record_exit->fh_attendance_status->m_type;
                            $is_found = 1;
                        } else {
                            $rowData['status_id'] = $attendance_record_exit->fh_attendance_status->m_id;
                            $rowData['status_code'] = $attendance_record_exit->fh_attendance_status->m_type;
                            
                            if ($attendance_record_exit->atd_attendance_status == 251) {
                                $rowData['presentCount'] = 1;
                            }
                            if (!$missedPunch) {
                                $rowData['status_id'] = $attendance_record_exit->fh_attendance_status->m_id;
                                $rowData['status_code'] = $attendance_record_exit->fh_attendance_status->m_type;
                                if ($attendance_record_exit->atd_attendance_status == 251) {
                                    $rowData['presentCount'] = 1;
                                } else if ($attendance_record_exit->atd_attendance_status == 320) {
                                    $rowData["weekOffPresentCount"] = 1;
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
                        
                        if ($attendance_log) {
                            $rowData['previousCheckInTime'] = $attendance_log->al_check_in_time ? Carbon::parse($attendance_log->al_check_in_time)->format('H:i') : '-';
                            $rowData['previousCheckOutTime'] = $attendance_log->al_check_out_time ? Carbon::parse($attendance_log->al_check_out_time)->format('H:i') :  '-';
                            $rowData['previousLate'] = ($attendance_log->al_is_late && $attendance_log->al_late_duration) ? number_format($attendance_log->al_late_duration, 2) : null;
                            $rowData['previousExit'] = ($attendance_log->al_is_early_exit && $attendance_log->al_early_exit_duration) ? number_format($attendance_log->al_early_exit_duration, 2) : null;
                        }
                    }
    
                    $half_day_present = ($attendance_record_exit && $attendance_record_exit->atd_attendance_status == 252);
    
                    if (is_null($is_found) || $half_day_present) {
                        if ($leave_record_exit && $leave_record_exit->count() == 1) {
                            $lvr_exit = $leave_record_exit->first();
    
                            $is_approved = $lvr_exit->lvr_status != 170 && $lvr_exit->lvr_stage_completed;
                            $is_unpaid = $lvr_exit->fh_leave_cat_type->m_id == 215;
                            $is_half_day_leave = optional($lvr_exit->fh_leave_day_type)->m_id != 201;
                            $segment_id = $lvr_exit->fh_leave_day_segment->m_id ?? null;
    
                            $leave_category_ids = MasterTable::where('m_group', 'LEAVE_CATEGORY')->pluck('m_id')->toArray();
    
                            $rowData['leaveCount'] = $is_half_day_leave ? 0.5 : 1;
                            $rowData['approvedLeaveCount'] = ($is_approved && !$is_unpaid) ? $rowData['leaveCount'] : 0;
                            $rowData['isApproved'] = $is_approved;
    
                            if (in_array($lvr_exit->fh_leave_cat_type->m_id, $leave_category_ids) && $is_approved) {
                                $is_found = 1;
    
                                if ($half_day_present && $is_half_day_leave) {
                                    $presentStatus = $attendance_record_exit->fh_attendance_status;
    
                                    if ($segment_id == 236) {
                                        $rowData['status_id'] = $presentStatus->m_id . '/' . $lvr_exit->fh_leave_cat_type->m_id;
                                        $rowData['status'] = $presentStatus->m_name . '/' . $lvr_exit->fh_leave_cat_type->m_name;
                                        $rowData['status_code'] = $presentStatus->m_type . '/' . $lvr_exit->fh_leave_cat_type->m_type;
                                        $rowData['statusColor'] = json_decode($presentStatus->m_other, true)['color'] . '/' . json_decode($lvr_exit->fh_leave_cat_type->m_other, true)['color'];
                                    } elseif ($segment_id == 235) {
                                        $rowData['status_id'] = $lvr_exit->fh_leave_cat_type->m_id . '/' . $presentStatus->m_id;
                                        $rowData['status'] = $lvr_exit->fh_leave_cat_type->m_name . '/' . $presentStatus->m_name;
                                        $rowData['status_code'] = $lvr_exit->fh_leave_cat_type->m_type . '/' . $presentStatus->m_type;
                                        $rowData['statusColor'] = json_decode($lvr_exit->fh_leave_cat_type->m_other, true)['color'] . '/' . json_decode($presentStatus->m_other, true)['color'];
                                    }
    
                                    $rowData['presentCount'] = $is_unpaid ? 0.5 : 1;
                                    $rowData['leaveCount'] = 0.5;
                                    $rowData['approvedLeaveCount'] = $is_unpaid ? 0 : 0.5;
                                } elseif ($lvr_exit->lvr_total_leave_days == 0.5 && Carbon::parse($date)->format('Y-m-d') < now()->format('Y-m-d')) {
                                    $rowData['status_id'] = $lvr_exit->fh_leave_cat_type->m_id . '/' . $uplStatus->m_id;
                                    $rowData['status'] = $lvr_exit->fh_leave_cat_type->m_name . '/' . $uplStatus->m_name;
                                    $rowData['status_code'] = $lvr_exit->fh_leave_cat_type->m_type . '/' . $uplStatus->m_type;
                                    $rowData['statusColor'] = json_decode($lvr_exit->fh_leave_cat_type->m_other, true)['color'] . '/' . json_decode($uplStatus->m_other, true)['color'];
    
                                    $rowData['presentCount'] = 0.5;
                                    $rowData['leaveCount'] = 1;
                                    $rowData['approvedLeaveCount'] = !$is_unpaid ? 0.5 : 0;
                                } elseif (!$half_day_present) {
                                    $rowData['status_id'] = $lvr_exit->fh_leave_cat_type->m_id;
                                    $rowData['status'] = $lvr_exit->fh_leave_cat_type->m_name;
                                    $rowData['status_code'] = $lvr_exit->fh_leave_cat_type->m_type;
                                    $rowData['statusColor'] = json_decode($lvr_exit->fh_leave_cat_type->m_other, true)['color'];
                                    $rowData['presentCount'] = $is_unpaid ? 0 : $rowData['leaveCount'];
                                }
                            }
                        }
                    }
    
                    if (is_null($is_found) || $half_day_present) {
                        if ($leave_record_exit && $leave_record_exit->count() == 2) {
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
                                $rowData['leaveCount'] = 1;
                                $rowData['approvedLeaveCount'] = 0;
                                $rowData['presentCount'] = 0;
    
                                if ($is_approved1 && $l1->fh_leave_cat_type->m_id != 215) {
                                    $rowData['presentCount'] += 0.5;
                                    $rowData['approvedLeaveCount'] += 0.5;
                                }
    
                                if ($is_approved2 && $l2->fh_leave_cat_type->m_id != 215) {
                                    $rowData['presentCount'] += 0.5;
                                    $rowData['approvedLeaveCount'] += 0.5;
                                }
    
                                $rowData['isApproved'] = $is_approved1 && $is_approved2;
                            }
                        }
                    }
    
                    $attendance_exist = $attendance_record_exit && ($attendance_record_exit->atd_check_in_time || $attendance_record_exit->atd_check_out_time);
    
                    if (is_null($is_found) || $attendance_exist) {
                        if ($missedPunch) {
                            $rowData['missedPunchCount'] =  1;
                            $rowData['checkInTime'] = Carbon::createFromFormat('H:i:s', $missedPunch->ae_in_time)->format('h:i A');
                            $rowData['checkOutTime'] =  Carbon::createFromFormat('H:i:s', $missedPunch->ae_out_time)->format('h:i A');
                            $rowData['workingHour'] = number_format($missedPunch->ae_total_working, 2);
                            
                            if ($missedPunch->ae_status != 170 && $missedPunch->ae_stage_completed) {
                                $shiftStartTime = date('Y-m-d', strtotime($date)) . ' ' . $employee->fh_shift_type->pst_start_time ?? '09:00:00';
                                $shiftEndTime = date('Y-m-d', strtotime($date)) . ' ' . $employee->fh_shift_type->pst_end_time ?? '18:00:00';
    
                                $pst_start_time = Carbon::parse($shiftStartTime);
                                $pst_end_time = Carbon::parse($shiftEndTime);
                                $dailyWorkingHours = $pst_start_time->diffInMinutes($pst_end_time);
    
                                $checkInTime = Carbon::parse($date . ' ' . $missedPunch->ae_in_time);
                                $checkOutTime =  Carbon::parse($date . ' ' . $missedPunch->ae_out_time);
                                $workedDuration = $checkInTime->diffInMinutes($checkOutTime);
                                $fullDayThreshold = $dailyWorkingHours;
                                $halfDayThreshold = $dailyWorkingHours / 2;
                                
                                if ($workedDuration >= $fullDayThreshold) {
                                    $rowData['presentCount']  = 1;
                                    $rowData['status_id'] = $presentStatus->m_id;
                                    $rowData['status'] = $presentStatus->m_name;
                                    $rowData['status_code'] = $presentStatus->m_type;
                                    $rowData['statusColor'] = json_decode($presentStatus->m_other, true)['color'];
                                    $rowData['approvedMissedPunchCount'] = 0.5;
                                } else if ($workedDuration >= $halfDayThreshold) {
                                    if ($leave_record_exit && $leave_record_exit->count() == 1) {
                                        $lvr_exit = $leave_record_exit->first();
                                        if (optional($lvr_exit->fh_leave_day_segment)->m_id == 236) {
                                            $rowData['status_id'] = $halfDayStatus->m_id . '/' . $lvr_exit->fh_leave_cat_type->m_id;
                                            $rowData['status'] = $halfDayStatus->m_name . '/' . $lvr_exit->fh_leave_cat_type->m_name;
                                            $rowData['status_code'] = $halfDayStatus->m_type . '/' . $lvr_exit->fh_leave_cat_type->m_type;
                                            $rowData['statusColor'] = json_decode($halfDayStatus->m_other, true)['color'] . '/' . json_decode($lvr_exit->fh_leave_cat_type->m_other, true)['color'];
                                        } elseif (optional($lvr_exit->fh_leave_day_segment)->m_id == 235) {
                                            $rowData['status_id'] = $lvr_exit->fh_leave_cat_type->m_id . '/' . $halfDayStatus->m_id;
                                            $rowData['status'] = $lvr_exit->fh_leave_cat_type->m_name . '/' . $halfDayStatus->m_name;
                                            $rowData['status_code'] =  $lvr_exit->fh_leave_cat_type->m_type . '/' . $halfDayStatus->m_type;
                                            $rowData['statusColor'] = json_decode($lvr_exit->fh_leave_cat_type->m_other, true)['color'] . '/' . json_decode($halfDayStatus->m_other, true)['color'];
                                        }
                                    }
                                }
                            } else {
                                $rowData['status_id'] = $missedPunchStatus->m_id;
                                $rowData['status'] =   $missedPunchStatus->m_name;
                                $rowData['status_code'] = $missedPunchStatus->m_type;
                                $rowData['statusColor'] = json_decode($missedPunchStatus->m_other, true)['color'];
                            }
                            
                            $is_found = 1;
                        }
                    }
    
                    if (is_null($is_found)) {
                        if ($holiday_record_exit) {
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
                }
            }
    
            if ($rowData['status_id']) {
                $rowData['status_id'] = (string)$rowData['status_id'];
            }
            $rowData['date'] = $date;
            $attendanceData[] = $rowData;
        }
        
        return $attendanceData;
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

    public static function generateCompOff($employee, $date) {
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
            $co_reason = $log_data->al_reason;
        } else if ($atd_data) {
            $in_time = $atd_data->atd_check_in_time;
            $out_time = $atd_data->atd_check_out_time;
            $p_id = $atd_data->atd_id;
        } else {
            return; // No attendance data available
        }

        $in_time = $in_time ? Carbon::parse($in_time) : null;
        $out_time = $out_time ? Carbon::parse($out_time) : null;

        $compOffPolicy = CompOffPolicy::with("duration_conditions")
            ->where('cop_b_id', $employee->emp_b_id)
            ->where('cop_effective_date', '<=', $date)
            ->orderBy('cop_effective_date', 'desc')
        ->first();

        if ($compOffPolicy) {
            $workedHrs = $in_time->diffInHours($out_time);
            // Apply Comp Off Policy Logic
            if ($workedHrs) {
                $comp_off_quantity = CentralLogics::getCompOffQuantity($workedHrs, $compOffPolicy->duration_conditions);

                if ($comp_off_quantity) {
                    $credit_date = Carbon::now()->format('Y-m-d');
                    // Check for existing CompOff entry before creating
                    $existingCompOff = CompOff::where([['co_request_date', $date], ['co_emp_id', $employee->emp_id], ['co_b_id', $employee->emp_b_id]])->orderByDesc('co_id')->first();

                    if (!$existingCompOff || ($existingCompOff && $existingCompOff->co_status == 170)) {
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

                    CompOffBalance::updateOrCreate(
                        [
                            'cb_b_id' => $employee->emp_b_id,
                            'cb_emp_id' => $employee->emp_id,
                            'cb_year' => date('Y', strtotime($date)),
                            'cb_month' => date('m', strtotime($date)),
                        ],
                        [
                            'cb_alloted' => $comp_off_quantity + ($remaining ? $remaining->cb_alloted : 0),
                            'cb_balance_remaining' => ($remaining ? $remaining->cb_balance_remaining : 0) + $comp_off_quantity,
                        ]
                    );

                }
            }
        }
    }
}
