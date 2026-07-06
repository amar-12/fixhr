<?php

namespace App\Http\Resources\Approval;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApprovalLogApiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [

            'log_id'=>$this->log_id,
            'module_id'=>$this->log_am_id ?? 0,
		    'request_id'=>$this->log_request_id ?? 0,
		    'user_id'=>$this->log_user_id ?? 0,
		    'user_role_id'=>$this->log_user_role_id ?? 0,
		    'status'=>$this->log_status ?? 0,
		    'description'=>$this->log_description ?? '',
        ];
    }
}
