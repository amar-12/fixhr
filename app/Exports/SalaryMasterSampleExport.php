<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use App\Models\SalaryAllowance;


class SalaryMasterSampleExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{

    private $allowanceHeads = [];
    private $businessId;

    public function __construct($businessId)
    {
        $this->businessId = $businessId;
        $this->allowanceHeads = SalaryAllowance::where('business_id', $businessId)
            ->orderBy('id')
            ->pluck('name')
            ->toArray();

    }


    // public function headings(): array
    // {
    //     return [
    //         'EMP CODE', 'Name', 'Annual CTC', 'Monthly CTC', 'Gross Pay', 'ESIC Applicable', 'PF Applicable', 'Basic', 'HRA', 'Dearness Allowance',
    //         'Conveyance Allowance', 'Medical Allowance', 'Education Allowance', 'Special Allowance',
    //         'Other Allowance', 'Employee PF', 'Employee ESIC', 'Employee LWF', 'Employee Deduction',
    //         'Employer PF', 'Employer ESIC', 'Employer LWF', 'Employer Deduction', 'Total Earning',
    //         'Net Pay', 'WEF', 'Currency', 'Salary Grade', 'Remark'
    //     ];
    // }

     public function headings(): array
    {
        return array_merge([
            'EMP CODE', 'Name', 'Annual CTC', 'Monthly CTC', 'Gross Pay',
            'ESIC Applicable', 'PF Applicable',
        ], $this->allowanceHeads, [
            'Employee PF', 'Employee ESIC', 'Employee LWF', 'Employee Deduction',
            'Employer PF', 'Employer ESIC', 'Employer LWF', 'Employer Deduction',
            'Total Earning', 'Net Pay', 'WEF', 'Currency', 'Salary Grade', 'Remark'
        ]);
    }


    public function array(): array
    {
        return [
            [
                // 'EMP001', 360000, 30000, 28000, 'Yes', 'Yes', 360000, 10000, 8000, 2000, 1600, 1500, 1200, 3000, 2700,
                // 1800, 200, 1000, 1800, 1200, 300, 900, 25000, 26000, 'INR', 'A1', 'July Salary'
            ]
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:AA1')->getFont()->setBold(true);
        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 15,
            'C' => 15,
            'D' => 18,
            'E' => 18,
            'F' => 15,
            'G' => 12,
            'H' => 12,
            'I' => 20,
            'J' => 20,
            'K' => 20,
            'L' => 20,
            'M' => 20,
            'N' => 15,
            'O' => 15,
            'P' => 15,
            'Q' => 20,
            'R' => 15,
            'S' => 15,
            'T' => 15,
            'U' => 20,
            'V' => 15,
            'W' => 15,
            'X' => 15,
            'Y' => 15,
            'Z' => 12,
            'AA' => 12,
            'AA' => 12,
        ];
    }

    public function registerEvents(): array
    {
        return [
            \Maatwebsite\Excel\Events\AfterSheet::class => function (\Maatwebsite\Excel\Events\AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Ensure at least 100 rows for dropdowns/formulas even if sheet is empty
                $highestRow = max(500, $sheet->getHighestRow());

                // Apply styles (center + bold header row)
                for ($row = 1; $row <= $highestRow; $row++) {
                    $sheet->getStyle("A{$row}:AD{$row}")->applyFromArray([
                        'font' => ['bold' => $row === 1],
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'wrapText' => true,
                        ],
                    ]);
                }

                $dropdownCols = ['F', 'G'];
                foreach ($dropdownCols as $col) {
                    for ($i = 2; $i <= $highestRow; $i++) {
                    for ($i = 2; $i <= $highestRow; $i++) {
                        $cell = "{$col}{$i}";
                        $validation = $sheet->getCell($cell)->getDataValidation();
                        $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
                        $validation->setAllowBlank(true);
                        $validation->setShowInputMessage(true);
                        $validation->setShowErrorMessage(true);
                        $validation->setShowDropDown(true);
                        $validation->setFormula1('"Yes,No"');
                    }
                }

                // Column formulas for Annual CTC (C) and Monthly CTC (D)
                for ($i = 2; $i <= $highestRow; $i++) {
                    $sheet->setCellValue("C{$i}", '=IF(AND(D' . $i . '<>0,D' . $i . '<>""),D' . $i . '*12,"")');
                    $sheet->setCellValue("D{$i}", '=IF(AND(C' . $i . '<>0,C' . $i . '<>""),C' . $i . '/12,"")');
                }


            },
        ];
    }
}
