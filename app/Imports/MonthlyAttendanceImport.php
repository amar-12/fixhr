<?php

namespace App\Imports;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\PolicyShiftTiming;
use App\Models\PolicyHolidayList;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use App\Helpers\CentralLogics;
use App\Helpers\ApprovalHelper;
use App\Helpers\ShiftResolver;
use App\Models\AttendanceLog;
use App\Models\AutomationRule;
use App\Models\LeaveRequest;
use App\Models\PayrollPeriod;
use App\Models\OvertimePolicy;
use App\Models\RuleCriterion;
use App\Models\OtApprovalStatus;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Log;

class MonthlyAttendanceImport implements OnEachRow, WithHeadingRow, WithChunkReading, WithValidation
{
    protected $user;
    protected $rowNumber = 1;
    protected $errorMessages = [];
    protected $successfulCount = 0;
    protected $skippedCount = 0;
    protected $failedCount = 0;
    protected $check_date = 0;
    // protected $latitude;
    // protected $longitude;

    public function __construct($user, $check_date, $latitude=null, $longitude=null)
    {
        $this->user = $user;
        $this->check_date = $check_date;
        // $this->latitude = $latitude;
        // $this->longitude = $longitude;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function rules(): array
    {
        return [
            '*.emp code*' => 'required',
            '*.attendance start date(dd/mm/yyyy)*' => 'required',
            '*.attendance end date(dd/mm/yyyy)*' => 'required',
            '*.remark*' => '',
        ];
    }

    public function onRow(Row $row)
    {
        $this->rowNumber++;
        $row = $row->toArray();

        // if (!$this->latitude || !$this->longitude) {
        //     $this->failedCount++;
        //     $this->errorMessages[] = "Row {$this->rowNumber}: Location not captured.";
        //     return;
        // }

        $empCode = trim($row['emp_code'] ?? '');
        $start   = $row['attendance_start_dateddmmyyyy'] ?? null;
        $end     = $row['attendance_end_dateddmmyyyy'] ?? null;
        $checkIn = $row['attendance_check_in_time'] ?? null;
        $checkOut = $row['attendance_check_out_time'] ?? null;
        $remark  = $row['remark'] ?? '';
        $check_date  = $row['check_date'] ?? '';

        $errors = [];

        try {
            $startDate = is_numeric($start) ? Carbon::instance(ExcelDate::excelToDateTimeObject($start)) : Carbon::createFromFormat('d/m/Y', $start);
        } catch (\Exception $e) {
            $startDate = null;
            $errors[] = "Invalid Start Date.";
        }

        try {
            $endDate = is_numeric($end) ? Carbon::instance(ExcelDate::excelToDateTimeObject($end)) : Carbon::createFromFormat('d/m/Y', $end);
        } catch (\Exception $e) {
            $endDate = null;
            $errors[] = "Invalid End Date.";
        }

        if (Carbon::parse($startDate) >= Carbon::now()) {
            $errors[] = "Can't update Future Date's Attendance";
        }

        if ($startDate && $endDate && $startDate->gt($endDate)) {
            $errors[] = "Start Date cannot be after End Date.";
        }

        $employee = Employee::where('emp_code', $empCode)
            ->where('emp_b_id', $this->user->emp_b_id)
            ->where('emp_status', 71)
            ->whereNull('emp_last_working_date')
            ->first();

        if (!$employee) {
            $errors[] = "Employee Code '$empCode' not found.";
        } elseif ($employee->emp_date_of_joining && $startDate && Carbon::parse($employee->emp_date_of_joining)->gt($startDate)) {
            $errors[] = "Employee DOJ is after the start date.";
        }

        // Check if the payroll period for this date is frozen
        if(isset($employee->emp_b_id) && !empty($employee->emp_b_id)) {
            $frozen = PayrollPeriod::where('pp_b_id', $employee->emp_b_id)
                ->where('pp_start_date', '<=', $startDate)
                ->where('pp_end_date', '>=', $endDate)
                ->where('pp_is_freezed', 120)  // 120 = frozen
                ->exists();
            if ($frozen) {
                $errors[] = "Freezed attendance records cannot be updated.";
            }
        }

        if (!empty($errors)) {
            $this->failedCount++;
            $this->errorMessages[] = "Row {$this->rowNumber}: " . implode(' ', $errors);
            return;
        }

        $checkInTime2 = $this->parseTime($checkIn, $startDate->format('Y-m-d'));
        $checkOutTime2 = $this->parseTime($checkOut, $startDate->format('Y-m-d'));

        $resolvedShift2 = ShiftResolver::resolveEmployeeShift($employee, $startDate->format('Y-m-d'), $checkInTime2, $checkOutTime2);
        $shiftTiming2 = $resolvedShift2->shift ?? PolicyShiftTiming::find($employee->emp_shift_type_id);
        
        if (!$shiftTiming2) {
            return ['status' => false, 'message' => "Please assign shift to this employee."];
        }

        $weekOffDates = CentralLogics::getWeekOffDates($employee, null, null, $startDate->copy(), $endDate->copy());
        $holidayDates = PolicyHolidayList::where('phl_b_id', $this->user->emp_b_id)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('phl_start_date', [$startDate, $endDate])
                    ->orWhereBetween('phl_end_date', [$startDate, $endDate])
                    ->orWhere(function ($q2) use ($startDate, $endDate) {
                        $q2->where('phl_start_date', '<=', $startDate)
                            ->where('phl_end_date', '>=', $endDate);
                    });
            })
            ->get()
            ->flatMap(fn($holiday) => Carbon::parse($holiday->phl_start_date)->daysUntil(Carbon::parse($holiday->phl_end_date))->map(fn($date) => $date->format('Y-m-d')))
            ->unique()
            ->values()
            ->toArray();

