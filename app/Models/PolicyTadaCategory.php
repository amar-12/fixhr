<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * class PolicyTadaCategory
 *
 * @property int $ptc_id
 * @property int|null $ptc_b_id
 * @property int|null $ptc_d_id
 * @property int|null $ptc_dg_id
 * @property int|null $ptc_grade_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Business|null $fh_business
 * @property MasterTable|null $fh_master_table
 * @property Department|null $fh_department
 * @property Designation|null $fh_designation
 * @property Collection|PolicyTadaDailyAllowanceLodging[] $fh_policy_tada_daily_allowance_lodgings
 * @property Collection|PolicyTadaMiscellaneous[] $fh_policy_tada_miscellaneous
 * @property Collection|PolicyTadaTravelAllowance[] $fh_policy_tada_travel_allowances
 *
 * @package App\Models
 */
class PolicyTadaCategory extends Model
{
	protected $table = 'policy_tada_categories';
	protected $primaryKey = 'ptc_id';

	protected $casts = [
		'ptc_b_id' => 'int',
		'ptc_d_id' => 'int',
		'ptc_pttt_id' => 'string',
		'ptc_name' => 'string',
		'ptc_grade_id' => 'int'
	];

	protected $fillable = [
		'ptc_b_id',
		'ptc_d_id',
		'ptc_dg_id',
		'ptc_grade_id',
		'ptc_name',
		'ptc_pttt_id',
        'ptc_status'
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'ptc_b_id');
	}

	public function fh_grade()
	{
		return $this->belongsTo(Grade::class, 'ptc_grade_id');
	}

	public function fh_travel_type()
	{
		return $this->belongsTo(PolicyTadaTravelType::class, 'ptc_pttt_id');
	}

	public function fh_department()
	{
		return $this->belongsTo(Department::class, 'ptc_d_id');
	}

	public function fh_designation()
	{
		return $this->belongsTo(Designation::class, 'ptc_dg_id');
	}

	public function fh_policy_tada_daily_allowance_lodgings()
	{
		return $this->hasMany(PolicyTadaDailyAllowanceLodging::class, 'ptdal_ptc_id');
	}

	public function fh_policy_tada_miscellaneous()
	{
		return $this->hasMany(PolicyTadaMiscellaneous::class, 'pm_ptc_id');
	}

	public function fh_policy_tada_travel_allowances()
	{
		return $this->hasMany(PolicyTadaTravelAllowance::class, 'ptta_ptc_id');
	}

    public function fh_vehicle_allowance(){
        return $this->hasMany(PolicyTadaTravelVehicle::class, 'pttv_ptc_id');
    }

    public function fh_policy_tada_daily_allowance(){
		return $this->hasMany(PolicyTadaDailyAllowance::class, 'ptda_ptc_id');
	}

    public function getgetFhDesignationsAttributeAttribute()
    {
        $ids = json_decode($this->ptc_dg_id);
        if (empty($ids)) {
            return collect();
        }
        return Designation::whereIn('dg_id', $ids)->get();
    }
}
