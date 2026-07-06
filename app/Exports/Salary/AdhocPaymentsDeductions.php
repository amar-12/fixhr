<?php

namespace App\Exports\Salary;

use App\Models\FinancialYear;
use App\Models\PayrollPeriod;
use App\Models\Business;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class AdhocPaymentsDeductions implements FromCollection, WithHeadings, WithColumnWidths, WithEvents
{
    protected $transactions;
    protected $businessId;
    protected $mergeCells = [];
    protected $date;
    protected $fileName;

    public function __construct(array $transactions, $businessId, $date)
    {
        $this->transactions = $transactions;
        $this->businessId = $businessId;
        $this->date = $date;
        $this->fileName = 'Adhoc Payments and Deductions Report';
    }

    /* ------------------------------------------------------------------ */
    /*  COLLECTION – Build rows with serial number & merge logic          */
    /* ------------------------------------------------------------------ */
    public function collection()
    {
        $data = [];
        $currentRow = 6; // Start after 5 header rows
        $serialNumber = 1;

        // Group transactions by employee
        $groupedByEmployee = collect($this->transactions)->groupBy('at_emp_id')->map(function ($group) {
            return [
                'employee' => $group[0]['employee'] ?? [],
                'department' => $group[0]['department'] ?? [],
                'transactions' => $group,
            ];
        });

        foreach ($groupedByEmployee as $empId => $group) {
            $employee = $group['employee'];
            $department = $group['department'];
            $transactions = $group['transactions'];
            $rowCount = 0;

            // Count total detail rows
            foreach ($transactions as $transaction) {
                $rowCount += count($transaction['transaction_details'] ?? []);
            }

            // Store merge ranges
            if ($rowCount > 1) {
                $endRow = $currentRow + $rowCount - 1;
                $this->mergeCells[] = "A{$currentRow}:A{$endRow}"; // Sr
                $this->mergeCells[] = "B{$currentRow}:B{$endRow}"; // Emp Code
                $this->mergeCells[] = "C{$currentRow}:C{$endRow}"; // Emp Name
                $this->mergeCells[] = "D{$currentRow}:D{$endRow}"; // Department
                $this->mergeCells[] = "J{$currentRow}:J{$endRow}"; // Total Earning
                $this->mergeCells[] = "K{$currentRow}:K{$endRow}"; // Total Deduction
            }

            $firstRowForEmployee = true;
            $totalEarning = 0;
            $totalDeduction = 0;

            // Calculate totals
            foreach ($transactions as $transaction) {
                $totalEarning += (float)($transaction['at_e_amount'] ?? 0);
                $totalDeduction += (float)($transaction['at_d_amount'] ?? 0);
            }

            foreach ($transactions as $transaction) {
                $payrollPeriod = PayrollPeriod::find($transaction['at_pp_id']);
                $financialYear = $payrollPeriod
                    ? FinancialYear::find($payrollPeriod->pp_fy_id)->fy_year ?? ''
                    : '';

                foreach ($transaction['transaction_details'] ?? [] as $detail) {
                    $row = [
                        'Sr' => $firstRowForEmployee ? $serialNumber : '',
                        'Emp Code' => $firstRowForEmployee ? ($employee['emp_code'] ?? '') : '',
                        'Emp Name' => $firstRowForEmployee ? ($employee['emp_full_name'] ?? '') : '',
                        'Department' => $firstRowForEmployee ? ($department['d_name'] ?? '') : '',
                        'Financial Year' => $financialYear,
                        'Payroll Period' => $payrollPeriod->pp_name ?? '',
                        'Component Name' => $detail['component']['ac_adhoc_component_name'] ?? 'N/A',
                        'Earning Amount' => number_format((float)($detail['earning_amount'] ?? 0), 2),
                        'Deduction Amount' => number_format((float)($detail['deduction_amount'] ?? 0), 2),
                        'Total Earning' => $firstRowForEmployee ? number_format($totalEarning, 2) : '',
                        'Total Deduction' => $firstRowForEmployee ? number_format($totalDeduction, 2) : '',
                        'Remarks' => $detail['remarks'] ?? '',
                    ];
                    $data[] = $row;
                    $firstRowForEmployee = false;
                    $currentRow++;
                }
            }
            $serialNumber++;
        }

        return collect($data);
    }

    /* ------------------------------------------------------------------ */
    /*  HEADINGS                                                          */
    /* ------------------------------------------------------------------ */
    public function headings(): array
    {
        return [
            'Sr',
            'Emp Code',
            'Emp Name',
            'Department',
            'Financial Year',
            'Payroll Period',
            'Component Name',
            'Earning Amount',
            'Deduction Amount',
            'Total Earning',
            'Total Deduction',
            'Remarks',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  COLUMN WIDTHS – Sr & Emp Code = 7, Others = 13                    */
    /* ------------------------------------------------------------------ */
    public function columnWidths(): array
    {
        $headings = $this->headings();
        $widths = [];

        foreach ($headings as $i => $heading) {
            $col = Coordinate::stringFromColumnIndex($i + 1);
            $widths[$col] = match ($heading) {
                'Sr', 'Employee Code' => 7,
                default => 13,
            };
        }

        return $widths;
    }

    /* ------------------------------------------------------------------ */
    /*  AFTER-SHEET – Full styling (same as GatepassReport)               */
    /* ------------------------------------------------------------------ */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $headings = $this->headings();
                $lastCol = Coordinate::stringFromColumnIndex(count($headings));
                $headingRow = 6;
                $dataRows = $this->collection()->count();
                $lastDataRow = $headingRow + $dataRows;

                // Insert 5 header rows
                $sheet->insertNewRowBefore(1, 5);
                $sheet->setShowGridlines(false);

                // === HEADER CONTENT ===
                $business = Business::find($this->businessId);
                $businessName = $business ? $business->b_name : 'Business';
                $printedOn = Carbon::now()->format('d-M-Y h:i A T');

                $sheet->setCellValue('A1', $businessName);
                $sheet->setCellValue('A2', $this->fileName);
                $sheet->setCellValue('A3', 'Date: ' . Carbon::parse($this->date)->format('d-M-Y'));
                $sheet->setCellValue('A4', "Printed on: {$printedOn}");

                foreach (range(1, 4) as $r) {
                    $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
                    $sheet->getStyle("A{$r}")->getFont()->setSize(11)->setBold(true);
                    $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // === HEADING ROW ===
                $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")
                    ->getFont()->setSize(10)->setBold(true)->getColor()->setRGB('FFFFFF');
                $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")
                    ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('263871');
                $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);
                $sheet->getRowDimension($headingRow)->setRowHeight(30);

                if ($dataRows > 0) {
                    // === DATA ROWS – Fixed height 25 ===
                    for ($r = $headingRow + 1; $r <= $lastDataRow; $r++) {
                        $sheet->getRowDimension($r)->setRowHeight(25);
                    }

                    // === BORDERS ===
                    $sheet->getStyle("A{$headingRow}:{$lastCol}{$lastDataRow}")
                        ->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D3D3D3');

                    // === CENTER + WRAP ALL DATA ===
                    $sheet->getStyle("A" . ($headingRow + 1) . ":{$lastCol}{$lastDataRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setWrapText(true);

                    // === FONT SIZE ===
                    $sheet->getStyle("A" . ($headingRow + 1) . ":{$lastCol}{$lastDataRow}")
                        ->getFont()->setSize(9);

                    // === ALTERNATING ROWS ===
                    for ($r = $headingRow + 1; $r <= $lastDataRow; $r++) {
                        $bg = (($r - $headingRow) % 2 == 0) ? 'F5F5F5' : 'FFFFFF';
                        $sheet->getStyle("A{$r}:{$lastCol}{$r}")
                            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bg);
                    }

                    // === MERGE CELLS ===
                    foreach ($this->mergeCells as $range) {
                        $sheet->mergeCells($range);
                        $sheet->getStyle($range)->getAlignment()
                            ->setVertical(Alignment::VERTICAL_CENTER);
                    }

                    // === CURRENCY FORMAT (Earning, Deduction, Totals) ===
                    $sheet->getStyle("H" . ($headingRow + 1) . ":K{$lastDataRow}")
                        ->getNumberFormat()
                        ->setFormatCode('#,##0.00');
                }

                // === FREEZE PANE ===
                $sheet->freezePane('A7'); // Below heading row (row 6)
                $sheet->freezePane('D7');
            },
        ];
    }
}