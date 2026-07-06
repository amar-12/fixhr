<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BusinessResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return[
            'b_unique_id' => $this->b_unique_id ?? '',
            'b_a_id' => $this->b_a_id ?? 0,
            'b_category_id' => $this->b_category_id ?? '',
            'b_type_id' => $this->b_type_id ?? 0,
            'b_name' => $this->b_name ?? '',
            'b_city_id' => $this->b_city_id ?? 0,
            'b_state_id' => $this->b_state_id ?? 0,
            'b_country_id' => $this->b_country_id ?? '',
            'b_gst_no' => $this->b_gst_no ?? '',
            'b_pan_no' => $this->b_pan_no ?? '',
            'b_pin_code' => $this->b_pin_code ?? '',
            'b_address' => $this->b_address ?? '',
            'b_logo' => $this->b_logo ?? '',
            'b_is_verified' => $this->b_is_verified ?? '',
            'b_status' => $this->b_status ?? '',
        ];
}
}
