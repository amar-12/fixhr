<?php

namespace App\Http\Controllers\Payroll;

use App\Helpers\CentralLogics;
use App\Helpers\PayrollLogics;
use App\Http\Controllers\Controller;
use App\Models\AdhocTransaction;
use App\Models\AdvanceLoanSetting;
use App\Models\AttendanceException;
use App\Models\AttendanceSummary;
use App\Models\AutomationRule;
use App\Models\Business;
use App\Models\Employee;
use App\Models\FinancialYear;
use App\Models\LeaveRequest;
use App\Models\LoanRequest;
use App\Models\MasterTable;
use App\Models\PayrollLoanInstallment;
use App\Models\PayrollPeriod;
use App\Models\PayslipConfiguration;
use App\Models\PolicyHolidayList;
use App\Models\PolicyWeekOff;
use App\Models\ProcessedEmployeeSalary;
use App\Models\ProcessedSalaryDeduction;
use App\Models\ProcessedSalaryEarning;
use App\Models\SalaryAllowance;
use App\Models\SalaryEmployeeEarnings;
use App\Models\SalaryEmployeeSalary;
use App\Models\SalaryHold;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use NumberToWords\NumberToWords;

class PayrollPeriodController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function payrollPeriodList(Request $request)
    {
        $business_id = auth()->user()->emp_b_id;
        // ✅ Step 1: Check if Payroll Master Settings exist
        $settingsExist = \App\Models\PayrollMasterSetting::where('pms_b_id', $business_id)->exists();
        // dd($settingsExist);

        // ✅ Step 2: If settings missing, just set a flag — don’t redirect
        $showPayrollError = ! $settingsExist;

        $columns = [
            'S. No.',
            'Financial Year',
            'Month Name',
            'Period Name',
            'Attendence Start Date',
            'Attendence End Date',
            'Status',
            'Action',
        ];

        if ($request->ajax()) {
            $query = PayrollPeriod::join('financial_years', 'financial_years.fy_id', '=', 'payroll_periods.pp_fy_id')
                ->join('master_table', 'master_table.m_id', '=', 'payroll_periods.pp_month_id')
                ->where('pp_b_id', $business_id)
                ->orderBy('pp_id', 'asc')
                ->select(
                    'payroll_periods.*',
                    'financial_years.fy_year',
                    'master_table.m_name as month_name'
                )
                ->withCount(['fh_employee'])  // Count employees in the payroll period
                ->withCount([
                    'processedSalaries as salary_processed_count' => function ($query) {
                        $query->select(DB::raw('count(*)'));
                    },
                ]);

            // Apply year filter if provided
            if ($request->has('year') && $request->year !== '') {
                $query->where('payroll_periods.pp_fy_id', $request->year);
            }

            $payrollPeriods = $query->get();

            // Shift the dates by 1 day

            $payrollPeriods->transform(function ($item) {
                $item->pp_start_date = Carbon::parse($item->pp_start_date)->addDay()->format('d-m-Y');
                $item->pp_end_date = Carbon::parse($item->pp_end_date)->addDay()->format('d-m-Y');

                return $item;
            });

            return response()->json(['data' => $payrollPeriods]);
        }

        $title = 'Salary Process';

        $financialYears = FinancialYear::orderBy('fy_id', 'desc')->where('fy_b_id', $business_id)->select('fy_id', 'fy_year', 'fy_is_current')->get();

        // dd($financialYears);
        $monthName = MasterTable::where('m_group', 'MONTH')
            ->select('m_id', 'm_name')
            ->orderBy('m_id', 'asc')
            ->get();

        $payrollQuarter = MasterTable::where('m_group', 'PAYROLL_QUARTER')
            ->select('m_id', 'm_name')
            ->orderBy('m_id', 'asc')
            ->get();

        $currentFinancialYear = FinancialYear::where('fy_b_id', $business_id)->where('fy_is_current', 1)->first();
        $paymentProcess = MasterTable::where('m_group', '=', 'PAYMENT_PROCESS')
            ->select('m_id', 'm_name')
            ->get();

        $paymentCycle = null;
        $payrollSettings = \App\Models\PayrollMasterSetting::where('pms_b_id', $business_id)->first();

        if ($payrollSettings && $payrollSettings->pms_payroll_cycle) {
            $paymentCycle = MasterTable::where('m_group', '=', 'PAYMENT_CYCLE')
                ->where('m_id', $payrollSettings->pms_payroll_cycle)
                ->select('m_id', 'm_name')
                ->first();
        }

        // $paymentCycle = MasterTable::where('m_group', '=', 'PAYMENT_CYCLE')
        //     ->select('m_id', 'm_name')
        //     ->first();

        $payrollPeriods = PayrollPeriod::with(['financialYear', 'month'])
            ->withCount([
                'fh_employee',
                'processedSalaries as salary_processed_count' => function ($query) {
                    $query->select(DB::raw('count(*)'));
                },
            ])
            ->where('pp_b_id', $business_id)
            ->orderBy('pp_id', 'desc')
            ->get();

        $payrollPeriods->transform(function ($item) {

            $item->pp_start_date = Carbon::parse($item->pp_start_date)->addDay()->format('d-m-Y');

            $item->pp_end_date = Carbon::parse($item->pp_end_date)->addDay()->format('d-m-Y');

            return $item;
        });
        // dd($payrollPeriods);

        $business = Business::where('b_id', $business_id)->first();

        return view('admin.payroll.payroll-period', compact(
            'columns',
            'title',
            'financialYears',
            'paymentProcess',
            'paymentCycle',
            'monthName',
            'payrollPeriods',
            'currentFinancialYear',
            'payrollQuarter',
            'business',
            'showPayrollError'
        ));
    }

    public function getPayrollPeriodsData($business_id)
    {
        return PayrollPeriod::with(['financialYear', 'month'])
            ->withCount([
                'fh_employee',
                'processedSalaries as salary_processed_count' => function ($query) {
                    $query->select(DB::raw('count(*)'));
                },
            ])
            ->where('pp_b_id', $business_id)
            ->orderBy('pp_id', 'desc')
            ->get()
            ->transform(function ($item) {
                $item->pp_start_date = \Carbon\Carbon::parse($item->pp_start_date)->addDay()->format('d-m-Y');
                $item->pp_end_date = \Carbon\Carbon::parse($item->pp_end_date)->addDay()->format('d-m-Y');

                return $item;
            });
    }

    public function updateOrCreatepayrollPeriod(Request $request)
    {

        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'payroll_quarter' => 'required|integer',
            'year' => 'required|integer',
            'month' => 'required|integer',
            'payroll_type' => 'required|integer',
            'name' => 'required|string|max:255',
            'sequence' => 'nullable|integer',
            'date_of_payment' => 'nullable|date',
            'payslip_online_date' => 'nullable|date',
            'description' => 'nullable|string',
            'attendance_start_date' => 'required|date',
            'attendance_end_date' => 'required|date|after_or_equal:attendance_start_date',

            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:30',
            'ifsc_code' => 'nullable|string|max:20',
            'bank_address' => 'nullable|string|max:255',
            'cheque_number' => 'nullable|string|max:30',

        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $businessId = auth()->user()->emp_b_id;
        $year = $request->year;
        $month = $request->month;
        $startDate = $request->attendance_start_date;
        $endDate = $request->attendance_end_date;

        // Skip duplicate check if editing the same record
        $query = PayrollPeriod::where('pp_fy_id', $year)
            ->where('pp_month_id', $month)
            ->where('pp_b_id', $businessId)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('pp_start_date', [$startDate, $endDate])
                        ->orWhereBetween('pp_end_date', [$startDate, $endDate]);
                })
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('pp_start_date', '<=', $startDate)
                            ->where('pp_end_date', '>=', $endDate);
                    });
            });

        // If editing, exclude current ID
        if ($request->filled('pp_id')) {
            $query->where('pp_id', '!=', $request->pp_id);
        }

        if ($query->exists()) {
            return redirect()->back()->withErrors(['error' => 'A payroll period already exists for this month and date range.']);
        }

        // If ID exists, update. Otherwise, create new.
        if ($request->filled('pp_id')) {
            $payrollPeriod = PayrollPeriod::find($request->pp_id);
        } else {
            $payrollPeriod = new PayrollPeriod;
            $payrollPeriod->pp_b_id = $businessId;
            $payrollPeriod->pp_fy_id = $year;
            $payrollPeriod->pp_month_id = $month;
        }

        $payrollPeriod->pp_type_id = $request->payroll_type;
        $payrollPeriod->pp_name = $request->name;
        $payrollPeriod->pp_seq_no = $request->sequence;
        $payrollPeriod->pp_start_date = $startDate;
        $payrollPeriod->pp_end_date = $endDate;
        $payrollPeriod->pp_description = $request->description;
        $payrollPeriod->pp_payment_date = $request->date_of_payment;
        $payrollPeriod->pp_payslip_date = $request->payslip_online_date;
        $payrollPeriod->pp_is_active = $request->is_active ?? 1;
        $payrollPeriod->pp_quarter_id = $request->payroll_quarter;

        $payrollPeriod->save();

        // ✅ Update business table with bank details
        Business::where('b_id', $businessId)->update([
            'b_bank_name' => $request->bank_name,
            'b_bank_acc_no' => $request->account_number,
            'b_bank_ifsc' => $request->ifsc_code,
            'b_bank_address' => $request->bank_address,
            'b_cheque_no' => $request->cheque_number,
        ]);

        return redirect()->back()->with('success', 'Payroll period and bank details saved successfully.');
    }

    public function deletePayrollPeriod($id)
    {
        // dd($id);
        $payrollPeriod = PayrollPeriod::find($id);

        if (! $payrollPeriod) {
            return response()->json(['message' => 'Payroll period not found!'], 404);
        }

        // Check if attendance is already frozen
        if ($payrollPeriod->pp_is_freezed == 120) {
            return response()->json(['message' => 'Cannot delete. Attendance is already frozen!'], 400);
        }

        $payrollPeriod->delete();

        return response()->json(['message' => 'Payroll period deleted successfully!']);
    }

    public function getSundayAttendanceSummary($emp, array $attendanceData, int $month, int $year)
    {
        // Step 1: get all Sundays of the month
        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth();
        $sundays = [];
        foreach (CarbonPeriod::create($startOfMonth, $endOfMonth) as $date) {
            if ($date->isSunday()) {
                $sundays[] = $date->toDateString();
            }
        }

        // Step 2: fetch shift details from employee
        $shift = $emp->fh_shift_type;
        $shiftStartTime = Carbon::parse($shift->pst_start_time);
        $shiftEndTime = Carbon::parse($shift->pst_end_time);
        $graceMins = $shift->pst_allow_grace_time ? $shift->pst_grace_time : 0;
        $shiftStartWithGrace = $shiftStartTime->copy()->subMinutes($graceMins);

        $dailyWorkingMinutes = $shiftStartWithGrace->diffInMinutes($shiftEndTime);
        $minWorkHrs = $shift->pst_min_work_hour
            ? $shiftStartTime->diffInMinutes(Carbon::parse($shift->pst_min_work_hour))
            : $dailyWorkingMinutes;
        $halfDayThreshold = $dailyWorkingMinutes / 2;

        // Summary counters
        $weekOffFullDayPresentCount = 0;
        $weekOffHalfDayPresentCount = 0;
        $weekOffDates = [];

        // Step 3: iterate Sundays
        foreach ($sundays as $sunday) {
            $record = collect($attendanceData)->firstWhere('date', $sunday);

            if ($record && ! empty($record['checkInTime']) && ! empty($record['checkOutTime'])) {

                if (
                    empty($record['checkInTime']) || $record['checkInTime'] == '-' ||
                    empty($record['checkOutTime']) || $record['checkOutTime'] == '-'
                ) {
                    $statusId = 203; // Absent
                } else {
                    $checkInTime = Carbon::parse($sunday.' '.$record['checkInTime']);
                    $checkOutTime = Carbon::parse($sunday.' '.$record['checkOutTime']);
                    $workedMinutes = $checkInTime->diffInMinutes($checkOutTime);

                    if ($workedMinutes >= $dailyWorkingMinutes || $workedMinutes >= $minWorkHrs) {
                        $statusId = 251; // Full day present
                        $weekOffFullDayPresentCount++;
                    } elseif ($workedMinutes >= $halfDayThreshold) {
                        $statusId = 252; // Half day
                        $weekOffHalfDayPresentCount++;
                    } else {
                        $statusId = 203; // Absent
                    }
                }
            } else {
                $statusId = 203; // Absent
            }

            $weekOffDates[] = [
                'date' => $sunday,
                'status_id' => $statusId,
            ];
        }

        return [
            'weekOffFullDayPresentCount' => $weekOffFullDayPresentCount,
            'weekOffHalfDayPresentCount' => $weekOffHalfDayPresentCount,
            'weekOffDates' => $weekOffDates,
        ];
    }

    public static function getWeekOffAttendanceSummary($emp, array $attendanceData, int $month, int $year)
    {
        $policy = $emp->fh_week_off_policy2;

        if (! $policy) {
            return ['weekOffSummary' => []];
        }

        // Decode fields safely
        $dayIds = json_decode($policy->pwo_day_ids, true) ?: [];
        $recurrenceDayIds = json_decode($policy->pwo_recurrence_day_ids, true) ?: [];
        $unpaidIds = json_decode($policy->pwo_is_unpaid, true) ?: [];

        // Map all master IDs to names
        $allIds = array_unique(array_merge($dayIds, array_keys($recurrenceDayIds), ...array_values($recurrenceDayIds)));
        $masterNames = \App\Models\MasterTable::whereIn('m_id', $allIds)->pluck('m_name', 'm_id')->toArray();

        // Prepare shift details
        $shift = $emp->fh_shift_type;
        $shiftStartTime = Carbon::parse($shift->pst_start_time);
        $shiftEndTime = Carbon::parse($shift->pst_end_time);
        $graceMins = $shift->pst_allow_grace_time ? $shift->pst_grace_time : 0;
        $shiftStartWithGrace = $shiftStartTime->copy()->subMinutes($graceMins);
        $dailyWorkingMinutes = $shiftStartWithGrace->diffInMinutes($shiftEndTime);
        $minWorkHrs = $shift->pst_min_work_hour
            ? $shiftStartTime->diffInMinutes(Carbon::parse($shift->pst_min_work_hour))
            : $dailyWorkingMinutes;
        $halfDayThreshold = $dailyWorkingMinutes / 2;

        // Date range for the month
        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $summary = [];

        // Loop through each configured week-off day
        foreach ($recurrenceDayIds as $dayId => $weekIds) {
            $dayName = $masterNames[$dayId] ?? null;
            if (! $dayName) {
                continue;
            }

            $isUnpaid = in_array($dayId, $unpaidIds);

            $fullDayPresent = 0;
            $halfDayPresent = 0;
            $unpaidWeekOffCount = 0;
            $dates = [];

            // Loop all weeks for that day
            foreach ($weekIds as $weekId) {
                $weekName = $masterNames[$weekId] ?? null;
                if (! $weekName) {
                    continue;
                }

                // Derive week number from name, e.g. "1st Week" → 1
                $weekNum = (int) filter_var($weekName, FILTER_SANITIZE_NUMBER_INT);
                if ($weekNum <= 0) {
                    continue;
                }

                // Get all days of month for this weekday
                $date = $startOfMonth->copy()->startOfMonth()->next($dayName);
                while ($date->month == $month) {
                    $weekOfMonth = ceil($date->day / 7);
                    if ($weekOfMonth == $weekNum) {
                        $unpaidWeekOffCount = $isUnpaid ? $unpaidWeekOffCount + 1 : $unpaidWeekOffCount + 0;
                        $currentDate = $date->toDateString();
                        $record = collect($attendanceData)->firstWhere('date', $currentDate);

                        if (
                            $record && ! empty($record['checkInTime']) && ! empty($record['checkOutTime'])
                            && $record['checkInTime'] != '-' && $record['checkOutTime'] != '-'
                        ) {

                            $checkIn = Carbon::parse($currentDate.' '.$record['checkInTime']);
                            $checkOut = Carbon::parse($currentDate.' '.$record['checkOutTime']);
                            $workedMinutes = $checkIn->diffInMinutes($checkOut);

                            if ($workedMinutes >= $dailyWorkingMinutes || $workedMinutes >= $minWorkHrs) {
                                $statusId = 251; // Full day present
                                $fullDayPresent++;
                            } elseif ($workedMinutes >= $halfDayThreshold) {
                                $statusId = 252; // Half day present
                                $halfDayPresent++;
                            } else {
                                $statusId = 203; // Absent
                            }
                        } else {
                            $statusId = 203;
                        }

                        $dates[] = [
                            'date' => $currentDate,
                            'status_id' => $statusId,
                        ];
                    }
                    $date->addWeek();
                }
            }

            $summary[] = [
                'day_name' => $dayName,
                'is_unpaid' => $isUnpaid,
                'full_day_present_count' => $fullDayPresent,
                'half_day_present_count' => $halfDayPresent,
                'dates' => $dates,
                'unpaidWeekOffCount' => $unpaidWeekOffCount,
            ];
        }

        return ['weekOffSummary' => $summary];
    }

    public function retrieveAttendance(Request $request)
    {
        $user = Auth::user();
        $title = 'Attendance-Add/Edit';

        if (! $request->has('payroll_id')) {
            return redirect()->back()->with('error', 'Payroll ID is required!');
        }

        $payrollId = $request->payroll_id;
        $payroll = PayrollPeriod::find($payrollId);

        if (! $payroll) {
            return redirect()->back()->with('error', 'Payroll Period not found!');
        }

        $payrollStartDate = Carbon::parse($payroll->pp_start_date);
        $payrollEndDate = Carbon::parse($payroll->pp_end_date);

        if ($payrollEndDate->lt($payrollStartDate)) {
            return redirect()->back()->with('error', 'End date cannot be before start date!');
        }

        $month = $payrollStartDate->format('m');
        $year = $payrollStartDate->format('Y');

        $employees = Employee::where('emp_b_id', $payroll->pp_b_id)
            ->where('emp_role_id', '!=', 1)
            ->whereIn('emp_status', [71, 72, 457, 458, 459, 460]) // agar multiple status allowed hain
            ->whereDate('emp_date_of_joining', '<=', $payrollEndDate)
            ->where(function ($q) use ($payrollStartDate) {
                $q->whereNull('emp_last_working_date')
                    ->orWhereDate('emp_last_working_date', '>=', $payrollStartDate);
            })
            ->get();

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // $holidayRecords = PolicyHolidayList::where('phl_b_id', $user->emp_b_id)
        //     ->where(function ($q) use ($startDate, $endDate) {
        //         $q->whereBetween('phl_start_date', [$startDate, $endDate])
        //             ->orWhereBetween('phl_end_date', [$startDate, $endDate]);
        //     })->get();

        //     $holidaysByDate = collect();
        //     foreach ($holidayRecords as $holiday) {
        //         $holidayStart = Carbon::parse($holiday->phl_start_date);
        //         $holidayEnd = Carbon::parse($holiday->phl_end_date);

        //         while ($holidayStart->lte($holidayEnd)) {
        //             $holidaysByDate->put($holidayStart->toDateString(), $holiday);
        //             $holidayStart->addDay();
        //         }
        //     }

        // $holidayRecords = PolicyHolidayList::where('phl_b_id', $user->emp_b_id)->where('phl_day_type_id', 201)->where(function ($q) use ($startDate, $endDate) {
        //     $q->whereBetween('phl_start_date', [$startDate, $endDate])
        //         ->orWhereBetween('phl_end_date', [$startDate, $endDate]);
        // })->get();

        $holidayRecords = PolicyHolidayList::where('phl_b_id', $user->emp_b_id)->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('phl_start_date', [$startDate, $endDate])
                ->orWhereBetween('phl_end_date', [$startDate, $endDate]);
        })->get();

        $holidaysByDate = collect();
        foreach ($holidayRecords as $holiday) {
            $start = Carbon::parse($holiday->phl_start_date);
            $end = Carbon::parse($holiday->phl_end_date);

            while ($start->lte($end)) {
                $holidaysByDate->put($start->toDateString(), $holiday);
                $start->addDay();
            }
        }

        $attendanceDetails = [];

        foreach ($employees as $employee) {
            $weekOffDates = CentralLogics::getWeekOffDatesReport($employee, null, null, $payrollStartDate, $payrollEndDate);
            $weekoffPaidUnpaid = PolicyWeekOff::where('pwo_b_id', $employee->emp_b_id)->first();
            $isUnpaid = $weekoffPaidUnpaid->pwo_is_unpaid ?? 0;
            $attendanceSummary = CentralLogics::newGetMonthlyAttendanceDetails(
                $employee,
                $month,
                $year,
                $holidaysByDate,
                $weekOffDates
            );

            $weekOffSummary = self::getWeekOffAttendanceSummary($employee, $attendanceSummary, $month, $year);
            $totalUnpaidWeekOffCount = array_sum(array_column($weekOffSummary['weekOffSummary'], 'unpaidWeekOffCount'));
            $weekOffFullDayPresentCount = array_sum(array_column($weekOffSummary['weekOffSummary'], 'full_day_present_count'));
            $weekOffHalfDayPresentCount = array_sum(array_column($weekOffSummary['weekOffSummary'], 'half_day_present_count'));
            $weekOffPresent = $weekOffFullDayPresentCount + ($weekOffHalfDayPresentCount * 0.5);

            $presentCount = collect($attendanceSummary)->sum('presentCount');
            $halfDayCount = collect($attendanceSummary)->sum('halfDayCount');
            $leaveCount = collect($attendanceSummary)->sum('leaveCount');
            $absentCount = collect($attendanceSummary)->sum('absentCount');
            $holidayCount = collect($attendanceSummary)->sum('holidayCount');
            $missedPunchCount = collect($attendanceSummary)->sum('missedPunchCount');
            $lateCount = collect($attendanceSummary)->sum('lateCount');
            $overtimeCount = collect($attendanceSummary)->sum('overtimeCount');
            $UPL = collect($attendanceSummary)->sum('UPL');
            $earlyExitCount = collect($attendanceSummary)->sum('earlyExitCount');

            $weekOffCount = collect($attendanceSummary)->sum('weekOffCount');
            $overtimeHours = collect($attendanceSummary)->sum('OT');

            $totalDays = $payrollStartDate->diffInDays($payrollEndDate) + 1;
            $totalDays -= $totalUnpaidWeekOffCount;

            $total = $presentCount + $weekOffCount + $holidayCount + $leaveCount;

            // $employee->emp_id == 1930 ? dd($presentCount, $totalDays, $absentCount, $weekOffCount, $total) : null;

            $attendanceDetails[] = [
                'employee' => $employee,
                'payroll' => $payroll,
                'attendance_summary' => [
                    'total_days' => $totalDays,
                    'presentCount' => $presentCount,
                    'halfDayCount' => $halfDayCount,
                    'leaveCount' => $leaveCount,
                    'absentCount' => $absentCount,
                    'weekOffCount' => $weekOffCount,
                    'weekOffPresentCount' => $weekOffPresent,
                    'holidayCount' => $holidayCount,
                    'missedPunchCount' => $missedPunchCount,
                    'lateCount' => $lateCount,
                    'earlyExitCount' => $earlyExitCount,
                    'overtimeCount' => $overtimeCount,
                    'total' => $total,
                    'UPL' => $UPL,
                    'overtimeHours' => $overtimeHours,
                ],
                'payroll_id' => $payrollId,
            ];
        }

        return view('admin.payroll.freeze-attendance-add-edit', compact('attendanceDetails', 'title'));
    }

    /**
     * Get all week-off dates within the payroll period
     */
    private function getPayrollWeekOffDates($employee, $payrollStartDate, $payrollEndDate)
    {
        $weekOffDates = [];

        for ($date = $payrollStartDate->copy(); $date->lte($payrollEndDate); $date->addDay()) {
            if ($date->isSunday()) { // Assuming Sunday as week off
                $weekOffDates[] = $date->format('Y-m-d');
            }
        }

        return $weekOffDates;
    }

    public function freezeAttendance(Request $request)
    {
        $attendanceData = json_decode($request->all_attendance_data, true);
        $pendingEmployees = [];
        $pendingMissPunchEmployees = [];
        $errorFlag = false;
        $errorMessage = '';

        collect($attendanceData)->chunk(50)->each(function ($chunk) use (
            &$pendingEmployees,
            &$pendingMissPunchEmployees,
            &$errorFlag,
            &$errorMessage
        ) {

            foreach ($chunk as $emp_id => $data) {

                if (! array_key_exists('UPL', $data)) {
                    dd("UPL key missing for employee ID: $emp_id", $data);
                }

                $payrollId = $data['payroll_id'];
                $payrollPeriod = PayrollPeriod::find($payrollId);

                if (! $payrollPeriod) {
                    continue; // Skip if payroll period is not found
                }

                $startDate = $payrollPeriod->pp_start_date;
                $endDate = $payrollPeriod->pp_end_date;

                // Check for pending leave requests
                // $leaveRequestPending = LeaveRequest::where('lvr_b_id', $data['business_id'])
                //     ->where('lvr_stage_completed', '=', 0)
                //     ->whereNull('lvr_p_id')
                //     ->whereNotIn('lvr_status', [170])
                //     ->exists();

                $leaveRequestPending = LeaveRequest::where('lvr_b_id', $data['business_id'])
                    ->where('lvr_stage_completed', 0)
                    ->whereNull('lvr_p_id')
                    ->whereNotIn('lvr_status', [170])
                    ->where(function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('lvr_start_date', [$startDate, $endDate])
                            ->orWhereBetween('lvr_end_date', [$startDate, $endDate])
                            ->orWhere(function ($subQuery) use ($startDate, $endDate) {
                                $subQuery->where('lvr_start_date', '<=', $startDate)
                                    ->where('lvr_end_date', '>=', $endDate);
                            });
                    })
                    ->exists();

                if ($leaveRequestPending) {
                    $pendingEmployees[] = $data['emp_id'];
                }

                // Check for pending missed punch requests
                // $missPunchPending = AttendanceException::where('ae_b_id', $data['business_id'])
                // ->where('ae_stage_completed','=', 0)
                // ->whereNotIn('ae_status', [139, 192, 156, 170])
                // ->whereNotNull('ae_am_id')
                // ->exists();

                $missPunchPending = AttendanceException::where('ae_b_id', $data['business_id'])
                    ->where('ae_stage_completed', 0)
                    ->whereNotIn('ae_status', [139, 192, 156, 170])
                    ->whereNotNull('ae_am_id')
                    ->where(function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('ae_date', [$startDate, $endDate])
                            ->orWhere(function ($subQuery) use ($startDate, $endDate) {
                                $subQuery->where('ae_date', '<=', $endDate)
                                    ->where('ae_date', '>=', $startDate);
                            });
                    })
                    ->exists();

                if ($missPunchPending) {
                    $pendingMissPunchEmployees[] = $data['emp_id'];
                }

                // If there are pending leave requests or missed punches, do not freeze attendance and show error
                if ($leaveRequestPending || $missPunchPending) {
                    $errorFlag = true;
                    $errorMessage = 'There are pending leave or missed punch requests.';

                    if (! empty($pendingEmployees)) {
                        // $errorMessage .= ' Leave Requests: ' . implode(', ', $pendingEmployees);
                        $errorMessage .= 'First Clear Leave Requests.';
                    }

                    if (! empty($pendingMissPunchEmployees)) {
                        $errorMessage .= 'First Clear Miss Punch Requests.';
                    }
                    break; // Exit the loop if any pending requests are found
                }

                // Proceed with creating attendance summary only if there are no pending requests
                $existing = AttendanceSummary::where('as_emp_id', $data['emp_id'])
                    ->where('as_pp_id', $data['payroll_id'])
                    ->first();

                if (! $existing) {
                    AttendanceSummary::create([
                        'as_emp_id' => $data['emp_id'],
                        'as_year_month' => date('Y-m'),
                        'as_br_id' => $data['branch_id'],
                        'as_d_id' => $data['dept_id'],
                        'as_b_id' => $data['business_id'],
                        'as_pp_id' => $data['payroll_id'],
                        'as_total_days' => (float) $data['total_days'],
                        'as_total_present' => (float) $data['presentCount'],
                        'as_total_half_day' => (float) $data['halfDayCount'],
                        'as_total_absent' => (float) $data['absentCount'],
                        'as_total_leave' => (float) $data['leaveCount'],
                        'as_total_weekoff' => (float) $data['weekOffCount'],
                        'as_total_weekoffPresent' => (float) $data['weekOffPresentCount'],
                        'as_total_holiday' => (float) $data['holidayCount'],
                        'as_total_missed_punch' => (float) $data['missedPunchCount'],
                        'as_days_late' => (float) $data['lateCount'],
                        'as_early_exit' => (float) $data['earlyExitCount'],
                        'as_total_overtime_hours' => (float) $data['overtimeHours'],
                        'as_total_upl_count' => (float) ($data['UPL'] ?? 0),
                        // 'as_total_worked_days' => (float) ($data['presentCount'] + $data['holidayCount'] + $data['weekOffCount']),
                        'as_total_worked_days' => (float) ($data['total'] ?? 0),

                    ]);

                    PayrollPeriod::where('pp_id', $data['payroll_id'])
                        ->update(['pp_is_freezed' => 120]);
                }
            }
        });

        if ($errorFlag) {
            return redirect()->route('payroll.period.list')->with('error', $errorMessage);
        }

        $flashMessage = 'Attendance saved successfully!';

        return redirect()->route('payroll.period.list')->with('success', $flashMessage);
    }

    public function unfreezeAttendance(Request $request, $id)
    {
        // dd($request->all());
        $business_id = auth()->user()->emp_b_id;

        $count = AttendanceSummary::where('as_pp_id', $id)
            ->where('as_b_id', $business_id)
            ->where(function ($query) {
                $query->where('as_is_sal_processed', 121)
                    ->orWhereNull('as_is_sal_processed');
            })->delete();

        // Update payroll period to mark as unprocessed
        PayrollPeriod::where('pp_id', $id)
            ->where('pp_b_id', $business_id)
            ->update(['pp_is_freezed' => 121]);

        return redirect()->back()->with('unfreeze_success', "$count attendance records successfully unfrozen.");
    }

    public function getProcessSalary(Request $request, $id)
    {
        $business_id = auth()->user()->emp_b_id;
        // dd($business_id);

        $payroll = PayrollPeriod::where('pp_id', $id)->where('pp_b_id', $business_id)
            ->first();

        if (! $payroll) {
            return redirect()->back()->withErrors('Payroll period not found.');
        }

        // Step 2: Get eligible employee IDs
        $salaryEmployeeIds = DB::table('employee_salaries')
            ->where('es_b_id', $business_id)
            ->pluck('es_emp_id')
            ->toArray();

        $heldEmployeeIds = SalaryHold::where('sh_b_id', $business_id)
            ->where('sh_pp_id', $payroll->pp_id)
            ->where('sh_status', 'held')
            ->pluck('sh_emp_id')
            ->toArray();

        // Step 3: Constants
        $EMPLOYEE_ACTIVE_STATUS = 71;
        // $SALARY_NOT_PROCESSED = 121;

        if ($request->ajax()) {
            try {
                // Get status filter from request (dropdown)
                $statusFilter = $request->input('mt_statusFilter'); // e.g. 71 for Active

                // Dynamic conditions for DataTable
                $dynamicConditions = [
                    [
                        'method' => 'whereIn',
                        'args' => ['as_emp_id', $salaryEmployeeIds],
                    ],
                    [
                        'method' => 'where',
                        'args' => ['as_pp_id', $id],
                    ],
                    [
                        'method' => 'where',
                        'args' => ['as_is_sal_processed', 121],
                    ],
                    [
                        'method' => 'whereNotIn',
                        'args' => ['as_emp_id', $heldEmployeeIds],
                    ],
                ];

                // Add status filter if selected
                if (! empty($statusFilter)) {
                    $dynamicConditions[] = [
                        'method' => 'whereRelation',
                        'parentMethod' => 'whereHas',
                        'childMethod' => 'where',
                        'args' => ['emp_status', $statusFilter],
                        'relation' => 'fh_employee',
                    ];
                }

                // Columns and relationships for searching/filtering
                $searchColumns = [
                    'as_total_days',
                    'as_total_present',
                ];
                $searchRelationships = [
                    'fh_employee' => ['emp_code', 'emp_full_name'],
                    'fh_employee.fh_department' => ['d_name'],
                    'fh_employee.fh_designation' => ['dg_name'],
                    'fh_employee.fh_employee_status' => ['m_name'],
                ];

                // Use DataTable helper for server-side processing
                $dataTableHelper = new \ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper(
                    eloquentModel: new \App\Models\AttendanceSummary,
                    dynamicConditions: $dynamicConditions,
                    searchColumns: $searchColumns,
                    searchRelationships: $searchRelationships
                );

                $list = $dataTableHelper->getServerSideDataTable();

                // Build row data for DataTable
                $rowData = [];
                $i = 1;
                foreach ($list as $val) {
                    $employee = $val->fh_employee;
                    $rowData[] = [
                        $i++,
                        '<input type="checkbox" name="selected_employees[]" value="'.$employee->emp_id.'" class="select-employee">',
                        $employee->emp_code ?? '-',
                        $employee->emp_full_name ?? '-',
                        optional($employee->fh_department)->d_name ?? '-',
                        optional($employee->fh_designation)->dg_name ?? '-',
                        $val->as_total_days,
                        $val->as_total_worked_days,
                        optional($employee->fh_employee_status)->m_name ?? '-',
                    ];
                }

                // DataTable output
                $output = [
                    'draw' => intval($request->input('draw')),
                    'recordsTotal' => count($list),
                    'recordsFiltered' => $dataTableHelper->countFilteredServerSideDataTable(),
                    'data' => $rowData,
                ];

                return response()->json($output);
            } catch (Exception $e) {
                return response()->json($e->getMessage(), 500);
            }
        }

        // $processableStatuses = [71, 72, 460, 457, 458, 459];

        // // Step 4: Get attendance summary for eligible employees
        // $attendanceSummary = AttendanceSummary::join('employees', 'employees.emp_id', '=', 'attendance_summaries.as_emp_id')
        //     ->join('payroll_periods', 'payroll_periods.pp_id', '=', 'attendance_summaries.as_pp_id')
        //     ->join('master_table', 'master_table.m_id', '=', 'employees.emp_status')
        //     ->join('designations', 'designations.dg_id', '=', 'employees.emp_dg_id')
        //     ->join('departments', 'departments.d_id', '=', 'employees.emp_d_id')
        //     ->where('attendance_summaries.as_pp_id', $id)
        //     ->where(function ($query) use ($SALARY_NOT_PROCESSED) {
        //         $query->where('attendance_summaries.as_is_sal_processed', $SALARY_NOT_PROCESSED)
        //             ->orWhereNull('attendance_summaries.as_is_sal_processed');
        //     })
        //     ->whereIn('employees.emp_id', $salaryEmployeeIds)
        //     ->where('employees.emp_status', $EMPLOYEE_ACTIVE_STATUS)
        //     ->where(function ($query) {
        //         $query->whereColumn('employees.emp_date_of_joining', '<=', 'payroll_periods.pp_end_date')
        //             ->where(function ($q) {
        //                 $q->whereNull('employees.emp_last_working_date')
        //                     ->orWhereColumn('employees.emp_last_working_date', '>=', 'payroll_periods.pp_start_date');
        //             });
        //     })
        //     ->select(
        //         'payroll_periods.*',
        //         'attendance_summaries.*',
        //         'employees.emp_id',
        //         'employees.emp_code',
        //         'employees.emp_full_name',
        //         'employees.emp_br_id',
        //         'employees.emp_d_id',
        //         'master_table.m_name as status_name',
        //         'designations.dg_name as designation_name',
        //         'departments.d_name as department_name'
        //     )
        //     ->get();
        /* ───── constants ──── */
        $SALARY_NOT_PROCESSED = 121;
        $processableStatuses = [71, 72, 457, 458, 459, 460];   // Active, Inactive, Absconding, Resigned, Terminated, On‑Leave

        /* ───── attendance query ──── */
        $attendanceSummary = AttendanceSummary::join('employees', 'employees.emp_id', '=', 'attendance_summaries.as_emp_id')
            ->join('payroll_periods', 'payroll_periods.pp_id', '=', 'attendance_summaries.as_pp_id')
            ->join('master_table', 'master_table.m_id', '=', 'employees.emp_status')
            ->join('designations', 'designations.dg_id', '=', 'employees.emp_dg_id')
            ->join('departments', 'departments.d_id', '=', 'employees.emp_d_id')
            ->where('attendance_summaries.as_pp_id', $id)
            ->whereIn('employees.emp_id', $salaryEmployeeIds)
            /* 🔻  **was** ->where('employees.emp_status', $EMPLOYEE_ACTIVE_STATUS)
            🔺  **now** include every status you want to show as an inner tab   */
            ->whereIn('employees.emp_status', $processableStatuses)
            ->where(function ($q) use ($SALARY_NOT_PROCESSED) {
                $q->where('attendance_summaries.as_is_sal_processed', $SALARY_NOT_PROCESSED)
                    ->orWhereNull('attendance_summaries.as_is_sal_processed');
            })
            ->whereColumn('employees.emp_date_of_joining', '<=', 'payroll_periods.pp_end_date')
            ->where(function ($q) {
                $q->whereNull('employees.emp_last_working_date')
                    ->orWhereColumn('employees.emp_last_working_date', '>=', 'payroll_periods.pp_start_date');
            })
            ->select(
                'payroll_periods.*',
                'attendance_summaries.*',
                'employees.emp_id',
                'employees.emp_code',
                'employees.emp_full_name',
                'employees.emp_br_id',
                'employees.emp_d_id',
                'master_table.m_name as status_name',
                'designations.dg_name as designation_name',
                'departments.d_name as department_name'
            )
            ->get();
        // dd($attendanceSummary);

        $payrollStart = $payroll->pp_start_date;
        // dd($payrollStart);
        $payrollEnd = $payroll->pp_end_date;

        // Step 5: Get employee stats
        // $totalEmployeeCount = Employee::where('emp_b_id', $business_id)->count();
        $totalEmployeeCount = Employee::where('emp_b_id', $business_id)
            ->whereDate('emp_date_of_joining', '<=', $payrollEnd)
            ->where(function ($q) use ($payrollStart) {
                $q->whereNull('emp_last_working_date')
                    ->orWhere('emp_last_working_date', '>=', $payrollStart);
            })
            ->count();

        $totalActiveEmployeeCount = Employee::where('emp_b_id', $business_id)
            ->whereDate('emp_date_of_joining', '<=', $payrollEnd)
            ->where(function ($q) use ($payrollStart) {
                $q->whereNull('emp_last_working_date')
                    ->orWhere('emp_last_working_date', '>=', $payrollStart);
            })->where('emp_status', 71)
            ->count();

        $totalInActiveEmployeeCount = Employee::where('emp_b_id', $business_id)
            ->whereDate('emp_date_of_joining', '<=', $payrollEnd)
            ->where(function ($q) use ($payrollStart) {
                $q->whereNull('emp_last_working_date')
                    ->orWhere('emp_last_working_date', '>=', $payrollStart);
            })->where('emp_status', 72)
            ->count();
        $totalProcessedCount = ProcessedEmployeeSalary::where('ps_b_id', $business_id)
            ->where('ps_payroll_id', $payroll->pp_id)
            ->distinct('ps_emp_id')
            ->count('ps_emp_id');

        $salaryToProcessCount = $totalEmployeeCount - $totalProcessedCount;

        $processedEmployees = ProcessedEmployeeSalary::with([
            'employee',
            'employee.fh_department',
            'employee.fh_designation',
            'employee.fh_employee_status',
        ])
            ->where('ps_payroll_id', $payroll->pp_id)
            ->where('ps_b_id', $business_id)          // ← business filter
            ->get();

        // $employeeStatusCounts = Employee::join('master_table', 'master_table.m_id', '=', 'employees.emp_status')
        //     ->where('employees.emp_b_id', $business_id)
        //     ->groupBy('employees.emp_status', 'master_table.m_name')
        //     ->select(
        //         'employees.emp_status as status_id',
        //         'master_table.m_name  as status_name',
        //         DB::raw('COUNT(*)      as total')
        //     )
        //     ->orderBy('status_name')   // optional, for neat display
        //     ->get();

        // 3. Employees by status (only active in period)
        $employeeStatusCounts = Employee::join('master_table', 'master_table.m_id', '=', 'employees.emp_status')
            ->where('employees.emp_b_id', $business_id)
            ->whereDate('employees.emp_date_of_joining', '<=', $payrollEnd)
            ->where(function ($q) use ($payrollStart) {
                $q->whereNull('employees.emp_last_working_date')
                    ->orWhere('employees.emp_last_working_date', '>=', $payrollStart);
            })
            ->groupBy('employees.emp_status', 'master_table.m_name')
            ->select(
                'employees.emp_status as status_id',
                'master_table.m_name  as status_name',
                DB::raw('COUNT(*)      as total')
            )
            ->orderBy('status_name')
            ->get();
        // dd($employeeStatusCounts);

        // IDs of employees that already have a salary structure
        $salaryEmployeeIds = DB::table('employee_salaries')
            ->where('es_b_id', $business_id)
            ->pluck('es_emp_id')
            ->toArray();

        // ⬇️ NEW  ➜  employees whose salary row is still missing
        // $salaryNotConfiguredCount = Employee::where('emp_b_id', $business_id)
        //     ->whereNotIn('emp_id', $salaryEmployeeIds)
        //     ->count();

        $salaryNotConfiguredEmployees = Employee::where('emp_b_id', $business_id)
            ->whereNotIn('emp_id', $salaryEmployeeIds)
            ->whereDate('emp_date_of_joining', '<=', $payroll->pp_end_date)
            ->where(function ($q) use ($payroll) {
                $q->whereNull('emp_last_working_date')
                    ->orWhere('emp_last_working_date', '>=', $payroll->pp_start_date);
            })->select('emp_id', 'emp_full_name')->orderBy('emp_full_name')->get();

        $salaryNotConfiguredCount = $salaryNotConfiguredEmployees->count();

        $remainingCount = $totalActiveEmployeeCount + $totalInActiveEmployeeCount - $salaryNotConfiguredCount;
        $attendanceByStatus = $attendanceSummary->groupBy('status_name');
        $heldEmployees = SalaryHold::with([
            'employee.fh_department',
            'employee.fh_designation',
        ])
            ->where('sh_b_id', $business_id)
            ->where('sh_pp_id', $payroll->pp_id)
            ->where('sh_status', 'held')
            ->get();

        $heldCount = $heldEmployees->count();

        $heldEmployees = SalaryHold::with('employee.fh_department', 'employee.fh_designation', 'employee.fh_employee_status')
            ->where('sh_pp_id', $payroll->pp_id)
            ->get();

        // Step 6: Return view
        return view('admin.payroll.process_salary', compact(
            'attendanceSummary',
            'salaryNotConfiguredEmployees',
            'payroll',
            'totalProcessedCount',
            'totalEmployeeCount',
            'salaryToProcessCount',
            'remainingCount',
            'processedEmployees',
            'employeeStatusCounts',
            'salaryNotConfiguredCount',
            'attendanceByStatus',
            'heldEmployees',      // ✅ UI ke liye list
            'heldCount'
        ));
    }

    public function unprocessSalaries(Request $request)
    {
        $employeeIds = $request->selected_employees;
        $payrollId = $request->input('pp_id');

        if (empty($employeeIds) || ! $payrollId) {
            return redirect()->back()->withErrors('No employees selected or invalid payroll period.');
        }

        DB::transaction(function () use ($employeeIds, $payrollId) {
            ProcessedEmployeeSalary::with('earnings', 'deductions')
                ->where('ps_payroll_id', $payrollId)
                ->whereIn('ps_emp_id', $employeeIds)
                ->chunkById(100, function ($salaries) {
                    foreach ($salaries as $salary) {
                        $salary->earnings()->delete();     // Delete related earnings
                        $salary->deductions()->delete();   // Delete related deductions
                        $salary->delete();                 // Delete processed salary
                    }
                });

            // Update attendance summaries
            DB::table('attendance_summaries')
                ->where('as_pp_id', $payrollId)
                ->whereIn('as_emp_id', $employeeIds)
                ->update(['as_is_sal_processed' => 121]);
        });

        return redirect()->back()->with('success', 'Selected salaries have been unprocessed.');
    }

    public function processSalaries(Request $request)
    {
        $employeeIds = $request->selected_employees;
        // dd($employeeIds);
        $payrollId = $request->pp_id;
        $business_id = auth()->user()->emp_b_id;
        $authUser = Auth::user()->emp_id;

        $payrollPeriod = PayrollPeriod::where('pp_id', $payrollId)
            ->where('pp_b_id', $business_id)
            ->first();

        if (! $payrollPeriod) {
            return response()->json(['message' => 'Payroll period not found!'], 404);
        }

        $startDate = Carbon::parse($payrollPeriod->pp_start_date);
        $endDate = Carbon::parse($payrollPeriod->pp_end_date);

        if (empty($employeeIds)) {
            return response()->json(['message' => 'No employees selected'], 400);
        }

        $employees = Employee::with('fh_business.fh_currency')
            ->select('employees.*')
            ->whereIn('emp_id', $employeeIds)
            ->where('emp_b_id', $business_id)
            ->get();

        if ($employees->isEmpty()) {
            return response()->json(['message' => 'No employees found for the selected business!'], 404);
        }

        $salaries = [];
        $anyProcessed = false; // Flag to track if any employee got processed

        Employee::with('fh_business.fh_currency')
            ->select('employees.*')
            ->whereIn('emp_id', $employeeIds)
            ->where('emp_b_id', $business_id)
            ->chunkById(100, function ($employees) use (
                $startDate,
                $endDate,
                $business_id,
                $payrollId,
                $payrollPeriod,
                $authUser,
                &$anyProcessed,
                &$salaries
            ) {

                foreach ($employees as $employee) {
                    $employee_salary = SalaryEmployeeSalary::where('es_emp_id', $employee->emp_id)->first();
                    $currency = $employee->fh_business->fh_currency->c_code;

                    if (! $employee_salary) {
                        $salaries[$employee->emp_id] = [
                            'message' => "Salary record not found for Employee ID: {$employee->emp_id}",
                        ];

                        continue;
                    }

                    $existingProcessedSalary = ProcessedEmployeeSalary::where([
                        'ps_emp_id' => $employee->emp_id,
                        'ps_b_id' => $business_id,
                        'ps_payroll_id' => $payrollId,
                    ])->first();

                    if ($existingProcessedSalary) {
                        continue;
                    }

                    $heldSalary = SalaryHold::where([
                        'sh_emp_id' => $employee->emp_id,
                        'sh_b_id' => $business_id,
                        'sh_pp_id' => $payrollId,
                    ])->where('sh_status', 'released')->first();

                    $weekOffDates = CentralLogics::getWeekOffDates($employee, null, null, $startDate, $endDate);
                    $filteredWeekOffDates = array_filter($weekOffDates, function ($date) use ($startDate, $endDate) {
                        return Carbon::parse($date)->between($startDate, $endDate);
                    });
                    if ($payrollPeriod->pp_type_id == 440) {
                        $salaryDetails = PayrollLogics::calculateMonthlySalary($employee, $startDate, $endDate, $employee_salary, $business_id, $payrollId);
                    } else {
                        $salaryDetails = PayrollLogics::calculateDailySalary($employee, $startDate, $endDate, $employee_salary, $business_id, $payrollId);
                    }
                    // ✅ Add held salary if exists
                    if ($heldSalary) {
                        $salaryDetails['gross_salary'] += $heldSalary->hs_amount;
                        $salaryDetails['total_earnings'] += $heldSalary->hs_amount;
                        $salaryDetails['net_salary'] += $heldSalary->hs_amount;

                        // Add held salary as an earning type in breakdown
                        $salaryDetails['earnings_breakdown'][] = [
                            'type_id' => 'hold_'.$heldSalary->sh_id,
                            'earning_type' => 'Held Salary',
                            'amount' => number_format($heldSalary->hs_amount, 2, '.', ''),
                            'employee' => ['' => number_format($heldSalary->hs_amount, 2)],
                        ];

                        // Mark held salary as released
                        $heldSalary->update(['sh_status' => 'processed', 'sh_released_at' => now()]);
                    }

                    // Skip if already processed and not on hold
                    if ($existingProcessedSalary && ! $heldSalary) {
                        continue;
                    }

                    $processedSalary = ProcessedEmployeeSalary::create([
                        'ps_b_id' => $business_id,
                        'ps_br_id' => $employee->emp_br_id ?? null,
                        'ps_emp_id' => $employee->emp_id,
                        'ps_payroll_id' => $payrollId,
                        'ps_monthly_salary' => round((float) str_replace(',', '', $salaryDetails['monthly_salary']), 2),
                        'ps_basic_salary' => round((float) str_replace(',', '', $salaryDetails['basic_salary']), 2),
                        'ps_per_day_salary' => round((float) str_replace(',', '', $salaryDetails['per_day_salary']), 2),
                        'ps_worked_days_salary' => round((float) str_replace(',', '', $salaryDetails['worked_days_salary']), 2),
                        'ps_earnings' => round((float) str_replace(',', '', $salaryDetails['total_earnings']), 2),
                        'ps_employee_deductions' => round((float) str_replace(',', '', $salaryDetails['total_employee_deductions']), 2),
                        'ps_employer_deductions' => round((float) str_replace(',', '', $salaryDetails['total_employer_deductions']), 2),
                        'ps_monthly_gross' => round((float) str_replace(',', '', $salaryDetails['gross_salary']), 2),
                        'ps_monthly_net_salary' => round((float) str_replace(',', '', $salaryDetails['net_salary']), 2),
                        'ps_monthly_ctc' => round((float) str_replace(',', '', $salaryDetails['monthly_ctc']), 2),
                        'ps_total_days_in_month' => $salaryDetails['total_month_working_days'],
                        'ps_week_off_count' => $salaryDetails['week_off_count'],
                        'ps_total_month_working_days' => $salaryDetails['total_month_working_days'],
                        'ps_total_days_worked' => $salaryDetails['total_days_worked'],
                        'ps_present_days' => $salaryDetails['present_days'],
                        'ps_workable_days' => $salaryDetails['workable_days'],
                        'ps_upl_count' => $salaryDetails['total_upl_count'],
                        'ps_days_late' => $salaryDetails['lateCount'],
                        'ps_esic_worked_days' => $salaryDetails['esic_worked_days'],
                        'ps_esic_monthly_gross' => $salaryDetails['esic_monthly_gross'],
                        'ps_currency' => $currency,
                        'ps_is_payslip' => 1,
                        'ps_generated_by' => $authUser,
                    ]);

                    foreach ($salaryDetails['earnings_breakdown'] as $earningType => $earningData) {
                        ProcessedSalaryEarning::create([
                            'ps_id' => $processedSalary->ps_id,
                            'ps_earning_type' => $earningData['earning_type'],
                            'ps_e_amount' => round((float) str_replace(',', '', $earningData['amount']), 2),
                            'ps_earning_type_id' => $earningData['type_id'],
                        ]);
                    }

                    foreach ($salaryDetails['deductions_breakdown'] as $deductionType => $deductionData) {
                        // Handle numeric keys with 'deduction_type' inside the array
                        if (is_int($deductionType)) {
                            $deductionType = $deductionData['deduction_type'] ?? 'Other';
                        }

                        $typeId = $deductionData['type_id'] ?? null;

                        // Employee Deductions
                        if (isset($deductionData['employee'])) {
                            foreach ($deductionData['employee'] as $key => $amount) {
                                $psDeductionType = $key === '' ? $deductionType : ($deductionType.' - '.$key);

                                ProcessedSalaryDeduction::create([
                                    'ps_id' => $processedSalary->ps_id,
                                    'ps_deduction_type' => $psDeductionType,
                                    'ps_d_amount' => round((float) str_replace(',', '', $amount), 2),
                                    'ps_d_category' => 'employee',
                                    'ps_deduction_type_id' => $typeId,
                                ]);
                            }
                        }

                        // Employer Deductions
                        if (isset($deductionData['employer'])) {
                            foreach ($deductionData['employer'] as $key => $amount) {
                                $psDeductionType = $key === '' ? $deductionType : ($deductionType.' - '.$key);

                                ProcessedSalaryDeduction::create([
                                    'ps_id' => $processedSalary->ps_id,
                                    'ps_deduction_type' => $psDeductionType,
                                    'ps_d_amount' => round((float) str_replace(',', '', $amount), 2),
                                    'ps_d_category' => 'employer',
                                    'ps_deduction_type_id' => $typeId,
                                ]);
                            }
                        }
                    }

                    $payslipUrl = $this->generatePayslip($processedSalary, $payrollPeriod);
                    $processedSalary->update(['ps_payslip_url' => $payslipUrl]);

                    $summary = AttendanceSummary::where([
                        'as_emp_id' => $employee->emp_id,
                        'as_pp_id' => $payrollId,
                        'as_b_id' => $business_id,
                    ])->first();

                    if ($summary) {
                        $summary->as_is_sal_processed = 120;
                        $summary->updated_at = now();
                        $summary->save();
                    }

                    $salaries[$employee->emp_id] = [
                        'employee_id' => $employee->emp_id,
                        'salary_details' => $salaryDetails,
                        'payslip_url' => $payslipUrl,
                        'week_off_dates' => array_values($filteredWeekOffDates),
                        'payroll_period' => $payrollPeriod,
                        'status' => 'Saved Successfully',
                    ];

                    $anyProcessed = true; // Set flag true if this employee's salary processed
                }

            });

        // ✅ Update payroll period only if any employee was processed
        if ($anyProcessed) {
            $payrollPeriod->update(['pp_is_processed' => 120]);
        }

        return redirect()->route('payroll.period.list')->with('process_success', 'Salaries processed and saved successfully!');
    }

    public function calculateMonthlySalary($employee, $startDate, $endDate, $employeeSalary, $business_id, $payrollId)
    {

        $business = DB::table('businesses')->where('b_id', $business_id)->first();
        $business_name = $business->b_name ?? 'null';
        $branch = DB::table('branches')->where('br_id', $employee->emp_br_id)->first();
        $branch_name = $branch->br_name ?? 'null';
        $employeeId = $employee->emp_id;
        $stateId = $branch->br_s_id ?? null;
        $state = DB::table('states')->where('s_id', $stateId)->first();

        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);
        $totalDaysInPeriod = $startDate->diffInDays($endDate) + 1;

        $attendanceSummary = AttendanceSummary::where('as_emp_id', $employee->emp_id)
            ->where('as_b_id', $business_id)
            ->where('as_pp_id', $payrollId)
            ->first();

        if (! $attendanceSummary) {
            return response()->json(['error' => 'Attendance summary not found for employee ID '.$employee->emp_id], 404);
        }

        // if ($adhocTransactions->isEmpty()) {
        //     return response()->json(['error' => 'Adhoc transactions not found for employee ID ' . $employee->emp_id], 404);
        // }

        $presentCount = (float) ($attendanceSummary->as_total_present ?? 0);
        $leaveCount = $attendanceSummary->as_total_leave ?? 0;
        $holidayCount = $attendanceSummary->as_total_holiday ?? 0;
        $weekOffCount = $attendanceSummary->as_total_weekoff ?? 0;
        $absentCount = $attendanceSummary->as_total_absent ?? 0;
        $overtimeCount = $attendanceSummary->as_total_overtime_hours ?? 0;
        $uplCpunt = $attendanceSummary->as_total_upl_count ?? 0;
        $lateCount = $attendanceSummary->as_days_late ?? 0;

        // $totalDaysWorked = (float)$presentCount + (float)$weekOffCount + (float)$holidayCount;
        $workedDaysinMonth = $attendanceSummary->as_total_worked_days ?? 0;
        $totalDaysWorked = (float) $workedDaysinMonth;
        $workableDays = $totalDaysInPeriod;
        $monthlySalary = round($employeeSalary->es_monthly_gross ?? 0, 2);   // Calculation on Monthly Gross
        $perDaySalary = round($monthlySalary / $workableDays, 2);
        $workedDaysSalary = round($perDaySalary * $totalDaysWorked, 2);

        $earnings = DB::table('salary_allowances')->where('sa_b_id', $employee->emp_b_id)->get();
        $empDA = SalaryEmployeeEarnings::where('es_e_b_id', $employee->emp_b_id)->where('es_e_cal_type_id', '=', 347)->where('es_e_type_id', '=', 362)->first();
        $deductions = DB::table('statutory_deductions')->where('std_b_id', $employee->emp_b_id)->get();
        $basicEarning = collect($earnings)->firstWhere('sa_earning_type_id', 360);
        $basicPercentage = $basicEarning->sa_threshold_value ?? 0;
        $basicSalary = round($workedDaysSalary * ($basicPercentage / 100), 2);

        $employeeDA = round($empDA->es_e_amount ?? 0, 2);

        // ESIC Report Days
        $fractionalPart = $totalDaysWorked - floor($totalDaysWorked);
        if ($fractionalPart <= 0.4) {
            $esicWorkedDays = floor($totalDaysWorked);
        } else {
            $esicWorkedDays = ceil($totalDaysWorked);
        }
        $esicMonthlyGross = round($esicWorkedDays * $perDaySalary, 2);

        $allowancesBreakdown = [];
        $totalAllowances = 0;
        foreach ($earnings as $earning) {
            $allowanceAmount = 0;

            // Basic Salary Calculation (Type ID: 360)
            if ($earning->sa_calculation_type == 346 && $earning->sa_earning_type_id == 360) {
                $empEarning = DB::table('employee_salaries_earnings')
                    ->where('es_e_emp_id', $employee->emp_id)
                    ->where('es_e_cal_type_id', 346)
                    ->where('es_e_type_id', 360)
                    ->first();

                $allowanceAmount = $empEarning ? round(($empEarning->es_e_amount / $workableDays) * $totalDaysWorked, 2) : 0;
                $empProcessedBasic = $allowanceAmount;
            } elseif ($earning->sa_calculation_type == 346 && $earning->sa_earning_type_id == 362) {
                $empEarning = DB::table('employee_salaries_earnings')
                    ->where('es_e_emp_id', $employee->emp_id)
                    ->where('es_e_cal_type_id', 346)
                    ->where('es_e_type_id', 362)
                    ->first();

                $allowanceAmount = $empEarning ? round(($empEarning->es_e_amount / $workableDays) * $totalDaysWorked, 2) : 0;
            } elseif ($earning->sa_calculation_type == 346 && $earning->sa_earning_type_id == 363) {
                $empEarning = DB::table('employee_salaries_earnings')
                    ->where('es_e_emp_id', $employee->emp_id)
                    ->where('es_e_cal_type_id', 346)
                    ->where('es_e_type_id', 363)
                    ->first();

                $allowanceAmount = $empEarning ? round(($empEarning->es_e_amount / $workableDays) * $totalDaysWorked, 2) : 0;
            }

            // House Rent Allowance (Type ID: 361)
            elseif ($earning->sa_calculation_type == 347 && $earning->sa_earning_type_id == 361) {
                $empEarning = DB::table('employee_salaries_earnings')
                    ->where('es_e_emp_id', $employee->emp_id)
                    ->where('es_e_cal_type_id', 347)
                    ->where('es_e_type_id', 361)
                    ->first();

                $allowanceAmount = $empEarning ? round(($empEarning->es_e_amount / $workableDays) * $totalDaysWorked, 2) : 0;
            }
            // Dearness Allowance (Type ID: 362)
            elseif ($earning->sa_calculation_type == 347 && $earning->sa_earning_type_id == 362) {
                $empEarning = DB::table('employee_salaries_earnings')
                    ->where('es_e_emp_id', $employee->emp_id)
                    ->where('es_e_cal_type_id', 347)
                    ->where('es_e_type_id', 362)
                    ->first();

                if ($empEarning) {
                    $allowanceAmount = round(($empEarning->es_e_amount / $workableDays) * $totalDaysWorked, 2);
                    $empProcessedDA = $allowanceAmount ?? 0;
                } else {
                    $empProcessedDA = 0;  // Set to 0 if the component doesn't exist
                }

                // $allowanceAmount = $empEarning ? round(($empEarning->es_e_amount / $workableDays) * $totalDaysWorked, 2) : 0;
                // $empProcessedDA = $allowanceAmount ?? 0;
            }

            // Processing Other Allowances (Type ID: 348)
            if ($earning->sa_calculation_type == 348) {
                $empEarnings = DB::table('employee_salaries_earnings')
                    ->where('es_e_emp_id', $employee->emp_id)
                    ->where('es_e_cal_type_id', 348)
                    ->get()
                    ->toArray();

                foreach ($empEarnings as $empEarning) {
                    $allowanceId = $empEarning->es_sa_id;
                    $typeId = $empEarning->es_e_type_id;
                    $amount = $empEarning->es_e_amount ?? 0;
                    $calculatedAmount = round(($amount / $workableDays) * $totalDaysWorked, 2);

                    // ✅ Store allowance separately by `typeId` and `allowanceId`
                    if (! isset($allowancesBreakdown[$typeId][$allowanceId])) {
                        $allowancesBreakdown[$typeId][$allowanceId] = $calculatedAmount;
                        $totalAllowances += $calculatedAmount;
                    }
                }
            } else {
                // ✅ Ensure each allowance type is added only once
                if ($allowanceAmount > 0 && ! isset($allowancesBreakdown[$earning->sa_earning_type_id])) {
                    $allowancesBreakdown[$earning->sa_earning_type_id] = $allowanceAmount;
                    $totalAllowances += $allowanceAmount;
                }
            }
        }

        // ✅ Step 1: Calculate Remaining Allowance
        $remainingBalance = 0;

        if ($employeeSalary->es_rem_allowance > 0 && $workableDays > 0) {
            $remainingBalance = round(($employeeSalary->es_rem_allowance / $workableDays) * $totalDaysWorked, 2);
            $totalAllowances += $remainingBalance;
        }

        $allowancesBreakdown['Remaining'] = $remainingBalance;

        $grossSalary = $totalAllowances;
        $earningTypeNames = SalaryAllowance::where('sa_b_id', $business_id)
            ->pluck('sa_title', 'sa_earning_type_id')
            ->toArray();

        $subAllowanceNames = SalaryAllowance::where('sa_b_id', $business_id)
            ->pluck('sa_title', 'sa_id')
            ->toArray();

        // Get Adhoc Transactions with Component and Details
        $adhocTransactions = AdhocTransaction::with(['adhocComponent', 'transaction_details.component'])
            ->where('at_emp_id', $employee->emp_id)
            ->where('at_b_id', $business_id)
            ->where('at_pp_id', $payrollId)
            ->get();

        // $grossSalary = 0;
        // $totalAllowances = 0;
        $formattedEarningsBreakdown = [];

        // Add standard allowances first (from your existing logic)
        foreach ($allowancesBreakdown as $earningTypeId => $amount) {
            if ($earningTypeId === 'Remaining') {
                $formattedEarningsBreakdown[] = [
                    'type_id' => 'Remaining',
                    'earning_type' => 'Other Allowance',
                    'amount' => number_format((float) $amount, 2, '.', ''),
                ];

                continue;
            }

            if (is_array($amount)) {
                foreach ($amount as $subId => $subAmount) {
                    $earningName = $subAllowanceNames[$subId] ?? 'Unknown Allowance';

                    $formattedEarningsBreakdown[] = [
                        'type_id' => $subId,
                        'earning_type' => $earningName,
                        'amount' => number_format((float) $subAmount, 2, '.', ''),
                    ];
                }
            } else {
                $earningName = $earningTypeNames[$earningTypeId] ?? 'Other Allowance';

                $formattedEarningsBreakdown[] = [
                    'type_id' => $earningTypeId,
                    'earning_type' => $earningName,
                    'amount' => number_format((float) $amount, 2, '.', ''),
                ];
            }
        }

        $formattedEarningsBreakdown = [];

        foreach ($allowancesBreakdown as $earningTypeId => $amount) {
            if ($earningTypeId === 'Remaining') {
                $formattedEarningsBreakdown[] = [
                    'type_id' => 'Remaining',
                    'earning_type' => 'Other Allowance',
                    'amount' => number_format((float) $amount, 2, '.', ''),
                ];

                continue;
            }

            if (is_array($amount)) {
                foreach ($amount as $subId => $subAmount) {
                    $earningName = $subAllowanceNames[$subId] ?? 'Unknown Allowance';

                    $formattedEarningsBreakdown[] = [
                        'type_id' => $subId,
                        'earning_type' => $earningName,
                        'amount' => number_format((float) $subAmount, 2, '.', ''),
                    ];
                }
            } else {
                $earningName = $earningTypeNames[$earningTypeId] ?? 'Other Allowance';

                $formattedEarningsBreakdown[] = [
                    'type_id' => $earningTypeId,
                    'earning_type' => $earningName,
                    'amount' => number_format((float) $amount, 2, '.', ''),
                ];
            }
        }

        // foreach ($adhocTransactions as $transaction) {
        //     $component = $transaction->adhocComponent;           // make sure you eager‑loaded it
        //     $amount    = (float) $transaction->at_e_amount;

        //     // include only positive amounts and only if a component exists
        //     if ($component && $amount > 0) {
        //         $formattedEarningsBreakdown[] = [
        //             'type_id'      => 'adhoc_' . $transaction->at_id,
        //             'earning_type' => $component->ac_adhoc_component_name ?? 'Adhoc',
        //             'amount'       => number_format($amount, 2, '.', ''),
        //         ];
        //     }
        // }

        // dd($formattedEarningsBreakdown);

        $totalEmployeeDeductions = 0;
        $totalEmployerDeductions = 0;
        $deductionsBreakdown = [];
        $professionalTax = 0;

        if ($employeeSalary->es_base_salary < 10090) {
            $employeeBasicforPF = ($empProcessedBasic ?? 0) + ($empProcessedDA ?? 0);
        } else {
            $employeeBasicforPF = $empProcessedBasic ?? 0;
        }

        foreach ($deductions as $deduction) {
            $deductionAmountEmployee = 0;
            $deductionAmountEmployer = 0;

            if ($deduction->std_deduction_type_id == 351 && ($employee->emp_is_pf_enabled == 121 || $employee->emp_is_pf_enabled == 0)) {
                continue; // Skip PF
            }

            if ($deduction->std_deduction_type_id == 352 && ($employee->emp_esic_limit == 121 || $employee->emp_esic_limit == 0)) {
                continue; // Skip ESIC
            }

            if ($deduction->std_deduction_type_id == 351) {
                $pfThreshold = $deduction->std_threshold;

                $basicForPF = min($employeeBasicforPF, $pfThreshold);
                $employeeContriRate = $deduction->std_employee_contri_rate_amount;
                $employerContriRate = $deduction->std_employer_contri_rate_amount;

                // $basicForPF = $employeeBasicforPF > $pfCap ? $pfCap : $employeeBasicforPF;
                // $basicForPF = $employeeBasicforPF;

                $deductionAmountEmployee = round($basicForPF * ($employeeContriRate / 100), 2);
                $deductionAmountEmployer = round($basicForPF * ($employerContriRate / 100), 2);
                // dd($deductionAmountEmployee, $deductionAmountEmployer);
                $epsAmount = round($basicForPF * (8.33 / 100), 2);
                $epfAmount = round($basicForPF * (3.67 / 100), 2);
                $edlisAmount = round($basicForPF * (0.50 / 100), 2);
                $epfAdminAmount = round($basicForPF * (0.50 / 100), 2);
                $edlisAdminAmount = round($basicForPF * (0.01 / 100), 2);

                $deductionsBreakdown[351] = [
                    'Employee Contribution (12%)' => $deductionAmountEmployee,
                    'Employer Contribution (12%)' => $deductionAmountEmployer,
                    'Employer EPS (8.33%)' => $epsAmount,
                    'Employer EPF (3.67%)' => $epfAmount,
                    'Employer EDLIS (0.50%)' => $edlisAmount,
                    'EPF Admin (0.50%)' => $epfAdminAmount,
                    'EDLIS Admin (0.01%)' => $edlisAdminAmount,
                ];

                $totalEmployeeDeductions += $deductionAmountEmployee;
                // $totalEmployerDeductions += ($epsAmount + $epfAmount + $edlisAmount + $epfAdminAmount + $edlisAdminAmount);
                $totalEmployerDeductions += $deductionAmountEmployer;
            }

            if ($deduction->std_deduction_type_id == 352 && $monthlySalary <= $deduction->std_threshold) {
                $deductionAmountEmployee = round($workedDaysSalary * ($deduction->std_employee_contri_rate_amount / 100), 2);
                $deductionAmountEmployer = round($workedDaysSalary * ($deduction->std_employer_contri_rate_amount / 100), 2);

                $deductionsBreakdown[352] = [
                    'Employee Contribution (0.75%)' => $deductionAmountEmployee,
                    'Employer Contribution (3.25%)' => $deductionAmountEmployer,
                ];

                $totalEmployeeDeductions += $deductionAmountEmployee;
                $totalEmployerDeductions += $deductionAmountEmployer;
            }

        }

        // Professional Tax Calculation
        if ($stateId) {
            $taxSlab = DB::table('professional_tax_master')
                ->where('ptm_s_id', $stateId)
                ->where('ptm_income_from', '<=', $grossSalary)
                ->where(function ($query) use ($grossSalary) {
                    $query->where('ptm_income_to', '>=', $grossSalary)
                        ->orWhereNull('ptm_income_to');
                })
                ->first();

            $professionalTax = $taxSlab ? $taxSlab->ptm_tax_amount : 0;
        }

        $deductionsBreakdown[353] = [
            'Employee Contribution' => number_format($professionalTax, 2),
        ];
        $totalEmployeeDeductions += $professionalTax;

        // LWF Calculation
        $lwfEmployee = 0;
        $lwfEmployer = 0;
        $currentMonth = Carbon::now()->month;

        if ($stateId) {
            $lwfData = DB::table('labour_welfare_fund_master')
                ->where('state_id', $stateId)
                ->first();

            if ($lwfData) {
                if ($lwfData->cycle_id == 355) {
                    $lwfEmployee = $lwfData->lwf_employee_contri;
                    $lwfEmployer = $lwfData->lwf_employer_contri;
                } elseif ($lwfData->cycle_id == 356 && in_array($currentMonth, [6, 12])) {
                    $lwfEmployee = $lwfData->lwf_employee_contri;
                    $lwfEmployer = $lwfData->lwf_employer_contri;
                } elseif ($lwfData->cycle_id == 4162 && $currentMonth == 12) {
                    $lwfEmployee = $lwfData->lwf_employee_contri;
                    $lwfEmployer = $lwfData->lwf_employer_contri;
                }
            }
        }

        // Add LWF
        $totalEmployeeDeductions += $lwfEmployee;
        $totalEmployerDeductions += $lwfEmployer;

        $loanDeduction = $this->calculateLoanDeduction($employeeId, $payrollId, $business_id);
        $totalEmployeeDeductions += $loanDeduction;

        // Add Loan Deduction to the breakdown with ID 358
        $deductionsBreakdown[358] = [
            'Employee Contribution' => number_format($loanDeduction, 2),
        ];
        // Fetch deduction type names from master table
        $deductionTypeNames = DB::table('statutory_deductions')
            ->join('master_table', 'master_table.m_id', '=', 'statutory_deductions.std_deduction_type_id')
            ->pluck('master_table.m_name', 'statutory_deductions.std_deduction_type_id')
            ->toArray();

        // Ensure $deductionsBreakdown is an array
        if (! is_array($deductionsBreakdown)) {
            $deductionsBreakdown = [];
        }
        $formattedDeductionsBreakdown = [];

        // Process all standard deductions
        foreach ($deductionsBreakdown as $typeId => $deductions) {
            // Fetch the deduction type name from master table
            $typeName = $deductionTypeNames[$typeId] ?? 'Unknown Deduction';

            // Skip unknown deductions
            if ($typeName === 'Unknown Deduction') {
                continue;
            }

            // Initialize employee & employer arrays if not set
            if (! isset($formattedDeductionsBreakdown[$typeName])) {
                $formattedDeductionsBreakdown[$typeName] = [
                    'type_id' => $typeId,
                    'employee' => [],
                    'employer' => [],
                ];
            }

            foreach ($deductions as $key => $value) {
                // Ensure the value is numeric before formatting
                $formattedValue = is_numeric($value) ? number_format($value, 2) : '0.00';

                if (stripos($key, 'employee') !== false) {
                    $formattedDeductionsBreakdown[$typeName]['employee'][$key] = $formattedValue;
                } elseif (stripos($key, 'employer') !== false) {
                    $formattedDeductionsBreakdown[$typeName]['employer'][$key] = $formattedValue;
                }
            }

            // Handle specific adjustments like EPF breakdown
            if ($typeName === 'EPF') {
                $formattedDeductionsBreakdown[$typeName]['employer']['EPF Admin (0.50%)'] = number_format($epfAdminAmount, 2);
                $formattedDeductionsBreakdown[$typeName]['employer']['EDLIS Admin (0.01%)'] = number_format($edlisAdminAmount, 2);
            }

            // Ensure default 0.00 if no values are found
            if (empty($formattedDeductionsBreakdown[$typeName]['employee'])) {
                $formattedDeductionsBreakdown[$typeName]['employee']['Employee Contribution'] = '0.00';
            }
            if (empty($formattedDeductionsBreakdown[$typeName]['employer'])) {
                $formattedDeductionsBreakdown[$typeName]['employer']['Employer Contribution'] = '0.00';
            }
        }

        // ✅ Add Professional Tax (Type ID 353) to Deductions Breakdown
        if ($professionalTax > 0) {
            $formattedDeductionsBreakdown['Professional Tax'] = [
                'type_id' => 353,
                'employee' => ['Employee Contribution' => number_format($professionalTax, 2)],
                'employer' => ['Employer Contribution' => '0.00'], // Assuming no employer contribution for professional tax
            ];
        }

        // 👇 Add Loan Deduction (Type ID 358) to Deductions Breakdown
        // $loanDeduction = $this->calculateLoanDeduction($employeeId, $payrollId, $business_id);
        if ($loanDeduction > 0) {
            $formattedDeductionsBreakdown['Loan Deduction'] = [
                'type_id' => 358,
                'employee' => ['' => number_format($loanDeduction, 2)],
                'employer' => ['' => '0.00'], // Assuming no employer contribution for loan deduction
            ];
        }

        // Ensure that we are including 0.00 where necessary for employer contributions if no values are found
        // foreach ($formattedDeductionsBreakdown as $typeName => &$deductions) {
        //     if (empty($deductions['employee'])) {
        //         $deductions['employee']['Employee Contribution'] = "0.00";
        //     }
        //     if (empty($deductions['employer'])) {
        //         $deductions['employer']['Employer Contribution'] = "0.00";
        //     }
        // }

        $totalFormattedEmployee = 0.00;
        $totalFormattedEmployer = 0.00;

        foreach ($formattedDeductionsBreakdown as $section) {
            // Sum employee contributions
            foreach ($section['employee'] ?? [] as $val) {
                $totalFormattedEmployee += floatval(str_replace(',', '', $val));
            }

            // Sum employer contributions
            foreach ($section['employer'] ?? [] as $key => $val) {
                // Only allow "Employer Contribution (12%)" when deduction type is 351
                if (($section['type_id'] ?? null) == 351) {
                    if ($key === 'Employer Contribution (12%)') {
                        $totalFormattedEmployer += floatval(str_replace(',', '', $val));
                    }
                } else {
                    // For other deduction types, include all employer contributions
                    $totalFormattedEmployer += floatval(str_replace(',', '', $val));
                }
            }

        }
        // Override totals to ensure they're consistent with the final formatted data
        $totalEmployeeDeductions = $totalFormattedEmployee;
        $totalEmployerDeductions = $totalFormattedEmployer;

        // ✅ Add Professional Tax (Type ID 353) to Deductions Breakdown
        if ($professionalTax > 0) {
            $formattedDeductionsBreakdown[353] = [
                'employee' => ['Employee Contribution' => number_format($professionalTax, 2)],
            ];
        }

        foreach ($adhocTransactions as $transaction) {
            foreach ($transaction->transaction_details as $detail) {
                $componentName = $detail->component->ac_adhoc_component_name ?? 'Adhoc Component';
                // Check for Earning
                if ((float) $detail->earning_amount > 0) {
                    $amount = (float) $detail->earning_amount;

                    $grossSalary += $amount;
                    $totalAllowances += $amount;

                    $formattedEarningsBreakdown[] = [
                        'type_id' => $detail->atd_id,
                        'earning_type' => $componentName,
                        'amount' => number_format($amount, 2, '.', ''),
                        'employee' => ['' => number_format($amount, 2)],
                    ];
                }

                // Check for Deduction
                if ((float) $detail->deduction_amount > 0) {
                    $amount = (float) $detail->deduction_amount;

                    $totalEmployeeDeductions += $amount;

                    $formattedDeductionsBreakdown[] = [
                        'type_id' => $detail->atd_id,
                        'deduction_type' => $componentName,
                        'amount' => number_format($amount, 2, '.', ''),
                        'employee' => ['' => number_format($amount, 2)],
                    ];
                }
            }
        }
        // dd($formattedDeductionsBreakdown);
        $lateComingDeduction = $this->calculateLateComingDeduction($employeeId, $payrollId, $business_id, $lateCount, $grossSalary);
        // dd($lateComingDeduction);
        $totalEmployeeDeductions += $lateComingDeduction;
        if ($lateComingDeduction > 0) {
            $formattedDeductionsBreakdown['Late Deduction'] = [
                'type_id' => 444,
                'employee' => ['' => number_format($lateComingDeduction, 2)],
            ];
        }

        $netSalary = $grossSalary - $totalEmployeeDeductions;
        $monthlyCtc = $grossSalary + $totalEmployerDeductions;

        return [
            'business_name' => $business_name,
            'branch_name' => $branch_name,
            'month' => $startDate->format('Y-m'),
            'month_name' => $startDate->format('F'),
            'total_days_in_month' => number_format($totalDaysInPeriod),
            'week_off_count' => number_format($weekOffCount),
            'total_month_working_days' => number_format($workableDays),
            'total_days_worked' => number_format($totalDaysWorked, 2),
            'present_days' => $presentCount,
            'workable_days' => number_format($workableDays),
            'lateCount' => $lateCount,
            'monthly_salary' => number_format($monthlySalary, 2),
            'per_day_salary' => number_format($perDaySalary, 2),
            'worked_days_salary' => number_format($workedDaysSalary, 2),
            'basic_salary' => number_format($basicSalary, 2),
            'total_earnings' => number_format($totalAllowances, 2),
            'earnings_breakdown' => $formattedEarningsBreakdown,
            'deductions_breakdown' => $formattedDeductionsBreakdown,
            'total_employee_deductions' => number_format($totalEmployeeDeductions, 2),
            'total_employer_deductions' => number_format($totalEmployerDeductions, 2),
            'gross_salary' => number_format($grossSalary, 2),
            'professionalTax' => number_format($professionalTax, 2),
            'net_salary' => number_format($netSalary, 2),
            'monthly_ctc' => number_format($monthlyCtc, 2),
            'total_upl_count' => $uplCpunt,
            'esic_worked_days' => $esicWorkedDays,
            'esic_monthly_gross' => $esicMonthlyGross,
        ];
    }

    private function calculateLateComingDeduction($employeeId, $payrollId, $business_id, $lateCount, $grossSalary)
    {
        $payrollPeriod = PayrollPeriod::where('pp_id', $payrollId)->first();
        $payrollStart = Carbon::parse($payrollPeriod->pp_start_date);
        $payrollEnd = Carbon::parse($payrollPeriod->pp_end_date);

        $lateComingRuleData = AutomationRule::where('ar_rule_type', 414)
            ->where('ar_b_id', $business_id)
            ->first();

        if (! $lateComingRuleData || $lateCount == 0) {
            return 0; // No rule or no late instances
        }

        $maxOccurrences = (int) $lateComingRuleData->ar_occurrences;

        if ($lateCount <= $maxOccurrences) {
            return 0;
        }

        $excessLates = $lateCount - $maxOccurrences;

        // Case 1: Flat penalty mode
        if ((int) $lateComingRuleData->ar_is_mode_enabled === 0) {
            $penaltyAmount = (float) $lateComingRuleData->ar_penalty_amount;

            return $penaltyAmount * $excessLates;
        }

        // Case 2: Dynamic deduction based on salary and attendance
        if ((int) $lateComingRuleData->ar_is_enabled === 1) {
            // Get the employee salary record
            $salaryRecord = SalaryEmployeeSalary::where('es_emp_id', $employeeId)
                ->where('es_b_id', $business_id)
                ->first();

            if (! $salaryRecord) {
                return 0; // No salary record found
            }

            $monthlyGross = (float) $grossSalary;

            // Get attendance summary
            $attendanceSummary = AttendanceSummary::where('as_emp_id', $employeeId)
                ->where('as_pp_id', $payrollId)
                ->first();

            if (! $attendanceSummary) {
                return 0; // No attendance summary found
            }

            $totalDays = (float) $attendanceSummary->as_total_days;
            $workedDays = (float) $attendanceSummary->as_total_worked_days;

            if ($totalDays == 0) {
                return 0; // Prevent division by zero
            }

            $perDaySalary = $monthlyGross / $totalDays;

            // Determine deduction multiplier based on ar_mark_absent
            $deductionMultiplier = 0; // default (no deduction)

            if ((int) $lateComingRuleData->ar_mark_absent === 201) {
                $deductionMultiplier = 1.0; // Full day
            } elseif ((int) $lateComingRuleData->ar_mark_absent === 202) {
                $deductionMultiplier = 0.5; // Half day
            }

            // Calculate total deduction
            $deductionAmount = $excessLates * $deductionMultiplier * $perDaySalary;

            return round($deductionAmount, 2);
        }

        return 0;
    }

    private function calculateLoanDeduction($employeeId, $payrollId, $business_id)
    {
        $payrollPeriod = PayrollPeriod::where('pp_id', $payrollId)->first();
        $payrollStart = Carbon::parse($payrollPeriod->pp_start_date);
        $payrollEnd = Carbon::parse($payrollPeriod->pp_end_date);

        $settings = AdvanceLoanSetting::where('als_b_id', $business_id)->first();
        if (! $settings) {
            return 0;
        }

        $minRepayment = $settings->als_min_repayment;
        $maxAge = $settings->als_max_age;
        $applyInterest = $settings->als_enable_interest;
        $interestRate = $settings->als_interest_rate;
        $interestScope = $settings->als_interest_scope;

        $employee = Employee::where('emp_id', $employeeId)->first();
        if (! $employee) {
            return 0;
        }

        if ($settings->als_enable_age_criteria && $employee->emp_dob) {
            $age = Carbon::parse($employee->emp_dob)->age;
            if ($age > $maxAge) {
                return 0;
            }
        }

        $installments = PayrollLoanInstallment::join('loan_requests', 'loan_requests.lnr_id', '=', 'payroll_loan_installments.pli_loan_id')
            ->where('loan_requests.lnr_emp_id', $employeeId)
            ->where('payroll_loan_installments.pli_b_id', $business_id)
            ->whereBetween('payroll_loan_installments.pli_due_date', [$payrollStart, $payrollEnd])
            ->orWhere('payroll_loan_installments.pli_due_date', '=', $payrollStart)
            ->where('payroll_loan_installments.pli_status', '!=', 'paid')
            ->get();

        $loanDeduction = 0;

        foreach ($installments as $installment) {
            if ($installment->pli_amount < $minRepayment) {
                continue; // skip this installment
            }

            $finalAmount = $installment->pli_amount;

            // Interest logic
            if ($applyInterest && $installment->lnr_salary_at_time) {
                $principal = $installment->lnr_amount;
                $salary = $installment->lnr_salary_at_time;

                if ($principal > $salary) {
                    $excess = $principal - $salary;

                    if ($interestScope === 'full') {
                        $interest = ($principal * $interestRate) / 100 / 12;
                    } else {
                        $interest = ($excess * $interestRate) / 100 / 12;
                    }

                    $finalAmount += round($interest, 2);
                }
            }

            $loanDeduction += $finalAmount;

            // Mark as paid
            $installment->pli_status = 'paid';
            $installment->save();

            // Check if loan is settled
            $remaining = PayrollLoanInstallment::where('pli_loan_id', $installment->pli_loan_id)
                ->where('pli_status', '!=', 'paid')
                ->count();

            if ($remaining === 0) {
                $loan = LoanRequest::find($installment->pli_loan_id);
                if ($loan) {
                    $loan->lnr_status = 'settled';
                    $loan->save();
                }
            }
        }

        return $loanDeduction;
    }

    public function viewPayslip2($id)
    {
        $user = auth()->user();
        $processedSalary = ProcessedEmployeeSalary::findOrFail($id);
        $authUserId = $processedSalary->ps_generated_by;
        $authUser = Employee::find($authUserId)?->emp_full_name ?? 'System';
        $payrollPeriod = PayrollPeriod::findOrFail($processedSalary->ps_payroll_id);
        $employee = Employee::with([
            'fh_department',
            'fh_designation',
            'fh_branch',
            'fh_business.fh_admin',          // ⬅️ makes $employee->fh_business available
        ])
            ->findOrFail($processedSalary->ps_emp_id);

        $businessId = (int) $user->emp_b_id;

        // Weekly payroll: combined weeks in period + per-week earnings / employee deductions.
        // Keep detection tolerant because some older UAT data may have week_id set while pp_type_id isn't synced.
        $isWeeklyPayslipRow = ! empty($processedSalary->ps_week_id) || (int) $payrollPeriod->pp_type_id === 441;
        if ($isWeeklyPayslipRow) {
            if ((int) $processedSalary->ps_b_id !== $businessId) {
                abort(403);
            }

            $pdf = app(WeeklyPayrunController::class)->makeWeeklySalarySeparatePdf($processedSalary, $businessId, true);

            $paymentMode = strtolower(trim($employee->emp_paymentmode ?? ''));
            $password = null;
            if ($paymentMode === 'bank') {
                if (! empty($employee->emp_pan_number)) {
                    $password = trim($employee->emp_pan_number);
                } else {
                    $password = 'PANNOTAVBL';
                }
            }
            if (! empty($password)) {
                $dompdf = $pdf->getDomPDF();
                $canvas = $dompdf->getCanvas();
                if (method_exists($canvas, 'get_cpdf')) {
                    $canvas->get_cpdf()->setEncryption($password, $password, ['copy', 'print']);
                } else {
                    $canvas->getAdapter()->get_cpdf()->setEncryption($password, $password, ['copy', 'print']);
                }
            }

            $employeeName = str_replace(' ', '', $employee->emp_full_name);

            return $pdf->stream("Payslip-{$employeeName}.pdf");
        }

        $logoPath = $employee->fh_business->b_logo ?? null;

        // Convert amount to words
        $numberToWords = new NumberToWords;
        $numberTransformer = $numberToWords->getNumberTransformer('en');
        $net_salary = number_format((float) str_replace(',', '', $processedSalary->ps_monthly_net_salary), 2, '.', '');
        [$integerPart, $decimalPart] = explode('.', $net_salary) + [0, 0];

        $net_salary_words = ucfirst($numberTransformer->toWords((int) $integerPart)).' rupees';
        if ((int) $decimalPart > 0) {
            $net_salary_words .= ' and '.$numberTransformer->toWords((int) $decimalPart).' paise';
        }
        $net_salary_words .= ' only';

        // Get configuration from payslip_configuration
        $config = PayslipConfiguration::where('pc_b_id', $user->emp_b_id)->latest()->first();

        $payslipOptions = [
            // Employee Info
            'show_employee_code' => $config->pc_show_employee_code ?? true,
            'show_employee_name' => $config->pc_show_employee_name ?? true,
            'show_department' => $config->pc_show_department ?? true,
            'show_designation' => $config->pc_show_designation ?? true,
            'show_branch' => $config->pc_show_branch ?? true,
            'show_bank_details' => $config->pc_show_bank_details ?? true,
            'show_doj' => $config->pc_show_doj ?? true,

            // Attendance Info
            'show_month' => $config->pc_show_month ?? true,
            'show_month_days' => $config->pc_show_month_days ?? true,
            'show_salary_days' => $config->pc_show_salary_days ?? true,
            'show_present_days' => $config->pc_show_present_days ?? true,
            'show_lwp_days' => $config->pc_show_lwp_days ?? true,
            'show_ip_uan' => $config->pc_show_ip_uan ?? true,

            // Earnings/Deductions
            'include_earnings' => $config->pc_show_earnings_breakdown ?? true,
            'include_employee_deduction' => $config->pc_show_employee_deductions_breakdown ?? true,
            'include_employer_deduction' => $config->pc_show_employer_deductions_breakdown ?? true,

            // Net Pay & Footer
            'include_ctc' => $config->pc_show_total_ctc ?? true,
            'show_signature' => $config->pc_show_signature ?? true,
            'show_disclaimer' => $config->pc_show_disclaimer ?? true,
            'show_net_salary_words' => $config->pc_show_net_salary_in_words ?? true,
            'round_off_net_salary' => $config->pc_round_off_net_salary ?? true,
        ];

        $data = [
            'employee' => $employee,
            'processedSalary' => $processedSalary,
            'employee_name' => $employee->emp_full_name,
            'monthly_salary' => $processedSalary->ps_worked_days_salary,
            'basic_salary' => $processedSalary->ps_basic_salary,
            'net_salary' => $processedSalary->ps_monthly_net_salary,
            'earnings' => $processedSalary->ps_earnings,
            'employee_deductions' => $processedSalary->ps_employee_deductions,
            'employer_deductions' => $processedSalary->ps_employer_deductions,
            'net_salary_words' => $net_salary_words,
            'logoPath' => $logoPath,
            'payroll_period' => $payrollPeriod,
            'authUser' => $authUser,
            'payslipOptions' => $payslipOptions,
        ];

        $pdf = Pdf::loadView('admin.employees.salary.monthly_salary_template_two', $data);

        // 🔐 Apply Password Based on Payment Mode
        $paymentMode = strtolower(trim($employee->emp_paymentmode ?? ''));
        // dd($user,$paymentMode);

        $password = null;

        if ($paymentMode === 'bank') {
            if (! empty($employee->emp_pan_number)) {
                $password = trim($employee->emp_pan_number);
            } else {
                $password = 'PANNOTAVBL';
            }
        }
        // Apply encryption only if password exists
        if (! empty($password)) {
            $dompdf = $pdf->getDomPDF();
            $canvas = $dompdf->getCanvas();
            if (method_exists($canvas, 'get_cpdf')) {
                $canvas->get_cpdf()->setEncryption($password, $password, ['copy', 'print']);
            } else {
                $canvas->getAdapter()->get_cpdf()->setEncryption($password, $password, ['copy', 'print']);
            }
        }

        // // 🔐 Apply Password Based on Payment Mode
        // $paymentMode = strtolower(trim($employee->emp_paymentmode ?? ''));

        // $password = null;

        // if ($paymentMode === 'bank') {

        //     if (!empty($employee->emp_pan_number)) {
        //         $password = trim($employee->emp_pan_number);
        //     } else {
        //         $password = 'PANNOTAVBL';
        //     }
        // }

        // // Apply encryption only if password exists
        // if (!empty($password)) {

        //     $dompdf = $pdf->getDomPDF();
        //     $canvas = $dompdf->getCanvas();

        //     if (method_exists($canvas, 'get_cpdf')) {
        //         $canvas->get_cpdf()->setEncryption($password, $password, ['copy', 'print']);
        //     } else {
        //         $canvas->getAdapter()->get_cpdf()->setEncryption($password, $password, ['copy', 'print']);
        //     }
        // }

        $employeeName = str_replace(' ', '', $employee->emp_full_name);

        return $pdf->stream("Payslip-{$employeeName}.pdf");
    }

    public function downloadPayslip2($id)
    {
        $user = auth()->user();
        $processedSalary = ProcessedEmployeeSalary::findOrFail($id);

        $employee = Employee::join('departments', 'departments.d_id', '=', 'employees.emp_d_id')
            ->join('designations', 'designations.dg_id', '=', 'employees.emp_dg_id')
            ->join('businesses', 'businesses.b_id', '=', 'employees.emp_b_id')
            ->join('branches', 'branches.br_id', '=', 'employees.emp_br_id')
            ->findOrFail($processedSalary->ps_emp_id);

        $payrollPeriod = PayrollPeriod::findOrFail($processedSalary->ps_payroll_id);
        $month = date('F Y', strtotime($payrollPeriod->pp_start_date));

        $numberToWords = new NumberToWords;
        $net_salary = number_format((float) str_replace(',', '', $processedSalary->ps_monthly_net_salary), 2, '.', '');
        [$integerPart, $decimalPart] = explode('.', $net_salary) + [0, 0];
        $net_salary_words = ucfirst($numberToWords->getNumberTransformer('en')->toWords((int) $integerPart)).' rupees';
        if ((int) $decimalPart > 0) {
            $net_salary_words .= ' and '.$numberToWords->getNumberTransformer('en')->toWords((int) $decimalPart).' paise';
        }
        $net_salary_words .= ' only';

        // Payslip configuration
        $config = PayslipConfiguration::where('pc_b_id', $user->emp_b_id)->latest()->first();
        $payslipOptions = [
            'show_employee_code' => $config->pc_show_employee_code ?? true,
            'show_employee_name' => $config->pc_show_employee_name ?? true,
            'show_department' => $config->pc_show_department ?? true,
            'show_designation' => $config->pc_show_designation ?? true,
            'show_branch' => $config->pc_show_branch ?? true,
            'show_bank_details' => $config->pc_show_bank_details ?? true,
            'show_doj' => $config->pc_show_doj ?? true,

            'show_month' => $config->pc_show_month ?? true,
            'show_month_days' => $config->pc_show_month_days ?? true,
            'show_salary_days' => $config->pc_show_salary_days ?? true,
            'show_present_days' => $config->pc_show_present_days ?? true,
            'show_lwp_days' => $config->pc_show_lwp_days ?? true,
            'show_ip_uan' => $config->pc_show_ip_uan ?? true,

            'include_earnings' => $config->pc_show_earnings_breakdown ?? true,
            'include_employee_deduction' => $config->pc_show_employee_deductions_breakdown ?? true,
            'include_employer_deduction' => $config->pc_show_employer_deductions_breakdown ?? true,

            'include_ctc' => $config->pc_show_total_ctc ?? true,
            'show_signature' => $config->pc_show_signature ?? true,
            'show_disclaimer' => $config->pc_show_disclaimer ?? true,
            'show_net_salary_words' => $config->pc_show_net_salary_in_words ?? true,
            'round_off_net_salary' => $config->pc_round_off_net_salary ?? true,
        ];

        $data = [
            'employee' => $employee,
            'processedSalary' => $processedSalary,
            'employee_name' => $employee->emp_full_name,
            'monthly_salary' => $processedSalary->ps_worked_days_salary,
            'basic_salary' => $processedSalary->ps_basic_salary,
            'net_salary' => $processedSalary->ps_monthly_net_salary,
            'earnings' => $processedSalary->ps_earnings,
            'employee_deductions' => $processedSalary->ps_employee_deductions,
            'employer_deductions' => $processedSalary->ps_employer_deductions,
            'net_salary_words' => $net_salary_words,
            'logoPath' => $employee->b_logo,
            'payroll_period' => $payrollPeriod,
            'payslipOptions' => $payslipOptions,
        ];

        $pdf = Pdf::loadView('admin.employees.salary.monthly_salary_template_two', $data);

        return $pdf->download("Payslip-{$employee->emp_fname}-{$employee->emp_code}-$month.pdf");
    }

    public function viewEmpPayslip($id)
    {
        $processedSalary = ProcessedEmployeeSalary::findOrFail($id);

        // Ensure the employee is accessing only their own payslip
        $employeeId = $processedSalary->ps_emp_id;
        $user = auth()->user();
        $businessId = (int) ($user->emp_b_id ?? 0);
        $isAdmin = (int) ($user->emp_r_id ?? 0) === 1;
        if (! $isAdmin && (int) ($user->emp_id ?? 0) !== (int) $employeeId) {
            abort(403);
        }

        $employee = Employee::with([
            'fh_branch',
            'fh_department',
            'fh_designation',
            'fh_business',
        ])->findOrFail($employeeId);
        // dd($employee);

        $payrollPeriod = PayrollPeriod::findOrFail($processedSalary->ps_payroll_id);
        $isWeeklyPayslipRow = ! empty($processedSalary->ps_week_id) || (int) $payrollPeriod->pp_type_id === 441;
        if ($isWeeklyPayslipRow) {
            if ((int) $processedSalary->ps_b_id !== $businessId) {
                abort(403);
            }

            $pdf = app(WeeklyPayrunController::class)->makeWeeklySalarySeparatePdf($processedSalary, $businessId, true);

            $paymentMode = strtolower(trim($employee->emp_paymentmode ?? ''));
            $password = null;
            if ($paymentMode === 'bank') {
                if (! empty($employee->emp_pan_number)) {
                    $password = trim($employee->emp_pan_number);
                } else {
                    $password = 'PANNOTAVBL';
                }
            }
            if (! empty($password)) {
                $dompdf = $pdf->getDomPDF();
                $canvas = $dompdf->getCanvas();
                if (method_exists($canvas, 'get_cpdf')) {
                    $canvas->get_cpdf()->setEncryption($password, $password, ['copy', 'print']);
                } else {
                    $canvas->getAdapter()->get_cpdf()->setEncryption($password, $password, ['copy', 'print']);
                }
            }

            $employeeName = str_replace(' ', '', $employee->emp_full_name);

            return $pdf->stream("Payslip-{$employeeName}.pdf");
        }

        $logoPath = $employee->fh_business->b_logo ?? null;

        $numberToWords = new NumberToWords;
        $numberTransformer = $numberToWords->getNumberTransformer('en');

        $net_salary = number_format((float) str_replace(',', '', $processedSalary->ps_monthly_net_salary), 2, '.', '');
        $parts = explode('.', $net_salary);
        $integerPart = (int) $parts[0];
        $decimalPart = isset($parts[1]) ? (int) $parts[1] : 0;

        $net_salary_words = ucfirst($numberTransformer->toWords($integerPart)).' rupees';
        if ($decimalPart > 0) {
            $net_salary_words .= ' and '.$numberTransformer->toWords($decimalPart).' paise';
        }
        $net_salary_words .= ' only';

        $data = [
            'employee' => $employee,
            'processedSalary' => $processedSalary,
            'employee_name' => $employee->emp_full_name,
            'monthly_salary' => $processedSalary->ps_worked_days_salary,
            'basic_salary' => $processedSalary->ps_basic_salary,
            'net_salary' => $processedSalary->ps_monthly_net_salary,
            'earnings' => $processedSalary->ps_earnings,
            'employee_deductions' => $processedSalary->ps_employee_deductions,
            'employer_deductions' => $processedSalary->ps_employer_deductions,
            'net_salary_words' => $net_salary_words,
            'logoPath' => $logoPath,
            'payroll_period' => $payrollPeriod,
        ];

        $pdf = Pdf::loadView('admin.employees.salary.monthly_salary_template_two', $data);

        $employeeName = str_replace(' ', '', $employee->emp_full_name);

        return $pdf->stream("Payslip-{$employeeName}.pdf");
    }

    private function generatePayslip($processedSalary, $payrollPeriod)
    {
        $employee = Employee::join('departments', 'departments.d_id', '=', 'employees.emp_d_id')
            ->join('designations', 'designations.dg_id', '=', 'employees.emp_dg_id')
            ->join('businesses', 'businesses.b_id', '=', 'employees.emp_b_id')
            ->join('branches', 'branches.br_id', '=', 'employees.emp_br_id')
            ->findOrFail($processedSalary->ps_emp_id);
        $business_id = $employee->emp_b_id;

        $logoPath = null;

        if ($employee->b_logo) {
            $logoPath = $employee->b_logo;
        } else {
            $logoPath = null;
        }

        $numberToWords = new NumberToWords;
        $numberTransformer = $numberToWords->getNumberTransformer('en');
        $net_salary = $processedSalary->ps_monthly_net_salary;

        $net_salary = number_format((float) str_replace(',', '', $net_salary), 2, '.', '');
        $parts = explode('.', $net_salary);
        $integerPart = (int) $parts[0];
        $decimalPart = isset($parts[1]) ? (int) $parts[1] : 0;

        // Convert integer part
        $net_salary_words = ucfirst($numberTransformer->toWords($integerPart)).' rupees';

        // Convert decimal part if exists
        if ($decimalPart > 0) {
            $net_salary_words .= ' and '.$numberTransformer->toWords($decimalPart).' paise';
        }

        $net_salary_words .= ' only';

        $data = [
            'employee' => $employee,
            'processedSalary' => $processedSalary,
            'employee_name' => $employee->emp_full_name,
            'monthly_salary' => $processedSalary->ps_worked_days_salary,
            'basic_salary' => $processedSalary->ps_basic_salary,
            'net_salary' => $processedSalary->ps_monthly_net_salary,
            'earnings' => $processedSalary->ps_earnings,
            'employee_deductions' => $processedSalary->ps_employee_deductions,
            'employer_deductions' => $processedSalary->ps_employer_deductions,
            'net_salary_words' => $net_salary_words,
            'logoPath' => $logoPath,
            'payroll_period' => $payrollPeriod,
        ];
        // dd($data);

        $pdf = Pdf::loadView('admin.employees.salary.monthly_salary_template', $data);

        $employeeName = str_replace(' ', '', $employee->emp_full_name);

        // Get Month-Year from Payroll Date
        // $monthYear = date('m-Y', strtotime($processedSalary->ps_payroll_date));

        // Define File Name & Path
        $pdfFileName = "Payslip-{$employeeName}.pdf";
        $pdfFolderPath = public_path('payslips'); // "public/payslips" folder path

        // Ensure the directory exists
        if (! file_exists($pdfFolderPath)) {
            mkdir($pdfFolderPath, 0777, true);
        }

        // Save the PDF in the specified path
        $pdf->save("{$pdfFolderPath}/{$pdfFileName}");

        // Generate the URL
        $payslipUrl = asset("payslips/{$pdfFileName}");

        return $payslipUrl;
    }

    public static function getWeekOffInRange($startDate, $endDate)
    {
        $startDate = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);
        $weekOffDates = [];

        while ($startDate->lte($endDate)) {
            if ($startDate->isSunday()) {
                $weekOffDates[] = $startDate->toDateString();
            }
            $startDate->addDay();
        }

        return $weekOffDates;
    }

    public function attendancelist()
    {
        $title = 'Attendance Vault';
        $columns = [
            'S. No.',
            'Financial Year',
            'Month Name',
            'Period Name',
            'Attendence Start Date',
            'Attendence End Date',
            'Status',
            'Action',
        ];
        $financialYears = FinancialYear::orderBy('fy_id', 'desc')->select('fy_id', 'fy_year')->get();
        $months = MasterTable::where('m_group', 'MONTH')->get();

        return view('admin.payroll.freeze-attendance', compact('title', 'columns', 'financialYears', 'months'));
    }

    public function getMonthsForFinancialYear($fy_id)
    {
        try {
            $fy = FinancialYear::findOrFail($fy_id);

            $start = \Carbon\Carbon::parse($fy->fy_start_date)->startOfMonth();
            $end = \Carbon\Carbon::parse($fy->fy_end_date)->endOfMonth();

            // Fetch all months from MasterTable
            $allMonths = MasterTable::where('m_group', 'MONTH')->get();

            $months = [];

            while ($start <= $end) {
                $monthNameText = $start->format('F'); // "January", "February", etc.
                $year = $start->format('Y'); // "2025", "2026", etc.

                // Match by m_name (e.g., "November")
                $month = $allMonths->firstWhere('m_name', $monthNameText);

                if ($month) {
                    $months[] = [
                        'value' => $month->m_id, // or "$month->m_id-$year" if needed
                        'label' => "{$month->m_name} $year",
                    ];
                }

                $start->addMonth();
            }

            return response()->json($months);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load months',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getPayslipDate($id)
    {
        $period = PayrollPeriod::findOrFail($id);

        return response()->json([
            'status' => true,
            'pp_id' => $period->pp_id,
            'date' => $period->pp_payslip_date
                ? \Carbon\Carbon::parse($period->pp_payslip_date)->format('Y-m-d')
                : null,
        ]);
    }

    public function updatePayslipDate(Request $request)
    {
        $request->validate([
            'pp_id' => 'required|integer|exists:payroll_periods,pp_id',
            'payslip_online_date' => 'required|date',
        ]);

        $payroll = PayrollPeriod::findOrFail($request->pp_id);
        $payroll->pp_payslip_date = $request->payslip_online_date;
        $payroll->save();

        return response()->json([
            'status' => true,
            'message' => 'Payslip online date updated successfully.',
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
