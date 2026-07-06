<?php

namespace App\Http\Controllers\Payroll;

use App\Helpers\CentralLogics;
use App\Helpers\PayrollLogics;
use App\Http\Controllers\Controller;
use App\Models\AttendanceException;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSummary;
use App\Models\Employee;
use App\Models\FinancialYear;
use App\Models\LeaveRequest;
use App\Models\MasterTable;
use App\Models\OvertimePolicy;
use App\Models\PayrollMasterSetting;
use App\Models\PayrollPeriod;
use App\Models\PayrollPeriodWeek;
use App\Models\PayslipConfiguration;
use App\Models\PolicyHolidayList;
use App\Models\PolicyWeekOff;
use App\Models\ProcessedEmployeeSalary;
use App\Models\ProcessedSalaryDeduction;
use App\Models\ProcessedSalaryEarning;
use App\Models\SalaryEmployeeSalary;
use App\Models\SalaryHold;
use App\Models\SalaryMasterHistory;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use NumberToWords\NumberToWords;

class WeeklyPayrunController extends Controller
{
    protected $user;

    const STATUS_CONFIGURED = 'configured';

    const STATUS_PENDING_APPROVAL = 'pending_approval';

    const STATUS_UNDER_REVIEW = 'under_review';

    const STATUS_FROZEN = 'frozen';

    const STATUS_PROCESSING = 'processing';

    const STATUS_PROCESSED = 'processed';

    const STATUS_FINALIZED = 'finalized';

    const STATUS_VERIFICATION = 'verification';    // Under verification

    /**
     * Normalize legacy weekly status aliases to canonical values.
     */
    private function normalizeWeeklyStatus(?string $status): string
    {
        $status = strtolower(trim((string) $status));
        // Normalize separators so values like "under review"/"under-review" map correctly.
        $status = str_replace(['-', ' '], '_', $status);
        $status = preg_replace('/_+/', '_', $status) ?? $status;

        return match ($status) {
            'in_process', 'in-process' => self::STATUS_PROCESSING,
            'attendance_frozen', 'frozen' => self::STATUS_PROCESSING,
            'pendingapproval' => self::STATUS_PENDING_APPROVAL,
            'pending_approval' => self::STATUS_PENDING_APPROVAL,
            'underreview' => self::STATUS_UNDER_REVIEW,
            'under_review' => self::STATUS_UNDER_REVIEW,
            'completed' => self::STATUS_FINALIZED,
            '' => PayrollPeriodWeek::STATUS_OPEN,
            default => $status,
        };
    }

    /**
     * Map weekly status to UI step.
     */
    private function getWeeklyStepFromStatus(string $status, bool $isFrozen = false, bool $isProcessed = false, ?int $processedCount = null): string
    {
        $status = $this->normalizeWeeklyStatus($status);

        $statusStepMap = [
            self::STATUS_CONFIGURED => 'STEP1',
            self::STATUS_PENDING_APPROVAL => 'STEP1',
            self::STATUS_UNDER_REVIEW => 'STEP2',
            self::STATUS_PROCESSING => 'STEP3',
            self::STATUS_PROCESSED => 'STEP5',
            self::STATUS_VERIFICATION => 'STEP5',
            self::STATUS_FINALIZED => 'STEP6',
        ];

        $currentStep = $statusStepMap[$status] ?? 'DASHBOARD';

        // Only force STEP5 if we truly have processed rows
        if ($isProcessed && $status !== self::STATUS_FINALIZED && ($processedCount === null || $processedCount > 0)) {
            return 'STEP5';
        }

        if ($status === self::STATUS_FINALIZED) {
            return 'STEP6';
        }

        if ($isFrozen && ! $isProcessed && $currentStep === 'DASHBOARD') {
            return 'STEP2';
        }

        return $currentStep;
    }

    /**
     * Processed salaries query for a week (with legacy fallback).
     *
     * Some older rows may have ps_week_id NULL/0 even though they belong to a week.
     * Treat those rows as belonging to the week if the employee exists in attendance_summaries
     * for the same (pp_id, week_id, b_id).
     *
     * @return array{0:\Illuminate\Database\Eloquent\Builder,1:array<int,int>} [$query, $weekEmployeeIds]
     */
    private function processedSalariesForWeekQuery(int $periodId, int $weekId, int $businessId, ?array $employeeIds = null): array
    {
        $weekEmployeeIds = AttendanceSummary::where('as_pp_id', $periodId)
            ->where('as_week_id', $weekId)
            ->where('as_b_id', $businessId)
            ->pluck('as_emp_id')
            ->unique()
            ->values()
            ->toArray();

        if (! empty($employeeIds)) {
            $weekEmployeeIds = array_values(array_intersect($weekEmployeeIds, $employeeIds));
        }

        $query = ProcessedEmployeeSalary::where('ps_payroll_id', $periodId)
            ->where('ps_b_id', $businessId)
            ->where(function ($q) use ($weekId, $weekEmployeeIds) {
                $q->where('ps_week_id', $weekId)
                    ->orWhere(function ($qq) use ($weekEmployeeIds) {
                        $qq->where(function ($qqq) {
                            $qqq->whereNull('ps_week_id')->orWhere('ps_week_id', 0);
                        });
                        if (! empty($weekEmployeeIds)) {
                            $qq->whereIn('ps_emp_id', $weekEmployeeIds);
                        } else {
                            $qq->whereRaw('1=0');
                        }
                    });
            });

        if (! empty($employeeIds)) {
            $query->whereIn('ps_emp_id', $employeeIds);
        }

        return [$query, $weekEmployeeIds];
    }

    /**
     * Update weekly status and sync parent payroll status.
     */
    private function applyWeeklyStatusUpdate(int $periodId, int $weekId, int $businessId, string $newStatus): PayrollPeriodWeek
    {
        $normalizedStatus = $this->normalizeWeeklyStatus($newStatus);

        $week = PayrollPeriodWeek::where('ppw_id', $weekId)
            ->where('ppw_pp_id', $periodId)
            ->where('ppw_b_id', $businessId)
            ->firstOrFail();

        $week->ppw_status = $normalizedStatus;
        $week->save();

        $payrollPeriod = PayrollPeriod::find($periodId);
        if ($payrollPeriod) {
            if ($normalizedStatus === self::STATUS_PROCESSING && $payrollPeriod->pp_status_code !== PayrollPeriod::STATUS_PROCESSING) {
                $payrollPeriod->pp_status_code = PayrollPeriod::STATUS_PROCESSING;
                $payrollPeriod->save();
            } elseif (in_array($normalizedStatus, [self::STATUS_VERIFICATION, self::STATUS_PROCESSED], true)
                && $payrollPeriod->pp_status_code !== PayrollPeriod::STATUS_VERIFICATION) {
                $payrollPeriod->pp_status_code = PayrollPeriod::STATUS_VERIFICATION;
                $payrollPeriod->save();
            }
        }

        return $week;
    }

    /**
     * Delete processed salaries and mark matching attendance summaries as unprocessed.
     */
    private function unprocessWeeklySalaries(int $periodId, int $weekId, int $businessId, ?array $employeeIds = null): array
    {
        [$processedQuery] = $this->processedSalariesForWeekQuery($periodId, $weekId, $businessId, $employeeIds);

        $processedRows = (clone $processedQuery)->select(['ps_id', 'ps_emp_id'])->get();
        $processedSalaryIds = $processedRows->pluck('ps_id')->unique()->values()->toArray();
        $processedEmployeeIds = $processedRows->pluck('ps_emp_id')->unique()->values()->toArray();

        if (empty($processedEmployeeIds)) {
            return [
                'deleted_count' => 0,
                'deleted_earnings' => 0,
                'deleted_deductions' => 0,
                'attendance_updated' => 0,
                'affected_employee_ids' => [],
                'remaining_processed' => ProcessedEmployeeSalary::where('ps_payroll_id', $periodId)
                    ->where('ps_week_id', $weekId)
                    ->where('ps_b_id', $businessId)
                    ->count(),
            ];
        }

        $deletedEarnings = 0;
        $deletedDeductions = 0;
        if (! empty($processedSalaryIds)) {
            $deletedEarnings = ProcessedSalaryEarning::whereIn('ps_id', $processedSalaryIds)->delete();
            $deletedDeductions = ProcessedSalaryDeduction::whereIn('ps_id', $processedSalaryIds)->delete();
        }

        $deletedCount = $processedQuery->delete();

        $attendanceUpdated = AttendanceSummary::where('as_pp_id', $periodId)
            ->where('as_week_id', $weekId)
            ->where('as_b_id', $businessId)
            ->whereIn('as_emp_id', $processedEmployeeIds)
            ->whereIn('as_is_sal_processed', [120, 1])
            ->update([
                'as_is_sal_processed' => 121,
                'updated_at' => now(),
            ]);

        [$remainingQuery] = $this->processedSalariesForWeekQuery($periodId, $weekId, $businessId);
        $remainingProcessed = $remainingQuery->count();

        return [
            'deleted_count' => $deletedCount,
            'deleted_earnings' => $deletedEarnings,
            'deleted_deductions' => $deletedDeductions,
            'attendance_updated' => $attendanceUpdated,
            'affected_employee_ids' => $processedEmployeeIds,
            'remaining_processed' => $remainingProcessed,
        ];
    }

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function weeklyCyclesIndex(Request $request)
    {
        $business_id = auth()->user()->emp_b_id;

        // Get financial years
        $financialYears = FinancialYear::orderBy('fy_id', 'desc')
            ->where('fy_b_id', $business_id)
            ->select('fy_id', 'fy_year', 'fy_is_current')
            ->get();

        // Get current financial year - FIXED
        $selectedFY = $request->get('fy_id');
        $currentFY = null;

        if ($selectedFY) {
            $currentFY = FinancialYear::where('fy_b_id', $business_id)
                ->where('fy_id', $selectedFY)
                ->first();
        } else {
            // Get the latest financial year (2025-2026)
            $currentFY = FinancialYear::where('fy_b_id', $business_id)
                ->orderBy('fy_start_date', 'desc')
                ->first();

            if (! $currentFY && $financialYears->isNotEmpty()) {
                $currentFY = $financialYears->first();
            }
        }

        // Get payroll settings
        $payrollSettings = PayrollMasterSetting::where('pms_b_id', $business_id)->first();
        $defaultPayrollType = '440';
        $isWeeklySystem = false;

        if ($payrollSettings && $payrollSettings->pms_payroll_cycle == 441) {
            $defaultPayrollType = '441';
            $isWeeklySystem = true;
        }

        // Status UI Config
        $statusConfig = [
            'upcoming' => ['color' => 'info', 'icon' => 'clock', 'label' => 'Upcoming', 'progress' => 0],
            'open' => ['color' => 'primary', 'icon' => 'unlock', 'label' => 'Open', 'progress' => 10],
            'processing' => ['color' => 'primary', 'icon' => 'refresh-cw', 'label' => 'Processing', 'progress' => 30],
            'salary_processing' => ['color' => 'primary', 'icon' => 'refresh-cw', 'label' => 'Salary Processing', 'progress' => 35],
            'pending_approval' => ['color' => 'warning', 'icon' => 'clock', 'label' => 'Pending Approval', 'progress' => 40],
            'under_review' => ['color' => 'warning', 'icon' => 'eye', 'label' => 'Under Review', 'progress' => 50],
            'in_process' => ['color' => 'info', 'icon' => 'cog', 'label' => 'In Process', 'progress' => 65],
            'verification' => ['color' => 'info', 'icon' => 'check-circle', 'label' => 'Verification', 'progress' => 80],
            'finalized_locked' => ['color' => 'success', 'icon' => 'lock', 'label' => 'Finalized & Locked', 'progress' => 100],
            'expired' => ['color' => 'secondary', 'icon' => 'calendar-x', 'label' => 'Expired', 'progress' => 0],
        ];

        $cycles = collect();

        if ($currentFY) {
            try {
                $payrollPeriods = PayrollPeriod::with([
                    'month:m_id,m_name',
                    'financialYear:fy_id,fy_year',
                    'weeks' => function ($query) use ($currentFY) {
                        // CRITICAL FIX: Filter weeks by the same financial year
                        $query->where('ppw_fy_id', $currentFY->fy_id);
                    },
                ])
                    ->where('pp_b_id', $business_id)
                    ->where('pp_fy_id', $currentFY->fy_id)
                    ->orderBy('pp_start_date', 'asc')
                    ->get();

                $periodIds = $payrollPeriods->pluck('pp_id')->toArray();
                $employeeCounts = [];
                $holdCounts = [];

                if (! empty($periodIds)) {
                    $employeeCounts = DB::table('attendance_summaries')
                        ->select('as_pp_id', DB::raw('COUNT(DISTINCT as_emp_id) as employee_count'))
                        ->whereIn('as_pp_id', $periodIds)
                        ->groupBy('as_pp_id')
                        ->get()
                        ->keyBy('as_pp_id');

                    $holdCounts = DB::table('salary_holds')
                        ->select('sh_pp_id', DB::raw('COUNT(*) as hold_count'))
                        ->whereIn('sh_pp_id', $periodIds)
                        ->where('sh_status', 'held')
                        ->groupBy('sh_pp_id')
                        ->get()
                        ->keyBy('sh_pp_id');
                }

                $cycles = $payrollPeriods->map(function ($pp) use ($statusConfig, $employeeCounts, $holdCounts, $currentFY) {
                    $status = $pp->pp_status_code ?? 'upcoming';
                    $statusKey = Str::snake(strtolower($status));

                    if ($status === 'IN-PROCESS') {
                        $statusKey = 'in_process';
                    }

                    $statusMapping = [
                        'payroll_locked' => 'finalized_locked',
                        'completed' => 'finalized_locked',
                        'PAYROLL_LOCKED' => 'finalized_locked',
                    ];

                    $statusKey = $statusMapping[$statusKey] ?? $statusKey;
                    $ui = $statusConfig[$statusKey] ?? $statusConfig['upcoming'];

                    $employeeCount = 0;
                    if (isset($employeeCounts[$pp->pp_id]) && is_object($employeeCounts[$pp->pp_id])) {
                        $employeeCount = $employeeCounts[$pp->pp_id]->employee_count ?? 0;
                    }

                    $holdCount = 0;
                    if (isset($holdCounts[$pp->pp_id]) && is_object($holdCounts[$pp->pp_id])) {
                        $holdCount = $holdCounts[$pp->pp_id]->hold_count ?? 0;
                    }

                    $hasHolds = $holdCount > 0;
                    $payrollType = $pp->pp_type_id ?? null;
                    $weeks = collect();

                    // Get month name properly
                    $monthName = 'Period';
                    if ($pp->month) {
                        $monthName = $pp->month->m_name;
                    } elseif ($pp->pp_month_id) {
                        $month = MasterTable::where('m_id', $pp->pp_month_id)
                            ->where('m_group', 'MONTH')
                            ->first();
                        if ($month) {
                            $monthName = $month->m_name;
                        }
                    }

                    // Get weeks - CRITICAL FIX: Ensure weeks belong to current financial year
                    if ($payrollType == 441) {
                        if ($pp->weeks && $pp->weeks->count() > 0) {
                            // Additional filter to ensure weeks are for the correct financial year
                            $filteredWeeks = $pp->weeks->filter(function ($week) use ($currentFY) {
                                return $week->ppw_fy_id == $currentFY->fy_id;
                            });

                            $weeks = $filteredWeeks->map(function ($week) {
                                $weekStatus = $week->ppw_status ?? 'pending';

                                $weekStatusMap = [
                                    'open' => ['label' => 'Open', 'color' => 'primary', 'icon' => 'unlock', 'can_process' => true, 'can_view' => false],
                                    'pending' => ['label' => 'Pending', 'color' => 'warning', 'icon' => 'clock', 'can_process' => true, 'can_view' => false],
                                    'configured' => ['label' => 'Configured', 'color' => 'info', 'icon' => 'settings', 'can_process' => true, 'can_view' => false],
                                    'pending_approval' => ['label' => 'Pending Approval', 'color' => 'warning', 'icon' => 'clock', 'can_process' => true, 'can_view' => false],
                                    'under_review' => ['label' => 'Under Review', 'color' => 'warning', 'icon' => 'eye', 'can_process' => true, 'can_view' => false],
                                    'processing' => ['label' => 'Processing', 'color' => 'primary', 'icon' => 'loader', 'can_process' => true, 'can_view' => false],
                                    'in_process' => ['label' => 'In-Process', 'color' => 'primary', 'icon' => 'loader', 'can_process' => true, 'can_view' => false],
                                    'verification' => ['label' => 'Verification', 'color' => 'info', 'icon' => 'check-circle', 'can_process' => false, 'can_view' => true],
                                    'processed' => ['label' => 'Processed', 'color' => 'success', 'icon' => 'check-circle', 'can_process' => false, 'can_view' => true],
                                    'finalized' => ['label' => 'Finalized', 'color' => 'success', 'icon' => 'lock', 'can_process' => false, 'can_view' => true],
                                ];

                                $statusInfo = $weekStatusMap[$weekStatus] ?? ['label' => ucfirst($weekStatus), 'color' => 'secondary', 'icon' => 'clock', 'can_process' => false, 'can_view' => false];

                                return [
                                    'id' => $week->ppw_id,
                                    'payroll_period_id' => $week->ppw_pp_id,
                                    'week_number' => $week->ppw_week_number,
                                    'week_name' => $week->ppw_week_name,
                                    'start_date' => Carbon::parse($week->ppw_start_date)->format('d M Y'),
                                    'end_date' => Carbon::parse($week->ppw_end_date)->format('d M Y'),
                                    'status' => $weekStatus,
                                    'status_label' => $statusInfo['label'],
                                    'status_color' => $statusInfo['color'],
                                    'status_icon' => $statusInfo['icon'],
                                    'employee_count' => $week->ppw_employee_count ?? 0,
                                    'processed_count' => $week->ppw_processed_count ?? 0,
                                    'is_frozen' => $week->ppw_is_frozen ?? false,
                                    'is_processed' => $week->ppw_is_processed ?? false,
                                    'can_process' => $statusInfo['can_process'],
                                    'can_view' => $statusInfo['can_view'],
                                ];
                            });
                        }
                    }

                    $year = $pp->financialYear->fy_year ?? '';
                    $isWeeklyPeriod = ($payrollType == 441);
                    $weeksCount = $weeks->count();
                    $processedWeeks = $weeks->where('is_processed', true)->count();
                    [$canEditWeeks, $editBlockReason] = $this->canEditCycleWeeks($pp, $weeks);

                    return [
                        'id' => $pp->pp_id,
                        'month' => $monthName,
                        'year' => $year,
                        'start' => $pp->pp_start_date ? Carbon::parse($pp->pp_start_date)->format('d M Y') : 'N/A',
                        'end' => $pp->pp_end_date ? Carbon::parse($pp->pp_end_date)->format('d M Y') : 'N/A',
                        'start_raw' => $pp->pp_start_date ? Carbon::parse($pp->pp_start_date)->toDateString() : null,
                        'end_raw' => $pp->pp_end_date ? Carbon::parse($pp->pp_end_date)->toDateString() : null,
                        'employees' => (int) $employeeCount,
                        'hold_count' => (int) $holdCount,
                        'has_holds' => $hasHolds,
                        'status' => $status,
                        'ui' => $ui,
                        'progress' => (int) $ui['progress'],
                        'pp_is_finalized' => $pp->pp_is_finalized ?? 0,
                        'cheque_number' => $pp->pp_cheque_number ?? null,
                        'is_weekly' => $isWeeklyPeriod,
                        'weeks' => $weeks,
                        'weeks_count' => $weeksCount,
                        'processed_weeks' => $processedWeeks,
                        'payroll_type' => $payrollType,
                        'can_edit_weeks' => $canEditWeeks,
                        'edit_block_reason' => $editBlockReason,
                    ];
                });

                // Merge month cards so all DB weeks for the same (month + FY) are shown together.
                $cycles = $cycles
                    ->groupBy(function ($cycle) {
                        return ($cycle['month'] ?? '').'|'.($cycle['year'] ?? '');
                    })
                    ->map(function ($monthCycles) {
                        $base = $monthCycles->first();

                        if ($monthCycles->count() === 1) {
                            return $base;
                        }

                        $allWeeks = $monthCycles
                            ->pluck('weeks')
                            ->flatten(1)
                            ->sortBy('week_number')
                            ->unique('id')
                            ->values();

                        $startRaw = $monthCycles->pluck('start_raw')->filter()->sort()->first();
                        $endRaw = $monthCycles->pluck('end_raw')->filter()->sort()->last();

                        $base['weeks'] = $allWeeks;
                        $base['weeks_count'] = $allWeeks->count();
                        $base['processed_weeks'] = $allWeeks->where('is_processed', true)->count();
                        $base['employees'] = (int) $monthCycles->max('employees');
                        $base['hold_count'] = (int) $monthCycles->sum('hold_count');
                        $base['has_holds'] = $base['hold_count'] > 0;
                        $base['can_edit_weeks'] = (bool) $monthCycles->contains(function ($cycle) {
                            return (bool) ($cycle['can_edit_weeks'] ?? false);
                        });

                        if ($startRaw) {
                            $base['start_raw'] = $startRaw;
                            $base['start'] = Carbon::parse($startRaw)->format('d M Y');
                        }
                        if ($endRaw) {
                            $base['end_raw'] = $endRaw;
                            $base['end'] = Carbon::parse($endRaw)->format('d M Y');
                        }

                        return $base;
                    })
                    ->values();
            } catch (\Exception $e) {
                \Log::error('Error loading payroll cycles: '.$e->getMessage());
                $cycles = collect();
            }
        }

        if (! ($cycles instanceof \Illuminate\Support\Collection)) {
            $cycles = collect($cycles);
        }

        // Calculate current quarter based on current date
        $currentMonth = date('n');
        $currentQuarter = 1;
        if ($currentMonth >= 4 && $currentMonth <= 6) {
            $currentQuarter = 1;
        } elseif ($currentMonth >= 7 && $currentMonth <= 9) {
            $currentQuarter = 2;
        } elseif ($currentMonth >= 10 && $currentMonth <= 12) {
            $currentQuarter = 3;
        } else {
            $currentQuarter = 4;
        }

        $quarterKeys = ['Q1', 'Q2', 'Q3', 'Q4'];
        $currentQuarterKey = $quarterKeys[$currentQuarter - 1];

        return view('admin.payroll.weekly_payrun.weekly-payroll-cycles', compact(
            'cycles',
            'currentFY',
            'financialYears',
            'defaultPayrollType',
            'isWeeklySystem',
            'currentQuarterKey'
        ));
    }

