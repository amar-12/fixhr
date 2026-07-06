<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BusinessPolicyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->bpd_id  ?? '',
            'folder_name' => $this->folder->bpf_name ?? '',
            'business_id' => $this->bpd_b_id ?? '',
            'version' => $this->bpd_version ?? '',
            'file_name' => $this->bpd_file_name ?? '',
            'with_effect_from' => $this->bpd_with_effect_from ?? '',
            'file_path' => $this->bpd_file_path ?? '',
            'status' => $this->bpd_status ?? '',
        ];
    }
}



