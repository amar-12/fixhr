<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GradeSampleExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [
            [1, 'L004'],
        ];
    }

    public function headings(): array
    {
        return [
            'S. No.*',
            'Grade Name*',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
