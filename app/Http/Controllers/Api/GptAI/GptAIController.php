<?php

namespace App\Http\Controllers\Api\GptAI;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Helpers\CentralLogics;
use App\Models\Employee;
use App\Models\AttendanceRecord;
use App\Models\AttendanceLog;
use App\Models\AttendanceException;
use App\Models\LeaveRequest;
use App\Models\PolicyHolidayList;
use App\Models\PolicyWeekOff;
use App\Models\MasterTable;
use App\Models\PolicyTadaTravelType;

class GptAIController extends Controller
{
    public function monthlyAttendanceSingleAPI2(Request $request)
    {
        $user = Auth::user();

        $month = $request->month ?? now()->format('Y-m');
        [$year, $monthNum] = explode('-', $month);

        $start = Carbon::create($year, $monthNum, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();
        $dateRange = CarbonPeriod::create($start, $end);

        $businessId = $user->emp_b_id;

        // ================= EMPLOYEES =================
        $employees = Employee::with('fh_week_off_policy')->where('emp_b_id', $businessId)
            ->where('emp_role_id', '!=', 1)
            // ->select('emp_id','emp_full_name','emp_code')
            ->get()
            ->keyBy('emp_id');

        if ($employees->isEmpty()) {
            return response()->json([
                'status' => true,
                'message' => 'No employees found',
                'data' => []
            ]);
        }

        $empIds = $employees->keys();

        // ================= MASTER CACHE =================
        $masters = MasterTable::whereIn('m_id',[203,251,321,322])
            ->get()->keyBy('m_id');

        // ================= RECORDS =================
        $records = AttendanceRecord::whereBetween('atd_date', [$start, $end])
            ->whereIn('atd_emp_id', $empIds)
            ->get()
            ->groupBy('atd_emp_id');

        // ================= LOGS =================
        $logs = AttendanceLog::whereBetween('al_date', [$start, $end])
            ->whereIn('al_emp_id', $empIds)
            ->get()
            ->groupBy('al_emp_id');

        // ================= EXCEPTIONS =================
        $exceptions = AttendanceException::whereBetween('ae_date', [$start, $end])
            ->whereIn('ae_emp_id', $empIds)
            ->where('ae_stage_completed', 1)
            ->get()
            ->groupBy('ae_emp_id');

        // ================= LEAVES =================
        $leaves = LeaveRequest::whereIn('lvr_emp_id', $empIds)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('lvr_start_date', [$start, $end])
                  ->orWhereBetween('lvr_end_date', [$start, $end])
                  ->orWhere(function ($q2) use ($start, $end) {
                      $q2->where('lvr_start_date', '<=', $start)
                         ->where('lvr_end_date', '>=', $end);
                  });
            })
            ->get()
            ->groupBy('lvr_emp_id');

