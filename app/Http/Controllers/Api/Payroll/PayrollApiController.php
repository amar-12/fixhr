<?php

namespace App\Http\Controllers\Api\Payroll;

use App\Helpers\PayrollLogics;
use App\Http\Controllers\Controller;
use App\Http\Resources\Payroll\ProcessedEmployeeResource;
use App\Models\AdhocTransaction;
use App\Models\Employee;
use App\Models\MasterTable;
use App\Models\PayrollPeriod;
use App\Models\ProcessedEmployeeSalary;
use App\Models\SalaryAllowance;
use App\Models\SalaryEmployeeDeductions;
use App\Models\SalaryEmployeeEarnings;
use App\Models\SalaryEmployeeSalary;
use App\Models\SalaryEmployerDeductions;
use App\Models\SalaryMasterHistory;
use Barryvdh\DomPDF\Facade\Pdf;
use ChandraHemant\HtkcUtils\PaginatedResource;
use ChandraHemant\HtkcUtils\ReturnHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use NumberToWords\NumberToWords;


class PayrollApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function generatePayslipPdf($payslipId)
    {

        $processedSalary = ProcessedEmployeeSalary::findOrFail($payslipId);
        $payrollPeriod = PayrollPeriod::findOrFail($processedSalary->ps_payroll_id);

        $employee = Employee::with('fh_department', 'fh_designation', 'fh_business', 'fh_branch')->findOrFail($processedSalary->ps_emp_id);

        $logoPath = $employee->b_logo ?? null;

        $numberToWords = new NumberToWords();
        $numberTransformer = $numberToWords->getNumberTransformer('en');
        $net_salary = number_format((float) str_replace(',', '', $processedSalary->ps_monthly_net_salary), 2, '.', '');
        $parts = explode(".", $net_salary);
        $integerPart = (int) $parts[0];
        $decimalPart = isset($parts[1]) ? (int) $parts[1] : 0;

        $net_salary_words = ucfirst($numberTransformer->toWords($integerPart)) . " rupees";
        if ($decimalPart > 0) {
            $net_salary_words .= " and " . $numberTransformer->toWords($decimalPart) . " paise";
        }
        $net_salary_words .= " only";

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


    public function index(Request $request)
    {
        $user = Auth::user();

        $data = ProcessedEmployeeSalary::with('payrollPeriod', 'payrollPeriod.financialYear')
            ->where('ps_b_id', $user->emp_b_id)
            ->where('ps_emp_id', $user->emp_id)
            ->get(); // Fetch the data

        if ($data->isNotEmpty()) {
            return ReturnHelper::jsonApiReturn(ProcessedEmployeeResource::collection($data));
        } else {
            return response()->json(['result' => [], 'status' => false]);
        }
    }


    public function getAllPayrollDetails($payroll_id)
    {
        $processedSalaries = ProcessedEmployeeSalary::with([
            'employee.fh_department',
            'employee.fh_designation',
            'employee.fh_employee_salary'
        ])
            ->where('ps_payroll_id', $payroll_id)
            ->get();

        if ($processedSalaries->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No processed salaries found for this payroll period'
            ], 404);
        }

        $data = $processedSalaries->map(function ($salary) use ($payroll_id) {
            $employee = $salary->employee;

            // 🔥 CALL YOUR MAIN CALCULATION METHOD
            $calculated = PayrollLogics::calculateMonthlySalary(
                $employee,
                $salary->ps_from_date,
                $salary->ps_to_date,
                $employee->fh_employee_salary,
                $employee->emp_b_id,
                $payroll_id
            );

            // Check if the result is a JsonResponse (error case)
            if ($calculated instanceof JsonResponse) {
                // Handle the error - you might want to skip this employee or return error
                return null;
            }

            // Now safely access the array values
            return [
                'ps_id' => $salary->ps_id,

                'employee' => [
                    'emp_id' => $employee->emp_id,
                    'emp_code' => $employee->emp_code,
                    'name' => $employee->emp_full_name,
                    'department' => $employee->fh_department?->d_name,
                    'designation' => $employee->fh_designation?->dg_name,
                    'pf_enabled' => $employee->emp_is_pf_enabled,
                    'esic_enabled' => $employee->emp_esic_limit,


                ],

                'attendance_summary' => [
                    'total_days' => $calculated['total_days_in_month'] ?? 0,
                    'working_days' => $calculated['total_month_working_days'] ?? 0,
                    'present_days' => $calculated['present_days'] ?? 0,
                    'week_offs' => $calculated['week_off_count'] ?? 0,
                    'workable_days' => $calculated['workable_days'] ?? 0,
                    'late_days' => $calculated['lateCount'] ?? 0,
                ],

                'salary_summary' => [
                    'monthly_salary' => $calculated['monthly_salary'] ?? 0,
                    'per_day_salary' => $calculated['per_day_salary'] ?? 0,
                    'worked_days_salary' => $calculated['worked_days_salary'] ?? 0,
                    'basic_salary' => $calculated['basic_salary'] ?? 0,
                    'total_earnings' => $calculated['total_earnings'] ?? 0,
                    'total_employee_deductions' => $calculated['total_employee_deductions'] ?? 0,
                    'total_employer_deductions' => $calculated['total_employer_deductions'] ?? 0,
                    'gross_salary' => $calculated['gross_salary'] ?? 0,
                    'net_salary' => $calculated['net_salary'] ?? 0,
                    'monthly_ctc' => $calculated['monthly_ctc'] ?? 0,
                ],

                // 🔥 FULL STRUCTURED BREAKDOWN
                'earnings_breakdown' => $calculated['earnings_breakdown'] ?? [],
                'deductions_breakdown' => $calculated['deductions_breakdown'] ?? [],
            ];
        })->filter(); // This removes any null values (failed calculations)

        return response()->json([
            'status' => true,
            'payroll_id' => $payroll_id,
            'total_employees' => $data->count(),
            'data' => $data->values() // Reset array keys after filtering
        ]);
    }

    public function getPayrollPeriods()
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $businessId = $user->emp_b_id;

        $periods = PayrollPeriod::with([
            'financialYear',
            'month',
            'quarter_master',
            'approvedBy',
            'verifiedBy',
            'lockedBy',
            'processedSalaries',
            'activeSalaryHolds'
        ])
            ->where('pp_b_id', $businessId)
            ->orderBy('pp_start_date', 'desc')
            ->get();

        $data = $periods->map(function ($period) {

            $statusDetails = $period->getStatusDetails();

            return [
                'payroll_id' => $period->pp_id,
                'name' => $period->pp_name,
                'sequence_no' => $period->pp_seq_no,

                'period' => [
                    'start_date' => $period->pp_start_date,
                    'end_date' => $period->pp_end_date,
                    'payment_date' => $period->pp_payment_date,
                    'payslip_date' => $period->pp_payslip_date,
                ],

                'financial_year' => $period->financialYear?->fy_name,
                'month' => $period->month?->m_name,
                'quarter' => $period->quarter_master?->m_name,

                'status' => [
                    'code' => $period->pp_status_code,
                    'label' => $statusDetails['label'],
                    'step' => $statusDetails['step'],
                    'description' => $statusDetails['description'],
                    'color' => $statusDetails['color'],
                    'icon' => $statusDetails['icon'],
                    'allowed_actions' => $statusDetails['actions'],
                ],

                'permissions' => [
                    'can_submit_for_approval' => $period->canSubmitForApproval(),
                    'can_approve' => $period->canApprove(),
                    'can_freeze_attendance' => $period->canFreezeAttendance(),
                    'can_process_salary' => $period->canProcessSalary(),
                    'can_verify' => $period->canVerify(),
                    'can_lock' => $period->canLock(),
                ],

                'summary' => [
                    'total_employees_processed' => $period->processedSalaries->count(),
                    'active_salary_holds' => $period->activeSalaryHolds->count(),
                ],

                'audit_info' => [
                    'approved_by' => $period->approvedBy?->emp_full_name,
                    'approved_at' => $period->pp_approved_at,
                    'verified_by' => $period->verifiedBy?->emp_full_name,
                    'verified_at' => $period->pp_verified_at,
                    'locked_by' => $period->lockedBy?->emp_full_name,
                    'locked_at' => $period->pp_locked_at,
                ],

                'flags' => [
                    'is_active' => $period->pp_is_active,
                    'is_processed' => $period->pp_is_processed,
                    'is_finalized' => $period->pp_is_finalized,
                    'is_freezed' => $period->pp_is_freezed,
                    'is_locked' => $period->isLocked(),
                ],
            ];
        });

        return response()->json([
            'status' => true,
            'business_id' => $businessId,
            'total_periods' => $data->count(),
            'data' => $data
        ]);
    }


      public function getEmployeeSalaryDetails($empId): JsonResponse
    {
        try {
            $user = Auth::user();
            $businessId = $user->business_id ?? null;

            // 1. Get Employee Main Salary with relationships
            $mainSalary = SalaryEmployeeSalary::where('es_emp_id', $empId)
                ->when($businessId, function($query) use ($businessId) {
                    return $query->where('es_b_id', $businessId);
                })
                ->with([
                    'fh_employee',
                    'fh_policy_salary',
                    'fh_business'
                ])
                ->first();

            if (!$mainSalary) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee salary details not found'
                ], 404);
            }

            $businessId = $mainSalary->es_b_id;

            // 2. Get all allowances for this business from SalaryAllowance table
            $allAllowances = SalaryAllowance::where('sa_b_id', $businessId)
                ->where('sa_is_active', true)
                ->with([
                    'fh_pf_condition',
                    'fh_allowance_calculation_type',
                    'fh_earning_type',
                    'fh_payroll_heading',
                    'fh_business'
                ])
                ->get();

            // 3. Get employee earnings from SalaryEmployeeEarnings table
            $employeeEarnings = SalaryEmployeeEarnings::where('es_e_emp_id', $empId)
                ->where('es_e_b_id', $businessId)
                ->with('fh_salary_earning_type')
                ->get()
                ->keyBy('es_sa_id'); // Key by salary_allowance_id for easy matching

            // 4. Match allowances with earnings and prepare response
            $allowancesWithAmounts = [];
            $totalEarnings = 0;

            foreach ($allAllowances as $allowance) {
                $earning = $employeeEarnings->get($allowance->sa_id);
                $amount = $earning ? $earning->es_e_amount : 0;
                $totalEarnings += $amount;

                // Get calculation type details
                $calculationType = null;
                if ($allowance->fh_allowance_calculation_type) {
                    $calculationType = [
                        'id' => $allowance->fh_allowance_calculation_type->m_id,
                        'name' => $allowance->fh_allowance_calculation_type->m_name,
                        'type' => $allowance->fh_allowance_calculation_type->m_type,
                        'group' => $allowance->fh_allowance_calculation_type->m_group
                    ];
                }

                // Get earning type details
                $earningType = null;
                if ($allowance->fh_earning_type) {
                    $earningType = [
                        'id' => $allowance->fh_earning_type->m_id,
                        'name' => $allowance->fh_earning_type->m_name,
                        'group' => $allowance->fh_earning_type->m_group
                    ];
                }

                // Get payroll heading details
                $payrollHeading = null;
                if ($allowance->fh_payroll_heading) {
                    $payrollHeading = [
                        'id' => $allowance->fh_payroll_heading->m_id,
                        'name' => $allowance->fh_payroll_heading->m_name,
                        'group' => $allowance->fh_payroll_heading->m_group
                    ];
                }

                // Get PF condition details
                $pfCondition = null;
                if ($allowance->fh_pf_condition) {
                    $pfCondition = [
                        'id' => $allowance->fh_pf_condition->m_id,
                        'name' => $allowance->fh_pf_condition->m_name,
                        'value' => $allowance->fh_pf_condition->mt_value,
                        'group' => $allowance->fh_pf_condition->m_group
                    ];
                }

                $allowancesWithAmounts[] = [
                    'allowance_id' => $allowance->sa_id,
                    'business_id' => $allowance->sa_b_id,
                    'title' => $allowance->sa_title,
                    'description' => $allowance->sa_description,
                    'name_in_payslip' => $allowance->sa_name_in_payslip,
                    'calculation_type_id' => $allowance->sa_calculation_type,
                    'earning_type_id' => $allowance->sa_earning_type_id,
                    'payroll_heading_id' => $allowance->sa_payroll_heading_id,
                    'percentage_of' => $allowance->sa_percentage_of,
                    'sequence' => $allowance->sa_sequence_valu,
                    'threshold_value' => $allowance->sa_threshold_value,

                    // Amount from earnings table
                    'amount' => $amount,

                    // Earning details if exists
                    'earning_details' => $earning ? [
                        'earning_id' => $earning->es_e_id,
                        'calculation_type_id' => $earning->es_e_cal_type_id,
                        'created_at' => $earning->created_at?->format('Y-m-d H:i:s'),
                        'updated_at' => $earning->updated_at?->format('Y-m-d H:i:s')
                    ] : null,

                    // PF Settings
                    'consider_for_pf' => $allowance->sa_consider_for_pf,
                    'pf_condition_id' => $allowance->sa_consider_for_pf_condition,
                    'pf_condition' => $pfCondition,

                    // ESIC Settings
                    'consider_for_esic' => $allowance->sa_consider_for_esic,

                    // Other Settings
                    'prorata_basis' => $allowance->sa_calculate_on_prorata_basis,
                    'is_taxable' => $allowance->sa_is_taxable,
                    'show_in_payslip' => $allowance->sa_show_in_payslip,
                    'is_active' => $allowance->sa_is_active,

                    // Related Data
                    'calculation_type' => $calculationType,
                    'earning_type' => $earningType,
                    'payroll_heading' => $payrollHeading,

                    'created_at' => $allowance->sa_created_at?->format('Y-m-d H:i:s'),
                    'updated_at' => $allowance->sa_updated_at?->format('Y-m-d H:i:s')
                ];
            }

            // 5. Get employee deductions
            $employeeDeductions = SalaryEmployeeDeductions::where('es_d_emp_id', $empId)
                ->where('es_d_b_id', $businessId)
                ->get()
                ->map(function($deduction) {
                    $deductionType = MasterTable::where('m_id', $deduction->es_d_type_id)->first();
                    $calculationType = MasterTable::where('m_id', $deduction->es_d_cal_type_id)
                        ->where('m_group', 'ALLOWANCE_CAL_TYPE')
                        ->first();

                    return [
                        'deduction_id' => $deduction->es_d_id,
                        'type_id' => $deduction->es_d_type_id,
                        'calculation_type_id' => $deduction->es_d_cal_type_id,
                        'amount' => $deduction->es_d_amount,
                        'deduction_name' => $deductionType?->m_name,
                        'calculation_type' => $calculationType?->m_name
                    ];
                });

            // 6. Get employer deductions
            $employerDeductions = SalaryEmployerDeductions::where('employer_sd_emp_id', $empId)
                ->where('employer_sd_b_id', $businessId)
                ->get()
                ->map(function($deduction) {
                    $deductionType = MasterTable::where('m_id', $deduction->employer_sd_type_id)->first();
                    $calculationType = MasterTable::where('m_id', $deduction->employer_sd_cal_type_id)
                        ->where('m_group', 'ALLOWANCE_CAL_TYPE')
                        ->first();

                    return [
                        'deduction_id' => $deduction->employer_sd_id,
                        'type_id' => $deduction->employer_sd_type_id,
                        'calculation_type_id' => $deduction->employer_sd_cal_type_id,
                        'amount' => $deduction->employer_sd_amount,
                        'deduction_name' => $deductionType?->m_name,
                        'calculation_type' => $calculationType?->m_name
                    ];
                });

            // 7. Get salary history
            $salaryHistory = SalaryMasterHistory::where('sm_emp_id', $empId)
                ->where('sm_emp_b_id', $businessId)
                ->orderBy('wef', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($history) {
                    return [
                        'history_id' => $history->sm_id,
                        'wef' => $history->wef ? date('Y-m-d', strtotime($history->wef)) : null,
                        'monthly_ctc' => $history->sm_monthly_ctc,
                        'annual_ctc' => $history->sm_annual_ctc,
                        'basic' => $history->sm_basic,
                        'hra' => $history->sm_hra,
                        'dearness_allowance' => $history->sm_dear_allow,
                        'conveyance_allowance' => $history->sm_conv_allow,
                        'medical_allowance' => $history->sm_med_allow,
                        'education_allowance' => $history->sm_edu_allow,
                        'gross_pay' => $history->sm_gross_pay,
                        'net_pay' => $history->sm_net_pay,
                        'remark' => $history->sm_remark,
                        'created_at' => $history->created_at?->format('Y-m-d H:i:s')
                    ];
                });

            // 8. Prepare final response
            $response = [
                'success' => true,
                'message' => 'Employee salary details retrieved successfully',
                'data' => [
                    // Employee Information
                    'employee' => [
                        'id' => $mainSalary->fh_employee?->emp_id,
                        'name' => $mainSalary->fh_employee?->emp_name,
                        'email' => $mainSalary->fh_employee?->emp_email,
                        'department' => $mainSalary->fh_employee?->emp_department,
                        'designation' => $mainSalary->fh_employee?->emp_designation,
                        'joining_date' => $mainSalary->fh_employee?->emp_joining_date?->format('Y-m-d')
                    ],

                    // Business Information
                    'business' => $mainSalary->fh_business ? [
                        'id' => $mainSalary->fh_business->b_id,
                        'name' => $mainSalary->fh_business->b_name,
                        'code' => $mainSalary->fh_business->b_code
                    ] : null,

                    // Main Salary Details
                    'salary_details' => [
                        'salary_id' => $mainSalary->es_id,
                        'policy_id' => $mainSalary->es_ps_id,
                        'annual_ctc' => $mainSalary->es_annual_ctc,
                        'monthly_ctc' => $mainSalary->es_monthly_ctc,
                        'base_salary' => $mainSalary->es_base_salary,
                        'hra_allowance' => $mainSalary->es_hra_allowance,
                        'conveyance_allowance' => $mainSalary->es_conveyance_allowance,
                        'medical_allowance' => $mainSalary->es_medical_allowance,
                        'special_allowance' => $mainSalary->es_special_allowance,
                        'rem_allowance' => $mainSalary->es_rem_allowance,
                        'performance_bonus' => $mainSalary->es_performance_bonus,
                        'incentives' => $mainSalary->es_incentives,
                        'retention_bonus' => $mainSalary->es_retention_bonus,
                        'employee_pf' => $mainSalary->es_employee_pf_contribution,
                        'employer_pf' => $mainSalary->es_employer_pf_contribution,
                        'employee_esic' => $mainSalary->es_employee_esic_contribution,
                        'employer_esic' => $mainSalary->es_employer_esic_contribution,
                        'professional_tax' => $mainSalary->es_professional_tax,
                        'tds' => $mainSalary->es_tds,
                        'deductions' => $mainSalary->es_deductions,
                        'bonuses' => $mainSalary->es_bonuses,
                        'monthly_gross' => $mainSalary->es_monthly_gross,
                        'annual_gross' => $mainSalary->es_annual_gross,
                        'monthly_net' => $mainSalary->es_monthly_net_salary,
                        'per_day_salary' => $mainSalary->es_perday_salary,
                        'currency' => $mainSalary->es_currency,
                        'effective_date' => $mainSalary->es_salary_effective_date?->format('Y-m-d'),
                        'revision_date' => $mainSalary->es_sales_ary_revision_date?->format('Y-m-d'),
                        'salary_grade' => $mainSalary->es_salary_grade,
                        'pay_frequency' => $mainSalary->es_pay_frequency,
                        'is_current' => $mainSalary->es_is_current,
                        'esic_validation' => $mainSalary->es_esic_validation_enabled,
                        'pf_validation' => $mainSalary->es_pf_validation_enabled,
                        'created_at' => $mainSalary->created_at?->format('Y-m-d H:i:s'),
                        'updated_at' => $mainSalary->updated_at?->format('Y-m-d H:i:s')
                    ],

                    // All Allowances with their amounts from earnings table
                    'allowances' => $allowancesWithAmounts,

                    // Deductions
                    'employee_deductions' => $employeeDeductions,
                    'employer_deductions' => $employerDeductions,

                    // Salary History
                    'salary_history' => $salaryHistory,

                    // Summary
                    'summary' => [
                        'total_allowances' => count($allowancesWithAmounts),
                        'total_earnings' => $totalEarnings,
                        'total_employee_deductions' => $employeeDeductions->sum('amount'),
                        'total_employer_deductions' => $employerDeductions->sum('amount'),
                        'monthly_gross' => $mainSalary->es_monthly_gross,
                        'monthly_net' => $mainSalary->es_monthly_net_salary,
                        'annual_ctc' => $mainSalary->es_annual_ctc,
                        'total_revisions' => $salaryHistory->count(),
                        'last_revision' => $salaryHistory->first()['wef'] ?? null
                    ]
                ]
            ];

            return response()->json($response, 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving salary details',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    /**
     * Get allowance-wise summary
     */
    public function getAllowanceWiseSummary($empId): JsonResponse
    {
        try {
            $user = Auth::user();
            $businessId = $user->business_id ?? null;

            // Get all allowances for business
            $allowances = SalaryAllowance::where('sa_b_id', $businessId)
                ->where('sa_is_active', true)
                ->get();

            // Get employee earnings
            $earnings = SalaryEmployeeEarnings::where('es_e_emp_id', $empId)
                ->where('es_e_b_id', $businessId)
                ->get()
                ->keyBy('es_sa_id');

            $allowanceWiseData = [];

            foreach ($allowances as $allowance) {
                $earning = $earnings->get($allowance->sa_id);

                $allowanceWiseData[] = [
                    'allowance_name' => $allowance->sa_title,
                    'payslip_name' => $allowance->sa_name_in_payslip,
                    'allowance_id' => $allowance->sa_id,
                    'earning_type_id' => $allowance->sa_earning_type_id,
                    'amount' => $earning ? $earning->es_e_amount : 0,
                    'calculation_type' => $allowance->sa_calculation_type,
                    'is_taxable' => $allowance->sa_is_taxable,
                    'consider_for_pf' => $allowance->sa_consider_for_pf,
                    'consider_for_esic' => $allowance->sa_consider_for_esic,
                    'has_earning' => $earning ? true : false
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $allowanceWiseData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving allowance summary',
                'error' => $e->getMessage()
            ], 500);
        }
    }


       public function apiGetAdhocPayments(Request $request)
    {
        try {
            $user = Auth::user();
            $business_id = $user->emp_b_id;

            // Build query with relationships
            $query = AdhocTransaction::with([
                'employee' => function ($q) {
                    $q->select('emp_id', 'emp_code', 'emp_full_name', 'emp_fname', 'emp_d_id', 'emp_dg_id');
                },
                'employee.fh_department' => function ($q) {
                    $q->select('d_id', 'd_name');
                },
                'employee.fh_designation' => function ($q) {
                    $q->select('dg_id', 'dg_name');
                },
                'payrollPeriod' => function ($q) {
                    $q->select('pp_id', 'pp_name', 'pp_start_date', 'pp_end_date');
                },
                'transaction_details' => function ($q) {
                    $q->with('component.payrollHeading');
                }
            ])
                ->where('at_b_id', $business_id)
                ->where('at_e_amount', '>', 0); // Only fetch records with earnings

            // Apply filters if provided
            if ($request->has('employee_id') && $request->employee_id) {
                $query->where('at_emp_id', $request->employee_id);
            }

            if ($request->has('payroll_period_id') && $request->payroll_period_id) {
                $query->where('at_pp_id', $request->payroll_period_id);
            }

            if ($request->has('department_id') && $request->department_id) {
                $query->where('at_emp_d_id', $request->department_id);
            }

            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereHas('payrollPeriod', function ($q) use ($request) {
                    $q->whereBetween('pp_start_date', [$request->start_date, $request->end_date]);
                });
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $adhocPayments = $query->orderBy('created_at', 'desc')->paginate($perPage);

            // Format the response data
            $formattedData = $adhocPayments->map(function ($transaction) {
                // Group details by heading
                $groupedDetails = [];
                foreach ($transaction->transaction_details as $detail) {
                    $headingName = $detail->component->payrollHeading->first()->m_name ?? 'Uncategorized';
                    $headingId = $detail->component->ac_adhoc_heading_id;

                    if (!isset($groupedDetails[$headingId])) {
                        $groupedDetails[$headingId] = [
                            'heading_id' => $headingId,
                            'heading_name' => $headingName,
                            'components' => []
                        ];
                    }

                    $groupedDetails[$headingId]['components'][] = [
                        'component_id' => $detail->component_id,
                        'component_name' => $detail->component->ac_adhoc_component_name ?? null,
                        'earning_amount' => $detail->earning_amount,
                        'deduction_amount' => $detail->deduction_amount,
                        'remarks' => $detail->remarks,
                    ];
                }

                return [
                    'id' => $transaction->at_id,
                    'employee' => [
                        'id' => $transaction->employee->emp_id ?? null,
                        'code' => $transaction->employee->emp_code ?? null,
                        'name' => $transaction->employee->emp_full_name ?? null,
                        'department' => $transaction->employee->fh_department->d_name ?? null,
                        'designation' => $transaction->employee->fh_designation->dg_name ?? null,
                    ],
                    'payroll_period' => [
                        'id' => $transaction->payrollPeriod->pp_id ?? null,
                        'name' => $transaction->payrollPeriod->pp_name ?? null,
                        'start_date' => $transaction->payrollPeriod->pp_start_date ?? null,
                        'end_date' => $transaction->payrollPeriod->pp_end_date ?? null,
                    ],
                    'total_earnings' => $transaction->at_e_amount,
                    'total_deductions' => $transaction->at_d_amount,
                    'net_amount' => $transaction->at_e_amount - $transaction->at_d_amount,
                    'details_by_heading' => array_values($groupedDetails),
                    'created_at' => $transaction->created_at,
                    'updated_at' => $transaction->updated_at,
                ];
            });

            return response()->json([
                'status' => true,
                'message' => 'Adhoc payments retrieved successfully',
                'data' => $formattedData,
                'pagination' => [
                    'current_page' => $adhocPayments->currentPage(),
                    'last_page' => $adhocPayments->lastPage(),
                    'per_page' => $adhocPayments->perPage(),
                    'total' => $adhocPayments->total(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve adhoc payments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

       /**
     * API: Get specific adhoc payment details by ID
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetAdhocPaymentDetails($id)
    {
        try {
            $user = Auth::user();
            $business_id = $user->emp_b_id;

            $transaction = AdhocTransaction::with([
                'employee' => function ($q) {
                    $q->with(['fh_department', 'fh_designation']);
                },
                'payrollPeriod',
                'transaction_details.component.payrollHeading'
            ])
                ->where('at_b_id', $business_id)
                ->where('at_id', $id)
                ->first();

            if (!$transaction) {
                return response()->json([
                    'status' => false,
                    'message' => 'Adhoc payment not found'
                ], 404);
            }

            // Group details by heading
            $groupedDetails = [];
            foreach ($transaction->transaction_details as $detail) {
                $headingName = $detail->component->payrollHeading->first()->m_name ?? 'Uncategorized';
                $headingId = $detail->component->ac_adhoc_heading_id;

                if (!isset($groupedDetails[$headingId])) {
                    $groupedDetails[$headingId] = [
                        'heading_id' => $headingId,
                        'heading_name' => $headingName,
                        'components' => []
                    ];
                }

                $groupedDetails[$headingId]['components'][] = [
                    'component_id' => $detail->component_id,
                    'component_name' => $detail->component->ac_adhoc_component_name ?? null,
                    'earning_amount' => $detail->earning_amount,
                    'deduction_amount' => $detail->deduction_amount,
                    'remarks' => $detail->remarks,
                    'created_at' => $detail->created_at,
                    'updated_at' => $detail->updated_at,
                ];
            }

            $formattedData = [
                'id' => $transaction->at_id,
                'employee' => [
                    'id' => $transaction->employee->emp_id ?? null,
                    'code' => $transaction->employee->emp_code ?? null,
                    'name' => $transaction->employee->emp_full_name ?? null,
                    'first_name' => $transaction->employee->emp_fname ?? null,
                    'last_name' => $transaction->employee->emp_lname ?? null,
                    'email' => $transaction->employee->emp_email ?? null,
                    'department' => [
                        'id' => $transaction->employee->fh_department->d_id ?? null,
                        'name' => $transaction->employee->fh_department->d_name ?? null,
                    ],
                    'designation' => [
                        'id' => $transaction->employee->fh_designation->dg_id ?? null,
                        'name' => $transaction->employee->fh_designation->dg_name ?? null,
                    ],
                ],
                'payroll_period' => [
                    'id' => $transaction->payrollPeriod->pp_id ?? null,
                    'name' => $transaction->payrollPeriod->pp_name ?? null,
                    'start_date' => $transaction->payrollPeriod->pp_start_date ?? null,
                    'end_date' => $transaction->payrollPeriod->pp_end_date ?? null,
                ],
                'total_earnings' => $transaction->at_e_amount,
                'total_deductions' => $transaction->at_d_amount,
                'net_amount' => $transaction->at_e_amount - $transaction->at_d_amount,
                'details_by_heading' => array_values($groupedDetails),
                'created_at' => $transaction->created_at,
                'updated_at' => $transaction->updated_at,
            ];

            return response()->json([
                'status' => true,
                'message' => 'Adhoc payment details retrieved successfully',
                'data' => $formattedData
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve adhoc payment details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Get all adhoc payments for a specific employee
     *
     * @param int $employeeId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiGetEmployeeAdhocPayments($employeeId, Request $request)
    {
        try {
            $user = Auth::user();
            $business_id = $user->emp_b_id;

            // Verify employee belongs to this business
            $employee = Employee::where('emp_id', $employeeId)
                ->where('emp_b_id', $business_id)
                ->with(['fh_department', 'fh_designation'])
                ->first();

            if (!$employee) {
                return response()->json([
                    'status' => false,
                    'message' => 'Employee not found'
                ], 404);
            }

            $query = AdhocTransaction::with([
                'payrollPeriod',
                'transaction_details.component.payrollHeading'
            ])
                ->where('at_b_id', $business_id)
                ->where('at_emp_id', $employeeId)
                ->where('at_e_amount', '>', 0);

            // Filter by financial year if provided
            if ($request->has('financial_year')) {
                $query->whereHas('payrollPeriod', function ($q) use ($request) {
                    $q->whereHas('financialYear', function ($q2) use ($request) {
                        $q2->where('fy_year', $request->financial_year);
                    });
                });
            }

            // Filter by date range
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereHas('payrollPeriod', function ($q) use ($request) {
                    $q->whereBetween('pp_start_date', [$request->start_date, $request->end_date]);
                });
            }

            $perPage = $request->get('per_page', 15);
            $transactions = $query->orderBy('created_at', 'desc')->paginate($perPage);

            $formattedData = $transactions->map(function ($transaction) {
                // Group components by heading
                $componentsByHeading = [];
                foreach ($transaction->transaction_details as $detail) {
                    $headingName = $detail->component->payrollHeading->first()->m_name ?? 'Uncategorized';

                    $componentsByHeading[] = [
                        'heading' => $headingName,
                        'component_name' => $detail->component->ac_adhoc_component_name ?? null,
                        'earning_amount' => $detail->earning_amount,
                        'deduction_amount' => $detail->deduction_amount,
                        'remarks' => $detail->remarks,
                    ];
                }

                return [
                    'id' => $transaction->at_id,
                    'payroll_period' => [
                        'id' => $transaction->payrollPeriod->pp_id ?? null,
                        'name' => $transaction->payrollPeriod->pp_name ?? null,
                        'start_date' => $transaction->payrollPeriod->pp_start_date ?? null,
                        'end_date' => $transaction->payrollPeriod->pp_end_date ?? null,
                    ],
                    'total_earnings' => $transaction->at_e_amount,
                    'total_deductions' => $transaction->at_d_amount,
                    'net_amount' => $transaction->at_e_amount - $transaction->at_d_amount,
                    'components' => $componentsByHeading,
                    'created_at' => $transaction->created_at,
                ];
            });

            // Calculate summary statistics
            $totalEarnings = $transactions->sum('at_e_amount');
            $totalDeductions = $transactions->sum('at_d_amount');
            $totalNet = $totalEarnings - $totalDeductions;

            return response()->json([
                'status' => true,
                'message' => 'Employee adhoc payments retrieved successfully',
                'data' => [
                    'employee' => [
                        'id' => $employee->emp_id,
                        'code' => $employee->emp_code,
                        'name' => $employee->emp_full_name,
                        'department' => $employee->fh_department->d_name ?? null,
                        'designation' => $employee->fh_designation->dg_name ?? null,
                    ],
                    'transactions' => $formattedData,
                    'summary' => [
                        'total_earnings' => $totalEarnings,
                        'total_deductions' => $totalDeductions,
                        'total_net' => $totalNet,
                        'total_transactions' => $transactions->total(),
                    ]
                ],
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve employee adhoc payments',
                'error' => $e->getMessage()
            ], 500);
        }
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
