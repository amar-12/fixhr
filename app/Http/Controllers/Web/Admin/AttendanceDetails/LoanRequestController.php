<?php

namespace App\Http\Controllers\Web\Admin\AttendanceDetails;

use Carbon\Carbon;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Designation;
use App\Models\LoanRequest;
use Illuminate\Http\Request;
use App\Helpers\ApprovalHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\PayrollLoanInstallment;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;

class LoanRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;

        $branch = Branch::whereNull('br_b_id')->orWhere('br_b_id', $businessId)->get();
        $departments = Department::whereNull('d_b_id')->orWhere('d_b_id', $businessId)->get();
        $designations = Designation::whereNull('dg_b_id')->orWhere('dg_b_id', $businessId)->get();

        $branchFilter = $request->input('loan_branchFilter');
        $departmentFilter = $request->input('loan_departmentFilter');
        $designationFilter = $request->input('loan_designationFilter');
        $toDateFilter = $request->input('toDate');

        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['lnr_b_id', $businessId]
                ],
                [
                    'method' => 'select',
                    'args' => ['lnr_id', 'lnr_b_id', 'lnr_emp_id', 'lnr_description', 'lnr_request_status', 'lnr_requested_amount', 'lnr_installments', 'lnr_installment_amount', 'lnr_stage_completed', 'lnr_module_id', 'lnr_am_id', 'lnr_next_approver', 'updated_at', 'created_at'],
                    'relation' => ['fh_employee:emp_id,emp_b_id,emp_full_name,emp_code,emp_br_id,emp_dg_id,emp_d_id', 'fh_approval_status:m_id,m_name,m_other']
                ],
                [
                    'method' => 'sortBy',
                    'args' => ['lnr_id', 'created_at']
                ]
            ];

            // Apply filters
            if (!empty($branchFilter)) {
                $dynamicConditions[] = [
                    'method' => 'whereRelation',
                    'parentMethod' => 'whereHas',
                    'childMethod' => 'where',
                    'args' => ['emp_br_id', $branchFilter],
                    'relation' => 'fh_employee'
                ];
            }

            if (!empty($designationFilter)) {
                $dynamicConditions[] = [
                    'method' => 'whereRelation',
                    'parentMethod' => 'whereHas',
                    'childMethod' => 'where',
                    'args' => ['emp_dg_id', $designationFilter],
                    'relation' => 'fh_employee'
                ];
            }

            if (!empty($departmentFilter)) {
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
                    'method' => 'whereDate',
                    'args' => ['created_at', $toDateFilter]
                ];
            }

            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new LoanRequest(),
                dynamicConditions: $dynamicConditions,
                searchColumns: ['lnr_id', 'lnr_emp_id', 'created_at', 'lnr_description', 'lnr_request_status'],
                searchRelationships: [
                    'fh_employee' => ['emp_fname', 'emp_mname', 'emp_lname', 'emp_code']
                ]
            ))->getServerSideDataTable();

            $rowData = [];
            $i = 1;
            foreach ($list as $val) {
                $row = [];
                $row[] = $i++;
                $row[] = optional($val->fh_employee)->emp_code;
                $row[] = optional($val->fh_employee)->emp_full_name;
                $row[] = Carbon::parse($val->created_at)->format('d-M-Y');
                $row[] = '<span class="text-center">' . $val->lnr_requested_amount . '</span>';
                $row[] = '<span class="text-center">' . $val->lnr_installments . '</span>';
                $approval = ApprovalHelper::checkApproval($businessId, $val->lnr_module_id, $val->lnr_id, optional($val->fh_employee)->emp_d_id, $val->lnr_emp_id);
                $nextApproval = ApprovalHelper::getNextApprovalDetails($val->lnr_module_id, $val->lnr_id, $businessId);

                $logData = $val->fh_plan_approval_log ?? collect();
                $formattedLog = $logData->map(function ($log) {
                    return ($log->fh_employee->emp_full_name ?? 'N/A') . ' (' . ($log->fh_role->role_name ?? 'N/A') . ') ' . ($log->fh_status->m_name ?? 'N/A');
                })->implode(', ');

                if (empty($formattedLog)) {
                    $formattedLog = 'Awaiting';
                }

                $jsonData = optional($val->fh_approval_status)->m_other;
                $decodedData = json_decode($jsonData, true);
                $color = $decodedData['color'] ?? '#ccc';
                $icon = $decodedData['web_icon'] ?? 'feather-alert-circle';

                $row[] = '<span class="badge" style="background-color:' . $color . '"><i class="' . $icon . '"></i> ' . ($val->fh_approval_status->m_name ?? '') . '</span>'
                    . '&nbsp;<i class="feather feather-info text-primary fs-14" data-bs-toggle="popover" data-bs-content="' . htmlspecialchars($formattedLog) . '" data-bs-placement="right" title="Approval Log"></i>'
                    . '<p class="text-muted mb-0 fs-12">Approval ' . ($approval['approvallog_count'] ?? '-') . ' (' . ($approval['approvalcount'] ?? '-') . ')</p>';

                $approverName = $nextApproval['approver_name'] ?? '---';
                $row[] = '<span class="text-center">' . $approverName . '</span>';

                // Generate URLs
                $showUrl = route('requests.loan-requests.show', md5($val->lnr_id));
                $exportUrl = route('requests.loan-requests.exportEmiSchedule', md5($val->lnr_id));
                $pdfUrl = url('/api/admin/loan/loan_request_pdf/' . md5($val->lnr_id));

                // Initialize dropdown items as empty string
                $dropdownItems = '';

                // Common items for all stages (View and PDF)
                $commonItems = '
                <li>
                    <a class="dropdown-item text-info fw-semibold d-flex align-items-center gap-2"
                    href="' . $showUrl . '">
                         View
                    </a>
                </li>
                <li>
                    <a class="dropdown-item text-info fw-semibold d-flex align-items-center gap-2"
                    href="' . $pdfUrl . '" target="_blank">
                         PDF
                    </a>
                </li>
            ';

                // Add common items to dropdown
                $dropdownItems .= $commonItems;

                // Add stage-specific items
                if ($val->lnr_stage_completed == 1) {
                    $dropdownItems .= '
                    <li>
                        <a class="dropdown-item text-info fw-semibold d-flex align-items-center gap-2"
                        href="' . $exportUrl . '">
                            Export EMI Schedule
                        </a>
                    </li>
                ';
                }

                $row[] = '
                <div class="btn-list ms-3">
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu p-2" style="min-width: 180px;">
                            ' . $dropdownItems . '
                        </ul>
                    </div>
                </div>
            ';

                $rowData[] = $row;
            }

            return response()->json([
                "draw" => intval($request->input('draw')),
                "recordsTotal" => sizeof($list),
                "recordsFiltered" => (new DynamicModelDataTableHelper(
                    eloquentModel: new LoanRequest(),
                    dynamicConditions: $dynamicConditions
                ))->countFilteredServerSideDataTable(),
                "data" => $rowData,
            ]);
        }

        $columns = [
            'S. No.',
            'Emp Code',
            'Employee Name',
            'Applied Date',
            'Requested Amount',
            'Installments',
            'Status',
            'Submitted To',
            'Action',
        ];

        return view('admin.payroll.requests.loan-requests', compact(
            'departments',
            'designations',
            'branch',
            'columns'
        ));
    }

    public function show($id)
    {
        $user = Auth::user();
        $data = LoanRequest::with(
            'fh_employee:emp_id,emp_fname,emp_mname,emp_lname,emp_dg_id,emp_d_id,emp_phone,emp_code,emp_profile_photo,emp_full_name,emp_email,emp_status,emp_br_id',
            'fh_employee.fh_department:d_id,d_name',
            'fh_employee.fh_designation:dg_id,dg_name',
            'fh_employee.fh_branch:br_id,br_name',
            'fh_approval_status:m_id,m_name,m_other',
            'fh_module:m_id,m_name,m_other',
            'fh_approval_log2',
            'fh_plan_approval_log.fh_employee', // ensure this is eager loaded if used
            'fh_plan_approval_log.fh_status',
        )->where(DB::raw('md5(lnr_id)'), $id)->first();
        if (!$data) {
            abort(404, 'Loan Request not found');
        }
        $approvalData = ApprovalHelper::getApprovalOrRejectionData($data->lnr_id, $data->lnr_request_status, $data->lnr_am_id, NULL, 442);
        // Optional: Handle null approval data gracefully
        if (is_null($approvalData)) {
            $approvalData = [];
        }

        // Get approval control flags
        $data->approvalData = $approvalData;
        $approval = ApprovalHelper::getApprovalData($data, $user, 'lnr_');
        $masterApproveBtn = $approval['masterApproveBtn'] ?? false;
        $canApprove = $approval['canApprove'] ?? false;


        $loan = LoanRequest::with([
            'fh_employee',
            'fh_business',
            'fh_employee_salary',
            'fh_payroll_periods',
            'fh_payroll_loan_installments',
            'fh_payroll_loan_installments.month_name'
        ])->where(DB::raw('md5(lnr_id)'), $id)->first();


        // dd($loan);

        if (!$data) {
            abort(404, 'Loan Request not found');
        }

        return view('admin.payroll.requests.loan-requests-show', compact('data', 'approvalData', 'canApprove', 'masterApproveBtn', 'loan'));
    }


    public function approve(Request $request)
    {
        $data = LoanRequest::findOrFail($request->log_request_id);

        // Process approval using the service
        $response = ApprovalHelper::processApproval($request, $data, $this->user, 'lnr_');

        return response()->json($response, $response['status'] === 'success' ? 200 : 500);
    }

    // public function approve(Request $request)
    // {
    //     $loan = LoanRequest::findOrFail($request->log_request_id);
    //     $user = Auth::user();

    //     $validated = $request->validate([
    //         'status' => 'required|string',
    //         'lnr_b_id' => 'required|integer',
    //         'installments' => 'required|array',
    //     ]);

    //     $userId = $user->emp_id;
    //     $business_id = $loan->lnr_b_id;

    //     // Check for existing active/pending loans
    //     // $existingActiveLoan = LoanRequest::where('lnr_emp_id', $userId)
    //     //     ->where('lnr_b_id', $business_id)
    //     //     ->whereIn('lnr_status', ['approved', 'pending'])
    //     //     ->where('lnr_id', '!=', $loan->lnr_id)
    //     //     ->exists();

    //     // if ($existingActiveLoan) {
    //     //     return response()->json([
    //     //         'message' => 'You already have an ongoing or pending loan.',
    //     //         'status' => false,
    //     //     ], 422);
    //     // }

    //     // Save loan data
    //     $loan->lnr_rate = $request->rate ?? 0;
    //     $loan->lnr_status = $validated['status'];
    //     $loan->save();

    //     // Save or update installment schedule
    //     $startDate = \Carbon\Carbon::parse($loan->lnr_start_date)->startOfMonth();
    //     foreach ($validated['installments'] as $installmentData) {
    //         $installmentNumber = $installmentData['installment_number'];
    //         $dueDate = $startDate->copy()->addMonths($installmentNumber - 1);

    //         PayrollLoanInstallment::updateOrCreate(
    //             [
    //                 'pli_loan_id' => $loan->lnr_id,
    //                 'pli_installment_no' => $installmentNumber,
    //             ],
    //             [
    //                 'pli_b_id' => $business_id,
    //                 'pli_amount' => $installmentData['amount'],
    //                 'pli_rem_bal' => $installmentData['remaining_balance'],
    //                 'pli_due_date' => $dueDate->format('Y-m-d'),
    //                 'pli_month' => $dueDate->month,
    //                 'pli_year' => $dueDate->year,
    //                 'pli_status' => $loan->lnr_status,
    //             ]
    //         );
    //     }

    //     // Call approval logic
    //     $response = ApprovalHelper::processApproval($request, $loan, $user, 'lnr_');

    //     return response()->json($response, $response['status'] === 'success' ? 200 : 500);
    // }


    public function exportEmiSchedule($loanId)
    {

        $user = auth()->user();
        $authUser = Employee::find($user->emp_id)?->emp_full_name ?? 'System';
        $employee = Employee::with([
            'fh_department',
            'fh_designation',
            'fh_branch',
            'fh_business.fh_admin'          // ⬅️ makes $employee->fh_business available
        ])
            ->findOrFail($user->emp_id);
        $logoPath = $employee->fh_business->b_logo ?? null;
        $loan = LoanRequest::with('fh_employee')
            ->whereRaw('md5(lnr_id) = ?', [$loanId])
            ->first();

        if (!$loan) {
            return response()->json([
                'message' => 'Loan not found',
                'status' => false,
            ], 404);
        }

        // EMI Installments
        $installments = PayrollLoanInstallment::where('pli_loan_id', $loan->lnr_id)
            ->orderBy('pli_id', 'asc')
            ->get();

        $principal = $installments->sum('pli_principal'); // column name for principal
        $interest  = $installments->sum('pli_interest');  // column name for interest

        $data = [
            'employee' => $employee,
            'loan' => $loan,
            'installments' => $installments,
            'logoPath' => $logoPath,
            'principal'   => $principal,
            'interest'    => $interest,
        ];

        $pdf = PDF::loadView('admin.payroll.exports.emi-schedule', $data)->setPaper('A4', 'portrait');

        // ✅ Show in browser instead of download
        return $pdf->stream('emi_schedule_' . $loanId . '.pdf');
    }



    public function loanReport($slug)
    {
        if (!in_array($slug, ['loan-register', 'loan-approval', 'loan-rejection'])) {
            return abort(404);
        }

        return view('admin.setting.payroll.loan-report', compact('slug'));
    }
}
