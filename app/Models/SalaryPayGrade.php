<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * class SalaryPayGrade
 * 
 * @property int $pg_id
 * @property int|null $pg_b_id
 * @property int $pg_grade_id
 * @property float $pg_min_salary
 * @property float $pg_max_salary
 * @property bool|null $pg_benefits_eligibility
 * @property float|null $pg_bonus_percentage
 * @property float|null $pg_overtime_rate
 * @property int|null $pg_leave_allowance
 * @property string|null $pg_description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Grade $fh_grade
 * @property Business|null $fh_business
 * @property Collection|FhPolicySalaryStructure[] $fh_policy_salary_structures
 *
 * @package App\Models
 */
class SalaryPayGrade extends Model
{
	protected $table = 'pay_grades';
	protected $primaryKey = 'pg_id';

	protected $casts = [
		'pg_b_id' => 'int',
		'pg_grade_id' => 'int',
		'pg_min_salary' => 'float',
		'pg_max_salary' => 'float',
		'pg_benefits_eligibility' => 'bool',
		'pg_bonus_percentage' => 'float',
		'pg_overtime_rate' => 'float',
		'pg_leave_allowance' => 'int'
	];

	protected $fillable = [
		'pg_b_id',
		'pg_grade_id',
		'pg_min_salary',
		'pg_max_salary',
		'pg_benefits_eligibility',
		'pg_bonus_percentage',
		'pg_overtime_rate',
		'pg_leave_allowance',
		'pg_description'
	];

	public function fh_grade()
	{
		return $this->belongsTo(Grade::class, 'pg_grade_id');
	}

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'pg_b_id');
	}

	public function fh_policy_salary_structures()
	{
		return $this->hasMany(SalaryPolicySalaryStructure::class, 'ssp_pg_id');
	}
}
