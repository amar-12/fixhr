<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PolicyTadaTravelVehicle
 *
 * @property int $pttv_id
 * @property int|null $pttv_b_id
 * @property int|null $pttv_pttm_id
 * @property int|null $pttv_vehicle_id
 * @property int|null $pttv_owner_id
 * @property int|null $pttv_class_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property PolicyTadaTravelMode|null $fh_policy_tada_travel_mode
 * @property MasterTable|null $fh_master_table
 * @property Business|null $fh_business
 *
 * @package App\Models
 */
class PolicyTadaTravelVehicle extends Model
{
	protected $table = 'policy_tada_travel_vehicle';
	protected $primaryKey = 'pttv_id';

	protected $casts = [
		'pttv_b_id' => 'int',
		'pttv_pttm_id' => 'int',
		'pttv_vehicle_id' => 'int',
		'pttv_owner_id' => 'int',
		'pttv_class_id' => 'int'
	];

	protected $fillable = [
		'pttv_b_id',
		'pttv_pttm_id',
		'pttv_vehicle_id',
		'pttv_owner_id',
		'pttv_class_id',
        'pttv_is_conveyance',
        'pttv_claim_type_id',
        'pttv_ptc_id',
        'pttv_eligibility',
	];

	public function fh_policy_tada_travel_mode()
	{
		return $this->belongsTo(PolicyTadaTravelMode::class, 'pttv_pttm_id');
	}

	public function fh_travel_class()
	{
		return $this->belongsTo(MasterTable::class, 'pttv_class_id')->where('m_group','TRAVEL_CLASS');
	}

	public function fh_vehicle()
	{
		return $this->belongsTo(MasterTable::class, 'pttv_vehicle_id')->where('m_group','VEHICLE');
	}

	public function fh_vehicle_owner()
	{
		return $this->belongsTo(MasterTable::class, 'pttv_owner_id')->where('m_group','VEHICLE_OWNER');
	}

    public function fh_claim_type()
	{
		return $this->belongsTo(MasterTable::class, 'pttv_claim_type_id')->where('m_group','CLAIM_TYPE');
	}

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'pttv_b_id');
	}

    public function fh_policy_tada_category()
	{
		return $this->belongsTo(PolicyTadaCategory::class, 'pttv_ptc_id');
	}
}
