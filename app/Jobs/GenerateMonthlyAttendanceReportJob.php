<?php

namespace App\Jobs;

use App\Exports\Attendance\AttendanceMonthlyReport;
use App\Exports\Attendance\MonthlyDetailReport;
use App\Exports\Attendance\MonthlyInOutReport;
use App\Helpers\CentralLogics;
use App\Models\Employee;
use App\Models\PolicyHolidayList;
use App\Models\PolicyWeekOff;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;
use Maatwebsite\Excel\Facades\Excel;

class GenerateMonthlyAttendanceReportJob implements ShouldQueue
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
        $this->jobId = uniqid('monthly_report_', true);
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
            $this->updateProgress('Starting monthly report generation...', 10);
            
            // Calculate dates based on financial year and month
            $financialYear = $p['financialYear'];
            $selectedMonth = $p['selectedMonth'];
            
            $fyStart = Carbon::parse($financialYear['fy_start_date']);
            $fyEnd = Carbon::parse($financialYear['fy_end_date']);
            
            $startMonth = $fyStart->month;
            $endMonth = $fyEnd->month;
            $startYear = $fyStart->year;
            $endYear = $fyEnd->year;
            
            $selectedYear = $startYear;
            if ($selectedMonth < $startMonth || $selectedMonth > 12) {
                $selectedYear = $endYear;
            } elseif ($selectedMonth <= $endMonth && $endMonth < $startMonth) {
                $selectedYear = $endYear;
            }

            $this->updateProgress('Fetching employee data...', 20);
            
            // Optimize: Load only required relationships
            $employeeQuery = Employee::with([
                'fh_branch',
                'fh_department',
                'fh_designation',
                'fh_dealership',
                'fh_shift_type',
                'fh_work_mode',
                'fh_grade',
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
                'emp_shift_type_id',
                'emp_work_mode_id',
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
            ->when($p['selectedWorkModeId'] ?? null, fn($q, $v) => $q->where('emp_work_mode_id', $v))
            ->orderBy('emp_code', 'asc');

            $employees = $employeeQuery->get();

            if ($employees->isEmpty()) {
                Redis::setex("report:status:{$this->jobId}", 600, 'failed');
                Redis::setex("report:error:{$this->jobId}", 600, 'No employees found.');
                $this->updateProgress('No employees found.', 0);
                return;
            }

            $this->updateProgress('Preparing date ranges...', 25);
            
            // Prepare holiday records
            $startDate = Carbon::create($selectedYear, $selectedMonth, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            
            $holidayRecords = collect();
            $holidays = PolicyHolidayList::where('phl_b_id', $p['businessId'])
                ->where('phl_day_type_id', 201)
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('phl_start_date', [$startDate, $endDate])
                        ->orWhereBetween('phl_end_date', [$startDate, $endDate])
                        ->orWhere(function ($q) use ($startDate, $endDate) {
                            $q->where('phl_start_date', '<=', $startDate)
                              ->where('phl_end_date', '>=', $endDate);
                        });
                })
                ->get();

            foreach ($holidays as $holiday) {
                $holidayStart = Carbon::parse($holiday->phl_start_date)->startOfDay();
                $holidayEnd = $holiday->phl_end_date ? Carbon::parse($holiday->phl_end_date)->endOfDay() : $holidayStart->copy()->endOfDay();
                $period = CarbonPeriod::create($holidayStart, $holidayEnd);
                foreach ($period as $date) {
                    if ($date->between($startDate, $endDate)) {
                        $holidayRecords[$date->toDateString()] = (object)[
                            'phl_id' => $holiday->phl_id,
                            'phl_name' => $holiday->phl_name,
                            'phl_date' => $date->toDateString(),
                        ];
                    }
                }
            }

            // Week Offs
            $weekOffDates = [];
            $weekOffPolicies = PolicyWeekOff::where('pwo_b_id', $p['businessId'])->get();
            foreach ($weekOffPolicies as $policy) {
                $days = $policy->getDays($policy->pwo_day_ids);
                foreach ($days as $day) {
                    $date = $startDate->copy();
                    while ($date->lte($endDate)) {
                        if ($date->is($day)) {
                            $weekOffDates[] = $date->toDateString();
                        }
                        $date->addDay();
                    }
                }
            }
            $weekOffDates = array_unique($weekOffDates);

            $this->updateProgress('Processing attendance records...', 30);
            
            $records = collect();
            $employeeCount = $employees->count();
            $processedCount = 0;
            
            foreach ($employees as $employee) {
                $attendanceData = CentralLogics::newGetMonthlyAttendanceDetails(
                    $employee,
                    $selectedMonth,
                    $selectedYear,
                    $holidayRecords,
                    $weekOffDates
                );

                foreach ($attendanceData as $data) {
                    // Handle $data as array or object
                    $data = is_object($data) ? (array) $data : $data;
                    
                    // Create a pseudo AttendanceRecord object
                    $record = new \stdClass();
                    $record->atd_date = $data['date'] ?? null;
                    $record->atd_attendance_status = $data['status_id'] ?? null;
                    $record->atd_check_in_time = !empty($data['checkInTime'])
                        ? Carbon::parse($data['checkInTime'])->format('H:i:s')
                        : null;

                    $record->atd_check_out_time = !empty($data['checkOutTime'])
                        ? Carbon::parse($data['checkOutTime'])->format('H:i:s')
                        : null;

                    $record->atd_total_worked_hours = ($data['workingHour'] ?? '-') !== '-' ? (float)($data['workingHour'] ?? 0) : null;
                    $record->atd_is_late = ($data['lateCount'] ?? 0) ? 1 : 0;
                    $record->atd_late_duration = $data['late'] ?? null;
                    $record->atd_is_early_exit = ($data['earlyExitCount'] ?? 0) ? 1 : 0;
                    $record->atd_early_exit_duration = $data['earlyExit'] ?? null;
                    $record->atd_is_overtime = ($data['overtimeCount'] ?? 0) ? 1 : 0;
                    $record->atd_overtime_hours = $data['OT'] ?? null;
                    $record->atd_remark = $data['attendance_remark'] ?? null;
                    $record->atd_punchin_location = $data['checkInLocation'] ?? null;
                    $record->atd_punchout_location = $data['checkOutLocation'] ?? null;
                    $record->atd_punchin_photo = isset($data['checkInPhoto']) ? json_encode($data['checkInPhoto']) : null;
                    $record->atd_punchout_photo = isset($data['checkOutPhoto']) ? json_encode($data['checkOutPhoto']) : null;
                    $record->atd_segments = isset($data['atd_segments']) ? json_encode($data['atd_segments']) : null;
                    $record->fh_employees_details = $employee; // Keep as object
                    $record->atd_checkin_method_id = $data['checkingMethodId'] ?? null;
                    $record->fh_attendance_status = (object)[
                        'm_id' => $data['status_id'] ?? null,
                        'm_name' => $data['status'] ?? null,
                        'm_type' => $data['status_code'] ?? null,
                        'm_other' => isset($data['statusColor']) ? json_encode(['color' => $data['statusColor']]) : null,
                    ];
                    $record->fh_attendance_work_mode = $employee->fh_work_mode; // Keep as object
                    $record->fh_policy_shift_timing = $employee->fh_shift_type; // Keep as object
                    $record->fh_business = (object)['b_id' => $p['businessId']];
                    $record->attendance_exceptions = ($data['missedPunchCount'] ?? 0) ? [(object)[
                        'ae_in_time' => $data['checkInTime'] ?? null,
                        'ae_out_time' => $data['checkOutTime'] ?? null,
                        'ae_total_working' => ($data['workingHour'] ?? '-') !== '-' ? (float)($data['workingHour'] ?? 0) : null,
                        'ae_reason_id' => null,
                        'ae_custom_reason' => $data['attendance_remark'] ?? null,
                        'ae_status' => ($data['approvedMissedPunchCount'] ?? 0) ? 157 : null,
                        'ae_stage_completed' => ($data['approvedMissedPunchCount'] ?? 0) ? 1 : 0,
                    ]] : [];

                    // Apply filters
                    $matchesShift = !($p['selectedShiftId'] ?? null) || ($employee->fh_shift_type && $employee->fh_shift_type->pst_id == $p['selectedShiftId']);
                    $matchesWorkMode = !($p['selectedWorkModeId'] ?? null) || ($data['status_id'] ?? null) == $p['selectedWorkModeId'];
                    $matchesDealer = !($p['selectedDealerId'] ?? null) || ($employee->emp_dlr_id == $p['selectedDealerId']);
                    $matchCheckInMethod = !($p['selectedCheckingMethodId'] ?? null) || ($record->atd_checkin_method_id == $p['selectedCheckingMethodId']);

                    if ($matchesShift && $matchesWorkMode && $matchesDealer && $matchCheckInMethod) {
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
                Redis::setex("report:error:{$this->jobId}", 600, 'No records found.');
                $this->updateProgress('No records found.', 0);
                return;
            }

            $this->updateProgress('Generating Excel file...', 75);
            
            // Prepare file name and path
            $monthName = date('F', mktime(0, 0, 0, $selectedMonth, 1));
            $fileName = ucfirst($p['slug']) . '_Report_' . $monthName . '_' . $selectedYear . '_' . now()->format('Y-m-d_His') . '.xlsx';

            $tempPath = storage_path('app/temp/' . $fileName);
            $tempDir = dirname($tempPath);
            
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            // Determine which exporter to use based on slug
            $yearLabel = "{$startYear}-{$endYear}";
            
            $exporter = match ($p['slug']) {
                'monthly-attendance-basic' => new AttendanceMonthlyReport($records, $p['filters'], $p['slug'], $yearLabel, $selectedMonth, $financialYear['fy_start_date'], $financialYear['fy_end_date'], $p['businessId']),
                'monthly-attendance-in-out' => new MonthlyInOutReport($records, $p['filters'], $p['slug'], $yearLabel, $selectedMonth, $financialYear['fy_start_date'], $financialYear['fy_end_date'], $p['businessId']),
                'monthly-attendance-detail' => new MonthlyDetailReport($records, $p['filters'], $p['slug'], $yearLabel, $selectedMonth, $financialYear['fy_start_date'], $financialYear['fy_end_date'], $p['businessId']),
                default => new AttendanceMonthlyReport($records, $p['filters'], $p['slug'], $yearLabel, $selectedMonth, $financialYear['fy_start_date'], $financialYear['fy_end_date'], $p['businessId']),
            };

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