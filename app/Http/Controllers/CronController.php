<?php

namespace App\Http\Controllers;

use App\Helpers\CentralLogics;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\PolicyHolidayList;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CronController extends Controller
{
	function attendancePreferenceDaily()
	{
		// Get all employees whose attendance preference is 370, i.e. check-in/check-out not required
		$emps = Employee::with('fh_shift_type')->where("emp_attendance_preference", 370)->get();

		foreach ($emps as $employee) {
			// Shift Timings
			$checkInTime = Carbon::parse($employee->fh_shift_type->pst_start_time);
			$checkOutTime = Carbon::parse($employee->fh_shift_type->pst_end_time);
			$workedHours = max(0, min(24, round(Carbon::parse($checkOutTime)->diffInMinutes(Carbon::parse($checkInTime)) / 60, 2)));

			// Check if the Current date is Week off or holiday
			$cuDate = Carbon::now();
			$weekOffDates = CentralLogics::getWeekOffDates($employee, null, null, $cuDate, $cuDate);
			$isHoliday = PolicyHolidayList::where('phl_b_id', $employee->emp_b_id)->whereDate('phl_start_date', '<=', $cuDate)->whereDate('phl_end_date', '>=', $cuDate)->exists();

			if (!$isHoliday && empty($weekOffDates)) {
				$record = AttendanceRecord::updateOrCreate(
					[
						'atd_b_id' => $employee->emp_b_id,
						'atd_emp_id' => $employee->emp_id,
						'atd_date' => $cuDate->format('Y-m-d'),
					],
					[
						'atd_check_in_time' => $checkInTime,
						'atd_check_out_time' => $checkOutTime,
						'atd_total_worked_hours' => $workedHours,
						'atd_attendance_status' => 251,
						'atd_pst_id' => $employee->fh_shift_type->pst_id ?? null,
						'atd_work_mode_type_id' => $employee->fh_work_mode->m_id,
						'atd_module_id' => 249,
						'atd_next_approver' => 1,
						'atd_request_status' => 140,
					]
				);

				\Log::info('Attendance of emp - ' . $employee->emp_full_name);
				\Log::info('Cron task ran at ' . now());
			}
		}
	}

	function attendancePreferenceMonthly()
	{
		// Get all employees whose attendance preference is 370, i.e. check-in/check-out not required
		$emps = Employee::with('fh_shift_type')->where("emp_attendance_preference", 370)->get();
		$currentDate = Carbon::now();

		foreach ($emps as $employee) {
			// Shift Timings
			$checkInTime = Carbon::parse($employee->fh_shift_type->pst_start_time);
			$checkOutTime = Carbon::parse($employee->fh_shift_type->pst_end_time);
			$workedHours = max(0, min(24, round(Carbon::parse($checkOutTime)->diffInMinutes(Carbon::parse($checkInTime)) / 60, 2)));

			$startDate = Carbon::parse($currentDate)->startOfMonth();
			$endDate = Carbon::parse($currentDate)->endOfMonth();

			$weekOffDates = CentralLogics::getWeekOffDates($employee, null, null, $startDate, $endDate);
			$holidayDates = PolicyHolidayList::where('phl_b_id', $employee->emp_b_id)
				->where(function ($q) use ($startDate, $endDate) {
					$q->whereBetween('phl_start_date', [$startDate, $endDate])
						->orWhereBetween('phl_end_date', [$startDate, $endDate])
						->orWhere(function ($q2) use ($startDate, $endDate) {
							$q2->where('phl_start_date', '<=', $startDate)
								->where('phl_end_date', '>=', $endDate);
						});
				})
				->get()
				->flatMap(fn($holiday) => Carbon::parse($holiday->phl_start_date)->daysUntil(Carbon::parse($holiday->phl_end_date))->map(fn($date) => $date->format('Y-m-d')))
				->unique()
				->values()
			->toArray();

			$cuDate = $startDate->copy();
			while ($cuDate->lte($endDate)) {
				$formattedDate = $cuDate->format("Y-m-d");
				// Check if the Current date is Week off or holiday
				if (!in_array($formattedDate, $holidayDates) && !in_array($formattedDate, $weekOffDates) && Carbon::parse($employee->emp_date_of_joining)->lte($cuDate)) {
					
					$record = AttendanceRecord::updateOrCreate(
						[
							'atd_b_id' => $employee->emp_b_id,
							'atd_emp_id' => $employee->emp_id,
							'atd_date' => $cuDate->format('Y-m-d'),
						],
						[
							'atd_check_in_time' => $checkInTime,
							'atd_check_out_time' => $checkOutTime,
							'atd_total_worked_hours' => $workedHours,
							'atd_attendance_status' => 251,
							'atd_pst_id' => $employee->fh_shift_type->pst_id ?? null,
							'atd_work_mode_type_id' => $employee->fh_work_mode->m_id,
							'atd_module_id' => 249,
							'atd_next_approver' => 1,
							'atd_request_status' => 140,
						]
					);

					\Log::info('Attendance of emp - ' . $employee->emp_full_name . " of date - " . $cuDate);
					\Log::info('Cron task ran at ' . now());
				}
				$cuDate->addDay();
			}
		}
	}
}
