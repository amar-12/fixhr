<?php

namespace App\Exports\Employee;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;

class EmployeeReport implements WithEvents, WithCustomStartCell
{
    protected $records;
    protected $filters;
    protected $detailsFilter;
    protected $categoryFields;
    protected $detailsFields;
    protected $relationFields;
    protected $relationToFilter;
    protected $businessName;
    protected $fileName;
    protected $fieldNameMapping;

    public function __construct(
        array $records,
        array $filters,
        array $detailsFilter,
        array $categoryFields,
        array $detailsFields,
        array $relationFields,
        array $relationToFilter,
        string $businessName
    ) {
        $this->records = $records;
        $this->filters = $filters;
        $this->detailsFilter = $detailsFilter;
        $this->categoryFields = $categoryFields;
        $this->detailsFields = $detailsFields;
        $this->relationFields = $relationFields;
        $this->relationToFilter = $relationToFilter;
        $this->businessName = $businessName;
        $this->fileName = 'Employee Report';

        // === EXACT FIELD NAME MAPPING – NO CHANGES, NO FALLBACKS ===
        $this->fieldNameMapping = [
            'aboutDetails' => [
                'prifix' => 'Prefix',
                'emp_fname' => 'Emp First Name',
                'emp_mname' => 'Emp Middle Name',
                'emp_lname' => 'Emp Last Name',
                'emp_full_name' => 'Emp Full Name',
                'emp_dob' => 'Date of Birth',
                'emp_nationality' => 'Nationality',
                'emp_religion' => 'Religion',
                'emp_body_mark' => 'Body Mark'
            ],
            'contactDetails' => [
                'emp_email' => 'Email',
                'emp_phone' => 'Phone',
                'emp_company_email' => 'Company Email',
                'emp_official_email' => 'Official Email',
                'emp_official_contact' => 'Official Contact',
                'emp_emergency_contact' => 'Emergency Contact',
                'emp_emergency_relation' => 'Emergency Relation',
                'emp_relative_name' => 'Relative Name',
                ' Hibernate_relationship' => 'Relationship',
                'emp_relative_phone_no' => 'Relative Phone Number'
            ],
            'personalDetails' => [
                'emp_permanent_address' => 'Permanent Address',
                'emp_permanent_pin_code' => 'Permanent Pin Code',
                'emp_temporary_address' => 'Temporary Address',
                'emp_temporary_pin_code' => 'Temporary Pin Code',
                'emp_permanent_latitude' => 'Permanent Latitude',
                'emp_permanent_longitude' => 'Permanent Longitude',
                'emp_temporary_latitude' => 'Temporary Latitude',
                'emp_temporary_longitude' => 'Temporary Longitude'
            ],
            'identityDetails' => [
                'gov_doc_type' => 'Government Document Type',
                'emp_gov_doc_type_number' => 'Government Document Number',
                'emp_pan_number' => 'PAN Number',
                'emp_pan_file' => 'PAN File',
                'emp_passport_number' => 'Passport Number',
                'emp_passport_file' => 'Passport File',
                'emp_passport_valid' => 'Passport Validity',
                'emp_voter_id_number' => 'Voter ID Number',
                'emp_voter_id_file' => 'Voter ID File',
                'emp_driving_license_number' => 'Driving License Number',
                'emp_driving_license_file' => 'Driving License File',
                'emp_drivng_license_valid' => 'Driving License Validity',
                'emp_aadhar_number' => 'Aadhar Number',
                'emp_aadhar_file' => 'Aadhar File',
                'emp_account_number' => 'Account Number',
                'emp_passbook_file' => 'Passbook File'
            ],
            'attendanceDetails' => [
                'emp_is_geofencing_active' => 'Geofencing Active',
                'emp_is_wifi_restricted' => 'WiFi Restricted',
                'emp_assign_shift_start_time' => 'Shift Start Time',
                'emp_assign_shift_end_time' => 'Shift End Time',
                'checkin_method' => 'Check-in Method',
                'attendance_preference' => 'Attendance Preference',
                'emp_imei_no' => 'IMEI Number'
            ],
            'organizationDetails' => [
                'emp_policy_tax' => 'Policy Tax',
                'leave_policy' => 'Leave Policy',
                'emp_wo_policy' => 'Week Off Policy',
                'emp_attendance_policy' => 'Attendance Policy',
                'emp_sap_budget_code' => 'SAP Budget Code',
                'emp_account_code' => 'Account Code',
                'emp_cost_center' => 'Cost Center',
                'approver_manager_1' => 'Approver Manager 1',
                'approver_manager_2' => 'Approver Manager 2',
                'emp_bank_ifsc_code' => 'Bank IFSC Code',
                'emp_bank_name' => 'Bank Name',
                'emp_bank_branch_name' => 'Bank Branch Name',
                'emp_bank_account_no' => 'Bank Account Number',
                'emp_bank_branch_code' => 'Bank Branch Code',
                'emp_bank_micr_code' => 'Bank MICR Code',
                'emp_bank_address_line1' => 'Bank Address Line 1',
                'emp_bank_address_line2' => 'Bank Address Line 2',
                'emp_salary_account_code' => 'Salary Account Code',
                'emp_salary_bank_ifsc_code' => 'Salary Bank IFSC Code',
                'emp_salary_bank_name' => 'Salary Bank Name',
                'emp_salary_bank_branch_name' => 'Salary Bank Branch Name',
                'emp_salary_bank_micr_code' => 'Salary Bank MICR Code',
                'emp_salary_bank_branch_code' => 'Salary Bank Branch Code',
                'emp_salary_bank_account_no' => 'Salary Bank Account Number',
                'emp_profit_center' => 'Profit Center',
                'emp_region' => 'Region',
                'emp_project' => 'Project',
                'emp_paymentmode' => 'Payment Mode',
                'emp_accountpurpose' => 'Account Purpose'
            ],
            'pfEsicDetails' => [
                'emp_is_pf_enabled' => 'PF Enabled',
                'emp_pf_no' => 'PF Number',
                'emp_pf_trust_code' => 'PF Trust Code',
                'emp_pf_found_member' => 'PF Found Member',
                'emp_pf_universal_ac_no' => 'PF Universal Account Number',
                'emp_pf_joining_date' => 'PF Joining Date',
                'emp_pf_leaving_date' => 'PF Leaving Date',
                'emp_pr_leaving_reason' => 'PF Leaving Reason',
                'emp_pf_joining_no' => 'PF Joining Number',
                'emp_lwf_no' => 'LWF Number',
                'emp_eps_no' => 'EPS Number',
                'emp_is_eps_enabled' => 'EPS Enabled',
                'emp_esic_no' => 'ESIC Number',
                'emp_esic_joining_date' => 'ESIC Joining Date',
                'emp_esic_leaving_date' => 'ESIC Leaving Date',
                'emp_esic_leaving_reason' => 'ESIC Leaving Reason',
                'emp_esic_dispensary' => 'ESIC Dispensary',
                'emp_esic_limit' => 'ESIC Limit',
                'emp_group_insured_by' => 'Group Insured By',
                'emp_group_insurance_no' => 'Group Insurance Number',
                'emp_group_insurance_start_date' => 'Group Insurance Start Date',
                'emp_group_insurance_till_date' => 'Group Insurance End Date',
                'emp_is_tds_enabled' => 'TDS Enabled',
                'emp_vpf_percentage' => 'VPF Percentage'
            ],
            'joiningDetails' => [
                'emp_date_of_joining' => 'Date of Joining',
                'emp_group_date_of_joining' => 'Group Date of Joining',
                'emp_date_of_gratuity' => 'Date of Gratuity',
                'emp_date_of_transfer' => 'Date of Transfer',
                'emp_date_of_expected_confirmation' => 'Expected Confirmation Date',
                'emp_date_of_confirmation' => 'Confirmation Date',
                'emp_date_of_pay_structure' => 'Pay Structure Date',
                'emp_probation_period' => 'Probation Period',
                'emp_probation_last_date' => 'Probation Last Date',
                'emp_year_of_service' => 'Years of Service',
                'emp_joining_leave_calc_type' => 'Joining Leave Calculation Type',
                'emp_joining_leave_before_date' => 'Joining Leave Before Date',
                'contractual_type' => 'Contractual Type'
            ],
            'separationDetails' => [
                'emp_retirement_date' => 'Retirement Date',
                'emp_separation_submit_date' => 'Separation Submit Date',
                'emp_expected_leaving_date' => 'Expected Leaving Date',
                'emp_leaving_date_as_per_notice_period' => 'Leaving Date per Notice Period',
                'emp_notice_period_req_days' => 'Notice Period Required Days',
                'emp_leaving_reason' => 'Leaving Reason',
                'emp_leave_date' => 'Leave Date',
                'emp_notice_period_serve_days' => 'Notice Period Served Days',
                'emp_settlement_from_date' => 'Settlement From Date',
                'emp_final_settlement_date' => 'Final Settlement Date',
                'emp_exit_interview_date' => 'Exit Interview Date',
                'emp_last_working_date' => 'Last Working Date',
                'emp_notice_period_shortfall_days' => 'Notice Period Shortfall Days',
                'emp_notice_period_day_for_employer' => 'Notice Period Days for Employer',
                'emp_notice_period_day_for_employee' => 'Notice Period Days for Employee',
                'emp_tada_settlement_amt' => 'TADA Settlement Amount'
            ],
            // === RELATION FIELDS – EXACT NAMES ===
            'fh_department' => ['d_name' => 'Department'],
            'fh_designation' => ['dg_name' => 'Designation'],
            'fh_dealership' => ['dlr_name' => 'Dealership'],
            'fh_branch' => ['br_name' => 'Branch'],
            'fh_job_status' => ['m_name' => 'Job Status'],
            'fh_grade' => ['g_name' => 'Grade'],
            'fh_shift_type' => ['pst_name' => 'Shift Type'],
            'fh_policy_leave' => ['pl_name' => 'Leave Policy'],
            'fh_employee_status' => ['m_name' => 'Employee Status'],
            'fh_employee_type' => ['m_name' => 'Employee Type'],
            'fh_work_mode' => ['m_name' => 'Work Mode'],
            'fh_gov_doc_type' => ['m_name' => 'Government Document Type'],
            'fh_attendance_preference' => ['m_name' => 'Attendance Preference'],
            'fh_contractual_type' => ['m_name' => 'Contractual Type'],
            'fh_employee_title' => ['m_name' => 'Prefix'],
            'fh_pf_master' => ['m_name' => 'ESIC Limit'],
            'fh_reporting_manager_id' => ['emp_full_name' => 'Supervisor'],
            'fh_week_off_policy' => ['pwo_name' => 'Week Off Policy'],
            'fh_attendance_policy' => ['pa_name' => 'Attendance Policy'],
            'policyTax' => ['pt_name' => 'Policy Tax'],
            'fh_geofencing' => ['m_name' => 'Geofencing Active'],
            'fh_project' => ['ps_name' => 'Project'],
            'fh_emp_region' => ['m_name' => 'Region']
        ];
    }

