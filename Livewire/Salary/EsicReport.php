<?php

namespace App\Livewire\Salary;

use Livewire\Component;
use App\Models\Business;
use App\Models\FinancialYear;
use App\Models\PayrollPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\SalaryEmployeeSalary;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\ProcessedSalaryEarning;
use App\Models\SalaryEmployeeEarnings;
use App\Models\ProcessedEmployeeSalary;
use App\Models\ProcessedSalaryDeduction;
use App\Models\SalaryEmployeeDeductions;
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
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EsicReport extends Component
{

    public $selectedPayrollPeriodId;
    public $searchPayroll = '';
    public $businessId;
    public $selectedFYId;
    public $searchFY = '';

    public $selectedEmployeeDetailsHeading;
    public $selectedEarningHeading;
    public $selectedDeductionHeading;
    public $selectedDaysInfoHeading;
    public $selectedEmployeeEarningsHeading;
    public $selectedEmployeeDeductionsHeading;
    public $selectedPreEarningHeading;
    public $selectedPostEarningHeading;

    public $selectedNetPayHeading;
    public function mount()
    {
        $user = Auth::user();
        $this->businessId = $user->emp_b_id;

        $this->selectedFYId = FinancialYear::where('fy_b_id', $user->emp_b_id)->where('fy_is_current', 1)->first();

        $this->selectFY($this->selectedFYId->fy_id, $this->selectedFYId->fy_year);
        // dd($this->searchFY);
    }
    public function selectPayrollPeriod($id, $name)
    {
        $this->selectedPayrollPeriodId = $id;
        $this->searchPayroll = $name;
    }
    public function selectFY($id, $name)
    {
        $this->selectedFYId = $id;
        $this->searchFY = $name;
    }
    public function updated($property, $value)
    {
        if ($property === 'searchFY') {

            $this->selectedPayrollPeriodId = null;
            $this->selectedFYId = null;
            $this->searchPayroll = '';
            return;
        }

        // Generic mapping of search fields to selected fields
        $mapping = [
            'searchPayroll' => 'selectedPayrollPeriodId',
            'searchFY' => 'selectedFYId',
        ];
        // Apply the generic reset logic
        if (array_key_exists($property, $mapping)) {
            $this->{$mapping[$property]} = null;
        }
    }
    public $filters = [
        'employeeDetails' => false,
        'employeeEarnings' => false,
        'employeeDeductions' => false,
        'daysInfo' => false,
        'earningComponents' => false,
        'deductionComponents' => false,
    ];
    public function toggleFilter($key, $state)
    {
        if (!array_key_exists($key, $this->filters)) return;
        $this->filters[$key] = filter_var($state, FILTER_VALIDATE_BOOLEAN);
        if ($this->filters[$key]) {
            match ($key) {
                'employeeDetails' => $this->selectedEmployeeDetailsHeading = 'Employee Details',
                'employeeEarnings' => $this->selectedEmployeeEarningsHeading = '',
                'employeeDeductions' => $this->selectedEmployeeDeductionsHeading = '',
                'daysInfo' => $this->selectedDaysInfoHeading = 'Attendance Summary',
                'earningComponents' => $this->selectedEarningHeading = 'Earning Components',
                'deductionComponents' => $this->selectedDeductionHeading = 'Deduction Components',
                default => null,
            };
        } else {
            match ($key) {
                'employeeDetails' => $this->selectedEmployeeDetailsHeading = null,
                'employeeEarnings' => $this->selectedEmployeeEarningsHeading = null,
                'employeeDeductions' => $this->selectedEmployeeDeductionsHeading = null,
                'daysInfo' => $this->selectedDaysInfoHeading = null,
                'earningComponents' => $this->selectedEarningHeading = null,
                'deductionComponents' => $this->selectedDeductionHeading = null,
                'preEarning' => $this->selectedPreEarningHeading = null,
                'postEarning' => $this->selectedPostEarningHeading = null,
                // 'deductions' => $this->selectedDeductionHeading = null,
                'netPay' => $this->selectedNetPayHeading = null,
                default => null,
            };
        }
    }



    public function render()
    {
        $financialYears = FinancialYear::where('fy_b_id', $this->businessId)->when($this->searchFY, function ($q) {
            $q->where('fy_year', 'like', "%{$this->searchFY}%");
        })->get();
        $payrollPeriods = PayrollPeriod::where('pp_b_id', $this->businessId)
            ->when($this->selectedFYId, function ($q) {
                $q->where('pp_fy_id', $this->selectedFYId);
            })
            ->when(
                strlen($this->searchPayroll) >= 1 && !$this->selectedPayrollPeriodId,
                fn($q) => $q->where('pp_name', 'like', "%{$this->searchPayroll}%")
            )
            ->limit(100)
            ->get();
        return view('livewire.salary.esic-report', compact('financialYears', 'payrollPeriods'));
    }

    public function generateReport()
    {
        $this->validate([
            'selectedFYId' => 'required|exists:financial_years,fy_id',
            'selectedPayrollPeriodId' => 'required|exists:payroll_periods,pp_id',

        ], [
            'selectedFYId.required' => 'Financial year is required.',
            'selectedPayrollPeriodId.required' => 'Payroll period is required.',
        ]);
        $payroll = PayrollPeriod::where('pp_b_id', $this->businessId)->where('pp_is_processed', 120)->find($this->selectedPayrollPeriodId);
        if (!$payroll) {
            return back()->with('error', 'Invalid Payroll Period');
        }
        $query = ProcessedEmployeeSalary::with('employee.fh_employee_status', 'earnings', 'deductions', 'employee.employeeProjects')
            ->where('ps_payroll_id', $this->selectedPayrollPeriodId);

        $processedSalary = $query->get();
        // dd($processedSalary);
        if ($processedSalary->isEmpty()) {
            session()->flash('error', 'No records found for the selected criteria.');
            return;
        }
        $employeeSalary = SalaryEmployeeSalary::with('fh_employee')
            ->where('es_b_id', $this->businessId)
            ->whereIn('es_emp_id', $processedSalary->pluck('ps_emp_id'))
            ->get();

        // Reordered columns: Basic info -> Salary Master/Deductions -> Attendance Summary -> Processed components
        $basicInfoColumns = [
            'S No.',
            'Emp Code',
            'Machine Code',
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
            // 'Cadre',
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
        $daysInfoColumns = ['Days In Month', 'Workable Days', 'Days Worked', 'Present Days', 'Late Count', 'WeekOffs', 'UPL'];
        $processedSalary = $processedSalary
            ->filter(function ($item) use ($employeeSalary) {
                // Condition 1: ESIC enabled check
                if (
                    empty($item->employee) ||
                    $item->employee->emp_esic_limit == 121 ||
                    $item->employee->emp_esic_no === null
                ) {
                    return false;
                }
                // Get threshold value for this business
                $threshold = DB::table('statutory_deductions')
                    ->where('std_b_id', $item->ps_b_id)
                    ->where('std_deduction_type_id', 352)
                    ->value('std_threshold'); // actual column for threshold
                    // dd($threshold);

                if ($threshold === null) {
                    return false; // no threshold configured
                }

                // Get employee's monthly gross
                $empMonthlyGross = optional(
                    $employeeSalary->where('es_emp_id', $item->ps_emp_id)->first()
                )->es_monthly_gross;

                // Condition 2: Only include if monthly gross < threshold
                return $empMonthlyGross !== null && $empMonthlyGross < $threshold;
            })
            ->values() // reset keys so S No. works
            ->transform(function ($item, $key) use ($daysInfoColumns, $employeeSalary) {

                $earningsData = ProcessedSalaryEarning::where('ps_id', $item->ps_id)
                    ->pluck('ps_e_amount', 'ps_earning_type')->toArray();

                $deductionsData = ProcessedSalaryDeduction::where('ps_id', $item->ps_id)
                    ->pluck('ps_d_amount', 'ps_deduction_type')->toArray();

                $salaryRecords = SalaryEmployeeSalary::where('es_emp_id', $item->ps_emp_id)
                    ->where('es_b_id', $item->ps_b_id)
                    ->with('salary_earnings.fh_salary_earning_type')
                    ->get();

                $empSalaryEarningData = [];
                foreach ($salaryRecords as $record) {
                    foreach ($record->salary_earnings as $earning) {
                        $title = optional($earning->fh_salary_earning_type)->sa_title;
                        $amount = $earning->es_e_amount;
                        if ($title) {
                            $empSalaryEarningData[$title] = $amount;
                        }
                    }
                }

                $empSalaryDeductionData = SalaryEmployeeDeductions::where('es_d_emp_id', $item->ps_emp_id)
                    ->with('fh_salary_deduction_type')
                    ->get()
                    ->pluck('es_d_amount', 'fh_salary_deduction_type.m_name')
                    ->toArray();

                $accountNo = $item->employee->emp_bank_account_no;
                $formattedAccountNo = preg_match('/^\d+$/', $accountNo) ? "'$accountNo" : $accountNo;

                $monthlyGross = $item->ps_monthly_gross ?? 0;
                $employeeEsic = $monthlyGross * 0.75 / 100;
                $employerEsic = $monthlyGross * 3.25 / 100;
                $esicNo = $item->employee->emp_esic_no ?? 0;

                return [
                    'S No.' => $key + 1,
                    'Emp Code' => $item->employee->emp_code,
                    'Machine Code' => 'Machine Code',
                    'ESI No.' => preg_match('/^\d+$/', $esicNo) ? "'$esicNo" : $esicNo,
                    'Employee Name' => $item->employee->emp_full_name,
                    'Gender' => $item->employee->fh_gender->m_name ?? '',
                    'Aadhaar No' => $item->employee->emp_aadhaar_no ?? '',
                    'DOB' => $item->employee->emp_dob ? Carbon::parse($item->employee->emp_dob)->format('d M, Y') : '',
                    'DOJ' => $item->employee->emp_date_of_joining ? Carbon::parse($item->employee->emp_date_of_joining)->format('d M, Y') : '',
                    'DOL' => '',
                    'Last Working Date' => $item->employee->emp_last_working_date ? Carbon::parse($item->employee->emp_last_working_date)->format('d M, Y') : '',
                    'Department' => $item->employee->fh_department->d_name ?? '',
                    'Designation' => $item->employee->fh_designation->dg_name ?? '',
                    // 'Cadre' => '',
                    'Days' => $item->ps_total_month_working_days ?? '',
                    'Arrear Days' =>  '',
                    'Amount on Which ESI Deducted' => $item->ps_monthly_gross ?? '',
                    'Arrear Amount on Which ESI Deducted' => '0',
                    'ESI' => $employeeEsic ?? '',
                    'Employer ESI' => $employerEsic ?? '',
                    'Arrear ESI' => '0',
                    'Employer Contribution' => $employerEsic ?? '',
                    'Arrear Employer Contribution' => '0',
                    'Total' => $employeeEsic + $employerEsic,
                    'Arrear Total' => '0'
                ];
            });

        $user = Auth::user();
        $businessName = $user->fh_business->b_name ?? 'Business';
        $monthName = optional(PayrollPeriod::find($this->selectedPayrollPeriodId))->pp_name ?? 'Month';
        $mainHeading = '';
        $employeeSalaryEarningComponentsFiltered = [];
        $employeeSalaryDeductionComponentsFiltered = [];
        $earningsComponentsFiltered = [];
        $deductionsComponentsFiltered = [];
        // Initialize headings
        $employeeSalaryEarningsHeading = null;
        $employeeSalaryDeductionHeading = null;
        $earningsHeading = null;
        $deductionsHeading = null;
        $daysInfoHeading = $this->selectedDaysInfoHeading;
        $postEarningHeading = $this->selectedPostEarningHeading;
        $postDeductionHeading = $this->selectedDeductionHeading;
        $netPayHeading = $this->selectedNetPayHeading;
        // Conditionally assign headings & component arrays

        return Excel::download(new class(
            $processedSalary,
            $businessName,
            $monthName,
            $mainHeading,
            $earningsHeading,
            $deductionsHeading,
            $employeeSalaryEarningsHeading,
            $employeeSalaryDeductionHeading,
            $earningsComponentsFiltered,
            $deductionsComponentsFiltered,
            $employeeSalaryEarningComponentsFiltered,
            $employeeSalaryDeductionComponentsFiltered,
            $basicInfoColumns,
            $daysInfoColumns,
            // $postEarningColumns,
            // $postDeductionColumns,
            $daysInfoHeading,
            $postEarningHeading,
            $postDeductionHeading,
            $netPayHeading
        ) implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents {
            use Exportable;
            protected $data;
            protected $businessName;
            protected $monthName;
            protected $mainHeading;
            protected $earningsHeading;
            protected $deductionsHeading;
            protected $employeeSalaryEarningsHeading;
            protected $employeeSalaryDeductionHeading;
            protected $earningsComponents;
            protected $deductionsComponents;
            protected $employeeSalaryEarningComponents;
            protected $employeeSalaryDeductionComponents;
            protected $basicInfoColumns;
            protected $daysInfoColumns;
            // protected $postEarningColumns;
            // protected $postDeductionColumns;
            protected $daysInfoHeading;
            protected $postEarningHeading;
            protected $postDeductionHeading;
            protected $netPayHeading;
            protected $columnHeaders;
            public function __construct(
                $data,
                $businessName,
                $monthName,
                $mainHeading,
                $earningsHeading,
                $deductionsHeading,
                $employeeSalaryEarningsHeading,
                $employeeSalaryDeductionHeading,
                $earningsComponents,
                $deductionsComponents,
                $employeeSalaryEarningComponents,
                $employeeSalaryDeductionComponents,
                $basicInfoColumns,
                $daysInfoColumns,
                // $postEarningColumns,
                // $postDeductionColumns,
                $daysInfoHeading,
                $postEarningHeading,
                $postDeductionHeading,
                $netPayHeading
            ) {
                $this->data = $data;
                //    dd($this->data);
                $this->businessName = $businessName;
                $this->monthName = $monthName;
                $this->mainHeading = $mainHeading;
                $this->earningsHeading = $earningsHeading;
                $this->deductionsHeading = $deductionsHeading;
                $this->employeeSalaryEarningsHeading = $employeeSalaryEarningsHeading;
                $this->employeeSalaryDeductionHeading = $employeeSalaryDeductionHeading;
                $this->earningsComponents = $earningsComponents;
                $this->deductionsComponents = $deductionsComponents;
                $this->employeeSalaryEarningComponents = $employeeSalaryEarningComponents;
                $this->employeeSalaryDeductionComponents = $employeeSalaryDeductionComponents;
                $this->basicInfoColumns = $basicInfoColumns;
                $this->daysInfoColumns = $daysInfoColumns;
                // $this->postEarningColumns = $postEarningColumns;
                // $this->postDeductionColumns = $postDeductionColumns;
                $this->daysInfoHeading = $daysInfoHeading;
                $this->postEarningHeading = $postEarningHeading;
                $this->postDeductionHeading = $postDeductionHeading;
                $this->netPayHeading = $netPayHeading;
                $this->columnHeaders = $this->buildColumnHeaders();
                //   dd($this->deductionsHeading);
            }
            protected function buildColumnHeaders()
            {
                $headers = $this->basicInfoColumns;
                // Salary Master components
                if ($this->employeeSalaryEarningsHeading) {

                    foreach ($this->employeeSalaryEarningComponents as $col) {
                        $headers[] = 'Salary Master_' . $col;
                    }
                    $headers[] = 'Salary Master_Other Allowance';
                    $headers[] = 'Monthly Gross';
                    $headers[] = 'CTC';
                }
                // Salary Master components
                if ($this->employeeSalaryDeductionHeading) {
                    foreach ($this->employeeSalaryDeductionComponents as $col) {
                        $headers[] = 'Salary Master_' . $col;
                    }
                }
                // Attendance Summary
                if ($this->daysInfoHeading) {
                    foreach ($this->daysInfoColumns as $col) {
                        $headers[] = 'Attendance Summary_' . $col;
                    }
                }
                // Processed Earnings components
                if ($this->earningsHeading) {
                    foreach ($this->earningsComponents as $col) {
                        $headers[] = 'Earnings_' . $col;
                    }
                    $headers[] = 'Monthly CTC';
                    $headers[] = 'Gross Salary';
                }
                // Processed Deductions components
                if ($this->deductionsHeading) {
                    foreach ($this->deductionsComponents as $col) {
                        $headers[] = 'Deductions_' . $col;
                    }
                }

                // $headers[] = 'Net Pay';
                return $headers;
            }
            public function collection()
            {
                $data = collect($this->data);
                $grandTotals = [

                    'S No.' => 'Grand Totals',
                    'Emp Code' =>  '',
                    'Machine Code' => '',
                    'ESI No.' => '',
                    'Employee Name' => '',
                    'Gender' => '',
                    'Aadhaar No' => '',
                    'DOB' => '',
                    'DOJ' => '',
                    'DOL' => '',
                    'Last Working Date' => '',
                    'Department' => '',
                    'Designation' => '',
                    // 'Cadre' => '',
                ];
                $columnHeaders = $this->buildColumnHeaders();
                $filteredData = $data->map(function ($item) use ($columnHeaders) {
                    $filteredItem = [];
                    // Only include columns that are in our headers
                    foreach ($columnHeaders as $col) {
                        $filteredItem[$col] = $item[$col] ?? '';
                    }
                    return $filteredItem;
                });
                // Calculate grand totals only for included numeric columns
                if ($filteredData->count() > 0) {
                    foreach ($filteredData->first() as $col => $value) {
                        if (!in_array($col, ['S No.', 'Emp Code', 'Title', 'Employee Name', 'Project', 'Region', 'Designation', 'Grade', 'Personal Email', 'Personal Mobile', 'Official Mobile', 'Work Email', 'Date of Joining', 'Service Length as on Payroll Date', 'Payment Model', 'Emp Bank Name', 'Bank IFSC', 'Account No.'])) {
                            // Only sum numeric values (handle both string and numeric formats)
                            $grandTotals[$col] = is_numeric(str_replace(',', '', $value))
                                ? $filteredData->sum(function ($item) use ($col) {
                                    return is_numeric($item[$col]) ? $item[$col] : 0;
                                })
                                : '';
                        }
                    }
                }
                $filteredData->push($grandTotals);
                return $filteredData;
            }
            public function headings(): array
            {
                $headings = [
                    [$this->businessName],
                    ["ESI Report For The Month Of " . strtoupper($this->monthName)],
                    [$this->mainHeading],
                ];
                // Initialize headers based on buildColumnHeaders()
                $columnHeaders = $this->buildColumnHeaders();
                $componentHeaders = [];
                // Build component headers based on actual columns
                foreach ($columnHeaders as $col) {
                    if (in_array($col, $this->basicInfoColumns)) {
                        $componentHeaders[] = '';
                        continue;
                    }
                    if (str_starts_with($col, 'Salary Master_')) {
                        $componentHeaders[] = $this->employeeSalaryEarningsHeading;
                    } elseif ($col === 'Monthly Gross') {
                        $componentHeaders[] = '';
                    } elseif ($col === 'Salary Master_OtherAllowance') {
                        $componentHeaders[] = 'Salary Master Other Allowance';
                    } elseif (str_starts_with($col, 'Salary Master_')) {
                        $componentHeaders[] = $this->employeeSalaryDeductionHeading;
                    } elseif (str_starts_with($col, 'Attendance Summary_')) {
                        $componentHeaders[] = $this->daysInfoHeading;
                    } elseif (str_starts_with($col, 'Earnings_')) {
                        $componentHeaders[] = $this->earningsHeading;
                    } elseif ($col === 'Gross Salary' || $col === 'Net Pay' || $col === 'Monthly CTC') {
                        $componentHeaders[] = '';
                    } elseif (str_starts_with($col, 'Deductions_')) {
                        $componentHeaders[] = $this->deductionsHeading;
                    } else {
                        $componentHeaders[] = '';
                    }
                }
                // Clean column headers by removing prefixes
                $cleanColumnHeaders = array_map(function ($col) {
                    if (str_starts_with($col, 'Salary Master_')) {
                        return substr($col, strlen('Salary Master_'));
                    }
                    if (str_starts_with($col, 'Salary Master_')) {
                        return substr($col, strlen('Salary Master_'));
                    }
                    if (str_starts_with($col, 'Attendance Summary_')) {
                        return substr($col, strlen('Attendance Summary_'));
                    }
                    if (str_starts_with($col, 'Earnings_')) {
                        return substr($col, strlen('Earnings_'));
                    }
                    if (str_starts_with($col, 'Deductions_')) {
                        return substr($col, strlen('Deductions_'));
                    }
                    return $col;
                }, $columnHeaders);
                $headings[] = $componentHeaders;
                $headings[] = $cleanColumnHeaders;
                return $headings;
            }
            public function registerEvents(): array
            {
                return [
                    AfterSheet::class => function (AfterSheet $event) {
                        $sheet = $event->sheet->getDelegate();
                        $highestColumn = $sheet->getHighestColumn();
                        $highestRow = $sheet->getHighestRow();
                        // Helper function for safe merging
                        $safeMerge = function ($start, $end) use ($sheet) {
                            if ($start !== $end) {
                                $sheet->mergeCells("$start:$end");
                            }
                        };
                        // Merge and style header rows
                        $sheet->mergeCells('A1:' . $highestColumn . '1');
                        $sheet->mergeCells('A2:' . $highestColumn . '2');
                        $sheet->mergeCells('A3:' . $highestColumn . '3');
                        // Get column headers to determine sections
                        $columnHeaders = $this->buildColumnHeaders();
                        $currentCol = 1; // Start from column A (1)
                        // Group headers by section
                        $sections = [];
                        $currentSection = null;
                        $sectionStart = 1; // Initialize sectionStart
                        foreach ($columnHeaders as $col) {
                            $section = $this->determineSection($col);
                            if ($section !== $currentSection) {
                                if ($currentSection !== null) {
                                    $sections[] = [
                                        'name' => $currentSection,
                                        'start' => $sectionStart,
                                        'end' => $currentCol - 1
                                    ];
                                }
                                $currentSection = $section;
                                $sectionStart = $currentCol; // Reset section start for new section
                            }
                            $currentCol++;
                        }
                        // Add the last section if it exists
                        if ($currentSection !== null) {
                            $sections[] = [
                                'name' => $currentSection,
                                'start' => $sectionStart,
                                'end' => $currentCol - 1
                            ];
                        }
                        // Merge section headers in row 4
                        foreach ($sections as $section) {
                            if ($section['start'] !== $section['end']) {
                                $start = Coordinate::stringFromColumnIndex($section['start']) . '4';
                                $end = Coordinate::stringFromColumnIndex($section['end']) . '4';
                                $safeMerge($start, $end);
                            }
                        }
                        $this->applyStyles($sheet, $highestColumn, $highestRow);
                    }
                ];
            }
            protected function determineSection($column)
            {
                if (in_array($column, $this->basicInfoColumns)) {
                    return '';
                }
                if (str_starts_with($column, 'Salary Master_')) {
                    return $this->employeeSalaryEarningsHeading;
                }
                if ($column === 'Monthly Gross') {
                    return 'Employee Totals';
                }
                if (str_starts_with($column, 'Salary Master_')) {
                    return $this->employeeSalaryDeductionHeading;
                }
                if (str_starts_with($column, 'Attendance Summary_')) {
                    return $this->daysInfoHeading;
                }
                if (str_starts_with($column, 'Earnings_')) {
                    return $this->earningsHeading;
                }
                if ($column === 'Gross Salary' || $column === 'Net Pay' || $column === 'Monthly CTC') {
                    return '';
                }
                if (str_starts_with($column, 'Deductions_')) {
                    return $this->deductionsHeading;
                }
                return '';
            }
            protected function applyStyles(Worksheet $sheet, string $highestColumn, int $highestRow)
            {

                $sheet->getParent()
                    ->getDefaultStyle()
                    ->getFont()
                    ->setName('Calibri')
                    ->setSize(11); // Optional: set font size

                // Header styles (rows 1-2)
                $sheet->getStyle('A1:' . $highestColumn . '3')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'font' => [
                        'bold' => true,
                    ]
                ]);

                // Header styles (rows 1-2)
                $sheet->getStyle('A3:' . $highestColumn . '3')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'font' => [
                        'bold' => true,
                    ]
                ]);

                // Component headers (row 4)
                $sheet->getStyle('A4:' . $highestColumn . '4')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 10,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                // Column headers (row 5)
                $sheet->getStyle('A5:' . $highestColumn . '5')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 8,
                    ],
                    'alignment' => [
                        'wrapText' => true,
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);
                // Data rows
                // $sheet->getStyle('A6:' . $highestColumn . ($highestRow - 1))->applyFromArray([
                //     'borders' => [
                //         'allBorders' => [
                //             'borderStyle' => Border::BORDER_THIN,
                //         ],
                //     ],
                // ]);
                // Grand total row
                $sheet->getStyle('A' . $highestRow . ':' . $highestColumn . $highestRow)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 8,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['argb' => 'FFD9D9D9'],
                    ],
                    // 'borders' => [
                    //     'allBorders' => [
                    //         'borderStyle' => Border::BORDER_THIN,
                    //     ],
                    // ],
                ]);
                // Set row heights
                $sheet->getRowDimension(1)->setRowHeight(20);
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getRowDimension(3)->setRowHeight(20);
                $sheet->getRowDimension(4)->setRowHeight(20);
                $sheet->getRowDimension(5)->setRowHeight(50);
                // Freeze header rows
                $sheet->freezePane('A6');
                // Auto-size columns and wrap text
                for ($col = 1; $col <= Coordinate::columnIndexFromString($highestColumn); $col++) {
                    $columnLetter = Coordinate::stringFromColumnIndex($col);
                    $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
                    // Wrap text for column headers
                    $cell = $columnLetter . '5';
                    $value = $sheet->getCell($cell)->getValue();
                    if (is_string($value)) {
                        $formattedValue = wordwrap($value, 10, "\n", true);
                        $sheet->setCellValue($cell, $formattedValue);
                        $sheet->getStyle($cell)->getAlignment()->setWrapText(true);
                    }
                }
                // Format monetary columns
                $monetaryColumns = array_merge(
                    $this->employeeSalaryEarningComponents ?? [],
                    $this->employeeSalaryDeductionComponents ?? [],
                    $this->earningsComponents ?? [],
                    $this->deductionsComponents ?? [],
                    ['Monthly Gross', 'Gross Salary', 'Net Pay', 'CTC']
                );
                foreach ($monetaryColumns as $col) {
                    $colIndex = array_search($col, $this->buildColumnHeaders());
                    if ($colIndex !== false) {
                        $colLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
                        $sheet->getStyle($colLetter . '6:' . $colLetter . $highestRow)
                            ->getNumberFormat()
                            ->setFormatCode('#,##0.00;[Red]-#,##0.00');
                    }
                }
            }
        }, 'ESIC_Report_sheet.xlsx');
    }
}
