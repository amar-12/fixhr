<?php

namespace App\Console\Commands;

use App\Models\AttendanceRecord;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DailyAttendanceCorrectionCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:missed-punch-late-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for missed punch late';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $previousDate = Carbon::yesterday()->format('Y-m-d');

        $updated = AttendanceRecord::whereDate('atd_date', $previousDate)
            ->whereIn('atd_attendance_status', [228, 252])
            ->update([
                'atd_is_late' => 0,
                'atd_late_duration' => 0.00,
                'atd_is_early_exit' => 0,
                'atd_early_exit_duration' => 0.00,
            ]);

        Log::info("Attendance mispunch cron executed. Updated records: $updated");
    }
}
