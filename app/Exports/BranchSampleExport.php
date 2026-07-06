<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BranchSampleExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [
            [1, 'BR001', 'Head Office', 'headoffice@example.com', 'India', 'Chhattisgarh', 'Kolkata'],
        ];
    }

    public function headings(): array
    {
        return [
            'S. No.*',
            'Branch Code*',
            'Branch Name*',
            'Branch Email*',
            'Country*',
            'State*',
            'Branch Address*',
            'Range Limit in meter',
            'Is Active',
            'Wifi Address',
            'Is Wifi Restricted',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
