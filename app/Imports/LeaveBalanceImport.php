<?php

namespace App\Imports;

use App\Exports\ErrorExport;
use App\Models\LeaveBalance;
use App\Models\Employee;
use App\Models\MasterTable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Facades\Excel;

class LeaveBalanceImport implements ToCollection
{
    /**
     * Handle the collection of rows from the imported file.
     *
     * @param Collection $rows
     */

    public $errorMessages = []; // To store error messages
    protected $user;
    protected $leaveTypes = [];
    protected $leaveTypesParsed = false;

    public function __construct($user)
    {
        $this->user = $user;
    }
    public function collection(Collection $rows)
    {
        $successfulImports = [];
        $successfulCount = 0;  // Count for successful rows
        $unsuccessfulCount = 0;

        foreach ($rows as $row) {
            $row = $row->toArray(); // Ensure row is an array
            $row = array_map('trim', $row);

            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            // If leave types are not parsed yet, try to detect them from a row that contains the leave headers
            if (!$this->leaveTypesParsed) {
                $detected = false;
                for ($i = 3; $i < count($row); $i += 4) {
                    if (!empty($row[$i]) && preg_match('/\((.*?)\)/', $row[$i], $matches)) {
                        $this->leaveTypes[] = $matches[1];
                        $detected = true;
                    }
                }

                if ($detected) {
                    $this->leaveTypesParsed = true;
                    // this row is a leave-type header row, skip to next
                    continue;
                }
            }

            // Skip header row (like 'S .No.', 'Name', 'Emp ID') if it appears
            if ((isset($row[0]) && stripos($row[0], 'S') !== false && isset($row[1]) && strcasecmp($row[1], 'Name') === 0)
                || (isset($row[1]) && strcasecmp($row[1], 'Name') === 0 && isset($row[2]) && strcasecmp($row[2], 'Emp ID') === 0)
            ) {
                continue;
            }

            // If leave types are still not parsed by now, we cannot map leave columns — record error and skip
            if (empty($this->leaveTypes)) {
                $this->errorMessages[] = "Unable to detect leave types from header row. Import aborted or wrong format.";
                break;
            }

            $Name = $row[1] ?? null;

            $leaveData = [];
            foreach ($this->leaveTypes as $index => $type) {
                $offset = $index * 4 + 3;
                $leaveData[$type] = [
                    'alloted'          => (float) ($row[$offset] ?? 0),
                    'taken'            => (float) ($row[$offset + 1] ?? 0),
                    'remaining'        => (float) ($row[$offset + 2] ?? 0),
                    'carried_forward'  => (float) ($row[$offset + 3] ?? 0),
                ];
            }

            // Identify employee by emp_code in column index 2
            $empCode = isset($row[2]) ? (string) $row[2] : null;
            $employee = $empCode ? Employee::where(['emp_code' => $empCode, 'emp_b_id' => $this->user->emp_b_id])->first() : null;

            $rowErrors = [];
            if (!$employee) {
                $rowErrors[] = "Employee '{$Name}' (Emp ID: {$empCode}) not found in the system.";
            }

            foreach ($leaveData as $leaveType => $leaveValues) {
                // if (array_sum($leaveValues) === 0.0) continue;

                if (
                    ($row[$offset] ?? '') === '' &&
                    ($row[$offset + 1] ?? '') === '' &&
                    ($row[$offset + 2] ?? '') === '' &&
                    ($row[$offset + 3] ?? '') === ''
                ) {
                    continue;
                }

                $masterTable = MasterTable::where('m_group', 'LEAVE_CATEGORY')
                    ->where('m_type', $leaveType)
                    ->first();

                if (!$masterTable) {
                    $rowErrors[] = "Leave Type '{$leaveType}' not found.";
                }
            }

            if (!empty($rowErrors)) {
                $this->errorMessages[] = "Row for emp_code {$empCode} failed: " . implode(", ", $rowErrors);
                $unsuccessfulCount++;
                continue;
            }

            $employeeID = $employee->emp_id;
            $b_id = $employee->emp_b_id;

            foreach ($leaveData as $leaveType => $leaveValues) {
                // if (array_sum($leaveValues) === 0.0) continue;
                if (
                    ($row[$offset] ?? '') === '' &&
                    ($row[$offset + 1] ?? '') === '' &&
                    ($row[$offset + 2] ?? '') === '' &&
                    ($row[$offset + 3] ?? '') === ''
                ) {
                    continue;
                }

                $masterTable = MasterTable::where('m_group', 'LEAVE_CATEGORY')
                    ->where('m_type', $leaveType)
                    ->first();

                if (!$masterTable) {
                    // should not happen because of earlier check, but guard anyway
                    continue;
                }

                $data = [
                    'lb_b_id'                  => $b_id,
                    'lb_emp_id'                => $employeeID,
                    'lb_cat_type_id'           => $masterTable->m_id,
                    'lb_year'                  => date('Y'),
                    'lb_month'                 => date('m'),
                    'lb_alloted_leave'         => $leaveValues['alloted'],
                    'lb_taken_leave'           => $leaveValues['taken'],
                    'lb_balance_remaining_leave' => $leaveValues['remaining'],
                    'lb_carried_forward'       => $leaveValues['carried_forward'],
                    'created_at'               => now(),
                    'updated_at'               => now(),
                ];

                LeaveBalance::updateOrCreate(
                    [
                        'lb_emp_id' => $data['lb_emp_id'],
                        'lb_cat_type_id' => $data['lb_cat_type_id'],
                        'lb_year' => $data['lb_year'],
                        'lb_month' => $data['lb_month']
                    ],
                    $data
                );
            }

            $successfulImports[] = $row;
            $successfulCount++;
        }

        // Do not return responses or downloads from inside the import. The controller will read getErrorMessages().
    }

    public function getErrorMessages()
    {
        return $this->errorMessages;
    }

    // public function getRows()
    // {
    //     return $this->rows;
    // }
}
