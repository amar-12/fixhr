<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'business_id' => $this->br_b_id ?? 0,
            'name' => $this->br_name ?? '',
            'email' => $this->br_email ?? '',
            'active' => $this->br_is_active ?? 0,
            'address' => $this->br_address ?? '',
            'longitude' => $this->br_longitude ?? null,
            'latitude' => $this->br_latitude ?? null,
            'range_limit' => $this->br_range_limit ?? null,

            'wifi_address' => $this->br_is_wifi_restricted ? $this->br_wifi_address : '',
        ];
    }
}
