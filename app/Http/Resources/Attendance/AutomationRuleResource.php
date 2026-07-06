<?php

namespace App\Http\Resources\Attendance;

use App\Http\Resources\MasterTableResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AutomationRuleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'occurrences' => $this->ar_occurrences,
            'half_day'=> $this->ar_mark_half_day_time,
            'min_overtime' => $this->ar_min_overtime ?? null,
            'max_overtime' => $this->ar_max_overtime ?? null,
            'bussiness_id' => $this->ar_b_id,
            'rule_type'=> $this->fh_rule_type ? MasterTableResource::collection([$this->fh_rule_type]) : [],
            'absent_day_type' => $this->fh_absent_day_type ? MasterTableResource::collection([$this->fh_absent_day_type]) : [],

        ];
    }
}
