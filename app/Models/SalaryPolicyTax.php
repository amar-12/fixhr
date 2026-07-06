<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * class SalaryPolicyTax
 * 
 * @property int $pt_id
 * @property int|null $pt_b_id
 * @property string $pt_name
 * @property float $pt_tax_rate
 * @property Carbon $pt_tax_year
 * @property float $pt_tax_paid
 * @property float $pt_min_income
 * @property float $pt_max_income
 * @property Carbon|null $pt_effective_date
 * @property Carbon|null $pt_expiration_date
 * @property float|null $pt_exemption_amount
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property Business|null $fh_business
 * @property Collection|FhEmployee[] $fh_employees
 *
 * @package App\Models
 */
class SalaryPolicyTax extends Model
{
	protected $table = 'policy_tax';
	protected $primaryKey = 'pt_id';

	protected $casts = [
		'pt_b_id' => 'int',
		'pt_tax_rate' => 'float',
		'pt_tax_year' => 'datetime',
		'pt_tax_paid' => 'float',
		'pt_min_income' => 'float',
		'pt_max_income' => 'float',
		'pt_effective_date' => 'datetime',
		'pt_expiration_date' => 'datetime',
		'pt_exemption_amount' => 'float'
	];

	protected $fillable = [
		'pt_b_id',
		'pt_name',
		'pt_tax_rate',
		'pt_tax_year',
		'pt_tax_paid',
		'pt_min_income',
		'pt_max_income',
		'pt_effective_date',
		'pt_expiration_date',
		'pt_exemption_amount'
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'pt_b_id');
	}

	public function fh_employees()
	{
		return $this->hasMany(Employee::class, 'emp_pt_id');
	}
}
