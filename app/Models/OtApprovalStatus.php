<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OtApprovalStatus extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ot_approval_status';
    protected $primaryKey = 'ot_id';

    protected $fillable = [
        'ot_atd_id',
        'ot_b_id',
        'ot_emp_id',
        'ot_atd_type',
        'ot_date',
        'ot_module_id',
        'ot_next_approver',
        'ot_requested_status',
        'ot_am_id',
        'ot_stage_completed',
        'ot_approved_by',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function fh_business()
    {
        return $this->belongsTo(Business::class, 'ot_b_id');
    }

    public function fh_employee()
    {
        return $this->belongsTo(Employee::class, 'ot_emp_id');
    }

    public function fh_approval_status()
    {
        return $this->belongsTo(MasterTable::class, 'ot_requested_status')->where('m_group', 'APPROVAL_STATUS');
    }

    public function fh_plan_approval_log()
    {
        return $this->hasMany(ApprovalLog::class, 'log_request_id', 'ot_id')->where([['log_request_id', $this->ot_id], ['log_am_id', $this->ot_am_id]])->orderBy('updated_at', 'desc');
    }

    public function fh_process_approvers()
    {
        return $this->hasMany(ProcessApprover::class, 'pa_am_id', 'ot_am_id');
    }

    public function fh_approval_log()
    {
        return $this->hasMany(ApprovalLog::class, 'log_request_id', 'ot_id');
    }

    public function fh_approval_log2()
    {
        return $this->hasMany(ApprovalLog::class, 'log_request_id', 'ot_id')->where('log_module_id', 562);
    }

    public function filteredProcessApprovers($emp_d_id)
    {
        return $this->hasMany(ProcessApprover::class, 'pa_am_id', 'ot_am_id')
            ->where(function ($query) use ($emp_d_id) {
                $query->where('pa_flow', 'business')
                    ->orWhere(function ($query) use ($emp_d_id) {
                        $query->where('pa_flow', 'department')
                            ->where('pa_d_id', $emp_d_id);
                    });
            });
    }

    public function scopeApprovableBy($query, $user)
    {
        $userId = (int) $user->emp_id;
        $businessId = (int) $user->emp_b_id;

        $attendanceModuleId = 562;

        $query->where(function($q) use ($userId, $businessId, $attendanceModuleId) {
            // 1) Hierarchy-wise approver: an applicable process_approver row must exist
            $q->orWhereExists(function($sub) use ($userId, $businessId, $attendanceModuleId) {
                $sub->select(\DB::raw(1))
                    ->from('approval_modules as am')
                    ->join('process_approver as pa', 'am.am_id', '=', 'pa.pa_am_id')
                    ->where('am.am_module_id', $attendanceModuleId)
                    ->where('pa.pa_b_id', $businessId)
                    ->where('pa.pa_emp_id', $userId)
                    ->limit(1);
            });

            // 2) Employee-wise approver: check mapping & status table
            $q->orWhereExists(function($sub) use ($userId, $businessId) {
                $sub->select(\DB::raw(1))
                    ->from('employee_approval_mappings as eam')
                    ->join('employee_approval_status as eas', 'eam.eam_id', '=', 'eas.eas_eam_id')
                    ->whereColumn('eam.eam_emp_id', 'ot_approval_status.ot_emp_id')
                    ->where('eam.eam_b_id', $businessId)
                    ->where('eas.eas_approvel_id', $userId)
                    ->limit(1);
            });
        });

        return $query;
    }
}
