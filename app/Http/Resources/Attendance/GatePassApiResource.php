<?php

namespace App\Http\Resources\Attendance;

use App\Helpers\ApprovalHelper;
use App\Http\Resources\Approval\Travel\ApprovalLogApiResource;
use App\Http\Resources\MasterTableResource;
use App\Models\ProcessApprover;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class GatePassApiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $approval = ApprovalHelper::checkApproval($this->fh_employee->emp_b_id, $this->gtp_module_id, $this->gtp_id, $this->fh_employee->emp_d_id, $this->fh_employee->emp_id);
        $nextApproverDetails = ApprovalHelper::getNextApprovalDetails($this->gtp_module_id, $this->gtp_id, $this->gtp_b_id);
        $approversList = ProcessApprover::where(['pa_b_id'=>$this->gtp_b_id,'pa_am_id'=>$this->gtp_am_id])->pluck('pa_emp_id');
        //$isApprover = $approversList->contains(Auth::user()->emp_id);
        return [
            'id' => $this->gtp_id,
            'date'=>$this->gtp_date ? Carbon::parse($this->gtp_date)->format('d M, Y') : '',
            'in_time'=>$this->gtp_in_time ? Carbon::parse($this->gtp_in_time)->format('h:i A'): '',
            'out_time'=>$this->gtp_out_time ? Carbon::parse($this->gtp_out_time)->format('h:i A') : '',
            'module_id'=>$this->gtp_module_id,
            'am_id'=>$this->gtp_am_id,
            'destination'=>$this->gtp_destination ?? '',
            'reason'=>$this->gtp_reason ?? '',
            'status'=>$this->fh_master_table ? MasterTableResource::collection([$this->fh_master_table]):[],
            'approval_log_count' => $approval['approvallog_count'] ?? 0,
            'approval_count' => $approval['approvalcount'] ?? 0,
            'approved_by' => $this->gtp_approved_by ? ApprovalHelper::getEmployeename($this->gtp_approved_by) : '',
            'is_request_deletable' => $this->gtp_stage_completed == 0,
            'approval_log' => $this->fh_approval_log2 ? ApprovalLogApiResource::collection($this->fh_approval_log2)->all() : [],
            'next_approver_details' => $nextApproverDetails,
            'can_approve' => $nextApproverDetails['approver_id'] == ($request->user()->emp_id ?? 0) ? true : false,
            'emp_id' => $this->fh_employee->emp_id,
            'emp_name' => $this->fh_employee->emp_full_name,
            'emp_code' => $this->fh_employee->emp_code,
            'emp_d_id' => $this->fh_employee->emp_d_id,
            'gatepass_confirmation' => [
                'gcp_in_time_confirmation' => $this->fh_gatepass_confirmation ? ($this->fh_gatepass_confirmation->gcp_in_time_confirmation ? '1' : '0') : '0',
                'gcp_out_time_confirmation' => $this->fh_gatepass_confirmation ? ($this->fh_gatepass_confirmation->gcp_out_time_confirmation ? '1' : '0') : '0',
            ]
        ];
    }
}
