<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * class PolicyTadaMiscellaneous
 *
 * @property int $pm_id
 * @property int|null $pm_b_id
 * @property int|null $pm_ptc_id
 * @property int|null $pm_miscellaneous_id
 * @property int|null $pm_ct_type_id
 * @property int|null $pm_eligibility
 * @property string|null $pm_remarks
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Business|null $fh_business
 * @property PolicyTadaCategory|null $fh_policy_tada_category
 * @property MasterTable|null $fh_master_table
 *
 * @package App\Models
 */
class PolicyTadaMiscellaneous extends Model
{
	protected $table = 'policy_tada_miscellaneous';
	protected $primaryKey = 'pm_id';

	protected $casts = [
		'pm_b_id' => 'int',
		'pm_ptc_id' => 'int',
		'pm_miscellaneous_id' => 'int',
		'pm_ct_type_id' => 'int',
		'pm_eligibility' => 'int'
	];

	protected $fillable = [
		'pm_b_id',
		'pm_ptc_id',
		'pm_miscellaneous_id',
		'pm_ct_type_id',
		'pm_eligibility',
		'pm_remarks'
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'pm_b_id');
	}

	public function fh_policy_tada_category()
	{
		return $this->belongsTo(PolicyTadaCategory::class, 'pm_ptc_id');
	}

	public function fh_city_type()
	{
		return $this->belongsTo(MasterTable::class, 'pm_ct_type_id')->where('m_group','CITY_TYPE');
	}

	public function fh_miscellaneous()
	{
		return $this->belongsTo(Miscellaneous::class, 'pm_miscellaneous_id');
	}
}
