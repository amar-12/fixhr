<?php
namespace App\Livewire\Salary;
use App\Exports\Salary\PfEpsSheetExport;
use App\Models\Business;
use App\Models\Employee;
use App\Models\FinancialYear;
use App\Models\MasterTable;
use App\Models\PayrollPeriod;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
class PFEPSSummary extends Component
{
    public $payroll_period;
    public $searchFY = '';
    public $businessId = null;
    public $selectedFYId;
    public $searchPayroll = '';
    public $selectedPayrollPeriodId;
    public $employeeNameHeading;
    public $companyNameHeading;
    public $departmentHeading;
    public $designationHeading;
    public $genderHeading;
    public $aadhaarHeading;
    public $fathersNameHeading;
    public $deductionsHeading;
    public $dobHeading;
    public $dojHeading;
    public $dolHeading;
    public $lastWorkingDateHeading;
    public $reasonOfLeavingHeading;
    public $pfHeading;
    public $epsHeading;
    public $uaHeading;
    public $monthPeriodHeading;
    public $daysWorkedHeading;
    public $arrearDaysHeading;
    public $lopHeading;
    public $grossSalaryHeading;
    public $basicDaHeading;
    public $pfContributionHeading;
    public $epsContributionHeading;
    public $edliHeading;
    public $search = '';
    public $selectedEmployeeId;
    public $employeeStatusId = null;
    public function mount()
    {
        $user = Auth::user();
        $this->businessId = $user->emp_b_id;
        $currentFinacialYear = FinancialYear::where('fy_b_id', $user->emp_b_id)->where('fy_is_current', 1)->first();
        $this->selectFY($currentFinacialYear->fy_id, $currentFinacialYear->fy_year);
        //  dd($currentFinacialYear);
    }
    public function selectEmployee($id, $name)
    {
        $this->selectedEmployeeId = $id;
        $this->search = $name;
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
    public $filters = [
        'employeeName' => true,
        'companyName' => true,
        'department' => true,
        'designation' => true,
        'gender' => true,
        'aadhaarNo' => true,
        'fathersName' => true,
        'deductions' => true,
        'dob' => true,
        'doj' => true,
        'dol' => true,
        'lastWorkingDate' => true,
        'reasonOfLeaving' => true,
        'pf' => true,
        'eps' => true,
        'ua' => true,
        'monthPeriod' => true,
        'daysWorked' => true,
        'arrearDays' => true,
        'lop' => true,
        'grossSalary' => true,
        'basicDa' => true,
        'pfContribution' => true,
        'epsContribution' => true,
        'edli' => true,
    ];
    public $preventCollapse = false;
    public function toggleFilter($key, $state)
    {
        if (!array_key_exists($key, $this->filters)) return;
        $this->filters[$key] = filter_var($state, FILTER_VALIDATE_BOOLEAN);
        // If toggling a group, update all related fields
        if ($key === 'pfContribution') {
            $this->filters['pfSalary'] = $this->filters[$key];
            $this->filters['mpf'] = $this->filters[$key];
            $this->filters['totalMpf'] = $this->filters[$key];
            $this->filters['cpf'] = $this->filters[$key];
            $this->filters['totalCpf'] = $this->filters[$key];
            $this->filters['vpf'] = $this->filters[$key];
            $this->filters['pfAdminCharges'] = $this->filters[$key];
        }
        if ($key === 'epsContribution') {
            $this->filters['epsSalary'] = $this->filters[$key];
            $this->filters['eps'] = $this->filters[$key];
            $this->filters['totalEps'] = $this->filters[$key];
        }
        if ($key === 'edli') {
            $this->filters['edliSalary'] = $this->filters[$key];
            $this->filters['totalEdliWages'] = $this->filters[$key];
            $this->filters['edliContribution'] = $this->filters[$key];
            $this->filters['adminCharges'] = $this->filters[$key];
        }
        $this->updatePreventCollapse();
    }
    public function updatePreventCollapse()
    {
        $this->preventCollapse = in_array(true, $this->filters, true);
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
        ];
        if (array_key_exists($property, $mapping)) {
            $this->{$mapping[$property]} = null;
        }
    }
    public function generateReport()
    {
        $this->validate(
            [
                'selectedFYId' => 'required|exists:financial_years,fy_id',
                'selectedPayrollPeriodId' => 'required|exists:payroll_periods,pp_id',
            ],
            [
                'selectedFYId.required' => 'Financial year is required.',
                'selectedPayrollPeriodId.required' => 'Payroll period is required.',
            ]
        );
        $payroll = PayrollPeriod::where('pp_b_id', $this->businessId)
            ->where('pp_is_processed', 120)
            ->find($this->selectedPayrollPeriodId);
        if (!$payroll) {
            return back()->with('error', 'Invalid Payroll Period');
        }
        $businessName = Business::where('b_id', $this->businessId)->value('b_name') ?? 'Unknown Business';
        // Step 1: Fetch all data into a variable
        $employeesWithPf = PayrollPeriod::join('processed_salaries', 'processed_salaries.ps_payroll_id', '=', 'payroll_periods.pp_id')
            ->join('employee_salaries', 'employee_salaries.es_emp_id', '=', 'processed_salaries.ps_emp_id')
            ->join('employees', 'employees.emp_id', '=', 'processed_salaries.ps_emp_id')
            ->leftJoin('departments', 'departments.d_id', '=', 'employees.emp_d_id')
            ->leftJoin('businesses', 'businesses.b_id', '=', 'employees.emp_b_id')
            ->leftJoin('designations', 'designations.dg_id', '=', 'employees.emp_dg_id')
            ->leftJoin('master_table AS gender', function ($join) {
                $join->on('gender.m_id', '=', 'employees.emp_gender_id')
                    ->where('gender.m_group', '=', 'GENDER');
            })
            // DA (362)
            ->leftJoin('processed_salary_earnings as da_earnings', function ($join) {
                $join->on('da_earnings.ps_id', '=', 'processed_salaries.ps_id')
                    ->where('da_earnings.ps_earning_type_id', '=', 362);
            })
            // BASIC (360)
            ->leftJoin('processed_salary_earnings as basic_earnings', function ($join) {
                $join->on('basic_earnings.ps_id', '=', 'processed_salaries.ps_id')
                    ->where('basic_earnings.ps_earning_type_id', '=', 360);
            })
            // Deductions: EPF (351)
            ->leftJoin('processed_salary_deductions as epf_deductions', function ($join) {
                $join->on('epf_deductions.ps_id', '=', 'processed_salaries.ps_id')
                    ->where('epf_deductions.ps_deduction_type_id', '=', 351)
                    ->where('epf_deductions.ps_d_category', '=', 'employee');
            })
            ->select(
                'employees.emp_id', // Include emp_id for deduplication
                'employees.emp_status',
                'employees.emp_code',
                'employees.emp_fname',
                'employees.emp_dob',
                'employees.emp_date_of_joining',
                'employees.emp_pf_no',
                'employees.emp_eps_no',
                'employees.emp_is_pf_enabled',
                'businesses.b_name as company_name',
                'departments.d_name as department',
                'designations.dg_name as designation',
                'gender.m_name as gender',
                'processed_salaries.*',
                'employee_salaries.*',
                'da_earnings.ps_e_amount as da_amount',
                'basic_earnings.ps_e_amount as basic_amount',
                'epf_deductions.ps_d_amount as epf_amount'
            )
            ->where('payroll_periods.pp_id', $this->selectedPayrollPeriodId)
            ->where('employees.emp_is_pf_enabled', 120)
            ->when($this->selectedEmployeeId, function ($q) {
                $q->where('employees.emp_id', $this->selectedEmployeeId);
            })
            ->when($this->employeeStatusId, function ($q) {
                $q->where('employees.emp_status', $this->employeeStatusId);
            })
            ->get();
        $deduplicatedEmployees = $employeesWithPf->unique('emp_id')->values();
        if ($deduplicatedEmployees->isEmpty()) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'No records found for the selected criteria.']);
            return;
        }
        // Step 3: Pass deduplicated data to Excel export
        return Excel::download(
            new PfEpsSheetExport(
                $businessName,
                $payroll,
                $deduplicatedEmployees,
                $this->filters
            ),
            'PF_EPS_Summary_Report.xlsx'
        );
    }
    public function render()
    {
        $financialYears = FinancialYear::when($this->searchFY, function ($q) {
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
        return view('livewire.salary.p-f-e-p-s-summary', compact('financialYears', 'payrollPeriods', 'employees', 'employeeStatus'));
    }
}
