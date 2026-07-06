<?php
namespace App\Http\Controllers\Api\FixGpt;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\PolicyHolidayList;
use App\Helpers\CentralLogics;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
class MonitoringReportController extends Controller
{
    public function index(Request $request)
    {
        /* ================= VALIDATION ================= */

        $request->validate([
            'business_id' => 'required|integer',
            'reports' => 'required|array',
            'reports.*.type' => 'required|string',
            'reports.*.from_date' => 'required|date',
            'reports.*.to_date' => 'required|date|after_or_equal:reports.*.from_date'
        ]);

        $businessId = $request->business_id;
        $reports = $request->reports;

        /* ================= GLOBAL FILTERS ================= */

        $filters = collect([
            'employee_id'    => $request->employee_id,
            'department_id'  => $request->department_id,
            'designation_id' => $request->designation_id,
            'branch_id'      => $request->branch_id,
            'dealer_id'      => $request->dealer_id,
            'shift_id'       => $request->shift_id,
            'grade_id'       => $request->grade_id,
            'job_status_id'  => $request->job_status_id,
            'work_mode_id'   => $request->work_mode_id,
        ])->filter();

        /* ================= LOAD EMPLOYEES ONCE ================= */

        $employeeQuery = Employee::with([
            'fh_branch',
            'fh_department',
            'fh_designation'
        ])
        ->where('emp_b_id', $businessId)
        ->where('emp_role_id', '<>', 1);

        foreach ($filters as $key => $value) {
            $column = $this->mapFilterToColumn($key);
            if ($column) {
                $employeeQuery->where($column, $value);
            }
        }

        $employees = $employeeQuery->get();

        if ($employees->isEmpty()) {
            return response()->json([
                "status" => false,
                "message" => "No employees found"
            ], 404);
        }

        /* ================= REPORT TYPE MAP ================= */

        $slugMap = [
            'present' => 251,
            'absent' => 203,
            'half-day' => 252,
            'comp-off' => 204,
            'holiday-present' => 319,
        ];

        $responseData = [];

        /* ================= PROCESS EACH REPORT BLOCK ================= */

        foreach ($reports as $block) {

            $type = $block['type'];
            $fromDate = Carbon::parse($block['from_date']);
            $toDate = Carbon::parse($block['to_date']);

            $holidayRecords = $this->getHolidayRecords($businessId, $fromDate, $toDate);

            $blockRecords = [];

            foreach ($employees as $employee) {

                $weekOffDates = CentralLogics::getWeekOffDatesReport(
                    $employee, null, null,
                    $fromDate->toDateString(),
                    $toDate->toDateString()
                );

                $attendanceData = CentralLogics::newGetMonthlyAttendanceReportAuxiliary(
                    $employee, null, null,
                    $holidayRecords,
                    $weekOffDates,
                    $fromDate->toDateString(),
                    $toDate->toDateString()
                );

                foreach ($attendanceData as $data) {

                    if (!$this->matchesReportType($type, $data, $slugMap)) {
                        continue;
                    }

                    $blockRecords[] = [
                        "employee_id" => $employee->emp_id,
                        "employee_name" => $employee->emp_full_name,
                        "date" => $data['date'],
                        "status" => $data['status'],
                        "check_in" => $data['checkInTime'],
                        "check_out" => $data['checkOutTime'],
                        "late" => (bool)$data['lateCount'],
                        "early_exit" => (bool)$data['earlyExitCount']
                    ];
                }
            }

            $responseData[$type] = [
                "from_date" => $fromDate->toDateString(),
                "to_date" => $toDate->toDateString(),
                "total_records" => count($blockRecords),
                "data" => $blockRecords
            ];
        }

        return response()->json([
            "status" => true,
            "business_id" => $businessId,
            "filters_applied" => $filters,
            "reports" => $responseData
        ]);
    }

    private function matchesReportType($type, $data, $slugMap)
    {
        if ($type === 'late-coming') {
            return $data['lateCount'];
        }

        if ($type === 'early-going') {
            return $data['earlyExitCount'];
        }

        if (isset($slugMap[$type])) {
            return $data['status_id'] == $slugMap[$type];
        }

        return false;
    }

    private function getHolidayRecords($businessId, $fromDate, $toDate)
    {
        $holidayRecords = [];

        $holidays = PolicyHolidayList::where('phl_b_id', $businessId)
            ->whereBetween('phl_start_date', [$fromDate, $toDate])
            ->get();

        foreach ($holidays as $holiday) {
            $period = CarbonPeriod::create(
                $holiday->phl_start_date,
                $holiday->phl_end_date ?? $holiday->phl_start_date
            );

            foreach ($period as $date) {
                $holidayRecords[$date->toDateString()] = $holiday->phl_name;
            }
        }

        return $holidayRecords;
    }

    private function mapFilterToColumn($key)
    {
        return match ($key) {
            'employee_id' => 'emp_id',
            'department_id' => 'emp_d_id',
            'designation_id' => 'emp_dg_id',
            'branch_id' => 'emp_br_id',
            'dealer_id' => 'emp_dlr_id',
            'shift_id' => 'emp_shift_type_id',
            'grade_id' => 'emp_grade_id',
            'job_status_id' => 'emp_job_status',
            'work_mode_id' => 'emp_work_mode_id',
            default => null,
        };
    }
}
