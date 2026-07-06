<?php
namespace App\Livewire\Salary;

use Livewire\Component;
use App\Models\Business;
use App\Models\Employee;
use App\Models\FinancialYear;
use App\Models\MasterTable;
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
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class FinancialYearSalaryReport extends Component
{
    public $search = '';
    public $selectedEmployeeId;
    public $employeeStatusId = null;
    public $selectedPayrollPeriodId;
    public $searchPayroll = '';
    public $businessId;
    public $selectedFYId;
    public $searchFY = '';
    public $paymentMode = '';
    // Add round off property
    public $roundOffValues = false;
    // Add filter properties
    public $searchDepartment = '';
    public $searchDealer = '';
    public $searchDesignation = '';
    public $searchBranch = '';
    public $searchGrade = '';
    public $selectedDepartmentId;
    public $selectedDealerId;
    public $selectedDesignationId;
    public $selectedBranchId;
    public $selectedGradeId;
    public $selectedEarningHeading;
    public $selectedDeductionHeading;
    public $selectedDaysInfoHeading;
    public $selectedEmployeeEarningsHeading;
    public $selectedEmployeeDeductionsHeading;
    public $selectedEmployeeContactsHeading;
    public $selectedPreEarningHeading;
    public $selectedPostEarningHeading;
    public $selectedNetPayHeading;
     public $showFilterPanel = false;

    public function mount()
    {
        $user = Auth::user();
        $this->businessId = $user->emp_b_id;
        $this->selectedFYId = FinancialYear::where('fy_b_id', $user->emp_b_id)->where('fy_is_current', 1)->first();
        $this->selectFY($this->selectedFYId->fy_id, $this->selectedFYId->fy_year);
    }

    public function selectEmployee($id, $name)
    {
        $this->selectedEmployeeId = $id;
        $this->search = $name;
    }

    public function selectGrade($id, $name)
    {
        $this->selectedGradeId = $id;
        $this->searchGrade = $name;
    }

    public function selectDesignation($id, $name)
    {
        $this->selectedDesignationId = $id;
        $this->searchDesignation = $name;
    }

    public function selectBranch($id, $name)
    {
        $this->selectedBranchId = $id;
        $this->searchBranch = $name;
    }

    public function selectDealer($id, $name)
    {
        $this->selectedDealerId = $id;
        $this->searchDealer = $name;
    }

    public function selectDepartment($id, $name)
    {
        $this->selectedDepartmentId = $id;
        $this->searchDepartment = $name;
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

        if ($property === 'employeeStatusId') {
            $this->selectedEmployeeId = null;
            $this->search = '';
            return;
        }

        $mapping = [
            'search' => 'selectedEmployeeId',
            'searchPayroll' => 'selectedPayrollPeriodId',
            'searchFY' => 'selectedFYId',
            'searchDepartment' => 'selectedDepartmentId',
            'searchDesignation' => 'selectedDesignationId',
            'searchDealer' => 'selectedDealerId',
            'searchBranch' => 'selectedBranchId',
            'searchGrade' => 'selectedGradeId',
        ];

        if (array_key_exists($property, $mapping)) {
            $this->{$mapping[$property]} = null;
        }
    }

    // Add filter array for conditional columns
    public $filter = [
        'department' => false,
        'designation' => false,
        'dealership' => false,
        'branch' => false,
        'grade' => false,
    ];
     public function toggleFilterPanel()
    {
        $this->showFilterPanel = !$this->showFilterPanel;
    }
    public function resetFilters()
    {
        $this->selectedEmployeeId        = null;
        $this->selectedDepartmentId      = null;
        $this->selectedDesignationId     = null;
        $this->selectedDealerId          = null;
        $this->selectedBranchId          = null;
        $this->selectedGradeId           = null;
        $this->selectedPayrollPeriodId = null;
        $this->search                = '';
        $this->searchDepartment      = '';
        $this->searchDesignation     = '';
        $this->searchDealer          = '';
        $this->searchBranch          = '';
        $this->searchGrade           = '';
        // Reset filters to defaults — adjust as needed
        $this->filter = [
        'department' => false,
        'designation' => false,
        'dealership' => false,
        'branch' => false,
        'grade' => false,
    ];
    }

    public function toggleFilters($key, $state)
    {
        if (!array_key_exists($key, $this->filter)) return;
        $this->filter[$key] = filter_var($state, FILTER_VALIDATE_BOOLEAN);
        if (!$this->filter[$key]) {
            match ($key) {
                'department' => [
                    $this->selectedDepartmentId = null,
                    $this->searchDepartment = '',
                ],
                'designation' => [
                    $this->selectedDesignationId = null,
                    $this->searchDesignation = '',
                ],
                'dealership' => [
                    $this->selectedDealerId = null,
                    $this->searchDealer = '',
                ],
                'branch' => [
                    $this->selectedBranchId = null,
                    $this->searchBranch = '',
                ],
                'grade' => [
                    $this->selectedGradeId = null,
                    $this->searchGrade = '',
                ],
                default => null,
            };
        }
    }

    public $filters = [
        'employeeContacts' => false,
        'employeeEarnings' => false,
        'employeeDeductions' => false,
        'daysInfo' => false,
        'earningComponents' => false,
        'deductionComponents' => false,
    ];

    public $preventCollapse = false;

    public function toggleFilter($key, $state)
    {
        if (!array_key_exists($key, $this->filters)) return;
        $this->filters[$key] = filter_var($state, FILTER_VALIDATE_BOOLEAN);
        if ($this->filters[$key]) {
            match ($key) {
                'employeeContacts' => $this->selectedEmployeeContactsHeading = 'Employee Contacts',
                'employeeEarnings' => $this->selectedEmployeeEarningsHeading = 'Salary Master',
                'employeeDeductions' => $this->selectedEmployeeDeductionsHeading = '',
                'daysInfo' => $this->selectedDaysInfoHeading = 'Attendance Summary',
                'earningComponents' => $this->selectedEarningHeading = 'Earning Components',
                'deductionComponents' => $this->selectedDeductionHeading = 'Deduction Components',
                default => null,
            };
        } else {
            match ($key) {
                'employeeContacts' => $this->selectedEmployeeContactsHeading = null,
                'employeeEarnings' => $this->selectedEmployeeEarningsHeading = null,
                'employeeDeductions' => $this->selectedEmployeeDeductionsHeading = null,
                'daysInfo' => $this->selectedDaysInfoHeading = null,
                'earningComponents' => $this->selectedEarningHeading = null,
                'deductionComponents' => $this->selectedDeductionHeading = null,
                'preEarning' => $this->selectedPreEarningHeading = null,
                'postEarning' => $this->selectedPostEarningHeading = null,
                'netPay' => $this->selectedNetPayHeading = null,
                default => null,
            };
        }
        $this->updatePreventCollapse();
    }

    public function updatePreventCollapse()
    {
        $this->preventCollapse = in_array(true, $this->filters, true);
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

        $employees = collect();
        if (!empty($this->search)) {
            $employees = Employee::where('emp_role_id', '<>', 1)
                ->where('emp_b_id', $this->businessId)
                ->where(fn($q) => $q->where('emp_full_name', 'like', "%{$this->search}%")
                    ->orWhere('emp_code', 'like', "%{$this->search}%"))
                ->when($this->employeeStatusId, fn($q) => $q->where('emp_status', $this->employeeStatusId))
                ->limit(100)
                ->get();
        }

        $employeeStatus = MasterTable::where('m_group', 'STATUS')
            ->limit(100)
            ->select('m_id', 'm_name')
            ->get();

        // Add other filter options
        $grades = \App\Models\Grade::where('g_b_id', $this->businessId)
            ->when(
                strlen($this->searchGrade) >= 1 && !$this->selectedGradeId,
                fn($q) => $q->where('g_name', 'like', "%{$this->searchGrade}%")
            )
            ->limit(100)
            ->get();

        $branches = \App\Models\Branch::where('br_b_id', $this->businessId)
            ->when(
                strlen($this->searchBranch) >= 1 && !$this->selectedBranchId,
                fn($q) => $q->where('br_name', 'like', "%{$this->searchBranch}%")
            )
            ->limit(100)
            ->get();

        $dealers = \App\Models\Dealership::where('dlr_b_id', $this->businessId)
            ->when(
                strlen($this->searchDealer) >= 1 && !$this->selectedDealerId,
                fn($q) => $q->where('dlr_name', 'like', "%{$this->searchDealer}%")
            )
            ->limit(100)
            ->get();

        $designations = \App\Models\Designation::where('dg_b_id', $this->businessId)
            ->when(
                strlen($this->searchDesignation) >= 1 && !$this->selectedDesignationId,
                fn($q) => $q->where('dg_name', 'like', "%{$this->searchDesignation}%")
            )
            ->limit(100)
            ->get();

        $departments = \App\Models\Department::where('d_b_id', $this->businessId)
            ->when(
                strlen($this->searchDepartment) >= 1 && !$this->selectedDepartmentId,
                fn($q) => $q->where('d_name', 'like', "%{$this->searchDepartment}%")
            )
            ->limit(100)
            ->get();

        return view('livewire.salary.financial-year-salary-report', compact(
            'financialYears',
            'payrollPeriods',
            'employees',
            'employeeStatus',
            'grades',
            'departments',
            'dealers',
            'designations',
            'branches'
        ));
    }

    public function generateReport()
    {
        $this->validate([
            'selectedFYId' => 'required|exists:financial_years,fy_id',
            'paymentMode' => 'nullable|in:bank,cash,cheque',
        ], [
            'selectedFYId.required' => 'Financial year is required.',
        ]);

        $payrollPeriods = PayrollPeriod::where('pp_b_id', $this->businessId)
            ->where('pp_fy_id', $this->selectedFYId)
            ->where('pp_is_processed', 120)
            ->get();

           
        if ($payrollPeriods->isEmpty()) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'No processed payroll periods found for the selected financial year.']);
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
            $this->selectedEmployeeContactsHeading,
            $this->selectedDaysInfoHeading,
            $this->selectedEarningHeading,
            $this->selectedDeductionHeading,
            $this->selectedPreEarningHeading,
            $this->selectedPostEarningHeading,
            $this->selectedNetPayHeading,
            $this->employeeStatusId ?? null,
            $this->selectedEmployeeId ?? null,
            $this->selectedGradeId ?? null,
            $this->selectedBranchId ?? null,
            $this->selectedDealerId ?? null,
            $this->selectedDesignationId ?? null,
            $this->selectedDepartmentId ?? null,
            $this->filter,
            $this->roundOffValues
        ) implements WithMultipleSheets {
            protected $periods;
            protected $businessId;
            protected $businessName;
            protected $mainHeading;
            protected $paymentMode;
            protected $employeeEarningsHeading;
            protected $employeeDeductionsHeading;
            protected $employeeContactsHeading;
            protected $daysInfoHeading;
            protected $earningsHeading;
            protected $deductionsHeading;
            protected $preEarningHeading;
            protected $postEarningHeading;
            protected $netPayHeading;
            protected $employeeStatusId;
            protected $selectedEmployeeId;
            protected $selectedGradeId;
            protected $selectedBranchId;
            protected $selectedDealerId;
            protected $selectedDesignationId;
            protected $selectedDepartmentId;
            protected $filter;
            protected $roundOffValues;

            public function __construct(
                $periods,
                $businessId,
                $businessName,
                $mainHeading,
                $paymentMode,
                $employeeEarningsHeading,
                $employeeDeductionsHeading,
                $employeeContactsHeading,
                $daysInfoHeading,
                $earningsHeading,
                $deductionsHeading,
                $preEarningHeading,
                $postEarningHeading,
                $netPayHeading,
                $employeeStatusId,
                $selectedEmployeeId,
                $selectedGradeId,
                $selectedBranchId,
                $selectedDealerId,
                $selectedDesignationId,
                $selectedDepartmentId,
                $filter,
                $roundOffValues
            ) {
                $this->periods = $periods;
                $this->businessId = $businessId;
                $this->businessName = $businessName;
                $this->mainHeading = $mainHeading;
                $this->paymentMode = $paymentMode;
                $this->employeeEarningsHeading = $employeeEarningsHeading;
                $this->employeeDeductionsHeading = $employeeDeductionsHeading;
                $this->employeeContactsHeading = $employeeContactsHeading;
                $this->daysInfoHeading = $daysInfoHeading;
                $this->earningsHeading = $earningsHeading;
                $this->deductionsHeading = $deductionsHeading;
                $this->preEarningHeading = $preEarningHeading;
                $this->postEarningHeading = $postEarningHeading;
                $this->netPayHeading = $netPayHeading;
                $this->employeeStatusId = $employeeStatusId;
                $this->selectedEmployeeId = $selectedEmployeeId;
                $this->selectedGradeId = $selectedGradeId;
                $this->selectedBranchId = $selectedBranchId;
                $this->selectedDealerId = $selectedDealerId;
                $this->selectedDesignationId = $selectedDesignationId;
                $this->selectedDepartmentId = $selectedDepartmentId;
                $this->filter = $filter;
                $this->roundOffValues = $roundOffValues;
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
                        $this->employeeContactsHeading,
                        $this->daysInfoHeading,
                        $this->earningsHeading,
                        $this->deductionsHeading,
                        $this->preEarningHeading,
                        $this->postEarningHeading,
                        $this->netPayHeading,
                        $this->employeeStatusId,
                        $this->selectedEmployeeId,
                        $this->selectedGradeId,
                        $this->selectedBranchId,
                        $this->selectedDealerId,
                        $this->selectedDesignationId,
                        $this->selectedDepartmentId,
                        $this->filter,
                        $this->roundOffValues
                    ) implements FromCollection, WithHeadings, WithEvents, WithTitle {
                        use Exportable;
                        protected $period;
                        protected $businessId;
                        protected $businessName;
                        protected $mainHeading;
                        protected $paymentMode;
                        protected $employeeEarningsHeading;
                        protected $employeeDeductionsHeading;
                        protected $employeeContactsHeading;
                        protected $daysInfoHeading;
                        protected $earningsHeading;
                        protected $deductionsHeading;
                        protected $preEarningHeading;
                        protected $postEarningHeading;
                        protected $netPayHeading;
                        protected $data;
                        protected $earningsComponents;
                        protected $deductionsComponents;
                        protected $employeeEarningsComponents;
                        protected $employeeDeductionsComponents;
                        protected $basicInfoColumns;
                        protected $employeeContactsColumns;
                        protected $personalBankingColumns;
                        protected $daysInfoColumns;
                        protected $columnHeaders;
                        protected $employeeStatusId;
                        protected $selectedEmployeeId;
                        protected $selectedGradeId;
                        protected $selectedBranchId;
                        protected $selectedDealerId;
                        protected $selectedDesignationId;
                        protected $selectedDepartmentId;
                        protected $filter;
                        protected $roundOffValues;
                        protected $projectNames;

                        public function __construct(
                            $period,
                            $businessId,
                            $businessName,
                            $mainHeading,
                            $paymentMode,
                            $employeeEarningsHeading,
                            $employeeDeductionsHeading,
                            $employeeContactsHeading,
                            $daysInfoHeading,
                            $earningsHeading,
                            $deductionsHeading,
                            $preEarningHeading,
                            $postEarningHeading,
                            $netPayHeading,
                            $employeeStatusId,
                            $selectedEmployeeId,
                            $selectedGradeId,
                            $selectedBranchId,
                            $selectedDealerId,
                            $selectedDesignationId,
                            $selectedDepartmentId,
                            $filter,
                            $roundOffValues
                        ) {
                            $this->period = $period;
                            $this->businessId = $businessId;
                            $this->businessName = $businessName;
                            $this->mainHeading = $mainHeading;
                            $this->paymentMode = $paymentMode;
                            $this->employeeEarningsHeading = $employeeEarningsHeading;
                            $this->employeeDeductionsHeading = $employeeDeductionsHeading;
                            $this->employeeContactsHeading = $employeeContactsHeading;
                            $this->daysInfoHeading = $daysInfoHeading;
                            $this->earningsHeading = $earningsHeading;
                            $this->deductionsHeading = $deductionsHeading;
                            $this->preEarningHeading = $preEarningHeading;
                            $this->postEarningHeading = $postEarningHeading;
                            $this->netPayHeading = $netPayHeading;
                            $this->employeeStatusId = $employeeStatusId;
                            $this->selectedEmployeeId = $selectedEmployeeId;
                            $this->selectedGradeId = $selectedGradeId;
                            $this->selectedBranchId = $selectedBranchId;
                            $this->selectedDealerId = $selectedDealerId;
                            $this->selectedDesignationId = $selectedDesignationId;
                            $this->selectedDepartmentId = $selectedDepartmentId;
                            $this->filter = $filter;
                            $this->roundOffValues = $roundOffValues;
                            $this->projectNames = cache()->remember('project_names_map_' . ($this->businessId ?? 'global'), now()->addHours(6), function () {
                                return \App\Models\Project::pluck('ps_name', 'ps_id')->toArray();
                            });
                            $this->basicInfoColumns = $this->buildBasicInfoColumns();
                            $this->employeeContactsColumns = ['Project', 'Region', 'Personal Mobile', 'Official Mobile', 'Personal Email', 'Work Email'];
                            $this->personalBankingColumns = ['Date of Joining', 'Service Length as on Payroll Date', 'Payment Mode', 'Emp Bank Name', 'Bank IFSC', 'Account No.'];
                            $this->daysInfoColumns = ['Days In Month', 'Workable Days', 'Days Worked', 'Present Days', 'Late Count', 'WeekOffs', 'UPL'];
                            $this->prepareData();
                            $this->columnHeaders = $this->buildColumnHeaders();
                        }

                        protected function buildBasicInfoColumns()
                        {
                            $columns = ['S No.', 'Emp Code', 'Title', 'Employee Name'];
                            // Conditionally add optional columns based on filters
                            if (!empty($this->filter['designation'])) {
                                $columns[] = 'Designation';
                            }
                            if (!empty($this->filter['grade'])) {
                                $columns[] = 'Grade';
                            }
                            if (!empty($this->filter['branch'])) {
                                $columns[] = 'Branch';
                            }
                            if (!empty($this->filter['dealership'])) {
                                $columns[] = 'Dealership';
                            }
                            if (!empty($this->filter['department'])) {
                                $columns[] = 'Department';
                            }
                            return $columns;
                        }

                        public function title(): string
                        {
                            return $this->period->pp_name;
                        }

                        protected function prepareData()
                        {
                            $query = ProcessedEmployeeSalary::with(
                                'employee.fh_employee_status',
                                'employee.fh_department',
                                'employee.fh_designation',
                                'employee.fh_grade',
                                'employee.fh_branch',
                                'employee.fh_dealership',
                                'employee.fh_employee_title',
                                'earnings',
                                'deductions',
                                'employee.employeeProjects'
                            )
                                ->where('ps_payroll_id', $this->period->pp_id)
                                ->when($this->employeeStatusId, function ($q) {
                                    $q->whereHas('employee', function ($subQuery) {
                                        $subQuery->where('emp_status', $this->employeeStatusId);
                                    });
                                })
                                ->when($this->selectedGradeId, function ($q) {
                                    $q->whereHas('employee', function ($subQuery) {
                                        $subQuery->where('emp_grade_id', $this->selectedGradeId);
                                    });
                                })
                                ->when($this->selectedBranchId, function ($q) {
                                    $q->whereHas('employee', function ($subQuery) {
                                        $subQuery->where('emp_br_id', $this->selectedBranchId);
                                    });
                                })
                                ->when($this->selectedDealerId, function ($q) {
                                    $q->whereHas('employee', function ($subQuery) {
                                        $subQuery->where('emp_dlr_id', $this->selectedDealerId);
                                    });
                                })
                                ->when($this->selectedDesignationId, function ($q) {
                                    $q->whereHas('employee', function ($subQuery) {
                                        $subQuery->where('emp_dg_id', $this->selectedDesignationId);
                                    });
                                })
                                ->when($this->selectedDepartmentId, function ($q) {
                                    $q->whereHas('employee', function ($subQuery) {
                                        $subQuery->where('emp_d_id', $this->selectedDepartmentId);
                                    });
                                });

                            if ($this->paymentMode) {
                                $query->whereHas('employee', fn($q) => $q->where('emp_paymentmode', $this->paymentMode));
                            }

                            if ($this->selectedEmployeeId) {
                                $query->where('ps_emp_id', $this->selectedEmployeeId);
                            }

                            $processedSalary = $query->get();

                            if ($processedSalary->isEmpty()) {
                                $this->data = collect();
                                $this->employeeEarningsComponents = [];
                                $this->employeeDeductionsComponents = [];
                                $this->earningsComponents = [];
                                $this->deductionsComponents = [];
                                return;
                            }

                            $employeeSalary = SalaryEmployeeSalary::with('fh_employee')
                                ->where('es_b_id', $this->businessId)
                                ->whereIn('es_emp_id', $processedSalary->pluck('ps_emp_id'))
                                ->get();

                            $this->employeeDeductionsComponents = SalaryEmployeeDeductions::with('fh_salary_deduction_type')
                                ->whereIn('es_d_emp_id', $employeeSalary->pluck('es_emp_id')->toArray())
                                ->get()
                                ->pluck('fh_salary_deduction_type.m_name')
                                ->unique()
                                ->values()
                                ->toArray();

                            $this->employeeEarningsComponents = SalaryEmployeeEarnings::with('fh_salary_earning_type')
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
                                ];

                                // Conditional columns
                                if (!empty($this->filter['designation'])) {
                                    $rowData['Designation'] = $item->employee->fh_designation->dg_name ?? '';
                                }
                                if (!empty($this->filter['grade'])) {
                                    $rowData['Grade'] = $item->employee->fh_grade->g_name ?? '';
                                }
                                if (!empty($this->filter['branch'])) {
                                    $rowData['Branch'] = $item->employee->fh_branch->br_name ?? $item->employee->emp_branch_id ?? '';
                                }
                                if (!empty($this->filter['dealership'])) {
                                    $rowData['Dealership'] = $item->employee->fh_dealership->dlr_name ?? '';
                                }
                                if (!empty($this->filter['department'])) {
                                    $rowData['Department'] = $item->employee->fh_department->d_name ?? '';
                                }

                                // Personal banking columns (part of Employee Details)
                                $rowData['Date of Joining'] = $item->employee->emp_date_of_joining ? Carbon::parse($item->employee->emp_date_of_joining)->format('d M, Y') : '';
                                $rowData['Service Length as on Payroll Date'] = "{$diff->y} y {$diff->m} m {$diff->d} d";
                                $rowData['Payment Mode'] = $item->employee->emp_paymentmode ?? '';
                                $rowData['Emp Bank Name'] = $item->employee->emp_bank_name ?? '';
                                $rowData['Bank IFSC'] = $item->employee->emp_bank_ifsc_code ?? '';
                                $rowData['Account No.'] = $formattedAccountNo ?? '';

                                // Employee Contacts (conditional)
                                if ($this->employeeContactsHeading) {
                                    $rowData['Project'] = (function () use ($item) {
                                        $ids = $item->employee->emp_project_id;
                                        if (empty($ids)) return '';
                                        if (is_string($ids)) {
                                            $ids = json_decode($ids, true);
                                        }
                                        if (!is_array($ids)) return '';
                                        $names = array_filter(array_map(fn($id) => $this->projectNames[$id] ?? 'Deleted Project', $ids));
                                        return implode(', ', $names);
                                    })();
                                    $rowData['Region'] = $item->employee->emp_region_id ?? '';
                                    $rowData['Personal Mobile'] = $item->employee->emp_phone ?? '';
                                    $rowData['Official Mobile'] = $item->employee->emp_official_contact ?? '';
                                    $rowData['Personal Email'] = $item->employee->emp_email ?? '';
                                    $rowData['Work Email'] = $item->employee->emp_company_email ?? '';
                                }

                                // Salary Master Earnings (conditional)
                                if ($this->employeeEarningsHeading) {
                                    foreach ($this->employeeEarningsComponents as $comp) {
                                        $value = $empSalaryEarningData[$comp] ?? 0;
                                        $rowData['Salary Master_' . $comp] = $this->roundOffValues ? round($value) : $value;
                                    }
                                    $otherAllowance = $employeeSalary->where('es_emp_id', $item->ps_emp_id)->first()->es_rem_allowance ?? 0;
                                    $monthlyGross = $employeeSalary->where('es_emp_id', $item->ps_emp_id)->first()->es_monthly_gross ?? 0;
                                    $ctc = $employeeSalary->where('es_emp_id', $item->ps_emp_id)->first()->es_monthly_ctc ?? 0;
                                    $rowData['Salary Master_Other Allowance'] = $this->roundOffValues ? round($otherAllowance) : $otherAllowance;
                                    $rowData['Monthly Gross'] = $this->roundOffValues ? round($monthlyGross) : $monthlyGross;
                                    $rowData['CTC'] = $this->roundOffValues ? round($ctc) : $ctc;
                                }

                                // Salary Master Deductions (conditional)
                                if ($this->employeeDeductionsHeading) {
                                    foreach ($this->employeeDeductionsComponents as $comp) {
                                        $value = $empSalaryDeductionData[$comp] ?? 0;
                                        $rowData['Salary Master_' . $comp] = $this->roundOffValues ? round($value) : $value;
                                    }
                                }

                                // Days Info (conditional)
                                if ($this->daysInfoHeading) {
                                    $rowData['Attendance Summary_Days In Month'] = $item->ps_total_days_in_month;
                                    $rowData['Attendance Summary_Workable Days'] = $item->ps_total_month_working_days;
                                    $rowData['Attendance Summary_Days Worked'] = $item->ps_total_days_worked;
                                    $rowData['Attendance Summary_Present Days'] = $item->ps_present_days;
                                    $rowData['Attendance Summary_Late Count'] = $item->ps_days_late ?? 0;
                                    $rowData['Attendance Summary_WeekOffs'] = $item->ps_week_off_count;
                                    $rowData['Attendance Summary_UPL'] = $item->ps_upl_count ?? 0;
                                }

                                // Earnings (conditional)
                                if ($this->earningsHeading) {
                                    foreach ($this->earningsComponents as $comp) {
                                        $value = $earningsData[$comp] ?? 0;
                                        $rowData['Earnings_' . $comp] = $this->roundOffValues ? round($value) : $value;
                                    }
                                    $monthlyCtc = $item->ps_monthly_ctc ?? 0;
                                    $grossSalary = $item->ps_monthly_gross ?? 0;
                                    $rowData['Monthly CTC'] = $this->roundOffValues ? round($monthlyCtc) : $monthlyCtc;
                                    $rowData['Gross Salary'] = $this->roundOffValues ? round($grossSalary) : $grossSalary;
                                }

                                // Deductions (conditional)
                                if ($this->deductionsHeading) {
                                    foreach ($this->deductionsComponents as $comp) {
                                        $value = $deductionsData[$comp] ?? 0;
                                        $rowData['Deductions_' . $comp] = $this->roundOffValues ? round($value) : $value;
                                    }
                                }

                                $netPay = $item->ps_monthly_net_salary ?? 0;
                                $rowData['Net Pay'] = $this->roundOffValues ? round($netPay) : $netPay;

                                return $rowData;
                            });
                        }

                        protected function buildColumnHeaders()
                        {
                            $headers = $this->basicInfoColumns;

                            // Add personal banking columns to Employee Details
                            $headers = array_merge($headers, $this->personalBankingColumns);

                            // Only add employee contacts if heading is enabled
                            if ($this->employeeContactsHeading) {
                                $headers = array_merge($headers, $this->employeeContactsColumns);
                            }

                            if ($this->employeeEarningsHeading) {
                                foreach ($this->employeeEarningsComponents as $col) {
                                    $headers[] = 'Salary Master_' . $col;
                                }
                                $headers[] = 'Salary Master_Other Allowance';
                                $headers[] = 'Monthly Gross';
                                $headers[] = 'CTC';
                            }

                            if ($this->employeeDeductionsHeading) {
                                foreach ($this->employeeDeductionsComponents as $col) {
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

                            $grandTotals = array_fill_keys($this->columnHeaders, '');
                            $grandTotals['S No.'] = 'Grand Totals';

                            $filteredData = $data->map(function ($item) {
                                $row = [];
                                foreach ($this->columnHeaders as $col) {
                                    $row[$col] = $item[$col] ?? '';
                                }
                                return $row;
                            });

                            if ($filteredData->isNotEmpty()) {
                                foreach ($this->columnHeaders as $col) {
                                    $nonSumColumns = array_merge(
                                        $this->basicInfoColumns,
                                        $this->personalBankingColumns,
                                        $this->employeeContactsHeading ? $this->employeeContactsColumns : []
                                    );
                                    if (!in_array($col, $nonSumColumns)) {
                                        $sum = $filteredData->sum(fn($row) => is_numeric($row[$col]) ? (float)$row[$col] : 0);
                                        $grandTotals[$col] = $sum > 0 ? ($this->roundOffValues ? round($sum) : $sum) : '';
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
        ['Salary Register'],
        ["For the month of {$this->period->pp_name}"],
        ['Printed on: ' . Carbon::now()->format('d-M-Y h:i A T')],
    ];
    
    if ($this->roundOffValues) {
        $headings[] = []; // Empty row
    }
    
    $headings[] = $this->buildGroupHeaderRow(); // Group header row
    $headings[] = $this->buildCleanColumnHeaders(); // Column header row
    
    return $headings;
}

                        protected function buildGroupHeaderRow()
                        {
                            $row = array_fill(0, count($this->columnHeaders), '');
                            $col = 0;

                            // Employee Details (includes basic info + personal banking)
                            $employeeDetailsCount = count($this->basicInfoColumns) + count($this->personalBankingColumns);
                            for ($i = 0; $i < $employeeDetailsCount; $i++) {
                                $row[$col++] = $i === 0 ? 'Employee Details' : '';
                            }

                            // Employee Contacts (conditional)
                            if ($this->employeeContactsHeading) {
                                $contactsCount = count($this->employeeContactsColumns);
                                $row[$col++] = $this->employeeContactsHeading;
                                for ($i = 1; $i < $contactsCount; $i++) $row[$col++] = '';
                            }

                            if ($this->employeeEarningsHeading) {
                                $cnt = count($this->employeeEarningsComponents) + 3;
                                $row[$col++] = $this->employeeEarningsHeading;
                                for ($i = 1; $i < $cnt; $i++) $row[$col++] = '';
                            }

                            if ($this->employeeDeductionsHeading) {
                                $cnt = count($this->employeeDeductionsComponents);
                                $row[$col++] = $this->employeeDeductionsHeading;
                                for ($i = 1; $i < $cnt; $i++) $row[$col++] = '';
                            }

                            if ($this->daysInfoHeading) {
                                $row[$col++] = $this->daysInfoHeading;
                                for ($i = 1; $i < count($this->daysInfoColumns); $i++) $row[$col++] = '';
                            }

                            if ($this->earningsHeading) {
                                $cnt = count($this->earningsComponents) + 2;
                                $row[$col++] = $this->earningsHeading;
                                for ($i = 1; $i < $cnt; $i++) $row[$col++] = '';
                            }

                            if ($this->deductionsHeading) {
                                $cnt = count($this->deductionsComponents);
                                $row[$col++] = $this->deductionsHeading;
                                for ($i = 1; $i < $cnt; $i++) $row[$col++] = '';
                            }

                            $row[$col] = 'Net Pay';

                            return $row;
                        }

                        protected function buildCleanColumnHeaders()
                        {
                            return array_map(fn($h) => preg_replace('/^(Salary Master_|Attendance Summary_|Earnings_|Deductions_)/', '', $h), $this->columnHeaders);
                        }

                        public function startCell(): string
                        {
                            // Start from the appropriate row based on round-off setting
                            if ($this->roundOffValues) {
                                return 'A6'; // Data starts at row 6 when round-off is enabled
                            } else {
                                return 'A5'; // Data starts at row 5 when round-off is disabled
                            }
                        }

                        public function registerEvents(): array
                        {
                            return [
                                AfterSheet::class => function (AfterSheet $event) {
                                    $sheet = $event->sheet->getDelegate();
                                    $highestColumn = $sheet->getHighestColumn();
                                    $highestRow = $sheet->getHighestRow();
                                    $printed = Carbon::now()->format('d-M-Y h:i A T');

                                    // Calculate starting rows dynamically
                                    $currentRow = 1;
                                    
                                    // Add round-off info to header if enabled
                                    if ($this->roundOffValues) {
                                        $sheet->setCellValue("A{$currentRow}", "Note: All monetary values are rounded to nearest whole number");
                                        $sheet->mergeCells("A{$currentRow}:{$highestColumn}{$currentRow}");
                                        $sheet->getStyle("A{$currentRow}")->getFont()->setSize(10)->setBold(true)->setItalic(true);
                                        $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                                        $currentRow++;
                                    }

                                    // Company info header rows
                                    $companyInfoRows = [
                                        $this->businessName,
                                        'Salary Register',
                                        "For the month of {$this->period->pp_name}",
                                        "Printed on: {$printed}"
                                    ];
                                    
                                    foreach ($companyInfoRows as $value) {
                                        $sheet->setCellValue("A{$currentRow}", $value);
                                        $sheet->mergeCells("A{$currentRow}:{$highestColumn}{$currentRow}");
                                        $sheet->getStyle("A{$currentRow}")->getFont()->setBold(true)->setSize(11);
                                        $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                                        $currentRow++;
                                    }

                                    // Now set the header rows positions
                                    $groupHeaderRow = $currentRow;
                                    $columnHeaderRow = $groupHeaderRow + 1;
                                    $firstDataRow = $columnHeaderRow + 1;

                                    /* ------------------- Group header merges ------------------- */
                                    $sections = [];
                                    $currentCol = 1;
                                    
                                    // Employee Details Section (basic info + personal banking)
                                    $employeeDetailsCount = count($this->basicInfoColumns) + count($this->personalBankingColumns);
                                    $sections[] = ['start' => $currentCol, 'end' => $currentCol + $employeeDetailsCount - 1];
                                    $currentCol += $employeeDetailsCount;
                                    
                                    // Employee Contacts Section (conditional)
                                    if ($this->employeeContactsHeading) {
                                        $contactsCount = count($this->employeeContactsColumns);
                                        $sections[] = ['start' => $currentCol, 'end' => $currentCol + $contactsCount - 1];
                                        $currentCol += $contactsCount;
                                    }
                                    
                                    if ($this->employeeEarningsHeading) {
                                        $c = count($this->employeeEarningsComponents) + 3;
                                        $sections[] = ['start' => $currentCol, 'end' => $currentCol + $c - 1];
                                        $currentCol += $c;
                                    }
                                    
                                    if ($this->employeeDeductionsHeading) {
                                        $c = count($this->employeeDeductionsComponents);
                                        $sections[] = ['start' => $currentCol, 'end' => $currentCol + $c - 1];
                                        $currentCol += $c;
                                    }
                                    
                                    if ($this->daysInfoHeading) {
                                        $sections[] = ['start' => $currentCol, 'end' => $currentCol + 6];
                                        $currentCol += 7;
                                    }
                                    
                                    if ($this->earningsHeading) {
                                        $c = count($this->earningsComponents) + 2;
                                        $sections[] = ['start' => $currentCol, 'end' => $currentCol + $c - 1];
                                        $currentCol += $c;
                                    }
                                    
                                    if ($this->deductionsHeading) {
                                        $c = count($this->deductionsComponents);
                                        $sections[] = ['start' => $currentCol, 'end' => $currentCol + $c - 1];
                                        $currentCol += $c;
                                    }
                                    
                                    $sections[] = ['start' => $currentCol, 'end' => $currentCol];
                                    
                                    // Apply group header merges
                                    foreach ($sections as $s) {
                                        if ($s['start'] !== $s['end']) {
                                            $start = Coordinate::stringFromColumnIndex($s['start']);
                                            $end = Coordinate::stringFromColumnIndex($s['end']);
                                            $sheet->mergeCells("{$start}{$groupHeaderRow}:{$end}{$groupHeaderRow}");
                                        }
                                    }
                                    
                                    // Set group header values
                                    $col = 1;
                                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $groupHeaderRow, 'Employee Details');
                                    for ($i = 1; $i < $employeeDetailsCount; $i++) $col++;
                                    
                                    if ($this->employeeContactsHeading) {
                                        $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $groupHeaderRow, $this->employeeContactsHeading);
                                        for ($i = 1; $i < count($this->employeeContactsColumns); $i++) $col++;
                                    }
                                    
                                    if ($this->employeeEarningsHeading) {
                                        $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $groupHeaderRow, $this->employeeEarningsHeading);
                                        for ($i = 1; $i < count($this->employeeEarningsComponents) + 3; $i++) $col++;
                                    }
                                    
                                    if ($this->employeeDeductionsHeading) {
                                        $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $groupHeaderRow, $this->employeeDeductionsHeading);
                                        for ($i = 1; $i < count($this->employeeDeductionsComponents); $i++) $col++;
                                    }
                                    
                                    if ($this->daysInfoHeading) {
                                        $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $groupHeaderRow, $this->daysInfoHeading);
                                        for ($i = 1; $i < count($this->daysInfoColumns); $i++) $col++;
                                    }
                                    
                                    if ($this->earningsHeading) {
                                        $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $groupHeaderRow, $this->earningsHeading);
                                        for ($i = 1; $i < count($this->earningsComponents) + 2; $i++) $col++;
                                    }
                                    
                                    if ($this->deductionsHeading) {
                                        $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $groupHeaderRow, $this->deductionsHeading);
                                        for ($i = 1; $i < count($this->deductionsComponents); $i++) $col++;
                                    }
                                    
                                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($col) . $groupHeaderRow, 'Net Pay');

                                    // Write the actual column headers
                                    $columnHeaders = $this->buildCleanColumnHeaders();
                                    foreach ($columnHeaders as $index => $header) {
                                        $colLetter = Coordinate::stringFromColumnIndex($index + 1);
                                        $sheet->setCellValue($colLetter . $columnHeaderRow, $header);
                                    }

                                    /* ------------------- Header styling (group and column headers) ------------------- */
                                    $headerRange = "A{$groupHeaderRow}:{$highestColumn}{$columnHeaderRow}";
                                    $sheet->getStyle($headerRange)
                                        ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('263871');
                                    $sheet->getStyle($headerRange)
                                        ->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('FFFFFF');
                                    $sheet->getStyle($headerRange)
                                        ->getAlignment()
                                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                                        ->setVertical(Alignment::VERTICAL_CENTER)
                                        ->setWrapText(true);

                                    // Freeze pane at first data row
                                    $sheet->freezePane("A{$firstDataRow}");

                                    /* ------------------- Data rows (wrap + height 25) ------------------- */
                                    $dataRange = "A{$firstDataRow}:{$highestColumn}{$highestRow}";
                                    $sheet->getStyle($dataRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);
                                    $sheet->getStyle($dataRange)->getFont()->setSize(8);
                                    
                                    for ($r = $firstDataRow; $r <= $highestRow; $r++) {
                                        $sheet->getRowDimension($r)->setRowHeight(25);
                                    }

                                    /* ------------------- Borders – only inside table area ------------------- */
                                    $borderStyle = [
                                        'borders' => [
                                            'allBorders' => [
                                                'borderStyle' => Border::BORDER_THIN,
                                                'color' => ['argb' => 'FFD3D3D3'],
                                            ],
                                        ],
                                    ];
                                    
                                    $tableStartRow = $groupHeaderRow;
                                    $lastDataRow = $firstDataRow + $this->data->count();
                                    $sheet->getStyle("A{$tableStartRow}:{$highestColumn}{$lastDataRow}")
                                        ->applyFromArray($borderStyle);

                                    // Hide default Excel gridlines for clean look
                                    $sheet->setShowGridlines(false);

                                    /* ------------------- Number format for monetary columns ------------------- */
                                    $monetaryStartCol = Coordinate::stringFromColumnIndex(count($this->basicInfoColumns) + count($this->personalBankingColumns) + ($this->employeeContactsHeading ? count($this->employeeContactsColumns) : 0) + 1);
                                    $sheet->getStyle("{$monetaryStartCol}{$firstDataRow}:{$highestColumn}{$highestRow}")
                                        ->getNumberFormat()
                                        ->setFormatCode('#,##0.00;[Red]-#,##0.00');

                                    /* ------------------- Alternating row colours ------------------- */
                                    $numEmp = $this->data->count();
                                    for ($i = 0; $i < $numEmp; $i++) {
                                        $row = $firstDataRow + $i;
                                        $color = $i % 2 === 0 ? 'F5F5F5' : 'FFFFFF';
                                        $sheet->getStyle("A{$row}:{$highestColumn}{$row}")
                                            ->getFill()
                                            ->setFillType(Fill::FILL_SOLID)
                                            ->getStartColor()
                                            ->setRGB($color);
                                    }

                                    /* ------------------- Grand total row ------------------- */
                                    $grandRow = $firstDataRow + $numEmp;
                                    $sheet->getStyle("A{$grandRow}:{$highestColumn}{$grandRow}")
                                        ->getFont()->setBold(true)->setSize(9);
                                    $sheet->getStyle("A{$grandRow}:{$highestColumn}{$grandRow}")
                                        ->getFill()
                                        ->setFillType(Fill::FILL_SOLID)
                                        ->getStartColor()
                                        ->setRGB('FFD9D9D9');
                                    $sheet->getStyle("A{$grandRow}")
                                        ->getAlignment()
                                        ->setHorizontal(Alignment::HORIZONTAL_LEFT);

                                    /* ------------------- Row heights ------------------- */
                                    // Set row heights for company info and round-off note
                                    if ($this->roundOffValues) {
                                        $sheet->getRowDimension(1)->setRowHeight(20); // Round-off note
                                        foreach (range(2, 5) as $r) {
                                            $sheet->getRowDimension($r)->setRowHeight(20); // Company info
                                        }
                                    } else {
                                        foreach (range(1, 4) as $r) {
                                            $sheet->getRowDimension($r)->setRowHeight(20); // Company info
                                        }
                                    }
                                    
                                    $sheet->getRowDimension($groupHeaderRow)->setRowHeight(15);
                                    $sheet->getRowDimension($columnHeaderRow)->setRowHeight(30);

                                    /* ------------------- Legend / Notes ------------------- */
                                    $legendStart = $grandRow + 2;
                                    $sheet->setCellValue("A{$legendStart}", 'Legend / Notes');
                                    $sheet->mergeCells("A{$legendStart}:{$highestColumn}{$legendStart}");
                                    $sheet->getStyle("A{$legendStart}")
                                        ->getFont()->setBold(true)->setSize(10);
                                    $sheet->getStyle("A{$legendStart}")
                                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                                    $notes = [
                                        'Employee Details: Basic information, joining details, and banking information',
                                        'Employee Contacts: Project, Region, Personal & official contact details',
                                        'Salary Master: Fixed components defined in employee salary structure',
                                        'Attendance Summary: Key attendance metrics for the payroll period',
                                        'Earnings: Variable or processed earnings calculated and paid this month',
                                        'Deductions: Variable or processed deductions applied this month',
                                        'Net Pay: Gross Salary minus total Deductions',
                                        'Grand Totals: Sum of all employees for respective numeric columns',
                                        'All monetary values are in INR (Indian Rupees)'
                                    ];
                                    
                                    // Add round-off note if enabled
                                    if ($this->roundOffValues) {
                                        $notes[] = 'Note: All monetary values are rounded to nearest whole number';
                                    }
                                    
                                    $row = $legendStart + 1;
                                    foreach ($notes as $note) {
                                        $sheet->setCellValue("A{$row}", $note);
                                        $sheet->mergeCells("A{$row}:{$highestColumn}{$row}");
                                        $sheet->getStyle("A{$row}")->getFont()->setSize(9);
                                        $sheet->getStyle("A{$row}")
                                            ->getAlignment()
                                            ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                                            ->setWrapText(true);
                                        $sheet->getRowDimension($row)->setRowHeight(18);
                                        $row++;
                                    }
                                }
                            ];
                        }
                    };
                }
                return $sheets;
            }
        }, 'financial_year_salary_report.xlsx');
    }
}