<?php

namespace App\Http\Controllers\Api\Attendance;

use App\Helpers\ApprovalHelper;
use App\Helpers\CentralLogics;
use ChandraHemant\HtkcUtils\CommonUtils;
use App\Http\Controllers\Controller;
use App\Http\Resources\Attendance\LeaveResource;
use App\Http\Resources\LeaveBalanceResource;
use App\Http\Resources\LeaveTypeResource;
use App\Http\Resources\MasterTableResource;
use App\Http\Resources\Policy\PolicyLeaveResource;
use App\Models\ApprovalModule;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\PolicyAttendance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\MasterTable;
use App\Models\PolicyHolidayList;
use App\Models\PolicyLeave;
use App\Models\ProcessApprover;
use App\Models\RuleCriterion;
use App\Notifications\AccountActivated;
use App\Notifications\AppNotification;
use App\Notifications\LeaveRequestNotification;
use ChandraHemant\HtkcUtils\FirebaseNotification;
use ChandraHemant\HtkcUtils\PaginatedResource;
use ChandraHemant\HtkcUtils\ReturnHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use function PHPSTORM_META\type;
use App\Helpers\Aws\AwsHelper;
use App\Helpers\NotificationHelper;
use App\Http\Resources\CompOffResource;
use App\Models\CompOffBalance;

class EmployeeLeaveController extends Controller
{

    protected $awsHelper;

    public function __construct(AwsHelper $awsHelper)
    {
        $this->awsHelper = $awsHelper;
    }

    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        $user = Auth::user();

        // Get month and year from request
        $month = request()->input('month');
        $year = request()->input('year');

        $query = LeaveRequest::where('lvr_emp_id', $user->emp_id)
            ->whereNull('lvr_p_id')
            ->orderByDesc('lvr_id');

        if ($month && $year) {
            // If month and year are provided, filter by that specific month and year
            $query->whereYear('lvr_start_date', $year)
                ->whereMonth('lvr_start_date', $month);
        } else {
            // Default: Fetch leave requests from the current month onwards
            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;

            $query->whereYear('lvr_start_date', '>=', $currentYear)
                ->whereMonth('lvr_start_date', '>=', $currentMonth);
        }

        // Get employee leave data
        $employeeLeaveData = LeaveResource::collection($query->get());

