<?php

namespace App\Http\Resources\Approval;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProcessApproverApiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'pa_id'=> $this-> pa_id,
            'pa_am_id'=> $this-> pa_am_id ?? 0,
            'pa_type'=> $this-> pa_type ?? '',
            'pa_status_id'=> $this-> pa_status_id ?? 0,
            'pa_sequence'=> $this-> pa_sequence ?? 0,
            'pa_is_last'=> $this-> pa_last ?? 0,
            'approval_status_name'=> $this->fh_approver_status->m_name ?? '',
        ];
    }
}
