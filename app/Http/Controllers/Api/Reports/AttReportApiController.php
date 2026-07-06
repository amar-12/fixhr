<?php

namespace App\Http\Controllers\Api\Reports;

use App\Models\Employee;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use App\Models\FinancialYear;
use App\Helpers\CentralLogics;
use Illuminate\Support\Carbon;
use App\Models\AttendanceRecord;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Attendance\MonthlyInOutReport;
use App\Exports\Attendance\MonthlyDetailReport;
use App\Exports\Attendance\AttendanceMonthlyReport;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;

class AttReportApiController extends Controller
{
    public function attSummary(Request $request)
    {
        try {
            $user = Auth::user();
            $businessId = $user->emp_b_id;

            // Use the month input or fallback to current month
            $monthFilter = $request->input('month') ?? now()->format('Y-m');
            [$year, $month] = explode('-', $monthFilter);

            // Get all employees for the business (except role_id = 1, usually admin)
            $employees = Employee::with([
                'fh_designation:dg_id,dg_name',
                'fh_week_off_policy:pwo_id,pwo_name,pwo_recurrence_day_ids'
            ])
                ->where('emp_b_id', $businessId)
                ->where('emp_role_id', '!=', 1)
                ->get();

            $response = [];

            foreach ($employees as $employee) {
                $weekOffDates = CentralLogics::getWeekOffDates($employee, $year, $month);
                $attendanceData = CentralLogics::getMonthlyAttendanceSummary($employee, $monthFilter, $weekOffDates);

                $response[] = [
                    'emp_id' => $employee->emp_id,
                    'emp_code' => $employee->emp_code,
                    'emp_name' => $employee->emp_full_name,
                    'designation' => $employee->fh_designation->dg_name ?? '',
                    'present' => $attendanceData['presentCount'] ?? 0,
                    'week_off' => count($weekOffDates),
                    'holiday' => $attendanceData['holidayCount'] ?? 0,
                    'total_working_days' => ($attendanceData['presentCount'] ?? 0) + count($weekOffDates) + ($attendanceData['holidayCount'] ?? 0),
                    'absent' => $attendanceData['absentCount'] ?? 0,
                    'half_day' => $attendanceData['halfDayCount'] ?? 0,
                    'leave' => $attendanceData['leaveCount'] ?? 0,
                    'missed_punch' => $attendanceData['missedPunchCount'] ?? 0,
                    'overtime' => $attendanceData['overtimeCount'] ?? 0,
                    'late' => $attendanceData['lateCount'] ?? 0,
                    'early_exit' => $attendanceData['earlyExitCount'] ?? 0,
                ];
            }

            return response()->json([
                'status' => true,
                'month' => $monthFilter,
                'total_employees' => count($response),
                'data' => $response
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }


    public function generateReport($slug, Request $request)
    {
        $month = $request->input('month');
        $year  = $request->input('year');

        // Validate: कम से कम एक filter होना चाहिए
        if (!$month || !$year) {
            return response()->json([
                'status'  => false,
                'message' => 'Either Month & Year OR Start Date & End Date are required',
            ], 422);
        }

        // Example: filter logic
        switch ($slug) {
            case 'daily-attendance':
                $data = $this->getDailyAttendance($month, $year);
                break;

                case 'monthly-attendance-detail':
                    $data = $this->getMonthlyDetail($month, $year,$request);

                break;

            case 'monthly-attendance-basic':
                $data = $this->getMonthlyBasic($month, $year);
                break;

            case 'monthly-attendance-in-out':
                $data = $this->getMonthlyInOut($month, $year);
                break;

            case 'yearly-summery':
                $data = $this->getYearlySummary($year);
                break;

            default:
                return response()->json([
                    'status'  => false,
                    'message' => 'Invalid report type',
                ], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => $data
        ]);

    }

    // Dummy private methods
    private function getDailyAttendance($month, $year) {
        return ["report" => "Daily attendance for $month/$year"];
    }
    private function getMonthlyDetail($month, $year, Request $request)
    {
        // dd($request->all());
        $user  = Auth::user();
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $month = (int) $request->input('month');
        $year  = (int) $request->input('year');

        $targetDate = \Carbon\Carbon::createFromDate($year, $month, 1);

        // ✅ Financial Year निकालना (optional)
        $financialYear = FinancialYear::whereDate('fy_start_date', '<=', $targetDate)
            ->whereDate('fy_end_date', '>=', $targetDate)
            ->first();

        $fyStartDate   = $financialYear?->fy_start_date;
        $fyEndDate     = $financialYear?->fy_end_date;

        // ✅ Month range
        $monthStart   = Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd     = $monthStart->copy()->endOfMonth();
        $daysInMonth  = $monthStart->daysInMonth;

        $query = AttendanceRecord::with('fh_attendance_status')
            ->where('atd_b_id', $user->emp_b_id);

        if ($startDate && $endDate) {
            $query->whereDate('atd_date', '>=', $startDate)
                ->whereDate('atd_date', '<=', $endDate);
        } elseif ($month && $year) {
            $query->whereYear('atd_date', $year)
                ->whereMonth('atd_date', $month);
        }

        // // ✅ Attendance Records
        // $records = AttendanceRecord::with('fh_attendance_status')
        //     ->where('atd_b_id', $user->emp_b_id)
        //     ->whereYear('atd_date', $year)
        //     ->whereMonth('atd_date', $month)
        //     ->get();

        // if ($records->isEmpty()) {
        //     return response()->json([
        //         "status"  => false,
        //         "message" => "No records found for {$month}/{$year}"
        //     ], 404);
        // }
        $records = $query->get();
        // ✅ Group by employee
        $recordsByEmployee = $records->groupBy('atd_emp_id');
        $employees = Employee::whereIn('emp_id', $recordsByEmployee->keys())
            ->get()->keyBy('emp_id');

        $reportData = [];

        foreach ($recordsByEmployee as $empId => $employeeRecords) {
            $employee = $employees->get($empId);
            if (!$employee) {
                continue;
            }

            $empCode = $employee->emp_code ?? 'N/A';
            $empName = $employee->emp_full_name ?? 'N/A';

            // ✅ Filter records for this month
            $attendanceRecords = $employeeRecords->keyBy(function ($rec) {
                return Carbon::parse($rec->atd_date)->format('Y-m-d');
            });

            // ✅ Date Range Set करो
            if ($startDate && $endDate) {
                $loopStart = Carbon::parse($startDate);
                $loopEnd   = Carbon::parse($endDate);
            } else {
                $loopStart = $monthStart;
                $loopEnd   = $monthEnd;
            }

            $dailyData = [];
            $statusCounts = [];

            $period = CarbonPeriod::create($loopStart, $loopEnd);

            foreach ($period as $dateCarbon) {
                $day  = $dateCarbon->day;
                $date = $dateCarbon->format('Y-m-d');

                $atd = $attendanceRecords->get($date);

                $status = $atd?->fh_attendance_status->m_type ?? 'ABS';

                // ✅ Count increment
                if (!isset($statusCounts[$status])) {
                    $statusCounts[$status] = 0;
                }
                $statusCounts[$status]++;

                $dailyData[] = [
                    "day"       => $day,
                    "date"      => $date,
                    "status"    => $status,
                    "in_time"   => $atd?->atd_check_in_time ? Carbon::parse($atd->atd_check_in_time)->format('H:i') : null,
                    "out_time"  => $atd?->atd_check_out_time ? Carbon::parse($atd->atd_check_out_time)->format('H:i') : null,
                    "work_hrs"  => $atd?->atd_total_worked_hours ?? null,
                ];
            }


            // $dailyData = [];
            // $statusCounts = []; // 👈 हर status का total रखने के लिए

            // for ($day = 1; $day <= $daysInMonth; $day++) {
            //     $dateCarbon = $monthStart->copy()->day($day);
            //     $date = $dateCarbon->format('Y-m-d');

            //     $atd = $attendanceRecords->get($date);

            //     $status = $atd?->fh_attendance_status->m_type ?? 'ABS';

            //     // ✅ Count increment
            //     if (!isset($statusCounts[$status])) {
            //         $statusCounts[$status] = 0;
            //     }
            //     $statusCounts[$status]++;

            //     $dailyData[] = [
            //         "day"       => $day,
            //         "date"      => $date,
            //         "status"    => $status,
            //         "in_time"   => $atd?->atd_check_in_time ? Carbon::parse($atd->atd_check_in_time)->format('H:i') : null,
            //         "out_time"  => $atd?->atd_check_out_time ? Carbon::parse($atd->atd_check_out_time)->format('H:i') : null,
            //         "work_hrs"  => $atd?->atd_total_worked_hours ?? null,
            //     ];
            // }

            $reportData[] = [
                "emp_id"       => $empId,
                "emp_code"     => $empCode,
                "emp_name"     => $empName,
                "days"         => $dailyData,
                "status_count" => $statusCounts   // 👈 New Added
            ];
        }

        // ✅ JSON Response
        return response()->json([
            "status"        => true,
            "report_type"   => "Monthly Detail Report",
            "month"         => $month,
            "year"          => $year,
            "financialYear" => [
                "start" => $fyStartDate,
                "end"   => $fyEndDate,
            ],
            "data"          => $reportData
        ]);
    }

    private function getMonthlyBasic($month, $year) {
        return ["report" => "Monthly basic for $month/$year", "data" => []];
    }
    private function getMonthlyInOut($month, $year) {
        return ["report" => "Monthly in/out for $month/$year", "data" => []];
    }
    private function getYearlySummary($year) {
        return ["report" => "Yearly summary for $year", "data" => []];
    }


    public function generateAttReport(Request $request, $slug)
    {
        try {
            $businessId = $request->user()->emp_b_id;

            // ✅ Monthly reports ke liye validation
            if (in_array($slug, ['monthly-attendance-basic', 'monthly-attendance-in-out', 'monthly-attendance-detail'])) {
                $request->validate([
                    'selectedYear' => 'required|exists:financial_years,fy_id',
                    'selectedMonth' => 'required|numeric|between:1,12',
                    'selectedDepartmentId' => 'nullable|exists:departments,d_id',
                    'selectedShiftId' => 'nullable|exists:policy_shift_timings,pst_id',
                    'selectedDealerId' => 'nullable|exists:dealerships,dlr_id',
                    'selectedWorkModeId' => 'nullable|exists:master_table,m_id',
                    'selectedDesignationId' => 'nullable|exists:designations,dg_id',
                    'selectedCheckingMethodId' => 'nullable|exists:master_table,m_id',
                    'selectedBranchId' => 'nullable|exists:branches,br_id',
                    'selectedJobStatusId' => 'nullable|exists:master_table,m_id',
                    'selectedGradeId' => 'nullable|exists:master_table,m_id',
                    'selectedEmployeeId' => 'nullable|exists:employees,emp_id',
                ]);

                // ✅ Financial year handling
                $financialYear = FinancialYear::findOrFail($request->selectedYear);
                $fyStartDate   = $financialYear->fy_start_date;
                $fyEndDate     = $financialYear->fy_end_date;
                $startYear     = Carbon::parse($fyStartDate)->year;
                $endYear       = Carbon::parse($fyEndDate)->year;

                $selectedYear = $startYear;
                if ($request->selectedMonth < Carbon::parse($fyStartDate)->month) {
                    $selectedYear = $endYear;
                }

                // ✅ Attendance query
                $query = AttendanceRecord::with([
                    'fh_employees_details',
                    'fh_attendance_checkin_type',
                    'fh_policy_shift_timing',
                    'fh_attendance_status',
                    'fh_business',
                    'attendance_exceptions',
                ])
                    ->where('atd_b_id', $businessId)
                    ->whereYear('atd_date', $selectedYear)
                    ->whereMonth('atd_date', $request->selectedMonth);

                // ✅ Apply filters dynamically
                if ($request->filled('selectedDepartmentId')) {
                    $query->whereHas('fh_employees_details', fn($q) =>
                    $q->where('emp_d_id', $request->selectedDepartmentId));
                }
                if ($request->filled('selectedShiftId')) {
                    $query->where('atd_pst_id', $request->selectedShiftId);
                }
                if ($request->filled('selectedDealerId')) {
                    $query->whereHas('fh_employees_details', fn($q) =>
                    $q->where('emp_dlr_id', $request->selectedDealerId));
                }
                if ($request->filled('selectedWorkModeId')) {
                    $query->where('atd_work_mode_type_id', $request->selectedWorkModeId);
                }
                if ($request->filled('selectedDesignationId')) {
                    $query->whereHas('fh_employees_details', fn($q) =>
                    $q->where('emp_dg_id', $request->selectedDesignationId));
                }
                if ($request->filled('selectedCheckingMethodId')) {
                    $query->where('atd_checkin_method_id', $request->selectedCheckingMethodId);
                }
                if ($request->filled('selectedBranchId')) {
                    $query->whereHas('fh_employee', fn($q) =>
                    $q->where('emp_br_id', $request->selectedBranchId));
                }
                if ($request->filled('selectedJobStatusId')) {
                    $query->join('employees', 'employees.emp_id', '=', 'attendance_records.atd_emp_id')
                        ->where('employees.emp_job_status', $request->selectedJobStatusId);
                }
                if ($request->filled('selectedGradeId')) {
                    $query->whereHas('fh_employees_details', fn($q) =>
                    $q->where('emp_grade_id', $request->selectedGradeId));
                }
                if ($request->filled('selectedEmployeeId')) {
                    $query->where('atd_emp_id', $request->selectedEmployeeId);
                }

                $records = $query->get();

                if ($records->isEmpty()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'No records found for the selected criteria.'
                    ], 404);
                }

                // ✅ File name
                $fileName = ucfirst($slug) . '_Report_' . now()->format('Y-m-d') . '.xlsx';

                // ✅ Agar json format chahiye
                if ($request->get('format') === 'json') {
                    if ($slug === 'monthly-attendance-basic') {
                        $report = new AttendanceMonthlyReport($records, $slug, $selectedYear, $request->selectedMonth, $fyStartDate, $fyEndDate, $businessId);
                    } elseif ($slug === 'monthly-attendance-in-out') {
                        $report = new MonthlyInOutReport($records, $slug, $selectedYear, $request->selectedMonth, $fyStartDate, $fyEndDate, $businessId);
                    } else {
                        $report = new MonthlyDetailReport($records, $slug, $selectedYear, $request->selectedMonth, $fyStartDate, $fyEndDate, $businessId);
                    }

                    return response()->json([
                        'status'  => true,
                        'message' => ucfirst(str_replace('-', ' ', $slug)) . " Data",
                        'data'    => $report->toArrayData() // 👈 yaha array data return hoga
                    ]);
                }

                // ✅ Default: Excel file download
                if ($slug === 'monthly-attendance-basic') {
                    return Excel::download(new AttendanceMonthlyReport($records, $slug, $selectedYear, $request->selectedMonth, $fyStartDate, $fyEndDate, $businessId), $fileName);
                } elseif ($slug === 'monthly-attendance-in-out') {
                    return Excel::download(new MonthlyInOutReport($records, $slug, $selectedYear, $request->selectedMonth, $fyStartDate, $fyEndDate, $businessId), $fileName);
                } else {
                    return Excel::download(new MonthlyDetailReport($records, $slug, $selectedYear, $request->selectedMonth, $fyStartDate, $fyEndDate, $businessId), $fileName);
                }
            }

            // ✅ Other reports (JSON only)
            switch ($slug) {
                case 'daily-attendance':
                    return response()->json(['status' => true, 'report_type' => 'Daily Attendance Report', 'data' => []]);
                case 'yearly-summery':
                    return response()->json(['status' => true, 'report_type' => 'Yearly Summary Report', 'data' => []]);
                case 'selfie-attendance':
                    return response()->json(['status' => true, 'report_type' => 'Selfie Attendance Report', 'data' => []]);
                default:
                    return response()->json(['status' => false, 'message' => 'Invalid report slug'], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Server error: ' . $e->getMessage(),
            ], 500);
        }
    }



}
