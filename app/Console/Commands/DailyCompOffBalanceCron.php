<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\CompOff;
use App\Models\CompOffBalance;
use App\Models\CompOffPolicy;
use App\Models\Employee;
use App\Models\SchedulerProcessTrack;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DailyCompOffBalanceCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:daily-comp-off-balance-cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command processes the daily comp off balance for employees and updates their comp off records.';

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

        $currentMonth = $today->format('m');
        $currentYear = $today->format('Y');
        $currentMonthStart = $today->copy()->startOfMonth()->toDateString();
        $currentMonthEnd = $today->copy()->endOfMonth()->toDateString();

        foreach ($businesses as $business) {
            $businessStart = microtime(true);
            Log::info('Processing business for comp off balances', ['b_id' => $business->b_id, 'b_unique' => $business->b_unique_id]);

            try {
                // Use transaction + row lock to avoid concurrent schedulers creating duplicate tracks
                DB::transaction(function () use ($business, $currentDate, $today, $currentMonth, $currentYear, $currentMonthStart, $currentMonthEnd) {
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

                    if ($employees->isNotEmpty()) {
                        foreach ($employees as $employee) {
                            $expired = 0;
                            
                            $compOffPolicy = CompOffPolicy::with("duration_conditions")
                                ->where('cop_b_id', $employee->emp_b_id)
                                ->where('cop_effective_date', '<=', $today)
                                ->orderBy('cop_effective_date', 'desc')
                                ->first();

                            // Get CO requests credited in the previous month (use date range for index-friendly query)
                            $co_requests = CompOff::where([
                                ['co_b_id', '=', $employee->emp_b_id],
                                ['co_emp_id', '=', $employee->emp_id],
                            ])
                                ->whereBetween('co_credit_date', [$currentMonthStart, $currentMonthEnd])
                                ->get();

                            // Calculate expired COs to be subtracted from remaining
                            foreach ($co_requests as $co) {
                                $creditDate = Carbon::parse($co->co_credit_date);
                                $validity = $creditDate->copy()->addDays(intval($compOffPolicy->validity ?? 0));
                                if ($validity->lessThan($today)) {
                                    $co->update(['co_is_expired' => 1]);
                                    $expired += intval($co->co_alloted ?? 0);
                                }
                            }

                            // Find existing comp off balance entry of previous month
                            $existingCompOffBal = CompOffBalance::where([
                                ['cb_b_id', '=', $employee->emp_b_id],
                                ['cb_emp_id', '=', $employee->emp_id],
                                ['cb_month', '=', $currentMonth],
                                ['cb_year', '=', $currentYear],
                            ])->select('cb_balance_remaining')->first();

                            // Safe handling when previous-month balance is missing
                            $prevBalance = $existingCompOffBal->cb_balance_remaining ?? 0;
                            $remaining = max(0, ($prevBalance - $expired));

                            if ($existingCompOffBal) {
                                $existingCompOffBal->update([
                                    'cb_expired' => $expired,
                                    'cb_balance_remaining' => $remaining,
                                ]);
                            } else {
                                Log::warning('Previous CompOffBalance row missing; cannot update expired/remaining for previous month', ['emp_id' => $employee->emp_id, 'b_id' => $employee->emp_b_id]);
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
