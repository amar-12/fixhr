<?php

namespace App\Http\Resources\Request;

use App\Helpers\ApprovalHelper;
use App\Http\Resources\Approval\Travel\ApprovalLogApiResource;
use App\Http\Resources\MasterTableResource;
use App\Http\Resources\TadaTravelTypeResource;
use App\Http\Resources\TravelPurposeResource;
use App\Models\PolicyTadaCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

use function React\Promise\all;

class FilterPlanRequestApiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = Auth::user();

        $is_auto_approval = optional($this->fh_policy_tada_travel_type)->pttt_approval_type_id == 197;

        $editableStatus = ['is_trp_plan_editable'=>false, 'is_trp_detail_editable'=>false, 'is_trp_expense_editable'=> false, 'is_trp_claimable'=>false];
        if($this->trp_is_claimed == 0){
            $editableStatus = ApprovalHelper::getEditableStatus($this->trp_b_id, $this->trp_module_id, $this->trp_id, $this->trp_request_status ,$is_auto_approval, $this->fh_employee->emp_d_id, $this->trp_emp_id);
        }

        if (!$is_auto_approval) {
            $approval = ApprovalHelper::checkApproval($user->emp_b_id, $this->trp_module_id, $this->trp_id, $this->fh_employee->emp_d_id, $this->trp_emp_id);
        }


        $policyCategory = PolicyTadaCategory::where(['ptc_b_id' => $user->emp_b_id, 'ptc_d_id' => $user->emp_d_id, 'ptc_grade_id' => $user->emp_grade_id])->whereJsonContains('ptc_dg_id', $user->emp_dg_id)->first();
        if (!$policyCategory) {
            return response()->json(['result' => [], 'status' => false, 'message' => 'Sorry! policy not found, contact administration.']);
        }
        $response = app('App\Http\Controllers\Api\Policy\TravelAllowanceApiController')->getEligibilityByPolicyTravelType($policyCategory, $this->fh_policy_tada_travel_type->pttt_id);

        $totalDAAll = app('App\Http\Controllers\Api\Policy\TravelAllowanceApiController')->calculateDailyAllowance($this, $response['da_eligibility']);
        $totalDA = $totalDAAll['totalDA'];


        $totalExpenseAmount = $this->fh_tada_expenses->where('te_paid_by', 'self')->sum('te_amount');
        $totalExpenseTaxes = $this->fh_tada_expenses->where('te_paid_by', 'self')->sum('te_taxes');
        $totalExpenseDeviation = $this->fh_tada_expenses->where('te_paid_by', 'self')->sum('te_deviation');
        $totalTravelAmount = $this->fh_tada_request_details->sum('trd_net_amount'); //This is currently for Local travel only

        $hasAdvanceLog = $this->fh_tada_advance_approval_log()->exists();
        $isAdvanceApproved = $this->fh_tada_advance_approval_log()->whereNull('adl_reimburse_amount')->doesntExist();

        $expenses = $this->fh_tada_expenses;

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

        $resourceData = [
            'trp_id'=> $this->trp_id ?? 0,
            'trp_unique_id'=> $this->trp_unique_id ?? '',
            'trp_b_id' => $this->trp_b_id ?? 0,
            'trp_br_id' => $this->trp_br_id ?? 0,
            'trp_emp_id' => $this->trp_emp_id ?? 0,
            'trp_claim_amount' => round(($totalExpenseAmount+$totalExpenseTaxes+$totalTravelAmount) - $totalExpenseDeviation),
            'trp_da' => $totalDA,
            'trp_approval_log_count' => $approval['approvallog_count'] ?? 0,
            'trp_approval_count' => $approval['approvalcount'] ?? 0,
            'trp_pttt_id' => $this->trp_pttt_id ?? 0,
            'trp_pttt_details' => TadaTravelTypeResource::collection([$this->fh_policy_tada_travel_type])->all(),
            'trp_ptc_id' => $this->trp_ptc_id ?? 0,
            'trp_name' => $this->trp_name ?? '',
            'trp_destination'=> $this->trp_destination ?? '',
            'trp_start_date' => $this->trp_start_date ? Carbon::parse($this->trp_start_date)->format('d M, Y') : null,
            'trp_end_date' => $this->trp_end_date ? Carbon::parse($this->trp_end_date)->format('d M, Y') : null,
            'trp_start_time' => $this->trp_start_time ? Carbon::parse($this->trp_start_time)->format('h:i A') : null,
            'trp_end_time' => $this->trp_end_time ? Carbon::parse($this->trp_end_time)->format('h:i A') : null,
            'trp_purpose' => $this->fh_travel_purpose ? TravelPurposeResource::collection([$this->fh_travel_purpose])->all() : [],
            'trp_advance_allowance' => $this->trp_advance_allowance ?? 0,
            'trp_remark' => $this->trp_remarks ?? '',
            'trp_document' => $this->trp_document ? json_decode($this->trp_document, true) : [],
            'trp_request_status' => $this->fh_approval_status ? MasterTableResource::collection([$this->fh_approval_status]) : [],
            'trp_created_at' => $this->created_at,
            'trp_call_id' => $this->trp_call_id ?? '',
            'trp_is_expense_added' => $this->trp_is_expense_added ?? 0,
            'trp_is_details_added' => $this->trp_is_details_added ?? 0,
            'has_advance_log' => $hasAdvanceLog,
            'is_advance_approved' => $isAdvanceApproved,
            'trp_approval_log' => $this->fh_plan_approval_log ? ApprovalLogApiResource::collection($this->fh_plan_approval_log)->all() : [],
            'trp_next_approver_details'=> ApprovalHelper::getNextApprovalDetails($this->trp_module_id, $this->trp_id, $this->trp_b_id),
            'claim_details' => [[
                'da' => $totalDA ?? 0,
                'ta' => $totalTravelAmount ?? 0,
                'travel' => $travel ?? 0,
                'lodging' => $lodging ?? 0,
                'meal' => $meal ?? 0,
                'other' => $other ?? 0,
                'miscClaim' => $miscClaim ?? 0,
                'deviation' => $totalExpenseDeviation ?? 0,
            ]],
        ];

        return array_merge($resourceData, $editableStatus);
    }
}
