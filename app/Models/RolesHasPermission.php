<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * class RolesHasPermission
 *
 * @property int $rhp_id
 * @property int $rhp_b_id
 * @property int $rhp_role_id
 * @property string|null $rhp_permissions
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Business $fh_business
 * @property Role $fh_role
 *
 * @package App\Models
 */
class RolesHasPermission extends Model
{
	protected $table = 'roles_has_permissions';
	protected $primaryKey = 'rhp_id';

	protected $casts = [
		'rhp_b_id' => 'int',
		'rhp_role_id' => 'int'
	];

	protected $fillable = [
		'rhp_b_id',
		'rhp_role_id',
		'rhp_permissions'
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'rhp_b_id');
	}

	public function fh_role()
	{
		return $this->belongsTo(Role::class, 'rhp_role_id');
	}
}
