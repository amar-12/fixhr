<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * class PolicyTadaTravelType
 *
 * @property int $pttt_id
 * @property int|null $pttt_b_id
 * @property int|null $pttt_status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Business|null $fh_business
 * @property Country|null $fh_country
 * @property Collection|PolicyTadaTravelAllowance[] $fh_policy_tada_travel_allowances
 *
 * @package App\Models
 */
class PolicyTadaTravelType extends Model
{
	protected $table = 'policy_tada_travel_type';
	protected $primaryKey = 'pttt_id';

	protected $casts = [
		'pttt_b_id' => 'int',
		'pttt_status' => 'int',
		'pttt_type_id' => 'int'
	];

	protected $fillable = [
		'pttt_b_id',
		'pttt_status',
		'pttt_type_id',
		'pttt_approval_type_id'
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'pttt_b_id');
	}

	public function fh_travel_type()
	{
		return $this->belongsTo(MasterTable::class, 'pttt_type_id')->where('m_group', 'TRAVEL_TYPE');
	}

    public function fh_approval_type()
	{
		return $this->belongsTo(MasterTable::class, 'pttt_approval_type_id')->where('m_group', 'APPROVAL_TYPE');
	}

	public function fh_policy_tada_travel_allowances()
	{
		return $this->hasMany(PolicyTadaTravelAllowance::class, 'ptta_pttt_id');
	}

}
