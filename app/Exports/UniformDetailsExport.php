<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UniformDetailsExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [
            [
                1,
                'Admin',
                'FD001',
                'Summer',
                'M',
                '32',
                '9',
                'Cap',
                'Medium',
                '2024-01-01',
                '2025-01-01',
                'Admin',
                'Issued on joining',
                'New',
                'uploads/uniforms/photo.jpg',
                'Navy Blue',
                'Light Blue',
                'Dark Grey',
                'Black',
                'Blue'
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'S.No.',
            'Employee Name',
            'Employee Code',
            'Uniform Type',
            'Shirt Size',
            'Pant Size',
            'Shoe Size',
            'Headgear Type',
            'Headgear Size',
            'Issue Date',
            'Replacement Due Date',
            'Issued By',
            'Remarks',
            'Status',
            'Photo Path',
            'Uniform Color',
            'Shirt Color',
            'Pant Color',
            'Shoe Color',
            'Headgear Color'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