    /**
     * Store Weekly Payroll Period
     */
    public function storeWeeklyPayrollCycles(Request $request)
    {
        $business_id = auth()->user()->emp_b_id;

        try {
            DB::beginTransaction();

            $isWeekly = $request->input('is_weekly_payroll', 0) == 1;

            if ($isWeekly) {
                $weeksData = json_decode($request->input('weekly_weeks_data', '[]'), true);

                if (empty($weeksData) || ! is_array($weeksData)) {
                    return redirect()->back()->with('error', 'Please add at least one week for weekly payroll.');
                }

                $financialYear = FinancialYear::find($request->financial_year);
                if (! $financialYear) {
                    return redirect()->back()->with('error', 'Invalid financial year selected.');
                }

                $monthId = $request->month ?? null;
                $monthName = null;
                $quarterId = null;

                if ($monthId) {
                    $month = MasterTable::where('m_id', $monthId)
                        ->where('m_group', 'MONTH')
                        ->first();
                    if ($month) {
                        $monthName = $month->m_name;
                        $quarterId = $this->getQuarterIdFromMonth($monthName, $request->financial_year);
                    }
                }

                $normalizedWeeks = [];
                foreach ($weeksData as $index => $week) {
                    $rawWeekValue = $week['week_value'] ?? $week['week_number'] ?? null;
                    $weekNumber = is_numeric($rawWeekValue)
                        ? (int) $rawWeekValue
                        : (int) preg_replace('/[^0-9]/', '', (string) $rawWeekValue);

                    if ($weekNumber <= 0) {
                        return redirect()->back()->with('error', 'Invalid week number at row '.($index + 1).'.');
                    }

                    if (empty($week['start']) || empty($week['end'])) {
                        return redirect()->back()->with('error', 'Week date range is required at row '.($index + 1).'.');
                    }

                    $start = Carbon::parse($week['start'])->startOfDay();
                    $end = Carbon::parse($week['end'])->startOfDay();

                    if ($end->lt($start)) {
                        return redirect()->back()->with('error', 'Week end date cannot be before start date at row '.($index + 1).'.');
                    }

                    $normalizedWeeks[] = [
                        'week_number' => $weekNumber,
                        'start' => $start->toDateString(),
                        'end' => $end->toDateString(),
                    ];
                }

                usort($normalizedWeeks, function ($a, $b) {
                    return strcmp($a['start'], $b['start']);
                });

                $requestedWeekNumbers = collect($normalizedWeeks)
                    ->pluck('week_number')
                    ->map(fn ($num) => (int) $num)
                    ->unique()
                    ->values()
                    ->toArray();

                $existingWeekNumbers = $this->getExistingWeeklyNumbersForMonth(
                    (int) $business_id,
                    (int) $request->financial_year,
                    $monthId ? (int) $monthId : null
                );

                $duplicateWeekNumbers = array_values(array_intersect($requestedWeekNumbers, $existingWeekNumbers));
                if (! empty($duplicateWeekNumbers)) {
                    return redirect()->back()->with(
                        'error',
                        'Week(s) already exist for selected month: '.implode(', ', $duplicateWeekNumbers).'. Please add only remaining weeks.'
                    );
                }

                $payrollPeriod = new PayrollPeriod;
                $payrollPeriod->pp_b_id = $business_id;
                $payrollPeriod->pp_fy_id = $request->financial_year;
                $payrollPeriod->pp_month_id = $request->month ?? null;
                $payrollPeriod->pp_quarter_id = $quarterId;
                $payrollPeriod->pp_name = $request->payroll_name;
                $payrollPeriod->pp_description = $request->description;
                $payrollPeriod->pp_status_code = 'open';
                $payrollPeriod->pp_type_id = 441;
                $payrollPeriod->pp_created_by = auth()->user()->emp_id;

                $payrollPeriod->pp_start_date = $normalizedWeeks[0]['start'];
                $payrollPeriod->pp_end_date = $normalizedWeeks[count($normalizedWeeks) - 1]['end'];

                $payrollPeriod->save();

                $createdWeeks = [];

                foreach ($normalizedWeeks as $week) {
                    $weekNumber = $week['week_number'];
                    $weekName = "Week {$weekNumber}";

                    $payrollPeriodWeek = new PayrollPeriodWeek;
                    $payrollPeriodWeek->ppw_pp_id = $payrollPeriod->pp_id;
                    $payrollPeriodWeek->ppw_b_id = $business_id;
                    $payrollPeriodWeek->ppw_fy_id = $request->financial_year;
                    $payrollPeriodWeek->ppw_month_id = $request->month ?? null;
                    $payrollPeriodWeek->ppw_week_number = (int) $weekNumber;
                    $payrollPeriodWeek->ppw_week_name = $weekName;
                    $payrollPeriodWeek->ppw_start_date = $week['start'];
                    $payrollPeriodWeek->ppw_end_date = $week['end'];
                    $payrollPeriodWeek->ppw_status = PayrollPeriodWeek::STATUS_OPEN;
                    $payrollPeriodWeek->ppw_description = "Week {$weekNumber} of {$request->payroll_name}";
                    $payrollPeriodWeek->ppw_created_by = auth()->user()->emp_id;
                    $payrollPeriodWeek->save();

                    $createdWeeks[] = $payrollPeriodWeek->ppw_id;
                }

                DB::commit();

                return redirect()->back()->with('success', count($createdWeeks).' weekly payroll week(s) created successfully.');
            } else {
                return redirect()->back()->with('error', 'Please enable weekly payroll in settings.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating payroll period: '.$e->getMessage());

            return redirect()->back()->with('error', 'Failed to create payroll period: '.$e->getMessage());
        }
    }

    /**
     * Get Weekly Pending Requests for Step 1
     */
    private function getWeeklyPendingRequestsForStep1($periodId, $weekId, $businessId)
    {
        $week = PayrollPeriodWeek::find($weekId);

        if (! $week) {
            return [
                'has_pending' => false,
                'missed_punches' => 0,
                'leave_requests' => 0,
                'overtime_requests' => 0,
                'total_pending' => 0,
            ];
        }

        $startDate = Carbon::parse($week->ppw_start_date);
        $endDate = Carbon::parse($week->ppw_end_date);

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

        // Overtime Requests
        $overtimePolicy = OvertimePolicy::where('ot_b_id', $businessId)
            ->where('ot_is_enabled', 1)
            ->exists();

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
            'payroll_id' => $periodId,
            'week_id' => $weekId,
        ];
    }

