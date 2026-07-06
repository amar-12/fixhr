<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * Class AttendanceOutDoor
 *
 * @property int $atd_od_id
 * @property int|null $atd_od_b_id
 * @property int $atd_od_emp_id
 * @property Carbon|null $atd_od_date
 * @property Carbon|null $atd_od_check_in_time
 * @property Carbon|null $atd_od_check_out_time
 * @property bool $atd_od_is_late
 * @property float|null $atd_od_late_duration
 * @property bool $atd_od_is_early_exit
 * @property float|null $atd_od_early_exit_duration
 * @property bool $atd_od_is_overtime
 * @property float|null $atd_od_overtime_hours
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class AttendanceOutDoor extends Model
{
    use SoftDeletes;

    protected $table = 'attendance_out_door';

    protected $primaryKey = 'atd_od_id';

    protected $guarded = ['atd_od_total_working_hours'];

    protected $casts = [
        'atd_od_b_id' => 'int',
        'atd_od_emp_id' => 'int',
        'atd_od_date' => 'date',
        'atd_od_pst_id' => 'int',
        'atd_od_work_mode_type_id' => 'int',
        'atd_od_checkin_method_id' => 'int',
        'atd_od_check_in_time' => 'datetime',
        'atd_od_check_out_time' => 'datetime',
        'atd_od_is_late' => 'bool',
        'atd_od_late_duration' => 'float',
        'atd_od_is_early_exit' => 'bool',
        'atd_od_early_exit_duration' => 'float',
        'atd_od_is_overtime' => 'bool',
        'atd_od_overtime_hours' => 'float',
        'atd_od_attendance_status' => 'int',
        'atd_od_approved_by' => 'int',
        'atd_od_stage_completed' => 'int',
        'atd_od_request_status' => 'int',
        'atd_od_next_approver' => 'int',
        'atd_od_module_id' => 'int',
        'atd_od_am_id' => 'int',
    ];

    protected $fillable = [
        'atd_od_b_id',
        'atd_od_emp_id',
        'atd_od_date',
        'atd_od_pst_id',
        'atd_od_device_id',
        'atd_od_work_mode_type_id',
        'atd_od_checkin_method_id',
        'atd_od_check_in_time',
        'atd_od_check_out_time',
        'atd_od_segments',
        'atd_od_is_late',
        'atd_od_late_duration',
        'atd_od_is_early_exit',
        'atd_od_early_exit_duration',
        'atd_od_is_overtime',
        'atd_od_overtime_hours',
        'atd_od_attendance_status',
        'atd_od_punchin_photo',
        'atd_od_punchout_photo',
        'atd_od_punchin_location',
        'atd_od_punchout_location',
        'atd_od_longitude_punchin',
        'atd_od_latitude_punchin',
        'atd_od_longitude_punchout',
        'atd_od_latitude_punchout',
        'atd_od_approved_by',
        'atd_od_remark',
        'atd_od_stage_completed',
        'atd_od_request_status',
        'atd_od_next_approver',
        'atd_od_module_id',
        'atd_od_am_id',
        'atd_od_next_approver_id',
        'atd_od_updated_auth_id',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function fh_employee()
    {
        return $this->belongsTo(Employee::class, 'atd_od_emp_id', 'emp_id');
    }

    public function fh_employees_details()
    {
        return $this->belongsTo(Employee::class, 'atd_od_emp_id', 'emp_id')
            ->with([
                'fh_department',
                'fh_designation',
                'fh_dealership',
                'fh_branch',
                'fh_job_status',
                'fh_grade',
                'fh_shift_type',
                'fh_employee_salary',
                'fh_policy_leave',
                'leave_requests'
            ]);
    }

    public function approved_by()
    {
        return $this->belongsTo(Employee::class, 'atd_od_approved_by', 'emp_id');
    }

    public function next_approver_id()
    {
        return $this->belongsTo(Employee::class, 'atd_od_next_approver_id', 'emp_id');
    }

    public function fh_policy_shift_timing()
    {
        return $this->belongsTo(
            PolicyShiftTiming::class,
            'atd_od_pst_id'
        );
    }

    public function fh_business()
    {
        return $this->belongsTo(
            Business::class,
            'atd_od_b_id'
        );
    }

    public function fh_attendance_status()
    {
        return $this->belongsTo(
            MasterTable::class,
            'atd_od_attendance_status'
        );
    }

    public function fh_attendance_checkin_type()
    {
        return $this->belongsTo(
            MasterTable::class,
            'atd_od_checkin_method_id'
        )->where('m_group', 'CHECKIN_METHOD');
    }

    public function fh_attendance_work_mode()
    {
        return $this->belongsTo(
            MasterTable::class,
            'atd_od_work_mode_type_id'
        )->where('m_group', 'WORK_MODE');
    }

    public function fh_process_approvers()
    {
        return $this->hasMany(
            ProcessApprover::class,
            'pa_am_id',
            'atd_od_am_id'
        );
    }

    public function filteredProcessApprovers($emp_d_id)
    {
        return $this->hasMany(ProcessApprover::class, 'pa_am_id', 'atd_od_am_id')
            ->where(function ($query) use ($emp_d_id) {
                $query->where('pa_flow', 'business')
                    ->orWhere(function ($query) use ($emp_d_id) {
                        $query->where('pa_flow', 'department')
                            ->where('pa_d_id', $emp_d_id);
                    });
            });
    }

    public function fh_approval_status()
    {
        return $this->belongsTo(
            MasterTable::class,
            'atd_od_request_status'
        )->where('m_group', 'APPROVAL_STATUS');
    }

    public function fh_approval_logs()
    {
        return $this->hasMany(
            ApprovalLog::class,
            'log_request_id',
            'atd_od_id'
        )->where('log_module_id', $this->atd_od_module_id);
    }

    public function fh_approval_log2()
    {
        return $this->hasMany(ApprovalLog::class, 'log_request_id', 'atd_od_id')
            ->where('log_module_id', 589);
    }

    public function fh_plan_approval_log()
    {
        return $this->hasMany(
            ApprovalLog::class,
            'log_am_id',
            'atd_od_am_id'
        )
        ->where('log_request_id', $this->atd_od_id)
        ->orderBy('updated_at', 'desc');
    }

    public function scopeApprovableBy($query, $user)
    {
        $userId = (int) $user->emp_id;
        $businessId = (int) $user->emp_b_id;

        $moduleId = 589 ?? null;

        $query->where(function ($q) use ($userId, $businessId, $moduleId) {

            $q->orWhereExists(function ($sub) use ($userId, $businessId, $moduleId) {
                $sub->select(DB::raw(1))
                    ->from('approval_modules as am')
                    ->join('process_approver as pa', 'am.am_id', '=', 'pa.pa_am_id')
                    ->where('am.am_module_id', $moduleId)
                    ->where('pa.pa_b_id', $businessId)
                    ->where('pa.pa_emp_id', $userId)
                    ->whereNull('am.deleted_at')
                    ->limit(1);
            });

            $q->orWhereExists(function ($sub) use ($userId, $businessId, $moduleId) {
                $sub->select(DB::raw(1))
                    ->from('employee_approval_mappings as eam')
                    ->join('employee_approval_status as eas', 'eam.eam_id', '=', 'eas.eas_eam_id')
                    ->whereColumn('eam.eam_emp_id', 'attendance_out_door.atd_od_emp_id')
                    ->where('eam.eam_b_id', $businessId)
                    ->where('eam.eam_module_id', $moduleId)
                    ->where('eas.eas_approvel_id', $userId)
                    ->whereNull('eam.deleted_at')
                    ->limit(1);
            });
        });

        return $query;
    }
}
