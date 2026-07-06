<?php

namespace App\Http\Controllers\Web\Admin\TadaSettings;

use App\Helpers\RolePermissionLogics;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Country;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\MasterTable;
use App\Models\PolicyTadaCategory;
use App\Models\TadaReimburse;
use Carbon\Carbon;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TadaSettlementController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }
    // public function index(Request $request)
    // {
    //     $user = Auth::user();
    //     $businessId = $user->emp_b_id;
    //     $currentMonth = now()->month;
    //     $branchFilter = request()->input('settlement_branchFilter');
    //     $activeFilter = request()->input('settlement_activeFilter');
    //     $designationFilter = request()->input('settlement_designationFilter');
    //     $departmentFilter = request()->input('settlement_departmentFilter');
    //     $gradeFilter = request()->input('settlement_gradeFilter');
    //     $fromDateFilter = request()->input('settlement_fromDate');
    //     $toDateFilter = request()->input('settlement_toDate');

    //     if ($request->ajax()) {

    //         $dynamicConditions = [
    //             [
    //                 'method' => 'where',
    //                 'args' => ['emp_b_id', $user->emp_b_id]
    //             ],
    //             [
    //                 'method' => 'where',
    //                 'args' => ['emp_tada_settlement_amt', '<', 0]
    //             ],
    //             [
    //                 'method' => 'whereNot',
    //                 'args' => ['emp_role_id', 1]
    //             ],
    //             [
    //                 'method' => 'select',
    //                 'args' => ['emp_id', 'emp_b_id', 'emp_fname', 'emp_mname', 'emp_lname', 'emp_full_name', 'emp_code', 'emp_role_id', 'emp_email', 'emp_type_id', 'emp_br_id', 'emp_d_id', 'emp_dg_id', 'emp_dob', 'emp_date_of_joining', 'emp_phone', 'emp_work_mode_id', 'emp_shift_type_id', 'emp_status', 'emp_grade_id', 'emp_gender_id', 'emp_date_of_joining', 'emp_marital_status_id', 'emp_profile_photo', 'created_at', 'emp_tada_settlement_amt'],
    //                 'relation' => ['fh_employee_type:m_id,m_name', 'fh_branch:br_id,br_name', 'fh_gender:m_id,m_name', 'fh_designation:dg_id,dg_name', 'fh_department:d_id,d_name', 'fh_work_mode:m_id,m_name', 'fh_shift_type:pst_id,pst_name', 'fh_employee_status:m_description,m_name'],
    //             ],
    //             [
    //                 'method' => 'sortBy',
    //                 'args' => ['emp_id', 'emp_full_name', 'emp_code', 'emp_type_id', 'emp_br_id', 'emp_d_id', 'emp_date_of_joining', 'emp_phone', 'emp_work_mode_id', 'emp_shift_type_id', 'emp_status']
    //             ]
    //         ];

    //         // Filter conditions
    //         if ($branchFilter != '') {
    //             $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_br_id', $branchFilter]];
    //         }

    //         if ($activeFilter != '') {
    //             $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_status', $activeFilter]];
    //         }

    //         if ($designationFilter != '') {
    //             $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_dg_id', $designationFilter]];
    //         }

    //         if ($departmentFilter != '') {
    //             $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_d_id', $departmentFilter]];
    //         }

    //         if ($departmentFilter != '') {
    //             $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_d_id', $departmentFilter]];
    //         }
    //         if ($gradeFilter != '') {
    //             $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_grade_id', $gradeFilter]];
    //         }

    //         if ($fromDateFilter || $toDateFilter) {
    //             if ($fromDateFilter && $toDateFilter) {
    //                 if ($fromDateFilter === $toDateFilter) {
    //                     // Apply filtering for the exact same date
    //                     $dynamicConditions[] = [
    //                         'method' => 'whereDate',
    //                         'args' => ['created_at', $fromDateFilter]
    //                     ];
    //                 } else {
    //                     // Ensure full-day coverage for both from_date and to_date
    //                     $fromDateTime = $fromDateFilter . ' 00:00:00';
    //                     $toDateTime = $toDateFilter . ' 23:59:59';

    //                     // Apply date filtering if both dates are provided and they are different
    //                     $dynamicConditions[] = [
    //                         'method' => 'whereBetween',
    //                         'args' => ['created_at', [$fromDateTime, $toDateTime]]
    //                     ];
    //                 }
    //             } elseif ($fromDateFilter) {
    //                 // Apply filtering from the start date onwards if only from_date is provided
    //                 $fromDateTime = $fromDateFilter . ' 00:00:00';
    //                 $dynamicConditions[] = [
    //                     'method' => 'where',
    //                     'args' => ['created_at', '>=', $fromDateTime]
    //                 ];
    //             } elseif ($toDateFilter) {
    //                 // Apply filtering up to the end date if only to_date is provided
    //                 $toDateTime = $toDateFilter . ' 23:59:59';
    //                 $dynamicConditions[] = [
    //                     'method' => 'where',
    //                     'args' => ['created_at', '<=', $toDateTime]
    //                 ];
    //             }
    //         }


    //         // Define search value, columns, and relationships
    //         $searchColumns = ['emp_id', 'emp_code', 'emp_full_name', 'emp_fname', 'emp_mname', 'emp_lname', 'emp_full_name', 'emp_email', 'emp_phone', 'emp_tada_settlement_amt'];
    //         $searchRelationships = [
    //             'fh_branch' => ['br_name'],
    //             'fh_department' => ['d_name'],
    //         ];

    //         $list = (new DynamicModelDataTableHelper(eloquentModel: new Employee(), dynamicConditions: $dynamicConditions, searchColumns: $searchColumns, searchRelationships: $searchRelationships))->getServerSideDataTable();


    //         $rowData = array();
    //         $i = 1;
    //         foreach ($list as $key => $val) {

    //             dd($val);
    //             $policyCategory = PolicyTadaCategory::where(['ptc_b_id' => $val->emp_b_id, 'ptc_d_id' => $val->emp_d_id, 'ptc_grade_id' => $val->emp_grade_id])->whereJsonContains('ptc_dg_id', $val->emp_dg_id)->first();
    //             $dataPolicy = $policyCategory->ptc_name ?? 'N/A';
    //             $row = array();

    //             $row[] = $i++;
    //             $row[] = ($val->emp_code ? $val->emp_code . ' - ' : '') . $val->emp_full_name;
    //             $row[] = $val->emp_tada_settlement_amt;
    //             $row[] = '<button class="btn action-btns btn-sm  openBtn" data-id="' . $val->emp_id . '" data-emp_full_name="' . $val->emp_full_name . '" data-emp_tada_settlement_amt="' . $val->emp_tada_settlement_amt . '" data-emp_code="' . $val->emp_code . '">    <i class="feather-eye fs-6" style="margin-top: 10px; color: black;"></i></button>';

    //             $rowData[] = $row;
    //         }

    //         $output = array(
    //             "draw" => request()->input('draw'),
    //             "recordsTotal" => sizeof($list),
    //             "recordsFiltered" => (new DynamicModelDataTableHelper(eloquentModel: new Employee(), dynamicConditions: $dynamicConditions, searchColumns: $searchColumns, searchRelationships: $searchRelationships))->countFilteredServerSideDataTable(),
    //             "data" => $rowData,
    //         );

    //         return json_encode($output);
    //     }

    //     $columns = [
    //         'S. No.',
    //         'Settlement ID',
    //         'Settlement Date',
    //         'Claim ID',
    //         'Amount',
    //         'Status',
    //         'Action'
    //     ];
    //     // D:\GitHUB\new_fixhr\resources\views\admin\ta-da-request\settlement.blade.php
    //     return view('admin.ta-da-request.settlement', compact('columns'));
    // }

    public function index(Request $request)
    {
        $user = Auth::user();
        $fromToDateFilter = request()->input('fromDate');
        $sheetStatusFilter = request()->input('reimburse_sheetStatusFilter');
        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['tr_b_id', $user->emp_b_id]
                ],
                ['method' => 'where', 'args' => ['tr_amount', '<=', 0]],
                [
                    'method' => 'select',
                    'args' => ['tr_id', 'tr_claims_id', 'tr_b_id',  'tr_unique_id', 'tr_status', 'tr_group_id', 'tr_amount', 'created_at','tr_date'],
                    'relation' => ['fh_business:b_id']
                ],
                [
                    'method' => 'sortBy',
                    'args' => ['tr_id', 'created_at', 'tr_b_id'],
                ],
            ];

            if (!empty($fromToDateFilter)) {
                $dates = explode(' - ', $fromToDateFilter);

                if (count($dates) == 2) {
                    $startDate = \Carbon\Carbon::createFromFormat('M d, Y', $dates[0])->startOfDay();
                    $endDate = \Carbon\Carbon::createFromFormat('M d, Y', $dates[1])->endOfDay();

                    $dynamicConditions[] = [
                        'method' => 'where',
                        'args' => ['created_at', '<=', $endDate->format('Y-m-d')]
                    ];

                    $dynamicConditions[] = [
                        'method' => 'where',
                        'args' => ['created_at', '>=', $startDate->format('Y-m-d')]
                    ];
                }
            }

            if ($sheetStatusFilter != '') {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['tr_status', $sheetStatusFilter]];
            }


            $searchColumns = ['tr_unique_id', 'tr_amount', 'created_at'];
            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new TadaReimburse(),
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
            ))->getServerSideDataTable();

            // dd($list);
            $rowData = [];
            $i = 0;
            foreach ($list as $key => $val) {
                $i++;
                $row = [];
                $row[] = $i;
                $row[] = $val->tr_unique_id ?? 'N/A';
                $row[] = $val->created_at? Carbon::parse($val->created_at)->format('d-M-Y'): 'N/A';
                $row[] = $val->tr_date? Carbon::parse($val->tr_date)->format('d-M-Y'): 'N/A';
                $row[] = $val->fh_claims->isNotEmpty()
                    ? collect($val->fh_claims)->map(function ($claim, $index) {
                        $badge = '<span class="badge bg-light text-black rounded-pill px-3 py-2 me-1 mb-1">'
                            . e($claim->tc_unique_id) . '</span>';
                        return $badge . ((($index + 1) % 5 === 0) ? '<br>' : '');
                    })->implode('')
                    : '';

                // Amount
                $row[] = round($val->tr_amount);
                $pendingCount = $val->fh_claims->where('tc_paid_status', '!=', 1)->count();
                if ($val->tr_status == 1) {
                    $status  = '<span class="badge bg-success me-1">Paid</span>';
                    if ($pendingCount > 0) {
                        $status .= '<span class="badge bg-danger">Pending: ' . $pendingCount . '</span>';
                    }
                } else {
                    $status = '<span class="badge bg-warning text-dark">Inprocessed</span>';
                }
                $row[] = $status;


                $row[] = '
                    <div class="btn-list ms-3">
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown">
                                <i class="fa fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu p-2" style="min-width: 180px;">
                                <li>
                                    <a href="' . route('reimburse.show', $val->tr_id) . '" 
                                    class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2">
                                        <i class="feather feather-eye"></i> View
                                    </a>
                                </li>
                                <li>
                                    <a href="' . route('reimburse.exportReport',  ['id' => $val->tr_id, 'type' => 'settlement']) . '" 
                                    class="dropdown-item text-warning fw-semibold d-flex align-items-center gap-2">
                                        <i class="las la-file-download"></i> Report
                                    </a>
                                </li>
                            
                            </ul>
                        </div>
                    </div>';

                $rowData[] = $row;
                }


            $output = [
                "draw" => $request->input('draw'),
                "recordsTotal" => sizeof($list),
                "recordsFiltered" => (new DynamicModelDataTableHelper(
                    eloquentModel: new TadaReimburse(),
                    dynamicConditions: $dynamicConditions,
                ))->countFilteredServerSideDataTable(),
                "data" => $rowData,
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

        return view('admin.ta-da-request.settlement', compact('columns'));
    }


    public function create()
    {
        //
    }

    
    public function store(Request $request)
    {
        //
    }

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {


    }

   
    public function update(Request $request, string $id)
    {
        // Validate the request data
        $request->validate([
            'finalSettlementAmount' => 'required|numeric|min:0',
        ]);

        // Find the employee or settlement by ID
        $employee = Employee::find($id);  // Assuming you're using Employee model
        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'Employee not found.']);
        }
        if ($employee->emp_tada_settlement_amt == 0) {
            return response()->json(['success' => false, 'message' => 'Settlement amount is already zero.']);
        }
        // Update the settlement amount
        $employee->emp_tada_settlement_amt = $employee->emp_tada_settlement_amt + $request->finalSettlementAmount;
        $employee->save();

        // Return a JSON response indicating success
        return response()->json(['success' => true, 'message' => 'Settlement updated successfully.']);
    }

    
    public function destroy(string $id)
    {
        //
    }
}
