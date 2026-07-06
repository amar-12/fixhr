<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Miscellaneous
 *
 * @property int $mis_id
 * @property int|null $mis_b_id
 * @property int|null $mis_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Business|null $fh_business
 *
 * @package App\Models
 */
class Miscellaneous extends Model
{
	protected $table = 'miscellaneous';
	protected $primaryKey = 'mis_id';

	protected $casts = [
		'mis_b_id' => 'int',
	];

	protected $fillable = [
		'mis_b_id',
		'mis_name'
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'mis_b_id');
	}
}
