<?php

namespace App\Jobs;

use App\Exports\Attendance\SelfieAttendanceReport;
use App\Helpers\CentralLogics;
use App\Models\Employee;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use Maatwebsite\Excel\Facades\Excel;

class GenerateSelfieAttendanceReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $params;
    public $jobId;
    
    // Increase timeout significantly
    public $timeout = 3600; // 60 minutes
    public $tries = 1; // Only try once, retries will make it worse

    public function __construct(array $params = [])
    {
        $this->params = $params;
        $this->jobId = uniqid('selfie_report_', true);
    }

    public function handle()
    {
        $p = $this->params;

        try {
            // Set initial status
            Redis::setex("report:status:{$this->jobId}", 7200, 'processing');
            
            // Set high memory and time limits
            ini_set('memory_limit', '4096M'); // 4GB memory
            set_time_limit(0); // No time limit for PHP script
            
            // Disable PHP execution time limit
            if (function_exists('set_time_limit')) {
                set_time_limit(0);
            }
            
            if (function_exists('ini_set')) {
                ini_set('max_execution_time', 0);
            }

            // Update progress: Starting
            $this->updateProgress('Starting selfie attendance report generation...', 10);
            
            // Date range check
            $fromDate = Carbon::parse($p['selectedFromDate']);
            $toDate = Carbon::parse($p['selectedToDate']);
            $diffInDays = $fromDate->diffInDays($toDate);
            
            if ($diffInDays > 31) {
                throw new Exception('Date range cannot exceed 31 days.');
            }

            $this->updateProgress('Fetching employee data...', 20);
            
            // Optimize: Load only required relationships
            $employeeQuery = Employee::with([
                'fh_branch',
                'fh_department',
                'fh_designation',
                'fh_shift_type',
                'fh_business',
            ])
            ->select([
                'emp_id',
                'emp_status',
                'emp_job_status',
                'emp_b_id',
                'emp_br_id',
                'emp_d_id',
                'emp_dg_id',
                'emp_role_id',
                'emp_grade_id',
                'emp_dlr_id',
                'emp_pl_id',
                'emp_pwo_id',
                'emp_full_name',
                'emp_supervisor_id',
                'emp_code',
            ])
            ->where('emp_b_id', $p['businessId'])
            ->where('emp_role_id', '<>', 1)
            ->when($p['selectedEmployeeId'] ?? null, fn($q, $v) => $q->where('emp_id', $v))
            ->when($p['employeeStatusId'] ?? null, fn($q, $v) => $q->where('emp_status', $v))
            ->when($p['selectedDepartmentId'] ?? null, fn($q, $v) => $q->where('emp_d_id', $v))
            ->when($p['selectedDealerId'] ?? null, fn($q, $v) => $q->where('emp_dlr_id', $v))
            ->when($p['selectedDesignationId'] ?? null, fn($q, $v) => $q->where('emp_dg_id', $v))
            ->when($p['selectedBranchId'] ?? null, fn($q, $v) => $q->where('emp_br_id', $v))
            ->when($p['selectedJobStatusId'] ?? null, fn($q, $v) => $q->where('emp_job_status', $v))
            ->when($p['selectedGradeId'] ?? null, fn($q, $v) => $q->where('emp_grade_id', $v))
            ->orderBy('emp_code', 'asc');

            $employees = $employeeQuery->get();

            if ($employees->isEmpty()) {
                Redis::setex("report:status:{$this->jobId}", 600, 'failed');
                Redis::setex("report:error:{$this->jobId}", 600, 'No employees found.');
                $this->updateProgress('No employees found.', 0);
                return;
            }

            $this->updateProgress('Processing attendance records...', 30);
            
            $records = collect();
            $employeeCount = $employees->count();
            $processedCount = 0;
            
            foreach ($employees as $employee) {
                $attendanceData = CentralLogics::newGetMonthlyAttendanceReportAuxiliary(
                    $employee,
                    null,
                    null,
                    null,
                    null,
                    $p['selectedFromDate'],
                    $p['selectedToDate']
                );

                foreach ($attendanceData as $data) {
                    // Create a pseudo AttendanceRecord object
                    $record = new \stdClass();
                    $record->atd_date = $data['date'];
                    $record->atd_attendance_status = $data['status_id'];
                    $record->atd_checkin_method_id = $data['checkingMethodId'] ?? null;
                    $record->atd_check_in_time = $data['checkInTime'] !== '-' ? Carbon::parse($data['checkInTime'])->format('H:i:s') : null;
                    $record->atd_check_out_time = $data['checkOutTime'] !== '-' ? Carbon::parse($data['checkOutTime'])->format('H:i:s') : null;
                    $record->atd_total_worked_hours = $data['workingHour'] !== '-' ? (float)$data['workingHour'] : null;
                    $record->atd_is_late = $data['lateCount'] ? 1 : 0;
                    $record->atd_late_duration = $data['late'];
                    $record->atd_is_early_exit = $data['earlyExitCount'] ? 1 : 0;
                    $record->atd_early_exit_duration = $data['earlyExit'];
                    $record->atd_is_overtime = $data['overtimeCount'] ? 1 : 0;
                    $record->atd_overtime_hours = $data['OT'];
                    $record->atd_remark = $data['attendance_remark'];
                    $record->atd_punchin_location = $data['checkInLocation'];
                    $record->atd_punchout_location = $data['checkOutLocation'];
                    $record->atd_punchin_photo = json_encode($data['checkInPhoto']);
                    $record->atd_punchout_photo = json_encode($data['checkOutPhoto']);
                    $record->atd_segments = json_encode($data['atd_segments']);
                    $record->atd_work_mode_type_id = $data['workModeId'] ?? null;
                    $record->fh_employees_details = $employee;
                    $record->fh_attendance_status = (object)[
                        'm_id' => $data['status_id'],
                        'm_name' => $data['status'],
                        'm_type' => $data['status_code'],
                        'm_other' => json_encode(['color' => $data['statusColor']]),
                    ];
                    $record->fh_policy_shift_timing = $employee->fh_shift_type;
                    $record->fh_business = (object)[
                        'b_id' => $p['businessId'], 
                        'b_name' => $employee->fh_business->b_name ?? ''
                    ];
                    $record->attendance_exceptions = $data['missedPunchCount'] ? [(object)[
                        'ae_in_time' => $data['checkInTime'],
                        'ae_out_time' => $data['checkOutTime'],
                        'ae_total_working' => $data['workingHour'],
                        'ae_reason_id' => null,
                        'ae_custom_reason' => $data['attendance_remark'],
                        'ae_status' => $data['approvedMissedPunchCount'] ? 157 : null,
                        'ae_stage_completed' => $data['approvedMissedPunchCount'] ? 1 : 0,
                    ]] : [];

                    // Apply filters
                    $matchesShift = !($p['selectedShiftId'] ?? null) || ($employee->fh_shift_type && $employee->fh_shift_type->pst_id == $p['selectedShiftId']);
                    $matchesWorkMode = !($p['selectedWorkModeId'] ?? null) || ($record->atd_work_mode_type_id == $p['selectedWorkModeId']);
                    $matchesCheckingMethod = !($p['selectedCheckingMethodId'] ?? null) || ($record->atd_checkin_method_id == $p['selectedCheckingMethodId']);
                    $matchesDealer = !($p['selectedDealerId'] ?? null) || ($employee->emp_dlr_id == $p['selectedDealerId']);

                    if ($matchesShift && $matchesWorkMode && $matchesCheckingMethod && $matchesDealer) {
                        $records->push($record);
                    }
                }
                
                // Update progress based on processed employees
                $processedCount++;
                $employeeProgress = 30 + (($processedCount / $employeeCount) * 40); // 30-70%
                $this->updateProgress("Processing employee {$processedCount} of {$employeeCount}...", (int)$employeeProgress);
            }

            if ($records->isEmpty()) {
                Redis::setex("report:status:{$this->jobId}", 600, 'failed');
                Redis::setex("report:error:{$this->jobId}", 600, 'No attendance records found.');
                $this->updateProgress('No attendance records found.', 0);
                return;
            }

            $this->updateProgress('Generating Excel file...', 75);
            
            // Prepare file name and path
            $fileName = 'SelfieAttendanceReport_' . now()->format('d-M-Y_His') . '.xlsx';
            
            $tempPath = storage_path('app/temp/' . $fileName);
            $tempDir = dirname($tempPath);
            
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            // Prepare date range for export
            $fromDate = Carbon::parse($p['selectedFromDate']);
            $toDate = Carbon::parse($p['selectedToDate']);
            $date = $fromDate->format('d-M-Y') . ' to ' . $toDate->format('d-M-Y');
            
            // Create exporter
            $exporter = new SelfieAttendanceReport($records, $p['filters'], $p['punchingMode'], $date);

            // Use chunked export for large files
            $this->updateProgress('Creating spreadsheet...', 80);
            
            Excel::store($exporter, 'temp/' . $fileName);
            
            $this->updateProgress('Finalizing file...', 85);
            
            if (!file_exists($tempPath)) {
                throw new Exception("Excel file was not created.");
            }
            
            $excelBinary = file_get_contents($tempPath);
            
            if (empty($excelBinary)) {
                throw new Exception("Excel file is empty.");
            }
            
            $this->updateProgress('Preparing download...', 90);
            
            $base64Data = base64_encode($excelBinary);
            Redis::setex("report:{$this->jobId}", 3600, $base64Data); // 1 hour expiry
            Redis::setex("report:filename:{$this->jobId}", 3600, $fileName);
            Redis::setex("report:status:{$this->jobId}", 3600, 'ready');
            
            $this->updateProgress('Report ready!', 95);
            
            unlink($tempPath);
            
            $this->updateProgress('Completed', 100);

        } catch (Exception $e) {
            Redis::setex("report:status:{$this->jobId}", 600, 'failed');
            Redis::setex("report:error:{$this->jobId}", 600, $e->getMessage());
            $this->updateProgress('Failed: ' . substr($e->getMessage(), 0, 100), 0);
        }
    }
    
    private function updateProgress(string $status, int $percentage)
    {
        // Store progress in Redis for the frontend to poll
        $progressData = [
            'status' => $status,
            'percentage' => $percentage,
            'timestamp' => now()->toISOString()
        ];
        
        Redis::setex("report:progress:{$this->jobId}", 7200, json_encode($progressData));
    }

    public function getJobId(): string
    {
        return $this->jobId;
    }
}