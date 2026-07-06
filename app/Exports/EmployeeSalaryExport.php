<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EmployeeSalaryExport implements FromCollection, WithHeadings
{
    protected $salary_allowances;

    public function __construct($salary_allowances)
    {
        // Extract only the sa_title values
        $this->salary_allowances = $salary_allowances->pluck('sa_title');
    }

    public function collection()
    {
        $employeeData = [
            'EMP001',       // Emp Code
            'Rajesh Kumar', // Emp Name
            50000,          // Monthly CTC
            600000,         // Annual CTC
        ];

        // Add dynamic allowance values (dummy values for now)
        foreach ($this->salary_allowances as $allowanceTitle) {
            $employeeData[] = 1000; // Replace with actual employee allowance if available
        }

        // Add remaining static fields
        $staticFields = [
            100,     // Other Allowance
            1800,  // Employee PF
            0,     // Employee ESIC
            10,    // Employee LWF
            1810,  // Employee Deduction
            1800,  // Employer PF
            0,     // Employer ESIC
            10,    // Employer LWF
            1810,  // Employer Deduction
            50000, // Total Earning
            600000,// Annual Gross
            48190, // Gross Pay
            46380, // Net Pay
            'INR', // Currency
            'G1',  // Salary Grade
            'Confirmed', // Remark
            '01-04-2025', // WEF
            'Yes', // PF Applicable
            'No',  // ESIC Applicable
        ];

        $employeeData = array_merge($employeeData, $staticFields);

        return collect([$employeeData]);
    }

    public function headings(): array
    {
        $headings = [
            'Emp Code',
            'Emp Name',
            'Monthly CTC',
            'Annual CTC',
        ];

        // Only sa_title as dynamic headings
        $headings = array_merge($headings, $this->salary_allowances->toArray());

        // Add remaining static headings
        $staticHeadings = [
            'Other Allowance',
            'Employee PF',
            'Employee ESIC',
            'Employee LWF',
            'Employee Deduction',
            'Employer PF',
            'Employer ESIC',
            'Employer LWF',
            'Employer Deduction',
            'Total Earning',
            'Annual Gross',
            'Gross Pay',
            'Net Pay',
            'Currency',
            'Salary Grade',
            'Remark',
            'WEF',
            'PF Applicable',
            'ESIC Applicable',
        ];

        return array_merge($headings, $staticHeadings);
    }
}
