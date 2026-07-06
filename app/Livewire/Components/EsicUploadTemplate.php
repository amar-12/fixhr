<?php

namespace App\Livewire\Components;

use App\Models\Business;
use App\Models\Employee;
use App\Models\FinancialYear;
use App\Models\MasterTable;
use App\Models\PayrollPeriod;
use App\Models\ProcessedEmployeeSalary;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ESICUploadTemplate extends Component
{
    public $selectedPayrollPeriodId;
    public $roundOffValues = false;
    public $businessId;

     public function mount($payrollId = null)
    {

        $user = Auth::user();
        $this->businessId = $user->emp_b_id;
        $this->selectedPayrollPeriodId = $payrollId;

    }
    public function generateReport()
    {
        $this->validate([

            'selectedPayrollPeriodId' => 'required|exists:payroll_periods,pp_id',
        ], [

            'selectedPayrollPeriodId.required' => 'Payroll period is required.',
        ]);



        try {
            $user = Auth::user();
            $business_id = $user->emp_b_id;
            $business = Business::where('b_id', $business_id)->select('b_name')->first();
            $businessName = $business?->b_name ?? 'Unknown Business';

            $payroll = PayrollPeriod::where('pp_id', $this->selectedPayrollPeriodId)
                ->where('pp_b_id', $business_id)
                ->first();


            if (!$payroll) {
                $this->dispatch('show-alert', [
                    'type' => 'error',
                    'message' => 'Invalid Payroll Period'
                ]);

                return;
            }

            $esicThreshold = \App\Models\StatutoryDeduction::where('std_b_id', $business_id)
                ->where('std_deduction_type_id', 352)
                ->value('std_threshold') ?? 21000;



            $empEsicData = ProcessedEmployeeSalary::with([
                'employee' => fn($q) => $q->select('emp_id', 'emp_full_name', 'emp_esic_no', 'emp_status')
            ])
                ->where('ps_payroll_id', $this->selectedPayrollPeriodId)
                ->where('ps_monthly_gross', '<', $esicThreshold)
                ->get();

            if ($empEsicData->isEmpty()) {

                $this->dispatch('show-alert', [
                    'type' => 'error',
                    'message' => 'No records found for the selected criteria.'
                ]);

                return;
            }

            $fileName = 'ESIC_Monthly_Contribution_' . $payroll->pp_name . '.xlsx';


            // Generate and download the Excel file
            return Excel::download(
                new class($businessName, $payroll, $empEsicData, $this->roundOffValues) implements WithMultipleSheets {
                    protected $businessName, $payroll, $employees, $roundOffValues;

                    public function __construct($businessName, $payroll, $employees, $roundOffValues)
                    {
                        $this->businessName = $businessName;
                        $this->payroll = $payroll;
                        $this->employees = $employees;
                        $this->roundOffValues = $roundOffValues;
                    }

                    public function sheets(): array
                    {
                        return [
                            new class($this->businessName, $this->payroll, $this->employees, $this->roundOffValues)
                                implements FromCollection, WithHeadings, WithCustomStartCell, WithEvents {

                                protected $businessName, $payroll, $employees, $roundOffValues;

                                public function __construct($businessName, $payroll, $employees, $roundOffValues)
                                {
                                    $this->businessName = $businessName;
                                    $this->payroll = $payroll;
                                    $this->employees = $employees;
                                    $this->roundOffValues = $roundOffValues;
                                }

                                public function startCell(): string
                                {
                                    return $this->roundOffValues ? 'A7' : 'A6';
                                }

                                public function collection()
                                {
                                    return collect($this->employees)->map(function ($salary, $index) {
                                        $emp = $salary->employee;
                                        $monthlyWages = $salary->ps_esic_monthly_gross ?? 0;

                                        if ($this->roundOffValues) {
                                            $monthlyWages = round($monthlyWages);
                                        }

                                        return [
                                            'S#' => $index + 1,
                                            'IP Number (10 Digits)' => $emp->emp_esic_no ?? '',
                                            'IP Name (Only alphabets and space)' => $emp->emp_full_name ?? '',
                                            'No of Days for which wages paid/payable during the month' => $salary->ps_esic_worked_days ?? 0,
                                            'Total Monthly Wages' => $monthlyWages,
                                            'Reason Code for Zero workings days' => '',
                                            'Last Working Day (Format DD/MM/YYYY)' => '',
                                        ];
                                    });
                                }

                                public function headings(): array
                                {
                                    return [
                                        'S#',
                                        'IP Number (10 Digits)',
                                        'IP Name (Only alphabets and space)',
                                        'No of Days for which wages paid/payable during the month',
                                        'Total Monthly Wages',
                                        'Reason Code for Zero workings days',
                                        'Last Working Day (Format DD/MM/YYYY)',
                                    ];
                                }

                                public function registerEvents(): array
                                {
                                    return [
                                        AfterSheet::class => function (AfterSheet $event) {
                                            $sheet = $event->sheet->getDelegate();
                                            $headings = $this->headings();
                                            $lastCol = Coordinate::stringFromColumnIndex(count($headings));
                                            $dataRows = $this->collection()->count();

                                            $sheet->setShowGridlines(false);
                                            $printedOn = Carbon::now()->format('d-M-Y h:i A T');
                                            $reportDate = Carbon::now()->format('d-M-Y');

                                            $sheet->setCellValue('A1', $this->businessName);
                                            $sheet->setCellValue('A2', 'ESIC Monthly Contribution Report');
                                            $sheet->setCellValue('A3', 'Payroll Period: ' . ($this->payroll->pp_name ?? 'N/A'));
                                            $sheet->setCellValue('A4', "Printed on: {$printedOn}");
                                            $sheet->setCellValue('A5', "Date: {$reportDate}");

                                            $headingRow = 6;

                                            if ($this->roundOffValues) {
                                                $sheet->setCellValue('A6', "Note: All monetary values are rounded to nearest whole number");
                                                $sheet->mergeCells("A6:{$lastCol}6");
                                                $sheet->getStyle("A6")->getFont()->setSize(10)->setBold(true)->setItalic(true);
                                                $sheet->getStyle("A6")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                                                $headingRow = 7;
                                            }

                                            foreach (range(1, 5) as $r) {
                                                $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
                                                $sheet->getStyle("A{$r}")->getFont()->setSize(11)->setBold(true);
                                                $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                                            }

                                            $col = 'A';
                                            foreach ($this->headings() as $heading) {
                                                $sheet->setCellValue($col . $headingRow, $heading);
                                                $col++;
                                            }

                                            $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")
                                                ->getFont()->setSize(10)->setBold(true)->getColor()->setRGB('FFFFFF');
                                            $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")
                                                ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('263871');
                                            $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")
                                                ->getAlignment()
                                                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                                                ->setVertical(Alignment::VERTICAL_CENTER)
                                                ->setWrapText(true);
                                            $sheet->getRowDimension($headingRow)->setRowHeight(45);

                                            $dataStartRow = $this->roundOffValues ? 8 : 7;
                                            $lastDataRow = $dataStartRow + $dataRows - 1;

                                            if ($dataRows > 0) {
                                                for ($r = $dataStartRow; $r <= $lastDataRow; $r++) {
                                                    $sheet->getRowDimension($r)->setRowHeight(25);
                                                }
                                                $sheet->getStyle("A{$headingRow}:{$lastCol}{$lastDataRow}")
                                                    ->getBorders()->getAllBorders()
                                                    ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D3D3D3');
                                                $sheet->getStyle("A{$dataStartRow}:{$lastCol}{$lastDataRow}")
                                                    ->getAlignment()
                                                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                                                    ->setVertical(Alignment::VERTICAL_CENTER)
                                                    ->setWrapText(true);
                                                $sheet->getStyle("A{$dataStartRow}:{$lastCol}{$lastDataRow}")
                                                    ->getFont()->setSize(9);
                                                for ($r = $dataStartRow; $r <= $lastDataRow; $r++) {
                                                    $bg = (($r - $dataStartRow) % 2 == 0) ? 'F5F5F5' : 'FFFFFF';
                                                    $sheet->getStyle("A{$r}:{$lastCol}{$r}")
                                                        ->getFill()->setFillType(Fill::FILL_SOLID)
                                                        ->getStartColor()->setRGB($bg);
                                                }
                                            }

                                            $widths = ['A' => 5, 'B' => 20, 'C' => 30, 'D' => 35, 'E' => 25, 'F' => 60, 'G' => 35];
                                            foreach ($widths as $col => $width) {
                                                $sheet->getColumnDimension($col)->setWidth($width);
                                            }

                                            $sheet->freezePane("A{$dataStartRow}");
                                        },
                                    ];
                                }
                            },
                        ];
                    }
                },
                $fileName
            );

        } catch (\Exception $e) {
            $this->dispatch('show-alert', [
                'type' => 'error',
                'message' => 'Error generating report: ' . $e->getMessage()
            ]);
        } finally {

        }
    }

    public function render()
    {

        return view('livewire.components.esic-upload-template');
    }
}
