<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayslipConfiguration extends Model
{
    protected $table = 'payslip_configurations';
    protected $primaryKey = 'pc_id';

    protected $fillable = [
        'pc_b_id',
        'pc_show_employee_code',
        'pc_show_employee_name',
        'pc_show_designation',
        'pc_show_department',
        'pc_show_branch',
        'pc_show_doj',
        'pc_show_ip_uan',
        'pc_show_bank_details',
        'pc_show_month',
        'pc_show_earnings_breakdown',
        'pc_show_net_pay',
        'pc_show_total_ctc',
        'pc_round_off_net_salary',
        'pc_show_net_salary_in_words',
        'pc_show_working_days',
        'pc_show_month_days',
        'pc_show_days_present',
        'pc_show_salary_days',
        'pc_show_lwp_days',
        'pc_show_leaves_taken',
        'pc_show_employee_deductions_breakdown',
        'pc_show_employer_deductions_breakdown',
        'pc_show_signature',
        'pc_show_disclaimer'
    ];


    public function fh_business()
    {
        return $this->belongsTo(Business::class, 'pc_b_id');
    }


    public static function getPayslipOptionsForEmployee($businessId)
    {
        $defaults = [
            'show_employee_code' => false,
            'show_employee_name' => false,
            'show_department' => false,
            'show_designation' => false,
            'show_branch' => false,
            'show_bank_details' => false,
            'show_month' => false,
            'show_doj' => false,
            'show_month_days' => false,
            'show_salary_days' => false,
            'show_present_days' => false,
            'show_lwp_days' => false,
            'show_ip_uan' => false,
            'include_earnings' => false,
            'include_employee_deduction' => false,
            'include_employer_deduction' => false,
            'include_ctc' => false,
            'show_disclaimer' => false,
            'show_signature' => false,
        ];

        $config = self::where('pc_b_id', $businessId)->first();

        if (!$config) {
            return $defaults;
        }

        // Map database columns to UI keys
        $mappedOptions = [
            'show_employee_code' => (bool)$config->pc_show_employee_code,
            'show_employee_name' => (bool)$config->pc_show_employee_name,
            'show_department' => (bool)$config->pc_show_department,
            'show_designation' => (bool)$config->pc_show_designation,
            'show_branch' => (bool)$config->pc_show_branch,
            'show_bank_details' => (bool)$config->pc_show_bank_details,
            'show_month' => (bool)$config->pc_show_month,
            'show_doj' => (bool)$config->pc_show_doj,
            'show_month_days' => (bool)$config->pc_show_month_days,
            'show_salary_days' => (bool)$config->pc_show_salary_days,
            'show_present_days' => (bool)$config->pc_show_days_present,
            'show_lwp_days' => (bool)$config->pc_show_lwp_days,
            'show_ip_uan' => (bool)$config->pc_show_ip_uan,
            'include_earnings' => (bool)$config->pc_show_earnings_breakdown,
            'include_employee_deduction' => (bool)$config->pc_show_employee_deductions_breakdown,
            'include_employer_deduction' => (bool)$config->pc_show_employer_deductions_breakdown,
            'include_ctc' => (bool)$config->pc_show_total_ctc,
            'show_disclaimer' => (bool)$config->pc_show_disclaimer,
            'show_signature' => (bool)$config->pc_show_signature,
        ];

        return array_merge($defaults, $mappedOptions);
    }
}
