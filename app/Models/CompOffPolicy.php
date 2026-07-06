<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompOffPolicy extends Model
{
	protected $table = 'compoff_policy';
	protected $primaryKey = 'cop_id';

	protected $casts = [
		'cop_b_id' => 'int',
		'grant_approval_status' => 'int',
		'request_approval_status' => 'int',
		'carry_forward' => 'int',
		'validity' => 'int',
		'cop_effective_date' => 'date',
		'cop_status' => 'boolean',
	];

	protected $fillable = [
		'cop_b_id',
		'co_policy_name',
		'grant_approval_status',
		'request_approval_status',
		'carry_forward',
		'validity',
		'cop_effective_date',
		'cop_status',
	];

	protected static function boot()
	{
		parent::boot();

		static::deleting(function ($policy) {
			$policy->duration_conditions()->delete();
		});
	}

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'cop_b_id');
	}

	public function duration_conditions()
	{
		return $this->hasMany(CompOffDurationCondition::class, 'cop_id', 'cop_id');
	}
}
