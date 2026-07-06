<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;

class EmployeeDataExport implements FromCollection, WithStyles
{
    protected $user;
    protected $employees;

    public function __construct($user, $employees)
    {
        $this->user = $user;
        $this->employees = $employees ?? [];
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $exportData = [];

        // Add the additional information at the top
        $exportData[] = [' ', $this->user->fh_business->b_name];

        $tableHeaders = [
            'S. No.', 'Code', 'Prefix', 'Name', 'Contact', 'Email', 'DOB', 'Gender', 'Marital Status',
            'Branch', 'Blood Group', 'Stream', 'Qualification', 'Qualification Course Type', 'Specialization',
            'Nature of Course', 'Qualification Status', 'Institute Name', 'University Name', 'From Date',
            'To Date', 'Passing Date', 'Percentage', 'Grade', 'Duration of Course', 'Year', 'Permanent Address',
            'Longitude', 'Latitude', 'Zip Code', 'Temporary Address', 'Longitude', 'Latitude', 'Zip Code',
            'Aadhar Number', 'Driving License Number', 'Election Card Number', 'Passport Number',
            'Bank A/C Number', 'PAN Number', 'Account Code', 'IFSC Code', 'Bank Name', 'Branch Name', 'MICR',
            'Branch Code', 'ESIC Limit', 'Branch', 'Department', 'Designation', 'Grade', 'Role', 'Reporting Manager',
            'Budget Code (SAP)', 'Assign Attendance Mode', 'Assign Check In Method', 'Assign Shift', 'Assign Attendance Policy',
            'Assign Leave Policy', 'Joining Leave', 'Probation Leave', 'Status', 'Employee Type', 'Date Of Joining',
            'Job Status', 'Date Of Group Joining', 'Gratuity Start Date', 'Transfer Date', 'Expected Confirmation Date',
            'Probation Period In Days', 'Confirmation Date', 'Pay Structure Applied From Date', 'PF Trust Code',
            'Pension Fund Member', 'PF Number', 'Universal Account Number', 'VPF(%)', 'PF Date of Joining',
            'PF Date of Leaving', 'Reason of Leaving PF', 'ESIC Number', 'ESIC Dispensary', 'ESIC Date of Joining',
            'ESIC Date of Leaving', 'Reason of Leaving ESIC'
        ];

        $exportData[] = $tableHeaders;

         // Check if employees data is iterable or not empty
        if ($this->employees instanceof \Illuminate\Support\Collection && $this->employees->isEmpty()) {
            return collect($exportData); // Return just headers if no employees
        }

        $i = 1;
        foreach ($this->employees as $item) {

            $rowData = [
            'S. No.' => $i++,
            'Code' => $item->emp_code ?? '',
            'Prefix' => $item->emp_prefix ?? '',
            'Name' => $item->emp_full_name ?? '',
            'Contact' => $item->emp_phone ?? '',
            'Email'  => $item->emp_email ?? '',
            'DOB' => $item->emp_dob ?? '',
            'Gender' => $item->fh_gender->m_name ?? '',
            'Marital Status' => $item->fh_marital_status->m_name ?? '',
            'Branch' => $item->fh_branch->br_name ?? '',
            'Blood Group' => $item->fh_blood_group->m_name ?? '',
            'Stream' => $item->emp_stream ?? '',
            'Qualification' => '',
            'Qualification Course Type' => '',
            'Specialization' => '',
            'Nature of Course' => '',
            'Qualification Status' => '',
            'Institute Name' => '',
            'University Name' => '',
            'From Date' => '',
            'To Date' => '',
            'Passing Date' => '',
            'Percentage' => '',
            'Grade' => '',
            'Duration of Course' => '',
            'Year' => '',
            'Permanent Address' => $item->emp_permanent_address ?? '',
            'Longitude' => $item->emp_permanent_longitude ?? '',
            'Latitude' => $item->emp_permanent_latitude ?? '',
            'Zip Code' => $item->emp_permanent_pin_code  ?? '',
            'Temporary Address' => $item->emp_temporary_address ?? '',
            'Longitude' => $item->emp_temporary_longitude ?? '',
            'Latitude' => $item->emp_temporary_latitude ?? '',
            'Zip Code' => $item->emp_temporary_pin_code ?? '',
            'Aadhar Number' => $item->emp_temporary_pin_code ?? '',
            'Driving License Number' => '',
            'Election Card Number' => '',
            'Passport Number' => '',
            'Bank A/C Number' => $item->emp_bank_account_no ?? '',
            'PAN Number' => '',
            'Account Code' => $item->emp_bank_branch_code ?? '',
            'IFSC Code' => $item->emp_bank_ifsc_code ?? '',
            'Bank Name' => $item->emp_bank_name ?? '',
            'Branch Name' => $item->emp_bank_branch_name ?? '',
            'MICR' => $item->emp_bank_micr_code ?? '',
            'Branch Code' => $item->emp_bank_branch_code ?? '',
            'ESIC Limit' => $item->emp_esic_limit ?? '',
            'Branch' => $item->fh_branch->br_name ?? '',
            'Department' => $item->fh_department->d_name ?? '',
            'Designation' => $item->fh_designation->dg_name ?? '',
            'Grade' => $item->fh_grade->g_name ?? '',
            'Role' => $item->fh_role->role_name ?? '',
            'Reporting Manager' => $item->fh_reporting_manager ?? '',
            'Budget Code (SAP)' => $item->emp_sap_budget_code ?? '',
            'Assign Attendance Mode' => $item->fh_work_mode->m_name ?? '',
            'Assign Check In Method' => $item->emp_checkin_method_id ? implode(', ', $item->fh_checkin_method()->pluck('m_name')->toArray()) :'' ,
            'Assign Shift' => $item->fh_shift_type->pst_name ?? '',
            'Assign Attendance Policy' => $item->fh_attendance_policy->ap_name ?? '',
            'Assign Leave Policy' => $item->fh_policy_leave->pl_name ?? '',
            'Joining Leave' => $item->emp_allow_joining_leave ?? '',
            'Probation Leave' => $item->emp_allow_probation_leave ?? '',
            'Status' => $item->fh_employee_status->m_name ?? '',
            'Employee Type' => $item->fh_employee_type->m_name ?? '',
            'Date Of Joining' => $item->emp_date_of_joining ?? '',
            'Job Status' => $item->fh_job_status->m_name ?? '',
            'Date Of Group Joining' => $item->emp_group_date_of_joining ?? '',
            'Gratuity Start Date' => $item->emp_date_of_gratuity ?? '',
            'Transfer Date' => $item->emp_date_of_transfer ?? '',
            'Expected Confirmation Date' => $item->emp_date_of_expected_confirmation ?? '',
            'Probation Period In Days' => $item->emp_probation_period ?? '',
            'Confirmation Date' => $item->emp_date_of_confirmation ?? '',
            'Pay Structure Applied From Date' => $item->emp_date_of_pay_structure ?? '',
            'PF Trust Code' => $item->emp_pf_trust_code ?? '',
            'Pension Fund Member' => $item->emp_pf_found_member ?? '',
            'PF Number' => $item->emp_pf_no ?? '',
            'Universal Account Number' => $item->emp_pf_universal_ac_no ?? '',
            'VPF(%)' => '',
            'PF Date of Joining' => $item->emp_pf_joining_date ?? '',
            'PF Date of Leaving' => $item->emp_pf_leaving_date ?? '',
            'Reason of Leaving PF' => $item->emp_pr_leaving_reason ?? '',
            'ESIC Number' => $item->emp_esic_no ?? '',
            'ESIC Dispensary' => $item->emp_esic_dispensary ?? '',
            'ESIC Date of Joining' => $item->emp_esic_joining_date ?? '',
            'ESIC Date of Leaving' => $item->emp_esic_leaving_date ?? '',
            'Reason of Leaving ESIC' => $item->emp_esic_leaving_reason ?? '',
            ];

            $exportData[] = $rowData;
        }

        return collect($exportData);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,   // S. No.
            'B' => 17,   // Code
            'C' => 10,   // Prefix
            'D' => 15,   // Name (First Name)
            'E' => 15,   // Contact
            'F' => 25,   // Email
            'G' => 15,   // DOB
            'H' => 10,   // Gender
            'I' => 15,   // Marital Status
            'J' => 15,   // Branch
            'K' => 12,   // Blood Group
            'L' => 12,   // Stream
            'M' => 25,   // Qualification
            'N' => 30,   // Qualification Course Type
            'O' => 25,   // Specialization
            'P' => 30,   // Nature of Course
            'Q' => 28,   // Qualification Status
            'R' => 30,   // Institute Name
            'S' => 30,   // University Name
            'T' => 12,   // From Date
            'U' => 12,   // To Date
            'V' => 20,   // Passing Date
            'W' => 15,   // Percentage
            'X' => 10,   // Grade
            'Y' => 30,   // Duration of Course
            'Z' => 10,   // Year
            'AA' => 30,  // Permanent Address
            'AB' => 30,  // Longitude
            'AC' => 30,  // Latitude
            'AD' => 10,  // Zip Code
            'AE' => 30,  // Temporary Address
            'AF' => 30,  // Temporary Longitude
            'AG' => 30,  // Temporary Latitude
            'AH' => 30,  // Temporary Zip Code
            'AI' => 15,  // Aadhar Number
            'AJ' => 18,  // Driving License Number
            'AK' => 20,  // Election Card Number
            'AL' => 18,  // Passport Number
            'AM' => 25,  // Bank A/C Number
            'AN' => 20,  // PAN Number
            'AO' => 25,  // Account Code
            'AP' => 18,  // IFSC Code
            'AQ' => 20,  // Bank Name
            'AR' => 20,  // Branch Name
            'AS' => 12,  // MICR
            'AT' => 15,  // Branch Code
            'AU' => 15,  // ESIC Limit
            'AV' => 15,  // Branch
            'AW' => 20,  // Department
            'AX' => 20,  // Designation
            'AY' => 10,  // Grade
            'AZ' => 12,  // Role
            'BA' => 18,  // Reporting Manager
            'BB' => 15,  // Budget Code
            'BC' => 28,  // Assign Attendance Mode
            'BD' => 25,  // Assign Check In Method
            'BE' => 25,  // Assign Shift
            'BF' => 30,  // Assign Attendance Policy
            'BG' => 22,  // Assign Leave Policy
            'BH' => 15,  // Joining Leave
            'BI' => 15,  // Probation Leave
            'BJ' => 10,  // Status
            'BK' => 15,  // Employee Type
            'BL' => 20,  // Date Of Joining
            'BM' => 12,  // Job Status
            'BN' => 22,  // Date Of Group Joining
            'BO' => 20,  // Gratuity Start Date
            'BP' => 15,  // Transfer Date
            'BQ' => 30,  // Expected Confirmation Date
            'BR' => 25,  // Probation Period In Days
            'BS' => 20,  // Confirmation Date
            'BT' => 30,  // Pay Structure Applied From Date
            'BU' => 20,  // PF Trust Code
            'BV' => 22,  // Pension Fund Member
            'BW' => 18,  // PF Number
            'BX' => 30,  // Universal Account Number
            'BY' => 12,  // VPF(%)
            'BZ' => 18,  // PF Date of Joining
            'CA' => 18,  // PF Date of Leaving
            'CB' => 20,  // Reason of Leaving PF
            'CC' => 18,  // ESIC Number
            'CD' => 18,  // ESIC Dispensary
            'CE' => 22,  // ESIC Date of Joining
            'CF' => 22,  // ESIC Date of Leaving
            'CG' => 25,  // Reason of Leaving ESIC
        ];
    }


    public function backgroundColor()
    {

    }

    public function defaultStyles(Style $sheet)
    {
        $sheet = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_TOP,
            ],
            'font' => [
                'bold' => false,
                'size' => 10,
            ],
            'quotePrefix' => true
        ];

        return $sheet;
    }

    public function styles($sheet)
{
    $lastRow = $sheet->getHighestRow();
    $lastColumn = $sheet->getHighestColumn();

    // Freeze header rows
    $sheet->freezePane('A3');

    // Apply border to all rows
    $sheet->getStyle("A1:{$lastColumn}{$lastRow}")->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000'],
            ],
        ],
    ]);

    // Center alignment for headers
    $sheet->getStyle('A2:' . $lastColumn . '2')->applyFromArray([
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
        'font' => [
            'bold' => true,
            'size' => 10,
        ],
    ]);

    // Style for the row with business name at the top
    $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
        'font' => [
            'bold' => true,
            'size' => 14, // You can adjust the size as per requirement
            'color' => ['rgb' => '000000'], // Black color
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'FFFFFF'], // White background (default)
        ],
    ]);

    // Adjust column widths
    foreach (range('A', $lastColumn) as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }
}

}
