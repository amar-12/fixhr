<?php

namespace App\Livewire\AttendanceReport;

use App\Exports\Attendance\DailyAttendanceReport;
use App\Helpers\CentralLogics;
use App\Models\AttendanceRecord;
use App\Models\Branch;
use App\Models\PolicyHolidayList;
use App\Models\PolicyWeekOff;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use App\Models\Department;
use App\Models\PolicyShiftTiming;
use App\Models\MasterTable;
use App\Models\Dealership;
use App\Models\Designation;
use App\Models\Grade;
use App\Models\WorkLocation;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Maatwebsite\Excel\Facades\Excel;

class DailyAttendanceDetailReport extends Component
{
    public $search = '',
        $searchAttendanceStatus = '',
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
        $selectedAttendanceStatusId,
        $selectedDepartmentId,
        $selectedShiftId,
        $selectedDealerId,
        $selectedWorkModeId,
        $selectedDesignationId,
        $selectedBranchId,
        $selectedJobStatusId,
        $selectedGradeId;
    public $businessId = '';
    //  static fields 
    public $selectedDate;
    public $employeeStatusId = 71;
    public $sortBy = 'emp_code';
    public $showFilterPanel = false;
    public $originalFilters = [];
    public function mount()
    {
        $this->businessId = Auth::user()->emp_b_id;
        $this->selectedDate = Carbon::now()->toDateString(); // Format: YYYY-MM-DD
        $this->originalFilters = $this->filters;
    }
    public $filters = [
        'department' => true,
        'shift' => true,
        'designation' => true,
        'workMode' => false,
        'dealership' => false,
        'attendanceStatus' => true,
        'branch' => true,
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
                'dealership' => [
                    $this->selectedDealerId = null,
                    $this->searchDealer = '',
                ],
                'attendanceStatus' => [
                    $this->selectedAttendanceStatusId = null,
                    $this->searchAttendanceStatus = '',
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
        $this->selectedDealerId          = null;
        $this->selectedBranchId          = null;
        $this->selectedJobStatusId       = null;
        $this->selectedGradeId           = null;
        $this->selectedAttendanceStatusId = null;
        $this->search                = '';
        $this->searchDepartment      = '';
        $this->searchShift           = '';
        $this->searchDesignation     = '';
        $this->searchWorkMode        = '';
        $this->searchDealer          = '';
        $this->searchBranch          = '';
        $this->searchJobStatus       = '';
        $this->searchGrade           = '';
        $this->searchAttendanceStatus = '';
        // Reset filters to defaults — adjust as needed
        $this->filters = [
            'department'       => true,
            'shift'            => true,
            'designation'      => true,
            'workMode'         => false,
            'dealership'       => false,
            'attendanceStatus' => true,
            'branch'           => true,
            'jobStatus'        => false,
            'grade'            => false,
        ];
    }
    public function selectEmployee($id, $name)
    {
        $this->selectedEmployeeId = $id;
        $this->search = $name;
    }
    public function selectAttendanceStatus($id, $name)
    {
        $this->selectedAttendanceStatusId = $id;
        $this->searchAttendanceStatus = $name;
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
        // dd($property, $value);
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
            'searchAttendanceStatus' => 'selectedAttendanceStatusId',
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
            'selectedAttendanceStatusId' => 'nullable|exists:master_table,m_id',
            'selectedDepartmentId' => 'nullable|exists:departments,d_id',
            'selectedShiftId' => 'nullable|exists:policy_shift_timings,pst_id',
            'selectedDealerId' => 'nullable|exists:dealerships,dlr_id',
            'selectedWorkModeId' => 'nullable|exists:master_table,m_id',
            'selectedDesignationId' => 'nullable|exists:designations,dg_id',
            'selectedBranchId' => 'nullable|exists:branches,br_id',
            'selectedJobStatusId' => 'nullable|exists:master_table,m_id',
            'selectedGradeId' => 'nullable|exists:master_table,m_id',
        ], [
            'selectedDate.required' => 'The date field is required.',
            'selectedDate.date' => 'The date must be a valid date.',
            'selectedEmployeeId.exists' => 'The selected employee does not exist.',
            'selectedAttendanceStatusId.exists' => 'The selected attendance status does not exist.',
            'selectedDepartmentId.exists' => 'The selected department does not exist.',
            'selectedShiftId.exists' => 'The selected shift does not exist.',
            'selectedDealerId.exists' => 'The selected dealer does not exist.',
            'selectedWorkModeId.exists' => 'The selected work mode does not exist.',
            'selectedDesignationId.exists' => 'The selected designation does not exist.',
            'selectedBranchId.exists' => 'The selected branch does not exist.',
            'selectedJobStatusId.exists' => 'The selected job status does not exist.',
            'selectedGradeId.exists' => 'The selected grade does not exist.',
        ]);
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
            'emp_dlr_id',
            'emp_work_mode_id',
        ])->where('emp_b_id', $this->businessId)
            ->where('emp_role_id', '<>', 1)
            // ->with(['fh_shift_type', 'fh_department', 'fh_designation', 'fh_branch','fh_dealership'])
            ->when($this->selectedEmployeeId, fn($q, $v) => $q->where('emp_id', $v))
            ->when($this->employeeStatusId, function ($q, $v) {
                if ($v === 'resigned') {
                    $q->whereNotNull('emp_last_working_date');
                } else {
                    $q->where('emp_status', $v);
                }
            })
            ->when($this->selectedDepartmentId, fn($q, $v) => $q->where('emp_d_id', $v))
            ->when($this->selectedDealerId, fn($q, $v) => $q->where('emp_dlr_id', $v))
            ->when($this->selectedDesignationId, fn($q, $v) => $q->where('emp_dg_id', $v))
            ->when($this->selectedBranchId, fn($q, $v) => $q->where('emp_br_id', $v))
            ->when($this->selectedJobStatusId, fn($q, $v) => $q->where('emp_job_status', $v))
            ->when($this->selectedGradeId, fn($q, $v) => $q->where('emp_grade_id', $v))
            ->orderBy($this->sortBy === 'emp_name' ? 'emp_full_name' : 'emp_code', 'asc');
        $employees = $employeeQuery->get();

        // dd($employees->toArray());
        if ($employees->isEmpty()) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'No employees found for the selected criteria.']);
            return;
        }
        $records = collect();
        foreach ($employees as $employee) {
            // Prepare holiday records
            $selectedDate = $this->selectedDate;
            $holidayRecords = collect();
            $holidays = PolicyHolidayList::where('phl_b_id', $this->businessId)->where('phl_day_type_id', 201)
                ->where(function ($query) use ($selectedDate) {
                    $query->whereBetween('phl_start_date', [$selectedDate, $selectedDate])
                        ->orWhereBetween('phl_end_date', [$selectedDate, $selectedDate])
                        ->orWhere(function ($q) use ($selectedDate) {
                            $q->where('phl_start_date', '<=', $selectedDate)
                                ->where('phl_end_date', '>=', $selectedDate);
                        });
                })
                ->get();
            foreach ($holidays as $holiday) {
                $holidayStart = Carbon::parse($holiday->phl_start_date)->startOfDay();
                $holidayEnd = $holiday->phl_end_date ? Carbon::parse($holiday->phl_end_date)->endOfDay() : $holidayStart->copy()->endOfDay();
                $period = CarbonPeriod::create($holidayStart, $holidayEnd);
                foreach ($period as $hDate) {
                    if ($hDate->between($selectedDate, $selectedDate)) {
                        $holidayRecords[$hDate->toDateString()] = (object)[
                            'phl_id' => $holiday->phl_id,
                            'phl_name' => $holiday->phl_name,
                            'phl_date' => $hDate->toDateString(),
                        ];
                    }
                }
            }
            // Prepare week-off dates
            $weekOffDates = [];
            $weekOffPolicies = PolicyWeekOff::where('pwo_b_id', $this->businessId)->get();
            foreach ($weekOffPolicies as $policy) {
                $days = $policy->getDays($policy->pwo_day_ids);
                foreach ($days as $day) {
                    $wDate = Carbon::parse($selectedDate)->copy();
                    while ($wDate->lte($selectedDate)) {
                        if ($wDate->is($day)) {
                            $weekOffDates[] = $wDate->toDateString();
                        }
                        $wDate->addDay();
                    }
                }
            }
            $weekOffDates = array_unique($weekOffDates);
            $attendanceData = CentralLogics::newGetMonthlyAttendanceReportAuxiliary(
                $employee,
                null,
                null,
                $holidayRecords,
                $weekOffDates,
                $date,
                $date
            );
            // dd($attendanceData);
            foreach ($attendanceData as $data) {
                // Handle $data as array or object
                $data = is_object($data) ? (array) $data : $data;
                // Create a pseudo AttendanceRecord object
                $record = new \stdClass();
                $record->atd_date = $data['date'] ?? null;
                $record->atd_attendance_status = $data['status_id'] ?? null;
                $record->atd_check_in_time = ($data['checkInTime'] ?? '-') !== '-' ? Carbon::parse($data['checkInTime'])->format('H:i:s') : null;
                $record->atd_check_out_time = ($data['checkOutTime'] ?? '-') !== '-' ? Carbon::parse($data['checkOutTime'])->format('H:i:s') : null;
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
                $record->fh_attendance_status = (object)[
                    'm_id' => $data['status_id'] ?? null,
                    'm_name' => $data['status'] ?? null,
                    'm_type' => $data['status_code'] ?? null,
                    'm_other' => isset($data['statusColor']) ? json_encode(['color' => $data['statusColor']]) : null,
                ];
                $record->fh_attendance_work_mode = $employee->fh_work_mode; // Keep as object
                $record->fh_policy_shift_timing = $employee->fh_shift_type; // Keep as object
                $record->fh_business = (object)['b_id' => $this->businessId];
                $record->attendance_exceptions = ($data['missedPunchCount'] ?? 0) ? [(object)[
                    'ae_in_time' => $data['checkInTime'] ?? null,
                    'ae_out_time' => $data['checkOutTime'] ?? null,
                    'ae_total_working' => ($data['workingHour'] ?? '-') !== '-' ? (float)($data['workingHour'] ?? 0) : null,
                    'ae_reason_id' => null,
                    'ae_custom_reason' => $data['attendance_remark'] ?? null,
                    'ae_status' => ($data['approvedMissedPunchCount'] ?? 0) ? 157 : null,
                    'ae_stage_completed' => ($data['approvedMissedPunchCount'] ?? 0) ? 1 : 0,
                ]] : [];
                // Apply additional filters
                $matchesShift = !$this->selectedShiftId || ($employee->fh_shift_type && $employee->fh_shift_type->pst_id == $this->selectedShiftId);
                $matchesWorkMode = !$this->selectedWorkModeId || ($record->atd_attendance_status == $this->selectedWorkModeId);
                $matchesAttendanceStatus = !$this->selectedAttendanceStatusId || ($record->atd_attendance_status == $this->selectedAttendanceStatusId);
                if ($matchesShift && $matchesWorkMode && $matchesAttendanceStatus) {
                    $records->push($record);
                }
            }
        }
        if ($records->isEmpty()) {
            // session()->flash('error', 'No attendance records found for the selected criteria.');
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'No attendance records found for the selected criteria.']);
            return;
        }
        // Debug the records structure before passing to Excel
        // \Log::debug('Records structure', $records->toArray());
        $fileName = 'DailyAttendanceReport_' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new DailyAttendanceReport($records, $this->filters), $fileName);
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
                fn($q) =>
                $q->where('m_name', 'like', "%{$this->searchAttendanceStatus}%")
            )
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
        return view('livewire.attendance-report.daily-attendance-detail-report', compact(
            'employees',
            'departments',
            'shifts',
            'designations',
            'dealers',
            'workMode',
            'attendanceStatuses',
            'branches',
            'jobStatuses',
            'grades',
            'employeeStatus'
        ));
    }
}
