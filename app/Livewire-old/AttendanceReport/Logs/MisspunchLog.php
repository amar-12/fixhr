<?php

namespace App\Livewire\AttendanceReport\Logs;

use Livewire\Component;
use App\Exports\Attendance\MispunchReport; // New export class
use App\Exports\Attendance\MisspunchRegularizationReport;
use App\Helpers\ApprovalHelper;
use App\Models\AttendanceException;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\Department;
use App\Models\PolicyShiftTiming;
use App\Models\MasterTable;
use App\Models\Dealership;
use App\Models\Designation;
use App\Models\Branch;
use App\Models\Grade;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;

class MisspunchLog extends Component
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
        $employeeStatusId,
        $selectedAttendanceStatusId,
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
    public $selectedDate;
    public $employeeStatusFilter = 'all';
    public $selectedFromDate;
    public $selectedToDate;
    public $slug;
    public function mount($slug = null)
    {
        $this->businessId = Auth::user()->emp_b_id;
        $this->selectedDate = Carbon::now()->toDateString();
        $this->slug = $slug;
        $this->selectedFromDate = Carbon::now()->toDateString();
        $this->selectedToDate = Carbon::now()->toDateString();
        $slugMap = [
            'missed-punch' => 228,
            'present' => 251,
            'half-day' => 252,
            'late-coming' => 1,
            'early-going' => 1,
            'comp-off' => 204,
            'absent' => 203,
            'holiday-present' => 319,
            'weekly-off-present-detailed' => 320,
            'weekly-off-present-summary' => 320,
        ];
        $this->selectedAttendanceStatusId = $slugMap[$slug] ?? null;
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
        if ($property === 'employeeStatusFilter') {
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
        $this->validate([
            'selectedFromDate' => 'required|date',
            'selectedToDate' => 'required|date|after_or_equal:selectedFromDate',
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
        ]);
        $this->dispatch('show-alert', [
            'type' => 'info',
            'message' => 'Report generation started. Please wait...',
        ]);
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        $fromDate = Carbon::parse($this->selectedFromDate)->startOfDay();
        $toDate = Carbon::parse($this->selectedToDate)->endOfDay();
        // Employee filtering
        $employeeQuery = Employee::select([
            'emp_id',
            'emp_code',
            'emp_full_name',
            'emp_b_id',
            'emp_d_id',
            'emp_dg_id',
            'emp_br_id',
            'emp_dlr_id',
            'emp_job_status',
            'emp_grade_id',
            'emp_br_id',
            'emp_role_id',
            'emp_pl_id',
            'emp_pwo_id',
            'emp_full_name',
            'emp_supervisor_id',
            'emp_shift_type_id',
            'emp_work_mode_id',
        ])
            ->with([
                'fh_branch:br_id,br_name',
                'fh_department:d_id,d_name',
                'fh_designation:dg_id,dg_name',
                'fh_dealership:dlr_id,dlr_name',
                'fh_shift_type:pst_id,pst_name',
                'fh_work_mode:m_id,m_name',
                'fh_grade:g_id,g_name',
                'fh_work_mode:m_id,m_name',


            ])
            ->where('emp_b_id', $this->businessId)
            ->where('emp_role_id', '<>', 1)
            ->when($this->selectedEmployeeId, fn($q, $v) => $q->where('emp_id', $v))
            ->when($this->selectedDepartmentId, fn($q, $v) => $q->where('emp_d_id', $v))
            ->when($this->selectedDealerId, fn($q, $v) => $q->where('emp_dlr_id', $v))
            ->when($this->selectedDesignationId, fn($q, $v) => $q->where('emp_dg_id', $v))
            ->when($this->selectedBranchId, fn($q, $v) => $q->where('emp_br_id', $v))
            ->when($this->selectedJobStatusId, fn($q, $v) => $q->where('emp_job_status', $v))
            ->when($this->selectedGradeId, fn($q, $v) => $q->where('emp_grade_id', $v))
            ->orderBy('emp_code', 'asc');
        $employees = $employeeQuery->get();
        if ($employees->isEmpty()) {
            session()->flash('error', 'No employees found for the selected criteria.');
            return;
        }
        $records = collect();
        foreach ($employees as $employee) {
            // Fetch attendance records (device logs)
            $attendanceRecords = AttendanceRecord::where('atd_emp_id', $employee->emp_id)
                ->whereBetween('atd_date', [$fromDate, $toDate])
                ->where('atd_attendance_status', 228) // Missed punch status
                ->get();
            // Fetch attendance exception records (mis-punch)
            $attendanceExceptions = AttendanceException::where('ae_emp_id', $employee->emp_id)
                ->whereBetween('ae_date', [$fromDate, $toDate])
                ->where('ae_b_id', $this->businessId)
                ->when($this->selectedAttendanceStatusId, fn($q) => $q->where('ae_type_id', $this->selectedAttendanceStatusId))
                ->with([

                    'fh_employee:emp_id,emp_full_name,emp_code',
                    'fh_mispunch_type:m_id,m_name',
                    'fh_mispunch_reason:m_id,m_name',
                    'fh_approval_status:m_id,m_name,m_other',
                    'fh_plan_approval_log' => function ($query) {
                        $query->where('log_module_id', 229)
                            ->with([
                                'fh_employee:emp_id,emp_full_name',
                                'fh_role:role_id,role_name',
                                'fh_status:m_id,m_name'
                            ]);
                    },
                    'fh_approval_log2' => function ($query) {
                        $query->where('log_module_id', 229)
                            ->with([
                                'fh_employee:emp_id,emp_full_name',
                                'fh_role:role_id,role_name',
                                'fh_status:m_id,m_name'
                            ]);
                    }
                ])
                ->get();
            // dd($attendanceExceptions->toArray());
            // Combine records by date
            $dates = collect();
            foreach ($attendanceRecords as $record) {
                $dates->push(Carbon::parse($record->atd_date)->format('Y-m-d'));
            }
            foreach ($attendanceExceptions as $exception) {
                $dates->push(Carbon::parse($exception->ae_date)->format('Y-m-d'));
            }
            $dates = $dates->unique()->sort()->values();
            foreach ($dates as $date) {
                $record = $attendanceRecords->firstWhere(fn($r) => Carbon::parse($r->atd_date)->format('Y-m-d') === $date);
                $exception = $attendanceExceptions->firstWhere(fn($e) => Carbon::parse($e->ae_date)->format('Y-m-d') === $date);
                $row = [
                    'emp_code' => $employee->emp_code,
                    'emp_name' => $employee->emp_full_name,
                    'branch_name'        => $employee->fh_branch?->br_name ?? '-',
                    'department_name'    => $employee->fh_department?->d_name ?? '-',
                    'designation_name'   => $employee->fh_designation?->dg_name ?? '-',
                    'dealership_name'    => $employee->fh_dealership?->dlr_name ?? '-',
                    'shift_name'         => $employee->fh_shift_type?->pst_name ?? '-',
                    'work_mode_name'     => $employee->fh_work_mode?->m_name ?? '-',
                    'grade_name'         => $employee->fh_grade?->g_name ?? '-',
                    'job_status_name'    => $employee->fh_job_status?->m_name ?? '-',
                    'date' => Carbon::parse($date)->format('d-M-Y'),
                    'actual_check_in' => $record && $record->atd_check_in_time ? Carbon::parse($record->atd_check_in_time)->format('h:i A') : '-',
                    'actual_check_out' => $record && $record->atd_check_out_time ? Carbon::parse($record->atd_check_out_time)->format('h:i A') : '-',
                    'exception_check_in' => '-',
                    'exception_check_out' => '-',
                    'working_hour' => '-',
                    'type' => '-',
                    'reason' => '-',
                    'approval_status' => '-',
                    'approval_log' => '-',
                    'submitted_to' => '-',
                    'applied_date' => '-',
                ];
                if ($exception) {
                    $row['exception_check_in'] = $exception->ae_in_time ? Carbon::parse($exception->ae_in_time)->format('h:i A') : '-';
                    $row['exception_check_out'] = $exception->ae_out_time ? Carbon::parse($exception->ae_out_time)->format('h:i A') : '-';
                    $row['applied_date'] = $exception->created_at->format('d-M-Y H:i');
                    $row['type'] = optional($exception->fh_mispunch_type)->m_name ?? '-';
                    $row['reason'] = optional($exception->fh_mispunch_reason)->m_name ?? ($exception->ae_custom_reason ?? '-');
                    // Calculate working hours
                    if ($exception->ae_in_time && $exception->ae_out_time) {
                        $in = new \DateTime($exception->ae_in_time);
                        $out = new \DateTime($exception->ae_out_time);
                        $interval = $in->diff($out);
                        $row['working_hour'] = $interval->format('%H:%I');
                    }
                    // Format approval log
                    $formattedLog = '';
                    $comma = false;
                    $logData = $exception->fh_plan_approval_log->isNotEmpty() ? $exception->fh_plan_approval_log : $exception->fh_approval_log2;
                    if ($logData->isNotEmpty()) {
                        foreach ($logData as $log) {
                            if ($log->log_am_id == $exception->ae_am_id || $logData === $exception->fh_approval_log2) {
                                $employeeName = optional($log->fh_employee)->emp_full_name ?? 'N/A';
                                $roleName = optional($log->fh_role)->role_name ?? 'N/A';
                                $statusName = optional($log->fh_status)->m_name ?? 'N/A';
                                $formattedLog .= ($comma ? ', ' : '') . "$employeeName ($roleName) $statusName";
                                $comma = true;
                            }
                        }
                    } else {
                        $formattedLog = $exception->ae_status == 171 ? 'Auto Approved' : 'Awaiting';
                    }
                    $row['approval_log'] = $formattedLog;
                    // Get approval status
                    $jsonData = optional($exception->fh_approval_status)->m_other;
                    $decodedData = json_decode($jsonData, true);
                    $color = $decodedData['color'] ?? '';
                    $icon = $decodedData['web_icon'] ?? '';
                    $row['approval_status'] = '<span class="badge" style="background-color:' . $color . '"><i class="' . $icon . '">&nbsp;</i>' . (optional($exception->fh_approval_status)->m_name ?? '') . '</span>';
                    // Get next approver
                    $nextApproval = ApprovalHelper::getNextApprovalDetails(229, $exception->ae_id, $exception->ae_b_id);
                    $approverName = optional($nextApproval)['approver_name'] ?? null;
                    if (!$approverName) {
                        $approvalMapping = ApprovalHelper::getApprovalMapping($this->businessId, $exception->ae_emp_id, $exception->ae_module_id);
                        if ($approvalMapping) {
                            $approvalArray = ApprovalHelper::getApprovalArray($approvalMapping);
                            $approvalLog = $exception->fh_approval_log2->pluck('log_user_id')->toArray() ?? [];
                            $diff = array_values(array_diff($approvalArray, $approvalLog));
                            $approverName = $diff ? Employee::where('emp_id', $diff[0])->pluck('emp_full_name')->first() ?? '---' : '---';
                        } else {
                            $approverName = '---';
                        }
                    }
                    $row['submitted_to'] = $approverName;
                }
                $records->push($row);
            }
        }
        // Sort records by emp_code and date
        $records = $records->sortBy([
            ['emp_code', 'asc'],
            ['date', 'asc'],
        ])->values();
        if ($records->isEmpty()) {
            session()->flash('error', 'No attendance or mis-punch records found for the selected date range.');
            return;
        }
        $fileName = 'Missed_Punch_Regularization_Report_' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(
            new MisspunchRegularizationReport(
                $records,
                $this->filters,
                $this->slug,
                $fromDate->format('Y-m-d'),
                $toDate->format('Y-m-d'),
                $this->businessId
            ),
            $fileName
        );
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
        $checkingMethods = MasterTable::where('m_group', 'CHECKIN_METHOD')
            ->when(
                strlen($this->searchCheckingMethod) >= 1 && !$this->selectedCheckingMethodId,
                fn($q) => $q->where('m_name', 'like', "%{$this->searchCheckingMethod}%")
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
        return view('livewire.attendance-report.logs.misspunch-log', compact(
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
