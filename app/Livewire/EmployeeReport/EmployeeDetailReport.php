<?php
namespace App\Livewire\EmployeeReport;
use App\Exports\Employee\EmployeeReport as EmployeeDetailedReport;
use Livewire\Component;
use App\Models\AttendanceRecord;
use App\Models\Branch;
use App\Models\Business;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Department;
use App\Models\PolicyShiftTiming;
use App\Models\MasterTable;
use App\Models\Dealership;
use App\Models\Designation;
use App\Models\Grade;
use App\Models\Role;
use App\Models\WorkLocation;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
class EmployeeDetailReport extends Component
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
    public $sortBy = 'emp_code';
    public $showFilterPanel = false;
    public function mount()
    {
        $this->businessId = Auth::user()->emp_b_id;
        $this->businessName = Business::where('b_id', $this->businessId)->first()->b_name ?? '';
        $this->selectedDate = Carbon::now()->toDateString();
    }
    // Filters visibility
    public $filters = [
        'department' => false,
        'shift' => false,
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
        'paymentMethod' => 'Payment Method',
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
            'shift'            => ['aboutDetails'],
            'workMode'         => ['attendanceDetails'],
            'role'             => ['organizationDetails'],
            'reportingManager' => ['organizationDetails'],
            'dealership'       => ['organizationDetails'],
            'attendanceStatus' => ['attendanceDetails'],
            'checkingMethod'   => ['attendanceDetails'],
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
        $this->filters = array_fill_keys(array_keys($this->filters), false);
        // Optionally reset details filters
        $this->detailsFilter = array_fill_keys(array_keys($this->detailsFilter), false);
        // Keep filter panel open if it was open
        // $this->showFilterPanel remains unchanged
    }
    public function toggleFilterPanel()
    {
        $this->showFilterPanel = !$this->showFilterPanel;
    }
    public function updatedFilters($value, $key)
    {
        $mapping = $this->filterToDetailSection();
        if (!array_key_exists($key, $mapping)) {
            return;
        }
        foreach ((array) $mapping[$key] as $section) {
            if ($value) {
                // Toggle ON → enable detail
                $this->detailsFilter[$section] = true;
            } else {
                // Toggle OFF → disable detail
                $this->detailsFilter[$section] = false;
            }
        }
        $this->updatePreventCollapse();
    }
    /**
     * Auto-enable detail sections when a filter is turned ON.
     * Does NOT reset or override manual OFF.
     */
    private function syncDetailsWithFilters(): void
    {
        $mapping = $this->filterToDetailSection();
        foreach ($mapping as $filterKey => $sections) {
            if (!empty($this->filters[$filterKey])) {
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
        ]);
        $categoryFields = [
            'aboutDetails' => ['prifix', 'emp_fname', 'emp_mname', 'emp_lname', 'emp_full_name', 'emp_dob', 'emp_nationality', 'emp_religion', 'emp_body_mark'],
            'contactDetails' => ['emp_email', 'emp_phone', 'emp_company_email', 'emp_official_email', 'emp_official_contact', 'emp_emergency_contact', 'emp_emergency_relation', 'emp_relative_name', 'emp_relationship', 'emp_relative_phone_no'],
            'personalDetails' => ['emp_permanent_address', 'emp_permanent_pin_code', 'emp_temporary_address', 'emp_temporary_pin_code', 'emp_permanent_latitude', 'emp_permanent_longitude', 'emp_temporary_latitude', 'emp_temporary_longitude'],
            'identityDetails' => ['gov_doc_type', 'emp_gov_doc_type_number', 'emp_pan_number', 'emp_pan_file', 'emp_passport_number', 'emp_passport_file', 'emp_passport_valid', 'emp_voter_id_number', 'emp_voter_id_file', 'emp_driving_license_number', 'emp_driving_license_file', 'emp_drivng_license_valid', 'emp_aadhar_number', 'emp_aadhar_file', 'emp_account_number', 'emp_passbook_file'],
            'attendanceDetails' => ['emp_is_geofencing_active', 'emp_is_wifi_restricted', 'emp_assign_shift_start_time', 'emp_assign_shift_end_time', 'checkin_method', 'attendance_preference', 'emp_imei_no'],
            'organizationDetails' => ['leave_policy', 'emp_wo_policy', 'emp_attendance_policy', 'emp_sap_budget_code', 'emp_cost_center', 'approver_manager_1', 'approver_manager_2', 'emp_profit_center', 'emp_region', 'emp_project', 'emp_accountpurpose', 'emp_role_id', 'emp_type_id'],
            'bankDetails' => ['emp_paymentmode', 'emp_account_code', 'emp_bank_ifsc_code', 'emp_bank_name', 'emp_bank_branch_name', 'emp_bank_account_no', 'emp_bank_branch_code', 'emp_bank_micr_code', 'emp_bank_address_line1', 'emp_bank_address_line2', 'emp_salary_account_code', 'emp_salary_bank_ifsc_code', 'emp_salary_bank_name', 'emp_salary_bank_branch_name', 'emp_salary_bank_micr_code', 'emp_salary_bank_branch_code', 'emp_salary_bank_account_no'],
            'pfEsicDetails' => ['emp_is_pf_enabled', 'emp_pf_no', 'emp_pf_trust_code', 'emp_pf_found_member', 'emp_pf_universal_ac_no', 'emp_pf_joining_date', 'emp_pf_leaving_date', 'emp_pr_leaving_reason', 'emp_pf_joining_no', 'emp_lwf_no', 'emp_eps_no', 'emp_is_eps_enabled', 'emp_esic_no', 'emp_esic_joining_date', 'emp_esic_leaving_date', 'emp_esic_leaving_reason', 'emp_esic_dispensary', 'emp_esic_limit', 'emp_group_insured_by', 'emp_group_insurance_no', 'emp_group_insurance_start_date', 'emp_group_insurance_till_date', 'emp_is_tds_enabled', 'emp_vpf_percentage'],
            'joiningDetails' => ['emp_date_of_joining', 'emp_group_date_of_joining', 'emp_date_of_gratuity', 'emp_date_of_transfer', 'emp_date_of_expected_confirmation', 'emp_date_of_confirmation', 'emp_date_of_pay_structure', 'emp_probation_period', 'emp_probation_last_date', 'emp_year_of_service', 'emp_joining_leave_calc_type', 'emp_joining_leave_before_date', 'contractual_type'],
            'separationDetails' => ['emp_retirement_date', 'emp_separation_submit_date', 'emp_expected_leaving_date', 'emp_leaving_date_as_per_notice_period', 'emp_notice_period_req_days', 'emp_leaving_reason', 'emp_leave_date', 'emp_notice_period_serve_days', 'emp_settlement_from_date', 'emp_final_settlement_date', 'emp_exit_interview_date', 'emp_last_working_date', 'emp_notice_period_shortfall_days', 'emp_notice_period_day_for_employer', 'emp_notice_period_day_for_employee', 'emp_tada_settlement_amt'],
            'employeeSalaryDetails' => [
                'es_annual_ctc',
                'es_monthly_ctc',
                'es_base_salary',
                'es_rem_allowance',
                'es_monthly_gross',
                'es_annual_gross',
                'es_monthly_net_salary'
            ],
        ];
        $relationFields = [
            'fh_department' => ['d_name' => 'department_name'],
            'fh_designation' => ['dg_name' => 'designation_name'],
            'fh_dealership' => ['dlr_name' => 'dealership_name'],
            'fh_branch' => ['br_name' => 'branch_name'],
            'fh_job_status' => ['m_name' => 'job_status'],
            'fh_grade' => ['g_name' => 'grade_name'],
            'fh_shift_type' => ['pst_name' => 'shift_type'],
            'fh_policy_leave' => ['leave_type' => 'policy_leave_type'],
            'fh_employee_status' => ['m_name' => 'employee_status'],
            'fh_employee_type' => ['m_name' => 'employee_type'],
            'fh_pf_master' => ['m_name' => 'emp_is_pf_enabled'],
            'fh_work_mode' => ['m_name' => 'work_mode_name'],
            'fh_gov_doc_type' => ['m_name' => 'gov_doc_type'],
            'fh_attendance_preference' => ['m_name' => 'attendance_preference'],
            'fh_contractual_type' => ['m_name' => 'contractual_type'],
            'fh_employee_title' => ['m_name' => 'prifix'],
            'fh_esic_limit' => ['m_name' => 'emp_esic_limit'],
            'fh_reporting_manager_id' => ['emp_full_name' => 'emp_supervisor'],
            'fh_policy_leave' => ['pl_name' => 'leave_policy'],
            'fh_week_off_policy' => ['pwo_name' => 'emp_wo_policy'],
            'fh_attendance_policy' => ['ap_name' => 'emp_attendance_policy'],
            'fh_geofencing' => ['m_name' => 'emp_is_geofencing_active'],
            'fh_emp_region' => ['m_name' => 'emp_region'],
            'fh_role' => ['role_name' => 'emp_role_id'],
            'fh_employee_type' => ['m_name' => 'emp_type_id'],
        ];
        $relationToFilter = [
            'fh_department' => 'department',
            'fh_designation' => null,
            'fh_dealership' => 'dealership',
            'fh_branch' => 'branch',
            'fh_job_status' => 'jobStatus',
            'fh_grade' => 'grade',
            'fh_shift_type' => 'shift',
            'fh_employee_status' => null,
            'fh_employee_type' => null,
            'fh_work_mode' => 'workMode',
            'fh_gov_doc_type' => null,
            'fh_attendance_preference' => 'attendanceStatus',
            'fh_contractual_type' => null,
            'fh_employee_title' => null,
        ];
        $query = Employee::with(array_keys($relationFields))
            ->where('emp_b_id', $this->businessId)
            ->where('emp_role_id', '<>', 1)
            ->when($this->selectedEmployeeId, fn($q, $v) => $q->where('emp_id', $v))
            ->when($this->selectedBranchId, fn($q, $v) => $q->where('emp_br_id', $v))
            ->when($this->selectedJobStatusId, fn($q, $v) => $q->where('emp_job_status', $v))
            ->when($this->selectedGradeId, fn($q, $v) => $q->where('emp_grade_id', $v))
            ->when($this->selectedRoleId, fn($q, $v) => $q->where('emp_role_id', $v))
            ->when($this->selectedReportingManagerId, fn($q, $v) => $q->where('emp_supervisor_id', $v))
            ->when($this->selectedPaymentMethod, fn($q, $v) => $q->where('emp_paymentmode', $v))
            ->when($this->employeeStatusId, function ($q, $v) {
                if ($v === 'resigned') {
                    $q->whereNotNull('emp_last_working_date');
                } else {
                    $q->where('emp_status', $v);
                }
            })
            ->orderBy($this->sortBy === 'emp_name' ? 'emp_full_name' : 'emp_code', 'asc');
        if ($this->selectedDepartmentId) {
            $query->whereHas('fh_department', fn($q) => $q->where('d_id', $this->selectedDepartmentId));
        }
        if ($this->selectedShiftId) {
            $query->where('emp_shift_type_id', $this->selectedShiftId);
        }
        if ($this->selectedAttendanceStatusId) {
            $query->where('emp_attendance_preference', $this->selectedAttendanceStatusId);
        }
        if ($this->selectedDealerId) {
            $query->whereHas('fh_dealership', fn($q) => $q->where('dlr_id', $this->selectedDealerId));
        }
        if ($this->selectedWorkModeId) {
            $query->where('emp_work_mode_id', $this->selectedWorkModeId);
        }
        if ($this->selectedDesignationId) {
            $query->whereHas('fh_designation', fn($q) => $q->where('dg_id', $this->selectedDesignationId));
        }
        if ($this->selectedCheckingMethodId) {
            $query->whereJsonContains('emp_checkin_method_id', $this->selectedCheckingMethodId);
        }
        if ($this->selectedRoleId) {
            $query->where('emp_role_id', $this->selectedRoleId);
        }
        if ($this->selectedEmployeeTypeId) {
            $query->where('emp_type_id', $this->selectedEmployeeTypeId);
        }
        // === EAGER LOAD SALARY MASTER COMPONENTS ===
        $query->with([
            'fh_employee_salary',
            'fh_employee_salary.salary_earnings.fh_salary_earning_type',
            // 'fh_employee_salary.salary_deductions.fh_salary_deduction_type',
        ]);
        $records = $query->get();
        if ($records->isEmpty()) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'No records found for the selected criteria.']);
            return;
        }
        // === OPTIMIZED: Fetch all project names in ONE query ===
        $allProjectIds = $records->pluck('emp_project_id')
            ->filter()
            ->map(function ($ids) {
                return is_array($ids) ? $ids : json_decode($ids, true);
            })
            ->flatten()
            ->filter()
            ->unique()
            ->values();
        $projectNameMap = [];
        if ($allProjectIds->isNotEmpty()) {
            $projectNameMap = Project::whereIn('ps_id', $allProjectIds)
                ->pluck('ps_name', 'ps_id')
                ->toArray();
        }
        // === COLLECT UNIQUE SALARY MASTER COMPONENTS (for dynamic columns in Excel) ===
        $earningComponents = $records->pluck('fh_employee_salary.salary_earnings')
            ->flatten()
            ->pluck('fh_salary_earning_type.sa_title')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();
        $deductionComponents = $records->pluck('fh_employee_salary.salary_deductions')
            ->flatten()
            ->pluck('fh_salary_deduction_type.m_name')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();
        $filteredRecords = $records->map(function ($record) use (
            $categoryFields,
            $relationFields,
            $relationToFilter,
            $projectNameMap,
            $earningComponents,
            $deductionComponents
        ) {
            $filteredData = [
                'emp_id' => $record->emp_id,
                'emp_full_name' => $record->emp_full_name,
                'emp_code' => $record->emp_code,
            ];
            // === Handle emp_project manually ===
            $projectIds = $record->emp_project_id;
            if (is_string($projectIds)) {
                $projectIds = json_decode($projectIds, true);
            }
            if (is_array($projectIds) && !empty($projectIds)) {
                $names = array_map(function ($id) use ($projectNameMap) {
                    return $projectNameMap[$id] ?? 'Unknown Project';
                }, array_filter($projectIds));
                $filteredData['emp_project'] = implode(', ', $names);
            } else {
                $filteredData['emp_project'] = null;
            }
            // === Manually add Salary Totals ===
            $salaryFields = [
                'es_annual_ctc',
                'es_monthly_ctc',
                'es_base_salary',
                'es_rem_allowance',
                'es_monthly_gross',
                'es_annual_gross',
                'es_monthly_net_salary'
            ];
            foreach ($salaryFields as $field) {
                $filteredData[$field] = $record->fh_employee_salary?->$field ?? null;
            }
            // === Process category fields (SKIP salary fields since already handled) ===
            foreach ($this->detailsFilter as $category => $isEnabled) {
                if (!$isEnabled || !isset($categoryFields[$category])) {
                    continue;
                }
                foreach ($categoryFields[$category] as $field) {
                    if (in_array($field, $salaryFields) || $field === 'emp_project') {
                        continue;
                    }
                    $relationKey = match ($field) {
                        'prifix' => 'fh_employee_title',
                        'emp_supervisor_id' => 'fh_reporting_manager_id',
                        'emp_esic_limit' => 'fh_pf_master',
                        'gov_doc_type' => 'fh_gov_doc_type',
                        'shift_type' => 'fh_shift_type',
                        'checkin_method' => 'fh_checkin_method',
                        'work_mode_name' => 'fh_work_mode',
                        'attendance_preference' => 'fh_attendance_preference',
                        'contractual_type' => 'fh_contractual_type',
                        'leave_policy' => 'fh_policy_leave',
                        'emp_wo_policy' => 'fh_week_off_policy',
                        'emp_attendance_policy' => 'fh_attendance_policy',
                        'emp_geofencing' => 'fh_geofencing',
                        'emp_region' => 'fh_emp_region',
                        'emp_role_id' => 'fh_role',
                        'emp_type_id' => 'fh_employee_type',
                        default => null,
                    };
                    if ($relationKey && isset($relationFields[$relationKey])) {
                        $alias = $relationFields[$relationKey]['m_name'] ?? $field;
                        $filteredData[$alias] = $record->$relationKey?->m_name
                            ?? $record->$relationKey?->pl_name
                            ?? $record->$relationKey?->pwo_name
                            ?? $record->$relationKey?->ap_name
                            ?? $record->$relationKey?->pt_name
                            ?? null;
                    } else {
                        $filteredData[$field] = $record->$field ?? null;
                    }
                }
            }
            // === Process relation fields (Department, Designation, etc.) ===
            foreach ($relationFields as $relation => $fields) {
                $filterKey = $relationToFilter[$relation] ?? null;
                if ($filterKey && !$this->filters[$filterKey]) {
                    continue;
                }
                if ($relation === 'fh_employee_title' && $this->detailsFilter['aboutDetails']) {
                    continue;
                }
                if ($record->$relation) {
                    foreach ($fields as $dbColumn => $alias) {
                        $filteredData[$alias] = $record->$relation->$dbColumn ?? null;
                    }
                } else {
                    foreach ($fields as $dbColumn => $alias) {
                        $filteredData[$alias] = null;
                    }
                }
            }
            // === ADD SALARY MASTER EARNINGS & DEDUCTIONS (only if enabled) ===
            if (!empty($this->detailsFilter['salaryMaster'])) {
                // Earnings
                $earnings = $record->fh_employee_salary?->salary_earnings ?? collect();
                foreach ($earnings as $earning) {
                    $title = $earning->fh_salary_earning_type?->sa_title ?? 'Unknown Earning';
                    $amount = $earning->es_e_amount ?? 0;
                    $filteredData["Salary Master_{$title}"] = $amount;
                }
                // Deductions
                $deductions = $record->fh_employee_salary?->salary_deductions ?? collect();
                foreach ($deductions as $deduction) {
                    $title = $deduction->fh_salary_deduction_type?->m_name ?? 'Unknown Deduction';
                    $amount = $deduction->es_d_amount ?? 0;
                    $filteredData["Salary Master_{$title}"] = $amount;
                }
                // Optional: Add key totals under Salary Master
                $filteredData['Salary Master_Monthly Gross'] = $record->fh_employee_salary?->es_monthly_gross ?? 0;
                $filteredData['Salary Master_Monthly CTC'] = $record->fh_employee_salary?->es_monthly_ctc ?? 0;
                $filteredData['Salary Master_Annual CTC'] = $record->fh_employee_salary?->es_annual_ctc ?? 0;
            }
            return $filteredData;
        })->toArray();
        $fileName = 'EmployeeReport_' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new EmployeeDetailedReport(
            $filteredRecords,
            $this->filters,
            $this->detailsFilter,
            $categoryFields,
            $this->detailsFields ?? [],
            $relationFields,
            $relationToFilter,
            $this->businessName,
            $earningComponents,      // For dynamic heading grouping
            $deductionComponents     // For dynamic heading grouping
        ), $fileName);
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
        return view('livewire.employee-report.employee-detail-report', compact(
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
