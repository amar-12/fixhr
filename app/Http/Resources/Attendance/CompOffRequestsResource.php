<?php

namespace App\Http\Resources\Attendance;

use App\Helpers\ApprovalHelper;
use App\Http\Resources\Approval\Travel\ApprovalLogApiResource;
use App\Http\Resources\MasterTableResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompOffRequestsResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @return array<string, mixed>
	 */
	public function toArray(Request $request): array
	{
		$approval = ApprovalHelper::checkApproval($this->fh_employee->emp_b_id, $this->co_module_id, $this->co_id, $this->fh_employee->emp_d_id, $this->co_emp_id);

		$compOffResource = [
			'emp_id' => $this->fh_employee->emp_id,
			'emp_name' => $this->fh_employee->emp_full_name,
			'emp_code' => $this->fh_employee->emp_code,
			'emp_d_id' => $this->fh_employee->emp_d_id,
			'co_id' => $this->co_id ?? 0,
			'applied_date' => $this->co_request_date ? Carbon::parse($this->co_request_date)->format('d M, Y') : null,
			'request_date' => $this->created_at ? Carbon::parse($this->created_at)->format('d M, Y') : null,
			'co_am_id' => $this->co_am_id ?? 0,
			'co_module_id' => $this->co_module_id ?? 0,
			'co_status' => $this->fh_approval_status ? MasterTableResource::collection([$this->fh_approval_status]) : [],
			'co_approval_log_count' => $approval['approvallog_count'] ?? 0,
			'co_approval_count' => $approval['approvalcount'] ?? 0,
			'is_request_deletable' => $this->co_stage_completed == 0,
			'co_approval_log' => $this->fh_plan_approval_log ? ApprovalLogApiResource::collection($this->fh_plan_approval_log)->all() : [],
			'co_next_approver_details' => ApprovalHelper::getNextApprovalDetails($this->co_module_id, $this->co_id, $this->co_b_id)
		];
		return $compOffResource;
	}
}
