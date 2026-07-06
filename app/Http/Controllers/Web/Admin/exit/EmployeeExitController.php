<?php

namespace App\Http\Controllers\Web\Admin\exit;

use App\Helpers\ApprovalHelper;
use App\Helpers\CentralLogics;
use App\Http\Controllers\Controller;
use App\Models\AdhocTransaction;
use App\Models\ApprovalFlow;
use App\Models\ApprovalLog;
use App\Models\ApprovalModule;
use App\Models\Asset;
use App\Models\Business;
use App\Models\Employee;
use App\Models\EmployeeApprovalMapping;
use App\Models\EmployeeExitChecks;
use App\Models\EmployeeExitContact;
use App\Models\EmployeeExitNotes;
use App\Models\EmployeeExitRequest;
use App\Models\FamilyDetail;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\LoanRequest;
use App\Models\MailTemplate;
use App\Models\MasterTable;
use App\Models\PayrollPeriod;
use App\Models\PolicyHolidayList;
use App\Models\ProcessedEmployeeSalary;
use App\Models\SalaryMasterHistory;
use App\Models\TadaClaim;
use App\Models\UniformItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use ZipArchive;
use Illuminate\Support\Facades\File;

class EmployeeExitController extends Controller
{

    public function index(Request $request)
    {
        $user       = Auth::user();
        $businessId = $user->emp_b_id;
        $employeeFilter   = $request->input('employeeFilter');
        $statusFilter     = $request->input('statusFilter');
        $exitTypeFilter   = $request->input('exitTypeFilter');
        $fromToDateFilter = $request->input('fromDate');

        if ($request->ajax()) {
            // 🔹 Dynamic conditions
            $dynamicConditions = [
                ['method' => 'where', 'args' => ['er_b_id', $businessId]],
                [
                    'method' => 'select',
                    'args' => [
                        'er_id',
                        'er_emp_id',
                        'er_exit_type_id',
                        'er_resignation_date',
                        'er_last_working_day',
                        'er_notice_period_days',
                        'er_manager_status',
                        'er_hr_status',
                        'er_overall_status',
                        'er_remark',
                        'created_at',
                        'updated_at'

                    ],
                    'relation' => []
                ],
                [
                    'method' => 'with',
                    'args' => [[
                        'employee:emp_id,emp_code,emp_full_name,emp_d_id,emp_dg_id,emp_date_of_joining,emp_notice_period_req_days,emp_notice_period_serve_days,emp_separation_submit_date',
                        'employee.fh_designation:dg_id,dg_name',
                        'employee.fh_department:d_id,d_name',
                        'exitType:m_id,m_name',
                    ]]
                ]
            ];

            // 🔹 Optional filters
            if (!empty($employeeFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['er_emp_id', $employeeFilter]];
            }

            if (!empty($statusFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['er_overall_status', $statusFilter]];
            }

            if (!empty($exitTypeFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['er_exit_type_id', $exitTypeFilter]];
            }

            if (!empty($fromToDateFilter)) {
                $dates = explode(' - ', $fromToDateFilter);
                if (count($dates) === 2) {
                    $startDate = \Carbon\Carbon::createFromFormat('M d, Y', trim($dates[0]))->startOfDay();
                    $endDate   = \Carbon\Carbon::createFromFormat('M d, Y', trim($dates[1]))->endOfDay();
                    $dynamicConditions[] = ['method' => 'whereBetween', 'args' => ['er_resignation_date', [$startDate, $endDate]]];
                }
            }

            $searchColumns = ['er_overall_status'];
            $searchRelationships = [
                'employee' => ['emp_full_name', 'emp_code'],
                'employee.fh_designation' => ['dg_name'],
                'employee.fh_department' => ['d_name'],
            ];

            $helper = new DynamicModelDataTableHelper(
                eloquentModel: new EmployeeExitRequest,
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
                searchRelationships: $searchRelationships
            );

            $list = $helper->getServerSideDataTable();

            // 🔹 Prepare row data
            $rowData = [];
            foreach ($list as $index => $exit) {
                $row = [];
                $row[] = $index + 1;
                $row[] = e($exit->employee->emp_code ?? 'N/A');
                $row[] = e($exit->employee->emp_full_name ?? 'Unassigned');
                $row[] = e($exit->employee->fh_designation->dg_name ?? 'Unassigned');
                $row[] = e($exit->employee->fh_department->d_name ?? 'Unassigned');
                $row[] = $exit->employee->emp_date_of_joining
                    ? \Carbon\Carbon::parse($exit->employee->emp_date_of_joining)->format('j M Y')
                    : 'N/A';
                $row[] = $exit->er_resignation_date
                    ? \Carbon\Carbon::parse($exit->er_resignation_date)->format('j M Y')
                    : 'N/A';
                $row[] = $exit->er_last_working_day
                    ? \Carbon\Carbon::parse($exit->er_last_working_day)->format('j M Y')
                    : 'N/A';
                $row[] = e($exit->exitType->m_name ?? '-');
                $statusClasses = [
                    'RESIGNATION_SUBMITTED'      => 'badge bg-warning text-dark',
                    'MANAGER_APPROVED'           => 'badge bg-info',
                    'MANAGER_REJECTED'           => 'badge bg-danger',
                    'HR_APPROVED'                => 'badge bg-primary',
                    'HR_REJECTED'                => 'badge bg-danger',
                    'CLEARANCE_IN_PROGRESS'      => 'badge bg-warning text-dark',
                    'DOCUMENTS_AND_RELIEVING'    => 'badge bg-dark',
                    'RELIEVED'                   => 'badge bg-success',
                ];

                $status = $exit->er_overall_status ?? 'N/A';
                $badgeClass = $statusClasses[$status] ?? 'badge bg-light text-dark';
                $statusHtml = '<span class="' . $badgeClass . '">' . str_replace('_', ' ', $status) . '</span>';
                $row[] = $statusHtml;

                $encryptedId = Crypt::encrypt($exit->er_id);

                $actions = '
                <div class="btn-list ms-3 d-flex align-items-center">
                    <div class="dropdown">
                        <a href="javascript:void(0);" 
                        class="btn btn-sm btn-link text-muted" 
                        data-bs-toggle="dropdown">
                            <i class="fa fa-ellipsis-v"></i>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="' . route('exit.view', ['id' => $encryptedId]) . '">
                                    <i class="fa fa-eye me-2"></i> View
                                </a>
                            </li>';

                // ✅ Only add Revert if status is RELIEVED
                if ($exit->er_overall_status === 'RELIEVED') {
                    $actions .= '
                            <li>
                                <a href="javascript:void(0);" 
                                class="dropdown-item text-danger"
                                data-bs-toggle="modal"
                                data-bs-target="#revertModal"
                                data-id="' . $encryptedId . '">
                                    <i class="fa fa-undo me-2"></i> Revert
                                </a>
                            </li>';
                }

                $actions .= '
                        </ul>
                    </div>
                </div>
                ';
                $row[] = $actions;
                $rowData[] = $row;
            }

            return response()->json([
                'draw'            => $request->input('draw'),
                'recordsTotal'    => EmployeeExitRequest::where('er_b_id', $businessId)->count(),
                'recordsFiltered' => $helper->countFilteredServerSideDataTable(),
                'data'            => $rowData,
            ]);
        }

        $columns = [
            ['name' => 'S.No.',          'width' => '5%'],
            ['name' => 'Emp. Code',          'width' => '10%'],
            ['name' => 'Emp. Name',          'width' => '15%'],
            ['name' => 'Designation',         'width' => '15%'],
            ['name' => 'Department',          'width' => '15%'],
            ['name' => 'DOJ',           'width' => '10%'],
            ['name' => 'Resign Date',   'width' => '10%'],
            ['name' => 'LWD',           'width' => '10%'],
            ['name' => 'Reason',        'width' => '10%'],
            ['name' => 'Status',        'width' => '10%'],
            ['name' => 'Action',        'width' => '10%'],


        ];

        $exit_resone = MasterTable::where('m_group', 'Leaving')->get();
        $employee_type = Employee::where('emp_b_id', $businessId)
            ->whereNotNull('emp_separation_submit_date')
            ->select(['emp_id', 'emp_full_name', 'emp_code'])
            ->get();

        $statusMapping = [
            'RESIGNATION_SUBMITTED'      => 1,
            'MANAGER_APPROVED'           => 2,
            'HR_APPROVED'                => 3,
            'CLEARANCE_IN_PROGRESS'      => 4,
            'DOCUMENTS_AND_RELIEVING'    => 5,
            'RELIEVED'                   => 6,
        ];

        $totalExits            = EmployeeExitRequest::where('er_b_id', $businessId)->count();
        $resignationSubmitted  = EmployeeExitRequest::where('er_b_id', $businessId)->where('er_overall_status', 'RESIGNATION_SUBMITTED')->count();
        $managerApproved       = EmployeeExitRequest::where('er_b_id', $businessId)->where('er_overall_status', 'MANAGER_APPROVED')->count();
        $hrApproved            = EmployeeExitRequest::where('er_b_id', $businessId)->where('er_overall_status', 'HR_APPROVED')->count();
        $clearanceInProgress   = EmployeeExitRequest::where('er_b_id', $businessId)->where('er_overall_status', 'CLEARANCE_IN_PROGRESS')->count();
        $documentsRelieving    = EmployeeExitRequest::where('er_b_id', $businessId)->where('er_overall_status', 'DOCUMENTS_AND_RELIEVING')->count();
        $relieved              = EmployeeExitRequest::where('er_b_id', $businessId)->where('er_overall_status', 'RELIEVED')->count();

        return view('admin.exit.index', compact(
            'columns',
            'exit_resone',
            'employee_type',
            'statusMapping',
            'totalExits',
            'resignationSubmitted',
            'managerApproved',
            'hrApproved',
            'clearanceInProgress',
            'documentsRelieving',
            'relieved'
        ));
    }

