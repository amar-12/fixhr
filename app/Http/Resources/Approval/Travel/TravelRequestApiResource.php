<?php

namespace App\Http\Resources\Approval\Travel;

use App\Helpers\ApprovalHelper;
use App\Http\Resources\MasterTableResource;
use App\Http\Resources\TadaExpenseResource;
use App\Http\Resources\TravelPurposeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class TravelRequestApiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $is_auto_approval = optional($this->fh_policy_tada_travel_type)->pttt_approval_type_id == 197;

        $editableStatus = ['is_trp_plan_editable'=>false, 'is_trp_detail_editable'=>false, 'is_trp_expense_editable'=> false, 'is_trp_claimable'=>false];
        if($this->trp_is_claimed == 0){
            $editableStatus = ApprovalHelper::getEditableStatus($this->trp_b_id, $this->trp_module_id, $this->trp_id, $this->trp_request_status ,$is_auto_approval, $this->fh_employee->emp_d_id, $this->trp_emp_id);
        }

        $resourceData =  [
            'trp_id' => $this->trp_id ?? 0,
            'trp_unique_id' => $this->trp_unique_id ?? '',
            'trp_b_id' => $this->trp_b_id ?? 0,
            'trp_br_id' => $this->trp_br_id ?? 0,
            'trp_emp_id' => $this->trp_emp_id,
            'trp_emp_d_id' => $this->fh_employee->emp_d_id,
            'trp_emp_name' => $this->fh_employee->emp_full_name ?? '',
            'trp_emp_profile' => $this->fh_employee->emp_profile_photo ? url('/uploads/employee_profile/'.$this->fh_employee->emp_profile_photo) : '',
            'trp_emp_code' => $this->fh_employee->emp_code ?? '',
            'trp_pttt_id' => $this->trp_pttt_id ?? 0,
            'trp_pttt_name' => $this->fh_policy_tada_travel_type->fh_travel_type->m_name ?? '',
            'trp_ptc_id' => $this->trp_ptc_id ?? 0,
            'trp_am_id' => $this->trp_am_id ?? 0,
            'trp_module_id' => $this->trp_module_id ?? 0,
            'trp_name' => $this->trp_name ?? '',
            'trp_purpose' => $this->fh_travel_purpose ? TravelPurposeResource::collection([$this->fh_travel_purpose])->all() : [],
            'trp_advance_allowance' => $this->trp_advance_allowance ?? 0,
            'trp_request_status' => $this->fh_approval_status ? MasterTableResource::collection([$this->fh_approval_status]) : [] ,
            'trp_call_id' => $this->trp_call_id ?? '',
            'trp_created_at' => $this->created_at,
            'trp_updated_at' => $this->updated_at,
            'trp_destination' => $this->trp_destination ?? '',
            'trp_start_date' => $this->trp_start_date ? Carbon::parse($this->trp_start_date)->format('d M, Y') : null,
            'trp_end_date' => $this->trp_end_date ? Carbon::parse($this->trp_end_date)->format('d M, Y') : null,
            'trp_start_time' => $this->trp_start_time ? Carbon::parse($this->trp_start_time)->format('h:i A') : null,
            'trp_end_time' => $this->trp_end_time ? Carbon::parse($this->trp_end_time)->format('h:i A') : null,
            'trp_documents' => $this->trp_document ? json_decode($this->trp_document, true) : [],
            'trp_details' => TravelRequestDetailResource::collection($this->fh_tada_request_details)->all(),
            'trp_expense_details' => TadaExpenseResource::collection($this->fh_tada_expenses)->all(),
            'trp_approval_log' => ApprovalLogApiResource::collection($this->fh_plan_approval_log)->all(),
        ];

        return array_merge($resourceData, $editableStatus);
    }
}
