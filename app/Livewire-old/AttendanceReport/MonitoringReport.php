<?php

namespace App\Livewire\AttendanceReport;

use App\Exports\Attendance\DailyAttendanceReport;
use App\Exports\Attendance\MonitoringReport as AttendanceMonitoringReport;
use App\Exports\Attendance\WeeklyOffSummaryReport;
use App\Exports\Gatepass\GatepassReport;
use App\Helpers\ApprovalHelper;
use App\Helpers\CentralLogics;
use App\Http\Resources\Approval\Travel\ApprovalLogApiResource;
use App\Models\AttendanceRecord;
use App\Models\Branch;
use Livewire\Component;
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
use GPBMetadata\Google\Api\Monitoring;
use Maatwebsite\Excel\Facades\Excel;

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
        $selectedAttendanceStatusId ='',
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
    public $employeeStatusFilter = 'all';
    public $selectedFromDate;
    public $selectedToDate;
    public $slug;
    public function mount($slug)
    {
        $this->businessId = Auth::user()->emp_b_id;
        $this->selectedDate = Carbon::now()->toDateString(); // Format: YYYY-MM-DD
        $this->slug = $slug;
        $this->selectedFromDate = Carbon::now()->toDateString(); // Format: YYYY-MM-DD
        $this->selectedToDate = Carbon::now()->toDateString(); // Format: YYYY-MM-DD
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
        // dd($this->slug,$this->selectedAttendanceStatusId);
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
        // dd($property, $value);
        // Reset selectedEmployeeId and search only when employeeStatusFilter is updated
        if ($property === 'employeeStatusFilter') {
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
            'searchCheckingMethod' => 'selectedCheckingMethodId',
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
        $this->validate([
            'selectedDate' => 'required|date',
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
        ], [
            'selectedDate.required' => 'The date field is required.',
            'selectedDate.date' => 'The date must be a valid date.',
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
        ]);

         set_time_limit(0); // Remove execution time limit
        ini_set('memory_limit', '512M'); // Increase memory limit
       
        $date = Carbon::parse($this->selectedDate)->format('Y-m-d');
        // Fetch employees based on filters
        $employeeQuery = Employee::select([
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
        ])->where('emp_b_id', $this->businessId)
            ->where('emp_role_id', '<>', 1)
            // ->with(['fh_shift_type', 'fh_department', 'fh_designation', 'fh_branch'])
            ->when($this->selectedEmployeeId, fn($q, $v) => $q->where('emp_id', $v))
            ->when($this->selectedDepartmentId, fn($q, $v) => $q->where('emp_d_id', $v))
            ->when($this->selectedDealerId, fn($q, $v) => $q->where('emp_dlr_id', $v))
            ->when($this->selectedDesignationId, fn($q, $v) => $q->where('emp_dg_id', $v))
            ->when($this->selectedBranchId, fn($q, $v) => $q->where('emp_br_id', $v))
            ->when($this->selectedJobStatusId, fn($q, $v) => $q->where('emp_job_status', $v))
            ->when($this->selectedGradeId, fn($q, $v) => $q->where('emp_grade_id', $v))
            ->when($this->selectedWorkModeId, fn($q, $v) => $q->where('emp_work_mode_id', $v))
            ->orderBy('emp_code', 'asc');
        $employees = $employeeQuery->get();
        if ($employees->isEmpty()) {
            session()->flash('error', 'No employees found for the selected criteria.');
            return;
        }
        $records = collect();
        foreach ($employees as $employee) {
            $attendanceData = CentralLogics::newGetMonthlyAttendanceReportAuxiliary(
                $employee,
                null,
                null,
                null,
                null,
                $this->selectedFromDate,
                $this->selectedToDate
            );

           
            foreach ($attendanceData as $data) {
                // Create a pseudo AttendanceRecord object
                $record = new \stdClass();
                $record->atd_date = $data['date'];
                $record->atd_attendance_status = $data['status_id'];
                $record->atd_check_in_time = $data['checkInTime'] !== '-' ? Carbon::parse($data['checkInTime'])->format('H:i:s') : null;
                $record->atd_check_out_time = $data['checkOutTime'] !== '-' ? Carbon::parse($data['checkOutTime'])->format('H:i:s') : null;
                $record->atd_total_worked_hours = $data['workingHour'] !== '-' ? (float)$data['workingHour'] : null;
                $record->atd_is_late = $data['lateCount'] ? 1 : 0;
                $record->atd_late_duration = $data['late'];
                $record->atd_is_early_exit = $data['earlyExitCount'] ? 1 : 0;
                $record->atd_holiday_present = $data['holidayPresentCount'] ? 1 : 0;
                $record->atd_early_exit_duration = $data['earlyExit'];
                $record->atd_is_overtime = $data['overtimeCount'] ? 1 : 0;
                $record->atd_overtime_hours = $data['OT'];
                $record->atd_remark = $data['attendance_remark'];
                $record->atd_punchin_location = $data['checkInLocation'];
                $record->atd_punchout_location = $data['checkOutLocation'];
                $record->atd_punchin_photo = json_encode($data['checkInPhoto']);
                $record->atd_punchout_photo = json_encode($data['checkOutPhoto']);
                $record->atd_segments = json_encode($data['atd_segments']);
                $record->fh_employees_details = $employee;
                $record->atd_checkin_method_id = $data['checkingMethodId'];                
                $record->fh_attendance_status = (object)[
                    'm_id' => $data['status_id'],
                    'm_name' => $data['status'],
                    'm_type' => $data['status_code'],
                    'm_other' => json_encode(['color' => $data['statusColor']]),
                ];
                $record->fh_attendance_work_mode = $employee->fh_work_mode;
                $record->fh_policy_shift_timing = $employee->fh_shift_type;
                $record->fh_business = (object)['b_id' => $this->businessId];
                $record->attendance_exceptions = $data['missedPunchCount'] ? [(object)[
                    'ae_in_time' => $data['checkInTime'],
                    'ae_out_time' => $data['checkOutTime'],
                    'ae_total_working' => $data['workingHour'],
                    'ae_reason_id' => null,
                    'ae_custom_reason' => $data['attendance_remark'],
                    'ae_status' => $data['approvedMissedPunchCount'] ? 157 : null,
                    'ae_stage_completed' => $data['approvedMissedPunchCount'] ? 1 : 0,
                ]] : [];

                
                // Apply shift, work mode, attendance status, dealer, and checking method filters
                $matchesShift = !$this->selectedShiftId || ($employee->fh_shift_type && $employee->fh_shift_type->pst_id == $this->selectedShiftId);
                $matchesWorkMode = !$this->selectedWorkModeId || ($data['status_id'] == $this->selectedWorkModeId);
                $matchesAttendanceStatus = true;
                if ($this->slug == 'early-going') {
                    $matchesAttendanceStatus = $record->atd_is_early_exit == 1;
                } elseif ($this->slug == 'late-coming') {
                    $matchesAttendanceStatus = $record->atd_is_late == 1;
                } elseif ($this->slug == 'absent') {
                    $matchesAttendanceStatus = $record->atd_attendance_status == $this->selectedAttendanceStatusId;
                }elseif($this->slug == 'holiday-present'){
                    $matchesAttendanceStatus = $record->atd_attendance_status == $this->selectedAttendanceStatusId;
                // dd( $record->atd_holiday_present,$this->selectedAttendanceStatusId);
                }elseif($this->slug == 'half-day'){
                   
                    $matchesAttendanceStatus = $record->atd_attendance_status == $this->selectedAttendanceStatusId;
                }

                 else {
                    $matchesAttendanceStatus = $record->atd_attendance_status == $this->selectedAttendanceStatusId;
                }
                $matchesDealer = !$this->selectedDealerId || ($employee->emp_dlr_id == $this->selectedDealerId);
                $matchCheckInMethod = !$this->selectedCheckingMethodId || ($record->atd_checkin_method_id == $this->selectedCheckingMethodId);

                if ($matchesShift && $matchesWorkMode && $matchesAttendanceStatus && $matchesDealer && $matchCheckInMethod) {
                    $records->push($record);
                }
            }
        }


        // dd($records->toArray());

        if ($records->isEmpty()) {
            session()->flash('error', 'No attendance records found for the selected criteria.');
            return;
        }
        $fileName = ucfirst($this->slug) . '_Report_' . now()->format('Y-m-d') . '.xlsx';
        $fromDate = Carbon::parse($this->selectedFromDate);
        $toDate = Carbon::parse($this->selectedToDate);
        $date = $fromDate->format('d-M-Y') . ' to ' . $toDate->format('d-M-Y');
        if ($this->slug == 'weekly-off-present-summary') {
            return Excel::download(new WeeklyOffSummaryReport($records, $this->filters, $this->slug, $date), $fileName);
        } elseif ($this->slug == 'gate-pass') {
            return Excel::download(new GatepassReport($records, $this->filters, $this->slug, $date), $fileName);
        }
        return Excel::download(new AttendanceMonitoringReport($records, $this->filters, $this->slug, $date), $fileName);
    }
    public function render()
    {
        $employees = Employee::where('emp_role_id', '<>', 1)
            ->where('emp_b_id', $this->businessId)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('emp_full_name', 'like', '%' . $this->search . '%')
                        ->orWhere('emp_code', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->employeeStatusFilter === 'active', fn($q) => $q->where('emp_status', 71))
            ->when($this->employeeStatusFilter === 'inactive', fn($q) => $q->where('emp_status', '<>', 71))
            ->limit(100)
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
