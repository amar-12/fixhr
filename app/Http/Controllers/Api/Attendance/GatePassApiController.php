<?php

namespace App\Http\Controllers\Api\Attendance;

use App\Helpers\ApprovalHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Attendance\AttendanceExceptionResource;
use App\Http\Resources\Attendance\GatePassApiResource;
use App\Models\AutomationRule;
use App\Models\Employee;
use App\Models\EmployeeApprovalMapping;
use App\Models\GatepassConfirmation;
use App\Models\GatePass;
use App\Models\ProcessApprover;
use App\Models\RuleCriterion;
use Carbon\Carbon;
use ChandraHemant\HtkcUtils\FirebaseNotification;
use ChandraHemant\HtkcUtils\PaginatedResource;
use ChandraHemant\HtkcUtils\ReturnHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\NotificationHelper;

class GatePassApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $gatePassData = GatePass::where('gtp_emp_id', $user->emp_id)
            ->whereMonth('gtp_date', $currentMonth)
            ->whereYear('gtp_date', $currentYear)->get();

        $gatePassLimit = AutomationRule::where('ar_b_id', $user->emp_b_id)->where('ar_rule_type', 418)->select('ar_occurrences')->first();

        $gatePassCountDetail = [
            'gate_pass_limit' => $gatePassLimit ? $gatePassLimit->ar_occurrences : null,
            'gate_pass_applied' => $gatePassData->count(),
            'gate_pass_remaining' => $gatePassLimit ? ($gatePassLimit->ar_occurrences - $gatePassData->count()) : null
        ];

        if ($gatePassData->isEmpty()) {
            return response()->json(['result' => [[
                'gate_pass_list' => [],
                'gate_pass_count_details' => $gatePassCountDetail
            ]], 'message' => 'No gate-pass application found.', 'status' => false]);
        } else {
            return ReturnHelper::jsonApiReturn([[
                'gate_pass_list' => GatePassApiResource::collection($gatePassData),
                'gate_pass_count_details' => $gatePassCountDetail
            ]]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $user = Auth::user();
        $inTime = Carbon::parse($request->input('in_time'))->format('H:i:s');
        $outTime = Carbon::parse($request->input('out_time'))->format('H:i:s');
        if ($this->hasTimeConflict($user->emp_id, $inTime, $outTime)) {
            return [
                'result' => [],
                'message' => 'The provided time range overlaps with an existing gate pass entry. Please choose a different time.',
                'status' => false
            ];
        }

        // NEW: Try to get employee-wise approval mapping first
        $approvalMapping = \App\Helpers\ApprovalHelper::getApprovalMapping($user->emp_b_id, $user->emp_id, 339);
        $approvalEmpIds = [];
        $amId = null;

        if ($approvalMapping) {
            // Employee-wise mapping exists, get approver emp_ids
            $approvalEmpIds = \App\Helpers\ApprovalHelper::getApprovalArray($approvalMapping);
            // $amId = null; // Store mapping's am_id if necessary (adjust as per actual column)
        } else {
            // Fallback to hierarchy: get RuleCriteria and processApprovers
            $ruleCriteria = RuleCriterion::with('fh_approval_module')
                ->where('rc_b_id', $user->emp_b_id)
                ->where('rc_condition_option_id', 140)
                ->whereHas('fh_approval_module', function ($query) {
                    $query->where('am_module_id', 339)
                        ->where('am_status', 1);
                })->first();
            $processApprovers = [];
            $emp_d_id = $user->emp_d_id;
            if ($ruleCriteria && $ruleCriteria->fh_approval_module) {
                $processApprovers = $ruleCriteria->fh_approval_module
                    ->filteredProcessApprovers($emp_d_id)
                    ->get();
            }

            if (!empty($processApprovers)) {
                $amId = $ruleCriteria->rc_am_id;
                foreach ($processApprovers as $pa) {
                    if ($pa->pa_emp_id) {
                        $approvalEmpIds[] = $pa->pa_emp_id;
                    }
                }
            } else {
                return response()->json(['result' => [], 'status' => false, 'message' => 'Sorry! not found any approval settings for gatepass module, contact administration.']);
            }
        }

        $data = new GatePass();
        $data->gtp_emp_id  = $user->emp_id;
        $data->gtp_b_id  = $user->emp_b_id;
        $data->gtp_date = now()->format('Y-m-d');
        $data->gtp_destination = $request->input('destination');
        $data->gtp_in_time = $inTime;
        $data->gtp_out_time = $outTime;
        $data->gtp_reason = $request->input('reason');
        $data->gtp_status = 140;
        $data->gtp_am_id = $amId;
        $data->gtp_stage_completed = 0;

        if ($data->save()) {
            $dateOnly = $data->created_at->format('d-m-Y'); 
            
                 
            $title = 'Gate Pass Request';
            $body = 'A gate pass request has been submitted by ' . $user->emp_full_name . 
                    ' (From: ' . ($outTime ?? 'N/A') .  // using null coalescing operator is cleaner
                    ', To: ' . ($inTime ?? 'N/A') . ')';

            $additionalData = [
                            'user_id' => $user->emp_id,
                            'notification_type' => 'alert',
                            'route' => '/GetPassApprovalList',
                        ];

            $serviceAccountPath = public_path('fixhr-app-firebase.json');
                
            // Notify only first approver (both employee-wise and hierarchy-wise)
            $notifyEmpIds = !empty($approvalEmpIds) ? [reset($approvalEmpIds)] : [];

            foreach ($notifyEmpIds as $approverEmpId) {
                $emp = Employee::find($approverEmpId);
                $approver = ApprovalHelper::getApprovalOrRejectionData($data->gtp_id, $data->gtp_status, $amId, $approverEmpId, 339);
                
                if ($emp && $emp->emp_is_notification_enabled=='1') {
                   
                        if($emp->emp_fcm_token) {
                            FirebaseNotification::sendPushNotification(
                                $title,
                                $body,
                                $emp->emp_fcm_token,
                                $serviceAccountPath,
                                config('credentials')['FIREBASE_MESSAGING_CONFIG'],
                                $additionalData
                            );
                        }
                        NotificationHelper::saveNotification(
                            $user->emp_id,
                            $approverEmpId,
                            $title,
                            $body,
                            $additionalData
                        );
                    
                }
            }
            return ReturnHelper::jsonApiReturn(GatePassApiResource::collection([$data]));
        }
        return [
            'result' => [],
            'message' => 'Failed to save gate pass.',
            'status' => false
        ];
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $gatePass = GatePass::find($id);
        if ($gatePass) {
            if($gatePass->delete()){
                return [
                    'result' => [],
                    'message' => 'Gate pass deleted successfully.',
                    'status' => true
                ];
            }else{
                return [
                    'result' => [],
                    'message' => 'Failed to delete gate pass.',
                    'status' => false
                ];
            }
        }else{
            return [
                'result' => [],
                'message' => 'Gate pass not found.',
                'status' => false
            ];
        }
    }

    private function hasTimeConflict($empId, $inTime, $outTime)
    {
        $conflict = GatePass::where('gtp_emp_id', $empId)
            ->where('gtp_status', '!=', 170) // Exclude rejected gate passes
            ->whereDate('gtp_date', now()->format('Y-m-d'))
            ->where(function ($query) use ($inTime, $outTime) {
                $query->where(function ($subQuery) use ($inTime) {
                    $subQuery->where('gtp_in_time', '<=', $inTime)
                        ->where('gtp_out_time', '>=', $inTime);
                })->orWhere(function ($subQuery) use ($outTime) {
                    $subQuery->where('gtp_in_time', '<=', $outTime)
                        ->where('gtp_out_time', '>=', $outTime);
                })->orWhere(function ($subQuery) use ($inTime, $outTime) {
                    $subQuery->where('gtp_in_time', '>=', $inTime)
                        ->where('gtp_out_time', '<=', $outTime);
                });
            })
        ->exists();

        return $conflict;
    }

    public function getGatePassApprovalList(Request $request)
    {

        $user = Auth::user();
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);

        $query = GatePass::where('gtp_b_id', $user->emp_b_id)->whereNotIn('gtp_status', [139, 192, 156]);//->whereNotNull('gtp_am_id');

        // $approversList = ProcessApprover::where(['pa_b_id' => $user->emp_b_id, 'pa_am_id' => $query->first()?->gtp_am_id])->pluck('pa_emp_id');

        // $isApprover = $approversList->contains($user->emp_id);
        // if (!$isApprover) {
        //     return [
        //         "result" => [],
        //         "message" => "You do not have the required permissions for gate pass approval.",
        //         "status" => false,
        //     ];
        // }

        if (!is_null($request->input('status'))) {
            $query->where('gtp_status', $request->input('status'));
        }
        if (!is_null($request->input('from_date'))) {
            $query->whereDate('gtp_date', '>=', Carbon::createFromFormat('d M, Y', $request->input('from_date'))->startOfDay());
        }
        if (!is_null($request->input('to_date'))) {
            $query->whereDate('gtp_date', '<=', Carbon::createFromFormat('d M, Y', $request->input('to_date'))->startOfDay());
        }
        if ($request->input('last_15_days')) {
            $query->whereDate('created_at', '>=', Carbon::now()->subDays(15));
        }

        $data = $query->with(['fh_gatepass_confirmation', 'fh_approval_log2'])->orderBy('gtp_id', 'DESC')->paginate($limit, ['*'], 'page', $page);

        if ($query->exists())
            return ReturnHelper::jsonApiReturn(new PaginatedResource($data, GatePassApiResource::class));
        else
            return response()->json(['result' => [], 'message' => 'No gate pass application found', 'status' => false]);
    }
}
