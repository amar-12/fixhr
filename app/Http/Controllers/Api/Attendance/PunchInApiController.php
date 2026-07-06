<?php

namespace App\Http\Controllers\Api\Attendance;

use App\Helpers\ApprovalHelper;
use App\Helpers\CentralLogics;
use App\Models\AttendanceSession;
use App\Models\AutomationRule;
use ChandraHemant\HtkcUtils\CommonUtils;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\AttendanceRequest;
use App\Http\Resources\Attendance\AttendanceExceptionResource;
use App\Http\Resources\Attendance\AttendanceResource;
use App\Http\Resources\Attendance\AttendanceOutDoorResource;
use App\Http\Resources\DesignationResource;
use App\Http\Resources\MasterTableResource;
use App\Models\AttendanceException;
use App\Models\PolicyAttendance;
use App\Models\AttendanceRecord;
use App\Models\AttendanceOutDoor;
use App\Models\AttendancePunchLog;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\MasterTable;
use App\Models\PolicyHolidayList;
use App\Models\PolicyShiftTiming;
use App\Models\OvertimePolicy;
use App\Models\OtApprovalStatus;
use App\Models\ProcessApprover;
use App\Models\RuleCriterion;
use Carbon\Carbon;
use ChandraHemant\HtkcUtils\FirebaseNotification;
use ChandraHemant\HtkcUtils\PaginatedResource;
use ChandraHemant\HtkcUtils\ReturnHelper;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use function PHPUnit\Framework\isEmpty;
use App\Helpers\Aws\AwsHelper;
use App\Helpers\NotificationHelper;
use App\Helpers\ShiftResolver;
use App\Models\AttendanceLog;
use App\Models\CompOff;
use App\Models\CompOffBalance;
use App\Models\CompOffPolicy;
use Illuminate\Support\Facades\DB;
use App\Models\UserDevice;
use Illuminate\Support\Facades\Log;
use App\Models\OfflineDeviceRegistration;
use App\Models\DeviceManagement;
use App\Models\SelfieVerify;

class PunchInApiController extends Controller
{
    protected $awsHelper;

    public function __construct(AwsHelper $awsHelper)
    {
        if (env('STORE_ON_S3')) {
            $this->awsHelper = $awsHelper;
        }
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $month = $request->month ??  Carbon::now()->month;
        $year = $request->year ??  Carbon::now()->year;
        $attendance = AttendanceRecord::where('atd_emp_id', $user->emp_id)
            ->whereMonth('atd_date', $month)
            ->whereYear('atd_date', $year)
            ->orderBy('atd_date', 'desc')
            ->get();

        if ($attendance->isNotEmpty()) {
            return ReturnHelper::jsonApiReturn(AttendanceResource::collection($attendance));
        }

        return response()->json(['result' => [], 'status' => false]);
    }

    public function getAttendanceByDate(Request $request)
    {
        $user = Auth::user();
        $dateInput = $request->get('date');
        if (!$dateInput) {
            return response()->json(['message' => 'Date field is required', 'status' => false], 400);
        }

        try {
            $parsedDate = Carbon::createFromFormat('d-M-Y', $dateInput)->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid date format. Use "23-Nov-2024"', 'status' => false], 400);
        }

        $attendance = AttendanceRecord::where('atd_emp_id', $user->emp_id)
            ->whereDate('created_at', $parsedDate)
            ->get();

        if ($attendance->isNotEmpty()) {
            return ReturnHelper::jsonApiReturn(AttendanceResource::collection($attendance));
        }

        return response()->json(['result' => [], 'status' => false]);
    }


    public function getTodayAttendance()
    {
        $user = Auth::user();

        $today = now()->toDateString();
        $attendance = AttendanceRecord::where('atd_emp_id', $user->emp_id)
            ->whereDate('atd_date', $today)
            ->get();
        if ($attendance->isNotEmpty()) {
            return ReturnHelper::jsonApiReturn(AttendanceResource::collection($attendance));
        }
        return response()->json(['result' => [], 'status' => true]);
    }

    public function store(Request $request)
    {
        // dd("hello");
        try {
            $checkinMethodId = $request->input('atd_checkin_method_id');
            if (is_null($checkinMethodId)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Check-in method is required'
                ], 400);
            }
            $user = Auth::user();
            $amId = null;
            $ruleCriteria = RuleCriterion::with('fh_approval_module')
                ->where('rc_b_id', $user->emp_b_id)
                ->where('rc_condition_option_id', 140)
                ->whereHas('fh_approval_module', function ($query) {
                    $query->where('am_module_id', 249)
                        ->where('am_status', 1);
                })->first();
            $processApprovers = [];

            // Ensure $ruleCriteria exists before accessing the relationship
            if ($ruleCriteria && $ruleCriteria->fh_approval_module) {
                // Fetch filtered process approvers using emp_d_id
                $processApprovers = $ruleCriteria->fh_approval_module
                    ->filteredProcessApprovers($user->emp_b_id)
                    ->get(); // Fetch the filtered data
            }

            if (count($processApprovers)) {
                $amId = $ruleCriteria->rc_am_id;
            } else {
                $approvalMapping = ApprovalHelper::getApprovalMapping($user->emp_b_id, $user->emp_id, 249);
                if (!$approvalMapping) {
                    return response()->json(['result' => [], 'status' => false, 'message' => 'Sorry! not found any approval settings for attendance module, contact hr.']);
                }
            }

            $request->merge([
                'am_id' => $amId,
            ]);

            $atd_date = $request->atd_check_in_date ? Carbon::parse($request->atd_check_in_date)->format('Y-m-d') : now()->format('Y-m-d');
            $isHoliday = PolicyHolidayList::where('phl_b_id', $user->emp_b_id)->whereDate('phl_start_date', '<=', $atd_date)->whereDate('phl_end_date', '>=', $atd_date)->first();
            if ($isHoliday && $isHoliday->phl_type_id == 205 && $isHoliday->phl_day_type_id == 201) {
                return response()->json(['status' => false, 'result' => [], 'message' => "You can't check in today because it's a public holiday."]);
            }

            $leaveApplied =  LeaveRequest::where('lvr_emp_id', $user->emp_id)->whereDate('lvr_start_date', '<=', now()->format('Y-m-d'))->whereDate('lvr_end_date', '>=', now()->format('Y-m-d'))->where('lvr_status', '!=', 170)->where('lvr_stage_completed', 1)->first();
            if ($leaveApplied && $leaveApplied->fh_leave_day_type->m_id == 201) { //201=='Full Day'
                return response()->json(['status' => false, 'result' => [], 'message' => "You can't check in due to you have applied leave for this date. Contact HR or your manager for assistance."]);
            } elseif ($leaveApplied && $leaveApplied->fh_leave_day_segment->m_id == 235) { //235==First Half

            } elseif ($leaveApplied && $leaveApplied->fh_leave_day_segment->m_id == 236) { //236==Second Half

            }

