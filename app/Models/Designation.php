<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * class Designation
 *
 * @property int $dg_id
 * @property int|null $dg_b_id
 * @property string|null $dg_name
 * @property Carbon $updated_at
 * @property Carbon $created_at
 *
 * @property Business|null $fh_business
 * @property Collection|Employee[] $fh_employees
 * @property Collection|PolicyTadaCategory[] $fh_policy_tada_categories
 *
 * @package App\Models
 */
class Designation extends Model
{
	protected $table = 'designations';
	protected $primaryKey = 'dg_id';

	protected $casts = [
		'dg_b_id' => 'int'
	];

	protected $fillable = [
		'dg_b_id',
		'dg_name'
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'dg_b_id');
	}

	public function fh_employees()
	{
		return $this->hasMany(Employee::class, 'emp_dg_id');
	}

	public function fh_policy_tada_categories()
	{
		return $this->hasMany(PolicyTadaCategory::class, 'ptc_dg_id');
	}
}
