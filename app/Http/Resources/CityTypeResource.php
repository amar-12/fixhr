<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CityTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'state_id' => StateResource::collection([$this->fh_state])->all(),
            'city_type' => MasterTableResource::collection([$this->fh_city_type])->all(),
            'city_code' => $this->ct_code,
            'city_name' => $this->ct_name
        ];
    }
}
