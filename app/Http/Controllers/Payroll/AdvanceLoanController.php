<?php

namespace App\Http\Controllers\Payroll;

use App\Models\Employee;
use App\Models\LoanRequest;
use Illuminate\Http\Request;
use App\Models\PayrollLoanAccount;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\PayrollLoanInstallment;
use ChandraHemant\HtkcUtils\CommonUtils;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;

class AdvanceLoanController extends Controller
{
    public function loanApprovalList(Request $request)
    {

        $user = Auth::user();
        $businessId = $user->emp_b_id;

        $loanTypeFilter = $request->input('loanTypeFilter');
        $empNameFilter = $request->input('empNameFilter');
        $statusFilter = $request->input('statusFilter');
        $monthFilter = $request->input('mt_monthFilter');


        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['lnr_b_id', $businessId]
                ],
                [
                    'method' => 'select',
                    'args' => [
                        'lnr_id',
                        'lnr_emp_id',
                        'lnr_requested_amount',
                        'lnr_installment_amount',
                        'lnr_installments',
                        'lnr_start_date',
                        'lnr_description',
                        'lnr_module_id',
                        'lnr_request_status',
                        'lnr_stage_completed',
                        'lnr_next_approver',
                        'lnr_status'
                    ],
                    'relation' => [
                        'fh_employee:emp_id,emp_code,emp_full_name',
                        // 'fh_master_type:m_id,m_name'
                    ]
                ]
            ];

            // Filter conditions
            if ($loanTypeFilter != '') {
                $dynamicConditions[] = [
                    'method' => 'where',
                    'args' => ['lnr_module_id', $loanTypeFilter]
                ];
            }

            if ($empNameFilter != '') {
                $dynamicConditions[] = [
                    'method' => 'where',
                    'args' => ['lnr_emp_id', $empNameFilter]
                ];
            }

            if ($statusFilter != '') {
                if ($statusFilter == 'approved') {
                    $dynamicConditions[] = [
                        'method' => 'where',
                        'args' => ['lnr_request_status', 1] // assuming 1 = Approved
                    ];
                } elseif ($statusFilter == 'pending') {
                    $dynamicConditions[] = [
                        'method' => 'where',
                        'args' => ['lnr_request_status', 0] // assuming 0 = Pending
                    ];
                } elseif ($statusFilter == 'rejected') {
                    $dynamicConditions[] = [
                        'method' => 'where',
                        'args' => ['lnr_request_status', 2] // assuming 2 = Rejected
                    ];
                }
            }


            if ($monthFilter != '') {
                // Start date of month
                $startDate = $monthFilter . '-01';

                // End date of month (last day of the month)
                $endDate = date('Y-m-t', strtotime($startDate));

                // Add condition
                $dynamicConditions[] = [
                    'method' => 'whereBetween',
                    'args' => ['lnr_start_date', [$startDate, $endDate]],
                ];
            }



            // Define search value, columns, and relationships
            $searchColumns = ['lnr_description', 'lnr_requested_amount'];
            $searchRelationships = [
                'fh_employee' => ['emp_full_name', 'emp_code'],
                // 'fh_master_type' => ['m_name'],
            ];

            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new LoanRequest(), // Note: Use correct Model mapped to fh_loan_requests
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
                searchRelationships: $searchRelationships
            ))->getServerSideDataTable();


            $rowData = [];
            $i = 1;
            foreach ($list as $key => $loan) {
                $row = [];
                $row[] = $i++;
                $row[] = $loan->fh_employee ? $loan->fh_employee->emp_full_name . ' (' . $loan->fh_employee->emp_code . ')' : 'N/A';
                $row[] = number_format($loan->lnr_requested_amount, 2);
                $row[] = number_format($loan->lnr_installment_amount, 2);
                $row[] = $loan->lnr_start_date ? \Carbon\Carbon::parse($loan->lnr_start_date)->format('d-m-Y') : 'N/A';
                $row[] = $loan->lnr_installments ?? 'N/A';

                // Status column
                $statusBadge = '';
                if ($loan->lnr_request_status == 1) {
                    $statusBadge = '<span class="badge bg-success">Approved</span>';
                } elseif ($loan->lnr_request_status == 2) {
                    $statusBadge = '<span class="badge bg-danger">Rejected</span>';
                } else {
                    $statusBadge = '<span class="badge bg-warning">Pending</span>';
                }
                $row[] = $statusBadge;

                // Action column
               $row[] = '<a href="#" class="btn btn-sm view-loan-btn" data-id="' . $loan->lnr_id . '">  <i class="feather feather-eye"></i>  </a>';

                $rowData[] = $row;
            }

            $output = [
                "draw" => $request->input('draw'),
                "recordsTotal" => count($list),
                "recordsFiltered" => (new DynamicModelDataTableHelper(
                    eloquentModel: new LoanRequest(),
                    dynamicConditions: $dynamicConditions,
                    searchColumns: $searchColumns,
                    searchRelationships: $searchRelationships
                ))->countFilteredServerSideDataTable(),
                "data" => $rowData,
            ];

            return response()->json($output);
        }

        $columns = [
            'S. No.',
            'Employee Name',
            'Requested Amount',
            'EMI Amount',
            'Start Date',
            'Installments',
            'Status',
            'Action'
        ];

        // Get employee list for filter
        $empNameFilter = CommonUtils::getCustomModelData(new Employee(), [
            ['method' => 'where', 'args' => ['emp_b_id', $businessId]]
        ]);

        $loan = new LoanRequest(); // Correct Model

        return view('admin.payroll.loan-approval-list', compact('columns', 'empNameFilter', 'loan'));
    }


    public function loanDetails($id)
    {
        $loan = LoanRequest::with([
            'fh_employee',
            // 'fh_master_type',
            'fh_business',
            'fh_employee_salary',
            'fh_payroll_periods'
        ])->findOrFail($id);
        // dd($loan);
        return view('admin.payroll.partials.loan-details', compact('loan'));
    }

    public function saveLoanDetails(LoanRequest $loan, Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'status' => 'required|string',
            'lnr_b_id' => 'required|integer',
            'installments' => 'required|array',
        ]);
        $userId = $user->emp_id;
        $business_id = $loan->lnr_b_id;

        $existingActiveLoan = LoanRequest::where('lnr_emp_id', $userId)
        ->where('lnr_b_id', $business_id)
        ->whereIn('lnr_status', ['approved', 'pending']) // adjust statuses as needed
        ->where('lnr_id', '!=', $loan->lnr_id) // exclude the current one in case of update
        ->exists();

        if ($existingActiveLoan) {
            return response()->json([
                'message' => 'You already have an ongoing or pending loan.',
                'status' => false,
            ], 422);
        }



        $loan->lnr_rate = $request->rate ?? 0;
        $loan->lnr_status = $validated['status'];
        $loan->save();



        // Loan Start Date
        $startDate = \Carbon\Carbon::parse($loan->lnr_start_date)->startOfMonth();



        // Save or Update Installments
        foreach ($validated['installments'] as $installmentData) {
            $installmentNumber = $installmentData['installment_number'];

            // Calculate Due Date based on Start Date
            $dueDate = $startDate->copy()->addMonths($installmentNumber - 1);

            PayrollLoanInstallment::updateOrCreate(
                [
                    'pli_loan_id' => $loan->lnr_id,
                    'pli_installment_no' => $installmentNumber,
                ],
                [
                    'pli_b_id' => $business_id,
                    'pli_amount' => $installmentData['amount'],
                    'pli_rem_bal' => $installmentData['remaining_balance'],
                    'pli_due_date' => $dueDate->format('Y-m-d'),
                    'pli_month' => $dueDate->month,    // Save month (1-12)
                    'pli_year' => $dueDate->year,      // Save year (e.g., 2025)
                    'pli_status' => $loan->lnr_status,
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Loan Approved Successfully',
        ]);
    }



    public function approveLoan(Request $request, $id)
    {
        // $loan = PayrollLoanAccount::findOrFail($id);
        $loan = LoanRequest::findOrFail($id);

        // $loan->update([
        //     'is_active' => true,
        //     'modified_by_id' => Auth::id(),
        //     'approved_date' => now()
        // ]);
        return response()->json([
            'success' => true,
            'message' => 'Loan approved successfully'
        ]);
    }

    public function rejectLoan(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:255'
        ]);

        $loan = PayrollLoanAccount::findOrFail($id);

        $loan->update([
            'is_active' => false,
            'settled' => true,
            'modified_by_id' => Auth::id(),
            'settled_date' => now(),
            'description' => $request->reason
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Loan rejected successfully'
        ]);

    }
}
