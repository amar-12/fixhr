<?php
namespace App\Livewire\EmployeeReport;
use App\Models\ApprovalModule;
use App\Models\Branch;
use App\Models\Business;
use App\Models\Dealership;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeApprovalMapping;
use App\Models\Grade;
use App\Models\MasterTable;
use App\Models\PolicyShiftTiming;
use App\Models\ProcessApprover;
use App\Models\Role;
use Carbon\Carbon;
use App\Exports\Employee\ApprovalReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
class EmployeeApprovalMatrixReport extends Component
{
    // Search inputs
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
        $searchRole = '',
        $searchReportingManager = '',
        $searchGrade = '';
    // Selected values
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
        $selectedGradeId,
        $selectedRoleId,
        $selectedReportingManagerId,
        $selectedPaymentMethod,
        $selectedEmployeeTypeId;
    public $businessId = '';
    public $selectedDate;
    public $employeeStatusId = null;
    public $preventCollapse = false;
    public $businessName = '';
    public $hierchyType;
    public $employeeType;
    public $sortBy = 'emp_code';
    // New: Module selection for export
    public $moduleSelection = [];
    // To store module list for checkbox display
    public $availableModules = [];
    public $showFilterPanel = false;
    public function mount()
    {
        $this->businessId = Auth::user()->emp_b_id;
        $this->businessName = Business::where('b_id', $this->businessId)->first()->b_name ?? '';
        $this->selectedDate = Carbon::now()->toDateString();
        $this->hierchyType = collect();
        $this->employeeType = collect();
        // Initialize module selection – all modules selected by default (or empty if you prefer)
        $this->moduleSelection = [];
    }

    // Filters visibility
    public $filters = [
        'department' => true,
        'shift' => true,
        'designation' => true,
        'workMode' => false,
        'checkingMethod' => false,
        'dealership' => false,
        'attendanceStatus' => false,
        'branch' => false,
        'jobStatus' => false,
        'grade' => false,
        'paymentMethod' => false,
        'role' => false,
        'reportingManager' => false,
        'employeeType' => true,
        'employeeStatusFilter' => false,
    ];
    // Details sections to include in report
    public $detailsFilter = [
        'separationDetails' => false,
        'pfEsicDetails' => false,
        'organizationDetails' => false,
        'bankDetails' => false,
        'attendanceDetails' => false,
        'identityDetails' => false,
        'personalDetails' => false,
        'contactDetails' => false,
        'aboutDetails' => false,
        'joiningDetails' => false,
        'employeeSalaryDetails' => false,
        'salaryMaster' => false,
    ];
    public $detailsFields = [
        'aboutDetails' => 'About',
        'contactDetails' => 'Contact Information',
        'personalDetails' => 'Personal Information',
        'identityDetails' => 'Identity',
        'pfEsicDetails' => 'PF & ESIC',
        'joiningDetails' => 'Joining',
        'separationDetails' => 'Separation',
        'organizationDetails' => 'Organization',
        'bankDetails' => 'Bank',
        'attendanceDetails' => 'Attendance',
        'employeeSalaryDetails' => 'Employee Salary',
        'salaryMaster' => 'Salary Master',
    ];
    public $filterFields = [
        'role' => 'Role',
        'employeeType' => 'Employee Type',
        'department' => 'Department',
        'shift' => 'Shift',
        'designation' => 'Designation',
        'workMode' => 'Work Mode',
        'dealership' => 'Dealership',
        'branch' => 'Branch',
        'jobStatus' => 'Job Status',
        'grade' => 'Grade',
        'reportingManager' => 'Reporting Manager',
    ];
    // ================================================================
    // AUTO-ENABLE: Filter ON → Details ON
    // ================================================================
    /**
     * Map each filter key to the detail section(s) it belongs to.
     */
    private function filterToDetailSection(): array
    {
        return [
            'paymentMethod'    => ['bankDetails'],
            'branch'           => ['organizationDetails'],
            'grade'            => ['organizationDetails'],
            'jobStatus'        => ['joiningDetails'],
            'department'       => ['aboutDetails'],
            'designation'      => ['aboutDetails'],
            'shift'            => ['aboutDetails'],
            'workMode'         => ['attendanceDetails'],
            'role'             => ['organizationDetails'],
            'reportingManager' => ['organizationDetails'],
            'dealership'       => ['organizationDetails'],
            'attendanceStatus' => ['attendanceDetails'],
            'checkingMethod'   => ['attendanceDetails'],
            'employeeType'     => ['aboutDetails'],
        ];
    }

