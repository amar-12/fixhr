<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftBatch extends Model
{
	protected $table = 'shift_batches';
	protected $primaryKey = 'sb_id';

	protected $fillable = [
		'sb_b_id',
		'sb_name',
		'sb_code',
		'sb_pst_id',
		'sb_start_date',
		'sb_end_date',
		'sb_created_by',
	];

	protected $casts = [
		'sb_id'         => 'integer',
		'sb_b_id'       => 'integer',
		'sb_pst_id'     => 'integer',
		'sb_created_by' => 'integer',
		'sb_name'       => 'string',
		'sb_code'       => 'string',
		'sb_start_date' => 'date',
		'sb_end_date'   => 'date',
		'created_at'    => 'datetime',
		'updated_at'    => 'datetime',
	];

	/*
	|--------------------------------------------------------------------------
	| Relationships
	|--------------------------------------------------------------------------
	*/
	public function employees()
	{
		return $this->hasMany(ShiftBatchEmployee::class, 'sb_id', 'sb_id');
	}

	public function shift()
	{
		return $this->belongsTo(PolicyShiftTiming::class, 'sb_pst_id', 'pst_id');
	}

	public function creator()
	{
		return $this->belongsTo(Employee::class, 'sb_created_by', 'emp_id');
	}
}
