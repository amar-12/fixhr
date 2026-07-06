<?php
namespace App\Livewire\WeeklyPayroll;

use App\Livewire\WeeklyPayroll\Concerns\ResolvesWeeklyPayrollReportPeriod;
use App\Exports\Combined\CombinedPayrollExport;
use App\Models\PayrollPeriod;
use App\Models\ProcessedEmployeeSalary;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class ConsolidatedPayrollReport extends Component
{
    use ResolvesWeeklyPayrollReportPeriod;

    public $roundOffValues = false;
    public $selectedPayrollPeriodId;
    public $businessId;

    /** @var int|null Payroll period week (ppw_id) — limits combined sheets to this week */
    public $selectedWeekId;

    public $showButton = true;

    public $buttonStyle = '';

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

    public function mount($payrollId = null, $weekId = null)
    {
        $user = Auth::user();
        $this->businessId = $user->emp_b_id;
        $this->selectedPayrollPeriodId = $payrollId;
        $this->selectedWeekId = $weekId;
    }

    public function generateCombinedReport()
    {
        if (! $this->validatePayrollReportRequest([
            'selectedPayrollPeriodId' => 'required|exists:payroll_periods,pp_id',
        ], [
            'selectedPayrollPeriodId.required' => 'Payroll period is required.',
        ])) {
            return;
        }

        $payroll = $this->resolvePayrollForWeeklyReport();
        if (! $payroll) {
            return;
        }

        $hasExportRows = ProcessedEmployeeSalary::query()
            ->where('ps_payroll_id', $this->selectedPayrollPeriodId)
            ->where('ps_b_id', $this->businessId)
            ->when($this->selectedWeekId, fn ($q) => $q->where('ps_week_id', $this->selectedWeekId))
            ->exists();

        if (! $hasExportRows) {
            $this->payrollReportSwalNoData('There is no data to download for the consolidated report. No processed salary rows match this selection.');

            return;
        }

        // Clean filters - remove empty values
        $filters = array_filter($this->filter, function($value) {
            return !is_null($value) && $value !== '';
        });

        $fileName = 'Consolidated_Payroll_Report_Weekly_' . $payroll->pp_name . '_' . Carbon::now()->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new CombinedPayrollExport(
                $this->selectedPayrollPeriodId,
                $this->businessId,
                $this->roundOffValues,
                $filters,
                $this->selectedEmployeeContactsHeading,
                $this->selectedEmployeeEarningsHeading,
                $this->selectedEmployeeDeductionsHeading,
                $this->selectedEarningHeading,
                $this->selectedDeductionHeading,
                $this->selectedDaysInfoHeading,
                $this->selectedWeekId
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

        return view('livewire.weekly-payroll.consolidated-payroll-report', [
            'payrollPeriods' => $payrollPeriods,
        ]);
    }
}
