<?php

namespace App\Http\Resources\Approval;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActionUponRejectionApiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return[
            'aur_id' =>$this->aur_id ,
            'b_id' =>$this->aur_b_id ,
            'module_id' =>$this->aur_am_id ?? 0,
            'user_ids' =>$this->aur_group_ids ?? 0,
            'status_id' =>$this->aur_status_id ?? 0,
            'description' =>$this->aur_description ?? '',
            'role_id' =>$this->aur_role_id ?? 0,
        ];

    }
}
