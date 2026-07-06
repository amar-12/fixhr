<?php

namespace App\Http\Resources\Approval\Travel;

use App\Helpers\ApprovalHelper;
use App\Http\Resources\Policy\TravelAllowanceResource;
use App\Http\Resources\Policy\TravelDailyAllowanceLodgingResource;
use App\Http\Resources\TadaExpenseResource;
use App\Http\Resources\Approval\Travel\TravelRequestDetailsApiResource;
use App\Http\Resources\MasterTableResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ClaimRequestDetailsApiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // $expenses = $this->fh_tada_request_plan->fh_tada_expenses;
        $expenses = $this->fh_tada_request_plan->fh_tada_expenses ?? collect();
        $approval = ApprovalHelper::checkApproval($this->tc_b_id, $this->tc_module_id, $this->tc_trp_id, $this->fh_employee->emp_d_id, $this->tc_emp_id);

        $travel = $expenses->filter(function ($expense) {
            return $expense->te_type_id == 159;
        })->sum(function ($expense) {
            return $expense->te_amount + $expense->te_taxes;
        });

        $lodging = $expenses->filter(function ($expense) {
            return $expense->te_type_id == 158;
        })->sum(function ($expense) {
            return $expense->te_amount + $expense->te_taxes;
        });

        $meal = $expenses->filter(function ($expense) {
            return $expense->te_type_id == 160;
        })->sum(function ($expense) {
            return $expense->te_amount + $expense->te_taxes;
        });

        $other = $expenses->filter(function ($expense) {
            return $expense->te_type_id == 161;
        })->sum(function ($expense) {
            return $expense->te_amount + $expense->te_taxes;
        });

        $miscClaim = $expenses->filter(function ($expense) {
            return $expense->te_type_id == 456;
        })->sum(function ($expense) {
            return $expense->te_amount + $expense->te_taxes;
        });

        $payable_amount = $this->tc_amount - (($this->fh_tada_request_plan->trp_advance_allowance ?? 0) + ($this->tc_deduction_amount ?? 0));

        $displayDeductionHandler = $this->fh_deduction_log->whereNull('dlog_requester_action')->first();

        $claimDetailValues = array_filter([
            'da' => $this->tc_da_amount ?? null,
            // 'ta' => $this->fh_tada_request_plan->fh_tada_request_details->sum(function ($details) {
            //     return $details->trd_net_amount;
            // }) ?? null,
            'ta' => $this->fh_tada_request_plan?->fh_tada_request_details
            ?->sum(fn($details) => $details->trd_net_amount) ?? null,
            'travel' => $travel > 0 ? $travel : null,
            'lodging' => $lodging > 0 ? $lodging : null,
            'meal' => $meal > 0 ? $meal : null,
            'other' => $other > 0 ? $other : null,
            'miscClaim' => $miscClaim > 0 ? $miscClaim : null,
        ]);

        $claim_details = !empty($claimDetailValues) ? [$claimDetailValues] : [];
        
        // Get reject remarks from approval logs where status is 170
        $rejectRemarks = $this->fh_claim_approval_log()
            ->where('log_status', 170)
            ->orderBy('created_at', 'desc')
            ->first()
            ->log_description ?? '';

        return [
            'tc_id' => $this->tc_id,
            'tc_trp_id' => $this->tc_trp_id,
            'tc_unique_id' => $this->tc_unique_id,
            'tc_emp_id' => $this->tc_emp_id,
            'tc_emp_d_id' => $this->fh_employee->emp_d_id,
            'tc_approval_log_count' => $approval['approvallog_count'] ?? 0,
            'tc_approval_count' => $approval['approvalcount'] ?? 0,
            'tc_amount' => $this->tc_amount,
            'tc_deduction_amount' => $this->tc_deduction_amount ?? 0,
            'tc_approved_date' => $this->tc_approved_date,
            'tc_payment_date' => $this->tc_payment_date,
            'net_payable_amount' => $payable_amount,
            'tc_paid_amount' => $this->tc_payed_amount > 0 ?$this->tc_payed_amount:null,
            'tc_status' => $this->fh_claim_status ? MasterTableResource::collection([$this->fh_claim_status]) : [] ,
            'tc_deduction_status' => $this->tc_deduction_status,
            'tc_am_id' => $this->tc_am_id ?? 146,
            'tc_module_id' => $this->tc_module_id,
            'tc_remarks' => $this->tc_remarks ?? '',
            'tc_deduction_remarks' => $this->tc_deduction_remarks ?? '',
            'tc_deduction_dlog_id' =>  ($displayDeductionHandler && $displayDeductionHandler->dlog_id ) ? $displayDeductionHandler->dlog_id  : 0,
            'tc_plan_details' => $this->fh_tada_request_plan ? TravelRequestDetailsApiResource::collection([$this->fh_tada_request_plan])->all() : [],
            'tc_approval_log' => ApprovalLogApiResource::collection($this->fh_claim_approval_log)->all(),
            'tc_deduction_log' => ApprovalDeductionLogApiResource::collection($this->fh_deduction_log)->all(),
            'claim_details' => $claim_details,
            'tc_next_approver_details'=> ApprovalHelper::getNextApprovalDetails($this->tc_module_id, $this->tc_id, $this->tc_b_id),
            'claim_pdf_url' => url('/api/admin/tada/travel_claim_pdf/' . md5($this->tc_id)),
            'tc_claim_reject_remakrs' => $rejectRemarks 
        ];
    }

}
