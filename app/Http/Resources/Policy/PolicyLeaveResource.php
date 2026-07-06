<?php

namespace App\Http\Resources\Policy;

use App\Http\Resources\LeaveTypeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PolicyLeaveResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'pl_id' => $this->pl_id ?? 0,
            'pl_name' => $this->pl_name ?? '',
            'pl_type' => $this->fh_leave_type ? LeaveTypeResource::collection($this->fh_leave_type) : [],
        ];
    }
}
