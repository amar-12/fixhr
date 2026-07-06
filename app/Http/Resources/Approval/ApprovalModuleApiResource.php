<?php

namespace App\Http\Resources\Approval;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApprovalModuleApiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'am_id'=> $this->am_id ,
            'b_id'=> $this->am_b_id ,
		    'module_id'=> $this->am_module_id ?? 0 ,
		    'name'=> $this->am_name ?? '' ,
		    'description'=> $this->am_description ?? '' ,
		    'exe_on'=> $this->am_exe_on ?? '',
		    'status'=> $this->am_status ?? 0 ,

        ];
    }
}
