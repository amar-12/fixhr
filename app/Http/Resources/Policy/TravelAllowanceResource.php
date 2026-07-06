<?php

namespace App\Http\Resources\Policy;

use App\Http\Resources\Policy\TravelCategoryResource;
use App\Http\Resources\Policy\TravelModeResource;
use App\Http\Resources\TadaTravelTypeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TravelAllowanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'business_id' => $this->ptta_b_id ?? 0,
            'travel_category' => TravelCategoryResource::collection([$this->fh_policy_tada_category])->get('ptc_id','category_name'),
            'travel_mode' => TravelModeResource::collection([$this->fh_policy_tada_travel_mode])->all(),
            'travel_type' => TadaTravelTypeResource::collection([$this->fh_policy_tada_travel_type])->all(),
            'vehicle_id' =>TravelVehicleResource::collection([$this->fh_policy_tada_travel_vehicle])->all() ?? [],
            'eligibility' => $this->ptta_eligibility,
            'other_eligibility' => $this->ptta_other_eligibility,
            'remarks' => $this->ptta_remarks ?? ''
        ];
    }
}
