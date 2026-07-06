<?php

namespace App\Http\Controllers\Web\Admin\AttendanceDetails;

use App\Helpers\ApprovalHelper;
use App\Helpers\CentralLogics;
use App\Http\Controllers\Controller;
use App\Models\ApprovalLog;
use App\Models\AttendanceException;
use App\Models\CompOffPolicy;
use App\Models\Employee;
use App\Models\AttendanceRecord;
use App\Models\EmployeeApprovalMapping;
use App\Models\MasterTable;
use App\Models\PolicyHolidayList;
use Carbon\Carbon;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

class MispunchController extends Controller
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
        if ($this->user) {
            $branchFilter = request()->input('punch_branchFilter');
            $departmentFilter = request()->input('punch_departmentFilter');
            $designationFilter = request()->input('punch_designationFilter');
            $fromToDateFilter = request()->input('fromDate');

            $approvalBtnData = [];
            $canApprove = true;
            $masterApproveBtn = null;
            $moduleId = [];

            if ($request->ajax()) {
                $dynamicConditions = [
                    [
                        'method' => 'where',
                        'args' => ['ae_b_id', $this->user->emp_b_id]
                    ],
                    [
                        'method' => 'select',
                        'args' => [
                            'ae_id',
                            'ae_b_id',
                            'ae_ap_id',
                            'ae_emp_id',
                            'ae_date',
                            'ae_type_id',
                            'ae_approved_by',
                            'ae_in_time',
                            'ae_out_time',
                            'ae_total_working',
                            'ae_reason_id',
                            'ae_stage_completed',
                            'ae_am_id',
                            'ae_next_approver',
                            'ae_module_id',
                            'ae_status',
                            'ae_status',
                            'created_at', // Record creation timestamp
                            'updated_at', // Record update timestamp
                        ],
                        'relation' => ['fh_employee:emp_id,emp_b_id,emp_full_name,emp_code,emp_br_id,emp_dg_id,emp_d_id', 'fh_approval_status:m_id,m_name,m_other']
                    ],
                    [
                        'method' => 'sortBy',
                        'args' => ['ae_id', 'ae_b_id', 'ae_ap_id', 'ae_emp_id', 'ae_date', 'ae_type_id', 'ae_approved_by', 'ae_in_time', 'ae_out_time', 'ae_total_working', 'ae_reason_id', 'ae_stage_completed', 'created_at', 'updated_at'],
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

                $searchColumns = [
                    'ae_b_id',
                    'ae_ap_id',
                    'ae_emp_id',
                    'ae_date',
                    'ae_type_id',
                    'ae_approved_by',
                    'ae_in_time',
                    'ae_out_time',
                    'ae_total_working',
                    'ae_reason_id',
                    'ae_stage_completed',
                    'updated_at', // Record update timestamp
                ];

                $searchRelationships = [
                    'fh_employee' => ['emp_full_name', 'emp_code'],
                ];

                $list = (new DynamicModelDataTableHelper(
                    eloquentModel: new AttendanceException(),
                    dynamicConditions: $dynamicConditions,
                    searchColumns: $searchColumns,
                    searchRelationships: $searchRelationships
                ))->getServerSideDataTable();

                $rowData = [];
                $i = 0;
                foreach ($list as $val) {
                    $i++;
                    $row = [];

                    // ✅ Approval handling
                    $approvalData = ApprovalHelper::getApprovalOrRejectionData($val->ae_id, $val->ae_status, $val->ae_am_id, NULL, 229);
                    if ($approvalData) {
                        $approvalBtnData['approval_status'] = $approvalData->fh_approver_status->m_id;
                        $approvalBtnData['approval_action_type'] = $approvalData->pa_type;
                        $approvalBtnData['approval_sequence'] = $approvalData->pa_sequence;
                        $approvalBtnData['module_id'] = $approvalData->pa_am_id;
                        $approvalBtnData['is_last_approval'] = $approvalData->pa_last;
                        $approvalBtnData['emp_d_id'] = optional($val->fh_employee)->emp_d_id;
                    } else {
                        $val->approvalData = $approvalData;
                        $approval = ApprovalHelper::getApprovalData($val, $this->user, 'ae_');
                        $masterApproveBtn = $approval['masterApproveBtn'];
                        $canApprove = $approval['canApprove'];
                        $moduleId['module_id'] = $val->ae_module_id;
                    }

                    $row[] = $i;
                    $row[] = optional($val->fh_employee)->emp_full_name ;
                    $row[] = optional($val->fh_employee)->emp_code;
                    $row[] = $val->created_at->format('d M Y');
                    $row[] = $val->ae_date->format('d M Y');
                    $row[] = optional($val->fh_mispunch_type)->m_name;

                    $in_time = new DateTime($val->ae_in_time);
                    $out_time = new DateTime($val->ae_out_time);
                    $interval = $in_time->diff($out_time);
                    $total_working = $interval->format('%H.%I'); // Hours and minutes

                    $row[] = $in_time->format('H:i');
                    $row[] = $out_time->format('H:i');
                    $row[] = $total_working;
                    $row[] = optional($val->fh_mispunch_reason)->m_name;
                    $nextApproval = ApprovalHelper::getNextApprovalDetails(229, $val->ae_id, $val->ae_b_id);
                    $approval = ApprovalHelper::checkApproval($this->user->emp_b_id, $val->ae_module_id, $val->ae_id, optional($val->fh_employee)->emp_d_id, optional($val->fh_employee)->emp_id);
                    $logData = $val->fh_plan_approval_log;
                    // Check if the log data is not empty
                    $formattedLog = '';
                    $comma = false;

                    if (count($logData)) {
                        foreach ($logData as $log) {
                            if ($log->log_am_id == $val->ae_am_id) {
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
                    $popoverContent = $formattedLog === '' ?  ($val->ae_status == 171 ? 'Auto Approved' : 'Awaiting') : $formattedLog;

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
                        $approvalMapping = ApprovalHelper::getApprovalMapping($this->user->emp_b_id, $val->ae_emp_id, $val->ae_module_id);

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
                    $row[] =   '<a class="" href="' . url('admin/requests/mis-punch/' . md5($val->ae_id)) . '">  <i class="feather-eye fs-6" style=" margin-top: 10;"></i></a>';
                    
                    if (($canApprove && $val->ae_stage_completed == 0) || (!empty($approvalData) && $val->ae_stage_completed == 0)) {
                        $row[] = '<div class="d-flex">
                            <label class="custom-control custom-checkbox-md p-0 ms-2">
                                <input type="checkbox" class="custom-control-input-success select-checkbox testClass"
                                    name="ae_ids[]" value="' . md5($val->ae_id) . '"
                                    onclick="selectCheckbox(this)">
                                <span class="custom-control-label-md success"></span>
                            </label>
                        </div>';
                    } else {
                        $row[] = '';
                    }

                    $rowData[] = $row;
                }

                $output = [
                    "draw" => intval($request->input('draw')),
                    "recordsTotal" => sizeof($list),
                    "recordsFiltered" => (new DynamicModelDataTableHelper(
                        eloquentModel: new AttendanceException(),
                        dynamicConditions: $dynamicConditions
                    ))->countFilteredServerSideDataTable(),
                    "data" => $rowData,
                    "apHiBtn" => $approvalBtnData,
                    "apEmpBtn" => $masterApproveBtn,
                    "canApprove" => $canApprove,
                    "moduleId" => $moduleId
                ];

                return response()->json($output);
            }

            if (!$request->ajax()) {
                // Fetch relevant leave requests based on your business logic
                $relevantMSPRequests = AttendanceException::where('ae_b_id', $this->user->emp_b_id)
                    ->where('ae_stage_completed', 0)
                    ->get();
                
                foreach ($relevantMSPRequests as $val) {
                    $approval = ApprovalHelper::getApprovalData($val, $this->user, 'ae_');
                    if (!empty($approval['masterApproveBtn'])) {
                        $masterApproveBtn = $approval['masterApproveBtn'];
                        $canApprove = $approval['canApprove'];
                    }
                }
            }

            $columns = [
                'S. No.',
                'Emp. Name',
                'Emp. Code',
                'Applied Date',
                'Date        ',
                'Type',
                'Check In',
                'Check Out',
                'Working Hour',
                'Reason',
                'Approval Status',
                'Submitted To',
                'Action',
                'Select All',
            ];
            $masterData = MasterTable::whereIn('m_group', ['RECURRENCE_DAY', 'WEEK_DAY'])
                ->get()
                ->groupBy('m_group');

            $business = $this->user->fh_business()
                ->with(
                    'fh_branches:br_id,br_b_id,br_name',
                    'fh_departments:d_id,d_b_id,d_name',
                    'fh_designations:dg_id,dg_name,dg_b_id'
                )
                ->where('b_id', $this->user->emp_b_id)
                ->get();

            // Pluck the required fields from each relationship
            $branch = $business->first()->fh_branches->pluck('br_id', 'br_name')->toArray();
            $departments = $business->first()->fh_departments->pluck('d_id', 'd_name')->toArray();
            $designations = $business->first()->fh_designations->pluck('dg_id', 'dg_name')->toArray();
            $msp_status = MasterTable::whereIn('m_id', [139, 140, 141, 156, 157, 170, 171, 174])->get();

            $recurrenceDay = $masterData->get('RECURRENCE_DAY', collect())
                ->pluck('m_name', 'm_id')
                ->toArray();

            $weekDay = $masterData->get('WEEK_DAY', collect())
                ->pluck('m_name', 'm_id')
                ->toArray();

            return view('admin.setting.attendance-details.mis-punch-requests',  compact(
                'departments',
                'designations',
                'branch',
                'masterApproveBtn',
                'canApprove',
                'columns',
                'msp_status',
            ));
        } else {
            abort(404);
        }
    }

    private function canApprove($approvalMappingArray, $approvalLog, $userId)
    {
        $diff = array_values(array_diff($approvalMappingArray, $approvalLog));
        return isset($diff[0]) && $diff[0] == $userId;
    }
    public function show(string $id)
    {
        $data = AttendanceException::with(
            'fh_employee:emp_id,emp_fname,emp_mname,emp_lname,emp_dg_id,emp_d_id,emp_phone,emp_code,emp_profile_photo,emp_full_name,emp_email,emp_status,emp_br_id',
            'fh_employee.fh_department:d_id,d_name',
            'fh_employee.fh_designation:dg_id,dg_name',
            'fh_employee.fh_branch:br_id,br_name',
            'fh_approval_status:m_id,m_name,m_other'
        )->where((DB::raw('md5(ae_id)')), $id)->first();

        $employee = Employee::where('emp_b_id', $data->ae_b_id)->where('emp_id', $data->ae_emp_id)->first();

        $records = AttendanceRecord::where('atd_emp_id', $data->ae_emp_id)->where('atd_date', $data->ae_date)->first();

        // Check if current day is a weekly off day
        $isWeeklyOff = CentralLogics::getWeekOffDatesReport($employee, null, null, ($data->ae_date)->format('Y-m-d'), $data->ae_date->format('Y-m-d'));

        // Check if current day is a holiday
        $isHoliday = PolicyHolidayList::where('phl_b_id', $employee->emp_b_id)
            ->whereDate('phl_start_date', '<=', $data->ae_date)
            ->whereDate('phl_end_date', '>=', $data->ae_date)
            ->where('phl_type_id', 206)
            ->exists();

        if ($isWeeklyOff || $isHoliday) {
            $compOffPolicy = CompOffPolicy::with("duration_conditions")
                ->where('cop_b_id', $data->ae_b_id)
                ->where('cop_effective_date', '<=', $data->ae_date)
                ->where('cop_status', 1)
                ->orderBy('cop_effective_date', 'desc')
            ->first();
            $co_quantity = $compOffPolicy ? CentralLogics::getCompOffQuantity($data->ae_total_working, $compOffPolicy->duration_conditions) : 0;
        }

        $mspApprovalLogs = ApprovalLog::with(
            'fh_employee:emp_id,emp_full_name,emp_code,emp_dg_id',
            'fh_employee.fh_designation:dg_id,dg_name',
            'fh_status:m_id,m_name,m_other'
        )
        ->where('log_request_id', $data->ae_id)
        ->where('log_module_id', 229)
        ->orWhere('log_am_id', 229)
        ->orderBy('created_at')
        ->get();

        $data->co_quantity = $co_quantity ?? 0;

        // Get approval data
        $approvalData = ApprovalHelper::getApprovalOrRejectionData($data->ae_id, $data->ae_status, $data->ae_am_id, NULL, 229);
        // Check for approval buttons and approval possibility
        // Check if the user employee mapping can approve start
        $data->approvalData = $approvalData;
        $approval = ApprovalHelper::getApprovalData($data, $this->user, 'ae_');
        $masterApproveBtn = $approval['masterApproveBtn'];
        $canApprove = $approval['canApprove'];
        // Check if the user employee mapping can approve end

        return view('admin.setting.attendance-details.mis-punch-requests-show', compact('data', 'approvalData', 'masterApproveBtn', 'canApprove', 'records', 'mspApprovalLogs'));  // Ensure the correct view path
    }

    public function approve(Request $request)
    {
        $data = AttendanceException::findOrFail($request->log_request_id);

        // Process approval using the service
        $response = ApprovalHelper::processApproval($request, $data, $this->user, 'ae_');

        return response()->json($response, $response['status'] === 'success' ? 200 : 500);
    }
}
