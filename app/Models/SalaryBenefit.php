<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * class SalaryBenefit
 * 
 * @property int $benefit_id
 * @property int|null $benefit_b_id
 * @property int $benefit_emp_id
 * @property string|null $benefit_type
 * @property float|null $benefit_amount
 * @property Carbon|null $benefit_effective_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Employee $fh_employee
 * @property Business|null $fh_business
 *
 * @package App\Models
 */
class SalaryBenefit extends Model
{
	protected $table = 'benefits';
	protected $primaryKey = 'benefit_id';

	protected $casts = [
		'benefit_b_id' => 'int',
		'benefit_emp_id' => 'int',
		'benefit_amount' => 'float',
		'benefit_effective_date' => 'datetime'
	];

	protected $fillable = [
		'benefit_b_id',
		'benefit_emp_id',
		'benefit_type',
		'benefit_amount',
		'benefit_effective_date'
	];

	public function fh_employee()
	{
		return $this->belongsTo(Employee::class, 'benefit_emp_id');
	}

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'benefit_b_id');
	}
}
