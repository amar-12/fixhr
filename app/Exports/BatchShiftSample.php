<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class BatchShiftSample implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            [1, 'FD001', 'Tester1'],
            [2, 'FD002', 'Tester2'],
        ];
    }

    public function headings(): array
    {
        return [
            'S. No.*',
            'Emp Code*',
            'Emp Name',
        ];
    }
}
