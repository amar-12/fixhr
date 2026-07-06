<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
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
use Illuminate\Http\Request;
use App\Events\ApprovalEvent;
use App\Models\RuleCriterion;
use App\Helpers\CentralLogics;
use App\Helpers\PayrollLogics;
use App\Models\ApprovalModule;
use App\Helpers\ApprovalHelper;
use App\Models\ProcessApprover;
use App\Models\TadaRequestPlan;
use App\Models\AttendanceRecord;
use App\Services\FirebaseService;
use Illuminate\Http\JsonResponse;
use App\Models\NextApprovalDetail;
use Illuminate\Support\Facades\DB;
use App\Events\TravelApprovalEvent;
use App\Helpers\NotificationHelper;
use App\Models\ActionUponRejection;
use App\Models\AttendanceException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\PayrollLoanInstallment;
use App\Events\TravelPlanStatusUpdated;
use App\Helpers\ShiftResolver;
use ChandraHemant\HtkcUtils\CommonUtils;
use ChandraHemant\HtkcUtils\ReturnHelper;
use App\Notifications\TestFcmNotification;
use ChandraHemant\HtkcUtils\FirebaseNotification;
use App\Http\Resources\Approval\ProcessApproverApiResource;
use App\Models\Business;
use App\Models\CompOff;
use App\Models\CompOffBalance;
use App\Models\EmployeeExitRequest;
use App\Models\MailTemplate;
use App\Models\PolicyHolidayList;
use App\Models\OtApprovalStatus;
use App\Models\PolicyShiftTiming;
use App\Models\AutomationRule;
use Illuminate\Support\Facades\Mail;
use App\Models\AttendanceOutDoor;
use App\Models\EmployeeApprovalMapping;

class CommonApprovalController extends Controller
{

    protected $user;

    private $database;

    public function __construct()
    {
        $this->user = Auth::user();
        //$this->database = FirebaseService::connect();
    }

    /**
     * Get approvers for notification (employee-wise or hierarchy-wise)
     *
     * @param int $businessId
     * @param int $employeeId
     * @param int $moduleId
     * @param int $departmentId
     * @return array
     */
    private function getApproversForNotification($businessId, $employeeId, $moduleId, $departmentId = null)
    {
        $approvalEmpIds = [];
        $amId = null;
        
        // Try employee-wise mapping first
        $approvalMapping = ApprovalHelper::getApprovalMapping($businessId, $employeeId, $moduleId);
        if ($approvalMapping) {
            $approvalEmpIds = ApprovalHelper::getApprovalArray($approvalMapping);
            $amId = $approvalMapping->eam_am_id ?? null;
        } else {
            // Fallback to hierarchy: get RuleCriteria and processApprovers
            $ruleCriteria = RuleCriterion::with('fh_approval_module')
                ->where('rc_b_id', $businessId)
                ->where('rc_condition_option_id', 140)
                ->whereHas('fh_approval_module', function ($query) use ($moduleId) {
                    $query->where('am_module_id', $moduleId)
                        ->where('am_status', 1);
                })->first();
            
            if ($ruleCriteria && $ruleCriteria->fh_approval_module) {
                $processApprovers = $ruleCriteria->fh_approval_module->filteredProcessApprovers($departmentId)->get();
                if (!empty($processApprovers)) {
                    $amId = $ruleCriteria->rc_am_id;
                    foreach ($processApprovers as $pa) {
                        if ($pa->pa_emp_id) {
                            $approvalEmpIds[] = $pa->pa_emp_id;
                        }
                    }
                }
            }
        }
        
        return [
            'approvalEmpIds' => $approvalEmpIds,
            'amId' => $amId
        ];
    }

    public function checkApproval(Request $request)
    {
        $displayDeductionHandler = null;
        $masterModuleId = $request->master_module_id;
        $claim = TadaClaim::where('tc_trp_id', $request->trp_id)->first();
        if ($claim) {
            $displayDeductionHandler = $claim->fh_deduction_log->whereNull('dlog_requester_action')->first();
        }
        $approvalData = ApprovalHelper::getApprovalOrRejectionData($request->trp_id, $request->approval_status, $request->module_id, NULL, $masterModuleId);
        $canApprove = false;
        if (!$approvalData) {
            $data = null;
            if ($masterModuleId == 199) {
                $data = AdvanceLog::findOrFail($request->trp_id);
                $prefix = 'adl_';
            } else if ($masterModuleId == 229) {
                $data = AttendanceException::findOrFail($request->trp_id);
                $prefix = 'ae_';
            } else if ($masterModuleId == 339) {
                $data = GatePass::findOrFail($request->trp_id);
                $prefix = 'gtp_';
            } else if ($masterModuleId == 250) {
                $data = LeaveRequest::findOrFail($request->trp_id);
                $prefix = 'lvr_';
            } else if ($masterModuleId == 145) {
                $data = TadaRequestPlan::findOrFail($request->trp_id);
                $prefix = 'trp_';
            }
            else if ($masterModuleId == 146) {
                $data = TadaClaim::where('tc_trp_id',$request->trp_id)->first();
                $prefix = 'tc_';
            } else if ($masterModuleId == 442) {
                $data = LoanRequest::where('lnr_id',$request->trp_id)->first();
                $prefix = 'lnr_';
            } else if ($masterModuleId == 249) {
                $data = AttendanceRecord::where('atd_id',$request->trp_id)->first();
                $prefix = 'atd_';
            } else if ($masterModuleId == 562) {
                $data = OtApprovalStatus::where('ot_id',$request->trp_id)->first();
                $prefix = 'ot_';
            } else if ($masterModuleId == 589) {
                $data = AttendanceOutDoor::where('atd_od_id',$request->trp_id)->first();
                $prefix = 'atd_od_';
            }
            
            if($data){
                $approval = ApprovalHelper::getApprovalData($data, $this->user, $prefix);
                $canApprove = $approval['canApprove'];
            }
        }
        if ($approvalData && !$displayDeductionHandler) {
            return ReturnHelper::jsonApiReturn(ProcessApproverApiResource::collection([$approvalData])->all());
        } elseif ($canApprove && !$displayDeductionHandler) {
            return response()->json(['result' => [], 'status' => true, 'can_approve' => true], 200);
        } else {
            return ReturnHelper::jsonApiReturn(ProcessApproverApiResource::collection([])->all());
        }
    }

    public function approveBulk(Request $request)
    {
        $responses = [];
        foreach ($request->data as $approvalData) {
            $data = LeaveRequest::where((DB::raw('md5(lvr_id)')), $approvalData['log_request_id'])->first();
            $approvalData['log_request_id'] = $data->lvr_id;

            if (!$data) {
                return response()->json([
                    'log_request_id' => $approvalData['log_request_id'],
                    'status' => 'error',
                    'message' => 'Leave request not found'
                ]);
            }

            $formattedRequest = new Request([
                'data' => (object) $approvalData // Convert array to object
            ]);

            // dd($formattedRequest['data']->log_status);
            $response = ApprovalHelper::processApproval($formattedRequest['data'], $data, $this->user, $formattedRequest['data']->prefix);
            $responses[] = [
                'log_request_id' => $approvalData['log_request_id'],
                'status' => $response['status'] ?? 'error',
                'message' => $response['message'] ?? 'Unknown error'
            ];

            if ($response instanceof JsonResponse) {
                $response = $response->getData(true); // Convert JSON response to an array
            }

            if (!isset($response['status']) || !$response['status']) {
                return response()->json($response, 400); // Stop if any approval fails
            }
        }

        // return response()->json(['status' => true, 'message' => 'All approvals processed successfully.', 'result' => true ], 200);
        // return response()->json($response, $response['status'] === 'success' ? 200 : 500);
        return response()->json([
            'status' => 'success',
            'message' => 'All approvals processed successfully.',
            'result' => $responses
        ], 200);
    }

    public function commonApproveBulk(Request $request)
    {
        $responses = [];
        foreach ($request->data as $approvalData) {
            $pId = $approvalData['prefix'] . 'id';
            $modelClass = "App\\Models\\{$approvalData['model']}"; // e.g., AttendanceRecord, LeaveRequest, etc.
            $data = $modelClass::where(DB::raw("md5($pId)"), $approvalData['log_request_id'])->first();
            $approvalData['log_request_id'] = $data->{$pId};

            if (!$data) {
                return response()->json([
                    'log_request_id' => $approvalData['log_request_id'],
                    'status' => 'error',
                    'message' => 'Approval request not found'
                ]);
            }

            $formattedRequest = new Request([
                'data' => (object) $approvalData // Convert array to object
            ]);

            $response = ApprovalHelper::processApproval($formattedRequest['data'], $data, $this->user, $approvalData['prefix']);
            $responses[] = [
                'log_request_id' => $approvalData['log_request_id'],
                'status' => $response['status'] ?? 'error',
                'message' => $response['message'] ?? 'Unknown error'
            ];

            if ($response instanceof JsonResponse) {
                $response = $response->getData(true); // Convert JSON response to an array
            }

            if (!isset($response['status']) || !$response['status']) {
                return response()->json($response, 400); // Stop if any approval fails
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'All approvals processed successfully.',
            'result' => $responses
        ], 200);
    }

    public function handlerBulk(Request $request)
    {
        $response = ['status' => false, 'message' => '', 'result' => false];
        if (!$request->has('data') || !is_array($request->data)) {
            $response['message'] = 'Invalid data format.';
            return response()->json($response, 400);
        }

        foreach ($request->data as $approvalData) {
            // Ensure that each data entry is an object
            $formattedRequest = new Request([
                'POST_TYPE' => $request->POST_TYPE,
                'data' => (object) $approvalData // Convert array to object
            ]);

            $result = $this->handlerApproval($formattedRequest);

            if ($result instanceof JsonResponse) {
                $result = $result->getData(true); // Convert JSON response to an array
            }

            if (!isset($result['status']) || !$result['status']) {
                return response()->json($result, 400); // Stop if any approval fails
            }
        }

        return response()->json(['status' => true, 'message' => 'All approvals processed successfully.', 'result' => true ], 200);
    }

