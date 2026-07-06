<?php

namespace App\Http\Resources\Approval\Travel;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class TravelRequestDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'trd_id' => $this->trd_id,
            'trd_type_id' => $this->trd_type_id,
            'travel_detail_name' => $this->trd_name ?? '',
            'trd_hotel_location' => $this->trd_hotel_location ?? '',
            'plan_id' => $this->trd_trp_id,
            'mode_id' => $this->trd_pttm_id ?? 0,
            'mode_name' => $this->fh_policy_tada_travel_mode->fh_travel_mode->m_name ?? '',
            'vehicle_detail' =>$this->fh_policy_tada_travel_vehicle ? VehicleResource::collection([$this->fh_policy_tada_travel_vehicle])->all() : null,
            'source' => $this->trd_source ?? '',
            'destination' => $this->trd_destination ?? '',
            'start_date' => $this->trd_start_date,
            'end_date' => $this->trd_end_date,
            'document' => $this->trd_documents ? json_decode($this->trd_documents): [],
            'segments' => json_decode($this->trd_segments) ?? [],
            'start_time' => Carbon::parse($this->trd_start_time)->format('h:i A') ?? '',
            'end_time' => Carbon::parse($this->trd_end_time)->format('h:i A') ?? '',
            'total_distance' => (double) $this->trd_total_distance ?? 0,
            'total_tolerance' => $this->trd_total_tolerance ?? 0,
            'call_id' => $this->trd_call_id ?? '',
            'status' => $this->trd_status ?? 0,
            'remark' => $this->trd_remarks ?? '',
            'purpose' => $this->trd_purpose ?? '',
            'ticket_type' => $this->trd_ticket_type ?? 0,
            'amount' => $this->trd_net_amount ?? 0,
        ];
    }
}
