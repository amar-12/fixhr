<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class LeaveRequest
 *
 * @property int $lvr_id
 * @property int|null $lvr_b_id
 * @property int $lvr_emp_id
 * @property int $lvr_pl_id
 * @property Carbon $lvr_start_date
 * @property Carbon $lvr_end_date
 * @property int|null $lvr_total_leave_days
 * @property string|null $lvr_status
 * @property int|null $lvr_approved_by
 * @property Carbon|null $created_at
 *
 * @property Carbon|null $updated_at
 *
 * @property Employee $fh_employee
 * @property PolicyLeave $fh_policy_leave_type
 * @property PolicyAttendance $fh_attendance_policy
 *
 * @package App\Models
 */
class LeaveRequest extends Model
{
    use SoftDeletes;
	protected $table = 'leave_requests';
	protected $primaryKey = 'lvr_id';

	protected $casts = [
		'lvr_b_id' => 'int',
		'lvr_emp_id' => 'int',
		'lvr_pl_id' => 'int',
		'lvr_start_date' => 'datetime',
		'lvr_end_date' => 'datetime',
		'lvr_total_leave_days' => 'float',
		'lvr_approved_by' => 'int',
		'lvr_is_comp_off' => 'boolean',
	];

	protected $fillable = [
		'lvr_b_id',
        'lvr_p_id',
		'lvr_emp_id',
		'lvr_pl_id',
		'lvr_start_date',
		'lvr_end_date',
        'lvr_cat_type_id',
		'lvr_reason',
		'lvr_total_leave_days',
		'lvr_day_segment_id',
		'lvr_documents',
		'lvr_approved_by',
        'lvr_leave_day_type_id',
		'lvr_is_comp_off',
        // for approval column
        'lvr_am_id',
		'lvr_status',
		'lvr_is_sandwich',
        'lvr_module_id',
        'lvr_next_approver',
        'lvr_stage_completed',
	];

    // protected static function booted()
    // {
    //     static::addGlobalScope('not_sandwich', function (Builder $builder) {
    //         $builder->where('lvr_is_sandwich', 0);
    //     });
    // }

	 // fetching data of employe with department,designation,dealer,branch,job status,grade,leave requests

    public function fh_employees_details()
    {
        return $this->belongsTo(Employee::class, 'lvr_emp_id', 'emp_id')
            ->with([
                'fh_department',
                'fh_designation',
                'fh_dealership',
                'leaveBalances' => function ($query) {
                    $query->where('lb_year', now()->year)
                          ->where('lb_month', now()->month);
                }
            ]);
    }

    public function fh_approver(){
    	return $this->belongsTo(Employee::class, 'lvr_approved_by', 'emp_id');
    }

	  public function fh_business()
    {
        return $this->belongsTo(Business::class, 'lvr_b_id');
    }

	public function fh_employee()
	{
		return $this->belongsTo(Employee::class, 'lvr_emp_id');
	}

	public function fh_policy_leave()
	{
		return $this->belongsTo(PolicyLeave::class, 'lvr_pl_id');
	}

    public function fh_leave_day_type()
	{
		return $this->belongsTo(MasterTable::class, 'lvr_leave_day_type_id')->where('m_group', 'LEAVE_TYPE');
	}

    public function fh_leave_day_segment()
	{
		return $this->belongsTo(MasterTable::class, 'lvr_day_segment_id')->where('m_group', 'LEAVE_DAY_SEGMENT');
	}

    public function fh_leave_cat_type()
	{
		return $this->belongsTo(MasterTable::class, 'lvr_cat_type_id');//->where('m_group', 'LEAVE_CATEGORY');
	}
	public function fh_attendance_policy()
	{
		return $this->belongsTo(PolicyAttendance::class, 'lvr_ap_id');
	}

    public function fh_approval_status()
	{
		return $this->belongsTo(MasterTable::class, 'lvr_status')->where('m_group','APPROVAL_STATUS');
	}

    public function fh_approval_log()
	{
       return $this->hasMany(ApprovalLog::class,'log_request_id','lvr_id');
	}

    public function fh_approval_log2(){
        return $this->hasMany(ApprovalLog::class, 'log_request_id', 'lvr_id')->where('log_module_id', 250);
    }

    public function fh_plan_approval_log()
	{
		return $this->hasMany(ApprovalLog::class, 'log_am_id', 'lvr_am_id')->where('log_request_id', $this->lvr_id)->orderBy('updated_at','desc');
	}

    public function fh_process_approvers()
	{
		return $this->hasMany(ProcessApprover::class, 'pa_am_id' , 'lvr_am_id');
	}


    public function filteredProcessApprovers($emp_d_id)
    {
        return $this->hasMany(ProcessApprover::class, 'pa_am_id', 'lvr_am_id')
            ->where(function ($query) use ($emp_d_id) {
                $query->where('pa_flow', 'business')
                    ->orWhere(function ($query) use ($emp_d_id) {
                        $query->where('pa_flow', 'department')
                            ->where('pa_d_id', $emp_d_id);
                    });
            });
    }

    public function fh_module()
    {
        return $this->belongsTo(MasterTable::class, 'lvr_module_id', 'm_id')->where('m_group', 'MODULE');
    }

    public function scopeApprovableBy($query, $user)
    {
        $userId = (int) $user->emp_id;
        $businessId = (int) $user->emp_b_id;

        $leaveModuleId = 250;

        $query->where(function($q) use ($userId, $businessId, $leaveModuleId) {
            // 1) Hierarchy-wise approver: an applicable process_approver row must exist
            $q->orWhereExists(function($sub) use ($userId, $businessId, $leaveModuleId) {
                $sub->select(DB::raw(1))
                    ->from('approval_modules as am')
                    ->join('process_approver as pa', 'am.am_id', '=', 'pa.pa_am_id')
                    ->where('am.am_module_id', $leaveModuleId)
                    ->where('pa.pa_b_id', $businessId)
                    ->where('pa.pa_emp_id', $userId)
                    ->whereNull('am.deleted_at')
                    ->limit(1);
            });

            // 2) Employee-wise approver: check mapping & status table
            $q->orWhereExists(function($sub) use ($userId, $businessId, $leaveModuleId) {
                $sub->select(DB::raw(1))
                    ->from('employee_approval_mappings as eam')
                    ->join('employee_approval_status as eas', 'eam.eam_id', '=', 'eas.eas_eam_id')
                    ->whereColumn('eam.eam_emp_id', 'leave_requests.lvr_emp_id')
                    ->where('eam.eam_b_id', $businessId)
                    ->where('eam.eam_module_id', $leaveModuleId)
                    ->where('eas.eas_approvel_id', $userId)
                    ->whereNull('eam.deleted_at')
                    ->limit(1);
            });
        });

        return $query;
    }
}
