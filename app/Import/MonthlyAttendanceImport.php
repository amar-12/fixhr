<?php

namespace App\Imports;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\PolicyShiftTiming;
use App\Models\PolicyHolidayList;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use App\Helpers\CentralLogics;
use App\Models\AttendanceLog;
use App\Models\AutomationRule;
use App\Models\PayrollPeriod;
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

    public function __construct($user)
    {
        $this->user = $user;
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

        $empCode = trim($row['emp_code'] ?? '');
        $start   = $row['attendance_start_dateddmmyyyy'] ?? null;
        $end     = $row['attendance_end_dateddmmyyyy'] ?? null;
        $checkIn = $row['attendance_check_in_time'] ?? null;
        $checkOut = $row['attendance_check_out_time'] ?? null;
        $remark  = $row['remark'] ?? '';

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
            ->first();

        if (!$employee) {
            $errors[] = "Employee Code '$empCode' not found.";
        } elseif ($employee->emp_date_of_joining && $startDate && Carbon::parse($employee->emp_date_of_joining)->gt($startDate)) {
            $errors[] = "Employee DOJ is after the start date.";
        }

        // ðŸ”’ Check if the payroll period for this date is frozen
        $frozen = PayrollPeriod::where('pp_b_id', $employee->emp_b_id)
            ->where('pp_start_date', '<=', $startDate)
            ->where('pp_end_date', '>=', $endDate)
            ->where('pp_is_freezed', 120)  // 120 = frozen
            ->exists();

        if ($frozen) {
            $errors[] = "Freezed attendance records cannot be updated.";
        }

        if (!empty($errors)) {
            $this->failedCount++;
            $this->errorMessages[] = "Row {$this->rowNumber}: " . implode(' ', $errors);
            return;
        }

        $shiftTiming = $employee->emp_shift_type_id ? PolicyShiftTiming::find($employee->emp_shift_type_id) : null;

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

        $currentDate = $startDate->copy();

        // Automation Settings
        $ar_late = AutomationRule::where('ar_b_id', $employee->emp_b_id)->where('ar_rule_type', 414)->first() ?? null;
        $ar_early_going = AutomationRule::where('ar_b_id', $employee->emp_b_id)->where('ar_rule_type', 415)->first() ?? null;
        $ar_late_occurrences = $ar_late ? $ar_late->ar_occurrences : 0;
        $ar_early_occurrences = $ar_late ? $ar_late->ar_occurrences : 0;
        $late_count = $early_count = 0;

        $noOfDays = $startDate->diffInDays($endDate) + 1;

        while ($currentDate->lte($endDate)) {
            $formattedDate = $currentDate->format('Y-m-d');

            $checkInTime = $this->parseTime($checkIn, $formattedDate);
            $checkOutTime = $this->parseTime($checkOut, $formattedDate);

            // ðŸ”’ Check if the payroll period for this date is frozen
            $frozen = PayrollPeriod::where('pp_b_id', $employee->emp_b_id)
                ->where('pp_start_date', '<=', $formattedDate)
                ->where('pp_end_date', '>=', $formattedDate)
                ->where('pp_is_freezed', 120)  // 120 = frozen
                ->exists();

            if ($frozen) {
                continue; // Skip if payroll is frozen for that date
            }

            $workedHours = ($checkInTime && $checkOutTime)
                ? max(0, min(24, round(Carbon::parse($checkOutTime)->diffInMinutes(Carbon::parse($checkInTime)) / 60, 2)))
                : 0;

            $workedDuration = Carbon::parse($checkInTime)->diffInMinutes(Carbon::parse($checkOutTime));

            // Attendance Preference
            $preference = $employee->emp_attendance_preference;

            // Retrieve shift start and end times
            $pst_start_time = Carbon::parse($shiftTiming->pst_start_time);
            $pst_end_time = Carbon::parse($shiftTiming->pst_end_time);
            $minWorkingHours = $pst_start_time->diffInMinutes(Carbon::parse($pst_start_time->format('Y-m-d') . ' ' . $shiftTiming->pst_min_work_hour)) ?? 0;

            $startOfMonth = Carbon::parse($formattedDate)->startOfMonth()->toDateString();
            $end_date = Carbon::parse($formattedDate)->subDay()->toDateString();

            // Getting Late & Early Counts
            if ($ar_late) {
                $late_count += AttendanceRecord::where(
                    [
                        ["atd_is_late", "=", 1],
                        ["atd_emp_id", "=", $employee->emp_id]
                    ]
                )
                    ->whereBetween("atd_date", [$startOfMonth, $end_date])
                ->count();
            }
            if ($ar_early_going) {
                $early_count += AttendanceRecord::where([
                    ["atd_is_early_exit", "=", 1],
                    ["atd_emp_id", "=", $employee->emp_id]
                ])
                    ->whereBetween("atd_date", [$startOfMonth, $end_date])
                    ->count();
            }
            
            $ar_mark_half_day = $ar_late && $ar_late->ar_is_mode_enabled && $ar_late->ar_mark_half_day_time
                ? Carbon::parse($formattedDate . ' ' . $pst_start_time->copy()->format('H:i:s'))
                ->addHours((int) date('H', strtotime($ar_late->ar_mark_half_day_time)))
            : null;
            $ar_mark_early = $ar_early_going && $ar_early_going->ar_mark_half_day_time
                ? Carbon::parse($formattedDate . ' ' . $pst_end_time->copy()->format('H:i:s'))->addHours((int) date('H', strtotime($ar_early_going->ar_mark_half_day_time)))
            : null;

            // Calculate total shift duration in minutes
            $dailyWorkingHours = $pst_start_time->diffInMinutes($pst_end_time);

            // Define half-day and full-day thresholds based on shift duration
            $halfDayThreshold = ($dailyWorkingHours / 2) - (int) $shiftTiming->pst_grace_time;
            $fullDayThreshold = $dailyWorkingHours - (int) $shiftTiming->pst_grace_time;

            $attendanceStatus = match (true) {
                in_array($formattedDate, $holidayDates) && $noOfDays > 1 => 321,
                in_array($formattedDate, $weekOffDates) && $noOfDays > 1 => 322,
                $preference == 370 || (!empty($checkInTime) && $preference == 368) || (!empty($checkOutTime) && $preference == 369) => 251, // Present
                (((empty($checkInTime) || empty($checkOutTime)) && $preference == 367) || (empty($checkInTime) && $preference == 368) || (empty($checkOutTime) && $preference == 369)) => 228, // MSP
                ($checkInTime > $ar_mark_half_day && $late_count >= $ar_late_occurrences) || ($workedDuration <= $minWorkingHours && $workedDuration >= ($minWorkingHours / 2)) => 252, // Half Day
                $workedDuration < $halfDayThreshold => 203, // Absent
                ($checkOutTime < $ar_mark_early && $early_count >= $ar_early_occurrences) || ($workedDuration <= $minWorkingHours && $workedDuration >= ($minWorkingHours / 2)) => 252, // Half Day
                $workedDuration >= $minWorkingHours  && $workedDuration < $fullDayThreshold => 251, // Present but early going
                $workedDuration >= $fullDayThreshold && in_array($formattedDate, $holidayDates) => 319, // Holiday Present
                $workedDuration >= $fullDayThreshold && in_array($formattedDate, $weekOffDates) => 320, // Week Off Present
                $workedDuration >= $fullDayThreshold && !in_array($formattedDate, $holidayDates) && !in_array($formattedDate, $weekOffDates) => 251, // Present Full Day
                default => 203,
            };

            $graceTime = $shiftTiming->pst_grace_time;
            $pst_start_with_grace = Carbon::parse(Carbon::parse($checkInTime)->format('Y-m-d') . ' ' . $pst_start_time->format('H:i:s'))->addMinutes($graceTime);

            $is_early_exit = $is_late = $late_duration = $early_duration = 0;
            if ($checkInTime && Carbon::parse($checkInTime)->gt($pst_start_with_grace)) {
                $is_late = 1;
                $late_duration = Carbon::parse($formattedDate . "" . $pst_start_time->copy()->format('H:i:s'))->diffInMinutes($checkInTime);
            }

            if ($checkOutTime && Carbon::parse($checkOutTime) < (Carbon::parse(Carbon::parse($checkOutTime)->format('Y-m-d') . ' ' . $pst_end_time->format('H:i:s')))) {
                $is_early_exit = 1;
                $early_duration = Carbon::parse($checkOutTime)->diffInMinutes(Carbon::parse($formattedDate . "" . $pst_end_time->copy()->format('H:i:s')));
            }

            $workedMinutes = Carbon::parse($formattedDate . " " . date('H:i:s', strtotime($checkInTime)))->diffInMinutes(Carbon::parse($formattedDate . " " . date('H:i:s', strtotime($checkOutTime))));
            if ($workedMinutes > $fullDayThreshold) {
                $overtime_mins = $workedMinutes - $dailyWorkingHours;
                $is_overtime = 1;
            } else {
                $overtime_mins = 0;
                $is_overtime = 0;
            }

            $is_absent = $workedDuration < $halfDayThreshold ? 1 : 0;

            // $this->rowNumber == 3 ? dd($currentDate, $checkInTime, $checkOutTime, $workedDuration >= $fullDayThreshold, in_array($formattedDate, $weekOffDates), $attendanceStatus) : "";

            $record = AttendanceRecord::updateOrCreate(
                [
                    'atd_b_id' => $this->user->emp_b_id,
                    'atd_emp_id' => $employee->emp_id,
                    'atd_date' => $formattedDate,
                ],
                [
                    'atd_check_in_time' => $checkInTime,
                    'atd_check_out_time' => $checkOutTime,
                    'atd_total_worked_hours' => $workedHours,
                    'atd_attendance_status' => $attendanceStatus,
                    'atd_is_late' => $is_late,
                    'atd_late_duration' => $late_duration,
                    'atd_is_early_exit' => $is_early_exit,
                    'atd_early_exit_duration' => $early_duration,
                    'atd_is_overtime' => $is_overtime,
                    'atd_overtime_hours' => $overtime_mins,
                    'atd_is_absent' => $is_absent,
                    'atd_remark' => $remark,
                    'atd_pst_id' => $shiftTiming->pst_id ?? null,
                    'atd_work_mode_type_id' => 62,
                    'atd_module_id' => 249,
                    'atd_next_approver' => 1,
                    'atd_request_status' => 140,
                ]
            );

            $currentDate->addDay();
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
