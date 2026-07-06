<?php

namespace App\Http\Controllers\Web\Admin\AttendanceDetails;

use App\Helpers\ApprovalHelper;
use App\Helpers\CentralLogics;
use App\Helpers\RolePermissionLogics;
use App\Http\Controllers\Controller;
use App\Models\AttendanceException;
use App\Models\AttendanceLog;
use App\Models\AttendanceRecord;
use App\Models\CompOff;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\PayrollPeriod;
use Illuminate\Support\Facades\Crypt;
use App\Models\PolicyAttendance;
use App\Models\PolicyHolidayList;
use App\Models\PolicyShiftTiming;
use App\Models\RuleCriterion;
use App\Models\SalaryEmployeeLeaves;
use App\Models\SalaryLeaveType;
use App\Models\OvertimePolicy;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Carbon\CarbonPeriod;
use Razorpay\Api\Card;

class SummaryAttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    
    public function index(Request $request)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;
    
        // Get the current date or the date from the filter
        $monthFilter = $request->input('sm_monthFilter') ?? now()->format('Y-m');
        [$year, $month] = explode('-', $monthFilter);
        $branchFilter = request()->input('sm_branchFilter');
        $designationFilter = request()->input('sm_designationFilter');
        $departmentFilter = request()->input('sm_departmentFilter');
        $activeFilter = request()->input('sm_activeFilter');
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
    
        // AJAX-based data fetching logic
        if ($request->ajax()) {
            $startDateStr = $startDate->format('Y-m-d');
            $endDateStr   = $endDate->format('Y-m-d');
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['emp_b_id', $user->emp_b_id]
                ],
                [
                    'method' => 'whereNot',
                    'args' => ['emp_role_id', 1]
                ],
                // Must have joined on/before this month's end
                ['method' => 'whereDate', 'args' => ['emp_date_of_joining', '<=', $endDateStr]],
            
                // And must be active in this month: (last_working IS NULL OR last_working >= start_of_month)
                // Use COALESCE to turn NULL into a far-future date so we can keep a single AND condition
                ['method' => 'whereRaw',  'args' => ['COALESCE(emp_last_working_date, "9999-12-31") >= ?', [$startDateStr]]],
                [
                    'method' => 'select',
                    'args' => ['emp_id', 'emp_b_id','emp_pwo_id', 'emp_fname', 'emp_mname', 'emp_lname', 'emp_full_name', 'emp_code', 'emp_role_id', 'emp_email', 'emp_type_id', 'emp_br_id', 'emp_d_id', 'emp_dg_id', 'emp_dob', 'emp_date_of_joining', 'emp_phone', 'emp_work_mode_id', 'emp_shift_type_id', 'emp_status', 'emp_grade_id', 'emp_gender_id', 'emp_date_of_joining', 'emp_marital_status_id', 'emp_profile_photo', 'created_at'],
                    'relation' => ['fh_employee_type:m_id,m_name', 'fh_branch:br_id,br_name', 'fh_gender:m_id,m_name', 'fh_designation:dg_id,dg_name', 'fh_department:d_id,d_name', 'fh_work_mode:m_id,m_name', 'fh_shift_type:pst_id,pst_name', 'fh_employee_status:m_description,m_name','fh_week_off_policy:pwo_id,pwo_name,pwo_recurrence_day_ids'],
                ]
            ];
    
            if ($branchFilter != '') {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_br_id', $branchFilter]];
            }
    
            if ($designationFilter != '') {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_dg_id', $designationFilter]];
            }
    
            if ($departmentFilter != '') {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_d_id', $departmentFilter]];
            }
    
            if ($activeFilter != '') {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_status', $activeFilter]];
            }
            
            // for calculated columns
            $searchColumns = ['emp_id', 'emp_code', 'emp_fname', 'emp_mname', 'emp_lname', 'emp_full_name', 'emp_email', 'emp_phone', 'emp_date_of_joining', 'created_at', 'updated_at'];
            $searchRelationships = [
                'fh_employee_type' => ['m_name'],
                'fh_branch' => ['br_name'],
                'fh_department' => ['d_name'],
                'fh_work_mode' => ['m_name'],
                'fh_shift_type' => ['pst_name'],
                'fh_employee_status' => ['m_name'],
            ];
    
            // Check if sorting is requested on calculated columns
            $orderColumn = $request->input('order.0.column');
            $orderDirection = $request->input('order.0.dir', 'asc');
            
            // Columns that are calculated and cannot be sorted by database
            $calculatedColumns = [3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15,16]; // Indexes of calculated columns
            
            if (in_array($orderColumn, $calculatedColumns)) {
                // For calculated columns, we need to handle sorting manually
                // Get all records without pagination first
                $baseQuery = (new DynamicModelDataTableHelper(
                    eloquentModel: new Employee(), 
                    dynamicConditions: $dynamicConditions, 
                    searchColumns: $searchColumns, 
                    searchRelationships: $searchRelationships
                ))->getServerSideDataTable(true); // Get query builder instance
                
                // Apply search if any
                $searchValue = $request->input('search.value');
                if ($searchValue) {
                    $baseQuery->where(function($query) use ($searchValue, $searchColumns, $searchRelationships) {
                        foreach ($searchColumns as $column) {
                            $query->orWhere($column, 'like', '%' . $searchValue . '%');
                        }
                        foreach ($searchRelationships as $relationship => $columns) {
                            $query->orWhereHas($relationship, function($q) use ($searchValue, $columns) {
                                foreach ($columns as $column) {
                                    $q->orWhere($column, 'like', '%' . $searchValue . '%');
                                }
                            });
                        }
                    });
                }
                
                $employees = $baseQuery->get();
                
                // Process each employee to calculate attendance data
                $processedData = [];
                foreach ($employees as $employee) {
                    $startDate = Carbon::create($year, $month)->startOfMonth();
                    $endDate = Carbon::create($year, $month)->endOfMonth();
                    $emp_doj = $employee->emp_date_of_joining ? Carbon::parse($employee->emp_date_of_joining) : $startDate;
                    
                    // Filter out dates before joining
                    if ($emp_doj->gt($startDate)) {
                        $startDate = $emp_doj;
                    }
    
                    // Get week off dates - use the same logic as byAttendance
                    $weekOfDates = CentralLogics::getWeekOffDates($employee, $year, $month);
                    
                    // Get holidays
                    $holiday_record_exits = PolicyHolidayList::where('phl_b_id', $user->emp_b_id)->where('phl_day_type_id', 201)->where(function ($q) use ($startDate, $endDate) {
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
                    
                    // Get attendance details using the same function as byAttendance
                    $attendanceData = CentralLogics::newGetMonthlyAttendanceDetails($employee, $month, $year, $holidaysByDate, $weekOfDates);
                    
                    // Calculate counts from attendance data
                    $presentCount = collect($attendanceData)->sum('presentCount');
                    $weekOffPresentCount = collect($attendanceData)->sum('weekOffPresentCount');
                    $holidayCount = collect($attendanceData)->sum('holidayCount');
                    $absentCount = collect($attendanceData)->sum('absentCount');
                    $halfDayCount = collect($attendanceData)->sum('halfDayCount');
                    $leaveCount = collect($attendanceData)->sum('leaveCount');
                    $missedPunchCount = collect($attendanceData)->sum('missedPunchCount');
                    $overtimeCount = collect($attendanceData)->sum('overtimeCount');
                    $lateCount = collect($attendanceData)->sum('lateCount');
                    $earlyExitCount = collect($attendanceData)->sum('earlyExitCount');
                    $approvedLeaveCount = collect($attendanceData)->sum('approvedLeaveCount');
                    $approvedMissedPunchCount = collect($attendanceData)->sum('approvedMissedPunchCount');
                    
                    // Calculate actual week off count (excluding sandwich rule conversions)
                    $actualWeekOffCount = 0;
                    foreach ($attendanceData as $data) {
                        $date = $data['date'];
                        $isWeekOff = in_array($date, $weekOfDates);
                        $isHoliday = isset($holidaysByDate[$date]);
                        
                        // Count as week off only if it's a week off and not converted by sandwich rule
                        if ($isWeekOff && !$isHoliday && $data['status_id'] != 251 && $data['status_id'] != 252) {
                            $actualWeekOffCount++;
                        }
                    }
    
                    $attendanceDataResult = [
                        'presentCount' => $presentCount,
                        'weekOffPresentCount' => $weekOffPresentCount,
                        'holidayCount' => $holidayCount,
                        'absentCount' => $absentCount,
                        'halfDayCount' => $halfDayCount,
                        'leaveCount' => $leaveCount,
                        'missedPunchCount' => $missedPunchCount,
                        'overtimeCount' => $overtimeCount,
                        'lateCount' => $lateCount,
                        'earlyExitCount' => $earlyExitCount,
                        'approvedLeaveCount' => $approvedLeaveCount,
                        'approvedMissedPunchCount' => $approvedMissedPunchCount,
                    ];
    
                    $processedData[] = [
                        'employee' => $employee,
                        'attendanceData' => $attendanceDataResult,
                        'weekOfDates' => $weekOfDates,
                        'actualWeekOffCount' => $actualWeekOffCount,
                        'sortValues' => self::getSortValues($employee, $attendanceDataResult, $actualWeekOffCount)
                    ];
                }
                
                // Sort the processed data
                usort($processedData, function($a, $b) use ($orderColumn, $orderDirection) {
                    $valueA = $a['sortValues'][$orderColumn] ?? 0;
                    $valueB = $b['sortValues'][$orderColumn] ?? 0;
                    
                    if ($orderDirection === 'asc') {
                        return $valueA <=> $valueB;
                    } else {
                        return $valueB <=> $valueA;
                    }
                });
                
                // Apply pagination manually
                $start = $request->input('start', 0);
                $length = $request->input('length', 10);
                $paginatedData = array_slice($processedData, $start, $length);
                
                $totalRecords = count($processedData);
                $filteredRecords = $totalRecords; // Since we're doing in-memory filtering
                
            } else {
                // For non-calculated columns, use the normal approach
                // Add sortBy condition only for database columns
                $databaseSortColumns = ['emp_id', 'emp_full_name', 'emp_code', 'dg_name', 'emp_date_of_joining'];
                $dynamicConditions[] = [
                    'method' => 'sortBy',
                    'args' => $databaseSortColumns
                ];
                
                $list = (new DynamicModelDataTableHelper(
                    eloquentModel: new Employee(), 
                    dynamicConditions: $dynamicConditions, 
                    searchColumns: $searchColumns, 
                    searchRelationships: $searchRelationships
                ))->getServerSideDataTable();
                
                $processedData = [];
                foreach ($list as $employee) {
                    $startDate = Carbon::create($year, $month)->startOfMonth();
                    $endDate = Carbon::create($year, $month)->endOfMonth();
                    $emp_doj = $employee->emp_date_of_joining ? Carbon::parse($employee->emp_date_of_joining) : $startDate;
                    
                    // Filter out dates before joining
                    if ($emp_doj->gt($startDate)) {
                        $startDate = $emp_doj;
                    }
    
                    // Get week off dates - use the same logic as byAttendance
                    $weekOfDates = CentralLogics::getWeekOffDates($employee, $year, $month);
                    
                    // Get holidays
                    $holiday_record_exits = PolicyHolidayList::where('phl_b_id', $user->emp_b_id)->where('phl_day_type_id', 201)->where(function ($q) use ($startDate, $endDate) {
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
                    
                    // Get attendance details using the same function as byAttendance
                    $attendanceData = CentralLogics::newGetMonthlyAttendanceDetails($employee, $month, $year, $holidaysByDate, $weekOfDates);
                    
                    // Calculate counts from attendance data
                    $presentCount = collect($attendanceData)->sum('presentCount');
                    $weekOffPresentCount = collect($attendanceData)->sum('weekOffPresentCount');
                    $holidayCount = collect($attendanceData)->sum('holidayCount');
                    $absentCount = collect($attendanceData)->sum('absentCount');
                    $halfDayCount = collect($attendanceData)->sum('halfDayCount');
                    $leaveCount = collect($attendanceData)->sum('leaveCount');
                    $missedPunchCount = collect($attendanceData)->sum('missedPunchCount');
                    $overtimeCount = collect($attendanceData)->sum('overtimeCount');
                    $lateCount = collect($attendanceData)->sum('lateCount');
                    $earlyExitCount = collect($attendanceData)->sum('earlyExitCount');
                    $approvedLeaveCount = collect($attendanceData)->sum('approvedLeaveCount');
                    $approvedMissedPunchCount = collect($attendanceData)->sum('approvedMissedPunchCount');
                    
                    // Calculate actual week off count (excluding sandwich rule conversions)
                    $actualWeekOffCount = 0;
                    foreach ($attendanceData as $data) {
                        $date = $data['date'];
                        $isWeekOff = in_array($date, $weekOfDates);
                        $isHoliday = isset($holidaysByDate[$date]);
                        
                        // Count as week off only if it's a week off and not converted by sandwich rule
                        if ($isWeekOff && !$isHoliday && $data['status_id'] != 251 && $data['status_id'] != 252) {
                            $actualWeekOffCount++;
                        }
                    }
    
                    $attendanceDataResult = [
                        'presentCount' => $presentCount,
                        'weekOffPresentCount' => $weekOffPresentCount,
                        'holidayCount' => $holidayCount,
                        'absentCount' => $absentCount,
                        'halfDayCount' => $halfDayCount,
                        'leaveCount' => $leaveCount,
                        'missedPunchCount' => $missedPunchCount,
                        'overtimeCount' => $overtimeCount,
                        'lateCount' => $lateCount,
                        'earlyExitCount' => $earlyExitCount,
                        'approvedLeaveCount' => $approvedLeaveCount,
                        'approvedMissedPunchCount' => $approvedMissedPunchCount,
                    ];
    
                    $processedData[] = [
                        'employee' => $employee,
                        'attendanceData' => $attendanceDataResult,
                        'weekOfDates' => $weekOfDates,
                        'actualWeekOffCount' => $actualWeekOffCount
                    ];
                }
                
                $paginatedData = $processedData;
                $totalRecords = (new DynamicModelDataTableHelper(
                    eloquentModel: new Employee(), 
                    dynamicConditions: $dynamicConditions, 
                    searchColumns: $searchColumns, 
                    searchRelationships: $searchRelationships
                ))->countFilteredServerSideDataTable();
                $filteredRecords = $totalRecords;
            }
    
            // Prepare row data
            $rowData = [];
            $i = ($request->input('start', 0) ?? 0) + 1;
    
            foreach ($paginatedData as $data) {
                $val = $data['employee'];
                $attendanceDataResult = $data['attendanceData'];
                $weekOfDates = $data['weekOfDates'];
                $actualWeekOffCount = $data['actualWeekOffCount'];
    
                $row = [];
                $row[] = $i++;
    
                $profilePhoto = isset($val->emp_profile_photo) && !empty($val->emp_profile_photo)
                    ? $val->emp_profile_photo
                    : asset('assets/imgs/user.png');
    
                $row[] = '<div class="d-flex"><span class="avatar avatar-md brround me-3 rounded-circle" style="background-image: url(\'' . $profilePhoto . '\');"></span>
                            <div class="mt-2">'. $val->emp_full_name. '</div>';
                $row[] = $val->emp_code ?? '--';
                $row[] = $val->fh_designation->dg_name;
    
                $row[] = $attendanceDataResult['presentCount'];
                $row[] = $actualWeekOffCount; // Use actual week off count (excluding sandwich conversions)
                $row[] = $attendanceDataResult['weekOffPresentCount'];
                $row[] = $attendanceDataResult['holidayCount'];
                $row[] = $attendanceDataResult['presentCount'] + $actualWeekOffCount + $attendanceDataResult['holidayCount'] + ($attendanceDataResult['leaveCount']);
                $row[] = $attendanceDataResult['absentCount'];
                $row[] = $attendanceDataResult['halfDayCount'];
                $row[] = $attendanceDataResult['leaveCount'];
                $row[] = $attendanceDataResult['missedPunchCount'];
                $row[] = $attendanceDataResult['overtimeCount'];
                $row[] = $attendanceDataResult['lateCount'];
                $row[] = $attendanceDataResult['earlyExitCount'];
    
                $encryptedId = Crypt::encryptString($val->emp_id);
                $row[] = '
                    <div class="btn-list ms-3">
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu p-2" style="min-width: 180px;">
                                <li>
                                    <a class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2"
                                    href="' . url('admin/attendance/byattendance', ['id' => $encryptedId, 'year_month' => $monthFilter]) . '">
                                        <i class="feather feather-edit"></i> View
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>';
    
                $rowData[] = $row;
            }
    
            $output = [
                "draw" => intval($request->input('draw')),
                "recordsTotal" => $totalRecords,
                "recordsFiltered" => $filteredRecords,
                "data" => $rowData,
            ];
    
            return response()->json($output);
        }
    
        $columns = [
            'S. No.',
            'Emp. Name',
            'Emp. Code',
            'Designation',
            'Present',
            'WeekOff',
            'WOP',
            'Holiday',
            'Total SD',
            'Absent',
            'HD',
            'Leave',
            'MSP',
            'OT',
            'late',
            'Early Exit',
            'Action'
        ];
    
        return view('admin.setting.attendance-details.summary-attendance', compact('columns'));
    }

    // Helper function to get sort values for calculated columns
    private static function getSortValues($employee, $attendanceData, $actualWeekOffCount)
    {
        return [
            4 => $attendanceData['presentCount'], // Present
            5 => $actualWeekOffCount, // WeekOff (use the actual count instead of counting array)
            6 => $attendanceData['weekOffPresentCount'], // WOP
            7 => $attendanceData['holidayCount'], // Holiday
            8 => $attendanceData['presentCount'] + $actualWeekOffCount + $attendanceData['holidayCount'] + ($attendanceData['halfDayCount']/2),
            9 => $attendanceData['absentCount'], // Absent
            10 => $attendanceData['halfDayCount'], // HD
            11 => $attendanceData['leaveCount'], // Leave
            12 => $attendanceData['missedPunchCount'], // MSP
            13 => $attendanceData['overtimeCount'], // OT
            14 => $attendanceData['lateCount'], // late
            15 => $attendanceData['earlyExitCount'], // Early Exit
        ];
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

        // Iterate through all days in the month
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($year, $month, $day);
            $dayName = $date->format('l'); // Get the full day name (e.g., Sunday, Monday)
            $occurrences[$dayName][] = $date->toDateString();
        }

        return $occurrences;
    }
    
    public function byAttendance($id,$year_month=null)
    {
        $user = Auth::user();
        $emp_id = Crypt::decryptString($id);
        $currentMonth = $year_month ? $year_month : now()->format('Y-m');
        [$year, $month] = explode('-', $currentMonth);

        //OT Rule get data
        $otRule = OvertimePolicy::where('ot_b_id', $user->emp_b_id)->first();
    
        // Generate dates of the current month
        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        $totalDays = $startDate->daysInMonth;
    
        $emp = Employee::where('emp_id', $emp_id)->first();
        $assignShift = PolicyShiftTiming::where('pst_id', $emp->emp_shift_type_id)->first();
    
        // Get week off dates
        $weekOfDates = CentralLogics::getWeekOffDates($emp, $year, $month);
        
        // Get holidays
        $holiday_record_exits = PolicyHolidayList::where('phl_b_id', $user->emp_b_id)->where('phl_day_type_id', 201)->where(function ($q) use ($startDate, $endDate) {
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
    
        // Get attendance details
        $attendanceData = CentralLogics::newGetMonthlyAttendanceDetails($emp, $month, $year, $holidaysByDate, $weekOfDates);

        // if($emp->emp_b_id == 44) {
        //     $attendanceData = CentralLogics::newGetMonthlyAttendanceDetails02Jan($emp, $month, $year, $holidaysByDate, $weekOfDates);
        // }
    
        // Filter out dates before joining and after last working day
        $filteredAttendanceData = array_filter($attendanceData, function($data) use ($emp) {
            $date = Carbon::parse($data['date']);
            
            // Skip dates before joining
            if ($emp->emp_date_of_joining && $date->lt(Carbon::parse($emp->emp_date_of_joining))) {
                return false;
            }
            
            // Skip dates after last working day
            if ($emp->emp_last_working_day && $date->gt(Carbon::parse($emp->emp_last_working_day))) {
                return false;
            }
            
            return true;
        });
    
        // Calculate actual week off count (excluding sandwich rule conversions)
        $actualWeekOffCount = 0;
        foreach ($filteredAttendanceData as $data) {
            $date = $data['date'];
            $isWeekOff = in_array($date, $weekOfDates);
            $isHoliday = isset($holidaysByDate[$date]);
            
            // Count as week off only if it's a week off and not converted by sandwich rule
            if ($isWeekOff && !$isHoliday && $data['status_id'] != 251 && $data['status_id'] != 252) {
                if($data['status_id'] == 203 || $data['status_id'] == 215){
                    continue;
                }
                $actualWeekOffCount++;
            }
        }
    
        // Fill missing dates with EBS status
        $filledAttendanceData = [];
        $allDates = CarbonPeriod::create($startDate, $endDate); // 1st to 30th/31st
    
        foreach ($allDates as $date) {
            $dateStr = $date->format('Y-m-d');
            
            // Skip dates before joining and after last working day
            $shouldInclude = true;
            if ($emp->emp_date_of_joining && $date->lt(Carbon::parse($emp->emp_date_of_joining))) {
                $shouldInclude = false;
            }
            if ($emp->emp_last_working_day && $date->gt(Carbon::parse($emp->emp_last_working_day))) {
                $shouldInclude = false;
            }
            
            if (!$shouldInclude) {
                continue;
            }
    
            // Try to get existing record
            $data = collect($filteredAttendanceData)->firstWhere('date', $dateStr);
    
            if (!$data) {
                // Push default EBS row
                $data = [
                    'date' => $dateStr,
                    'status' => '--',
                    'statusColor' => '#999',
                    'checkInTime' => null,
                    'checkOutTime' => null,
                    'workingHour' => '0',
                    'updatedBy' => null,
                    'previousCheckInTime' => null,
                    'previousCheckOutTime' => null,
                    'late' => null,
                    'earlyExit' => null,
                    'OT' => null,
                    'previousLate' => null,
                    'previousExit' => null,
                    'checkInLocation' => null,
                    'checkOutLocation' => null,
                    'checkInPhoto' => null,
                    'checkOutPhoto' => null,
                    'presentCount' => 0,
                    'weekOffPresentCount' => 0,
                    'leaveCount' => 0,
                    'holidayCount' => 0,
                    'weekOffCount' => 0,
                    'absentCount' => 0,
                    'halfDayCount' => 0,
                    'missedPunchCount' => 0,
                    'overtimeCount' => 0,
                    'lateCount' => 0,
                    'earlyExitCount' => 0,
                    'approvedLeaveCount' => 0,
                    'approvedMissedPunchCount' => 0,
                    'UPL' => 0,
                ];
            }
    
            $filledAttendanceData[] = $data;
        }
    
        // Sort by date to ensure chronological order
        usort($filledAttendanceData, fn($a, $b) => strtotime($a['date']) - strtotime($b['date']));
    
        // Calculate counts from filtered data
        $presentCount = collect($filteredAttendanceData)->sum('presentCount');
        $weekOffPresentCount = collect($filteredAttendanceData)->sum('weekOffPresentCount');
        $leaveCount = collect($filteredAttendanceData)->sum('leaveCount');
        $holidayCount = collect($filteredAttendanceData)->sum('holidayCount');
        $absentCount = collect($filteredAttendanceData)->sum('absentCount');
        $halfDayCount = collect($filteredAttendanceData)->sum('halfDayCount');
        $missedPunchCount = collect($filteredAttendanceData)->sum('missedPunchCount');
        $overtimeCount = collect($filteredAttendanceData)->sum('overtimeCount');
        $lateCount = collect($filteredAttendanceData)->sum('lateCount');
        $earlyExitCount = collect($filteredAttendanceData)->sum('earlyExitCount');
        $approvedLeaveCount = collect($filteredAttendanceData)->sum('approvedLeaveCount');
        $approvedMissedPunchCount = collect($filteredAttendanceData)->sum('approvedMissedPunchCount');
        $weekOffCount = collect($filteredAttendanceData)->sum('weekOffCount');
        $totalOTMinutes = collect($filteredAttendanceData)->sum(function ($row) {
            if (empty($row['OT'])) return 0;
            $hours = floor($row['OT']);
            $minutes = ($row['OT'] - $hours) * 100; // 4.4 → 40 min
            return ($hours * 60) + $minutes;
        });

        // ✅ convert back
        $hours = floor($totalOTMinutes / 60);
        $minutes = $totalOTMinutes % 60;

        $totalOTHrs = sprintf('%d hrs %d min', $hours, $minutes);
    
        return view('admin.setting.attendance-details.attendanceby', [
            'emp' => $emp,
            'presentCount' => $presentCount,
            'weekOffPresentCount' => $weekOffPresentCount,
            'absentCount' => $absentCount,
            'missedPunchCount' => $missedPunchCount,
            'approvedMissedPunchCount' => $approvedMissedPunchCount,
            'overtimeCount' => $overtimeCount,
            'lateCount' => $lateCount,
            'earlyExitCount' => $earlyExitCount,
            'leaveCount' => $leaveCount,
            'approvedLeaveCount' => $approvedLeaveCount,
            'halfDayCount' => $halfDayCount,
            'weekOffCount' => $actualWeekOffCount, // Use actual week off count
            'holidayCount' => $holidayCount,
            'otRule'       => $otRule,
            'assignShift'  => $assignShift,
            'monthFilter' => $currentMonth,
            'monthlyAttendanceData' => $filledAttendanceData,
            'totalDays' => $totalDays,
            'totalWorkedDay' => $presentCount + $weekOffCount + $holidayCount + $leaveCount,
            'totalOTHrs' => $totalOTHrs,
        ]);
    }


    public function byAttendanceUpdatePre(Request $request)
    {
        try {
        
            $user = Auth::user();

            $employeeId = $request->id;
            
            $employee = Employee::where('emp_id', $employeeId)->first();

            if (Carbon::parse($request->punch_date) >= Carbon::now()) {
                return redirect()->back()->with('error', "Can't update Future Date's Attendance");
            }

            $frozen = PayrollPeriod::where('pp_b_id', $employee->emp_b_id)
                ->where('pp_start_date', '<=', $request->punch_date)
                ->where('pp_end_date', '>=', $request->punch_date)
                ->where('pp_is_freezed', 120)  // 120 = frozen
            ->exists();

            if ($frozen) {
                return redirect()->back()->with('error', "Freezed attendance records cannot be updated.");
            }

            $isHoliday = PolicyHolidayList::where('phl_b_id', $user->emp_b_id)->whereDate('phl_start_date', '<=', $request->punch_date)->whereDate('phl_end_date', '>=', $request->punch_date)->first();
            if ($isHoliday && $isHoliday->phl_type_id == 205) {
                return response()->json(['status' => false, 'result' => [], 'message' => "You can't check in today because it's a public holiday."]);
            } else if ($isHoliday && $isHoliday->phl_type_id == 206) {
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
                        return response()->json(['result' => [], 'status' => false, 'message' => 'Sorry! not found any approval settings for comp off module, contact administration.']);
                    }
                }
            }

            // Fetch the attendance record
            $attendance = AttendanceRecord::where('atd_emp_id', $employeeId)
                ->whereDate('atd_date', $request->punch_date)
                ->first();

            if ($attendance) {
                // Store the previous record in AttendanceLog before updating
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
            }

            // Fetch shift details
            $checkInTime = ($request->in_time != "" && $request->in_time != null) ? Carbon::parse($request->punch_date.' '.$request->in_time)->format('Y-m-d H:i:s') : null;
            $checkOutTime = ($request->out_time != "" && $request->out_time != null) ? Carbon::parse($request->punch_date.' '.$request->out_time)->format('Y-m-d H:i:s') : null;

            if(!$attendance){
                $attendance = AttendanceRecord::create([
                    'atd_b_id' => $employee->emp_b_id,
                    'atd_emp_id' => $employee->emp_id,
                    'atd_date' => $request->punch_date,
                    'atd_work_mode_type_id' => 62,
                    'atd_checkin_method_id' => 314,
                    'atd_check_in_time' => $checkInTime,
                    'atd_check_out_time'=> $checkOutTime,
                ]);
            }

            $attendance->update([
                'atd_check_in_time' => $checkInTime,
                'atd_check_out_time' => $checkOutTime,
                'atd_ar_reason'=> $request->reason,
                'atd_updated_by'=>$user->emp_id,
            ]);

            // dd($attendance);
            app('App\Http\Controllers\Api\Attendance\PunchInApiController')->handleAttendance($attendance, $employee, 'check-in');
            app('App\Http\Controllers\Api\Attendance\PunchInApiController')->handleAttendance($attendance, $employee, 'check-out');

            return redirect()->back()->with('success', 'Attendance record updated successfully.');

            // if ($attendance->save()) {
            //     return redirect()->back()->with('success', "Attendance record updated successfully.");
            // } else {
            //     return redirect()->back()->with('error', "Failed to update attendance record.");
            // }
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    public function byAttendanceUpdateNew(Request $request)
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
                $co_request = CompOff::where([['co_b_id', $user->emp_b_id], ['co_emp_id', $employeeId], ['co_request_date', $request->punch_date]])->first();
                if ($co_request) {
                    return redirect()->back()->with('error', "There is already a comp off for this date, you will not be able to update.");
                }
            }

            // Fetch the attendance record
            $attendance = AttendanceRecord::where('atd_emp_id', $employeeId)
                ->whereDate('atd_date', $request->punch_date)
                ->first();
    
            // Format the times
            $checkInTime = (!empty($request->in_time)) ? Carbon::parse($request->punch_date.' '.$request->in_time)->format('Y-m-d H:i:s') : null;
            $checkOutTime = (!empty($request->out_time)) ? Carbon::parse($request->punch_date.' '.$request->out_time)->format('Y-m-d H:i:s') : null;

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
                    'al_attendance_status' => 203,
                    'al_is_absent' => $markAsAbsent ?? 0,
                    'al_reason' => $request->reason,
                    'al_updated_by' => $user->emp_id,
                ]);
            }
    
            // ✅ Generate and update al_code after creation
            if ($attendanceLog) {
                $attendanceLog->al_code = 'AL' . Carbon::parse($attendanceLog->al_date)->format('Ymd') . '-' . $attendanceLog->al_id;
                $attendanceLog->save();
            }
            if ($attendanceStatus == 320 || $attendanceStatus == 319) {
                CentralLogics::generateCompOff($employee, $request->punch_date);
            }
            
    
            return redirect()->back()->with('success', 'Attendance log record created successfully.');
    
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function byAttendanceUpdate(Request $request)
    {
        $result = CentralLogics::processAttendance($request);

        $data = $result instanceof \Illuminate\Http\JsonResponse
                ? $result->getData(true)
                : $result;

        if ($data['status']) {
            return redirect()->back()->with('success', 'Attendance log record created successfully.');
        } else {
            return redirect()->back()->with('error', $data['message']);
        }

        return redirect()->back()->with('error', $data['message']);
    }
    
    // public function getAttendanceHistoryByDate(Request $request)
    // {
    //     $empId = $request->emp_id;
    //     $date = $request->date;

    //     $history = AttendanceRecord::where('atd_emp_id', $empId)
    //         ->whereDate('atd_date', $date)
    //         ->orderBy('atd_id', 'desc')
    //         ->get();
    //     if ($history->isNotEmpty()) {
    //         $history = $history->map(function($item){
    //             return [
    //                 'al_id'             => $item->atd_id,
    //                 'al_code'           => '-',
    //                 'al_check_in_time'  => $item->atd_check_in_time ? Carbon::parse($item->atd_check_in_time)->format('d-M-Y H:i') : '-',
    //                 'al_check_out_time' => $item->atd_check_out_time ? Carbon::parse($item->atd_check_out_time)->format('d-M-Y H:i') : '-',
    //                 'al_updated_by'     => '-',
    //                 'al_is_absent'      => '-',
    //                 'al_create_time'    => '-',
    //                 'al_reason'         => '-',
    //                 'editable'          => false,
    //             ];
    //         });
    //     } else {
    
    //         $history = AttendanceLog::where('al_emp_id', $empId)
    //             ->whereDate('al_date', $date)
    //             ->orderBy('al_id', 'desc')
    //             ->get()
    //             ->map(function($item){
    //                 return [
    //                     'al_id'             => $item->al_id,
    //                     'al_code'           => $item->al_code ?? '-',
    //                     'al_check_in_time'  => $item->al_check_in_time ? Carbon::parse($item->al_check_in_time)->format('d-M-Y H:i') : '-',
    //                     'al_check_out_time' => $item->al_check_out_time ? Carbon::parse($item->al_check_out_time)->format('d-M-Y H:i') : '-',
    //                     'al_updated_by'     => $item->fh_employee_data->emp_full_name ?? '-',
    //                     'al_is_absent'      => $item->al_is_absent,
    //                     'al_create_time'    => strtolower(date('H:i d-M-Y', strtotime($item->created_at))) ?? '-',
    //                     'al_reason'         => $item->al_reason ?? '-',
    //                     'editable'          => true,
    //                 ];
    //             });
    //     }
    
    //     return response()->json(['status' => true, 'data' => $history]);
    // }

    public function getAttendanceHistoryByDate(Request $request)
    {
        $empId = $request->emp_id;
        $date = $request->date;

        $history = AttendanceLog::where('al_emp_id', $empId)
                ->whereDate('al_date', $date)
                ->orderBy('al_id', 'desc')
                ->get();

        if ($history->isNotEmpty()) {
            $history = $history->map(function($item){
                return [
                    'al_id'             => $item->al_id,
                    'al_code'           => $item->al_code ?? '-',
                    'al_check_in_time'  => $item->al_check_in_time ? Carbon::parse($item->al_check_in_time)->format('d-M-Y H:i') : '-',
                    'al_check_out_time' => $item->al_check_out_time ? Carbon::parse($item->al_check_out_time)->format('d-M-Y H:i') : '-',
                    'al_updated_by'     => $item->fh_employee_data->emp_full_name ?? '-',
                    'al_is_absent'      => $item->al_is_absent,
                    'al_create_time'    => strtolower(date('H:i d-M-Y', strtotime($item->created_at))) ?? '-',
                    'al_reason'         => $item->al_reason ?? '-',
                    'editable'          => true,
                ];
            });
        } else {
            $history = AttendanceRecord::where('atd_emp_id', $empId)
            ->whereDate('atd_date', $date)
            ->orderBy('atd_id', 'desc')
            ->get()
            ->map(function($item){
                return [
                    'al_id'             => $item->atd_id,
                    'al_code'           => '-',
                    'al_check_in_time'  => $item->atd_check_in_time ? Carbon::parse($item->atd_check_in_time)->format('d-M-Y H:i') : '-',
                    'al_check_out_time' => $item->atd_check_out_time ? Carbon::parse($item->atd_check_out_time)->format('d-M-Y H:i') : '-',
                    'al_updated_by'     => '-',
                    'al_is_absent'      => '-',
                    'al_create_time'    => '-',
                    'al_reason'         => '-',
                    'editable'          => false,
                ];
            });
        }
    
        return response()->json(['status' => true, 'data' => $history]);
    }
    
    public function deleteAttendanceHistory(Request $request)
    {
        $record = AttendanceLog::where('al_id', $request->id)->first();
        if (!$record) {
            return response()->json(['status' => false, 'message' => 'Record not found']);
        }

        $frozen = PayrollPeriod::where('pp_b_id', $record->al_b_id)
                ->where('pp_start_date', '<=', $record->al_date)
                ->where('pp_end_date', '>=', $record->al_date)
                ->where('pp_is_freezed', 120)
                ->exists();
        if ($frozen) {
            return ['status' => false, 'message' => "Frozen attendance records can't be deleted."];
        }
    
        $record->delete();
        return response()->json(['status' => true, 'message' => 'Record deleted successfully']);
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
