<?php

namespace App\Http\Resources\Travel;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TravelLocationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // $decodedLocations = json_decode($this->locations, true) ?? [];

        // // Ensure the structure is correctly treated as an object
        // $formattedLocations = is_array($decodedLocations) && array_values($decodedLocations) !== $decodedLocations
        //     ? $decodedLocations
        //     : (object)[];  // Ensures empty response appears as an object

        return [
            'lc_id' => $this->lc_id,
            'lc_trp_id' =>  $this->lc_trp_id,
            'lc_trd_id' => $this->lc_trd_id,
            'lc_total_distance'=> $this->lc_total_distance ? $this->lc_total_distance : 0,
            'lc_emp_id' => $this->lc_emp_id,
            'locations' => json_decode($this->locations)
        ];
    }
}
