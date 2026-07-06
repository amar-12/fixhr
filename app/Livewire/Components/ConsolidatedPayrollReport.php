<?php

namespace App\Livewire\Components;

use App\Exports\Combined\CombinedPayrollExport;
use App\Models\PayrollPeriod;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class ConsolidatedPayrollReport extends Component
{
    public $roundOffValues = false;
    public $selectedPayrollPeriodId;
    public $businessId;

    // Headings for Payroll Register
    public $selectedEmployeeContactsHeading = 'Employee Contacts';
    public $selectedEmployeeEarningsHeading = 'Salary Master Earnings';
    public $selectedEmployeeDeductionsHeading = 'Salary Master Deductions';
    public $selectedDaysInfoHeading = 'Attendance Summary';
    public $selectedEarningHeading = 'Earnings';
    public $selectedDeductionHeading = 'Deductions';

    // Filters
    public $filter = [
        'designation' => null,
        'grade' => null,
        'branch' => null,
        'dealership' => null,
        'department' => null,
    ];

    public function mount($payrollId = null)
    {
        $user = Auth::user();
        $this->businessId = $user->emp_b_id;
        $this->selectedPayrollPeriodId = $payrollId;
    }

    public function generateCombinedReport()
    {
        $this->validate([
            'selectedPayrollPeriodId' => 'required|exists:payroll_periods,pp_id',
        ], [
            'selectedPayrollPeriodId.required' => 'Payroll period is required.',
        ]);

        $payroll = PayrollPeriod::where('pp_b_id', $this->businessId)
            ->find($this->selectedPayrollPeriodId);

        if (!$payroll) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'Invalid Payroll Period']);
            return;
        }

        // Check if payroll is processed
        if ($payroll->pp_is_processed == 121) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'Salaries are not processed for the selected Payroll Period.']);
            return;
        }

        // Clean filters - remove empty values
        $filters = array_filter($this->filter, function($value) {
            return !is_null($value) && $value !== '';
        });

        $fileName = 'Consolidated_Payroll_Report_' . $payroll->pp_name . '_' . Carbon::now()->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new CombinedPayrollExport(
                $this->selectedPayrollPeriodId,
                $this->businessId,
                $this->roundOffValues,
                $filters,
                $this->selectedEmployeeContactsHeading,  // Pass as separate parameter
                $this->selectedEmployeeEarningsHeading,  // Pass as separate parameter
                $this->selectedEmployeeDeductionsHeading, // Pass as separate parameter
                $this->selectedEarningHeading,           // Pass as separate parameter
                $this->selectedDeductionHeading,         // Pass as separate parameter
                $this->selectedDaysInfoHeading           // Pass as separate parameter
            ),
            $fileName
        );
    }

    public function render()
    {
        $payrollPeriods = PayrollPeriod::where('pp_b_id', $this->businessId)
            ->where('pp_is_processed', 120) // Only processed payrolls
            ->orderBy('pp_end_date', 'desc')
            ->get();

        return view('livewire.components.consolidated-payroll-report', [
            'payrollPeriods' => $payrollPeriods,
        ]);
    }
}