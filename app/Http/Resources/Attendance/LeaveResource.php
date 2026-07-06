<?php

namespace App\Http\Resources\Attendance;

use App\Helpers\ApprovalHelper;
use App\Http\Resources\Approval\Travel\ApprovalLogApiResource;
use App\Http\Resources\MasterTableResource;
use App\Http\Resources\Policy\PolicyLeaveResource;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\MasterTable;
use App\Models\ProcessApprover;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class LeaveResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $relatedLeaveRequestsIncludingParent = LeaveRequest::withoutGlobalScope('not_sandwich')->where('lvr_p_id', $this->lvr_p_id ?? $this->lvr_id)->orWhere('lvr_id', $this->lvr_p_id ?? $this->lvr_id)->get();

        $startDate = $relatedLeaveRequestsIncludingParent->min('lvr_start_date');
        $endDate = $relatedLeaveRequestsIncludingParent->max('lvr_end_date');

        $leaveCategoryBreakdown = $relatedLeaveRequestsIncludingParent->groupBy('lvr_cat_type_id')->map(function ($group) {
            return [
                'category' => new MasterTableResource($group->first()->fh_leave_cat_type),
                'count' => $group->sum('lvr_total_leave_days'),
            ];
        })->values();

        $approval = ApprovalHelper::checkApproval($this->fh_employee->emp_b_id, $this->lvr_module_id, $this->lvr_id, $this->fh_employee->emp_d_id, $this->fh_employee->emp_id);
        $nextApproverDetails = ApprovalHelper::getNextApprovalDetails($this->lvr_module_id, $this->lvr_id, $this->lvr_b_id);
        $superAdmin = Employee::where('emp_role_id', 1)->where('emp_b_id', $this->lvr_b_id)->select('emp_id', 'emp_b_id', 'emp_full_name')->first();

        $leaveResource = [
            'emp_id' => $this->fh_employee->emp_id,
            'emp_name' => $this->fh_employee->emp_full_name,
            'emp_code' => $this->fh_employee->emp_code,
            'emp_d_id' => $this->fh_employee->emp_d_id,
            'leave_id' => $this->lvr_id ?? 0,
            'leave_category' => $leaveCategoryBreakdown ?? ($this->lvr_cat_type_id ? MasterTableResource::collection([$this->fh_leave_cat_type]) : []),
            'start_date' => $startDate ? Carbon::parse($startDate)->format('d M, Y') : null,
            'end_date' => $endDate ? Carbon::parse($endDate)->format('d M, Y'): null ,
            'leave_am_id' => $this->lvr_am_id ?? 0,
            'leave_module_id' => $this->lvr_module_id ?? 0,
            'total_days' => $relatedLeaveRequestsIncludingParent->sum('lvr_total_leave_days') ?? 0,
            'leave_document' => $this->lvr_documents ? json_decode($this->lvr_documents) : [],
            'leave_day_type' => $this->fh_leave_day_type ? MasterTableResource::collection([$this->fh_leave_day_type]) : [],
            'leave_day_segment' => $this->fh_leave_day_segment ? MasterTableResource::collection([$this->fh_leave_day_segment]) : [],
            'leave_status' => $this->fh_approval_status ? MasterTableResource::collection([$this->fh_approval_status]) : [],
            'approved_by' => $this->lvr_approved_by ? ApprovalHelper::getEmployeename($this->lvr_approved_by) : '',
            'approval_status_id' => $approval['status_id'] ?? 157,
            'can_approve' => $nextApproverDetails['approver_id'] == ($request->user()->emp_id ?? 0) ? true : false,
            'apply_date' => $this->created_at ? Carbon::parse($this->created_at)->format('d M, Y') : null,
            'reason' => $this->lvr_reason ?? '',
            'lvr_approval_log_count' => $approval['approvallog_count'] ?? 0,
            'lvr_approval_count' => $approval['approvalcount'] ?? 0,
            'is_request_deletable' => $this->lvr_stage_completed == 0,
            'leave_approval_log' => $this->fh_approval_log2 ? ApprovalLogApiResource::collection($this->fh_approval_log2)->all() : [],
            'leave_next_approver_details' => $nextApproverDetails,
            'super_admin_log' => $this->lvr_status == 171 ? [['name' => $superAdmin->emp_full_name, 'emp_id' => $superAdmin->emp_id, 'emp_b_id' => $superAdmin->emp_b_id, 'created_at' => $this->created_at ? Carbon::parse($this->created_at)->format('d M, Y') : null]] : [],
        ];

        // if ($isApprover) {
        //     $additionalData = [
        //         'emp_id' => $this->fh_employee->emp_id,
        //         'emp_name' => $this->fh_employee->emp_full_name,
        //         'emp_code' => $this->fh_employee->emp_code,
        //         'emp_d_id' => $this->fh_employee->emp_d_id
        //     ];
        //     return array_merge($additionalData, $leaveResource);
        // } else
            return $leaveResource;
    }
}
