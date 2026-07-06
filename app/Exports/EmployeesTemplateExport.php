<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeesTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [
            ['John Doe', 'john.doe@company.com', 'EMP001', 'Lipl', 'Raipur'],
            ['Jane Smith', 'jane.smith@company.com', 'EMP002', 'Ajax', 'Raigarh'],
            ['Mike Johnson', 'mike.johnson@company.com', 'EMP003', 'KTPL', 'Ambikapur'],
        ];
    }

    public function headings(): array
    {
        return [
            'name',
            'email', 
            'employee_id',
            'division',
            'branch'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}