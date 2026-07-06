<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OvertimeRecord
 *
 * @property int $otr_id
 * @property int|null $otr_b_id
 * @property int $otr_emp_id
 * @property int $otr_attendance_id
 * @property Carbon $otr_date
 * @property float $otr_hours
 * @property float $otr_amount
 * @property int|null $otr_approved_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Employee|null $fh_employee
 * @property AttendanceRecord $fh_attendance_record
 * @property Business|null $fh_business
 *
 * @package App\Models
 */
class OvertimeRecord extends Model
{
	protected $table = 'overtime_records';
	protected $primaryKey = 'otr_id';

	protected $casts = [
		'otr_b_id' => 'int',
		'otr_emp_id' => 'int',
		'otr_attendance_id' => 'int',
		'otr_date' => 'datetime',
		'otr_hours' => 'float',
		'otr_amount' => 'float',
		'otr_approved_by' => 'int'
	];

	protected $fillable = [
		'otr_b_id',
		'otr_emp_id',
		'otr_attendance_id',
		'otr_date',
		'otr_hours',
		'otr_amount',
		'otr_approved_by'
	];

	public function fh_employee()
	{
		return $this->belongsTo(Employee::class, 'otr_approved_by');
	}

	public function fh_attendance_record()
	{
		return $this->belongsTo(AttendanceRecord::class, 'otr_attendance_id');
	}

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'otr_b_id');
	}
}
