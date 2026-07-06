<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * class SalaryEmployeeLeafe
 * 
 * @property int $el_id
 * @property int|null $el_b_id
 * @property int $el_emp_id
 * @property int $el_lvt_id
 * @property float|null $el_total_leaves
 * @property float|null $el_used_leaves
 * @property float|null $el_encashment_amount
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Employee $fh_employee
 * @property LeaveType $fh_leave_type
 * @property Business|null $fh_business
 *
 * @package App\Models
 */
class SalaryEmployeeLeaves extends Model
{
	protected $table = 'employee_leaves';
	protected $primaryKey = 'el_id';

	protected $casts = [
		'el_b_id' => 'int',
		'el_emp_id' => 'int',
		'el_lvt_id' => 'int',
		'el_total_leaves' => 'float',
		'el_used_leaves' => 'float',
		'el_encashment_amount' => 'float'
	];

	protected $fillable = [
		'el_b_id',
		'el_emp_id',
		'el_lvt_id',
		'el_total_leaves',
		'el_used_leaves',
		'el_encashment_amount'
	];

	public function fh_employee()
	{
		return $this->belongsTo(Employee::class, 'el_emp_id');
	}

	public function fh_leave_type()
	{
		return $this->belongsTo(SalaryLeaveType::class, 'el_lvt_id');
	}

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'el_b_id');
	}
}
