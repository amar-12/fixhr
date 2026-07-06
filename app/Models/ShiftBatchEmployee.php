<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftBatchEmployee extends Model
{
	protected $table = 'shift_batch_employees';
	protected $primaryKey = 'sbe_id';

	protected $fillable = [
		'sbe_b_id',
		'sbe_sb_id',
		'sbe_emp_id',
	];

	protected $casts = [
		'sbe_id'     => 'integer',
		'sbe_b_id'   => 'integer',
		'sbe_sb_id'  => 'integer',
		'sbe_emp_id' => 'integer',
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
	];

	/*
	|--------------------------------------------------------------------------
	| Relationships
	|--------------------------------------------------------------------------
	*/
	public function batch()
	{
		return $this->belongsTo(ShiftBatch::class, 'sbe_sb_id', 'sb_id');
	}

	public function employee()
	{
		return $this->belongsTo(Employee::class, 'sbe_emp_id', 'emp_id');
	}
}
