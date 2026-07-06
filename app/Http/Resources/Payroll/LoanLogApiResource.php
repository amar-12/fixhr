<?php

namespace App\Http\Resources\Payroll;

use Carbon\Carbon;
use App\Models\ApprovalLog;
use Illuminate\Http\Request;
use App\Helpers\ApprovalHelper;
use App\Models\ProcessApprover;
use App\Http\Resources\MasterTableResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Approval\Travel\ApprovalLogApiResource;

class LoanLogApiResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        $approval = ApprovalHelper::checkApproval(
            $this->fh_employee->emp_b_id,
            $this->lnr_module_id,
            $this->lnr_id,
            $this->fh_employee->emp_d_id,
            $this->lnr_emp_id
        );

        $approversList = ProcessApprover::where([
            'pa_b_id' => $this->lnr_b_id,
            'pa_am_id' => $this->lnr_am_id
        ])->pluck('pa_emp_id');

        return [
            'id' => $this->lnr_id,
            'unique_id' => $this->lnr_unique_id,
            'emp_id' => $this->lnr_emp_id,
            'b_id' => $this->lnr_b_id,
            'requested_amount' => $this->lnr_requested_amount,
            'installment_amount' => $this->lnr_installment_amount,
            'installments' => $this->lnr_installments,
            'rate' => $this->lnr_rate,
            'start_date' => $this->lnr_start_date ? Carbon::parse($this->lnr_start_date)->format('d M, Y') : null,
            'description' => $this->lnr_description,
            'module_id' => $this->lnr_module_id,
            'am_id' => $this->lnr_am_id,
            'request_status_id' => $this->lnr_request_status,
            'stage_completed' => $this->lnr_stage_completed,
            'next_approver' => $this->lnr_next_approver,
            'status' => $this->lnr_status,
            'created_at' => $this->created_at ? $this->created_at->format('d M Y, h:i A') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('d M Y, h:i A') : null,

            // Approval & Deletion Logic
            'is_request_deletable' => $this->lnr_stage_completed == 0,
            'approval_log_count' => $approval['approvallog_count'] ?? 0,
            'approval_count' => $approval['approvalcount'] ?? 0,
            'approval_log' => $this->fh_plan_approval_log
                ? ApprovalLogApiResource::collection($this->fh_plan_approval_log)->all()
                : [],
            'next_approver_details' => ApprovalHelper::getNextApprovalDetails(
                $this->lnr_module_id,
                $this->lnr_id,
                $this->lnr_b_id
            ),

            // Employee Info
            'emp_name' => $this->fh_employee->emp_full_name ?? '',
            'emp_code' => $this->fh_employee->emp_code ?? '',
            'emp_d_id' => $this->fh_employee->emp_d_id ?? '',

            // Status as Master Table Resource
            'status' => $this->fh_master_table
                ? MasterTableResource::collection([$this->fh_master_table])
                : [],
            'loan_request_pdf_url' => url('/api/admin/loan/loan_request_pdf/' . md5($this->lnr_id)),
            'emi_schedule_pdf_url' => url('/api/admin/loan/emi-schedule/' . md5($this->lnr_id)),
        ];
    }


}
