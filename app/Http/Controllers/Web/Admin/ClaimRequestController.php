<?php

namespace App\Http\Controllers\Web\Admin;

use App\Helpers\RolePermissionLogics;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use App\Helpers\ApprovalHelper;
use App\Models\ApprovalLog;
use App\Models\DeductionLog;
use App\Models\ApprovalModule;
use App\Models\PolicyTadaCategory;
use Illuminate\Http\Request;
use App\Models\TadaClaim;
use App\Models\TadaMetroCity;
use App\Models\TadaReimburse;
use App\Models\TadaRequestDetail;
use App\Models\Employee;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use NumberToWords\NumberToWords;
use Illuminate\Support\Facades\Log;

class ClaimRequestController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }


    public function index(Request $request)
    {
        return $this->handleRequest($request, true, false, false);
    }
    public function index2(Request $request)
    {
        return $this->handleRequest($request, false, true, false);
    }

    public function index3(Request $request)
    {
        return $this->handleRequest($request, false, false, true);
    }

    private function handleRequest(Request $request, $isIndex1, $isIndex2, $isIndex3)
    {
        $user = Auth::user();
        $employeeFilter = request()->input('claim_employeeFilter');
        $branchFilter = request()->input('claim_branchFilter');
        $gradeFilter = request()->input('claim_gradeFilter');
        $statusFilter = request()->input('claim_activeFilter');
        $fromToDateFilter = request()->input('fromDate');

        if ($request->ajax()) {
            if ($isIndex2) {

                /*$uniqueIds = DB::table('tada_claim')
                    ->select('tc_unique_id')
                    ->where('tc_b_id', $user->emp_b_id)
                    ->groupBy('tc_unique_id')
                    ->havingRaw('COUNT(*) = 1')
                    ->pluck('tc_unique_id')
                    ->toArray(); */

                $dynamicConditions = [
                    [
                        'method' => 'where',
                        'args' => ['tc_b_id', $user->emp_b_id]
                    ],
                    [
                        'method' => 'whereNotIn',
                        'args' => ['tc_status', [139, 200]]
                    ],
                    [
                        'method' => 'whereNotIn',
                        'args' => ['tc_group_claim', [1]]
                    ],
                    [
                        'method' => 'where',
                        'args' => ['tc_stage_completed', '!=', 1]
                    ],
                    // [
                    //     'method' => 'whereIn',
                    //     'args' => ['tc_unique_id', $uniqueIds] 
                    // ],
                    [
                        'method' => 'select',
                        'args' => [
                            'tc_id',
                            'tc_unique_id',
                            'tc_b_id',
                            'tc_trp_id',
                            'tc_emp_id',
                            'tc_am_id',
                            'tc_amount',
                            'tc_claimed_amount',
                            'tc_module_id',
                            'tc_status',
                            'tc_next_approver',
                            'tc_remarks',
                            'tc_is_payed',
                            'tc_stage_completed',
                            'tc_group_claim',
                            'created_at',
                            'updated_at',
                        ],
                        'relation' => [
                            'fh_tada_request_plan',
                            'fh_employee:emp_code,emp_id,emp_full_name,emp_dg_id',
                            'fh_employee.fh_department:d_id,d_name',
                            'fh_employee.fh_designation:dg_id,dg_name',
                            'fh_tada_request_plan.fh_policy_tada_travel_type.fh_travel_type:m_id,m_name',
                            'fh_tada_request_plan.fh_policy_tada_category:ptc_id,ptc_name',
                            'fh_claim_status:m_id,m_name,m_other'
                        ]
                    ],
                    [
                        'method' => 'sortBy',
                        'args' => ['tc_id', 'tc_unique_id', 'tc_b_id', 'tc_trp_id', 'tc_emp_id', 'tc_amount', 'tc_claimed_amount', 'tc_module_id', 'tc_status']
                    ],
                ];
            } else if ($isIndex1) {
                /*$uniqueIds = DB::table('tada_claim')
                    ->select('tc_unique_id')
                    ->where('tc_b_id', $user->emp_b_id)
                    ->groupBy('tc_unique_id')
                    ->havingRaw('COUNT(*) = 1')
                    ->pluck('tc_unique_id')
                    ->toArray(); */

                $dynamicConditions = [
                     [
                        'method' => 'where',
                        'args' => ['tc_b_id', $user->emp_b_id]
                    ],
                    [
                        'method' => 'whereNotIn',
                        'args' => ['tc_status', [139, 200]]
                    ],
                    [
                        'method' => 'whereNotIn',
                        'args' => ['tc_group_claim', [1]]
                    ],
                    [
                        'method' => 'where',
                        'args' => ['tc_stage_completed', '!=', 1]
                    ],
                    /*[
                        'method' => 'whereIn',
                        'args' => ['tc_unique_id', $uniqueIds] 
                    ],*/
                    [
                        'method' => 'select',
                        'args' => [
                            'tc_id',
                            'tc_unique_id',
                            'tc_b_id',
                            'tc_trp_id',
                            'tc_emp_id',
                            'tc_am_id',
                            'tc_amount',
                            'tc_claimed_amount',
                            'tc_module_id',
                            'tc_status',
                            'tc_is_payed',
                            'tc_next_approver',
                            'tc_remarks',
                            'tc_stage_completed',
                            'tc_group_claim',
                            'created_at',
                            'updated_at',
                        ],
                        'relation' => [
                            'fh_tada_request_plan',
                            'fh_employee:emp_code,emp_id,emp_full_name,emp_dg_id,emp_d_id',
                            'fh_employee.fh_department:d_id,d_name',
                            'fh_employee.fh_designation:dg_id,dg_name',
                            'fh_tada_request_plan.fh_policy_tada_travel_type.fh_travel_type:m_id,m_name',
                            'fh_tada_request_plan.fh_policy_tada_category:ptc_id,ptc_name',
                            'fh_claim_status:m_id,m_name,m_other'
                        ]
                    ],
                    [
                        'method' => 'sortBy',
                        'args' => ['tc_id', 'tc_unique_id', 'tc_b_id', 'tc_trp_id', 'tc_emp_id', 'tc_amount', 'tc_claimed_amount', 'tc_module_id', 'tc_status', 'tc_id']
                    ],
                ];
            } else {
                /*$uniqueIds = DB::table('tada_claim')
                    ->select('tc_unique_id')
                    ->where('tc_b_id', $user->emp_b_id)
                    ->groupBy('tc_unique_id')
                    ->havingRaw('COUNT(*) = 1')
                    ->pluck('tc_unique_id')
                    ->toArray();*/
                $dynamicConditions = [
                    [
                        'method' => 'where',
                        'args' => ['tc_b_id', $user->emp_b_id]
                    ],
                    [
                        'method' => 'whereNotIn',
                        'args' => ['tc_status', [139, 200]]
                    ],
                    [
                        'method' => 'whereNotIn',
                        'args' => ['tc_group_claim', [1]]
                    ],
                    [
                        'method' => 'where',
                        'args' => ['tc_stage_completed', '!=', 1]
                    ],
                   /* [
                        'method' => 'whereIn',
                        'args' => ['tc_unique_id', $uniqueIds] 
                    ],*/

                    [
                        'method' => 'select',
                        'args' => [
                            'tc_id',
                            'tc_unique_id',
                            'tc_b_id',
                            'tc_trp_id',
                            'tc_emp_id',
                            'tc_am_id',
                            'tc_amount',
                            'tc_claimed_amount',
                            'tc_module_id',
                            'tc_status',
                            'tc_is_payed',
                            'tc_next_approver',
                            'tc_remarks',
                            'tc_stage_completed',
                            'tc_group_claim',
                            'created_at',
                            'updated_at',
                        ],
                        'relation' => [
                            'fh_tada_request_plan',
                            'fh_employee:emp_code,emp_id,emp_full_name,emp_dg_id,emp_d_id',
                            'fh_employee.fh_department:d_id,d_name',
                            'fh_employee.fh_designation:dg_id,dg_name',
                            'fh_tada_request_plan.fh_policy_tada_travel_type.fh_travel_type:m_id,m_name',
                            'fh_tada_request_plan.fh_policy_tada_category:ptc_id,ptc_name',
                            'fh_claim_status:m_id,m_name,m_other'
                        ]
                    ],
                    [
                        'method' => 'sortBy',
                        'args' => ['tc_id', 'tc_unique_id', 'tc_b_id', 'tc_trp_id', 'tc_emp_id', 'tc_amount', 'tc_claimed_amount', 'tc_module_id', 'tc_status', 'tc_id']
                    ],
                ];
            }

            // Filter conditions
            if ($employeeFilter != '') {
                $dynamicConditions[] = [
                    'method' => 'where',
                    'args' => ['tc_emp_id', $employeeFilter]
                ];
            }

            if ($branchFilter != '') {
                $dynamicConditions[] = [
                    'method' => 'whereRelation',
                    'parentMethod' => 'whereHas',
                    'childMethod' => 'where',
                    'args' => ['trp_br_id', $branchFilter],
                    'relation' => 'fh_tada_request_plan'
                ];
            }

            if ($gradeFilter != '') {
                $dynamicConditions[] =
                    [
                        'method' => 'whereHas',
                        'args' => ['emp_grade_id', $gradeFilter],
                        'relation' => 'fh_employee'
                    ];
            }

            if ($statusFilter != '') {
                $dynamicConditions[] = [
                    'method' => 'where',
                    'args' => ['tc_status', $statusFilter]
                ];
            }

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

            $searchColumns = ['tc_unique_id', 'tc_emp_id', 'tc_id', 'created_at', 'tc_status']; //'trp_name',
            $searchRelationships = [
                'fh_employee' => ['emp_full_name', 'emp_code'],
                'fh_employee.fh_department' => ['d_name'],
                'fh_employee.fh_designation' => ['dg_name'],
                'fh_tada_request_plan.fh_policy_tada_travel_type.fh_travel_type' => ['m_name'],
                'fh_tada_request_plan.fh_policy_tada_category' => ['ptc_name'],
                'fh_claim_status' => ['m_name'],
            ];

            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new TadaClaim(),
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
                searchRelationships: $searchRelationships
            ))->getServerSideDataTable();

            $rowData = array();
            $i = 0;
            foreach ($list as $key => $item) {

                $nextApproval = ApprovalHelper::getNextApprovalDetails(146, $item->tc_id, $item->tc_b_id);
                $approval = ApprovalHelper::checkApproval($user->emp_b_id, $item->tc_module_id, $item->tc_trp_id, optional($item->fh_employee)->emp_d_id, $item->tc_emp_id);


                $row = []; // Initialize the row array
                $employeeName = isset($item->fh_employee) ?  ($item->fh_employee->emp_code ?  $item->fh_employee->emp_code . ' - ' : '') . $item->fh_employee->emp_full_name  : '';
                $designation = isset($item->fh_employee->fh_designation) ? $item->fh_employee->fh_designation->dg_name : '';
                $row[] = '';

                $row[] = '<div class="d-flex dynamic-width">

                            <div class="me-3 mt-0 mt-sm-1 d-block">
                                <h6 class="mb-1 fs-14">' . $employeeName . '</h6>
                                <p class="text-muted mb-0 fs-12">' . $designation . '</p>
                            </div>
                        </div>';


                $row[] =  isset($item->fh_tada_request_plan->fh_policy_tada_travel_type->fh_travel_type) ?  $item->fh_tada_request_plan->fh_policy_tada_travel_type->fh_travel_type->m_name : '';
                $row[] =  isset($item->fh_tada_request_plan) ? ('#' . $item->fh_tada_request_plan->trp_unique_id) : '';
                $row[] = '#' . $item->tc_unique_id;
                $row[] = $item->tc_amount;
                $row[] = $item->created_at->format('d-M-Y H:i');

                $logData = $item->fh_approval_log_employee_wise;

                // Check if the log data is not empty
                $formattedLog = '';
                $comma = false;
                if (count($logData)) {
                    foreach ($logData as $log) {
                        if ($log->log_am_id == $item->tc_am_id) {
                            // Escape each piece of data for safe HTML output
                            $employeeName = isset($log->fh_employee->emp_full_name) ? htmlspecialchars($log->fh_employee->emp_full_name, ENT_QUOTES, 'UTF-8') : 'N/A';
                            $roleName = isset($log->fh_role->role_name) ? htmlspecialchars($log->fh_role->role_name, ENT_QUOTES, 'UTF-8') : 'N/A';
                            $statusName = isset($log->fh_status->m_name) ? htmlspecialchars($log->fh_status->m_name, ENT_QUOTES, 'UTF-8') : 'N/A';

                            // Format the log entry with HTML line breaks
                            $formattedLog .= ($comma ? ', ' : '') .  $employeeName . ' (' . $roleName . ') ' . $statusName;
                            $comma = true;
                        }
                    }
                }

                // Use a default message if $formattedLog is empty
                $popoverContent = $formattedLog === '' ? 'Awaiting' : $formattedLog;

                // Ensure the content is properly escaped for use in data attributes
                $safePopoverContent = htmlspecialchars($popoverContent, ENT_QUOTES, 'UTF-8');

                $jsonData = $item->fh_claim_status->m_other;

                // Decode the JSON to an associative array
                $decodedData = json_decode($jsonData, true); // true for associative array

                // Now access the color value
                $color = $decodedData['color'];
                $icon = $decodedData['web_icon'];

                $row[] = '<span class="badge" style="background-color:' . $color . '"><i class="' . $icon . '">&nbsp;</i>'
                    . (isset($item->fh_claim_status->m_name) ? $item->fh_claim_status->m_name : '')
                    . '</span> &nbsp;'
                    . '<i class="feather feather-info text-primary fs-14" data-bs-container="body" data-bs-content="' . $safePopoverContent
                    . '" data-bs-placement="right" data-bs-popover-color="default" data-bs-toggle="popover" title="Approval Log"> </i>'
                    // Show approval count only for statuses other than 140
                    . '<p class="text-muted mb-0 fs-12">Approval ' . ($approval['approvallog_count'] ?? ' ') . ' (' . ($approval['approvalcount'] ?? ' ') . ')</p>';

                if (!(isset($nextApproval['approver_name']) && $nextApproval['approver_name'])) {
                    $approvalMapping = ApprovalHelper::getApprovalMapping($user->emp_b_id, $item->tc_emp_id, $item->tc_module_id);
                    if ($approvalMapping) {
                        $approvalArray = ApprovalHelper::getApprovalArray($approvalMapping);


                        $approvalLog = $item?->fh_approval_log_employee_wise?->pluck('log_user_id')?->toArray() ?? [];
                        $diff = array_values(array_diff($approvalArray, $approvalLog));
                        if ($diff) {

                            $approverName = Employee::where('emp_id', $diff[0])->pluck('emp_full_name')->first() ??  '---';
                            // if($item->tc_id == 95){
                            //   dd($diff[0],$approverName);
                            // }
                        } else {
                            $approverName = '---';
                        }
                    } else {
                        $approverName = '---';
                    }
                }

                $row[] = '<span class="text-center">' .
                    (isset($nextApproval['approver_name']) && $nextApproval['approver_name']
                        ? $nextApproval['approver_name']
                        : ($approverName ?? '---')) .
                    '</span>';

                $encryptedId = Crypt::encrypt($item->tc_id);
                $deleteUrl = route('claim-request.destroy', $encryptedId);

                $revertButton = '';
                if ($user->emp_role_id == 1) {
                    $revertButton = '<li>
                        <button class="dropdown-item revert-button" data-id="' . $encryptedId . '" data-url="' . $deleteUrl . '">
                            <i class="fa fa-undo"></i> Revert
                        </button>
                    </li>';
                }

                $dropdownStart = '<div class="dropdown">
                    <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu p-2" style="min-width: 180px;">';

                $dropdownEnd = '</ul></div>';

                $actionLinks = '';

                if ($isIndex2) {
                    $actionLinks .= RolePermissionLogics::check_route_permission('admin/ta-da-request/claim-request/show/{id}', 116) ? '
                        <li>
                            <a class="dropdown-item text-primary" href="' . route('claim-request.request.show', md5($item->tc_id)) . '">
                                <i class="feather feather-eye"></i> View
                            </a>
                        </li>' : '';

                    $actionLinks .= RolePermissionLogics::check_route_permission('admin/ta-da-request/travel-claim/report/{id}', 116) ? '
                        <li>
                            <a class="dropdown-item text-info" href="' . route('travel.claim.report', md5($item->tc_id)) . '">
                                <i class="feather feather-file"></i> Report
                            </a>
                        </li>' : '';

                    $row[] = $dropdownStart . $actionLinks . $revertButton . $dropdownEnd;
                } elseif ($isIndex3) {
                    $actionLinks .= RolePermissionLogics::check_route_permission('admin/ta-da-request/claim-request-is-paid/show/{id}', 116) ? '
                        <li>
                            <a class="dropdown-item text-primary" href="' . route('claim-request-is-paid.request.show', md5($item->tc_id)) . '">
                                <i class="feather feather-eye"></i> View
                            </a>
                        </li>' : '';

                    $actionLinks .= RolePermissionLogics::check_route_permission('admin/ta-da-request/travel-claim/report/{id}', 116) ? '
                        <li>
                            <a class="dropdown-item text-info" href="' . route('travel.claim.report', md5($item->tc_id)) . '">
                                <i class="feather feather-file"></i> Report
                            </a>
                        </li>' : '';

                    $row[] = $dropdownStart . $actionLinks . $revertButton . $dropdownEnd;
                } elseif ($isIndex1) {
                    if (
                        ApprovalHelper::isApprovalCompleted($item->tc_b_id, $item->tc_module_id, $item->tc_trp_id, $item->fh_employee->emp_d_id)
                        && $item->tc_stage_completed == 1
                        && $item->tc_is_payed != 1
                    ) {
                        $actionLinks .= RolePermissionLogics::check_route_permission('admin/ta-da-request/claim/show/{id}', 116) ? '
                            <li>
                                <a class="dropdown-item text-primary" href="' . route('claim.request.show', md5($item->tc_id)) . '">
                                    <i class="feather feather-eye"></i> View
                                </a>
                            </li>' : '';

                        $actionLinks .= RolePermissionLogics::check_route_permission('admin/ta-da-request/travel-claim/report/{id}', 116) ? '
                            <li>
                                <a class="dropdown-item text-info" href="' . route('travel.claim.report', md5($item->tc_id)) . '">
                                    <i class="feather feather-file"></i> Report
                                </a>
                            </li>' : '';

                        $row[] = '<div class="d-flex align-items-center justify-content-between">' .
                            $dropdownStart . $actionLinks . $revertButton . $dropdownEnd . '
                                    <label class="custom-control custom-checkbox-md p-0 ms-2 mb-0">
                                        <input type="checkbox" class="custom-control-input-success select-checkbox testClass"
                                            name="claim_ids[]" value="' . Crypt::encrypt($item->tc_id) . '"
                                            onclick="selectCheckbox(this)">
                                        <span class="custom-control-label-md success"></span>
                                    </label>
                                </div>';
                    } else {
                        $actionLinks .= RolePermissionLogics::check_route_permission('admin/ta-da-request/claim/show/{id}', 116) ? '
                            <li>
                                <a class="dropdown-item text-primary" href="' . route('claim.request.show', md5($item->tc_id)) . '">
                                    <i class="feather feather-eye"></i> View
                                </a>
                            </li>' : '';

                        $actionLinks .= RolePermissionLogics::check_route_permission('admin/ta-da-request/travel-claim/report/{id}', 116) ? '
                            <li>
                                <a class="dropdown-item text-info" href="' . route('travel.claim.report', md5($item->tc_id)) . '">
                                    <i class="feather feather-file"></i> Report
                                </a>
                            </li>' : '';

                        $row[] = $dropdownStart . $actionLinks . $revertButton . $dropdownEnd;
                    }
                }
                $rowData[] = $row;
            }

            $output = array(
                'draw' => $request->input('draw'),
                'recordsTotal' =>  sizeof($list),
                'recordsFiltered' => (new DynamicModelDataTableHelper(
                    eloquentModel: new TadaClaim(),
                    dynamicConditions: $dynamicConditions,
                    searchColumns: $searchColumns,
                    searchRelationships: $searchRelationships
                ))->countFilteredServerSideDataTable(),
                'data' => $rowData,
            );

            return json_encode($output);
        }

        $columns = [
            'S.No.',
            'Emp. Name',
            'Travel Type',
            'Trip ID',
            'Claim ID',
            'Claim Amount',
            'Claim Date',
            'Status',
            'Submitted To',
            'Action',
        ];
        $actionRoute = request()->route()->getName();
        $titleRoute = $isIndex2 ? 'claim-request' : ($isIndex3 == true ? 'claim-request-is-paid' : 'claim');
        $title = $isIndex2 ? 'Claim Requests' : ($isIndex3 == true ? 'Mark Claim Is Reimbursed' : 'Claim Approval');
        return view('admin.ta-da-request.claim', compact('columns', 'title', 'titleRoute', 'actionRoute'));
    }

    public function approvedindex(Request $request)
    {
        $user = Auth::user();
        $employeeFilter = request()->input('claim_employeeFilter');
        $branchFilter = request()->input('claim_branchFilter');
        $gradeFilter = request()->input('claim_gradeFilter');
        $statusFilter = request()->input('claim_activeFilter');
        $fromToDateFilter = request()->input('fromDate');

        if ($request->ajax()) {

            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['tc_b_id', $user->emp_b_id]
                ],
                [
                    'method' => 'whereNotIn',
                    'args' => ['tc_status', [139, 200]]
                ],
                [
                    'method' => 'where',
                    'args' => ['tc_group_claim', '!=', 1]
                ],

                [
                    'method' => 'where',
                    'args' => ['tc_next_approver', 1]
                ],

                [
                    'method' => 'where',
                    'args' => ['tc_stage_completed', 1]
                ],


                [
                    'method' => 'select',
                    'args' => [
                        'tc_id',
                        'tc_unique_id',
                        'tc_b_id',
                        'tc_trp_id',
                        'tc_emp_id',
                        'tc_am_id',
                        'tc_amount',
                        'tc_claimed_amount',
                        'tc_module_id',
                        'tc_status',
                        'tc_is_payed',
                        'tc_next_approver',
                        'tc_remarks',
                        'tc_stage_completed',
                        'tc_group_claim',
                        'created_at',
                        'updated_at',
                    ],
                    'relation' => [
                        'fh_tada_request_plan',
                        'fh_employee:emp_code,emp_id,emp_full_name,emp_dg_id,emp_d_id',
                        'fh_employee.fh_department:d_id,d_name',
                        'fh_employee.fh_designation:dg_id,dg_name',
                        'fh_tada_request_plan.fh_policy_tada_travel_type.fh_travel_type:m_id,m_name',
                        'fh_tada_request_plan.fh_policy_tada_category:ptc_id,ptc_name',
                        'fh_claim_status:m_id,m_name,m_other'
                    ]
                ],
                [
                    'method' => 'sortBy',
                    'args' => ['tc_id', 'tc_unique_id', 'tc_b_id', 'tc_trp_id', 'tc_emp_id', 'tc_amount', 'tc_claimed_amount', 'tc_module_id', 'tc_status', 'tc_id']
                ],
            ];



            if ($employeeFilter != '') {
                $dynamicConditions[] = [
                    'method' => 'where',
                    'args' => ['tc_emp_id', $employeeFilter]
                ];
            }

            if ($branchFilter != '') {
                $dynamicConditions[] = [
                    'method' => 'whereRelation',
                    'parentMethod' => 'whereHas',
                    'childMethod' => 'where',
                    'args' => ['trp_br_id', $branchFilter],
                    'relation' => 'fh_tada_request_plan'
                ];
            }

            if ($gradeFilter != '') {
                $dynamicConditions[] =
                    [
                        'method' => 'whereHas',
                        'args' => ['emp_grade_id', $gradeFilter],
                        'relation' => 'fh_employee'
                    ];
            }

            if ($statusFilter != '') {
                $dynamicConditions[] = [
                    'method' => 'where',
                    'args' => ['tc_status', $statusFilter]
                ];
            }

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

            $searchColumns = ['tc_unique_id', 'tc_emp_id', 'tc_id', 'created_at', 'tc_status']; //'trp_name',
            $searchRelationships = [
                'fh_employee' => ['emp_full_name', 'emp_code'],
                'fh_employee.fh_department' => ['d_name'],
                'fh_employee.fh_designation' => ['dg_name'],
                'fh_tada_request_plan.fh_policy_tada_travel_type.fh_travel_type' => ['m_name'],
                'fh_tada_request_plan.fh_policy_tada_category' => ['ptc_name'],
                'fh_claim_status' => ['m_name'],
            ];

            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new TadaClaim(),
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
                searchRelationships: $searchRelationships
            ))->getServerSideDataTable();

            // dd($list);

            $rowData = array();
            $i = 0;


            $groupedClaims = [];
            $rowData = [];


            foreach ($list as $item) {
                if (!isset($item->tc_unique_id)) {
                    continue;
                }

                $uniqueId = $item->tc_unique_id;

                if (!isset($groupedClaims[$uniqueId])) {
                    $groupedClaims[$uniqueId] = [
                        'items' => [],
                        'total_amount' => 0,
                        'count' => 0,
                        'earliest_date' => $item->created_at,
                        'latest_status' => $item->tc_status,
                        'representative_item' => $item,
                        'has_completed_stage_and_approver' => false
                    ];
                }

                $groupedClaims[$uniqueId]['items'][] = $item;
                $groupedClaims[$uniqueId]['total_amount'] += $item->tc_amount ?? 0;
                $groupedClaims[$uniqueId]['count']++;

                if ($item->created_at < $groupedClaims[$uniqueId]['earliest_date']) {
                    $groupedClaims[$uniqueId]['earliest_date'] = $item->created_at;
                }

                if ($item->tc_status > $groupedClaims[$uniqueId]['latest_status']) {
                    $groupedClaims[$uniqueId]['latest_status'] = $item->tc_status;
                    $groupedClaims[$uniqueId]['representative_item'] = $item;
                }

                if (isset($item->tc_stage_completed) && isset($item->tc_next_approver) && $item->tc_stage_completed == 1 && $item->tc_next_approver == 1) {
                    $groupedClaims[$uniqueId]['has_completed_stage_and_approver'] = true;
                }
            }


            foreach ($groupedClaims as $uniqueId => $group) {
                if (!$group['has_completed_stage_and_approver']) {
                    Log::info("Skipping group $uniqueId: Conditions not met");
                    continue;
                }

                $item = $group['representative_item'];

                $nextApproval = ApprovalHelper::getNextApprovalDetails(146, $item->tc_id, $item->tc_b_id);
                $approval = ApprovalHelper::checkApproval($user->emp_b_id, $item->tc_module_id, $item->tc_trp_id, optional($item->fh_employee)->emp_d_id, $item->tc_emp_id);

                $row = [];
                $employeeName = isset($item->fh_employee) ? ($item->fh_employee->emp_code ? $item->fh_employee->emp_code . ' - ' : '') . $item->fh_employee->emp_full_name : '';
                $designation = isset($item->fh_employee->fh_designation) ? $item->fh_employee->fh_designation->dg_name : '';

                $row[] = '';
                $row[] = '<div class="d-flex dynamic-width"><div class="me-3 mt-0 mt-sm-1 d-block"><h6 class="mb-1 fs-14">' . $employeeName . '</h6><p class="text-muted mb-0 fs-12">' . $designation . '</p></div></div>';
                $row[] = isset($item->fh_tada_request_plan->fh_policy_tada_travel_type->fh_travel_type) ? $item->fh_tada_request_plan->fh_policy_tada_travel_type->fh_travel_type->m_name : '';
                $row[] = isset($item->fh_tada_request_plan) ? '#' . $item->fh_tada_request_plan->trp_unique_id : '';
                // $row[] = '#' . $uniqueId;

                if ($group['count'] > 1) {
                    $row[] = '#' . $uniqueId . '<br>' . '<span class="badge bg-primary">' . $group['count'] . ' claims</span>';
                } else {
                    $row[] = '#' . $uniqueId;
                }

                $row[] = $group['total_amount'];
                $row[] = $group['earliest_date']->format('d-M-Y H:i');

                $logData = $item->fh_approval_log_employee_wise;
                $formattedLog = '';
                $comma = false;

                if (!empty($logData)) {
                    foreach ($logData as $log) {
                        if ($log->log_am_id == $item->tc_am_id) {
                            $employeeName = isset($log->fh_employee->emp_full_name) ? htmlspecialchars($log->fh_employee->emp_full_name, ENT_QUOTES, 'UTF-8') : 'N/A';
                            $roleName = isset($log->fh_role->role_name) ? htmlspecialchars($log->fh_role->role_name, ENT_QUOTES, 'UTF-8') : 'N/A';
                            $statusName = isset($log->fh_status->m_name) ? htmlspecialchars($log->fh_status->m_name, ENT_QUOTES, 'UTF-8') : 'N/A';
                            $formattedLog .= ($comma ? ', ' : '') . $employeeName . ' (' . $roleName . ') ' . $statusName;
                            $comma = true;
                        }
                    }
                }

                $popoverContent = $formattedLog ?: 'Awaiting';
                $safePopoverContent = htmlspecialchars($popoverContent, ENT_QUOTES, 'UTF-8');

                $color = '#4CAF50';
                $icon = 'fa fa-check';
                $row[] = '<span class="badge" style="background-color:' . $color . '"><i class="' . $icon . '">&nbsp;</i>' . (isset($item->fh_claim_status->m_name) ? $item->fh_claim_status->m_name : '') . '</span> &nbsp;<i class="feather feather-info text-primary fs-14" data-bs-container="body" data-bs-content="' . $safePopoverContent . '" data-bs-placement="right" data-bs-toggle="popover" title="Approval Log"></i><p class="text-muted mb-0 fs-12">Approval ' . ($approval['approvallog_count'] ?? ' ') . ' (' . ($approval['approvalcount'] ?? ' ') . ')</p>';

                $approverName = isset($nextApproval['approver_name']) && $nextApproval['approver_name'] ? $nextApproval['approver_name'] : null;
                if (!$approverName) {
                    $approvalMapping = ApprovalHelper::getApprovalMapping($user->emp_b_id, $item->tc_emp_id, $item->tc_module_id);
                    if ($approvalMapping) {
                        $approvalArray = ApprovalHelper::getApprovalArray($approvalMapping);
                        $approvalLog = $item?->fh_approval_log_employee_wise?->pluck('log_user_id')?->toArray() ?? [];
                        $diff = array_values(array_diff($approvalArray, $approvalLog));
                        $approverName = $diff ? Employee::where('emp_id', $diff[0])->pluck('emp_full_name')->first() ?? '---' : '---';
                    } else {
                        $approverName = '---';
                    }
                }
                $row[] = '<span class="text-center">' . $approverName . '</span>';

                $encryptedId = Crypt::encrypt($item->tc_id);
                $deleteUrl = route('claim-request.destroy', $encryptedId);

                $revertButton = $user->emp_role_id == 1 ? '<li><button class="dropdown-item revert-button" data-id="' . $encryptedId . '" data-url="' . $deleteUrl . '"><i class="fa fa-undo"></i> Revert</button></li>' : '';

                $dropdownStart = '<div class="dropdown"><button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fa fa-ellipsis-v"></i></button><ul class="dropdown-menu p-2" style="min-width: 180px;">';
                $dropdownEnd = '</ul></div>';

                $actionLinks = '';

                if ($group['count'] > 1) {
                    // If multiple claims in the group → group view
                    $actionLinks .= RolePermissionLogics::check_route_permission('admin/ta-da-request/claim/show/{id}', 116)
                        ? '<li><a class="dropdown-item text-primary" href="' . route('claim-group.request.show', md5($uniqueId)) . '"><i class="feather feather-eye"></i> View</a></li>'
                        : '';
                } else {
                    // Single claim → normal view
                    $actionLinks .= RolePermissionLogics::check_route_permission('admin/ta-da-request/claim/show/{id}', 116)
                        ? '<li><a class="dropdown-item text-primary" href="' . route('claim.request.show', md5($item->tc_id)) . '"><i class="feather feather-eye"></i> View</a></li>'
                        : '';
                }


                $actionLinks .= RolePermissionLogics::check_route_permission('admin/ta-da-request/travel-claim/report/{id}', 116) ? '<li><a class="dropdown-item text-info" href="' . route('travel.claim.report', md5($item->tc_id)) . '"><i class="feather feather-file"></i> Report</a></li>' : '';

                // if (ApprovalHelper::isApprovalCompleted($item->tc_b_id, $item->tc_module_id, $item->tc_trp_id, $item->fh_employee->emp_d_id) && $item->tc_stage_completed == 1 && $item->tc_is_payed != 1) {
                //     $row[] = '<div class="d-flex align-items-center justify-content-between">' . $dropdownStart . $actionLinks . $revertButton . $dropdownEnd . '<label class="custom-control custom-checkbox-md p-0 ms-2 mb-0"><input type="checkbox" class="custom-control-input-success select-checkbox testClass" name="claim_ids[]" value="' . Crypt::encrypt($item->tc_id) . '" onclick="selectCheckbox(this)"><span class="custom-control-label-md success"></span></label></div>';
                // } else {
                //     $row[] = $dropdownStart . $actionLinks . $revertButton . $dropdownEnd;
                // }

                
            $duplicateIds = TadaClaim::where('tc_unique_id', $item->tc_unique_id)
                ->pluck('tc_id')
                ->toArray();
            // dd($duplicateIds);

            if (
                ApprovalHelper::isApprovalCompleted($item->tc_b_id, $item->tc_module_id, $item->tc_trp_id, $item->fh_employee->emp_d_id) &&
                $item->tc_stage_completed == 1 &&
                $item->tc_is_payed != 1
            ) {
                if (count($duplicateIds) > 1) {
                    // multiple IDs ko ek saath bhejna
                  $row[] = '<div class="d-flex align-items-center ">'
                          . $dropdownStart . $actionLinks . $revertButton . $dropdownEnd
                     . '<label class="custom-control custom-checkbox-md p-0 ms-7 mb-2">'
                          . '<input type="checkbox" class="custom-control-input-success select-checkbox testClass" '
                        . 'name="claim_ids[]" value="' . Crypt::encrypt(json_encode($duplicateIds)) . '" '
                        . 'onclick="selectCheckbox(this)">'
                        . '<span class="custom-control-label-md success"></span></label></div>';
                } else {
                    // single ID case
                   $row[] = '<div class="d-flex align-items-center ">'
                        . $dropdownStart . $actionLinks . $revertButton . $dropdownEnd
                        . '<label class="custom-control custom-checkbox-md p-0 ms-7 mb-2 ps-5">'
                        . '<input type="checkbox" class="custom-control-input-success select-checkbox testClass" '
                        . 'name="claim_ids[]" value="' . Crypt::encrypt($item->tc_id) . '" '
                        . 'onclick="selectCheckbox(this)">'
                        . '<span class="custom-control-label-md success"></span></label></div>';
                }
            } else {
                $row[] = $dropdownStart . $actionLinks . $revertButton . $dropdownEnd;
            }




                $rowData[] = $row;
            }



            // end code 

            $output = array(
                'draw' => $request->input('draw'),
                'recordsTotal' =>  sizeof($list),
                'recordsFiltered' => (new DynamicModelDataTableHelper(
                    eloquentModel: new TadaClaim(),
                    dynamicConditions: $dynamicConditions,
                    searchColumns: $searchColumns,
                    searchRelationships: $searchRelationships
                ))->countFilteredServerSideDataTable(),
                'data' => $rowData,
            );

            return json_encode($output);
        }

        $columns = [
              ['name' => 'S.No.', 'width' => '5%'],
            ['name' => 'Emp. Name', 'width' => '20%'],
            ['name' => 'Travel Type', 'width' => '8%'],
            ['name' => 'Trip ID', 'width' => '8%'],
            ['name' => 'Claim ID', 'width' => '8%'],
            ['name' => 'Claim Amount', 'width' => '8%'],
            ['name' => 'Claim Date', 'width' => '8%'],
            ['name' => 'Status', 'width' => '8%'],
            ['name' => 'Submitted To', 'width' => '8%'],
            ['name' => 'Action', 'width' => '8%'],
        ];
        $actionRoute = request()->route()->getName();
        $titleRoute = ('approved-claim');
        $title = ('Approved Claim');
        return view('admin.ta-da-request.approved_claim', compact('columns', 'title', 'titleRoute', 'actionRoute'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //     $user = Auth::user();
    //     $claimIds = $request->claim_ids;

    //     // Assuming $claimIds is an array of encrypted IDs
    //     $decryptedClaimIds = array_map(function ($claimId) {
    //         return Crypt::decrypt($claimId);
    //     }, $claimIds);
    //     $claimData = TadaClaim::whereIn('tc_id', $decryptedClaimIds)->with('fh_tada_request_plan.fh_tada_expenses')->get();

    //     $total_net_payed_amount = 0;
    //     foreach ($claimData as $claimRecord) {

    //         $net_payed_amount = $claimRecord->tc_amount - (($claimRecord->fh_tada_request_plan->trp_advance_allowance ?? 0) + ($claimRecord->tc_deduction_amount ?? 0));
    //         $total_net_payed_amount += $net_payed_amount;


    //         $claimRecord->update([
    //             'tc_payed_amount' => $net_payed_amount,
    //             'tc_is_payed' => 1,
    //             'tc_status' => 200,
    //         ]);

    //         ($net_payed_amount < 0 && $claimRecord->fh_employee) ?
    //             $claimRecord->fh_employee->update(['emp_tada_settlement_amt' => $claimRecord->fh_employee->emp_tada_settlement_amt + $net_payed_amount])
    //             : '';

    //         ApprovalLog::create([
    //             'log_am_id' => $claimRecord->tc_am_id,
    //             'log_request_id' => $claimRecord->tc_id,
    //             'log_user_id' => $user->emp_id,
    //             'log_user_role_id' => $user->emp_role_id,
    //             'log_status' => 200,
    //             'log_description' => 'Mark Claim as Reimbursed',
    //         ]);
    //     } 

    //     TadaReimburse::create([
    //         'tr_b_id' => $user->emp_b_id,
    //         'tr_claims_id' => $decryptedClaimIds, // Store as JSON
    //         'tr_amount' => $total_net_payed_amount,
    //     ]);

    //     $responseData = [
    //         'message' => 'Claims marked as reimbursed.',
    //         'success' => true
    //     ];

    //     return response()->json($responseData);
    // }


    // public function store(Request $request)
    // {
    //     $user = Auth::user();
    //     $claimIds = $request->claim_ids;
        

    //     // Decrypt claim IDs
    //     // $decryptedClaimIds = array_map(function ($claimId) {
    //     //     return Crypt::decrypt($claimId);
    //     // }, $claimIds);



    //     // Decrypt claim IDs
    //     $decryptedClaimIds = [];

    //     foreach ($claimIds as $claimId) {
    //         $decoded = Crypt::decrypt($claimId);

    //         // Agar JSON array string hai to decode karke merge kar do
    //         if (is_string($decoded) && str_starts_with($decoded, '[')) {
    //             $decodedArray = json_decode($decoded, true);
    //             if (is_array($decodedArray)) {
    //                 $decryptedClaimIds = array_merge($decryptedClaimIds, $decodedArray);
    //             }
    //         } else {
    //             // single id case
    //             $decryptedClaimIds[] = $decoded;
    //         }
    //     }

    //     // dd($request->all(), $decryptedClaimIds);

    //     // Get all claims
    //     $claimData = TadaClaim::whereIn('tc_id', $decryptedClaimIds)
    //         ->with('fh_tada_request_plan', 'fh_employee')
    //         ->get();

    //     $total_net_payed_amount = 0;
    //     $positiveClaimIds = [];


    //     $prefix = 'REM';
    //     $lastRecord = TadaReimburse::where('tr_unique_id', 'like', $prefix . '%')
    //         ->orderBy('tr_id', 'desc')
    //         ->first();

    //     if ($lastRecord && preg_match('/^' . $prefix . '([A-Z]{2})(\d{4})$/', $lastRecord->tr_unique_id, $matches)) {
    //         $letters = $matches[1];
    //         $number = intval($matches[2]);

    //         if ($number >= 9999) {
    //             $letters = $this->incrementLetters($letters);
    //             $number = 1;
    //         } else {
    //             $number++;
    //         }
    //     } else {
    //         $letters = 'AA';
    //         $number = 1;
    //     }

    //     $uniqueId = $prefix . $letters . str_pad($number, 4, '0', STR_PAD_LEFT);


    //     $lastReimburse = TadaReimburse::orderBy('tr_id', 'desc')->first();
    //     $nextId = $lastReimburse ? $lastReimburse->tr_id + 1 : 1;
    //     $groupId = 'REB' . str_pad($nextId, 6, '0', STR_PAD_LEFT);

    //     foreach ($claimData as $claimRecord) {
    //         $net_payed_amount = $claimRecord->tc_amount
    //             - (($claimRecord->fh_tada_request_plan->trp_advance_allowance ?? 0)
    //                 + ($claimRecord->tc_deduction_amount ?? 0));

    //         // ✅ Update claim
    //         $claimRecord->update([
    //             'tc_payed_amount' => $net_payed_amount,
    //             'tc_is_payed' => 1,
    //             'tc_status' => 200,
    //         ]);

    //         // ✅ Update employee settlement if negative
    //         if ($net_payed_amount < 0 && $claimRecord->fh_employee) {
    //             $claimRecord->fh_employee->update([
    //                 'emp_tada_settlement_amt' =>
    //                 $claimRecord->fh_employee->emp_tada_settlement_amt + $net_payed_amount
    //             ]);
    //         }

    //         // ✅ Approval log
    //         ApprovalLog::create([
    //             'log_am_id' => $claimRecord->tc_am_id,
    //             'log_request_id' => $claimRecord->tc_id,
    //             'log_user_id' => $user->emp_id,
    //             'log_user_role_id' => $user->emp_role_id,
    //             'log_status' => 200,
    //             'log_description' => 'Mark Claim as Reimbursed',
    //         ]);

    //         // ✅ If amount is negative → separate row
    //         if ($net_payed_amount < 0) {
    //             TadaReimburse::create([
    //                 'tr_unique_id' => $uniqueId,
    //                 'tr_group_id' => $groupId,
    //                 'tr_b_id' => $user->emp_b_id,
    //                 'tr_claims_id' => [$claimRecord->tc_id],
    //                 'tr_amount' => $net_payed_amount,
    //             ]);
    //         } else {
    //             // ✅ Collect positive claim IDs
    //             $positiveClaimIds[] = $claimRecord->tc_id;
    //             $total_net_payed_amount += $net_payed_amount;
    //         }
    //     }

    //     // ✅ If any positive claims → one row entry
    //     if (!empty($positiveClaimIds)) {
    //         TadaReimburse::create([
    //             'tr_unique_id' => $uniqueId,
    //             'tr_group_id' => $groupId,
    //             'tr_b_id' => $user->emp_b_id,
    //             'tr_claims_id' => $positiveClaimIds,
    //             'tr_amount' => $total_net_payed_amount,
    //         ]);
    //     }

    //     return response()->json([
    //         'message' => 'Claims marked as reimbursed.',
    //         'success' => true,
    //     ]);
    // }

    public function store(Request $request)
    {
        $user = Auth::user();
    
        // 🛡️ Validate input
        $request->validate([
            'claim_ids' => 'required|array|min:1',
            'claim_ids.*' => 'required|string'
        ]);
    
        // 🛡️ Decrypt claim IDs
        $decryptedClaimIds = [];
        foreach ($request->claim_ids as $claimId) {
            $decoded = Crypt::decrypt($claimId);
    
            if (is_string($decoded) && str_starts_with($decoded, '[')) {
                $decodedArray = json_decode($decoded, true);
                if (is_array($decodedArray)) {
                    $decryptedClaimIds = array_merge($decryptedClaimIds, $decodedArray);
                }
            } else {
                $decryptedClaimIds[] = $decoded;
            }
        }
    
        DB::beginTransaction();
        try {
            // ✅ Fetch claims only for the current user's business
            $claimData = TadaClaim::whereIn('tc_id', $decryptedClaimIds)
                ->where('tc_b_id', $user->emp_b_id)
                ->with('fh_tada_request_plan', 'fh_employee')
                ->get();
    
            if ($claimData->isEmpty()) {
                return response()->json([
                    'message' => 'No valid claims found for this business.',
                    'success' => false
                ], 404);
            }
    
            // ✅ Generate unique IDs
            $prefix = 'REM';
            $lastRecord = TadaReimburse::where('tr_b_id', $user->emp_b_id)
                ->where('tr_unique_id', 'like', $prefix.'%')
                ->orderBy('tr_id', 'desc')
                ->first();
    
            if ($lastRecord && preg_match('/^'.$prefix.'([A-Z]{2})(\d{4})$/', $lastRecord->tr_unique_id, $matches)) {
                [$letters, $number] = [$matches[1], intval($matches[2])];
                if ($number >= 9999) {
                    $letters = $this->incrementLetters($letters);
                    $number = 1;
                } else {
                    $number++;
                }
            } else {
                $letters = 'AA';
                $number = 1;
            }
            $uniqueId = $prefix.$letters.str_pad($number, 4, '0', STR_PAD_LEFT);
    
            $lastReimburse = TadaReimburse::where('tr_b_id', $user->emp_b_id)
                ->orderBy('tr_id', 'desc')->first();
            $nextId = $lastReimburse ? $lastReimburse->tr_id + 1 : 1;
            $groupId = 'REB'.str_pad($nextId, 6, '0', STR_PAD_LEFT);
    
            $positiveClaimIds = [];
            $totalNetPayed = 0;
    
            foreach ($claimData as $claim) {
                $net = $claim->tc_amount
                    - (($claim->fh_tada_request_plan->trp_advance_allowance ?? 0)
                    + ($claim->tc_deduction_amount ?? 0));
    
                $claim->update([
                    'tc_payed_amount' => $net,
                    'tc_is_payed' => 1,
                    'tc_status' => 200,
                ]);
    
                if ($net < 0 && $claim->fh_employee) {
                    $claim->fh_employee->increment('emp_tada_settlement_amt', $net);
                }
    
                ApprovalLog::create([
                    'log_am_id' => $claim->tc_am_id,
                    'log_request_id' => $claim->tc_id,
                    'log_user_id' => $user->emp_id,
                    'log_user_role_id' => $user->emp_role_id,
                    'log_status' => 200,
                    'log_description' => 'Mark Claim as Reimbursed',
                ]);
    
                if ($net < 0) {
                    TadaReimburse::create([
                        'tr_unique_id' => $uniqueId,
                        'tr_group_id' => $groupId,
                        'tr_b_id' => $user->emp_b_id,
                        'tr_claims_id' => [$claim->tc_id],
                        'tr_amount' => $net,
                    ]);
                } else {
                    $positiveClaimIds[] = $claim->tc_id;
                    $totalNetPayed += $net;
                }
            }
    
            if (!empty($positiveClaimIds)) {
                TadaReimburse::create([
                    'tr_unique_id' => $uniqueId,
                    'tr_group_id' => $groupId,
                    'tr_b_id' => $user->emp_b_id,
                    'tr_claims_id' => $positiveClaimIds,
                    'tr_amount' => $totalNetPayed,
                ]);
            }
    
            DB::commit();
    
            return response()->json([
                'message' => 'Claims marked as reimbursed.',
                'success' => true
            ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to mark claims as reimbursed.',
                'error' => $e->getMessage(),
                'success' => false
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id) //
    {
        $user = Auth::user();
        $actionRoute = request()->route()->getName();
        $claimData = TadaClaim::with(
            'fh_employee:emp_id,emp_full_name,emp_fname,emp_mname,emp_lname,emp_b_id,emp_dg_id,emp_d_id,emp_grade_id,emp_phone,emp_code,emp_email,emp_status,emp_profile_photo',
            'fh_employee.fh_department:d_id,d_name',
            'fh_employee.fh_designation:dg_id,dg_name',
            'fh_tada_request_plan',
            'fh_tada_request_plan.fh_policy_tada_category:ptc_id,ptc_name',
            'fh_tada_request_plan.fh_policy_tada_travel_type.fh_travel_type:m_id,m_name',
            'fh_tada_request_plan.fh_branch:br_id,br_name',
            'fh_tada_request_plan.fh_tada_expenses',
            'fh_tada_request_plan.fh_tada_expenses.fh_sub_expense',
            'fh_tada_request_plan.fh_tada_expenses.fh_expense_type',
            'fh_claim_status:m_id,m_name,m_other',
            'fh_deduction_log:dlog_id,dlog_am_id,dlog_tc_id,dlog_user_id,dlog_user_role_id,dlog_deduction_amount,dlog_remarks,dlog_requester_action',
            'fh_approval_log',
            'fh_approval_log_employee_wise',

        )->where('tc_b_id', $user->emp_b_id)
            ->where((DB::raw('md5(tc_id)')), $id)
            ->first();

       /* // Previous Code 
            $approvalData = ApprovalHelper::getApprovalOrRejectionData($claimData->tc_trp_id, $claimData->tc_status, $claimData->tc_am_id, NULL, 146);
    
            $displayDeductionHandler = $claimData->fh_deduction_log->whereNull('dlog_requester_action')->first();
        */
        

        // Handle both hierarchy-wise and employee-wise approval flows
        if ($claimData->tc_am_id) {
            // Hierarchy-wise approval flow
            $approvalData = ApprovalHelper::getApprovalOrRejectionData($claimData->tc_trp_id, $claimData->tc_status, $claimData->tc_am_id, NULL, 146);
        } else {
            // Employee-wise approval flow - custom logic for claims
            $approvalData = null;
            
            // Check if claim is not completed
            if ($claimData->tc_stage_completed != 1) {
                // Get employee approval mapping
                $approvalMapping = ApprovalHelper::getApprovalMapping($user->emp_b_id, $claimData->tc_emp_id, $claimData->tc_module_id);
                
                if ($approvalMapping) {
                    $approvalMappingArray = ApprovalHelper::getApprovalArray($approvalMapping);
                    $approvalLog = $claimData->fh_approval_log_employee_wise->pluck('log_user_id')->toArray() ?? [];
                    
                    // Check for declined deduction scenario
                    $logData = $claimData->fh_approval_log_employee_wise->sortByDesc('log_id')->first();
                    $requesterActPending = $logData?->fh_deductionLog;
                    
                    // Check if this is the last approver in the sequence
                    $isLastApprover = false;
                    $currentUserPosition = array_search($user->emp_id, $approvalMappingArray);
                    if ($currentUserPosition !== false) {
                        $isLastApprover = ($currentUserPosition === count($approvalMappingArray) - 1);
                    }
                    
                    $canApprove = false;
                    if ($requesterActPending && $requesterActPending->dlog_requester_action == 0) {
                        // Deduction declined scenario - check if user can re-approve
                        $isDeductionCreator = $requesterActPending->dlog_user_id == $user->emp_id;
                        $isNextApproverEmployeeWise = ApprovalHelper::canApprove($approvalMappingArray, $approvalLog, $user->emp_id);
                        $canApprove = $isDeductionCreator || $isNextApproverEmployeeWise;
                    } else {
                        // Normal approval flow - but check stage completion logic
                        if ($claimData->tc_stage_completed == 1) {
                            // If stage is already completed, check if we need to reopen for deduction decline
                            $hasDeductionPending = $claimData->fh_deduction_log->whereNull('dlog_requester_action')->exists();
                            if ($hasDeductionPending) {
                                // Stage should not be completed if there's pending deduction
                                $claimData->update(['tc_stage_completed' => 0]);
                            }
                            $canApprove = false; // Don't allow approval if stage is completed
                        } else {
                            // Normal approval flow
                            $canApprove = ApprovalHelper::canApprove($approvalMappingArray, $approvalLog, $user->emp_id);
                        }
                    }
                    
                    if ($canApprove) {
                        // Get the current user's approval status from EmployeeApprovalMapping
                        $mapData = \App\Models\EmployeeApprovalMapping::where([
                            'eam_module_id' => $claimData->tc_module_id, 
                            'eam_emp_id' => $claimData->tc_emp_id
                        ])->first();
                        
                        $approverStatus = null;
                        if ($mapData) {
                            // Get the approval status for current user
                            $statusData = $mapData->approvalStatuses->where('eas_approvel_id', $user->emp_id)->first();
                            if ($statusData) {
                                $approverStatus = \App\Models\MasterTable::where('m_group', 'APPROVAL_STATUS')
                                    ->where('m_id', $statusData->eas_approvel_status)
                                    ->first();
                            }
                        }
                        
                        // If no specific status found, use default approved status
                        if (!$approverStatus) {
                            $approverStatus = \App\Models\MasterTable::where('m_group', 'APPROVAL_STATUS')
                                ->whereIn('m_id', [157, 140])
                                ->first() ?: (object)['m_id' => 157, 'm_name' => 'Approved'];
                        }
                        
                        // Create approval data object for employee-wise approval flow
                        $approvalData = (object)[
                            'can_approve' => true,
                            'pa_emp_id' => $user->emp_id,
                            'pa_type' => 'single',
                            'pa_sequence' => $currentUserPosition + 1,
                            'pa_last' => $isLastApprover ? 1 : 0,
                            'pa_am_id' => $claimData->tc_module_id,
                            'pa_status_id' => $approverStatus->m_id,
                            'employee_wise_approval' => true,
                            'is_last_approver' => $isLastApprover,
                            'approval_mapping_array' => $approvalMappingArray,
                            'current_approval_log' => $approvalLog,
                            'fh_approver_status' => $approverStatus
                        ];
                    }
                }
            }
        }

        $displayDeductionHandler = $claimData->fh_deduction_log->whereNull('dlog_requester_action')->first();

        // Debug: Check approval data for both flows (remove this when testing is complete)
        // dd($approvalData, $displayDeductionHandler, $claimData->tc_am_id ? 'Hierarchy-wise' : 'Employee-wise');

        $workingHour = 8;

        $nextApprovalData = DB::table('next_approval_details')->where('nxt_tc_id', $claimData->tc_id)->first();

        //We are also storing the claim approval log based on the plan's primary ID.
        if ($claimData->tc_am_id) { //This is for approval based on hierarchy
            $claimApproval = ApprovalLog::where('log_request_id', $claimData->tc_trp_id)
                ->where(function ($query) use ($claimData) {
                    $query->where('log_am_id', $claimData->tc_am_id)
                        ->orWhere('log_module_id', $claimData->tc_module_id);
                })
                ->get();
        } else { //This is for employee-wise approval.
            $claimApproval = $claimData->fh_approval_log_employee_wise;
        }


        $claimDeduction = DeductionLog::whereNotNull('dlog_requester_action')
            ->where(DB::raw('md5(dlog_tc_id)'), $id)
            ->get();
        $combinedLog = $claimApproval->merge($claimDeduction);

        if (request()->ajax()) {
            $rawPlanData = $claimData->fh_tada_request_plan;
            $rawTravelTypeLocal = $rawPlanData->fh_policy_tada_travel_type->fh_travel_type->m_id == 124;
            $rawTravelDetails = $rawPlanData->fh_tada_request_details;
            $rawTravelDetailSumAmt = $rawTravelTypeLocal
                ? $rawTravelDetails->sum('trd_net_amount')
                : TadaRequestDetail::whereHas('fh_policy_tada_travel_vehicle', function ($query) {
                    $query->where('pttv_claim_type_id', 155);
                })
                ->where('trd_trp_id', $claimData->fh_tada_request_plan->trp_id) // Assuming you are filtering based on the related detail's ID.
                ->sum('trd_net_amount');
            $response = '
            <div class="row">
                <div class="col-md-12">
                    <div class="card overflow-hidden">
                        <div class="card-body">
                            <div class="card-body ps-0 pe-0">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <span>Claim ID:</span><br>
                                        <strong>' . e($claimData->tc_unique_id) . '</strong>
                                    </div>
                                    <div class="col-sm-6 text-end">
                                        <span>Claimed Date:</span><br>
                                        <strong>' . e(\Carbon\Carbon::parse($claimData->created_at)->format('d-M-Y')) . '</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive push">
                                <table class="table table-bordered table-hover text-nowrap">
                                    <tr>
                                        <th class="text-center" style="width: 1%">S.No.</th>
                                        <th>Expenses</th>
                                        <th class="text" style="width: 1%">Amount</th>
                                        <th class="text" style="width: 1%">Deviation</th>
                                        <th class="text" style="width: 1%">Payable Amount</th>';

            // Check if there is approval data and the deduction handler is not to be displayed
            if ($approvalData && !$displayDeductionHandler) {
                $response .= '<th class="text" style="width: 1%">Deduction</th>';
            }

            $response .= '</tr>';

            // Loop through the grouped expenses
            $iteration = 1; // for manual iteration since $loop->iteration is not available
            foreach ($claimData->fh_tada_request_plan->fh_tada_expenses->where('te_paid_by', 'self')->groupBy('fh_expense_type.m_name') as $expenseType => $expenses) {
                $exp_type_id = $expenses->pluck('te_type_id')->first();
                $amount = round($expenses->sum('te_amount') + $expenses->sum('te_taxes'));
                $deviation = round($expenses->sum('te_deviation'));
                $payableAmount = $amount - $deviation;

                $response .= '
                <tr>
                    <td>' . $iteration++ . '</td>
                    <td>' . e($expenseType) . '</td>
                    <td>₹' . $amount . '</td>
                    <td>₹' . $deviation . '</td>
                    <td>₹' . $payableAmount . '</td>';

                // If approval data exists and deduction handler is not displayed
                if ($approvalData && !$displayDeductionHandler) {
                    $response .= '
                    <td>
                        <input type="number"
                               name="deduction[' . $exp_type_id . ']"
                               id="' . $exp_type_id . '_deduction"
                               data-payableAmount="' . $payableAmount . '"
                               class="form-control deduction-input"
                               step="0.01" min="0"
                               placeholder="₹ 0">
                    </td>';
                }

                $response .= '</tr>';
            }

            // Handle TA, DA, Subtotal, Advance, Deduction, and Net Payable Amount
            if ($rawPlanData->trp_is_details_added) {
                $response .= '
                <tr>
                    <td colspan="4" class="font-weight-semibold text-end">TA</td>
                    <td>₹' . round($rawTravelDetailSumAmt) . '</td>
                    <td></td>
                </tr>';
            }

            $response .= '
            <tr>
                <td colspan="4" class="font-weight-semibold text-end">DA</td>
                <td>₹' . ($claimData->tc_da_amount ?? 0) . '</td>
                <td></td>
            </tr>

            <tr>
                <td colspan="4" class="font-weight-semibold text-end">
                    Subtotal<span> Total Expense Amount (Inc. Taxes), Travel Allowance, and DA) after Deduction of Deviation.
                </td>
                <td>₹' . $claimData->tc_amount . '</td>
                <td></td>
            </tr>

            <tr>
                <td colspan="4" class="font-weight-semibold text-end">Advance</td>
                <td>₹' . ($claimData->fh_tada_request_plan->trp_advance_allowance ?? 0) . '</td>
                <td></td>
            </tr>

            <tr>
                <td colspan="4" class="font-weight-semibold text-end">Deduction</td>
                <td>₹' . ($claimData->tc_deduction_amount ?? 0) . '</td>
                <td></td>
            </tr>

            <tr>
                <td colspan="4" class="font-weight-bold text-uppercase text-end h4 mb-0">Net Payable Amount</td>
                <td class="font-weight-bold h4 mb-0">₹' . ($claimData->tc_amount - (($claimData->fh_tada_request_plan->trp_advance_allowance ?? 0) + ($claimData->tc_deduction_amount ?? 0))) . '</td>
                <td></td>
            </tr>';

            $response .= '
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';

            // Approval Section
            if ($actionRoute !== 'claim-request.request.show' && $actionRoute !== 'claim-request-is-paid.request.show') {
                if ($approvalData && !$displayDeductionHandler) {
                    $response .= '
                    <div class="card-header">
                        <h3 class="card-title">Approval Or Reject</h3>
                    </div>
                    <div class="card-body">
                        <form id="approvalForm">
                            <div class="form-group">
                                <div id="deductionAmountDiv"></div>
                                <div class="row">
                                    <div class="col-md-12 col-lg-2">
                                        <label class="form-label mb-0 mt-2">Message</label>
                                    </div>
                                    <div class="col-md-12 col-lg-12">
                                        <textarea rows="2" name="message" class="form-control" id="actionMessage"></textarea>
                                    </div>
                                </div>
                                <div class="card-footer mt-3">
                                    <div class="row">
                                        <div class="col-md-12 col-lg-12 d-flex justify-content-end">
                                            <a href="javascript:void(0);"
                                               data-approval_status="' . $approvalData->fh_approver_status->m_id . '"
                                               data-approval_type="0"
                                               data-approval_action_type="' . $approvalData->pa_type . '"
                                               data-approval_sequence="' . $approvalData->pa_sequence . '"
                                               data-tc_id="' . md5($claimData->tc_id) . '"
                                               data-module_id="' . md5($approvalData->pa_am_id) . '"
                                               data-is_last_approval="' . $approvalData->pa_last . '"
                                               class="btn btn-outline-danger  actionBtn mx-3">Reject</a>
                                            <a href="javascript:void(0);"
                                               data-approval_status="' . $approvalData->fh_approver_status->m_id . '"
                                               data-approval_type="1"
                                               data-approval_action_type="' . $approvalData->pa_type . '"
                                               data-approval_sequence="' . $approvalData->pa_sequence . '"
                                               data-tc_id="' . md5($claimData->tc_id) . '"
                                               data-module_id="' . md5($approvalData->pa_am_id) . '"
                                               data-is_last_approval="' . $approvalData->pa_last . '"
                                               class="btn btn-success actionBtn">' . $approvalData->fh_approver_status->m_name . '</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>';
                }
            }

            // Claim Log Section
            $response .= '
            <div class="col-xl-12 col-md-12 col-lg-6">
                <ul id="claimLogList" class="timeline">';

            foreach ($combinedLog->sortBy('updated_at') as $index => $item2) {
                $className = get_class($item2);

                if ($className == 'App\Models\ApprovalLog') {
                    $jsonData2 = $item2->fh_status->m_other;
                    $item2DecodedData = json_decode($jsonData2, true);
                    $item2Color = $item2DecodedData['color'];
                    $item2Icon = $item2DecodedData['web_icon'];
                    $item2StatusName = $item2->fh_status->m_name;
                    $remark = $item2->log_description;
                    $emp_name = $item2->fh_employee->emp_full_name;
                    $emp_name = isset($item2->fh_employee) ? $item2->fh_employee->emp_full_name : '';
                    $emp_code = $item2->fh_employee->emp_code;
                    $emp_designation = $item2->fh_employee->fh_designation->dg_name;
                } else {
                    $item2StatusName = $item2->dlog_requester_action == 1 ? 'Accepted' : 'Declined';
                    $item2Color = $item2->dlog_requester_action == 1 ? '#4CAF50' : '#F44336';
                    $item2Icon = $item2->dlog_requester_action == 1 ? 'check_circle' : 'cancel';
                    $remark = $item2->dlog_description;
                    $emp_name = $item2->fh_employee->emp_full_name;
                    $emp_code = $item2->fh_employee->emp_code;
                    $emp_designation = $item2->fh_employee->fh_designation->dg_name;
                }

                $response .= '
                    <li class="' . e(($index + 1) % 2 ? 'primary' : 'success') . '">
                        <a href="javascript:void(0);" class="font-weight-semibold fs-15 mb-2 ms-3">
                            <span class="badge" style="background-color:' . e($item2Color) . '">
                                <i class="' . e($item2Icon) . '">&nbsp;</i>' . e($item2StatusName) . '
                            </span>
                        </a>
                        <a href="javascript:void(0);" class="text-muted float-end fs-12">
                            On ' . e(\Carbon\Carbon::parse($item2->created_at)->format('l')) . '
                        </a><br>
                        <span class="text-muted float-end ms-3 fs-14">
                            <i class="fa fa-calendar"></i>
                            ' . e(\Carbon\Carbon::parse($item2->created_at)->format('d-M-Y')) . '
                            <i class="ms-3 fa fa-clock-o"></i>
                            ' . e(\Carbon\Carbon::parse($item2->created_at)->format('h:i A')) . '
                        </span>
                        <p class="mb-0 pb-0 text-muted fs-18 pt-1 ms-3">' . e($emp_name) . ' &nbsp; <span class="fs-14">(' . e($emp_code) . ')</span></p>
                        <span class="mb-0 pb-0 text-muted fs-14 ms-3">' . e($emp_designation) . '</span><br>
                        <span class="text-muted ms-3 fs-14">Remark: ' . e($remark) . '</span>
                        <div>';

                if (isset($item2->fh_deductionLog->dlog_additional_info)) {
                    $response .= '<span class="text-muted ms-3 fs-14">Deduction:</span>';

                    $deductionInfo = json_decode($item2->fh_deductionLog->dlog_additional_info);
                    foreach ($deductionInfo as $key => $keyItem) {
                        $deductionName = \App\Models\MasterTable::find($key)->m_name ?? 'Unknown';
                        $response .= '<span class="text-muted ms-3 fs-14">' . e($deductionName) . ': ' . e($keyItem) . '</span>';
                    }
                }

                $response .= '
                        </div>
                    </li>';
            }


            $response .= '
                </ul>
            </div>';

            if ($actionRoute == 'claim-request.request.show' && $displayDeductionHandler) {
                $response .= '
                <div class="card-header">
                    <h3 class="card-title">Accept Or Decline</h3>
                </div>
                <div class="card-body">
                    <form id="deductionForm">
                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-12 col-lg-2">
                                    <label class="form-label mb-0 mt-2">Message</label>
                                </div>
                                <div class="col-md-12 col-lg-12">
                                    <textarea rows="2" name="empAcceptanceMsg" class="form-control" id="empAcceptanceMsg"></textarea>
                                </div>
                            </div>

                            <div class="card-footer mt-3">
                                <div class="row">
                                    <div class="col-md-12 col-lg-12 d-flex justify-content-end">
                                        <a href="javascript:void(0);"
                                           class="btn btn-outline-danger  mx-3 handleDeductionBtn"
                                           data-tc_id="' . md5($claimData->tc_id) . '"
                                           data-dlog_id="' . md5($displayDeductionHandler->dlog_id) . '"
                                           data-action="0">Decline</a>

                                        <a href="javascript:void(0);"
                                           class="btn btn-success handleDeductionBtn"
                                           data-tc_id="' . md5($claimData->tc_id) . '"
                                           data-dlog_id="' . md5($displayDeductionHandler->dlog_id) . '"
                                           data-action="1">Accept</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                ';
            }

            $jsonData = $claimData->fh_claim_status->m_other;

            // Decode the JSON to an associative array
            $decodedData = json_decode($jsonData, true); // true for associative array

            // Now access the color value
            $color = $decodedData['color'];
            $icon = $decodedData['web_icon'];

            return response()->json(['html' => $response, 'tc_request_status_name' => $claimData->fh_claim_status->m_name ?? 'N/A', 'color' => $color, 'icon' => $icon]);
        }

        // Check if the user employee mapping can approve start
        $claimData->approvalData = $approvalData;
        $approval = ApprovalHelper::getApprovalData($claimData, $this->user, 'tc_');
        $masterApproveBtn = $approval['masterApproveBtn'];
        $canApprove = $approval['canApprove'];

        // Check if the user employee mapping can approve end
        return view('admin.ta-da-request.claimdetails', compact('claimData', 'combinedLog', 'approvalData', 'displayDeductionHandler',  'workingHour', 'nextApprovalData', 'actionRoute', 'masterApproveBtn', 'canApprove'));
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
    public function destroy(Request $request)
    {
        // Decrypt the ID
        try {
            $decryptedId = Crypt::decrypt($request->input('id'));
        } catch (DecryptException $e) {
            return response()->json(['result' => [], 'status' => false, 'message' => 'Invalid ID.']);
        }

        // Find the claim data and eager load the required relationships
        $claimData = TadaClaim::with('fh_tada_request_plan.fh_policy_tada_travel_type', 'fh_tada_request_plan.fh_process_approvers')->find($decryptedId);

        // Check if the claim data exists
        if (!$claimData) {
            return response()->json(['result' => [], 'status' => false, 'message' => 'Claim data not found.']);
        }

        // Get the travel type from the nested relationship
        $travel = $claimData->fh_tada_request_plan->fh_policy_tada_travel_type;  // travel type get -> approval type check

        // Logic for updating request status based on approval type
        if ($travel->pttt_approval_type_id == 197) {  // when approval type auto
            $claimData->fh_tada_request_plan->trp_request_status = 171; // Status for approval type 197 auto approved id
        } elseif ($travel->pttt_approval_type_id == 198) { // when approval type mannual
            // For approval type 198, get the latest approver status
            $latestApprover = $claimData->fh_tada_request_plan
                ->fh_process_approvers() // Make sure this is a relationship method returning a query builder
                ->orderBy('pa_id', 'desc')
                ->first(); // first approval heirarcy get
            if ($latestApprover) {
                $claimData->fh_tada_request_plan->trp_request_status = $latestApprover->pa_status_id; // last id store
            } else {
                $claimData->fh_tada_request_plan->trp_request_status = 157; // Status for approval type 157 without approver

            }
        }
        // Save the deleted_at_remark and make sure to persist changes
        $claimData->fh_tada_request_plan->trp_is_claimed = 0;
        $claimData->deleted_by = $this->user->emp_id;
        $claimData->deleted_at_remark = $request->input('reason');
        $claimData->fh_tada_request_plan->save(); // Don't forget to save the updated `request_status`
        $claimData->save(); // Save the main record with the remark

        // Perform the soft delete
        $claimData->delete();

        // Return a success response
        return response()->json(['result' => [], 'status' => true, 'message' => 'Data reverted successfully.']);
    }

    public function travelClaim($claimId)
    {
        $user = Auth::user();
        $claimData = TadaClaim::where('tc_b_id', $user->emp_b_id)->where((DB::raw('md5(tc_id)')), $claimId)->first();
        if (!$claimData) {
            return response()->json(['result' => [], 'status' => false, 'message' => 'Claim data not found.']);
        }

        // $policyCategory = PolicyTadaCategory::where([
        //     'ptc_b_id' => optional($claimData->fh_employee)->emp_b_id,
        //     'ptc_d_id' => optional($claimData->fh_employee)->emp_d_id,
        //     'ptc_grade_id' => optional($claimData->fh_employee)->emp_grade_id,
        // ])->whereJsonContains('ptc_dg_id', optional($claimData->fh_employee)->emp_dg_id)
        //     ->first();

        // if (!$policyCategory) {
        //     return response()->json(['result' => [], 'status' => false, 'message' => 'Sorry! policy not found, contact administration.']);
        // }

        // $cities = TadaMetroCity::where('ctm_b_id', optional($user)->emp_b_id)->pluck('ctm_ct_address');
        // $isMetro = $cities->filter(function ($city) use ($claimData) {
        //     return strpos($city, optional($claimData->fh_tada_request_plan)->trp_destination) === 0;
        // });
        // if (!empty($isMetro)) {
        //     $daEligibility = optional($policyCategory->fh_policy_tada_daily_allowance->where('ptda_ptc_id', 23))->pluck('ptda_da_amount')->first() ?? 0; // 23 == 'Metro'
        // } else {
        //     $daEligibility = optional($policyCategory->fh_policy_tada_daily_allowance->where('ptda_ptc_id', 24))->pluck('ptda_da_amount')->first() ?? 0; // 24 == 'Non Metro'
        // }

        $expenseData = optional($claimData->fh_tada_request_plan->fh_tada_expenses)->groupBy('fh_expense_type.m_name') ?? [];
        $numberToWords = new NumberToWords();

        // Get the converter for English
        $numberTransformer = $numberToWords->getNumberTransformer('en');

        // Convert the number to words
        $payableAmount = ($claimData->tc_amount ?? 0) - (($claimData->fh_tada_request_plan->trp_advance_allowance ??  0) + ($claimData->tc_deduction_amount ?? 0));
        $words = $numberTransformer->toWords($payableAmount);
        $capitalizedWords = ucfirst($words);

        $data = [
            'title' => 'Welcome to Laravel PDF Generation',
            'claimData' => $claimData,
            'capitalizedWords' => $capitalizedWords,
            // 'daEligibility' => $daEligibility,
            'expenseData' => $expenseData,
            'logoPath' => optional($user->fh_business)->b_logo,
        ];

        $pdf = Pdf::loadView('admin.tada-reports.document', $data);

        return $pdf->stream('document.pdf');
    }

    public function approve(Request $request)
    {
        $data = TadaClaim::findOrFail($request->log_request_id);

        // Process approval using the service
        $response = ApprovalHelper::processApproval($request, $data, $this->user, 'tc_');
        return response()->json($response, $response['status'] === 'success' ? 200 : 500);
    }
}
