<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompOffDurationCondition extends Model
{
	protected $table = 'compoff_duration_conditions';
	protected $primaryKey = 'condition_id';

	protected $casts = [
		'condition_b_id' => 'int',
		'cop_id' => 'int',
		'work_duration' => 'decimal:2',
		'co_quantity' => 'decimal:2',
	];

	protected $fillable = [
		'condition_b_id',
		'cop_id',
		'work_duration',
		'operator',
		'co_quantity',
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'condition_b_id');
	}

	public function fh_comp_off_policy()
	{
		return $this->belongsTo(CompOffPolicy::class, 'cop_id');
	}
}
