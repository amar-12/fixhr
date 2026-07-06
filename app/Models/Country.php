<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * class Country
 *
 * @property int $c_id
 * @property string|null $c_name
 * @property string|null $c_code
 * @property string|null $c_currency_code
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Collection|Business[] $fh_businesses
 * @property Collection|Employee[] $fh_employees
 * @property Collection|PolicyTadaTravelType[] $fh_policy_tada_travel_types
 * @property Collection|State[] $fh_states
 *
 * @package App\Models
 */
class Country extends Model
{
	protected $table = 'countries';
	protected $primaryKey = 'c_id';

	protected $fillable = [
		'c_name',
		'c_code',
		'c_currency_code'
	];

	public function fh_businesses()
	{
		return $this->hasMany(Business::class, 'b_country_id');
	}

	public function fh_policy_tada_travel_types()
	{
		return $this->hasMany(PolicyTadaTravelType::class, 'pttt_c_id');
	}

	public function fh_states()
	{
		return $this->hasMany(State::class, 's_c_id');
	}

    public function fh_timezones()
    {
        return $this->hasMany(Timezone::class, 'tz_country_id', 'c_id');
    }
}
