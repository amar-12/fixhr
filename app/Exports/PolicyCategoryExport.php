<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class PolicyCategoryExport implements FromCollection, WithColumnWidths, WithHeadings, WithStyles
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
            'Category Name *',
            'Grade *',
            'Department *',
            'Designation(Multiple) *',
            'Travel Type(Multiple) *',
            'Status *',
        ];
    }

    /**
     * Define column widths.
     */
    public function columnWidths(): array
    {
        return [
            'A' => 10,  // S. No.
            'B' => 25, // Category Name
            'C' => 20, // Grade
            'D' => 22, // Department
            'E' => 33, // Designation(Multiple)
            'F' => 45, // Travel Type(Multiple)
            'G' => 20, // Status
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
