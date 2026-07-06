<?php
namespace App\Livewire\AttendanceReport;
use App\Exports\Attendance\AttendanceYearlyReport;
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
use App\Models\FinancialYear;
use App\Models\Grade;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
class YearlySummaryReport extends Component
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
    public $selectedYear;
    public $slug;
    public $financialYears;
    public $fyStartDate = '';
    public $fyEndDate = '';
    public $year;
    // public $filters = [
    //     'department' => false,
    //     'shift' => false,
    //     'designation' => false,
    //     'workMode' => false,
    //     'checkingMethod' => false,
    //     'dealership' => false,
    //     'attendanceStatus' => false,
    //     'branch' => false,
    //     'jobStatus' => false,
    //     'grade' => false,
    // ];
    public function mount()
    {
        // dd('mount');
        $this->businessId = Auth::user()->emp_b_id;
        $this->selectedDate = Carbon::now()->toDateString();
        $this->slug = 'yearly-summary';
        $this->financialYears = FinancialYear::where('fy_b_id', $this->businessId)->get();
        $currentYear = $this->financialYears->firstWhere('fy_is_current', 1);
        if ($currentYear) {
            $this->selectedYear = $currentYear->fy_id;
        }
        if ($this->selectedYear) {
            // dd('selectedYear', $this->selectedYear);
            $this->fyStartDate = $this->financialYears->where('fy_id', $this->selectedYear)->first()->fy_start_date;
            $this->fyEndDate = $this->financialYears->where('fy_id', $this->selectedYear)->first()->fy_end_date;
        }
    }
    public function selectYear($id)
    {
        $this->selectedYear = $id;
        $this->year = $this->financialYears->where('fy_id', $id)->first()?->fy_year;
        $this->fyStartDate = $this->financialYears->where('fy_id', $id)->first()?->fy_start_date;
        $this->fyEndDate = $this->financialYears->where('fy_id', $id)->first()?->fy_end_date;
    }
    // public function toggleFilter($key, $state)
    // {
    //     if (!array_key_exists($key, $this->filters)) return;
    //     $this->filters[$key] = filter_var($state, FILTER_VALIDATE_BOOLEAN);
    //     if (!$this->filters[$key]) {
    //         match ($key) {
    //             'department' => [
    //                 $this->selectedDepartmentId = null,
    //                 $this->searchDepartment = '',
    //             ],
    //             'shift' => [
    //                 $this->selectedShiftId = null,
    //                 $this->searchShift = '',
    //             ],
    //             'designation' => [
    //                 $this->selectedDesignationId = null,
    //                 $this->searchDesignation = '',
    //             ],
    //             'workMode' => [
    //                 $this->selectedWorkModeId = null,
    //                 $this->searchWorkMode = '',
    //             ],
    //             'checkingMethod' => [
    //                 $this->selectedCheckingMethodId = null,
    //                 $this->searchCheckingMethod = '',
    //             ],
    //             'dealership' => [
    //                 $this->selectedDealerId = null,
    //                 $this->searchDealer = '',
    //             ],
    //             'branch' => [
    //                 $this->selectedBranchId = null,
    //                 $this->searchBranch = '',
    //             ],
    //             'jobStatus' => [
    //                 $this->selectedJobStatusId = null,
    //                 $this->searchJobStatus = '',
    //             ],
    //             'grade' => [
    //                 $this->selectedGradeId = null,
    //                 $this->searchGrade = '',
    //             ],
    //             default => null,
    //         };
    //     }
    // }
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
        //   dd( 'selected id',$this->selectedYear, 'fyStartDate', $this->fyStartDate, 'fyEndDate', $this->fyEndDate,);
        $this->validate([
            'selectedYear' => 'required|exists:financial_years,fy_id',
            'selectedEmployeeId' => 'required|exists:employees,emp_id',
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
            'selectedYear.required' => 'The financial year field is required.',
            'selectedYear.exists' => 'The selected financial year does not exist.',
            'selectedEmployeeId.required' => 'The employee field is required.',
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
        $query = AttendanceRecord::with([
            'fh_employees_details',
            'fh_attendance_checkin_type',
            'fh_policy_shift_timing',
            'fh_attendance_status',
            'fh_business',
            'attendance_exceptions',
        ])
            ->where('atd_b_id', $this->businessId)
            ->whereBetween('atd_date', [$this->fyStartDate, $this->fyEndDate]);
        if ($this->selectedDepartmentId) {
            $query->whereHas('fh_employees_details', function ($q) {
                $q->where('emp_d_id', $this->selectedDepartmentId);
            });
        }
        if ($this->selectedEmployeeId) {
            $query->where('atd_emp_id', $this->selectedEmployeeId);
        }
        if ($this->selectedShiftId) {
            $query->where('atd_pst_id', $this->selectedShiftId);
        }
        if ($this->selectedDealerId) {
            $query->whereHas('fh_employees_details', function ($q) {
                $q->where('emp_dlr_id', $this->selectedDealerId);
            });
        }
        if ($this->selectedWorkModeId) {
            $query->where('atd_work_mode_type_id', $this->selectedWorkModeId);
        }
        if ($this->selectedAttendanceStatusId) {
            $query->where('atd_attendance_status', $this->selectedAttendanceStatusId);
        }
        if ($this->selectedDesignationId) {
            $query->whereHas('fh_employees_details', function ($q) {
                $q->where('emp_dg_id', $this->selectedDesignationId);
            });
        }
        if ($this->selectedCheckingMethodId) {
            $query->where('atd_checkin_method_id', $this->selectedCheckingMethodId);
        }
        if ($this->selectedBranchId) {
            $query->whereHas('fh_employee', function ($q) {
                $q->where('emp_br_id', $this->selectedBranchId);
            });
        }
        if ($this->selectedJobStatusId) {
            $query->join('employees', 'employees.emp_id', '=', 'attendance_records.atd_emp_id')
                ->where('employees.emp_job_status', $this->selectedJobStatusId);
        }
        if ($this->selectedGradeId) {
            $query->whereHas('fh_employees_details', function ($q) {
                $q->where('emp_grade_id', $this->selectedGradeId);
            });
        }
        $records = $query->get();
        
        if ($records->isEmpty()) {
            session()->flash('error', 'No records found for the selected criteria.');
            return;
        }
        // Fetch the employee's joining date
        $employee = Employee::where('emp_id', $this->selectedEmployeeId)->first();
        // dd('employee', $employee);
        $employeeJoiningDate = $employee ? $employee->emp_date_of_joining : null;
        
      
        $fileName = ucfirst($this->slug) . '_Report_' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new AttendanceYearlyReport($records, $this->slug, $this->year, $this->fyStartDate, $this->fyEndDate, $employeeJoiningDate), $fileName);
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
        return view('livewire.attendance-report.yearly-summary-report', compact(
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