    public function approve(Request $request)
    {
        $user = Auth::user();

        $data = EmployeeExitRequest::findOrFail($request->log_request_id);

        // Process approval using the service
        $response = ApprovalHelper::processApproval($request, $data, $user, 'er_');
        return response()->json($response, $response['status'] === 'success' ? 200 : 500);
    }

    public function view($id)
    {
        try {
            $user = Auth::user();
            $businessId = $user->emp_b_id;

            // Decrypt ID safely
            $decryptedId = Crypt::decrypt($id);

            // Fetch Exit Request
            $exit = EmployeeExitRequest::with([
                'employee:emp_id,emp_code,emp_full_name,emp_d_id,emp_dg_id,emp_date_of_joining,emp_notice_period_req_days,emp_notice_period_serve_days,emp_separation_submit_date',
                'employee.fh_designation:dg_id,dg_name',
                'employee.fh_department:d_id,d_name',
                'exitType:m_id,m_name',
            ])
                ->where('er_b_id', $businessId)
                ->where('er_id', $decryptedId)
                ->firstOrFail();

            // Status Classes
            $statusClasses = [
                'RESIGNATION_SUBMITTED' => 'badge bg-warning text-dark',
                'MANAGER_APPROVED' => 'badge bg-info',
                'MANAGER_REJECTED' => 'badge bg-danger',
                'HR_APPROVED' => 'badge bg-primary',
                'HR_REJECTED' => 'badge bg-danger',
                'CLEARANCE_IN_PROGRESS' => 'badge bg-warning text-dark',
                'DOCUMENTS_AND_RELIEVING' => 'badge bg-dark',
                'RELIEVED' => 'badge bg-success',
            ];

            // Status Labels
            $statusLabels = [
                'RESIGNATION_SUBMITTED' => 'Submitted',
                'MANAGER_APPROVED' => 'Approved',
                'MANAGER_REJECTED' => 'Rejected',
                'HR_APPROVED' => 'HR Approved',
                'HR_REJECTED' => 'HR Rejected',
                'CLEARANCE_IN_PROGRESS' => 'Clearance',
                'DOCUMENTS_AND_RELIEVING' => 'Docs & Relieving',
                'RELIEVED' => 'Relieved',
            ];

            $emp_checks_data = EmployeeExitChecks::where('b_id', $businessId)
                ->where('eec_er_id', $exit->er_id)
                ->first();

            $er_emp_id = $exit->er_emp_id;

            // Employee
            $employee = Employee::with('fh_policy_leave')->where('emp_b_id', $businessId)
                ->where('emp_id', $er_emp_id)
                ->firstOrFail();

            $fromDate = $employee->emp_separation_submit_date
                ? Carbon::parse($employee->emp_separation_submit_date)
                : Carbon::now()->startOfMonth();

            $toDate = $employee->emp_last_working_date
                ? Carbon::parse($employee->emp_last_working_date)
                : Carbon::now()->endOfMonth();

            // WeekOff + Attendance
            $weekOffDates = CentralLogics::getWeekOffDatesInRange($employee, $fromDate, $toDate);

            $holiday_record_exits = PolicyHolidayList::where('phl_b_id', $employee->emp_b_id)
                ->where('phl_type_id', 201)
                ->where(function ($q) use ($fromDate, $toDate) {
                    $q->whereBetween('phl_start_date', [$fromDate, $toDate])
                        ->orWhereBetween('phl_end_date', [$fromDate, $toDate]);
                })
                ->get();

            $weekOffDates = CentralLogics::getAttendanceDetailsForRange(
                $employee,
                $fromDate->toDateString(),
                $toDate->toDateString(),
                $holiday_record_exits,
                $weekOffDates
            );

            $totalSalariedDays = collect($weekOffDates)->sum(fn($item) => $item['totalSalariedDays'] ?? 0);

            // Loan
            $loanandadvance = LoanRequest::with(['fh_payroll_loan_installments' => function ($q) use ($fromDate, $toDate) {
                $q->whereBetween('pli_due_date', [$fromDate, $toDate])
                    ->where('pli_status', 'pending');
            }])
                ->where('lnr_b_id', $businessId)
                ->where('lnr_emp_id', $er_emp_id)
                ->whereHas('fh_payroll_loan_installments', function ($q) use ($fromDate, $toDate) {
                    $q->whereBetween('pli_due_date', [$fromDate, $toDate])
                        ->where('pli_status', 'pending');
                })
                ->first();

            $totalLoanRecovery = $loanandadvance
                ? $loanandadvance->fh_payroll_loan_installments->sum('pli_rem_bal')
                : 0;

            // Assets
            $assets = Asset::with('assetType')
                ->where('assets_b_id', $businessId)
                ->where('employee_id', $er_emp_id)
                ->where('status', 'assigned')
                ->get();

            // Uniform
            $uniformItem = UniformItem::with(['fh_stock.kit'])
                ->where('uit_b_id', $businessId)
                ->where('uit_payable', 'yes')
                ->where('uit_emp_id', $er_emp_id)
                ->get();

            // Claims
            $payble_claims = TadaClaim::where('tc_b_id', $businessId)
                ->where('tc_emp_id', $er_emp_id)
                ->where('tc_next_approver', 1)
                ->where('tc_stage_completed', 1)
                ->where('tc_paid_status', 0)
                ->get();

            $pending_claims = TadaClaim::where('tc_b_id', $businessId)
                ->where('tc_emp_id', $er_emp_id)
                ->where('tc_paid_status', 0)
                ->get();

            // Salary
            $processedSalary = ProcessedEmployeeSalary::where('ps_b_id', $businessId)
                ->where('ps_emp_id', $er_emp_id)
                ->latest('ps_id')
                ->first();

            $payrollPeriod = PayrollPeriod::find($processedSalary?->ps_payroll_id);

            $payrollStartDate = $payrollPeriod ? Carbon::parse($payrollPeriod->pp_start_date) : null;
            $payrollEndDate   = $payrollPeriod ? Carbon::parse($payrollPeriod->pp_end_date) : null;

            // Adhoc
            $adhocTransactions = AdhocTransaction::with('transaction_details')
                ->where('at_b_id', $businessId)
                ->where('at_emp_id', $er_emp_id)
                ->where('at_pp_id', $payrollPeriod?->pp_id)
                ->get();

            $netAdhoc = $adhocTransactions->sum('at_e_amount') - $adhocTransactions->sum('at_d_amount');

            // Get active leave types for the business

            $encashableLeaveTypeIds = optional($employee->fh_policy_leave->fh_leave_type)
                ?->where('lvt_encashable', 1)
                ->pluck('lvt_cat_type_id')
                ->toArray() ?? [];

            // dd($encashableLeaveTypeIds);
            $ps_per_day_salary = $processedSalary->ps_per_day_salary ?? 0;

            $salary = $ps_per_day_salary * $totalSalariedDays ?? null;

            // dd($salary);

            $leaveTypeMap = [
                207 => 'CL',
                208 => 'SL',
            ];

            $activeLeaveTypes = LeaveBalance::query()
                ->where('lb_b_id', $businessId)
                ->where('lb_emp_id', $er_emp_id)
                ->where('lb_year', now()->year)
                ->where('lb_month', now()->month)
                ->when(!empty($encashableLeaveTypeIds), function ($q) use ($encashableLeaveTypeIds) {
                    $q->whereIn('lb_cat_type_id', $encashableLeaveTypeIds);
                })
                ->get()
                ->map(function ($item) use ($leaveTypeMap, $ps_per_day_salary) {

                    $balance = (float) $item->lb_balance_remaining_leave;

                    return [
                        'leave_type' => $leaveTypeMap[$item->lb_cat_type_id] ?? 'UNKNOWN',
                        'balance'    => $balance,
                        'encash_amount' => $balance * $ps_per_day_salary,
                    ];
                });
            // dd($activeLeaveTypes);


            $adhoc = $netAdhoc;
            $leaveEncashment = 0;
            $incentives = 0;

            // Contacts
            $managerStage = EmployeeExitContact::with('employee')->where('ee_b_id', $businessId)->where('ee_role_id', 1)->first();
            $hrStage      = EmployeeExitContact::with('employee')->where('ee_b_id', $businessId)->where('ee_role_id', 2)->first();
            $adminStage   = EmployeeExitContact::with('employee')->where('ee_b_id', $businessId)->where('ee_role_id', 3)->first();
            $financeStage = EmployeeExitContact::with('employee')->where('ee_b_id', $businessId)->where('ee_role_id', 4)->first();

            // Master Data
            $names = ['Admin Review', 'Finance Review', 'HR Review', 'Manager Review'];

            $masterData = MasterTable::where('m_group', 'MODULE')
                ->where('m_type', 'FNF')
                ->whereIn('m_name', $names)
                ->pluck('m_id', 'm_name')
                ->toArray();

            $managerReviewId = $masterData['Manager Review'] ?? null;
            $hrReviewId      = $masterData['HR Review'] ?? null;
            $financeReviewId = $masterData['Finance Review'] ?? null;
            $adminReviewId   = $masterData['Admin Review'] ?? null;

            // Approval
            $approvalData = ApprovalHelper::getApprovalOrRejectionData(
                $exit->er_id,
                $exit->er_status,
                $exit->er_am_id,
                $user->emp_id,
                603
            );

            $approval = ApprovalHelper::getApprovalData($exit, $user, 'er_');

            $masterApproveBtn = $approval['masterApproveBtn'] ?? false;
            $canApprove       = $approval['canApprove'] ?? false;

            // Logs FIXED (typo removed $exist)
            $approvlLog1  = $exit->fh_approval_log_all_module ?? $exit->fh_approval_log1 ?? '';
            $approvlLogs2 = $exit->fh_approval_log_all_module ?? $exit->fh_approval_log2 ?? '';
            $approvlLogs3 = $exit->fh_approval_log_all_module ?? $exit->fh_approval_log3 ?? '';
            $approvlLogs4 = $exit->fh_approval_log_all_module ?? $exit->fh_approval_log4 ?? '';

            // FNF Modules
            $fnfStage = Business::where('b_id', $user->emp_b_id)->first();

            $moduleIds = is_array($fnfStage->b_fnf_modules)
                ? $fnfStage->b_fnf_modules
                : json_decode($fnfStage->b_fnf_modules ?? '[]', true);

            $fnfStages = MasterTable::whereIn('m_id', $moduleIds)->get();

            $signatures = EmployeeExitNotes::where('b_id', $businessId)->first();

            return view('admin.exit.view', compact(
                'exit',
                'emp_checks_data',
                'statusClasses',
                'fnfStages',
                'statusLabels',
                'assets',
                'uniformItem',
                'payble_claims',
                'pending_claims',
                'totalSalariedDays',
                'totalLoanRecovery',
                'salary',
                'adhoc',
                'incentives',
                'leaveEncashment',
                'managerStage',
                'hrStage',
                'adminStage',
                'financeStage',
                'approvalData',
                'canApprove',
                'approvlLog1',
                'approvlLogs2',
                'approvlLogs3',
                'approvlLogs4',
                'masterApproveBtn',
                'managerReviewId',
                'hrReviewId',
                'financeReviewId',
                'adminReviewId',
                'signatures',
                'activeLeaveTypes'
            ));
        } catch (\Throwable $e) {
            \Log::error('Exit View Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    public function managerApprove(Request $request, $id)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;

        // Fetch Exit Request
        $exit = EmployeeExitRequest::findOrFail($id);
        $er_emp_id = $exit->er_emp_id;


        // Get Employee Details
        $employee = Employee::with('fh_department', 'fh_designation', 'fh_business')
            ->where('emp_b_id', $businessId)
            ->where('emp_id', $er_emp_id)
            ->firstOrFail();

        // Get Manager Details
        $manager = Employee::with('fh_department', 'fh_designation')
            ->where('emp_b_id', $businessId)
            ->where('emp_id', $employee->emp_supervisor_id)
            ->first();



        // Get HR Details (single HR)
        // $hr = EmployeeExitContact::where('b_id', $businessId)
        //     ->where('name', 'HR Manager')
        //     ->select('name', 'email')
        //     ->first();



        // if (!$hr) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'HR Manager not found'
        //     ]);
        // }

        // Fetch the required template
        // $templates = MailTemplate::where('mt_b_id', $businessId)
        //     ->whereIn('mt_title', ['Manager Approved Resignation'])
        //     ->get()
        //     ->keyBy('mt_title');

        // if (!$templates->has('Manager Approved Resignation')) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'Email template not found'
        //     ]);
        // }

        // $template = $templates['Manager Approved Resignation'];

        // Prepare placeholders
        // $placeholders = [
        //     '[Employee Name]'     => $employee->emp_full_name,
        //     '[Emp Code]'          => $employee->emp_code,
        //     '[Company Name]'      => $employee->fh_business->b_name ?? '',
        //     '[HR Name]'           => $hr->name ?? '',
        //     '[HR Email]'          => $hr->email ?? '',
        //     '[Remark]'            => $exit->er_manager_remark ?? '',
        //     '[Current Date]'      => \Carbon\Carbon::now()->format('d-m-Y'),
        //     '[Submission Date]'   => $employee->emp_separation_submit_date? \Carbon\Carbon::parse($employee->emp_separation_submit_date)->format('d-m-Y'): 'N/A',
        //     '[Manager Name]'      => $manager->emp_full_name ?? 'Not Assigned',
        //     '[Designation]'       => $employee->fh_designation->dg_name ?? '',
        //     '[Department]'        => $employee->fh_department->d_name ?? '',
        //     '[Effective LWD]'     => $employee->emp_last_working_date? \Carbon\Carbon::parse($employee->emp_last_working_date)->format('d-m-Y'): 'Pending Approval',
        // ];


        // Replace placeholders in subject and body
        // $subject = str_replace(array_keys($placeholders), array_values($placeholders), $template->mt_title);
        // $body    = str_replace(array_keys($placeholders), array_values($placeholders), $template->mt_body);

        // Send email
        // try {
        //     Mail::html($body, function ($message) use ($employee, $subject) {
        //         $message->to($employee->emp_email)
        //             ->subject($subject);
        //     });

        //     Log::info('Manager approval email sent to: ' . $employee->emp_email);
        // } catch (\Exception $e) {
        //     Log::error('Failed to send manager approval email to ' . $employee->emp_email . ' - ' . $e->getMessage());
        // }

        $exit->er_manager_remark = $request->remark;
        $exit->er_manager_action_at = now();
        $exit->er_overall_status = 'MANAGER_APPROVED';
        $exit->save();

        return response()->json([
            'success' => true,
            'message' => 'Manager Approved successfully',
        ]);
    }

    public function managerReject(Request $request, $id)
    {

        $user = Auth::user();
        $businessId = $user->emp_b_id;

        // Fetch Exit Request
        $exit = EmployeeExitRequest::findOrFail($id);
        $er_emp_id = $exit->er_emp_id;

        // Get Employee Details
        $employee = Employee::with('fh_department', 'fh_designation', 'fh_business')
            ->where('emp_b_id', $businessId)
            ->where('emp_id', $er_emp_id)
            ->firstOrFail();

        // Get Manager Details
        $manager = Employee::with('fh_department', 'fh_designation')
            ->where('emp_b_id', $businessId)
            ->where('emp_id', $employee->emp_supervisor_id)
            ->first();


        // Get HR Details (single HR)
        $hr = EmployeeExitContact::where('b_id', $businessId)
            ->where('name', 'HR Manager')
            ->select('name', 'email')
            ->first();

        if (!$hr) {
            return response()->json([
                'status' => false,
                'message' => 'HR Manager not found'
            ]);
        }

        // Fetch the required template
        $templates = MailTemplate::where('mt_b_id', $businessId)
            ->whereIn('mt_title', ['Manager Rejected Resignation'])
            ->get()
            ->keyBy('mt_title');

        if (!$templates->has('Manager Rejected Resignation')) {
            return response()->json([
                'status' => false,
                'message' => 'Email template not found'
            ]);
        }

        $template = $templates['Manager Rejected Resignation'];

        // Prepare placeholders
        // $placeholders = [
        //     '[Employee Name]'     => $employee->emp_full_name,
        //     '[Emp Code]'          => $employee->emp_code,
        //     '[Company Name]'      => $employee->fh_business->b_name ?? '',
        //     '[HR Name]'           => $hr->name ?? '',
        //     '[HR Email]'          => $hr->email ?? '',
        //     '[Remark]'            => $exit->er_manager_remark ?? '',
        //     '[Current Date]'      => \Carbon\Carbon::now()->format('d-m-Y'),
        //     '[Submission Date]'   => $employee->emp_separation_submit_date? \Carbon\Carbon::parse($employee->emp_separation_submit_date)->format('d-m-Y'): 'N/A',
        //     '[Manager Name]'      => $manager->emp_full_name ?? 'Not Assigned',
        //     '[Designation]'       => $employee->fh_designation->dg_name ?? '',
        //     '[Department]'        => $employee->fh_department->d_name ?? '',
        //     '[Effective LWD]'     => $employee->emp_last_working_date? \Carbon\Carbon::parse($employee->emp_last_working_date)->format('d-m-Y'): 'Pending Approval',
        // ];


        // // Replace placeholders in subject and body
        // $subject = str_replace(array_keys($placeholders), array_values($placeholders), $template->mt_title);
        // $body    = str_replace(array_keys($placeholders), array_values($placeholders), $template->mt_body);

        // // Send email
        // try {
        //     Mail::html($body, function ($message) use ($employee, $subject) {
        //         $message->to($employee->emp_email)
        //             ->subject($subject);
        //     });

        //     Log::info('Manager approval email sent to: ' . $employee->emp_email);
        // } catch (\Exception $e) {
        //     Log::error('Failed to send manager approval email to ' . $employee->emp_email . ' - ' . $e->getMessage());
        // }


        $exit->er_manager_remark = $request->remark;
        $exit->er_manager_action_at = now();
        $exit->er_overall_status = 'MANAGER_REJECTED';
        $exit->save();

        return response()->json([
            'success' => true,
            'message' => 'Manager Rejected successfully',
        ]);
    }

    public function hrApprove(Request $request, $id)
    {

        $user = Auth::user();
        $businessId = $user->emp_b_id;

        // Fetch Exit Request
        $exit = EmployeeExitRequest::findOrFail($id);
        $er_emp_id = $exit->er_emp_id;

        // Get Employee Details
        $employee = Employee::with('fh_department', 'fh_designation', 'fh_business')
            ->where('emp_b_id', $businessId)
            ->where('emp_id', $er_emp_id)
            ->firstOrFail();

        // Get Manager Details
        $manager = Employee::with('fh_department', 'fh_designation')
            ->where('emp_b_id', $businessId)
            ->where('emp_id', $employee->emp_supervisor_id)
            ->first();


        // Get HR Details (single HR)
        $hr = EmployeeExitContact::where('b_id', $businessId)
            ->where('name', 'HR Manager')
            ->select('name', 'email')
            ->first();

        if (!$hr) {
            return response()->json([
                'status' => false,
                'message' => 'HR Manager not found'
            ]);
        }

        // Fetch the required template
        $templates = MailTemplate::where('mt_b_id', $businessId)
            ->whereIn('mt_title', ['HR Approved Resignation'])
            ->get()
            ->keyBy('mt_title');

        if (!$templates->has('HR Approved Resignation')) {
            return response()->json([
                'status' => false,
                'message' => 'Email template not found'
            ]);
        }

        $template = $templates['HR Approved Resignation'];

        // Prepare placeholders
        // $placeholders = [
        //     '[Employee Name]'     => $employee->emp_full_name,
        //     '[Emp Code]'          => $employee->emp_code,
        //     '[Company Name]'      => $employee->fh_business->b_name ?? '',
        //     '[HR Name]'           => $hr->name ?? '',
        //     '[HR Email]'          => $hr->email ?? '',
        //     '[Remark]'            => $exit->er_hr_remark ?? '',
        //     '[Current Date]'      => \Carbon\Carbon::now()->format('d-m-Y'),
        //     '[Submission Date]'   => $employee->emp_separation_submit_date? \Carbon\Carbon::parse($employee->emp_separation_submit_date)->format('d-m-Y'): 'N/A',
        //     '[Manager Name]'      => $manager->emp_full_name ?? 'Not Assigned',
        //     '[Designation]'       => $employee->fh_designation->dg_name ?? '',
        //     '[Department]'        => $employee->fh_department->d_name ?? '',
        //     '[Effective LWD]'     => $employee->emp_last_working_date? \Carbon\Carbon::parse($employee->emp_last_working_date)->format('d-m-Y'): 'Pending Approval',
        // ];


        // // Replace placeholders in subject and body
        // $subject = str_replace(array_keys($placeholders), array_values($placeholders), $template->mt_title);
        // $body    = str_replace(array_keys($placeholders), array_values($placeholders), $template->mt_body);

        // // Send email
        // try {
        //     Mail::html($body, function ($message) use ($employee, $subject) {
        //         $message->to($employee->emp_email)
        //             ->subject($subject);
        //     });

        //     Log::info('HR approval email sent to: ' . $employee->emp_email);
        // } catch (\Exception $e) {
        //     Log::error('Failed to send HR approval email to ' . $employee->emp_email . ' - ' . $e->getMessage());
        // }


        $exit->er_hr_remark = $request->remark;
        $exit->er_hr_action_at = now();
        $exit->er_overall_status = 'HR_APPROVED';
        $exit->save();

        return response()->json([
            'success' => true,
            'message' => 'HR Approved successfully',
        ]);
    }

    public function hrReject(Request $request, $id)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;

        // Fetch Exit Request
        $exit = EmployeeExitRequest::findOrFail($id);
        $er_emp_id = $exit->er_emp_id;

        // Get Employee Details
        $employee = Employee::with('fh_department', 'fh_designation', 'fh_business')
            ->where('emp_b_id', $businessId)
            ->where('emp_id', $er_emp_id)
            ->firstOrFail();

        // Get Manager Details
        $manager = Employee::with('fh_department', 'fh_designation')
            ->where('emp_b_id', $businessId)
            ->where('emp_id', $employee->emp_supervisor_id)
            ->first();


        // Get HR Details (single HR)
        $hr = EmployeeExitContact::where('b_id', $businessId)
            ->where('name', 'HR Manager')
            ->select('name', 'email')
            ->first();

        if (!$hr) {
            return response()->json([
                'status' => false,
                'message' => 'HR Manager not found'
            ]);
        }

        // Fetch the required template
        $templates = MailTemplate::where('mt_b_id', $businessId)
            ->whereIn('mt_title', ['HR Rejected Resignation'])
            ->get()
            ->keyBy('mt_title');

        if (!$templates->has('HR Rejected Resignation')) {
            return response()->json([
                'status' => false,
                'message' => 'Email template not found'
            ]);
        }

        $template = $templates['HR Rejected Resignation'];

        // Prepare placeholders
        // $placeholders = [
        //     '[Employee Name]'     => $employee->emp_full_name,
        //     '[Emp Code]'          => $employee->emp_code,
        //     '[Company Name]'      => $employee->fh_business->b_name ?? '',
        //     '[HR Name]'           => $hr->name ?? '',
        //     '[HR Email]'          => $hr->email ?? '',
        //     '[Remark]'            => $exit->er_hr_remark ?? '',
        //     '[Current Date]'      => \Carbon\Carbon::now()->format('d-m-Y'),
        //     '[Submission Date]'   => $employee->emp_separation_submit_date? \Carbon\Carbon::parse($employee->emp_separation_submit_date)->format('d-m-Y'): 'N/A',
        //     '[Manager Name]'      => $manager->emp_full_name ?? 'Not Assigned',
        //     '[Designation]'       => $employee->fh_designation->dg_name ?? '',
        //     '[Department]'        => $employee->fh_department->d_name ?? '',
        //     '[Effective LWD]'     => $employee->emp_last_working_date? \Carbon\Carbon::parse($employee->emp_last_working_date)->format('d-m-Y'): 'Pending Approval',
        // ];


        // // Replace placeholders in subject and body
        // $subject = str_replace(array_keys($placeholders), array_values($placeholders), $template->mt_title);
        // $body    = str_replace(array_keys($placeholders), array_values($placeholders), $template->mt_body);

        // // Send email
        // try {
        //     Mail::html($body, function ($message) use ($employee, $subject) {
        //         $message->to($employee->emp_email)
        //             ->subject($subject);
        //     });

        //     Log::info('HR approval email sent to: ' . $employee->emp_email);
        // } catch (\Exception $e) {
        //     Log::error('Failed to send HR approval email to ' . $employee->emp_email . ' - ' . $e->getMessage());
        // }


        $exit->er_hr_remark = $request->remark;
        $exit->er_hr_action_at = now();
        $exit->er_overall_status = 'HR_REJECTED';
        $exit->save();

        return response()->json([
            'success' => true,
            'message' => 'HR Rejected successfully',
        ]);
    }

    public function financeClearance(Request $request, $id)
    {
        $exit = EmployeeExitRequest::findOrFail($id);
        $exit->er_finance_remark = $request->remark;
        $exit->er_finance_action_at = now();
        $exit->er_overall_status = 'CLEARANCE_IN_PROGRESS';
        $exit->save();

        return response()->json([
            'success' => true,
            'message' => 'Finance clearance completed successfully',
        ]);
    }

    public function storeDocuments(Request $request, $id)
    {
        $exit = EmployeeExitRequest::findOrFail($id);
        $exit->er_finance_remark = $request->docNotes;
        $exit->er_overall_status = 'RELIEVED';
        $exit->save();

        return response()->json([
            'success' => true,
            'message' => 'Finance clearance completed successfully',
        ]);
    }

    public function relieveEmployee(Request $request, $id)
    {
        $exit = EmployeeExitRequest::findOrFail($id);
        $exit->er_overall_status = 'RELIEVED';
        $exit->er_last_working_day = now();
        $exit->save();

        return response()->json(['message' => 'Employee Relieved']);
    }

    public function update(Request $request, $id)
    {

        $user = Auth::user();
        $b_id = $user->emp_b_id;
        $request->validate([
            'role' => 'required|string|max:255',
            'email' => 'required|email|unique:contacts,email,' . $id,
        ]);

        $contact = EmployeeExitContact::find($id);
        if (! $contact) {
            $contact = EmployeeExitContact::create([
                'id' => $id,
                'role' => $request->role,
                'email' => $request->email,
                'b_id' => $b_id ?? 1,
                'name' => $request->role,
            ]);
        } else {
            $contact->role = $request->role;
            $contact->email = $request->email;
            $contact->b_id = $request->b_id ?? $contact->b_id;
            $contact->save();
        }

        return response()->json([
            'message' => 'Contact updated successfully',
            'contact' => $contact,
        ]);
    }

    public function saveNotes(Request $request)
    {
        $user = Auth::user();
        $b_id = $user->emp_b_id;

        $request->validate([
            'notes' => 'required|string|max:5000',
        ]);

        $exit = EmployeeExitNotes::updateOrCreate(
            [
                'b_id' => $b_id,
            ],
            [
                'notes' => $request->notes,
            ]
        );

        return response()->json([
            'success' => true,
            'notes' => $exit->notes,
        ]);
    }

    public function generatePDF($id)
    {
        $exit = EmployeeExitRequest::with([
            'employee.fh_department',
            'employee.fh_designation',
            'exitType'
        ])->findOrFail($id);

        // ===== Earnings =====
        $salary = $exit->salary ?? 0;
        $leaveEncashment = $exit->leave_encashment ?? 0;
        $incentives = $exit->incentives ?? 0;
        $payble_claims = $exit->claims ?? collect();

        // ===== Deductions =====
        $totalLoanRecovery = $exit->loan_recovery ?? 0;
        $adhoc = $exit->adhoc ?? 0;

        $totalPayable =
            $salary + $leaveEncashment + $incentives +
            $payble_claims->sum('tc_claimed_amount');

        $totalDeductions =
            $totalLoanRecovery + $adhoc;

        $netPay = $totalPayable - $totalDeductions;

        $pdf = Pdf::loadView('admin.exit.doc.full_final', compact(
            'exit',
            'salary',
            'leaveEncashment',
            'incentives',
            'payble_claims',
            'totalLoanRecovery',
            'adhoc',
            'totalPayable',
            'totalDeductions',
            'netPay'
        ));

        return $pdf->download('Full_and_Final_' . $exit->employee->emp_code . '.pdf');
        // return view('admin.exit.doc.full_final', compact('exit','salary','leaveEncashment','incentives','payble_claims','totalLoanRecovery','adhoc','totalPayable','totalDeductions','netPay'));
    }

    public function generatePDFtow($id, $type, $download = false)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;
        try {
            $id = Crypt::decrypt($id); // decrypt the ID
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404); // invalid ID
        }
        // dd($id, $type);

