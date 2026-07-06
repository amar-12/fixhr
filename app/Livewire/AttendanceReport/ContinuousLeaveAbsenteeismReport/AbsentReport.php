<?php
namespace App\Livewire\AttendanceReport\ContinuousLeaveAbsenteeismReport;
use App\Exports\Attendance\ContinuousAbsentReport;
use App\Exports\Attendance\WeeklyOffSummaryReport;
use App\Helpers\CentralLogics;
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
use App\Models\Grade;
use App\Models\WorkLocation;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\PolicyHolidayList;
use App\Models\PolicyWeekOff;
use Maatwebsite\Excel\Facades\Excel;
class AbsentReport extends Component
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
    public $employeeStatusId = 71;
    public $selectedFromDate;
    public $selectedToDate;
    public $slug;
    public $showFilterPanel = false;
    public function mount($slug)
    {
        $this->businessId = Auth::user()->emp_b_id;
        $this->selectedDate = Carbon::now()->toDateString();
        $this->slug = $slug;
        $this->selectedFromDate = Carbon::now()->toDateString();
        $this->selectedToDate = Carbon::parse($this->selectedFromDate)->addDay()->toDateString();
        $slugMap = [
            'continuous-absent' => 203,
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
        $this->validate([
            'selectedFromDate' => 'required|date',
            'selectedToDate'   => 'required|date|after:selectedFromDate',
        ], [
            'selectedFromDate.required' => 'Please select From Date.',
            'selectedToDate.required'   => 'Please select To Date.',
            'selectedToDate.after'      => 'To Date must be at least 1 day after From Date.',
        ]);
        $from = Carbon::parse($this->selectedFromDate)->startOfDay();
        $to   = Carbon::parse($this->selectedToDate)->endOfDay();
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
        ])
            ->where('emp_b_id', $this->businessId)
            ->where('emp_role_id', '<>', 1)
            ->when($this->employeeStatusId,   fn($q, $v) => $q->where('emp_status', $v))
            ->when($this->selectedEmployeeId,   fn($q, $v) => $q->where('emp_id', $v))
            ->when($this->selectedDepartmentId, fn($q, $v) => $q->where('emp_d_id', $v))
            ->when($this->selectedDealerId,     fn($q, $v) => $q->where('emp_dlr_id', $v))
            ->when($this->selectedDesignationId, fn($q, $v) => $q->where('emp_dg_id', $v))
            ->when($this->selectedBranchId,     fn($q, $v) => $q->where('emp_br_id', $v))
            ->when($this->selectedJobStatusId,  fn($q, $v) => $q->where('emp_job_status', $v))
            ->when($this->selectedGradeId,      fn($q, $v) => $q->where('emp_grade_id', $v))
            ->when($this->selectedWorkModeId,   fn($q, $v) => $q->where('emp_work_mode_id', $v))
            ->orderBy('emp_code', 'asc');
        $employees = $employeeQuery->get();
        if ($employees->isEmpty()) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'No employees found for the selected criteria']);
            return;
        }
        $allPseudoRecords = collect();
        foreach ($employees as $employee) {
            $startDate = Carbon::parse($this->selectedFromDate);
            $endDate = Carbon::parse($this->selectedToDate);
            $holidayRecords = collect();
            $holidays = PolicyHolidayList::where('phl_b_id', $this->businessId)->where('phl_day_type_id', 201)
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
            // Prepare week-off dates
            $weekOffDates = [];
            $weekOffPolicies = PolicyWeekOff::where('pwo_b_id', $this->businessId)->get();
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
            $attendanceData = CentralLogics::newGetMonthlyAttendanceReportAuxiliary(
                $employee,
                null,
                null,
                $holidayRecords,
                $weekOffDates,
                $this->selectedFromDate,
                $this->selectedToDate
            );
            foreach ($attendanceData as $day) {
                // ---- skip days that are NOT in the selected range ----
                $dayDate = Carbon::parse($day['date'])->startOfDay();
                if ($dayDate->lt($from) || $dayDate->gt($to)) {
                    continue;
                }
                // ---- create the same pseudo AttendanceRecord object ----
                $record = new \stdClass();
                $record->atd_date               = $day['date'];
                $record->atd_attendance_status  = $day['status_id'];
                $record->atd_check_in_time      = $day['checkInTime'] !== '-' ? Carbon::parse($day['checkInTime'])->format('H:i:s') : null;
                $record->atd_check_out_time     = $day['checkOutTime'] !== '-' ? Carbon::parse($day['checkOutTime'])->format('H:i:s') : null;
                $record->atd_total_worked_hours = $day['workingHour'] !== '-' ? (float)$day['workingHour'] : null;
                $record->atd_is_late            = $day['lateCount'] ? 1 : 0;
                $record->atd_late_duration      = $day['late'];
                $record->atd_is_early_exit      = $day['earlyExitCount'] ? 1 : 0;
                $record->atd_early_exit_duration = $day['earlyExit'];
                $record->atd_is_overtime        = $day['overtimeCount'] ? 1 : 0;
                $record->atd_overtime_hours      = $day['OT'];
                $record->atd_remark             = $day['attendance_remark'];
                $record->atd_punchin_location   = $day['checkInLocation'];
                $record->atd_punchout_location  = $day['checkOutLocation'];
                $record->atd_punchin_photo      = json_encode($day['checkInPhoto']);
                $record->atd_punchout_photo     = json_encode($day['checkOutPhoto']);
                $record->atd_segments           = json_encode($day['atd_segments']);
                $record->fh_employees_details   = $employee;
                $record->atd_checkin_method_id  = $day['checkingMethodId'];
                $record->fh_attendance_status = (object)[
                    'm_id'    => $day['status_id'],
                    'm_name'  => $day['status'],
                    'm_type'  => $day['status_code'],
                    'm_other' => json_encode(['color' => $day['statusColor']]),
                ];
                $record->fh_attendance_work_mode = $employee->fh_work_mode;
                $record->fh_policy_shift_timing   = $employee->fh_shift_type;
                $record->fh_business             = (object)['b_id' => $this->businessId];
                $record->attendance_exceptions = $day['missedPunchCount']
                    ? [(object)[
                        'ae_in_time'          => $day['checkInTime'],
                        'ae_out_time'         => $day['checkOutTime'],
                        'ae_total_working'    => $day['workingHour'],
                        'ae_reason_id'        => null,
                        'ae_custom_reason'    => $day['attendance_remark'],
                        'ae_status'           => $day['approvedMissedPunchCount'] ? 157 : null,
                        'ae_stage_completed'  => $day['approvedMissedPunchCount'] ? 1 : 0,
                    ]]
                    : [];
                // ---- apply the remaining filters (shift, work-mode, checking-method, dealer…) ----
                $matchesShift   = !$this->selectedShiftId   || ($employee->fh_shift_type && $employee->fh_shift_type->pst_id == $this->selectedShiftId);
                $matchesWorkMode = !$this->selectedWorkModeId || ($day['status_id'] == $this->selectedWorkModeId);
                $matchesDealer  = !$this->selectedDealerId  || ($employee->emp_dlr_id == $this->selectedDealerId);
                $matchesCheckIn = !$this->selectedCheckingMethodId || ($record->atd_checkin_method_id == $this->selectedCheckingMethodId);
                if ($matchesShift && $matchesWorkMode && $matchesDealer && $matchesCheckIn) {
                    $allPseudoRecords->push($record);
                }
            }
        }
        // ------------------------------------------------------------
        // 5. CONTINUOUS-ABSENT LOGIC (only for the “continuous-absent” slug)
        // ------------------------------------------------------------
        if ($this->slug === 'continuous-absent') {
            $absentStatusId = 203; // Absent
            // Group by employee
            $byEmployee = $allPseudoRecords->groupBy(fn($r) => $r->fh_employees_details->emp_id);
            $filtered = collect();
            foreach ($byEmployee as $empId => $empRecords) {
                // Sort by date
                $sorted = $empRecords->sortBy('atd_date');
                $consecutive = collect();
                $count       = 0;
                foreach ($sorted as $idx => $rec) {
                    $isAbsent = $rec->atd_attendance_status == $absentStatusId;
                    if ($isAbsent) {
                        $count++;
                        $consecutive->push($rec);
                    }
                    // If we are NOT at the first record, check if the previous day was also absent
                    if ($idx > 0 && $isAbsent) {
                        $prevDate = Carbon::parse($sorted[$idx - 1]->atd_date);
                        $currDate = Carbon::parse($rec->atd_date);
                        $diffDays = $currDate->diffInDays($prevDate);
                        if ($diffDays !== 1) {
                            // Gap → reset
                            if ($count >= 2) {
                                $filtered = $filtered->merge($consecutive);
                            }
                            $consecutive = collect();
                            $count       = 0;
                        }
                    } elseif (!$isAbsent && $count >= 2) {
                        // End of a run
                        $filtered = $filtered->merge($consecutive);
                        $consecutive = collect();
                        $count       = 0;
                    }
                }
                // Flush the last run if it qualifies
                if ($count >= 2) {
                    $filtered = $filtered->merge($consecutive);
                }
            }
            $records = $filtered->unique(fn($r) => $r->fh_employees_details->emp_id . '-' . $r->atd_date);
        } else {
            // For any other slug just return everything that passed the filters
            $records = $allPseudoRecords;
        }
        // ------------------------------------------------------------
        // 6. FINAL GUARD & EXPORT
        // ------------------------------------------------------------
        if ($records->isEmpty()) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'No records found for the selected criteria.']);
            return;
        }
        // dd($records->toArray());
        $fileName = ucfirst($this->slug) . '_Report_' . now()->format('Y-m-d') . '.xlsx';
        $dateRange = $from->format('d-M-y') . ' to ' . $to->format('d-M-y');
        return Excel::download(
            new ContinuousAbsentReport($records, $this->filters, $this->slug, $dateRange),
            $fileName
        );
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
        return view('livewire.attendance-report.continuous-leave-absenteeism-report.absent-report', compact(
            'employees',
            'departments',
            'shifts',
            'designations',
            'dealers',
            'workMode',
            'checkingMethods',
            'branches',
            'jobStatuses',
            'grades',
            'employeeStatus',
        ));
    }
}
