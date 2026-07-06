<?php

namespace App\Http\Controllers\Web\Admin;

use App\Helpers\RolePermissionLogics;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use App\Helpers\ApprovalHelper;
use App\Models\ApprovalLog;
use App\Models\MasterTable;
use App\Models\DeductionLog;
use App\Models\ApprovalModule;
use App\Models\PolicyTadaCategory;
use Illuminate\Http\Request;
use App\Models\TadaClaim;
use App\Models\TadaMetroCity;
use App\Models\TadaReimburse;
use App\Models\ProcessApprover;
use App\Models\TadaRequestDetail;
use App\Models\Employee;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use NumberToWords\NumberToWords;
use Illuminate\Support\Facades\Validator;

class ClaimGroupRequestController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function index(Request $request)
    {
        //Claim Group Approval View
        return $this->handleRequest($request, true, false, false);
    }
    public function index2(Request $request)
    {
        //Claim Requests View
        return $this->handleRequest($request, false, true, false);
    }

    public function index3(Request $request)
    {
        //Mark Claim as Paid View
        return $this->handleRequest($request, false, false, true);
    }

    /*private function handleRequest(Request $request, $isIndex1, $isIndex2, $isIndex3)
    {
        $user = Auth::user();
        $employeeFilter = request()->input('claim_employeeFilter');
        $branchFilter = request()->input('claim_branchFilter');
        $gradeFilter = request()->input('claim_gradeFilter');
        $statusFilter = request()->input('claim_activeFilter');
        $fromToDateFilter = request()->input('fromDate');

        if ($request->ajax()) {
            if ($isIndex2) {
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
                        'method' => 'groupBy',
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
                            'created_at',
                            'updated_at'
                        ]
                    ],
                    [
                        'method' => 'sortBy',
                        'args' => ['tc_id', 'tc_unique_id', 'tc_b_id', 'tc_trp_id', 'tc_emp_id', 'tc_amount', 'tc_claimed_amount', 'tc_module_id', 'tc_status', 'tc_id']
                    ],
                ];
            } else {
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
                        'method' => 'groupBy',
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
                            'created_at',
                            'updated_at'
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

            $searchColumns = ['tc_unique_id','tc_emp_id', 'tc_id', 'created_at', 'tc_status'];
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

            // Group claims by tc_unique_id
            $groupedClaims = [];
            foreach ($list as $item) {
                $uniqueId = $item->tc_unique_id;
                if (!isset($groupedClaims[$uniqueId])) {
                    $groupedClaims[$uniqueId] = [
                        'items' => [],
                        'total_amount' => 0,
                        'count' => 0,
                        'earliest_date' => $item->created_at,
                        'latest_status' => $item->tc_status,
                        'representative_item' => $item
                    ];
                }
                $groupedClaims[$uniqueId]['items'][] = $item;
                $groupedClaims[$uniqueId]['total_amount'] += $item->tc_amount;
                $groupedClaims[$uniqueId]['count']++;

                // Track earliest date
                if ($item->created_at < $groupedClaims[$uniqueId]['earliest_date']) {
                    $groupedClaims[$uniqueId]['earliest_date'] = $item->created_at;
                }

                // Track latest status (assuming higher number means newer status)
                if ($item->tc_status > $groupedClaims[$uniqueId]['latest_status']) {
                    $groupedClaims[$uniqueId]['latest_status'] = $item->tc_status;
                    $groupedClaims[$uniqueId]['representative_item'] = $item;
                }
            }


            $rowData = array();
            $i = 0;
            foreach ($groupedClaims as $uniqueId => $group) {

                // Get m_id value safely
                $travelTypeId = optional($item->fh_tada_request_plan->fh_policy_tada_travel_type->fh_travel_type)->m_id;

                // Skip single-entry groups unless m_id == 124
                if ($group['count'] === 1) {
                    continue;
                }

                $item = $group['representative_item'];
                $nextApproval = ApprovalHelper::getNextApprovalDetails(146, $item->tc_id, $item->tc_b_id);
                $approval = ApprovalHelper::checkApproval($user->emp_b_id, $item->tc_module_id, $item->tc_trp_id, optional($item->fh_employee)->emp_d_id, $item->tc_emp_id);

                $row = [];
                $employeeName = isset($item->fh_employee) ? ($item->fh_employee->emp_code ? $item->fh_employee->emp_code . ' - ' : '') . $item->fh_employee->emp_full_name : '';
                $designation = isset($item->fh_employee->fh_designation) ? $item->fh_employee->fh_designation->dg_name : '';
                $row[] = '';

                // Employee info with group count badge if multiple claims
                $employeeInfo = '<div class="d-flex dynamic-width">
                    <div class="me-3 mt-0 mt-sm-1 d-block">
                        <h6 class="mb-1 fs-14">' . $employeeName;

                if ($group['count'] > 1) {
                    $employeeInfo .= ' <span class="badge bg-primary">' . $group['count'] . ' claims</span>';
                }

                $employeeInfo .= '</h6><p class="text-muted mb-0 fs-12">' . $designation . '</p>
                    </div>
                </div>';

                $row[] = $employeeInfo;

                $row[] = isset($item->fh_tada_request_plan->fh_policy_tada_travel_type->fh_travel_type) ? $item->fh_tada_request_plan->fh_policy_tada_travel_type->fh_travel_type->m_name : '';
                $row[] = '#' . $item->tc_unique_id;
                $row[] = $group['total_amount'];
                $row[] = $group['earliest_date']->format('d-M-Y H:i');

                // Status and approval log
                $logData = $item->fh_approval_log_employee_wise;
                $formattedLog = '';
                $comma = false;

                if (count($logData)) {
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

                $popoverContent = $formattedLog === '' ? 'Awaiting' : $formattedLog;
                $safePopoverContent = htmlspecialchars($popoverContent, ENT_QUOTES, 'UTF-8');

                $jsonData = $item->fh_claim_status->m_other;
                $decodedData = json_decode($jsonData, true);
                $color = $decodedData['color'] ?? '#999';
                $icon = $decodedData['web_icon'] ?? 'fa fa-question';


                $statusBadge = '<span class="badge" style="background-color:' . $color . '"><i class="' . $icon . '">&nbsp;</i>'
                    . (isset($item->fh_claim_status->m_name) ? $item->fh_claim_status->m_name : '')
                    . '</span> &nbsp;'
                    . '<i class="feather feather-info text-primary fs-14" data-bs-container="body" data-bs-content="' . $safePopoverContent
                    . '" data-bs-placement="right" data-bs-popover-color="default" data-bs-toggle="popover" title="Approval Log"> </i>'
                    . '<p class="text-muted mb-0 fs-12">Approval ' . ($approval['approvallog_count'] ?? ' ') . ' (' . ($approval['approvalcount'] ?? ' ') . ')</p>';

                $row[] = $statusBadge;

                // Next approver
                if (!(isset($nextApproval['approver_name']) && $nextApproval['approver_name'])) {
                    $approvalMapping = ApprovalHelper::getApprovalMapping($user->emp_b_id, $item->tc_emp_id, $item->tc_module_id);
                    if ($approvalMapping) {
                        $approvalArray = ApprovalHelper::getApprovalArray($approvalMapping);
                        $approvalLog = $item?->fh_approval_log_employee_wise?->pluck('log_user_id')?->toArray() ?? [];
                        $diff = array_values(array_diff($approvalArray, $approvalLog));
                        $approverName = $diff ? (Employee::where('emp_id', $diff[0])->pluck('emp_full_name')->first() ?? '---') : '---';
                    } else {
                        $approverName = '---';
                    }
                }

                $row[] = '<span class="text-center">' .
                    (isset($nextApproval['approver_name']) && $nextApproval['approver_name']
                        ? $nextApproval['approver_name']
                        : ($approverName ?? '---')) .
                    '</span>';

                // Action column
                $dropdownStart = '<div class="dropdown">
                    <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu p-2" style="min-width: 180px;">';

                $dropdownEnd = '</ul></div>';

                $revertButton = '';
                if ($user->emp_role_id == 1) {
                    $revertButton = '<li>
                        <button class="dropdown-item revert-button" data-id="' . Crypt::encrypt($item->tc_id) . '" data-url="' . route('claim-request.destroy', Crypt::encrypt($item->tc_id)) . '">
                            <i class="fa fa-undo"></i> Revert
                        </button>
                    </li>';
                }

                $actionLinks = '';
                $uniqueTcId = ($group['count'] > 1) ? $group['items'][0]->tc_unique_id : $item->tc_unique_id;

                if ($isIndex1 || $isIndex2 || $isIndex3) {
                    if ($group['count'] > 1) {
                        $allClaims = [];
                        foreach ($group['items'] as $groupItem) {
                            $allClaims[] = [
                                'tc_id' => $groupItem->tc_id,
                                'amount' => $groupItem->tc_amount,
                                'travel_id' => $groupItem->tc_trp_id,
                                'expenses' => $groupItem->expenses ?? 'N/A', // Ensure expenses is included
                            ];
                        }



                        $actionLinks .= '
                            <li>
                                <a class="dropdown-item text-primary" href="' . route('claim-group.request.show', md5($uniqueTcId)) . '">
                                    <i class="feather feather-eye"></i> View
                                </a>
                            </li>';

                    } else {
                        $actionLinks .= '
                        <li>
                            <a class="dropdown-item text-primary open-modal"
                               data-tc-id="' . md5($item->tc_id) . '"
                               data-claims="' . htmlspecialchars(json_encode([
                                   'tc_id' => $item->tc_id,
                                   'amount' => $item->tc_amount,
                                   'travel_id' => $item->tc_trp_id,
                                   'expenses' => $item->expenses ?? 'N/A'
                               ]), ENT_QUOTES, 'UTF-8') . '">
                                <i class="feather feather-eye"></i> View
                            </a>
                        </li>';
                    }

                    $actionLinks .= RolePermissionLogics::check_route_permission('admin/ta-da-request/travel-claim/report/{id}', 116) ? '
                    <li>
                        <a class="dropdown-item text-info" href="' . route('travel.claim.report', md5($uniqueTcId)) . '">
                            <i class="feather feather-file"></i> Report
                        </a>
                    </li>' : '';

                    if ($isIndex1 && ApprovalHelper::isApprovalCompleted($item->tc_b_id, $item->tc_module_id, $item->tc_trp_id, $item->fh_employee->emp_d_id)
                        && $item->tc_stage_completed == 1
                        && $item->tc_is_payed != 1) {
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
                        $row[] = $dropdownStart . $actionLinks . $revertButton . $dropdownEnd;
                    }
                } else {
                    $row[] = $dropdownStart . $actionLinks . $revertButton . $dropdownEnd;
                }

                $rowData[] = $row;
            }

            $output = array(
                'draw' => $request->input('draw'),
                'recordsTotal' => sizeof($list),
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
            'Claim ID',
            'Claim Amount',
            'Claim Date',
            'Status',
            'Submitted To',
            'Action',
        ];
        $actionRoute = request()->route()->getName();
        $titleRoute = $isIndex2 ? 'claim-request' : ($isIndex3 == true ? 'claim-request-is-paid' : 'claim');
        $title = $isIndex2 ? 'Claim Requests' : ($isIndex3 == true ? 'Mark Claim Is Reimbursed' : 'Claim Group Approval');
        return view('admin.ta-da-request.claim-group', compact('columns', 'title', 'titleRoute', 'actionRoute'));
    }*/

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
                        'args' => ['tc_emp_id', $user->emp_id]
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
                            'tc_next_approver',
                            'tc_remarks',
                            'tc_is_payed',
                            'tc_stage_completed',
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
                        'method' => 'orderBy',
                        'args' => ['created_at', 'desc']
                    ],
                    [
                        'method' => 'orderBy',
                        'args' => ['tc_id', 'desc']
                    ],
                ];
            } else if ($isIndex1) {
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
                        'method' => 'groupBy',
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
                            'created_at',
                            'updated_at'
                        ]
                    ],
                    [
                        'method' => 'orderBy',
                        'args' => ['created_at', 'desc']
                    ],
                    [
                        'method' => 'orderBy',
                        'args' => ['tc_id', 'desc']
                    ],
                ];
            } else {
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
                        'method' => 'groupBy',
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
                            'created_at',
                            'updated_at'
                        ]
                    ],
                    [
                        'method' => 'orderBy',
                        'args' => ['created_at', 'desc']
                    ],
                    [
                        'method' => 'orderBy',
                        'args' => ['tc_id', 'desc']
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

            $searchColumns = ['tc_unique_id', 'tc_emp_id', 'tc_id', 'created_at', 'tc_status'];
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

            // Group claims by tc_unique_id
            $groupedClaims = [];
            foreach ($list as $item) {
                $uniqueId = $item->tc_unique_id;
                if (!isset($groupedClaims[$uniqueId])) {
                    $groupedClaims[$uniqueId] = [
                        'items' => [],
                        'total_amount' => 0,
                        'count' => 0,
                        'earliest_date' => $item->created_at,
                        'latest_status' => $item->tc_status,
                        'representative_item' => $item
                    ];
                }
                $groupedClaims[$uniqueId]['items'][] = $item;
                $groupedClaims[$uniqueId]['total_amount'] += $item->tc_amount;
                $groupedClaims[$uniqueId]['count']++;

                // Track earliest date
                if ($item->created_at < $groupedClaims[$uniqueId]['earliest_date']) {
                    $groupedClaims[$uniqueId]['earliest_date'] = $item->created_at;
                }

                // Track latest status (assuming higher number means newer status)
                if ($item->tc_status > $groupedClaims[$uniqueId]['latest_status']) {
                    $groupedClaims[$uniqueId]['latest_status'] = $item->tc_status;
                    $groupedClaims[$uniqueId]['representative_item'] = $item;
                }
            }

            $rowData = array();
            $i = 0;
            foreach ($groupedClaims as $uniqueId => $group) {
                // Use the group's representative item first
                $item = $group['representative_item'];

                if ($group['count'] <= 1) {
                    continue; // Skip groups with only one claim
                }

                // Show only Local travel (m_id == 124)
                $travelTypeId = optional($item->fh_tada_request_plan->fh_policy_tada_travel_type->fh_travel_type)->m_id;
                if ((int)$travelTypeId !== 124) {
                    continue;
                }
                $nextApproval = ApprovalHelper::getNextApprovalDetails(146, $item->tc_id, $item->tc_b_id);
                $approval = ApprovalHelper::checkApproval($user->emp_b_id, $item->tc_module_id, $item->tc_trp_id, optional($item->fh_employee)->emp_d_id, $item->tc_emp_id);

                $row = [];
                $employeeName = isset($item->fh_employee) ? ($item->fh_employee->emp_code ? $item->fh_employee->emp_code . ' - ' : '') . $item->fh_employee->emp_full_name : '';
                $designation = isset($item->fh_employee->fh_designation) ? $item->fh_employee->fh_designation->dg_name : '';
                $row[] = '';

                // Employee info with group count badge if multiple claims
                $employeeInfo = '<div class="d-flex dynamic-width">
                    <div class="me-3 mt-0 mt-sm-1 d-block">
                        <h6 class="mb-1 fs-14">' . $employeeName;

                if ($group['count'] > 1) {
                    $employeeInfo .= ' <span class="badge bg-primary">' . $group['count'] . ' claims</span>';
                }

                $employeeInfo .= '</h6><p class="text-muted mb-0 fs-12">' . $designation . '</p>
                    </div>
                </div>';

                $row[] = $employeeInfo;

                $row[] = isset($item->fh_tada_request_plan->fh_policy_tada_travel_type->fh_travel_type) ? $item->fh_tada_request_plan->fh_policy_tada_travel_type->fh_travel_type->m_name : '';
                $row[] = '#' . $item->tc_unique_id;
                $row[] = $group['total_amount'];
                $row[] = $group['earliest_date']->format('d-M-Y H:i');

                // Status and approval log
                $logData = $item->fh_approval_log_employee_wise;
                $formattedLog = '';
                $comma = false;

                if (count($logData)) {
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

                $popoverContent = $formattedLog === '' ? 'Awaiting' : $formattedLog;
                $safePopoverContent = htmlspecialchars($popoverContent, ENT_QUOTES, 'UTF-8');

                $jsonData = $item->fh_claim_status->m_other;
                $decodedData = json_decode($jsonData, true);
                $color = $decodedData['color'] ?? '#999';
                $icon = $decodedData['web_icon'] ?? 'fa fa-question';
                // $color = $decodedData['color'];
                // $icon = $decodedData['web_icon'];

                $statusBadge = '<span class="badge" style="background-color:' . $color . '"><i class="' . $icon . '">&nbsp;</i>'
                    . (isset($item->fh_claim_status->m_name) ? $item->fh_claim_status->m_name : '')
                    . '</span> &nbsp;'
                    . '<i class="feather feather-info text-primary fs-14" data-bs-container="body" data-bs-content="' . $safePopoverContent
                    . '" data-bs-placement="right" data-bs-popover-color="default" data-bs-toggle="popover" title="Approval Log"> </i>'
                    . '<p class="text-muted mb-0 fs-12">Approval ' . ($approval['approvallog_count'] ?? ' ') . ' (' . ($approval['approvalcount'] ?? ' ') . ')</p>';

                $row[] = $statusBadge;

                // Next approver
                if (!(isset($nextApproval['approver_name']) && $nextApproval['approver_name'])) {
                    $approvalMapping = ApprovalHelper::getApprovalMapping($user->emp_b_id, $item->tc_emp_id, $item->tc_module_id);
                    if ($approvalMapping) {
                        $approvalArray = ApprovalHelper::getApprovalArray($approvalMapping);
                        $approvalLog = $item?->fh_approval_log_employee_wise?->pluck('log_user_id')?->toArray() ?? [];
                        $diff = array_values(array_diff($approvalArray, $approvalLog));
                        $approverName = $diff ? (Employee::where('emp_id', $diff[0])->pluck('emp_full_name')->first() ?? '---') : '---';
                    } else {
                        $approverName = '---';
                    }
                }

                $row[] = '<span class="text-center">' .
                    (isset($nextApproval['approver_name']) && $nextApproval['approver_name']
                        ? $nextApproval['approver_name']
                        : ($approverName ?? '---')) .
                    '</span>';

                // Action column
                $dropdownStart = '<div class="dropdown">
                    <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu p-2" style="min-width: 180px;">';

                $dropdownEnd = '</ul></div>';

                $revertButton = '';
                if ($user->emp_role_id == 1) {
                    $revertButton = '<li>
                        <button class="dropdown-item revert-button" data-id="' . Crypt::encrypt($item->tc_id) . '" data-url="' . route('claim-request.destroy', Crypt::encrypt($item->tc_id)) . '">
                            <i class="fa fa-undo"></i> Revert
                        </button>
                    </li>';
                }

                $actionLinks = '';
                $uniqueTcId = ($group['count'] > 1) ? $group['items'][0]->tc_unique_id : $item->tc_unique_id;

                if ($isIndex1 || $isIndex2 || $isIndex3) {
                    if ($group['count'] > 1) {
                        $allClaims = [];
                        foreach ($group['items'] as $groupItem) {
                            $allClaims[] = [
                                'tc_id' => $groupItem->tc_id,
                                'amount' => $groupItem->tc_amount,
                                'travel_id' => $groupItem->tc_trp_id,
                                'expenses' => $groupItem->expenses ?? 'N/A', // Ensure expenses is included
                            ];
                        }

                        // $actionLinks .= '
                        //     <li>
                        //         <a class="dropdown-item text-primary open-claim-modal"
                        //           href="javascript:void(0);"
                        //           data-tc-id="' . md5($uniqueTcId) . '">
                        //             <i class="feather feather-eye"></i> View
                        //         </a>
                        //     </li>';

                        $actionLinks .= '
                            <li>
                                <a class="dropdown-item text-primary" href="' . route('claim-group.request.show', md5($uniqueTcId)) . '">
                                    <i class="feather feather-eye"></i> View
                                </a>
                            </li>';

                        // $actionLinks .= '
                        //     <li>
                        //         <a class="dropdown-item text-primary open-claim-modal"
                        //           href="javascript:void(0);"
                        //           data-tc-id="' . md5($uniqueTcId) . '"
                        //           data-claims=\'' . htmlspecialchars(json_encode([
                        //                 'tc_id' => $item->tc_id,
                        //                 'amount' => $item->tc_amount,
                        //                 'travel_id' => $item->tc_trp_id,
                        //                 'expenses' => $item->expenses ?? 'N/A'
                        //             ]), ENT_QUOTES, 'UTF-8') . '\'>
                        //             <i class="feather feather-eye"></i> View
                        //         </a>
                        //     </li>';
                    } else {
                        $actionLinks .= '
                        <li>
                            <a class="dropdown-item text-primary open-modal"
                               data-tc-id="' . md5($item->tc_id) . '"
                               data-claims="' . htmlspecialchars(json_encode([
                            'tc_id' => $item->tc_id,
                            'amount' => $item->tc_amount,
                            'travel_id' => $item->tc_trp_id,
                            'expenses' => $item->expenses ?? 'N/A'
                        ]), ENT_QUOTES, 'UTF-8') . '">
                                <i class="feather feather-eye"></i> View
                            </a>
                        </li>';
                    }

                    /*        $actionLinks .= RolePermissionLogics::check_route_permission('admin/ta-da-request/travel-claim/report/{id}', 116) ? '
                    <li>
                        <a class="dropdown-item text-info" href="' . route('travel.claim.report', md5($uniqueTcId)) . '">
                            <i class="feather feather-file"></i> Report
                        </a>
                    </li>' : '';
*/

                    if ($group['count'] > 1) {
                        $allClaimIds = array_column($group['items'], 'tc_id');
                        $encodedIds  = base64_encode(json_encode($allClaimIds));

                        $actionLinks .= RolePermissionLogics::check_route_permission('admin/ta-da-request/travel-claim/report/{id}', 116) ? '
   <li>
    <a class="dropdown-item text-info" 
       href="' . route('travel.claim.group.report', $encodedIds) . '" 
       target="_blank">
        <i class="feather feather-file"></i> Report (All)
    </a>
</li>' : '';
                    } else {
                        $actionLinks .= RolePermissionLogics::check_route_permission('admin/ta-da-request/travel-claim/report/{id}', 116) ? '
    <li>
        <a class="dropdown-item text-info" href="' . route('travel.claim.report', md5($item->tc_id)) . '">
            <i class="feather feather-file"></i> Report
        </a>
    </li>' : '';
                    }
                    if (
                        $isIndex1 && ApprovalHelper::isApprovalCompleted($item->tc_b_id, $item->tc_module_id, $item->tc_trp_id, $item->fh_employee->emp_d_id)
                        && $item->tc_stage_completed == 1
                        && $item->tc_is_payed != 1
                    ) {
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
                        $row[] = $dropdownStart . $actionLinks . $revertButton . $dropdownEnd;
                    }
                } else {
                    $row[] = $dropdownStart . $actionLinks . $revertButton . $dropdownEnd;
                }

                $rowData[] = $row;
            }

            // Apply pagination AFTER grouping (DataTables server-side expectations)
            $totalGrouped = count($rowData);
            $start = (int)$request->input('start', 0);
            $length = (int)$request->input('length', 10);
            $pagedData = array_slice($rowData, $start, $length);

            $output = array(
                'draw' => $request->input('draw'),
                'recordsTotal' => $totalGrouped,
                'recordsFiltered' => $totalGrouped,
                'data' => $pagedData,
            );

            return json_encode($output);
        }

        $columns = [
            'S.No.',
            'Emp. Name',
            'Travel Type',
            'Claim ID',
            'Claim Amount',
            'Claim Date',
            'Status',
            'Submitted To',
            'Action',
        ];
        $actionRoute = request()->route()->getName();
        $titleRoute = $isIndex2 ? 'claim-request' : ($isIndex3 == true ? 'claim-request-is-paid' : 'claim');
        $title = $isIndex2 ? 'Claim Requests' : ($isIndex3 == true ? 'Mark Claim Is Reimbursed' : 'Claim Group Approval');
        return view('admin.ta-da-request.claim-group', compact('columns', 'title', 'titleRoute', 'actionRoute'));
    }

    public function groupReport($encodedId)
    {
        try {
            \Log::info('[groupReport] hit', ['encodedId' => $encodedId, 'url' => request()->fullUrl()]);

            $user = Auth::user();
            \Log::info('[groupReport] auth', ['user_id' => optional($user)->emp_id, 'b_id' => optional($user)->emp_b_id]);

            $ids = null;
            $decoded = base64_decode($encodedId, true);
            if ($decoded) {
                $maybeIds = json_decode($decoded, true);
                if (is_array($maybeIds) && count($maybeIds)) {
                    $ids = $maybeIds;
                }
            }
            if (!$ids) {
                $ids = [decrypt_or_lookup($encodedId)];
            }
            \Log::info('[groupReport] resolved ids', ['ids' => $ids]);

            $claims = \App\Models\TadaClaim::query()
                ->where('tc_b_id', optional($user)->emp_b_id)
                ->where(function ($q) use ($ids) {
                    $plainIds = array_values(array_filter($ids, function ($v) {
                        return is_numeric($v);
                    }));
                    if (!empty($plainIds)) {
                        $q->orWhereIn('tc_id', $plainIds);
                    }
                    $md5Ids = array_values(array_filter($ids, function ($v) {
                        return is_string($v) && strlen($v) === 32 && ctype_xdigit($v);
                    }));
                    if (!empty($md5Ids)) {
                        $q->orWhereIn(DB::raw('md5(tc_id)'), $md5Ids);
                    }
                })
                ->get();
            \Log::info('[groupReport] claims fetched', ['count' => $claims->count()]);

            if ($claims->isEmpty()) {
                \Log::warning('[groupReport] no claims found');
                return response()->json(['result' => [], 'status' => false, 'message' => 'No claims found.']);
            }

            $html = '';
            foreach ($claims as $index => $claimData) {
                $expenseData = optional($claimData->fh_tada_request_plan->fh_tada_expenses)->groupBy('fh_expense_type.m_name') ?? [];

                $numberToWords = new \NumberToWords\NumberToWords();
                $numberTransformer = $numberToWords->getNumberTransformer('en');
                $payableAmount = ($claimData->tc_amount ?? 0) - ((optional($claimData->fh_tada_request_plan)->trp_advance_allowance ?? 0) + ($claimData->tc_deduction_amount ?? 0));
                $capitalizedWords = ucfirst($numberTransformer->toWords($payableAmount));

                $data = [
                    'title' => 'Welcome to Laravel PDF Generation',
                    'claimData' => $claimData,
                    'capitalizedWords' => $capitalizedWords,
                    'expenseData' => $expenseData,
                    'logoPath' => optional($user->fh_business)->b_logo,
                ];

                $html .= view('admin.tada-reports.document', $data)->render();
                if ($index < ($claims->count() - 1)) {
                    $html .= '<div style="page-break-after: always;"></div>';
                }
            }

            $pdf = Pdf::loadHTML($html);
            \Log::info('[groupReport] streaming pdf');
            return $pdf->stream('document.pdf');
        } catch (\Throwable $e) {
            \Log::error('[groupReport] exception', ['message' => $e->getMessage()]);
            return response()->json(['result' => [], 'status' => false, 'message' => 'Error generating PDF', 'error' => $e->getMessage()], 500);
        }
    }

    public function getClaimDetails($tc_id)
    {
        try {
            // Fetch claims with relationships
            $claims = TadaClaim::with([
                'fh_employee:emp_id,emp_full_name,emp_fname,emp_mname,emp_lname,emp_b_id,emp_d_id',
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
            ])
                ->where(DB::raw('md5(tc_unique_id)'), $tc_id)
                ->where('tc_b_id', auth()->user()->emp_b_id)
                ->get();

            if ($claims->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No claims found'], 404);
            }

            $claimData = [];
            foreach ($claims as $claim) {
                $expenses = [];
                if ($claim->fh_tada_request_plan && $claim->fh_tada_request_plan->fh_tada_expenses) {
                    foreach ($claim->fh_tada_request_plan->fh_tada_expenses as $expense) {
                        $expenses[] = [
                            'name' => $expense->fh_expense_type->name ?? 'N/A',
                            'amount' => $expense->amount ?? 0,
                            'deviation' => $expense->deviation ?? 0,
                            'payable_amount' => $expense->payable_amount ?? 0,
                            'deduction' => $expense->deduction ?? 0,
                        ];
                    }
                }

                $claimData[] = [
                    'travel_id' => $claim->fh_tada_request_plan->trp_unique_id,
                    'tc_id' => $claim->tc_id,
                    'amount' => $claim->tc_amount,
                    'travel_id' => $claim->tc_trp_id,
                    'expenses' => $expenses,
                    'da' => $claim->da_amount ?? 0,
                    'advance' => $claim->advance_amount ?? 0,
                    'deduction' => $claim->total_deduction ?? 0,
                    'net_payable' => $claim->net_payable_amount ?? 0,
                    'claimed_date' => $claim->created_at->format('d-M-Y'),
                ];
            }

            // Additional details
            $additionalDetails = [
                'employee_name' => $claims->first()->fh_employee->emp_full_name ?? 'N/A',
                'status' => $claims->first()->fh_claim_status->m_name ?? 'N/A',
                'claim_id' => $claims->first()->tc_unique_id ?? 'N/A',
            ];

            return response()->json([
                'success' => true,
                'claims' => $claimData,
                'additional_details' => $additionalDetails
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching claim details: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error fetching claim details'], 500);
        }
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
    public function store(Request $request)
    {
        $user = Auth::user();
        $claimIds = $request->claim_ids;

        // Assuming $claimIds is an array of encrypted IDs
        $decryptedClaimIds = array_map(function ($claimId) {
            return Crypt::decrypt($claimId);
        }, $claimIds);
        $claimData = TadaClaim::whereIn('tc_id', $decryptedClaimIds)->with('fh_tada_request_plan.fh_tada_expenses')->get();

        $total_net_payed_amount = 0;
        foreach ($claimData as $claimRecord) {

            $net_payed_amount = $claimRecord->tc_amount - (($claimRecord->fh_tada_request_plan->trp_advance_allowance ?? 0) + ($claimRecord->tc_deduction_amount ?? 0));
            $total_net_payed_amount += $net_payed_amount;

            $claimRecord->update([
                'tc_payed_amount' => $net_payed_amount,
                'tc_is_payed' => 1,
                'tc_status' => 200,
            ]);

            ($net_payed_amount < 0 && $claimRecord->fh_employee) ?
                $claimRecord->fh_employee->update(['emp_tada_settlement_amt' => $claimRecord->fh_employee->emp_tada_settlement_amt + $net_payed_amount])
                : '';

            ApprovalLog::create([
                'log_am_id' => $claimRecord->tc_am_id,
                'log_request_id' => $claimRecord->tc_id,
                'log_user_id' => $user->emp_id,
                'log_user_role_id' => $user->emp_role_id,
                'log_status' => 200,
                'log_description' => 'Mark Claim as Reimbursed',
            ]);
        }

        TadaReimburse::create([
            'tr_b_id' => $user->emp_b_id,
            'tr_claims_id' => $decryptedClaimIds, // Store as JSON
            'tr_amount' => $total_net_payed_amount,
        ]);

        $responseData = [
            'message' => 'Claims marked as reimbursed.',
            'success' => true
        ];

        return response()->json($responseData);
    }



    /**
     * Display the specified resource.
     */

    /*public function show(string $id)
    {
        $user = Auth::user();
        $actionRoute = request()->route()->getName();

        // Fetch all claims for grouping
        $claimData = TadaClaim::with([
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
            ])
            ->where('tc_b_id', $user->emp_b_id)
            ->where(DB::raw('md5(tc_unique_id)'), $id)
            ->get();

        // Initialize variables for grouping
        $approvalData = null;
        $displayDeductionHandler = null;
        $workingHour = 8;
        $nextApprovalData = null;
        $combinedLog = collect();
        $canApprove = false;
        $masterApproveBtn = false;

        // Process each claim for grouping data
        foreach ($claimData as $singleClaim) {
            // Get approval data for each claim
            $singleApprovalData = ApprovalHelper::getApprovalOrRejectionData(
                $singleClaim->tc_trp_id,
                $singleClaim->tc_status,
                $singleClaim->tc_am_id,
                NULL,
                146
            );

            $singleDeductionHandler = $singleClaim->fh_deduction_log->whereNull('dlog_requester_action')->first();

            $singleNextApprovalData = DB::table('next_approval_details')
                ->where('nxt_tc_id', $singleClaim->tc_id)
                ->first();

            // Approval log logic for each claim
            if($singleClaim->tc_am_id) {
                $singleClaimApproval = ApprovalLog::where('log_request_id', $singleClaim->tc_trp_id)
                   ->where(function ($query) use ($singleClaim) {
                       $query->where('log_am_id', $singleClaim->tc_am_id)
                             ->orWhere('log_module_id', $singleClaim->tc_module_id);
                   })
                   ->get();
            } else {
                $singleClaimApproval = $singleClaim->fh_approval_log_employee_wise;
            }

            $singleClaimDeduction = DeductionLog::whereNotNull('dlog_requester_action')
                ->where('dlog_tc_id', $singleClaim->tc_id)
                ->get();

            $singleCombinedLog = $singleClaimApproval->merge($singleClaimDeduction);

            // Merge all logs for grouping
            $combinedLog = $combinedLog->merge($singleCombinedLog);

            // Set variables if this is the first claim or if we need to show approver interface
            if (!$approvalData && $singleApprovalData) {
                $approvalData = $singleApprovalData;
            }

            if (!$displayDeductionHandler && $singleDeductionHandler) {
                $displayDeductionHandler = $singleDeductionHandler;
            }

            if (!$nextApprovalData && $singleNextApprovalData) {
                $nextApprovalData = $singleNextApprovalData;
            }
        }

        // AJAX Response
        if (request()->ajax()) {
            $response = $this->generateGroupViewResponse(
                $claimData,
                $approvalData,
                $displayDeductionHandler,
                $combinedLog,
                $actionRoute,
                $id
            );

            // Get status info for the group
            $firstClaim = $claimData->first();
            if ($firstClaim) {
                $jsonData = $firstClaim->fh_claim_status->m_other;
                $decodedData = json_decode($jsonData, true);
                $color = $decodedData['color'];
                $icon = $decodedData['web_icon'];
                $statusName = $firstClaim->fh_claim_status->m_name ?? 'N/A';
            } else {
                $color = '#666';
                $icon = 'info';
                $statusName = 'N/A';
            }

            return response()->json([
                'html' => $response,
                'tc_request_status_name' => 'Group: ' . $statusName . ' (' . $claimData->count() . ' claims)',
                'color' => $color,
                'icon' => $icon,
                'total_claims' => $claimData->count(),
                'total_amount' => number_format($this->calculateTotalClaimAmount($claimData), 2)
            ]);
        }

        // Non-AJAX response
        if ($claimData->isNotEmpty()) {
            $firstClaim = $claimData->first();
            $firstClaim->approvalData = $approvalData;
            $approval = ApprovalHelper::getApprovalData($firstClaim, $user, 'tc_');
            $masterApproveBtn = $approval['masterApproveBtn'];
            $canApprove = $approval['canApprove'];
        }

        return view('admin.ta-da-request.claimgroupdetails', compact(
            'claimData',
            'combinedLog',
            'approvalData',
            'displayDeductionHandler',
            'workingHour',
            'nextApprovalData',
            'actionRoute',
            'masterApproveBtn',
            'canApprove'
        ));
    }*/

    /**
     * Handle bulk approval/rejection of multiple claims
     */
    /*public function approveMultipleClaims(Request $request)
    {
        $user = Auth::user();
        $response = ['status' => false, 'message' => '', 'result' => false];

        // Validate request with new structure
        $validator = Validator::make($request->all(), [
            'data.claim_ids' => 'required|array',
            'data.claim_ids.*' => 'required|string',
            'data.approval_type' => 'required|in:0,1',
            'data.approval_status' => 'required|numeric',
            'data.approval_action_type' => 'required|string',
            'data.approval_sequence' => 'required|numeric',
            'data.module_id' => 'required|string',
            'data.is_last_approval' => 'required|boolean',
            'data.emp_d_id' => 'required|numeric',
            'data.message' => 'required|string',
            'data.deduction_amount' => 'nullable|array',
            'data.deduction_info' => 'nullable|array',
            'data.payable_amount' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            $response['message'] = 'Validation failed';
            $response['errors'] = $validator->errors();
            return response()->json($response, 422);
        }

        $isApproval = $request->data['approval_type'] == 1;
        $nextApprover = 1;
        $approvalSequence = (int) $request->data['approval_sequence'];

        if ($request->data['approval_action_type'] != 'and') {
            $isLast = 1;
        } else {
            $isLast = (int) $request->data['is_last_approval'];
            if (!$isLast) {
                $nextApprover = $approvalSequence + 1;
            }
        }

        // Get all claims with necessary relationships
        $claims = TadaClaim::with([
                'fh_tada_request_plan.fh_tada_expenses',
                'fh_deduction_log' => function($q) {
                    $q->where('dlog_requester_action', 1);
                }
            ])
            ->whereIn('tc_id', $request->data['claim_ids'])
            ->where('tc_b_id', $user->emp_b_id)
            ->get();

        if ($claims->isEmpty()) {
            $response['message'] = 'No valid claims found to process';
            return response()->json($response, 404);
        }

        // For approval - validate deductions against payable amounts
        if ($isApproval && isset($request->data['deduction_info'])) {
            foreach ($request->data['deduction_info'] as $claimId => $deductions) {
                foreach ($deductions as $expTypeId => $deductionAmount) {
                    if (isset($request->data['payable_amount'][$claimId][$expTypeId]) &&
                        $request->data['payable_amount'][$claimId][$expTypeId] < $deductionAmount) {
                        $response['message'] = "Deduction amount (₹$deductionAmount) cannot be greater than payable amount (₹{$request->data['payable_amount'][$claimId][$expTypeId]}) for claim $claimId";
                        return response()->json($response, 422);
                    }
                }
            }
        }

        DB::beginTransaction();
        try {
            $successCount = 0;
            $failedClaims = [];

            foreach ($claims as $claim) {
                try {
                    // Get deduction data for this specific claim
                    $claimDeductionAmount = $request->data['deduction_amount'][$claim->tc_id] ?? 0;
                    $claimDeductionInfo = $request->data['deduction_info'][$claim->tc_id] ?? [];
                    $claimPayableAmounts = $request->data['payable_amount'][$claim->tc_id] ?? [];

                    // Prepare data object for common approval function
                    $data = (object)[
                        'approval_status' => $request->data['approval_status'],
                        'approval_type' => $request->data['approval_type'],
                        'approval_action_type' => $request->data['approval_action_type'],
                        'approval_sequence' => $request->data['approval_sequence'],
                        'module_id' => $request->data['module_id'],
                        'is_last_approval' => $request->data['is_last_approval'],
                        'emp_d_id' => $request->data['emp_d_id'],
                        'tc_id' => md5($claim->tc_id),
                        'message' => $request->data['message'],
                        'deduction_amount' => $claimDeductionAmount,
                        'deduction_info' => $claimDeductionInfo,
                        'payable_amount' => $claimPayableAmounts
                    ];

                    if ($isApproval) {
                        $statusData = MasterTable::where([
                                'm_group' => 'APPROVAL_STATUS',
                                'm_id' => $request->data['approval_status']
                            ])
                            ->select('m_id', 'm_name')
                            ->first();

                        app(\App\Http\Controllers\CommonApprovalController::class)
                            ->updateRequestStatusAndLog($data, $statusData, 0, 'CLAIM_REQUEST_APPROVAL', $isLast);
                    } else {
                        $statusData = MasterTable::where([
                                'm_group' => 'APPROVAL_STATUS',
                                'm_id' => 170 // Rejected
                            ])
                            ->select('m_id', 'm_name')
                            ->first();

                        app(\App\Http\Controllers\CommonApprovalController::class)
                            ->updateRequestStatusAndLog($data, $statusData, 0, 'CLAIM_REQUEST_APPROVAL', $isLast);
                    }

                    $successCount++;
                } catch (\Exception $e) {
                    $failedClaims[] = [
                        'id' => $claim->tc_id,
                        'error' => $e->getMessage()
                    ];
                    continue;
                }
            }

            DB::commit();

            $response['status'] = true;
            $response['result'] = true;
            $response['message'] = ($isApproval ? 'Approved' : 'Rejected') . ' ' . $successCount . ' claims successfully';
            $response['failed'] = $failedClaims;

            return response()->json($response);

        } catch (\Exception $e) {
            DB::rollBack();
            $response['message'] = 'Failed to process claims: ' . $e->getMessage();
            return response()->json($response, 500);
        }
    }*/

    /**
     * Helper method to calculate total claim amount
     */
    /*protected function calculateTotalClaimAmount($claims)
    {
        return $claims->sum('tc_amount');
    }*/

    /**
     * Generate the HTML response for group view
     */
    /*protected function generateGroupViewResponse($claimData, $approvalData, $displayDeductionHandler, $combinedLog, $actionRoute, $groupId)
    {
        $response = '
        <div class="row">
            <div class="col-md-12">
                <div class="card overflow-hidden">
                    <div class="card-header">
                        <h3 class="card-title">Grouped Claims Summary</h3>
                        <small class="text-muted">Total Claims: ' . $claimData->count() . '</small>
                    </div>
                    <div class="card-body">';

        // Group Summary Table
        $allExpenses = collect();
        $totalClaimAmount = 0;
        $totalDAAmount = 0;
        $totalTAAmount = 0;
        $totalAdvanceAmount = 0;
        $totalDeductionAmount = 0;

        // Collect all expenses from all claims
        foreach ($claimData as $singleClaim) {
            $rawPlanData = $singleClaim->fh_tada_request_plan;

            if ($rawPlanData && $rawPlanData->fh_tada_expenses) {
                $claimExpenses = $rawPlanData->fh_tada_expenses->where('te_paid_by', 'self');
                $allExpenses = $allExpenses->merge($claimExpenses);
            }

            // Calculate totals
            $totalClaimAmount += $singleClaim->tc_amount ?? 0;
            $totalDAAmount += $singleClaim->tc_da_amount ?? 0;
            $totalAdvanceAmount += $rawPlanData->trp_advance_allowance ?? 0;
            $totalDeductionAmount += $singleClaim->tc_deduction_amount ?? 0;

            // Calculate TA for each claim
            if ($rawPlanData && $rawPlanData->trp_is_details_added) {
                $rawTravelTypeLocal = $rawPlanData->fh_policy_tada_travel_type->fh_travel_type->m_id == 124;
                $rawTravelDetails = $rawPlanData->fh_tada_request_details;
                $rawTravelDetailSumAmt = $rawTravelTypeLocal
                    ? ($rawTravelDetails ? $rawTravelDetails->sum('trd_net_amount') : 0)
                    : TadaRequestDetail::whereHas('fh_policy_tada_travel_vehicle', function ($query) {
                        $query->where('pttv_claim_type_id', 155);
                    })
                    ->where('trd_trp_id', $rawPlanData->trp_id)
                    ->sum('trd_net_amount');

                $totalTAAmount += $rawTravelDetailSumAmt;
            }
        }

        $response .= '
                        <div class="table-responsive push">
                            <table class="table table-bordered table-hover text-nowrap">
                                <tr>
                                    <th class="text-center" style="width: 1%">S.No.</th>
                                    <th>Expense Type</th>
                                    <th class="text-end" style="width: 15%">Total Amount</th>
                                    <th class="text-end" style="width: 15%">Total Deviation</th>
                                    <th class="text-end" style="width: 15%">Net Payable</th>';

        // Add deduction column if approver can make deductions
        if ($approvalData && !$displayDeductionHandler) {
            $response .= '<th class="text-center" style="width: 15%">Deduction</th>';
        }

        $response .= '</tr>';

        // Group expenses by type and display
        $groupedExpenses = $allExpenses->groupBy('fh_expense_type.m_name');
        $iteration = 1;

        foreach ($groupedExpenses as $expenseType => $expenses) {
            $exp_type_id = $expenses->pluck('te_type_id')->first();
            $totalAmount = round($expenses->sum('te_amount') + $expenses->sum('te_taxes'));
            $totalDeviation = round($expenses->sum('te_deviation'));
            $netPayable = $totalAmount - $totalDeviation;

            $response .= '
            <tr>
                <td class="text-center">' . $iteration++ . '</td>
                <td><strong>' . e($expenseType) . '</strong></td>
                <td class="text-end">₹' . number_format($totalAmount, 2) . '</td>
                <td class="text-end">₹' . number_format($totalDeviation, 2) . '</td>
                <td class="text-end">₹' . number_format($netPayable, 2) . '</td>';

            if ($approvalData && !$displayDeductionHandler) {
                $response .= '
                <td>
                    <input type="number"
                           name="group_deduction[' . $exp_type_id . ']"
                           id="group_' . $exp_type_id . '_deduction"
                           data-payableAmount="' . $netPayable . '"
                           class="form-control deduction-input text-end"
                           step="0.01" min="0"
                           placeholder="₹ 0">
                </td>';
            }

            $response .= '</tr>';
        }

        // Summary rows
        if ($totalTAAmount > 0) {
            $response .= '
            <tr class="table-info">
                <td colspan="4" class="font-weight-semibold text-end">Total TA</td>
                <td class="text-end font-weight-semibold">₹' . number_format($totalTAAmount, 2) . '</td>
                <td></td>
            </tr>';
        }

        $response .= '
        <tr class="table-info">
            <td colspan="4" class="font-weight-semibold text-end">Total DA</td>
            <td class="text-end font-weight-semibold">₹' . number_format($totalDAAmount, 2) . '</td>
            <td></td>
        </tr>

        <tr class="table-warning">
            <td colspan="4" class="font-weight-semibold text-end">
                Subtotal <small>(Total Expenses + TA + DA - Deviation)</small>
            </td>
            <td class="text-end font-weight-semibold">₹' . number_format($totalClaimAmount, 2) . '</td>
            <td></td>
        </tr>

        <tr class="table-secondary">
            <td colspan="4" class="font-weight-semibold text-end">Total Advance</td>
            <td class="text-end font-weight-semibold">₹' . number_format($totalAdvanceAmount, 2) . '</td>
            <td></td>
        </tr>

        <tr class="table-secondary">
            <td colspan="4" class="font-weight-semibold text-end">Total Deduction</td>
            <td class="text-end font-weight-semibold">₹' . number_format($totalDeductionAmount, 2) . '</td>
            <td></td>
        </tr>

        <tr class="table-success">
            <td colspan="4" class="font-weight-bold text-uppercase text-end h4 mb-0">Net Payable Amount</td>
            <td class="font-weight-bold h4 mb-0 text-end">₹' . number_format(($totalClaimAmount - ($totalAdvanceAmount + $totalDeductionAmount)), 2) . '</td>
            <td></td>
        </tr>';

        $response .= '
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>';

        // Individual Claims Details Table
        $response .= '
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Individual Claims in this Group</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Claim ID</th>
                                        <th>Employee</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>';

        foreach ($claimData as $singleClaim) {
            $jsonData = $singleClaim->fh_claim_status->m_other;
            $decodedData = json_decode($jsonData, true);
            $statusColor = $decodedData['color'] ?? '#666';

            $response .= '
            <tr>
                <td><strong>' . e($singleClaim->tc_unique_id) . '</strong></td>
                <td>
                    ' . e($singleClaim->fh_employee->emp_full_name) . '<br>
                    <small class="text-muted">(' . e($singleClaim->fh_employee->emp_code) . ')</small>
                </td>
                <td>' . e(\Carbon\Carbon::parse($singleClaim->created_at)->format('d-M-Y')) . '</td>
                <td>₹' . number_format($singleClaim->tc_amount, 2) . '</td>
                <td>
                    <span class="badge" style="background-color: ' . $statusColor . '">
                        ' . e($singleClaim->fh_claim_status->m_name) . '
                    </span>
                </td>
                <td>
                    <a href="' . route('claim-request.request.show', md5($singleClaim->tc_id)) . '"
                       class="btn btn-sm btn-outline-primary" target="_blank">
                        <i class="fa fa-eye"></i> View Details
                    </a>
                </td>
            </tr>';
        }

        $response .= '
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>';

        // Group Approval Section
        if ($actionRoute !== 'claim-request.request.show' && $actionRoute !== 'claim-request-is-paid.request.show') {
            if ($approvalData && !$displayDeductionHandler) {
                $response .= '
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Group Approval Action</h3>
                            </div>
                            <div class="card-body">
                                <form id="groupApprovalForm">
                                    <div class="form-group">
                                        <div id="groupDeductionAmountDiv"></div>
                                        <div class="row">
                                            <div class="col-md-12 col-lg-2">
                                                <label class="form-label mb-0 mt-2">Message</label>
                                            </div>
                                            <div class="col-md-12 col-lg-10">
                                                <textarea rows="3" name="group_message" class="form-control"
                                                         id="groupActionMessage"
                                                         placeholder="Enter message for all claims in this group..."></textarea>
                                            </div>
                                        </div>
                                        <div class="card-footer mt-3">
                                            <div class="row">
                                                <div class="col-md-12 d-flex justify-content-end">
                                                    <a href="javascript:void(0);"
                                                       data-group_id="' . $groupId . '"
                                                       data-approval_type="0"
                                                       data-total_claims="' . $claimData->count() . '"
                                                       class="btn btn-outline-danger mx-3 groupActionBtn">
                                                       <i class="fa fa-times"></i> Reject All
                                                    </a>
                                                    <a href="javascript:void(0);"
                                                       data-group_id="' . $groupId . '"
                                                       data-approval_type="1"
                                                       data-total_claims="' . $claimData->count() . '"
                                                       class="btn btn-success groupActionBtn">
                                                       <i class="fa fa-check"></i> Approve All (' . $claimData->count() . ' Claims)
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>';
            }
        }

        // Combined Activity Log
        $response .= '
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Activity Timeline (All Claims)</h3>
                    </div>
                    <div class="card-body">
                        <div class="col-xl-12">
                            <ul id="groupClaimLogList" class="timeline">';

        foreach ($combinedLog->sortByDesc('created_at')->take(20) as $index => $item2) {
            $className = get_class($item2);

            if ($className == 'App\Models\ApprovalLog') {
                $jsonData2 = $item2->fh_status->m_other;
                $item2DecodedData = json_decode($jsonData2, true);
                $item2Color = $item2DecodedData['color'];
                $item2Icon = $item2DecodedData['web_icon'];
                $item2StatusName = $item2->fh_status->m_name;
                $remark = $item2->log_description;
                $emp_name = isset($item2->fh_employee) ? $item2->fh_employee->emp_full_name : '';
                $emp_code = isset($item2->fh_employee) ? $item2->fh_employee->emp_code : '';
                $emp_designation = isset($item2->fh_employee->fh_designation) ? $item2->fh_employee->fh_designation->dg_name : '';
            } else {
                $item2StatusName = $item2->dlog_requester_action == 1 ? 'Accepted' : 'Declined';
                $item2Color = $item2->dlog_requester_action == 1 ? '#4CAF50' : '#F44336';
                $item2Icon = $item2->dlog_requester_action == 1 ? 'check_circle' : 'cancel';
                $remark = $item2->dlog_description ?? '';
                $emp_name = isset($item2->fh_employee) ? $item2->fh_employee->emp_full_name : '';
                $emp_code = isset($item2->fh_employee) ? $item2->fh_employee->emp_code : '';
                $emp_designation = isset($item2->fh_employee->fh_designation) ? $item2->fh_employee->fh_designation->dg_name : '';
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
            </li>';
        }

        $response .= '
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>';

        // Employee deduction handling section
        if($actionRoute == 'claim-request.request.show' && $displayDeductionHandler) {
            $response .= '
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Group Deduction Response</h3>
                        </div>
                        <div class="card-body">
                            <form id="groupDeductionForm">
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-12 col-lg-2">
                                            <label class="form-label mb-0 mt-2">Response Message</label>
                                        </div>
                                        <div class="col-md-12 col-lg-10">
                                            <textarea rows="3" name="groupEmpAcceptanceMsg" class="form-control"
                                                     id="groupEmpAcceptanceMsg"
                                                     placeholder="Enter your response for the group deduction..."></textarea>
                                        </div>
                                    </div>

                                    <div class="card-footer mt-3">
                                        <div class="row">
                                            <div class="col-md-12 d-flex justify-content-end">
                                                <a href="javascript:void(0);"
                                                   class="btn btn-outline-danger mx-3 handleGroupDeductionBtn"
                                                   data-group_id="' . $groupId . '"
                                                   data-action="0">
                                                   <i class="fa fa-times"></i> Decline All
                                                </a>
                                                <a href="javascript:void(0);"
                                                   class="btn btn-success handleGroupDeductionBtn"
                                                   data-group_id="' . $groupId . '"
                                                   data-action="1">
                                                   <i class="fa fa-check"></i> Accept All
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>';
        }

        return $response;
    }*/

    public function show(string $id)
    {
        $user = Auth::user();
        $actionRoute = request()->route()->getName();

        // Fetch all claims for grouping
        $claimData = TadaClaim::with([
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
        ])
            ->where('tc_b_id', $user->emp_b_id)
            ->where(DB::raw('md5(tc_unique_id)'), $id)
            ->get();

        // Initialize variables for grouping
        $approvalData = null;
        $displayDeductionHandler = null;
        $workingHour = 8;
        $nextApprovalData = null;
        $combinedLog = collect();
        $canApprove = false;
        $masterApproveBtn = false;

        // Process each claim for grouping data
        foreach ($claimData as $singleClaim) {
            // Get approval data for each claim
            $singleApprovalData = ApprovalHelper::getApprovalOrRejectionData(
                $singleClaim->tc_trp_id,
                $singleClaim->tc_status,
                $singleClaim->tc_am_id,
                NULL,
                146
            );

            $singleDeductionHandler = $singleClaim->fh_deduction_log->whereNull('dlog_requester_action')->first();

            $singleNextApprovalData = DB::table('next_approval_details')
                ->where('nxt_tc_id', $singleClaim->tc_id)
                ->first();

            // Approval log logic for each claim
            if ($singleClaim->tc_am_id) {
                $singleClaimApproval = ApprovalLog::where('log_request_id', $singleClaim->tc_trp_id)
                    ->where(function ($query) use ($singleClaim) {
                        $query->where('log_am_id', $singleClaim->tc_am_id)
                            ->orWhere('log_module_id', $singleClaim->tc_module_id);
                    })
                    ->get();
            } else {
                $singleClaimApproval = $singleClaim->fh_approval_log_employee_wise;
            }

            $singleClaimDeduction = DeductionLog::whereNotNull('dlog_requester_action')
                ->where('dlog_tc_id', $singleClaim->tc_id)
                ->get();

            $singleCombinedLog = $singleClaimApproval->merge($singleClaimDeduction);

            // Attach per-claim combined log
            $singleClaim->combined_log = $singleCombinedLog;

            // Merge all logs for grouping
            $combinedLog = $combinedLog->merge($singleCombinedLog);

            // Set variables if this is the first claim or if we need to show approver interface
            if (!$approvalData && $singleApprovalData) {
                $approvalData = $singleApprovalData;
            }

            if (!$displayDeductionHandler && $singleDeductionHandler) {
                $displayDeductionHandler = $singleDeductionHandler;
            }

            if (!$nextApprovalData && $singleNextApprovalData) {
                $nextApprovalData = $singleNextApprovalData;
            }
        }

        // AJAX Response
        if (request()->ajax()) {
            $response = $this->generateGroupViewResponse(
                $claimData,
                $approvalData,
                $displayDeductionHandler,
                $combinedLog,
                $actionRoute,
                $id
            );

            // Get status info for the group
            $firstClaim = $claimData->first();
            if ($firstClaim) {
                $jsonData = $firstClaim->fh_claim_status->m_other;
                $decodedData = json_decode($jsonData, true);
                $color = $decodedData['color'];
                $icon = $decodedData['web_icon'];
                $statusName = $firstClaim->fh_claim_status->m_name ?? 'N/A';
            } else {
                $color = '#666';
                $icon = 'info';
                $statusName = 'N/A';
            }

            return response()->json([
                'html' => $response,
                'tc_request_status_name' => 'Group: ' . $statusName . ' (' . $claimData->count() . ' claims)',
                'color' => $color,
                'icon' => $icon,
                'total_claims' => $claimData->count(),
                'total_amount' => number_format($this->calculateTotalClaimAmount($claimData), 2)
            ]);
        }

        // Non-AJAX response
        if ($claimData->isNotEmpty()) {
            $firstClaim = $claimData->first();
            $firstClaim->approvalData = $approvalData;
            $approval = ApprovalHelper::getApprovalData($firstClaim, $user, 'tc_');
            $masterApproveBtn = $approval['masterApproveBtn'];
            $canApprove = $approval['canApprove'];
        }

        return view('admin.ta-da-request.claimgroupdetails', compact(
            'claimData',
            'combinedLog',
            'approvalData',
            'displayDeductionHandler',
            'workingHour',
            'nextApprovalData',
            'actionRoute',
            'masterApproveBtn',
            'canApprove'
        ));
    }

    public function approveMultipleClaims(Request $request)
    {
        $user = Auth::user();
        $response = ['status' => false, 'message' => '', 'result' => false];

        // Validate request with new structure
        $validator = Validator::make($request->all(), [
            'data.claim_ids' => 'required|array',
            'data.claim_ids.*' => 'required|string',
            'data.approval_type' => 'required|in:0,1',
            'data.approval_status' => 'required|numeric',
            'data.approval_action_type' => 'required|string',
            'data.approval_sequence' => 'required|numeric',
            'data.module_id' => 'required|string',
            'data.is_last_approval' => 'required|boolean',
            'data.emp_d_id' => 'required|numeric',
            'data.message' => 'required|string',
            'data.deduction_amount' => 'nullable|array',
            'data.deduction_info' => 'nullable|array',
            'data.payable_amount' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            $response['message'] = 'Validation failed';
            $response['errors'] = $validator->errors();
            return response()->json($response, 422);
        }

        $isApproval = $request->data['approval_type'] == 1;
        $nextApprover = 1;
        $approvalSequence = (int) $request->data['approval_sequence'];

        if ($request->data['approval_action_type'] != 'and') {
            $isLast = 1;
        } else {
            $isLast = (int) $request->data['is_last_approval'];
            if (!$isLast) {
                $nextApprover = $approvalSequence + 1;
            }
        }

        // Get all claims with necessary relationships
        $claims = TadaClaim::with([
            'fh_tada_request_plan.fh_tada_expenses',
            'fh_deduction_log' => function ($q) {
                $q->where('dlog_requester_action', 1);
            }
        ])
            ->whereIn('tc_id', $request->data['claim_ids'])
            ->where('tc_b_id', $user->emp_b_id)
            ->get();

        if ($claims->isEmpty()) {
            $response['message'] = 'No valid claims found to process';
            return response()->json($response, 404);
        }

        // For approval - validate deductions against payable amounts
        if ($isApproval && isset($request->data['deduction_info'])) {
            foreach ($request->data['deduction_info'] as $claimId => $deductions) {
                foreach ($deductions as $expTypeId => $deductionAmount) {
                    if (
                        isset($request->data['payable_amount'][$claimId][$expTypeId]) &&
                        $request->data['payable_amount'][$claimId][$expTypeId] < $deductionAmount
                    ) {
                        $response['message'] = "Deduction amount (₹$deductionAmount) cannot be greater than payable amount (₹{$request->data['payable_amount'][$claimId][$expTypeId]}) for claim $claimId";
                        return response()->json($response, 422);
                    }
                }
            }
        }

        DB::beginTransaction();
        try {
            $successCount = 0;
            $failedClaims = [];

            foreach ($claims as $claim) {
                try {
                    // Get deduction data for this specific claim
                    $claimDeductionAmount = $request->data['deduction_amount'][$claim->tc_id] ?? 0;
                    $claimDeductionInfo = $request->data['deduction_info'][$claim->tc_id] ?? [];
                    $claimPayableAmounts = $request->data['payable_amount'][$claim->tc_id] ?? [];

                    // Prepare data object for common approval function
                    $data = (object)[
                        'approval_status' => $request->data['approval_status'],
                        'approval_type' => $request->data['approval_type'],
                        'approval_action_type' => $request->data['approval_action_type'],
                        'approval_sequence' => $request->data['approval_sequence'],
                        'module_id' => $request->data['module_id'],
                        'is_last_approval' => $request->data['is_last_approval'],
                        'emp_d_id' => $request->data['emp_d_id'],
                        'tc_id' => md5($claim->tc_id),
                        'message' => $request->data['message'],
                        'deduction_amount' => $claimDeductionAmount,
                        'deduction_info' => $claimDeductionInfo,
                        'payable_amount' => $claimPayableAmounts
                    ];

                    if ($isApproval) {
                        $statusData = MasterTable::where([
                            'm_group' => 'APPROVAL_STATUS',
                            'm_id' => $request->data['approval_status']
                        ])
                            ->select('m_id', 'm_name')
                            ->first();

                        app(\App\Http\Controllers\CommonApprovalController::class)
                            ->updateRequestStatusAndLog($data, $statusData, $nextApprover, 'CLAIM_REQUEST_APPROVAL', $isLast);
                    } else {
                        $statusData = MasterTable::where([
                            'm_group' => 'APPROVAL_STATUS',
                            'm_id' => 170 // Rejected
                        ])
                            ->select('m_id', 'm_name')
                            ->first();

                        app(\App\Http\Controllers\CommonApprovalController::class)
                            ->updateRequestStatusAndLog($data, $statusData, 0, 'CLAIM_REQUEST_APPROVAL', $isLast);
                    }

                    $successCount++;
                } catch (\Exception $e) {
                    $failedClaims[] = [
                        'id' => $claim->tc_id,
                        'error' => $e->getMessage()
                    ];
                    continue;
                }
            }

            DB::commit();

            $response['status'] = true;
            $response['result'] = true;
            $response['message'] = ($isApproval ? 'Approved' : 'Rejected') . ' ' . $successCount . ' claims successfully';
            $response['failed'] = $failedClaims;

            return response()->json($response);
        } catch (\Exception $e) {
            DB::rollBack();
            $response['message'] = 'Failed to process claims: ' . $e->getMessage();
            return response()->json($response, 500);
        }
    }

    protected function calculateTotalClaimAmount($claims)
    {
        return $claims->sum('tc_amount');
    }

    protected function generateGroupViewResponse($claimData, $approvalData, $displayDeductionHandler, $combinedLog, $actionRoute, $groupId)
    {
        $response = '
        <div class="row">
            <div class="col-md-12">
                <div class="card overflow-hidden">
                    <div class="card-header">
                        <h3 class="card-title">Grouped Claims Summary</h3>
                        <small class="text-muted">Total Claims: ' . $claimData->count() . '</small>
                    </div>
                    <div class="card-body">';

        // Group Summary Table
        $allExpenses = collect();
        $totalClaimAmount = 0;
        $totalDAAmount = 0;
        $totalTAAmount = 0;
        $totalAdvanceAmount = 0;
        $totalDeductionAmount = 0;

        // Collect all expenses from all claims
        foreach ($claimData as $singleClaim) {
            $rawPlanData = $singleClaim->fh_tada_request_plan;

            if ($rawPlanData && $rawPlanData->fh_tada_expenses) {
                $claimExpenses = $rawPlanData->fh_tada_expenses->where('te_paid_by', 'self');
                $allExpenses = $allExpenses->merge($claimExpenses);
            }

            // Calculate totals
            $totalClaimAmount += $singleClaim->tc_amount ?? 0;
            $totalDAAmount += $singleClaim->tc_da_amount ?? 0;
            $totalAdvanceAmount += $rawPlanData->trp_advance_allowance ?? 0;
            $totalDeductionAmount += $singleClaim->tc_deduction_amount ?? 0;

            // Calculate TA for each claim
            if ($rawPlanData && $rawPlanData->trp_is_details_added) {
                $rawTravelTypeLocal = $rawPlanData->fh_policy_tada_travel_type->fh_travel_type->m_id == 124;
                $rawTravelDetails = $rawPlanData->fh_tada_request_details;
                $rawTravelDetailSumAmt = $rawTravelTypeLocal
                    ? ($rawTravelDetails ? $rawTravelDetails->sum('trd_net_amount') : 0)
                    : TadaRequestDetail::whereHas('fh_policy_tada_travel_vehicle', function ($query) {
                        $query->where('pttv_claim_type_id', 155);
                    })
                    ->where('trd_trp_id', $rawPlanData->trp_id)
                    ->sum('trd_net_amount');

                $totalTAAmount += $rawTravelDetailSumAmt;
            }
        }

        $response .= '
                        <div class="table-responsive push">
                            <table class="table table-bordered table-hover text-nowrap">
                                <tr>
                                    <th class="text-center" style="width: 1%">S.No.</th>
                                    <th>Expense Type</th>
                                    <th class="text-end" style="width: 15%">Total Amount</th>
                                    <th class="text-end" style="width: 15%">Total Deviation</th>
                                    <th class="text-end" style="width: 15%">Net Payable</th>';

        // Add deduction column if approver can make deductions
        if ($approvalData && !$displayDeductionHandler) {
            $response .= '<th class="text-center" style="width: 15%">Deduction</th>';
        }

        $response .= '</tr>';

        // Group expenses by type and display
        $groupedExpenses = $allExpenses->groupBy('fh_expense_type.m_name');
        $iteration = 1;

        foreach ($groupedExpenses as $expenseType => $expenses) {
            $exp_type_id = $expenses->pluck('te_type_id')->first();
            $totalAmount = round($expenses->sum('te_amount') + $expenses->sum('te_taxes'));
            $totalDeviation = round($expenses->sum('te_deviation'));
            $netPayable = $totalAmount - $totalDeviation;

            $response .= '
            <tr>
                <td class="text-center">' . $iteration++ . '</td>
                <td><strong>' . e($expenseType) . '</strong></td>
                <td class="text-end">₹' . number_format($totalAmount, 2) . '</td>
                <td class="text-end">₹' . number_format($totalDeviation, 2) . '</td>
                <td class="text-end">₹' . number_format($netPayable, 2) . '</td>';

            if ($approvalData && !$displayDeductionHandler) {
                $response .= '
                <td>
                    <input type="number"
                           name="group_deduction[' . $exp_type_id . ']"
                           id="group_' . $exp_type_id . '_deduction"
                           data-payableAmount="' . $netPayable . '"
                           class="form-control deduction-input text-end"
                           step="0.01" min="0"
                           placeholder="₹ 0">
                </td>';
            }

            $response .= '</tr>';
        }

        // Summary rows
        if ($totalTAAmount > 0) {
            $response .= '
            <tr class="table-info">
                <td colspan="4" class="font-weight-semibold text-end">Total TA</td>
                <td class="text-end font-weight-semibold">₹' . number_format($totalTAAmount, 2) . '</td>
                <td></td>
            </tr>';
        }

        $response .= '
        <tr class="table-info">
            <td colspan="4" class="font-weight-semibold text-end">Total DA</td>
            <td class="text-end font-weight-semibold">₹' . number_format($totalDAAmount, 2) . '</td>
            <td></td>
        </tr>

        <tr class="table-warning">
            <td colspan="4" class="font-weight-semibold text-end">
                Subtotal <small>(Total Expenses + TA + DA - Deviation)</small>
            </td>
            <td class="text-end font-weight-semibold">₹' . number_format($totalClaimAmount, 2) . '</td>
            <td></td>
        </tr>

        <tr class="table-secondary">
            <td colspan="4" class="font-weight-semibold text-end">Total Advance</td>
            <td class="text-end font-weight-semibold">₹' . number_format($totalAdvanceAmount, 2) . '</td>
            <td></td>
        </tr>

        <tr class="table-secondary">
            <td colspan="4" class="font-weight-semibold text-end">Total Deduction</td>
            <td class="text-end font-weight-semibold">₹' . number_format($totalDeductionAmount, 2) . '</td>
            <td></td>
        </tr>

        <tr class="table-success">
            <td colspan="4" class="font-weight-bold text-uppercase text-end h4 mb-0">Net Payable Amount</td>
            <td class="font-weight-bold h4 mb-0 text-end">₹' . number_format(($totalClaimAmount - ($totalAdvanceAmount + $totalDeductionAmount)), 2) . '</td>
            <td></td>
        </tr>';

        $response .= '
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>';

        // Individual Claims Details Table
        $response .= '
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Individual Claims in this Group</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Claim ID</th>
                                        <th>Employee</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>';

        foreach ($claimData as $singleClaim) {
            $jsonData = $singleClaim->fh_claim_status->m_other;
            $decodedData = json_decode($jsonData, true);
            $statusColor = $decodedData['color'] ?? '#666';

            $response .= '
            <tr>
                <td><strong>' . e($singleClaim->tc_unique_id) . '</strong></td>
                <td>
                    ' . e($singleClaim->fh_employee->emp_full_name) . '<br>
                    <small class="text-muted">(' . e($singleClaim->fh_employee->emp_code) . ')</small>
                </td>
                <td>' . e(\Carbon\Carbon::parse($singleClaim->created_at)->format('d-M-Y')) . '</td>
                <td>₹' . number_format($singleClaim->tc_amount, 2) . '</td>
                <td>
                    <span class="badge" style="background-color: ' . $statusColor . '">
                        ' . e($singleClaim->fh_claim_status->m_name) . '
                    </span>
                </td>
                <td>
                    <a href="' . route('claim-request.request.show', md5($singleClaim->tc_id)) . '"
                       class="btn btn-sm btn-outline-primary" target="_blank">
                        <i class="fa fa-eye"></i> View Details
                    </a>
                </td>
            </tr>';
        }

        $response .= '
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>';

        // Group Approval Section
        if ($actionRoute !== 'claim-request.request.show' && $actionRoute !== 'claim-request-is-paid.request.show') {
            if ($approvalData && !$displayDeductionHandler) {
                $response .= '
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Group Approval Action</h3>
                            </div>
                            <div class="card-body">
                                <form id="groupApprovalForm">
                                    <div class="form-group">
                                        <div id="groupDeductionAmountDiv"></div>
                                        <div class="row">
                                            <div class="col-md-12 col-lg-2">
                                                <label class="form-label mb-0 mt-2">Message</label>
                                            </div>
                                            <div class="col-md-12 col-lg-10">
                                                <textarea rows="3" name="group_message" class="form-control"
                                                         id="groupActionMessage"
                                                         placeholder="Enter message for all claims in this group..."></textarea>
                                            </div>
                                        </div>
                                        <div class="card-footer mt-3">
                                            <div class="row">
                                                <div class="col-md-12 d-flex justify-content-end">
                                                    <a href="javascript:void(0);"
                                                       data-group_id="' . $groupId . '"
                                                       data-approval_type="0"
                                                       data-total_claims="' . $claimData->count() . '"
                                                       class="btn btn-outline-danger mx-3 groupActionBtn">
                                                       <i class="fa fa-times"></i> Reject All
                                                    </a>
                                                    <a href="javascript:void(0);"
                                                       data-group_id="' . $groupId . '"
                                                       data-approval_type="1"
                                                       data-total_claims="' . $claimData->count() . '"
                                                       class="btn btn-success groupActionBtn">
                                                       <i class="fa fa-check"></i> Approve All (' . $claimData->count() . ' Claims)
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>';
            }
        }

        // Combined Activity Log
        $response .= '
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Activity Timeline (All Claims)</h3>
                    </div>
                    <div class="card-body">
                        <div class="col-xl-12">
                            <ul id="groupClaimLogList" class="timeline">';

        foreach ($combinedLog->sortByDesc('created_at')->take(20) as $index => $item2) {
            $className = get_class($item2);

            if ($className == 'App\Models\ApprovalLog') {
                $jsonData2 = $item2->fh_status->m_other;
                $item2DecodedData = json_decode($jsonData2, true);
                $item2Color = $item2DecodedData['color'];
                $item2Icon = $item2DecodedData['web_icon'];
                $item2StatusName = $item2->fh_status->m_name;
                $remark = $item2->log_description;
                $emp_name = isset($item2->fh_employee) ? $item2->fh_employee->emp_full_name : '';
                $emp_code = isset($item2->fh_employee) ? $item2->fh_employee->emp_code : '';
                $emp_designation = isset($item2->fh_employee->fh_designation) ? $item2->fh_employee->fh_designation->dg_name : '';
            } else {
                $item2StatusName = $item2->dlog_requester_action == 1 ? 'Accepted' : 'Declined';
                $item2Color = $item2->dlog_requester_action == 1 ? '#4CAF50' : '#F44336';
                $item2Icon = $item2->dlog_requester_action == 1 ? 'check_circle' : 'cancel';
                $remark = $item2->dlog_description ?? '';
                $emp_name = isset($item2->fh_employee) ? $item2->fh_employee->emp_full_name : '';
                $emp_code = isset($item2->fh_employee) ? $item2->fh_employee->emp_code : '';
                $emp_designation = isset($item2->fh_employee->fh_designation) ? $item2->fh_employee->fh_designation->dg_name : '';
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
            </li>';
        }

        $response .= '
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>';

        // Employee deduction handling section
        if ($actionRoute == 'claim-request.request.show' && $displayDeductionHandler) {
            $response .= '
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Group Deduction Response</h3>
                        </div>
                        <div class="card-body">
                            <form id="groupDeductionForm">
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-12 col-lg-2">
                                            <label class="form-label mb-0 mt-2">Response Message</label>
                                        </div>
                                        <div class="col-md-12 col-lg-10">
                                            <textarea rows="3" name="groupEmpAcceptanceMsg" class="form-control"
                                                     id="groupEmpAcceptanceMsg"
                                                     placeholder="Enter your response for the group deduction..."></textarea>
                                        </div>
                                    </div>

                                    <div class="card-footer mt-3">
                                        <div class="row">
                                            <div class="col-md-12 d-flex justify-content-end">
                                                <a href="javascript:void(0);"
                                                   class="btn btn-outline-danger mx-3 handleGroupDeductionBtn"
                                                   data-group_id="' . $groupId . '"
                                                   data-action="0">
                                                   <i class="fa fa-times"></i> Decline All
                                                </a>
                                                <a href="javascript:void(0);"
                                                   class="btn btn-success handleGroupDeductionBtn"
                                                   data-group_id="' . $groupId . '"
                                                   data-action="1">
                                                   <i class="fa fa-check"></i> Accept All
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>';
        }

        return $response;
    }

   /* public function show(string $id)
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
            ->where((DB::raw('md5(tc_unique_id)')), $id)
            ->first();

        $approvalData = ApprovalHelper::getApprovalOrRejectionData($claimData->tc_trp_id, $claimData->tc_status, $claimData->tc_am_id, NULL, 146);

        $displayDeductionHandler = $claimData->fh_deduction_log->whereNull('dlog_requester_action')->first();



        $workingHour = 8;

        $nextApprovalData = DB::table('next_approval_details')->where('nxt_tc_id', $claimData->tc_id)->first();

        //We are also storing the claim approval log based on the plan's primary ID.
        if($claimData->tc_am_id){//This is for approval based on hierarchy
            $claimApproval = ApprovalLog::where('log_request_id', $claimData->tc_trp_id)
               ->where(function ($query) use ($claimData) {
                   $query->where('log_am_id', $claimData->tc_am_id)
                         ->orWhere('log_module_id', $claimData->tc_module_id);
               })
               ->get();
       }else{//This is for employee-wise approval.
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

                foreach ($combinedLog->sortBy('updated_at') as $index=> $item2) {
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
                    <li class="' . e(($index +1) % 2 ? 'primary' :'success') . '">
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

            if($actionRoute == 'claim-request.request.show' && $displayDeductionHandler) {
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

            return response()->json(['html' => $response, 'tc_request_status_name' => $claimData->fh_claim_status->m_name ?? 'N/A' , 'color' => $color, 'icon'=> $icon]);
        }

        // Check if the user employee mapping can approve start
        $claimData->approvalData = $approvalData;
        $approval = ApprovalHelper::getApprovalData($claimData, $this->user, 'tc_');
        $masterApproveBtn = $approval['masterApproveBtn'];
        $canApprove = $approval['canApprove'];

        // Check if the user employee mapping can approve end
        return view('admin.ta-da-request.claimgroupdetails', compact('claimData', 'combinedLog', 'approvalData', 'displayDeductionHandler',  'workingHour', 'nextApprovalData', 'actionRoute','masterApproveBtn','canApprove'));
    }*/

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
