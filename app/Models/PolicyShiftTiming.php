<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PolicyShiftTiming
 *
 * @property int $pst_id
 * @property int|null $pst_b_id
 * @property int $pst_ap_id
 * @property int|null $pst_type_id
 * @property string $pst_name
 * @property Carbon $pst_start_time
 * @property Carbon $pst_end_time
 * @property int|null $pst_break_duration_minutes
 * @property int|null $pst_is_break_paid
 * @property int|null $pst_allow_break
 * @property Carbon|null $pst_break_begin_time
 * @property Carbon|null $pst_break_end_time
 * @property int|null $pst_allow_punch_begin_before
 * @property int|null $pst_mins_punch_begin_before
 * @property int|null $pst_allow_punch_end_after
 * @property int|null $pst_mins_punch_end_after
 * @property int|null $pst_allow_grace_time
 * @property int|null $pst_grace_time
 * @property int|null $pst_allow_partial_day
 * @property int|null $pst_partial_day_type_id
 * @property Carbon|null $pst_partial_day_begin_time
 * @property Carbon|null $pst_partial_day_end_time
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property PolicyAttendance $fh_policy_attendance
 * @property Business|null $fh_business
 * @property MasterTable|null $fh_master_table
 * @property Collection|AttendanceRecord[] $fh_attendance_records
 * @property Collection|Employee[] $fh_employees
 *
 * @package App\Models
 */
class PolicyShiftTiming extends Model
{
	protected $table = 'policy_shift_timings';
	protected $primaryKey = 'pst_id';

	protected $casts = [
		'pst_b_id' => 'int',
		'pst_ap_id' => 'int',
		'pst_type_id' => 'int',
		'pst_start_time' => 'datetime',
		'pst_end_time' => 'datetime',
		'pst_break_duration_minutes' => 'int',
		'pst_is_break_paid' => 'int',
		'pst_allow_break1' => 'int',
		'pst_break_begin_time1' => 'datetime',
		'pst_break_end_time1' => 'datetime',
		'pst_allow_punch_begin_before' => 'int',
		'pst_mins_punch_begin_before' => 'int',
		'pst_allow_punch_end_after' => 'int',
		'pst_mins_punch_end_after' => 'int',
		'pst_allow_grace_time' => 'int',
		'pst_grace_time' => 'int',
		'pst_allow_partial_day' => 'int',
		'pst_partial_day_type_id' => 'int',
		'pst_partial_day_begin_time' => 'datetime',
		'pst_partial_day_end_time' => 'datetime'
	];

	protected $fillable = [
		'pst_b_id',
		'pst_ap_id',
		'pst_type_id',
		'pst_name',
		'pst_code',
		'pst_start_time',
		'pst_end_time',
		'pst_allow_grace_time',
		'pst_grace_time',
		'pst_shift_duration',
		'pst_break_duration_minutes',
        'pst_min_work_hour',
        'pst_work_hour_penalty_status_id',
        'pst_end_next_day',
        'pst_end_by',
        'pst_allow_break1',
        'pst_break_begin_time1',
        'pst_break_end_time1',
        'pst_break1_duration',
		'pst_is_break_paid',
		'pst_allow_break2',
		'pst_break_begin_time2',
		'pst_break_end_time2',
		'pst_allow_punch_begin_before',
		'pst_mins_punch_begin_before',
		'pst_allow_punch_end_after',
		'pst_mins_punch_end_after',
		'pst_allow_partial_day',
		'pst_partial_day_type_id',
		'pst_partial_day_begin_time',
		'pst_partial_day_end_time',
		'week_off',
		'pst_allow_partial_day2',
		'pst_partial_day_type_id2',
		'pst_partial_day_begin_time2',
		'pst_partial_day_end_time2',
		'week_off2',
		'pst_hd_office_report_after',
		'pst_hd_office_report_after_time',
		'pst_session1_end_by',
		'pst_session2_grace_time',
		'pst_hd_office_report_before',
		'pst_hd_office_report_before_time',
		'pst_is_high',
		'pst_is_face_track_yes',
		'pst_auto_assign_shift',
	];

	public function fh_policy_attendance()
	{
		return $this->belongsTo(PolicyAttendance::class, 'pst_ap_id');
	}

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'pst_b_id');
	}

	public function fh_master_table_week()
	{
		return $this->belongsTo(MasterTable::class, 'pst_partial_day_type_id');
	}

	public function fh_master_table_week2()
	{
		return $this->belongsTo(MasterTable::class, 'pst_partial_day_type_id2');
	}

    public function fh_master_table()
	{
		return $this->belongsTo(MasterTable::class, 'pst_type_id');
	}

	public function fh_attendance_records()
	{
		return $this->hasMany(AttendanceRecord::class, 'atd_pst_id');
	}

	public function fh_employees()
	{
		return $this->hasMany(Employee::class, 'emp_shift_type_id');
	}


    public function fh_attendance_policy()
	{
		return $this->belongsTo(PolicyAttendance::class, 'pst_ap_id');
	}
}
