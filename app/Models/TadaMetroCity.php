<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TadaMetroCity
 *
 * @property int $ctm_id
 * @property int|null $ctm_b_id
 * @property string|null $ctm_ct_address
 * @property float|null $ctm_longitude
 * @property float|null $ctm_latitude
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Business|null $business
 *
 * @package App\Models
 */
class TadaMetroCity extends Model
{
	protected $table = 'tada_metro_cities';
	protected $primaryKey = 'ctm_id';

	protected $casts = [
		'ctm_b_id' => 'int',
		'ctm_longitude' => 'float',
		'ctm_latitude' => 'float'
	];

	protected $fillable = [
		'ctm_b_id',
		'ctm_ct_address',
		'ctm_longitude',
		'ctm_latitude'
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'ctm_b_id');
	}
}
