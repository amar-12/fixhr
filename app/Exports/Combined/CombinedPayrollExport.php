<?php
namespace App\Exports\Combined;

use App\Models\PayrollPeriod;
use App\Models\ProcessedEmployeeSalary;
use App\Models\ProcessedSalaryDeduction;
use App\Models\ProcessedSalaryEarning;
use App\Models\SalaryEmployeeDeductions;
use App\Models\SalaryEmployeeEarnings;
use App\Models\SalaryEmployeeSalary;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class CombinedPayrollExport implements WithMultipleSheets
{
    use Exportable;

    protected $payrollPeriodId;
    protected $businessId;
    protected $roundOffValues;
    protected $filters;
    protected $employeeContactsHeading;
    protected $employeeEarningsHeading;
    protected $employeeDeductionsHeading;
    protected $earningsHeading;
    protected $deductionsHeading;
    protected $daysInfoHeading;
    protected $filter;

    public function __construct(
        $payrollPeriodId,
        $businessId,
        $roundOffValues = false,
        $filters = [],
        $employeeContactsHeading = null,
        $employeeEarningsHeading = null,
        $employeeDeductionsHeading = null,
        $earningsHeading = null,
        $deductionsHeading = null,
        $daysInfoHeading = null
    ) {
        $this->payrollPeriodId = $payrollPeriodId;
        $this->businessId = $businessId;
        $this->roundOffValues = $roundOffValues;
        $this->filters = $filters;
        $this->employeeContactsHeading = $employeeContactsHeading;
        $this->employeeEarningsHeading = $employeeEarningsHeading;
        $this->employeeDeductionsHeading = $employeeDeductionsHeading;
        $this->earningsHeading = $earningsHeading;
        $this->deductionsHeading = $deductionsHeading;
        $this->daysInfoHeading = $daysInfoHeading;
    }

    public function sheets(): array
    {
        return [
            // Sheet 1: Payroll Register (Your exact same logic)
            new PayrollRegisterSheet(
                $this->payrollPeriodId,
                $this->businessId,
                $this->roundOffValues,
                $this->filters,
                $this->employeeContactsHeading,
                $this->employeeEarningsHeading,
                $this->employeeDeductionsHeading,
                $this->earningsHeading,
                $this->deductionsHeading,
                $this->daysInfoHeading
            ),
            // Sheet 2: Bank Sheet
             new BankSheetSheet(
                $this->payrollPeriodId,
                $this->businessId,
                $this->roundOffValues
            ),

            // Sheet 3: ESIC Report
            new ESICSheet(
                $this->payrollPeriodId,
                $this->businessId,
                $this->roundOffValues
            ),

             // Sheet 4: PF/EPF Report
            new PFEPFSheet(
                $this->payrollPeriodId,
                $this->businessId,
                $this->roundOffValues,
                $this->filters
            ),


        ];
    }
}
