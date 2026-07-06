<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * class Stream
 *
 * @property int $stm_id
 * @property int|null $stm_b_id
 * @property string|null $stm_name
 * @property Carbon $created_at
 * @property Carbon|null $updated_at
 *
 * @property Collection|EmployeeQualification[] $fh_employee_qualifications
 *
 * @package App\Models
 */
class Stream extends Model
{
	protected $table = 'streams';
	protected $primaryKey = 'stm_id';

	protected $casts = [
		'stm_b_id' => 'int'
	];

	protected $fillable = [
		'stm_b_id',
		'stm_name'
	];

	public function fh_employee_qualifications()
	{
		return $this->hasMany(EmployeeQualification::class, 'eq_stream_id');
	}
}
