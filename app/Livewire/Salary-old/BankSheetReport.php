<?php
namespace App\Livewire\Salary;

use App\Exports\Salary\BankSheetExport;
use App\Models\Employee;
use App\Models\FinancialYear;
use App\Models\MasterTable;
use App\Models\PayrollPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
class BankSheetReport extends Component
{
    public $search = '';
    public $selectedEmployeeId;
    public $employeeStatusId = null;
    public $selectedPayrollPeriodId;
    public $searchPayroll = '';
    public $businessId;
    public $selectedFYId;
    public $searchFY = '';
    public function mount()
    {
        $user = Auth::user();
        $this->businessId = $user->emp_b_id;
        $this->selectedFYId = FinancialYear::where('fy_b_id', $user->emp_b_id)->where('fy_is_current', 1)->first();
        $this->selectFY($this->selectedFYId->fy_id, $this->selectedFYId->fy_year);
        // dd($this->searchFY);
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
        // Generic mapping of search fields to selected fields
        $mapping = [
            'searchPayroll' => 'selectedPayrollPeriodId',
            'searchFY' => 'selectedFYId',
            'search' => 'selectedEmployeeId',
        ];
        // Apply the generic reset logic
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
        $user = Auth::user();
        // $selectedFields = $this->selectedFields;
        // $fields = $this->filters;
        $payrollPeriodId = $this->selectedPayrollPeriodId;
        $amountCheck = 0;
        // Validate payroll period
        if (!$payrollPeriodId) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'Please select a Payroll Period.']);
            return;
        }
        $payroll = PayrollPeriod::find($payrollPeriodId);
        if (!$payroll) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'Invalid Payroll Period selected.']);
            return;
        }
        if ($payroll->pp_is_processed == 121) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'Salaries are not processed for the selected Payroll Period.']);
            return;
        }
       $data = Employee::with([
        'processedSalaries' => function ($q) use ($payrollPeriodId) {
            $q->where('ps_payroll_id', $payrollPeriodId);
        }
    ])
    ->whereHas('processedSalaries', function ($q) use ($payrollPeriodId) {
        $q->where('ps_payroll_id', $payrollPeriodId);
    })
    ->where('emp_b_id', $this->businessId)
    ->when($this->selectedEmployeeId, function ($q) {
        // If employee is selected, check for both emp_id and emp_status (if provided)
        $q->where('emp_id', $this->selectedEmployeeId)
          ->when($this->employeeStatusId, function ($query) {
              $query->where('emp_status', $this->employeeStatusId);
          });
    }, function ($q) {
        // If employee is not selected, check only for status (if provided)
        $q->when($this->employeeStatusId, function ($query) {
            $query->where('emp_status', $this->employeeStatusId);
        });
    })
    ->get();


         
        if ($data->isEmpty()) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'No records found for the selected criteria.']);
            return;
        }
        $business = DB::table('businesses')->where('b_id', $this->businessId)->first();
        $fileName = 'BankSheetReport_' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(
            new BankSheetExport($data, $user, $amountCheck, $payroll->pp_b_id, $business, $payroll->pp_name),
            $fileName
        );
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
        return view('livewire.salary.bank-sheet-report', compact('financialYears', 'payrollPeriods', 'employees', 'employeeStatus'));
    }
}
