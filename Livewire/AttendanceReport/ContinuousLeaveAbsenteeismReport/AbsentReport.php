<?php
namespace App\Livewire\AttendanceReport\ContinuousLeaveAbsenteeismReport;

use App\Exports\Attendance\ContinuousAbsentReport;
use App\Exports\Attendance\WeeklyOffSummaryReport;
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
    public $employeeStatusFilter = 'all';
    public $selectedFromDate;
    public $selectedToDate;
    public $slug;

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
    'selectedToDate'   => 'required|date|after:selectedFromDate',
], [
    'selectedFromDate.required' => 'Please select From Date.',
    'selectedToDate.required'   => 'Please select To Date.',
    'selectedToDate.after'      => 'To Date must be at least 1 day after From Date.',
]);



    if ($this->slug == 'continuous-absent') {
        $query = AttendanceRecord::with([
            'fh_employees_details',
            'fh_attendance_checkin_type',
            'fh_policy_shift_timing',
            'fh_attendance_status',
            'fh_business',
            'attendance_exceptions'
        ])
            ->where('atd_b_id', $this->businessId)
            ->whereBetween('atd_date', [$this->selectedFromDate, $this->selectedToDate])
            ->where('atd_attendance_status', 203)
            ->when($this->selectedEmployeeId, fn($q) => $q->where('atd_emp_id', $this->selectedEmployeeId));

        $rawRecords = $query->orderBy('atd_emp_id')->orderBy('atd_date')->get();

        $filteredRecords = collect();
        $employeeRecords = $rawRecords->groupBy('atd_emp_id');

        foreach ($employeeRecords as $empId => $empRecords) {
            $dates = $empRecords->pluck('atd_date')->map(function ($date) {
                return Carbon::parse($date)->startOfDay();
            })->sort()->values();

            if ($dates->count() < 2) {
                continue;
            }

            $consecutiveCount = 1;
            $consecutiveRecords = collect([$empRecords[0]]);

            for ($i = 1; $i < $dates->count(); $i++) {
                $diffInDays = abs($dates[$i]->diffInDays($dates[$i - 1]));

                if ($diffInDays == 1) {
                    $consecutiveCount++;
                    $consecutiveRecords->push($empRecords[$i]);
                } else {
                    if ($consecutiveCount >= 2) {
                        $filteredRecords = $filteredRecords->merge($consecutiveRecords);
                    }
                    $consecutiveCount = 1;
                    $consecutiveRecords = collect([$empRecords[$i]]);
                }
            }

            if ($consecutiveCount >= 2) {
                $filteredRecords = $filteredRecords->merge($consecutiveRecords);
            }
        }

        $records = $filteredRecords->unique('atd_id');

        if ($records->isEmpty()) {
            session()->flash('error', 'No records found for the selected criteria.');
            return;
        }
    } else {
        $query = AttendanceRecord::with([
            'fh_employees_details',
            'fh_attendance_checkin_type',
            'fh_policy_shift_timing',
            'fh_attendance_status',
            'fh_business',
            'attendance_exceptions'
        ])
            ->where('atd_b_id', $this->businessId)
            ->whereBetween('atd_date', [$this->selectedFromDate, $this->selectedToDate]);
        $records = $query->get();
    }

    if ($records->isEmpty()) {
        session()->flash('error', 'No records found for the selected criteria.');
        return;
    }

    $fileName = ucfirst($this->slug) . '_Report_' . now()->format('Y-m-d') . '.xlsx';
    $date = Carbon::parse($this->selectedFromDate)->format('d-M-y') . ' to ' . Carbon::parse($this->selectedToDate)->format('d-M-y');

    return Excel::download(new ContinuousAbsentReport($records, $this->filters, $this->slug, $date), $fileName);
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
            'grades'
        ));
    }
}