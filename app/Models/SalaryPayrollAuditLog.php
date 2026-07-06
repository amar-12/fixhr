<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * class SalaryPayrollAuditLog
 * 
 * @property int $pal_id
 * @property int|null $pal_b_id
 * @property int $pal_pr_id
 * @property string $pal_description
 * @property int $pal_created_by
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property PayrollRecord $fh_payroll_record
 * @property Employee $fh_employee
 * @property Business|null $fh_business
 *
 * @package App\Models
 */
class SalaryPayrollAuditLog extends Model
{
	protected $table = 'payroll_audit_logs';
	protected $primaryKey = 'pal_id';

	protected $casts = [
		'pal_b_id' => 'int',
		'pal_pr_id' => 'int',
		'pal_created_by' => 'int'
	];

	protected $fillable = [
		'pal_b_id',
		'pal_pr_id',
		'pal_description',
		'pal_created_by'
	];

	public function fh_payroll_record()
	{
		return $this->belongsTo(SalaryPayrollRecord::class, 'pal_pr_id');
	}

	public function fh_employee()
	{
		return $this->belongsTo(Employee::class, 'pal_created_by');
	}

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'pal_b_id');
	}
}
