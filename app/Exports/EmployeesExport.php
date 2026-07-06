<?php
namespace App\Exports;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Grade;
use App\Models\MasterTable;
use App\Models\PolicyAttendance;
use App\Models\PolicyLeave;
use App\Models\PolicyShiftTiming;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeesExport implements FromArray, WithHeadings, WithStyles, WithEvents, ShouldAutoSize
{

    public function array(): array
    {
        return [
            [
                1,                           // S. No.
                'EMP001',                    // Employee Code
                'Mr.',                       // Prefix
                'Ravi',                      // First Name
                'Kumar',                     // Middle Name
                'Sharma',                    // Last Name
                '9876543210',                // Contact Number
                'ravi@example.com',          // Email
                '01/01/1990',                // Date Of Birth
                'Male',                      // Gender
                'Unmarried',                 // Marital Status
                'B+',                        // Blood Group
                'Kolkata',                   // Permanent Address
                '700001',                    // Permanent Pin Code
                'Delhi',                     // Temporary Address
                '110001',                    // Temporary Pin Code
                'Head Quarter',              // Branch
                'IT Department',             // Department
                'Developer',                 // Designation
                'L1',                        // Grade
                'IT Manager',                // Role
                'EMP001',                    // Reporting Manager (use Employee Code)
                '101',                       // Budget Code (SAP)
                'General Attendance Policy', // Assign Policy
                'Selfie,Biometric',          // Assign Check In Method
                'General Shift',             // Assign Shift
                'Office',                    // Assign Mode
                'Active',                    // Geofencing
                'Saturday-Sunday',           // Weekoff
                'Policy Based Calculation',  // Preference
                'Standard Leave Policy',     // Leave Assign Policy
                'Allowed',                   // Leave credit on pro-rata
                'Policy Based Calculation',  // Joining Leave Calculation Method
                'Allowed',                   // Probation leave on pro-rata
                'Active',                    // Status
                'Regular',                   // Employee Type
                '01/06/2025',                // Date Of Joining
                'Staff',                     // Job Status
                'Bank',                      // Payment Method
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'S. No. *',
            'Employee Code *',
            'Prefix *',
            'First Name *',
            'Middle Name',
            'Last Name',
            'Contact Number *',
            'Email *',
            'Date Of Birth (DD/MM/YYYY) *',
            'Gender *',
            'Marital Status *',
            'Blood Group *',
            'Permanent Address *',
            'Permanent Pin Code *',
            'Temporary Address *',
            'Temporary Pin Code *',
            'Branch *',
            'Department *',
            'Designation *',
            'Grade *',
            'Role *',
            'Reporting Manager *',
            'Budget Code (SAP)',
            'Assign Policy *',
            'Assign Check In Method *',
            'Assign Shift *',
            'Assign Mode *',
            'Geofencing *',
            'Weekoff *',
            'Preference *',
            'Leave Assign Policy *',
            'Leave credit on pro-rata *',
            'Joining Leave Calculation Method *',
            'Probation leave on pro-rata *',
            'Status *',
            'Employee Type *',
            'Date Of Joining (DD/MM/YYYY) *',
            'Job Status *',
            'Payment Method *',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet       = $event->sheet->getDelegate();
                $spreadsheet = $sheet->getParent();
                $user        = Auth::user();
                $businessId  = $user->emp_b_id;
                $maxRows     = 1000; // Adjust as needed

                // 🔹 STATIC DROPDOWNS (hardcoded)
                $staticDropdowns = [
                    'Geofencing *'                  => '"Active,Inactive"',
                    'Leave credit on pro-rata *'    => '"Allowed,Not Allowed"',
                    'Probation leave on pro-rata *' => '"Allowed,Not Allowed"',
                    'Payment Method *'              => '"bank,cash,cheque"',
                ];

                // Apply static dropdowns
                foreach ($this->headings() as $index => $heading) {
                    if (isset($staticDropdowns[$heading])) {
                        $column     = Coordinate::stringFromColumnIndex($index + 1);
                        $validation = $sheet->getCell("{$column}2")->getDataValidation();
                        $validation->setType(DataValidation::TYPE_LIST);
                        $validation->setAllowBlank(true);
                        $validation->setShowDropDown(true);
                        $validation->setFormula1($staticDropdowns[$heading]);
                        $sheet->setDataValidation("{$column}2:{$column}{$maxRows}", $validation);
                    }
                }

                $masterSheet = $spreadsheet->createSheet();
                $masterSheet->setTitle('master_data');

                // Fetch fresh master data
                $masterData = [
                    'branches'                 => Branch::where('br_b_id', $businessId)->pluck('br_name')->toArray(),
                    'departments'              => Department::where('d_b_id', $businessId)->pluck('d_name')->toArray(),
                    'designations'             => Designation::where('dg_b_id', $businessId)->pluck('dg_name')->toArray(),
                    'grades'                   => Grade::where('g_b_id', $businessId)->pluck('g_name')->toArray(),
                    'roles'                    => Role::where('role_b_id', $businessId)->pluck('role_name')->toArray(),
                    'shiftType'                => PolicyShiftTiming::where('pst_b_id', $businessId)->pluck('pst_name')->toArray(),
                    'leavePolicy'              => PolicyLeave::where('pl_b_id', $businessId)->pluck('pl_name')->toArray(),
                    'leaveCalcBy'              => MasterTable::where('m_group', 'LEAVE_CALCULATION_BY')->pluck('m_name')->toArray(),
                    'weekOffs'                 => $user->fh_business->fh_weekOff_policies?->pluck('pwo_name')->toArray(),
                    'attendancePreference'     => MasterTable::where('m_group', 'ATTENDANCE_PREFERENCE')->pluck('m_name')->toArray(),
                    'attendancePolicies'       => PolicyAttendance::where('ap_b_id', $user->emp_b_id)->where('ap_status', 1)->orderBy('ap_id', 'desc')->pluck('ap_name')->toArray(),
                    'employees'                => Employee::where('emp_b_id', $businessId)->pluck('emp_code')->toArray(),

                    'Prefix *'                 => MasterTable::where('m_group', 'PREFIX')->pluck('m_name')->toArray(),
                    'Gender *'                 => MasterTable::where('m_group', 'GENDER')->pluck('m_name')->toArray(),
                    'Marital Status *'         => MasterTable::where('m_group', 'MARITAL_STATUS')->pluck('m_name')->toArray(),
                    'Blood Group *'            => MasterTable::where('m_group', 'BLOOD_GROUP')->pluck('m_name')->toArray(),
                    'Assign Check In Method *' => MasterTable::where('m_group', 'CHECKIN_METHOD')->pluck('m_name')->toArray(),
                    'Assign Mode *'            => MasterTable::where('m_group', 'WORK_MODE')->pluck('m_name')->toArray(),
                    'Status *'                 => MasterTable::where('m_group', 'STATUS')->pluck('m_name')->toArray(),
                    'Employee Type *'          => MasterTable::where('m_group', 'EMPLOYEE_TYPE')->pluck('m_name')->toArray(),
                    'Job Status *'             => MasterTable::where('m_group', 'JOB_STATUS')->pluck('m_name')->toArray(),
                ];

                $ranges = [];
                $col    = 1;

                foreach ($masterData as $key => $values) {
                    if (empty($values)) {
                        continue;
                    }

                    $letter = Coordinate::stringFromColumnIndex($col);
                    $masterSheet->fromArray(array_map(fn($v) => [$v], $values), null, "{$letter}1");
                    $lastRow      = count($values);
                    $ranges[$key] = "'master_data'!{$letter}\$1:\${$letter}\${$lastRow}";
                    $col++;
                }

                $masterSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

                // 🔹 DYNAMIC DROPDOWNS (from master sheet)
                $dynamicDropdowns = [
                    'Branch *'                           => 'branches',
                    'Department *'                       => 'departments',
                    'Designation *'                      => 'designations',
                    'Grade *'                            => 'grades',
                    'Role *'                             => 'roles',
                    'Assign Shift *'                     => 'shiftType',
                    'Leave Assign Policy *'              => 'leavePolicy',
                    'Joining Leave Calculation Method *' => 'leaveCalcBy',
                    'Weekoff *'                          => 'weekOffs',
                    'Preference *'                       => 'attendancePreference',
                    'Assign Policy *'                    => 'attendancePolicies',
                    'Reporting Manager *'                => 'employees',

                    // Added master-table-based dropdowns
                    'Prefix *'                           => 'Prefix *',
                    'Gender *'                           => 'Gender *',
                    'Marital Status *'                   => 'Marital Status *',
                    'Blood Group *'                      => 'Blood Group *',
                    'Assign Check In Method *'           => 'Assign Check In Method *',
                    'Assign Mode *'                      => 'Assign Mode *',
                    'Status *'                           => 'Status *',
                    'Employee Type *'                    => 'Employee Type *',
                    'Job Status *'                       => 'Job Status *',
                ];

                foreach ($this->headings() as $index => $heading) {
                    if (! isset($dynamicDropdowns[$heading])) {
                        continue;
                    }

                    $key = $dynamicDropdowns[$heading];
                    if (! isset($ranges[$key])) {
                        continue;
                    }

                    $column     = Coordinate::stringFromColumnIndex($index + 1);
                    $validation = $sheet->getCell("{$column}2")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setAllowBlank(true);
                    $validation->setShowDropDown(true);
                    $validation->setFormula1('=' . $ranges[$key]);

                    // Apply to full column
                    $sheet->setDataValidation("{$column}2:{$column}{$maxRows}", $validation);
                }
            },
        ];
    }
}
