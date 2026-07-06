<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * class SalaryLeaveType
 * 
 * @property int $lvt_id
 * @property string $lvt_name
 * @property string $lvt_code
 * @property int|null $lvt_max_leave_per_year
 * @property bool|null $lvt_carry_forward
 * @property bool|null $lvt_is_paid_leave
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|FhEmployeeLeafe[] $fh_employee_leaves
 *
 * @package App\Models
 */
class SalaryLeaveType extends Model
{
	protected $table = 'leave_types';
	protected $primaryKey = 'lvt_id';

	protected $casts = [
		'lvt_max_leave_per_year' => 'int',
		'lvt_carry_forward' => 'bool',
		'lvt_is_paid_leave' => 'bool'
	];

	protected $fillable = [
		'lvt_name',
		'lvt_code',
		'lvt_max_leave_per_year',
		'lvt_carry_forward',
		'lvt_is_paid_leave'
	];

	public function fh_employee_leaves()
	{
		return $this->hasMany(SalaryEmployeeLeaves::class, 'el_lvt_id');
	}
}