    public function startCell(): string
    {
        return 'A6';
    }

    private function columnLetter($index)
    {
        $letter = '';
        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $index = (int)(($index - $mod) / 26);
        }
        return $letter;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $dataStartRow = 6;
                $groupHeaderRow = 4;
                $subHeaderRow = 5;

                $sheet->setShowGridlines(false);

                // === HEADER ROWS (1–3) ===
                $printedOn = date('d-M-Y h:i A T');
                $sheet->setCellValue('A1', $this->businessName);
                $sheet->setCellValue('A2', $this->fileName);
                $sheet->setCellValue('A3', "Printed on: {$printedOn}");

                // === BUILD HEADERS TO KNOW LAST COLUMN ===
                $basicCols = ['A' => 'Sr', 'B' => 'Employee Name', 'C' => 'Emp Code'];
                $colIndex = 4;
                $dateColumns = [];
                $allCategoryFields = array_merge(...array_values($this->categoryFields));

                // Relation fields
                foreach ($this->relationFields as $relation => $fields) {
                    $filterKey = $this->relationToFilter[$relation] ?? null;
                    if ($filterKey && empty($this->filters[$filterKey])) continue;

                    foreach ($fields as $field => $alias) {
                        if (in_array($alias, $allCategoryFields)) continue;
                        $colLetter = $this->columnLetter($colIndex++);
                        $header = $this->fieldNameMapping[$relation][$field] ?? $alias; // Uses your exact name
                        $basicCols[$colLetter] = $header;
                        if (stripos($field, 'date') !== false || stripos($field, 'dob') !== false) {
                            $dateColumns[] = $colLetter;
                        }
                    }
                }

