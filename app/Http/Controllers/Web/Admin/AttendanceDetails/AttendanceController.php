<?php

namespace App\Http\Controllers\Web\Admin\AttendanceDetails;

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
use App\Models\CompOffPolicy;
use App\Models\Department;
use App\Models\Designation;
use App\Models\MasterTable;

use App\Models\PolicyShiftTiming;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

use function PHPUnit\Framework\isNull;

class AttendanceController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function downloadDailyAttendanceSampleExcel()
    {
        $filePath = public_path('upload_sample/daily-attendance-upload.xlsx');
        // Check if the file exists
        if (file_exists($filePath)) {
            // Return the file as a download response
            return response()->download($filePath);
        } else {
            // If the file doesn't exist, return a 404 error
            return abort(404, 'File not found');
        }
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;
        $dateFilter = $request->input('fromDate') ?? now()->toDateString();

        // Filters
        $branchFilter = request()->input('daily_branchFilter');
        $designationFilter = request()->input('daily_designationFilter');
        $departmentFilter = request()->input('daily_departmentFilter');
        $statusFilter = request()->input('daily_activeFilter');
        $approvalBtnData = [];
        $canApprove = false;
        $masterApproveBtn = null;
        $moduleId = [];

        // Counts for the selected date
        // $query = Employee::where('emp_b_id', $businessId)
        //     ->whereNot('emp_role_id', 1);
        
        $query = Employee::where('emp_b_id', $businessId)
            ->whereNot('emp_role_id', 1)
            ->whereDate('emp_date_of_joining', '<=', $dateFilter) // Joining date se pehle nahi dikhna chahiye
            ->where(function ($q) use ($dateFilter) {
                $q->whereNull('emp_last_working_date') // Agar last working date null hai to abhi tak active hai
                  ->orWhereDate('emp_last_working_date', '>=', $dateFilter); // Agar last working date hai to wo date ke baad tak hi dikhega
            });

        if ($branchFilter) {
            $query->where('emp_br_id', $branchFilter);
        }
        if ($departmentFilter) {
            $query->where('emp_d_id', $departmentFilter);
        }
        if ($designationFilter) {
            $query->where('emp_dg_id', $designationFilter);
        }
        if ($statusFilter) {
            $query->where('emp_status', $statusFilter);
        }

        $totalEmployees = $query->count();

        // Present count (not absent)
        $presentQuery = AttendanceRecord::where('atd_b_id', $businessId)
            ->whereDate('atd_date', $dateFilter)
            ->where('atd_is_absent', 0);

        if ($branchFilter) {
            $presentQuery->whereHas('fh_employee', function ($q) use ($branchFilter) {
                $q->where('emp_br_id', $branchFilter);
            });
        }
        if ($departmentFilter) {
            $presentQuery->whereHas('fh_employee', function ($q) use ($departmentFilter) {
                $q->where('emp_d_id', $departmentFilter);
            });
        }
        if ($designationFilter) {
            $presentQuery->whereHas('fh_employee', function ($q) use ($designationFilter) {
                $q->where('emp_dg_id', $designationFilter);
            });
        }
        if ($statusFilter) {
            $presentQuery->whereHas('fh_employee', function ($q) use ($statusFilter) {
                $q->where('emp_status', $statusFilter);
            });
        }

        $presentCount = $presentQuery->count();
        
        // Half Day Count Logic Based on Current Date
        $today = Carbon::today()->toDateString();
        
        if ($dateFilter === $today) {
            // Use LeaveRequest for today's date
            $halfDayQuery = LeaveRequest::where('lvr_b_id', $businessId)
                ->whereDate('lvr_start_date', '=', $dateFilter)
                ->whereDate('lvr_end_date', '=', $dateFilter)
                ->where('lvr_leave_day_type_id', 202);
        } else {
            // Use AttendanceRecord for other dates
            $halfDayQuery = AttendanceRecord::where('atd_b_id', $businessId)
                ->whereDate('atd_date', $dateFilter)
                ->where('atd_attendance_status', 252);
        }
        
        // Apply branch filter to both models
        if ($branchFilter) {
            $halfDayQuery->whereHas('fh_employee', function ($q) use ($branchFilter) {
                $q->where('emp_br_id', $branchFilter);
            });
        }
        
        $halfDayCount = $halfDayQuery->count();

        // Late count
        $lateQuery = AttendanceRecord::where('atd_b_id', $businessId)
            ->whereDate('atd_date', $dateFilter)
            ->where('atd_is_late', 1);

        if ($branchFilter) {
            $lateQuery->whereHas('fh_employee', function ($q) use ($branchFilter) {
                $q->where('emp_br_id', $branchFilter);
            });
        }
        // Add other filters similarly...

        $lateCount = $lateQuery->count();

        // Leave count
        $leaveQuery = LeaveRequest::where('lvr_b_id', $businessId)
            ->where('lvr_status', 140) // Approved
            ->whereDate('lvr_start_date', '<=', $dateFilter)
            ->whereDate('lvr_end_date', '>=', $dateFilter);

        if ($branchFilter) {
            $leaveQuery->whereHas('fh_employee', function ($q) use ($branchFilter) {
                $q->where('emp_br_id', $branchFilter);
            });
        }
        // Add other filters similarly...

        $leaveCount = $leaveQuery->count();

        // Absent count = Total employees - Present - On Leave
        $absentCount = $totalEmployees - $presentCount - $leaveCount;

        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['emp_b_id', $businessId]
                ],
                [
                    'method' => 'whereNot',
                    'args' => ['emp_role_id', 1]
                ],
                [
                    'method' => 'whereDate',
                    'args' => ['emp_date_of_joining', '<=', $dateFilter]
                ],
                // Compound condition: last working date null OR >= date
                ['method' => 'whereRaw', 'args' => ["(emp_last_working_date IS NULL OR emp_last_working_date >= ?)", [$dateFilter]]],
                [
                    'method' => 'select',
                    'args' => ['emp_id', 'emp_fname', 'emp_mname', 'emp_lname', 'emp_code', 'emp_br_id', 'emp_dg_id', 'emp_d_id', 'emp_status', 'emp_profile_photo', 'emp_date_of_joining', 'emp_last_working_date'],
                    'relation' => [
                        'fh_branch:br_id,br_name',
                        'fh_designation:dg_id,dg_name',
                        'fh_department:d_id,d_name',
                        'fh_employee_status:m_id,m_name'
                    ],
                ],
                [
                    'method' => 'sortBy',
                    'args' => ['emp_id', 'emp_full_name', 'emp_code', 'emp_checkin_method_id', 'emp_status', 'emp_br_id', 'emp_d_id', 'emp_date_of_joining']
                ]
            ];

            // Apply filters
            if ($branchFilter != '') {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_br_id', $branchFilter]];
            }

            if ($designationFilter != '') {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_dg_id', $designationFilter]];
            }

            if ($departmentFilter != '') {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_d_id', $departmentFilter]];
            }

            if ($statusFilter != '') {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_status', $statusFilter]];
            }

            $searchColumns = ['emp_fname', 'emp_mname', 'emp_lname', 'emp_code'];
            $searchRelationships = [
                'fh_branch' => ['br_name'],
                'fh_designation' => ['dg_name'],
                'fh_department' => ['d_name']
            ];

            $employees = (new DynamicModelDataTableHelper(
                eloquentModel: new Employee(),
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
                searchRelationships: $searchRelationships
            ))->getServerSideDataTable();

            $rowData = [];
            $i = 1;

            foreach ($employees as $employee) {
                // Get attendance record for the selected date with relationships
                $attendance = AttendanceRecord::with([
                    'fh_approval_status',
                    'fh_plan_approval_log' => function ($query) {
                        $query->with(['fh_employee', 'fh_role', 'fh_status']);
                    },
                    'fh_approval_log2' => function ($query) {
                        $query->with(['fh_employee', 'fh_role', 'fh_status']);
                    },
                    'fh_policy_shift_timing',
                    'fh_attendance_checkin_type'
                ])
                    ->where('atd_emp_id', $employee->emp_id)
                    ->whereDate('atd_date', $dateFilter)
                    ->first();

                // Get Attendance Log record for the selected date
                // $atd_log = AttendanceLog::where('al_emp_id', $employee->emp_id)->whereDate('al_date', $dateFilter)->first();
                $atd_log = AttendanceLog::where('al_emp_id', $employee->emp_id)->whereDate('al_date', $dateFilter)->orderByDesc('created_at')->first();
                // Get Attendance Exception record for the selected date
                $exception = AttendanceException::where('ae_emp_id', $employee->emp_id)->whereDate('ae_date', $dateFilter)->where('ae_stage_completed', 1)->where('ae_status', '!=', 170)->first();

                // Get leave record for the selected date
                $leave = LeaveRequest::where('lvr_emp_id', $employee->emp_id)
                    ->whereDate('lvr_start_date', '<=', $dateFilter)
                    ->whereDate('lvr_end_date', '>=', $dateFilter)
                    ->where('lvr_stage_completed', 1)
                    ->where('lvr_status', '!=', 170)
                    ->first();

                $checkInTime = $checkoutTime = null;
                $workHrs = $late_duration = $early_duration = $overtime_duration = 0;
                $method = "";

                $status = $statusClass = $style = '';

                if ($leave) {
                    $status = $leave->fh_leave_cat_type->m_type;
                    $colorArr = json_decode($leave->fh_leave_cat_type->m_other);
                    $style = 'color: ' . $colorArr->color;
                } else if ($exception) {
                    $method = "Missed Punch";
                    $checkInTime = $exception->ae_in_time ?? optional($attendance)->atd_check_in_time;
                    $checkoutTime = $exception->ae_out_time ?? optional($attendance)->atd_check_out_time;
                    $workHrs = $exception->ae_total_working ? $exception->ae_total_working : abs(Carbon::parse($checkInTime)->diffInMinutes(Carbon::parse($checkoutTime)));

                    if ($attendance) {
                        $status = $attendance->fh_attendance_status->m_name;
                        $colorArr = json_decode($attendance->fh_attendance_status->m_other);
                        $style = 'color: ' . $colorArr->color;
                    } else {
                        $resolvedShift = ShiftResolver::resolveEmployeeShift($employee, $dateFilter);
                        $shift = $resolvedShift ?? $employee->fh_shift_type;
                        $shiftStartTime = $shift->pst_start_time;
                        $shiftEndTime = $shift->pst_end_time;
                        $graceMins = $shift->pst_allow_grace_time ? $shift->pst_grace_time : 0;
                        $shiftStartTime = $shiftStartTime->subMinutes($graceMins);
                        $dailyWorkingHours = $shiftStartTime->diffInMinutes($shiftEndTime);
                        $fullDayThreshold = $dailyWorkingHours;
                        $minWorkHrs = $shift->pst_min_work_hour ? $shiftStartTime->diffInMinutes(Carbon::parse($exception->ae_date->format('Y-m-d') . " " . $shift->pst_min_work_hour)) : 0;
                        $halfDayThreshold = $dailyWorkingHours / 2;

                        if ($workHrs >= $fullDayThreshold || $workHrs >= $minWorkHrs) {
                            $status = 251; // Present
                        } else if ($workHrs >= $halfDayThreshold) {
                            $status = 252; // Half Day
                        } else {
                            $status = 203; // Absent
                        }
                        
                        $late_duration = max($shiftStartTime->diffInMinutes(Carbon::parse($checkInTime)), 0);
                        $early_duration = max($checkoutTime->diffInMinutes(Carbon::parse($shiftEndTime)), 0);
                        $overtime_duration = max($shiftEndTime->diffInMinutes(Carbon::parse($checkoutTime)), 0);
                    }
                    
                    $approval_status = $exception->fh_approval_status;
                    $jsonData = $approvalStatus->m_other ?? '{}';
                    $decodedData = json_decode($jsonData, true);
                    $color = $decodedData['color'] ?? '';
                    $icon = $decodedData['web_icon'] ?? '';
                    $submitted_to = "--";
                } else if ($atd_log) {
                    $method = "Admin";
                    $status = $atd_log->fh_attendance_status->m_name;
                    $colorArr = json_decode($atd_log->fh_attendance_status->m_other);
                    $style = 'color: ' . $colorArr->color;

                    $checkInTime = $atd_log->al_check_in_time;
                    $checkoutTime = $atd_log->al_check_out_time;
                    $workHrs = $atd_log->al_total_worked_hours;

                    $late_duration = $atd_log->al_late_duration ?? 0;
                    $early_duration = $atd_log->al_early_exit_duration ?? 0;
                    $overtime_duration = $atd_log->al_overtime_hours ?? 0;

                    $approval_status = $atd_log->fh_approval_status;
                    $jsonData = $approvalStatus->m_other ?? '{}';
                    $decodedData = json_decode($jsonData, true);
                    $color = $decodedData['color'] ?? '';
                    $icon = $decodedData['web_icon'] ?? '';
                    $submitted_to = "--";
                } else if ($attendance) {
                    $method = optional($attendance)->fh_attendance_checkin_type ? $attendance->fh_attendance_checkin_type->m_name : '--';
                    $checkInTime = optional($attendance)->atd_check_in_time ? $attendance->atd_check_in_time : null;
                    $outTime = optional($attendance)->atd_check_out_time;
                    if (!empty($outTime) && Carbon::parse($outTime)->format('H:i') !== '00:00') {
                        $checkoutTime = $outTime;
                    } else {
                        $checkoutTime = null;
                    }

                    $status = $attendance->fh_attendance_status->m_name;
                    $colorArr = json_decode($attendance->fh_attendance_status->m_other);
                    $style = 'color: ' . $colorArr->color;
                    $workHrs = optional($attendance)->atd_total_worked_hours;

                    $late_duration = $attendance->atd_late_duration ?? 0;
                    $early_duration = $attendance->atd_early_exit_duration ?? 0;
                    $overtime_duration = $attendance->atd_overtime_hours ?? 0;

                    $approvalData = ApprovalHelper::getApprovalOrRejectionData($attendance->atd_id, $attendance->atd_request_status, $attendance->atd_am_id, NULL, 249);
                    if ($approvalData) {
                        $approvalBtnData['approval_status'] = $approvalData->fh_approver_status->m_id;
                        // $approvalBtnData['approval_name'] = $approvalData->fh_approver_status->m_name;
                        $approvalBtnData['approval_action_type'] = $approvalData->pa_type;
                        $approvalBtnData['approval_sequence'] = $approvalData->pa_sequence;
                        $approvalBtnData['module_id'] = $approvalData->pa_am_id;
                        $approvalBtnData['is_last_approval'] = $approvalData->pa_last;
                        $approvalBtnData['emp_d_id'] = optional($attendance->fh_employee)->emp_d_id;
                    } else {
                        // Check if the user employee mapping can approve start
                        $attendance->approvalData = $approvalData;
                        $approval = ApprovalHelper::getApprovalData($attendance, $this->user, 'atd_');
                        $masterApproveBtn = $approval['masterApproveBtn'];
                        $canApprove = $approval['canApprove'];
                        $moduleId['module_id'] = $attendance->atd_module_id;
                        // dd($approval, $canApprove, $masterApproveBtn);
                    }
                    
                    $approval = ApprovalHelper::checkApproval($user->emp_b_id, $attendance->atd_module_id, $attendance->atd_id, optional($attendance->fh_employee)->emp_d_id, $attendance->atd_emp_id);
                } else {
                    $status = 'Absent';
                    $statusClass = 'text-danger';
                }

                $profilePhoto = $employee->emp_profile_photo ?: asset('assets/imgs/user.png');

                $lateMessage = '';
                if ($late_duration > 0) {
                    $hours = intval($late_duration / 60);
                    $minutes = intval($late_duration % 60);
                    $parts = [];
                    if ($hours > 0) {
                        $parts[] = $hours . ' Hr';
                    }
                    if ($minutes > 0) {
                        $parts[] = $minutes . ' Min';
                    }
                    $lateMessage = '<p class="late-status fs-10 fw-bolder">Late By: ' . implode(' ', $parts) . '</p>';
                }
                $overMessage = '';
                if ($early_duration > 0) {
                    $hours = intval($early_duration / 60);
                    $minutes = intval($early_duration % 60);
                    $parts = [];
                    if ($hours > 0) {
                        $parts[] = $hours . ' Hr';
                    }
                    if ($minutes > 0) {
                        $parts[] = $minutes . ' Min';
                    }
                    $overMessage = '<p class="earlygoing-status fs-10 fw-bolder" style="color:#9C27B0;">EG By: ' . implode(' ', $parts) . '</p>';
                } elseif ($overtime_duration > 0) {
                    $totalMinutes = round($overtime_duration * 60); // Convert 0.18 hr to 11 min (rounded)
                    $hours2 = intval($totalMinutes / 60);
                    $minutes2 = intval($totalMinutes % 60);
                    $parts2 = [];

                    if ($hours2 > 0) {
                        $parts2[] = $hours2 . ' Hr';
                    }
                    if ($minutes2 > 0) {
                        $parts2[] = $minutes2 . ' Min';
                    }

                    if (!empty($parts2)) {
                        $overMessage = '<p class="overtime-status fs-10 fw-bolder">OT By: ' . implode(' ', $parts2) . '</p>';
                    }
                }

                if (!empty($workHrs) && $workHrs > 0) {
                    $hours = floor($workHrs);
                    $minutes = round(($workHrs - $hours) * 60);
                    $workHrs = sprintf('%02d:%02d', $hours, $minutes);
                } else {
                    $workHrs = '--';
                }

                $row = [];
                $row[] = $i++;
                $row[] = '<div class="d-flex">
                            <span class="avatar avatar-md brround me-3 rounded-circle" style="background-image: url(\'' . $profilePhoto . '\');"></span>
                            <div class="me-3 mt-0 mt-sm-1 d-block">
                                <h6 class="mb-1 fs-14">' . $employee->emp_fname . ' ' . $employee->emp_mname . ' ' . $employee->emp_lname . '</h6>
                                <p class="text-muted mb-0 fs-10">' . optional($employee->fh_designation)->dg_name . '</p>
                            </div>
                          </div>';
                $row[] = $employee->emp_code;
                $row[] = $method ?? '--';
                $row[] = '<span style="'. $style . '" class="' . $statusClass . '">' . $status . '</span>';
                $row[] = $checkInTime !== null ? Carbon::parse($checkInTime)->format('H:i') . ($lateMessage ? '<br>' . $lateMessage : '') : '--';
                $row[] = $checkoutTime !== null ? Carbon::parse($checkoutTime)->format('H:i') . ($overMessage ? '<br>' . $overMessage : '') : '--';
                $row[] = $workHrs;



                // Approval Status Column
                if ($attendance && $attendance->fh_approval_status) {
                    $approvalStatus = $attendance->fh_approval_status;
                    $jsonData = $approvalStatus->m_other ?? '{}';
                    $decodedData = json_decode($jsonData, true);
                    $color = $decodedData['color'] ?? '';
                    $icon = $decodedData['web_icon'] ?? '';

                    // Format approval log
                    $formattedLog = '';
                    $comma = false;

                    if ($attendance->fh_plan_approval_log->isNotEmpty()) {
                        foreach ($attendance->fh_plan_approval_log as $log) {
                            $employeeName = isset($log->fh_employee->emp_full_name) ? htmlspecialchars($log->fh_employee->emp_full_name, ENT_QUOTES, 'UTF-8') : 'N/A';
                            $roleName = isset($log->fh_role->role_name) ? htmlspecialchars($log->fh_role->role_name, ENT_QUOTES, 'UTF-8') : 'N/A';
                            $statusName = isset($log->fh_status->m_name) ? htmlspecialchars($log->fh_status->m_name, ENT_QUOTES, 'UTF-8') : 'N/A';
                            $formattedLog .= ($comma ? ', ' : '') . "$employeeName ($roleName) $statusName";
                            $comma = true;
                        }
                    } elseif ($attendance->fh_approval_log2->isNotEmpty()) {
                        foreach ($attendance->fh_approval_log2 as $log) {
                            $employeeName = isset($log->fh_employee->emp_full_name) ? htmlspecialchars($log->fh_employee->emp_full_name, ENT_QUOTES, 'UTF-8') : 'N/A';
                            $roleName = isset($log->fh_role->role_name) ? htmlspecialchars($log->fh_role->role_name, ENT_QUOTES, 'UTF-8') : 'N/A';
                            $statusName = isset($log->fh_status->m_name) ? htmlspecialchars($log->fh_status->m_name, ENT_QUOTES, 'UTF-8') : 'N/A';
                            $formattedLog .= ($comma ? ', ' : '') . "$employeeName ($roleName) $statusName";
                            $comma = true;
                        }
                    }

                    // $popoverContent = $formattedLog ?: ($attendance->atd_request_status == 171 ? 'Auto Approved' : 'Awaiting');
                    $popoverContent = $formattedLog === '' ?  ($attendance->atd_request_status == 171 ? 'Auto Approved' : 'Awaiting') : $formattedLog;
                    $safePopoverContent = htmlspecialchars($popoverContent, ENT_QUOTES, 'UTF-8');

                    $row[] = '<span class="badge" style="background-color:' . $color . '">
                                <i class="' . $icon . '">&nbsp;</i>' . (isset($approvalStatus->m_name) ? ($approvalStatus->m_name) : '') . '
                              </span>
                              &nbsp;<i class="feather feather-info text-primary fs-14"
                                data-bs-container="body"
                                data-bs-content="' . $safePopoverContent . '"
                                data-bs-placement="bottom"
                                data-bs-popover-color="default"
                                data-bs-toggle="popover"
                                title="Approval Log"></i>
                                <p class="text-muted mb-0 fs-12">Approval ' . ($approval['approvallog_count'] ?? ' ') . ' (' . ($approval['approvalcount'] ?? ' ') . ')</p>';
                } else if (!empty($leave)) {
                    $logData = $leave->fh_plan_approval_log;
                    $formattedLog = '';
                    $comma = false;
                    if ($logData && count($logData)) {
                        foreach ($logData as $log) {
                            if ($log->log_am_id == $leave->lvr_am_id) {
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
                        $logData2 = $leave->fh_approval_log2;
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
                    $popoverContent = $formattedLog === '' ?  ($leave->lvr_status == 171 ? 'Auto Approved' : 'Awaiting') : $formattedLog;

                    // Ensure the content is properly escaped for use in data attributes
                    $safePopoverContent = htmlspecialchars($popoverContent, ENT_QUOTES, 'UTF-8');
                    $jsonData = optional($leave->fh_approval_status)->m_other;
                    // Decode the JSON to an associative array
                    $is_auto_approval = false;
                    $decodedData = json_decode($jsonData, true); // true for associative array
                    // Now access the color value
                    $color = $decodedData['color'] ?? '';
                    $icon = $decodedData['web_icon'] ?? '';
                    $row[] = '<span class="badge" style="background-color:' . $color . '"><i class="' . $icon . '">&nbsp;</i>' . (isset($leave->fh_approval_status->m_name) ? ($leave->fh_approval_status->m_name) : '') . '</span> &nbsp;<i class="feather feather-info text-primary fs-14"
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
                        $approvalMapping = ApprovalHelper::getApprovalMapping($user->emp_b_id, $leave->lvr_emp_id, $leave->lvr_module_id);

                        if ($approvalMapping) {
                            $approvalArray = ApprovalHelper::getApprovalArray($approvalMapping);
                            $approvalLog = $leave?->fh_approval_log2?->pluck('log_user_id')?->toArray() ?? [];
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
                } else {
                    $row[] = '--';
                }

                // Submitted To Column
                if ($attendance) {
                    $nextApproval = ApprovalHelper::getNextApprovalDetails(249, $attendance->atd_id, $attendance->atd_b_id);
                    if (!empty($nextApproval['approver_name'])) {
                        $approverName = $nextApproval['approver_name'];
                    } else {
                        $approvalMapping = ApprovalHelper::getApprovalMapping($user->emp_b_id, $attendance->atd_emp_id, $attendance->atd_module_id);

                        if ($approvalMapping && $attendance->atd_stage_completed == 0) {
                            $approvalArray = ApprovalHelper::getApprovalArray($approvalMapping);
                            $approvalLog = $attendance->fh_approval_log2->pluck('log_user_id')->toArray() ?? [];
                            $diff = array_values(array_diff($approvalArray, $approvalLog));
                            $approverName = $diff ? Employee::where('emp_id', $diff[0])->value('emp_full_name') : '---';
                        } else {
                            $approverName = '---';
                        }
                    }
                    $row[] = '<span class="text-center">' . $approverName . '</span>';
                } else {
                    $row[] = '--';
                }

                $viewLink = ($attendance && !empty($attendance->atd_id))
                    ? '<a class="" href="' . url('admin/attendance/daily-attendance/' . md5($attendance->atd_id)) . '">
                            <i class="feather-eye fs-6" style="margin-top: 10px;"></i>
                      </a>'
                    : '<a class="" href="javascript:void(0)">
                            <i class="feather-eye fs-6" style="margin-top: 10px;"></i>
                      </a>';

                // Action Column (always visible)
                $action = '<div class="d-flex">'
                    . $viewLink;

                if ($canApprove || !empty($approvalData)) {
                    $action .= '<label class="custom-control custom-checkbox-md p-0 ms-2">
                                <input type="checkbox" name="employee[]" value="'
                    . $employee->emp_id . '|'
                    . $dateFilter . '|'
                    . ($attendance->fh_policy_shift_timing->pst_start_time ?? '') . '|'
                    . ($attendance->fh_policy_shift_timing->pst_end_time ?? '') . '"
                                    class="custom-control-input-success select-checkbox employee-checkbox"
                                    data-id="' . md5($attendance->atd_id ?? '') . '" onclick="selectCheckbox(this)">
                                <span class="custom-control-label-md success"></span>
                            </label>
                          </div>';
                } else {
                    $action .= '</div>';
                }
                
                $row[] = $action;

                $rowData[] = $row;
            }
            $output = [
                "draw" => intval($request->input('draw')),
                "recordsTotal" => sizeof($employees),
                "recordsFiltered" => (new DynamicModelDataTableHelper(
                    eloquentModel: new Employee(),
                    dynamicConditions: $dynamicConditions
                ))->countFilteredServerSideDataTable(),
                "data" => $rowData,
                "totalEmployees" => $totalEmployees,
                "presentCount"   => $presentCount,
                "absentCount"    => $absentCount,
                "halfDayCount"   => $halfDayCount,
                "lateCount"      => $lateCount,
                "leaveCount"     => $leaveCount,
                "apHiBtn" => $approvalBtnData,
                "apEmpBtn" => $masterApproveBtn,
                "canApprove" => $canApprove,
                "moduleId" => $moduleId
            ];

            return json_encode($output);
        }

        $columns = [
            'S.No.',
            'Employee Name',
            'Emp. Code',
            'Method',
            'Status',
            'Check In',
            'Check Out',
            'Working Hours',
            'Approval Status',
            'Submitted To',
            'Action'
        ];

        return view('admin.setting.attendance-details.daily-attendence', compact(
            'columns',
            'totalEmployees',
            'presentCount',
            'absentCount',
            'halfDayCount',
            'lateCount',
            'leaveCount',
            'approvalBtnData'
        ));
    }

    public function bulkAttendance(Request $request)
    {
        // dd($request->all()); // Debugging: See if data is coming correctly
        try {
            // ✅ Convert comma-separated IDs into an array
            $employeeIds = explode(',', $request->id);

            foreach ($employeeIds as $key => $id) {
                $request->merge(['id' => $id]);
                CentralLogics::processAttendance($request);
            }

            return redirect()->back()->with('success', 'Bulk attendance recorded successfully.');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = AttendanceRecord::with(
            'fh_employee:emp_id,emp_fname,emp_mname,emp_lname,emp_dg_id,emp_d_id,emp_phone,emp_code,emp_profile_photo,emp_full_name,emp_email,emp_status,emp_br_id',
            'fh_employee.fh_department:d_id,d_name',
            'fh_employee.fh_designation:dg_id,dg_name',
            'fh_employee.fh_branch:br_id,br_name',
            // 'fh_approval_status:m_id,m_name,m_other',
            // 'fh_module:m_id,m_name,m_other',
            // 'fh_approval_log2',
        )->where(DB::raw('md5(atd_id)'), $id)->first();

        if ($data->atd_attendance_status == 319 || $data->atd_attendance_status == 320) {
            $workHrs = $data->atd_check_in_time && $data->atd_check_out_time ? (Carbon::parse($data->atd_check_in_time)->diffInHours(Carbon::parse($data->atd_check_out_time))) : 0;
            $compOffPolicy = CompOffPolicy::with("duration_conditions")
                ->where('cop_b_id', $data->atd_b_id)
                ->where('cop_effective_date', '<=', $data->atd_date)
                ->where('cop_status', 1)
                ->orderBy('cop_effective_date', 'desc')
            ->first();
            $co_quantity = CentralLogics::getCompOffQuantity($workHrs, $compOffPolicy->duration_conditions);
        }
        $data->co_quantity = $co_quantity ?? 0;

        // Get approval data
        $approvalData = ApprovalHelper::getApprovalOrRejectionData($data->atd_id, $data->atd_request_status, $data->atd_am_id, NULL, 249);
        // Check if the user employee mapping can approve start
        $data->approvalData = $approvalData;
        $approval = ApprovalHelper::getApprovalData($data, $this->user, 'atd_');
        $masterApproveBtn = $approval['masterApproveBtn'];
        $canApprove = $approval['canApprove'];
        // Check if the user employee mapping can approve end
        return view('admin.setting.attendance-details.daily-attendence-show', compact('data', 'approvalData', 'canApprove', 'masterApproveBtn'));
    }

    public function import(Request $request)
    {
        // dd($request);
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,csv',
        ]);

        $file = $request->file('import_file');
        $import = new AttendanceImport();

        try {
            // Attempt the import
            Excel::import($import, $file);
            
            $errors = $import->getErrorMessages();
            $summary = $import->getSummary();

            if (!empty($errors)) {
                session()->put('import_errors', $errors);
                session()->flash('import_errors_blade', $errors);
                return redirect()->back()->with('error', 'Import failed! Please check the errors.');
            }
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', "Import completed successfully! Imported: {$summary['success']}, Skipped: {$summary['skipped']}");
    }

    public function approve(Request $request)
    {
        $data = AttendanceRecord::findOrFail($request->log_request_id);

        // Process approval using the service
        $response = ApprovalHelper::processApproval($request, $data, $this->user, 'atd_');

        return response()->json($response, $response['status'] === 'success' ? 200 : 500);
    }

    public function faceAttendance()
    {

        $user = Auth::user();
        $activeEmployeeCount = Employee::select('emp_id', 'emp_code', 'emp_full_name', 'emp_email', 'emp_b_id', 'emp_pwo_id', 'emp_date_of_joining')
            ->where('emp_b_id', $user->emp_b_id)
            ->where('emp_role_id', '<>', 1)
            ->where('emp_status', 71)->count(); // Active employees only


        $attendanceQuery = AttendanceRecord::with([
            'fh_employee' => function ($query) {
                $query->select('emp_id', 'emp_full_name', 'emp_code');
            }
        ])
            ->where('atd_b_id', $user->emp_b_id)
            ->where('atd_date', now()->format('Y-m-d'))
            ->select('atd_id', 'atd_emp_id', 'atd_date', 'atd_check_in_time', 'atd_check_out_time')
            ->orderBy('updated_at', 'desc');

        $totalToDayAttendance = $attendanceQuery->count();

        $attendanceData = $attendanceQuery->limit(5)->get();

        return view('admin.setting.attendance-details.face-attendance', compact('user', 'activeEmployeeCount', 'totalToDayAttendance', 'attendanceData'));
    }
}
