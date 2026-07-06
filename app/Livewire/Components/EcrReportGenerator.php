<?php

namespace App\Livewire\Components;

use Carbon\Carbon;
use Livewire\Component;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\ProcessedEmployeeSalary;
use App\Models\ProcessedSalaryEarning;
use App\Models\ProcessedSalaryDeduction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class EcrReportGenerator extends Component
{
    public $payrollId;
    public $isGenerating = false;
    public $businessId;
    public $payrollPeriod;
    public $message = '';
    public $messageType = 'info';
    public $totalEligibleEmployees = 0;
    public $totalPfWages = 0;
    public $totalEpfContribution = 0;
    public $totalEpsContribution = 0;

    // PF Constants
    const PF_WAGE_CEILING = 15000;
    const EPS_WAGE_CEILING = 15000;
    const EPS_MAX_CONTRIBUTION = 1250;
    const EPF_EMPLOYEE_RATE = 0.12;
    const EPS_EMPLOYER_RATE = 0.0833;
    const EDLI_RATE = 0.005;
    const EPF_ADMIN_RATE = 0.005;
    const EDLI_ADMIN_RATE = 0.0001;

    // Earning Type IDs
    const EARNING_BASIC = 360;
    const EARNING_DA = 362;

    // Deduction Type IDs
    const DEDUCTION_PF = 351;

    public function mount($payrollId = null)
    {
        $this->payrollId = $payrollId;
        $this->businessId = Auth::user()->emp_b_id ?? 1;

        if ($this->payrollId) {
            $this->loadPayrollData();
        }
    }

    /**
     * Load payroll data and statistics
     */
    public function loadPayrollData()
    {
        $this->payrollPeriod = PayrollPeriod::where('pp_b_id', $this->businessId)
            ->where('pp_id', $this->payrollId)
            ->first();

        if ($this->payrollPeriod) {
            $this->loadStatistics();
        }
    }

    /**
     * Load statistics for the selected payroll
     */
    public function loadStatistics()
    {
        try {
            $employeeData = $this->getEmployeePfData($this->payrollPeriod);
            $this->totalEligibleEmployees = count($employeeData);

            $this->totalPfWages = 0;
            $this->totalEpfContribution = 0;
            $this->totalEpsContribution = 0;

            foreach ($employeeData as $emp) {
                $this->totalPfWages += $emp['epf_wages'] ?? 0;
                $this->totalEpfContribution += $emp['epf_contribution'] ?? 0;
                $this->totalEpsContribution += $emp['eps_contribution'] ?? 0;
            }

            $this->message = 'Data loaded successfully. Found ' . $this->totalEligibleEmployees . ' eligible employees.';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            $this->message = 'Error loading statistics: ' . $e->getMessage();
            $this->messageType = 'error';
            Log::error('ECR Statistics Error', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Generate and download ECR report
     */
    public function generateReport()
    {
        $this->isGenerating = true;
        $this->message = 'Generating ECR report...';
        $this->messageType = 'info';

        try {
            // Get payroll data
            $payroll = PayrollPeriod::where('pp_b_id', $this->businessId)
                ->where('pp_id', $this->payrollId)
                ->first();

            if (!$payroll) {
                throw new \Exception('Payroll data not found!');
            }

            // Get employee PF data for this payroll
            $employeeData = $this->getEmployeePfData($payroll);

            if (empty($employeeData)) {
                throw new \Exception('No PF eligible employees found for this payroll period.');
            }

            // Generate ECR content
            $ecrContent = $this->generateEcrContent($employeeData, $payroll);

            // Generate filename
            $fileName = $this->generateFileName($payroll);

            $this->message = 'ECR report generated successfully!';
            $this->messageType = 'success';
            $this->isGenerating = false;

            // Create downloadable response
            return response()->streamDownload(
                function () use ($ecrContent) {
                    echo $ecrContent;
                },
                $fileName,
                [
                    'Content-Type' => 'text/plain',
                    'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                ]
            );

        } catch (\Exception $e) {
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
            $this->isGenerating = false;

            Log::error('ECR Generation Error', [
                'error' => $e->getMessage(),
                'payroll_id' => $this->payrollId,
                'trace' => $e->getTraceAsString()
            ]);

            return null;
        }
    }

    /**
     * Generate ECR content in EPFO format with #-# separator
     */
    private function generateEcrContent(array $employeeData, $payroll): string
    {
        $lines = [];

        // Add header row - exactly as shown in your example
        $headers = [
            'UAN',
            'NAME',
            'GROSS_WAGES',
            'EPF_WAGES',
            'EPS_WAGES',
            'EDU_WAGES',
            'EPF_CONTRIBUTION_REMITTED',
            'EPS_CONTRIBUTION_REMITTED',
            'DIFF_EPF_EPS_CONTRIBUTION_REMITTED',
            'NCP_DAYS',
            'REFUND_OF_ADVANCES'
        ];
        $lines[] = implode('#-#', $headers);

        // Add data for each employee
        foreach ($employeeData as $employee) {
            $lines[] = $this->formatEcrLine($employee);
        }

        // Add summary at the end (optional, but good to have)
        $lines[] = '';
        $lines[] = 'ECR Report Summary#-#' . $payroll->pp_name;
        $lines[] = 'Total Employees#-#' . count($employeeData);
        $lines[] = 'Total EPF Contribution#-#' . round(array_sum(array_column($employeeData, 'epf_contribution')), 0);
        $lines[] = 'Total EPS Contribution#-#' . round(array_sum(array_column($employeeData, 'eps_contribution')), 0);

        return implode("\n", $lines);
    }

    /**
     * Format a single ECR line for an employee with #-# separator
     */
    private function formatEcrLine(array $employee): string
    {
        // Round all values to nearest integer as shown in your example
        $fields = [
            $employee['uan'] ?? '',
            strtoupper($employee['name'] ?? ''),
            round($employee['gross_wages'] ?? 0),
            round($employee['epf_wages'] ?? 0),
            round($employee['eps_wages'] ?? 0),
            round($employee['edli_wages'] ?? 0),
            round($employee['epf_contribution'] ?? 0),
            round($employee['eps_contribution'] ?? 0),
            round(($employee['epf_contribution'] ?? 0) - ($employee['eps_contribution'] ?? 0)),
            $employee['ncp_days'] ?? 0,
            round($employee['recovery'] ?? 0)
        ];

        return implode('#-#', $fields);
    }

    /**
     * Generate filename for the report
     */
    private function generateFileName($payroll): string
    {
        $business = Auth::user()->fh_business ?? null;
        $businessCode = $business->b_code ?? 'BUS';
        $month = str_pad($payroll->pp_month ?? date('m'), 2, '0', STR_PAD_LEFT);
        $year = $payroll->pp_year ?? date('Y');
        $timestamp = Carbon::now()->format('Ymd_His');

        return "ECR_{$businessCode}_{$month}{$year}_{$timestamp}.txt";
    }

    /**
     * Get employee PF data using multiple methods
     */
    private function getEmployeePfData($payroll): array
    {
        try {
            // Method 1: Try using ProcessedEmployeeSalary model with relations
            $pfData = $this->getEmployeePfDataMethod1($payroll);

            // Method 2: If no data, try direct DB query
            if (empty($pfData)) {
                $pfData = $this->getEmployeePfDataMethod2($payroll);
            }

            // Method 3: If still no data, try simplified approach
            if (empty($pfData)) {
                $pfData = $this->getEmployeePfDataMethod3($payroll);
            }

            Log::info('Employee PF Data fetched', [
                'payroll_id' => $payroll->pp_id,
                'count' => count($pfData),
                'method' => $pfData ? 'success' : 'none'
            ]);

            return $pfData;

        } catch (\Exception $e) {
            Log::error('Error in getEmployeePfData:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * Method 1: Using Eloquent relationships - FIXED calculations
     */
    private function getEmployeePfDataMethod1($payroll): array
    {
        try {
            $processedSalaries = ProcessedEmployeeSalary::with([
                    'employee',
                    'earnings' => function($q) {
                        $q->whereIn('ps_earning_type_id', [
                            self::EARNING_BASIC,
                            self::EARNING_DA
                        ]);
                    }
                ])
                ->where('ps_payroll_id', $payroll->pp_id)
                ->where('ps_b_id', $this->businessId)
                ->whereHas('employee', function($q) {
                    $q->where('emp_is_pf_enabled', 120)
                      ->whereNotNull('emp_pf_no')
                      ->where('emp_pf_no', '!=', '');
                })
                ->get();

            $pfData = [];

            foreach ($processedSalaries as $ps) {
                if (!$ps->employee) continue;

                // Get Basic and DA amounts
                $basic = $ps->earnings
                    ->where('ps_earning_type_id', self::EARNING_BASIC)
                    ->sum('ps_e_amount') ?? 0;

                $da = $ps->earnings
                    ->where('ps_earning_type_id', self::EARNING_DA)
                    ->sum('ps_e_amount') ?? 0;

                // Calculate PF Wages (Basic + DA) and apply ceiling
                $pfWages = $basic + $da;
                $pfWagesCapped = min($pfWages, self::PF_WAGE_CEILING);

                // =============================
                // CORRECT CALCULATIONS
                // =============================

                // Employee Contribution (12% of PF wages)
                $employeeContribution = round($pfWagesCapped * self::EPF_EMPLOYEE_RATE, 2);

                // EPS Contribution (8.33% of PF wages, capped at 1250)
                $epsContribution = round(min($pfWagesCapped * self::EPS_EMPLOYER_RATE, self::EPS_MAX_CONTRIBUTION), 2);

                // Employer PF Contribution (Balance: Employee PF - EPS)
                $employerPfContribution = round($employeeContribution - $epsContribution, 2);

                // Total EPF Contribution remitted (Employee + Employer)
                $totalEpfContribution = $employeeContribution + $employerPfContribution;

                // Calculate NCP days
                $ncpDays = 0;
                if ($ps->ps_total_days_in_month && $ps->ps_total_days_worked) {
                    $ncpDays = max(0, $ps->ps_total_days_in_month - $ps->ps_total_days_worked);
                }

                // Get gross wages
                $grossWages = $ps->ps_monthly_gross ?? 0;

                Log::info('Employee calculation:', [
                    'name' => $ps->employee->emp_full_name,
                    'uan' => $ps->employee->emp_pf_no,
                    'basic' => $basic,
                    'da' => $da,
                    'pf_wages' => $pfWagesCapped,
                    'gross' => $grossWages,
                    'employee_contribution' => $employeeContribution,
                    'eps' => $epsContribution,
                    'employer_pf' => $employerPfContribution,
                    'total_epf' => $totalEpfContribution,
                    'diff' => $totalEpfContribution - $epsContribution
                ]);

                // Build the PF data array
                $pfData[] = [
                    'uan' => $ps->employee->emp_pf_no,
                    'name' => strtoupper(trim($ps->employee->emp_full_name ?? '')),
                    'gross_wages' => $grossWages,
                    'epf_wages' => $pfWagesCapped,
                    'eps_wages' => $pfWagesCapped,
                    'edli_wages' => $pfWagesCapped,
                    'epf_contribution' => $totalEpfContribution,
                    'eps_contribution' => $epsContribution,
                    'pension_contribution' => $employerPfContribution,
                    'ncp_days' => $ncpDays,
                    'recovery' => $ps->ps_recovery_amount ?? 0,
                ];
            }

            return $pfData;

        } catch (\Exception $e) {
            Log::error('Error in Method 1:', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Method 2: Direct DB query for PF data
     */
    private function getEmployeePfDataMethod2($payroll): array
    {
        try {
            $results = DB::table('processed_salaries as ps')
                ->join('employees as e', 'e.emp_id', '=', 'ps.ps_emp_id')
                ->leftJoin('processed_salary_earnings as basic', function($join) {
                    $join->on('basic.ps_id', '=', 'ps.ps_id')
                         ->where('basic.ps_earning_type_id', self::EARNING_BASIC);
                })
                ->leftJoin('processed_salary_earnings as da', function($join) {
                    $join->on('da.ps_id', '=', 'ps.ps_id')
                         ->where('da.ps_earning_type_id', self::EARNING_DA);
                })
                ->where('ps.ps_payroll_id', $payroll->pp_id)
                ->where('ps.ps_b_id', $this->businessId)
                ->where('e.emp_is_pf_enabled', 120)
                ->whereNotNull('e.emp_pf_no')
                ->where('e.emp_pf_no', '!=', '')
                ->select(
                    'e.emp_pf_no as uan',
                    'e.emp_full_name as name',
                    DB::raw('COALESCE(ps.ps_monthly_gross, 0) as gross_wages'),
                    DB::raw('COALESCE(basic.ps_e_amount, 0) + COALESCE(da.ps_e_amount, 0) as pf_wages'),
                    'ps.ps_total_days_in_month as total_days',
                    'ps.ps_total_days_worked as worked_days',
                    'ps.ps_recovery_amount as recovery'
                )
                ->orderBy('e.emp_full_name')
                ->get();

            $pfData = [];

            foreach ($results as $row) {
                $pfWagesCapped = min($row->pf_wages ?? 0, self::PF_WAGE_CEILING);

                // Calculate contributions
                $employeeContribution = round($pfWagesCapped * self::EPF_EMPLOYEE_RATE, 2);
                $epsContribution = round(min($pfWagesCapped * self::EPS_EMPLOYER_RATE, self::EPS_MAX_CONTRIBUTION), 2);
                $employerPfContribution = round($employeeContribution - $epsContribution, 2);
                $totalEpfContribution = $employeeContribution + $employerPfContribution;

                $ncpDays = 0;
                if ($row->total_days && $row->worked_days) {
                    $ncpDays = max(0, $row->total_days - $row->worked_days);
                }

                $pfData[] = [
                    'uan' => $row->uan,
                    'name' => strtoupper(trim($row->name ?? '')),
                    'gross_wages' => $row->gross_wages,
                    'epf_wages' => $pfWagesCapped,
                    'eps_wages' => $pfWagesCapped,
                    'edli_wages' => $pfWagesCapped,
                    'epf_contribution' => $totalEpfContribution,
                    'eps_contribution' => $epsContribution,
                    'pension_contribution' => $employerPfContribution,
                    'ncp_days' => $ncpDays,
                    'recovery' => $row->recovery ?? 0,
                ];
            }

            return $pfData;

        } catch (\Exception $e) {
            Log::error('Error in Method 2:', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Method 3: Simplified method using only processed_salaries table
     */
    private function getEmployeePfDataMethod3($payroll): array
    {
        try {
            $results = DB::table('processed_salaries as ps')
                ->join('employees as e', 'e.emp_id', '=', 'ps.ps_emp_id')
                ->where('ps.ps_payroll_id', $payroll->pp_id)
                ->where('ps.ps_b_id', $this->businessId)
                ->where('e.emp_is_pf_enabled', 120)
                ->whereNotNull('e.emp_pf_no')
                ->where('e.emp_pf_no', '!=', '')
                ->select(
                    'e.emp_pf_no as uan',
                    'e.emp_full_name as name',
                    'ps.ps_monthly_gross as gross_wages',
                    'ps.ps_basic_salary as basic_salary',
                    'ps.ps_total_days_in_month as total_days',
                    'ps.ps_total_days_worked as worked_days',
                    'ps.ps_recovery_amount as recovery'
                )
                ->orderBy('e.emp_full_name')
                ->get();

            $pfData = [];

            foreach ($results as $row) {
                $pfWagesCapped = min($row->basic_salary ?? 0, self::PF_WAGE_CEILING);

                // Calculate contributions
                $employeeContribution = round($pfWagesCapped * self::EPF_EMPLOYEE_RATE, 2);
                $epsContribution = round(min($pfWagesCapped * self::EPS_EMPLOYER_RATE, self::EPS_MAX_CONTRIBUTION), 2);
                $employerPfContribution = round($employeeContribution - $epsContribution, 2);
                $totalEpfContribution = $employeeContribution + $employerPfContribution;

                $ncpDays = 0;
                if ($row->total_days && $row->worked_days) {
                    $ncpDays = max(0, $row->total_days - $row->worked_days);
                }

                $pfData[] = [
                    'uan' => $row->uan,
                    'name' => strtoupper(trim($row->name ?? '')),
                    'gross_wages' => $row->gross_wages,
                    'epf_wages' => $pfWagesCapped,
                    'eps_wages' => $pfWagesCapped,
                    'edli_wages' => $pfWagesCapped,
                    'epf_contribution' => $totalEpfContribution,
                    'eps_contribution' => $epsContribution,
                    'pension_contribution' => $employerPfContribution,
                    'ncp_days' => $ncpDays,
                    'recovery' => $row->recovery ?? 0,
                ];
            }

            return $pfData;

        } catch (\Exception $e) {
            Log::error('Error in Method 3:', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Helper function to get gender code
     */
    private function getGenderCode($gender): string
    {
        $gender = strtoupper(substr(trim($gender), 0, 1));

        return match($gender) {
            'M' => 'M',
            'F' => 'F',
            'T' => 'T',
            default => 'M'
        };
    }

    /**
     * Reset component state
     */
    public function resetState()
    {
        $this->message = '';
        $this->messageType = 'info';
        $this->isGenerating = false;
    }

    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.components.ecr-report-generator', [
            'payrollPeriod' => $this->payrollPeriod,
            'totalEligibleEmployees' => $this->totalEligibleEmployees,
            'totalPfWages' => $this->totalPfWages,
            'totalEpfContribution' => $this->totalEpfContribution,
            'totalEpsContribution' => $this->totalEpsContribution,
            'isGenerating' => $this->isGenerating,
            'message' => $this->message,
            'messageType' => $this->messageType
        ]);
    }
}
