<?php

namespace App\Exports;

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
        // $this->selectedFields = $selectedFields;
        $this->amountCheck = $amountCheck;
        $this->businessId = $businessId;
        $this->payrollName = $payrollName;
        $this->business = $business;
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_TEXT, // Column D is Account Number
        ];
    }

    public function collection()
    {
        $exportData = [];

        // Header content
        for ($i = 0; $i < 6; $i++) $exportData[] = [' '];
        $exportData[] = ['To', ' ', ' ', ' ', date('d-M-Y')];
        $exportData[] = [$this->business->b_bank_name];
        $exportData[] = [$this->business->b_bank_address];
        $exportData[] = [' '];
        $exportData[] = ['Sub: Kindly Process Salary for the month of ' . $this->payrollName . ' Vide No. ' . $this->business->b_cheque_no];
        $exportData[] = [' '];

        // Table header
        $exportData[] = [
            'S. No.',
            'Employee Code',
            'Employee Name',
            'Account Number',
            'Amount'
        ];

        $i = 1;
        $grandTotal = 0;

        foreach ($this->data as $employee) {
            $processed = $employee->processedSalaries->first();

            // Get account number from JSON or fallback
            $accountNo = '';

            if (!empty($employee->emp_documents_ref_file)) {
                $accountData = json_decode($employee->emp_documents_ref_file, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($accountData['account_number'])) {
                    $accountNo = $accountData['account_number'];
                }
            }

            // Fallback to bank account column
            if (empty($accountNo)) {
                $accountNo = $employee->emp_bank_account_no ?? '';
            }

            $amount = isset($processed) && is_numeric($processed->ps_monthly_net_salary)
                ? (float)$processed->ps_monthly_net_salary
                : 0.00;
            $accountNoFormatted = is_numeric($accountNo) ? (float)$accountNo : $accountNo;


            $exportData[] = [
                $i++,
                $employee->emp_code ?? '',
                $employee->emp_full_name ?? '',
                preg_match('/^\d+$/', $accountNo) ? "'$accountNo" : $accountNo,

                number_format($amount, 2, '.', ''),
            ];

            $grandTotal += $amount;
        }

        $numberToWords = new NumberToWords();
        $words = ucfirst($numberToWords->getNumberTransformer('en')->toWords($grandTotal));

        $exportData[] = [' ', ' ', ' ', 'Grand Total', number_format($grandTotal, 2, '.', '')];
        $exportData[] = [' '];
        $exportData[] = ['In Words : ' . $words];
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
                'vertical' => Alignment::VERTICAL_TOP,
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
        $lastRow = $sheet->getHighestRow();
        $lastColumn = $sheet->getHighestColumn();

        $sheet->freezePane('A14');

        $borderRange = 'A13:' . $lastColumn . $lastRow;

        $sheet->getStyle($borderRange)->applyFromArray([
            'borders' => ['allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000']
            ]]
        ]);

        $sheet->getStyle($borderRange)->getAlignment()->setWrapText(true);
        $sheet->getStyle($borderRange)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);

        foreach (range('B', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->getStyle('A11:H11')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'font' => ['bold' => true, 'size' => 11]
        ]);
        $sheet->mergeCells('A11:H11');

        $sheet->getRowDimension(12)->setRowHeight(30);

        $sheet->getStyle('A13:' . $lastColumn . '13')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'font' => ['bold' => true, 'size' => 12],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '89CFF0']
            ]
        ]);

        $sheet->setAutoFilter('A13:' . $lastColumn . $lastRow);

        // Grand Total Row
        $grandTotalRow = $lastRow - 7;
        $sheet->getStyle('D' . $grandTotalRow . ':' . $lastColumn . $grandTotalRow)->applyFromArray([
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

        // In Words Row
        $inWordsRow = $lastRow - 5;
        $sheet->getStyle('A' . $inWordsRow)->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true]
        ]);
        $sheet->mergeCells("A$inWordsRow:$lastColumn$inWordsRow");

        // Signature
        $sheet->getStyle('A' . ($lastRow - 2))->applyFromArray([
            'font' => ['size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);
        $sheet->mergeCells('A' . ($lastRow - 2) . ':' . $lastColumn . ($lastRow - 2));

        // Business name at bottom
        $sheet->getStyle('A' . $lastRow)->applyFromArray([
            'font' => ['size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);
        $sheet->mergeCells('A' . $lastRow . ':' . $lastColumn . $lastRow);

        return $sheet;
    }

    public function title(): string
    {
        return 'Bank Sheet';
    }
}
