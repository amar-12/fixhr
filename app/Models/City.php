<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * class City
 *
 * @property int $ct_id
 * @property int|null $ct_s_id
 * @property int|null $ct_type_id
 * @property string|null $ct_code
 * @property string|null $ct_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property State|null $fh_state
 * @property MasterTable|null $fh_master_table
 * @property Collection|Business[] $fh_businesses
 * @property Collection|Employee[] $fh_employees
 *
 * @package App\Models
 */
class City extends Model
{
	protected $table = 'cities';
	protected $primaryKey = 'ct_id';

	protected $casts = [
		'ct_s_id' => 'int',
		'ct_type_id' => 'int'
	];

	protected $fillable = [
		'ct_s_id',
		'ct_type_id',
		'ct_code',
		'ct_name'
	];

	public function fh_state()
	{
		return $this->belongsTo(State::class, 'ct_s_id');
	}

	public function fh_city_type()
	{
		return $this->belongsTo(MasterTable::class, 'ct_type_id')->where('m_group','CITY_TYPE');
	}

	public function fh_businesses()
	{
		return $this->hasMany(Business::class, 'b_city_id');
	}
}
