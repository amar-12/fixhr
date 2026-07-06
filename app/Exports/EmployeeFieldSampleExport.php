<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class EmployeeFieldSampleExport implements FromArray, WithHeadings, WithColumnFormatting
{
    /**
     * Sample rows
     */
    public function array(): array
    {
        return [
            [1, 'EMP001', '987654'],
            [2, 'EMP002', '912345'],
        ];
    }

    /**
     * Excel headings
     */
    public function headings(): array
    {
        return [
            'S.No',
            'Employee Code',
            'Field Data',
        ];
    }

    /**
     * Column format
     */
    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
        ];
    }
}