            switch ($checkinMethodId) {
                case 317: //QR
                    return $this->handleCheckinWithQr($request);
                case 318: //PhoneBoimetric/
                    return $this->handleCheckinWithBoimetric($request);
                case 314: //SELFIE
                    return $this->handleCheckinWithPhoto($request);
                default:
                    return response()->json(['status' => false, 'message' => 'Invalid check-in method'], 400);
            }
        } catch (Exception $e) {
            return response()->json(['result' => [], 'status' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function handleCheckinWithQr(Request $request)
    {
        $user = Auth::user();

        $newSegment = $request->input('atd_segments');

        // Validate the input
        if (!is_array($newSegment) || empty($newSegment['name']) || empty($newSegment['emp_code'])) {
            return response()->json(['status' => false, 'message' => 'Invalid or missing atd_segments data'], 400);
        }
        $today = now()->format('Y-m-d');
        $currentTime = now()->format('Y-m-d H:i:s');

        $employee = Employee::where('emp_id', $request->emp_id)->first();
        if ($user->emp_b_id !== $employee->emp_b_id) {
            return response()->json([
                'status' => false, 
                'message' => "One business approval can’t mark another’s attendance"
            ], 200);
        }

        // Check if attendance record exists
        $attendance = AttendanceRecord::firstOrCreate(
            [
                'atd_b_id' => $user->emp_b_id,
                'atd_emp_id' => $request->emp_id,
                'atd_date' => $today,
            ],
            [
                'atd_am_id' => $request->input('am_id'),
                'atd_work_mode_type_id' => $request->input('atd_work_mode_type_id'),
                'atd_checkin_method_id' => $request->input('atd_checkin_method_id'),
                'atd_device_id' => $request->input('atd_device_id'),
            ]
        );

        // Handle first-time check-in scenario
        if ($attendance->wasRecentlyCreated) {
            $attendance->atd_check_in_time = $currentTime;
            $attendance->atd_segments = json_encode($newSegment);
            $type = 'check-in';
        } else {
            $attendance->atd_check_out_time = $currentTime;
            $attendance->atd_segments = json_encode($newSegment);
            $type = 'check-out';
        }
        $this->handleAttendance($attendance, $user, $type);
        $attendance->refresh();

        return ReturnHelper::jsonApiReturn(AttendanceResource::collection([$attendance]));
    }

    private function handleCheckinWithBoimetric(Request $request)
    {
        $user = Auth::user();
        $deviceId = $request->input('atd_device_id');
        $empCode = $user->emp_code;
        $employeeId = $user->emp_id;
        $today = now()->format('Y-m-d');
        $currentTime = now()->format('Y-m-d H:i:s');

        // Validate input
        if (empty($deviceId)) {
            return response()->json(['status' => false, 'message' => 'Device ID is required for biometric punching'], 400);
        }

        // Match device using emp_code from UserDevice model
        $userDevice = UserDevice::where('ud_emp_code', $empCode)
            ->where('ud_device_id', $deviceId)
            ->first();

        if (!$userDevice) {
            return response()->json(['status' => false, 'message' => 'Device is not registerd for biometric punching'], 404);
        }
        if (!$userDevice->ud_status) {
            return response()->json(['status' => false, 'message' => 'Device is not verified. Please verify your device before punching.'], 403);
        }

        // Check if attendance record exists for today
        $attendance = AttendanceRecord::firstOrCreate(
            [
                'atd_emp_id' => $employeeId,
                'atd_date' => $today,
                'atd_b_id' => $user->fh_business->b_id,
            ],
            [
                'atd_am_id' => $request->input('am_id'),
                'atd_work_mode_type_id' => $request->input('atd_work_mode_type_id'),
                'atd_checkin_method_id' => $request->input('atd_checkin_method_id'),
                'atd_device_id' => $deviceId,
            ]
        );

        // Handle first-time check-in or check-out
        if ($attendance->wasRecentlyCreated) {
            $attendance->atd_check_in_time = $currentTime;
            $attendance->atd_segments = json_encode([
                'device_id' => $deviceId,
                'method' => 'biometric',
            ]);
            $type = 'check-in';
        } else {
            if (empty($attendance->atd_check_in_time)) {
                $attendance->atd_check_in_time = $currentTime;
                $attendance->atd_segments = json_encode([
                    'device_id' => $deviceId,
                    'method' => 'biometric',
                ]);
                $type = 'check-in';
            } else {
                $attendance->atd_check_out_time = $currentTime;
                $attendance->atd_segments = json_encode([
                    'device_id' => $deviceId,
                    'method' => 'biometric',
                ]);
                $type = 'check-out';
            }
        }
        $this->handleAttendance($attendance, $user, $type);
        $attendance->refresh();

        return ReturnHelper::jsonApiReturn(AttendanceResource::collection([$attendance]));
    }

    private function handleCheckinWithPhoto(Request $request)
    {
        $user = Auth::user();
        // Determine check-in date and time from request or fallback to now()
        $currentDate = $request->atd_check_in_date ?? now()->format('Y-m-d');
        $currentTime = $request->atd_check_in_time ?? now()->format('H:i:s');
        $checkInDateTime = $currentDate . ' ' . $currentTime;
        $dayName = Carbon::parse($currentDate)->format('l');

        /*if ($user?->fh_attendance_policy?->ap_is_selfie_restricted == 1) {
            $existingDevice = SelfieVerify::where('sv_device_id', $request->atd_device_id)
            ->where('sv_emp_b_id', $user->emp_b_id)
            ->first();

            if (!$existingDevice) {
                return response()->json([
                    'message' => 'This device is not registered. Please register your device first.',
                    'status' => false,
                    'result' => []
                ], 200);
            }

            if ($existingDevice->sv_emp_id != $user->emp_id) {
                $name = trim(preg_replace('/\s+/', ' ', $existingDevice?->employee?->emp_full_name));
                return response()->json([
                    'message' => 'This device is already being used by another user (' . $name . ')',
                    'status' => false,
                    'result' => []
                ], 200);
            }

            if ($existingDevice->sv_status == SelfieVerify::STATUS_PENDING) {
                return response()->json([
                    'message' => 'Your device verification is still pending. Please contact to HR.',
                    'status' => false,
                    'result' => []
                ], 200);
            }

            if ($existingDevice->sv_status == SelfieVerify::STATUS_REJECTED) {
                return response()->json([
                    'message' => 'Your device verification has been rejected. Please contact to HR.',
                    'status' => false,
                    'result' => []
                ], 200);
            }
        }*/

        $uploadedPhotos = [];
        if (env('STORE_ON_S3')) { //upload file to AWS S3 storage.
            $bucket = 'fixhr-uploads';
            if ($request->atd_punchin_photo != '' && $request->atd_punchin_photo != NULL && $request->atd_punchin_photo != []) {
                foreach ($request->atd_punchin_photo as $file) {
                    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $extension = $file->getClientOriginalExtension();
                    $filename = time() . '_' . md5($originalName) . '.' . $extension;
                    $imagePath = 'AttendancePhoto/' . $user->fh_business->b_unique_id . '/' . $filename;
                    $uploadResult = $this->awsHelper->uploadFileToS3($bucket, $imagePath, $file);
                    if ($uploadResult['status']) {
                        $uploadedPhotos[] = $uploadResult['ObjectURL'];
                    }
                }
            }
        } else { //upload file to the server directory
            $uploadedPath = CommonUtils::uploadFiles($request, 'atd_punchin_photo', 'AttendancePhoto', ['prefix' => 'Attendance', 'isApi' => true]);
            if (!empty($uploadedPath)) {
                foreach ($uploadedPath as $path) {
                    $uploadedPhotos[] = url($path);
                }
            }
        }

        // ==================================== New Logic ====================================
        // ---- 0) Load shift policy via resolver --------------------------------
        // $shift = ShiftResolver::resolveEmployeeShift($user, $currentDate);
        $shift = ShiftResolver::resolveEmployeeShiftforUser($user, $currentDate);
        $shiftTiming = $shift ? $shift->shift : null;
        if (!$shiftTiming) {
            return response()->json(['status' => false, 'result' => [], 'message' => 'Shift timing settings are missing.']);
        }

        // ---------------------------------------------------------
        // 1) Load base shift timings
        // ---------------------------------------------------------
        $shiftStartTime = $shiftTiming->pst_start_time;
        $shiftEndTime   = $shiftTiming->pst_end_time;

        // ---------------------------------------------------------
        // 2) Resolve partial day overrides (if any)
        // ---------------------------------------------------------
        $dayName     = Carbon::parse($currentDate)->format('l');     // e.g. Monday
        $weekOfMonth = Carbon::parse($currentDate)->weekOfMonth;     // e.g. 2 (2nd week)

        // Partial Day 1
        if (
            $shiftTiming->pst_allow_partial_day == 1
            && $shiftTiming->fh_master_table_week
            && $shiftTiming->fh_master_table_week->m_name === $dayName
            && (int)$shiftTiming->week_off === $weekOfMonth
        ) {
            $shiftStartTime = $shiftTiming->pst_partial_day_begin_time;
            $shiftEndTime   = $shiftTiming->pst_partial_day_end_time;
        }

        // Partial Day 2
        elseif (
            $shiftTiming->pst_allow_partial_day2 == 1
            && $shiftTiming->fh_master_table_week2
            && $shiftTiming->fh_master_table_week2->m_name === $dayName
            && (int)$shiftTiming->week_off2 === $weekOfMonth
        ) {
            $shiftStartTime = $shiftTiming->pst_partial_day_begin_time2;
            $shiftEndTime   = $shiftTiming->pst_partial_day_end_time2;
        }

        // Normalize with date
        $shiftStart = Carbon::parse($currentDate . ' ' . Carbon::parse($shiftStartTime)->format('H:i:s'));
        $shiftEnd   = Carbon::parse($currentDate . ' ' . Carbon::parse($shiftEndTime)->format('H:i:s'));
        if ($shiftEnd->lessThan($shiftStart)) {
            $shiftEnd->addDay(); // overnight shifts
        }

        // ---------------------------------------------------------
        // 3) Validate Punch-In time
        // ---------------------------------------------------------
        $checkIn = Carbon::parse($checkInDateTime);

        // if early punch-in control is enabled
        if ((int)$shiftTiming->pst_allow_punch_begin_before === 1 && $shiftTiming->pst_type_id == 244) {
            $earliestCheckIn = $shiftStart->copy()->subMinutes((int)$shiftTiming->pst_mins_punch_begin_before);

            if ($checkIn->lessThan($earliestCheckIn)) {
                return response()->json([
                    'status'  => false,
                    'result'  => [],
                    'message' => 'Check-in can only be done after ' . $earliestCheckIn->format('h:i A'),
                ]);
            }
        }

        // If not present, proceed to create or update
        $attendanceData = [
            'atd_check_in_time' => $checkInDateTime,
            'atd_punchin_photo' => json_encode($uploadedPhotos),
            'atd_punchin_location' => $request->input('atd_punchin_location', null),
            'atd_longitude_punchin' => $request->input('atd_longitude_punchin', null),
            'atd_latitude_punchin' => $request->input('atd_latitude_punchin', null),
            'atd_is_absent' => false,
            'atd_work_mode_type_id' => $request->input('atd_work_mode_type_id'),
            'atd_device_id' => $request->input('atd_device_id'),
            'atd_checkin_method_id' => $request->input('atd_checkin_method_id'),
            'atd_am_id' => $request->input('am_id'),
            'atd_pst_id' => $shiftTiming->pst_id,
        ];

        $attendanceRecord = AttendanceRecord::updateOrCreate(
            [
                'atd_b_id' => $user->fh_business->b_id,
                'atd_emp_id' => $user->emp_id,
                'atd_date' => $currentDate,
                'atd_pst_id' => $shiftTiming->pst_id,
            ],
            $attendanceData
        );

        if ($shiftTiming->pst_type_id == 245) {
            return $this->handleAttendance2($attendanceRecord, $user, 'check-in');
        } else {
            return $this->handleAttendance($attendanceRecord, $user, 'check-in');
        }
    }

    public function punchOut(Request $request)
    {
        $user = Auth::user();
        // Determine check-out date and time from request or fallback to now()
        $currentDate = $request->atd_check_out_date ?? now()->format('Y-m-d');
        $currentTime = $request->atd_check_out_time ?? now()->format('H:i:s');
        $checkOutDateTime = $currentDate . ' ' . $currentTime;

        /*if ($user?->fh_attendance_policy?->ap_is_selfie_restricted == 1) {
            $existingDevice = SelfieVerify::where('sv_device_id', $request->atd_device_id)
            ->where('sv_emp_b_id', $user->emp_b_id)
            ->first();

            if (!$existingDevice) {
                return response()->json([
                    'message' => 'This device is not registered. Please register your device first.',
                    'status' => false,
                    'result' => []
                ], 200);
            }

            if ($existingDevice->sv_emp_id != $user->emp_id) {
                $name = trim(preg_replace('/\s+/', ' ', $existingDevice?->employee?->emp_full_name));
                return response()->json([
                    'message' => 'This device is already being used by another user (' . $name . ')',
                    'status' => false,
                    'result' => []
                ], 200);
            }

            if ($existingDevice->sv_status == SelfieVerify::STATUS_PENDING) {
                return response()->json([
                    'message' => 'Your device verification is still pending. Please contact to HR.',
                    'status' => false,
                    'result' => []
                ], 200);
            }

            if ($existingDevice->sv_status == SelfieVerify::STATUS_REJECTED) {
                return response()->json([
                    'message' => 'Your device verification has been rejected. Please contact to HR.',
                    'status' => false,
                    'result' => []
                ], 200);
            }
        }*/

        $uploadedPhotos = [];
        if (env('STORE_ON_S3')) { //upload file to AWS S3 storage.
            $bucket = 'fixhr-uploads';
            if ($request->atd_punchout_photo != '' && $request->atd_punchout_photo != NULL && $request->atd_punchout_photo != []) {
                foreach ($request->atd_punchout_photo as $file) {
                    $imageUniqueName = $file->getClientOriginalName();
                    $imagePath = 'AttendancePhoto/' . $user->fh_business->b_unique_id . '/' . $imageUniqueName;
                    $uploadResult = $this->awsHelper->uploadFileToS3($bucket, $imagePath, $file);
                    if ($uploadResult['status']) {
                        $uploadedPhotos[] = $uploadResult['ObjectURL'];
                    }
                }
            }
        } else { //upload file to the server directory
            $uploadedPath = CommonUtils::uploadFiles($request, 'atd_punchout_photo', 'AttendancePhoto', ['prefix' => 'Attendance', 'isApi' => true]);
            if (!empty($uploadedPath)) {
                foreach ($uploadedPath as $path) {
                    $uploadedPhotos[] = url($path);
                }
            }
        }

        $attendanceRecord = AttendanceRecord::where('atd_b_id', $user->emp_b_id)
            ->where('atd_emp_id', $user->emp_id)
            ->whereNotNull('atd_check_in_time')
            ->whereNull('atd_check_out_time')
            ->latest('atd_date')
            ->first();

        if (!$attendanceRecord) {
            return response()->json([
                'status' => false,
                'result' => [],
                'message' => 'No active check-in found for check-out.'
            ]);
        }

        if (!$attendanceRecord->atd_pst_id) {   // <-- aapka actual column naam yahan use karein
            return response()->json([
                'status' => false,
                'result' => [],
                'message' => 'Shift timing settings are missing.'
            ]);
        }

        $shiftTiming = PolicyShiftTiming::find($attendanceRecord->atd_pst_id);
        if (!$shiftTiming) {
            return response()->json([
                'status' => false,
                'result' => [],
                'message' => 'Shift timing settings are missing.'
            ]);
        }

        // ---- Load shift policy via resolver --------------------------------
        // $shift = ShiftResolver::resolveEmployeeShift($user, $currentDate);
        // $shiftTiming = $shift ? $shift->shift : null;
        // if (!$shiftTiming) {
        //     return response()->json(['status' => false, 'result' => [], 'message' => 'Shift timing settings are missing.']);
        // }
        $endBy = Carbon::parse($currentDate . " " . $shiftTiming->pst_end_by);
        $outTimeStamp = Carbon::parse($checkOutDateTime);
        // if ((int)($shiftTiming->pst_end_next_day ?? 0) === 1 && $outTimeStamp->lte($endBy)) {
        //     $currentDate = Carbon::parse($currentDate)->subDay()->format('Y-m-d');
        // }

        // // Find or create today's attendance record
        // $attendanceRecord = AttendanceRecord::firstOrNew(
        //     [
        //         'atd_b_id' => $user->fh_business->b_id,
        //         'atd_emp_id' => $user->emp_id,
        //         'atd_date' => $currentDate
        //     ]
        // );

        $attendanceRecord->atd_check_out_time = $checkOutDateTime;
        $attendanceRecord->atd_punchout_photo = json_encode($uploadedPhotos);
        $attendanceRecord->atd_punchout_location = $request->input('atd_punchout_location');
        $attendanceRecord->atd_longitude_punchout = $request->input('atd_longitude_punchout');
        $attendanceRecord->atd_latitude_punchout = $request->input('atd_latitude_punchout');
        $attendanceRecord->atd_is_absent = false;

        // Save the attendance record
        $attendanceRecord->save();

        if ($shiftTiming->pst_type_id == 245) {
            return $this->handleAttendance2($attendanceRecord, $user, 'check-out');
        } else {
            return $this->handleAttendance($attendanceRecord, $user, 'check-out');
        }
    }

    public function handleAttendance(AttendanceRecord $data, $user, $type)
    {
        try {
            // Fetch the shift timing policy
            $attendancePolicy = PolicyAttendance::where(['ap_id' => $user->emp_ap_id, 'ap_status' => 1])->first();
            if (!$attendancePolicy) {
                return response()->json(['status' => false, 'result' => [], 'message' => 'Attendance policy settings are missing.']);
            }

            $shiftTiming = PolicyShiftTiming::find($user->emp_shift_type_id);
            if (!$shiftTiming) {
                return response()->json(['status' => false, 'result' => [], 'message' => 'The shift time has not been set.']);
            }

            $shiftStartTime = Carbon::parse($data->atd_date->format('Y-m-d') . ' ' . $shiftTiming->pst_start_time->format('H:i'));
            $shiftEndTime = Carbon::parse($data->atd_date->format('Y-m-d') . ' ' . $shiftTiming->pst_end_time->format('H:i'));

            $dayName = Carbon::parse($data->atd_date)->format('l');


            // Handle Partial day punch
            if ($shiftTiming->pst_allow_partial_day == 1 && $shiftTiming->fh_master_table_week->m_name == $dayName) {
                $shiftStartTime = $shiftTiming->pst_partial_day_begin_time;
                $shiftEndTime = $shiftTiming->pst_partial_day_end_time;
            }

            // Calculate time for check-in or check-out

            $data->atd_pst_id = $shiftTiming->pst_id;
            $currentTimestamp = ($type === 'check-in') ? date('Y-m-d H:i', strtotime($data->atd_check_in_time)) : date('Y-m-d H:i', strtotime($data->atd_check_out_time));
            $shiftTime = ($type === 'check-in') ? $shiftStartTime : $shiftEndTime;


            $pst_start_time = Carbon::parse($shiftStartTime);
            $pst_end_time = Carbon::parse($shiftEndTime);
            $dailyWorkingHours = $pst_start_time->diffInMinutes($pst_end_time);

            $checkInTime = Carbon::parse($data->atd_check_in_time);
            $checkOutTime = Carbon::parse($data->atd_check_out_time);
            $workedDuration = $checkInTime->diffInMinutes($checkOutTime);

            $fullDayThreshold = $dailyWorkingHours;
            $halfDayThreshold = $dailyWorkingHours / 2;

            if ($type === 'check-in') { // Handle late check-in or overtime check-out
                // Added grace time
                $shiftGraceTime = strtotime($shiftTime) + (($shiftTiming->pst_allow_grace_time == 1) ? $shiftTiming->pst_grace_time * 60 : 0);

                $leaveRequest = LeaveRequest::where([
                    ['lvr_emp_id', '=', $user->emp_id],
                    ['lvr_b_id', '=', $user->emp_b_id],
                    ['lvr_start_date', '=', date('Y-m-d')],
                    ['lvr_day_segment_id', '=', 235],
                    ['lvr_leave_day_type_id', '=', 202],
                    ['lvr_status', '!=', 170],
                    ['lvr_stage_completed', '=', 1],
                ])->first();

                if (isset($leaveRequest) && !empty($leaveRequest)) {

                    if ($shiftTiming->pst_allow_break1 == 1) {
                        $breakStart = Carbon::parse($shiftTiming->pst_break_begin_time1);
                        $breakEnd = $breakStart->copy()->addMinutes($shiftTiming->pst_break1_duration);
                        $secondHalfStart = $breakEnd->format('Y-m-d H:i');
                    } else {
                        $breakStart = $pst_start_time->copy()->addHours(4);
                        $breakEnd = $breakStart->copy()->addMinutes(30);
                        $secondHalfStart = $breakEnd->format('Y-m-d H:i');
                    }

                    // If second-half started, update grace time
                    $shiftGraceTime = strtotime($secondHalfStart) + (($shiftTiming->pst_allow_grace_time == 1) ? $shiftTiming->pst_grace_time * 60 : 0);
                }

                // Single late calculation block
                $shiftGraceCarbon = Carbon::parse(date('Y-m-d H:i', $shiftGraceTime));
                $currentTimestampCarbon = Carbon::parse($currentTimestamp); // Current timestamp

                if ($shiftGraceCarbon->lt($currentTimestampCarbon)) {
                    $data->atd_is_late = 1;
                    $lateDurationMinutes = $currentTimestampCarbon->diffInMinutes($shiftGraceCarbon);
                    $data->atd_late_duration = abs(number_format($lateDurationMinutes, 2));
                } else {
                    $data->atd_is_late = 0;
                    $data->atd_late_duration = 0;
                }
            } else {
                // Early Exit calculation
                $leaveRequestSecHalf = LeaveRequest::where([
                    ['lvr_emp_id', '=', $user->emp_id],
                    ['lvr_b_id', '=', $user->emp_b_id],
                    ['lvr_start_date', '=', date('Y-m-d')],
                    ['lvr_day_segment_id', '=', 236],
                    ['lvr_leave_day_type_id', '=', 202],
                    ['lvr_status', '!=', 170],
                    ['lvr_stage_completed', '=', 1],
                ])->first();

                // Convert currentTimestamp to a Carbon instance for comparison
                $currentTimestampCarbon = Carbon::parse($currentTimestamp);
                $shiftTimeCarbon = Carbon::parse($shiftEndTime);
                if (isset($leaveRequestSecHalf) && !empty($leaveRequestSecHalf)) {
                    if ($shiftTiming->pst_allow_break1 == 1) {
                        $firstHalfEnd = Carbon::parse($shiftTiming->pst_break_begin_time1);
                    } else {
                        $firstHalfEnd = $pst_end_time->copy()->subHours(5);
                    }

                    if ($firstHalfEnd->gt($currentTimestampCarbon)) {
                        $data->atd_is_early_exit = 1;
                        $earlyExitDuration = $firstHalfEnd->diffInMinutes($currentTimestampCarbon);
                        $data->atd_early_exit_duration = abs(number_format($earlyExitDuration, 2));
                    } else {
                        $data->atd_is_early_exit = 0;
                        $data->atd_early_exit_duration = 0;
                    }
                } else {
                    if ($shiftTimeCarbon->gt($currentTimestampCarbon)) {
                        $data->atd_is_early_exit = 1;
                        $earlyExitDuration = $shiftTimeCarbon->diffInMinutes($currentTimestampCarbon);
                        $data->atd_early_exit_duration = (float) abs(number_format($earlyExitDuration, 2));
                    } else {
                        $data->atd_is_early_exit = 0;
                        $data->atd_early_exit_duration = 0;
                    }
                }
            }
            // Calculate mispunch status if only one punch (in or out) is present
            // $data = $data->save();

            $this->calculateAttendanceStatus($data);

            if ($data->save()) {
                return ReturnHelper::jsonApiReturn(AttendanceResource::collection([$data]));
            }
            return response()->json(['result' => [], 'status' => false]);
        } catch (Exception $e) {
            return response()->json(['result' => [], 'status' => false, $e->getMessage()]);
        }
    }


    public function handleAttendance2(AttendanceRecord $data, $user, string $type)
    {
        // ---- 0) Load shift policy via resolver --------------------------------
        $shift = PolicyShiftTiming::find($data->atd_pst_id);

        if (!$shift) {
            return response()->json([
                'status'  => false,
                'result'  => [],
                'message' => 'No shift assigned for this employee on ' . $data->atd_date,
            ]);
        }

        // ---- 1) Resolve shift window ------------------------------------------
        $date    = Carbon::parse($data->atd_date)->format('Y-m-d');
        $dayName = Carbon::parse($date)->format('l');

        $shiftStart = Carbon::parse($date . ' ' . $shift->pst_start_time->format('H:i:s'));
        $shiftEnd   = Carbon::parse($date . ' ' . $shift->pst_end_time->format('H:i:s'));
        if ((int)($shift->pst_end_next_day ?? 0) === 1) {
            $shiftEnd->addDay(); // night shift support
        }

        // Partial day overrides
        $isPartialDay = false;
        if (
            (int)($shift->pst_allow_partial_day ?? 0) === 1
            && $shift->fh_master_table_week
            && $shift->fh_master_table_week->m_name === $dayName
        ) {
            $isPartialDay = true;
            $shiftStart = Carbon::parse($date . ' ' . $shift->pst_partial_day_begin_time->format('H:i:s'));
            $shiftEnd   = Carbon::parse($date . ' ' . $shift->pst_partial_day_end_time->format('H:i:s'));
            if ($shiftEnd->lessThan($shiftStart)) {
                $shiftEnd->addDay();
            }
        }

        if (!$isPartialDay && (int)($shift->pst_allow_partial_day2 ?? 0) === 1) {
            if (!empty($shift->week_off2) && trim($shift->week_off2) === $dayName) {
                $isPartialDay = true;
                $shiftStart = Carbon::parse($date . ' ' . $shift->pst_partial_day_begin_time2);
                $shiftEnd   = Carbon::parse($date . ' ' . $shift->pst_partial_day_end_time2);
                if ($shiftEnd->lessThan($shiftStart)) {
                    $shiftEnd->addDay();
                }
            }
        }

        // ---- 2) Read punches --------------------------------------------------
        $checkIn  = $data->atd_check_in_time ? Carbon::parse($data->atd_check_in_time) : null;
        $checkOut = $data->atd_check_out_time ? Carbon::parse($data->atd_check_out_time) : null;

        if ($checkIn && $checkOut && $checkOut->lessThan($checkIn)) {
            $checkOut = $checkOut->copy()->addDay(); // past midnight normalization
        }

        // ---- 3) Mark late / early-exit flags ---------------------------------
        if ($checkIn) {
            $graceMin     = ((int)($shift->pst_allow_grace_time ?? 0) === 1) ? (int)($shift->pst_grace_time ?? 0) : 0;
            $latestOnTime = $shiftStart->copy()->addMinutes($graceMin);

            $data->atd_is_late        = $checkIn->greaterThan($latestOnTime) ? 1 : 0;
            $data->atd_late_duration  = $data->atd_is_late
                ? max(0, $latestOnTime->diffInMinutes($checkIn, false))
                : 0;
        }

        if ($checkOut) {
            $data->atd_is_early_exit        = $checkOut->lessThan($shiftEnd) ? 1 : 0;
            $data->atd_early_exit_duration  = $data->atd_is_early_exit
                ? $checkOut->diffInMinutes($shiftEnd)
                : 0;
        }

        // ---- 4) Compute worked minutes (minus unpaid breaks) -----------------
        $workedMinutes = 0;
        if ($checkIn && $checkOut) {
            $workedMinutes = $checkIn->diffInMinutes($checkOut);

            $break1Unpaid = (int)($shift->pst_is_break_paid ?? 1) === 0;
            $break1Dur    = (int)($shift->pst_break1_duration ?? 0);
            if ($break1Unpaid && $break1Dur > 0) {
                $workedMinutes = max(0, $workedMinutes - $break1Dur);
            }
            // Break 2 always paid → ignored
        }

        // ---- 5) Holiday / Week Off handling ----------------------------------
        $isHoliday = PolicyHolidayList::where('phl_b_id', $data->fh_employee->emp_b_id)
            ->whereDate('phl_start_date', '<=', $date)
            ->whereDate('phl_end_date', '>=', $date)
            ->exists();

        $isWeeklyOff = CentralLogics::getWeekOffDates($data->fh_employee, null, null, $date, $date);

        if (($isHoliday || $isWeeklyOff) && $checkIn && $checkOut) {
            $data->atd_attendance_status = $isWeeklyOff ? 320 : 319;

            // Side effect: generate comp-off
            try {
                CentralLogics::generateCompOff($data->fh_employee, $date);
            } catch (\Throwable $e) {
                // swallow exception, attendance must still save
            }

            $data->save();
            return ReturnHelper::jsonApiReturn(AttendanceResource::collection([$data]));
        }

        // ---- 6) If any punch is missing → Mispunch ---------------------------
        if (!$checkIn || !$checkOut) {
            $data->atd_attendance_status = 228; // Mispunch
            $data->save();
            return ReturnHelper::jsonApiReturn(AttendanceResource::collection([$data]));
        }

        // ---- 7) Compute status code ------------------------------------------
        $statusCode = $this->calculateAttendanceStatus2($data, $isPartialDay);

        $data->atd_attendance_status = $statusCode;

        if ($data->save()) {
            return ReturnHelper::jsonApiReturn(AttendanceResource::collection([$data]));
        }

        return response()->json([
            'status'  => false,
            'result'  => [],
            'message' => 'Failed to save attendance record.'
        ]);
    }

    /**
     * Calculate attendance status code for a given record.
     *
     * @param  AttendanceRecord  $data
     * @param  bool              $isPartialDay (optional) - when true, use partial day thresholds
     * @return int
     */
    public function calculateAttendanceStatus2(AttendanceRecord $data, bool $isPartialDay = false): int
    {
        /** @var PolicyShiftTiming|null $shift */
        $shift = $data->fh_policy_shift_timing;
        if (!$shift) {
            return 0; // fallback, should never happen if handleAttendance2 runs first
        }

        $checkIn  = $data->atd_check_in_time ? Carbon::parse($data->atd_check_in_time) : null;
        $checkOut = $data->atd_check_out_time ? Carbon::parse($data->atd_check_out_time) : null;

        if (!$checkIn || !$checkOut) {
            return 228; // Mispunch safeguard
        }

        // normalize overnight check-outs
        if ($checkOut->lessThan($checkIn)) {
            $checkOut->addDay();
        }

        // ---- Compute worked minutes (with unpaid break adjustment) ----------------
        $workedMinutes = $checkIn->diffInMinutes($checkOut);

        $break1Unpaid = (int)($shift->pst_is_break_paid ?? 1) === 0;
        $break1Dur    = (int)($shift->pst_break1_duration ?? 0);
        if ($break1Unpaid && $break1Dur > 0) {
            $workedMinutes = max(0, $workedMinutes - $break1Dur);
        }
        // Break 2 always paid → ignore

        // ---- Decide thresholds ----------------------------------------------------
        $minWorkMins   = (int)($shift->pst_min_work_hour ?? 0);
        $shiftDuration = (int)($shift->pst_shift_duration ?? 0); // total scheduled duration in minutes

        // If partial day → override shift duration and thresholds
        if ($isPartialDay) {
            $partialStart = $shift->pst_partial_day_begin_time ?? null;
            $partialEnd   = $shift->pst_partial_day_end_time ?? null;

            if ($partialStart && $partialEnd) {
                $pStart = Carbon::parse($partialStart);
                $pEnd   = Carbon::parse($partialEnd);
                if ($pEnd->lessThan($pStart)) {
                    $pEnd->addDay();
                }

                $shiftDuration = $pStart->diffInMinutes($pEnd);
                $minWorkMins   = $shiftDuration; // must complete partial hours fully
            }
        }

        //OT Calculation
        if ($workedMinutes >= $shiftDuration) {
            $user = Auth::user();
            $ot_enabled = OvertimePolicy::where('ot_b_id', $user->emp_b_id)->where('ot_is_enabled', 1)->exists();
            if ($ot_enabled !== null && $ot_enabled) {
                $ruleCriteria = RuleCriterion::with('fh_approval_module')
                    ->where('rc_b_id', $user->emp_b_id)
                    ->where('rc_condition_option_id', 140)
                    ->whereHas('fh_approval_module', function ($query) {
                        $query->where('am_module_id', 562)
                            ->where('am_status', 1);
                    })->first();
                $processApprovers = [];
                $emp_d_id = $user->emp_d_id;
                $amId = null;
                $notApproval = 0;
                // Ensure $ruleCriteria exists before accessing the relationship
                if ($ruleCriteria && $ruleCriteria->fh_approval_module) {
                    $processApprovers = $ruleCriteria->fh_approval_module
                        ->filteredProcessApprovers($emp_d_id)
                        ->get();
                }
                $approvalEmpIds = [];
                if (empty($processApprovers)) {
                    $approvalMapping = ApprovalHelper::getApprovalMapping($user->emp_b_id, $user->emp_id, 562);
                    if (!$approvalMapping) {
                        $data->atd_is_overtime = 0;
                        $data->atd_overtime_hours = 0;
                        $notApproval = 1;
                    }
                    $amId = $approvalMapping->eam_am_id ?? null;
                    $approvalEmpIds = ApprovalHelper::getApprovalArray($approvalMapping);
                } else {
                    $amId = $ruleCriteria->rc_am_id;
                    foreach ($processApprovers as $pa) {
                        if ($pa->pa_emp_id) {
                            $approvalEmpIds[] = $pa->pa_emp_id;
                        }
                    }
                }
                if($notApproval != 1) {
                    $data->atd_is_overtime = 1;
                    $data->atd_overtime_hours = CentralLogics::calculateOTRoster($data) ?? 0.00;
                    OtApprovalStatus::updateOrCreate(
                        [
                            'ot_atd_id' => $data->atd_id,
                            'ot_b_id'   => $user->emp_b_id,
                            'ot_date'   => $data->atd_date,
                        ],
                        [
                            'ot_emp_id'          => $data->atd_emp_id,
                            'ot_atd_type'        => 'rec',
                            'ot_module_id'       => 562,
                            'ot_next_approver'   => $approvalEmpIds[0] ?? null,
                            'ot_requested_status'=> 140,
                            'ot_am_id'           => $amId,
                            'ot_stage_completed' => 0,
                        ]
                    );
                }
            }
        } else {
            $data->atd_is_overtime = 0;
            $data->atd_overtime_hours = 0;
        }

        $halfDayThreshold = (int)floor($shiftDuration * 0.5);

        // ---- Apply rules ----------------------------------------------------------
        if ($workedMinutes >= $minWorkMins) {
            return 251; // Present
        }

        if ($workedMinutes >= $halfDayThreshold && $workedMinutes < $minWorkMins) {
            return 252; // Half Day
        }

        return 203; // Absent
    }

    function calculateAttendanceStatus(AttendanceRecord $data)
    {
        $atd_req = $data->fh_employee->emp_attendance_preference;
        $shift = $data->fh_policy_shift_timing;
        if (!$shift) {
            $data->atd_attendance_status = 228; // Mispunch
            return;
        }

        // if ((!isset($data->atd_check_in_time) || !isset($data->atd_check_out_time)) && ($atd_req != 368 || $atd_req != 369)) {
        //     $data->atd_attendance_status = 228; // Mispunch
        //     return;
        // }

        // Retrieve shift start and end times
        $pst_start_time = Carbon::parse($shift->pst_start_time);
        $pst_end_time = Carbon::parse($shift->pst_end_time);
        $dailyWorkingMinutes = $pst_start_time->diffInMinutes($pst_end_time);

        $dayName = Carbon::parse($data->atd_date)->format('l');

        // Handle Partial day punch
        if ($shift->pst_allow_partial_day == 1 && $shift->fh_master_table_week->m_name == $dayName) {
            $shiftStartTime = $shift->pst_partial_day_begin_time;
            $shiftEndTime = $shift->pst_partial_day_end_time;
            $dailyWorkingMinutes = $shiftStartTime->diffInMinutes($shiftEndTime);
        }

        // Check if current day is a holiday
        $isHoliday = PolicyHolidayList::where('phl_b_id', $data->fh_employee->emp_b_id)
            ->whereDate('phl_start_date', '<=', $data->atd_date)
            ->whereDate('phl_end_date', '>=', $data->atd_date)
            ->first();

        // If today is a holiday and it's a half-day holiday, override daily working minutes
        // based on the shift's start/end and break timings. Compute segment length from pst_start_time / pst_end_time
        // and the break timings (pst_break_begin_time1 / pst_break_end_time1). If
        // break timings are not present, fallback to a static 4 hours (240 minutes).
        if ($isHoliday && isset($isHoliday->phl_day_type_id) && (int)$isHoliday->phl_day_type_id === 202) {
            // 202 => Half Day, segments expected to be 235 (First Half) or 236 (Second Half)
            $holidaySegment = $isHoliday->phl_day_segment_type_id ?? null;
            $segmentMinutes = null;

            if ($shift) {
                // Parse shift start and end
                try {
                    $shiftStart = Carbon::parse($shift->pst_start_time);
                    $shiftEnd = Carbon::parse($shift->pst_end_time);
                    if ($shiftEnd->lessThan($shiftStart)) {
                        $shiftEnd->addDay();
                    }
                } catch (\Exception $e) {
                    $shiftStart = null;
                    $shiftEnd = null;
                }

                // If break timings are configured, use them to split morning/afternoon
                if (!empty($shift->pst_break_begin_time1) && !empty($shift->pst_break_end_time1) && $shiftStart && $shiftEnd) {
                    $breakStart = Carbon::parse($shift->pst_break_begin_time1);
                    $breakEnd = Carbon::parse($shift->pst_break_end_time1);
                    // Normalize break times to the same date as shift for comparison
                    $breakStart = Carbon::parse($data->atd_date->format('Y-m-d') . ' ' . $breakStart->format('H:i:s'));
                    $breakEnd = Carbon::parse($data->atd_date->format('Y-m-d') . ' ' . $breakEnd->format('H:i:s'));
                    if ($breakEnd->lessThan($breakStart)) {
                        $breakEnd->addDay();
                    }
                    // If break is outside shift window, clamp to shift
                    if ($breakStart->lessThan($shiftStart)) {
                        $breakStart = $shiftStart->copy();
                    }
                    if ($breakEnd->greaterThan($shiftEnd)) {
                        $breakEnd = $shiftEnd->copy();
                    }

                    // Morning segment = shiftStart -> breakStart
                    $morningMinutes = max(0, $shiftStart->diffInMinutes($breakStart));
                    // Afternoon segment = breakEnd -> shiftEnd
                    $afternoonMinutes = max(0, $breakEnd->diffInMinutes($shiftEnd));

                    if ($holidaySegment == 235) {
                        $segmentMinutes = $morningMinutes;
                    } elseif ($holidaySegment == 236) {
                        $segmentMinutes = $afternoonMinutes;
                    }
                } else {
                    // No break timings — we cannot safely derive segments, fall back to static
                    $segmentMinutes = null;
                }
            }

            // Final fallback: static 4 hours
            if (empty($segmentMinutes) || $segmentMinutes <= 0) {
                $segmentMinutes = 4 * 60; // 4 hours in minutes
            }

            // Override daily working minutes for this day (half-day holiday)
            $dailyWorkingMinutes = (int) $segmentMinutes;
        }

        $breakMinutes = (int) $shift->pst_break_duration_minutes;
        $graceMinutes = (int) $shift->pst_grace_time;

        // Calculate thresholds
        $halfDayThreshold = $dailyWorkingMinutes / 2 - $graceMinutes - $breakMinutes;
        $fullDayThreshold = $dailyWorkingMinutes - $graceMinutes;

        // Check if current day is a weekly off day
        $isWeeklyOff = CentralLogics::getWeekOffDates($data->fh_employee, null, null, $data->atd_date, $data->atd_date);

        $checkIn = Carbon::parse($data->atd_check_in_time);
        $checkOut = Carbon::parse($data->atd_check_out_time);
        $workedDuration = $checkIn->diffInMinutes($checkOut);
        $worked_time = $checkIn->diff($checkOut)->format('%H:%I:%S');

        $punchDate = $checkIn->format('Y-m-d');

        if (($isWeeklyOff || ($isHoliday && isset($isHoliday->phl_day_type_id) && (int)$isHoliday->phl_day_type_id == 201))
            && isset($data->atd_check_in_time, $data->atd_check_out_time)
            && $data->atd_check_in_time !== ''
            && $data->atd_check_out_time !== ''
        ) {
            $data->atd_attendance_status = $isWeeklyOff ? 320 : 319;
            $data->atd_is_late = 0;
            $data->atd_is_early_exit = 0;
            return;
        }

        // Calculate min work hour in minutes
        $minWorkHour = $shift->pst_min_work_hour
            ? Carbon::parse($shift->pst_start_time)->diffInMinutes(
                Carbon::parse($shift->pst_min_work_hour)
            )
            : 0;

        // Attendance Status Logic
        if ($workedDuration >= $fullDayThreshold || $workedDuration >= $minWorkHour || $atd_req == 368 || $atd_req == 369) {
            if (
                (
                    $shift->pst_hd_office_report_after &&
                    $shift->pst_hd_office_report_after_time &&
                    $checkIn->gt(Carbon::parse($shift->pst_hd_office_report_after_time))
                )
                ||
                (
                    $shift->pst_hd_office_report_before &&
                    $shift->pst_hd_office_report_before_time &&
                    $checkIn->lt(Carbon::parse($shift->pst_hd_office_report_before_time))
                )
            ) {
                $data->atd_attendance_status = 252; // Half Day
                $data->atd_is_late = 0;
                $data->atd_is_early_exit = 0;
            } else {
                $data->atd_attendance_status = 251; // Present
            }

        } elseif ($workedDuration >= ($fullDayThreshold / 2) || $workedDuration >= ($minWorkHour / 2)) {
            $data->atd_attendance_status = 252; // Half Day
        } else {
            $data->atd_attendance_status = 203; // Half Day
        }

        //OT Calculation
        if (($workedDuration >= $fullDayThreshold)) {
            $user = Auth::user();
            $ot_enabled = OvertimePolicy::where('ot_b_id', $user->emp_b_id)->where('ot_is_enabled', 1)->exists();
            if ($ot_enabled !== null && $ot_enabled) {
                $ruleCriteria = RuleCriterion::with('fh_approval_module')
                    ->where('rc_b_id', $user->emp_b_id)
                    ->where('rc_condition_option_id', 140)
                    ->whereHas('fh_approval_module', function ($query) {
                        $query->where('am_module_id', 562)
                            ->where('am_status', 1);
                    })->first();
                $processApprovers = [];
                $emp_d_id = $user->emp_d_id;
                $amId = null;
                // Ensure $ruleCriteria exists before accessing the relationship
                if ($ruleCriteria && $ruleCriteria->fh_approval_module) {
                    $processApprovers = $ruleCriteria->fh_approval_module
                        ->filteredProcessApprovers($emp_d_id)
                        ->get();
                }
                $approvalEmpIds = [];
                if (empty($processApprovers)) {
                    $approvalMapping = ApprovalHelper::getApprovalMapping($user->emp_b_id, $user->emp_id, 562);
                    if (!$approvalMapping) {
                        return [
                            'status' => false,
                            'message' => 'Attendance created successfully, but overtime was not created. Please set the overtime approval or disable the overtime feature.',
                        ];
                    }
                    $amId = $approvalMapping->eam_am_id ?? null;
                    $approvalEmpIds = ApprovalHelper::getApprovalArray($approvalMapping);
                } else {
                    $amId = $ruleCriteria->rc_am_id;
                    foreach ($processApprovers as $pa) {
                        if ($pa->pa_emp_id) {
                            $approvalEmpIds[] = $pa->pa_emp_id;
                        }
                    }
                }
                $data->atd_is_overtime = 1;
                $data->atd_overtime_hours = CentralLogics::calculateOT($data) ?? 0.00;
                OtApprovalStatus::updateOrCreate(
                    [
                        'ot_atd_id' => $data->atd_id,
                        'ot_b_id'   => $data->atd_b_id,
                        'ot_date'   => $data->atd_date,
                    ],
                    [
                        'ot_emp_id'          => $data->atd_emp_id,
                        'ot_atd_type'        => 'rec',
                        'ot_module_id'       => 562,
                        'ot_next_approver'   => $approvalEmpIds[0] ?? null,
                        'ot_requested_status'=> 140,
                        'ot_am_id'           => $amId,
                        'ot_stage_completed' => 0,
                    ]
                );
            }
            // $data->atd_is_overtime = 0;
            // $data->atd_overtime_hours = 0;
        } else {
            $data->atd_is_overtime = 0;
            $data->atd_overtime_hours = 0;
        }
    }

    public function mispunch(Request $request, $isApproval = null)
    {
        $user = Auth::user();

        if ($isApproval) {
            $page = $request->input('page', 1);
            $limit = $request->input('limit', 10);

            // $query = AttendanceException::where('ae_b_id', $user->emp_b_id)
            //     ->whereNotIn('ae_status', [139, 192, 156]);
            $query = AttendanceException::where('ae_b_id', $user->emp_b_id)
                ->whereNotIn('ae_status', [139, 192, 156])->approvableBy($user);
            // ->whereNotNull('ae_am_id');

            if (!$query->first()) {
                return response()->json(['result' => [], 'message' => 'No missed-punch application found', 'status' => false]);
            }

            if (!is_null($request->input('status'))) {
                $query->where('ae_status', $request->input('status'));
            }
            if (!is_null($request->input('from_date'))) {
                $query->whereDate('ae_date', '>=', Carbon::createFromFormat('d M, Y', $request->input('from_date'))->startOfDay());
            }
            if (!is_null($request->input('to_date'))) {
                $query->whereDate('ae_date', '<=', Carbon::createFromFormat('d M, Y', $request->input('to_date'))->startOfDay());
            }
            if ($request->input('last_15_days')) {
                $query->whereDate('created_at', '>=', Carbon::now()->subDays(15));
            }

            $data = $query->orderBy('ae_id', 'DESC')->paginate($limit, ['*'], 'page', $page);

            if ($query->exists()) {
                return ReturnHelper::jsonApiReturn(new PaginatedResource($data, AttendanceExceptionResource::class));
            } else {
                return response()->json(['result' => [], 'message' => 'No missed-punch application found', 'status' => false]);
            }
        } else {
            $month = $request->input('month') ?? Carbon::now()->month;
            $year = $request->input('year') ?? Carbon::now()->year;

            $attendanceExceptions = AttendanceException::where('ae_emp_id', $user->emp_id)
                ->whereMonth('ae_date', $month)
                ->whereYear('ae_date', $year)
                ->get();

            $missedPunchCountDetail = self::calculateMissedPunchCounts($user, $month, $year);

            if ($attendanceExceptions->isEmpty()) {
                return response()->json([
                    'result' => [
                        'missed_punch_list' => [],
                        'missed_punch_count_details' => $missedPunchCountDetail
                    ],
                    'message' => 'No missed-punch application found.',
                    'status' => false
                ]);
            } else {
                return ReturnHelper::jsonApiReturn([
                    'missed_punch_list' => AttendanceExceptionResource::collection($attendanceExceptions),
                    'missed_punch_count_details' => $missedPunchCountDetail
                ]);
            }
        }
    }

    public function mispunchstore(AttendanceRequest $request)
    {

        $user = Auth::user();

        $ae_date = $request ? Carbon::createFromFormat('d M, Y', $request->input('date'))->format('Y-m-d') : null;
        $ae_type_id = $request->input('type_id', null);
        $approved_by = $request->input('approved_by', null);
        $reason = $request->input('reason', null);
        $customReason = $request->input('custom_reason', null);

        $requestAlreadyExists = AttendanceException::where('ae_emp_id', $user->emp_id)
            ->where('ae_status', '!=', 170)
            ->where('ae_date', $ae_date)->exists();

        if ($requestAlreadyExists) {
            return response()->json(['result' => [], 'status' => false, 'message' => 'A missed punch request for this date has already been submitted.']);
        }

        $attendancePolicy = PolicyAttendance::where('ap_b_id', $user->emp_b_id)
            ->where('ap_status', true)
            ->first();

        if (!$attendancePolicy) {
            return response()->json(['result' => [], 'status' => false, 'message' => 'Shift Time is not set']);
        }

        $attendanceRecord = AttendanceRecord::where('atd_emp_id', $user->emp_id)
            ->where('atd_b_id', $user->fh_business->b_id)
            ->where('atd_date', $ae_date)
            ->first();

        if (!MasterTable::where('m_id', $ae_type_id)->exists()) {
            return response()->json(['result' => [], 'status' => false, 'message' => 'Invalid type ID'], 400);
        }

        // $in_time = $request->in_time ? Carbon::parse($request->input('in_time', 0))->format('H:i:s') : $in_time;
        // $out_time = $request->out_time ? Carbon::parse($request->input('out_time', 0))->format('H:i:s') : $out_time;

        $in_time = $request->in_time ? Carbon::parse($request->in_time) : null;
        $out_time = $request->out_time ? Carbon::parse($request->out_time) : null;

        if (empty($attendanceRecord) || (!$attendanceRecord->atd_check_in_time && !$attendanceRecord->atd_check_out_time)) {
            return response()->json([
                'result' => [],
                'status' => false,
                'message' => 'Sorry! Not allowed for this date because this date does not have any record.'
            ]);
        }

        $ruleCriteria = RuleCriterion::with('fh_approval_module')
            ->where('rc_b_id', $user->emp_b_id)
            ->where('rc_condition_option_id', 140)
            ->whereHas('fh_approval_module', function ($query) {
                $query->where('am_module_id', 229)
                    ->where('am_status', 1);
            })->first();

        $processApprovers = [];
        $emp_d_id = $user->emp_d_id;
        $amId = null;

        $shiftTiming = PolicyShiftTiming::find($attendanceRecord->fh_employee->emp_shift_type_id);
        $shiftStartTime = $shiftTiming->pst_start_time;
        $shiftMinEndTime = $shiftTiming->pst_min_work_hour;

        $shiftStartTime = Carbon::parse($shiftStartTime);
        $shiftMinEndTime = Carbon::parse($shiftMinEndTime);

        // Calculate the difference in minutes
        $timeDifferenceInMinutes = $shiftStartTime->diffInMinutes($shiftMinEndTime);
        $requestsMinutes = $in_time->diffInMinutes($out_time);

        if ($timeDifferenceInMinutes > $requestsMinutes) {
            return response()->json(['result' => [], 'status' => false, 'message' => 'Please correct missed punch time as per shift policy.']);
        }

        // Ensure $ruleCriteria exists before accessing the relationship
        if ($ruleCriteria && $ruleCriteria->fh_approval_module) {
            // Fetch filtered process approvers using emp_d_id
            $processApprovers = $ruleCriteria->fh_approval_module
                ->filteredProcessApprovers($emp_d_id)
                ->get(); // Fetch the filtered data
        }

        if (empty($processApprovers)) {
            $approvalMapping = ApprovalHelper::getApprovalMapping($user->emp_b_id, $user->emp_id, 229);
            if (!$approvalMapping) {
                return response()->json(['result' => [], 'status' => false, 'message' => 'Sorry! not found any approval settings for mip-punch module, contact hr.']);
            }
        } else {
            $amId = $ruleCriteria->rc_am_id;
        }

        $missedPunchLimit = AutomationRule::where('ar_b_id', $user->emp_b_id)
            ->where('ar_rule_type', 417)
            ->select('ar_occurrences', 'ar_both_time_count')
            ->first();

        if ($missedPunchLimit) {
            $month = Carbon::parse($ae_date)->month;
            $year = Carbon::parse($ae_date)->year;

            $attendanceExceptions = AttendanceException::where('ae_emp_id', $user->emp_id)
                ->whereMonth('ae_date', $month)
                ->whereYear('ae_date', $year)
                ->where("ae_status", "!=", 170) // exclude rejected
                ->get();

            $usedCount = $attendanceExceptions->sum(function ($exception) use ($missedPunchLimit) {
                return ($missedPunchLimit->ar_both_time_count == 2 && $exception->ae_type_id == 217) ? 2 : 1;
            });

            $remaining = $missedPunchLimit->ar_occurrences - $usedCount;

            // Stop if limit exceeded
            if ($ae_type_id == 217 && $missedPunchLimit->ar_both_time_count == 2 && $remaining < 2) {
                return response()->json([
                    'result' => [],
                    'status' => false,
                    'message' => 'You cannot apply both-time missed punch because limit exceeded.'
                ]);
            }

            if ($ae_type_id != 217 && $remaining < 1) {
                return response()->json([
                    'result' => [],
                    'status' => false,
                    'message' => 'You cannot apply missed punch because limit exceeded.'
                ]);
            }
        }

        $attendanceException = AttendanceException::Create(
            [
                'ae_b_id' => $user->fh_business->b_id,
                'ae_emp_id' => $user->emp_id,
                'ae_date' => $ae_date,
                'ae_type_id' => $ae_type_id,
                'ae_ap_id' => $attendancePolicy->ap_id,
                'ae_approved_by' => $approved_by,
                'ae_reason_id' => $reason,
                'ae_custom_reason' => $customReason,
                'ae_in_time' => $in_time,
                'ae_out_time' => $out_time,
                // 'ae_total_working' => $total_working,
                'ae_status' => 140,
                'ae_am_id' => $amId,
                'ae_stage_completed' => 0,

            ]
        );
        // ✅ Generate and update ae_code after creation using ae_date and ae_id
        if ($attendanceException) {
            $attendanceException->ae_code = 'AE' . Carbon::parse($attendanceException->ae_date)->format('Ymd') . '-' . $attendanceException->ae_id;
            $attendanceException->save();
        }
        $attendanceException->refresh();

        if ($attendanceException) {


            $approvalEmpIds = [];
            $amId = null;

            // Get employee-wise mapping first
            $approvalMapping = ApprovalHelper::getApprovalMapping($user->emp_b_id, $user->emp_id, 229);
            if ($approvalMapping) {
                $approvalEmpIds = ApprovalHelper::getApprovalArray($approvalMapping);
                $amId = $approvalMapping->eam_am_id ?? null;
            } else {
                // Fallback to hierarchy: get RuleCriteria and processApprovers
                $ruleCriteria = RuleCriterion::with('fh_approval_module')
                    ->where('rc_b_id', $user->emp_b_id)
                    ->where('rc_condition_option_id', 140)
                    ->whereHas('fh_approval_module', function ($query) {
                        $query->where('am_module_id', 229)
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

            $dateOnly = $attendanceException->ae_date;
            $title = 'Missed Punch Request';
            $body = 'A missed punch request has been submitted by ' . $user->emp_full_name . ' on ' . \Carbon\Carbon::parse($dateOnly)->format('d/m/y') . '.';
            $additionalData = [
                'user_id' => $user->emp_id,
                'notification_type' => 'alert',
                'route' => '/MissedPunchApprovalList',
            ];
            $serviceAccountPath = public_path('fixhr-app-firebase.json');

            // Notification: send to FIRST approver only
            if (!empty($approvalEmpIds)) {
                $firstApproverEmpId = $approvalEmpIds[0];
                $emp = Employee::find($firstApproverEmpId);
                $approver = ApprovalHelper::getApprovalOrRejectionData($attendanceException->ae_id, $attendanceException->ae_status, $amId, $firstApproverEmpId, 229);

                if ($emp && $emp->emp_is_notification_enabled == '1') {

                    if ($emp->emp_fcm_token) {
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
            return response()->json(['result' => [], 'status' => true, 'message' => 'Missed punch request submitted successfully.']);
        } else {
            return response()->json(['result' => [], 'status' => false, 'message' => 'Failed to update attendance exception.']);
        }
    }

    public function misPunchDelete($id)
    {
        $user = Auth::user();
        $ae = AttendanceException::find($id);

        if (!$ae) {
            return [
                'result' => [],
                'message' => 'Missed punch request not found',
                'status' => false
            ];
        }

        if (!$ae->delete()) {
            return [
                'result' => [],
                'message' => 'Failed to delete missed punch request',
                'status' => false
            ];
        }

        // ✅ Recalculate after delete
        $month = Carbon::now()->month;
        $year = Carbon::now()->year;
        $missedPunchCountDetail = self::calculateMissedPunchCounts($user, $month, $year);

        return [
            'result' => [
                'missed_punch_count_details' => $missedPunchCountDetail
            ],
            'message' => 'Missed punch request deleted successfully',
            'status' => true
        ];
    }

    private function calculateMissedPunchCounts($user, $month, $year)
    {
        $attendanceExceptions = AttendanceException::where('ae_emp_id', $user->emp_id)
            ->whereMonth('ae_date', $month)
            ->whereYear('ae_date', $year)
            ->get();

        $missedPunchLimit = AutomationRule::where('ar_b_id', $user->emp_b_id)
            ->where('ar_rule_type', 417)
            ->select('ar_occurrences', 'ar_both_time_count')
            ->first();

        $missedPunchApplyBeforeDay = AutomationRule::where('ar_b_id', $user->emp_b_id)
            ->where('ar_rule_type', 417)
            ->select('ar_apply_before_day')
            ->first();

        $appliedCount = null;
        $remainingCount = null;

        if ($missedPunchLimit && !empty($missedPunchLimit->ar_both_time_count)) {
            $appliedCount = $attendanceExceptions->sum(function ($exception) use ($missedPunchLimit) {
                return ($missedPunchLimit->ar_both_time_count == 2 && $exception->ae_type_id == 217) ? 2 : 1;
            });

            $remainingCount = $missedPunchLimit
                ? ($missedPunchLimit->ar_occurrences - $attendanceExceptions
                    ->where("ae_status", "!=", 170)
                    ->sum(function ($exception) use ($missedPunchLimit) {
                        return ($missedPunchLimit->ar_both_time_count == 2 && $exception->ae_type_id == 217) ? 2 : 1;
                    }))
                : null;
        }

        return [
            'missed_punch_limit' => $missedPunchLimit ? $missedPunchLimit->ar_occurrences : null,
            'missed_punch_applied' => $appliedCount !== null ? $appliedCount : $attendanceExceptions->count(),
            'missed_punch_remaining' => $missedPunchLimit
                ? ($remainingCount !== null
                    ? $remainingCount
                    : ($missedPunchLimit->ar_occurrences - $attendanceExceptions->where("ae_status", "!=", 170)->count()))
                : null,
            'missed_punch_apply_before_day' => $missedPunchApplyBeforeDay ? $missedPunchApplyBeforeDay->ar_apply_before_day : null,
        ];
    }

    public function typeid()
    {
        $data = MasterTable::where('m_group', 'ATTENDANCE_EXCEPTION')->get();

        if ($data) {
            return ReturnHelper::jsonApiReturn(MasterTableResource::collection($data)->all());
        }
        return response()->json(['result' => [], 'status' => false]);
    }

    public function mispunchReason()
    {
        $data = MasterTable::where('m_group', 'MISPUNCH_REASON')->get();

        if ($data) {
            return ReturnHelper::jsonApiReturn(MasterTableResource::collection($data)->all());
        }
        return response()->json(['result' => [], 'status' => false]);
    }


    public function getInOutTime(Request $request)
    {
        $user = Auth::user();
        $inputDate = $request ? Carbon::createFromFormat('d M, Y', $request->input('date'))->format('Y-m-d') : null;

        $attendanceRecord = AttendanceRecord::where([
            ['atd_emp_id', $user->emp_id],
            ['atd_b_id', $user->fh_business->b_id],
        ])->whereDate('atd_date', $inputDate)->first();

        $typeCollection = MasterTableResource::collection(
            MasterTable::where('m_group', 'ATTENDANCE_EXCEPTION')->get()
        );

        $checkInTime = $attendanceRecord?->atd_check_in_time ? Carbon::parse($attendanceRecord->atd_check_in_time)->format('h:i A') : null;
        $checkOutTime = $attendanceRecord?->atd_check_out_time ? Carbon::parse($attendanceRecord->atd_check_out_time)->format('h:i A') : null;

        if ($attendanceRecord) {
            /*if ($checkInTime && !$checkOutTime) {
                $type = $typeCollection->where('m_id', 216)->values(); // Only check-in
            } elseif ($checkOutTime && !$checkInTime) {
                $type = $typeCollection->where('m_id', 218)->values(); // Only check-out
            } else {
                $type = $typeCollection->where('m_id', 217)->values(); // Both present or both missing
            }*/
            $type = $typeCollection->whereIn('m_id', [216, 217, 218])->values();
        } else {
            $type = $typeCollection->where('m_id', 217)->values(); // No record
        }

        $result = [[
            'check_in_time'  => $checkInTime,
            'check_out_time' => $checkOutTime,
            'type'           => $type,
        ]];

        return response()->json([
            'result' => $result,
            'status' => true,
        ]);
    }


    public function dateWiseAttendanceSummary($date)
    {
        $user = Auth::user();
        $parseDate = Carbon::parse($date);

        $allActiveEmployeeIds = Employee::where(['emp_b_id' => $user->emp_b_id, 'emp_status' => 71])
            ->whereNot('emp_role_id', 1);

        $user->emp_role_id != 1 ? $allActiveEmployeeIds->where('emp_supervisor_id', $user->emp_id) : null;

        $allActiveEmployeeIds = $allActiveEmployeeIds->pluck('emp_id')->toArray();

        $employeeLeaveIds = LeaveRequest::where('lvr_b_id', $user->emp_b_id);

        $user->emp_role_id != 1 ? $employeeLeaveIds->whereHas('fh_employee', function ($q) use ($user) {
            $q->where('emp_supervisor_id', $user->emp_id);
        })
            : null;

        $employeeLeaveIds = $employeeLeaveIds
            ->where('lvr_stage_completed', 1)->where('lvr_status', '!=', 170)
            ->whereDate('lvr_start_date', '<=', $parseDate)
            ->whereDate('lvr_end_date', '>=', $parseDate)->pluck('lvr_emp_id')->toArray();

        $attendanceLogs = AttendanceLog::where('al_b_id', $user->emp_b_id)
            ->whereDate('al_date', $parseDate);

        $user->emp_role_id != 1
            ? $attendanceLogs->whereHas('fh_employee_real', function ($q) use ($user) {
                $q->where('emp_supervisor_id', $user->emp_id);
            })
            : null;

        $attendanceLogs = $attendanceLogs->get([
            'al_emp_id as emp_id',
            'al_attendance_status as attendance_status',
            'al_is_late as is_late',
            'al_is_early_exit as is_early_exit',
        ]);

        $logEmployeeIds = $attendanceLogs->pluck('emp_id')->toArray();

        $attendanceRecords = AttendanceRecord::where('atd_b_id', $user->emp_b_id)
            ->whereDate('atd_date', $parseDate)
            ->whereNotIn('atd_emp_id', $logEmployeeIds);

        $user->emp_role_id != 1 ? $attendanceRecords->whereHas('fh_employee', function ($q) use ($user) {
            $q->where('emp_supervisor_id', $user->emp_id);
        })
            : null;

        $attendanceRecords = $attendanceRecords->get([
            'atd_emp_id as emp_id',
            'atd_attendance_status as attendance_status',
            'atd_is_late as is_late',
            'atd_is_early_exit as is_early_exit',
        ]);

        $finalAttendance = $attendanceLogs->concat($attendanceRecords)->values();

        $employeePresentIds = $finalAttendance->pluck('emp_id')->toArray();
        // dd($finalAttendance->toArray(), $attendanceLogs->toArray(), $attendanceRecords, $employeePresentIds);

        if ($parseDate->format('Y-m-d') == now()->format('Y-m-d')) {
            $presentStatus = [228, 251];
        } else {
            $presentStatus = [251];
        }
        $fullDayPresentCount = $finalAttendance->whereIn('attendance_status', $presentStatus)->count();


        $halfDayCount = $finalAttendance->where('attendance_status', 252)->count();

        $lateCount = $finalAttendance->where('is_late', 1)->count();

        $atd_is_early_exit = $finalAttendance->where('is_early_exit', 1)->count();

        $absentEmployeeIds = array_diff($allActiveEmployeeIds, array_merge($employeePresentIds, $employeeLeaveIds));


        return [
            'result' => [
                'all_employee'     => count($allActiveEmployeeIds),
                'full_day_present' => $fullDayPresentCount,
                'half_day'         => $halfDayCount,
                'late'             => $lateCount,
                'early_exit'             => $atd_is_early_exit,
                'leave'            => count($employeeLeaveIds),
                'absent'           => !empty($absentEmployeeIds) ? count($absentEmployeeIds) : 0,
            ],
            'status' => true,
        ];
    }

    public function dateWiseAttendanceSummaryNew($date)
    {
        $user = Auth::user();
        $parseDate = Carbon::parse($date);

        // ===============================
        // 1. Get Employee IDs (Single Source)
        // ===============================
        $employeeQuery = Employee::where([
            'emp_b_id' => $user->emp_b_id,
            'emp_status' => 71
        ])->where('emp_role_id', '!=', 1);

        if ($user->emp_role_id != 1) {
            $employeeQuery->where('emp_supervisor_id', $user->emp_id);
        }

        $employeeIds = $employeeQuery->pluck('emp_id');

        // ===============================
        // 2. Get Leave Employees
        // ===============================
        $leaveQuery = LeaveRequest::where('lvr_b_id', $user->emp_b_id)
            ->where('lvr_stage_completed', 1)
            ->where('lvr_status', '!=', 170)
            ->whereDate('lvr_start_date', '<=', $parseDate)
            ->whereDate('lvr_end_date', '>=', $parseDate);

        if ($user->emp_role_id != 1) {
            $leaveQuery->whereIn('lvr_emp_id', $employeeIds);
        }

        $leaveEmployeeIds = $leaveQuery->pluck('lvr_emp_id');

        // ===============================
        // 3. Attendance (UNION - DB Level Merge)
        // ===============================
        $attendanceLogsQuery = AttendanceLog::select(
            'al_emp_id as emp_id',
            'al_attendance_status as attendance_status',
            'al_is_late as is_late',
            'al_is_early_exit as is_early_exit'
        )
        ->where('al_b_id', $user->emp_b_id)
        ->whereIn('al_emp_id', $employeeIds)
        ->whereBetween('al_date', [
            $parseDate->copy()->startOfDay(),
            $parseDate->copy()->endOfDay()
        ]);

        $attendanceRecordsQuery = AttendanceRecord::select(
            'atd_emp_id as emp_id',
            'atd_attendance_status as attendance_status',
            'atd_is_late as is_late',
            'atd_is_early_exit as is_early_exit'
        )
        ->where('atd_b_id', $user->emp_b_id)
        ->whereIn('atd_emp_id', $employeeIds)
        ->whereBetween('atd_date', [
            $parseDate->copy()->startOfDay(),
            $parseDate->copy()->endOfDay()
        ]);

        $finalAttendance = $attendanceLogsQuery
            ->unionAll($attendanceRecordsQuery)
            ->get();

        // ===============================
        // 4. Counts
        // ===============================
        $employeePresentIds = $finalAttendance->pluck('emp_id')->unique();

        $presentStatus = $parseDate->isToday() ? [228, 251] : [251];

        $fullDayPresentCount = $finalAttendance
            ->whereIn('attendance_status', $presentStatus)
            ->count();

        $halfDayCount = $finalAttendance
            ->where('attendance_status', 252)
            ->count();

        $lateCount = $finalAttendance
            ->where('is_late', 1)
            ->count();

        $earlyExitCount = $finalAttendance
            ->where('is_early_exit', 1)
            ->count();

        // ===============================
        // 5. Absent Calculation (Optimized)
        // ===============================
        $absentCount = $employeeIds->count()
            - collect($employeePresentIds)
                ->merge($leaveEmployeeIds)
                ->unique()
                ->count();

        // ===============================
        // 6. Final Response
        // ===============================
        return [
            'result' => [
                'all_employee'     => $employeeIds->count(),
                'full_day_present' => $fullDayPresentCount,
                'half_day'         => $halfDayCount,
                'late'             => $lateCount,
                'early_exit'       => $earlyExitCount,
                'leave'            => $leaveEmployeeIds->count(),
                'absent'           => max($absentCount, 0),
            ],
            'status' => true,
        ];
    }

    public function dateWiseAttendanceSummaryList($date, $status)
    {
        $user = Auth::user();
        $parseDate = Carbon::parse($date);
        $result = collect();
        switch ($status) {
            case 1: // Late
                // 1️⃣ Logs (priority)
                $logQuery = AttendanceLog::with([
                    'fh_employee_real:emp_id,emp_full_name,emp_dg_id,emp_phone,emp_code',
                    'fh_employee_real.fh_designation:dg_id,dg_name',
                    'fh_attendance_status:m_id,m_name'
                ])->whereDate('al_date', $parseDate)
                    ->where([
                        'al_b_id' => $user->emp_b_id,
                        'al_is_late' => 1
                    ]);

                if ($user->emp_role_id != 1) {
                    $logQuery->whereHas(
                        'fh_employee_real',
                        fn($q) =>
                        $q->where('emp_supervisor_id', $user->emp_id)->where('emp_role_id', '!=', 1)
                    );
                }

                $logData = $logQuery->get();
                $logEmpIds = $logData->pluck('al_emp_id')->toArray();


                // 2️⃣ Records (fallback)
                $recordQuery = AttendanceRecord::with([
                    'fh_employee:emp_id,emp_full_name,emp_dg_id,emp_phone,emp_code',
                    'fh_employee.fh_designation:dg_id,dg_name',
                    'fh_attendance_status:m_id,m_name'
                ])->whereDate('atd_date', $parseDate)
                    ->where([
                        'atd_b_id' => $user->emp_b_id,
                        'atd_is_late' => 1
                    ])
                    ->whereNotIn('atd_emp_id', $logEmpIds);

                if ($user->emp_role_id != 1) {
                    $recordQuery->whereHas(
                        'fh_employee',
                        fn($q) =>
                        $q->where('emp_supervisor_id', $user->emp_id)->where('emp_role_id', '!=', 1)
                    );
                }

                $recordData = $recordQuery->get();

                $employees = $logData->merge($recordData);
                break;
            case 2: // Leave
                $employeesQuery = LeaveRequest::with([
                    'fh_employee:emp_id,emp_full_name,emp_dg_id,emp_phone,emp_code',
                    'fh_employee.fh_designation:dg_id,dg_name',
                    'fh_approval_status:m_id,m_name'
                ])->where('lvr_b_id', $user->emp_b_id)
                    ->where('lvr_stage_completed', 1)->where('lvr_status', '!=', 170)
                    ->whereDate('lvr_start_date', '<=', $parseDate)
                    ->whereDate('lvr_end_date', '>=', $parseDate);

                if ($user->emp_role_id != 1) {
                    $employeesQuery->whereHas('fh_employee', function ($q) use ($user) {
                        $q->where('emp_supervisor_id', $user->emp_id)->where('emp_role_id', '!=', 1);
                    });
                }

                $employees = $employeesQuery->get();
                break;
            case 203: // Absent
                $allActiveEmployeeIdsQuery = Employee::where('emp_b_id', $user->emp_b_id)
                    ->where('emp_status', 71)
                    ->where('emp_role_id', '!=', 1);

                if ($user->emp_role_id != 1) {
                    $allActiveEmployeeIdsQuery->where('emp_supervisor_id', $user->emp_id);
                }

                $allActiveEmployeeIds = $allActiveEmployeeIdsQuery->pluck('emp_id')->toArray();

                // 1️⃣ Attendance LOG present employees
                $logPresentQuery = AttendanceLog::where('al_b_id', $user->emp_b_id)
                    ->whereDate('al_date', $parseDate);

                if ($user->emp_role_id != 1) {
                    $logPresentQuery->whereHas('fh_employee_real', function ($q) use ($user) {
                        $q->where('emp_supervisor_id', $user->emp_id)
                            ->where('emp_role_id', '!=', 1);
                    });
                }

                $logPresentIds = $logPresentQuery
                    ->pluck('al_emp_id')
                    ->toArray();


                // 2️⃣ Attendance RECORD present employees (excluding log ones)
                $recordPresentQuery = AttendanceRecord::where('atd_b_id', $user->emp_b_id)
                    ->whereDate('atd_date', $parseDate)
                    ->whereNotIn('atd_emp_id', $logPresentIds);

                if ($user->emp_role_id != 1) {
                    $recordPresentQuery->whereHas('fh_employee', function ($q) use ($user) {
                        $q->where('emp_supervisor_id', $user->emp_id)
                            ->where('emp_role_id', '!=', 1);
                    });
                }

                $recordPresentIds = $recordPresentQuery
                    ->pluck('atd_emp_id')
                    ->toArray();


                // 3️⃣ Final present employee list
                $employeePresentIds = array_values(array_unique(
                    array_merge($logPresentIds, $recordPresentIds)
                ));

                $employeeLeaveQuery = LeaveRequest::where('lvr_b_id', $user->emp_b_id)
                    ->whereDate('lvr_start_date', '<=', $parseDate)
                    ->whereDate('lvr_end_date', '>=', $parseDate)
                    ->where('lvr_stage_completed', 1)->where('lvr_status', '!=', 170);

                if ($user->emp_role_id != 1) {
                    $employeeLeaveQuery->whereHas('fh_employee', function ($q) use ($user) {
                        $q->where('emp_supervisor_id', $user->emp_id)->where('emp_role_id', '!=', 1);
                    });
                }
                $employeeLeaveIds = $employeeLeaveQuery->pluck('lvr_emp_id')->toArray();

                $absentEmployeeIds = array_diff($allActiveEmployeeIds, array_merge($employeePresentIds, $employeeLeaveIds));

                $employeesQuery = Employee::whereIn('emp_id', $absentEmployeeIds)
                    ->select('emp_id', 'emp_full_name', 'emp_dg_id', 'emp_phone', 'emp_code')
                    ->with('fh_designation:dg_id,dg_name');

                if ($user->emp_role_id != 1) {
                    $employeesQuery->where('emp_supervisor_id', $user->emp_id)->where('emp_role_id', '!=', 1);
                }

                $employees = $employeesQuery->get();
                break;
            case 4: // Early Exit
                // 1️⃣ Logs (priority)
                $logQuery = AttendanceLog::with([
                    'fh_employee_real:emp_id,emp_full_name,emp_dg_id,emp_phone,emp_code',
                    'fh_employee_real.fh_designation:dg_id,dg_name',
                    'fh_attendance_status:m_id,m_name'
                ])->whereDate('al_date', $parseDate)
                    ->where([
                        'al_b_id' => $user->emp_b_id,
                        'al_is_early_exit' => 1
                    ]);

                if ($user->emp_role_id != 1) {
                    $logQuery->whereHas(
                        'fh_employee',
                        fn($q) =>
                        $q->where('emp_supervisor_id', $user->emp_id)->where('emp_role_id', '!=', 1)
                    );
                }

                $logData = $logQuery->get();
                $logEmpIds = $logData->pluck('al_emp_id')->toArray();


                // 2️⃣ Records (fallback)
                $recordQuery = AttendanceRecord::with([
                    'fh_employee:emp_id,emp_full_name,emp_dg_id,emp_phone,emp_code',
                    'fh_employee.fh_designation:dg_id,dg_name',
                    'fh_attendance_status:m_id,m_name'
                ])->whereDate('atd_date', $parseDate)
                    ->where([
                        'atd_b_id' => $user->emp_b_id,
                        'atd_is_early_exit' => 1
                    ])
                    ->whereNotIn('atd_emp_id', $logEmpIds);

                if ($user->emp_role_id != 1) {
                    $recordQuery->whereHas(
                        'fh_employee',
                        fn($q) =>
                        $q->where('emp_supervisor_id', $user->emp_id)->where('emp_role_id', '!=', 1)
                    );
                }

                $recordData = $recordQuery->get();

                $employees = $logData->merge($recordData);
                break;
            default: // Other attendance statuses
                $statusArr = [$status];
                if ($parseDate->isToday() && in_array(251, $statusArr)) {
                    $statusArr = [251, 228];
                }

                $logQuery = AttendanceLog::with([
                    'fh_employee_real:emp_id,emp_full_name,emp_dg_id,emp_phone,emp_code',
                    'fh_employee_real.fh_designation:dg_id,dg_name',
                    'fh_attendance_status:m_id,m_name'
                ])->where('al_b_id', $user->emp_b_id)
                    ->whereIn('al_attendance_status', $statusArr)
                    ->whereDate('al_date', $parseDate);

                if ($user->emp_role_id != 1) {
                    $logQuery->whereHas(
                        'fh_employee_real',
                        fn($q) =>
                        $q->where('emp_supervisor_id', $user->emp_id)->where('emp_role_id', '!=', 1)
                    );
                }

                $logData = $logQuery->get();
                $logEmpIds = $logData->pluck('al_emp_id')->toArray();

                $recordQuery = AttendanceRecord::with([
                    'fh_employee:emp_id,emp_full_name,emp_dg_id,emp_phone,emp_code',
                    'fh_employee.fh_designation:dg_id,dg_name',
                    'fh_attendance_status:m_id,m_name'
                ])->where('atd_b_id', $user->emp_b_id)
                    ->whereIn('atd_attendance_status', $statusArr)
                    ->whereDate('atd_date', $parseDate)
                    ->whereNotIn('atd_emp_id', $logEmpIds);

                if ($user->emp_role_id != 1) {
                    $recordQuery->whereHas(
                        'fh_employee',
                        fn($q) =>
                        $q->where('emp_supervisor_id', $user->emp_id)->where('emp_role_id', '!=', 1)
                    );
                }

                $recordData = $recordQuery->get();

                $employees = $logData->merge($recordData);
                break;
        }

        $result = $employees->map(function ($record) use ($status) {

            $isLog = isset($record->al_emp_id); // LOG vs RECORD

            $employee = $status == 203
                ? $record
                : $record->fh_employee;

            if ($status != 203 && $isLog) {
                $employee = $record->fh_employee_real;
            }

            $data = [
                'employee_code'  => $employee->emp_code ?? 'N/A',
                'employee_name'  => $employee->emp_full_name ?? 'N/A',
                'employee_phone' => $employee->emp_phone ?? 'N/A',
                'designation'    => $employee->fh_designation
                    ? DesignationResource::collection([$employee->fh_designation])
                    : [],
                'attendance_status' => $status == 203
                    ? MasterTableResource::collection([MasterTable::where('m_id', 203)->first()])
                    : ($record->fh_attendance_status
                        ? MasterTableResource::collection([$record->fh_attendance_status])
                        : []),
            ];

            // --------------------
            // ATTENDANCE DATA
            // --------------------
            $attendanceData = [
                'attendance_data' => [],
                'attendance_status' => [],
            ];

            // 🟢 FULL DATA (AttendanceRecord)
            if (!$isLog && isset($record->atd_id)) {

                $attendanceData['attendance_data'] = [[
                    'b_id' => $record->atd_b_id,
                    'emp_id' => $record->atd_emp_id,
                    'date' => $record->atd_date ? Carbon::parse($record->atd_date)->format('d M, Y') : '',
                    'check_in_time' => $record->atd_check_in_time ? Carbon::parse($record->atd_check_in_time)->format('h:i A') : '',
                    'check_out_time' => $record->atd_check_out_time ? Carbon::parse($record->atd_check_out_time)->format('h:i A') : '',
                    'total_worked_hours' => $record->atd_total_worked_hours ? number_format($record->atd_total_worked_hours, 2) : '',
                    'is_late' => $record->atd_is_late,
                    'late_duration' => $record->atd_late_duration ? number_format($record->atd_late_duration, 2) : '',
                    'is_absent' => $record->atd_is_absent,
                    'is_overtime' => $record->atd_is_overtime,
                    'overtime_hours' => $record->atd_overtime_hours ? number_format($record->atd_overtime_hours, 2) : 0,
                    'punchin_location' => $record->atd_punchin_location ?? '',
                    'punchout_location' => $record->atd_punchout_location ?? '',
                    'punchin_photo' => json_decode($record->atd_punchin_photo, true) ?? [],
                    'punchout_photo' => json_decode($record->atd_punchout_photo, true) ?? [],
                ]];

                $attendanceData['attendance_status'] =
                    $record->fh_attendance_status
                    ? MasterTableResource::collection([$record->fh_attendance_status])
                    : [];
            }

            // 🟡 PARTIAL DATA (AttendanceLog)
            if ($isLog) {

                $attendanceData['attendance_data'] = [[
                    'b_id' => $record->al_b_id,
                    'emp_id' => $record->al_emp_id,
                    'date' => $record->al_date ? Carbon::parse($record->al_date)->format('d M, Y') : '',
                    'check_in_time' => $record->al_check_in_time
                        ? Carbon::parse($record->al_check_in_time)->format('h:i A')
                        : '',
                    'check_out_time' => $record->al_check_out_time
                        ? Carbon::parse($record->al_check_out_time)->format('h:i A')
                        : '',
                    'is_late' => $record->al_is_late ?? 0,
                    'is_early_exit' => $record->al_is_early_exit ?? 0,
                    'source' => 'log', // 🔥 optional but gold
                ]];

                $attendanceData['attendance_status'] =
                    $record->fh_attendance_status
                    ? MasterTableResource::collection([$record->fh_attendance_status])
                    : [];
            }

            return array_merge($data, $attendanceData);
        });

        return [
            'result' => $result,
            'status' => true,
        ];
    }

    // New Method
    public function totalAttendance()
    {
        $user = Auth::user();
        $emp_id = $user->emp_id;
        $currentMonth = now()->format('Y-m');
        [$year, $month] = explode('-', $currentMonth);
        $emp = Employee::where('emp_id', $emp_id)->first();

        $weekOfDates = CentralLogics::getWeekOffDates($emp, $year, $month);

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();

        $attendanceData = CentralLogics::getMonthlyAttendanceCount($emp, $year, $month, $weekOfDates);

        $startDate->addDay();

        return response()->json([
            'result' =>
            [[
                'present_count' => $attendanceData['presentCount'],
                'absentCount' => $attendanceData['absentCount'],
                'leaveCount' => $attendanceData['leaveCount'],
                'late_count' => $attendanceData['lateCount'],
            ]],
            'status' => true
        ]);
    }

    public function attendanceLog(Request $request)
    {
        // Find the employee by ID
        $user = Auth::user();
        $emp = Employee::find($user->emp_id);
        $month = $request->month ??  Carbon::now()->month;
        $year = $request->year ??  Carbon::now()->year;

        $formatted_month = str_pad($month, 2, '0', STR_PAD_LEFT);

        $year_month = "$year-$formatted_month";

        if (!$emp) {
            return response()->json(['error' => 'Employee not found'], 404);
        }
        // Determine the month and year to filter by
        $currentMonth = $year_month ?: now()->format('Y-m');
        // return  $currentMonth;
        [$year, $month] = explode('-', $currentMonth);

        // Generate start and end dates of the selected month
        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        $totalDays = $startDate->daysInMonth;

        // Initialize counters
        $presentCount = $leaveCount = $approvedLeaveCount = $holidayCount = 0;
        $weekOffCount = $absentCount = $halfDayCount = $missedPunchCount = 0;
        $approvedMissedPunchCount = $overtimeCount = $lateCount = $earlyExitCount = 0;

        // Fetch week off dates
        $weekOfDates = CentralLogics::getWeekOffDates($emp, $year, $month);

        $status_type = MasterTable::where('m_group', 'ATTENDANCE_STATUS')->get();
        $status_type = MasterTableResource::collection($status_type);


        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $dateRange = $startDate->toPeriod($endDate);

        $holiday_record_exits = PolicyHolidayList::where('phl_b_id', $user->emp_b_id)->where('phl_day_type_id', 201)->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('phl_start_date', [$startDate, $endDate])
                ->orWhereBetween('phl_end_date', [$startDate, $endDate]);
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

        // Accumulate counts
        $attendanceData = CentralLogics::newGetMonthlyAttendanceDetails($emp, $month, $year, $holidaysByDate, $weekOfDates);

        // Accumulate counts
        $presentCount = collect($attendanceData)->sum('presentCount');
        $ODCount = collect($attendanceData)->sum('ODCount');
        $leaveCount = collect($attendanceData)->sum('leaveCount');
        $absentCount = collect($attendanceData)->sum('absentCount');
        $halfDayCount = collect($attendanceData)->sum('halfDayCount');
        $missedPunchCount = collect($attendanceData)->sum('missedPunchCount');
        $overtimeCount = collect($attendanceData)->sum('overtimeCount');
        $lateCount = collect($attendanceData)->sum('lateCount');
        $earlyExitCount = collect($attendanceData)->sum('earlyExitCount');
        $holidayCount = collect($attendanceData)->sum('holidayCount');
        $approvedLeaveCount = collect($attendanceData)->sum('approvedLeaveCount');
        $approvedMissedPunchCount = collect($attendanceData)->sum('approvedMissedPunchCount');

        // Filter unwanted keys from each day's data
        $unwantedKeys = [
            'presentCount',
            'ODCount',
            'leaveCount',
            'holidayCount',
            'weekOffCount',
            'absentCount',
            'halfDayCount',
            'missedPunchCount',
            'overtimeCount',
            'lateCount',
            'earlyExitCount',
            'approvedLeaveCount',
            'approvedMissedPunchCount',
            'isApproved',
        ];

        $monthlyAttendanceData = [];
        foreach ($attendanceData as $dayData) {
            // Ensure date exists in data
            $date = isset($dayData['date']) ? $dayData['date'] : null;

            if ($date) {
                $carbonDate = \Carbon\Carbon::parse($date); // Convert to Carbon object for formatting

                $monthDetails = [
                    'date' => $carbonDate->format('Y-m-d'),
                    'user_friendly_date' => $carbonDate->format('d-M-Y'),
                    'day' => $carbonDate->format('l')
                ];

                $filteredArray = array_diff_key($dayData, array_flip($unwantedKeys));
                $finalArray = array_merge($monthDetails, $filteredArray);

                $monthlyAttendanceData[] = $finalArray;
            }
        }


        // Calculate the number of week-offs
        $weekOffCount = !empty($weekOfDates) ? count($weekOfDates) : 0;

        return response()->json([
            'result' => [
                [
                    'b_id' => $emp->emp_b_id,
                    'emp_id' => $emp->emp_id,
                    'presentCount' => (string) $presentCount,
                    'ODCount' => (string) $ODCount,
                    'absentCount' => (string) $absentCount,
                    'missedPunchCount' => (string) $missedPunchCount,
                    'overtimeCount' => (string) $overtimeCount,
                    'lateCount' => (string) $lateCount,
                    'earlyExitCount' => (string)  $earlyExitCount,
                    'leaveCount' => (string) $leaveCount,
                    'halfDayCount' => (string) $halfDayCount,
                    'weekOffCount' => (string) $weekOffCount,
                    'holidayCount' => (string) $holidayCount,
                    'monthFilter' => (string) $currentMonth,
                    'monthlyAttendanceData' => $monthlyAttendanceData,
                    'totalDays' => (string)  $totalDays,
                    'totalWorkedDay' => (string) ($presentCount + $ODCount + $weekOffCount + $holidayCount + $approvedLeaveCount + $approvedMissedPunchCount),
                    'status_type' => $status_type,
                ]
            ],
            'status' => true
        ]);
    }

    public function getEmployeeByFaceId($faceId)
    {
        try {
            $response = ['status' => false, 'message' => 'Something went wrong', 'data' => null];
            $employee = Employee::where('emp_rekognition_id', $faceId)->first();
            if ($employee) {
                $result = $this->markAttendanceByFaceDetection($employee);
                $response['status'] = true;
                $response['message'] = 'Employee found.';
                $response['data'] = [
                    'employeeId' => $employee->emp_code ?? '',
                    'name' => $employee->emp_full_name ?? '',
                    'department' => $employee->fh_department?->d_name,
                    'designation' => $employee->fh_designation?->dg_name,
                    'email' => $employee->emp_email ?? '',
                    'phone' => $employee->emp_phone ?? '',
                    'address' => $employee->emp_permanent_address ?? '',
                    'attendance_status' => $result['status'],
                    'attendance_message' => $result['message'],
                ];
            } else {
                $response['message'] = 'Employee not found.';
            }
        } catch (Exception $e) {
            $response['status'] = 500;
            $response['message'] = $e->getMessage();
        }
        return response()->json($response);
    }

    public function markAttendanceByFaceDetection($employee, $image_url = NULL)
    {
        $response = ['status' => true, 'message' => '', 'data' => null];
        try {
            $today = now();
            $currentTime = now()->format('Y-m-d H:i:s');

            // Define constants for readability
            $WORK_MODE_OFFICE = 62;
            $CHECKIN_METHOD_FACE_DETECTION = 316;

            // ==================================== New Logic ====================================
            // ---- 0) Load shift policy via resolver --------------------------------
            $shift = ShiftResolver::resolveEmployeeShift($employee, $today->format('Y-m-d'));
            $shiftPolicy = $shift ? $shift->shift : null;
            $endNextDay = $shiftPolicy ? (int)($shiftPolicy->pst_end_next_day) : 0;
            $endBy = $shiftPolicy && $endNextDay ? Carbon::parse($shiftPolicy->pst_end_by) : null;

            if ($endNextDay && $endBy && $today->lte($endBy)) {
                $today = now()->subDay();
            }
            $todayDate = $today->copy()->toDateString();

            $shiftTiming = $shift ? $shift->shift : null;
            if (!$shiftTiming) {
                return response()->json(['status' => false, 'result' => [], 'message' => 'Shift timing settings are missing.']);
            }

            // ---------------------------------------------------------
            // 1) Load base shift timings
            // ---------------------------------------------------------
            $shiftStartTime = $shiftTiming->pst_start_time;
            $shiftEndTime   = $shiftTiming->pst_end_time;

            // ---------------------------------------------------------
            // 2) Resolve partial day overrides (if any)
            // ---------------------------------------------------------
            $dayName     = Carbon::parse($today)->format('l');     // e.g. Monday
            $weekOfMonth = Carbon::parse($today)->weekOfMonth;     // e.g. 2 (2nd week)

            // Partial Day 1
            if (
                $shiftTiming->pst_allow_partial_day == 1
                && $shiftTiming->fh_master_table_week
                && $shiftTiming->fh_master_table_week->m_name === $dayName
                && (int)$shiftTiming->week_off === $weekOfMonth
            ) {
                $shiftStartTime = $shiftTiming->pst_partial_day_begin_time;
                $shiftEndTime   = $shiftTiming->pst_partial_day_end_time;
            }

            // Partial Day 2
            elseif (
                $shiftTiming->pst_allow_partial_day2 == 1
                && $shiftTiming->fh_master_table_week2
                && $shiftTiming->fh_master_table_week2->m_name === $dayName
                && (int)$shiftTiming->week_off2 === $weekOfMonth
            ) {
                $shiftStartTime = $shiftTiming->pst_partial_day_begin_time2;
                $shiftEndTime   = $shiftTiming->pst_partial_day_end_time2;
            }

            // Normalize with date
            $shiftStart = Carbon::parse($todayDate . ' ' . Carbon::parse($shiftStartTime)->format('H:i:s'));
            $shiftEnd   = Carbon::parse($todayDate . ' ' . Carbon::parse($shiftEndTime)->format('H:i:s'));
            if ($shiftEnd->lessThan($shiftStart)) {
                $shiftEnd->addDay(); // overnight shifts
            }

            // ---------------------------------------------------------
            // 3) Validate Punch-In time
            // ---------------------------------------------------------
            $attendanceRec = AttendanceRecord::where([
                'atd_b_id' => $employee->emp_b_id,
                'atd_emp_id' => $employee->emp_id,
                'atd_date' => $todayDate,
            ])->get();

            if (!isset($attendanceRec) && empty($attendanceRec)) {
                $checkIn = Carbon::parse($currentTime);

                // if early punch-in control is enabled
                if ((int)$shiftTiming->pst_allow_punch_begin_before === 1) {
                    $earliestCheckIn = $shiftStart->copy()->subMinutes((int)$shiftTiming->pst_mins_punch_begin_before);

                    if ($checkIn->lessThan($earliestCheckIn)) {
                        return [
                            'status'  => false,
                            'result'  => [],
                            'message' => 'Check-in can only be done after ' . $earliestCheckIn->format('h:i A'),
                        ];
                    }
                }
            } else {
                $checkOut = Carbon::parse($currentTime);
                // if late punch-out control is enabled
                if ((int)$shiftTiming->pst_allow_punch_end_after === 1) {
                    $lateCheckOut = $shiftEnd->copy()->addMinutes((int)$shiftTiming->pst_mins_punch_end_after);

                    if ($checkOut->greaterThan($lateCheckOut)) {
                        return [
                            'status'  => false,
                            'result'  => [],
                            'message' => 'Check-out window closed at ' . $lateCheckOut->format('h:i A'),
                        ];
                    }
                }
            }

            // Check if attendance record exists
            $attendance = AttendanceRecord::firstOrCreate(
                [
                    'atd_b_id' => $employee->emp_b_id,
                    'atd_emp_id' => $employee->emp_id,
                    'atd_date' => $todayDate,
                ],
                [
                    'atd_work_mode_type_id' => $WORK_MODE_OFFICE,
                    'atd_checkin_method_id' => $CHECKIN_METHOD_FACE_DETECTION,
                    'atd_check_in_time' => $currentTime,
                ]
            );

            // Handle first-time check-in scenario
            if ($attendance->wasRecentlyCreated) {
                $attendance->atd_check_in_time = $currentTime;
                $type = 'check-in';
                $attendance->atd_punchin_photo = json_encode([$image_url]);
                // $attendance->atd_latitude_punchin = session('latitude') ?? null;
                // $attendance->atd_longitude_punchin = session('longitude') ?? null;
            } else {
                $attendance->atd_check_out_time = $currentTime;
                $type = 'check-out';
                $attendance->atd_punchout_photo = json_encode([$image_url]);
                // $attendance->atd_latitude_punchout = session('latitude') ?? null;
                // $attendance->atd_longitude_punchout = session('longitude') ?? null;
            }

            if ($shiftTiming->pst_type_id == 245) {
                $result = $this->handleAttendance2($attendance, $employee, $type);
            } else {
                $result = $this->handleAttendance($attendance, $employee, $type);
            }

            $responseData = json_decode($result->getContent(), true);
            $response['status'] = $responseData['status'];
            if (!$responseData['status']) {
                $response['message'] = isset($responseData['message']) ? $responseData['message'] : 'something went wrong.';
            } else {
                $response['message'] =  'Your attendance is marked.';
            }
        } catch (Exception $e) {
            $response['status'] = false;
            $response['message'] = $e->getMessage();
        }

        return $response;
    }

    // private function createAttendanceSession($data)
    // {
    //     AttendanceSession::create($data);
    // }

    public function registerOfflineDevice(Request $request)
    {
        $user = Auth::user();
        // Validation
        $data = $request->validate([
            'device_model'  => 'required|string',
            'mac_address'   => 'nullable|string',
            'device_pin'    => 'nullable|string',
        ]);
        // If both are empty → reject
        if (empty($data['mac_address']) && empty($data['device_pin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Either MAC address or device PIN is required.',
            ], 422);
        }
        // MAC Normalize Function
        $normalizeMac = function ($mac) {
            return strtoupper(preg_replace('/[^A-Fa-f0-9]/', '', $mac));
        };
        $requestedMac = !empty($data['mac_address']) ? $normalizeMac($data['mac_address']) : null;
        // Check if device already registered
        $isDeviceRegistered = OfflineDeviceRegistration::where('odr_b_id', $user->emp_b_id)
            ->where(function ($q) use ($requestedMac, $data) {
                if (!empty($requestedMac)) {
                    $q->whereRaw("REPLACE(UPPER(odr_mac_address), ':', '') = ?", [$requestedMac]);
                }
                if (!empty(trim($data['device_pin']))) {
                    $q->orWhere('odr_device_pin', trim($data['device_pin']));
                }
            })
            ->exists();
        if ($isDeviceRegistered) {
            return response()->json([
                'success' => false,
                'message' => 'This device is already registered.',
            ], 409);
        }
        // Save Data
        $payload = [
            'odr_b_id'          => $user->emp_b_id,
            'odr_device_model'  => $data['device_model'],
            'odr_mac_address'   => $data['mac_address'],
            'odr_registered_by' => $user->emp_id,
            'odr_device_pin'    => $data['device_pin'],
        ];
        $record = OfflineDeviceRegistration::create($payload);
        return response()->json([
            'success' => true,
            'message' => 'Offline device registered successfully',
            'data'    => $record,
        ]);
    }

    public function syncOfflineAttendance(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'emp_code'              => 'required',
                'emp_b_id'              => 'required|integer',
                'check_in_time'         => 'required|date_format:d-m-Y H:i:s',
                'check_out_time'        => 'nullable|date_format:d-m-Y H:i:s|after:check_in_time',
                'am_id'                 => 'nullable|integer',
                'atd_checkin_method_id' => 'required|integer',
                'mac_address' => 'nullable|string|required_without:device_pin',
                'device_pin'  => 'nullable|string|required_without:mac_address',
            ]);
            // dd('Validated Data:', $validatedData);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Failed',
                'errors'  => $e->errors(),
            ], 422);
        }
        $normalizeMac = function ($mac) {
            return strtoupper(preg_replace('/[^A-Fa-f0-9]/', '', $mac));
        };
        $isDeviceRegistered = OfflineDeviceRegistration::where('odr_b_id', $request->emp_b_id)
            ->where(function ($q) use ($request) {
                if (!empty($request->mac_address)) {
                    $q->where('odr_mac_address', strtolower(trim($request->mac_address)));
                }
                if (!empty($request->device_pin)) {
                    $q->orWhere('odr_device_pin', trim($request->device_pin));
                }
            })
            ->exists();
        if (!$isDeviceRegistered) {
            return response()->json([
                'success' => false,
                'message' => 'Device not matched. Please check MAC address or PIN.',
            ], 403);
        }
        $employeeId  = Employee::where('emp_code', $request->emp_code)->value('emp_id');
        $employeeCode  = $request->emp_code;
        $businessId  = $request->emp_b_id;
        $checkInTime  = \Carbon\Carbon::createFromFormat('d-m-Y H:i:s', $request->check_in_time)->format('Y-m-d H:i:s');
        $checkOutTime = $request->check_out_time
            ? \Carbon\Carbon::createFromFormat('d-m-Y H:i:s', $request->check_out_time)->format('Y-m-d H:i:s')
            : null;
        $currentDate  = \Carbon\Carbon::createFromFormat('d-m-Y H:i:s', $request->check_in_time)->format('Y-m-d');
        // 🔍 Check Existing Record
        $existing = AttendanceRecord::where([
            'atd_b_id'   => $businessId,
            'atd_emp_id' => $employeeId,
            'atd_date'   => $currentDate
        ])->first();

        // ------------------------------------------
        // 📌 Case 1: Record exists (do NOT update check-in)
        // ------------------------------------------
        if ($existing) {
            $updateData = [
                'atd_checkin_method_id' => $request->atd_checkin_method_id,
                'atd_am_id'             => $request->am_id,
                'atd_is_absent'         => false,
            ];
            // 👉 Check-out tabhi update hoga JAB existing check-in time ho
            if ($checkOutTime && $existing->atd_check_in_time) {
                $updateData['atd_check_out_time'] = $checkOutTime;
                // dd($existing,$checkOutTime, $existing->atd_check_in_time, $updateData);
            }
            $existing->update($updateData);
            $user = Employee::where('emp_id', $employeeId)
                ->where('emp_b_id', $businessId)
                ->first();
            return $this->handleAttendance($existing, $user, 'check-out');
        }

        //  ------------------------------------------
        // 📌 Case 2: Create new record (check-in)
        // ------------------------------------------
        $attendanceRecord = AttendanceRecord::create([
            'atd_b_id'               => $businessId,
            'atd_emp_id'             => $employeeId,
            'atd_date'               => $currentDate,
            'atd_check_in_time'      => $checkInTime,
            'atd_check_out_time'     => $checkOutTime,
            'atd_is_absent'          => false,
            'atd_checkin_method_id'  => $request->atd_checkin_method_id,
            'atd_am_id'              => $request->am_id,
        ]);
        $user = Employee::where('emp_id', $employeeId)->where('emp_b_id', $businessId)->first();
        return $this->handleAttendance($attendanceRecord, $user, 'check-in');
    }

    public function terminalServices()
    {
        $response['data'] = [
            'employeeId' => '1234',
            'b_id' => '12',
            'name' => 'Testing',
            'email' => 'tester@test.com',
        ];
        return response()->json([
            'status'  => true,
            'message' => 'Attendance  data fetched',
            'result'    => [$response]
        ]);
    }

    // public function storeDevice(Request $request)
    // {
    //     Log::info('storeDevice() request received', [
    //         'data' => $request->all(),
    //         'ip'   => $request->ip(),
    //         'user' => Auth::check() ? Auth::user()->emp_id : null
    //     ]);
    //     try {
    //         /**
    //          * ===============================================================
    //          *  SYSTEM SYNC MODE (from queued job)
    //          * ===============================================================
    //          */
    //         if ($request->input('is_system_sync') === true) {
    //             Log::info("System sync mode detected");
    //             $employee = Employee::where('emp_b_id', $request->business_id)
    //                 ->where('emp_code', $request->emp_code)
    //                 ->first();
    //             if (!$employee) {
    //                 return response()->json([
    //                     'status' => false,
    //                     'message' => "Employee not found"
    //                 ], 404);
    //             }
    //             Auth::shouldUse('web');
    //             Auth::guard('web')->setUser($employee);  // ⭐ FIXED
    //             Log::info("System Sync Authenticated Employee", [
    //                 'emp_id'      => $employee->emp_id,
    //                 'business_id' => $employee->emp_b_id
    //             ]);
    //         }
    //         /**
    //          * 1️⃣ Validate check-in method
    //          */
    //         $checkinMethodId = $request->input('atd_checkin_method_id');
    //         if (is_null($checkinMethodId)) {
    //             Log::warning('Missing check-in method');
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Check-in method is required'
    //             ], 400);
    //         }
    //         /**
    //          * 2️⃣ Get Authenticated Employee
    //          */
    //         $user = Auth::user();
    //         if (!$user) {
    //             Log::warning('Unauthenticated request in storeDevice()');
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'User not authenticated'
    //             ], 401);
    //         }
    //         /**
    //          * 3️⃣ Approval mapping
    //          */
    //         $amId = null;
    //         $ruleCriteria = RuleCriterion::with('fh_approval_module')
    //             ->where('rc_b_id', $user->emp_b_id)
    //             ->where('rc_condition_option_id', 140)
    //             ->whereHas('fh_approval_module', function ($query) {
    //                 $query->where('am_module_id', 249)->where('am_status', 1);
    //             })
    //             ->first();
    //         $processApprovers = [];
    //         if ($ruleCriteria && $ruleCriteria->fh_approval_module) {
    //             $processApprovers = $ruleCriteria->fh_approval_module
    //                 ->filteredProcessApprovers($user->emp_b_id)
    //                 ->get();
    //         }
    //         if (count($processApprovers)) {
    //             $amId = $ruleCriteria->rc_am_id;
    //         } else {
    //             $approvalMapping = ApprovalHelper::getApprovalMapping(
    //                 $user->emp_b_id,
    //                 $user->emp_id,
    //                 249
    //             );
    //             if (!$approvalMapping) {
    //                 return response()->json([
    //                     'result' => [],
    //                     'status' => false,
    //                     'message' => 'No approval settings for attendance module. Contact admin.'
    //                 ]);
    //             }
    //         }
    //         $request->merge(['am_id' => $amId]);
    //         /**
    //          * 4️⃣ Holiday Check
    //          */
    //         $atd_date = $request->atd_check_in_date
    //             ? Carbon::parse($request->atd_check_in_date)->format('Y-m-d')
    //             : now()->format('Y-m-d');
    //         $isHoliday = PolicyHolidayList::where('phl_b_id', $user->emp_b_id)
    //             ->whereDate('phl_start_date', '<=', $atd_date)
    //             ->whereDate('phl_end_date', '>=', $atd_date)
    //             ->first();
    //         if ($isHoliday && $isHoliday->phl_type_id == 205) {
    //             return response()->json([
    //                 'status' => false,
    //                 'result' => [],
    //                 'message' => "You can't check in today because it's a public holiday."
    //             ]);
    //         }
    //         /**
    //          * 5️⃣ Leave Check
    //          */
    //         $leaveApplied = LeaveRequest::where('lvr_emp_id', $user->emp_id)
    //             ->whereDate('lvr_start_date', '<=', $atd_date)
    //             ->whereDate('lvr_end_date', '>=', $atd_date)
    //             ->first();
    //         if ($leaveApplied && $leaveApplied->fh_leave_day_type->m_id == 201) {
    //             return response()->json([
    //                 'status' => false,
    //                 'result' => [],
    //                 'message' => "You can't check in due to full-day leave."
    //             ]);
    //         }
    //         /**
    //          * 6️⃣ Route by method
    //          */
    //         switch ($checkinMethodId) {
    //             case 315:
    //                 return $this->handleCheckinWithDeviceMultiple($request);
    //             default:
    //                 Log::warning("Invalid check-in method", ['id' => $checkinMethodId]);
    //                 return response()->json([
    //                     'status' => false,
    //                     'message' => 'Invalid check-in method'
    //                 ], 400);
    //         }
    //     } catch (Exception $e) {
    //         Log::error('storeDevice() failed', [
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString()
    //         ]);
    //         return response()->json([
    //             'result' => [],
    //             'status' => false,
    //             'error'  => $e->getMessage()
    //         ], 500);
    //     }
    // }

    // public function syncAttendance(Request $request)
    // {
    //     Log::info('syncAttendance request', $request->all());
    //     $request->validate([
    //         'device_sn' => 'required|string',
    //         'records'   => 'required|array',
    //     ]);
    //     $devices = DeviceManagement::where('serial_name', $request->device_sn)->get();
    //     if ($devices->isEmpty()) {
    //         Log::error("Device not found: {$request->device_sn}");
    //         return response()->json(['status' => 'error', 'message' => 'Device not found'], 404);
    //     }
    //     $businessIds = $devices->pluck('b_id')->toArray();
    //     $actualDevice = $devices->first();
    //     foreach ($request->records as $record) {
    //         try {
    //             $empCode = $record['emp_code'];
    //             $employee = Employee::whereIn('emp_b_id', $businessIds)
    //                 ->where('emp_code', $empCode)
    //                 ->first();
    //             if (!$employee) {
    //                 Log::warning('Employee not found for attendance record', [
    //                     'emp_code' => $empCode,
    //                     'business_ids' => $businessIds,
    //                 ]);
    //                 continue;
    //             }
    //             Auth::login($employee);
    //             $fakeRequest = new Request([
    //                 'atd_checkin_method_id' => 315,
    //                 'device_sn'             => $request->device_sn,
    //                 'atd_device_id'         => $actualDevice->id,
    //                 'timestamp'             => $record['timestamp'],
    //             ]);
    //             $this->storeDevice($fakeRequest);
    //         } catch (Exception $e) {
    //             Log::error('syncAttendance processing error', [
    //                 'error' => $e->getMessage(),
    //                 'record' => $record,
    //             ]);
    //         }
    //     }
    //     return response()->json(['status' => true, 'message' => 'Sync complete for multiple businesses']);
    // }

    // private function handleCheckinWithDeviceMultiple(Request $request)
    // {
    //     Log::info('handleCheckinWithDeviceMultiple', $request->all());
    //     try {
    //         $deviceSn = $request->input('device_sn');
    //         $timestamp = $request->input('timestamp');
    //         $checkinMethodId = $request->input('atd_checkin_method_id');
    //         // Fetch all devices with this serial number
    //         $devices = DeviceManagement::where('serial_name', $deviceSn)->get();
    //         if ($devices->isEmpty()) {
    //             Log::error('Device not found', ['device_sn' => $deviceSn]);
    //             return response()->json(['status' => false, 'message' => 'Device not registered'], 404);
    //         }
    //         $actualDevice = $devices->first();
    //         $businessIds = $devices->pluck('b_id')->toArray();
    //         $user = Auth::user();
    //         $employeeId = $user->emp_id;
    //         $empCode = $user->emp_code;
    //         $date = \Carbon\Carbon::parse($timestamp)->format('Y-m-d');
    //         $currentTime = \Carbon\Carbon::parse($timestamp)->format('Y-m-d H:i:s');
    //         // Try to find employee for any of the businesses linked to the device
    //         $employee = Employee::whereIn('emp_b_id', $businessIds)
    //             ->where('emp_code', $empCode)
    //             ->first();
    //         if (!$employee) {
    //             Log::warning('Employee not found for any business of device', [
    //                 'emp_code' => $empCode,
    //                 'business_ids' => $businessIds,
    //             ]);
    //             return response()->json(['status' => false, 'message' => 'Employee not found for this device'], 404);
    //         }
    //         $bId = $employee->emp_b_id;
    //         $actualDeviceid = DeviceManagement::where('b_id', $bId)->first()->id;
    //         // Check or create attendance record
    //         $attendance = AttendanceRecord::firstOrCreate(
    //             [
    //                 'atd_emp_id' => $employeeId,
    //                 'atd_date'   => $date,
    //                 'atd_b_id'   => $bId,
    //             ],
    //             [
    //                 'atd_am_id'             => $request->input('am_id'),
    //                 'atd_work_mode_type_id' => $request->input('atd_work_mode_type_id'),
    //                 'atd_checkin_method_id' => $checkinMethodId,
    //                 'atd_device_id'         => $actualDeviceid,
    //             ]
    //         );
    //         // Determine check-in or check-out
    //         if ($attendance->wasRecentlyCreated || empty($attendance->atd_check_in_time)) {
    //             $attendance->atd_check_in_time = $currentTime;
    //             $type = 'check-in';
    //         } else {
    //             $existingCheckIn = \Carbon\Carbon::parse($attendance->atd_check_in_time);
    //             $newCheckOut = \Carbon\Carbon::parse($currentTime);
    //             if ($existingCheckIn->equalTo($newCheckOut) || $existingCheckIn->diffInSeconds($newCheckOut) < 60) {
    //                 $type = 'check-in'; // Don't update checkout if too close
    //             } else {
    //                 $attendance->atd_check_out_time = $currentTime;
    //                 $type = 'check-out';
    //             }
    //         }
    //         $attendance->atd_segments = json_encode([
    //             'device_sn' => $deviceSn,
    //             'method' => 'device',
    //         ]);
    //         $attendance->save();
    //         // Handle any post-processing
    //         $this->handleAttendance($attendance, $user, $type);
    //         $attendance->refresh();
    //         return ReturnHelper::jsonApiReturn(AttendanceResource::collection([$attendance]));
    //     } catch (\Exception $e) {
    //         Log::error('Error in handleCheckinWithDeviceMultiple', [
    //             'message' => $e->getMessage(),
    //             'trace'   => $e->getTraceAsString(),
    //         ]);
    //         return response()->json(['status' => false, 'message' => 'Something went wrong while handling device check-in'], 500);
    //     }
    // }

    public function syncAttendance(Request $request)
    {
        Log::info('syncAttendance request', $request->all());
        $request->validate([
            'device_sn' => 'required|string',
            'records'   => 'required|array',
        ]);
        $devices = DeviceManagement::where('serial_name', $request->device_sn)->get();
        if ($devices->isEmpty()) {
            Log::error("Device not found: {$request->device_sn}");
            return response()->json(['status' => 'error', 'message' => 'Device not found'], 404);
        }
        $businessIds = $devices->pluck('b_id')->toArray();
        $actualDevice = $devices->first();
        foreach ($request->records as $record) {
            try {
                $empCode = $record['emp_code'];
                $employee = Employee::whereIn('emp_b_id', $businessIds)
                    ->where('emp_code', $empCode)
                    ->first();
                if (!$employee) {
                    Log::warning('Employee not found for attendance record', [
                        'emp_code' => $empCode,
                        'business_ids' => $businessIds,
                    ]);
                    continue;
                }
                Auth::login($employee);
                $fakeRequest = new Request([
                    'atd_checkin_method_id' => 315,
                    'device_sn'             => $request->device_sn,
                    'atd_device_id'         => $actualDevice->id,
                    'timestamp'             => $record['timestamp'],
                ]);
                $this->storeDevice($fakeRequest);
            } catch (Exception $e) {
                Log::error('syncAttendance processing error', [
                    'error' => $e->getMessage(),
                    'record' => $record,
                ]);
            }
        }
        return response()->json(['status' => true, 'message' => 'Sync complete for multiple businesses']);
    }

    public function storeDevice(Request $request)
    {

        Log::info("System sync mode detected ", ['request' => $request->all()]);
        try {
            /**
             * ===============================================================
             *  SYSTEM SYNC MODE (from queued job)
             * ===============================================================
             */
            if ($request->boolean('is_system_sync')) {

                Log::info("System sync mode detected");

                $employee = Employee::where('emp_b_id', $request->business_id)
                    ->where('emp_code', $request->emp_code)
                    ->first();

                if (!$employee) {
                    return response()->json([
                        'status' => false,
                        'message' => "Employee not found"
                    ], 404);
                }

                Auth::shouldUse('web');
                Auth::guard('web')->setUser($employee);

                Log::info("System Sync Authenticated Employee", [
                    'emp_id' => $employee->emp_id,
                    'business_id' => $employee->emp_b_id
                ]);
            }

            /**
             * Validate check-in method
             */
            $checkinMethodId = $request->input('atd_checkin_method_id');
            if (is_null($checkinMethodId)) {
                Log::warning('Missing check-in method');
                return response()->json([
                    'status' => false,
                    'message' => 'Check-in method is required'
                ], 400);
            }

            /**
             * Authenticated Employee
             */
            $user = Auth::user() ?? Employee::where('emp_code', $request->emp_code)->where('emp_status', 71)->first();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }
            /**
             * Approval mapping
             */
            $amId = null;
            $ruleCriteria = RuleCriterion::with('fh_approval_module')
                ->where('rc_b_id', $user->emp_b_id)
                ->where('rc_condition_option_id', 140)
                ->whereHas('fh_approval_module', function ($query) {
                    $query->where('am_module_id', 249)->where('am_status', 1);
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
                $approvalMapping = ApprovalHelper::getApprovalMapping(
                    $user->emp_b_id,
                    $user->emp_id,
                    249
                );
                if (!$approvalMapping) {
                    return response()->json([
                        'result' => [],
                        'status' => false,
                        'message' => 'No approval settings for attendance module. Contact HR.'
                    ]);
                }
            }
            $request->merge(['am_id' => $amId]);
            /**
             * Holiday Check
             */
            $atd_date = $request->atd_check_in_date
                ? Carbon::parse($request->atd_check_in_date)->format('Y-m-d')
                : now()->format('Y-m-d');
            $isHoliday = PolicyHolidayList::where('phl_b_id', $user->emp_b_id)
                ->whereDate('phl_start_date', '<=', $atd_date)
                ->whereDate('phl_end_date', '>=', $atd_date)
                ->first();
            if ($isHoliday && $isHoliday->phl_type_id == 205) {
                return response()->json([
                    'status' => false,
                    'result' => [],
                    'message' => "You can't check in today because it's a public holiday."
                ]);
            }
            /**
             * Leave Check
             */
            $leaveApplied = LeaveRequest::where('lvr_emp_id', $user->emp_id)
                ->whereDate('lvr_start_date', '<=', $atd_date)
                ->whereDate('lvr_end_date', '>=', $atd_date)
                ->first();
            if ($leaveApplied && $leaveApplied->fh_leave_day_type->m_id == 201) {
                return response()->json([
                    'status' => false,
                    'result' => [],
                    'message' => "You can't check in due to full-day leave."
                ]);
            }
            /**
             * Route by method
             */
            switch ($checkinMethodId) {
                case 315:
                    return $this->handleCheckinWithDeviceMultiple($request);
                default:
                    Log::warning("Invalid check-in method", ['id' => $checkinMethodId]);
                    return response()->json([
                        'status' => false,
                        'message' => 'Invalid check-in method'
                    ], 400);
            }
        } catch (Exception $e) {
            Log::error('storeDevice() failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'result' => [],
                'status' => false,
                'error'  => $e->getMessage()
            ], 500);
        }
    }

    private function handleCheckinWithDeviceMultiple(Request $request)
    {
        Log::info('Hello Amarnath', $request->all());

        try {
            $deviceSn = $request->input('device_sn');
            $timestamp = $request->input('timestamp');
            $checkinMethodId = $request->input('atd_checkin_method_id');
            // Fetch all devices with this serial number
            $devices = DeviceManagement::where('serial_name', $deviceSn)->get();
            if ($devices->isEmpty()) {
                Log::error('Device not found', ['device_sn' => $deviceSn]);
                return response()->json(['status' => false, 'message' => 'Device not registered'], 404);
            }
            $actualDevice = $devices->first();
            $businessIds = $devices->pluck('b_id')->toArray();
            $user = Auth::user();
            $employeeId = $user->emp_id;
            $empCode = $user->emp_code;
            $date = \Carbon\Carbon::parse($timestamp)->format('Y-m-d');
            $currentTime = \Carbon\Carbon::parse($timestamp)->format('Y-m-d H:i:s');
            // Try to find employee for any of the businesses linked to the device
            $employee = Employee::whereIn('emp_b_id', $businessIds)
                ->where('emp_code', $empCode)
                ->first();

            if (!$employee) {
                Log::warning('Employee not found for any business of device', [
                    'emp_code' => $empCode,
                    'business_ids' => $businessIds,
                ]);
                return response()->json(['status' => false, 'message' => 'Employee not found for this device'], 404);
            }
            $bId = $employee->emp_b_id;
            $actualDeviceid = DeviceManagement::where('b_id', $bId)->first()->id;

            // Check or create attendance record
            // $attendance = AttendanceRecord::firstOrCreate(
            //     [
            //         'atd_emp_id' => $employeeId,
            //         'atd_date'   => $date,
            //         'atd_b_id'   => $bId,
            //     ],
            //     [
            //         'atd_am_id'             => $request->input('am_id'),
            //         'atd_work_mode_type_id' => $request->input('atd_work_mode_type_id'),
            //         'atd_checkin_method_id' => $checkinMethodId,
            //         'atd_device_id'         => $actualDeviceid,
            //     ]
            // );

            $attendance = AttendanceRecord::where('atd_emp_id', $employeeId)
                ->where('atd_b_id', $bId)
                ->whereNull('atd_check_out_time')
                ->where(function ($q) use ($currentTime) {
                    $q->whereDate('atd_date', Carbon::parse($currentTime)->toDateString())
                        ->orWhereDate(
                            'atd_date',
                            Carbon::parse($currentTime)->subDay()->toDateString()
                        );
                })
                ->orderBy('atd_date', 'desc')
                ->first();

            if ($attendance) {
                $date = $attendance->atd_date; // 🔒 LOCK DATE
            } else {
                $attendance = AttendanceRecord::firstOrCreate([
                    'atd_emp_id' => $employeeId,
                    'atd_date'   => $date,
                    'atd_b_id'   => $bId,
                ]);
            }

            // ---------- UPDATE ONLY WHEN EMPTY ----------
            if (empty($attendance->atd_am_id)) {
                $attendance->atd_am_id = $request->input('am_id');
            }

            if (empty($attendance->atd_work_mode_type_id)) {
                $attendance->atd_work_mode_type_id = $request->input('atd_work_mode_type_id');
            }

            if (empty($attendance->atd_checkin_method_id)) {
                $attendance->atd_checkin_method_id = $checkinMethodId;
            }

            if (empty($attendance->atd_device_id)) {
                $attendance->atd_device_id = $actualDeviceid;
            }


            // $resolvedShift = ShiftResolver::resolveEmployeeShift($employee, $date, $currentTime, $currentTime);
            // $shiftTiming = $resolvedShift->shift ?? PolicyShiftTiming::find($employee->emp_shift_type_id);

            if (is_null($attendance->atd_check_in_time)) {
                $resolvedShift = ShiftResolver::resolveEmployeeShift(
                    $employee,
                    $date,
                    $currentTime,
                    $currentTime
                );
                $shiftTiming = $resolvedShift->shift
                    ?? PolicyShiftTiming::find($employee->emp_shift_type_id);
            } else {
                // 🔒 checkout me LOCKED shift hi use hoga
                $shiftTiming = PolicyShiftTiming::find($attendance->atd_pst_id);
            }

            // Log::info('Amarnath', ['resolvedShift' => $employee, 'date' => $date,'currentTime' => $currentTime]);
            if (!empty($shiftTiming) && $shiftTiming->pst_auto_assign_shift == 1) {
                // --------------------------------------------------
                // BASIC SETUP
                // --------------------------------------------------
                $attendanceDate = Carbon::parse($attendance->atd_check_in_time ?? $attendance->atd_date)->format('Y-m-d');
                $punchTime      = Carbon::parse($currentTime);

                /*
                |--------------------------------------------------
                | 1️⃣ FIRST PUNCH → CHECK-IN (LOCK SHIFT)
                |--------------------------------------------------
                */
                if (is_null($attendance->atd_check_in_time)) {
                    Log::info('Single check-in punch Start');
                    // 🔒 Lock shift only once
                    $attendance->atd_pst_id = $shiftTiming->pst_id;
                    $attendance->atd_check_in_time = $punchTime;
                }

                /*
                |--------------------------------------------------
                | 2️⃣ SECOND PUNCH → CHECK-OUT
                |--------------------------------------------------
                */ elseif (is_null($attendance->atd_check_out_time)) {

                    $checkIn = Carbon::parse($attendance->atd_check_in_time);

                    // ✅ skip duplicate punch (same as check-in)
                    if ($punchTime->equalTo($checkIn)) {
                        Log::info('Duplicate check-in punch skipped');
                        return;
                    }

                    // 🌙 Overnight safety
                    if ($punchTime->lessThan($checkIn)) {
                        $punchTime->addDay();
                    }

                    // ✅ valid checkout
                    $attendance->atd_check_out_time = $punchTime;
                } else {
                    // ❌ Extra punches ignore
                    return null;
                }

                /*
                |--------------------------------------------------
                | 🔒 STEP 3 — FINAL SHIFT WINDOW (SINGLE SOURCE)
                |--------------------------------------------------
                */
                $shiftTiming = PolicyShiftTiming::find($attendance->atd_pst_id);

                $shiftStart = Carbon::parse(
                    $attendanceDate . ' ' .
                        Carbon::parse($shiftTiming->pst_start_time)->format('H:i:s')
                );

                $shiftEnd = Carbon::parse(
                    $attendanceDate . ' ' .
                        Carbon::parse($shiftTiming->pst_end_time)->format('H:i:s')
                );

                // 🌙 Overnight shift fix
                if ($shiftEnd->lessThanOrEqualTo($shiftStart)) {
                    $shiftEnd->addDay();
                }

                /*
                |--------------------------------------------------
                | FINAL CHECK-IN / CHECK-OUT
                |--------------------------------------------------
                */
                $checkIn  = $attendance->atd_check_in_time
                    ? Carbon::parse($attendance->atd_check_in_time)
                    : null;

                $checkOut = $attendance->atd_check_out_time
                    ? Carbon::parse($attendance->atd_check_out_time)
                    : null;

                if ($checkIn && $checkOut && $checkOut->lessThan($checkIn)) {
                    $checkOut->addDay();
                }

                /*
                |--------------------------------------------------
                | WORKED MINUTES
                |--------------------------------------------------
                */
                $workedMinutes = ($checkIn && $checkOut)
                    ? $checkIn->diffInMinutes($checkOut)
                    : 0;

                /*
                |--------------------------------------------------
                | 🔒 STEP 4 — MIN WORK (HH:MM → minutes)
                |--------------------------------------------------
                */
                // [$h, $m] = explode(':', $shiftTiming->pst_min_work_hour);
                // $minWorkMinutes = ((int)$h * 60) + (int)$m;

                $minWorkTime = Carbon::parse(
                    $attendanceDate . ' ' .
                        Carbon::parse($shiftTiming->pst_min_work_hour)->format('H:i:s')
                );

                // 🌙 Overnight safety
                if ($minWorkTime->lessThan($shiftStart)) {
                    $minWorkTime->addDay();
                }

                $minWorkMinutes = $shiftStart->diffInMinutes($minWorkTime);

                $minWorkEnd = $shiftStart->copy()->addMinutes($minWorkMinutes);

                // 🌙 Overnight min-work safety
                if ($minWorkEnd->lessThan($shiftStart)) {
                    $minWorkEnd->addDay();
                }

                /*
                |--------------------------------------------------
                | LATE CALCULATION
                |--------------------------------------------------
                */
                $attendance->atd_is_late = 0;
                $attendance->atd_late_duration = 0;

                $graceMinutes = ($shiftTiming->pst_allow_grace_time == 1)
                    ? (int)$shiftTiming->pst_grace_time
                    : 0;

                $lateStart = $shiftStart->copy()->addMinutes($graceMinutes);

                if ($checkIn && $checkIn->greaterThan($lateStart)) {
                    $attendance->atd_is_late = 1;
                    $attendance->atd_late_duration = $lateStart->diffInMinutes($checkIn);
                }

                /*
                |--------------------------------------------------
                | EARLY EXIT (ONLY IF MIN WORK COMPLETED)
                |--------------------------------------------------
                */
                $attendance->atd_is_early_exit = 0;
                $attendance->atd_early_exit_duration = 0;

                if (
                    $checkOut &&
                    $workedMinutes >= $minWorkMinutes &&
                    $checkOut->lessThan($shiftEnd)
                ) {
                    $attendance->atd_is_early_exit = 1;
                    $attendance->atd_early_exit_duration =
                        $checkOut->diffInMinutes($shiftEnd);
                }

                Log::info('Employee shift', [
                    'workedMinutes' => $workedMinutes,
                    'minWorkMinutes' => $minWorkMinutes,
                    'a1' => $shiftTiming->pst_min_work_hour,
                    'a2' => explode(':', $shiftTiming->pst_min_work_hour),
                ]);

                /*
                |--------------------------------------------------
                | ATTENDANCE STATUS
                |--------------------------------------------------
                */
                if ($workedMinutes >= $minWorkMinutes) {
                    $attendance->atd_attendance_status = 251; // Present
                } elseif ($workedMinutes >= ($minWorkMinutes / 2)) {
                    $attendance->atd_attendance_status = 252; // Half Day
                } else {
                    $attendance->atd_attendance_status = 203; // Absent
                }

                $isHoliday = PolicyHolidayList::where('phl_b_id', $attendance->fh_employee->emp_b_id)
                    ->whereDate('phl_start_date', '<=', $attendanceDate)
                    ->whereDate('phl_end_date', '>=', $attendanceDate)
                    ->first();

                $isWeeklyOff = CentralLogics::getWeekOffDates($attendance->fh_employee, null, null, $attendanceDate, $attendanceDate);

                if ($isWeeklyOff || $isHoliday && isset($attendance->atd_check_in_time, $attendance->atd_check_out_time)
                    ) {
                        $attendance->atd_attendance_status = $isWeeklyOff ? 320 : 319;
                        $attendance->atd_is_late = 0;
                        $attendance->atd_is_early_exit = 0;
                        $attendance->atd_is_overtime = 0;
                        $attendance->atd_remark = 'Biometric attendance';
                        $attendance->save();

                        return ReturnHelper::jsonApiReturn(
                            AttendanceResource::collection([$attendance])
                        );
                }

                /*
                |--------------------------------------------------
                | SAVE
                |--------------------------------------------------
                */

                $ot_enabled = OvertimePolicy::where('ot_b_id', $user->emp_b_id)->where('ot_is_enabled', 1)->exists();
                if ($ot_enabled !== null && $ot_enabled) {
                    $ruleCriteria = RuleCriterion::with('fh_approval_module')
                        ->where('rc_b_id', $user->emp_b_id)
                        ->where('rc_condition_option_id', 140)
                        ->whereHas('fh_approval_module', function ($query) {
                            $query->where('am_module_id', 562)
                                ->where('am_status', 1);
                        })->first();
                    $processApprovers = [];
                    $emp_d_id = $user->emp_d_id;
                    $amId = null;
                    // Ensure $ruleCriteria exists before accessing the relationship
                    if ($ruleCriteria && $ruleCriteria->fh_approval_module) {
                        $processApprovers = $ruleCriteria->fh_approval_module
                            ->filteredProcessApprovers($emp_d_id)
                            ->get();
                    }
                    $approvalEmpIds = [];

                    if (empty($processApprovers)) {
                        $approvalMapping = ApprovalHelper::getApprovalMapping($user->emp_b_id, $user->emp_id, 562);
                        if (!$approvalMapping) {
                            return [
                                'status' => false,
                                'message' => 'Attendance created successfully, But not created approval for overtime.',
                                'logData' => [],
                            ];
                        }
                        $amId = $approvalMapping->eam_am_id ?? null;
                        $approvalEmpIds = ApprovalHelper::getApprovalArray($approvalMapping);
                    } else {
                        $amId = $ruleCriteria->rc_am_id;
                        foreach ($processApprovers as $pa) {
                            if ($pa->pa_emp_id) {
                                $approvalEmpIds[] = $pa->pa_emp_id;
                            }
                        }
                    }

                    $attendance->atd_is_overtime = 1;
                    $attendance->atd_overtime_hours = CentralLogics::calculateOTRoster($attendance) ?? 0.00;

                    Log::info('OT Logs', [
                        'OT' => CentralLogics::calculateOTRoster($attendance),
                    ]);
                    OtApprovalStatus::updateOrCreate(
                        [
                            'ot_atd_id' => $attendance->atd_id,
                            'ot_b_id'   => $user->emp_b_id,
                            'ot_date'   => $attendance->atd_date,
                        ],
                        [
                            'ot_emp_id'          => $attendance->atd_emp_id,
                            'ot_atd_type'        => 'rec',
                            'ot_module_id'       => 562,
                            'ot_next_approver'   => $approvalEmpIds[0] ?? null,
                            'ot_requested_status' => 140,
                            'ot_am_id'           => $amId,
                            'ot_stage_completed' => 0,
                        ]
                    );
                }

                $attendance->atd_remark = 'Biometric attendance';
                $attendance->save();

                return ReturnHelper::jsonApiReturn(
                    AttendanceResource::collection([$attendance])
                );
            } else {
                // Determine check-in or check-out
                if ($attendance->wasRecentlyCreated || empty($attendance->atd_check_in_time)) {
                    $attendance->atd_check_in_time = $currentTime;
                    $type = 'check-in';
                } else {
                    $existingCheckIn = \Carbon\Carbon::parse($attendance->atd_check_in_time);
                    $newCheckOut = \Carbon\Carbon::parse($currentTime);
                    if ($existingCheckIn->equalTo($newCheckOut) || $existingCheckIn->diffInSeconds($newCheckOut) < 60) {
                        $type = 'check-in'; // Don't update checkout if too close
                    } else {
                        $attendance->atd_check_out_time = $currentTime;
                        $type = 'check-out';
                    }
                }
                $attendance->atd_segments = json_encode([
                    'device_sn' => $deviceSn,
                    'method' => 'device',
                ]);
                $attendance->save();

                // Handle any post-processing
                $this->handleAttendance($attendance, $user, $type);
            }
            $attendance->refresh();
            return ReturnHelper::jsonApiReturn(AttendanceResource::collection([$attendance]));
        } catch (\Exception $e) {
            Log::error('Error in handleCheckinWithDeviceMultiple', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return response()->json(['status' => false, 'message' => 'Something went wrong while handling device check-in'], 500);
        }
    }

    //MY OD

    public function outdoorStore(Request $request)
    {
        try {
            $user = Auth::user();
            $amId = null;
            $ruleCriteria = RuleCriterion::with('fh_approval_module')
                ->where('rc_b_id', $user->emp_b_id)
                ->where('rc_condition_option_id', 140)
                ->whereHas('fh_approval_module', function ($query) {
                    $query->where('am_module_id', 589)
                        ->where('am_status', 1);
                })->first();
            $processApprovers = [];

            // Ensure $ruleCriteria exists before accessing the relationship
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

            $request->merge([
                'am_id' => $amId,
            ]);

            $atd_date = $request->atd_od_check_in_time ? Carbon::parse($request->atd_od_check_in_time)->format('Y-m-d') : now()->format('Y-m-d');
            $isHoliday = PolicyHolidayList::where('phl_b_id', $user->emp_b_id)->whereDate('phl_start_date', '<=', $atd_date)->whereDate('phl_end_date', '>=', $atd_date)->first();
            if ($isHoliday && $isHoliday->phl_type_id == 205 && $isHoliday->phl_day_type_id == 201) {
                return response()->json(['status' => false, 'result' => [], 'message' => "You can't check in today because it's a public holiday."]);
            }

            $leaveApplied =  LeaveRequest::where('lvr_emp_id', $user->emp_id)->whereDate('lvr_start_date', '<=', now()->format('Y-m-d'))->whereDate('lvr_end_date', '>=', now()->format('Y-m-d'))->where('lvr_status', '!=', 170)->where('lvr_stage_completed', 1)->first();
            if ($leaveApplied && $leaveApplied->fh_leave_day_type->m_id == 201) { //201=='Full Day'
                return response()->json(['status' => false, 'result' => [], 'message' => "You can't check in due to you have applied leave for this date. Contact HR or your manager for assistance."]);
            } elseif ($leaveApplied && $leaveApplied->fh_leave_day_segment->m_id == 235) { //235==First Half

            } elseif ($leaveApplied && $leaveApplied->fh_leave_day_segment->m_id == 236) { //236==Second Half

            }
            return $this->handleCheckinOutdoor($request);

        } catch (Exception $e) {
            return response()->json(['result' => [], 'status' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function handleCheckinOutdoor(Request $request)
    {
        $user = Auth::user();
        // Determine check-in date and time from request or fallback to now()
        $currentDate = $request->atd_od_check_in_date ?? now()->format('Y-m-d');
        $currentTime = $request->atd_od_check_in_time ?? now()->format('H:i:s');
        $checkInDateTime = $currentDate . ' ' . $currentTime;

        // Upload photos if present (reuse existing logic)
        $uploadedPhotos = [];
        if (env('STORE_ON_S3')) {
            $bucket = 'fixhr-uploads';
            if ($request->atd_od_punchin_photo != '' && $request->atd_od_punchin_photo != NULL && $request->atd_od_punchin_photo != []) {
                foreach ($request->atd_od_punchin_photo as $file) {
                    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $extension = $file->getClientOriginalExtension();
                    $filename = time() . '_' . md5($originalName) . '.' . $extension;
                    $imagePath = 'AttendancePhoto/' . $user->fh_business->b_unique_id . '/' . $filename;
                    $uploadResult = $this->awsHelper->uploadFileToS3($bucket, $imagePath, $file);
                    if ($uploadResult['status']) {
                        $uploadedPhotos[] = $uploadResult['ObjectURL'];
                    }
                }
            }
        } else {
            $uploadedPath = CommonUtils::uploadFiles($request, 'atd_od_punchin_photo', 'AttendancePhoto', ['prefix' => 'Attendance', 'isApi' => true]);
            if (!empty($uploadedPath)) {
                foreach ($uploadedPath as $path) {
                    $uploadedPhotos[] = url($path);
                }
            }
        }

        $today = Carbon::parse($currentDate)->format('Y-m-d');
        $currentTimeFull = Carbon::parse($checkInDateTime)->format('Y-m-d H:i:s');
        $amIdToStore = $request->input('am_id') ?? null;
        $nextApproverId = null;
        if ($amIdToStore) {
            $firstApprover = ProcessApprover::where('pa_am_id', $amIdToStore)
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

        $attendance = AttendanceOutDoor::firstOrCreate(
            [
                'atd_od_b_id' => $user->emp_b_id,
                'atd_od_emp_id' => $request->emp_id ?? $user->emp_id,
                'atd_od_date' => $today,
            ],
            [
                'atd_od_am_id' => $amIdToStore,
                'atd_od_work_mode_type_id' => $request->input('atd_od_work_mode_type_id'),
                'atd_od_checkin_method_id' => 590,
                'atd_od_device_id' => $request->input('atd_od_device_id'),
                'atd_od_next_approver' => 1,
                'atd_od_next_approver_id'   => $nextApproverId,
                'atd_od_module_id' => 589, // link to attendance module
                'atd_od_request_status' => 139, // Pending
            ]
        );

        if ($attendance->wasRecentlyCreated || empty($attendance->atd_od_check_in_time)) {
            $attendance->atd_od_check_in_time = $currentTimeFull;
            $attendance->atd_od_punchin_location = $request->input('atd_od_punchin_location') ?? null;
            $attendance->atd_od_longitude_punchin = $request->input('atd_od_longitude_punchin') ?? null;
            $attendance->atd_od_latitude_punchin = $request->input('atd_od_latitude_punchin') ?? null;
            $attendance->atd_od_segments = $request->input('atd_od_segments') ? json_encode($request->input('atd_od_segments')) : null;
            $attendance->atd_od_punchin_photo = !empty($uploadedPhotos) ? json_encode($uploadedPhotos) : null;
            $type = 'check-in';
        } else {
            $attendance->atd_od_check_out_time = $currentTimeFull;
            $attendance->atd_od_segments = $request->input('atd_od_segments') ? json_encode($request->input('atd_od_segments')) : $attendance->atd_od_segments;
            $attendance->atd_od_punchout_photo = !empty($uploadedPhotos) ? json_encode($uploadedPhotos) : $attendance->atd_od_punchout_photo;
            $type = 'check-out';
        }

        $attendance->save();
        $attendance->refresh();

        // Resolve shift and call appropriate OD attendance handler
        $shift = ShiftResolver::resolveEmployeeShift($user, $today);
        $shiftTiming = $shift ? ($shift->shift ?? $shift) : null;

        if ($shiftTiming && $shiftTiming->pst_type_id == 245) {
            return $this->handleAttendanceOutDoor2($attendance, $user, $type);
        } else {
            return $this->handleAttendanceOutDoor($attendance, $user, $type);
        }
    }

    public function handleAttendanceOutDoor($data, $user, $type)
    {
        try {
            // Fetch the shift timing policy
            $attendancePolicy = PolicyAttendance::where(['ap_id' => $user->emp_ap_id, 'ap_status' => 1])->first();
            if (!$attendancePolicy) {
                return response()->json(['status' => false, 'result' => [], 'message' => 'Attendance policy settings are missing.']);
            }

            $shiftTiming = PolicyShiftTiming::find($user->emp_shift_type_id);
            if (!$shiftTiming) {
                return response()->json(['status' => false, 'result' => [], 'message' => 'The shift time has not been set.']);
            }

            $shiftStartTime = Carbon::parse($data->atd_od_date->format('Y-m-d') . ' ' . $shiftTiming->pst_start_time->format('H:i'));
            $shiftEndTime = Carbon::parse($data->atd_od_date->format('Y-m-d') . ' ' . $shiftTiming->pst_end_time->format('H:i'));

            $dayName = Carbon::parse($data->atd_od_date)->format('l');

            // Handle Partial day punch
            if ($shiftTiming->pst_allow_partial_day == 1 && $shiftTiming->fh_master_table_week->m_name == $dayName) {
                $shiftStartTime = $shiftTiming->pst_partial_day_begin_time;
                $shiftEndTime = $shiftTiming->pst_partial_day_end_time;
            }

            // Calculate time for check-in or check-out

            $data->atd_od_pst_id = $shiftTiming->pst_id;
            $currentTimestamp = ($type === 'check-in') ? date('Y-m-d H:i', strtotime($data->atd_od_check_in_time)) : date('Y-m-d H:i', strtotime($data->atd_od_check_out_time));
            $shiftTime = ($type === 'check-in') ? $shiftStartTime : $shiftEndTime;


            $pst_start_time = Carbon::parse($shiftStartTime);
            $pst_end_time = Carbon::parse($shiftEndTime);
            $dailyWorkingHours = $pst_start_time->diffInMinutes($pst_end_time);

            $checkInTime = Carbon::parse($data->atd_od_check_in_time);
            $checkOutTime = Carbon::parse($data->atd_od_check_out_time);
            $workedDuration = $checkInTime->diffInMinutes($checkOutTime);

            $fullDayThreshold = $dailyWorkingHours;
            $halfDayThreshold = $dailyWorkingHours / 2;

            if ($type === 'check-in') { // Handle late check-in or overtime check-out
                // Added grace time
                $shiftGraceTime = strtotime($shiftTime) + (($shiftTiming->pst_allow_grace_time == 1) ? $shiftTiming->pst_grace_time * 60 : 0);

                $leaveRequest = LeaveRequest::where([
                    ['lvr_emp_id', '=', $user->emp_id],
                    ['lvr_b_id', '=', $user->emp_b_id],
                    ['lvr_start_date', '=', date('Y-m-d')],
                    ['lvr_day_segment_id', '=', 235],
                    ['lvr_leave_day_type_id', '=', 202],
                    ['lvr_status', '!=', 170],
                    ['lvr_stage_completed', '=', 1],
                ])->first();

                if (isset($leaveRequest) && !empty($leaveRequest)) {

                    if ($shiftTiming->pst_allow_break1 == 1) {
                        $breakStart = Carbon::parse($shiftTiming->pst_break_begin_time1);
                        $breakEnd = $breakStart->copy()->addMinutes($shiftTiming->pst_break1_duration);
                        $secondHalfStart = $breakEnd->format('Y-m-d H:i');
                    } else {
                        $breakStart = $pst_start_time->copy()->addHours(4);
                        $breakEnd = $breakStart->copy()->addMinutes(30);
                        $secondHalfStart = $breakEnd->format('Y-m-d H:i');
                    }

                    // If second-half started, update grace time
                    $shiftGraceTime = strtotime($secondHalfStart) + (($shiftTiming->pst_allow_grace_time == 1) ? $shiftTiming->pst_grace_time * 60 : 0);
                }

                // Single late calculation block
                $shiftGraceCarbon = Carbon::parse(date('Y-m-d H:i', $shiftGraceTime));
                $currentTimestampCarbon = Carbon::parse($currentTimestamp); // Current timestamp

                if ($shiftGraceCarbon->lt($currentTimestampCarbon)) {
                    $data->atd_od_is_late = 1;
                    $lateDurationMinutes = $currentTimestampCarbon->diffInMinutes($shiftGraceCarbon);
                    $data->atd_od_late_duration = abs(number_format($lateDurationMinutes, 2));
                } else {
                    $data->atd_od_is_late = 0;
                    $data->atd_od_late_duration = 0;
                }
            } else {
                // Early Exit calculation
                $leaveRequestSecHalf = LeaveRequest::where([
                    ['lvr_emp_id', '=', $user->emp_id],
                    ['lvr_b_id', '=', $user->emp_b_id],
                    ['lvr_start_date', '=', date('Y-m-d')],
                    ['lvr_day_segment_id', '=', 236],
                    ['lvr_leave_day_type_id', '=', 202],
                    ['lvr_status', '!=', 170],
                    ['lvr_stage_completed', '=', 1],
                ])->first();

                // Convert currentTimestamp to a Carbon instance for comparison
                $currentTimestampCarbon = Carbon::parse($currentTimestamp);
                $shiftTimeCarbon = Carbon::parse($shiftEndTime);
                if (isset($leaveRequestSecHalf) && !empty($leaveRequestSecHalf)) {
                    if ($shiftTiming->pst_allow_break1 == 1) {
                        $firstHalfEnd = Carbon::parse($shiftTiming->pst_break_begin_time1);
                    } else {
                        $firstHalfEnd = $pst_end_time->copy()->subHours(5);
                    }

                    if ($firstHalfEnd->gt($currentTimestampCarbon)) {
                        $data->atd_od_is_early_exit = 1;
                        $earlyExitDuration = $firstHalfEnd->diffInMinutes($currentTimestampCarbon);
                        $data->atd_od_early_exit_duration = abs(number_format($earlyExitDuration, 2));
                    } else {
                        $data->atd_od_is_early_exit = 0;
                        $data->atd_od_early_exit_duration = 0;
                    }
                } else {
                    if ($shiftTimeCarbon->gt($currentTimestampCarbon)) {
                        $data->atd_od_is_early_exit = 1;
                        $earlyExitDuration = $shiftTimeCarbon->diffInMinutes($currentTimestampCarbon);
                        $data->atd_od_early_exit_duration = (float) abs(number_format($earlyExitDuration, 2));
                    } else {
                        $data->atd_od_is_early_exit = 0;
                        $data->atd_od_early_exit_duration = 0;
                    }
                }
            }
            // Calculate mispunch status if only one punch (in or out) is present
            // $data = $data->save();

            $this->calculateAttendanceStatusOutDoor($data);

            if ($data->save()) {
                return ReturnHelper::jsonApiReturn(AttendanceOutDoorResource::collection([$data]));
            }
            return response()->json(['result' => [], 'status' => false]);
        } catch (Exception $e) {
            return response()->json(['result' => [], 'status' => false, $e->getMessage()]);
        }
    }

    public function handleAttendanceOutDoor2($data, $user, string $type)
    {
        // ---- 0) Load shift policy via resolver --------------------------------
        $shift = ShiftResolver::resolveEmployeeShift($user, $data->atd_od_date);
        $shift = $shift ? $shift->shift : null;

        if (!$shift) {
            return response()->json([
                'status'  => false,
                'result'  => [],
                'message' => 'No shift assigned for this employee on ' . $data->atd_od_date,
            ]);
        }

        // persist shift id on record
        $data->atd_od_pst_id = $shift->pst_id;

        // ---- 1) Resolve shift window ------------------------------------------
        $date    = Carbon::parse($data->atd_od_date)->format('Y-m-d');
        $dayName = Carbon::parse($date)->format('l');

        $shiftStart = Carbon::parse($date . ' ' . $shift->pst_start_time->format('H:i:s'));
        $shiftEnd   = Carbon::parse($date . ' ' . $shift->pst_end_time->format('H:i:s'));
        if ((int)($shift->pst_end_next_day ?? 0) === 1) {
            $shiftEnd->addDay(); // night shift support
        }

        // Partial day overrides
        $isPartialDay = false;
        if (
            (int)($shift->pst_allow_partial_day ?? 0) === 1
            && $shift->fh_master_table_week
            && $shift->fh_master_table_week->m_name === $dayName
        ) {
            $isPartialDay = true;
            $shiftStart = Carbon::parse($date . ' ' . $shift->pst_partial_day_begin_time->format('H:i:s'));
            $shiftEnd   = Carbon::parse($date . ' ' . $shift->pst_partial_day_end_time->format('H:i:s'));
            if ($shiftEnd->lessThan($shiftStart)) {
                $shiftEnd->addDay();
            }
        }

        if (!$isPartialDay && (int)($shift->pst_allow_partial_day2 ?? 0) === 1) {
            if (!empty($shift->week_off2) && trim($shift->week_off2) === $dayName) {
                $isPartialDay = true;
                $shiftStart = Carbon::parse($date . ' ' . $shift->pst_partial_day_begin_time2);
                $shiftEnd   = Carbon::parse($date . ' ' . $shift->pst_partial_day_end_time2);
                if ($shiftEnd->lessThan($shiftStart)) {
                    $shiftEnd->addDay();
                }
            }
        }

        // ---- 2) Read punches --------------------------------------------------
        $checkIn  = $data->atd_od_check_in_time ? Carbon::parse($data->atd_od_check_in_time) : null;
        $checkOut = $data->atd_od_check_out_time ? Carbon::parse($data->atd_od_check_out_time) : null;

        if ($checkIn && $checkOut && $checkOut->lessThan($checkIn)) {
            $checkOut = $checkOut->copy()->addDay(); // past midnight normalization
        }

        // ---- 3) Mark late / early-exit flags ---------------------------------
        if ($checkIn) {
            $graceMin     = ((int)($shift->pst_allow_grace_time ?? 0) === 1) ? (int)($shift->pst_grace_time ?? 0) : 0;
            $latestOnTime = $shiftStart->copy()->addMinutes($graceMin);

            $data->atd_od_is_late        = $checkIn->greaterThan($latestOnTime) ? 1 : 0;
            $data->atd_od_late_duration  = $data->atd_od_is_late
                ? max(0, $latestOnTime->diffInMinutes($checkIn, false))
                : 0;
        }

        if ($checkOut) {
            $data->atd_od_is_early_exit        = $checkOut->lessThan($shiftEnd) ? 1 : 0;
            $data->atd_od_early_exit_duration  = $data->atd_od_is_early_exit
                ? $checkOut->diffInMinutes($shiftEnd)
                : 0;
        }

        // ---- 4) Compute worked minutes (minus unpaid breaks) -----------------
        $workedMinutes = 0;
        if ($checkIn && $checkOut) {
            $workedMinutes = $checkIn->diffInMinutes($checkOut);

            $break1Unpaid = (int)($shift->pst_is_break_paid ?? 1) === 0;
            $break1Dur    = (int)($shift->pst_break1_duration ?? 0);
            if ($break1Unpaid && $break1Dur > 0) {
                $workedMinutes = max(0, $workedMinutes - $break1Dur);
            }
            // Break 2 always paid → ignored
        }

        // ---- 5) Holiday / Week Off handling ----------------------------------
        $isHoliday = PolicyHolidayList::where('phl_b_id', $data->fh_employee->emp_b_id)
            ->whereDate('phl_start_date', '<=', $date)
            ->whereDate('phl_end_date', '>=', $date)
            ->exists();

        $isWeeklyOff = CentralLogics::getWeekOffDates($data->fh_employee, null, null, $date, $date);

        if (($isHoliday || $isWeeklyOff) && $checkIn && $checkOut) {
            $data->atd_od_attendance_status = $isWeeklyOff ? 320 : 319;

            // Side effect: generate comp-off
            try {
                CentralLogics::generateCompOff($data->fh_employee, $date);
            } catch (\Throwable $e) {
                // swallow exception, attendance must still save
            }

            $data->save();
            return ReturnHelper::jsonApiReturn(AttendanceOutDoorResource::collection([$data]));
        }

        // ---- 6) If any punch is missing → Mispunch ---------------------------
        if (!$checkIn || !$checkOut) {
            $data->atd_od_attendance_status = 228; // Mispunch
            $data->save();
            return ReturnHelper::jsonApiReturn(AttendanceOutDoorResource::collection([$data]));
        }

        // ---- 7) Compute status code ------------------------------------------
        $statusCode = $this->calculateAttendanceStatusOutDoor2($data, $isPartialDay);

        $data->atd_od_attendance_status = $statusCode;

        if ($data->save()) {
            return ReturnHelper::jsonApiReturn(AttendanceOutDoorResource::collection([$data]));
        }

        return response()->json([
            'status'  => false,
            'result'  => [],
            'message' => 'Failed to save attendance record.'
        ]);
    }

    public function calculateAttendanceStatusOutDoor($data)
    {
        $atd_od_req = $data->fh_employee->emp_attendance_preference;
        $shift = $data->fh_policy_shift_timing;
        if (!$shift) {
            $data->atd_od_attendance_status = 228; // Mispunch
            return;
        }

        // Retrieve shift start and end times
        $pst_start_time = Carbon::parse($shift->pst_start_time);
        $pst_end_time = Carbon::parse($shift->pst_end_time);
        $dailyWorkingMinutes = $pst_start_time->diffInMinutes($pst_end_time);

        $dayName = Carbon::parse($data->atd_od_date)->format('l');

        // Handle Partial day punch
        if ($shift->pst_allow_partial_day == 1 && $shift->fh_master_table_week->m_name == $dayName) {
            $shiftStartTime = $shift->pst_partial_day_begin_time;
            $shiftEndTime = $shift->pst_partial_day_end_time;
            $dailyWorkingMinutes = $shiftStartTime->diffInMinutes($shiftEndTime);
        }

        // Check if current day is a holiday
        $isHoliday = PolicyHolidayList::where('phl_b_id', $data->fh_employee->emp_b_id)
            ->whereDate('phl_start_date', '<=', $data->atd_od_date)
            ->whereDate('phl_end_date', '>=', $data->atd_od_date)
            ->first();

        // If today is a holiday and it's a half-day holiday, override daily working minutes
        // based on the shift's start/end and break timings. Compute segment length from pst_start_time / pst_end_time
        // and the break timings (pst_break_begin_time1 / pst_break_end_time1). If
        // break timings are not present, fallback to a static 4 hours (240 minutes).
        if ($isHoliday && isset($isHoliday->phl_day_type_id) && (int)$isHoliday->phl_day_type_id === 202) {
            // 202 => Half Day, segments expected to be 235 (First Half) or 236 (Second Half)
            $holidaySegment = $isHoliday->phl_day_segment_type_id ?? null;
            $segmentMinutes = null;

            if ($shift) {
                // Parse shift start and end
                try {
                    $shiftStart = Carbon::parse($shift->pst_start_time);
                    $shiftEnd = Carbon::parse($shift->pst_end_time);
                    if ($shiftEnd->lessThan($shiftStart)) {
                        $shiftEnd->addDay();
                    }
                } catch (\Exception $e) {
                    $shiftStart = null;
                    $shiftEnd = null;
                }

                // If break timings are configured, use them to split morning/afternoon
                if (!empty($shift->pst_break_begin_time1) && !empty($shift->pst_break_end_time1) && $shiftStart && $shiftEnd) {
                    $breakStart = Carbon::parse($shift->pst_break_begin_time1);
                    $breakEnd = Carbon::parse($shift->pst_break_end_time1);
                    // Normalize break times to the same date as shift for comparison
                    $breakStart = Carbon::parse($data->atd_od_date->format('Y-m-d') . ' ' . $breakStart->format('H:i:s'));
                    $breakEnd = Carbon::parse($data->atd_od_date->format('Y-m-d') . ' ' . $breakEnd->format('H:i:s'));
                    if ($breakEnd->lessThan($breakStart)) {
                        $breakEnd->addDay();
                    }
                    // If break is outside shift window, clamp to shift
                    if ($breakStart->lessThan($shiftStart)) {
                        $breakStart = $shiftStart->copy();
                    }
                    if ($breakEnd->greaterThan($shiftEnd)) {
                        $breakEnd = $shiftEnd->copy();
                    }

                    // Morning segment = shiftStart -> breakStart
                    $morningMinutes = max(0, $shiftStart->diffInMinutes($breakStart));
                    // Afternoon segment = breakEnd -> shiftEnd
                    $afternoonMinutes = max(0, $breakEnd->diffInMinutes($shiftEnd));

                    if ($holidaySegment == 235) {
                        $segmentMinutes = $morningMinutes;
                    } elseif ($holidaySegment == 236) {
                        $segmentMinutes = $afternoonMinutes;
                    }
                } else {
                    // No break timings — we cannot safely derive segments, fall back to static
                    $segmentMinutes = null;
                }
            }

            // Final fallback: static 4 hours
            if (empty($segmentMinutes) || $segmentMinutes <= 0) {
                $segmentMinutes = 4 * 60; // 4 hours in minutes
            }

            // Override daily working minutes for this day (half-day holiday)
            $dailyWorkingMinutes = (int) $segmentMinutes;
        }

        $breakMinutes = (int) $shift->pst_break_duration_minutes;
        $graceMinutes = (int) $shift->pst_grace_time;

        // Calculate thresholds
        $halfDayThreshold = $dailyWorkingMinutes / 2 - $graceMinutes - $breakMinutes;
        $fullDayThreshold = $dailyWorkingMinutes - $graceMinutes;

        // Check if current day is a weekly off day
        $isWeeklyOff = CentralLogics::getWeekOffDates($data->fh_employee, null, null, $data->atd_od_date, $data->atd_od_date);

        $checkIn = Carbon::parse($data->atd_od_check_in_time);
        $checkOut = Carbon::parse($data->atd_od_check_out_time);
        $workedDuration = $checkIn->diffInMinutes($checkOut);
        $worked_time = $checkIn->diff($checkOut)->format('%H:%I:%S');

        $punchDate = $checkIn->format('Y-m-d');

        if (($isWeeklyOff || ($isHoliday && isset($isHoliday->phl_day_type_id) && (int)$isHoliday->phl_day_type_id == 201))
            && isset($data->atd_od_check_in_time, $data->atd_od_check_out_time)
            && $data->atd_od_check_in_time !== ''
            && $data->atd_od_check_out_time !== ''
        ) {
            $data->atd_od_attendance_status = $isWeeklyOff ? 320 : 319;
            $data->atd_od_is_late = 0;
            $data->atd_od_is_early_exit = 0;
            return;
        }

        // Calculate min work hour in minutes
        $minWorkHour = $shift->pst_min_work_hour
            ? Carbon::parse($shift->pst_start_time)->diffInMinutes(
                Carbon::parse($shift->pst_min_work_hour)
            )
            : 0;

        // Attendance Status Logic
        if ($workedDuration >= $fullDayThreshold || $workedDuration >= $minWorkHour || $atd_od_req == 368 || $atd_od_req == 369) {
            if (
                (
                    $shift->pst_hd_office_report_after &&
                    $shift->pst_hd_office_report_after_time &&
                    $checkIn->gt(Carbon::parse($shift->pst_hd_office_report_after_time))
                )
                ||
                (
                    $shift->pst_hd_office_report_before &&
                    $shift->pst_hd_office_report_before_time &&
                    $checkIn->lt(Carbon::parse($shift->pst_hd_office_report_before_time))
                )
            ) {
                $data->atd_od_attendance_status = 252; // Half Day
                $data->atd_od_is_late = 0;
                $data->atd_od_is_early_exit = 0;
            } else {
                $data->atd_od_attendance_status = 251; // Present
            }

        } elseif ($workedDuration >= ($fullDayThreshold / 2) || $workedDuration >= ($minWorkHour / 2)) {
            $data->atd_od_attendance_status = 252; // Half Day
        } else {
            $data->atd_od_attendance_status = 203; // Half Day
        }

        //OT Calculation
        if (($workedDuration >= $fullDayThreshold)) {
            // $user = Auth::user();
            // $ot_enabled = OvertimePolicy::where('ot_b_id', $user->emp_b_id)->where('ot_is_enabled', 1)->exists();
            // if ($ot_enabled !== null && $ot_enabled) {
            //     $ruleCriteria = RuleCriterion::with('fh_approval_module')
            //         ->where('rc_b_id', $user->emp_b_id)
            //         ->where('rc_condition_option_id', 140)
            //         ->whereHas('fh_approval_module', function ($query) {
            //             $query->where('am_module_id', 562)
            //                 ->where('am_status', 1);
            //         })->first();
            //     $processApprovers = [];
            //     $emp_d_id = $user->emp_d_id;
            //     $amId = null;
            //     // Ensure $ruleCriteria exists before accessing the relationship
            //     if ($ruleCriteria && $ruleCriteria->fh_approval_module) {
            //         $processApprovers = $ruleCriteria->fh_approval_module
            //             ->filteredProcessApprovers($emp_d_id)
            //             ->get();
            //     }
            //     $approvalEmpIds = [];
            //     if (empty($processApprovers)) {
            //         $approvalMapping = ApprovalHelper::getApprovalMapping($user->emp_b_id, $user->emp_id, 562);
            //         if (!$approvalMapping) {
            //             return [
            //                 'status' => false,
            //                 'message' => 'Attendance created successfully, but overtime was not created. Please set the overtime approval or disable the overtime feature.',
            //             ];
            //         }
            //         $amId = $approvalMapping->eam_am_id ?? null;
            //         $approvalEmpIds = ApprovalHelper::getApprovalArray($approvalMapping);
            //     } else {
            //         $amId = $ruleCriteria->rc_am_id;
            //         foreach ($processApprovers as $pa) {
            //             if ($pa->pa_emp_id) {
            //                 $approvalEmpIds[] = $pa->pa_emp_id;
            //             }
            //         }
            //     }
            //     $data->atd_od_is_overtime = 0;
            //     $data->atd_od_overtime_hours = 0.00;
            // }
        } else {
            $data->atd_od_is_overtime = 0;
            $data->atd_od_overtime_hours = 0;
        }
    }

    public function calculateAttendanceStatusOutDoor2($data, bool $isPartialDay = false): int
    {
        /** @var PolicyShiftTiming|null $shift */
        $shift = $data->fh_policy_shift_timing;
        if (!$shift) {
            return 0; // fallback, should never happen if handleAttendance2 runs first
        }

        $checkIn  = $data->atd_od_check_in_time ? Carbon::parse($data->atd_od_check_in_time) : null;
        $checkOut = $data->atd_od_check_out_time ? Carbon::parse($data->atd_od_check_out_time) : null;

        if (!$checkIn || !$checkOut) {
            return 228; // Mispunch safeguard
        }

        // normalize overnight check-outs
        if ($checkOut->lessThan($checkIn)) {
            $checkOut->addDay();
        }

        // ---- Compute worked minutes (with unpaid break adjustment) ----------------
        $workedMinutes = $checkIn->diffInMinutes($checkOut);

        $break1Unpaid = (int)($shift->pst_is_break_paid ?? 1) === 0;
        $break1Dur    = (int)($shift->pst_break1_duration ?? 0);
        if ($break1Unpaid && $break1Dur > 0) {
            $workedMinutes = max(0, $workedMinutes - $break1Dur);
        }
        // Break 2 always paid → ignore

        // ---- Decide thresholds ----------------------------------------------------
        $minWorkMins   = (int)($shift->pst_min_work_hour ?? 0);
        $shiftDuration = (int)($shift->pst_shift_duration ?? 0); // total scheduled duration in minutes

        // If partial day → override shift duration and thresholds
        if ($isPartialDay) {
            $partialStart = $shift->pst_partial_day_begin_time ?? null;
            $partialEnd   = $shift->pst_partial_day_end_time ?? null;

            if ($partialStart && $partialEnd) {
                $pStart = Carbon::parse($partialStart);
                $pEnd   = Carbon::parse($partialEnd);
                if ($pEnd->lessThan($pStart)) {
                    $pEnd->addDay();
                }

                $shiftDuration = $pStart->diffInMinutes($pEnd);
                $minWorkMins   = $shiftDuration; // must complete partial hours fully
            }
        }

        //OT Calculation
        if ($workedMinutes >= $shiftDuration) {
            $user = Auth::user();
            $ot_enabled = OvertimePolicy::where('ot_b_id', $user->emp_b_id)->where('ot_is_enabled', 1)->exists();
            if ($ot_enabled !== null && $ot_enabled) {
                $ruleCriteria = RuleCriterion::with('fh_approval_module')
                    ->where('rc_b_id', $user->emp_b_id)
                    ->where('rc_condition_option_id', 140)
                    ->whereHas('fh_approval_module', function ($query) {
                        $query->where('am_module_id', 562)
                            ->where('am_status', 1);
                    })->first();
                $processApprovers = [];
                $emp_d_id = $user->emp_d_id;
                $amId = null;
                $notApproval = 0;
                // Ensure $ruleCriteria exists before accessing the relationship
                if ($ruleCriteria && $ruleCriteria->fh_approval_module) {
                    $processApprovers = $ruleCriteria->fh_approval_module
                        ->filteredProcessApprovers($emp_d_id)
                        ->get();
                }
                $approvalEmpIds = [];
                if (empty($processApprovers)) {
                    $approvalMapping = ApprovalHelper::getApprovalMapping($user->emp_b_id, $user->emp_id, 562);
                    if (!$approvalMapping) {
                        $data->atd_od_is_overtime = 0;
                        $data->atd_od_overtime_hours = 0;
                        $notApproval = 1;
                    }
                    $amId = $approvalMapping->eam_am_id ?? null;
                    $approvalEmpIds = ApprovalHelper::getApprovalArray($approvalMapping);
                } else {
                    $amId = $ruleCriteria->rc_am_id;
                    foreach ($processApprovers as $pa) {
                        if ($pa->pa_emp_id) {
                            $approvalEmpIds[] = $pa->pa_emp_id;
                        }
                    }
                }
                if($notApproval != 1) {
                    $data->atd_od_is_overtime = 1;
                    $data->atd_od_overtime_hours = 0.00;
                }
            }
        } else {
            $data->atd_is_overtime = 0;
            $data->atd_overtime_hours = 0.00;
        }

        $halfDayThreshold = (int)floor($shiftDuration * 0.5);

        // ---- Apply rules ----------------------------------------------------------
        if ($workedMinutes >= $minWorkMins) {
            return 251; // Present
        }

        if ($workedMinutes >= $halfDayThreshold && $workedMinutes < $minWorkMins) {
            return 252; // Half Day
        }

        return 203; // Absent
    }

    public function punchOutOutDoor(Request $request)
    {
        $user = Auth::user();
        $currentDate = $request->atd_od_check_out_date ?? now()->format('Y-m-d');
        $currentTime = $request->atd_od_check_out_time ?? now()->format('H:i:s');
        $checkOutDateTime = $currentDate . ' ' . $currentTime;

        $uploadedPhotos = [];
        if (env('STORE_ON_S3')) { //upload file to AWS S3 storage.
            $bucket = 'fixhr-uploads';
            if ($request->atd_od_punchout_photo != '' && $request->atd_od_punchout_photo != NULL && $request->atd_od_punchout_photo != []) {
                foreach ($request->atd_od_punchout_photo as $file) {
                    $imageUniqueName = $file->getClientOriginalName();
                    $imagePath = 'AttendancePhoto/' . $user->fh_business->b_unique_id . '/' . $imageUniqueName;
                    $uploadResult = $this->awsHelper->uploadFileToS3($bucket, $imagePath, $file);
                    if ($uploadResult['status']) {
                        $uploadedPhotos[] = $uploadResult['ObjectURL'];
                    }
                }
            }
        } else { //upload file to the server directory
            $uploadedPath = CommonUtils::uploadFiles($request, 'atd_od_punchout_photo', 'AttendancePhoto', ['prefix' => 'Attendance', 'isApi' => true]);
            if (!empty($uploadedPath)) {
                foreach ($uploadedPath as $path) {
                    $uploadedPhotos[] = url($path);
                }
            }
        }

        // ---- Load shift policy via resolver --------------------------------
        $shift = ShiftResolver::resolveEmployeeShift($user, $currentDate);
        $shiftTiming = $shift ? $shift->shift : null;
        if (!$shiftTiming) {
            return response()->json(['status' => false, 'result' => [], 'message' => 'Shift timing settings are missing.']);
        }
        $endBy = Carbon::parse($currentDate . " " . $shiftTiming->pst_end_by);
        $outTimeStamp = Carbon::parse($checkOutDateTime);
        if ((int)($shiftTiming->pst_end_next_day ?? 0) === 1 && $outTimeStamp->lte($endBy)) {
            $currentDate = Carbon::parse($currentDate)->subDay()->format('Y-m-d');
        }

        // Find or create today's attendance record
        $attendance = AttendanceOutDoor::firstOrNew(
            [
                'atd_od_b_id' => $user->fh_business->b_id,
                'atd_od_emp_id' => $user->emp_id,
                'atd_od_date' => $currentDate
            ]
        );

        $attendance->atd_od_check_out_time = $checkOutDateTime;
        $attendance->atd_od_punchout_photo = json_encode($uploadedPhotos);
        $attendance->atd_od_punchout_location = $request->input('atd_od_punchout_location');
        $attendance->atd_od_longitude_punchout = $request->input('atd_od_longitude_punchout');
        $attendance->atd_od_latitude_punchout = $request->input('atd_od_latitude_punchout');
        $attendance->save();

        if ($shiftTiming->pst_type_id == 245) {
            return $this->handleAttendanceOutDoor2($attendance, $user, 'check-out');
        } else {
            return $this->handleAttendanceOutDoor($attendance, $user, 'check-out');
        }
    }
}
