<?php

namespace App\Http\Controllers\Api\Approval;

use App\Helpers\ApprovalHelper;
use App\Http\Controllers\CommonApprovalController;
use ChandraHemant\HtkcUtils\ReturnHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Approval\AdvanceLogRequest;
use App\Http\Resources\Approval\Travel\AdvanceLogApiResource;
use App\Http\Resources\Approval\Travel\TravelPlanApiResource;
use App\Models\AdvanceLog;
use App\Models\Employee;
use App\Models\MasterTable;
use App\Models\ProcessApprover;
use App\Models\RuleCriterion;
use App\Models\TadaRequestPlan;
use ChandraHemant\HtkcUtils\FirebaseNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Helpers\NotificationHelper;

class AdvanceLogApiController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function filterAdvanceLog(Request $request)
    {
        $user = Auth::user();
        $travel_id = $request->travel_id;
        $travel_type = $request->travel_type;

        $data = AdvanceLog::whereHas('fh_tada_request_plan', function ($query) use ($user, $travel_id, $travel_type) {
            $query->where('trp_b_id', $user->emp_b_id)
                ->when($travel_id, function ($q) use ($travel_id) {
                    $q->where('trp_id', $travel_id);
                })
                ->when($travel_type, function ($q) use ($travel_type) {
                    $q->where('trp_pttt_id', $travel_type);
                });
        })
            ->with('fh_tada_request_plan') // Eager load the relationship
            ->orderBy('adl_id', 'desc')
            ->get();
        if ($data->isNotEmpty()) {
            // Group the data by trp_id
            $groupedData = $data->groupBy(function ($item) {
                return $item->fh_tada_request_plan->trp_id;
            });


            $is_adv_approval_pending = false;
            $result = $groupedData->map(function ($group) use ($is_adv_approval_pending) {
                $plan = $group->first()->fh_tada_request_plan;

                $advanceLogCount = $group->count();

                $groupFirst = $group->first();
                $approvalData = ApprovalHelper::getApprovalOrRejectionData(
                    $groupFirst->adl_id,
                    $groupFirst->adl_request_status,
                    $groupFirst->adl_am_id,
                    NULL,
                    199
                );

                if ($approvalData) {
                    $is_adv_approval_pending = true;
                } else {
                    $approval = ApprovalHelper::getApprovalData($groupFirst, $this->user, 'adl_');
                    $is_adv_approval_pending = $approval['canApprove'];
                }
                foreach ($group as $adv) {
                    if (!is_null($adv->adl_reimburse_amount)) {
                        $is_adv_approval_pending = false;
                    }
                }

                return [
                    'plan' => TravelPlanApiResource::collection([$plan])->first(),
                    'advance_logs' => AdvanceLogApiResource::collection($group),
                    'advance_log_count' => $advanceLogCount,
                    'advance_approved_pending' => $is_adv_approval_pending
                ];
            })->values();

            return ReturnHelper::jsonApiReturn($result);
        }

        return response()->json(['result' => [], 'status' => false]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $data = AdvanceLog::where('adl_trp_id', $request->plan_id)
            ->whereHas('fh_tada_request_plan', function ($query) use ($user) {
                $query->where('trp_b_id', $user->emp_b_id)
                    ->whereColumn('trp_id', 'adl_trp_id');
            })->with('fh_tada_request_plan') // Eager load the relationship
            ->orderBy('adl_id', 'desc')
            ->get();

        // if ($data->isNotEmpty()) {
        //     // Group the data by trp_id
        //     $groupedData = $data->groupBy(function ($item) {
        //         return $item->fh_tada_request_plan->trp_id;
        //     });

        //     // Transform the grouped data
        //     $result = $groupedData->map(function ($group) use ($data) {
        //         return [
        //             'plan' => TravelPlanApiResource::collection([$group->first()->fh_tada_request_plan])->first(),
        //             'advance_logs' => AdvanceLogApiResource::collection($group),
        //             'next_approver' =>ApprovalHelper::getNextApprovalDetails($data->last()->adl_module_id, $data->last()->adl_id, $data->last()->adl_b_id)
        //         ];
        //     })->values();

        //     return ReturnHelper::jsonApiReturn($result);
        // }

        if ($data->isNotEmpty()) {
            // Group the data by trp_id
            $groupedData = $data->groupBy(function ($item) {
                return $item->fh_tada_request_plan->trp_id;
            });

            // Get the next approver details once
            $nextApproval = ApprovalHelper::getNextApprovalDetails($data->last()->adl_module_id, $data->last()->adl_id, $data->last()->adl_b_id);
            if (isset($nextApproval['approver_name']) && $nextApproval['approver_name']) {
                // Transform the grouped data
                $result = $groupedData->map(function ($group) use ($data, $nextApproval) {
                    return [
                        'plan' => TravelPlanApiResource::collection([$group->first()->fh_tada_request_plan])->first(),
                        'advance_logs' => AdvanceLogApiResource::collection($group),
                        'next_approver' => $nextApproval // Use the already fetched next approval
                    ];
                })->values();
            } else {

                //    $approverNameResponse =  ApprovalHelper::getNextApprovalDetails($data->last()->adl_module_id, $data->last()->adl_id, $data->last()->fh_tada_request_plan->trp_b_id);

                //     $result = $groupedData->map(function ($group) use ($data, $approverNameResponse) {
                //         return [
                //             'plan' => TravelPlanApiResource::collection([$group->first()->fh_tada_request_plan])->first(),
                //             'advance_logs' => AdvanceLogApiResource::collection($group),
                //             'next_approver' => $approverNameResponse
                //         ];
                //     })->values();

                $result = $groupedData->map(function ($group) {
                    // Default value
                    $nextApprover = ['message' => 'Completed'];

                    foreach ($group as $advance) {
                        // Check for pending status
                         $approvalStatus = $advance->adl_request_status[0]['id'] ?? null;

                        if ($approvalStatus != 157) { // Not "Approved"
                            $approverResponse = ApprovalHelper::getNextApprovalDetails(
                                $advance->adl_module_id,
                                $advance->adl_id,
                                $advance->fh_tada_request_plan->trp_b_id
                            );

                            // If it's a pending approval (data = 1), set the nextApprover
                            if (isset($approverResponse['data']) && $approverResponse['data'] === 1) {
                                $nextApprover = $approverResponse;
                                break;
                            } elseif (!isset($approverResponse['data']) || $approverResponse['data'] !== 1) {
                                // Fallback: even if `data != 1`, still use it unless it says "Completed"
                                $nextApprover = $approverResponse;
                                break;
                            }
                        }
                    }

                    return [
                        'plan' => TravelPlanApiResource::collection([$group->first()->fh_tada_request_plan])->first(),
                        'advance_logs' => AdvanceLogApiResource::collection($group),
                        'next_approver' => $nextApprover,
                    ];
                })->values();
            }

            return ReturnHelper::jsonApiReturn($result);
        }

        return response()->json(['result' => [], 'status' => true, 'message' => 'Data is not available.',]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AdvanceLogRequest $request)
    {
        $user = Auth::user();
        $advanceLogRequest = $request->validated();
        // $amId = null;
        // $ruleCriteria = RuleCriterion::where('rc_b_id', $user->emp_b_id)
        //     ->where('rc_condition_option_id', 140)
        //     ->whereHas('fh_approval_module', function ($query) {
        //         $query->where('am_module_id', 199)->where('am_status', 1);
        //     })->first();

        // if (!isset($ruleCriteria)) {
        //     $approvalMapping = ApprovalHelper::getApprovalMapping($user->emp_b_id, $user->emp_id, 199);
        //     if (!$approvalMapping) {
        //         return response()->json(['result' => [], 'status' => false, 'message' => 'Sorry! not found any approval settings for advance module, contact administration.']);
        //     }
        // } else {
        //     $amId = $ruleCriteria->rc_am_id;
        // }

        // NEW: Try to get employee-wise approval mapping first
       $approvalMapping = \App\Helpers\ApprovalHelper::getApprovalMapping($user->emp_b_id, $user->emp_id, 199);
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
                    $query->where('am_module_id', 199)
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
                return response()->json(['result' => [], 'status' => false, 'message' => 'Sorry! not found any approval settings for advance module, contact administration.']);
            }
 }

        $data = new AdvanceLog();
        $data->adl_trp_id = $advanceLogRequest['plan_id'];
        $data->adl_b_id = $user->emp_b_id;
        $data->adl_requested_amount = $advanceLogRequest['requested_amount'];
        $data->adl_remark = $advanceLogRequest['remark'];
        $data->adl_am_id = $amId;
        $data->adl_request_status = isset($advanceLogRequest['adl_request_status']) ? $advanceLogRequest['adl_request_status'] : '140';

        if ($data->save()) {
            // Fetch all AdvanceLog records related to the same trp_id
            $advanceLogs = AdvanceLog::where('adl_trp_id', $data->adl_trp_id)->get();

            // Group the data by trp_id
            $groupedData = $advanceLogs->groupBy(function ($item) {
                return $item->fh_tada_request_plan->trp_id;
            });

            // Transform the grouped data
            $result = $groupedData->map(function ($group) {
                return [
                    'plan' => TravelPlanApiResource::collection([$group->first()->fh_tada_request_plan])->first(),
                    'advance_logs' => AdvanceLogApiResource::collection($group)
                ];
            })->values();
            $title = 'Advance Request';
            $body = 'A request for travel advance has been submitted by ' . $user->emp_full_name;
            $additionalData = [
                'user_id' => $user->emp_id,
                'notification_type' => 'alert',
                'route' => '/AdvanceApprovalList',
            ];
            $processApprovers = ProcessApprover::where('pa_b_id', $user->emp_b_id)->where('pa_am_id', $amId)->get();

            $serviceAccountPath = public_path('fixhr-app-firebase.json');
                         
            // Notify only first approver (both employee-wise and hierarchy-wise)
            $notifyEmpIds = !empty($approvalEmpIds) ? [reset($approvalEmpIds)] : [];

            foreach ($notifyEmpIds as $approverEmpId) {
                $emp = Employee::find($approverEmpId);
                
                $approver = ApprovalHelper::getApprovalOrRejectionData($data->adl_id, $data->adl_request_status, $amId, $approverEmpId, 199);                
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

            return ReturnHelper::jsonApiReturn($result);
        }
        return response()->json(['result' => [], 'status' => false]);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $user = Auth::user();
        $businessId = $user->emp_b_id;
        $is_web = (isset($request->api) && $request->api == 0);
        $id = $is_web ? base64_decode($id) : $id;
        $advance = AdvanceLog::find($id);

        if (!$advance) {
            if ($is_web) {
                return response()->json(['success' => false]);
            } else {
                return response()->json(['result' => [], 'status' => false, 'message' => 'Record not found.'], 404);
            }
        }

        $is_approver = ProcessApprover::where('pa_emp_id', $user->emp_id)
            ->whereHas('fh_approval_module', function ($query) use ($businessId) {
                $query->where('am_b_id', $businessId)
                    ->where('am_module_id', 199)
                    ->whereColumn('am_id', 'pa_am_id');
            })->first();


        if ($is_approver) {
            $advance->adl_reimburse_amount = $request->reimburse_amount ?? $advance->adl_reimburse_amount;
            $advance->adl_approver_id = $user->emp_id;
            if ($advance->save()) {
                $totalReceivedAmount = AdvanceLog::where('adl_trp_id', $advance->adl_trp_id)
                    ->sum('adl_reimburse_amount');
                TadaRequestPlan::where('trp_id', $advance->adl_trp_id)
                    ->update(['trp_advance_allowance' => $totalReceivedAmount]);
            }
        } else {
            $advance->adl_requested_amount = $request->requested_amount;
            $advance->adl_remark = $request->$request->remark;
            $advance->save();
        }

        if ($advance) {
            // Fetch all AdvanceLog records related to the same trp_id
            $advanceLogs = AdvanceLog::where('adl_trp_id', $advance->adl_trp_id)->get();

            // Group the data by trp_id
            $groupedData = $advanceLogs->groupBy(function ($item) {
                return $item->fh_tada_request_plan->trp_id;
            });


            // Transform the grouped data
            $result = $groupedData->map(function ($group) {
                return [
                    'plan' => TravelPlanApiResource::collection([$group->first()->fh_tada_request_plan])->first(),
                    'advance_logs' => AdvanceLogApiResource::collection($group)
                ];
            })->values();
            if ($is_web) {
                return response()->json(['success' => true]);
            } else {
                return ReturnHelper::jsonApiReturn($result);
            }
        }
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $action = AdvanceLog::find($id);

        if (!$action) {
            return response()->json(['result' => [], 'status' => false, 'message' => 'Record not found'], 404);
        }

        if ($action->delete()) {
            return response()->json(['result' => true, 'status' => true, 'message' => 'Record deleted successfully'], 200);
        }

        return response()->json(['result' => [], 'status' => false, 'message' => 'Failed to delete record'], 500);
    }
}
