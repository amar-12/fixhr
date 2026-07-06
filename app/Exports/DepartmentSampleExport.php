<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DepartmentSampleExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [
            [1, 'New Department', 'Business Attendance Policy', 'Business Shift Policy', 'Business Holiday Policy', 'My Leave Policy', 'New Weekly Policy'],
        ];
    }

    public function headings(): array
    {
        return [
            'S. No.*',
            'Department Name*',
            'Attendance Policy',
            'Shift Policy',
            'Holiday Policy',
            'Leave Policy',
            'Weekly Policy'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
