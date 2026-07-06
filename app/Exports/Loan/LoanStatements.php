<?php
namespace App\Exports\Loan;
use App\Models\Business;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class LoanStatements implements FromCollection, WithHeadings, WithCustomStartCell, WithStyles, WithEvents
{
    protected $loans;
    protected $businessId;
    protected $selectedMonth;

   public function __construct($loans, $businessId, $selectedMonth)
{
    $this->loans = $loans;
    $this->businessId = $businessId;
    // If $selectedMonth is a number (e.g., '7'), prepend the current year
    if (is_numeric($selectedMonth)) {
        $this->selectedMonth = Carbon::now()->year . '-' . str_pad($selectedMonth, 2, '0', STR_PAD_LEFT);
    } else {
        $this->selectedMonth = $selectedMonth;
    }
}

    public function collection()
    {
        $rows = [];

        // Filter continuing loans (those not closed in the month)
        $continuing = $this->loans->filter(function ($loan) {
            if ($loan->fh_payroll_loan_installments->isEmpty()) {
                return false;
            }
            $firstInstallment = $loan->fh_payroll_loan_installments->first();
            $pendingCount = $loan->fh_payroll_loan_installments->where('pli_status', 'pending')->count();
            return $pendingCount > 0 || ((float) $firstInstallment->pli_rem_bal > 0);
        });

        $index = 1;
        foreach ($continuing as $loan) {
            // Same logic as before for row data
            $deductionStartDate = '';
            $openingBalance = '';
            $openingPrincipal = '';
            $openingInterest = '';
            $openingInstallments = 0;
            $outStandingBalance = '';
            if ($loan->fh_payroll_loan_installments->isNotEmpty()) {
                $firstInstallment = $loan->fh_payroll_loan_installments->first();
                $deductionStartDate = $firstInstallment->pli_due_date ? Carbon::parse($firstInstallment->pli_due_date)->format('d-M-y') : '';
                $openingBalance = $firstInstallment->pli_opening_balance ?? '';
                $openingPrincipal = $firstInstallment->pli_principal ?? '';
                $openingInterest = $firstInstallment->pli_interest ?? '';
                $outStandingBalance = $firstInstallment->pli_rem_bal ?? '';
                $openingInstallments = $loan->fh_payroll_loan_installments->where('pli_status', 'pending')->count();
            }
            $interestAmount = $loan->lnr_interest_amount ?? '0';
            $loanDate = $loan->lnr_start_date ? Carbon::parse($loan->lnr_start_date)->format('d-M-y') : '';
            $wefDate = $deductionStartDate ? $deductionStartDate : '';
            $rows[] = [
                (string) ($index),
                $loan->fh_employee->emp_code ?? '',
                $loan->fh_employee->emp_full_name ?? '',
                (string) $loan->lnr_unique_id,
                $loan->lnr_advance_type ?? '',
                $loanDate,
                $deductionStartDate,
                (string) $loan->lnr_installments,
                (string) $loan->lnr_requested_amount,
                (string) $loan->lnr_rate,
                $wefDate,
                (string) ($firstInstallment->pli_installment_no ?? ''),
                (string) ($firstInstallment->pli_principal ?? ''),
                (string) ($firstInstallment->pli_interest ?? ''),
                (string) $openingInstallments,
                (string) $openingBalance,
                (string) $openingPrincipal,
                (string) $openingInterest,
                (string) $outStandingBalance,
            ];
            $index++;
        }
        return collect($rows);
    }

    public function headings(): array
    {
        return [
            'Sr No',
            'Employee Code',
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
            'Outstanding Balance'
        ];
    }

    public function startCell(): string
    {
        return 'A5';
    }

    public function styles(Worksheet $sheet)
    {
        // Base styles for main table (we'll extend borders in AfterSheet for additional sections)
        $dataRowCount = $this->loans->filter(function ($loan) {
            if ($loan->fh_payroll_loan_installments->isEmpty()) return false;
            $firstInstallment = $loan->fh_payroll_loan_installments->first();
            return $loan->fh_payroll_loan_installments->where('pli_status', 'pending')->count() > 0 || ((float) $firstInstallment->pli_rem_bal > 0);
        })->count();
        $lastRow = 5 + $dataRowCount;
        $sheet->getStyle('A5:S' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ],
            'font' => [
                'size' => 10,
            ],
        ]);
        $sheet->getStyle('A5:S5')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 10,
            ],
        ]);
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                // Add custom headers at the top
                $businessName = Business::where('b_id', $this->businessId)->value('b_name') ?? 'Unknown Business';
                $fileName = 'Loan Statement Report';
                $printedDate = Carbon::now()->format('d-M-y');
                $sheet->setCellValue('A1', $businessName);
                $sheet->setCellValue('A2', $fileName . ' (Month Wise Recovery)');
                $sheet->setCellValue('A3', 'Printed Date: ' . $printedDate);
                $sheet->getStyle('A1:A3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 10,
                    ],
                ]);
                // Set column widths
                $sheet->getColumnDimension('A')->setWidth(5);
                foreach (range('B', 'S') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                    $sheet->getColumnDimension($column)->setWidth(10);
                }

                // Filter continuing and closed loans
                $continuing = $this->loans->filter(function ($loan) {
                    if ($loan->fh_payroll_loan_installments->isEmpty()) return false;
                    $firstInstallment = $loan->fh_payroll_loan_installments->first();
                    return $loan->fh_payroll_loan_installments->where('pli_status', 'pending')->count() > 0 || ((float) $firstInstallment->pli_rem_bal > 0);
                });
                $closed = $this->loans->filter(function ($loan) {
                    if ($loan->fh_payroll_loan_installments->isEmpty()) return true;
                    $firstInstallment = $loan->fh_payroll_loan_installments->first();
                    return $loan->fh_payroll_loan_installments->where('pli_status', 'pending')->count() == 0 && ((float) $firstInstallment->pli_rem_bal == 0);
                });

                // Main table last row
                $dataRowCount = $continuing->count();
                $lastMainRow = 5 + $dataRowCount;

                // Add Grand Total for main table
                $grandTotalRow = $lastMainRow + 1;
                $sheet->setCellValue('A' . $grandTotalRow, 'Grand Total');
                $numericColumns = ['H' => 'No. of Installment', 'I' => 'Loan Amount', 'J' => 'Interest %', 'L' => 'Loan Schedule No. of Installment', 'M' => 'Principal Amount', 'N' => 'Interest Amount', 'O' => 'Opening No. of Installment', 'P' => 'Opening Balance', 'Q' => 'Principal', 'R' => 'Opening Interest', 'S' => 'Outstanding Balance'];
                foreach ($numericColumns as $col => $label) {
                    $sheet->setCellValue($col . $grandTotalRow, '=SUM(' . $col . '6:' . $col . $lastMainRow . ')');
                }
                $sheet->getStyle('A' . $grandTotalRow . ':S' . $grandTotalRow)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '000000']],
                    ],
                ]);

                // Add Opening Balance row
                $previousMonth = Carbon::parse($this->selectedMonth)->subMonth()->format('M-y');
                $openingRow = $grandTotalRow + 2;
                $sheet->setCellValue('A' . $openingRow, 'Opening Balance: ' . $previousMonth);
                // Calculate sums for opening (continuing + closed)
                $openingSums = $this->calculateSums($this->loans); // All loans for opening
                $sheet->setCellValue('H' . $openingRow, $openingSums['num_install']);
                $sheet->setCellValue('I' . $openingRow, $openingSums['loan']);
                $sheet->setCellValue('J' . $openingRow, $openingSums['interest_rate']);
                $sheet->setCellValue('L' . $openingRow, $openingSums['schedule_no']);
                $sheet->setCellValue('M' . $openingRow, $openingSums['principal_amount']);
                $sheet->setCellValue('N' . $openingRow, $openingSums['interest_amount']);
                $sheet->setCellValue('O' . $openingRow, $openingSums['opening_install']);
                $sheet->setCellValue('P' . $openingRow, $openingSums['opening_bal']);
                $sheet->setCellValue('Q' . $openingRow, $openingSums['principal']);
                $sheet->setCellValue('R' . $openingRow, $openingSums['opening_interest']);
                $sheet->setCellValue('S' . $openingRow, $openingSums['balance']);
                $sheet->getStyle('A' . $openingRow . ':S' . $openingRow)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                ]);

                // Add Less section
                $lessTitleRow = $openingRow + 2;
                $sheet->setCellValue('A' . $lessTitleRow, 'Less');
                $sheet->getStyle('A' . $lessTitleRow)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                ]);

                // Add headings for less section
                $lessHeadRow = $lessTitleRow + 1;
                $headings = $this->headings();
                foreach (range('A', 'S') as $colIndex => $col) {
                    $sheet->setCellValue($col . $lessHeadRow, $headings[$colIndex]);
                }
                $sheet->getStyle('A' . $lessHeadRow . ':S' . $lessHeadRow)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '000000']],
                    ],
                ]);

                // Add data rows for closed loans
                $index = 1;
                $lessLastRow = $lessHeadRow;
                foreach ($closed as $loan) {
                    $dataRow = $lessHeadRow + $index;
                    // Same row logic as collection
                    $deductionStartDate = '';
                    $openingBalance = '';
                    $openingPrincipal = '';
                    $openingInterest = '';
                    $openingInstallments = 0;
                    $outStandingBalance = '';
                    if ($loan->fh_payroll_loan_installments->isNotEmpty()) {
                        $firstInstallment = $loan->fh_payroll_loan_installments->first();
                        $deductionStartDate = $firstInstallment->pli_due_date ? Carbon::parse($firstInstallment->pli_due_date)->format('d-M-y') : '';
                        $openingBalance = $firstInstallment->pli_opening_balance ?? '';
                        $openingPrincipal = $firstInstallment->pli_principal ?? '';
                        $openingInterest = $firstInstallment->pli_interest ?? '';
                        $outStandingBalance = $firstInstallment->pli_rem_bal ?? '';
                        $openingInstallments = $loan->fh_payroll_loan_installments->where('pli_status', 'pending')->count();
                    }
                    $loanDate = $loan->lnr_start_date ? Carbon::parse($loan->lnr_start_date)->format('d-M-y') : '';
                    $wefDate = $deductionStartDate ? $deductionStartDate : '';
                    $sheet->setCellValue('A' . $dataRow, (string) $index);
                    $sheet->setCellValue('B' . $dataRow, $loan->fh_employee->emp_code ?? '');
                    $sheet->setCellValue('C' . $dataRow, $loan->fh_employee->emp_full_name ?? '');
                    $sheet->setCellValue('D' . $dataRow, (string) $loan->lnr_unique_id);
                    $sheet->setCellValue('E' . $dataRow, $loan->lnr_advance_type ?? '');
                    $sheet->setCellValue('F' . $dataRow, $loanDate);
                    $sheet->setCellValue('G' . $dataRow, $deductionStartDate);
                    $sheet->setCellValue('H' . $dataRow, (string) $loan->lnr_installments);
                    $sheet->setCellValue('I' . $dataRow, (string) $loan->lnr_requested_amount);
                    $sheet->setCellValue('J' . $dataRow, (string) $loan->lnr_rate);
                    $sheet->setCellValue('K' . $dataRow, $wefDate);
                    $sheet->setCellValue('L' . $dataRow, (string) ($firstInstallment->pli_installment_no ?? ''));
                    $sheet->setCellValue('M' . $dataRow, (string) ($firstInstallment->pli_principal ?? ''));
                    $sheet->setCellValue('N' . $dataRow, (string) ($firstInstallment->pli_interest ?? ''));
                    $sheet->setCellValue('O' . $dataRow, (string) $openingInstallments);
                    $sheet->setCellValue('P' . $dataRow, (string) $openingBalance);
                    $sheet->setCellValue('Q' . $dataRow, (string) $openingPrincipal);
                    $sheet->setCellValue('R' . $dataRow, (string) $openingInterest);
                    $sheet->setCellValue('S' . $dataRow, (string) $outStandingBalance);
                    $index++;
                    $lessLastRow = $dataRow;
                }

                // Apply borders to less table
                $sheet->getStyle('A' . $lessHeadRow . ':S' . $lessLastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '000000']],
                    ],
                    'font' => ['size' => 10],
                ]);

                // Add Total Subtraction row
                $totalSubRow = $lessLastRow + 1;
                $sheet->setCellValue('A' . $totalSubRow, 'Total Subtraction');
                foreach ($numericColumns as $col => $label) {
                    $sheet->setCellValue($col . $totalSubRow, '= ' . $col . $grandTotalRow);
                }
                $sheet->getStyle('A' . $totalSubRow . ':S' . $totalSubRow)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => '000000']],
                    ],
                ]);

                // Add Closing Balance label
                $closingRow = $totalSubRow + 2;
                $sheet->setCellValue('A' . $closingRow, 'Closing Balance');
                $sheet->getStyle('A' . $closingRow)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                ]);

                // Add employee counts
                $employeeOpeningRow = $closingRow + 2;
                $sheet->setCellValue('A' . $employeeOpeningRow, 'Employee Opening Balance');
                $sheet->setCellValue('S' . $employeeOpeningRow, $this->loans->count());

                $addedRow = $employeeOpeningRow + 1;
                $added = $this->loans->filter(function ($loan) {
                    return Carbon::parse($loan->lnr_start_date)->format('Y-m') === $this->selectedMonth;
                })->count();
                $sheet->setCellValue('A' . $addedRow, 'Added Employees');
                $sheet->setCellValue('S' . $addedRow, $added);

                $lessEmployeeRow = $addedRow + 1;
                $sheet->setCellValue('A' . $lessEmployeeRow, 'Less Employees');
                $sheet->setCellValue('S' . $lessEmployeeRow, $closed->count());

                $employeeClosingRow = $lessEmployeeRow + 1;
                $sheet->setCellValue('A' . $employeeClosingRow, 'Employee Closing Balance: Jul-25');
                $sheet->setCellValue('S' . $employeeClosingRow, $continuing->count());

                $sheet->getStyle('A' . $employeeOpeningRow . ':A' . $employeeClosingRow)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                ]);
                $sheet->getStyle('S' . $employeeOpeningRow . ':S' . $employeeClosingRow)->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                ]);
            },
        ];
    }

    // Helper method to calculate sums for a collection of loans
    protected function calculateSums($loans)
    {
        $sums = [
            'loan' => 0,
            'num_install' => 0,
            'interest_rate' => 0,
            'schedule_no' => 0,
            'principal_amount' => 0,
            'interest_amount' => 0,
            'opening_install' => 0,
            'opening_bal' => 0,
            'principal' => 0,
            'opening_interest' => 0,
            'balance' => 0,
        ];
        foreach ($loans as $loan) {
            $sums['loan'] += (float) $loan->lnr_requested_amount;
            $sums['num_install'] += (float) $loan->lnr_installments;
            $sums['interest_rate'] += (float) $loan->lnr_rate;
            if ($loan->fh_payroll_loan_installments->isNotEmpty()) {
                $firstInstallment = $loan->fh_payroll_loan_installments->first();
                $sums['schedule_no'] += (float) ($firstInstallment->pli_installment_no ?? 0);
                $sums['principal_amount'] += (float) ($firstInstallment->pli_principal ?? 0);
                $sums['interest_amount'] += (float) ($firstInstallment->pli_interest ?? 0);
                $sums['opening_install'] += (float) $loan->fh_payroll_loan_installments->where('pli_status', 'pending')->count();
                $sums['opening_bal'] += (float) ($firstInstallment->pli_opening_balance ?? 0);
                $sums['principal'] += (float) ($firstInstallment->pli_principal ?? 0);
                $sums['opening_interest'] += (float) ($firstInstallment->pli_interest ?? 0);
                $sums['balance'] += (float) ($firstInstallment->pli_rem_bal ?? 0);
            }
        }
        return $sums;
    }
}