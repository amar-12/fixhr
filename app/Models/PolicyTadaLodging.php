<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PolicyTadaLodging
 * 
 * @property int $ptl_id
 * @property int|null $ptl_b_id
 * @property int|null $ptl_ptc_id
 * @property int|null $ptl_pttt_id
 * @property int|null $ptl_ct_type_id
 * @property float|null $ptl_sngl_w_bill
 * @property float|null $ptl_sngl_wo_bill
 * @property float|null $ptl_dbl_w_bill
 * @property float|null $ptl_dbl_wo_bill
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Business|null $fh_business
 * @property PolicyTadaCategory|null $fh_policy_tada_category
 * @property PolicyTadaTravelType|null $fh_policy_tada_travel_type
 * @property MasterTable|null $fh_master_table
 *
 * @package App\Models
 */
class PolicyTadaLodging extends Model
{
	protected $table = 'policy_tada_lodging';
	protected $primaryKey = 'ptl_id';

	protected $casts = [
		'ptl_b_id' => 'int',
		'ptl_ptc_id' => 'int',
		'ptl_pttt_id' => 'int',
		'ptl_ct_type_id' => 'int',
		'ptl_sngl_w_bill' => 'float',
		'ptl_sngl_wo_bill' => 'float',
		'ptl_dbl_w_bill' => 'float',
		'ptl_dbl_wo_bill' => 'float'
	];

	protected $fillable = [
		'ptl_b_id',
		'ptl_ptc_id',
		'ptl_pttt_id',
		'ptl_ct_type_id',
		'ptl_sngl_w_bill',
		'ptl_sngl_wo_bill',
		'ptl_dbl_w_bill',
		'ptl_dbl_wo_bill'
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'ptl_b_id');
	}

	public function fh_policy_tada_category()
	{
		return $this->belongsTo(PolicyTadaCategory::class, 'ptl_ptc_id');
	}

	public function fh_policy_tada_travel_type()
	{
		return $this->belongsTo(PolicyTadaTravelType::class, 'ptl_pttt_id');
	}

	public function fh_city_type()
	{
        return $this->belongsTo(MasterTable::class, 'ptl_ct_type_id')->where('m_group','CITY_TYPE');
	}
}
