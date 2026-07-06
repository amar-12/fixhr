<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TadaClaim
 *
 * @property int $tc_id
 * @property int|null $tc_trp_id
 * @property int|null $tc_emp_id
 * @property float|null $tc_amount
 * @property float|null $tc_deduction_amount
 * @property Carbon|null $tc_approved_date
 * @property Carbon|null $tc_payment_date
 * @property float|null $tc_claimed_amount
 * @property int|null $tc_status
 * @property string|null $tc_remarks
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property MasterTable|null $fh_master_table
 * @property Employee|null $fh_employee
 * @property TadaRequestPlan|null $fh_tada_request_plan
 *
 * @package App\Models
 */
class TadaClaim extends Model
{
    use SoftDeletes;
	protected $table = 'tada_claim';
	protected $primaryKey = 'tc_id';

	protected $casts = [
		'tc_b_id' => 'int',
		'tc_trp_id' => 'int',
		'tc_emp_id' => 'int',
		'tc_am_id' => 'int',
		'tc_module_id' => 'int',
		'tc_amount' => 'float',
		'tc_deduction_amount' => 'float',
		'tc_approved_date' => 'datetime',
		'tc_payment_date' => 'datetime',
		'tc_claimed_amount' => 'float',
		'tc_next_approver' => 'int',
		'tc_stage_completed' => 'int',
		'tc_status' => 'int',
        'tc_deduction_status'=> 'int',
		'tc_da_amount'=> 'float',
	];

	protected $fillable = [
        'tc_unique_id',
		'tc_b_id',
		'tc_trp_id',
		'tc_emp_id',
		'tc_am_id',
		'tc_module_id',
		'tc_amount',
		'tc_deduction_amount',
        'tc_deduction_status',
		'tc_approved_date',
		'tc_payment_date',
		'tc_claimed_amount',
		'tc_status',
		'tc_stage_completed',
		'tc_next_approver',
		'tc_deduction_remarks',
		'tc_remarks',
		'tc_da_amount',
        'tc_is_payed',
		'transaction_date',
        'reference_no',
        'tc_payed_amount',
        'tc_da_calculation_message',
        'deleted_by',
		'tc_paid_status',
		'tc_group_claim'

	];

	public function fh_claim_approval_log()
	{
		return $this->hasMany(ApprovalLog::class, 'log_request_id', 'tc_trp_id')->where('log_am_id', $this->tc_am_id)->orderBy('updated_at','desc');
	}

	public function fh_approval_log()
	{
		return $this->hasMany(ApprovalLog::class, 'log_request_id', 'tc_trp_id');
	}

    public function fh_approval_log_employee_wise(){
        return $this->hasMany(ApprovalLog::class, 'log_request_id', 'tc_trp_id')->where('log_module_id', 146);
    }

	public function fh_claim_status()
	{
		return $this->belongsTo(MasterTable::class, 'tc_status');
	}

	public function fh_employee()
	{
		return $this->belongsTo(Employee::class, 'tc_emp_id');
	}

	public function fh_tada_request_plan()
	{
		return $this->belongsTo(TadaRequestPlan::class, 'tc_trp_id');
	}

    public function fh_deduction_log(){
        return $this->hasMany(DeductionLog::class, 'dlog_tc_id', 'tc_id');
    }

    public function fh_process_approvers()
	{
		return $this->hasMany(ProcessApprover::class, 'pa_am_id' , 'tc_am_id');
	}

    public function fh_branch(){
        return $this->belongsTo(Branch::class, 'tc_b_id');
    }

    public function filteredProcessApprovers($emp_d_id)
    {
        return $this->hasMany(ProcessApprover::class, 'pa_am_id', 'tc_am_id')
            ->where(function ($query) use ($emp_d_id) {
                $query->where('pa_flow', 'business')
                    ->orWhere(function ($query) use ($emp_d_id) {
                        $query->where('pa_flow', 'department')
                            ->where('pa_d_id', $emp_d_id);
                    });
            });
    }

    public function canApprove()
    {
        return $this->belongsTo(EmployeeApprovalMapping::class,'gtp_emp_id'  ,'eam_emp_id');
    }

    public function getApproverIds()
    {
        return array_filter([$this->eam_approver_manager_1, $this->eam_approver_manager_2]);
    }

    public function fh_module()
    {
        return $this->belongsTo(MasterTable::class, 'tc_module_id', 'm_id')->where('m_group', 'MODULE');
    }
}
