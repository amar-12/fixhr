<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MasterTableResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->m_id,
            'group' => $this->m_group,
            'type' => $this->m_type,
            'name' => $this->m_name,
            'other' => $this->m_other ? [json_decode($this->m_other, true)] : [],
            'description' => $this->m_description ?? '',
        ];
    }
}
