<?php

namespace App\Imports;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use Carbon\Carbon;
use Hamcrest\Type\IsNumeric;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class AttendanceImport implements ToCollection
{
    protected $errorMessages = [];
    
    protected $successfulCount = 0;
    protected $skippedCount = 0;
    protected $failedCount = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                // Skip the header row
                if (empty($row[1]) || $row[1] === 'Employee Code *') continue;

                \Log::info(print_r($row, true));
                
                $employeeCode = trim($row[1] ?? '');
                $punchDate = trim($row[2] ?? '');
                $checkinMethod = trim($row[3] ?? '');
                $punchInTime = trim($row[4] ?? '');
                $punchOutTime = trim($row[5] ?? '');

                // Validate the employee exists
                $employee = Employee::where('emp_code', $employeeCode)
                    ->where('emp_status', 71)
                ->first();

                // Validate and parse the punch date
                try {
                    $formattedDate = is_numeric($punchDate) ? Carbon::instance(ExcelDate::excelToDateTimeObject($punchDate)) : Carbon::createFromFormat('d/m/Y', $punchDate);
                } catch (\Exception $e) {
                    $formattedDate = null;
                    $errors[] = "Invalid Start Date.";
                }

                if (!$employee) {
                    $errors[] = "Employee Code '$employeeCode' not found.";
                } elseif ($employee->emp_date_of_joining && $formattedDate && Carbon::parse($employee->emp_date_of_joining)->gt($formattedDate)) {
                    $errors[] = "Employee DOJ is after the start date.";
                }

                if (Carbon::parse($formattedDate) >= Carbon::now()) {
                    $errors[] = "Can't update Future Date's Attendance";
                }

                // Check if the payroll period for this date is frozen
                $frozen = PayrollPeriod::where('pp_b_id', $employee->emp_b_id)
                    ->where('pp_start_date', '<=', $formattedDate)
                    ->where('pp_end_date', '>=', $formattedDate)
                    ->where('pp_is_freezed', 120)  // 120 = frozen
                    ->exists();

                if ($frozen) {
                    $errors[] = "Freezed attendance records cannot be updated.";
                }

                if (!empty($errors)) {
                    $this->failedCount++;
                    $this->errorMessages[] = "Row {$row}: " . implode(' ', $errors);
                    return;
                }

                // Parse datetime safely
                $punchInDateTime = null;
                if ($punchInTime) {
                    $punchInDateTime = Carbon::parse($formattedDate->format('Y-m-d') . "$punchInTime");
                }

                $punchOutDateTime = null;
                if ($punchOutTime) {
                    $punchOutDateTime = Carbon::parse($formattedDate->format('Y-m-d') . "$punchOutTime");
                }

                // Check for existing attendance record
                $attendance = AttendanceRecord::where('atd_emp_id', $employee->emp_id)
                    ->where('atd_date', $formattedDate)
                ->first();

                if (!$attendance) {
                    // Create a new attendance record
                    AttendanceRecord::create([
                        'atd_emp_id' => $employee->emp_id,
                        'atd_b_id' => $employee->emp_b_id,
                        'atd_pst_id' => $employee->emp_shift_type_id,
                        'atd_work_mode_type_id' => $employee->emp_work_mode_id,
                        'atd_checkin_method_id' => 314, // Selfie
                        'atd_date' => $formattedDate,
                        'atd_check_in_time' => $punchInDateTime ? $punchInDateTime->format('Y-m-d H:i:s') : NULL,
                        'atd_check_out_time' => $punchOutDateTime ? $punchOutDateTime->format('Y-m-d H:i:s') : NULL,
                    ]);

                    $this->successfulCount++;
                }
            } catch (\Exception $e) {
                // Log or handle errors gracefully
                \Log::error("Error processing row: {$e->getMessage()}", [
                    'row' => $row,
                ]);
            }
        }
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
