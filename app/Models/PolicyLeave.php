<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PolicyLeave
 *
 * @property int $pl_id
 * @property int|null $pl_b_id
 * @property int $pl_ap_id
 * @property string $pl_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property PolicyAttendance $fh_attendance_policy
 * @property Business|null $fh_business
 *
 * @package App\Models
 */
class PolicyLeave extends Model
{
	protected $table = 'policy_leaves';
	protected $primaryKey = 'pl_id';

	protected $casts = [
		'pl_b_id' => 'int',
		'pl_ap_id' => 'int',
        'pl_name' => 'string',
        'pl_upl_applicable' => 'boolean',
        'pl_limit_check' => 'boolean',
        'pl_limit_before' => 'int',
        'pl_limit_after' => 'int',
	];

	protected $fillable = [
		'pl_b_id',
		'pl_ap_id',
		'pl_name',
        'pl_effective_date',
        'pl_expire_date',
		'pl_upl_applicable',
		'pl_limit_check',
        'pl_limit_before',
        'pl_limit_after',
	];


	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'pl_b_id');
	}

    public function fh_leave_type()
    {
        return $this->hasMany(LeaveType::class, 'lvt_pl_id', 'pl_id');
    }
}
