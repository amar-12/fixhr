<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FhAttendanceSummary
 *
 * @property int $as_id
 * @property int|null $as_b_id
 * @property int $as_emp_id
 * @property string|null $as_year_month
 * @property int|null $as_total_days
 * @property float|null $as_total_present
 * @property int|null $as_total_missed_punch
 * @property float|null $as_total_half_day
 * @property float|null $as_total_absent
 * @property float|null $as_total_leave
 * @property float|null $as_total_weekoff
 * @property int|null $as_total_holiday
 * @property float|null $as_total_worked_days
 * @property int|null $as_days_late
 * @property float|null $as_total_overtime_hours
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Employee $fh_employee
 * @property Business|null $fh_business
 *
 * @package App\Models
 */
class AttendanceSummary extends Model
{
	protected $table = 'attendance_summaries';
	protected $primaryKey = 'as_id';
	public $incrementing = true; // Ensure auto-increment



	protected $fillable = [
		'as_b_id',
		'as_emp_id',
		'as_br_id',
		'as_d_id',
		'as_pp_id',
		'as_year_month',
		'as_total_days',
		'as_total_present',
		'as_total_missed_punch',
		'as_total_half_day',
		'as_total_absent',
		'as_total_leave',
		'as_total_weekoff',
        'as_total_weekoffPresent',
		'as_total_holiday',
		'as_total_worked_days',
		'as_days_late',
        'as_early_exit',
		'as_total_upl_count',
		'as_total_overtime_hours',
		'as_is_sal_processed',
		'as_week_id',
        'as_is_frozen',
        'as_frozen_at',
        'as_frozen_by'
	];

	public function fh_employee()
	{
		return $this->belongsTo(Employee::class, 'as_emp_id');
	}

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'as_b_id');
	}

    public function payrollPeriod()
    {
        return $this->belongsTo(PayrollPeriod::class, 'as_pp_id', 'pp_id');
    }
}
