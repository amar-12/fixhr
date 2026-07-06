<?php

namespace App\Livewire\Components;

use App\Exports\Salary\BankSheetExport;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class BankSheetReport extends Component
{
 public $selectedPayrollPeriodId;
  public $roundOffValues = false;
  public $businessId;

public function mount($payrollId = null)
    {
        $user = Auth::user();
        $this->businessId = $user->emp_b_id;
        $this->selectedPayrollPeriodId = $payrollId;
        // dd($this->selectedPayrollPeriodId);
    }

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
        
        $user = Auth::user();
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
        ->get();

        if ($data->isEmpty()) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'No records found for the selected criteria.']);
            return;
        }

        $business = DB::table('businesses')->where('b_id', $this->businessId)->first();
        $fileName = 'BankSheetReport_' . now()->format('Y-m-d') . '.xlsx';
        
        return Excel::download(
            new BankSheetExport(
                $data, 
                $user, 
                $amountCheck, 
                $payroll->pp_b_id, 
                $business, 
                $payroll->pp_name,
                $this->roundOffValues // Pass roundOffValues to export class
            ),
            $fileName
        );
    }
    public function render()
    {
        return view('livewire.components.bank-sheet-report');
    }
}
