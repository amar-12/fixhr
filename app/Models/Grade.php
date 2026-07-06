<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * class Grade
 *
 * @property int $g_id
 * @property int|null $g_b_id
 * @property string|null $g_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Business|null $fh_business
 * @property Collection|Employee[] $fh_employees
 * @property Collection|PolicyTadaCategory[] $fh_policy_tada_categories
 *
 * @package App\Models
 */
class Grade extends Model
{
	protected $table = 'grades';
	protected $primaryKey = 'g_id';

	protected $casts = [
		'g_b_id' => 'int'
	];

	protected $fillable = [
		'g_b_id',
		'g_name'
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'g_b_id');
	}

	public function fh_employees()
	{
		return $this->hasMany(Employee::class, 'emp_grade_id');
	}

	public function fh_policy_tada_categories()
	{
		return $this->hasMany(PolicyTadaCategory::class, 'ptc_grade_id');
	}
}
