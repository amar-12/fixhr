<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftCalendar extends Model
{
	protected $table = 'shift_calendar';
	protected $primaryKey = 'sc_id';

	protected $casts = [
		'sc_b_id' => 'int',
		'sc_pst_id' => 'int',
		'sc_emp_id' => 'int',
		'sc_start_date' => 'date',
		'sc_end_date' => 'date',
	];

	protected $fillable = [
		'sc_b_id',
		'sc_pst_id',
		'sc_emp_id',
		'sc_start_date',
		'sc_end_date',
	];

	public function business()
	{
		return $this->belongsTo(Business::class, 'sc_b_id', 'b_id');
	}

	public function shift()
	{
		return $this->belongsTo(PolicyShiftTiming::class, 'sc_pst_id', 'pst_id');
	}

	public function employee()
	{
		return $this->belongsTo(Employee::class, 'sc_emp_id', 'emp_id');
	}
}
