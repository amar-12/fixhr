<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Models\EmployeeApprovalStatus;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class EmployeeApprovalMapping
 *
 * @property int $eam_id
 * @property int $eam_b_id
 * @property int $eam_module_id
 * @property int $eam_emp_id
 * @property int|null $eam_approver_manager_1
 * @property int|null $eam_approver_manager_2
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Business $fh_business
 * @property MasterTable $fh_master_table
 * @property Employee|null $fh_employee
 *
 * @package App\Models
 */
class EmployeeApprovalMapping extends Model
{
	use SoftDeletes;
	protected $table = 'employee_approval_mappings';
	protected $primaryKey = 'eam_id';

	protected $casts = [
		'eam_id' => 'int',
		'eam_b_id' => 'int',
		'eam_module_id' => 'int',
		'eam_emp_id' => 'int',
	];

	protected $fillable = [
		'eam_b_id',
		'eam_module_id',
		'eam_emp_id'
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'eam_b_id');
	}

	public function fh_master_table()
	{
		return $this->belongsTo(MasterTable::class, 'eam_module_id');
	}

    public function fh_employee_approver_manager_1()
	{
		return $this->belongsTo(Employee::class, 'eam_approver_manager_1');
	}

	public function fh_employee_approver_manager_2()
	{
		return $this->belongsTo(Employee::class, 'eam_approver_manager_2');
	}

    public function fh_employee()
	{
		return $this->belongsTo(Employee::class, 'eam_emp_id');
	}

	public function approvalStatuses()
    {
        return $this->hasMany(EmployeeApprovalStatus::class, 'eas_eam_id', 'eam_id');
    }
}
