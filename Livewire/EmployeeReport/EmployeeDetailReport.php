<?php

namespace App\Livewire\EmployeeReport;

use App\Exports\Employee\EmployeeReport as EmployeeDetailedReport;
use Livewire\Component;
use App\Models\AttendanceRecord;
use App\Models\Branch;
use App\Models\Business;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
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
    public function mount()
    {
        $this->businessId = Auth::user()->emp_b_id;
        $this->businessName = Business::where('b_id', $this->businessId)->first()->b_name ?? '';
        $this->selectedDate = Carbon::now()->toDateString();
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
        'paymentMethod' => false,
        'role' => false,
        'reportingManager' => false,
        'employeeType' => false,
        'employeeStatusFilter' => false
    ];
    public $detailsFilter = [
        'separationDetails' => false,
        'pfEsicDetails' => false,
        'organizationDetails' => false,
        'attendanceDetails' => false,
        'identityDetails' => false,
        'personalDetails' => false,
        'contactDetails' => false,
        'aboutDetails' => false,
        'joiningDetails' => false,
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
        'attendanceDetails' => 'Attendance',
    ];
    public $filterFields = [
        'role' => 'Role',
        'employeeType' => 'Employee Type',
        'department' => 'Department',
        'shift' => 'Shift',
        'designation' => 'Designation',
        'workMode' => 'Work Mode',
        // 'checkingMethod' => 'Checking Method',
        'dealership' => 'Dealership',
        'paymentMethod' => 'Payment Method',
        'branch' => 'Branch',
        'jobStatus' => 'Job Status',
        'grade' => 'Grade',
        'reportingManager' => 'Reporting Manager',
    ];
    public function updatePreventCollapse()
    {
        $this->preventCollapse = collect($this->filters)->contains(true);
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
                'paymentMethod' => [
                    $this->selectedPaymentMethod = null,
                ],
                'role' => [
                    $this->selectedRoleId = null,
                    $this->searchRole = '',
                ],
                'reportingManager' => [
                    $this->selectedReportingManagerId = null,
                    $this->searchReportingManager = '',
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
            'searchCheckingMethod' => 'selectedCheckingMethodId',
            'searchDealer' => 'selectedDealerId',
            'searchAttendanceStatus' => 'selectedAttendanceStatusId',
            'searchBranch' => 'selectedBranchId',
            'searchJobStatus' => 'selectedJobStatusId',
            'searchGrade' => 'selectedGradeId',
            'searchRole' => 'selectedRoleId',
        ];
        // Apply the generic reset logic
        if (array_key_exists($property, $mapping)) {
            $this->{$mapping[$property]} = null;
        }
    }
    public function generateReport()
    {
        // Validation rules
        $this->validate([
            'selectedDate' => 'required|date',
            'selectedEmployeeId' => 'nullable|exists:employees,emp_id',
            'selectedAttendanceStatusId' => 'nullable',
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
            'selectedAttendanceStatusId.exists' => 'The selected attendance status does not exist.',
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
        // Define the fields for each category
        $categoryFields = [
            'aboutDetails' => [
                'prifix', // Changed to prifix to map to fh_employee_title
                'emp_fname',
                'emp_mname',
                'emp_lname',
                'emp_full_name',
                'emp_dob',
                'emp_nationality',
                'emp_religion',
                'emp_body_mark',
            ],
            'contactDetails' => [
                'emp_email',
                'emp_phone',
                'emp_company_email',
                'emp_official_email',
                'emp_official_contact',
                'emp_emergency_contact',
                'emp_emergency_relation',
                // 'emp_is_temporary_add_same',
                'emp_relative_name',
                'emp_relationship',
                'emp_relative_phone_no',
            ],
            'personalDetails' => [
                'emp_permanent_address',
                'emp_permanent_pin_code',
                'emp_temporary_address',
                'emp_temporary_pin_code',
                'emp_permanent_latitude',
                'emp_permanent_longitude',
                'emp_temporary_latitude',
                'emp_temporary_longitude',
            ],
            'identityDetails' => [
                'gov_doc_type',
                'emp_gov_doc_type_number',
                'emp_pan_number',
                'emp_pan_file',
                'emp_passport_number',
                'emp_passport_file',
                'emp_passport_valid',
                'emp_voter_id_number',
                'emp_voter_id_file',
                'emp_driving_license_number',
                'emp_driving_license_file',
                'emp_drivng_license_valid',
                'emp_aadhar_number',
                'emp_aadhar_file',
                'emp_account_number',
                'emp_passbook_file',
            ],
            'attendanceDetails' => [
                'emp_is_geofencing_active',
                // 'emp_is_geowork_active',
                'emp_is_wifi_restricted',
                // 'shift_type',
                'emp_assign_shift_start_time',
                'emp_assign_shift_end_time',
                'checkin_method',
                'attendance_preference',
                'emp_imei_no',
            ],
            'organizationDetails' => [
                'emp_policy_tax',
                'leave_policy',
                'emp_wo_policy',
                'emp_attendance_policy',
                // 'emp_supervisor_id',
                'emp_sap_budget_code',
                'emp_account_code',
                'emp_cost_center',
                // 'emp_reporting_manager_id',
                'approver_manager_1',
                'approver_manager_2',
                // 'work_mode_name',
                'emp_bank_ifsc_code',
                'emp_bank_name',
                'emp_bank_branch_name',
                'emp_bank_account_no',
                'emp_bank_branch_code',
                'emp_bank_micr_code',
                'emp_bank_address_line1',
                'emp_bank_address_line2',
                'emp_salary_account_code',
                'emp_salary_bank_ifsc_code',
                'emp_salary_bank_name',
                'emp_salary_bank_branch_name',
                'emp_salary_bank_micr_code',
                'emp_salary_bank_branch_code',
                'emp_salary_bank_account_no',
                'emp_profit_center',
                'emp_region',
                'emp_project',
                'emp_paymentmode',
                'emp_accountpurpose',
            ],
            'pfEsicDetails' => [
                'emp_is_pf_enabled',
                'emp_pf_no',
                'emp_pf_trust_code',
                'emp_pf_found_member',
                'emp_pf_universal_ac_no',
                'emp_pf_joining_date',
                'emp_pf_leaving_date',
                'emp_pr_leaving_reason',
                'emp_pf_joining_no',
                'emp_lwf_no',
                'emp_eps_no',
                'emp_is_eps_enabled',
                'emp_esic_no',
                'emp_esic_joining_date',
                'emp_esic_leaving_date',
                'emp_esic_leaving_reason',
                'emp_esic_dispensary',
                'emp_esic_limit',
                'emp_group_insured_by',
                'emp_group_insurance_no',
                'emp_group_insurance_start_date',
                'emp_group_insurance_till_date',
                'emp_is_tds_enabled',
                'emp_vpf_percentage',
            ],
            'joiningDetails' => [
                'emp_date_of_joining',
                'emp_group_date_of_joining',
                'emp_date_of_gratuity',
                'emp_date_of_transfer',
                'emp_date_of_expected_confirmation',
                'emp_date_of_confirmation',
                'emp_date_of_pay_structure',
                'emp_probation_period',
                'emp_probation_last_date',
                'emp_year_of_service',
                // 'emp_allow_joining_leave',
                'emp_joining_leave_calc_type',
                'emp_joining_leave_before_date',
                // 'emp_allow_probation_leave',
                'contractual_type',
            ],
            'separationDetails' => [
                'emp_retirement_date',
                'emp_separation_submit_date',
                'emp_expected_leaving_date',
                'emp_leaving_date_as_per_notice_period',
                'emp_notice_period_req_days',
                'emp_leaving_reason',
                'emp_leave_date',
                'emp_notice_period_serve_days',
                'emp_settlement_from_date',
                'emp_final_settlement_date',
                'emp_exit_interview_date',
                'emp_last_working_date',
                'emp_notice_period_shortfall_days',
                'emp_notice_period_day_for_employer',
                'emp_notice_period_day_for_employee',
                'emp_tada_settlement_amt',
            ],
        ];
        // Define fields for relations (including fh_employee_title for prifix mapping)
        $relationFields = [
            'fh_department' => ['d_name' => 'department_name'],
            'fh_designation' => ['dg_name' => 'designation_name'],
            'fh_dealership' => ['dlr_name' => 'dealership_name'],
            'fh_branch' => ['br_name' => 'branch_name'],
            'fh_job_status' => ['m_name' => 'job_status'],
            'fh_grade' => ['g_name' => 'grade_name'],
            'fh_shift_type' => ['pst_name' => 'shift_type'],
            
            // 'fh_employee_salary' => ['gross_salary' => 'employee_salary'],
            'fh_policy_leave' => ['leave_type' => 'policy_leave_type'],
            'fh_employee_status' => ['m_name' => 'employee_status'],
            'fh_employee_type' => ['m_name' => 'employee_type'],
            // 'fh_employee_category' => ['m_name' => 'employee_category'],
            'fh_work_mode' => ['m_name' => 'work_mode_name'],
            'fh_gov_doc_type' => ['m_name' => 'gov_doc_type'],
            // 'fh_checkin_method' => ['m_name' => 'checkin_method'],
            'fh_attendance_preference' => ['m_name' => 'attendance_preference'],
            'fh_contractual_type' => ['m_name' => 'contractual_type'],
            'fh_employee_title' => ['m_name' => 'prifix'],
            'fh_pf_master' => ['m_name' => 'emp_esic_limit'],
            'fh_reporting_manager_id' => ['emp_full_name' => 'emp_supervisor'],
            'fh_policy_leave' => ['pl_name' => 'leave_policy'],
            'fh_week_off_policy' => ['pwo_name' => 'emp_wo_policy'],
            'fh_attendance_policy' => ['ap_name' => 'emp_attendance_policy'],
            'policyTax' => ['pt_name' => 'emp_policy_tax'],
            'fh_geofencing' => ['m_name' => 'emp_is_geofencing_active'],
            'fh_project' => ['ps_name' => 'emp_project'],
            'fh_emp_region' => ['m_name' => 'emp_region'],

        ];
        $relationToFilter = [
            'fh_department' => 'department',
            'fh_designation' => 'designation',
            'fh_dealership' => 'dealership',
            'fh_branch' => 'branch',
            'fh_job_status' => 'jobStatus',
            'fh_grade' => 'grade',
            'fh_shift_type' => 'shift',
            'fh_employee_status' => null,
            'fh_employee_type' => 'employeeType',
            'fh_employee_category' => null,
            'fh_work_mode' => 'workMode',
            'fh_gov_doc_type' => null,
            // 'fh_checkin_method' => 'checkingMethod',
            'fh_attendance_preference' => 'attendanceStatus',
            'fh_contractual_type' => null,
            'fh_employee_title' => null, // No filter, controlled by aboutDetails
        ];
        // Build the query with additional relations
        $query = Employee::with(array_keys($relationFields))
            ->where('emp_b_id', $this->businessId)
            ->where('emp_role_id', '<>', 1)
            ->when($this->selectedEmployeeId, fn($q, $v) => $q->where('emp_id', $v))
            ->when($this->employeeStatusId, fn($q, $v) => $q->where('emp_status', $v))
            ->when($this->selectedBranchId, fn($q, $v) => $q->where('emp_br_id', $v))
            ->when($this->selectedJobStatusId, fn($q, $v) => $q->where('emp_job_status', $v))
            ->when($this->selectedGradeId, fn($q, $v) => $q->where('emp_grade_id', $v))
            ->when($this->selectedRoleId, fn($q, $v) => $q->where('emp_role_id', $v))
            ->when($this->selectedReportingManagerId, fn($q, $v) => $q->where('emp_supervisor_id', $v))
            ->when($this->selectedPaymentMethod, fn($q, $v) => $q->where('emp_paymentmode', $v));
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
        // Execute the query and get the records
        $records = $query->get();
        // dd($records->toArray());
        if ($records->isEmpty()) {
            session()->flash('error', 'No records found for the selected criteria.');
            return;
        }
        // Filter the records based on the selected categories and include relations
        $filteredRecords = $records->map(function ($record) use ($categoryFields, $relationFields, $relationToFilter) {
            $filteredData = [
                'emp_id' => $record->emp_id,
                'emp_full_name' => $record->emp_full_name,
                'emp_code' => $record->emp_code,
            ];
            // Include fields from Employee model and map IDs to names
            foreach ($this->detailsFilter as $category => $isEnabled) {
                if ($isEnabled && isset($categoryFields[$category])) {
                    foreach ($categoryFields[$category] as $field) {
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
                            'emp_policy_tax' => 'policyTax',
                            'emp_geofencing' => 'fh_geofencing',
                            'emp_project' => 'fh_project',
                            'emp_region' => 'fh_emp_region',
                            default => null,
                        };
                        if ($relationKey && isset($relationFields[$relationKey])) {
                            $alias = $relationFields[$relationKey]['m_name'] ?? $field;
                            $filteredData[$alias] = $record->$relationKey ? $record->$relationKey->m_name : null;
                        } else {
                            $filteredData[$field] = $record->$field ?? null;
                        }
                    }
                }
            }
            // Include fields from relations, excluding those already handled in categoryFields
            foreach ($relationFields as $relation => $fields) {
                $filterKey = $relationToFilter[$relation] ?? null;
                if ($filterKey && !$this->filters[$filterKey]) {
                    continue;
                }
                // Skip fh_employee_title if aboutDetails is enabled to avoid duplication
                if ($relation === 'fh_employee_title' && $this->detailsFilter['aboutDetails']) {
                    continue;
                }
                if ($record->$relation) {
                    foreach ($fields as $field => $alias) {
                        $filteredData[$alias] = $record->$relation->$field ?? null;
                    }
                } else {
                    foreach ($fields as $field => $alias) {
                        $filteredData[$alias] = null;
                    }
                }
            }
            // dd($filteredData);
            return $filteredData;
        })->toArray();
        // Generate the Excel file with the filtered records
        $fileName = 'EmployeeReport_' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new EmployeeDetailedReport($filteredRecords, $this->filters, $this->detailsFilter, $categoryFields, $this->detailsFields, $relationFields, $relationToFilter, $this->businessName), $fileName);
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
            ->when($this->employeeStatusId, fn($q) => $q->where('emp_status', $this->employeeStatusId))
            ->limit(100)
            ->get();
        $employeeStatus = MasterTable::where('m_group', 'STATUS')
            ->limit(100)
            ->select('m_id', 'm_name')
            ->get();
        $employeeTypes = MasterTable::where('m_group', 'EMPLOYEE_TYPE')
            ->limit(100)
            ->select('m_id', 'm_name')
            ->get();
        $attendanceStatuses = MasterTable::where('m_group', 'Attendance_Status')
            ->when(
                strlen($this->searchAttendanceStatus) >= 1 && !$this->selectedAttendanceStatusId,
                fn($q) => $q->where('m_name', 'like', "%{$this->searchAttendanceStatus}%")
            )
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
            ->select('m_id', 'm_name')
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
            ->select('m_id', 'm_name')
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
            ->select('m_id', 'm_name')
            ->get();
        $grades = Grade::where('g_b_id', $this->businessId)
            ->when(
                strlen($this->searchGrade) >= 1 && !$this->selectedGradeId,
                fn($q) => $q->where('g_name', 'like', "%{$this->searchGrade}%")
            )
            ->limit(100)
            ->get();
        $roles = Role::where('role_b_id', $this->businessId)
            ->when(
                strlen($this->searchRole) >= 1 && !$this->selectedRoleId,
                fn($q) => $q->where('role_name', 'like', "%{$this->searchRole}%")
            )
            ->limit(100)
            ->get();
        $reportingManagers = Employee::where('emp_b_id', $this->businessId)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('emp_full_name', 'like', '%' . $this->search . '%')
                        ->orWhere('emp_code', 'like', '%' . $this->search . '%');
                });
            })
            ->limit(100)
            ->get();
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
            'employeeTypes',
        ));
    }
}
