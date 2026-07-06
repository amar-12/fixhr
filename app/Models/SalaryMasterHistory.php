<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FhSalaryMasterHistory
 *
 * @property int $sm_id
 *
 * @package App\Models
 */
class SalaryMasterHistory extends Model
{
	protected $table = 'salary_master_history';
	protected $primaryKey = 'sm_id';
	public $timestamps = true;

	protected $casts = [
		'sm_emp_id' => 'int',
		'sm_emp_b_id' => 'int',
		'sm_cal_mode' => 'string',
		'sm_is_employer_deduction' => 'string',
		'sm_monthly_ctc' => 'float',
		'sm_annual_ctc' => 'float',
		'sm_basic' => 'float',
		'sm_hra' => 'float',
		'sm_dear_allow' => 'float',
		'sm_conv_allow' => 'float',
        'sm_med_allow' => 'float',
		'sm_edu_allow' => 'float',
		'sm_other_allow' => 'float',
		'sm_employee_epf' => 'float',
		'sm_employee_esic' => 'float',
		'sm_employee_lwf' => 'float',
		'sm_employee_total_ded' => 'float',
		'sm_employer_epf' => 'float',
		'sm_employer_esic' => 'float',
		'sm_employer_lwf' => 'float',
		'sm_employer_total_ded' => 'float',
		'sm_total_earning' => 'float',
		'sm_gross_pay' => 'float',
		'sm_net_pay' => 'float',
		'sm_working_days' => 'int',
		'sm_per_day_gross' => 'float',
		'sm_per_day_ctc' => 'float',
		'sm_weekly_ctc' => 'float',
		'wef' => 'datetime',
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
	];

	protected $fillable = [
		'sm_emp_id',
		'sm_emp_b_id',
		'sm_cal_mode',
		'sm_is_employer_deduction',
		'sm_monthly_ctc',
		'sm_annual_ctc',
		'sm_basic',
		'sm_hra',
        'sm_per_day_wage',
		'sm_dear_allow',
		'sm_conv_allow',
        'sm_med_allow',
		'sm_edu_allow',
		'sm_other_allow',
		'sm_employee_epf',
		'sm_employee_esic',
		'sm_employee_lwf',
		'sm_employee_total_ded',
		'sm_employer_epf',
		'sm_employer_esic',
		'sm_employer_lwf',
		'sm_employer_total_ded',
        'sm_esic_validation_enabled',
		'sm_pf_validation_enabled',
		'sm_total_earning',
		'sm_gross_pay',
		'sm_net_pay',
		'sm_working_days',
		'sm_per_day_gross',
		'sm_per_day_ctc',
		'sm_weekly_ctc',
        'sm_annual_gross',
		'sm_fy_id',
		'sm_remark',
		'wef',
		'created_at',
		'updated_at',
	];

	public function financial_years()
    {
        return $this->belongsTo(\App\Models\FinancialYear::class, 'sm_fy_id', 'fy_id');
    }

    public function employees()
    {
        return $this->belongsTo(\App\Models\Employee::class, 'sm_emp_id', 'emp_id');
    }

    public function business()
    {
        return $this->belongsTo(\App\Models\Business::class, 'sm_emp_b_id', 'b_id');
    }

}
