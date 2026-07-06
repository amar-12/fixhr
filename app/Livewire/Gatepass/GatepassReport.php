<?php
namespace App\Livewire\Gatepass;
use App\Exports\Attendance\DailyAttendanceReport;
use App\Exports\Attendance\MonitoringReport as AttendanceMonitoringReport;
use App\Exports\Attendance\WeeklyOffSummaryReport;
use App\Exports\Gatepass\GatepassReport as GatePassReportExport;
use App\Helpers\ApprovalHelper;
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
class GatepassReport extends Component
{
    public $search = '',
        $searchDepartment = '',
        $searchShift = '',
        $searchDealer = '',
        $searchDesignation = '',
        $searchBranch = '',
        $searchGrade = '';
    public $selectedEmployeeId,
        $selectedAttendanceStatusId,
        $selectedDepartmentId,
        $selectedShiftId,
        $selectedDealerId,
        $selectedDesignationId,
        $selectedBranchId,
        $selectedGradeId;
    public $businessId = '';
    //  static fields 
    public $selectedDate;
    public $employeeStatusFilter = 'all';
    public $selectedFromDate;
    public $selectedToDate;
    public $showFilterPanel = false;
    public function mount()
    {
        $this->businessId = Auth::user()->emp_b_id;
        $this->selectedDate = Carbon::now()->toDateString(); // Format: YYYY-MM-DD
        $this->selectedFromDate = Carbon::now()->toDateString(); // Format: YYYY-MM-DD
        $this->selectedToDate = Carbon::now()->toDateString(); // Format: YYYY-MM-DD
    }
    public $filters = [
        'department' => false,
        'shift' => false,
        'designation' => false,
        'dealership' => false,
        'attendanceStatus' => false,
        'branch' => false,
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
        $this->selectedDealerId          = null;
        $this->selectedBranchId          = null;
        $this->selectedGradeId           = null;
        $this->selectedAttendanceStatusId = null;
        $this->search                = '';
        $this->searchDepartment      = '';
        $this->searchShift           = '';
        $this->searchDesignation     = '';
        $this->searchDealer          = '';
        $this->searchBranch          = '';
        $this->searchGrade           = '';

        // Reset filters to defaults — adjust as needed
        $this->filters = [
            'department' => false,
            'shift' => false,
            'designation' => false,
            'dealership' => false,
            'attendanceStatus' => false,
            'branch' => false,
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
                'dealership' => [
                    $this->selectedDealerId = null,
                    $this->searchDealer = '',
                ],
                'branch' => [
                    $this->selectedBranchId = null,
                    $this->searchBranch = '',
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
            'searchDealer' => 'selectedDealerId',
            'searchBranch' => 'selectedBranchId',
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
            'selectedDesignationId' => 'nullable|exists:designations,dg_id',
            'selectedBranchId' => 'nullable|exists:branches,br_id',
            'selectedGradeId' => 'nullable|exists:master_table,m_id',
        ], [
            'selectedDate.required' => 'The date field is required.',
            'selectedDate.date' => 'The date must be a valid date.',
            'selectedEmployeeId.exists' => 'The selected employee does not exist.',
            'selectedDepartmentId.exists' => 'The selected department does not exist.',
            'selectedShiftId.exists' => 'The selected shift does not exist.',
            'selectedDealerId.exists' => 'The selected dealer does not exist.',
            'selectedDesignationId.exists' => 'The selected designation does not exist.',
            'selectedBranchId.exists' => 'The selected branch does not exist.',
            'selectedGradeId.exists' => 'The selected grade does not exist.',
        ]);
        $query = GatePass::with([
            'fh_employees_details:emp_id,emp_code,emp_full_name,emp_d_id,emp_dg_id,emp_dlr_id,emp_br_id,emp_role_id,emp_type_id,emp_shift_type_id',
            'fh_gatepass_confirmation'
        ])
            ->where('gtp_b_id', $this->businessId)
            ->whereBetween('gtp_date', [$this->selectedFromDate, $this->selectedToDate]);
        if ($this->selectedDepartmentId) {
            $query->whereHas('fh_employees_details', function ($q) {
                $q->where('emp_d_id', $this->selectedDepartmentId);
            });
        }
        if ($this->selectedEmployeeId) {
            $query->whereHas('fh_employees_details', function ($q) {
                $q->where('emp_id', $this->selectedEmployeeId);
            });
        }
        if ($this->selectedShiftId) {
            $query->whereHas('fh_employees_details', function ($q) {
                $q->where('emp_shift_type_id', $this->selectedShiftId);
            });
        }
        if ($this->selectedDealerId) {
            $query->whereHas('fh_employees_details', function ($q) {
                $q->where('emp_dlr_id', $this->selectedDealerId);
            });
        }
        if ($this->selectedDesignationId) {
            $query->whereHas('fh_employees_details', function ($q) {
                $q->where('emp_dg_id', $this->selectedDesignationId);
            });
        }
        if ($this->selectedBranchId) {
            $query->whereHas('fh_employee', function ($q) {
                $q->where('emp_br_id', $this->selectedBranchId);
            });
        }
        if ($this->selectedGradeId) {
            $query->whereHas('fh_employees_details', function ($q) {
                $q->where('emp_grade_id', $this->selectedGradeId);
            });
        }
        // Execute the query and get the record
        $records = $query->get();
        // dd($records->toArray());
        if ($records->isEmpty()) {
             $this->dispatch('show-alert', ['type' => 'error', 'message' => 'No records found for the selected criteria.']);
            return;
        }
        $fileName =   'GatePassReport_' . now()->format('Y-m-d') . '.xlsx';
        $fromDate = Carbon::parse($this->selectedFromDate);
        $toDate = Carbon::parse($this->selectedToDate);
        $date = $fromDate->format('Y-m-d') . ' to ' . $toDate->format('Y-m-d');
        return Excel::download(new GatePassReportExport($records, $this->filters, $fileName, $date), $fileName);
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
        $grades = Grade::where('g_b_id', $this->businessId)
            ->when(
                strlen($this->searchGrade) >= 1 && !$this->selectedGradeId,
                fn($q) =>
                $q->where('g_name', 'like', "%{$this->searchGrade}%")
            )
            ->limit(100)
            ->get();
        return view('livewire.gatepass.gatepass-report', compact(
            'employees',
            'departments',
            'shifts',
            'designations',
            'dealers',
            'branches',
            'grades'
        ));
    }
}
