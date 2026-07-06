<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FhDepart
 *
 * @property int $dept_id
 * @property string|null $dept_name
 * @property string|null $dept_code
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Collection|FhDesig[] $fh_desigs
 *
 * @package App\Models
 */
class Depart extends Model
{
	protected $table = 'departs';
	protected $primaryKey = 'dept_id';

	protected $fillable = [
		'dept_name',
		'dept_code'
	];

	public function fh_desigs()
	{
		return $this->hasMany(FhDesig::class, 'dept_id');
	}
}
