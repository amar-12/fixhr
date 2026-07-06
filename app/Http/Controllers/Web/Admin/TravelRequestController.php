<?php

namespace App\Http\Controllers\Web\Admin;

use App\Helpers\ApprovalHelper;
use App\Helpers\RolePermissionLogics;
use App\Http\Controllers\Controller;
use App\Models\AdvanceLog;
use App\Models\ApprovalLog;
use App\Models\ApprovalModule;
use App\Models\MasterTable;
use App\Models\ProcessApprover;
use App\Models\TadaRequestPlan;
use Carbon\Carbon;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Exp;
use App\Models\Employee;

class TravelRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function index(Request $request)
    {
        return $this->handleRequest($request, false);
    }
    public function index2(Request $request)
    {
        return $this->handleRequest($request, true);
    }

    private function handleRequest(Request $request, $isIndex2)
    {
        $user = Auth::user();
        $isSuper = $user->emp_role_id;
        $employeeFilter = request()->input('travel_employeeFilter');
        $branchFilter = request()->input('travel_branchFilter');
        $gradeFilter = request()->input('travel_gradeFilter');
        $statusFilter = request()->input('travel_statusFilter');
        $fromToDateFilter = request()->input('fromDate');

        // dd($fromToDateFilter);
        if ($request->ajax()) {
            if ($isIndex2) {
                $dynamicConditions = [
                    [
                        'method' => 'where',
                        'args' => ['trp_b_id', $user->emp_b_id]
                    ],
                    [
                        'method' => 'where',
                        'args' => ['trp_request_status', '!=', 139]
                    ],
                    // [
                    //     'method' => 'where',
                    //     'args' => ['trp_request_status', '!=', 156]
                    // ],
                    [
                        'method' => 'where',
                        'args' => ['trp_request_status', '!=', 192]
                    ],
                    [
                        'method' => 'where',
                        'args' => ['trp_emp_id',  $user->emp_id]
                    ],
                    [
                        'method' => 'select',
                        'args' => [
                            'trp_id',
                            'trp_b_id',
                            'trp_br_id',
                            'trp_emp_id',
                            'trp_pttt_id',
                            'trp_ptc_id',
                            'trp_module_id',
                            'trp_name',
                            'trp_purpose',
                            'trp_advance_allowance',
                            'trp_request_status',
                            'updated_at',
                            'trp_call_id',
                            'trp_unique_id',
                            'trp_am_id',
                            'created_at',
                        ],
                        'relation' => [
                            'fh_employee:emp_code,emp_id,emp_full_name,emp_dg_id,emp_profile_photo,emp_d_id',
                            'fh_employee.fh_department:d_id,d_name',
                            'fh_employee.fh_designation:dg_id,dg_name',
                            'fh_policy_tada_travel_type.fh_travel_type:m_id,m_name',
                            'fh_policy_tada_category:ptc_id,ptc_name',
                            'fh_policy_tada_travel_mode.fh_travel_mode:m_id,m_name',
                            'fh_approval_status:m_id,m_name,m_other'
                        ]
                    ],
                    [
                        'method' => 'sortBy',
                        'args' => ['trp_id', 'trp_emp_id', 'trp_b_id', 'updated_at', 'trp_pttt_id', 'trp_ptc_id', 'trp_name', 'trp_purpose', 'trp_advance_allowance', 'trp_request_status', 'trp_id']
                    ],
                ];
            } else {
                $dynamicConditions = [
                    [
                        'method' => 'where',
                        'args' => ['trp_b_id', $user->emp_b_id]
                    ],
                    // [
                    //     'method' => 'where',
                    //     'args' => ['trp_request_status', '!=', 156]
                    // ],
                    [
                        'method' => 'where',
                        'args' => ['trp_request_status', '!=', 192]
                    ],
                    [
                        'method' => 'where',
                        'args' => ['trp_request_status', '!=', 139]
                    ],
                    [
                        'method' => 'select',
                        'args' => [
                            'trp_id',
                            'trp_b_id',
                            'trp_br_id',
                            'trp_emp_id',
                            'trp_pttt_id',
                            'trp_ptc_id',
                            'trp_module_id',
                            'trp_name',
                            'trp_purpose',
                            'trp_advance_allowance',
                            'trp_request_status',
                            'updated_at',
                            'trp_call_id',
                            'trp_unique_id',
                            'trp_am_id',
                            'created_at',
                        ],
                        'relation' => [
                            'fh_employee:emp_code,emp_id,emp_full_name,emp_dg_id,emp_profile_photo,emp_d_id',
                            'fh_employee.fh_department:d_id,d_name',
                            'fh_employee.fh_designation:dg_id,dg_name',
                            'fh_policy_tada_travel_type.fh_travel_type:m_id,m_name',
                            'fh_policy_tada_category:ptc_id,ptc_name',
                            'fh_policy_tada_travel_mode.fh_travel_mode:m_id,m_name',
                            'fh_approval_status:m_id,m_name,m_other',
                            'fh_travel_purpose:tp_id,tp_name'
                        ]
                    ],
                    [
                        'method' => 'sortBy',
                        'args' => ['trp_id', 'trp_emp_id', 'trp_b_id', 'updated_at', 'trp_pttt_id', 'trp_ptc_id', 'trp_name', 'trp_purpose', 'trp_advance_allowance', 'trp_request_status', 'trp_id']
                    ],
                ];

                if ($isSuper != 1) {
                    $dynamicConditions[] = [
                        'method' => 'where',
                        'args' => ['trp_request_status', '!=', 171]
                    ];
                }
            }

            // Filter conditions
            if ($employeeFilter != '') {
                $dynamicConditions[] = [
                    'method' => 'where',
                    'args' => ['trp_emp_id', $employeeFilter]
                ];
            }

            if ($branchFilter != '') {
                $dynamicConditions[] = [
                    'method' => 'where',
                    'args' => ['trp_br_id', $branchFilter]
                ];
            }

            if ($gradeFilter != '') {
                $dynamicConditions[] = [
                    'method' => 'whereHas',
                    'args' => ['emp_grade_id', $gradeFilter],
                    'relation' => 'fh_employee'
                ];
            }

            if ($statusFilter != '') {
                $dynamicConditions[] = [
                    'method' => 'where',
                    'args' => ['trp_request_status', $statusFilter]
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

            $searchColumns = ['trp_name', 'trp_emp_id','trp_unique_id','trp_purpose', 'trp_id', 'created_at', 'trp_request_status'];
            $searchRelationships = [
                'fh_employee' => ['emp_full_name','emp_fname','emp_mname','emp_lname','emp_code'],
                'fh_employee.fh_designation' => ['dg_name'],
                'fh_policy_tada_travel_type.fh_travel_type' => ['m_name'],
                'fh_policy_tada_category' => ['ptc_name'],
                // 'fh_policy_tada_travel_mode' => ['m_name'],
                'fh_approval_status' => ['m_name'],
                'fh_travel_purpose' => ['tp_name'],
                // 'fh_business' => ['b_name'],
            ];

            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new TadaRequestPlan(),
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
                searchRelationships: $searchRelationships
            ))->getServerSideDataTable();

            $rowData = array();
            $i = 0;
            foreach ($list as $key => $travel) {

                $nextApproval = ApprovalHelper::getNextApprovalDetails(145, $travel->trp_id, $travel->trp_b_id);
                $travel_advance = AdvanceLog::where('adl_trp_id', $travel->trp_id)->first();

                $is_auto_approval = $travel->fh_policy_tada_travel_type->pttt_approval_type_id == 197;

                if (!$is_auto_approval) {
                    $approval = ApprovalHelper::checkApproval($user->emp_b_id, $travel->trp_module_id, $travel->trp_id, optional($travel->fh_employee)->emp_d_id, $travel->trp_emp_id);
                }
                $i++;
                $row = [];
                $row[] = $i;

                $employeeName = isset($travel->fh_employee) ? ($travel->fh_employee->emp_code ?  $travel->fh_employee->emp_code . ' - ' : '') . $travel->fh_employee->emp_full_name  : '';
                $designation = isset($travel->fh_employee->fh_designation) ? $travel->fh_employee->fh_designation->dg_name : '';

                $row[] = '<div class="d-flex dynamic-width">
                            <div class="me-3 mt-0 mt-sm-1 d-block">
                                <h6 class="mb-1 fs-14">' . $employeeName . '</h6>
                                <p class="text-muted mb-0 fs-12">' . $designation . '</p>
                            </div>
                        </div>';
                $row[] = isset($travel->fh_policy_tada_travel_type->fh_travel_type) ? $travel->fh_policy_tada_travel_type->fh_travel_type->m_name : '';
                $row[] = '#' . $travel->trp_unique_id;


                $row[] = $travel->trp_name;
                $row[] =  $travel->fh_travel_purpose->tp_name;
                $row[] = $travel->trp_advance_allowance;
                $row[] = Carbon::parse($travel->created_at)->format('d-M-Y H:i');
                // $row[] = isset($travel->fh_policy_tada_category) ? $travel->fh_policy_tada_category->ptc_name : '';


                $logData = $travel->fh_plan_approval_log;
                // Check if the log data is not empty
                $formattedLog = '';
                $comma = false;

                if (count($logData)) {
                    foreach ($logData as $log) {
                        if ($log->log_am_id == $travel->trp_am_id) {
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
                    $logData2 = $travel->fh_approval_log2;

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
                $popoverContent = $formattedLog === '' ?  ($travel->trp_request_status == 171 ? 'Auto Approved' : 'Awaiting') : $formattedLog;

                // Ensure the content is properly escaped for use in data attributes
                $safePopoverContent = htmlspecialchars($popoverContent, ENT_QUOTES, 'UTF-8');
                $jsonData = $travel->fh_approval_status->m_other;

                // Decode the JSON to an associative array
                $decodedData = json_decode($jsonData, true); // true for associative array

                // Now access the color value
                $color = $decodedData['color'] ?? '';
                $icon = $decodedData['web_icon'] ?? '';
                $row[] = '<span class="badge" style="background-color:' . $color . '"><i class="' . $icon . '">&nbsp;</i>' . (isset($travel->fh_approval_status->m_name) ? ($travel->fh_approval_status->m_name) : '') . '</span> &nbsp;<i class="feather feather-info text-primary fs-14"
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
                        $approvalMapping = ApprovalHelper::getApprovalMapping($user->emp_b_id, $travel->trp_emp_id, $travel->trp_module_id);
                        if ($approvalMapping) {
                            $approvalArray = ApprovalHelper::getApprovalArray($approvalMapping);
                            $approvalLog = $travel?->fh_approval_log2?->pluck('log_user_id')?->toArray() ?? [];
                            $diff = array_values(array_diff($approvalArray, $approvalLog));

                            if ($diff) {
                                $approverName = Employee::where('emp_id',$diff[0])->pluck('emp_full_name')->first() ??  '---';
                            } else {
                                $approverName = '---';
                            }
                        } else {
                            $approverName = '---';
                        }
                    }

                // $row[] = '<span class="text-center">' . (isset($nextApproval['approver_name']) && $nextApproval['approver_name'] ? $nextApproval['approver_name'] : "---") . '</span>';
                $row[] = '<span class="text-center">' . (isset($nextApproval['approver_name']) && $nextApproval['approver_name'] ? $nextApproval['approver_name'] : $approverName ?? '---') . '</span>';
                if ($isIndex2) {
                    $row[] = '<a class="" href="' . (RolePermissionLogics::check_route_permission('admin/ta-da-request/travel-request/show/{id}', 116) ? route('travel-request.request.show', md5($travel->trp_id)) : '') . '">

                        <i class="feather-eye fs-6" style=" margin-top: 10;"></i>
                            </a>';
                } else {
                    $row[] = '<a class="" href="' . (RolePermissionLogics::check_route_permission('admin/ta-da-request/travel/show/{id}', 116) ? route('travel.request.show', md5($travel->trp_id)) : '') . '">

                     <i class="feather-eye fs-6" style=" margin-top: 10;"></i>
                            </a>';
                }
                $rowData[] = $row;
            }

            $output = array(
                'draw' => $request->input('draw'),
                'recordsTotal' =>  sizeof($list),
                'recordsFiltered' => (new DynamicModelDataTableHelper(
                    eloquentModel: new TadaRequestPlan(),
                    dynamicConditions: $dynamicConditions,
                    searchColumns: $searchColumns,
                    searchRelationships: $searchRelationships
                ))->countFilteredServerSideDataTable(),
                'data' => $rowData,
            );

            return json_encode($output);
        }

        $columns = [
            'S. No.',
            'Employee Name',
            'Travel Type',
            'Travel Id',
            'Trip Name',
            'Trip Purpose',
            'Advance',
            'Applied Date',
            // 'Travel Category',
            'Status',
            'Submitted To',
            'Action',
        ];
        $titleRoute = $isIndex2 ? 'travel-request' : 'travel';
        $title = $isIndex2 ? 'Travel Requests' : 'Travel Approval';
        return view('admin.ta-da-request.travel', compact('columns', 'title', 'titleRoute', 'titleRoute'));
    }

    /*private function handleRequest(Request $request, $isIndex2)
    {
        $user = Auth::user();
        $isSuper = $user->emp_role_id;
        $employeeFilter = request()->input('travel_employeeFilter');
        $branchFilter = request()->input('travel_branchFilter');
        $gradeFilter = request()->input('travel_gradeFilter');
        $statusFilter = request()->input('travel_statusFilter');
        $fromToDateFilter = request()->input('fromDate');

        // dd($fromToDateFilter);
        if ($request->ajax()) {
            if ($isIndex2) {
                $dynamicConditions = [
                    [
                        'method' => 'where',
                        'args' => ['trp_b_id', $user->emp_b_id]
                    ],
                    [
                        'method' => 'where',
                        'args' => ['trp_request_status', '!=', 139]
                    ],
                    // [
                    //     'method' => 'where',
                    //     'args' => ['trp_request_status', '!=', 156]
                    // ],
                    [
                        'method' => 'where',
                        'args' => ['trp_request_status', '!=', 192]
                    ],
                    [
                        'method' => 'where',
                        'args' => ['trp_emp_id',  $user->emp_id]
                    ],
                    [
                        'method' => 'select',
                        'args' => [
                            'trp_id',
                            'trp_b_id',
                            'trp_br_id',
                            'trp_emp_id',
                            'trp_pttt_id',
                            'trp_ptc_id',
                            'trp_module_id',
                            'trp_name',
                            'trp_purpose',
                            'trp_advance_allowance',
                            'trp_request_status',
                            'updated_at',
                            'trp_call_id',
                            'trp_unique_id',
                            'trp_am_id',
                            'created_at',
                        ],
                        'relation' => [
                            'fh_employee:emp_code,emp_id,emp_full_name,emp_dg_id,emp_profile_photo,emp_d_id,emp_is_geowork_active',
                            'fh_employee.fh_department:d_id,d_name',
                            'fh_employee.fh_designation:dg_id,dg_name',
                            'fh_policy_tada_travel_type.fh_travel_type:m_id,m_name',
                            'fh_policy_tada_category:ptc_id,ptc_name',
                            'fh_policy_tada_travel_mode.fh_travel_mode:m_id,m_name',
                            'fh_approval_status:m_id,m_name,m_other'
                        ]
                    ],
                    [
                        'method' => 'sortBy',
                        'args' => ['trp_id', 'trp_emp_id', 'trp_b_id', 'updated_at', 'trp_pttt_id', 'trp_ptc_id', 'trp_name', 'trp_purpose', 'trp_advance_allowance', 'trp_request_status', 'trp_id']
                    ],
                ];
            } else {
                $dynamicConditions = [
                    [
                        'method' => 'where',
                        'args' => ['trp_b_id', $user->emp_b_id]
                    ],
                    // [
                    //     'method' => 'where',
                    //     'args' => ['trp_request_status', '!=', 156]
                    // ],
                    [
                        'method' => 'where',
                        'args' => ['trp_request_status', '!=', 192]
                    ],
                    [
                        'method' => 'where',
                        'args' => ['trp_request_status', '!=', 139]
                    ],
                    [
                        'method' => 'select',
                        'args' => [
                            'trp_id',
                            'trp_b_id',
                            'trp_br_id',
                            'trp_emp_id',
                            'trp_pttt_id',
                            'trp_ptc_id',
                            'trp_module_id',
                            'trp_name',
                            'trp_purpose',
                            'trp_advance_allowance',
                            'trp_request_status',
                            'updated_at',
                            'trp_call_id',
                            'trp_unique_id',
                            'trp_am_id',
                            'created_at',
                        ],
                        'relation' => [
                            'fh_employee:emp_code,emp_id,emp_full_name,emp_dg_id,emp_profile_photo,emp_d_id,emp_is_geowork_active',
                            'fh_employee.fh_department:d_id,d_name',
                            'fh_employee.fh_designation:dg_id,dg_name',
                            'fh_policy_tada_travel_type.fh_travel_type:m_id,m_name',
                            'fh_policy_tada_category:ptc_id,ptc_name',
                            'fh_policy_tada_travel_mode.fh_travel_mode:m_id,m_name',
                            'fh_approval_status:m_id,m_name,m_other',
                            'fh_travel_purpose:tp_id,tp_name'
                        ]
                    ],
                    [
                        'method' => 'sortBy',
                        'args' => ['trp_id', 'trp_emp_id', 'trp_b_id', 'updated_at', 'trp_pttt_id', 'trp_ptc_id', 'trp_name', 'trp_purpose', 'trp_advance_allowance', 'trp_request_status', 'trp_id']
                    ],
                ];

                if ($isSuper != 1) {
                    $dynamicConditions[] = [
                        'method' => 'where',
                        'args' => ['trp_request_status', '!=', 171]
                    ];
                }
            }

            // Filter conditions
            if ($employeeFilter != '') {
                $dynamicConditions[] = [
                    'method' => 'where',
                    'args' => ['trp_emp_id', $employeeFilter]
                ];
            }

            if ($branchFilter != '') {
                $dynamicConditions[] = [
                    'method' => 'where',
                    'args' => ['trp_br_id', $branchFilter]
                ];
            }

            if ($gradeFilter != '') {
                $dynamicConditions[] = [
                    'method' => 'whereHas',
                    'args' => ['emp_grade_id', $gradeFilter],
                    'relation' => 'fh_employee'
                ];
            }

            if ($statusFilter != '') {
                $dynamicConditions[] = [
                    'method' => 'where',
                    'args' => ['trp_request_status', $statusFilter]
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

            $searchColumns = ['trp_name', 'trp_emp_id', 'trp_id', 'created_at', 'trp_request_status'];
            $searchRelationships = [
                'fh_employee' => ['emp_full_name', 'emp_code'],
                'fh_employee.fh_designation' => ['dg_name'],
                'fh_policy_tada_travel_type.fh_travel_type' => ['m_name'],
                'fh_policy_tada_category' => ['ptc_name'],
                // 'fh_policy_tada_travel_mode' => ['m_name'],
                'fh_approval_status' => ['m_name'],
                // 'fh_business' => ['b_name'],
            ];

            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new TadaRequestPlan(),
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
                searchRelationships: $searchRelationships
            ))->getServerSideDataTable();

            $rowData = array();
            $i = 0;

            foreach ($list as $key => $travel) {

                $nextApproval = ApprovalHelper::getNextApprovalDetails(145, $travel->trp_id, $travel->trp_b_id);
                $travel_advance = AdvanceLog::where('adl_trp_id', $travel->trp_id)->first();

                $is_auto_approval = $travel->fh_policy_tada_travel_type->pttt_approval_type_id == 197;

                if (!$is_auto_approval) {
                    $approval = ApprovalHelper::checkApproval($user->emp_b_id, $travel->trp_module_id, $travel->trp_id, optional($travel->fh_employee)->emp_d_id, $travel->trp_emp_id);
                }
                $i++;
                $row = [];
                $row[] = $i;

                $employeeName = isset($travel->fh_employee) ? ($travel->fh_employee->emp_code ?  $travel->fh_employee->emp_code . ' - ' : '') . $travel->fh_employee->emp_full_name  : '';
                $designation = isset($travel->fh_employee->fh_designation) ? $travel->fh_employee->fh_designation->dg_name : '';

                $row[] = '<div class="d-flex dynamic-width">
                            <div class="me-3 mt-0 mt-sm-1 d-block">
                                <h6 class="mb-1 fs-14">' . $employeeName . '</h6>
                                <p class="text-muted mb-0 fs-12">' . $designation . '</p>
                            </div>
                        </div>';
                $row[] = isset($travel->fh_policy_tada_travel_type->fh_travel_type) ? $travel->fh_policy_tada_travel_type->fh_travel_type->m_name : '';
                $row[] = '#' . $travel->trp_unique_id;


                $row[] = $travel->trp_name;
                $row[] =  $travel->fh_travel_purpose->tp_name;
                $row[] = $travel->trp_advance_allowance;
                $row[] = Carbon::parse($travel->created_at)->format('d-M-Y H:i');

                $row[] = ($travel->fh_tada_request_details && $travel->fh_tada_request_details->isNotEmpty())
                    ? match ((int) $travel->fh_tada_request_details->first()->trd_geo_work_active) {
                        1 => '<span class="text-success">Active</span>',
                        0 => '<span class="text-danger">Inactive</span>',
                        default => '---'
                    }
                    : '---';

                // $row[] = isset($travel->fh_policy_tada_category) ? $travel->fh_policy_tada_category->ptc_name : '';


                $logData = $travel->fh_plan_approval_log;
                // Check if the log data is not empty
                $formattedLog = '';
                $comma = false;

                if (count($logData)) {
                    foreach ($logData as $log) {
                        if ($log->log_am_id == $travel->trp_am_id) {
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
                    $logData2 = $travel->fh_approval_log2;

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
                $popoverContent = $formattedLog === '' ?  ($travel->trp_request_status == 171 ? 'Auto Approved' : 'Awaiting') : $formattedLog;

                // Ensure the content is properly escaped for use in data attributes
                $safePopoverContent = htmlspecialchars($popoverContent, ENT_QUOTES, 'UTF-8');
                $jsonData = $travel->fh_approval_status->m_other;

                // Decode the JSON to an associative array
                $decodedData = json_decode($jsonData, true); // true for associative array

                // Now access the color value
                $color = $decodedData['color'] ?? '';
                $icon = $decodedData['web_icon'] ?? '';
                $row[] = '<span class="badge" style="background-color:' . $color . '"><i class="' . $icon . '">&nbsp;</i>' . (isset($travel->fh_approval_status->m_name) ? ($travel->fh_approval_status->m_name) : '') . '</span> &nbsp;<i class="feather feather-info text-primary fs-14"
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
                        $approvalMapping = ApprovalHelper::getApprovalMapping($user->emp_b_id, $travel->trp_emp_id, $travel->trp_module_id);
                        if ($approvalMapping) {
                            $approvalArray = ApprovalHelper::getApprovalArray($approvalMapping);
                            $approvalLog = $travel?->fh_approval_log2?->pluck('log_user_id')?->toArray() ?? [];
                            $diff = array_values(array_diff($approvalArray, $approvalLog));

                            if ($diff) {
                                $approverName = Employee::where('emp_id',$diff[0])->pluck('emp_full_name')->first() ??  '---';
                            } else {
                                $approverName = '---';
                            }
                        } else {
                            $approverName = '---';
                        }
                    }

                // $row[] = '<span class="text-center">' . (isset($nextApproval['approver_name']) && $nextApproval['approver_name'] ? $nextApproval['approver_name'] : "---") . '</span>';
                $row[] = '<span class="text-center">' . (isset($nextApproval['approver_name']) && $nextApproval['approver_name'] ? $nextApproval['approver_name'] : $approverName ?? '---') . '</span>';
                if ($isIndex2) {
                    $row[] = '<a class="" href="' . (RolePermissionLogics::check_route_permission('admin/ta-da-request/travel-request/show/{id}', 116) ? route('travel-request.request.show', md5($travel->trp_id)) : '') . '">

                        <i class="feather-eye fs-6" style=" margin-top: 10;"></i>
                            </a>';
                } else {
                    $row[] = '<a class="" href="' . (RolePermissionLogics::check_route_permission('admin/ta-da-request/travel/show/{id}', 116) ? route('travel.request.show', md5($travel->trp_id)) : '') . '">

                     <i class="feather-eye fs-6" style=" margin-top: 10;"></i>
                            </a>';
                }
                $rowData[] = $row;
            }

            $output = array(
                'draw' => $request->input('draw'),
                'recordsTotal' =>  sizeof($list),
                'recordsFiltered' => (new DynamicModelDataTableHelper(
                    eloquentModel: new TadaRequestPlan(),
                    dynamicConditions: $dynamicConditions,
                    searchColumns: $searchColumns,
                    searchRelationships: $searchRelationships
                ))->countFilteredServerSideDataTable(),
                'data' => $rowData,
            );

            return json_encode($output);
        }

        $columns = [
            'S. No.',
            'Employee Name',
            'Travel Type',
            'Travel Id',
            'Trip Name',
            'Trip Purpose',
            'Advance',
            'Applied Date',
            'Geowork',
            // 'Travel Category',
            'Status',
            'Submitted To',
            'Action',
        ];
        $titleRoute = $isIndex2 ? 'travel-request' : 'travel';
        $title = $isIndex2 ? 'Travel Requests' : 'Travel Approval';
        return view('admin.ta-da-request.travel', compact('columns', 'title', 'titleRoute', 'titleRoute'));
    }*/
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $actionRoute = request()->route()->getName();
        try {


            $user = Auth::user();
            $data = TadaRequestPlan::with(
                'fh_employee:emp_id,emp_fname,emp_mname,emp_lname,emp_dg_id,emp_d_id,emp_phone,emp_code,emp_profile_photo,emp_full_name,emp_email,emp_status,emp_is_geowork_active',
                'fh_employee.fh_department:d_id,d_name',
                'fh_employee.fh_designation:dg_id,dg_name',
                'fh_policy_tada_travel_type.fh_travel_type:m_id,m_name',
                'fh_policy_tada_category:ptc_id,ptc_name',
                'fh_policy_tada_travel_mode.fh_travel_mode:m_id,m_name',
                'fh_branch:br_id,br_name',
                'fh_tada_request_details',
                'fh_tada_request_details.fh_policy_tada_travel_mode.fh_travel_mode:m_id,m_name',
                'fh_tada_request_details.fh_policy_tada_travel_vehicle.fh_vehicle:m_id,m_name',
                'fh_approval_status:m_id,m_name,m_other'
            )->where(['trp_b_id' => $user->emp_b_id])->where((DB::raw('md5(trp_id)')), $id)->first();

            // Define module IDs
            $travelModuleId  = 145;
            $claimModuleId   = 146;
            $advanceModuleId = 199;

            // Fetch Approval Modules
            $travelModuleIds  = ApprovalModule::where('am_b_id', $user->emp_b_id)->where('am_module_id', $travelModuleId)->pluck('am_id')->toArray();
            $claimModuleIds   = ApprovalModule::where('am_b_id', $user->emp_b_id)->where('am_module_id', $claimModuleId)->pluck('am_id')->toArray();
            $advanceModuleIds = ApprovalModule::where('am_b_id', $user->emp_b_id)->where('am_module_id', $advanceModuleId)->pluck('am_id')->toArray();

            // Fetch ProcessApprover for all modules (ordered by sequence)
            $approvalDetailsTravel  = ProcessApprover::where('pa_b_id', $user->emp_b_id)->whereIn('pa_am_id', $travelModuleIds)->orderBy('pa_sequence')->get();
            $approvalDetailsClaim   = ProcessApprover::where('pa_b_id', $user->emp_b_id)->whereIn('pa_am_id', $claimModuleIds)->orderBy('pa_sequence')->get();
            $approvalDetailsAdvance = ProcessApprover::where('pa_b_id', $user->emp_b_id)->whereIn('pa_am_id', $advanceModuleIds)->orderBy('pa_sequence')->get();

            // Claim Tada ID
            $TadaClaimId = $data->fh_tada_claim;
            $tcId = $TadaClaimId ? $TadaClaimId->tc_id : null; // Check if not null before accessing

            // Advance Tada ID
            $TadaAdvanceId = $data->fh_tada_advance_approval_log;
            $TadaAdvanceDetails = $TadaAdvanceId->toArray();

            // Extracting adl_id values from the collection
            $advanceIds = $TadaAdvanceId->pluck('adl_id')->toArray();


            // Fetch Approval Logs
            $approvalLogTravel = ApprovalLog::whereIn('log_am_id', $travelModuleIds)->where(DB::raw('md5(log_request_id)'), $id)->get()->keyBy('log_user_id');
            $approvalLogClaim = ApprovalLog::whereIn('log_am_id', $claimModuleIds)->where(DB::raw('md5(log_request_id)'), $id)->get()->keyBy('log_user_id');
            $approvalLogAdvance = ApprovalLog::whereIn('log_am_id', $advanceModuleIds)->whereIn(DB::raw('md5(log_request_id)'), array_map('md5', $advanceIds))->get();

            // dd($data->fh_tada_advance_approval_log);

            $approvalData = ApprovalHelper::getApprovalOrRejectionData($data->trp_id, $data->trp_request_status, $data->trp_am_id, NULL, 145);

            // Check if the user employee mapping can approve start
            $data->approvalData = $approvalData;
            $approval = ApprovalHelper::getApprovalData($data, $this->user, 'trp_');
            $masterApproveBtn = $approval['masterApproveBtn'];
            $canApprove = $approval['canApprove'];
            // Check if the user employee mapping can approve end
            if (request()->ajax()) {
                $response = '
                <div class="card">
                    <div class="card-body border-0 p-0">
                        ' . (($actionRoute != 'travel-request.request.show') ? '
                            ' . ($approvalData ? '
                                <div class="card-header">
                                    <h3 class="card-title">Approval Or Reject</h3>
                                </div>
                                <div class="card-body">
                                    <form id="approvalForm">
                                        <div class="form-group">
                                            <div class="row">
                                                <label class="form-label mb-0 mt-2">Message</label>
                                                <div class="col-md-12 col-lg-12">
                                                    <textarea rows="2" name="message" class="form-control" id="actionMessage"></textarea>
                                                </div>
                                            </div>

                                            <div class="card-footer mt-3">
                                                <div class="row">
                                                    <div class="col-md-12 col-lg-12 d-flex justify-content-end">
                                                        <button href="javascript:void(0);"
                                                            data-approval_status="' . $approvalData->fh_approver_status->m_id . '"
                                                            data-approval_type="0"
                                                            data-approval_action_type="' . $approvalData->pa_type . '"
                                                            data-approval_sequence="' . $approvalData->pa_sequence . '"
                                                            data-trp_id="' . md5($data->trp_id) . '"
                                                            data-module_id="' . md5($approvalData->pa_am_id) . '"
                                                            data-is_last_approval="' . $approvalData->pa_last . '"
                                                            class="btn btn-outline-danger  actionBtn mx-3">Reject</button>

                                                        <button href="javascript:void(0);"
                                                            data-approval_status="' . $approvalData->fh_approver_status->m_id . '"
                                                            data-approval_type="1"
                                                            data-approval_action_type="' . $approvalData->pa_type . '"
                                                            data-approval_sequence="' . $approvalData->pa_sequence . '"
                                                            data-trp_id="' . md5($data->trp_id) . '"
                                                            data-module_id="' . md5($approvalData->pa_am_id) . '"
                                                            data-is_last_approval="' . $approvalData->pa_last . '"
                                                            class="btn btn-success actionBtn">' . $approvalData->fh_approver_status->m_name . '</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            ' : '') . '
                        ' : '') . '
                    </div>
                </div>
                ';

                if (count($data->fh_plan_approval_log) > 0) {
                    $response .= '
                    <div class="card">
                        <div class="card-header px-3">
                            <div class="card-title">Approval Details</div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-vcenter text-nowrap border-bottom">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="col-md-4">Name</th>
                                            <th class="col-md-4">Action</th>
                                            <th class="col-md-4">Remark</th>
                                        </tr>
                                    </thead>
                                    <tbody>';

                    foreach ($data->fh_plan_approval_log as $item) {
                        $response .= '
                            <tr>
                                <td class="col-md-4">' . ($item->fh_employee->emp_full_name ?? '') . '</td>
                                <td class="col-md-4">' . $item->fh_status->m_name . '</td>
                                <td class="col-md-4 text-nowrap">' . $item->log_description . '</td>
                            </tr>';
                    }

                    $response .= '
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>';
                }

                $jsonData = $data->fh_approval_status->m_other;

                // Decode the JSON to an associative array
                $decodedData = json_decode($jsonData, true); // true for associative array

                // Now access the color value
                $color = $decodedData['color'];
                $icon = $decodedData['web_icon'];
                return response()->json(['html' => $response, 'trp_request_status_name' => $data->fh_approval_status->m_name, 'color' => $color, 'icon' => $icon]);
            }
            return view('admin.ta-da-request.traveldetails', compact('data', 'approvalData', 'actionRoute', 'canApprove', 'masterApproveBtn', 'approvalDetailsTravel', 'approvalDetailsClaim', 'approvalDetailsAdvance', 'approvalLogTravel', 'approvalLogClaim', 'approvalLogAdvance', 'TadaAdvanceDetails'));
        } catch (Exception $e) {
            \Log::info('TadaRequestPlan data fetched', ['data' => $e->getMessage()]);
            if ('The payload is invalid.' == $e->getMessage()) {
                return redirect()->route('travel.request.index');
            }
            return redirect()->route('travel.request.index');
        }
    }

    public function approve(Request $request)
    {
        $data = TadaRequestPlan::findOrFail($request->log_request_id);

        // Process approval using the service
        $response = ApprovalHelper::processApproval($request, $data, $this->user, 'trp_');

        return response()->json($response, $response['status'] === 'success' ? 200 : 500);
    }
}
