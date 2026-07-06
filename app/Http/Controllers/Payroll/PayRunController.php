<?php

namespace App\Http\Controllers\Payroll;

use App\Helpers\CentralLogics;
use App\Helpers\PayrollLogics;
use App\Helpers\RolePermissionLogics;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Payroll\PayrollPeriodController;
use App\Models\AdhocTransaction;
use App\Models\AttendanceException;
use App\Models\AttendanceSummary;
use App\Models\Business;
use App\Models\Employee;
use App\Models\FinancialYear;
use App\Models\LeaveRequest;
use App\Models\MasterTable;
use App\Models\OvertimePolicy;
use App\Models\PayrollMasterSetting;
use App\Models\PayrollPeriod;
use App\Models\PayslipConfiguration;
use App\Models\PolicyHolidayList;
use App\Models\PolicyWeekOff;
use App\Models\ProcessedEmployeeSalary;
use App\Models\ProcessedSalaryDeduction;
use App\Models\ProcessedSalaryEarning;
use App\Models\SalaryAllowance;
use App\Models\SalaryEmployeeDeductions;
use App\Models\SalaryEmployeeEarnings;
use App\Models\SalaryEmployeeSalary;
use App\Models\SalaryEmployerDeductions;
use App\Models\SalaryHold;
use App\Models\SalaryMasterHistory;
use App\Models\SalaryPolicySalary;
use App\Models\StatutoryDeduction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use MehediJaman\LaravelZkteco\Lib\Attendance;
use NumberToWords\NumberToWords;

