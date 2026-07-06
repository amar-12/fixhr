<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * class SalaryDeduction
 * 
 * @property int $deduct_id
 * @property int|null $deduct_b_id
 * @property int $deduct_pr_id
 * @property string $deduct_description
 * @property float $deduct_amount
 * @property string $deduct_deduction_type
 * @property Carbon $deduct_effective_date
 * @property bool|null $deduct_is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property PayrollRecord $fh_payroll_record
 * @property Business|null $fh_business
 *
 * @package App\Models
 */
class SalaryDeduction extends Model
{
	protected $table = 'deductions';
	protected $primaryKey = 'deduct_id';

	protected $casts = [
		'deduct_b_id' => 'int',
		'deduct_pr_id' => 'int',
		'deduct_amount' => 'float',
		'deduct_effective_date' => 'datetime',
		'deduct_is_active' => 'bool'
	];

	protected $fillable = [
		'deduct_b_id',
		'deduct_pr_id',
		'deduct_description',
		'deduct_amount',
		'deduct_deduction_type',
		'deduct_effective_date',
		'deduct_is_active'
	];

	public function fh_payroll_record()
	{
		return $this->belongsTo(SalaryPayrollRecord::class, 'deduct_pr_id');
	}

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'deduct_b_id');
	}
}
