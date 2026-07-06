<?php

namespace App\Http\Resources\Payroll;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class ProcessedEmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    public function toArray(Request $request)
    {
        return [
            'payroll_period' => [
                'pp_id' => $this->payrollPeriod->pp_id,
                'pp_name' => $this->payrollPeriod->pp_name,
                'from' => Carbon::parse($this->payrollPeriod->pp_start_date)->format('Y-m-d'),
                'to' => Carbon::parse($this->payrollPeriod->pp_end_date)->format('Y-m-d'),
                'pp_payslip_date' => Carbon::parse($this->payrollPeriod->pp_payslip_date)->format('Y-m-d'),
            ],
            'financial_year' => [
                'fy_id' => $this->payrollPeriod->financialYear?->fy_id,
                'fy_year' => $this->payrollPeriod->financialYear?->fy_year,
            ],


            'processed_salry' => [
                'ps_id' => $this->ps_id,
                'ps_b_id' => $this->ps_b_id,
                'ps_br_id' => $this->ps_br_id,
                'ps_emp_id' => $this->ps_emp_id,
                'ps_payroll_id' => $this->ps_payroll_id,
                'ps_monthly_salary' => (string) $this->ps_monthly_salary,
                'ps_basic_salary' => (string) $this->ps_basic_salary,
                'ps_per_day_salary' => (string) $this->ps_per_day_salary,
                'ps_worked_days_salary' => (string) $this->ps_worked_days_salary,
                'ps_earnings' => (string) $this->ps_earnings,
                'ps_employee_deductions' => (string) $this->ps_employee_deductions,
                'ps_employer_deductions' => (string) $this->ps_employer_deductions,
                'ps_rem_allowance' => $this->ps_rem_allowance,
                'ps_monthly_gross' => (string) $this->ps_monthly_gross,
                'ps_monthly_net_salary' => (string) $this->ps_monthly_net_salary,
                'ps_monthly_ctc' => (string) $this->ps_monthly_ctc,
                'ps_total_days_in_month' => (string) $this->ps_total_days_in_month,
                'ps_week_off_count' => (string) $this->ps_week_off_count,
                'ps_total_month_working_days' => (string) $this->ps_total_month_working_days,
                'ps_total_days_worked' => (string) $this->ps_total_days_worked,
                'ps_present_days' => (string) (string) $this->ps_present_days,
                'ps_workable_days' => (string) (string) $this->ps_workable_days,
                'ps_upl_count' => (string) $this->ps_upl_count,
                'ps_currency' => $this->ps_currency,
                'ps_is_payslip' => $this->ps_is_payslip,
                'ps_payslip_url' => $this->ps_payslip_url,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],

        ];
    }


}
