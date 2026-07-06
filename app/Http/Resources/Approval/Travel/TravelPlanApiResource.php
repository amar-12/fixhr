<?php

namespace App\Http\Resources\Approval\Travel;

use App\Http\Resources\MasterTableResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TravelPlanApiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'trp_id' => $this->trp_id ?? 0,
            'trp_unique_id' => $this->trp_unique_id ?? '',
            'trp_branch_name' => $this->fh_branch->br_name ?? '',
            'trp_emp_id' => $this->trp_emp_id ?? 0,
            'trp_emp_d_id' => $this->fh_employee->emp_d_id ?? 0,
            'trp_emp_name' => $this->fh_employee->emp_full_name ?? '',
            'trp_emp_profile' => $this->fh_employee->emp_profile_photo ?? '',
            'trp_emp_code' => $this->fh_employee->emp_code ?? '',
            'trp_pttt_name' => $this->fh_policy_tada_travel_type->fh_travel_type->m_name ?? '',
            'trp_name' => $this->trp_name ?? '',
            'trp_purpose' => $this->fh_travel_purpose ? $this->fh_travel_purpose->tp_name : '',
            'trp_advance_allowance' => $this->trp_advance_allowance ?? 0,
            'trp_request_status' => $this->fh_approval_status ? MasterTableResource::collection([$this->fh_approval_status]) : [] ,
            'trp_call_id' => $this->trp_call_id ?? '',
            'trp_destination' => $this->trp_destination ?? '',
        ];
    }
}
