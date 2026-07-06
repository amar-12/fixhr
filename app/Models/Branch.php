<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * class Branch
 *
 * @property int $br_id
 * @property int|null $br_b_id
 * @property string|null $br_name
 * @property string|null $br_email
 * @property bool|null $br_is_active
 * @property string|null $br_address
 * @property string|null $br_longitude
 * @property string|null $br_latitude
 * @property string|null $br_range_limit
 * @property Carbon $updated_at
 * @property Carbon $created_at
 *
 * @property Business|null $fh_business
 * @property Collection|Employee[] $fh_employees
 *
 * @package App\Models
 */
class Branch extends Model
{
	protected $table = 'branches';
	protected $primaryKey = 'br_id';

	protected $casts = [
		'br_b_id' => 'int',
		'br_is_active' => 'bool',
		'br_is_wifi_restricted' => 'bool',
	];

	protected $fillable = [
		'br_b_id',
		'br_code',
		'br_name',
		'br_email',
		'br_is_active',
		'br_address',
		'br_longitude',
		'br_latitude',
		'br_range_limit',
        'br_is_wifi_restricted',
        'br_wifi_address',
        'br_c_id',
        'br_s_id',
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'br_b_id');
	}

	public function fh_employees()
	{
		return $this->hasMany(Employee::class, 'emp_br_id');
	}

    public function fh_country()
	{
		return $this->belongsTo(Country::class, 'br_c_id');
	}

	public function fh_state()
	{
		return $this->belongsTo(State::class, 'br_s_id');
	}

    public function fh_professional_tax_slabs()
	{
		return $this->hasOne(ProfessionalTaxSlab::class, 'pts_br_id', 'br_id');
	}
}
