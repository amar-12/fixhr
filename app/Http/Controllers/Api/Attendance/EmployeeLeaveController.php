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
use App\Models\AttendanceLog;
use App\Models\AttendanceRecord;
use App\Models\CompOffBalance;
use App\Models\PolicyWeekOff;
use Illuminate\Support\Facades\Log;

class EmployeeLeaveController extends Controller
{

    protected $awsHelper;

    public function __construct(AwsHelper $awsHelper)
    {
        $this->awsHelper = $awsHelper;
        // $this->middleware(\App\Http\Middleware\RemoveContentLength::class)
        //  ->only('store');
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

    public function store(Request $request)
    {
        DB::beginTransaction(); // Begin transaction
        try {
            if ($request->emp_id) {
                $user = Employee::where('emp_id', $request->emp_id)->firstOrFail();
            } else {
                $user = Auth::user();
            }
            
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

            // // Ensure $ruleCriteria exists before accessing the relationship
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

            $emp_info = Employee::where('emp_id', $user->emp_id)->select('emp_allow_probation_leave', 'emp_status', 'emp_last_working_date')->first();

            // Check Employee probation for leave
            $probActive = $emp_info->emp_allow_probation_leave == 1;
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
                    return response()->json(['result' => [], 'status' => false, 'message' => 'Invalid start date format.']);
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

            // Check joining date
            if (Carbon::parse($user->emp_date_of_joining)->gte(Carbon::parse($start_date))) {
                return response()->json(['result' => [], 'status' => false, 'message' => 'Cannot apply leave before joining date']);
            }

            // Check end date
            if (Carbon::parse($start_date)->gt(Carbon::parse($end_date))) {
                return response()->json(['result' => [], 'status' => false, 'message' => 'Start Date cannot be greater than End Date.']);
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

            if ($emp_info->emp_status == 72 && $emp_info->emp_last_working_date != null &&
            (Carbon::parse($start_date)->gt(Carbon::parse($emp_info->emp_last_working_date)) || Carbon::parse($end_date)->gt(Carbon::parse($emp_info->emp_last_working_date)
            ))) {
                return response()->json(['result' => [], 'status' => false, 'message' => 'Inactive employee cannot apply for leave.']);
            }

            $atdRecordExists = AttendanceRecord::where('atd_date', $start_date)->where('atd_emp_id', $user->emp_id)->exists();
            $atdLogExists = AttendanceLog::where('al_date', $start_date)->where('al_emp_id', $user->emp_id)->exists();
            if (($atdRecordExists || $atdLogExists) && $leave_day_segment == 201) {
                return response()->json([
                    'result' => [],
                    'status' => false,
                    'message' => sprintf('You cannot apply for leave for this date because attendance has already been recorded.')
                ]);
            }

            // Enforce policy-level apply window limits if enabled.
            // pl_limit_before: maximum days in future the employee can apply before start_date
            // pl_limit_after: maximum days allowed for retro-active applications after start_date
            $approvedById = $request->has('lvr_approved_by') ? $request->lvr_approved_by : null;
            $approvedBy = isset($approvedById) ? Employee::where('emp_id', $approvedById)->first() : null;
            if (isset($leave_policy) && isset($leave_policy->pl_limit_check) && $leave_policy->pl_limit_check && (!$approvedById || ($approvedBy && $approvedBy->emp_role_id != 1))) {
                try {
                    $today = Carbon::now()->startOfDay();
                    $start = Carbon::parse($start_date)->startOfDay();

                    // diffInDays with $absolute=false returns negative when start is in past
                    $diff = $today->diffInDays($start, false);

                    // diffInDays with $absolute=false returns negative when start is in past
                    $diff = $today->diffInDays($start, false);

                    // If start is in the future and exceeds pl_limit_after -> reject
                    if ($diff > 0 && isset($leave_policy->pl_limit_after) && $leave_policy->pl_limit_after !== null && (int) $request->lvr_stage_completed !== 1) {
                        if ($diff > (int) $leave_policy->pl_limit_after) {
                            return response()->json([
                                'result' => [],
                                'status' => false,
                                'message' => sprintf('You cannot apply for this leave more than %d days before the start date.', (int) $leave_policy->pl_limit_after)
                            ]);
                        }
                    }

                    // If start is in the past and exceeds pl_limit_before -> reject (retro applications)
                    if ($diff < 0 && isset($leave_policy->pl_limit_before) && $leave_policy->pl_limit_before !== null && (int) $request->lvr_stage_completed !== 1) {
                        if (abs($diff) > (int) $leave_policy->pl_limit_before) {
                            return response()->json([
                                'result' => [],
                                'status' => false,
                                'message' => sprintf('Retro-active leave applications are allowed up to %d days after the start date.', (int) $leave_policy->pl_limit_before)
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    // If anything goes wrong parsing dates, return an error to the caller
                    return response()->json(['result' => [], 'status' => false, 'message' => 'Invalid leave date provided.']);
                }
            }

            // Check weekly unpaid week-off policy: if any applied date falls on an unpaid week-off day, reject
            try {
                $weekPolicy = $user->fh_week_off_policy;
                if ($weekPolicy && $weekPolicy->pwo_is_unpaid) {
                    $unpaidIds = json_decode($weekPolicy->pwo_is_unpaid, true) ?: [];
                    if (!empty($unpaidIds)) {
                        $dates = [];
                        $periodStart = Carbon::parse($start_date);
                        $periodEnd = Carbon::parse($end_date);
                        for ($date = $periodStart->copy(); $date->lte($periodEnd); $date->addDay()) {
                            $dayName = $date->format('l'); // e.g., Monday
                            $masterDay = MasterTable::where('m_group', 'WEEK_DAY')->where('m_name', $dayName)->first();
                            if ($masterDay && in_array($masterDay->m_id, $unpaidIds)) {
                                $dates[] = $date->format('d-M-Y');
                            }
                        }
                        if (!empty($dates)) {
                            return response()->json([
                                'result' => [],
                                'status' => false,
                                'message' => 'You cannot apply for leave on unpaid week-off day(s): ' . implode(', ', $dates)
                            ]);
                        }
                    }
                }
            } catch (\Exception $e) {
                // If week policy lookup fails, continue silently (do not block apply)
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
                if ($request->leave_document != '' && $request->leave_document != NULL && $request->leave_document != []) {
                    foreach ($request->file('leave_document') as $file) {
                        $imageUniqueName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                        $imagePath = 'LeaveDocument/' . $user->fh_business->b_unique_id . '/' . $imageUniqueName;
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
            $leaveRequest->lvr_is_comp_off = $leaveReqCat == 567 ? 1 : 0;
            $request->has('lvr_approved_by') ? $leaveRequest->lvr_approved_by = $request->lvr_approved_by : null;
            $request->has('lvr_status') ? $leaveRequest->lvr_status = $request->lvr_status : null;
            $request->has('lvr_stage_completed') ? $leaveRequest->lvr_stage_completed = $request->lvr_stage_completed : null;

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

                if (($leaveReqCat != 567 && $leaveBalance->lb_balance_remaining_leave < $leaveRequest->lvr_total_leave_days) || ($leaveReqCat == 567 && $compOffBalance->cb_balance_remaining < $leaveRequest->lvr_total_leave_days)) {
                    // Compute available paid days safely (handle missing balance records)
                    $availableLeave = $leaveReqCat == 567
                        ? ($compOffBalance->cb_balance_remaining ?? 0)
                        : ($leaveBalance->lb_balance_remaining_leave ?? 0);

                    // If policy does not allow Unpaid Leave (UPL) then do not create UPL split - reject the request
                    // pl_upl_applicable is a boolean on PolicyLeave model
                    if (!isset($leave_policy->pl_upl_applicable) || !$leave_policy->pl_upl_applicable) {
                        return response()->json([
                            'result' => [],
                            'status' => false,
                            'message' => 'Insufficient leave balance. You have ' . ($availableLeave) . ' paid day(s) available and Unpaid Leave (UPL) is not allowed for your leave policy.'
                        ]);
                    }
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

                    if ($leaveReqCat == 567) {
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
                // If sunday is unpaid, emp cannot apply leave for sunday if applying on a date range or single

                // $currentLeaveBalance = LeaveBalance::where('lb_emp_id', $user->emp_id)
                // ->where('lb_cat_type_id', $leaveReqCat)
                // ->first();

                $currentLeaveBalance = LeaveBalance::where('lb_emp_id', $user->emp_id)
                            ->where('lb_b_id', $user->fh_business->b_id)
                            ->where('lb_month', date('m'))
                            ->where('lb_year', date('Y'))
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
                    // First create the sandwich leave, then update balance
                    $sandwichResult = $this->handleSandwichLeave($leaveRequest, $leaveReqCat, $extraDays, $user, $currLvrId, $currentLeaveBalance, $amId);
                    if ($sandwichResult === true) {
                        // Deduct from current leave category
                        $currentLeaveBalance->lb_balance_remaining_leave -= $extraDays->count();
                        $currentLeaveBalance->lb_taken_leave += $extraDays->count();
                        $currentLeaveBalance->save();
                    } else {
                        // If sandwich leave creation failed, rollback main leave
                        $leaveRequest->delete();
                        return [
                            'result' => [],
                            'message' => 'Failed to apply sandwich leave.',
                            'status' => false
                        ];
                    }
                } else {
                    // If current leave category balance is insufficient, check the last leave taken
                    $lastLeave = LeaveRequest::where('lvr_emp_id', $user->emp_id)->orderBy('lvr_end_date', 'desc')->skip(1)->first();

                    if ($lastLeave) {
                        $lastLeaveCategoryId = $lastLeave->lvr_cat_type_id;
                        $lastLeaveBalance = LeaveBalance::where('lb_emp_id', $user->emp_id)->where('lb_cat_type_id', $lastLeaveCategoryId)->first();
                        $leaveTypes = LeaveType::where('lvt_pl_id', $user->emp_pl_id)->where('lvt_cat_type_id', $lastLeaveCategoryId)->first();
                        $isSandwich = $leaveTypes && $leaveTypes->lvt_is_sandwich == 1 ? 1 : 0;

                        if ($lastLeaveBalance && $extraDays->isNotEmpty() && $lastLeaveBalance->lb_balance_remaining_leave >= $extraDays->count() && $isSandwich == 1) {
                            // First create the sandwich leave, then update balance
                            $sandwichResult = $this->handleSandwichLeave($leaveRequest, $lastLeaveCategoryId, $extraDays, $user, $currLvrId, $lastLeaveBalance, $amId);

                            if ($sandwichResult === true) {
                                // Now deduct from last leave category
                                $lastLeaveBalance->lb_balance_remaining_leave -= $extraDays->count();
                                $lastLeaveBalance->lb_taken_leave += $extraDays->count();
                                $lastLeaveBalance->save();
                            } else {
                                // If sandwich leave creation failed, rollback main leave
                                $leaveRequest->delete();
                                return [
                                    'result' => [],
                                    'message' => 'Failed to apply sandwich leave.',
                                    'status' => false
                                ];
                            }
                        } else if ($extraDays->isNotEmpty()) {
                            // Mark extra days as UPL
                            $sandwichResult = $this->handleSandwichLeave($leaveRequest, 215, $extraDays, $user, $currLvrId, null, $amId);
                            if ($sandwichResult !== true) {
                                $leaveRequest->delete();
                                return [
                                    'result' => [],
                                    'message' => 'Failed to apply sandwich leave as UPL.',
                                    'status' => false
                                ];
                            }
                        }
                    } else if ($extraDays->isNotEmpty()) {
                        // Mark extra days as UPL
                        $sandwichResult = $this->handleSandwichLeave($leaveRequest, 215, $extraDays, $user, $currLvrId, null, $amId);
                        if ($sandwichResult !== true) {
                            $leaveRequest->delete();
                            return [
                                'result' => [],
                                'message' => 'Failed to apply sandwich leave as UPL.',
                                'status' => false
                            ];
                        }
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
                }

                // Get employee-wise mapping first
                $approvalMapping = ApprovalHelper::getApprovalMapping($user->emp_b_id, $user->emp_id, 250);
                if ($approvalMapping) {
                    $approvalEmpIds = ApprovalHelper::getApprovalArray($approvalMapping);
                    $amId = $approvalMapping->eam_am_id ?? null;
                } else {
                    // Fallback to hierarchy: get RuleCriteria and processApprovers
                    $ruleCriteria = RuleCriterion::with('fh_approval_module')
                        ->where('rc_b_id', $user->emp_b_id)
                        ->where('rc_condition_option_id', 140)
                        ->whereHas('fh_approval_module', function ($query) {
                            $query->where('am_module_id', 250)
                                ->where('am_status', 1);
                        })->first();
                    $processApprovers = [];
                    $emp_d_id = $user->emp_d_id;
                    if ($ruleCriteria && $ruleCriteria->fh_approval_module) {
                        $processApprovers = $ruleCriteria->fh_approval_module->filteredProcessApprovers($emp_d_id)->get();
                    }
                    if (!empty($processApprovers)) {
                        $amId = $ruleCriteria->rc_am_id;
                        foreach ($processApprovers as $pa) {
                            if ($pa->pa_emp_id) {
                                $approvalEmpIds[] = $pa->pa_emp_id;
                            }
                        }
                    }
                }

                if($leaveRequest->lvr_status == 171){
                    $title = 'Leave Auto Approved';
                    $body = 'Your leave has been auto approved (From: ' .
                            ($leaveRequest->lvr_start_date ? $leaveRequest->lvr_start_date->format('d-m-Y') : 'N/A') .
                            ', To: ' .
                            ($leaveRequest->lvr_end_date ? $leaveRequest->lvr_end_date->format('d-m-Y') : 'N/A') .
                            ')';
                    $additionalData = [
                        'user_id' => $user->emp_id,
                        'notification_type' => 'alert',
                        'route' => '/LeaveApprovalList',
                    ];
                    $leaveText = 'A leave request has been auto approved by ';

                    $emp = Employee::find($user->emp_id);
                    if ($emp && $emp->emp_is_notification_enabled == '1') {

                        if($emp->emp_fcm_token){
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
                            $user->emp_id,
                            $title,
                            $body,
                            $additionalData
                        );
                    }
                } else {
                    $leaveText = 'A leave request has been submitted by ';
                }
                
                $title = 'New Leave Request';
                $body = $leaveText . $user->emp_full_name .
                             ' (From: ' . ($leaveRequest->lvr_start_date ? $leaveRequest->lvr_start_date->format('d-m-Y') : 'N/A') .
                             ', To: ' . ($leaveRequest->lvr_end_date ? $leaveRequest->lvr_end_date->format('d-m-Y') : 'N/A') . ')';
                        $additionalData = [
                            'user_id' => $user->emp_id,
                            'notification_type' => 'alert',
                            'route' => '/LeaveApprovalList',
                        ];
 
                // Notification: send to FIRST approver only
                if (!empty($approvalEmpIds)) {
                    $firstApproverEmpId = $approvalEmpIds[0];
                    $emp = Employee::find($firstApproverEmpId);
                    // $approver = ApprovalHelper::getApprovalOrRejectionData($attendanceException->ae_id, $attendanceException->ae_status, $amId, $firstApproverEmpId, 229);

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
                                $firstApproverEmpId,
                                $title,
                                $body,
                                $additionalData
                            );


                        }
                }


            // Leave Request Email Notification
                // if (!empty($approvalEmpIds)) {
                //     $firstApproverEmpId = $approvalEmpIds[0];
                //     $emp = Employee::find($firstApproverEmpId);
                //     $employee_manager_id = $emp->emp_supervisor_id;
                //     $manager_data = Employee::with(['fh_branch', 'fh_department', 'fh_designation'])->select('emp_id', 'emp_full_name', 'emp_code', 'emp_email')->find($employee_manager_id);
                //     if ($manager_data) {
                //         $employeeEmail = $manager_data->emp_email;
                //         $employeeEmail = 'khilesh.fixingdot@gmail.com';
                //         // dd($leaveRequest);
                //         if ($emp && $emp->emp_email) {
                //             // Format leave dates
                //             $startDateFormatted = $leaveRequest->lvr_start_date ? Carbon::parse($leaveRequest->lvr_start_date)->format('d-m-Y') : 'N/A';
                //             $endDateFormatted = $leaveRequest->lvr_end_date ? Carbon::parse($leaveRequest->lvr_end_date)->format('d-m-Y') : 'N/A';

                //             // Prepare placeholders according to your format
                //             $placeholders = [
                //                 '{manager_name}' => $manager_data->emp_full_name,
                //                 '{employee_name}' => $user->emp_full_name,
                //                 '{employee_code}' => $user->emp_code ?? '-',
                //                 '{start_date}'    => $startDateFormatted,
                //                 '{end_date}'      => $endDateFormatted,
                //                 '{reason}'        => $leaveRequest->lvr_reason ?? 'N/A',
                //             ];
                //             // Send email using your existing helper function
                //             $emailSent = CentralLogics::sendCustomEmail(
                //                 $templateType = 250,        // Use your template type ID
                //                 $placeholders,
                //                 $employeeEmail,
                //                 $businessId = $user->emp_b_id,
                //                 $attachment = null           // agar attachment hai to use karo
                //             );
                //             if ($emailSent) {
                //                 Log::info("Leave request email sent to {$emp->emp_email}");
                //             } else {
                //                 Log::warning("Failed to send leave request email to {$emp->emp_email}");
                //             }
                //         }
                //     }
                // }

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

    private function handleSandwichLeave($leaveRequest, $leaveCategoryId, $extraDays, $user, $currLvrId, $leaveBalance, $amId)
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
        $sandwichLeaveData->lvr_am_id = $amId;
        $sandwichLeaveData->lvr_b_id = $user->fh_business->b_id; // Add missing business ID

        if ($sandwichLeaveData->save()) {
            // Update leave balance only if category is not UPL (215) and balance exists
            if ($leaveCategoryId != 215 && $leaveBalance) {
                // The balance was already updated in the calling method, so we don't need to update it again here
                // But if you want to handle it here instead, uncomment the lines below:
                /*
                $leaveBalance->lb_balance_remaining_leave -= $extraDays->count();
                $leaveBalance->lb_taken_leave += $extraDays->count();
                $leaveBalance->save();
                */
            }

            return true; // Return success
        } else {
            // Rollback the leave balance changes if sandwich leave creation fails
            if ($leaveCategoryId != 215 && $leaveBalance) {
                $leaveBalance->lb_balance_remaining_leave += $extraDays->count();
                $leaveBalance->lb_taken_leave -= $extraDays->count();
                $leaveBalance->save();
            }

            $leaveRequest->delete();

            return [
                'result' => [],
                'message' => 'Failed to apply sandwich leave.',
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

        DB::beginTransaction(); // Begin transaction for data consistency

        try {
            // Find the main leave request first
            $mainLeaveRequest = LeaveRequest::find($id);

            if (!$mainLeaveRequest) {
                return [
                    'result' => [],
                    'message' => 'Leave request not found.',
                    'status' => false
                ];
            }

            $allRelatedLeaves = collect();

            // Case 1: If deleting a main leave, find all its children (including sandwich)
            if (is_null($mainLeaveRequest->lvr_p_id)) {
                // This is a main leave request
                $allRelatedLeaves = LeaveRequest::where('lvr_id', $id)
                    ->orWhere('lvr_p_id', $id)
                    ->get();
            }
            // Case 2: If deleting a child leave, find main leave and all siblings
            else {
                // This is a child leave, find the main leave and all related leaves
                $parentId = $mainLeaveRequest->lvr_p_id;
                $allRelatedLeaves = LeaveRequest::where('lvr_id', $parentId)
                    ->orWhere('lvr_p_id', $parentId)
                    ->get();
            }

            // NEW: Find sandwich leaves that might be related to this leave but not linked by parent ID
            $employeeId = $mainLeaveRequest->lvr_emp_id;
            $businessId = $mainLeaveRequest->lvr_b_id;
            $leaveDate = $mainLeaveRequest->lvr_start_date;

            // Look for sandwich leaves that are adjacent to this leave date
            $adjacentSandwichLeaves = LeaveRequest::where('lvr_emp_id', $employeeId)
                ->where('lvr_b_id', $businessId)
                ->where('lvr_is_sandwich', 1)
                ->where(function($query) use ($leaveDate) {
                    $query->whereDate('lvr_start_date', '=', date('Y-m-d', strtotime($leaveDate . ' +1 day')))
                          ->orWhereDate('lvr_start_date', '=', date('Y-m-d', strtotime($leaveDate . ' -1 day')));
                })
                ->get();

            // Merge any found adjacent sandwich leaves
            if (!$adjacentSandwichLeaves->isEmpty()) {
                $allRelatedLeaves = $allRelatedLeaves->merge($adjacentSandwichLeaves)->unique('lvr_id');
            }

            if ($allRelatedLeaves->isEmpty()) {
                return [
                    'result' => [],
                    'message' => 'Leave request not found.',
                    'status' => false
                ];
            }

            // Separate different types of leaves
            $mainLeave = null;
            $childLeaves = collect();
            $sandwichLeaves = collect();

            foreach ($allRelatedLeaves as $leave) {
                if (is_null($leave->lvr_p_id)) {
                    $mainLeave = $leave;
                } elseif ($leave->lvr_is_sandwich == 1) {
                    $sandwichLeaves->push($leave);
                } else {
                    $childLeaves->push($leave);
                }
            }

            $isDeleted = true;
            $totalRestoredDays = 0;
            $leaveCategoriesAffected = collect();

            // Process each leave request for deletion and balance restoration
            foreach ($allRelatedLeaves as $leaveRequest) {

                // Handle balance restoration BEFORE deletion (in case we need leave data)
                if ($leaveRequest->lvr_is_comp_off == 1) {
                    // Handle Comp Off Balance restoration
                    $compOffBalance = CompOffBalance::where('cb_emp_id', $leaveRequest->lvr_emp_id)
                        ->where('cb_b_id', $leaveRequest->lvr_b_id)
                        ->where('cb_year', now()->year)
                        ->where('cb_month', now()->month)
                        ->first();

                    if ($compOffBalance) {
                        $exist_cb_taken = $compOffBalance->cb_taken - $leaveRequest->lvr_total_leave_days;
                        $exist_cb_balance_remaining = $compOffBalance->cb_balance_remaining + $leaveRequest->lvr_total_leave_days;
                        $compOffBalance->cb_taken = max(0, $exist_cb_taken);
                        $compOffBalance->cb_balance_remaining = $exist_cb_balance_remaining;
                        $compOffBalance->save();

                        \Log::info("Comp Off balance restored", [
                            'leave_id' => $leaveRequest->lvr_id,
                            'days_restored' => $leaveRequest->lvr_total_leave_days,
                            'employee' => $leaveRequest->lvr_emp_id
                        ]);
                    }
                } else {
                    // Handle regular leave balance restoration (including sandwich leaves)
                    if ($leaveRequest->lvr_cat_type_id != 215) { // Not UPL (Unpaid Leave)
                        $leaveMonth = date('m', strtotime($leaveRequest->created_at));
                        $leaveYear  = date('Y', strtotime($leaveRequest->created_at));
                        // $leaveBalance = LeaveBalance::where([
                        //     ['lb_emp_id', '=', $leaveRequest->lvr_emp_id],
                        //     ['lb_cat_type_id', '=', $leaveRequest->lvr_cat_type_id],
                        //     ['lb_b_id', '=', $leaveRequest->lvr_b_id],
                        //     ['lb_month', '=', date('m', strtotime($leaveRequest->created_at))],
                        //     ['lb_year', '=', date('Y', strtotime($leaveRequest->created_at))],
                        // ])->first();

                        $leaveBalance = LeaveBalance::where([
                            ['lb_emp_id', '=', $leaveRequest->lvr_emp_id],
                            ['lb_cat_type_id', '=', $leaveRequest->lvr_cat_type_id],
                            ['lb_b_id', '=', $leaveRequest->lvr_b_id],
                            ['lb_month', '=', now()->month],
                            ['lb_year', '=', now()->year],
                        ])->first();

                        if ($leaveBalance) {
                            // $exist_lb_taken_leave = $leaveBalance->lb_taken_leave - $leaveRequest->lvr_total_leave_days;
                            // $exist_lb_balance_remaining_leave = $leaveBalance->lb_balance_remaining_leave + $leaveRequest->lvr_total_leave_days;
                            // $leaveBalance->lb_taken_leave = max(0, $exist_lb_taken_leave);
                            // $leaveBalance->lb_balance_remaining_leave = $exist_lb_balance_remaining_leave;
                            // $leaveBalance->lb_carried_forward = ($leaveBalance->lb_carried_forward ?? 0) + $leaveRequest->lvr_total_leave_days;
                            // // $leaveBalance->lb_total_leave = $leaveBalance->lb_taken_leave + $leaveBalance->lb_balance_remaining_leave;
                            // $leaveBalance->save();

                            // $exist_lb_taken_leave = $leaveBalance->lb_taken_leave - $leaveRequest->lvr_total_leave_days;
                            // $leaveBalance->lb_taken_leave = max(0, $exist_lb_taken_leave);
                            // // Increase remaining leave
                            // // $leaveBalance->lb_balance_remaining_leave = $leaveBalance->lb_balance_remaining_leave + $leaveRequest->lvr_total_leave_days;
                            // // Increase carried forward leave
                            // $leaveBalance->lb_carried_forward = ($leaveBalance->lb_carried_forward ?? 0) + $leaveRequest->lvr_total_leave_days;
                            // $leaveBalance->save();

                            $exist_lb_taken_leave = $leaveBalance->lb_taken_leave - $leaveRequest->lvr_total_leave_days;

                            // Update taken leave
                            $leaveBalance->lb_taken_leave = max(0, $exist_lb_taken_leave);

                            // Leave date month/year
                            $leaveMonth = date('m', strtotime($leaveRequest->lvr_start_date));
                            $leaveYear  = date('Y', strtotime($leaveRequest->lvr_start_date));

                            // Current month/year
                            $currentMonth = now()->month;
                            $currentYear  = now()->year;

                            // Previous month leave
                            if (
                                $leaveYear < $currentYear ||
                                ($leaveYear == $currentYear && $leaveMonth < $currentMonth)
                            ) {
                                // Add in carried forward
                                $leaveBalance->lb_carried_forward = ($leaveBalance->lb_carried_forward ?? 0) + $leaveRequest->lvr_total_leave_days;
                            } else {
                                // Current month leave
                                $leaveBalance->lb_balance_remaining_leave = $leaveBalance->lb_balance_remaining_leave + $leaveRequest->lvr_total_leave_days;
                            }

                            $leaveBalance->save();

                            LeaveBalance::where('lb_emp_id', $leaveRequest->lvr_emp_id)
                                ->where('lb_cat_type_id', $leaveRequest->lvr_cat_type_id)
                                ->where('lb_b_id', $leaveRequest->lvr_b_id)
                                ->where(function ($q) use ($leaveYear, $leaveMonth) {
                                    $q->where('lb_year', '>', $leaveYear)
                                      ->orWhere(function ($q2) use ($leaveYear, $leaveMonth) {
                                          $q2->where('lb_year', $leaveYear)
                                             ->where('lb_month', '>', $leaveMonth);
                                      });
                                })
                                ->increment('lb_balance_remaining_leave', $leaveRequest->lvr_total_leave_days);

                            // Track affected categories and days
                            if (!$leaveCategoriesAffected->has($leaveRequest->lvr_cat_type_id)) {
                                $leaveCategoriesAffected[$leaveRequest->lvr_cat_type_id] = 0;
                            }
                            $leaveCategoriesAffected[$leaveRequest->lvr_cat_type_id] += $leaveRequest->lvr_total_leave_days;
                            $totalRestoredDays += $leaveRequest->lvr_total_leave_days;

                            $leaveType = $leaveRequest->lvr_is_sandwich == 1 ? 'Sandwich' : 'Regular';
                            \Log::info("{$leaveType} leave balance restored", [
                                'leave_id' => $leaveRequest->lvr_id,
                                'category' => $leaveRequest->lvr_cat_type_id,
                                'days_restored' => $leaveRequest->lvr_total_leave_days,
                                'employee' => $leaveRequest->lvr_emp_id,
                                'new_taken' => $exist_lb_taken_leave,
                                // 'new_balance' => $exist_lb_balance_remaining_leave,
                                'is_sandwich' => $leaveRequest->lvr_is_sandwich == 1,
                                'leave_dates' => $leaveRequest->lvr_start_date . ' to ' . $leaveRequest->lvr_end_date
                            ]);
                        } else {
                            \Log::warning("Leave balance not found for restoration", [
                                'leave_id' => $leaveRequest->lvr_id,
                                'emp_id' => $leaveRequest->lvr_emp_id,
                                'category' => $leaveRequest->lvr_cat_type_id,
                                'business' => $leaveRequest->lvr_b_id,
                                'created_month' => date('m', strtotime($leaveRequest->created_at)),
                                'created_year' => date('Y', strtotime($leaveRequest->created_at))
                            ]);
                        }
                    } else {
                        // UPL doesn't affect paid leave balance but log for tracking
                        \Log::info("UPL leave deleted", [
                            'leave_id' => $leaveRequest->lvr_id,
                            'days' => $leaveRequest->lvr_total_leave_days,
                            'employee' => $leaveRequest->lvr_emp_id,
                            'is_sandwich' => $leaveRequest->lvr_is_sandwich == 1
                        ]);
                    }
                }

                // Now delete the leave request
                $leaveDeleted = $leaveRequest->delete();

                if (!$leaveDeleted) {
                    $isDeleted = false;
                    \Log::error("Failed to delete leave request", [
                        'leave_id' => $leaveRequest->lvr_id
                    ]);
                    break; // Stop processing if any deletion fails
                }
            }

            if ($isDeleted) {
                DB::commit(); // Commit transaction if all deletions are successful

                // Create detailed success message
                $message = "Leave request(s) deleted successfully";

                if ($sandwichLeaves->count() > 0) {
                    $message .= " (including {$sandwichLeaves->count()} sandwich leave(s))";
                }

                if ($childLeaves->count() > 0) {
                    $message .= " and {$childLeaves->count()} related leave(s)";
                }

                if ($totalRestoredDays > 0) {
                    $message .= ". Total {$totalRestoredDays} day(s) credited back to leave balance";

                    // Add category-wise breakdown
                    if ($leaveCategoriesAffected->count() > 0) {
                        $categoryBreakdown = [];
                        foreach ($leaveCategoriesAffected as $categoryId => $days) {
                            $categoryName = $this->getLeaveCategoryName($categoryId);
                            $categoryBreakdown[] = "{$days} day(s) to {$categoryName}";
                        }
                        if (count($categoryBreakdown) > 1) {
                            $message .= " (" . implode(", ", $categoryBreakdown) . ")";
                        }
                    }
                }

                $message .= ".";

                return [
                    'result' => [
                        'total_leaves_deleted' => $allRelatedLeaves->count(),
                        'sandwich_leaves_deleted' => $sandwichLeaves->count(),
                        'total_days_restored' => $totalRestoredDays,
                        'categories_affected' => $leaveCategoriesAffected->toArray()
                    ],
                    'message' => $message,
                    'status' => true
                ];
            } else {
                DB::rollBack(); // Rollback transaction if any deletion fails
                return [
                    'result' => [],
                    'message' => 'Failed to delete one or more leave requests.',
                    'status' => false
                ];
            }

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error deleting leave request: ' . $e->getMessage(), [
                'leave_id' => $id,
                'user_id' => $user->emp_id,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'result' => [],
                'message' => 'An error occurred while deleting the leave request: ' . $e->getMessage(),
                'status' => false
            ];
        }
    }


    /**
     * Helper method to get leave category name
     */
    private function getLeaveCategoryName($categoryId)
    {
        // Map common category IDs to names
        $categoryNames = [
            207 => 'CL',
            208 => 'ML',
            209 => 'SL',
            215 => 'UPL',
            564 => 'Comp Off'
        ];

        return $categoryNames[$categoryId] ?? "Category {$categoryId}";
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

        $compOffLeaveType = CompOffResource::collection(MasterTable::where('m_id', 567)->get());
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

    public function getLeaveBalance($id = null)
    {
        if ($id) {
            $user = Employee::where('emp_id', $id)->firstOrFail();
        } else {
            $user = Auth::user();
        }

        $month = request()->input('month') ?? now()->month;
        $year = request()->input('year') ?? now()->year;

        // Validate month and year inputs
        if (!is_numeric($month) || $month < 1 || $month > 12) {
            return [
                'result' => [],
                'message' => 'Invalid month provided. Please provide a month between 1 and 12.',
                'status' => false
            ];
        }

        if (!is_numeric($year) || $year < 2000 || $year > now()->year) {
            return [
                'result' => [],
                'message' => 'Invalid year provided.',
                'status' => false
            ];
        }

        if ($user->emp_gender_id == 34) { // If employee is Female
            $excludeLeaveCatId = 211; //Paternity Leave (PL)
        } elseif ($user->emp_gender_id == 33) { // If employee is Male
            $excludeLeaveCatId = 210; //Maternity Leave (ML)
        } else {
            $excludeLeaveCatId = null; // No exclusion if gender is not specified
        }

        // Fetch leave balances for the specified month and year
        $leaveBalances = LeaveBalance::where('lb_emp_id', $user->emp_id)
            ->selectRaw('lb_cat_type_id, lb_alloted_leave as total_alloted_leave, lb_taken_leave as total_taken_leave, lb_balance_remaining_leave as total_balance_remaining_leave, lb_carried_forward as total_carried_forward')
            ->when($excludeLeaveCatId, function ($query) use ($excludeLeaveCatId) {
                return $query->whereNot('lb_cat_type_id', $excludeLeaveCatId);
            })
            ->where('lb_year', $year)
            ->where('lb_month', $month)
            ->get();

        $leaveBalanceData = LeaveBalanceResource::collection($leaveBalances);

        $compOffBalance = CompOffBalance::where('cb_emp_id', $user->emp_id)
            ->selectRaw('567 as lb_cat_type_id,cb_alloted as total_alloted_leave,cb_taken as total_taken_leave,cb_balance_remaining as total_balance_remaining_leave,cb_carried_forward as total_carried_forward')
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
            })
            ->where('lvr_status', '!=', 170)
            ->exists();

        return $overlappingLeave;
    }

    public function getLeaveListForApproval(Request $request)
    {
        $user = Auth::user();
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);

        $query = LeaveRequest::withoutGlobalScope('not_sandwich')->where('lvr_b_id', $user->emp_b_id)->whereNotIn('lvr_status', [139, 192, 156])->where('lvr_p_id', null)->approvableBy($user);

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



        $requestedLeaveEmployees = LeaveRequest::withoutGlobalScope('not_sandwich')
            ->where('lvr_b_id', $user->emp_b_id)
            // ->where('lvr_status', 140) // Requested
            // ->where('lvr_stage_completed', 0)
            ->where('lvr_start_date', now()->format('Y-m-d'))
            ->where('deleted_at', NULL)
            ->approvableBy($user)
            ->with('fh_employee:emp_id,emp_full_name,emp_code')
            ->latest('lvr_id')
            ->get()
            ->map(function ($leave) {
                return [
                    'emp_name' => trim(preg_replace('/\s+/', ' ', $leave->fh_employee->emp_full_name)),
                    'emp_code' => $leave->fh_employee->emp_code,
                    // 'start_date' => $leave->lvr_start_date,
                    // 'lvr_status' => $leave->lvr_status,
                ];
            })
            ->unique('emp_code')
            ->values();

        if ($query->exists()) {
            // return ReturnHelper::jsonApiReturn(new PaginatedResource($data, LeaveResource::class));
            $response = ReturnHelper::jsonApiReturn(
                new PaginatedResource($data, LeaveResource::class)
            );

            $responseData = $response->getData(true);

            // requested_leave ko result ke andar add karo
            // $responseData['result']['requested_leave'] = $requestedLeaveEmployees;
            if (!empty($responseData['result']['data'])) {
                foreach ($responseData['result']['data'] as &$leave) {

                    // super_admin_log ke baad inject
                    $newLeave = [];

                    foreach ($leave as $key => $value) {
                        $newLeave[$key] = $value;

                        if ($key === 'super_admin_log') {
                            $newLeave['requested_leave'] = $requestedLeaveEmployees;
                        }
                    }

                    $leave = $newLeave;
                }
            }

            return response()->json($responseData);
        } else {
            return response()->json(['result' => [], 'message' => 'No leave application found.', 'status' => false]);
        }
    }

    public function getSameDateLeaves(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'date' => 'required|date',
            'exclude_emp_id' => 'required|integer',
        ]);

        $date = Carbon::parse($request->date)->format('Y-m-d');

        $leaves = LeaveRequest::withoutGlobalScope('not_sandwich')
            ->where('lvr_b_id', $user->emp_b_id)
            ->whereDate('lvr_start_date', '<=', $date)
            ->whereDate('lvr_end_date', '>=', $date) // date range me aana chahiye
            ->where('lvr_emp_id', '!=', $request->exclude_emp_id) // current employee exclude
            ->where('lvr_status', '!=', 170)
            ->whereNull('deleted_at')
            ->approvableBy($user) // optional (agar approval based hi dikhana hai)
            ->with('fh_employee:emp_id,emp_full_name,emp_code')
            ->latest('lvr_id')
            ->get()
            ->map(function ($leave) {
                return [
                    'emp_id'   => $leave->fh_employee->emp_id,
                    'emp_name' => trim(preg_replace('/\s+/', ' ', $leave->fh_employee->emp_full_name)),
                    'emp_code' => $leave->fh_employee->emp_code,
                    'status'     => $leave->fh_approval_status?->m_name ?? null,
                ];
            })
            ->unique('emp_id')
            ->values();

        return response()->json([
            'result' => [
                'data' => $leaves,
            ],
            'status' => true,
        ]);
    }

    public function checkSandwichLeave09Dec(Request $request)
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
            'leave_category_id' => 'required',
            'leave_start_date' => 'required|date',
            'leave_end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $user = Auth::user();
        $startDate = Carbon::parse($request->leave_start_date);
        $endDate = Carbon::parse($request->leave_end_date);
        $empId = $user->emp_id;
        $leaveCat = $request->leave_category_id;

        // Check if sandwich leave is allowed for this leave type
        if ($leaveCat != 215) {
            $leaveTypes = LeaveType::where('lvt_pl_id', $user->emp_pl_id)
                ->where('lvt_cat_type_id', $leaveCat)
                ->select('lvt_is_sandwich')
                ->first();
                
            if ($leaveTypes == null || $leaveTypes->lvt_is_sandwich == 0) {
                return response()->json([
                    'status' => true,
                    'message' => 'The selected leave type does not allow sandwich leave.'
                ], 400);
            }
        }

        // Fetch ALL holidays (not filtered by month) - we'll filter by date range later
        $allHolidays = PolicyHolidayList::where('phl_b_id', 143)->get()
            ->flatMap(function ($holiday) {
                return Carbon::parse($holiday->phl_start_date)
                    ->toPeriod(Carbon::parse($holiday->phl_end_date))
                    ->toArray();
            })
            ->map(function ($date) {
                return Carbon::parse($date)->toDateString();
            })
            ->unique()
            ->values()
            ->toArray();

        // Get week-offs for the employee (need to get for relevant months)
        $employee = Employee::where('emp_id', $empId)->first();
        
        // Calculate which months we need to check (from potential previous leave to current leave)
        $checkStartDate = $startDate->copy()->subDays(10);
        $checkEndDate = $endDate->copy();
        
        $allWeekOffs = [];
        $currentCheck = $checkStartDate->copy()->startOfMonth();
        while ($currentCheck->lte($checkEndDate)) {
            $year = $currentCheck->year;
            $month = $currentCheck->month;
            $monthWeekOffs = CentralLogics::getWeekOffDates($employee, $year, $month);
            $allWeekOffs = array_merge($allWeekOffs, $monthWeekOffs);
            $currentCheck->addMonth();
        }
        $allWeekOffs = array_unique($allWeekOffs);

        // Find previous consecutive leave (within last 10 days)
        $previousLeaves = LeaveRequest::where('lvr_emp_id', $empId)
            ->where('lvr_end_date', '<', $startDate)
            ->where('lvr_end_date', '>=', $startDate->copy()->subDays(10))
            ->orderByDesc('lvr_end_date')
            ->whereNotIn('lvr_status', [170])
            ->get();

        // Identify consecutive leave periods
        $tempStartDate = $startDate->copy();
        $tempEndDate = $endDate->copy();
        $previousLeaveCategoryId = null;

        if ($previousLeaves->isNotEmpty()) {
            $leave = $previousLeaves->first();
            $leaveEndDate = Carbon::parse($leave->lvr_end_date);
            $leaveStartDate = Carbon::parse($leave->lvr_start_date);

            // Check gap between previous leave end and current leave start
            $gapDays = collect();
            $checkDate = $leaveEndDate->copy()->addDay();
            
            while ($checkDate->lessThan($startDate)) {
                $gapDays->push($checkDate->toDateString());
                $checkDate->addDay();
            }

            // Check if ALL gap days are holidays or week-offs
            $allHolidaysOrWeekOffs = $gapDays->every(function ($date) use ($allHolidays, $allWeekOffs) {
                return in_array($date, $allHolidays) || in_array($date, $allWeekOffs);
            });

            // If all gap days are holidays/week-offs, this is a sandwich leave
            if ($allHolidaysOrWeekOffs && $gapDays->isNotEmpty()) {
                $tempStartDate = $leaveStartDate;
                $previousLeaveCategoryId = $leave->lvr_cat_type_id;
            }
        }

        // Calculate extra days (holidays/week-offs) in the gap
        $extraDays = collect();
        
        // If there's a previous leave forming sandwich, count the gap days
        if ($previousLeaveCategoryId !== null) {
            $checkDate = $tempStartDate->copy();
            while ($checkDate->lessThan($startDate)) {
                $dateStr = $checkDate->toDateString();
                if (in_array($dateStr, $allHolidays) || in_array($dateStr, $allWeekOffs)) {
                    $extraDays->push($dateStr);
                }
                $checkDate->addDay();
            }
        }
        
        // Also check for holidays/week-offs WITHIN the current leave period
        $checkDate = $startDate->copy()->addDay();
        while ($checkDate->lessThan($endDate)) {
            $dateStr = $checkDate->toDateString();
            if (in_array($dateStr, $allHolidays) || in_array($dateStr, $allWeekOffs)) {
                $extraDays->push($dateStr);
            }
            $checkDate->addDay();
        }

        $extraDays = $extraDays->unique()->values();

        // Check if there are extra days (sandwich scenario)
        if ($extraDays->isEmpty()) {
            return response()->json([
                'status' => true,
                'result' => []
            ]);
        }

        // Get leave balances
        $clBalance = LeaveBalance::where('lb_emp_id', $empId)
            ->where('lb_cat_type_id', 207)
            ->where('lb_year', now()->year)
            ->where('lb_month', now()->month)
            ->first();
        $slBalance = LeaveBalance::where('lb_emp_id', $empId)
            ->where('lb_cat_type_id', 208)
            ->where('lb_year', now()->year)
            ->where('lb_month', now()->month)
            ->first();

        $clAvailable = $clBalance ? $clBalance->lb_balance_remaining_leave : 0;
        $slAvailable = $slBalance ? $slBalance->lb_balance_remaining_leave : 0;
        $requestedLeaveDays = $startDate->diffInDays($endDate) + 1;

        // Determine which category to use based on sandwich rules
        $deductedLeaveCategory = 215;
        $categoryName = 'UPL';

        // Apply sandwich rules based on previous and current leave types
        if ($previousLeaveCategoryId !== null) {
            // Previous leave exists - check combination rules
            if ($previousLeaveCategoryId == 207 && $leaveCat == 207) {
                // CL + HO/WO + CL
                // Check if CL balance can cover: requested days + extra sandwich days
                if ($clAvailable >= ($requestedLeaveDays + $extraDays->count())) {
                    $deductedLeaveCategory = 207;
                    $categoryName = 'CL';
                } elseif ($slAvailable >= $extraDays->count()) {
                    // Only extra days from SL (requested days already from CL)
                    $deductedLeaveCategory = 208;
                    $categoryName = 'SL';
                }
            } elseif ($previousLeaveCategoryId == 208 && $leaveCat == 208) {
                // SL + HO/WO + SL
                if ($slAvailable >= ($requestedLeaveDays + $extraDays->count())) {
                    // SL can cover both requested days + extra sandwich days
                    $deductedLeaveCategory = 208;
                    $categoryName = 'SL';
                } elseif ($clAvailable >= $extraDays->count()) {
                    // Not enough SL, check if CL can cover extra days
                    $deductedLeaveCategory = 207;
                    $categoryName = 'CL';
                } else {
                    // Not enough balance in either - mark as UPL
                    $deductedLeaveCategory = 215;
                    $categoryName = 'UPL';
                }
            } elseif (($previousLeaveCategoryId == 207 && $leaveCat == 208) ||
                  ($previousLeaveCategoryId == 208 && $leaveCat == 207)) {
                // CL + HO/WO + SL OR SL + HO/WO + CL
                // Check if the CURRENT leave category has enough balance for requested + extra days
                if ($leaveCat == 208) {
                    // Current leave is SL
                    if ($slAvailable >= ($requestedLeaveDays + $extraDays->count())) {
                        // SL can cover both requested days + extra sandwich days
                        $deductedLeaveCategory = 208;
                        $categoryName = 'SL';
                    } elseif ($clAvailable >= $extraDays->count()) {
                        // SL for requested days, CL for extra days only
                        $deductedLeaveCategory = 207;
                        $categoryName = 'CL';
                    } else {
                        // Not enough balance in either - mark as UPL
                        $deductedLeaveCategory = 215;
                        $categoryName = 'UPL';
                    }
                } elseif ($leaveCat == 207) {
                    // Current leave is CL
                    if ($clAvailable >= ($requestedLeaveDays + $extraDays->count())) {
                        // CL can cover both requested days + extra sandwich days
                        $deductedLeaveCategory = 207;
                        $categoryName = 'CL';
                    } elseif ($slAvailable >= $extraDays->count()) {
                        // CL for requested days, SL for extra days only
                        $deductedLeaveCategory = 208;
                        $categoryName = 'SL';
                    } else {
                        // Not enough balance in either - mark as UPL
                        $deductedLeaveCategory = 215;
                        $categoryName = 'UPL';
                    }
                }
            } elseif (($previousLeaveCategoryId == 207 && $leaveCat == 215) ||
                  ($previousLeaveCategoryId == 215 && $leaveCat == 207)) {
                // CL + HO/WO + UPL OR UPL + HO/WO + CL
                if ($leaveCat == 207 && $clAvailable >= ($requestedLeaveDays + $extraDays->count())) {
                    $deductedLeaveCategory = 207;
                    $categoryName = 'CL';
                } elseif ($clAvailable >= $extraDays->count()) {
                    $deductedLeaveCategory = 207;
                    $categoryName = 'CL';
                } elseif ($slAvailable >= $extraDays->count()) {
                    $deductedLeaveCategory = 208;
                    $categoryName = 'SL';
                }
            } elseif (($previousLeaveCategoryId == 208 && $leaveCat == 215) ||
                      ($previousLeaveCategoryId == 215 && $leaveCat == 208)) {
                // SL + HO/WO + UPL OR UPL + HO/WO + SL
                if ($leaveCat == 208 && $slAvailable >= ($requestedLeaveDays + $extraDays->count())) {
                    $deductedLeaveCategory = 208;
                    $categoryName = 'SL';
                } elseif ($slAvailable >= $extraDays->count()) {
                    $deductedLeaveCategory = 208;
                    $categoryName = 'SL';
                } elseif ($clAvailable >= $extraDays->count()) {
                    $deductedLeaveCategory = 207;
                    $categoryName = 'CL';
                }
            } elseif ($previousLeaveCategoryId == 215 && $leaveCat == 215) {
                // UPL + HO/WO + UPL - always UPL
                $deductedLeaveCategory = 215;
                $categoryName = 'UPL';
            }
        } else {
            // No previous leave - just check current leave type balances for holidays within the leave
            if ($leaveCat == 207) {
                if ($clAvailable >= ($requestedLeaveDays + $extraDays->count())) {
                    $deductedLeaveCategory = 207;
                    $categoryName = 'CL';
                } elseif ($slAvailable >= $extraDays->count()) {
                    $deductedLeaveCategory = 208;
                    $categoryName = 'SL';
                }
            } elseif ($leaveCat == 208) {
                if ($slAvailable >= ($requestedLeaveDays + $extraDays->count())) {
                    $deductedLeaveCategory = 208;
                    $categoryName = 'SL';
                } elseif ($clAvailable >= $extraDays->count()) {
                    $deductedLeaveCategory = 207;
                    $categoryName = 'CL';
                }
            }
        }

        // Return sandwich leave warning
        return response()->json([
            'status' => true,
            'result' => [
                [
                    'message' => "Your leave from {$startDate->format('d M Y')} to {$endDate->format('d M Y')} will form a sandwich leave with {$extraDays->count()} day(s) of weekoff/holiday. These extra days will be marked as {$categoryName}.",
                    'extra_days' => $extraDays->map(function ($date) {
                        return Carbon::parse($date)->format('d M, Y');
                    }),
                    'extra_days_count' => $extraDays->count(),
                    'deducted_leave_count' => $extraDays->count(),
                    'deducted_leave_category' => $deductedLeaveCategory,
                    'deducted_leave_category_name' => $categoryName
                ]
            ]
        ]);
    }

    public function checkSandwichLeave(Request $request)
    {
        /* -------------------- 1. Validate / Parse Dates -------------------- */
        try {
            $request->merge([
                'leave_start_date' => Carbon::createFromFormat('d M, Y', $request->leave_start_date)->format('Y-m-d'),
                'leave_end_date'   => Carbon::createFromFormat('d M, Y', $request->leave_end_date)->format('Y-m-d'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid date format. Please use "03 Feb, 2025".',
                'errors'  => [
                    'leave_start_date' => ['Invalid leave start date format'],
                    'leave_end_date'   => ['Invalid leave end date format'],
                ]
            ], 422);
        }

        $request->validate([
            'leave_category_id' => 'required',
            'leave_start_date'  => 'required|date',
            'leave_end_date'    => 'required|date|after_or_equal:leave_start_date',
        ]);

        /* ------------------------- Base Variables -------------------------- */
        $user       = Auth::user();
        $empId      = $user->emp_id;
        $leaveCat   = $request->leave_category_id;
        $startDate  = Carbon::parse($request->leave_start_date);
        $endDate    = Carbon::parse($request->leave_end_date);

        /* ----------------- 2. Check If Sandwich Leave Allowed -------------- */
        if ($leaveCat != 215) {
            $leaveTypes = LeaveType::where('lvt_pl_id', $user->emp_pl_id)
                ->where('lvt_cat_type_id', $leaveCat)
                ->value('lvt_is_sandwich');

            if (!$leaveTypes) {
                return response()->json([
                    'status'  => true,
                    'message' => 'The selected leave type does not allow sandwich leave.'
                ], 400);
            }
        }

        /* -------------------- 3. Fetch All Holidays ------------------------ */
        $allHolidays = PolicyHolidayList::where('phl_b_id', 143)
            ->get()
            ->flatMap(function ($h) {
                return Carbon::parse($h->phl_start_date)
                    ->toPeriod($h->phl_end_date)
                    ->map(fn($d) => $d->toDateString());
            })
            ->unique()
            ->values()
            ->toArray();

        /* -------------------- 4. Fetch All Week Offs ----------------------- */
        $employee = Employee::find($empId);

        $checkStart = $startDate->copy()->subDays(10);
        $checkEnd   = $endDate->copy();

        $weekOffs = [];
        $loopDay = $checkStart->copy()->startOfMonth();

        while ($loopDay->lte($checkEnd)) {
            $weekOffs = array_merge($weekOffs,
                CentralLogics::getWeekOffDates($employee, $loopDay->year, $loopDay->month)
            );
            $loopDay->addMonth();
        }

        $weekOffs = array_unique($weekOffs);

        /* -------------------- 5. Check Previous Leave ---------------------- */
        $previousLeave = LeaveRequest::where('lvr_emp_id', $empId)
            ->whereBetween('lvr_end_date', [
                $startDate->copy()->subDays(10), 
                $startDate->copy()->subDay()
            ])
            ->whereNotIn('lvr_status', [170])
            ->orderByDesc('lvr_end_date')
            ->first();

        $previousCategory = null;
        $extraDays = collect();

        if ($previousLeave) {
            $prevStart = Carbon::parse($previousLeave->lvr_start_date);
            $prevEnd   = Carbon::parse($previousLeave->lvr_end_date);

            $gapDates = collect();
            $dt = $prevEnd->copy()->addDay();

            while ($dt->lessThan($startDate)) {
                $gapDates->push($dt->toDateString());
                $dt->addDay();
            }

            if ($gapDates->isNotEmpty() &&
                $gapDates->every(fn($d) => in_array($d, $allHolidays) || in_array($d, $weekOffs))) {
                $previousCategory = $previousLeave->lvr_cat_type_id;

                foreach ($gapDates as $g) {
                    if (in_array($g, $allHolidays) || in_array($g, $weekOffs)) {
                        $extraDays->push($g);
                    }
                }
            }
        }

        /* --------- 6. Holiday/WeekOff Inside Current Leave Range ---------- */
        $dt = $startDate->copy()->addDay();
        while ($dt->lessThan($endDate)) {
            $d = $dt->toDateString();
            if (in_array($d, $allHolidays) || in_array($d, $weekOffs)) {
                $extraDays->push($d);
            }
            $dt->addDay();
        }

        $extraDays = $extraDays->unique()->values();

        if ($extraDays->isEmpty()) {
            return response()->json(['status' => true, 'result' => []]);
        }

        /* ---------------------- 7. Fetch Leave Balances -------------------- */
        $balances = LeaveBalance::where('lb_emp_id', $empId)
            ->whereIn('lb_cat_type_id', [207, 208])
            ->where('lb_year', now()->year)
            ->where('lb_month', now()->month)
            ->pluck('lb_balance_remaining_leave', 'lb_cat_type_id');

        $cl = $balances[207] ?? 0;
        $sl = $balances[208] ?? 0;
        $requestedDays = $startDate->diffInDays($endDate) + 1;

        /* -------------------- 8. Sandwich Deduction Logic ------------------ */
        $extra = $extraDays->count();
        $category = 'UPL';
        $deductId = 215;

        $can = fn($bal, $need) => $bal >= $need;

        if ($previousCategory) {
            /* SAME CATEGORY CONTINUING */
            if ($previousCategory == $leaveCat) {
                if ($leaveCat == 207 && $can($cl, $requestedDays + $extra)) {
                    $category = 'CL'; $deductId = 207;
                }
                elseif ($leaveCat == 208 && $can($sl, $requestedDays + $extra)) {
                    $category = 'SL'; $deductId = 208;
                }
                elseif ($can($cl, $extra)) { $category = 'CL'; $deductId = 207; }
                elseif ($can($sl, $extra)) { $category = 'SL'; $deductId = 208; }
            }

            /* SWITCH CAT: CL->SL or SL->CL */
            elseif (in_array($previousCategory.$leaveCat, ['207208','208207'])) {
                if ($leaveCat == 207 && $can($cl, $requestedDays + $extra)) {
                    $category = 'CL'; $deductId = 207;
                } elseif ($leaveCat == 208 && $can($sl, $requestedDays + $extra)) {
                    $category = 'SL'; $deductId = 208;
                } elseif ($can($cl, $extra)) { $category = 'CL'; $deductId = 207; }
                elseif ($can($sl, $extra)) { $category = 'SL'; $deductId = 208; }
            }

            /* PREVIOUS OR CURRENT IS UPL */
            else {
                if ($leaveCat == 207 && $can($cl, $requestedDays + $extra)) {
                    $category = 'CL'; $deductId = 207;
                } elseif ($leaveCat == 208 && $can($sl, $requestedDays + $extra)) {
                    $category = 'SL'; $deductId = 208;
                } elseif ($can($cl, $extra)) { $category = 'CL'; $deductId = 207; }
                elseif ($can($sl, $extra)) { $category = 'SL'; $deductId = 208; }
            }
        }

        else {
            /* NO PREVIOUS LEAVE — SIMPLE CASE */
            if ($leaveCat == 207 && $can($cl, $requestedDays + $extra)) {
                $category = 'CL'; $deductId = 207;
            } elseif ($leaveCat == 208 && $can($sl, $requestedDays + $extra)) {
                $category = 'SL'; $deductId = 208;
            } elseif ($can($cl, $extra)) {
                $category = 'CL'; $deductId = 207;
            } elseif ($can($sl, $extra)) {
                $category = 'SL'; $deductId = 208;
            }
        }

        /* ----------------------- 9. Final Response ------------------------- */
        return response()->json([
            'status' => true,
            'result' => [[
                'message' => "Your leave from {$startDate->format('d M Y')} to {$endDate->format('d M Y')} will form a sandwich leave with {$extra} day(s). These extra days will be marked as {$category}.",
                'extra_days' => $extraDays->map(fn($d) => Carbon::parse($d)->format('d M, Y')),
                'extra_days_count' => $extra,
                'deducted_leave_category' => $deductId,
                'deducted_leave_category_name' => $category
            ]]
        ]);
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
