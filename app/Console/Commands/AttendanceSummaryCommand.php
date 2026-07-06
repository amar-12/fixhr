<?php

namespace App\Console\Commands;

use App\Helpers\CentralLogics;
use App\Models\Business;
use App\Models\Employee;
use App\Models\PolicyHolidayList;
use App\Models\SchedulerProcessTrack;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AttendanceSummaryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-attendance-summary';

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
        $currentDate =  now()->format('Y-m-d');
        [$year, $month] = explode('-', $currentDate);
        $businesses =  Business::where('b_id', 30)->get();
        $processLimit = 50;
        $offset = 0;
        foreach ($businesses as $business) {
            $schedulerTrack = SchedulerProcessTrack::where('spt_b_id', $business->b_id)->WhereDate('spt_process_date',$currentDate)->where('spt_process_type','ATTENDANCE_SUMMARY')->first();
            if($schedulerTrack){
                if($schedulerTrack->spt_total_items == $schedulerTrack->spt_processed_count){
                    break;
                }
                $offset = $schedulerTrack->spt_processed_count;
                $employees = Employee::where('emp_b_id', $business->b_id)->skip($offset)->limit($processLimit)->orderBy('emp_id','asc')->get();
            }else{
                $total_employees = Employee::where('emp_b_id', $business->b_id)->count();
                $employees = Employee::where('emp_b_id', $business->b_id)->skip($offset)->limit($processLimit)->orderBy('emp_id','asc')->get();
                SchedulerProcessTrack::create(['spt_b_id'=>$business->b_id, 'spt_process_type'=>'ATTENDANCE_SUMMARY', 'spt_total_items'=>$total_employees,'spt_status'=>'processing','spt_process_date'=>$currentDate, 'spt_processed_count'=>$processLimit]);
            }
            $schedulerTrack ? $schedulerTrack->spt_processed_count + $processLimit: $processLimit;
            foreach ($employees as $key => $val) {
                $weekOfDates = CentralLogics::getWeekOffDates($val, $year, $month);
                $attendanceDataResult = CentralLogics::getAttendanceByDate($val, $currentDate, $weekOfDates);
                $attendanceDataResult['absentCount'];
            }
        }

    }
}
