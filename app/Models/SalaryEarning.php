<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * class SalaryEarning
 * 
 * @property int $earn_id
 * @property int|null $earn_b_id
 * @property int $earn_pr_id
 * @property string $earn_description
 * @property float $earn_amount
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property PayrollRecord $fh_payroll_record
 * @property Business|null $fh_business
 *
 * @package App\Models
 */
class SalaryEarning extends Model
{
	protected $table = 'earnings';
	protected $primaryKey = 'earn_id';

	protected $casts = [
		'earn_b_id' => 'int',
		'earn_pr_id' => 'int',
		'earn_amount' => 'float'
	];

	protected $fillable = [
		'earn_b_id',
		'earn_pr_id',
		'earn_description',
		'earn_amount'
	];

	public function fh_payroll_record()
	{
		return $this->belongsTo(SalaryPayrollRecord::class, 'earn_pr_id');
	}

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'earn_b_id');
	}
}
