<?php
namespace App\Livewire\AttendanceReport;
use App\Exports\Attendance\DailyAttendanceReport;
use App\Exports\Attendance\MonitoringReport as AttendanceMonitoringReport;
use App\Exports\Attendance\WeeklyOffSummaryReport;
use App\Exports\Gatepass\GatepassReport;
use App\Helpers\ApprovalHelper;
use App\Helpers\CentralLogics;
use App\Http\Resources\Approval\Travel\ApprovalLogApiResource;
use App\Jobs\GenerateMonitoringReportJob;
use App\Models\AttendanceRecord;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use App\Models\Department;
use App\Models\PolicyShiftTiming;
use App\Models\MasterTable;
use App\Models\Dealership;
use App\Models\Designation;
use App\Models\GatePass;
use App\Models\Grade;
use App\Models\WorkLocation;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use GPBMetadata\Google\Api\Monitoring;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\PolicyHolidayList;
use App\Models\PolicyWeekOff;
use Illuminate\Support\Facades\Log as FacadesLog;
use Illuminate\Support\Facades\Redis;
use Livewire\Component;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
class MonitoringReport extends Component
{
    public $search = '',
        $searchDepartment = '',
        $searchShift = '',
        $searchDealer = '',
        $searchWorkMode = '',
        $searchDesignation = '',
        $searchCheckingMethod = '',
        $searchBranch = '',
        $searchJobStatus = '',
        $searchGrade = '';
    public $selectedEmployeeId,
        $selectedAttendanceStatusId = '',
        $selectedDepartmentId,
        $selectedShiftId,
        $selectedDealerId,
        $selectedWorkModeId,
        $selectedDesignationId,
        $selectedCheckingMethodId,
        $selectedBranchId,
        $selectedJobStatusId,
        $selectedGradeId;
    public $businessId = '';
    //  static fields 
    public $selectedDate;
    public $employeeStatusId = 71;
    public $selectedFromDate;
    public $selectedToDate;
    public $slug;
    public $auth;
    // New properties for queue and progress
    public $generatingReport = false;
    public $currentJobId = null;
    public $reportReady = false;
    public $downloadMessage = '';
    public $progressStatus = 'Starting...';
    public $progressPercentage = 0;
    public $hasActiveJob = false;
    public $showAbsentWithPunches = false;
    public $showFilterPanel = false;
    public $sortBy = 'emp_code';
    public $reportName = '';
      public $originalFilters = [];
    public function mount($slug)
    {
        $this->businessId = Auth::user()->emp_b_id;
        $this->auth = Auth::user()->emp_id;
        $this->selectedDate = Carbon::now()->toDateString();
        $this->slug = $slug;
        $this->selectedFromDate = Carbon::now()->toDateString();
        $this->selectedToDate = Carbon::now()->toDateString();
        $slugMap = [
            'missed-punch' => 228,
            'present' => 251,
            'half-day' => 252,
            'comp-off' => 204,
            'absent' => 203,
            'holiday-present' => 319,
            'weekly-off-present-detailed' => 320,
            'weekly-off-present-summary' => 320,
        ];
        $this->selectedAttendanceStatusId = $slugMap[$slug] ?? null;
        // Dispatch event to check localStorage on page load
        $this->dispatch('check-localstorage-job');

       $this->reportName = Str::headline($slug);
    }
    // New method to restore job from localStorage
    public function restoreFromLocalStorage($jobId)
    {
        if ($jobId) {
            $this->currentJobId = $jobId;
            // Check Redis for current status
            $status = Redis::get("report:status:{$jobId}");
            if ($status === 'processing' || $status === 'queued') {
                $this->hasActiveJob = true;
                $this->generatingReport = true;
                // Get progress
                $progressData = Redis::get("report:progress:{$jobId}");
                if ($progressData) {
                    $progress = json_decode($progressData, true);
                    $this->progressStatus = $progress['status'] ?? 'Processing...';
                    $this->progressPercentage = $progress['percentage'] ?? 0;
                }
                Log::info('✅ Restored job from localStorage', [
                    'job_id' => $jobId,
                    'status' => $status,
                    'progress' => $this->progressPercentage
                ]);
            } elseif ($status === 'ready' || $status === 'failed') {
                // Clean up localStorage if job is done
                $this->dispatch('clear-localstorage-job');
                Log::info('🗑️ Cleared localStorage for completed/failed job', [
                    'job_id' => $jobId,
                    'status' => $status
                ]);
            }
        }
    }
    public $filters = [
        'department' => false,
        'shift' => false,
        'designation' => false,
        'workMode' => false,
        'checkingMethod' => false,
        'dealership' => false,
        'attendanceStatus' => false,
        'branch' => false,
        'jobStatus' => false,
        'grade' => false,
    ];
    public function toggleFilterPanel()
    {
        $this->showFilterPanel = !$this->showFilterPanel;
    }
    public function resetFilters()
    {
        $this->selectedEmployeeId        = null;
        $this->selectedDepartmentId      = null;
        $this->selectedShiftId           = null;
        $this->selectedDesignationId     = null;
        $this->selectedWorkModeId        = null;
        $this->selectedCheckingMethodId  = null;
        $this->selectedDealerId          = null;
        $this->selectedBranchId          = null;
        $this->selectedJobStatusId       = null;
        $this->selectedGradeId           = null;
        $this->search                = '';
        $this->searchDepartment      = '';
        $this->searchShift           = '';
        $this->searchDesignation     = '';
        $this->searchWorkMode        = '';
        $this->searchCheckingMethod  = '';
        $this->searchDealer          = '';
        $this->searchBranch          = '';
        $this->searchJobStatus       = '';
        $this->searchGrade           = '';
        // Reset filters to defaults — adjust as needed
        $this->filters = [
            'department' => false,
        'shift' => false,
        'designation' => false,
        'workMode' => false,
        'checkingMethod' => false,
        'dealership' => false,
        'attendanceStatus' => false,
        'branch' => false,
        'jobStatus' => false,
        'grade' => false,
        ];
    }
    public function toggleFilter($key, $state)
    {
        if (!array_key_exists($key, $this->filters)) return;
        $this->filters[$key] = filter_var($state, FILTER_VALIDATE_BOOLEAN);
        if (!$this->filters[$key]) {
            match ($key) {
                'department' => [
                    $this->selectedDepartmentId = null,
                    $this->searchDepartment = '',
                ],
                'shift' => [
                    $this->selectedShiftId = null,
                    $this->searchShift = '',
                ],
                'designation' => [
                    $this->selectedDesignationId = null,
                    $this->searchDesignation = '',
                ],
                'workMode' => [
                    $this->selectedWorkModeId = null,
                    $this->searchWorkMode = '',
                ],
                'checkingMethod' => [
                    $this->selectedCheckingMethodId = null,
                    $this->searchCheckingMethod = '',
                ],
                'dealership' => [
                    $this->selectedDealerId = null,
                    $this->searchDealer = '',
                ],
                'branch' => [
                    $this->selectedBranchId = null,
                    $this->searchBranch = '',
                ],
                'jobStatus' => [
                    $this->selectedJobStatusId = null,
                    $this->searchJobStatus = '',
                ],
                'grade' => [
                    $this->selectedGradeId = null,
                    $this->searchGrade = '',
                ],
                default => null,
            };
        }
    }
    public function selectEmployee($id, $name)
    {
        $this->selectedEmployeeId = $id;
        $this->search = $name;
    }
    public function selectDepartment($id, $name)
    {
        $this->selectedDepartmentId = $id;
        $this->searchDepartment = $name;
    }
    public function selectShift($id, $name)
    {
        $this->selectedShiftId = $id;
        $this->searchShift = $name;
    }
    public function selectDealer($id, $name)
    {
        $this->selectedDealerId = $id;
        $this->searchDealer = $name;
    }
    public function selectLocation($id, $name)
    {
        $this->selectedWorkModeId = $id;
        $this->searchWorkMode = $name;
    }
    public function selectDesignation($id, $name)
    {
        $this->selectedDesignationId = $id;
        $this->searchDesignation = $name;
    }
    public function selectCheckingMethod($id, $name)
    {
        $this->selectedCheckingMethodId = $id;
        $this->searchCheckingMethod = $name;
    }
    public function selectBranch($id, $name)
    {
        $this->selectedBranchId = $id;
        $this->searchBranch = $name;
    }
    public function selectJobStatus($id, $name)
    {
        $this->selectedJobStatusId = $id;
        $this->searchJobStatus = $name;
    }
    public function selectGrade($id, $name)
    {
        $this->selectedGradeId = $id;
        $this->searchGrade = $name;
    }
    public function updated($property, $value)
    {
        if ($property === 'employeeStatusId') {
            $this->selectedEmployeeId = null;
            $this->search = '';
            return;
        }
        $mapping = [
            'search' => 'selectedEmployeeId',
            'searchDepartment' => 'selectedDepartmentId',
            'searchShift' => 'selectedShiftId',
            'searchDesignation' => 'selectedDesignationId',
            'searchWorkMode' => 'selectedWorkModeId',
            'searchCheckingMethod' => 'selectedCheckingMethodId',
            'searchDealer' => 'selectedDealerId',
            'searchBranch' => 'selectedBranchId',
            'searchJobStatus' => 'selectedJobStatusId',
            'searchGrade' => 'selectedGradeId',
        ];
        if (array_key_exists($property, $mapping)) {
            $this->{$mapping[$property]} = null;
        }
    }
    public function generateReport()
    {
        $this->validate(
            [
                'selectedFromDate' => 'required|date',
                'selectedToDate'   => 'required|date|after_or_equal:selectedFromDate',
                'selectedEmployeeId' => 'nullable|exists:employees,emp_id',
                'selectedDepartmentId' => 'nullable|exists:departments,d_id',
                'selectedShiftId' => 'nullable|exists:policy_shift_timings,pst_id',
                'selectedDealerId' => 'nullable|exists:dealerships,dlr_id',
                'selectedWorkModeId' => 'nullable|exists:master_table,m_id',
                'selectedDesignationId' => 'nullable|exists:designations,dg_id',
                'selectedCheckingMethodId' => 'nullable|exists:master_table,m_id',
                'selectedBranchId' => 'nullable|exists:branches,br_id',
                'selectedJobStatusId' => 'nullable|exists:master_table,m_id',
                'selectedGradeId' => 'nullable|exists:master_table,m_id',
            ],
            [
                'selectedFromDate.required' => 'Please select start date.',
                'selectedFromDate.date' => 'Start date must be valid date.',
                'selectedToDate.required' => 'Please select end date.',
                'selectedToDate.date' => 'End date must be valid date.',
                'selectedToDate.after_or_equal' => 'End date cannot be before start date.',
                'selectedEmployeeId.exists' => 'Selected employee does not exist.',
                'selectedDepartmentId.exists' => 'Selected department does not exist.',
                'selectedShiftId.exists' => 'Selected shift does not exist.',
                'selectedDealerId.exists' => 'Selected dealer does not exist.',
                'selectedWorkModeId.exists' => 'Selected work mode is invalid.',
                'selectedDesignationId.exists' => 'Selected designation is invalid.',
                'selectedCheckingMethodId.exists' => 'Selected checking method is invalid.',
                'selectedBranchId.exists' => 'Selected branch is invalid.',
                'selectedJobStatusId.exists' => 'Selected job status is invalid.',
                'selectedGradeId.exists' => 'Selected grade is invalid.',
            ]
        );
        // Date range check
        $from = Carbon::parse($this->selectedFromDate);
        $to   = Carbon::parse($this->selectedToDate);
        if ($from->diffInDays($to) > 61) {
            $this->addError('selectedToDate', 'Date range cannot be more than 61 days.');
            return;
        }
        // Reset progress
        $this->progressStatus = 'Starting...';
        $this->progressPercentage = 0;
        // Prepare params
        $params = [
            'businessId' => $this->businessId,
            'slug' => $this->slug,
            'selectedFromDate' => $this->selectedFromDate,
            'selectedToDate' => $this->selectedToDate,
            'selectedAttendanceStatusId' => $this->selectedAttendanceStatusId,
            'employeeStatusId' => $this->employeeStatusId,
            'selectedEmployeeId' => $this->selectedEmployeeId,
            'selectedDepartmentId' => $this->selectedDepartmentId,
            'selectedShiftId' => $this->selectedShiftId,
            'selectedDealerId' => $this->selectedDealerId,
            'selectedWorkModeId' => $this->selectedWorkModeId,
            'selectedDesignationId' => $this->selectedDesignationId,
            'selectedCheckingMethodId' => $this->selectedCheckingMethodId,
            'selectedBranchId' => $this->selectedBranchId,
            'selectedJobStatusId' => $this->selectedJobStatusId,
            'selectedGradeId' => $this->selectedGradeId,
            'filters' => $this->filters,
            'userId' => $this->auth,
            'showAbsentWithPunches' => $this->showAbsentWithPunches,
            'sortBy' => $this->sortBy,
        ];
        // Dispatch job
        $job = new GenerateMonitoringReportJob($params);
        dispatch($job);
        $this->currentJobId = $job->getJobId();
        $this->generatingReport = true;
        $this->hasActiveJob = true;
        // Store in localStorage via JavaScript
        $this->dispatch('store-job-in-localstorage', jobId: $this->currentJobId);
        // Store user-job relationship in Redis
        Redis::setex("user:{$this->auth}:job:{$this->currentJobId}", 7200, 'active');
        // Store initial progress
        Redis::setex("report:progress:{$this->currentJobId}", 7200, json_encode([
            'status' => 'Starting...',
            'percentage' => 10
        ]));
        Log::info('📋 Job dispatched', [
            'job_id' => $this->currentJobId,
            'user' => $this->auth,
        ]);
    }
    public function checkReportProgress()
    {
        if ((!$this->generatingReport && !$this->hasActiveJob) || !$this->currentJobId) {
            return;
        }
        // Check progress from Redis
        $progressData = Redis::get("report:progress:{$this->currentJobId}");
        if ($progressData) {
            $progress = json_decode($progressData, true);
            $this->progressStatus = $progress['status'] ?? 'Processing...';
            $this->progressPercentage = $progress['percentage'] ?? 0;
            // Update stage based on progress
            if ($this->progressPercentage >= 90) {
                $this->progressStatus = 'Finalizing download...';
            } elseif ($this->progressPercentage >= 70) {
                $this->progressStatus = 'Generating Excel file...';
            } elseif ($this->progressPercentage >= 40) {
                $this->progressStatus = 'Processing attendance data...';
            } elseif ($this->progressPercentage >= 20) {
                $this->progressStatus = 'Collecting employee data...';
            }
        }
        // Check if report is ready
        $this->checkReportStatus();
    }
    public function checkReportStatus()
    {
        if ((!$this->generatingReport && !$this->hasActiveJob) || !$this->currentJobId) {
            Log::debug('checkReportStatus skipped - not generating or no job ID');
            return;
        }
        Log::debug('🔄 Checking report status', ['job_id' => $this->currentJobId]);
        $status = Redis::get("report:status:{$this->currentJobId}");
        if ($status === 'ready') {
            Log::info('✅ Report ready for download', ['job_id' => $this->currentJobId]);
            $filename = Redis::get("report:filename:{$this->currentJobId}") ?? 'Report_' . time() . '.xlsx';
            $base64Data = Redis::get("report:{$this->currentJobId}");
            if (empty($base64Data)) {
                Log::error('❌ Empty base64 data in Redis', ['job_id' => $this->currentJobId]);
                $this->dispatch('show-alert', [
                    'type' => 'error',
                    'message' => 'Report data is empty. Please try again.'
                ]);
                return;
            }
            // Update progress to 100%
            $this->progressStatus = 'Report ready!';
            $this->progressPercentage = 100;
            // Dispatch download event
            $this->dispatch('download-report', [
                'filename' => $filename,
                'data' => $base64Data,
            ]);
            // Show success toast
            $this->dispatch('show-alert', [
                'type' => 'success',
                'message' => "Report downloaded successfully!"
            ]);
            // Cleanup Redis
            $keys = [
                "report:{$this->currentJobId}",
                "report:filename:{$this->currentJobId}",
                "report:status:{$this->currentJobId}",
                "report:error:{$this->currentJobId}",
                "report:progress:{$this->currentJobId}",
                "user:{$this->auth}:job:{$this->currentJobId}",
            ];
            Redis::del($keys);
            // Clear localStorage
            $this->dispatch('clear-localstorage-job');
            // Reset states
            $this->generatingReport = false;
            $this->hasActiveJob = false;
            $this->currentJobId = null;
            Log::info('🧹 Redis keys cleaned up', ['keys' => $keys]);
        } elseif ($status === 'failed') {
            Log::error('❌ Report generation failed', ['job_id' => $this->currentJobId]);
            $error = Redis::get("report:error:{$this->currentJobId}") ?? 'Report generation failed.';
            $this->dispatch('show-alert', [
                'type' => 'error',
                'message' => $error
            ]);
            // Cleanup
            $keys = [
                "report:{$this->currentJobId}",
                "report:filename:{$this->currentJobId}",
                "report:status:{$this->currentJobId}",
                "report:error:{$this->currentJobId}",
                "report:progress:{$this->currentJobId}",
                "user:{$this->auth}:job:{$this->currentJobId}",
            ];
            Redis::del($keys);
            // Clear localStorage
            $this->dispatch('clear-localstorage-job');
            $this->generatingReport = false;
            $this->hasActiveJob = false;
            $this->currentJobId = null;
            $this->reportReady = false;
            $this->progressStatus = 'Failed';
            $this->progressPercentage = 0;
        }
    }
    public function closeSuccessCard()
    {
        $this->reportReady = false;
        $this->downloadMessage = '';
        $this->currentJobId = null;
        $this->generatingReport = false;
        $this->hasActiveJob = false;
        $this->progressStatus = 'Starting...';
        $this->progressPercentage = 0;
        // Also clear localStorage
        $this->dispatch('clear-localstorage-job');
    }
    public function hidePopup()
    {
        $this->generatingReport = false;
    }
    public function showPopup()
    {
        $this->generatingReport = true;
    }
    public function cancelJobAndClosePopup()
    {
        Log::info('❌ User canceled report generation', [
            'job_id' => $this->currentJobId,
            'user' => $this->auth
        ]);
        if ($this->currentJobId) {
            // Cleanup all Redis keys
            $keys = [
                "report:{$this->currentJobId}",
                "report:filename:{$this->currentJobId}",
                "report:status:{$this->currentJobId}",
                "report:error:{$this->currentJobId}",
                "report:progress:{$this->currentJobId}",
                "user:{$this->auth}:job:{$this->currentJobId}",
            ];
            foreach ($keys as $key) {
                Redis::del($key);
            }
            Log::info('🧹 Redis keys cleaned up after user cancellation', [
                'job_id' => $this->currentJobId,
                'keys' => $keys
            ]);
        }
        // Clear localStorage
        $this->dispatch('clear-localstorage-job');
        // Reset all states
        $this->generatingReport = false;
        $this->hasActiveJob = false;
        $this->currentJobId = null;
        $this->reportReady = false;
        $this->progressStatus = 'Cancelled by user';
        $this->progressPercentage = 0;
        // Show cancellation message
        $this->dispatch('show-alert', [
            'type' => 'info',
            'message' => 'Report generation was cancelled.'
        ]);
    }
    public function render()
    {
        $employees = collect();
        if (!empty($this->search)) {
            $employees = Employee::where('emp_role_id', '<>', 1)
                ->where('emp_b_id', $this->businessId)
                ->where(function ($q) {
                    $q->where('emp_full_name', 'like', '%' . $this->search . '%')
                        ->orWhere('emp_code', 'like', '%' . $this->search . '%');
                })
                ->when($this->employeeStatusId, fn($q) => $q->where('emp_status', $this->employeeStatusId))
                ->limit(100)
                ->get();
        }
        $employeeStatus = MasterTable::where('m_group', 'STATUS')
            ->limit(100)
            ->select('m_id', 'm_name')
            ->get();
        $departments = Department::where('d_b_id', $this->businessId)
            ->when(
                strlen($this->searchDepartment) >= 1 && !$this->selectedDepartmentId,
                fn($q) =>
                $q->where('d_name', 'like', "%{$this->searchDepartment}%")
            )
            ->limit(100)
            ->get();
        $shifts = PolicyShiftTiming::where('pst_b_id', $this->businessId)
            ->when(
                strlen($this->searchShift) >= 1 && !$this->selectedShiftId,
                fn($q) =>
                $q->where('pst_name', 'like', "%{$this->searchShift}%")
            )
            ->limit(100)
            ->get();
        $designations = Designation::where('dg_b_id', $this->businessId)
            ->when(
                strlen($this->searchDesignation) >= 1 && !$this->selectedDesignationId,
                fn($q) =>
                $q->where('dg_name', 'like', "%{$this->searchDesignation}%")
            )
            ->limit(100)
            ->get();
        $workMode = MasterTable::where('m_group', 'Work_Mode')
            ->when(
                strlen($this->searchWorkMode) >= 1 && !$this->selectedWorkModeId,
                fn($q) =>
                $q->where('m_name', 'like', "%{$this->searchWorkMode}%")
            )
            ->limit(100)
            ->get();
        $dealers = Dealership::where('dlr_b_id', $this->businessId)
            ->when(
                strlen($this->searchDealer) >= 1 && !$this->selectedDealerId,
                fn($q) =>
                $q->where('dlr_name', 'like', "%{$this->searchDealer}%")
            )
            ->limit(100)
            ->get();
        $checkingMethods = MasterTable::where('m_group', 'CHECKIN_METHOD')
            ->when(
                strlen($this->searchCheckingMethod) >= 1 && !$this->selectedCheckingMethodId,
                fn($q) =>
                $q->where('m_name', 'like', "%{$this->searchCheckingMethod}%")
            )
            ->limit(100)
            ->get();
        $branches = Branch::where('br_b_id', $this->businessId)
            ->when(
                strlen($this->searchBranch) >= 1 && !$this->selectedBranchId,
                fn($q) =>
                $q->where('br_name', 'like', "%{$this->searchBranch}%")
            )
            ->limit(100)
            ->get();
        $jobStatuses = MasterTable::where('m_group', 'JOB_STATUS')
            ->when(
                strlen($this->searchJobStatus) >= 1 && !$this->selectedJobStatusId,
                fn($q) =>
                $q->where('m_name', 'like', "%{$this->searchJobStatus}%")
            )
            ->limit(100)
            ->get();
        $grades = Grade::where('g_b_id', $this->businessId)
            ->when(
                strlen($this->searchGrade) >= 1 && !$this->selectedGradeId,
                fn($q) =>
                $q->where('g_name', 'like', "%{$this->searchGrade}%")
            )
            ->limit(100)
            ->get();
        return view('livewire.attendance-report.monitoring-report', compact(
            'employees',
            'employeeStatus',
            'departments',
            'shifts',
            'designations',
            'dealers',
            'workMode',
            'checkingMethods',
            'branches',
            'jobStatuses',
            'grades'
        ));
    }
}
