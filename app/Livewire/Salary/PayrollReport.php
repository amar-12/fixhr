<?php
namespace App\Livewire\Salary;
use Livewire\Component;
use App\Models\Business;
use App\Models\Dealership;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Branch;
use App\Models\Grade;
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
// Excel Concerns
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
class PayrollReport extends Component
{
    public $search = '',
        $searchDepartment = '',
        $searchDealer = '',
        $searchDesignation = '',
        $searchBranch = '',
        $searchGrade = '';
    public $selectedEmployeeId,
        $selectedDepartmentId,
        $selectedDealerId,
        $selectedDesignationId,
        $selectedBranchId,
        $selectedGradeId;
    public $employeeStatusId = null;
    public $selectedPayrollPeriodId;
    public $searchPayroll = '';
    public $businessId;
    public $selectedFYId;
    public $searchFY = '';
    public $paymentMode = '';
    public $selectedPreEarningHeading;
    public $selectedPostEarningHeading;
    public $selectedNetPayHeading;
    public $selectedEmployeeContactsHeading;
    public $selectedEmployeeEarningsHeading = 'Salary Master';
    public $selectedEmployeeDeductionsHeading = '';
    public $selectedDaysInfoHeading = 'Attendance Summary';
    public $selectedEarningHeading = 'Earning Components';
    public $selectedDeductionHeading = 'Deduction Components';
    public $sortBy = 'emp_code';
    // Add round off property
    public $roundOffValues = false;
      public $showFilterPanel = false;
    public $filters = [
        'employeeContacts' => false,
        'employeeEarnings' => true,
        'employeeDeductions' => true,
        'daysInfo' => true,
        'earningComponents' => true,
        'deductionComponents' => true,
    ];
    public $preventCollapse = false;
    public function mount()
    {
        $user = Auth::user();
        $this->businessId = $user->emp_b_id;
        $currentFY = FinancialYear::where('fy_b_id', $user->emp_b_id)
            ->where('fy_is_current', 1)
            ->first();
        if ($currentFY) {
            $this->selectFY($currentFY->fy_id, $currentFY->fy_year);
        }
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
    public $filter = [
        'department' => false,
        'shift' => false,
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
        $this->selectedEmployeeContactsHeading = null;
        $this->selectedEmployeeEarningsHeading = null;
        $this->selectedEmployeeDeductionsHeading = null;
        $this->selectedDaysInfoHeading = null;
        $this->selectedEarningHeading = null;
        $this->selectedDeductionHeading = null;
        $this->search                = '';
        $this->searchDepartment      = '';
        $this->searchDesignation     = '';
        $this->searchDealer          = '';
        $this->searchBranch          = '';
        $this->searchGrade           = '';
        // Reset filters to defaults — adjust as needed
        $this->filter = [
        'department' => false,
        'shift' => false,
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
        $financialYears = FinancialYear::where('fy_b_id', $this->businessId)
            ->when($this->searchFY, fn($q) => $q->where('fy_year', 'like', "%{$this->searchFY}%"))
            ->get();
        $payrollPeriods = PayrollPeriod::where('pp_b_id', $this->businessId)
            ->when($this->selectedFYId, fn($q) => $q->where('pp_fy_id', $this->selectedFYId))
            ->when(
                strlen($this->searchPayroll) >= 1 && !$this->selectedPayrollPeriodId,
                fn($q) => $q->where('pp_name', 'like', "%{$this->searchPayroll}%")
            )->orderBy('pp_start_date', 'desc')
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
        $grades = Grade::where('g_b_id', $this->businessId)
            ->when(
                strlen($this->searchGrade) >= 1 && !$this->selectedGradeId,
                fn($q) => $q->where('g_name', 'like', "%{$this->searchGrade}%")
            )
            ->limit(100)
            ->get();
        $branches = Branch::where('br_b_id', $this->businessId)
            ->when(
                strlen($this->searchBranch) >= 1 && !$this->selectedBranchId,
                fn($q) => $q->where('br_name', 'like', "%{$this->searchBranch}%")
            )
            ->limit(100)
            ->get();
        $dealers = Dealership::where('dlr_b_id', $this->businessId)
            ->when(
                strlen($this->searchDealer) >= 1 && !$this->selectedDealerId,
                fn($q) => $q->where('dlr_name', 'like', "%{$this->searchDealer}%")
            )
            ->limit(100)
            ->get();
        $designations = Designation::where('dg_b_id', $this->businessId)
            ->when(
                strlen($this->searchDesignation) >= 1 && !$this->selectedDesignationId,
                fn($q) => $q->where('dg_name', 'like', "%{$this->searchDesignation}%")
            )
            ->limit(100)
            ->get();
        $departments = Department::where('d_b_id', $this->businessId)
            ->when(
                strlen($this->searchDepartment) >= 1 && !$this->selectedDepartmentId,
                fn($q) => $q->where('d_name', 'like', "%{$this->searchDepartment}%")
            )
            ->limit(100)
            ->get();
        return view('livewire.salary.payroll-report', compact(
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
            'selectedPayrollPeriodId' => 'required|exists:payroll_periods,pp_id',
            'paymentMode' => 'nullable|in:bank,cash,cheque',
        ], [
            'selectedFYId.required' => 'Financial year is required.',
            'selectedPayrollPeriodId.required' => 'Payroll period is required.',
        ]);
        $payroll = PayrollPeriod::where('pp_b_id', $this->businessId)
            ->where('pp_is_processed', 120)
            ->find($this->selectedPayrollPeriodId);
        if (!$payroll) {
            return back()->with('error', 'Invalid Payroll Period');
        }
        $query = ProcessedEmployeeSalary::with(
            'employee.fh_employee_status',
            'employee.fh_department',
            'employee.fh_designation',
            'employee.fh_grade',
            'employee.fh_branch',
            'earnings',
            'deductions',
            'employee.employeeProjects'
        )
            ->where('ps_payroll_id', $this->selectedPayrollPeriodId)
            ->when($this->employeeStatusId, function ($q) {
                $q->whereHas('employee', function ($subQuery) {
                    if ($this->employeeStatusId === 'resigned') {
                        $subQuery->whereNotNull('emp_last_working_date');
                    } else {
                        $subQuery->where('emp_status', $this->employeeStatusId);
                    }
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
        // 🔥 SORT RESULTS BY emp_code NUMERICALLY
        // $processedSalary = $processedSalary->sortBy(function ($item) {
        //     return (int) $item->employee->emp_code;
        // })->values();
        $sortBy = $this->sortBy;
        $processedSalary = $processedSalary->sortBy(function ($item) use ($sortBy) {
            if ($sortBy === 'emp_name') {
                return strtolower($item->employee->emp_full_name);
            }
            // default: emp_code
            return (int) $item->employee->emp_code;
        })->values();
        // dd($processedSalary->toArray());
        if ($processedSalary->isEmpty()) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'No records found for the selected criteria.']);
            return;
        }
        $employeeSalary = SalaryEmployeeSalary::with('fh_employee')
            ->where('es_b_id', $this->businessId)
            ->whereIn('es_emp_id', $processedSalary->pluck('ps_emp_id'))
            ->get();
        $employeeSalaryDeductionComponents = SalaryEmployeeDeductions::with('fh_salary_deduction_type')
            ->whereIn('es_d_emp_id', $employeeSalary->pluck('es_emp_id')->toArray())
            ->get()
            ->pluck('fh_salary_deduction_type.m_name')
            ->unique()
            ->values()
            ->toArray();
        $employeeSalaryEarningComponents = SalaryEmployeeEarnings::with('fh_salary_earning_type')
            ->whereIn('es_e_emp_id', $employeeSalary->pluck('es_emp_id')->toArray())
            ->get()
            ->pluck('fh_salary_earning_type.sa_title')
            ->unique()
            ->values()
            ->toArray();
        $earningsComponents = ProcessedSalaryEarning::whereIn('ps_id', $processedSalary->pluck('ps_id'))
            ->distinct()->pluck('ps_earning_type')->toArray();
        $deductionsComponents = ProcessedSalaryDeduction::whereIn('ps_id', $processedSalary->pluck('ps_id'))
            ->distinct()->pluck('ps_deduction_type')->toArray();
        // Step 1: Build dynamic columns properly
        $basicInfoColumns = [
            'S#',
            'Emp Code',
            'Title',
            'Employee Name',
        ];
        // Conditionally add optional columns based on filters
        if (!empty($this->filter['designation'])) {
            $basicInfoColumns[] = 'Designation';
        }
        if (!empty($this->filter['grade'])) {
            $basicInfoColumns[] = 'Grade';
        }
        if (!empty($this->filter['branch'])) {
            $basicInfoColumns[] = 'Branch';
        }
        if (!empty($this->filter['dealership'])) {
            $basicInfoColumns[] = 'Dealership';
        }
        if (!empty($this->filter['department'])) {
            $basicInfoColumns[] = 'Department';
        }
        // Add personal banking columns to Employee Details
        $basicInfoColumns = array_merge($basicInfoColumns, [
            'Date of Joining',
            'Service Length as on Payroll Date',
            'Payment Mode',
            'Emp Bank Name',
            'Bank IFSC',
            'Account No.'
        ]);
        // Employee Contacts Group - INCLUDES Project and Region
        $employeeContactsColumns = [
            'Project',
            'Region',
            'Personal Mobile',
            'Official Mobile',
            'Personal Email',
            'Work Email',
        ];
        $daysInfoColumns = [
            'Days In Month',
            'Workable Days',
            'Days Worked',
            'Present Days',
            'Late Count',
            'WeekOffs',
            'UPL'
        ];
        $projectNames = cache()->remember('project_names_map_' . auth()->user()->business_id ?? 'global', now()->addHours(6), function () {
            return \App\Models\Project::pluck('ps_name', 'ps_id')->toArray();
        });
        $processedSalary = $processedSalary->transform(function ($item, $key) use (
            $earningsComponents,
            $deductionsComponents,
            $employeeSalaryEarningComponents,
            $employeeSalaryDeductionComponents,
            $employeeSalary,
            $projectNames
        ) {
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
                'S#'                        => $key + 1,
                'Emp Code'                  => $item->employee->emp_code ?? '',
                'Title'                     => $item->employee->fh_employee_title?->m_name ?? '',
                'Employee Name'             => $item->employee->emp_full_name ?? '',
            ];
            // Conditional columns
            if (!empty($this->filter['designation'])) {
                $rowData['Designation'] = $item->employee->fh_designation?->dg_name ?? '';
            }
            if (!empty($this->filter['grade'])) {
                $rowData['Grade'] = $item->employee->fh_grade?->g_name ?? '';
            }
            if (!empty($this->filter['branch'])) {
                $rowData['Branch'] = $item->employee->fh_branch?->br_name ?? $item->employee->emp_branch_id ?? '';
            }
            if (!empty($this->filter['dealership'])) {
                $rowData['Dealership'] = $item->employee->fh_dealership?->dlr_name ?? '';
            }
            if (!empty($this->filter['department'])) {
                $rowData['Department'] = $item->employee->fh_department?->d_name ?? '';
            }
            // Personal banking columns (now part of Employee Details)
            $rowData['Date of Joining'] = $item->employee->emp_date_of_joining
                ? Carbon::parse($item->employee->emp_date_of_joining)->format('d M, Y')
                : '';
            $rowData['Service Length as on Payroll Date'] = "{$diff->y} y {$diff->m} m {$diff->d} d";
            $rowData['Payment Mode'] = $item->employee->emp_paymentmode ?? '';
            $rowData['Emp Bank Name'] = $item->employee->emp_bank_name ?? '';
            $rowData['Bank IFSC'] = $item->employee->emp_bank_ifsc_code ?? '';
            $rowData['Account No.'] = $formattedAccountNo ?? '';
            // Employee Contacts (conditional) - INCLUDES Project and Region
            if ($this->selectedEmployeeContactsHeading) {
                $rowData['Project'] = (function () use ($item, $projectNames) {
                    $ids = $item->employee->emp_project_id;
                    if (empty($ids)) return '';
                    if (is_string($ids)) {
                        $ids = json_decode($ids, true);
                    }
                    if (!is_array($ids)) return '';
                    $names = array_filter(array_map(fn($id) => $projectNames[$id] ?? 'Deleted Project', $ids));
                    return implode(', ', $names);
                })();
                $rowData['Region'] = $item->employee->fh_emp_region?->m_name ?? $item->employee->emp_region_id ?? '';
                $rowData['Personal Mobile'] = $item->employee->emp_phone ?? '';
                $rowData['Official Mobile'] = $item->employee->emp_official_contact ?? '';
                $rowData['Personal Email'] = $item->employee->emp_email ?? '';
                $rowData['Work Email'] = $item->employee->emp_company_email ?? '';
            }
            // Salary Master Earnings (conditional)
            if ($this->selectedEmployeeEarningsHeading) {
                foreach ($employeeSalaryEarningComponents as $comp) {
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
            if ($this->selectedEmployeeDeductionsHeading) {
                foreach ($employeeSalaryDeductionComponents as $comp) {
                    $value = $empSalaryDeductionData[$comp] ?? 0;
                    $rowData['Salary Master_' . $comp] = $this->roundOffValues ? round($value) : $value;
                }
            }
            // Days Info (conditional)
            if ($this->selectedDaysInfoHeading) {
                $rowData['Attendance Summary_Days In Month'] = $item->ps_total_days_in_month;
                $rowData['Attendance Summary_Workable Days'] = $item->ps_total_month_working_days;
                $rowData['Attendance Summary_Days Worked'] = $item->ps_total_days_worked;
                $rowData['Attendance Summary_Present Days'] = $item->ps_present_days;
                $rowData['Attendance Summary_Late Count'] = $item->ps_days_late ?? 0;
                $rowData['Attendance Summary_WeekOffs'] = $item->ps_week_off_count;
                $rowData['Attendance Summary_UPL'] = $item->ps_upl_count ?? 0;
            }
            // Earnings (conditional)
            if ($this->selectedEarningHeading) {
                foreach ($earningsComponents as $comp) {
                    $value = $earningsData[$comp] ?? 0;
                    $rowData['Earnings_' . $comp] = $this->roundOffValues ? round($value) : $value;
                }
                $monthlyCtc = $item->ps_monthly_ctc ?? 0;
                $grossSalary = $item->ps_monthly_gross ?? 0;
                $rowData['Monthly CTC'] = $this->roundOffValues ? round($monthlyCtc) : $monthlyCtc;
                $rowData['Gross Salary'] = $this->roundOffValues ? round($grossSalary) : $grossSalary;
            }
            // Deductions (conditional)
            if ($this->selectedDeductionHeading) {
                foreach ($deductionsComponents as $comp) {
                    $value = $deductionsData[$comp] ?? 0;
                    $rowData['Deductions_' . $comp] = $this->roundOffValues ? round($value) : $value;
                }
            }
            $netPay = $item->ps_monthly_net_salary ?? 0;
            $rowData['Net Pay'] = $this->roundOffValues ? round($netPay) : $netPay;
            return $rowData;
        });
        $user = Auth::user();
        $businessName = $user->fh_business->b_name ?? 'Business';
        $monthName = optional(PayrollPeriod::find($this->selectedPayrollPeriodId))->pp_name ?? 'Month';
        $employeeContactsHeading = $this->selectedEmployeeContactsHeading;
        $employeeSalaryEarningsHeading = $this->selectedEmployeeEarningsHeading;
        $employeeSalaryDeductionHeading = $this->selectedEmployeeDeductionsHeading;
        $earningsHeading = $this->selectedEarningHeading;
        $deductionsHeading = $this->selectedDeductionHeading;
        $daysInfoHeading = $this->selectedDaysInfoHeading;
        return Excel::download(new class(
            $processedSalary,
            $businessName,
            $monthName,
            $employeeContactsHeading,
            $employeeSalaryEarningsHeading,
            $employeeSalaryDeductionHeading,
            $earningsHeading,
            $deductionsHeading,
            $employeeSalaryEarningComponents,
            $employeeSalaryDeductionComponents,
            $earningsComponents,
            $deductionsComponents,
            $basicInfoColumns,
            $employeeContactsColumns,
            $daysInfoColumns,
            $daysInfoHeading,
            $this->roundOffValues // Pass roundOffValues to export class
        ) implements FromCollection, WithHeadings, WithEvents, WithCustomStartCell, WithColumnWidths {
            use Exportable;
            protected $data;
            protected $businessName;
            protected $monthName;
            protected $employeeContactsHeading;
            protected $employeeSalaryEarningsHeading;
            protected $employeeSalaryDeductionHeading;
            protected $earningsHeading;
            protected $deductionsHeading;
            protected $employeeSalaryEarningComponents;
            protected $employeeSalaryDeductionComponents;
            protected $earningsComponents;
            protected $deductionsComponents;
            protected $basicInfoColumns;
            protected $employeeContactsColumns;
            protected $daysInfoColumns;
            protected $daysInfoHeading;
            protected $roundOffValues;
            protected $columnHeaders;
            public function __construct(
                $data,
                $businessName,
                $monthName,
                $employeeContactsHeading,
                $employeeSalaryEarningsHeading,
                $employeeSalaryDeductionHeading,
                $earningsHeading,
                $deductionsHeading,
                $employeeSalaryEarningComponents,
                $employeeSalaryDeductionComponents,
                $earningsComponents,
                $deductionsComponents,
                $basicInfoColumns,
                $employeeContactsColumns,
                $daysInfoColumns,
                $daysInfoHeading,
                $roundOffValues
            ) {
                $this->data = $data;
                $this->businessName = $businessName;
                $this->monthName = $monthName;
                $this->employeeContactsHeading = $employeeContactsHeading;
                $this->employeeSalaryEarningsHeading = $employeeSalaryEarningsHeading;
                $this->employeeSalaryDeductionHeading = $employeeSalaryDeductionHeading;
                $this->earningsHeading = $earningsHeading;
                $this->deductionsHeading = $deductionsHeading;
                $this->employeeSalaryEarningComponents = $employeeSalaryEarningComponents;
                $this->employeeSalaryDeductionComponents = $employeeSalaryDeductionComponents;
                $this->earningsComponents = $earningsComponents;
                $this->deductionsComponents = $deductionsComponents;
                $this->basicInfoColumns = $basicInfoColumns;
                $this->employeeContactsColumns = $employeeContactsColumns;
                $this->daysInfoColumns = $daysInfoColumns;
                $this->daysInfoHeading = $daysInfoHeading;
                $this->roundOffValues = $roundOffValues;
                $this->columnHeaders = $this->buildColumnHeaders();
            }
            protected function buildColumnHeaders()
            {
                $headers = $this->basicInfoColumns;
                // Only add employee contacts if heading is enabled - INCLUDES Project and Region
                if ($this->employeeContactsHeading) {
                    $headers = array_merge($headers, $this->employeeContactsColumns);
                }
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
                $headers = $this->columnHeaders;
                $rows = collect();
                foreach ($this->data as $item) {
                    $row = [];
                    foreach ($headers as $header) {
                        $row[] = $item[$header] ?? 0;
                    }
                    $rows->push($row);
                }
                $grand = [];
                foreach ($headers as $header) {
                    // Updated to handle conditional employee contacts
                    $nonSumColumns = array_merge(
                        $this->basicInfoColumns,
                        $this->employeeContactsHeading ? $this->employeeContactsColumns : []
                    );
                    if (in_array($header, $nonSumColumns)) {
                        $grand[] = $header === 'S#' ? 'Grand Totals' : '';
                        continue;
                    }
                    $sum = $this->data->sum(fn($i) => is_numeric($i[$header] ?? 0) ? (float)$i[$header] : 0);
                    $grand[] = $this->roundOffValues ? round($sum) : $sum;
                }
                $rows->push($grand);
                return $rows;
            }
            public function headings(): array
            {
                $groupRow = ['Employee Details'];
                for ($i = 1; $i < count($this->basicInfoColumns); $i++) {
                    $groupRow[] = '';
                }
                // Only add employee contacts header if enabled - INCLUDES Project and Region
                if ($this->employeeContactsHeading) {
                    $groupRow[] = $this->employeeContactsHeading;
                    for ($i = 1; $i < count($this->employeeContactsColumns); $i++) {
                        $groupRow[] = '';
                    }
                }
                // Calculate positions dynamically based on what's actually included
                $currentPosition = count($this->basicInfoColumns)
                    + ($this->employeeContactsHeading ? count($this->employeeContactsColumns) : 0);
                if ($this->employeeSalaryEarningsHeading) {
                    $cnt = count($this->employeeSalaryEarningComponents) + 3;
                    $groupRow[$currentPosition] = $this->employeeSalaryEarningsHeading;
                    for ($i = 1; $i < $cnt; $i++) {
                        $groupRow[$currentPosition + $i] = '';
                    }
                    $currentPosition += $cnt;
                }
                if ($this->employeeSalaryDeductionHeading) {
                    $cnt = count($this->employeeSalaryDeductionComponents);
                    $groupRow[$currentPosition] = $this->employeeSalaryDeductionHeading;
                    for ($i = 1; $i < $cnt; $i++) {
                        $groupRow[$currentPosition + $i] = '';
                    }
                    $currentPosition += $cnt;
                }
                if ($this->daysInfoHeading) {
                    $cnt = count($this->daysInfoColumns);
                    $groupRow[$currentPosition] = $this->daysInfoHeading;
                    for ($i = 1; $i < $cnt; $i++) {
                        $groupRow[$currentPosition + $i] = '';
                    }
                    $currentPosition += $cnt;
                }
                if ($this->earningsHeading) {
                    $cnt = count($this->earningsComponents) + 2;
                    $groupRow[$currentPosition] = $this->earningsHeading;
                    for ($i = 1; $i < $cnt; $i++) {
                        $groupRow[$currentPosition + $i] = '';
                    }
                    $currentPosition += $cnt;
                }
                if ($this->deductionsHeading) {
                    $cnt = count($this->deductionsComponents);
                    $groupRow[$currentPosition] = $this->deductionsHeading;
                    for ($i = 1; $i < $cnt; $i++) {
                        $groupRow[$currentPosition + $i] = '';
                    }
                    $currentPosition += $cnt;
                }
                $groupRow[$currentPosition] = 'Net Pay';
                // Ensure the group row has the same length as column headers
                while (count($groupRow) < count($this->columnHeaders)) {
                    $groupRow[] = '';
                }
                $clean = array_map(fn($h) => preg_replace('/^(Salary Master_|Attendance Summary_|Earnings_|Deductions_)/', '', $h), $this->columnHeaders);
                return [$groupRow, $clean];
            }
            public function startCell(): string
            {
                return 'A6';
            }
            public function columnWidths(): array
            {
                $widths = [];
                foreach ($this->columnHeaders as $idx => $header) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 1);
                    if ($header === 'S#' || $header === 'Emp Code') {
                        $widths[$colLetter] = 7;
                    } elseif ($header === 'Project' || $header === 'Employee Name' || $header === 'Emp Bank Name') {
                        $widths[$colLetter] = 20; // Wider for project names, employee names, and bank names
                    } elseif ($header === 'Account No.') {
                        $widths[$colLetter] = 18; // Wider for account numbers
                    } else {
                        $widths[$colLetter] = 13;
                    }
                }
                return $widths;
            }
            public function registerEvents(): array
            {
                return [
                    AfterSheet::class => function (AfterSheet $event) {
                        $sheet = $event->sheet->getDelegate();
                        $highestColumn = $sheet->getHighestColumn();
                        $highestRow    = $sheet->getHighestRow();
                        $printed       = Carbon::now()->format('d-M-Y h:i A T');
                        foreach (range(1, 4) as $r) {
                            $sheet->mergeCells("A{$r}:{$highestColumn}{$r}");
                            $sheet->getStyle("A{$r}")->getFont()->setBold(true)->setSize(11);
                            $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                        }
                        $sheet->setCellValue('A1', $this->businessName);
                        $sheet->setCellValue('A2', 'Salary Register');
                        $sheet->setCellValue('A3', "For the month of {$this->monthName}");
                        $sheet->setCellValue('A4', "Printed on: {$printed}");
                        // Add round-off info to header if enabled
                        if ($this->roundOffValues) {
                            $sheet->setCellValue('A5', "Note: All monetary values are rounded to nearest whole number");
                            $sheet->mergeCells("A5:{$highestColumn}5");
                            $sheet->getStyle("A5")->getFont()->setSize(10)->setBold(true)->setItalic(true);
                            $sheet->getStyle("A5")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                        }
                        // Dynamic section calculation that respects conditional headings
                        $currentCol = 1; // Start from column A
                        $sections = [];
                        // Basic Info Section (now INCLUDES personal banking columns)
                        $basicEnd = count($this->basicInfoColumns);
                        $sections[] = ['start' => $currentCol, 'end' => $basicEnd];
                        $currentCol = $basicEnd + 1;
                        // Employee Contacts Section (conditional) - INCLUDES Project and Region
                        if ($this->employeeContactsHeading) {
                            $contactsEnd = $currentCol + count($this->employeeContactsColumns) - 1;
                            $sections[] = ['start' => $currentCol, 'end' => $contactsEnd];
                            $currentCol = $contactsEnd + 1;
                        }
                        // Salary Master Earnings (conditional)
                        if ($this->employeeSalaryEarningsHeading) {
                            $earningsCount = count($this->employeeSalaryEarningComponents) + 3;
                            $earningsEnd = $currentCol + $earningsCount - 1;
                            $sections[] = ['start' => $currentCol, 'end' => $earningsEnd];
                            $currentCol = $earningsEnd + 1;
                        }
                        // Salary Master Deductions (conditional)
                        if ($this->employeeSalaryDeductionHeading) {
                            $deductionsCount = count($this->employeeSalaryDeductionComponents);
                            $deductionsEnd = $currentCol + $deductionsCount - 1;
                            $sections[] = ['start' => $currentCol, 'end' => $deductionsEnd];
                            $currentCol = $deductionsEnd + 1;
                        }
                        // Days Info (conditional)
                        if ($this->daysInfoHeading) {
                            $daysCount = count($this->daysInfoColumns);
                            $daysEnd = $currentCol + $daysCount - 1;
                            $sections[] = ['start' => $currentCol, 'end' => $daysEnd];
                            $currentCol = $daysEnd + 1;
                        }
                        // Earnings (conditional)
                        if ($this->earningsHeading) {
                            $processedEarningsCount = count($this->earningsComponents) + 2;
                            $processedEarningsEnd = $currentCol + $processedEarningsCount - 1;
                            $sections[] = ['start' => $currentCol, 'end' => $processedEarningsEnd];
                            $currentCol = $processedEarningsEnd + 1;
                        }
                        // Deductions (conditional)
                        if ($this->deductionsHeading) {
                            $processedDeductionsCount = count($this->deductionsComponents);
                            $processedDeductionsEnd = $currentCol + $processedDeductionsCount - 1;
                            $sections[] = ['start' => $currentCol, 'end' => $processedDeductionsEnd];
                            $currentCol = $processedDeductionsEnd + 1;
                        }
                        // Net Pay Section
                        $sections[] = ['start' => $currentCol, 'end' => $currentCol];
                        // Apply section merging
                        foreach ($sections as $s) {
                            if ($s['start'] !== $s['end']) {
                                $start = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($s['start']);
                                $end   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($s['end']);
                                $sheet->mergeCells("{$start}6:{$end}6");
                            }
                        }
                        $sheet->getStyle("A6:{$highestColumn}7")
                            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('263871');
                        $sheet->getStyle("A6:{$highestColumn}7")
                            ->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('FFFFFF');
                        $sheet->getStyle("A6:{$highestColumn}7")
                            ->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                            ->setVertical(Alignment::VERTICAL_CENTER)
                            ->setWrapText(true);
                        $sheet->freezePane('E8');
                        $sheet->setShowGridlines(false);
                        $dataRange = "A8:{$highestColumn}{$highestRow}";
                        $sheet->getStyle($dataRange)
                            ->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                            ->setWrapText(true);
                        $sheet->getStyle($dataRange)->getFont()->setSize(8);
                        for ($r = 8; $r <= $highestRow; $r++) {
                            $sheet->getRowDimension($r)->setRowHeight(25);
                        }
                        $sheet->getStyle("A6:{$highestColumn}{$highestRow}")
                            ->getBorders()
                            ->getAllBorders()
                            ->setBorderStyle(Border::BORDER_THIN)
                            ->getColor()->setRGB('D3D3D3');
                        $sheet->getStyle("T8:{$highestColumn}{$highestRow}")
                            ->getNumberFormat()
                            ->setFormatCode('#,##0.00;[Red]-#,##0.00');
                        $numEmp = $this->data->count();
                        for ($i = 0; $i < $numEmp; $i++) {
                            $row = 8 + $i;
                            $color = $i % 2 === 0 ? 'F5F5F5' : 'FFFFFF';
                            $sheet->getStyle("A{$row}:{$highestColumn}{$row}")
                                ->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()
                                ->setRGB($color);
                        }
                        $grandRow = 8 + $numEmp;
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
                        foreach (range(1, 4) as $r) {
                            $sheet->getRowDimension($r)->setRowHeight(20);
                        }
                        $sheet->getRowDimension(6)->setRowHeight(15);
                        $sheet->getRowDimension(7)->setRowHeight(30);
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
                            $sheet->getStyle("A{$row}")->getAlignment()
                                ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                                ->setWrapText(true);
                            $sheet->getRowDimension($row)->setRowHeight(18);
                            $row++;
                        }
                    }
                ];
            }
        }, 'payroll_report_sheet.xlsx');
    }
}
