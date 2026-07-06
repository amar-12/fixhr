<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * class SalaryPolicyLeaveEncashment
 * 
 * @property int $lep_id
 * @property int|null $lep_b_id
 * @property float|null $lep_max_encashment_percentage
 * @property int|null $lep_min_balance_required
 * @property Carbon|null $lep_effective_date
 * @property Carbon|null $lep_expiration_date
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property Business|null $fh_business
 *
 * @package App\Models
 */
class SalaryPolicyLeaveEncashment extends Model
{
	protected $table = 'policy_leave_encashment';
	protected $primaryKey = 'lep_id';

	protected $casts = [
		'lep_b_id' => 'int',
		'lep_max_encashment_percentage' => 'float',
		'lep_min_balance_required' => 'int',
		'lep_effective_date' => 'datetime',
		'lep_expiration_date' => 'datetime'
	];

	protected $fillable = [
		'lep_b_id',
		'lep_max_encashment_percentage',
		'lep_min_balance_required',
		'lep_effective_date',
		'lep_expiration_date'
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'lep_b_id');
	}
}
