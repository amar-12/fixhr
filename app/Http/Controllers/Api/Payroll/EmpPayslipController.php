<?php

namespace App\Http\Controllers\Api\Payroll;

use Carbon\Carbon;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Models\PayrollPeriod;
use Barryvdh\DomPDF\Facade\Pdf;
use NumberToWords\NumberToWords;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\PayslipConfiguration;
use Illuminate\Support\Facades\Auth;
use App\Models\ProcessedEmployeeSalary;
use ChandraHemant\HtkcUtils\ReturnHelper;
use ChandraHemant\HtkcUtils\PaginatedResource;
use App\Http\Resources\Payroll\PayslipResource;


class EmpPayslipController extends Controller
{

    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }







    // public function generateEmpPayslip()
    // {
    //     $user = Auth::user();

    //     $processedSalaries = ProcessedEmployeeSalary::with(['earnings'])->where('ps_emp_id', $user->emp_id)->get();

    //     if ($processedSalaries->isEmpty()) {
    //         return response()->json([
    //             'result' => [],
    //             'status' => false,
    //             'message' => 'Payslips not found',
    //         ], 404);
    //     }

    //     $employee = Employee::with([
    //         'fh_branch',
    //         'fh_department',
    //         'fh_designation',
    //         'fh_business'
    //     ])->findOrFail($user->emp_id);

    //     $payslipConfig = PayslipConfiguration::where('pc_b_id', $employee->emp_b_id)->first();


    //     $numberToWords = new NumberToWords();
    //     $numberTransformer = $numberToWords->getNumberTransformer('en');

    //     $payslips = $processedSalaries->map(function ($salary) use ($employee, $numberTransformer, $payslipConfig) {
    //         $net_salary = number_format((float) str_replace(',', '', $salary->ps_monthly_net_salary), 2, '.', '');
    //         [$integerPart, $decimalPart] = array_pad(explode(".", $net_salary), 2, 0);

    //         $net_salary_words = ucfirst($numberTransformer->toWords((int)$integerPart)) . " rupees";
    //         if ((int)$decimalPart > 0) {
    //             $net_salary_words .= " and " . $numberTransformer->toWords((int)$decimalPart) . " paise";
    //         }
    //         $net_salary_words .= " only";

    //         $payrollPeriod = PayrollPeriod::with('month', 'quarter_master', 'financialYear')
    //             ->find($salary->ps_payroll_id);

    //         $basicEarning = $salary->earnings->firstWhere('ps_earning_type_id', 360);
    //         $basicAmount = $basicEarning->ps_e_amount ?? 0;

    //        $payslipOptions = PayslipConfiguration::getPayslipOptionsForEmployee($employee->emp_b_id);


    //         return new PayslipResource((object)[
    //             'employee' => $employee,
    //             'processedSalary' => $salary,
    //             'employee_name' => $employee->emp_full_name,
    //             'monthly_salary' => $salary->ps_worked_days_salary,
    //             'basic_salary' => $salary->ps_basic_salary,
    //             'net_salary' => $salary->ps_monthly_net_salary,
    //             'earnings' => $salary->ps_earnings,
    //             'employee_deductions' => $salary->ps_employee_deductions,
    //             'employer_deductions' => $salary->ps_employer_deductions,
    //             'net_salary_words' => $net_salary_words,
    //             'payroll_period' => $payrollPeriod,
    //             'basic_amount' => $basicAmount,
    //             'payslip_options' => $payslipOptions, // ✅ pass here for blade
    //         ], $payslipConfig); // ✅ also pass config object if needed
    //     });

    //     return ReturnHelper::jsonApiReturn($payslips);
    // }


    public function generateEmpPayslip()
    {
        $user = Auth::user();

        $processedSalaries = ProcessedEmployeeSalary::with(['earnings', 'payrollPeriod'])
            ->where('ps_emp_id', $user->emp_id)
            ->get()
            ->filter(function ($salary) {
                $payslipDate = optional($salary->payrollPeriod)->pp_payslip_date;
                return $payslipDate && Carbon::parse($payslipDate)->lte(Carbon::today());
            });

        if ($processedSalaries->isEmpty()) {
            return response()->json([
                'result' => [],
                'status' => false,
                'message' => 'Payslips not found',
            ], 404);
        }

        $employee = Employee::with([
            'fh_branch',
            'fh_department',
            'fh_designation',
            'fh_business'
        ])->findOrFail($user->emp_id);

        $payslipConfig = PayslipConfiguration::where('pc_b_id', $employee->emp_b_id)->first();

        $numberToWords = new NumberToWords();
        $numberTransformer = $numberToWords->getNumberTransformer('en');

        $payslips = $processedSalaries->map(function ($salary) use ($employee, $numberTransformer, $payslipConfig) {
            $net_salary = number_format((float) str_replace(',', '', $salary->ps_monthly_net_salary), 2, '.', '');
            [$integerPart, $decimalPart] = array_pad(explode(".", $net_salary), 2, 0);

            $net_salary_words = ucfirst($numberTransformer->toWords((int)$integerPart)) . " rupees";
            if ((int)$decimalPart > 0) {
                $net_salary_words .= " and " . $numberTransformer->toWords((int)$decimalPart) . " paise";
            }
            $net_salary_words .= " only";

            $payrollPeriod = PayrollPeriod::with('month', 'quarter_master', 'financialYear')
                ->find($salary->ps_payroll_id);

            $basicEarning = $salary->earnings->firstWhere('ps_earning_type_id', 360);
            $basicAmount = $basicEarning->ps_e_amount ?? 0;

            $payslipOptions = PayslipConfiguration::getPayslipOptionsForEmployee($employee->emp_b_id);

            return new PayslipResource((object)[
                'employee' => $employee,
                'processedSalary' => $salary,
                'employee_name' => $employee->emp_full_name,
                'monthly_salary' => $salary->ps_worked_days_salary,
                'basic_salary' => $salary->ps_basic_salary,
                'net_salary' => $salary->ps_monthly_net_salary,
                'earnings' => $salary->ps_earnings,
                'employee_deductions' => $salary->ps_employee_deductions,
                'employer_deductions' => $salary->ps_employer_deductions,
                'net_salary_words' => $net_salary_words,
                'payroll_period' => $payrollPeriod,
                'basic_amount' => $basicAmount,
                'payslip_options' => $payslipOptions,
            ], $payslipConfig);
        })->values()->all(); // ✅ Reset keys and convert to array

        return ReturnHelper::jsonApiReturn($payslips);
    }

    public function empPayslipPdf($id)
    {
        $processedSalary = ProcessedEmployeeSalary::with('employee')->get()->first(function ($item) use ($id) {
            return md5($item->ps_payroll_id . '_' . $item->ps_emp_id) === $id;
        });

        if (!$processedSalary) {
            return response()->json([
                'result' => [],
                'status' => false,
                'message' => 'Payslip not found'
            ], 404);
        }

        $employee = Employee::with([
            'fh_branch',
            'fh_department',
            'fh_designation',
            'fh_business'
        ])->findOrFail($processedSalary->ps_emp_id);

        $payrollPeriod = PayrollPeriod::findOrFail($processedSalary->ps_payroll_id);
        $logoPath = $employee->fh_business->b_logo ?? null;

        // ✅ Fetch payslip configuration for employee's business
          $payslipConfig = PayslipConfiguration::where('pc_b_id', $employee->emp_b_id)->first();

          $payslipOptions = PayslipConfiguration::getPayslipOptionsForEmployee($employee->emp_b_id);


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
            'payslipOptions' => $payslipOptions, // ✅ pass to view
        ];

        $pdf = Pdf::loadView('admin.employees.salary.monthly_salary_template_two', $data)->setPaper('A4', 'portrait');

         // 🔐 Apply Password Based on Payment Mode
        $paymentMode = strtolower(trim($employee->emp_paymentmode ?? ''));

        $password = null; 

        if ($paymentMode === 'bank') {
            if (!empty($employee->emp_pan_number)) {
                $password = trim($employee->emp_pan_number);
            } else {
                $password = 'PANNOTAVBL';
            }
        }
        // Apply encryption only if password exists
        if (!empty($password)) {
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
}
