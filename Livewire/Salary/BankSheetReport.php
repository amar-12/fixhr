<?php
namespace App\Livewire\Salary;

use App\Exports\BankSheetExport;
use App\Models\Employee;
use App\Models\FinancialYear;
use App\Models\PayrollPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class BankSheetReport extends Component
{
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
//  public $filters = [
//         'department' => true,
//         'financial_year' => true,
//         'employee' => true,
//     ];

    //  public $filterFields = [
    //     'department' => 'Department',
    //     'financial_year' => 'Financial Year',
    //     'employee' => 'Employee',
    // ];
    


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
  

 public function generateReport(){
 $this->validate([
            'selectedFYId' => 'required|exists:financial_years,fy_id',
            'selectedPayrollPeriodId' => 'required|exists:payroll_periods,pp_id',

        ], [
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
            return redirect()->back()->with('error', 'Please select a Payroll Period.');
        }
        $payroll = PayrollPeriod::find($payrollPeriodId);
        if (!$payroll) {
            return redirect()->back()->with('error', 'Invalid Payroll Period selected.');
        }
        if ($payroll->pp_is_processed == 121) {
            return redirect()->back()->with('error', 'Salaries are not processed for the selected Payroll Period.');
        }
        // Validate field selection
        // if (empty($selectedFields) && empty($fields)) {
        //     return redirect()->back()->with('error', 'Please select at least one field to export.');
        // }

        

        
        $data = Employee::with([
            'processedSalaries' => function ($q) use ($payrollPeriodId) {
                $q->where('ps_payroll_id', $payrollPeriodId);
            }
        ])
            ->whereHas('processedSalaries', function ($q) use ($payrollPeriodId) {
                $q->where('ps_payroll_id', $payrollPeriodId);
            })
            ->where('emp_b_id', $this->businessId)
            ->where('emp_status', 71)
            ->get();

        if ($data->isEmpty()) {
            return redirect()->back()->with('error', 'No data found for the selected criteria.');
        }

        $business = DB::table('businesses')->where('b_id', $this->businessId)->first();
        $fileName = 'BankSheetReport_' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(
            new BankSheetExport($data, $user,$amountCheck, $payroll->pp_b_id, $business, $payroll->pp_name),
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
        return view('livewire.salary.bank-sheet-report', compact('financialYears', 'payrollPeriods'));
    }
}
