<?php

namespace App\Http\Resources\Announcement;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementApiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'ann_id'       => $this->ann_id,
            'ann_b_id'     => $this->ann_b_id,
            'ann_is_read'  => (int) $this->ann_is_read,
            'ann_title'    => $this->ann_title,
            'ann_message'  => $this->ann_message,
            'ann_role_id'  => $this->ann_role_id,
            'ann_category' => $this->ann_category,
            'ann_image'    => $this->ann_image,
            'ann_status'   => $this->ann_status ?? null,
            'created_at'   => $this->created_at ? $this->created_at->format('d M Y, h:i A') : null,
            'updated_at'   => $this->updated_at ? $this->updated_at->format('d M Y, h:i A') : null,
        ];
    }

}
