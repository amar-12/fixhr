<?php

namespace App\Http\Resources\Policy;

use App\Http\Resources\MasterTableResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TravelVehicleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'vehicle_id' => $this-> pttv_id,
            'business_id' => $this->pttv_b_id ?? 0,
            'eligibility_name' => isset($this->fh_claim_type) ? $this->fh_claim_type->m_name : '',
            'eligibility_data' => [[
                'eligibility'=> $this->pttv_eligibility ?? 0,
                'claim_type_id' => isset($this->fh_claim_type) ? $this->fh_claim_type->m_id : null,
            ]],
            'claim_type_id'=> $this->fh_claim_type->m_id,
            'claim_type_name'=> $this->fh_claim_type->m_name,
            'vehicle_detail' => $this->fh_vehicle ? MasterTableResource::collection([$this->fh_vehicle]) : [],
            'vehicle_owner_detail' => $this->fh_vehicle_owner ? MasterTableResource::collection([$this->fh_vehicle_owner]) : [],
            'vehicle_class_detail' => $this->fh_travel_class ? MasterTableResource::collection([$this->fh_travel_class]) : []
        ];

    }
}
