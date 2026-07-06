<?php

namespace App\Http\Resources\Attendance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HolidayResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // 'phl_b_id' => $this->phl_b_id ?? 0,
            'phl_ap_id' => $this->phl_ap_id ?? 0,
            'phl_name' => $this->phl_name ?? '',
            'phl_month' => $this->phl_month ?? '',
            'phl_month_number' => $this->phl_month_number ?? null,
            'days_in_month' => $this->days_in_month ?? 0,
            'phl_start_date' => $this->phl_start_date ? ($this->phl_start_date)->format('d M, Y') : null,
            'phl_end_date' => $this->phl_end_date ? ($this->phl_end_date)->format('d M, Y') : null,
        ];
    }
}
