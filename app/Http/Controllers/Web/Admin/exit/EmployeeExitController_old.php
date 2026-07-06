<?php

namespace App\Http\Controllers\Web\Admin\exit;

use App\Helpers\CentralLogics;
use App\Http\Controllers\Controller;
use App\Models\AdhocTransaction;
use App\Models\Asset;
use App\Models\Employee;
use App\Models\EmployeeApprovalMapping;
use App\Models\EmployeeExitContact;
use App\Models\EmployeeExitNotes;
use App\Models\EmployeeExitRequest;
use App\Models\LoanRequest;
use App\Models\MailTemplate;
use App\Models\MasterTable;
use App\Models\PayrollPeriod;
use App\Models\PolicyHolidayList;
use App\Models\ProcessedEmployeeSalary;
use App\Models\TadaClaim;
use App\Models\UniformItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

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
                <div class="btn-list ms-3">
                    <a href="' . route('exit.view', ['id' => $encryptedId]) . '" class="btn btn-sm btn-link text-primary" title="View">
                        <i class="fa fa-eye"></i>
                    </a>
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
            ['name' => 'S.No',          'width' => '5%'],
            ['name' => 'Code',          'width' => '10%'],
            ['name' => 'Name',          'width' => '15%'],
            ['name' => 'Desig',         'width' => '15%'],
            ['name' => 'Dept',          'width' => '15%'],
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

    public function view($id)
    {
        $user = Auth::user();
        $employee_id = $user->emp_id;
        $businessId = $user->emp_b_id;

        // Decrypt ID
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


        // Status → Badge Class Map (Updated)
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



        // Status → Label Map (Updated)
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

        $loanandadvance = LoanRequest::with(['fh_payroll_loan_installments' => function ($query) use ($fromDate, $toDate) {
            $query->whereBetween('pli_due_date', [$fromDate, $toDate]);
        }])
            ->where('lnr_b_id', $businessId)
            ->where('lnr_emp_id', $er_emp_id)
            ->whereHas('fh_payroll_loan_installments', function ($query) use ($fromDate, $toDate) {
                $query->whereBetween('pli_due_date', [$fromDate, $toDate]);
            })
            ->first();

        $totalLoanRecovery = 0;

        if ($loanandadvance) {
            $totalLoanRecovery = $loanandadvance->fh_payroll_loan_installments->sum('pli_rem_bal');
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

        $salary = 100;
        $adhoc = 100;
        $leaveEncashment = 123;
        $incentives = 0;

        $managerStage = EmployeeExitContact::with('employee')
            ->where('ee_b_id', $businessId)
            ->where('ee_role_id', 1)
            ->first();

        $hrStage = EmployeeExitContact::with('employee')
            ->where('ee_b_id', $businessId)
            ->where('ee_role_id', 2)
            ->first();

        $adminStage = EmployeeExitContact::with('employee')
            ->where('ee_b_id', $businessId)
            ->where('ee_role_id', 3)
            ->first();

        $financeStage = EmployeeExitContact::with('employee')
            ->where('ee_b_id', $businessId)
            ->where('ee_role_id', 4)
            ->first();

        // dd($financeStage);

        return view('admin.exit.view', compact('exit', 'statusClasses', 'statusLabels', 'assets', 'uniformItem', 'payble_claims', 'pending_claims', 'totalSalariedDays', 'totalLoanRecovery', 'salary', 'adhoc', 'incentives', 'leaveEncashment', 'managerStage', 'hrStage', 'adminStage', 'financeStage', 'employee_id'));
    }

    // public function view($id)
    // {
    //     $user = Auth::user();
    //     $businessId = $user->emp_b_id;

    //     // Decrypt ID
    //     $decryptedId = Crypt::decrypt($id);

    //     // Fetch Exit Request
    //     $exit = EmployeeExitRequest::with([
    //         'employee:emp_id,emp_code,emp_full_name,emp_d_id,emp_dg_id,emp_date_of_joining,emp_notice_period_req_days,emp_notice_period_serve_days,emp_separation_submit_date',
    //         'employee.fh_designation:dg_id,dg_name',
    //         'employee.fh_department:d_id,d_name',
    //         'exitType:m_id,m_name',
    //     ])
    //         ->where('er_b_id', $businessId)
    //         ->where('er_id', $decryptedId)
    //         ->firstOrFail();


    //     // Status → Badge Class Map (Updated)
    //     $statusClasses = [
    //         'RESIGNATION_SUBMITTED' => 'badge bg-warning text-dark',
    //         'MANAGER_APPROVED' => 'badge bg-info',
    //         'MANAGER_REJECTED' => 'badge bg-danger',
    //         'HR_APPROVED' => 'badge bg-primary',
    //         'HR_REJECTED' => 'badge bg-danger',
    //         'CLEARANCE_IN_PROGRESS' => 'badge bg-warning text-dark',
    //         'DOCUMENTS_AND_RELIEVING' => 'badge bg-dark',
    //         'RELIEVED' => 'badge bg-success',
    //     ];



    //     // Status → Label Map (Updated)
    //     $statusLabels = [
    //         'RESIGNATION_SUBMITTED' => 'Submitted',
    //         'MANAGER_APPROVED' => 'Approved',
    //         'MANAGER_REJECTED' => 'Rejected',
    //         'HR_APPROVED' => 'HR Approved',
    //         'HR_REJECTED' => 'HR Rejected',
    //         'CLEARANCE_IN_PROGRESS' => 'Clearance',
    //         'DOCUMENTS_AND_RELIEVING' => 'Docs & Relieving',
    //         'RELIEVED' => 'Relieved',
    //     ];


    //     // Make sure $exit is defined
    //     if (!$exit) {
    //         throw new \Exception("Exit record not found.");
    //     }

    //     $er_emp_id = $exit->er_emp_id;

    //     // Get employee
    //     $employee = Employee::where('emp_b_id', $businessId)->where('emp_id', $er_emp_id)->first();

    //     // dd($employee);

    //     // Set from and to dates
    //     $fromDate = $employee->emp_final_settlement_date
    //         ? Carbon::parse($employee->emp_final_settlement_date)
    //         : Carbon::now()->startOfMonth();

    //     // dd($fromDate);


    //     $toDate = $employee->emp_last_working_date
    //         ? Carbon::parse($employee->emp_last_working_date)
    //         : Carbon::now()->endOfMonth();

    //     // dd($toDate);

    //     // Get week off dates
    //     $weekOffDates = CentralLogics::getWeekOffDatesInRange($employee, $fromDate, $toDate);

    //     // dd($weekOffDates);


    //     // Get holidays for the employee's business
    //     $holiday_record_exits = PolicyHolidayList::where('phl_b_id', $employee->emp_b_id)
    //         ->where('phl_type_id', 201)
    //         ->where(function ($q) use ($fromDate, $toDate) {
    //             $q->whereBetween('phl_start_date', [$fromDate, $toDate])
    //                 ->orWhereBetween('phl_end_date', [$fromDate, $toDate]);
    //         })
    //         ->get();



    //     // Get attendance details
    //     $weekOffDates = CentralLogics::getAttendanceDetailsForRange($employee, $fromDate->toDateString(), $toDate->toDateString(), $holiday_record_exits, $weekOffDates);
    //     $totalSalariedDaysArray = array_map(function ($item) {
    //         return $item['totalSalariedDays'] ?? 0;
    //     }, $weekOffDates);



    //     $totalSalariedDays = array_sum($totalSalariedDaysArray);

    //     // dd($totalSalariedDays);

    //     // $loanandadvance = LoanRequest::with(['fh_payroll_loan_installments' => function ($query) use ($fromDate, $toDate) {
    //     //     $query->whereBetween('pli_due_date', [$fromDate, $toDate]);
    //     // }])
    //     //     ->where('lnr_b_id', $businessId)
    //     //     ->where('lnr_emp_id', $er_emp_id)
    //     //     ->whereHas('fh_payroll_loan_installments', function ($query) use ($fromDate, $toDate) {
    //     //         $query->whereBetween('pli_due_date', [$fromDate, $toDate]);
    //     //     })
    //     //     ->first();

    //     $loanandadvance = LoanRequest::with(['fh_payroll_loan_installments' => function ($query) use ($fromDate, $toDate) {
    //         $query->whereBetween('pli_due_date', [$fromDate, $toDate])
    //             ->where('pli_status', 'pending'); // 👈 EMI status filter
    //     }])
    //         ->where('lnr_b_id', $businessId)
    //         ->where('lnr_emp_id', $er_emp_id)
    //         ->whereHas('fh_payroll_loan_installments', function ($query) use ($fromDate, $toDate) {
    //             $query->whereBetween('pli_due_date', [$fromDate, $toDate])
    //                 ->where('pli_status', 'pending'); // 👈 same condition here
    //         })
    //         ->first();


    //     $totalLoanRecovery = 0;

    //     if ($loanandadvance) {
    //         $totalLoanRecovery = $loanandadvance->fh_payroll_loan_installments->sum('pli_rem_bal');
    //         // dd($totalLoanRecovery);
    //     }


    //     $assets = Asset::with('assetType')->where('assets_b_id', $businessId)
    //         ->where('employee_id', $er_emp_id)
    //         ->where('status', 'assigned')
    //         ->get();

    //     $uniformItem = UniformItem::with(['fh_stock', 'fh_stock.kit'])
    //         ->where('uit_b_id', $businessId)
    //         ->where('uit_payable', 'yes')
    //         ->where('uit_emp_id', $er_emp_id)
    //         ->get();

    //     $payble_claims = TadaClaim::where('tc_b_id', $businessId)
    //         ->where('tc_emp_id', $er_emp_id)
    //         ->where('tc_next_approver', 1)
    //         ->where('tc_stage_completed', 1)
    //         ->where('tc_paid_status', 0)
    //         ->get();

    //     $pending_claims = TadaClaim::where('tc_b_id', $businessId)
    //         ->where('tc_emp_id', $er_emp_id)
    //         ->where('tc_paid_status', 0)
    //         ->get();

    //     $processedSalary = ProcessedEmployeeSalary::where('ps_b_id', $businessId)
    //         ->where('ps_emp_id', $er_emp_id)
    //         ->latest('ps_id')
    //         ->first();

    //     if ($processedSalary) {
    //         $salary = $processedSalary->ps_monthly_net_salary;
    //     } else {
    //         $salary = null;
    //     }

    //     $payrollPeriod = PayrollPeriod::find($processedSalary?->ps_payroll_id);

    //     $payrollStartDate = Carbon::parse($payrollPeriod->pp_start_date);
    //     $payrollEndDate   = Carbon::parse($payrollPeriod->pp_end_date);

    //     $fnfStartDate = $employee->emp_final_settlement_date
    //         ? Carbon::parse($employee->emp_final_settlement_date)
    //         : $payrollStartDate;

    //     $fnfEndDate = $employee->emp_last_working_date
    //         ? Carbon::parse($employee->emp_last_working_date)
    //         : $payrollEndDate;

    //     $effectiveFromDate = $fnfStartDate->greaterThan($payrollStartDate)
    //         ? $fnfStartDate
    //         : $payrollStartDate;

    //     $effectiveToDate = $fnfEndDate->lessThan($payrollEndDate)
    //         ? $fnfEndDate
    //         : $payrollEndDate;


    //     $payrollPeriodId = $payrollPeriod->pp_id;
    //     // dd($payrollPeriodId);

    //     $adhocTransactions = AdhocTransaction::with('transaction_details')
    //         ->where('at_b_id', $businessId)
    //         ->where('at_emp_id', $er_emp_id)
    //         ->where('at_pp_id', $payrollPeriodId)
    //         ->get();

    //     $totalAdhocEarning = $adhocTransactions->sum('at_e_amount');
    //     $totalAdhocDeduction = $adhocTransactions->sum('at_d_amount');

    //     $netAdhoc = $totalAdhocEarning - $totalAdhocDeduction;



    //     $adhoc = $netAdhoc;
    //     $leaveEncashment = 0;
    //     $incentives = 0;
    //     return view('admin.exit.view', compact('exit', 'statusClasses', 'statusLabels', 'assets', 'uniformItem', 'payble_claims', 'pending_claims', 'totalSalariedDays', 'totalLoanRecovery', 'salary', 'adhoc', 'incentives', 'leaveEncashment'));
    // }

    // Manager Approve

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

    // Manager Reject
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

    // HR Approve
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

    // HR Reject → Stops process
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

    // Finance Clearance → Moves to Documentation & Relieving stage
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

    // public function send_emails()
    // {
    //     $user = Auth::user();
    //     $b_id = $user->emp_b_id;
    //     $today = Carbon::now();


    //     $query = Employee::with('fh_business')->where('emp_b_id',$b_id)->where('emp_role_id', '<>', 1)
    //         ->where('emp_status', 71)
    //         ->whereMonth('emp_dob', $today->month)
    //         ->whereDay('emp_dob', $today->day);

    //     if ($b_id) {
    //         $query->where('emp_b_id', $b_id);
    //     }

    //     $employees = $query->select('emp_b_id', 'emp_id', 'emp_full_name', 'emp_email', 'emp_dob', 'emp_code')->get();


    //     if ($employees->isEmpty()) {
    //         $this->info('No birthdays today.');

    //         return;
    //     }
    //     $template = MailTemplate::where('mt_title', 'Happy Birthday – Wishing You a Wonderful Year Ahead!')->first();
    //     if (! $template) {
    //         $this->error('Birthday template not found in database.');

    //         return;
    //     }

    //     foreach ($employees as $employee) {

    // $search = ['{{Employee Name}}', '{{Employee Code}}','{{Company Name}}'];
    // $replace = [$employee->emp_full_name, $employee->emp_code, $employee->fh_business->b_name];


    // $subject = str_replace($search, $replace, $template->mt_title);
    // $body = str_replace($search, $replace, $template->mt_body);
    // // dd($body);

    // try {
    //     Mail::raw($body, function ($message) use ($employee, $subject) {
    //         $message->to($employee->emp_email)
    //             ->subject($subject);
    //     });

    //     Log::info('Birthday email sent to: '.$employee->emp_email);
    // } catch (\Exception $e) {
    //     Log::error('Failed to send birthday email to '.$employee->emp_email.' - '.$e->getMessage());
    // }

    //     }

    // }

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

    public function generatePDFtow($id, $type)
    {


        try {
            $id = Crypt::decrypt($id); // decrypt the ID
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404); // invalid ID
        }
        // dd($id, $type);


        $exit = EmployeeExitRequest::with([
            'employee.fh_department',
            'employee.fh_designation',
            'employee.fh_employee_title',
            'employee.fh_business',
            'exitType'
        ])->findOrFail($id);

        // dd($exit);

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
        ];

        if (!isset($templates[$type])) {
            abort(404, 'Invalid document type');
        }

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
            'netPay'
        ));

        // You can return download OR stream
        // return $pdf->stream(ucwords(str_replace('_', ' ', $type)) . '_' . $exit->employee->emp_code . '.pdf');
        return $pdf->download($type . '_' . $exit->employee->emp_code . '.pdf');

        // return view($templates[$type], compact('exit', 'salary', 'leaveEncashment', 'incentives', 'payble_claims', 'totalLoanRecovery', 'adhoc', 'totalPayable', 'totalDeductions', 'netPay'));
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
        if ($request->ajax()) {

            $dynamicConditions = [
                ['method' => 'where', 'args' => ['m_group', 'MODULE']],
                ['method' => 'where', 'args' => ['m_type', 'FNF']],
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
                    // dd($assignedCount);

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
                } else {
                    $actionDropdown .= '<li>
                        <button class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2 create-approval-flow-btn"
                                data-module="' . md5($val->m_id) . '">
                            <i class="fa fa-plus"></i> Create
                        </button>
                    </li>';
                }

                $actionDropdown .= '</ul></div>';
                $row[] = $actionDropdown;

                // Email Template Column (moved to last)
                // $row[] = '<a class="btn btn-sm btn-info text-white" href="' . url('/privilege/email-templates?module_id=' . $val->m_id) . '" title="Email Templates">
                //     <i class="fa fa-envelope"></i>
                // </a>';

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
        $modules = MasterTable::where('m_group', 'MODULE')->where('m_type','FNF')->pluck('m_name', 'm_id');

        return view('admin.exit.exit', compact('columns', 'approvalFlowType', 'modules'));
    }

}
