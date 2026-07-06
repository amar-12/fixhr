<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * class SalaryPolicySalaryStructure
 * 
 * @property int $ssp_id
 * @property int|null $ssp_b_id
 * @property int $ssp_pg_id
 * @property float|null $ssp_min_salary
 * @property float|null $ssp_max_salary
 * @property string|null $ssp_currency
 * @property Carbon|null $ssp_effective_date
 * @property Carbon|null $ssp_expiration_date
 * 
 * @property PayGrade $fh_pay_grade
 * @property Business|null $fh_business
 * @property Collection|FhEmployee[] $fh_employees
 *
 * @package App\Models
 */
class SalaryPolicySalaryStructure extends Model
{
	protected $table = 'policy_salary_structure';
	protected $primaryKey = 'ssp_id';
	public $timestamps = false;

	protected $casts = [
		'ssp_b_id' => 'int',
		'ssp_pg_id' => 'int',
		'ssp_min_salary' => 'float',
		'ssp_max_salary' => 'float',
		'ssp_effective_date' => 'datetime',
		'ssp_expiration_date' => 'datetime'
	];

	protected $fillable = [
		'ssp_b_id',
		'ssp_pg_id',
		'ssp_min_salary',
		'ssp_max_salary',
		'ssp_currency',
		'ssp_effective_date',
		'ssp_expiration_date'
	];

	public function fh_pay_grade()
	{
		return $this->belongsTo(SalaryPayGrade::class, 'ssp_pg_id');
	}

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'ssp_b_id');
	}

	public function fh_employees()
	{
		return $this->hasMany(Employee::class, 'emp_ssp_id');
	}
}
