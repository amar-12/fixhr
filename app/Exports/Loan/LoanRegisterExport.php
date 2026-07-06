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

class LoanRegisterExport implements FromCollection, WithHeadings, WithColumnWidths, WithEvents
{
    protected $loans;
    protected $businessId;
    protected $fileName = 'Loan Register Report';

    public function __construct($loans, $businessId)
    {
        $this->loans      = $loans;
        $this->businessId = $businessId;
    }

    public function collection()
    {
        $serial = 1;
        return $this->loans->map(function ($loan) use (&$serial) {
            $firstInstallment = $loan->fh_payroll_loan_installments->first();

            $deductionStartDate = $firstInstallment?->pli_due_date
                ? Carbon::parse($firstInstallment->pli_due_date)->format('d-M-y')
                : '';

            $openingBalance      = $firstInstallment?->pli_opening_balance ?? '';
            $openingPrincipal    = $firstInstallment?->pli_principal ?? '';
            $openingInterest     = $firstInstallment?->pli_interest ?? '';
            $outstandingBalance  = $firstInstallment?->pli_rem_bal ?? '';
            $openingInstallments = $loan->fh_payroll_loan_installments
                ->where('pli_status', 'pending')
                ->count();

            $loanDate = $loan->lnr_start_date
                ? Carbon::parse($loan->lnr_start_date)->format('d-M-y')
                : '';

            return [
                'S#'                     => $serial++,
                'Emp Code'             => $loan->fh_employee->emp_code ?? '',
                'Employee Name'             => $loan->fh_employee->emp_full_name ?? '',
                'Loan No'                   => (string) $loan->lnr_unique_id,
                'Loan Type'                 => $loan->lnr_advance_type ?? '',
                'Loan Date'                 => $loanDate,
                'Deduction Start Date'      => $deductionStartDate,
                'No. of Installment'        => (string) $loan->lnr_installments,
                'Loan Amount'               => (string) $loan->lnr_requested_amount,
                'Interest %'                => (string) $loan->lnr_rate,
                'WEF'                       => $deductionStartDate,
                'Loan Schedule No. of Installment' => (string) ($firstInstallment?->pli_installment_no ?? ''),
                'Principal Amount'                 => (string) $openingPrincipal,
                'Interest Amount'                  => (string) $openingInterest,
                'Opening No. of Installment'       => (string) $openingInstallments,
                'Opening Balance'                  => (string) $openingBalance,
                'Principal'                        => (string) $openingPrincipal,
                'Opening Interest'                 => (string) $openingInterest,
                'Outstanding Balance'              => (string) $outstandingBalance,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'S#',
            'Emp Code',
            'Employee Name',
            'Loan No',
            'Loan Type',
            'Loan Date',
            'Deduction Start Date',
            'No. of Installment',
            'Loan Amount',
            'Interest %',
            'WEF',
            'Loan Schedule No. of Installment',
            'Principal Amount',
            'Interest Amount',
            'Opening No. of Installment',
            'Opening Balance',
            'Principal',
            'Opening Interest',
            'Outstanding Balance',
        ];
    }

    public function columnWidths(): array
    {
        $headings = $this->headings();
        $widths   = [];

        foreach ($headings as $i => $heading) {
            $col = Coordinate::stringFromColumnIndex($i + 1);
            $widths[$col] = match ($heading) {
                'S#'        => 7,
                'Employee Code'=> 7,
                default        => 13,
            };
        }

        return $widths;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet       = $event->sheet->getDelegate();
                $headings    = $this->headings();
                $lastCol     = Coordinate::stringFromColumnIndex(count($headings));
                $headingRow  = 6;
                $dataRows    = $this->collection()->count();
                $lastDataRow = $headingRow + $dataRows;

                // 1. Insert 5 rows
                $sheet->insertNewRowBefore(1, 5);
                $sheet->setShowGridlines(false);

                // 2. Top Header
                $businessName = Business::where('b_id', $this->businessId)->value('b_name') ?? 'Business';
                $printedOn = Carbon::now()->format('d-M-Y h:i A T');

                $sheet->setCellValue('A1', $businessName);
                $sheet->setCellValue('A2', $this->fileName . ' (Month Wise Recovery)');
                $sheet->setCellValue('A3', 'Date: ' . Carbon::now()->format('d-M-Y'));
                $sheet->setCellValue('A4', "Printed on: {$printedOn}");

                foreach (range(1, 4) as $r) {
                    $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
                    $sheet->getStyle("A{$r}")->getFont()->setSize(11)->setBold(true);
                    $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // 3. Row 5: Parent Headers (H:S)
                $sheet->mergeCells('H5:J5'); $sheet->setCellValue('H5', 'Loan Section');
                $sheet->mergeCells('K5:N5'); $sheet->setCellValue('K5', 'Loan Schedule (as per latest revision)');
                $sheet->mergeCells('O5:S5'); $sheet->setCellValue('O5', 'Installments Details');

                // 4. Write headings in Row 6
                foreach ($headings as $i => $heading) {
                    $col = Coordinate::stringFromColumnIndex($i + 1);
                    $sheet->setCellValue("{$col}{$headingRow}", $heading);
                }

                // 5. Duplicate first 7 headings in Row 5
                $firstSeven = array_slice($headings, 0, 7);
                foreach ($firstSeven as $i => $text) {
                    $col = Coordinate::stringFromColumnIndex($i + 1);
                    $sheet->setCellValue("{$col}5", $text);
                }

                // 6. VERTICAL MERGE: A5:A6 ... G5:G6
                foreach (range('A', 'G') as $col) {
                    $sheet->mergeCells("{$col}5:{$col}6");
                }

                // 7. STYLE: Background, Font, Alignment
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

                // 8. THIN BORDERS: Apply to BOTH row 5 and row 6
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

                // Row heights
                $sheet->getRowDimension(5)->setRowHeight(25);
                $sheet->getRowDimension($headingRow)->setRowHeight(30);

                // 9. Data Rows
                if ($dataRows > 0) {
                    for ($r = $headingRow + 1; $r <= $lastDataRow; $r++) {
                        $sheet->getRowDimension($r)->setRowHeight(25);
                    }

                    $sheet->getStyle("A{$headingRow}:{$lastCol}{$lastDataRow}")
                        ->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->getColor()->setRGB('D3D3D3');

                    $sheet->getStyle("A" . ($headingRow + 1) . ":{$lastCol}{$lastDataRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setWrapText(true);
                    $sheet->getStyle("A" . ($headingRow + 1) . ":{$lastCol}{$lastDataRow}")
                        ->getFont()->setSize(9);

                    for ($r = $headingRow + 1; $r <= $lastDataRow; $r++) {
                        $bg = (($r - $headingRow) % 2 == 0) ? 'F5F5F5' : 'FFFFFF';
                        $sheet->getStyle("A{$r}:{$lastCol}{$r}")
                            ->getFill()->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB($bg);
                    }
                }

                // 10. Freeze Pane
                $sheet->freezePane('D7');
            },
        ];
    }
}