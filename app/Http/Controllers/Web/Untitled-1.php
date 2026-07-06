namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmployeeLeave;
use App\Models\Holiday;
use App\Models\Weekoff;
use App\Models\Employee; // Assuming you track leave balances here
use Carbon\Carbon;

class LeaveController extends Controller
{
    public function checkSandwichLeave(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'leave_type' => 'required|string', // E.g., 'Paid Leave', 'Casual Leave', etc.
        ]);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $leaveType = $request->leave_type;

        // Fetch holidays and week-offs
        $holidays = Holiday::pluck('holiday_date')->toArray();
        $weekoffs = Weekoff::pluck('day_name')->toArray();

        // Get all dates in the leave period
        $leaveDates = [];
        for ($date = $startDate; $date <= $endDate; $date->addDay()) {
            $leaveDates[] = $date->copy();
        }

        // Check for holidays and week-offs in the leave period
        $extraDays = [];
        foreach ($leaveDates as $date) {
            if (in_array($date->toDateString(), $holidays) || in_array($date->format('l'), $weekoffs)) {
                $extraDays[] = $date->toDateString();
            }
        }

        // Treat all holidays and week-offs as part of the leave
        $totalLeaveDays = count($leaveDates) + count($extraDays);

        // Fetch employee's leave balance
        $employee = Employee::find($request->employee_id);
        $leaveBalance = $employee->leaveBalances[$leaveType] ?? 0; // Assuming leave balances are tracked in `leaveBalances`

        // Check if the employee has enough leave balance
        if ($leaveBalance >= $totalLeaveDays) {
            // Deduct leave balance
            $employee->leaveBalances[$leaveType] -= $totalLeaveDays;
            $employee->save();

            $status = 'Paid Leave';
        } else {
            $status = 'Unpaid Leave';
        }

        // Store the leave request
        $leave = EmployeeLeave::create([
            'employee_id' => $request->employee_id,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'total_days' => $totalLeaveDays,
            'status' => $status,
        ]);

        return response()->json([
            'warning' => count($extraDays) > 0,
            'message' => count($extraDays) > 0
                ? 'Your leave period includes holidays or week-offs that are treated as part of the leave.'
                : 'Leave request submitted successfully.',
            'data' => [
                'leave' => $leave,
                'extra_days' => $extraDays,
                'total_leave_days' => $totalLeaveDays,
                'status' => $status,
            ],
        ]);
    }
}
//--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
//**************************************************************************************************************************************************************************************************************************

