<?php

namespace App\Livewire\WeeklyPayroll;

use App\Livewire\WeeklyPayroll\Concerns\ResolvesWeeklyPayrollReportPeriod;
use App\Exports\Salary\BankSheetExport;
use App\Models\AttendanceSummary;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class BankSheetReport extends Component
{
    use ResolvesWeeklyPayrollReportPeriod;

    public $selectedPayrollPeriodId;

    public $roundOffValues = false;

    public $businessId;

    /** @var int|null Payroll period week (ppw_id) — limits rows to this weekly pay run */
    public $selectedWeekId;

    public $showButton = true;

    public $buttonStyle = '';

    public function mount($payrollId = null, $weekId = null)
    {
        $user = Auth::user();
        $this->businessId = $user->emp_b_id;
        $this->selectedPayrollPeriodId = $payrollId;
        $this->selectedWeekId = $weekId;
    }

    public function generateReport()
    {
        if (! $this->validatePayrollReportRequest(
            ['selectedPayrollPeriodId' => 'required|exists:payroll_periods,pp_id'],
            ['selectedPayrollPeriodId.required' => 'Payroll period is required.']
        )) {
            return;
        }

        $user = Auth::user();
        $payrollPeriodId = $this->selectedPayrollPeriodId;
        $amountCheck = 0;

        $payroll = $this->resolvePayrollForWeeklyReport();
        if (! $payroll) {
            return;
        }

        $weekEmployeeIds = [];
        if ($this->selectedWeekId) {
            $weekEmployeeIds = AttendanceSummary::query()
                ->where('as_pp_id', $payrollPeriodId)
                ->where('as_week_id', $this->selectedWeekId)
                ->where('as_b_id', $this->businessId)
                ->pluck('as_emp_id')
                ->unique()
                ->values()
                ->toArray();
        }

        $data = Employee::with([
            'processedSalaries' => function ($q) use ($payrollPeriodId, $weekEmployeeIds) {
                $q->where('ps_payroll_id', $payrollPeriodId);
                if ($this->selectedWeekId) {
                    $q->where(function ($weekScoped) use ($weekEmployeeIds) {
                        $weekScoped->where('ps_week_id', $this->selectedWeekId)
                            ->orWhere(function ($legacyWeekScope) use ($weekEmployeeIds) {
                                $legacyWeekScope->where(function ($legacyNullOrZero) {
                                    $legacyNullOrZero->whereNull('ps_week_id')
                                        ->orWhere('ps_week_id', 0);
                                });

                                if (! empty($weekEmployeeIds)) {
                                    $legacyWeekScope->whereIn('ps_emp_id', $weekEmployeeIds);
                                } else {
                                    $legacyWeekScope->whereRaw('1=0');
                                }
                            });
                    });
                }
            },
        ])
            ->whereHas('processedSalaries', function ($q) use ($payrollPeriodId, $weekEmployeeIds) {
                $q->where('ps_payroll_id', $payrollPeriodId);
                if ($this->selectedWeekId) {
                    $q->where(function ($weekScoped) use ($weekEmployeeIds) {
                        $weekScoped->where('ps_week_id', $this->selectedWeekId)
                            ->orWhere(function ($legacyWeekScope) use ($weekEmployeeIds) {
                                $legacyWeekScope->where(function ($legacyNullOrZero) {
                                    $legacyNullOrZero->whereNull('ps_week_id')
                                        ->orWhere('ps_week_id', 0);
                                });

                                if (! empty($weekEmployeeIds)) {
                                    $legacyWeekScope->whereIn('ps_emp_id', $weekEmployeeIds);
                                } else {
                                    $legacyWeekScope->whereRaw('1=0');
                                }
                            });
                    });
                }
            })
            ->where('emp_b_id', $this->businessId)
            ->get();

        if ($data->isEmpty()) {
            $this->payrollReportSwalNoData('There is no data to download for the bank sheet. No employees with processed salary match this selection.');

            return;
        }

        $business = DB::table('businesses')->where('b_id', $this->businessId)->first();
        $fileName = 'BankSheetReport_Weekly_'.now()->format('Y-m-d').'.xlsx';

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
        return view('livewire.weekly-payroll.bank-sheet-report');
    }

    public function getGrandTotal()
    {
        return $this->data->sum(function ($emp) {
            return $emp->processedSalaries->first()->ps_net_salary;
        });
    }
}
