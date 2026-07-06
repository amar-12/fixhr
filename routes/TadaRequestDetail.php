<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Models\TravelLocation;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class TadaRequestDetail
 *
 * @property int $trd_id
 * @property int $trd_trp_id
 * @property string $trd_name
 * @property Carbon|null $created_at
 * @property int $trd_pttm_id
 * @property int $trd_pttv_id
 * @property string|null $trd_source
 * @property string|null $trd_destination
 * @property Carbon|null $trd_start_date
 * @property int|null $trd_end_date
 * @property string|null $trd_documents
 * @property string|null $trd_latitude
 * @property string|null $trd_longitude
 * @property Carbon|null $trd_start_time
 * @property Carbon|null $trd_end_time
 * @property float|null $trd_total_distance
 * @property float|null $trd_total_tolerance
 * @property string|null $trd_call_id
 * @property int $trd_status
 * @property int|null $trd_remarks
 * @property string|null $trd_purpose
 * @property int|null $trd_ticket_type
 * @property float|null $trd_net_amount
 * @property Carbon|null $updated_at
 *
 * @property TadaRequestPlan $tada_request_plan
 *
 * @package App\Models
 */
class TadaRequestDetail extends Model
{
    use SoftDeletes;
	protected $table = 'tada_request_details';
	protected $primaryKey = 'trd_id';

    protected $casts = [
        'trd_trp_id' => 'int',
        'trd_pttm_id' => 'int',
        'trd_pttv_id' => 'int',
        // 'trd_start_date' => 'datetime',
        // 'trd_end_date' => 'datetime',
        'trd_total_distance' => 'float',
        'trd_total_tolerance' => 'float',
        'trd_status' => 'int',
        'trd_ticket_type' => 'int',
        'trd_net_amount' => 'float',
        'trd_geo_work_active' => 'boolean'
    ];

    protected $fillable = [
        'trd_trp_id',
        'trd_name',
        'trd_pttm_id',
        'trd_pttv_id',
        'trd_source',
        'trd_destination',
        'trd_start_date',
        'trd_end_date',
        'trd_documents',
        'trd_segments',
        'trd_start_time',
        'trd_end_time',
        'trd_total_distance',
        'trd_total_tolerance',
        'trd_call_id',
        'trd_status',
        'trd_remarks',
        'trd_purpose',
        'trd_ticket_type',
        'trd_net_amount',
        'trd_hotel_location',
        'trd_type_id',
        'trd_p_set_amount',
        'trd_geo_work_active'
    ];

    public function fh_policy_tada_travel_allowance()
    {
        return $this->hasOne(PolicyTadaTravelVehicle::class, 'pttv_id', 'trd_pttv_id');
    }

    public function fh_policy_tada_request_plan()
    {
        return $this->belongsTo(TadaRequestPlan::class, 'trd_trp_id');
    }

    public function fh_policy_tada_travel_mode()
    {
        return $this->belongsTo(PolicyTadaTravelMode::class, 'trd_pttm_id');
    }

    public function fh_policy_tada_travel_vehicle()
    {
        return $this->belongsTo(PolicyTadaTravelVehicle::class, 'trd_pttv_id');
    }

    public function fh_details_type(){
        return $this->belongsTo(MasterTable::class, 'trd_type_id')->where('m_group', 'DETAILS_TYPE');
    }

    public function fh_tada_locations()
    {
        return $this->hasMany(TravelLocation::class, 'lc_trd_id');
    }
}
