<?php

namespace App\Http\Resources\RolePermission;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppMenusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'menu_id' => $this->menu_id,
            'menu_p_id' => $this->menu_p_id,
            'menu_route_type_id' => $this->menu_route_type_id,
            'menu_name' => $this->menu_name,
            'menu_icon' => $this->menu_icon,
            'menu_status' => $this->menu_status,
            'menu_sub_status' => $this->menu_sub_status,
            'menu_route' => $this->menu_route,
            'menu_group' => $this->menu_group,
            'menu_sequence' => $this->menu_sequence,
            'menu_description' => $this->menu_description
        ];
    }

    public function toArray2(Request $request): array
    {
        return [
            'icon' => $this->menu_icon,
            'title' => $this->menu_name,
            'route' => $this->menu_route
        ];
    }
}
