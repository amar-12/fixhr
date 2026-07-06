<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TadaExpense
 *
 * @property int $te_id
 * @property int|null $te_trp_id
 * @property int $te_type_id
 * @property string|null $te_from_location
 * @property string|null $te_to_location
 * @property Carbon|null $te_from_date
 * @property Carbon|null $te_to_date
 * @property Carbon|null $te_date
 * @property float|null $te_total_km_driven
 * @property string|null $te_hotle_name
 * @property Carbon|null $te_from_time
 * @property Carbon|null $te_to_time
 * @property string|null $te_document
 * @property float|null $te_taxes
 * @property string|null $te_occupancy
 * @property string|null $te_paid_by
 * @property string|null $te_remarks
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property TadaRequestPlan|null $fh_tada_request_plan
 * @property MasterTable $fh_master_table
 *
 * @package App\Models
 */
class TadaExpense extends Model
{
    protected $table = 'tada_expenses';
    protected $primaryKey = 'te_id';

    protected $casts = [
        'te_trp_id' => 'int',
        'te_type_id' => 'int',
        'te_sub_expense_id' => 'int',
        'te_name' => 'string',
        'te_total_km_driven' => 'float',
        'te_taxes' => 'float',
        'te_amount' => 'float',
        'te_deviation' => 'float',
        'te_tolerance_km' => 'float',
    ];

    protected $fillable = [
        'te_trp_id',
        'te_type_id',
        'te_sub_expense_id',
        'te_country_code',
        'te_foreign_amount',
        'te_conversion_rate',
        'te_name',
        'te_pttm_id',
        'te_pttv_id',
        'te_from_location',
        'te_to_location',
        'te_from_date',
        'te_to_date',
        'te_round_trip',
        'te_standard_checkout_time',
        'te_date',
        'te_total_km_driven',
        'te_hotel_name',
        'te_from_time',
        'te_to_time',
        'te_document',
        'te_tolerance_km',
        'te_taxes',
        'te_amount',
        'te_deviation',
        'te_occupancy',
        'te_paid_by',
        'te_remarks',
        'te_p_set_amount',
        'calculation_message',
        'te_additional_info'
    ];

    public function fh_tada_request_plan()
    {
        return $this->belongsTo(TadaRequestPlan::class, 'te_trp_id');
    }

    public function fh_expense_type()
    {
        return $this->belongsTo(MasterTable::class, 'te_type_id')->where('m_group', 'EXPENSE_TYPE');
    }

    public function fh_policy_tada_travel_mode()
    {
        return $this->belongsTo(PolicyTadaTravelMode::class, 'te_pttm_id');
    }

    public function fh_policy_tada_travel_vehicle()
    {
        return $this->belongsTo(PolicyTadaTravelVehicle::class, 'te_pttv_id');
    }
    public function fh_sub_expense()
    {
        return $this->belongsTo(TadaExpenseSetting::class, 'te_sub_expense_id');
    }
}
