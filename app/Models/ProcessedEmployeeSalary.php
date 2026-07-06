<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessedEmployeeSalary extends Model
{
    use HasFactory;

    protected $table = 'processed_salaries'; // Replace with actual table name if different
    protected $primaryKey = 'ps_id';
    public $timestamps = true; // Enables created_at & updated_at

    protected $fillable = [
        'ps_b_id',
        'ps_br_id',
        'ps_emp_id',
        'ps_payroll_id',
        'ps_monthly_salary',
        'ps_basic_salary',
        'ps_per_day_salary',
        'ps_worked_days_salary',
        'ps_earnings',
        'ps_employee_deductions',
        'ps_employer_deductions',
        'ps_rem_allowance',
        'ps_monthly_gross',
        'ps_monthly_net_salary',
        'ps_monthly_ctc',
        'ps_total_days_in_month',
        'ps_week_off_count',
        'ps_total_month_working_days',
        'ps_total_days_worked',
        'ps_present_days',
        'ps_days_late',
        'ps_workable_days',
        'ps_esic_worked_days',
        'ps_esic_monthly_gross',
        'ps_tada_payed_amount',
        'ps_currency',
        'ps_upl_count',
        'ps_payslip_url',
        'ps_is_payslip',
        'ps_generated_by'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'ps_emp_id', 'emp_id')->with('fh_employee_salary');
    }

    public function payrollPeriod()
    {
        return $this->belongsTo(PayrollPeriod::class, 'ps_payroll_id', 'pp_id','pp_fy_id');
    }

     public function fh_employee()
    {
        return $this->hasMany(Employee::class, 'emp_b_id', 'ps_b_id');
    }

    public function financialYear()
    {
        return $this->belongsTo(FinancialYear::class, 'fy_id');
    }

    public function earnings()
    {
        return $this->hasMany(ProcessedSalaryEarning::class, 'ps_id', 'ps_id');
    }

     public function deductions()
    {
        return $this->hasMany(ProcessedSalaryDeduction::class, 'ps_id', 'ps_id');
    }





}
