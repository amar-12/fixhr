<?php
namespace App\Livewire\Salary;
use App\Exports\Salary\AdhocPaymentsDeductions;
use App\Models\AdhocTransaction;
use Livewire\Component;
use App\Models\Business;
use App\Models\Employee;
use App\Models\FinancialYear;
use App\Models\PayrollPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Department;
use App\Models\MasterTable;
use Maatwebsite\Excel\Facades\Excel;
class AdhocReport extends Component
{
  
    public $selectedEmployeeId;
    public $employeeStatusId = null;
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
    public $searchEmployee = '';

    public $selectedDepartmentId;
    public $searchDepartment = '';
    public function mount()
    {
        $user = Auth::user();
        $this->businessId = $user->emp_b_id;
        $fy = FinancialYear::where('fy_b_id', $user->emp_b_id)->where('fy_is_current', 1)->first();
        if ($fy) {
            $this->selectedFYId = $fy->fy_id;
            $this->searchFY = $fy->fy_year;
        }
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
    public function selectEmployee($id, $name)
    {
        // dd($id, $name);
        $this->selectedEmployeeId = $id;
        $this->searchEmployee = $name;
    }
    public function selectDepartment($id, $name)
    {
        $this->selectedDepartmentId = $id;
        $this->searchDepartment = $name;
    }
    public function updated($property, $value)
    {
        if ($property === 'employeeStatusId') {
            $this->selectedEmployeeId = null;
            $this->searchEmployee = '';
            return;
        }
        if ($property === 'searchFY') {
            $this->selectedPayrollPeriodId = null;
            $this->selectedFYId = null;
            $this->searchPayroll = '';
        //    dd($this->selectedFYId,'dfdfds');
            return;
        }
        // Generic mapping of search fields to selected fields
        $mapping = [
            'searchPayroll' => 'selectedPayrollPeriodId',
            'searchFY' => 'selectedFYId',
            'searchEmployee' => 'selectedEmployeeId',
            'searchDepartment' => 'selectedDepartmentId',
        ];
        // Apply the generic reset logic
        if (array_key_exists($property, $mapping)) {
            $this->{$mapping[$property]} = null;
        }
    }
    public $filters = [
        'department' => false,
        'financial_year' => true,
        'employee' => true,
    ];

     public $filterFields = [
        'department' => 'Department',
        'financial_year' => 'Financial Year',
        'employee' => 'Employee',
    ];
    public function toggleFilter($key, $state)
    {
        if (!array_key_exists($key, $this->filters)) return;
        $this->filters[$key] = filter_var($state, FILTER_VALIDATE_BOOLEAN);
        if (!$this->filters[$key]) {
            match ($key) {
                'department' => [
                    $this->selectedDepartmentId = null,
                    $this->searchDepartment = '',
                ],
                'financial_year' => [
                    $this->selectedFYId = null,
                    $this->searchFY = '',
                ],
                'employee' => [
                    $this->selectedEmployeeId = null,
                    $this->searchEmployee = '',
                ],
                default => null,
            };
        }
    }
   
   
    public function generateReport()
    {
        $this->validate([
            'selectedEmployeeId' => 'nullable|exists:employees,emp_id',
            'selectedDepartmentId' => 'nullable|exists:departments,d_id',
            'selectedPayrollPeriodId' => 'required|exists:payroll_periods,pp_id',
        ], [
            'selectedEmployeeId.exists' => 'The selected employee does not exist.',
            'selectedDepartmentId.exists' => 'The selected department does not exist.',
            'selectedPayrollPeriodId.exists' => 'The selected payroll period does not exist.',
            'selectedPayrollPeriodId.required' => 'Please select a payroll period.',
        ]);
        $query = AdhocTransaction::with([
            'employee' => fn($q) => $q->select('emp_id', 'emp_code', 'emp_full_name', 'emp_status'),
            'department' => fn($q) => $q->select('d_id', 'd_name'),
            'transaction_details.component' => fn($q) => $q->select('ac_id', 'ac_adhoc_component_name')
        ])
            ->where('at_b_id', $this->businessId)
            ->where('at_pp_id', $this->selectedPayrollPeriodId)
            ->when($this->selectedFYId, fn($q) => $q->whereHas('payrollPeriod', fn($subQ) => $subQ->where('pp_fy_id', $this->selectedFYId)))
            ->when($this->selectedEmployeeId, fn($q, $v) => $q->where('at_emp_id', $v))
            ->when($this->selectedDepartmentId, fn($q, $v) => $q->where('at_emp_d_id', $v))
            ->when($this->employeeStatusId, fn($q) => $q->whereHas('employee', fn($sq) => $sq->where('emp_status', $this->employeeStatusId)));
        $transactions = $query->get()->toArray();
        // dd($transactions);
        if (empty($transactions)) {
          
              $this->dispatch('show-alert', ['type' => 'error', 'message' => 'No records found for the selected criteria.']);
            return;
        }
        $fileName = 'EmployeeReport_' . now()->format('Y-m-d') . '.xlsx';
        $date = Carbon::now()->format('d-m-Y');
        return Excel::download(new AdhocPaymentsDeductions($transactions, $this->businessId, $date), $fileName);
    }
    public function render()
    {
         $employees = collect();
        if (!empty($this->searchEmployee)) {
            $employees = Employee::where('emp_role_id', '<>', 1)
                ->where('emp_b_id', $this->businessId)
                ->where(fn($q) => $q->where('emp_full_name', 'like', "%{$this->searchEmployee}%")
                    ->orWhere('emp_code', 'like', "%{$this->searchEmployee}%"))
                ->when($this->employeeStatusId, fn($q) => $q->where('emp_status', $this->employeeStatusId))
                ->limit(100)
                ->get();
        }
        $employeeStatus = MasterTable::where('m_group', 'STATUS')
            ->limit(100)
            ->select('m_id', 'm_name')
            ->get();
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
        $departments = Department::where('d_b_id', $this->businessId)
            ->when(
                strlen($this->searchDepartment) >= 1 && !$this->selectedDepartmentId,
                fn($q) =>
                $q->where('d_name', 'like', "%{$this->searchDepartment}%")
            )
            ->limit(100)
            ->get();
        return view('livewire.salary.adhoc-report', compact('financialYears', 'payrollPeriods', 'employees', 'departments', 'employeeStatus'));
    }
}
