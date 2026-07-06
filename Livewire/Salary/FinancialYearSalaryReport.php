<?php

namespace App\Livewire\Salary;

use Livewire\Component;
use App\Models\Business;
use App\Models\FinancialYear;
use App\Models\PayrollPeriod;
use Illuminate\Support\Carbon;
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
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FinancialYearSalaryReport extends Component
{
    public $selectedPayrollPeriodId;
    public $searchPayroll = '';
    public $businessId;
    public $selectedFYId;
    public $searchFY = '';
    public $paymentMode = '';
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

        $this->selectedFYId=FinancialYear::where('fy_b_id',$user->emp_b_id)->where('fy_is_current',1)->first();

        $this->selectFY($this->selectedFYId->fy_id,$this->selectedFYId->fy_year);
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
                'employeeEarnings' => $this->selectedEmployeeEarningsHeading = 'Salary Master',
                'employeeDeductions' => $this->selectedEmployeeDeductionsHeading = '',
                'daysInfo' => $this->selectedDaysInfoHeading = 'Attendance Summary',
                'earningComponents' => $this->selectedEarningHeading = 'Earning Components',
                'deductionComponents' => $this->selectedDeductionHeading = 'Deduction Components',
                default => null,
            };
        } else {
            match ($key) {
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
        return view('livewire.salary.financial-year-salary-report', compact('financialYears', 'payrollPeriods'));
    }
    public function generateReport()
    {
        $this->validate([
            'selectedFYId' => 'required|exists:financial_years,fy_id',
            'paymentMode' => 'nullable|in:bank,cash,cheque',
        ],[
            'selectedFYId.required' => 'Financial year is required.',
        ]);

        $payrollPeriods = PayrollPeriod::where('pp_b_id', $this->businessId)
            ->where('pp_fy_id', $this->selectedFYId)
            ->where('pp_is_processed', 120)
            ->get();

        if ($payrollPeriods->isEmpty()) {
            session()->flash('error', 'No processed payroll periods found for the selected financial year.');
            return;
        }

        $user = Auth::user();
        $businessName = $user->fh_business->b_name ?? 'Business';
        $mainHeading = 'Salary Register';

        return Excel::download(new class(
            $payrollPeriods,
            $this->businessId,
            $businessName,
            $mainHeading,
            $this->paymentMode,
            $this->selectedEmployeeEarningsHeading,
            $this->selectedEmployeeDeductionsHeading,
            $this->selectedDaysInfoHeading,
            $this->selectedEarningHeading,
            $this->selectedDeductionHeading,
            $this->selectedPreEarningHeading,
            $this->selectedPostEarningHeading,
            $this->selectedNetPayHeading
        ) implements WithMultipleSheets {
            protected $periods;
            protected $businessId;
            protected $businessName;
            protected $mainHeading;
            protected $paymentMode;
            protected $employeeEarningsHeading;
            protected $employeeDeductionsHeading;
            protected $daysInfoHeading;
            protected $earningHeading;
            protected $deductionHeading;
            protected $preEarningHeading;
            protected $postEarningHeading;
            protected $netPayHeading;

            public function __construct(
                $periods,
                $businessId,
                $businessName,
                $mainHeading,
                $paymentMode,
                $employeeEarningsHeading,
                $employeeDeductionsHeading,
                $daysInfoHeading,
                $earningHeading,
                $deductionHeading,
                $preEarningHeading,
                $postEarningHeading,
                $netPayHeading
            ) {
                $this->periods = $periods;
                $this->businessId = $businessId;
                $this->businessName = $businessName;
                $this->mainHeading = $mainHeading;
                $this->paymentMode = $paymentMode;
                $this->employeeEarningsHeading = $employeeEarningsHeading;
                $this->employeeDeductionsHeading = $employeeDeductionsHeading;
                $this->daysInfoHeading = $daysInfoHeading;
                $this->earningHeading = $earningHeading;
                $this->deductionHeading = $deductionHeading;
                $this->preEarningHeading = $preEarningHeading;
                $this->postEarningHeading = $postEarningHeading;
                $this->netPayHeading = $netPayHeading;
            }

            public function sheets(): array
            {
                $sheets = [];
                foreach ($this->periods as $period) {
                    $sheets[] = new class(
                        $period,
                        $this->businessId,
                        $this->businessName,
                        $this->mainHeading,
                        $this->paymentMode,
                        $this->employeeEarningsHeading,
                        $this->employeeDeductionsHeading,
                        $this->daysInfoHeading,
                        $this->earningHeading,
                        $this->deductionHeading,
                        $this->preEarningHeading,
                        $this->postEarningHeading,
                        $this->netPayHeading
                    ) implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents, WithTitle {
                        use Exportable;

                        protected $period;
                        protected $businessId;
                        protected $businessName;
                        protected $mainHeading;
                        protected $paymentMode;
                        protected $employeeSalaryEarningsHeading;
                        protected $employeeSalaryDeductionHeading;
                        protected $daysInfoHeading;
                        protected $earningsHeading;
                        protected $deductionsHeading;
                        protected $preEarningHeading;
                        protected $postEarningHeading;
                        protected $netPayHeading;
                        protected $data;
                        protected $earningsComponents;
                        protected $deductionsComponents;
                        protected $employeeSalaryEarningComponents;
                        protected $employeeSalaryDeductionComponents;
                        protected $basicInfoColumns;
                        protected $daysInfoColumns;
                        protected $columnHeaders;

                        public function __construct(
                            $period,
                            $businessId,
                            $businessName,
                            $mainHeading,
                            $paymentMode,
                            $employeeSalaryEarningsHeading,
                            $employeeSalaryDeductionHeading,
                            $daysInfoHeading,
                            $earningsHeading,
                            $deductionsHeading,
                            $preEarningHeading,
                            $postEarningHeading,
                            $netPayHeading
                        ) {
                            $this->period = $period;
                            $this->businessId = $businessId;
                            $this->businessName = $businessName;
                            $this->mainHeading = $mainHeading;
                            $this->paymentMode = $paymentMode;
                            $this->employeeSalaryEarningsHeading = $employeeSalaryEarningsHeading;
                            $this->employeeSalaryDeductionHeading = $employeeSalaryDeductionHeading;
                            $this->daysInfoHeading = $daysInfoHeading;
                            $this->earningsHeading = $earningsHeading;
                            $this->deductionsHeading = $deductionsHeading;
                            $this->preEarningHeading = $preEarningHeading;
                            $this->postEarningHeading = $postEarningHeading;
                            $this->netPayHeading = $netPayHeading;
                            $this->basicInfoColumns = ['S No.', 'Emp Code', 'Title', 'Employee Name','Project','Region','Designation','Grade', 'Personal Email', 'Personal Mobile', 'Official Mobile', 'Work Email', 'Date of Joining', 'Service Length as on Payroll Date', 'Payment Mode', 'Emp Bank Name', 'Bank IFSC', 'Account No.'];
                            $this->daysInfoColumns = ['Days In Month', 'Workable Days', 'Days Worked', 'Present Days', 'Late Count', 'WeekOffs', 'UPL'];
                            $this->prepareData();
                            $this->columnHeaders = $this->buildColumnHeaders();
                        }

                        public function title(): string
                        {
                            return $this->period->pp_name;
                        }

                        protected function prepareData()
                        {
                            $query = ProcessedEmployeeSalary::with('employee.fh_employee_status', 'earnings', 'deductions','employee.employeeProjects')
                                ->where('ps_payroll_id', $this->period->pp_id);
                            if ($this->paymentMode) {
                                $query->whereHas('employee', function ($q) {
                                    $q->where('emp_paymentmode', $this->paymentMode);
                                });
                            }
                            $processedSalary = $query->get();
                            if ($processedSalary->isEmpty()) {
                                $this->data = collect();
                                $this->employeeSalaryEarningComponents = [];
                                $this->employeeSalaryDeductionComponents = [];
                                $this->earningsComponents = [];
                                $this->deductionsComponents = [];
                                return;
                            }
                            $employeeSalary = SalaryEmployeeSalary::with('fh_employee')
                                ->where('es_b_id', $this->businessId)
                                ->whereIn('es_emp_id', $processedSalary->pluck('ps_emp_id'))
                                ->get();
                            $this->employeeSalaryDeductionComponents = SalaryEmployeeDeductions::with('fh_salary_deduction_type')
                                ->whereIn('es_d_emp_id', $employeeSalary->pluck('es_emp_id')->toArray())
                                ->get()
                                ->pluck('fh_salary_deduction_type.m_name')
                                ->unique()
                                ->values()
                                ->toArray();
                            $this->employeeSalaryEarningComponents = SalaryEmployeeEarnings::with('fh_salary_earning_type')
                                ->whereIn('es_e_emp_id', $employeeSalary->pluck('es_emp_id')->toArray())
                                ->get()
                                ->pluck('fh_salary_earning_type.sa_title')
                                ->unique()
                                ->values()
                                ->toArray();
                            $this->earningsComponents = ProcessedSalaryEarning::whereIn('ps_id', $processedSalary->pluck('ps_id'))
                                ->distinct()->pluck('ps_earning_type')->toArray();
                            $this->deductionsComponents = ProcessedSalaryDeduction::whereIn('ps_id', $processedSalary->pluck('ps_id'))
                                ->distinct()->pluck('ps_deduction_type')->toArray();
                            $this->data = $processedSalary->transform(function ($item, $key) use ($employeeSalary) {
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
                                $joiningDate = Carbon::parse($item->employee->emp_date_of_joining);
                                $currentDate = Carbon::now();
                                $diff = $joiningDate->diff($currentDate);
                                $accountNo = $item->employee->emp_bank_account_no;
                                $formattedAccountNo = preg_match('/^\d+$/', $accountNo) ? "'$accountNo" : $accountNo;
                                $rowData = [
                                    'S No.' => $key + 1,
                                    'Title' => $item->employee->fh_employee_title->m_name ?? '',
                                    'Emp Code' => $item->employee->emp_code,
                                    'Employee Name' => $item->employee->emp_full_name,
                                    'Project' => $item->employee->employeeProjects->pluck('ps_name')->implode(', '),
                                    'Region' => $item->employee->emp_region_id ?? '',
                                    'Designation' => $item->employee->fh_designation->dg_name ?? '',
                                    'Grade' => $item->employee->fh_grade->g_name ?? '',
                                    'Personal Email' => $item->employee->emp_email ?? '',
                                    'Personal Mobile' => $item->employee->emp_phone ?? '',
                                    'Official Mobile' => $item->employee->emp_official_contact ?? '',
                                    'Work Email' => $item->employee->emp_company_email ?? '',
                                    'Date of Joining' => $item->employee->emp_date_of_joining ? Carbon::parse($item->employee->emp_date_of_joining)->format('d M, Y') : '',
                                    'Service Length as on Payroll Date' => "{$diff->y} y {$diff->m} m {$diff->d} d",
                                    'Payment Mode' => $item->employee->emp_paymentmode ?? '',
                                    'Emp Bank Name' => $item->employee->emp_bank_name ?? '',
                                    'Bank IFSC' => $item->employee->emp_bank_ifsc_code ?? '',
                                    'Account No.' => $formattedAccountNo ?? ''
                                ];
                                foreach ($this->employeeSalaryEarningComponents as $empSalaryearning) {
                                    $rowData['Salary Master_' . $empSalaryearning] = $empSalaryEarningData[$empSalaryearning] ?? 0;
                                }
                                $rowData['Salary Master_Other Allowance'] = $employeeSalary->where('es_emp_id', $item->ps_emp_id)->first()->es_rem_allowance ?? 0;
                                $rowData['Monthly Gross'] = $employeeSalary->where('es_emp_id', $item->ps_emp_id)->first()->es_monthly_gross ?? 0;
                                $rowData['CTC'] = $employeeSalary->where('es_emp_id', $item->ps_emp_id)->first()->es_monthly_ctc ?? 0;
                                foreach ($this->employeeSalaryDeductionComponents as $empSalarydeduction) {
                                    $rowData['Salary Master_' . $empSalarydeduction] = $empSalaryDeductionData[$empSalarydeduction] ?? 0;
                                }
                                $rowData['Attendance Summary_Days In Month'] = $item->ps_total_days_in_month;
                                $rowData['Attendance Summary_Workable Days'] = $item->ps_total_month_working_days;
                                $rowData['Attendance Summary_Days Worked'] = $item->ps_total_days_worked;
                                $rowData['Attendance Summary_Present Days'] = $item->ps_present_days;
                                $rowData['Attendance Summary_Late Count'] = $item->ps_days_late ?? 0;
                                $rowData['Attendance Summary_WeekOffs'] = $item->ps_week_off_count;
                                $rowData['Attendance Summary_UPL'] = $item->ps_upl_count ?? 0;
                                foreach ($this->earningsComponents as $earning) {
                                    $rowData['Earnings_' . $earning] = $earningsData[$earning] ?? 0;
                                }
                                $rowData['Monthly CTC'] = $item->ps_monthly_ctc ?? 0;
                                $rowData['Gross Salary'] = $item->ps_monthly_gross ?? 0;
                                foreach ($this->deductionsComponents as $deduction) {
                                    $rowData['Deductions_' . $deduction] = $deductionsData[$deduction] ?? 0;
                                }
                                $rowData['Net Pay'] = $item->ps_monthly_net_salary ?? 0;
                                return $rowData;
                            });
                        }

                        protected function buildColumnHeaders()
                        {
                            $headers = $this->basicInfoColumns;
                            if ($this->employeeSalaryEarningsHeading) {

                                foreach ($this->employeeSalaryEarningComponents as $col) {
                                    $headers[] = 'Salary Master_' . $col;
                                }
                                $headers[] = 'Salary Master_Other Allowance';
                                $headers[] = 'Monthly Gross';
                                $headers[] = 'CTC';
                            }
                            if ($this->employeeSalaryDeductionHeading) {
                                foreach ($this->employeeSalaryDeductionComponents as $col) {
                                    $headers[] = 'Salary Master_' . $col;
                                }
                            }
                            if ($this->daysInfoHeading) {
                                foreach ($this->daysInfoColumns as $col) {
                                    $headers[] = 'Attendance Summary_' . $col;
                                }
                            }
                            if ($this->earningsHeading) {
                                foreach ($this->earningsComponents as $col) {
                                    $headers[] = 'Earnings_' . $col;
                                }
                                $headers[] = 'Monthly CTC';
                                $headers[] = 'Gross Salary';

                            }
                            if ($this->deductionsHeading) {
                                foreach ($this->deductionsComponents as $col) {
                                    $headers[] = 'Deductions_' . $col;
                                }
                            }

                            $headers[] = 'Net Pay';
                            return $headers;
                        }

                        public function collection()
                        {
                            $data = collect($this->data);
                            $grandTotals = [
                                'S No.' => 'Grand Totals',
                                'Emp Code' => '',
                                'Title' => '',
                                'Employee Name' => '',
                                'Project' => '',
                                'Region' => '',
                                'Designation' => '',
                                'Grade' => '',
                                'Personal Email' => '',
                                'Personal Mobile' => '',
                                'Official Mobile' => '',
                                'Work Email' => '',
                                'Date of Joining' => '',
                                'Service Length as on Payroll Date' => '',
                                'Payment Mode' => '',
                                'Emp Bank Name' => '',
                                'Bank IFSC' => '',
                                'Account No.' => '',
                            ];
                            $columnHeaders = $this->buildColumnHeaders();
                            $filteredData = $data->map(function ($item) use ($columnHeaders) {
                                $filteredItem = [];
                                foreach ($columnHeaders as $col) {
                                    $filteredItem[$col] = $item[$col] ?? '';
                                }
                                return $filteredItem;
                            });
                            if ($filteredData->count() > 0) {
                                foreach ($filteredData->first() as $col => $value) {
                                    if (!in_array($col, ['S No.', 'Emp Code', 'Title','Employee Name','Project','Region','Designation','Grade', 'Personal Email', 'Personal Mobile', 'Official Mobile', 'Work Email', 'Date of Joining', 'Service Length as on Payroll Date', 'Payment Mode', 'Emp Bank Name', 'Bank IFSC', 'Account No.'])) {
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
                                ["SALARY DETAIL FOR THE MONTH OF " . strtoupper($this->period->pp_name)],
                                [$this->mainHeading],
                            ];
                            $componentHeaders = [];
                            foreach ($this->columnHeaders as $col) {
                                if (in_array($col, $this->basicInfoColumns)) {
                                    $componentHeaders[] = '';
                                    continue;
                                }
                                if (str_starts_with($col, 'Salary Master_')) {
                                    $componentHeaders[] = $this->employeeSalaryEarningsHeading;
                                } elseif ($col === 'Monthly Gross') {
                                    $componentHeaders[] = '';
                                } elseif ($col === 'Salary Master_Other Allowance') {
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
                            }, $this->columnHeaders);
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
                                    $safeMerge = function ($start, $end) use ($sheet) {
                                        if ($start !== $end) {
                                            $sheet->mergeCells("$start:$end");
                                        }
                                    };
                                    $sheet->mergeCells('A1:' . $highestColumn . '1');
                                    $sheet->mergeCells('A2:' . $highestColumn . '2');
                                    $sheet->mergeCells('A3:' . $highestColumn . '3');
                                    $columnHeaders = $this->columnHeaders;
                                    $currentCol = 1;
                                    $sections = [];
                                    $currentSection = null;
                                    $sectionStart = 1;
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
                                            $sectionStart = $currentCol;
                                        }
                                        $currentCol++;
                                    }
                                    if ($currentSection !== null) {
                                        $sections[] = [
                                            'name' => $currentSection,
                                            'start' => $sectionStart,
                                            'end' => $currentCol - 1
                                        ];
                                    }
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
                                ['Monthly Gross', 'Gross Salary', 'Net Pay' ,'CTC']
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
                    };
                }
                return $sheets;
            }
        }, 'payroll_report_sheet.xlsx');
    }
}