<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Employee;
use App\Models\EmployeeManagerLog;
use App\Models\EmployeeApprovalStatus;

class ApplyManagerChange extends Command
{
    protected $signature = 'app:manager-apply-change';
    protected $description = 'Apply pending employee manager changes (WEF = today only)';

    public function handle()
    {
        $today = Carbon::today();
        DB::transaction(function () use ($today) {

            $logs = EmployeeManagerLog::where('eml_applied', 0)
                ->whereDate('eml_wef_date', $today) // ✅ ONLY TODAY
                ->orderBy('eml_id')
                ->lockForUpdate()
                ->get();

            if ($logs->isEmpty()) {
                Log::info('No manager change logs for today.');
                $this->info('No manager change logs for today.');
                return;
            }

            foreach ($logs as $log) {

                // 🔁 Reporting Manager Change
                if ($log->eml_form_type === 'REPORTING_MANAGER') {
                    Employee::where('emp_supervisor_id', $log->eml_old_manager_id)
                        ->update([
                            'emp_supervisor_id' => $log->eml_new_manager_id
                        ]);
                }

                // 🔁 Approval Manager Change
                if ($log->eml_form_type === 'APPROVAL_MANAGER') {
                    EmployeeApprovalStatus::where('eas_approvel_id', $log->eml_old_manager_id)
                        ->update([
                            'eas_approvel_id' => $log->eml_new_manager_id
                        ]);
                }

                // ✅ Mark as applied
                $log->update([
                    'eml_applied'     => 1,
                    'eml_applied_at'  => now(),
                ]);
            }

            Log::info('Manager change applied successfully.');
            $this->info('Manager change applied successfully.');
        });
        \Log::info("✅ Manager approvel change ");
    }
}