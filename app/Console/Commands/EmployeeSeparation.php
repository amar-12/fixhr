<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use Carbon\Carbon;

class EmployeeSeparation extends Command
{
    protected $signature = 'app:employee-separation';

    protected $description = 'Update employee status based on last working date and leaving reason';

    public function handle()
    {
        // Step 1: Reset remind me later for all active separated employees
        Employee::where('emp_status', 71)
            ->update([
                'emp_remind_me_later' => 0
            ]);

        // Step 2: Update employee status whose last working date is over
        $updatedCount = Employee::where('emp_status', 71)
            ->whereDate('emp_last_working_date', '<=', Carbon::yesterday())
            ->update([
                'emp_status' => 72
            ]);

        $this->info($updatedCount . " employees updated.");

        $this->info("All employees updated successfully.");
    }
}
