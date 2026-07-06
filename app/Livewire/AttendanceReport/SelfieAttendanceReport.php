<?php

namespace App\Livewire\AttendanceReport;

use App\Jobs\GenerateSelfieAttendanceReportJob;
use App\Helpers\CentralLogics;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use App\Models\Department;
use App\Models\PolicyShiftTiming;
use App\Models\MasterTable;
use App\Models\Dealership;
use App\Models\Designation;
use App\Models\Branch;
use App\Models\Grade;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\Rule;

class SelfieAttendanceReport extends Component
{
    public $search = '',
        $searchAttendanceStatus = '',
        $searchDepartment = '',
        $searchShift = '',
        $searchDealer = '',
        $searchWorkMode = '',
        $searchDesignation = '',
        $searchBranch = '',
        $searchJobStatus = '',
        $searchGrade = '';
    public $selectedEmployeeId,
        $selectedAttendanceStatusId,
        $selectedDepartmentId,
        $selectedShiftId,
        $selectedDealerId,
        $selectedWorkModeId,
        $selectedDesignationId,
        $selectedCheckingMethodId = 314,
        $selectedBranchId,
        $selectedJobStatusId,
        $selectedGradeId;
    public $businessId = '';
    //  static fields 
    public $selectedDate;
    public $employeeStatusId = 71;
    public $selectedFromDate;
    public $selectedToDate;
    public $punchingMode = 'all';
    public $auth;
    public $sortBy = 'emp_code';
    
    // New properties for queue and progress
    public $generatingReport = false;
    public $currentJobId = null;
    public $reportReady = false;
    public $downloadMessage = '';
    public $progressStatus = 'Starting...';
    public $progressPercentage = 0;
    public $hasActiveJob = false;
    public $showFilterPanel = false;
    
    public $filters = [
        'department' => true,
        'shift' => true,
        'designation' => true,
        'workMode' => false,
        'dealership' => false,
        'branch' => false,
        'jobStatus' => false,
        'grade' => false,
        'punchingMode' => true,
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
    $this->searchDealer          = '';
    $this->searchBranch          = '';
    $this->searchJobStatus       = '';
    $this->searchGrade           = '';
  
  

    // Reset filters to defaults — adjust as needed
    $this->filters = [
         'department' => true,
        'shift' => true,
        'designation' => true,
        'workMode' => false,
        'dealership' => false,
        'branch' => false,
        'jobStatus' => false,
        'grade' => false,
        'punchingMode' => true,
    ];
}

public function toggleFilterPanel()
{
    $this->showFilterPanel = !$this->showFilterPanel;
}

    
    public function mount()
    {
        $this->businessId = Auth::user()->emp_b_id;
        $this->auth = Auth::user()->emp_id; // ADD THIS
        $this->selectedDate = Carbon::now()->toDateString(); // Format: YYYY-MM-DD
        $this->selectedFromDate = Carbon::now()->toDateString(); // Format: YYYY-MM-DD
        $this->selectedToDate = Carbon::now()->toDateString(); // Format: YYYY-MM-DD
        
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
                
                Log::info('✅ Restored selfie job from localStorage', [
                    'job_id' => $jobId,
                    'status' => $status,
                    'progress' => $this->progressPercentage
                ]);
            } elseif ($status === 'ready' || $status === 'failed') {
                // Clean up localStorage if job is done
                $this->dispatch('clear-localstorage-job');
                
                Log::info('🗑️ Cleared localStorage for completed/failed selfie job', [
                    'job_id' => $jobId,
                    'status' => $status
                ]);
            }
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
    
