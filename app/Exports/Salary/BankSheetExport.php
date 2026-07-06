<?php

namespace App\Exports\Salary;

use NumberToWords\NumberToWords;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Style;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class BankSheetExport implements FromCollection, WithStyles, WithTitle
{
    protected $data, $user, $amountCheck, $businessId, $business, $payrollName;

    public function __construct($data, $user, $amountCheck, $businessId, $business, $payrollName)
    {
        $this->data = $data;
        $this->user = $user;
        $this->amountCheck = $amountCheck;
        $this->businessId = $businessId;
        $this->payrollName = $payrollName;
        $this->business = $business;
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function collection()
    {
        $exportData = [];

        // Row 1: To + Date
        $exportData[] = ['To', '', '', '', date('d-M-Y')];
        $exportData[] = [$this->business->b_bank_name];
        $exportData[] = [$this->business->b_bank_address];
        $exportData[] = [' '];
        $exportData[] = ['Sub: Kindly Process Salary for the month of ' . $this->payrollName . ' Vide No. ' . $this->business->b_cheque_no];
        $exportData[] = [' '];

        // Table Header
        $exportData[] = ['S#', 'Emp Code', 'Employee Name', 'Account Number', 'Amount'];

        $i = 1;
        $grandTotal = 0;

        foreach ($this->data as $employee) {
            $processed = $employee->processedSalaries->first();

            $accountNo = '';
            if (!empty($employee->emp_documents_ref_file)) {
                $accountData = json_decode($employee->emp_documents_ref_file, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($accountData['account_number'])) {
                    $accountNo = $accountData['account_number'];
                }
            }
            if (empty($accountNo)) {
                $accountNo = $employee->emp_bank_account_no ?? '';
            }

            $amount = isset($processed) && is_numeric($processed->ps_monthly_net_salary)
                ? (int) round((float) $processed->ps_monthly_net_salary)
                : 0;

            $exportData[] = [
                $i++,
                $employee->emp_code ?? '',
                $employee->emp_full_name ?? '',
                preg_match('/^\d+$/', $accountNo) ? "'$accountNo" : $accountNo,
                number_format($amount, 0, '.', ','),
            ];

            $grandTotal += $amount;
        }

        $numberToWords = new NumberToWords();
        $words = ucfirst($numberToWords->getNumberTransformer('en')->toWords($grandTotal));
        $exportData[] = [' ', ' ', ' ', 'Grand Total', number_format($grandTotal, 0, '.', ',')];
        $exportData[] = [' '];
        $exportData[] = ['In Words: ' . $words];
        $exportData[] = [' '];
        $exportData[] = [' '];
        $exportData[] = ['Thanking You'];
        $exportData[] = [' '];
        $exportData[] = [$this->business->b_name];

        return collect($exportData);
    }

    public function defaultStyles(Style $sheet)
    {
        return [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'bold' => false,
                'size' => 10,
            ],
            'quotePrefix' => true
        ];
    }

   public function styles($sheet)
{
    $lastRow        = $sheet->getHighestRow();
    $grandTotalRow  = $lastRow - 7;

    // <<< NEW LINE >>>
    $sheet->setShowGridlines(false);
    // <<< END NEW LINE >>>

    // Freeze below header
    $sheet->freezePane('A8');

    // === COLUMN WIDTHS ===
    $sheet->getColumnDimension('A')->setWidth(9);   // S#
    $sheet->getColumnDimension('B')->setWidth(10);  // Emp Code
    $sheet->getColumnDimension('C')->setWidth(13);  // Employee Name
    $sheet->getColumnDimension('D')->setWidth(13);  // Account Number
    $sheet->getColumnDimension('E')->setWidth(13);  // Amount + Date

    // === ROW HEIGHT = 25 for ALL DATA ROWS (from header to grand total) ===
    for ($row = 7; $row <= $grandTotalRow; $row++) {
        $sheet->getRowDimension($row)->setRowHeight(35);
    }

    // === WRAP TEXT in ALL TABLE CELLS ===
    $tableRange = "A7:E{$grandTotalRow}";
    $sheet->getStyle($tableRange)->getAlignment()->setWrapText(true);
    $sheet->getStyle($tableRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

    // === BORDERS ONLY ON TABLE ===
    $sheet->getStyle($tableRange)->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => 'D3D3D3']
            ]
        ]
    ]);

    // === DATE IN E1: Visible, Right-aligned, Bold ===
    $sheet->getStyle('E1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->getStyle('E1')->getFont()->setBold(true);

    // === "To" in A1 Bold ===
    $sheet->getStyle('A1')->getFont()->setBold(true);

    // === Bank Name & Address Bold ===
    $sheet->getStyle('A2:A3')->getFont()->setBold(true);

    // === Subject Line: Merged + Bold ===
    $sheet->mergeCells('A5:E5');
    $sheet->getStyle('A5')->getFont()->setBold(true);

    // === TABLE HEADER: Dark Blue + White Text ===
    $sheet->getStyle('A7:E7')->applyFromArray([
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '263871']
        ],
        'font' => [
            'bold' => true,
            'size' => 12,
            'color' => ['rgb' => 'FFFFFF']
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER
        ]
    ]);

    // === AUTO FILTER (Data only) ===
    $dataLastRow = $grandTotalRow - 1;
    $sheet->setAutoFilter("A7:E{$dataLastRow}");

    // === GRAND TOTAL ROW: Yellow + Bold ===
    $sheet->getStyle("D{$grandTotalRow}:E{$grandTotalRow}")->applyFromArray([
        'font' => ['bold' => true, 'size' => 12],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'FFFF00']
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER
        ]
    ]);

    // === IN WORDS ===
    $inWordsRow = $lastRow - 5;
    $sheet->mergeCells("A{$inWordsRow}:E{$inWordsRow}");
    $sheet->getStyle("A{$inWordsRow}")->applyFromArray([
        'font' => ['bold' => true, 'size' => 12],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
    ]);

    // === THANKING YOU ===
    $thankYouRow = $lastRow - 2;
    $sheet->mergeCells("A{$thankYouRow}:E{$thankYouRow}");
    $sheet->getStyle("A{$thankYouRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // === COMPANY NAME ===
    $sheet->mergeCells("A{$lastRow}:E{$lastRow}");
    $sheet->getStyle("A{$lastRow}")->applyFromArray([
        'font' => ['bold' => true, 'size' => 12],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
    ]);

    return $sheet;
}

    public function title(): string
    {
        return 'Bank Sheet';
    }
}