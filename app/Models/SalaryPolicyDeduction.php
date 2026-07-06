<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * class SalaryPolicyDeduction
 * 
 * @property int $pd_id
 * @property int|null $pd_b_id
 * @property string|null $pd_type
 * @property float|null $pd_max_deduction_percentage
 * @property Carbon|null $pd_effective_date
 * @property Carbon|null $pd_expiration_date
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property Business|null $fh_business
 * @property Collection|FhEmployee[] $fh_employees
 *
 * @package App\Models
 */
class SalaryPolicyDeduction extends Model
{
	protected $table = 'policy_deduction';
	protected $primaryKey = 'pd_id';

	protected $casts = [
		'pd_b_id' => 'int',
		'pd_max_deduction_percentage' => 'float',
		'pd_effective_date' => 'datetime',
		'pd_expiration_date' => 'datetime'
	];

	protected $fillable = [
		'pd_b_id',
		'pd_type',
		'pd_max_deduction_percentage',
		'pd_effective_date',
		'pd_expiration_date'
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'pd_b_id');
	}

	public function fh_employees()
	{
		return $this->hasMany(Employee::class, 'emp_pd_id');
	}
}
