<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * class Department
 *
 * @property int $d_id
 * @property int|null $d_b_id
 * @property string|null $d_name
 * @property int $d_status
 * @property Carbon $updated_at
 * @property Carbon $created_at
 *
 * @property Business|null $fh_business
 * @property Collection|Employee[] $fh_employees
 * @property Collection|PolicyTadaCategory[] $fh_policy_tada_categories
 *
 * @package App\Models
 */
class Department extends Model
{
	protected $table = 'departments';
	protected $primaryKey = 'd_id';

	protected $casts = [
		'd_b_id' => 'int',
        'd_ap_id' => 'int',
		// 'd_pst_id' => 'int',
		'd_phl_id' => 'int',
		'd_pl_id' => 'int',
		'd_pwo_id' => 'int',
		'd_status' => 'int'
	];

	protected $fillable = [
		'd_b_id',
        'd_name',
		'd_ap_id',
		'd_pst_id',
		'd_phl_id',
		'd_pl_id',
		'd_pwo_id',
		'd_status'
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'd_b_id');
	}

	public function fh_employees()
	{
		return $this->hasMany(Employee::class, 'emp_d_id');
	}

	public function fh_policy_tada_categories()
	{
		return $this->hasMany(PolicyTadaCategory::class, 'ptc_d_id');
	}

    public function fh_policy_attendance()
	{
		return $this->belongsTo(PolicyAttendance::class, 'd_ap_id');
	}

	public function fh_policy_holiday_list()
	{
		return $this->belongsTo(PolicyHolidayList::class, 'd_phl_id');
	}

	public function fh_policy_leave()
	{
		return $this->belongsTo(PolicyLeave::class, 'd_pl_id');
	}

	public function fh_policy_shift_timing()
	{
		return $this->belongsTo(PolicyShiftTiming::class, 'd_pst_id');
	}

	public function fh_policy_week_off()
	{
		return $this->belongsTo(PolicyWeekOff::class, 'd_pwo_id');
	}

}
