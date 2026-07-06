<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class EmployeeErrorReportExport implements FromCollection
{
    protected $errors;

    public function __construct($errors)
    {
        $this->errors = $errors;
    }

    public function collection()
    {
        $exportData = []; // Initialize an empty array
        $i = 1;
        // Use foreach to loop over the errors array
        foreach ($this->errors as $error) {

            $rowData = [
                'S. No.' =>  $error['S. No.'], // Increment the index for 'S. No.'
                'Employee Code' => $error['Employee Code'] ?? '',
                'Prefix' => $error['Prefix'] ?? '',
                'First Name' => $error['First Name'] ?? '',
                'Middle Name' => $error['Middle Name'] ?? '',
                'Last Name' => $error['Last Name'] ?? '',
                'Contact Number' => $error['Contact Number'] ?? '',
                'Email' => $error['Email'] ?? '',
                'Date Of Birth (DD-MM-YYYY)' => $error['Date Of Birth (DD-MM-YYYY)'] ?? '',
                'Gender' => $error['Gender'] ?? '',
                'Marital Status' => $error['Marital Status'] ?? '',
                'Blood Group' => $error['Blood Group'] ?? '',
                'Status' => $error['Status'] ?? '',
                'Employee Type' => $error['Employee Type'] ?? '',
                'Date Of Joining (DD-MM-YYYY)' => $error['Date Of Joining (DD-MM-YYYY)'] ?? '',
                'Job Status' => $error['Job Status'] ?? '',
                'Branch' => $error['Branch'] ?? '',
                'Department' => $error['Department'] ?? '',
                'Designation' => $error['Designation'] ?? '',
                'Grade' => $error['Grade'] ?? '',
                'Role' => $error['Role'] ?? '',
                'Assign Attendance Mode' => $error['Assign Attendance Mode'] ?? '',
                'Assign Shift' => $error['Assign Shift'] ?? '',
                'Permanent Address' => $error['Permanent Address'] ?? '',
                'Permanent Pin Code' => $error['Permanent Pin Code'] ?? '',
                'Temporary Address' => $error['Temporary Address'] ?? '',
                'Temporary Pin Code' => $error['Temporary Pin Code'] ?? '',
                'Budget Code (SAP)' => $error['Budget Code'] ?? '',
                'Account Code' => $error['Account Code'] ?? '',
                'PAN Number' => $error['PAN Number'] ?? '',
                'Error Name' => $error['Error Name'] ?? '',
            ];

            $exportData[] = $rowData;
        }
        return collect($exportData);
    }
}
