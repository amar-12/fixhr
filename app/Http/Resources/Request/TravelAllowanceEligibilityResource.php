<?php

namespace App\Http\Resources\Request;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TravelAllowanceEligibilityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'claim_type_id' => $this->pttv_claim_type_id ?? 0,
            'eligibility' => $this->pttv_eligibility ?? 0,
            'other_eligibility' => $this->pttv_eligibility ?? 0,
        ];
    }
}
