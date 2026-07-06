<?php

namespace App\Http\Resources\Policy;

use App\Http\Resources\MasterTableResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class TravelModeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'mode_id' => $this->pttm_id ?? 0,
            'business_id' => $this->pttm_b_id ?? 0,
            'travel_mode' => MasterTableResource::collection([$this->fh_travel_mode])->all()
        ];
    }
}
