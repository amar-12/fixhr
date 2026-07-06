<?php

namespace App\Http\Controllers\Api\Employee;

use App\Helpers\CentralLogics;
use App\Http\Resources\Attendance\AttendanceResource;
use App\Http\Resources\MasterTableResource;
use App\Http\Resources\Policy\PolicyShiftTimeResource;
use App\Http\Resources\RolePermission\AppMenusResource;
use App\Http\Resources\RolePermission\AppRolesHasPermissionsResource;
use App\Models\AppMenu;
use App\Models\AppRolesHasPermission;
use App\Models\AttendanceRecord;
use App\Models\AttendanceLog;
use App\Models\AttendanceException;
use App\Models\PolicyHolidayList;
use App\Models\LeaveRequest;
use App\Models\PolicyLeave;
use App\Models\TadaRequestPlan;

use App\Models\EmployeeApprovalMapping;
use App\Models\EmployeeApprovalStatus;
use App\Models\ApprovalModule;
use App\Models\ProcessApprover;
use App\Models\TadaClaim;
use App\Models\GatePass;
use App\Models\LoanRequest;
use App\Models\OtApprovalStatus;
use App\Models\RuleCriterion;
use App\Helpers\ApprovalHelper;
use ChandraHemant\HtkcUtils\PaginatedResource;
use ChandraHemant\HtkcUtils\FirebaseNotification;
use App\Helpers\NotificationHelper;

use Carbon\Carbon;
use ChandraHemant\HtkcUtils\ReturnHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\EmployeeResource;
use App\Models\AcademicDetail;
use App\Models\Employee;
use App\Models\FamilyDetail;
use App\Models\UniformItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Aws\AwsHelper;
use App\Helpers\ShiftResolver;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\Attendance\AttendanceOutDoorResource;
use App\Models\AttendanceOutDoor;

class EmployeeApiController extends Controller
{

    protected $user, $awsHelper;

    public function __construct()
    {
        $this->user = Auth::user();
        if (env('STORE_ON_S3')) {
            $this->awsHelper = new AwsHelper();
        }
    }


    //  public function index()
    // {
    //     $user = Auth::user();
    //     $employee = Employee::where('emp_id', $user->emp_id)->get();

    //     if ($employee) {
    //         return ReturnHelper::jsonApiReturn(EmployeeResource::collection($employee)->all());
    //     }
    //     return response()->json(['result' => [], 'status' => false]);
    // }


