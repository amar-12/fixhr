<?php

namespace App\Http\Resources\Approval\Travel;

use App\Http\Resources\MasterTableResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class AdvanceLogApiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Directly use optional when needed
        $employee = optional($this->fh_tada_request_plan)->fh_employee;

        return [
            'advance_id' => $this->adl_id,
            'requested_amount' => $this->adl_requested_amount ?? 0,
            'received_amount' => $this->adl_reimburse_amount ,
            'advance_status' => $this->fh_adl_request_status ? MasterTableResource::collection([$this->fh_adl_request_status]):[],
            'am_id' => $this->adl_am_id ?? null,
            'module_id' => $this->adl_module_id ?? null,
            'remark' => $this->adl_remark ?? '',
            'name' => $employee->emp_full_name ?? '',
            'code' => $employee->emp_code ?? '',
            'adl_approval_log' => $this->fh_plan_approval_log ? ApprovalLogApiResource::collection($this->fh_plan_approval_log)->all() : [],
            'created_date' => Carbon::parse($this->created_at)->format('Y-m-d'),
        ];
    }
}
