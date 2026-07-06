<?php
namespace App\Exports\Combined;

use App\Models\Employee;
use App\Models\PayrollPeriod;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

class BankSheetSheet implements FromCollection, WithTitle, WithHeadings, WithEvents, WithCustomStartCell, WithColumnWidths
{
    protected $payrollPeriodId;
    protected $businessId;
    protected $roundOffValues;
    protected $data;
    protected $business;
    protected $payroll;
    protected $amountCheck = 0;

    public function __construct($payrollPeriodId, $businessId, $roundOffValues)
    {
        $this->payrollPeriodId = $payrollPeriodId;
        $this->businessId = $businessId;
        $this->roundOffValues = $roundOffValues;

        $this->initializeData();
    }

    protected function initializeData()
    {
        // Get payroll details
        $this->payroll = PayrollPeriod::find($this->payrollPeriodId);

        if (!$this->payroll) {
            $this->data = collect();
            return;
        }

        // Get business details
        $this->business = DB::table('businesses')->where('b_id', $this->businessId)->first();

        // Get employee data with processed salaries - exactly like BankSheetReport component
        $this->data = Employee::with([
                'processedSalaries' => function ($q) {
                    $q->where('ps_payroll_id', $this->payrollPeriodId);
                },
            ])
            ->whereHas('processedSalaries', function ($q) {
                $q->where('ps_payroll_id', $this->payrollPeriodId);
            })
            ->where('emp_b_id', $this->businessId)
            ->get();
    }

    public function title(): string
    {
        return 'Bank Sheet';
    }

    public function collection()
    {
        if ($this->data->isEmpty()) {
            return collect([['No data available for the selected payroll period']]);
        }

        $rows = collect();
        $serialNo = 1;
        $totalAmount = 0;

        foreach ($this->data as $employee) {
            $processedSalary = $employee->processedSalaries->first();
            if (!$processedSalary) continue;

            // Get net salary - using ps_monthly_net_salary like in your other components
            $netSalary = $processedSalary->ps_monthly_net_salary ?? 0;
            $netSalary = $this->roundOffValues ? round($netSalary) : $netSalary;

            // Format account number to prevent Excel from converting to scientific notation
            $accountNo = $employee->emp_bank_account_no;
            $formattedAccountNo = preg_match('/^\d+$/', $accountNo) ? "'" . $accountNo : $accountNo;

            $rows->push([
                $serialNo++,
                $employee->emp_code ?? '',
                $employee->emp_full_name ?? '',
                $employee->emp_bank_name ?? '',
                $formattedAccountNo ?? '',
                $employee->emp_bank_ifsc_code ?? '',
                $employee->emp_paymentmode ?? '',
                $netSalary,
            ]);

            $totalAmount += $netSalary;
        }

        // Add empty row for spacing
        $rows->push(['', '', '', '', '', '', '', '']);

        // Add total row - exactly like BankSheetReport
        $rows->push([
            '',
            '',
            '',
            '',
            '',
            '',
            'Total Amount:',
            $totalAmount,
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'S.No',
            'Employee Code',
            'Employee Name',
            'Bank Name',
            'Account No.',
            'IFSC Code',
            'Payment Mode',
            'Amount',
        ];
    }

    public function startCell(): string
    {
        return 'A6';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 15,
            'C' => 25,
            'D' => 20,
            'E' => 20,
            'F' => 15,
            'G' => 15,
            'H' => 15,
        ];
    }

    public function registerEvents(): array
    {
        if ($this->data->isEmpty()) {
            return [];
        }

        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestColumn = $sheet->getHighestColumn();
                $highestRow = $sheet->getHighestRow();
                $printed = Carbon::now()->format('d-M-Y h:i A T');
                $businessName = $this->business->b_name ?? 'Business';
                $monthName = $this->payroll->pp_name ?? 'Month';

                // Header styling - exactly like BankSheetReport
                foreach (range(1, 4) as $r) {
                    $sheet->mergeCells("A{$r}:{$highestColumn}{$r}");
                    $sheet->getStyle("A{$r}")->getFont()->setBold(true)->setSize(11);
                    $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                $sheet->setCellValue('A1', $businessName);
                $sheet->setCellValue('A2', 'Bank Sheet Report');
                $sheet->setCellValue('A3', "For the month of {$monthName}");
                $sheet->setCellValue('A4', "Printed on: {$printed}");

                // Add round-off info if enabled
                if ($this->roundOffValues) {
                    $sheet->setCellValue('A5', "Note: All monetary values are rounded to nearest whole number");
                    $sheet->mergeCells("A5:{$highestColumn}5");
                    $sheet->getStyle("A5")->getFont()->setSize(10)->setBold(true)->setItalic(true);
                    $sheet->getStyle("A5")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // Style the header row
                $sheet->getStyle("A6:{$highestColumn}6")
                    ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('263871');
                $sheet->getStyle("A6:{$highestColumn}6")
                    ->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('FFFFFF');
                $sheet->getStyle("A6:{$highestColumn}6")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);

                $sheet->freezePane('A7');
                $sheet->setShowGridlines(false);

                // Style data rows (excluding total row)
                $lastDataRow = $highestRow - 2; // Subtract header row and total row
                if ($lastDataRow >= 7) {
                    $dataRange = "A7:{$highestColumn}{$lastDataRow}";
                    $sheet->getStyle($dataRange)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setWrapText(true);
                    $sheet->getStyle($dataRange)->getFont()->setSize(9);

                    // Alternate row colors
                    $numRows = $this->data->count();
                    for ($i = 0; $i < $numRows; $i++) {
                        $row = 7 + $i;
                        $color = $i % 2 === 0 ? 'F5F5F5' : 'FFFFFF';
                        $sheet->getStyle("A{$row}:{$highestColumn}{$row}")
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB($color);
                    }
                }

                // Add borders to entire table
                $sheet->getStyle("A6:{$highestColumn}{$highestRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->getColor()->setRGB('D3D3D3');

                // Style total row
                $totalRow = $highestRow;
                $sheet->getStyle("A{$totalRow}:{$highestColumn}{$totalRow}")
                    ->getFont()->setBold(true)->setSize(10);
                $sheet->getStyle("G{$totalRow}:H{$totalRow}")
                    ->getFont()->setBold(true)->setSize(10);
                $sheet->getStyle("A{$totalRow}:{$highestColumn}{$totalRow}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('FFD9D9D9');

                // Align total text to right
                $sheet->getStyle("G{$totalRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Format amount column as currency
                $sheet->getStyle("H7:H{$totalRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                // Set row heights
                foreach (range(1, 4) as $r) {
                    $sheet->getRowDimension($r)->setRowHeight(20);
                }
                $sheet->getRowDimension(6)->setRowHeight(25);

                $lastDataRow = $highestRow - 2;
                for ($r = 7; $r <= $lastDataRow; $r++) {
                    $sheet->getRowDimension($r)->setRowHeight(20);
                }
                $sheet->getRowDimension($highestRow)->setRowHeight(25);
            },
        ];
    }
}
