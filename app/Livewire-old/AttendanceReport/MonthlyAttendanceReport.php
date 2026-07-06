<?php
namespace App\Livewire\AttendanceReport;
use Livewire\Component;
use App\Exports\Attendance\AttendanceMonthlyReport;
use App\Exports\Attendance\MonthlyDetailReport;
use App\Exports\Attendance\MonthlyInOutReport;
use App\Models\Branch;
use App\Models\PolicyHolidayList;
use App\Models\PolicyWeekOff;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use App\Models\Department;
use App\Models\PolicyShiftTiming;
use App\Models\MasterTable;
use App\Models\Dealership;
use App\Models\Designation;
use App\Models\FinancialYear;
use App\Models\Grade;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Maatwebsite\Excel\Facades\Excel;
use App\Helpers\CentralLogics;
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
        $selectedAttendanceStatusId,
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
    public $employeeStatusId = null;
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
    public function mount($slug)
    {
        $this->businessId = Auth::user()->emp_b_id;
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
        set_time_limit(0); // Remove execution time limit
        ini_set('memory_limit', '512M'); // Increase memory limit
        $fyStart = Carbon::parse($this->fyStartDate);
        $fyEnd = Carbon::parse($this->fyEndDate);
        $startMonth = $fyStart->month;
        $endMonth = $fyEnd->month;
        $startYear = $fyStart->year;
        $endYear = $fyEnd->year;
        $selectedYear = $startYear;
        if ($this->selectedMonth < $startMonth || $this->selectedMonth > 12) {
            $selectedYear = $endYear;
        } elseif ($this->selectedMonth <= $endMonth && $endMonth < $startMonth) {
            $selectedYear = $endYear;
        }
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
            // ->with(['fh_shift_type', 'fh_department', 'fh_designation', 'fh_branch','fh_dealership'])
            ->when($this->selectedEmployeeId, fn($q, $v) => $q->where('emp_id', $v))
            ->when($this->selectedDepartmentId, fn($q, $v) => $q->where('emp_d_id', $v))
            ->when($this->selectedDealerId, fn($q, $v) => $q->where('emp_dlr_id', $v))
            ->when($this->selectedDesignationId, fn($q, $v) => $q->where('emp_dg_id', $v))
            ->when($this->selectedBranchId, fn($q, $v) => $q->where('emp_br_id', $v))
            ->when($this->selectedJobStatusId, fn($q, $v) => $q->where('emp_job_status', $v))
            ->when($this->selectedGradeId, fn($q, $v) => $q->where('emp_grade_id', $v))
            ->when($this->selectedWorkModeId, fn($q, $v) => $q->where('emp_work_mode_id', $v))
            ->orderBy('emp_code', 'asc');
        
     
        if ($this->selectedDepartmentId) {
            $employeeQuery->where('emp_d_id', $this->selectedDepartmentId);
        }
        if ($this->selectedDealerId) {
            $employeeQuery->where('emp_dlr_id', $this->selectedDealerId);
        }
        if ($this->selectedDesignationId) {
            $employeeQuery->where('emp_dg_id', $this->selectedDesignationId);
        }
        if ($this->selectedBranchId) {
            $employeeQuery->where('emp_br_id', $this->selectedBranchId);
        }
        if ($this->selectedJobStatusId) {
            $employeeQuery->where('emp_job_status', $this->selectedJobStatusId);
        }
        if ($this->selectedGradeId) {
            $employeeQuery->where('emp_grade_id', $this->selectedGradeId);
        }
        $employees = $employeeQuery->get();
        if ($employees->isEmpty()) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'No employees found for the selected criteria.']);
            return;
        }
        // Prepare holiday records
        $startDate = Carbon::create($selectedYear, $this->selectedMonth, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $holidayRecords = collect();
        $holidays = PolicyHolidayList::where('phl_b_id', $this->businessId)->where('phl_day_type_id', 201)
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
        $records = collect();
        foreach ($employees as $employee) {
            $attendanceData = CentralLogics::newGetMonthlyAttendanceDetails(
                $employee,
                $this->selectedMonth,
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
                $record->atd_checkin_method_id = $data['checkingMethodId'];
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
                 // Apply shift, work mode, attendance status, dealer, and checking method filters
                $matchesShift = !$this->selectedShiftId || ($employee->fh_shift_type && $employee->fh_shift_type->pst_id == $this->selectedShiftId);
                $matchesWorkMode = !$this->selectedWorkModeId || ($data['status_id'] == $this->selectedWorkModeId);
                // $matchesAttendanceStatus = true;
                // if ($this->slug == 'early-going') {
                //     $matchesAttendanceStatus = $record->atd_is_early_exit == $this->selectedAttendanceStatusId;
                // } elseif ($this->slug == 'late-coming') {
                //     $matchesAttendanceStatus = $record->atd_is_late == $this->selectedAttendanceStatusId;
                // } elseif ($this->slug == 'absent') {
                //     $matchesAttendanceStatus = $record->atd_attendance_status == $this->selectedAttendanceStatusId;
                // } else {
                //     $matchesAttendanceStatus = $record->atd_attendance_status == $this->selectedAttendanceStatusId;
                // }
                $matchesDealer = !$this->selectedDealerId || ($employee->emp_dlr_id == $this->selectedDealerId);
                $matchCheckInMethod = !$this->selectedCheckingMethodId || ($record->atd_checkin_method_id == $this->selectedCheckingMethodId);

                if ($matchesShift && $matchesWorkMode &&  $matchesDealer && $matchCheckInMethod) {
                    $records->push($record);
                }
            }
        }
        // dd($records->toArray());
        if ($records->isEmpty()) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'No records found for the selected criteria.']);
            return;
        }
        $fileName = ucfirst($this->slug) . '_Report_' . now()->format('Y-m-d') . '.xlsx';
        if ($this->slug == 'monthly-attendance-basic') {
            return Excel::download(new AttendanceMonthlyReport($records, $this->filters, $this->slug, $this->year, $this->selectedMonth, $this->fyStartDate, $this->fyEndDate, $this->businessId), $fileName);
        } elseif ($this->slug == 'monthly-attendance-in-out') {
            return Excel::download(new MonthlyInOutReport($records, $this->filters, $this->slug, $this->year, $this->selectedMonth, $this->fyStartDate, $this->fyEndDate, $this->businessId), $fileName);
        } elseif ($this->slug == 'monthly-attendance-detail') {
            return Excel::download(new MonthlyDetailReport($records, $this->filters, $this->slug, $this->year, $this->selectedMonth, $this->fyStartDate, $this->fyEndDate, $this->businessId), $fileName);
        }
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
                fn($q) =>
                $q->where('m_name', 'like', "%{$this->searchCheckingMethod}%")
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
