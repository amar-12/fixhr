<?php

namespace App\Imports;

use App\Models\EmployeeOpeningBalance;
use App\Models\LeaveBalance;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Exports\ErrorExport;
use Maatwebsite\Excel\Facades\Excel;

class OpeningBalanceImport implements ToCollection
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
            'Emp Code*',
            'Casual Leave*',
            'Sick Leave*',
            'Earned Leave',
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

            $emp_code    = trim($row[1]) ?? null;
            $casualLeave = floatval(trim($row[2]) ?? 0);
            $sickLeave   = floatval(trim($row[3]) ?? 0);
            $earnedLeave = floatval(trim($row[4]) ?? 0);

            // dd($emp_code, $casualLeave, $sickLeave, $earnedLeave);

            $rowErrors = [];

            if (!$emp_code) {
                $rowErrors[] = "Employee code is missing.";
            }

            $checkEmp = \App\Models\Employee::where('emp_code', $emp_code)
                ->where('emp_b_id', $this->user->emp_b_id)
                ->first();

            if (!$checkEmp) {
                $rowErrors[] = "Employee with code '{$emp_code}' not found.";
            }

            if (!empty($rowErrors)) {
                $unsuccessfulCount++;
                $this->errorMessages[] = "Row " . ($key + 1) . ": " . implode(' ', $rowErrors);
                continue;
            }

            try {
                $empId = $checkEmp->emp_id;
                $year = now()->year;
                $month = now()->month;

                \DB::beginTransaction();

                // Step 1: Fetch last opening balance
                $lastOpening = \App\Models\EmployeeOpeningBalance::where('eob_emp_id', $empId)
                    ->latest()
                    ->first();

                $previousLeaves = [
                    207 => $lastOpening->eob_cl ?? 0,
                    208 => $lastOpening->eob_sl ?? 0,
                    209 => $lastOpening->eob_el ?? 0,
                ];

                $newLeaves = [
                    207 => $casualLeave,
                    208 => $sickLeave,
                    209 => $earnedLeave,
                ];

                // Step 2: Update leave balances
                foreach ($newLeaves as $catId => $newValue) {
                    $oldValue = $previousLeaves[$catId];

                    $leaveBalance = \App\Models\LeaveBalance::firstOrNew([
                        'lb_b_id' => $this->user->emp_b_id,
                        'lb_emp_id' => $empId,
                        'lb_cat_type_id' => $catId,
                        'lb_year' => $year,
                        'lb_month' => $month,
                    ]);

                    $leaveBalance->lb_alloted_leave = max(($leaveBalance->lb_alloted_leave ?? 0) + $newValue, 0);
                    $leaveBalance->lb_balance_remaining_leave = max(($leaveBalance->lb_balance_remaining_leave ?? 0) + $newValue + $leaveBalance->lb_carried_forward, 0);

                    $taken = $leaveBalance->lb_taken_leave ?? 0;
                    // $leaveBalance->lb_carried_forward = max($leaveBalance->lb_balance_remaining_leave - $taken, 0);

                    $leaveBalance->save();
                }

                // Step 3: Save new opening balance
                \App\Models\EmployeeOpeningBalance::create([
                    'eob_b_id' => $this->user->emp_b_id,
                    'eob_emp_id' => $empId,
                    'eob_cl' => $casualLeave,
                    'eob_sl' => $sickLeave,
                    'eob_el' => $earnedLeave,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                \DB::commit();
                $successfulCount++;
                $this->successRows[] = $row->toArray();

            } catch (\Exception $e) {
                \DB::rollBack();
                $unsuccessfulCount++;
                $this->errorMessages[] = "Row " . ($key + 1) . ": Exception - " . $e->getMessage();
            }
        }

        if (!empty($unsuccessfulCount)) {
            $this->errorMessages[] = "Successfully imported: {$successfulCount} | Failed: {$unsuccessfulCount}";
        } else {
            $this->errorMessages[] = $unsuccessfulCount;
        }
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
