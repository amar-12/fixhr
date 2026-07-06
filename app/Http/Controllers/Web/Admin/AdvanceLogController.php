<?php

namespace App\Http\Controllers\Web\Admin;

use App\Helpers\ApprovalHelper;
use App\Helpers\RolePermissionLogics;
use App\Http\Controllers\Controller;
use App\Models\AdvanceLog;
use App\Models\Employee;
use App\Models\PolicyTadaCategory;
use App\Models\ProcessApprover;
use App\Models\TadaRequestPlan;
use App\Models\EmployeeApprovalMapping;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;

class AdvanceLogController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function advanceLog()
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;

        $advance_log = AdvanceLog::whereHas('fh_tada_request_plan', function ($query) use ($businessId) {
            $query->where('trp_b_id', $businessId)
                ->whereColumn('trp_id', 'adl_trp_id');
        })->get();

        $tadaPlanList = TadaRequestPlan::where('trp_b_id', $businessId)
            ->where('trp_is_claimed', '0')
            ->get();

        $AdvancUpdate = AdvanceLog::whereNull('adl_approver_id')
            ->whereHas('fh_tada_request_plan', function ($query) use ($businessId) {
                $query->where('trp_b_id', $businessId)
                    ->whereColumn('trp_id', 'adl_trp_id');
            })->get();

        $approvalData = ProcessApprover::where('pa_emp_id', $user->emp_id)
            ->whereHas('fh_approval_module', function ($query) use ($businessId) {
                $query->where('am_b_id', $businessId)
                    ->where('am_module_id', 199)
                    ->whereColumn('am_id', 'pa_am_id');
            })->get();

        return view('admin.setting.tada-settings.advancelog', compact('advance_log', 'tadaPlanList', 'approvalData', 'AdvancUpdate'));
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;

        $travelTypeFilter = request()->input('advance_travelTypeFilter');
        $planUniqueIdFilter = request()->input('advance_planUniqueIdFilter');
        $empNameFilter = request()->input('advance_empNameFilter');
        $statusFilter = request()->input('advance_sheetStatusFilter');

        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['trp_b_id', $user->emp_b_id]
                ],
                [
                    'method' => 'whereHas',
                    'args' => ['adl_id', '<>', null],
                    'relation' => 'fh_tada_advance_approval_log'
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
                        'trp_is_claimed'
                    ],
                    'relation' => [
                        'fh_employee:emp_code,emp_id,emp_full_name,emp_dg_id,emp_profile_photo',
                      'fh_employee.fh_designation:dg_id,dg_name',
                        'fh_policy_tada_travel_type.fh_travel_type:m_id,m_name',
                        'fh_tada_advance_approval_log.fh_approval_status:m_id,m_name,m_other',
                    ]
                ],
                [
                    'method' => 'sortBy',
                    'args' => ['trp_id', 'trp_emp_id', 'trp_b_id', 'updated_at', 'trp_pttt_id', 'trp_ptc_id', 'trp_name', 'trp_purpose', 'trp_advance_allowance', 'trp_request_status', 'trp_id']
                ],
            ];

            // Filter conditions
            if ($travelTypeFilter != '') {
                $dynamicConditions[] = [
                    'method' => 'whereHas',
                    'args' => ['m_id',  $travelTypeFilter],
                    'relation' => 'fh_policy_tada_travel_type.fh_travel_type'
                ];
            }

            if ($planUniqueIdFilter != '') {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['trp_id', $planUniqueIdFilter]];
            }

            if ($empNameFilter != '') {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['trp_emp_id', $empNameFilter]];
            }

              if ($statusFilter != '') {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['trp_request_status', $statusFilter]];
            }
            

                

            // Define search value, columns, and relationships
            $searchColumns = ['trp_unique_id', 'trp_advance_allowance'];
            $searchRelationships = [
                'fh_employee' => ['emp_full_name', 'emp_code'],
                'fh_policy_tada_travel_type.fh_travel_type' => ['m_name'],
            ];
            $list = (new DynamicModelDataTableHelper(eloquentModel: new TadaRequestPlan(), dynamicConditions: $dynamicConditions, searchColumns: $searchColumns, searchRelationships: $searchRelationships))->getServerSideDataTable();


            $rowData = array();
            $i = 1;
            foreach ($list as $key => $val) {
                $policyCategory = PolicyTadaCategory::where(['ptc_b_id' => $val->emp_b_id, 'ptc_d_id' => $val->emp_d_id, 'ptc_grade_id' => $val->emp_grade_id])->whereJsonContains('ptc_dg_id', $val->emp_dg_id)->first();
                $dataPolicy = $policyCategory->ptc_name ?? 'N/A';
                $row = array();

                $row[] = $i++;
                        
                $employeeName = isset($val->fh_employee) ? ($val->fh_employee->emp_code ? $val->fh_employee->emp_code . ' - ' : '') . $val->fh_employee->emp_full_name : '';
                $designation = isset($val->fh_employee->fh_designation) ? $val->fh_employee->fh_designation->dg_name : '';
                $row[] = '<div class="d-flex dynamic-width">
                            <div class="me-3 mt-0 mt-sm-1 d-block">
                                <h6 class="mb-1 fs-14">' . $employeeName . '</h6>
                                <p class="text-muted mb-0 fs-12">' . $designation . '</p>
                            </div>
                        </div>';
                
                $row[] = isset($val->fh_policy_tada_travel_type->fh_travel_type) ? $val->fh_policy_tada_travel_type->fh_travel_type->m_name : '';
                $row[] = $val->trp_unique_id;
                // $row[] = isset($val->fh_employee) ? $val->fh_employee->emp_full_name . ' (' . $val->fh_employee->emp_code . ')' : '';
                $row[] = $val->trp_name;
                $row[] = isset($val->fh_travel_purpose) ?  $val->fh_travel_purpose->tp_name : '';
                $row[] = $val->trp_advance_allowance;
                   // Status column with styling similar to approved claim
                $advanceLog = $val->fh_tada_advance_approval_log->first();
                if ($advanceLog && $advanceLog->fh_approval_status) {
                    $statusData = $advanceLog->fh_approval_status;
                    $jsonData = $statusData->m_other;
                    $decodedData = json_decode($jsonData, true);
                    
                    $color = $decodedData['color'] ?? '#6c757d';
                    $icon = $decodedData['web_icon'] ?? 'fa fa-question';
                    
                    // Get approval log data for popover
                    $logData = $advanceLog->fh_plan_approval_log ?? collect();
                    $formattedLog = '';
                    $comma = false;
                    
                    if ($logData->count() > 0) {
                        foreach ($logData as $log) {
                            if ($log->log_am_id == $advanceLog->adl_am_id) {
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
                    
                    $row[] = '<span class="badge" style="background-color:' . $color . '"><i class="' . $icon . '">&nbsp;</i>'
                        . $statusData->m_name
                        . '</span> &nbsp;'
                        . '<i class="feather feather-info text-primary fs-14" data-bs-container="body" data-bs-content="' . $safePopoverContent
                        . '" data-bs-placement="right" data-bs-popover-color="default" data-bs-toggle="popover" title="Approval Log"> </i>'
                        . '<p class="text-muted mb-0 fs-12">Approval ' . ($logData->count() ?? '0') . '</p>';
                } else {
                    $row[] = '<span class="badge" style="background-color:#6c757d"><i class="fa fa-clock">&nbsp;</i>Pending</span>';
                }
                $row[] = '<button
                    style="background: none; border: none; cursor: pointer; padding: 5px;"
                    data-adl_trp_id="' . $val->trp_id . '"
                    data-is_claimed="' . $val->trp_is_claimed . '"
                    onclick="openViewModel(this)"
                    onmouseover="this.children[0].style.color=\'#007bff\'"
                    onmouseout="this.children[0].style.color=\'black\'">
                    <i class="feather-eye fs-6" style="margin-top: 10px; color: black;"></i>
                </button>';


                $rowData[] = $row;
            }

            $output = array(
                "draw" => request()->input('draw'),
                "recordsTotal" => sizeof($list),
                "recordsFiltered" => (new DynamicModelDataTableHelper(eloquentModel: new TadaRequestPlan(), dynamicConditions: $dynamicConditions, searchColumns: $searchColumns, searchRelationships: $searchRelationships))->countFilteredServerSideDataTable(),
                "data" => $rowData,
            );

            return json_encode($output);
        }

        $columns = [
           ['name' => 'S. No.', 'width' => '5%'],
            ['name' => 'Employee Name', 'width' => '22%'],
            ['name' => 'Travel Type', 'width' => '8%'],
            ['name' => 'Plan Unique Id', 'width' => '10%'],
            ['name' => 'Plan Name', 'width' => '15%'],
            ['name' => 'Travel Purpose', 'width' => '12%'],
            ['name' => 'Travel Advance', 'width' => '10%'],
            ['name' => 'Status', 'width' => '10%'],
            ['name' => 'Action', 'width' => '8%'],
        ];
        return view('admin.ta-da-request.advance-list', compact('columns'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'reimburse_amount' => 'required|numeric|min:0.01',  // Ensure it's a positive number
        ]);

        // Process the reimbursement, e.g., update the database
        $data = AdvanceLog::where('adl_id', base64_decode($request->id))->update([
            'adl_reimburse_amount' => $request->reimburse_amount,
            'adl_approver_id' => Auth::user()->emp_id, // Set the approver ID to the current user
        ]);
        if ($data) {
            // Respond with success
            return response()->json(['success' => true]);
        } else {
            // Respond with failure
            return response()->json(['success' => false]);
        }
    }

    public function edit($id)
    {


        // Fetch the data related to the advance request using the decoded ID
        $advanceRequests = AdvanceLog::with('fh_approval_log2','fh_tada_request_plan')->where('adl_trp_id', base64_decode($id))->get();
        // Map through each advance request and add approval data
        $advanceRequests = $advanceRequests->map(function ($request) {
            // Fetch approval data
            $approvalData = ApprovalHelper::getApprovalOrRejectionData(
                $request->adl_id,
                $request->adl_request_status,
                $request->adl_am_id,
                NULL,
                199
            );

            // Fetch next approval details
            $nextApproval = ApprovalHelper::getNextApprovalDetails(199, $request->adl_id, $request?->fh_tada_request_plan?->trp_b_id);
            if (!(isset($nextApproval['approver_name']) && $nextApproval['approver_name'])) {
                $approvalMapping = ApprovalHelper::getApprovalMapping($request?->fh_tada_request_plan?->trp_b_id, $request?->fh_tada_request_plan?->trp_emp_id, $request->adl_module_id);

                if ($approvalMapping) {
                    $approvalArray = ApprovalHelper::getApprovalArray($approvalMapping);
                    $approvalLog = $request?->fh_approval_log2?->pluck('log_user_id')?->toArray() ?? [];
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
            // Add approval data to the request
            $request->approvalData = $approvalData;
            // Check if the user employee mapping can approve start
            $approval = ApprovalHelper::getApprovalData($request, $this->user, 'adl_');
            $masterApproveBtn = $approval['masterApproveBtn'];
            $canApprove = $approval['canApprove'];
            $request->masterApproveBtn = $masterApproveBtn;
            $request->canApprove = $canApprove;
            $request->route = $approvalData ? "admin.approval-handler" : ($canApprove ? "approve.advance" : '');

            // Check if the user employee mapping can approve end
            // Use null coalescing operator to avoid multiple isset checks
            $request->approval_status = $approvalData->fh_approver_status->m_id ?? null;
            $logData = $request->fh_plan_approval_log;
            $formattedLog = '';
            $comma = false;
            if ($logData && is_array($logData) && count($logData)) {
                foreach ($logData as $log) {
                    if ($log->log_am_id == $request->adl_am_id) {
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
                $logData2 = $request->fh_approval_log2;

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
             $popoverContent = $formattedLog === '' ?  ($request->adl_request_status == 171 ? 'Auto Approved' : 'Awaiting') : $formattedLog;
            $request->approval_status_check = (isset($request->fh_approval_status->m_name) ? ($request->fh_approval_status->m_name) : '');

            $request->approval_type = 1;
            $request->approval_action_type = $approvalData->pa_type ?? '';
            $request->approval_sequence = $approvalData->pa_sequence ?? '';
            $request->advance_id = md5($request->adl_id ?? '');
            $request->module_id =  md5($approvalData->pa_am_id ?? '') ?? '';
            $request->is_last_approval = $approvalData->pa_last ?? '';
            $request->approver_name = isset($nextApproval['approver_name']) && $nextApproval['approver_name'] ? $nextApproval['approver_name'] : $approverName ?? '---';
             $request->message = isset($request->fh_plan_approval_log[0])
                ? $request->fh_plan_approval_log[0]->log_description
                : (isset($request->fh_approval_log2)
                    ? $request->fh_approval_log2->last()?->log_description
                    : null);
                    $request->log_amount = isset($request->fh_plan_approval_log[0])
                    ? $request->fh_plan_approval_log[0]->log_other
                    : (isset($request->fh_approval_log2)
                        ? $request->fh_approval_log2->last()?->log_other
                        : null);
            $request->emp_tada_settlement_amt =  isset($request->fh_tada_request_plan->fh_employee->emp_tada_settlement_amt) ? $request->fh_tada_request_plan->fh_employee->emp_tada_settlement_amt : null;
            return $request;
        });

        // Return the optimized response as JSON
        return response()->json($advanceRequests);
    }

    public function approve(Request $request)
    {
        $user = Auth::user();
        $data = AdvanceLog::findOrFail($request->data['adl_id']);
        $mapData = EmployeeApprovalMapping::where(['eam_module_id' => $data->adl_module_id, 'eam_emp_id' => $data->fh_tada_request_plan->trp_emp_id])->first();
        $statusData = $mapData->approvalStatuses->where('eas_approvel_id',$user->emp_id)->pluck('eas_approvel_status')->first();
        $request->merge(['log_request_id' => $request->data['adl_id']]);
        $request->merge(['log_description' => $request->data['message']]);
        $request->merge(['log_status' => $request->data['approval_type'] == 1 ? $statusData : 170]);
        $request->merge(['reimburse_amount' =>  $request->data['reimburse_amount']]);

        $request->merge(['adl_trp_id' =>  $data->adl_trp_id]);

        // Process approval using the service
        $response = ApprovalHelper::processApproval($request, $data, $this->user, 'adl_');
        $response = ['status' => false, 'message' => '', 'result' => false];
        $response['result'] = true;
        $response['status'] = true;
        $response['message'] = 'Advance Approved Successfully';
        return response()->json($response);
        return response()->json($response, $response['status'] === 'success' ? 200 : 500);
    }
}
