<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AttendanceSession
 *
 * @property int $ads_id
 * @property int|null $ads_atd_id
 * @property int|null $ads_b_id
 * @property int $ads_emp_id
 * @property string|null $ads_device_id
 * @property Carbon|null $ads_date
 * @property Carbon|null $ads_time
 * @property Carbon $created_at
 * @property Carbon|null $updated_at
 *
 * @property Employee $fh_employee
 * @property Business|null $fh_business
 * @property AttendanceRecord|null $fh_attendance_record
 *
 * @package App\Models
 */
class AttendanceSession extends Model
{
	protected $table = 'attendance_sessions';
	protected $primaryKey = 'ads_id';

	protected $casts = [
		'ads_atd_id' => 'int',
		'ads_b_id' => 'int',
		'ads_emp_id' => 'int',
		'ads_date' => 'datetime',
		'ads_time' => 'datetime'
	];

	protected $fillable = [
		'ads_atd_id',
		'ads_b_id',
		'ads_emp_id',
		'ads_device_id',
		'ads_date',
		'ads_time'
	];

	public function fh_employee()
	{
		return $this->belongsTo(Employee::class, 'ads_emp_id');
	}

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'ads_b_id');
	}

	public function fh_attendance_record()
	{
		return $this->belongsTo(AttendanceRecord::class, 'ads_atd_id');
	}
}
