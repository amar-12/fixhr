<?php

namespace App\Http\Controllers;

use App\Helpers\CentralLogics;
use App\Helpers\ShiftResolver;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\MasterTable;
use App\Models\PolicyHolidayList;
use App\Models\PolicyShiftTiming;
use App\Models\ShiftCalendar;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EmployeeCalendarSampleExport;
use App\Imports\EmployeeCalendarImport;

class EmployeeCalendar extends Controller
{
	/**
	 * Display a listing of the resource.
	 */
	public function index(Request $request)
	{
		$user = Auth::user();
		$employees = Employee::where([
			['emp_b_id', $user->emp_b_id],
			['emp_role_id', '!=', 1]
		])->get();
		$shiftPolicies = PolicyShiftTiming::where('pst_b_id', $user->emp_b_id)->get();

		return view('admin.employees.employee-calendar', compact('employees', 'shiftPolicies'));
	}

	public function empCalendarSampleExport() {
        return Excel::download(new EmployeeCalendarSampleExport, 'bulk_assign_shift_upload_format.xlsx');
	}

	public function empCalendarImport(Request $request) {
		$request->validate([
            'import_file' => 'required|file|mimes:xlsx,csv',
        ]);

        $file = $request->file('import_file');
        $import = new EmployeeCalendarImport(Auth::user());

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

            // If no errors, redirect with success message
            return redirect()->back()->with('success', "Import completed successfully! Imported: {$summary['success']}, Skipped: {$summary['skipped']}");
            // return redirect()->back()->with('success', 'Import completed successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create(Request $request)
	{
		// AJAX request
		$user = Auth::user();
		$employee_id = $request->employee_id;

		$emp = Employee::where([
			['emp_id', $employee_id],
			['emp_b_id', $user->emp_b_id]
		])->first();

		$start = Carbon::parse($request->start) ?? Carbon::now()->startOfMonth();
		$end   = Carbon::parse($request->end) ?? Carbon::now()->endOfMonth();

		$attendanceStatus = MasterTable::where('m_group', 'ATTENDANCE_STATUS')->get();
		$leaveCategory    = MasterTable::whereIn('m_group', ['LEAVE_CATEGORY', 'LEAVE_CATEGORY_LWP'])->get();

		$weekoffs = CentralLogics::getWeekOffDatesReport($emp, "", "", $start, $end);

		$holidays = PolicyHolidayList::where([
			['phl_b_id', $user->emp_b_id],
			['phl_start_date', '>=', $start->toDateString()],
			['phl_end_date', '<=', $end->toDateString()]
		])->get();

		$leaves = LeaveRequest::where([
			['lvr_b_id', $user->emp_b_id],
			['lvr_emp_id', $employee_id],
			['lvr_stage_completed', 1],
			['lvr_status', '!=', 170]
		])
			->where(function ($query) use ($start, $end) {
				$query->whereBetween('lvr_start_date', [$start->toDateString(), $end->toDateString()])
					->orWhereBetween('lvr_end_date', [$start->toDateString(), $end->toDateString()])
					->orWhere(function ($q) use ($start, $end) {
						$q->where('lvr_start_date', '<', $start->toDateString())
							->where('lvr_end_date', '>', $end->toDateString());
					});
			})
			->get();

		// Calendar events
		$events = [];

		// Week Offs
		foreach ($weekoffs as $date) {
			$events[] = [
				'title' => 'Week Off',
				'start' => $date,
				'allDay' => true,
				'display' => 'background',
				'backgroundColor' => json_decode($attendanceStatus->where('m_id', 322)->first()->m_other, true)['color'],
				'prefetched' => true,
				'event_type' => 'WeekOff'
			];
		}

		// Leaves
		foreach ($leaves as $leave) {
			$events[] = [
				'title' => 'Leave: ' . $leave->fh_leave_cat_type->m_name . ' - ' . $leave->fh_leave_day_type->m_name,
				'start' => $leave->lvr_start_date,
				'end' => Carbon::parse($leave->lvr_end_date)->addDay()->toDateString(),
				'allDay' => true,
				'backgroundColor' => json_decode($leaveCategory->where('m_id', $leave->lvr_cat_type_id)->first()->m_other, true)['color'] . '80',
				'prefetched' => true,
				'event_type' => 'Leave'
			];
		}

		// Holidays
		foreach ($holidays as $holiday) {
			$events[] = [
				'title' => 'Holiday: ' . $holiday->phl_name,
				'start' => $holiday->phl_start_date,
				'end'   => $holiday->phl_end_date,
				'allDay' => true,
				'display' => 'background',
				'backgroundColor' => json_decode($attendanceStatus->where('m_id', 321)->first()->m_other, true)['color'],
				'prefetched' => true,
				'event_type' => 'Holiday'
			];
		}

		// Pre-build arrays for faster lookup
		$leaveDates = [];
		foreach ($leaves as $leave) {
			$period = CarbonPeriod::create($leave->lvr_start_date, $leave->lvr_end_date);
			foreach ($period as $d) {
				$leaveDates[$d->toDateString()] = true;
			}
		}

		$holidayDates = [];
		foreach ($holidays as $holiday) {
			$period = CarbonPeriod::create($holiday->phl_start_date, $holiday->phl_end_date);
			foreach ($period as $d) {
				$holidayDates[$d->toDateString()] = true;
			}
		}

		$weekoffDates = [];
		foreach ($weekoffs as $date) {
			$weekoffDates[Carbon::parse($date)->toDateString()] = true;
		}


		// ---- Shifts (using ShiftResolver per day) ----
		$current = $start->copy();
		while ($current->lte($end)) {
			$dateStr = $current->toDateString();

			// Skip if leave / holiday / weekoff
			if (isset($leaveDates[$dateStr]) || isset($holidayDates[$dateStr]) || isset($weekoffDates[$dateStr])) {
				$current->addDay();
				continue;
			}

			$resolved = ShiftResolver::resolveEmployeeShift($emp, $dateStr);

			if ($resolved && $resolved->shift) {
				$events[] = [
					'title' => $resolved->shift->pst_name,
					'start' => $dateStr,
					'end'   => $current->copy()->addDay()->toDateString(),
					'allDay' => true,
					'backgroundColor' => match ($resolved->resolved_from) {
						'calendar'   => '#ffe5e5',
						'department' => '#e5f7ff',
						'employee'   => '#e5ffe5',
						default      => '#f0f0f0',
					},
					'sc_id' => $resolved->resolved_from == 'calendar' ? $resolved->source?->sc_id : null,
					'resolved_from' => Str::ucfirst($resolved->resolved_from),
					'prefetched' => $resolved->resolved_from != 'calendar' || $current->lte(now()) ? true : false,
					'event_type' => 'Shift'
				];
			}

			$current->addDay();
		}

		return response()->json([
			'emp'    => $emp,
			'events' => $events,
		]);
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request)
	{
		// AJAX request to add a shift event
		$user = Auth::user();
		$employee_id = $request->employee_id;
		$pst_id = $request->pst_id;
		$is_date_range = $request->is_date_range;
		$startDate = $is_date_range ? $request->start_date : $request->assign_date;
		$endDate = $is_date_range ? $request->end_date : $request->assign_date;

		$shiftCalendar = ShiftCalendar::where([
			['sc_b_id', $user->emp_b_id],
			['sc_emp_id', $employee_id]
		])->where(function ($query) use ($startDate, $endDate) {
			$query->whereBetween('sc_start_date', [$startDate, $endDate])
				->orWhereBetween('sc_end_date', [$startDate, $endDate])
				->orWhere(function ($q) use ($startDate, $endDate) {
					$q->where('sc_start_date', '<', $startDate)
						->where('sc_end_date', '>', $endDate);
				});
		})->first();
		if (!$shiftCalendar) {
			$newShiftCalendar = ShiftCalendar::create([
				'sc_b_id' => $user->emp_b_id,
				'sc_emp_id' => $employee_id,
				'sc_pst_id' => $pst_id,
				'sc_start_date' => $startDate,
				'sc_end_date' => $endDate,
			]);

			$events[] = [
				'sc_id' => $newShiftCalendar->sc_id,
				'title' => $newShiftCalendar->shift->pst_name,
				'start' => $newShiftCalendar->sc_start_date,
				'end' => Carbon::parse($newShiftCalendar->sc_end_date)->addDay()->format('Y-m-d'),
				'allDay' => true,
				'backgroundColor' => '#ffe5e5',
				'resolved_from' => 'Calendar',
				'event_type' => 'Shift'
			];
			return response()->json(['status' => 'success', 'message' => 'Shift assigned successfully.', 'events' => $events], 200);
		} else {
			return response()->json(['status' => 'error', 'message' => 'Shift already assigned!']);
		}
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
		$user = Auth::user();
		$empShiftCalendar = ShiftCalendar::findOrFail($id);
		if ($empShiftCalendar && Carbon::parse($empShiftCalendar->sc_start_date)->lte(now())) {
			return response()->json(['status' => 'warning', 'message' => 'Past assigned policy cannot be deleted. Date from ' . $empShiftCalendar->sc_start_date->format('d-m-Y') . ' to ' . $empShiftCalendar->sc_end_date->format('d-m-Y')], 200);
		}
		if ($empShiftCalendar) {
			$empShiftCalendar->delete();
			return response()->json(['status' => 'success', 'message' => 'Assigned Policy deleted successfully.'], 200);
		} else {
			return response()->json(['status' => 'error', 'message' => 'Assigned Policy not found.'], 400);
		}
	}
}
