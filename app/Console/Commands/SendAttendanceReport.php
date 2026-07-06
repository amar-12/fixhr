<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Business;
use App\Models\Employee;
use App\Models\AttendanceRecord;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Jobs\SendWhatsappReportJob;

class SendAttendanceReport extends Command
{
    protected $signature = 'app:report-attendance';
    protected $description = 'Send daily attendance report on WhatsApp';

    public function handle()
    {
        $today = Carbon::today();
        $todaymsg = $today->format('d-M-y');

        $attendanceStats = AttendanceRecord::whereDate('atd_date', $today)
            ->selectRaw('
                atd_b_id,
                COUNT(DISTINCT atd_emp_id) as present_count,
                COUNT(DISTINCT CASE WHEN atd_is_late = 1 THEN atd_emp_id END) as late_count
            ')
            ->groupBy('atd_b_id')
            ->get()
            ->keyBy('atd_b_id');

        $employeeCounts = Employee::where('emp_status', 71)
            ->whereNotNull('emp_job_status')
            ->selectRaw('emp_b_id, COUNT(*) as total')
            ->groupBy('emp_b_id')
            ->get()
            ->keyBy('emp_b_id');

        /*
        |--------------------------------------------------------------------------
        | STEP 3: Process businesses in chunks (memory safe)
        |--------------------------------------------------------------------------
        */
        Business::where('b_status', 1)
            ->whereHas('subscription', function ($q) use ($today) {
                $q->where('status', 'active')
                  ->whereDate('end_date', '>=', $today);
            })
            ->with('fh_admin')
            ->chunk(50, function ($businesses) use (
                $attendanceStats,
                $employeeCounts,
                $todaymsg
            ) {

                foreach ($businesses as $business) {

                    $admin = $business->fh_admin;

                    if (!$admin || empty($admin->emp_phone)) {
                        continue;
                    }

                    if ($business->is_whatsapp == 0) {
                        $this->info('This business '. $business->b_name .' whatsapp is disabled.');
                        continue;
                    }

                    $businessId = $business->b_id;

                    $totalEmployees = $employeeCounts[$businessId]->total ?? 0;
                    $totalPresent  = $attendanceStats[$businessId]->present_count ?? 0;
                    $totalLate     = $attendanceStats[$businessId]->late_count ?? 0;
                    $totalAbsent   = max(0, $totalEmployees - $totalPresent);

                    $payload = [
                        "phone" => '+918889436902', //. $admin->emp_phone,
                        "template" => [
                            "name" => "fixhrreport5",
                            "language" => ["code" => "en_GB"],
                            "components" => [[
                                "type" => "body",
                                "parameters" => [
                                    ["type" => "text", "text" => $todaymsg],
                                    ["type" => "text", "text" => $business->b_name],
                                    ["type" => "text", "text" => $totalPresent],
                                    ["type" => "text", "text" => $totalAbsent],
                                    ["type" => "text", "text" => $totalLate],
                                ]
                            ]]
                        ]
                    ];

                    // send to queue (parallel processing)
                    dispatch(new SendWhatsappReportJob($payload));

                    $this->info("Queued: {$business->b_name}");
                }
            });

        $this->info('Attendance reports queued successfully.');
    }
}