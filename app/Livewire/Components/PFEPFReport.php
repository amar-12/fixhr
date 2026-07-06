<?php
namespace App\Livewire\Components;
use App\Exports\Salary\PfEpsSheetExport;
use App\Models\Business;
use App\Models\PayrollPeriod;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
class PFEPFReport extends Component
{

    public $roundOff = false;
    public $selectedPayrollPeriodId;
    public $businessId;
    public function mount( $payrollId = null)
    {
        $user = Auth::user();
        $this->businessId = $user->emp_b_id;
        $this->selectedPayrollPeriodId = $payrollId;
       
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
    public function generateReport()
    {
        $this->validate(
            [
                'selectedPayrollPeriodId' => 'required|exists:payroll_periods,pp_id',
            ],
            [
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
                $this->filters,
                $this->roundOff,
            ),
            'PF_EPS_Summary_Report.xlsx'
        );
    }
    public function render()
    {
        return view('livewire.components.p-f-e-p-f-report');
    }
}