class PayRunController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function cyclesIndex(Request $request)
    {
        $business_id = auth()->user()->emp_b_id;

        // ✅ Get payroll settings first to check payment cycle
        $payrollSettings = \App\Models\PayrollMasterSetting::where('pms_b_id', $business_id)->first();

        // ✅ Check if payroll cycle is 441 (Weekly)
        if ($payrollSettings && $payrollSettings->pms_payroll_cycle == 441) {
            // Redirect to weekly payroll cycles view
            return redirect()->route('payroll.cycles.weekly');
            // OR if you want to return a different view directly:
            // return view('admin.payroll.payrun.weekly-payroll-cycles', compact('payrollSettings'));
        }

        // ✅ Get financial years
        $financialYears = FinancialYear::orderBy('fy_id', 'desc')
            ->where('fy_b_id', $business_id)
            ->select('fy_id', 'fy_year', 'fy_is_current')
            ->get();

        // ✅ Get current financial year
        $selectedFY = $request->get('fy_id');
        $currentFY = null;

        if ($selectedFY) {
            $currentFY = FinancialYear::where('fy_b_id', $business_id)
                ->where('fy_id', $selectedFY)
                ->first();
        } else {
            $currentFY = FinancialYear::where('fy_b_id', $business_id)
                ->where('fy_is_current', 1)
                ->first();

            if (! $currentFY && $financialYears->isNotEmpty()) {
                $currentFY = $financialYears->first();
            }
        }

        // ✅ Get payroll settings
        $payrollSettings = \App\Models\PayrollMasterSetting::where('pms_b_id', $business_id)->first();

        $paymentCycle = null;
        if ($payrollSettings && $payrollSettings->pms_payroll_cycle) {
            $paymentCycle = MasterTable::where('m_group', '=', 'PAYMENT_CYCLE')
                ->where('m_id', $payrollSettings->pms_payroll_cycle)
                ->select('m_id', 'm_name')
                ->first();
        }

        // ✅ STATUS UI CONFIG
        $statusConfig = [
            'upcoming' => [
                'color' => 'info',
                'icon' => 'clock',
                'label' => 'Upcoming',
                'progress' => 0,
            ],
            'open' => [
                'color' => 'primary',
                'icon' => 'unlock',
                'label' => 'Open',
                'progress' => 10,
            ],
            'attendance_frozen' => [
                'color' => 'warning',
                'icon' => 'calendar-lock',
                'label' => 'Attendance Frozen',
                'progress' => 20,
            ],
            'processing' => [
                'color' => 'primary',
                'icon' => 'refresh-cw',
                'label' => 'Processing',
                'progress' => 30,
            ],
            'SALARY_PROCESSING' => [  // ये आपका actual status है
                'color' => 'primary',
                'icon' => 'refresh-cw',
                'label' => 'Salary Processing',
                'progress' => 35,
            ],
            'salary_processing' => [  // lowercase version
                'color' => 'primary',
                'icon' => 'refresh-cw',
                'label' => 'Salary Processing',
                'progress' => 35,
            ],
            'pending_approval' => [
                'color' => 'warning',
                'icon' => 'clock',
                'label' => 'Pending Approval',
                'progress' => 40,
            ],
            'under_review' => [
                'color' => 'warning',
                'icon' => 'eye',
                'label' => 'Under Review',
                'progress' => 50,
            ],
            'IN-PROCESS' => [  // Note: All caps with hyphen
                'color' => 'info',
                'icon' => 'cog',
                'label' => 'In Process',
                'progress' => 65,
            ],
            'verification' => [
                'color' => 'info',
                'icon' => 'check-circle',
                'label' => 'Verification',
                'progress' => 80,
            ],
            'finalized_locked' => [
                'color' => 'success',
                'icon' => 'lock',
                'label' => 'Finalized & Locked',
                'progress' => 100,
            ],
            'expired' => [
                'color' => 'secondary',
                'icon' => 'calendar-x',
                'label' => 'Expired',
                'progress' => 0,
            ],
        ];

        $cycles = collect(); // Always initialize as collection

        if ($currentFY) {
            try {
                // ✅ Get payroll periods
                $payrollPeriods = PayrollPeriod::with([
                    'month:m_id,m_name',
                    'financialYear:fy_id,fy_year',
                ])
                    ->where('pp_b_id', $business_id)
                    ->where('pp_fy_id', $currentFY->fy_id)
                    ->orderBy('pp_month_id', 'asc')
                    ->get();

                // ✅ Get period IDs for batch queries
                $periodIds = $payrollPeriods->pluck('pp_id')->toArray();

                // ✅ Initialize empty arrays for counts
                $employeeCounts = [];
                $holdCounts = [];

                if (! empty($periodIds)) {
                    // ✅ Get employee counts in ONE query
                    $employeeCounts = DB::table('attendance_summaries')
                        ->select('as_pp_id', DB::raw('COUNT(DISTINCT as_emp_id) as employee_count'))
                        ->whereIn('as_pp_id', $periodIds)
                        ->groupBy('as_pp_id')
                        ->get()
                        ->keyBy('as_pp_id');

                    // ✅ Get salary hold counts in ONE query
                    $holdCounts = DB::table('salary_holds')
                        ->select('sh_pp_id', DB::raw('COUNT(*) as hold_count'))
                        ->whereIn('sh_pp_id', $periodIds)
                        ->where('sh_status', 'held')
                        ->groupBy('sh_pp_id')
                        ->get()
                        ->keyBy('sh_pp_id');
                }

                // ✅ Process payroll periods
                $cycles = $payrollPeriods->map(function ($pp) use ($statusConfig, $employeeCounts, $holdCounts) {
                    $status = $pp->pp_status_code ?? 'upcoming';
                    $statusKey = Str::snake(strtolower($status));

                    if ($status === 'IN-PROCESS') {
                        $statusKey = 'IN-PROCESS';
                    }

                    // Map old statuses to new statuses
                    $statusMapping = [
                        'payroll_locked' => 'finalized_locked',
                        'completed' => 'finalized_locked',
                        'PAYROLL_LOCKED' => 'finalized_locked',
                    ];

                    $statusKey = $statusMapping[$statusKey] ?? $statusKey;
                    $ui = $statusConfig[$statusKey] ?? $statusConfig['upcoming'];

                    // ✅ Get employee count
                    $employeeCount = 0;
                    if (isset($employeeCounts[$pp->pp_id]) && is_object($employeeCounts[$pp->pp_id])) {
                        $employeeCount = $employeeCounts[$pp->pp_id]->employee_count ?? 0;
                    }

                    // ✅ Get salary hold count
                    $holdCount = 0;
                    if (isset($holdCounts[$pp->pp_id]) && is_object($holdCounts[$pp->pp_id])) {
                        $holdCount = $holdCounts[$pp->pp_id]->hold_count ?? 0;
                    }

                    $hasHolds = $holdCount > 0;

                    // Get month name
                    $monthName = $pp->month->m_name ?? '-';
                    $year = $pp->financialYear->fy_year ?? '';

                    return [
                        'id' => $pp->pp_id,
                        'month' => $monthName,
                        'year' => $year,
                        'start' => \Carbon\Carbon::parse($pp->pp_start_date)->format('d M Y'),
                        'end' => \Carbon\Carbon::parse($pp->pp_end_date)->format('d M Y'),
                        'employees' => (int) $employeeCount,
                        'hold_count' => (int) $holdCount,
                        'has_holds' => $hasHolds,
                        'status' => $status,
                        'ui' => $ui,
                        'progress' => (int) $ui['progress'],
                        'pp_is_finalized' => $pp->pp_is_finalized ?? 0,
                        'cheque_number' => $pp->cheque_number ?? null,
                    ];
                });

            } catch (\Exception $e) {
                \Log::error('Error loading payroll cycles: '.$e->getMessage());
                // Keep $cycles as empty collection if there's an error
                $cycles = collect();
            }
        }

        // ✅ Ensure $cycles is always a countable collection
        if (! ($cycles instanceof \Illuminate\Support\Collection)) {
            $cycles = collect($cycles);
        }

        return view('admin.payroll.payrun.payroll-cycles', compact(
            'cycles',
            'currentFY',
            'financialYears',
            'paymentCycle'
        ));
    }

    public function getHoldDetails($periodId)
    {
        try {
            $holds = SalaryHold::with([
                'employee:emp_id,emp_code,emp_fname,emp_mname,emp_lname,emp_d_id,emp_dg_id',
                'employee.fh_department:d_id,d_name',
                'employee.fh_designation:dg_id,dg_name',
                'heldBy:emp_id,emp_fname,emp_lname',
            ])
                ->where('sh_pp_id', $periodId)
                ->where('sh_status', 'held')
                ->orderByDesc('sh_held_at')
                ->get()
                ->map(function ($hold) {

                    $employee = $hold->employee;

                    return [
                        'employee_id' => $hold->sh_emp_id,
                        'employee_code' => $employee->emp_code ?? 'N/A',

                        'employee_name' => trim(
                            ($employee->emp_fname ?? '').' '.
                            ($employee->emp_mname ?? '').' '.
                            ($employee->emp_lname ?? '')
                        ) ?: 'Unknown',

                        'department' => $employee->fh_department->d_name ?? 'N/A',
                        'designation' => $employee->fh_designation->dg_name ?? 'N/A',

                        'reason' => $hold->sh_reason,
                        'hold_until_release' => $hold->sh_hold_until_release ? 'Yes' : 'No',

                        'held_by' => $hold->heldBy
                            ? trim($hold->heldBy->emp_fname.' '.$hold->heldBy->emp_lname)
                            : 'System',

                        'held_at' => optional($hold->sh_held_at)->format('d-m-Y H:i'),
                    ];
                });

            $payrollPeriod = PayrollPeriod::find($periodId);

            return response()->json([
                'success' => true,
                'count' => $holds->count(),
                'holds' => $holds,
                'payrollPeriod' => $payrollPeriod,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to load hold details',
                'error' => $e->getMessage(), // remove in production
            ], 500);
        }
    }

    public function updateOrCreatePayrollPeriod(Request $request)
    {
        // Check if it's an AJAX request
        if ($request->ajax() || $request->expectsJson()) {
            \Log::info('AJAX Request received for Payroll Period:', $request->all());

            // Validation rules
            $validator = Validator::make($request->all(), [
                'year' => 'required|integer|exists:financial_years,fy_id',
                'month' => 'required|integer|exists:master_table,m_id',
                'payroll_type' => 'required|integer|exists:master_table,m_id',
                'name' => 'required|string|max:255',
                'attendance_start_date' => 'required|date',
                'attendance_end_date' => 'required|date|after_or_equal:attendance_start_date',
                'date_of_payment' => 'required|date|after_or_equal:attendance_end_date',
                'payslip_online_date' => 'required|date|after_or_equal:attendance_end_date',
                'cheque_number' => 'nullable|string|max:100',
                'description' => 'nullable|string',
                'payroll_quarter' => 'nullable|integer|exists:master_table,m_id',
            ], [
                'month.exists' => 'Selected month is invalid.',
                'payroll_type.exists' => 'Selected payroll type is invalid.',
                'year.exists' => 'Selected financial year is invalid.',
                'payroll_quarter.exists' => 'Selected quarter is invalid.',
            ]);

            if ($validator->fails()) {
                \Log::error('Validation failed:', $validator->errors()->toArray());

                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $businessId = auth()->user()->emp_b_id;
            $year = $request->year;
            $month = $request->month;
            $startDate = $request->attendance_start_date;
            $endDate = $request->attendance_end_date;

            // Check for overlapping periods (same business, same FY, same month)
            $exists = PayrollPeriod::where('pp_b_id', $businessId)
                ->where('pp_fy_id', $year)
                ->where('pp_month_id', $month)
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->where(function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('pp_start_date', [$startDate, $endDate])
                            ->orWhereBetween('pp_end_date', [$startDate, $endDate]);
                    })
                        ->orWhere(function ($query) use ($startDate, $endDate) {
                            $query->where('pp_start_date', '<=', $startDate)
                                ->where('pp_end_date', '>=', $endDate);
                        });
                });

            // If editing, exclude current ID
            if ($request->filled('pp_id')) {
                $exists->where('pp_id', '!=', $request->pp_id);
            }

            if ($exists->exists()) {
                \Log::warning('Duplicate payroll period detected', [
                    'business_id' => $businessId,
                    'year' => $year,
                    'month' => $month,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'A payroll period already exists for this month and date range.',
                ], 409);
            }

            DB::beginTransaction();

            try {
                // Create or update payroll period
                if ($request->filled('pp_id')) {
                    $payrollPeriod = PayrollPeriod::find($request->pp_id);
                    if (! $payrollPeriod) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Payroll period not found.',
                        ], 404);
                    }
                    \Log::info('Updating existing payroll period:', ['pp_id' => $payrollPeriod->pp_id]);
                } else {
                    $payrollPeriod = new PayrollPeriod;
                    $payrollPeriod->pp_b_id = $businessId;
                    $payrollPeriod->pp_fy_id = $year;
                    $payrollPeriod->pp_month_id = $month;
                    \Log::info('Creating new payroll period');
                }

                // Calculate quarter ID based on month
                $quarterId = $this->calculateQuarterId($month);

                // Fill all fields according to model
                $payrollPeriod->pp_type_id = $request->payroll_type;
                $payrollPeriod->pp_name = $request->name;
                $payrollPeriod->pp_seq_no = 1;
                $payrollPeriod->pp_start_date = $startDate;
                $payrollPeriod->pp_end_date = $endDate;
                $payrollPeriod->pp_cheque_no = $request->cheque_number;
                $payrollPeriod->pp_description = $request->description;
                $payrollPeriod->pp_payment_date = $request->date_of_payment;
                $payrollPeriod->pp_payslip_date = $request->payslip_online_date;
                $payrollPeriod->pp_is_active = 1;
                $payrollPeriod->pp_quarter_id = $request->payroll_quarter ?? $quarterId;

                // Store cheque number - या तो PayrollPeriod model में field add करें या business table में save करें
                // यहाँ मैं business table में save कर रहा हूँ
                if ($request->filled('cheque_number')) {
                    // Update business table
                    \App\Models\Business::where('b_id', $businessId)
                        ->update(['b_cheque_no' => $request->cheque_number]);
                }

                // Default status and flags
                $payrollPeriod->pp_status_code = 'processing';
                $payrollPeriod->pp_is_freezed = 0;
                $payrollPeriod->pp_is_processed = 0;
                $payrollPeriod->pp_is_finalized = 0;
                $payrollPeriod->pp_process_id = null;

                // Save the payroll period
                $payrollPeriod->save();

                \Log::info('Payroll period saved successfully:', [
                    'pp_id' => $payrollPeriod->pp_id,
                    'pp_name' => $payrollPeriod->pp_name,
                    'pp_quarter_id' => $payrollPeriod->pp_quarter_id,
                    'cheque_number' => $request->cheque_number ?? 'Not provided',
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Payroll cycle '.($request->filled('pp_id') ? 'updated' : 'created').' successfully!',
                    'data' => [
                        'id' => $payrollPeriod->pp_id,
                        'name' => $payrollPeriod->pp_name,
                        'start_date' => $payrollPeriod->pp_start_date->format('d M Y'),
                        'end_date' => $payrollPeriod->pp_end_date->format('d M Y'),
                        'status' => $payrollPeriod->pp_status_code,
                        'quarter_id' => $payrollPeriod->pp_quarter_id,
                        'cheque_number' => $request->cheque_number ?? null,
                    ],
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Payroll period creation/update error: '.$e->getMessage());
                \Log::error('Error trace: ', $e->getTrace());

                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while saving the payroll cycle. Error: '.$e->getMessage(),
                ], 500);
            }
        }

        // For non-AJAX requests
        \Log::warning('Non-AJAX request to payroll period endpoint');

        return response()->json([
            'success' => false,
            'message' => 'Invalid request type. Please use AJAX.',
        ], 400);
    }

    private function calculateQuarterId($monthId)
    {
        try {
            // Get month from master table
            $month = MasterTable::where('m_id', $monthId)
                ->where('m_group', 'MONTH')
                ->first();

            if (! $month) {
                return null;
            }

            $monthName = strtolower($month->m_name);

            // Map month names to numbers
            $monthMap = [
                'january' => 1, 'february' => 2, 'march' => 3,
                'april' => 4, 'may' => 5, 'june' => 6,
                'july' => 7, 'august' => 8, 'september' => 9,
                'october' => 10, 'november' => 11, 'december' => 12,
            ];

            $monthNumber = $monthMap[$monthName] ?? 1;

            // Determine quarter number based on Indian financial year
            // Q1: April-June (4-6)
            // Q2: July-September (7-9)
            // Q3: October-December (10-12)
            // Q4: January-March (1-3)

            $quarterNumber = 1;
            if ($monthNumber >= 4 && $monthNumber <= 6) {
                $quarterNumber = 1; // Q1: April-June
            } elseif ($monthNumber >= 7 && $monthNumber <= 9) {
                $quarterNumber = 2; // Q2: July-September
            } elseif ($monthNumber >= 10 && $monthNumber <= 12) {
                $quarterNumber = 3; // Q3: October-December
            } else {
                $quarterNumber = 4; // Q4: January-March
            }

            // Find quarter in master table (444: Q1, 445: Q2, 446: Q3, 447: Q4)
            $quarter = MasterTable::where('m_group', 'PAYROLL_QUARTER')
                ->where('m_name', 'Q'.$quarterNumber)
                ->first();

            \Log::info('Quarter calculated:', [
                'month_id' => $monthId,
                'month_name' => $monthName,
                'month_number' => $monthNumber,
                'quarter_number' => $quarterNumber,
                'quarter_id' => $quarter ? $quarter->m_id : null,
            ]);

            return $quarter ? $quarter->m_id : null;

        } catch (\Exception $e) {
            \Log::error('Error calculating quarter: '.$e->getMessage());

            return null;
        }
    }

    // Helper method to get quarter from quarter master table
    private function getQuarterFromMaster($quarterNumber)
    {
        try {
            $quarter = \App\Models\MasterTable::where('m_group', 'PAYROLL_QUARTER')
                ->where('m_name', 'like', "%Q{$quarterNumber}%")
                ->orWhere('m_value', $quarterNumber)
                ->first();

            return $quarter ? $quarter->m_id : null;
        } catch (\Exception $e) {
            \Log::error('Error getting quarter from master: '.$e->getMessage());

            return null;
        }
    }

    // Also add this method for getting months
    public function getMonths($fyId)
    {
        try {
            $financialYear = FinancialYear::find($fyId);
            if (! $financialYear) {
                return response()->json([]);
            }

            // Parse year from financial year string (e.g., "2024-2025")
            $year = intval(explode('-', $financialYear->fy_year)[0]);

            $months = [];
            for ($i = 0; $i < 12; $i++) {
                $date = \Carbon\Carbon::create($year, $i + 1, 1);
                $months[] = [
                    'value' => $i + 1,
                    'label' => $date->format('F Y'),
                ];
            }

            return response()->json($months);
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }

    public function empPayrollDetails(Request $request)
    {
        try {
            $businessId = auth()->user()->emp_b_id;
            $employeeId = $request->employee_id;
            $payrollId = $request->payroll_id;
            $periodId = $request->period_id;

            // Get employee details
            $employee = Employee::with(['fh_department', 'fh_designation', 'fh_branch', 'fh_business.fh_currency'])
                ->where('emp_id', $employeeId)
                ->where('emp_b_id', $businessId)
                ->first();

            if (! $employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found',
                ], 404);
            }

            // Get payroll period
            $payrollPeriod = PayrollPeriod::where('pp_id', $payrollId)
                ->where('pp_b_id', $businessId)
                ->first();

            if (! $payrollPeriod) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payroll period not found',
                ], 404);
            }

            // Get employee salary
            $salary = SalaryEmployeeSalary::where('es_emp_id', $employeeId)->where('es_b_id', $businessId)->first();
            if (! $salary) {
                $salary = SalaryEmployeeSalary::where('es_emp_id', $employeeId)->first();
            }

            if (! $salary) {
                return response()->json([
                    'success' => false,
                    'message' => 'Salary configuration not found',
                ], 404);
            }

            // Get attendance summary
            $attendance = AttendanceSummary::where('as_emp_id', $employeeId)
                ->where('as_pp_id', $payrollId)
                ->where('as_b_id', $businessId)
                ->first();

            if (! $attendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance not found',
                ], 404);
            }

            // Calculate payroll period days
            $startDate = Carbon::parse($payrollPeriod->pp_start_date);
            $endDate = Carbon::parse($payrollPeriod->pp_end_date);
            $totalMonthDays = $startDate->diffInDays($endDate) + 1;

            // Get weekoff days
            $weekoffDays = $attendance->as_total_weekoff;

            // Calculate salary days (present + 0.5 of half days)
            $presentDays = $attendance->as_total_present;
            $halfDays = $attendance->as_total_half_day;
            $salaryDays = $attendance->as_total_worked_days;

            // Calculate worked percentage
            $workedPct = $totalMonthDays > 0 ? ($salaryDays / $totalMonthDays) * 100 : 0;

            // Get base salary
            $baseSalary = floatval($salary->es_base_salary);

            // Calculate prorated base salary
            $proratedBase = round($baseSalary * ($workedPct / 100), 2);

            // Get adhoc data
            $adhocTransactions = AdhocTransaction::with(['transaction_details.component'])
                ->where('at_pp_id', $payrollId)
                ->where('at_emp_id', $employeeId)
                ->where('at_b_id', $businessId)
                ->get();

            $adhocEarnings = 0;
            $adhocDeductions = 0;
            $adhocDetails = [];

            foreach ($adhocTransactions as $transaction) {
                foreach ($transaction->transaction_details as $detail) {
                    $earningAmount = floatval($detail->earning_amount ?? 0);
                    $deductionAmount = floatval($detail->deduction_amount ?? 0);
                    $netAmount = $earningAmount - $deductionAmount;

                    $adhocEarnings += $earningAmount;
                    $adhocDeductions += $deductionAmount;

                    $adhocDetails[] = [
                        'component_name' => $detail->component->ac_adhoc_component_name ?? 'Adjustment',
                        'net_amount' => $netAmount,
                        'is_earning' => $netAmount > 0,
                        'remarks' => $detail->remarks ?? '',
                        'created_at' => $detail->created_at ?? null,
                    ];
                }
            }

            // Check if already processed
            $processedSalary = ProcessedEmployeeSalary::where('ps_emp_id', $employeeId)
                ->where('ps_payroll_id', $payrollId)
                ->where('ps_b_id', $businessId)
                ->first();

            $isProcessed = ! empty($processedSalary);

            // Get processed salary details if exists
            $earningsBreakdown = [];
            $deductionsBreakdown = [];
            $netPayable = 0;
            $totalEarnings = 0;
            $totalDeductions = 0;
            $payslipUrl = null;
            $processedDate = null;

            if ($isProcessed) {
                // Get earnings breakdown
                $processedEarnings = ProcessedSalaryEarning::where('ps_id', $processedSalary->ps_id)
                    ->get();

                foreach ($processedEarnings as $earning) {
                    $earningsBreakdown[] = [
                        'type' => $earning->ps_earning_type,
                        'amount' => floatval($earning->ps_e_amount),
                    ];
                    $totalEarnings += floatval($earning->ps_e_amount);
                }

                // Get deductions breakdown
                $processedDeductions = ProcessedSalaryDeduction::where('ps_id', $processedSalary->ps_id)
                    ->get();

                foreach ($processedDeductions as $deduction) {
                    $deductionsBreakdown[] = [
                        'type' => $deduction->ps_deduction_type,
                        'amount' => floatval($deduction->ps_d_amount),
                        'category' => $deduction->ps_d_category,
                    ];
                    if ($deduction->ps_d_category === 'employee') {
                        $totalDeductions += floatval($deduction->ps_d_amount);
                    }
                }

                $netPayable = $processedSalary->ps_monthly_net_salary;
                $payslipUrl = $processedSalary->ps_payslip_url;
                $processedDate = $processedSalary->created_at;
            } else {
                // Same engine as salary process: gross includes prorated components, Other Allowance (364), Remaining, etc.
                $salaryCalc = (int) $payrollPeriod->pp_type_id === 440
                    ? PayrollLogics::calculateMonthlySalary($employee, $startDate, $endDate, $salary, $businessId, $payrollId)
                    : PayrollLogics::calculateDailySalary($employee, $startDate, $endDate, $salary, $businessId, $payrollId);

                if ($salaryCalc instanceof \Illuminate\Http\JsonResponse) {
                    return $salaryCalc;
                }

                $totalEarnings = (float) str_replace(',', '', (string) ($salaryCalc['gross_salary'] ?? 0));
                $netPayable = (float) str_replace(',', '', (string) ($salaryCalc['net_salary'] ?? 0));
                $totalDeductions = (float) str_replace(',', '', (string) ($salaryCalc['total_employee_deductions'] ?? 0));

                $earningsBreakdown = [];
                foreach ($salaryCalc['earnings_breakdown'] ?? [] as $row) {
                    if (! is_array($row) || ! isset($row['earning_type'])) {
                        continue;
                    }
                    $earningsBreakdown[] = [
                        'type' => $row['earning_type'],
                        'amount' => (float) str_replace(',', '', (string) ($row['amount'] ?? 0)),
                    ];
                }

                if ($earningsBreakdown === []) {
                    $earningsBreakdown[] = ['type' => 'Gross earnings', 'amount' => $totalEarnings];
                }
            }

            // Check if held
            $isHeld = SalaryHold::where('sh_emp_id', $employeeId)
                ->where('sh_pp_id', $payrollId)
                ->where('sh_b_id', $businessId)
                ->where('sh_status', 'held')
                ->exists();

            return response()->json([
                'success' => true,
                'data' => [
                    'employee' => [
                        'id' => $employee->emp_id,
                        'code' => $employee->emp_code,
                        'name' => $employee->emp_full_name,
                        'department' => $employee->fh_department->d_name ?? 'N/A',
                        'designation' => $employee->fh_designation->dg_name ?? 'N/A',
                        'branch' => $employee->fh_branch->br_name ?? 'N/A',
                        'currency' => $employee->fh_business->fh_currency->c_code ?? 'INR',
                    ],

                    'payroll_period' => [
                        'id' => $payrollPeriod->pp_id,
                        'name' => $payrollPeriod->pp_name ?? 'N/A',
                        'start_date' => $payrollPeriod->pp_start_date,
                        'end_date' => $payrollPeriod->pp_end_date,
                        'month_days' => $totalMonthDays,
                    ],

                    'attendance' => [
                        'month_days' => $totalMonthDays,
                        'worked_days' => round($salaryDays, 1),
                        'salary_days' => round($salaryDays, 1),
                        'present_days' => $presentDays,
                        'half_days' => $halfDays,
                        'leave_days' => $attendance->as_total_leave,
                        'absent_days' => $attendance->as_total_absent,
                        'weekoff_days' => $weekoffDays,
                        'worked_percentage' => round($workedPct, 2),
                        'late_days' => $attendance->as_days_late,
                        'early_exit_days' => $attendance->as_early_exit,
                        'upl_days' => $attendance->as_total_upl_count,
                    ],

                    'salary' => [
                        'base_salary' => $baseSalary,
                        'prorated_base_salary' => $proratedBase,
                        'per_day_salary' => $totalMonthDays > 0 ? round($baseSalary / $totalMonthDays, 2) : 0,
                    ],

                    'adhoc_adjustments' => [
                        'count' => count($adhocDetails),
                        'total_earnings' => $adhocEarnings,
                        'total_deductions' => $adhocDeductions,
                        'net_amount' => $adhocEarnings - $adhocDeductions,
                        'details' => $adhocDetails,
                    ],

                    'calculated_summary' => [
                        'total_earnings' => $totalEarnings,
                        'total_deductions' => $totalDeductions,
                        'net_payable' => $netPayable,
                    ],

                    'earnings_breakdown' => $earningsBreakdown,
                    'deductions_breakdown' => $deductionsBreakdown,

                    'processed_salary' => $isProcessed ? [
                        'processed_date' => $processedDate,
                        'payslip_url' => $payslipUrl,
                        'basic_salary' => $processedSalary->ps_basic_salary ?? 0,
                        'earnings' => $processedSalary->ps_earnings ?? 0,
                        'employee_deductions' => $processedSalary->ps_employee_deductions ?? 0,
                        'employer_deductions' => $processedSalary->ps_employer_deductions ?? 0,
                        'gross_salary' => $processedSalary->ps_monthly_gross ?? 0,
                        'net_salary' => $processedSalary->ps_monthly_net_salary ?? 0,
                    ] : null,

                    'status' => [
                        'is_processed' => $isProcessed,
                        'is_held' => $isHeld,
                        'payslip_available' => ! empty($payslipUrl),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Employee payroll details error', [
                'error' => $e->getMessage(),
                'employee_id' => $request->employee_id,
                'payroll_id' => $request->payroll_id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load employee details: '.$e->getMessage(),
            ], 500);
        }
    }

    // Preview employee salary (without processing)
    public function getEmployeeSalaryPreview(Request $request)
    {
        try {
            $businessId = auth()->user()->emp_b_id;
            $employeeId = $request->employee_id;
            $payrollId = $request->payroll_id;

            // Get employee details
            $employee = Employee::with(['fh_department', 'fh_designation'])
                ->where('emp_id', $employeeId)
                ->where('emp_b_id', $businessId)
                ->first();

            if (! $employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found',
                ], 404);
            }

            // Get payroll period
            $payrollPeriod = PayrollPeriod::where('pp_id', $payrollId)
                ->where('pp_b_id', $businessId)
                ->first();

            if (! $payrollPeriod) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payroll period not found',
                ], 404);
            }

            // Get employee salary
            $salary = SalaryEmployeeSalary::where('es_emp_id', $employeeId)->where('es_b_id', $businessId)->first();
            if (! $salary) {
                $salary = SalaryEmployeeSalary::where('es_emp_id', $employeeId)->first();
            }

            if (! $salary) {
                return response()->json([
                    'success' => false,
                    'message' => 'Salary configuration not found',
                ], 404);
            }

            // Get attendance summary
            $attendance = AttendanceSummary::where('as_emp_id', $employeeId)
                ->where('as_pp_id', $payrollId)
                ->where('as_b_id', $businessId)
                ->first();

            if (! $attendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance not found',
                ], 404);
            }

            // Calculate payroll period days
            $startDate = Carbon::parse($payrollPeriod->pp_start_date);
            $endDate = Carbon::parse($payrollPeriod->pp_end_date);
            $totalMonthDays = $startDate->diffInDays($endDate) + 1;

            // Get weekoff days
            $weekoffDays = $attendance->as_total_weekoff;

            // Calculate salary days (present + 0.5 of half days)
            $presentDays = $attendance->as_total_present;
            $halfDays = $attendance->as_total_half_day;
            $salaryDays = $attendance->as_total_worked_days;

            // Calculate worked percentage
            $workedPct = $totalMonthDays > 0 ? ($salaryDays / $totalMonthDays) * 100 : 0;

            // Get base salary
            $baseSalary = floatval($salary->es_base_salary);

            // Calculate prorated base salary
            $proratedBase = round($baseSalary * ($workedPct / 100), 2);

            // Get adhoc data
            $adhocTransactions = AdhocTransaction::with(['transaction_details.component'])
                ->where('at_pp_id', $payrollId)
                ->where('at_emp_id', $employeeId)
                ->where('at_b_id', $businessId)
                ->get();

            $adhocEarnings = 0;
            $adhocDeductions = 0;
            $adhocDetails = [];

            foreach ($adhocTransactions as $transaction) {
                foreach ($transaction->transaction_details as $detail) {
                    $earningAmount = floatval($detail->earning_amount ?? 0);
                    $deductionAmount = floatval($detail->deduction_amount ?? 0);
                    $netAmount = $earningAmount - $deductionAmount;

                    $adhocEarnings += $earningAmount;
                    $adhocDeductions += $deductionAmount;

                    $adhocDetails[] = [
                        'component_name' => $detail->component->ac_adhoc_component_name ?? 'Adjustment',
                        'net_amount' => $netAmount,
                        'is_earning' => $netAmount > 0,
                        'remarks' => $detail->remarks ?? '',
                    ];
                }
            }

            // Calculate net salary
            $adHocAmount = $adhocEarnings - $adhocDeductions;
            $netSalary = $proratedBase + $adHocAmount;

            // Check if already processed
            $isProcessed = ProcessedEmployeeSalary::where('ps_emp_id', $employeeId)
                ->where('ps_payroll_id', $payrollId)
                ->where('ps_b_id', $businessId)
                ->exists();

            // Check if held
            $isHeld = SalaryHold::where('sh_emp_id', $employeeId)
                ->where('sh_pp_id', $payrollId)
                ->where('sh_b_id', $businessId)
                ->where('sh_status', 'held')
                ->exists();

            return response()->json([
                'success' => true,
                'data' => [
                    'emp_id' => $employee->emp_id,
                    'emp_code' => $employee->emp_code,
                    'emp_name' => $employee->emp_full_name,
                    'department' => $employee->fh_department->d_name ?? 'N/A',
                    'designation' => $employee->fh_designation->dg_name ?? 'N/A',

                    // Days data
                    'month_days' => $totalMonthDays,
                    'worked_days' => round($salaryDays, 1),
                    'present_days' => $presentDays,
                    'half_days' => $halfDays,
                    'leave_days' => $attendance->as_total_leave,
                    'absent_days' => $attendance->as_total_absent,
                    'weekoff_days' => $weekoffDays,
                    'worked_percentage' => round($workedPct, 2),

                    // Salary data
                    'base_salary' => $baseSalary,
                    'prorated_base_salary' => $proratedBase,
                    'ad_hoc_amount' => $adHocAmount,
                    'ad_hoc_earnings' => $adhocEarnings,
                    'ad_hoc_deductions' => $adhocDeductions,
                    'total_earnings' => $proratedBase + $adhocEarnings,
                    'total_deductions' => $adhocDeductions,
                    'net_salary' => $netSalary,

                    // Status
                    'is_processed' => $isProcessed,
                    'is_held' => $isHeld,
                    'has_ad_hoc' => count($adhocDetails) > 0,
                    'adhoc_details' => $adhocDetails,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load salary preview: '.$e->getMessage(),
            ], 500);
        }
    }

    // public function payrollNewProcess(Request $request)
    // {
    //     $user = Auth::user();
    //     $businessId = $user->emp_b_id;

    //     /* -------------------------------------------------
    //     | Payroll Period
    //     |--------------------------------------------------*/
    //     $payrollPeriod = PayrollPeriod::with(['month', 'financialYear'])
    //         ->withCount([
    //             'activeSalaryHolds as hold_count',
    //             'holdUntilRelease as hold_until_release_count',
    //             'processedSalaries as processed_count',
    //         ])
    //         ->where('pp_id', $request->period)
    //         ->firstOrFail();

    //     // UI helper
    //     $payrollPeriod->has_holds = $payrollPeriod->hold_count > 0;
    //     $payrollPeriod->is_processed = $payrollPeriod->processed_count > 0;

    //     $monthName = $payrollPeriod->month?->m_name;
    //     $year = Carbon::parse($payrollPeriod->pp_start_date)->format('Y');

    //     // Payroll dates
    //     $ppStartDate = Carbon::parse($payrollPeriod->pp_start_date);
    //     $ppEndDate = Carbon::parse($payrollPeriod->pp_end_date);

    //     /* -------------------------------------------------
    //     | Payroll Eligible Employee Condition
    //     |--------------------------------------------------*/
    //     $payrollEmployeeCondition = function ($query) use ($ppEndDate, $ppStartDate) {
    //         $query->whereDate('emp_date_of_joining', '<=', $ppEndDate)
    //             ->where(function ($q) use ($ppStartDate) {
    //                 $q->whereNull('emp_last_working_date')
    //                     ->orWhereDate('emp_last_working_date', '>=', $ppStartDate);
    //             });
    //     };

    //     /* -------------------------------------------------
    //     | Total Employees (Payroll Eligible)
    //     |--------------------------------------------------*/
    //     $totalEmployees = Employee::where('emp_b_id', $businessId)
    //         ->where($payrollEmployeeCondition)
    //         ->count();

    //     /* -------------------------------------------------
    //     | Active Employees (Status = 71)
    //     |--------------------------------------------------*/
    //     $totalActiveEmployees = Employee::where('emp_b_id', $businessId)
    //         ->where('emp_status', 71)
    //         ->where($payrollEmployeeCondition)
    //         ->count();

    //     /* -------------------------------------------------
    //     | Inactive Employees (Status = 72 but overlapping)
    //     |--------------------------------------------------*/
    //     $totalInactiveEmp = Employee::where('emp_b_id', $businessId)
    //         ->where('emp_status', 72)
    //         ->whereDate('emp_date_of_joining', '<=', $ppEndDate)
    //         ->whereDate('emp_last_working_date', '>=', $ppStartDate)
    //         ->count();

    //     /* -------------------------------------------------
    //     | Employees on Hold for this payroll period
    //     |--------------------------------------------------*/
    //     $heldEmployees = SalaryHold::where('sh_pp_id', $payrollPeriod->pp_id)
    //         ->where('sh_b_id', $businessId)
    //         ->where('sh_status', 'held')
    //         ->count();

    //     /* -------------------------------------------------
    //     | Already Processed Employees
    //     |--------------------------------------------------*/
    //     $processedEmployeesCount = ProcessedEmployeeSalary::where('ps_payroll_id', $payrollPeriod->pp_id)
    //         ->where('ps_b_id', $businessId)
    //         ->count();

    //     /* -------------------------------------------------
    //     | Ready To Process (excluding holds and already processed)
    //     |--------------------------------------------------*/
    //     $readyToProcess = max(0, $totalEmployees - $heldEmployees - $processedEmployeesCount);

    //     // Store in payrollPeriod object for view
    //     $payrollPeriod->ready_to_process = $readyToProcess;
    //     $payrollPeriod->held_count = $heldEmployees;
    //     $payrollPeriod->processed_count = $processedEmployeesCount;

    //     /* -------------------------------------------------
    //     | Pending Employees (not held and not processed)
    //     |--------------------------------------------------*/
    //     $pendingEmployees = $totalEmployees - $heldEmployees - $processedEmployeesCount;

    //     /* -------------------------------------------------
    //     | Processed Employees Details (for recent list)
    //     |--------------------------------------------------*/
    //     $processedEmployees = ProcessedEmployeeSalary::with([
    //         'employee.fh_designation',
    //         'employee.fh_department',
    //     ])
    //         ->where('ps_payroll_id', $payrollPeriod->pp_id)
    //         ->where('ps_b_id', $businessId)
    //         ->orderBy('created_at', 'desc')
    //         ->take(3)
    //         ->get()
    //         ->map(function ($salary) {
    //             return [
    //                 'employee_id' => $salary->employee->emp_id ?? null,
    //                 'name' => $salary->employee->emp_full_name ?? 'N/A',
    //                 'code' => $salary->employee->emp_code ?? 'N/A',
    //                 'designation' => $salary->employee->fh_designation->dg_name ?? 'N/A',
    //                 'department' => $salary->employee->fh_department->d_name ?? 'N/A',
    //                 'net_salary' => number_format($salary->ps_monthly_net_salary ?? 0, 2),
    //                 'status' => 'Processed',
    //             ];
    //         });

    //         // dd($processedEmployees);
    //     /* -------------------------------------------------
    //     | Calculate total days in payroll period
    //     |--------------------------------------------------*/
    //     $totalDays = $ppStartDate->diffInDays($ppEndDate) + 1;

    //     // Calculate working days (excluding weekends - you might need to adjust this based on your business logic)
    //     $workingDays = $totalDays; // Default, you can add logic to exclude weekends

    //     $payrollPeriod->total_days = $totalDays;
    //     $payrollPeriod->working_days = $workingDays;

    //     /* -------------------------------------------------
    //     | Attendance + Pending Requests
    //     |--------------------------------------------------*/
    //     $attendanceDetails = $this->getAttendanceDetails($payrollPeriod);

    //     $pendingRequests = $this->getPendingRequestsForStep1(
    //         $payrollPeriod->pp_id,
    //         $businessId
    //     );

    //     /* -------------------------------------------------
    //     | Calculate progress percentage
    //     |--------------------------------------------------*/
    //     $progressPercent = $totalEmployees > 0 ?
    //         round(($processedEmployeesCount / $totalEmployees) * 100, 2) : 0;

    //     /* -------------------------------------------------
    //     | Get payroll period status display
    //     |--------------------------------------------------*/
    //     $payrollPeriod->status_display = match ($payrollPeriod->pp_status_code) {
    //         'PROCESSING' => 'PROCESSING',
    //         'ATTENDANCE_FROZEN' => 'Attendance Frozen',
    //         'SALARY_PROCESSING' => 'Salary Processing',
    //         'VERIFICATION' => 'Verification',
    //         'COMPLETED' => 'Completed',
    //         'PAYROLL_LOCKED' => 'Payroll Locked',
    //         default => $payrollPeriod->pp_status_code
    //     };

    //     /* -------------------------------------------------
    //     | Get financial year name
    //     |--------------------------------------------------*/
    //     $financialYearName = $payrollPeriod->financialYear?->fy_name ??
    //                         $payrollPeriod->financialYear?->fy_year ?? '2025';

    //     /* -------------------------------------------------
    //     | View
    //     |--------------------------------------------------*/
    //     return view(
    //         'admin.payroll.payrun.payroll-new-process',
    //         compact(
    //             'payrollPeriod',
    //             'monthName',
    //             'year',
    //             'financialYearName',
    //             'attendanceDetails',
    //             'processedEmployees',
    //             'totalEmployees',
    //             'totalActiveEmployees',
    //             'totalInactiveEmp',
    //             'heldEmployees',
    //             'processedEmployeesCount',
    //             'readyToProcess',
    //             'pendingEmployees',
    //             'totalDays',
    //             'workingDays',
    //             'progressPercent',
    //             'pendingRequests'
    //         )
    //     );
    // }


     public function payrollNewProcess(Request $request)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;

        /* -------------------------------------------------
        | Payroll Period
        |--------------------------------------------------*/
        $payrollPeriod = PayrollPeriod::with(['month', 'financialYear'])
            ->withCount([
                'activeSalaryHolds as hold_count',
                'holdUntilRelease as hold_until_release_count',
                'processedSalaries as processed_count',
            ])
            ->where('pp_id', $request->period)
            ->where('pp_b_id', $businessId)
            ->firstOrFail();

        // UI helper
        $payrollPeriod->has_holds = $payrollPeriod->hold_count > 0;
        $payrollPeriod->is_processed = $payrollPeriod->processed_count > 0;

        $monthName = $payrollPeriod->month?->m_name;
        $year = Carbon::parse($payrollPeriod->pp_start_date)->format('Y');

        // Payroll dates
        $ppStartDate = Carbon::parse($payrollPeriod->pp_start_date);
        $ppEndDate = Carbon::parse($payrollPeriod->pp_end_date);

        /* -------------------------------------------------
        | Payroll Eligible Employee Condition
        |--------------------------------------------------*/
        $payrollEmployeeCondition = function ($query) use ($ppEndDate, $ppStartDate) {
            $query->whereDate('emp_date_of_joining', '<=', $ppEndDate)
                ->where(function ($q) use ($ppStartDate) {
                    $q->whereNull('emp_last_working_date')
                        ->orWhereDate('emp_last_working_date', '>=', $ppStartDate);
                });
        };

        /* -------------------------------------------------
        | Total Employees (Payroll Eligible)
        |--------------------------------------------------*/
        $totalEmployees = Employee::where('emp_b_id', $businessId)
            ->where($payrollEmployeeCondition)
            ->count();

        /* -------------------------------------------------
        | Active Employees (Status = 71)
        |--------------------------------------------------*/
        $totalActiveEmployees = Employee::where('emp_b_id', $businessId)
            ->where('emp_status', 71)
            ->where($payrollEmployeeCondition)
            ->count();

        /* -------------------------------------------------
        | Inactive Employees (Status = 72 but overlapping)
        |--------------------------------------------------*/
        $totalInactiveEmp = Employee::where('emp_b_id', $businessId)
            ->where('emp_status', 72)
            ->whereDate('emp_date_of_joining', '<=', $ppEndDate)
            ->whereDate('emp_last_working_date', '>=', $ppStartDate)
            ->count();

        /* -------------------------------------------------
        | Employees on Hold for this payroll period
        |--------------------------------------------------*/
        $heldEmployees = SalaryHold::where('sh_pp_id', $payrollPeriod->pp_id)
            ->where('sh_b_id', $businessId)
            ->where('sh_status', 'held')
            ->count();

        /* -------------------------------------------------
        | Already Processed Employees
        |--------------------------------------------------*/
        $processedEmployeesCount = ProcessedEmployeeSalary::where('ps_payroll_id', $payrollPeriod->pp_id)
            ->where('ps_b_id', $businessId)
            ->count();

        /* -------------------------------------------------
        | Ready To Process (excluding holds and already processed)
        |--------------------------------------------------*/
        $readyToProcess = max(0, $totalEmployees - $heldEmployees - $processedEmployeesCount);

        // Store in payrollPeriod object for view
        $payrollPeriod->ready_to_process = $readyToProcess;
        $payrollPeriod->held_count = $heldEmployees;
        $payrollPeriod->processed_count = $processedEmployeesCount;

        /* -------------------------------------------------
        | Pending Employees (not held and not processed)
        |--------------------------------------------------*/
        $pendingEmployees = $totalEmployees - $heldEmployees - $processedEmployeesCount;

        /* -------------------------------------------------
        | Processed Employees Details (for recent list)
        |--------------------------------------------------*/
        $processedEmployees = ProcessedEmployeeSalary::with([
            'employee.fh_designation',
            'employee.fh_department',
            'employee.fh_employee_salary'
        ])
            ->where('ps_payroll_id', $payrollPeriod->pp_id)
            ->where('ps_b_id', $businessId)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get()
            ->map(function ($salary) {
                // Debug के लिए डेटा देखें
                // dd($salary->employee); // Employee object check करें
                // dd($salary->employee->fh_designation); // Designation check करें

                return [
                    'id' => $salary->ps_id,
                    'employee_id' => $salary->employee->emp_id ?? null,
                    'name' => $salary->employee->emp_full_name ?? 'N/A',
                    'code' => $salary->employee->emp_code ?? 'N/A',
                    'designation' => $salary->employee->fh_designation->dg_name ?? 'N/A',
                    'department' => $salary->employee->fh_department->d_name ?? 'N/A',
                    'salary' => number_format($salary->ps_monthly_net_salary ?? 0, 2),
                    'net_salary' => number_format($salary->ps_monthly_net_salary ?? 0, 2),
                    'status' => 'Processed',
                ];
            });


        /* -------------------------------------------------
        | Processed Employees For Revert Modal
        |--------------------------------------------------*/
        $allProcessedEmployeesForRevert = ProcessedEmployeeSalary::with([
            'employee.fh_designation',
            'employee.fh_department',
            'employee' => function($query) {
                $query->select('emp_id', 'emp_full_name', 'emp_code');
            }
        ])
            ->where('ps_payroll_id', $payrollPeriod->pp_id)
            ->where('ps_b_id', $businessId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($salary) {
                return [
                    'id' => $salary->ps_id, // Revert के लिए processed salary ID
                    'employee_id' => $salary->employee->emp_id ?? null,
                    'name' => $salary->employee->emp_full_name ?? 'N/A',
                    'code' => $salary->employee->emp_code ?? 'N/A',
                    'designation' => $salary->employee->fh_designation->dg_name ?? 'N/A',
                    'department' => $salary->employee->fh_department->d_name ?? 'N/A',
                    'salary' => number_format($salary->ps_monthly_net_salary ?? 0, 2),
                    'net_salary' => number_format($salary->ps_monthly_net_salary ?? 0, 2),
                    'status' => 'Processed',
                ];
            });

        /* -------------------------------------------------
        | Calculate total days in payroll period
        |--------------------------------------------------*/
        $totalDays = $ppStartDate->diffInDays($ppEndDate) + 1;

        // Calculate working days (excluding weekends - you might need to adjust this based on your business logic)
        $workingDays = $totalDays; // Default, you can add logic to exclude weekends

        $payrollPeriod->total_days = $totalDays;
        $payrollPeriod->working_days = $workingDays;

        /* -------------------------------------------------
        | Attendance + Pending Requests
        |--------------------------------------------------*/
        $attendanceDetails = $this->getAttendanceDetails($payrollPeriod);

        $pendingRequests = $this->getPendingRequestsForStep1(
            $payrollPeriod->pp_id,
            $businessId
        );

        /* -------------------------------------------------
        | Calculate progress percentage
        |--------------------------------------------------*/
        $progressPercent = $totalEmployees > 0 ?
            round(($processedEmployeesCount / $totalEmployees) * 100, 2) : 0;

        /* -------------------------------------------------
        | Get payroll period status display
        |--------------------------------------------------*/
        $payrollPeriod->status_display = match ($payrollPeriod->pp_status_code) {
            'PROCESSING' => 'PROCESSING',
            'ATTENDANCE_FROZEN' => 'Attendance Frozen',
            'SALARY_PROCESSING' => 'Salary Processing',
            'VERIFICATION' => 'Verification',
            'COMPLETED' => 'Completed',
            'PAYROLL_LOCKED' => 'Payroll Locked',
            default => $payrollPeriod->pp_status_code
        };

        /* -------------------------------------------------
        | Get financial year label (same as payrun / freeze: fy_year on linked FY)
        |--------------------------------------------------*/
        $financialYearName = $payrollPeriod->financialYear?->fy_year;
        if ($financialYearName === null || $financialYearName === '') {
            $financialYearName = FinancialYear::where('fy_id', $payrollPeriod->pp_fy_id)
                ->where('fy_b_id', $businessId)
                ->value('fy_year');
        }
        $financialYearName = $financialYearName ?: '—';

        $currentStep = $this->getStepFromStatus($payrollPeriod->pp_status_code);

        /* -------------------------------------------------
        | View
        |--------------------------------------------------*/
        return view(
            'admin.payroll.payrun.payroll-new-process',
            compact(
                'payrollPeriod',
                'monthName',
                'year',
                'financialYearName',
                'attendanceDetails',
                'processedEmployees',
                'allProcessedEmployeesForRevert', // नया variable जोड़ा
                'totalEmployees',
                'totalActiveEmployees',
                'totalInactiveEmp',
                'heldEmployees',
                'processedEmployeesCount',
                'readyToProcess',
                'pendingEmployees',
                'totalDays',
                'workingDays',
                'progressPercent',
                'pendingRequests',
                'currentStep'
            )
        );
    }

        /**
     * Determine which step to show based on payroll status
     */
    private function getStepFromStatus($statusCode)
    {
        // Status to step mapping
        $statusStepMap = [
            'PROCESSING' => 'DASHBOARD',
            'UPCOMING' => 'DASHBOARD',
            'OPEN' => 'DASHBOARD',
            'PENDING_APPROVAL' => 'STEP1',
            'UNDER_REVIEW' => 'STEP2',
            'ATTENDANCE_FROZEN' => 'STEP2',
            'IN-PROCESS' => 'STEP3',
            'SALARY_PROCESSING' => 'STEP3',
            'VERIFICATION' => 'STEP5',
            'COMPLETED' => 'STEP6',
            'finalized_locked' => 'STEP6',
            'PAYROLL_LOCKED' => 'STEP6',
        ];

        return $statusStepMap[$statusCode] ?? 'DASHBOARD';
    }

    // ✅ New method to get pending requests for Step 1
    private function getPendingRequestsForStep1($payrollId, $businessId)
    {
        $payrollPeriod = PayrollPeriod::find($payrollId);

        if (! $payrollPeriod) {
            return [
                'has_pending' => false,
                'missed_punches' => 0,
                'leave_requests' => 0,
                'overtime_requests' => 0,
                'total_pending' => 0,
            ];
        }

        $startDate = Carbon::parse($payrollPeriod->pp_start_date);
        $endDate = Carbon::parse($payrollPeriod->pp_end_date);

        // Missed Punches
        $missPunchPending = AttendanceException::where('ae_b_id', $businessId)
            ->where('ae_stage_completed', 0)
            ->whereNotIn('ae_status', [139, 192, 156, 170])
            ->whereNotNull('ae_am_id')
            ->whereBetween('ae_date', [$startDate, $endDate])
            ->count();

        // Leave Requests
        $leavePending = 0;
        if (class_exists('App\\Models\\LeaveRequest')) {
            $leavePending = LeaveRequest::where('lvr_b_id', $businessId)
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
                ->count();
        }


        $overtimePolicy = OvertimePolicy::where('ot_b_id', $businessId)->where('ot_is_enabled', 1)->exists();
        $overTimePending = 0;
        if ($overtimePolicy) {
            $overTimePending = DB::table('ot_approval_status')
            ->where('ot_b_id', $businessId)
            ->where('ot_stage_completed', 0)
            ->where(function ($query) {
                $query->whereNull('ot_requested_status')
                ->orWhere('ot_requested_status', '!=', 170);
                })
                ->whereBetween('ot_date', [$startDate, $endDate])
                ->count();
                }

        $totalPending = $missPunchPending + $leavePending + $overTimePending;

        return [
            'has_pending' => $totalPending > 0,
            'missed_punches' => $missPunchPending,
            'leave_requests' => $leavePending,
            'overtime_requests' => $overTimePending,
            'total_pending' => $totalPending,
            'missed_punch_url' => route('mis-punch.index'),
            'payroll_id' => $payrollId,
        ];
    }

    public function checkPendingRequests(Request $request)
    {
        try {
            $businessId = auth()->user()->emp_b_id;
            $payrollPeriodId = $request->get('payroll_period');

            $payrollPeriod = PayrollPeriod::find($payrollPeriodId);
            if (! $payrollPeriod) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payroll period not found',
                ], 404);
            }

            $startDate = $payrollPeriod->pp_start_date;
            $endDate = $payrollPeriod->pp_end_date;

            $currentStatus = $payrollPeriod->pp_status_code ?? 'UPCOMING';
            $oldStatus = $currentStatus;
            $statusChanged = false;

            // ✅ SIMPLE LOGIC: Jab bhi yeh route hit ho, status ko PENDING_APPROVAL update karo
            if ($currentStatus !== 'PENDING_APPROVAL') {
                $payrollPeriod->pp_status_code = 'PENDING_APPROVAL';
                $payrollPeriod->save();
                $statusChanged = true;
            }

            // 2. Missed Punches with Employee Details
            $missPunchPendingQuery = AttendanceException::join('employees', 'attendance_exceptions.ae_emp_id', '=', 'employees.emp_id')
                ->where('attendance_exceptions.ae_b_id', $businessId)
                ->where('attendance_exceptions.ae_stage_completed', 0)
                ->whereNotIn('attendance_exceptions.ae_status', [139, 192, 156, 170])
                ->whereNotNull('attendance_exceptions.ae_am_id')
                ->whereBetween('attendance_exceptions.ae_date', [$startDate, $endDate])
                ->select(
                    'attendance_exceptions.*',
                    'employees.emp_full_name',
                    'employees.emp_code',
                    'employees.emp_dg_id',
                    'employees.emp_d_id'
                );

            $missPunchPending = $missPunchPendingQuery->count();

            // Get details for modal
            $missPunchDetails = $missPunchPendingQuery
                ->orderBy('attendance_exceptions.ae_date', 'desc')
                ->limit(3)
                ->get()
                ->map(function ($exception) {
                    return [
                        'employee_id' => $exception->ae_emp_id,
                        'employee_name' => $exception->emp_full_name ?? 'Unknown',
                        'employee_code' => $exception->emp_code ?? 'N/A',
                        'date' => \Carbon\Carbon::parse($exception->ae_date)->format('M d'),
                        'type' => 'Missed Punch',
                        'in_time' => $exception->ae_in_time,
                        'out_time' => $exception->ae_out_time,
                        'status' => $exception->ae_status,
                    ];
                });

            // 3. Leave Requests (with Employee Details)
            $leavePending = 0;
            $leaveDetails = [];

            if (class_exists('App\\Models\\LeaveRequest')) {
                $leavePendingQuery = LeaveRequest::join('employees', 'leave_requests.lvr_emp_id', '=', 'employees.emp_id')
                    ->where('leave_requests.lvr_b_id', $businessId)
                    ->where('leave_requests.lvr_stage_completed', 0)
                    ->whereNull('leave_requests.lvr_p_id')
                    ->whereNotIn('leave_requests.lvr_status', [170])
                    ->where(function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('leave_requests.lvr_start_date', [$startDate, $endDate])
                            ->orWhereBetween('leave_requests.lvr_end_date', [$startDate, $endDate])
                            ->orWhere(function ($subQuery) use ($startDate, $endDate) {
                                $subQuery->where('leave_requests.lvr_start_date', '<=', $startDate)
                                    ->where('leave_requests.lvr_end_date', '>=', $endDate);
                            });
                    });

                $leavePending = $leavePendingQuery->count();

                $leaveDetails = $leavePendingQuery
                    ->select(
                        'leave_requests.*',
                        'employees.emp_full_name',
                        'employees.emp_code'
                    )
                    ->orderBy('leave_requests.lvr_start_date', 'desc')
                    ->limit(3)
                    ->get()
                    ->map(function ($leave) {
                        $startDate = \Carbon\Carbon::parse($leave->lvr_start_date);
                        $endDate = \Carbon\Carbon::parse($leave->lvr_end_date);
                        $duration = $startDate->diffInDays($endDate) + 1;

                        return [
                            'employee_id' => $leave->lvr_emp_id,
                            'employee_name' => $leave->emp_full_name ?? 'Unknown',
                            'employee_code' => $leave->emp_code ?? 'N/A',
                            'leave_type' => $leave->leave_type_name ?? 'N/A',
                            'duration' => $duration.' Day'.($duration > 1 ? 's' : ''),
                            'start_date' => $startDate->format('M d'),
                            'end_date' => $endDate->format('M d'),
                            'status' => $leave->lvr_status,
                        ];
                    });
            }

            // 4. Over Time Requests with Employee Details
            $overTimePendingQuery = DB::table('ot_approval_status as ot')
                ->join('employees', 'ot.ot_emp_id', '=', 'employees.emp_id')
                ->where('ot.ot_b_id', $businessId)
                ->where('ot.ot_stage_completed', 0)
                ->where(function ($query) {
                    $query->whereNull('ot.ot_requested_status')
                        ->orWhere('ot.ot_requested_status', '!=', 170);
                })
                ->whereBetween('ot.ot_date', [$startDate, $endDate]);

            $overTimePending = $overTimePendingQuery->count();

            $overTimeDetails = $overTimePendingQuery
                ->select(
                    'ot.*',
                    'employees.emp_full_name',
                    'employees.emp_code'
                )
                ->orderBy('ot.ot_date', 'desc')
                ->limit(3)
                ->get()
                ->map(function ($ot) {
                    return [
                        'employee_id' => $ot->ot_emp_id ?? $ot->ot_atd_id,
                        'employee_name' => $ot->emp_full_name ?? 'Unknown',
                        'employee_code' => $ot->emp_code ?? 'N/A',
                        'date' => \Carbon\Carbon::parse($ot->ot_date)->format('M d'),
                        'hours' => $ot->ot_hours ?? 0,
                        'type' => $ot->ot_type ?? 'Overtime',
                        'status' => $ot->ot_requested_status,
                    ];
                });

            $totalPending = $missPunchPending + $leavePending + $overTimePending;

            // 5. Response में status information add करें
            return response()->json([
                'success' => true,
                'data' => [
                    'missed_punches' => [
                        'count' => $missPunchPending,
                        'details' => $missPunchDetails,
                        'exists' => $missPunchPending > 0,
                    ],
                    'leave_requests' => [
                        'count' => $leavePending,
                        'details' => $leaveDetails,
                        'exists' => $leavePending > 0,
                    ],
                    'overtime_requests' => [
                        'count' => $overTimePending,
                        'details' => $overTimeDetails,
                        'exists' => $overTimePending > 0,
                    ],
                    // Status info
                    'current_status' => $payrollPeriod->pp_status_code,
                    'previous_status' => $oldStatus,
                    'status_changed' => $statusChanged,
                    'status_update_message' => $statusChanged ?
                        "Status updated from {$oldStatus} to PENDING_APPROVAL" :
                        'Already in PENDING_APPROVAL status',

                    // Simple message
                    'message' => $totalPending > 0 ?
                        "Found {$totalPending} pending requests" :
                        'No pending requests found',
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check pending requests',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // In your Controller
    public function storeWithHoldSalary(Request $request)
    {
        $business_id = $this->user->emp_b_id;

        $request->validate([
            'payroll_period_id' => 'required|array',
            'reason' => 'nullable|string',
            'employee_id' => 'nullable|integer',
            'hold_until_release' => 'nullable|boolean',
        ]);

        $userId = Auth::id();
        $now = now();

        try {
            $payrollPeriods = $request->payroll_period_id;
            $successCount = 0;
            $failedCount = 0;
            $messages = [];

            // ✅ Get payroll periods with details for response
            $allPeriods = PayrollPeriod::whereIn('pp_id', $payrollPeriods)
                ->where('pp_b_id', $business_id)
                ->with(['month']) // month relation load करें
                ->get()
                ->keyBy('pp_id');

            // If "Hold until release" is checked, enforce single period selection
            if ($request->boolean('hold_until_release') && count($payrollPeriods) > 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'When "Hold until release" is enabled, only one payroll period can be selected.',
                ]);
            }

            $holdDetails = [];

            foreach ($payrollPeriods as $ppId) {
                $payrollPeriod = $allPeriods->get($ppId);

                if (! $payrollPeriod) {
                    $failedCount++;
                    $messages[] = "Payroll period ID {$ppId} not found";

                    continue;
                }

                // Get month name
                $monthName = $payrollPeriod->month->m_name ?? date('F', strtotime($payrollPeriod->pp_start_date));
                $periodName = $payrollPeriod->pp_name ?? "{$monthName} ".date('Y', strtotime($payrollPeriod->pp_start_date));

                // Check if salary is already processed
                $alreadyProcessed = ProcessedEmployeeSalary::where('ps_emp_id', $request->employee_id)
                    ->where('ps_payroll_id', $ppId)
                    ->exists();

                if ($alreadyProcessed) {
                    $failedCount++;
                    $messages[] = "Salary already processed for payroll period: {$periodName}";

                    continue;
                }

                // Check if already on hold
                $alreadyHeld = SalaryHold::where('sh_emp_id', $request->employee_id)
                    ->where('sh_pp_id', $ppId)
                    ->where('sh_status', 'held')
                    ->exists();

                if ($alreadyHeld) {
                    $failedCount++;
                    $messages[] = "Salary already on hold for payroll period: {$periodName}";

                    continue;
                }

                // Create hold record
                $hold = SalaryHold::create([
                    'sh_emp_id' => $request->employee_id,
                    'sh_b_id' => $business_id,
                    'sh_dept_id' => null,
                    'sh_pp_id' => $ppId,
                    'sh_status' => 'held',
                    'sh_reason' => $request->reason,
                    'sh_hold_until_release' => $request->boolean('hold_until_release') ? 1 : 0,
                    'sh_held_by' => $userId,
                    'sh_held_at' => $now,
                ]);

                $successCount++;

                // Add to response details
                $holdDetails[] = [
                    'period_id' => $ppId,
                    'period_name' => $periodName,
                    'month' => $monthName,
                    'start_date' => date('d M Y', strtotime($payrollPeriod->pp_start_date)),
                    'end_date' => date('d M Y', strtotime($payrollPeriod->pp_end_date)),
                    'hold_id' => $hold->sh_id,
                    'hold_until_release' => $request->boolean('hold_until_release') ? 'Yes' : 'No',
                    'held_at' => $now->format('d M Y, h:i A'),
                ];
            }

            if ($successCount > 0) {
                // ✅ Get employee details for response
                $employee = Employee::find($request->employee_id);
                $employeeName = $employee ? $employee->emp_full_name : 'Employee #'.$request->employee_id;

                return response()->json([
                    'status' => true,
                    'message' => "Successfully put salary on hold for {$successCount} period(s).".
                                ($failedCount > 0 ? " Failed for {$failedCount} period(s)." : ''),
                    'success_count' => $successCount,
                    'failed_count' => $failedCount,
                    'messages' => $messages,
                    'data' => [
                        'employee_id' => $request->employee_id,
                        'employee_name' => $employeeName,
                        'reason' => $request->reason,
                        'hold_details' => $holdDetails,
                        'hold_until_release' => $request->boolean('hold_until_release') ? true : false,
                    ],
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to hold salary for all selected periods.',
                    'errors' => $messages,
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Hold Salary Error: '.$e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'System error: '.$e->getMessage(),
            ], 500);
        }
    }

    public function releaseSalary(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer',
            'cycle_id' => 'nullable',
            'cycle_id.*' => 'integer',
        ]);

        try {
            $userId = Auth::id();
            $now = now();
            $releasedCount = 0;

            $query = SalaryHold::where('sh_emp_id', $request->employee_id)
                ->where('sh_status', 'held');

            // If specific periods are provided, release only those
            if ($request->filled('cycle_id')) {
                $cycleIds = is_array($request->cycle_id)
                    ? $request->cycle_id
                    : [$request->cycle_id];

                $query->whereIn('sh_pp_id', $cycleIds);
            }

            // Release all holds for this employee (across all periods)
            // if ($request->boolean('release_all')) {
            //     $query->where('sh_hold_until_release', 1);
            // }

            $holds = $query->get();

            foreach ($holds as $hold) {
                $hold->update([
                    'sh_status' => 'released',
                    'sh_released_by' => $userId,
                    'sh_released_at' => $now,
                    'sh_release_notes' => $request->release_notes,
                ]);
                $releasedCount++;
            }

            if ($releasedCount > 0) {
                return response()->json([
                    'status' => true,
                    'message' => "Successfully released {$releasedCount} salary hold(s).",
                    'released_count' => $releasedCount,
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'No holds found to release.',
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Release Salary Error: '.$e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'System error: '.$e->getMessage(),
            ], 500);
        }
    }

    // public function getPendingRequests(Request $request)
    // {
    //     try {
    //         $businessId = auth()->user()->emp_b_id;
    //         $payrollPeriodId = $request->get('payroll_period');

    //         // Payroll period से dates fetch करें
    //         $payrollPeriod = PayrollPeriod::find($payrollPeriodId);
    //         if (! $payrollPeriod) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Payroll period not found',
    //             ], 404);
    //         }

    //         $startDate = Carbon::parse($payrollPeriod->pp_start_date);
    //         $endDate = Carbon::parse($payrollPeriod->pp_end_date);

    //         // Temporary debug code add करें
    //         $testQuery = AttendanceException::where('ae_b_id', $businessId)
    //             ->where('ae_stage_completed', 0)
    //             ->whereNotIn('ae_status', [139, 192, 156, 170])
    //             ->whereNotNull('ae_am_id')
    //             ->whereBetween('ae_date', [$startDate, $endDate]);

    //         dd(
    //             [
    //                 'Start Date' => $startDate,
    //                 'End Date' => $endDate,
    //                 'Business ID' => $businessId,
    //                 'Total Records in Range' => $testQuery->count(),
    //                 'Sample Records' => $testQuery->take(5)->get(['ae_date', 'ae_status', 'ae_stage_completed', 'ae_am_id']),
    //                 'All Status Counts' => AttendanceException::where('ae_b_id', $businessId)
    //                     ->whereBetween('ae_date', [$startDate, $endDate])
    //                     ->select('ae_status', DB::raw('count(*) as count'))
    //                     ->groupBy('ae_status')
    //                     ->get(),
    //             ]
    //         );

    //         // Missed Punches
    //         $missPunchPending = AttendanceException::where('ae_b_id', $businessId)
    //             ->where('ae_stage_completed', 0)
    //             ->whereNotIn('ae_status', [139, 192, 156, 170])
    //             ->whereNotNull('ae_am_id')
    //             ->whereBetween('ae_date', [$startDate, $endDate])
    //             ->count();

    //         // Leave Requests
    //         $leavePending = 0;
    //         if (class_exists('App\\Models\\LeaveRequest')) {
    //             $leavePending = LeaveRequest::where('lvr_b_id', $businessId)
    //                 ->where('lvr_stage_completed', 0)
    //                 ->whereNull('lvr_p_id')
    //                 ->whereNotIn('lvr_status', [170])
    //                 ->where(function ($query) use ($startDate, $endDate) {
    //                     $query->whereBetween('lvr_start_date', [$startDate, $endDate])
    //                         ->orWhereBetween('lvr_end_date', [$startDate, $endDate])
    //                         ->orWhere(function ($subQuery) use ($startDate, $endDate) {
    //                             $subQuery->where('lvr_start_date', '<=', $startDate)
    //                                 ->where('lvr_end_date', '>=', $endDate);
    //                         });
    //                 })
    //                 ->count();
    //         }

    //         // Over Time Requests
    //         $overTimePending = DB::table('ot_approval_status')
    //             ->where('ot_b_id', $businessId)
    //             ->where('ot_stage_completed', 0)
    //             ->where(function ($query) {
    //                 $query->whereNull('ot_requested_status')
    //                     ->orWhere('ot_requested_status', '!=', 170);
    //             })
    //             ->whereBetween('ot_date', [$startDate, $endDate])
    //             ->count();

    //         $totalPending = $missPunchPending + $leavePending + $overTimePending;

    //         return response()->json([
    //             'success' => true,
    //             'data' => [
    //                 'missed_punches' => $missPunchPending,
    //                 'leave_requests' => $leavePending,
    //                 'overtime_requests' => $overTimePending,
    //                 'total_pending' => $totalPending,
    //                 'has_pending' => $totalPending > 0,
    //                 'missed_punch_url' => route('mis-punch.index'),
    //                 'payroll_id' => $payrollPeriodId,
    //             ],
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to get pending requests',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    // public function getPendingRequests(Request $request)
    // {
    //     try {
    //         $businessId = auth()->user()->emp_b_id;
    //         $payrollPeriodId = $request->get('payroll_period');

    //         // Payroll period से dates fetch करें
    //         $payrollPeriod = PayrollPeriod::find($payrollPeriodId);
    //         $startDate = $payrollPeriod->pp_start_date ?? Carbon::now()->startOfMonth()->format('Y-m-d');
    //         $endDate = $payrollPeriod->pp_end_date ?? Carbon::now()->endOfMonth()->format('Y-m-d');

    //         // Missed Punches
    //         $missPunchPending = AttendanceException::where('ae_b_id', $businessId)
    //             ->where('ae_stage_completed', 0)
    //             ->whereNotIn('ae_status', [139, 192, 156, 170])
    //             ->whereNotNull('ae_am_id')
    //             ->whereBetween('ae_date', [$startDate, $endDate])
    //             ->count();

    //             // Missed Punch Details (top 3 for display)
    //             $missPunchDetails = AttendanceException::with(['employee'])
    //             ->where('ae_b_id', $businessId)
    //             ->where('ae_stage_completed', 0)
    //             ->whereNotIn('ae_status', [139, 192, 156, 170])
    //             ->whereNotNull('ae_am_id')
    //             ->whereBetween('ae_date', [$startDate, $endDate])
    //             ->orderBy('ae_date', 'desc')
    //             ->limit(3)
    //             ->get()
    //             ->map(function ($exception) {
    //                 return [
    //                     'employee_name' => $exception->employee ? $exception->employee->emp_full_name : 'Unknown',
    //                     'employee_code' => $exception->employee ? $exception->employee->emp_code : 'N/A',
    //                     'date' => Carbon::parse($exception->ae_date)->format('M d'),
    //                     'type' => 'Missed Punch'
    //                 ];
    //             });

    //             // dd($businessId,$payrollPeriodId,$missPunchPending,$startDate,$endDate,$missPunchDetails);
    //         // Leave Requests (assuming LeaveRequest model exists)
    //         $leavePending = 0;
    //         $leaveDetails = [];

    //         // Check if LeaveRequest model exists
    //         if (class_exists('App\\Models\\LeaveRequest')) {
    //             $leavePending = LeaveRequest::where('lvr_b_id', $businessId)
    //                 ->where('lvr_stage_completed', 0)
    //                 ->whereNull('lvr_p_id')
    //                 ->whereNotIn('lvr_status', [170])
    //                 ->where(function ($query) use ($startDate, $endDate) {
    //                     $query->whereBetween('lvr_start_date', [$startDate, $endDate])
    //                         ->orWhereBetween('lvr_end_date', [$startDate, $endDate])
    //                         ->orWhere(function ($subQuery) use ($startDate, $endDate) {
    //                             $subQuery->where('lvr_start_date', '<=', $startDate)
    //                                 ->where('lvr_end_date', '>=', $endDate);
    //                         });
    //                 })
    //                 ->count();

    //             $leaveDetails = LeaveRequest::with(['employee', 'leaveType'])
    //                 ->where('lvr_b_id', $businessId)
    //                 ->where('lvr_stage_completed', 0)
    //                 ->whereNull('lvr_p_id')
    //                 ->whereNotIn('lvr_status', [170])
    //                 ->where(function ($query) use ($startDate, $endDate) {
    //                     $query->whereBetween('lvr_start_date', [$startDate, $endDate])
    //                         ->orWhereBetween('lvr_end_date', [$startDate, $endDate])
    //                         ->orWhere(function ($subQuery) use ($startDate, $endDate) {
    //                             $subQuery->where('lvr_start_date', '<=', $startDate)
    //                                 ->where('lvr_end_date', '>=', $endDate);
    //                         });
    //                 })
    //                 ->orderBy('lvr_start_date', 'desc')
    //                 ->limit(3)
    //                 ->get()
    //                 ->map(function ($leave) {
    //                     $startDate = Carbon::parse($leave->lvr_start_date);
    //                     $endDate = Carbon::parse($leave->lvr_end_date);
    //                     $duration = $startDate->diffInDays($endDate) + 1;

    //                     return [
    //                         'employee_name' => $leave->employee ? $leave->employee->full_name : 'Unknown',
    //                         'employee_code' => $leave->employee ? $leave->employee->employee_code : 'N/A',
    //                         'leave_type' => $leave->leaveType ? $leave->leaveType->lvt_name : 'N/A',
    //                         'duration' => $duration . ' Day' . ($duration > 1 ? 's' : ''),
    //                         'start_date' => $startDate->format('M d'),
    //                         'end_date' => $endDate->format('M d')
    //                     ];
    //                 });
    //         }

    //         // Over Time Requests - USING YOUR TABLE STRUCTURE
    //         $overTimePending = DB::table('ot_approval_status')
    //             ->where('ot_b_id', $businessId)
    //             ->where('ot_stage_completed', 0)
    //             ->where(function ($query) {
    //                 $query->whereNull('ot_requested_status')
    //                     ->orWhere('ot_requested_status', '!=', 170); // Assuming 170 is approved/rejected status
    //             })
    //             ->whereBetween('ot_date', [$startDate, $endDate])
    //             ->count();

    //         // Over Time Details
    //         $overTimeDetails = DB::table('ot_approval_status as ot')
    //             ->leftJoin('employees as emp', 'ot.ot_atd_id', '=', 'emp.emp_id')
    //             ->where('ot.ot_b_id', $businessId)
    //             ->where('ot.ot_stage_completed', 0)
    //             ->where(function ($query) {
    //                 $query->whereNull('ot.ot_requested_status')
    //                     ->orWhere('ot.ot_requested_status', '!=', 170);
    //             })
    //             ->whereBetween('ot.ot_date', [$startDate, $endDate])
    //             ->orderBy('ot.ot_date', 'desc')
    //             ->limit(3)
    //             ->get()
    //             ->map(function ($ot) {
    //                 // Calculate overtime hours from attendance (you'll need to adjust this based on your actual logic)
    //                 $hours = 0;
    //                 // Assuming you have attendance data to calculate actual OT hours
    //                 // $attendance = Attendance::find($ot->ot_atd_id);
    //                 // $hours = $attendance ? $attendance->ot_hours : 0;

    //                 return [
    //                     'employee_name' => $ot->emp_full_name ?? 'Unknown',
    //                     'employee_code' => $ot->emp_code ?? 'N/A',
    //                     'date' => Carbon::parse($ot->ot_date)->format('M d'),
    //                     'hours' => $hours,
    //                     'type' => $ot->ot_atd_type ?? 'Overtime'
    //                 ];
    //             });

    //         // Comp Off Requests (assuming similar table structure)
    //         $compOffPending = 0;
    //         $compOffDetails = [];

    //         // Early Departures (calculate from attendance exceptions)
    //         // $earlyDeparturePending = AttendanceException::where('ae_b_id', $businessId)
    //         //     ->where('ae_stage_completed', 0)
    //         //     ->where('ae_type', 'early_departure') // Assuming you have this field
    //         //     ->whereBetween('ae_date', [$startDate, $endDate])
    //         //     ->count();

    //         // $earlyDepartureDetails = AttendanceException::with(['employee'])
    //         //     ->where('ae_b_id', $businessId)
    //         //     ->where('ae_stage_completed', 0)
    //         //     ->where('ae_type', 'early_departure')
    //         //     ->whereBetween('ae_date', [$startDate, $endDate])
    //         //     ->orderBy('ae_date', 'desc')
    //         //     ->limit(3)
    //         //     ->get()
    //         //     ->map(function ($exception) {
    //         //         return [
    //         //             'employee_name' => $exception->employee ? $exception->employee->full_name : 'Unknown',
    //         //             'employee_code' => $exception->employee ? $exception->employee->employee_code : 'N/A',
    //         //             'date' => Carbon::parse($exception->ae_date)->format('M d'),
    //         //             'duration' => $exception->ae_duration ?? '--',
    //         //             'type' => 'Early Departure'
    //         //         ];
    //         //     });

    //         // // Late Arrivals (calculate from attendance exceptions)
    //         // $lateArrivalPending = AttendanceException::where('ae_b_id', $businessId)
    //         //     ->where('ae_stage_completed', 0)
    //         //     ->where('ae_type', 'late_arrival') // Assuming you have this field
    //         //     ->whereBetween('ae_date', [$startDate, $endDate])
    //         //     ->count();

    //         // $lateArrivalDetails = AttendanceException::with(['employee'])
    //         // ->where('ae_b_id', $businessId)
    //         // ->where('ae_stage_completed', 0)
    //         // ->where('ae_type', 'late_arrival')
    //         // ->whereBetween('ae_date', [$startDate, $endDate])
    //         // ->orderBy('ae_date', 'desc')
    //         // ->limit(3)
    //         // ->get()
    //         // ->map(function ($exception) {
    //         //     return [
    //         //         'employee_name' => $exception->employee ? $exception->employee->full_name : 'Unknown',
    //         //         'employee_code' => $exception->employee ? $exception->employee->employee_code : 'N/A',
    //         //         'date' => Carbon::parse($exception->ae_date)->format('M d'),
    //         //         'duration' => $exception->ae_duration ?? '--',
    //         //         'type' => 'Late Arrival'
    //         //     ];
    //         // });

    //         // Calculate total pending
    //         $totalPending = $missPunchPending + $leavePending + $overTimePending +
    //             $compOffPending;

    //         return response()->json([
    //             'success' => true,
    //             'data' => [
    //                 'missed_punches' => [
    //                     'count' => $missPunchPending,
    //                     'details' => $missPunchDetails,
    //                     'exists' => $missPunchPending > 0
    //                 ],
    //                 'leave_requests' => [
    //                     'count' => $leavePending,
    //                     'details' => $leaveDetails,
    //                     'exists' => $leavePending > 0
    //                 ],
    //                 'overtime_requests' => [
    //                     'count' => $overTimePending,
    //                     'details' => $overTimeDetails,
    //                     'exists' => $overTimePending > 0
    //                 ],
    //                 'comp_off_requests' => [
    //                     'count' => $compOffPending,
    //                     'details' => $compOffDetails,
    //                     'exists' => $compOffPending > 0
    //                 ],
    //                 // 'early_departures' => [
    //                 //     'count' => $earlyDeparturePending,
    //                 //     'details' => $earlyDepartureDetails,
    //                 //     'exists' => $earlyDeparturePending > 0
    //                 // ],
    //                 // 'late_arrivals' => [
    //                 //     'count' => $lateArrivalPending,
    //                 //     'details' => $lateArrivalDetails,
    //                 //     'exists' => $lateArrivalPending > 0
    //                 // ],
    //                 'total_pending' => $totalPending
    //             ]
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to fetch pending requests',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function payrunRetrieveAttendance(Request $request)
    {
        $request->validate([
            'payroll_id' => 'required|integer|exists:payroll_periods,pp_id',
        ]);

        $payrollPeriod = PayrollPeriod::findOrFail($request->payroll_id);

        // ✅ STEP 1: First update status to UNDER_REVIEW
        $oldStatus = $payrollPeriod->pp_status_code;

        // Update status to UNDER_REVIEW (jab bhi attendance retrieve ho)
        $payrollPeriod->pp_status_code = 'UNDER_REVIEW';
        $payrollPeriod->save();

        // FIX: Allow both PROCESSING and ATTENDANCE_FROZEN statuses
        $allowedStatuses = ['PROCESSING', 'ATTENDANCE_FROZEN', 'UNDER_REVIEW'];

        if (! in_array($payrollPeriod->pp_status_code, $allowedStatuses)) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot access attendance. Current status: '.$payrollPeriod->pp_status_code,
                'required_status' => 'PROCESSING or ATTENDANCE_FROZEN',
            ], 403);
        }

        $attendanceDetails = $this->getAttendanceDetails($payrollPeriod);

        $attendanceData = [];

        foreach ($attendanceDetails as $row) {
            $empId = $row['employee']->emp_id;

            $attendanceData[$empId] = [
                'emp_id' => $empId,
                'payroll_id' => $payrollPeriod->pp_id,

                // full summary snapshot
                'total_days' => $row['attendance_summary']['total_days'],
                'presentCount' => $row['attendance_summary']['presentCount'],
                'absentCount' => $row['attendance_summary']['absentCount'],
                'weekOffCount' => $row['attendance_summary']['weekOffCount'],
                'weekOffPresentCount' => $row['attendance_summary']['weekOffPresentCount'],
                'halfDayCount' => $row['attendance_summary']['halfDayCount'],
                'leaveCount' => $row['attendance_summary']['leaveCount'],
                'holidayCount' => $row['attendance_summary']['holidayCount'],
                'UPL' => $row['attendance_summary']['UPL'],
                'lateCount' => $row['attendance_summary']['lateCount'],
                'earlyExitCount' => $row['attendance_summary']['earlyExitCount'],
                'missedPunchCount' => $row['attendance_summary']['missedPunchCount'],
                'overtimeCount' => $row['attendance_summary']['overtimeCount'],
                'overtimeHours' => $row['attendance_summary']['overtimeHours'] ?? $row['attendance_summary']['overtimeCount'],
                'total' => $row['attendance_summary']['total'],
            ];
        }

        return response()->json([
            'attendanceData' => $attendanceData,
            'html' => view(
                'admin.payroll.partials.attendance-rows',
                compact('attendanceDetails')
            )->render(),
        ]);
    }


    public function payrunfreezeAttendanceChunk(Request $request)
    {
        try {
            // Get JSON input
            $data = $request->json()->all();

            $validator = Validator::make($data, [
                'payroll_id' => 'required|integer',
                'attendance_chunk' => 'required|array',
                'attendance_chunk.*.emp_id' => 'required',
                'chunk_number' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $attendanceChunk = $data['attendance_chunk'];
            $payrollId = $data['payroll_id'];
            $chunkNumber = $data['chunk_number'] ?? 1;

            DB::beginTransaction();

            try {
                $payrollPeriod = PayrollPeriod::find($payrollId);

                if (!$payrollPeriod) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Payroll period not found',
                    ], 404);
                }

                // Check if status allows freezing
                if (!in_array($payrollPeriod->pp_status_code, ['PROCESSING', 'UNDER_REVIEW', 'IN-PROCESS'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot access attendance. Current status: ' . $payrollPeriod->pp_status_code,
                    ], 403);
                }

                $startDate = $payrollPeriod->pp_start_date;
                $endDate = $payrollPeriod->pp_end_date;
                $otEnabled = OvertimePolicy::where('ot_b_id', $payrollPeriod->pp_b_id)->value('ot_is_enabled');

                $processedCount = 0;
                $pendingEmployees = [];
                $pendingMissPunchEmployees = [];
                $notFoundEmployees = [];

                foreach ($attendanceChunk as $row) {
                    if (empty($row['emp_id']) && empty($row['emp_code'])) {
                        continue;
                    }

                    // CRITICAL FIX: Find employee by BOTH emp_id and emp_code
                    // Since your data has emp_id as string like "BVM134", we need to search by emp_code
                    $employee = Employee::select('emp_id', 'emp_b_id', 'emp_br_id', 'emp_d_id')
                        ->where(function ($query) use ($row) {
                            // Try to find by emp_code first (since your data uses emp_code values)
                            if (!empty($row['emp_code'])) {
                                $query->where('emp_code', $row['emp_code']);
                            }
                            // Also try by emp_id if it's numeric
                            if (!empty($row['emp_id']) && is_numeric($row['emp_id'])) {
                                $query->orWhere('emp_id', $row['emp_id']);
                            }
                            // If emp_id is string like "BVM134", treat it as emp_code
                            if (!empty($row['emp_id']) && !is_numeric($row['emp_id'])) {
                                $query->orWhere('emp_code', $row['emp_id']);
                            }
                        })
                        ->first();

                    if (!$employee) {
                        $notFoundEmployees[] = $row['emp_code'] ?? $row['emp_id'] ?? 'Unknown';
                        \Log::warning('Employee not found', [
                            'emp_code' => $row['emp_code'] ?? null,
                            'emp_id' => $row['emp_id'] ?? null
                        ]);
                        continue;
                    }

                    // Debug log to verify employee found
                    \Log::info('Processing employee', [
                        'emp_id' => $employee->emp_id,
                        'emp_code' => $row['emp_code'] ?? $row['emp_id']
                    ]);

                    // Calculate worked days properly (handle decimal values like 23.5)
                    $totalDays = (float)($row['total_days'] ?? 0);
                    $presentCount = (float)($row['presentCount'] ?? 0);
                    $halfDayCount = (float)($row['halfDayCount'] ?? 0);
                    $leaveCount = (float)($row['leaveCount'] ?? 0);
                    $absentCount = (float)($row['absentCount'] ?? 0);
                    $weekOffCount = (float)($row['weekOffCount'] ?? 0);
                    $weekOffPresentCount = (float)($row['weekOffPresentCount'] ?? 0);
                    $holidayCount = (float)($row['holidayCount'] ?? 0);
                    $lateCount = (float)($row['lateCount'] ?? 0);
                    $earlyExitCount = (float)($row['earlyExitCount'] ?? 0);
                    $missedPunchCount = (float)($row['missedPunchCount'] ?? 0);
                    $overtimeCount = (float)($row['overtimeHours'] ?? $row['overtimeCount'] ?? 0);
                    $uplCount = (float)($row['UPL'] ?? 0);

                    // Keep salary days in sync with Freeze table "total" column.
                    // If frontend sends explicit total, persist that exact value.
                    $totalWorkedDays = isset($row['total'])
                        ? (float) $row['total']
                        : ($presentCount + ($halfDayCount * 0.5));

                    // Check leave pending (only if not already absent)
                    $leavePending = false;
                    if ($leaveCount == 0) {
                        $leavePending = LeaveRequest::where('lvr_b_id', $employee->emp_b_id)
                            ->where('lvr_emp_id', $employee->emp_id)
                            ->where('lvr_stage_completed', 0)
                            ->whereNull('lvr_p_id')
                            ->whereNotIn('lvr_status', [170])
                            ->where(function ($q) use ($startDate, $endDate) {
                                $q->whereBetween('lvr_start_date', [$startDate, $endDate])
                                    ->orWhereBetween('lvr_end_date', [$startDate, $endDate])
                                    ->orWhere(function ($sq) use ($startDate, $endDate) {
                                        $sq->where('lvr_start_date', '<=', $startDate)
                                            ->where('lvr_end_date', '>=', $endDate);
                                    });
                            })
                            ->exists();
                    }

                    if ($leavePending) {
                        $pendingEmployees[] = $employee->emp_id;
                        continue;
                    }

                    // Check miss punch pending
                    $missPunchPending = AttendanceException::where('ae_b_id', $employee->emp_b_id)
                        ->where('ae_emp_id', $employee->emp_id)
                        ->where('ae_stage_completed', 0)
                        ->whereNotIn('ae_status', [139, 192, 156, 170])
                        ->whereNotNull('ae_am_id')
                        ->whereBetween('ae_date', [$startDate, $endDate])
                        ->exists();

                    if ($missPunchPending) {
                        $pendingMissPunchEmployees[] = $employee->emp_id;
                        continue;
                    }

                    // Calculate year_month from payroll period
                    $yearMonth = date('Y-m', strtotime($payrollPeriod->pp_start_date));

                    // Save attendance summary
                    $attendanceSummary = AttendanceSummary::updateOrCreate(
                        [
                            'as_emp_id' => $employee->emp_id,
                            'as_pp_id' => $payrollId,
                        ],
                        [
                            'as_year_month' => $yearMonth,
                            'as_br_id' => $employee->emp_br_id,
                            'as_d_id' => $employee->emp_d_id,
                            'as_b_id' => $employee->emp_b_id,
                            'as_total_days' => $totalDays,
                            'as_total_present' => $presentCount,
                            'as_total_half_day' => $halfDayCount,
                            'as_total_absent' => $absentCount,
                            'as_total_leave' => $leaveCount,
                            'as_total_weekoff' => $weekOffCount,
                            'as_total_weekoffPresent' => $weekOffPresentCount,
                            'as_total_holiday' => $holidayCount,
                            'as_total_missed_punch' => $missedPunchCount,
                            'as_days_late' => $lateCount,
                            'as_early_exit' => $earlyExitCount,
                            'as_total_overtime_hours' => $otEnabled ? $overtimeCount : 0,
                            'as_total_upl_count' => $uplCount,
                            'as_total_worked_days' => $totalWorkedDays,
                            'is_frozen' => 1,
                            'frozen_at' => now(),
                            'frozen_by' => auth()->id(),
                        ]
                    );

                    if ($attendanceSummary) {
                        $processedCount++;
                        \Log::info('Attendance saved successfully', [
                            'emp_id' => $employee->emp_id,
                            'emp_code' => $row['emp_code'] ?? $row['emp_id'],
                            'worked_days' => $totalWorkedDays
                        ]);
                    }
                }

                // Check for pending requests
                if (!empty($pendingEmployees) || !empty($pendingMissPunchEmployees)) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Pending Leave / Miss Punch requests found',
                        'pending_leave_employees' => $pendingEmployees,
                        'pending_miss_punch_employees' => $pendingMissPunchEmployees,
                        'stop_processing' => true,
                    ], 422);
                }

                // Log if any employees were not found
                if (!empty($notFoundEmployees)) {
                    \Log::warning('Some employees were not found', [
                        'not_found' => $notFoundEmployees,
                        'chunk' => $chunkNumber
                    ]);
                }

                // Check if this is the last chunk
                $totalChunks = $data['total_chunks'] ?? 1;
                $isLastChunk = ($chunkNumber == $totalChunks);

                if ($isLastChunk) {
                    $payrollPeriod->pp_status_code = 'IN-PROCESS';
                    $payrollPeriod->pp_is_freezed = 120;
                    $payrollPeriod->save();

                    \Log::info('Payroll period finalized', [
                        'payroll_id' => $payrollId,
                        'status' => 'IN-PROCESS',
                        'total_processed' => $processedCount
                    ]);
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => $isLastChunk ? 'All attendance frozen successfully' : "Chunk {$chunkNumber} processed successfully",
                    'processed_count' => $processedCount,
                    'not_found_count' => count($notFoundEmployees),
                    'not_found_employees' => $notFoundEmployees,
                    'chunk_number' => $chunkNumber,
                    'is_last_chunk' => $isLastChunk,
                    'payroll_id' => $payrollId,
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Attendance chunk freeze failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'chunk_number' => $chunkNumber,
                    'payroll_id' => $payrollId,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Database error: ' . $e->getMessage(),
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Invalid request format', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid request format: ' . $e->getMessage(),
            ], 400);
        }
    }

    public function payrunfreezeAttendanceOld(Request $request)
    {
        // ---------------- JSON Validation ----------------
        $validator = Validator::make($request->all(), [
            'payroll_id' => 'required|integer',
            'all_attendance_data' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $attendanceData = $request->all_attendance_data;
        $payrollId = $request->payroll_id;

        DB::beginTransaction();

        try {

            $pendingEmployees = [];
            $pendingMissPunchEmployees = [];

            // ---------------- Payroll Period ----------------
            $payrollPeriod = PayrollPeriod::find($payrollId);

            if (! $payrollPeriod->canAccessStep2()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot access attendance. Current status: '.$payrollPeriod->pp_status_code,
                    'required_status' => 'PROCESSING or UNDER-REVIEW',
                ], 403);
            }

            // $payrollPeriod->pp_status_code = 'IN-PROCESS';
            // $payrollPeriod->save(); // यहाँ सीधे update करें

            $startDate = $payrollPeriod->pp_start_date;
            $endDate = $payrollPeriod->pp_end_date;

            $otEnabled = OvertimePolicy::where('ot_b_id', $payrollPeriod->pp_b_id)->value('ot_is_enabled');
            foreach ($attendanceData as $row) {

                if (empty($row['emp_id']) && empty($row['emp_code'])) {
                    continue;
                }

                // ---------------- Employee Fetch ----------------
                $employee = Employee::select('emp_id', 'emp_b_id', 'emp_br_id', 'emp_d_id')
                    ->when(
                        ! empty($row['emp_id']),
                        fn ($q) => $q->where('emp_id', $row['emp_id'])
                    )
                    ->when(
                        empty($row['emp_id']) && ! empty($row['emp_code']),
                        fn ($q) => $q->where('emp_code', $row['emp_code'])
                    )
                    ->first();

                if (! $employee) {
                    continue;
                }

                // ---------------- Leave Pending ----------------
                $leavePending = LeaveRequest::where('lvr_b_id', $employee->emp_b_id)
                    ->where('lvr_stage_completed', 0)
                    ->whereNull('lvr_p_id')
                    ->whereNotIn('lvr_status', [170])
                    ->where(function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('lvr_start_date', [$startDate, $endDate])
                            ->orWhereBetween('lvr_end_date', [$startDate, $endDate])
                            ->orWhere(function ($sq) use ($startDate, $endDate) {
                                $sq->where('lvr_start_date', '<=', $startDate)
                                    ->where('lvr_end_date', '>=', $endDate);
                            });
                    })
                    ->exists();

                if ($leavePending) {
                    $pendingEmployees[] = $employee->emp_id;
                }

                // ---------------- Miss Punch Pending ----------------
                $missPunchPending = AttendanceException::where('ae_b_id', $employee->emp_b_id)
                    ->where('ae_stage_completed', 0)
                    ->whereNotIn('ae_status', [139, 192, 156, 170])
                    ->whereNotNull('ae_am_id')
                    ->whereBetween('ae_date', [$startDate, $endDate])
                    ->exists();

                if ($missPunchPending) {
                    $pendingMissPunchEmployees[] = $employee->emp_id;
                }

                // ❌ Stop if any pending
                if ($leavePending || $missPunchPending) {
                    DB::rollBack();

                    return response()->json([
                        'success' => false,
                        'message' => 'Pending Leave / Miss Punch requests found',
                        'pending_leave_employees' => $pendingEmployees,
                        'pending_miss_punch_employees' => $pendingMissPunchEmployees,
                    ], 422);
                }

                // ---------------- Save Attendance ----------------
                AttendanceSummary::updateOrCreate(
                    [
                        'as_emp_id' => $employee->emp_id,
                        'as_pp_id' => $payrollId,
                    ],
                    [
                        'as_year_month' => date('Y-m'),
                        'as_br_id' => $employee->emp_br_id,
                        'as_d_id' => $employee->emp_d_id,
                        'as_b_id' => $employee->emp_b_id,

                        'as_total_days' => (float) ($row['total_days'] ?? 0),
                        'as_total_present' => (float) ($row['presentCount'] ?? 0),
                        'as_total_half_day' => (float) ($row['halfDayCount'] ?? 0),
                        'as_total_absent' => (float) ($row['absentCount'] ?? 0),
                        'as_total_leave' => (float) ($row['leaveCount'] ?? 0),
                        'as_total_weekoff' => (float) ($row['weekOffCount'] ?? 0),
                        'as_total_weekoffPresent' => (float) ($row['weekOffPresentCount'] ?? 0),
                        'as_total_holiday' => (float) ($row['holidayCount'] ?? 0),
                        'as_total_missed_punch' => (float) ($row['missedPunchCount'] ?? 0),
                        'as_days_late' => (float) ($row['lateCount'] ?? 0),
                        'as_early_exit' => (float) ($row['earlyExitCount'] ?? 0),
                        'as_total_overtime_hours' => $otEnabled ? (float) ($row['overtimeHours'] ?? $row['overtimeCount'] ?? 0) : 0,
                        // 'as_total_overtime_hours' => (float) ($row['overtimeCount'] ?? 0),
                        'as_total_upl_count' => (float) ($row['UPL'] ?? 0),
                        'as_total_worked_days' => (float) ($row['total'] ?? 0),

                        'as_is_frozen' => 1,
                        'as_frozen_at' => now(),
                        'as_frozen_by' => auth()->id(),
                    ]
                );
            }

            // ---------------- Freeze Payroll ----------------
            $payrollPeriod->update(['pp_is_freezed' => 120]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Attendance frozen successfully',
                'status' => $payrollPeriod->pp_status_code,
                'employee_count' => count($attendanceData),
                'payroll_id' => $payrollId,
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Attendance freeze failed', [
                'error' => $e->getMessage(),
                'payroll_id' => $payrollId,
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if attendance is already frozen
     */
    public function checkAttendanceFrozen(Request $request)
    {
        $payrollId = $request->input('payroll_id');

        $isFrozen = DB::table('payroll_periods')
            ->where('pp_id', $payrollId)
            ->value('attendance_frozen');

        return response()->json([
            'is_frozen' => (bool) $isFrozen,
            'payroll_id' => $payrollId,
        ]);
    }

    /**
     * Unfreeze attendance (if needed for corrections)
     */
    public function payrunUnfreezeAttendance(Request $request, $id)
    {
        $business_id = auth()->user()->emp_b_id;

        try {
            // Delete attendance summaries for this payroll that are not yet processed
            $count = AttendanceSummary::where('as_pp_id', $id)
                ->where('as_b_id', $business_id)
                ->where(function ($query) {
                    $query->where('as_is_sal_processed', 121)
                        ->orWhereNull('as_is_sal_processed');
                })
                ->delete();

            $payrollPeriod = PayrollPeriod::where('pp_id', $id)
                ->where('pp_b_id', $business_id)
                ->first();

            if ($payrollPeriod) {
                // FIX: Set status to PENDING_APPROVAL when unfreezing
                $payrollPeriod->pp_status_code = 'UNDER_REVIEW';
                $payrollPeriod->pp_is_freezed = 121;
                $payrollPeriod->save();
            }

            return response()->json([
                'success' => true,
                'message' => "Attendance records successfully unfrozen.",
                'status' => $payrollPeriod ? $payrollPeriod->pp_status_code : 'UNKNOWN',
            ]);
        } catch (\Exception $e) {
            Log::error('Unfreeze Attendance Failed', [
                'payroll_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to unfreeze attendance. Please try again.',
            ], 500);
        }
    }

    public function getProcessSalaryStep3(Request $request)
    {
        $businessId = auth()->user()->emp_b_id;
        $payrollId = $request->payroll_id;

        // 1️⃣ Validate payroll + frozen check
        $payrollPeriod = PayrollPeriod::select(
            'pp_id',
            'pp_start_date',
            'pp_end_date',
            'pp_is_freezed',
            'pp_status_code'
        )
            ->where('pp_id', $payrollId)
            ->where('pp_b_id', $businessId)
            ->first();

        if (! $payrollPeriod) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance not frozen yet',
            ], 400);
        }

        if (! in_array($payrollPeriod->pp_status_code, ['PROCESSING', 'IN-PROCESS','VERIFICATION'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot process salaries. Current status: '.$payrollPeriod->pp_status_code,
                'current_status' => $payrollPeriod->pp_status_code,
                'required_status' => 'PROCESSING or IN-PROCESS',
            ], 403);
        }

        /* ─────────── PAYROLL PERIOD DATES ─────────── */
        $startDate = Carbon::parse($payrollPeriod->pp_start_date);
        $endDate = Carbon::parse($payrollPeriod->pp_end_date);
        $currentPayrollMonth = $startDate->format('Y-m');

        /* ─────────── PRELOAD REQUIRED DATA ─────────── */

        // Salary configuration
        $salaryMap = SalaryEmployeeSalary::where('es_b_id', $businessId)
            ->orderByDesc('es_id')
            ->get()
            ->unique('es_emp_id')
            ->pluck('es_base_salary', 'es_emp_id');

        // Held employees (ONE QUERY)
        $heldMap = \App\Models\SalaryHold::where('sh_b_id', $businessId)
            ->where('sh_pp_id', $payrollId)
            ->where('sh_status', 'held')
            ->pluck('sh_emp_id')
            ->flip();

        /* ─────────── ADHOC TRANSACTIONS PROCESSING ─────────── */
        $adhocTransactions = \App\Models\AdhocTransaction::with(['transaction_details.component'])
            ->where('at_pp_id', $payrollId)
            ->where('at_b_id', $businessId)
            ->get();

        $adhocData = [];
        $totalAdhocEarnings = 0;
        $totalAdhocDeductions = 0;
        $employeesWithAdhoc = 0;

        foreach ($adhocTransactions as $transaction) {
            $empId = $transaction->at_emp_id;

            // Initialize employee adhoc data
            if (! isset($adhocData[$empId])) {
                $adhocData[$empId] = [
                    'total_earnings' => 0,
                    'total_deductions' => 0,
                    'net_amount' => 0,
                    'count' => 0,
                    'details' => [],
                ];
            }

            // Process each transaction detail
            foreach ($transaction->transaction_details as $detail) {
                $earningAmount = floatval($detail->earning_amount ?? 0);
                $deductionAmount = floatval($detail->deduction_amount ?? 0);
                $netAmount = $earningAmount - $deductionAmount;

                // Update employee totals
                $adhocData[$empId]['total_earnings'] += $earningAmount;
                $adhocData[$empId]['total_deductions'] += $deductionAmount;
                $adhocData[$empId]['net_amount'] += $netAmount;
                $adhocData[$empId]['count']++;

                // Add detail record
                $adhocData[$empId]['details'][] = [
                    'component_id' => $detail->component_id,
                    'component_name' => $detail->component->ac_adhoc_component_name ?? 'Adjustment',
                    'earning_amount' => $earningAmount,
                    'deduction_amount' => $deductionAmount,
                    'net_amount' => $netAmount,
                    'is_earning' => $earningAmount > 0,
                    'is_recurring' => false, // Mark as non-recurring
                    'remarks' => $detail->remarks,
                    'transaction_date' => $transaction->created_at ?? null,
                ];

                // Update global totals
                $totalAdhocEarnings += $earningAmount;
                $totalAdhocDeductions += $deductionAmount;
            }

            // Count employees with adhoc
            if ($adhocData[$empId]['count'] > 0) {
                $employeesWithAdhoc++;
            }
        }

        /* ─────────── RECURRING TRANSACTIONS PROCESSING ─────────── */
        $recurringTransactions = \App\Models\RecurringTransaction::with(['recurringDetails.component'])
            ->where('rt_b_id', $businessId)
            ->where('rt_start_month', '<=', $currentPayrollMonth)
            ->where(function ($q) use ($currentPayrollMonth) {
                $q->whereNull('rt_end_month')
                    ->orWhere('rt_end_month', '>=', $currentPayrollMonth);
            })
            ->get();

        $recurringData = [];
        $totalRecurringEarnings = 0;
        $totalRecurringDeductions = 0;
        $employeesWithRecurring = 0;

        foreach ($recurringTransactions as $transaction) {
            $empId = $transaction->rt_emp_id;

            // Initialize employee recurring data
            if (! isset($recurringData[$empId])) {
                $recurringData[$empId] = [
                    'total_earnings' => 0,
                    'total_deductions' => 0,
                    'net_amount' => 0,
                    'count' => 0,
                    'details' => [],
                ];
            }

            // Process each recurring detail
            foreach ($transaction->recurringDetails as $detail) {
                $earningAmount = floatval($detail->rtd_earning_amount ?? 0);
                $deductionAmount = floatval($detail->rtd_deduction_amount ?? 0);
                $netAmount = $earningAmount - $deductionAmount;

                // Update employee totals
                $recurringData[$empId]['total_earnings'] += $earningAmount;
                $recurringData[$empId]['total_deductions'] += $deductionAmount;
                $recurringData[$empId]['net_amount'] += $netAmount;
                $recurringData[$empId]['count']++;

                // Add detail record
                $recurringData[$empId]['details'][] = [
                    'component_id' => $detail->component_id,
                    'component_name' => $detail->component->ac_adhoc_component_name ?? 'Recurring Component',
                    'earning_amount' => $earningAmount,
                    'deduction_amount' => $deductionAmount,
                    'net_amount' => $netAmount,
                    'is_earning' => $earningAmount > 0,
                    'is_recurring' => true, // Mark as recurring
                    'remarks' => $detail->remarks ?? 'Recurring transaction',
                    'start_month' => $transaction->rt_start_month,
                    'end_month' => $transaction->rt_end_month,
                ];

                // Update global totals
                $totalRecurringEarnings += $earningAmount;
                $totalRecurringDeductions += $deductionAmount;
            }

            // Count employees with recurring
            if ($recurringData[$empId]['count'] > 0) {
                $employeesWithRecurring++;
            }
        }


        $SALARY_NOT_PROCESSED = 121;
        $processableStatuses  = [71, 72, 457, 458, 459, 460];


         /* ───── attendance query ──── */
        $attendance = AttendanceSummary::join('employees', 'employees.emp_id', '=', 'attendance_summaries.as_emp_id')
            ->join('payroll_periods', 'payroll_periods.pp_id', '=', 'attendance_summaries.as_pp_id')
            ->join('master_table', 'master_table.m_id', '=', 'employees.emp_status')
            ->join('designations', 'designations.dg_id', '=', 'employees.emp_dg_id')
            ->join('departments', 'departments.d_id', '=', 'employees.emp_d_id')
            ->where('attendance_summaries.as_pp_id', $payrollId)
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
                'employees.emp_status',
                'employees.emp_br_id',
                'employees.emp_d_id',
                'master_table.m_name as status_name',
                'designations.dg_name as designation_name',
                'departments.d_name as department_name'
            )
            ->get();

        /* ─────────── MAIN ATTENDANCE QUERY WITH ALL DAYS DATA ─────────── */
        // $attendance = \App\Models\AttendanceSummary::join('employees', 'employees.emp_id', '=', 'attendance_summaries.as_emp_id')
        //     ->join('designations', 'designations.dg_id', '=', 'employees.emp_dg_id')
        //     ->join('departments', 'departments.d_id', '=', 'employees.emp_d_id')
        //     ->join('master_table', 'master_table.m_id', '=', 'employees.emp_status')
        //     ->where('attendance_summaries.as_pp_id', $payrollId)
        //     ->whereIn('employees.emp_status', [71, 72, 457, 458, 459, 460])
        //     ->select(
        //         // Employee info
        //         'employees.emp_id',
        //         'employees.emp_code',
        //         'employees.emp_full_name',
        //         'designations.dg_name as designation_name',
        //         'departments.d_name as department_name',
        //         'master_table.m_name as status_name',

        //         // All attendance days data
        //         'attendance_summaries.as_total_days',
        //         'attendance_summaries.as_total_worked_days',
        //         'attendance_summaries.as_total_present',
        //         'attendance_summaries.as_total_absent',
        //         'attendance_summaries.as_total_leave',
        //         'attendance_summaries.as_total_half_day',
        //         'attendance_summaries.as_total_weekoff',
        //         'attendance_summaries.as_total_weekoffPresent',
        //         'attendance_summaries.as_total_holiday',
        //         'attendance_summaries.as_total_missed_punch',
        //         'attendance_summaries.as_days_late',
        //         'attendance_summaries.as_early_exit',
        //         'attendance_summaries.as_total_overtime_hours',
        //         'attendance_summaries.as_total_upl_count'
        //     )
        //     ->get();

        /* ─────────── CALCULATE MONTH DAYS AND SALARY DAYS ─────────── */

        // Payroll period days calculation
        $totalMonthDays = $startDate->diffInDays($endDate) + 1;

        // Get holidays in this period
        $holidaysCount = PolicyHolidayList::where('phl_b_id', $businessId)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('phl_start_date', [$startDate, $endDate])
                    ->orWhereBetween('phl_end_date', [$startDate, $endDate])
                    ->orWhere(function ($sq) use ($startDate, $endDate) {
                        $sq->where('phl_start_date', '<=', $startDate)
                            ->where('phl_end_date', '>=', $endDate);
                    });
            })
            ->count();

        /* ─────────── FORMAT RESPONSE ─────────── */

        $employees = [];
        $totalSalary = 0;
        $totalBaseSalary = 0;
        $totalAdhocNet = 0;
        $totalRecurringNet = 0;

        foreach ($attendance as $row) {
            $hasSalaryConfigured = isset($salaryMap[$row->emp_id]);
            $isHeld = isset($heldMap[$row->emp_id]);
            $baseSalary = $hasSalaryConfigured ? floatval($salaryMap[$row->emp_id]) : 0.0;

            // Calculate month days and salary days
            $monthDays = $totalMonthDays;
            $weekOffDays = $row->as_total_weekoff;
            $holidayDays = $row->as_total_holiday;
            $leaveDays = $row->as_total_leave;
            $absentDays = $row->as_total_absent;
            $presentDays = $row->as_total_present;
            $halfDays = $row->as_total_half_day;

            // Calculate salary days (present + 0.5 of half days)
            $salaryDays = $row->as_total_worked_days;

            // Calculate worked percentage based on salary days
            $workedPct = $monthDays > 0 ? ($salaryDays / $monthDays) * 100 : 0;

            // Get adhoc data for this employee
            $empAdhoc = $adhocData[$row->emp_id] ?? [
                'total_earnings' => 0,
                'total_deductions' => 0,
                'net_amount' => 0,
                'count' => 0,
                'details' => [],
            ];

            // Get recurring data for this employee
            $empRecurring = $recurringData[$row->emp_id] ?? [
                'total_earnings' => 0,
                'total_deductions' => 0,
                'net_amount' => 0,
                'count' => 0,
                'details' => [],
            ];

            // Calculate salaries
            $proratedBase = round($baseSalary * ($workedPct / 100), 2);
            $netSalary = $proratedBase + $empAdhoc['net_amount'] + $empRecurring['net_amount'];

            // Combine all adjustments for tooltip/display
            $allAdjustments = array_merge(
                array_map(function ($item) {
                    $item['type'] = 'adhoc';

                    return $item;
                }, $empAdhoc['details']),
                array_map(function ($item) {
                    $item['type'] = 'recurring';

                    return $item;
                }, $empRecurring['details'])
            );

            $employees[] = [
                'emp_id' => $row->emp_id,
                'emp_code' => $row->emp_code,
                'emp_name' => $row->emp_full_name,
                'department' => $row->department_name,
                'designation' => $row->designation_name,
                'status_name' => $row->status_name,
                'emp_status' => $row->emp_status,
                'emp_status_name' => $row->status_name,
                'is_salary_configured' => $hasSalaryConfigured,
                'is_held' => $isHeld,

                // Month and Salary Days Data
                'month_days' => $monthDays,
                'salary_days' => round($salaryDays, 1),
                'present_days' => $presentDays,
                'half_days' => $halfDays,
                'absent_days' => $absentDays,
                'leave_days' => $leaveDays,
                'weekoff_days' => $weekOffDays,
                'holiday_days' => $holidayDays,
                'late_days' => $row->as_days_late,
                'early_exit_days' => $row->as_early_exit,
                'upl_days' => $row->as_total_upl_count,

                // Attendance summary
                'total_days' => $row->as_total_days,
                'worked_days' => $row->as_total_worked_days,
                'worked_percentage' => round($workedPct, 2),

                // Salary
                'base_salary' => $baseSalary,
                'prorated_base_salary' => $proratedBase,

                // Adhoc components
                'ad_hoc_amount' => $empAdhoc['net_amount'],
                'ad_hoc_earnings' => $empAdhoc['total_earnings'],
                'ad_hoc_deductions' => $empAdhoc['total_deductions'],
                'has_ad_hoc' => $empAdhoc['count'] > 0,
                'adhoc_count' => $empAdhoc['count'],

                // Recurring components
                'recurring_amount' => $empRecurring['net_amount'],
                'recurring_earnings' => $empRecurring['total_earnings'],
                'recurring_deductions' => $empRecurring['total_deductions'],
                'has_recurring' => $empRecurring['count'] > 0,
                'recurring_count' => $empRecurring['count'],

                // Total adjustments
                'total_adjustments' => $empAdhoc['net_amount'] + $empRecurring['net_amount'],
                'total_adjustment_earnings' => $empAdhoc['total_earnings'] + $empRecurring['total_earnings'],
                'total_adjustment_deductions' => $empAdhoc['total_deductions'] + $empRecurring['total_deductions'],

                // Net salary
                'net_salary' => $netSalary,

                // All adjustments details for tooltip
                'adjustment_details' => $allAdjustments,

                // UI formatted
                'formatted' => [
                    'base_salary' => '₹'.number_format($baseSalary, 2),
                    'prorated_base_salary' => '₹'.number_format($proratedBase, 2),
                    'ad_hoc' => $empAdhoc['net_amount'] != 0 ?
                        ($empAdhoc['net_amount'] > 0 ? '+' : '').'₹'.
                        number_format(abs($empAdhoc['net_amount']), 2) :
                        '₹0.00',
                    'recurring' => $empRecurring['net_amount'] != 0 ?
                        ($empRecurring['net_amount'] > 0 ? '+' : '').'₹'.
                        number_format(abs($empRecurring['net_amount']), 2) :
                        '₹0.00',
                    'total_adjustments' => ($empAdhoc['net_amount'] + $empRecurring['net_amount']) != 0 ?
                        (($empAdhoc['net_amount'] + $empRecurring['net_amount']) > 0 ? '+' : '').'₹'.
                        number_format(abs($empAdhoc['net_amount'] + $empRecurring['net_amount']), 2) :
                        '₹0.00',
                    'net_salary' => '₹'.number_format($netSalary, 2),
                    'month_days' => $monthDays.' days',
                    'salary_days' => round($salaryDays, 1).' days',
                    'attendance_summary' => "P: {$presentDays}, HD: {$halfDays}, L: {$leaveDays}, A: {$absentDays}",
                    'worked_days_display' => "{$row->as_total_worked_days}/{$row->as_total_days} days",
                    'adjustment_summary' => ($empAdhoc['count'] + $empRecurring['count']) > 0 ?
                        ($empAdhoc['count'] > 0 ? "{$empAdhoc['count']} adhoc" : '').
                        ($empAdhoc['count'] > 0 && $empRecurring['count'] > 0 ? ', ' : '').
                        ($empRecurring['count'] > 0 ? "{$empRecurring['count']} recurring" : '').
                        ' adjustments' :
                        'No adjustments',
                ],
            ];

            $totalSalary += $netSalary;
            $totalBaseSalary += $proratedBase;
            $totalAdhocNet += $empAdhoc['net_amount'];
            $totalRecurringNet += $empRecurring['net_amount'];
        }

        /* ─────────── RESPONSE ─────────── */

        $totalEmployees = count($employees);
        $totalAdhocNetAmount = $totalAdhocEarnings - $totalAdhocDeductions;
        $totalRecurringNetAmount = $totalRecurringEarnings - $totalRecurringDeductions;
        $totalAdjustmentsNet = $totalAdhocNetAmount + $totalRecurringNetAmount;

        return response()->json([
            'success' => true,
            'data' => [
                'employees' => $employees,
                'summary' => [
                    'total_net_amount' => $totalSalary,
                    'total_base_salary' => $totalBaseSalary,

                    // Adhoc Summary
                    'total_ad_hoc_amount' => $totalAdhocNetAmount,
                    'total_ad_hoc_earnings' => $totalAdhocEarnings,
                    'total_ad_hoc_deductions' => $totalAdhocDeductions,
                    'employees_with_adhoc' => $employeesWithAdhoc,

                    // Recurring Summary
                    'total_recurring_amount' => $totalRecurringNetAmount,
                    'total_recurring_earnings' => $totalRecurringEarnings,
                    'total_recurring_deductions' => $totalRecurringDeductions,
                    'employees_with_recurring' => $employeesWithRecurring,

                    // Combined Adjustments Summary
                    'total_adjustments' => $totalAdjustmentsNet,
                    'total_adjustment_earnings' => $totalAdhocEarnings + $totalRecurringEarnings,
                    'total_adjustment_deductions' => $totalAdhocDeductions + $totalRecurringDeductions,
                    'total_adjusted_employees' => count(array_unique(
                        array_merge(
                            array_keys($adhocData),
                            array_keys($recurringData)
                        )
                    )),

                    // Employee Counts
                    'total_employees' => $totalEmployees,
                    'percentage_with_adhoc' => $totalEmployees > 0 ?
                        round(($employeesWithAdhoc / $totalEmployees) * 100, 2) : 0,
                    'percentage_with_recurring' => $totalEmployees > 0 ?
                        round(($employeesWithRecurring / $totalEmployees) * 100, 2) : 0,
                    'percentage_with_adjustments' => $totalEmployees > 0 ?
                        round((count(array_unique(
                            array_merge(
                                array_keys($adhocData),
                                array_keys($recurringData)
                            )
                        )) / $totalEmployees) * 100, 2) : 0,

                    // Payroll Period Info
                    'payroll_period_days' => $totalMonthDays,
                    'total_holidays' => $holidaysCount,
                ],
                'payroll_period' => [
                    'id' => $payrollPeriod->pp_id,
                    'start_date' => $payrollPeriod->pp_start_date,
                    'end_date' => $payrollPeriod->pp_end_date,
                    'month_days' => $totalMonthDays,
                    'is_frozen' => true,
                    'month' => Carbon::parse($payrollPeriod->pp_start_date)->format('F Y'),
                ],
            ],
            'message' => 'Step 3 salary data loaded with adhoc and recurring adjustments',
        ]);
    }

    public function getProcessSalaryStep3Old(Request $request)
    {
        $businessId = auth()->user()->emp_b_id;
        $payrollId = $request->payroll_id;

        // 1️⃣ Validate payroll + frozen check
        $payrollPeriod = PayrollPeriod::select(
            'pp_id',
            'pp_start_date',
            'pp_end_date',
            'pp_is_freezed',
            'pp_status_code'
        )
            ->where('pp_id', $payrollId)
            ->where('pp_b_id', $businessId)
            // ->where('pp_is_freezed', 120)
            ->first();

        if (! $payrollPeriod) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance not frozen yet',
            ], 400);
        }

        if (! in_array($payrollPeriod->pp_status_code, ['PROCESSING', 'IN-PROCESS'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot process salaries. Current status: '.$payrollPeriod->pp_status_code,
                'current_status' => $payrollPeriod->pp_status_code,
                'required_status' => 'PROCESSING or IN-PROCESS',
            ], 403);
        }

        /* ─────────── PRELOAD REQUIRED DATA ─────────── */

        // Salary configuration
        $salaryMap = SalaryEmployeeSalary::where('es_b_id', $businessId)
            ->orderByDesc('es_id')
            ->get()
            ->unique('es_emp_id')
            ->pluck('es_base_salary', 'es_emp_id');

        // Held employees (ONE QUERY)
        $heldMap = \App\Models\SalaryHold::where('sh_b_id', $businessId)
            ->where('sh_pp_id', $payrollId)
            ->where('sh_status', 'held')
            ->pluck('sh_emp_id')
            ->flip();

        // Adhoc transactions for this payroll period
        $adhocTransactions = \App\Models\AdhocTransaction::with(['transaction_details.component'])
            ->where('at_pp_id', $payrollId)
            ->where('at_b_id', $businessId)
            ->get();

        // Find this section in getProcessSalaryStep3 method:

        $adhocData = [];
        $totalAdhocEarnings = 0;
        $totalAdhocDeductions = 0;
        $employeesWithAdhoc = 0;

        foreach ($adhocTransactions as $transaction) {
            $empId = $transaction->at_emp_id;

            // Initialize employee adhoc data
            if (! isset($adhocData[$empId])) {
                $adhocData[$empId] = [
                    'total_earnings' => 0,
                    'total_deductions' => 0,
                    'net_amount' => 0,
                    'count' => 0,
                    'details' => [],
                ];
            }

            // Process each transaction detail
            foreach ($transaction->transaction_details as $detail) {
                $earningAmount = floatval($detail->earning_amount ?? 0);
                $deductionAmount = floatval($detail->deduction_amount ?? 0);
                $netAmount = $earningAmount - $deductionAmount;

                // Update employee totals
                $adhocData[$empId]['total_earnings'] += $earningAmount;
                $adhocData[$empId]['total_deductions'] += $deductionAmount;
                $adhocData[$empId]['net_amount'] += $netAmount;
                $adhocData[$empId]['count']++;

                // Add detail record
                $adhocData[$empId]['details'][] = [
                    'component_id' => $detail->component_id,
                    'component_name' => $detail->component->ac_adhoc_component_name ?? 'Adjustment',
                    'earning_amount' => $earningAmount,
                    'deduction_amount' => $deductionAmount,
                    'net_amount' => $netAmount,
                    'is_earning' => $earningAmount > 0,
                    'remarks' => $detail->remarks,
                    'transaction_date' => $transaction->created_at ?? null,
                ];

                // Update global totals
                $totalAdhocEarnings += $earningAmount;
                $totalAdhocDeductions += $deductionAmount;
            }

            // Count employees with adhoc
            if ($adhocData[$empId]['count'] > 0) {
                $employeesWithAdhoc++;
            }
        }
        /* ─────────── MAIN ATTENDANCE QUERY WITH ALL DAYS DATA ─────────── */

        $attendance = \App\Models\AttendanceSummary::join('employees', 'employees.emp_id', '=', 'attendance_summaries.as_emp_id')
            ->join('designations', 'designations.dg_id', '=', 'employees.emp_dg_id')
            ->join('departments', 'departments.d_id', '=', 'employees.emp_d_id')
            ->join('master_table', 'master_table.m_id', '=', 'employees.emp_status')
            ->where('attendance_summaries.as_pp_id', $payrollId)
            ->whereIn('employees.emp_status', [71, 72, 457, 458, 459, 460])
            ->select(
                // Employee info
                'employees.emp_id',
                'employees.emp_code',
                'employees.emp_full_name',
                'designations.dg_name as designation_name',
                'departments.d_name as department_name',
                'master_table.m_name as status_name',

                // All attendance days data
                'attendance_summaries.as_total_days',
                'attendance_summaries.as_total_worked_days',
                'attendance_summaries.as_total_present',
                'attendance_summaries.as_total_absent',
                'attendance_summaries.as_total_leave',
                'attendance_summaries.as_total_half_day',
                'attendance_summaries.as_total_weekoff',
                'attendance_summaries.as_total_weekoffPresent',
                'attendance_summaries.as_total_holiday',
                'attendance_summaries.as_total_missed_punch',
                'attendance_summaries.as_days_late',
                'attendance_summaries.as_early_exit',
                'attendance_summaries.as_total_overtime_hours',
                'attendance_summaries.as_total_upl_count'
            )
            ->get();

        /* ─────────── CALCULATE MONTH DAYS AND SALARY DAYS ─────────── */

        // Payroll period days calculation
        $startDate = Carbon::parse($payrollPeriod->pp_start_date);
        $endDate = Carbon::parse($payrollPeriod->pp_end_date);
        $totalMonthDays = $startDate->diffInDays($endDate) + 1;

        // Get holidays in this period
        $holidaysCount = PolicyHolidayList::where('phl_b_id', $businessId)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('phl_start_date', [$startDate, $endDate])
                    ->orWhereBetween('phl_end_date', [$startDate, $endDate])
                    ->orWhere(function ($sq) use ($startDate, $endDate) {
                        $sq->where('phl_start_date', '<=', $startDate)
                            ->where('phl_end_date', '>=', $endDate);
                    });
            })
            ->count();

        /* ─────────── FORMAT RESPONSE ─────────── */

        $employees = [];
        $totalSalary = 0;
        $totalBaseSalary = 0;
        $totalAdhocNet = 0;
        foreach ($attendance as $row) {
            // skip salary not configured OR held
            if (! isset($salaryMap[$row->emp_id]) || isset($heldMap[$row->emp_id])) {
                continue;
            }

            $baseSalary = floatval($salaryMap[$row->emp_id]);

            // Calculate month days and salary days
            $monthDays = $totalMonthDays;
            $weekOffDays = $row->as_total_weekoff;
            $holidayDays = $row->as_total_holiday;
            $leaveDays = $row->as_total_leave;
            $absentDays = $row->as_total_absent;
            $presentDays = $row->as_total_present;
            $halfDays = $row->as_total_half_day;

            // Calculate salary days (present + 0.5 of half days)
            $salaryDays = $row->as_total_worked_days;

            // Calculate worked percentage based on salary days
            $workedPct = $monthDays > 0 ? ($salaryDays / $monthDays) * 100 : 0;

            // Get adhoc data for this employee
            $empAdhoc = $adhocData[$row->emp_id] ?? [
                'total_earnings' => 0,
                'total_deductions' => 0,
                'net_amount' => 0,
                'count' => 0,
                'details' => [],
            ];

            // Calculate salaries
            $proratedBase = round($baseSalary * ($workedPct / 100), 2);
            $netSalary = $proratedBase + $empAdhoc['net_amount'];

            $employees[] = [
                'emp_id' => $row->emp_id,
                'emp_code' => $row->emp_code,
                'emp_name' => $row->emp_full_name,
                'department' => $row->department_name,
                'designation' => $row->designation_name,
                'status_name' => $row->status_name,

                // Month and Salary Days Data
                'month_days' => $monthDays,
                'salary_days' => round($salaryDays, 1),
                'present_days' => $presentDays,
                'half_days' => $halfDays,
                'absent_days' => $absentDays,
                'leave_days' => $leaveDays,
                'weekoff_days' => $weekOffDays,
                'holiday_days' => $holidayDays,
                'late_days' => $row->as_days_late,
                'early_exit_days' => $row->as_early_exit,
                'upl_days' => $row->as_total_upl_count,

                // Attendance summary
                'total_days' => $row->as_total_days,
                'worked_days' => $row->as_total_worked_days,
                'worked_percentage' => round($workedPct, 2),

                // Salary
                'base_salary' => $baseSalary,
                'prorated_base_salary' => $proratedBase,
                'ad_hoc_amount' => $empAdhoc['net_amount'],
                'ad_hoc_earnings' => $empAdhoc['total_earnings'],
                'ad_hoc_deductions' => $empAdhoc['total_deductions'],
                'has_ad_hoc' => $empAdhoc['count'] > 0,
                'adhoc_count' => $empAdhoc['count'],
                'net_salary' => $netSalary,

                // Adhoc details for tooltip
                'adhoc_details' => $empAdhoc['details'],

                // UI formatted
                'formatted' => [
                    'base_salary' => '₹'.number_format($baseSalary, 2),
                    'prorated_base_salary' => '₹'.number_format($proratedBase, 2),
                    'ad_hoc' => $empAdhoc['net_amount'] != 0 ?
                        ($empAdhoc['net_amount'] > 0 ? '+' : '').'₹'.
                        number_format(abs($empAdhoc['net_amount']), 2) :
                        '₹0.00',
                    'net_salary' => '₹'.number_format($netSalary, 2),
                    'month_days' => $monthDays.' days',
                    'salary_days' => round($salaryDays, 1).' days',
                    'attendance_summary' => "P: {$presentDays}, HD: {$halfDays}, L: {$leaveDays}, A: {$absentDays}",
                    'worked_days_display' => "{$row->as_total_worked_days}/{$row->as_total_days} days",
                    'adhoc_summary' => $empAdhoc['count'] > 0 ?
                        "{$empAdhoc['count']} adjustment".($empAdhoc['count'] > 1 ? 's' : '') :
                        'No adjustments',
                ],
            ];

            $totalSalary += $netSalary;
            $totalBaseSalary += $proratedBase;
            $totalAdhocNet += $empAdhoc['net_amount'];
        }

        /* ─────────── RESPONSE ─────────── */

        $totalEmployees = count($employees);
        $totalAdhocNetAmount = $totalAdhocEarnings - $totalAdhocDeductions;

        return response()->json([
            'success' => true,
            'data' => [
                'employees' => $employees,
                'summary' => [
                    'total_net_amount' => $totalSalary,
                    'total_base_salary' => $totalBaseSalary,
                    'total_ad_hoc_amount' => $totalAdhocNet,
                    'total_ad_hoc_earnings' => $totalAdhocEarnings,
                    'total_ad_hoc_deductions' => $totalAdhocDeductions,
                    'employees_with_adhoc' => $employeesWithAdhoc,
                    'total_employees' => $totalEmployees,
                    'percentage_with_adhoc' => $totalEmployees > 0 ?
                        round(($employeesWithAdhoc / $totalEmployees) * 100, 2) : 0,
                    'payroll_period_days' => $totalMonthDays,
                    'total_holidays' => $holidaysCount,
                ],
                'payroll_period' => [
                    'id' => $payrollPeriod->pp_id,
                    'start_date' => $payrollPeriod->pp_start_date,
                    'end_date' => $payrollPeriod->pp_end_date,
                    'month_days' => $totalMonthDays,
                    'is_frozen' => true,
                    'month' => Carbon::parse($payrollPeriod->pp_start_date)->format('F Y'),
                ],
            ],
            'message' => 'Step 3 salary data loaded with days calculation',
        ]);
    }

    /**
     * Payroll chunk: extend PHP time limits and optionally cap batch size.
     * Set PAYROLL_CHUNK_MAX_EMPLOYEES in .env only if you need a hard cap (>0). Default 0 = no cap (e.g. 300+ per request).
     */
    protected function configureRuntimeForPayrollChunk(int $employeeCount): ?\Illuminate\Http\JsonResponse
    {
        @ini_set('max_execution_time', '0');
        set_time_limit(0);
        if (function_exists('ignore_user_abort')) {
            ignore_user_abort(true);
        }

        $hardMax = (int) env('PAYROLL_CHUNK_MAX_EMPLOYEES', 0);
        if ($hardMax > 0 && $employeeCount > $hardMax) {
            return response()->json([
                'success' => false,
                'message' => "Too many employees in one request (max {$hardMax}). Reduce chunk size or raise PAYROLL_CHUNK_MAX_EMPLOYEES in .env.",
                'max_employees' => $hardMax,
            ], 422);
        }

        return null;
    }

    /**
     * Reset execution timer periodically (helps long loops under some PHP/FPM setups).
     */
    protected function refreshPayrollChunkTimeLimit(): void
    {
        @ini_set('max_execution_time', '0');
        set_time_limit(0);
    }

    public function payrunprocessSalariesChunk(Request $request)
    {
        $employeeIds = $request->selected_employees;
        $payrollId = $request->pp_id;
        $business_id = auth()->user()->emp_b_id;
        $authUser = Auth::user()->emp_id;

        // Get chunk info from request
        $chunkNumber = $request->chunk_number ?? 1;
        $totalChunks = $request->total_chunks ?? 1;
        $processedEmployeeIds = $request->processed_employee_ids ?? [];

        $payrollPeriod = PayrollPeriod::where('pp_id', $payrollId)
            ->where('pp_b_id', $business_id)
            ->first();

        if (!$payrollPeriod) {
            return response()->json([
                'success' => false,
                'message' => 'Payroll period not found!',
            ], 404);
        }

        $startDate = Carbon::parse($payrollPeriod->pp_start_date);
        $endDate = Carbon::parse($payrollPeriod->pp_end_date);

        if (empty($employeeIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No employees selected',
            ], 400);
        }

        // Filter out already processed employees
        $employeesToProcess = array_values(array_diff($employeeIds, $processedEmployeeIds));

        if (empty($employeesToProcess)) {
            return response()->json([
                'success' => true,
                'message' => 'All employees in this chunk already processed',
                'processed_count' => 0,
                'processed_employee_ids' => $processedEmployeeIds,
                'is_last_chunk' => ($chunkNumber == $totalChunks),
            ]);
        }

        if ($response = $this->configureRuntimeForPayrollChunk(count($employeesToProcess))) {
            return $response;
        }

        $employees = Employee::with(['fh_business.fh_currency', 'fh_employee_salary'])
            ->select('employees.*')
            ->whereIn('emp_id', $employeesToProcess)
            ->where('emp_b_id', $business_id)
            ->get();

        if ($employees->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No employees found for the selected business!',
            ], 404);
        }

        $chunkEmpIds = $employees->pluck('emp_id')->all();
        $alreadyProcessedIds = ProcessedEmployeeSalary::where('ps_payroll_id', $payrollId)
            ->where('ps_b_id', $business_id)
            ->whereIn('ps_emp_id', $chunkEmpIds)
            ->pluck('ps_emp_id')
            ->all();
        $alreadyProcessedSet = array_fill_keys($alreadyProcessedIds, true);
        $releasedHoldsByEmp = SalaryHold::where('sh_b_id', $business_id)
            ->where('sh_pp_id', $payrollId)
            ->whereIn('sh_emp_id', $chunkEmpIds)
            ->where('sh_status', 'released')
            ->get()
            ->keyBy('sh_emp_id');

        $attendanceSummariesByEmp = AttendanceSummary::where('as_pp_id', $payrollId)
            ->where('as_b_id', $business_id)
            ->whereIn('as_emp_id', $chunkEmpIds)
            ->get()
            ->keyBy('as_emp_id');

        PayrollLogics::beginPayrollSalaryBatch((int) $business_id, (int) $payrollId, $chunkEmpIds, $attendanceSummariesByEmp);

        $processedCount = 0;
        $errors = [];
        $newProcessedIds = [];

        try {
            $loopIdx = 0;
            foreach ($employees as $employee) {
                $loopIdx++;
                if ($loopIdx % 25 === 0) {
                    $this->refreshPayrollChunkTimeLimit();
                }
                try {
                // Check if already processed in this session
                if (in_array($employee->emp_id, $processedEmployeeIds)) {
                    continue;
                }

                $eid = (int) $employee->emp_id;
                $employee_salary = SalaryEmployeeSalary::where('es_emp_id', $eid)->where('es_b_id', $business_id)->first();
                if (! $employee_salary) {
                    $employee_salary = SalaryEmployeeSalary::where('es_emp_id', $eid)->first();
                }

                $currency = $employee->fh_business->fh_currency->c_code
                    ?? $employee->fh_business->fh_currency->c_currency_code
                    ?? 'INR';

                if (!$employee_salary) {
                    $errors[] = "Salary record not found for Employee: {$employee->emp_name}";
                    continue;
                }

                // Check if already exists in database
                if (isset($alreadyProcessedSet[$employee->emp_id])) {
                    $newProcessedIds[] = $employee->emp_id;
                    $processedCount++;
                    continue;
                }

                $heldSalary = $releasedHoldsByEmp->get($employee->emp_id);

                // Calculate salary based on payroll type
                if ($payrollPeriod->pp_type_id == 440) {
                    $salaryDetails = PayrollLogics::calculateMonthlySalary($employee, $startDate, $endDate, $employee_salary, $business_id, $payrollId);
                } else {
                    $salaryDetails = PayrollLogics::calculateDailySalary($employee, $startDate, $endDate, $employee_salary, $business_id, $payrollId);
                }

                // Add held salary if exists
                if ($heldSalary) {
                    $salaryDetails['gross_salary'] += $heldSalary->hs_amount;
                    $salaryDetails['total_earnings'] += $heldSalary->hs_amount;
                    $salaryDetails['net_salary'] += $heldSalary->hs_amount;
                    $salaryDetails['earnings_breakdown'][] = [
                        'type_id' => 'hold_' . $heldSalary->sh_id,
                        'earning_type' => 'Held Salary',
                        'amount' => number_format($heldSalary->hs_amount, 2, '.', ''),
                        'employee' => ['' => number_format($heldSalary->hs_amount, 2)],
                    ];
                    $heldSalary->update(['sh_status' => 'processed', 'sh_released_at' => now()]);
                }

                // Create processed salary record
                $processedSalary = ProcessedEmployeeSalary::create([
                    'ps_b_id' => $business_id,
                    'ps_br_id' => $employee->emp_br_id ?? null,
                    'ps_emp_id' => $employee->emp_id,
                    'ps_payroll_id' => $payrollId,
                    'ps_monthly_salary' => round((float)str_replace(',', '', $salaryDetails['monthly_salary']), 2),
                    'ps_basic_salary' => round((float)str_replace(',', '', $salaryDetails['basic_salary']), 2),
                    'ps_per_day_salary' => round((float)str_replace(',', '', $salaryDetails['per_day_salary']), 2),
                    'ps_worked_days_salary' => round((float)str_replace(',', '', $salaryDetails['worked_days_salary']), 2),
                    'ps_earnings' => round((float)str_replace(',', '', $salaryDetails['total_earnings']), 2),
                    'ps_employee_deductions' => round((float)str_replace(',', '', $salaryDetails['total_employee_deductions']), 2),
                    'ps_employer_deductions' => round((float)str_replace(',', '', $salaryDetails['total_employer_deductions']), 2),
                    'ps_monthly_gross' => round((float) str_replace(',', '', $salaryDetails['gross_salary'] ?? '0'), 2),
                    'ps_monthly_net_salary' => round((float)str_replace(',', '', $salaryDetails['net_salary']), 2),
                    'ps_monthly_ctc' => round((float)str_replace(',', '', $salaryDetails['monthly_ctc']), 2),
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

                // Save earnings breakdown (same pattern as PayrollPeriodController::processSalaries)
                foreach ($salaryDetails['earnings_breakdown'] as $earningType => $earningData) {
                    if (! is_array($earningData)) {
                        continue;
                    }
                    $tid = $earningData['type_id'] ?? null;
                    $psEarningTypeId = is_numeric($tid) ? (int) $tid : $tid;

                    ProcessedSalaryEarning::create([
                        'ps_id' => $processedSalary->ps_id,
                        'ps_earning_type' => $earningData['earning_type'] ?? 'Earning',
                        'ps_e_amount' => round((float) str_replace(',', '', (string) ($earningData['amount'] ?? 0)), 2),
                        'ps_earning_type_id' => $psEarningTypeId,
                    ]);
                }

                // Save deductions breakdown
                foreach ($salaryDetails['deductions_breakdown'] as $deductionType => $deductionData) {
                    // When breakdown is keyed by head (e.g., EPF/ESIC), preserve that key.
                    // Fallback to inner deduction_type only for numeric indexes.
                    if (is_int($deductionType)) {
                        $deductionType = $deductionData['deduction_type'] ?? 'Other';
                    }
                    $typeId = $deductionData['type_id'] ?? null;

                    // Employee Deductions
                    if (isset($deductionData['employee'])) {
                        foreach ($deductionData['employee'] as $key => $amount) {
                            $psDeductionType = $key === '' ? $deductionType : ($deductionType . ' - ' . $key);
                            ProcessedSalaryDeduction::create([
                                'ps_id' => $processedSalary->ps_id,
                                'ps_deduction_type' => $psDeductionType,
                                'ps_d_amount' => round((float)str_replace(',', '', $amount), 2),
                                'ps_d_category' => 'employee',
                                'ps_deduction_type_id' => $typeId,
                            ]);
                        }
                    }

                    // Employer Deductions
                    if (isset($deductionData['employer'])) {
                        foreach ($deductionData['employer'] as $key => $amount) {
                            $psDeductionType = $key === '' ? $deductionType : ($deductionType . ' - ' . $key);
                            ProcessedSalaryDeduction::create([
                                'ps_id' => $processedSalary->ps_id,
                                'ps_deduction_type' => $psDeductionType,
                                'ps_d_amount' => round((float)str_replace(',', '', $amount), 2),
                                'ps_d_category' => 'employer',
                                'ps_deduction_type_id' => $typeId,
                            ]);
                        }
                    }
                }

                try {
                    $payslipUrl = app(PayrollPeriodController::class)->generatePayslip($processedSalary, $payrollPeriod);
                    $processedSalary->update(['ps_payslip_url' => $payslipUrl]);
                } catch (\Throwable $e) {
                    \Log::warning('Payslip PDF failed for ps_id '.$processedSalary->ps_id.': '.$e->getMessage());
                }

                // Update attendance summary
                $summary = $attendanceSummariesByEmp->get($employee->emp_id);

                if ($summary) {
                    $summary->as_is_sal_processed = 120;
                    $summary->updated_at = now();
                    $summary->save();
                }

                $newProcessedIds[] = $employee->emp_id;
                $processedCount++;
            } catch (\Exception $e) {
                \Log::error('Salary processing error for employee ' . $employee->emp_id . ': ' . $e->getMessage());
                $errors[] = "Error processing {$employee->emp_name}: " . $e->getMessage();
            }
            }
        } finally {
            PayrollLogics::endPayrollSalaryBatch();
        }

        // Merge with previously processed IDs
        $allProcessedIds = array_merge($processedEmployeeIds, $newProcessedIds);
        $isLastChunk = ($chunkNumber == $totalChunks);

        // Update payroll status only on last chunk
        if ($isLastChunk && $processedCount > 0) {
            $payrollPeriod->pp_status_code = 'VERIFICATION';
            $payrollPeriod->save();
            $payrollPeriod->update(['pp_is_processed' => 120]);
        }

        $response = [
            'success' => true,
            'message' => $isLastChunk ? 'Salaries processed successfully!' : "Chunk {$chunkNumber} processed successfully",
            'status' => $payrollPeriod->pp_status_code,
            'processed_count' => $processedCount,
            'chunk_number' => $chunkNumber,
            'total_chunks' => $totalChunks,
            'is_last_chunk' => $isLastChunk,
            'payroll_id' => $payrollId,
            'processed_employee_ids' => $allProcessedIds,
        ];

        if ($isLastChunk) {
            $response['next_step'] = 'STEP5';
            $response['step_name'] = 'Salary Review';
            $response['step_description'] = 'Review processed salaries and download reports';
            $response['data'] = [
                'processed_employees' => count($allProcessedIds),
                'payroll_id' => $payrollId,
                'payroll_period' => $payrollPeriod->pp_start_date . ' to ' . $payrollPeriod->pp_end_date,
                'bank_sheet_available' => true,
                'reports_generated' => true,
            ];
        }

        if (!empty($errors)) {
            $response['details'] = implode('<br>', $errors);
        }

        return response()->json($response);
    }


    public function payrunprocessSalaries(Request $request)
    {
        $employeeIds = $request->selected_employees;
        $payrollId = $request->pp_id;
        $business_id = auth()->user()->emp_b_id;
        $authUser = Auth::user()->emp_id;

        $payrollPeriod = PayrollPeriod::where('pp_id', $payrollId)
            ->where('pp_b_id', $business_id)
            ->first();

        if (! $payrollPeriod) {
            return response()->json([
                'success' => false,
                'message' => 'Payroll period not found!',
            ], 404);
        }

        $startDate = Carbon::parse($payrollPeriod->pp_start_date);
        $endDate = Carbon::parse($payrollPeriod->pp_end_date);

        if (empty($employeeIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No employees selected',
            ], 400);
        }

        $employees = Employee::with('fh_business.fh_currency')
            ->select('employees.*')
            ->whereIn('emp_id', $employeeIds)
            ->where('emp_b_id', $business_id)
            ->get();

        if ($employees->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No employees found for the selected business!',
            ], 404);
        }

        $salaries = [];
        $processedCount = 0;
        $errors = [];

        foreach ($employees as $employee) {
            try {
                $eid = (int) $employee->emp_id;
                $employee_salary = SalaryEmployeeSalary::where('es_emp_id', $eid)->where('es_b_id', $business_id)->first();
                if (! $employee_salary) {
                    $employee_salary = SalaryEmployeeSalary::where('es_emp_id', $eid)->first();
                }
                $currency = $employee->fh_business->fh_currency->c_code
                    ?? $employee->fh_business->fh_currency->c_currency_code
                    ?? 'INR';

                if (! $employee_salary) {
                    $errors[] = "Salary record not found for Employee: {$employee->emp_name}";

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
                        'type_id' => 'hold_' . $heldSalary->sh_id,
                        'earning_type' => 'Held Salary',
                        'amount' => number_format($heldSalary->hs_amount, 2, '.', ''),
                        'employee' => ['' => number_format($heldSalary->hs_amount, 2)],
                    ];

                    // Mark held salary as released
                    $heldSalary->update(['sh_status' => 'processed', 'sh_released_at' => now()]);
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
                    'ps_monthly_gross' => round((float) str_replace(',', '', $salaryDetails['gross_salary'] ?? '0'), 2),
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
                    if (! is_array($earningData)) {
                        continue;
                    }
                    $tid = $earningData['type_id'] ?? null;
                    $psEarningTypeId = is_numeric($tid) ? (int) $tid : $tid;

                    ProcessedSalaryEarning::create([
                        'ps_id' => $processedSalary->ps_id,
                        'ps_earning_type' => $earningData['earning_type'] ?? 'Earning',
                        'ps_e_amount' => round((float) str_replace(',', '', (string) ($earningData['amount'] ?? 0)), 2),
                        'ps_earning_type_id' => $psEarningTypeId,
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
                            $psDeductionType = $key === '' ? $deductionType : ($deductionType . ' - ' . $key);

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
                            $psDeductionType = $key === '' ? $deductionType : ($deductionType . ' - ' . $key);

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

                $payslipUrl = null;
                try {
                    $payslipUrl = app(PayrollPeriodController::class)->generatePayslip($processedSalary, $payrollPeriod);
                    $processedSalary->update(['ps_payslip_url' => $payslipUrl]);
                } catch (\Throwable $e) {
                    \Log::warning('Payslip PDF failed for ps_id '.$processedSalary->ps_id.': '.$e->getMessage());
                }

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

                $processedCount++;
            } catch (\Exception $e) {
                $errors[] = "Error processing {$employee->emp_name}: " . $e->getMessage();
            }
        }

        // ✅ UPDATE: Change status to VERIFICATION (not IN-PROCESS)
        if ($processedCount > 0) {
            $payrollPeriod->pp_status_code = 'VERIFICATION';
            $payrollPeriod->save();
            $payrollPeriod->update(['pp_is_processed' => 120]);
        }

        // Prepare response with NEXT STEP information
        $response = [
            'success' => true,
            'message' => 'Salaries processed successfully!',
            'status' => $payrollPeriod->pp_status_code,
            'next_step' => 'STEP4', // ✅ यह बताता है कि next step STEP4 है
            'step_name' => 'Salary Review', // Step का नाम
            'step_description' => 'Review processed salaries and download reports',
            'processed_count' => $processedCount,
            'total_employees' => count($employees),
            'step_description' => 'Review processed salaries and download reports', // Description
            'data' => [
                'processed_employees' => $processedCount,
                'payroll_id' => $payrollId,
                'payroll_period' => $payrollPeriod->pp_start_date . ' to ' . $payrollPeriod->pp_end_date,
                'bank_sheet_available' => true,
                'reports_generated' => true,
            ],
        ];

        // Add errors if any
        if (! empty($errors)) {
            $response['details'] = implode('<br>', $errors);
        }

        return response()->json($response);
    }

    private function getAttendanceDetails(
        PayrollPeriod $payroll,
        int $limit = 100,
        int $offset = 0
    ) {
        $payrollStartDate = Carbon::parse($payroll->pp_start_date);
        $payrollEndDate = Carbon::parse($payroll->pp_end_date);

        $month = $payrollStartDate->format('m');
        $year = $payrollStartDate->format('Y');

        /**
         * 1️⃣ Load employees with LIMIT & OFFSET
         */
        // $employees = Employee::where('emp_b_id', $payroll->pp_b_id)
        //     ->where('emp_role_id', '!=', 1)
        //     ->where('emp_status', 71)
        //     ->orderBy('emp_id')
        //     ->limit($limit)
        //     ->offset($offset)
        //     ->get();


        $employees = Employee::where('emp_b_id', $payroll->pp_b_id)
            ->where('emp_role_id', '!=', 1)
            ->whereIn('emp_status', [71, 72, 457, 458, 459, 460]) // agar multiple status allowed hain
            ->whereDate('emp_date_of_joining', '<=', $payrollEndDate)
            ->where(function ($q) use ($payrollStartDate) {
                $q->whereNull('emp_last_working_date')
                    ->orWhereDate('emp_last_working_date', '>=', $payrollStartDate);
            })
            ->get();

        /**
         * 2️⃣ Load holidays ONCE
         */
        $holidayRecords = PolicyHolidayList::where('phl_b_id', $payroll->pp_b_id)
            ->where(function ($q) use ($payrollStartDate, $payrollEndDate) {
                $q->whereBetween('phl_start_date', [$payrollStartDate, $payrollEndDate])
                    ->orWhereBetween('phl_end_date', [$payrollStartDate, $payrollEndDate]);
            })->get();

        $holidaysByDate = collect();
        foreach ($holidayRecords as $holiday) {
            $start = Carbon::parse($holiday->phl_start_date);
            $end = Carbon::parse($holiday->phl_end_date);
            while ($start->lte($end)) {
                $holidaysByDate->put($start->toDateString(), true);
                $start->addDay();
            }
        }

        /**
         * 3️⃣ Weekoff policy ONCE
         */
        $weekoffPolicy = PolicyWeekOff::where('pwo_b_id', $payroll->pp_b_id)->first();
        $isUnpaidWeekOff = $weekoffPolicy->pwo_is_unpaid ?? 0;

        $attendanceDetails = [];

        /**
         * 4️⃣ Loop employees
         */
        foreach ($employees as $employee) {

            $weekOffDates = CentralLogics::getWeekOffDates(
                $employee,
                null,
                null,
                $payrollStartDate,
                $payrollEndDate
            );

            $attendanceSummary = CentralLogics::newGetMonthlyAttendanceDetails(
                $employee,
                $month,
                $year,
                $holidaysByDate,
                $weekOffDates
            );

            $weekOffSummary = CentralLogics::getWeekOffAttendanceSummary(
                $employee,
                $attendanceSummary,
                $month,
                $year
            );

            $totalUnpaidWeekOffCount = array_sum(
                array_column($weekOffSummary['weekOffSummary'], 'unpaidWeekOffCount')
            );

            $weekOffPresent =
                array_sum(array_column($weekOffSummary['weekOffSummary'], 'full_day_present_count')) +
                (array_sum(array_column($weekOffSummary['weekOffSummary'], 'half_day_present_count')) * 0.5);

            /**
             * 5️⃣ Fast aggregate (single pass)
             */
            $summary = collect($attendanceSummary);

            $presentCount = $summary->sum('presentCount');
            $halfDayCount = $summary->sum('halfDayCount');
            $leaveCount = $summary->sum('leaveCount');
            $absentCount = $summary->sum('absentCount');
            $holidayCount = $summary->sum('holidayCount');
            $weekOffCount = $summary->sum('weekOffCount');
            $missedPunchCount = $summary->sum('missedPunchCount');
            $lateCount = $summary->sum('lateCount');
            $earlyExitCount = $summary->sum('earlyExitCount');
            $overtimeCount = $summary->sum('overtimeCount');
            $UPL = $summary->sum('UPL');
            $overtimeHours = $summary->sum('OT');

            $totalDays = $payrollStartDate->diffInDays($payrollEndDate) + 1;
            $totalDays -= $totalUnpaidWeekOffCount;

            $total = $presentCount + $weekOffCount + $holidayCount + $leaveCount;

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
                    'UPL' => $UPL,
                    'overtimeHours' => $overtimeHours,
                    'total' => $total,
                ],
                'payroll_id' => $payroll->pp_id,
            ];
        }

        return $attendanceDetails;
    }

    public function payrunDownloadPayslip($id)
    {
        \Log::info('Download payslip called for ID: '.$id);
        $user = auth()->user();
        $processedSalary = ProcessedEmployeeSalary::findOrFail($id);
        $authUserId = $processedSalary->ps_generated_by;
        $authUser = Employee::find($authUserId)?->emp_full_name ?? 'System';
        $payrollPeriod = PayrollPeriod::findOrFail($processedSalary->ps_payroll_id);
        $employee = Employee::with([
            'fh_department',
            'fh_designation',
            'fh_branch',
            'fh_business.fh_admin',
        ])
            ->findOrFail($processedSalary->ps_emp_id);

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
        $employeeName = str_replace(' ', '', $employee->emp_full_name);

        // Force download
        return $pdf->download("Payslip-{$employeeName}.pdf");
    }

    public function payslipBatchView(Request $request, $payrollId)
    {
        $businessId = auth()->user()->emp_b_id;

        // Get payroll period
        $payrollPeriod = PayrollPeriod::with(['month', 'financialYear'])
            ->where('pp_id', $payrollId)
            ->where('pp_b_id', $businessId)
            ->firstOrFail();

        // Get processed employees for preview (limited to 5 records)
        $processedEmployees = ProcessedEmployeeSalary::with([
            'employee.fh_department',
            'employee.fh_designation',
        ])
            ->where('ps_payroll_id', $payrollId)
            ->where('ps_b_id', $businessId)
            ->orderBy('created_at', 'desc')
            ->limit(5) // Only show 5 records for preview
            ->get()
            ->map(function ($salary) {
                return [
                    'employee_id' => $salary->employee->emp_id ?? null,
                    'name' => $salary->employee->emp_full_name ?? 'N/A',
                    'code' => $salary->employee->emp_code ?? 'N/A',
                    'designation' => $salary->employee->fh_designation->dg_name ?? 'N/A',
                    'department' => $salary->employee->fh_department->d_name ?? 'N/A',
                    'net_salary' => number_format($salary->ps_monthly_net_salary ?? 0, 2),
                    'basic_salary' => number_format($salary->ps_basic_salary ?? 0, 2),
                    'earnings' => number_format($salary->ps_earnings ?? 0, 2),
                    'deductions' => number_format($salary->ps_employee_deductions ?? 0, 2),
                    'processed_date' => optional($salary->created_at)->format('d M Y') ?? 'N/A',
                    'status' => 'Processed',
                    'payslip_url' => $salary->ps_payslip_url ?? null,
                ];
            });
        // Get total count for "View All" button
        $totalProcessedCount = ProcessedEmployeeSalary::where('ps_payroll_id', $payrollId)
            ->where('ps_b_id', $businessId)
            ->count();

        return view('admin.payroll.payrun.payslip-batch-view', [
            'payrollPeriod' => $payrollPeriod,
            'processedEmployees' => $processedEmployees,
            'totalCount' => $totalProcessedCount,
            'showPreview' => true, // Flag for preview mode
        ]);
    }

    public function viewAllPayslips(Request $request, $payrollId)
    {
        $businessId = auth()->user()->emp_b_id;

        // Get payroll period
        $payrollPeriod = PayrollPeriod::with(['month', 'financialYear'])
            ->where('pp_id', $payrollId)
            ->where('pp_b_id', $businessId)
            ->firstOrFail();

        // Get processed employees with their processed salaries
        // Using fh_department and fh_designation instead of department/designation
        $processedEmployees = Employee::with([
            'fh_department',   // ✅ Using existing relationship
            'fh_designation',  // ✅ Using existing relationship
            'processedSalaries' => function ($query) use ($payrollId) {
                $query->where('ps_payroll_id', $payrollId);
            },
        ])
            ->whereHas('processedSalaries', function ($query) use ($payrollId) {
                $query->where('ps_payroll_id', $payrollId);
            })
            ->where('emp_b_id', $businessId)
            ->get()
            ->map(function ($employee) use ($payrollId) {
                // Get processed salary for this payroll period
                $employee->processedSalary = $employee->processedSalaries
                    ->where('ps_payroll_id', $payrollId)
                    ->first();

                // Also access department and designation directly for convenience
                $employee->department_name = $employee->fh_department->d_name ?? 'N/A';
                $employee->designation_name = $employee->fh_designation->dg_name ?? 'N/A';

                return $employee;
            });

        return view('admin.payroll.payrun.payslip-list', compact(
            'payrollPeriod',
            'processedEmployees'
        ));
    }

    public function getEmployeePayslip($employeeId, Request $request)
    {
        // Ensure it's an AJAX request
        if (! $request->ajax() && ! $request->wantsJson()) {
            abort(403, 'AJAX request required');
        }

        try {
            $payrollId = $request->input('payroll_id');
            $businessId = auth()->user()->emp_b_id;

            // Debug logging
            \Log::info('Payslip AJAX request', [
                'employee_id' => $employeeId,
                'payroll_id' => $payrollId,
                'business_id' => $businessId,
            ]);

            // Get employee with relations
            $employee = Employee::with([
                'fh_department',
                'fh_designation',
                'fh_business',
            ])->find($employeeId);

            if (! $employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found',
                ], 404);
            }

            // Get payroll period
            $payrollPeriod = PayrollPeriod::find($payrollId);

            if (! $payrollPeriod) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payroll period not found',
                ], 404);
            }

            // Get processed salary - CORRECTED TABLE NAME
            $processedSalary = ProcessedEmployeeSalary::where('ps_emp_id', $employeeId)
                ->where('ps_payroll_id', $payrollId)
                ->where('ps_b_id', $businessId)
                ->first();

            if (! $processedSalary) {
                return response()->json([
                    'success' => false,
                    'message' => 'Processed salary not found for this period',
                ], 404);
            }

            // Get earnings and deductions
            $earnings = ProcessedSalaryEarning::where('ps_id', $processedSalary->ps_id)->get();
            $deductions = ProcessedSalaryDeduction::where('ps_id', $processedSalary->ps_id)->get();

            // Generate HTML
            $html = view('admin.payroll.partials.payslip-preview', [
                'employee' => $employee,
                'payrollPeriod' => $payrollPeriod,
                'processedSalary' => $processedSalary,
                'earnings' => $earnings,
                'deductions' => $deductions,
            ])->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'employee' => [
                    'name' => $employee->emp_full_name,
                    'code' => $employee->emp_code,
                ],
            ]);

        } catch (\Exception $e) {
            \Log::error('Payslip error: '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine());

            return response()->json([
                'success' => false,
                'message' => 'Server error occurred: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getProcessedEmployees(Request $request)
    {
        try {
            $request->validate([
                'payroll_id' => 'required|integer|exists:payroll_periods,pp_id',
            ]);

            $payrollId = $request->payroll_id;
            $businessId = auth()->user()->emp_b_id;

            // Using Eloquent with() for eager loading relationships
            $processedSalaries = ProcessedEmployeeSalary::with([
                'employee.fh_department',
                'employee.fh_designation',
                'payrollPeriod',
            ])
                ->where('ps_payroll_id', $payrollId)
                ->where('ps_b_id', $businessId)
                ->orderBy('created_at', 'desc')
                ->get();

            // Check if data found
            if ($processedSalaries->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'processed_employees' => [],
                    'count' => 0,
                    'message' => 'No processed salaries found for this payroll period.',
                    'payroll_id' => $payrollId,
                ]);
            }

            // Transform data using Laravel collections
            $processedEmployees = $processedSalaries->map(function ($salary) {
                return [
                    'id' => $salary->employee->emp_id ?? null,
                    'name' => $salary->employee->emp_full_name ?? 'N/A',
                    'code' => $salary->employee->emp_code ?? 'N/A',
                    'designation' => $salary->employee->designation->dg_name ?? 'N/A',
                    'department' => $salary->employee->department->d_name ?? 'N/A',
                    'salary' => number_format($salary->ps_monthly_net_salary ?? 0, 2),
                    'processed_date' => optional($salary->created_at)->format('d M Y') ?? 'N/A',
                    'payroll_period' => $salary->payrollPeriod->pp_name ?? 'N/A',
                    'salary_id' => $salary->ps_id,
                    // Additional details
                    'basic_salary' => number_format($salary->ps_basic_salary ?? 0, 2),
                    'earnings' => number_format($salary->ps_earnings ?? 0, 2),
                    'employee_deductions' => number_format($salary->ps_employee_deductions ?? 0, 2),
                    'employer_deductions' => number_format($salary->ps_employer_deductions ?? 0, 2),
                    'gross_salary' => number_format($salary->ps_monthly_gross ?? 0, 2),
                    'worked_days' => $salary->ps_total_days_worked ?? 0,
                    'present_days' => $salary->ps_present_days ?? 0,
                    'late_days' => $salary->ps_days_late ?? 0,
                    'currency' => $salary->ps_currency ?? 'INR',
                ];
            });

            // Calculate summary statistics
            $totalSalary = $processedSalaries->sum('ps_monthly_net_salary');
            $totalEmployees = $processedSalaries->count();
            $averageSalary = $totalEmployees > 0 ? $totalSalary / $totalEmployees : 0;

            return response()->json([
                'success' => true,
                'processed_employees' => $processedEmployees,
                'count' => $totalEmployees,
                'payroll_id' => $payrollId,
                'summary' => [
                    'total_salary_amount' => number_format($totalSalary, 2),
                    'total_employees' => $totalEmployees,
                    'average_salary' => number_format($averageSalary, 2),
                    'currency' => $processedSalaries->first()->ps_currency ?? 'INR',
                ],
                'metadata' => [
                    'payroll_period' => optional($processedSalaries->first()->payrollPeriod)->pp_name ?? 'N/A',
                    'business_id' => $businessId,
                    'fetched_at' => now()->format('Y-m-d H:i:s'),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Get processed employees failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payroll_id' => $request->payroll_id ?? null,
                'business_id' => auth()->user()->emp_b_id ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get processed employees: '.$e->getMessage(),
                'processed_employees' => [],
                'error_details' => [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ],
            ], 500);
        }
    }

    public function payrunUnprocessSalaries(Request $request)
    {
        try {
            $request->validate([
                'payroll_id' => 'required|integer|exists:payroll_periods,pp_id',
                'selected_employees' => 'required|array',
                'selected_employees.*' => 'required|integer|exists:employees,emp_id',
            ]);

            $employeeIds = $request->selected_employees;
            $payrollId = $request->input('payroll_id');
            $businessId = auth()->user()->emp_b_id;

            $payrollPeriod = PayrollPeriod::with(['month', 'financialYear'])
                ->where('pp_id', $payrollId)
                ->where('pp_b_id', $businessId)
                ->firstOrFail();

            DB::beginTransaction();

            // Count before deletion for response
            $countBefore = ProcessedEmployeeSalary::where('ps_payroll_id', $payrollId)
                ->where('ps_b_id', $businessId)
                ->whereIn('ps_emp_id', $employeeIds)
                ->count();

            if ($countBefore === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No processed salaries found for selected employees.',
                ]);
            }

            // Delete processed salaries with related data
            ProcessedEmployeeSalary::with(['earnings', 'deductions'])
                ->where('ps_payroll_id', $payrollId)
                ->where('ps_b_id', $businessId)
                ->whereIn('ps_emp_id', $employeeIds)
                ->chunkById(100, function ($salaries) {
                    foreach ($salaries as $salary) {
                        // Delete related earnings if exists
                        if ($salary->earnings()->exists()) {
                            $salary->earnings()->delete();
                        }

                        // Delete related deductions if exists
                        if ($salary->deductions()->exists()) {
                            $salary->deductions()->delete();
                        }

                        // Delete the processed salary record
                        $salary->delete();
                    }
                });

            // Update attendance summaries to mark as unprocessed
            // Assuming 121 means "Pending" or "Unprocessed"
            DB::table('attendance_summaries')
                ->where('as_pp_id', $payrollId)
                ->where('as_b_id', $businessId)
                ->whereIn('as_emp_id', $employeeIds)
                ->update([
                    'as_is_sal_processed' => 121,
                    'updated_at' => now(),
                ]);

            // Update payroll period if all employees are unprocessed
            $remainingProcessed = ProcessedEmployeeSalary::where('ps_payroll_id', $payrollId)
                ->where('ps_b_id', $businessId)
                ->count();

            if ($remainingProcessed === 0) {
                // If ALL employees are unprocessed, revert to VERIFICATION status
                $currentStatus = 'IN-PROCESS';
                $payrollPeriod->pp_status_code = 'VERIFICATION';
                $newStatus = 'VERIFICATION';
                $statusUpdated = true;

                // Also reset other flags if needed
                $payrollPeriod->pp_is_processed = 0;
                $payrollPeriod->pp_is_finalized = 0;

            } else {
                // If only SOME employees are unprocessed, check if we should stay in current status
                // For IN-PROCESS status, we should revert to VERIFICATION when any salaries are unprocessed
                if (in_array($currentStatus, ['IN-PROCESS', 'VERIFICATION'])) {
                    $payrollPeriod->pp_status_code = 'VERIFICATION';
                    $newStatus = 'VERIFICATION';
                    $statusUpdated = true;
                }
            }

            $payrollPeriod->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Salaries unprocessed successfully.',
                'data' => [
                    'unprocessed_count' => $countBefore,
                    'remaining_processed' => $remainingProcessed,
                    'payroll_id' => $payrollId,
                    'previous_status' => $currentStatus,
                    'new_status' => $newStatus,
                    'status_updated' => $statusUpdated,
                    'status_message' => $statusUpdated
                        ? "Status reverted from {$currentStatus} to VERIFICATION"
                        : "Status remains {$currentStatus}",
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Unprocess salaries failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payroll_id' => $request->payroll_id ?? null,
                'employee_ids' => $request->selected_employees ?? [],
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to unprocess salaries: '.$e->getMessage(),
                'error_details' => [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ],
            ], 500);
        }
    }

    public function getHeldPeriods(Request $request)
    {
        $employeeId = $request->employee_id;

        $holds = SalaryHold::where('sh_emp_id', $employeeId)
            ->where('sh_status', 'held')
            ->with('payrollPeriod')
            ->get();

        $periods = [];
        foreach ($holds as $hold) {
            if ($hold->payrollPeriod) {
                $periods[] = [
                    'pp_id' => $hold->payrollPeriod->pp_id,
                    'pp_name' => $hold->payrollPeriod->pp_name,
                    'from_date' => $hold->payrollPeriod->from_date,
                    'to_date' => $hold->payrollPeriod->to_date,
                    'reason' => $hold->sh_reason,
                    'hold_until_release' => $hold->sh_hold_until_release,
                    'held_at' => $hold->sh_held_at,
                    'held_by' => $hold->heldBy->name ?? null,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'periods' => $periods,
        ]);
    }

    public function getPayrollPeriods(Request $request)
    {
        $employeeId = $request->employee_id;
        $currentPayrollId = $request->current_payroll_id;

        // Get all payroll periods (you might want to filter based on date range)
        $periods = PayrollPeriod::where('pp_b_id', $this->user->emp_b_id)
            ->orderBy('pp_start_date', 'desc')
            ->limit(12) // Last 12 periods
            ->get();

        $formattedPeriods = [];
        foreach ($periods as $period) {
            // Check if salary is already processed for this employee
            $isProcessed = ProcessedEmployeeSalary::where('ps_emp_id', $employeeId)
                ->where('ps_payroll_id', $period->pp_id)
                ->exists();

            // Check if salary is already held
            $isHeld = SalaryHold::where('sh_emp_id', $employeeId)
                ->where('sh_pp_id', $period->pp_id)
                ->where('sh_status', 'held')
                ->exists();

            $formattedPeriods[] = [
                'pp_id' => $period->pp_id,
                'pp_name' => $period->pp_name,
                'from_date' => $period->from_date,
                'to_date' => $period->to_date,
                'is_current_period' => $period->pp_id == $currentPayrollId,
                'is_processed' => $isProcessed,
                'is_held' => $isHeld,
            ];
        }

        return response()->json([
            'success' => true,
            'periods' => $formattedPeriods,
        ]);
    }

    public function getCurrentPayrollPeriod(Request $request)
    {
        try {
            $businessId = auth()->user()->emp_b_id;
            $employeeId = $request->input('employee_id');

            // Get current employee
            $employee = Employee::where('emp_id', $employeeId)
                ->where('emp_b_id', $businessId)
                ->first();

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found',
                ], 404);
            }

            // Get current active payroll period (status is PROCESSING or ATTENDANCE_FROZEN)
            $currentPeriod = PayrollPeriod::with(['month', 'financialYear'])
                ->where('pp_b_id', $businessId)
                ->whereIn('pp_status_code', ['PROCESSING', 'ATTENDANCE_FROZEN', 'IN-PROCESS', 'VERIFICATION'])
                ->orderBy('pp_start_date', 'desc')
                ->first();

            // If no active period, get the latest upcoming or completed period
            if (!$currentPeriod) {
                $currentPeriod = PayrollPeriod::with(['month', 'financialYear'])
                    ->where('pp_b_id', $businessId)
                    ->orderBy('pp_start_date', 'desc')
                    ->first();
            }

            if ($currentPeriod) {
                // Check if employee salary is already processed for this period
                $isProcessed = ProcessedEmployeeSalary::where('ps_emp_id', $employeeId)
                    ->where('ps_payroll_id', $currentPeriod->pp_id)
                    ->exists();

                // Check if salary is on hold for this period
                $isHeld = SalaryHold::where('sh_emp_id', $employeeId)
                    ->where('sh_pp_id', $currentPeriod->pp_id)
                    ->where('sh_status', 'held')
                    ->exists();

                // Get month name
                $monthName = $currentPeriod->month->m_name ?? date('F', strtotime($currentPeriod->pp_start_date));
                $year = $currentPeriod->financialYear->fy_year ?? date('Y', strtotime($currentPeriod->pp_start_date));

                return response()->json([
                    'success' => true,
                    'period' => [
                        'id' => $currentPeriod->pp_id,
                        'name' => $currentPeriod->pp_name ?? "{$monthName} {$year}",
                        'month' => $monthName,
                        'year' => $year,
                        'start_date' => date('d M Y', strtotime($currentPeriod->pp_start_date)),
                        'end_date' => date('d M Y', strtotime($currentPeriod->pp_end_date)),
                        'status' => $currentPeriod->pp_status_code,
                        'status_display' => $this->getStatusDisplay($currentPeriod->pp_status_code),
                        'is_processed' => $isProcessed,
                        'is_held' => $isHeld,
                        'can_hold' => !$isProcessed && !$isHeld,
                        'total_days' => \Carbon\Carbon::parse($currentPeriod->pp_start_date)
                            ->diffInDays(\Carbon\Carbon::parse($currentPeriod->pp_end_date)) + 1,
                    ],
                    'employee' => [
                        'id' => $employee->emp_id,
                        'name' => $employee->emp_full_name,
                        'code' => $employee->emp_code,
                        'department' => $employee->fh_department->d_name ?? 'N/A',
                        'designation' => $employee->fh_designation->dg_name ?? 'N/A',
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No payroll period found',
            ]);

        } catch (\Exception $e) {
            \Log::error('Get current payroll period error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load payroll period',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function getStatusDisplay($statusCode)
    {
        $statusMap = [
            'PROCESSING' => 'Processing',
            'ATTENDANCE_FROZEN' => 'Attendance Frozen',
            'IN-PROCESS' => 'In Process',
            'VERIFICATION' => 'Verification',
            'COMPLETED' => 'Completed',
            'PAYROLL_LOCKED' => 'Payroll Locked',
        ];

        return $statusMap[$statusCode] ?? $statusCode;
    }

    public function finalizePayroll(Request $request)
    {
        try {
            $request->validate([
                'payroll_id' => 'required|integer|exists:payroll_periods,pp_id',
                'period_id' => 'required|integer|exists:payroll_periods,pp_id',
            ]);

            $payrollPeriodId = $request->input('period_id');
            $payrollPeriod = PayrollPeriod::findOrFail($payrollPeriodId);

            // Check current status
            $currentStatus = $payrollPeriod->pp_status_code;

            // Define allowed statuses for finalization
            $allowedStatusesForFinalization = ['VERIFICATION', 'IN-PROCESS'];

            if (! in_array($currentStatus, $allowedStatusesForFinalization)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot finalize payroll. Current status must be VERIFICATION or IN-PROCESS.',
                    'current_status' => $currentStatus,
                ], 400);
            }

            // Always set to PAYROLL_LOCKED (no COMPLETED option anymore)
            $payrollPeriod->pp_status_code = 'finalized_locked';
            $payrollPeriod->pp_is_finalized = 120;
            $payrollPeriod->pp_is_processed = 120;
            $payrollPeriod->pp_is_freezed = 120;
            $payrollPeriod->pp_finalized_at = now();
            $payrollPeriod->pp_finalized_by = auth()->id();
            $payrollPeriod->save();

            \Log::info('Payroll finalized and locked', [
                'payroll_id' => $payrollPeriod->pp_id,
                'previous_status' => $currentStatus,
                'new_status' => 'finalized_locked',
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payroll locked successfully!',
                'status' => $payrollPeriod->pp_status_code,
                'data' => [
                    'period_id' => $payrollPeriod->pp_id,
                    'status' => $payrollPeriod->pp_status_code,
                    'message' => 'Payroll permanently locked. No further changes allowed.',
                    'finalized_at' => $payrollPeriod->pp_finalized_at,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to finalize payroll: '.$e->getMessage(),
            ], 500);
        }
    }

    public function revertToStep(Request $request)
    {
        try {
            $request->validate([
                'period_id' => 'required|integer|exists:payroll_periods,pp_id',
                'target_step' => 'required|string|in:STEP1,STEP2,STEP3,STEP5',
            ]);

            $payrollPeriod = PayrollPeriod::findOrFail($request->period_id);

            $targetStatus = match ($request->target_step) {
                'STEP1' => PayrollPeriod::STATUS_PROCESSING,
                'STEP2' => PayrollPeriod::STATUS_ATTENDANCE_FROZEN,
                'STEP3' => PayrollPeriod::STATUS_SALARY_PROCESSING,
                'STEP5' => PayrollPeriod::STATUS_VERIFICATION,
            };

            // Transition to target status
            $success = $payrollPeriod->transitionTo($targetStatus);

            if (! $success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot revert to '.$request->target_step.'. Current status: '.$payrollPeriod->pp_status_code,
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payroll reverted to '.$request->target_step,
                'status' => $payrollPeriod->pp_status_code,
                'next_step' => $request->target_step,
                'data' => [
                    'period_id' => $payrollPeriod->pp_id,
                    'current_status' => $payrollPeriod->pp_status_code,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to revert step: '.$e->getMessage(),
            ], 500);
        }
    }

    public function payrollPayrun()
    {
        $business_id = $this->user->emp_b_id;

        $payrollPeriodController = new PayrollPeriodController;

        $periods = $payrollPeriodController->getPayrollPeriodsData($business_id);
        //    dd($periods);

        return view('admin.payroll.payrun.index', [
            'cycles' => $periods,
            'employees' => [
                ['id' => 'EMP001', 'name' => 'Sarah Connor', 'role' => 'Senior Dev', 'salary' => '4,500', 'status' => 'pending'],
                ['id' => 'EMP002', 'name' => 'John Wick', 'role' => 'Security Lead', 'salary' => '3,200', 'status' => 'processed'],
            ],
            // Pass current step logic if handling via backend, otherwise default to DASHBOARD
            'currentStep' => session('currentStep', 'DASHBOARD'),
        ]);
    }

    public function checkStepAccess(Request $request)
    {
        try {
            $request->validate([
                'period_id' => 'required|integer|exists:payroll_periods,pp_id',
                'target_step' => 'required|string|in:DASHBOARD,STEP1,STEP2,STEP3,STEP4,STEP5,STEP6',
            ]);

            $payrollPeriod = PayrollPeriod::findOrFail($request->period_id);

            return response()->json([
                'success' => true,
                'can_access' => $payrollPeriod->canAccessStep($request->target_step),
                'current_status' => $payrollPeriod->pp_status_code,
                'current_step' => $payrollPeriod->getCurrentStep(),
                'message' => $payrollPeriod->canAccessStep($request->target_step)
                    ? 'Access granted'
                    : 'Cannot access this step. Current status: '.$payrollPeriod->pp_status_code,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check step access: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Start payroll process - Step 1
     */
    public function startPayrollProcess(Request $request)
    {
        try {
            $request->validate([
                'period_id' => 'required|integer|exists:payroll_periods,pp_id',
            ]);

            $payrollPeriod = PayrollPeriod::findOrFail($request->period_id);

            // Initialize status if not set
            if (! $payrollPeriod->pp_status_code) {
                $payrollPeriod->pp_status_code = PayrollPeriod::STATUS_PROCESSING;
                $payrollPeriod->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Payroll process started',
                'status' => $payrollPeriod->pp_status_code,
                'current_step' => $payrollPeriod->getCurrentStep(),
                'next_step' => 'STEP1',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start payroll process: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update payroll status
     */
    public function updatePayrollStatus(Request $request)
    {
        try {
            $request->validate([
                'period_id' => 'required|integer|exists:payroll_periods,pp_id',
                'status' => 'required|string|in:PROCESSING,ATTENDANCE_FROZEN,SALARY_PROCESSING,VERIFICATION,COMPLETED,PAYROLL_LOCKED',
                'notes' => 'nullable|string',
            ]);

            $payrollPeriod = PayrollPeriod::findOrFail($request->period_id);

            // Transition to new status
            $success = $payrollPeriod->transitionTo($request->status);

            if (! $success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status transition from '.$payrollPeriod->pp_status_code.' to '.$request->status,
                ], 400);
            }

            // Log status change
            if ($request->notes) {
                // You can create a log table for payroll status changes
                // PayrollStatusLog::create([...]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payroll status updated to '.$request->status,
                'status' => $payrollPeriod->pp_status_code,
                'current_step' => $payrollPeriod->getCurrentStep(),
                'next_step' => $payrollPeriod->getCurrentStep(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payroll status: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get current payroll status
     */
    public function getPayrollStatus(Request $request)
    {
        try {
            $request->validate([
                'period_id' => 'required|integer|exists:payroll_periods,pp_id',
            ]);

            $payrollPeriod = PayrollPeriod::findOrFail($request->period_id);

            return response()->json([
                'success' => true,
                'status' => $payrollPeriod->pp_status_code,
                'is_frozen' => $payrollPeriod->isFrozen(),
                'is_processed' => $payrollPeriod->isProcessed(),
                'is_finalized' => $payrollPeriod->isFinalized(),
                'current_step' => $payrollPeriod->getCurrentStep(),
                'can_access_steps' => [
                    'STEP1' => $payrollPeriod->canAccessStep1(),
                    'STEP2' => $payrollPeriod->canAccessStep2(),
                    'STEP3' => $payrollPeriod->canAccessStep3(),
                    'STEP5' => $payrollPeriod->canAccessStep5(),
                    'STEP6' => $payrollPeriod->canAccessStep6(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get payroll status: '.$e->getMessage(),
            ], 500);
        }
    }


    public function employeeSalaries(Request $request)
    {
        $user = Auth::user();

        $search = request()->input('searchFilter');
        $activeFilter = request()->input('activeFilter');



        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['emp_b_id', $user->emp_b_id],
                ],
                [
                    'method' => 'where',
                    'args' => ['emp_role_id', '!=', 1],
                ],
                [
                    'method' => 'with', // 🔹 Eager load relationships
                    'args' => [
                        'fh_business',
                        'fh_employee_salary',
                        'fh_department:d_id,d_name',
                        'fh_payroll_master_settings',
                    ],
                ],
                [
                    'method' => 'select',
                    'args' => ['emp_id', 'emp_b_id', 'emp_code', 'emp_fname', 'emp_mname', 'emp_lname', 'emp_full_name', 'emp_email', 'emp_date_of_joining', 'emp_phone', 'emp_d_id', 'updated_at'],
                    'relation' => [
                        'fh_business:b_id,b_payment_mode',
                        'fh_employee_salary:es_id,es_emp_id,es_monthly_ctc,es_monthly_gross,updated_at',
                        'fh_department:d_id,d_name',
                        'fh_payroll_master_settings:pms_id,pms_b_id,pms_payroll_cycle',
                    ],
                ],
                [
                    'method' => 'sortBy',
                    'args' => ['emp_id', 'emp_code', 'emp_full_name', 'updated_at', 'emp_date_of_joining'],
                ],
            ];

            if ($activeFilter != '') {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_status', $activeFilter]];
            }

            $searchRelationships = [
                'fh_employee_type' => ['m_name'],
                'fh_branch' => ['br_name'],
                'fh_department' => ['d_name'],
                'fh_work_mode' => ['m_name'],
                'fh_shift_type' => ['pst_name'],
                'fh_employee_status' => ['m_name'],
            ];

            $searchColumns = ['emp_id', 'emp_code', 'emp_fname', 'emp_mname', 'emp_lname', 'emp_full_name', 'emp_email', 'emp_date_of_joining', 'emp_phone', 'updated_at'];

            $searchRelationships = [
                'fh_employee_type' => ['m_name'],
                'fh_branch' => ['br_name'],
                'fh_department' => ['d_name'],
                'fh_work_mode' => ['m_name'],
                'fh_shift_type' => ['pst_name'],
                'fh_employee_status' => ['m_name'],
            ];

            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new Employee,
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
                searchRelationships: $searchRelationships
            ))->getServerSideDataTable();

            $rowData = [];
            $i = 0;
            foreach ($list as $val) {
                $i++;
                $row = [];

                $row[] = $i;
                $row[] = $val->emp_code;
                $row[] = $val->emp_full_name;
                $row[] = $val->fh_department->d_name;
                $row[] = date('d M Y', strtotime($val->emp_date_of_joining));

                $row[] = optional($val->fh_employee_salary)->es_monthly_ctc ?? '-';
                $row[] = optional($val->fh_employee_salary)->es_monthly_gross ?? '-';
                $updatedAt = optional($val->latest_salary_master_history)->wef;
                $row[] = $updatedAt ? $updatedAt->format('d M Y') : '-';

                // working url
                $editUrl = route('employee.salaries.addEdit', ['id' => Crypt::encrypt($val->emp_id)]);

                // Step 1: Get business payment mode
                $businessPaymentMode = optional($val->fh_payroll_master_settings)->pms_payroll_cycle;
                $payrollMode = optional($val->fh_payroll_master_settings)->pms_payroll_mode;

                // Step 2: Choose correct route based on payment mode
                if ($businessPaymentMode === 441) {
                    $editUrl = route('employee.salaries.addEdit', ['id' => Crypt::encrypt($val->emp_id)]);
                }
                if ($businessPaymentMode === 440 && $payrollMode = 'manual') {
                    $editUrl = route('employee.salaries.addEdit', ['id' => Crypt::encrypt($val->emp_id)]);
                } elseif ($businessPaymentMode === 439) {
                    $editUrl = route('employee.salaries.addEdit', ['id' => Crypt::encrypt($val->emp_id)]);
                } elseif ($businessPaymentMode === 440) {
                    $editUrl = route('employee.salaries.addEdit', ['id' => Crypt::encrypt($val->emp_id)]);
                } else {
                    // ❌ No Payroll Master Setting Found
                    $editUrl = '#';
                }
                // $row[] = '<a class="btn action-btns btn-sm btn-primary" href="' . (RolePermissionLogics::check_route_permission('admin/employee/payroll-add-edit/{id?}', 117) ? $editUrl : '') . '">
                //             <i class="feather feather-edit"></i>
                //         </a>
                //       <button class="btn btn-sm btn-danger delete-shift-type"
                //         data-id="' . $val->emp_id . '"><i class="feather feather-trash"></i>
                //       </button>';

                // $rowData[] = $row;

                $row[] = '
                <div class="btn-list ms-3">
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu p-2" style="min-width: 180px;">
                            <li>
                                <a class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2"
                                href="' . (RolePermissionLogics::check_route_permission('admin/employee/employee-salaries-add-edit/{id?}', 117) && $editUrl !== '#' ? $editUrl : 'javascript:void(0)') . '"
                                ' . ($editUrl === '#' ? 'onclick="showPayrollSettingAlert()"' : '') . '>
                                    <i class="feather feather-edit"></i> Edit
                                </a>
                            </li>
                            <li>
                                <button class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2 delete-shift-type"
                                        data-id="' . $val->emp_id . '" type="button">
                                    <i class="feather feather-trash"></i> Delete
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>';

                $rowData[] = $row;
            }

            $output = [
                'draw' => intval($request->input('draw')),
                'recordsTotal' => count($list),
                'recordsFiltered' => (new DynamicModelDataTableHelper(
                    eloquentModel: new Employee,
                    dynamicConditions: $dynamicConditions,
                    searchColumns: $searchColumns,
                    searchRelationships: $searchRelationships
                ))->countFilteredServerSideDataTable(),
                'data' => $rowData,
            ];

            return response()->json($output);
        }

        $businessId = $user->emp_b_id;
        $payrollSetting = PayrollMasterSetting::where('pms_b_id', $businessId)->first();

        $missingSettingMessage = null;

        if (! $payrollSetting) {
            $missingSettingMessage = 'Please configure Payroll Master Settings before accessing Employee Payroll.';
        }

        $columns = [
            'S. No.',
            'Emp. ID',
            'Emp. Name',
            'Department',
            'DOJ',
            'CTC Salary',
            'Gross Salary',
            'WEF',
            'Action',
        ];
        $title = 'Employee Salary';
        $financialYears = FinancialYear::where('fy_b_id', $user->emp_b_id)->where('fy_is_current', 1)->get();
        $business = Business::where('b_id', $user->emp_b_id)->first();

        return view('admin.payroll.payrun.employee-salaries-index', compact('title', 'businessId', 'business', 'columns', 'financialYears'));
    }


    public function addEditEmployeeSalaries($emp_id = null)
    {

        try {
            $user = Auth::user();
            $emp_actual_id = Crypt::decrypt($emp_id);

            // $smhistory = SalaryMasterHistory::where('sm_emp_id', $emp_actual_id)->where('sm_emp_b_id', $user->emp_b_id)->get();
            $smhistory = SalaryMasterHistory::with('financial_years')->where('sm_emp_id', $emp_actual_id)
                ->where('sm_emp_b_id', $user->emp_b_id)
                ->orderBy('sm_id', 'desc')
                ->get();
            $smhistoryLastData = $smhistory->first();
            $employee = Employee::with('fh_designation')->where('emp_id', $emp_actual_id)
                ->firstOrFail();
            $financialYears = FinancialYear::get();

            $employee_list = Employee::where('emp_b_id', $user->emp_b_id)
                ->where('emp_status', 71)
                ->get();

            // Fetch existing salary record
            $emp_salary = SalaryEmployeeSalary::where('es_emp_id', $emp_actual_id)->where('es_b_id', $user->emp_b_id)->first();
            if (! $emp_salary) {
                $emp_salary = SalaryEmployeeSalary::where('es_emp_id', $emp_actual_id)->first();
            }

            // dd($emp_salary);

            $calculation_modes = MasterTable::where('m_group', 'CALCULATION_MODE')
                ->orderBy('m_name', 'desc')
                ->pluck('m_name', 'm_id')
                ->toArray();

            $payroll_structures = SalaryPolicySalary::where('ps_b_id', $user->emp_b_id)->get();
            $business_id = $user->emp_b_id; // Logged-in user ka business_id

            if (! $business_id) {
                return response()->json(['error' => 'Business ID not found'], 404);
            }

            $earnings = SalaryAllowance::where('sa_b_id', $business_id)->orderBy('sa_sequence_valu', 'asc')->get();
            $deductions = StatutoryDeduction::join('master_table', 'statutory_deductions.std_deduction_type_id', '=', 'master_table.m_id')
                ->where('std_b_id', $business_id)
                ->select('statutory_deductions.*', 'master_table.m_name as deduction_type_name')
                ->get();

            $employerDeductions = StatutoryDeduction::join('master_table', 'statutory_deductions.std_deduction_type_id', '=', 'master_table.m_id')
                ->where('std_b_id', $business_id)
                ->select('statutory_deductions.*', 'master_table.m_name as deduction_type_name')
                ->get();
            // dd($deductions);

            $emp_earnings = [];
            $emp_deductions = [];
            $emplyer_deductions = [];

            if ($emp_salary) {
                $title = 'Update Payroll';

                $emp_earnings = [];
                SalaryEmployeeEarnings::with('fh_salary_earning_type')
                    ->where('es_e_emp_id', $emp_actual_id)
                    ->get()
                    ->each(function ($earning) use (&$emp_earnings) {
                        $amount = (float) $earning->es_e_amount;
                        if ($earning->es_sa_id) {
                            $emp_earnings[$earning->es_sa_id] = $amount;
                        }
                        if ($earning->es_e_type_id) {
                            $emp_earnings[$earning->es_e_type_id] = $amount;
                        }
                        if ($earning->fh_salary_earning_type?->sa_title) {
                            $emp_earnings[$earning->fh_salary_earning_type->sa_title] = $amount;
                        }
                    });


                $emp_deductions = SalaryEmployeeDeductions::where('es_d_emp_id', $emp_actual_id)
                    ->pluck('es_d_amount', 'es_d_type_id')
                    ->toArray();

                $emplyer_deductions = SalaryEmployerDeductions::where('employer_sd_emp_id', $emp_actual_id)
                    ->pluck('employer_sd_amount', 'employer_sd_type_id')
                    ->toArray();
            } else {
                $title = 'Add Payroll';
            }
            $payrollSetting = PayrollMasterSetting::where('pms_b_id', $business_id)->first();
            $automaticSalaryBasis = $payrollSetting?->pms_automatic_salary_basis;
            $calcMode = ($payrollSetting?->pms_payroll_mode === 'auto' && $automaticSalaryBasis === 'gross_to_ctc')
                ? 'gross'
                : 'ctc';
            $inputPeriod = 'monthly';
            $payrollMasterMode = $payrollSetting?->pms_payroll_mode === 'manual' ? 'manual' : 'auto';
            $entryMode = $payrollMasterMode;
            $inputVal = $calcMode === 'gross'
                ? (float) (($emp_salary?->es_monthly_gross ?? (($emp_salary?->es_annual_gross ?? 0) / 12)) ?: 0)
                : (float) (($emp_salary?->es_monthly_ctc ?? (($emp_salary?->es_annual_ctc ?? 0) / 12)) ?: 0);
            if ($inputVal <= 0) {
                $inputVal = 37500;
            }
            $isAutomaticSalaryBasisLocked = $payrollSetting?->pms_payroll_mode === 'auto' && ! empty($automaticSalaryBasis);

            $defaultTdsFyId = FinancialYear::where('fy_b_id', $user->emp_b_id)
                ->where('fy_is_current', 1)
                ->value('fy_id');
            if (! $defaultTdsFyId) {
                $defaultTdsFyId = FinancialYear::where('fy_b_id', $user->emp_b_id)
                    ->orderByDesc('fy_id')
                    ->value('fy_id');
            }

            return view('admin.payroll.payrun.salary-calculator', compact(
                'title',
                'employee',
                'business_id',
                'earnings',
                'deductions',
                'calculation_modes',
                'payroll_structures',
                'emp_salary',
                'emp_actual_id',
                'employee_list',
                'emp_earnings',
                'employerDeductions',
                'smhistory',
                'smhistoryLastData',
                'financialYears',
                'emplyer_deductions',
                'emp_deductions',
                'calcMode',
                'inputPeriod',
                'entryMode',
                'payrollMasterMode',
                'inputVal',
                'automaticSalaryBasis',
                'isAutomaticSalaryBasisLocked',
                'defaultTdsFyId'
            ));
        } catch (Exception $e) {
            if ($e->getMessage() == 'The payload is invalid.') {
                return redirect()->route('employee.payroll');
            }
        }
    }

    public function saveEmployeeSalaries(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|integer',
            'business_id' => 'required|integer',
            'annual_ctc' => 'required|numeric|min:0',
            'monthly_ctc' => 'required|numeric|min:0',
            'monthly_gross' => 'required|numeric|min:0',
            'monthly_net' => 'required|numeric|min:0',
            'earnings' => 'nullable|array',
            'deductions' => 'nullable|array',
            'employer_contributions' => 'nullable|array',
            'calc_mode' => 'nullable|string',
            'input_period' => 'nullable|string',
            'entry_mode' => 'nullable|string',
        ]);

        $user = Auth::user();
        $businessId = (int) $validated['business_id'];
        $employeeId = (int) $validated['employee_id'];

        if ((int) $user->emp_b_id !== $businessId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid business for this user.',
            ], 403);
        }

        $employeeExists = Employee::where('emp_id', $employeeId)
            ->where('emp_b_id', $businessId)
            ->exists();

        if (! $employeeExists) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found for this business.',
            ], 404);
        }

        try {
            DB::beginTransaction();

            $earnings = collect($validated['earnings'] ?? [])
                ->map(fn ($amount) => (float) $amount);
            $deductions = collect($validated['deductions'] ?? [])
                ->map(fn ($amount) => (float) $amount);
            $employerContributions = collect($validated['employer_contributions'] ?? [])
                ->map(fn ($amount) => (float) $amount);

            $allowances = SalaryAllowance::where('sa_b_id', $businessId)->get();
            $allowanceByTitle = $allowances->keyBy(fn ($allowance) => Str::lower(trim($allowance->sa_title)));

            SalaryEmployeeEarnings::where('es_e_b_id', $businessId)
                ->where('es_e_emp_id', $employeeId)
                ->delete();

            $baseSalary = 0;
            $otherAllowance = 0;
            $history = new SalaryMasterHistory();
            $history->sm_emp_id = $employeeId;
            $history->sm_emp_b_id = $businessId;
            $history->sm_cal_mode = $validated['calc_mode'] ?? null;
            $history->sm_monthly_ctc = $validated['monthly_ctc'];
            $history->sm_annual_ctc = $validated['annual_ctc'];
            $history->sm_total_earning = $earnings->sum();
            $history->sm_gross_pay = $validated['monthly_gross'];
            $history->sm_annual_gross = $validated['monthly_gross'] * 12;
            $history->sm_net_pay = $validated['monthly_net'];
            $history->sm_employee_total_ded = $deductions->sum();
            $history->sm_employer_total_ded = $employerContributions->sum();
            $history->sm_remark = 'Saved from salary calculator';
            $history->wef = now()->toDateString();

            foreach ($earnings as $name => $amount) {
                $allowance = $allowanceByTitle->get(Str::lower(trim((string) $name)));
                if (! $allowance) {
                    continue;
                }

                if ((int) $allowance->sa_earning_type_id === 360) {
                    $baseSalary = $amount;
                    $history->sm_basic = $amount;
                } elseif ((int) $allowance->sa_earning_type_id === 361) {
                    $history->sm_hra = $amount;
                } elseif ((int) $allowance->sa_earning_type_id === 362) {
                    $history->sm_dear_allow = $amount;
                } elseif ((int) $allowance->sa_earning_type_id === 363) {
                    $history->sm_conv_allow = $amount;
                } elseif ((int) $allowance->sa_earning_type_id === 364) {
                    $otherAllowance += $amount;
                    if (Str::contains(Str::upper($allowance->sa_title), 'EDUCATION')) {
                        $history->sm_edu_allow = $amount;
                    }
                    if (Str::contains(Str::upper($allowance->sa_title), 'MEDICAL')) {
                        $history->sm_med_allow = $amount;
                    }
                }

                SalaryEmployeeEarnings::create([
                    'es_e_b_id' => $businessId,
                    'es_e_emp_id' => $employeeId,
                    'es_sa_id' => $allowance->sa_id,
                    'es_e_type_id' => $allowance->sa_earning_type_id,
                    'es_e_amount' => $amount,
                    'es_e_cal_type_id' => $allowance->sa_calculation_type,
                ]);
            }

            $statutoryRows = StatutoryDeduction::join('master_table', 'statutory_deductions.std_deduction_type_id', '=', 'master_table.m_id')
                ->where('std_b_id', $businessId)
                ->select('statutory_deductions.*', 'master_table.m_name as deduction_type_name')
                ->get()
                ->keyBy(fn ($deduction) => Str::lower(trim($deduction->deduction_type_name)));

            foreach ($deductions as $name => $amount) {
                if (Str::upper((string) $name) === 'TDS') {
                    continue;
                }

                $deduction = $statutoryRows->get(Str::lower(trim((string) $name)));
                if (! $deduction) {
                    continue;
                }

                SalaryEmployeeDeductions::updateOrCreate(
                    [
                        'es_d_b_id' => $businessId,
                        'es_d_emp_id' => $employeeId,
                        'es_d_type_id' => $deduction->std_deduction_type_id,
                    ],
                    [
                        'es_d_amount' => $amount,
                        'es_d_cal_type_id' => $deduction->std_calculation_type ?? $deduction->std_deduction_type_id,
                    ]
                );
            }

            foreach ($employerContributions as $name => $amount) {
                $deduction = $statutoryRows->get(Str::lower(trim((string) $name)));
                if (! $deduction) {
                    continue;
                }

                SalaryEmployerDeductions::updateOrCreate(
                    [
                        'employer_sd_b_id' => $businessId,
                        'employer_sd_emp_id' => $employeeId,
                        'employer_sd_type_id' => $deduction->std_deduction_type_id,
                    ],
                    [
                        'employer_sd_amount' => $amount,
                        'employer_sd_cal_type_id' => $deduction->std_calculation_type ?? $deduction->std_deduction_type_id,
                    ]
                );
            }

            $history->sm_other_allow = $otherAllowance;
            $history->save();

            SalaryEmployeeSalary::updateOrCreate(
                [
                    'es_b_id' => $businessId,
                    'es_emp_id' => $employeeId,
                ],
                [
                    'es_annual_ctc' => $validated['annual_ctc'],
                    'es_monthly_ctc' => $validated['monthly_ctc'],
                    'es_base_salary' => $baseSalary,
                    'es_earnings' => $validated['monthly_gross'],
                    'es_deductions' => $deductions->sum(),
                    'es_rem_allowance' => $otherAllowance,
                    'es_monthly_gross' => $validated['monthly_gross'],
                    'es_annual_gross' => $validated['monthly_gross'] * 12,
                    'es_monthly_net_salary' => $validated['monthly_net'],
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Salary structure saved successfully.',
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to save salary structure', [
                'employee_id' => $employeeId,
                'business_id' => $businessId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save salary structure.',
            ], 500);
        }
    }

    public function salaryCalculatorEmployees(Request $request)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;

        $search = trim((string) $request->get('search', ''));
        $status = $request->get('status', '');

        $query = Employee::with([
                'fh_department',
                'fh_designation',
                'fh_employee_salary:es_id,es_emp_id,es_monthly_gross,es_monthly_ctc',
            ])
            ->where('emp_b_id', $businessId)
            ->where('emp_role_id', '!=', 1)
            ->whereIn('emp_status', [71, 72, 457, 458, 459, 460]);

        $employees = $query->orderBy('emp_full_name')->get();

        return view('admin.payroll.payrun.salary-calculator-employees-payroll-new', [
            'title' => 'Salary Calculator',
            'employees' => $employees,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function salaryCalculatorHistory($id)
    {
        try {
            $user = Auth::user();
            $businessId = (int) $user->emp_b_id;
            $employeeId = (int) Crypt::decrypt($id);

            $employee = Employee::with(['fh_department', 'fh_designation'])
                ->where('emp_id', $employeeId)
                ->where('emp_b_id', $businessId)
                ->firstOrFail();

            $histories = SalaryMasterHistory::with('financial_years')
                ->where('sm_emp_id', $employeeId)
                ->where('sm_emp_b_id', $businessId)
                ->orderBy('wef')
                ->orderBy('created_at')
                ->get();

            $trackedFields = [
                'sm_monthly_ctc' => 'Monthly CTC',
                'sm_annual_ctc' => 'Annual CTC',
                'sm_gross_pay' => 'Monthly Gross',
                'sm_annual_gross' => 'Annual Gross',
                'sm_net_pay' => 'Net Pay',
                'sm_total_earning' => 'Total Earnings',
                'sm_employee_total_ded' => 'Employee Deductions',
                'sm_employer_total_ded' => 'Employer Deductions',
                'sm_basic' => 'Basic',
                'sm_hra' => 'HRA',
                'sm_dear_allow' => 'Dearness Allowance',
                'sm_conv_allow' => 'Conveyance',
                'sm_med_allow' => 'Medical Allowance',
                'sm_edu_allow' => 'Education Allowance',
                'sm_other_allow' => 'Other Allowance',
                'sm_employee_epf' => 'Employee EPF',
                'sm_employee_esic' => 'Employee ESIC',
                'sm_employee_lwf' => 'Employee LWF',
                'sm_employer_epf' => 'Employer EPF',
                'sm_employer_esic' => 'Employer ESIC',
                'sm_employer_lwf' => 'Employer LWF',
            ];

            $previous = null;
            $timeline = $histories->map(function ($history) use (&$previous, $trackedFields) {
                $changes = [];

                foreach ($trackedFields as $field => $label) {
                    $oldValue = $previous ? (float) ($previous->{$field} ?? 0) : null;
                    $newValue = (float) ($history->{$field} ?? 0);

                    if ($previous && abs($newValue - $oldValue) < 0.01) {
                        continue;
                    }

                    if (! $previous && abs($newValue) < 0.01) {
                        continue;
                    }

                    $changes[] = [
                        'label' => $label,
                        'old' => $oldValue,
                        'new' => $newValue,
                        'diff' => $previous ? $newValue - $oldValue : $newValue,
                    ];
                }

                $item = [
                    'history' => $history,
                    'changes' => $changes,
                    'is_initial' => $previous === null,
                ];

                $previous = $history;

                return $item;
            })->reverse()->values();

            return view('admin.payroll.payrun.salary-calculator-history', [
                'title' => 'Salary History',
                'employee' => $employee,
                'timeline' => $timeline,
                'historyCount' => $histories->count(),
            ]);
        } catch (Exception $e) {
            if ($e->getMessage() === 'The payload is invalid.') {
                return redirect()->route('payroll.salary-calculator.employees');
            }

            throw $e;
        }
    }

    /**
     * Taxation menu version of the salary calculator employee list UI.
     * Reuses the same filtering logic, but renders a taxation-specific Blade.
     */
    public function salaryCalculatorEmployeesTaxation(Request $request)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;

        $search = trim((string) $request->get('search', ''));
        $status = $request->get('status', '');

        $query = Employee::with(['fh_department', 'fh_designation'])
            ->where('emp_b_id', $businessId)
            ->whereIn('emp_status', [71, 72, 457, 458, 459, 460]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('emp_full_name', 'like', "%{$search}%")
                    ->orWhere('emp_code', 'like', "%{$search}%");
            });
        }

        if ($status !== '' && in_array((int) $status, [71, 72], true)) {
            $query->where('emp_status', (int) $status);
        }

        $employees = $query->orderBy('emp_full_name')->paginate(20)->withQueryString();

        return view('admin.payroll.taxation.salary-calculator-employees', [
            'title' => 'Salary Calculator',
            'employees' => $employees,
            'search' => $search,
            'status' => $status,
        ]);
    }

    /**
     * Monthly TDS preview for salary calculator UI (Form 16 Part B–aligned projection).
     */
    public function previewMonthlyTds(Request $request)
    {
        $validated = $request->validate([
            'monthly_gross' => 'required|numeric|min:0',
            'monthly_pf' => 'nullable|numeric|min:0',
            'monthly_pt' => 'nullable|numeric|min:0',
            'financial_year_id' => 'required|integer|exists:financial_years,fy_id',
        ]);

        $user = Auth::user();
        $businessId = (int) $user->emp_b_id;
        $fyId = (int) $validated['financial_year_id'];

        $fyOk = FinancialYear::where('fy_id', $fyId)->where('fy_b_id', $businessId)->exists();
        if (! $fyOk) {
            return response()->json(['message' => 'Invalid financial year for this business'], 422);
        }

        $breakdown = PayrollLogics::getMonthlyTdsProjectionBreakdown(
            (float) $validated['monthly_gross'],
            (float) ($validated['monthly_pf'] ?? 0),
            (float) ($validated['monthly_pt'] ?? 0),
            $businessId,
            $fyId
        );

        return response()->json($breakdown);
    }

}
