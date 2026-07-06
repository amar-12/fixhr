<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * class SalaryBonus
 * 
 * @property int $bonus_id
 * @property int|null $bonus_b_id
 * @property int $bonus_emp_id
 * @property float|null $bonus_amount
 * @property string|null $bonus_type
 * @property Carbon|null $bonus_date
 * @property string|null $bonus_reason
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property Employee $fh_employee
 * @property Business|null $fh_business
 *
 * @package App\Models
 */
class SalaryBonus extends Model
{
	protected $table = 'bonuses';
	protected $primaryKey = 'bonus_id';

	protected $casts = [
		'bonus_b_id' => 'int',
		'bonus_emp_id' => 'int',
		'bonus_amount' => 'float',
		'bonus_date' => 'datetime'
	];

	protected $fillable = [
		'bonus_b_id',
		'bonus_emp_id',
		'bonus_amount',
		'bonus_type',
		'bonus_date',
		'bonus_reason'
	];

	public function fh_employee()
	{
		return $this->belongsTo(Employee::class, 'bonus_emp_id');
	}

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'bonus_b_id');
	}
}