    public function updated($property, $value)
    {
        // Reset selectedEmployeeId and search only when employeeStatusFilter is updated
        if ($property === 'employeeStatusId') {
            $this->selectedEmployeeId = null;
            $this->search = '';
            return;
        }
        // Generic mapping of search fields to selected fields
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
        ];
        // Apply the generic reset logic
        if (array_key_exists($property, $mapping)) {
            $this->{$mapping[$property]} = null;
        }
    }
    
    public function generateReport()
{
    // Define validation rules
    $rules = [
        'selectedFromDate' => 'required|date',
        'selectedToDate' => [
            'required',
            'date',
            'after_or_equal:selectedFromDate',
        ],
        'selectedEmployeeId' => 'nullable|exists:employees,emp_id',
        'selectedDepartmentId' => 'nullable|exists:departments,d_id',
        'selectedShiftId' => 'nullable|exists:policy_shift_timings,pst_id',
        'selectedDealerId' => 'nullable|exists:dealers,dlr_id',
        'selectedWorkModeId' => 'nullable|exists:master_table,m_id',
        'selectedDesignationId' => 'nullable|exists:designations,dg_id',
        'selectedCheckingMethodId' => 'required|exists:master_table,m_id',
        'selectedBranchId' => 'nullable|exists:branches,br_id',
        'selectedJobStatusId' => 'nullable|exists:master_table,m_id',
        'selectedGradeId' => 'nullable|exists:master_table,m_id',
    ];
    
    // Add conditional validation for date range
    if ($this->selectedEmployeeId) {
        // If employee is selected, limit to maximum 31 days
        $rules['selectedToDate'][] = function ($attribute, $value, $fail) {
            $fromDate = \Carbon\Carbon::parse($this->selectedFromDate);
            $toDate = \Carbon\Carbon::parse($value);
            // Add 1 day to include both start and end dates
            $diffInDays = $fromDate->diffInDays($toDate) + 1;
            
            if ($diffInDays > 31) {
                $fail('When selecting an employee, the date range cannot exceed 31 days.');
            }
        };
    } else {
        // If no employee selected, date range must be exactly 1 day
        $rules['selectedToDate'][] = function ($attribute, $value, $fail) {
            $fromDate = \Carbon\Carbon::parse($this->selectedFromDate);
            $toDate = \Carbon\Carbon::parse($value);
            // Add 1 day to include both start and end dates
            $diffInDays = $fromDate->diffInDays($toDate) + 1;
            
            if ($diffInDays != 1) {
                $fail('When no employee is selected, the date range must be exactly 1 day.');
            }
        };
    }
    
    // Custom validation messages
    $messages = [
        'selectedFromDate.required' => 'The from date field is required.',
        'selectedFromDate.date' => 'The from date must be a valid date.',
        'selectedToDate.required' => 'The to date field is required.',
        'selectedToDate.date' => 'The to date must be a valid date.',
        'selectedToDate.after_or_equal' => 'The to date must be on or after the from date.',
        'selectedEmployeeId.exists' => 'The selected employee does not exist.',
        'selectedDepartmentId.exists' => 'The selected department does not exist.',
        'selectedShiftId.exists' => 'The selected shift does not exist.',
        'selectedDealerId.exists' => 'The selected dealer does not exist.',
        'selectedWorkModeId.exists' => 'The selected work mode does not exist.',
        'selectedDesignationId.exists' => 'The selected designation does not exist.',
        'selectedCheckingMethodId.exists' => 'The selected checking method does not exist.',
        'selectedBranchId.exists' => 'The selected branch does not exist.',
        'selectedJobStatusId.exists' => 'The selected job status does not exist.',
        'selectedGradeId.exists' => 'The selected grade does not exist.',
    ];
    
    // Validate
    $this->validate($rules, $messages);
    
    // Reset progress
    $this->progressStatus = 'Starting...';
    $this->progressPercentage = 0;
    
    // Prepare params
    $params = [
        'businessId' => $this->businessId,
        'selectedFromDate' => $this->selectedFromDate,
        'selectedToDate' => $this->selectedToDate,
        'punchingMode' => $this->punchingMode,
        'selectedCheckingMethodId' => $this->selectedCheckingMethodId,
        'employeeStatusId' => $this->employeeStatusId,
        'selectedEmployeeId' => $this->selectedEmployeeId,
        'selectedDepartmentId' => $this->selectedDepartmentId,
        'selectedShiftId' => $this->selectedShiftId,
        'selectedDealerId' => $this->selectedDealerId,
        'selectedWorkModeId' => $this->selectedWorkModeId,
        'selectedDesignationId' => $this->selectedDesignationId,
        'selectedBranchId' => $this->selectedBranchId,
        'selectedJobStatusId' => $this->selectedJobStatusId,
        'selectedGradeId' => $this->selectedGradeId,
        'filters' => $this->filters,
        'userId' => $this->auth,
         'sortBy'=>$this->sortBy,
    ];
    
    // Dispatch job
    $job = new GenerateSelfieAttendanceReportJob($params);
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
    
    Log::info('📋 Selfie report job dispatched', [
        'job_id' => $this->currentJobId,
        'user' => $this->auth,
        'date_range' => "{$this->selectedFromDate} to {$this->selectedToDate}",
        'has_employee' => !empty($this->selectedEmployeeId),
        'range_days' => Carbon::parse($this->selectedFromDate)->diffInDays(Carbon::parse($this->selectedToDate)) + 1,
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
        
        Log::debug('🔄 Checking selfie report status', ['job_id' => $this->currentJobId]);
        $status = Redis::get("report:status:{$this->currentJobId}");
        
        if ($status === 'ready') {
            Log::info('✅ Selfie report ready for download', ['job_id' => $this->currentJobId]);
            $filename = Redis::get("report:filename:{$this->currentJobId}") ?? 'Selfie_Report_' . time() . '.xlsx';
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
                'message' => "Selfie report downloaded successfully!"
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
            Log::error('❌ Selfie report generation failed', ['job_id' => $this->currentJobId]);
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
        Log::info('❌ User canceled selfie report generation', [
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
            'message' => 'Selfie report generation was cancelled.'
        ]);
    }
    
    public function render()
    {
        $employees = collect(); // Default empty collection
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
        
        $attendanceStatuses = MasterTable::where('m_group', 'Attendance_Status')
            ->when(
                strlen($this->searchAttendanceStatus) >= 1 && !$this->selectedAttendanceStatusId,
                fn($q) => $q->where('m_name', 'like', "%{$this->searchAttendanceStatus}%")
            )
            ->limit(100)
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
        
        return view('livewire.attendance-report.selfie-attendance-report', compact(
            'employees',
            'departments',
            'shifts',
            'designations',
            'dealers',
            'workMode',
            'branches',
            'jobStatuses',
            'grades',
            'employeeStatus'
        ));
    }
}