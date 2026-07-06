<?php

namespace App\Console\Commands;

use App\Helpers\CentralLogics;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PastAbsentRecords extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'app:past-absent-records';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Inserts past absent records into the database';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$businessIds = [68];
		$summary = []; // Per-employee stats

		$emps = Employee::query()
			->join('businesses as b', 'employees.emp_b_id', '=', 'b.b_id')
			->with([
				'fh_shift_type',
				'fh_work_mode',
				'fh_week_off_policy',
			])
			->where('employees.emp_attendance_preference', '!=', 370)
			->where('employees.emp_role_id', '<>', 1)
			->where('b.b_status', 1)
			->whereIn('b.b_id', $businessIds)
			->get();

		foreach ($emps as $employee) {
			$joiningDate = $employee->emp_date_of_joining ? Carbon::parse($employee->emp_date_of_joining)->format('Y-m-d') : null;
			$startDate = $joiningDate ? $joiningDate : Carbon::parse($employee->business_created_at)->format('Y-m-d');

			$emp_last_working_date = $employee->emp_last_working_date ? Carbon::parse($employee->emp_last_working_date)->format('Y-m-d') : null;
			$endDate = $emp_last_working_date ?: Carbon::now()->format('Y-m-d');

			// 1 Generate all possible dates
			$allDates = collect(CarbonPeriod::create($startDate, $endDate))
				->map(fn($date) => $date->format('Y-m-d'))
				->toArray();

			// 2 Existing dates via UNION
			$existingDates = DB::table('attendance_records')
				->select('atd_date as date')
				->where('atd_emp_id', $employee->emp_id)
				->union(
					DB::table('attendance_exceptions')
						->select('ae_date as date')
						->where('ae_emp_id', $employee->emp_id)
				)
				->union(
					DB::table('attendance_log')
						->select('al_date as date')
						->where('al_emp_id', $employee->emp_id)
				)
				->pluck('date')
				->toArray();

			// 3 Week Offs
			$weekOffDates = CentralLogics::getWeekOffDatesReport($employee, null, null, $startDate, $endDate);

			// 4 Holidays
			$holidays = DB::table('policy_holiday_list')
				->where('phl_b_id', $employee->emp_b_id)
				->where(function ($q) use ($startDate, $endDate) {
				    $q->whereBetween('phl_start_date', [$startDate, $endDate])
					->orWhereBetween('phl_end_date', [$startDate, $endDate]);
				})
				->get();

			$holidayDates = [];
			foreach ($holidays as $holiday) {
				$period = CarbonPeriod::create($holiday->phl_start_date, $holiday->phl_end_date);
				foreach ($period as $date) {
					$holidayDates[] = $date->format('Y-m-d');
				}
			}

			// 5 Leave Records
			$leaveRecords = LeaveRequest::where([
				['lvr_b_id', $employee->emp_b_id],
				['lvr_emp_id', $employee->emp_id],
				['lvr_stage_completed', 1],
				['lvr_status', '!=', 170]
			])
				->where(function ($q) use ($startDate, $endDate) {
					$q->whereBetween('lvr_start_date', [$startDate, $endDate])
						->orWhereBetween('lvr_end_date', [$startDate, $endDate]);
				})
			->get();

			$leaveDates = [];

			foreach ($leaveRecords as $leave) {
				$start = Carbon::parse($leave->lvr_start_date);
				$end   = Carbon::parse($leave->lvr_end_date);

				// Generate all dates between start and end (inclusive)
				$period = CarbonPeriod::create($start, $end);

				foreach ($period as $date) {
					$leaveDates[] = $date->format('Y-m-d'); // or keep Carbon instance if you want
				}
			}

			// If you want unique dates only
			$leaveDates = array_unique($leaveDates);

			// 6 Exclude dates
			$excludeDates = collect($existingDates)
				->merge($holidayDates)
				->merge($weekOffDates)
				->merge($leaveDates)
				->unique()
				->toArray();

			// 6 Orphan dates
			$orphanDates = array_diff($allDates, $excludeDates);
			
			// 7 Bulk insert data
			$insertData = [];
			foreach ($orphanDates as $date) {
				$insertData[] = [
					'atd_b_id' => $employee->emp_b_id,
					'atd_emp_id' => $employee->emp_id,
					'atd_date' => $date,
					'atd_attendance_status' => 203,
					'atd_is_absent' => 1,
					'atd_pst_id' => $employee->fh_shift_type->pst_id ?? null,
					'atd_work_mode_type_id' => $employee->fh_work_mode->m_id,
					'atd_module_id' => 249,
					'atd_next_approver' => 1,
					'atd_request_status' => 140,
					'created_at' => now(),
					'updated_at' => now(),
				];
			}

			if (!empty($insertData)) {
				// DB::table('attendance_records')->insert($insertData);
			}

			// 8 Add per-employee summary row
			$summary[] = [
				'emp_id'          => $employee->emp_id,
				'name'            => $employee->emp_full_name,
				'start_date'      => $startDate,
				'end_date'        => $endDate,
				'total_days'      => count($allDates),
				'existing'        => count($existingDates),
				'leaves'          => count($leaveDates),
				'week_offs'       => count($weekOffDates),
				'holidays'        => count($holidayDates),
				'orphans'         => count($orphanDates),
				'inserted'        => count($insertData)
			];
		}

		// Totals row
		$summary[] = [
			'emp_id'          => 'TOTAL',
			'name'            => '',
			'start_date'      => '',
			'end_date'        => '',
			'total_days'      => array_sum(array_column($summary, 'total_days')),
			'existing'        => array_sum(array_column($summary, 'existing')),
			'leaves'          => array_sum(array_column($summary, 'leaves')),
			'week_offs'       => array_sum(array_column($summary, 'week_offs')),
			'holidays'        => array_sum(array_column($summary, 'holidays')),
			'orphans'         => array_sum(array_column($summary, 'orphans')),
			'inserted'        => array_sum(array_column($summary, 'inserted')),
		];

		// Output table
		$this->table(
			['Emp ID', 'Name', 'Start', 'End', 'Total Days', 'Existing', 'Leave_Records', 'Week_Offs', 'Holidays', 'Orphans', 'Inserted'],
			$summary
		);

		$this->info('Past absent records command executed at - ' . now());
	}
}