        $signatures = EmployeeExitNotes::where('b_id', $businessId)->first();
        // dd($signatures);

        if (!$signatures) {
            return back()->with('error', 'Signature not uploaded. Please upload your signature first.');
        }

        $exit = EmployeeExitRequest::with([
            'employee.fh_department',
            'employee.fh_designation',
            'employee.fh_employee_title',
            'employee.fh_business',
            'exitType'
        ])->findOrFail($id);

        // dd($exit);


        $emp_id = $exit->er_emp_id;

        $family_details = FamilyDetail::where('fd_b_id', $businessId)->where('fd_emp_id', $emp_id)->first();
        $business_data = Employee::where('emp_b_id', $businessId)->where('emp_role_id', 1)->first();


        $salary_data = SalaryMasterHistory::where('sm_emp_b_id', $businessId)
            ->where('sm_emp_id', $emp_id)
            ->orderBy('created_at', 'desc') // latest first
            ->take(2)
            ->get(['sm_annual_ctc', 'created_at']); // select only amount and date


        $latest_salary = $salary_data->first();
        $previous_salary = $salary_data->count() > 1 ? $salary_data->last() : null;

        // dd($latest_salary ,$previous_salary);



        // ===== Earnings =====
        $salary = $exit->salary ?? 0;
        $leaveEncashment = $exit->leave_encashment ?? 0;
        $incentives = $exit->incentives ?? 0;
        $payble_claims = $exit->claims ?? collect();

