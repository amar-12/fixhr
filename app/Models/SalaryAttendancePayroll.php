<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * class SalaryAttendancePayroll
 *
 * @property int $atdp_id
 * @property int|null $atdp_b_id
 * @property int $atdp_emp_id
 * @property Carbon $atdp_date
 * @property float|null $atdp_hours_worked
 * @property float|null $atdp_overtime_hours
 * @property string|null $status
 * @property int|null $atdp_pr_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Employee $fh_employee
 * @property PayrollRecord|null $fh_payroll_record
 * @property Business|null $fh_business
 *
 * @package App\Models
 */
class SalaryAttendancePayroll extends Model
{
	protected $table = 'attendance_payroll';
	protected $primaryKey = 'atdp_id';

	protected $casts = [
		'atdp_b_id' => 'int',
		'atdp_emp_id' => 'int',
		'atdp_date' => 'datetime',
		'atdp_hours_worked' => 'float',
		'atdp_overtime_hours' => 'float',
		'atdp_pr_id' => 'int'
	];

	protected $fillable = [
		'atdp_b_id',
		'atdp_emp_id',
		'atdp_date',
		'atdp_hours_worked',
		'atdp_overtime_hours',
		'status',
		'atdp_pr_id'
	];

	public function fh_employee()
	{
		return $this->belongsTo(Employee::class, 'atdp_emp_id');
	}

	public function fh_payroll_record()
	{
		return $this->belongsTo(SalaryPayrollRecord::class, 'atdp_pr_id');
	}

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'atdp_b_id');
	}
}
