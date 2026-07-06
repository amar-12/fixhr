<?php

namespace App\Helpers;

use Exception;
use Carbon\Carbon;
use App\Models\CompOff;
use App\Models\Employee;
use App\Models\GatePass;
use App\Models\TadaClaim;
use App\Mail\ApprovalMail;
use App\Models\AdvanceLog;
use App\Models\ApprovalLog;
use App\Models\LoanRequest;
use App\Models\MasterTable;
use App\Models\DeductionLog;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Helpers\CentralLogics;
use App\Helpers\ShiftResolver;
use App\Models\ApprovalModule;
use App\Models\CompOffBalance;
use App\Models\ProcessApprover;
use App\Models\TadaRequestPlan;
use App\Models\AttendanceRecord;
use Illuminate\Support\Facades\DB;
use App\Models\ActionUponRejection;
use App\Models\AttendanceException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\EmployeeApprovalStatus;
use App\Models\PayrollLoanInstallment;
use App\Models\EmployeeApprovalMapping;
use App\Models\PolicyHolidayList;
use App\Models\OtApprovalStatus;
use App\Http\Requests\Approval\AdvanceLogRequest;
use App\Models\EmployeeExitRequest;
use App\Models\PolicyShiftTiming;
use App\Models\AutomationRule;
use App\Models\AttendanceOutDoor;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class ApprovalHelper
{
    public static function checkRuleCriteriaModule($isPlan, $exeTypeId, $travelType = 124, $trpId = 0, $amId = 0, $statusId = 140, $trpAdvanceAllowance = 0, $plan = null)
    {
        $user = Auth::user();
        try {
            if ($isPlan) {
                if ($plan->fh_policy_tada_travel_type->pttt_approval_type_id == 198) {
                    $approvalModule = ApprovalModule::with([
                        'fh_rule_criteria' => ['fh_approval_rule:m_id,m_name', 'fh_rule_condition:m_id,m_name', 'fh_condition_option:m_id,m_name'],
                        'fh_process_approvers' => [
                            'fh_employee:emp_id,emp_fname,emp_email,emp_fname'
                        ]
                    ])->where(['am_b_id' => $user->emp_b_id, 'am_id' => $amId, 'am_status' => 1])->first();
                    if ($approvalModule) {
                        if (in_array($exeTypeId, json_decode($approvalModule->am_exe_on))) {
                            $ruleMatched = self::checkRuleCriteria($statusId, $trpAdvanceAllowance, $approvalModule->fh_rule_criteria);
                            if ($ruleMatched) {
                                foreach ($approvalModule->fh_process_approvers as $approval) {
                                    if ($approval->fh_employee()->exists()) {
                                        // $mailData = [
                                        //     'subject' => 'Tour Request Approval',
                                        //     'url' => route('travel.request.show', ['id' => md5($plan->trp_id)]),
                                        //     'mail_type' => 'TOUR_APPROVAL_REQUEST',
                                        //     'data' => ['planData' => $plan, 'nextApproverName' => $approval->fh_employee->emp_fname],
                                        // ];

                                        if ($approval->pa_type == 'single' || $approval->pa_type == 'anyone' || ($approval->pa_type == 'and' && $approval->pa_sequence == 1)) {
                                            $placeholders = [
                                                '{travel_type}' =>  $plan->fh_policy_tada_travel_type->fh_travel_type->m_name,
                                                '{receiver_name}' => $approval->fh_employee->emp_full_name,
                                                '{request_id}'  => $plan->trp_unique_id,
                                                '{submit_date}' => Carbon::parse($plan->created_at)->format('d M, Y'),
                                                '{start_time}' =>  Carbon::parse($plan->trd_start_time)->format('h:i A'),
                                                '{end_date}' =>  Carbon::parse($plan->trp_end_date)->format('d M, Y'),
                                                '{end_time}' =>  Carbon::parse($plan->trd_end_time)->format('h:i A'),
                                                '{destination}' => $plan->trp_destination,
                                                '{travel_purpose}' => $plan->fh_travel_purpose->tp_name,
                                                '{employee_name}' => $plan->fh_employee->emp_full_name ?? 'N/A',
                                                '{employee_code}' => $plan->fh_employee->emp_code ?? 'N/A',
                                                '{employee_designation}' => $plan->fh_employee->fh_designation->dg_name ?? 'N/A',
                                                '{employee_phone}' => $plan->fh_employee->emp_phone ?? 'N/A',
                                            ];
                                            // The recipient's email address
                                            $recipientEmail = $approval->fh_employee->emp_email;
                                            // The email template type and business ID
                                            $templateType = 406; // Replace with your mail template type
                                            $businessId = $user->emp_b_id; // Replace with your business ID if applicable
                                            // Sending the email using the CentralLogics class
                                            $sent = CentralLogics::sendCustomEmail($templateType, $placeholders, $recipientEmail, $businessId);

                                            // CentralLogics::send_mail($approval->fh_employee->emp_email, new ApprovalMail($mailData));
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public static function checkRuleCriteria($statusId, $trpAdvanceAllowance, $rules)
    {

        try {
            $ruleMatched = false;
            foreach ($rules as $rule) {
                $ruleValue = isset($rule->fh_condition_option->m_id) ? $rule->fh_condition_option->m_id : $rule->rc_custom_value;

                switch ($rule->fh_approval_rule->m_id) {
                    case 131: //group=>APPROVAL_RULE, name=Request Status
                        $condition = $rule->fh_rule_condition->m_name;
                        if (
                            ($condition == "IS" && $statusId == $ruleValue) ||
                            ($condition == "IS NOT" && $statusId != $ruleValue)
                        ) {
                            $ruleMatched = true;
                        }
                        break;

                    case 132: //group=>APPROVAL_RULE, name=Advance
                        $expressionLanguage = new ExpressionLanguage();
                        $expression = $trpAdvanceAllowance . $rule->fh_rule_condition->m_name . $ruleValue;
                        $result = $expressionLanguage->evaluate($expression);
                        $ruleMatched = $result ? true : false;
                        break;
                }
            }
            return $ruleMatched;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public static function getApprovalOrRejectionData($requestId, $statusId, $moduleId, $approverId = null, $masterModuleId = null)
    {
        $user = Auth::user();
        if ($approverId) {
            $user = Employee::find($approverId);
        }
        $nextApprover = null;

        switch ($masterModuleId) {
            case 146:
                $model = TadaClaim::class;
                $fields = ['tc_next_approver as next_approver', 'tc_emp_id', 'tc_am_id', 'tc_trp_id', 'tc_stage_completed'];
                break;

            case 145:
                $model = TadaRequestPlan::class;
                $fields = ['trp_next_approver as next_approver', 'trp_emp_id', 'trp_am_id', 'trp_id', 'trp_stage_completed'];
                break;

            case 199:
                $model = AdvanceLog::class;
                $fields = ['adl_next_approver as next_approver', 'adl_approver_id', 'adl_am_id', 'adl_id', 'adl_stage_completed'];
                break;

            case 229:
                $model = AttendanceException::class;
                $fields = ['ae_next_approver as next_approver', 'ae_emp_id', 'ae_am_id', 'ae_id', 'ae_stage_completed'];
                break;

            case 339:
                $model = GatePass::class;
                $fields = ['gtp_next_approver as next_approver', 'gtp_emp_id', 'gtp_am_id', 'gtp_id', 'gtp_stage_completed'];
                break;

            case 250:
                $model = LeaveRequest::class;
                $fields = ['lvr_next_approver as next_approver', 'lvr_emp_id', 'lvr_am_id', 'lvr_id', 'lvr_stage_completed'];
                break;

            case 249:
                $model = AttendanceRecord::class;
                $fields = ['atd_next_approver as next_approver', 'atd_emp_id', 'atd_am_id', 'atd_id', 'atd_stage_completed'];
                break;

            case 442:
                $model = LoanRequest::class;
                $fields = ['lnr_next_approver as next_approver', 'lnr_emp_id', 'lnr_am_id', 'lnr_id', 'lnr_stage_completed'];
                break;

            case 562:
                $model = OtApprovalStatus::class;
                $fields = ['ot_next_approver as next_approver', 'ot_emp_id', 'ot_am_id', 'ot_id', 'ot_stage_completed'];
                break;
             case 603:
                $model = EmployeeExitRequest::class;
                $fields = ['er_next_approver as next_approver', 'er_emp_id', 'er_am_id', 'er_id', 'er_stage_completed'];
                break;

            case 604:
                $model = EmployeeExitRequest::class;
                $fields = ['er_next_approver as next_approver', 'er_emp_id', 'er_am_id', 'er_id', 'er_stage_completed'];
                break;

            case 605:
                $model = EmployeeExitRequest::class;
                $fields = ['er_next_approver as next_approver', 'er_emp_id', 'er_am_id', 'er_id', 'er_stage_completed'];
                break;

            case 606:
                $model = EmployeeExitRequest::class;
                $fields = ['er_next_approver as next_approver', 'er_emp_id', 'er_am_id', 'er_id', 'er_stage_completed'];
                break;

            case 589:
                $model = AttendanceOutDoor::class;
                $fields = ['atd_od_next_approver as next_approver', 'atd_od_emp_id', 'atd_od_am_id', 'atd_od_id', 'atd_od_stage_completed'];
                break;

            default:
                $fields = null;
                break;
        }

        if (!empty($fields)) {
            [$nextApproverField, $empField, $amField, $idField, $completedField] = $fields;

            // 🔥 SUPERADMIN DIRECT APPROVAL / REJECTION
            if (($user->emp_role_id == 1 || $user->is_approval_manager == 1) && ($masterModuleId == 229 || $masterModuleId == 249 || $masterModuleId == 250 || $masterModuleId == 339 || $masterModuleId == 562 || $masterModuleId == 589)) {

                // get the request record to check stage status
                $record = $model::select($completedField, $idField)
                    ->where($idField, $requestId)
                    ->first();

                // if record not found OR stage already completed → do not bypass
                if (!$record || $record->{$completedField} == 1) {
                    return null;
                }

                $super = new ProcessApprover();

                $super->pa_id        = 0; // dummy
                $super->pa_b_id      = $user->emp_b_id ?? null;
                $super->pa_am_id     = $moduleId;
                $super->pa_module_id = $masterModuleId;
                $super->pa_flow      = 'bypass';
                $super->pa_d_id      = $user->emp_d_id ?? null;
                $super->pa_type      = 'superadmin';
                $super->pa_sequence  = 1;
                $super->pa_role_id   = $user->emp_dg_id;
                $super->pa_emp_id    = $user->emp_id;
                $super->pa_last      = 1;
                $super->pa_status_id = 157; // 157 approve / 170 reject
                $super->pa_message   = 'Super Admin - Direct Action';
                $super->created_at   = now();
                $super->updated_at   = now();

                return $super;
            }

            $nextApprover = $model::with('fh_employee')
                ->selectRaw("$nextApproverField, $empField, $idField")
                ->where($amField, $moduleId)
                ->where($idField, $requestId)
                ->whereNot($completedField, 1)
                ->first();
        }

        $emp_d_id = $nextApprover ? optional($nextApprover->fh_employee)->emp_d_id : null;
        $approvalDataQuery = ProcessApprover::whereHas('fh_approval_module', function ($query) use ($moduleId) {
            $query->where('am_id', $moduleId);
        })->where('pa_emp_id', $user->emp_id)
            ->where(function ($query) use ($emp_d_id) {
                $query->where('pa_flow', 'business')
                    ->orWhere(function ($query) use ($emp_d_id) {
                        $query->where('pa_flow', 'department')
                            ->where('pa_d_id', $emp_d_id);
                    });
            });
        $approvalData = $approvalDataQuery->first();

        if ($approvalData && $nextApprover) {
            if (($approvalData->pa_type == 'and') && ($approvalData->pa_sequence == ($nextApprover->next_approver ?? 1))) {
                $approvalData = $approvalDataQuery->whereHas('fh_approval_module', function ($query) use ($user, $requestId, $statusId) {
                    $query->whereDoesntHave('fh_approval_logs', function ($query1) use ($user, $requestId, $statusId) {
                        $query1->where('log_request_id', $requestId)
                            ->where('log_status', $statusId)
                            ->where('log_user_id', $user->emp_id)
                            ->where('log_status', 170);
                    });
                })->first();
            } elseif (($approvalData->pa_type == 'single') || ($approvalData->pa_type == 'anyone')) {

                $approvalData = $approvalDataQuery->whereHas('fh_approval_module', function ($query) use ($requestId, $statusId) {
                    $query->whereDoesntHave('fh_approval_logs', function ($query1) use ($requestId, $statusId) {
                        $query1->where('log_request_id', $requestId)
                            ->where('log_status', $statusId)
                            ->where('log_status', 170);
                    });
                })->first();
            } else {
                $approvalData = null;
            }
        } else {
            $approvalData = null;
        }
        return $approvalData;
    }

	public static function isApprovalCompleted($b_id, $module_id, $request_id, $emp_d_id, $emp_id = null)
    {
        $approvalStages = ApprovalModule::where([
            'am_b_id' => $b_id,
            'am_module_id' => $module_id
        ])->get();

        $approvalCount = 0;
        $approvalLogCount = 0;

        if (count($approvalStages)) {
            // Hierarchy-wise approval
            foreach ($approvalStages as $stage) {
                $stageApprovers = $stage->filteredProcessApprovers($emp_d_id)->get();

                foreach ($stageApprovers as $approver) {
                    $log = ApprovalLog::where([
                        'log_request_id' => $request_id,
                        'log_am_id' => $approver->pa_am_id,
                        'log_status' => $approver->pa_status_id,
                        'log_user_id' => $approver->pa_emp_id
                    ])->first();

                    if ($approver->pa_type == 'and') {
                        if ($log) {
                            $approvalLogCount++;
                        }
                        $approvalCount++;
                    } else {
                        if ($log) {
                            $approvalLogCount++;
                        }
                        $approvalCount = 1; // Only 1 needed for OR
                    }
                }
            }
        } else {
            // Employee-wise approval
            $approvalMapping = ApprovalHelper::getApprovalMapping($b_id, $emp_id, $module_id);
            $getApprovalArray = ApprovalHelper::getApprovalArray($approvalMapping);

            foreach ($getApprovalArray as $empId) {
                $log = ApprovalLog::where([
                    'log_request_id' => $request_id,
                    'log_module_id' => $module_id,
                    'log_user_id' => $empId
                ])->first();

                if ($log) {
                    $approvalLogCount++;
                }
                $approvalCount++;
            }
        }

        return $approvalCount == $approvalLogCount ? true : false;
    }



    public static function checkApproval($b_id, $module_id, $request_id, $emp_d_id, $emp_id = null)
    {
        $approvalStages = ApprovalModule::where(['am_b_id' => $b_id, 'am_module_id' => $module_id])->get();

        $approvalCount = 0;
        $approvalLogCount = 0;
        if (count($approvalStages)) {
            foreach ($approvalStages as $stage) {
                $stage = $stage->filteredProcessApprovers($emp_d_id)->get();
                foreach ($stage as $approver) {
                    if ($approver->pa_type == 'and') {
                        $log = ApprovalLog::where(['log_request_id' => $request_id, 'log_am_id' => $approver->pa_am_id, 'log_status' => $approver->pa_status_id, 'log_user_id' => $approver->pa_emp_id])->first();
                        if ($log) {
                            $approvalLogCount++;
                        }
                        $approvalCount++;
                    } else {
                        $log = ApprovalLog::where(['log_request_id' => $request_id, 'log_am_id' => $approver->pa_am_id, 'log_status' => $approver->pa_status_id, 'log_user_id' => $approver->pa_emp_id])->first();
                        if ($log) {
                            $approvalLogCount++;
                        }
                        $approvalCount = 1;
                    }
                }
            }
        } else {
            $approvalMapping =  ApprovalHelper::getApprovalMapping($b_id, $emp_id, $module_id);
            $getApprovalArray = ApprovalHelper::getApprovalArray($approvalMapping);
            foreach ($getApprovalArray as $key => $item) {
                $log = ApprovalLog::where(['log_request_id' => $request_id, 'log_module_id' => $module_id,  'log_user_id' => $item])->first();
                if ($log) {
                    $approvalLogCount++;
                }
                $approvalCount++;
            }
        }
        $trueOrfalse = $approvalCount == $approvalLogCount;

        return [
            'trueorfalse' => $trueOrfalse,
            'approvalcount' => $approvalCount,
            'approvallog_count' => $approvalLogCount
        ];
    }


    public static function getEditableStatus($b_id, $module_id, $request_id, $status_id, $is_auto_approval, $emp_d_id, $emp_id)
    {

        $editableStatus = ['is_trp_plan_editable' => false, 'is_trp_detail_editable' => false, 'is_trp_expense_editable' => false, 'is_trp_claimable' => false];

        //common for Auto and Manual Approval
        if (in_array($status_id, [139, 192])) { //139=='Pending, 192=='Recall'
            $editableStatus['is_trp_plan_editable'] = true;
            $editableStatus['is_trp_detail_editable'] = false;
            $editableStatus['is_trp_expense_editable'] = false;
            $editableStatus['is_trp_claimable'] = false;
        } else {
            if ($is_auto_approval) { //Auto Approval
                if ($status_id == 171) { //171=='Auto Approved'
                    $editableStatus['is_trp_plan_editable'] = false;
                    $editableStatus['is_trp_detail_editable'] = true;
                    $editableStatus['is_trp_expense_editable'] = true;
                    $editableStatus['is_trp_claimable'] = true;
                }
            } else { // Manual Approval
                if (in_array($status_id, [140])) { // 140 == 'Requested'
                    $editableStatus['is_trp_plan_editable'] = false;
                    $editableStatus['is_trp_detail_editable'] = false;
                    $editableStatus['is_trp_expense_editable'] = false;
                    $editableStatus['is_trp_claimable'] = false;
                } elseif (ApprovalHelper::isApprovalCompleted($b_id, $module_id, $request_id, $emp_d_id, $emp_id)) {
                    $editableStatus['is_trp_plan_editable'] = false;
                    $editableStatus['is_trp_detail_editable'] = true;
                    $editableStatus['is_trp_expense_editable'] = true;
                    $editableStatus['is_trp_claimable'] = true;
                }
            }
        }



        return $editableStatus;
    }

    public static function getNextApprovalDetails($type, $id, $bid)
    {
        $result = ['data' => null, 'message' => '', 'approver_name' => '', 'approver_id' => null];

        $am_field = NULL;
        $requesterUserId = NULL;
        // Determine the appropriate request model based on type
        if ($type == 146) { // Claim
            $request = TadaClaim::with(['fh_process_approvers.fh_employee', 'fh_deduction_log'])
                ->where('tc_b_id', $bid)
                ->where('tc_id', $id)
                ->first();
            $statusField = 'tc_status';
            $stageField = 'tc_stage_completed';
            $am_field = 'tc_am_id';
            $requesterUserId = $request->tc_emp_id;
        } elseif ($type == 145) { // Travel
            $request = TadaRequestPlan::with(['fh_process_approvers.fh_employee', 'fh_policy_tada_travel_type'])
                ->where('trp_b_id', $bid)
                ->where('trp_id', $id)
                ->first();
            $statusField = 'trp_request_status';
            $stageField = 'trp_stage_completed';
            $am_field = 'trp_am_id';
            $requesterUserId = $request->trp_emp_id;
        } elseif ($type == 229) { // Mis-Punch
            $request = AttendanceException::with(['fh_process_approvers.fh_employee'])
                ->where('ae_b_id', $bid)
                ->where('ae_id', $id)
                ->first();
            $statusField = 'ae_status';
            $stageField = 'ae_stage_completed';
            $am_field = 'ae_am_id';
            $requesterUserId = $request->ae_emp_id;
        } elseif ($type == 339) { // GatePass
            $request = GatePass::with(['fh_process_approvers.fh_employee'])
                ->where('gtp_b_id', $bid)
                ->where('gtp_id', $id)
                ->first();
            $statusField = 'gtp_status';
            $stageField = 'gtp_stage_completed';
            $am_field = 'gtp_am_id';
            $requesterUserId = $request->gtp_emp_id;
        }elseif ($type == 442) { // Loan
            $request = LoanRequest::with(['fh_process_approvers.fh_employee'])
                ->where('lnr_b_id', $bid)
                ->where('lnr_id', $id)
                ->first();
            $statusField = 'lnr_request_status';
            $stageField = 'lnr_stage_completed';
            $am_field = 'lnr_am_id';
            $requesterUserId = $request->lnr_emp_id;
        } elseif ($type == 250) { // Leave
            $request = LeaveRequest::with(['fh_process_approvers.fh_employee'])
                ->where('lvr_b_id', $bid)
                ->where('lvr_id', $id)
                ->first();
            $statusField = 'lvr_status';
            $stageField = 'lvr_stage_completed';
            $am_field = 'lvr_am_id';
            $requesterUserId = $request->lvr_emp_id;
        } elseif ($type == 562) { // Overtime
            $request = OtApprovalStatus::with(['fh_process_approvers.fh_employee'])
                ->where('ot_b_id', $bid)
                ->where('ot_id', $id)
                ->first();
            $statusField = 'ot_requested_status';
            $stageField = 'ot_stage_completed';
            $am_field = 'ot_am_id';
            $requesterUserId = $request->ot_emp_id;
        } elseif ($type == 199) { // Advance
            $request = AdvanceLog::with(['fh_process_approvers.fh_employee', 'fh_tada_request_plan.fh_employee'])
                ->where('adl_id', $id)
                ->first();
            $request->fh_employee = $request->fh_tada_request_plan->fh_employee;
            $statusField = 'adl_request_status';
            $stageField = 'adl_stage_completed';
            $am_field = 'adl_am_id';
            $requesterUserId = $request?->fh_tada_request_plan?->trp_emp_id;

        } elseif ($type == 249) { // Attendance

            $request = AttendanceRecord::with(['fh_process_approvers.fh_employee', 'fh_employee'])
                ->where('atd_b_id', $bid)
                ->where('atd_id', $id)
                ->first();
            $statusField = 'atd_request_status';
            $stageField = 'atd_stage_completed';
            $am_field = 'atd_am_id';
            $requesterUserId = $request->atd_emp_id;
        }
        
        elseif ($type == 603) { // FNF Manager Review
            $request = EmployeeExitRequest::with(['fh_process_approvers.fh_employee', 'fh_employee'])
                ->where('er_b_id', $bid)
                ->where('er_id', $id)
                ->first();
            $statusField = 'er_request_status';
            $stageField = 'er_stage_completed';
            $am_field = 'er_am_id';
            $requesterUserId = $request->er_emp_id;
        } elseif ($type == 604) { // FNF HR Review
            $request = EmployeeExitRequest::with(['fh_process_approvers.fh_employee', 'fh_employee'])
                ->where('er_b_id', $bid)
                ->where('er_id', $id)
                ->first();
            $statusField = 'er_request_status';
            $stageField = 'er_stage_completed';
            $am_field = 'er_am_id';
            $requesterUserId = $request->er_emp_id;
        } elseif ($type == 605) { // FNF Fiance Review
            $request = EmployeeExitRequest::with(['fh_process_approvers.fh_employee', 'fh_employee'])
                ->where('er_b_id', $bid)
                ->where('er_id', $id)
                ->first();
            $statusField = 'er_request_status';
            $stageField = 'er_stage_completed';
            $am_field = 'er_am_id';
            $requesterUserId = $request->er_emp_id;
        } elseif ($type == 606) { // FNF Admin Review
            $request = EmployeeExitRequest::with(['fh_process_approvers.fh_employee', 'fh_employee'])
                ->where('er_b_id', $bid)
                ->where('er_id', $id)
                ->first();
            $statusField = 'er_request_status';
            $stageField = 'er_stage_completed';
            $am_field = 'er_am_id';
            $requesterUserId = $request->er_emp_id;
        } elseif ($type == 589) { // Outdoor Attendance
            $request = AttendanceOutDoor::with(['fh_process_approvers.fh_employee', 'fh_employee'])
                ->where('atd_od_b_id', $bid)
                ->where('atd_od_id', $id)
                ->first();
            $statusField   = 'atd_od_request_status';
            $stageField    = 'atd_od_stage_completed';
            $am_field      = 'atd_od_am_id';
            $requesterUserId = $request->atd_od_emp_id;
        }
        
        
        
        else {
            $result['message'] = 'Invalid request type';
            return $result;
        }
        // Check if request exists
        if (!$request) {
            $result['message'] = 'No request found.';
            return $result;
        }

        // Handle common checks for completion, rejection, and auto-approval (for travel)
        if ($request->$stageField == 1) {
            $result['message'] = 'Completed';
            return $result;
        }
        if ($request->$statusField == 170) {
            $result['message'] = 'Request is rejected.';
            return $result;
        }
        if (isset($request->fh_policy_tada_travel_type) && $request->fh_policy_tada_travel_type->pttt_approval_type_id == 197) {
            $result['message'] = 'Auto Approved';
            return $result;
        }

        // Handle deduction log specifically for claims
        if ($type == 146 && $request->fh_deduction_log) {
            $deductionLog = $request->fh_deduction_log->sortByDesc('dlog_id')->first();
            if ($deductionLog) {
                if (is_null($deductionLog->dlog_requester_action)) {
                    $result['message'] = 'To Requester';
                    $result['data'] = 1;
                    return $result;
                } elseif ($deductionLog->dlog_requester_action === 0) {
                    $result['message'] = 'Claim was declined.';
                    return $result;
                }
            }
        }

        if($request->$am_field){// for hierarchy  wise approval flow
             // Get the next approver
            $approverIds = $request->filteredProcessApprovers($request->fh_employee?->emp_d_id) ? $request->filteredProcessApprovers($request->fh_employee?->emp_d_id)->pluck('pa_emp_id') : collect();
            $logUserIds = $type == 146 ? ($request->fh_claim_approval_log ? $request->fh_claim_approval_log->pluck('log_user_id') : collect()) : ($request->fh_plan_approval_log ? $request->fh_plan_approval_log->pluck('log_user_id') : collect());
            $nextApprover = self::getNextApprover($approverIds, $logUserIds, $request);
            if ($nextApprover) {
                $result['approver_name'] = $nextApprover->emp_full_name;
                $result['data'] = 1;
                $result['approver_id'] = $nextApprover->emp_id;
            } else {
                $result['message'] = 'No next approval data available.';
            }

        }else{// for employee wise approval flow
            $approvalMapping = ApprovalHelper::getApprovalMapping($bid, $requesterUserId, $type);
            if ($approvalMapping) {
                $approvalArray = ApprovalHelper::getApprovalArray($approvalMapping);
                $approvalLog = ($type == 146) ? $request->fh_approval_log_employee_wise?->pluck('log_user_id')?->toArray() : $request->fh_approval_log2?->pluck('log_user_id')?->toArray();

                $diff = array_values(array_diff($approvalArray, $approvalLog));
                if ($diff) {
                    $result['approver_name'] = Employee::where('emp_id',$diff[0])->pluck('emp_full_name')->first() ??  '---';
                    $result['data'] = 1;
                    $result['approver_id'] = $diff[0];
                }else {
                    $result['message'] = 'No next approval data available.';
                }
            }
        }

        return $result;
    }

    private static function getNextApprover($approverIds, $logUserIds, $request)
    {
        // Get the difference in user IDs and return the first missing approver
        $difference = $approverIds->diff($logUserIds);
        $firstDifference = $difference->first();

        if ($firstDifference) {
            return $request->fh_process_approvers->firstWhere('pa_emp_id', $firstDifference)->fh_employee ?? null;
        }
        return null;
    }

    public static function getApprovalMapping($bId, $empId, $moduleId)
    {
        return EmployeeApprovalMapping::where('eam_b_id', $bId)
            ->where('eam_emp_id', $empId)
            ->where('eam_module_id', $moduleId)
            ->first();
    }

    public static function getApprovalArray($approvalMapping)
    {
        $approvalMappingArray = [];
        if($approvalMapping?->approvalStatuses){
            foreach($approvalMapping->approvalStatuses as $approveStatus){
                $approvalMappingArray[] = $approveStatus['eas_approvel_id'];
            }
        }
        return $approvalMappingArray;
    }

    public static function canApprove($approvalMappingArray, $approvalLog, $userId)
    {
        $diff = array_values(array_diff($approvalMappingArray, $approvalLog));
        return isset($diff[0]) && $diff[0] == $userId;
    }

    public static function getApprovalData($data, $user, $prefix)
    {
        $masterApproveBtn = [];
        $canApprove = false;
        if (!$data->approvalData && $data->{$prefix . 'stage_completed'} != 1) {
            $approvalStatuses = [170];
            $moduleId = $prefix . 'module_id';
            $employeId = $prefix . 'emp_id';

            if ($data->$moduleId == 199) {
                $mapData = EmployeeApprovalMapping::where(['eam_module_id' => $data->$moduleId, 'eam_emp_id' => $data->fh_tada_request_plan->trp_emp_id])->first();
                $approvalMapping = self::getApprovalMapping($user->emp_b_id, $data->fh_tada_request_plan->trp_emp_id, $data->$moduleId);
            } else {
                $mapData = EmployeeApprovalMapping::where(['eam_module_id' => $data->$moduleId, 'eam_emp_id' => $data->$employeId])->first();
                $approvalMapping = self::getApprovalMapping($user->emp_b_id, $data->$employeId, $data->$moduleId);
            }
            if($mapData){
                $statusData = $mapData->approvalStatuses->where('eas_approvel_id',$user->emp_id)->pluck('eas_approvel_status')->toArray();
                $approvalStatuses = array_merge($approvalStatuses, $statusData);
                $masterApproveBtn = MasterTable::whereIn('m_id', $approvalStatuses)->orderBy('m_id', 'desc')->get();
            }

            $requesterActPending = [];
            if ($approvalMapping) {
                $logMethod = ($data->$moduleId == 146) ? 'fh_approval_log_employee_wise' : 'fh_approval_log2';
                $logData = $data?->$logMethod('orderBy', 'desc')->first();
                // $requesterActPending = $logData?->fh_deductionLog?->orderBy('dlog_id', 'desc')->first();
                if($data->$moduleId == 146){
                    // Get the latest deduction log directly from the claim
                    $requesterActPending = $data->fh_deductionLog()->orderBy('dlog_id', 'desc')->first();
                }

                if ($requesterActPending && $requesterActPending->dlog_requester_action == 0) {
                    $canApprove = $requesterActPending->dlog_user_id == $user->emp_id;
                } else {
                    $approvalMappingArray = self::getApprovalArray($approvalMapping);
                    $approvalLog = $data?->$logMethod?->pluck('log_user_id')?->toArray() ?? [];
                    $canApprove = self::canApprove($approvalMappingArray, $approvalLog, $user->emp_id);
                }
            }
        }

        return compact('masterApproveBtn', 'canApprove');
    }

    public static function processApproval($request, $data, $user, $prefix)
    {
        // Start the transaction
        DB::beginTransaction();
        try {
            if ($data->{$prefix . 'module_id'} == 199) { // advance
                $approvalMapping = self::getApprovalMapping($user->emp_b_id, $data->fh_tada_request_plan->trp_emp_id, $data->{$prefix . 'module_id'});
            } elseif ($data->{$prefix . 'module_id'} == 146) { // 146==Claim

                //start check the deduction amount must be less than payable amount
                $deductionInfo = $request->deduction_info ?? [];
                if ($data && $data->fh_deduction_log->where('dlog_requester_action', 1)->isNotEmpty()) {
                    // Iterate through deduction logs where requester action is 1
                    foreach ($data->fh_deduction_log->where('dlog_requester_action', 1) as $deduction_log) {
                        $additionalInfo = json_decode($deduction_log->dlog_additional_info, true);

                        // Merge additional info into deduction info
                        foreach ($additionalInfo as $logKey => $log) {
                            $deductionInfo[$logKey] = isset($deductionInfo[$logKey])
                                ? $deductionInfo[$logKey] + $log
                                : $log; // Initialize if not set
                        }
                    }
                }
                // Validate deduction info against payable amount
                if (!empty($deductionInfo) && isset($request->payable_amount)) {
                    foreach ($deductionInfo as $key => $deductionAmount) {
                        // Ensure payable amount exists for the key
                        if (isset($request->payable_amount[$key])) {
                            if ($request->payable_amount[$key] < $deductionAmount) {

                                return ['status' => 'error', 'message' => 'Deduction amount cannot be greater than the payable amount', 'data' => ''];
                            }
                        }
                    }
                }
                //end check the deduction amount must be less than payable amount

                $approvalMapping = self::getApprovalMapping($user->emp_b_id, $data->{$prefix . 'emp_id'}, $data->{$prefix . 'module_id'});
            } else {
                $approvalMapping = self::getApprovalMapping($user->emp_b_id, $data->{$prefix . 'emp_id'}, $data->{$prefix . 'module_id'});
            }
            $approvalArray = $approvalMapping ? self::getApprovalArray($approvalMapping) : null;
            $lastApprover = $approvalArray ? end($approvalArray) : null;

            $stageComplete = ($request->log_status == 170 || $lastApprover == $user->emp_id) ? 1 : 0;
            if (($data->{$prefix . 'module_id'} == 145) || ($data->{$prefix . 'module_id'} == 249) || ($data->{$prefix . 'module_id'} == 199)) {
                $data->update([
                    $prefix . 'request_status' => $request->log_status,
                    $prefix . 'stage_completed' => $stageComplete,
                ]);
            } else {

                if ($data->{$prefix . 'module_id'} == 250) {
                    $sandwichLeave = LeaveRequest::where('lvr_p_id', $data->lvr_id)->first();
                    if ($sandwichLeave) {
                        $sandwichLeave->update(['lvr_status' => $request->log_status, 'lvr_stage_completed' => $stageComplete, 'lvr_approved_by' => Auth::user()->emp_id]);
                    }
                }

                // Manage leave balance for each rejected leave request
                if ($data->{$prefix . 'module_id'} == 250 && $request->log_status == 170) {
                    if ($data->lvr_is_comp_off) {
                        $compOffBalance = CompOffBalance::where('cb_emp_id', $data->lvr_emp_id)->where('cb_b_id', $user->fh_business->b_id)
                            ->where('cb_year', now()->year)
                            ->where('cb_month', now()->month)
                            ->first();

                        if ($compOffBalance) {
                            $exist_cb_taken = $compOffBalance->cb_taken - $data->lvr_total_leave_days;
                            $exist_cb_balance_remaining = $compOffBalance->cb_balance_remaining + $data->lvr_total_leave_days;

                            $compOffBalance->cb_taken = $exist_cb_taken;
                            $compOffBalance->cb_balance_remaining = $exist_cb_balance_remaining;
                            $compOffBalance->save();
                        }
                    } else {
                        $leaveBalance = LeaveBalance::where([
                            ['lb_emp_id', '=', $data->lvr_emp_id],
                            ['lb_cat_type_id', '!=', 215],
                            ['lb_cat_type_id', '=', $data->lvr_cat_type_id],
                            ['lb_b_id', '=', $user->fh_business->b_id],
                            ['lb_month', '=', now()->month],
                            ['lb_year', '=', now()->year],
                        ])
                            ->first();

                        if ($leaveBalance) {
                            // $exist_lb_taken_leave = $leaveBalance->lb_taken_leave - $data->lvr_total_leave_days;
                            // $exist_lb_balance_remaining_leave = $leaveBalance->lb_balance_remaining_leave + $data->lvr_total_leave_days;

                            // $leaveBalance->lb_taken_leave = $exist_lb_taken_leave;
                            // $leaveBalance->lb_balance_remaining_leave = $exist_lb_balance_remaining_leave;
                            // $leaveBalance->save();

                            $exist_lb_taken_leave = $leaveBalance->lb_taken_leave - $data->lvr_total_leave_days;

                            $leaveBalance->lb_taken_leave = max(0, $exist_lb_taken_leave);

                            // Leave month/year
                            $leaveMonth = date('m', strtotime($data->lvr_start_date));
                            $leaveYear  = date('Y', strtotime($data->lvr_start_date));

                            // Current month/year
                            $currentMonth = now()->month;
                            $currentYear  = now()->year;

                            // Previous month leave
                            if (
                                $leaveYear < $currentYear ||
                                ($leaveYear == $currentYear && $leaveMonth < $currentMonth)
                            ) {

                                // Add in carried forward
                                $leaveBalance->lb_carried_forward =
                                    ($leaveBalance->lb_carried_forward ?? 0) +
                                    $data->lvr_total_leave_days;

                            } else {

                                // Add in remaining leave
                                $leaveBalance->lb_balance_remaining_leave =
                                    $leaveBalance->lb_balance_remaining_leave +
                                    $data->lvr_total_leave_days;
                            }

                            $leaveBalance->save();
                        }

                        if ($sandwichLeave) {
                            $sandwichLB = LeaveBalance::where([
                                ['lb_emp_id', '=', $sandwichLeave->lvr_emp_id],
                                ['lb_cat_type_id', '!=', 215],
                                ['lb_cat_type_id', '=', $sandwichLeave->lvr_cat_type_id],
                                ['lb_b_id', '=', $user->fh_business->b_id],
                                ['lb_month', '=', now()->month],
                                ['lb_year', '=', now()->year],
                            ])->first();

                            if ($sandwichLB) {
                                // $exist_lb_taken_leave = $sandwichLB->lb_taken_leave - $data->lvr_total_leave_days;
                                // $exist_lb_balance_remaining_leave = $sandwichLB->lb_balance_remaining_leave + $data->lvr_total_leave_days;

                                // $sandwichLB->lb_taken_leave = $exist_lb_taken_leave;
                                // $sandwichLB->lb_balance_remaining_leave = $exist_lb_balance_remaining_leave;
                                // $sandwichLB->save();

                                $exist_lb_taken_leave = $sandwichLB->lb_taken_leave - $data->lvr_total_leave_days;

                                $sandwichLB->lb_taken_leave = max(0, $exist_lb_taken_leave);

                                // Leave month/year
                                $leaveMonth = date('m', strtotime($sandwichLeave->lvr_start_date));
                                $leaveYear  = date('Y', strtotime($sandwichLeave->lvr_start_date));

                                // Current month/year
                                $currentMonth = now()->month;
                                $currentYear  = now()->year;

                                // Previous month leave
                                if (
                                    $leaveYear < $currentYear ||
                                    ($leaveYear == $currentYear && $leaveMonth < $currentMonth)
                                ) {

                                    // Add in carried forward
                                    $sandwichLB->lb_carried_forward_leave =
                                        ($sandwichLB->lb_carried_forward ?? 0) +
                                        $data->lvr_total_leave_days;

                                } else {

                                    // Add in remaining leave
                                    $sandwichLB->lb_balance_remaining_leave =
                                        $sandwichLB->lb_balance_remaining +
                                        $data->lvr_total_leave_days;
                                }

                                $sandwichLB->save();
                            }
                        }
                    }
                }

                if ($data->{$prefix . 'module_id'} == 250 && $request->log_status != 170 && $stageComplete) {
                    // If the leave is of type 'Half Day' and the start date is today or in the past, reset late/early exit flags
                    if ($data->lvr_leave_day_type_id == 202 && Carbon::parse($data->lvr_start_date)->lte(now())) {
                        $attendance = AttendanceRecord::where('atd_emp_id', $data->lvr_emp_id)
                            ->where('atd_date', '=', $data->lvr_start_date)
                            ->where('atd_b_id', $data->lvr_b_id)
                            ->first();
                        $resolvedShift = ShiftResolver::resolveEmployeeShift($data->fh_employee, $data->lvr_start_date);
                        $shift = $resolvedShift ? $resolvedShift->shift : $attendance->fh_policy_shift_timing;

                        if ($attendance && $data->lvr_day_segment_id == 235) {
                            if ($shift->pst_allow_break1 && $shift->pst_break_end_time1 && $shift->pst_is_break_paid) {
                                $shiftStart = Carbon::parse($attendance->atd_date->format('Y-m-d') . " " . $shift->pst_break_end_time1->format('H:i:s'));
                            } else {
                                $shiftStart = Carbon::parse($attendance->atd_date->format('Y-m-d') . " " . $shift->pst_start_time->format('H:i:s'))->subHours(4);
                            }

                            // Reset late check-in if the shift start time is before or equal to the check-in time
                            if ($shiftStart && (Carbon::parse($attendance->atd_check_in_time))->lte($shiftStart)) {
                                $attendance->atd_is_late = $attendance->atd_late_duration = 0;
                            } else {
                                $late = Carbon::parse($attendance->atd_check_in_time)->diffInMinutes($shiftStart);
                                $attendance->atd_is_late = 1;
                                $attendance->atd_late_duration = $late ?? 0;
                            }
                            $attendance->update();
                        } else if ($attendance && $data->lvr_day_segment_id == 236) {
                            if ($shift->pst_allow_break1 && $shift->pst_break_begin_time1 && $shift->pst_is_break_paid) {
                                $shiftEnd = Carbon::parse($attendance->atd_date->format('Y-m-d') . " " . $shift->pst_break_begin_time1->format('H:i:s'));
                            } else {
                                $shiftEnd = Carbon::parse($attendance->atd_date->format('Y-m-d') . " " . $shift->pst_end_time->format('H:i:s'))->addHours(4);
                            }

                            // Reset early exit if the shift end time is before or equal to the check-out time
                            if ($shiftEnd && $shiftEnd->lte(Carbon::parse($attendance->atd_check_out_time))) {
                                $attendance->atd_is_early_exit = $attendance->atd_early_exit_duration = 0;
                            } else {
                                $earlyExit = Carbon::parse($attendance->atd_check_out_time)->diffInMinutes($shiftEnd);
                                $attendance->atd_is_early_exit = 1;
                                $attendance->atd_early_exit_duration = $earlyExit ?? 0;
                            }
                            $attendance->update();
                        }
                    }
                }

                if ($data->{$prefix . 'module_id'} == 229 && $request->log_status != 170 && $stageComplete) {
                    $resolvedShift = ShiftResolver::resolveEmployeeShift($data->fh_employee, $data->ae_date);
                    $shift = $resolvedShift ? $resolvedShift->shift ?? $data->fh_employee->fh_shift_type : $data->fh_employee->fh_shift_type;
                    $shiftStartTime = $shift->pst_start_time;
                    $shiftEndTime = $shift->pst_end_time;
                    $graceMins = $shift->pst_allow_grace_time ? $shift->pst_grace_time : 0;
                    $shiftStartTime = $shiftStartTime->subMinutes($graceMins);
                    $dailyWorkingHours = $shiftStartTime->diffInMinutes($shiftEndTime);
                    $checkInTime = Carbon::parse($data->ae_date->format('Y-m-d') . ' ' . $data->ae_in_time);
                    $checkOutTime =  Carbon::parse($data->ae_date->format('Y-m-d') . ' ' . $data->ae_out_time);
                    $workedDuration = $checkInTime->diffInMinutes($checkOutTime);
                    $fullDayThreshold = $dailyWorkingHours;
                    $minWorkHrs = $shift->pst_min_work_hour ? $shiftStartTime->diffInMinutes(Carbon::parse($shift->pst_min_work_hour)) : 0;
                    $halfDayThreshold = $dailyWorkingHours / 2;

                    // Check if current day is a weekly off day
                    $isWeeklyOff = CentralLogics::getWeekOffDatesReport($data->fh_employee, null, null, Carbon::parse($data->ae_date)->format('Y-m-d'), Carbon::parse($data->ae_date)->format('Y-m-d'));
                    // Check if current day is a holiday
                    $isHoliday = PolicyHolidayList::where('phl_b_id', $data->fh_employee->emp_b_id)
                        ->whereDate('phl_start_date', '<=', $data->ae_date)
                        ->whereDate('phl_end_date', '>=', $data->ae_date)
                        ->where('phl_type_id', 206)
                        ->exists();
                    // Handle holiday/week off attendance
                    if ($isWeeklyOff || $isHoliday) {
                        $atd_status = $isWeeklyOff ? 320 : 319; // Weekly Off Work : Holiday Work
                        $request->log_status != 170 && $stageComplete && CentralLogics::generateCompOff($data->fh_employee, $data->ae_date, 1);
                    }

                    if ($workedDuration >= $fullDayThreshold || $workedDuration >= $minWorkHrs) {
                        $atd_status = 251; // Present
                    } else if ($workedDuration >= $halfDayThreshold) {
                        $atd_status = 252; // Half Day
                    } else {
                        $atd_status = 203; // Absent
                    }

                    $isLate = $checkInTime->greaterThan($shiftStartTime) ? 1 : 0;
                    $isEarlyExit = $checkOutTime->lessThan($shiftEndTime) ? 1 : 0;

                    $isLate = $checkInTime->greaterThan($shiftStartTime) ? 1 : 0;
                    $isEarlyExit = $checkOutTime->lessThan($shiftEndTime) ? 1 : 0;

                    // Check if current day is a weekly off day
                    $isWeeklyOff = CentralLogics::getWeekOffDatesReport($data->fh_employee, null, null, Carbon::parse($data->ae_date)->format('Y-m-d'), Carbon::parse($data->ae_date)->format('Y-m-d'));

                    // Check if current day is a holiday
                    $isHoliday = PolicyHolidayList::where('phl_b_id', $data->fh_employee->emp_b_id)
                        ->whereDate('phl_start_date', '<=', $data->ae_date)
                        ->whereDate('phl_end_date', '>=', $data->ae_date)
                        ->where('phl_type_id', 206)
                        ->exists();

                    // Handle holiday/week off attendance
                    if ($isWeeklyOff || $isHoliday) {
                        $atd_status = $isWeeklyOff ? 320 : 319; // Weekly Off Work : Holiday Work
                        $request->log_status != 170 && $stageComplete && CentralLogics::generateCompOff($data->fh_employee, $data->ae_date, 1);
                    }

                    // Update attendance record if conditions are met
                    AttendanceRecord::where([
                        ['atd_b_id', '=', $data->ae_b_id],
                        ['atd_date', '=', $data->ae_date],
                        ['atd_emp_id', '=', $data->ae_emp_id],
                    ])->update(['atd_is_late' => $isLate, 'atd_is_early_exit' => $isEarlyExit]);

                    $data->update(['ae_attendance_status' => $atd_status, 'ae_approved_by' => Auth::user()->emp_id]);
                }

                // --- CASE: Loan Request Approval ---
                if ($data->{$prefix . 'module_id'} == 442) {  // Loan Request
                    if ($stageComplete && $request->log_status != 170) {  // Approved
                        $loan = LoanRequest::with([
                            'fh_employee' => function ($q) {
                                $q->select(
                                    'emp_id',
                                    'emp_fname',
                                    'emp_full_name',
                                    'emp_email',
                                    'emp_d_id',
                                    'emp_date_of_joining',
                                    'emp_dob'
                                )->with('fh_employee_salary');
                            }
                        ])->where('lnr_id', $data->lnr_id)->first();

                        // Validate against payroll settings
                        // $check = PayrollLogics::validateLoanAgainstSettings($loan);
                        // if (!$check['status']) {
                        //     return ['status' => 'error', 'message' => $check['message']];
                        // }

                        // Check if active loan already exists
                        $existingLoan = LoanRequest::where('lnr_emp_id', $loan->lnr_emp_id)
                            ->where('lnr_b_id', $loan->lnr_b_id)
                            ->whereIn('lnr_status', ['approved', 'pending'])
                            ->where('lnr_id', '!=', $loan->lnr_id)
                            ->exists();

                        if ($existingLoan) {
                            return ['status' => 'error', 'message' => 'Employee already has an ongoing or pending loan'];
                        }

                        // Update Loan as Approved
                        $loan->update([
                            'lnr_rate' => $request->rate ?? 0,
                            'lnr_status' => 'approved',
                        ]);

                        // Create Installments
                        $startDate = Carbon::parse($loan->lnr_start_date)->startOfMonth();
                        $openingBalance = $loan->lnr_requested_amount;

                        if (!empty($request->installments_json)) {
                            $installments = json_decode($request->installments_json, true);

                            foreach ($installments as $installment) {
                                // Due date calculate
                                $dueDate = $startDate->copy()->addMonths(($installment['number'] ?? 1) - 1);

                                PayrollLoanInstallment::updateOrCreate(
                                    [
                                        'pli_loan_id'        => $loan->lnr_id,
                                        'pli_installment_no' => $installment['number'],
                                    ],
                                    [
                                        'pli_b_id'            => $loan->lnr_b_id,
                                        'pli_opening_balance' => $installment['opening_balance'] ?? 0,
                                        'pli_amount'          => $installment['emi_amount'] ?? 0,
                                        'pli_principal'       => $installment['principal'] ?? 0,
                                        'pli_interest'        => $installment['interest'] ?? 0,
                                        'pli_rem_bal'         => $installment['outstanding_balance'] ?? 0,
                                        'pli_due_date'        => $dueDate->format('Y-m-d'),
                                        'pli_month'           => $dueDate->month,
                                        'pli_year'            => $dueDate->year,
                                        'pli_status'          => 'pending',
                                    ]
                                );
                            }
                        }
                    }
                    // --- CASE: Loan Request Rejection ---
                    elseif ($request->log_status == 170) {
                        $loan = LoanRequest::where('lnr_id', $data->lnr_id)->first();

                        if ($loan) {
                            $loan->update([
                                'lnr_status' => 'rejected',
                                'lnr_rate'   => $request->rate ?? 0,
                            ]);
                        }

                        // Agar installments already create ho gayi thi (rare case), to delete kar do
                        PayrollLoanInstallment::where('pli_loan_id', $data->lnr_id)->delete();
                    }
                }


                if ($data->{$prefix . 'module_id'} == 442) {  // Loan Request case
                    $data->update([
                        'lnr_request_status'  => $request->log_status,
                        'lnr_stage_completed' => $stageComplete,
                    ]);
                } else {
                    // $data->update([
                    //     $prefix . 'status'          => $request->log_status,
                    //     $prefix . 'stage_completed' => $stageComplete,
                    // ]);

                    // Prepare update data
                    $updateData = [
                        $prefix . 'status'          => $request->log_status,
                        $prefix . 'stage_completed' => $stageComplete,
                        $prefix . 'approved_by' => Auth::user()->emp_id,
                    ];

                    // For claims module (146), update next approver if not completed
                    if ($data->{$prefix . 'module_id'} == 146 && !$stageComplete && $nextApprover) {
                        $updateData['tc_next_approver'] = $nextApprover;
                    }
                    $data->update($updateData);
                }
            }

            if ($data->{$prefix . 'module_id'} == 199 && $stageComplete) { // advance
                $plan = TadaRequestPlan::where('trp_id', $request->adl_trp_id)->first();
                if ($plan && $request->log_status != 170) {
                    $plan->update(['trp_advance_allowance' => $plan->trp_advance_allowance
                        ? ($plan->trp_advance_allowance + $request->reimburse_amount)
                        : $request->reimburse_amount]);
                }

                $data->update([
                    $prefix . 'reimburse_amount' => $request->reimburse_amount
                ]);
            }

            if ($data->{$prefix . 'module_id'} == 249 && $stageComplete && $request->log_status != 170) {
                CentralLogics::generateCompOff($data->fh_employee, $data->atd_date);
            }

            // if ($data->{$prefix . 'module_id'} == 562 && $stageComplete && $request->log_status != 170) { // OverTime
            if ($data->{$prefix . 'module_id'} == 562) { // OverTime
                $data->update(['ot_requested_status' => $request->log_status, 'ot_next_approver' => 1, 'ot_stage_completed' => $stageComplete, 'ot_approved_by' => $user->emp_id]);
            }

            if ($data->{$prefix . 'module_id'} == 339 && $stageComplete && $request->log_status == 157) {
                try {
                    $gtpDate = $data->gtp_date ?? null;
                    $gtpOut = $data->gtp_in_time ?? null;
                    $empId = $data->gtp_emp_id ?? ($data->{$prefix . 'emp_id'} ?? null);
                    if ($gtpDate && $gtpOut && $empId) {
                        $attendance = AttendanceRecord::where('atd_emp_id', $empId)
                            ->whereDate('atd_date', Carbon::parse($gtpDate)->format('Y-m-d'))
                            ->first();
                        $employee = Employee::find($empId);
                        $isEnableGtpCheckout = AutomationRule::where('ar_b_id', $employee->emp_b_id)
                            ->where('ar_rule_type', 418)
                            ->where('ar_apply_gatepass_checkout', 1)
                            ->exists();

                        if ($attendance) {
                            $resolvedShift = ShiftResolver::resolveEmployeeShift($data->fh_employee ?? $employee, $gtpDate);
                            $shift = $resolvedShift ? $resolvedShift->shift : ($data->fh_employee->fh_shift_type ?? null);

                            if ($shift && isset($shift->pst_end_time)) {
                                $shiftEnd = Carbon::parse(Carbon::parse($gtpDate)->format('Y-m-d') . ' ' . $shift->pst_end_time->format('H:i:s'));
                                $gateOut = Carbon::parse(Carbon::parse($gtpDate)->format('Y-m-d') . ' ' . $gtpOut);

                                if ($gateOut->greaterThan($shiftEnd) && $employee->emp_is_geofencing_active == 1 && $isEnableGtpCheckout) {
                                    $attendance->atd_check_out_time = $gateOut->format('Y-m-d H:i:s');
                                    $attendance->atd_attendance_status = 251;
                                    $attendance->atd_is_early_exit = 0;
                                    $attendance->atd_early_exit_duration = 0.00;
                                    $attendance->atd_gtp_id = $data->gtp_id;
                                    $attendance->save();
                                }
                            }
                        }
                    }
                } catch (Exception $e) {
                    \Log::error('GatePass attendance update error: ' . $e->getMessage());
                }
            }

            $approval = ApprovalLog::create([
                'log_request_id' => $data->{$prefix . 'module_id'} == 146 ? $data->{$prefix . 'trp_id'}  : $data->{$prefix . 'id'},
                'log_user_id' => $user->emp_id,
                'log_user_role_id' => $user->emp_role_id,
                'log_status' => $request->log_status,
                'log_description' => $request->log_description,
                'log_module_id' => $data->{$prefix . 'module_id'},
                'log_other' => $data->{$prefix . 'module_id'} == 199 ? $request->reimburse_amount : null,
            ]);


            if ($data->{$prefix . 'module_id'} == 589) {
                $currentApproverId = $user->emp_id;
                $existingUpdated = $data->atd_od_updated_auth_id ?? '';
                $idsArr = array_filter(array_map('trim', explode(',', $existingUpdated)));
                if (!in_array($currentApproverId, $idsArr)) {
                    $idsArr[] = $currentApproverId;
                }
                $updatedAuthStr = implode(',', $idsArr);
                $nextApproverId = null;
                if ($approvalMapping) {
                    $approvalArray = self::getApprovalArray($approvalMapping);
                    $approvalLog = $data->fh_approval_log2?->pluck('log_user_id')?->toArray() ?? [];
                    $approvalLog[] = $currentApproverId;
                    $approvalLog = array_unique($approvalLog);
                    $diff = array_values(array_diff($approvalArray, $approvalLog));
                    $nextApproverId = $diff[0] ?? null;
                }

                $data->update([
                    'atd_od_request_status'   => $request->log_status,
                    'atd_od_stage_completed'  => $stageComplete,
                    'atd_od_approved_by'      => $currentApproverId,
                    'atd_od_updated_auth_id'  => $updatedAuthStr,
                    'atd_od_next_approver_id' => $nextApproverId,
                ]);
            }
            
            // Start approval For FNF

            $fnfStage = \App\Models\Business::find($user->emp_b_id);
            $moduleIds = is_array($fnfStage->b_fnf_modules)
                ? $fnfStage->b_fnf_modules
                : (json_decode($fnfStage->b_fnf_modules, true) ?? []);
            $masterData = MasterTable::whereIn('m_id', $moduleIds)
                ->pluck('m_id', 'm_name');

            // Specific Module IDs
            $managerReviewId = $masterData['Manager Review'] ?? null;
            $hrReviewId      = $masterData['HR Review'] ?? null;
            $financeReviewId = $masterData['Finance Review'] ?? null;
            $adminReviewId   = $masterData['Admin Review'] ?? null;

            // Get approval modules
            $approvalFlows = ApprovalModule::where('am_b_id', $user->emp_b_id)
                ->whereIn('am_module_id', array_filter([
                    $managerReviewId,
                    $hrReviewId,
                    $financeReviewId,
                    $adminReviewId
                ]))
                ->get()
                ->keyBy('am_module_id');

            $managerApproval = $approvalFlows[$managerReviewId] ?? null;
            $hrApproval      = $approvalFlows[$hrReviewId] ?? null;
            $financeApproval = $approvalFlows[$financeReviewId] ?? null;
            $adminApproval   = $approvalFlows[$adminReviewId] ?? null;


            $moduleId = $data->{$prefix . 'module_id'};
            $stageModules = array_filter([
                $managerReviewId,
                $hrReviewId,
                $financeReviewId,
                $adminReviewId
            ]);

            if (in_array($moduleId, $stageModules)) {

                $data->update([
                    'er_requested_status' => $request->log_status,
                    'er_next_approver'    => 1,
                    'er_stage_completed'  => $stageComplete,
                    'er_approved_by'      => $user->emp_id
                ]);
            }

            $requestId = $data->{$prefix . 'module_id'} == 146
                ? $data->{$prefix . 'trp_id'}
                : $data->{$prefix . 'id'};

            $approval = ApprovalLog::create([
                'log_request_id'    => $requestId,
                'log_user_id'       => $user->emp_id,
                'log_user_role_id'  => $user->emp_role_id,
                'log_status'        => $request->log_status,
                'log_description'   => $request->log_description,
                'log_module_id'     => $data->{$prefix . 'module_id'},
                'log_other'         => $data->{$prefix . 'module_id'} == 199 ? $request->reimburse_amount : null,
            ]);

            if ($approval->log_status == 170) {

                // Rejection Flow
                if ($data->er_module_id == $managerReviewId) { //5888

                    $data->update([
                        'er_overall_status' => "MANAGER_REJECTED",
                    ]);
                } elseif ($data->er_module_id == $hrReviewId) { //5889

                    $data->update([
                        'er_overall_status' => "HR_REJECTED",
                    ]);
                }
            } else {
                if ($data->er_next_approver == 1 || ($data->er_stage_completed == 1 && $data->er_status != 170)) {

                    // 🔹 Flow define (IMPORTANT)
                    // 🔹 Helper function (upar kahin bhi define kar le)
                    function getNextStage($flow, $current)
                    {
                        $flow = array_values(array_filter($flow)); // null remove
                        $index = array_search($current, $flow);

                        return ($index !== false && isset($flow[$index + 1]))
                            ? $flow[$index + 1]
                            : null;
                    }

                    $flow = [
                        $managerReviewId,
                        $financeReviewId,
                        $hrReviewId,
                        $adminReviewId
                    ];

                    if ($data->er_module_id == $managerReviewId) {

                        // 🔹 Manager Approved → Next valid stage (skip null automatically)
                        $nextStage = getNextStage($flow, $managerReviewId);

                        $data->update([
                            'er_overall_status'  => "MANAGER_APPROVED",
                            'er_am_id'           => $approvalFlows[$nextStage] ?? null, // 🔥 dynamic approver
                            'er_status'          => 140,
                            'er_next_approver'   => $nextStage ? 1 : 0,
                            'er_stage_completed' => $nextStage ? 0 : 1,
                            'er_module_id'       => $nextStage ?? $managerReviewId,
                        ]);
                    } elseif ($data->er_module_id == $financeReviewId) {

                        // 🔹 Finance → Next valid stage
                        $nextStage = getNextStage($flow, $financeReviewId);

                        $data->update([
                            'er_overall_status'  => "CLEARANCE_IN_PROGRESS",
                            'er_am_id'           => $approvalFlows[$nextStage] ?? null,
                            'er_status'          => 140,
                            'er_next_approver'   => $nextStage ? 1 : 0,
                            'er_stage_completed' => $nextStage ? 0 : 1,
                            'er_module_id'       => $nextStage ?? $financeReviewId,
                        ]);
                    } elseif ($data->er_module_id == $hrReviewId) {

                        // 🔹 HR → Next valid stage
                        $nextStage = getNextStage($flow, $hrReviewId);

                        $data->update([
                            'er_overall_status'  => "HR_APPROVED",
                            'er_am_id'           => $approvalFlows[$nextStage] ?? null,
                            'er_status'          => 140,
                            'er_next_approver'   => $nextStage ? 1 : 0,
                            'er_stage_completed' => $nextStage ? 0 : 1,
                            'er_module_id'       => $nextStage ?? $hrReviewId,
                        ]);
                    } elseif ($data->er_module_id == $adminReviewId) {

                        // 🔹 Admin → Final (no next stage)
                        $data->update([
                            'er_overall_status'  => "RELIEVED",
                            'er_am_id'           => $adminApproval,
                            'er_status'          => 140,
                            'er_next_approver'   => 0,
                            'er_stage_completed' => 1,
                            'er_module_id'       => $adminReviewId,
                        ]);
                    }
                }
            }

            if ($data->{$prefix . 'module_id'} == 589 && $stageComplete && $request->log_status != 170) {
                try {
                    $employee = $data->fh_employee ?? Employee::find($data->atd_od_emp_id);

                    // Check weekly off / holiday
                    $isWeeklyOff = CentralLogics::getWeekOffDatesReport(
                        $employee, null, null,
                        Carbon::parse($data->atd_od_date)->format('Y-m-d'),
                        Carbon::parse($data->atd_od_date)->format('Y-m-d')
                    );

                    $isHoliday = PolicyHolidayList::where('phl_b_id', $employee->emp_b_id)
                        ->whereDate('phl_start_date', '<=', $data->atd_od_date)
                        ->whereDate('phl_end_date', '>=', $data->atd_od_date)
                        ->where('phl_type_id', 206)
                        ->exists();

                    $atd_status = null;
                    if ($isWeeklyOff || $isHoliday) {
                        $atd_status = $isWeeklyOff ? 320 : 319;
                        CentralLogics::generateCompOff($employee, $data->atd_od_date, 1);
                    }

                    $employee = Employee::find($data->atd_od_emp_id);
                    $resolvedShift = ShiftResolver::resolveEmployeeShift($employee, $data->atd_od_date);
                    $shift = $resolvedShift->shift ?? PolicyShiftTiming::find($employee->emp_shift_type_id);
                    $checkInTime = $data->atd_od_check_in_time;
                    $checkOutTime = $data->atd_od_check_out_time;
                    $totalHours = $data->atd_od_total_working_hours ?? 0.00;

                    if ($shift && $data->atd_od_check_in_time > $shift->pst_start_time) {
                        $checkInTime = $shift->pst_start_time;
                        $totalHours = round(Carbon::parse($shift->pst_start_time)->diffInMinutes(Carbon::parse($shift->pst_end_time)) / 60, 2);
                    }

                    if ($shift && $shift->pst_end_time > $data->atd_od_check_out_time) {
                        $checkOutTime = $shift->pst_end_time;
                        $totalHours = round(Carbon::parse($shift->pst_start_time)->diffInMinutes(Carbon::parse($shift->pst_end_time)) / 60, 2);
                    }

                    AttendanceRecord::create([
                        'atd_b_id'                => $data->atd_od_b_id,
                        'atd_emp_id'              => $data->atd_od_emp_id,
                        'atd_device_id'           => $data->atd_od_device_id,
                        'atd_date'                => $data->atd_od_date,
                        'atd_pst_id'              => $data->atd_od_pst_id,
                        'atd_work_mode_type_id'   => $data->atd_od_work_mode_type_id,
                        'atd_checkin_method_id'   => $data->atd_od_checkin_method_id,
                        'atd_check_in_time'       => $checkInTime,
                        'atd_check_out_time'      => $checkOutTime,
                        'atd_total_worked_hours'  => $totalHours,
                        'atd_is_late'             => 0,
                        'atd_late_duration'       => 0.00,
                        'atd_is_overtime'         => 0,
                        'atd_overtime_hours'      => 0.00,
                        'atd_attendance_status'   => 251,
                        'atd_module_id'           => 249,
                        'atd_segments'            => $data->atd_od_segments,
                        'atd_punchin_photo'       => $data->atd_od_punchin_photo,
                        'atd_punchout_photo'      => $data->atd_od_punchout_photo,
                        'atd_punchin_location'    => $data->atd_od_punchin_location,
                        'atd_punchout_location'   => $data->atd_od_punchout_location,
                        'atd_longitude_punchin'   => $data->atd_od_longitude_punchin,
                        'atd_latitude_punchin'    => $data->atd_od_latitude_punchin,
                        'atd_longitude_punchout'  => $data->atd_od_longitude_punchout,
                        'atd_latitude_punchout'   => $data->atd_od_latitude_punchout,
                        'atd_remark'              => $data->atd_od_remark,
                        'atd_am_id'               => $data->atd_od_am_id,
                        'atd_stage_completed'     => 1,
                        'atd_request_status'      => $request->log_status,
                        'atd_next_approver'       => 1,
                        'atd_updated_by'          => $user->emp_id,
                    ]);

                    // Update outdoor record status
                    $data->update([
                        'atd_od_request_status'   => $request->log_status,
                        'atd_od_stage_completed'  => $stageComplete,
                        'atd_od_approved_by'      => $user->emp_id,
                    ]);

                } catch (\Exception $e) {
                    \Log::error('Outdoor attendance record creation failed: ' . $e->getMessage());
                }
            }

            // Rejection case for outdoor
            if ($data->{$prefix . 'module_id'} == 589 && $request->log_status == 170) {
                $data->update([
                    'atd_od_request_status'  => $request->log_status,
                    'atd_od_stage_completed' => $stageComplete,
                    'atd_od_approved_by'     => $user->emp_id,
                ]);
            }


            // End
            //start handle deduction
            if ($data->{$prefix . 'module_id'} == 146 && (isset($request->deduction_amount) && $request->deduction_amount)) { //146==Claim
                $updateData['tc_deduction_amount'] = $data->tc_deduction_amount ? ($data->tc_deduction_amount + $request->deduction_amount) : $request->deduction_amount;
                $updateData['tc_deduction_status'] = null;
                $updateData['tc_deduction_remarks'] = $request->log_description;
                DeductionLog::create([
                    'dlog_log_id' => $approval->log_id,
                    'dlog_am_id' => $data->tc_am_id,
                    'dlog_tc_id' => $data->tc_id,
                    'dlog_user_id' => $user->emp_id,
                    'dlog_user_role_id' => $user->emp_role_id,
                    'dlog_deduction_amount' => $request->deduction_amount,
                    'dlog_remarks' => $request->log_description,
                    'dlog_additional_info' => isset($request->deduction_info) ? (is_array($request->deduction_info) ? json_encode($request->deduction_info) : $request->deduction_info) : '',
                ]);

                $data->update($updateData);
            }
            //start handle deduction
            $masterTable = MasterTable::where('m_id', $request->log_status)->first();
            // Commit the transaction
            DB::commit();
            return ['status' => 'success', 'message' => optional($data->fh_module)->m_name . ' Request ' . optional($masterTable)->m_name . ' Successfully', 'data' => $approval];
        } catch (Exception $e) {
            // Rollback the transaction if an error occurs
            DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public static function getDistanceAndDuration($origin, $destination, $speedKmph = 50)
    {
        // Parse origin and destination coordinates
        list($lat1, $lon1) = explode(',', $origin);
        list($lat2, $lon2) = explode(',', $destination);

        // Convert degrees to radians
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        // Haversine formula
        $earthRadius = 6371000; // in meters
        $deltaLat = $lat2 - $lat1;
        $deltaLon = $lon2 - $lon1;

        $a = sin($deltaLat / 2) ** 2 +
             cos($lat1) * cos($lat2) *
             sin($deltaLon / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distanceMeters = $earthRadius * $c;

        // Estimate duration based on speed (default: 50 km/h)
        $speedMps = ($speedKmph * 1000) / 3600; // meters per second
        $durationSeconds = $distanceMeters / $speedMps;

        return [
            'distance_text'  => round($distanceMeters / 1000, 2) . ' km',
            'distance_value' => round($distanceMeters), // in meters
            'duration_text'  => gmdate('H:i:s', round($durationSeconds)),
            'duration_value' => round($durationSeconds), // in seconds
        ];
    }

    public static function checkPending($employeeId, $masterModuleId)
    {
        $modules = [
            199 => [AdvanceLog::class, 'adl_'],        // Advance
            229 => [AttendanceException::class, 'ae_'], // Missed Punch
            339 => [GatePass::class, 'gtp_'],          // GatePass
            250 => [LeaveRequest::class, 'lvr_'],      // Leave
            145 => [TadaRequestPlan::class, 'trp_'],   // Tada Request
            146 => [TadaClaim::class, 'tc_'],          // Tada Claim
            442 => [LoanRequest::class, 'lnr_'],       // Loan
            249 => [AttendanceRecord::class, 'atd_'],  // Attendance
            562 => [OtApprovalStatus::class, 'ot_'],   // OT Approval
            589 => [\App\Models\AttendanceOutDoor::class, 'atd_od_'], // Outdoor Attendance
        ];

        if (!isset($modules[$masterModuleId])) {
            return false; // no module mapping
        }

        [$model, $prefix] = $modules[$masterModuleId];

        // Build dynamic column name
        $idCol = $prefix . "emp_id";
        if ($masterModuleId == 562) {
            $statusCol = $prefix . "requested_status";
        } elseif ($masterModuleId == 249) {
            $statusCol = $prefix . "request_status";
        } elseif ($masterModuleId == 589) {
            $statusCol = $prefix . "request_status";
        } else {
            $statusCol = $prefix . "status";
        }
        
        $stageCol = $prefix . "stage_completed";

        // If model has no employee column (ex: OT), fall back to standard
        if (!self::columnExists($model, $idCol)) {
            $idCol = $prefix . "id";
        }

        // check pending
        $pending = $model::where($idCol, $employeeId)
            ->where($statusCol, '!=', 157)        // 157 = rejected
            ->where($stageCol, '!=', 1)           // 1 = cycle completed
            ->exists();

        if ($pending) {
            return "Please finish the approval process for this module before updating.";
        }

        return false;
    }

    private static function columnExists($model, $column)
    {
        try {
            return \Schema::hasColumn((new $model)->getTable(), $column);
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function getEmployeename($id)
    {
        try {
            if (!$id) {
                return '';
            }

            $empName = Employee::where('emp_id', $id)->value('emp_full_name');
            return preg_replace('/\s+/', ' ', trim($empName));

        } catch (\Exception $e) {
            return false;
        }
    }
}