                // Write basic headers
                foreach ($basicCols as $col => $header) {
                    $sheet->setCellValue("{$col}{$groupHeaderRow}", $header);
                    $sheet->mergeCells("{$col}{$groupHeaderRow}:{$col}{$subHeaderRow}");
                }

                // Category fields
                $orderedCategories = [
                    'aboutDetails', 'contactDetails', 'personalDetails', 'identityDetails',
                    'organizationDetails', 'attendanceDetails', 'joiningDetails', 'pfEsicDetails', 'separationDetails'
                ];

                foreach ($orderedCategories as $category) {
                    if (empty($this->detailsFilter[$category]) || empty($this->categoryFields[$category])) continue;

                    $fields = $this->categoryFields[$category];
                    $startCol = $colIndex;
                    $endCol = $colIndex + count($fields) - 1;
                    $startLetter = $this->columnLetter($startCol);
                    $endLetter = $this->columnLetter($endCol);

                    $groupName = $this->detailsFields[$category] ?? ucfirst(str_replace('Details', '', $category));
                    $sheet->setCellValue("{$startLetter}{$groupHeaderRow}", $groupName);
                    $sheet->mergeCells("{$startLetter}{$groupHeaderRow}:{$endLetter}{$groupHeaderRow}");

                    foreach ($fields as $field) {
                        $colLetter = $this->columnLetter($colIndex++);
                        $header = $this->fieldNameMapping[$category][$field] ?? $field; // Uses your exact name
                        $sheet->setCellValue("{$colLetter}{$subHeaderRow}", $header);
                        if (stripos($field, 'date') !== false || stripos($field, 'dob') !== false) {
                            $dateColumns[] = $colLetter;
                        }
                    }
                }