    public function index()
    {
        $user = Auth::user();
        $employee = Employee::with(['uniformItems', 'academicDetails', 'familyDetails', 'userDevices','fh_assets','assetTypes'])
        ->where('emp_id', $user->emp_id)
        // ->where('emp_id',798)
        ->get();
        // dd($employee);

        if ($employee->isNotEmpty()) {
            return ReturnHelper::jsonApiReturn(EmployeeResource::collection($employee)->all());
        }

        return response()->json([
            'result' => [],
            'status' => false
        ]);
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
    public function store(Request $request)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getInitialData()
    {
        $resP = ['employee_data' => [], 'menus_data' => [], 'today_attendance_count' => [], 'today_attendance_data' => [], 'current_plan' => [], 'menus_permission_data' => [], 'allowed_check_in_methods' => []];
        try {
            $user = Auth::user();

            //start employee data
            $employee = Employee::where('emp_id', $user->emp_id)->first();
            if ($employee) {
                $resP['employee_data'] = EmployeeResource::collection([$employee])->all();
            }
            //end employee data

            //start allowed check in methods
            $resP['allowed_check_in_methods'] = $user->emp_checkin_method_id ? MasterTableResource::collection($user->fh_checkin_method()) : [];
            //end allowed check in methods

            //start get  shift details
            // $resP['shift_details'] = $user->fh_shift_type() ? PolicyShiftTimeResource::collection($user->fh_shift_type()->get()) : [];

            $resP['shift_details'] = $this->getAutoAssignedShift($user, Carbon::now()->format('Y-m-d'));

            //end get  shift details

            //start menu data
            // $menus = AppMenu::where('menu_status', 1)->whereNot('menu_id',1)->orderBy('menu_sequence', 'ASC')->get();
            // $permissionData = AppRolesHasPermission::where(['rhp_b_id'=> Auth::user()->emp_b_id, 'rhp_role_id'=> Auth::user()->emp_role_id])->pluck('rhp_permissions')->first();
            // $hasAttendance = false;
            // if($permissionData){
            //     $hasAttendance = isset(json_decode($permissionData,true)[1]);
            // }
            // if($menus) {
            //     $resP['menus_data'] =  [['menus'=>AppMenusResource::collection($menus)->all(),
            //     'drawer_menu' => config('app_drawer_menu.app_drawer_menu'),
            //     'has_attendance' => $hasAttendance,
            //     ]];
            // }
            $permissionData = AppRolesHasPermission::where(['rhp_b_id' => Auth::user()->emp_b_id, 'rhp_role_id' => Auth::user()->emp_role_id])->pluck('rhp_permissions')->first();
            $hasAttendance = false;
            $menus = collect([]);
            $drawer_menu = collect([]);

            if ($permissionData) {
                $permissionArray = json_decode($permissionData, true);

                if (is_array($permissionArray)) {
                    $hasAttendance = isset($permissionArray[1]);
                    $menus = AppMenu::where('menu_status', 1)
                        ->whereNot('menu_id', 1)
                        ->whereNot('menu_group', 'AppDrawer')
                        ->whereIn('menu_id', array_keys($permissionArray))
                        ->orderBy('menu_sequence', 'ASC')
                        ->get();
                    $drawer_menu = AppMenu::where('menu_status', 1)
                        ->where('menu_group', 'AppDrawer')
                        ->whereIn('menu_id', array_keys($permissionArray))
                        ->orderBy('menu_sequence', 'ASC')
                        ->get();
                }
            } else {
                return response()->json(['result' => [], 'status' => false, 'message' => 'Please contact the administrator to request permission to access the application.']);
            }

            if ($menus->isNotEmpty() || $drawer_menu->isNotEmpty()) {
                $resP['menus_data'] = [[
                    'menus' => AppMenusResource::collection($menus)->all(),
                    'drawer_menu' => $drawer_menu->map(function ($menu) {
                        return (new AppMenusResource($menu))->toArray2(request());
                    })->all(),
                    'has_attendance' => $hasAttendance,
                ]];
            }
            //end menu data


            //start get menu permission data
            $permissions = AppRolesHasPermission::where('rhp_role_id', $user->emp_role_id)->where('rhp_b_id', $user->emp_b_id)->first();

            if ($permissions) {
                $resP['menus_permission_data'] =  AppRolesHasPermissionsResource::collection([$permissions])->all();
            }


            //start attendance data count
            $currentMonth = now()->format('Y-m');
            [$year, $month] = explode('-', $currentMonth);

            $weekOfDates = CentralLogics::getWeekOffDates($employee, $year, $month);
            $attendanceData = CentralLogics::getMonthlyAttendanceCount($employee, $year, $month, $weekOfDates);

            $resP['today_attendance_count'] =  [[
                'present_count' => $attendanceData['presentCount'],
                'absentCount' => $attendanceData['absentCount'],
                'leaveCount' => $attendanceData['leaveCount'],
                'late_count' => $attendanceData['lateCount'],
            ]];
            //end attendance data count

            //start get today attendance data
            $today = now();
            $shift = ShiftResolver::resolveEmployeeShift($user, $today->format('Y-m-d'));
            $shiftPolicy = $shift ? $shift->shift : null;
            $endNextDay = $shiftPolicy ? (int)($shiftPolicy->pluck('pst_end_next_day')->first()) : 0;
            $endBy = $shiftPolicy && $endNextDay ? Carbon::parse($shiftPolicy->pluck('pst_end_by')->first()) : null;
            if ($endNextDay && $endBy && $today->lte($endBy)) {
                $today = now()->subDay();
            }
            $todayDate = $today->copy()->toDateString();
            $attendance = AttendanceRecord::where('atd_emp_id', $user->emp_id)
                ->whereDate('atd_date', $todayDate)
                ->first();
            if (!$attendance) {
                $attendance = AttendanceLog::where('al_emp_id', $user->emp_id)
                    ->whereDate('al_date', $todayDate)
                    ->first();
            }
            if ($attendance) {
                $resP['today_attendance_data'] =  AttendanceResource::collection([$attendance])->all();
            }
            //end get today attendance data


            //start get current plan
            $currentDateTime = Carbon::now();
            $plan = TadaRequestPlan::whereHas('fh_policy_tada_travel_type', function ($query) {
                $query->where('pttt_type_id', 124);
            })
                ->where('trp_emp_id', $user->emp_id)
                ->whereRaw("CONCAT(trp_start_date, ' ', trp_start_time) <= ?", [$currentDateTime])
                ->whereRaw("CONCAT(trp_end_date, ' ', trp_end_time) >= ?", [$currentDateTime])
                ->where('trp_is_claimed', 0)
                ->select('trp_id', 'trp_pttt_id', 'trp_name', 'trp_unique_id', 'trp_start_time', 'trp_end_time')
                ->first();

            if ($plan) {
                $travel_details = $plan->fh_tada_request_details->sortByDesc('trp_id')->last();
                $resP['current_plan'] =  [
                    'trp_id' => $plan->trp_id,
                    'trp_pttt_id' => $plan->trp_pttt_id,
                    'trp_name' => $plan->trp_name,
                    'trp_unique_id' => $plan->trp_unique_id,
                    'trp_start_time' => $plan->trp_start_time,
                    'trp_end_time' => $plan->trp_end_time,
                    'is_manual' => $plan->fh_policy_tada_travel_type->pttt_approval_type_id == 198,
                    'trd_id' => (int) ($travel_details->trd_id ?? 0),
                ];
            }
            //end get current plan


            return response()->json(['result' => $resP, 'status' => true, 'message' => 'data fetched successfully']);
        } catch (\Exception $e) {
            return response()->json(['result' => [], 'status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getInitialDataOutdoor()
    {
        $resP = ['employee_data' => [], 'today_attendance_data' => []];
        try {
            $user = Auth::user();

            //start employee data
            $employee = Employee::where('emp_id', $user->emp_id)->first();
            if ($employee) {
                $resP['employee_data'] = EmployeeResource::collection([$employee])->all();
            }
            //end employee data

            //start get  shift details
            $resP['shift_details'] = $user->fh_shift_type() ? PolicyShiftTimeResource::collection($user->fh_shift_type()->get()) : [];
            //end get  shift details

            //start get today OD attendance data
            $today = now();
            $shift = ShiftResolver::resolveEmployeeShift($user, $today->format('Y-m-d'));
            $shiftPolicy = $shift ? $shift->shift : null;
            $endNextDay = $shiftPolicy ? (int)($shiftPolicy->pluck('pst_end_next_day')->first()) : 0;
            $endBy = $shiftPolicy && $endNextDay ? Carbon::parse($shiftPolicy->pluck('pst_end_by')->first()) : null;
            if ($endNextDay && $endBy && $today->lte($endBy)) {
                $today = now()->subDay();
            }
            $todayDate = $today->copy()->toDateString();

            $attendance = AttendanceOutDoor::where('atd_od_emp_id', $user->emp_id)
                ->whereDate('atd_od_date', $todayDate)
                ->first();

            if ($attendance) {
                $resP['today_attendance_data'] =  AttendanceOutDoorResource::collection([$attendance])->all();
            }
            //end get today OD attendance data

            return response()->json(['result' => $resP, 'status' => true, 'message' => 'data fetched successfully']);
        } catch (\Exception $e) {
            return response()->json(['result' => [], 'status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getOutdoorAttendance(Request $request)
    {
        try {
            $user = Auth::user();

            $month = $request->month;

            if ($month) {
                $date = Carbon::createFromFormat('F, Y', $month);

                $startDate = $date->copy()->startOfMonth()->toDateString();
                $endDate = $date->copy()->endOfMonth()->toDateString();
            } else {
                $startDate = now()->startOfMonth()->toDateString();
                $endDate = now()->endOfMonth()->toDateString();
            }

            $attendance = AttendanceOutDoor::where('atd_od_emp_id', $user->emp_id)
                ->whereBetween('atd_od_date', [$startDate, $endDate])
                ->orderBy('atd_od_date', 'ASC')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Data fetched successfully',
                'result' => [
                    'attendance_list' => AttendanceOutDoorResource::collection($attendance) ?? []
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'result' => []
            ]);
        }
    }

    public function applyOutdoorRequest(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'result' => [],
                'message' => 'Employee not authenticated.'
            ]);
        }

        \DB::beginTransaction();

        try {
            $request->validate([
                'od_id'   => 'required|integer',
                'reason'  => 'required|string',
                'trp_id'  => 'nullable|integer',
            ]);

            $outdoor = AttendanceOutDoor::where([
                    'atd_od_id'     => $request->od_id,
                    'atd_od_emp_id' => $user->emp_id,
                    'atd_od_b_id'   => $user->emp_b_id,
                ])
                ->first();

            if (!$outdoor) {
                return response()->json([
                    'status' => false,
                    'result' => [],
                    'message' => 'Outdoor attendance record not found.'
                ]);
            }

            if ($outdoor->atd_od_request_status == 140) {
                return response()->json([
                    'status' => false,
                    'result' => [],
                    'message' => 'Outdoor attendance request already submitted.'
                ]);
            }

            if ($outdoor->atd_od_request_status == 157) {
                return response()->json([
                    'status' => false,
                    'result' => [],
                    'message' => 'Outdoor attendance request already approved.'
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Approval Settings
            |--------------------------------------------------------------------------
            */

            $amId = null;
            $nextApprover = null;

            $ruleCriteria = RuleCriterion::with('fh_approval_module')
                ->where('rc_b_id', $user->emp_b_id)
                ->where('rc_condition_option_id', 140)
                ->whereHas('fh_approval_module', function ($query) {
                    $query->where('am_module_id', 589)
                        ->where('am_status', 1);
                })
                ->first();

            $processApprovers = [];

            if ($ruleCriteria && $ruleCriteria->fh_approval_module) {
                $processApprovers = $ruleCriteria->fh_approval_module
                    ->filteredProcessApprovers($user->emp_b_id)
                    ->get();
            }

            if (count($processApprovers)) {
                $amId = $ruleCriteria->rc_am_id;
            } else {
                $approvalMapping = ApprovalHelper::getApprovalMapping($user->emp_b_id, $user->emp_id, 589);
                if (!$approvalMapping) {
                    return response()->json(['result' => [], 'status' => false, 'message' => 'Sorry! not found any approval settings for outdoor, contact HR.']);
                }
            }

            $nextApproverId = null;
            if ($amId) {
                $firstApprover = ProcessApprover::where('pa_am_id', $amId)
                    ->where('pa_sequence', 1)
                    ->first();
                $nextApproverId = $firstApprover->pa_emp_id ?? null;
            } else {
                $approvalMapping = ApprovalHelper::getApprovalMapping(
                    $user->emp_b_id,
                    $request->emp_id ?? $user->emp_id,
                    589
                );
                if ($approvalMapping) {
                    $approvalArray = ApprovalHelper::getApprovalArray($approvalMapping);
                    $nextApproverId = $approvalArray[0] ?? null;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Update Outdoor Request
            |--------------------------------------------------------------------------
            */

            $outdoor->update([
                'atd_od_remark'           => $request->reason,
                'atd_od_request_status'   => 140,
                'atd_od_stage_completed'  => 0,
                'atd_od_next_approver'    => 1,
                'atd_od_module_id'        => 589,
                'atd_od_am_id'            => $amId,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Travel Reference (Optional)
            |--------------------------------------------------------------------------
            */

            if ($request->filled('trp_id')) {
                $outdoor->update([
                    'trp_id' => $request->trp_id,
                ]);
            }

            \DB::commit();

            // Notification to next approver
            try {
                $serviceAccountPath = public_path('fixhr-app-firebase.json');

                if ($nextApproverId) {
                    $nextApproverEmp = Employee::find($nextApproverId);
                    if ($nextApproverEmp) {
                        $title = 'Out Door Attendance Approval Pending';
                        $body  = 'A new outdoor attendance request requires your approval.';
                        $additionalData = [
                            'notification_type' => 'outdoor_approval',
                            'status'            => 'requested',
                            'route'             => '/OutDoorApprovalPage',
                            'atd_od_id'         => $outdoor->atd_od_id,
                        ];
                        if (!empty($nextApproverEmp->emp_fcm_token) && $nextApproverEmp->emp_is_notification_enabled == '1') {
                            FirebaseNotification::sendPushNotification(
                                $title, $body,
                                $nextApproverEmp->emp_fcm_token,
                                $serviceAccountPath,
                                config('credentials')['FIREBASE_MESSAGING_CONFIG'],
                                $additionalData
                            );
                        }
                        NotificationHelper::saveNotification(
                            $user->emp_id,
                            $nextApproverEmp->emp_id,
                            $title, $body, $additionalData
                        );
                    }
                }
            } catch (\Throwable $e) {
                \Log::warning('Outdoor apply request notification failed: ' . $e->getMessage());
            }

            return response()->json([
                'status' => true,
                // 'result' => new AttendanceOutDoorResource($outdoor->fresh()),
                'result' => true,
                'message' => 'Outdoor attendance request submitted successfully.'
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'status' => false,
                'result' => [],
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function getOutdoorAttendanceByApproval(Request $request)
    {
        try {
            $user = Auth::user();

            $page  = $request->input('page', 1);
            $limit = $request->input('limit', 10);

            $query = AttendanceOutDoor::where('atd_od_b_id', $user->emp_b_id)->where('atd_od_request_status', '!=', 139)->approvableBy($user);

            if (!is_null($request->input('status'))) {
                $query->where('atd_od_request_status', $request->input('status'));
            }

            if ($request->filled('month')) {
                try {
                    $date = Carbon::createFromFormat('F, Y', trim($request->month));

                    $query->whereBetween('atd_od_date', [
                        $date->copy()->startOfMonth()->toDateString(),
                        $date->copy()->endOfMonth()->toDateString()
                    ]);
                } catch (\Exception $e) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Invalid month format. Use example: May, 2026',
                        'result' => []
                    ]);
                }
            }

            if (!is_null($request->input('from_date'))) {
                $query->whereDate(
                    'atd_od_date',
                    '>=',
                    Carbon::createFromFormat(
                        'd M, Y',
                        $request->input('from_date')
                    )->format('Y-m-d')
                );
            }

            if (!is_null($request->input('to_date'))) {
                $query->whereDate(
                    'atd_od_date',
                    '<=',
                    Carbon::createFromFormat(
                        'd M, Y',
                        $request->input('to_date')
                    )->format('Y-m-d')
                );
            }

            if ($request->boolean('last_15_days')) {
                $query->whereDate(
                    'atd_od_date',
                    '>=',
                    Carbon::now()->subDays(15)->format('Y-m-d')
                );
            }

            if ($request->filled('search_filter')) {
                $searchFilter = trim($request->search_filter);
                if (preg_match('/^\d{1,2}(?:\/\d{1,2})?(?:\/\d{4})?$/', $searchFilter)) {

                    $dateParts = explode('/', $searchFilter);

                    if (count($dateParts) == 1) {

                        $query->where(function ($q) use ($dateParts) {
                            $q->whereDay('atd_od_date', $dateParts[0])
                              ->orWhereDay('created_at', $dateParts[0]);
                        });

                    } elseif (count($dateParts) == 2) {

                        $query->where(function ($q) use ($dateParts) {
                            $q->where(function ($sub) use ($dateParts) {
                                $sub->whereMonth('atd_od_date', $dateParts[1])
                                    ->whereDay('atd_od_date', $dateParts[0]);
                            })
                            ->orWhere(function ($sub) use ($dateParts) {
                                $sub->whereMonth('created_at', $dateParts[1])
                                    ->whereDay('created_at', $dateParts[0]);
                            });
                        });

                    } elseif (count($dateParts) == 3) {

                        $formattedDate = Carbon::createFromFormat(
                            'd/m/Y',
                            $searchFilter
                        )->format('Y-m-d');

                        $query->where(function ($q) use ($formattedDate) {
                            $q->whereDate('atd_od_date', $formattedDate)
                              ->orWhereDate('created_at', $formattedDate);
                        });
                    }

                } else {
                    $query->whereHas('fh_employee', function ($q) use ($searchFilter) {
                        $q->whereRaw(
                            "REPLACE(emp_full_name,'  ',' ') LIKE ?",
                            ["%{$searchFilter}%"]
                        )
                        ->orWhereRaw(
                            "REPLACE(emp_fname,'  ',' ') LIKE ?",
                            ["%{$searchFilter}%"]
                        )
                        ->orWhereRaw(
                            "REPLACE(emp_lname,'  ',' ') LIKE ?",
                            ["%{$searchFilter}%"]
                        )
                        ->orWhere('emp_code', 'LIKE', "%{$searchFilter}%");
                    });
                }
            }

            $data = $query
                ->orderBy('atd_od_id', 'DESC')
                ->paginate($limit, ['*'], 'page', $page);

            if ($data->count()) {

                return ReturnHelper::jsonApiReturn(
                    new PaginatedResource(
                        $data,
                        AttendanceOutDoorResource::class
                    )
                );
            }

            return response()->json([
                'status' => false,
                'message' => 'No Outdoor Attendance Data found.',
                'result' => []
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'result' => []
            ]);
        }
    }

    public function getEmployeeData(Request $request)
    {
        try {
            $user = Auth::user();

            // Optional pagination (safe for large datasets)
            $limit = $request->input('limit', 10000);
            $offset = $request->input('offset', 0);

            // Use Eloquent with only needed columns and filters
            $employees = Employee::select('emp_b_id', 'emp_code', 'emp_full_name', 'emp_phone', 'emp_email', 'emp_profile_photo', 'emp_profile_s3_url')
                ->where('emp_b_id', $user->emp_b_id)
                ->where('emp_status', 71)
                ->whereNotIn('emp_role_id', [0, 1])
                ->skip($offset)
                ->take($limit)
                ->get();

            return response()->json([
                'result' => $employees,
                'status' => true,
                'message' => 'Data fetched successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => [],
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function uploadEmpImageByAdmin(Request $request)
    {
        $request->validate([
            'image' => 'required|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'emp_code' => 'required|string',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized.'
            ], 401);
        }

        $employee = Employee::where('emp_code', $request->emp_code)->first();
        if (!$employee || !$employee->fh_business) {
            return response()->json([
                'status' => false,
                'message' => 'Employee or business not found.'
            ], 404);
        }

        $file = $request->file('image');

        // Generate unique filename
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $filename = time() . '_' . md5($originalName) . '.' . $extension;
        $imagePath = $employee->fh_business->b_unique_id . '/' . $filename;

        $shouldStoreOnS3 = env('STORE_ON_S3', false);
        $bucket = 'fixhr-employee-profiles';
        $profile_url = null;

        // ✅ Delete old image if exists
        if (!empty($employee->emp_profile_photo)) {
            try {
                if ($shouldStoreOnS3) {
                    $parsedUrl = parse_url($employee->emp_profile_photo, PHP_URL_PATH);
                    $key = ltrim($parsedUrl, '/');
                    $this->awsHelper->deleteFileFromS3($bucket, $key);
                } else {
                    $oldPath = str_replace(url('storage/'), '', $employee->emp_profile_photo);
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Old profile delete failed: ' . $e->getMessage());
            }
        }

        // Upload and index face on AWS Rekognition
        if (is_null($employee->emp_rekognition_id) && $file && in_array(316, $employee->emp_checkin_method_id)) {
            $awsResponse = app('App\Http\Controllers\FaceController')->uploadAndIndexFace($employee, $file, $filename);
        }

        // ✅ Upload file
        try {
            if ($shouldStoreOnS3) {
                $uploadResult = $this->awsHelper->uploadFileToS3($bucket, $imagePath, $file);
                if (!empty($uploadResult['status'])) {
                    $profile_url = $uploadResult['ObjectURL'];
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Failed to upload to S3.'
                    ], 500);
                }
            } else {
                $storagePath = 'employee_profile/' . $employee->fh_business->b_unique_id;
                $path = $file->storeAs($storagePath, $filename, 'public');
                $profile_url = asset('storage/' . $path);
            }
        } catch (\Exception $e) {
            Log::error('File upload failed: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'File upload failed: ' . $e->getMessage()
            ], 500);
        }

        // ✅ Update employee record
        $employee->update([
            'emp_profile_photo' => $profile_url,
        ]);

        return response()->json([
            'result' => [
                ['file_url' => $profile_url]
            ],
            'status' => true,
            'message' => 'Image uploaded successfully.',
        ]);
    }

    public function reminderStatus(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->emp_status == 72) {
            return response()->json([
                'status' => false,
                'message' => 'Inactive employee or employee not found.',
                'data' => [],
            ], 200);
        }

        if ($user && $user->emp_role_id == 1) {
            return response()->json([
                'status' => false,
                'message' => 'Cannot show for super admin.',
                'data' => [],
            ], 200);
        }

        $employee = Employee::with('fh_business')->find($user->emp_id);
        $today = $request->reminder_date;
        $now = now();
        $leavePolicy = PolicyLeave::where('pl_b_id', $employee->emp_b_id)->where('pl_limit_check', 1)->first();

        // Business reminder OFF
        if ($user->emp_is_reminder_enabled != 1) {
            return response()->json([
                'status' => false,
                'message' => 'Reminder inactive.',
                'data' => [],
            ], 200);
        }

        $weekOffDates = CentralLogics::getWeekOffDates($employee, null, null, $today, $today);
        // Get holidays
        $holiday_record_exits = PolicyHolidayList::where('phl_b_id', $user->emp_b_id)->where('phl_day_type_id', 201)->where(function ($q) use ($today) {
            $q->whereBetween('phl_start_date', [$today, $today])
              ->orWhereBetween('phl_end_date', [$today, $today]);
        })->get();
        
        $holidaysByDate = collect();
        foreach ($holiday_record_exits as $holiday) {
            $start = Carbon::parse($holiday->phl_start_date);
            $end = Carbon::parse($holiday->phl_end_date);

            while ($start->lte($end)) {
                $holidaysByDate->put($start->toDateString(), $holiday);
                $start->addDay();
            }
        }

        $isWeekOff = in_array($today, $weekOffDates);
        $isHoliday = $holidaysByDate->has($today);

        if ($isHoliday || $isWeekOff) {
            return response()->json([
                'status' => false,
                'message' => 'today is weekoff or holiday.',
                'data' => [],
            ], 200);
        }

        $resolvedShift = ShiftResolver::resolveEmployeeShift($employee, $today);
        $shift = $resolvedShift->shift ?? PolicyShiftTiming::find($employee->emp_shift_type_id);
        if (!$shift) {
            return response()->json([
                'status' => false,
                'message' => 'shift not found.',
                'data' => [],
            ], 200);
        }

        $hasAttendance = AttendanceRecord::where('atd_emp_id', $employee->emp_id)
            ->where('atd_date', $today)
            ->exists();

        $log = AttendanceLog::where('al_emp_id', $employee->emp_id)
            ->where('al_date', $today)
            ->exists();

        $hasLeave = LeaveRequest::where('lvr_emp_id', $employee->emp_id)
            ->whereDate('lvr_start_date', '<=', $today)
            ->whereDate('lvr_end_date', '>=', $today)
            ->exists();

        $previousDate = Carbon::parse($today)->subDay()->toDateString();
        $logAttendance = AttendanceRecord::where('atd_emp_id', $employee->emp_id)
            ->where('atd_date', $previousDate)
            ->where(function ($q) {
                $q->whereNull('atd_check_in_time')
                  ->orWhereNull('atd_check_out_time');
            })
            ->exists();

        if ($logAttendance ) {
            $mspLog = AttendanceLog::where('al_emp_id', $employee->emp_id)
                ->where('al_date', $previousDate)
                ->where(function ($q) {
                    $q->whereNull('al_check_in_time')
                      ->orWhereNull('al_check_out_time');
                })
                ->exists();

            $missedPunch = AttendanceException::where('ae_emp_id', $employee->emp_id)
                ->where('ae_date', $previousDate)
                ->where(function ($q) {
                    $q->whereNull('ae_in_time')
                      ->orWhereNull('ae_out_time');
                })
                ->exists();
            if ($mspLog && !isset($missedPunch)) {
                return response()->json([
                    'status' => true,
                    'message' => 'show_reminder',
                    'data' => [
                        [
                            'date' => $today,
                            'message' => 'Previous day attendance is incomplete. Please update it.',
                        ]
                    ],
                ]);
            }
        }

        if ($hasAttendance || $hasLeave || $log) {
            return response()->json([
                'status' => false,
                'message' => 'attendance or leave marked.',
                'data' => [],
            ], 200);
        }

        $shiftReminder = Carbon::parse($shift->pst_start_time)->addHour()->format('Y-m-d H:i:s');

        if ($now->greaterThanOrEqualTo($shiftReminder)) {
            return response()->json([
                'status' => true,
                'message' => 'show_reminder',
                'data' => [
                    [
                        'date' => $today,
                        'message' => 'You have not marked your attendance or leave today.'
                    ]
                ],
            ]);
        }

        if ($shift->pst_allow_break1 == 1) {
            $lunchReminder = Carbon::parse($shift->pst_break_begin_time1)->addHour()->format('Y-m-d H:i:s');
            if ($now->greaterThanOrEqualTo($lunchReminder)) {
                return response()->json([
                'status' => true,
                    'message' => 'show_reminder',
                    'data' => [
                        [
                            'date' => $today,
                            'message' => 'You have not marked your attendance or leave today.'
                        ]
                    ],
                ]);
            }
        }

        return response()->json([
            'show_reminder' => false,
            'status' => false,
        ]);
    }

    public function reminderStatusNew(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->emp_status == 72) {
            return response()->json(['status' => false, 'message' => 'Inactive Employee', 'data' => []]);
        }

        $employee = Employee::with('fh_business')->find($user->emp_id);
        $today = $request->reminder_date ?? Carbon::today()->toDateString();
        $now = now();

        /* ================= Business Check ================= */
        if (!$employee->fh_business || $employee->fh_business->is_reminder != 1) {
            return response()->json(['status' => false, 'message' => 'Reminder disabled', 'data' => []]);
        }

        /* ================= Leave Policy ================= */
        $leavePolicy = PolicyLeave::where('pl_b_id', $employee->emp_b_id)
            ->where('pl_limit_check', 1)
            ->first();

        $canApplyLeaveForDate = function ($date) use ($leavePolicy) {
            if (!$leavePolicy || !$leavePolicy->pl_limit_before) {
                return false;
            }
            return now()->lte(Carbon::parse($date)->addDays($leavePolicy->pl_limit_before));
        };

        /* ================= WeekOff / Holiday ================= */
        $weekOffDates = CentralLogics::getWeekOffDates($employee, null, null, $today, $today);

        $holidayExists = PolicyHolidayList::where('phl_b_id', $employee->emp_b_id)
            ->where('phl_day_type_id', 201)
            ->whereDate('phl_start_date', '<=', $today)
            ->whereDate('phl_end_date', '>=', $today)
            ->exists();

        if (in_array($today, $weekOffDates) || $holidayExists) {
            return response()->json(['status' => false, 'message' => '', 'data' => []]);
        }

        /* ================= Shift ================= */
        $resolvedShift = ShiftResolver::resolveEmployeeShift($employee, $today);
        $shift = $resolvedShift->shift ?? PolicyShiftTiming::find($employee->emp_shift_type_id);

        if (!$shift) {
            return response()->json(['status' => false, 'message' => 'Shift not found', 'data' => []]);
        }

        /* ================= TODAY CHECKS ================= */
        $hasAttendance = AttendanceRecord::where('atd_emp_id', $employee->emp_id)
            ->where('atd_date', $today)
            ->exists();

        $hasLog = AttendanceLog::where('al_emp_id', $employee->emp_id)
            ->where('al_date', $today)
            ->exists();

        $hasLeave = LeaveRequest::where('lvr_emp_id', $employee->emp_id)
            ->whereDate('lvr_start_date', '<=', $today)
            ->whereDate('lvr_end_date', '>=', $today)
            ->exists();

        /* ================= PREVIOUS DAY MSP ================= */
        $previousDate = Carbon::parse($today)->subDay()->toDateString();

        $incompleteAttendance = AttendanceRecord::where('atd_emp_id', $employee->emp_id)
            ->where('atd_date', $previousDate)
            ->where(function ($q) {
                $q->whereNull('atd_check_in_time')
                  ->orWhereNull('atd_check_out_time');
            })
            ->exists();

        if ($incompleteAttendance && !$canApplyLeaveForDate($previousDate)) {

            $missedPunch = AttendanceException::where('ae_emp_id', $employee->emp_id)
                ->where('ae_date', $previousDate)
                ->exists();

            if (!$missedPunch) {
                return response()->json([
                    'status' => true,
                    'message' => 'show_reminder',
                    'data' => [
                        [
                            'date' => $previousDate,
                            'message' => 'Your attendance for the previous day is incomplete. Please update it.'
                        ]
                    ],
                ]);
            }
        }

        /* ================= LAST N DAYS (GROUP LOGIC) ================= */
        if ($leavePolicy && $leavePolicy->pl_limit_check == 1 && $leavePolicy->pl_limit_before > 0) {

            $missingDates = [];
            $days = $leavePolicy->pl_limit_before;

            for ($i = 1; $i <= $days; $i++) {
                $checkDate = Carbon::parse($today)->subDays($i)->toDateString();

                $att = AttendanceRecord::where('atd_emp_id', $employee->emp_id)
                    ->where('atd_date', $checkDate)
                    ->exists();

                $lev = LeaveRequest::where('lvr_emp_id', $employee->emp_id)
                    ->whereDate('lvr_start_date', '<=', $checkDate)
                    ->whereDate('lvr_end_date', '>=', $checkDate)
                    ->exists();

                if (!$att && !$lev) {
                    $missingDates[] = $checkDate;
                }
            }

            if (!empty($missingDates)) {

                sort($missingDates);
                $groups = [];
                $group = [$missingDates[0]];

                for ($i = 1; $i < count($missingDates); $i++) {
                    if (Carbon::parse($missingDates[$i - 1])->addDay()->eq($missingDates[$i])) {
                        $group[] = $missingDates[$i];
                    } else {
                        $groups[] = $group;
                        $group = [$missingDates[$i]];
                    }
                }
                $groups[] = $group;

                $latestGroup = collect($groups)->sortByDesc(fn ($g) => end($g))->first();

                $start = reset($latestGroup);
                $end = end($latestGroup);

                $rangeText = ($start === $end) ? $start : "{$start} to {$end}";

                return response()->json([
                    'status' => true,
                    'message' => 'show_reminder',
                    'data' => [
                        [
                            'date' => $end,
                            'message' => "Attendance or leave has not been marked for {$rangeText}. Please update it."
                        ]
                    ],
                ]);
            }
        }

        // dd($hasAttendance, $hasLeave, $hasLog, $canApplyLeaveForDate($today));

        /* ================= TODAY REMINDER ================= */
        if (!$hasAttendance && !$hasLeave && !$hasLog && !$canApplyLeaveForDate($today)) {

            $shiftReminderTime = Carbon::parse($shift->pst_start_time)->addHour();

            if ($now->gte($shiftReminderTime)) {
                return response()->json([
                    'status' => true,
                    'message' => 'show_reminder',
                    'data' => [
                        [
                            'date' => $today,
                            'message' => 'Your attendance has not been marked today, and no leave has been applied.'
                        ]
                    ],
                ]);
            }

            if ($shift->pst_allow_break1 == 1) {
                $lunchReminderTime = Carbon::parse($today . ' ' . $shift->pst_break_begin_time1)->addHour();

                if ($now->gte($lunchReminderTime)) {
                    return response()->json([
                        'status' => true,
                        'message' => 'show_reminder',
                        'data' => [
                            [
                                'date' => $today,
                                'message' => 'Your attendance has not been marked today, and no leave has been applied.'
                            ]
                        ],
                    ]);
                }
            }
        }

        return response()->json(['status' => false, 'message' => 'Not working', 'data' => []]);
    }

    public function getEmployeeDataByCode($code)
    {
        $employee = Employee::select(
            'emp_id',
            'emp_full_name',
            'emp_email',
            'emp_phone'
        )->where('emp_code', $code)->first();

        if (!$employee) {
            return response()->json([
                'status' => false,
                'message' => 'No employee found for this code.',
                'result' => null,
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Employee found successfully.',
            'result' => $employee,
        ]);
    }

    public function approvalPendingReminder(Request $request)
    {
        try {

            $user = Auth::user();

            // User Validation
            if (!$user || $user->emp_status == 72) {
                return response()->json([
                    'status' => false,
                    'message' => 'Inactive employee or employee not found.',
                    'data' => []
                ], 200);
            }

            if ($user && $user->emp_b_id != 66) {
                return response()->json([
                    'status' => false,
                    'message' => 'Business not applicable.',
                    'data' => []
                ], 200);
            }

            // Super Admin Check
            if ($user->emp_role_id == 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cannot show for super admin.',
                    'data' => []
                ], 200);
            }

            // Reminder Enable Check
            if ($user->emp_is_reminder_enabled != 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'Reminder disabled.',
                    'data' => []
                ], 200);
            }

            $businessId = $user->emp_b_id;
            $employeeId = $user->emp_id;

            $response = [];
            $pendingStatusId = 140;

            // Module List
            $modules = [
                145 => 'Travel Request',
                146 => 'Claim Request',
                199 => 'Advance Request',
                229 => 'Missed Punch',
                250 => 'Leave Request',
                339 => 'Gate Pass',
                442 => 'Loan Request',
                562 => 'OT Request',
            ];

            foreach ($modules as $moduleId => $moduleName) {

                $count = 0;

                // =====================================================
                // EMPLOYEE WISE FLOW
                // =====================================================

                $employeeWiseExists = EmployeeApprovalMapping::where('eam_b_id', $businessId)
                    ->where('eam_module_id', $moduleId)
                    ->exists();

                if ($employeeWiseExists) {
                    //Get requester Ids
                    $mappingIds = EmployeeApprovalMapping::where('eam_b_id', $businessId)
                        ->where('eam_module_id', $moduleId)
                        ->pluck('eam_emp_id');

                    //Get approver Ids
                    $pendingEamIds = EmployeeApprovalStatus::whereIn('eas_eam_id', $mappingIds)
                        ->where('eas_approvel_id', $employeeId)
                        ->pluck('eas_eam_id');

                    //Leave
                    if ($moduleId == 250) {
                        $leaveRequests = LeaveRequest::whereIn('lvr_emp_id', $mappingIds)
                            ->where('lvr_stage_completed', '!=', 1)
                            ->where('lvr_status', $pendingStatusId)
                            ->get();

                        $count = $leaveRequests->count();
                    }

                    //Travel
                    elseif ($moduleId == 145) {
                        $count = TadaRequestPlan::whereIn('trp_emp_id', $mappingIds)
                            ->where('trp_stage_completed', '!=', 1)
                            ->where('trp_request_status', $pendingStatusId)
                            ->count();
                    }

                    //Claim
                    elseif ($moduleId == 146) {
                        $count = TadaClaim::whereIn('tc_emp_id', $mappingIds)
                            ->where('tc_stage_completed', '!=', 1)
                            ->where('tc_status', $pendingStatusId)
                            ->count();
                    }

                    //Advance
                    elseif ($moduleId == 199) {

                        $count = AdvanceLog::whereIn('adl_emp_id', $mappingIds)
                            ->where('adl_stage_completed', '!=', 1)
                            ->where('adl_request_status', $pendingStatusId)
                            ->count();
                    }

                    //Missed Punch
                    elseif ($moduleId == 229) {
                        $count = AttendanceException::whereIn('ae_emp_id', $mappingIds)
                            ->where('ae_stage_completed', '!=', 1)
                            ->where('ae_status', $pendingStatusId)
                            ->count();
                    }

                    //Gate Pass
                    elseif ($moduleId == 339) {
                        $count = GatePass::whereIn('gtp_emp_id', $mappingIds)
                            ->where('gtp_stage_completed', '!=', 1)
                            ->where('gtp_status', $pendingStatusId)
                            ->count();
                    }

                    //Loan
                    elseif ($moduleId == 442) {
                        $count = LoanRequest::whereIn('lnr_emp_id', $mappingIds)
                            ->where('lnr_stage_completed', '!=', 1)
                            ->where('lnr_request_status', $pendingStatusId)
                            ->count();
                    }

                    //Overtime
                    elseif ($moduleId == 562) {
                        $count = OtApprovalStatus::whereIn('ot_emp_id', $mappingIds)
                            ->where('ot_stage_completed', '!=', 1)
                            ->where('ot_requested_status', $pendingStatusId)
                            ->count();
                    }
                }

                // =====================================================
                // HIERARCHY WISE FLOW
                // =====================================================

                else {

                    $approvalModule = ApprovalModule::where('am_b_id', $businessId)
                        ->where('am_module_id', $moduleId)
                        ->first();

                    if ($approvalModule) {

                        $isApprover = ProcessApprover::where('pa_am_id', $approvalModule->am_id)
                            ->where('pa_emp_id', $employeeId)
                            ->exists();

                        if ($isApprover) {

                            //Leave
                            if ($moduleId == 250) {
                                $count = LeaveRequest::where('lvr_b_id', $businessId)
                                    ->where('lvr_status', $pendingStatusId)
                                    ->where('lvr_stage_completed', '!=', 1)
                                    ->count();
                            }

                            //Travel
                            elseif ($moduleId == 145) {
                                $count = TadaRequestPlan::where('trp_b_id', $businessId)
                                    ->where('trp_request_status', $pendingStatusId)
                                    ->where('trp_stage_completed', '!=', 1)
                                    ->count();
                            }

                            //Claim
                            elseif ($moduleId == 146) {
                                $count = TadaClaim::where('tc_b_id', $businessId)
                                    ->where('tc_status', $pendingStatusId)
                                    ->where('tc_stage_completed', '!=', 1)
                                    ->count();
                            }

                            //Advance
                            elseif ($moduleId == 199) {
                                $count = AdvanceLog::where('adl_b_id', $businessId)
                                    ->where('adl_request_status', $pendingStatusId)
                                    ->where('adl_stage_completed', '!=', 1)
                                    ->count();
                            }

                            //Missed Punch
                            elseif ($moduleId == 229) {
                                $count = AttendanceException::where('ae_b_id', $businessId)
                                    ->where('ae_status', $pendingStatusId)
                                    ->where('ae_stage_completed', '!=', 1)
                                    ->count();
                            }

                            //Gate Pass
                            elseif ($moduleId == 339) {
                                $count = GatePass::where('gtp_b_id', $businessId)
                                    ->where('gtp_status', $pendingStatusId)
                                    ->where('gtp_stage_completed', '!=', 1)
                                    ->count();
                            }

                            //Loan
                            elseif ($moduleId == 442) {
                                $count = LoanRequest::where('lnr_b_id', $businessId)
                                    ->where('lnr_request_status', $pendingStatusId)
                                    ->where('lnr_stage_completed', '!=', 1)
                                    ->count();
                            }

                            //Overtime
                            elseif ($moduleId == 562) {
                                $count = OtApprovalStatus::where('ot_b_id', $businessId)
                                    ->where('ot_requested_status', $pendingStatusId)
                                    ->where('ot_stage_completed', '!=', 1)
                                    ->count();
                            }
                        }
                    }
                }

                if ($count > 0) {
                    $response[] = [
                        'module_id' => $moduleId,
                        'module_name' => $moduleName,
                        'pending_count' => $count,
                        'message' => "{$count} {$moduleName} pending for approval."
                    ];
                }
            }

            if ($user->emp_remind_me_later === 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'Do not  remind me later',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'status' => count($response) > 0,
                'message' => count($response) > 0
                    ? 'Pending approvals found.'
                    : 'No pending approvals found.',
                'total_modules' => count($response),
                'key' => 'APPROVAL',
                'data' => $response
            ], 200);

        } catch (\Throwable $th) {

            \Log::error("Approval reminder API failed : " . $th->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong.',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public static function approvalReminderToggleVisible()
    {
        try {

            $user = Auth::user();

            if (!$user || $user->emp_status == 72) {
                return [
                    'show_toggle' => false,
                ];
            }

            if ($user || $user->emp_b_id != 66) {
                return [
                    'show_toggle' => false,
                ];
            }

            $businessId = $user->emp_b_id;
            $employeeId = $user->emp_id;

            $hasApproval = false;

            $modules = [
                145 => 'Travel Request',
                146 => 'Claim Request',
                199 => 'Advance Request',
                229 => 'Missed Punch',
                250 => 'Leave Request',
                339 => 'Gate Pass',
                442 => 'Loan Request',
                562 => 'OT Request',
            ];

            foreach ($modules as $moduleId => $moduleName) {

                // Employee Wise
                $employeeWiseExists = EmployeeApprovalMapping::where('eam_b_id', $businessId)
                    ->where('eam_module_id', $moduleId)
                    ->exists();

                if ($employeeWiseExists) {

                    $isApprover = EmployeeApprovalStatus::where('eas_approvel_id', $employeeId)
                        ->whereHas('employee_approval_mapping', function ($q) use ($businessId, $moduleId) {

                            $q->where('eam_b_id', $businessId)
                                ->where('eam_module_id', $moduleId);

                        })
                        ->exists();

                    if ($isApprover) {
                        $hasApproval = true;
                        break;
                    }
                }

                // Hierarchy Wise
                else {

                    $approvalModule = ApprovalModule::where('am_b_id', $businessId)
                        ->where('am_module_id', $moduleId)
                        ->first();

                    if ($approvalModule) {

                        $isApprover = ProcessApprover::where('pa_am_id', $approvalModule->am_id)
                            ->where('pa_emp_id', $employeeId)
                            ->exists();

                        if ($isApprover) {
                            $hasApproval = true;
                            break;
                        }
                    }
                }
            }

            return [
                'show_toggle' => $hasApproval,
            ];

        } catch (\Throwable $th) {

            \Log::error("Approval Reminder Toggle Failed : " . $th->getMessage());

            return [
                'show_toggle' => false,
            ];
        }
    }

    public function updateRemindMeLater(Request $request)
    {
        try {
            $request->validate([
                'emp_remind_me_later' => 'required|in:0,1'
            ]);

            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Employee not found',
                    'result' => false
                ]);
            }

            if ($user || $user->emp_b_id != 66) {
                return response()->json([
                    'status' => false,
                    'message' => 'Business not applicable.',
                    'data' => []
                ], 200);
            }

            $user->emp_remind_me_later = $request->emp_remind_me_later;
            $user->save();
            return response()->json([
                'status' => true,
                'message' => 'Remind me later updated successfully',
                'result' => true
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
                'result' => false
            ]);
        }
    }

    protected function getAutoAssignedShift($employee, $date)
    {
        $resolvedShift = ShiftResolver::resolveEmployeeShiftforUser($employee, $date);
        $shift = $resolvedShift->shift ?? PolicyShiftTiming::find($employee->emp_shift_type_id);
        
        if ($shift) {
            return PolicyShiftTimeResource::collection(collect([$shift]));
        }
        
        return PolicyShiftTimeResource::collection([]);
    }
}
