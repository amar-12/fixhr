<?php

namespace App\Helpers;

use App\Models\AdhocTransaction;
use App\Models\AdvanceLoanInterestRule;
use App\Models\AdvanceLoanSetting;
use App\Models\AttendanceSummary;
use App\Models\AutomationRule;
use App\Models\EarlyGoingAutomation;
use App\Models\Employee;
use App\Models\LateComingAutomation;
use App\Models\LoanRequest;
use App\Models\PayrollLoanInstallment;
use App\Models\PayrollMasterSetting;
use App\Models\PayrollPeriod;
use App\Models\PolicyHolidayList;
use App\Models\PolicyShiftTiming;
use App\Models\RecurringTransaction;
use App\Models\SalaryAllowance;
use App\Models\SalaryEmployeeEarnings;
use App\Models\SalaryEmployeeSalary;
use App\Models\SalaryMasterHistory;
use App\Models\TadaClaim;
use App\Models\TadaReimburse;
use App\Models\ProcessedEmployeeSalary;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\IncomeTaxSlab;

    
class PayrollLogics
{
    /**
     * Validate loan request against loan settings.
     *
     * @param  \App\Models\LoanRequest  $loan
     * @return array [status => bool, message => string]
     */
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }


      public static function calculateMonthlySalary($employee, $startDate, $endDate, $employeeSalary, $business_id, $payrollId)
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
        // $totalDaysInPeriod = $startDate->diffInDays($endDate) + 1;

        $attendanceSummary = AttendanceSummary::where('as_emp_id', $employee->emp_id)
        ->where('as_b_id', $business_id)
        ->where('as_pp_id', $payrollId)
        ->first();
        $totalDaysInPeriod = $attendanceSummary->as_total_days ?? 0;

        if (!$attendanceSummary) {
            return response()->json(['error' => 'Attendance summary not found for employee ID ' . $employee->emp_id], 404);
        }


        // if ($adhocTransactions->isEmpty()) {
        //     return response()->json(['error' => 'Adhoc transactions not found for employee ID ' . $employee->emp_id], 404);
        // }

        $presentCount = (float)($attendanceSummary->as_total_present ?? 0);
        $leaveCount = $attendanceSummary->as_total_leave ?? 0;
        $holidayCount = $attendanceSummary->as_total_holiday ?? 0;
        $weekOffCount = $attendanceSummary->as_total_weekoff ?? 0;
        $absentCount = $attendanceSummary->as_total_absent ?? 0;
        $overtimeCount = $attendanceSummary->as_total_overtime_hours ?? 0;
        $uplCpunt = $attendanceSummary->as_total_upl_count ?? 0;
        $lateCount = $attendanceSummary->as_days_late ?? 0;
        $earlyExitCount = $attendanceSummary->as_early_exit ?? 0;
        $overtimeHours = $attendanceSummary->as_total_overtime_hours ?? 0;
        $overtimeMinutes = round($overtimeHours * 60) ?? 0;

        // $totalDaysWorked = (float)$presentCount + (float)$weekOffCount + (float)$holidayCount;
        $workedDaysinMonth = $attendanceSummary->as_total_worked_days ?? 0;
        $totalDaysWorked = (float)$workedDaysinMonth;
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

        //ESIC Report Days
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
                    if (!isset($allowancesBreakdown[$typeId][$allowanceId])) {
                        $allowancesBreakdown[$typeId][$allowanceId] = $calculatedAmount;
                        $totalAllowances += $calculatedAmount;
                    }
                }
            } else {
                // ✅ Ensure each allowance type is added only once
                if ($allowanceAmount > 0 && !isset($allowancesBreakdown[$earning->sa_earning_type_id])) {
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

        $policyShiftTime = PolicyShiftTiming::find($employee->emp_shift_type_id);

        if ($policyShiftTime) {
            $startTimeRaw = trim($policyShiftTime->pst_start_time ?? '');
            $endTimeRaw   = trim($policyShiftTime->pst_end_time ?? '');
            $breakMinutes = (int) ($policyShiftTime->pst_break_duration_minutes ?? 0);
            $isBreakPaid  = (int) ($policyShiftTime->pst_is_break_paid ?? 0); // ✅ new flag

            if (empty($startTimeRaw) || empty($endTimeRaw)) {
                dd('Start time or end time missing in database.');
            }

            // ✅ Auto-detect format (time or datetime)
            $parseTime = function ($timeString) {
                $timeString = trim($timeString);

                // If full datetime like 2025-10-31 10:00:00
                if (preg_match('/\d{4}-\d{2}-\d{2}/', $timeString)) {
                    return Carbon::parse($timeString);
                }

                // If only time format like 10:00:00 or 10:00
                try {
                    return Carbon::createFromFormat('H:i:s', $timeString);
                } catch (\Exception $e) {
                    return Carbon::createFromFormat('H:i', $timeString);
                }
            };

            $start = $parseTime($startTimeRaw);
            $end   = $parseTime($endTimeRaw);

            // ✅ Handle overnight shifts (e.g. 22:00 → 06:00 next day)
            if ($end->lessThan($start)) {
                $end->addDay();
            }

            // ✅ Get total duration in minutes (always positive)
            $totalMinutes = abs($end->diffInMinutes($start));

            // ✅ Apply break condition
            if ($isBreakPaid === 0) {
                // unpaid break → subtract from total duration
                $netMinutes = max(0, abs($totalMinutes - $breakMinutes));
            } else {
                // paid break → include break in total duration
                $netMinutes = abs($totalMinutes);
            }

            // ✅ Format output cleanly
            $hours = floor($netMinutes / 60);
            $minutes = $netMinutes % 60;
            $formattedDuration = sprintf('%02d:%02d:00', $hours, $minutes);

            $net_duration_in_hours = round(abs($netMinutes) / 60, 2);
            $net_duration_in_mins = round(abs($netMinutes));
            $policyShiftData = [
                'Business ID' => $business_id,
                'Shift Name' => $policyShiftTime->pst_name,
                'Start Time' => $startTimeRaw,
                'End Time' => $endTimeRaw,
                'Break (minutes)' => $breakMinutes,
                'Is Break Paid' => $isBreakPaid,
                'Total Duration (before break)' => gmdate('H:i:s', abs($totalMinutes) * 60),
                'Net Duration (after break)' => $formattedDuration,
                'net_duration' => $net_duration_in_mins,
            ];
        } else {
            $policyShiftData = ['error' => 'No shift timing found for this business ID'];
            $net_duration_in_mins = 0;
        }
        // =======================
        // FINAL OVERTIME CALCULATION (MONTHLY BASED - MINUTES ACCURATE)
        // =======================

        // Inputs
        $monthlyGross   = $monthlySalary;                 // Base monthly salary
        $workingDays    = $workableDays ?? 0;             // Payroll period days
        $shiftMinutes   = $net_duration_in_mins ?? 0;     // Shift duration from policy
        $overTimeAmount = 0;
        
        // Safety check
        if ($monthlyGross > 0 && $workingDays > 0 && $shiftMinutes > 0) {
            
            // 1️⃣ Calculate Monthly OT Rate (Per Minute)
            $perDaySalary        = $monthlyGross / $workingDays;
            $perMinuteSalary     = $perDaySalary / $shiftMinutes;
            // dd($monthlyGross,$workingDays,$shiftMinutes,$perDaySalary,$perMinuteSalary);

            // 👉 If double OT required (Factory rule), use below instead:
            // $perMinuteSalary = ($perDaySalary / $shiftMinutes) * 2;

            // 2️⃣ Convert OT Hours to Minutes
            // If OT stored as decimal hours
            $totalOtMinutes = round(($attendanceSummary->as_total_overtime_hours ?? 0) * 60);

            // If OT stored separately as hours + minutes, use this instead:
            // $totalOtMinutes = ($otHours * 60) + $otMinutes;

            // 3️⃣ Final OT Amount
            $overTimeAmount = round($totalOtMinutes * $perMinuteSalary, 2);
        }

        // Add OT to allowances
        // if ($overTimeAmount > 0) {
        //     $totalAllowances += $overTimeAmount;
        // }

        // Update Gross Salary
        $grossSalary = $totalAllowances;






        //  //Overtime Calculation
        // $perMinOT = $perDaySalary / $net_duration_in_mins ?? 0;
        // $overTimeAmount = round($perMinOT * $overtimeMinutes, 2);
        // $totalAllowances += $overTimeAmount;
        // $grossSalary = $totalAllowances;


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

        // ✅ Now add overtime here — this will appear as the last entry
        if (!empty($overTimeAmount) && $overTimeAmount > 0) {
            $formattedEarningsBreakdown[] = [
                'type_id'      => 'Overtime',
                'earning_type' => 'Overtime',
                'amount'       => number_format((float) $overTimeAmount, 2, '.', ''),
            ];
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

        $isPfValidationEnabled = $employeeSalary->es_pf_validation_enabled ?? 1; // default ON

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

                // $basicForPF = min($employeeBasicforPF, $pfThreshold);
                $employeeContriRate = $deduction->std_employee_contri_rate_amount;
                $employerContriRate = $deduction->std_employer_contri_rate_amount;


                // 🧩 APPLY PF VALIDATION LOGIC
                if ($isPfValidationEnabled == 1) {
                    // Use pre-calculated employeeBasicforPF
                    $pfBase = $employeeBasicforPF;

                    // Apply threshold (like ₹15000)
                    $pfBase = min($pfBase, $pfThreshold);

                    $deductionAmountEmployee = round(($pfBase * $employeeContriRate) / 100, 2);

                    // Cap PF at ₹1800
                    if ($deductionAmountEmployee > 1800) {
                        $deductionAmountEmployee = 1800;
                    }

                    $deductionAmountEmployer = round(($pfBase * $employerContriRate) / 100, 2);

                } else {


                    // 🚀 Simple PF logic (no validation, no cap)
                    $pfBase = $employeeSalary->es_base_salary;  // Also here
                    $deductionAmountEmployee = round(($pfBase * $employeeContriRate) / 100, 2);
                    $deductionAmountEmployer = round(($pfBase * $employerContriRate) / 100, 2);
                }


                // $basicForPF = $employeeBasicforPF > $pfCap ? $pfCap : $employeeBasicforPF;
                // $basicForPF = $employeeBasicforPF;

                // $deductionAmountEmployee = round($basicForPF * ($employeeContriRate / 100), 2);
                // $deductionAmountEmployer = round($basicForPF * ($employerContriRate / 100), 2);
                // // dd($deductionAmountEmployee, $deductionAmountEmployer);
                $epsAmount = round($pfBase * (8.33 / 100), 2);
                $epfAmount = round($pfBase * (3.67 / 100), 2);
                $edlisAmount = round($pfBase * (0.50 / 100), 2);
                $epfAdminAmount = round($pfBase * (0.50 / 100), 2);
                $edlisAdminAmount = round($pfBase * (0.01 / 100), 2);

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

            // if ($deduction->std_deduction_type_id == 352 && $monthlySalary <= $deduction->std_threshold) {
                //     $deductionAmountEmployee = round($workedDaysSalary * ($deduction->std_employee_contri_rate_amount / 100), 2);
                //     $deductionAmountEmployer = round($workedDaysSalary * ($deduction->std_employer_contri_rate_amount / 100), 2);

                //     $deductionsBreakdown[352] = [
                    //         'Employee Contribution (0.75%)' => $deductionAmountEmployee,
                    //         'Employer Contribution (3.25%)' => $deductionAmountEmployer,
                    //     ];

                    //     $totalEmployeeDeductions += $deductionAmountEmployee;
                    //     $totalEmployerDeductions += $deductionAmountEmployer;
                    // }


                    if ($deduction->std_deduction_type_id == 352) {
                        // check employee table flag (enabled/disabled)
                        if ($employee->fh_employee_salary && $employee->fh_employee_salary->es_esic_validation_enabled == 0) {
                            // Validation enabled -> same as frontend JS
                            $esicBase = min($monthlySalary, $deduction->std_threshold);
                            $deductionAmountEmployee = round($esicBase * ($deduction->std_employee_contri_rate_amount / 100), 2);
                            $deductionAmountEmployer = round($esicBase * ($deduction->std_employer_contri_rate_amount / 100), 2);
                        } else {
                            // Old behavior (without validation) → use worked days salary
                            if ($monthlySalary <= $deduction->std_threshold) {
                                $deductionAmountEmployee = round($workedDaysSalary * ($deduction->std_employee_contri_rate_amount / 100), 2);
                                $deductionAmountEmployer = round($workedDaysSalary * ($deduction->std_employer_contri_rate_amount / 100), 2);
                            }
                        }

                        if ($deductionAmountEmployee > 0 || $deductionAmountEmployer > 0) {
                            $deductionsBreakdown[352] = [
                                'Employee Contribution (0.75%)' => $deductionAmountEmployee,
                                'Employer Contribution (3.25%)' => $deductionAmountEmployer,
                            ];

                            $totalEmployeeDeductions += $deductionAmountEmployee;
                            $totalEmployerDeductions += $deductionAmountEmployer;
                        }
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

        $loanDeduction = self::calculateLoanDeduction($employeeId, $payrollId, $business_id);
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
        if (!is_array($deductionsBreakdown)) {
            $deductionsBreakdown = [];
        }
        $formattedDeductionsBreakdown = [];

        // Process all standard deductions
        foreach ($deductionsBreakdown as $typeId => $deductions) {
            // Fetch the deduction type name from master table
            $typeName = $deductionTypeNames[$typeId] ?? "Unknown Deduction";

            // Skip unknown deductions
            if ($typeName === "Unknown Deduction") {
                continue;
            }

            // Initialize employee & employer arrays if not set
            if (!isset($formattedDeductionsBreakdown[$typeName])) {
                $formattedDeductionsBreakdown[$typeName] = [
                    'type_id' => $typeId,
                    'employee' => [],
                    'employer' => [],
                ];
            }

            foreach ($deductions as $key => $value) {
                // Ensure the value is numeric before formatting
                $formattedValue = is_numeric($value) ? number_format($value, 2) : "0.00";

                if (stripos($key, 'employee') !== false) {
                    $formattedDeductionsBreakdown[$typeName]['employee'][$key] = $formattedValue;
                } elseif (stripos($key, 'employer') !== false) {
                    $formattedDeductionsBreakdown[$typeName]['employer'][$key] = $formattedValue;
                }
            }

            // Handle specific adjustments like EPF breakdown
            if ($typeName === "EPF") {
                $formattedDeductionsBreakdown[$typeName]['employer']['EPF Admin (0.50%)'] = number_format($epfAdminAmount, 2);
                $formattedDeductionsBreakdown[$typeName]['employer']['EDLIS Admin (0.01%)'] = number_format($edlisAdminAmount, 2);
            }

            // Ensure default 0.00 if no values are found
            if (empty($formattedDeductionsBreakdown[$typeName]['employee'])) {
                $formattedDeductionsBreakdown[$typeName]['employee']['Employee Contribution'] = "0.00";
            }
            if (empty($formattedDeductionsBreakdown[$typeName]['employer'])) {
                $formattedDeductionsBreakdown[$typeName]['employer']['Employer Contribution'] = "0.00";
            }
        }

        // ✅ Add Professional Tax (Type ID 353) to Deductions Breakdown
        if ($professionalTax > 0) {
            $formattedDeductionsBreakdown['Professional Tax'] = [
                'type_id' => 353,
                'employee' => ['Employee Contribution' => number_format($professionalTax, 2)],
                'employer' => ['Employer Contribution' => "0.00"], // Assuming no employer contribution for professional tax
            ];
        }

        // 👇 Add Loan Deduction (Type ID 358) to Deductions Breakdown
        // $loanDeduction = $this->calculateLoanDeduction($employeeId, $payrollId, $business_id);
        if ($loanDeduction > 0) {
            $formattedDeductionsBreakdown['Loan Deduction'] = [
                'type_id' => 358,
                'employee' => ['' => number_format($loanDeduction, 2)],
                'employer' => ['' => "0.00"], // Assuming no employer contribution for loan deduction
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
                'employee' => ['Employee Contribution' => number_format($professionalTax, 2)]
            ];
        }

        $adhocEarnings = 0;

        foreach ($adhocTransactions as $transaction) {
            foreach ($transaction->transaction_details as $detail) {
                $componentName = $detail->component->ac_adhoc_component_name ?? 'Adhoc Component';
                // Check for Earning
                if ((float)$detail->earning_amount > 0) {
                    $amount = (float) $detail->earning_amount;

                    $adhocEarnings += $amount;
                    // $grossSalary += $amount;
                    // $totalAllowances += $amount;

                    $formattedEarningsBreakdown[] = [
                        'type_id'      => $detail->atd_id,
                        'earning_type' => $componentName,
                        'amount'       => number_format($amount, 2, '.', ''),
                        'employee' => ['' => number_format($amount, 2)],
                    ];
                }

                // Check for Deduction
                if ((float)$detail->deduction_amount > 0) {
                    $amount = (float) $detail->deduction_amount;

                    $totalEmployeeDeductions += $amount;

                    $formattedDeductionsBreakdown[] = [
                        'type_id'         => $detail->atd_id,
                        'deduction_type'  => $componentName,
                        'amount'          => number_format($amount, 2, '.', ''),
                        'employee' =>     ['' => number_format($amount, 2)],
                    ];
                }
            }
        }



        $currentPayrollMonth = \Carbon\Carbon::parse($startDate)->format('Y-m');

        $recurringTransactions = RecurringTransaction::with(['recurringDetails.component'])
            ->where('rt_emp_id', $employee->emp_id)
            ->where('rt_b_id', $business_id)
            ->where('rt_start_month', '<=', $currentPayrollMonth)
            ->where(function ($q) use ($currentPayrollMonth) {
                $q->whereNull('rt_end_month')
                    ->orWhere('rt_end_month', '>=', $currentPayrollMonth);
            })
            ->get();



        // dd($currentPayrollMonth,$recurringTransactions);

        $recurringEarnings = 0;
        foreach ($recurringTransactions as $transaction) {
            foreach ($transaction->recurringDetails as $detail) {

                $componentName = $detail->component->ac_adhoc_component_name ?? 'Recurring Component';

                /* ------------------------------------------------------
                    |   RECURRING EARNINGS
                    ------------------------------------------------------ */
                if ((float)$detail->rtd_earning_amount > 0) {

                    $amount = (float)$detail->rtd_earning_amount;
                    $recurringEarnings += $amount;

                    // check if already exists
                    $found = false;
                    foreach ($formattedEarningsBreakdown as &$entry) {
                        if ($entry['earning_type'] == $componentName) {

                            $entry['amount'] = number_format(floatval($entry['amount']) + $amount, 2, '.', '');
                            $entry['employee'][''] = number_format(floatval($entry['employee']['']) + $amount, 2);

                            $found = true;
                            break;
                        }
                    }
                    unset($entry);

                    // If NOT found
                    if (!$found) {
                        $formattedEarningsBreakdown[] = [
                            'type_id'      => $detail->rtd_id,
                            'earning_type' => $componentName,
                            'amount'       => number_format($amount, 2, '.', ''),
                            'employee'     => ['' => number_format($amount, 2)],
                        ];
                    }
                }

                /* ------------------------------------------------------
                    |   RECURRING DEDUCTIONS
                    ------------------------------------------------------ */
                if ((float)$detail->rtd_deduction_amount > 0) {

                    $amount = (float)$detail->rtd_deduction_amount;

                    $found = false;
                    foreach ($formattedDeductionsBreakdown as &$entry) {
                        if (isset($entry['deduction_type']) && $entry['deduction_type'] == $componentName) {

                            $entry['amount'] = number_format(floatval($entry['amount']) + $amount, 2, '.', '');
                            $entry['employee'][''] = number_format(floatval($entry['employee']['']) + $amount, 2);

                            $found = true;
                            break;
                        }
                    }
                    unset($entry);

                    // If not found
                    if (!$found) {
                        $formattedDeductionsBreakdown[] = [
                            'type_id'        => $detail->rtd_id,
                            'deduction_type' => $componentName,
                            'amount'         => number_format($amount, 2, '.', ''),
                            'employee'       => ['' => number_format($amount, 2)],
                        ];
                    }

                    $totalEmployeeDeductions += $amount;
                }
            }
        }

        // $tdsDeduction = self::calculateTDS($employee, $monthlySalary);


        // dd($totalEmployeeDeductions);
        $lateComingDeduction = self::calculateLateComingDeduction($employeeId, $payrollId, $business_id, $lateCount, $monthlySalary);
        // dd($lateComingDeduction);
        $totalEmployeeDeductions += $lateComingDeduction;
        if ($lateComingDeduction > 0) {
            $formattedDeductionsBreakdown['Late Deduction'] = [
                'type_id' => 444,
                'employee' => ['' => number_format($lateComingDeduction, 2)]
            ];
        }



        $earlyExitDeduction = self::calculateEarlyGoingDeduction($employeeId, $payrollId, $business_id, $earlyExitCount, $monthlySalary);
        $totalEmployeeDeductions += $earlyExitDeduction;
        if ($earlyExitDeduction > 0) {
            $formattedDeductionsBreakdown['Early Exit Deduction'] = [
                'type_id' => 445,
                'employee' => ['' => number_format($earlyExitDeduction, 2)]
            ];
        }

        // // TADA Inclusion in salary
        // $tadaPayedAmount = 0;

        // $payrollMasterSetting = PayrollMasterSetting::where('pms_b_id', $business_id)
        //     ->where('pms_is_locked', 1)
        //     ->first();

        // if ($payrollMasterSetting && $payrollMasterSetting->pms_include_tada_with_salary == 1) {

        //     $payrollPeriod = PayrollPeriod::findOrFail($payrollId);

        //     // ✅ Step 1: Get all reimburse records
        //     $tadaRecords = TadaReimburse::where('tr_b_id', $business_id)->get();

        //     foreach ($tadaRecords as $tadaRecord) {

        //         // JSON array → [405,404,...]
        //         $claimIds = $tadaRecord->tr_claims_id;
        //         // dd($tadaRecord,$claimIds,$tadaRecord->fh_claims,$employee->emp_id);

        //         if (empty($claimIds)) {
        //             continue;
        //         }

        //         // ✅ Step 2: Get only required claims from DB
        //         $validClaims = TadaClaim::whereIn('tc_id', $claimIds)
        //             ->where('tc_emp_id', $employee->emp_id)
        //             ->where('tc_status', 200)
        //             ->where('tc_next_approver', 1)
        //             ->where('tc_stage_completed', 1)
        //             ->get();

        //         if ($validClaims->isEmpty()) {
        //             continue;
        //         }

        //         // ✅ Step 3: Sum only valid claim amounts
        //         $tadaPayedAmount += $validClaims->sum('tc_payed_amount');
        //     }
        // }

        // // dd($claimIds,$employee->emp_id,$tadaPayedAmount);

        // if ($tadaPayedAmount > 0) {
        //     $formattedEarningsBreakdown[] = [
        //         'type_id'      => 'TADA',
        //         'earning_type' => 'TADA Reimbursement',
        //         'amount'       => number_format($tadaPayedAmount, 2, '.', ''),
        //     ];

        //     $grossSalary += $tadaPayedAmount;
        // }


        $netSalary = $grossSalary - $totalEmployeeDeductions;
        $monthlyCtc = $grossSalary + $totalEmployerDeductions;
        $netSalary = $netSalary + $adhocEarnings + $recurringEarnings + $overTimeAmount;
        

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
            'earlyExit' => $earlyExitCount,
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
            // 'tada_payed_amount' => $tadaPayedAmount,
        ];
    }

    public static function calculateMonthlySalaryUatOld($employee, $startDate, $endDate, $employeeSalary, $business_id, $payrollId)
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
        // $totalDaysInPeriod = $startDate->diffInDays($endDate) + 1;

        $attendanceSummary = AttendanceSummary::where('as_emp_id', $employee->emp_id)
            ->where('as_b_id', $business_id)
            ->where('as_pp_id', $payrollId)
            ->first();
        $totalDaysInPeriod = $attendanceSummary->as_total_days ?? 0;

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
        $earlyExitCount = $attendanceSummary->as_early_exit ?? 0;

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

        $isPfValidationEnabled = $employeeSalary->es_pf_validation_enabled ?? 1; // default ON

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

                // $basicForPF = min($employeeBasicforPF, $pfThreshold);
                $employeeContriRate = $deduction->std_employee_contri_rate_amount;
                $employerContriRate = $deduction->std_employer_contri_rate_amount;

                // 🧩 APPLY PF VALIDATION LOGIC
                if ($isPfValidationEnabled == 1) {

                    // Use pre-calculated employeeBasicforPF
                    $pfBase = $employeeBasicforPF;

                    // Apply threshold (like ₹15000)
                    $pfBase = min($pfBase, $pfThreshold);

                    $deductionAmountEmployee = round(($pfBase * $employeeContriRate) / 100, 2);

                    // Cap PF at ₹1800
                    if ($deductionAmountEmployee > 1800) {
                        $deductionAmountEmployee = 1800;
                    }

                    $deductionAmountEmployer = round(($pfBase * $employerContriRate) / 100, 2);
                } else {

                    // 🚀 Simple PF logic (no validation, no cap)
                    $pfBase = $employeeSalary->es_base_salary;  // Also here
                    $deductionAmountEmployee = round(($pfBase * $employeeContriRate) / 100, 2);
                    $deductionAmountEmployer = round(($pfBase * $employerContriRate) / 100, 2);
                }

                // $basicForPF = $employeeBasicforPF > $pfCap ? $pfCap : $employeeBasicforPF;
                // $basicForPF = $employeeBasicforPF;

                // $deductionAmountEmployee = round($basicForPF * ($employeeContriRate / 100), 2);
                // $deductionAmountEmployer = round($basicForPF * ($employerContriRate / 100), 2);
                // // dd($deductionAmountEmployee, $deductionAmountEmployer);
                $epsAmount = round($pfBase * (8.33 / 100), 2);
                $epfAmount = round($pfBase * (3.67 / 100), 2);
                $edlisAmount = round($pfBase * (0.50 / 100), 2);
                $epfAdminAmount = round($pfBase * (0.50 / 100), 2);
                $edlisAdminAmount = round($pfBase * (0.01 / 100), 2);

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

            // if ($deduction->std_deduction_type_id == 352 && $monthlySalary <= $deduction->std_threshold) {
            //     $deductionAmountEmployee = round($workedDaysSalary * ($deduction->std_employee_contri_rate_amount / 100), 2);
            //     $deductionAmountEmployer = round($workedDaysSalary * ($deduction->std_employer_contri_rate_amount / 100), 2);

            //     $deductionsBreakdown[352] = [
            //         'Employee Contribution (0.75%)' => $deductionAmountEmployee,
            //         'Employer Contribution (3.25%)' => $deductionAmountEmployer,
            //     ];

            //     $totalEmployeeDeductions += $deductionAmountEmployee;
            //     $totalEmployerDeductions += $deductionAmountEmployer;
            // }

            if ($deduction->std_deduction_type_id == 352) {
                // check employee table flag (enabled/disabled)
                if ($employee->fh_employee_salary && $employee->fh_employee_salary->es_esic_validation_enabled == 0) {
                    // Validation enabled -> same as frontend JS
                    $esicBase = min($monthlySalary, $deduction->std_threshold);
                    $deductionAmountEmployee = round($esicBase * ($deduction->std_employee_contri_rate_amount / 100), 2);
                    $deductionAmountEmployer = round($esicBase * ($deduction->std_employer_contri_rate_amount / 100), 2);
                } else {
                    // Old behavior (without validation) → use worked days salary
                    if ($monthlySalary <= $deduction->std_threshold) {
                        $deductionAmountEmployee = round($workedDaysSalary * ($deduction->std_employee_contri_rate_amount / 100), 2);
                        $deductionAmountEmployer = round($workedDaysSalary * ($deduction->std_employer_contri_rate_amount / 100), 2);
                    }
                }

                if ($deductionAmountEmployee > 0 || $deductionAmountEmployer > 0) {
                    $deductionsBreakdown[352] = [
                        'Employee Contribution (0.75%)' => $deductionAmountEmployee,
                        'Employer Contribution (3.25%)' => $deductionAmountEmployer,
                    ];

                    $totalEmployeeDeductions += $deductionAmountEmployee;
                    $totalEmployerDeductions += $deductionAmountEmployer;
                }
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

        $loanDeduction = self::calculateLoanDeduction($employeeId, $payrollId, $business_id);
        $totalEmployeeDeductions += $loanDeduction;

        // Add Loan Deduction to the breakdown with ID 358
        // $deductionsBreakdown[358] = [
        //     'Employee Contribution' => number_format($loanDeduction, 2),
        // ];
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

        $adhocEarnings = 0;

        foreach ($adhocTransactions as $transaction) {
            foreach ($transaction->transaction_details as $detail) {
                $componentName = $detail->component->ac_adhoc_component_name ?? 'Adhoc Component';
                // Check for Earning
                if ((float) $detail->earning_amount > 0) {
                    $amount = (float) $detail->earning_amount;

                    $adhocEarnings += $amount;
                    // $grossSalary += $amount;
                    // $totalAllowances += $amount;

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

        $currentPayrollMonth = \Carbon\Carbon::parse($startDate)->format('Y-m');

        $recurringTransactions = RecurringTransaction::with(['recurringDetails.component'])
            ->where('rt_emp_id', $employee->emp_id)
            ->where('rt_b_id', $business_id)
            ->where('rt_start_month', '<=', $currentPayrollMonth)
            ->where(function ($q) use ($currentPayrollMonth) {
                $q->whereNull('rt_end_month')
                    ->orWhere('rt_end_month', '>=', $currentPayrollMonth);
            })
            ->get();

        $recurringEarnings = 0;
        foreach ($recurringTransactions as $transaction) {
            foreach ($transaction->recurringDetails as $detail) {

                $componentName = $detail->component->ac_adhoc_component_name ?? 'Recurring Component';

                /* ------------------------------------------------------
                    |   RECURRING EARNINGS
                    ------------------------------------------------------ */
                if ((float) $detail->rtd_earning_amount > 0) {

                    $amount = (float) $detail->rtd_earning_amount;
                    $recurringEarnings += $amount;

                    // check if already exists
                    $found = false;
                    foreach ($formattedEarningsBreakdown as &$entry) {
                        if ($entry['earning_type'] == $componentName) {

                            $entry['amount'] = number_format(floatval($entry['amount']) + $amount, 2, '.', '');
                            $entry['employee'][''] = number_format(floatval($entry['employee']['']) + $amount, 2);

                            $found = true;
                            break;
                        }
                    }
                    unset($entry);

                    // If NOT found
                    if (! $found) {
                        $formattedEarningsBreakdown[] = [
                            'type_id' => $detail->rtd_id,
                            'earning_type' => $componentName,
                            'amount' => number_format($amount, 2, '.', ''),
                            'employee' => ['' => number_format($amount, 2)],
                        ];
                    }
                }

                /* ------------------------------------------------------
                    |   RECURRING DEDUCTIONS
                    ------------------------------------------------------ */
                if ((float) $detail->rtd_deduction_amount > 0) {

                    $amount = (float) $detail->rtd_deduction_amount;

                    $found = false;
                    foreach ($formattedDeductionsBreakdown as &$entry) {
                        if (isset($entry['deduction_type']) && $entry['deduction_type'] == $componentName) {

                            $entry['amount'] = number_format(floatval($entry['amount']) + $amount, 2, '.', '');
                            $entry['employee'][''] = number_format(floatval($entry['employee']['']) + $amount, 2);

                            $found = true;
                            break;
                        }
                    }
                    unset($entry);

                    // If not found
                    if (! $found) {
                        $formattedDeductionsBreakdown[] = [
                            'type_id' => $detail->rtd_id,
                            'deduction_type' => $componentName,
                            'amount' => number_format($amount, 2, '.', ''),
                            'employee' => ['' => number_format($amount, 2)],
                        ];
                    }

                    $totalEmployeeDeductions += $amount;
                }
            }
        }

        // dd($totalEmployeeDeductions);
        $lateComingDeduction = self::calculateLateComingDeduction($employeeId, $payrollId, $business_id, $lateCount, $grossSalary);
        // dd($lateComingDeduction);
        $totalEmployeeDeductions += $lateComingDeduction;
        if ($lateComingDeduction > 0) {
            $formattedDeductionsBreakdown['Late Deduction'] = [
                'type_id' => 444,
                'employee' => ['' => number_format($lateComingDeduction, 2)],
            ];
        }

        $earlyExitDeduction = self::calculateEarlyGoingDeduction($employeeId, $payrollId, $business_id, $earlyExitCount, $grossSalary);
        // dd($earlyExitDeduction);
        $totalEmployeeDeductions += $earlyExitDeduction;
        if ($earlyExitDeduction > 0) {
            $formattedDeductionsBreakdown['Early Exit Deduction'] = [
                'type_id' => 445,
                'employee' => ['' => number_format($earlyExitDeduction, 2)],
            ];
        }

        $netSalary = $grossSalary - $totalEmployeeDeductions;
        $monthlyCtc = $grossSalary + $totalEmployerDeductions;
        $netSalary = $netSalary + $adhocEarnings + $recurringEarnings;

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
            'earlyExit' => $earlyExitCount,
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

     public static function calculateDailySalary($employee, $startDate, $endDate, $employeeSalary, $business_id, $payrollId)
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
        // $totalDaysInPeriod = $startDate->diffInDays($endDate) + 1;

        $attendanceSummary = AttendanceSummary::where('as_emp_id', $employee->emp_id)
        ->where('as_b_id', $business_id)
        ->where('as_pp_id', $payrollId)
        ->first();
        $totalDaysInPeriod = $attendanceSummary->as_total_days ?? 0;

        if (!$attendanceSummary) {
            return response()->json(['error' => 'Attendance summary not found for employee ID ' . $employee->emp_id], 404);
        }


        // if ($adhocTransactions->isEmpty()) {
        //     return response()->json(['error' => 'Adhoc transactions not found for employee ID ' . $employee->emp_id], 404);
        // }

        $presentCount = (float)($attendanceSummary->as_total_present ?? 0);
        $leaveCount = $attendanceSummary->as_total_leave ?? 0;
        $holidayCount = $attendanceSummary->as_total_holiday ?? 0;
        $weekOffCount = $attendanceSummary->as_total_weekoff ?? 0;
        $absentCount = $attendanceSummary->as_total_absent ?? 0;
        $overtimeCount = $attendanceSummary->as_total_overtime_hours ?? 0;
        $uplCpunt = $attendanceSummary->as_total_upl_count ?? 0;
        $lateCount = $attendanceSummary->as_days_late ?? 0;
        $overtimeHours = $attendanceSummary->as_total_overtime_hours ?? 0;
        // $overtimeMinutes = round($overtimeHours * 60) ?? 0;
        $time = (string)$overtimeHours;

        if (strpos($time, '.') !== false) {
            list($hours, $minutes) = explode('.', $time);
            $overtimeMinutes = ($hours * 60) + $minutes;
        } else {
            // Pure hours (e.g., "2")
            $overtimeMinutes = $time * 60;
        }
        // dd($overtimeMinutes, $overtimeHours);

        // $totalDaysWorked = (float)$presentCount + (float)$weekOffCount + (float)$holidayCount;
        $workedDaysinMonth = $attendanceSummary->as_total_worked_days ?? 0;
        $totalDaysWorked = (float)$workedDaysinMonth;
        $workableDays = $totalDaysInPeriod;
        $monthlySalary = round($employeeSalary->es_monthly_gross ?? 0, 2);   // Calculation on Monthly Gross
        $perDaySalary = round($employeeSalary->es_perday_salary ?? 0, 2);
        $workedDaysSalary = round($perDaySalary * $totalDaysWorked, 2);
        // dd($workedDaysinMonth,$workableDays, $totalDaysWorked, $monthlySalary, $perDaySalary, $workedDaysSalary);

        $earnings = DB::table('salary_allowances')->where('sa_b_id', $employee->emp_b_id)->get();
        $empDA = SalaryEmployeeEarnings::where('es_e_b_id', $employee->emp_b_id)->where('es_e_cal_type_id', '=', 347)->where('es_e_type_id', '=', 362)->first();
        $deductions = DB::table('statutory_deductions')->where('std_b_id', $employee->emp_b_id)->get();
        $basicEarning = collect($earnings)->firstWhere('sa_earning_type_id', 360);
        $basicPercentage = $basicEarning->sa_threshold_value ?? 0;
        $basicSalary = round($workedDaysSalary * ($basicPercentage / 100), 2);

        $employeeDA = round($empDA->es_e_amount ?? 0, 2);

        //ESIC Report Days
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

                $allowanceAmount = $empEarning ? round($empEarning->es_e_amount * $totalDaysWorked, 2) : 0;
                $empProcessedBasic = $allowanceAmount;
            } elseif ($earning->sa_calculation_type == 346 && $earning->sa_earning_type_id == 362) {
                $empEarning = DB::table('employee_salaries_earnings')
                    ->where('es_e_emp_id', $employee->emp_id)
                    ->where('es_e_cal_type_id', 346)
                    ->where('es_e_type_id', 362)
                    ->first();

                $allowanceAmount = $empEarning ? round($empEarning->es_e_amount * $totalDaysWorked, 2) : 0;
            } elseif ($earning->sa_calculation_type == 346 && $earning->sa_earning_type_id == 363) {
                $empEarning = DB::table('employee_salaries_earnings')
                    ->where('es_e_emp_id', $employee->emp_id)
                    ->where('es_e_cal_type_id', 346)
                    ->where('es_e_type_id', 363)
                    ->first();

                $allowanceAmount = $empEarning ? round($empEarning->es_e_amount * $totalDaysWorked, 2) : 0;
            }

            // House Rent Allowance (Type ID: 361)
            elseif ($earning->sa_calculation_type == 347 && $earning->sa_earning_type_id == 361) {
                $empEarning = DB::table('employee_salaries_earnings')
                    ->where('es_e_emp_id', $employee->emp_id)
                    ->where('es_e_cal_type_id', 347)
                    ->where('es_e_type_id', 361)
                    ->first();

                $allowanceAmount = $empEarning ? round($empEarning->es_e_amount * $totalDaysWorked, 2) : 0;
            }
            // Dearness Allowance (Type ID: 362)
            elseif ($earning->sa_calculation_type == 347 && $earning->sa_earning_type_id == 362) {
                $empEarning = DB::table('employee_salaries_earnings')
                    ->where('es_e_emp_id', $employee->emp_id)
                    ->where('es_e_cal_type_id', 347)
                    ->where('es_e_type_id', 362)
                    ->first();

                if ($empEarning) {
                    $allowanceAmount = round($empEarning->es_e_amount * $totalDaysWorked, 2);
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
                    $calculatedAmount = round($amount * $totalDaysWorked, 2);

                    // ✅ Store allowance separately by `typeId` and `allowanceId`
                    if (!isset($allowancesBreakdown[$typeId][$allowanceId])) {
                        $allowancesBreakdown[$typeId][$allowanceId] = $calculatedAmount;
                        $totalAllowances += $calculatedAmount;
                    }
                }
            } else {
                // ✅ Ensure each allowance type is added only once
                if ($allowanceAmount > 0 && !isset($allowancesBreakdown[$earning->sa_earning_type_id])) {
                    $allowancesBreakdown[$earning->sa_earning_type_id] = $allowanceAmount;
                    $totalAllowances += $allowanceAmount;
                }
            }
        }

        // ✅ Step 1: Calculate Remaining Allowance
        $remainingBalance = 0;

        if ($employeeSalary->es_rem_allowance > 0 && $workableDays > 0) {
            $remainingBalance = round($employeeSalary->es_rem_allowance * $totalDaysWorked, 2);
            $totalAllowances += $remainingBalance;
        }

        $allowancesBreakdown['Remaining'] = $remainingBalance;

        $policyShiftTime = PolicyShiftTiming::find($employee->emp_shift_type_id);

        if ($policyShiftTime) {
            $startTimeRaw = trim($policyShiftTime->pst_start_time ?? '');
            $endTimeRaw   = trim($policyShiftTime->pst_end_time ?? '');
            $breakMinutes = (int) ($policyShiftTime->pst_break_duration_minutes ?? 0);
            $isBreakPaid  = (int) ($policyShiftTime->pst_is_break_paid ?? 0); // ✅ new flag

            if (empty($startTimeRaw) || empty($endTimeRaw)) {
                dd('Start time or end time missing in database.');
            }

            // ✅ Auto-detect format (time or datetime)
            $parseTime = function ($timeString) {
                $timeString = trim($timeString);

                // If full datetime like 2025-10-31 10:00:00
                if (preg_match('/\d{4}-\d{2}-\d{2}/', $timeString)) {
                    return Carbon::parse($timeString);
                }

                // If only time format like 10:00:00 or 10:00
                try {
                    return Carbon::createFromFormat('H:i:s', $timeString);
                } catch (\Exception $e) {
                    return Carbon::createFromFormat('H:i', $timeString);
                }
            };

            $start = $parseTime($startTimeRaw);
            $end   = $parseTime($endTimeRaw);

            // ✅ Handle overnight shifts (e.g. 22:00 → 06:00 next day)
            if ($end->lessThan($start)) {
                $end->addDay();
            }

            // ✅ Get total duration in minutes (always positive)
            $totalMinutes = abs($end->diffInMinutes($start));

            // ✅ Apply break condition
            if ($isBreakPaid === 0) {
                // unpaid break → subtract from total duration
                $netMinutes = max(0, abs($totalMinutes - $breakMinutes));
            } else {
                // paid break → include break in total duration
                $netMinutes = abs($totalMinutes);
            }

            // ✅ Format output cleanly
            $hours = floor($netMinutes / 60);
            $minutes = $netMinutes % 60;
            $formattedDuration = sprintf('%02d:%02d:00', $hours, $minutes);

            $net_duration_in_hours = round(abs($netMinutes) / 60, 2);
            $net_duration_in_mins = round(abs($netMinutes));
            $policyShiftData = [
                'Business ID' => $business_id,
                'Shift Name' => $policyShiftTime->pst_name,
                'Start Time' => $startTimeRaw,
                'End Time' => $endTimeRaw,
                'Break (minutes)' => $breakMinutes,
                'Is Break Paid' => $isBreakPaid,
                'Total Duration (before break)' => gmdate('H:i:s', abs($totalMinutes) * 60),
                'Net Duration (after break)' => $formattedDuration,
                'net_duration' => $net_duration_in_mins,
            ];
        } else {
            $policyShiftData = ['error' => 'No shift timing found for this business ID'];
            $net_duration_in_mins = 0;
        }
        
        
        //Overtime Calculation
        $perMinOT = $perDaySalary / $net_duration_in_mins ?? 0;
        $overTimeAmount = round($perMinOT * $overtimeMinutes, 2);
        // dd($perMinOT, $perDaySalary, $net_duration_in_hours,$overtimeMinutes, $overTimeAmount);
        $totalAllowances += $overTimeAmount;
        // dd($perDaySalary, $net_duration_in_mins,$overTimeAmount,$totalAllowances);
        //Gross Salary
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

        // ✅ Now add overtime here — this will appear as the last entry

        if (!empty($overTimeAmount) && $overTimeAmount > 0) {
            $formattedEarningsBreakdown[] = [
                'type_id'      => 'Overtime',
                'earning_type' => 'Overtime',
                'amount'       => number_format((float) $overTimeAmount, 2, '.', ''),
            ];
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
                // dd($employeeBasicforPF, $pfThreshold,$basicForPF);
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

            // if ($deduction->std_deduction_type_id == 352 && $monthlySalary <= $deduction->std_threshold) {
            //     $deductionAmountEmployee = round($workedDaysSalary * ($deduction->std_employee_contri_rate_amount / 100), 2);
            //     $deductionAmountEmployer = round($workedDaysSalary * ($deduction->std_employer_contri_rate_amount / 100), 2);

            //     $deductionsBreakdown[352] = [
            //         'Employee Contribution (0.75%)' => $deductionAmountEmployee,
            //         'Employer Contribution (3.25%)' => $deductionAmountEmployer,
            //     ];

            //     $totalEmployeeDeductions += $deductionAmountEmployee;
            //     $totalEmployerDeductions += $deductionAmountEmployer;
            // }


            if ($deduction->std_deduction_type_id == 352) {
                // check employee table flag (enabled/disabled)
                if ($employee->fh_employee_salary && $employee->fh_employee_salary->es_esic_validation_enabled == 1) {
                    // Validation enabled -> same as frontend JS
                    $esicBase = min($grossSalary, $deduction->std_threshold);
                    $deductionAmountEmployee = round($esicBase * ($deduction->std_employee_contri_rate_amount / 100), 2);
                    $deductionAmountEmployer = round($esicBase * ($deduction->std_employer_contri_rate_amount / 100), 2);
                } else {
                    // Old behavior (without validation) → use worked days salary
                    if ($monthlySalary <= $deduction->std_threshold) {
                        $deductionAmountEmployee = round($workedDaysSalary * ($deduction->std_employee_contri_rate_amount / 100), 2);
                        $deductionAmountEmployer = round($workedDaysSalary * ($deduction->std_employer_contri_rate_amount / 100), 2);
                    }
                }

                if ($deductionAmountEmployee > 0 || $deductionAmountEmployer > 0) {
                    $deductionsBreakdown[352] = [
                        'Employee Contribution (0.75%)' => $deductionAmountEmployee,
                        'Employer Contribution (3.25%)' => $deductionAmountEmployer,
                    ];

                    $totalEmployeeDeductions += $deductionAmountEmployee;
                    $totalEmployerDeductions += $deductionAmountEmployer;
                }
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

        $loanDeduction = self::calculateLoanDeduction($employeeId, $payrollId, $business_id);
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
        if (!is_array($deductionsBreakdown)) {
            $deductionsBreakdown = [];
        }
        $formattedDeductionsBreakdown = [];

        // Process all standard deductions
        foreach ($deductionsBreakdown as $typeId => $deductions) {
            // Fetch the deduction type name from master table
            $typeName = $deductionTypeNames[$typeId] ?? "Unknown Deduction";

            // Skip unknown deductions
            if ($typeName === "Unknown Deduction") {
                continue;
            }

            // Initialize employee & employer arrays if not set
            if (!isset($formattedDeductionsBreakdown[$typeName])) {
                $formattedDeductionsBreakdown[$typeName] = [
                    'type_id' => $typeId,
                    'employee' => [],
                    'employer' => [],
                ];
            }

            foreach ($deductions as $key => $value) {
                // Ensure the value is numeric before formatting
                $formattedValue = is_numeric($value) ? number_format($value, 2) : "0.00";

                if (stripos($key, 'employee') !== false) {
                    $formattedDeductionsBreakdown[$typeName]['employee'][$key] = $formattedValue;
                } elseif (stripos($key, 'employer') !== false) {
                    $formattedDeductionsBreakdown[$typeName]['employer'][$key] = $formattedValue;
                }
            }

            // Handle specific adjustments like EPF breakdown
            if ($typeName === "EPF") {
                $formattedDeductionsBreakdown[$typeName]['employer']['EPF Admin (0.50%)'] = number_format($epfAdminAmount, 2);
                $formattedDeductionsBreakdown[$typeName]['employer']['EDLIS Admin (0.01%)'] = number_format($edlisAdminAmount, 2);
            }

            // Ensure default 0.00 if no values are found
            if (empty($formattedDeductionsBreakdown[$typeName]['employee'])) {
                $formattedDeductionsBreakdown[$typeName]['employee']['Employee Contribution'] = "0.00";
            }
            if (empty($formattedDeductionsBreakdown[$typeName]['employer'])) {
                $formattedDeductionsBreakdown[$typeName]['employer']['Employer Contribution'] = "0.00";
            }
        }

        // ✅ Add Professional Tax (Type ID 353) to Deductions Breakdown
        if ($professionalTax > 0) {
            $formattedDeductionsBreakdown['Professional Tax'] = [
                'type_id' => 353,
                'employee' => ['Employee Contribution' => number_format($professionalTax, 2)],
                'employer' => ['Employer Contribution' => "0.00"], // Assuming no employer contribution for professional tax
            ];
        }

        // 👇 Add Loan Deduction (Type ID 358) to Deductions Breakdown
        // $loanDeduction = $this->calculateLoanDeduction($employeeId, $payrollId, $business_id);
        if ($loanDeduction > 0) {
            $formattedDeductionsBreakdown['Loan Deduction'] = [
                'type_id' => 358,
                'employee' => ['' => number_format($loanDeduction, 2)],
                'employer' => ['' => "0.00"], // Assuming no employer contribution for loan deduction
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
                'employee' => ['Employee Contribution' => number_format($professionalTax, 2)]
            ];
        }

        $adhocEarnings = 0;

        foreach ($adhocTransactions as $transaction) {
            foreach ($transaction->transaction_details as $detail) {
                $componentName = $detail->component->ac_adhoc_component_name ?? 'Adhoc Component';
                // Check for Earning
                if ((float)$detail->earning_amount > 0) {
                    $amount = (float) $detail->earning_amount;

                    $adhocEarnings += $amount;
                    // $grossSalary += $amount;
                    // $totalAllowances += $amount;

                    $formattedEarningsBreakdown[] = [
                        'type_id'      => $detail->atd_id,
                        'earning_type' => $componentName,
                        'amount'       => number_format($amount, 2, '.', ''),
                        'employee' => ['' => number_format($amount, 2)],
                    ];
                }

                // Check for Deduction
                if ((float)$detail->deduction_amount > 0) {
                    $amount = (float) $detail->deduction_amount;

                    $totalEmployeeDeductions += $amount;

                    $formattedDeductionsBreakdown[] = [
                        'type_id'         => $detail->atd_id,
                        'deduction_type'  => $componentName,
                        'amount'          => number_format($amount, 2, '.', ''),
                        'employee' =>     ['' => number_format($amount, 2)],
                    ];
                }
            }
        }
        // dd($totalEmployeeDeductions);
        $lateComingDeduction = self::calculateLateComingDeduction($employeeId, $payrollId, $business_id, $lateCount, $grossSalary);
        // dd($lateComingDeduction);
        $totalEmployeeDeductions += $lateComingDeduction;
        if ($lateComingDeduction > 0) {
            $formattedDeductionsBreakdown['Late Deduction'] = [
                'type_id' => 444,
                'employee' => ['' => number_format($lateComingDeduction, 2)]
            ];
        }

        $netSalary = $grossSalary - $totalEmployeeDeductions;
        $monthlyCtc = $grossSalary + $totalEmployerDeductions;
        $netSalary = $netSalary + $adhocEarnings;

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
            'earlyExit' => $lateCount,
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


    public static function calculateDailySalaryUatOld($employee, $startDate, $endDate, $employeeSalary, $business_id, $payrollId)
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
        // $totalDaysInPeriod = $startDate->diffInDays($endDate) + 1;

        $attendanceSummary = AttendanceSummary::where('as_emp_id', $employee->emp_id)
            ->where('as_b_id', $business_id)
            ->where('as_pp_id', $payrollId)
            ->first();
        $totalDaysInPeriod = $attendanceSummary->as_total_days ?? 0;

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
        $overtimeHours = $attendanceSummary->as_total_overtime_hours ?? 0;
        $overtimeMinutes = round($overtimeHours * 60) ?? 0;

        // $totalDaysWorked = (float)$presentCount + (float)$weekOffCount + (float)$holidayCount;
        $workedDaysinMonth = $attendanceSummary->as_total_worked_days ?? 0;
        $totalDaysWorked = (float) $workedDaysinMonth;
        $workableDays = $totalDaysInPeriod;
        $monthlySalary = round($employeeSalary->es_monthly_gross ?? 0, 2);   // Calculation on Monthly Gross
        $perDaySalary = round($employeeSalary->es_perday_salary ?? 0, 2);
        $workedDaysSalary = round($perDaySalary * $totalDaysWorked, 2);
        // dd($workedDaysinMonth,$workableDays, $totalDaysWorked, $monthlySalary, $perDaySalary, $workedDaysSalary);

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

                $allowanceAmount = $empEarning ? round($empEarning->es_e_amount * $totalDaysWorked, 2) : 0;
                $empProcessedBasic = $allowanceAmount;
            } elseif ($earning->sa_calculation_type == 346 && $earning->sa_earning_type_id == 362) {
                $empEarning = DB::table('employee_salaries_earnings')
                    ->where('es_e_emp_id', $employee->emp_id)
                    ->where('es_e_cal_type_id', 346)
                    ->where('es_e_type_id', 362)
                    ->first();

                $allowanceAmount = $empEarning ? round($empEarning->es_e_amount * $totalDaysWorked, 2) : 0;
            } elseif ($earning->sa_calculation_type == 346 && $earning->sa_earning_type_id == 363) {
                $empEarning = DB::table('employee_salaries_earnings')
                    ->where('es_e_emp_id', $employee->emp_id)
                    ->where('es_e_cal_type_id', 346)
                    ->where('es_e_type_id', 363)
                    ->first();

                $allowanceAmount = $empEarning ? round($empEarning->es_e_amount * $totalDaysWorked, 2) : 0;
            }

            // House Rent Allowance (Type ID: 361)
            elseif ($earning->sa_calculation_type == 347 && $earning->sa_earning_type_id == 361) {
                $empEarning = DB::table('employee_salaries_earnings')
                    ->where('es_e_emp_id', $employee->emp_id)
                    ->where('es_e_cal_type_id', 347)
                    ->where('es_e_type_id', 361)
                    ->first();

                $allowanceAmount = $empEarning ? round($empEarning->es_e_amount * $totalDaysWorked, 2) : 0;
            }
            // Dearness Allowance (Type ID: 362)
            elseif ($earning->sa_calculation_type == 347 && $earning->sa_earning_type_id == 362) {
                $empEarning = DB::table('employee_salaries_earnings')
                    ->where('es_e_emp_id', $employee->emp_id)
                    ->where('es_e_cal_type_id', 347)
                    ->where('es_e_type_id', 362)
                    ->first();

                if ($empEarning) {
                    $allowanceAmount = round($empEarning->es_e_amount * $totalDaysWorked, 2);
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
                    $calculatedAmount = round($amount * $totalDaysWorked, 2);

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
            $remainingBalance = round($employeeSalary->es_rem_allowance * $totalDaysWorked, 2);
            $totalAllowances += $remainingBalance;
        }

        $allowancesBreakdown['Remaining'] = $remainingBalance;

        $policyShiftTime = PolicyShiftTiming::find($employee->emp_shift_type_id);

        if ($policyShiftTime) {
            $startTimeRaw = trim($policyShiftTime->pst_start_time ?? '');
            $endTimeRaw = trim($policyShiftTime->pst_end_time ?? '');
            $breakMinutes = (int) ($policyShiftTime->pst_break_duration_minutes ?? 0);
            $isBreakPaid = (int) ($policyShiftTime->pst_is_break_paid ?? 0); // ✅ new flag

            if (empty($startTimeRaw) || empty($endTimeRaw)) {
                dd('Start time or end time missing in database.');
            }

            // ✅ Auto-detect format (time or datetime)
            $parseTime = function ($timeString) {
                $timeString = trim($timeString);

                // If full datetime like 2025-10-31 10:00:00
                if (preg_match('/\d{4}-\d{2}-\d{2}/', $timeString)) {
                    return Carbon::parse($timeString);
                }

                // If only time format like 10:00:00 or 10:00
                try {
                    return Carbon::createFromFormat('H:i:s', $timeString);
                } catch (\Exception $e) {
                    return Carbon::createFromFormat('H:i', $timeString);
                }
            };

            $start = $parseTime($startTimeRaw);
            $end = $parseTime($endTimeRaw);

            // ✅ Handle overnight shifts (e.g. 22:00 → 06:00 next day)
            if ($end->lessThan($start)) {
                $end->addDay();
            }

            // ✅ Get total duration in minutes (always positive)
            $totalMinutes = abs($end->diffInMinutes($start));

            // ✅ Apply break condition
            if ($isBreakPaid === 0) {
                // unpaid break → subtract from total duration
                $netMinutes = max(0, abs($totalMinutes - $breakMinutes));
            } else {
                // paid break → include break in total duration
                $netMinutes = abs($totalMinutes);
            }

            // ✅ Format output cleanly
            $hours = floor($netMinutes / 60);
            $minutes = $netMinutes % 60;
            $formattedDuration = sprintf('%02d:%02d:00', $hours, $minutes);

            $net_duration_in_hours = round(abs($netMinutes) / 60, 2);
            $net_duration_in_mins = round(abs($netMinutes));
            $policyShiftData = [
                'Business ID' => $business_id,
                'Shift Name' => $policyShiftTime->pst_name,
                'Start Time' => $startTimeRaw,
                'End Time' => $endTimeRaw,
                'Break (minutes)' => $breakMinutes,
                'Is Break Paid' => $isBreakPaid,
                'Total Duration (before break)' => gmdate('H:i:s', abs($totalMinutes) * 60),
                'Net Duration (after break)' => $formattedDuration,
                'net_duration' => $net_duration_in_mins,
            ];
        } else {
            $policyShiftData = ['error' => 'No shift timing found for this business ID'];
            $net_duration_in_mins = 0;
        }

        // Overtime Calculation
        $perMinOT = $perDaySalary / $net_duration_in_mins ?? 0;
        $overTimeAmount = round($perMinOT * $overtimeMinutes, 2);
        $totalAllowances += $overTimeAmount;
        // Gross Salary
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

        // ✅ Now add overtime here — this will appear as the last entry

        if (! empty($overTimeAmount) && $overTimeAmount > 0) {
            $formattedEarningsBreakdown[] = [
                'type_id' => 'Overtime',
                'earning_type' => 'Overtime',
                'amount' => number_format((float) $overTimeAmount, 2, '.', ''),
            ];
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

            // if ($deduction->std_deduction_type_id == 352 && $monthlySalary <= $deduction->std_threshold) {
            //     $deductionAmountEmployee = round($workedDaysSalary * ($deduction->std_employee_contri_rate_amount / 100), 2);
            //     $deductionAmountEmployer = round($workedDaysSalary * ($deduction->std_employer_contri_rate_amount / 100), 2);

            //     $deductionsBreakdown[352] = [
            //         'Employee Contribution (0.75%)' => $deductionAmountEmployee,
            //         'Employer Contribution (3.25%)' => $deductionAmountEmployer,
            //     ];

            //     $totalEmployeeDeductions += $deductionAmountEmployee;
            //     $totalEmployerDeductions += $deductionAmountEmployer;
            // }

            if ($deduction->std_deduction_type_id == 352) {
                // check employee table flag (enabled/disabled)
                if ($employee->fh_employee_salary && $employee->fh_employee_salary->es_esic_validation_enabled == 0) {
                    // Validation enabled -> same as frontend JS
                    $esicBase = min($monthlySalary, $deduction->std_threshold);
                    $deductionAmountEmployee = round($esicBase * ($deduction->std_employee_contri_rate_amount / 100), 2);
                    $deductionAmountEmployer = round($esicBase * ($deduction->std_employer_contri_rate_amount / 100), 2);
                } else {
                    // Old behavior (without validation) → use worked days salary
                    if ($monthlySalary <= $deduction->std_threshold) {
                        $deductionAmountEmployee = round($workedDaysSalary * ($deduction->std_employee_contri_rate_amount / 100), 2);
                        $deductionAmountEmployer = round($workedDaysSalary * ($deduction->std_employer_contri_rate_amount / 100), 2);
                    }
                }

                if ($deductionAmountEmployee > 0 || $deductionAmountEmployer > 0) {
                    $deductionsBreakdown[352] = [
                        'Employee Contribution (0.75%)' => $deductionAmountEmployee,
                        'Employer Contribution (3.25%)' => $deductionAmountEmployer,
                    ];

                    $totalEmployeeDeductions += $deductionAmountEmployee;
                    $totalEmployerDeductions += $deductionAmountEmployer;
                }
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

        $loanDeduction = self::calculateLoanDeduction($employeeId, $payrollId, $business_id);
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

        $adhocEarnings = 0;

        foreach ($adhocTransactions as $transaction) {
            foreach ($transaction->transaction_details as $detail) {
                $componentName = $detail->component->ac_adhoc_component_name ?? 'Adhoc Component';
                // Check for Earning
                if ((float) $detail->earning_amount > 0) {
                    $amount = (float) $detail->earning_amount;

                    $adhocEarnings += $amount;
                    // $grossSalary += $amount;
                    // $totalAllowances += $amount;

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
        // dd($totalEmployeeDeductions);
        $lateComingDeduction = self::calculateLateComingDeduction($employeeId, $payrollId, $business_id, $lateCount, $grossSalary);
        // dd($lateComingDeduction);
        $totalEmployeeDeductions += $lateComingDeduction;
        if ($lateComingDeduction > 0) {
            $formattedDeductionsBreakdown['Late Deduction'] = [
                'type_id' => 444,
                'employee' => ['' => number_format($lateComingDeduction, 2)],
            ];
        }

         $earlyExitDeduction = self::calculateEarlyGoingDeduction($employeeId, $payrollId, $business_id, $lateCount, $grossSalary);
        // dd($lateComingDeduction);
        $totalEmployeeDeductions += $earlyExitDeduction;
        if ($earlyExitDeduction > 0) {
            $formattedDeductionsBreakdown['Early Exit Deduction'] = [
                'type_id' => 444,
                'employee' => ['' => number_format($earlyExitDeduction, 2)],
            ];
        }

        $netSalary = $grossSalary - $totalEmployeeDeductions;
        $monthlyCtc = $grossSalary + $totalEmployerDeductions;
        $netSalary = $netSalary + $adhocEarnings;

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

    private static function extractTime($value)
    {
        return Carbon::parse($value)->format('H:i:s');
    }

    private static function calculateLateComingDeduction(
        $employeeId,
        $payrollId,
        $business_id,
        $lateCount,
        $grossSalary
    ) {
        if ($lateCount <= 0) {
            return 0;
        }

        $payrollPeriod = PayrollPeriod::where('pp_id', $payrollId)->first();
        if (! $payrollPeriod) {
            return 0;
        }

        $payrollStartDate = Carbon::parse($payrollPeriod->pp_start_date);
        $payrollEndDate = Carbon::parse($payrollPeriod->pp_end_date);

        $month = $payrollStartDate->format('m');
        $year = $payrollStartDate->format('Y');

        /** -------------------------
         * Employee
         * ------------------------- */
        $employee = Employee::find($employeeId);
        if (! $employee) {
            return 0;
        }

        /** -------------------------
         * Holidays
         * ------------------------- */
        $holidayRecords = PolicyHolidayList::where('phl_b_id', $business_id)
            ->where(function ($q) use ($payrollStartDate, $payrollEndDate) {
                $q->whereBetween('phl_start_date', [$payrollStartDate, $payrollEndDate])
                    ->orWhereBetween('phl_end_date', [$payrollStartDate, $payrollEndDate]);
            })
            ->get();

        $holidaysByDate = collect();
        foreach ($holidayRecords as $holiday) {
            $start = Carbon::parse($holiday->phl_start_date);
            $end = Carbon::parse($holiday->phl_end_date);

            while ($start->lte($end)) {
                $holidaysByDate->put($start->toDateString(), $holiday);
                $start->addDay();
            }
        }

        /** -------------------------
         * Week Off Dates
         * ------------------------- */
        $weekOffDates = CentralLogics::getWeekOffDatesReport(
            $employee,
            null,
            null,
            $payrollStartDate,
            $payrollEndDate
        );

        /** -------------------------
         * Attendance Summary (Day-wise)
         * ------------------------- */
        $attendanceSummary = CentralLogics::newGetMonthlyAttendanceDetails(
            $employee,
            $month,
            $year,
            $holidaysByDate,
            $weekOffDates
        );

        if (empty($attendanceSummary)) {
            return 0;
        }

        $hasPenaltyEnabledRule = LateComingAutomation::where('lca_b_id', $business_id)
            ->where('lca_is_penalty_enabled', 1)
            ->exists();

        $orderDirection = $hasPenaltyEnabledRule ? 'asc' : 'desc';

        /**
         * STEP 1: Pick slab rule
         */
        $lateRule = LateComingAutomation::where('lca_b_id', $business_id)
            ->where('lca_no_late', '<=', $lateCount)
            ->orderBy('lca_no_late', $orderDirection)
            ->first();

        if (! $lateRule) {
            return 0;
        }

        /**
         * STEP 2: PENALTY ENABLED + LATE TILL PRESENT
         */
        if (
            (int) $lateRule->lca_is_penalty_enabled === 1 &&
            ! is_null($lateRule->lca_late_till) &&
            (float) $lateRule->lca_penalty_amount > 0
        ) {
            $totalPenalty = 0;
            // dd($attendanceSummary);
            foreach ($attendanceSummary as $day) {

                if (
                    empty($day['date']) ||
                    empty($day['checkInTime']) ||
                    empty($day['lateCount']) ||
                    $day['lateCount'] <= 0
                ) {
                    continue;
                }

                // ✅ Always safe
                $checkInTime = Carbon::parse($day['checkInTime']);

                $lateTillTime = Carbon::parse(
                    $day['date'].' '.Carbon::parse($lateRule->lca_late_till)->format('H:i:s')
                );
                // Attach date to check-in also (important)
                $checkInTime = Carbon::parse(
                    $day['date'].' '.$checkInTime->format('H:i:s')
                );

                if ($checkInTime->gt($lateTillTime)) {
                    $totalPenalty += (float) $lateRule->lca_penalty_amount;
                }
            }
            $totalPenalty += $totalPenalty;

            return round($totalPenalty, 2);
        }

        /**
         * STEP 3: DAYS BASED DEDUCTION
         * (Penalty disabled OR late_till is NULL OR time not crossed)
         */
        if ((float) $lateRule->lca_days_to_deduct <= 0) {
            return 0;
        }

        $attendanceSummary = AttendanceSummary::where('as_emp_id', $employeeId)
            ->where('as_pp_id', $payrollId)
            ->first();

        if (! $attendanceSummary || $attendanceSummary->as_total_days <= 0) {
            return 0;
        }
        $perDaySalary = $grossSalary / $attendanceSummary->as_total_days;

        // dd($attendanceSummary,$perDaySalary,$grossSalary,($lateRule->lca_days_to_deduct * $perDaySalary));

        return round(
            $lateRule->lca_days_to_deduct * $perDaySalary,
            2
        );
    }

    private static function calculateEarlyGoingDeduction(
        $employeeId,
        $payrollId,
        $businessId,
        $earlyExitCount,
        $grossSalary
    ) {
        if ($earlyExitCount <= 0) {
            return 0;
        }

        $payrollPeriod = PayrollPeriod::where('pp_id', $payrollId)->first();
        if (! $payrollPeriod) {
            return 0;
        }

        $payrollStartDate = Carbon::parse($payrollPeriod->pp_start_date);
        $payrollEndDate   = Carbon::parse($payrollPeriod->pp_end_date);

        $month = $payrollStartDate->format('m');
        $year  = $payrollStartDate->format('Y');

        /** -------------------------
         * Employee
         * ------------------------- */
        $employee = Employee::find($employeeId);
        if (! $employee) {
            return 0;
        }

        /** -------------------------
         * Holidays
         * ------------------------- */
        $holidayRecords = PolicyHolidayList::where('phl_b_id', $businessId)
            ->where(function ($q) use ($payrollStartDate, $payrollEndDate) {
                $q->whereBetween('phl_start_date', [$payrollStartDate, $payrollEndDate])
                    ->orWhereBetween('phl_end_date', [$payrollStartDate, $payrollEndDate]);
            })
            ->get();

        $holidaysByDate = collect();
        foreach ($holidayRecords as $holiday) {
            $start = Carbon::parse($holiday->phl_start_date);
            $end   = Carbon::parse($holiday->phl_end_date);

            while ($start->lte($end)) {
                $holidaysByDate->put($start->toDateString(), $holiday);
                $start->addDay();
            }
        }

        /** -------------------------
         * Week Off Dates
         * ------------------------- */
        $weekOffDates = CentralLogics::getWeekOffDatesReport(
            $employee,
            null,
            null,
            $payrollStartDate,
            $payrollEndDate
        );

        /** -------------------------
         * Attendance Summary
         * ------------------------- */
        $attendanceSummary = CentralLogics::newGetMonthlyAttendanceDetails(
            $employee,
            $month,
            $year,
            $holidaysByDate,
            $weekOffDates
        );

        if (empty($attendanceSummary)) {
            return 0;
        }

        /** -------------------------
         * Pick slab rule
         * ------------------------- */
        $hasPenaltyEnabledRule = EarlyGoingAutomation::where('ega_b_id', $businessId)
            ->where('ega_is_penalty_enabled', 1)
            ->exists();

        $orderDirection = $hasPenaltyEnabledRule ? 'desc' : 'asc';

        $earlyRule = EarlyGoingAutomation::where('ega_b_id', $businessId)
            ->where('ega_no_early', '<=', $earlyExitCount)
            ->orderBy('ega_no_early', $orderDirection)
            ->first();

        if (! $earlyRule) {
            return 0;
        }

        /**
         * ======================================================
         * STEP 1: TIME BASED PENALTY
         * ======================================================
         */
        if (
            (int) $earlyRule->ega_is_penalty_enabled === 1 &&
            ! is_null($earlyRule->ega_exit_before) &&
            (float) $earlyRule->ega_penalty_amount > 0
        ) {
            $totalPenalty = 0;

            foreach ($attendanceSummary as $day) {

                if (
                    empty($day['date']) ||
                    empty($day['checkOutTime']) ||
                    empty($day['earlyExitCount']) ||
                    $day['earlyExitCount'] <= 0
                ) {
                    continue;
                }

                $checkOutTime = Carbon::parse(
                    $day['date'] . ' ' . Carbon::parse($day['checkOutTime'])->format('H:i:s')
                );

                $exitBeforeTime = Carbon::parse(
                    $day['date'] . ' ' . Carbon::parse($earlyRule->ega_exit_before)->format('H:i:s')
                );

                if ($checkOutTime->lt($exitBeforeTime)) {
                    $totalPenalty += (float) $earlyRule->ega_penalty_amount;
                }
            }

            return round($totalPenalty, 2);
        }

        /**
         * ======================================================
         * STEP 2: DAYS BASED DEDUCTION
         * ======================================================
         */
        if ((float) $earlyRule->ega_days_to_deduct <= 0) {
            return 0;
        }

        $attendanceRow = AttendanceSummary::where('as_emp_id', $employeeId)
            ->where('as_pp_id', $payrollId)
            ->first();

        if (! $attendanceRow || $attendanceRow->as_total_days <= 0) {
            return 0;
        }

        $perDaySalary = $grossSalary / $attendanceRow->as_total_days;

        return round(
            $earlyRule->ega_days_to_deduct * $perDaySalary,
            2
        );
    }



    private static function calculateEarlyExitDeductionOldUI(
        $employeeId,
        $payrollId,
        $businessId,
        $earlyExitCount,
        $grossSalary
    ) {
        // No early exits
        if ($earlyExitCount <= 0) {
            return 0;
        }

        /** -------------------------
         * Payroll Period
         * ------------------------- */
        $payrollPeriod = PayrollPeriod::where('pp_id', $payrollId)->first();
        if (! $payrollPeriod) {
            return 0;
        }

        $payrollStartDate = Carbon::parse($payrollPeriod->pp_start_date);
        $payrollEndDate   = Carbon::parse($payrollPeriod->pp_end_date);

        $month = $payrollStartDate->format('m');
        $year  = $payrollStartDate->format('Y');

        /** -------------------------
         * Employee
         * ------------------------- */
        $employee = Employee::find($employeeId);
        if (! $employee) {
            return 0;
        }

        /** -------------------------
         * Attendance (Day-wise)
         * ------------------------- */
        $attendanceSummary = CentralLogics::newGetMonthlyAttendanceDetails(
            $employee,
            $month,
            $year,
            collect(),
            []
        );

        if (empty($attendanceSummary)) {
            return 0;
        }

        $hasPenaltyEnabledEarlyRule = EarlyGoingAutomation::where('ega_b_id', $business_id)
        ->where('ega_is_penalty_enabled', 1)
        ->exists();


        $orderDirection = $hasPenaltyEnabledEarlyRule ? 'asc' : 'desc';


        /** -------------------------
         * Pick Early Going Slab
         * ------------------------- */
        $earlyRule = EarlyGoingAutomation::where('ega_b_id', $businessId)
            ->where('ega_no_early', '<=', $earlyExitCount)
            ->orderBy('ega_no_early', $orderDirection)
            ->first();

        if (! $earlyRule) {
            return 0;
        }

        /** ======================================================
         * CASE 1: FIXED PENALTY (TIME BASED)
         * ====================================================== */
        if (
            (int) $earlyRule->ega_is_penalty_enabled === 1 &&
            ! empty($earlyRule->ega_exit_before) &&
            (float) $earlyRule->ega_penalty_amount > 0
        ) {
            $totalPenalty = 0;

            $exitBeforeTime = Carbon::createFromFormat('H:i:s', $earlyRule->ega_exit_before);

            foreach ($attendanceSummary as $day) {

                if (
                    empty($day['checkOutTime']) ||
                    empty($day['earlyExitCount']) ||
                    $day['earlyExitCount'] <= 0
                ) {
                    continue;
                }

                $checkOutTime = Carbon::createFromFormat('H:i', $day['checkOutTime']);

                // 🔥 MAIN CONDITION
                if ($checkOutTime->lt($exitBeforeTime)) {
                    $totalPenalty += (float) $earlyRule->ega_penalty_amount;
                }
            }

            return round($totalPenalty, 2);
        }

        /** ======================================================
         * CASE 2: DAYS BASED DEDUCTION
         * ====================================================== */
        if ((float) $earlyRule->ega_days_to_deduct <= 0) {
            return 0;
        }

        $attendanceRow = AttendanceSummary::where('as_emp_id', $employeeId)
            ->where('as_pp_id', $payrollId)
            ->first();

        if (! $attendanceRow || $attendanceRow->as_total_days <= 0) {
            return 0;
        }

        $perDaySalary = $grossSalary / $attendanceRow->as_total_days;

        return round(
            $earlyRule->ega_days_to_deduct * $perDaySalary,
            2
        );
    }


    private static function calculateLateComingDeductionoldUI($employeeId, $payrollId, $business_id, $lateCount, $grossSalary)
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

    private static function calculateLoanDeduction($employeeId, $payrollId, $business_id)
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

    public static function validateLoanAgainstSettings(LoanRequest $loan)
    {
        $settings = AdvanceLoanSetting::where('als_b_id', $loan->lnr_b_id)->first();

        if (! $settings) {
            return [
                'status' => false,
                'message' => 'No loan settings found for this business.',
            ];
        }

        $employee = $loan->fh_employee;
        $salary = $employee->fh_employee_salary->es_monthly_gross ?? 0;
        $loanAmount = $loan->lnr_requested_amount;
        $months = $loan->lnr_installments;
        // dd($loanAmount);
        // ==================================================
        // 1. ADVANCE LIMIT (Fixed amount OR % of Salary)
        // ==================================================
        if ($settings->als_limit_type === 'FIXED') {
            if ($loanAmount > $settings->als_fixed_limit) {
                return [
                    'status' => false,
                    'message' => "Loan amount exceeds fixed limit ₹{$settings->als_fixed_limit}.",
                ];
            }
        } elseif ($settings->als_limit_type === 'PERCENTAGE') {
            // $maxAllowed = ($salary * $settings->als_percentage_limit) / 100;
            // if ($loanAmount > $maxAllowed) {
            //     return [
            //         'status' => false,
            //         'message' => "Loan amount exceeds allowed percentage ({$settings->als_percentage_limit}%) of salary.",
            //     ];
            // }

            // Example: 50.00 means 50%
            $percentage = (float) $settings->als_percentage_limit;
            $salary = (float) $salary;

            $maxAllowed = ($salary * $percentage) / 100;

            if ((float) $loanAmount > $maxAllowed) {
                return [
                    'status' => false,
                    'message' => "Loan amount exceeds {$percentage}% of salary. Max allowed: ₹".number_format($maxAllowed, 2),
                ];
            }

        }

        // ==================================================
        // 2. EMPLOYMENT RESTRICTIONS
        // ==================================================
        if ($settings->als_permanent_only) {
            if ($employee->emp_type !== 'PERMANENT') {
                return [
                    'status' => false,
                    'message' => 'Only permanent employees are eligible for loan.',
                ];
            }
        }

        if ($settings->als_min_employment > 0) {
            $joiningDate = \Carbon\Carbon::parse($employee->emp_date_of_joining);
            $monthsWorked = $joiningDate->diffInMonths(now());
            if ($monthsWorked < $settings->als_min_employment) {
                return [
                    'status' => false,
                    'message' => "Employee must complete at least {$settings->als_min_employment} months of employment.",
                ];
            }
        }

        // ==================================================
        // 3. AGE CRITERIA
        // ==================================================
        if ($settings->als_enable_age_criteria) {
            $age = \Carbon\Carbon::parse($employee->emp_dob)->age;
            if ($age > $settings->als_max_age) {
                return [
                    'status' => false,
                    'message' => "Employee age exceeds allowed maximum of {$settings->als_max_age} years.",
                ];
            }
        }

        // ==================================================
        // 4. ADDITIONAL SETTINGS
        // ==================================================
        if ($settings->als_max_concurrent > 0) {
            $activeLoans = LoanRequest::where('lnr_emp_id', $employee->emp_id)
                ->where('lnr_b_id', $loan->lnr_b_id)
                ->whereIn('lnr_status', ['approved', 'pending'])
                ->count();

            if ($activeLoans >= $settings->als_max_concurrent) {
                return [
                    'status' => false,
                    'message' => "Employee already has {$activeLoans} active loan(s). Max concurrent allowed: {$settings->als_max_concurrent}.",
                ];
            }
        }

        if ($months < $settings->als_min_repayment) {
            return [
                'status' => false,
                'message' => "Repayment months must be at least {$settings->als_min_repayment}.",
            ];
        }
        if ($months > $settings->als_max_repayment) {
            return [
                'status' => false,
                'message' => "Repayment period exceeds maximum of {$settings->als_max_repayment} months.",
            ];
        }

        // ==================================================
        // 5. INTEREST RULES (Dynamic from rules table)
        // ==================================================
        $interestRate = 0;
        if ($settings->als_apply_interest) {
            $interestRate = self::calculateInterestRate($loan, $settings);
        }

        return [
            'status' => true,
            'message' => 'Loan request is valid.',
            'interest_rate' => $interestRate,
        ];
    }

    /**
     * Calculate applicable interest rate based on dynamic rules.
     *
     * @return float
     */
    private static function calculateInterestRate(LoanRequest $loan, AdvanceLoanSetting $settings)
    {
        $salary = $loan->fh_employee->fh_employee_salary->es_monthly_gross ?? 0;
        $loanAmount = $loan->lnr_requested_amount;
        $months = $loan->lnr_installments;
        $appliedRate = 0;

        $rules = AdvanceLoanInterestRule::where('alir_b_id', $loan->lnr_b_id)
            ->where('alir_als_id', $settings->als_id)
            ->get();

        foreach ($rules as $rule) {
            switch ($rule->alir_rule_type) {

                // ✅ If exceeds installments
                case 'installments_exceed':
                    if ($months > $rule->alir_param1) {
                        $appliedRate = $rule->alir_rule_rate;
                    }
                    break;

                    // ✅ If loan is × salary
                case 'loan_multiple':
                    if ($loanAmount > ($salary * $rule->alir_param1)) {
                        $appliedRate = $rule->alir_rule_rate;
                    }
                    break;

                    // ✅ If exceeds tenure (months)
                case 'tenure_exceed':
                    if ($months > $rule->alir_param1) {
                        $appliedRate = $rule->alir_rule_rate;
                    }
                    break;

                    // ✅ If loan amount > fixed value
                case 'amount_exceed':
                    if ($loanAmount > $rule->alir_param1) {
                        $appliedRate = $rule->alir_rule_rate;
                    }
                    break;

                    // ✅ If loan amount between range
                case 'amount_range':
                    if ($loanAmount >= $rule->alir_param1 && $loanAmount <= $rule->alir_param2) {
                        $appliedRate = $rule->alir_rule_rate;
                    }
                    break;

                    // ✅ Custom condition (tum logic JSON ya expression store kar sakte ho)
                case 'custom':
                    // Example: param1 = minLoan, param2 = maxMonths
                    if ($loanAmount > $rule->alir_param1 && $months < $rule->alir_param2) {
                        $appliedRate = $rule->alir_rule_rate;
                    }
                    break;
            }
        }

        return $appliedRate;
    }


    public static function processSingleWeeklyEmployeeSalary(
        int $empId,
        int $periodId,
        int $weekId,
        int $businessId,
        ?int $authUserId
    ): array {
        $existingProcessed = ProcessedEmployeeSalary::where([
            'ps_emp_id' => $empId,
            'ps_payroll_id' => $periodId,
            'ps_week_id' => $weekId,
            'ps_b_id' => $businessId,
        ])->exists();

        if ($existingProcessed) {
            return ['success' => true, 'skipped' => true, 'message' => 'already_processed'];
        }

        $attendanceSummary = AttendanceSummary::where('as_emp_id', $empId)
            ->where('as_pp_id', $periodId)
            ->where('as_week_id', $weekId)
            ->where('as_b_id', $businessId)
            ->first();

        if (!$attendanceSummary) {
            return ['success' => false, 'message' => "Attendance summary not found for employee ID {$empId}"];
        }

        $employee = Employee::find($empId);
        if (!$employee) {
            return ['success' => false, 'message' => "Employee not found for ID {$empId}"];
        }

        $employeeSalary = SalaryEmployeeSalary::where('es_emp_id', $empId)->first();
        if (!$employeeSalary) {
            return ['success' => false, 'message' => "Salary record not found for: {$employee->emp_full_name}"];
        }

        $workedDays = (float) ($attendanceSummary->as_total_worked_days ?? 0);
        $presentDays = $attendanceSummary->as_total_present ?? 0;
        $halfDays = $attendanceSummary->as_total_half_day ?? 0;
        $lateDays = $attendanceSummary->as_days_late ?? 0;

        $salaryMasterHistory = SalaryMasterHistory::where('sm_emp_id', $empId)
            ->where('sm_emp_b_id', $businessId)
            ->orderByDesc('wef')
            ->orderByDesc('sm_id')
            ->first();

        $perDayWage = (float) ($employeeSalary->es_perday_salary ?? 0);
        if ($perDayWage <= 0) {
            $perDayWage = (float) (($salaryMasterHistory->sm_per_day_gross ?? 0) ?: ($salaryMasterHistory->sm_per_day_wage ?? 0));
        }
        if ($perDayWage <= 0) {
            $monthlyBasicFallback = (float) ($employeeSalary->es_base_salary ?? 0);
            $perDayWage = round($monthlyBasicFallback / 30, 2);
        }

        // Weekly payroll logic: per-day wage × salaried days.
        $proratedSalary = $perDayWage * $workedDays;
        $weeklyBasic = $proratedSalary;

        $hra = ($employeeSalary->es_hra ?? 0) / 4 * ($workedDays / 7);
        $conveyance = ($employeeSalary->es_conveyance ?? 0) / 4 * ($workedDays / 7);
        $medical = ($employeeSalary->es_medical ?? 0) / 4 * ($workedDays / 7);
        $special = ($employeeSalary->es_special ?? 0) / 4 * ($workedDays / 7);

        $totalEarnings = $proratedSalary + $hra + $conveyance + $medical + $special;

        $pf = $totalEarnings * 0.12;
        $esi = $totalEarnings * 0.0075;
        $pt = $totalEarnings > 15000 ? 200 : 0;
        $totalDeductions = $pf + $esi + $pt;
        $netPayable = $totalEarnings - $totalDeductions;

        ProcessedEmployeeSalary::create([
            'ps_b_id' => $businessId,
            'ps_emp_id' => $empId,
            'ps_payroll_id' => $periodId,
            'ps_week_id' => $weekId,
            'ps_basic_salary' => round($weeklyBasic, 2),
            'ps_worked_days_salary' => round($proratedSalary, 2),
            'ps_earnings' => round($totalEarnings, 2),
            'ps_employee_deductions' => round($totalDeductions, 2),
            'ps_monthly_gross' => round($totalEarnings, 2),
            'ps_monthly_net_salary' => round($netPayable, 2),
            'ps_total_days_worked' => $workedDays,
            'ps_total_days_in_month' => 7,
            'ps_present_days' => $presentDays,
            'ps_days_late' => $lateDays,
            'ps_currency' => 'INR',
            'ps_is_payslip' => 1,
            'ps_generated_by' => $authUserId,
        ]);

        $attendanceSummary->as_is_sal_processed = 120;
        $attendanceSummary->save();

        Log::info('Weekly salary processed', [
            'emp_id' => $empId,
            'emp_name' => $employee->emp_full_name,
            'worked_days' => $workedDays,
            'per_day_wage' => $perDayWage,
            'net_payable' => $netPayable,
            'week_id' => $weekId,
        ]);

        return [
            'success' => true,
            'skipped' => false,
            'net_payable' => round($netPayable, 2),
            'employee_name' => $employee->emp_full_name,
        ];
    }


    /**
     * Same progressive slab engine as Form 16 Part B / LedgerController (IncomeTaxSlab master).
     */
    public static function calculateIncomeTaxFromSlabs(int $businessId, int $financialYearId, string $regime, float $annualTaxableIncome): float
    {
        $slabs = IncomeTaxSlab::where('its_b_id', $businessId)
            ->where('its_fy_id', $financialYearId)
            ->where('its_regime', $regime)
            ->where('its_is_active', true)
            ->orderBy('its_income_from')
            ->get(['its_income_from', 'its_income_to', 'its_tax_rate']);

        if ($slabs->isEmpty() || $annualTaxableIncome <= 0) {
            return 0.0;
        }

        $tax = 0.0;
        foreach ($slabs as $slab) {
            $from = (float) $slab->its_income_from;
            $to = $slab->its_income_to !== null ? (float) $slab->its_income_to : INF;
            $rate = (float) $slab->its_tax_rate;

            if ($annualTaxableIncome <= $from) {
                continue;
            }

            $taxablePortion = min($annualTaxableIncome, $to) - $from;
            if ($taxablePortion > 0) {
                $tax += $taxablePortion * ($rate / 100);
            }
        }

        return round($tax, 2);
    }

    /**
     * Prefer "new" regime slabs when configured for the FY, else "old", matching LedgerController::generateForm16.
     */
    public static function resolveIncomeTaxRegime(int $businessId, int $financialYearId): string
    {
        $newExists = IncomeTaxSlab::where('its_b_id', $businessId)
            ->where('its_fy_id', $financialYearId)
            ->where('its_regime', 'new')
            ->where('its_is_active', true)
            ->exists();

        if ($newExists) {
            return 'new';
        }

        $oldExists = IncomeTaxSlab::where('its_b_id', $businessId)
            ->where('its_fy_id', $financialYearId)
            ->where('its_regime', 'old')
            ->where('its_is_active', true)
            ->exists();

        return $oldExists ? 'old' : 'new';
    }

    /**
     * Monthly TDS from annual projection using the same steps as LedgerController::generateForm16 (Part B).
     * Annual gross/PT/PF = monthly × 12; standard deduction ₹50,000; Chapter VI-A PF capped at ₹1,50,000; tax + 4% cess.
     */
    public static function calculateMonthlyTdsFromForm16Projection(
        float $monthlyGross,
        float $monthlyPfEmployee,
        float $monthlyProfessionalTax,
        int $businessId,
        int $financialYearId
    ): float {
        $breakdown = self::getMonthlyTdsProjectionBreakdown(
            $monthlyGross,
            $monthlyPfEmployee,
            $monthlyProfessionalTax,
            $businessId,
            $financialYearId
        );

        return (float) $breakdown['tds'];
    }

    /**
     * Same projection as calculateMonthlyTdsFromForm16Projection, plus regime and marginal slab for UI.
     *
     * @return array{
     *     tds: float,
     *     slabs_configured: bool,
     *     regime: string|null,
     *     regime_label: string,
     *     annual_taxable_income: float,
     *     annual_gross_projected: float,
     *     standard_deduction_annual: float,
     *     chapter_vi_a_pf_annual: float,
     *     marginal_slab: array<string, mixed>|null,
     *     annual_tax_before_cess: float,
     *     cess_annual: float,
     *     total_tax_annual: float
     * }
     */
    public static function getMonthlyTdsProjectionBreakdown(
        float $monthlyGross,
        float $monthlyPfEmployee,
        float $monthlyProfessionalTax,
        int $businessId,
        int $financialYearId
    ): array {
        $annualGross = $monthlyGross * 12;
        $annualPf = $monthlyPfEmployee * 12;
        $annualPt = $monthlyProfessionalTax * 12;

        $hraExempt = 0.0;
        $ltaExempt = 0.0;
        $stdDeduction = 50000.0;
        $salaryAfterExemptions = max($annualGross - $hraExempt - $ltaExempt, 0);
        $totalSection16 = $stdDeduction + $annualPt;
        $incomeFromSalary = max($salaryAfterExemptions - $totalSection16, 0);
        $totalVIA = min($annualPf, 150000.0);
        $taxableIncome = max($incomeFromSalary - $totalVIA, 0);

        $slabsConfigured = self::incomeTaxSlabsConfiguredForYear($businessId, $financialYearId);
        $regime = self::resolveIncomeTaxRegime($businessId, $financialYearId);
        $tax = self::calculateIncomeTaxFromSlabs($businessId, $financialYearId, $regime, $taxableIncome);
        $cess = round($tax * 0.04, 2);
        $totalTax = round($tax + $cess, 2);
        $monthlyTds = round($totalTax / 12, 2);

        $marginalSlab = null;
        if ($slabsConfigured && $taxableIncome > 0) {
            $marginalSlab = self::resolveMarginalIncomeTaxSlab($businessId, $financialYearId, $regime, $taxableIncome);
        }

        $regimeLabel = 'Income tax slabs not configured for this FY';
        if ($slabsConfigured) {
            $regimeLabel = $regime === 'new' ? 'New tax regime' : 'Old tax regime';
        }

        return [
            'tds' => $monthlyTds,
            'slabs_configured' => $slabsConfigured,
            'regime' => $slabsConfigured ? $regime : null,
            'regime_label' => $regimeLabel,
            'annual_taxable_income' => round($taxableIncome, 2),
            'annual_gross_projected' => round($annualGross, 2),
            'standard_deduction_annual' => $stdDeduction,
            'chapter_vi_a_pf_annual' => round(min($annualPf, 150000.0), 2),
            'marginal_slab' => $marginalSlab,
            'annual_tax_before_cess' => round($tax, 2),
            'cess_annual' => $cess,
            'total_tax_annual' => $totalTax,
        ];
    }

    /**
     * Highest slab bracket that applies to any rupee of taxable income (marginal bracket).
     */
    private static function resolveMarginalIncomeTaxSlab(
        int $businessId,
        int $financialYearId,
        string $regime,
        float $annualTaxableIncome
    ): ?array {
        if ($annualTaxableIncome <= 0) {
            return null;
        }

        $slabs = IncomeTaxSlab::where('its_b_id', $businessId)
            ->where('its_fy_id', $financialYearId)
            ->where('its_regime', $regime)
            ->where('its_is_active', true)
            ->orderBy('its_income_from')
            ->get(['its_income_from', 'its_income_to', 'its_tax_rate']);

        if ($slabs->isEmpty()) {
            return null;
        }

        $marginal = null;
        foreach ($slabs as $slab) {
            $from = (float) $slab->its_income_from;
            if ($annualTaxableIncome > $from) {
                $marginal = $slab;
            }
        }

        if ($marginal === null) {
            return null;
        }

        $from = (float) $marginal->its_income_from;
        $to = $marginal->its_income_to !== null ? (float) $marginal->its_income_to : null;
        $rate = (float) $marginal->its_tax_rate;
        $rateLabel = abs($rate - round($rate, 2)) < 0.0001
            ? (string) (int) round($rate)
            : rtrim(rtrim(number_format($rate, 2, '.', ''), '0'), '.');

        if ($to === null) {
            $label = sprintf(
                '₹%s and above — marginal rate %s%%',
                number_format($from, 0, '.', ','),
                $rateLabel
            );
        } else {
            $label = sprintf(
                '₹%s – ₹%s — marginal rate %s%%',
                number_format($from, 0, '.', ','),
                number_format($to, 0, '.', ','),
                $rateLabel
            );
        }

        return [
            'income_from' => $from,
            'income_to' => $to,
            'rate_percent' => $rate,
            'label' => $label,
        ];
    }

    private static function incomeTaxSlabsConfiguredForYear(int $businessId, int $financialYearId): bool
    {
        return IncomeTaxSlab::where('its_b_id', $businessId)
            ->where('its_fy_id', $financialYearId)
            ->where('its_is_active', true)
            ->exists();
    }

    private static function resolveFinancialYearIdForTds(int $businessId, ?int $payrollId): ?int
    {
        if ($payrollId) {
            $fy = PayrollPeriod::where('pp_id', $payrollId)->value('pp_fy_id');
            if ($fy) {
                return (int) $fy;
            }
        }

        $fy = DB::table('financial_years')
            ->where('fy_b_id', $businessId)
            ->where('fy_is_current', 1)
            ->value('fy_id');

        if ($fy) {
            return (int) $fy;
        }

        $fy = DB::table('financial_years')
            ->where('fy_b_id', $businessId)
            ->orderByDesc('fy_id')
            ->value('fy_id');

        return $fy ? (int) $fy : null;
    }

    /**
     * Legacy employee-declared TDS (% or fixed per master) when slab config is absent.
     */
    private static function calculateTdsFromEmployeeDeclaredRates($employee, float $monthlySalaryBase): float
    {
        $tdsValue = $employee->emp_tds_value;
        $tdsType = $employee->emp_tds_type;

        if ($tdsType == 573) {
            return round(($monthlySalaryBase * $tdsValue) / 100, 2);
        }
        if ($tdsType == 574) {
            return round((float) $tdsValue, 2);
        }

        return 0.0;
    }

    /**
     * @param  float  $declaredMonthlyGrossFromMaster  Salary master monthly gross (fallback base for legacy TDS)
     * @param  float|null  $periodGross  Pay-period gross for Form 16 projection (incl. recurring where passed)
     * @param  float|null  $periodPfEmployee  Employee PF for the same period
     * @param  float|null  $periodProfessionalTax  PT for the same period
     */
    public static function calculateTDS(
        $employee,
        float $declaredMonthlyGrossFromMaster,
        int $businessId,
        ?int $payrollId = null,
        ?float $periodGross = null,
        ?float $periodPfEmployee = null,
        ?float $periodProfessionalTax = null
    ): float {
        if ((int) ($employee->emp_is_tds_applicable ?? 0) !== 120) {
            return 0.0;
        }

        $fyId = self::resolveFinancialYearIdForTds($businessId, $payrollId);

        $mg = $periodGross ?? $declaredMonthlyGrossFromMaster;
        $mpf = $periodPfEmployee ?? 0.0;
        $mpt = $periodProfessionalTax ?? 0.0;

        if ($fyId && self::incomeTaxSlabsConfiguredForYear($businessId, $fyId)) {
            return self::calculateMonthlyTdsFromForm16Projection($mg, $mpf, $mpt, $businessId, $fyId);
        }

        return self::calculateTdsFromEmployeeDeclaredRates($employee, $declaredMonthlyGrossFromMaster);
    }



    
}