        // ===== Deductions =====
        $totalLoanRecovery = $exit->loan_recovery ?? 0;
        $adhoc = $exit->adhoc ?? 0;

        $totalPayable =
            $salary + $leaveEncashment + $incentives +
            $payble_claims->sum('tc_claimed_amount');

        $totalDeductions =
            $totalLoanRecovery + $adhoc;

        $netPay = $totalPayable - $totalDeductions;

        // ===== Determine the template based on type =====
        $templates = [
            'relieving'   => 'admin.exit.doc.relieving',
            'experience'  => 'admin.exit.doc.experience',
            'noc'         => 'admin.exit.doc.noc',
            'nodues'      => 'admin.exit.doc.nodues',
            'service'     => 'admin.exit.doc.service',
            'full_final'  => 'admin.exit.doc.full_final',
            'salary_revision'  => 'admin.exit.doc.salary_revision',
        ];

        if (!isset($templates[$type])) {
            abort(404, 'Invalid document type');
        }

        // Make sure $exit is defined
        if (!$exit) {
            throw new \Exception("Exit record not found.");
        }

        $er_emp_id = $exit->er_emp_id;

        // Get employee
        $employee = Employee::where('emp_b_id', $businessId)->where('emp_id', $er_emp_id)->first();

        // dd($employee);

