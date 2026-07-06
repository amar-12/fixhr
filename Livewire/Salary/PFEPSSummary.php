<?php
namespace App\Livewire\Salary;
use App\Exports\PfEpsSheetExport;
use App\Models\Business;
use App\Models\FinancialYear;
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
    public function mount()
    {
        $user = Auth::user();
        $this->businessId = $user->emp_b_id;
        $currentFinacialYear = FinancialYear::where('fy_b_id', $user->emp_b_id)->where('fy_is_current', 1)->first();
        $this->selectFY($currentFinacialYear->fy_id, $currentFinacialYear->fy_year);
        //  dd($currentFinacialYear);
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
        return view('livewire.salary.p-f-e-p-s-summary', compact('financialYears', 'payrollPeriods'));
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
        $payroll = PayrollPeriod::where('pp_b_id', $this->businessId)->where('pp_is_processed', 120)->find($this->selectedPayrollPeriodId);
        // dd($payroll);
        if (!$payroll) {
            return back()->with('error', 'Invalid Payroll Period');
        }
        $businessName = Business::where('b_id', $this->businessId)->value('b_name') ?? 'Unknown Business';
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
            ->leftJoin('processed_salary_earnings as earnings', function ($join) {
                $join->on('earnings.ps_id', '=', 'processed_salaries.ps_id')
                    ->where('earnings.ps_earning_type_id', '=', 362);
            })
            ->select(
                'employees.emp_code',
                'employees.emp_fname',
                'employees.emp_dob',
                'employees.emp_date_of_joining',
                'employees.emp_pf_no',
                'employees.emp_eps_no',
                'businesses.b_name as company_name',
                'departments.d_name as department',
                'designations.dg_name as designation',
                'gender.m_name as gender',
                'processed_salaries.*',
                'employee_salaries.*',
                'earnings.ps_earning_type_id',
                'earnings.ps_e_amount',
                'earnings.*'
            )
            ->where('payroll_periods.pp_id', $this->selectedPayrollPeriodId)->get();
        return Excel::download(
            new PfEpsSheetExport(
                $businessName,
                $payroll,
                $employeesWithPf,
                $this->filters
            ),
            'PF_EPS_Summary_Report.xlsx'
        );
    }
}
