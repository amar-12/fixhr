<?php

namespace App\Http\Controllers\Web\Admin\AttendanceDetails;

use App\Exports\Attendance\DailyAttendanceReport;
use App\Models\PolicyHolidayList;
use Log;
use App\Models\PolicyLeave;
use Dompdf\Dompdf;
use App\Models\Grade;
use App\Models\Branch;
use App\Models\Business;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Designation;
use App\Models\MasterTable;
use Illuminate\Http\Request;
use App\Helpers\CentralLogics;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\AttendanceRecord;
use App\Exports\ReportAttendance;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EmployeeReportExport;
use App\Jobs\ExportEmployeeReportJob;
use PhpOffice\PhpSpreadsheet\Style\Border;

use Maatwebsite\Excel\Excel as ExcelFormat;
use App\Exports\MonthlyAttendanceBasicReport;
use App\Exports\MonthlyInOutReport;
use App\Jobs\ExportDummyDataJob;

class ReportAttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.setting.attendance-details.report-attendance');
    }
    public function attendanceExport()
    {
        $user = Auth::user();
        $branch = Branch::where('br_b_id', $user->emp_b_id)->get();
        $department = Department::where('d_b_id', $user->emp_b_id)->get();
        $designation = Designation::where('dg_b_id', $user->emp_b_id)->get();
        $grade = Grade::where('g_b_id', $user->emp_b_id)->get();
        return view('admin.setting.attendance-details.attendance-export', compact('branch', 'department', 'designation', 'grade'));
    }

    public function detailedattendanceExport()
    {
        $user = Auth::user();
        $branch = Branch::where('br_b_id', $user->emp_b_id)->get();
        $department = Department::where('d_b_id', $user->emp_b_id)->get();
        $designation = Designation::where('dg_b_id', $user->emp_b_id)->get();
        $grade = Grade::where('g_b_id', $user->emp_b_id)->get();
        return view('admin.setting.attendance-details.detailed-attendance-export', compact('branch', 'department', 'designation', 'grade'));
    }

    public function selfieattendanceExport()
    {
        $user = Auth::user();
        $branch = Branch::where('br_b_id', $user->emp_b_id)->get();
        $department = Department::where('d_b_id', $user->emp_b_id)->get();
        $designation = Designation::where('dg_b_id', $user->emp_b_id)->get();
        $grade = Grade::where('g_b_id', $user->emp_b_id)->get();
        return view('admin.setting.attendance-details.selfie-attendance-export', compact('branch', 'department', 'designation', 'grade'));
    }

    public function monthlySummaryAttendanceExport()
    {
        $user = Auth::user();
        $branch = Branch::where('br_b_id', $user->emp_b_id)->get();
        $department = Department::where('d_b_id', $user->emp_b_id)->get();
        $designation = Designation::where('dg_b_id', $user->emp_b_id)->get();
        $grade = Grade::where('g_b_id', $user->emp_b_id)->get();
        return view('admin.setting.attendance-details.monthly-summary-attendance-export', compact('branch', 'department', 'designation', 'grade'));
    }
    public function employeeReportOld()
    {
        $user = Auth::user();
        $branch = Branch::where('br_b_id', $user->emp_b_id)->get();
        $department = Department::where('d_b_id', $user->emp_b_id)->get();
        $designation = Designation::where('dg_b_id', $user->emp_b_id)->get();
        $grade = Grade::where('g_b_id', $user->emp_b_id)->get();
        $status = MasterTable::where('m_group', 'STATUS')->get();
        return view('admin.setting.attendance-details.employee-report-old',  compact('branch', 'department', 'designation', 'grade', 'status'));
    }


     public function employeeReport($slug=null)
    {
        if(!in_array($slug,['employee-detail','employee-birthday','employee-joining'])){
            abort(404);
        }
        return view('admin.setting.attendance-details.employee-report', compact('slug'));
    }


    public function employeeReportExport(Request $request)
    {
        $user = Auth::user();
        $selected_columns = $request->input('columns', []);

        if (empty($selected_columns)) {
            return response()->json(['error' => 'No columns selected.'], 400);
        }

        // Start with filtering employees
        $filter_data = Employee::where('emp_role_id', '<>', 1)
            ->with(['fh_role', 'fh_marital_status', 'fh_department', 'fh_grade', 'fh_employee_type', 'fh_branch',
            'fh_designation', 'policyTax', 'fh_week_off_policy', 'fh_status','fh_job_status','fh_esic_limit','fh_pf_master'
        ])->where('emp_b_id',$user->emp_b_id); // Eager load relations


        $fileName_updated = 'dummy_data_report.xlsx';
        ExportDummyDataJob::dispatch($fileName_updated);
        // Apply additional filters if they are provided
        if ($request->filled('branch')) {
            $filter_data->where('emp_br_id', $request->branch);
        }
        if ($request->filled('designation')) {
            $filter_data->where('emp_dg_id', $request->designation);
        }
        if ($request->filled('department')) {
            $filter_data->where('emp_d_id', $request->department);
        }
        if ($request->filled('grade')) {
            $filter_data->where('emp_grade_id', $request->grade);
        }
        if ($request->filled('status')) {
            $filter_data->where('emp_status', $request->status);
        }

        $employeeData = $filter_data->select($selected_columns)->get();
        $fileName = 'EmployeeReport_' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new EmployeeReportExport($employeeData, $selected_columns), $fileName);



    }



    public function musterRollReport(Request $request)
    {

        $user = Auth::user();
        $month = $request->month ?? now()->format('m'); // Default to the current month if not provided
        $year = $request->year ?? now()->format('Y'); // Default to the current year if not provided


        $joiningDateThreshold = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        $employees = Employee::select('emp_id','emp_code','emp_full_name','emp_email','emp_b_id','emp_pwo_id','emp_date_of_joining','emp_shift_type_id')
        ->where('emp_b_id', $user->emp_b_id)
        ->where('emp_role_id','<>', 1)
        ->whereDate('emp_date_of_joining', '<=', $joiningDateThreshold)
        ->where('emp_status', 71); // Active employees only

        if ($request->branch) {
            $employees->orWhere('emp_br_id', $request->branch);
        }

        if ($request->department) {
            $employees->where('emp_d_id', $request->department);
        }

        if ($request->designation) {
            $employees->where('emp_dg_id', $request->designation);
        }

        if ($request->grade) {
            $employees->where('emp_grade_id', $request->grade);
        }

        $employees = $employees->get();

        if ($employees->isEmpty()) {
            return redirect()->back()->with('error', 'No active employee found.');
        }

        $attendanceDetails = [];
        $daysInMonth = Carbon::createFromFormat('Y-m', "{$year}-{$month}")->daysInMonth;

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $dateRange = $startDate->toPeriod($endDate);

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

        $attendanceDetails = [];
        foreach ($employees as $employee) {

            $weekOfDates = CentralLogics::getWeekOffDates($employee, $year, $month);
            $attendanceData =  CentralLogics::newGetMonthlyAttendanceDetails($employee, $month, $year, $holidaysByDate, $weekOfDates);
            $attendanceDetails[] = [
                'employee' => $employee,
                'attendanceDetails' => $attendanceData,
            ];
        }

        if ($request->has('export_excel')) {
            $business = Business::find($user->emp_b_id);
            return $this->exportAttendanceDataExcel($attendanceDetails, $month, $year, $business, $request->type);
        }

        return view('admin.setting.attendance-details.muster-roll', compact('attendanceDetails', 'month', 'year'));
    }


    // public function exportAttendanceDataExcel($attendanceDetails, $month, $year, $business, $type=NULL)
    // {
    //     set_time_limit(0);
    //     $summaryHeader = [];
    //     $exportData = [];
    //     $daysInMonth = Carbon::createFromFormat('Y-m', "{$year}-{$month}")->daysInMonth;
    //     $logoPath = $business->b_logo ? $business->b_logo : '';

    //     foreach ($attendanceDetails as $entry) {

    //         $leaveHeads = [];
    //         $employee = $entry['employee'];
    //         $dailyAttendance = $entry['attendanceDetails'];

    //         // Get employee joining date
    //         $joiningDate = null;
    //         if (isset($employee->emp_date_of_joining) && $employee->emp_date_of_joining) {
    //             $joiningDate = Carbon::parse($employee->emp_date_of_joining);
    //         }

    //         $row = [
    //             'Employee ID' => $employee->emp_code,
    //             'First Name' => $employee->emp_full_name,
    //         ];

    //         // Create attendance data indexed by date for easier lookup
    //         $attendanceByDate = [];
    //         foreach ($dailyAttendance as $attendance) {
    //             if (isset($attendance['date'])) {
    //                 $attendanceByDate[Carbon::parse($attendance['date'])->format('Y-m-d')] = $attendance;
    //             }
    //         }

    //         if($type=='MONTHLY_BASIC'){
    //             for ($day = 1; $day <= $daysInMonth; $day++) {
    //                 $currentDate = Carbon::createFromDate($year, $month, $day);
    //                 $currentDateString = $currentDate->format('Y-m-d');

    //                 // Check if current date is before joining date
    //                 if ($joiningDate && $currentDate->lt($joiningDate)) {
    //                     $row["Day {$day}"] = '--';
    //                     continue;
    //                 }

    //                 // Get attendance data for current date
    //                 $dayAttendance = $attendanceByDate[$currentDateString] ?? null;
    //                 $status = $dayAttendance['status_code'] ?? null;
    //                 $row["Day {$day}"] = $status;
    //             }
    //         } else {
    //             for ($day = 1; $day <= $daysInMonth; $day++) {
    //                 $currentDate = Carbon::createFromDate($year, $month, $day);
    //                 $currentDateString = $currentDate->format('Y-m-d');

    //                 // Check if current date is before joining date
    //                 if ($joiningDate && $currentDate->lt($joiningDate)) {
    //                     $row["Day {$day}"] = '--';
    //                     continue;
    //                 }

    //                 // Get attendance data for current date
    //                 $dayAttendance = $attendanceByDate[$currentDateString] ?? null;
    //                 $status = $dayAttendance['status_code'] ?? null;
    //                 $checkIn = $dayAttendance['checkInTime'] ?? '';
    //                 $checkOut = $dayAttendance['checkOutTime'] ?? '';

    //                 if(($checkIn && $checkIn!='-') && ($checkOut && $checkOut!='-')){
    //                     $row["Day {$day}"] = $checkIn . "\n" . $checkOut;
    //                 } else {
    //                     $row["Day {$day}"] = $status;
    //                 }
    //             }
    //         }

    //         $leave_types = [];
    //         $leavePolicy = PolicyLeave::with('fh_leave_type.fh_leave_cat_type')
    //             ->where('pl_b_id', $business->b_id)
    //             ->first();
    //         if ($leavePolicy) {
    //             $leave_types = $leavePolicy->fh_leave_type
    //                 ->pluck('fh_leave_cat_type.m_type')
    //                 ->filter()
    //                 ->toArray();
    //                 $leave_types[] = 'UPL';
    //         }

    //         // Calculate summary only from joining date onwards
    //         $validAttendanceData = [];
    //         foreach ($dailyAttendance as $attendance) {
    //             if (isset($attendance['date'])) {
    //                 $attendanceDate = Carbon::parse($attendance['date']);
    //                 if (!$joiningDate || $attendanceDate->gte($joiningDate)) {
    //                     $validAttendanceData[] = $attendance;
    //                 }
    //             }
    //         }

    //         $header1 = [
    //             'P' => array_sum(array_column($dailyAttendance, 'presentCount')) ?: '0',
    //             'HD' => array_sum(array_column($dailyAttendance, 'halfDayCount')) ?: '0',
    //             'L' => array_sum(array_column($dailyAttendance, 'leaveCount')) ?: '0',
    //         ];

    //         foreach($leave_types as $lt){
    //             $leaveHeads[$lt] = array_sum(array_column($dailyAttendance, $lt)) ?: '0';
    //         }

    //         $header2 = [
    //             'A' => array_sum(array_column($dailyAttendance, 'absentCount')) ?: '0',
    //             'WO' => array_sum(array_column($dailyAttendance, 'weekOffCount')) ?: '0',
    //             'HO' => array_sum(array_column($dailyAttendance, 'holidayCount')) ?: '0',
    //             'MSP' => array_sum(array_column($dailyAttendance, 'missedPunchCount')) ?: '0',
    //             'LC' => array_sum(array_column($dailyAttendance, 'lateCount')) ?: '0',
    //             'EG' => array_sum(array_column($dailyAttendance, 'earlyExitCount')) ?: '0',
    //             'OT' => array_sum(array_column($dailyAttendance, 'overtimeCount')) ?: '0',
    //         ];

    //         $row = array_merge($row, $header1, $leaveHeads, $header2);

    //         //this is total worked count
    //         $row['Total'] = array_sum([
    //             $row['P'],
    //             $row['WO'],
    //             $row['HO'],
    //         ]);

    //         $exportData[] = $row;

    //         $summaryHeader = array_merge(array_keys($header1),array_keys($leaveHeads),array_keys($header2),['Total']);
    //     }

    //     if($type=='MONTHLY_BASIC'){
    //         $fileName = "Monthly_Basic_{$month}_{$year}.xlsx";
    //          return  Excel::download(new MonthlyAttendanceBasicReport($exportData, $daysInMonth, $month, $year, $logoPath, $business,$summaryHeader), $fileName);
    //     }else{
    //         $fileName = "Monthly_In-Out_{$month}_{$year}.xlsx";
    //         return  Excel::download(new MonthlyInOutReport($exportData, $daysInMonth, $month, $year, $logoPath, $business,$summaryHeader), $fileName);
    //     }
    // }

    public function exportAttendanceDataExcel($attendanceDetails, $month, $year, $business, $type=NULL)
    {
        set_time_limit(0);
        $summaryHeader = [];
        $exportData = [];
        $daysInMonth = Carbon::createFromFormat('Y-m', "{$year}-{$month}")->daysInMonth;
        $logoPath = $business->b_logo ? $business->b_logo : '';

        foreach ($attendanceDetails as $entry) {

            $leaveHeads = [];
            $employee = $entry['employee'];
            $dailyAttendance = $entry['attendanceDetails'];

            // Get employee joining date
            $joiningDate = null;
            if (isset($employee->emp_date_of_joining) && $employee->emp_date_of_joining) {
                $joiningDate = Carbon::parse($employee->emp_date_of_joining);
            }

            $row = [
                'Employee ID' => $employee->emp_code,
                'First Name' => $employee->emp_full_name,
            ];

            // Create attendance data indexed by date for easier lookup
            $attendanceByDate = [];
            foreach ($dailyAttendance as $attendance) {
                if (isset($attendance['date'])) {
                    $attendanceByDate[Carbon::parse($attendance['date'])->format('Y-m-d')] = $attendance;
                }
            }

            if($type=='MONTHLY_BASIC'){
                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $currentDate = Carbon::createFromDate($year, $month, $day);
                    $currentDateString = $currentDate->format('Y-m-d');

                    // Check if current date is before joining date
                    if ($joiningDate && $currentDate->lt($joiningDate)) {
                        $row["Day {$day}"] = '--';
                        continue;
                    }

                    // Get attendance data for current date
                    $dayAttendance = $attendanceByDate[$currentDateString] ?? null;
                    $status = $dayAttendance['status_code'] ?? null;
                    $row["Day {$day}"] = $status;
                }
            } else {
                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $currentDate = Carbon::createFromDate($year, $month, $day);
                    $currentDateString = $currentDate->format('Y-m-d');

                    // Check if current date is before joining date
                    if ($joiningDate && $currentDate->lt($joiningDate)) {
                        $row["Day {$day}"] = '--';
                        continue;
                    }

                    // Get attendance data for current date
                    $dayAttendance = $attendanceByDate[$currentDateString] ?? null;
                    $status = $dayAttendance['status_code'] ?? null;
                    $checkIn = $dayAttendance['checkInTime'] ?? '';
                    $checkOut = $dayAttendance['checkOutTime'] ?? '';

                    if(($checkIn && $checkIn!='-') && ($checkOut && $checkOut!='-')){
                        $row["Day {$day}"] = $checkIn . "\n" . $checkOut;
                    } else {
                        $row["Day {$day}"] = $status;
                    }
                }
            }

            $leave_types = [];
            $leavePolicy = PolicyLeave::with('fh_leave_type.fh_leave_cat_type')
                ->where('pl_b_id', $business->b_id)
                ->first();
            if ($leavePolicy) {
                $leave_types = $leavePolicy->fh_leave_type
                    ->pluck('fh_leave_cat_type.m_type')
                    ->filter()
                    ->toArray();
                    $leave_types[] = 'UPL';
            }

            // Calculate summary only from joining date onwards
            $validAttendanceData = [];
            foreach ($dailyAttendance as $attendance) {
                if (isset($attendance['date'])) {
                    $attendanceDate = Carbon::parse($attendance['date']);
                    if (!$joiningDate || $attendanceDate->gte($joiningDate)) {
                        $validAttendanceData[] = $attendance;
                    }
                }
            }

            $header1 = [
                'P' => array_sum(array_column($dailyAttendance, 'presentCount')) ?: '0',
                'HD' => array_sum(array_column($dailyAttendance, 'halfDayCount')) ?: '0',
                'L' => array_sum(array_column($dailyAttendance, 'leaveCount')) ?: '0',
            ];

            foreach($leave_types as $lt){
                $leaveHeads[$lt] = array_sum(array_column($dailyAttendance, $lt)) ?: '0';
            }

            $header2 = [
                'A' => array_sum(array_column($dailyAttendance, 'absentCount')) ?: '0',
                'WO' => array_sum(array_column($dailyAttendance, 'weekOffCount')) ?: '0',
                'HO' => array_sum(array_column($dailyAttendance, 'holidayCount')) ?: '0',
                'MSP' => array_sum(array_column($dailyAttendance, 'missedPunchCount')) ?: '0',
                'LC' => array_sum(array_column($dailyAttendance, 'lateCount')) ?: '0',
                'EG' => array_sum(array_column($dailyAttendance, 'earlyExitCount')) ?: '0',
                'OT' => array_sum(array_column($dailyAttendance, 'overtimeCount')) ?: '0',
            ];

            $row = array_merge($row, $header1, $leaveHeads, $header2);

            //this is total worked count
            $row['Total'] = array_sum([
                $row['P'],
                $row['WO'],
                $row['HO'],
            ]);

            $exportData[] = $row;

            $summaryHeader = array_merge(array_keys($header1),array_keys($leaveHeads),array_keys($header2),['Total']);
        }

        if($type=='MONTHLY_BASIC'){
            $fileName = "Monthly_Basic_{$month}_{$year}.xlsx";
             return  Excel::download(new MonthlyAttendanceBasicReport($exportData, $daysInMonth, $month, $year, $logoPath, $business,$summaryHeader), $fileName);
        }else{
            $fileName = "Monthly_In-Out_{$month}_{$year}.xlsx";
            return  Excel::download(new MonthlyInOutReport($exportData, $daysInMonth, $month, $year, $logoPath, $business,$summaryHeader), $fileName);
        }
    }


    public function detailedmusterRollReport(Request $request)
    {
        $user = Auth::user();
        $month = $request->month ?? now()->format('m'); // Default to current month
        $year = $request->year ?? now()->format('Y'); // Default to current year
        $branch = Branch::where('br_b_id', $user->emp_b_id)->where('br_id', $request->branch)->first();
        $department = Department::where('d_b_id', $user->emp_b_id)->where('d_id', $request->department)->first();
        $designation = Designation::where('dg_b_id', $user->emp_b_id)->where('dg_id', $request->designation)->first();
        $grade = Grade::where('g_b_id', $user->emp_b_id)->where('g_id', $request->grade)->first();
        $businessId = $user->emp_b_id;

        $business = Business::find($businessId);
        $employees = Employee::select('emp_id', 'emp_code', 'emp_full_name', 'emp_email', 'emp_b_id', 'emp_pwo_id','emp_shift_type_id')
        ->where('emp_b_id', $businessId)
        ->where('emp_status', 71);
        if ($branch) {
            $employees->orWhere('emp_br_id', $branch->br_id);
        }

        if ($department) {
            $employees->where('emp_d_id', $department->d_id);
        }

        if ($designation) {
            $employees->where('emp_dg_id', $designation->dg_id);
        }

        if ($grade) {
            $employees->where('emp_grade_id', $grade->g_id);
        }

        $employees = $employees->get();

        if ($employees->isEmpty()) {
            return redirect()->back()->with('error', 'No employees found for this period.');
        }


        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $dateRange = $startDate->toPeriod($endDate);

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


        $attendanceDetails = [];
        $daysInMonth = Carbon::createFromFormat('Y-m', "{$year}-{$month}")->daysInMonth;

        foreach ($employees as $employee) {

            $weekOfDates = CentralLogics::getWeekOffDates($employee, $year, $month);

            $attendanceData = [];

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = Carbon::createFromFormat('Y-m-d', "{$year}-{$month}-{$day}");

                $dailyDetails = CentralLogics::newGetMonthlyAttendanceDetails($employee, $month, $year, $holidaysByDate, $weekOfDates);
                // dd($dailyDetails);

                $dailyDetails['date'] = $date->format('Y-m-d');

                if (in_array($date->format('Y-m-d'), $weekOfDates)) {
                    $dailyDetails['status'] = 'WO'; // Weekly Off
                    $dailyDetails['status_id'] = null;
                    $dailyDetails['status_code'] = null;
                    $dailyDetails['statusColor'] = '#cccccc'; // Default color for weekly off
                    $dailyDetails['checkInTime'] = '-';
                    $dailyDetails['checkOutTime'] = '-';
                    $dailyDetails['workingHour'] = '-';
                }

                $attendanceData[] = $dailyDetails;
            }

            $attendanceDetails[] = [
                'employee' => $employee,
                'attendanceDetails' => $attendanceData,
            ];
            // dump($attendanceDetails);
        }

        if ($request->has('export_excel')) {
            return $this->exportCheckInOutDataExcel($attendanceDetails, $month, $year, $business);
        }

        return view('admin.setting.attendance-details.muster-roll', compact('attendanceDetails', 'month', 'year'));
    }
    public function exportCheckInOutDataExcel($attendanceDetails, $month, $year, $business)
    {
        set_time_limit(0);

        $exportData = [];
        $daysInMonth = Carbon::createFromFormat('Y-m', "{$year}-{$month}")->daysInMonth;

        $logoFileName = $business->b_logo;

        if (filter_var($logoFileName, FILTER_VALIDATE_URL)) {
            $logoPath = $logoFileName;
        } else {
            $logoPath = asset($logoFileName);
        }

        foreach ($attendanceDetails as $entry) {
            $employee = $entry['employee'];
            $dailyAttendance = $entry['attendanceDetails'];

            // Initialize counters
            $summaryCounts = [
                'presentCount' => 0,
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
            ];

            $row = [
                'Employee ID' => $employee->emp_code,
                'Employee Name' => $employee->emp_full_name,
            ];

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = Carbon::createFromFormat('Y-m-d', "{$year}-{$month}-{$day}");

                $checkIn = $dailyAttendance[$day - 1]['checkInTime'] ?? '';
                $checkOut = $dailyAttendance[$day - 1]['checkOutTime'] ?? '';
                $status = $dailyAttendance[$day - 1]['status'] ?? '';

                // Increment relevant counters
                switch ($status) {
                    case 'Present':
                        $summaryCounts['presentCount']++;
                        break;
                    case 'Leave':
                        $summaryCounts['leaveCount']++;
                        break;
                    case 'Holiday':
                        $summaryCounts['holidayCount']++;
                        break;
                    case 'WO': // Weekly Off
                        $summaryCounts['weekOffCount']++;
                        break;
                    case 'Absent':
                        $summaryCounts['absentCount']++;
                        break;
                    case 'Half Day':
                        $summaryCounts['halfDayCount']++;
                        break;
                    case 'Both Time - Missed Punch':
                    case 'Out Time - Missed Punch':
                    case 'In Time - Missed Punch':
                        $summaryCounts['missedPunchCount']++;
                        break;
                    case 'Overtime':
                        $summaryCounts['overtimeCount']++;
                        break;
                    case 'Late':
                        $summaryCounts['lateCount']++;
                        break;
                    case 'Early Exit':
                        $summaryCounts['earlyExitCount']++;
                        break;
                    case 'Approved Leave':
                        $summaryCounts['approvedLeaveCount']++;
                        break;
                    case 'Approved Missed Punch':
                        $summaryCounts['approvedMissedPunchCount']++;
                        break;
                }

                if ($status == 'WO') {
                    $row["Day {$day}"] = 'WO';
                } elseif ($status) {
                    $row["Day {$day}"] = $dailyAttendance[$day - 1]['status_code'];
                } else {
                    $row["Day {$day}"] = "{$checkIn}\n{$checkOut}";
                }
            }

            // Append summary counts as additional columns
            $row = array_merge($row, [
                'P' => $summaryCounts['presentCount'],
                'HD' => $summaryCounts['halfDayCount'],
                'OT' => $summaryCounts['overtimeCount'],
                'Leave Count' => $summaryCounts['leaveCount'],
                'Holiday Count' => $summaryCounts['holidayCount'],
                'Week Off Count' => $summaryCounts['weekOffCount'],
                'Absent Count' => $summaryCounts['absentCount'],

                'Missed Punch Count' => $summaryCounts['missedPunchCount'],

                'Late Count' => $summaryCounts['lateCount'],
                'Early Exit Count' => $summaryCounts['earlyExitCount'],
                'Approved Leave Count' => $summaryCounts['approvedLeaveCount'],
                'Approved Missed Punch Count' => $summaryCounts['approvedMissedPunchCount'],
            ]);

            $exportData[] = $row;
        }

        $fileName = "CheckInOut_Report_{$month}_{$year}.xlsx";

        return Excel::download(new class($exportData, $daysInMonth, $month, $year, $logoPath, $business) implements
            \Maatwebsite\Excel\Concerns\FromArray,
            \Maatwebsite\Excel\Concerns\WithHeadings,
            \Maatwebsite\Excel\Concerns\WithEvents {
            private $data;
            private $daysInMonth;
            private $month;
            private $year;
            private $logoPath;
            private $business;

            public function __construct($data, $daysInMonth, $month, $year, $logoPath, $business)
            {
                $this->data = $data;
                $this->daysInMonth = $daysInMonth;
                $this->month = $month;
                $this->year = $year;
                $this->logoPath = $logoPath;
                $this->business = $business;
            }

            public function array(): array
            {
                return $this->data;
            }

            public function headings(): array
            {
                $headings = ['Employee ID', 'Employee Name'];

                for ($day = 1; $day <= $this->daysInMonth; $day++) {
                    $date = Carbon::createFromDate($this->year, $this->month, $day);
                    $formattedDate = $date->format("d\nM\ny\nD");
                    $headings[] = $formattedDate;
                }

                // Add headings for summary counts
                $headings = array_merge($headings, [
                    'Present Count',
                    'Leave Count',
                    'Holiday Count',
                    'Week Off Count',
                    'Absent Count',
                    'Half Day Count',
                    'Missed Punch Count',
                    'Overtime Count',
                    'Late Count',
                    'Early Exit Count',
                    'Approved Leave Count',
                    'Approved Missed Punch Count',
                ]);

                return $headings;
            }

            public function registerEvents(): array
            {
                return [
                    \Maatwebsite\Excel\Events\AfterSheet::class => function (\Maatwebsite\Excel\Events\AfterSheet $event) {
                        $sheet = $event->sheet;

                        $worksheet = $sheet->getDelegate();
                        $worksheet->freezePane('A3');

                        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                        $drawing->setPath($this->logoPath);
                        $drawing->setHeight(100);
                        $drawing->setCoordinates('A1');
                        $drawing->setOffsetX(10);
                        $drawing->setOffsetY(5);
                        $drawing->setWorksheet($worksheet);

                        $sheet->getColumnDimension('A')->setWidth(15);
                        $sheet->getRowDimension('1')->setRowHeight(100);

                        $sheet->insertNewRowBefore(1, 5);

                        // Business name styling
                        $sheet->mergeCells('D1:I1');
                        $sheet->setCellValue('D1', $this->business->b_name);
                        $sheet->getStyle('D1')->applyFromArray([
                            'font' => ['bold' => true, 'size' => 14],
                            'alignment' => ['horizontal' => 'left', 'vertical' => 'center'],
                        ]);

                        $sheet->mergeCells('D2:I2');
                        $sheet->setCellValue('D2', 'Check-In/Check-Out Report');
                        $sheet->getStyle('D2')->applyFromArray([
                            'font' => ['bold' => false, 'size' => 14],
                            'alignment' => ['horizontal' => 'left', 'vertical' => 'center'],
                        ]);

                        $monthName = Carbon::createFromFormat('m', $this->month)->format('F');
                        $startDate = Carbon::createFromDate($this->year, $this->month, 1)->format('d-m-Y');
                        $endDate = Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth()->format('d-m-Y');

                        $sheet->mergeCells('D3:I3');
                        $sheet->setCellValue('D3', "Date (From: {$startDate} To: {$endDate})");
                        $sheet->getStyle('D3')->applyFromArray([
                            'font' => ['bold' => false, 'size' => 12],
                            'alignment' => ['horizontal' => 'left', 'vertical' => 'center'],
                        ]);

                        $sheet->getStyle('A6:ZZ1000')->applyFromArray([
                            'alignment' => [
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                                'wrapText' => true,
                            ],
                        ]);

                        foreach (range('A', $worksheet->getHighestColumn()) as $col) {
                            $sheet->getColumnDimension($col)->setAutoSize(true);
                        }
                    },
                ];
            }
        }, $fileName, \Maatwebsite\Excel\Excel::XLSX);
    }


    public function selfiePunchReport(Request $request)
    {
        $user = Auth::user();
        $selectedDate = $request->date ?? now()->format('Y-m-d');
        $branch = Branch::where('br_b_id', $user->emp_b_id)->where('br_id', $request->branch)->first();
        $department = Department::where('d_b_id', $user->emp_b_id)->where('d_id', $request->department)->first();
        $designation = Designation::where('dg_b_id', $user->emp_b_id)->where('dg_id', $request->designation)->first();
        $grade = Grade::where('g_b_id', $user->emp_b_id)->where('g_id', $request->grade)->first();
        $businessId = $user->emp_b_id;
        $business = Business::find($businessId);

        if (!$business) {
            return redirect()->back()->with('error', 'Business not found.');
        }

        $employees = Employee::select('emp_id', 'emp_code', 'emp_full_name', 'emp_email', 'emp_b_id', 'emp_pwo_id')
            ->where('emp_b_id', $businessId)
            ->where('emp_status', 71);
        if ($branch) {
            $employees->orWhere('emp_br_id', $branch->br_id);
        }

        if ($department) {
            $employees->where('emp_d_id', $department->d_id);
        }

        if ($designation) {
            $employees->where('emp_dg_id', $designation->dg_id);
        }

        if ($grade) {
            $employees->where('emp_grade_id', $grade->g_id);
        }

        $employees = $employees->get();

        if ($employees->isEmpty()) {
            return redirect()->back()->with('error', 'No employees found for this period.');
        }

        $attendanceDetails = [];

        foreach ($employees as $employee) {
            $employeeId = $employee->emp_id;

            $attendanceRecord = DB::table('attendance_records')
                ->where('atd_emp_id', $employeeId)
                ->whereDate('atd_date', $selectedDate)
                ->first();

            $dailyDetails = [
                'date' => $selectedDate,
                'selfie_photo' => $attendanceRecord ? $attendanceRecord->atd_punchin_photo : '',
                'atd_punchin_location' => $attendanceRecord ? $attendanceRecord->atd_punchin_location : '',
                'atd_checkin_method_id' => $attendanceRecord ? $attendanceRecord->atd_checkin_method_id : '',
                'atd_date' => $attendanceRecord ? $attendanceRecord->atd_date : '',
                'atd_check_in_time' => $attendanceRecord ? $attendanceRecord->atd_check_in_time : '',
                'atd_check_out_time' => $attendanceRecord ? $attendanceRecord->atd_check_out_time : '',
                'atd_total_worked_hours' => $attendanceRecord ? $attendanceRecord->atd_total_worked_hours : '',

                'status' => $attendanceRecord ? $attendanceRecord->atd_attendance_status : 'Absent',
            ];

            $attendanceDetails[] = [
                'employee' => $employee,
                'attendanceDetails' => $dailyDetails,
            ];
        }

        if (!empty($attendanceDetails)) {
            return $this->exportSelfieAttendanceExcel($attendanceDetails, $business, $selectedDate);
        }

        return redirect()->back()->with('error', 'No selfie-related attendance records found.');
    }




    public function exportSelfieAttendanceExcel($attendanceDetails, $business, $selectedDate)
    {
        set_time_limit(0);

        $exportData = [];

        $logoPath = $business->b_logo ? $business->b_logo : '';

        foreach ($attendanceDetails as $entry) {
            $employee = $entry['employee'];
            $dailyAttendance = $entry['attendanceDetails'];

            $row = [
                'Employee ID' => $employee->emp_code,
                'Employee Name' => $employee->emp_full_name,
                'Location' => $dailyAttendance['atd_punchin_location'] ?? '--',
                'Generation Time / Upload Time' => $dailyAttendance['atd_punchin_location'] ?? '--',
                'Punch Date' => $dailyAttendance['atd_date'] ?? '--',
                'Punch In Time' => $dailyAttendance['atd_check_in_time'] ?? '--',
                'Punch Out Time' => $dailyAttendance['atd_check_out_time'] ?? '--',
                'Working Hours' => $dailyAttendance['atd_total_worked_hours'] ?? '--',


                'Selfie Photo' => '',
            ];

            $selfiePath = $dailyAttendance['selfie_photo'] ?? '--';


            if ($selfiePath !== '--' && $selfiePath !== null && !empty($selfiePath)) {
                if (is_string($selfiePath)) {
                    $selfiePath = json_decode($selfiePath, true);
                }

                if (is_array($selfiePath)) {
                    $selfiePath = $selfiePath[0] ?? null;
                }

                $row['Selfie Photo'] = $selfiePath ? ('https://dev.fixhr.app/' . $selfiePath) : '--';
            } else {
                $row['Selfie Photo'] = '--';
            }

            $exportData[] = $row;
        }

        $fileName = "Selfie_Attendance_Report_{$selectedDate}.xlsx";

        return Excel::download(new class($exportData, $logoPath, $business, $selectedDate) implements
            \Maatwebsite\Excel\Concerns\FromArray,
            \Maatwebsite\Excel\Concerns\WithHeadings,
            \Maatwebsite\Excel\Concerns\WithEvents {
            private $data;
            private $logoPath;
            private $business;
            private $selectedDate;

            public function __construct($data, $logoPath, $business, $selectedDate)
            {
                $this->data = $data;
                $this->logoPath = $logoPath;
                $this->business = $business;
                $this->selectedDate = $selectedDate;
            }

            public function array(): array
            {
                return $this->data;
            }

            public function headings(): array
            {
                return ['Emp Code', 'Emp Name', 'Location', 'Generation Time / Upload Time', 'Punch Date', 'Punch In Time', 'Punch Out Time', 'Working Hours', 'Selfie Image'];
            }

            public function registerEvents(): array
            {
                return [
                    \Maatwebsite\Excel\Events\AfterSheet::class => function (\Maatwebsite\Excel\Events\AfterSheet $event) {
                        $sheet = $event->sheet;
                        $worksheet = $sheet->getDelegate();

                        $worksheet->freezePane('A8');

                        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                        $drawing->setPath($this->logoPath);
                        $drawing->setHeight(100);
                        $drawing->setCoordinates('A1');
                        $drawing->setOffsetX(10);
                        $drawing->setOffsetY(5);
                        $drawing->setWorksheet($worksheet);

                        $sheet->mergeCells('D1:I1');
                        $sheet->setCellValue('D1', $this->business->b_name);
                        $sheet->getStyle('D1')->applyFromArray([
                            'font' => ['bold' => true, 'size' => 14],
                            'alignment' => ['horizontal' => 'left', 'vertical' => 'center'],
                        ]);

                        $sheet->mergeCells('D2:I2');
                        $sheet->setCellValue('D2', 'Selfie Attendance Report');
                        $sheet->getStyle('D2')->applyFromArray([
                            'font' => ['bold' => false, 'size' => 14],
                            'alignment' => ['horizontal' => 'left', 'vertical' => 'center'],
                        ]);

                        $selectedDate = Carbon::parse($request->date ?? now()->format('Y-m-d'));
                        $startDate = Carbon::parse($selectedDate)->format('d-m-Y');
                        $endDate = Carbon::parse($selectedDate)->format('d-m-Y');

                        $sheet->mergeCells('D3:I3');
                        $sheet->setCellValue('D3', "Date: {$startDate}");
                        $sheet->getStyle('D3')->applyFromArray([
                            'font' => ['bold' => false, 'size' => 12],
                            'alignment' => ['horizontal' => 'left', 'vertical' => 'center'],
                        ]);

                        $sheet->setCellValue('A7', 'Employee ID');
                        $sheet->setCellValue('B7', 'Employee Name');
                        $sheet->setCellValue('C7', 'Location');
                        $sheet->setCellValue('D7', 'Generation Time / Upload Time');
                        $sheet->setCellValue('E7', 'Punch Date');
                        $sheet->setCellValue('F7', 'Punch In Time');
                        $sheet->setCellValue('G7', 'Punch Out Time');

                        $sheet->setCellValue('H7', 'Working Hours');
                        $sheet->setCellValue('I7', 'Selfie Image');



                        $sheet->getStyle('A7:I7')->applyFromArray([
                            'font' => ['bold' => true],
                            'alignment' => [
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                                'wrapText' => true,
                            ],
                        ]);

                        foreach (range('A', 'I') as $col) {
                            $sheet->getColumnDimension($col)->setAutoSize(true);
                        }

                        $rowIndex = 8; // Data starts here
                        foreach ($this->data as $employeeData) {
                            $worksheet->fromArray(array_values($employeeData), null, "A{$rowIndex}");

                            foreach (range('A', $worksheet->getHighestColumn()) as $col) {
                                $worksheet->getStyle("{$col}{$rowIndex}")->applyFromArray([
                                    'alignment' => [
                                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                                        'wrapText' => true,  // Enable text wrapping
                                    ],
                                ]);
                            }

                            $selfieImageUrl = $employeeData['Selfie Photo'];
                            if (filter_var($selfieImageUrl, FILTER_VALIDATE_URL)) {
                                $imagePath = public_path(parse_url($selfieImageUrl, PHP_URL_PATH));

                                // Check if the image exists at the given path
                                if (file_exists($imagePath)) {
                                    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                                    $drawing->setPath($imagePath); // Set the image path from the URL
                                    $drawing->setHeight(50); // Adjust height of the image
                                    $drawing->setWidth(60);  // Adjust width of the image
                                    $drawing->setCoordinates("I{$rowIndex}"); // Specify the cell to insert the image
                                    $drawing->setWorksheet($worksheet); // Add the image to the worksheet

                                    // Center the image horizontally in the cell
                                    $drawing->getShadow()->setVisible(true); // Add a shadow to enhance the effect
                                    $drawing->getShadow()->setDirection(45); // Set shadow direction (optional)

                                    // To wrap text within the cell, set wrap text on the cell
                                    $worksheet->getStyle("I{$rowIndex}")->getAlignment()->setWrapText(true);
                                    $worksheet->getStyle("I{$rowIndex}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                                }
                            }

                            $rowIndex++;
                        }

                        foreach (range(8, $worksheet->getHighestRow()) as $row) {
                            // Set row height
                            $worksheet->getRowDimension($row)->setRowHeight(100); // Adjust height for selfie images

                            foreach (range('A', $worksheet->getHighestColumn()) as $col) {
                                $worksheet->getStyle("{$col}{$row}")->applyFromArray([
                                    'alignment' => [
                                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                                        'wrapText' => true,  // Enable text wrapping
                                    ],
                                ]);
                            }
                        }
                    },
                ];
            }
            private function getSelfiePhoto($dailyAttendance)
            {
                $selfiePath = $dailyAttendance['selfie_photo'] ?? '--';
                if ($selfiePath !== '--' && $selfiePath !== null && !empty($selfiePath)) {
                    if (is_string($selfiePath)) {
                        $selfiePath = json_decode($selfiePath, true);
                    }

                    if (is_array($selfiePath)) {
                        $selfiePath = $selfiePath[0] ?? null;
                    }

                    return $selfiePath ? ('https://dev.fixhr.app/' . $selfiePath) : '--';
                }
                return '--';
            }
        }, $fileName, \Maatwebsite\Excel\Excel::XLSX);
    }


    public function monthlySummaryReport()
    {
        dd("hi");
    }


    private function getWeekOfDates($month, $year)
    {
        // Generate dates for weekends based on company policy
        $weekends = [];
        $totalDays = Carbon::parse("{$year}-{$month}-01")->daysInMonth;
        for ($day = 1; $day <= $totalDays; $day++) {
            $date = Carbon::parse("{$year}-{$month}-{$day}");
            if ($date->isWeekend()) {
                $weekends[] = $date->format('Y-m-d');
            }
        }
        return $weekends;
    }

    private function addSummaryColumns($sheet, $startColumnIndex, $endColumnIndex, $title)
    {
        $startColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColumnIndex);
        $endColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($endColumnIndex);

        $sheet->mergeCells("{$startColumn}5:{$endColumn}5");
        $sheet->setCellValue("{$startColumn}5", $title);
        $sheet->getStyle("{$startColumn}5:{$endColumn}5")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
            ]
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    // public function musterRollReport(Request $request)
    // {
    //     $user = Auth::user();
    //     $month = $request->month;
    //     $year = $request->year;
    //     $businessId = $user->emp_b_id;

    //     $data = ['month' => $month, 'year' => $year, 'businessId' => $businessId];
    //     // Generate the PDF if necessary
    //     // if ($request->has('generate_pdf')) {
    //     //     return $this->musterRollrRollReportPDF(new Request($data), 'POST');
    //     // }

    //     // Retrieve employee data
    //     $EmpData = Employee::where('emp_b_id', $businessId)
    //         ->where('emp_status', 71)
    //         ->whereHas('attendance_record', function ($query) use ($businessId, $month, $year) {
    //             $query->where('atd_b_id', $businessId)
    //                 ->whereMonth('atd_date', $month)
    //                 ->whereYear('atd_date', $year);
    //         })
    //         ->get();

    //     if ($EmpData->isEmpty()) {
    //         return redirect()->back()->with('error', 'No records to export.');
    //     }

    //     $length = $EmpData->count();

    //     return Excel::download(
    //         new ReportAttendance($EmpData, $length, 9, $month, $year, 01, $businessId ?? 'All Branch'),
    //         'EmployeeMusterRollReport.xlsx',
    //         \Maatwebsite\Excel\Excel::XLSX
    //     );
    // }


    // private function musterRollrRollReportPDF(Request $request)
    // {
    //     $user = Auth::user();
    //     $businessId = $user->emp_b_id;
    //     $month = (int) $request->month;
    //     $year = (int) $request->year;
    //     $totalDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    //     $date = date('Y-m-d', strtotime($year . '-' . $month . '-' . $totalDays));

    //     $monthDay = $month == date('m') ? date('j') : cal_days_in_month(CAL_GREGORIAN, $month, $year);

    //     $business = Business::where('b_id', $businessId)->first();
    //     $business_name = $business->b_name ?? 'Not Found';

    //     $EmpData = Employee::where('emp_b_id', $businessId)->where('emp_status', 71)->whereHas('attendance_record', function ($query) use ($businessId, $month, $year) {
    //             $query->where('atd_b_id', $businessId)
    //                         ->whereMonth('atd_date', $month)
    //                         ->whereYear('atd_date', $year);
    //            })->get();

    //     $pdf = PDF::loadView('admin.setting.attendance-details.pdf-report-attendance.muster-roll-attendance', compact('business_name', 'EmpData', 'monthDay', 'month', 'year'));
    //     $pdf->setPaper('a4', 'landscape');
    //     $pdf->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true, 'margin_left' => 0, 'margin_right' => 0, 'margin_top' => 0, 'margin_bottom' => 0]);

    //     return $pdf->download('admin.setting.attendance-details.pdf-report-attendance.muster-roll-attendance');
    // }


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



    public function attendanceReport($slug=null){

       if(!in_array($slug,['daily-attendance','yearly-summery','monthly-attendance-detail','monthly-attendance-basic','monthly-attendance-in-out','selfie-attendance'])){
            abort(404);
        }
        return view('admin.setting.attendance-details.daily-attendance-detail-report',compact('slug'));

    }
    public function leaveReport($slug){
       if(!in_array($slug,['summery','deatails','balance'])){
            abort(404);
        }
        return view('admin.setting.attendance-details.daily-attendance-leave-report',compact('slug'));
    }

    public function monitoringReport($slug){


        if(!in_array($slug,['late-coming','early-going','half-day','present','missed-punch','absent','holiday-present','weekly-off-present-detailed','weekly-off-present-summary','comp-off','continuous-absent','continuous-leave'])){
            abort(404);
        }

    
        return view('admin.setting.attendance-details.monitoring-report',compact('slug'));

    }
}
