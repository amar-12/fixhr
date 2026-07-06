<?php

namespace App\Exports\Salary;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class EcrExport implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    protected $data;
    protected $payrollPeriod;

    public function __construct($data, $payrollPeriod = null)
    {
        $this->data = $data;
        $this->payrollPeriod = $payrollPeriod;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'UAN',
            'NAME',
            'GROSS_WAGES',
            'EPF_WAGES',
            'EPS_WAGES',
            'EDLI_WAGES',
            'EPF_CONTRIBUTION_REMITTED',
            'EPS_CONTRIBUTION_REMITTED',
            'DIFF_EPF_EPS_CONTRIBUTION_REMITTED',
            'NCP_DAYS',
            'REFUND_OF_ADVANCES'
        ];
    }

    public function title(): string
    {
        return 'EPF ECR FORMAT';
    }

    public function styles(Worksheet $sheet)
    {
        $totalRows = $this->data->count() + 1;

        // Header style
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['rgb' => 'DC2626'] // Red color matching card
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        // Data rows style
        if ($totalRows > 1) {
            $dataRange = "A2:K{$totalRows}";

            $sheet->getStyle($dataRange)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'D3D3D3']
                    ]
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ]
            ]);

            // Align specific columns
            $sheet->getStyle("A2:B{$totalRows}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
            ]);

            $sheet->getStyle("C2:I{$totalRows}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
            ]);

            $sheet->getStyle("J2:J{$totalRows}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);

            $sheet->getStyle("K2:K{$totalRows}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
            ]);

            // Number formatting for currency columns
            $currencyColumns = ['C', 'D', 'E', 'F', 'G', 'H', 'I', 'K'];
            foreach ($currencyColumns as $col) {
                $sheet->getStyle("{$col}2:{$col}{$totalRows}")
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            }

            // Set row heights
            for ($i = 1; $i <= $totalRows; $i++) {
                $sheet->getRowDimension($i)->setRowHeight(20);
            }

            // Auto filter
            $sheet->setAutoFilter('A1:K1');
        }

        // Freeze header row
        $sheet->freezePane('A2');

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20, // UAN
            'B' => 30, // NAME
            'C' => 15, // GROSS_WAGES
            'D' => 12, // EPF_WAGES
            'E' => 12, // EPS_WAGES
            'F' => 12, // EDLI_WAGES
            'G' => 20, // EPF_CONTRIBUTION_REMITTED
            'H' => 20, // EPS_CONTRIBUTION_REMITTED
            'I' => 25, // DIFF_EPF_EPS_CONTRIBUTION_REMITTED
            'J' => 10, // NCP_DAYS
            'K' => 18, // REFUND_OF_ADVANCES
        ];
    }
}
