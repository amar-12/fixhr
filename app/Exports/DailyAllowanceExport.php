<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DailyAllowanceExport implements FromCollection, WithColumnWidths, WithHeadings, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Adding empty rows for user input
        $exportData = [];
        for ($i = 1; $i <= 10; $i++) {
            $exportData[] = ['', '', '', '', '', '', '', ''];
        }

        return new Collection($exportData);
    }

    /**
     * Headings for the export.
     */
    public function headings(): array
    {
        return [
            'S. No. *',
            'Policy Category *',
            'Travel Type *',
            'Daily Allowance Type *',
            'Hours (Hour Wise)',
            'KM From (Distance Wise)',
            'KM To (Distance Wise)',
            'DA Amount *',
        ];
    }

    /**
     * Define column widths.
     */
    public function columnWidths(): array
    {
        return [
            'A' => 10,  // S. No.
            'B' => 17, // Policy Category
            'C' => 15, // Travel Type
            'D' => 25, // Daily Allowance Type
            'E' => 20, // Hours (Hour Wise)
            'F' => 25, // KM From (Distance Wise)
            'G' => 25, // KM To (Distance Wise)
            'H' => 15, // DA Amount
        ];
    }

    /**
     * Apply styles to the sheet.
     */
    public function styles(Worksheet $sheet)
    {
        // Make the heading row (first row) bold
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);

        return [];
    }
}
