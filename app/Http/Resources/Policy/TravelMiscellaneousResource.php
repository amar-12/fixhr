<?php

namespace App\Http\Resources\Policy;

use App\Http\Resources\MasterTableResource;
use App\Http\Resources\MiscellaneousResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TravelMiscellaneousResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'business_id' => $this->pm_b_id ?? 0,
            'category_id' => TravelCategoryResource::collection([$this->fh_policy_tada_category])->all(),
            'miscellaneous-id' => MiscellaneousResource::collection([$this->fh_miscellaneous])->all(),
            'city_type' => MasterTableResource::collection([$this->fh_city_type])->all(),
            'eligibility' => $this->pm_eligibility,
            'remarks' => $this->pm_remarks
        ];
    }
}
