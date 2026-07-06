<?php
namespace App\Exports\Attendance;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class PayrollReportExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    protected $data;
    protected $businessName;
    protected $monthName;
    protected $mainHeading;
    protected $earningsHeading;
    protected $deductionsHeading;
    protected $earningsComponents;
    protected $deductionsComponents;
    protected $preEarningColumns;
    protected $postEarningColumns;
    protected $postDeductionColumns;
    protected $employeeSalaryEarningComponents;
    protected $employeeSalaryDeductionComponents;

    public function __construct(
        $data,
        $businessName,
        $monthName,
        $mainHeading,
        $earningsHeading,
        $deductionsHeading,
        $employeeSalaryEarningComponents,
        $employeeSalaryDeductionComponents,
        $earningsComponents,
        $deductionsComponents,
        $preEarningColumns,
        $postEarningColumns,
        $postDeductionColumns
    ) {
        $this->data = $data;
        $this->businessName = $businessName;
        $this->monthName = $monthName;
        $this->mainHeading = $mainHeading;
        $this->earningsHeading = $earningsHeading;
        $this->deductionsHeading = $deductionsHeading;
        $this->earningsComponents = $earningsComponents;
        $this->deductionsComponents = $deductionsComponents;
        $this->preEarningColumns = $preEarningColumns;
        $this->postEarningColumns = $postEarningColumns;
        $this->postDeductionColumns = $postDeductionColumns;
        $this->employeeSalaryEarningComponents = $employeeSalaryEarningComponents;
        $this->employeeSalaryDeductionComponents = $employeeSalaryDeductionComponents;
    }

    public function collection()
    {
        $data = collect($this->data);
        
        // Calculate grand totals
        $grandTotals = [
            'S No.' => 'Grand Total',
            'Emp Status' => '',
            'Emp Code' => '',
            'Employee Name' => '',
        ];

        // Summary columns totals
        $summaryColumns = ['Days In Month', 'Workable Days', 'Days Worked', 'Present Days', 'WeekOffs', 'UPL'];
        foreach ($summaryColumns as $col) {
            $grandTotals[$col] = $data->sum($col);
        }

        // Employee salary components totals
        foreach ($this->employeeSalaryEarningComponents as $col) {
            $grandTotals[$col] = $data->sum($col);
        }

        foreach ($this->employeeSalaryDeductionComponents as $col) {
            $grandTotals[$col] = $data->sum($col);
        }

        // Processed components totals
        foreach ($this->earningsComponents as $earning) {
            $grandTotals[$earning] = $data->sum($earning);
        }

        foreach ($this->postEarningColumns as $col) {
            $grandTotals[$col] = $data->sum($col);
        }

        foreach ($this->deductionsComponents as $deduction) {
            $grandTotals[$deduction] = $data->sum($deduction);
        }

        foreach ($this->postDeductionColumns as $col) {
            $grandTotals[$col] = $data->sum($col);
        }

        // Add grand totals row
        $data->push($grandTotals);

        return $data;
    }

    public function headings(): array
    {
        $headings = [
            [$this->businessName],
            ["SALARY DETAIL FOR THE MONTH OF " . strtoupper($this->monthName)],
            [$this->mainHeading],
        ];

        // Calculate column positions
        $summaryCols = count($this->preEarningColumns);
        $empEarningsCols = count($this->employeeSalaryEarningComponents);
        $empDeductionsCols = count($this->employeeSalaryDeductionComponents);
        $earningsCols = count($this->earningsComponents);
        $postEarningsCols = count($this->postEarningColumns);
        $deductionsCols = count($this->deductionsComponents);
        $postDeductionsCols = count($this->postDeductionColumns);

        // Create component headers row
        $componentHeaders = array_fill(0, $summaryCols + $empEarningsCols + $empDeductionsCols + $earningsCols + $postEarningsCols + $deductionsCols + $postDeductionsCols, '');
        
        // Set the component headers
        $componentHeaders[0] = 'Summary';
        $componentHeaders[$summaryCols] = 'Employee Earnings';
        $componentHeaders[$summaryCols + $empEarningsCols] = 'Employee Deductions';
        $componentHeaders[$summaryCols + $empEarningsCols + $empDeductionsCols] = 'Processed Earnings';
        $componentHeaders[$summaryCols + $empEarningsCols + $empDeductionsCols + $earningsCols + $postEarningsCols] = 'Processed Deductions';
        $componentHeaders[count($componentHeaders) - 1] = 'Net Pay';
        
        $headings[] = $componentHeaders;

        // Add the actual column headers row
        $columnHeaders = array_merge(
            $this->preEarningColumns,
            $this->employeeSalaryEarningComponents,
            $this->employeeSalaryDeductionComponents,
            $this->earningsComponents,
            $this->postEarningColumns,
            $this->deductionsComponents,
            $this->postDeductionColumns
        );

        $headings[] = $columnHeaders;

        return $headings;
    }

    public function styles(Worksheet $sheet)
    {
        $safeMerge = function ($sheet, $start, $end) {
            if ($start !== $end) {
                $sheet->mergeCells("$start:$end");
            }
        };

        // Merge business name, month, and main heading
        $highestColumn = $sheet->getHighestColumn();
        $sheet->mergeCells('A1:' . $highestColumn . '1');
        $sheet->mergeCells('A2:' . $highestColumn . '2');
        $sheet->mergeCells('A3:' . $highestColumn . '3');

        // Calculate column positions for merging
        $summaryCols = count($this->preEarningColumns);
        $empEarningsCols = count($this->employeeSalaryEarningComponents);
        $empDeductionsCols = count($this->employeeSalaryDeductionComponents);
        $earningsCols = count($this->earningsComponents);
        $postEarningsCols = count($this->postEarningColumns);
        $deductionsCols = count($this->deductionsComponents);

        // Merge summary columns (A4-J4)
        $summaryStart = 'A4';
        $summaryEnd = Coordinate::stringFromColumnIndex($summaryCols) . '4';
        $safeMerge($sheet, $summaryStart, $summaryEnd);

        // Merge employee earnings components
        if ($empEarningsCols > 0) {
            $empEarningsStart = Coordinate::stringFromColumnIndex($summaryCols + 1) . '4';
            $empEarningsEnd = Coordinate::stringFromColumnIndex($summaryCols + $empEarningsCols) . '4';
            $safeMerge($sheet, $empEarningsStart, $empEarningsEnd);
        }

        // Merge employee deductions components
        if ($empDeductionsCols > 0) {
            $empDeductionsStart = Coordinate::stringFromColumnIndex($summaryCols + $empEarningsCols + 1) . '4';
            $empDeductionsEnd = Coordinate::stringFromColumnIndex($summaryCols + $empEarningsCols + $empDeductionsCols) . '4';
            $safeMerge($sheet, $empDeductionsStart, $empDeductionsEnd);
        }

        // Merge processed earning components
        if ($earningsCols > 0) {
            $earningsStart = Coordinate::stringFromColumnIndex($summaryCols + $empEarningsCols + $empDeductionsCols + 1) . '4';
            $earningsEnd = Coordinate::stringFromColumnIndex($summaryCols + $empEarningsCols + $empDeductionsCols + $earningsCols) . '4';
            $safeMerge($sheet, $earningsStart, $earningsEnd);
        }

        // Merge processed deduction components
        if ($deductionsCols > 0) {
            $deductionsStart = Coordinate::stringFromColumnIndex($summaryCols + $empEarningsCols + $empDeductionsCols + $earningsCols + $postEarningsCols + 1) . '4';
            $deductionsEnd = Coordinate::stringFromColumnIndex($summaryCols + $empEarningsCols + $empDeductionsCols + $earningsCols + $postEarningsCols + $deductionsCols) . '4';
            $safeMerge($sheet, $deductionsStart, $deductionsEnd);
        }

        // Style the headers
        $sheet->getStyle('A1:' . $highestColumn . '3')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
        
        $sheet->getStyle('A1:' . $highestColumn . '3')->getFont()->setBold(true);

        // Style the component headers row (row 4)
        $sheet->getStyle('A4:' . $highestColumn . '4')
            ->getFont()->setBold(true)
            ->setSize(10);
        
        $sheet->getStyle('A4:' . $highestColumn . '4')
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        // Style the column headers row (row 5)
        $sheet->getStyle('A5:' . $highestColumn . '5')
            ->getFont()->setBold(true)
            ->setSize(8);
        
        $sheet->getStyle('A5:' . $highestColumn . '5')
            ->getAlignment()
            ->setWrapText(true)
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        // Set row heights
        $sheet->getRowDimension(1)->setRowHeight(20);
        $sheet->getRowDimension(2)->setRowHeight(20);
        $sheet->getRowDimension(3)->setRowHeight(20);
        $sheet->getRowDimension(4)->setRowHeight(20);
        $sheet->getRowDimension(5)->setRowHeight(50);

        // Freeze the header rows
        $sheet->freezePane('A6');

        // Auto-size columns and wrap text
        for ($col = 1; $col <= Coordinate::columnIndexFromString($highestColumn); $col++) {
            $columnLetter = Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
            
            // Wrap text for column headers
            $cell = $columnLetter . '5';
            $value = $sheet->getCell($cell)->getValue();
            
            if (strpos($value, ' ') !== false) {
                $formattedValue = wordwrap($value, 10, "\n", true);
                $sheet->setCellValue($cell, $formattedValue);
                $sheet->getStyle($cell)->getAlignment()->setWrapText(true);
            }
        }

        // Style for grand total row
        $totalRows = $sheet->getHighestRow();
        $sheet->getStyle('A' . $totalRows . ':' . $highestColumn . $totalRows)
            ->getFont()->setBold(true)
            ->setSize(8);

        // Add number formatting for monetary values
        $dataStartRow = 6;
        $dataEndRow = $totalRows - 1; // Exclude grand total row
        
        // Format all numeric columns (skip first 4 columns which are text)
        for ($col = 5; $col <= Coordinate::columnIndexFromString($highestColumn); $col++) {
            $columnLetter = Coordinate::stringFromColumnIndex($col);
            $sheet->getStyle($columnLetter . $dataStartRow . ':' . $columnLetter . $dataEndRow)
                ->getNumberFormat()
                ->setFormatCode('#,##0.00');
        }

        return [];
    }
}