<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * class PolicyTadaTravelAllowance
 *
 * @property int $ptta_id
 * @property int|null $ptta_b_id
 * @property int|null $ptta_ptc_id
 * @property int|null $ptta_pttm_id
 * @property int|null $ptta_pttt_id
 * @property string|null $ptta_eligibility
 * @property string|null $ptta_other_eligibility
 * @property string|null $ptta_remarks
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property PolicyTadaCategory|null $fh_policy_tada_category
 * @property PolicyTadaTravelMode|null $fh_policy_tada_travel_mode
 * @property PolicyTadaTravelType|null $fh_policy_tada_travel_type
 * @property Business|null $fh_business
 *
 * @package App\Models
 */
class PolicyTadaTravelAllowance extends Model
{
	protected $table = 'policy_tada_travel_allowance';
	protected $primaryKey = 'ptta_id';

	protected $casts = [
		'ptta_b_id' => 'int',
		'ptta_ptc_id' => 'int',
		'ptta_pttm_id' => 'int',
		'ptta_pttt_id' => 'int',
        'ptta_claim_type_id'=> 'int',
		'ptta_eligibility' => 'float',
		'ptta_other_eligibility' => 'float',
		'ptta_pttv_id' => 'int'
	];

	protected $fillable = [
		'ptta_b_id',
		'ptta_ptc_id',
		'ptta_pttm_id',
		'ptta_pttt_id',
		'ptta_pttv_id',
        'PolicyTadaTravelAllowance',
        'ptta_claim_type_id',
		'ptta_eligibility',
		'ptta_other_eligibility',
		'ptta_remarks'
	];

	public function fh_policy_tada_category()
	{
		return $this->belongsTo(PolicyTadaCategory::class, 'ptta_ptc_id');
	}

	public function fh_policy_tada_travel_mode()
	{
		return $this->belongsTo(PolicyTadaTravelMode::class, 'ptta_pttm_id');
	}

	public function fh_policy_tada_travel_type()
	{
		return $this->belongsTo(PolicyTadaTravelType::class, 'ptta_pttt_id');
	}
	public function fh_policy_tada_travel_vehicle()
	{
		return $this->belongsTo(PolicyTadaTravelVehicle::class, 'ptta_pttv_id');
	}

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'ptta_b_id');
	}

    public function fh_claim_type()
	{
		return $this->belongsTo(MasterTable::class, 'ptta_claim_type_id');
	}
}
