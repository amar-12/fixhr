<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RoleSampleExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [
            [1, ' HR', 'Hr Department'],
        ];
    }

    public function headings(): array
    {
        return [
            'S. No.*',
            'Role Name*',
            'Role Description*'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
