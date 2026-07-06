<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * class TravelPurpose
 *
 * @property int $tp_id
 * @property int|null $tp_b_id
 * @property int|null $tp_d_id
 * @property string $tp_name
 * @property Carbon $updated_at
 * @property Carbon $created_at
 *
 * @property Business|null $fh_business
 * @property Department|null $fh_department
 *
 * @package App\Models
 */
class TravelPurpose extends Model
{
	protected $table = 'travel_purpose';
	protected $primaryKey = 'tp_id';

	protected $casts = [
		'tp_b_id' => 'int',
		'tp_d_id' => 'int'
	];

	protected $fillable = [
		'tp_b_id',
		'tp_d_id',
		'tp_name'
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'tp_b_id');
	}

	public function fh_department()
	{
		return $this->belongsTo(Department::class, 'tp_d_id');
	}
}
