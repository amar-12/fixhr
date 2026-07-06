<?php

namespace App\Http\Controllers;

use App\Helpers\CentralLogics;
use App\Models\AttendanceSummary;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\MasterTable;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use App\Http\Resources\MasterTableResource;
use App\Models\PolicyHolidayList;
use Illuminate\Support\Facades\Log;

class EmpLeaveCalendar extends Controller
{
	/**
	 * Display a listing of the resource.
	 */
	public function index()
	{
	
	$slug = 'employee-leave-calendar';
		return view('admin.setting.attendance-details.emp-leave-calendar',compact('slug'));
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request)
	{
		$user = Auth::user();

		// Prepare payload and add required fields before forwarding to API controller
		$data = $request->all();

		// Add or override the required fields
		$data['leave_start_date'] = Carbon::parse($data['leave_start_date'])->format('d M, Y');
		$data['leave_end_date'] = Carbon::parse($data['leave_end_date'])->format('d M, Y');
		$data['lvr_approved_by'] = $user->emp_id;
		$data['lvr_status'] = 171; // auto approved status
		$data['lvr_module_id'] = 250; // module id
		$data['lvr_stage_completed'] = 1; // mark stage completed

		// Create a new Request object with merged data and forward to the API controller
		$forwardReq = new Request($data);

		$apiController = app()->make(\App\Http\Controllers\Api\Attendance\EmployeeLeaveController::class);
		$response = $apiController->store($forwardReq);

		// If the API returned a Response instance, return it directly. Otherwise, json-encode.
		if ($response instanceof \Illuminate\Http\JsonResponse || $response instanceof \Illuminate\Http\Response) {
			return $response;
		}

		return response()->json($response);
	}

