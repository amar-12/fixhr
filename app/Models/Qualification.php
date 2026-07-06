<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * class Qualification
 *
 * @property int $qua_id
 * @property int|null $qua_b_id
 * @property int|null $qua_stm_id
 * @property string|null $qua_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Stream|null $fh_stream
 *
 * @package App\Models
 */
class Qualification extends Model
{
	protected $table = 'qualifications';
	protected $primaryKey = 'qua_id';

	protected $casts = [
		'qua_b_id' => 'int',
		'qua_stm_id' => 'int'
	];

	protected $fillable = [
		'qua_b_id',
		'qua_stm_id',
		'qua_name'
	];

	public function fh_stream()
	{
		return $this->belongsTo(Stream::class, 'qua_stm_id');
	}
}
