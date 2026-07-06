<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * class TravelPurpose
 *
 * @property int $tp_id
 * @property int|null $tp_b_id
 * @property int|null $tp_d_id
 * @property string $tp_name
 * @property Carbon $updated_at
 * @property Carbon $created_at
 *
 * @property Business|null $fh_business
 * @property Department|null $fh_department
 *
 * @package App\Models
 */
class TripDistancesModel extends Model
{
	protected $table = 'trip_distances_calculation';
	protected $primaryKey = 'tdc_id';

	protected $casts = [
		'tdc_trd_id' => 'int',
		'tdc_emp_id' => 'int',
		'tdc_trp_id' => 'int',
		'tdc_b_id' => 'int',
		'tdc_avg_distance' => 'double'
	];

	protected $fillable = [
		'tdc_trd_id',
		'tdc_emp_id',
		'tdc_trp_id',
		'tdc_b_id',
		'tdc_emp_name',
		'tdc_vehicle_type',
		'tdc_segment',
		'tdc_directions_response',
		'tdc_routes',
		'tdc_distance_difference_km',
		'tdc_avg_distance',
		'tdc_weighted_avg_cal',
		'tdc_weightage',
		'tdc_route_legs',
		'tdc_punch_type',
		'tdc_route_1_km',
		'tdc_route_2_km',
		'tdc_route_3_km',
		'tdc_route_4_km',
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'tp_b_id');
	}

	public function fh_department()
	{
		return $this->belongsTo(Department::class, 'tp_d_id');
	}
}
