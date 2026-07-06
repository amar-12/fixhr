<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * class AttendanceException
 *
 * @property int $ae_id
 * @property int|null $ae_b_id
 * @property int $ae_ap_id
 * @property int $ae_emp_id
 * @property Carbon $ae_date
 * @property int $ae_type_id
 * @property int|null $ae_approved_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Employee|null $fh_employee
 * @property PolicyAttendance $fh_attendance_policy
 * @property MasterTable|null $fh_master_table
 * @property OtApprovalStatus|null $fh_ot_approval_status
 *
 * @package App\Models
 */
class AttendanceException extends Model
{
    use SoftDeletes;
	protected $table = 'attendance_exceptions';
	protected $primaryKey = 'ae_id';

	protected $casts = [
		'ae_b_id' => 'int',
		'ae_ap_id' => 'int',
		'ae_emp_id' => 'int',
		'ae_date' => 'date',
		'ae_type_id' => 'int',
		'ae_approved_by' => 'int',
        'ae_attendance_status' => 'int',
		'ae_total_working'=>'float'
		// 'ae_reason_id'=>'text'
	];

	protected $fillable = [
		'ae_b_id',
		'ae_ap_id',
		'ae_emp_id',
		'ae_date',
		'ae_type_id',
		'ae_approved_by',
		'ae_in_time',
		'ae_out_time',
		'ae_total_working',
		'ae_reason_id',
        'ae_stage_completed',
        'ae_am_id',
        'ae_next_approver',
        'ae_module_id',
        'ae_status',
        'ae_custom_reason',
        'ae_attendance_status',
	];

    public function employee()
	{
		return $this->belongsTo(Employee::class, 'ae_emp_id');
	}

	public function fh_employee()

	{
		return $this->belongsTo(Employee::class, 'ae_emp_id');
	}
    public function fh_approval_log(){
        return $this->hasMany(ApprovalLog::class, 'log_request_id', 'ae_id')->where('log_module_id', 229);
    }

    public function fh_approval_log2(){
        return $this->hasMany(ApprovalLog::class, 'log_request_id', 'ae_id')->where('log_module_id', 229);
    }

	public function fh_attendance_policy()
	{
		return $this->belongsTo(PolicyAttendance::class, 'ae_ap_id');
	}

	public function fh_mispunch_type()
	{
		return $this->belongsTo(MasterTable::class, 'ae_type_id')->where('m_group', 'ATTENDANCE_EXCEPTION');
	}
	public function fh_mispunch_reason()
	{
		return $this->belongsTo(MasterTable::class, 'ae_reason_id')->where('m_group', 'MISPUNCH_REASON');
	}

    public function fh_plan_approval_log()
	{
		return $this->hasMany(ApprovalLog::class, 'log_am_id', 'ae_am_id')->where('log_request_id', $this->ae_id)->orderBy('updated_at','desc');
	}

    public function fh_approval_status()
	{
		return $this->belongsTo(MasterTable::class, 'ae_status')->where('m_group','APPROVAL_STATUS');
	}

    public function fh_process_approvers()
	{
		return $this->hasMany(ProcessApprover::class, 'pa_am_id' , 'ae_am_id');
	}

    public function filteredProcessApprovers($emp_d_id)
    {
        return $this->hasMany(ProcessApprover::class, 'pa_am_id', 'ae_am_id')
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
        return $this->belongsTo(MasterTable::class, ' ', 'm_id')->where('m_group', 'MODULE');
    }

    public function fh_ot_approval_status()
    {
        return $this->hasOne(OtApprovalStatus::class, 'ot_atd_id', 'ae_id');
    }

    public function scopeApprovableBy($query, $user)
    {
        $userId = (int) $user->emp_id;
        $businessId = (int) $user->emp_b_id;

        $attendanceExceptionModuleId = 229;

        $query->where(function($q) use ($userId, $businessId, $attendanceExceptionModuleId) {
            // 1) Hierarchy-wise approver: an applicable process_approver row must exist
            $q->orWhereExists(function($sub) use ($userId, $businessId, $attendanceExceptionModuleId) {
                $sub->select(DB::raw(1))
                    ->from('approval_modules as am')
                    ->join('process_approver as pa', 'am.am_id', '=', 'pa.pa_am_id')
                    ->where('am.am_module_id', $attendanceExceptionModuleId)
                    ->where('pa.pa_b_id', $businessId)
                    ->where('pa.pa_emp_id', $userId)
                    ->whereNull('am.deleted_at')
                    ->limit(1);
            });

            // 2) Employee-wise approver: check mapping & status table
            $q->orWhereExists(function($sub) use ($userId, $businessId, $attendanceExceptionModuleId) {
                $sub->select(DB::raw(1))
                    ->from('employee_approval_mappings as eam')
                    ->join('employee_approval_status as eas', 'eam.eam_id', '=', 'eas.eas_eam_id')
                    ->whereColumn('eam.eam_emp_id', 'attendance_exceptions.ae_emp_id')
                    ->where('eam.eam_b_id', $businessId)
                    ->where('eam.eam_module_id', $attendanceExceptionModuleId)
                    ->where('eas.eas_approvel_id', $userId)
                    ->whereNull('eam.deleted_at')
                    ->limit(1);
            });
        });

        return $query;
    }

}
