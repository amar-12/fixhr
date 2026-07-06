<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AttendanceLog
 *
 * @property int $al_id
 * @property int|null $al_b_id
 * @property int $al_emp_id
 * @property Carbon|null $al_check_in_time
 * @property Carbon|null $al_check_out_time
 * @property float|null $al_total_worked_hours
 * @property Carbon|null $al_date
 * @property bool|null $al_is_late
 * @property float|null $al_late_duration
 * @property bool|null $al_is_early_exit
 * @property float|null $al_early_exit_duration
 * @property bool|null $al_is_absent
 * @property bool|null $al_is_overtime
 * @property float|null $al_overtime_hours
 * @property int|null $al_attendance_status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class AttendanceLog extends Model
{
	protected $table = 'attendance_log';
	protected $primaryKey = 'al_id';

	protected $casts = [
		'al_b_id' => 'int',
		'al_emp_id' => 'int',
		'al_check_in_time' => 'datetime',
		'al_check_out_time' => 'datetime',
		'al_total_worked_hours' => 'float',
		'al_date' => 'datetime',
		'al_is_late' => 'bool',
		'al_late_duration' => 'float',
		'al_is_early_exit' => 'bool',
		'al_early_exit_duration' => 'float',
		'al_is_absent' => 'bool',
		'al_is_overtime' => 'bool',
		'al_overtime_hours' => 'float',
		'al_attendance_status' => 'int',
		'al_module_id' => 'int',
		'al_next_approver' => 'int',
		'al_request_status' => 'int',
		'al_am_id' => 'int',
		'al_stage_completed' => 'int',
	];

	protected $fillable = [
		'al_b_id',
		'al_emp_id',
		'al_atd_id',
		'al_pst_id',
		'al_check_in_time',
		'al_check_out_time',
		'al_total_worked_hours',
		'al_date',
		'al_is_late',
		'al_late_duration',
		'al_is_early_exit',
		'al_early_exit_duration',
		'al_is_absent',
		'al_is_overtime',
		'al_overtime_hours',
		'al_attendance_status',
		'al_code',
		'al_reason',
		'al_updated_by',
		'al_module_id',
		'al_next_approver',
		'al_request_status',
		'al_am_id',
		'al_stage_completed',
	];


    public function fh_business()
    {
        return $this->belongsTo(Business::class, 'al_b_id');
    }

	public function fh_employee_real()
    {
        return $this->belongsTo(Employee::class, 'al_emp_id', 'emp_id');
    }

    public function fh_employee()
    {
        return $this->belongsTo(AttendanceRecord::class, 'al_emp_id', 'atd_emp_id');
    }
    
    public function fh_employee_data()
    {
        return $this->belongsTo(Employee::class, 'al_updated_by', 'emp_id');
    }

    public function fh_attendance()
    {
        return $this->belongsTo(AttendanceRecord::class, 'al_atd_id');
    }

	public function fh_attendance_status() {
		return $this->belongsTo(MasterTable::class, 'al_attendance_status');
	}

	public function fh_approval_status() {
		return $this->belongsTo(MasterTable::class, 'al_request_status');
	}

	public function fh_ot_approval_status()
    {
        return $this->hasOne(OtApprovalStatus::class, 'ot_atd_id', 'al_id');
    }
}
