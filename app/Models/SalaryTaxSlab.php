<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FhSalaryTaxSlab
 *
 * @property int $sts_id
 * @property int $sts_std_id
 * @property string $sts_nature_of_payment
 * @property string|null $sts_state
 * @property float $sts_min_income
 * @property float|null $sts_max_income
 * @property float|null $sts_tax_percentage_amount
 * @property float|null $sts_fixed_tax_amount
 * @property Carbon|null $sts_created_at
 * @property Carbon|null $sts_updated_at
 *
 * @property Business|null $fh_business
 * @property StatutoryDeduction $fh_statutory_deduction
 *
 * @package App\Models
 */
class SalaryTaxSlab extends Model
{
	protected $table = 'fh_salary_tax_slabs';
	protected $primaryKey = 'sts_id';
	public $timestamps = false;

	protected $casts = [
		'sts_b_id' => 'int',
		'sts_std_id' => 'int',
		'sts_min_income' => 'float',
		'sts_max_income' => 'float',
		'sts_tax_percentage_amount' => 'float',
		'sts_fixed_tax_amount' => 'float',
	];

	protected $fillable = [
		'sts_b_id',
		'sts_std_id',
		'sts_nature_of_payment',
		'sts_state',
		'sts_min_income',
		'sts_max_income',
		'sts_tax_percentage_amount',
		'sts_fixed_tax_amount'
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'sts_b_id');
	}

	public function fh_statutory_deduction()
	{
		return $this->belongsTo(StatutoryDeduction::class, 'sts_std_id');
	}
}
