<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PolicyShiftTimingLog
 *
 * @property int $pstl_id
 * @property int|null $pstl_b_id
 * @property int $pstl_ap_id
 * @property int|null $pstl_type_id
 * @property string $pstl_name
 * @property Carbon $pstl_start_time
 * @property Carbon $pstl_end_time
 * @property int|null $pstl_break_duration_minutes
 * @property int|null $pstl_is_break_paid
 * @property int|null $pstl_allow_break
 * @property Carbon|null $pstl_break_begin_time
 * @property Carbon|null $pstl_break_end_time
 * @property int|null $pstl_allow_punch_begin_before
 * @property int|null $pstl_mins_punch_begin_before
 * @property int|null $pstl_allow_punch_end_after
 * @property int|null $pstl_mins_punch_end_after
 * @property int|null $pstl_allow_grace_time
 * @property int|null $pstl_grace_time
 * @property int|null $pstl_allow_partial_day
 * @property int|null $pstl_partial_day_type_id
 * @property Carbon|null $pstl_partial_day_begin_time
 * @property Carbon|null $pstl_partial_day_end_time
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
class PolicyShiftTimingLog extends Model
{
	protected $table = 'policy_shift_timings_log';
	protected $primaryKey = 'pstl_id';

	protected $casts = [
		'pstl_b_id' => 'int',
		'pstl_ap_id' => 'int',
		'pstl_type_id' => 'int',
		'pstl_break_duration_minutes' => 'int',
		'pstl_is_break_paid' => 'int',
		'pstl_allow_punch_begin_before' => 'int',
		'pstl_mins_punch_begin_before' => 'int',
		'pstl_allow_punch_end_after' => 'int',
		'pstl_mins_punch_end_after' => 'int',
		'pstl_allow_grace_time' => 'int',
		'pstl_grace_time' => 'int',
		'pstl_allow_partial_day' => 'int',
		'pstl_partial_day_type_id' => 'int',
	];

	protected $fillable = [
		'pstl_b_id',
		'pstl_ap_id',
		'pstl_type_id',
		'pstl_name',
		'pstl_start_time',
		'pstl_end_time',
        'pstl_min_work_hour',
        'pstl_work_hour_penalty_status_id',
		'pstl_break_duration_minutes',
		'pstl_is_break_paid',
		'pstl_allow_break',
		'pstl_break_begin_time',
		'pstl_break_end_time',
		'pstl_allow_punch_begin_before',
		'pstl_mins_punch_begin_before',
		'pstl_allow_punch_end_after',
		'pstl_mins_punch_end_after',
		'pstl_allow_grace_time',
		'pstl_grace_time',
		'pstl_allow_partial_day',
		'pstl_partial_day_type_id',
		'pstl_partial_day_begin_time',
		'pstl_partial_day_end_time'
	];

	public function fh_policy_attendance()
	{
		return $this->belongsTo(PolicyAttendance::class, 'pstl_ap_id');
	}

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'pstl_b_id');
	}

	public function fh_master_table_week()
	{
		return $this->belongsTo(MasterTable::class, 'pstl_partial_day_type_id');
	}

    public function fh_master_table()
	{
		return $this->belongsTo(MasterTable::class, 'pstl_type_id');
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
		return $this->belongsTo(PolicyAttendance::class, 'pstl_ap_id');
	}
}
