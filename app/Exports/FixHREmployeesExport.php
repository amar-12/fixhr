<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FixHREmployeesExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        // Adjust this query based on your main FixHR database structure
        // Replace table name and column names as needed
        $query = \DB::table('employees') // Change 'employees' to your actual table name
            ->select([
                'employee_id',     // or 'emp_id', 'staff_id', etc.
                'full_name',       // or 'name', 'employee_name', etc.
                'email',
                'department',      // or 'dept', 'division', etc.
                'branch',          // or 'location', 'office', etc.
                'status',          // or 'is_active', 'employee_status', etc.
                'created_at'
            ])
            ->where('status', 'active'); // Adjust based on your status field

        // Apply filters
        if (!empty($this->filters['department'])) {
            $query->where('department', $this->filters['department']);
        }

        if (!empty($this->filters['branch'])) {
            $query->where('branch', $this->filters['branch']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['employee_type'])) {
            $query->where('employee_type', $this->filters['employee_type']);
        }

        return $query;
    }

    public function headings(): array
    {
        // These headings match the Asset Management import format
        return [
            'name',
            'email',
            'employee_id',
            'division',
            'branch'
        ];
    }

    public function map($employee): array
    {
        // Map your FixHR employee data to Asset Management format
        return [
            $employee->full_name ?? $employee->name,           // name
            $employee->email,                                  // email
            $employee->employee_id ?? $employee->emp_id,       // employee_id
            $this->mapDivision($employee->department),         // division
            $this->mapBranch($employee->branch)                // branch
        ];
    }

    /**
     * Map department names to division codes used in Asset Management
     */
    private function mapDivision($department)
    {
        $divisionMapping = [
            // Dealership Mappings
            'Lipl' => 'Lipl',
            'LIPL' => 'Lipl',
            'Lipl Dealership' => 'Lipl',
            'Ajax' => 'Ajax',
            'AJAX' => 'Ajax',
            'Ajax Dealership' => 'Ajax',
            'KTPL' => 'KTPL',
            'Ktpl' => 'KTPL',
            'KTPL Dealership' => 'KTPL',
            'Sndk' => 'Sndk',
            'SNDK' => 'Sndk',
            'Sndk Dealership' => 'Sndk',
            'FD' => 'FD',
            'Fd' => 'FD',
            'FD Dealership' => 'FD',
            'Wbco' => 'Wbco',
            'WBCO' => 'Wbco',
            'Wbco Dealership' => 'Wbco',
            // Legacy department names (if any)
            'Information Technology' => 'Lipl',
            'Human Resources' => 'Ajax',
            'Finance' => 'KTPL',
            'Operations' => 'Sndk',
            'Marketing' => 'FD',
            'Sales' => 'Wbco',
        ];

        return $divisionMapping[$department] ?? $department ?? 'Lipl';
    }

    /**
     * Map branch names to standardized branch names
     */
    private function mapBranch($branch)
    {
        $branchMapping = [
            // Head Office mappings
            'Head Office' => 'Raipur',
            'Main Office' => 'Raipur',
            'HO' => 'Raipur',
            'Raipur' => 'Raipur',
            'RAIPUR' => 'Raipur',
            
            // Branch mappings
            'Raigarh' => 'Raigarh',
            'RAIGARH' => 'Raigarh',
            'Branch 1' => 'Raigarh',
            
            'Ambikapur' => 'Ambikapur',
            'AMBIKAPUR' => 'Ambikapur',
            'Branch 2' => 'Ambikapur',
            
            'Jagdalpur' => 'Jagdalpur',
            'JAGDALPUR' => 'Jagdalpur',
            'Branch 3' => 'Jagdalpur',
            
            'Bilaspur' => 'Bilaspur',
            'BILASPUR' => 'Bilaspur',
            'Branch 4' => 'Bilaspur',
            
            'Korba' => 'Korba',
            'KORBA' => 'Korba',
            'Branch 5' => 'Korba',
        ];

        return $branchMapping[$branch] ?? $branch ?? 'Raipur';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}