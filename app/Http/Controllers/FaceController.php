<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Services\FaceRekognitionService;
use App\Helpers\Aws\AwsHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FaceController extends Controller
{
    protected $rekognitionService;
    protected $awsHelper;

    public function __construct(FaceRekognitionService $rekognitionService, AwsHelper $awsHelper)
    {
        $this->rekognitionService = $rekognitionService;
        $this->awsHelper = $awsHelper;
    }

    public function uploadAndIndexFace(Employee $employee, $file, string $imageKey)
    {
        $bucket = 'fixhr-employee-profiles';
        $imagePath = $employee->fh_business->b_unique_id . '/' . $imageKey;
        $uploadResult = $this->awsHelper->uploadFileToS3($bucket, $imagePath, $file);

        $profile_s3_url = NULL;
        $rekognition_id = NULL;
        if ($uploadResult['status']) {
            $rekognitionRes = $this->rekognitionService->indexFaces($bucket, $imagePath, 'fixhr_employees');
            $employee->emp_profile_s3_url = $uploadResult['ObjectURL'];
            $rekognition_id = $rekognitionRes['FaceRecords'][0]['Face']['FaceId'] ?? NULL;
            $employee->emp_rekognition_id = $rekognition_id;
            $employee->save();
        }

        return ['status' => $uploadResult['status'], 'message' => $uploadResult['message'] ?? 'Upload Successful', 'result'=>['profile_s3_url'=>$profile_s3_url, 'rekognition_id'=>$rekognition_id] ];
    }

    public function uploadVisitorImageAndAuthenticate(Request $request)
    {

        $user = Auth::user();
        $bucket = 'fixhr-visitor-images';
        $imageKey = $request->image->getClientOriginalName();
        $result = $this->rekognitionService->uploadAndAuthenticate($user->emp_b_id, $bucket, $request->image, $imageKey);

        if ($result['status']) {

            $employee = Employee::where(['emp_b_id' => $user->emp_b_id, 'emp_rekognition_id' => $result['result']['faceId']])->first();

            if ($employee) {
                // Get today's latest attendance
                $lastAttendance = AttendanceRecord::where('atd_b_id', $user->emp_b_id)
                    ->where('atd_emp_id', $employee->emp_id)
                    ->whereDate('atd_date', now())
                    ->orderBy('atd_check_in_time', 'desc')
                    ->first();
    
                $now = now();
                $gapInHours = 0;
                $gapInMinutes = 0;
                // $attendance_type = 'checkin';
    
                if ($lastAttendance && $lastAttendance->atd_check_in_time) {
                    $checkIn = \Carbon\Carbon::parse($lastAttendance->atd_check_in_time);
                    $gapInHours = $checkIn->diffInHours($now);
                    $gapInMinutes = $checkIn->diffInMinutes($now);
                    // $attendanceTypeOut = 'checkout';
                }
    
                if (!$lastAttendance || ($gapInMinutes >= 10)) {
                    // Proceed to mark attendance
                    $attendanceRes = app('App\Http\Controllers\Api\Attendance\PunchInApiController')
                        ->markAttendanceByFaceDetection($employee, $result['result']['image_url']);
                
                    // Fetch today's attendance records for display
                    $attendanceQuery = AttendanceRecord::with([
                        'fh_employee' => function ($query) {
                            $query->select('emp_id', 'emp_full_name', 'emp_code');
                        }
                    ])
                        ->where('atd_b_id', $user->emp_b_id)
                        ->where('atd_date', now()->format('Y-m-d'))
                        ->select('atd_id', 'atd_emp_id', 'atd_date', 'atd_check_in_time', 'atd_check_out_time')
                        ->orderBy('updated_at', 'desc');
        
                    $totalToDayAttendance = $attendanceQuery->count();
                    $attendanceData = $attendanceQuery->limit(5)->get();
        
                    $result['result'] = [
                        'employeeId'         => $employee->emp_code ?? '',
                        'name'               => $employee->emp_full_name ?? '',
                        'department'         => $employee->fh_department?->d_name,
                        'designation'        => $employee->fh_designation?->dg_name,
                        'email'              => $employee->emp_email ?? '',
                        'phone'              => $employee->emp_phone ?? '',
                        'address'            => $employee->emp_permanent_address ?? '',
                        'attendance_status'  => $attendanceRes['status'],
                        'attendance_message' => $attendanceRes['message'],
                        // 'attendance_type'    => $attendanceTypeOut ?? $attendance_type,
                        'recent_attendance'  => $attendanceData,
                        'total_attendance'   => $totalToDayAttendance,
                    ];
                } else {
                    $result['status'] = false;
                    $result['message'] = 'Already marked.';
                }
            } else {
                $result['status'] = false;
                $result['message'] = 'Employee not found.';
            }
        }
        return response()->json($result);
    }

    public function createLivenessSession()
    {
        return response()->json($this->rekognitionService->createLivenessSession('fixhr-visitor-images'));
    }
}
