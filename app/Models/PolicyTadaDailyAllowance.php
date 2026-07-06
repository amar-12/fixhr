<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PolicyTadaDailyAllowance
 *
 * @property int $ptda_id
 * @property int|null $ptda_b_id
 * @property int|null $ptda_ptc_id
 * @property int|null $ptda_pttt_id
 * @property int|null $ptda_per_day
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Business|null $fh_business
 * @property PolicyTadaCategory|null $fh_policy_tada_category
 * @property PolicyTadaTravelType|null $fh_policy_tada_travel_type
 *
 * @package App\Models
 */
class PolicyTadaDailyAllowance extends Model
{
	protected $table = 'policy_tada_daily_allowance';
	protected $primaryKey = 'ptda_id';

	protected $casts = [
		'ptda_b_id' => 'int',
		'ptda_ptc_id' => 'int',
		'ptda_pttt_id' => 'int',
		'ptda_per_day' => 'int'
	];

	protected $fillable = [
		'ptda_b_id',
		'ptda_ptc_id',
		'ptda_pttt_id',
		'ptda_per_day',
        'ptda_da_amount',
        'ptda_da_amount2',
        'ptda_da_cal_limit',
        'ptda_da_cal_type_id',
        'ptda_distance',
        'ptda_lodging',
        'ptda_half_da'
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'ptda_b_id');
	}

	public function fh_policy_tada_category()
	{
		return $this->belongsTo(PolicyTadaCategory::class, 'ptda_ptc_id');
	}

	public function fh_policy_tada_travel_type()
	{
		return $this->belongsTo(PolicyTadaTravelType::class, 'ptda_pttt_id');
	}

    public function fh_policy_tada_daily_allowance_cal_type(){
        return $this->belongsTo(MasterTable::class, 'ptda_da_cal_type_id');
    }
}