        // ================= HOLIDAYS =================
        $holidays = PolicyHolidayList::where('phl_b_id', $businessId)
            ->where('phl_day_type_id', 201)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('phl_start_date', [$start, $end])
                  ->orWhereBetween('phl_end_date', [$start, $end]);
            })
            ->get();

        $holidayDates = [];

        foreach ($holidays as $holiday) {
            $hStart = Carbon::parse($holiday->phl_start_date);
            $hEnd   = Carbon::parse($holiday->phl_end_date);

            while ($hStart->lte($hEnd)) {
                $holidayDates[] = $hStart->toDateString();
                $hStart->addDay();
            }
        }

        $holidayMap = array_flip($holidayDates);

        // ================= WEEK OFF =================
        // $weekoffDays = PolicyWeekOff::pluck('day')->toArray();


        $finalData = [];

        // ================= MAIN LOOP =================
        foreach($employees as $empId => $employee){

            $salaryDays = 0;
            $overtimeMinutes = 0;

            $empLogs = collect($logs[$empId] ?? [])->keyBy('al_date');
            $empLeaves = collect($leaves[$empId] ?? []);
            $empExceptions = collect($exceptions[$empId] ?? [])->keyBy('ae_date');

            $attendanceCompact = [];

            $weekoffDays = CentralLogics::getWeekOffDates($employee, $year, $monthNum);

            foreach($dateRange as $dateObj){

                $date = $dateObj->toDateString();
                $status = 'A';

                // Holiday
                if(isset($holidayMap[$date])){
                    $status = 'HO';
                }
                // Weekoff
                elseif(in_array($date, $weekoffDays)){
                    $status = 'WO';
                }
                // Leave
                elseif($this->isLeaveDate($date,$empLeaves)){
                    $status = 'L';
                    $salaryDays++;
                }
                // Missed Punch
                elseif(isset($empExceptions[$date])){
                    $status = 'MP';
                }
                // Present
                elseif(isset($empLogs[$date])){
                    $log = $empLogs[$date];
                    $status = 'P';
                    $salaryDays++;

                    $worked = $log->al_total_worked_minutes ?? 0;
                    if($worked > 480){
                        $overtimeMinutes += ($worked - 480);
                    }
                }

                $attendanceCompact[$date] = $status;
            }

            $finalData[] = [
                'emp_id' => $employee->emp_id,
                'name'   => $employee->emp_full_name,
                'code'   => $employee->emp_code,
                'salary_days' => $salaryDays,
                'overtime_hours' => round($overtimeMinutes/60,2),
                'attendance' => $attendanceCompact
            ];
        }

        return response()->json([
            'status'=>true,
            'month'=>$month,
            'employee_count'=>count($finalData),
            'data'=>$finalData
        ]);
    }

    private function isLeaveDate($date,$leaves)
    {
        foreach($leaves as $leave){
            if($date >= $leave->lvr_start_date && $date <= $leave->lvr_end_date){
                return true;
            }
        }
        return false;
    }

    public function monthlyAttendanceSingleAPI(Request $request)
    {
        $user = Auth::user();

        $monthYear = $request->month ?? now()->format('Y-m');
        [$year, $month] = explode('-', $monthYear);

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();
        $dateRange = CarbonPeriod::create($start, $end);

        // Pre-load holidays for better performance
        $holiday_record_exits = PolicyHolidayList::where('phl_b_id', $user->emp_b_id)->where('phl_day_type_id', 201)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('phl_start_date', [$start, $end])
                    ->orWhereBetween('phl_end_date', [$start, $end]);
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

        $employees = Employee::where('emp_b_id', $user->emp_b_id)
            ->where('emp_role_id', '!=', 1)
            // ->select('emp_id','emp_full_name','emp_code')
            ->get()
            ->keyBy('emp_id');

        if ($employees->isEmpty()) {
            return response()->json([
                'status' => true,
                'message' => 'No employees found',
                'data' => []
            ]);
        }

        $filledAttendanceData = [];
        foreach ($employees as $employee) {
            $summary = [
                'presentCount' => 0,
                'holidayPresentCount' => 0,
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
                'UPLCount' => 0,
                'totalSalariedDays' => 0,
                'totalOTHrs' => 0,
            ];
            $weekOfDates = CentralLogics::getWeekOffDates($employee, $year, $month);

            $attendanceData = CentralLogics::newGetMonthlyAttendanceDetailsGPT($employee, $month, $year, $holidaysByDate, $weekOfDates);

            foreach ($attendanceData as $day) {
                $summary['presentCount'] += $day['presentCount'] ?? 0;
                $summary['holidayPresentCount'] += $day['holidayPresentCount'] ?? 0;
                $summary['weekOffPresentCount'] += $day['weekOffPresentCount'] ?? 0;
                $summary['leaveCount'] += $day['leaveCount'] ?? 0;
                $summary['holidayCount'] += $day['holidayCount'] ?? 0;
                $summary['weekOffCount'] += $day['weekOffCount'] ?? 0;
                $summary['absentCount'] += $day['absentCount'] ?? 0;
                $summary['halfDayCount'] += $day['halfDayCount'] ?? 0;
                $summary['missedPunchCount'] += $day['missedPunchCount'] ?? 0;
                $summary['lateCount'] += $day['lateCount'] ?? 0;
                $summary['earlyExitCount'] += $day['earlyExitCount'] ?? 0;

                $summary['approvedLeaveCount'] += $day['approvedLeaveCount'] ?? 0;
                $summary['approvedMissedPunchCount'] += $day['approvedMissedPunchCount'] ?? 0;
                $summary['UPLCount'] += $day['UPLCount'] ?? 0;
                $summary['totalOTHrs'] += $day['overtimeMinutes'] ?? 0;
            }

            $summary['totalSalariedDays'] =
                  $summary['presentCount']
                + $summary['holidayPresentCount']
                + $summary['weekOffPresentCount']
                + $summary['approvedLeaveCount']
                + $summary['holidayCount']
                + $summary['weekOffCount'];

            $summary['totalOTHrs'] = round($summary['totalOTHrs'] / 60, 2);
            $filledAttendanceData[] = [
                'emp_id'    => $employee->emp_id,
                'emp_name'  => $employee->emp_full_name ?? null,
                'emp_code'  => $employee->emp_code ?? null,
                'emp_email' => $employee->emp_email ?? null,
                'emp_phone' => $employee->emp_phone ?? null,

                // optional mobile optimization
                'summary' => $summary,
                'attendance' => $attendanceData,
            ];
        }

        return response()->json([
            'status' => true,
            'month' => $monthYear,
            'employees' => $filledAttendanceData
        ]);
    }
}