	/**
	 * Display the specified resource.
	 */
	public function show(string $id)
	{
		$user = Auth::user();
		// Fetch employee and ensure same business
		$employee = Employee::where('emp_id', $id)->where('emp_b_id', Auth::user()->emp_b_id)->first();
		if (! $employee) {
			return response()->json(['message' => 'Employee not found or unauthorized'], 404);
		}

		$isUplApplicable = $employee->fh_policy_leave->pl_upl_applicable;
		if ($isUplApplicable === true || $isUplApplicable === 1) {
			$UplLeave = MasterTableResource::collection(MasterTable::where('m_id', 215)->get());
		} else {
			$UplLeave = null;
		}

		$apiController = app()->make(\App\Http\Controllers\Api\Attendance\EmployeeLeaveController::class);
		$leaveBalance = $apiController->getLeaveBalance($id);

		// Optional date range filter from query params
		$startInput = request()->input('start');
		$endInput = request()->input('end');

		// Build dynamic conditions similar to LeaveController but scoped to the employee
		$dynamicConditions = [
			['method' => 'where', 'args' => ['lvr_b_id', Auth::user()->emp_b_id]],
			['method' => 'where', 'args' => ['lvr_emp_id', $employee->emp_id]],
			['method' => 'whereNull', 'args' => ['lvr_p_id']],
			['method' => 'where', 'args' => ['lvr_stage_completed', 1]],
			['method' => 'where', 'args' => ['lvr_status', '!=', 170]],
			['method' => 'select', 'args' => ['lvr_id', 'lvr_emp_id', 'lvr_cat_type_id', 'lvr_start_date', 'lvr_end_date', 'lvr_total_leave_days', 'lvr_status', 'lvr_reason', 'lvr_stage_completed', 'created_at'], 'relation' => ['fh_leave_cat_type:m_id,m_name,m_type', 'fh_employee:emp_id,emp_b_id,emp_full_name,emp_code,emp_br_id,emp_dg_id,emp_d_id,emp_status', 'fh_approval_status:m_id,m_name,m_other', 'fh_leave_day_type:m_id,m_name,m_other', 'fh_leave_day_segment:m_id,m_name,m_other']],
		];

		if ($startInput && $endInput) {
			$dynamicConditions[] = ['method' => 'where', 'args' => ['lvr_start_date', '<=', Carbon::parse($endInput)->format('Y-m-d')]];
			$dynamicConditions[] = ['method' => 'where', 'args' => ['lvr_end_date', '>=', Carbon::parse($startInput)->format('Y-m-d')]];
		}

		$searchColumns = ['lvr_start_date', 'lvr_end_date', 'lvr_cat_type_id', 'lvr_reason', 'lvr_total_leave_days', 'lvr_day_segment_id', 'lvr_documents', 'lvr_approved_by', 'lvr_am_id', 'lvr_status', 'lvr_module_id', 'lvr_status', 'lvr_stage_completed', 'updated_at', 'created_at'];
		$searchRelationships = ['fh_leave_cat_type' => ['m_name']];

		$helper = new DynamicModelDataTableHelper(
			eloquentModel: new LeaveRequest(),
			dynamicConditions: $dynamicConditions,
			searchColumns: $searchColumns,
			searchRelationships: $searchRelationships
		);

		$month = Carbon::parse($endInput)->format('m');
		$year = Carbon::parse($endInput)->format('Y');

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

		$weekOfDates = [];
		if (isset($employee->fh_week_off_policy) && $employee->fh_week_off_policy) {
			$occurrences = $this->getOccurrencesOfDaysInMonth($year, $month);
			foreach ($occurrences as $key => $dates) {
				$weekDays = $employee->fh_week_off_policy->getWeek(json_decode($employee->fh_week_off_policy->pwo_recurrence_day_ids));
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

		$monthlyAttendance = CentralLogics::newGetMonthlyAttendanceDetails($employee, $month, $year, $holidaysByDate, $weekOfDates);

		// Always fetch list for the employee (small-ish result expected)
		$list = $helper->getServerSideDataTable();

		// Exclude leaves that have status == 170 AND stage_complete == 1
		$listFiltered = collect($list)->reject(function ($row) {
			$status = data_get($row, 'lvr_status');
			$stage = data_get($row, 'lvr_stage_completed');
			return ((($status == 170) && ($stage !== null && $stage == 1)) || ($stage !== null && $stage == 0));
		})->values()->all();

		// Build table rows (DataTables-friendly)
		$tableRows = collect($list)->map(function ($row, $index) {
			$from = data_get($row, 'lvr_start_date');
			$to = data_get($row, 'lvr_end_date');
			return [
				'lvr_id' => data_get($row, 'lvr_id'),
				'category' => data_get($row, 'fh_leave_cat_type.m_name') ?? '',
				'from' => $from ? Carbon::parse($from)->format('d-M-y') : '',
				'to' => $to ? Carbon::parse($to)->format('d-M-y') : '',
				'days' => data_get($row, 'lvr_total_leave_days'),
				'status' => data_get($row, 'fh_approval_status.m_name') ?? data_get($row, 'lvr_status'),
				'reason' => data_get($row, 'lvr_reason') ?? '',
				'applied_on' => data_get($row, 'created_at') ? Carbon::parse(data_get($row, 'created_at'))->format('d-m-Y') : '',
				'approved_by' => data_get($row, 'fh_approver.emp_full_name') ?? '',
			];
		})->values()->toArray();

		// Build calendar events from the filtered leave list
		$calendarEvents = collect($listFiltered)->map(function ($row) {
			$start = data_get($row, 'lvr_start_date');
			$end = data_get($row, 'lvr_end_date');
			return [
				'id' => data_get($row, 'lvr_id'),
				'title' => data_get($row, 'fh_leave_cat_type.m_type') ?? 'Leave',
				'start' => $start ? Carbon::parse($start)->toDateString() : null,
				'end' => $end ? Carbon::parse($end)->addDay()->toDateString() : null,
				'allDay' => true,
				'extendedProps' => [
					'status' => data_get($row, 'fh_approval_status.m_name') ?? data_get($row, 'lvr_status'),
					'total_days' => data_get($row, 'lvr_total_leave_days'),
					'reason' => data_get($row, 'lvr_reason') ?? '',
					'_is_leave' => true,
				],
			];
		})->values()->toArray();

		// Add attendance-only events for days without leave
		$attendanceEvents = [];
		$leaveList = collect($listFiltered);
		foreach ($monthlyAttendance as $attRow) {
			$attDate = data_get($attRow, 'date');
			if (! $attDate) {
				continue;
			}

			// Skip if any leave covers this attendance date
			$hasLeave = $leaveList->contains(function ($row) use ($attDate) {
				$s = data_get($row, 'lvr_start_date');
				$e = data_get($row, 'lvr_end_date');
				if (! $s || ! $e) {
					return false;
				}
				$sd = Carbon::parse($s)->toDateString();
				$ed = Carbon::parse($e)->toDateString();
				return ($attDate >= $sd && $attDate <= $ed);
			});
			if ($hasLeave) {
				continue;
			}

			$status = data_get($attRow, 'status') ?? data_get($attRow, 'status_code') ?? '';
			$statusId = data_get($attRow, 'status_id');

			$attendanceEvents[] = [
				'id' => 'att_' . $attDate,
				'title' => $status ?: 'Attendance',
				'start' => Carbon::parse($attDate)->toDateString(),
				'end' => Carbon::parse($attDate)->addDay()->toDateString(),
				'allDay' => true,
				'extendedProps' => [
					'attendance_status' => $status,
					'attendance_status_id' => $statusId,
					'_is_leave' => false,
				],
			];
		}

		$calendarEvents = array_merge($calendarEvents, $attendanceEvents);

		return response()->json([
			'employee' => $employee,
			'table' => ['data' => $tableRows, 'recordsTotal' => count($tableRows)],
			'calendar' => $calendarEvents,
			'leaveBalance' => $leaveBalance,
			'UplLeave' => $UplLeave,
			'monthlyAttendance' => $monthlyAttendance,
		], 200);
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(string $id)
	{
		if (empty($id)) {
			return $response = ['status' => false, 'message' => 'Leave Request not found'];
		}

		$leave = LeaveRequest::where('lvr_id', $id)->where('lvr_b_id', Auth::user()->emp_b_id)->first();

		$att_summary = AttendanceSummary::where('as_emp_id', $leave->lvr_emp_id)->where('as_year_month', Carbon::parse($leave->lvr_start_date)->format('Y-m'))->first();
		$sal_processed = optional($att_summary)->as_is_sal_processed ?? 0;

		if ($sal_processed != 120 && ($leave->lvr_status == 171 || $leave->lvr_status == 140)) {
			$apiController = app()->make(\App\Http\Controllers\Api\Attendance\EmployeeLeaveController::class);
			$response = $apiController->destroy($id);
		} else {
			return $response = ['status' => false, 'message' => 'This leave request cannot be deleted because the salary has already been processed or the request was submitted by the employee.'];
		}
		return $response;
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

		Log::info('daysInMonth: ' . $daysInMonth);

		// Iterate through all days in the month
		for ($day = 1; $day <= 30; $day++) {
			$date = Carbon::create($year, $month, $day);
			$dayName = $date->format('l'); // Get the full day name (e.g., Sunday, Monday)
			$occurrences[$dayName][] = $date->toDateString();
		}

		return $occurrences;
	}
}
