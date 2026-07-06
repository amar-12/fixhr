<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * class Dealership
 *
 * @property int $d_id
 * @property int|null $d_b_id
 * @property string|null $d_name
 * @property int $d_status
 * @property Carbon $updated_at
 * @property Carbon $created_at
 *
 * @property Business|null $fh_business
 * @property Collection|Employee[] $fh_employees
 * @property Collection|PolicyTadaCategory[] $fh_policy_tada_categories
 *
 * @package App\Models
 */
class Dealership extends Model
{
	protected $table = 'dealerships';
	protected $primaryKey = 'dlr_id';

	protected $casts = [
		'dlr_b_id' => 'int',
        'dlr_name' => 'string',
        'dlr_code' => 'string',
	];

	protected $fillable = [
        'dlr_b_id',
		'dlr_name',
		'dlr_code',
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'b_id');
	}

	public function fh_employees()
	{
		return $this->hasMany(Employee::class, 'emp_dlr_id');
	}

}
