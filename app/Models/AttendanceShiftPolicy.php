<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AttendanceShiftPolicy
 *
 * @property int $asp_id
 * @property int $asp_b_id
 * @property int $asp_shift_type
 * @property string $asp_shift_type_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Business $fh_business
 * @property MasterTable $fh_master_table
 * @property Collection|AttendanceShiftPolicyItem[] $attendance_shift_policy_items
 *
 * @package App\Models
 */
class AttendanceShiftPolicy extends Model
{
	protected $table = 'attendance_shift_policy';
	protected $primaryKey = 'asp_id';

	protected $casts = [
		'asp_b_id' => 'int',
		'asp_shift_type' => 'int'
	];

	protected $fillable = [
		'asp_b_id',
		'asp_shift_type',
		'asp_shift_type_name'
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'asp_b_id');
	}

	public function fh_master_table()
	{
		return $this->belongsTo(MasterTable::class, 'asp_shift_type');
	}

	public function fh_attendance_shift_policy_items()
	{
		return $this->hasMany(AttendanceShiftPolicyItem::class, 'aspi_asp_id');
	}
}
