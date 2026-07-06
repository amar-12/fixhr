<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * class PolicyTadaDailyAllowanceLodging
 *
 * @property int $ptdal_id
 * @property int|null $ptdal_b_id
 * @property int|null $ptdal_ptc_id
 * @property int|null $ptdal_ct_type_id
 * @property string|null $ptdal_same_day_remark
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Business|null $fh_business
 * @property MasterTable|null $fh_master_table
 * @property PolicyTadaCategory|null $fh_policy_tada_category
 *
 * @package App\Models
 */
class PolicyTadaDailyAllowanceLodging extends Model 
{
	protected $table = 'policy_tada_daily_allowance_lodging';
	protected $primaryKey = 'ptdal_id';

	protected $casts = [
		'ptdal_b_id' => 'int',
		'ptdal_ptc_id' => 'int',
		'ptdal_ct_type_id' => 'int',
	];

	protected $fillable = [
		'ptdal_b_id',
		'ptdal_ptc_id',
        'ptdal_pttt_id',
		'ptdal_ct_type_id',
		'ptdal_same_day_remark',
        'ptdal_da_per_day_elig' ,
        'ptdal_da_same_day_ret_elig' ,
        'ptdal_lodg_sngl_w_bill_elig' ,
        'ptdal_lodg_sngl_wo_bill_elig' ,
        'ptdal_lodg_dbl_w_bill_elig' ,
        'ptdal_lodg_dbl_wo_bill_elig',
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'ptdal_b_id');
	}

	public function fh_city_type()
	{
		return $this->belongsTo(MasterTable::class, 'ptdal_ct_type_id')->where('m_group','CITY_TYPE');
	}

	public function fh_policy_tada_category()
	{
		return $this->belongsTo(PolicyTadaCategory::class, 'ptdal_ptc_id');
	}

    public function fh_policy_tada_travel_type()
    {
        return $this->belongsTo(PolicyTadaTravelType::class, 'ptdal_pttt_id');
    }
}