        // Set from and to dates
        $fromDate = $employee->emp_final_settlement_date
            ? Carbon::parse($employee->emp_final_settlement_date)
            : Carbon::now()->startOfMonth();

        // dd($fromDate);


        $toDate = $employee->emp_last_working_date
            ? Carbon::parse($employee->emp_last_working_date)
            : Carbon::now()->endOfMonth();

        // dd($toDate);

        // Get week off dates
        $weekOffDates = CentralLogics::getWeekOffDatesInRange($employee, $fromDate, $toDate);

        // dd($weekOffDates);


        // Get holidays for the employee's business
        $holiday_record_exits = PolicyHolidayList::where('phl_b_id', $employee->emp_b_id)
            ->where('phl_type_id', 201)
            ->where(function ($q) use ($fromDate, $toDate) {
                $q->whereBetween('phl_start_date', [$fromDate, $toDate])
                    ->orWhereBetween('phl_end_date', [$fromDate, $toDate]);
            })
            ->get();




        // Get attendance details
        $weekOffDates = CentralLogics::getAttendanceDetailsForRange($employee, $fromDate->toDateString(), $toDate->toDateString(), $holiday_record_exits, $weekOffDates);
        $totalSalariedDaysArray = array_map(function ($item) {
            return $item['totalSalariedDays'] ?? 0;
        }, $weekOffDates);



        $totalSalariedDays = array_sum($totalSalariedDaysArray);

        // dd($totalSalariedDays);

        // $loanandadvance = LoanRequest::with(['fh_payroll_loan_installments' => function ($query) use ($fromDate, $toDate) {
        //     $query->whereBetween('pli_due_date', [$fromDate, $toDate]);
        // }])
        //     ->where('lnr_b_id', $businessId)
        //     ->where('lnr_emp_id', $er_emp_id)
        //     ->whereHas('fh_payroll_loan_installments', function ($query) use ($fromDate, $toDate) {
        //         $query->whereBetween('pli_due_date', [$fromDate, $toDate]);
        //     })
        //     ->first();

        $loanandadvance = LoanRequest::with(['fh_payroll_loan_installments' => function ($query) use ($fromDate, $toDate) {
            $query->whereBetween('pli_due_date', [$fromDate, $toDate])
                ->where('pli_status', 'pending'); // 👈 EMI status filter
        }])
            ->where('lnr_b_id', $businessId)
            ->where('lnr_emp_id', $er_emp_id)
            ->whereHas('fh_payroll_loan_installments', function ($query) use ($fromDate, $toDate) {
                $query->whereBetween('pli_due_date', [$fromDate, $toDate])
                    ->where('pli_status', 'pending'); // 👈 same condition here
            })
            ->first();


        $totalLoanRecovery = 0;

        if ($loanandadvance) {
            $totalLoanRecovery = $loanandadvance->fh_payroll_loan_installments->sum('pli_rem_bal');
            // dd($totalLoanRecovery);
        }


        $assets = Asset::with('assetType')->where('assets_b_id', $businessId)
            ->where('employee_id', $er_emp_id)
            ->where('status', 'assigned')
            ->get();

        $uniformItem = UniformItem::with(['fh_stock', 'fh_stock.kit'])
            ->where('uit_b_id', $businessId)
            ->where('uit_payable', 'yes')
            ->where('uit_emp_id', $er_emp_id)
            ->get();

        $payble_claims = TadaClaim::where('tc_b_id', $businessId)
            ->where('tc_emp_id', $er_emp_id)
            ->where('tc_next_approver', 1)
            ->where('tc_stage_completed', 1)
            ->where('tc_paid_status', 0)
            ->get();

        $pending_claims = TadaClaim::where('tc_b_id', $businessId)
            ->where('tc_emp_id', $er_emp_id)
            ->where('tc_paid_status', 0)
            ->get();

