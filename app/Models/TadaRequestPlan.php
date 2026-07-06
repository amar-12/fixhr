<?php
/**
 * Created by Reliese Model.
 */
namespace App\Models;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * class TadaRequestPlan
 *
 * @property int $trp_id
 * @property int|null $trp_b_id
 * @property int|null $trp_br_id
 * @property int|null $trp_emp_id
 * @property int|null $trp_pttt_id
 * @property int|null $trp_ptc_id
 * @property int|null $trp_pttm_id
 * @property string|null $trp_name
 * @property int|null $trp_purpose
 * @property int|null $trp_advance_allowance
 * @property int|null $trp_request_status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Business|null $fh_business
 * @property Branch|null $fh_branch
 * @property Employee|null $fh_employee
 * @property PolicyTadaTravelType|null $fh_policy_tada_travel_type
 * @property PolicyTadaCategory|null $fh_policy_tada_category
 * @property PolicyTadaTravelMode|null $fh_policy_tada_travel_mode
 * @property Collection|TadaExpense[] $fh_tada_expenses
 * @property Collection|TadaRequestDetail[] $fh_tada_request_details
 *
 * @package App\Models
 */
class TadaRequestPlan extends Model
{
    use SoftDeletes;
    protected $table = 'tada_request_plan';
    protected $primaryKey = 'trp_id';
    protected $casts = [
        'trp_tc_id' => 'int',
        'trp_b_id' => 'int',
        'trp_br_id' => 'int',
        'trp_emp_id' => 'int',
        'trp_pttt_id' => 'int',
        'trp_ptc_id' => 'int',
        'trp_advance_allowance' => 'int',
        'trp_module_id' => 'int',
        'trp_am_id' => 'int',
        'trp_is_expense_added'=>'int',
        'trp_is_details_added'=>'int',
        'trp_next_approver' => 'int',
        'trp_stage_completed' => 'int',
        'trp_is_claimed' => 'int',
        'trp_request_status' => 'int',
        'trp_purpose' => 'int'

    ];
    protected $fillable = [
        'trp_unique_id',
        'trp_tc_id',
        'trp_b_id',
        'trp_br_id',
        'trp_emp_id',
        'trp_pttt_id',
        'trp_ptc_id',
        'trp_module_id',
        'trp_am_id',
        'trp_name',
        'trp_purpose',
        'trp_advance_allowance',
        'trp_is_expense_added',
        'trp_is_details_added',
        'trp_request_status',
        'trp_next_approver',
        'trp_stage_completed',
        'trp_is_claimed',
        'trp_call_id',
        'trp_destination',
        'trp_start_date',
        'trp_end_date',
        'trp_start_time',
        'trp_end_time',
        'trp_remarks',
        'trp_document',
        'trp_adv_payment_processed',
        'trp_tada_payment_processed',
        'trp_exp_payment_processed'
    ];

	public function fh_plan_approval_log()
	{
		return $this->hasMany(ApprovalLog::class, 'log_am_id', 'trp_am_id')->where('log_request_id', $this->trp_id)->orderBy('updated_at','desc');
	}

    public function latestApprovalLog()
    {
        return $this->fh_plan_approval_log()->first();
    }

	public function fh_approval_log()
	{
       return $this->hasMany(ApprovalLog::class,'log_request_id','trp_id');
	}

    public function fh_approval_log2(){
        return $this->hasMany(ApprovalLog::class, 'log_request_id', 'trp_id')->where('log_module_id', 145);
    }

	public function fh_modules()
	{
		return $this->belongsTo(MasterTable::class, 'trp_module_id')->where('m_group','MODULE');
	}

    public function fh_business()
    {
        return $this->belongsTo(Business::class, 'trp_b_id');
    }
    public function fh_branch()
    {
        return $this->belongsTo(Branch::class, 'trp_br_id');
    }
    public function fh_employee()
    {
        return $this->belongsTo(Employee::class, 'trp_emp_id');
    }
    public function fh_policy_tada_travel_type()
    {
        return $this->belongsTo(PolicyTadaTravelType::class, 'trp_pttt_id');
    }
    public function fh_policy_tada_category()
    {
        return $this->belongsTo(PolicyTadaCategory::class, 'trp_ptc_id');
    }
    public function fh_policy_tada_travel_mode()
    {
        return $this->belongsTo(PolicyTadaTravelMode::class, 'trp_pttm_id');
    }
    public function fh_tada_expenses()
    {
        return $this->hasMany(TadaExpense::class, 'te_trp_id');
    }
    public function fh_tada_request_details()
    {
        return $this->hasMany(TadaRequestDetail::class, 'trd_trp_id');
    }

    public function fh_tada_claim()
    {
        return $this->hasOne(TadaClaim::class, 'tc_trp_id');
    }

    public function fh_approval_status()
	{
		return $this->belongsTo(MasterTable::class, 'trp_request_status')->where('m_group','APPROVAL_STATUS');
	}

    public function tada_metro_cities()
    {
        return $this->hasOne(TadaMetroCity::class, 'tc_trp_id');
    }

    public function fh_tada_advance_approval_log()
    {
        return $this->hasMany(AdvanceLog::class, 'adl_trp_id', 'trp_id');
    }

    public function fh_travel_purpose()
    {
        return $this->belongsTo(TravelPurpose::class, 'trp_purpose');
    }

    public function fh_process_approvers()
	{
		return $this->hasMany(ProcessApprover::class, 'pa_am_id' , 'trp_am_id');
	}

    public function filteredProcessApprovers($emp_d_id)
    {
        return $this->hasMany(ProcessApprover::class, 'pa_am_id', 'trp_am_id')
            ->where(function ($query) use ($emp_d_id) {
                $query->where('pa_flow', 'business')
                    ->orWhere(function ($query) use ($emp_d_id) {
                        $query->where('pa_flow', 'department')
                            ->where('pa_d_id', $emp_d_id);
                    });
            });
    }

    public function fh_module(){
        return $this->belongsTo(MasterTable::class, 'trp_module_id','m_id')->where('m_group', 'MODULE');
    }

    public function fh_travel_vehicle()

    {

        return $this->hasOne(TadaRequestDetail::class, 'trd_trp_id');
    }

  
}
