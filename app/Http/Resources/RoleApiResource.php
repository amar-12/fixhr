<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RoleApiResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'role_name' => $this->role_name,
            'role_id' => $this->role_id,
        ];
    }
}
