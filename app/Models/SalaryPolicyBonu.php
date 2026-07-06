<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * class SalaryPolicyBonu
 * 
 * @property int $pb_id
 * @property int|null $pb_b_id
 * @property string|null $pb_bonus_criteria
 * @property float|null $pb_max_bonus_amount
 * @property Carbon|null $pb_effective_date
 * @property Carbon|null $pb_expiration_date
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property Business|null $fh_business
 * @property Collection|FhEmployee[] $fh_employees
 *
 * @package App\Models
 */
class SalaryPolicyBonu extends Model
{
	protected $table = 'policy_bonus';
	protected $primaryKey = 'pb_id';

	protected $casts = [
		'pb_b_id' => 'int',
		'pb_max_bonus_amount' => 'float',
		'pb_effective_date' => 'datetime',
		'pb_expiration_date' => 'datetime'
	];

	protected $fillable = [
		'pb_b_id',
		'pb_bonus_criteria',
		'pb_max_bonus_amount',
		'pb_effective_date',
		'pb_expiration_date'
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'pb_b_id');
	}

	public function fh_employees()
	{
		return $this->hasMany(Employee::class, 'emp_pb_id');
	}
}
