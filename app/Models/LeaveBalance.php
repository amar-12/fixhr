<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LeaveBalance
 *
 * @property int $lb_id
 * @property int|null $lb_b_id
 * @property int $lb_emp_id
 * @property int|null $lb_cat_type_id
 * @property int|null $lb_month
 * @property int|null $lb_year
 * @property float|null $lb_alloted_leave
 * @property float|null $lb_taken_leave
 * @property float|null $lb_balance_remaining_leave
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Business|null $fh_business
 * @property Employee $fh_employee
 * @property LeaveType|null $fh_leave_type
 *
 * @package App\Models
 */
class LeaveBalance extends Model
{
	protected $table = 'leave_balance';
	protected $primaryKey = 'lb_id';

	protected $casts = [
		'lb_b_id' => 'int',
		'lb_emp_id' => 'int',
		'lb_cat_type_id' => 'int',
		'lb_month' => 'int',
		'lb_year' => 'int',
		'lb_alloted_leave' => 'float',
		'lb_taken_leave' => 'float',
		'lb_balance_remaining_leave' => 'float'
	];

	protected $fillable = [
		'lb_b_id',
		'lb_emp_id',
		'lb_cat_type_id',
		'lb_month',
		'lb_year',
		'lb_alloted_leave',
		'lb_taken_leave',
		'lb_balance_remaining_leave',
        'lb_carried_forward'
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'lb_b_id');
	}

	public function fh_employee()
	{
		return $this->belongsTo(Employee::class, 'lb_emp_id');
	}

    public function fh_leave_type()
	{
		return $this->belongsTo(MasterTable::class, 'lb_cat_type_id')->where('m_group', 'LEAVE_CATEGORY');
	}
    public function employee(){
        return $this->belongsTo(Employee::class, 'lb_emp_id', 'emp_id');
    }
}
