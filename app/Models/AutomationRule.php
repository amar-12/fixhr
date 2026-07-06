<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FhAutomationRule
 *
 * @property int $ar_id
 * @property int|null $ar_b_id
 * @property int $ar_rule_type
 * @property bool $ar_is_enabled
 * @property int|null $ar_occurrences
 * @property int|null $ar_mark_absent
 * @property Carbon|null $ar_mark_half_day_time
 * @property bool $ar_allow_overtime_early
 * @property bool $ar_allow_overtime_late
 * @property Carbon|null $ar_min_overtime
 * @property Carbon|null $ar_max_overtime
 * @property int|null $ar_apply_before_day
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property FhMasterTable $fh_master_table
 *
 * @package App\Models
 */
class AutomationRule extends Model
{
	protected $table = 'automation_rules';
	protected $primaryKey = 'ar_id';

	protected $casts = [
		'ar_b_id' => 'int',
		'ar_rule_type' => 'int',
		'ar_is_enabled' => 'bool',
		'ar_occurrences' => 'int',
		'ar_mark_absent' => 'int',
		'ar_allow_overtime_early' => 'bool',
		'ar_allow_overtime_late' => 'bool',
		'ar_apply_before_day' => 'integer'
	];

	protected $fillable = [
		'ar_b_id',
		'ar_rule_type',
		'ar_is_enabled',
		'ar_occurrences',
		'ar_mark_absent',
        'ar_penalty_amount',
		'ar_mark_half_day_time',
		'ar_allow_overtime_early',
		'ar_allow_overtime_late',
		'ar_min_overtime',
		'ar_max_overtime',
		'ar_apply_before_day',
		'ar_both_time_count',
        'ar_is_mode_enabled',
        'ar_apply_gatepass_checkout'
	];

	public function fh_absent_day_type()
	{
		return $this->belongsTo(MasterTable::class, 'ar_mark_absent');
	}

    public function fh_rule_type()
    {
        return $this->belongsTo(MasterTable::class, 'ar_rule_type');
    }

}
