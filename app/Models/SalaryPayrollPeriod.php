<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * class SalaryPayrollPeriod
 *
 * @property int $pp_id
 * @property int|null $pp_b_id
 * @property Carbon $pp_start_date
 * @property Carbon $pp_end_date
 * @property bool|null $pp_is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Business|null $fh_business
 * @property Collection|FhPayrollRecord[] $fh_payroll_records
 *
 * @package App\Models
 */
class SalaryPayrollPeriod extends Model
{
	protected $table = 'payroll_periods';
	protected $primaryKey = 'pp_id';

	protected $casts = [
		'pp_b_id' => 'int',
		'pp_start_date' => 'datetime',
		'pp_end_date' => 'datetime',
		'pp_is_active' => 'bool'
	];

	protected $fillable = [
		'pp_b_id',
		'pp_start_date',
		'pp_end_date',
		'pp_is_active'
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'pp_b_id');
	}

	public function fh_payroll_records()
	{
		return $this->hasMany(SalaryPayrollRecord::class, 'pr_pp_id');
	}
}
