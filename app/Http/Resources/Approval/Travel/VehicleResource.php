<?php

namespace App\Http\Resources\Approval\Travel;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'vehicle_id' => $this-> pttv_id ?? 0,
            'vehicle_name' => $this->fh_vehicle->m_name ?? '',
            'vehicle_owner_id' => $this->pttv_owner_id ?? 0,
            'vehicle_owner_name' => isset($this->fh_vehicle_owner) ? $this->fh_vehicle_owner->m_name : '',
            'travel_class_id' => $this->pttv_class_id ?? 0,
            'travel_class_name' => isset($this->fh_travel_class) ? $this->fh_travel_class->m_name : ''
        ];

    }
}
