<?php

namespace App\Http\Resources\Payroll;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayslipResource extends JsonResource
{
    protected $config;

    public function __construct($resource, $config = null)
    {
        parent::__construct($resource);
        $this->config = $config;
    }

    public function toArray(Request $request)
    {
        // ✅ Use injected config (don't query again)
        $options = $this->config ? $this->extractPayslipOptions($this->config) : [];
        // dd($options);

        return [
            'employee_name' => $this->employee_name,
            'employee_id' => $this->employee->emp_code ?? null,
            'paymentmode' => $this->employee->emp_paymentmode ?? null,
            'pan_number' => $this->employee->emp_pan_number ?? null,
            'designation' => $this->employee->fh_designation->d_name ?? null,
            'department' => $this->employee->fh_department->dept_name ?? null,
            'branch' => $this->employee->fh_branch->br_name ?? null,
            'business' => $this->employee->fh_business->b_name ?? null,

            'basic_salary' => $this->basic_salary,
            'monthly_salary' => $this->monthly_salary,
            'net_salary' => $this->net_salary,
            'net_salary_words' => $this->net_salary_words,

            'earnings' => $this->earnings,
            'employee_deductions' => $this->employee_deductions,
            'employer_deductions' => $this->employer_deductions,

            'financial_year' => [
                'fy_id' => $this->payroll_period->financialYear->fy_id,
                'fy_year' => $this->payroll_period->financialYear->fy_year,
                'fy_start_date' => $this->payroll_period->financialYear->fy_start_date,
                'fy_end_date' => $this->payroll_period->financialYear->fy_end_date,
                'fy_is_current' => $this->payroll_period->financialYear->fy_is_current,
            ],

            'payroll_period' => [
                'pp_id' => $this->payroll_period->pp_id,
                'pp_name' => $this->payroll_period->pp_name,
                'from' => $this->payroll_period->pp_start_date,
                'to' => $this->payroll_period->pp_end_date,
                'month' => $this->payroll_period->month->m_name ?? null,
            ],

            'processed_salary' => [
                'ps_id' => $this->processedSalary->ps_id,
                'ps_monthly_ctc' => $this->processedSalary->ps_monthly_ctc,
                'ps_monthly_gross' => $this->processedSalary->ps_monthly_gross,
                'ps_monthly_net_salary' => $this->processedSalary->ps_monthly_net_salary,
                'ps_basic_salary' => $this->basic_amount,
                'ps_workable_days' => $this->processedSalary->ps_workable_days,
                'ps_total_days_worked' => $this->processedSalary->ps_total_days_worked,
                'lwp' => $this->processedSalary->ps_upl_count,
            ],

            // Direct config flags (optional)
            'show_employee_code' => (bool) ($this->config->pc_show_employee_code ?? false),
            'show_employee_name' => (bool) ($this->config->pc_show_employee_name ?? false),
            'show_designation' => (bool) ($this->config->pc_show_designation ?? false),
            'show_department' => (bool) ($this->config->pc_show_department ?? false),
            'show_branch' => (bool) ($this->config->pc_show_branch ?? false),
            'show_doj' => (bool) ($this->config->pc_show_doj ?? false),
            'show_ip_uan' => (bool) ($this->config->pc_show_ip_uan ?? false),
            'show_bank_details' => (bool) ($this->config->pc_show_bank_details ?? false),
            'show_month' => (bool) ($this->config->pc_show_month ?? false),
            'show_earnings_breakdown' => (bool) ($this->config->pc_show_earnings_breakdown ?? false),
            'show_employee_deductions_breakdown' => (bool) ($this->config->pc_show_employee_deductions_breakdown ?? false),
            'show_employer_deductions_breakdown' => (bool) ($this->config->pc_show_employer_deductions_breakdown ?? false),
            'show_net_pay' => (bool) ($this->config->pc_show_net_pay ?? false),
            'show_total_ctc' => (bool) ($this->config->pc_show_total_ctc ?? false),
            'round_off_net_salary' => (bool) ($this->config->pc_round_off_net_salary ?? false),
            'show_net_salary_in_words' => (bool) ($this->config->pc_show_net_salary_in_words ?? false),
            'show_working_days' => (bool) ($this->config->pc_show_working_days ?? false),
            'show_month_days' => (bool) ($this->config->pc_show_month_days ?? false),
            'show_days_present' => (bool) ($this->config->pc_show_days_present ?? false),
            'show_salary_days' => (bool) ($this->config->pc_show_salary_days ?? false),
            'show_lwp_days' => (bool) ($this->config->pc_show_lwp_days ?? false),
            'show_leaves_taken' => (bool) ($this->config->pc_show_leaves_taken ?? false),
            'show_signature' => (bool) ($this->config->pc_show_signature ?? false),
            'show_disclaimer' => (bool) ($this->config->pc_show_disclaimer ?? false),

            // Blade-compatible flag set
            'payslipOptions' => $options,

            'payroll_period_id' => $this->payroll_period->pp_id,
            'payslip_pdf_url' => url('/api/admin/payroll/emp_payslip_pdf/' . md5($this->payroll_period->pp_id . '_' . $this->employee->emp_id)),
        ];
    }

    private function extractPayslipOptions($config)
    {
        return [
            'show_employee_code' => (bool) $config->pc_show_employee_code,
            'show_employee_name' => (bool) $config->pc_show_employee_name,
            'show_designation' => (bool) $config->pc_show_designation,
            'show_department' => (bool) $config->pc_show_department,
            'show_branch' => (bool) $config->pc_show_branch,
            'show_doj' => (bool) $config->pc_show_doj,
            'show_ip_uan' => (bool) $config->pc_show_ip_uan,
            'show_bank_details' => (bool) $config->pc_show_bank_details,
            'show_month' => (bool) $config->pc_show_month,
            'show_earnings_breakdown' => (bool) $config->pc_show_earnings_breakdown,
            'show_employee_deductions_breakdown' => (bool) $config->pc_show_employee_deductions_breakdown,
            'show_employer_deductions_breakdown' => (bool) $config->pc_show_employer_deductions_breakdown,
            'show_net_pay' => (bool) $config->pc_show_net_pay,
            'show_total_ctc' => (bool) $config->pc_show_total_ctc,
            'round_off_net_salary' => (bool) $config->pc_round_off_net_salary,
            'show_net_salary_in_words' => (bool) $config->pc_show_net_salary_in_words,
            'show_working_days' => (bool) $config->pc_show_working_days,
            'show_month_days' => (bool) $config->pc_show_month_days,
            'show_days_present' => (bool) $config->pc_show_days_present,
            'show_salary_days' => (bool) $config->pc_show_salary_days,
            'show_lwp_days' => (bool) $config->pc_show_lwp_days,
            'show_leaves_taken' => (bool) $config->pc_show_leaves_taken,
            'show_signature' => (bool) $config->pc_show_signature,
            'show_disclaimer' => (bool) $config->pc_show_disclaimer,
        ];
    }
}