    public function handlerApproval(Request $request)
    {
        $user = Auth::user();
        $response = ['status' => false, 'message' => '', 'result' => false];
        $nextApprover = 1;
        if (isset($request->POST_TYPE)) {
            $data = (object)$request->data;
            $approval_sequence = (int) $data->approval_sequence;

            if ($data->approval_action_type != 'and') {
                $isLast = 1;
            } else { //$data->approval_action_type == 'and'
                $isLast = (int) $data->is_last_approval;
                if (!$isLast) {
                    $nextApprover = $approval_sequence + 1;
                }
            }
            // Retrieve the claim data
            if ($request->POST_TYPE == 'CLAIM_REQUEST_APPROVAL') {
                // this code check the deduction amount must be less than payable amount start
                $claimData = TadaClaim::where('tc_b_id', $user->emp_b_id)
                    ->where(DB::raw('md5(tc_id)'), $request->data['tc_id'])
                    ->first();

                $deductionInfo = $request->data['deduction_info'] ?? [];
                if ($claimData && $claimData->fh_deduction_log->where('dlog_requester_action', 1)->isNotEmpty()) {
                    // Iterate through deduction logs where requester action is 1
                    foreach ($claimData->fh_deduction_log->where('dlog_requester_action', 1) as $deduction_log) {
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
                if (!empty($deductionInfo) && isset($request->data['payable_amount'])) {
                    foreach ($deductionInfo as $key => $deductionAmount) {
                        // Ensure payable amount exists for the key
                        if (isset($request->data['payable_amount'][$key])) {
                            if ($request->data['payable_amount'][$key] < $deductionAmount) {
                                $response = [
                                    'result' => false,
                                    'status' => false,
                                    'message' => 'Deduction amount cannot be greater than the payable amount',
                                ];
                                return response()->json($response);
                            }
                        }
                    }
                }
                // this code check the deduction amount must be less than payable amount  end
            }

            if ($data->approval_type == 1) { // Approval action
                $actionStatusId = $data->approval_status;
                $statusData = MasterTable::where(['m_group' => 'APPROVAL_STATUS', 'm_id' => $actionStatusId])
                ->select('m_id', 'm_name')
                ->first();

                if ($request->POST_TYPE == 'LOAN_REQUEST_APPROVAL') {

                    $loan = LoanRequest::with([
                        'fh_employee' => function ($q) {
                            $q->select('emp_id', 'emp_fname', 'emp_full_name', 'emp_email', 'emp_d_id', 'emp_date_of_joining', 'emp_dob')
                            ->with('fh_employee_salary');
                        }
                        ])->where(DB::raw('md5(lnr_id)'), $data->lnr_id)->first();

                        $check = PayrollLogics::validateLoanAgainstSettings($loan);

                    if (!$check['status']) {
                        return response()->json([
                            'status' => false,
                            'message' => $check['message']
                        ], 422);
                    }

                    if (!isset($data->installments)) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Installments data is required'
                        ], 422);
                    }

                    $existingLoan = LoanRequest::where('lnr_emp_id', $loan->lnr_emp_id)
                        ->where('lnr_b_id', $loan->lnr_b_id)
                        ->whereIn('lnr_status', ['approved', 'pending'])
                        ->where('lnr_id', '!=', $loan->lnr_id)
                        ->exists();

                    if ($existingLoan) {
                        return response()->json([
                            'message' => 'Employee already has an ongoing or pending loan',
                            'status' => false,
                        ], 422);
                    }

                    $loan->update([
                        'lnr_rate' => $data->rate ?? 0,
                        'lnr_status' => 'approved'
                    ]);


                    $startDate = Carbon::parse($loan->lnr_start_date)->startOfMonth();

                    $openingBalance = $loan->lnr_amount;

                    foreach ($data->installments as $installment) {
                        // dd($installment);
                        $dueDate = $startDate->copy()->addMonths($installment['installment_number'] - 1);
                        PayrollLoanInstallment::updateOrCreate(
                            [
                                'pli_loan_id'        => $loan->lnr_id,
                                'pli_installment_no' => $installment['installment_number'],
                            ],
                            [
                                'pli_b_id'          => $loan->lnr_b_id,
                                'pli_opening_balance'   => $installment['opening_balance'] ?? $openingBalance,
                                'pli_amount'        => $installment['amount'],
                                'pli_principal'     => $installment['principal_amount'] ?? 0,
                                'pli_interest'      => $installment['interest_amount'] ?? 0,
                                'pli_rem_bal'       => $installment['remaining_balance'],
                                'pli_due_date'      => $dueDate->format('Y-m-d'),
                                'pli_month'         => $dueDate->month,
                                'pli_year'          => $dueDate->year,
                                'pli_status'        => 'pending',
                            ]
                        );

                        $openingBalance = $installment['remaining_balance'];
                    }
                }

                if ($request->POST_TYPE == 'LOAN_REQUEST_APPROVAL_APP') {

                    $loan = LoanRequest::with('fh_employee:emp_id,emp_fname,emp_email,emp_full_name,emp_d_id')
                        ->where(DB::raw('md5(lnr_id)'), $data->lnr_id)
                        ->first();
                    if (!$loan) {
                        return response()->json([
                            'status'  => false,
                            'message' => 'Loan request not found'
                        ], 404);
                    }

                    // Check for existing loan
                    $existingLoan = LoanRequest::where('lnr_emp_id', $loan->lnr_emp_id)
                        ->where('lnr_b_id', $loan->lnr_b_id)
                        ->whereIn('lnr_status', ['approved', 'pending'])
                        ->where('lnr_id', '!=', $loan->lnr_id)
                        ->exists();

                    if ($existingLoan) {
                        return response()->json([
                            'message' => 'Employee already has an ongoing or pending loan',
                            'status'  => false,
                        ], 422);
                    }

                    // Update loan details
                    $loan->update([
                        'lnr_rate'   => $data->rate ?? 0,
                        'lnr_status' => 'approved'
                    ]);

                    // Calculate installment amount (equal amounts)
                    $totalAmount      = $loan->lnr_requested_amount;
                    $totalInstallments = $loan->lnr_installments ?? 1;
                    $installmentAmount = round($totalAmount / $totalInstallments, 2);

                    $startDate = Carbon::parse($loan->lnr_start_date)->startOfMonth();

                    // Create installments
                    $remainingBalance = $totalAmount;


                    for ($i = 1; $i <= $totalInstallments; $i++) {

                        $dueDate = $startDate->copy()->addMonths($i - 1);

                        PayrollLoanInstallment::updateOrCreate(
                            [
                                'pli_loan_id'        => $loan->lnr_id,
                                'pli_installment_no' => $i,
                            ],
                            [
                                'pli_b_id'     => $loan->lnr_b_id,
                                'pli_amount'   => $installmentAmount,
                                'pli_rem_bal'  => $remainingBalance,
                                'pli_due_date' => $dueDate->format('Y-m-d'),
                                'pli_month'    => $dueDate->month,
                                'pli_year'     => $dueDate->year,
                                'pli_status'   => 'pending',
                            ]
                        );

                        $remainingBalance -= $installmentAmount;
                    }
                }

                // Update status for all request types (including loans)
                $this->updateRequestStatusAndLog($data, $statusData, $nextApprover, $request->POST_TYPE, $isLast);

                $response['result'] = true;
                $response['status'] = true;
                $response['message'] = $statusData->m_name . ' Successfully';
            } else { //rejection action
                $userIds = ActionUponRejection::where(['aur_b_id' => $user->emp_b_id])->where((DB::raw('md5(aur_am_id)')), $data->module_id)->pluck('aur_group_ids')->first();
                if ($userIds) {
                    $receiverGroup = MasterTable::where(['m_group' => 'APPROVAL_NOTIFY'])->whereIn('m_id', json_decode($userIds))->pluck('m_id')->toArray();
                    $nextApprover = $approval_sequence;
                    foreach ($receiverGroup as $receiver_group_id) {
                        if ($receiver_group_id == 152) {
                            if ($request->POST_TYPE == 'TRAVEL_REQUEST_APPROVAL') {
                                $plan = TadaRequestPlan::with('fh_employee:emp_id,emp_fname,emp_email,emp_full_name,emp_d_id')->where((DB::raw('md5(trp_id)')), $data->trp_id)->first();
                                if ($plan->fh_employee()->exists()) {
                                    $placeholders = [
                                        '{travel_type_name}' => $plan->fh_policy_tada_travel_type->fh_travel_type->m_name,
                                        '{receiver_name}' => $plan->fh_employee->emp_full_name,
                                        '{destination}' => $plan->trp_destination,
                                        '{start_date}' => Carbon::parse($plan->trp_start_date)->format('d M, Y'),
                                        '{start_time}' =>  Carbon::parse($plan->trd_start_time)->format('h:i A'),
                                        '{end_date}' =>  Carbon::parse($plan->trp_end_date)->format('d M, Y'),
                                        '{end_time}' =>  Carbon::parse($plan->trd_end_time)->format('h:i A'),
                                        '{request_id}' => $plan->trp_unique_id,
                                        '{submitted_date}' => Carbon::parse($plan->created_at)->format('h:i A'),
                                        '{travel_purpose}' => $plan->fh_travel_purpose->tp_name,
                                        '{approver_name}' => $user->emp_full_name,
                                        '{approver_designation}' => $user->fh_designation->dg_name,
                                        '{approver_phone}' => $user->emp_phone,
                                        '{rejection_remark}' => $plan->fh_plan_approval_log()->where('log_user_id', $user->emp_id)->orderBy('log_id', 'desc')->pluck('log_description')->first(),

                                    ];
                                    $recipientEmail = $plan->fh_employee->emp_email; // Replace with dynamic recipient email
                                    $templateType = 388; // Replace with your mail template type
                                    $businessId = $user->emp_b_id; // Replace with your business ID if applicable
                                    $sent = CentralLogics::sendCustomEmail($templateType, $placeholders, $recipientEmail, $businessId);                                // $mailData = [
                                    //     'subject' => 'Tour Request - ' . $plan->fh_policy_tada_travel_type->fh_travel_type->m_name . ' Rejected',
                                    //     'url' => route('travel-request.request.show', ['id' => md5($plan->trp_id)]),
                                    //     'mail_type' => 'TOUR_APPROVAL_REQUEST_REJECT',
                                    //     'data' => ['planData' => $plan, 'receiverName' => $plan->fh_employee->emp_fname, 'approver' => $user],
                                    // ];
                                    // CentralLogics::send_mail($plan->fh_employee->emp_email, new ApprovalMail($mailData));
                                }
                            } elseif ($request->POST_TYPE == 'CLAIM_REQUEST_APPROVAL') {
                                $claim = TadaClaim::where((DB::raw('md5(tc_id)')), $data->tc_id)->first();
                                if ($claim->fh_tada_request_plan->fh_employee()->exists()) {
                                    if (isset($claim) && $claim->fh_tada_request_plan && $claim->fh_tada_request_plan->fh_tada_expenses()->exists()) {
                                        $expenses = $claim->fh_tada_request_plan->fh_tada_expenses ?? []; //$data['data']['claimData']->fh_tada_request_plan->fh_tada_expenses ?? [];
                                        if ($expenses->isNotEmpty()) {
                                            $expenseSummary = "Below is a brief summary of expenses:\n\n";
                                            $expenseSummary .= "S.No.\tParticulars\tAmount\n";

                                            $i = 1;
                                            foreach ($expenses->groupBy('te_type_id') as $tadaExpenseItem) {
                                                $particulars = $tadaExpenseItem[0]->fh_expense_type->m_name ?? 'N/A';
                                                $amount = $tadaExpenseItem->sum('te_amount') + $tadaExpenseItem->sum('te_taxes');
                                                $expenseSummary .= "$i\t$particulars\t$amount\n";
                                                $i++;
                                            }
                                            $totalExpenses = $expenses->sum('te_amount') + $expenses->sum('te_taxes');
                                            $expenseSummary .= "\nTotal Expenses: $totalExpenses\n";
                                        } else {
                                            $expenseSummary = "No expenses were reported.";
                                        }
                                    }

                                    $placeholders = [
                                        '{reciver_name}' => $claim->fh_tada_request_plan->fh_employee->emp_full_name,
                                        '{claim_id}' => $claim->tc_unique_id,
                                        '{claim_ref_Id}' => $claim->fh_tada_request_plan->trp_unique_id,
                                        '{claim_date}' => Carbon::parse($claim->created_at)->format('d-M-Y'),
                                        '{approver_name}' => $user->emp_full_name,
                                        '{approver_designation}' => $user->fh_designation->dg_name,
                                        '{approver_phone}' => $user->emp_phone,
                                        '{expense_summary}' => isset($expenseSummary) ? $expenseSummary: '',
                                        '{rejection_remark}' => $claim->fh_claim_approval_log()->where('log_user_id', $user->emp_id)->orderBy('log_id', 'desc')->pluck('log_description')->first(),
                                    ];
                                    $recipientEmail = $claim->fh_tada_request_plan->fh_employee->emp_email; // Replace with dynamic recipient email
                                    $templateType = 389; // Replace with your mail template type
                                    $businessId = $user->emp_b_id; // Replace with your business ID if applicable
                                    $sent = CentralLogics::sendCustomEmail($templateType, $placeholders, $recipientEmail, $businessId);

                                    // $mailData = [
                                    //     'subject' => 'Travel expense claim request - Rejected',
                                    //     'url' => route('claim.request.show', ['id' => $claim->tc_id]),
                                    //     'mail_type' => 'CLAIM_REQUEST_REQUESTER_REJECT',
                                    //     'data' => ['claimData' => $claim, 'receiverName' => $claim->fh_tada_request_plan->fh_employee->emp_fname, 'approver' => $user],
                                    // ];
                                    // CentralLogics::send_mail($claim->fh_tada_request_plan->fh_employee->emp_email, new ApprovalMail($mailData));
                                }
                            } elseif ($request->POST_TYPE == 'MISPUNCH_REQUEST_APPROVAL') {
                                $plan = AttendanceException::with('fh_employee:emp_id,emp_fname,emp_email,emp_full_name,emp_d_id')->where((DB::raw('md5(ae_id)')), $data->ae_id)->first();
                                if ($plan->fh_employee()->exists()) {
                                    $placeholders = [
                                        '{receiver_name}' => $plan->fh_employee->emp_full_name,
                                        '{date}' => $plan->ae_date,
                                        '{in_time}' => $plan->ae_in_time,
                                        '{out_time}' => $plan->ae_out_time,
                                        '{working_hour}' => $plan->ae_total_working,
                                        '{reason}' => $plan->ae_reason_id,
                                        '{approver_name}' => $user->emp_full_name,
                                        '{approver_designation}' => $user->fh_designation->dg_name,
                                        '{approver_phone}' => $user->emp_phone,
                                        '{rejection_remark}' => $plan->fh_plan_approval_log()->where('log_user_id', $user->emp_id)->pluck('log_description')->first(),
                                    ];
                                    $recipientEmail = $plan->fh_employee->emp_email; // $plan->fh_employee->emp_email; // Replace with dynamic recipient email
                                    $templateType = 390; // Replace with your mail template type
                                    $businessId = $user->emp_b_id; // Replace with your business ID if applicable
                                    $sent = CentralLogics::sendCustomEmail($templateType, $placeholders, $recipientEmail, $businessId);

                                    //  $mailData = [
                                    //      'subject' => 'Mis-Punch Request -  Rejected',
                                    //      'url' => route('mis-punch.show', ['mis_punch' => md5($plan->ae_id)]),
                                    //      'mail_type' => 'MISPUNCH_APPROVAL_REQUEST_REJECT',
                                    //      'data' => ['mispunchData' => $plan, 'receiverName' => $plan->fh_employee->emp_full_name, 'approver' => $user],
                                    //  ];
                                    //  CentralLogics::send_mail($plan->fh_employee->emp_email, new ApprovalMail($mailData));
                                }
                            } elseif ($request->POST_TYPE == 'GATEPASS_REQUEST_APPROVAL') {
                                $plan = GatePass::with('fh_employee:emp_id,emp_fname,emp_email,emp_full_name,emp_d_id')->where((DB::raw('md5(gtp_id)')), $data->gtp_id)->first();
                                if ($plan->fh_employee()->exists()) {
                                    $placeholders = [
                                        '{receiver_name}' => $plan->fh_employee->emp_full_name,
                                        '{date}' => $plan->gtp_date,
                                        '{in_time}' => $plan->gtp_in_time,
                                        '{out_time}' => $plan->gtp_out_time,
                                        '{destination}' => $plan->gtp_destination,
                                        '{reason}' => $plan->gtp_reason,
                                        '{approver_name}' => $user->emp_full_name,
                                        '{approver_designation}' => $user->fh_designation->dg_name,
                                        '{approver_phone}' => $user->emp_phone,
                                        '{rejection_remark}' => $plan->fh_plan_approval_log()->where('log_user_id', $user->emp_id)->pluck('log_description')->first(),
                                    ];
                                    $recipientEmail = $plan->fh_employee->emp_email; // $plan->fh_employee->emp_email; // Replace with dynamic recipient email
                                    $templateType = 391; // Replace with your mail template type
                                    $businessId = $user->emp_b_id; // Replace with your business ID if applicable
                                    $sent = CentralLogics::sendCustomEmail($templateType, $placeholders, $recipientEmail, $businessId);

                                    //  $mailData = [
                                    //      'subject' => 'Gatepass Request - Rejected',
                                    //      'url' => route('requests.gate-pass.show', ['id' => md5($plan->gtp_id)]),
                                    //      'mail_type' => 'GATEPASS_APPROVAL_REQUEST_REJECT',
                                    //      'data' => ['gatepassData' => $plan, 'receiverName' => $plan->fh_employee->emp_full_name, 'approver' => $user],
                                    //  ];
                                    //  CentralLogics::send_mail($plan->fh_employee->emp_email, new ApprovalMail($mailData));
                                }
                            }elseif ($request->POST_TYPE == 'LOAN_REQUEST_APPROVAL') {
                                $plan = LoanRequest::with('fh_employee:emp_id,emp_fname,emp_email,emp_full_name,emp_d_id')->where((DB::raw('md5(lnr_id)')), $data->lnr_id)->first();

                                // $plan = LoanRequest::with('fh_employee:emp_id,emp_fname,emp_email,emp_full_name,emp_d_id')
                                // ->where(DB::raw('md5(lnr_id)'), md5($data->lnr_id))
                                // ->first();
                                // dd($plan);
                                if ($plan->fh_employee()->exists()) {
                                    $placeholders = [
                                        '{receiver_name}' => $plan->fh_employee->emp_full_name,
                                        '{reason}' => $plan->lnr_description,
                                        '{approver_name}' => $user->emp_full_name,
                                        '{approver_designation}' => $user->fh_designation->dg_name,
                                        '{approver_phone}' => $user->emp_phone,
                                        '{rejection_remark}' => $plan->fh_plan_approval_log()->where('log_user_id', $user->emp_id)->pluck('log_description')->first(),
                                    ];
                                    $recipientEmail = $plan->fh_employee->emp_email; // $plan->fh_employee->emp_email; // Replace with dynamic recipient email
                                    $templateType = 391; // Replace with your mail template type
                                    $businessId = $user->emp_b_id; // Replace with your business ID if applicable
                                    $sent = CentralLogics::sendCustomEmail($templateType, $placeholders, $recipientEmail, $businessId);

                                    //  $mailData = [
                                    //      'subject' => 'Gatepass Request - Rejected',
                                    //      'url' => route('requests.gate-pass.show', ['id' => md5($plan->gtp_id)]),
                                    //      'mail_type' => 'GATEPASS_APPROVAL_REQUEST_REJECT',
                                    //      'data' => ['gatepassData' => $plan, 'receiverName' => $plan->fh_employee->emp_full_name, 'approver' => $user],
                                    //  ];
                                    //  CentralLogics::send_mail($plan->fh_employee->emp_email, new ApprovalMail($mailData));
                                }
                            } elseif ($request->POST_TYPE == 'LOAN_REQUEST_APPROVAL_APP') {
                                $plan = LoanRequest::with('fh_employee:emp_id,emp_fname,emp_email,emp_full_name,emp_d_id')->where((DB::raw('md5(lnr_id)')), $data->lnr_id)->first();

                                // $plan = LoanRequest::with('fh_employee:emp_id,emp_fname,emp_email,emp_full_name,emp_d_id')
                                // ->where(DB::raw('md5(lnr_id)'), md5($data->lnr_id))
                                // ->first();
                                // dd($plan);
                                if ($plan->fh_employee()->exists()) {
                                    $placeholders = [
                                        '{receiver_name}' => $plan->fh_employee->emp_full_name,
                                        '{reason}' => $plan->lnr_description,
                                        '{approver_name}' => $user->emp_full_name,
                                        '{approver_designation}' => $user->fh_designation->dg_name,
                                        '{approver_phone}' => $user->emp_phone,
                                        '{rejection_remark}' => $plan->fh_plan_approval_log()->where('log_user_id', $user->emp_id)->pluck('log_description')->first(),
                                    ];
                                    $recipientEmail = $plan->fh_employee->emp_email; // $plan->fh_employee->emp_email; // Replace with dynamic recipient email
                                    $templateType = 391; // Replace with your mail template type
                                    $businessId = $user->emp_b_id; // Replace with your business ID if applicable
                                    $sent = CentralLogics::sendCustomEmail($templateType, $placeholders, $recipientEmail, $businessId);

                                    //  $mailData = [
                                    //      'subject' => 'Gatepass Request - Rejected',
                                    //      'url' => route('requests.gate-pass.show', ['id' => md5($plan->gtp_id)]),
                                    //      'mail_type' => 'GATEPASS_APPROVAL_REQUEST_REJECT',
                                    //      'data' => ['gatepassData' => $plan, 'receiverName' => $plan->fh_employee->emp_full_name, 'approver' => $user],
                                    //  ];
                                    //  CentralLogics::send_mail($plan->fh_employee->emp_email, new ApprovalMail($mailData));
                                }
                            } elseif ($request->POST_TYPE == 'LEAVE_REQUEST_APPROVAL') {
                                $plan = LeaveRequest::with('fh_employee:emp_id,emp_fname,emp_email,emp_full_name,emp_d_id')->where((DB::raw('md5(lvr_id)')), $data->lvr_id)->first();
                                if ($plan->fh_employee()->exists()) {
                                    $placeholders = [
                                        '{receiver_name}' => $plan->fh_employee->emp_full_name,
                                        '{employee_code}' => $plan->fh_employee->emp_code,
                                        '{employee_name}' => $plan->fh_employee->emp_full_name,
                                        '{submit_date}' => $plan->created_at->format('d M, Y'),
                                        '{start_date}' => $plan->lvr_start_date->format('d M, Y'),
                                        '{end_date}' => $plan->lvr_end_date->format('d M, Y'),
                                        '{leave_type}' => optional($plan->fh_leave_day_type)->m_name ?? 'N/A',
                                        '{leave_category}' => optional($plan->fh_leave_cat_type)->m_name ?? 'N/A',
                                        '{day_segment}' => optional($plan->fh_leave_day_segment)->m_name ?  '(' . optional($plan->fh_leave_day_segment)->m_name . ')' :  '',
                                        '{start_date}' => $plan->lvr_start_date,
                                        '{to_date}' => $plan->lvr_end_date,
                                        '{reason}' => $plan->lvr_reason,
                                        '{approver_name}' => $user->emp_full_name,
                                        '{approver_designation}' => $user->fh_designation->dg_name,
                                        '{approver_phone}' => $user->emp_phone,
                                        '{rejection_remark}' => $plan->fh_plan_approval_log()->where('log_user_id', $user->emp_id)->pluck('log_description')->first(),
                                    ];
                                    $recipientEmail =  $plan->fh_employee->emp_email; // $plan->fh_employee->emp_email; // Replace with dynamic recipient email
                                    $templateType = 392; // Replace with your mail template type
                                    $businessId = $user->emp_b_id; // Replace with your business ID if applicable
                                    $sent = CentralLogics::sendCustomEmail($templateType, $placeholders, $recipientEmail, $businessId);
                                    // $mailData = [
                                    //     'subject' => 'Leave Request - Rejected',
                                    //     'url' => route('leave.requests.show', ['id' => md5($plan->lvr_id)]),
                                    //     'mail_type' => 'LEAVE_APPROVAL_REQUEST_REJECT',
                                    //     'data' => ['leaveData' => $plan, 'receiverName' => $plan->fh_employee->emp_full_name, 'approver' => $user ],
                                    // ];
                                    // CentralLogics::send_mail($plan->fh_employee->emp_email, new ApprovalMail($mailData));
                                }
                            } elseif ($request->POST_TYPE == 'OUTDOOR_REQUEST_APPROVAL') {
                                $plan = AttendanceOutDoor::with('fh_employee:emp_id,emp_fname,emp_email,emp_full_name,emp_d_id')
                                    ->where((DB::raw('md5(atd_od_id)')), $data->atd_od_id)->first();
                                if ($plan->fh_employee()->exists()) {
                                    $placeholders = [
                                        '{receiver_name}'        => $plan->fh_employee->emp_full_name,
                                        '{date}'                 => Carbon::parse($plan->atd_od_date)->format('d-M-Y'),
                                        '{in_time}'              => $plan->atd_od_check_in_time ?? 'N/A',
                                        '{out_time}'             => $plan->atd_od_check_out_time ?? 'N/A',
                                        '{reason}'               => $plan->atd_od_remark ?? 'N/A',
                                        '{approver_name}'        => $user->emp_full_name,
                                        '{approver_designation}' => $user->fh_designation->dg_name,
                                        '{approver_phone}'       => $user->emp_phone,
                                        '{rejection_remark}'     => $plan->fh_plan_approval_log()
                                                                        ->where('log_user_id', $user->emp_id)
                                                                        ->pluck('log_description')->first(),
                                    ];
                                    $recipientEmail = $plan->fh_employee->emp_email;
                                    $templateType   = 391; // outdoor rejection template ID (adjust as needed)
                                    $businessId     = $user->emp_b_id;
                                    CentralLogics::sendCustomEmail($templateType, $placeholders, $recipientEmail, $businessId);
                                }
                            }
                        }
                        if ($receiver_group_id == 153) {
                            if ($request->POST_TYPE == 'TRAVEL_REQUEST_APPROVAL') {
                                $approvalModule = ApprovalModule::with(['fh_process_approvers' => function ($query) use ($user) {
                                    $query->where('pa_emp_id', '!=', $user->emp_id)
                                        ->with(['fh_employee:emp_id,emp_fname,emp_email,emp_full_name,emp_d_id']);
                                }])->where((DB::raw('md5(am_id)')), $data->module_id)
                                    ->where(['am_status' => 1])
                                    ->first();
                                $emp_d_id = $request->data['emp_d_id'];
                                foreach ($approvalModule->filteredProcessApprovers($emp_d_id)->get() as $approval) {
                                    if ($approval->fh_employee->exists()) {
                                        $placeholders = [
                                            '{travel_type}' => $plan->fh_policy_tada_travel_type->fh_travel_type->m_name,
                                            '{receiver_name}' => $approval->fh_employee->emp_full_name,
                                            '{employee_name}' => $plan->fh_employee->emp_full_name,
                                            '{employee_code}' => $plan->fh_employee->emp_code,
                                            '{request_id}' => $plan->trp_unique_id,
                                            '{submit_date}' => $plan->created_at->format('d M, Y'),
                                            '{destination}' => $plan->trp_destination ?? 'N/A',
                                            '{purpose}' => optional($plan->fh_travel_purpose)->tp_name,
                                            '{start_date}' => Carbon::parse($plan->trp_start_date)->format('d M, Y'),
                                            '{start_time}' => Carbon::parse($plan->trd_start_time)->format('h:i A'),
                                            '{end_date}' => Carbon::parse($plan->trp_end_date)->format('d M, Y'),
                                            '{end_time}' => Carbon::parse($plan->trd_end_time)->format('h:i A'),
                                            '{approver_name}' => $user->emp_full_name,
                                            '{approver_designation}' => $user->fh_designation->dg_name,
                                            '{approver_phone}' => $user->emp_phone,
                                            '{rejection_remark}' => $plan->fh_plan_approval_log()->where('log_user_id', $user->emp_id)->orderBy('log_id', 'desc')->pluck('log_description')->first(),

                                        ];
                                        $recipientEmail =  $approval->fh_employee->emp_email; // $plan->fh_employee->emp_email; // Replace with dynamic recipient email
                                        $templateType = 393; // Replace with your mail template type
                                        $businessId = $user->emp_b_id; // Replace with your business ID if applicable
                                        $sent = CentralLogics::sendCustomEmail($templateType, $placeholders, $recipientEmail, $businessId);
                                        // $mailData = [
                                        //     'subject' => 'Notification: Tour Request - ' . $plan->fh_policy_tada_travel_type->fh_travel_type->m_name . ' Rejected',
                                        //     'url' => route('travel.request.show', ['id' => md5($plan->trp_id)]),
                                        //     'mail_type' => 'TOUR_APPROVAL_REJECT',
                                        //     'data' => ['planData' => $plan, 'receiverName' => $approval->fh_employee->emp_fname, 'approver' => $user],
                                        // ];
                                        // CentralLogics::send_mail($approval->fh_employee->emp_email, new ApprovalMail($mailData));
                                    }
                                }
                            } elseif ($request->POST_TYPE == 'CLAIM_REQUEST_APPROVAL') {
                                $claim = TadaClaim::where((DB::raw('md5(tc_id)')), $data->tc_id)->first();

                                $approvalModule = ApprovalModule::with(['fh_process_approvers' => function ($query) use ($user) {
                                    $query->where('pa_emp_id', '!=', $user->emp_id)
                                        ->with(['fh_employee:emp_id,emp_fname,emp_email,emp_full_name,emp_d_id']);
                                }])->where((DB::raw('md5(am_id)')), $data->module_id)
                                    ->where(['am_status' => 1])
                                    ->first();
                                $emp_d_id = $request->data['emp_d_id'];
                                foreach ($approvalModule->filteredProcessApprovers($emp_d_id)->get() as $approval) {
                                    if ($approval->fh_employee->exists()) {
                                        if ($claim->fh_tada_request_plan && $claim->fh_tada_request_plan->fh_tada_expenses()->exists()) {
                                            $expenses = $claim->fh_tada_request_plan->fh_tada_expenses ?? []; //$data['data']['claimData']->fh_tada_request_plan->fh_tada_expenses ?? [];
                                            if ($expenses->isNotEmpty()) {
                                                $expenseSummary = "Below is a brief summary of expenses:\n\n";
                                                $expenseSummary .= "S.No.\tParticulars\tAmount\n";
                                                $i = 1;
                                                foreach ($expenses->groupBy('te_type_id') as $tadaExpenseItem) {
                                                    $particulars = $tadaExpenseItem[0]->fh_expense_type->m_name ?? 'N/A';
                                                    $amount = $tadaExpenseItem->sum('te_amount') + $tadaExpenseItem->sum('te_taxes');
                                                    $expenseSummary .= "$i\t$particulars\t$amount\n";
                                                    $i++;
                                                }
                                                $totalExpenses = $expenses->sum('te_amount') + $expenses->sum('te_taxes');
                                                $expenseSummary .= "\nTotal Expenses: $totalExpenses\n";
                                            } else {
                                                $expenseSummary = "No expenses were reported.";
                                            }
                                        }

                                        $placeholders = [
                                            '{receiver_name}' => $approval->fh_employee->emp_full_name,
                                            '{employee_name}' => $claim->fh_employee->emp_full_name,
                                            '{employee_code}' => $claim->fh_employee->emp_code,
                                            '{request_id}' => $claim->tc_unique_id,
                                            '{ref_id}' => $claim->fh_tada_request_plan->trp_unique_id,
                                            '{submit_date}' => $claim->created_at->format('d M, Y'),
                                            '{approver_name}' => $user->emp_full_name,
                                            '{approver_designation}' => $user->fh_designation->dg_name,
                                            '{approver_phone}' => $user->emp_phone,
                                            '{expense_summary}' => isset($expenseSummary) ? $expenseSummary : '',
                                            '{rejection_remark}' => $claim->fh_claim_approval_log()->where('log_user_id', $user->emp_id)->orderBy('log_id', 'desc')->pluck('log_description')->first(),

                                        ];
                                        $recipientEmail =  $approval->fh_employee->emp_email; // $plan->fh_employee->emp_email; // Replace with dynamic recipient email
                                        $templateType = 394; // Replace with your mail template type
                                        $businessId = $user->emp_b_id; // Replace with your business ID if applicable
                                        $sent = CentralLogics::sendCustomEmail($templateType, $placeholders, $recipientEmail, $businessId);

                                        // $mailData = [
                                        //     'subject' => 'Notification: Tour Request - ' . $claim->fh_tada_request_plan->fh_policy_tada_travel_type->fh_travel_type->m_name . ' Rejected',
                                        //     'url' => route('claim.request.show', ['id' => md5($claim->fh_tada_request_plan->trp_id)]),
                                        //     'mail_type' => 'CLAIM_REQUEST_REJECT',
                                        //     'data' => ['claimData' => $claim, 'receiverName' => $approval->fh_employee->emp_fname, 'approver' => $user],
                                        // ];
                                        // CentralLogics::send_mail($approval->fh_employee->emp_email, new ApprovalMail($mailData));
                                    }
                                }
                            } elseif ($request->POST_TYPE == 'MISPUNCH_REQUEST_APPROVAL') {
                                ;
                                $approvalModule = ApprovalModule::with(['fh_process_approvers' => function ($query) use ($user) {
                                    $query->where('pa_emp_id', '!=', $user->emp_id)
                                        ->with(['fh_employee:emp_id,emp_fname,emp_email,emp_full_name,emp_d_id']);
                                }])->where((DB::raw('md5(am_id)')), $data->module_id)
                                    ->where(['am_status' => 1])
                                    ->first();
                                $emp_d_id = $request->data['emp_d_id'];

                                foreach ($approvalModule->filteredProcessApprovers($emp_d_id)->get() as $approval) {
                                    if ($approval->fh_employee->exists()) {
                                        $placeholders = [
                                            '{reciver_name}' => $approval->fh_employee->emp_full_name,
                                            '{employee_name}' => $plan->fh_employee->emp_full_name,
                                            '{employee_code}' => $plan->fh_employee->emp_code,
                                            '{date}' => Carbon::parse($plan->ae_date)->format('d-M-Y'),
                                            '{in_time}' => $plan->ae_in_time,
                                            '{out_time}' => $plan->ae_out_time,
                                            '{working_hour}' => $plan->ae_total_working,
                                            '{reason}' => $plan->ae_reason_id,
                                            '{approver_name}' => $user->emp_full_name,
                                            '{approver_designation}' => $user->fh_designation->dg_name,
                                            '{approver_phone}' => $user->emp_phone,
                                            '{rejection_remark}' => $plan->fh_plan_approval_log()->where('log_user_id', $user->emp_id)->pluck('log_description')->first(),

                                        ];
                                        $recipientEmail = $approval->fh_employee->emp_email; // Replace with dynamic recipient email
                                        $templateType = 395; // Replace with your mail template type
                                        $businessId = $user->emp_b_id; // Replace with your business ID if applicable
                                        $sent = CentralLogics::sendCustomEmail($templateType, $placeholders, $recipientEmail, $businessId);
                                        $employee =\App\Models\Employee::find($plan->fh_employee->emp_id);

                                        $title = 'Miss Punch'.($data->approval_type==1? 'Approved' : 'Rejected');
                                        $body = "Your miss punch request has been ". ($data->approval_type == 1 ? 'Approved' : 'Rejected');
                                        $additionalData = [
                                            'notification_type' => 'misspunch_status',
                                            'status' => $data->approval_type == 1 ? 'approved' : 'rejected',
                                            'route'=>'/MisPunchList',
                                            'remark'=>$data->message,
                                        ];

                                        if ($employee && $employee->emp_fcm_token) {
                                            $serviceAccountPath = public_path('fixhr-app-firebase.json');
                                            FirebaseNotification::sendPushNotification(
                                                $title,
                                                $body,
                                                $employee->emp_fcm_token,
                                                $serviceAccountPath,
                                                config('credentials')['FIREBASE_MESSAGING_CONFIG'],
                                                $additionalData
                                            );
                                        }
                                        NotificationHelper::saveNotification(
                                            $user->emp_id,
                                            $employee->emp_id,
                                            $title,
                                            $body,
                                            $additionalData
                                        );

                                        // $mailData = [
                                        //     'subject' => 'Notification: Mis-Punch Request - Rejected',
                                        //     'url' => route('mis-punch.show', ['mis_punch' => md5($plan->ae_id)]),
                                        //     'mail_type' => 'MISPUNCH_APPROVAL_REJECT',
                                        //     'data' => ['mispunchData' => $plan, 'receiverName' => $approval->fh_employee->emp_full_name, 'approver' => $user],
                                        // ];
                                        // CentralLogics::send_mail($approval->fh_employee->emp_email, new ApprovalMail($mailData));
                                    }
                                }
                            } elseif ($request->POST_TYPE == 'GATEPASS_REQUEST_APPROVAL') {

                                $approvalModule = ApprovalModule::with(['fh_process_approvers' => function ($query) use ($user) {
                                    $query->where('pa_emp_id', '!=', $user->emp_id)
                                        ->with(['fh_employee:emp_id,emp_fname,emp_email,emp_full_name,emp_d_id']);
                                }])->where((DB::raw('md5(am_id)')), $data->module_id)
                                    ->where(['am_status' => 1])
                                    ->first();
                                $emp_d_id = $request->data['emp_d_id'];
                                foreach ($approvalModule->filteredProcessApprovers($emp_d_id)->get() as $approval) {
                                    if ($approval->fh_employee->exists()) {
                                        $placeholders = [
                                            '{reciver_name}' => $approval->fh_employee->emp_full_name,
                                            '{employee_name}' => $plan->fh_employee->emp_full_name,
                                            '{employee_code}' => $plan->fh_employee->emp_code,
                                            '{date}' => Carbon::parse($plan->gtp_date)->format('d-M-Y'),
                                            '{in_time}' => $plan->gtp_in_time,
                                            '{out_time}' => $plan->gtp_out_time,
                                            '{destination}' => $plan->gtp_destination,
                                            '{reason}' => $plan->gtp_reason,
                                            '{approver_name}' => $user->emp_full_name,
                                            '{approver_designation}' => $user->fh_designation->dg_name,
                                            '{approver_phone}' => $user->emp_phone,
                                            '{rejection_remark}' => $plan->fh_plan_approval_log()->where('log_user_id', $user->emp_id)->pluck('log_description')->first(),

                                        ];
                                        $recipientEmail = $approval->fh_employee->emp_email; // Replace with dynamic recipient email
                                        $templateType = 396; // Replace with your mail template type
                                        $businessId = $user->emp_b_id; // Replace with your business ID if applicable
                                        $sent = CentralLogics::sendCustomEmail($templateType, $placeholders, $recipientEmail, $businessId);


                                      $employee = \App\Models\Employee::find($plan->gtp_emp_id);

                                    $title = 'Gate Pass ' . ($data->approval_type == 1 ? 'Approved' : 'Rejected');
                                    $body = "Your gate pass request has been ". ($data->approval_type == 1 ? 'Approved' : 'Rejected');
                                        $additionalData = [
                                            'notification_type' => 'gatepass_status',
                                        'status' => $data->approval_type == 1 ? 'approved' : 'rejected',
                                            'route'=>'/GetPassApprovalList',
                                            'remark'=>$data->message,
                                        ];
                                    if ($employee && $employee->emp_fcm_token) {
                                        $serviceAccountPath = public_path('fixhr-app-firebase.json');
                                        FirebaseNotification::sendPushNotification(
                                            $title,
                                            $body,
                                            $employee->emp_fcm_token,
                                            $serviceAccountPath,
                                            config('credentials')['FIREBASE_MESSAGING_CONFIG'],
                                            $additionalData
                                        );
                                    }
                                    NotificationHelper::saveNotification(
                                        $user->emp_id,
                                        $employee->emp_id,
                                        $title,
                                        $body,
                                        $additionalData
                                    );

                                        // $mailData = [
                                        //     'subject' => 'Notification: Gatepass Request - Rejected',
                                        //     'url' => route('requests.gate-pass.show', ['id' => md5($plan->gtp_id)]),
                                        //     'mail_type' => 'GATEPASS_APPROVAL_REJECT',
                                        //     'data' => ['gatepassData' => $plan, 'receiverName' => $approval->fh_employee->emp_full_name, 'approver' => $user],
                                        // ];
                                        // CentralLogics::send_mail($approval->fh_employee->emp_email, new ApprovalMail($mailData));
                              
                                    }
                                }
                            } elseif ($request->POST_TYPE == 'LOAN_REQUEST_APPROVAL') {
                                $approvalModule = ApprovalModule::with(['fh_process_approvers' => function ($query) use ($user) {
                                    $query->where('pa_emp_id', '!=', $user->emp_id)
                                        ->with(['fh_employee:emp_id,emp_fname,emp_email,emp_full_name,emp_d_id']);
                                }])->where((DB::raw('md5(am_id)')), $data->module_id)
                                    ->where(['am_status' => 1])
                                    ->first();
                                $emp_d_id = $request->data['emp_d_id'];
                                foreach ($approvalModule->filteredProcessApprovers($emp_d_id)->get() as $approval) {
                                    if ($approval->fh_employee->exists()) {
                                        $placeholders = [
                                            '{reciver_name}' => $approval->fh_employee->emp_full_name,
                                            '{employee_name}' => $plan->fh_employee->emp_full_name,
                                            '{employee_code}' => $plan->fh_employee->emp_code,
                                            '{date}' => Carbon::parse($plan->lnr_start_date)->format('d-M-Y'),
                                            '{reason}' => $plan->lnr_description,
                                            '{approver_name}' => $user->emp_full_name,
                                            '{approver_designation}' => $user->fh_designation->dg_name,
                                            '{approver_phone}' => $user->emp_phone,
                                            '{rejection_remark}' => $plan->fh_plan_approval_log()->where('log_user_id', $user->emp_id)->pluck('log_description')->first(),

                                        ];
                                        $recipientEmail = $approval->fh_employee->emp_email; // Replace with dynamic recipient email
                                        $templateType = 396; // Replace with your mail template type
                                        $businessId = $user->emp_b_id; // Replace with your business ID if applicable
                                        $sent = CentralLogics::sendCustomEmail($templateType, $placeholders, $recipientEmail, $businessId);

                                        // $mailData = [
                                        //     'subject' => 'Notification: Gatepass Request - Rejected',
                                        //     'url' => route('requests.gate-pass.show', ['id' => md5($plan->gtp_id)]),
                                        //     'mail_type' => 'GATEPASS_APPROVAL_REJECT',
                                        //     'data' => ['gatepassData' => $plan, 'receiverName' => $approval->fh_employee->emp_full_name, 'approver' => $user],
                                        // ];
                                        // CentralLogics::send_mail($approval->fh_employee->emp_email, new ApprovalMail($mailData));
                          

                                    }
                                }
                            } elseif ($request->POST_TYPE == 'LOAN_REQUEST_APPROVAL_APP') {
                                $approvalModule = ApprovalModule::with(['fh_process_approvers' => function ($query) use ($user) {
                                    $query->where('pa_emp_id', '!=', $user->emp_id)
                                        ->with(['fh_employee:emp_id,emp_fname,emp_email,emp_full_name,emp_d_id']);
                                }])->where((DB::raw('md5(am_id)')), $data->module_id)
                                    ->where(['am_status' => 1])
                                    ->first();
                                $emp_d_id = $request->data['emp_d_id'];
                                foreach ($approvalModule->filteredProcessApprovers($emp_d_id)->get() as $approval) {
                                    if ($approval->fh_employee->exists()) {
                                        $placeholders = [
                                            '{reciver_name}' => $approval->fh_employee->emp_full_name,
                                            '{employee_name}' => $plan->fh_employee->emp_full_name,
                                            '{employee_code}' => $plan->fh_employee->emp_code,
                                            '{date}' => Carbon::parse($plan->lnr_start_date)->format('d-M-Y'),
                                            '{reason}' => $plan->lnr_description,
                                            '{approver_name}' => $user->emp_full_name,
                                            '{approver_designation}' => $user->fh_designation->dg_name,
                                            '{approver_phone}' => $user->emp_phone,
                                            '{rejection_remark}' => $plan->fh_plan_approval_log()->where('log_user_id', $user->emp_id)->pluck('log_description')->first(),

                                        ];
                                        $recipientEmail = $approval->fh_employee->emp_email; // Replace with dynamic recipient email
                                        $templateType = 396; // Replace with your mail template type
                                        $businessId = $user->emp_b_id; // Replace with your business ID if applicable
                                        $sent = CentralLogics::sendCustomEmail($templateType, $placeholders, $recipientEmail, $businessId);

                                        // $mailData = [
                                        //     'subject' => 'Notification: Gatepass Request - Rejected',
                                        //     'url' => route('requests.gate-pass.show', ['id' => md5($plan->gtp_id)]),
                                        //     'mail_type' => 'GATEPASS_APPROVAL_REJECT',
                                        //     'data' => ['gatepassData' => $plan, 'receiverName' => $approval->fh_employee->emp_full_name, 'approver' => $user],
                                        // ];
                                        // CentralLogics::send_mail($approval->fh_employee->emp_email, new ApprovalMail($mailData));
                            

                                    }
                                }
                            } elseif ($request->POST_TYPE == 'LEAVE_REQUEST_APPROVAL') {

                                $approvalModule = ApprovalModule::with(['fh_process_approvers' => function ($query) use ($user) {
                                    $query->where('pa_emp_id', '!=', $user->emp_id)
                                        ->with(['fh_employee:emp_id,emp_fname,emp_email,emp_full_name,emp_d_id']);
                                }])->where((DB::raw('md5(am_id)')), $data->module_id)
                                    ->where(['am_status' => 1])
                                    ->first();
                                $emp_d_id = $request->data['emp_d_id'];
                                foreach ($approvalModule->filteredProcessApprovers($emp_d_id)->get() as $approval) {
                                    if ($approval->fh_employee->exists()) {
                                        $placeholders = [
                                            '{receiver_name}' => $approval->fh_employee->emp_full_name,
                                            '{employee_name}' => $plan->fh_employee->emp_full_name,
                                            '{employee_code}' => $plan->fh_employee->emp_code,
                                            '{start_date}' => Carbon::parse($plan->lvr_start_date)->format('d-M-Y'),
                                            '{end_date}' => Carbon::parse($plan->lvr_end_date)->format('d-M-Y'),
                                            '{submit_date}' => Carbon::parse($plan->created_at)->format('d-M-Y'),
                                            '{leave_type}' => optional($plan->fh_leave_day_type)->m_name,
                                            '{leave_category}' =>  optional($plan->fh_leave_cat_type)->m_name ?? 'N/A',
                                            '{day_segment}' => optional($plan->fh_leave_day_segment)->m_name ?  '(' . optional($plan->fh_leave_day_segment)->m_name . ')' :  '',
                                            '{destination}' => $plan->gtp_destination,
                                            '{reason}' => $plan->lvr_reason ?? 'N/A',
                                            '{approver_name}' => $user->emp_full_name,
                                            '{approver_designation}' => $user->fh_designation->dg_name,
                                            '{approver_phone}' => $user->emp_phone,
                                            '{rejection_remark}' => $plan->fh_plan_approval_log()->where('log_user_id', $user->emp_id)->pluck('log_description')->first(),
                                        ];
                                        $recipientEmail = $approval->fh_employee->emp_email; // Replace with dynamic recipient email
                                        $templateType = 397; // Replace with your mail template type
                                        $businessId = $user->emp_b_id; // Replace with your business ID if applicable
                                        $sent = CentralLogics::sendCustomEmail($templateType, $placeholders, $recipientEmail, $businessId);

                                        $employee =Employee::find($plan->lvr_emp_id);
                                        $title = 'Leave' . ($data->approval_type == 1 ? 'Approved' : 'Rejected');
                                        $body = 'Your leave has been'. ($data->approval_type == 1 ? 'Approved' : 'Rejected');
                                        $additionalData = [
                                            'notification_type' => 'leave_status',
                                            'status' => $data->approval_type == 1 ? 'approved' : 'rejected',
                                            'route'=>'/LeaveApplications',
                                            'remark'=>$data->message,
                                        ];

                                        if ($employee && $employee->emp_fcm_token) {
                                            $serviceAccountPath = public_path('fixhr-app-firebase.json');
                                            FirebaseNotification::sendPushNotification(
                                                $title,
                                                $body,
                                                $employee->emp_fcm_token,
                                                $serviceAccountPath,
                                                config('credentials')['FIREBASE_MESSAGING_CONFIG'],
                                                $additionalData
                                            );
                                        }
                                        NotificationHelper::saveNotification(
                                            $user->emp_id,
                                            $employee->emp_id,
                                            $title,
                                            $body,
                                            $additionalData
                                        );

                                        // $mailData = [
                                        //     'subject' => 'Notification: Leave Request - Rejected',
                                        //     'url' => route('leave.requests.show', ['id' => md5($plan->lvr_id)]),
                                        //     'mail_type' => 'LEAVE_APPROVAL_REJECT',
                                        //     'data' => ['leaveData' => $plan, 'receiverName' => $approval->fh_employee->emp_full_name, 'approver' => $user],
                                        // ];
                                        // CentralLogics::send_mail($approval->fh_employee->emp_email, new ApprovalMail($mailData));
                                    }
                                }
                            } elseif ($request->POST_TYPE == 'OUTDOOR_REQUEST_APPROVAL') {
                                $approvalModule = ApprovalModule::with(['fh_process_approvers' => function ($query) use ($user) {
                                    $query->where('pa_emp_id', '!=', $user->emp_id)
                                          ->with(['fh_employee:emp_id,emp_fname,emp_email,emp_full_name,emp_d_id']);
                                }])->where((DB::raw('md5(am_id)')), $data->module_id)
                                  ->where(['am_status' => 1])->first();

                                $emp_d_id = $request->data['emp_d_id'];
                                foreach ($approvalModule->filteredProcessApprovers($emp_d_id)->get() as $approval) {
                                    if ($approval->fh_employee->exists()) {
                                        $placeholders = [
                                            '{receiver_name}'        => $approval->fh_employee->emp_full_name,
                                            '{employee_name}'        => $plan->fh_employee->emp_full_name,
                                            '{employee_code}'        => $plan->fh_employee->emp_code,
                                            '{date}'                 => Carbon::parse($plan->atd_od_date)->format('d-M-Y'),
                                            '{in_time}'              => $plan->atd_od_check_in_time ?? 'N/A',
                                            '{out_time}'             => $plan->atd_od_check_out_time ?? 'N/A',
                                            '{approver_name}'        => $user->emp_full_name,
                                            '{approver_designation}' => $user->fh_designation->dg_name,
                                            '{approver_phone}'       => $user->emp_phone,
                                            '{rejection_remark}'     => $plan->fh_plan_approval_log()
                                                                            ->where('log_user_id', $user->emp_id)
                                                                            ->pluck('log_description')->first(),
                                        ];
                                        $recipientEmail = $approval->fh_employee->emp_email;
                                        $templateType   = 396; // adjust
                                        $businessId     = $user->emp_b_id;
                                        CentralLogics::sendCustomEmail($templateType, $placeholders, $recipientEmail, $businessId);

                                        // Firebase push notification
                                        $employee = \App\Models\Employee::find($plan->atd_od_emp_id);
                                        $title = 'Out Door Attendance ' . ($data->approval_type == 1 ? 'Approved' : 'Rejected');
                                        $body  = 'Your out door attendance request has been ' . ($data->approval_type == 1 ? 'Approved' : 'Rejected');
                                        $additionalData = [
                                            'notification_type' => 'outdoor_status',
                                            'status'            => $data->approval_type == 1 ? 'approved' : 'rejected',
                                            'route'             => '/OutDoorApprovalPage',
                                            'remark'            => $data->message,
                                        ];
                                        if ($employee && $employee->emp_fcm_token) {
                                            $serviceAccountPath = public_path('fixhr-app-firebase.json');
                                            FirebaseNotification::sendPushNotification(
                                                $title, $body, $employee->emp_fcm_token,
                                                $serviceAccountPath,
                                                config('credentials')['FIREBASE_MESSAGING_CONFIG'],
                                                $additionalData
                                            );
                                        }
                                        NotificationHelper::saveNotification($user->emp_id, $employee->emp_id, $title, $body, $additionalData);
                                    }
                                }
                            }
                        }
                    }
                }
                $statusData = MasterTable::where(['m_group' => 'APPROVAL_STATUS', 'm_id' => 170])->select('m_id', 'm_name')->first(); //170 == 'Rejected'

                $this->updateRequestStatusAndLog($data, $statusData, 0, $request->POST_TYPE, $isLast);

                $response['result'] = true;
                $response['status'] = true;
                $response['message'] = 'Request ' . $statusData->m_name;
            }
        }
        return response()->json($response);
    }

    public function  updateRequestStatusAndLog($data, $statusData, $sequence, $postType = '', $isLast = 0)
    {
        $user = Auth::user();
        if ($postType == 'TRAVEL_REQUEST_APPROVAL') {
            $plan = TadaRequestPlan::where((DB::raw('md5(trp_id)')), $data->trp_id)->first();


            ApprovalLog::create([
                'log_am_id' => $plan->trp_am_id,
                'log_request_id' => $plan->trp_id,
                'log_user_id' => $user->emp_id,
                'log_user_role_id' => $user->emp_role_id,
                'log_status' => $statusData->m_id,
                'log_description' => $data->message,
                'log_module_id' => $plan->trp_module_id,
            ]);

            if ($isLast) {
                $ruleCriteria = RuleCriterion::where('rc_b_id', $user->emp_b_id)->whereNot('rc_am_id', $plan->trp_am_id)->where('rc_condition_option_id', $statusData->m_id)
                    ->whereHas('fh_approval_module', function ($query) {
                        $query->where('am_module_id', 145)->where('am_status', 1);
                    })->first();

                if (!$ruleCriteria) {
                    $amId = $plan->trp_am_id;
                    $isCompleted = 1;
                } else {
                    $amId = $ruleCriteria->rc_am_id;
                    $isCompleted = 0;
                }
                $plan->update(['trp_request_status' => $statusData->m_id, 'trp_next_approver' => 1, 'trp_am_id' => $amId, 'trp_stage_completed' => $isCompleted]);
                // section for mail template start
                // Define placeholders dynamically
                $placeholders = [
                    '{approver_name}' => $user->emp_full_name ?? '',
                    '{receiver_name}' => $user->emp_full_name,
                    '{employee_name}' => optional($plan->fh_employee)->emp_full_name ?? 'N/A',
                    '{employee_code}' => optional($plan->fh_employee)->emp_code ?? 'N/A',
                    '{travel_id}' => $plan->trp_unique_id ?? 'N/A',
                    '{request_id}' => $plan->trp_unique_id ?? 'N/A',
                    '{trip_name}' => $plan->trp_name ?? 'N/A',
                    '{travel_purpose}' => $plan->trp_purpose ?? 'N/A',
                    '{destination}' => $plan->trp_destination ?? 'N/A',
                    '{travel_type}' => optional($plan->fh_policy_tada_travel_type->fh_travel_type)->m_name ?? 'N/A',
                    '{departure_date}' => $plan->trp_start_date ?? 'N/A'  . ' ' . $plan->trp_start_time ?? 'N/A',
                    '{return_date}' => $plan->trp_end_date ?? 'N/A' . ' ' . $plan->trp_end_time ?? 'N/A',
                    '{start_date_time}' => $plan->trp_start_date ?? 'N/A'  . ' ' . $plan->trp_start_time ?? 'N/A',
                    '{end_date_time}' => $plan->trp_end_date ?? 'N/A' . ' ' . $plan->trp_end_time ?? 'N/A',
                    '{advance_amount}' => $plan->trp_advance_allowance ?? 'N/A',
                    '{submitted_date}' => $plan->updated_at,
                    '{portal_url}' => route('login'),
                ];
                $recipientEmail = optional($plan->fh_employee)->emp_email;
                $templateType = 383; // Replace with your mail template type
                $businessId = $user->emp_b_id; // Replace with your business ID if applicable
                $sent = CentralLogics::sendCustomEmail($templateType, $placeholders, $recipientEmail, $businessId);
                // section for mail template end

                // Notify requester (final decision)
                try {
                    $employee = $plan->fh_employee; // requester
                    if ($employee) {
                        $title = 'Travel Request ' . ($statusData->m_id == 169 ? 'Approved' : ($statusData->m_id == 170 ? 'Rejected' : 'Updated'));
                        $body  = 'Your travel request ' . ($plan->trp_unique_id ?? '') . ' has been '
                            . ($statusData->m_id == 169 ? 'approved' : ($statusData->m_id == 170 ? 'rejected' : 'updated')) . '.';
                        $additionalData = [
                            'notification_type' => 'travel_status',
                            'status' => ($statusData->m_id == 169 ? 'approved' : ($statusData->m_id == 170 ? 'rejected' : 'updated')),
                            'route' => '/TadaApprovalList',
                            'request_id' => $plan->trp_id,
                        ];

                        if (!empty($employee->emp_fcm_token)) {
                            $serviceAccountPath = public_path('fixhr-app-firebase.json');
                            FirebaseNotification::sendPushNotification(
                                $title,
                                $body,
                                $employee->emp_fcm_token,
                                $serviceAccountPath,
                                config('credentials')['FIREBASE_MESSAGING_CONFIG'],
                                $additionalData
                            );
                        }

                        NotificationHelper::saveNotification(
                            $user->emp_id,
                            $employee->emp_id,
                            $title,
                            $body,
                            $additionalData
                        );
                    }
                } catch (\Throwable $e) {
                    \Log::warning('Travel final notification failed: ' . $e->getMessage());
                }
            } else {
                $plan->update(['trp_request_status' => $statusData->m_id, 'trp_next_approver' => $sequence]);

                //start notify next approver & refresh page
                $nextApproverDetails =  ProcessApprover::with([
                    'fh_employee:emp_id,emp_fname,emp_email,emp_full_name,emp_d_id'
                ])->where((DB::raw('md5(pa_am_id)')), $data->module_id)->where(['pa_b_id' => $user->emp_b_id, 'pa_type' => $data->approval_action_type, 'pa_sequence' => $sequence])
                    ->orderBy('pa_am_id', 'asc')->first();

                if (isset($nextApproverDetails) && isset($nextApproverDetails->fh_employee)) {
                    if ($nextApproverDetails->fh_employee->exists()) {
                        $placeholders = [
                            '{travel_type}' => optional($plan->fh_policy_tada_travel_type->fh_travel_type)->m_name ?? 'N/A',
                            '{approver_name}' => optional($nextApproverDetails->fh_employee)->emp_full_name ?? 'John Doe',
                            '{travel_id}' => $plan->trp_unique_id ?? 'N/A',
                            '{submitted_date}' => optional($plan->updated_at)->format('d M, Y') ?? 'N/A',
                            '{portal_url}' => route('login'),
                            '{start_date_time}' => (optional($plan->trp_start_date)->format('d M, Y') ?? 'N/A') . ' ' .
                                (optional($plan->trd_start_time)->format('h:i A') ?? ''),
                            '{end_date_time}' => (optional($plan->trp_end_date)->format('d M, Y') ?? 'N/A') . ' ' .
                                (optional($plan->trd_end_time)->format('h:i A') ?? ''),
                            '{destination}' => $plan->trp_destination ?? 'N/A',
                            '{travel_purpose}' => optional($plan->fh_travel_purpose)->tp_name ?? 'N/A',
                            '{portal_url}' => route('travel.request.show', ['id' => md5($plan->trp_id)]),
                            '{employee_name}' => optional($plan->fh_employee)->emp_full_name ?? 'N/A',
                            '{employee_designation}' => optional($plan->fh_employee->fh_designation)->dg_name ?? 'N/A',
                            '{employee_phone_number}' => optional($plan->fh_employee)->emp_phone ?? 'N/A',
                            '{employee_code}' => optional($plan->fh_employee)->emp_code ?? 'N/A',
                        ];

                        $recipientEmail = optional($nextApproverDetails->fh_employee)->emp_email;
                        $templateType = 383; // Replace with your mail template type
                        $businessId = $user->emp_b_id; // Replace with your business ID if applicable
                        $sent = CentralLogics::sendCustomEmail($templateType, $placeholders, $recipientEmail, $businessId);
                     // Push notification to next approver (intermediate step)
                        try {
                            // Check if we have hierarchy-based approver
                            if ($nextApproverDetails && $nextApproverDetails->fh_employee) {
                                $approverEmp = $nextApproverDetails->fh_employee;
                                $title = 'Travel Approval Pending';
                                $body  = 'Approval required for travel ' . ($plan->trp_unique_id ?? '');
                                $additionalData = [
                                    'notification_type' => 'travel_approval',
                                    'status' => 'pending',
                                    'route' => '/TadaApprovalList',
                                    'request_id' => $plan->trp_id,
                                ];

                                if (!empty($approverEmp->emp_fcm_token)) {
                                    $serviceAccountPath = public_path('fixhr-app-firebase.json');
                                    FirebaseNotification::sendPushNotification(
                                        $title,
                                        $body,
                                        $approverEmp->emp_fcm_token,
                                        $serviceAccountPath,
                                        config('credentials')['FIREBASE_MESSAGING_CONFIG'],
                                        $additionalData
                                    );
                                }

                                NotificationHelper::saveNotification(
                                    $user->emp_id,
                                    $approverEmp->emp_id,
                                    $title,
                                    $body,
                                    $additionalData
                                );
                            } 
                       
                        } catch (\Throwable $e) {
                            \Log::warning('Travel next-approver notification failed: ' . $e->getMessage());
                        }
                    }
                }
            }
            // event(new TravelApprovalEvent($plan->trp_id));
            $approversList = ProcessApprover::where(['pa_b_id' => $plan->trp_b_id, 'pa_am_id' => $plan->trp_am_id])->pluck('pa_emp_id');
            //$this->addDataToFirebase($plan->trp_id,'TRAVEL_EVENT',$approversList,$plan->trp_b_id,$plan->trp_emp_id,);
        } elseif ($postType == 'CLAIM_REQUEST_APPROVAL') {
            $claim = TadaClaim::where((DB::raw('md5(tc_id)')), $data->tc_id)->first();
            $approvalLog = ApprovalLog::create([
                'log_am_id' => $claim->tc_am_id,
                'log_request_id' => $claim->tc_trp_id,
                'log_user_id' => $user->emp_id,
                'log_user_role_id' => $user->emp_role_id,
                'log_status' => $statusData->m_id,
                'log_description' => $data->message,
                'log_module_id' => $claim->tc_module_id,
            ]);

            $updateData['tc_status'] = $statusData->m_id;
            if ((isset($data->deduction_amount) && $data->deduction_amount)) {
                $updateData['tc_deduction_amount'] = $claim->tc_deduction_amount ? ($claim->tc_deduction_amount + $data->deduction_amount) : $data->deduction_amount;
                $updateData['tc_deduction_status'] = null;
                $updateData['tc_deduction_remarks'] = $data->message;

                NextApprovalDetail::updateOrCreate(
                    ['nxt_tc_id' => $claim->tc_id], // Condition to check
                    [
                        'nxt_am_id' => $claim->tc_am_id,
                        'nxt_approver_sequence' => $sequence,
                        'nxt_approval_type' => $data->approval_action_type,
                        'nxt_is_last' => $isLast,
                    ]
                );

                DeductionLog::create([
                    'dlog_log_id' => $approvalLog->log_id,
                    'dlog_am_id' => $claim->tc_am_id,
                    'dlog_tc_id' => $claim->tc_id,
                    'dlog_user_id' => $user->emp_id,
                    'dlog_user_role_id' => $user->emp_role_id,
                    'dlog_deduction_amount' => $data->deduction_amount,
                    'dlog_remarks' => $data->message,
                    'dlog_additional_info' => isset($data->deduction_info) ? (is_array($data->deduction_info) ? json_encode($data->deduction_info) : $data->deduction_info) : '',
                ]);
                $claim->update($updateData);
                if ($claim->fh_tada_request_plan->fh_employee->exists()) {
                    $claimSummary = "S.No.\tDescription\tAmount\n";
                    $claimSummary .= "1\tClaimed Amount\t" . $claim->tc_amount . " including DA\n";
                    $claimSummary .= "2\Approved Amount\t" . $claim->tc_amount - $claim->tc_deduction_amount . " including DA\n";
                    $claimSummary .= "3\Deductions\t" . $claim->tc_deduction_amount . "\n";

                    $placeholders = [
                        '{subject}' => 'Expense Claim Approved with Deductions - Claim REF ID: ' . $claim->fh_tada_request_plan->trp_unique_id,
                        '{receiver_name}' => $claim->fh_tada_request_plan->fh_employee->emp_full_name,
                        '{request_id}' => $claim->tc_unique_id,
                        '{ref_id}' => $claim->fh_tada_request_plan->trp_unique_id,
                        '{deduction_remark}' => $data->message,
                        '{approver_name}' => $user->emp_full_name,
                        '{approver_designation}' => $user->fh_designation->dg_name,
                        '{approver_phone}' => $user->emp_phone,
                        '{deduction_summary}' => $claimSummary,
                    ];

                    // The recipient's email address
                    $recipientEmail = $claim->fh_tada_request_plan->fh_employee->emp_email;

                    // The email template type and business ID
                    $templateType = 398; // Replace with your mail template type
                    $businessId = $user->emp_b_id; // Replace with your business ID if applicable

                    // Sending the email using the CentralLogics class
                    $sent = CentralLogics::sendCustomEmail($templateType, $placeholders, $recipientEmail, $businessId);

                 // Push: notify requester to accept/reject deduction
                    try {
                        $employee = $claim->fh_tada_request_plan->fh_employee;
                        $title = 'Claim Deduction Review';
                        $body  = 'Deductions added to claim ' . ($claim->tc_unique_id ?? '') . '. Please accept or reject.';
                        $additionalData = [
                            'notification_type' => 'claim_deduction',
                            'action_required' => true,
                            'status' => 'pending',
                            'route' => '/TadaApprovalList',
                            'claim_id' => $claim->tc_id,
                        ];
                        if (!empty($employee->emp_fcm_token)) {
                            $serviceAccountPath = public_path('fixhr-app-firebase.json');
                            FirebaseNotification::sendPushNotification(
                                $title,
                                $body,
                                $employee->emp_fcm_token,
                                $serviceAccountPath,
                                config('credentials')['FIREBASE_MESSAGING_CONFIG'],
                                $additionalData
                            );
                        }
                        NotificationHelper::saveNotification(
                            $user->emp_id,
                            $employee->emp_id,
                            $title,
                            $body,
                            $additionalData
                        );
                    } catch (\Throwable $e) {
                        \Log::warning('Claim deduction notify requester failed: ' . $e->getMessage());
                    }

                    // $mailData = [
                    //     'subject' => "Expense Claim Approved with Deductions - Claim REF ID: #" . $claim->fh_tada_request_plan->trp_unique_id,
                    //     'url' => '#',
                    //     'mail_type' => 'CLAIM_APPROVED_WITH_DEDUCTION',
                    //     'data' => ['claimData' => $claim, 'receiverName' => $claim->fh_tada_request_plan->fh_employee->emp_fname, 'approver' => $user, 'deduction_remark' => $data->message],
                    // ];
                    // CentralLogics::send_mail($claim->fh_tada_request_plan->fh_employee->emp_email, new ApprovalMail($mailData));
                }
            } else {
                $this->updateStatusAndNextApproval('CLAIM_REQUEST_APPROVAL', $claim, $statusData->m_id, $data->approval_action_type, $sequence, $isLast, $updateData);
            }
            // event(new ApprovalEvent($claim->tc_id));
            $approversList = ProcessApprover::where(['pa_b_id' => $claim->tc_b_id, 'pa_am_id' => $claim->tc_am_id])->pluck('pa_emp_id');

            //$this->addDataToFirebase($claim->tc_id,'CLAIM_EVENT',$approversList,$claim->tc_b_id,$claim->tc_emp_id);
        } elseif ($postType == 'ADVANCE_REQUEST_APPROVAL') {
            $advance = AdvanceLog::where((DB::raw('md5(adl_id)')), $data->advance_id)->first();
            ApprovalLog::create([
                'log_am_id' => $advance->adl_am_id,
                'log_request_id' => $advance->adl_id,
                'log_user_id' => $user->emp_id,
                'log_user_role_id' => $user->emp_role_id,
                'log_status' => $statusData?->m_id,
                'log_description' =>  $data->message,
                'log_other' => $data->reimburse_amount,
                'log_module_id' => $advance->adl_module_id,
            ]);
            $plan = TadaRequestPlan::where('trp_id', $advance->adl_trp_id)->first();
            if ($isLast) {
                $ruleCriteria = RuleCriterion::where('rc_b_id', $user->emp_b_id)->whereNot('rc_am_id', $advance->adl_am_id)->where('rc_condition_option_id', $statusData->m_id)
                    ->whereHas('fh_approval_module', function ($query) {
                        $query->where('am_module_id', 199)->where('am_status', 1);
                    })->first();

                if (!$ruleCriteria) {
                    $amId = $advance->adl_am_id;
                    $isCompleted = 1;
                } else {
                    $amId = $ruleCriteria->rc_am_id;
                    $isCompleted = 0;
                }

               
                if ($plan && $statusData?->m_id!= 170) {
                    $plan->update(['trp_advance_allowance' => $plan->trp_advance_allowance
                        ? ($plan->trp_advance_allowance + $data->reimburse_amount)
                        : $data->reimburse_amount]);
                }

                $advance->update([
                    'adl_request_status' => $statusData->m_id,
                    'adl_next_approver' => 1,
                    'adl_am_id' => $amId,
                    'adl_stage_completed' => $isCompleted,
                    'adl_reimburse_amount' => $data->reimburse_amount,

                ]);

               // Push: notify requester (final decision)
                try {
                    if ($plan && $plan->fh_employee) {
                        $employee = $plan->fh_employee;
                        $title = 'Advance Request ' . ($statusData->m_id == 169 ? 'Approved' : ($statusData->m_id == 170 ? 'Rejected' : 'Updated'));
                        $body  = 'Your advance request for travel ' . ($plan->trp_unique_id ?? '') . ' has been '
                            . ($statusData->m_id == 169 ? 'approved' : ($statusData->m_id == 170 ? 'rejected' : 'updated')) . '.';
                        $additionalData = [
                            'notification_type' => 'advance_status',
                            'status' => ($statusData->m_id == 169 ? 'approved' : ($statusData->m_id == 170 ? 'rejected' : 'updated')),
                            'route' => '/TadaApprovalList',
                            'plan_id' => $plan->trp_id,
                        ];
                        if (!empty($employee->emp_fcm_token)) {
                            $serviceAccountPath = public_path('fixhr-app-firebase.json');
                            FirebaseNotification::sendPushNotification(
                                $title,
                                $body,
                                $employee->emp_fcm_token,
                                $serviceAccountPath,
                                config('credentials')['FIREBASE_MESSAGING_CONFIG'],
                                $additionalData
                            );
                        }
                        NotificationHelper::saveNotification($user->emp_id, $employee->emp_id, $title, $body, $additionalData);
                    }
                } catch (\Throwable $e) {
                    \Log::warning('Advance final requester notification failed: ' . $e->getMessage());
                }
            } else {
                $advance->update([
                    'adl_request_status' => $statusData->m_id,
                    'adl_next_approver' => $sequence
                ]);

                $nextApproverDetails = ProcessApprover::with([
                    'fh_employee:emp_id,emp_fname,emp_email,emp_full_name,emp_d_id'
                ])->where(DB::raw('md5(pa_am_id)'), '=', $data->module_id)->where(['pa_b_id' => $user->emp_b_id, 'pa_type' => $data->approval_action_type, 'pa_sequence' => $sequence])
                    ->orderBy('pa_am_id', 'asc')->first();

                    // Push: notify next approver (intermediate step)
                try {
                    if ($nextApproverDetails  && $nextApproverDetails->fh_employee) {
                        $approverEmp = $nextApproverDetails->fh_employee;
                        $title = 'Advance Approval Pending';
                        $body  = 'Approval required for advance on travel ' . ($plan->trp_unique_id ?? '');
                        $additionalData = [
                            'notification_type' => 'advance_approval',
                            'status' => 'pending',
                            'route' => '/TadaApprovalList',
                            'plan_id' => $plan->trp_id,
                        ];
                        if (!empty($approverEmp->emp_fcm_token)) {
                            $serviceAccountPath = public_path('fixhr-app-firebase.json');
                            FirebaseNotification::sendPushNotification(
                                $title,
                                $body,
                                $approverEmp->emp_fcm_token,
                                $serviceAccountPath,
                                config('credentials')['FIREBASE_MESSAGING_CONFIG'],
                                $additionalData
                            );
                        }
                        NotificationHelper::saveNotification($user->emp_id, $approverEmp->emp_id, $title, $body, $additionalData);
                    }
                } catch (\Throwable $e) {
                    \Log::warning('Advance next-approver notification failed: ' . $e->getMessage());
                }
                // if (isset($nextApproverDetails) && isset($nextApproverDetails->fh_employee)) {
                //     if ($nextApproverDetails->fh_employee->exists()) {
                //         $mailData = [
                //             'subject' => 'Advance Request Approval',
                //             'url' => route('advance.request.index'),
                //             'mail_type' => 'ADVANCE_APPROVAL_REQUEST',
                //             'data' => ['advanceData' => $data, 'nextApproverName' => $nextApproverDetails->fh_employee->emp_fname]
                //         ];
                //     }
                // }
            }
        } elseif ($postType == 'MISPUNCH_REQUEST_APPROVAL') {
               $plan = AttendanceException::where((DB::raw('md5(ae_id)')), $data->ae_id)->first();
               $employee =\App\Models\Employee::find($plan->fh_employee->emp_id);
                $title = 'Missed Punch '. ($data->approval_type == 1 ? 'Approved' : 'Rejected');
                // $body = "Your missed punch request has been". ($data->approval_type == 1 ? 'Approved' : 'Rejected');
                            
                $requestDate = $plan->ae_date ? $plan->ae_date->format('d-m-Y') : 'N/A';

                $body = "Your missed punch request dated {$requestDate} has been " . 
                        ($data->approval_type == 1 ? 'Approved' : 'Rejected');

                $additionalData = [
                  'notification_type' => 'misspunch_status',
                  'status' => $data->approval_type == 1 ? 'approved' : 'rejected',
                 'route'=>'/MisPunchList',
                 'remark'=>$data->message,

                                    ];

                $serviceAccountPath = public_path('fixhr-app-firebase.json');

            ApprovalLog::create([
                'log_am_id' => $plan->ae_am_id,
                'log_request_id' => $plan->ae_id,
                'log_user_id' => $user->emp_id,
                'log_user_role_id' => $user->emp_role_id,
                'log_status' => $statusData->m_id,
                'log_description' => $data->message,
                'log_module_id' => $plan->ae_module_id,
            ]);

            if ($isLast) {
                $ruleCriteria = RuleCriterion::where('rc_b_id', $user->emp_b_id)->whereNot('rc_am_id', $plan->ae_am_id)->where('rc_condition_option_id', $statusData->m_id)
                    ->whereHas('fh_approval_module', function ($query) {
                        $query->where('am_module_id', 229)->where('am_status', 1);
                    })->first();

                if (!$ruleCriteria) {
                    $amId = $plan->ae_am_id;
                    $isCompleted = 1;
                } else {
                    $amId = $ruleCriteria->rc_am_id;
                    $isCompleted = 0;
                }

                if ($isCompleted && $statusData->m_id != 170) {
                    $resolvedShift = ShiftResolver::resolveEmployeeShift($employee, $plan->ae_date);
                    $shift = $resolvedShift ? $resolvedShift->shift ?? $employee->fh_shift_type : $employee->fh_shift_type;
                    $shiftStartTime = $shift->pst_start_time;
                    $shiftEndTime = $shift->pst_end_time;
                    $graceMins = $shift->pst_allow_grace_time ? $shift->pst_grace_time : 0;
                    $shiftStartTime = $shiftStartTime->subMinutes($graceMins);
                    $dailyWorkingHours = $shiftStartTime->diffInMinutes($shiftEndTime);
                    $checkInTime = Carbon::parse($plan->ae_date->format('Y-m-d') . ' ' . $plan->ae_in_time);
                    $checkOutTime =  Carbon::parse($plan->ae_date->format('Y-m-d') . ' ' . $plan->ae_out_time);
                    $workedDuration = $checkInTime->diffInMinutes($checkOutTime);
                    $fullDayThreshold = $dailyWorkingHours;
                    $minWorkHrs = $shift->pst_min_work_hour ? $shiftStartTime->diffInMinutes(Carbon::parse($plan->ae_date->format('Y-m-d') . " " . $shift->pst_min_work_hour)) : 0;
                    $halfDayThreshold = $dailyWorkingHours / 2;

                    // Check if current day is a weekly off day
                    $isWeeklyOff = CentralLogics::getWeekOffDatesReport($employee, null, null, Carbon::parse($plan->ae_date)->format('Y-m-d'), Carbon::parse($plan->ae_date)->format('Y-m-d'));

                    // Check if current day is a holiday
                    $isHoliday = PolicyHolidayList::where('phl_b_id', $employee->emp_b_id)
                        ->whereDate('phl_start_date', '<=', $plan->ae_date)
                        ->whereDate('phl_end_date', '>=', $plan->ae_date)
                        ->where('phl_type_id', 206)
                        ->exists();

                    if ($workedDuration >= $fullDayThreshold || $workedDuration >= $minWorkHrs) {
                        $atd_status = 251; // Present
                    } else if ($workedDuration >= $halfDayThreshold) {
                        $atd_status = 252; // Half Day
                    } else {
                        $atd_status = 203; // Absent
                    }

                    // Handle holiday/week off attendance
                    if ($isWeeklyOff || $isHoliday) {
                        $atd_status = $isWeeklyOff ? 320 : 319; // Weekly Off Work : Holiday Work
                        $statusData->m_id != 170 && $isCompleted && CentralLogics::generateCompOff($plan->fh_employee, $plan->ae_date, 1);
                    }

                    $isLate = $checkInTime->greaterThan($shiftStartTime) ? 1 : 0;
                    $isEarlyExit = $checkOutTime->lessThan($shiftEndTime) ? 1 : 0;

                    // Update attendance record if conditions are met
                    AttendanceRecord::where([
                        ['atd_b_id', '=', $plan->ae_b_id],
                        ['atd_date', '=', $plan->ae_date],
                        ['atd_emp_id', '=', $plan->ae_emp_id],
                    ])->update(['atd_is_late' => $isLate, 'atd_is_early_exit' => $isEarlyExit]);

                }

                $plan->update(['ae_status' => $statusData->m_id, 'ae_next_approver' => 1, 'ae_am_id' => $amId, 'ae_stage_completed' => $isCompleted, 'ae_attendance_status' => $atd_status ?? null, 'ae_approved_by' => Auth::user()->emp_id]);
                 try {
                    if ($employee) {
                        $titleF = 'Mis-punch ' . ($data->approval_type == 1 ? 'Approved' : ($data->approval_type != 1 ? 'Rejected' : 'Updated'));
                        $bodyF  = 'Your mis-punch request has been ' . ($data->approval_type == 1 ? 'approved' : ($data->approval_type != 1 ? 'rejected' : 'updated')) . '.';
                        $dataF = [ 'notification_type' => 'misspunch_status', 'status' => ($data->approval_type == 1 ? 'approved' : ($data->approval_type != 1 ? 'rejected' : 'updated')), 'route' => '/MisPunchList', 'mis_id' => $plan->ae_id ];
                        if (!empty($employee->emp_fcm_token)) { FirebaseNotification::sendPushNotification($titleF, $bodyF, $employee->emp_fcm_token, $serviceAccountPath, config('credentials')['FIREBASE_MESSAGING_CONFIG'], $dataF); }
                        NotificationHelper::saveNotification($user->emp_id, $employee->emp_id, $titleF, $bodyF, $dataF);
                    }
                } catch (\Throwable $e) { \Log::warning('Mispunch final requester notification failed: ' . $e->getMessage()); }
            } else {
                $plan->update(['ae_status' => $statusData->m_id, 'ae_next_approver' => $sequence, 'ae_approved_by' => Auth::user()->emp_id]);

                //start notify next approver & refresh page
                $nextApproverDetails =  ProcessApprover::with([
                    'fh_employee:emp_id,emp_fname,emp_email,emp_full_name,emp_d_id'
                ])->where((DB::raw('md5(pa_am_id)')), $data->module_id)->where(['pa_b_id' => $user->emp_b_id, 'pa_type' => $data->approval_action_type, 'pa_sequence' => $sequence])
                    ->orderBy('pa_am_id', 'asc')->first();


                 if ($nextApproverDetails && $nextApproverDetails->fh_employee) {
                    try {
                        // Check if we have hierarchy-based approver
                        if ($nextApproverDetails && $nextApproverDetails->fh_employee) {
                            $approverEmp = $nextApproverDetails->fh_employee;
                            $title = 'Mis-punch Approval Pending';
                            $body = 'Approval required for a mis-punch request.';
                            $additionalData = [
                                'notification_type' => 'misspunch_approval',
                                'status' => 'pending',
                                'route' => '/MisPunchList',
                                'mis_id' => $plan->ae_id
                            ];
                            if (!empty($approverEmp->emp_fcm_token)) {
                                FirebaseNotification::sendPushNotification($title, $body, $approverEmp->emp_fcm_token, $serviceAccountPath, config('credentials')['FIREBASE_MESSAGING_CONFIG'], $additionalData);
                            }
                            NotificationHelper::saveNotification($user->emp_id, $approverEmp->emp_id, $title, $body, $additionalData);
                        } 
                     
                    } catch (\Throwable $e) { \Log::warning('Mispunch next-approver notification failed: ' . $e->getMessage()); }
                }
            }
            // event(new TravelApprovalEvent($plan->ae_id));
            $approversList = ProcessApprover::where(['pa_b_id' => $plan->ae_b_id, 'pa_am_id' => $plan->ae_am_id])->pluck('pa_emp_id');
            // $this->addDataToFirebase($plan->ae_id,'TRAVEL_EVENT',$approversList,$plan->ae_b_id,$plan->ae_emp_id,);
        } elseif ($postType == 'GATEPASS_REQUEST_APPROVAL') {
            // dd('hey');
            $plan = GatePass::where((DB::raw('md5(gtp_id)')), $data->gtp_id)->first();
            ApprovalLog::create([
                'log_am_id' => $plan->gtp_am_id,
                'log_request_id' => $plan->gtp_id,
                'log_user_id' => $user->emp_id,
                'log_user_role_id' => $user->emp_role_id,
                'log_status' => $statusData->m_id,
                'log_description' => $data->message,
                'log_module_id' => $plan->gtp_module_id,

            ]);
            $employee = \App\Models\Employee::find($plan->gtp_emp_id);
            if ($isLast) {
                $ruleCriteria = RuleCriterion::where('rc_b_id', $user->emp_b_id)->whereNot('rc_am_id', $plan->gtp_am_id)->where('rc_condition_option_id', $statusData->m_id)
                    ->whereHas('fh_approval_module', function ($query) {
                        $query->where('am_module_id', 339)->where('am_status', 1);
                    })->first();

                if (!$ruleCriteria) {
                    $amId = $plan->gtp_am_id;
                    $isCompleted = 1;
                } else {
                    $amId = $ruleCriteria->rc_am_id;
                    $isCompleted = 0;
                }
                $plan->update(['gtp_status' => $statusData->m_id, 'gtp_next_approver' => 1, 'gtp_am_id' => $amId, 'gtp_stage_completed' => $isCompleted, 'gtp_approved_by' => Auth::user()->emp_id]);

                $atdRecord = AttendanceRecord::where('atd_emp_id', $plan->gtp_emp_id)
                    ->where('atd_date', $plan->gtp_date)
                    ->whereNull('atd_check_out_time')
                    ->first();

                $resolvedShift = ShiftResolver::resolveEmployeeShift($employee, $plan->gtp_date);
                $shift = $resolvedShift->shift ?? PolicyShiftTiming::find($employee->emp_shift_type_id);
                $isEnableGtpCheckout = AutomationRule::where('ar_b_id', $employee->emp_b_id)
                    ->where('ar_module_id', 418)
                    ->where('ar_apply_gatepass_checkout', 1)
                    ->exists();

                if (
                    $statusData?->m_id == 157 &&
                    $employee->emp_is_geofencing_active == 1 &&
                    $atdRecord &&
                    $shift
                ) {
                    $shiftEndTime = $shift->pst_end_time;
                    $gatePassOutTime = $plan->gtp_out_time;

                    if ($gatePassOutTime >= $shiftEndTime && $isEnableGtpCheckout) {
                        $atdRecord->update([
                            'atd_attendance_status' => 251,
                            'atd_check_out_time' => $plan->gtp_date.' '.$gatePassOutTime,
                            'atd_gtp_id' => $plan->gtp_id,
                            'atd_is_early_exit' => 0,
                            'atd_early_exit_duration' => 0.00,
                        ]);
                    }
                }

                // Push requester final
                try {
                    if ($employee) {
                        $titleF = 'Gatepass ' . ($data->approval_type == 1 ? 'Approved' : ($data->approval_type != 1 ? 'Rejected' : 'Updated'));
                        $bodyF  = 'Your gatepass request has been ' . ($data->approval_type == 1 ? 'approved' : ($data->approval_type != 1 ? 'rejected' : 'updated')) . '.';
                        $dataF = [ 'notification_type' => 'gatepass_status', 'status' => ($data->approval_type == 1? 'approved' : ($data->approval_type != 1 ? 'rejected' : 'updated')), 'route' => '/GetPassApprovalList', 'gatepass_id' => $plan->gtp_id ];
                        $serviceAccountPath = public_path('fixhr-app-firebase.json');
                        if (!empty($employee->emp_fcm_token)) { FirebaseNotification::sendPushNotification($titleF, $bodyF, $employee->emp_fcm_token, $serviceAccountPath, config('credentials')['FIREBASE_MESSAGING_CONFIG'], $dataF); }
                        NotificationHelper::saveNotification($user->emp_id, $employee->emp_id, $titleF, $bodyF, $dataF);
                    }
                } catch (\Throwable $e) { \Log::warning('Gatepass final requester notification failed: ' . $e->getMessage()); }
            } else {
                $plan->update(['gtp_status' => $statusData->m_id, 'gtp_next_approver' => $sequence, 'gtp_approved_by' => Auth::user()->emp_id]);

                // Get approvers for notification (employee-wise or hierarchy-wise)
                $approverData = $this->getApproversForNotification($user->emp_b_id, $plan->gtp_emp_id, 339, $plan->fh_employee->emp_d_id ?? null);
                $approvalEmpIds = $approverData['approvalEmpIds'];
                $amId = $approverData['amId'];

                //start notify next approver & refresh page
                $nextApproverDetails =  ProcessApprover::with([
                    'fh_employee:emp_id,emp_fname,emp_email,emp_full_name,emp_d_id'
                ])->where((DB::raw('md5(pa_am_id)')), $data->module_id)->where(['pa_b_id' => $user->emp_b_id, 'pa_type' => $data->approval_action_type, 'pa_sequence' => $sequence])
                    ->orderBy('pa_am_id', 'asc')->first();

                if ($nextApproverDetails && $nextApproverDetails->fh_employee) {
                    try {
                        // Check if we have hierarchy-based approver
                        if ($nextApproverDetails->fh_employee) {
                            $approverEmp = $nextApproverDetails->fh_employee;
                            
                            $placeholders = [
                                '{receiver_name}' => $approverEmp->emp_full_name,
                                '{submit_date}' => Carbon::parse($plan->created_at)->format('d M, Y'),
                                '{date}' => Carbon::parse($plan->gtp_date)->format('d M, Y'),
                                '{in_time}' => $plan->gtp_in_time ?? 'N/A',
                                '{out_time}' => $plan->gtp_out_time ?? 'N/A',
                                '{destination}' => $plan->gtp_destination ?? 'N/A',
                                '{reason}' => $plan->gtp_reason ?? 'N/A',
                                '{employee_code}' => $plan->fh_employee->emp_code ?? 'N/A',
                                '{employee_name}' => $plan->fh_employee->emp_full_name ?? 'N/A',
                                '{employee_designation}' => $plan->fh_employee->fh_designation->dg_name ?? 'N/A',
                                '{employee_phone}' => $plan->fh_employee->emp_phone ?? 'N/A',
                                '{portal_url}' => route('requests.gate-pass.show', ['id' => md5($plan->gtp_id)]),
                            ];

                            // The recipient's email address
                            $recipientEmail = $approverEmp->emp_email;

                            // The email template type and business ID
                            $templateType = 400; // Replace with your mail template type
                            $businessId = $user->emp_b_id; // Replace with your business ID if applicable

                            // Sending the email using the CentralLogics class
                            $sent = CentralLogics::sendCustomEmail($templateType, $placeholders, $recipientEmail, $businessId);
                            
                            // Push: next approver
                            $title = 'Gatepass Approval Pending';
                            $body = 'Approval required for a gatepass request.';
                            $additionalData = [
                                'notification_type' => 'gatepass_approval',
                                'status' => 'pending',
                                'route' => '/GetPassApprovalList',
                                'gatepass_id' => $plan->gtp_id
                            ];
                            $serviceAccountPath = public_path('fixhr-app-firebase.json');
                            if (!empty($approverEmp->emp_fcm_token)) {
                                FirebaseNotification::sendPushNotification($title, $body, $approverEmp->emp_fcm_token, $serviceAccountPath, config('credentials')['FIREBASE_MESSAGING_CONFIG'], $additionalData);
                            }
                            NotificationHelper::saveNotification($user->emp_id, $approverEmp->emp_id, $title, $body, $additionalData);
                        }
                    } catch (\Throwable $e) { 
                        \Log::warning('Gatepass next-approver notification failed: ' . $e->getMessage()); 
                    }
                }
            }
            // event(new TravelApprovalEvent($plan->gtp_id));
            $approversList = ProcessApprover::where(['pa_b_id' => $plan->gtp_b_id, 'pa_am_id' => $plan->gtp_am_id])->pluck('pa_emp_id');
            // $this->addDataToFirebase($plan->gtp_id,'TRAVEL_EVENT',$approversList,$plan->gtp_b_id,$plan->gtp_emp_id,);
        } elseif ($postType == 'LOAN_REQUEST_APPROVAL') {
            $plan = LoanRequest::where((DB::raw('md5(lnr_id)')), $data->lnr_id)->first();
            ApprovalLog::create([
                'log_am_id' => $plan->lnr_am_id,
                'log_request_id' => $plan->lnr_id,
                'log_user_id' => $user->emp_id,
                'log_user_role_id' => $user->emp_role_id,
                'log_status' => $statusData->m_id,
                'log_description' => $data->message,
                'log_module_id' => $plan->lnr_module_id,
            ]);

            if ($isLast) {
                $ruleCriteria = RuleCriterion::where('rc_b_id', $user->emp_b_id)->whereNot('rc_am_id', $plan->lnr_am_id)->where('rc_condition_option_id', $statusData->m_id)
                    ->whereHas('fh_approval_module', function ($query) {
                        $query->where('am_module_id', 442)->where('am_status', 1);
                    })->first();

                if (!$ruleCriteria) {
                    $amId = $plan->lnr_am_id;
                    $isCompleted = 1;
                } else {
                    $amId = $ruleCriteria->rc_am_id;
                    $isCompleted = 0;
                }
                $plan->update(['lnr_request_status' => $statusData->m_id, 'lnr_next_approver' => 1, 'lnr_am_id' => $amId, 'lnr_stage_completed' => $isCompleted]);
             // Push requester final
                try {
                    $employee = Employee::find($plan->lnr_emp_id);
                    if ($employee) {
                        $titleF = 'Loan Request ' . ($statusData->m_id == 169 ? 'Approved' : ($statusData->m_id == 170 ? 'Rejected' : 'Updated'));
                        $bodyF  = 'Your loan request has been ' . ($statusData->m_id == 169 ? 'approved' : ($statusData->m_id == 170 ? 'rejected' : 'updated')) . '.';
                        $dataF = [ 'notification_type' => 'loan_status', 'status' => ($statusData->m_id == 169 ? 'approved' : ($statusData->m_id == 170 ? 'rejected' : 'updated')), 'route' => '/RequestAdvanceListPage', 'loan_id' => $plan->lnr_id ];
                        $serviceAccountPath = public_path('fixhr-app-firebase.json');
                        if (!empty($employee->emp_fcm_token)) { FirebaseNotification::sendPushNotification($titleF, $bodyF, $employee->emp_fcm_token, $serviceAccountPath, config('credentials')['FIREBASE_MESSAGING_CONFIG'], $dataF); }
                        NotificationHelper::saveNotification($user->emp_id, $employee->emp_id, $titleF, $bodyF, $dataF);
                    }
                } catch (\Throwable $e) { \Log::warning('Loan final requester notification failed: ' . $e->getMessage()); }
            }else {
                $plan->update(['lnr_request_status' => $statusData->m_id, 'lnr_next_approver' => $sequence]);

                //start notify next approver & refresh page
                $nextApproverDetails =  ProcessApprover::with([
                    'fh_employee:emp_id,emp_fname,emp_email,emp_full_name,emp_d_id'
                ])->where((DB::raw('md5(pa_am_id)')), $data->module_id)->where(['pa_b_id' => $user->emp_b_id, 'pa_type' => $data->approval_action_type, 'pa_sequence' => $sequence])
                    ->orderBy('pa_am_id', 'asc')->first();

                if (isset($nextApproverDetails) && isset($nextApproverDetails->fh_employee)) {
                    if ($nextApproverDetails->fh_employee->exists()) {
                        $placeholders = [
                            '{receiver_name}' => $nextApproverDetails->fh_employee->emp_full_name,
                            '{submit_date}' => Carbon::parse($plan->created_at)->format('d M, Y'),

                            '{description}' => $plan->lnr_description ?? 'N/A',
                            '{employee_code}' => $plan->fh_employee->emp_code ?? 'N/A',
                            '{employee_name}' => $plan->fh_employee->emp_full_name ?? 'N/A',
                            '{employee_designation}' => $plan->fh_employee->fh_designation->dg_name ?? 'N/A',
                            '{employee_phone}' => $plan->fh_employee->emp_phone ?? 'N/A',
                            '{portal_url}' => route('requests.loan-requests.show', ['id' => md5($plan->lnr_id)]),
                        ];

                        // The recipient's email address
                        $recipientEmail = $nextApproverDetails->fh_employee->emp_email;

                        // The email template type and business ID
                        $templateType = 443; // Replace with your mail template type
                        $businessId = $user->emp_b_id; // Replace with your business ID if applicable

                        // Sending the email using the CentralLogics class
                        $sent = CentralLogics::sendCustomEmail($templateType, $placeholders, $recipientEmail, $businessId);

                        // $mailData = [
                        //     'subject' => 'Gatepass Request Approval',
                        //     'url' => route('requests.gate-pass.show', ['id' => md5($plan->gtp_id)]),
                        //     'mail_type' => 'GATEPASS_APPROVAL_REQUEST',
                        //     'data' => ['gatepassData' => $plan, 'nextApproverName' => $nextApproverDetails->fh_employee->emp_fname],
                        // ];
                        // CentralLogics::send_mail($nextApproverDetails->fh_employee->emp_email, new ApprovalMail($mailData));
                        //end notify next approver & refresh page
                      // Push: next approver (loan)
                        try {
                            $ap = $nextApproverDetails->fh_employee;
                            $t = 'Loan Approval Pending';
                            $b = 'Approval required for a loan request.';
                            $d = [ 'notification_type' => 'loan_approval', 'status' => 'pending', 'route' => '/RequestAdvanceListPage', 'loan_id' => $plan->lnr_id ];
                            $serviceAccountPath = public_path('fixhr-app-firebase.json');
                            if (!empty($ap->emp_fcm_token)) { FirebaseNotification::sendPushNotification($t, $b, $ap->emp_fcm_token, $serviceAccountPath, config('credentials')['FIREBASE_MESSAGING_CONFIG'], $d); }
                            NotificationHelper::saveNotification($user->emp_id, $ap->emp_id, $t, $b, $d);
                        } catch (\Throwable $e) { \Log::warning('Loan next-approver notification failed: ' . $e->getMessage()); }
                    }
                }
            }
            // event(new TravelApprovalEvent($plan->gtp_id));
            $approversList = ProcessApprover::where(['pa_b_id' => $plan->lnr_b_id, 'pa_am_id' => $plan->lnr_am_id])->pluck('pa_emp_id');
            // $this->addDataToFirebase($plan->gtp_id,'TRAVEL_EVENT',$approversList,$plan->gtp_b_id,$plan->gtp_emp_id,);
        } elseif ($postType == 'LOAN_REQUEST_APPROVAL_APP') {
            $plan = LoanRequest::where((DB::raw('md5(lnr_id)')), $data->lnr_id)->first();
            ApprovalLog::create([
                'log_am_id' => $plan->lnr_am_id,
                'log_request_id' => $plan->lnr_id,
                'log_user_id' => $user->emp_id,
                'log_user_role_id' => $user->emp_role_id,
                'log_status' => $statusData->m_id,
                'log_description' => $data->message,
                'log_module_id' => $plan->lnr_module_id,
            ]);

            if ($isLast) {
                $ruleCriteria = RuleCriterion::where('rc_b_id', $user->emp_b_id)->whereNot('rc_am_id', $plan->lnr_am_id)->where('rc_condition_option_id', $statusData->m_id)
                    ->whereHas('fh_approval_module', function ($query) {
                        $query->where('am_module_id', 442)->where('am_status', 1);
                    })->first();

                if (!$ruleCriteria) {
                    $amId = $plan->lnr_am_id;
                    $isCompleted = 1;
                } else {
                    $amId = $ruleCriteria->rc_am_id;
                    $isCompleted = 0;
                }
                $plan->update(['lnr_request_status' => $statusData->m_id, 'lnr_next_approver' => 1, 'lnr_am_id' => $amId, 'lnr_stage_completed' => $isCompleted]);
               // Push requester final (app)
                try {
                    $employee = Employee::find($plan->lnr_emp_id);
                    if ($employee) {
                        $titleF = 'Loan Request ' . ($statusData->m_id == 169 ? 'Approved' : ($statusData->m_id == 170 ? 'Rejected' : 'Updated'));
                        $bodyF  = 'Your loan request has been ' . ($statusData->m_id == 169 ? 'approved' : ($statusData->m_id == 170 ? 'rejected' : 'updated')) . '.';
                        $dataF = [ 'notification_type' => 'loan_status', 'status' => ($statusData->m_id == 169 ? 'approved' : ($statusData->m_id == 170 ? 'rejected' : 'updated')), 'route' => '/RequestAdvanceListPage', 'loan_id' => $plan->lnr_id ];
                        $serviceAccountPath = public_path('fixhr-app-firebase.json');
                        if (!empty($employee->emp_fcm_token)) { FirebaseNotification::sendPushNotification($titleF, $bodyF, $employee->emp_fcm_token, $serviceAccountPath, config('credentials')['FIREBASE_MESSAGING_CONFIG'], $dataF); }
                        NotificationHelper::saveNotification($user->emp_id, $employee->emp_id, $titleF, $bodyF, $dataF);
                    }
                } catch (\Throwable $e) { \Log::warning('Loan(app) final requester notification failed: ' . $e->getMessage()); }
            } else {
                $plan->update(['lnr_request_status' => $statusData->m_id, 'lnr_next_approver' => $sequence]);

                //start notify next approver & refresh page
                $nextApproverDetails =  ProcessApprover::with([
                    'fh_employee:emp_id,emp_fname,emp_email,emp_full_name,emp_d_id'
                ])->where((DB::raw('md5(pa_am_id)')), $data->module_id)->where(['pa_b_id' => $user->emp_b_id, 'pa_type' => $data->approval_action_type, 'pa_sequence' => $sequence])
                    ->orderBy('pa_am_id', 'asc')->first();

                if (isset($nextApproverDetails) && isset($nextApproverDetails->fh_employee)) {
                    if ($nextApproverDetails->fh_employee->exists()) {
                        $placeholders = [
                            '{receiver_name}' => $nextApproverDetails->fh_employee->emp_full_name,
                            '{submit_date}' => Carbon::parse($plan->created_at)->format('d M, Y'),

                            '{description}' => $plan->lnr_description ?? 'N/A',
                            '{employee_code}' => $plan->fh_employee->emp_code ?? 'N/A',
                            '{employee_name}' => $plan->fh_employee->emp_full_name ?? 'N/A',
                            '{employee_designation}' => $plan->fh_employee->fh_designation->dg_name ?? 'N/A',
                            '{employee_phone}' => $plan->fh_employee->emp_phone ?? 'N/A',
                            '{portal_url}' => route('requests.loan-requests.show', ['id' => md5($plan->lnr_id)]),
                        ];

                        // The recipient's email address
                        $recipientEmail = $nextApproverDetails->fh_employee->emp_email;

                        // The email template type and business ID
                        $templateType = 443; // Replace with your mail template type
                        $businessId = $user->emp_b_id; // Replace with your business ID if applicable

                        // Sending the email using the CentralLogics class
                        $sent = CentralLogics::sendCustomEmail($templateType, $placeholders, $recipientEmail, $businessId);
                        // Push: next approver (loan app)
                        try {
                            $ap = $nextApproverDetails->fh_employee;
                            $t = 'Loan Approval Pending';
                            $b = 'Approval required for a loan request.';
                            $d = [ 'notification_type' => 'loan_approval', 'status' => 'pending', 'route' => '/RequestAdvanceListPage', 'loan_id' => $plan->lnr_id ];
                            $serviceAccountPath = public_path('fixhr-app-firebase.json');
                            if (!empty($ap->emp_fcm_token)) { FirebaseNotification::sendPushNotification($t, $b, $ap->emp_fcm_token, $serviceAccountPath, config('credentials')['FIREBASE_MESSAGING_CONFIG'], $d); }
                            NotificationHelper::saveNotification($user->emp_id, $ap->emp_id, $t, $b, $d);
                        } catch (\Throwable $e) { \Log::warning('Loan(app) next-approver notification failed: ' . $e->getMessage()); }
                    }
                        // $mailData = [
                        //     'subject' => 'Gatepass Request Approval',
                        //     'url' => route('requests.gate-pass.show', ['id' => md5($plan->gtp_id)]),
                        //     'mail_type' => 'GATEPASS_APPROVAL_REQUEST',
                        //     'data' => ['gatepassData' => $plan, 'nextApproverName' => $nextApproverDetails->fh_employee->emp_fname],
                        // ];
                        // CentralLogics::send_mail($nextApproverDetails->fh_employee->emp_email, new ApprovalMail($mailData));
                        //end notify next approver & refresh page

                    }
                
            }
            // event(new TravelApprovalEvent($plan->gtp_id));
            $approversList = ProcessApprover::where(['pa_b_id' => $plan->lnr_b_id, 'pa_am_id' => $plan->lnr_am_id])->pluck('pa_emp_id');
            // $this->addDataToFirebase($plan->gtp_id,'TRAVEL_EVENT',$approversList,$plan->gtp_b_id,$plan->gtp_emp_id,);
        } elseif ($postType == 'LEAVE_REQUEST_APPROVAL') {
            $plan = LeaveRequest::where((DB::raw('md5(lvr_id)')), $data->lvr_id)->first();
            $sandwichLeave = LeaveRequest::where((DB::raw('md5(lvr_p_id)')), $data->lvr_id)->first();

            $employee =Employee::find($plan->lvr_emp_id);
            $title = 'Leave '. ($data->approval_type == 1 ? 'Approved' : 'Rejected');
            $body = 'Your leave has been ' . ($data->approval_type == 1 ? 'Approved' : 'Rejected') .
            ' (From: ' . ($plan->lvr_start_date ? $plan->lvr_start_date->format('d-m-Y') : 'N/A') .
            ', To: ' . ($plan->lvr_end_date ? $plan->lvr_end_date->format('d-m-Y') : 'N/A') . ')';

                $additionalData = [
                    'notification_type' => 'leave_status',
                    'status' => $data->approval_type == 1 ? 'approved' : 'rejected',
                     'route'=>'/LeaveApplications',
                     'remark'=>$data->message,
                ];
            // if($employee->emp_is_notification_enabled =='1') {
            //     if ($employee && $employee->emp_fcm_token) {
            //         $serviceAccountPath = public_path('fixhr-app-firebase.json');
            //         FirebaseNotification::sendPushNotification(
            //             $title,
            //             $body,
            //             $employee->emp_fcm_token,
            //             $serviceAccountPath,
            //             config('credentials')['FIREBASE_MESSAGING_CONFIG'],
            //             $additionalData
            //         );
            //     }
            //     NotificationHelper::saveNotification(
            //         $user->emp_id,
            //         $employee->emp_id,
            //         $title,
            //         $body,
            //         $additionalData
            //     );
            // }
            ApprovalLog::create([
                'log_am_id' => $plan->lvr_am_id,
                'log_request_id' => $plan->lvr_id,
                'log_user_id' => $user->emp_id,
                'log_user_role_id' => $user->emp_role_id,
                'log_status' => $statusData->m_id,
                'log_description' => $data->message,
                'log_module_id' => $plan->lvr_module_id,
            ]);

            if ($isLast) {
                $ruleCriteria = RuleCriterion::where('rc_b_id', $user->emp_b_id)->whereNot('rc_am_id', $plan->lvr_am_id)->where('rc_condition_option_id', $statusData->m_id)
                    ->whereHas('fh_approval_module', function ($query) {
                        $query->where('am_module_id', 250)->where('am_status', 1);
                    })->first();
                if (!$ruleCriteria) {
                    $amId = $plan->lvr_am_id;
                    $isCompleted = 1;
                } else {
                    $amId = $ruleCriteria->rc_am_id;
                    $isCompleted = 0;
                }

                // Manage leave balance for each rejected leave request
                if ($plan->lvr_module_id == 250 && $statusData->m_id == 170 && $plan->lvr_stage_completed == 0) {
                    if ($plan->lvr_is_comp_off) {
                        $compOffBalance = CompOffBalance::where('cb_emp_id', $plan->lvr_emp_id)->where('cb_b_id', $user->fh_business->b_id)
                            ->where('cb_year', now()->year)
                            ->where('cb_month', now()->month)
                            ->first();

                        if ($compOffBalance) {
                            $exist_cb_taken = $compOffBalance->cb_taken - $plan->lvr_total_leave_days;
                            $exist_cb_balance_remaining = $compOffBalance->cb_balance_remaining + $plan->lvr_total_leave_days;

                            $compOffBalance->cb_taken = $exist_cb_taken;
                            $compOffBalance->cb_balance_remaining = $exist_cb_balance_remaining;
                            $compOffBalance->save();
                        }
                    } else {
                        $leaveBalance = LeaveBalance::where([
                                ['lb_emp_id', '=', $plan->lvr_emp_id],
                                ['lb_cat_type_id', '!=', 215],
                                ['lb_cat_type_id', '=', $plan->lvr_cat_type_id],
                                ['lb_b_id', '=', $user->fh_business->b_id],
                                ['lb_month', '=', now()->month],
                                ['lb_year', '=', now()->year],
                            ])
                        ->first();

                        if ($leaveBalance) {
                            $exist_lb_taken_leave = $leaveBalance->lb_taken_leave - $plan->lvr_total_leave_days;
                            $exist_lb_balance_remaining_leave = $leaveBalance->lb_balance_remaining_leave + $plan->lvr_total_leave_days;

                            $leaveBalance->lb_taken_leave = $exist_lb_taken_leave;
                            $leaveBalance->lb_balance_remaining_leave = $exist_lb_balance_remaining_leave;
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
                                $exist_lb_taken_leave = $sandwichLB->lb_taken_leave - $plan->lvr_total_leave_days;
                                $exist_lb_balance_remaining_leave = $sandwichLB->lb_balance_remaining_leave + $plan->lvr_total_leave_days;

                                $sandwichLB->lb_taken_leave = $exist_lb_taken_leave;
                                $sandwichLB->lb_balance_remaining_leave = $exist_lb_balance_remaining_leave;
                                $sandwichLB->save();
                            }
                        }
                    }
                }
                $plan->update(['lvr_status' => $statusData->m_id, 'lvr_next_approver' => 1, 'lvr_am_id' => $amId, 'lvr_stage_completed' => $isCompleted, 'lvr_approved_by' => Auth::user()->emp_id]);
                if (isset($sandwichLeave) && !empty($sandwichLeave)) {
                    $sandwichLeave->update(['lvr_status' => $statusData->m_id, 'lvr_next_approver' => 1, 'lvr_am_id' => $amId, 'lvr_stage_completed' => $isCompleted]);
                }

                // If the leave is of type 'Half Day' and the start date is today or in the past, reset late/early exit flags
                if ($plan->lvr_leave_day_type_id == 202 && Carbon::parse($plan->lvr_start_date)->lte(now())) {
                    $attendance = AttendanceRecord::where('atd_emp_id', $plan->lvr_emp_id)
                        ->where('atd_date', '=', $plan->lvr_start_date)
                        ->where('atd_b_id', $plan->lvr_b_id)
                    ->first();
                    $resolvedShift = ShiftResolver::resolveEmployeeShift($plan->fh_employee, $plan->lvr_start_date);
                    $shift = $resolvedShift ? $resolvedShift->shift : $attendance->fh_policy_shift_timing;

                    if ($attendance && $plan->lvr_day_segment_id == 235) {
                        if ($shift->pst_allow_break1 && $shift->pst_break_end_time1 && $shift->pst_is_break_paid) {
                            $shiftStart = Carbon::parse($shift->pst_break_end_time1);
                        } else {
                            $shiftStart = Carbon::parse($shift->pst_start_time)->addHours(4);
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
                    } else if ($attendance && $plan->lvr_day_segment_id == 236) {
                        if ($shift->pst_allow_break1 && $shift->pst_break_begin_time1 && $shift->pst_is_break_paid) {
                            $shiftEnd = Carbon::parse($shift->pst_break_begin_time1);
                        } else {
                            $shiftEnd = Carbon::parse($shift->pst_end_time)->subHours(5);
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
                    // Push: notify requester (final leave decision)
                try {
                    $employee = Employee::find($plan->lvr_emp_id);
                    if ($employee) {
                        $title = 'Leave ' . ($data->approval_type == 1? 'Approved' : ($data->approval_type != 1 ? 'Rejected' : 'Updated'));
                        $body  = 'Your leave request has been ' . ($data->approval_type == 1 ? 'approved' : ($data->approval_type != 1 ? 'rejected' : 'updated')) . '.';
                        $additionalData = [
                            'notification_type' => 'leave_status',
                            'status' => ($data->approval_type == 1 ? 'approved' : ($data->approval_type != 1? 'rejected' : 'updated')),
                            'route' => '/LeaveApplications',
                            'leave_id' => $plan->lvr_id,
                        ];
                        if (!empty($employee->emp_fcm_token)) {
                            $serviceAccountPath = public_path('fixhr-app-firebase.json');
                            FirebaseNotification::sendPushNotification($title, $body, $employee->emp_fcm_token, $serviceAccountPath, config('credentials')['FIREBASE_MESSAGING_CONFIG'], $additionalData);
                        }
                        NotificationHelper::saveNotification($user->emp_id, $employee->emp_id, $title, $body, $additionalData);
                    }
                } catch (\Throwable $e) { \Log::warning('Leave final requester notification failed: ' . $e->getMessage()); }
            } else {
                //lvr_next_approver
                $plan->update(['lvr_status' => $statusData->m_id, 'lvr_next_approver' => $sequence, 'lvr_approved_by' => Auth::user()->emp_id]);

                //start notify next approver & refresh page
                $nextApproverDetails =  ProcessApprover::with([
                    'fh_employee:emp_id,emp_fname,emp_email,emp_full_name,emp_d_id'
                ])->where((DB::raw('md5(pa_am_id)')), $data->module_id)->where(['pa_b_id' => $user->emp_b_id, 'pa_type' => $data->approval_action_type, 'pa_sequence' => $sequence])
                    ->orderBy('pa_am_id', 'asc')->first();

                if (isset($nextApproverDetails) && isset($nextApproverDetails->fh_employee)) {
                    if ($nextApproverDetails->fh_employee->exists()) {
                        // Push: notify next approver (leave)
                        try {
                            // Check if we have hierarchy-based approver
                            if ($nextApproverDetails && $nextApproverDetails->fh_employee) {
                                $approverEmp = $nextApproverDetails->fh_employee;
                                $title = 'Leave Approval Pending';
                                $body  = 'Approval required for a leave request.';
                                $additionalData = [
                                    'notification_type' => 'leave_approval',
                                    'status' => 'pending',
                                    'route' => '/LeaveApplications',
                                    'leave_id' => $plan->lvr_id,
                                ];
                                if (!empty($approverEmp->emp_fcm_token)) {
                                    $serviceAccountPath = public_path('fixhr-app-firebase.json');
                                    FirebaseNotification::sendPushNotification($title, $body, $approverEmp->emp_fcm_token, $serviceAccountPath, config('credentials')['FIREBASE_MESSAGING_CONFIG'], $additionalData);
                                }
                                NotificationHelper::saveNotification($user->emp_id, $approverEmp->emp_id, $title, $body, $additionalData);
                            }
                    
                        } catch (\Throwable $e) { \Log::warning('Leave next-approver notification failed: ' . $e->getMessage()); }
                    }
                }
            }
            $approversList = ProcessApprover::where(['pa_b_id' => $plan->lvr_b_id, 'pa_am_id' => $plan->lvr_am_id])->pluck('pa_emp_id');
            // $this->addDataToFirebase($plan->lvr_id,'TRAVEL_EVENT',$approversList,$plan->lvr_b_id,$plan->lvr_emp_id,);
        } elseif ($postType == 'Attendance_REQUEST_APPROVAL') {
            $plan = AttendanceRecord::where((DB::raw('md5(atd_id)')), $data->atd_id)->first();
            ApprovalLog::create([
                'log_am_id' => $plan->atd_am_id,
                'log_request_id' => $plan->atd_id,
                'log_user_id' => $user->emp_id,
                'log_user_role_id' => $user->emp_role_id,
                'log_status' => $statusData->m_id,
                'log_description' => $data->message,
            ]);
            if ($isLast) {
                $ruleCriteria = RuleCriterion::where('rc_b_id', $user->emp_b_id)->whereNot('rc_am_id', $plan->atd_am_id)->where('rc_condition_option_id', $statusData->m_id)
                    ->whereHas('fh_approval_module', function ($query) {
                        $query->where('am_module_id', 249)->where('am_status', 1);
                    })->first();
                if (!$ruleCriteria) {
                    $amId = $plan->atd_am_id;
                    $isCompleted = 1;
                } else {
                    $amId = $ruleCriteria->rc_am_id;
                    $isCompleted = 0;
                }
                $plan->update(['atd_request_status' => $statusData->m_id, 'atd_next_approver' => 1, 'atd_am_id' => $amId, 'atd_stage_completed' => $isCompleted]);
                $statusData->m_id != 170 && $isCompleted && ($plan->atd_attendance_status == 320 || $plan->atd_attendance_status == 319) && CentralLogics::generateCompOff($plan->fh_employee, $plan->atd_date);
            } else {
                $plan->update(['atd_request_status' => $statusData->m_id, 'atd_next_approver' => $sequence]);

                //start notify next approver & refresh page
                $nextApproverDetails =  ProcessApprover::with([
                    'fh_employee:emp_id,emp_fname,emp_email,emp_full_name,emp_d_id'
                ])->where((DB::raw('md5(pa_am_id)')), $data->module_id)->where(['pa_b_id' => $user->emp_b_id, 'pa_type' => $data->approval_action_type, 'pa_sequence' => $sequence])
                    ->orderBy('pa_am_id', 'asc')->first();
            }
            // event(new TravelApprovalEvent($plan->trp_id));
            $approversList = ProcessApprover::where(['pa_b_id' => $plan->atd_b_id, 'pa_am_id' => $plan->atd_am_id])->pluck('pa_emp_id');
            //$this->addDataToFirebase($plan->trp_id,'TRAVEL_EVENT',$approversList,$plan->trp_b_id,$plan->trp_emp_id,);
        } elseif ($postType == 'OVERTIME_REQUEST_APPROVAL') {
            $plan = OtApprovalStatus::where((DB::raw('md5(ot_id)')), $data->ot_id)->first();
            $employee =Employee::find($plan->ot_emp_id);
            $title = 'Overtime'. ($data->approval_type == 1 ? 'Approved' : 'Rejected');
            $body = 'Your overtime has been '. ($data->approval_type == 1 ? 'Approved' : 'Rejected');
            $additionalData = [
                'notification_type' => 'leave_status',
                'status' => $data->approval_type == 1 ? 'approved' : 'rejected',
                    'route'=>'/LeaveApplications',
                    'remark'=>$data->message,
            ];
            //  if ($employee && $employee->emp_fcm_token) {

            //         $serviceAccountPath = public_path('fixhr-app-firebase.json');

            //         FirebaseNotification::sendPushNotification(
            //             $title,
            //             $body,
            //             $employee->emp_fcm_token,
            //             $serviceAccountPath,
            //             config('credentials')['FIREBASE_MESSAGING_CONFIG'],
            //             $additionalData
            //         );
            //     }
            //      NotificationHelper::saveNotification(
            //         $user->emp_id,
            //         $employee->emp_id,
            //         $title,
            //         $body,
            //         $additionalData
            //     );
            ApprovalLog::create([
                'log_am_id' => $plan->ot_am_id,
                'log_request_id' => $plan->ot_id,
                'log_user_id' => $user->emp_id,
                'log_user_role_id' => $user->emp_role_id,
                'log_status' => $statusData->m_id,
                'log_description' => $data->message,
                'log_module_id' => $plan->atd_module_id,
            ]);

            if ($isLast) {
                $ruleCriteria = RuleCriterion::where('rc_b_id', $user->emp_b_id)->whereNot('rc_am_id', $plan->co_am_id)->where('rc_condition_option_id', $statusData->m_id)
                    ->whereHas('fh_approval_module', function ($query) {
                        $query->where('am_module_id', 562)->where('am_status', 1);
                    })->first();
                if (!$ruleCriteria) {
                    $amId = $plan->ot_am_id;
                    $isCompleted = 1;
                } else {
                    $amId = $ruleCriteria->rc_am_id;
                    $isCompleted = 0;
                }

                $plan->update(['ot_requested_status' => $statusData->m_id, 'ot_next_approver' => 1, 'ot_am_id' => $amId, 'ot_stage_completed' => $isCompleted, 'ot_approved_by' => $user->emp_id]);
            } else {
                //co_next_approver
                $plan->update(['ot_requested_status' => $statusData->m_id, 'ot_next_approver' => $sequence]);

                //start notify next approver & refresh page
                $nextApproverDetails =  ProcessApprover::with([
                    'fh_employee:emp_id,emp_fname,emp_email,emp_full_name,emp_d_id'
                ])->where((DB::raw('md5(pa_am_id)')), $data->module_id)->where(['pa_b_id' => $user->emp_b_id, 'pa_type' => $data->approval_action_type, 'pa_sequence' => $sequence])
                    ->orderBy('pa_am_id', 'asc')->first();

                if (isset($nextApproverDetails) && isset($nextApproverDetails->fh_employee)) {
                    if ($nextApproverDetails->fh_employee->exists()) {
                        $placeholders = [
                            '{receiver_name}' => $nextApproverDetails->fh_employee->emp_full_name,
                            '{submit_date}' => Carbon::parse($plan->created_at)->format('d M, Y'),
                            '{from_date}' =>  $plan->co_request_date ?? 'N/A',
                            '{employee_code}' => $plan->fh_employee->emp_code ?? 'N/A',
                            '{employee_name}' => $plan->fh_employee->emp_full_name ?? 'N/A',
                            '{employee_designation}' => $plan->fh_employee->fh_designation->dg_name ?? 'N/A',
                            '{employee_phone}' => $plan->fh_employee->emp_phone ?? 'N/A',
                            '{portal_url}' => route('leave.requests.show', ['id' => md5($plan->co_id)]),
                        ];

                        // The recipient's email address
                        $recipientEmail = $nextApproverDetails->fh_employee->emp_email;

                        // The email template type and business ID
                        $templateType = 401; // Replace with your mail template type
                        $businessId = $user->emp_b_id; // Replace with your business ID if applicable

                        // Sending the email using the CentralLogics class
                        $sent = CentralLogics::sendCustomEmail($templateType, $placeholders, $recipientEmail, $businessId);
                        // $mailData = [
                        //     'subject' => 'Leave Request Approval',
                        //     'url' => route('leave.requests.show', ['id' => md5($plan->co_id)]),
                        //     'mail_type' => 'LEAVE_APPROVAL_REQUEST',
                        //     'data' => ['leaveData' => $plan, 'nextApproverName' => $nextApproverDetails->fh_employee->emp_fname],
                        // ];
                        // CentralLogics::send_mail($nextApproverDetails->fh_employee->emp_email, new ApprovalMail($mailData));
                        //end notify next approver & refresh page
                    }
                }
            }
            $approversList = ProcessApprover::where(['pa_b_id' => $plan->co_b_id, 'pa_am_id' => $plan->co_am_id])->pluck('pa_emp_id');
        } elseif ($postType == 'FNF_REQUEST_APPROVAL') {
            $plan = EmployeeExitRequest::where((DB::raw('md5(er_id)')), $data->atd_id)->first();
            $approval = ApprovalLog::create([
                'log_am_id' => $plan->er_am_id,
                'log_request_id' => $plan->er_id,
                'log_module_id' => $plan->er_module_id,
                'log_user_id' => $user->emp_id,
                'log_user_role_id' => $user->emp_role_id,
                'log_status' => $statusData->m_id,
                'log_description' => $data->message,
            ]);
            if ($isLast) {
                $ruleCriteria = RuleCriterion::where('rc_b_id', $user->emp_b_id)->whereNot('rc_am_id', $plan->er_am_id)->where('rc_condition_option_id', $statusData->m_id)
                    ->whereHas('fh_approval_module', function ($query) {
                        $query->where('am_module_id', 603)->where('am_status', 1);
                    })->first();
                if (!$ruleCriteria) {
                    $amId = $plan->er_am_id;
                    $isCompleted = 1;
                } else {
                    $amId = $ruleCriteria->rc_am_id;
                    $isCompleted = 0;
                }
                // $plan->update(['er_request_status' => $statusData->m_id, 'er_next_approver' => 1, 'er_am_id' => $amId, 'er_stage_completed' => $isCompleted]);
                $plan->update([
                    'er_request_status' => $statusData->m_id,
                    'er_next_approver' => 1,
                    'er_am_id' => $amId,
                    'er_stage_completed' => $isCompleted,
                    'er_module_stage' => 1,
                ]);

                $fnfStage = Business::find($user->emp_b_id);

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


                // dd($managerReviewId, $hrReviewId, $financeReviewId, $adminReviewId);

                $user = Auth::user();
                $emp_b_id = $user->emp_b_id;

                $managerApproval = ApprovalModule::where('am_b_id',$emp_b_id)->where('am_module_id',$managerReviewId)->first();
                $hrApproval = ApprovalModule::where('am_b_id',$emp_b_id)->where('am_module_id',$hrReviewId)->first();
                $financeApproval = ApprovalModule::where('am_b_id',$emp_b_id)->where('am_module_id',$financeReviewId)->first();
                $adminApproval = ApprovalModule::where('am_b_id',$emp_b_id)->where('am_module_id',$adminReviewId)->first();




                $managerApproval    =  $managerApproval->am_id;
                $hrApproval         =  $hrApproval->am_id;
                $financeApproval    =  $financeApproval->am_id;
                $adminApproval      =  $adminApproval->am_id;


                if ($approval->log_status == 170) {

                    if ($plan->er_module_id == $managerReviewId) {

                        $plan->update([
                            'er_overall_status' => "MANAGER_REJECTED",
                        ]);


                           $manager_id = $user->emp_id;
                            Log::info('Manager ID fetched', ['manager_id' => $manager_id]);
                            $manager = Employee::with('fh_department', 'fh_designation')
                                ->where('emp_b_id', $user->emp_b_id)
                                ->where('emp_id', $manager_id)
                                ->first();

                            if (!$manager) {
                                Log::error('Manager not found', ['manager_id' => $manager_id]);

                                return response()->json([
                                    'status' => false,
                                    'message' => 'Manager not found'
                                ]);
                            }
                            Log::info('Manager fetched', ['manager' => $manager->emp_full_name]);
                            $employee = Employee::with('fh_department', 'fh_designation')
                                ->where('emp_b_id', $user->emp_b_id)
                                ->where('emp_id', $plan->er_emp_id)
                                ->first();

                            if (!$employee) {
                                Log::error('Employee not found', ['employee_id' => $plan->er_emp_id]);
                                return response()->json([
                                    'status' => false,
                                    'message' => 'Employee not found'
                                ]);
                            }

                            Log::info('Employee fetched', ['employee' => $employee->emp_full_name]);

                            $templates = MailTemplate::where('mt_b_id', $user->emp_b_id)
                                ->whereIn('mt_title', ['Manager Rejected Resignation'])
                                ->get()
                                ->keyBy('mt_title');

                            Log::info('Mail templates fetched', ['templates' => $templates->keys()]);
                            $sendEmail = function ($email, $subject, $body) {
                                try {
                                    Mail::html($body, function ($message) use ($email, $subject) {
                                        $message->to($email)->subject($subject);
                                    });

                                    Log::info('Email sent successfully', [
                                        'email' => $email,
                                        'subject' => $subject
                                    ]);
                                } catch (\Exception $e) {
                                    Log::error("Email failed", [
                                        'email' => $email,
                                        'error' => $e->getMessage()
                                    ]);
                                }
                            };
                            $replace = function ($body, $placeholders) {
                                return str_replace(array_keys($placeholders), array_values($placeholders), $body);
                            };

                            // Send email to Manager
                            if ($templates->has('Manager Rejected Resignation') && !empty($manager->emp_email)) {
                                Log::info('Preparing email for manager', ['email' => $manager->emp_email]);
                                $template = $templates['Manager Rejected Resignation'];
                                $body = $replace($template->mt_body, [
                                    '[Manager Name]'     => $manager->emp_full_name,
                                    '[Employee Name]'    => $employee->emp_full_name,
                                    '[Emp Code]'         => $employee->emp_code,
                                    '[Submission Date]'  => $employee->emp_separation_submit_date
                                        ? Carbon::parse($employee->emp_separation_submit_date)->format('d-m-Y')
                                        : '',
                                    '[Designation]'      => $employee->fh_designation->dg_name ?? '',
                                    '[Department]'       => $employee->fh_department->d_name ?? '',
                                    '[Effective LWD]'    => $employee->emp_last_working_date ?? 'Not Specified'
                                ]);

                                $sendEmail(
                                    $manager->emp_email,
                                    $template->mt_subject ?? 'Manager Rejected Resignation',
                                    $body
                                );
                            } else {
                                Log::warning('Email not sent', [
                                    'template_exists' => $templates->has('Manager Rejected Resignation'),
                                    'manager_email' => $manager->emp_email ?? null
                                ]);
                            }


                    } elseif ($plan->er_module_id == $hrReviewId) {

                        $plan->update([
                            'er_overall_status' => "HR_REJECTED",
                        ]);


                           $manager_id = $user->emp_id;
                            Log::info('Manager ID fetched', ['manager_id' => $manager_id]);
                            $manager = Employee::with('fh_department', 'fh_designation')
                                ->where('emp_b_id', $user->emp_b_id)
                                ->where('emp_id', $manager_id)
                                ->first();

                            if (!$manager) {
                                Log::error('Manager not found', ['manager_id' => $manager_id]);

                                return response()->json([
                                    'status' => false,
                                    'message' => 'Manager not found'
                                ]);
                            }
                            Log::info('Manager fetched', ['manager' => $manager->emp_full_name]);
                            $employee = Employee::with('fh_department', 'fh_designation')
                                ->where('emp_b_id', $user->emp_b_id)
                                ->where('emp_id', $plan->er_emp_id)
                                ->first();

                            if (!$employee) {
                                Log::error('Employee not found', ['employee_id' => $plan->er_emp_id]);
                                return response()->json([
                                    'status' => false,
                                    'message' => 'Employee not found'
                                ]);
                            }

                            Log::info('Employee fetched', ['employee' => $employee->emp_full_name]);

                            $templates = MailTemplate::where('mt_b_id', $user->emp_b_id)
                                ->whereIn('mt_title', ['HR Rejected Resignation'])
                                ->get()
                                ->keyBy('mt_title');

                            Log::info('Mail templates fetched', ['templates' => $templates->keys()]);
                            $sendEmail = function ($email, $subject, $body) {
                                try {
                                    Mail::html($body, function ($message) use ($email, $subject) {
                                        $message->to($email)->subject($subject);
                                    });

                                    Log::info('Email sent successfully', [
                                        'email' => $email,
                                        'subject' => $subject
                                    ]);
                                } catch (\Exception $e) {
                                    Log::error("Email failed", [
                                        'email' => $email,
                                        'error' => $e->getMessage()
                                    ]);
                                }
                            };
                            $replace = function ($body, $placeholders) {
                                return str_replace(array_keys($placeholders), array_values($placeholders), $body);
                            };

                            // Send email to Manager
                            if ($templates->has('HR Rejected Resignation') && !empty($manager->emp_email)) {
                                Log::info('Preparing email for manager', ['email' => $manager->emp_email]);
                                $template = $templates['HR Rejected Resignation'];
                                $body = $replace($template->mt_body, [
                                    '[Manager Name]'     => $manager->emp_full_name,
                                    '[Employee Name]'    => $employee->emp_full_name,
                                    '[Emp Code]'         => $employee->emp_code,
                                    '[Submission Date]'  => $employee->emp_separation_submit_date
                                        ? Carbon::parse($employee->emp_separation_submit_date)->format('d-m-Y')
                                        : '',
                                    '[Designation]'      => $employee->fh_designation->dg_name ?? '',
                                    '[Department]'       => $employee->fh_department->d_name ?? '',
                                    '[Effective LWD]'    => $employee->emp_last_working_date ?? 'Not Specified'
                                ]);

                                $sendEmail(
                                    $manager->emp_email,
                                    $template->mt_subject ?? 'HR Rejected Resignation',
                                    $body
                                );
                            } else {
                                Log::warning('Email not sent', [
                                    'template_exists' => $templates->has('HR Rejected Resignation'),
                                    'manager_email' => $manager->emp_email ?? null
                                ]);
                            }

                    }
                } else {

                    if ($plan->er_next_approver == 1 || ($plan->er_stage_completed == 1 && $plan->er_status != 170)) {

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

                        if ($plan->er_module_id == $managerReviewId) {

                            $nextStage = getNextStage($flow, $managerReviewId);

                            $plan->update([
                                'er_overall_status'  => "MANAGER_APPROVED",
                                'er_am_id'           => $financeApproval ?? 208,
                                'er_status'          => 140,
                                'er_next_approver'   => $nextStage ? 1 : 0,
                                'er_stage_completed' => $nextStage ? 0 : 1,
                                'er_module_id'       => $nextStage ?? $managerReviewId,
                            ]);


                            $manager_id = $user->emp_id;
                            Log::info('Manager ID fetched', ['manager_id' => $manager_id]);
                            $manager = Employee::with('fh_department', 'fh_designation')
                                ->where('emp_b_id', $user->emp_b_id)
                                ->where('emp_id', $manager_id)
                                ->first();

                            if (!$manager) {
                                Log::error('Manager not found', ['manager_id' => $manager_id]);

                                return response()->json([
                                    'status' => false,
                                    'message' => 'Manager not found'
                                ]);
                            }
                            Log::info('Manager fetched', ['manager' => $manager->emp_full_name]);
                            $employee = Employee::with('fh_department', 'fh_designation')
                                ->where('emp_b_id', $user->emp_b_id)
                                ->where('emp_id', $plan->er_emp_id)
                                ->first();

                            if (!$employee) {
                                Log::error('Employee not found', ['employee_id' => $plan->er_emp_id]);
                                return response()->json([
                                    'status' => false,
                                    'message' => 'Employee not found'
                                ]);
                            }

                            Log::info('Employee fetched', ['employee' => $employee->emp_full_name]);

                            $templates = MailTemplate::where('mt_b_id', $user->emp_b_id)
                                ->whereIn('mt_title', ['Manager Approved Resignation'])
                                ->get()
                                ->keyBy('mt_title');

                            Log::info('Mail templates fetched', ['templates' => $templates->keys()]);
                            $sendEmail = function ($email, $subject, $body) {
                                try {
                                    Mail::html($body, function ($message) use ($email, $subject) {
                                        $message->to($email)->subject($subject);
                                    });

                                    Log::info('Email sent successfully', [
                                        'email' => $email,
                                        'subject' => $subject
                                    ]);
                                } catch (\Exception $e) {
                                    Log::error("Email failed", [
                                        'email' => $email,
                                        'error' => $e->getMessage()
                                    ]);
                                }
                            };
                            $replace = function ($body, $placeholders) {
                                return str_replace(array_keys($placeholders), array_values($placeholders), $body);
                            };

                            // Send email to Manager
                            if ($templates->has('Manager Approved Resignation') && !empty($manager->emp_email)) {
                                Log::info('Preparing email for manager', ['email' => $manager->emp_email]);
                                $template = $templates['Manager Approved Resignation'];
                                $body = $replace($template->mt_body, [
                                    '[Manager Name]'     => $manager->emp_full_name,
                                    '[Employee Name]'    => $employee->emp_full_name,
                                    '[Emp Code]'         => $employee->emp_code,
                                    '[Submission Date]'  => $employee->emp_separation_submit_date
                                        ? Carbon::parse($employee->emp_separation_submit_date)->format('d-m-Y')
                                        : '',
                                    '[Designation]'      => $employee->fh_designation->dg_name ?? '',
                                    '[Department]'       => $employee->fh_department->d_name ?? '',
                                    '[Effective LWD]'    => $employee->emp_last_working_date ?? 'Not Specified'
                                ]);

                                $sendEmail(
                                    $manager->emp_email,
                                    $template->mt_subject ?? 'Manager Approved Resignation',
                                    $body
                                );
                            } else {
                                Log::warning('Email not sent', [
                                    'template_exists' => $templates->has('Manager Approved Resignation'),
                                    'manager_email' => $manager->emp_email ?? null
                                ]);
                            }
                        } elseif ($plan->er_module_id == $financeReviewId) {

                            $nextStage = getNextStage($flow, $financeReviewId);

                            $plan->update([
                                'er_overall_status'  => "CLEARANCE_IN_PROGRESS",
                                'er_am_id'           => $hrApproval ?? 207,
                                'er_status'          => 140,
                                'er_next_approver'   => $nextStage ? 1 : 0,
                                'er_stage_completed' => $nextStage ? 0 : 1,
                                'er_module_id'       => $nextStage ?? $financeReviewId,
                            ]);
                        } elseif ($plan->er_module_id == $hrReviewId) {

                            $nextStage = getNextStage($flow, $hrReviewId);

                            $plan->update([
                                'er_overall_status'  => "HR_APPROVED",
                                'er_am_id'           => $adminApproval ?? 206,
                                'er_status'          => 140,
                                'er_next_approver'   => $nextStage ? 1 : 0,
                                'er_stage_completed' => $nextStage ? 0 : 1,
                                'er_module_id'       => $nextStage ?? $hrReviewId,
                            ]);



                             $manager_id = $user->emp_id;
                            Log::info('Manager ID fetched', ['manager_id' => $manager_id]);
                            $manager = Employee::with('fh_department', 'fh_designation')
                                ->where('emp_b_id', $user->emp_b_id)
                                ->where('emp_id', $manager_id)
                                ->first();

                            if (!$manager) {
                                Log::error('Manager not found', ['manager_id' => $manager_id]);

                                return response()->json([
                                    'status' => false,
                                    'message' => 'Manager not found'
                                ]);
                            }
                            Log::info('Manager fetched', ['manager' => $manager->emp_full_name]);
                            $employee = Employee::with('fh_department', 'fh_designation')
                                ->where('emp_b_id', $user->emp_b_id)
                                ->where('emp_id', $plan->er_emp_id)
                                ->first();

                            if (!$employee) {
                                Log::error('Employee not found', ['employee_id' => $plan->er_emp_id]);
                                return response()->json([
                                    'status' => false,
                                    'message' => 'Employee not found'
                                ]);
                            }

                            Log::info('Employee fetched', ['employee' => $employee->emp_full_name]);

                            $templates = MailTemplate::where('mt_b_id', $user->emp_b_id)
                                ->whereIn('mt_title', ['HR Approved Resignation'])
                                ->get()
                                ->keyBy('mt_title');

                            Log::info('Mail templates fetched', ['templates' => $templates->keys()]);
                            $sendEmail = function ($email, $subject, $body) {
                                try {
                                    Mail::html($body, function ($message) use ($email, $subject) {
                                        $message->to($email)->subject($subject);
                                    });

                                    Log::info('Email sent successfully', [
                                        'email' => $email,
                                        'subject' => $subject
                                    ]);
                                } catch (\Exception $e) {
                                    Log::error("Email failed", [
                                        'email' => $email,
                                        'error' => $e->getMessage()
                                    ]);
                                }
                            };
                            $replace = function ($body, $placeholders) {
                                return str_replace(array_keys($placeholders), array_values($placeholders), $body);
                            };

                            // Send email to Manager
                            if ($templates->has('HR Approved Resignation') && !empty($manager->emp_email)) {
                                Log::info('Preparing email for manager', ['email' => $manager->emp_email]);
                                $template = $templates['HR Approved Resignation'];
                                $body = $replace($template->mt_body, [
                                    '[Manager Name]'     => $manager->emp_full_name,
                                    '[Employee Name]'    => $employee->emp_full_name,
                                    '[Emp Code]'         => $employee->emp_code,
                                    '[Submission Date]'  => $employee->emp_separation_submit_date
                                        ? Carbon::parse($employee->emp_separation_submit_date)->format('d-m-Y')
                                        : '',
                                    '[Designation]'      => $employee->fh_designation->dg_name ?? '',
                                    '[Department]'       => $employee->fh_department->d_name ?? '',
                                    '[Effective LWD]'    => $employee->emp_last_working_date ?? 'Not Specified'
                                ]);

                                $sendEmail(
                                    $manager->emp_email,
                                    $template->mt_subject ?? 'HR Approved Resignation',
                                    $body
                                );
                            } else {
                                Log::warning('Email not sent', [
                                    'template_exists' => $templates->has('HR Approved Resignation'),
                                    'manager_email' => $manager->emp_email ?? null
                                ]);
                            }

                            
                        } elseif ($plan->er_module_id == $adminReviewId) {

                            $plan->update([
                                'er_overall_status'  => "RELIEVED",
                                'er_am_id'           => $adminApproval ?? 206,
                                'er_status'          => 140,
                                'er_next_approver'   => 0,
                                'er_stage_completed' => 1,
                                'er_module_id'       => $adminReviewId,
                            ]);
                        }
                    }
                }
            } else {
                $plan->update(['er_request_status' => $statusData->m_id, 'er_next_approver' => $sequence]);

                //start notify next approver & refresh page
                $nextApproverDetails =  ProcessApprover::with([
                    'fh_employee:emp_id,emp_fname,emp_email,emp_full_name,emp_d_id'
                ])->where((DB::raw('md5(pa_am_id)')), $data->module_id)->where(['pa_b_id' => $user->emp_b_id, 'pa_type' => $data->approval_action_type, 'pa_sequence' => $sequence])
                    ->orderBy('pa_am_id', 'asc')->first();
            }
            // event(new TravelApprovalEvent($plan->trp_id));
            $approversList = ProcessApprover::where(['pa_b_id' => $plan->er_b_id, 'pa_am_id' => $plan->er_am_id])->pluck('pa_emp_id');
        } elseif ($postType == 'OUTDOOR_REQUEST_APPROVAL') {
            $plan = AttendanceOutDoor::where((DB::raw('md5(atd_od_id)')), $data->atd_od_id)->first();
            if (!$plan) {
                return; // nothing to do
            }

            $employee = $plan->fh_employee ?? Employee::find($plan->atd_od_emp_id);

            $title = 'Out Door Attendance ' . ($data->approval_type == 1 ? 'Approved' : 'Rejected');
            $requestDate = $plan->atd_od_date ? $plan->atd_od_date->format('d-m-Y') : 'N/A';
            $body = "Your out door attendance request dated {$requestDate} has been " . ($data->approval_type == 1 ? 'Approved' : 'Rejected');

            $additionalData = [
                'notification_type' => 'outdoor_status',
                'status' => $data->approval_type == 1 ? 'approved' : 'rejected',
                'route' => '/OutDoorPage',
                'remark' => $data->message,
            ];

            $serviceAccountPath = public_path('fixhr-app-firebase.json');

            ApprovalLog::create([
                'log_am_id' => $plan->atd_od_am_id,
                'log_request_id' => $plan->atd_od_id,
                'log_user_id' => $user->emp_id,
                'log_user_role_id' => $user->emp_role_id,
                'log_status' => $statusData->m_id,
                'log_description' => $data->message,
                'log_module_id' => $plan->atd_od_module_id,
            ]);

            if ($isLast) {
                $ruleCriteria = RuleCriterion::where('rc_b_id', $user->emp_b_id)
                    ->whereNot('rc_am_id', $plan->atd_od_am_id)
                    ->where('rc_condition_option_id', $statusData->m_id)
                    ->whereHas('fh_approval_module', function ($query) {
                        $query->where('am_module_id', 589)->where('am_status', 1);
                    })->first();

                if (!$ruleCriteria) {
                    $amId = $plan->atd_od_am_id;
                    $isCompleted = 1;
                } else {
                    $amId = $ruleCriteria->rc_am_id;
                    $isCompleted = 0;
                }

                if ($isCompleted && $statusData->m_id != 170) {
                    // Check weekly off / holiday
                    $isWeeklyOff = CentralLogics::getWeekOffDatesReport($employee, null, null, Carbon::parse($plan->atd_od_date)->format('Y-m-d'), Carbon::parse($plan->atd_od_date)->format('Y-m-d'));

                    $isHoliday = PolicyHolidayList::where('phl_b_id', $employee->emp_b_id)
                        ->whereDate('phl_start_date', '<=', $plan->atd_od_date)
                        ->whereDate('phl_end_date', '>=', $plan->atd_od_date)
                        ->where('phl_type_id', 206)
                        ->exists();

                    if ($isWeeklyOff || $isHoliday) {
                        $atd_status = $isWeeklyOff ? 320 : 319; // Weekly Off Work : Holiday Work
                        $statusData->m_id != 170 && $isCompleted && CentralLogics::generateCompOff($plan->fh_employee, $plan->atd_od_date, 1);
                    }

                    $employee = Employee::find($plan->atd_od_emp_id);
                    $resolvedShift = ShiftResolver::resolveEmployeeShift($employee, $plan->atd_od_date);
                    $shift = $resolvedShift->shift ?? PolicyShiftTiming::find($employee->emp_shift_type_id);
                    $checkInTime = $plan->atd_od_check_in_time;
                    $checkOutTime = $plan->atd_od_check_out_time;
                    $totalHours = $plan->atd_od_total_working_hours ?? 0.00;

                    if ($shift && $plan->atd_od_check_in_time > $shift->pst_start_time) {
                        $checkInTime = $shift->pst_start_time;
                        $totalHours = round(Carbon::parse($shift->pst_start_time)->diffInMinutes(Carbon::parse($shift->pst_end_time)) / 60, 2);
                    }

                    if ($shift && $shift->pst_end_time > $plan->atd_od_check_out_time) {
                        $checkOutTime = $shift->pst_end_time;
                        $totalHours = round(Carbon::parse($shift->pst_start_time)->diffInMinutes(Carbon::parse($shift->pst_end_time)) / 60, 2);
                    }

                    AttendanceRecord::create([
                        'atd_b_id' => $plan->atd_od_b_id,
                        'atd_emp_id' => $plan->atd_od_emp_id,
                        'atd_device_id' => $plan->atd_od_device_id,
                        'atd_date' => $plan->atd_od_date,
                        'atd_pst_id' => $plan->atd_od_pst_id,
                        'atd_work_mode_type_id' => $plan->atd_od_work_mode_type_id,
                        'atd_checkin_method_id' => $plan->atd_od_checkin_method_id,
                        'atd_check_in_time' => $checkInTime,
                        'atd_check_out_time' => $checkOutTime,
                        'atd_segments' => $plan->atd_od_segments,
                        'atd_is_late' => 0,
                        'atd_late_duration' => 0.00,
                        'atd_is_overtime' => 0,
                        'atd_overtime_hours' => 0.00,
                        'atd_attendance_status' => 251,
                        'atd_punchin_photo' => $plan->atd_od_punchin_photo,
                        'atd_punchout_photo' => $plan->atd_od_punchout_photo,
                        'atd_punchin_location' => $plan->atd_od_punchin_location,
                        'atd_punchout_location' => $plan->atd_od_punchout_location,
                        'atd_longitude_punchin' => $plan->atd_od_longitude_punchin,
                        'atd_latitude_punchin' => $plan->atd_od_latitude_punchin,
                        'atd_longitude_punchout' => $plan->atd_od_longitude_punchout,
                        'atd_latitude_punchout' => $plan->atd_od_latitude_punchout,
                        'atd_updated_by' => $plan->atd_od_approved_by,
                        'atd_remark' => $plan->atd_od_remark,
                        'atd_stage_completed' => 1,
                        'atd_request_status' => 157,
                        'atd_next_approver' => 1,
                        'atd_module_id' => 249,
                        'atd_am_id' => $plan->atd_od_am_id,
                        'atd_total_worked_hours' => $totalHours,

                        // 'atd_is_late' => $plan->atd_od_is_late,
                        // 'atd_late_duration' => $plan->atd_od_late_duration,
                        // 'atd_is_overtime' => $plan->atd_od_is_overtime,
                        // 'atd_overtime_hours' => $plan->atd_od_overtime_hours,
                        // 'atd_attendance_status' => $plan->atd_od_attendance_status ?? ($atd_status ?? null),
                        // 'atd_punchin_photo' => $plan->atd_od_punchin_photo,
                        // 'atd_punchout_photo' => $plan->atd_od_punchout_photo,
                        // 'atd_punchin_location' => $plan->atd_od_punchin_location,
                        // 'atd_punchout_location' => $plan->atd_od_punchout_location,
                        // 'atd_longitude_punchin' => $plan->atd_od_longitude_punchin,
                        // 'atd_latitude_punchin' => $plan->atd_od_latitude_punchin,
                        // 'atd_longitude_punchout' => $plan->atd_od_longitude_punchout,
                        // 'atd_latitude_punchout' => $plan->atd_od_latitude_punchout,
                        // 'atd_updated_by' => $plan->atd_od_approved_by,
                        // 'atd_remark' => $plan->atd_od_remark,
                        // 'atd_stage_completed' => $plan->atd_od_stage_completed,
                        // 'atd_request_status' => $plan->atd_od_request_status,
                        // 'atd_next_approver' => $plan->atd_od_next_approver,
                        // 'atd_module_id' => 249,
                        // 'atd_am_id' => $plan->atd_od_am_id,
                        // 'atd_total_worked_hours' => $plan->atd_od_total_working_hours,
                    ]);
                }

                $currentApproverId = Auth::user()->emp_id;

                // Append current approver to updated auth ids (comma separated, unique)
                $existingUpdated = $plan->atd_od_updated_auth_id ?? '';
                $idsArr = array_filter(array_map('trim', explode(',', $existingUpdated)));
                if (!in_array($currentApproverId, $idsArr)) {
                    $idsArr[] = $currentApproverId;
                }
                $updatedAuthStr = implode(',', $idsArr);

                $plan->update([
                    'atd_od_request_status' => $statusData->m_id,
                    'atd_od_next_approver' => 1,
                    'atd_od_next_approver_id' => null,
                    'atd_od_am_id' => $amId,
                    'atd_od_stage_completed' => $isCompleted,
                    'atd_od_approved_by' => $currentApproverId,
                    'atd_od_updated_auth_id' => $updatedAuthStr,
                ]);

                try {
                    if ($employee) {
                        $titleF = 'Out door ' . ($data->approval_type == 1 ? 'Approved' : ($data->approval_type != 1 ? 'Rejected' : 'Updated'));
                        $bodyF  = 'Your out door request has been ' . ($data->approval_type == 1 ? 'approved' : ($data->approval_type != 1 ? 'rejected' : 'updated')) . '.';
                        $dataF = [ 'notification_type' => 'outdoor_status', 'status' => ($data->approval_type == 1 ? 'approved' : ($data->approval_type != 1 ? 'rejected' : 'updated')), 'route' => '/OutDoorPage', 'atd_od_id' => $plan->atd_od_id ];
                        if (!empty($employee->emp_fcm_token)) { FirebaseNotification::sendPushNotification($titleF, $bodyF, $employee->emp_fcm_token, $serviceAccountPath, config('credentials')['FIREBASE_MESSAGING_CONFIG'], $dataF); }
                        NotificationHelper::saveNotification($user->emp_id, $employee->emp_id, $titleF, $bodyF, $dataF);
                    }
                } catch (\Throwable $e) { \Log::warning('Outdoor final requester notification failed: ' . $e->getMessage()); }
            } else {
                // increment sequence to point to next approver
                $nextSequence = intval($sequence);
                $currentApproverId = Auth::user()->emp_id;

                // determine next approver id (if available)
                $nextApproverDetails = ProcessApprover::with([
                    'fh_employee:emp_id,emp_fname,emp_email,emp_full_name,emp_d_id'
                ])->where('pa_am_id', $plan->atd_od_am_id)
                  ->where('pa_b_id', $user->emp_b_id)
                  ->where('pa_sequence', $nextSequence)
                  ->orderBy('pa_id', 'asc')->first();

                $nextApproverId = $nextApproverDetails?->fh_employee?->emp_id ?? null;

                // Append current approver to updated auth ids (comma separated, unique)
                $existingUpdated = $plan->atd_od_updated_auth_id ?? '';
                $idsArr = array_filter(array_map('trim', explode(',', $existingUpdated)));
                if (!in_array($currentApproverId, $idsArr)) {
                    $idsArr[] = $currentApproverId;
                }
                $updatedAuthStr = implode(',', $idsArr);

                $plan->update([
                    'atd_od_request_status' => $statusData->m_id,
                    'atd_od_next_approver' => $nextSequence,
                    'atd_od_next_approver_id' => $nextApproverId,
                    'atd_od_approved_by' => $currentApproverId,
                    'atd_od_updated_auth_id' => $updatedAuthStr,
                ]);

                // notify next approver & refresh page
                if ($nextApproverDetails && $nextApproverDetails->fh_employee) {
                    try {
                        $approverEmp = $nextApproverDetails->fh_employee;
                        $title = 'Out door Approval Pending';
                        $body = 'Approval required for an out door request.';
                        $additionalData = [
                            'notification_type' => 'outdoor_approval',
                            'status' => 'pending',
                            'route' => '/OutDoorApprovalPage',
                            'atd_od_id' => $plan->atd_od_id
                        ];
                        if (!empty($approverEmp->emp_fcm_token)) {
                            FirebaseNotification::sendPushNotification($title, $body, $approverEmp->emp_fcm_token, $serviceAccountPath, config('credentials')['FIREBASE_MESSAGING_CONFIG'], $additionalData);
                        }
                        NotificationHelper::saveNotification($user->emp_id, $approverEmp->emp_id, $title, $body, $additionalData);
                    } catch (\Throwable $e) { \Log::warning('Outdoor next-approver notification failed: ' . $e->getMessage()); }
                }
            }

            $approversList = ProcessApprover::where(['pa_b_id' => $plan->atd_od_b_id, 'pa_am_id' => $plan->atd_od_am_id])->pluck('pa_emp_id');
        }
    }

    public function handleDeduction(Request $request)
    {
        $response['result'] = [];
        $response['status'] = false;
        $response['message'] = '';
        $user = Auth::user();
        $data = (object)$request->data;

        $data->tc_id;
        $data->action;
        $data->remarks;
        $claim = TadaClaim::where((DB::raw('md5(tc_id)')), $data->tc_id)->first();
        // Get the query builder for the fh_deduction_log relationship
        $deductionDataQuery = $claim->fh_deduction_log();
        // Get the most recent deduction entry by dlog_id
        $deductedPerson = isset($deductionDataQuery->orderByDesc('dlog_id')->first()->fh_employee) ? $deductionDataQuery->orderByDesc('dlog_id')->first()->fh_employee : '';

        $dLog = DeductionLog::where((DB::raw('md5(dlog_id)')), $data->dlog_id)->first();

        if ($data->action == 1) {
            $nextApprovalData = NextApprovalDetail::where('nxt_tc_id', $claim->tc_id)->first();
            $this->updateStatusAndNextApproval('CLAIM_DEDUCTION_ACCEPTANCE', $claim, $claim->tc_status, $nextApprovalData?->nxt_approval_type, $nextApprovalData?->nxt_approver_sequence, $nextApprovalData?->nxt_is_last, ['tc_deduction_status' => 1, 'tc_deduction_remarks' => $data->remarks]);
            $response['status'] = true;
            $response['message'] = 'Deduction Accepted';
            //$this->notifyAccounts($claim); // Notify Accounts
            if ($deductedPerson) {
                $claimSummary = "Description\tAmount\n";
                $claimSummary .= "Claimed Amount\t$claim->tc_amount including DA\n";
                $claimSummary .= "Approved Amount\t" . ($claim->tc_amount - $claim->tc_deduction_amount) . " including DA\n";
                $claimSummary .= "Deductions\t$claim->tc_deduction_amount\n";
                $placeholders = [
                    '{request_id}' => isset($claim->tc_unique_id) ? $claim->tc_unique_id : 'N/A',
                    '{ref_id}' => isset($claim->fh_tada_request_plan) ? $claim->fh_tada_request_plan->trp_unique_id : 'N/A',
                    '{receiver_name}' => $deductedPerson->emp_full_name,
                    '{approver_name}' => $deductedPerson->emp_full_name,
                    '{claim_summary}' => $claimSummary,
                    '{employee_name}' => $plan->fh_employee->emp_full_name ?? 'N/A',
                    '{employee_code}' => $plan->fh_employee->emp_code ?? 'N/A',
                    '{employee_designation}' => $plan->fh_employee->fh_designation->dg_name ?? 'N/A',
                    '{employee_phone}' => $plan->fh_employee->emp_phone ?? 'N/A',
                    '{portal_url}' => route('claim.request.show', ['id' => md5($claim->tc_id)]),
                ];
                // The recipient's email address
                $recipientEmail = $deductedPerson->emp_email;
                // The email template type and business ID
                $templateType = 403; // Replace with your mail template type
                $businessId = $user->emp_b_id; // Replace with your business ID if applicable
                // Sending the email using the CentralLogics class
                $sent = CentralLogics::sendCustomEmail($templateType, $placeholders, $recipientEmail, $businessId);

                  // Push: notify requester and first approver
                try {
                    $requester = $claim->fh_tada_request_plan->fh_employee;
                    if ($requester) {
                        $titleReq = 'Claim Deduction Accepted';
                        $bodyReq  = 'You accepted deductions for claim ' . ($claim->tc_unique_id ?? '') . '.';
                        $additionalDataReq = [
                            'notification_type' => 'claim_deduction',
                            'status' => 'accepted',
                            'route' => '/TadaApprovalList',
                            'claim_id' => $claim->tc_id,
                        ];
                        if (!empty($requester->emp_fcm_token)) {
                            $serviceAccountPath = public_path('fixhr-app-firebase.json');
                            FirebaseNotification::sendPushNotification(
                                $titleReq,
                                $bodyReq,
                                $requester->emp_fcm_token,
                                $serviceAccountPath,
                                config('credentials')['FIREBASE_MESSAGING_CONFIG'],
                                $additionalDataReq
                            );
                        }
                        NotificationHelper::saveNotification($requester->emp_id, $requester->emp_id, $titleReq, $bodyReq, $additionalDataReq);
                    }
                    $firstApprover = ProcessApprover::where('pa_am_id', $claim->tc_am_id)->where('pa_sequence', 1)->with('fh_employee')->first();
                    if ($firstApprover && $firstApprover->fh_employee) {
                        $titleAp = 'Claim Deduction Accepted';
                        $bodyAp  = 'Requester accepted deduction for claim ' . ($claim->tc_unique_id ?? '') . '.';
                        $additionalDataAp = [
                            'notification_type' => 'claim_deduction',
                            'status' => 'accepted',
                            'route' => '/TadaApprovalList',
                            'claim_id' => $claim->tc_id,
                        ];
                        if (!empty($firstApprover->fh_employee->emp_fcm_token)) {
                            $serviceAccountPath = public_path('fixhr-app-firebase.json');
                            FirebaseNotification::sendPushNotification(
                                $titleAp,
                                $bodyAp,
                                $firstApprover->fh_employee->emp_fcm_token,
                                $serviceAccountPath,
                                config('credentials')['FIREBASE_MESSAGING_CONFIG'],
                                $additionalDataAp
                            );
                        }
                        NotificationHelper::saveNotification($requester->emp_id ?? $user->emp_id, $firstApprover->fh_employee->emp_id, $titleAp, $bodyAp, $additionalDataAp);
                    }
                } catch (\Throwable $e) {
                    \Log::warning('Claim deduction accepted notifications failed: ' . $e->getMessage());
                }
            }
        } elseif ($data->action == 0) {
            $claim->tc_deduction_amount = $claim->tc_deduction_amount ? ($claim->tc_deduction_amount -  $dLog->dlog_deduction_amount) : 0;
            $claim->save();
            $response['status'] = true;
            $response['message'] = 'Deduction Decline';
            if ($deductedPerson) {
                $claimSummary = "Description\tAmount\n";
                $claimSummary .= "Claimed Amount\t$claim->tc_amount including DA\n";
                $claimSummary .= "Approved Amount\t" . ($claim->tc_amount - $claim->tc_deduction_amount) . " including DA\n";
                $claimSummary .= "Deductions\t$claim->tc_deduction_amount\n";
                $placeholders = [
                    '{request_id}' => isset($claim->tc_unique_id) ? $claim->tc_unique_id : 'N/A',
                    '{ref_id}' => isset($claim->fh_tada_request_plan) ? $claim->fh_tada_request_plan->trp_unique_id : 'N/A',
                    '{receiver_name}' => $deductedPerson->emp_full_name,
                    '{claim_summary}' => $claimSummary,
                    '{employee_name}' => $plan->fh_employee->emp_full_name ?? 'N/A',
                    '{employee_code}' => $plan->fh_employee->emp_code ?? 'N/A',
                    '{employee_designation}' => $plan->fh_employee->fh_designation->dg_name ?? 'N/A',
                    '{employee_phone}' => $plan->fh_employee->emp_phone ?? 'N/A',
                    '{portal_url}' => route('claim.request.show', ['id' => md5($claim->tc_id)]),
                ];

                // The recipient's email address
                $recipientEmail = $deductedPerson->emp_email;
                // The email template type and business ID
                $templateType = 404; // Replace with your mail template type
                $businessId = $user->emp_b_id; // Replace with your business ID if applicable
                // Sending the email using the CentralLogics class
                $sent = CentralLogics::sendCustomEmail($templateType, $placeholders, $recipientEmail, $businessId);
                // Push: notify first approver to recheck
                try {
                    $firstApprover = ProcessApprover::where('pa_am_id', $claim->tc_am_id)->where('pa_sequence', 1)->with('fh_employee')->first();
                    if ($firstApprover && $firstApprover->fh_employee) {
                        $titleAp = 'Claim Deduction Declined';
                        $bodyAp  = 'Requester declined deduction for claim ' . ($claim->tc_unique_id ?? '') . '. Please recheck.';
                        $additionalDataAp = [
                            'notification_type' => 'claim_deduction',
                            'status' => 'declined',
                            'route' => '/TadaApprovalList',
                            'claim_id' => $claim->tc_id,
                        ];
                        if (!empty($firstApprover->fh_employee->emp_fcm_token)) {
                            $serviceAccountPath = public_path('fixhr-app-firebase.json');
                            FirebaseNotification::sendPushNotification(
                                $titleAp,
                                $bodyAp,
                                $firstApprover->fh_employee->emp_fcm_token,
                                $serviceAccountPath,
                                config('credentials')['FIREBASE_MESSAGING_CONFIG'],
                                $additionalDataAp
                            );
                        }
                        NotificationHelper::saveNotification($user->emp_id, $firstApprover->fh_employee->emp_id, $titleAp, $bodyAp, $additionalDataAp);
                    }
                } catch (\Throwable $e) {
                    \Log::warning('Claim deduction declined notifications failed: ' . $e->getMessage());
                }
                // $mailData = [
                //     'subject' => "Expense Claim Deduction Decline - Claim REF ID: #" . $claim->fh_tada_request_plan->trp_unique_id,
                //     'url' => route('claim.request.show', ['id' => md5($claim->tc_id)]),
                //     'mail_type' => 'CLAIM_DEDUCTION_DECLINED',
                //     'data' => ['claimData' => $claim, "receiverName" => $deductedPerson->emp_full_name, 'deduction_remark' => $data->remarks],
                // ];
                // CentralLogics::send_mail($deductedPerson->emp_email, new ApprovalMail($mailData));
            }
            //$this->notifyHOD($claim);// Notify HOD
        }
        $dLog->update([
            'dlog_requester_action' => $data->action,
            'dlog_remarks' => $data->remarks,
        ]);

        // event(new ApprovalEvent($claim->tc_id));
        $approversList = ProcessApprover::where(['pa_b_id' => $claim->tc_b_id, 'pa_am_id' => $claim->tc_am_id])->pluck('pa_emp_id');
        //$this->addDataToFirebase($claim->tc_id,'ACCEPTANCE_EVENT',$approversList,$claim->tc_b_id,$claim->tc_emp_id);
        return response()->json($response);
    }

    public function updateStatusAndNextApproval($type, $claim, $statusId, $approval_type, $sequence, $is_last, $additionUptArr)
    {
        $user = Auth::user();
        $statusData = MasterTable::where(['m_group' => 'APPROVAL_STATUS', 'm_id' => $statusId])->select('m_id', 'm_name')->first();
        if ($type == 'CLAIM_REQUEST_APPROVAL' || $type == 'CLAIM_DEDUCTION_ACCEPTANCE') {
            if ($is_last) {
                $ruleCriteria = RuleCriterion::where('rc_b_id', $user->emp_b_id)->whereNot('rc_am_id', $claim->tc_am_id)->where('rc_condition_option_id', $statusId)
                    ->whereHas('fh_approval_module', function ($query) {
                        $query->where('am_module_id', 146)->where('am_status', 1);
                    })->first();
                if (!$ruleCriteria) {
                    $amId = $claim->tc_am_id;
                    $isCompleted = 1;
                } else {
                    $amId = $ruleCriteria->rc_am_id;
                    $isCompleted = 0;
                }
                $updateData = ['tc_next_approver' => 1, 'tc_am_id' => $amId, 'tc_stage_completed' => $isCompleted];
            } else {
                $updateData = ['tc_next_approver' => $sequence];

                ($type == 'CLAIM_DEDUCTION_ACCEPTANCE' ? ' with deduction' : '');

                //start notify next approver & refresh page
                $nextApproverDetails =  ProcessApprover::with([
                    'fh_employee:emp_id,emp_fname,emp_email,emp_full_name,emp_d_id'
                ])->where('pa_am_id', $claim->tc_am_id)->where(['pa_b_id' => $user->emp_b_id, 'pa_type' => $approval_type, 'pa_sequence' => $sequence])
                    ->orderBy('pa_am_id', 'asc')->first();
                if ($nextApproverDetails && $nextApproverDetails->fh_employee->exists()) {
                    $expenses = $claim->fh_tada_request_plan->fh_tada_expenses ?? []; //$data['data']['claimData']->fh_tada_request_plan->fh_tada_expenses ?? [];
                    if ($expenses->isNotEmpty()) {
                        $expenseSummary = "Below is a brief summary of expenses:\n\n";
                        $expenseSummary .= "S.No.\tParticulars\tAmount\n";

                        $i = 1;
                        foreach ($expenses->groupBy('te_type_id') as $tadaExpenseItem) {
                            $particulars = $tadaExpenseItem[0]->fh_expense_type->m_name ?? 'N/A';
                            $amount = $tadaExpenseItem->sum('te_amount') + $tadaExpenseItem->sum('te_taxes');
                            $expenseSummary .= "$i\t$particulars\t$amount\n";
                            $i++;
                        }
                        $totalExpenses = $expenses->sum('te_amount') + $expenses->sum('te_taxes');
                        $expenseSummary .= "\nTotal Expenses: $totalExpenses\n";
                    } else {
                        $expenseSummary = "No expenses were reported.";
                    }

                    $placeholders = [
                        '{receiver_name}' => $nextApproverDetails->fh_employee->emp_full_name,
                        '{approver_name}' => $nextApproverDetails->fh_employee->emp_full_name,
                        '{request_id}' => isset($claim->tc_unique_id) ? $claim->tc_unique_id : 'N/A',
                        '{ref_id}' => isset($claim->fh_tada_request_plan) ? $claim->fh_tada_request_plan->trp_unique_id : 'N/A',
                        '{claim_date}' => isset($claim->tc_date) ? Carbon::parse($claim->created_at)->format('d-M-Y') : 'N/A',
                        '{employee_name}' => $plan->fh_employee->emp_full_name ?? 'N/A',
                        '{employee_code}' => $plan->fh_employee->emp_code ?? 'N/A',
                        '{employee_designation}' => $plan->fh_employee->fh_designation->dg_name ?? 'N/A',
                        '{employee_phone}' => $plan->fh_employee->emp_phone ?? 'N/A',
                        '{portal_url}' => route('claim.request.show', ['id' => md5($claim->tc_id)]),
                        '{expense_summary}' => isset($expenseSummary)? $expenseSummary : '',
                    ];

                    // The recipient's email address
                    $recipientEmail = $nextApproverDetails->fh_employee->emp_email;
                    // The email template type and business ID
                    $templateType = 405; // Replace with your mail template type
                    $businessId = $user->emp_b_id; // Replace with your business ID if applicable
                    // Sending the email using the CentralLogics class
                    $sent = CentralLogics::sendCustomEmail($templateType, $placeholders, $recipientEmail, $businessId);

                    // $mailData = [
                    //     'subject' => 'Travel and Expense Claim Approval Request',
                    //     'url' => route('claim.request.show', ['id' => $claim->tc_id]),
                    //     'mail_type' => 'CLAIM_APPROVAL_REQUEST',
                    //     'data' => ['claimData' => $claim, 'nextApproverName' => $nextApproverDetails->fh_employee->emp_fname],
                    // ];
                    // CentralLogics::send_mail($nextApproverDetails->fh_employee->emp_email, new ApprovalMail($mailData));

                      try {
                        $approverEmp = $nextApproverDetails->fh_employee;
                        $title = 'Claim Approval Pending';
                        $body  = 'Approval required for claim ' . ($claim->tc_unique_id ?? '');
                        $additionalData = [
                            'notification_type' => 'claim_approval',
                            'status' => 'pending',
                            'route' => '/TadaApprovalList',
                            'claim_id' => $claim->tc_id,
                        ];
                        if (!empty($approverEmp->emp_fcm_token)) {
                            $serviceAccountPath = public_path('fixhr-app-firebase.json');
                            FirebaseNotification::sendPushNotification(
                                $title,
                                $body,
                                $approverEmp->emp_fcm_token,
                                $serviceAccountPath,
                                config('credentials')['FIREBASE_MESSAGING_CONFIG'],
                                $additionalData
                            );
                        }
                        NotificationHelper::saveNotification($user->emp_id, $approverEmp->emp_id, $title, $body, $additionalData);
                    } catch (\Throwable $e) {
                        \Log::warning('Claim next-approver notification failed: ' . $e->getMessage());
                    }
                }
            }

            $updateData = array_merge($updateData, $additionUptArr);
            $claim->update($updateData);
        } elseif ($type == 'TRAVEL_REQUEST_APPROVAL') {
        }
    }

    public function addDataToFirebase($requestId, $flag, $empList, $bId, $requesterId)
    {
        $path = 'fixhr/' . $bId . '/' . $flag . '_' . $requestId;
        $this->database->getReference($path)->remove();
        $this->database
            ->getReference($path)
            ->set([
                'flag' => $flag,
                'approvers_list' => $empList,
                'timestamp' => Carbon::now()->valueOf(),
                'requester_id' => $requesterId
            ]);
    }

    public function approve(Request $request)
    {
        // dd($request->all());
        try {
            // Debug: Log the incoming request data
            \Log::info('CommonApprovalController approve method called', [
                'request_data' => $request->data,
                'request_all' => $request->all(),
                'user_id' => $this->user->emp_id ?? 'NULL'
            ]);
            
            $master = $request->data['master_module_id'];
            
            $request->merge(['log_status' => $request->data['approval_action_type']]);
            $request->merge(['log_request_id' => $request->data['request_id']]);
            $request->merge(['log_description' => $request->data['message']]);
            
            if ($master == 199) {
                $request->merge(['reimburse_amount' =>  $request->data['reimburse_amount']]);
                
                // Try to get the ID from either request_id or adl_id (could be MD5 hash)
                $advanceLogId = $request->data['request_id'] ?? $request->data['adl_id'] ?? null;
                
                if (empty($advanceLogId)) {
                    \Log::error('Request ID is null or empty for Advance Log approval', [
                        'request_data' => $request->data,
                        'user_id' => $this->user->emp_id ?? 'NULL'
                    ]);
                    return response()->json([
                        'status' => false,
                        'message' => 'Request ID is missing or invalid'
                    ], 400);
                }
                
                // Handle MD5 hash - find by MD5 of adl_id
                $data = AdvanceLog::where(DB::raw('md5(adl_id)'), $advanceLogId)->first();
                
                if (!$data) {
                    \Log::error('Advance Log not found with ID', [
                        'advance_log_id' => $advanceLogId,
                        'user_id' => $this->user->emp_id ?? 'NULL'
                    ]);
                    return response()->json([
                        'status' => false,
                        'message' => 'Advance Log not found'
                    ], 404);
                }
                
                $request->merge(['adl_trp_id' =>  $data->adl_trp_id]);
                $request->merge(['log_request_id' => $data->adl_id]); // Use actual ID, not MD5 hash
                $prefix = 'adl_';

            } else if ($master == 250) {
                // Try to get the ID from either request_id or lvr_id (could be MD5 hash)
                $leaveRequestId = $request->data['request_id'] ?? $request->data['lvr_id'] ?? null;
                
                if (empty($leaveRequestId)) {
                    \Log::error('Request ID is null or empty for Leave Request approval', [
                        'request_data' => $request->data,
                        'user_id' => $this->user->emp_id ?? 'NULL'
                    ]);
                    return response()->json([
                        'status' => false,
                        'message' => 'Request ID is missing or invalid'
                    ], 400);
                }
                
                 $data = LeaveRequest::withoutGlobalScope('not_sandwich')
                    ->where(DB::raw('md5(lvr_id)'), $leaveRequestId)
                    ->first();
                
                // If not found by MD5, try direct ID lookup
                if (!$data && is_numeric($leaveRequestId)) {
                    $data = LeaveRequest::withoutGlobalScope('not_sandwich')
                        ->where('lvr_id', $leaveRequestId)
                        ->first();
                }
                
                if (!$data) {
                    \Log::error('Leave Request not found with ID', [
                        'leave_request_id' => $leaveRequestId,
                        'user_id' => $this->user->emp_id ?? 'NULL'
                    ]);
                    return response()->json([
                        'status' => false,
                        'message' => 'Leave Request not found'
                    ], 404);
                }
                
                $request->merge(['log_request_id' => $data->lvr_id]); // Use actual ID, not MD5 hash
                $prefix = 'lvr_';
                // Employee-wise approval mapping for Leave
                $mapData = \App\Models\EmployeeApprovalMapping::where([
                    'eam_module_id' => $data->lvr_module_id ?? $request->data['module_id'] ?? 250,
                    'eam_emp_id'    => $data->lvr_emp_id,
                ])->first();
                $statusData = $mapData?->approvalStatuses?->where('eas_approvel_id', $this->user->emp_id)->pluck('eas_approvel_status')->first();
                if ($statusData && isset($request->data['approval_type'])) {
                    $request->merge(['log_status' => ($request->data['approval_type'] == 1 ? $statusData : 170)]);
                }
            } else if($master == 229){
                // Try to get the ID from either request_id or ae_id (could be MD5 hash)
                $attendanceExceptionId = $request->data['request_id'] ?? $request->data['ae_id'] ?? null;
                if (empty($attendanceExceptionId)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Request ID is missing or invalid'
                    ], 400);
                }
                
                // Try to find by MD5 hash first, then by direct ID
                $data = AttendanceException::where(DB::raw('md5(ae_id)'), $attendanceExceptionId)->first();
                
                // If not found by MD5, try direct ID lookup
                if (!$data && is_numeric($attendanceExceptionId)) {
                    $data = AttendanceException::where('ae_id', $attendanceExceptionId)->first();
                }
                
                if (!$data) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Attendance Exception not found'
                    ], 404);
                }
                
                $prefix = 'ae_';
                
                // Update log_request_id with the correct ID for attendance exception
                $request->merge(['log_request_id' => $data->ae_id]); // Use actual ID, not MD5 hash
                
                // Employee-wise approval mapping for Attendance Exception
                $mapData = \App\Models\EmployeeApprovalMapping::where([
                    'eam_module_id' => $request->data['module_id'] ?? 229,
                    'eam_emp_id'    => $data->ae_emp_id,
                ])->first();
                $statusData = $mapData?->approvalStatuses?->where('eas_approvel_id', $this->user->emp_id)->pluck('eas_approvel_status')->first();
                if ($statusData && isset($request->data['approval_type'])) {
                    $request->merge(['log_status' => ($request->data['approval_type'] == 1 ? $statusData : 170)]);
                }
            } else if($master == 339){
                $data = GatePass::where((DB::raw('md5(gtp_id)')),$request->data['gtp_id'])->first();
                $prefix = 'gtp_';
                $request->merge(['log_request_id' => $data['gtp_id']]);
                // Employee-wise approval mapping for GatePass
                $mapData = \App\Models\EmployeeApprovalMapping::where([
                    'eam_module_id' => $data->gtp_module_id,
                    'eam_emp_id'    => $data->gtp_emp_id,
                ])->first();
              
                $statusData = $mapData?->approvalStatuses?->where('eas_approvel_id', $this->user->emp_id)->pluck('eas_approvel_status')->first();
              
                // dd($statusData);
                // When approver clicks approve/reject:
                // - If mapping exists, use mapped status for approve, else 170 for reject
                // - If no mapping, default to 169 for approve, 170 for reject
                if (isset($request->data['approval_type'])) {
                    
                    $approvalType = (int)$request->data['approval_type'];
                    $finalStatus = $statusData ?? ($approvalType === 1 ? 169 : 170);
                    
                    $request->merge(['log_status' => ($approvalType === 1 ? $finalStatus : 170)]);
                }
            } else if($master == 442){
                $data = LoanRequest::where((DB::raw('md5(lnr_id)')),$request->data['lnr_id'])->first();
                $prefix = 'lnr_';
                $request->merge(['log_request_id' => $data['lnr_id']]);
                // Employee-wise approval mapping for Loan
                $mapData = \App\Models\EmployeeApprovalMapping::where([
                    'eam_module_id' => $data->lnr_module_id ?? $request->data['module_id'] ?? 442,
                    'eam_emp_id'    => $data->lnr_emp_id,
                ])->first();
                $statusData = $mapData?->approvalStatuses?->where('eas_approvel_id', $this->user->emp_id)->pluck('eas_approvel_status')->first();
                if ($statusData && isset($request->data['approval_type'])) {
                    $request->merge(['log_status' => ($request->data['approval_type'] == 1 ? $statusData : 170)]);
                }
            } else if($master == 145){
                // Try to get the ID from either request_id or trp_id (could be MD5 hash)
                $tadaRequestPlanId = $request->data['request_id'] ?? $request->data['trp_id'] ?? null;
                
                if (empty($tadaRequestPlanId)) {
                    \Log::error('Request ID is null or empty for Tada Request Plan approval', [
                        'request_data' => $request->data,
                        'user_id' => $this->user->emp_id ?? 'NULL'
                    ]);
                    return response()->json([
                        'status' => false,
                        'message' => 'Request ID is missing or invalid'
                    ], 400);
                }
                
                // Handle MD5 hash - find by MD5 of trp_id
                $data = TadaRequestPlan::where(DB::raw('md5(trp_id)'), $tadaRequestPlanId)->first();
                
                  // If not found by MD5, try direct ID lookup
                if (!$data && is_numeric($tadaRequestPlanId)) {
                    $data = TadaRequestPlan::where('trp_id', $tadaRequestPlanId)->first();
                }
                
                if (!$data) {
                    \Log::error('Tada Request Plan not found with ID', [
                        'tada_request_plan_id' => $tadaRequestPlanId,
                        'user_id' => $this->user->emp_id ?? 'NULL'
                    ]);
                    return response()->json([
                        'status' => false,
                        'message' => 'Tada Request Plan not found'
                    ], 404);
                }
                
                $request->merge(['log_request_id' => $data->trp_id]); // Use actual ID, not MD5 hash
                $prefix = 'trp_';
                $request->merge(['trp_module_id' => $data->trp_module_id]);
                // Employee-wise approval mapping for Travel Request Plan
                $mapData = \App\Models\EmployeeApprovalMapping::where([
                    'eam_module_id' => $data->trp_module_id ?? $request->data['module_id'] ?? 145,
                    'eam_emp_id'    => $data->trp_emp_id,
                ])->first();
                $statusData = $mapData?->approvalStatuses?->where('eas_approvel_id', $this->user->emp_id)->pluck('eas_approvel_status')->first();
                if ($statusData && isset($request->data['approval_type'])) {
                    $request->merge(['log_status' => ($request->data['approval_type'] == 1 ? $statusData : 170)]);
                }
            } else if($master == 146){
                if(isset($request->data['deduction_info'])) {
                    $request->merge(['deduction_info' =>  $request->data['deduction_info']]);
                    $request->merge(['deduction_amount' =>  $request->data['deduction_amount']]);
                }
                
                // Try to get the ID from either request_id or tc_id (could be MD5 hash)
                $tadaClaimId = $request->data['request_id'] ?? $request->data['tc_id'] ?? null;
                
                if (empty($tadaClaimId)) {
                    \Log::error('Request ID is null or empty for Tada Claim approval', [
                        'request_data' => $request->data,
                        'user_id' => $this->user->emp_id ?? 'NULL'
                    ]);
                    return response()->json([
                        'status' => false,
                        'message' => 'Request ID is missing or invalid'
                    ], 400);
                }
                
                // Handle MD5 hash - find by MD5 of tc_id
                $data = TadaClaim::where(DB::raw('md5(tc_id)'), $tadaClaimId)->first();
                
                if (!$data) {
                    \Log::error('Tada Claim not found with ID', [
                        'tada_claim_id' => $tadaClaimId,
                        'user_id' => $this->user->emp_id ?? 'NULL'
                    ]);
                    return response()->json([
                        'status' => false,
                        'message' => 'Tada Claim not found'
                    ], 404);
                }
                
                $request->merge(['log_request_id' => $data->tc_id]); // Use actual ID, not MD5 hash
                $prefix = 'tc_';
            } else if ($master == 589) {
                $outdoorId = $request->data['request_id'] ?? $request->data['atd_od_id'] ?? null;
                if (empty($outdoorId)) {
                    return response()->json(['status' => false, 'message' => 'Request ID is missing'], 400);
                }

                $data = AttendanceOutDoor::where(DB::raw('md5(atd_od_id)'), $outdoorId)->first();
                if (!$data && is_numeric($outdoorId)) {
                    $data = AttendanceOutDoor::where('atd_od_id', $outdoorId)->first();
                }

                if (!$data) {
                    return response()->json(['status' => false, 'message' => 'Outdoor Attendance not found'], 404);
                }

                $request->merge(['log_request_id' => $data->atd_od_id]);
                $prefix = 'atd_od_';

                // Employee-wise approval mapping
                $mapData = EmployeeApprovalMapping::where([
                    'eam_module_id' => $data->atd_od_module_id ?? $request->data['module_id'] ?? 589,
                    'eam_emp_id'    => $data->atd_od_emp_id,
                ])->first();
                $statusData = $mapData?->approvalStatuses
                    ?->where('eas_approvel_id', $this->user->emp_id)
                    ->pluck('eas_approvel_status')->first();
                if ($statusData && isset($request->data['approval_type'])) {
                    $request->merge(['log_status' => ($request->data['approval_type'] == 1 ? $statusData : 170)]);
                }
            }

            $response = ApprovalHelper::processApproval($request, $data, $this->user, $prefix);
            $message =  $response['status'] === 'success' ? 'processed successfully' : 'not processed';
            // Employee-wise notifications (all modules)
            if ($response['status'] === 'success' && in_array($master, [339, 250, 145, 229, 442, 199, 146, 589])) {
                try {
                 
                    // Determine module-specific requester and labels
                    $moduleId = $master;
                    $requestId = $data->{$prefix . 'id'};
                    $requesterEmpId = null;
                    $notifyMeta = [
                        339 => ['pendingType' => 'gatepass_approval', 'finalType' => 'gatepass_status', 'route' => '/GetPassApprovalList', 'titleBase' => 'Gate Pass','routeUser' => '/GatePassList'],
                        250 => ['pendingType' => 'leave_approval', 'finalType' => 'leave_status', 'route' => '/LeaveApprovalList', 'titleBase' => 'Leave','routeUser' => '/LeaveApplications'],
                        145 => ['pendingType' => 'travel_approval', 'finalType' => 'travel_status', 'route' => '/TadaApprovalList', 'titleBase' => 'Travel Request','routeUser' => '/TadaApprovalList'],
                        229 => ['pendingType' => 'misspunch_approval', 'finalType' => 'misspunch_status', 'route' => '/MissedPunchApprovalList', 'titleBase' => 'Miss Punch','routeUser' => '/MisPunchList'],
                        442 => ['pendingType' => 'loan_approval', 'finalType' => 'loan_status', 'route' => '/RequestAdvanceListPage', 'titleBase' => 'Loan Request','routeUser' => '/RequestAdvanceListPage'],
                        199 => ['pendingType' => 'advance_approval', 'finalType' => 'advance_status', 'route' => '/RequestAdvanceListPage', 'titleBase' => 'Advance Request','routeUser' => '/RequestAdvanceListPage'],
                        146 => ['pendingType' => 'claim_approval', 'finalType' => 'claim_status', 'route' => '/ClaimApprovalList', 'titleBase' => 'Claim Request','routeUser' => '/ClaimApprovalList'],
                        589 => ['pendingType'=>'outdoor_approval', 'finalType'=>'outdoor_status', 'route'=> '/OutDoorApprovalPage', 'titleBase'=> 'Out Door Attendance', 'routeUser'=>'/OutDoorApprovalPage'],
                    ][$moduleId] ?? null;

                    if (!$notifyMeta) { goto skip_module_notify; }

                    if ($moduleId == 339) { $requesterEmpId = $data->gtp_emp_id; }
                    elseif ($moduleId == 250) { $requesterEmpId = $data->lvr_emp_id; }
                    elseif ($moduleId == 145) { $requesterEmpId = $data->trp_emp_id; }
                    elseif ($moduleId == 229) { $requesterEmpId = $data->ae_emp_id; }
                    elseif ($moduleId == 442) { $requesterEmpId = $data->lnr_emp_id; }
                    elseif ($moduleId == 199) { $requesterEmpId = $data->fh_tada_request_plan->trp_emp_id; }
                    elseif ($moduleId == 146) { $requesterEmpId = $data->tc_emp_id; }
                    elseif ($moduleId == 589) { $requesterEmpId = $data->atd_od_emp_id; }

                    // Refresh data to get latest stage_completed status after processApproval
                    try {
                        $data->refresh();
                    } catch (\Exception $e) {
                        \Log::warning('Failed to refresh data object: ' . $e->getMessage());
                    }
                    
                    // Get employee-wise mapping approvers
                    $approvalMapping = ApprovalHelper::getApprovalMapping($this->user->emp_b_id, $requesterEmpId, $moduleId);
                    $approvalEmpIds = $approvalMapping ? ApprovalHelper::getApprovalArray($approvalMapping) : [];

                    // Normalize IDs to integers for consistent comparison
                    $approvalEmpIds = array_values(array_map('intval', $approvalEmpIds));
                    $alreadyApprovedEmpIds = \App\Models\ApprovalLog::where('log_request_id', $requestId)
                        ->where('log_module_id', $moduleId)
                        ->pluck('log_user_id')
                        ->map(function ($id) { return (int)$id; })
                        ->toArray();
                    $currentApproverId = (int)($this->user->emp_id ?? 0);

                    // Check if all approvers have approved (compare counts)
                    $allApproversApproved = !empty($approvalEmpIds) && count($approvalEmpIds) === count($alreadyApprovedEmpIds);
                    
                    // Check stage_completed status (indicates final approval for most modules)
                    $stageCompletedField = $prefix . 'stage_completed';
                    $isStageCompleted = isset($data->$stageCompletedField) && $data->$stageCompletedField == 1;

                    // Next approver: first in sequence that hasn't already approved and isn't the current approver
                    $nextApproverId = null;
                    foreach ($approvalEmpIds as $approverId) {
                        if ($approverId === $currentApproverId) { continue; }
                        if (!in_array($approverId, $alreadyApprovedEmpIds)) { $nextApproverId = $approverId; break; }
                    }

                    // Debug logging for notification decision
                    \Log::info('Notification decision debug', [
                        'module_id' => $moduleId,
                        'request_id' => $requestId,
                        'log_status' => $request->log_status,
                        'current_approver_id' => $currentApproverId,
                        'approval_emp_ids' => $approvalEmpIds,
                        'already_approved_ids' => $alreadyApprovedEmpIds,
                        'all_approvers_approved' => $allApproversApproved,
                        'next_approver_id' => $nextApproverId,
                        'stage_completed_field' => $stageCompletedField,
                        'is_stage_completed' => $isStageCompleted,
                        'requester_emp_id' => $requesterEmpId,
                    ]);

                    $serviceAccountPath = public_path('fixhr-app-firebase.json');
                    $approvalType = (int)($request->data['approval_type'] ?? 0);

                    // Notify requester if: rejected, no next approver, all approvers approved, or stage completed
                    $shouldNotifyRequester = $request->log_status == 170 || $nextApproverId === null || $allApproversApproved || $isStageCompleted;
                    
                    \Log::info('Notification decision', [
                        'should_notify_requester' => $shouldNotifyRequester,
                        'reason' => [
                            'rejected' => $request->log_status == 170,
                            'no_next_approver' => $nextApproverId === null,
                            'all_approved' => $allApproversApproved,
                            'stage_completed' => $isStageCompleted,
                        ]
                    ]);

                    if ($shouldNotifyRequester) {
                        
                        // Determine if request was approved or rejected
                        // Status 170 = Rejected, otherwise if we're here (final stage), it's approved
                        $isRejected = $request->log_status == 170;
                        $isApproved = !$isRejected;
                        
                        // Final stage or rejected — notify requester
                        $employee = Employee::find($requesterEmpId);
                        if ($employee) {
                            $title = $notifyMeta['titleBase'] . ' ' . ($isApproved ? 'Approved' : 'Rejected');
                            $body  = 'Your ' . strtolower($notifyMeta['titleBase']) . ' has been ' . ($isApproved ? 'approved' : 'rejected') . '.';
                            $additionalData = [
                                'notification_type' => $notifyMeta['finalType'],
                                'status' => ($isApproved ? 'approved' : 'rejected'),
                                'route' => $notifyMeta['routeUser'],
                                'request_id' => $requestId,
                                'remark' => $request->data['message'] ?? '',
                            ];

                                      // Send Email to Requester on Final Approval/Rejection

                            if ($employee && !empty($employee->emp_email)) {
                                $templateType = match ($moduleId) {
                                    250 => $isApproved ? 251 : 252,  // Leave: adjust IDs as per your MailTemplate table
                                    229 => $isApproved ? 231 : 232,  // Miss Punch
                                    145 => $isApproved ? 146 : 147,  // Travel Request
                                    146 => $isApproved ? 148 : 149,  // Claim
                                    199 => $isApproved ? 200 : 201,  // Advance
                                    339 => $isApproved ? 340 : 341,  // Gate Pass
                                    442 => $isApproved ? 443 : 444,  // Loan
                                    default => null,
                                };

                                if (!$templateType) {
                                    \Log::warning('No email template type defined for final notification', [
                                        'module_id' => $moduleId,
                                        'is_approved' => $isApproved
                                    ]);
                                    // Skip email if no template mapped
                                } else {
                                    // Common placeholders (customize per module if needed)
                                    $placeholders = [
                                        '{employee_name}'     => $employee->emp_full_name ?? $employee->emp_name ?? 'Employee',
                                        '{employee_code}'     => $employee->emp_code ?? '-',
                                        '{request_type}'      => $notifyMeta['titleBase'],
                                        '{status}'            => $isApproved ? 'Approved' : 'Rejected',
                                        '{remark}'            => $request->data['message'] ?? 'No remarks provided',
                                        '{approver_name}'     => $this->user->emp_full_name ?? $this->user->emp_name ?? 'Approver',
                                        '{approval_date}'     => Carbon::now()->format('d-m-Y'),
                                    ];

                                    // Module-specific extra placeholders (example overrides/additions)
                                    if ($moduleId == 250 && isset($data->lvr_start_date)) { // Leave
                                        $placeholders['{start_date}'] = Carbon::parse($data->lvr_start_date)->format('d-m-Y');
                                        $placeholders['{end_date}']   = Carbon::parse($data->lvr_end_date)->format('d-m-Y');
                                        $placeholders['{reason}']     = $data->lvr_reason ?? 'N/A';
                                        $placeholders['{leave_type}'] = $data->leaveType?->lt_name ?? 'N/A';
                                    }

                                    if ($moduleId == 229) { // Miss Punch
                                        $placeholders['{punch_date}'] = Carbon::parse($data->ae_date)->format('d-m-Y');
                                        $placeholders['{reason}']     = $data->ae_reason ?? 'N/A';
                                    }

                                    if (in_array($moduleId, [145, 146])) { // Travel / Claim
                                        $placeholders['{travel_period}'] = 'N/A'; // customize if needed
                                        $placeholders['{amount}']        = $data->tc_amount ?? $data->trp_amount ?? 'N/A';
                                    }

                                    if ($moduleId == 199) { // Advance
                                        $placeholders['{amount}'] = $data->adl_amount ?? 'N/A';
                                    }

                                    if ($moduleId == 339) { // Gate Pass
                                        $placeholders['{gatepass_date}'] = Carbon::parse($data->gtp_date)->format('d-m-Y');
                                        $placeholders['{from_time}']     = $data->gtp_from_time ?? 'N/A';
                                        $placeholders['{to_time}']       = $data->gtp_to_time ?? 'N/A';
                                    }

                                    if ($moduleId == 442) { // Loan
                                        $placeholders['{loan_amount}'] = $data->lnr_amount ?? 'N/A';
                                        $placeholders['{installments}'] = $data->lnr_installment ?? 'N/A';
                                    }

                                    try {
                                        $emailSent = CentralLogics::sendCustomEmail(
                                            $templateType,
                                            $placeholders,
                                            $employee->emp_email,
                                            $this->user->emp_b_id ?? $employee->emp_b_id, // use business ID
                                            $attachment = null
                                        );

                                        if ($emailSent) {
                                            \Log::info('Final approval/rejection email sent successfully', [
                                                'module_id'       => $moduleId,
                                                'request_id'      => $requestId,
                                                'requester_email' => $employee->emp_email,
                                                'status'          => $isApproved ? 'Approved' : 'Rejected',
                                                'template_type'   => $templateType,
                                            ]);
                                        } else {
                                            \Log::warning('Failed to send final approval/rejection email', [
                                                'module_id'       => $moduleId,
                                                'requester_email' => $employee->emp_email,
                                                'template_type'   => $templateType,
                                            ]);
                                        }
                                    } catch (\Exception $e) {
                                        \Log::error('Exception while sending final approval email', [
                                            'module_id' => $moduleId,
                                            'error'     => $e->getMessage(),
                                            'trace'     => $e->getTraceAsString(),
                                        ]);
                                    }
                                }
                            }


                            
                            \Log::info('Sending notification to requester', [
                                'requester_emp_id' => $requesterEmpId,
                                'title' => $title,
                                'has_fcm_token' => !empty($employee->emp_fcm_token),
                                'notification_enabled' => $employee->emp_is_notification_enabled,
                            ]);
                            
                            if (!empty($employee->emp_fcm_token) && $employee->emp_is_notification_enabled == '1') {
                                try {
                                    FirebaseNotification::sendPushNotification($title, $body, $employee->emp_fcm_token, $serviceAccountPath, config('credentials')['FIREBASE_MESSAGING_CONFIG'], $additionalData);
                                    \Log::info('Firebase notification sent to requester', ['emp_id' => $requesterEmpId]);
                                } catch (\Exception $e) {
                                    \Log::error('Failed to send Firebase notification to requester', [
                                        'emp_id' => $requesterEmpId,
                                        'error' => $e->getMessage()
                                    ]);
                                }
                            }
                            
                            try {
                                NotificationHelper::saveNotification($this->user->emp_id, $employee->emp_id, $title, $body, $additionalData);
                                \Log::info('In-app notification saved for requester', ['emp_id' => $requesterEmpId]);
                            } catch (\Exception $e) {
                                \Log::error('Failed to save notification for requester', [
                                    'emp_id' => $requesterEmpId,
                                    'error' => $e->getMessage()
                                ]);
                            }
                        } else {
                            \Log::warning('Requester employee not found', ['requester_emp_id' => $requesterEmpId]);
                        }
                    } else {
                        // Notify next approver
                        \Log::info('Notifying next approver', ['next_approver_id' => $nextApproverId]);
                        
                        $approver = Employee::find($nextApproverId);
                        
                        if ($approver) {
                            $title = $notifyMeta['titleBase'] . ' Approval Pending';
                            $body  = 'Approval required.';
                            $additionalData = [
                                'notification_type' => $notifyMeta['pendingType'],
                                'status' => 'pending',
                                'route' => $notifyMeta['route'],
                                'request_id' => $requestId,
                            ];
                            
                            \Log::info('Sending notification to next approver', [
                                'approver_emp_id' => $nextApproverId,
                                'title' => $title,
                                'has_fcm_token' => !empty($approver->emp_fcm_token),
                                'notification_enabled' => $approver->emp_is_notification_enabled,
                            ]);
                            
                            if (!empty($approver->emp_fcm_token) && $approver->emp_is_notification_enabled == '1') {
                                try {
                                    FirebaseNotification::sendPushNotification($title, $body, $approver->emp_fcm_token, $serviceAccountPath, config('credentials')['FIREBASE_MESSAGING_CONFIG'], $additionalData);
                                    \Log::info('Firebase notification sent to next approver', ['emp_id' => $nextApproverId]);
                                } catch (\Exception $e) {
                                    \Log::error('Failed to send Firebase notification to next approver', [
                                        'emp_id' => $nextApproverId,
                                        'error' => $e->getMessage()
                                    ]);
                                }
                            }
                            
                            try {
                                NotificationHelper::saveNotification($this->user->emp_id, $approver->emp_id, $title, $body, $additionalData);
                                \Log::info('In-app notification saved for next approver', ['emp_id' => $nextApproverId]);
                            } catch (\Exception $e) {
                                \Log::error('Failed to save notification for next approver', [
                                    'emp_id' => $nextApproverId,
                                    'error' => $e->getMessage()
                                ]);
                            }
                        } else {
                            \Log::warning('Next approver employee not found', ['next_approver_id' => $nextApproverId]);
                        }
                    }

                    skip_module_notify:;
                } catch (\Throwable $e) {
                    \Log::warning('Employee-wise step notification failed (module ' . $master . '): ' . $e->getMessage());
                }
            }
            return response()->json([
                'status' => $response['status'] === 'success' ?  true : false,
                'message' => 'Approval ' . $message,
                // 'data' => $response,
            ], $response['status'] === 'success' ? 200 : 500,);
        } catch (\Exception $e) {
            return response()->json([
                'status' =>  false,
                'message' => 'Something went wrong.',
                'error' => $e->getMessage(),
            ], 500);  // Internal Server Error
        }
    }
}