                // === FINAL LAST COLUMN (only real columns) ===
                $lastCol = $this->columnLetter($colIndex - 1);

                // === HEADER ROWS (1–3) – OVERFLOW + AUTO HEIGHT ===
                foreach (range(1, 3) as $r) {
                    $cell = "A{$r}";
                    $value = $sheet->getCell($cell)->getValue();

                    $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
                    $sheet->getStyle($cell)
                        ->getFont()->setSize(11)->setBold(true);
                    $sheet->getStyle($cell)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setWrapText(false);

                    // Auto row height
                    $totalWidth = 0;
                    for ($c = 1; $c <= Coordinate::columnIndexFromString($lastCol); $c++) {
                        $col = $this->columnLetter($c);
                        $width = $sheet->getColumnDimension($col)->getWidth();
                        $totalWidth += $width * 7.5;
                    }
                    $textPixels = mb_strlen($value) * 1.2 * 11 / 10;
                    $lines = max(1, ceil($textPixels / $totalWidth));
                    $rowHeight = max(20, $lines * 16);
                    $sheet->getRowDimension($r)->setRowHeight($rowHeight);
                }

                $sheet->getStyle("A1:{$lastCol}3")->getFill()->setFillType(Fill::FILL_NONE);

                // === DATA POPULATION ===
                $row = 6;
                $serial = 1;
                foreach ($this->records as $record) {
                    $col = 1;
                    $sheet->setCellValue($this->columnLetter($col++) . $row, $serial++);
                    $sheet->setCellValueExplicit($this->columnLetter($col++) . $row, $record['emp_full_name'] ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $sheet->setCellValueExplicit($this->columnLetter($col++) . $row, $record['emp_code'] ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

                    foreach ($this->relationFields as $relation => $fields) {
                        $filterKey = $this->relationToFilter[$relation] ?? null;
                        if ($filterKey && empty($this->filters[$filterKey])) continue;
                        foreach ($fields as $field => $alias) {
                            if (in_array($alias, $allCategoryFields)) continue;
                            $value = $record[$alias] ?? '';
                            if (preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
                                try { $value = (new \DateTime($value))->format('d-M-Y'); } catch (\Exception $e) {}
                            }
                            $sheet->setCellValueExplicit($this->columnLetter($col++) . $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        }
                    }

                    foreach ($orderedCategories as $category) {
                        if (empty($this->detailsFilter[$category]) || empty($this->categoryFields[$category])) continue;
                        foreach ($this->categoryFields[$category] as $field) {
                            $value = $record[$field] ?? '';
                            if (preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
                                try { $value = (new \DateTime($value))->format('d-M-Y'); } catch (\Exception $e) {}
                            }
                            $sheet->setCellValueExplicit($this->columnLetter($col++) . $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                        }
                    }
                    $row++;
                }

                $highestRow = $sheet->getHighestRow();

                // === STYLING ===
                for ($r = $dataStartRow; $r <= $highestRow; $r++) {
                    $sheet->getRowDimension($r)->setRowHeight(25);
                }
                $sheet->getRowDimension($groupHeaderRow)->setRowHeight(30);
                $sheet->getRowDimension($subHeaderRow)->setRowHeight(25);

                $headerRange = "A{$groupHeaderRow}:{$lastCol}{$subHeaderRow}";
                $sheet->getStyle($headerRange)
                    ->getFont()->setSize(10)->setBold(true)->getColor()->setRGB('FFFFFF');
                $sheet->getStyle($headerRange)
                    ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('263871');
                $sheet->getStyle($headerRange)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);

                $dataRange = "A{$dataStartRow}:{$lastCol}{$highestRow}";
                $sheet->getStyle($dataRange)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);
                $sheet->getStyle($dataRange)->getFont()->setSize(9);

                $sheet->getStyle("A{$groupHeaderRow}:{$lastCol}{$highestRow}")
                    ->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D3D3D3');

                for ($r = $dataStartRow; $r <= $highestRow; $r++) {
                    $bg = (($r - $dataStartRow) % 2 == 0) ? 'F5F5F5' : 'FFFFFF';
                    $sheet->getStyle("A{$r}:{$lastCol}{$r}")
                        ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bg);
                }

                // === COLUMN WIDTHS ===
                $sheet->getColumnDimension('A')->setWidth(4);
                $sheet->getColumnDimension('B')->setWidth(13);
                $sheet->getColumnDimension('C')->setWidth(7);
                for ($i = 4; $i <= Coordinate::columnIndexFromString($lastCol); $i++) {
                    $col = $this->columnLetter($i);
                    $sheet->getColumnDimension($col)->setWidth(13);
                }

                foreach ($dateColumns as $col) {
                    $sheet->getStyle("{$col}6:{$col}{$highestRow}")
                        ->getNumberFormat()->setFormatCode('dd-mmm-yyyy');
                }

                $sheet->freezePane('D6');
            },
        ];
    }
}