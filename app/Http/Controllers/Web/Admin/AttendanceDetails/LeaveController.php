<?php

namespace App\Http\Controllers\Web\Admin\AttendanceDetails;

use App\Helpers\ApprovalHelper;
use App\Http\Controllers\Controller;
use App\Imports\LeaveRequestImport;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use App\Models\ApprovalLog;
use App\Models\AttendanceSummary;
use App\Models\MasterTable;
use Carbon\Carbon;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ErrorExport;
use App\Models\CompOffBalance;
use Illuminate\Contracts\Encryption\DecryptException;

class LeaveController extends Controller
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
        $leave_status = MasterTable::whereIn('m_id', [139, 140, 141, 156, 157, 170, 171, 174])->get();
        $branchFilter = request()->input('leave_branchFilter');
        $departmentFilter = request()->input('leave_departmentFilter');
        $designationFilter = request()->input('leave_designationFilter');
        $fromToDateFilter = request()->input('fromDate');
        $activeFilter = request()->input('leave_activeFilter');
        $statusFilter = request()->input('leave_statusFilter');
        $leaveDateFilter = request()->input('leave_dateFilter');
        $data = LeaveRequest::where('lvr_b_id', $businessId)->get();
        $approvalBtnData = [];
        $canApprove = true;
        $masterApproveBtn = null;
        $moduleId = [];

        // AJAX-based data fetching logic
        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['lvr_b_id', $user->emp_b_id]
                ],
                [
                    'method' => 'whereNull',
                    'args' => ['lvr_p_id']
                ],
                [
                    'method' => 'select',
                    'args' => ['lvr_id', 'lvr_b_id', 'lvr_emp_id', 'lvr_pl_id', 'lvr_start_date', 'lvr_end_date', 'lvr_cat_type_id', 'lvr_reason', 'lvr_total_leave_days', 'lvr_day_segment_id', 'lvr_documents', 'lvr_approved_by', 'lvr_am_id', 'lvr_status', 'lvr_module_id', 'lvr_status', 'lvr_stage_completed', 'lvr_leave_day_type_id', 'lvr_next_approver', 'updated_at', 'created_at'],
                    'relation' => ['fh_employee:emp_id,emp_b_id,emp_full_name,emp_code,emp_br_id,emp_dg_id,emp_d_id,emp_status', 'fh_approval_status:m_id,m_name,m_other', 'fh_leave_cat_type:m_id,m_name,m_other', 'fh_leave_day_segment:m_id,m_name,m_other', 'fh_leave_day_type:m_id,m_name,m_other']
                ],
                [
                    'method' => 'sortBy',
                    'args' => ['lvr_id', 'lvr_emp_id', 'lvr_emp_id', 'updated_at', 'lvr_cat_type_id', 'lvr_leave_day_type_id', 'lvr_start_date', 'lvr_end_date', 'lvr_total_leave_days', 'lvr_status', 'lvr_id', 'lvr_id'],
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
                    'args' => ['lvr_status', $statusFilter],
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
                        'args' => ['lvr_start_date', '<=', $endDate->format('Y-m-d')]
                    ];

                    $dynamicConditions[] = [
                        'method' => 'where',
                        'args' => ['lvr_end_date', '>=', $startDate->format('Y-m-d')]
                    ];
                }
            }

            if (!empty($leaveDateFilter)) {
                // Split the date range string
                $dates = explode(' - ', $leaveDateFilter);
                if (count($dates) == 2) {
                    // Parse start and end dates
                    $startDate = Carbon::createFromFormat('M d, Y', $dates[0])->startOfDay();
                    $endDate = Carbon::createFromFormat('M d, Y', $dates[1])->endOfDay();
                }
            }

            // Additional filter logic...
            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new LeaveRequest(),
                dynamicConditions: $dynamicConditions,
                searchColumns: ['lvr_id', 'lvr_b_id', 'lvr_emp_id', 'lvr_pl_id', 'lvr_start_date', 'lvr_end_date', 'lvr_cat_type_id', 'lvr_reason', 'lvr_total_leave_days', 'lvr_day_segment_id', 'lvr_documents', 'lvr_approved_by', 'lvr_am_id', 'lvr_status', 'lvr_module_id', 'lvr_status', 'lvr_stage_completed', 'updated_at', 'created_at'],
                searchRelationships: [
                    'fh_employee' => ['emp_fname', 'emp_mname', 'emp_lname', 'emp_code', 'emp_status'],
                ]
            ))->getServerSideDataTable();
            $rowData = [];
            $i = 1;

            foreach ($list as $key => $val) {
                $approvalData = ApprovalHelper::getApprovalOrRejectionData($val->lvr_id, $val->lvr_status, $val->lvr_am_id, NULL, 250);
                if ($approvalData) {
                    $approvalBtnData['approval_status'] = $approvalData->fh_approver_status->m_id;
                    // $approvalBtnData['approval_name'] = $approvalData->fh_approver_status->m_name;
                    $approvalBtnData['approval_action_type'] = $approvalData->pa_type;
                    $approvalBtnData['approval_sequence'] = $approvalData->pa_sequence;
                    $approvalBtnData['module_id'] = $approvalData->pa_am_id;
                    $approvalBtnData['is_last_approval'] = $approvalData->pa_last;
                    $approvalBtnData['emp_d_id'] = optional($val->fh_employee)->emp_d_id;
                } else {
                    $val->approvalData = $approvalData;
                    $approval = ApprovalHelper::getApprovalData($val, $this->user, 'lvr_');
                    $masterApproveBtn = $approval['masterApproveBtn'];
                    $canApprove = $approval['canApprove'];
                    $moduleId['module_id'] = $val->lvr_module_id;
                }

                $data = $val->where('lvr_p_id', $val->lvr_p_id ?? $val->lvr_id)->orWhere('lvr_id', $val->lvr_p_id ?? $val->lvr_id)->get();
                $leaveCategoryBreakdown = $data->groupBy('lvr_cat_type_id')->map(function ($group) {
                    return optional($group->first()->fh_leave_cat_type)->m_name; // Fetch category name only
                })->values();
                $total_leave = $data->sum('lvr_total_leave_days');
                $startDate = $data->min('lvr_start_date');
                $endDate = $data->max('lvr_end_date');
                $status = optional($val->fh_employee)->emp_status;
                $nextApproval = ApprovalHelper::getNextApprovalDetails(250, $val->lvr_id, $val->lvr_b_id);
                $approval = ApprovalHelper::checkApproval($user->emp_b_id, $val->lvr_module_id, $val->lvr_id, optional($val->fh_employee)->emp_d_id, $val->lvr_emp_id);
                $row = [];
                $row[] = $i++;
                $row[] = optional($val->fh_employee)->emp_full_name;
                $row[] = optional($val->fh_employee)->emp_code;
                if ($status == 71) {
                    $row[] = '<span class="badge bg-success"></i> Active</span>';
                } elseif ($status == 72) {
                    $row[] = '<span class="badge bg-danger"></> Inactive</span>';
                }
                $row[] = Carbon::parse($val->created_at)->format('d-M-Y');
                $row[] = $leaveCategoryBreakdown->isNotEmpty() ? $leaveCategoryBreakdown->implode(', ') : ($val->lvr_cat_type_id
                    ? optional($val->fh_leave_cat_type)->m_name : '--');
                $row[] = !empty($val->lvr_leave_day_type_id) ? $val->fh_leave_day_type->m_name : '--';
                $row[] = !empty($startDate) ? Carbon::parse($startDate)->format('d-M-Y') : '--';
                $row[] = !empty($endDate) ? Carbon::parse($endDate)->format('d-M-Y') : '--';
                $row[] = $total_leave > 0 ? $total_leave : ($val->lvr_total_leave_days ?? '--');

                $logData = $val->fh_plan_approval_log;
                $formattedLog = '';
                $comma = false;
                if ($logData && count($logData)) {
                    foreach ($logData as $log) {
                        if ($log->log_am_id == $val->lvr_am_id) {
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
                }

                // Use a default message if $formattedLog is empty
                $popoverContent = $formattedLog === '' ?  ($val->lvr_status == 171 ? 'Auto Approved' : 'Awaiting') : $formattedLog;

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
                    $approvalMapping = ApprovalHelper::getApprovalMapping($user->emp_b_id, $val->lvr_emp_id, $val->lvr_module_id);

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

                $emp_att_sum_id  = $val->lvr_emp_id;
                $att_summary = AttendanceSummary::where('as_emp_id', $emp_att_sum_id)->where('as_year_month', Carbon::parse($val->lvr_start_date)->format('Y-m'))->first();
                $sal_processed = optional($att_summary)->as_is_sal_processed ?? 0;

                // Build action dropdown
                $action = '<div class="d-flex">
                    <div class="mt-3 mt-sm-0">
                        <div class="d-flex">
                            <a class="btn btn-outline-light text-muted" href="javascript:void(0);" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fe fe-more-vertical" style="font-size:18px;"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" role="menu">
                                <li>
                                    <a href="' . url('admin/requests/leave/' . md5($val->lvr_id)) . '">
                                        <i class="feather feather-eye me-2"></i>View
                                    </a>
                                </li>';

                if ($user->emp_role_id == 1 && $val->lvr_status != 170 && $val->lvr_stage_completed == 1) {
                    $action .= '
                                <li>
                                    <button class="dropdown-item revert-button"
                                            data-id="' . Crypt::encrypt($val->lvr_id) . '"
                                            data-url="' . route('leave-request.revert', Crypt::encrypt($val->lvr_id)) . '">
                                        <i class="fa fa-undo"></i> Revert
                                    </button>
                                </li>';
                }

                if ($sal_processed != 120 && $val->lvr_status == 140) {
                    $action .= '
                                <li>
                                    <a href="javascript:void(0);"
                                       class="remove-btn"
                                       data-id="' . $val->lvr_id . '">
                                        <i class="feather feather-trash-2 me-2"></i>Delete
                                    </a>
                                </li>';
                }

                $action .= '
                            </ul>
                        </div>
                    </div>
                </div>';

                $row[] = $action;

                if (($canApprove && $val->lvr_stage_completed == 0) || (!empty($approvalData) && $val->lvr_stage_completed == 0)) {
                    $row[] = '<div class="d-flex">
                    <label class="custom-control custom-checkbox-md p-0 ms-2">
                  <input type="checkbox" class="custom-control-input-success select-checkbox testClass"
                            name="leave_ids[]" value="' . md5($val->lvr_id) . '"
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
                    eloquentModel: new LeaveRequest(),
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

        if (!$request->ajax()) {
            // Fetch relevant leave requests based on your business logic
            $relevantLeaveRequests = LeaveRequest::where('lvr_b_id', $businessId)
                ->whereNull('lvr_p_id')
                ->where('lvr_stage_completed', 0)
                ->get();
            
            foreach ($relevantLeaveRequests as $val) {
                $approval = ApprovalHelper::getApprovalData($val, $user, 'lvr_');
                if (!empty($approval['masterApproveBtn'])) {
                    $masterApproveBtn = $approval['masterApproveBtn'];
                    $canApprove = $approval['canApprove'];
                }
            }
        }

        // View
        $columns = [
            'S. No.',
            'Emp. Name',
            'Emp. Code',
            'Status',
            'Applied Date',
            'Leave Category',
            'Leave Type',
            'From Date',
            'To Date',
            'Days',
            'Approval Status',
            'Submitted To',
            'Action',
            'Select All',
        ];

        return view('admin.setting.attendance-details.leave-requests', compact(
            'departments',
            'designations',
            'branch',
            'columns',
            'approvalBtnData',
            'leave_status',
            'masterApproveBtn',
            'canApprove'
        ));
    }

    public function show($id)
    {
        $data = LeaveRequest::with(
            'fh_employee:emp_id,emp_fname,emp_mname,emp_lname,emp_dg_id,emp_d_id,emp_phone,emp_code,emp_profile_photo,emp_full_name,emp_email,emp_status,emp_br_id',
            'fh_employee.fh_department:d_id,d_name',
            'fh_employee.fh_designation:dg_id,dg_name',
            'fh_employee.fh_branch:br_id,br_name',
            'fh_approval_status:m_id,m_name,m_other',
            'fh_leave_cat_type:m_id,m_name,m_other',
            'fh_leave_day_segment:m_id,m_name,m_other',
            'fh_leave_day_type:m_id,m_name,m_other'
        )->where((DB::raw('md5(lvr_id)')), $id)->first();

        $values = $data->where('lvr_p_id', $val->lvr_p_id ?? $data->lvr_id)->orWhere('lvr_id', $data->lvr_p_id ?? $data->lvr_id)->get();
        $leaveCategoryBreakdown = $values->groupBy('lvr_cat_type_id')->map(function ($group) {
            return [
                'category' => optional($group->first()->fh_leave_cat_type)->m_name,
                'count' => $group->sum('lvr_total_leave_days'),
            ];
        })->values();

        $leaveApprovalLogs = ApprovalLog::with(
            'fh_employee:emp_id,emp_full_name,emp_code,emp_dg_id',
            'fh_employee.fh_designation:dg_id,dg_name',
            'fh_status:m_id,m_name,m_other'
        )
        ->where('log_request_id', $data->lvr_id)
        ->where('log_module_id', 250)
        ->orWhere('log_am_id', 250)
        ->orderBy('created_at')
        ->get();

        $startDate = $values->min('lvr_start_date');
        $endDate = $values->max('lvr_end_date');
        $approvalData = ApprovalHelper::getApprovalOrRejectionData($data->lvr_id, $data->lvr_status, $data->lvr_am_id, NULL, 250);
        $data->approvalData = $approvalData;
        $approval = ApprovalHelper::getApprovalData($data, $this->user, 'lvr_');
        $masterApproveBtn = $approval['masterApproveBtn'];
        $canApprove = $approval['canApprove'];

        // Prepare Leave Category dynamically
        $categoryWiseLeave = $leaveCategoryBreakdown->map(function ($category) {
            return [
                'name' => $category['category'],
                'count' => $category['count'] . ' day'
            ];
        });

        return view('admin.setting.attendance-details.leave-requests-show', compact('data', 'approvalData', 'canApprove', 'masterApproveBtn', 'startDate', 'endDate', 'leaveCategoryBreakdown', 'categoryWiseLeave', 'leaveApprovalLogs'));
    }

    public function approve(Request $request)
    {
        $data = LeaveRequest::findOrFail($request->log_request_id);

        // Process approval using the service
        $response = ApprovalHelper::processApproval($request, $data, $this->user, 'lvr_');

        return response()->json($response, $response['status'] === 'success' ? 200 : 500);
    }

    public function leaveRequestRevert(Request $request)
    {
        // Validate required inputs
        if (!$request->has('id') || !$request->filled('remark')) {
            return response()->json([
                'status' => false,
                'message' => 'Leave ID and remark are required.',
            ], 200);
        }

        // Decrypt the ID safely
        try {
            $decryptedId = Crypt::decrypt($request->input('id'));
        } catch (DecryptException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Leave ID.',
            ], 400);
        }

        // Start a database transaction for safety
        DB::beginTransaction();
        try {
            // Get all related leave requests (main or parent)
            $leaveRequests = LeaveRequest::where('lvr_id', $decryptedId)
                ->orWhere('lvr_p_id', $decryptedId)
                ->get();

            if ($leaveRequests->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Leave not found or not eligible for revert.',
                ], 400);
            }

            // Delete all related approval logs
            ApprovalLog::where('log_request_id', $decryptedId)->delete();

            // Update all related leave requests
            LeaveRequest::where('lvr_id', $decryptedId)
                ->orWhere('lvr_p_id', $decryptedId)
                ->update([
                    'lvr_status' => 140,
                    'lvr_stage_completed' => 0,
                    'is_reverted' => 1,
                    'revert_remark' => $request->input('remark'),
                ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Leave reverted successfully.',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong while reverting the leave.',
                'error' => $e->getMessage(), // Optional: remove in production
            ], 500);
        }

        $leaveRequest = LeaveRequest::where('lvr_id', $decryptedId)->where('lvr_stage_completed', 1)->first();
    }

    public function leaveRequestImport(Request $request)
    {
        $file = $request->file('import_file');
        $user = Auth::user();

        try {
            $fileExt = $file->getClientOriginalExtension();
            if (!in_array($fileExt, ['xlsx', 'csv'])) {
                return redirect()->back()->with('error', 'Invalid file format. Only .xlsx or .csv files are accepted.');
            }

            $leaveRequestImport = new LeaveRequestImport($user, $file);
            Excel::import($leaveRequestImport, $file);

            $successfulImports = $leaveRequestImport->successfulImports;
            $errorMessages = $leaveRequestImport->getErrorMessages();

            if (!empty($errorMessages)) {
                session()->put('import_errors', $errorMessages);
                session()->flash('import_errors_blade', $errorMessages);
                return redirect()->back()->with('error', 'Import failed! Please check the errors.');
            }

            if ($successfulImports > 0) {
                return redirect()->back()->with('success', "$successfulImports leave approval records imported successfully!");
            } else {
                return redirect()->back()->with('error', 'No leave approval records were imported. Please check the file contents.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'File import failed. Error: ' . $e->getMessage());
        }
    }

    public function downloadLeaveRequestSampleExcel()
    {
        // $filename = "employee-sheet.xlsx";
        // return Excel::download(new EmployeeExport(), $filename);
        $filePath = public_path('upload_sample/leave-request-bulk-upload.csv');
        // Check if the file exists
        if (file_exists($filePath)) {
            // Return the file as a download response
            return response()->download($filePath);
        } else {
            // If the file doesn't exist, return a 404 error
            return abort(404, 'File not found');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {

        $id = $request->input('id');

        $user = Auth::user();

        // Find leave requests with the given ID or parent ID
        $leaveRequests = LeaveRequest::where('lvr_id', $id)
            ->orWhere('lvr_p_id', $id)
            ->get();

        if ($leaveRequests->isEmpty()) {
            return [
                'result' => [],
                'message' => 'Leave request not found.',
                'status' => false
            ];
        }

        $isDeleted = true;

        foreach ($leaveRequests as $leaveRequest) {
            $leaveDeleted = $leaveRequest->delete();
         if (!$leaveDeleted) {
                $isDeleted = false;
            } else {
                if ($leaveRequest->lvr_is_comp_off) {
                    $compOffBalance = CompOffBalance::where('cb_emp_id', $leaveRequest->lvr_emp_id)->where('cb_b_id', $user->fh_business->b_id)
                            ->where('cb_year', now()->year)
                            ->where('cb_month', now()->month)
                            ->first();

                        if ($compOffBalance) {
                            $exist_cb_taken = $compOffBalance->cb_taken - $leaveRequest->lvr_total_leave_days;
                            $exist_cb_balance_remaining = $compOffBalance->cb_balance_remaining + $leaveRequest->lvr_total_leave_days;

                            $compOffBalance->cb_taken = $exist_cb_taken;
                            $compOffBalance->cb_balance_remaining = $exist_cb_balance_remaining;
                            $compOffBalance->save();
                        }
                } else {
                    // Manage leave balance for each deleted leave request
                    $leaveBalance = LeaveBalance::where([
                        ['lb_emp_id', '=', $user->emp_id],
                        ['lb_cat_type_id', '!=', 215],
                        ['lb_cat_type_id', '=', $leaveRequest->lvr_cat_type_id],
                        ['lb_b_id', '=', $user->fh_business->b_id],
                        ['lb_month', '=', now()->month],
                        ['lb_year', '=', now()->year],
                    ])
                        ->first();



                    if ($leaveBalance) {
                        $exist_lb_taken_leave = $leaveBalance->lb_taken_leave - $leaveRequest->lvr_total_leave_days;
                        $exist_lb_balance_remaining_leave = $leaveBalance->lb_balance_remaining_leave + $leaveRequest->lvr_total_leave_days;


                        $leaveBalance->lb_taken_leave = $exist_lb_taken_leave;
                        $leaveBalance->lb_balance_remaining_leave = $exist_lb_balance_remaining_leave;
                        $leaveBalance->save();
                    }
                }
            }
        }

        if ($isDeleted) {
            return [
                'result' => [],
                'message' => 'Leave request deleted successfully, and leave balance updated.',
                'status' => true
            ];
        } else {
            return [
                'result' => [],
                'message' => 'Failed to delete one or more leave requests.',
                'status' => false
            ];
        }
    }
}
