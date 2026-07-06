<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ApprovalModule
 *
 * @property int $am_id
 * @property int $am_b_id
 * @property int|null $am_module_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property MasterTable|null $fh_master_table
 *
 * @package App\Models
 */
class ApprovalModule extends Model
{
	use SoftDeletes;
	protected $table = 'approval_modules';
	protected $primaryKey = 'am_id';

	protected $casts = [
		'am_b_id' => 'int',
		'am_module_id' => 'int',
		'am_status' => 'int'
	];

	protected $fillable = [
		'am_b_id',
		'am_module_id',
		'am_name',
		'am_exp_rej_day',
		'am_noti_before_days',
		'am_description',
		'am_exe_on',
		'am_status'
	];

	public function fh_modules()
	{
		return $this->belongsTo(MasterTable::class, 'am_module_id')->where('m_group','MODULE');
	}

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'am_b_id');
	}

	public function fh_action_upon_rejections()
	{
		return $this->hasOne(ActionUponRejection::class, 'aur_am_id');
	}

	public function fh_approval_logs()
	{
		return $this->hasMany(ApprovalLog::class, 'log_am_id');
	}

	public function fh_process_approvers()
	{
		return $this->hasMany(ProcessApprover::class, 'pa_am_id');
	}

	public function fh_rule_criteria()
	{
		return $this->hasMany(RuleCriterion::class, 'rc_am_id');
	}

    public function filteredProcessApprovers($emp_d_id)
    {
        return $this->hasMany(ProcessApprover::class, 'pa_am_id', 'am_id')
            ->where(function ($query) use ($emp_d_id) {
                $query->where('pa_flow', 'business')
                    ->orWhere(function ($query) use ($emp_d_id) {
                        $query->where('pa_flow', 'department')
                            ->where('pa_d_id', $emp_d_id);
                    });
            });
    }
}
