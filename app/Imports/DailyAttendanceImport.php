<?php

namespace App\Imports;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\MasterTable;
use App\Models\PolicyShiftTiming;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\Log;

class DailyAttendanceImport implements ToCollection
{
    protected $user;
    public $successfulImports = 0;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function collection(Collection $collection)
    {
        foreach ($collection as $key => $row) {
            // Skip header rows
            if ($key === 0 || $key === 1) {
                continue;
            }

            try {
                // Validate essential columns
                if (empty($row[1]) || empty($row[2]) || empty($row[3]) || empty($row[4]) || empty($row[5])) {
                    continue;
                }
                $emp_code_data = $row[1];
                $atd_date_data = $row[2];
                $checkin_method_data = $row[3];
                $atd_check_in_time_data = $row[4];
                $atd_check_out_time_data = $row[5];

                // Convert Excel serial number to a PHP DateTime object
                $php_date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($atd_date_data);
                // Format the date as 'Y-m-d' (for database storage)
                $attendance_date = $php_date->format('Y-m-d'); // Example: '2024-10-19'


                // Convert the time (e.g., "4pm" or "10am") to 24-hour format using strtotime
                $attendance_check_in_time = strtotime($atd_check_in_time_data);
                $attendance_check_out_time = strtotime($atd_check_out_time_data);

                // Merge the date with the time to create a full datetime string (Y-m-d H:i:s)
                $attendance_check_in_datetime = $attendance_date . ' ' . date('H:i:s', $attendance_check_in_time); // Example: '2024-10-04 10:00:00'
                $attendance_check_out_datetime = $attendance_date . ' ' . date('H:i:s', $attendance_check_out_time); // Example: '2024-10-04 17:00:00'

                // Check if the conversion was successful
                if (!$attendance_check_in_datetime || !$attendance_check_out_time) {
                    // Log::error("Invalid date/time format in row $key");
                    continue; // Skip this row if the format is incorrect
                }

                // Process attendance data
                $attendance_method = MasterTable::where('m_group', 'CHECKIN_METHOD')
                    ->where('m_name', $checkin_method_data)
                    ->first();

                $employee = Employee::where('emp_b_id', $this->user->emp_b_id)
                    ->where('emp_code', $emp_code_data)
                    ->where('emp_status', 71)
                    ->first();

                if (!$attendance_method || !$employee) {
                    continue; // Skip if method or employee not found
                }

                $shiftTiming = $employee->fh_shift_type;
                if (!$shiftTiming) {
                    continue; // Skip if method or employee not found
                }

                // / Parse shift start and end times
                $shiftStartTime = Carbon::createFromFormat('Y-m-d H:i:s', $attendance_date . ' ' . $shiftTiming->pst_start_time);
                $shiftEndTime = Carbon::createFromFormat('Y-m-d H:i:s', $attendance_date . ' ' . $shiftTiming->pst_end_time);


                // Check Late or Early Exit
                $status = 228;
                $atd_is_late = 0;
                $atd_is_early_exit = 0;
                $atd_is_overtime = 0;
                $atd_late_duration = 0;
                $atd_early_exit_duration = 0;
                $atd_overtime_hours = 0;
                if ($attendance_check_in_datetime > $shiftStartTime) {
                    $status = 251; // 'Late'
                    $atd_is_late = 1;
                    // Calculate the difference in minutes
                    $atd_late_duration = number_format($shiftStartTime->diffInMinutes($attendance_check_in_datetime, false),2); // Set the second parameter to false for negative differences
                }
                if ($attendance_check_out_datetime < $shiftEndTime) {
                    $status = 251; // 'Early Exit'
                    $atd_is_early_exit = 1;
                    $atd_early_exit_duration = number_format(abs($shiftEndTime->diffInMinutes($attendance_check_out_datetime, false)),2);
                }
                if ($attendance_check_out_datetime > $shiftEndTime) {
                    $atd_is_overtime = 1;
                    $atd_overtime_hours = number_format(abs($shiftEndTime->diffInMinutes($attendance_check_out_datetime, false)) / 60,2);
                }

                // Calculate total shift duration in minutes
                $dailyWorkingHours = $shiftStartTime->diffInMinutes($shiftEndTime);
                // Define half-day and full-day thresholds based on shift duration
                $halfDayThreshold = $dailyWorkingHours / 2;
                $fullDayThreshold = $dailyWorkingHours;

                // Parse into Carbon objects
                $attendance_check_in_time_m = Carbon::parse($attendance_check_in_time);
                $attendance_check_out_time_m = Carbon::parse($attendance_check_out_time);

                // Calculate the worked duration
                $workedDuration = $attendance_check_in_time_m->diffInMinutes($attendance_check_out_time_m, false);

                // Determine attendance status based on worked duration
                if ($workedDuration <= $halfDayThreshold) {
                    $status = 252; // Half-Day
                } elseif (($workedDuration >= $fullDayThreshold)) {
                    $status = 251; // Present
                }

                // Create or update attendance record
                AttendanceRecord::updateOrCreate(
                    [
                        'atd_emp_id' => $employee->emp_id,
                        'atd_date' => $attendance_date,
                    ],
                    [
                        'atd_b_id' => $employee->emp_b_id,
                        'atd_pst_id' => $employee->emp_shift_type_id,
                        'atd_work_mode_type_id' => $employee->emp_work_mode_id,
                        'atd_checkin_method_id' => $attendance_method->m_id,
                        'atd_check_in_time' => $attendance_check_in_datetime,
                        'atd_check_out_time' => $attendance_check_out_datetime,
                        'atd_attendance_status' => $status,
                        'atd_is_late' => $atd_is_late,
                        'atd_is_early_exit' => $atd_is_early_exit,
                        'atd_late_duration' => $atd_late_duration,
                        'atd_early_exit_duration' => number_format($atd_early_exit_duration,2),
                        'atd_is_overtime' => $atd_is_overtime,
                        'atd_overtime_hours' => number_format($atd_overtime_hours),
                    ]
                );


                $this->successfulImports++;
            } catch (\Exception $e) {
                Log::error('Import failed for row ' . $key . ': ' . $e->getMessage());
                continue;
            }
        }
    }
}