        $processedSalary = ProcessedEmployeeSalary::where('ps_b_id', $businessId)
            ->where('ps_emp_id', $er_emp_id)
            ->latest('ps_id')
            ->first();



        if ($processedSalary) {
            $salary = $processedSalary->ps_monthly_net_salary;
        } else {
            $salary = null;
        }

        $payrollPeriod = PayrollPeriod::find($processedSalary?->ps_payroll_id);


        $payrollStartDate = Carbon::parse($payrollPeriod->pp_start_date ?? null);
        $payrollEndDate   = Carbon::parse($payrollPeriod->pp_end_date ?? null);

        $fnfStartDate = $employee->emp_final_settlement_date
            ? Carbon::parse($employee->emp_final_settlement_date)
            : $payrollStartDate;

        $fnfEndDate = $employee->emp_last_working_date
            ? Carbon::parse($employee->emp_last_working_date)
            : $payrollEndDate;

        $effectiveFromDate = $fnfStartDate->greaterThan($payrollStartDate)
            ? $fnfStartDate
            : $payrollStartDate;

        $effectiveToDate = $fnfEndDate->lessThan($payrollEndDate)
            ? $fnfEndDate
            : $payrollEndDate;


        $payrollPeriodId = $payrollPeriod->pp_id;

        $adhocTransactions = AdhocTransaction::with('transaction_details')
            ->where('at_b_id', $businessId)
            ->where('at_emp_id', $er_emp_id)
            ->where('at_pp_id', $payrollPeriodId)
            ->get();

        $totalAdhocEarning = $adhocTransactions->sum('at_e_amount');
        $totalAdhocDeduction = $adhocTransactions->sum('at_d_amount');

        $netAdhoc = $totalAdhocEarning - $totalAdhocDeduction;

        $signatures = EmployeeExitNotes::where('b_id', $businessId)->first();



        $adhoc = $netAdhoc;
        $leaveEncashment = 0;
        $incentives = 0;



        $pdf = Pdf::loadView($templates[$type], compact(
            'exit',
            'salary',
            'leaveEncashment',
            'incentives',
            'payble_claims',
            'totalLoanRecovery',
            'adhoc',
            'totalPayable',
            'totalDeductions',
            'netPay',
            'latest_salary',
            'previous_salary',
            'signatures',
            'totalSalariedDays',
            'family_details',
            'business_data'
        ));

        // You can return download OR stream
        return $pdf->stream(ucwords(str_replace('_', ' ', $type)) . '_' . $exit->employee->emp_code . '.pdf');
        return $pdf->download($type . '_' . $exit->employee->emp_code . '.pdf');