        // $currentDate = $startDate->copy();
        $currentDate = $startDate->copy()->startOfDay();
        $endDate = $endDate->copy()->endOfDay();
        $late_count = $early_count = 0;
        $noOfDays = $startDate->diffInDays($endDate) + 1;

        // if ($resolvedShift2?->shift?->pst_auto_assign_shift == 1) {
        if ($this->check_date == 1) {
            $attendanceDate = $currentDate->format('Y-m-d');

            // Skip future attendance
            if (Carbon::parse($attendanceDate)->gt(Carbon::now())) {
                $this->skippedCount++;
                return;
            }

            // Skip holiday / week-off (if required by business rule)
            if (in_array($attendanceDate, $holidayDates) || in_array($attendanceDate, $weekOffDates)) {
                $this->skippedCount++;
                return;
            }

            // Build proper overnight datetime
            $checkInTime  = $this->parseTime($checkIn, $startDate->format('Y-m-d'));
            $checkOutTime = $this->parseTime($checkOut, $endDate->format('Y-m-d'));

            // Check if the payroll period for this date is frozen
            $frozen = PayrollPeriod::where('pp_b_id', $employee->emp_b_id)
                ->where('pp_start_date', '<=', $attendanceDate)
                ->where('pp_end_date', '>=', $attendanceDate)
                ->where('pp_is_freezed', 120)  // 120 = frozen
                ->exists();

            if ($frozen) {
                $this->skippedCount++;
                return;
            }

            // Ensure positive values for time calculations
            $workedHours = ($checkInTime && $checkOutTime && Carbon::parse($checkOutTime)->gte(Carbon::parse($checkInTime)))
                ? max(0, min(24, round(abs(Carbon::parse($checkOutTime)->diffInMinutes(Carbon::parse($checkInTime))) / 60, 2)))
                : 0;

            if (
                $shiftTiming2 &&
                $shiftTiming2->pst_allow_break1 == 1 &&
                $shiftTiming2->pst_is_break_paid == 0
            ) {

                $breakMinutes = (int) $shiftTiming2->pst_break1_duration;

                $workedHours = max(
                    0,
                    min(
                        24,
                        round($workedHours - ($breakMinutes / 60), 2)
                    )
                );
            }

            $workedDuration = ($checkInTime && $checkOutTime && Carbon::parse($checkOutTime)->gte(Carbon::parse($checkInTime)))
                ? abs(Carbon::parse($checkInTime)->diffInMinutes(Carbon::parse($checkOutTime)))
                : 0;

            // Attendance Preference
            $preference = $employee->emp_attendance_preference;

            // Retrieve shift start and end times
            $pst_start_time = Carbon::parse($startDate->format('Y-m-d').' '.$shiftTiming2->pst_start_time->format('H:i:s'));
            $pst_end_time = Carbon::parse($endDate->format('Y-m-d').' '.$shiftTiming2->pst_end_time->format('H:i:s'));
            // $minWorkingHours = $endDate->format('Y-m-d').' '.$shiftTiming2->pst_min_work_hour;

            $minWorkingHours = $shiftTiming2->pst_min_work_hour ? $pst_start_time->diffInMinutes($endDate->format('Y-m-d').' '.$shiftTiming2->pst_min_work_hour) : 0;

            // Calculate total shift duration in minutes
            $dailyWorkingHours = $pst_start_time && $pst_end_time ? $pst_start_time->diffInMinutes($pst_end_time) : 0;

            // Define half-day and full-day thresholds based on shift duration
            $halfDayThreshold = $dailyWorkingHours ? ($dailyWorkingHours / 2) - (int) ($shiftTiming2->pst_grace_time ?? 0) : 0;
            $fullDayThreshold = $dailyWorkingHours ? $dailyWorkingHours - (int) ($shiftTiming2->pst_grace_time ?? 0) : 0;

            // Modified attendance status logic
            $attendanceStatus = match (true) {
                // Single date with check-in/check-out on holiday or week-off
                in_array($attendanceDate, $holidayDates) && $checkInTime && $checkOutTime => 319, //HP
                in_array($attendanceDate, $weekOffDates) && $checkInTime && $checkOutTime => 320, // WOP
                $preference == 370 || (!empty($checkInTime) && $preference == 368) || (!empty($checkOutTime) && $preference == 369) => 251, // Present
                (((empty($checkInTime) || empty($checkOutTime)) && $preference == 367) || (empty($checkInTime) && $preference == 368) || (empty($checkOutTime) && $preference == 369)) => 228, // MSP
                ($workedDuration <= $minWorkingHours && $workedDuration >= ($minWorkingHours / 2)) => 252,
                $workedDuration < $halfDayThreshold => 203, // Absent
                $workedDuration >= $minWorkingHours && $workedDuration < $fullDayThreshold => 251, // Present but early going
                in_array($attendanceDate, $holidayDates) => 319, // Holiday Present
                in_array($attendanceDate, $weekOffDates) => 320, // Week Off Present
                $workedDuration >= $fullDayThreshold && !in_array($attendanceDate, $holidayDates) && !in_array($attendanceDate, $weekOffDates) => 251, // Present Full Day
                default => 203, // Absent
            };

            $graceTime = $shiftTiming2 ? $shiftTiming2->pst_grace_time : 0;
            $pst_start_with_grace = $checkInTime && $pst_start_time
                ? Carbon::parse(Carbon::parse($checkInTime)->format('Y-m-d') . ' ' . $pst_start_time->format('H:i:s'))->addMinutes($graceTime)
                : null;

            $is_early_exit = $is_late = $late_duration = $early_duration = 0;
            if ($attendanceStatus != 319 && $attendanceStatus != 320 && $checkInTime && $pst_start_with_grace && Carbon::parse($checkInTime)->gt($pst_start_with_grace)) {
                $is_late = 1;
                $late_duration = abs(Carbon::parse($attendanceDate . " " . $pst_start_time->copy()->format('H:i:s'))->diffInMinutes(Carbon::parse($checkInTime)));
            }

            if ($attendanceStatus != 319 && $attendanceStatus != 320 && $checkOutTime && $pst_end_time && Carbon::parse($checkOutTime)->lt(Carbon::parse($attendanceDate . " " . $pst_end_time->copy()->format('H:i:s')))) {
                $is_early_exit = 1;
                $early_duration = abs(Carbon::parse($checkOutTime)->diffInMinutes(Carbon::parse($attendanceDate . " " . $pst_end_time->copy()->format('H:i:s'))));
            }

            $is_absent = $workedDuration < $halfDayThreshold ? 1 : 0;

            $logExist = AttendanceLog::where('al_emp_id', $employee->emp_id)
                ->where('al_date', $attendanceDate)
                ->count();

            if ($logExist >= 3) {
                $this->skippedCount++;
                $this->errorMessages[] = "Row {$this->rowNumber}: Attendance already exists 3 times for Employee {$employee->emp_code} on {$attendanceDate}.";
                return;
            }

            $record = AttendanceLog::firstOrCreate(
                [
                    'al_b_id'                => $this->user->emp_b_id,
                    'al_emp_id'              => $employee->emp_id,
                    'al_pst_id'              => $shiftTiming2->pst_id,
                    'al_check_in_time'       => $checkInTime,
                    'al_check_out_time'      => $checkOutTime,
                    'al_date'                => $attendanceDate,
                    'al_is_late'             => $is_late,
                    'al_late_duration'       => $late_duration,
                    'al_is_early_exit'       => $is_early_exit,
                    'al_early_exit_duration' => $early_duration,
                    'al_is_absent'           => $is_absent,
                    'al_total_worked_hours'  => $workedHours,
                    'al_attendance_status'   => $attendanceStatus,
                    'al_reason'              => $remark,
                    'al_updated_by'          => $this->user->emp_id,
                    // 'al_latitude'          => $this->latitude ?? null,
                    // 'al_longitude'          => $this->longitude ?? null,
                ]
            );

            if ($record) {
                $record->al_code = 'AL-MI' . Carbon::parse($record->al_date)->format('Ymd') . '-' . $record->al_id;
                $record->save();
                $ot_enabled = OvertimePolicy::where('ot_b_id', $this->user->emp_b_id)->where('ot_is_enabled', 1)->exists();
                if ($ot_enabled) {
                    // $record->al_is_overtime = 1;
                    // $record->al_overtime_hours = CentralLogics::calculateOTRoster($record) ?? 0.00;
                    CentralLogics::processOvertimeApproval($this->user, $employee, $record, $shiftTiming2);
                }
                // $record->save();
            }
        } else {
            while ($currentDate->lte($endDate)) {
                $formattedDate = $currentDate->format('Y-m-d');

                // Skip week-off and holiday dates for date range
                if ($noOfDays > 1 && (in_array($formattedDate, $holidayDates) || in_array($formattedDate, $weekOffDates))) {
                    $this->skippedCount++;
                    $currentDate->addDay();
                    continue;
                }

                if (Carbon::parse($formattedDate) > Carbon::now()) {
                    $this->skippedCount++;
                    $currentDate->addDay();
                    continue;
                }

                $leaveExists = LeaveRequest::where('lvr_emp_id', $employee->emp_id)
                    ->where('lvr_b_id', $employee->emp_b_id)
                    // Check if the current formatted date falls between start and end (inclusive)
                    ->whereDate('lvr_start_date', '<=', $formattedDate)
                    ->whereDate('lvr_end_date', '>=', $formattedDate)
                // dd($leaveExists->toSql(), $leaveExists->getBindings());
                ->exists();
                if ($leaveExists) {
                    $currentDate->addDay();
                    continue;
                }

                $checkInTime = $this->parseTime($checkIn, $formattedDate);
                $checkOutTime = $this->parseTime($checkOut, $formattedDate);
                $resolvedShift = ShiftResolver::resolveEmployeeShift($employee, $formattedDate, $checkInTime, $checkOutTime);
                $shiftTiming = $resolvedShift->shift ?? PolicyShiftTiming::find($employee->emp_shift_type_id);
                if (!$shiftTiming) {
                    return ['status' => false, 'message' => "Please assign shift to this employee."];
                }
                // dd($resolvedShift, $resolvedShift->shift, $checkInTime, $checkOutTime);

                if (
                    $shiftTiming->pst_type_id == 245 &&
                    $checkInTime &&
                    $checkOutTime &&
                    Carbon::parse($checkOutTime)->lessThan(Carbon::parse($checkInTime))
                ) {
                    $checkOutTime = Carbon::parse($checkOutTime)->addDay()->format('Y-m-d H:i:s');
                }

                // Check if the payroll period for this date is frozen
                $frozen = PayrollPeriod::where('pp_b_id', $employee->emp_b_id)
                    ->where('pp_start_date', '<=', $formattedDate)
                    ->where('pp_end_date', '>=', $formattedDate)
                    ->where('pp_is_freezed', 120)  // 120 = frozen
                    ->exists();

                if ($frozen) {
                    $this->skippedCount++;
                    $currentDate->addDay();
                    continue;
                }

                // Ensure positive values for time calculations
                $workedHours = ($checkInTime && $checkOutTime && Carbon::parse($checkOutTime)->gte(Carbon::parse($checkInTime)))
                    ? max(0, min(24, round(abs(Carbon::parse($checkOutTime)->diffInMinutes(Carbon::parse($checkInTime))) / 60, 2)))
                    : 0;

                if (
                    $shiftTiming &&
                    $shiftTiming->pst_allow_break1 == 1 &&
                    $shiftTiming->pst_is_break_paid == 0
                ) {

                    $breakMinutes = (int) $shiftTiming->pst_break1_duration;

                    $workedHours = max(
                        0,
                        min(
                            24,
                            round($workedHours - ($breakMinutes / 60), 2)
                        )
                    );
                }

                $workedDuration = ($checkInTime && $checkOutTime && Carbon::parse($checkOutTime)->gte(Carbon::parse($checkInTime)))
                    ? abs(Carbon::parse($checkInTime)->diffInMinutes(Carbon::parse($checkOutTime)))
                    : 0;

                // Attendance Preference
                $preference = $employee->emp_attendance_preference;

                // Retrieve shift start and end times
                $pst_start_time = $shiftTiming ? Carbon::parse($shiftTiming->pst_start_time) : null;
                $pst_end_time = $shiftTiming ? Carbon::parse($shiftTiming->pst_end_time) : null;
                $minWorkingHours = $pst_start_time && $shiftTiming->pst_min_work_hour
                    ? $pst_start_time->diffInMinutes(Carbon::parse($pst_start_time->format('Y-m-d') . ' ' . $shiftTiming->pst_min_work_hour)) ?? 0
                    : 0;

                $startOfMonth = Carbon::parse($formattedDate)->startOfMonth()->toDateString();
                $end_date = Carbon::parse($formattedDate)->subDay()->toDateString();

                // Calculate total shift duration in minutes
                $dailyWorkingHours = $pst_start_time && $pst_end_time ? $pst_start_time->diffInMinutes($pst_end_time) : 0;

                // Define half-day and full-day thresholds based on shift duration
                $halfDayThreshold = $dailyWorkingHours ? ($dailyWorkingHours / 2) - (int) ($shiftTiming->pst_grace_time ?? 0) : 0;
                $fullDayThreshold = $dailyWorkingHours ? $dailyWorkingHours - (int) ($shiftTiming->pst_grace_time ?? 0) : 0;

                // Modified attendance status logic
                $attendanceStatus = match (true) {
                    // Single date with check-in/check-out on holiday or week-off
                    $noOfDays == 1 && in_array($formattedDate, $holidayDates) && $checkInTime && $checkOutTime => 319, // Holiday Present
                    $noOfDays == 1 && in_array($formattedDate, $weekOffDates) && $checkInTime && $checkOutTime => 320, // Week Off Present
                    $preference == 370 || (!empty($checkInTime) && $preference == 368) || (!empty($checkOutTime) && $preference == 369) => 251, // Present
                    (((empty($checkInTime) || empty($checkOutTime)) && $preference == 367) || (empty($checkInTime) && $preference == 368) || (empty($checkOutTime) && $preference == 369)) => 228, // MSP
                    ($workedDuration <= $minWorkingHours && $workedDuration >= ($minWorkingHours / 2)) => 252,

                    $workedDuration < $halfDayThreshold => 203, // Absent
                    $workedDuration >= $minWorkingHours && $workedDuration < $fullDayThreshold => 251, // Present but early going
                    in_array($formattedDate, $holidayDates) => 319, // Holiday Present
                    in_array($formattedDate, $weekOffDates) => 320, // Week Off Present
                    $workedDuration >= $fullDayThreshold && !in_array($formattedDate, $holidayDates) && !in_array($formattedDate, $weekOffDates) => 251, // Present Full Day
                    default => 203, // Absent
                };

                $graceTime = $shiftTiming ? $shiftTiming->pst_grace_time : 0;
                $pst_start_with_grace = $checkInTime && $pst_start_time
                    ? Carbon::parse(Carbon::parse($checkInTime)->format('Y-m-d') . ' ' . $pst_start_time->format('H:i:s'))->addMinutes($graceTime)
                    : null;

                $is_early_exit = $is_late = $late_duration = $early_duration = 0;
                if ($attendanceStatus != 319 && $attendanceStatus != 320 && $checkInTime && $pst_start_with_grace && Carbon::parse($checkInTime)->gt($pst_start_with_grace)) {
                    $is_late = 1;
                    $late_duration = abs(Carbon::parse($formattedDate . " " . $pst_start_time->copy()->format('H:i:s'))->diffInMinutes(Carbon::parse($checkInTime)));
                }

                if ($attendanceStatus != 319 && $attendanceStatus != 320 && $checkOutTime && $pst_end_time && Carbon::parse($checkOutTime)->lt(Carbon::parse($formattedDate . " " . $pst_end_time->copy()->format('H:i:s')))) {
                    $is_early_exit = 1;
                    $early_duration = abs(Carbon::parse($checkOutTime)->diffInMinutes(Carbon::parse($formattedDate . " " . $pst_end_time->copy()->format('H:i:s'))));
                }

                $is_absent = $workedDuration < $halfDayThreshold ? 1 : 0;

                $logExist = AttendanceLog::where('al_emp_id', $employee->emp_id)
                    ->where('al_date', $formattedDate)
                    ->count();

                if ($logExist >= 3) {
                    $this->skippedCount++;
                    $this->errorMessages[] = "Row {$this->rowNumber}: Attendance already exists 3 times for Employee {$employee->emp_code} on {$formattedDate}.";
                    $currentDate->addDay();
                    continue;
                }

                $record = AttendanceLog::firstOrCreate(
                    [
                        'al_b_id'                => $this->user->emp_b_id,
                        'al_emp_id'              => $employee->emp_id,
                        'al_pst_id'              => $shiftTiming->pst_id,
                        'al_check_in_time'       => $checkInTime,
                        'al_check_out_time'      => $checkOutTime,
                        'al_date'                => $formattedDate,
                        'al_is_late'             => $is_late,
                        'al_late_duration'       => $late_duration,
                        'al_is_early_exit'       => $is_early_exit,
                        'al_early_exit_duration' => $early_duration,
                        'al_is_absent'           => $is_absent,
                        'al_total_worked_hours'  => $workedHours,
                        'al_attendance_status'   => $attendanceStatus,
                        'al_reason'              => $remark,
                        'al_updated_by'          => $this->user->emp_id,
                    ]
                );

                if ($record) {
                    $record->al_code = 'AL-MI' . Carbon::parse($record->al_date)->format('Ymd') . '-' . $record->al_id;
                    $record->save();
                    $ot_enabled = OvertimePolicy::where('ot_b_id', $this->user->emp_b_id)->where('ot_is_enabled', 1)->exists();
                    if ($ot_enabled) {
                        // $record->al_is_overtime = 1;
                        // if ($shiftTiming->pst_type_id == 245) {
                        //     $record->al_overtime_hours = CentralLogics::calculateOTRoster($record) ?? 0.00;
                        // } else {
                        //     $record->al_overtime_hours = CentralLogics::calculateOT($record) ?? 0.00;
                        // }
                        CentralLogics::processOvertimeApproval($this->user, $employee, $record, $shiftTiming);
                    }
                    // $record->save();
                }

                $currentDate->addDay();
            }
        }

        $this->successfulCount++;
    }

    private function parseTime($value, $date)
    {
        if (!$value) return null;

        if (is_numeric($value)) {
            $parsed = ExcelDate::excelToDateTimeObject($value);
            return $date . ' ' . $parsed->format('H:i:s');
        }

        if (preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', trim($value), $matches)) {
            $hour = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $minute = $matches[2];
            $second = $matches[3] ?? '00';
            return "$date $hour:$minute:$second";
        }

        return null;
    }

    public function getErrorMessages(): array
    {
        return $this->errorMessages;
    }

    public function getSummary(): array
    {
        return [
            'success' => $this->successfulCount,
            'failed' => $this->failedCount,
            'skipped' => $this->skippedCount,
        ];
    }
}
