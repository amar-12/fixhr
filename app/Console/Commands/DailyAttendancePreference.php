<?php

namespace App\Console\Commands;

use App\Helpers\CentralLogics;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\PolicyHolidayList;
use Carbon\Carbon;

use Illuminate\Console\Command;

class DailyAttendancePreference extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:daily-attendance-preference';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get all employees whose attendance preference is 370, i.e. check-in/check-out not required
        $emps = Employee::with('fh_shift_type')->where("emp_attendance_preference", 370)->get();

        foreach ($emps as $employee) {
            // Shift Timings
            $checkInTime = Carbon::parse($employee->fh_shift_type->pst_start_time);
            $checkOutTime = Carbon::parse($employee->fh_shift_type->pst_end_time);
            $workedHours = $checkInTime->diffInHours($checkOutTime);

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

                \Log::info('Attendance of Preference Employee Daily - ' . $employee->emp_full_name);
                \Log::info('Cron task ran at ' . now());
            }
        }
    }
}
