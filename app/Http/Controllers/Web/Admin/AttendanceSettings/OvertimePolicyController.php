<?php

namespace App\Http\Controllers\Web\Admin\AttendanceSettings;

use App\Models\OvertimePolicy;
use App\Models\CompOffPolicy;
use Illuminate\Validation\Rule;
use App\Helpers\ApprovalHelper;
use App\Helpers\CentralLogics;
use App\Helpers\ShiftResolver;
use App\Http\Controllers\Api\Attendance\PunchInApiController;
use App\Http\Controllers\Controller;
use App\Imports\AttendanceImport;
use App\Imports\DailyAttendanceImport;
use App\Models\AttendanceException;
use App\Models\AttendanceLog;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\MasterTable;
use App\Models\PolicyShiftTiming;
use App\Models\OtApprovalStatus;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use function PHPUnit\Framework\isNull;

class OvertimePolicyController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $shift_types = MasterTable::select('m_id', 'm_name')->where('m_group', 'ATTENDANCE_SHIFT_TYPE')->get();
        $overtimeRule = OvertimePolicy::where('ot_b_id', $user->emp_b_id)->first();
        if ($user) {
            return view('admin.setting.attendance.overtime-policy', compact('shift_types', 'overtimeRule'));
        }
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$request->is_enabled || $request->is_enabled === 0) {
            OvertimePolicy::where('ot_b_id', $user->emp_b_id)->update(['ot_is_enabled' => 0]);
            return response()->json([
                'success' => true,
                'message' => 'Overtime policy deactivate successfully.',
            ], 200);
        }
        $validator = Validator::make($request->all(), [
            'is_enabled'           => 'required|boolean',
            'ot_working_days'      => 'required|boolean',
            'ot_holidays'          => 'required|boolean',
            'required_work_hours'  => 'required|numeric|min:0|max:24',
            'min_ot_minutes'       => 'required|numeric|min:0|max:120',
            'max_ot_daily'         => 'required|numeric|min:0|max:50',
            'max_ot_monthly'       => 'required|numeric|min:0|max:500',
            'ot_basis'             => 'required|string',
            'buffer_minutes'       => 'nullable|numeric|min:0|max:60',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $policy = OvertimePolicy::where('ot_b_id', $user->emp_b_id)->first();

            $data = [
                'ot_b_id'                => $user->emp_b_id ?? 1,
                'ot_is_enabled'          => $request->is_enabled,
                'ot_working_day'         => $request->ot_working_days,
                'ot_non_working_day'     => $request->ot_holidays,
                'ot_calculation_method'  => $request->ot_basis,
                'ot_buffer_mins_per_day' => $request->buffer_minutes,
                'ot_min_work_per_day'    => $request->required_work_hours,
                'ot_min_work_required'   => $request->min_ot_minutes,
                'ot_max_work_per_day'    => $request->max_ot_daily,
                'ot_max_work_per_month'  => $request->max_ot_monthly,
            ];

            // if ($request->is_enabled == 1) {
                if ($policy) {
                    $policy->update($data);
                    $message = 'Overtime policy updated successfully.';
                } else {
                    OvertimePolicy::create($data);
                    $message = 'Overtime policy created successfully.';
                }
                return response()->json(['success' => true, 'message' => $message]);
            // }
            // return response()->json(['success' => false, 'message' => 'Please enable OT field.']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function overtime_index(Request $request)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;
        
        // Handle date filter - single date or range
        $dateFilter = $request->input('fromDate') ?? now()->toDateString();
        
        // Filters
        $branchFilter = $request->input('daily_branchFilter');
        $designationFilter = $request->input('daily_designationFilter');
        $departmentFilter = $request->input('daily_departmentFilter');
        $fromToDateFilter = $request->input('fromDate');
        $statusFilter = $request->input('daily_activeFilter');

        $approvalBtnData = [];
        $canApprove = false;
        $masterApproveBtn = null;
        $moduleId = [];

        // Handle AJAX request
        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['ot_b_id', $businessId]
                ],
                [
                    'method' => 'select',
                    'args' => [
                        'ot_id', 'ot_atd_id', 'ot_b_id', 'ot_emp_id', 'ot_atd_type', 'ot_date',
                        'ot_module_id', 'ot_next_approver', 'ot_requested_status', 'ot_am_id',
                        'ot_stage_completed', 'ot_approved_by', 'created_at', 'updated_at'
                    ],
                    'relation' => [
                        'fh_employee:emp_id,emp_b_id,emp_full_name,emp_code,emp_br_id,emp_dg_id,emp_d_id,emp_status,emp_profile_photo',
                        'fh_approval_status:m_id,m_name,m_other'
                    ]
                ],
            ];

            // Check if date range filter is provided
            // if (!empty($fromToDateFilter)) {
            //     $dates = explode(' - ', $fromToDateFilter);
                
            //     if (count($dates) == 2) {
            //         // Date range filter - parse both dates
            //         $startDate = \Carbon\Carbon::createFromFormat('M d, Y', $dates[0])->startOfDay();
            //         $endDate = \Carbon\Carbon::createFromFormat('M d, Y', $dates[1])->endOfDay();
                    
            //         $dynamicConditions[] = [
            //             'method' => 'whereBetween',
            //             'args' => ['ot_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]]
            //         ];
            //     } else {
            //         // Single date filter
            //         try {
            //             $singleDate = \Carbon\Carbon::createFromFormat('M d, Y', $fromToDateFilter)->toDateString();
            //             $dynamicConditions[] = [
            //                 'method' => 'whereDate',
            //                 'args' => ['ot_date', $singleDate]
            //             ];
            //         } catch (\Exception $e) {
            //             // Fallback to original dateFilter if format is different
            //             $dynamicConditions[] = [
            //                 'method' => 'whereDate',
            //                 'args' => ['ot_date', $dateFilter]
            //             ];
            //         }
            //     }
            // } else {
            //     // Use the single date filter if no range is provided
            //     $dynamicConditions[] = [
            //         'method' => 'whereDate',
            //         'args' => ['ot_date', $dateFilter]
            //     ];
            // }

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

            if ($statusFilter != '') {
                $dynamicConditions[] = [
                    'method' => 'whereRelation',
                    'parentMethod' => 'whereHas',
                    'childMethod' => 'where',
                    'args' => ['emp_status', $statusFilter],
                    'relation' => 'fh_employee'
                ];
            }

            // Create helper and fetch paginated data + counts
            $helper = new DynamicModelDataTableHelper(
                eloquentModel: new OtApprovalStatus(),
                dynamicConditions: $dynamicConditions,
                searchColumns: ['ot_id', 'ot_atd_id', 'ot_emp_id', 'ot_date', 'ot_module_id', 'ot_next_approver'],
                searchRelationships: [
                    'fh_employee' => ['emp_fname', 'emp_mname', 'emp_lname', 'emp_full_name', 'emp_code'],
                ]
            );

            $list = $helper->getServerSideDataTable();
            $filteredCount = $helper->countFilteredServerSideDataTable();

            // Update total records count based on date filter
            // if (!empty($fromToDateFilter)) {
            //     $dates = explode(' - ', $fromToDateFilter);
                
            //     if (count($dates) == 2) {
            //         $startDate = \Carbon\Carbon::createFromFormat('M d, Y', $dates[0])->startOfDay();
            //         $endDate = \Carbon\Carbon::createFromFormat('M d, Y', $dates[1])->endOfDay();
                    
            //         $recordsTotal = OtApprovalStatus::where('ot_b_id', $businessId)
            //             ->whereBetween('ot_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            //             ->count();
            //     } else {
            //         try {
            //             $singleDate = \Carbon\Carbon::createFromFormat('M d, Y', $fromToDateFilter)->toDateString();
            //             $recordsTotal = OtApprovalStatus::where('ot_b_id', $businessId)
            //                 ->whereDate('ot_date', $singleDate)
            //                 ->count();
            //         } catch (\Exception $e) {
            //             $recordsTotal = OtApprovalStatus::where('ot_b_id', $businessId)
            //                 ->whereDate('ot_date', $dateFilter)
            //                 ->count();
            //         }
            //     }
            // } else {
            //     $recordsTotal = OtApprovalStatus::where('ot_b_id', $businessId)
            //         ->whereDate('ot_date', $dateFilter)
            //         ->count();
            // }

            if ($request->get_ids == 1) {
                $query = OtApprovalStatus::where('ot_b_id', $businessId);

                $fromToDateFilter = $request->input('fromDate');
                $branchFilter     = $request->input('daily_branchFilter');
                $departmentFilter = $request->input('daily_departmentFilter');
                $designationFilter= $request->input('daily_designationFilter');
                $statusFilter     = $request->input('daily_activeFilter');

                if ($branchFilter != '') {
                    $query->whereHas('fh_employee', function ($q) use ($branchFilter) {
                        $q->where('emp_br_id', $branchFilter);
                    });
                }

                if ($departmentFilter != '') {
                    $query->whereHas('fh_employee', function ($q) use ($departmentFilter) {
                        $q->where('emp_d_id', $departmentFilter);
                    });
                }

                if ($designationFilter != '') {
                    $query->whereHas('fh_employee', function ($q) use ($designationFilter) {
                        $q->where('emp_dg_id', $designationFilter);
                    });
                }

                if ($statusFilter != '') {
                    $query->whereHas('fh_employee', function ($q) use ($statusFilter) {
                        $q->where('emp_status', $statusFilter);
                    });
                }

                if (!empty($fromToDateFilter)) {
                    $dates = explode(' - ', $fromToDateFilter);

                    if (count($dates) == 2) {
                        $startDate = Carbon::createFromFormat('M d, Y', $dates[0])->startOfDay();
                        $endDate = Carbon::createFromFormat('M d, Y', $dates[1])->endOfDay();

                        $query->whereDate('created_at', '<=', $endDate->format('Y-m-d'));

                        $query->whereDate('created_at', '>=', $startDate->format('Y-m-d'));
                    }
                }

                $ids = $query->pluck('ot_id')->map(fn($id) => (md5($id)));
                return response()->json([
                    'ids' => $ids
                ]);
            }

            $rowData = [];
            $i = intval($request->input('start', 0)) + 1; // DataTables start offset aware

            foreach ($list as $val) {
                $approvalData = ApprovalHelper::getApprovalOrRejectionData($val->ot_id, $val->ot_requested_status, $val->ot_am_id, $user->emp_id, 562);
                
                if ($approvalData) {
                    $approvalBtnData['approval_status'] = $approvalData->fh_approver_status->m_id;
                    $approvalBtnData['approval_action_type'] = $approvalData->pa_type;
                    $approvalBtnData['approval_sequence'] = $approvalData->pa_sequence;
                    $approvalBtnData['module_id'] = $approvalData->pa_am_id;
                    $approvalBtnData['is_last_approval'] = $approvalData->pa_last;
                    $approvalBtnData['emp_d_id'] = optional($val->fh_employee)->emp_d_id;
                } else {
                    $val->approvalData = $approvalData;
                    $approval = ApprovalHelper::getApprovalData($val, $user, 'ot_');
                    $masterApproveBtn = $approval['masterApproveBtn'];
                    $canApprove = $approval['canApprove'];
                    $moduleId['module_id'] = $val->ot_module_id;
                }

                // Resolve employee object
                $emp = $val->fh_employee ?? $val->employee ?? null;

                // Defensive: if no employee attached, skip row
                if (!$emp) {
                    continue;
                }

                // Prepare attendance/exception/log lookups (use ot_date and employee id)
                $otDate = $val->ot_date ?? $dateFilter;
                $empId = $emp->emp_id;

                // Attendance (matching your previous queries)
                $attendance = AttendanceRecord::with([
                    'fh_approval_status',
                    'fh_plan_approval_log.fh_employee',
                    'fh_plan_approval_log.fh_role',
                    'fh_plan_approval_log.fh_status',
                    'fh_approval_log2.fh_employee',
                    'fh_approval_log2.fh_role',
                    'fh_approval_log2.fh_status',
                    'fh_policy_shift_timing',
                    'fh_attendance_checkin_type'
                ])
                    ->where('atd_emp_id', $empId)
                    ->whereDate('atd_date', $otDate)
                    ->first();

                $atd_log = AttendanceLog::where('al_emp_id', $empId)
                    ->whereDate('al_date', $otDate)
                    ->latest()
                    ->first();

                $exception = AttendanceException::where('ae_emp_id', $empId)
                    ->whereDate('ae_date', $otDate)
                    ->where('ae_stage_completed', 1)
                    ->where('ae_status', '!=', 170)
                    ->first();

                $checkInTime = $checkoutTime = null;
                $workHrs = $overtime_duration = 0;
                $method = $status = $style = '';
                $approvalStatus = null;
                $lateMessage = $overMessage = '';

                // 1. Exception
                if ($exception) {
                    $method = "Missed Punch";
                    $checkInTime = $exception->ae_in_time ?? optional($attendance)->atd_check_in_time;
                    $checkoutTime = $exception->ae_out_time ?? optional($attendance)->atd_check_out_time;
                    $workHrs = $exception->ae_total_working
                        ? $exception->ae_total_working
                        : abs(\Carbon\Carbon::parse($checkInTime)->diffInMinutes(\Carbon\Carbon::parse($checkoutTime)));
                }
                // 2. Admin log
                elseif ($atd_log) {
                    $method = "Admin";
                    $checkInTime = $atd_log->al_check_in_time;
                    $checkoutTime = $atd_log->al_check_out_time;
                    $workHrs = $atd_log->al_total_worked_hours;
                    $overtime_duration = $atd_log->al_overtime_hours ?? 0;
                }
                // 3. Attendance record
                elseif ($attendance) {
                    $method = optional($attendance->fh_attendance_checkin_type)->m_name ?? '--';
                    $checkInTime = $attendance->atd_check_in_time;
                    $checkoutTime = ($attendance->atd_check_out_time && \Carbon\Carbon::parse($attendance->atd_check_out_time)->format('H:i') !== '00:00')
                        ? $attendance->atd_check_out_time
                        : null;
                    $workHrs = $attendance->atd_total_worked_hours ?? '--';
                    $overtime_duration = $attendance->atd_overtime_hours ?? 0;
                }
                // 4. Absent
                else {
                    $method = '--';
                    $status = 'Absent';
                    $style = 'color:red;';
                    $workHrs = '--';
                }

                // Prepare formatted row
                $profilePhoto = $emp->emp_profile_photo ?: asset('assets/imgs/user.png');

                $row = [];
                $row[] = $i++;
                $row[] = '<div class="d-flex">
                            <span class="avatar avatar-md brround me-3 rounded-circle" style="background-image: url(\'' . $profilePhoto . '\');"></span>
                            <div class="me-3 mt-0 mt-sm-1 d-block">
                                <h6 class="mb-1 fs-14">' . e($emp->emp_full_name) . '</h6>
                                <p class="text-muted mb-0 fs-10">' . e(optional($emp->fh_designation)->dg_name ?? '') . '</p>
                            </div>
                          </div>';
                $row[] = e($emp->emp_code);
                $row[] = e($method);
                $row[] = $checkInTime ? \Carbon\Carbon::parse($checkInTime)->format('H:i') : '--';
                $row[] = $checkoutTime ? \Carbon\Carbon::parse($checkoutTime)->format('H:i') : '--';
                $row[] = CentralLogics::convertHourMins($workHrs).' Hrs';
                $row[] = $overtime_duration.' Hrs';
                // $row[] = CentralLogics::convertHourMins($overtime_duration).' Hrs';

                // Approval / status related items from OtApprovalStatus record ($val)
                $ot_id = $val->ot_id ?? null;
                $ot_b_id = $val->ot_b_id ?? null;
                $ot_module_id = $val->ot_module_id ?? null;
                $ot_requested_status = $val->ot_requested_status ?? null;

                // Get next approval & approval check using your helpers (defensive nulls)
                $nextApproval = [];
                try {
                    $nextApproval = ApprovalHelper::getNextApprovalDetails(562, $ot_id, $ot_b_id) ?? [];
                } catch (\Throwable $e) {
                    $nextApproval = [];
                }

                $approval = [];
                try {
                    $approval = ApprovalHelper::checkApproval($ot_b_id, $ot_module_id, $ot_id, $emp->emp_d_id ?? null, $empId) ?? [];
                } catch (\Throwable $e) {
                    $approval = [];
                }

                $formattedLog = '';
                $comma = false;
                $logData = $val->fh_plan_approval_log;

                if ($logData && count($logData)) {
                    foreach ($logData as $log) {
                        if ($log->log_am_id == $val->ot_am_id) {
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
                               title="' . ($safePopoverContent ?? 'Approval Log') . '">
                               </i>'
                    // Show approval count only for statuses other than 140
                    . (!$is_auto_approval ? '<p class="text-muted mb-0 fs-12">Approval ' . ($approval['approvallog_count'] ?? ' ') . ' (' . ($approval['approvalcount'] ?? ' ') . ')</p>' : '');

                // Determine next approver name if not returned by nextApproval
                $approverName = $nextApproval['approver_name'] ?? null;
                if (empty($approverName)) {
                    try {
                        $approvalMapping = ApprovalHelper::getApprovalMapping($emp->emp_b_id ?? $businessId, $emp->emp_id, $ot_module_id);
                        if ($approvalMapping) {
                            $approvalArray = ApprovalHelper::getApprovalArray($approvalMapping);
                            $approvalLog   = $logSource->fh_approval_log2?->pluck('log_user_id')?->toArray() ?? [];
                            $diff          = array_values(array_diff($approvalArray, $approvalLog));
                            $approverName  = !empty($diff)
                                ? Employee::where('emp_id', $diff[0])->value('emp_full_name')
                                : '---';
                        } else {
                            $approverName = '---';
                        }
                    } catch (\Throwable $e) {
                        $approverName = '---';
                    }
                }

                $row[] = '<span class="text-center">' . e($approverName ?? '---') . '</span>';

                // Action Buttons (link to approve page). Use md5 of ot_id (consistent with your original)
                $row[] = '<a href="' . url('admin/approve/overtime-approve/' . md5($ot_id)) . '"><i class="feather-eye fs-6" style="margin-top:10px;"></i></a>';

                // Checkbox for bulk selection
                if (($canApprove && $val->ot_stage_completed == 0) || (!empty($approvalData) && $val->ot_stage_completed == 0)) {
                    $row[] = '<div class="d-flex">
                                <label class="custom-control custom-checkbox-md p-0 ms-2">
                                    <input type="checkbox"
                                        class="custom-control-input-success employee-checkbox testClass"
                                        name="ot_ids[]"
                                        value="' . e(md5($ot_id)) . '"
                                        data-id="' . e(md5($ot_id)) . '"
                                        onclick="selectOTCheckbox(this)">
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
                    eloquentModel: new OtApprovalStatus(),
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
            $sampleOT = OtApprovalStatus::where('ot_b_id', $user->emp_b_id)
                ->where('ot_stage_completed', 0)
                ->orderBy('ot_id', 'desc')
                ->limit(1)
                ->first();

            if ($sampleOT) {
                $approval = ApprovalHelper::getApprovalData($sampleOT, $user, 'ot_');
                $masterApproveBtn = $approval['masterApproveBtn'] ?? null;
                $canApprove       = $approval['canApprove'] ?? false;
            }
        }

        $columns = [
            'S.No.', 'Emp. Name', 'Emp. Code', 'Method',
            'Checkin', 'Checkout', 'Working Hours', 'Overtime Hours',
            'Approval Status', 'Submitted To', 'Action', 'Select All'
        ];

        return view('admin.setting.attendance-details.overtime-approval', compact('columns', 'masterApproveBtn', 'canApprove',));
    }

    public function edit($id) {
        $data = OtApprovalStatus::with('fh_approval_status:m_id,m_name,m_other')->where(DB::raw('md5(ot_id)'), $id)->firstOrFail();

        $employee = Employee::where('emp_b_id', $data->ot_b_id)
        ->where('emp_id', $data->ot_emp_id)
        ->first();

        $attendance = null;

        if ($data->ot_atd_type == 'msp') {
            $a = AttendanceException::where('ae_id', $data->ot_atd_id)->first();
            if ($a) {
                $attendance = [
                    'check_in'   => $a->ae_in_time,
                    'check_out'  => $a->ae_out_time,
                    'total_work' => $a->ae_total_working,
                    'overtime'   => null
                ];
            }
        } else if ($data->ot_atd_type == 'log') {
            $a = AttendanceLog::where('al_id', $data->ot_atd_id)->first();
            if ($a) {
                $attendance = [
                    'check_in'   => $a->al_check_in_time,
                    'check_out'  => $a->al_check_out_time,
                    'total_work' => $a->al_total_worked_hours,
                    'overtime'   => CentralLogics::convertHourMins($a->al_overtime_hours)
                ];
            }
        } else if ($data->ot_atd_type == 'rec') {
            $a = AttendanceRecord::where('atd_id', $data->ot_atd_id)->first();
            if ($a) {
                $attendance = [
                    'check_in'   => $a->atd_check_in_time,
                    'check_out'  => $a->atd_check_out_time,
                    'total_work' => $a->atd_total_worked_hours,
                    'overtime'   => CentralLogics::convertHourMins($a->atd_overtime_hour)
                ];
            }
        }

        // Get approval data
        $approvalData = ApprovalHelper::getApprovalOrRejectionData($data->ot_id, $data->ot_requested_status, $data->ot_am_id, NULL, 562);
        $data->approvalData = $approvalData;
        $approval = ApprovalHelper::getApprovalData($data, Auth::user(), 'ot_');
        $masterApproveBtn = $approval['masterApproveBtn'];
        $canApprove = $approval['canApprove'];

        return view('admin.setting.attendance-details.overtime-approval-show',
            compact('data', 'employee', 'attendance', 'approvalData', 'canApprove', 'masterApproveBtn')
        );
    }

    public function approve(Request $request)
    {
        $data = OtApprovalStatus::findOrFail($request->log_request_id);

        // Process approval using the service
        $response = ApprovalHelper::processApproval($request, $data, Auth::user(), 'ot_');

        return response()->json($response, $response['status'] === 'success' ? 200 : 500);
    }
}
