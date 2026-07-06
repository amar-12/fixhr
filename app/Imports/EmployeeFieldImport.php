<?php

namespace App\Imports;

use App\Models\Employee;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EmployeeFieldImport implements ToCollection, WithHeadingRow
{
    protected string $field;

    protected array $errors = [];

    protected array $summary = [
        'success' => 0,
        'skipped' => 0,
    ];

    public function __construct(string $field)
    {
        $this->field = $field;
    }

    /**
     * Handle Excel rows
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {

            $rowNumber = $index + 2; // +2 because heading row

            // Validate employee code
            if (empty($row['employee_code'])) {
                $this->addError($rowNumber, 'Employee Code is missing');
                $this->summary['skipped']++;
                continue;
            }

            $employee = Employee::where('emp_code', $row['employee_code'])->where('emp_status', 71)->first();
            if (!$employee) {
                $this->addError($rowNumber, 'Employee not found');
                $this->summary['skipped']++;
                continue;
            }

            // Field data empty
            if (!isset($row['field_data']) || $row['field_data'] === '') {
                $this->addError($rowNumber, 'Field Data is empty');
                $this->summary['skipped']++;
                continue;
            }

            try {
                // Update selected field
                $employee->{$this->field} = $row['field_data'];
                $employee->save();

                $this->summary['success']++;

            } catch (\Exception $e) {
                $this->addError($rowNumber, $e->getMessage());
                $this->summary['skipped']++;
            }
        }
    }

    /**
     * Store error messages
     */
    protected function addError(int $row, string $message): void
    {
        $this->errors[] = [
            'row'     => $row,
            'message' => $message,
        ];
    }

    /**
     * Get error list
     */
    public function getErrorMessages(): array
    {
        return $this->errors;
    }

    /**
     * Get import summary
     */
    public function getSummary(): array
    {
        return $this->summary;
    }
}
