<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * class AttendanceRecord
 *
 * @property int $atd_id
 * @property int|null $atd_b_id
 * @property int $atd_emp_id
 * @property Carbon $atd_date
 * @property int|null $atd_pst_id
 * @property Carbon|null $atd_check_in_time
 * @property Carbon|null $atd_check_out_time
 * @property float|null $atd_total_worked_hours
 * @property bool|null $atd_is_late
 * @property int|null $atd_late_duration
 * @property bool|null $atd_is_absent
 * @property bool|null $atd_is_overtime
 * @property float|null $atd_overtime_hours
 * @property string|null $atd_attendance_status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Employee $fh_employee
 * @property PolicyShiftTiming|null $fh_policy_shift_timing
 * @property Business|null $fh_business
 * @property OtApprovalStatus|null $fh_ot_approval_status
 *
 * @package App\Models
 */
class AttendanceRecord extends Model
{
    protected $table = 'attendance_records';
    protected $primaryKey = 'atd_id';

    protected $guarded = ['atd_total_worked_hours'];

    protected $casts = [
        'atd_b_id' => 'int',
        'atd_emp_id' => 'int',
        'atd_date' => 'datetime',
        'atd_pst_id' => 'int',
        'atd_work_mode_type_id' => 'int',
        'atd_checkin_method_id' => 'int',
        'atd_check_in_time' => 'datetime',
        'atd_check_out_time' => 'datetime',
        'atd_is_late' => 'bool',
        'atd_late_duration' => 'float',
        'atd_is_absent' => 'bool',
        'atd_is_overtime' => 'bool',
        'atd_overtime_hours' => 'float',
        'atd_attendance_status' => 'int',
        'atd_stage_completed' => 'int'
    ];

    protected $fillable = [
        'atd_b_id',
        'atd_emp_id',
        'atd_device_id',
        'atd_date',
        'atd_pst_id',
        'atd_work_mode_type_id',
        'atd_checkin_method_id',
        'atd_check_in_time',
        'atd_check_out_time',
        'atd_segments',
        'atd_is_late',
        'atd_late_duration',
        'atd_is_absent',
        'atd_is_overtime',
        'atd_overtime_hours',
        'atd_attendance_status',
        'atd_punchin_photo',
        'atd_punchout_photo',
        'atd_punchin_location',
        'atd_punchout_location',
        'atd_longitude_punchin',
        'atd_latitude_punchin',
        'atd_longitude_punchout',
        'atd_updated_by',
        'atd_remark',
        'atd_latitude_punchout',
        'atd_stage_completed',
        'atd_is_early_exit',
        'atd_early_exit_duration',
        'atd_request_status',
        'atd_next_approver',
        'atd_module_id',
        'atd_am_id',
        'atd_ar_reason',
        'atd_co_d_id',
        'atd_gtp_id',
    ];



    public function fh_employees_details()
    {
        return $this->belongsTo(Employee::class, 'atd_emp_id', 'emp_id')
            ->with(['fh_department', 'fh_designation', 'fh_dealership', 'fh_branch', 'fh_job_status', 'fh_grade', 'fh_shift_type', 'fh_employee_salary', 'fh_policy_leave', 'leave_requests']);
    }

    public function fh_employee()
    {
        return $this->belongsTo(Employee::class, 'atd_emp_id', 'emp_id');
    }
    public function updated_by()
    {
        return $this->belongsTo(Employee::class, 'atd_updated_by', 'emp_id');
    }

    public function fh_policy_shift_timing()
    {
        return $this->belongsTo(PolicyShiftTiming::class, 'atd_pst_id');
    }

    public function fh_business()
    {
        return $this->belongsTo(Business::class, 'atd_b_id');
    }

    public function fh_attendance_status()
    {
        return $this->belongsTo(MasterTable::class, 'atd_attendance_status');
    }


    public function fh_attendance_checkin_type()
    {
        return $this->belongsTo(MasterTable::class, 'atd_checkin_method_id')->where('m_group', 'CHECKIN_METHOD');
    }
    public function fh_attendance_work_mode()
    {
        return $this->belongsTo(MasterTable::class, 'atd_work_mode_type_id')->where('m_group', 'WORK_MODE');
    }

    // In your AttendanceRecord model
    public function attendance_exceptions()
    {
        return $this->hasMany(AttendanceException::class, 'ae_emp_id', 'atd_emp_id'); // Adjust foreign key and local key accordingly
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class, 'emp_id', 'emp_id'); // Use appropriate keys
    }

    public function fh_process_approvers()
    {
        return $this->hasMany(ProcessApprover::class, 'pa_am_id', 'atd_am_id');
    }


    public function filteredProcessApprovers($emp_d_id)
    {
        return $this->hasMany(ProcessApprover::class, 'pa_am_id', 'atd_am_id')
            ->where(function ($query) use ($emp_d_id) {
                $query->where('pa_flow', 'business')
                    ->orWhere(function ($query) use ($emp_d_id) {
                        $query->where('pa_flow', 'department')
                            ->where('pa_d_id', $emp_d_id);
                    });
            });
    }

    public function fh_plan_approval_log()
    {
        return $this->hasMany(ApprovalLog::class, 'log_am_id', 'atd_am_id')->where('log_request_id', $this->atd_id)->orderBy('updated_at', 'desc');
    }

    public function fh_approval_status()
    {
        return $this->belongsTo(MasterTable::class, 'atd_request_status')->where('m_group', 'APPROVAL_STATUS');
    }

    public function fh_approval_log2()
    {
        return $this->hasMany(ApprovalLog::class, 'log_request_id', 'atd_id')->where('log_module_id', 249);
    }

    public function scopeApprovableBy($query, $user)
    {
        $userId = (int) $user->emp_id;
        $businessId = (int) $user->emp_b_id;

        $attendanceModuleId = 249;

        $query->where(function($q) use ($userId, $businessId, $attendanceModuleId) {
            // 1) Hierarchy-wise approver: an applicable process_approver row must exist
            $q->orWhereExists(function($sub) use ($userId, $businessId, $attendanceModuleId) {
                $sub->select(DB::raw(1))
                    ->from('approval_modules as am')
                    ->join('process_approver as pa', 'am.am_id', '=', 'pa.pa_am_id')
                    ->where('am.am_module_id', $attendanceModuleId)
                    ->where('pa.pa_b_id', $businessId)
                    ->where('pa.pa_emp_id', $userId)
                    ->whereNull('am.deleted_at')
                    ->limit(1);
            });

            // 2) Employee-wise approver: check mapping & status table
            $q->orWhereExists(function($sub) use ($userId, $businessId, $attendanceModuleId) {
                $sub->select(DB::raw(1))
                    ->from('employee_approval_mappings as eam')
                    ->join('employee_approval_status as eas', 'eam.eam_id', '=', 'eas.eas_eam_id')
                    ->whereColumn('eam.eam_emp_id', 'attendance_records.atd_emp_id')
                    ->where('eam.eam_b_id', $businessId)
                    ->where('eam.eam_module_id', $attendanceModuleId)
                    ->where('eas.eas_approvel_id', $userId)
                    ->whereNull('eam.deleted_at')
                    ->limit(1);
            });
        });

        return $query;
    }

    public function fh_ot_approval_status()
    {
        return $this->hasOne(OtApprovalStatus::class, 'ot_atd_id', 'atd_id');
    }
}
