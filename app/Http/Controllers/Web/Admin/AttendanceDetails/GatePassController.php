<?php

namespace App\Http\Controllers\Web\Admin\AttendanceDetails;

use App\Helpers\ApprovalHelper;
use App\Http\Controllers\Controller;
use App\Models\ApprovalLog;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeApprovalMapping;
use App\Models\GatePass;
use App\Models\Grade;
use App\Models\MasterTable;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Http\JsonResponse;

class GatePassController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;
        $branch = Branch::whereNull('br_b_id')->orWhere('br_b_id', $user->emp_b_id)->get();
        $departments = Department::whereNull('d_b_id')->orWhere('d_b_id', $user->emp_b_id)->get();
        $designations = Designation::whereNull('dg_b_id')->orWhere('dg_b_id', $user->emp_b_id)->get();
        $gate_pass_status = MasterTable::whereIn('m_id', [139, 140, 141, 156, 157, 170, 171, 174])->get();
        $branchFilter = request()->input('gate-passbranchFilter');
        $departmentFilter = request()->input('gate-passdepartmentFilter');
        $designationFilter = request()->input('gate-passdesignationFilter');
        $fromToDateFilter = request()->input('fromDate');
        $activeFilter = request()->input('gate-passactiveFilter');
        $statusFilter = request()->input('gate_pass_statusFilter');

        // AJAX-based data fetching logic
        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['gtp_b_id', $user->emp_b_id]
                ],
                [
                    'method' => 'select',
                    'args' => ['gtp_id', 'gtp_b_id', 'gtp_emp_id', 'gtp_date', 'gtp_in_time', 'gtp_out_time', 'gtp_reason', 'gtp_destination', 'gtp_status', 'gtp_stage_completed', 'gtp_module_id', 'gtp_am_id', 'gtp_next_approver', 'updated_at', 'created_at'],
                    'relation' => ['fh_employee:emp_id,emp_b_id,emp_full_name,emp_code,emp_br_id,emp_dg_id,emp_d_id', 'fh_approval_status:m_id,m_name,m_other']

                ],
                [
                    'method' => 'sortBy',
                    'args' => ['gtp_id', 'gtp_date']
                ]
            ];

            if ($branchFilter != '') {
                $dynamicConditions[] = [
                    'method' => 'whereRelation',
                    'parentMethod' => 'whereHas',
                    'childMethod' => 'where',
                    'args' => ['emp_br_id', $branchFilter],
                    'relation' => 'fh_employee'
                ];
            }


            if ($designationFilter != '') {
                $dynamicConditions[] = [
                    'method' => 'whereRelation',
                    'parentMethod' => 'whereHas',
                    'childMethod' => 'where',
                    'args' => ['emp_dg_id', $designationFilter],
                    'relation' => 'fh_employee'
                ];
            }

            if ($departmentFilter != '') {
                $dynamicConditions[] = [
                    'method' => 'whereRelation',
                    'parentMethod' => 'whereHas',
                    'childMethod' => 'where',
                    'args' => ['emp_d_id', $departmentFilter],
                    'relation' => 'fh_employee'
                ];
            }

            if ($activeFilter != '') {
                $dynamicConditions[] = [
                    'method' => 'whereRelation',
                    'parentMethod' => 'whereHas',
                    'childMethod' => 'where',
                    'args' => ['emp_status', $activeFilter],
                    'relation' => 'fh_employee'
                ];
            }


            if ($statusFilter != '') {
                $dynamicConditions[] = [
                    'method' => 'where',
                    'args' => ['gtp_status', $statusFilter],
                ];
            }

            if (!empty($fromToDateFilter)) {
                // Split the date range string
                $dates = explode(' - ', $fromToDateFilter);

                if (count($dates) == 2) {
                    // Parse start and end dates
                    $startDate = \Carbon\Carbon::createFromFormat('M d, Y', $dates[0])->startOfDay();
                    $endDate = \Carbon\Carbon::createFromFormat('M d, Y', $dates[1])->endOfDay();

                    $dynamicConditions[] = [
                        'method' => 'where',
                        'args' => ['gtp_date', '<=', $endDate->format('Y-m-d')]
                    ];

                    $dynamicConditions[] = [
                        'method' => 'where',
                        'args' => ['gtp_date', '>=', $startDate->format('Y-m-d')]
                    ];
                }
            }

            // Additional filter logic...
            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new GatePass(),
                dynamicConditions: $dynamicConditions,
                searchColumns: ['gtp_id', 'gtp_emp_id', 'gtp_date', 'gtp_in_time', 'gtp_out_time', 'gtp_reason', 'gtp_destination', 'gtp_status', 'created_at'],
                searchRelationships: [
                    'fh_employee' => ['emp_fname', 'emp_mname', 'emp_lname', 'emp_code'],
                    // 'fh_policy_shift_timing' => ['pst_name'],
                ]
            ))->getServerSideDataTable();

            // Employee Name 	Employee Id 	Date 	Out Time 	In Time 	Status 	Action
            $rowData = [];
            $i = 1;
            foreach ($list as $key => $val) {
                $row = [];
                $row[] = $i++;
                $row[] = optional($val->fh_employee)->emp_full_name;
                $row[] = optional($val->fh_employee)->emp_code;
                $row[] = Carbon::parse($val->created_at)->format('d-M-Y');
                $row[] = Carbon::parse($val->gtp_date)->format('d-M-Y');
                $row[] = !empty($val->gtp_out_time) ? Carbon::parse($val->gtp_out_time)->format('g:i A') : '--';
                $row[] = !empty($val->gtp_in_time) ? Carbon::parse($val->gtp_in_time)->format('g:i A') : '--';
                $nextApproval = ApprovalHelper::getNextApprovalDetails(339, $val->gtp_id, $val->gtp_b_id);
                $approval = ApprovalHelper::checkApproval($user->emp_b_id, $val->gtp_module_id, $val->gtp_id, optional($val->fh_employee)->emp_d_id, $val->gtp_emp_id);
                $logData = $val->fh_plan_approval_log;
                // Check if the log data is not empty
                $formattedLog = '';
                $comma = false;

                if ($logData && count($logData)) {
                    foreach ($logData as $log) {
                        if ($log->log_am_id == $val->gtp_am_id) {
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
                    $formattedLog = ''; // Show a message if there are no logs
                    $logData2 = $val->fh_approval_log2;
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
                }
                // Use a default message if $formattedLog is empty
                $popoverContent = $formattedLog === '' ?  ($val->gtp_status == 171 ? 'Auto Approved' : 'Awaiting') : $formattedLog;

                // Ensure the content is properly escaped for use in data attributes
                $safePopoverContent = htmlspecialchars($popoverContent, ENT_QUOTES, 'UTF-8');
                $jsonData = optional($val->fh_approval_status)->m_other;
                // Decode the JSON to an associative array
                $decodedData = json_decode($jsonData, true); // true for associative array
                $is_auto_approval = false;
                // Now access the color value
                $color = $decodedData['color'] ?? '';
                $icon = $decodedData['web_icon'] ?? '';
                $row[] = '<span class="badge" style="background-color:' . $color . '"><i class="' . $icon . '">&nbsp;</i>' . (isset($val->fh_approval_status->m_name) ? ($val->fh_approval_status->m_name) : '') . '</span> &nbsp;<i class="feather feather-info text-primary fs-14"
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
                    $approvalMapping = ApprovalHelper::getApprovalMapping($user->emp_b_id, $val->gtp_emp_id, $val->gtp_module_id);

                    if ($approvalMapping) {
                        $approvalArray = ApprovalHelper::getApprovalArray($approvalMapping);
                        $approvalLog = $val?->fh_approval_log2?->pluck('log_user_id')?->toArray() ?? [];
                        $diff = array_values(array_diff($approvalArray, $approvalLog));

                        if ($diff) {
                            $approverName = Employee::where('emp_id', $diff[0])->pluck('emp_full_name')->first() ??  '---';
                        } else {
                            $approverName = '---';
                        }
                    } else {
                        $approverName = '---';
                    }
                }

                $row[] = '<span class="text-center">' . (isset($nextApproval['approver_name']) && $nextApproval['approver_name'] ? $nextApproval['approver_name'] : $approverName ?? '---') . '</span>';

                $row[] =   '<a class="" href="' . url('admin/requests/gate-pass/' . md5($val->gtp_id)) . '">  <i class="feather-eye fs-6" style=" margin-top: 10;"></i></a>';

                $rowData[] = $row;
            }

            $output = [
                "draw" => intval($request->input('draw')),
                "recordsTotal" => sizeof($list),
                "recordsFiltered" => (new DynamicModelDataTableHelper(
                    eloquentModel: new GatePass(),
                    dynamicConditions: $dynamicConditions
                ))->countFilteredServerSideDataTable(),
                "data" => $rowData,
            ];

            return json_encode($output);
        }

        // View
        $columns = [
            'S. No.',
            'Emp. Name',
            'Emp. Code',
            'Applied Date',
            'Date',
            'Out Time',
            'In Time',
            'Approval Status',
            'Submitted To',
            'Action',
        ];

        return view('admin.setting.attendance-details.gatepass-requests', compact(
            'departments',
            'designations',
            'branch',
            'gate_pass_status',
            'columns'
        ));
    }

    public function show($id)
    {
        $data = GatePass::with(
            'fh_employee:emp_id,emp_fname,emp_mname,emp_lname,emp_dg_id,emp_d_id,emp_phone,emp_code,emp_profile_photo,emp_full_name,emp_email,emp_status,emp_br_id',
            'fh_employee.fh_department:d_id,d_name',
            'fh_employee.fh_designation:dg_id,dg_name',
            'fh_employee.fh_branch:br_id,br_name',
            'fh_approval_status:m_id,m_name,m_other',
            'fh_module:m_id,m_name,m_other',
            'fh_approval_log2',
        )->where(DB::raw('md5(gtp_id)'), $id)->first();

        // Get approval data
        $approvalData = ApprovalHelper::getApprovalOrRejectionData($data->gtp_id, $data->gtp_status, $data->gtp_am_id, NULL, 339);

        $gatepassApprovalLogs = ApprovalLog::with(
            'fh_employee:emp_id,emp_full_name,emp_code,emp_dg_id',
            'fh_employee.fh_designation:dg_id,dg_name',
            'fh_status:m_id,m_name,m_other'
        )
        ->where('log_request_id', $data->gtp_id)
        ->where('log_module_id', 339)
        ->orWhere('log_am_id', 339)
        ->orderBy('created_at')
        ->get();

        // Check if the user employee mapping can approve start
        $data->approvalData = $approvalData;
        $approval = ApprovalHelper::getApprovalData($data, $this->user, 'gtp_');
        $masterApproveBtn = $approval['masterApproveBtn'];
        $canApprove = $approval['canApprove'];
        // Check if the user employee mapping can approve end
        return view('admin.setting.attendance-details.gate-pass-requests-show', compact('data', 'approvalData', 'canApprove', 'masterApproveBtn', 'gatepassApprovalLogs'));
    }

    public function approve(Request $request)
    {
        $data = GatePass::findOrFail($request->log_request_id);

        // Process approval using the service
        $response = ApprovalHelper::processApproval($request, $data, $this->user, 'gtp_');

        return response()->json($response, $response['status'] === 'success' ? 200 : 500);
    }
}
