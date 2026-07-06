<?php

namespace App\Http\Resources\Policy;

use App\Http\Resources\DepartmentResource;
use App\Http\Resources\DesignationResource;
use App\Http\Resources\GradeResource;
use App\Http\Resources\TadaTravelTypeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TravelCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'ptc_id'=>$this->ptc_id,
            'business_id' => $this->ptc_b_id ?? 0,
            'category_name' => $this->ptc_name ?? '',
            'department' => DepartmentResource::collection([$this->fh_department])->all(),
            'designation' => DesignationResource::collection($this->getFhDesignationsAttribute)->all(),
            'travel_type' => TadaTravelTypeResource::collection([$this->fh_travel_type])->all(),
            'grade' => GradeResource::collection([$this->fh_grade])->all()
        ];
    }
}
