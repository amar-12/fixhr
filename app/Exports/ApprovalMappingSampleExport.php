<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Models\Employee;
use App\Models\MasterTable;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Auth;

class ApprovalMappingSampleExport implements FromArray, WithHeadings, ShouldAutoSize, WithEvents
{
    public function headings(): array
    {
        return [
            'Employee Code', 
            'Approver 1 Code', 'Approver 1 Status',
            'Approver 2 Code', 'Approver 2 Status',
            'Approver 3 Code', 'Approver 3 Status',
            'Approver 4 Code', 'Approver 4 Status',
            'Approver 5 Code', 'Approver 5 Status'
        ];
    }

    public function array(): array
    {
        return [
            // Employee EMP001 has multiple approvers with different statuses
            ['EMP001', 'EMP010', 'Approved', 'EMP011', 'Pending', 'EMP012', 'Approved', '', '', '', ''],
            
            // Employee EMP002 has multiple approvers with different statuses
            ['EMP002', 'EMP010', 'Rejected', 'EMP013', 'Approved', 'EMP014', 'Pending', '', '', '', ''],
            
            // Employee EMP003 has multiple approvers with different statuses
            ['EMP003', 'EMP015', 'Approved', 'EMP016', 'Approved', 'EMP017', 'Pending', '', '', '', ''],
            
            // Employee EMP004 has multiple approvers with different statuses
            ['EMP004', 'EMP018', 'Rejected', 'EMP019', 'Approved', '', '', '', '', '', ''],
            
            // Employee EMP005 has only one approver
            ['EMP005', 'EMP020', 'Approved', '', '', '', '', '', '', '', ''],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $workbook = $sheet->getParent();
                // Use the business ID from the request if available, otherwise fallback to the first business found
                $user = Auth::user();
                $businessId = $user && isset($user->emp_b_id) ? $user->emp_b_id : Employee::query()->value('emp_b_id');
                // Fetch dropdown source data (as "CODE - NAME")
                $employees = Employee::where('emp_b_id', $businessId)
                    // ->orderBy('emp_full_name')
                    ->get();
                $employeeDisplay = [];
                foreach ($employees as $emp) {
                    if ($emp->emp_code || $emp->emp_role_id == 1) {
                        $name = $emp->emp_full_name ?? '';
                        $employeeDisplay[] = trim($emp->emp_code . ' - ' . $name);
                    }
                }
                $statusNames = MasterTable::where('m_group', 'APPROVAL_STATUS')
                    ->orderBy('m_id')
                    ->pluck('m_name')
                    ->filter()
                    ->values()
                    ->all();

                // Create hidden sheet with lists
                $listSheet = new Worksheet($workbook, 'Lists');
                $workbook->addSheet($listSheet);

                // Populate lists: A = Employees/Approvers, B = Statuses
                $rowIndex = 1;
                foreach ($employeeDisplay as $display) {
                    $listSheet->setCellValue('A' . $rowIndex, $display);
                    $rowIndex++;
                }

                $rowIndex = 1;
                foreach ($statusNames as $name) {
                    $listSheet->setCellValue('B' . $rowIndex, $name);
                    $rowIndex++;
                }

                // Hide list sheet
                $listSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

                // Define ranges
                $empCount = max(count($employeeDisplay), 1);
                $statusCount = max(count($statusNames), 1);
                $employeeRange = "=Lists!$" . 'A' . "$" . '1' . ":$" . 'A' . "$" . $empCount;
                $statusRange = "=Lists!$" . 'B' . "$" . '1' . ":$" . 'B' . "$" . $statusCount;

                // Apply validations for rows 2..1000
                $maxRow = 1000;
                $employeeCol = 'A';
                $approverCols = ['B','D','F','H','J'];
                $statusCols = ['C','E','G','I','K'];

                for ($row = 2; $row <= $maxRow; $row++) {
                    // Employee dropdown
                    $cell = $sheet->getCell($employeeCol . $row);
                    $dv = $cell->getDataValidation();
                    $dv->setType(DataValidation::TYPE_LIST);
                    $dv->setAllowBlank(true);
                    $dv->setShowDropDown(true);
                    $dv->setShowErrorMessage(true);
                    $dv->setErrorTitle('Invalid selection');
                    $dv->setError('Please select a value from the list.');
                    $dv->setFormula1($employeeRange);

                    // Approver dropdowns
                    foreach ($approverCols as $col) {
                        $cell = $sheet->getCell($col . $row);
                        $dv = $cell->getDataValidation();
                        $dv->setType(DataValidation::TYPE_LIST);
                        $dv->setAllowBlank(true);
                        $dv->setShowDropDown(true);
                        $dv->setShowErrorMessage(true);
                        $dv->setErrorTitle('Invalid selection');
                        $dv->setError('Please select a value from the list.');
                        $dv->setFormula1($employeeRange);
                    }

                    // Status dropdowns
                    foreach ($statusCols as $col) {
                        $cell = $sheet->getCell($col . $row);
                        $dv = $cell->getDataValidation();
                        $dv->setType(DataValidation::TYPE_LIST);
                        $dv->setAllowBlank(true);
                        $dv->setShowDropDown(true);
                        $dv->setShowErrorMessage(true);
                        $dv->setErrorTitle('Invalid selection');
                        $dv->setError('Please select a value from the list.');
                        $dv->setFormula1($statusRange);
                    }
                }
            }
        ];
    }
}



