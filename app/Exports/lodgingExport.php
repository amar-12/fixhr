<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LodgingExport implements FromCollection, WithColumnWidths, WithHeadings, WithStyles
{

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
            'City Type *',
            'S. Occupancy (Lodging With Bill) *',
            'D. Occupancy (Per Person)|(Lodging With Bill) *',
            'S. Occupancy (Lodging With Out Bill) *',
            'D. Occupancy (Per Person)|(Lodging With Out Bill) *',
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
            'D' => 22, // City Type
            'E' => 33, // S. Occupancy (Lodging With Bill)
            'F' => 45, // D. Occupancy (per person)|(Lodging With Bill)
            'G' => 37, // S. Occupancy (Lodging With Bill)
            'H' => 50, // D. Occupancy (Per Person)|(Lodging With Bill)
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
