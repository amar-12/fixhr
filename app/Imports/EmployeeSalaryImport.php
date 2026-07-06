<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\SalaryAllowance;
use App\Models\SalaryMasterHistory;
use App\Models\SalaryEmployeeSalary;
use App\Models\SalaryEmployeeEarnings;
use App\Models\SalaryEmployeeDeductions;
use App\Models\SalaryEmployerDeductions;
use App\Models\StatutoryDeduction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class EmployeeSalaryImport implements ToCollection
{
    protected $financial_year;

    public function __construct($financialYearId)
    {
        $this->financial_year = $financialYearId;
    }

    public function collection(Collection $rows)
    {
        Log::info("🟢 Salary Import Started");

        if ($rows->isEmpty()) {
            Log::error("Import failed: Excel file contains no rows.");
            return;
        }

        $headers = $rows->first();
        if (!$headers || $headers->filter()->isEmpty()) {
            Log::error("Import failed: Excel headers missing or empty.");
            return;
        }

        $dataRows = $rows->slice(1);
        if ($dataRows->isEmpty()) {
            Log::warning("Import warning: No data rows found after header.");
            return;
        }

        $successful = 0;
        $skipped = [];

        foreach ($dataRows as $index => $row) {
            $rowNumber = $index + 2;
            $data = $headers->combine($row);
            $normalizedData = collect($data)->keyBy(fn($v, $k) => strtolower(str_replace([' ', '_'], '', trim($k))));

            $empCode = $normalizedData->get('empcode');
            if (!$empCode) {
                $skipped[] = "Row #$rowNumber: Missing Employee Code.";
                continue;
            }

            Log::debug("Processing Row #$rowNumber (EmpCode: {$empCode})");

            $empData = Employee::where('emp_code', $empCode)->first();
            if (!$empData) {
                $skipped[] = "Row #$rowNumber: Employee '{$empCode}' not found in database.";
                continue;
            }

            $empId = $empData->emp_id;
            $businessId = $empData->emp_b_id;
            $financialYearId = $this->financial_year;

            $num = fn($key, $default = 0) =>
            is_numeric($normalizedData->get($key)) ? $normalizedData->get($key) : (float)str_replace(',', '', $normalizedData->get($key, $default));

            // Salary Fields
            $monthlyCtc = $num('monthlyctc');
            $annualCTC = $num('annualctc');
            $basic = $num('basicsalary');
            $hra = $num('houserentallowance');
            $da = $num('dearnessallowance');
            $ca = $num('conveyanceallowance');
            $medAll = $num('medicalallowance');
            $eduAll = $num('educationallowance');
            $speAll = $num('specialallowance');
            $otherAll = $num('otherallowance');
            $empPF = $num('employeepf');
            $empESIC = $num('employeeesic');
            $empLWF = $num('employeelwf');
            $empDeduction = $num('employeededuction');
            $employerPF = $num('employerpf');
            $employerESIC = $num('employeresic');
            $employerLWF = $num('employerlwf');
            $employerDeduction = $num('employerdeduction');
            $totalEarning = $num('totalearning');
            $annualGross = $num('annualgross');
            $grossPay = $num('grosspay');
            $netPay = $num('netpay');

            $currency = $normalizedData->get('currency', 'INR');
            $salaryGrade = $normalizedData->get('salarygrade', '');
            $remark = $normalizedData->get('remark', '');

            $pfApplicable = strtolower(trim($normalizedData->get('pfapplicable', 'no'))) === 'yes';
            $esicApplicable = strtolower(trim($normalizedData->get('esicapplicable', 'no'))) === 'yes';

            // WEF DATE
            $wef = null;
            if (!empty($normalizedData->get('wef'))) {
                $wefRaw = trim($normalizedData->get('wef'));
                try {
                    $wef = is_numeric($wefRaw)
                        ? ExcelDate::excelToDateTimeObject($wefRaw)->format('Y-m-d')
                        : Carbon::parse($wefRaw)->format('Y-m-d');
                } catch (\Exception $e) {
                    $skipped[] = "Row #$rowNumber: Invalid WEF format ({$wefRaw}).";
                    continue;
                }
            }

            // PF & ESIC Threshold
            $pfApplicable = strtolower(trim($normalizedData->get('pfapplicable', 'no'))) === 'yes';
            $esicApplicable = strtolower(trim($normalizedData->get('esicapplicable', 'no'))) === 'yes';

            if ($pfApplicable) {
                $calcEmployeePF = ($empPF > 1800) ? 1800 : $empPF;
                $calcEmployerPF = ($employerPF > 1950) ? 1950 : $employerPF;
            } else {
                $calcEmployeePF = 0;
                $calcEmployerPF = 0;
            }

            $calcEmployeeESIC = ($esicApplicable && $monthlyCtc <= 21000) ? $empESIC : 0;
            $calcEmployerESIC = ($esicApplicable && $monthlyCtc <= 21000) ? $employerESIC : 0;

            // DELETE OLD DATA
            SalaryEmployeeEarnings::where('es_e_emp_id', $empId)->delete();
            SalaryEmployeeDeductions::where('es_d_emp_id', $empId)->delete();
            SalaryEmployerDeductions::where('employer_sd_emp_id', $empId)->delete();

            // ✅ TOTAL DEDUCTION FIX (only PF/ESIC/LWF used)
            $finalEmployeeTotalDeduction = $calcEmployeePF + $calcEmployeeESIC + $empLWF;
            $finalEmployerTotalDeduction = $calcEmployerPF + $calcEmployerESIC + $employerLWF;

            // SALARY MASTER HISTORY
            SalaryMasterHistory::create([
                'sm_emp_id' => $empId,
                'sm_emp_b_id' => $businessId,
                'sm_monthly_ctc' => $monthlyCtc,
                'sm_annual_ctc' => $annualCTC,
                'sm_basic' => $basic,
                'sm_hra' => $hra,
                'sm_dear_allow' => $da,
                'sm_conv_allow' => $ca,
                'sm_med_allow' => $medAll,
                'sm_edu_allow' => $eduAll,
                'sm_spec_allow' => $speAll,
                'sm_other_allow' => $otherAll,
                'sm_employee_epf' => $calcEmployeePF,
                'sm_employee_esic' => $calcEmployeeESIC,
                'sm_employee_lwf' => $empLWF,
                'sm_employee_total_ded' => $finalEmployeeTotalDeduction,
                'sm_employer_epf' => $calcEmployerPF,
                'sm_employer_esic' => $calcEmployerESIC,
                'sm_employer_lwf' => $employerLWF,
                'sm_employer_total_ded' => $finalEmployerTotalDeduction,
                'sm_total_earning' => $totalEarning,
                'sm_annual_gross' => $annualGross,
                'sm_gross_pay' => $grossPay,
                'sm_net_pay' => $netPay,
                'sm_fy_id' => $financialYearId,
                'sm_remark' => $remark,
                'wef' => $wef,
            ]);

            // EMPLOYEE SALARY
            SalaryEmployeeSalary::updateOrCreate(
                ['es_b_id' => $businessId, 'es_emp_id' => $empId],
                [
                    'es_annual_ctc' => $annualCTC,
                    'es_monthly_ctc' => $monthlyCtc,
                    'es_base_salary' => $basic,
                    'es_earnings' => $totalEarning,
                    'es_deductions' => $finalEmployeeTotalDeduction,  // ✅ FIXED
                    'es_monthly_gross' => $grossPay,
                    'es_monthly_net_salary' => $netPay,
                    'es_currency' => $currency,
                    'es_salary_grade' => $salaryGrade,
                    'es_is_current' => 1,
                ]
            );

            // ALLOWANCES
            $amountMap = [
                'basic salary' => $basic,
                'house rent allowance' => $hra,
                'dearness allowance' => $da,
                'conveyance allowance' => $ca,
                'education allowance' => $eduAll,
                'special allowance' => $speAll,
                'medical allowance' => $medAll,
                'other allowance' => $otherAll,
            ];

            $salaryAllowances = SalaryAllowance::where('sa_b_id', $businessId)
                ->where('sa_is_active', 1)
                ->get();

            foreach ($salaryAllowances as $allowance) {
                $title = strtolower(trim($allowance->sa_title));
                if (isset($amountMap[$title])) {
                    SalaryEmployeeEarnings::create([
                        'es_sa_id' => $allowance->sa_id,
                        'es_e_b_id' => $businessId,
                        'es_e_emp_id' => $empId,
                        'es_e_type_id' => $allowance->sa_earning_type_id,
                        'es_e_cal_type_id' => $allowance->sa_calculation_type,
                        'es_e_amount' => $amountMap[$title]
                    ]);
                }
            }

            // DEDUCTIONS
            if ($calcEmployeePF > 0) {
                SalaryEmployeeDeductions::create([
                    'es_d_b_id' => $businessId,
                    'es_d_emp_id' => $empId,
                    'es_d_type_id' => 351,
                    'es_d_cal_type_id' => 1,
                    'es_d_amount' => $calcEmployeePF,
                ]);
            }

            if ($calcEmployeeESIC > 0) {
                SalaryEmployeeDeductions::create([
                    'es_d_b_id' => $businessId,
                    'es_d_emp_id' => $empId,
                    'es_d_type_id' => 352,
                    'es_d_cal_type_id' => 1,
                    'es_d_amount' => $calcEmployeeESIC,
                ]);
            }

            if ($calcEmployerPF > 0) {
                SalaryEmployerDeductions::create([
                    'employer_sd_b_id' => $businessId,
                    'employer_sd_emp_id' => $empId,
                    'employer_sd_type_id' => 351,
                    'employer_sd_cal_type_id' => 1,
                    'employer_sd_amount' => $calcEmployerPF,
                ]);
            }

            if ($calcEmployerESIC > 0) {
                SalaryEmployerDeductions::create([
                    'employer_sd_b_id' => $businessId,
                    'employer_sd_emp_id' => $empId,
                    'employer_sd_type_id' => 352,
                    'employer_sd_cal_type_id' => 1,
                    'employer_sd_amount' => $calcEmployerESIC,
                ]);
            }

            $successful++;
        }

        Log::info("✅ Salary Import Completed: {$successful} inserted/updated, " . count($skipped) . " skipped.");
        if (!empty($skipped)) {
            Log::warning("⛔ Skipped Details:", $skipped);
        }
    }
}