    /**
     * Check Weekly Pending Requests (AJAX)
     * Checks for missed punches, leave requests, and overtime requests between week start and end dates
     */
    public function checkWeeklyPendingRequests(Request $request)
    {
        try {
            $businessId = auth()->user()->emp_b_id;
            $periodId = $request->get('payroll_period');
            $weekId = $request->get('week_id');

            $week = PayrollPeriodWeek::find($weekId);
            if (! $week) {
                return response()->json([
                    'success' => false,
                    'message' => 'Week not found',
                ], 404);
            }

            $startDate = Carbon::parse($week->ppw_start_date);
            $endDate = Carbon::parse($week->ppw_end_date);

            $oldStatus = $week->ppw_status;
            $statusChanged = false;

            // Update week status to PENDING_APPROVAL if not already
            if ($oldStatus !== 'pending_approval' && $oldStatus !== 'PENDING_APPROVAL') {
                $week->ppw_status = 'pending_approval';
                $week->save();
                $statusChanged = true;
            }

            // 1. Missed Punches with Employee Details
            $missPunchPendingQuery = AttendanceException::join('employees', 'attendance_exceptions.ae_emp_id', '=', 'employees.emp_id')
                ->where('attendance_exceptions.ae_b_id', $businessId)
                ->where('attendance_exceptions.ae_stage_completed', 0)
                ->whereNotIn('attendance_exceptions.ae_status', [139, 192, 156, 170])
                ->whereNotNull('attendance_exceptions.ae_am_id')
                ->whereBetween('attendance_exceptions.ae_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->select(
                    'attendance_exceptions.*',
                    'employees.emp_full_name',
                    'employees.emp_code',
                    'employees.emp_dg_id',
                    'employees.emp_d_id'
                );

            $missPunchPending = $missPunchPendingQuery->count();

            // Get details for modal (top 3)
            $missPunchDetails = $missPunchPendingQuery
                ->orderBy('attendance_exceptions.ae_date', 'desc')
                ->limit(3)
                ->get()
                ->map(function ($exception) {
                    return [
                        'employee_id' => $exception->ae_emp_id,
                        'employee_name' => $exception->emp_full_name ?? 'Unknown',
                        'employee_code' => $exception->emp_code ?? 'N/A',
                        'date' => Carbon::parse($exception->ae_date)->format('M d, Y'),
                        'type' => 'Missed Punch',
                        'in_time' => $exception->ae_in_time,
                        'out_time' => $exception->ae_out_time,
                        'status' => $exception->ae_status,
                    ];
                });

            // 2. Leave Requests with Employee Details (MATCHING YOUR MONTHLY METHOD)
            $leavePending = 0;
            $leaveDetails = [];

            if (class_exists('App\\Models\\LeaveRequest')) {
                $leavePendingQuery = LeaveRequest::join('employees', 'leave_requests.lvr_emp_id', '=', 'employees.emp_id')
                    ->where('leave_requests.lvr_b_id', $businessId)
                    ->where('leave_requests.lvr_stage_completed', 0)
                    ->whereNull('leave_requests.lvr_p_id')
                    ->whereNotIn('leave_requests.lvr_status', [170])
                    ->where(function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('leave_requests.lvr_start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                            ->orWhereBetween('leave_requests.lvr_end_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                            ->orWhere(function ($subQuery) use ($startDate, $endDate) {
                                $subQuery->where('leave_requests.lvr_start_date', '<=', $startDate->format('Y-m-d'))
                                    ->where('leave_requests.lvr_end_date', '>=', $endDate->format('Y-m-d'));
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
                        $leaveStartDate = Carbon::parse($leave->lvr_start_date);
                        $leaveEndDate = Carbon::parse($leave->lvr_end_date);
                        $duration = $leaveStartDate->diffInDays($leaveEndDate) + 1;

                        return [
                            'employee_id' => $leave->lvr_emp_id,
                            'employee_name' => $leave->emp_full_name ?? 'Unknown',
                            'employee_code' => $leave->emp_code ?? 'N/A',
                            'leave_type' => $leave->leave_type_name ?? 'N/A',
                            'duration' => $duration.' Day'.($duration > 1 ? 's' : ''),
                            'start_date' => $leaveStartDate->format('M d'),
                            'end_date' => $leaveEndDate->format('M d'),
                            'status' => $leave->lvr_status,
                        ];
                    });
            }

            // 3. Overtime Requests with Employee Details
            $overTimePendingQuery = DB::table('ot_approval_status as ot')
                ->join('employees', 'ot.ot_emp_id', '=', 'employees.emp_id')
                ->where('ot.ot_b_id', $businessId)
                ->where('ot.ot_stage_completed', 0)
                ->where(function ($query) {
                    $query->whereNull('ot.ot_requested_status')
                        ->orWhere('ot.ot_requested_status', '!=', 170);
                })
                ->whereBetween('ot.ot_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

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
                        'date' => Carbon::parse($ot->ot_date)->format('M d, Y'),
                        'hours' => $ot->ot_hours ?? 0,
                        'type' => $ot->ot_type ?? 'Overtime',
                        'status' => $ot->ot_requested_status,
                    ];
                });

            $totalPending = $missPunchPending + $leavePending + $overTimePending;

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
                    'current_status' => $week->ppw_status,
                    'previous_status' => $oldStatus,
                    'status_changed' => $statusChanged,
                    'status_update_message' => $statusChanged ?
                        "Status updated from {$oldStatus} to PENDING_APPROVAL" :
                        'Already in PENDING_APPROVAL status',
                    'total_pending' => $totalPending,
                    'has_pending' => $totalPending > 0,
                    'message' => $totalPending > 0 ?
                        "Found {$totalPending} pending request(s) for Week {$week->ppw_week_number} ({$startDate->format('d M Y')} - {$endDate->format('d M Y')})" :
                        'No pending requests found for this week',
                    'missed_punch_url' => route('mis-punch.index'),
                    'payroll_id' => $periodId,
                    'week_id' => $weekId,
                    'week_number' => $week->ppw_week_number,
                    'week_start_date' => $startDate->format('d M Y'),
                    'week_end_date' => $endDate->format('d M Y'),
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Check Weekly Pending Requests Error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to check pending requests: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update Weekly Week Status (for Back to Processing functionality)
     */
    public function updateWeekStatus(Request $request)
    {
        try {
            $request->validate([
                'payroll_id' => 'required|integer|exists:payroll_periods,pp_id',
                'week_id' => 'required|integer|exists:payroll_period_weeks,ppw_id',
                'status' => 'required|string|in:in_process,in-process,processing,under_review,verification,processed',
            ]);

            $periodId = $request->payroll_id;
            $weekId = $request->week_id;
            $newStatus = $this->normalizeWeeklyStatus($request->status);
            $businessId = auth()->user()->emp_b_id;

            $week = PayrollPeriodWeek::where('ppw_id', $weekId)
                ->where('ppw_pp_id', $periodId)
                ->where('ppw_b_id', $businessId)
                ->firstOrFail();
            $oldStatus = $week->ppw_status;
            $week = $this->applyWeeklyStatusUpdate($periodId, $weekId, $businessId, $newStatus);

            \Log::info('Weekly week status updated', [
                'week_id' => $weekId,
                'payroll_id' => $periodId,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Week status updated from {$oldStatus} to {$newStatus} successfully",
                'data' => [
                    'week_id' => $weekId,
                    'payroll_id' => $periodId,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'week_number' => $week->ppw_week_number,
                    'next_step' => (function () use ($periodId, $weekId, $businessId, $newStatus, $week) {
                        [$q] = $this->processedSalariesForWeekQuery($periodId, $weekId, $businessId);
                        $processedCount = $q->count();

                        return $this->getWeeklyStepFromStatus($newStatus, (bool) $week->ppw_is_frozen, (bool) $week->ppw_is_processed, $processedCount);
                    })(),
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Update week status failed: '.$e->getMessage(), [
                'payroll_id' => $request->payroll_id ?? null,
                'week_id' => $request->week_id ?? null,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update week status: '.$e->getMessage(),
            ], 500);
        }
    }

    public function backToProcessing(Request $request)
    {
        try {
            $request->validate([
                'payroll_id' => 'required|integer|exists:payroll_periods,pp_id',
                'week_id' => 'required|integer|exists:payroll_period_weeks,ppw_id',
            ]);

            $periodId = $request->payroll_id;
            $weekId = $request->week_id;
            $businessId = auth()->user()->emp_b_id;

            $week = PayrollPeriodWeek::where('ppw_id', $weekId)
                ->where('ppw_pp_id', $periodId)
                ->where('ppw_b_id', $businessId)
                ->firstOrFail();
            $oldStatus = $week->ppw_status;

            DB::beginTransaction();

            // ✅ One step back: delete processed salaries (week scope) + mark attendance as unprocessed (121)
            $unprocessResult = $this->unprocessWeeklySalaries($periodId, $weekId, $businessId);

            $week = $this->applyWeeklyStatusUpdate($periodId, $weekId, $businessId, self::STATUS_PROCESSING);

            $week->ppw_processed_count = 0;
            $week->ppw_is_processed = false;
            $week->ppw_processed_at = null;
            $week->ppw_processed_by = null;
            $week->save();

            DB::commit();

            \Log::info('Weekly status updated for back to processing', [
                'week_id' => $weekId,
                'old_status' => $oldStatus,
                'new_status' => self::STATUS_PROCESSING,
                'deleted_processed_rows' => $unprocessResult['deleted_count'] ?? 0,
                'deleted_processed_earnings' => $unprocessResult['deleted_earnings'] ?? 0,
                'deleted_processed_deductions' => $unprocessResult['deleted_deductions'] ?? 0,
                'attendance_updated' => $unprocessResult['attendance_updated'] ?? 0,
            ]);

            // Step 2: Get the salary data
            $salaryData = $this->getWeeklySalaryData($periodId, $weekId);
            // Use associative arrays so Blade partial can use $emp['...'] safely
            $salaryDataArray = $salaryData->getData(true);

            // Generate HTML for the table if needed
            $html = '';
            if (! empty($salaryDataArray['success']) && ! empty($salaryDataArray['data']['employees'])) {
                $html = view('admin.payroll.weekly_payrun.partials.weekly-salary-rows', [
                    'employees' => $salaryDataArray['data']['employees'],
                ])->render();
            }

            return response()->json([
                'success' => true,
                'message' => 'Week status updated to In-Process. You can now edit salaries.',
                'html' => $html,
                'week_status' => self::STATUS_PROCESSING,
                'week_number' => $week->ppw_week_number,
                'next_step' => 'STEP3',
                'unprocess_summary' => [
                    'deleted_processed_rows' => $unprocessResult['deleted_count'] ?? 0,
                    'deleted_processed_earnings' => $unprocessResult['deleted_earnings'] ?? 0,
                    'deleted_processed_deductions' => $unprocessResult['deleted_deductions'] ?? 0,
                    'attendance_updated' => $unprocessResult['attendance_updated'] ?? 0,
                ],
                'data' => $salaryDataArray['data'] ?? null,
            ]);
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            \Log::error('Back to processing failed: '.$e->getMessage(), [
                'payroll_id' => $request->payroll_id ?? null,
                'week_id' => $request->week_id ?? null,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Weekly Pending Requests Summary (for view)
     */
    private function getWeeklyPendingRequestsSummary($periodId, $weekId, $businessId)
    {
        $week = PayrollPeriodWeek::find($weekId);

        if (! $week) {
            return [
                'total_pending' => 0,
                'missed_punches' => 0,
                'leave_requests' => 0,
                'overtime_requests' => 0,
                'has_pending' => false,
                'week_number' => null,
                'week_name' => null,
            ];
        }

        $startDate = Carbon::parse($week->ppw_start_date);
        $endDate = Carbon::parse($week->ppw_end_date);

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
                        ->orWhereBetween('lvr_end_date', [$startDate, $endDate]);
                })
                ->count();
        }

        // Overtime Requests
        $overTimePending = DB::table('ot_approval_status')
            ->where('ot_b_id', $businessId)
            ->where('ot_stage_completed', 0)
            ->where(function ($query) {
                $query->whereNull('ot_requested_status')
                    ->orWhere('ot_requested_status', '!=', 170);
            })
            ->whereBetween('ot_date', [$startDate, $endDate])
            ->count();

        $totalPending = $missPunchPending + $leavePending + $overTimePending;

        return [
            'total_pending' => $totalPending,
            'missed_punches' => $missPunchPending,
            'leave_requests' => $leavePending,
            'overtime_requests' => $overTimePending,
            'has_pending' => $totalPending > 0,
            'week_number' => $week->ppw_week_number,
            'week_name' => $week->ppw_week_name,
            'week_start_date' => $startDate->format('d M Y'),
            'week_end_date' => $endDate->format('d M Y'),
            'missed_punch_url' => route('mis-punch.index'),
            'payroll_id' => $periodId,
            'week_id' => $weekId,
        ];
    }

    /**
     * Retrieve Weekly Attendance (AJAX) - For STEP2
     */
    /**
     * Retrieve Weekly Attendance (AJAX) - For STEP2
     */
    public function payrunRetrieveAttendance(Request $request)
    {
        $request->validate([
            'payroll_id' => 'required|integer|exists:payroll_periods,pp_id',
            'week_id' => 'required|integer|exists:payroll_period_weeks,ppw_id',
        ]);

        $payrollPeriod = PayrollPeriod::findOrFail($request->payroll_id);
        $week = PayrollPeriodWeek::where('ppw_id', $request->week_id)
            ->where('ppw_pp_id', $request->payroll_id)
            ->firstOrFail();

        // ✅ Update week status to UNDER_REVIEW (when attendance is retrieved)
        $oldStatus = $week->ppw_status;

        // Only update if status is open/pending/configured
        $allowedStatuses = ['open', 'pending', 'configured', 'pending_approval'];
        if (in_array($oldStatus, $allowedStatuses)) {
            $week->ppw_status = 'under_review';
            $week->save();
        }

        // ✅ Also update main payroll period status if needed
        if ($payrollPeriod->pp_status_code == 'PROCESSING' || $payrollPeriod->pp_status_code == 'open') {
            $payrollPeriod->pp_status_code = 'UNDER_REVIEW';
            $payrollPeriod->save();
        }

        // Get weekly attendance details
        $attendanceDetails = $this->getWeeklyAttendanceDetails($week);

        $attendanceData = [];

        foreach ($attendanceDetails as $row) {
            $empId = $row['employee']->emp_id;

            $attendanceData[$empId] = [
                'emp_id' => $empId,
                'payroll_id' => $payrollPeriod->pp_id,
                'week_id' => $week->ppw_id,
                'total_days' => $row['attendance_summary']['total_days'] ?? 7,
                'presentCount' => $row['attendance_summary']['presentCount'] ?? 0,
                'absentCount' => $row['attendance_summary']['absentCount'] ?? 0,
                'weekOffCount' => $row['attendance_summary']['weekOffCount'] ?? 0,
                'weekOffPresentCount' => $row['attendance_summary']['weekOffPresentCount'] ?? 0,
                'halfDayCount' => $row['attendance_summary']['halfDayCount'] ?? 0,
                'leaveCount' => $row['attendance_summary']['leaveCount'] ?? 0,
                'holidayCount' => $row['attendance_summary']['holidayCount'] ?? 0,
                'UPL' => $row['attendance_summary']['UPL'] ?? 0,
                'lateCount' => $row['attendance_summary']['lateCount'] ?? 0,
                'earlyExitCount' => $row['attendance_summary']['earlyExitCount'] ?? 0,
                'missedPunchCount' => $row['attendance_summary']['missedPunchCount'] ?? 0,
                'overtimeCount' => $row['attendance_summary']['overtimeCount'] ?? 0,
                'total' => $row['attendance_summary']['total'] ?? 0,
            ];
        }

        // Generate HTML using the partial view
        $html = view('admin.payroll.weekly_payrun.partials.weekly-attendance-rows', compact('attendanceDetails'))->render();

        return response()->json([
            'success' => true,
            'attendanceData' => $attendanceData,
            'html' => $html,
            'week_status' => $week->ppw_status,
            'message' => 'Attendance data retrieved successfully',
        ]);
    }

    /**
     * Fallback method to generate HTML if view doesn't exist
     */
    private function generateAttendanceRowsHtml($attendanceDetails)
    {
        $html = '';
        foreach ($attendanceDetails as $row) {
            $html .= '<tr data-emp-id="'.$row['employee']->emp_id.'" data-emp-code="'.$row['employee']->emp_code.'">';
            $html .= '<td class="para-text-table" style="padding:8px; position:sticky; left:0; background: inherit;">'.$row['employee']->emp_code.'</td>';
            $html .= '<td class="para-text-table" style="padding:8px; position:sticky; left:70px; text-align:left; background: inherit;">'.$row['employee']->emp_full_name.'</td>';
            $html .= '<td class="para-text-table text-center">'.($row['attendance_summary']['total_days'] ?? 7).'</td>';
            $html .= '<td class="para-text-table text-center">'.($row['attendance_summary']['presentCount'] ?? 0).'</td>';
            $html .= '<td class="para-text-table text-center">'.($row['attendance_summary']['absentCount'] ?? 0).'</td>';
            $html .= '<td class="para-text-table text-center">'.($row['attendance_summary']['weekOffCount'] ?? 0).'</td>';
            $html .= '<td class="para-text-table text-center">'.($row['attendance_summary']['weekOffPresentCount'] ?? 0).'</td>';
            $html .= '<td class="para-text-table text-center">'.($row['attendance_summary']['halfDayCount'] ?? 0).'</td>';
            $html .= '<td class="para-text-table text-center">'.($row['attendance_summary']['leaveCount'] ?? 0).'</td>';
            $html .= '<td class="para-text-table text-center">'.($row['attendance_summary']['holidayCount'] ?? 0).'</td>';
            $html .= '<td class="para-text-table text-center">'.($row['attendance_summary']['UPL'] ?? 0).'</td>';
            $html .= '<td class="para-text-table text-center"><input type="number" class="late-input input-text" value="'.($row['attendance_summary']['lateCount'] ?? 0).'" data-original="'.($row['attendance_summary']['lateCount'] ?? 0).'" min="0" max="7" step="0.5" style="width:55px;text-align:center; border-radius:4px; border:1px solid #cbd5e1; padding:4px;"></td>';
            $html .= '<td class="para-text-table text-center"><input type="number" class="early-input input-text" value="'.($row['attendance_summary']['earlyExitCount'] ?? 0).'" data-original="'.($row['attendance_summary']['earlyExitCount'] ?? 0).'" min="0" max="7" step="0.5" style="width:55px;text-align:center; border-radius:4px; border:1px solid #cbd5e1; padding:4px;"></td>';
            $html .= '<td class="para-text-table text-center">'.($row['attendance_summary']['missedPunchCount'] ?? 0).'</td>';
            $html .= '<td class="para-text-table text-center">'.($row['attendance_summary']['overtimeCount'] ?? 0).'</td>';
            $html .= '<td class="para-text-table text-center" style="font-weight:bold; color: #059669;">'.($row['attendance_summary']['total'] ?? 0).'</td>';
            $html .= '</tr>';
        }

        if (count($attendanceDetails) == 0) {
            $html .= '<tr><td colspan="16" style="padding:40px; text-align:center; color:#94a3b8;">No employees found for this week</td></tr>';
        }

        return $html;
    }

    /**
     * Get Weekly Attendance Details for a specific week
     */
    private function getWeeklyAttendanceDetails(PayrollPeriodWeek $week, int $limit = 100, int $offset = 0)
    {
        $business_id = Auth::user()->emp_b_id;
        $weekStart = Carbon::parse($week->ppw_start_date);
        $weekEnd = Carbon::parse($week->ppw_end_date);
        $month = $weekStart->format('m');
        $year = $weekStart->format('Y');

        // 1️⃣ Get employees with proper filters
        $employees = Employee::where('emp_b_id', $business_id)
            ->where('emp_role_id', '!=', 1)
            ->whereIn('emp_status', [71, 72, 457, 458, 459, 460])
            ->whereDate('emp_date_of_joining', '<=', $weekEnd)
            ->where(function ($q) use ($weekStart) {
                $q->whereNull('emp_last_working_date')
                    ->orWhereDate('emp_last_working_date', '>=', $weekStart);
            })
            ->orderBy('emp_id')
            ->limit($limit)
            ->offset($offset)
            ->get();

        // 2️⃣ Load holidays ONCE for the week
        $holidayRecords = PolicyHolidayList::where('phl_b_id', $business_id)
            ->where(function ($q) use ($weekStart, $weekEnd) {
                $q->whereBetween('phl_start_date', [$weekStart, $weekEnd])
                    ->orWhereBetween('phl_end_date', [$weekStart, $weekEnd]);
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

        // 3️⃣ Weekoff policy ONCE
        $weekoffPolicy = PolicyWeekOff::where('pwo_b_id', $business_id)->first();
        $isUnpaidWeekOff = $weekoffPolicy->pwo_is_unpaid ?? 0;

        $attendanceDetails = [];

        // 4️⃣ Loop through employees
        foreach ($employees as $employee) {
            $weekOffDates = CentralLogics::getWeekOffDatesReport($employee, null, null, $weekStart, $weekEnd);

            // Get attendance summary using CentralLogics
            $attendanceSummary = CentralLogics::newGetMonthlyAttendanceDetails(
                $employee,
                $month,
                $year,
                $holidaysByDate,
                $weekOffDates
            );

            // Get week-off summary
            $weekOffSummary = self::getWeekOffAttendanceSummary($employee, $attendanceSummary, $month, $year);

            // Filter week-off summary to only include dates within the week
            $filteredWeekOffSummary = collect($weekOffSummary['weekOffSummary'])->map(function ($weekOff) use ($weekStart, $weekEnd) {
                $filteredDates = collect($weekOff['dates'])->filter(function ($dateInfo) use ($weekStart, $weekEnd) {
                    $date = Carbon::parse($dateInfo['date']);

                    return $date->between($weekStart, $weekEnd);
                });

                return [
                    'day_name' => $weekOff['day_name'],
                    'is_unpaid' => $weekOff['is_unpaid'],
                    'full_day_present_count' => $filteredDates->where('status_id', 251)->count(),
                    'half_day_present_count' => $filteredDates->where('status_id', 252)->count(),
                    'dates' => $filteredDates->toArray(),
                    'unpaidWeekOffCount' => $weekOff['is_unpaid'] ? $filteredDates->count() : 0,
                ];
            });

            $totalUnpaidWeekOffCount = $filteredWeekOffSummary->sum('unpaidWeekOffCount');
            $weekOffFullDayPresentCount = $filteredWeekOffSummary->sum('full_day_present_count');
            $weekOffHalfDayPresentCount = $filteredWeekOffSummary->sum('half_day_present_count');
            $weekOffPresent = $weekOffFullDayPresentCount + ($weekOffHalfDayPresentCount * 0.5);

            // Filter attendance summary to only include dates within the week
            $filteredAttendanceSummary = collect($attendanceSummary)->filter(function ($day) use ($weekStart, $weekEnd) {
                $dayDate = Carbon::parse($day['date'] ?? $day['atd_date']);

                return $dayDate->between($weekStart, $weekEnd);
            });

            $presentCount = $filteredAttendanceSummary->sum('presentCount');
            $halfDayCount = $filteredAttendanceSummary->sum('halfDayCount');
            $leaveCount = $filteredAttendanceSummary->sum('leaveCount');
            $absentCount = $filteredAttendanceSummary->sum('absentCount');
            $holidayCount = $filteredAttendanceSummary->sum('holidayCount');
            $missedPunchCount = $filteredAttendanceSummary->sum('missedPunchCount');
            $lateCount = $filteredAttendanceSummary->sum('lateCount');
            $earlyExitCount = $filteredAttendanceSummary->sum('earlyExitCount');
            $overtimeCount = $filteredAttendanceSummary->sum('overtimeCount');
            $UPL = $filteredAttendanceSummary->sum('UPL');
            $weekOffCount = $filteredAttendanceSummary->sum('weekOffCount');
            $overtimeHours = $filteredAttendanceSummary->sum('OT');

            $totalDays = $weekStart->diffInDays($weekEnd) + 1;
            $totalDays -= $totalUnpaidWeekOffCount;
            $total = $presentCount + $weekOffCount + $holidayCount + $leaveCount;

            $attendanceDetails[] = [
                'employee' => $employee,
                'week' => $week,
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
            ];
        }

        return $attendanceDetails;
    }

    /**
     * Get Weekly Attendance Summary for a single employee
     */
    private function getWeeklyAttendanceSummaryForEmployee($employee, $weekStart, $weekEnd, $holidaysByDate, $weekOffDates)
    {
        $attendance = AttendanceRecord::where('atd_emp_id', $employee->emp_id)
            ->whereBetween('atd_date', [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')])
            ->get();

        $presentCount = 0;
        $absentCount = 0;
        $lateCount = 0;
        $earlyExitCount = 0;
        $leaveCount = 0;
        $holidayCount = 0;
        $weekOffCount = 0;
        $halfDayCount = 0;
        $missedPunchCount = 0;
        $overtimeCount = 0;
        $UPL = 0;
        $overtimeHours = 0;

        $currentDate = $weekStart->copy();
        while ($currentDate <= $weekEnd) {
            $dateString = $currentDate->toDateString();
            $attendanceRecord = $attendance->firstWhere('atd_date', $dateString);

            // Check if it's a holiday
            if ($holidaysByDate->has($dateString)) {
                $holidayCount++;
            }
            // Check if it's a weekoff
            elseif (in_array($dateString, $weekOffDates)) {
                $weekOffCount++;
            }
            // Check attendance record
            elseif ($attendanceRecord) {
                if ($attendanceRecord->atd_status == 'PRESENT') {
                    $presentCount++;
                    if ($attendanceRecord->atd_late_entry > 0) {
                        $lateCount++;
                    }
                    if ($attendanceRecord->atd_early_exit > 0) {
                        $earlyExitCount++;
                    }
                } elseif ($attendanceRecord->atd_status == 'LEAVE') {
                    $leaveCount++;
                } elseif ($attendanceRecord->atd_status == 'HALF_DAY') {
                    $halfDayCount++;
                    $presentCount += 0.5;
                } else {
                    $absentCount++;
                }
            }
            // No attendance record = absent
            else {
                $absentCount++;
            }

            $currentDate->addDay();
        }

        // Calculate total for the week (present + weekoff + holiday + leave)
        $total = $presentCount + $weekOffCount + $holidayCount + $leaveCount;

        return [
            'presentCount' => $presentCount,
            'absentCount' => $absentCount,
            'lateCount' => $lateCount,
            'earlyExitCount' => $earlyExitCount,
            'leaveCount' => $leaveCount,
            'holidayCount' => $holidayCount,
            'weekOffCount' => $weekOffCount,
            'halfDayCount' => $halfDayCount,
            'missedPunchCount' => $missedPunchCount,
            'overtimeCount' => $overtimeCount,
            'UPL' => $UPL,
            'overtimeHours' => $overtimeHours,
            'totalDays' => 7,
            'total' => $total,
        ];
    }

    /**
     * Update Weekly Processed Flag
     */
    public function updateProcessedFlag(Request $request)
    {
        try {
            $weekId = $request->week_id;
            $payrollId = $request->payroll_id;

            \Log::info('Update processed flag called', [
                'week_id' => $weekId,
                'payroll_id' => $payrollId,
            ]);

            $week = PayrollPeriodWeek::where('ppw_id', $weekId)
                ->where('ppw_pp_id', $payrollId)
                ->first();

            if (! $week) {
                return response()->json([
                    'success' => false,
                    'message' => 'Week not found',
                ], 404);
            }

            // Update week status
            $week->ppw_is_processed = true;
            $week->ppw_status = PayrollPeriodWeek::STATUS_PROCESSED;
            $week->ppw_processed_at = now();
            $week->ppw_processed_by = auth()->id();

            // Update processed count
            $processedCount = ProcessedEmployeeSalary::where('ps_payroll_id', $payrollId)
                ->where('ps_week_id', $weekId)
                ->count();
            $week->ppw_processed_count = $processedCount;

            $week->save();

            return response()->json([
                'success' => true,
                'message' => 'Week processed flag updated successfully',
                'processed_count' => $processedCount,
            ]);
        } catch (\Exception $e) {
            \Log::error('Update processed flag error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update processed flag: '.$e->getMessage(),
            ], 500);
        }
    }

    public function checkWeeklyProcessedStatus(Request $request)
    {
        try {
            $periodId = $request->payroll_id;
            $weekId = $request->week_id;
            $businessId = auth()->user()->emp_b_id;
            $employeeIds = $request->employee_ids ?? [];

            // Get all employees with attendance summary for this week
            $query = AttendanceSummary::where('as_pp_id', $periodId)
                ->where('as_week_id', $weekId)
                ->where('as_b_id', $businessId);

            // If specific employee IDs provided, filter them
            if (! empty($employeeIds)) {
                $query->whereIn('as_emp_id', $employeeIds);
            }

            $attendanceSummaries = $query->get();

            $employeeData = [];

            foreach ($attendanceSummaries as $summary) {
                $employee = Employee::find($summary->as_emp_id);

                // Check if processed salary exists
                $hasProcessedSalary = ProcessedEmployeeSalary::where('ps_emp_id', $summary->as_emp_id)
                    ->where('ps_payroll_id', $periodId)
                    ->where('ps_week_id', $weekId)
                    ->where('ps_b_id', $businessId)
                    ->exists();

                $employeeData[] = [
                    'emp_id' => $summary->as_emp_id,
                    'emp_name' => $employee->emp_full_name ?? 'N/A',
                    'emp_code' => $employee->emp_code ?? 'N/A',
                    'department' => $employee->fh_department->d_name ?? 'N/A',
                    'as_is_sal_processed' => $summary->as_is_sal_processed ?? null,
                    'has_processed_salary' => $hasProcessedSalary,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $employeeData,
                'total_employees' => count($employeeData),
                'message' => 'Employee status fetched successfully',
            ]);
        } catch (\Exception $e) {
            \Log::error('Check Weekly Processed Status Error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch employee status: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Weekly WeekOff Summary for a single employee
     */
    private function getWeeklyWeekOffSummary($employee, $weekStart, $weekEnd, $weekOffDates)
    {
        $attendance = AttendanceRecord::where('atd_emp_id', $employee->emp_id)
            ->whereBetween('atd_date', [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')])
            ->get();

        $fullDayPresentCount = 0;
        $halfDayPresentCount = 0;
        $unpaidWeekOffCount = 0;

        foreach ($weekOffDates as $date) {
            $attendanceRecord = $attendance->firstWhere('atd_date', $date);

            if ($attendanceRecord && $attendanceRecord->atd_status == 'PRESENT') {
                $fullDayPresentCount++;
            } elseif ($attendanceRecord && $attendanceRecord->atd_status == 'HALF_DAY') {
                $halfDayPresentCount++;
            } else {
                $unpaidWeekOffCount++;
            }
        }

        return [
            'full_day_present_count' => $fullDayPresentCount,
            'half_day_present_count' => $halfDayPresentCount,
            'unpaid_weekoff_count' => $unpaidWeekOffCount,
            'total_unpaid_weekoff' => $unpaidWeekOffCount,
            'weekoff_present' => $fullDayPresentCount + ($halfDayPresentCount * 0.5),
        ];
    }

    /**
     * Get Week Off Attendance Summary
     */
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

    /**
     * Process Weekly Payroll - Main Entry Point
     */
    public function processWeeklyPayroll($periodId, $weekId)
    {
        $business_id = Auth::user()->emp_b_id;

        // Get payroll period
        $payrollPeriod = PayrollPeriod::where('pp_id', $periodId)
            ->where('pp_b_id', $business_id)
            ->firstOrFail();
        // dd($payrollPeriod);

        // Get week
        $week = PayrollPeriodWeek::where('ppw_id', $weekId)
            ->where('ppw_pp_id', $periodId)
            ->where('ppw_b_id', $business_id)
            ->firstOrFail();

        // Get week dates
        $weekStart = Carbon::parse($week->ppw_start_date);
        $weekEnd = Carbon::parse($week->ppw_end_date);
        $weekNumber = $week->ppw_week_number;
        $monthName = $week->month->m_name ?? date('F');
        $year = $weekStart->year;

        // Get all active employees
        $employees = Employee::with(['fh_department', 'fh_designation'])
            ->where('emp_b_id', $business_id)
            ->where('emp_status', 71)
            ->whereDate('emp_date_of_joining', '<=', $weekEnd)
            ->where(function ($q) use ($weekStart) {
                $q->whereNull('emp_last_working_date')
                    ->orWhereDate('emp_last_working_date', '>=', $weekStart);
            })
            ->get();

        // Calculate statistics
        $totalEmployees = $employees->count();
        $totalActiveEmployees = $employees->count();
        $totalInactiveEmp = 0;
        $readyToProcess = $employees->count();

        $heldEmployees = SalaryHold::where('sh_pp_id', $periodId)
            ->where('sh_b_id', $business_id)
            ->where('sh_status', 'held')
            ->count();

        $processedEmployeesCount = ProcessedEmployeeSalary::where('ps_payroll_id', $periodId)
            ->where('ps_week_id', $weekId)
            ->where('ps_b_id', $business_id)
            ->count();

        $pendingEmployees = $totalEmployees - $heldEmployees - $processedEmployeesCount;

        // Week status
        $isFrozen = $week->ppw_is_frozen ?? false;
        // Real processed count (week-scoped + legacy fallback for NULL/0 ps_week_id)
        [$processedQuery] = $this->processedSalariesForWeekQuery($periodId, $weekId, $business_id);
        $realProcessedCount = $processedQuery->count();

        // Reconcile week flags if DB is inconsistent
        if ((bool) ($week->ppw_is_processed ?? false) && $realProcessedCount === 0) {
            $week->ppw_is_processed = false;
            $week->ppw_processed_count = 0;
            $week->ppw_processed_at = null;
            $week->ppw_processed_by = null;
            $week->save();
        } elseif ($realProcessedCount > 0 && (int) ($week->ppw_processed_count ?? 0) !== $realProcessedCount) {
            $week->ppw_processed_count = $realProcessedCount;
            $week->ppw_is_processed = true;
            $week->save();
        }

        $processedEmployeesCount = $realProcessedCount;
        $pendingEmployees = $totalEmployees - $heldEmployees - $processedEmployeesCount;
        $isProcessed = $realProcessedCount > 0;
        $status = $this->normalizeWeeklyStatus($week->ppw_status ?? 'pending');
        $hasHolds = $heldEmployees > 0;

        $currentStep = $this->getWeeklyStepFromStatus($status, (bool) $isFrozen, (bool) $isProcessed, $realProcessedCount);

        // Pending requests data
        $pendingRequests = $this->getWeeklyPendingRequestsSummary($periodId, $weekId, $business_id);
        // Calculate total weeks in month
        $startOfMonth = $weekStart->copy()->startOfMonth();
        $endOfMonth = $weekStart->copy()->endOfMonth();
        $totalWeeksInMonth = ceil($startOfMonth->diffInDays($endOfMonth) / 7);

        $isWeeklyPayrollMode = $this->isWeeklyPayslipUiEnabled((int) $business_id);

        // Return the weekly process view
        return view('admin.payroll.weekly_payrun.payroll-new-process-weekly', compact(
            'payrollPeriod',
            'week',
            'weekNumber',
            'weekStart',
            'weekEnd',
            'monthName',
            'year',
            'employees',
            'isFrozen',
            'isProcessed',
            'status',
            'hasHolds',
            'currentStep',
            'totalEmployees',
            'totalActiveEmployees',
            'totalInactiveEmp',
            'readyToProcess',
            'heldEmployees',
            'processedEmployeesCount',
            'pendingEmployees',
            'pendingRequests',
            'totalWeeksInMonth',
            'isWeeklyPayrollMode'
        ));
    }

    /**
     * Unfreeze Weekly Attendance - Delete attendance summaries
     */
    public function unfreezeWeeklyAttendance(Request $request, $payrollId)
    {
        $business_id = auth()->user()->emp_b_id;

        try {
            $weekId = $request->input('week_id');
            $weekNumber = $request->input('week_number');

            DB::beginTransaction();

            // Find the week - prioritize week_id first
            $week = null;

            if ($weekId) {
                $week = PayrollPeriodWeek::where('ppw_id', $weekId)
                    ->where('ppw_pp_id', $payrollId)
                    ->first();
            }

            if (! $week && $weekNumber) {
                $week = PayrollPeriodWeek::where('ppw_pp_id', $payrollId)
                    ->where('ppw_week_number', $weekNumber)
                    ->first();
            }

            if (! $week) {
                return response()->json([
                    'success' => false,
                    'message' => 'Week not found. Please check week_id or week_number.',
                ], 404);
            }

            // Delete attendance summaries for this week
            $deletedCount = AttendanceSummary::where('as_pp_id', $payrollId)
                ->where('as_week_id', $week->ppw_id)
                ->where('as_b_id', $business_id)
                ->where(function ($query) {
                    $query->where('as_is_sal_processed', 121)
                        ->orWhere('as_is_sal_processed', 0)
                        ->orWhereNull('as_is_sal_processed');
                })
                ->delete();

            \Log::info('Attendance summaries deleted during unfreeze', [
                'week_id' => $week->ppw_id,
                'payroll_id' => $payrollId,
                'deleted_count' => $deletedCount,
            ]);

            // Delete processed salary records for this week
            $deletedProcessed = ProcessedEmployeeSalary::where('ps_payroll_id', $payrollId)
                ->where('ps_week_id', $week->ppw_id)
                ->where('ps_b_id', $business_id)
                ->delete();

            \Log::info('Processed salaries deleted during unfreeze', [
                'week_id' => $week->ppw_id,
                'deleted_count' => $deletedProcessed,
            ]);

            // Update week status
            $week->ppw_is_frozen = false;
            $week->ppw_status = PayrollPeriodWeek::STATUS_UNDER_REVIEW;
            $week->ppw_frozen_at = null;
            $week->ppw_frozen_by = null;
            $week->ppw_employee_count = 0;
            $week->ppw_processed_count = 0;
            $week->ppw_is_processed = false;
            $week->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Attendance records successfully unfrozen for Week {$week->ppw_week_number}. {$deletedCount} records deleted.",
                'deleted_attendance_count' => $deletedCount,
                'deleted_processed_count' => $deletedProcessed,
                'status' => $week->ppw_status,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Unfreeze Weekly Attendance Failed', [
                'payroll_id' => $payrollId,
                'week_id' => $weekId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to unfreeze attendance: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Weekly Attendance Data (AJAX)
     */
    public function getWeeklyAttendance($periodId, $weekId)
    {
        $business_id = Auth::user()->emp_b_id;

        $week = PayrollPeriodWeek::where('ppw_id', $weekId)
            ->where('ppw_pp_id', $periodId)
            ->firstOrFail();

        $weekStart = Carbon::parse($week->ppw_start_date);
        $weekEnd = Carbon::parse($week->ppw_end_date);

        // Get attendance details for this week
        $attendanceDetails = $this->getWeeklyAttendanceDetails($week);

        $attendanceData = [];

        foreach ($attendanceDetails as $row) {
            $empId = $row['employee']->emp_id;

            $attendanceData[$empId] = [
                'emp_id' => $empId,
                'emp_code' => $row['employee']->emp_code,
                'payroll_id' => $periodId,
                'week_id' => $weekId,
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
                'total' => $row['attendance_summary']['total'],
            ];
        }

        // Generate HTML using the partial view
        $html = view('admin.payroll.weekly_payrun.partials.weekly-attendance-rows', compact('attendanceDetails'))->render();

        return response()->json([
            'success' => true,
            'attendanceData' => $attendanceData,
            'html' => $html,
        ]);
    }

    /**
     * Get Weekly Attendance Summary for an Employee
     */
    private function getWeeklyAttendanceSummary($employee, $weekStart, $weekEnd, $holidaysByDate, $weekOffDates)
    {
        // FIXED: Use correct column names based on your AttendanceRecord model
        $attendance = AttendanceRecord::where('atd_emp_id', $employee->emp_id)
            ->whereBetween('atd_date', [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')])
            ->get();

        // REMOVED THE dd() STATEMENT THAT WAS CAUSING THE ERROR
        // dd($attendance);  // <-- REMOVE THIS LINE

        $presentCount = 0;
        $absentCount = 0;
        $lateCount = 0;
        $earlyCount = 0;
        $leaveCount = 0;
        $holidayCount = 0;
        $weekOffCount = 0;

        $currentDate = $weekStart->copy();
        while ($currentDate <= $weekEnd) {
            $dateString = $currentDate->toDateString();
            $attendanceRecord = $attendance->firstWhere('atd_date', $dateString);

            if ($holidaysByDate->has($dateString)) {
                $holidayCount++;
            } elseif (in_array($dateString, $weekOffDates)) {
                $weekOffCount++;
            } elseif ($attendanceRecord) {
                // FIXED: Use correct column names
                if ($attendanceRecord->atd_status == 'PRESENT') {
                    $presentCount++;
                    if ($attendanceRecord->atd_late_entry > 0) {
                        $lateCount++;
                    }
                    if ($attendanceRecord->atd_early_exit > 0) {
                        $earlyCount++;
                    }
                } elseif ($attendanceRecord->atd_status == 'LEAVE') {
                    $leaveCount++;
                } else {
                    $absentCount++;
                }
            } else {
                $absentCount++;
            }

            $currentDate->addDay();
        }

        return [
            'present' => $presentCount,
            'absent' => $absentCount,
            'late' => $lateCount,
            'early' => $earlyCount,
            'leave' => $leaveCount,
            'holiday' => $holidayCount,
            'week_off' => $weekOffCount,
        ];
    }

    /**
     * Freeze Weekly Attendance
     */
    public function freezeWeeklyAttendanceChunk(Request $request)
    {

        try {
            // Get JSON input
            $data = $request->json()->all();

            $validator = Validator::make($data, [
                'payroll_id' => 'required|integer',
                'week_id' => 'required|integer',
                'attendance_chunk' => 'required|array',
                'attendance_chunk.*.emp_code' => 'required',
                'chunk_number' => 'nullable|integer',
                'total_chunks' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $attendanceChunk = $data['attendance_chunk'];
            $periodId = $data['payroll_id'];
            $weekId = $data['week_id'];
            $chunkNumber = $data['chunk_number'] ?? 1;
            $totalChunks = $data['total_chunks'] ?? 1;

            DB::beginTransaction();

            try {
                $week = PayrollPeriodWeek::where('ppw_id', $weekId)
                    ->where('ppw_pp_id', $periodId)
                    ->first();

                if (! $week) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Week not found',
                    ], 404);
                }

                // Check if week status allows freezing (allow open/pending/configured/under_review)
                $allowedStatuses = ['open', 'pending', 'configured', 'pending_approval', 'under_review'];
                if (! in_array($week->ppw_status, $allowedStatuses)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot freeze attendance. Current status: '.$week->ppw_status,
                    ], 403);
                }

                $payrollPeriod = PayrollPeriod::find($periodId);
                if (! $payrollPeriod) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Payroll period not found',
                    ], 404);
                }

                $processedCount = 0;
                $pendingLeaveEmployees = [];
                $pendingMissPunchEmployees = [];
                $notFoundEmployees = [];

                // Get week start and end dates for validation
                $weekStart = Carbon::parse($week->ppw_start_date);
                $weekEnd = Carbon::parse($week->ppw_end_date);
                $yearMonth = $weekStart->format('Y-m');

                // Check if overtime is enabled
                $otEnabled = OvertimePolicy::where('ot_b_id', $payrollPeriod->pp_b_id)->value('ot_is_enabled') ?? false;

                foreach ($attendanceChunk as $row) {
                    if (empty($row['emp_code']) && empty($row['emp_id'])) {
                        continue;
                    }

                    // Find employee
                    $employee = Employee::select('emp_id', 'emp_code', 'emp_b_id', 'emp_br_id', 'emp_d_id')
                        ->where(function ($query) use ($row) {
                            if (! empty($row['emp_code'])) {
                                $query->where('emp_code', $row['emp_code']);
                            }
                            if (! empty($row['emp_id']) && is_numeric($row['emp_id'])) {
                                $query->orWhere('emp_id', $row['emp_id']);
                            }
                            if (! empty($row['emp_id']) && ! is_numeric($row['emp_id'])) {
                                $query->orWhere('emp_code', $row['emp_id']);
                            }
                        })
                        ->first();

                    if (! $employee) {
                        $notFoundEmployees[] = [
                            'emp_code' => $row['emp_code'] ?? null,
                            'emp_id' => $row['emp_id'] ?? null,
                        ];

                        continue;
                    }

                    // Check leave pending for this week
                    $leavePending = LeaveRequest::where('lvr_b_id', $employee->emp_b_id)
                        ->where('lvr_emp_id', $employee->emp_id)
                        ->where('lvr_stage_completed', 0)
                        ->whereNull('lvr_p_id')
                        ->whereNotIn('lvr_status', [170])
                        ->where(function ($q) use ($weekStart, $weekEnd) {
                            $q->whereBetween('lvr_start_date', [$weekStart, $weekEnd])
                                ->orWhereBetween('lvr_end_date', [$weekStart, $weekEnd])
                                ->orWhere(function ($sq) use ($weekStart, $weekEnd) {
                                    $sq->where('lvr_start_date', '<=', $weekStart)
                                        ->where('lvr_end_date', '>=', $weekEnd);
                                });
                        })
                        ->exists();

                    if ($leavePending) {
                        $pendingLeaveEmployees[] = [
                            'emp_id' => $employee->emp_id,
                            'emp_code' => $employee->emp_code,
                            'emp_name' => $row['emp_name'] ?? '',
                        ];

                        continue;
                    }

                    // Check miss punch pending for this week
                    $missPunchPending = AttendanceException::where('ae_b_id', $employee->emp_b_id)
                        ->where('ae_emp_id', $employee->emp_id)
                        ->where('ae_stage_completed', 0)
                        ->whereNotIn('ae_status', [139, 192, 156, 170])
                        ->whereNotNull('ae_am_id')
                        ->whereBetween('ae_date', [$weekStart, $weekEnd])
                        ->exists();

                    if ($missPunchPending) {
                        $pendingMissPunchEmployees[] = [
                            'emp_id' => $employee->emp_id,
                            'emp_code' => $employee->emp_code,
                            'emp_name' => $row['emp_name'] ?? '',
                        ];

                        continue;
                    }

                    // Calculate values from the attendance data
                    $totalDays = (float) ($row['total_days'] ?? 7);
                    $presentCount = (float) ($row['presentCount'] ?? 0);
                    $halfDayCount = (float) ($row['halfDayCount'] ?? 0);
                    $leaveCount = (float) ($row['leaveCount'] ?? 0);
                    $absentCount = (float) ($row['absentCount'] ?? 0);
                    $weekOffCount = (float) ($row['weekOffCount'] ?? 0);
                    $weekOffPresentCount = (float) ($row['weekOffPresentCount'] ?? 0);
                    $holidayCount = (float) ($row['holidayCount'] ?? 0);
                    $lateCount = (float) ($row['lateCount'] ?? 0);
                    $earlyExitCount = (float) ($row['earlyExitCount'] ?? 0);
                    $missedPunchCount = (float) ($row['missedPunchCount'] ?? 0);
                    $overtimeCount = (float) ($row['overtimeCount'] ?? 0);
                    $uplCount = (float) ($row['UPL'] ?? 0);

                    // Keep worked days aligned with Freeze-page Total column (same as monthly flow).
                    $totalWorkedDays = (float) ($row['total'] ?? ($presentCount + ($halfDayCount * 0.5)));

                    // ✅ SAVE TO ATTENDANCE SUMMARY TABLE ONLY
                    $attendanceSummary = AttendanceSummary::updateOrCreate(
                        [
                            'as_emp_id' => $employee->emp_id,
                            'as_pp_id' => $periodId,
                            'as_week_id' => $weekId,  // Add week_id to identify weekly attendance
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
                            'as_is_sal_processed' => 121,
                            'as_is_frozen' => 1,
                            'as_frozen_at' => now(),
                            'as_frozen_by' => auth()->id(),
                        ]
                    );

                    if ($attendanceSummary) {
                        $processedCount++;
                        \Log::info('Weekly attendance saved successfully', [
                            'emp_id' => $employee->emp_id,
                            'emp_code' => $employee->emp_code,
                            'week_id' => $weekId,
                            'worked_days' => $totalWorkedDays,
                            'present_days' => $presentCount,
                            'half_days' => $halfDayCount,
                        ]);
                    }
                }

                // Check if there are any pending requests
                if (! empty($pendingLeaveEmployees) || ! empty($pendingMissPunchEmployees)) {
                    DB::rollBack();

                    return response()->json([
                        'success' => false,
                        'message' => 'Pending requests found. Please resolve before freezing.',
                        'pending_leave_employees' => $pendingLeaveEmployees,
                        'pending_miss_punch_employees' => $pendingMissPunchEmployees,
                        'stop_processing' => true,
                    ], 422);
                }

                // Log if any employees were not found
                if (! empty($notFoundEmployees)) {
                    \Log::warning('Some employees were not found while freezing weekly attendance', [
                        'week_id' => $weekId,
                        'chunk' => $chunkNumber,
                        'not_found' => $notFoundEmployees,
                    ]);
                }

                $isLastChunk = ($chunkNumber == $totalChunks);

                if ($isLastChunk) {
                    // Update week status
                    $week->freezeAttendance($processedCount, auth()->id());
                    $week->markAsProcessing(auth()->id());

                    \Log::info('Weekly attendance frozen completely', [
                        'week_id' => $weekId,
                        'payroll_id' => $periodId,
                        'total_processed' => $processedCount,
                        'total_chunks' => $totalChunks,
                    ]);
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Weekly attendance frozen successfully',
                    'processed_count' => $processedCount,
                    'is_last_chunk' => true,
                    'week_status' => self::STATUS_PROCESSING,
                    'next_step' => 'STEP3',  // ✅ Tell frontend to go to STEP3
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Weekly attendance chunk freeze failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'chunk_number' => $chunkNumber,
                    'week_id' => $weekId,
                    'payroll_id' => $periodId,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Database error: '.$e->getMessage(),
                ], 500);
            }
        } catch (\Exception $e) {
            \Log::error('Invalid request format for weekly freeze', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid request format: '.$e->getMessage(),
            ], 400);
        }
    }

    /**
     * Process Weekly Salaries
     */
    public function processWeeklySalaries(Request $request)
    {
        try {
            $request->validate([
                'payroll_id' => 'required|integer|exists:payroll_periods,pp_id',
                'week_id' => 'required|integer|exists:payroll_period_weeks,ppw_id',
                'selected_employees' => 'required|array',
                'selected_employees.*' => 'required|integer|exists:employees,emp_id',
            ]);

            $employeeIds = $request->selected_employees;
            $periodId = $request->payroll_id;
            $weekId = $request->week_id;
            $businessId = auth()->user()->emp_b_id;
            $authUser = auth()->id();

            $week = PayrollPeriodWeek::where('ppw_id', $weekId)
                ->where('ppw_pp_id', $periodId)
                ->where('ppw_b_id', $businessId)
                ->firstOrFail();

            DB::beginTransaction();

            $processedCount = 0;
            $alreadyProcessedCount = 0;
            $errors = [];

            foreach ($employeeIds as $empId) {
                try {
                    $result = PayrollLogics::processSingleWeeklyEmployeeSalary(
                        (int) $empId,
                        (int) $periodId,
                        (int) $weekId,
                        (int) $businessId,
                        $authUser
                    );

                    if (! empty($result['skipped'])) {
                        $alreadyProcessedCount++;

                        continue;
                    }

                    if (empty($result['success'])) {
                        $errors[] = $result['message'] ?? 'Unknown error';

                        continue;
                    }

                    $processedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Error processing employee ID {$empId}: ".$e->getMessage();
                    \Log::error('Weekly salary processing error: '.$e->getMessage(), [
                        'emp_id' => $empId,
                        'week_id' => $weekId,
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            // Update week status if any employees were processed
            $newWeekStatus = self::STATUS_VERIFICATION; // Go to verification

            if ($processedCount > 0) {
                [$processedQuery] = $this->processedSalariesForWeekQuery($periodId, $weekId, $businessId);
                $totalProcessed = $processedQuery->count();

                $week->ppw_processed_count = $totalProcessed;
                $week->ppw_is_processed = true;
                $week->ppw_status = $newWeekStatus;
                $week->ppw_processed_at = now();
                $week->ppw_processed_by = $authUser;
                $week->save();

                // Update main payroll period status
                $payrollPeriod = PayrollPeriod::find($periodId);
                if ($payrollPeriod && $payrollPeriod->pp_status_code != PayrollPeriod::STATUS_VERIFICATION) {
                    $payrollPeriod->pp_status_code = PayrollPeriod::STATUS_VERIFICATION;
                    $payrollPeriod->save();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Processed {$processedCount} employees for Week {$week->ppw_week_number}",
                'data' => [
                    'processed_count' => $processedCount,
                    'already_processed_count' => $alreadyProcessedCount,
                    'total_processed_now' => $week->ppw_processed_count,
                    'payroll_id' => $periodId,
                    'week_id' => $weekId,
                    'week_status' => $newWeekStatus,
                    'next_step' => 'STEP5', // Explicitly tell the frontend to go to STEP5
                    'errors' => $errors,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Process weekly salaries failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payroll_id' => $request->payroll_id ?? null,
                'week_id' => $request->week_id ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process salaries: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Revert ALL Weekly Salaries - Back to Processing
     * This method reverts ALL processed salaries back to pending state
     */
    public function revertAllWeeklySalaries(Request $request)
    {
        try {
            $request->validate([
                'payroll_id' => 'required|integer|exists:payroll_periods,pp_id',
                'week_id' => 'required|integer|exists:payroll_period_weeks,ppw_id',
            ]);

            $periodId = $request->payroll_id;
            $weekId = $request->week_id;
            $businessId = auth()->user()->emp_b_id;

            DB::beginTransaction();

            // Get all employees with processed flag (120 or legacy 1)
            $processedEmployees = AttendanceSummary::where('as_pp_id', $periodId)
                ->where('as_week_id', $weekId)
                ->where('as_b_id', $businessId)
                ->whereIn('as_is_sal_processed', [120, 1])  // processed (new/legacy)
                ->pluck('as_emp_id')
                ->toArray();

            if (empty($processedEmployees)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No processed salaries found to revert.',
                ]);
            }

            \Log::info('Reverting all weekly salaries', [
                'payroll_id' => $periodId,
                'week_id' => $weekId,
                'employee_count' => count($processedEmployees),
            ]);

            // Delete from processed_employee_salaries table
            $deletedCount = ProcessedEmployeeSalary::where('ps_payroll_id', $periodId)
                ->where('ps_week_id', $weekId)
                ->where('ps_b_id', $businessId)
                ->whereIn('ps_emp_id', $processedEmployees)
                ->delete();

            \Log::info('Deleted from processed_employee_salaries:', ['count' => $deletedCount]);

            // UPDATE attendance_summary: as_is_sal_processed from 1 to 121 (Pending/Unprocessed)
            $attendanceUpdated = AttendanceSummary::where('as_pp_id', $periodId)
                ->where('as_week_id', $weekId)
                ->where('as_b_id', $businessId)
                ->whereIn('as_emp_id', $processedEmployees)
                ->whereIn('as_is_sal_processed', [120, 1])
                ->update([
                    'as_is_sal_processed' => 121,  // 121 = Pending/Unprocessed
                    'updated_at' => now(),
                ]);

            \Log::info('Attendance summaries updated (1 → 121):', ['updated_count' => $attendanceUpdated]);

            // Update week status
            $week = PayrollPeriodWeek::where('ppw_id', $weekId)
                ->where('ppw_pp_id', $periodId)
                ->first();

            if ($week) {
                $week->ppw_processed_count = 0;
                $week->ppw_is_processed = false;
                $week->ppw_status = self::STATUS_PROCESSING; // Back to salary processing stage
                $week->ppw_processed_at = null;
                $week->ppw_processed_by = null;
                $week->save();
            }

            // Update main payroll period status if needed
            $remainingProcessed = ProcessedEmployeeSalary::where('ps_payroll_id', $periodId)
                ->where('ps_b_id', $businessId)
                ->count();

            if ($remainingProcessed == 0) {
                $payrollPeriod = PayrollPeriod::find($periodId);
                if ($payrollPeriod && $payrollPeriod->pp_status_code == PayrollPeriod::STATUS_VERIFICATION) {
                    $payrollPeriod->pp_status_code = PayrollPeriod::STATUS_PROCESSING;
                    $payrollPeriod->save();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'All salaries reverted successfully.',
                'data' => [
                    'reverted_count' => $attendanceUpdated,
                    'deleted_from_processed_table' => $deletedCount,
                    'payroll_id' => $periodId,
                    'week_id' => $weekId,
                    'week_status' => $week->ppw_status ?? self::STATUS_PROCESSING,
                    'redirect_to' => 'STEP3',
                    'next_step' => 'STEP3',
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Revert all weekly salaries failed: '.$e->getMessage(), [
                'payroll_id' => $request->payroll_id ?? null,
                'week_id' => $request->week_id ?? null,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to revert salaries: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Weekly Salary Data for STEP3
     */
    public function getWeeklySalaryData($periodId, $weekId)
    {
        $business_id = Auth::user()->emp_b_id;

        $week = PayrollPeriodWeek::where('ppw_id', $weekId)
            ->where('ppw_pp_id', $periodId)
            ->firstOrFail();

        // Employee statuses for Step-3 filter (master table based).
        $allEmployeeStatuses = MasterTable::where('m_group', 'STATUS')
            ->whereNotNull('m_name')
            ->select('m_id', 'm_name')
            ->get();

        // Prefer commonly used payroll statuses first: Active/Inactive/Resigned.
        $preferredStatusOrder = ['active', 'inactive', 'resigned', 'resigned employee'];
        $preferredStatuses = $allEmployeeStatuses
            ->filter(function ($status) use ($preferredStatusOrder) {
                return in_array(strtolower(trim((string) $status->m_name)), $preferredStatusOrder, true);
            });
        $remainingStatuses = $allEmployeeStatuses
            ->reject(function ($status) use ($preferredStatusOrder) {
                return in_array(strtolower(trim((string) $status->m_name)), $preferredStatusOrder, true);
            })
            ->sortBy('m_name')
            ->values();
        $employeeStatusFilters = $preferredStatuses
            ->values()
            ->merge($remainingStatuses)
            ->map(fn ($status) => [
                'id' => (int) $status->m_id,
                'name' => (string) $status->m_name,
            ])
            ->values();

        // Only include employees who have salary setup in employee_salaries.
        $salaryEmployeeIds = SalaryEmployeeSalary::where('es_b_id', $business_id)
            ->pluck('es_emp_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        // Get employees who have attendance summary for this week
        $attendanceSummaries = AttendanceSummary::where('as_pp_id', $periodId)
            ->where('as_week_id', $weekId)
            ->where('as_b_id', $business_id)
            ->whereIn('as_emp_id', $salaryEmployeeIds)
            ->get();

        $employeeData = [];

        foreach ($attendanceSummaries as $attendanceSummary) {
            $employee = Employee::with(['fh_department', 'fh_designation', 'fh_employee_status'])
                ->find($attendanceSummary->as_emp_id);

            if (! $employee) {
                continue;
            }

            $processedSalary = ProcessedEmployeeSalary::where('ps_payroll_id', $periodId)
                ->where('ps_b_id', $business_id)
                ->where('ps_emp_id', $employee->emp_id)
                ->where(function ($q) use ($weekId) {
                    $q->where('ps_week_id', $weekId)
                        ->orWhereNull('ps_week_id')
                        ->orWhere('ps_week_id', 0);
                })
                ->first();

            $isHeld = SalaryHold::where('sh_pp_id', $periodId)
                ->where('sh_week_id', $weekId)
                ->where('sh_emp_id', $employee->emp_id)
                ->where('sh_status', 'held')
                ->exists();

            // Use the exact frozen "Total" count stored in attendance summary.
            $freezePageTotalDays = (float) ($attendanceSummary->as_total_worked_days ?? 0);
            $isProcessedLegacy = in_array((int) ($attendanceSummary->as_is_sal_processed ?? 0), [120, 1], true);

            $employeeData[] = [
                'emp_id' => $employee->emp_id,
                'emp_name' => $employee->emp_full_name,
                'emp_code' => $employee->emp_code,
                'department' => $employee->fh_department->d_name ?? '',
                'designation' => $employee->fh_designation->dg_name ?? '',
                'week_days' => 7,
                'salary_days' => round($freezePageTotalDays, 2),
                'is_processed' => ($processedSalary ? true : false) || $isProcessedLegacy,
                'is_held' => $isHeld,
                'checked' => true,
                'ad_hoc_amount' => 0,
                'emp_status' => $employee->emp_status,
                'emp_status_name' => $employee->fh_employee_status->m_name ?? ('Status '.$employee->emp_status),
                'net_payable' => $processedSalary->ps_monthly_net_salary ?? 0,
            ];
        }

        // Include employees with no attendance summary
        $employeesWithNoSummary = Employee::where('emp_b_id', $business_id)
            ->whereIn('emp_id', $salaryEmployeeIds)
            ->whereIn('emp_status', [71, 72, 457, 458, 459, 460])
            ->whereNotIn('emp_id', $attendanceSummaries->pluck('as_emp_id'))
            ->with(['fh_department', 'fh_designation', 'fh_employee_status'])
            ->get();

        foreach ($employeesWithNoSummary as $employee) {
            $isHeld = SalaryHold::where('sh_pp_id', $periodId)
                ->where('sh_week_id', $weekId)
                ->where('sh_emp_id', $employee->emp_id)
                ->where('sh_status', 'held')
                ->exists();

            $employeeData[] = [
                'emp_id' => $employee->emp_id,
                'emp_name' => $employee->emp_full_name,
                'emp_code' => $employee->emp_code,
                'department' => $employee->fh_department->d_name ?? '',
                'designation' => $employee->fh_designation->dg_name ?? '',
                'week_days' => 7,
                'salary_days' => 0,
                'is_processed' => false,
                'is_held' => $isHeld,
                'checked' => true,
                'ad_hoc_amount' => 0,
                'emp_status' => $employee->emp_status,
                'emp_status_name' => $employee->fh_employee_status->m_name ?? ('Status '.$employee->emp_status),
                'net_payable' => 0,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'employees' => $employeeData,
                'summary' => [
                    'total_employees' => count($employeeData),
                    'week_number' => $week->ppw_week_number,
                    'week_name' => $week->ppw_week_name,
                    'week_start_date' => $week->ppw_start_date,
                    'week_end_date' => $week->ppw_end_date,
                    // Processed can be inferred either from processed_salaries rows (with legacy fallback)
                    // OR legacy attendance_summaries flags
                    'processed_count' => max(
                        (function () use ($periodId, $weekId, $business_id) {
                            [$q] = $this->processedSalariesForWeekQuery($periodId, $weekId, $business_id);

                            return $q->count();
                        })(),
                        AttendanceSummary::where('as_pp_id', $periodId)
                            ->where('as_week_id', $weekId)
                            ->where('as_b_id', $business_id)
                            ->whereIn('as_is_sal_processed', [120, 1])
                            ->count()
                    ),
                    'held_count' => SalaryHold::where('sh_pp_id', $periodId)
                        ->where('sh_week_id', $weekId)
                        ->where('sh_status', 'held')
                        ->count(),
                    'week_status' => $week->ppw_status, // Add this line
                    'should_show_step5' => in_array($week->ppw_status, [PayrollPeriodWeek::STATUS_VERIFICATION, PayrollPeriodWeek::STATUS_PROCESSED], true),
                    'employee_status_filters' => $employeeStatusFilters,
                ],
            ],
        ]);
    }

    /**
     * Weekly payslip list (separate flow for payroll cycle 441).
     */
    public function viewWeeklyPayslips(Request $request, int $payrollId, int $weekId)
    {
        $businessId = (int) auth()->user()->emp_b_id;
        $isWeeklyPayrollMode = $this->isWeeklyPayslipUiEnabled($businessId);

        if (! $isWeeklyPayrollMode) {
            return redirect()->route('payroll.payslip.list', ['payrollId' => $payrollId]);
        }

        $payrollPeriod = PayrollPeriod::with(['month', 'financialYear'])
            ->where('pp_id', $payrollId)
            ->where('pp_b_id', $businessId)
            ->firstOrFail();

        $week = PayrollPeriodWeek::where('ppw_id', $weekId)
            ->where('ppw_pp_id', $payrollId)
            ->where('ppw_b_id', $businessId)
            ->firstOrFail();

        [$processedQuery] = $this->processedSalariesForWeekQuery($payrollId, $weekId, $businessId);
        $processedSalaries = $processedQuery
            ->with(['employee.fh_department', 'employee.fh_designation'])
            ->orderByDesc('created_at')
            ->get();

        return view('admin.payroll.weekly_payrun.payslip-list-weekly', [
            'payrollPeriod' => $payrollPeriod,
            'week' => $week,
            'processedSalaries' => $processedSalaries,
            'isWeeklyPayrollMode' => $isWeeklyPayrollMode,
        ]);
    }

    /**
     * Weekly payslip preview HTML (AJAX).
     */
    public function getWeeklyEmployeePayslip(int $employeeId, Request $request)
    {
        if (! $request->ajax() && ! $request->wantsJson() && ! $request->expectsJson()) {
            abort(403, 'AJAX request required');
        }

        try {
            $validated = $request->validate([
                'payroll_id' => 'required|integer|exists:payroll_periods,pp_id',
                'week_id' => 'required|integer|exists:payroll_period_weeks,ppw_id',
            ]);

            $businessId = (int) auth()->user()->emp_b_id;

            $payrollId = (int) $validated['payroll_id'];
            $weekId = (int) $validated['week_id'];

            if (! $this->isWeeklyPayslipUiEnabled($businessId)) {
                $employee = Employee::with(['fh_department', 'fh_designation', 'fh_business'])
                    ->where('emp_id', $employeeId)
                    ->where('emp_b_id', $businessId)
                    ->first();

                if (! $employee) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Employee not found.',
                    ], 404);
                }

                $payrollPeriod = PayrollPeriod::where('pp_id', $payrollId)
                    ->where('pp_b_id', $businessId)
                    ->first();

                if (! $payrollPeriod) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Payroll period not found.',
                    ], 404);
                }

                $processedSalary = ProcessedEmployeeSalary::where('ps_emp_id', $employeeId)
                    ->where('ps_payroll_id', $payrollId)
                    ->where('ps_b_id', $businessId)
                    ->orderByDesc('created_at')
                    ->first();

                if (! $processedSalary) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Processed salary not found for this period.',
                    ], 404);
                }

                $earnings = ProcessedSalaryEarning::where('ps_id', $processedSalary->ps_id)->get();
                $deductions = ProcessedSalaryDeduction::where('ps_id', $processedSalary->ps_id)->get();

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
                ]);
            }

            $employee = Employee::with(['fh_department', 'fh_designation', 'fh_business'])
                ->where('emp_id', $employeeId)
                ->where('emp_b_id', $businessId)
                ->first();

            if (! $employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found.',
                ], 404);
            }

            $payrollPeriod = PayrollPeriod::where('pp_id', $payrollId)
                ->where('pp_b_id', $businessId)
                ->first();

            $week = PayrollPeriodWeek::where('ppw_id', $weekId)
                ->where('ppw_pp_id', $payrollId)
                ->where('ppw_b_id', $businessId)
                ->first();

            if (! $payrollPeriod || ! $week) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payroll period/week not found.',
                ], 404);
            }

            [$processedQuery] = $this->processedSalariesForWeekQuery($payrollId, $weekId, $businessId, [$employeeId]);
            $processedSalary = $processedQuery
                ->where('ps_emp_id', $employeeId)
                ->first();

            if (! $processedSalary) {
                return response()->json([
                    'success' => false,
                    'message' => 'Processed weekly salary not found.',
                ], 404);
            }

            $rollup = $this->buildEmployeeWeeklyPayslipRollup($employeeId, $payrollId, $businessId, false);

            $attendancePayslip = $this->buildWeeklyAttendancePayslipSnapshot(
                $employeeId,
                $payrollId,
                $weekId,
                $businessId,
                $processedSalary
            );

            $salaryMasterPerDayWage = $this->resolveWeeklyPayslipSalaryMasterPerDayWage($employeeId, $businessId);

            $html = view('admin.payroll.weekly_payrun.partials.payslip-preview-weekly', [
                'employee' => $employee,
                'payrollPeriod' => $payrollPeriod,
                'week' => $week,
                'processedSalary' => $processedSalary,
                'salaryMasterPerDayWage' => $salaryMasterPerDayWage,
                'weeklySummary' => $rollup['weeklySummary'],
                'periodTotals' => $rollup['periodTotals'],
                'period_net_salary_words' => $rollup['period_net_salary_words'],
                'highlightWeekId' => $weekId,
                'attendancePayslip' => $attendancePayslip,
            ])->render();

            return response()->json([
                'success' => true,
                'html' => $html,
            ]);
        } catch (\Throwable $e) {
            Log::error('Weekly payslip preview failed', [
                'employee_id' => $employeeId,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to load weekly payslip preview.',
            ], 500);
        }
    }

    /**
     * Weekly payslip download (separate function/template for 441).
     */
    public function downloadWeeklyPayslip(int $id)
    {
        $businessId = (int) auth()->user()->emp_b_id;
        if (! $this->isWeeklyPayslipUiEnabled($businessId)) {
            return redirect()->route('payrun.downloadPayslip', ['id' => $id]);
        }

        $processedSalary = ProcessedEmployeeSalary::where('ps_id', $id)
            ->where('ps_b_id', $businessId)
            ->firstOrFail();

        $pdf = $this->makeWeeklySalarySeparatePdf($processedSalary, $businessId, true);

        $employee = Employee::query()->findOrFail($processedSalary->ps_emp_id);
        $week = null;
        if (! empty($processedSalary->ps_week_id)) {
            $week = PayrollPeriodWeek::where('ppw_id', $processedSalary->ps_week_id)->first();
        }
        $employeeName = str_replace(' ', '', (string) $employee->emp_full_name);
        $weekLabel = $week ? ('Week'.$week->ppw_week_number) : 'Weekly';

        return $pdf->download("Payslip-{$weekLabel}-{$employeeName}.pdf");
    }

    /**
     * DomPDF instance for weekly payslip (single week + all weeks in period + optional line matrices).
     * Used by downloadWeeklyPayslip and PayrollPeriodController::viewPayslip2.
     */
    public function makeWeeklySalarySeparatePdf(ProcessedEmployeeSalary $processedSalary, int $businessId, bool $includeLineMatrices = true): DomPdfWrapper
    {
        if ((int) $processedSalary->ps_b_id !== $businessId) {
            abort(403);
        }

        return Pdf::loadView(
            'admin.employees.salary.weekly_salary_template_separate',
            $this->composeWeeklySalarySeparatePdfViewData($processedSalary, $businessId, $includeLineMatrices)
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function composeWeeklySalarySeparatePdfViewData(ProcessedEmployeeSalary $processedSalary, int $businessId, bool $includeLineMatrices): array
    {
        $employee = Employee::with([
            'fh_department',
            'fh_designation',
            'fh_branch',
            'fh_business.fh_admin',
        ])->findOrFail($processedSalary->ps_emp_id);

        $payrollPeriod = PayrollPeriod::where('pp_id', $processedSalary->ps_payroll_id)
            ->where('pp_b_id', $businessId)
            ->firstOrFail();

        $week = null;
        if (! empty($processedSalary->ps_week_id)) {
            $week = PayrollPeriodWeek::where('ppw_id', $processedSalary->ps_week_id)
                ->where('ppw_pp_id', $payrollPeriod->pp_id)
                ->where('ppw_b_id', $businessId)
                ->first();
        }

        $rollup = $this->buildEmployeeWeeklyPayslipRollup($employee->emp_id, $payrollPeriod->pp_id, $businessId, $includeLineMatrices);

        $attendancePayslip = $this->buildWeeklyAttendancePayslipSnapshot(
            $employee->emp_id,
            $payrollPeriod->pp_id,
            $processedSalary->ps_week_id ? (int) $processedSalary->ps_week_id : null,
            $businessId,
            $processedSalary
        );

        $salaryMasterPerDayWage = $this->resolveWeeklyPayslipSalaryMasterPerDayWage((int) $employee->emp_id, $businessId);

        $numberToWords = new NumberToWords;
        $numberTransformer = $numberToWords->getNumberTransformer('en');
        $netSalary = number_format((float) str_replace(',', '', (string) ($processedSalary->ps_monthly_net_salary ?? 0)), 2, '.', '');
        [$integerPart, $decimalPart] = explode('.', $netSalary) + [0, 0];
        $net_salary_words = ucfirst($numberTransformer->toWords((int) $integerPart)).' rupees';
        if ((int) $decimalPart > 0) {
            $net_salary_words .= ' and '.$numberTransformer->toWords((int) $decimalPart).' paise';
        }
        $net_salary_words .= ' only';

        $logoPath = $employee->fh_business->b_logo ?? null;
        $authUserId = $processedSalary->ps_generated_by;
        $authUser = Employee::find($authUserId)?->emp_full_name ?? 'System';

        $config = PayslipConfiguration::where('pc_b_id', $businessId)->latest()->first();
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

        $weeklyMatrixWeekHeaders = [];
        foreach ($rollup['weeklySummary'] as $wr) {
            $weeklyMatrixWeekHeaders[] = 'W'.((int) ($wr['week_number'] ?? 0));
        }

        return [
            'employee' => $employee,
            'processedSalary' => $processedSalary,
            'payrollPeriod' => $payrollPeriod,
            'week' => $week,
            'net_salary_words' => $net_salary_words,
            'salaryMasterPerDayWage' => $salaryMasterPerDayWage,
            'weeklySummary' => $rollup['weeklySummary'],
            'periodTotals' => $rollup['periodTotals'],
            'period_net_salary_words' => $rollup['period_net_salary_words'],
            'highlightWeekId' => $week?->ppw_id,
            'logoPath' => $logoPath,
            'authUser' => $authUser,
            'payslipOptions' => $payslipOptions,
            'attendancePayslip' => $attendancePayslip,
            'weekly_earning_rows' => $rollup['weekly_earning_rows'],
            'weekly_deduction_rows' => $rollup['weekly_deduction_rows'],
            'weekly_matrix_week_headers' => $weeklyMatrixWeekHeaders,
        ];
    }

    /**
     * Per-day wage for weekly payslip display — same resolution order as weekly salary master:
     * stored weekly meta (es_rem_allowance per_day_wage) → sm_per_day_wage → es_perday_salary,
     * then sm_per_day_gross, then base/30.
     */
    private function resolveWeeklyPayslipSalaryMasterPerDayWage(int $employeeId, int $businessId): ?float
    {
        $parseMoney = static function ($value): float {
            if ($value === null || $value === '') {
                return 0.0;
            }

            return (float) str_replace(',', '', (string) $value);
        };

        $salary = SalaryEmployeeSalary::where('es_emp_id', $employeeId)
            ->where('es_b_id', $businessId)
            ->first();

        $weeklyMeta = [];
        if ($salary && ! empty($salary->es_rem_allowance)) {
            $decoded = json_decode($salary->es_rem_allowance, true);
            if (is_array($decoded)) {
                $weeklyMeta = $decoded;
            }
        }

        $hist = SalaryMasterHistory::where('sm_emp_id', $employeeId)
            ->where('sm_emp_b_id', $businessId)
            ->orderByDesc('wef')
            ->orderByDesc('sm_id')
            ->first();

        foreach ([
            $weeklyMeta['per_day_wage'] ?? null,
            $hist?->sm_per_day_wage,
            $salary?->es_perday_salary,
        ] as $candidate) {
            $n = $parseMoney($candidate);
            if ($n > 0) {
                return round($n, 2);
            }
        }

        $fromGross = $parseMoney($hist?->sm_per_day_gross ?? 0);
        if ($fromGross > 0) {
            return round($fromGross, 2);
        }

        $base = $parseMoney($salary?->es_base_salary ?? 0);
        if ($base > 0) {
            return round($base / 30, 2);
        }

        return null;
    }

    /**
     * Weekly / period aggregates and line-item matrices for one employee (PDF + preview).
     *
     * @return array{
     *   weeklySummary: array,
     *   weeklyTotals: array,
     *   weekly_earning_rows: array,
     *   weekly_deduction_rows: array,
     *   periodTotals: array,
     *   period_net_salary_words: string
     * }
     */
    private function buildEmployeeWeeklyPayslipRollup(int $employeeId, int $payrollPeriodId, int $businessId, bool $includeLineMatrices = true): array
    {
        $weeks = PayrollPeriodWeek::where('ppw_pp_id', $payrollPeriodId)
            ->where('ppw_b_id', $businessId)
            ->orderBy('ppw_start_date')
            ->orderBy('ppw_week_number')
            ->get();

        $employeeWeeklySalaries = ProcessedEmployeeSalary::where('ps_b_id', $businessId)
            ->where('ps_payroll_id', $payrollPeriodId)
            ->where('ps_emp_id', $employeeId)
            ->whereNotNull('ps_week_id')
            ->get()
            ->keyBy('ps_week_id');

        $weekIds = $weeks->pluck('ppw_id')->map(fn ($id) => (int) $id)->filter()->values()->all();
        $attendanceByWeek = collect();
        if ($weekIds !== []) {
            $attendanceByWeek = AttendanceSummary::query()
                ->where('as_pp_id', $payrollPeriodId)
                ->where('as_b_id', $businessId)
                ->where('as_emp_id', $employeeId)
                ->whereIn('as_week_id', $weekIds)
                ->get()
                ->keyBy('as_week_id');
        }

        $weeklySummary = [];
        $weeklyTotals = [
            'gross' => 0.0,
            'employee_ded' => 0.0,
            'employer_ded' => 0.0,
            'net' => 0.0,
            'ctc' => 0.0,
        ];

        foreach ($weeks as $periodWeek) {
            $weekSalary = $employeeWeeklySalaries->get($periodWeek->ppw_id);
            $gross = (float) ($weekSalary?->ps_earnings ?? 0);
            $employeeDed = (float) ($weekSalary?->ps_employee_deductions ?? 0);
            $employerDed = (float) ($weekSalary?->ps_employer_deductions ?? 0);
            $net = (float) ($weekSalary?->ps_monthly_net_salary ?? 0);
            $ctc = (float) ($weekSalary?->ps_monthly_ctc ?? 0);

            /** @var AttendanceSummary|null $as */
            $as = $attendanceByWeek->get((int) $periodWeek->ppw_id);
            $salariedDays = (float) ($as?->as_total_worked_days ?? $weekSalary?->ps_total_days_worked ?? 0);
            $calendarDays = (float) ($as?->as_total_days ?? 0);
            if ($calendarDays <= 0) {
                $calendarDays = 7.0;
            }

            $weeklySummary[] = [
                'ppw_id' => (int) $periodWeek->ppw_id,
                'week_number' => (int) $periodWeek->ppw_week_number,
                'week_label' => 'Week '.$periodWeek->ppw_week_number.
                    ' ('.Carbon::parse($periodWeek->ppw_start_date)->format('d M').
                    ' - '.Carbon::parse($periodWeek->ppw_end_date)->format('d M').')',
                'start_date' => Carbon::parse($periodWeek->ppw_start_date)->format('d M'),
                'end_date' => Carbon::parse($periodWeek->ppw_end_date)->format('d M'),
                'gross' => $gross,
                'employee_ded' => $employeeDed,
                'employer_ded' => $employerDed,
                'total_ded' => $employeeDed + $employerDed,
                'net' => $net,
                'salaried_days' => $salariedDays,
                'calendar_days' => $calendarDays,
                'present' => (float) ($as?->as_total_present ?? $weekSalary?->ps_present_days ?? 0),
                'half_day' => (float) ($as?->as_total_half_day ?? 0),
                'absent' => (float) ($as?->as_total_absent ?? 0),
                'leave' => (float) ($as?->as_total_leave ?? 0),
                'weekoff' => (float) ($as?->as_total_weekoff ?? 0),
                'holiday' => (float) ($as?->as_total_holiday ?? 0),
                'weekoff_present' => (float) ($as?->as_total_weekoffPresent ?? 0),
                'upl' => (float) ($as?->as_total_upl_count ?? $weekSalary?->ps_upl_count ?? 0),
                'attendance_frozen' => (bool) ($as?->as_is_frozen ?? false),
            ];

            $weeklyTotals['gross'] += $gross;
            $weeklyTotals['employee_ded'] += $employeeDed;
            $weeklyTotals['employer_ded'] += $employerDed;
            $weeklyTotals['net'] += $net;
            $weeklyTotals['ctc'] += $ctc;
        }

        if ($includeLineMatrices) {
            [$weeklyEarningRows, $weeklyDeductionRows] = $this->buildWeeklyPayslipLineMatrices(
                $weeks,
                $employeeWeeklySalaries
            );
        } else {
            $weeklyEarningRows = [];
            $weeklyDeductionRows = [];
        }

        $periodTotals = [
            'ctc' => $weeklyTotals['ctc'],
            'gross' => $weeklyTotals['gross'],
            'emp_ded' => $weeklyTotals['employee_ded'],
            'empr_ded' => $weeklyTotals['employer_ded'],
            'net' => $weeklyTotals['net'],
        ];

        $numberToWords = new NumberToWords;
        $numberTransformer = $numberToWords->getNumberTransformer('en');
        $periodNetStr = number_format((float) $weeklyTotals['net'], 2, '.', '');
        [$pInt, $pDec] = explode('.', $periodNetStr) + [0, 0];
        $period_net_salary_words = ucfirst($numberTransformer->toWords((int) $pInt)).' rupees';
        if ((int) $pDec > 0) {
            $period_net_salary_words .= ' and '.$numberTransformer->toWords((int) $pDec).' paise';
        }
        $period_net_salary_words .= ' only';

        return [
            'weeklySummary' => $weeklySummary,
            'weeklyTotals' => $weeklyTotals,
            'weekly_earning_rows' => $weeklyEarningRows,
            'weekly_deduction_rows' => $weeklyDeductionRows,
            'periodTotals' => $periodTotals,
            'period_net_salary_words' => $period_net_salary_words,
        ];
    }

    /**
     * Frozen weekly attendance for payslip: salaried-day count matches freeze "Total" (as_total_worked_days).
     *
     * @return array<string, mixed>|null
     */
    private function buildWeeklyAttendancePayslipSnapshot(
        int $employeeId,
        int $payrollPeriodId,
        ?int $weekId,
        int $businessId,
        ?ProcessedEmployeeSalary $processedSalary
    ): ?array {
        if (! $weekId) {
            return null;
        }

        $as = AttendanceSummary::query()
            ->where('as_emp_id', $employeeId)
            ->where('as_pp_id', $payrollPeriodId)
            ->where('as_week_id', $weekId)
            ->where('as_b_id', $businessId)
            ->first();

        $processedDays = $processedSalary ? (float) ($processedSalary->ps_total_days_worked ?? 0) : 0.0;
        $salariedDays = (float) ($as?->as_total_worked_days ?? $processedDays);

        $calendarDays = (float) ($as?->as_total_days ?? 0);
        if ($calendarDays <= 0) {
            $calendarDays = 7.0;
        }

        $grossProcessed = $processedSalary ? (float) ($processedSalary->ps_earnings ?? 0) : 0.0;
        $impliedGrossPerDay = ($salariedDays > 0 && $grossProcessed > 0)
            ? round($grossProcessed / $salariedDays, 4)
            : null;

        $frozenAt = $as?->as_frozen_at
            ? Carbon::parse($as->as_frozen_at)->format('d M Y, H:i')
            : null;

        return [
            'has_attendance_row' => (bool) $as,
            'salaried_days' => $salariedDays,
            'calendar_days_in_week' => $calendarDays,
            'processed_salaried_days' => $processedDays,
            'days_match' => abs($salariedDays - $processedDays) < 0.005,
            'present' => (float) ($as?->as_total_present ?? $processedSalary?->ps_present_days ?? 0),
            'half_day' => (float) ($as?->as_total_half_day ?? 0),
            'absent' => (float) ($as?->as_total_absent ?? 0),
            'leave' => (float) ($as?->as_total_leave ?? 0),
            'weekoff' => (float) ($as?->as_total_weekoff ?? 0),
            'weekoff_present' => (float) ($as?->as_total_weekoffPresent ?? 0),
            'holiday' => (float) ($as?->as_total_holiday ?? 0),
            'missed_punch' => (float) ($as?->as_total_missed_punch ?? 0),
            'late' => (float) ($as?->as_days_late ?? $processedSalary?->ps_days_late ?? 0),
            'early_exit' => (float) ($as?->as_early_exit ?? 0),
            'ot_hours' => (float) ($as?->as_total_overtime_hours ?? 0),
            'upl' => (float) ($as?->as_total_upl_count ?? $processedSalary?->ps_upl_count ?? 0),
            'attendance_frozen' => (bool) ($as?->as_is_frozen ?? false),
            'frozen_at_display' => $frozenAt,
            'implied_gross_per_salaried_day' => $impliedGrossPerDay,
        ];
    }

    /**
     * Per-week columns for PDF: earning types and employee deduction types across the payroll period.
     *
     * @param  \Illuminate\Support\Collection<int, PayrollPeriodWeek>  $periodWeeks
     * @param  \Illuminate\Support\Collection<string|int, ProcessedEmployeeSalary>  $employeeWeeklySalaries  keyed by ps_week_id
     * @return array{0: array<int, array{description: string, amounts: float[], total: float}>, 1: array<int, array{description: string, amounts: float[], total: float}>}
     */
    private function buildWeeklyPayslipLineMatrices($periodWeeks, $employeeWeeklySalaries): array
    {
        $weekIds = $periodWeeks->pluck('ppw_id')->all();
        $earningAccumulator = [];
        $deductionAccumulator = [];

        foreach ($periodWeeks as $pw) {
            $ws = $employeeWeeklySalaries->get($pw->ppw_id);
            if (! $ws) {
                continue;
            }

            $earnings = ProcessedSalaryEarning::where('ps_id', $ws->ps_id)->get();
            foreach ($earnings as $e) {
                $label = trim((string) ($e->ps_earning_type ?? '')) ?: 'Earning';
                if (! isset($earningAccumulator[$label])) {
                    $earningAccumulator[$label] = array_fill_keys($weekIds, 0.0);
                }
                $earningAccumulator[$label][$pw->ppw_id] += (float) $e->ps_e_amount;
            }

            $deductions = ProcessedSalaryDeduction::where('ps_id', $ws->ps_id)
                ->where('ps_d_category', 'employee')
                ->get();
            foreach ($deductions as $d) {
                $label = trim((string) ($d->ps_deduction_type ?? '')) ?: 'Deduction';
                if (! isset($deductionAccumulator[$label])) {
                    $deductionAccumulator[$label] = array_fill_keys($weekIds, 0.0);
                }
                $deductionAccumulator[$label][$pw->ppw_id] += (float) $d->ps_d_amount;
            }
        }

        $toRows = function (array $acc) use ($periodWeeks) {
            $rows = [];
            foreach ($acc as $label => $byWeek) {
                $amounts = [];
                $total = 0.0;
                foreach ($periodWeeks as $pw) {
                    $v = (float) ($byWeek[$pw->ppw_id] ?? 0);
                    $amounts[] = $v;
                    $total += $v;
                }
                $rows[] = [
                    'description' => $label,
                    'amounts' => $amounts,
                    'total' => $total,
                ];
            }

            return $rows;
        };

        return [$toRows($earningAccumulator), $toRows($deductionAccumulator)];
    }

    private function isWeeklyPayslipUiEnabled(int $businessId): bool
    {
        $setting = PayrollMasterSetting::where('pms_b_id', $businessId)->first();

        return (int) ($setting->pms_payroll_cycle ?? 0) === 441;
    }

    /**
     * Preview employee weekly salary for step 3 View action.
     */
    public function getWeeklyEmployeeSalaryPreview(Request $request)
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|integer|exists:employees,emp_id',
                'payroll_id' => 'required|integer|exists:payroll_periods,pp_id',
                'week_id' => 'required|integer|exists:payroll_period_weeks,ppw_id',
            ]);

            $businessId = (int) auth()->user()->emp_b_id;
            $employeeId = (int) $validated['employee_id'];
            $payrollId = (int) $validated['payroll_id'];
            $weekId = (int) $validated['week_id'];

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

            $week = PayrollPeriodWeek::where('ppw_id', $weekId)
                ->where('ppw_pp_id', $payrollId)
                ->where('ppw_b_id', $businessId)
                ->first();

            if (! $week) {
                return response()->json([
                    'success' => false,
                    'message' => 'Weekly payroll period not found',
                ], 404);
            }

            $attendance = AttendanceSummary::where('as_emp_id', $employeeId)
                ->where('as_pp_id', $payrollId)
                ->where('as_week_id', $weekId)
                ->where('as_b_id', $businessId)
                ->first();

            if (! $attendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance summary not found for this week',
                ], 404);
            }

            $salary = SalaryEmployeeSalary::where('es_emp_id', $employeeId)
                ->where('es_b_id', $businessId)
                ->first();

            if (! $salary) {
                return response()->json([
                    'success' => false,
                    'message' => 'Salary setup not found for this employee',
                ], 404);
            }

            [$processedQuery] = $this->processedSalariesForWeekQuery($payrollId, $weekId, $businessId, [$employeeId]);
            $processedSalary = $processedQuery->first();

            $isHeld = SalaryHold::where('sh_pp_id', $payrollId)
                ->where('sh_week_id', $weekId)
                ->where('sh_emp_id', $employeeId)
                ->where('sh_status', 'held')
                ->exists();

            $weekDays = 7;
            $workedDays = (float) ($attendance->as_total_worked_days ?? 0);
            $presentDays = (float) ($attendance->as_total_present ?? 0);
            $halfDays = (float) ($attendance->as_total_half_day ?? 0);
            $leaveDays = (float) ($attendance->as_total_leave ?? 0);
            $absentDays = (float) ($attendance->as_total_absent ?? 0);

            $salaryHistory = SalaryMasterHistory::where('sm_emp_id', $employeeId)
                ->where('sm_emp_b_id', $businessId)
                ->orderByDesc('wef')
                ->orderByDesc('sm_id')
                ->first();
            $perDayWage = (float) ($salary->es_perday_salary ?? 0);
            if ($perDayWage <= 0) {
                $perDayWage = (float) (($salaryHistory->sm_per_day_gross ?? 0) ?: ($salaryHistory->sm_per_day_wage ?? 0));
            }
            if ($perDayWage <= 0) {
                $perDayWage = round(((float) ($salary->es_base_salary ?? 0)) / 30, 2);
            }

            $workedRatio = $weekDays > 0 ? ($workedDays / $weekDays) : 0;
            $proratedBasic = $perDayWage * $workedDays;
            $weeklyBasic = $proratedBasic;

            $hra = ((float) ($salary->es_hra ?? 0) / 4) * $workedRatio;
            $conveyance = ((float) ($salary->es_conveyance ?? 0) / 4) * $workedRatio;
            $medical = ((float) ($salary->es_medical ?? 0) / 4) * $workedRatio;
            $special = ((float) ($salary->es_special ?? 0) / 4) * $workedRatio;

            $calculatedEarnings = $proratedBasic + $hra + $conveyance + $medical + $special;
            $calculatedPf = $calculatedEarnings * 0.12;
            $calculatedEsi = $calculatedEarnings * 0.0075;
            $calculatedPt = $calculatedEarnings > 15000 ? 200 : 0;
            $calculatedDeductions = $calculatedPf + $calculatedEsi + $calculatedPt;
            $calculatedNet = $calculatedEarnings - $calculatedDeductions;

            $isProcessed = (bool) $processedSalary;
            $totalEarnings = $isProcessed ? (float) ($processedSalary->ps_earnings ?? 0) : $calculatedEarnings;
            $totalDeductions = $isProcessed ? (float) ($processedSalary->ps_employee_deductions ?? 0) : $calculatedDeductions;
            $netPayable = $isProcessed ? (float) ($processedSalary->ps_monthly_net_salary ?? 0) : $calculatedNet;

            return response()->json([
                'success' => true,
                'data' => [
                    'employee' => [
                        'id' => $employee->emp_id,
                        'code' => $employee->emp_code,
                        'name' => $employee->emp_full_name,
                        'department' => $employee->fh_department->d_name ?? 'N/A',
                        'designation' => $employee->fh_designation->dg_name ?? 'N/A',
                    ],
                    'week' => [
                        'id' => $week->ppw_id,
                        'name' => $week->ppw_week_name,
                        'number' => $week->ppw_week_number,
                        'start_date' => $week->ppw_start_date,
                        'end_date' => $week->ppw_end_date,
                        'total_days' => $weekDays,
                    ],
                    'attendance' => [
                        'worked_days' => round($workedDays, 2),
                        'present_days' => round($presentDays, 2),
                        'half_days' => round($halfDays, 2),
                        'leave_days' => round($leaveDays, 2),
                        'absent_days' => round($absentDays, 2),
                        'worked_percentage' => round($weekDays > 0 ? (($workedDays / $weekDays) * 100) : 0, 2),
                    ],
                    'earnings' => [
                        'per_day_wage' => round($perDayWage, 2),
                        'weekly_basic' => round($weeklyBasic, 2),
                        'prorated_basic' => round($proratedBasic, 2),
                        'hra' => round($hra, 2),
                        'conveyance' => round($conveyance, 2),
                        'medical' => round($medical, 2),
                        'special' => round($special, 2),
                        'total' => round($totalEarnings, 2),
                    ],
                    'deductions' => [
                        'pf' => round($calculatedPf, 2),
                        'esi' => round($calculatedEsi, 2),
                        'pt' => round($calculatedPt, 2),
                        'total' => round($totalDeductions, 2),
                    ],
                    'summary' => [
                        'net_payable' => round($netPayable, 2),
                        'is_processed' => $isProcessed,
                        'is_held' => $isHeld,
                        'preview_source' => $isProcessed ? 'processed_salary' : 'live_calculation',
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Weekly employee salary preview failed', [
                'message' => $e->getMessage(),
                'employee_id' => $request->employee_id ?? null,
                'payroll_id' => $request->payroll_id ?? null,
                'week_id' => $request->week_id ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to load employee salary preview.',
            ], 500);
        }
    }

    /**
     * Unprocess Selected Salaries for Weekly Payroll
     * This method reverts processed salaries back to pending state
     */
    public function unprocessSelectedSalaries(Request $request)
    {
        try {
            $request->validate([
                'payroll_id' => 'required|integer|exists:payroll_periods,pp_id',
                'week_id' => 'required|integer|exists:payroll_period_weeks,ppw_id',
                'selected_employees' => 'nullable|array',
                'selected_employees.*' => 'required|integer|exists:employees,emp_id',
            ]);

            $periodId = $request->payroll_id;
            $weekId = $request->week_id;
            $businessId = auth()->user()->emp_b_id;
            $selectedEmployees = $request->selected_employees ?? null;

            $week = PayrollPeriodWeek::where('ppw_id', $weekId)
                ->where('ppw_pp_id', $periodId)
                ->where('ppw_b_id', $businessId)
                ->firstOrFail();

            $oldStatus = $week->ppw_status;

            DB::beginTransaction();

            $unprocessResult = $this->unprocessWeeklySalaries($periodId, $weekId, $businessId, $selectedEmployees);
            if ($unprocessResult['deleted_count'] === 0) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'No processed salaries found for selected employees.',
                ]);
            }

            $week = $this->applyWeeklyStatusUpdate($periodId, $weekId, $businessId, self::STATUS_PROCESSING);
            $week->ppw_processed_count = $unprocessResult['remaining_processed'];
            $week->ppw_is_processed = $unprocessResult['remaining_processed'] > 0;
            if ($unprocessResult['remaining_processed'] === 0) {
                $week->ppw_processed_at = null;
                $week->ppw_processed_by = null;
            }
            $week->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Processed salaries deleted and moved back to processing successfully.',
                'data' => [
                    'payroll_id' => $periodId,
                    'week_id' => $weekId,
                    'old_status' => $oldStatus,
                    'new_status' => $week->ppw_status,
                    'deleted_processed_rows' => $unprocessResult['deleted_count'],
                    'attendance_marked_unprocessed' => $unprocessResult['attendance_updated'],
                    'affected_employees_count' => count($unprocessResult['affected_employee_ids']),
                    'remaining_processed' => $unprocessResult['remaining_processed'],
                    'next_step' => 'STEP3',
                ],
            ]);
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            \Log::error('Unprocess salaries failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to unprocess salaries: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save Weekly Draft
     */
    public function saveWeeklyDraft(Request $request)
    {
        try {
            $weekId = $request->week_id;
            $week = PayrollPeriodWeek::findOrFail($weekId);
            $week->ppw_status = PayrollPeriodWeek::STATUS_DRAFT;
            $week->ppw_draft_data = json_encode($request->except(['_token', 'week_id', 'payroll_id']));
            $week->save();

            return response()->json([
                'success' => true,
                'message' => 'Weekly payroll saved as draft',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function revertWeeklySalaries(Request $request)
    {
        try {
            $request->validate([
                'payroll_id' => 'required|integer|exists:payroll_periods,pp_id',
                'week_id' => 'nullable|integer|exists:payroll_period_weeks,ppw_id',
                'selected_employees' => 'required|array',
                'selected_employees.*' => 'required|integer|exists:employees,emp_id',
            ]);

            $employeeIds = $request->selected_employees;
            $periodId = $request->payroll_id;
            $weekId = $request->week_id;
            $businessId = auth()->user()->emp_b_id;

            DB::beginTransaction();

            // ✅ CHECK: Count employees with processed flag (120 or legacy 1)
            $countBefore = AttendanceSummary::where('as_pp_id', $periodId)
                ->where('as_b_id', $businessId)
                ->whereIn('as_emp_id', $employeeIds)
                ->whereIn('as_is_sal_processed', [120, 1])  // processed (new/legacy)
                ->when($weekId && $weekId > 0, function ($q) use ($weekId) {
                    $q->where('as_week_id', $weekId);
                })
                ->count();

            \Log::info('Revert check - Processed employees count (as_is_sal_processed=120):', [
                'count' => $countBefore,
                'employee_ids' => $employeeIds,
            ]);

            if ($countBefore === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No processed employees found (as_is_sal_processed = 120) for selected employees.',
                    'debug' => [
                        'payroll_id' => $periodId,
                        'week_id' => $weekId,
                        'employee_ids' => $employeeIds,
                        'required_status' => '120 (Processed)',
                    ],
                ]);
            }

            // ✅ Delete from processed_employee_salaries table
            $deletedCount = ProcessedEmployeeSalary::where('ps_payroll_id', $periodId)
                ->where('ps_b_id', $businessId)
                ->whereIn('ps_emp_id', $employeeIds)
                ->when($weekId && $weekId > 0, function ($q) use ($weekId) {
                    $q->where('ps_week_id', $weekId);
                })
                ->delete();

            \Log::info('Deleted from processed_employee_salaries:', ['count' => $deletedCount]);

            // ✅ UPDATE attendance_summary: as_is_sal_processed from 120 to 121 (Unprocessed)
            $attendanceUpdated = AttendanceSummary::where('as_pp_id', $periodId)
                ->where('as_b_id', $businessId)
                ->whereIn('as_emp_id', $employeeIds)
                ->whereIn('as_is_sal_processed', [120, 1])  // Only update processed ones (new/legacy)
                ->when($weekId && $weekId > 0, function ($q) use ($weekId) {
                    $q->where('as_week_id', $weekId);
                })
                ->update([
                    'as_is_sal_processed' => 121,  // 121 = Pending/Unprocessed
                    'updated_at' => now(),
                ]);

            \Log::info('Attendance summaries updated (120 → 121):', ['updated_count' => $attendanceUpdated]);

            // Update week processed count
            if ($weekId && $weekId > 0) {
                $remainingProcessed = AttendanceSummary::where('as_pp_id', $periodId)
                    ->where('as_week_id', $weekId)
                    ->where('as_b_id', $businessId)
                    ->whereIn('as_is_sal_processed', [120, 1])
                    ->count();

                $week = PayrollPeriodWeek::find($weekId);
                if ($week) {
                    $week->ppw_processed_count = $remainingProcessed;
                    $week->ppw_is_processed = ($remainingProcessed > 0);
                    if ($remainingProcessed == 0) {
                        $week->ppw_status = PayrollPeriodWeek::STATUS_FROZEN;
                    }
                    $week->save();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Salaries reverted successfully.',
                'data' => [
                    'reverted_count' => $attendanceUpdated,
                    'deleted_from_processed_table' => $deletedCount,
                    'remaining_processed' => $remainingProcessed ?? 0,
                    'payroll_id' => $periodId,
                    'week_id' => $weekId,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Revert weekly salaries failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to revert salaries: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Finalize Weekly Payroll (Verification to Completion)
     */
    public function finalizeWeeklyPayroll(Request $request)
    {
        try {
            $request->validate([
                'payroll_id' => 'required|integer|exists:payroll_periods,pp_id',
                'week_id' => 'required|integer|exists:payroll_period_weeks,ppw_id',
            ]);

            $periodId = $request->payroll_id;
            $weekId = $request->week_id;
            $businessId = auth()->user()->emp_b_id;

            $week = PayrollPeriodWeek::where('ppw_id', $weekId)
                ->where('ppw_pp_id', $periodId)
                ->where('ppw_b_id', $businessId)
                ->firstOrFail();

            DB::beginTransaction();

            // Check if all employees are processed
            $totalEmployees = AttendanceSummary::where('as_pp_id', $periodId)
                ->where('as_week_id', $weekId)
                ->where('as_b_id', $businessId)
                ->count();

            $processedEmployees = ProcessedEmployeeSalary::where('ps_payroll_id', $periodId)
                ->where('ps_week_id', $weekId)
                ->where('ps_b_id', $businessId)
                ->count();

            if ($processedEmployees < $totalEmployees) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot finalize. Only {$processedEmployees} out of {$totalEmployees} employees have been processed.",
                    'data' => [
                        'total_employees' => $totalEmployees,
                        'processed_employees' => $processedEmployees,
                        'pending_employees' => $totalEmployees - $processedEmployees,
                    ],
                ], 422);
            }

            $previousStatus = $week->ppw_status;

            // Update week status to FINALIZED/COMPLETED
            $week->ppw_status = self::STATUS_FINALIZED; // or STATUS_COMPLETED
            $week->ppw_is_frozen = true;
            $week->ppw_finalized_at = now();
            $week->ppw_finalized_by = auth()->id();
            $week->save();

            // Check if all weeks in this period are finalized
            $allWeeks = PayrollPeriodWeek::where('ppw_pp_id', $periodId)
                ->where('ppw_b_id', $businessId)
                ->get();

            $allProcessed = $allWeeks->every(function ($w) {
                return $w->ppw_status == PayrollPeriodWeek::STATUS_PROCESSED;
            });

            $periodStatusUpdated = false;
            $periodNewStatus = null;

            if ($allProcessed) {
                // Update main payroll period
                $payrollPeriod = PayrollPeriod::find($periodId);
                $periodPreviousStatus = $payrollPeriod->pp_status_code;
                $payrollPeriod->pp_status_code = PayrollPeriod::STATUS_COMPLETED;
                $payrollPeriod->pp_is_finalized = 1;
                $payrollPeriod->pp_finalized_at = now();
                $payrollPeriod->pp_finalized_by = auth()->id();
                $payrollPeriod->save();

                $periodNewStatus = PayrollPeriod::STATUS_COMPLETED;
                $periodStatusUpdated = true;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Weekly payroll finalized successfully.',
                'data' => [
                    'week_id' => $weekId,
                    'payroll_id' => $periodId,
                    'week_number' => $week->ppw_week_number,
                    'previous_status' => $previousStatus,
                    'week_status' => self::STATUS_FINALIZED,
                    'next_step' => 'STEP6',  // ✅ Go to success page
                    'total_employees' => $totalEmployees,
                    'processed_employees' => $processedEmployees,
                    'all_weeks_processed' => $allProcessed,
                    'period_status_updated' => $periodStatusUpdated,
                    'period_new_status' => $periodNewStatus,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Finalize weekly payroll failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payroll_id' => $request->payroll_id ?? null,
                'week_id' => $request->week_id ?? null,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to finalize weekly payroll: '.$e->getMessage(),
            ], 500);
        }
    }

    public function releaseWeeklyHold(Request $request)
    {
        try {
            $hold = SalaryHold::findOrFail($request->hold_id);
            $hold->sh_status = 'released';
            $hold->sh_released_at = now();
            $hold->sh_released_by = Auth::user()->emp_id;
            $hold->save();

            return response()->json(['success' => true, 'message' => 'Salary hold released successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get Weekly Hold Details
     */
    public function getWeeklyHoldDetails($periodId, $weekId)
    {
        try {
            $holds = SalaryHold::with(['employee:emp_id,emp_code,emp_fname,emp_lname,emp_d_id,emp_dg_id'])
                ->where('sh_pp_id', $periodId)
                ->where('sh_week_id', $weekId)
                ->where('sh_status', 'held')
                ->get()
                ->map(function ($hold) {
                    return [
                        'sh_id' => $hold->sh_id,
                        'employee_id' => $hold->sh_emp_id,
                        'employee_name' => $hold->employee->emp_full_name ?? 'N/A',
                        'employee_code' => $hold->employee->emp_code ?? 'N/A',
                        'department' => $hold->employee->fh_department->d_name ?? 'N/A',
                        'reason' => $hold->sh_reason,
                        'held_at' => optional($hold->sh_held_at)->format('d-m-Y H:i'),
                    ];
                });

            return response()->json(['success' => true, 'count' => $holds->count(), 'holds' => $holds]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get Weekly Pending Requests
     */
    private function getWeeklyPendingRequests($periodId, $weekId, $businessId)
    {
        $week = PayrollPeriodWeek::find($weekId);
        if (! $week) {
            return [
                'total_pending' => 0,
                'missed_punches' => 0,
                'leave_requests' => 0,
                'overtime_requests' => 0,
                'payroll_id' => $periodId,
                'missed_punch_url' => route('mis-punch.index'),
            ];
        }

        $startDate = Carbon::parse($week->ppw_start_date);
        $endDate = Carbon::parse($week->ppw_end_date);

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
            $leavePending = \App\Models\LeaveRequest::where('lvr_b_id', $businessId)
                ->where('lvr_stage_completed', 0)
                ->whereNull('lvr_p_id')
                ->whereNotIn('lvr_status', [170])
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('lvr_start_date', [$startDate, $endDate])
                        ->orWhereBetween('lvr_end_date', [$startDate, $endDate]);
                })
                ->count();
        }

        // Overtime Requests
        $overTimePending = DB::table('ot_approval_status')
            ->where('ot_b_id', $businessId)
            ->where('ot_stage_completed', 0)
            ->whereBetween('ot_date', [$startDate, $endDate])
            ->count();

        return [
            'total_pending' => $missPunchPending + $leavePending + $overTimePending,
            'missed_punches' => $missPunchPending,
            'leave_requests' => $leavePending,
            'overtime_requests' => $overTimePending,
            'payroll_id' => $periodId,
            'missed_punch_url' => route('mis-punch.index'),
        ];
    }

    /**
     * Get Quarter ID from Month
     */
    private function getQuarterIdFromMonth($monthName, $financialYearId)
    {
        $quarterMapping = [
            'April' => 1,
            'May' => 1,
            'June' => 1,
            'July' => 2,
            'August' => 2,
            'September' => 2,
            'October' => 3,
            'November' => 3,
            'December' => 3,
            'January' => 4,
            'February' => 4,
            'March' => 4,
        ];

        $quarterNumber = $quarterMapping[$monthName] ?? 1;

        $quarter = MasterTable::where('m_group', 'PAYROLL_QUARTER')
            ->where('m_name', 'LIKE', "%Q{$quarterNumber}%")
            ->first();

        if ($quarter) {
            return $quarter->m_id;
        }

        $defaultQuarter = MasterTable::firstOrCreate(
            ['m_group' => 'PAYROLL_QUARTER', 'm_name' => "Q{$quarterNumber}"],
            ['m_name' => "Q{$quarterNumber}", 'm_group' => 'PAYROLL_QUARTER', 'm_status' => 1]
        );

        return $defaultQuarter->m_id;
    }

    /**
     * Get Weeks for Month
     */
    public function getWeeksForMonth(Request $request)
    {
        $month = $request->get('month');
        $year = $request->get('year', date('Y'));
        $financialYearId = (int) $request->get('financial_year');
        $monthId = $request->get('month_id');
        $excludePayrollId = (int) $request->get('exclude_payroll_id', 0);
        $businessId = (int) auth()->user()->emp_b_id;

        if (! $month) {
            return response()->json([]);
        }

        $monthNumber = date('n', strtotime($month.' 1, '.$year));
        $startDate = Carbon::create($year, $monthNumber, 1);
        $endDate = Carbon::create($year, $monthNumber, $startDate->daysInMonth);

        $weeks = [];
        $weekNumber = 1;
        $currentWeekStart = $startDate->copy();

        while ($currentWeekStart <= $endDate) {
            $weekEnd = $currentWeekStart->copy()->addDays(6);
            if ($weekEnd > $endDate) {
                $weekEnd = $endDate->copy();
            }

            $weeks[] = [
                'value' => $weekNumber,
                'label' => "Week {$weekNumber} ({$currentWeekStart->format('d M')} - {$weekEnd->format('d M Y')})",
                'week_number' => $weekNumber,
                'start_date' => $currentWeekStart->format('Y-m-d'),
                'end_date' => $weekEnd->format('Y-m-d'),
                'year' => $year,
            ];

            $currentWeekStart = $weekEnd->copy()->addDay();
            $weekNumber++;
        }

        $existingWeekNumbers = [];
        if ($financialYearId > 0 && ! empty($monthId)) {
            $existingWeekNumbers = $this->getExistingWeeklyNumbersForMonth(
                $businessId,
                $financialYearId,
                (int) $monthId,
                $excludePayrollId > 0 ? $excludePayrollId : null
            );
        }

        return response()->json([
            'weeks' => $weeks,
            'existing_week_numbers' => $existingWeekNumbers,
        ]);
    }

    private function getExistingWeeklyNumbersForMonth(int $businessId, int $financialYearId, ?int $monthId, ?int $excludePayrollId = null): array
    {
        if (empty($monthId) || $financialYearId <= 0) {
            return [];
        }

        $query = PayrollPeriodWeek::query()
            ->where('ppw_b_id', $businessId)
            ->where('ppw_fy_id', $financialYearId)
            ->where('ppw_month_id', $monthId)
            ->whereHas('payrollPeriod', function ($q) use ($businessId, $financialYearId, $monthId) {
                $q->where('pp_b_id', $businessId)
                    ->where('pp_fy_id', $financialYearId)
                    ->where('pp_month_id', $monthId)
                    ->where('pp_type_id', 441);
            });

        if (! empty($excludePayrollId)) {
            $query->where('ppw_pp_id', '!=', $excludePayrollId);
        }

        return $query->pluck('ppw_week_number')
            ->map(fn ($num) => (int) $num)
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }

    public function getWeeklyCycleEditData(int $payrollId)
    {
        $businessId = (int) auth()->user()->emp_b_id;
        $period = PayrollPeriod::with(['weeks', 'month', 'financialYear'])
            ->where('pp_id', $payrollId)
            ->where('pp_b_id', $businessId)
            ->where('pp_type_id', 441)
            ->firstOrFail();

        [$canEditWeeks, $reason] = $this->canEditCycleWeeks($period);
        if (! $canEditWeeks) {
            return response()->json([
                'success' => false,
                'message' => $reason ?: 'This cycle cannot be edited in current stage.',
            ], 422);
        }

        $weeks = $period->weeks
            ->sortBy('ppw_week_number')
            ->values()
            ->map(function ($w) {
                return [
                    'id' => (int) $w->ppw_id,
                    'week_number' => (int) $w->ppw_week_number,
                    'week_name' => (string) $w->ppw_week_name,
                    'start' => ! empty($w->ppw_start_date) ? Carbon::parse($w->ppw_start_date)->format('Y-m-d') : '',
                    'end' => ! empty($w->ppw_end_date) ? Carbon::parse($w->ppw_end_date)->format('Y-m-d') : '',
                    'status' => (string) ($w->ppw_status ?? 'open'),
                ];
            });

        $existingOthers = $this->getExistingWeeklyNumbersForMonth(
            $businessId,
            (int) $period->pp_fy_id,
            $period->pp_month_id ? (int) $period->pp_month_id : null,
            (int) $period->pp_id
        );

        return response()->json([
            'success' => true,
            'data' => [
                'payroll_id' => (int) $period->pp_id,
                'payroll_name' => (string) $period->pp_name,
                'description' => (string) ($period->pp_description ?? ''),
                'financial_year' => (int) $period->pp_fy_id,
                'month_id' => (int) ($period->pp_month_id ?? 0),
                'month_name' => (string) optional($period->month)->m_name,
                'weeks' => $weeks,
                'existing_week_numbers_other_cycles' => $existingOthers,
            ],
        ]);
    }

    public function updateWeeklyCycleWeeks(Request $request, int $payrollId)
    {
        $businessId = (int) auth()->user()->emp_b_id;
        $period = PayrollPeriod::with('weeks')
            ->where('pp_id', $payrollId)
            ->where('pp_b_id', $businessId)
            ->where('pp_type_id', 441)
            ->firstOrFail();

        [$canEditWeeks, $reason] = $this->canEditCycleWeeks($period);
        if (! $canEditWeeks) {
            return response()->json([
                'success' => false,
                'message' => $reason ?: 'This cycle cannot be edited in current stage.',
            ], 422);
        }

        $weeksData = json_decode((string) $request->input('weekly_weeks_data', '[]'), true);
        if (empty($weeksData) || ! is_array($weeksData)) {
            return response()->json(['success' => false, 'message' => 'Please add at least one week.'], 422);
        }

        $normalizedWeeks = [];
        foreach ($weeksData as $index => $week) {
            $rawWeekValue = $week['week_value'] ?? $week['week_number'] ?? null;
            $weekNumber = is_numeric($rawWeekValue)
                ? (int) $rawWeekValue
                : (int) preg_replace('/[^0-9]/', '', (string) $rawWeekValue);
            if ($weekNumber <= 0) {
                return response()->json(['success' => false, 'message' => 'Invalid week number at row '.($index + 1).'.'], 422);
            }
            if (empty($week['start']) || empty($week['end'])) {
                return response()->json(['success' => false, 'message' => 'Week date range is required at row '.($index + 1).'.'], 422);
            }
            $start = Carbon::parse($week['start'])->startOfDay();
            $end = Carbon::parse($week['end'])->startOfDay();
            if ($end->lt($start)) {
                return response()->json(['success' => false, 'message' => 'Week end date cannot be before start date at row '.($index + 1).'.'], 422);
            }
            $normalizedWeeks[] = [
                'week_number' => $weekNumber,
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
            ];
        }

        $requestedWeekNumbers = collect($normalizedWeeks)->pluck('week_number')->map(fn ($n) => (int) $n)->sort()->values()->toArray();
        $existingPeriodWeekNumbers = $period->weeks->pluck('ppw_week_number')->map(fn ($n) => (int) $n)->sort()->values()->toArray();
        if ($requestedWeekNumbers !== $existingPeriodWeekNumbers) {
            return response()->json([
                'success' => false,
                'message' => 'Week numbers cannot be changed in edit. Keep existing week set: '.implode(', ', $existingPeriodWeekNumbers).'.',
            ], 422);
        }

        $existingInOtherCycles = $this->getExistingWeeklyNumbersForMonth(
            $businessId,
            (int) $period->pp_fy_id,
            $period->pp_month_id ? (int) $period->pp_month_id : null,
            (int) $period->pp_id
        );
        $duplicateWeekNumbers = array_values(array_intersect($requestedWeekNumbers, $existingInOtherCycles));
        if (! empty($duplicateWeekNumbers)) {
            return response()->json([
                'success' => false,
                'message' => 'Week(s) already exist in another cycle for this month: '.implode(', ', $duplicateWeekNumbers).'.',
            ], 422);
        }

        usort($normalizedWeeks, fn ($a, $b) => strcmp($a['start'], $b['start']));

        DB::transaction(function () use ($period, $normalizedWeeks, $request) {
            $byNumber = collect($normalizedWeeks)->keyBy('week_number');
            foreach ($period->weeks as $weekRow) {
                $item = $byNumber->get((int) $weekRow->ppw_week_number);
                if (! $item) {
                    continue;
                }
                $weekRow->ppw_start_date = $item['start'];
                $weekRow->ppw_end_date = $item['end'];
                $weekRow->ppw_description = 'Week '.$weekRow->ppw_week_number.' of '.$period->pp_name;
                $weekRow->save();
            }

            $period->pp_name = $request->input('payroll_name', $period->pp_name);
            $period->pp_description = $request->input('description', $period->pp_description);
            $period->pp_start_date = $normalizedWeeks[0]['start'];
            $period->pp_end_date = $normalizedWeeks[count($normalizedWeeks) - 1]['end'];
            $period->save();
        });

        return response()->json([
            'success' => true,
            'message' => 'Weekly cycle updated successfully.',
        ]);
    }

    private function canEditCycleWeeks(PayrollPeriod $period, $mappedWeeks = null): array
    {
        $lockStatuses = ['frozen', 'processing', 'in_process', 'verification', 'processed', 'finalized', 'finalized_locked', 'payroll_locked'];
        $weeks = $mappedWeeks instanceof \Illuminate\Support\Collection
            ? $mappedWeeks
            : $period->weeks()->get();

        foreach ($weeks as $w) {
            $status = strtolower((string) ($w['status'] ?? $w->ppw_status ?? 'open'));
            if (in_array($status, $lockStatuses, true)) {
                return [false, 'Cannot edit this cycle because one or more weeks are in frozen/processing/verification or later stage.'];
            }
        }

        return [true, null];
    }

    /**
     * Get Months for Financial Year
     */
    public function getMonths($fyId)
    {
        $business_id = auth()->user()->emp_b_id;
        $payrollSettings = PayrollMasterSetting::where('pms_b_id', $business_id)->first();
        $isWeekly = $payrollSettings && $payrollSettings->pms_payroll_cycle == 441;

        if ($isWeekly) {
            return $this->getWeeksByFY($fyId);
        }

        $financialYear = FinancialYear::find($fyId);
        if (! $financialYear) {
            return response()->json([]);
        }

        $months = [];
        $startDate = Carbon::parse($financialYear->fy_start_date);
        $endDate = Carbon::parse($financialYear->fy_end_date);

        while ($startDate <= $endDate) {
            $months[] = [
                'value' => $startDate->format('Y-m'),
                'label' => $startDate->format('F Y'),
                'month_number' => $startDate->month,
                'year' => $startDate->year,
                'start_date' => $startDate->copy()->startOfMonth()->format('Y-m-d'),
                'end_date' => $startDate->copy()->endOfMonth()->format('Y-m-d'),
            ];
            $startDate->addMonth();
        }

        return response()->json($months);
    }

    /**
     * Get Weeks by Financial Year
     */
    public function getWeeksByFY($fyId)
    {
        $financialYear = FinancialYear::find($fyId);
        if (! $financialYear) {
            return response()->json([]);
        }

        $weeks = [];
        $startDate = Carbon::parse($financialYear->fy_start_date);
        $endDate = Carbon::parse($financialYear->fy_end_date);
        $weekNumber = 1;
        $currentWeekStart = $startDate->copy();

        while ($currentWeekStart <= $endDate) {
            $weekEnd = $currentWeekStart->copy()->endOfWeek(Carbon::SATURDAY);
            if ($weekEnd > $endDate) {
                $weekEnd = $endDate->copy();
            }

            $weeks[] = [
                'value' => $weekNumber,
                'label' => "Week {$weekNumber} ({$currentWeekStart->format('d M')} - {$weekEnd->format('d M Y')})",
                'week_number' => $weekNumber,
                'start_date' => $currentWeekStart->format('Y-m-d'),
                'end_date' => $weekEnd->format('Y-m-d'),
                'year' => $currentWeekStart->year,
            ];

            $currentWeekStart = $weekEnd->copy()->addDay();
            $weekNumber++;
        }

        return response()->json($weeks);
    }
}
