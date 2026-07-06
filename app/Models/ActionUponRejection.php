<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * class ActionUponRejection
 *
 * @property int $aur_id
 * @property int|null $aur_b_id
 * @property int|null $aur_am_id
 * @property string|null $aur_group_ids
 * @property int|null $aur_status_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Business|null $fh_business
 * @property ApprovalModule|null $fh_approval_module
 *
 * @package App\Models
 */
class ActionUponRejection extends Model
{
	protected $table = 'action_upon_rejection';
	protected $primaryKey = 'aur_id';

	protected $casts = [
		'aur_emp_id' => 'int',
		'aur_b_id' => 'int',
		'aur_am_id' => 'int',
		'aur_status_id' => 'int'
	];

	protected $fillable = [
		'aur_emp_id',
		'aur_b_id',
		'aur_am_id',
		'aur_group_ids',
		'aur_status_id',
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'aur_b_id');
	}

	public function fh_employee()
	{
		return $this->belongsTo(Employee::class, 'aur_emp_id');
	}

	public function fh_approval_module()
	{
		return $this->belongsTo(ApprovalModule::class, 'aur_am_id');
	}

	public function fh_approver_status()
	{
        return $this->belongsTo(MasterTable::class, 'aur_status_id')->where('m_group','APPROVAL_STATUS');
	}
}
