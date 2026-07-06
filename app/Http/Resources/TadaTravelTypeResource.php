<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TadaTravelTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return[
            'travel_id' => $this->pttt_id,
            'business_id' => $this->pttt_b_id ?? 0,
            'type_status' => $this->pttt_status,
            'type' => MasterTableResource::collection([$this->fh_travel_type])->all(),
            'approval_type' => MasterTableResource::collection([$this->fh_approval_type])->all(),
        ];
    }
}
