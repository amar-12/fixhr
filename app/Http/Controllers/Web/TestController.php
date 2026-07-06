<?php

namespace App\Http\Controllers\Web;

use App\Events\TestReverbEvent;
use App\Helpers\CentralLogics;
use App\Http\Controllers\Controller;
use App\Mail\ApprovalMail;
use App\Models\ApprovalLog;
use App\Models\AttendanceRecord;
use App\Models\Business;
use App\Models\Country;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\TadaClaim;
use App\Models\TadaRequestPlan;
use Carbon\Carbon;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Depart;
use App\Models\Desig;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\MailTemplate;
use App\Models\MasterTable;
use DateTime;
use Illuminate\Support\Facades\Http;
use App\Models\PolicyHolidayList;
use App\Models\PolicyWeekOff;
use App\Models\Recruitment;
use App\Models\RecruitmentCandidate;
use App\Models\SchedulerProcessTrack;
use App\Models\RuleCriterion;
use App\Models\State;
use App\Models\Timezone;
use ChandraHemant\HtkcUtils\CommonUtils;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use NumberToWords\Grammar\Gender;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Style\Border;
use App\Models\EmployeeApprovalMapping;

class TestController extends Controller
{
    public function importView()
    {
        //     AttendanceRecord::create([
        //     "atd_emp_id" => 666,
        //     "atd_b_id" => 44,
        //     "atd_pst_id" => 46,
        //     "atd_work_mode_type_id" => 62,
        //     "atd_checkin_method_id" => 314,
        //     "atd_date" => "2025-02-01",
        //     "atd_check_in_time" => "2025-02-01 10:00:00",
        //     "atd_check_out_time" => "2025-02-01 18:45:00",
        //   ]);
        //     dd('attendance inserted');
        return view('attendance-import');
    }

    public function sandwichLeave(Request $request)
    {
        // $request->validate([
        //     'employee_id' => 'required|integer',
        //     'start_date' => 'required|date',
        //     'end_date' => 'required|date|after_or_equal:start_date',
        // ]);
        $currentDate =  now()->format('Y-m');
        [$year, $month] = explode('-', $currentDate);
        $request['start_date'] = '13-01-2025';
        $request['end_date'] = '14-01-2025';
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $empId = 787; //$request->emp_id;
        $leaveCat = 207; //$request->leave_type;
        // dd($request->all(), $request->start_date, $startDate, $endDate);

        // Fetch holidays and week-offs
        $holidays = PolicyHolidayList::where('phl_b_id', 143)->get()
            ->flatMap(function ($holiday) {
                // Create a date range from phl_start_date to phl_end_date
                return Carbon::parse($holiday->phl_start_date)
                    ->toPeriod(Carbon::parse($holiday->phl_end_date))
                    ->toArray();
            })
            ->map(function ($date) {
                return Carbon::parse($date)->toDateString();
            })
            ->unique() // This will remove duplicate values
            ->filter(function ($date) use ($year, $month) {
                $holidayDate = Carbon::parse($date);
                return $holidayDate->year == $year && $holidayDate->month == $month;
            })
            ->values() // This will reset the keys of the collection
            ->toArray();
        // $weekoffs = PolicyWeekOff::pluck('day_name')->toArray();

        $employees = Employee::where('emp_id', $empId)->get();
        foreach ($employees as $key => $val) {
            $weekOfDates = CentralLogics::getWeekOffDates($val, $year, $month);
        }
        // dd($holidays, $weekOfDates);

        //START --- consecutive leave days case
        $previousLeaves = LeaveRequest::where('lvr_emp_id', $empId) // Example employee ID
            // ->where('lvr_cat_type_id', $leaveCat)
            ->where('lvr_end_date', '<', $startDate)
            ->whereYear('lvr_end_date', $year)
            ->whereMonth('lvr_end_date', $month)
            ->orderBy('lvr_end_date', 'desc')
            ->get();
        // dd($previousLeaves);

        // Identify consecutive leave periods
        $tempStartDate = $startDate->copy(); // Temporary start date for adjustments
        $tempEndDate = $endDate->copy(); // Temporary start date for adjustments

        $cnt = 0;
        foreach ($previousLeaves as $leave) {
            if ($cnt == 0) {
                $leaveEndDate = Carbon::parse($leave->lvr_end_date);
                $cnt++;
            }
            $leaveStartDate = Carbon::parse($leave->lvr_start_date);

            // Check if the gap between leaveEndDate and tempStartDate is filled with holidays/week-offs
            $gapDays = collect();
            for ($date = $leaveEndDate->copy()->addDay(); $date->lessThan($tempStartDate); $date->addDay()) {
                // dd($leaveEndDate->copy()->addDay(), $date->lessThan($tempStartDate), $date->addDay());
                $gapDays->push($date->toDateString());
            }
            if ($cnt == 0) {
                // dd($gapDays, $leaveStartDate, $leaveEndDate);
            }

            // Check if all gap days are holidays or week-offs
            $allHolidaysOrWeekOffs = $gapDays->every(function ($date) use ($holidays, $weekOfDates) {
                return in_array(Carbon::parse($date)->toDateString(), $holidays) || in_array(Carbon::parse($date)->toDateString(), $weekOfDates);
            });
            // dd($allHolidaysOrWeekOffs, $date);
            if ($allHolidaysOrWeekOffs) {
                // dd('if ', $allHolidaysOrWeekOffs, $leaveStartDate);
                // Update tempStartDate to the start of the previous leave
                $tempStartDate = $leaveStartDate;
                $leaveEndDate = $tempEndDate;
            } else {
                $leaveStartDate = $tempStartDate;
                $leaveEndDate = $tempEndDate;
                // echo 'aaya ';
                // Break the loop if there are working days in the gap
                break;
            }
        }
        // Final consolidated leave period
        // $startDate = $tempStartDate;
        // dd($cnt, $previousLeaves, $startDate, $tempStartDate, $leaveStartDate, $leaveEndDate);

        // Calculate total sandwich leave days
        $allLeaveDates = collect();
        for ($date = $tempStartDate->copy(); $date->lte($tempEndDate); $date->addDay()) {
            $allLeaveDates->push($date->toDateString());
        }

        // Identify holidays and week-offs within the total leave period
        $extraDays = $allLeaveDates->filter(function ($date) use ($holidays, $weekOfDates) {
            return in_array($date, $holidays) || in_array($date, $weekOfDates);
        })->values();
        // dd($extraDays);

        // Combine leave dates and extra days
        $finalLeaveDates = $allLeaveDates->merge($extraDays)->unique()->values();
        $totalSandwichDays = $finalLeaveDates->count();

        // Debugging: Output the results
        // dd([
        //     'Adjusted Start Date' => $tempStartDate->toDateString(),
        //     'Adjusted End Date' => $tempEndDate->toDateString(),
        //     'Total Sandwich Days' => $totalSandwichDays,
        //     'Leave Dates' => $finalLeaveDates->toArray(),
        //     'Extra Days' => $extraDays,
        //     'Holidays' => $holidays,
        //     'Week-offs' => $weekOfDates,
        // ]);

        // Warn user about sandwich leave
        if ($totalSandwichDays > $endDate->diffInDays($startDate) + 1) {
            return response()->json([
                'warning' => 'This leave will form a sandwich leave and total ' . $totalSandwichDays . ' days with included ' . $extraDays->count() . ' days of weekoff and holiday.',
                'message' => 'Do you want to proceed with the leave ?',
            ]);
        }

        //**************** Save ke baad ka calculation *******************************

        // AUTH SE SANDWICH CHECK KARNA HAI AUR SANDWICH SAVE KESE KARNA HAI
        $user = Auth::user();

        $currentLeaveBalance = LeaveBalance::where('lb_emp_id', $empId)
            ->where('lb_cat_type_id', $leaveCat)
            ->first(); // Assuming leave balances are tracked in `leaveBalances`

        // Get the leave types that match the user's plan and leave type
        $leaveTypes = LeaveType::where('lvt_pl_id', $user->emp_pl_id)
            ->where('lvt_cat_type_id', $leaveCat)
            ->first();

        // $sandwichLeaveTypeIds = $leaveTypes->where('lvt_is_sandwich', 1)->pluck('lvt_cat_type_id')->toArray();

        // Add new_key to currentLeaveBalance based on whether the leave type is a sandwich type
        // $currentLeaveBalance = $currentLeaveBalance->map(function ($balance) use ($sandwichLeaveTypeIds) {
        //     // Check if the leave type ID is in the sandwich leave type IDs
        //     $balance->isSandwich = $balance->lb_cat_type_id == $sandwichLeaveTypeIds ? 1 : 0;
        //     return $balance; // Return the modified balance
        // });

        // Check if the leave type is a sandwich type
        $isSandwich = $leaveTypes && $leaveTypes->lvt_is_sandwich == 1 ? 1 : 0;

        // dd($user->emp_pl_id, $user->emp_id, $leaveTypes, $currentLeaveBalance);

        if ($currentLeaveBalance && $currentLeaveBalance->lb_balance_remaining_leave >= $extraDays->count() && $isSandwich == 1) {
            // Deduct from current leave category
            $currentLeaveBalance->lb_balance_remaining_leave -= $extraDays->count();
            $currentLeaveBalance->lb_taken_leave += $extraDays->count();
            $currentLeaveBalance->save();
            // dd('if :',$currentLeaveBalance->lb_balance_remaining_leave);
        } else {
            // If current leave category balance is insufficient, check the last leave taken
            $lastLeave = LeaveRequest::where('lvr_emp_id', $empId)->orderBy('lvr_end_date', 'desc')->first();
            // dd($lastLeave, $previousLeaves);

            if ($lastLeave) {
                $lastLeaveCategoryId = $lastLeave->lvr_cat_type_id;
                $lastLeaveBalance = LeaveBalance::where('lb_emp_id', $empId)->where('lb_cat_type_id', $lastLeaveCategoryId)->first();
                $leaveTypes = LeaveType::where('lvt_pl_id', $user->emp_pl_id)->where('lvt_cat_type_id', $lastLeaveCategoryId)->first();

                $isSandwich = $leaveTypes && $leaveTypes->lvt_is_sandwich == 1 ? 1 : 0;
                // dd($isSandwich, $lastLeaveCategoryId, $lastLeaveBalance->lb_balance_remaining_leave, $extraDays->count());

                if ($lastLeaveBalance && $lastLeaveBalance->lb_balance_remaining_leave >= $extraDays->count() && $isSandwich == 1) {
                    // Deduct from last leave category
                    $lastLeaveBalance->lb_balance_remaining_leave -= $extraDays->count();
                    $lastLeaveBalance->lb_taken_leave += $extraDays->count();
                    $lastLeaveBalance->save();
                    // dd('else - if :',$currentLeaveBalance->lb_balance_remaining_leave);

                } else {
                    // Mark extra days as UPL
                    $extraStartDay = $extraDays->first();
                    $extraEndDay = $extraDays->last();

                    $leaveWithoutPayData = new LeaveRequest();
                    $leaveWithoutPayData->lvr_start_date = $extraStartDay;
                    $leaveWithoutPayData->lvr_end_date = $extraEndDay;
                    $leaveWithoutPayData->lvr_cat_type_id = 215;
                    $leaveWithoutPayData->lvr_total_leave_days = $extraDays->count();
                    $leaveWithoutPayData->lvr_p_id = $leaveRequest->lvr_id;
                    $leaveWithoutPayData->save();
                }
            }
        }



        //END ----------------------------------------------------------------

        // Get all dates in the leave period
        $leaveDates = [];
        for ($date = $startDate; $date <= $endDate; $date->addDay()) {
            $date = $date->copy();
            $leaveDates[] = Carbon::parse($date)->toDateString(); //$date->toString();
        }
        // dd($holidays, $weekOfDates, $leaveDates);

        // Check for holidays and week-offs in the leave period
        $extraDays = [];
        foreach ($leaveDates as $date) {
            if (in_array($date, $holidays) || in_array($date, $weekOfDates)) {
                $extraDays[] = $date;
            }
            // dd($date, $extraDays, $date->toDateString(), $holidays, in_array($date->toDateString(), $holidays), $weekOfDates, $date->toDateString(), in_array($date->toDateString(), $weekOfDates));
        }
        // dd($extraDays);

        // Check for holidays/week-offs before the start date
        $allHolidaysAndWeekOffs = array_merge($holidays, $weekOfDates);
        // only for sort date -- otherwise nothing
        usort($allHolidaysAndWeekOffs, function ($a, $b) {
            return strtotime($a) - strtotime($b);
        });

        $beforeStartHolidays = collect($allHolidaysAndWeekOffs)
            ->filter(fn($date) => Carbon::parse($date)->lt($startDate))
            ->sort()
            ->toArray();
        // dd($allHolidaysAndWeekOffs, $beforeStartHolidays);

        if (!empty($beforeStartHolidays)) {
            $lastHolidayBeforeStart = Carbon::parse(end($beforeStartHolidays));

            // Check if a leave exists before the last holiday/week-off
            $previousLeave = LeaveRequest::where('lvr_emp_id', $empId) // Example employee ID
                ->where('lvr_cat_type_id', $leaveCat)
                ->where('lvr_end_date', '<', $lastHolidayBeforeStart)
                ->orderBy('lvr_end_date', 'desc')
                ->first();
            // dd($lastHolidayBeforeStart, $previousLeave);

            if ($previousLeave) {
                // Update start date and calculate total leave days
                $tempStartDate = $startDate->copy(); // Store the original start date
                $startDate = Carbon::parse($previousLeave->lvr_start_date); // Update start date
                // dd($lastHolidayBeforeStart, $previousLeave, $tempStartDate, $startDate);

                // Recalculate leave days including previous leave
                $leaveDates = [];
                for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
                    $leaveDates[] = $date->toDateString();
                }
                // dd($leaveDates);

                // Combine leave dates and extra days, ensuring no duplicates
                $allLeaveDate = collect($leaveDates)
                    ->merge($extraDays)
                    ->unique()
                    ->toArray();

                // Total leave days
                $totalLeaveDays = count($allLeaveDate);

                // Debug output
                dd($tempStartDate, $startDate, $endDate, $allLeaveDate, $totalLeaveDays);
            }
        }

