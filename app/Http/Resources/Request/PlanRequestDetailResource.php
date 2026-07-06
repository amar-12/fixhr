<?php

namespace App\Http\Resources\Request;

use App\Http\Resources\Request\TravelAllowanceEligibilityResource;
use App\Http\Resources\Policy\TravelModeResource;
use App\Http\Resources\Policy\TravelVehicleResource;
use App\Http\Resources\TravelPurposeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use DateTime;

class PlanRequestDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $segments = json_decode($this->trd_segments, true) ?? [];

        $uniqueSegments = collect($segments)->unique(function ($item) {
            return $item['latitude'] . '|' . $item['longitude'] . '|' . $item['location'] . '|' . $item['date'] . '|' . $item['time'] . '|' . $item['distance'];
        })->values()->all();
        return [
            'trd_id' => $this->trd_id,
            'travel_detail_name' => $this->trd_name,
            'plan_id' => $this->trd_trp_id,
            // 'trp_destination'=>$this->fh_policy_tada_request_plan->trp_destination,
            // 'trp_start_date'=> $this->fh_policy_tada_request_plan->trp_start_date ? Carbon::parse($this->fh_policy_tada_request_plan->trp_start_date)->format('d M, Y') : null,
            // 'trp_end_date'=> $this->fh_policy_tada_request_plan->trp_end_date ? Carbon::parse($this->fh_policy_tada_request_plan->trp_end_date)->format('d M, Y') : null,
            // 'trp_from_time'=> $this->fh_policy_tada_request_plan->trd_start_time ? DateTime::createFromFormat('H:i:s', $this->fh_policy_tada_request_plan->trd_start_time)->format('g:i A') : null,
            // 'trp_to_time'=> $this->fh_policy_tada_request_plan->trd_end_time ? DateTime::createFromFormat('H:i:s', $this->fh_policy_tada_request_plan->trd_end_time)->format('g:i A') : null,
            'mode_id' => $this->fh_policy_tada_travel_mode ? TravelModeResource::collection([$this->fh_policy_tada_travel_mode])->all() : null,
            'vehicle_id' => $this->fh_policy_tada_travel_vehicle ? TravelVehicleResource::collection([$this->fh_policy_tada_travel_vehicle])->all() : null,
            'eligibility' => $this->fh_policy_tada_travel_allowance ? TravelAllowanceEligibilityResource::collection([$this->fh_policy_tada_travel_allowance])->all() : null,
            'source' => $this->trd_source ?? '',
            'destination' => $this->trd_destination ?? '',
            'start_date' => $this->trd_start_date ? Carbon::parse($this->trd_start_date)->format('d M, Y') : null,
            'end_date' => $this->trd_end_date ? Carbon::parse($this->trd_end_date)->format('d M, Y') : null,
            'document' => $this->trd_documents ? json_decode($this->trd_documents) : null,
            // 'segments' => json_decode($this->trd_segments) ?? [],
            'segments' => $uniqueSegments,
            'start_time' => $this->trd_start_time ? Carbon::parse($this->trd_start_time)->format('h:i A') : null,
            'end_time' => $this->trd_end_time ? Carbon::parse($this->trd_end_time)->format('h:i A') : null,
            'total_distance' => (double) $this->trd_total_distance ?? 0,
            'total_tolerance' => $this->trd_total_tolerance ?? 0,
            'call_id' => $this->trd_call_id ?? '',
            'status' => $this->trd_status ?? 0,
            'remark' => $this->trd_remarks ?? '',
            'purpose' => $this->fh_travel_purpose ? TravelPurposeResource::collection([$this->fh_travel_purpose])->all() : null,
            'ticket_type' => $this->trd_ticket_type ?? 0,
            'amount' => $this->trd_net_amount ?? 0,
            // 'amount' => $this->trd_total_distance > 0 ? $this->trd_net_amount * $this->trd_total_distance : $this->trd_net_amount,
            'trd_hotel_location'=> $this->trd_hotel_location ?? '',
            'trd_type_id'=> $this->trd_type_id,
            // 'trd_geo_work_active' => $this->trd_geo_work_active,
            'trd_geo_work_active' => (bool) $this->trd_geo_work_active,

        ];
    }
}
