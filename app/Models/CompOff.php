<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompOff extends Model
{
	protected $table = 'compoff';
	protected $primaryKey = 'co_id';

	protected $casts = [
		'co_b_id' => 'int',
		'co_emp_id' => 'int',
		'cop_id' => 'int',
		'co_record_id' => 'int',
		'co_request_date' => 'date',
		'co_credit_date' => 'date',
		'co_validity' => 'int',
		'co_is_expiry' => 'boolean',
		'co_alloted' => 'decimal:2',
		'co_carry_forward' => 'int',
		'co_am_id' => 'int',
		'co_status' => 'int',
		'co_next_approver' => 'int',
		'co_approved_by' => 'int',
		'co_stage_completed' => 'boolean',
	];

	protected $fillable = [
		'co_b_id',
		'co_emp_id',
		'cop_id',
		'co_record_id',
		'co_code',
		'co_request_date',
		'co_credit_date',
		'co_validity',
		'co_is_expiry',
		'co_alloted',
		'co_carry_forward',
		'co_am_id',
		'co_status',
		'co_next_approver',
		'co_approved_by',
		'co_stage_completed',
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'co_b_id');
	}

	public function fh_employee()
	{
		return $this->belongsTo(Employee::class, 'co_emp_id');
	}

	public function fh_CoPolicy() {
		return $this->belongsTo(CompOffPolicy::class, 'cop_id');
	}

	public function fh_approval_status()
	{
		return $this->belongsTo(MasterTable::class, 'co_status')->where('m_group', 'APPROVAL_STATUS');
	}

	public function fh_plan_approval_log()
	{
		return $this->hasMany(ApprovalLog::class, 'log_request_id', 'co_id')->where([['log_request_id', $this->co_id], ['log_am_id', $this->co_am_id]])->orderBy('updated_at', 'desc');
	}

	public function fh_process_approvers()
	{
		return $this->hasMany(ProcessApprover::class, 'pa_am_id', 'co_am_id');
	}

	public function fh_approval_log()
	{
		return $this->hasMany(ApprovalLog::class, 'log_request_id', 'co_id');
	}

	public function fh_approval_log2()
	{
		return $this->hasMany(ApprovalLog::class, 'log_request_id', 'co_id')->where('log_module_id', 562);
	}

	public function filteredProcessApprovers($emp_d_id)
	{
		return $this->hasMany(ProcessApprover::class, 'pa_am_id', 'co_am_id')
			->where(function ($query) use ($emp_d_id) {
				$query->where('pa_flow', 'business')
					->orWhere(function ($query) use ($emp_d_id) {
						$query->where('pa_flow', 'department')
							->where('pa_d_id', $emp_d_id);
					});
			});
	}
}
