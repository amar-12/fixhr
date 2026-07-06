<?php

namespace App\Exports\Loan;

use App\Models\Business;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LoanProjectionExport implements FromCollection, WithHeadings, WithColumnWidths, WithEvents
{
    protected $loans;
    protected $businessId;
    protected $fileName = 'Loan Projection Report';

    public function __construct($loans, $businessId)
    {
        $this->loans = $loans;
        $this->businessId = $businessId;
    }

    public function collection()
    {
        $rows = [];
        $srNo = 1;
        $previousEmployeeId = null;

        foreach ($this->loans as $loan) {
            $employeeId = $loan->lnr_emp_id;
            $employeeCode = $loan->fh_employee->emp_code ?? '';
            $employeeName = $loan->fh_employee->emp_full_name ?? '';
            $loanNo = $loan->lnr_unique_id ?? '';

            // Add a blank row if the employee changes (skip for the first employee)
            if ($previousEmployeeId !== null && $previousEmployeeId !== $employeeId) {
                $rows[] = array_fill(0, 11, ''); // Blank row
            }

            foreach ($loan->fh_payroll_loan_installments as $installment) {
                $rows[] = [
                    (string) $srNo++,
                    $employeeCode,
                    $employeeName,
                    $loanNo,
                    (string) $installment->pli_installment_no,
                    Carbon::parse($installment->pli_due_date)->format('d-M-y'),
                    number_format($installment->pli_principal, 2),
                    number_format($installment->pli_interest, 2),
                    number_format($installment->pli_amount, 2),
                    number_format($installment->pli_rem_bal, 2),
                    ucfirst($installment->pli_status),
                ];
            }

            $previousEmployeeId = $employeeId;
        }

        return collect($rows);
    }

    public function headings(): array
    {
        return [
            'S#',
            'Emp Code',
            'Employee Name',
            'Loan No',
            'Installment No',
            'Due Date',
            'Principal Payment',
            'Interest',
            'Total Payment',
            'Remaining Balance',
            'Status',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 12,
            'C' => 20,
            'D' => 15,
            'E' => 12,
            'F' => 12,
            'G' => 15,
            'H' => 10,
            'I' => 12,
            'J' => 15,
            'K' => 10,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $headings = $this->headings();
                $lastCol = Coordinate::stringFromColumnIndex(count($headings)); // K
                $headingRow = 6;
                $dataRows = $this->collection()->count();
                $lastDataRow = $headingRow + $dataRows;

                // 1. Insert 5 empty rows at the top
                $sheet->insertNewRowBefore(1, 5);
                $sheet->setShowGridlines(false);

                // 2. Top Header (Rows 1–4)
                $businessName = Business::where('b_id', $this->businessId)->value('b_name') ?? 'Business';
                $printedOn = Carbon::now()->format('d-M-Y h:i A T');

                $sheet->setCellValue('A1', $businessName);
                $sheet->setCellValue('A2', $this->fileName);
                $sheet->setCellValue('A3', 'Date: ' . Carbon::now()->format('d-M-Y'));
                $sheet->setCellValue('A4', "Printed on: {$printedOn}");

                foreach (range(1, 4) as $r) {
                    $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
                    $sheet->getStyle("A{$r}")->getFont()->setSize(11)->setBold(true);
                    $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // 3. Parent Headers (Row 5)
                $sheet->mergeCells('A5:A6');   $sheet->setCellValue('A5', 'S#');
                $sheet->mergeCells('B5:D5');   $sheet->setCellValue('B5', 'Borrower Details');
                $sheet->mergeCells('E5:J5');   $sheet->setCellValue('E5', 'Installment Details');
                $sheet->mergeCells('K5:K6');   $sheet->setCellValue('K5', 'Status');

                // 4. Write actual headings in Row 6
                foreach ($headings as $i => $heading) {
                    $col = Coordinate::stringFromColumnIndex($i + 1);
                    $sheet->setCellValue("{$col}{$headingRow}", $heading);
                }

                // 5. Vertical merge for S# and Status
                // Already done above: A5:A6 and K5:K6

                // 6. Header Style (Row 5 & 6) - Dark Blue Background
                $headerStyle = [
                    'font' => [
                        'size' => 10,
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '263871'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ];

                $sheet->getStyle("A5:{$lastCol}5")->applyFromArray($headerStyle);
                $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")->applyFromArray($headerStyle);

                // 7. Thin Borders for Header Rows
                $borderStyle = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D3D3D3'],
                        ],
                    ],
                ];

                $sheet->getStyle("A5:{$lastCol}5")->applyFromArray($borderStyle);
                $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")->applyFromArray($borderStyle);

                // 8. Row Heights
                $sheet->getRowDimension(5)->setRowHeight(25);
                $sheet->getRowDimension($headingRow)->setRowHeight(30);

                // 9. Data Rows Styling
                if ($dataRows > 0) {
                    for ($r = $headingRow + 1; $r <= $lastDataRow; $r++) {
                        $sheet->getRowDimension($r)->setRowHeight(25);
                    }

                    // Apply thin borders to entire data + header area
                    $sheet->getStyle("A5:{$lastCol}{$lastDataRow}")
                        ->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->getColor()->setRGB('D3D3D3');

                    // Center align data
                    $sheet->getStyle("A" . ($headingRow + 1) . ":{$lastCol}{$lastDataRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setWrapText(true);

                    // Font size
                    $sheet->getStyle("A" . ($headingRow + 1) . ":{$lastCol}{$lastDataRow}")
                        ->getFont()->setSize(9);

                    // Alternating row colors
                    for ($r = $headingRow + 1; $r <= $lastDataRow; $r++) {
                        $bg = (($r - $headingRow) % 2 == 0) ? 'F5F5F5' : 'FFFFFF';
                        $sheet->getStyle("A{$r}:{$lastCol}{$r}")
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB($bg);
                    }
                }

                // 10. Freeze Pane
                $sheet->freezePane('D7');
            },
        ];
    }
}