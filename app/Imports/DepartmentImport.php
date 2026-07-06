<?php

namespace App\Imports;

use App\Models\Department;
use App\Models\PolicyAttendance;
use App\Models\PolicyShiftTiming;
use App\Models\PolicyHolidayList;
use App\Models\PolicyLeave;
use App\Models\PolicyWeekOff;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Exports\ErrorExport;
use Maatwebsite\Excel\Facades\Excel;

class DepartmentImport implements ToCollection
{
    public $errorMessages = [];
    protected $user;
    protected $successRows = [];

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function collection(Collection $rows)
    {
        $expectedColumns = [
            'S. No.*',
            'Department Name*',
            'Attendance Policy',
            'Shift Policy',
            'Holiday Policy',
            'Leave Policy',
            'Weekly Policy'
        ];

        $successfulCount = 0;
        $unsuccessfulCount = 0;

        foreach ($rows as $key => $row) {
            if ($key === 0) {
                $headers = array_map('trim', $row->toArray());

                $missing = array_diff($expectedColumns, $headers);
                $extra = array_diff($headers, $expectedColumns);

                if ($missing || $extra) {
                    $msg = "Invalid column headers.";
                    if ($missing) {
                        $msg .= " Missing: " . implode(', ', $missing) . ".";
                    }
                    if ($extra) {
                        $msg .= " Unexpected: " . implode(', ', $extra) . ".";
                    }
                    throw new \Exception($msg);
                }
                continue;
            }

            $department_name   = trim($row[1]) ?? null;
            $attendance_policy = trim($row[2]) ?? null;
            $shift_policy      = trim($row[3]) ?? null;
            $holiday_policy    = trim($row[4]) ?? null;
            $leave_policy      = trim($row[5]) ?? null;
            $weakly_policy     = trim($row[6]) ?? null;

            $rowErrors = [];
            if (!$department_name) {
                $rowErrors[] = "Department Name '{$department_name}' not found.";
            }

            // Unique check
            if (
                !empty($department_name) &&
                Department::where('d_name', $department_name)
                      ->where('d_b_id', $this->user->emp_b_id)
                      ->exists()
            ) {
                $rowErrors[] = "Department Name '$department_name' already exists.";
            }

            $attendance_policy_data = PolicyAttendance::where('ap_name', $attendance_policy)->where('ap_b_id', $this->user->emp_b_id)->first();
            $shift_policy_data = PolicyShiftTiming::where('pst_name', $shift_policy)->where('pst_b_id', $this->user->emp_b_id)->first();
            $holiday_policy_data = PolicyHolidayList::where('phl_name', $holiday_policy)->where('phl_b_id', $this->user->emp_b_id)->first();
            $leave_policy_data = PolicyLeave::where('pl_name', $leave_policy)->where('pl_b_id', $this->user->emp_b_id)->first();
            $weakly_policy_data = PolicyWeekOff::where('pwo_name', $weakly_policy)->where('pwo_b_id', $this->user->emp_b_id)->first();

            if (!$attendance_policy_data) {
                $rowErrors[] = "Attendance Policy '{$attendance_policy_data}' not found.";
            }
            if (!$shift_policy_data) {
                $rowErrors[] = "Shift Policy '{$shift_policy_data}' not found.";
            }
            if (!$holiday_policy_data) {
                $rowErrors[] = "Holiday Policy '{$holiday_policy_data}' not found.";
            }
            if (!$leave_policy_data) {
                $rowErrors[] = "Leave Policy '{$leave_policy_data}' not found.";
            }
            if (!$weakly_policy_data) {
                $rowErrors[] = "Weekoff Policy '{$weakly_policy_data}' not found.";
            }

            if (!empty($rowErrors)) {
                $unsuccessfulCount++;
                $this->errorMessages[] = "Row " . ($key + 1) . ": " . implode(' ', $rowErrors);
                continue;
            }

            try {
                Department::create([
                    'd_b_id' => $this->user->emp_b_id,
                    'd_name' => $department_name,
                    'd_ap_id' => $attendance_policy_data->ap_id,
                    'd_pst_id' => $shift_policy_data->pst_id,
                    'd_phl_id' => $holiday_policy_data->phl_id,
                    'd_pl_id' => $leave_policy_data->pl_id,
                    'd_pwo_id' => $weakly_policy_data->pwo_id,
                    'd_status' => 1,
                ]);

                $successfulCount++;
                $this->successRows[] = $row->toArray();
            } catch (\Exception $e) {
                $unsuccessfulCount++;
                $this->errorMessages[] = "Row " . ($key + 1) . ": Exception - " . $e->getMessage();
            }
        }

        // Return response with the results
        if (!empty($this->errorMessages)) {
            $this->errorMessages[] = "Successfully records imported count: {$successfulCount} & Unsuccessfully records imported count: {$unsuccessfulCount} ";
            return Excel::download(new ErrorExport($this->errorMessages), 'import_errors.xlsx');
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Import completed successfully!',
            'successful_count' => $successfulCount,  // Return successful count
            'unsuccessful_count' => $unsuccessfulCount,  // Return unsuccessful count
        ]);
    }

    public function getErrorMessages()
    {
        return $this->errorMessages;
    }

    public function getSuccessRows()
    {
        return $this->successRows;
    }
}
