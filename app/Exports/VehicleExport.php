<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VehicleExport implements FromCollection, WithColumnWidths, WithHeadings, WithStyles
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
            'Travel Type *',
            'Travel Mode *',
            "Travel Vehicle *",
            'Claim Type *',
            "Policy Category *",
            "Vehicle Class",
            'Eligibility',
            'Conveyance *',
        ];
    }

    /**
     * Define column widths.
     */
    public function columnWidths(): array
    {
        return [
            'A' => 10, // S. No.
            'B' => 20, // Travel Type
            'C' => 20, // Travel Mode
            'D' => 20, // Travel Vehicle
            'E' => 15, // Claim Type
            'F' => 20, // Policy Category
            'G' => 20, // Vehicle Class
            'H' => 15, // Eligibility
            'I' => 15, // Conveyance
        ];
    }

    /**
     * Apply styles to the sheet.
     */
    public function styles(Worksheet $sheet)
    {
        // Make the heading row (first row) bold
        $sheet->getStyle('A1:I1')->getFont()->setBold(true);

        return [];
    }
}