        return view($templates[$type], compact('exit', 'salary', 'leaveEncashment', 'incentives', 'payble_claims', 'totalLoanRecovery', 'adhoc', 'totalPayable', 'totalDeductions', 'netPay', 'latest_salary', 'previous_salary', 'signatures', 'family_details', 'business_data'));
    }

    public function employeeexitapprovalstore(Request $request)
    {
        $user = Auth::user();
        $b_id = $user->emp_b_id;

        // Validate input
        $validated = $request->validate([
            'stage_id'      => 'nullable|array',
            'stage_id.*'    => 'nullable|integer',
            'role_id'       => 'required|array',
            'role_id.*'     => 'required|integer',
            'employee_id'   => 'required|array',
            'employee_id.*' => 'required|integer',
        ]);

        // Delete any existing contacts for this business
        EmployeeExitContact::where('ee_b_id', $b_id)->delete();

        // Loop through submitted data
        foreach ($validated['role_id'] as $key => $roleId) {
            $empId   = $validated['employee_id'][$key] ?? null;
            $stageId = $validated['stage_id'][$key] ?? null;

            if ($empId) {
                // Check duplicates only for new entries
                $exists = EmployeeExitContact::where('ee_emp_id', $empId)
                    ->where('ee_role_id', $roleId)
                    ->where('ee_b_id', $b_id)
                    ->exists();

                if ($exists) {
                    return back()->with('error', 'This employee with this role already exists.');
                }

                if ($stageId) {
                    // Update existing record if stage_id is provided
                    EmployeeExitContact::updateOrCreate(
                        ['id' => $stageId],
                        [
                            'ee_emp_id'  => $empId,
                            'ee_role_id' => $roleId,
                            'ee_b_id'    => $b_id,
                        ]
                    );
                } else {
                    // Create new record
                    $contact = EmployeeExitContact::create([
                        'ee_emp_id'  => $empId,
                        'ee_role_id' => $roleId,
                        'ee_b_id'    => $b_id,
                    ]);

                    $contact->load('role', 'employee', 'business');

                    Log::info('Employee Exit Contact saved', [
                        'user_id'     => $user->id,
                        'user_name'   => $user->name ?? null,
                        'business_id' => $b_id,
                        'ee_emp_id'   => $empId,
                        'ee_role_id'  => $roleId,
                        'contact_id'  => $contact->id,
                        'timestamp'   => now()->toDateTimeString(),
                    ]);
                }
            }
        }

        return back()->with('success', 'Employee exit contacts saved successfully.');
    }


    public function fnf_seting_index(Request $request)
    {
        $user = Auth::user();
        $approvalFlowType = MasterTable::where('m_group', 'APPROVAL_FLOW_TYPE')->get();

        $fnfData = Business::where('b_id', $user->emp_b_id)->first();
        $bFnfModules = [];

        if (!empty($fnfData->b_fnf_modules)) {
            $bFnfModules = is_array($fnfData->b_fnf_modules)
                ? $fnfData->b_fnf_modules
                : json_decode($fnfData->b_fnf_modules, true);
        }
        if ($request->ajax()) {

            $dynamicConditions = [
                ['method' => 'where', 'args' => ['m_group', 'MODULE']],
                ['method' => 'where', 'args' => ['m_type', 'FNF']],
                ['method' => 'whereIn', 'args' => ['m_id', $bFnfModules]], // <-- filter only allowed modules
                [
                    'method' => 'select',
                    'args' => ['m_id', 'm_group', 'm_name', 'm_type', 'm_description', 'm_other'],
                    'relation' => ['fh_approval_modules:*', 'fh_approval_modules2:*']
                ],
                ['method' => 'sortBy', 'args' => ['m_id', 'm_group', 'm_name', 'm_type', 'm_description', 'm_other']]
            ];

            $searchColumns = ['m_id', 'm_group', 'm_name', 'm_type', 'm_description', 'm_other'];
            $searchRelationships = ['fh_approval_modules' => ['am_name']];

            $dynamicHelper = new DynamicModelDataTableHelper(
                eloquentModel: new MasterTable(),
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
                searchRelationships: $searchRelationships
            );

            $list = $dynamicHelper->getServerSideDataTable();

            // Total employees
            $totalEmployee = Employee::where('emp_b_id', $user->emp_b_id)
                ->where('emp_role_id', '<>', 1)
                ->where('emp_status', 71)
                ->get();

            $rowData = [];
            $i = 0;

            foreach ($list as $val) {
                $i++;
                $row = [];
                $row[] = $i;

                // Module Name link
                $row[] = '<a href="' . route('approval.process.details', Crypt::encryptString($val->m_id)) . '" class="text-primary">'
                    . $val->m_name . '</a>';

                // Approval Type
                if ($val->fh_approval_modules()->where('am_b_id', $user->emp_b_id)->first()) {
                    $row[] = 'Hierarchy Wise';
                } elseif ($val->fh_approval_modules2()->where('eam_b_id', $user->emp_b_id)->first()) {
                    $row[] = 'Employee Wise';
                } else {
                    $row[] = '---';
                }

                // Employee Ratio Column
                // if ($val->fh_approval_modules2()->where('eam_b_id', $user->emp_b_id)->first()) {
                //     $eamModuleId = $val->fh_approval_modules2()
                //         ->where('eam_b_id', $user->emp_b_id)
                //         ->pluck('eam_module_id')
                //         ->first();

                //     $approvalFlowEmpCount = EmployeeApprovalMapping::where('eam_b_id', $user->emp_b_id)
                //         ->where('eam_module_id', $eamModuleId)
                //         ->whereHas('fh_employee', function ($query) {
                //             $query->where('emp_status', 71)
                //                 ->where('emp_role_id', '!=', 1);
                //         })
                //         ->count();

                //     $totalEmpCount = $totalEmployee->count();

                //     $row[] = $approvalFlowEmpCount . ' / ' . $totalEmpCount;
                // } else {
                //     $row[] = '---';
                // }


                // Assigned & UnAssigned Columns
                if ($val->fh_approval_modules2()->where('eam_b_id', $user->emp_b_id)->first()) {
                    $eamModuleId = $val->fh_approval_modules2()
                        ->where('eam_b_id', $user->emp_b_id)
                        ->pluck('eam_module_id')
                        ->first();

                    $assignedCount = EmployeeApprovalMapping::where('eam_b_id', $user->emp_b_id)
                        ->where('eam_module_id', $eamModuleId)
                        ->whereHas('fh_employee', function ($query) {
                            $query->where('emp_status', 71)
                                ->where('emp_role_id', '!=', 1);
                        })
                        ->count();

                    $totalEmpCount = $totalEmployee->count();
                    $unAssignedCount = $totalEmpCount - $assignedCount;

                    $row[] = $assignedCount . ' / ' . $totalEmpCount;   // Assigned

                    if ($unAssignedCount > 0) {
                        $row[] = '<a href="javascript:void(0)"
                                            class="text-danger fw-semibold view-unassigned-employees"
                                            data-module-id="' . $eamModuleId . '"
                                            data-module-name="' . e($val->m_name) . '"
                                            data-unassigned="' . $unAssignedCount . '">
                                            ' . $unAssignedCount . '
                                        </a>';
                    } else {
                        $row[] = '<span class="text-muted">0</span>';
                    }
                } else {
                    $row[] = '---'; // Assigned
                    $row[] = '---'; // UnAssigned
                }



                // 3-dot dropdown
                $actionDropdown = '<div class="dropdown">
                    <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="actionDropdownBtn-' . $val->m_id . '">
                        <i class="fa fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu p-2" style="min-width: 180px;">';

                if ($val->fh_approval_modules()->where('am_b_id', $user->emp_b_id)->first()) {
                    $encryptedId = Crypt::encryptString(
                        $val->fh_approval_modules()->where('am_b_id', $user->emp_b_id)->pluck('am_id')->first()
                    );
                    $actionDropdown .= '<li>
                        <a class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2"
                        href="' . url('admin/settings/tada-settings/approval-setting', ['id' => $encryptedId]) . '">
                        <i class="fa fa-edit"></i> Edit
                        </a>
                    </li>';
                    $actionDropdown .= '<li>
                        <button class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2"
                        id="change-approval-flow-btn" data-module="' . $val->m_id . '">
                        <i class="fa fa-refresh"></i> Reset
                        </button>
                    </li>';
                    // $actionDropdown .= '   <li>
                    //     <a href="javascript:void(0)" class="dropdown-item ' . ($val->m_enable ? 'text-secondary' : 'text-success') . ' toggleStatusBtn"
                    //        data-id="' . $val->m_id . '" data-status="' . ($val->m_enable ? 1 : 0) . '">
                    //         <i class="bi ' . ($val->m_enable ? 'bi-eye-slash' : 'bi-eye') . '"></i> ' . ($val->m_enable ? 'Disable' : 'Enable') . '
                    //     </a>
                    // </li>';
                } elseif ($val->fh_approval_modules2()->where('eam_b_id', $user->emp_b_id)->first()) {
                    $encryptedId = Crypt::encryptString($val->m_id);
                    $actionDropdown .= '<li>
                        <a class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2"
                        href="' . route('form.show', ['id' => $encryptedId]) . '">
                        <i class="fa fa-edit"></i> Edit
                        </a>
                    </li>';
                    $actionDropdown .= '<li>
                        <button class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2"
                        id="change-approval-flow-btn" data-module="' . $val->m_id . '">
                        <i class="fa fa-refresh"></i> Reset
                        </button>
                    </li>';
                    // $actionDropdown .= '   <li>
                    //     <a href="javascript:void(0)" class="dropdown-item ' . ($val->m_enable ? 'text-secondary' : 'text-success') . ' toggleStatusBtn"
                    //        data-id="' . $val->m_id . '" data-status="' . ($val->m_enable ? 1 : 0) . '">
                    //         <i class="bi ' . ($val->m_enable ? 'bi-eye-slash' : 'bi-eye') . '"></i> ' . ($val->m_enable ? 'Disable' : 'Enable') . '
                    //     </a>
                    // </li>';
                } else {
                    $actionDropdown .= '<li>
                        <button class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2 create-approval-flow-btn"
                                data-module="' . md5($val->m_id) . '">
                            <i class="fa fa-plus"></i> Create
                        </button>
                    </li>';
                    // $actionDropdown .= '   <li>
                    //     <a href="javascript:void(0)" class="dropdown-item ' . ($val->m_enable ? 'text-secondary' : 'text-success') . ' toggleStatusBtn"
                    //        data-id="' . $val->m_id . '" data-status="' . ($val->m_enable ? 1 : 0) . '">
                    //         <i class="bi ' . ($val->m_enable ? 'bi-eye-slash' : 'bi-eye') . '"></i> ' . ($val->m_enable ? 'Disable' : 'Enable') . '
                    //     </a>
                    // </li>';
                }

                // $row[] = ($val->m_enable === 1)
                //     ? '<span class="badge bg-success">Enabled</span>'
                //     : '<span class="badge bg-secondary">Disabled</span>';


                $actionDropdown .= '</ul></div>';
                $row[] = $actionDropdown;

                $rowData[] = $row;
            }

            $output = [
                "draw" => request()->input('draw'),
                "recordsTotal" => sizeof($list),
                "recordsFiltered" => $dynamicHelper->countFilteredServerSideDataTable(),
                "data" => $rowData,
            ];

            return response()->json($output);
        }

        // Columns including the new Employee Ratio column      
        $columns = ['S. No.', 'Module Name', 'Approval Type', 'Assigned', 'UnAssigned', 'Action'];
        $modules = MasterTable::where('m_group', 'MODULE')->pluck('m_name', 'm_id');

        // return view('admin.setting.approval-settings.approval-list', compact('columns', 'approvalFlowType', 'modules'));
        return view('admin.exit.exit', compact('columns', 'approvalFlowType', 'modules'));
    }

    public function uploadSignature(Request $request)
    {
        $user = Auth::user();
        $b_id = $user->emp_b_id;

        $request->validate([
            'signature' => 'required|image|mimes:png,jpg,jpeg|max:20',
            'description' => 'nullable|string|max:500'
        ]);

        $filePath = null;

        if ($request->hasFile('signature')) {

            $file = $request->file('signature');
            $fileName = time() . '_signature.' . $file->getClientOriginalExtension();

            $file->move(public_path('uploads/signature'), $fileName);

            $filePath = 'uploads/signature/' . $fileName;
        }

        EmployeeExitNotes::updateOrCreate(
            [
                'b_id' => $b_id   // condition (check existing)
            ],
            [
                'notes' => $request->description,
                'signature' => $filePath
            ]
        );

        return back()->with('success', 'Signature saved successfully');
    }

    public function saveFnfModules(Request $request)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;

        $validated = $request->validate([
            'fnf_modules'   => 'required|array|min:1',
            'fnf_modules.*' => 'exists:master_table,m_id',
        ]);

        $moduleIds = $validated['fnf_modules'];

        try {
            DB::beginTransaction();

            $employeeModules = EmployeeApprovalMapping::where('eam_b_id', $businessId)
                ->pluck('eam_module_id')
                ->toArray();

            $hierarchyModules = ApprovalModule::where('am_b_id', $businessId)
                ->pluck('am_module_id')
                ->toArray();

            $existingModules = array_unique(array_merge($employeeModules, $hierarchyModules));
            $finalModules = array_values(array_unique($moduleIds));

            Business::updateOrCreate(
                ['b_id' => $businessId],
                ['b_fnf_modules' => $finalModules]
            );
            DB::commit();
            return back()->with('success', 'FNF Modules saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('FNF Module Save Error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }
    
    public function downloadAll($id, $type, $download = false)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;

        try {
            $id = Crypt::decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404);
        }

        $signatures = EmployeeExitNotes::where('b_id', $businessId)->first();

        if (!$signatures) {
            return back()->with('error', 'Signature not uploaded. Please upload your signature first.');
        }

        $exit = EmployeeExitRequest::with([
            'employee.fh_department',
            'employee.fh_designation',
            'employee.fh_employee_title',
            'employee.fh_business',
            'exitType'
        ])->findOrFail($id);

        $emp_id = $exit->er_emp_id;

        $family_details = FamilyDetail::where('fd_b_id', $businessId)
            ->where('fd_emp_id', $emp_id)
            ->first();

        $business_data = Employee::where('emp_b_id', $businessId)
            ->where('emp_role_id', 1)
            ->first();

        $salary_data = SalaryMasterHistory::where('sm_emp_b_id', $businessId)
            ->where('sm_emp_id', $emp_id)
            ->orderBy('created_at', 'desc')
            ->take(2)
            ->get(['sm_annual_ctc', 'created_at']);

        $latest_salary = $salary_data->first();
        $previous_salary = $salary_data->count() > 1 ? $salary_data->last() : null;

        // ===== Earnings =====
        $salary = $exit->salary ?? 0;
        $leaveEncashment = $exit->leave_encashment ?? 0;
        $incentives = $exit->incentives ?? 0;
        $payble_claims = $exit->claims ?? collect();

        // ===== Deductions =====
        $totalLoanRecovery = $exit->loan_recovery ?? 0;
        $adhoc = $exit->adhoc ?? 0;

        $totalPayable =
            $salary + $leaveEncashment + $incentives +
            $payble_claims->sum('tc_claimed_amount');

        $totalDeductions =
            $totalLoanRecovery + $adhoc;

        $netPay = $totalPayable - $totalDeductions;

        // ===== Templates =====
        $templates = [
            'relieving'   => 'admin.exit.doc.relieving',
            'experience'  => 'admin.exit.doc.experience',
            'noc'         => 'admin.exit.doc.noc',
            'nodues'      => 'admin.exit.doc.nodues',
            'service'     => 'admin.exit.doc.service',
            'full_final'  => 'admin.exit.doc.full_final',
            'salary_revision'  => 'admin.exit.doc.salary_revision',
        ];

        if (!$exit) {
            throw new \Exception("Exit record not found.");
        }

        $er_emp_id = $exit->er_emp_id;

        $employee = Employee::where('emp_b_id', $businessId)
            ->where('emp_id', $er_emp_id)
            ->first();

        $fromDate = $employee->emp_final_settlement_date
            ? Carbon::parse($employee->emp_final_settlement_date)
            : Carbon::now()->startOfMonth();

        $toDate = $employee->emp_last_working_date
            ? Carbon::parse($employee->emp_last_working_date)
            : Carbon::now()->endOfMonth();

        $weekOffDates = CentralLogics::getWeekOffDatesInRange($employee, $fromDate, $toDate);

        $holiday_record_exits = PolicyHolidayList::where('phl_b_id', $employee->emp_b_id)
            ->where('phl_type_id', 201)
            ->where(function ($q) use ($fromDate, $toDate) {
                $q->whereBetween('phl_start_date', [$fromDate, $toDate])
                    ->orWhereBetween('phl_end_date', [$fromDate, $toDate]);
            })
            ->get();

        $weekOffDates = CentralLogics::getAttendanceDetailsForRange(
            $employee,
            $fromDate->toDateString(),
            $toDate->toDateString(),
            $holiday_record_exits,
            $weekOffDates
        );

        $totalSalariedDaysArray = array_map(function ($item) {
            return $item['totalSalariedDays'] ?? 0;
        }, $weekOffDates);

        $totalSalariedDays = array_sum($totalSalariedDaysArray);

        $loanandadvance = LoanRequest::with(['fh_payroll_loan_installments' => function ($query) use ($fromDate, $toDate) {
            $query->whereBetween('pli_due_date', [$fromDate, $toDate])
                ->where('pli_status', 'pending');
        }])
            ->where('lnr_b_id', $businessId)
            ->where('lnr_emp_id', $er_emp_id)
            ->whereHas('fh_payroll_loan_installments', function ($query) use ($fromDate, $toDate) {
                $query->whereBetween('pli_due_date', [$fromDate, $toDate])
                    ->where('pli_status', 'pending');
            })
            ->first();

        if ($loanandadvance) {
            $totalLoanRecovery = $loanandadvance->fh_payroll_loan_installments->sum('pli_rem_bal');
        }

        $assets = Asset::with('assetType')
            ->where('assets_b_id', $businessId)
            ->where('employee_id', $er_emp_id)
            ->where('status', 'assigned')
            ->get();

        $uniformItem = UniformItem::with(['fh_stock', 'fh_stock.kit'])
            ->where('uit_b_id', $businessId)
            ->where('uit_payable', 'yes')
            ->where('uit_emp_id', $er_emp_id)
            ->get();

        $payble_claims = TadaClaim::where('tc_b_id', $businessId)
            ->where('tc_emp_id', $er_emp_id)
            ->where('tc_next_approver', 1)
            ->where('tc_stage_completed', 1)
            ->where('tc_paid_status', 0)
            ->get();

        $processedSalary = ProcessedEmployeeSalary::where('ps_b_id', $businessId)
            ->where('ps_emp_id', $er_emp_id)
            ->latest('ps_id')
            ->first();

        if ($processedSalary) {
            $salary = $processedSalary->ps_monthly_net_salary;
        }

        $payrollPeriod = PayrollPeriod::find($processedSalary?->ps_payroll_id);

        $adhocTransactions = AdhocTransaction::with('transaction_details')
            ->where('at_b_id', $businessId)
            ->where('at_emp_id', $er_emp_id)
            ->where('at_pp_id', $payrollPeriod?->pp_id)
            ->get();

        $totalAdhocEarning = $adhocTransactions->sum('at_e_amount');
        $totalAdhocDeduction = $adhocTransactions->sum('at_d_amount');

        $adhoc = $totalAdhocEarning - $totalAdhocDeduction;

        $leaveEncashment = 0;
        $incentives = 0;

        // ================= ZIP GENERATION =================
        $folderPath = storage_path('app/temp_' . time());
        if (!File::exists($folderPath)) {
            File::makeDirectory($folderPath, 0755, true);
        }

        $files = [];

        foreach ($templates as $type => $view) {

            $pdf = Pdf::loadView($view, compact(
                'exit',
                'salary',
                'leaveEncashment',
                'incentives',
                'payble_claims',
                'totalLoanRecovery',
                'adhoc',
                'totalPayable',
                'totalDeductions',
                'netPay',
                'latest_salary',
                'previous_salary',
                'signatures',
                'totalSalariedDays',
                'family_details',
                'business_data'
            ));

            $fileName = $type . '_' . $exit->employee->emp_code . '.pdf';
            $filePath = $folderPath . '/' . $fileName;

            $pdf->save($filePath);
            $files[] = $filePath;
        }

        $zipFileName = 'FNF_' . $exit->employee->emp_code . '.zip';
        $zipPath = storage_path('app/' . $zipFileName);

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($files as $file) {
                $zip->addFile($file, basename($file));
            }
            $zip->close();
        }

        foreach ($files as $file) {
            File::delete($file);
        }
        File::deleteDirectory($folderPath);

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    public function saveClearance(Request $request, $id = null)
    {
        $user = Auth::user();
        $business_id = $user->emp_b_id;

        // 🔹 All fields mapping
        $data = [
            'b_id' => $business_id,
            'eec_er_id' => $id,

            // Manager
            'handover_documents' => $request->handover_documents,

            // HR
            'id_card' => $request->id_card,
            'insurance_card' => $request->insurance_card,
            'helmet' => $request->helmet,
            'exit_interview' => $request->exit_interview,
            'vehicle' => $request->vehicle,
            'petrol_card' => $request->petrol_card,
            'hr_others' => $request->hr_others,

            // IT
            'laptop' => $request->laptop,
            'mouse' => $request->mouse,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'storage' => $request->storage,
            'access' => $request->access,
            'whatsapp' => $request->whatsapp,
            'github' => $request->github,
            'sheet' => $request->sheet,
            'credentials' => $request->credentials,
            'it_others' => $request->it_others,

            // Finance
            'signatory' => $request->signatory,
            'lease' => $request->lease,
            'loan' => $request->loan,
            'salary_adv' => $request->salary_adv,
            'travel' => $request->travel,
            'deduction' => $request->deduction,
            'bank_loan' => $request->bank_loan,
            'pf' => $request->pf,
            'notice' => $request->notice,
            'buyback' => $request->buyback,
            'accessories' => $request->accessories,
            'finance_others' => $request->finance_others,
        ];

        // 🔹 Remove only NULL values (important: keep 0, false, '')
        $filteredData = array_filter($data, function ($value) {
            return $value !== null;
        });

        // 🔹 Check if record exists
        $exitCheck = EmployeeExitChecks::where('eec_er_id', $id)->first();

        if ($exitCheck) {
            // ✅ Update only given fields
            $exitCheck->update($filteredData);
            $message = 'Exit check updated successfully';
        } else {
            // ✅ Create new record
            $exitCheck = EmployeeExitChecks::create($filteredData);
            $message = 'Exit check created successfully';
        }

        return back()->with('success', $message);
    }

    public function revert(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'id' => 'required',
            'remark' => 'required|string|min:5|max:500',
        ]);

        try {
            $user = Auth::user();
            $emp_b_id = $user->emp_b_id;
            $decryptedId = Crypt::decrypt($request->id);
            $data = EmployeeExitRequest::findOrFail($decryptedId);
            ApprovalLog::where('log_request_id', $data->er_id)->delete();
            $manager_data = MasterTable::where('m_name', 'Manager Review')->first();
            $approval_data = ApprovalModule::where('am_b_id', $emp_b_id)
                ->where('am_module_id', $manager_data->m_id)
                ->first();

            $data->update([
                'er_overall_status'  => "RESIGNATION_SUBMITTED",
                'er_am_id'           => $approval_data->am_id ?? null,
                'er_status'          => 140,
                'er_next_approver'   => 1,
                'er_stage_completed' => 0,
                'er_module_id'       => $manager_data->m_id ?? null,
                'er_revert_remark'   => $request->remark,
            ]);
            return response()->json([
                'status' => 'success',
                'message' => 'Reverted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function configuration()
    {
        $user = Auth::user();
        $businessid = $user->emp_b_id;

        $fnfModules = MasterTable::where('m_group', 'MODULE')
            ->where('m_type', 'FNF')
            ->get();

        $fnf_data = Business::where('b_id', $businessid)->first();

        $totalapprovaldata = $fnf_data->b_fnf_modules ?? [];

        // dd($totalapprovaldata);
        $signature = EmployeeExitNotes::where('b_id', $businessid)->first();


        return view('admin.exit.account', compact(
            'fnf_data',
            'totalapprovaldata',
            'fnfModules',
            'signature'
        ));
    }
}