       public function resetFilters()
    {
        // Reset all selected values
        $this->selectedEmployeeId = null;
        $this->selectedAttendanceStatusId = null;
        $this->selectedDepartmentId = null;
        $this->selectedShiftId = null;
        $this->selectedDealerId = null;
        $this->selectedWorkModeId = null;
        $this->selectedDesignationId = null;
        $this->selectedCheckingMethodId = null;
        $this->selectedBranchId = null;
        $this->selectedJobStatusId = null;
        $this->selectedGradeId = null;
        $this->selectedRoleId = null;
        $this->selectedReportingManagerId = null;
        $this->selectedPaymentMethod = null;
        $this->selectedEmployeeTypeId = null;
        $this->employeeStatusId = null;
        // Reset search fields
        $this->search = '';
        $this->searchAttendanceStatus = '';
        $this->searchDepartment = '';
        $this->searchShift = '';
        $this->searchDealer = '';
        $this->searchWorkMode = '';
        $this->searchDesignation = '';
        $this->searchCheckingMethod = '';
        $this->searchBranch = '';
        $this->searchJobStatus = '';
        $this->searchRole = '';
        $this->searchReportingManager = '';
        $this->searchGrade = '';
        // Reset filters to default state
        $this->filters = [
            'department' => true,
            'shift' => true,
            'designation' => true,
            'workMode' => false,
            'checkingMethod' => false,
            'dealership' => false,
            'attendanceStatus' => false,
            'branch' => false,
            'jobStatus' => false,
            'grade' => false,
            'paymentMethod' => false,
            'role' => false,
            'reportingManager' => false,
            'employeeType' => true,
            'employeeStatusFilter' => false,
        ];
        // Optionally reset details filters
        $this->detailsFilter = array_fill_keys(array_keys($this->detailsFilter), false);
        // Keep filter panel open if it was open
        // $this->showFilterPanel remains unchanged
    }
    public function toggleFilterPanel()
    {
        $this->showFilterPanel = !$this->showFilterPanel;
    }
    /**
     * Auto-enable detail sections when a filter is turned ON.
     * Does NOT reset or override manual OFF.
     */
    private function syncDetailsWithFilters(): void
    {
        $mapping = $this->filterToDetailSection();
        foreach ($mapping as $filterKey => $sections) {
            if ($this->filters[$filterKey]) {
                foreach ((array) $sections as $section) {
                    $this->detailsFilter[$section] = true;
                }
            }
        }
    }
    // ================================================================
    // FILTER & SELECT METHODS
    // ================================================================
    public function updatePreventCollapse()
    {
        $this->preventCollapse = collect($this->filters)->contains(true);
    }
    public function toggleFilter($key, $state)
    {
        if (!array_key_exists($key, $this->filters)) return;
        $wasOn = $this->filters[$key];
        $this->filters[$key] = filter_var($state, FILTER_VALIDATE_BOOLEAN);
        // If filter was OFF and now ON → auto-enable details
        if (!$wasOn && $this->filters[$key]) {
            $this->syncDetailsWithFilters();
        }
        // If turned OFF → clear selected value
        if (!$this->filters[$key]) {
            match ($key) {
                'department' => [$this->selectedDepartmentId = null, $this->searchDepartment = ''],
                'shift' => [$this->selectedShiftId = null, $this->searchShift = ''],
                'designation' => [$this->selectedDesignationId = null, $this->searchDesignation = ''],
                'workMode' => [$this->selectedWorkModeId = null, $this->searchWorkMode = ''],
                'checkingMethod' => [$this->selectedCheckingMethodId = null, $this->searchCheckingMethod = ''],
                'dealership' => [$this->selectedDealerId = null, $this->searchDealer = ''],
                'attendanceStatus' => [$this->selectedAttendanceStatusId = null, $this->searchAttendanceStatus = ''],
                'branch' => [$this->selectedBranchId = null, $this->searchBranch = ''],
                'jobStatus' => [$this->selectedJobStatusId = null, $this->searchJobStatus = ''],
                'grade' => [$this->selectedGradeId = null, $this->searchGrade = ''],
                'paymentMethod' => [$this->selectedPaymentMethod = null],
                'role' => [$this->selectedRoleId = null, $this->searchRole = ''],
                'reportingManager' => [$this->selectedReportingManagerId = null, $this->searchReportingManager = ''],
                'employeeType' => [$this->selectedEmployeeTypeId = null],
                default => null,
            };
        }
        $this->updatePreventCollapse();
    }
    // Select Methods — NO syncDetailsWithFilters() here
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
    public function selectRole($id, $name)
    {
        $this->selectedRoleId = $id;
        $this->searchRole = $name;
    }
    public function selectPaymentMethod($name)
    {
        $this->selectedPaymentMethod = $name;
    }
    public function selectEmployeeType($id)
    {
        $this->selectedEmployeeTypeId = $id;
    }
    public function selectReportingManager($id, $name)
    {
        $this->selectedReportingManagerId = $id;
        $this->searchReportingManager = $name;
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
            'searchAttendanceStatus' => 'selectedAttendanceStatusId',
            'searchBranch' => 'selectedBranchId',
            'searchJobStatus' => 'selectedJobStatusId',
            'searchGrade' => 'selectedGradeId',
            'searchRole' => 'selectedRoleId',
            'searchReportingManager' => 'selectedReportingManagerId',
        ];
        if (array_key_exists($property, $mapping)) {
            $this->{$mapping[$property]} = null;
        }
    }
    public function generateReport()
    {
        $user = Auth::user();
        // Load modules (unchanged)
        $modules = MasterTable::with([
            'fh_approval_modules' => fn($q) => $q->where('am_b_id', $user->emp_b_id),
            'fh_approval_modules.fh_process_approvers.fh_employee',
            'fh_approval_modules.fh_process_approvers.fh_approver_status',
            'fh_approval_modules.fh_business.fh_employees',
            'fh_approval_modules2' => fn($q) => $q->where('eam_b_id', $user->emp_b_id),
            'fh_approval_modules2.fh_employee',
            'fh_approval_modules2.approvalStatuses.manager',
            'fh_approval_modules2.approvalStatuses.approvalStatus',
        ])
            ->where('m_group', 'MODULE')
            ->get();
        $hierarchyModules = $modules
            ->filter(fn($m) => $m->fh_approval_modules->isNotEmpty() && $m->fh_approval_modules2->isEmpty())
            ->flatMap(fn($m) => $m->fh_approval_modules)
            ->values();
        $employeeModules = $modules
            ->filter(fn($m) => $m->fh_approval_modules2->isNotEmpty() && $m->fh_approval_modules->isEmpty())
            ->map(function ($module) {
                $records = $module->fh_approval_modules2;
                $employees = $records
                    ->groupBy('eam_emp_id')
                    ->map(function ($rows) {
                        $employee = $rows->first()->fh_employee;
                        $managers = $rows
                            ->flatMap(fn($r) => $r->approvalStatuses)
                            ->sortBy('level')
                            ->map(fn($s) => [
                                'name' => $s->manager->emp_full_name ?? 'N/A',
                                'status' => $s->approvalStatus->m_name ?? '-',
                            ])
                            ->values();
                        return [
                            'employee' => $employee,
                            'managers' => $managers,
                        ];
                    })
                    ->values();
                $module->employeeRows = $employees;
                return $module;
            })
            ->values();
        // Build process matrix (unchanged)
        $processMatrix = [];
        // === Hierarchy Wise Modules (HW) ===
        foreach ($hierarchyModules as $module) {
            $moduleKey = 'hw_' . $module->am_id;
            // Include if: no selection (default = all) OR explicitly selected
            if (empty($this->moduleSelection) || in_array($moduleKey, $this->moduleSelection)) {
                $processMatrix[$moduleKey] = [
                    'title'     => $module->am_name . ' (Hierarchy Wise)',
                    'approvers' => max(1, $module->fh_process_approvers->count()),
                ];
            }
        }
        // === Employee Wise Modules (EW) ===
        foreach ($employeeModules as $module) {
            $moduleKey = 'ew_' . $module->m_id;
            // Include if: no selection (default = all) OR explicitly selected
            if (empty($this->moduleSelection) || in_array($moduleKey, $this->moduleSelection)) {
                $maxApprovers = $module->employeeRows
                    ->map(fn($row) => $row['managers']->count())
                    ->max() ?? 0;
                $processMatrix[$moduleKey] = [
                    'title'     => $module->m_name . ' (Employee Wise)',
                    'approvers' => max(1, $maxApprovers),
                ];
            }
        }
        // Build employees with ALL filters applied
        $employees = [];
        $baseQuery = Employee::where('emp_b_id', $user->emp_b_id)
            ->with([
                'fh_branch',
                'fh_reporting_manager_id',
                'fh_department',
                'fh_designation',
                'fh_grade',
                'fh_role',
            ])
            ->when($this->employeeStatusId, function ($q, $v) {
                if ($v === 'resigned') {
                    $q->whereNotNull('emp_last_working_date');
                } else {
                    $q->where('emp_status', $v);
                }
            })
            ->orderBy($this->sortBy === 'emp_name' ? 'emp_full_name' : 'emp_code', 'asc');
        if ($this->selectedEmployeeId) {
            $baseQuery->where('emp_id', $this->selectedEmployeeId);
        }
        if ($this->selectedDepartmentId) {
            $baseQuery->where('emp_d_id', $this->selectedDepartmentId);
        }
        if ($this->selectedDesignationId) {
            $baseQuery->where('emp_dg_id', $this->selectedDesignationId);
        }
        if ($this->selectedBranchId) {
            $baseQuery->where('emp_br_id', $this->selectedBranchId);
        }
        if ($this->selectedJobStatusId) {
            $baseQuery->where('emp_job_status', $this->selectedJobStatusId); // assuming m_id maps to emp_status
        }
        if ($this->selectedGradeId) {
            $baseQuery->where('emp_grade_id', $this->selectedGradeId);
        }
        if ($this->selectedRoleId) {
            $baseQuery->where('emp_role_id', $this->selectedRoleId);
        }
        if ($this->selectedReportingManagerId) {
            $baseQuery->where('emp_reporting_manager_id', $this->selectedReportingManagerId);
        }
        if ($this->selectedEmployeeTypeId) {
            $baseQuery->where('emp_type_id', $this->selectedEmployeeTypeId); // adjust if column name differs
        }
        $filteredEmployees = $baseQuery->get();
        // Now populate approval data for filtered employees only
        foreach ($hierarchyModules as $module) {
            foreach ($filteredEmployees as $employee) {
                $empId = $employee->emp_id;
                if (!isset($employees[$empId])) {
                    $employees[$empId] = [
                        'name' => $employee->emp_full_name,
                        'code' => $employee->emp_code ?? '-',
                        'branch' => $employee->fh_branch->br_name ?? '-',
                        'status' => $employee->emp_status_label ?? '-',
                        'type' => $employee->emp_type_label ?? 'Regular',
                        'reporting_manager' => $employee->fh_reporting_manager_id?->emp_full_name ?? '-',
                        'department' => $employee->fh_department?->d_name ?? '-',
                        'designation' => $employee->fh_designation?->dg_name ?? '-',
                        'grade' => $employee->fh_grade?->g_name ?? '-',
                        'role' => $employee->fh_role?->role_name ?? '-',
                        // Add more fields if needed
                        'processes' => [],
                    ];
                }
                $employees[$empId]['processes']['hw_' . $module->am_id] =
                    $module->fh_process_approvers
                    ->sortBy('level')
                    ->map(fn($a) => [
                        'name' => $a->fh_employee->emp_full_name ?? 'N/A',
                        'status' => ucfirst($a->fh_approver_status->m_name ?? '-'),
                    ])
                    ->values()
                    ->toArray();
            }
        }
        foreach ($employeeModules as $module) {
            foreach ($module->employeeRows as $row) {
                $employee = $row['employee'];
                if (!$filteredEmployees->contains('emp_id', $employee->emp_id)) {
                    continue; // Skip if not in filtered list
                }
                $empId = $employee->emp_id;
                if (!isset($employees[$empId])) {
                    $employees[$empId] = [
                        'name' => $employee->emp_full_name,
                        'code' => $employee->emp_code ?? '-',
                        'branch' => $employee->fh_branch->br_name ?? '-',
                        'status' => $employee->emp_status_label ?? '-',
                        'type' => $employee->emp_type_label ?? 'Regular',
                        'reporting_manager' => $employee->fh_reporting_manager_id->emp_full_name ?? '-',
                        'department' => $employee->fh_department?->d_name ?? '-',
                        'designation' => $employee->fh_designation?->dg_name ?? '-',
                        'grade' => $employee->fh_grade?->g_name ?? '-',
                        'role' => $employee->fh_role?->role_name ?? '-',
                        'processes' => [],
                    ];
                }
                $employees[$empId]['processes']['ew_' . $module->m_id] =
                    collect($row['managers'])->values()->toArray();
            }
        }
        $employeesCollection = collect(array_values($employees));
        if ($employeesCollection->isEmpty()) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'No records found for the selected criteria.']);
            return;
        }
        return Excel::download(
            new ApprovalReportExport(
                $this->businessName,
                $employeesCollection,
                $processMatrix,
                $this->filters // This controls which columns appear
            ),
            'approver-matrix-report' . now()->format('d-m-Y') . '.xlsx'
        );
    }
    // ================================================================
    // RENDER
    // ================================================================
    public function render()
    {
        $employees = Employee::where('emp_role_id', '<>', 1)
            ->where('emp_b_id', $this->businessId)
            ->when($this->search, fn($q) => $q->where(fn($qq) => $qq->where('emp_full_name', 'like', "%{$this->search}%")->orWhere('emp_code', 'like', "%{$this->search}%")))
            ->when($this->employeeStatusId, fn($q) => $q->where('emp_status', $this->employeeStatusId))
            ->limit(100)->get();
        $employeeStatus = MasterTable::where('m_group', 'STATUS')->limit(100)->select('m_id', 'm_name')->get();
        $employeeTypes = MasterTable::where('m_group', 'EMPLOYEE_TYPE')->limit(100)->select('m_id', 'm_name')->get();
        $attendanceStatuses = MasterTable::where('m_group', 'Attendance_Status')
            ->when(strlen($this->searchAttendanceStatus) >= 1 && !$this->selectedAttendanceStatusId, fn($q) => $q->where('m_name', 'like', "%{$this->searchAttendanceStatus}%"))
            ->limit(100)->select('m_id', 'm_name')->get();
        $departments = Department::where('d_b_id', $this->businessId)
            ->when(strlen($this->searchDepartment) >= 1 && !$this->selectedDepartmentId, fn($q) => $q->where('d_name', 'like', "%{$this->searchDepartment}%"))
            ->limit(100)->get();
        $shifts = PolicyShiftTiming::where('pst_b_id', $this->businessId)
            ->when(strlen($this->searchShift) >= 1 && !$this->selectedShiftId, fn($q) => $q->where('pst_name', 'like', "%{$this->searchShift}%"))
            ->limit(100)->get();
        $designations = Designation::where('dg_b_id', $this->businessId)
            ->when(strlen($this->searchDesignation) >= 1 && !$this->selectedDesignationId, fn($q) => $q->where('dg_name', 'like', "%{$this->searchDesignation}%"))
            ->limit(100)->get();
        $workMode = MasterTable::where('m_group', 'Work_Mode')
            ->when(strlen($this->searchWorkMode) >= 1 && !$this->selectedWorkModeId, fn($q) => $q->where('m_name', 'like', "%{$this->searchWorkMode}%"))
            ->limit(100)->select('m_id', 'm_name')->get();
        $dealers = Dealership::where('dlr_b_id', $this->businessId)
            ->when(strlen($this->searchDealer) >= 1 && !$this->selectedDealerId, fn($q) => $q->where('dlr_name', 'like', "%{$this->searchDealer}%"))
            ->limit(100)->get();
        $checkingMethods = MasterTable::where('m_group', 'CHECKIN_METHOD')
            ->when(strlen($this->searchCheckingMethod) >= 1 && !$this->selectedCheckingMethodId, fn($q) => $q->where('m_name', 'like', "%{$this->searchCheckingMethod}%"))
            ->limit(100)->select('m_id', 'm_name')->get();
        $branches = Branch::where('br_b_id', $this->businessId)
            ->when(strlen($this->searchBranch) >= 1 && !$this->selectedBranchId, fn($q) => $q->where('br_name', 'like', "%{$this->searchBranch}%"))
            ->limit(100)->get();
        $jobStatuses = MasterTable::where('m_group', 'JOB_STATUS')
            ->when(strlen($this->searchJobStatus) >= 1 && !$this->selectedJobStatusId, fn($q) => $q->where('m_name', 'like', "%{$this->searchJobStatus}%"))
            ->limit(100)->select('m_id', 'm_name')->get();
        $grades = Grade::where('g_b_id', $this->businessId)
            ->when(strlen($this->searchGrade) >= 1 && !$this->selectedGradeId, fn($q) => $q->where('g_name', 'like', "%{$this->searchGrade}%"))
            ->limit(100)->get();
        $roles = Role::where('role_b_id', $this->businessId)
            ->when(strlen($this->searchRole) >= 1 && !$this->selectedRoleId, fn($q) => $q->where('role_name', 'like', "%{$this->searchRole}%"))
            ->limit(100)->get();
        $reportingManagers = Employee::where('emp_b_id', $this->businessId)
            ->when($this->searchReportingManager, fn($q) => $q->where(fn($qq) => $qq->where('emp_full_name', 'like', "%{$this->searchReportingManager}%")->orWhere('emp_code', 'like', "%{$this->searchReportingManager}%")))
            ->limit(100)->get();
        $paymentMethods = ['Cash', 'Bank', 'Cheque'];
        // Load approval modules
        $modules = MasterTable::with([
            'fh_approval_modules' => fn($q) => $q->where('am_b_id', $this->businessId),
            'fh_approval_modules2' => fn($q) => $q->where('eam_b_id', $this->businessId),
        ])
            ->where('m_group', 'MODULE')
            ->get();
        $this->availableModules = $modules->map(function ($module) {
            if ($module->fh_approval_modules->isNotEmpty()) {
                $am = $module->fh_approval_modules->first();
                return [
                    'key' => 'hw_' . $am->am_id,
                    'name' => $am->am_name . ' (Hierarchy Wise)',
                ];
            }
            if ($module->fh_approval_modules2->isNotEmpty()) {
                return [
                    'key' => 'ew_' . $module->m_id,
                    'name' => $module->m_name . ' (Employee Wise)',
                ];
            }
            return null;
        })->filter()->values()->toArray();
        // CRITICAL: THIS LINE MAKES ALL MODULES SELECTED BY DEFAULT
        if (empty($this->moduleSelection) && count($this->availableModules) > 0) {
            $this->moduleSelection = collect($this->availableModules)->pluck('key')->toArray();
        }
        return view('livewire.employee-report.employee-approval-matrix-report', compact(
            'employees',
            'departments',
            'shifts',
            'designations',
            'dealers',
            'workMode',
            'attendanceStatuses',
            'checkingMethods',
            'branches',
            'jobStatuses',
            'grades',
            'paymentMethods',
            'roles',
            'reportingManagers',
            'employeeStatus',
            'employeeTypes'
        ));
    }
}
