<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * class SalaryPayrollRecord
 *
 * @property int $pr_id
 * @property int|null $pr_b_id
 * @property int $pr_emp_id
 * @property int $pr_pp_id
 * @property float|null $pr_base_salary
 * @property float|null $pr_total_earnings
 * @property float|null $pr_total_deductions
 * @property float|null $pr_net_salary
 * @property string|null $status
 * @property Carbon|null $pr_processed_at
 * @property string|null $pr_payment_method
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Employee $fh_employee
 * @property PayrollPeriod $fh_payroll_period
 * @property Business|null $fh_business
 * @property Collection|FhAttendancePayroll[] $fh_attendance_payrolls
 * @property Collection|FhDeduction[] $fh_deductions
 * @property Collection|FhEarning[] $fh_earnings
 * @property Collection|FhPayrollAuditLog[] $fh_payroll_audit_logs
 * @property Collection|FhPayslip[] $fh_payslips
 *
 * @package App\Models
 */
class SalaryPayrollRecord extends Model
{
	protected $table = 'payroll_records';
	protected $primaryKey = 'pr_id';

	protected $casts = [
		'pr_b_id' => 'int',
		'pr_emp_id' => 'int',
		'pr_pp_id' => 'int',
		'pr_base_salary' => 'float',
		'pr_total_earnings' => 'float',
		'pr_total_deductions' => 'float',
		'pr_net_salary' => 'float',
		'pr_processed_at' => 'datetime'
	];

	protected $fillable = [
		'pr_b_id',
		'pr_emp_id',
		'pr_pp_id',
        'pr_year_month',
		'pr_base_salary',
		'pr_total_earnings',
		'pr_total_deductions',
		'pr_net_salary',
		'status',
		'pr_processed_at',
		'pr_payment_method'
	];

	public function fh_employee()
	{
		return $this->belongsTo(Employee::class, 'pr_emp_id');
	}

	// public function fh_payroll_period()
	// {
	// 	return $this->belongsTo(SalaryPayrollPeriod::class, 'pr_pp_id');
	// }

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'pr_b_id');
	}

	public function fh_attendance_payrolls()
	{
		return $this->hasMany(SalaryAttendancePayroll::class, 'atdp_pr_id');
	}

	public function fh_deductions()
	{
		return $this->hasMany(SalaryDeduction::class, 'deduct_pr_id');
	}

	public function fh_earnings()
	{
		return $this->hasMany(SalaryEarning::class, 'earn_pr_id');
	}

	public function fh_payroll_audit_logs()
	{
		return $this->hasMany(SalaryPayrollAuditLog::class, 'pal_pr_id');
	}

	public function fh_payslips()
	{
		return $this->hasMany(SalaryPayslip::class, 'p_pr_id');
	}
}
