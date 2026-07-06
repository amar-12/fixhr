<?php

namespace App\Http\Resources;

use App\Http\Resources\Policy\TravelModeResource;
use App\Http\Resources\Policy\TravelVehicleResource;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TadaExpenseWiseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    public function toArray(Request $request): array
    {
        $type_id = MasterTableResource::collection([$this->fh_expense_type])->all();
        $m_name = $type_id[0]['m_name'] ?? '';

        return [
            'expense_id' => $this->te_id,
            'plan_id' => $this->te_trp_id,
            'type_id' => $type_id,
            'te_name' => $this->te_name ?? '',
            'te_mode_id' => $this->fh_policy_tada_travel_mode
                ? TravelModeResource::collection([$this->fh_policy_tada_travel_mode])->all()
                : null,
            'te_vehicle_id' => $this->fh_policy_tada_travel_vehicle
                ? TravelVehicleResource::collection([$this->fh_policy_tada_travel_vehicle])->all()
                : null,
            'te_sub_expense_id' => $this->fh_sub_expense
                ? TadaExpenseSettingResource::collection([$this->fh_sub_expense])->all()
                : null,
            'from_location' => $this->te_from_location ?? '',
            'to_location' => $this->te_to_location ?? '',
            'from_date' => $this->te_from_date,
            'round_trip' => (string)($this->te_round_trip ?? '0'),
            'country_code' => $this->te_country_code,
            'foreign_amount' => (string)($this->te_foreign_amount ?? '0'),
            'conversion_rate' => (string)($this->te_conversion_rate ?? '0'),
            'standard_checkout_time' => $this->te_standard_checkout_time
                ? DateTime::createFromFormat('H:i:s', $this->te_standard_checkout_time)->format('g:i A')
                : null,
            'to_date' => $this->te_to_date,
            'date' => $this->te_date,
            'total_km' => (string)($this->te_total_km_driven ?? '0'),
            'hotel_name' => $this->te_hotel_name ?? '',
            'from_time' => $this->te_from_time
                ? DateTime::createFromFormat('H:i:s', $this->te_from_time)->format('g:i A')
                : null,
            'to_time' => $this->te_to_time
                ? DateTime::createFromFormat('H:i:s', $this->te_to_time)->format('g:i A')
                : null,
            'amount' => (string)($this->te_amount ?? '0'),
            'document' => $this->te_document ? json_decode($this->te_document) : [],
            'taxes' => (string)($this->te_taxes ?? '0'),
            'occupancy' => $this->te_occupancy ?? '',
            'paid_by' => $this->te_paid_by ?? '',
            'remarks' => $this->te_remarks ?? '',
            'toleranceKm' => (string)$this->te_tolerance_km ?? ''
        ];
    }
}
