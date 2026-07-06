<?php

namespace App\Http\Controllers;

use App\Exports\ReimbursementExport;
use App\Exports\TadaReimbursedetailsExport;
use App\Exports\TadaReimburseExport;
use App\Imports\TadaReimburseImport;
use App\Imports\TadaSetalmentImport;
use App\Models\PaymentMode;
use App\Models\TadaClaim;
use App\Models\TadaReimburse;
use Carbon\Carbon;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class TadaReimburseController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $fromToDateFilter = request()->input('fromDate');
        $sheetStatusFilter = request()->input('reimburse_sheetStatusFilter');
        $claimUniqueId = request()->input('reimburse_claimIdFilter', 0);
        $claimPrimaryId = 0;
        if ($claimUniqueId > 0) {
            $claimPrimaryId = TadaClaim::where('tc_b_id', $user->emp_b_id)
                ->where('tc_unique_id', $claimUniqueId)
                ->value('tc_id');
        }
        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['tr_b_id', $user->emp_b_id],
                ],
                ['method' => 'where', 'args' => ['tr_amount', '>=', 0]],
                [
                    'method' => 'select',
                    'args' => ['tr_id', 'tr_claims_id', 'tr_b_id',  'tr_unique_id', 'tr_status', 'tr_group_id', 'tr_amount', 'created_at', 'tr_date'],
                    'relation' => ['fh_business:b_id'],
                ],
                [
                    'method' => 'sortBy',
                    'args' => ['tr_id', 'created_at', 'tr_b_id'],
                ],
            ];

            if (! empty($fromToDateFilter)) {
                $dates = explode(' - ', $fromToDateFilter);

                if (count($dates) == 2) {
                    $startDate = \Carbon\Carbon::createFromFormat('M d, Y', $dates[0])->startOfDay();
                    $endDate = \Carbon\Carbon::createFromFormat('M d, Y', $dates[1])->endOfDay();

                    $dynamicConditions[] = [
                        'method' => 'where',
                        'args' => ['created_at', '<=', $endDate->format('Y-m-d')],
                    ];

                    $dynamicConditions[] = [
                        'method' => 'where',
                        'args' => ['created_at', '>=', $startDate->format('Y-m-d')],
                    ];
                }
            }

            if ($sheetStatusFilter != '') {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['tr_status', $sheetStatusFilter]];
            }

            if ($claimPrimaryId > 0) {
                $dynamicConditions[] = [
                    'method' => 'whereRaw',
                    'args' => [
                        "CONCAT(',', REPLACE(REPLACE(tr_claims_id, '[', ''), ']', ''), ',') LIKE ?",
                        ['%,' . $claimPrimaryId . ',%'],
                    ],
                ];
            }

            $searchColumns = ['tr_unique_id', 'tr_amount', 'created_at'];
            $searchRelationships = [
                'fh_employee' => ['emp_full_name', 'emp_code'],
            ];
            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new TadaReimburse,
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
                searchRelationships: $searchRelationships
            ))->getServerSideDataTable();

            // dd($list);

            $rowData = [];
            $i = 0;
            foreach ($list as $key => $val) {

                $i++;
                $row = [];
                $row[] = $i;
                $row[] = $val->tr_unique_id ?? 'N/A';
                $row[] = $val->created_at ? Carbon::parse($val->created_at)->format('d-M-Y') : 'N/A';
                $row[] = $val->tr_date ? Carbon::parse($val->tr_date)->format('d-M-Y') : 'N/A';
                $row[] = $val->fh_claims->isNotEmpty()
                    ? (function ($claims) {
                        $formattedIds = '';
                        foreach ($claims as $index => $claim) {
                            $formattedIds .= '<span class="badge bg-light text-black rounded-pill px-3 py-2 me-1 mb-1">'
                                . $claim->tc_unique_id . '</span>';

                            // Line break after every 5 badges
                            if (($index + 1) % 5 === 0) {
                                $formattedIds .= '<br>';
                            }
                        }

                        return $formattedIds;
                    })($val->fh_claims)
                    : '';

                $row[] = round($val->tr_amount);
                $pendingCount = $val->fh_claims->where('tc_paid_status', '!=', 1)->count();

                if ($val->tr_status == 1) {
                    $status = '<span class="badge bg-success me-1">Paid</span>';
                    if ($pendingCount > 0) {
                        $status .= '<span class="badge bg-danger">Pending: ' . $pendingCount . '</span>';
                    }
                } else {
                    $status = '<span class="badge bg-warning text-dark">Inprocessed</span>';
                }

                $row[] = $status;

                $actions = '
                <div class="btn-list ms-3">
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown">
                            <i class="fa fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu p-2" style="min-width: 180px;">
                            <!-- View Reimburse Modal (if needed) -->

                            <!-- View Documents Page -->
                            <li>
                                <a href="' . route('reimburse.show', $val->tr_id) . '">
                                    <i class="feather feather-eye"></i> View
                                </a>
                            </li>


                        <!-- Report -->
                        <li>
                            <a href="' . route('reimburse.exportReport', ['id' => $val->tr_id, 'type' => 'reimbursement']) . '"
                                class="dropdown-item text-warning fw-semibold d-flex align-items-center gap-2">
                                    <i class="las la-file-download"></i> Details Report
                                </a>
                        </li>

                        <!-- Report -->
                        <li>
                            <a href="' . route('exportreimburedReport.exportReport', ['id' => $val->tr_id, 'type' => 'reimbursement']) . '"
                                class="dropdown-item text-warning fw-semibold d-flex align-items-center gap-2">
                                    <i class="las la-file-download"></i>Report
                                </a>
                        </li>

                        </ul>
                    </div>
                </div>';

                $row[] = $actions;

                $rowData[] = $row;
            }

            $output = [
                'draw' => $request->input('draw'),
                'recordsTotal' => count($list),
                'recordsFiltered' => (new DynamicModelDataTableHelper(
                    eloquentModel: new TadaReimburse,
                    dynamicConditions: $dynamicConditions,
                ))->countFilteredServerSideDataTable(),
                'data' => $rowData,
            ];

            return json_encode($output);
        }
        $columns = [
            ['name' => 'S. No.', 'width' => '5%'],
            ['name' => 'Reimburse ID', 'width' => '15%'],
            // ['name' => 'Batch ID', 'width' => '15%'],
            ['name' => 'Reimburse Date', 'width' => '15%'],
            ['name' => 'Transaction Date', 'width' => '15%'],
            ['name' => 'Claim Id', 'width' => '35%'],
            ['name' => 'Amount', 'width' => '10%'],
            ['name' => 'Status', 'width' => '10%'],
            ['name' => 'Action', 'width' => '10%'],
        ];

        return view('admin.ta-da-request.tada_reimburses', compact('columns'));
    }

    public function reimburse_index(Request $request, $id = null)
    {
        $fromToDateFilter = request()->input('fromDate');
        $sheetStatusFilter = request()->input('reimburse_sheetStatusFilter');

        $user = Auth::user();
        if (! $user) {
            abort(404);
        }

        if ($request->ajax()) {
            $id = $request->get('id') ?? $id;

            $allClaimsIds = TadaReimburse::where('tr_id', $id)
                ->pluck('tr_claims_id')
                ->toArray();

            $claimsIds = [];

            // Flatten nested arrays
            foreach ($allClaimsIds as $idsValue) {
                if (is_string($idsValue)) {
                    $decoded = json_decode($idsValue, true);
                    if (is_array($decoded)) {
                        $claimsIds = array_merge($claimsIds, $decoded);
                    }
                } elseif (is_array($idsValue)) {
                    $claimsIds = array_merge($claimsIds, $idsValue);
                }
            }

            $claimsIds = array_unique($claimsIds);
            $claimsIds = TadaClaim::whereIn('tc_id', $claimsIds)->pluck('tc_id')->toArray();

            if (empty($claimsIds)) {
                return response()->json([
                    'draw' => intval($request->input('draw')),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                ]);
            }

            $dynamicConditions[] = ['method' => 'orderBy', 'args' => ['created_at', 'desc']];
            $dynamicConditions[] = ['method' => 'whereIn', 'args' => ['tc_id', $claimsIds]];

            if (! empty($fromToDateFilter)) {
                $dates = explode(' - ', $fromToDateFilter);

                if (count($dates) == 2) {
                    $startDate = \Carbon\Carbon::createFromFormat('M d, Y', $dates[0])->startOfDay();
                    $endDate = \Carbon\Carbon::createFromFormat('M d, Y', $dates[1])->endOfDay();

                    $dynamicConditions[] = [
                        'method' => 'where',
                        'args' => ['created_at', '<=', $endDate->format('Y-m-d')],
                    ];

                    $dynamicConditions[] = [
                        'method' => 'where',
                        'args' => ['created_at', '>=', $startDate->format('Y-m-d')],
                    ];
                }
            }

            if ($sheetStatusFilter != '') {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['tc_paid_status', $sheetStatusFilter]];
            }

            $searchColumns = ['tc_unique_id', 'fh_employee.emp_full_name', 'tc_payed_amount'];

            $datatableHelper = new DynamicModelDataTableHelper(
                eloquentModel: new TadaClaim,
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns
            );

            $list = $datatableHelper->getServerSideDataTable();

            // dd($list);

            // Count total records without search
            $totalRecordsQuery = TadaClaim::where('tc_b_id', $user->emp_b_id)
                ->where('tc_amount', '>=', 0)
                ->whereIn('tc_id', $claimsIds);

            $totalRecords = $totalRecordsQuery->count();

            $start = intval($request->input('start', 0));

            $rowData = [];
            foreach ($list as $i => $claim) {
                $row = [];
                $row[] = $start + $i + 1;
                $row[] = e($claim->fh_employee->emp_full_name ?? 'N/A');
                $row[] = e($claim->tc_unique_id ?? 'N/A');
                $row[] = $claim->tc_payed_amount !== null ? number_format($claim->tc_payed_amount, 2) : 'N/A';
                $row[] = $claim->transaction_date ? Carbon::parse($claim->transaction_date)->format('d-M-Y') : 'N/A';
                $row[] = $claim->reference_no ?? 'N/A';
                $row[] = $claim->tc_paid_status == 1
                    ? '<span class="badge bg-success">Paid</span>'
                    : '<span class="badge bg-secondary">Pending</span>';
                $row[] = '
                <div class="btn-list ms-3">
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown">
                            <i class="fa fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu p-2" style="min-width: 180px;">
                            <li>
                                <a class="dropdown-item text-info fw-semibold d-flex align-items-center gap-2"
                                    href="' . route('claim-request-is-paid.request.show', md5($claim->tc_id)) . '">
                                    <i class="feather feather-eye"></i> View
                                </a>
                            </li>
                        <li>
                        <a class="dropdown-item text-info fw-semibold d-flex align-items-center gap-2"
                        href="' . route('travel.claim.report', md5($claim->tc_id)) . '"
                        target="_blank">
                            <i class="feather feather-file"></i> Report
                        </a>
                    </li>

                        </ul>
                    </div>
                </div>';

                $rowData[] = $row;
            }

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $datatableHelper->countFilteredServerSideDataTable(),
                'data' => $rowData,
            ]);
        }

        $columns = [
            'S. No.',
            'Employee Name',
            'Claim ID',
            'Paid Amount',
            'Transaction Date',
            'Reference No',
            'Status',
            'Action',
        ];

        return view('admin.ta-da-request.tada_reimburses_show', compact('columns', 'id'));
    }

    public function edit($tadaReimburse)
    {
        $user = Auth::user();
        $tadaReimburseClaimData = TadaClaim::with('fh_employee')->where('tc_b_id', $user->emp_b_id)->whereIn('tc_id', json_decode($tadaReimburse))->get();
        foreach ($tadaReimburseClaimData as $claim) {
            $claim->hashed_id = md5($claim->tc_id);
        }
        if ($tadaReimburseClaimData) {
            return response()->json(['status' => true, 'data' => $tadaReimburseClaimData]);
        } else {
            return response()->json(['status' => false]);
        }
    }

    public function exportReport(Request $request, $id)
    {
        $user = Auth::user();
        $mode = PaymentMode::where('pm_b_id', $user->emp_b_id)->first();

        if (! $mode) {
            return redirect()->back()->with('error', 'Payment mode not created.');
        }

        $type = $request->query('type', 'reimbursement');

        $fileName = $type === 'settlement'
            ? 'settlement_report_' . $id . '.xlsx'
            : 'reimbursement_report_' . $id . '.xlsx';

        try {
            return Excel::download(new TadaReimburseExport($id, $type), $fileName);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to export report. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function exportreimburedReport(Request $request, $id)
    {
        $user = Auth::user();
        $mode = PaymentMode::where('pm_b_id', $user->emp_b_id)->first();

        if (! $mode) {
            return redirect()->back()->with('error', 'Payment mode not created.');
        }

        $type = $request->query('type', 'reimbursement');

        $fileName = $type === 'settlement'
            ? 'settlement_report_' . $id . '.xlsx'
            : 'reimbursement_report_' . $id . '.xlsx';

        try {
            return Excel::download(new TadaReimbursedetailsExport($id, $type), $fileName);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to export report. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv|max:2048',
        ]);

        try {

            if ($request->input('hidden') === 'settlement') {

                Excel::import(new TadaSetalmentImport, $request->file('file'));
                $msg = 'TADA Settlement Status Updated Successfully!';
            } else {

                Excel::import(new TadaReimburseImport, $request->file('file'));
                $msg = 'TADA Reimburse Status Updated Successfully!';
            }

            return redirect()->back()->with('success', $msg);
        } catch (Exception $e) {

            return redirect()->back()->with('error', 'Import Failed! ' . $e->getMessage());
        }
    }

    public function exportReportformate(Request $request)
    {
        $type = $request->query('type', 'reimbursement'); // default = reimbursement

        $fileName = ucfirst($type) . '_Report_Format.xlsx';

        return Excel::download(new ReimbursementExport($type), $fileName);
    }
}
