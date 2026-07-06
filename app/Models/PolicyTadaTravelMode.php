<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * class PolicyTadaTravelMode
 *
 * @property int $pttm_id
 * @property int $pttm_b_id
 * @property int|null $pttm_by_mode_id
 * @property int|null $pttm_by_class_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Business $fh_business
 * @property MasterTable|null $fh_master_table
 * @property Collection|PolicyTadaTravelAllowance[] $fh_policy_tada_travel_allowances
 *
 * @package App\Models
 */
class PolicyTadaTravelMode extends Model
{
	protected $table = 'policy_tada_travel_mode';
	protected $primaryKey = 'pttm_id';

	protected $casts = [
		'pttm_b_id' => 'int',
		'pttm_by_mode_id' => 'int',
		'pttm_pttt_id' => 'int'
	];

	protected $fillable = [
		'pttm_b_id',
		'pttm_by_mode_id',
		'pttm_pttt_id',
        'pttm_status'
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'pttm_b_id');
	}

	public function fh_travel_mode()
	{
		return $this->belongsTo(MasterTable::class, 'pttm_by_mode_id')->where('m_group','TRAVEL_MODE');
	}

	public function fh_travel_type()
	{
		return $this->belongsTo(PolicyTadaTravelType::class, 'pttm_pttt_id');
	}

	public function fh_policy_tada_travel_allowances()
	{
		return $this->hasMany(PolicyTadaTravelAllowance::class, 'ptta_pttm_id');
	}
}
