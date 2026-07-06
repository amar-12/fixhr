<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * class SalaryEmployeeSalary
 *
 * @property int $es_id
 * @property int|null $es_b_id
 * @property int $es_emp_id
 * @property int|null $es_ps_id
 * @property float $es_base_salary
 * @property float|null $es_hra_allowance
 * @property float|null $es_deductions
 * @property float|null $es_bonuses
 * @property float|null $es_gross_salary
 * @property float|null $es_net_salary
 * @property string|null $es_currency
 * @property Carbon $es_salary_effective_date
 * @property Carbon|null $es_sales_ary_revision_date
 * @property string|null $es_salary_grade
 * @property string|null $es_pay_frequency
 * @property float|null $es_tax_rate
 * @property string|null $es_bonus_description
 * @property bool|null $es_is_current
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Employee $fh_employee
 * @property PolicySalary|null $fh_policy_salary
 * @property Business|null $fh_business
 *
 * @package App\Models
 */
class SalaryEmployeeSalary extends Model
{
	protected $table = 'employee_salaries';
	protected $primaryKey = 'es_id';

	protected $casts = [
		'es_b_id' => 'int',
		'es_emp_id' => 'int',
		'es_ps_id' => 'int',
		'es_base_salary' => 'float',
		'es_hra_allowance' => 'float',
		'es_deductions' => 'float',
		'es_bonuses' => 'float',
		'es_gross_salary' => 'float',
        'es_perday_salary' => 'float',
		'es_net_salary' => 'float',
		'es_salary_effective_date' => 'datetime',
		'es_sales_ary_revision_date' => 'datetime',
		'es_tax_rate' => 'float',
		'es_is_current' => 'bool'
	];

	protected $fillable = [
		'es_b_id',
		'es_emp_id',
		'es_ps_id',
        'es_perday_salary',
		'es_annual_ctc',
		'es_monthly_ctc',
		'es_rem_allowance',
		'es_base_salary',
		'es_hra_allowance',
		'es_conveyance_allowance',
		'es_medical_allowance',
		'es_special_allowance',
		'es_performance_bonus',
		'es_incentives',
		'es_retention_bonus',
		'es_employee_pf_contribution',
		'es_employer_pf_contribution',
		'es_employee_esic_contribution',
		'es_employer_esic_contribution',
		'es_professional_tax',
		'es_tds',
		'es_deductions',
		'es_bonuses',
		'es_monthly_gross',
		'es_annual_gross',
		'es_monthly_net_salary',
		'es_currency',
        'es_esic_validation_enabled',
		'es_pf_validation_enabled',
		'es_salary_effective_date',
		'es_sales_ary_revision_date',
		'es_salary_grade',
		'es_pay_frequency',
		'es_tax_rate',
		'es_bonus_description',
		'es_is_current'
	];

	public function fh_employee()
	{
		return $this->belongsTo(Employee::class, 'es_emp_id')->with('fh_employee_earnings_salary', 'fh_employee_deduction_salary');
	}

	public function fh_policy_salary()
	{
		return $this->belongsTo(SalaryPolicySalary::class, 'es_ps_id');
	}

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'es_b_id');
	}
	public function fh_employee_salary()
	{
		return $this->hasOne(SalaryEmployeeSalary::class, 'es_emp_id', 'emp_id');
	}

	public function salary_earnings()
	{
		return $this->hasMany(SalaryEmployeeEarnings::class, 'es_e_emp_id', 'es_emp_id');
	}

	public function earningType()
	{
		return $this->belongsTo(SalaryAllowance::class, 'es_e_type_id', 'sa_earning_type_id');
	}
}
