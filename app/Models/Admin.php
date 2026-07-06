<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * class Admin
 *
 * @property int $a_id
 * @property string|null $a_name
 * @property string|null $a_email
 * @property string|null $a_password
 * @property string|null $a_otp
 * @property string|null $a_auth_token
 * @property string|null $a_fcm_token
 * @property int|null $a_role_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Role|null $fh_role
 * @property Collection|Business[] $fh_businesses
 *
 * @package App\Models
 */
class Admin extends Authenticatable
{
    use HasApiTokens, Notifiable;

	protected $table = 'admins';
	protected $primaryKey = 'a_id';

	protected $casts = [
		'a_role_id' => 'int'
	];

	protected $hidden = [
		'a_password'
	];

	protected $fillable = [
		'a_name',
		'a_email',
        'a_phone',
		'a_password',
		'a_otp',
		'a_auth_token',
		'a_fcm_token',
		'a_role_id',
        'a_otp_created_at',
        'a_profile_photo'
	];


	public function fh_role()
	{
		return $this->belongsTo(Role::class, 'a_role_id');
	}

}
