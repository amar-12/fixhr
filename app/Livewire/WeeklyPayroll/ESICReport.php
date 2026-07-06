<?php
namespace App\Livewire\WeeklyPayroll;

use App\Livewire\WeeklyPayroll\Concerns\ResolvesWeeklyPayrollReportPeriod;
use App\Models\PayrollPeriod;
use App\Models\ProcessedEmployeeSalary;
use App\Models\ProcessedSalaryDeduction;
use App\Models\ProcessedSalaryEarning;
use App\Models\SalaryEmployeeDeductions;
use App\Models\SalaryEmployeeSalary;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Kreait\Firebase\RemoteConfig\UpdateType;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Facades\Excel;
class ESICReport extends Component
{
    use ResolvesWeeklyPayrollReportPeriod;

    public $selectedPayrollPeriodId;

    public $roundOffValues = false;

    public $businessId;

    /** @var int|null */
    public $selectedWeekId;

    public $showButton = true;

    public $buttonStyle = '';

    public function mount($payrollId = null, $weekId = null)
    {
        $user = Auth::user();
        $this->businessId = $user->emp_b_id;
        $this->selectedPayrollPeriodId = $payrollId;
        $this->selectedWeekId = $weekId;
    }
    public function generateReport()
    {
        if (! $this->validatePayrollReportRequest([
            'selectedPayrollPeriodId' => 'required|exists:payroll_periods,pp_id',
        ], [
            'selectedPayrollPeriodId.required' => 'Payroll period is required.',
        ])) {
            return;
        }
        if (! $this->resolvePayrollForWeeklyReport()) {
            return;
        }
        /* ------------------------------------------------------------------ */
        /* 1. Base query */
        /* ------------------------------------------------------------------ */
        $query = ProcessedEmployeeSalary::with([
            'employee.fh_gender',
            'employee.fh_department',
            'employee.fh_designation',
            'employee.employeeProjects',
        ])
            ->where('ps_payroll_id', $this->selectedPayrollPeriodId)
            ->when($this->selectedWeekId, fn ($q) => $q->where('ps_week_id', $this->selectedWeekId));

        $processedSalary = $query->get();
        if ($processedSalary->isEmpty()) {
            $this->payrollReportSwalNoData('There is no data to download. No processed salary rows exist for this payroll period and week.');

            return;
        }
        /* ------------------------------------------------------------------ */
        /* 2. Pre-load data */
        /* ------------------------------------------------------------------ */
        $psIds   = $processedSalary->pluck('ps_id');
        $empIds  = $processedSalary->pluck('ps_emp_id');
        $allEarnings = ProcessedSalaryEarning::whereIn('ps_id', $psIds)
            ->get()
            ->groupBy('ps_id')
            ->map(fn($g) => $g->pluck('ps_e_amount', 'ps_earning_type'));
        $allDeductions = ProcessedSalaryDeduction::whereIn('ps_id', $psIds)
            ->get()
            ->groupBy('ps_id')
            ->map(fn($g) => $g->pluck('ps_d_amount', 'ps_deduction_type'));
        $allSalary = SalaryEmployeeSalary::where('es_b_id', $this->businessId)
            ->whereIn('es_emp_id', $empIds)
            ->with('salary_earnings.fh_salary_earning_type')
            ->get()
            ->groupBy('es_emp_id');
        $allSalaryDed = SalaryEmployeeDeductions::with('fh_salary_deduction_type')
            ->whereIn('es_d_emp_id', $empIds)
            ->get()
            ->groupBy('es_d_emp_id')
            ->map(fn($g) => $g->pluck('es_d_amount', 'fh_salary_deduction_type.m_name'));
        $employeeSalary = SalaryEmployeeSalary::with('fh_employee')
            ->where('es_b_id', $this->businessId)
            ->whereIn('es_emp_id', $empIds)
            ->get();
        /* ------------------------------------------------------------------ */
        /* 3. ESI filter */
        /* ------------------------------------------------------------------ */
        $threshold = DB::table('statutory_deductions')
            ->where('std_b_id', $this->businessId)
            ->where('std_deduction_type_id', 352)
            ->value('std_threshold');
        $processedSalary = $processedSalary
            ->filter(function ($item) use ($employeeSalary) {
                // 1. Employee record missing
                if (empty($item->employee)) {
                    return false;
                }
                // 2. ESIC must be enabled (120 = enabled)
                if ($item->employee->emp_esic_limit != 120) {
                    return false;
                }
                // 3. ESIC Number must be present
                if (empty($item->employee->emp_esic_no)) {
                    return false;
                }
                // 4. No need to check gross < threshold anymore
                return true;
            })
            ->values();
        if ($processedSalary->isEmpty()) {
            $this->payrollReportSwalNoData('There is no data to download. No ESI-eligible employees (ESIC enabled with ESI number) were found in this selection.');

            return;
        }
        /* ------------------------------------------------------------------ */
        /* 4. Columns */
        /* ------------------------------------------------------------------ */
        $basicInfoColumns = [
            'S#',
            'Emp Code',
            'ESI No.',
            'Employee Name',
            'Gender',
            'Aadhaar No',
            'DOB',
            'DOJ',
            'DOL',
            'Last Working Date',
            'Department',
            'Designation',
            'Days',
            'Arrear Days',
            'Amount on Which ESI Deducted',
            'Arrear Amount on Which ESI Deducted',
            'ESI',
            'Arrear ESI',
            'Employer Contribution',
            'Arrear Employer Contribution',
            'Total',
            'Arrear Total'
        ];
        /* ------------------------------------------------------------------ */
        /* 5. Transform */
        /* ------------------------------------------------------------------ */
        $processedSalary = $processedSalary->transform(function ($item, $key) use ($employeeSalary) {
            $monthlyGross = $item->ps_monthly_gross ?? 0;
            $employeeEsic = $monthlyGross * 0.75 / 100;
            $employerEsic = $monthlyGross * 3.25 / 100;
            // Apply rounding if enabled
            if ($this->roundOffValues) {
                $monthlyGross = round($monthlyGross);
                $employeeEsic = round($employeeEsic);
                $employerEsic = round($employerEsic);
            }
            $row = [
                'S#'                                 => (string) ($key + 1),
                'Emp Code'                           => (string) ($item->employee->emp_code ?? '-'),
                'ESI No.'                            => (string) ($item->employee->emp_esic_no ?? '-'),
                'Employee Name'                      => (string) ($item->employee->emp_full_name ?? '-'),
                'Gender'                             => (string) ($item->employee->fh_gender->m_name ?? '-'),
                'Aadhaar No'                         => (string) ($item->employee->emp_aadhaar_no ?? '-'),
                'DOB'                                => $item->employee->emp_dob ? Carbon::parse($item->employee->emp_dob)->format('d M, Y') : '-',
                'DOJ'                                => $item->employee->emp_date_of_joining ? Carbon::parse($item->employee->emp_date_of_joining)->format('d M, Y') : '-',
                'DOL'                                => '-',
                'Last Working Date'                  => $item->employee->emp_last_working_date ? Carbon::parse($item->employee->emp_last_working_date)->format('d M, Y') : '-',
                'Department'                         => (string) ($item->employee->fh_department->d_name ?? '-'),
                'Designation'                        => (string) ($item->employee->fh_designation->dg_name ?? '-'),
                'Days'                               => number_format($item->ps_total_days_worked ?? 0, 2, '.', ''),
                'Arrear Days'                        => '0.00',
                'Amount on Which ESI Deducted'       => number_format($monthlyGross, 2, '.', ''),
                'Arrear Amount on Which ESI Deducted' => '0.00',
                'ESI'                                => number_format($employeeEsic, 2, '.', ''),
                'Arrear ESI'                         => '0.00',
                'Employer Contribution'              => number_format($employerEsic, 2, '.', ''),
                'Arrear Employer Contribution'       => '0.00',
                'Total'                              => number_format($employeeEsic + $employerEsic, 2, '.', ''),
                'Arrear Total'                       => '0.00',
            ];
            return collect($row)->map('strval')->all();
        });
        /* ------------------------------------------------------------------ */
        /* 6. Export */
        /* ------------------------------------------------------------------ */
        $user        = Auth::user();
        $businessName = $user->fh_business->b_name ?? 'Business';
        $monthName   = optional(PayrollPeriod::find($this->selectedPayrollPeriodId))->pp_name ?? 'Month';
        return Excel::download(new class(
            $processedSalary,
            $businessName,
            $monthName,
            $basicInfoColumns,
            $this->roundOffValues // Pass roundOffValues to export class
        ) implements FromCollection, WithHeadings, WithEvents {
            use Exportable;
            protected $data;
            protected $businessName;
            protected $monthName;
            protected $basicInfoColumns;
            protected $roundOffValues;
            public function __construct($data, $businessName, $monthName, $basicInfoColumns, $roundOffValues)
            {
                $this->data             = $data;
                $this->businessName     = $businessName;
                $this->monthName        = $monthName;
                $this->basicInfoColumns = $basicInfoColumns;
                $this->roundOffValues   = $roundOffValues;
            }
            public function collection()
            {
                $rows = $this->data;
                $grand = collect($this->basicInfoColumns)->mapWithKeys(function ($col) use ($rows) {
                    if (in_array($col, [
                        'S#',
                        'Emp Code',
                        'ESI No.',
                        'Employee Name',
                        'Gender',
                        'Aadhaar No',
                        'DOB',
                        'DOJ',
                        'DOL',
                        'Last Working Date',
                        'Department',
                        'Designation'
                    ])) {
                        return [$col => $col === 'S#' ? 'Grand Totals' : ''];
                    }
                    $sum = $rows->sum(fn($r) => floatval(str_replace(',', '', $r[$col] ?? '0')));
                    $sum = $this->roundOffValues ? round($sum) : $sum;
                    return [$col => $sum > 0 ? number_format($sum, 2, '.', '') : ''];
                })->all();
                $rows->push($grand);
                return $rows;
            }
            public function headings(): array
            {
                return [
                    [$this->businessName],
                    ['ESI REPORT FOR THE MONTH OF ' . strtoupper($this->monthName)],
                    ['Printed on: ' . Carbon::now()->format('d-M-Y h:i A T')],
                    array_fill(0, count($this->basicInfoColumns), ''),
                    $this->basicInfoColumns,
                ];
            }
            public function registerEvents(): array
            {
                return [
                    AfterSheet::class => function (AfterSheet $event) {
                        $sheet = $event->sheet->getDelegate();
                        $highestRow = $sheet->getHighestRow();
                        $dataCols = count($this->basicInfoColumns);
                        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($dataCols);
                        $dataRange = "A6:{$lastCol}{$highestRow}";
                        /* ==================== COLUMN WIDTHS ==================== */
                        foreach (range(1, $dataCols) as $i) {
                            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
                            $header = $sheet->getCell("{$col}5")->getValue();
                            $width = in_array($header, ['S#', 'Emp Code']) ? 7 : 13;
                            $sheet->getColumnDimension($col)->setWidth($width);
                        }
                        /* ==================== ROW HEIGHTS ==================== */
                        for ($r = 6; $r <= $highestRow; $r++) {
                            $sheet->getRowDimension($r)->setRowHeight(25);
                        }
                        /* ==================== TEXT WRAP ==================== */
                        $sheet->getStyle($dataRange)->getAlignment()->setWrapText(true);
                        $sheet->getStyle("A5:{$lastCol}5")->getAlignment()->setWrapText(true);
                        /* ==================== TITLE (1-3) ==================== */
                        $highestCol = $sheet->getHighestColumn();
                        foreach (range(1, 3) as $r) {
                            $sheet->mergeCells("A{$r}:{$highestCol}{$r}");
                            $sheet->getStyle("A{$r}")->getFont()->setBold(true)->setSize(11);
                            $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                        }
                        $sheet->setCellValue('A1', $this->businessName);
                        $sheet->setCellValue('A2', 'ESI REPORT FOR THE MONTH OF ' . strtoupper($this->monthName));
                        $sheet->setCellValue('A3', 'Printed on: ' . Carbon::now()->format('d-M-Y h:i A T'));
                        // Add round-off info to header if enabled
                        if ($this->roundOffValues) {
                            $sheet->setCellValue('A4', "Note: All monetary values are rounded to nearest whole number");
                            $sheet->mergeCells("A4:{$highestCol}4");
                            $sheet->getStyle("A4")->getFont()->setSize(10)->setBold(true)->setItalic(true);
                            $sheet->getStyle("A4")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                        }
                        /* ==================== HEADER (5) ==================== */
                        $sheet->getStyle("A5:{$lastCol}5")
                            ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('263871');
                        $sheet->getStyle("A5:{$lastCol}5")
                            ->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('FFFFFF');
                        $sheet->getStyle("A5:{$lastCol}5")
                            ->getAlignment()
                            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                        $sheet->setShowGridlines(false);
                        /* ==================== MERGE ROW 4 ==================== */
                        if ($dataCols > 1) {
                            $start = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(1);
                            $end   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($dataCols);
                            $sheet->mergeCells("{$start}4:{$end}4");
                        }
                        /* ==================== DATA STYLE ==================== */
                        $sheet->getStyle($dataRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle($dataRange)->getFont()->setSize(8);
                        /* ==================== BORDERS ==================== */
                        $sheet->getStyle("A5:{$lastCol}{$highestRow}")
                            ->getBorders()
                            ->getAllBorders()
                            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                            ->getColor()->setRGB('D3D3D3');
                        /* ==================== CURRENCY FORMAT ==================== */
                        $moneyCols = [
                            'Days',
                            'Arrear Days',
                            'Amount on Which ESI Deducted',
                            'Arrear Amount on Which ESI Deducted',
                            'ESI',
                            'Arrear ESI',
                            'Employer Contribution',
                            'Arrear Employer Contribution',
                            'Total',
                            'Arrear Total'
                        ];
                        foreach ($moneyCols as $col) {
                            $idx = array_search($col, $this->basicInfoColumns);
                            if ($idx !== false) {
                                $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 1);
                                $sheet->getStyle("{$letter}6:{$letter}{$highestRow}")
                                    ->getNumberFormat()
                                    ->setFormatCode('#,##0.00');
                            }
                        }
                        /* ==================== ALTERNATING ROWS ==================== */
                        $numRows = $this->data->count();
                        for ($i = 0; $i < $numRows; $i++) {
                            $row = 6 + $i;
                            $color = $i % 2 === 0 ? 'F5F5F5' : 'FFFFFF';
                            $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                                ->getFill()
                                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                ->getStartColor()->setRGB($color);
                        }
                        /* ==================== GRAND TOTAL ==================== */
                        $grandRow = 6 + $numRows;
                        $sheet->getStyle("A{$grandRow}:{$lastCol}{$grandRow}")
                            ->getFont()->setBold(true)->setSize(9);
                        $sheet->getStyle("A{$grandRow}:{$lastCol}{$grandRow}")
                            ->getFill()
                            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('FFD9D9D9');
                        $sheet->getStyle("A{$grandRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                        /* ==================== ROW HEIGHTS ==================== */
                        foreach (range(1, 3) as $r) $sheet->getRowDimension($r)->setRowHeight(20);
                        $sheet->getRowDimension(4)->setRowHeight(15);
                        $sheet->getRowDimension(5)->setRowHeight(30);
                        $sheet->freezePane('A6');
                    },
                ];
            }
        }, 'ESIC_Report_' . $monthName . '.xlsx');
    }
    public function render()
    {
        return view('livewire.weekly-payroll.e-s-i-c-report');
    }
}
