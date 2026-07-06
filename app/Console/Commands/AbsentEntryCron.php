<?php

namespace App\Console\Commands;

use App\Helpers\CentralLogics;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\PolicyHolidayList;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AbsentEntryCron extends Command
{
	/**
	 * The name and signature of the console command.
	 */
	protected $signature = 'app:absent-entry-cron {--date=}';

	/**
	 * The console command description.
	 */
	protected $description = 'Create Records for absent employees in AttendanceRecord';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		// Parse --date option or use yesterday by default
		$inputDate = $this->option('date');
		try {
			$cuDate = $inputDate ? Carbon::parse($inputDate) : Carbon::now()->subDay();
		} catch (\Exception $e) {
			$this->error("Invalid date format. Use YYYY-MM-DD.");
			return;
		}

		// Fetch eligible employees without attendance record on that date
		$emps = Employee::query()
			->select([
				'emp_id',
				'emp_b_id',
				'emp_full_name',
				'emp_shift_type_id',
				'emp_work_mode_id',
				'emp_pwo_id' // ✅ Include FK for week off policy
			])
			->with([
				'fh_shift_type' => function ($q) {
					$q->select('pst_id');
				},
				'fh_work_mode' => function ($q) {
					$q->select('m_id');
				},
				'fh_week_off_policy2' => function ($q) {
					$q->select('pwo_id', 'pwo_recurrence_day_ids');
				},
			])
			->where([
				['emp_attendance_preference', '!=', 370],
				['emp_role_id', '!=', 1],
				['emp_status', '=', 71],
			])
			->whereNull('emp_last_working_date')
			->whereDoesntHave('attendance_record', function ($q) use ($cuDate) {
				$q->whereDate('atd_date', $cuDate);
			})
			->get();

		foreach ($emps as $employee) {
			$isLeave = LeaveRequest::where('lvr_emp_id', $employee->emp_id)
				->where(function ($q) use ($cuDate) {
					$q->whereBetween('lvr_start_date', [$cuDate, $cuDate])
						->orWhereBetween('lvr_end_date', [$cuDate, $cuDate]);
				})
				->exists();

			// Attach week off policy manually so getWeekOffDates can access it
			$employee->fh_week_off_policy = $employee->fh_week_off_policy2;

			$weekOffDates = CentralLogics::getWeekOffDates($employee, null, null, $cuDate, $cuDate);
			$isHoliday = PolicyHolidayList::where('phl_b_id', $employee->emp_b_id)
				->whereDate('phl_start_date', '<=', $cuDate)
				->whereDate('phl_end_date', '>=', $cuDate)
				->exists();

			if (!$isHoliday && empty($weekOffDates) && !$isLeave) {
				AttendanceRecord::create([
					'atd_b_id' => $employee->emp_b_id,
					'atd_emp_id' => $employee->emp_id,
					'atd_date' => $cuDate->format('Y-m-d'),
					'atd_attendance_status' => 203,
					'atd_is_absent' => 1,
					'atd_pst_id' => $employee->fh_shift_type->pst_id ?? null,
					'atd_work_mode_type_id' => $employee->fh_work_mode->m_id,
					'atd_module_id' => 249,
					'atd_next_approver' => 1,
					'atd_request_status' => 140,
				]);

				\Log::info("✅ Absent record created for {$employee->emp_full_name} on " . $cuDate->format('Y-m-d') . " at " . now());
			} else {
				\Log::info("❌ Not marked absent (Holiday or Week Off)" . $cuDate->format('Y-m-d') . " at " . now());
			}
		}

		\Log::info("✅ AbsentEntryCron completed for " . $cuDate->format('Y-m-d') . " at " . now());
		$this->info("AbsentEntryCron ran for " . $cuDate->format('Y-m-d'));
	}
}
