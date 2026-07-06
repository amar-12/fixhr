<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * class Role
 *
 * @property int $role_id
 * @property int|null $role_b_id
 * @property string|null $role_name
 * @property string|null $role_description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Business|null $fh_business
 * @property Collection|Admin[] $fh_admins
 * @property Collection|Employee[] $fh_employees
 *
 * @package App\Models
 */
class Role extends Model
{
	protected $table = 'roles';
	protected $primaryKey = 'role_id';

	protected $casts = [
		'role_b_id' => 'int'
	];

	protected $fillable = [
		'role_b_id',
		'role_name',
		'role_description'
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'role_b_id');
	}

	public function fh_admins()
	{
		return $this->hasMany(Admin::class, 'a_role_id');
	}

	public function fh_employees()
	{
		return $this->hasMany(Employee::class, 'emp_role_id');
	}
}
