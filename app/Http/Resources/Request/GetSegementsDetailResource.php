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

class GetSegementsDetailResource extends JsonResource
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
            'segments' => $uniqueSegments,
        ];
    }
}