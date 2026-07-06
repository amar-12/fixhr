<?php

namespace App\Http\Controllers\Payroll;

use App\Helpers\CentralLogics;
use App\Http\Controllers\Controller;
use App\Models\AttendanceSummary;
use App\Models\Employee;
use App\Models\PolicyHolidayList;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayrollRecordsController extends Controller {

    protected $user;

    public function __construct() {
        $this->user = Auth::user();
    }

    public function freezeAttendance( Request $request ) {
        $user = Auth::user();

        $businessId = $request->business_id;
        $branchId = $request->branch_id;
        $month = $request->freeze_month;
        $departmentId = $request->freeze_department;


        $year = Carbon::createFromFormat('Y-m', $month)->year;
        $month = Carbon::createFromFormat('Y-m', $month)->format('m');



        $employees = Employee::where( 'emp_b_id', $businessId )
        ->where( 'emp_br_id', $branchId )
        ->where( 'emp_d_id', $departmentId )
        ->get();

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $dateRange = $startDate->toPeriod($endDate);

        $holiday_record_exits = PolicyHolidayList::where('phl_b_id', $user->emp_b_id)->where(function ($q) use ($startDate, $endDate) {
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



        $daysInMonth = Carbon::createFromFormat( 'Y-m', "{$year}-{$month}" )->daysInMonth;
        $attendanceDetails = [];

        foreach ( $employees as $employee ) {
            $weekOfDates = CentralLogics::getWeekOffDates( $employee, $year, $month );
            $attendanceData = [
                'total_days' => $daysInMonth,
                'presentCount' => 0,
                'halfDayCount' => 0,
                'leaveCount' => 0,
                'absentCount' => 0,
                'weekOffCount' => 0,
                'holidayCount' => 0,
                'missedPunchCount' => 0,
                'lateCount' => 0,
                'overtimeCount' => 0
            ];

            for ( $day = 1; $day <= $daysInMonth; $day++ ) {
                $date = Carbon::createFromFormat( 'Y-m-d', "{$year}-{$month}-{$day}" );
                $dailyDetails = CentralLogics::newGetMonthlyAttendanceDetails($employee, $month, $year, $holidaysByDate, $weekOfDates);

                $attendanceData[ 'presentCount' ] += $dailyDetails[ 'presentCount' ] ?? 0;
                $attendanceData[ 'halfDayCount' ] += $dailyDetails[ 'halfDayCount' ] ?? 0;
                $attendanceData[ 'leaveCount' ] += $dailyDetails[ 'leaveCount' ] ?? 0;
                $attendanceData[ 'absentCount' ] += $dailyDetails[ 'absentCount' ] ?? 0;
                $attendanceData[ 'weekOffCount' ] += $dailyDetails[ 'weekOffCount' ] ?? 0;
                $attendanceData[ 'holidayCount' ] += $dailyDetails[ 'holidayCount' ] ?? 0;
                $attendanceData[ 'missedPunchCount' ] += $dailyDetails[ 'missedPunchCount' ] ?? 0;
                $attendanceData[ 'lateCount' ] += $dailyDetails[ 'lateCount' ] ?? 0;
                $attendanceData[ 'overtimeCount' ] += $dailyDetails[ 'overtimeCount' ] ?? 0;
            }

            // Save the summary in `attendance_summaries` table
            AttendanceSummary::updateOrCreate(
                [
                    'as_emp_id' => $employee->emp_id,
                    'as_b_id' => $businessId,
                    'as_br_id' => $branchId,
                    'as_year_month' => "{$year}-{$month}",                ],
                [
                    'as_total_days' => $attendanceData[ 'total_days' ],
                    'as_total_present' => $attendanceData[ 'presentCount' ],
                    'as_total_missed_punch' => $attendanceData[ 'missedPunchCount' ],
                    'as_total_half_day' => $attendanceData[ 'halfDayCount' ],
                    'as_total_absent' => $attendanceData[ 'absentCount' ],
                    'as_total_leave' => $attendanceData[ 'leaveCount' ],
                    'as_total_weekoff' => $attendanceData[ 'weekOffCount' ],
                    'as_total_holiday' => $attendanceData[ 'holidayCount' ],
                    'as_total_worked_days' => $attendanceData[ 'presentCount' ] + ( $attendanceData[ 'halfDayCount' ] * 0.5 ),
                    'as_days_late' => $attendanceData[ 'lateCount' ],
                    'as_total_overtime_hours' => $attendanceData[ 'overtimeCount' ]
                    ]
                );

                $attendanceDetails[] = [
                    'employee' => $employee,
                    'attendance_summary' => $attendanceData,
                ];
                // dd($attendanceDetails);
        }

        return response()->json( [ 'message' => 'Attendance frozen successfully!', 'data' => $attendanceDetails ] );
    }





}
