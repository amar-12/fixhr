<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LeaveType
 *
 * @property int $lvt_id
 * @property int|null $lvt_pl_id
 * @property int|null $lvt_cat_type_id
 * @property int|null $lvt_leave_cycle_id
 * @property float|null $lvt_days_per_year
 * @property int|null $lvt_unused_leave_rule_id
 * @property float|null $lvt_leave_accrual_rate
 * @property float|null $lvt_carry_forward
 * @property int|null $lvt_applicable_to_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property FhPolicyLeafe|null $fh_policy_leafe
 * @property FhMasterTable|null $fh_master_table
 * @property Collection|FhEmployeeLeafe[] $fh_employee_leaves
 *
 * @package App\Models
 */
class LeaveType extends Model
{
	protected $table = 'leave_types';
	protected $primaryKey = 'lvt_id';

	protected $casts = [
		'lvt_pl_id' => 'int',
		'lvt_cat_type_id' => 'int',
		'lvt_leave_cycle_id' => 'int',
		'lvt_days_per_year' => 'float',
		'lvt_unused_leave_rule_id' => 'int',
		'lvt_leave_accrual_rate' => 'float',
		'lvt_carry_forward' => 'float',
		'lvt_applicable_to_id' => 'int',
		'lvt_priority' => 'int',
		'lvt_encashable' => 'boolean',
	];

	protected $fillable = [
		'lvt_pl_id',
		'lvt_cat_type_id',
		'lvt_leave_cycle_id',
		'lvt_days_per_year',
		'lvt_unused_leave_rule_id',
		'lvt_leave_accrual_rate',
		'lvt_carry_forward',
		'lvt_applicable_to_id',
        'lvt_el_per_period',
        'lvt_is_sandwich',
		'lvt_encashable',
		'lvt_priority',
	];

	public function fh_policy_leave()
	{
		return $this->belongsTo(PolicyLeave::class, 'lvt_pl_id');
	}

	public function fh_master_table()
	{
		return $this->belongsTo(MasterTable::class, 'lvt_applicable_to_id');
	}

    public function fh_leave_cat_type()
	{
		return $this->belongsTo(MasterTable::class, 'lvt_cat_type_id');
	}

    public function fh_leave_cycle()
	{
		return $this->belongsTo(MasterTable::class, 'lvt_leave_cycle_id');
	}

    public function fh_leave_requests()
	{
		return $this->hasMany(LeaveRequest::class, 'lvt_pl_id');
	}
}
