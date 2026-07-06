<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\SchedulerProcessTrack;
use Illuminate\Console\Command;

class MonthlyLeaveBalanceCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:monthly-leave-balance-cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command processes the monthly leave balance for employees and updates their leave records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $currentDateTime = now();
        \Log::info('Monthly leave balance cron job executed successfully. - ' . $currentDateTime);
        $currentDateTime2 = now();
        $currentDate = $currentDateTime->format('Y-m-d');
        $currentTime = $currentDateTime->format('H:i');

        // Check if the current date is the 1st of the month and time is 12:00 AM
        if ($currentDateTime->day === 1 && $currentTime === '00:00') {

            $previousMonthDate = $currentDateTime2->copy()->subMonth();
            $currentYear = $currentDateTime->year;
            $currentMonth = $currentDateTime->month;
            $processLimit = 50; // number of employees per batch

            \Log::info("Processing Monthly Leave - Prev Month: " . $previousMonthDate->month . ", Year: " . $previousMonthDate->year);
            \Log::info("Current Year : " . $currentYear . ' Month : ' . $currentMonth . ' Date : ' . $currentDateTime);

            $businesses = Business::where('b_status', 1)->select('b_id', 'b_unique_id')->get();

            if ($businesses->isNotEmpty()) {
                foreach ($businesses as $business) {

                    $schedulerTrack = SchedulerProcessTrack::where('spt_b_id', $business->b_id)
                        ->whereDate('spt_process_date', $currentDate)
                        ->where('spt_process_type', 'MONTHLY_LEAVE_BALANCE')
                        ->first();

                    if ($schedulerTrack) {
                        $offset = $schedulerTrack->spt_processed_count;
                        \Log::info("IF wala - Business Unique Number" . $business->b_unique_id, ['spt_processed_count' => $offset]).PHP_EOL;
                    } else {
                        $totalEmployees = Employee::where('emp_b_id', $business->b_id)->count();
                        \Log::info("Business Unique Number " . $business->b_unique_id, ['total_emp_count' => $totalEmployees]);
                        $schedulerTrack = SchedulerProcessTrack::create([
                            'spt_b_id' => $business->b_id,
                            'spt_process_type' => 'MONTHLY_LEAVE_BALANCE',
                            'spt_total_items' => $totalEmployees,
                            'spt_status' => 'processing',
                            'spt_process_date' => $currentDate,
                            'spt_processed_count' => 0,
                        ]);
                        $offset = 0;
                        \Log::info("Starting new process for business: {$business->b_unique_id} | Employees: {$totalEmployees}");
                    }

                    // While loop: process until all employees done
                    while (true) {
                        $employees = Employee::where('emp_status', 71)
                            ->where('emp_b_id', $business->b_id)
                            ->skip($offset)
                            ->limit($processLimit)
                            ->orderBy('emp_id', 'asc')
                            ->select('emp_id', 'emp_b_id', 'emp_gender_id', 'emp_pl_id')
                            ->get();

                        if ($employees->isEmpty()) {
                            \Log::info("All employees processed for business: {$business->b_unique_id}");
                            break;
                        }

                        foreach ($employees as $employee) {
                            $gender = null;
                            if ($employee->emp_gender_id == 33) {
                                $gender = 224; // Male
                            } elseif ($employee->emp_gender_id == 34) {
                                $gender = 225; // Female
                            } else {
                                $gender = 0; // Other
                            }

                            $leaveTypes = LeaveType::where('lvt_pl_id', $employee->emp_pl_id)
                                ->where('lvt_cycle_id', 219)
                                ->select('lvt_cat_type_id', 'lvt_days_per_year', 'lvt_applicable_to_id')
                                ->get();

                            if ($leaveTypes->isNotEmpty()) {
                                foreach ($leaveTypes as $lvType) {

                                    $existingLeaveBal = LeaveBalance::where([
                                        ['lb_b_id', '=', $employee->emp_b_id],
                                        ['lb_emp_id', '=', $employee->emp_id],
                                        ['lb_cat_type_id', '=', $lvType->lvt_cat_type_id],
                                        ['lb_month', '=', $previousMonthDate->month],
                                        ['lb_year', '=', $previousMonthDate->year],
                                    ])->select('lb_balance_remaining_leave')->first();

                                    $carriedForward = $existingLeaveBal->lb_balance_remaining_leave ?? 0;

                                    $updateData = [
                                        'lb_b_id' => $employee->emp_b_id,
                                        'lb_emp_id' => $employee->emp_id,
                                        'lb_month' => $currentMonth,
                                        'lb_year' => $currentYear,
                                        'lb_cat_type_id' => $lvType->lvt_cat_type_id,
                                        'lb_alloted_leave' => $lvType->lvt_days_per_year,
                                        'lb_taken_leave' => 0,
                                        'lb_balance_remaining_leave' => $lvType->lvt_days_per_year + $carriedForward,
                                        'lb_carried_forward' => $carriedForward,
                                    ];

                                    $isValidGender = ($lvType->lvt_applicable_to_id == 223 || $lvType->lvt_applicable_to_id == $gender);

                                    if ($isValidGender || $lvType->lvt_applicable_to_id == 0) {
                                        $currentLeaveBal = LeaveBalance::where([
                                            ['lb_b_id', '=', $employee->emp_b_id],
                                            ['lb_emp_id', '=', $employee->emp_id],
                                            ['lb_cat_type_id', '=', $lvType->lvt_cat_type_id],
                                            ['lb_month', '=', $currentMonth],
                                            ['lb_year', '=', $currentYear],
                                        ])->first();

                                        if (!$currentLeaveBal) {
                                            LeaveBalance::create($updateData);
                                            \Log::info("New leave balance created for Employee ID: {$employee->emp_id} | Business: {$business->b_unique_id}");
                                        } else {
                                            \Log::info("Leave balance exists for Employee ID: {$employee->emp_id}, skipped.");
                                        }
                                    }
                                }
                            }
                        }

                        // update offset
                        $offset += $employees->count();
                        $schedulerTrack->update(['spt_processed_count' => $offset]);
                        \Log::info("Processed {$offset} employees for business: {$business->b_unique_id}");
                    }

                    // Mark as completed
                    $schedulerTrack->update(['spt_status' => 'completed']);
                }
            }

        } else {
            \Log::info('Command is scheduled to run only on the 1st of the month at 12 AM.');
            $this->info('Command is scheduled to run only on the 1st of the month at 12 AM.');
        }
    }
}
