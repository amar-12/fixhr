<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OvertimePolicy extends Model
{
	protected $table = 'overtime_policy';
	protected $primaryKey = 'ot_id';

	protected $fillable = [
		'ot_b_id',
		'ot_is_enabled',
		'ot_working_day',
		'ot_non_working_day',
		'ot_max_co_per_day',
		'ot_shift_type',
		'ot_break_type',
		'ot_min_work_per_day',
		'ot_min_work_required',
		'ot_max_work_per_day',
		'ot_max_work_per_month',
		'ot_calculation_method',
		'ot_buffer_mins_per_day',
		'ot_max_co_per_day',
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'ot_b_id');
	}
}
