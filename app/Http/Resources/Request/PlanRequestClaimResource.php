<?php

namespace App\Http\Resources\Request;

use App\Http\Resources\Approval\Travel\TravelRequestApiResource;
use App\Http\Resources\MasterTableResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanRequestClaimResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'tc_trp_id' => $this->tc_trp_id,
            'unique_id' => $this->tc_unique_id ?? "",
            'tc_emp_id' => $this->tc_emp_id,
            'tc_amount' => $this->tc_amount,
            'tc_deduction_amount' => $this->tc_deduction_amount ?? 0,
            'tc_approved_date' => $this->tc_approved_date,
            'tc_payment_date' => $this->tc_payment_date,
            'tc_claimed_amount' => $this->tc_claimed_amount ?? 0,
            'tc_status' => $this->fh_claim_status ? MasterTableResource::collection([$this->fh_claim_status]) : [] ,
            'tc_deduction_status' => $this->tc_deduction_status,
            'tc_deduction_remarks' => $this->tc_deduction_remarks ?? '',
            'tc_remarks' => $this->tc_remarks ?? '',
            'tc_plan_details' => FilterPlanRequestApiResource::collection([$this->fh_tada_request_plan])->all(),
        ];
    }
}
