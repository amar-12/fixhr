<?php
namespace App\Livewire\AttendanceReport;
use Livewire\Component;
use App\Jobs\GenerateMonthlyAttendanceReportJob;
use App\Models\Branch;
use App\Models\FinancialYear;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use App\Models\Department;
use App\Models\PolicyShiftTiming;
use App\Models\MasterTable;
use App\Models\Dealership;
use App\Models\Designation;
use App\Models\Grade;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
class MonthlyAttendanceReport extends Component
{
    public $search = '',
        $searchDepartment = '',
        $searchShift = '',
        $searchDealer = '',
        $searchWorkMode = '',
        $searchDesignation = '',
        $searchBranch = '',
        $searchJobStatus = '',
        $searchCheckingMethod = '',
        $searchGrade = '';
    public $selectedEmployeeId,
        $selectedDepartmentId,
        $selectedShiftId,
        $selectedDealerId,
        $selectedWorkModeId,
        $selectedDesignationId,
        $selectedBranchId,
        $selectedJobStatusId,
        $selectedCheckingMethodId,
        $selectedGradeId;
    public $businessId = '';
    public $selectedDate;
    public $selectedYear;
    public $selectedMonth;
    public $slug;
    public $financialYears;
    public $fyStartDate = '';
    public $fyEndDate = '';
    public $months;
    public $year;
    public $employeeStatusId = 71;
    public $showFilterPanel = false;
    public $sortBy = 'emp_code';
    // New properties for queue and progress
    public $generatingReport = false;
    public $currentJobId = null;
    public $reportReady = false;
    public $downloadMessage = '';
    public $progressStatus = 'Starting...';
    public $progressPercentage = 0;
    public $hasActiveJob = false;
    public $auth;
    public $filters = [
        'department' => false,
        'shift' => false,
        'designation' => false,
        'workMode' => false,
        'dealership' => false,
        'branch' => false,
        'jobStatus' => false,
        'grade' => false,
        'checkingMethod' => false,
        'employeeStatusFilter' => false
    ];
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
        'dealership' => false,
        'branch' => false,
        'jobStatus' => false,
        'grade' => false,
        'checkingMethod' => false,
        'employeeStatusFilter' => false
        ];
    }
    public function toggleFilterPanel()
    {
        $this->showFilterPanel = !$this->showFilterPanel;
    }
    public function mount($slug)
    {
        $this->businessId = Auth::user()->emp_b_id;
        $this->auth = Auth::user()->emp_id;
        $this->selectedDate = Carbon::now()->toDateString();
        $this->slug = $slug;
        $this->financialYears = FinancialYear::where('fy_b_id', $this->businessId)->get();
        $currentYear = $this->financialYears->firstWhere('fy_is_current', 1);
        if ($currentYear) {
            $this->selectedYear = $currentYear->fy_id;
        }
        if ($this->selectedYear) {
            $financialYear = $this->financialYears->where('fy_id', $this->selectedYear)->first();
            $this->fyStartDate = $financialYear->fy_start_date;
            $this->fyEndDate = $financialYear->fy_end_date;
            $startYear = Carbon::parse($this->fyStartDate)->year;
            $endYear = Carbon::parse($this->fyEndDate)->year;
            $this->year = "{$startYear}-{$endYear}";
            $start = Carbon::parse($this->fyStartDate);
            $end = Carbon::parse($this->fyEndDate);
            $this->months = [];
            while ($start->lessThanOrEqualTo($end)) {
                $this->months[] = $start->format('F');
                $start->addMonth();
            }
            $currentMonth = Carbon::now()->month;
            $this->selectedMonth = in_array($currentMonth, range(1, 12)) ? $currentMonth : 1;
        } else {
            $this->fyStartDate = '';
            $this->fyEndDate = '';
            $this->year = null;
            $this->months = [];
            $this->selectedMonth = 1;
        }
        // Dispatch event to check localStorage on page load
        $this->dispatch('check-localstorage-job');
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
                Log::info('✅ Restored monthly job from localStorage', [
                    'job_id' => $jobId,
                    'status' => $status,
                    'progress' => $this->progressPercentage
                ]);
            } elseif ($status === 'ready' || $status === 'failed') {
                // Clean up localStorage if job is done
                $this->dispatch('clear-localstorage-job');
                Log::info('🗑️ Cleared localStorage for completed/failed monthly job', [
                    'job_id' => $jobId,
                    'status' => $status
                ]);
            }
        }
    }
    public function selectYear($id)
    {
        $this->selectedYear = $id;
        $financialYear = $this->financialYears->where('fy_id', $id)->first();
        if ($financialYear) {
            $this->fyStartDate = $financialYear->fy_start_date;
            $this->fyEndDate = $financialYear->fy_end_date;
            $startYear = Carbon::parse($this->fyStartDate)->year;
            $endYear = Carbon::parse($this->fyEndDate)->year;
            $this->year = "{$startYear}-{$endYear}";
        } else {
            $this->year = null;
            $this->fyStartDate = '';
            $this->fyEndDate = '';
        }
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
    public function selectCheckingMethod($id, $name)
    {
        $this->selectedCheckingMethodId = $id;
        $this->searchCheckingMethod = $name;
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
            'searchDealer' => 'selectedDealerId',
            'searchBranch' => 'selectedBranchId',
            'searchJobStatus' => 'selectedJobStatusId',
            'searchGrade' => 'selectedGradeId',
            'searchCheckingMethod' => 'selectedCheckingMethodId',
        ];
        if (array_key_exists($property, $mapping)) {
            $this->{$mapping[$property]} = null;
        }
    }
    public function generateReport()
    {
        $this->validate([
            'selectedYear' => 'required|exists:financial_years,fy_id',
            'selectedMonth' => 'required|numeric|between:1,12',
            'selectedDepartmentId' => 'nullable|exists:departments,d_id',
            'selectedShiftId' => 'nullable|exists:policy_shift_timings,pst_id',
            'selectedDealerId' => 'nullable|exists:dealerships,dlr_id',
            'selectedWorkModeId' => 'nullable|exists:master_table,m_id',
            'selectedDesignationId' => 'nullable|exists:designations,dg_id',
            'selectedBranchId' => 'nullable|exists:branches,br_id',
            'selectedJobStatusId' => 'nullable|exists:master_table,m_id',
            'selectedGradeId' => 'nullable|exists:master_table,m_id',
            'selectedEmployeeId' => 'nullable|exists:employees,emp_id',
            'selectedCheckingMethodId' => 'nullable|exists:master_table,m_id',
        ], [
            'selectedYear.required' => 'The financial year field is required.',
            'selectedYear.exists' => 'The selected financial year does not exist.',
            'selectedMonth.required' => 'The month field is required.',
            'selectedMonth.numeric' => 'The month must be a number.',
            'selectedMonth.between' => 'The month must be between 1 and 12.',
            'selectedEmployeeId.exists' => 'The selected employee does not exist.',
            'selectedDepartmentId.exists' => 'The selected department does not exist.',
            'selectedShiftId.exists' => 'The selected shift does not exist.',
            'selectedDealerId.exists' => 'The selected dealer does not exist.',
            'selectedWorkModeId.exists' => 'The selected work mode does not exist.',
            'selectedDesignationId.exists' => 'The selected designation does not exist.',
            'selectedBranchId.exists' => 'The selected branch does not exist.',
            'selectedJobStatusId.exists' => 'The selected job status does not exist.',
            'selectedGradeId.exists' => 'The selected grade does not exist.',
            'selectedCheckingMethodId.exists' => 'The selected checking method does not exist.',
        ]);
        // Reset progress
        $this->progressStatus = 'Starting...';
        $this->progressPercentage = 0;
        // Get financial year data
        $financialYear = $this->financialYears->where('fy_id', $this->selectedYear)->first();
        // Prepare params
        $params = [
            'businessId' => $this->businessId,
            'slug' => $this->slug,
            'selectedYear' => $this->selectedYear,
            'selectedMonth' => $this->selectedMonth,
            'financialYear' => [
                'fy_start_date' => $financialYear->fy_start_date,
                'fy_end_date' => $financialYear->fy_end_date,
            ],
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
            'sortBy' => $this->sortBy,
        ];
        // Dispatch job
        $job = new GenerateMonthlyAttendanceReportJob($params);
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
        Log::info('📋 Monthly report job dispatched', [
            'job_id' => $this->currentJobId,
            'user' => $this->auth,
            'month' => $this->selectedMonth,
            'year' => $this->selectedYear,
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
        Log::debug('🔄 Checking monthly report status', ['job_id' => $this->currentJobId]);
        $status = Redis::get("report:status:{$this->currentJobId}");
        if ($status === 'ready') {
            Log::info('✅ Monthly report ready for download', ['job_id' => $this->currentJobId]);
            $filename = Redis::get("report:filename:{$this->currentJobId}") ?? 'Monthly_Report_' . time() . '.xlsx';
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
                'message' => "Monthly report downloaded successfully!"
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
            Log::error('❌ Monthly report generation failed', ['job_id' => $this->currentJobId]);
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
        Log::info('❌ User canceled monthly report generation', [
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
            'message' => 'Monthly report generation was cancelled.'
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
                fn($q) => $q->where('d_name', 'like', "%{$this->searchDepartment}%")
            )
            ->limit(100)
            ->get();
        $shifts = PolicyShiftTiming::where('pst_b_id', $this->businessId)
            ->when(
                strlen($this->searchShift) >= 1 && !$this->selectedShiftId,
                fn($q) => $q->where('pst_name', 'like', "%{$this->searchShift}%")
            )
            ->limit(100)
            ->get();
        $designations = Designation::where('dg_b_id', $this->businessId)
            ->when(
                strlen($this->searchDesignation) >= 1 && !$this->selectedDesignationId,
                fn($q) => $q->where('dg_name', 'like', "%{$this->searchDesignation}%")
            )
            ->limit(100)
            ->get();
        $workMode = MasterTable::where('m_group', 'Work_Mode')
            ->when(
                strlen($this->searchWorkMode) >= 1 && !$this->selectedWorkModeId,
                fn($q) => $q->where('m_name', 'like', "%{$this->searchWorkMode}%")
            )
            ->limit(100)
            ->get();
        $dealers = Dealership::where('dlr_b_id', $this->businessId)
            ->when(
                strlen($this->searchDealer) >= 1 && !$this->selectedDealerId,
                fn($q) => $q->where('dlr_name', 'like', "%{$this->searchDealer}%")
            )
            ->limit(100)
            ->get();
        $branches = Branch::where('br_b_id', $this->businessId)
            ->when(
                strlen($this->searchBranch) >= 1 && !$this->selectedBranchId,
                fn($q) => $q->where('br_name', 'like', "%{$this->searchBranch}%")
            )
            ->limit(100)
            ->get();
        $jobStatuses = MasterTable::where('m_group', 'JOB_STATUS')
            ->when(
                strlen($this->searchJobStatus) >= 1 && !$this->selectedJobStatusId,
                fn($q) => $q->where('m_name', 'like', "%{$this->searchJobStatus}%")
            )
            ->limit(100)
            ->get();
        $grades = Grade::where('g_b_id', $this->businessId)
            ->when(
                strlen($this->searchGrade) >= 1 && !$this->selectedGradeId,
                fn($q) => $q->where('g_name', 'like', "%{$this->searchGrade}%")
            )
            ->limit(100)
            ->get();
        $checkingMethods = MasterTable::where('m_group', 'CHECKIN_METHOD')
            ->when(
                strlen($this->searchCheckingMethod) >= 1 && !$this->selectedCheckingMethodId,
                fn($q) => $q->where('m_name', 'like', "%{$this->searchCheckingMethod}%")
            )
            ->limit(100)
            ->get();
        return view('livewire.attendance-report.monthly-attendance-report', compact(
            'departments',
            'shifts',
            'designations',
            'dealers',
            'workMode',
            'branches',
            'jobStatuses',
            'grades',
            'employees',
            'employeeStatus',
            'checkingMethods'
        ));
    }
}
