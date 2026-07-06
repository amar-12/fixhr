<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * class DeductionLog
 *
 * @property int $dlog_id
 * @property int|null $dlog_am_id
 * @property int|null $dlog_tc_id
 * @property int|null $dlog_user_id
 * @property int|null $dlog_user_role_id
 * @property int|null $dlog_deduction_amount
 * @property string|null $dlog_remarks
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property TadaClaim|null $fh_tada_claim
 * @property Employee|null $fh_employee
 * @property Role|null $fh_role
 * @property ApprovalModule|null $fh_approval_module
 *
 * @package App\Models
 */
class DeductionLog extends Model
{
	protected $table = 'deduction_logs';
	protected $primaryKey = 'dlog_id';
	public $incrementing = false;

	protected $casts = [
		'dlog_id' => 'int',
		'dlog_am_id' => 'int',
		'dlog_tc_id' => 'int',
		'dlog_user_id' => 'int',
		'dlog_user_role_id' => 'int',
		'dlog_deduction_amount' => 'int',
		'dlog_requester_action'=> 'int',
	];

	protected $fillable = [
        'dlog_log_id',
		'dlog_am_id',
		'dlog_tc_id',
		'dlog_user_id',
		'dlog_user_role_id',
		'dlog_deduction_amount',
		'dlog_remarks',
		'dlog_requester_action',
        'dlog_additional_info',
	];

	public function fh_tada_claim()
	{
		return $this->belongsTo(TadaClaim::class, 'dlog_tc_id');
	}

	public function fh_employee()
	{
		return $this->belongsTo(Employee::class, 'dlog_user_id');
	}

	public function fh_role()
	{
		return $this->belongsTo(Role::class, 'dlog_user_role_id');
	}

	public function fh_approval_module()
	{
		return $this->belongsTo(ApprovalModule::class, 'dlog_am_id');
	}
}
