<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * class ProcessApprover
 *
 * @property int $pa_id
 * @property int $pa_b_id
 * @property int|null $pa_am_id
 * @property string|null $pa_type
 * @property int|null $pa_role_id
 * @property int|null $pa_emp_id
 * @property int|null $pa_status_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property ProcessApprover|null $fh_approval_module
 * @property Role|null $fh_role
 * @property Employee|null $fh_employee
 * @property MasterTable|null $fh_master_table
 *
 * @package App\Models
 */
class ProcessApprover extends Model
{
	protected $table = 'process_approver';
	protected $primaryKey = 'pa_id';

	protected $casts = [
		'pa_b_id'=> 'int',
		'pa_am_id' => 'int',
		'pa_role_id' => 'int',
		'pa_emp_id' => 'int',
		'pa_status_id' => 'int',
		'pa_sequence'=> 'int',
        'pa_last'=>'int'
	];

	protected $fillable = [
		'pa_b_id',
		'pa_am_id',
		'pa_type',
		'pa_role_id',
		'pa_emp_id',
		'pa_status_id',
        'pa_last',
		'pa_sequence',
        'pa_message',
        'pa_d_id',
        'pa_flow',
	];

	public function fh_approval_module()
	{
		return $this->belongsTo(ApprovalModule::class, 'pa_am_id');
	}

	public function fh_role()
	{
		return $this->belongsTo(Role::class, 'pa_role_id');
	}

	public function fh_employee()
	{
		return $this->belongsTo(Employee::class, 'pa_emp_id');
	}

	public function fh_approver_status()
	{
        return $this->belongsTo(MasterTable::class, 'pa_status_id')->where('m_group','APPROVAL_STATUS');
	}

    public function fh_department(){
        return $this->belongsTo(Department::class, 'pa_d_id');
    }
}
