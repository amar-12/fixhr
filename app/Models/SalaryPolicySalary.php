<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * class SalaryPolicySalary
 *
 * @property int $ps_id
 * @property int|null $ps_b_id
 * @property string $ps_name
 * @property string|null $ps_description
 * @property Carbon|null $ps_expiration_date
 * @property float|null $ps_max_bonus_percentage
 * @property float|null $ps_min_salary_increase_percentage
 * @property float|null $ps_tax_deduction_percentage
 * @property string|null $ps_eligibility_criteria
 * @property string|null $ps_adjustment_frequency
 * @property Carbon|null $ps_review_date
 * @property string|null $ps_applicable_locations
 * @property bool|null $ps_is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Collection|FhEmployeeSalary[] $fh_employee_salaries
 *
 * @package App\Models
 */
class SalaryPolicySalary extends Model
{
	protected $table = 'policy_salary';
	protected $primaryKey = 'ps_id';

	protected $casts = [
		'ps_b_id' => 'int',
		'ps_max_bonus_percentage' => 'float',
		'ps_min_salary_increase_percentage' => 'float',
		'ps_tax_deduction_percentage' => 'float',
		'ps_review_date' => 'datetime',
		'ps_is_active' => 'bool',
        'ps_employee_esic_percentage' => 'float',
        'ps_employer_esic_percentage' => 'float',
        'ps_employer_pf_percentage' => 'float',
        'ps_employee_pf_percentage' => 'float',
	];

	protected $fillable = [
		'ps_b_id',
		'ps_name',
		'ps_description',
        'ps_basic_salary_percentage',
        'ps_hra_allowance_percentage',
        'ps_conveyance_allowance_threshhold',
        'ps_medical_allowance_threshhold',
		'ps_max_bonus_percentage',
		'ps_min_salary_increase_percentage',
        'ps_employee_pf_percentage',
        'ps_employer_pf_percentage',
        'ps_employee_esic_percentage',
        'ps_employer_esic_percentage',
        'ps_pf_threshhold',
        'ps_esic_threshhold' ,
		'ps_tax_deduction_percentage',
		'ps_eligibility_criteria',
		'ps_adjustment_frequency',
		'ps_review_date',
		'ps_applicable_locations',
		'ps_is_active'
	];

	public function fh_employee_salaries()
	{
		return $this->hasMany(SalaryEmployeeSalary::class, 'es_ps_id');
	}


    public function fh_business()
	{
		return $this->belongsTo(Business::class, 'ps_b_id');
	}
}
