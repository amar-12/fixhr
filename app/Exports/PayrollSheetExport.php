<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class PayrollSheetExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    protected $data, $businessName, $monthName, $mainHeading, $earningsHeading, $deductionsHeading, $earningsComponents, $deductionsComponents, $preEarningColumns, $postEarningColumns, $postDeductionColumns;

    public function __construct($data, $businessName, $monthName, $mainHeading, $earningsHeading, $deductionsHeading, $earningsComponents, $deductionsComponents, $preEarningColumns, $postEarningColumns, $postDeductionColumns)
    {
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
    }

    public function collection()
    {
        $data = collect($this->data);

        $grandTotals = [
            'S No.' => 'Grand Total',
            'Emp Status' => '',
            'Emp Code' => '',
            'Employee Name' => '',
        ];

        foreach (['Days In Month', 'Workable Days', 'Days Worked', 'Present Days', 'WeekOffs', 'UPL'] as $col) {
            $grandTotals[$col] = $data->sum($col);
        }

        foreach ($this->earningsComponents as $earning) {
            $grandTotals[$earning] = $data->sum($earning);
        }

        foreach ($this->postEarningColumns as $postEarning) {
            $grandTotals[$postEarning] = $data->sum($postEarning);
        }

        foreach ($this->deductionsComponents as $deduction) {
            $grandTotals[$deduction] = $data->sum($deduction);
        }

        foreach ($this->postDeductionColumns as $postDeduction) {
            $grandTotals[$postDeduction] = $data->sum($postDeduction);
        }

        $data->push($grandTotals);
        return $data;
    }

    public function headings(): array
    {
        $summaryColumnsCount = 6;
        $earningsCount = count($this->earningsComponents);
        $deductionsCount = count($this->deductionsComponents);
        $postEarningsCount = count($this->postEarningColumns);

        return [
            [$this->businessName],
            ["SALARY DETAIL FOR THE MONTH OF " . strtoupper($this->monthName)],
            [$this->mainHeading],

            array_merge(
                ['', '', ''],
                ['Summary'],
                array_fill(0, $summaryColumnsCount - 1, ''),

                ['Earning Components'],
                array_fill(0, $earningsCount - 1, ''),

                [''],
                array_fill(0, $postEarningsCount - 1, ''),

                ['Deduction Components'],
                array_fill(0, $deductionsCount - 1, ''),

                ['Net Pay']
            ),

            array_merge(
                ['S No.', 'Emp Status', 'Emp Code', 'Employee Name'],
                ['Days In Month', 'Workable Days', 'Days Worked', 'Present Days', 'WeekOffs', 'UPL'],
                $this->earningsComponents,
                $this->postEarningColumns,
                $this->deductionsComponents,
                ['Net Pay']
            )
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $summaryStart = 'D4';
        $summaryEnd = chr(ord('D') + 5) . '4';

        $earningsStart = chr(ord('D') + 6) . '4';
        $earningsEnd = chr(ord($earningsStart[0]) + count($this->earningsComponents) - 1) . '4';

        $postEarningsStart = chr(ord($earningsEnd[0]) + 1) . '4';
        $postEarningsEnd = chr(ord($postEarningsStart[0]) + count($this->postEarningColumns) - 1) . '4';

        $deductionsStart = chr(ord($postEarningsEnd[0]) + 1) . '4';
        $deductionsEnd = chr(ord($deductionsStart[0]) + count($this->deductionsComponents) - 1) . '4';

        $netPayStart = chr(ord($deductionsEnd[0]) + 1) . '4';

        $sheet->mergeCells("$summaryStart:$summaryEnd");
        $sheet->mergeCells("$earningsStart:$earningsEnd");
        $sheet->mergeCells("$postEarningsStart:$postEarningsEnd");
        $sheet->mergeCells("$deductionsStart:$deductionsEnd");
        $sheet->mergeCells("$netPayStart:$netPayStart");

        $sheet->freezePane('D6');

        $sheet->mergeCells('A1:D1');
        $sheet->mergeCells('A2:D2');
        $sheet->mergeCells('A3:D3');

        $sheet->getStyle('A1:C3')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);

        $sheet->getStyle('A1:C3')->getFont()->setBold(true);

        $totalRows = $sheet->getHighestRow();
        $highestColumn = Coordinate::stringFromColumnIndex(
            Coordinate::columnIndexFromString($sheet->getHighestColumn())
        );
        $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $cell = Coordinate::stringFromColumnIndex($col) . '5';
            $value = $sheet->getCell($cell)->getValue();

            if (strpos($value, ' ') !== false) {
                $formattedValue = wordwrap($value, 10, "\n", true);
                $sheet->setCellValue($cell, $formattedValue);
                $sheet->getStyle($cell)->getAlignment()->setWrapText(true);
            }

            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
        }

        $sheet->getStyle('A5:' . $highestColumn . '5')
            ->getAlignment()
            ->setWrapText(true)
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $columnLetter = Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($columnLetter)->setWidth(12);
            $sheet->getStyle($columnLetter . '5')->getAlignment()->setWrapText(true);
        }

        $sheet->getRowDimension(1)->setRowHeight(20);
        $sheet->getRowDimension(2)->setRowHeight(20);
        $sheet->getRowDimension(3)->setRowHeight(20);
        $sheet->getRowDimension(5)->setRowHeight(50);

        return [
            1 => ['font' => ['bold' => true, 'size' => 11], 'alignment' => ['horizontal' => 'center']],
            2 => ['font' => ['bold' => true, 'size' => 10], 'alignment' => ['horizontal' => 'center']],
            3 => ['font' => ['bold' => true, 'size' => 10], 'alignment' => ['horizontal' => 'center']],
            4 => [
                'font' => ['bold' => true, 'size' => 10],
                'alignment' => ['horizontal' => 'center'],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM]],
            ],
            5 => [
                'font' => ['bold' => true, 'size' => 8],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM]],
            ],
            $totalRows => ['font' => ['bold' => true, 'size' => 8]]
        ];
    }
}