        // Return response based on data availability
        if ($employeeLeaveData->isEmpty()) {
            return response()->json([
                'message' => 'No leave request found.',
                'result' => [],
                'status' => false
            ]);
        } else {
            return response()->json([
                'result' => $employeeLeaveData,
                'status' => true
            ]);
        }
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction(); // Begin transaction
        try {
            $user = Auth::user();
            $leaveReqCat = $request->input('leave_category_id');
            $dayType = $request->input('leave_day_type_id');
            $extraDays = $request->input('extra_days') ? collect($request->input('extra_days')) : collect([]);

            if ($user->emp_pl_id) {
                $leave_policy = PolicyLeave::with(['fh_leave_type'])->where('pl_id', $user->emp_pl_id)->first();
                if (!$leave_policy) {
                    return response()->json(['result' => [], 'status' => false, 'message' => 'Sorry! It seems the leave policy could not be found.']);
                }
            } else {
                return response()->json(['result' => [], 'status' => false, 'message' => 'We apologize for the inconvenience, but it seems that the leave policy has not been assigned.']);
            }

            $ruleCriteria = RuleCriterion::with('fh_approval_module')
                ->where('rc_b_id', $user->emp_b_id)
                ->where('rc_condition_option_id', 140)
                ->whereHas('fh_approval_module', function ($query) {
                    $query->where('am_module_id', 250)
                        ->where('am_status', 1);
                })->first();

            $processApprovers = [];
            $emp_d_id = $user->emp_d_id;
            $amId = null;

            // Ensure $ruleCriteria exists before accessing the relationship
            if ($ruleCriteria && $ruleCriteria->fh_approval_module) {
                // Fetch filtered process approvers using emp_d_id
                $processApprovers = $ruleCriteria->fh_approval_module
                    ->filteredProcessApprovers($emp_d_id)
                    ->get(); // Fetch the filtered data
            }
            // dd($ruleCriteria->fh_approval_module);
            if (empty($processApprovers)) {
                $approvalMapping = ApprovalHelper::getApprovalMapping($user->emp_b_id, $user->emp_id, 250);
                if (!$approvalMapping) {
                    return response()->json(['result' => [], 'status' => false, 'message' => 'Sorry! not found any approval settings for leave module, contact administration.']);
                }
            } else {
                $amId = $ruleCriteria->rc_am_id;
            }

            // Check Employee probation for leave
            $probActive = Employee::where('emp_id', $user->emp_id)->where('emp_allow_probation_leave', 1)->first();
            if ($probActive === null) {
                return response()->json(['result' => [], 'status' => false, 'message' => 'Sorry! you can not take leave in probation period.']);
            }

            // Check if joining leave is allowed
            // $isJoiningLeave = Employee::where('emp_id', $user->emp_id)->where('emp_allow_joining_leave', 0)->first();
            // if($isJoiningLeave) {
            //     return response()->json(['result' => [], 'status' => false, 'message' => 'Sorry! you can not take leave.']);
            // }

            $leaveRequest = new LeaveRequest();
            $start_date = null;
            if ($request->input('leave_start_date')) {
                try {
                    $start_date = Carbon::createFromFormat('d M, Y', $request->input('leave_start_date'))->format('Y-m-d');
                } catch (\Exception $e) {
                    $start_date = null;
                }
            }

            $end_date = null;
            if ($request->input('leave_end_date')) {
                try {
                    $end_date = Carbon::createFromFormat('d M, Y', $request->input('leave_end_date'))->format('Y-m-d');
                } catch (\Exception $e) {
                    $end_date = null;
                }
            }

            $leave_day_segment = null;
            $checkSegment = false;

            if ($request->input('leave_day_type_id') == 202) {
                $leave_day_segment = $request->input('leave_day_segment_id');

                // Check if any leave exists already on that start_date for the user
                $startDateExists = LeaveRequest::where('lvr_emp_id', $user->emp_id)
                    ->where('lvr_start_date', $start_date)->where('lvr_leave_day_type_id', 201)
                    ->exists();

                // Only check segment if the start date is NOT already taken
                $checkSegment = !$startDateExists;
            }

            if (is_null($end_date)) {
                $end_date = $start_date;
            }

            if ($this->checkForOverlap($start_date, $end_date, $user->emp_id, $checkSegment ? $leave_day_segment : null)) {
                return [
                    'result' => [],
                    'message' => 'There is already a leave request for the specified dates.',
                    'status' => false
                ];
            }

            $uploadedPhotos = [];
            if (env('STORE_ON_S3')) { //upload file to AWS S3 storage.
                $bucket = 'fixhr-uploads';
                if ($request->lvr_documents != '' && $request->lvr_documents != NULL && $request->lvr_documents != []) {
                    foreach ($request->lvr_documents as $file) {
                        $imageUniqueName = $file->getClientOriginalName();
                        $imagePath = 'LeaveDocument/' . $user->fh_business->b_unique_id . '/' . time() . $imageUniqueName;
                        $uploadResult = $this->awsHelper->uploadFileToS3($bucket, $imagePath, $file);
                        if ($uploadResult['status']) {
                            $uploadedPhotos[] = $uploadResult['ObjectURL'];
                        }
                    }
                }
            } else { //upload file to the server directory
                $uploadedPath = CommonUtils::uploadFiles($request, 'leave_document', 'LeaveDocument', ['prefix' => 'leave', 'isApi' => true]);
                if (!empty($uploadedPath)) {
                    foreach ($uploadedPath as $path) {
                        $uploadedPhotos[] = url($path);
                    }
                }
            }



            if ($dayType == 202) {
                $leaveRequest->lvr_total_leave_days = 0.5;
            } else {
                $leaveRequest->lvr_total_leave_days = Carbon::parse($start_date)->diffInDays(Carbon::parse($end_date)) + 1;
            }

            $leaveRequest->lvr_b_id = $user->fh_business->b_id;
            $leaveRequest->lvr_emp_id = $user->emp_id;
            $leaveRequest->lvr_pl_id = $leave_policy->pl_id;
            $leaveRequest->lvr_leave_day_type_id = $dayType;
            $leaveRequest->lvr_day_segment_id = $request->input('leave_day_segment_id');
            $leaveRequest->lvr_reason = $request->input('reason');
            $leaveRequest->lvr_documents = json_encode($uploadedPhotos);
            $leaveRequest->lvr_am_id = $amId;
            $leaveRequest->lvr_is_comp_off = $leaveReqCat == 564 ? 1 : 0;

            $leaveWithoutPayData = new LeaveRequest();

            // Checking Leave Balance before applying leave
            if ($leaveReqCat != 215) {
                $leaveBalance = LeaveBalance::where(['lb_emp_id'=>$user->emp_id, 'lb_b_id'=>$user->fh_business->b_id,'lb_cat_type_id'=>$leaveReqCat])
                    ->orderBy('lb_id', 'desc')
                    ->first();

                $compOffBalance = CompOffBalance::where('cb_emp_id', $user->emp_id)->where('cb_b_id', $user->fh_business->b_id)
                    ->where('cb_year', now()->year)
                    ->where('cb_month', now()->month)
                    ->first();

                if (($leaveReqCat != 564 && $leaveBalance->lb_balance_remaining_leave < $leaveRequest->lvr_total_leave_days) || ($leaveReqCat == 564 && $compOffBalance->cb_balance_remaining < $leaveRequest->lvr_total_leave_days)) {
                    $availableLeave = $leaveReqCat == 564 ? $compOffBalance->cb_balance_remaining : $leaveBalance->lb_balance_remaining_leave;
                    $leaveWithoutPay = $leaveRequest->lvr_total_leave_days - $availableLeave;

                    if ($availableLeave == 0.5) {
                        $firstEndDate = $start_date;
                        $secondStartDate = $start_date;
                    } else {
                        $firstEndDate = Carbon::parse($start_date)->addDays($availableLeave - 1)->format('Y-m-d');
                        $secondStartDate = Carbon::parse($firstEndDate)->addDays(1)->format('Y-m-d');
                    }

                    $leaveWithoutPayData = $leaveRequest->replicate();

                    $leaveRequest->lvr_start_date = $start_date;
                    $leaveRequest->lvr_end_date = $firstEndDate;
                    $leaveRequest->lvr_cat_type_id = $leaveReqCat;
                    $leaveRequest->lvr_total_leave_days = $availableLeave;

                    $leaveWithoutPayData->lvr_start_date = $secondStartDate;
                    $leaveWithoutPayData->lvr_end_date = $end_date;
                    $leaveWithoutPayData->lvr_cat_type_id = 215;
                    $leaveWithoutPayData->lvr_total_leave_days = $leaveWithoutPay;
                } else { // this section is for when leave balance is available
                    $leaveRequest->lvr_start_date = $start_date;
                    $leaveRequest->lvr_end_date = $end_date;
                    $leaveRequest->lvr_cat_type_id = $leaveReqCat;
                }
            } else {
                $leaveRequest->lvr_start_date = $start_date;
                $leaveRequest->lvr_end_date = $end_date ?? $start_date;
                $leaveRequest->lvr_cat_type_id = $leaveReqCat;
            }

            if ($leaveRequest->save()) {
                $currLvrId = $leaveRequest->lvr_id;
                if ($leaveWithoutPayData->lvr_total_leave_days > 0) {
                    $leaveWithoutPayData->lvr_p_id = $currLvrId;
                    if (!($leaveWithoutPayData->save())) {
                        $leaveRequest->delete();
                        return [
                            'result' => [],
                            'message' => 'Failed to apply leave.',
                            'status' => false
                        ];
                    }
                }

                if ($leaveReqCat != 215) {
                    $leaveReqCount = $leaveRequest->lvr_total_leave_days ?? 0;

                    if ($leave_policy && $leave_policy->fh_leave_type->isNotEmpty()) { //lvt_cat_type_id
                        $leave_type = $leave_policy->fh_leave_type->where('lvt_cat_type_id', $leaveReqCat)->first();
                    }

                    if ($leaveReqCat == 564) {
                        $compOffBalance = CompOffBalance::where('cb_emp_id', $user->emp_id)->where('cb_b_id', $user->fh_business->b_id)
                            ->where('cb_year', now()->year)
                            ->where('cb_month', now()->month)
                        ->first();
                        $lastTakenCount = $compOffBalance->cb_taken ?? 0;

                        $update = [
                            'cb_taken' => $lastTakenCount + $leaveReqCount,
                            'cb_balance_remaining' => ($compOffBalance->cb_balance_remaining - $leaveReqCount),
                        ];

                        // Update Comp Off Balance
                        CompOffBalance::where('cb_emp_id', $user->emp_id)->where('cb_b_id', $user->fh_business->b_id)
                            ->where('cb_year', now()->year)
                            ->where('cb_month', now()->month)
                        ->update($update);
                    } else {
                        $leaveBalance = LeaveBalance::where('lb_emp_id', $user->emp_id)
                            ->where('lb_b_id', $user->fh_business->b_id)
                            ->where('lb_cat_type_id', $leaveReqCat)->orderBy('lb_id', 'desc')
                            ->first();

                        $lastTakenCount = $leaveBalance->lb_taken_leave;

                        $update = [
                            'lb_taken_leave' => $lastTakenCount + $leaveReqCount,
                            'lb_balance_remaining_leave' => ($leaveBalance->lb_balance_remaining_leave - $leaveReqCount),
                        ];

                        // Update Leave Balance
                        LeaveBalance::where('lb_emp_id', $user->emp_id)
                            ->where('lb_b_id', $user->fh_business->b_id)
                            ->where('lb_month', date('m'))
                            ->where('lb_year', date('Y'))
                            ->where('lb_cat_type_id', $leaveReqCat)
                        ->update($update);
                    }
                }

                //**************** SANDWICH LEAVE CALCULATION *******************************

                $currentLeaveBalance = LeaveBalance::where('lb_emp_id', $user->emp_id)
                    ->where('lb_cat_type_id', $leaveReqCat)
                    ->first();

                // Get the leave types that match the user's plan and leave type
                $leaveTypes = LeaveType::where('lvt_pl_id', $user->emp_pl_id)
                    ->where('lvt_cat_type_id', $leaveReqCat)
                    ->first();

                // Check if the leave type is a sandwich type
                $isSandwich = $leaveTypes && $leaveTypes->lvt_is_sandwich == 1 ? 1 : 0;
                // dd($user->emp_pl_id, $user->emp_id, $leaveTypes, $currentLeaveBalance);

                if ($currentLeaveBalance && $extraDays->isNotEmpty() && $currentLeaveBalance->lb_balance_remaining_leave >= $extraDays->count() && $isSandwich == 1) {
                    // Deduct from current leave category
                    $currentLeaveBalance->lb_balance_remaining_leave -= $extraDays->count();
                    $currentLeaveBalance->lb_taken_leave += $extraDays->count();
                    if (!($currentLeaveBalance->save())) {
                        $leaveRequest->delete();
                        return [
                            'result' => [],
                            'message' => 'Failed to apply leave.',
                            'status' => false
                        ];
                    } else {
                        $this->handleSandwichLeave($leaveRequest, $leaveReqCat, $extraDays, $user, $currLvrId, $currentLeaveBalance);
                    }
                    // dd('if :',$currentLeaveBalance->lb_balance_remaining_leave);
                } else {
                    // If current leave category balance is insufficient, check the last leave taken
                    $lastLeave = LeaveRequest::where('lvr_emp_id', $user->emp_id)->orderBy('lvr_end_date', 'desc')->skip(1)->first();
                    // dd($lastLeave, $previousLeaves);

                    if ($lastLeave) {
                        $lastLeaveCategoryId = $lastLeave->lvr_cat_type_id;
                        $lastLeaveBalance = LeaveBalance::where('lb_emp_id', $user->emp_id)->where('lb_cat_type_id', $lastLeaveCategoryId)->first();
                        $leaveTypes = LeaveType::where('lvt_pl_id', $user->emp_pl_id)->where('lvt_cat_type_id', $lastLeaveCategoryId)->first();

                        $isSandwich = $leaveTypes && $leaveTypes->lvt_is_sandwich == 1 ? 1 : 0;
                        // dd($isSandwich, $lastLeaveCategoryId, $lastLeaveBalance->lb_balance_remaining_leave, $extraDays->count());

                        if ($lastLeaveBalance && $extraDays->isNotEmpty() && $lastLeaveBalance->lb_balance_remaining_leave >= $extraDays->count() && $isSandwich == 1) {
                            // Deduct from last leave category
                            $lastLeaveBalance->lb_balance_remaining_leave -= $extraDays->count();
                            $lastLeaveBalance->lb_taken_leave += $extraDays->count();
                            if (!($lastLeaveBalance->save())) {
                                $leaveRequest->delete();
                                return [
                                    'result' => [],
                                    'message' => 'Failed to apply leave.',
                                    'status' => false
                                ];
                            } else {
                                $this->handleSandwichLeave($leaveRequest, $lastLeaveCategoryId, $extraDays, $user, $currLvrId, $lastLeaveBalance);
                            }
                            // dd('else - if :',$currentLeaveBalance->lb_balance_remaining_leave);
                        } else if ($extraDays->isNotEmpty()) {
                            // Mark extra days as UPL
                            $this->handleSandwichLeave($leaveRequest, 215, $extraDays, $user, $currLvrId, null);
                        }
                    } else if ($extraDays->isNotEmpty()) {
                        // Mark extra days as UPL
                        $this->handleSandwichLeave($leaveRequest, 215, $extraDays, $user, $currLvrId, null);
                    }
                }
                $serviceAccountPath = public_path('fixhr-app-firebase.json');
                foreach ($processApprovers as $pa) {
                    $emp = Employee::where('emp_id', $pa->pa_emp_id)->first();
                    $placeholders = [
                        '{start_date}' => ($leaveRequest->lvr_start_date)->format('d-m-Y'),
                        '{end_date}' => $leaveWithoutPayData->lvr_end_date
                            ? ($leaveWithoutPayData->lvr_end_date)->format('d-m-Y')
                            : ($leaveRequest->lvr_end_date)->format('d-m-Y'),
                        '{reason}' => $leaveRequest->lvr_reason,
                        '{receiver_name}' => optional($pa->fh_employee)->emp_full_name ?? 'N/A',
                        '{approver_name}' => optional($pa->fh_employee)->emp_full_name ?? 'N/A',
                        '{emp_full_name}' => optional($leaveRequest->fh_employee)->emp_full_name,
                        '{employee_name}' => optional($leaveRequest->fh_employee)->emp_full_name,
                        '{emp_code}' => optional($leaveRequest->fh_employee)->emp_code,
                        '{employee_code}' => optional($leaveRequest->fh_employee)->emp_code,
                        '{emp_position}' => optional($leaveRequest->fh_employee)->fh_designation->dg_name,
                        '{employee_position}' => optional($leaveRequest->fh_employee)->fh_designation->dg_name,
                        '{emp_phone}' => optional($leaveRequest->fh_employee)->emp_phone,
                        '{employee_phone}' => optional($leaveRequest->fh_employee)->emp_phone,
                        '{leave_category}' => ($leaveWithoutPayData && $leaveWithoutPayData->fh_leave_cat_type)
                            ? $leaveRequest->fh_leave_cat_type->m_name . ' & ' . $leaveWithoutPayData->fh_leave_cat_type->m_name
                            : $leaveRequest->fh_leave_cat_type->m_name ?? 'N/A',
                        '{leave_type}' =>  optional($leaveRequest->fh_leave_day_type)->m_name ?? 'N/A',
                        '{applied_date}' => $leaveRequest->created_at ? $leaveRequest->created_at->format('d-m-Y H:i:s') : 'N/A',
                        '{day_segment}' => optional($leaveRequest->fh_leave_day_segment)->m_name ?  '(' . optional($leaveRequest->fh_leave_day_segment)->m_name . ')' :  '',
                        '{days}' => $leaveWithoutPayData->lvr_total_leave_days
                            ? ($leaveRequest->lvr_total_leave_days + $leaveWithoutPayData->lvr_total_leave_days)
                            : $leaveRequest->lvr_total_leave_days ?? 'N/A',
                    ];

                    // The recipient's email address
                    $recipientEmail = $pa->fh_employee->emp_email; //'virendran818@gmail.com';// optional($pa->fh_employee)->emp_email ?? 'N/a';
                    // The email template type and business ID
                    $templateType = 407; // Replace with your mail template type
                    $businessId = $user->emp_b_id; // Replace with your business ID if applicable

                    // Sending the email using the CentralLogics class
                    $sent = CentralLogics::sendCustomEmail($templateType, $placeholders, $recipientEmail, $businessId);
                    $approver = ApprovalHelper::getApprovalOrRejectionData($leaveRequest->lvr_id, $leaveRequest->lvr_status, $amId, $pa->pa_emp_id, 250);
                             $title = 'New Leave Request';
                             $body = 'A new leave request has been submitted by ' . $user->emp_full_name .
                                 ' (From: ' . ($leaveRequest->lvr_start_date ? $leaveRequest->lvr_start_date->format('d-m-Y') : 'N/A') .
                                 ', To: ' . ($leaveRequest->lvr_end_date ? $leaveRequest->lvr_end_date->format('d-m-Y') : 'N/A') . ')';
                            $additionalData = [
                                'user_id' => $user->emp_id,
                                'notification_type' => 'alert',
                                'route' => '/LeaveApprovalList',
                            ];


                    // Send the push notification
                    // if ($approver) {
                    //     if($emp->emp_fcm_token)
                    //    { $response = FirebaseNotification::sendPushNotification(
                    //         $title,
                    //         $body,
                    //         $emp->emp_fcm_token,
                    //         $serviceAccountPath,
                    //         config('credentials')['FIREBASE_MESSAGING_CONFIG'],
                    //         $additionalData
                    //     );
                    //    }
                    //     NotificationHelper::saveNotification(
                    //     $user->emp_id,
                    //     $pa->pa_emp_id,
                    //     $title,
                    //     $body,
                    //     $additionalData
                    // );

                    // }

                }

                DB::commit(); // Commit transaction if all is successful
                return ReturnHelper::jsonApiReturn(LeaveResource::collection([LeaveRequest::find($leaveRequest->lvr_id)]));
            } else {
                DB::commit(); // Commit transaction if all is successful
                return response()->json(['result' => [], 'status' => false]);
            }
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback transaction if an error occurs
            return [
                'result' => [],
                'message' => $e->getMessage(),
                'status' => false
            ];
        }
    }

    private function handleSandwichLeave($leaveRequest, $leaveCategoryId, $extraDays, $user, $currLvrId, $leaveBalance)
    {
        $extraStartDay = $extraDays->first();
        $extraEndDay = $extraDays->last();

        $sandwichLeaveData = new LeaveRequest();
        $sandwichLeaveData->lvr_start_date = $extraStartDay;
        $sandwichLeaveData->lvr_end_date = $extraEndDay;
        $sandwichLeaveData->lvr_cat_type_id = $leaveCategoryId;
        $sandwichLeaveData->lvr_total_leave_days = $extraDays->count();
        $sandwichLeaveData->lvr_p_id = $currLvrId;
        $sandwichLeaveData->lvr_emp_id = $user->emp_id;
        $sandwichLeaveData->lvr_pl_id = $user->emp_pl_id;
        $sandwichLeaveData->lvr_is_sandwich = 1;
        $sandwichLeaveData->lvr_leave_day_type_id = 201;

        if (!($sandwichLeaveData->save())) {
            $leaveRequest->delete();

            if ($leaveCategoryId != 215) {
                $leaveBalance->lb_balance_remaining_leave += $extraDays->count();
                $leaveBalance->lb_taken_leave -= $extraDays->count();
                $leaveBalance->save();
            }

            return [
                'result' => [],
                'message' => 'Failed to apply leave.',
                'status' => false
            ];
        }
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
    public function destroy($id)
    {
        $user = Auth::user();
        // Find leave requests with the given ID or parent ID
        $leaveRequests = LeaveRequest::where('lvr_id', $id)
            ->orWhere('lvr_p_id', $id)
            ->get();

        if ($leaveRequests->isEmpty()) {
            return [
                'result' => [],
                'message' => 'Leave request not found.',
                'status' => false
            ];
        }

        $isDeleted = true;

        foreach ($leaveRequests as $leaveRequest) {
            $leaveDeleted = $leaveRequest->delete();
            if (!$leaveDeleted) {
                $isDeleted = false;
            } else {
                if ($leaveRequest->lvr_is_comp_off) {
                    $compOffBalance = CompOffBalance::where('cb_emp_id', $leaveRequest->lvr_emp_id)->where('cb_b_id', $user->fh_business->b_id)
                            ->where('cb_year', now()->year)
                            ->where('cb_month', now()->month)
                            ->first();

                        if ($compOffBalance) {
                            $exist_cb_taken = $compOffBalance->cb_taken - $leaveRequest->lvr_total_leave_days;
                            $exist_cb_balance_remaining = $compOffBalance->cb_balance_remaining + $leaveRequest->lvr_total_leave_days;

                            $compOffBalance->cb_taken = $exist_cb_taken;
                            $compOffBalance->cb_balance_remaining = $exist_cb_balance_remaining;
                            $compOffBalance->save();
                        }
                } else {
                    // Manage leave balance for each deleted leave request
                    $leaveBalance = LeaveBalance::where([
                        ['lb_emp_id', '=', $user->emp_id],
                        ['lb_cat_type_id', '!=', 215],
                        ['lb_cat_type_id', '=', $leaveRequest->lvr_cat_type_id],
                        ['lb_b_id', '=', $user->fh_business->b_id],
                        ['lb_month', '=', now()->month],
                        ['lb_year', '=', now()->year],
                    ])
                        ->first();



                    if ($leaveBalance) {
                        $exist_lb_taken_leave = $leaveBalance->lb_taken_leave - $leaveRequest->lvr_total_leave_days;
                        $exist_lb_balance_remaining_leave = $leaveBalance->lb_balance_remaining_leave + $leaveRequest->lvr_total_leave_days;


                        $leaveBalance->lb_taken_leave = $exist_lb_taken_leave;
                        $leaveBalance->lb_balance_remaining_leave = $exist_lb_balance_remaining_leave;
                        $leaveBalance->save();
                    }
                }
            }
        }

        if ($isDeleted) {
            return [
                'result' => [],
                'message' => 'Leave request deleted successfully, and leave balance updated.',
                'status' => true
            ];
        } else {
            return [
                'result' => [],
                'message' => 'Failed to delete one or more leave requests.',
                'status' => false
            ];
        }
    }


    public function getLeaveData()
    {
        $user = Auth::user();
        // dd($user);
        $dayTypes = MasterTableResource::collection(MasterTable::where('m_group', 'LEAVE_TYPE')->get());
        $daySegment = MasterTableResource::collection(MasterTable::where('m_group', 'LEAVE_DAY_SEGMENT')->get());

        if ($user->emp_gender_id == 34) { // If employee is Female
            $excludeLeaveCatId = 211; //Paternity Leave (PL)
        } elseif ($user->emp_gender_id == 33) { // If employee is Male
            $excludeLeaveCatId = 210; //Maternity Leave (ML)
        }

        $leaveType = LeaveTypeResource::collection(LeaveType::where('lvt_pl_id', $user->emp_pl_id)->whereNot('lvt_cat_type_id', $excludeLeaveCatId)->get());

        $compOffLeaveType = CompOffResource::collection(MasterTable::where('m_id', 564)->get());
        if (!$compOffLeaveType->isEmpty()) {
            $leaveType = $leaveType->merge($compOffLeaveType);
        }

        $master = MasterTableResource::collection(MasterTable::where('m_id', 215)->get());
        $customLeaveType = [
            [
                "lvt_id" => 0,
                "cat_type_id" => $master,
                "leave_balance_details" => []
            ]
        ];

        // Merge the fetched leave types with the custom leave type
        $leaveType = $leaveType->merge($customLeaveType);

        if ($dayTypes->isEmpty() && $leaveType->isEmpty() && $daySegment->isEmpty()) {
            return [
                'leave_day_type' => [],
                'leave_type' => [],
                'leave_day_segment' => [],
                'message' => 'Day type, leave type or day segment not found.',
                'status' => false
            ];
        }

        return [
            'result' => [
                [
                    'leave_day_type' => $dayTypes,
                    'leave_day_segment' => $daySegment,
                    'leave_type' => $leaveType,
                ]
            ],
            'status' => true,
        ];
    }

    public function getLeaveBalance()
    {
        $user = Auth::user();

        if ($user->emp_gender_id == 34) { // If employee is Female
            $excludeLeaveCatId = 211; //Paternity Leave (PL)
        } elseif ($user->emp_gender_id == 33) { // If employee is Male
            $excludeLeaveCatId = 210; //Maternity Leave (ML)
        }

        $leaveBalances = LeaveBalance::where('lb_emp_id', $user->emp_id)
            ->selectRaw('lb_cat_type_id,lb_alloted_leave as total_alloted_leave,lb_taken_leave as total_taken_leave,lb_balance_remaining_leave as total_balance_remaining_leave,lb_carried_forward as total_carried_forward')
            ->whereNot('lb_cat_type_id', $excludeLeaveCatId)
            ->where('lb_year', now()->year)
            ->where('lb_month', now()->month)
            ->get();

        $leaveBalanceData = LeaveBalanceResource::collection($leaveBalances);

        $compOffBalance = CompOffBalance::where('cb_emp_id', $user->emp_id)
            ->selectRaw('562 as lb_cat_type_id,cb_alloted as total_alloted_leave,cb_taken as total_taken_leave,cb_balance_remaining as total_balance_remaining_leave,cb_carried_forward as total_carried_forward')
            ->where('cb_year', now()->year)
            ->where('cb_month', now()->month)
            ->first();

        if (!empty($compOffBalance)) {
            $compOffBalanceData = LeaveBalanceResource::collection([$compOffBalance]);
            $leaveBalanceData = $leaveBalanceData->merge($compOffBalanceData);
        }

        if ($leaveBalanceData->isEmpty()) {
            return [
                'result' => [],
                'message' => 'Leave balance data not found.',
                'status' => false
            ];
        }

        return [
            'result' => $leaveBalanceData,
            'status' => true,
        ];
    }
    // public function holidayType(){
    //     $data= MasterTable::where('m_group','HOLIDAY_TYPE')->get();
    //     return[
    //         'result'=>$data,
    //         'status'=>true,
    //     ];
    // }

    public function checkForOverlap($start_date, $end_date, $user_id, $leave_day_segment = null)
    {
        $overlappingLeave = LeaveRequest::where('lvr_emp_id', $user_id)
            ->when($leave_day_segment, function ($query, $leave_day_segment) {
                return $query->where('lvr_day_segment_id', $leave_day_segment);
            })
            ->where(function ($query) use ($start_date, $end_date) {
                $query->whereBetween('lvr_start_date', [$start_date, $end_date])
                    ->orWhereBetween('lvr_end_date', [$start_date, $end_date])
                    ->orWhere(function ($query) use ($start_date, $end_date) {
                        $query->where('lvr_start_date', '<=', $start_date)
                            ->where('lvr_end_date', '>=', $end_date);
                    });
            })->exists();

        return $overlappingLeave;
    }

    public function getLeaveListForApproval(Request $request)
    {
        $user = Auth::user();
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);

        $query = LeaveRequest::withoutGlobalScope('not_sandwich')->where('lvr_b_id', $user->emp_b_id)->whereNotIn('lvr_status', [139, 192, 156])->where('lvr_p_id', null);//->whereNotNull('lvr_am_id')

        // $approversList = ProcessApprover::where(['pa_b_id' => $user->emp_b_id, 'pa_am_id' => $query->first()?->lvr_am_id])->pluck('pa_emp_id');

        if (!is_null($request->input('status'))) {
            $query->where('lvr_status', $request->input('status'));
        }
        if (!is_null($request->input('from_date'))) {
            $query->whereDate('lvr_start_date', '>=', Carbon::createFromFormat('d M, Y', $request->input('from_date'))->startOfDay());
        }
        if (!is_null($request->input('to_date'))) {
            $query->whereDate('lvr_end_date', '<=', Carbon::createFromFormat('d M, Y', $request->input('to_date'))->startOfDay());
        }
        if (!is_null($request->input('leave_type'))) {
            $query->where('lvr_cat_type_id', $request->input('leave_type'));
        }
        if ($request->input('last_15_days')) {
            $query->whereDate('created_at', '>=', Carbon::now()->subDays(15));
        }
        if ($request->input('search_filter')) {
            $searchFilter = trim($request->input('search_filter'));

            // Check if the input is a date format
            if (preg_match('/^\d{1,2}(?:\/\d{1,2})?(?:\/\d{4})?$/', $searchFilter)) {
                $dateParts = explode('/', $searchFilter);

                if (count($dateParts) == 1) {
                    // Case: Searching by day (e.g., "18" -> all months' 18th day)
                    $query->whereDay('lvr_start_date', $dateParts[0])
                        ->orWhereDay('created_at', $dateParts[0]);
                } elseif (count($dateParts) == 2) {
                    // Case: Searching by day and month (e.g., "18/02" -> 18th Feb of any year)
                    $query->whereMonth('lvr_start_date', $dateParts[1])
                        ->whereDay('lvr_start_date', $dateParts[0])
                        ->orWhereMonth('created_at', $dateParts[1])
                        ->whereDay('created_at', $dateParts[0]);
                } elseif (count($dateParts) == 3) {
                    // Case: Searching by full date (e.g., "18/02/2025")
                    $formattedDate = Carbon::createFromFormat('d/m/Y', $searchFilter)->format('Y-m-d');
                    $query->whereDate('lvr_start_date', $formattedDate)
                        ->orWhereDate('created_at', $formattedDate);
                }
            } else {
                // Case: Searching by category (e.g., "UPL" or "CL")
                $categoryMapping = [
                    'UPL' => 215,
                    'CL' => 207,
                    'SL' => 208,
                    'EL' => 209,
                    'ML' => 210,
                    'PL' => 211,
                    'MRL' => 212,
                    'BL' => 213,
                ];

                if (array_key_exists($searchFilter, $categoryMapping)) {
                    $query->where('lvr_cat_type_id', $categoryMapping[$searchFilter]);
                } else {
                    // Case: Searching by employee name
                    $query->whereHas('fh_employee', function ($q) use ($searchFilter) {
                        $q->whereRaw("REPLACE(emp_full_name, '  ', ' ') LIKE ?", ["%$searchFilter%"])
                            ->orWhereRaw("REPLACE(emp_fname, '  ', ' ') LIKE ?", ["%$searchFilter%"])
                            ->orWhereRaw("REPLACE(emp_lname, '  ', ' ') LIKE ?", ["%$searchFilter%"]);
                    });
                }
            }
        }

        $data = $query->orderBy('lvr_id', 'DESC')->paginate($limit, ['*'], 'page', $page);


        if ($query->exists())
            return ReturnHelper::jsonApiReturn(new PaginatedResource($data, LeaveResource::class));
        else
            return response()->json(['result' => [], 'message' => 'No leave application found.', 'status' => false]);
    }

    public function checkSandwichLeave(Request $request)
    {
        try {
            $request->merge([
                'leave_start_date' => Carbon::createFromFormat('d M, Y', $request->leave_start_date)->format('Y-m-d'),
                'leave_end_date' => Carbon::createFromFormat('d M, Y', $request->leave_end_date)->format('Y-m-d'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid date format. Please use "03 Feb, 2025".',
                'errors' => [
                    'leave_start_date' => ['The leave start date is not in the correct format.'],
                    'leave_end_date' => ['The leave end date is not in the correct format.']
                ]
            ], 422);
        }

        $request->validate([
            'leave_category_id' => 'required|integer',
            'leave_start_date' => 'required|date',
            'leave_end_date' => 'required|date|after_or_equal:start_date',
        ]);

        // dd(gettype($request->leave_start_date));
        $user = Auth::user();
        $currentDate =  now()->format('Y-m');
        [$year, $month] = explode('-', $currentDate);
        // $request['start_date'] = '13-01-2025';
        // $request['end_date'] = '14-01-2025';
        $startDate = Carbon::parse($request->leave_start_date);
        $endDate = Carbon::parse($request->leave_end_date);
        $empId = $user->emp_id;
        $leaveCat = $request->leave_category_id;
        // dd($request->all(), $startDate, $endDate);
        // $leaveTypeC = Cache::get("leave_type_{$user->emp_pl_id}_{$leaveCat}");
        // dd('leaveTypeC',$leaveTypeC);
        // $leaveType = Cache::remember("leave_type_{$user->emp_pl_id}_{$leaveCat}", 120, function () use ($user, $leaveCat) {
        //     return LeaveType::where('lvt_pl_id', $user->emp_pl_id)
        //         ->where('lvt_cat_type_id', $leaveCat)
        //         ->select('lvt_is_sandwich')
        //         ->first();
        // });

        $leaveTypes = LeaveType::where('lvt_pl_id', $user->emp_pl_id)->where('lvt_cat_type_id', $leaveCat)->select('lvt_is_sandwich')->first();
        // dd($leaveTypes);
        if ($leaveTypes == null || $leaveTypes->lvt_is_sandwich == 0) {
            return response()->json([
                'status' => true,
                'message' => 'The selected leave type does not allow sandwich leave.'
            ], 400);
        }
        // dd($leaveTypes);
        // Fetch holidays and week-offs
        $holidays = PolicyHolidayList::where('phl_b_id', 143)->get()
            ->flatMap(function ($holiday) {
                // Create a date range from phl_start_date to phl_end_date
                return Carbon::parse($holiday->phl_start_date)
                    ->toPeriod(Carbon::parse($holiday->phl_end_date))
                    ->toArray();
            })
            ->map(function ($date) {
                return Carbon::parse($date)->toDateString();
            })
            ->unique() // This will remove duplicate values
            ->filter(function ($date) use ($year, $month) {
                $holidayDate = Carbon::parse($date);
                return $holidayDate->year == $year && $holidayDate->month == $month;
            })
            ->values() // This will reset the keys of the collection
            ->toArray();

        $employees = Employee::where('emp_id', $empId)->get();
        foreach ($employees as $key => $val) {
            $weekOfDates = CentralLogics::getWeekOffDates($val, $year, $month);
        }
        // dd($holidays, $weekOfDates);

        //START --- Consecutive Leave Days
        $previousLeaves = LeaveRequest::where('lvr_emp_id', $empId)
            ->where(function ($query) use ($year, $month, $startDate) {
                // Fetch previous leaves from the same month before the new leave start date
                $query->whereYear('lvr_end_date', $year)
                    ->whereMonth('lvr_end_date', $month)
                    ->where('lvr_end_date', '<', $startDate);
            })
            ->orWhere(function ($query) use ($year, $month, $startDate) {
                // Check if leave from the previous month is within 7 days of the new leave
                $prevMonth = $month - 1;
                $prevYear = $year;
                if ($prevMonth == 0) {
                    $prevMonth = 12;
                    $prevYear -= 1;
                }

                $query->whereYear('lvr_end_date', $prevYear)
                    ->whereMonth('lvr_end_date', $prevMonth)
                    ->whereBetween('lvr_end_date', [
                        Carbon::parse($startDate)->subDays(7), // Only consider leaves ending within 7 days before the start
                        Carbon::parse($startDate)->subDay()
                    ]);
            })
            ->orderByDesc('lvr_end_date')
            ->limit(1)
            ->get();

        // dd($previousLeaves);

        // Identify consecutive leave periods
        $tempStartDate = $startDate->copy(); // Temporary start date for adjustments
        $tempEndDate = $endDate->copy(); // Temporary start date for adjustments

        $cnt = 0;
        foreach ($previousLeaves as $leave) {
            if ($cnt == 0) {
                $leaveEndDate = Carbon::parse($leave->lvr_end_date);
                $cnt++;
            }
            $leaveStartDate = Carbon::parse($leave->lvr_start_date);

            // Check if the gap between leaveEndDate and tempStartDate is filled with holidays/week-offs
            $gapDays = collect();
            for ($date = $leaveEndDate->copy()->addDay(); $date->lessThan($tempStartDate); $date->addDay()) {
                // dd($leaveEndDate->copy()->addDay(), $date->lessThan($tempStartDate), $date->addDay());
                $gapDays->push($date->toDateString());
            }

            // Check if all gap days are holidays or week-offs
            $allHolidaysOrWeekOffs = $gapDays->every(function ($date) use ($holidays, $weekOfDates) {
                return in_array(Carbon::parse($date)->toDateString(), $holidays) || in_array(Carbon::parse($date)->toDateString(), $weekOfDates);
            });

            if ($allHolidaysOrWeekOffs) {
                // dd('if ', $allHolidaysOrWeekOffs, $leaveStartDate);
                // Update tempStartDate to the start of the previous leave
                $tempStartDate = $leaveStartDate;
                $leaveEndDate = $tempEndDate;
            } else {
                $leaveStartDate = $tempStartDate;
                $leaveEndDate = $tempEndDate;
                // Break the loop if there are working days in the gap
                break;
            }
        }
        // dd($cnt, $previousLeaves, $startDate, $tempStartDate, $tempEndDate, $leaveStartDate, $leaveEndDate);

        // Calculate total sandwich leave days
        $allLeaveDates = collect();
        for ($date = $tempStartDate->copy(); $date->lte($tempEndDate); $date->addDay()) {
            $allLeaveDates->push($date->toDateString());
        }
        // dd($allLeaveDates);

        // Identify holidays and week-offs within the total leave period
        $extraDays = $allLeaveDates->filter(function ($date) use ($holidays, $weekOfDates) {
            return in_array($date, $holidays) || in_array($date, $weekOfDates);
        })->values();
        // dd($extraDays);

        // Combine leave dates and extra days
        $finalLeaveDates = $allLeaveDates->merge($extraDays)->unique()->values();
        $totalSandwichDays = $finalLeaveDates->count();

        // Assuming leave balances are tracked in `leaveBalances`
        $currentLeaveBalance = LeaveBalance::where('lb_emp_id', $empId)->where('lb_cat_type_id', $leaveCat)->first();

        // Check if the leave type is a sandwich type
        $isSandwich = $leaveTypes && $leaveTypes->lvt_is_sandwich == 1 ? 1 : 0;
        // dd($user->emp_pl_id, $user->emp_id, $leaveTypes, $currentLeaveBalance);
        $deducted_leave_count = '';
        $deducted_leave_category = '';

        $totalLeaveDays = $startDate->diffInDays($endDate) + 1;
        // dd($totalLeaveDays);
        $currLvBalRemaining = $currentLeaveBalance->lb_balance_remaining_leave - $totalLeaveDays;
        if ($currentLeaveBalance && $currLvBalRemaining >= $extraDays->count() && $isSandwich == 1) {
            // Deduct from current leave category
            $deducted_leave_count = $currLvBalRemaining - $extraDays->count();
            $deducted_leave_category = $leaveCat;
            // dd('if :',$currentLeaveBalance->lb_balance_remaining_leave);
        } else {
            // If current leave category balance is insufficient, check the last leave taken
            $lastLeave = LeaveRequest::where('lvr_emp_id', $empId)->orderBy('lvr_end_date', 'desc')->first();
            // dd($lastLeave, $previousLeaves);

            if ($lastLeave && $lastLeave->lvr_cat_type_id != 215) {
                $lastLeaveCategoryId = $lastLeave->lvr_cat_type_id;
                $lastLeaveBalance = LeaveBalance::where('lb_emp_id', $empId)->where('lb_cat_type_id', $lastLeaveCategoryId)->first();
                $leaveTypes = LeaveType::where('lvt_pl_id', $user->emp_pl_id)->where('lvt_cat_type_id', $lastLeaveCategoryId)->select('lvt_is_sandwich')->first();

                $isSandwich = $leaveTypes && $leaveTypes->lvt_is_sandwich == 1 ? 1 : 0;
                // dd($isSandwich, $lastLeaveCategoryId, $lastLeaveBalance->lb_balance_remaining_leave, $extraDays->count());
                $lastLvBalRemaining = $lastLeaveBalance->lb_balance_remaining_leave - $totalLeaveDays;

                if ($lastLeaveBalance && $lastLvBalRemaining >= $extraDays->count() && $isSandwich == 1) {
                    // Deduct from last leave category
                    $deducted_leave_count = $lastLvBalRemaining - $extraDays->count();
                    $deducted_leave_category = $lastLeaveCategoryId;
                    // dd('else - if :',$currentLeaveBalance->lb_balance_remaining_leave);
                } else {
                    // Mark extra days as UPL
                    $deducted_leave_count = $extraDays->count();
                    $deducted_leave_category = 215;
                }
            } else {
                // Mark extra days as UPL
                $deducted_leave_count = $extraDays->count();
                $deducted_leave_category = 215;
            }
        }

        // Warn user about sandwich leave
        if ($totalSandwichDays > $startDate->diffInDays($endDate) + 1) {
            return response()->json([
                'status' => true,
                'result' => [
                    [
                        'message' => "Your leave from {$startDate->format('d M Y')} to {$endDate->format('d M Y')} will form a sandwich leave with included " . $extraDays->count() . " days of weekoff and holiday.",
                        'extra_days' => $extraDays->map(function ($date) {
                            return Carbon::parse($date)->format('d M, Y');
                        }),
                        'extra_days_count' => $extraDays->count(),
                        'deducted_leave_count' => $deducted_leave_count ?? '',
                        'deducted_leave_category' => $deducted_leave_category ?? ''
                    ]
                ]
            ]);
        } else {
            return response()->json([
                'status' => true,
                'result' => []
            ]);
        }
    }

    public function leaveRequestRevert(Request $request)
    {
        $leaveId = $request->id;
        $revertRemark = $request->remark;
    
        if (!$leaveId && !revertRemark) {
            return response()->json([
                'status' => false,
                'message' => 'Leave id and remark is required.',
            ], 200);
        }
    
        // Find the leave request by ID and check if it is in the correct stage
        $leaveRequest = LeaveRequest::where('lvr_id', $leaveId)
                                    ->where('lvr_stage_completed', 1)
                                    ->first();
    
        if ($leaveRequest) {
            $leaveRequest->update(['lvr_status' => 140, 'lvr_stage_completed' => 0, 'is_reverted' => 1, 'revert_remark' => $revertRemark]);
            return response()->json([
                'status' => true,
                'message' => 'Leave reverted successfully.',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Leave not reverted. The request may not be in the correct stage.',
            ], 404);
        }
    }
}
