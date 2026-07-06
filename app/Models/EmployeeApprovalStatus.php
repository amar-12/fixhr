<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Models\EmployeeApprovalMapping;
use App\Models\Employee;
use App\Models\MasterTable;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class EmployeeApprovalStatus
 *
 * @property int $eas_id
 * @property int $eas_eam_id
 * @property int|null $eas_approvel_id
 * @property int|null $eas_approvel_status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class EmployeeApprovalStatus extends Model
{
	use SoftDeletes;
	protected $table = 'employee_approval_status';
	protected $primaryKey = 'eas_id';

	protected $casts = [
		'eas_id' => 'int',
		'eas_eam_id' => 'int',
		'eas_approvel_id' => 'int',
		'eas_approvel_status' => 'int'
	];

	protected $fillable = [
		'eas_eam_id',
		'eas_approvel_id',
		'eas_approvel_status'
	];

    public function employee_approval_mapping()
	{
		return $this->belongsTo(EmployeeApprovalMapping::class, 'eas_eam_id', 'eam_id');
	}

	public function manager()
	{
	    return $this->belongsTo(Employee::class, 'eas_approvel_id', 'emp_id');
	}

	public function approvalStatus()
	{
	    return $this->belongsTo(MasterTable::class, 'eas_approvel_status', 'm_id')->where('m_group', 'APPROVAL_STATUS');
	}
}
