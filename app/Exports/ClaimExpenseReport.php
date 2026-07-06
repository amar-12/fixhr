<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClaimExpenseReport implements FromCollection, WithHeadings, WithColumnWidths, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Adding empty rows for user input
        $exportData = [];
        for ($i = 1; $i <= 10; $i++) {
            $exportData[] = ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
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
            'Employee Code *',
            'Prefix *',
            'First Name *',
            'Middle Name',
            'Last Name *',
            'Contact Number *',
            'Email *',
            'Date Of Birth (DD-MM-YYYY) *',
            'Gender *',
            'Marital Status *',
            'Blood Group *',
            'Status *',
            'Employee Type *',
            'Date Of Joining (DD-MM-YYYY) *',
            'Job Status *',
            'Branch *',
            'Department *',
            'Designation *',
            'Grade *',
            'Role *',
            'Attendance Policy Name *',
            'Assign Attendance Mode *',
            'Assign Shift *',
            'Permanent Address *',
            'Permanent Pin Code *',
            'Temporary Address *',
            'Temporary Pin Code *',
            'Budget Code (SAP)*',
            'Account Code *',
            'PAN Number *'
        ];
    }

    /**
     * Define column widths.
     */
    public function columnWidths(): array
    {
        return [
            'A' => 10, // S. No.
            'B' => 17, // Employee Code
            'C' => 10, // Prefix
            'D' => 15, // First Name
            'E' => 15, // Middle Name
            'F' => 15, // Last Name
            'G' => 18, // Contact Number
            'H' => 25, // Email
            'I' => 30, // Date Of Birth
            'J' => 10, // Gender
            'K' => 15, // Marital Status
            'L' => 15, // Blood Group
            'M' => 10, // Status
            'N' => 17, // Employee Type
            'O' => 32, // Date Of Joining
            'P' => 13, // Job Status
            'Q' => 12, // Branch
            'R' => 20, // Department
            'S' => 20, // Designation
            'T' => 12, // Grade
            'U' => 12, // Role
            'V' => 27, // Attendance Policy
            'W' => 27, // Assign Attendance Mode
            'X' => 25, // Assign Shift
            'Y' => 25, // Permanent Address
            'Z' => 25, // Permanent Pin Code
            'AA' => 25, // Temporary Address
            'AB' => 25, // Temporary Pin Code
            'AC' => 17, // Budget Code
            'AD' => 17, // Account Code
            'AE' => 22, // PAN Number
        ];
    }

    /**
     * Apply styles to the sheet.
     */
    public function styles(Worksheet $sheet)
    {
        // Make the heading row (first row) bold
        $sheet->getStyle('A1:AE1')->getFont()->setBold(true);

        return [];
    }
}
