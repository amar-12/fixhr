<?php

namespace App\Http\Controllers\Web\Admin\AttendanceDetails;

use App\Helpers\CentralLogics;
use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\MasterTable;
use App\Models\PolicyHolidayList;
use App\Models\PolicyWeekOff;
use App\Models\PolicyShiftTiming;
use App\Models\AttendanceLog;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use App\Http\Controllers\Api\Attendance\PunchInApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MonthlyAttendanceSampleExport;
use App\Exports\MonthlyAttendanceStatusExport;
use App\Helpers\ApprovalHelper;
use App\Imports\MonthlyAttendanceImport;
use App\Models\CompOff;
use App\Models\PayrollPeriod;
use App\Models\RuleCriterion;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MonthlyAttendanceController extends Controller
{

    public function index(Request $request)
    {
        $user = Auth::user();

        // Add error handling for missing user
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $weekOff = PolicyWeekOff::where("pwo_b_id", $user->emp_b_id)->first();
        $branchList = Branch::where('br_b_id', $user->emp_b_id)->get();
        $departmentList = Department::where('d_b_id', $user->emp_b_id)->get();
        $designationList = Designation::where('dg_b_id', $user->emp_b_id)->get();
        $data = Employee::where('emp_b_id', 10)->limit(10)->get();
        $attendanceStatus = MasterTable::where('m_group', 'ATTENDANCE_STATUS')->get();
        $leaveStatuses = MasterTable::where('m_group', 'LEAVE_CATEGORY')->get();

        // Date handling - FIXED: Ensure proper month handling
        if (request()->input('mt_monthFilter')) {
            $monthFilter = request()->input('mt_monthFilter');
            $year = Carbon::parse($monthFilter)->format('Y');
            $month = Carbon::parse($monthFilter)->format('m');
            $totalDays = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        } else {
            $year = now()->format('Y');
            $month = now()->format('m');
            $monthFilter = now()->format('Y-m');
            $totalDays = now()->daysInMonth;
        }

        $branchFilter = request()->input('mt_branchFilter');
        $departmentFilter = request()->input('mt_departmentFilter');
        $designationFilter = request()->input('mt_designationFilter');
        $statusFilter = "";
        $emp_statusFilter = request()->input('mt_activeFilter');

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Pre-load holidays for better performance
        $holiday_record_exits = PolicyHolidayList::where('phl_b_id', $user->emp_b_id)->where('phl_day_type_id', 201)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('phl_start_date', [$startDate, $endDate])
                    ->orWhereBetween('phl_end_date', [$startDate, $endDate]);
            })->get();

        $holidaysByDate = collect();
        foreach ($holiday_record_exits as $holiday) {
            $start = Carbon::parse($holiday->phl_start_date);
            $end = Carbon::parse($holiday->phl_end_date);

            while ($start->lte($end)) {
                $holidaysByDate->put($start->toDateString(), $holiday);
                $start->addDay();
            }
        }

        if ($request->ajax()) {
            try {
                // Set memory limit and time limit
                ini_set('memory_limit', '512M');
                set_time_limit(300);

                // Make strings once (safe for bindings)
                $startDateStr = $startDate->format('Y-m-d');
                $endDateStr   = $endDate->format('Y-m-d');

                $dynamicConditions = [
                    // Tenant/branch
                    ['method' => 'where',     'args' => ['emp_b_id', $user->emp_b_id]],

                    // Must have joined on/before this month's end
                    ['method' => 'whereDate', 'args' => ['emp_date_of_joining', '<=', $endDateStr]],

                    // And must be active in this month: (last_working IS NULL OR last_working >= start_of_month)
                    // Use COALESCE to turn NULL into a far-future date so we can keep a single AND condition
                    ['method' => 'whereRaw',  'args' => ['COALESCE(emp_last_working_date, "9999-12-31") >= ?', [$startDateStr]]],

                    // Exclude super-admin role
                    ['method' => 'whereNot',  'args' => ['emp_role_id', 1]],

                    // Your selects + relations
                    [
                        'method'   => 'select',
                        'args'     => [
                            'emp_id',
                            'emp_b_id',
                            'emp_fname',
                            'emp_mname',
                            'emp_lname',
                            'emp_full_name',
                            'emp_code',
                            'emp_role_id',
                            'emp_email',
                            'emp_type_id',
                            'emp_br_id',
                            'emp_d_id',
                            'emp_dg_id',
                            'emp_dob',
                            'emp_date_of_joining',
                            'emp_phone',
                            'emp_work_mode_id',
                            'emp_shift_type_id',
                            'emp_status',
                            'emp_grade_id',
                            'emp_gender_id',
                            'emp_last_working_date',
                            'emp_marital_status_id',
                            'emp_profile_photo',
                            'emp_ap_id',
                            'created_at',
                            'emp_pwo_id'
                        ],
                        'relation' => [
                            'fh_employee_type:m_id,m_name',
                            'fh_branch:br_id,br_name',
                            'fh_gender:m_id,m_name',
                            'fh_designation:dg_id,dg_name',
                            'fh_department:d_id,d_name,d_pst_id',
                            'fh_work_mode:m_id,m_name',
                            'fh_shift_type:pst_id,pst_name',
                            'fh_employee_status:m_description,m_name',
                            'fh_attendance_policy:ap_id,ap_mark_absent_check',
                            'attendance_record:atd_id,atd_emp_id,atd_date,atd_attendance_status,atd_total_worked_hours,atd_is_early_exit,atd_is_late',
                            'leave_requests:lvr_id,lvr_emp_id,lvr_leave_day_type_id',
                            'fh_week_off_policy:pwo_id,pwo_name,pwo_recurrence_day_ids'
                        ],
                    ],

                    ['method' => 'orderBy', 'args' => ['emp_id', 'asc']],
                ];

                // Filter conditions
                if ($branchFilter != '') {
                    $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_br_id', $branchFilter]];
                }

                if ($departmentFilter != '') {
                    $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_d_id', $departmentFilter]];
                }

                if ($designationFilter != '') {
                    $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_dg_id', $designationFilter]];
                }

                if ($emp_statusFilter != '') {
                    $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_status', $emp_statusFilter]];
                }

                if ($statusFilter != '') {
                    if (in_array($statusFilter, ['251', '252'])) {
                        $dynamicConditions[] = [
                            'method' => 'whereRelation',
                            'parentMethod' => 'whereHas',
                            'childMethod' => 'where',
                            'args' => ['atd_attendance_status', $statusFilter],
                            'relation' => 'attendance_record'
                        ];
                    } elseif ($statusFilter == '1') {
                        $dynamicConditions[] = [
                            'method' => 'whereRelation',
                            'parentMethod' => 'whereHas',
                            'childMethod' => 'where',
                            'args' => ['atd_is_late', 1],
                            'relation' => 'attendance_record'
                        ];
                    } elseif ($statusFilter == '2') {
                        $dynamicConditions[] = [
                            'method' => 'whereRelation',
                            'parentMethod' => 'whereHas',
                            'childMethod' => 'where',
                            'args' => ['atd_is_early_exit', 1],
                            'relation' => 'attendance_record'
                        ];
                    }
                }

                // Define search value, columns, and relationships
                $searchColumns = [
                    'emp_id',
                    'emp_code',
                    'emp_fname',
                    'emp_mname',
                    'emp_lname',
                    'emp_full_name',
                    'emp_email',
                    'emp_phone',
                    'created_at',
                    'updated_at',
                ];
                $searchRelationships = [
                    'fh_employee_type' => ['m_name'],
                    'fh_branch' => ['br_name'],
                    'fh_department' => ['d_name'],
                    'fh_work_mode' => ['m_name'],
                    'fh_shift_type' => ['pst_name'],
                    'fh_employee_status' => ['m_name'],
                ];

                $list = (new DynamicModelDataTableHelper(
                    eloquentModel: new Employee(),
                    dynamicConditions: $dynamicConditions,
                    searchColumns: $searchColumns,
                    searchRelationships: $searchRelationships
                ))->getServerSideDataTable();

                // Better validation
                if (empty($list)) {
                    return response()->json([
                        "draw" => intval($request->input('draw', 0)),
                        "recordsTotal" => 0,
                        "recordsFiltered" => 0,
                        "data" => [],
                        "error" => null
                    ]);
                }

                // Pre-load all shift details to avoid repeated queries
                $allShiftIds = collect($list)->pluck('emp_shift_type_id')->unique()->filter();
                $shiftDetails = PolicyShiftTiming::whereIn('pst_id', $allShiftIds)->get()->keyBy('pst_id');

                $rowData = [];
                $i = 1;
                $processedCount = 0;
                $errorCount = 0;

                // Process employees in chunks to prevent memory issues
                foreach ($list as $key => $val) {
                    try {
                        // Add processing limit to prevent timeout - increased limit
                        if ($processedCount >= 200) {
                            \Log::warning('Processing limit reached, stopping after 200 employees');
                            break;
                        }

                        // Validate employee data
                        if (!isset($val->emp_id) || !isset($val->emp_full_name)) {
                            $errorCount++;
                            continue;
                        }

                        $weekOfDates = [];
                        if (isset($val->fh_week_off_policy) && $val->fh_week_off_policy) {
                            $occurrences = $this->getOccurrencesOfDaysInMonth($year, $month);
                            foreach ($occurrences as $key => $dates) {
                                $weekDays = $val->fh_week_off_policy->getWeek(json_decode($val->fh_week_off_policy->pwo_recurrence_day_ids));
                                if ($weekDays && isset($weekDays[$key])) {
                                    foreach ($weekDays[$key] as $k => $wd) {
                                        $nthDay = (int) filter_var($wd, FILTER_SANITIZE_NUMBER_INT);
                                        if (isset($dates[$nthDay - 1]) && $dates[$nthDay - 1]) {
                                            $weekOfDates[] = $dates[$nthDay - 1];
                                        }
                                    }
                                }
                            }
                        }

                        // Get attendance data with timeout protection
                        $attendanceData = [];
                        try {
                            $attendanceData = CentralLogics::newGetMonthlyAttendanceDetails($val, $month, $year, $holidaysByDate, $weekOfDates);
                        } catch (Exception $e) {
                            \Log::warning('Attendance data fetch failed for employee: ' . $val->emp_id, [
                                'error' => $e->getMessage()
                            ]);
                            $attendanceData = [];
                        }

                        // Ensure attendanceData is array and create indexed version for faster lookup
                        if (!is_array($attendanceData)) {
                            $attendanceData = [];
                        }

                        // Create indexed array for O(1) lookup
                        $attendanceByDate = [];
                        foreach ($attendanceData as $attendance) {
                            if (isset($attendance['date'])) {
                                $dateKey = Carbon::parse($attendance['date'])->format('Y-m-d');
                                $attendanceByDate[$dateKey] = $attendance;
                            }
                        }

                        // Get shift details
                        $emp_shift_id = $val->emp_shift_type_id;
                        $shift_details = $shiftDetails->get($emp_shift_id);

                        $row = [];
                        $row[] = $i++;

                        $profilePhoto = isset($val->emp_profile_photo) && !empty($val->emp_profile_photo)
                            ? $val->emp_profile_photo
                            : asset('assets/imgs/user.png');
                        $designationName = isset($val->fh_designation) && $val->fh_designation ? $val->fh_designation->dg_name : 'N/A';

                        $row[] = '<div class="d-flex"><span class="avatar avatar-md brround me-3 rounded-circle" style="background-image: url(\'' . $profilePhoto . '\');"></span>
                                  <div class="me-3 mt-0 mt-sm-1 d-block">
                                      <h6 class="mb-1 fs-14">' . htmlspecialchars($val->emp_full_name) . '</h6>
                                      <p class="text-muted mb-0 fs-10">' . htmlspecialchars($designationName) . '</p>
                                  </div></div>';
                        $row[] = '<div class="d-flex"><div><h6>' . htmlspecialchars($val->emp_code) . '</h6></div></div>';

                        $joiningDate = $val->emp_date_of_joining ? Carbon::parse($val->emp_date_of_joining) : null;
                        $lastWorkingDate = $val->emp_last_working_date ? Carbon::parse($val->emp_last_working_date) : null;

                        for ($day = 1; $day <= $totalDays; $day++) {
                            $currentDate = Carbon::createFromDate($year, $month, $day);
                            $dateKey = $currentDate->format('Y-m-d');

                            // Skip days before joining date if joining date exists
                            if ($joiningDate && $currentDate->lt($joiningDate)) {
                                $row[] = "<span>--</span>";
                                continue;
                            }

                            // Skip days after last working date
                            if ($lastWorkingDate && $currentDate->gt($lastWorkingDate)) {
                                $row[] = "<span>--</span>";
                                continue;
                            }

                            // Use indexed lookup for better performance
                            $dayAttendance = $attendanceByDate[$dateKey] ?? null;

                            if (!$dayAttendance) {
                                $row[] = "<span class='status-cell'>--</span>";
                                continue;
                            }

                            // Process attendance data with null checks and proper time formatting
                            $status = $dayAttendance['status_code'] ?? '--';
                            $tooltip = $dayAttendance['status'] ?? 'No Data';
                            $status_id = $dayAttendance['status_id'] ?? '--';

                            // Fix for in/out time - show -- if empty or 12:00 AM
                            $checkInTime = $dayAttendance['checkInTime'] ?? '--';
                            $checkOutTime = $dayAttendance['checkOutTime'] ?? '--';

                            // Handle default 12:00 AM case
                            if ($checkInTime === '12:00 AM' || $checkInTime === '00:00:00' || empty($checkInTime)) {
                                $checkInTime = '--';
                            }
                            if ($checkOutTime === '12:00 AM' || $checkOutTime === '00:00:00' || empty($checkOutTime)) {
                                $checkOutTime = '--';
                            }

                            $date = $dayAttendance['date'] ?? '--';
                            $statusColor = $dayAttendance['statusColor'] ?? '#000';
                            $workingHourRaw = $dayAttendance['workingHour'] ?? 0;
                            $earlyExitRaw = $dayAttendance['earlyExit'] ?? 0;

                            $shift_name = $shift_details->pst_name ?? '--';
                            $shift_start_time = $shift_details->pst_start_time ?? '--';
                            $shift_end_time = $shift_details->pst_end_time ?? '--';

                            // Format working hours
                            $workingHourDecimal = is_numeric($workingHourRaw) ? (float)$workingHourRaw : 0;
                            $earlyExitDecimal = is_numeric($earlyExitRaw) ? (float)$earlyExitRaw : 0;

                            $formatDecimalToHrMin = function ($decimalHours) {
                                $totalMinutes = round($decimalHours * 60);
                                $hours = floor($totalMinutes / 60);
                                $minutes = $totalMinutes % 60;
                                return sprintf('%02d:%02d', $hours, $minutes);
                            };

                            $workingHour = $formatDecimalToHrMin($workingHourDecimal);
                            $earlyExit = $formatDecimalToHrMin($earlyExitDecimal);
                            $resolverData = CentralLogics::getResolverDataMultiple($val, $date);
                            $resolverDataJson = htmlspecialchars(json_encode($resolverData));

                            $row[] = "<span style='color:" . htmlspecialchars($statusColor) . "' class='status-cell status-" . htmlspecialchars($status) . "'
                                data-date='" . htmlspecialchars($date) . "'
                                data-dept-shifts='" . $resolverDataJson . "'
                                data-employee='" . htmlspecialchars($val->emp_full_name) . "'
                                data-employee-id='" . htmlspecialchars($val->emp_id) . "'
                                data-employee-code='" . htmlspecialchars($val->emp_code) . "'
                                data-status-id='" . htmlspecialchars($status_id) . "'
                                data-intime='" . htmlspecialchars($checkInTime) . "'
                                data-outtime='" . htmlspecialchars($checkOutTime) . "'
                                data-status-color='" . htmlspecialchars($statusColor) . "'
                                data-shift-name='" . htmlspecialchars($shift_name) . "'
                                data-shift-start-time='" . htmlspecialchars($shift_start_time) . "'
                                data-shift-end-time='" . htmlspecialchars($shift_end_time) . "'
                                data-working-hour='" . htmlspecialchars($workingHour) . "'
                                data-early-exist='" . htmlspecialchars($earlyExit) . "'
                                data-status='" . htmlspecialchars($tooltip) . "'>
                                " . htmlspecialchars($status) . "
                            </span>";
                        }

                        $rowData[] = $row;
                        $processedCount++;
                    } catch (Exception $e) {
                        $errorCount++;
                        \Log::error('Error processing employee attendance: ' . $e->getMessage(), [
                            'employee_id' => $val->emp_id ?? 'unknown',
                            'line' => $e->getLine(),
                            'file' => $e->getFile()
                        ]);
                        // Don't break the loop, continue with next employee
                        continue;
                    }
                }

                $totalRecords = (new DynamicModelDataTableHelper(
                    eloquentModel: new Employee(),
                    dynamicConditions: $dynamicConditions,
                    searchColumns: $searchColumns,
                    searchRelationships: $searchRelationships
                ))->countFilteredServerSideDataTable();

                $output = [
                    "draw" => intval($request->input('draw', 0)),
                    "recordsTotal" => $totalRecords,
                    "recordsFiltered" => $totalRecords,
                    "data" => $rowData,
                    "processed" => $processedCount,
                    "errors" => $errorCount,
                    // Add month info to help frontend
                    "monthInfo" => [
                        "year" => $year,
                        "month" => $month,
                        "totalDays" => $totalDays
                    ]
                ];

                return response()->json($output);
            } catch (Exception $e) {
                \Log::error('DataTable Ajax Error: ' . $e->getMessage(), [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);

                return response()->json([
                    "draw" => intval($request->input('draw', 0)),
                    "recordsTotal" => 0,
                    "recordsFiltered" => 0,
                    "data" => [],
                    "error" => "Data processing failed. Please try again or contact support."
                ], 500);
            }
        }

        // Generate columns for view with day names - FIXED: Use the correct totalDays
        $columns = ['S. No.', 'Emp. Name', 'Emp. Code'];
        for ($day = 1; $day <= $totalDays; $day++) {
            $currentDate = Carbon::createFromDate($year, $month, $day);
            $dayName = $currentDate->format('D'); // Mon, Tue, Wed, etc.
            $columns[] = $day . '<br><small>' . $dayName . '</small>';
        }

        return view('admin.setting.attendance-details.monthly-attendance', compact(
            'branchList',
            'departmentList',
            'designationList',
            'data',
            'attendanceStatus',
            'totalDays',
            'columns',
            'leaveStatuses',
            'monthFilter',
            'branchFilter',
            'departmentFilter',
            'designationFilter',
            'statusFilter'  // Add this line
        ));
    }

    public function monthlyAttendanceSampleExport()
    {
        return Excel::download(new MonthlyAttendanceSampleExport, 'monthly_attendance_upload_format.xlsx');
    }

    public function monthlyAttendanceStatusExport(Request $request)
    {
        $user = Auth::user();
        $attendanceStatus = $request->input('attendanceStatus');
        $status = MasterTable::where('m_id', $attendanceStatus)->value('m_name');
        $branch = $request->input('branch');
        $department = $request->input('department');
        $designation = $request->input('designation');
        $month_filter = $request->input('month');
        $year = Carbon::parse($month_filter)->format('Y');
        $month = Carbon::parse($month_filter)->format('m');
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Fetch filtered data based on these parameters
        $query = Employee::query();

        $query->where('emp_b_id', $user->emp_b_id)->where('emp_status', 71)->where('emp_role_id', '!=', 1);

        if ($branch) {
            $query->where('emp_br_id', $branch);
        }
        if ($department) {
            $query->where('emp_d_id', $department);
        }
        if ($designation) {
            $query->where('emp_dg_id', $designation);
        }

        $emps = $query->get();

        // Prepare data for export (array of arrays)
        $exportData = [];
        $i = 1;

        foreach ($emps as $employee) {

            $weekOfDates = CentralLogics::getWeekOffDatesReport($employee, $year, $month);

            // Get holidays
            $holiday_record_exits = PolicyHolidayList::where('phl_b_id', $employee->emp_b_id)->where('phl_day_type_id', 201)->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('phl_start_date', [$startDate, $endDate])
                    ->orWhereBetween('phl_end_date', [$startDate, $endDate]);
            })->get();

            $holidaysByDate = collect();
            foreach ($holiday_record_exits as $holiday) {
                $start = Carbon::parse($holiday->phl_start_date);
                $end = Carbon::parse($holiday->phl_end_date);

                while ($start->lte($end)) {
                    $holidaysByDate->put($start->toDateString(), $holiday);
                    $start->addDay();
                }
            }

            $attendanceData = CentralLogics::newGetMonthlyAttendanceDetails($employee, $month, $year, $holidaysByDate, $weekOfDates);

            foreach ($attendanceData as $record) {
                // If specific attendance status is selected, filter records
                if ($attendanceStatus && $record["status_id"] != $attendanceStatus) {
                    continue; // Skip non-matching status
                }
                if ($employee->emp_date_of_joining && Carbon::parse($record["date"])->lt(Carbon::parse($employee->emp_date_of_joining))) {
                    continue; // Skip records before employee joining date
                }
                if ($employee->emp_last_working_date && Carbon::parse($record["date"])->gt(Carbon::parse($employee->emp_last_working_date))) {
                    continue; // Skip records after employee last working date
                }
                $exportData[] = [
                    $i++,
                    $employee->emp_code ?? '',
                    $employee->emp_full_name ?? '',
                    \Carbon\Carbon::parse($record["date"])->format('d/m/Y'),
                    \Carbon\Carbon::parse($record["date"])->format('d/m/Y'),
                    $record["checkInTime"] != "" && $record["checkInTime"] != "-" ? \Carbon\Carbon::parse($record["checkInTime"])->format('H:i:s') : '',
                    $record["checkOutTime"] != "" && $record["checkOutTime"] != "-" ? \Carbon\Carbon::parse($record["checkOutTime"])->format('H:i:s') : '',
                    $record["attendance_remark"],
                    $status ?? '',
                ];
            }
        }

        // Use a custom export class or closure
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\MonthlyAttendanceStatusExport($exportData),
            'monthly_attendance_status_data.xlsx'
        );
    }

    public function monthlyAttendanceImport(Request $request)
    {
        // $user = Auth::user();

        // // SYSTEM (EXE) CHECK
        // $employee = Employee::where('emp_id', $user->emp_id)
        //     ->whereNotNull('emp_sys_uuid')
        //     ->first();

        // if (!$employee) {
        //     return response()->json([
        //         'status' => false,
        //         'type' => 'system_missing',
        //         'message' => 'System app not running'
        //     ]);
        // }

        // $latitude = $request->latitude;
        // $longitude = $request->longitude;

        // if (!$latitude || !$longitude) {
        //     return response()->json([
        //         'status' => false,
        //         'type' => 'location_missing',
        //         'message' => 'Location required'
        //     ]);
        // }

        // // GEOFENCE CHECK
        // $officeLat = 21.2514; // change
        // $officeLng = 81.6296; // change
        // $allowedRadius = 200; // meters

        // $distance = $this->calculateDistance($latitude, $longitude, $officeLat, $officeLng);

        // if ($distance > $allowedRadius) {
        //     return response()->json([
        //         'status' => false,
        //         'type' => 'outside_location',
        //         'message' => 'You are outside office location'
        //     ]);
        // }

        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,csv',
            // 'latitude' => 'nullable',
            // 'longitude' => 'nullable',
        ]);

        $file = $request->file('import_file');
        $check_date = $request->check_date;
        $import = new MonthlyAttendanceImport(Auth::user(), $check_date);
        // $import = new MonthlyAttendanceImport(Auth::user(), $check_date, $latitude, $longitude);

        try {
            // Attempt the import
            Excel::import($import, $file);

            $errors = $import->getErrorMessages();
            $summary = $import->getSummary();

            if (!empty($errors)) {
                session()->put('import_errors', $errors);
                session()->flash('import_errors_blade', $errors);
                return redirect()->back()->with('error', 'Import failed! Please check the errors.');

                // return response()->json([
                //     'status' => false,
                //     'type' => 'import_error',
                //     'errors' => $errors
                // ]);
            }

            // If no errors, redirect with success message
            // return response()->json([
            //     'status' => true,
            //     'message' => "Imported: {$summary['success']}, Skipped: {$summary['skipped']}"
            // ]);
            return redirect()->back()->with('success', "Import completed successfully! Imported: {$summary['success']}, Skipped: {$summary['skipped']}");
            // return redirect()->back()->with('success', 'Import completed successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon/2) * sin($dLon/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $earthRadius * $c;
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
    public function updatePre(Request $request)
    {
        try {
            $user = Auth::user();

            $employeeId = $request->employee_id;

            $employee = Employee::where('emp_id', $employeeId)->first();

            if (!$employee) {
                return response()->json(['status' => false, 'message' => 'Employee not found.'], 404);
            }

            $punchDate = Carbon::parse($request->punch_date)->format('Y-m-d');
            $checkInTime = ($request->in_time != "" && $request->in_time != null) ? Carbon::parse($punchDate . ' ' . $request->in_time)->format('Y-m-d H:i:s') : null;
            $checkOutTime = ($request->out_time != "" && $request->out_time != null) ? Carbon::parse($punchDate . ' ' . $request->out_time)->format('Y-m-d H:i:s') : null;

            $ruleCriteria = RuleCriterion::with('fh_approval_module')
                ->where('rc_b_id', $user->emp_b_id)
                ->where('rc_condition_option_id', 140)
                ->whereHas('fh_approval_module', function ($query) {
                    $query->where('am_module_id', 249)
                        ->where('am_status', 1);
                })->first();
            $processApprovers = [];

            // Ensure $ruleCriteria exists before accessing the relationship
            if ($ruleCriteria && $ruleCriteria->fh_approval_module) {
                // Fetch filtered process approvers using emp_d_id
                $processApprovers = $ruleCriteria->fh_approval_module
                    ->filteredProcessApprovers($user->emp_b_id)
                    ->get(); // Fetch the filtered data
            }

            if (count($processApprovers)) {
                $atd_amId = $ruleCriteria->rc_am_id;
            }

            if (Carbon::parse($punchDate) >= Carbon::now()) {
                return response()->json(['status' => false, 'message' => "Can't update Future Date's Attendance"], 200);
            }

            $frozen = PayrollPeriod::where('pp_b_id', $employee->emp_b_id)
                ->where('pp_start_date', '<=', $punchDate)
                ->where('pp_end_date', '>=', $punchDate)
                ->where('pp_is_freezed', 120)  // 120 = frozen
                ->exists();

            if ($frozen) {
                return response()->json(['status' => false, 'message' => "Freezed attendance records cannot be updated."], 200);
            }

            // Check if current day is a weekly off day
            $isWeeklyOff = CentralLogics::getWeekOffDates($employee, null, null, $punchDate, $punchDate);

            $isHoliday = PolicyHolidayList::where('phl_b_id', $user->emp_b_id)->whereDate('phl_start_date', '<=', $punchDate)->whereDate('phl_end_date', '>=', $punchDate)->first();
            if ($isHoliday && $isHoliday->phl_type_id == 205) {
                return response()->json(['status' => false, 'result' => [], 'message' => "You can't check in today because it's a public holiday."]);
            } else if (($isHoliday && $isHoliday->phl_type_id == 206) || $isWeeklyOff) {
                $co_request = CompOff::where([['co_b_id', $user->emp_b_id], ['co_emp_id', $employeeId], ['co_request_date', $request->punch_date]])->first();
                if ($co_request) {
                    return response()->json(['status' => false, 'message' => "There is already a comp off for this date, you will not be able to update."]);
                }
            }

            $attendance = AttendanceRecord::where([
                'atd_b_id' => $employee->emp_b_id,
                'atd_emp_id' => $employee->emp_id,
                'atd_date' => $punchDate,
            ])->first();

            // If attendance found, log it before update
            if ($attendance) {
                AttendanceLog::create([
                    'al_b_id' => $attendance->atd_b_id,
                    'al_emp_id' => $attendance->atd_emp_id,
                    'al_atd_id' => $attendance->atd_id,
                    'al_check_in_time' => $attendance->atd_check_in_time,
                    'al_check_out_time' => $attendance->atd_check_out_time,
                    'al_total_worked_hours' => $attendance->atd_total_worked_hours,
                    'al_date' => $attendance->atd_date,
                    'al_is_late' => $attendance->atd_is_late,
                    'al_late_duration' => $attendance->atd_late_duration,
                    'al_is_early_exit' => $attendance->atd_is_early_exit,
                    'al_early_exit_duration' => $attendance->atd_early_exit_duration,
                    'al_is_absent' => $attendance->atd_is_absent,
                    'al_is_overtime' => $attendance->atd_is_overtime,
                    'al_overtime_hours' => $attendance->atd_overtime_hours,
                    'al_attendance_status' => $attendance->atd_attendance_status,
                ]);

                // Update existing attendance
                $attendance->update([
                    'atd_check_in_time' => $checkInTime,
                    'atd_check_out_time' => $checkOutTime,
                    'atd_ar_reason' => $request->reason,
                ]);
            } else {
                // Create new attendance record if not found
                $attendance = AttendanceRecord::create([
                    'atd_b_id' => $employee->emp_b_id,
                    'atd_emp_id' => $employee->emp_id,
                    'atd_date' => $punchDate,
                    'atd_work_mode_type_id' => 62,
                    'atd_checkin_method_id' => 314,
                    'atd_check_in_time' => $checkInTime,
                    'atd_check_out_time' => $checkOutTime,
                    'atd_ar_reason' => $request->reason,
                    'atd_am_id' => $atd_amId ?? null,
                ]);
            }

            // Recalculate attendance after update/create
            $punchInData = app('App\Http\Controllers\Api\Attendance\PunchInApiController')->handleAttendance($attendance, $employee, 'check-in');
            $punchOutData = app('App\Http\Controllers\Api\Attendance\PunchInApiController')->handleAttendance($attendance, $employee, 'check-out');

            return response()->json(['status' => true, 'message' => 'Attendance updated successfully.', 'punchInData' => $punchInData, 'punchOutData' => $punchOutData]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateNew(Request $request)
    {
        try {
            $user = Auth::user();
            $employeeId = $request->id;
            $employee = Employee::where('emp_id', $employeeId)->first();
            $markAsAbsent = $request->has('mark_as_absent') ? 1 : 0;

            if (!$employee) {
                return redirect()->back()->with('error', "Employee not found.");
            }

            $shift = PolicyShiftTiming::find($employee->emp_shift_type_id);
            if (!$shift) {
                return redirect()->back()->with('error', "Please assign shift to this employee.");
            }

            if (Carbon::parse($request->punch_date) >= Carbon::now()) {
                return redirect()->back()->with('error', "Can't update Future Date's Attendance");
            }

            $frozen = PayrollPeriod::where('pp_b_id', $employee->emp_b_id)
                ->where('pp_start_date', '<=', $request->punch_date)
                ->where('pp_end_date', '>=', $request->punch_date)
                ->where('pp_is_freezed', 120)  // 120 = frozen
                ->exists();

            if ($frozen) {
                return redirect()->back()->with('error', "Frozen attendance records cannot be updated.");
            }

            // Check if current day is a weekly off day
            $isWeeklyOff = CentralLogics::getWeekOffDates($employee, null, null, $request->punch_date, $request->punch_date);

            $isHoliday = PolicyHolidayList::where('phl_b_id', $user->emp_b_id)->whereDate('phl_start_date', '<=', $request->punch_date)->whereDate('phl_end_date', '>=', $request->punch_date)->first();

            if ($isHoliday && $isHoliday->phl_type_id == 205) {
                return redirect()->back()->with('error', "You can't update attendance on a public holiday.");
            } else if (($isHoliday && $isHoliday->phl_type_id == 206) || $isWeeklyOff) {
                $co_request = CompOff::where([['co_b_id', $user->emp_b_id], ['co_emp_id', $employeeId], ['co_request_date', $request->punch_date]])->whereNotIn('co_status', [140, 171, 170])->first();
                if ($co_request) {
                    return redirect()->back()->with('error', "There is already a comp off request for this date, you will not be able to update.");
                }

                $ruleCriteria = RuleCriterion::with('fh_approval_module')
                    ->where('rc_b_id', $user->emp_b_id)
                    ->where('rc_condition_option_id', 140)
                    ->whereHas('fh_approval_module', function ($query) {
                        $query->where('am_module_id', 562)
                            ->where('am_status', 1);
                    })->first();
                $processApprovers = [];

                // Ensure $ruleCriteria exists before accessing the relationship
                if ($ruleCriteria && $ruleCriteria->fh_approval_module) {
                    // Fetch filtered process approvers using emp_d_id
                    $processApprovers = $ruleCriteria->fh_approval_module
                        ->filteredProcessApprovers($user->emp_b_id)
                        ->get(); // Fetch the filtered data
                }

                if (count($processApprovers)) {
                    $comp_off_amId = $ruleCriteria->rc_am_id;
                    $request->merge([
                        'co_am_id' => $comp_off_amId,
                    ]);
                } else {
                    $approvalMapping = ApprovalHelper::getApprovalMapping($user->emp_b_id, $user->emp_id, 562);
                    if (!$approvalMapping) {
                        return redirect()->back()->with('error', "Sorry! not found any approval settings for comp off module, contact administration.");
                    }
                }
            }

            // Fetch the attendance record
            $attendance = AttendanceRecord::where('atd_emp_id', $employeeId)
                ->whereDate('atd_date', $request->punch_date)
                ->first();

            // Format the times
            $checkInTime = (!empty($request->in_time)) ? Carbon::parse($request->punch_date . ' ' . $request->in_time)->format('Y-m-d H:i:s') : null;
            $checkOutTime = (!empty($request->out_time)) ? Carbon::parse($request->punch_date . ' ' . $request->out_time)->format('Y-m-d H:i:s') : null;

            // Calculate shift timings
            $shiftStartTime = $request->punch_date . ' ' . Carbon::parse($shift->pst_start_time)->format('H:i:s');
            $shiftEndTime = $request->punch_date . ' ' . Carbon::parse($shift->pst_end_time)->format('H:i:s');

            $dayName = Carbon::parse($request->punch_date)->format('l');

            // Handle Partial day punch
            if ($shift->pst_allow_partial_day == 1 && $shift->fh_master_table_week->m_name == $dayName) {
                $shiftStartTime = $request->punch_date . ' ' . Carbon::parse($shift->pst_partial_day_begin_time)->format('H:i:s');
                $shiftEndTime = $request->punch_date . ' ' . Carbon::parse($shift->pst_partial_day_end_time)->format('H:i:s');
            }

            // Initialize variables for calculations
            $isLate = 0;
            $lateDuration = 0;
            $isEarlyExit = 0;
            $earlyExitDuration = 0;
            $isOvertime = 0;
            $overtimeHours = 0;
            $totalWorkedHours = 0;
            $attendanceStatus = 228; // Default to Mispunch (228)

            // Calculate values only if both check-in and check-out are provided
            if ($checkInTime && $checkOutTime) {
                $checkIn = Carbon::parse($checkInTime);
                $checkOut = Carbon::parse($checkOutTime);
                $shiftStart = Carbon::parse($shiftStartTime);
                $shiftEnd = Carbon::parse($shiftEndTime);

                // Calculate total worked hours
                $totalWorkedMinutes = $checkIn->diffInMinutes($checkOut);
                $totalWorkedHours = number_format($totalWorkedMinutes / 60, 2);

                // Late calculation
                $shiftGraceTime = $shiftStart->copy();
                if ($shift->pst_allow_grace_time == 1) {
                    $shiftGraceTime->addMinutes($shift->pst_grace_time);
                }

                if ($checkIn->gt($shiftGraceTime)) {
                    $isLate = 1;
                    $lateDuration = number_format($checkIn->diffInMinutes($shiftGraceTime), 2);
                }

                // Early exit calculation
                if ($checkOut->lt($shiftEnd)) {
                    $isEarlyExit = 1;
                    $earlyExitDuration = number_format($shiftEnd->diffInMinutes($checkOut), 2);
                }

                // Overtime calculation
                if ($checkOut->gt($shiftEnd)) {
                    $isOvertime = 1;
                    $overtimeMinutes = $checkOut->diffInMinutes($shiftEnd);  // Get positive difference
                    $overtimeHours = number_format(abs($overtimeMinutes) / 60, 2);  // Convert to hours
                }

                // Check if current day is a holiday
                $isHoliday = PolicyHolidayList::where('phl_b_id', $employee->emp_b_id)
                    ->whereDate('phl_start_date', '<=', $request->punch_date)
                    ->whereDate('phl_end_date', '>=', $request->punch_date)
                    ->exists();

                // Handle holiday/week off attendance
                if ($isWeeklyOff || $isHoliday) {
                    $attendanceStatus = $isWeeklyOff ? 320 : 319; // Weekly Off Work : Holiday Work
                } else {
                    // Calculate minimum work hours in minutes
                    // $minWorkHour = $shift->pst_min_work_hour
                    //     ? Carbon::parse($request->punch_date . ' ' . $shift->pst_start_time)->diffInMinutes(
                    //         Carbon::parse($request->punch_date . ' ' . $shift->pst_min_work_hour)
                    //     )
                    //     : 0;

                    $minWorkHour = $shift->pst_min_work_hour
                        ? Carbon::parse($shift->pst_start_time)->diffInMinutes(
                            Carbon::parse($shift->pst_min_work_hour)
                        )
                        : 0;

                    $halfMinWorkHour = $minWorkHour / 2;

                    // Determine attendance status based on worked hours
                    if ($totalWorkedMinutes >= $minWorkHour) {
                        $attendanceStatus = 251; // Present
                    } elseif ($totalWorkedMinutes >= $halfMinWorkHour) {
                        $attendanceStatus = 252; // Half Day
                    } else {
                        $attendanceStatus = 203; // Absent
                    }
                }
            } elseif ($checkInTime || $checkOutTime) {
                // Only one punch provided - mark as mispunch
                $attendanceStatus = 228; // Mispunch
            }

            if (!$markAsAbsent) {

                // Create the attendance log
                $attendanceLog = AttendanceLog::create([
                    'al_b_id' => $user->emp_b_id,
                    'al_emp_id' => $employeeId,
                    'al_atd_id' => $attendance->atd_id ?? null,
                    'al_check_in_time' => $checkInTime,
                    'al_check_out_time' => $checkOutTime,
                    'al_date' => $request->punch_date,
                    'al_is_late' => $isLate,
                    'al_late_duration' => $lateDuration,
                    'al_is_early_exit' => $isEarlyExit,
                    'al_early_exit_duration' => $earlyExitDuration,
                    'al_is_overtime' => $isOvertime,
                    'al_overtime_hours' => $overtimeHours,
                    'al_total_worked_hours' => $totalWorkedHours,
                    'al_attendance_status' => $attendanceStatus,
                    'al_reason' => $request->reason,
                    'al_updated_by' => $user->emp_id,
                ]);
                if ($attendanceStatus == 320 || $attendanceStatus == 319) {
                    CentralLogics::generateCompOff($employee, $request->punch_date);
                }
            } else {
                $attendanceStatus = 203;
                $attendanceLog = AttendanceLog::create([
                    'al_b_id' => $user->emp_b_id,
                    'al_emp_id' => $employeeId,
                    'al_atd_id' => $attendance->atd_id ?? null,
                    'al_check_in_time' => null,
                    'al_check_out_time' => null,
                    'al_date' => $request->punch_date,
                    'al_is_late' => 0,
                    'al_late_duration' => 0,
                    'al_is_early_exit' => 0,
                    'al_early_exit_duration' => 0,
                    'al_is_overtime' => 0,
                    'al_overtime_hours' => 0,
                    'al_total_worked_hours' => 0,
                    'al_attendance_status' => $attendanceStatus,
                    'al_is_absent' => $markAsAbsent ?? 0,
                    'al_reason' => $request->reason,
                    'al_updated_by' => $user->emp_id,
                ]);
            }

            // ✅ Generate and update al_code after creation
            if ($attendanceLog) {
                $attendanceLog->al_code = 'AL' . Carbon::parse($attendanceLog->al_date)->format('Ymd') . '-' . $attendanceLog->al_id;
                $attendanceLog->save();
                $masterStatusData = MasterTable::where('m_id', $attendanceStatus)->first();
            }
            return response()->json(['status' => true, 'message' => 'Attendance updated successfully.', 'logData' => $attendanceLog, 'statusData' => $masterStatusData]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function update(Request $request)
    {
        $result = CentralLogics::processAttendance($request);

        // If CentralLogics accidentally returns JsonResponse, return it directly
        if ($result instanceof \Illuminate\Http\JsonResponse) {
            return $result;
        }

        if ($result['status']) {
            return response()->json([
                'status' => true,
                'message' => $result['message'],
                'logData' => $result['logData'],
                'statusData' => $result['statusData']
            ]);
        }

        if ($request->ajax()) {
            return response()->json([
                'status'  => false,
                'message' => $result['message']
            ], 400);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function monthlyAttendanceStatusUpdate(Request $request)
    {
        $month = $request->mt_monthFilter;
        $user = auth()->user();

        // Month start & end
        $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $endDate   = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

        $controller = app()->make(PunchInApiController::class);

        AttendanceRecord::with(['fh_policy_shift_timing', 'fh_employee'])
            ->where('atd_b_id', $user->emp_b_id)
            ->whereBetween('atd_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('atd_date')
            ->chunk(500, function($attendanceRecords) use ($controller) {

                foreach ($attendanceRecords as $record) {
                    try {
                        // Only process if check-in & check-out are present
                        if ($record->atd_check_in_time && $record->atd_check_out_time) {

                            $checkIn = Carbon::parse($record->atd_check_in_time);
                            $checkOut = Carbon::parse($record->atd_check_out_time);

                            if ($checkOut->lessThan($checkIn)) {
                                $checkOut->addDay();
                            }

                            $shift = $record->fh_policy_shift_timing;
                            if ($shift) {
                                $date = Carbon::parse($record->atd_date)->format('Y-m-d');
                                $shiftStart = Carbon::parse($date.' '.Carbon::parse($shift->pst_start_time)->format('H:i:s'));
                                $shiftEnd   = Carbon::parse($date.' '.Carbon::parse($shift->pst_end_time)->format('H:i:s'));

                                if ((int)$shift->pst_end_next_day === 1) {
                                    $shiftEnd->addDay();
                                }

                                $grace = ((int)$shift->pst_allow_grace_time === 1)
                                    ? (int)$shift->pst_grace_time
                                    : 0;

                                $latestOnTime = $shiftStart->copy()->addMinutes($grace);

                                $record->atd_is_late = $checkIn->greaterThan($latestOnTime) ? 1 : 0;
                                $record->atd_late_duration = $record->atd_is_late
                                    ? $latestOnTime->diffInMinutes($checkIn)
                                    : 0;

                                $record->atd_is_early_exit = $checkOut->lessThan($shiftEnd) ? 1 : 0;
                                $record->atd_early_exit_duration = $record->atd_is_early_exit
                                    ? $checkOut->diffInMinutes($shiftEnd)
                                    : 0;
                            }

                            $controller->calculateAttendanceStatus($record);
                            $record->save();

                        } else {
                            // Handle mispunch or missing check-in/out
                            $controller->calculateAttendanceStatus($record);
                            $record->save();
                        }
                    } catch (\Exception $e) {
                        Log::error("Monthly correction error", [
                            'atd_id' => $record->atd_id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }

            });

        return response()->json([
            'status' => true,
            'message' => "Monthly attendance correction completed for {$month}"
        ]);
    }

    public function getOccurrencesOfDaysInMonth(int $year, int $month): array
    {
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;
        $occurrences = [];

        // Initialize array for each day of the week
        $weekDays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        foreach ($weekDays as $day) {
            $occurrences[$day] = [];
        }

        \Log::info('daysInMonth: ' . $daysInMonth);

        // Iterate through all days in the month
        for ($day = 1; $day <= 30; $day++) {
            $date = Carbon::create($year, $month, $day);
            $dayName = $date->format('l'); // Get the full day name (e.g., Sunday, Monday)
            $occurrences[$dayName][] = $date->toDateString();
        }

        return $occurrences;
    }
}
