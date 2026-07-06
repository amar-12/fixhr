<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AdvanceLog
 *
 * @property int $adl_id
 * @property int|null $adl_trp_id
 * @property float|null $adl_requested_amount
 * @property float|null $adl_reimburse_amount
 * @property int|null $adl_approver_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property TadaRequestPlan|null $fh_tada_request_plan
 * @property Employee|null $fh_employee
 *
 * @package App\Models
 */
class AdvanceLog extends Model
{
	protected $table = 'advance_logs';
	protected $primaryKey = 'adl_id';

	protected $casts = [
		'adl_trp_id' => 'int',
		'adl_requested_amount' => 'float',
		'adl_reimburse_amount' => 'float',
		'adl_approver_id' => 'int'
	];

	protected $fillable = [
		'adl_trp_id',
		'adl_b_id',
		'adl_requested_amount',
		'adl_reimburse_amount',
		'adl_approver_id',
		'adl_remark',
        'adl_module_id',
        'adl_am_id',
        'adl_request_status',
        'adl_next_approver',
        'adl_stage_completed',
	];

	public function fh_tada_request_plan()
	{
		return $this->belongsTo(TadaRequestPlan::class, 'adl_trp_id');
	}

	public function fh_employee()
	{
		return $this->belongsTo(Employee::class, 'adl_approver_id');
	}

	public function fh_adl_request_status()
	{
		return $this->belongsTo(MasterTable::class, 'adl_request_status');
	}

    public function fh_plan_approval_log()
	{
		return $this->hasMany(ApprovalLog::class, 'log_am_id', 'adl_am_id')->where('log_request_id', $this->adl_id)->orderBy('updated_at','desc');
	}

    public function fh_process_approvers()
	{
		return $this->hasMany(ProcessApprover::class, 'pa_am_id' , 'adl_am_id');
	}

    public function fh_approval_log2(){
        return $this->hasMany(ApprovalLog::class, 'log_request_id', 'adl_id')->where('log_module_id', 199);
    }

    public function fh_approval_status()
	{
		return $this->belongsTo(MasterTable::class, 'adl_request_status')->where('m_group','APPROVAL_STATUS');
	}

    public function filteredProcessApprovers($emp_d_id)
    {
        return $this->hasMany(ProcessApprover::class, 'pa_am_id', 'adl_am_id')
            ->where(function ($query) use ($emp_d_id) {
                $query->where('pa_flow', 'business')
                    ->orWhere(function ($query) use ($emp_d_id) {
                        $query->where('pa_flow', 'department')
                            ->where('pa_d_id', $emp_d_id);
                    });
            });
    }
}