        // Combine leave dates and extra days, ensuring no duplicates
        $allLeaveDate = collect($leaveDates)
            ->merge($extraDays)
            ->unique() // Remove duplicates
            ->toArray(); // Convert back to an array

        // Count the total unique leave days
        $totalLeaveDays = count($allLeaveDate);

        // dd($totalLeaveDays, $allLeaveDate, $leaveDates, $extraDays);

        // Fetch employee's leave balance
        $leaveBalance = LeaveBalance::where('lb_emp_id', $empId)->where('lb_cat_type_id', $leaveCat)->first(); // Assuming leave balances are tracked in `leaveBalances`
        dd($leaveBalance->lb_balance_remaining_leave, $totalLeaveDays);

        // Check if the employee has enough leave balance
        if ($leaveBalance->lb_balance_remaining_leave >= $totalLeaveDays) {
            // Deduct leave balance
            // $employee->leaveBalances[$leaveCat] -= $totalLeaveDays;
            // $employee->save();

            $status = 'Paid Leave';
        } else {
            $status = 'Unpaid Leave';
        }
        dd($status);
    }

    public function monthlyLeaveBalance()
    {
        $currentDateTime = now();
        $currentDate = $currentDateTime->format('Y-m-d');
        $currentTime = $currentDateTime->format('H:i');

        // dd($currentDate, $currentTime, $currentDateTime->day);

        // Check if the current date is the 1st of the month and time is 12:00 AM
        if ($currentDateTime) { //->day === 1 && $currentTime === '00:00') {
            $currentYear = $currentDateTime->year;
            $currentMonth = $currentDateTime->month;
            $previousMonthDate = $currentDateTime->subMonth();
            $processLimit = 50; // Number of records to process in each chunk
            // dd($currentDateTime, $currentDate, $currentMonth, $currentYear);
            // Adjust for financial year boundary
            if ($currentMonth === 1) {
                $previousMonthDate = now()->subMonths(1);
            }

            $businesses = Business::all();
            // dd($businesses, $previousMonthDate->month, $previousMonthDate->year);

            foreach ($businesses as $business) {
                // if($business->b_id == 143) {

                $schedulerTrack = SchedulerProcessTrack::where('spt_b_id', $business->b_id)
                    ->whereDate('spt_process_date', $currentDate)
                    ->where('spt_process_type', 'MONTHLY_LEAVE_BALANCE')
                    ->first();

                if ($schedulerTrack) {
                    // Check if all items have been processed
                    if ($schedulerTrack->spt_total_items == $schedulerTrack->spt_processed_count) {
                        continue;
                    }
                    $offset = $schedulerTrack->spt_processed_count;
                } else {
                    // Initialize SchedulerProcessTrack for this business
                    $totalEmployees = Employee::where('emp_b_id', $business->b_id)->count();
                    $schedulerTrack = SchedulerProcessTrack::create([
                        'spt_b_id' => $business->b_id,
                        'spt_process_type' => 'MONTHLY_LEAVE_BALANCE',
                        'spt_total_items' => $totalEmployees,
                        'spt_status' => 'processing',
                        'spt_process_date' => $currentDate,
                        'spt_processed_count' => 0,
                    ]);
                    $offset = 0;
                }

                $employees = Employee::where('emp_status', 1)->where('emp_b_id', $business->b_id)->where('emp_role_id', '!=', 1)
                    ->skip($offset)->limit($processLimit)->orderBy('emp_id', 'asc')->get();

                foreach ($employees as $employee) {
                    $gender = null;
                    if ($employee->emp_gender_id == 33) { // Male
                        $gender = 224;
                    } elseif ($employee->emp_gender_id == 34) { // Female
                        $gender = 225;
                    } else { // Other
                        $gender = 0;
                    }

                    // Assign employee leave balance by leave policy & leave category wise
                    $leaveTypes = LeaveType::where('lvt_pl_id', $employee->emp_pl_id)->get();
                    // dd($leaveTypes, $gender);
                    foreach ($leaveTypes as $lvType) {
                        // $isValidGender = false;
                        $cycleIsValid = $this->isValidCycle($lvType->lvt_leave_cycle_id, $currentMonth);
                        $unusedLeaveRule = $lvType->lvt_unused_leave_rule_id;
                        $carryForwardLimit = $lvType->lvt_carry_forward ?? 0;

                        // Find existing leave balance entry for the specific leave type
                        $existingLeaveBal = LeaveBalance::where([
                            ['lb_b_id', '=', $employee->emp_b_id],
                            ['lb_emp_id', '=', $employee->emp_id],
                            ['lb_cat_type_id', '=', $lvType->lvt_cat_type_id],
                            ['lb_month', '=', $previousMonthDate->month],
                            ['lb_year', '=', $previousMonthDate->year],
                        ])->first();

                        $carriedForward = $this->calculateCarriedForward($existingLeaveBal, $unusedLeaveRule, $carryForwardLimit);

                        if ($cycleIsValid && ($lvType->lvt_applicable_to_id == 223 || $lvType->lvt_applicable_to_id == $gender)) {
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

                            LeaveBalance::create($updateData);
                            echo 'create hua ';
                        }

                        // Check if the gender matches or is applicable for all
                        // if ($lvType->lvt_applicable_to_id == 223 || $lvType->lvt_applicable_to_id == $gender) {
                        //     $isValidGender = true;
                        // }
                        // dd($existingLeaveBal, $updateData);
                        // dd(($existingLeaveBal && ($lvType->lvt_applicable_to_id == 0 || $isValidGender)));
                        // Insert if leave is not assigned to the employee
                        // if ($existingLeaveBal && ($lvType->lvt_applicable_to_id == 0 || $isValidGender)) {
                        //     LeaveBalance::create($updateData);
                        // }
                    }
                }

                // Update SchedulerProcessTrack with the processed count
                $schedulerTrack->update([
                    'spt_processed_count' => $schedulerTrack->spt_processed_count + $employees->count(),
                ]);

                // }
            }
        } else {
            dd('Command is scheduled to run only on the 1st of the month at 12 AM.');
        }
    }

    // Helper function to validate if the leave cycle is applicable
    private function isValidCycle($leaveCycleId, $currentMonth)
    {
        if ($leaveCycleId == 219) { // Monthly
            return true;
        } elseif ($leaveCycleId == 220) { // Yearly
            return $currentMonth == 1; // Only process yearly leave at the beginning of the year
        }
        return false;
    }

    // Helper function to calculate carried forward leave
    private function calculateCarriedForward($existingLeaveBal, $unusedLeaveRule, $carryForwardLimit)
    {
        if ($unusedLeaveRule == 221) { // Carry Forward
            $carriedForward = $existingLeaveBal->lb_balance_remaining_leave ?? 0;
            return min($carriedForward, $carryForwardLimit); // Respect the carry forward limit
        } elseif ($unusedLeaveRule == 222) { // Lapse
            return 0;
        }
        return 0;
    }

    public function testmap(Request $request)
    {

        $businessTime = now()->format('H:i:s');
        dd($businessTime);
    }

    public function show($encryptedId)
    {
        // Fetch the candidate along with its recruitment data
        $recruitmentCandidate = RecruitmentCandidate::with('fh_recruitment')
            ->whereRaw('md5(rc_id) = ?', [$encryptedId]);
        // ->first(); // Use first() to get the first matching record
        $candidate = $recruitmentCandidate->pluck('rc_name', 'rc_id')->toArray();
        // Ensure the candidate was found
        $recruitmentCandidate = $recruitmentCandidate->first();
        if ($recruitmentCandidate) {
            // Get the associated recruitment data (assuming there is a relationship)
            $recruitment = $recruitmentCandidate->fh_recruitment;

            // Check if the recruitment data exists
            if ($recruitment) {
                // Decode the 'r_managers' JSON field and fetch the associated interviewers
                $interviewers = Employee::whereIn('emp_id', json_decode($recruitment->r_managers))
                    ->pluck('emp_full_name', 'emp_id')->toArray();

                // Debug output of recruitmentCandidate, key-value pair of rc_name and rc_id, recruitment, and interviewers
                return view('admin.setting.setting', compact('candidate', 'recruitment', 'interviewers', 'recruitmentCandidate'));
            } else {
                // Handle the case where the recruitment data is missing
                dd('Recruitment data not found for candidate.');
            }
        } else {
            // Handle the case where no candidate is found
            dd('Candidate not found.');
        }



        return view('admin.setting.setting', compact('candidate', 'recruitment', 'interviewer'));
        $decryptedId = Crypt::decrypt($encryptedId);
        $user = Auth::user();
        $gender = MasterTable::where("m_group", 'GENDER')->pluck('m_name', 'm_id')->toArray();
        $recruitment = Recruitment::find($decryptedId);
        $designations = Designation::where('dg_b_id', $user->emp_b_id);
        if ($decryptedId) {
            $designations =   $designations->whereIn("dg_id", json_decode($recruitment->r_dg_id));
        }
        $designations =   $designations->pluck('dg_name', 'dg_id')->toArray();
        // dd($designationId)
        $country = Country::pluck('c_name', 'c_id')->toArray();
        return view('recruitment.application-form', compact('designations', 'gender', 'country', 'recruitment', 'decryptedId'));
        dd($encryptedId, $decryptedId);
        // $cc = Carbon::parse('12:46 AM');
        // $id = 357;
        // Fetch travel record with related travel type
        $travelRecord = TadaRequestPlan::with('fh_policy_tada_travel_type')
            ->whereHas('fh_policy_tada_travel_type', function ($query) {
                $query->where('pttt_type_id', 125);  // Adjust the column name based on your actual schema
            })
            ->find($id);

        // Check if travel record exists
        if (!$travelRecord) {
            return '<pre>No travel record found.</pre>';
        }

        // Get travel details that match the conditions
        $travelDetails = $travelRecord->fh_tada_request_details->filter(function ($detail) {
            return $detail->trd_type_id == 181 || !is_null($detail->trd_segments); // check travel id = 181 and travel tap location only
        });

        // Fetch the expenses related to the travel record
        $travelExpenses = $travelRecord->fh_tada_expenses;

        // Check if the relationship returns a collection
        if ($travelExpenses) {
            // Filter expenses with te_type_id equal to 159
            $travelExpenses = $travelExpenses->filter(function ($expense) {
                return $expense->te_type_id == 159;
            });
        }

        // Initialize variables to hold min and max date and time
        $minDateTime = null;
        $maxDateTime = null;
        $totalDistance = 0;

        // Function to compare and set min and max date times

        // Loop through each travel detail
        foreach ($travelDetails as $detail) {
            $totalDistance += $detail->trd_total_distance;
            // Check if trd_start_date and trd_start_time have values
            if (!empty($detail->trd_start_date) && !empty($detail->trd_start_time)) {
                $compareAndSetRes = $this->compareAndSet($minDateTime, $maxDateTime, $detail->trd_start_date . ' ' . $detail->trd_start_time);
                $minDateTime = $compareAndSetRes['minDateTime'];
                $maxDateTime = $compareAndSetRes['maxDateTime'];
            }
            // Check if trd_end_date and trd_end_time have values
            if (!empty($detail->trd_end_date) && !empty($detail->trd_end_time)) {
                $compareAndSetRes = $this->compareAndSet($minDateTime, $maxDateTime, $detail->trd_end_date . ' ' . $detail->trd_end_time);
                $minDateTime = $compareAndSetRes['minDateTime'];
                $maxDateTime = $compareAndSetRes['maxDateTime'];
            }

            // Check if trd_segments has a value
            if (!empty($detail->trd_segments)) {
                // Decode the segments JSON
                $segments = json_decode($detail->trd_segments, true);
                foreach ($segments as $segment) {
                    // Check if segment has date and time values
                    if (!empty($segment['date']) && !empty($segment['time'])) {
                        $time = date("H:i:s", strtotime($segment['time'])); // Convert to 24-hour format
                        $segmentDateTime = $segment['date'] . ' ' . $time;
                        $compareAndSetRes = $this->compareAndSet($minDateTime, $maxDateTime, $segmentDateTime);
                        $minDateTime = $compareAndSetRes['minDateTime'];
                        $maxDateTime = $compareAndSetRes['maxDateTime'];
                    }
                }
            }
        }

        // Process travel expenses for date/time
        foreach ($travelExpenses as $expense) {
            $totalDistance += $expense->te_total_km_driven;
            if (!empty($expense->te_from_date) && !empty($expense->te_from_time)) {
                $compareAndSetRes = $this->compareAndSet($minDateTime, $maxDateTime, $expense->te_from_date . ' ' . $expense->te_from_time);
                $minDateTime = $compareAndSetRes['minDateTime'];
                $maxDateTime = $compareAndSetRes['maxDateTime'];
            }
            if (!empty($expense->te_to_date) && !empty($expense->te_to_time)) {
                $compareAndSetRes = $this->compareAndSet($minDateTime, $maxDateTime, $expense->te_to_date . ' ' . $expense->te_to_time);
                $minDateTime = $compareAndSetRes['minDateTime'];
                $maxDateTime = $compareAndSetRes['maxDateTime'];
            }
        }



        // Calculate the total daytime hours and messages
        $this->calculateDaytimeHours($minDateTime, $maxDateTime, $totalDistance);
    }

    function compareAndSet(&$minDateTime, &$maxDateTime, $dateTime)
    {
        if ($minDateTime === null || $dateTime < $minDateTime) {
            $minDateTime = $dateTime;
        }
        if ($maxDateTime === null || $dateTime > $maxDateTime) {
            $maxDateTime = $dateTime;
        }

        return ['minDateTime' => $minDateTime, 'maxDateTime' => $maxDateTime];
    }

    function calculateDaytimeHours($start, $end, $totalDistance = 0)
    {
        $daPerDayAmount = 500;
        $startDateTime = new DateTime($start);
        $endDateTime = new DateTime($end);
        $dayStart = new DateTime($startDateTime->format('Y-m-d H:i:s'));
        $dayEnd = new DateTime($endDateTime->format('Y-m-d H:i:s'));

        $totalDaytimeHours = 0;
        $i = 0;
        $daCalculationMessages = '';
        $daAmount = 0;
        while ($dayStart < $dayEnd) {
            ++$i;

            // Check if the current day is the last day
            if (new DateTime($dayStart->format('Y-m-d')) == new DateTime($dayEnd->format('Y-m-d'))) {
                // On the last day, check if the end time is after 23:59:00
                $effectiveEnd = ($dayEnd->format('H:i:s') > '23:59:00') ?
                    (new DateTime($dayEnd->format('Y-m-d') . ' 23:59:00')) : $dayEnd;
            } else {
                // For other days, set the effective end time to 23:59:00
                // $dayStart = new DateTime($dayStart->format('Y-m-d') . ' 06:00:00');
                $effectiveEnd = new DateTime($dayStart->format('Y-m-d') . ' 23:59:00');
            }

            // Calculate the interval and daytime hours
            $interval = $dayStart->diff($effectiveEnd);
            $daytimeHours = ($interval->h) + ($interval->i / 60); // Convert to decimal hours
            $amount = ($daytimeHours == 8) ? ($daPerDayAmount / 2) : (($daytimeHours > 8) ? $daPerDayAmount : 0);
            $daAmount += $amount;


            // Accumulate total daytime hours
            $daCalculationMessages .= $i . ' start date ' . $dayStart->format('Y-m-d H:i:s') .
                ' day end ' . $effectiveEnd->format('Y-m-d H:i:s') .
                ' hours ' . $daytimeHours . ' amount ' . $amount . "\n<br>"; // Use .= to append messages

            $totalDaytimeHours += $daytimeHours;

            // Move to the next day
            $dayStart = new DateTime($dayStart->format('Y-m-d') . ' 06:00:00');
            $dayStart->modify('+1 day');
        }

        echo "start date : " . $start;
        echo "<br>";
        echo "end date : " . $end;
        echo "<br>";

        echo "Da Per Day Amount : " . $daPerDayAmount;
        echo "<br>";

        echo "Calculated Hours : " . $totalDaytimeHours;
        echo "<br>";
        echo "Calculated Message : <br>" . $daCalculationMessages;
        echo "Total Da Sum Amount : " . $daAmount;
        echo "<br>";
        echo "total travel " . number_format($totalDistance, 2);
    }





    public function test()
    {
        // return view('test-face-detection');
        $approver_mapping = [
            "VM102" => ["approver_1" => "super_admin", "approver_2" => ""],
            "VM103" => ["approver_1" => "VM102", "approver_2" => "super_admin"],
            "VM104" => ["approver_1" => "VM102", "approver_2" => "super_admin"],
            "VM105" => ["approver_1" => "super_admin", "approver_2" => ""],
            "VM106" => ["approver_1" => "VM102", "approver_2" => "super_admin"],
            "VM107" => ["approver_1" => "super_admin", "approver_2" => ""],
            "VM109" => ["approver_1" => "VM107", "approver_2" => "super_admin"],
            "VM110" => ["approver_1" => "VM107", "approver_2" => "super_admin"],
            "VM111" => ["approver_1" => "VM107", "approver_2" => "super_admin"],
            "VM112" => ["approver_1" => "VM107", "approver_2" => "super_admin"],
            "VM113" => ["approver_1" => "VM107", "approver_2" => "super_admin"],
            "VM114" => ["approver_1" => "VM107", "approver_2" => "super_admin"],
            "VM115" => ["approver_1" => "VM107", "approver_2" => "super_admin"],
            "VM116" => ["approver_1" => "VM107", "approver_2" => "super_admin"],
            "VM117" => ["approver_1" => "VM107", "approver_2" => "super_admin"],
            "VM118" => ["approver_1" => "VM107", "approver_2" => "super_admin"],
            "VM119" => ["approver_1" => "VM107", "approver_2" => "super_admin"],
            "VM120" => ["approver_1" => "VM107", "approver_2" => "super_admin"],
            "VM121" => ["approver_1" => "VM107", "approver_2" => "super_admin"],
            "VM123" => ["approver_1" => "super_admin", "approver_2" => ""],
            "VM124" => ["approver_1" => "VM123", "approver_2" => "super_admin"],
            "VM125" => ["approver_1" => "VM123", "approver_2" => "super_admin"],
            "VM126" => ["approver_1" => "VM123", "approver_2" => "super_admin"],
            "VM127" => ["approver_1" => "VM123", "approver_2" => "super_admin"],
            "VM130" => ["approver_1" => "VM123", "approver_2" => "super_admin"],
            "VM131" => ["approver_1" => "VM123", "approver_2" => "super_admin"],
            "VM132" => ["approver_1" => "VM123", "approver_2" => "super_admin"],
            "VM133" => ["approver_1" => "VM123", "approver_2" => "super_admin"],
            "VM135" => ["approver_1" => "VM123", "approver_2" => "super_admin"],
            "VM136" => ["approver_1" => "VM102", "approver_2" => "super_admin"],
            "VM137" => ["approver_1" => "VM102", "approver_2" => "super_admin"],
            "VM138" => ["approver_1" => "VM102", "approver_2" => "super_admin"],
            "VM139" => ["approver_1" => "VM102", "approver_2" => "super_admin"],
            "VM140" => ["approver_1" => "VM102", "approver_2" => "super_admin"],
            "VM141" => ["approver_1" => "VM102", "approver_2" => "super_admin"],
            "VM143" => ["approver_1" => "VM105", "approver_2" => "super_admin"],
            "VM144" => ["approver_1" => "VM105", "approver_2" => "super_admin"],
            "VM145" => ["approver_1" => "VM105", "approver_2" => "super_admin"],
            "VM146" => ["approver_1" => "VM105", "approver_2" => "super_admin"],
            "VM147" => ["approver_1" => "VM105", "approver_2" => "super_admin"],
            "VM142" => ["approver_1" => "VM105", "approver_2" => "super_admin"],
            "VM148" => ["approver_1" => "VM123", "approver_2" => "super_admin"],
            "VM149" => ["approver_1" => "VM123", "approver_2" => "super_admin"],
            "VM150" => ["approver_1" => "VM123", "approver_2" => "super_admin"],
            "VM151" => ["approver_1" => "VM105", "approver_2" => "super_admin"],
            "VM152" => ["approver_1" => "VM105", "approver_2" => "super_admin"],
            "VM153" => ["approver_1" => "VM123", "approver_2" => "super_admin"],
            "VM154" => ["approver_1" => "VM102", "approver_2" => "super_admin"]
        ];
        $approverMapping = [];
        $businessId = 54;

        foreach ($approver_mapping as $key => $value) {


            $emp = Employee::where(['emp_b_id' => $businessId, 'emp_code' => $key])
                ->select('emp_id', 'emp_code')
                ->first();


            // Check if employee exists
            if (!$emp) {
                continue; // Skip if employee not found
            }


            $emp_approver_1 = NULL;
            // Fetch first approver details
            $emp_approver_1 = Employee::where(['emp_b_id' => $businessId, 'emp_code' => $value['approver_1'] ?? null])
                ->select('emp_id', 'emp_code')
                ->first();
            if(is_null($emp_approver_1)){
                $emp_approver_1 = NULL;
            }

            // Fetch second approver details if provided
            $emp_approver_2 = NULL;
            if (!empty($value['approver_2'])) {
                $emp_approver_2 = Employee::where(['emp_b_id' => $businessId, 'emp_code' => $value['approver_2']])
                    ->select('emp_id', 'emp_code')
                    ->first();
            }

            // Add to mapping with null safety
            $approverMapping[] = [
                'eam_b_id'=> $businessId,
                'eam_module_id'=>250,
                'eam_emp_id' =>$emp->emp_id,
                'eam_approver_manager_1' => $emp_approver_1->emp_id ?? NULL,
                'eam_approver_manager_2' => $emp_approver_2->emp_id ?? NULL,
            ];
        }
        EmployeeApprovalMapping::insert($approverMapping);
        dd('data inserted');


    }

    public function exportAttendanceDataExcel($attendanceDetails, $month, $year, $business)
    {

        set_time_limit(0);

        $exportData = [];
        $daysInMonth = Carbon::createFromFormat('Y-m', "{$year}-{$month}")->daysInMonth;


        $logoFileName = $business->b_logo;
        $logoPath = public_path($logoFileName);


        if (!file_exists($logoPath)) {
            throw new \Exception("Logo file not found at: {$logoPath}");
        }

        foreach ($attendanceDetails as $entry) {

            $employee = $entry['employee'];
            $dailyAttendance = $entry['attendanceDetails'];

            $row = [
                'Employee ID' => $employee->emp_code,
                'First Name' => $employee->emp_full_name,
            ];

            for ($day = 1; $day <= $daysInMonth; $day++) {

                $status_id = $dailyAttendance[$day - 1]['status_id'] ? $dailyAttendance[$day - 1]['status_id'] : null;
                $status = $dailyAttendance[$day - 1]['status_code'] ?? '--';
                $is_approved = $dailyAttendance[$day - 1]['isApproved'] ? $dailyAttendance[$day - 1]['isApproved'] : null;
                $leave_category_ids = MasterTable::where('m_group', 'LEAVE_CATEGORY')->pluck('m_id')->toArray();

                if (in_array($status_id, $leave_category_ids) && $is_approved) {
                    $attendance_status = isset($dailyAttendance[$day - 1]['atd_status_code']) ? $dailyAttendance[$day - 1]['atd_status_code'] : null;
                    if ($attendance_status) { //this condition is for half day leave & half day present
                        $status = $attendance_status . '+' . $status;
                    } else {
                        $status = MasterTable::whereIn('m_id', [$status_id, 251])->pluck('m_type')->toArray(); //$status_id==leave category id (, 251==Present(P)
                        $status = implode('+', $status);
                    }
                } else {
                    if ($status_id == 228 && $is_approved) { // if miessed punch is approved automatically it will be considered as present
                        $status = MasterTable::whereIn('m_id', [228, 251])->pluck('m_type')->toArray(); //228==Missedpunch (MSP), 251==Present(P)
                        $status = implode('+', $status); //MSP+P
                    }
                }

                $row["Day {$day}"] =  $status;
            }
            $row = array_merge($row, [
                'P' => array_sum(array_column($dailyAttendance, 'presentCount')),
                'HD' => array_sum(array_column($dailyAttendance, 'halfDayCount')),
                'A' => array_sum(array_column($dailyAttendance, 'absentCount')),
                'WO' => array_sum(array_column($dailyAttendance, 'weekOffCount')),
                'H' => array_sum(array_column($dailyAttendance, 'holidayCount')),
                'PMSP ' => array_sum(array_column($dailyAttendance, 'approvedMissedPunchCount')),
                'MSP' => array_sum(array_column($dailyAttendance, 'missedPunchCount')),
                'PL' => array_sum(array_column($dailyAttendance, 'approvedLeaveCount')),
                'UPL ' => array_sum(array_column($dailyAttendance, 'leaveCount')),
                'LC' => array_sum(array_column($dailyAttendance, 'lateCount')),
                'EG' => array_sum(array_column($dailyAttendance, 'earlyExitCount')),
                'OT' => array_sum(array_column($dailyAttendance, 'overtimeCount')),
            ]);
            $exportData[] = $row;
        }

        $fileName = "Attendance_Report_{$month}_{$year}.xlsx";

        return Excel::download(new class($exportData, $daysInMonth, $month, $year, $logoPath, $business) implements
            \Maatwebsite\Excel\Concerns\FromArray,
            \Maatwebsite\Excel\Concerns\WithHeadings,
            \Maatwebsite\Excel\Concerns\WithEvents
        {
            private $data;
            private $daysInMonth;
            private $month;
            private $year;
            private $logoPath;
            private $business;

            public function __construct($data, $daysInMonth, $month, $year, $logoPath, $business)
            {
                $this->data = $data;
                $this->daysInMonth = $daysInMonth;
                $this->month = $month;
                $this->year = $year;
                $this->logoPath = $logoPath;
                $this->business = $business;
            }

            public function array(): array
            {
                return $this->data;
            }

            public function headings(): array
            {
                $headings = ['Emp Code', 'Emp Name'];

                for ($day = 1; $day <= $this->daysInMonth; $day++) {
                    $date = Carbon::createFromDate($this->year, $this->month, $day);
                    $dayNumber = $date->format('d'); // Day number (e.g., 01)
                    $monthShort = $date->format('M'); // Short month name (e.g., Nov)
                    $yearShort = $date->format('y'); // Two-digit year (e.g., 24)
                    $dayName = $date->format('D'); // Day name (e.g., Fri)

                    $headings[] = "{$dayNumber}\n{$monthShort}\n{$yearShort}\n{$dayName}";
                }

                return array_merge($headings, [
                    'P',
                    'HD',
                    'A',
                    'WO',
                    'H',
                    'PMSP',
                    'MSP',
                    'PL',
                    'UPL',
                    'LC',
                    'EG',
                    'OT'
                ]);
            }

            public function registerEvents(): array
            {
                return [
                    \Maatwebsite\Excel\Events\AfterSheet::class => function (\Maatwebsite\Excel\Events\AfterSheet $event) {
                        $sheet = $event->sheet;



                        $worksheet = $sheet->getDelegate();
                        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                        $drawing->setPath($this->logoPath); // Path to the logo file
                        $drawing->setHeight(100); // Adjust the height of the logo
                        $drawing->setCoordinates('A1'); // Specify the top-left corner of the cell where the logo will be placed
                        $drawing->setOffsetX(10); // Adjust horizontal offset inside the cell
                        $drawing->setOffsetY(5); // Adjust vertical offset inside the cell
                        $drawing->setWorksheet($worksheet); // Attach the drawing to the worksheet

                        $sheet->getColumnDimension('A')->setWidth(15); // Adjust the width of column 'A'
                        $sheet->getRowDimension('1')->setRowHeight(80); // Adjust the height of row '1'


                        $sheet->insertNewRowBefore(1, 5);


                        $sheet->mergeCells('D1:I1');
                        $sheet->setCellValue('D1', $this->business->b_name);
                        $sheet->getStyle('D1')->applyFromArray([
                            'font' => ['bold' => true, 'size' => 14],
                            'alignment' => ['horizontal' => 'left', 'vertical' => 'center'],
                        ]);


                        $sheet->mergeCells('D2:I2');
                        $sheet->setCellValue('D2', 'Attendance Report');

                        $sheet->getStyle('D2')->applyFromArray([
                            'font' => ['bold' => false, 'size' => 14],
                            'alignment' => ['horizontal' => 'left', 'vertical' => 'center'],
                        ]);

                        $monthName = Carbon::createFromFormat('m', $this->month)->format('F');
                        $monthYear = "{$monthName} {$this->year}";

                        $startDate = Carbon::createFromDate($this->year, $this->month, 1)->format('d-m-Y'); // Format: DD-MM-YYYY
                        $endDate = Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth()->format('d-m-Y'); // Format: DD-MM-YYYY

                        $sheet->mergeCells('D3:I3');
                        $sheet->setCellValue('D3', "Date (From: {$startDate} To: {$endDate})");
                        $sheet->getStyle('D3')->applyFromArray([
                            'font' => ['bold' => false, 'size' => 12],
                            'alignment' => ['horizontal' => 'left', 'vertical' => 'center'],
                        ]);




                        $sheet->mergeCells('A4:Z4');
                        $sheet->setCellValue('A4', '');


                        $startColumn = 3;
                        $lastDateColumnIndex = $startColumn + $this->daysInMonth - 1;
                        $lastDateColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastDateColumnIndex);

                        $sheet->mergeCells("C5:{$lastDateColumn}5");
                        $sheet->setCellValue('C5', $monthYear);
                        $sheet->getStyle("C5:{$lastDateColumn}5")->applyFromArray([
                            'font' => ['bold' => true],
                            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                            'borders' => [
                                'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                                'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                                'left' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                                'right' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                            ],
                        ]);

                        $summaryStartColumnIndex = $lastDateColumnIndex + 1;
                        $summaryStartColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($summaryStartColumnIndex);

                        $lastSummaryColumnIndex = $summaryStartColumnIndex + 12;
                        $lastSummaryColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastSummaryColumnIndex);

                        $sheet->mergeCells("{$summaryStartColumn}5:{$lastSummaryColumn}5");
                        $sheet->setCellValue("{$summaryStartColumn}5", 'Attendance Summary');
                        $sheet->getStyle("{$summaryStartColumn}5:{$lastSummaryColumn}5")->applyFromArray([
                            'font' => ['bold' => true],
                            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                        ]);




                        $sheet->getStyle("{$summaryStartColumn}5:{$lastSummaryColumn}9")->applyFromArray([
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                    'color' => ['rgb' => '000000'],
                                ],
                            ],
                        ]);


                        $sheet->getStyle("{$summaryStartColumn}5:{$lastSummaryColumn}9")->applyFromArray([
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                    'color' => ['rgb' => '000000'],
                                ],
                            ],
                        ]);




                        $sheet->getStyle("A6:{$lastDateColumn}{$sheet->getHighestRow()}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                        $sheet->getStyle("A6:{$lastDateColumn}6")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                        $sheet->getStyle("A6:{$lastDateColumn}6")->applyFromArray([
                            'alignment' => [
                                'wrapText' => true, // Enable text wrapping
                                'horizontal' => 'center', // Center-align horizontally
                                'vertical' => 'center', // Center-align vertically
                            ],
                            'font' => [
                                'size' => 10, // Adjust the font size if needed
                            ],
                        ]);



                        $sheet->mergeCells('A5:B5');
                        $sheet->setCellValue('A5', 'Employee Details');
                        $sheet->getStyle('A5:B5')->applyFromArray([
                            'font' => ['bold' => true],
                            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                            'borders' => [
                                'top' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                                'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                                'left' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                                'right' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                            ],
                        ]);


                        $sheet->getStyle('A6:AR6')->applyFromArray([
                            'font' => ['bold' => true],
                            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                        ]);

                        $footerRow = $sheet->getHighestRow() + 10;
                        $lastColumn = $sheet->getHighestColumn();
                        $sheet->mergeCells("A{$footerRow}:{$lastColumn}{$footerRow}");
                        $sheet->setCellValue("A{$footerRow}", "P=>Present, HD=>Half Day, A=>Absent, WO=>Week Off, H=> Holiday, MSP=>Missed Punch, PMSP=>Paid Missed Punch, PL=>Paid Leave, UPL=>Unpaid Leave, LC=> Late Comming, EG=>Early Going, OT=>Over Time");
                        $sheet->getStyle("A{$footerRow}:{$lastColumn}{$footerRow}")->applyFromArray([
                            'font' => ['italic' => true, 'size' => 10],
                            'alignment' => ['horizontal' => 'left', 'vertical' => 'center'],
                        ]);


                        $footerRow = $sheet->getHighestRow() + 1;
                        $lastColumn = $sheet->getHighestColumn();
                        $sheet->mergeCells("A{$footerRow}:{$lastColumn}{$footerRow}");
                        $sheet->setCellValue("A{$footerRow}", "Note: For today`s attendance, count would temporarily reflect in MSP till the punch out and the status shown would be `Present`
");
                        $sheet->getStyle("A{$footerRow}:{$lastColumn}{$footerRow}")->applyFromArray([
                            'font' => ['bold' => true, 'size' => 10],
                            'alignment' => ['horizontal' => 'left', 'vertical' => 'center'],
                        ]);
                    },
                ];
            }
        }, $fileName, \Maatwebsite\Excel\Excel::XLSX);
    }





    private function getLocationFromCoordinates($latitude, $longitude)
    {
        // Your Google Maps API key from .env
        $apiKey = config('credentials')['MAP_API_KEY'];

        // Make the API request
        $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
            'latlng' => $latitude . ',' . $longitude,
            'key' => $apiKey,
        ]);

        // Convert the JSON response into an array
        $data = $response->json();

        // Check if the response contains results
        if (!empty($data['results'])) {
            // Extract detailed address components
            $addressComponents = $data['results'][0]['address_components'];

            // Prepare a detailed response
            $locationDetails = [
                'formatted_address' => $data['results'][0]['formatted_address'],
                'latitude' => $latitude,
                'longitude' => $longitude,
                'country' => $this->getAddressComponent($addressComponents, 'country'),
                'state' => $this->getAddressComponent($addressComponents, 'administrative_area_level_1'),
                'city' => $this->getAddressComponent($addressComponents, 'locality'),
                'sublocality' => $this->getAddressComponent($addressComponents, 'sublocality'),
                'postal_code' => $this->getAddressComponent($addressComponents, 'postal_code'),
                'neighborhood' => $this->getAddressComponent($addressComponents, 'neighborhood'),
                'area' => $this->getAddressComponent($addressComponents, 'administrative_area_level_2'),  // Can represent district/area
            ];

            return $locationDetails;
        }

        return null;
    }

    private function getAddressComponent($components, $type)
    {
        // Helper function to extract the specific type from address components
        foreach ($components as $component) {
            if (in_array($type, $component['types'])) {
                return $component['long_name'];  // Return the full name of the component
            }
        }

        return null;  // Return null if the component is not found
    }


    public function testReverbPage()
    {
        return view('test-reverb-page');
    }


    public function testReverb()
    {
        event(new TestReverbEvent('Test vk'));
    }

    public function testTabs()
    {
        return view('test-tabs');
    }

    public function create_desig()
    {
        $departments = Depart::all();
        $designations = Desig::all();
        return view('admin.setting.business.departs', compact('departments', 'designations'));
    }

    public function save_new_desig(Request $request)
    {

        // dd($request->designations);
        foreach ($request->new_designations as $index => $designationId) {
            if ($request->new_designations[$index]) {
                // Create new designation
                $newDesignation = new Desig();
                $newDesignation->desig_name = $request->new_designations[$index];
                $newDesignation->dept_id = $request->department_id;
                $newDesignation->save();
            }

            // Assign designation and other logic here
            // You may want to associate the employee with this designation
        }
    }


    function calculateMonthlySalary($employeeId, $month, $year)
    {
        $ctc = 30000;
        $basicSalary = $ctc * 0.50;
        $hra = $ctc * 0.20;
        $conveyance = $ctc * 0.10;
        $medical = $ctc * 0.05;
        $otherAllowance = $ctc * 0.15;

        $grossSalary = $basicSalary + $hra + $conveyance + $medical + $otherAllowance;
        $pf = $basicSalary * 0.12;
        $esic = $grossSalary > 21000 ? 0 : $grossSalary * 0.0175;

        $workingDays = 22; // Adjust as per the actual working days in the month
        $dailySalary = $grossSalary / $workingDays;
        $hourlyRate = $dailySalary / 8;

        // Get attendance details
        $attendanceRecords = AttendanceRecord::where('employee_id', $employeeId)
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->get();

        $latePenalty = 0;
        $earlyExitPenalty = 0;
        $absentPenalty = 0;
        $overtimePay = 0;

        foreach ($attendanceRecords as $record) {
            // Late coming penalty
            if ($record->is_late) {
                $latePenalty += $hourlyRate;
            }

            // Early exit penalty
            if ($record->early_exit_hours > 0) {
                $earlyExitPenalty += $record->early_exit_hours * $hourlyRate;
            }

            // Absence penalty
            if ($record->is_absent) {
                $absentPenalty += $dailySalary;
            }

            // Overtime pay
            if ($record->is_overtime && $record->overtime_hours > 0) {
                $overtimePay += $record->overtime_hours * $hourlyRate * 1.5; // 1.5x rate
            }
        }

        // Total deductions
        $totalDeductions = $pf + $esic + $latePenalty + $earlyExitPenalty + $absentPenalty;

        // Loan/Advance Deduction (if any)
        $loanDeduction = Loan::where('employee_id', $employeeId)->sum('monthly_deduction');
        $totalDeductions += $loanDeduction;

        // Net Salary
        $netSalary = $grossSalary - $totalDeductions + $overtimePay;

        return [
            'gross_salary' => $grossSalary,
            'total_deductions' => $totalDeductions,
            'overtime_pay' => $overtimePay,
            'net_salary' => $netSalary,
        ];
    }


    public function storeEmployeeMonthlyAttendance($emp_id, $month, $customCheckInTime = null, $customCheckOutTime = null, $customBranchName = null, $customLatitude = null, $customLongitude = null)
    {
        /* ********************************************************** Testing Route ********************************************** */
        // Only Required Parameters	/attendance/store/430/11
        // All Optional Parameters	/attendance/store/430/11/2024-11-01T09:00:00/2024-11-01T17:00:00/Main%20Office/12.34567/76.54321
        // Custom Check-In and Check-Out Only	/attendance/store/430/11/2024-11-01T09:00:00/2024-11-01T17:00:00
        // Custom Branch Name	/attendance/store/430/11//Main%20Office
        // Custom Latitude and Longitude	/attendance/store/430/11///12.34567/76.54321
        // No Optional Parameters	/attendance/store/430/11
        // Attendance Status	Route Example	Interpretation
        // Mispunch	/attendance/store/430/11/2024-11-01T09:00:00//	Checked in at 9:00 AM, no check out recorded.
        // Full Day Present	/attendance/store/430/11/2024-11-01T09:00:00/2024-11-01T17:00:00	Checked in at 9:00 AM, checked out at 5:00 PM.
        // Half Day Present	/attendance/store/430/11/2024-11-01T09:00:00/2024-11-01T12:00:00	Checked in at 9:00 AM, checked out at 12:00 PM.
        // Overtime	/attendance/store/430/11/2024-11-01T09:00:00/2024-11-01T20:00:00	Checked in at 9:00 AM, checked out at 6:00 PM.
        // Partial	/attendance/store/430/11/2024-11-01T09:00:00/2024-11-01T15:00:00	Checked in at 9:00 AM, checked out at 3:00 PM.

        $year = Carbon::now()->year;
        $startDate = Carbon::parse("$year-$month-01");
        $endDate = $startDate->copy()->endOfMonth();

        // Find Employee Record
        $employee = Employee::with('fh_shift_type', 'fh_branch')->find($emp_id);
        if (!$employee) {
            return response()->json(['message' => "Employee not found"], 404);
        }

        // Set default values based on policy or custom inputs
        $defaultCheckInTime = optional($employee->fh_shift_type)->pst_start_time ?? $startDate->copy()->setTime(9, 0);
        $defaultCheckOutTime = optional($employee->fh_shift_type)->pst_end_time ?? $startDate->copy()->setTime(17, 0);
        $checkInTime = $customCheckInTime ?? $defaultCheckInTime;
        $checkOutTime = $customCheckOutTime ?? $defaultCheckOutTime;
        $locationName = $customBranchName ?? optional($employee->fh_branch)->br_name;
        $locationLatitude = $customLatitude ?? optional($employee->fh_branch)->br_latitude;
        $locationLongitude = $customLongitude ?? optional($employee->fh_branch)->br_longitude;

        // Loop through each day in the month
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            // Initial creation or updating of attendance record
            $data = AttendanceRecord::updateOrCreate(
                [
                    'atd_emp_id' => $emp_id,
                    'atd_date' => $date->format('Y-m-d'),
                ],
                [
                    'atd_b_id' => $employee->emp_b_id,
                    'atd_pst_id' => optional($employee->fh_shift_type)->pst_id ?? '',
                    'atd_check_in_time' => $checkInTime,
                    'atd_check_out_time' => $checkOutTime,
                    'atd_punchin_location' => $locationName,
                    'atd_punchout_location' => $locationName,
                    'atd_longitude_punchin' => $locationLongitude,
                    'atd_latitude_punchin' => $locationLatitude,
                    'atd_longitude_punchout' => $locationLongitude,
                    'atd_latitude_punchout' => $locationLatitude,
                    'atd_punchin_photo' => '[]',
                    'atd_punchout_photo' => '[]',
                    'atd_stage_completed' => 0,
                    'atd_is_absent' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // Now that $data is defined, calculate and update fields
            $this->updateAttendanceFields($data, $defaultCheckInTime, $defaultCheckOutTime);
        }

        return response()->json(['message' => "Attendance records for $month $year stored successfully"], 200);
    }

    // Function to update attendance fields
    private function updateAttendanceFields(AttendanceRecord $data, $defaultCheckInTime, $defaultCheckOutTime)
    {
        $data->update([
            'atd_is_late' => $this->isLate($data, $defaultCheckInTime),
            'atd_late_duration' => $this->calculateLateDuration($data, $defaultCheckInTime),
            'atd_is_overtime' => $this->isOvertime($data, $defaultCheckOutTime),
            'atd_overtime_hours' => $this->calculateOvertime($data, $defaultCheckOutTime)['hours'],
            // 'atd_overtime_hours' => number_format($this->calculateOvertime($data, $defaultCheckOutTime)['duration'], 2, '.', ''),
            'atd_attendance_status' => $this->calculateAttendanceStatus($data),
        ]);
    }

    // Function to check if employee is late
    private function isLate($data, $defaultCheckInTime)
    {
        $checkInTime = Carbon::parse($data->atd_check_in_time);
        return $checkInTime->greaterThan($defaultCheckInTime) ? 1 : 0;
    }

    // Function to check overtime
    private function isOvertime($data, $defaultCheckOutTime)
    {
        $checkOutTime = Carbon::parse($data->atd_check_out_time);
        return $checkOutTime->greaterThan($defaultCheckOutTime) ? 1 : 0;
    }

    // Function to calculate late duration
    private function calculateLateDuration($data, $defaultCheckInTime)
    {
        $checkInTime = Carbon::parse($data->atd_check_in_time);
        return $checkInTime->greaterThan($defaultCheckInTime) ? $defaultCheckInTime->diffInMinutes($checkInTime) : 0;
    }

    // Function to check if employee has overtime and calculate hours and duration
    private function calculateOvertime(AttendanceRecord $data, $defaultCheckOutTime)
    {
        $checkOutTime = Carbon::parse($data->atd_check_out_time);
        $workedMinutes = $checkOutTime->diffInMinutes(Carbon::parse($data->atd_check_in_time));
        $defaultCheckoutMinutes = Carbon::parse($defaultCheckOutTime)->diffInMinutes(Carbon::parse($data->atd_check_in_time));

        $overtimeDuration = max(0, $workedMinutes - $defaultCheckoutMinutes);
        return [
            'hours' => $overtimeDuration / 60,
            'duration' => $overtimeDuration
        ];
    }

    private function calculateAttendanceStatus(AttendanceRecord $data)
    {
        $shift = $data->fh_policy_shift_timing;
        if (!$shift) {
            return 228; // Default to Mispunch if no shift data is found
        }

        // Retrieve shift start and end times
        $pst_start_time = Carbon::parse($shift->pst_start_time);
        $pst_end_time = Carbon::parse($shift->pst_end_time);

        // Calculate total shift duration in minutes
        $dailyWorkingMinutes = $pst_start_time->diffInMinutes($pst_end_time);
        $halfDayThreshold = $dailyWorkingMinutes / 2;

        // Check if check-in and check-out times exist
        if (empty($data->atd_check_in_time) || empty($data->atd_check_out_time)) {
            return 228; // Mispunch
        }

        // Calculate actual worked duration
        $checkInTime = Carbon::parse($data->atd_check_in_time);
        $checkOutTime = Carbon::parse($data->atd_check_out_time);
        $workedMinutes = $checkInTime->diffInMinutes($checkOutTime);

        // Determine attendance status based on worked duration
        return match (true) {
            $workedMinutes <= $halfDayThreshold => 252, // Half-Day
            $workedMinutes >= $dailyWorkingMinutes => 251, // Full-Day
            $workedMinutes > $halfDayThreshold && $workedMinutes < $dailyWorkingMinutes => 204, // Partial
            default => 203, // Absent
        };
    }


    private function getAttendanceData($employee, $currentDate, $weekOfDates)
    {
        $attendanceCount = 0;
        $leaveCount = 0;
        $holidayCount = 0;
        $weekOffCount = 0;
        $absentCount = 0;
        $halfDayCount = 0;
        $missedPunchCount = 0;

        // Check Attendance
        $attendanceRecord = $employee->attendance_record()
            ->whereDate('atd_date', $currentDate)
            ->first();

        if ($attendanceRecord && $attendanceRecord->fh_attendance_status) {
            switch ($attendanceRecord->atd_attendance_status) {
                case 228: // Miss Punch
                    $missedPunchCount++;
                    break;
                case 252: // Half Day
                    $halfDayCount++;
                    break;
                default: // Present
                    $attendanceCount++;
            }
        } else {
            // Check Leave
            $leaveRecord = $employee->leave_requests()
                ->where('lvr_start_date', '<=', $currentDate)
                ->where('lvr_end_date', '>=', $currentDate)
                ->first();
            if ($leaveRecord && $leaveRecord->fh_leave_category) {
                $leaveCount++;
            }

            // Check Holiday
            if ($leaveCount === 0) {
                $holidayRecord = PolicyHolidayList::where('phl_b_id', $employee->emp_b_id)
                    ->where('phl_start_date', '<=', $currentDate)
                    ->where('phl_end_date', '>=', $currentDate)
                    ->first();
                if ($holidayRecord) {
                    $holidayCount++;
                }
            }

            // Check Week Off
            if ($leaveCount === 0 && $holidayCount === 0 && in_array($currentDate, $weekOfDates)) {
                $weekOffCount++;
            }

            // Mark Absent if no records found and the date is in the past
            if (
                $leaveCount === 0 && $holidayCount === 0 && $weekOffCount === 0
                && Carbon::parse($currentDate)->isPast()
            ) {
                $absentCount++;
            }
        }

        return [
            'attendanceCount' => $attendanceCount,
            'leaveCount' => $leaveCount,
            'holidayCount' => $holidayCount,
            'weekOffCount' => $weekOffCount,
            'absentCount' => $absentCount,
            'halfDayCount' => $halfDayCount,
            'missedPunchCount' => $missedPunchCount,
        ];
    }


    public function getOccurrencesOfDaysInMonth(int $year, int $month): array
    {
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;
        $occurrences = [];

        // Initialize array for each day of the week
        $weekDays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        foreach ($weekDays as $day) {
            $occurrences[$day] = [];
        }

        // Iterate through all days in the month
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($year, $month, $day);
            $dayName = $date->format('l'); // Get the full day name (e.g., Sunday, Monday)
            $occurrences[$dayName][] = $date->toDateString();
        }

        return $occurrences;
    }

    public function registerEmp(Request $request)
    {
        $result = [];
        try {
            $imageFileNames = []; // Array to hold image file names
            // Process and save images
            $regNo = $request->reg_no;
            $folderPath = public_path("labels/{$regNo}/");

            if (!File::exists($folderPath)) {
                File::makeDirectory($folderPath, 0777, true, true); // Recursive directory creation
            }
            for ($i = 1; $i <= 5; $i++) {
                $capturedImageKey = "capturedImage{$i}";
                if ($request->has($capturedImageKey)) {
                    $base64Data = explode(',', $request->input($capturedImageKey))[1]; // Extract base64 data
                    $imageData = base64_decode($base64Data);
                    $fileName = "{$regNo}_image{$i}.png";
                    $labelName = "{$i}.png";
                    File::put("{$folderPath}{$labelName}", $imageData); // Save the image file
                    $imageFileNames[] = $fileName; // Add to filenames array
                }
            }

            $imagesJson = json_encode($imageFileNames);
            DB::table('face_detection_employees')->insert(['fd_name' => $request->name, 'fd_email' => $request->email, 'fd_registration_num' => $request->reg_no, 'fd_images' => $imagesJson]);
            $result['status'] = true;
            $result['message'] =  'Employee registered successfully.';
            return response()->json($result);
        } catch (\Exception $e) {
            $result['status'] = false;
            $result['message'] =  $e->getMessage();
            return response()->json($result);
        }
    }

    public function getStates(Request $request)
    {
        $states = State::where("s_c_id", $request->countryId)->get(["s_id as id", "s_name as name"]);

        // Return JSON response
        return response()->json([
            'states' => $states
        ]);
    }

    public function applicationFormSubmit(Request $request)
    {
        // dd($request->all());
        // Validate the form data
        $validatedData = $request->validate([
            'profile_image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048', // optional, must be an image
            'rc_name' => 'required|string|max:30',
            'rc_dg_id' => 'required|exists:designations,dg_id',  // at least one job position is selected
            'rc_email' => 'required|email|max:255|unique:users,email',  // email is unique
            'rc_mobile' => 'required|string|max:15|regex:/^[0-9]+$/', // only numbers
            'rc_gender' => 'required|exists:master_table,m_id',  // at least one gender is selected
            'rc_address' => 'required|string|max:255',
            'rc_country' => 'required|exists:countries,c_id',  // at least one country selected
            'rc_state' => 'required|exists:states,s_id',    // at least one state selected
            'rc_city' => 'required|string|max:50',
            'rc_resume' => 'required',
            'rc_address' => 'required',
            'rc_zip' => 'required',
            // 'rc_zip' => 'required|string|max:10|regex:/^\d{5}(-\d{4})?$/', // valid zip code
        ], [
            'rc_name.required' => 'Please enter your name',
            'rc_name.max' => 'Name should not be more than 30 characters',
            'rc_dg_id.required' => 'Please select at least one job position',
            'rc_dg_id.exists' => 'Please select at least one job position',
            'rc_email.required' => 'Please enter your email',
            'rc_email.email' => 'Please enter a valid email',
            'rc_email.unique' => 'Email already exists',
            'rc_mobile.required' => 'Please enter your mobile number',
            'rc_mobile.max' => 'Mobile number should not be more than 15 characters',
            'rc_mobile.regex' => 'Please enter a valid mobile number',
            'rc_gender.required' => 'Please select at least one gender',
            'rc_gender.exists' => 'Please select at least one gender',
            'rc_address.required' => 'Please enter your address',
            'rc_address.max' => 'Address should not be more than 255 characters',
            'rc_country.required' => 'Please select at least one country',
            'rc_country.exists' => 'Please select at least one country',
            'rc_state.required' => 'Please select at least one state',
            'rc_state.exists' => 'Please select at least one state',
            'rc_city.required' => 'Please enter your city',
            'rc_city.max' => 'City should not be more than 50 characters',
            'rc_resume.required' => 'Please upload your resume',
            'rc_zip.required' => 'Please enter your zip code',
            'rc_zip.max' => 'Zip code should not be more than 10 characters',
            'rc_zip.regex' => 'Please enter a valid zip code',

        ]);

        // Upload files (profile image and resume)
        $imageName = $request->file('profile_image')
            ? CommonUtils::uploadFiles($request, 'profile_image', 'RecruitmentCandidate/ProfileImage', ['prefix' => 'ProfileImage'])
            : null;

        $resumeName = $request->file('rc_resume')
            ? CommonUtils::uploadFiles($request, 'rc_resume', 'RecruitmentCandidate/Resume', ['prefix' => 'Resume'])
            : null;
        // Prepare data to insert into the database
        $application = RecruitmentCandidate::create([
            'rc_name' => $request->rc_name,
            'rc_dg_id' => $request->rc_dg_id[0] ?? null, // Ensure valid data is passed
            'rc_email' => $request->rc_email,
            'rc_mobile' => $request->rc_mobile,
            'rc_gender' => $request->rc_gender[0] ?? null,
            'rc_address' => $request->rc_address,
            'rc_country' => $request->rc_country[0] ?? null,
            'rc_state' => $request->rc_state[0] ?? null,
            'rc_city' => $request->rc_city,
            'rc_zip' => $request->rc_zip,
            'rc_resume' => $resumeName[0],
            'rc_offer_letter_status' => 0,
            'rc_recruitment_id' => $request->rc_recruitment,
            // 'rc_profile_image' => $imageName,
        ]);
        // dd($application);

        return response()->json(['status' => true, 'message' => 'Application submitted successfully']);
    }
}
