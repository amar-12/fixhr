<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FhLocation
 *
 * @property int $lc_id
 * @property int|null $lc_emp_id
 * @property string|null $locations
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Employee|null $fh_employee
 *
 * @package App\Models
 */
class TravelLocation extends Model
{
	protected $table = 'travel_locations';
	protected $primaryKey = 'lc_id';

	protected $casts = [
		'lc_emp_id' => 'int',
        'lc_trp_id' => 'int',
        'lc_trd_id' => 'int',
	];

	protected $fillable = [
		'lc_emp_id',
        'lc_trp_id',
        'lc_trd_id',
		'locations',
		'lc_total_distance'
	];

	public function fh_employee()
	{
		return $this->belongsTo(Employee::class, 'lc_emp_id');
	}

    public function fh_tada_request_plan()
	{
		return $this->belongsTo(TadaRequestPlan::class, 'lc_trp_id');
	}

     public function fh_tada_request_details()
	{
		return $this->belongsTo(TadaRequestDetail::class, 'lc_trd_id');
	}
}
