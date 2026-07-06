<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * class SalaryPayslip
 * 
 * @property int $p_id
 * @property int|null $p_b_id
 * @property int $p_pr_id
 * @property string|null $p_payslip_url
 * @property Carbon|null $p_generated_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property Business|null $fh_business
 *
 * @package App\Models
 */
class SalaryPayslip extends Model
{
	protected $table = 'payslips';
	protected $primaryKey = 'p_id';

	protected $casts = [
		'p_b_id' => 'int',
		'p_pr_id' => 'int',
		'p_generated_at' => 'datetime'
	];

	protected $fillable = [
		'p_b_id',
		'p_emp_id',
		'p_pr_id',
		'p_payslip_url',
		'p_generated_at'
	];


	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'p_b_id');
	}
}
