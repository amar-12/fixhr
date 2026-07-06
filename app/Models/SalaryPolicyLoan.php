<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * class SalaryPolicyLoan
 * 
 * @property int $pl_id
 * @property int|null $pl_b_id
 * @property float|null $pl_max_loan_amount
 * @property int|null $pl_max_loan_duration
 * @property float|null $pl_interest_rate
 * @property bool|null $pl_requires_approval
 * @property Carbon|null $pl_effective_date
 * @property Carbon|null $pl_expiration_date
 * 
 * @property Business|null $fh_business
 * @property Collection|FhEmployee[] $fh_employees
 *
 * @package App\Models
 */
class SalaryPolicyLoan extends Model
{
	protected $table = 'policy_loan';
	protected $primaryKey = 'pl_id';
	public $timestamps = false;

	protected $casts = [
		'pl_b_id' => 'int',
		'pl_max_loan_amount' => 'float',
		'pl_max_loan_duration' => 'int',
		'pl_interest_rate' => 'float',
		'pl_requires_approval' => 'bool',
		'pl_effective_date' => 'datetime',
		'pl_expiration_date' => 'datetime'
	];

	protected $fillable = [
		'pl_b_id',
		'pl_max_loan_amount',
		'pl_max_loan_duration',
		'pl_interest_rate',
		'pl_requires_approval',
		'pl_effective_date',
		'pl_expiration_date'
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'pl_b_id');
	}

	public function fh_employees()
	{
		return $this->hasMany(Employee::class, 'emp_pl_id');
	}
}
