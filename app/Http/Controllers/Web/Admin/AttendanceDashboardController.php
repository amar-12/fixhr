<?php

namespace App\Http\Controllers\Web\Admin;

use App\Models\Branch;
use App\Models\Country;
use App\Models\Business;
use App\Models\Employee;
use App\Models\GatePass;
use App\Models\MasterTable;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use App\Models\PayrollPeriod;
use Illuminate\Support\Carbon;
use App\Helpers\ApprovalHelper;
use App\Models\AttendanceRecord;
use App\Models\PolicyHolidayList;
use Illuminate\Support\Facades\DB;
use App\Models\AttendanceException;
use App\Http\Controllers\Controller;
use App\Models\EmployeeApprovalMapping;
use App\Models\EmployeeExitRequest;

use App\Models\ProcessedEmployeeSalary;
use App\Models\RecruitmentCandidate;
use Illuminate\Support\Facades\Auth;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;


class AttendanceDashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // $selectedDate = $request->input('date');
        $selectedDate = $request->input('date') ?? now()->toDateString();

        $user = Auth::user();
        $businessId = $user->emp_b_id;
        $monthFilter = now()->format('Y-m');
        $onlyMonth = now()->format('m-d');
        $monthStart = now()->startOfMonth()->format('Y-m-d');
        $monthEnd = now()->endOfMonth()->format('Y-m-d');
        // $dateFilter = now()->toDateString();

        $dateFilter = $selectedDate ?? now()->toDateString();

        $sine = (Country::where('c_id', $user->fh_business->b_currency)->pluck('c_currency_symbol')->first());
        // dd($sine);

        $allEmployeeCount = Employee::where('emp_b_id', $businessId)->whereNot('emp_role_id', 1)->where('emp_status', 71)->count();
        // $allEmployeeCount = Employee::where('emp_b_id', $businessId)->whereNot('emp_role_id', 1)->count();

        $attendance = AttendanceRecord::where('atd_b_id', $businessId)->whereDate('atd_date', $dateFilter)->where('atd_is_absent', 0);

        $full_day_present = $attendance->count(); // Count of full-day present
        $late_coming = AttendanceRecord::where('atd_b_id', $businessId)->whereDate('atd_date', $dateFilter)->where('atd_is_late', 1)->count();
        $half_day = AttendanceRecord::where('atd_b_id', $businessId)->whereDate('atd_date', $dateFilter)->where('atd_attendance_status', 252)->count();
        $leave_count = LeaveRequest::where('lvr_b_id', $businessId)->whereBetween('lvr_start_date', [$dateFilter, $dateFilter])->where('lvr_status', 140)->count();
        $absent = $allEmployeeCount - $full_day_present - $leave_count;

        // $recruitment_candidate = RecruitmentCandidate::where('rc_b_id', $businessId)->where('rc_stage_id', NULL)->count();

        //****** branch wise employee *****
        $branches = Branch::where('br_b_id', $businessId)->get();

        $labels = [];
        $total = [];
        $active = [];
        $inactive = [];

        foreach ($branches as $branch) {
            $labels[] = $branch->br_name; // chart label me branch name show hoga

            $total[] = Employee::where('emp_b_id', $businessId)
                ->where('emp_br_id', $branch->br_id)
                ->whereNot('emp_role_id', 1)
                ->count();

            $active[] = Employee::where('emp_b_id', $businessId)
                ->where('emp_br_id', $branch->br_id)
                ->whereNot('emp_role_id', 1)
                ->where('emp_status', 71)
                ->count();

            $inactive[] = Employee::where('emp_b_id', $businessId)
                ->where('emp_br_id', $branch->br_id)
                ->whereNot('emp_role_id', 1)
                ->where('emp_status', 72)
                ->count();
        }

        $today = now()->startOfDay();

        // Upcoming holidays
        /*$upcoming_holiday = PolicyHolidayList::where('phl_b_id', $businessId)
            ->where(function ($query) use ($monthStart, $monthEnd) {
                $query->whereBetween('phl_start_date', [$monthStart, $monthEnd])
                      ->orWhereBetween('phl_end_date', [$monthStart, $monthEnd]);
            })
            ->where(function ($query) use ($today) {
                $query->whereNotNull('phl_end_date') // Ensure phl_end_date is not null
                      ->where('phl_end_date', '>=', $today); // Exclude holidays that have passed
            })
            ->get();*/

        $currentYear = $today->year;

        $upcoming_holiday = PolicyHolidayList::where('phl_b_id', $businessId)
            ->whereYear('phl_start_date', $currentYear)
            ->whereDate('phl_end_date', '>=', $today)
            ->orderBy('phl_start_date', 'asc')
            ->take(4)
            ->get();


        // Upcoming birthdays
        $onlyMonth = $today->month; // Get the current month
        $todayDay = $today->day; // Get the current day

        /*$upcoming_birthday = Employee::where('emp_b_id', $businessId)
            ->whereMonth('emp_dob', $onlyMonth) // Filter birthdays for the current month
            ->where(function ($query) use ($todayDay) {
                $query->whereNotNull('emp_dob') // Ensure emp_dob is not null
                      ->whereDay('emp_dob', '>=', $todayDay); // Exclude dates that have already passed in the current month
            })
            ->where('emp_status', 71) // Filter active employees (status 71)
            ->get();*/

        $currentYear = $today->year;

        $upcoming_birthday = Employee::where('emp_b_id', $businessId)
            ->where('emp_status', 71)
            ->whereNotNull('emp_dob')
            ->whereRaw("DATE_FORMAT(emp_dob, '%m-%d') >= DATE_FORMAT(CURDATE(), '%m-%d')")
            ->select('*', DB::raw("
                DATE_FORMAT(emp_dob, '%m-%d') as dob_md,
                DATE_FORMAT(CONCAT($currentYear, '-', DATE_FORMAT(emp_dob, '%m-%d')), '%Y-%m-%d') as next_birthday
            "))
            ->orderBy('next_birthday')
            ->limit(5)
            ->get();

        $gatepass_request = GatePass::where('gtp_b_id', $businessId)
            ->whereBetween('gtp_date', [$monthStart, $monthEnd])
            ->where('gtp_status', 140)
            ->latest('gtp_date')
            ->take(3)
            ->get();

        $mispunch_request = AttendanceException::with(['employee:emp_id,emp_full_name', 'fh_mispunch_reason:m_id,m_name'])
            ->where('ae_b_id', $businessId)
            ->whereBetween('ae_date', [$monthStart, $monthEnd])
            ->where('ae_status', 140)
            ->latest('ae_date')
            ->take(3)
            ->get();
        $leave = LeaveRequest::where('lvr_b_id', $businessId)->whereBetween('lvr_start_date', [$monthStart, $monthEnd])->where('lvr_status', 140)->take(3)->get();

        //filter
        $branchFilter = request()->input('branchFilter');
        $designationFilter = request()->input('designationFilter');
        $departmentFilter = request()->input('departmentFilter');
        $toDateFilter = request()->input('toDate');

        $payrollPeriod = PayrollPeriod::where('pp_b_id', $businessId)
            ->whereDate('pp_start_date', '<=', $selectedDate)
            ->whereDate('pp_end_date', '>=', $selectedDate)
            ->first();

        // Get all modules that have at least one approval mapping for this business
        $moduleIds = EmployeeApprovalMapping::where('eam_b_id', $user->emp_b_id)
            ->distinct()
            ->pluck('eam_module_id')
            ->toArray();

        // Get module names from MasterTable
        $modules = MasterTable::whereIn('m_id', $moduleIds)
            ->pluck('m_name', 'm_id'); // module_id => module_name

        $moduleWiseEmployees = [];

        foreach ($modules as $moduleId => $moduleName) {

            // Employees already assigned in approval flow for this module
            $assignedEmpIds = EmployeeApprovalMapping::where('eam_b_id', $user->emp_b_id)
                ->where('eam_module_id', $moduleId)
                ->pluck('eam_emp_id')
                ->toArray();

            // Employees NOT assigned yet
            $employees = Employee::where('emp_b_id', $user->emp_b_id)
                ->where('emp_status', 71) // active?
                ->where('emp_role_id', '!=', 1) // skip admin
                ->whereNotIn('emp_id', $assignedEmpIds)
                ->select('emp_id', 'emp_code', 'emp_full_name')
                ->orderBy('emp_full_name')
                ->get();

            // Only include module if it has unassigned employees
            if ($employees->isNotEmpty()) {
                $moduleWiseEmployees[$moduleId] = [
                    'module_name' => $moduleName,
                    'employees'   => $employees,
                ];
            }
        }


        $processedSalaries = collect(); // Default empty collection
        $processedCount = 0; // Default count
        $netPayTotal = 0;

        if ($payrollPeriod) {
            $processedSalaries = ProcessedEmployeeSalary::with(['employee'])
                ->where('ps_payroll_id', $payrollPeriod->pp_id)
                ->where('ps_b_id', $businessId)
                ->get();

            $processedCount = $processedSalaries->count();
            $netPayTotal = $processedSalaries->sum('ps_monthly_net_salary');
        }
        // dd($processedSalaries);


        // Employee Exit module 

        // $statusCounts = EmployeeExitRequest::where('er_b_id', $businessId)
        //     ->selectRaw('er_overall_status, COUNT(*) as total')
        //     ->groupBy('er_overall_status')
        //     ->pluck('total', 'er_overall_status');

        // $total_exits                = $statusCounts->sum();
        // $resignation_submitted      = $statusCounts['RESIGNATION_SUBMITTED'] ?? 0;
        // $management_approved        = $statusCounts['MANAGEMENT_APPROVED'] ?? 0;
        // $clearance_in_progress      = $statusCounts['CLEARANCE_IN_PROGRESS'] ?? 0;
        // $exit_interview_completed   = $statusCounts['EXIT_INTERVIEW_COMPLETED'] ?? 0;
        // $final_settlement_completed = $statusCounts['FINAL_SETTLEMENT_COMPLETED'] ?? 0;
        // $document_generated         = $statusCounts['DOCUMENT_GENERATED'] ?? 0;
        // $relieved                   = $statusCounts['RELIEVED'] ?? 0;
        // $rejected                   = $statusCounts['REJECTED'] ?? 0;

        // dd($total_exits, $resignation_submitted, $management_approved, $clearance_in_progress, $exit_interview_completed, $final_settlement_completed, $document_generated,
        // $relieved, $rejected);


        if ($request->ajax()) {
            return response()->json([
                'sine' => $sine,
                'processedSalaries' => $processedSalaries,
                'processedCount' => $processedCount,
                'payrollPeriod' => $payrollPeriod,
                'netPayTotal' => $netPayTotal,
                'allEmployeeCount' => $allEmployeeCount,
                'attendance' => $attendance,

                'full_day_present' => $full_day_present,
                'absent' => $absent,
                'late_coming' => $late_coming,
                'half_day' => $half_day,
                'leave_count' => $leave_count,

                'branch_labels' => $labels,
                'branch_data' => $data,

                'upcoming_holiday' => $upcoming_holiday,
                'upcoming_birthday' => $upcoming_birthday,
            ]);
        }

        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['lvr_b_id', $user->emp_b_id]
                ],
                // [
                //     'method' => 'whereDate',
                //     'args' => ['lvr_date', $dateFilter] // Ensure daily data retrieval
                // ],
                [
                    'method' => 'whereNull',
                    'args' => ['lvr_p_id']
                ],
                [
                    'method' => 'where',
                    'args' => ['lvr_status', 140]
                ],
                [
                    'method' => 'select',
                    'args' => ['lvr_id', 'lvr_b_id', 'lvr_emp_id', 'lvr_pl_id', 'lvr_start_date', 'lvr_end_date', 'lvr_cat_type_id', 'lvr_reason', 'lvr_total_leave_days', 'lvr_day_segment_id', 'lvr_documents', 'lvr_approved_by', 'lvr_am_id', 'lvr_status', 'lvr_module_id', 'lvr_status', 'lvr_stage_completed', 'lvr_leave_day_type_id', 'lvr_next_approver', 'updated_at', 'created_at'],
                    'relation' => ['fh_employee:emp_id,emp_b_id,emp_full_name,emp_code,emp_br_id,emp_dg_id,emp_d_id', 'fh_approval_status:m_id,m_name,m_other', 'fh_leave_cat_type:m_id,m_name,m_other', 'fh_leave_day_segment:m_id,m_name,m_other', 'fh_leave_day_type:m_id,m_name,m_other']
                ],
                [
                    'method' => 'orderBy',
                    'args' => ['lvr_start_date', 'desc'], // Latest start date first
                ],
                [
                    'method' => 'orderBy',
                    'args' => ['lvr_end_date', 'desc'], // Latest end date first
                ],
                [
                    'method' => 'orderBy',
                    'args' => ['lvr_status', 'desc'], // Latest approval status first
                ],
                [
                    'method' => 'orderBy',
                    'args' => ['created_at', 'desc'], // Latest created at first
                ],

            ];

            if ($branchFilter != '') {
                $dynamicConditions[] = [
                    'method' => 'whereRelation',
                    'parentMethod' => 'whereHas',
                    'childMethod' => 'where',
                    'args' => ['emp_br_id', $branchFilter],
                    'relation' => 'fh_employee'
                ];
            }

            if ($designationFilter != '') {
                $dynamicConditions[] = [
                    'method' => 'whereRelation',
                    'parentMethod' => 'whereHas',
                    'childMethod' => 'where',
                    'args' => ['emp_dg_id', $designationFilter],
                    'relation' => 'fh_employee'
                ];
            }

            if ($departmentFilter != '') {
                $dynamicConditions[] = [
                    'method' => 'whereRelation',
                    'parentMethod' => 'whereHas',
                    'childMethod' => 'where',
                    'args' => ['emp_d_id', $departmentFilter],
                    'relation' => 'fh_employee'
                ];
            }

            if (!empty($toDateFilter)) {
                $dynamicConditions[] = [
                    'method' => 'whereRelation',
                    'args' => ['ae_date', $toDateFilter]
                ];
            }

            // Additional filter logic...
            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new LeaveRequest(),
                dynamicConditions: $dynamicConditions,
                searchColumns: ['lvr_id', 'lvr_b_id', 'lvr_emp_id', 'lvr_pl_id', 'lvr_start_date', 'lvr_end_date', 'lvr_cat_type_id', 'lvr_reason', 'lvr_total_leave_days', 'lvr_day_segment_id', 'lvr_documents', 'lvr_approved_by', 'lvr_am_id', 'lvr_status', 'lvr_module_id', 'lvr_status', 'lvr_stage_completed', 'updated_at', 'created_at'],
                searchRelationships: [
                    'fh_employee' => ['emp_fname', 'emp_mname', 'emp_lname', 'emp_code'],
                    // 'fh_policy_shift_timing' => ['pst_name'],
                ]
            ))->getServerSideDataTable();

            $rowData = [];
            $i = 1;
            foreach ($list as $key => $val) {
                $data = $val->where('lvr_p_id', $val->lvr_p_id ?? $val->lvr_id)->orWhere('lvr_id', $val->lvr_p_id ?? $val->lvr_id)->get();
                $leaveCategoryBreakdown = $data->groupBy('lvr_cat_type_id')->map(function ($group) {
                    return optional($group->first()->fh_leave_cat_type)->m_name; // Fetch category name only
                })->values();
                $total_leave = $data->sum('lvr_total_leave_days');
                $startDate = $data->min('lvr_start_date');
                $endDate = $data->max('lvr_end_date');

                $nextApproval = ApprovalHelper::getNextApprovalDetails(250, $val->lvr_id, $val->lvr_b_id);
                $approval = ApprovalHelper::checkApproval($user->emp_b_id, $val->lvr_module_id, $val->lvr_id, optional($val->fh_employee)->emp_d_id, $val->lvr_emp_id);
                $row = [];
                $row[] = $i++;
                $row[] = optional($val->fh_employee)->emp_code . ' - ' . optional($val->fh_employee)->emp_full_name;
                $row[] = Carbon::parse($val->created_at)->format('d-M-Y');
                $row[] = $leaveCategoryBreakdown->isNotEmpty() ? $leaveCategoryBreakdown->implode(', ') : ($val->lvr_cat_type_id
                    ? optional($val->fh_leave_cat_type)->m_name : '--');
                $row[] = !empty($val->lvr_leave_day_type_id) ? $val->fh_leave_day_type->m_name : '--';
                $row[] = !empty($startDate) ? Carbon::parse($startDate)->format('d-M-Y') : '--';
                $row[] = !empty($endDate) ? Carbon::parse($endDate)->format('d-M-Y') : '--';
                $row[] = $total_leave > 0 ? $total_leave : ($val->lvr_total_leave_days ?? '--');

                $logData = $val->fh_plan_approval_log;
                $formattedLog = '';
                $comma = false;
                if ($logData && count($logData)) {
                    foreach ($logData as $log) {
                        if ($log->log_am_id == $val->lvr_am_id) {
                            // Escape each piece of data for safe HTML output
                            $employeeName = isset($log->fh_employee->emp_full_name) ? htmlspecialchars($log->fh_employee->emp_full_name, ENT_QUOTES, 'UTF-8') : 'N/A';
                            $roleName = isset($log->fh_role->role_name) ? htmlspecialchars($log->fh_role->role_name, ENT_QUOTES, 'UTF-8') : 'N/A';
                            $statusName = isset($log->fh_status->m_name) ? htmlspecialchars($log->fh_status->m_name, ENT_QUOTES, 'UTF-8') : 'N/A';

                            // Add each log entry as a list item
                            $formattedLog .= ($comma ? ', ' : '') .  $employeeName . ' (' . $roleName . ') ' . $statusName;
                            $comma = true;
                        }
                    }
                } else {
                    $logData2 = $val->fh_approval_log2;
                    if ($logData2 && count($logData2)) {
                        foreach ($logData2 as $log) {
                            $employeeName = isset($log->fh_employee->emp_full_name) ? htmlspecialchars($log->fh_employee->emp_full_name, ENT_QUOTES, 'UTF-8') : 'N/A';
                            $roleName = isset($log->fh_role->role_name) ? htmlspecialchars($log->fh_role->role_name, ENT_QUOTES, 'UTF-8') : 'N/A';
                            $statusName = isset($log->fh_status->m_name) ? htmlspecialchars($log->fh_status->m_name, ENT_QUOTES, 'UTF-8') : 'N/A';
                            // Add each log entry as a list item
                            $formattedLog .= ($comma ? ', ' : '') .  $employeeName . ' (' . $roleName . ') ' . $statusName;
                            $comma = true;
                        }
                    }
                    // $formattedLog = '<p>No logs available.</p>'; // Show a message if there are no logs
                }

                // Use a default message if $formattedLog is empty
                $popoverContent = $formattedLog === '' ?  ($val->lvr_status == 171 ? 'Auto Approved' : 'Awaiting') : $formattedLog;

                // Ensure the content is properly escaped for use in data attributes
                $safePopoverContent = htmlspecialchars($popoverContent, ENT_QUOTES, 'UTF-8');
                $jsonData = optional($val->fh_approval_status)->m_other;
                // Decode the JSON to an associative array
                $is_auto_approval = false;
                $decodedData = json_decode($jsonData, true); // true for associative array
                // Now access the color value
                $color = $decodedData['color'] ?? '';
                $icon = $decodedData['web_icon'] ?? '';
                $row[] = '<span class="badge" style="background-color:' . $color . '"><i class="' . $icon . '">&nbsp;</i>' . (isset($val->fh_approval_status->m_name) ? ($val->fh_approval_status->m_name) : '') . '</span> &nbsp;<i class="feather feather-info text-primary fs-14"
                data-bs-container="body"
                data-bs-content="' . $safePopoverContent . '"
                               data-bs-placement="right"
                               data-bs-popover-color="default"
                               data-bs-toggle="popover"
                               title="Approval Log">
                               </i>'
                    // Show approval count only for statuses other than 140
                    . (!$is_auto_approval ? '<p class="text-muted mb-0 fs-12">Approval ' . ($approval['approvallog_count'] ?? ' ') . ' (' . ($approval['approvalcount'] ?? ' ') . ')</p>' : '');
                if (!(isset($nextApproval['approver_name']) && $nextApproval['approver_name'])) {
                    $approvalMapping = ApprovalHelper::getApprovalMapping($user->emp_b_id, $val->lvr_emp_id, $val->lvr_module_id);

                    if ($approvalMapping) {
                        $approvalArray = ApprovalHelper::getApprovalArray($approvalMapping);
                        $approvalLog = $val?->fh_approval_log2?->pluck('log_user_id')?->toArray() ?? [];
                        $diff = array_values(array_diff($approvalArray, $approvalLog));

                        if ($diff) {
                            $approverName = Employee::where('emp_id', $diff[0])->pluck('emp_full_name')->first() ??  '---';
                        } else {
                            $approverName = '---';
                        }
                    } else {
                        $approverName = '---';
                    }
                }

                $rowData[] = $row;
            }

            $output = [
                "draw" => intval($request->input('draw')),
                "recordsTotal" => sizeof($list),
                "recordsFiltered" => (new DynamicModelDataTableHelper(
                    eloquentModel: new LeaveRequest(),
                    dynamicConditions: $dynamicConditions
                ))->countFilteredServerSideDataTable(),
                "data" => $rowData,
            ];

            return json_encode($output);
        }

        // View
        $columns = [
            'S. No.',
            'Employee Name',
            'Applied Date',
            'Leave Category',
            'Leave Type',
            'From Date',
            'To Date',
            'Days',
            'Status',
        ];

        return view('admin.dashboard.attendance-dashboard', compact(
            'netPayTotal',
            'allEmployeeCount',
            'absent',
            'full_day_present',
            'columns',
            'upcoming_holiday',
            'upcoming_birthday',
            'gatepass_request',
            'mispunch_request',
            'leave',
            'late_coming',
            'half_day',
            'leave_count',
            'sine',
            'labels',
            'total',
            'active',
            'inactive',
            'processedSalaries',
            'processedCount',
            'payrollPeriod',
            'moduleWiseEmployees',

            // 'total_exits',
            // 'clearance_in_progress',
            // 'resignation_submitted',
            // 'relieved',
        ));


    }

    /**
     * Show the form for creating a new resource.
     */
    public function get()
    {
        return view('admin.dashboard.invoice');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
