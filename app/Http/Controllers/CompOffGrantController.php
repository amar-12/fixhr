<?php

namespace App\Http\Controllers;

use App\Helpers\ApprovalHelper;
use App\Models\AttendanceSummary;
use App\Models\Branch;
use App\Models\CompOff;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use Carbon\Carbon;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CompOffGrantController extends Controller
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
        $user = Auth::user();
        $businessId = $user->emp_b_id;
        $branch = Branch::whereNull('br_b_id')->orWhere('br_b_id', $user->emp_b_id)->get();
        $departments = Department::whereNull('d_b_id')->orWhere('d_b_id', $user->emp_b_id)->get();
        $designations = Designation::whereNull('dg_b_id')->orWhere('dg_b_id', $user->emp_b_id)->get();

        $branchFilter = request()->input('leave_branchFilter');
        $departmentFilter = request()->input('leave_departmentFilter');
        $designationFilter = request()->input('leave_designationFilter');
        $fromToDateFilter = request()->input('fromDate');
        $activeFilter = request()->input('leave_activeFilter');
        $data = CompOff::where('co_b_id', $businessId)->get();
        $approvalBtnData = [];
        $canApprove = true;
        $masterApproveBtn = null;
        $moduleId = [];

        // AJAX-based data fetching logic
        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['co_b_id', $user->emp_b_id]
                ],
                [
                    'method' => 'select',
                    'args' => ['co_id', 'co_b_id', 'co_emp_id', 'co_record_id', 'co_request_date', 'co_credit_date', 'co_validity', 'co_is_expiry', 'co_alloted', 'co_carry_forward', 'co_am_id', 'co_approved_by', 'co_status', 'co_next_approver', 'co_stage_completed', 'co_module_id', 'updated_at', 'created_at'],
                    'relation' => ['fh_employee:emp_id,emp_b_id,emp_full_name,emp_code,emp_br_id,emp_dg_id,emp_d_id,emp_status', 'fh_approval_status:m_id,m_name,m_other']
                ],
                [
                    'method' => 'sortBy',
                    'args' => ['co_id', 'co_emp_id', 'co_record_id', 'updated_at', 'co_validity', 'co_alloted', 'co_status', 'co_is_expiry', 'co_carry_forward', 'co_request_date', 'co_credit_date', 'co_stage_completed', 'created_at', 'co_next_approver', 'co_am_id'],
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

            if (!empty($fromToDateFilter)) {
                // Split the date range string
                $dates = explode(' - ', $fromToDateFilter);

                if (count($dates) == 2) {
                    // Parse start and end dates
                    $co_request_date = \Carbon\Carbon::createFromFormat('M d, Y', $dates[0])->startOfDay();
                    $endDate = \Carbon\Carbon::createFromFormat('M d, Y', $dates[1])->endOfDay();

                    $dynamicConditions[] = [
                        'method' => 'where',
                        'args' => ['co_request_date', '<=', $endDate->format('Y-m-d')]
                    ];

                    $dynamicConditions[] = [
                        'method' => 'where',
                        'args' => ['co_request_date', '>=', $co_request_date->format('Y-m-d')]
                    ];
                }
            }

            // Additional filter logic...
            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new CompOff(),
                dynamicConditions: $dynamicConditions,
                searchColumns: ['co_id', 'co_b_id', 'co_emp_id', 'co_record_id', 'co_request_date', 'co_credit_date', 'co_validity', 'co_is_expiry', 'co_alloted', 'co_carry_forward', 'co_am_id', 'co_approved_by', 'co_status', 'co_next_approver', 'co_stage_completed', 'updated_at', 'created_at'],
                searchRelationships: [
                    'fh_employee' => ['emp_fname', 'emp_mname', 'emp_lname', 'emp_code', 'emp_status'],
                ]
            ))->getServerSideDataTable();

            // Employee Name    Employee Id     Applied Date    Leave Category  Leave Type  From    To  Days    Status  Action
            $rowData = [];
            $i = 1;

            foreach ($list as $key => $val) {
                $approvalData = ApprovalHelper::getApprovalOrRejectionData($val->co_id, $val->co_status, $val->co_am_id, NULL, 562);
                if ($approvalData) {
                    $approvalBtnData['approval_status'] = $approvalData->fh_approver_status->m_id;
                    // $approvalBtnData['approval_name'] = $approvalData->fh_approver_status->m_name;
                    $approvalBtnData['approval_action_type'] = $approvalData->pa_type;
                    $approvalBtnData['approval_sequence'] = $approvalData->pa_sequence;
                    $approvalBtnData['module_id'] = $approvalData->pa_am_id;
                    $approvalBtnData['is_last_approval'] = $approvalData->pa_last;
                    $approvalBtnData['emp_d_id'] = optional($val->fh_employee)->emp_d_id;
                } else {
                    // Check if the user employee mapping can approve start
                    $val->approvalData = $approvalData;
                    $approval = ApprovalHelper::getApprovalData($val, $this->user, 'co_');
                    $masterApproveBtn = $approval['masterApproveBtn'];
                    $canApprove = $approval['canApprove'];
                    $moduleId['module_id'] = $val->co_module_id;
                    // dd($approval, $canApprove, $masterApproveBtn);
                }
                // dd(is_null($canApprove), isset($canApprove), $canApprove);

                $data = $val->where('cop_id', $val->cop_id ?? $val->co_id)->orWhere('co_id', $val->cop_id ?? $val->co_id)->get();
                $status = optional($val->fh_employee)->emp_status;
                $nextApproval = ApprovalHelper::getNextApprovalDetails(562, $val->co_id, $val->co_b_id);
                $approval = ApprovalHelper::checkApproval($user->emp_b_id, $val->co_module_id, $val->co_id, optional($val->fh_employee)->emp_d_id, $val->co_emp_id);
                $row = [];
                $row[] = $i++;
                $row[] = optional($val->fh_employee)->emp_full_name;
                $row[] = optional($val->fh_employee)->emp_code;
                if ($status == 71) {
                    $row[] = '<span class="badge bg-success"></i> Active</span>';
                } elseif ($status == 72) {
                    $row[] = '<span class="badge bg-danger"></> Inactive</span>';
                }
                $row[] = Carbon::parse($val->co_request_date)->format('d-M-Y');
                $row[] = !empty($val->co_credit_date) ? Carbon::parse($val->co_credit_date)->format('d-M-Y') : '--';

                $logData = $val->fh_plan_approval_log;
                $formattedLog = '';
                $comma = false;
                if ($logData && count($logData)) {
                    foreach ($logData as $log) {
                        if ($log->log_am_id == $val->co_am_id) {
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
                    // $formattedLog = '<p>No logs available.</p>'; // Show a message if there are no logs
                }

                // Use a default message if $formattedLog is empty
                $popoverContent = $formattedLog === '' ?  ($val->co_status == 171 ? 'Auto Approved' : 'Awaiting') : $formattedLog;

                // Ensure the content is properly escaped for use in data attributes
                $safePopoverContent = htmlspecialchars($popoverContent, ENT_QUOTES, 'UTF-8');
                $jsonData = optional($val->fh_approval_status)->m_other;
                // Decode the JSON to an associative array
                $is_auto_approval = false;
                $decodedData = json_decode($jsonData, true); // true for associative array
                // Now access the color value
                $color = $decodedData['color'] ?? '';
                $icon = $decodedData['web_icon'] ?? '';
                $row[] = '<span class="badge" style="background-color:' . $color . '"><i class="' . $icon . '">&nbsp;</i>' . (isset($val->fh_approval_status->m_name) ? ($val->fh_approval_status->m_name) : '') . '</span> &nbsp;<i class="feather feather-info text-primary fs-14"
                    data-bs-container="body"
                    data-bs-content="' . $safePopoverContent . '"
                            data-bs-placement="bottom"
                            data-bs-popover-color="default"
                            data-bs-toggle="popover"
                            title="' . $safePopoverContent . '">
                        </i>'
                    // Show approval count only for statuses other than 140
                    . (!$is_auto_approval ? '<p class="text-muted mb-0 fs-12">Approval ' . ($approval['approvallog_count'] ?? ' ') . ' (' . ($approval['approvalcount'] ?? ' ') . ')</p>' : '');

                if (!(isset($nextApproval['approver_name']) && $nextApproval['approver_name'])) {
                    $approvalMapping = ApprovalHelper::getApprovalMapping($user->emp_b_id, $val->co_emp_id, $val->co_module_id);

                    if ($approvalMapping) {
                        $approvalArray = ApprovalHelper::getApprovalArray($approvalMapping);
                        $approvalLog = $val?->fh_approval_log2?->pluck('log_user_id')?->toArray() ?? [];
                        $diff = array_values(array_diff($approvalArray, $approvalLog));

                        if ($diff) {
                            $approverName = Employee::where('emp_id', $diff[0])->pluck('emp_full_name')->first() ?? '---';
                        } else {
                            $approverName = '---';
                        }
                    } else {
                        $approverName = '---';
                    }
                }

                $row[] = '<span class="text-center">' . (isset($nextApproval['approver_name']) && $nextApproval['approver_name'] ? $nextApproval['approver_name'] : $approverName ?? '---') . '</span>';
                /*$action = '<div class="d-flex">
                    <a class="btn btn-sm btn-info" href="' . url('admin/requests/leave/' . md5($val->co_id)) . '">
                        <i class="feather feather-eye"></i>
                    </a>';*/

                $emp_att_sum_id  = $val->co_emp_id;
                $att_summary = AttendanceSummary::where('as_emp_id', $emp_att_sum_id)->first();
                $sal_processed = optional($att_summary)->as_is_sal_processed;

                // Build action dropdown
                $action = '<div class="d-flex">
                    <div class="mt-3 mt-sm-0">
                        <div class="d-flex">
                            <a class="btn btn-outline-light text-muted" href="javascript:void(0);" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fe fe-more-vertical" style="font-size:18px;"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" role="menu">
                                <li>
                                    <a href="' . url('admin/requests/comp-off-approval/' . md5($val->co_id)) . '">
                                        <i class="feather feather-eye me-2"></i>View
                                    </a>
                                </li>
                                </li>';

                                if ($sal_processed == 121 && $val->co_status == 140) {
                                    $action .= '
                                    <li>
                                        <a href="javascript:void(0);"
                                           class="remove-btn"
                                           data-id="' . $val->co_id . '">
                                            <i class="feather feather-trash-2 me-2"></i>Delete
                                        </a>
                                    </li>';
                                }
                                if ($val->co_status != 140 && $val->co_status != 170 && $val->co_status != 171) {
                                    $action .= '
                                    <li>
                                        <a href="javascript:void(0);"
                                             class="revert-btn"
                                           data-id="' . $val->co_id . '">
                                            <i class="feather feather-rotate-ccw me-2"></i>Revert Request
                                        </a>
                                    </li>';
                                }
                                $action .= '
                            </ul>
                        </div>
                    </div>
                </div>';


                $row[] = $action;


                if (($canApprove && $val->co_stage_completed == 0) || (!empty($approvalData) && $val->co_stage_completed == 0)) {
                    $row[] = '<div class="d-flex">
                    <label class="custom-control custom-checkbox-md p-0 ms-2">
                  <input type="checkbox" class="custom-control-input-success select-checkbox testClass"
                            name="leave_ids[]" value="' . md5($val->co_id) . '"
                            onclick="selectCheckbox(this)">

                        <span class="custom-control-label-md success"></span>
                    </label>
                </div>';
                }
                $row[] = '';
                $rowData[] = $row;
            }

            $output = [
                "draw" => intval($request->input('draw')),
                "recordsTotal" => sizeof($list),
                "recordsFiltered" => (new DynamicModelDataTableHelper(
                    eloquentModel: new CompOff(),
                    dynamicConditions: $dynamicConditions
                ))->countFilteredServerSideDataTable(),
                "data" => $rowData,
                "apHiBtn" => $approvalBtnData,
                "apEmpBtn" => $masterApproveBtn,
                "canApprove" => $canApprove,
                "moduleId" => $moduleId
            ];

            return json_encode($output);
        }


        // View
        $columns = [
            'S. No.',
            'Emp. Name',
            'Emp. Code',
            'Status',
            'Request Date',
            'Credit Date',
            'Approval Status',
            'Submitted To',
            'Action',
            'Select All',
        ];

        return view('admin.setting.attendance-details.comp-off-approval', compact(
            'departments',
            'designations',
            'branch',
            'columns',
            'approvalBtnData'
        ));
    }

    /**
     * Display the specified resource.
     */
    public function showRequest(string $id)
    {
        $data = CompOff::with(
            'fh_employee:emp_id,emp_fname,emp_mname,emp_lname,emp_dg_id,emp_d_id,emp_phone,emp_code,emp_profile_photo,emp_full_name,emp_email,emp_status,emp_br_id',
            'fh_employee.fh_department:d_id,d_name',
            'fh_employee.fh_designation:dg_id,dg_name',
            'fh_employee.fh_branch:br_id,br_name',
            'fh_approval_status:m_id,m_name,m_other',
        )->where((DB::raw('md5(co_id)')), $id)->first();

        $requestDate = $data->co_request_date;
        $approvalData = ApprovalHelper::getApprovalOrRejectionData($data->co_id, $data->co_status, $data->co_am_id, NULL, 562);
        $data->approvalData = $approvalData;
        $approval = ApprovalHelper::getApprovalData($data, $this->user, 'co_');
        $masterApproveBtn = $approval['masterApproveBtn'];
        $canApprove = $approval['canApprove'];

        return view('admin.setting.attendance-details.compoff-request-show', compact('data', 'approvalData', 'canApprove', 'masterApproveBtn', 'requestDate'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $data = CompOff::findOrFail($request->log_request_id);

        // Process approval using the service
        $response = ApprovalHelper::processApproval($request, $data, $this->user, 'co_');

        return response()->json($response, $response['status'] === 'success' ? 200 : 500);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = CompOff::where('co_b_id', $this->user->emp_b_id)->where('co_id', $id)->firstOrFail();

        if (in_array($data->co_status, [140]) && $data->fh_approval_log2->count() == 0) {
            $data->delete();

            return response()->json(['status' => 'success', 'message' => 'Comp Off request deleted successfully.'], 200);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Comp Off request cannot be deleted.'], 400);
        }
    }

    public function revertRequest(Request $request)
    {
        $data = CompOff::with('fh_approval_log2')->with('fh_plan_approval_log')->where('co_b_id', $this->user->emp_b_id)->where('co_id', $request->id)->firstOrFail();
        dd($data->toArray());

        if (!in_array($data->co_status, [170, 171, 140]) && $data->fh_approval_log2->count() > 0) {
            // Revert the status to pending (140) and clear approval logs
            $data->co_status = 140;
            $data->co_next_approver = 1;
            $data->co_stage_completed = 0;
            $data->save();

            // Delete related approval logs
            DB::table('fh_approval_log')->where('log_request_id', $data->co_id)->orWhere("log_module_id", 562)->delete();

            return response()->json(['status' => 'success', 'message' => 'Comp Off request reverted successfully.'], 200);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Comp Off request cannot be reverted.'], 400);
        }
    }
}
