<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AttendanceShiftPolicyItem
 *
 * @property int $aspi_id
 * @property int $aspi_b_id
 * @property int $aspi_asp_id
 * @property string $aspi_shift_name
 * @property Carbon $aspi_shift_start
 * @property Carbon $aspi_shift_end
 * @property int $aspi_break_minute
 * @property int $aspi_break_type
 * @property int|null $aspi_punch_begin_before
 * @property int|null $aspi_punch_end_after
 * @property int|null $aspi_grace_time
 * @property int|null $aspi_partial_day_on
 * @property Carbon|null $aspi_begins_at
 * @property Carbon|null $aspi_end_at
 * @property int $aspi_shift_hour
 * @property int $aspi_shift_minutes
 * @property int $aspi_working_duration
 * @property bool $aspi_is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property AttendanceShiftPolicy $attendance_shift_policy
 * @property Business $business
 * @property MasterTable|null $master_table
 *
 * @package App\Models
 */
class AttendanceShiftPolicyItem extends Model
{
	protected $table = 'attendance_shift_policy_item';
	protected $primaryKey = 'aspi_id';

	protected $casts = [
		'aspi_b_id' => 'int',
		'aspi_asp_id' => 'int',
		// 'aspi_shift_start' => 'datetime',
		// 'aspi_shift_end' => 'datetime',
		'aspi_break_minute' => 'int',
		'aspi_break_type' => 'int',
		// 'aspi_punch_begin_before' => 'int',
		// 'aspi_punch_end_after' => 'int',
		'aspi_grace_time' => 'int',
		'aspi_partial_day_on' => 'int',
		// 'aspi_begins_at' => 'datetime',
		// 'aspi_end_at' => 'datetime',
		'aspi_shift_hour' => 'int',
		'aspi_shift_minutes' => 'int',
		// 'aspi_working_duration' => 'int',
		'aspi_is_active' => 'bool'
	];

	protected $fillable = [
		'aspi_b_id',
		'aspi_asp_id',
		'aspi_shift_name',
		'aspi_shift_start',
		'aspi_shift_end',
		'aspi_break_minute',
		'aspi_break_type',
		'aspi_punch_begin_before',
		'aspi_punch_end_after',
		'aspi_grace_time',
		'aspi_partial_day_on',
		'aspi_begins_at',
		'aspi_end_at',
		'aspi_shift_hour',
		'aspi_shift_minutes',
		'aspi_working_duration',
		'aspi_is_active'
	];

	public function fh_attendance_shift_policy()
	{
		return $this->belongsTo(AttendanceShiftPolicy::class, 'aspi_asp_id');
	}

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'aspi_b_id');
	}

	public function fh_master_table()
	{
		return $this->belongsTo(MasterTable::class, 'aspi_partial_day_on');
	}
}
