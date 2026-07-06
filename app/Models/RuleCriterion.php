<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * class RuleCriterion
 *
 * @property int $rc_id
 * @property int $rc_b_id
 * @property int|null $rc_am_id
 * @property int|null $rc_approval_rule_id
 * @property int|null $rc_rule_condition_id
 * @property int|null $rc_condition_option_id
 * @property string|null $rc_custom_value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property ApprovalModule|null $fh_approval_module
 * @property MasterTable|null $fh_master_table
 *
 * @package App\Models
 */
class RuleCriterion extends Model
{
	protected $table = 'rule_criteria';
	protected $primaryKey = 'rc_id';

	protected $casts = [
		'rc_b_id' => 'int',
		'rc_am_id' => 'int',
		'rc_approval_rule_id' => 'int',
		'rc_rule_condition_id' => 'int',
		'rc_condition_option_id' => 'int'
	];

	protected $fillable = [
		'rc_b_id',
		'rc_am_id',
		'rc_approval_rule_id',
		'rc_rule_condition_id',
		'rc_condition_option_id',
		'rc_custom_value',
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'rc_b_id');
	}

	public function fh_approval_module()
	{
		return $this->belongsTo(ApprovalModule::class, 'rc_am_id');
	}

	public function fh_approval_rule()
	{
		return $this->belongsTo(MasterTable::class, 'rc_approval_rule_id')->where('m_group','APPROVAL_RULE');
	}

	public function fh_rule_condition()
	{
		return $this->belongsTo(MasterTable::class, 'rc_rule_condition_id')->where('m_group','RULE_CONDITION');
	}

	public function fh_condition_option()
	{
		return $this->belongsTo(MasterTable::class, 'rc_condition_option_id')->where('m_group','APPROVAL_STATUS');
	}
}
