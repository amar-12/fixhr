<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * class State
 *
 * @property int $s_id
 * @property int|null $s_c_id
 * @property string|null $s_name
 * @property string|null $s_code
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Country|null $fh_country
 * @property Collection|Business[] $fh_businesses
 * @property Collection|City[] $fh_cities
 * @property Collection|Employee[] $fh_employees
 *
 * @package App\Models
 */
class State extends Model
{
	protected $table = 'states';
	protected $primaryKey = 's_id';

	protected $casts = [
		's_c_id' => 'int'
	];

	protected $fillable = [
		's_c_id',
		's_name',
		's_code',
        's_c_id'
	];

	public function fh_country()
	{
		return $this->belongsTo(Country::class, 's_c_id');
	}

	public function fh_businesses()
	{
		return $this->hasMany(Business::class, 'b_state_id');
	}

	public function fh_cities()
	{
		return $this->hasMany(City::class, 'ct_s_id');
	}

}
