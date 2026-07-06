<?php
namespace App\Exports\Combined;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

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
        ];
    }
}
