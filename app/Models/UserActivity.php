<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * class UserActivity
 *
 * @property int $ua_id
 * @property int|null $ua_b_id
 * @property int|null $ua_a_id
 * @property int|null $ua_emp_id
 * @property string|null $ua_ip_address
 * @property string|null $ua_activity
 * @property Carbon|null $ua_login_at
 * @property Carbon|null $ua_logout_at
 *
 * @property Admin|null $fh_admin
 * @property Employee|null $fh_employee
 * @property Business|null $fh_business
 *
 * @package App\Models
 */
class UserActivity extends Model
{
	protected $table = 'user_activities';
	protected $primaryKey = 'ua_id';
	public $timestamps = false;

	protected $casts = [
		'ua_b_id' => 'int',
		'ua_emp_id' => 'int',
		'ua_login_at' => 'datetime',
		'ua_logout_at' => 'datetime'
	];

	protected $fillable = [
		'ua_b_id',
		'ua_emp_id',
		'ua_ip_address',
		'ua_activity',
		'ua_login_at',
		'ua_logout_at'
	];

	public function fh_employee()
	{
		return $this->belongsTo(Employee::class, 'ua_emp_id');
	}

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'ua_b_id');
	}
}
