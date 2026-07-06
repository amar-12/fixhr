<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OpeningBalanceSampleExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [
            [1, 'AB001', 2, 3, 0],
        ];
    }

    public function headings(): array
    {
        return [
            'S. No.*',
            'Emp Code*',
            'Casual Leave*',
            'Sick Leave*',
            'Earned Leave',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
