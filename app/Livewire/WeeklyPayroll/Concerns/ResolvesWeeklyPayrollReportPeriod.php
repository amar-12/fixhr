<?php

namespace App\Livewire\WeeklyPayroll\Concerns;

use App\Models\AttendanceSummary;
use App\Models\PayrollPeriod;
use App\Models\ProcessedEmployeeSalary;

trait ResolvesWeeklyPayrollReportPeriod
{
    use WeeklyPayrollReportSwal;

    /**
     * Weekly runs often keep the payroll period as "not fully processed" (pp_is_processed === 121)
     * while processed_salaries rows exist per week. When a week is selected, allow export if that
     * week has rows; otherwise keep the classic monthly gate (period must be processed).
     */
    protected function resolvePayrollForWeeklyReport(): ?PayrollPeriod
    {
        $payroll = PayrollPeriod::where('pp_b_id', $this->businessId)
            ->find($this->selectedPayrollPeriodId);

        if (! $payroll) {
            $this->payrollReportSwalError('Unable to export', 'Invalid payroll period for this business.');

            return null;
        }

        if ($this->selectedWeekId) {
            $weekEmployeeIds = AttendanceSummary::query()
                ->where('as_pp_id', $this->selectedPayrollPeriodId)
                ->where('as_week_id', $this->selectedWeekId)
                ->where('as_b_id', $this->businessId)
                ->pluck('as_emp_id')
                ->unique()
                ->values()
                ->toArray();

            $hasWeekData = ProcessedEmployeeSalary::query()
                ->where('ps_payroll_id', $this->selectedPayrollPeriodId)
                ->where('ps_b_id', $this->businessId)
                ->where(function ($q) use ($weekEmployeeIds) {
                    $q->where('ps_week_id', $this->selectedWeekId)
                        ->orWhere(function ($qq) use ($weekEmployeeIds) {
                            $qq->where(function ($legacyWeekScope) {
                                $legacyWeekScope->whereNull('ps_week_id')
                                    ->orWhere('ps_week_id', 0);
                            });

                            if (! empty($weekEmployeeIds)) {
                                $qq->whereIn('ps_emp_id', $weekEmployeeIds);
                            } else {
                                $qq->whereRaw('1=0');
                            }
                        });
                })
                ->exists();

            if (! $hasWeekData) {
                $this->payrollReportSwalNoData('No processed salaries for this week. Process payroll for this week first.');

                return null;
            }

            return $payroll;
        }

        if ($payroll->pp_is_processed == 121) {
            $this->payrollReportSwalNoData('Salaries are not processed for this payroll period yet. Complete processing before downloading reports.');

            return null;
        }

        return $payroll;
    }
}