public function sandwichLeave(Request $request) {
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
        $empId = 787;//$request->emp_id;
        $leaveType = 207;//$request->leave_type;
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
                // ->where('lvr_cat_type_id', $leaveType)
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
                if($cnt == 0) {
                    $leaveEndDate = Carbon::parse($leave->lvr_end_date);
                    $cnt++;
                }
                $leaveStartDate = Carbon::parse($leave->lvr_start_date);

                // Check if the gap between leaveEndDate and tempStartDate is filled with holidays/week-offs
                $gapDays = collect();
                for ($date = $leaveEndDate->copy()->addDay(); $date->lessThan($tempStartDate); $date->addDay()) {
                    // echo '$leaveEndDate '. $leaveEndDate. ' tempStartDate '. $tempStartDate;
                    // dd($leaveEndDate->copy()->addDay(), $date->lessThan($tempStartDate), $date->addDay());
                    $gapDays->push($date->toDateString());
                }
                if($cnt == 0) {
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
            });

            // Combine leave dates and extra days
            $finalLeaveDates = $allLeaveDates
                ->merge($extraDays)
                ->unique()
                ->values(); // Ensure no duplicate dates and reset keys

            // Calculate the total number of days
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

            // Fetch employee's leave balance

            // AUTH SE SANDWICH CHECK KARNA HAI AUR SANDWICH SAVE KESE KARNA HAI
            $user = Auth::user();

            $currentLeaveBalance = LeaveBalance::where('lb_emp_id', $empId)
                ->where('lb_cat_type_id', $leaveType)
                ->first(); // Assuming leave balances are tracked in `leaveBalances`

            // Get the leave types that match the user's plan and leave type
            $leaveTypes = LeaveType::where('lvt_pl_id', $user->emp_pl_id)
                ->where('lvt_cat_type_id', $leaveType)
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
                // $currentLeaveBalance->save();
                dd('if :',$currentLeaveBalance->lb_balance_remaining_leave);
            } else {
                // If current leave category balance is insufficient, check the last leave taken
                $lastLeave = LeaveRequest::where('lvr_emp_id', $empId)->orderBy('lvr_end_date', 'desc')->first();
                // dd($lastLeave, $previousLeaves);
                if ($lastLeave) {
                    $lastLeaveCategoryId = $lastLeave->lvr_cat_type_id;

                    // Check balance for the last leave category
                    $lastLeaveBalance = LeaveBalance::where('lb_emp_id', $empId)
                        ->where('lb_cat_type_id', $lastLeaveCategoryId)
                        ->first();

                    $leaveTypes = LeaveType::where('lvt_pl_id', $user->emp_pl_id)
                        ->where('lvt_cat_type_id', $leaveType)
                        ->first();

                    $isSandwich = $leaveTypes && $leaveTypes->lvt_is_sandwich == 1 ? 1 : 0;
                    dd($isSandwich, $lastLeaveCategoryId, $lastLeaveBalance->lb_balance_remaining_leave, $extraDays->count());
                    if ($lastLeaveBalance && $lastLeaveBalance->lb_balance_remaining_leave >= $extraDays->count() && $isSandwich == 1) {
                        // Deduct from last leave category
                        $lastLeaveBalance->lb_balance_remaining_leave -= $extraDays->count();
                        // $lastLeaveBalance->save();
                        dd('else - if :',$currentLeaveBalance->lb_balance_remaining_leave);

                    } else {
                        // Mark extra days as UPL
                        foreach ($extraDays as $extraDay) {
                            // Assuming you have a model for Unpaid Leave
                            // UnpaidLeave::create([
                            //     'employee_id' => $empId,
                            //     'date' => $extraDay,
                            //     'reason' => 'Sandwich leave without sufficient balance',
                            // ]);
                        }
                    }
                }
            }

            // Warn user about sandwich leave
            if ($totalSandwichDays > $endDate->diffInDays($startDate) + 1) {
                return response()->json([
                    'warning' => 'This leave will form a sandwich leave and total ' . $totalSandwichDays . ' days with included extra ' . $extraDays->count() . ' days.',
                    'message' => 'Do you want to proceed with the leave ?',
                ]);
            }



        //END ----------------------------------------------------------------

        // Get all dates in the leave period
        $leaveDates = [];
        for ($date = $startDate; $date <= $endDate; $date->addDay()) {
            $date = $date->copy();
            $leaveDates[] = Carbon::parse($date)->toDateString();//$date->toString();
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
        usort($allHolidaysAndWeekOffs, function($a, $b) {
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
                ->where('lvr_cat_type_id', $leaveType)
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
        $leaveBalance = LeaveBalance::where('lb_emp_id', $empId)->where('lb_cat_type_id', $leaveType)->first(); // Assuming leave balances are tracked in `leaveBalances`
        dd($leaveBalance->lb_balance_remaining_leave, $totalLeaveDays);

        // Check if the employee has enough leave balance
        if ($leaveBalance->lb_balance_remaining_leave >= $totalLeaveDays) {
            // Deduct leave balance
            // $employee->leaveBalances[$leaveType] -= $totalLeaveDays;
            // $employee->save();

            $status = 'Paid Leave';
        } else {
            $status = 'Unpaid Leave';
        }
        dd($status);
    }
