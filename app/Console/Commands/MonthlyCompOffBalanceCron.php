<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\CompOffBalance;
use App\Models\CompOffPolicy;
use App\Models\Employee;
use App\Models\SchedulerProcessTrack;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MonthlyCompOffBalanceCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:monthly-comp-off-balance-cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command processes the monthly comp off balance for employees and updates their comp off records.';

    /**
     * Number of employees to process in one chunk. Make configurable if needed.
     *
     * @var int
     */
    protected $processLimit = 50;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now();
        Log::info('Daily Comp Off Balance Cron started', ['timestamp' => $today->toDateTimeString()]);
        $currentDate = $today->format('Y-m-d');

        // Fetch active businesses
        $businesses = Business::where('b_status', 1)->select('b_id', 'b_unique_id')->get();

        if ($businesses->isEmpty()) {
            Log::info('No active businesses found for DailyCompOffBalanceCron.');
            return;
        }

        // Precompute previous month/year and date range once for clarity
        $previousMonth = $today->copy()->subMonth();
        $previousMonthNumber = $previousMonth->format('m');
        $previousMonthYear = $previousMonth->format('Y');

        foreach ($businesses as $business) {
            $businessStart = microtime(true);
            Log::info('Processing business for comp off balances', ['b_id' => $business->b_id, 'b_unique' => $business->b_unique_id]);

            try {
                // Use transaction + row lock to avoid concurrent schedulers creating duplicate tracks
                DB::transaction(function () use ($business, $currentDate, $today, $previousMonthNumber, $previousMonthYear) {
                    // Obtain or create scheduler track with lock
                    $schedulerTrack = SchedulerProcessTrack::where('spt_b_id', $business->b_id)
                        ->whereDate('spt_process_date', $currentDate)
                        ->where('spt_process_type', 'DAILY_COMP_OFF_BALANCE')
                        ->lockForUpdate()
                        ->first();

                    if ($schedulerTrack) {
                        // If already finished for this business, skip
                        if ($schedulerTrack->spt_total_items == $schedulerTrack->spt_processed_count) {
                            Log::info('Scheduler already completed for business', ['b_id' => $business->b_id]);
                            return;
                        }
                        $offset = $schedulerTrack->spt_processed_count;
                    } else {
                        // Count only active employees that will be processed (emp_status = 71)
                        $totalEmployees = Employee::where('emp_b_id', $business->b_id)->where('emp_status', 71)->count();
                        Log::info('Initializing scheduler track', ['b_id' => $business->b_id, 'total_emp_count' => $totalEmployees]);
                        $schedulerTrack = SchedulerProcessTrack::create([
                            'spt_b_id' => $business->b_id,
                            'spt_process_type' => 'DAILY_COMP_OFF_BALANCE',
                            'spt_total_items' => $totalEmployees,
                            'spt_status' => 'processing',
                            'spt_process_date' => $currentDate,
                            'spt_processed_count' => 0,
                        ]);
                        $offset = 0;
                    }

                    // Fetch a chunk of active employees for this business
                    $employees = Employee::where('emp_status', 71)->where('emp_b_id', $business->b_id)
                        ->skip($offset)->limit($this->processLimit)->orderBy('emp_id', 'asc')
                        ->select('emp_id', 'emp_b_id')
                        ->get();

                    $currentMonth = $today->format('m');
                    $currentYear = $today->format('Y');

                    if ($employees->isNotEmpty()) {
                        foreach ($employees as $employee) {
                            // Find existing comp off balance entry of previous month
                            $existingCompOffBal = CompOffBalance::where([
                                ['cb_b_id', '=', $employee->emp_b_id],
                                ['cb_emp_id', '=', $employee->emp_id],
                                ['cb_month', '=', $previousMonthNumber],
                                ['cb_year', '=', $previousMonthYear],
                            ])->select('cb_balance_remaining')->first();

                            $compOffPolicy = CompOffPolicy::with("duration_conditions")
                                ->where('cop_b_id', $employee->emp_b_id)
                                ->where('cop_effective_date', '<=', $today)
                                ->orderBy('cop_effective_date', 'desc')
                                ->first();

                            // Ensure policy exists and today is the first day of the month
                            if ($compOffPolicy && $compOffPolicy->carry_forward && $today->day === 1) {
                                $carriedForward = $existingCompOffBal->cb_balance_remaining ?? 0;

                                $updateData = [
                                    'cb_b_id' => $employee->emp_b_id,
                                    'cb_emp_id' => $employee->emp_id,
                                    'cb_month' => $currentMonth,
                                    'cb_year' => $currentYear,
                                    'cb_taken_leave' => 0,
                                    'cb_balance_remaining' => $carriedForward,
                                    'cb_carried_forward' => $carriedForward,
                                ];

                                // Upsert comp off row for the employee (create or update existing)
                                $match = [
                                    'cb_b_id' => $employee->emp_b_id,
                                    'cb_emp_id' => $employee->emp_id,
                                    'cb_month' => $currentMonth,
                                    'cb_year' => $currentYear,
                                ];

                                $compOff = CompOffBalance::updateOrCreate($match, $updateData);

                                if ($compOff->wasRecentlyCreated) {
                                    Log::info('New Comp Off balance created', ['emp_id' => $employee->emp_id, 'cb_id' => $compOff->cb_id]);
                                } else {
                                    Log::info('Comp Off balance updated/skipped (already existed)', ['emp_id' => $employee->emp_id, 'cb_id' => $compOff->cb_id]);
                                }
                            }
                        }
                    } else {
                        Log::info('No employees found in this chunk for business', ['b_id' => $business->b_id, 'offset' => $offset]);
                    }

                    // Update SchedulerProcessTrack with the processed count
                    $schedulerTrack->update([
                        'spt_processed_count' => $schedulerTrack->spt_processed_count + $employees->count(),
                    ]);
                }); // end DB transaction

                $duration = round(microtime(true) - $businessStart, 2);
                Log::info('Finished processing business', ['b_id' => $business->b_id, 'duration_s' => $duration]);
            } catch (\Throwable $e) {
                // Log and continue with next business - don't let one business stop the whole cron
                Log::error('Error processing DailyCompOffBalance for business', ['b_id' => $business->b_id, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                $this->error('Error processing business ' . $business->b_unique_id . ': ' . $e->getMessage());
                continue;
            }
        }
    }
}
