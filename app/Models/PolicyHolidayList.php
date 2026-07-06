<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PolicyHolidayList
 *
 * @property int $phl_id
 * @property int|null $phl_b_id
 * @property int $phl_ap_id
 * @property int|null $phl_type_id
 * @property string $phl_name
 * @property Carbon $phl_start_date
 * @property Carbon|null $phl_end_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Business|null $fh_business
 * @property MasterTable|null $fh_master_table
 *
 * @package App\Models
 */
class PolicyHolidayList extends Model
{
	protected $table = 'policy_holiday_list';
	protected $primaryKey = 'phl_id';

	protected $casts = [
		'phl_b_id' => 'int',
		'phl_ap_id' => 'int',
		'phl_start_date' => 'datetime',
		'phl_end_date' => 'datetime',
		'phl_day_type_id' => 'int',
		'phl_day_segment_type_id' => 'int',
	];

	protected $fillable = [
		'phl_b_id',
		'phl_ap_id',
		'phl_name',
		'phl_type_id',
		'phl_start_date',
		'phl_end_date',
		'phl_day_type_id',
		'phl_day_segment_type_id',
	];


	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'phl_b_id');
	}

	public function fh_master_table()
	{
		return $this->belongsTo(MasterTable::class, 'phl_type_id')->where('m_group', 'HOLIDAY_TYPE');
	}
}
