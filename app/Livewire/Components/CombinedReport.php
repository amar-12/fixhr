<?php

namespace App\Livewire\Components;

use App\Exports\Salary\BankSheetExport;
use App\Exports\Salary\PfEpsSheetExport;
use App\Models\Business;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\ProcessedEmployeeSalary;
use App\Models\ProcessedSalaryDeduction;
use App\Models\ProcessedSalaryEarning;
use App\Models\SalaryEmployeeDeductions;
use App\Models\SalaryEmployeeSalary;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Sheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CombinedReport extends Component
{
    public $selectedPayrollPeriodId;
    public $roundOffValues = false;
    public $businessId;
    public $showButton = true;
    public $buttonStyle = '';

    public function mount($payrollId = null, $showButton = true, $buttonStyle = '')
    {
        $user = Auth::user();
        $this->businessId = $user->emp_b_id;
        $this->selectedPayrollPeriodId = $payrollId;
        $this->showButton = $showButton;
        $this->buttonStyle = $buttonStyle;
    }

    public function generateCombinedReport()
    {
        $this->validate([
            'selectedPayrollPeriodId' => 'required|exists:payroll_periods,pp_id',
        ], [
            'selectedPayrollPeriodId.required' => 'Payroll period is required.',
        ]);

        $payroll = PayrollPeriod::where('pp_b_id', $this->businessId)
            ->where('pp_is_processed', 120)
            ->find($this->selectedPayrollPeriodId);

        if (!$payroll) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'Invalid Payroll Period selected.']);
            return;
        }
        dd($this->businessId);

        $business = Business::where('b_id', $this->businessId)->first();

        // Business details
        // $business = DB::table('businesses')->where('b_id', $this->businessId)->first();
        $businessName = $business->b_name ?? 'Business';

        // Get month name
        $monthName = $payroll->pp_name ?? 'Month';
        $user = Auth::user();

        // Generate combined report
        $combinedExport = new class(
            $this->selectedPayrollPeriodId,
            $this->businessId,
            $businessName,
            $monthName,
            $user,
            $business,
            $payroll,
            $this->roundOffValues
        ) implements WithMultipleSheets {
            use Exportable;

            protected $payrollId;
            protected $businessId;
            protected $businessName;
            protected $monthName;
            protected $user;
            protected $business;
            protected $payroll;
            protected $roundOffValues;

            public function __construct($payrollId, $businessId, $businessName, $monthName, $user, $business, $payroll, $roundOffValues)
            {
                $this->payrollId = $payrollId;
                $this->businessId = $businessId;
                $this->businessName = $businessName;
                $this->monthName = $monthName;
                $this->user = $user;
                $this->business = $business;
                $this->payroll = $payroll;
                $this->roundOffValues = $roundOffValues;
            }

            public function sheets(): array
            {
                $sheets = [];

                // Sheet 1: Bank Sheet
                $sheets[] = new BankSheetReportSheet(
                    $this->payrollId,
                    $this->businessId,
                    $this->businessName,
                    $this->monthName,
                    $this->user,
                    $this->business,
                    $this->payroll,
                    $this->roundOffValues
                );

                // Sheet 2: Payroll Register
                $sheets[] = new PayrollRegisterReportSheet(
                    $this->payrollId,
                    $this->businessId,
                    $this->businessName,
                    $this->monthName,
                    $this->user,
                    $this->roundOffValues
                );

                // Sheet 3: ESIC Report
                $sheets[] = new ESICReportSheet(
                    $this->payrollId,
                    $this->businessId,
                    $this->businessName,
                    $this->monthName,
                    $this->user,
                    $this->roundOffValues
                );

                // Sheet 4: PF/EPF Report
                $sheets[] = new PFEPFReportSheet(
                    $this->payrollId,
                    $this->businessId,
                    $this->businessName,
                    $this->monthName,
                    $this->user,
                    $this->payroll,
                    $this->roundOffValues
                );

                // Sheet 5: Letter Head
                $sheets[] = new LetterHeadSheet(
                    $this->payrollId,
                    $this->businessId,
                    $this->businessName,
                    $this->monthName,
                    $this->user,
                    $this->business,
                    $this->payroll,
                    $this->roundOffValues
                );

                return $sheets;
            }
        };

        $fileName = 'Combined_Report_' . str_replace(' ', '_', $businessName) . '_' .
                    str_replace(' ', '_', $monthName) . '_' .
                    Carbon::now()->format('Y_m_d') . '.xlsx';

        return Excel::download($combinedExport, $fileName);
    }

    public function render()
    {
        return view('livewire.components.combined-report');
    }
}

// Bank Sheet Report Sheet
class BankSheetReportSheet implements FromCollection, WithHeadings, WithTitle, WithEvents
{
    protected $payrollId;
    protected $businessId;
    protected $businessName;
    protected $monthName;
    protected $user;
    protected $business;
    protected $payroll;
    protected $roundOffValues;
    protected $data;

    public function __construct($payrollId, $businessId, $businessName, $monthName, $user, $business, $payroll, $roundOffValues)
    {
        $this->payrollId = $payrollId;
        $this->businessId = $businessId;
        $this->businessName = $businessName;
        $this->monthName = $monthName;
        $this->user = $user;
        $this->business = $business;
        $this->payroll = $payroll;
        $this->roundOffValues = $roundOffValues;
        $this->prepareData();
    }

    private function prepareData()
    {
        $data = Employee::with([
            'processedSalaries' => function ($q) {
                $q->where('ps_payroll_id', $this->payrollId);
            },
        ])
        ->whereHas('processedSalaries', function ($q) {
            $q->where('ps_payroll_id', $this->payrollId);
        })
        ->where('emp_b_id', $this->businessId)
        ->get();

        $this->data = $data->map(function ($emp, $index) {
            $processedSalary = $emp->processedSalaries->first();
            $netSalary = $processedSalary->ps_net_salary ?? 0;

            if ($this->roundOffValues) {
                $netSalary = round($netSalary);
            }

            return [
                'S#' => $index + 1,
                'Employee Code' => $emp->emp_code ?? '',
                'Employee Name' => $emp->emp_full_name ?? '',
                'Account Number' => "'" . ($emp->emp_bank_account_no ?? ''),
                'Bank Name' => $emp->emp_bank_name ?? '',
                'IFSC Code' => $emp->emp_bank_ifsc_code ?? '',
                'Net Salary' => number_format($netSalary, 2),
            ];
        });
    }

    public function collection()
    {
        // Add grand total row
        $grandTotal = $this->data->sum(function ($row) {
            return floatval(str_replace(',', '', $row['Net Salary']));
        });

        if ($this->roundOffValues) {
            $grandTotal = round($grandTotal);
        }

        $grandRow = [
            'S#' => 'Grand Total',
            'Employee Code' => '',
            'Employee Name' => '',
            'Account Number' => '',
            'Bank Name' => '',
            'IFSC Code' => '',
            'Net Salary' => number_format($grandTotal, 2),
        ];

        return $this->data->push($grandRow);
    }

    public function headings(): array
    {
        return [
            [$this->businessName],
            ['Bank Sheet Report - ' . $this->monthName],
            ['Printed on: ' . Carbon::now()->format('d-M-Y h:i A T')],
            $this->roundOffValues ? ['Note: All monetary values are rounded to nearest whole number'] : [''],
            ['S#', 'Employee Code', 'Employee Name', 'Account Number', 'Bank Name', 'IFSC Code', 'Net Salary (₹)']
        ];
    }

    public function title(): string
    {
        return 'Bank Sheet';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Apply styles
                $sheet->getStyle('A1:G5')->getFont()->setBold(true);
                $sheet->mergeCells('A1:G1');
                $sheet->mergeCells('A2:G2');
                $sheet->mergeCells('A3:G3');
                if ($this->roundOffValues) {
                    $sheet->mergeCells('A4:G4');
                }

                // Header row style
                $lastRow = $sheet->getHighestRow();
                $headerRow = $this->roundOffValues ? 5 : 4;

                $sheet->getStyle("A{$headerRow}:G{$headerRow}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('263871');

                $sheet->getStyle("A{$headerRow}:G{$headerRow}")
                    ->getFont()
                    ->setColor(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE)
                    ->setBold(true);

                // Borders
                $sheet->getStyle("A{$headerRow}:G{$lastRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // Grand total row style
                $sheet->getStyle("A{$lastRow}:G{$lastRow}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('D9D9D9');

                $sheet->getStyle("A{$lastRow}:G{$lastRow}")
                    ->getFont()
                    ->setBold(true);

                // Column widths
                $sheet->getColumnDimension('A')->setWidth(8);
                $sheet->getColumnDimension('B')->setWidth(15);
                $sheet->getColumnDimension('C')->setWidth(25);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(20);
                $sheet->getColumnDimension('F')->setWidth(15);
                $sheet->getColumnDimension('G')->setWidth(15);
            },
        ];
    }
}

// Payroll Register Report Sheet
class PayrollRegisterReportSheet implements FromCollection, WithHeadings, WithTitle, WithEvents
{
    protected $payrollId;
    protected $businessId;
    protected $businessName;
    protected $monthName;
    protected $user;
    protected $roundOffValues;
    protected $data;
    protected $columnHeaders;

    public function __construct($payrollId, $businessId, $businessName, $monthName, $user, $roundOffValues)
    {
        $this->payrollId = $payrollId;
        $this->businessId = $businessId;
        $this->businessName = $businessName;
        $this->monthName = $monthName;
        $this->user = $user;
        $this->roundOffValues = $roundOffValues;
        $this->prepareData();
    }

    private function prepareData()
    {
        $processedSalary = ProcessedEmployeeSalary::with([
            'employee.fh_employee_status',
            'employee.fh_department',
            'employee.fh_designation',
            'employee.fh_grade',
            'employee.fh_branch',
            'earnings',
            'deductions',
        ])
        ->where('ps_payroll_id', $this->payrollId)
        ->get();

        // Get earnings and deductions components
        $earningsComponents = ProcessedSalaryEarning::whereIn('ps_id', $processedSalary->pluck('ps_id'))
            ->distinct()->pluck('ps_earning_type')->toArray();

        $deductionsComponents = ProcessedSalaryDeduction::whereIn('ps_id', $processedSalary->pluck('ps_id'))
            ->distinct()->pluck('ps_deduction_type')->toArray();

        // Define basic columns
        $basicColumns = ['S#', 'Emp Code', 'Employee Name', 'Department', 'Designation', 'DOJ'];

        // Add earnings columns
        foreach ($earningsComponents as $earning) {
            $basicColumns[] = $earning;
        }

        // Add deductions columns
        foreach ($deductionsComponents as $deduction) {
            $basicColumns[] = $deduction;
        }

        $basicColumns[] = 'Gross Salary';
        $basicColumns[] = 'Total Deductions';
        $basicColumns[] = 'Net Salary';

        $this->columnHeaders = $basicColumns;

        $this->data = $processedSalary->map(function ($item, $index) use ($earningsComponents, $deductionsComponents) {
            $row = [
                'S#' => $index + 1,
                'Emp Code' => $item->employee->emp_code ?? '',
                'Employee Name' => $item->employee->emp_full_name ?? '',
                'Department' => $item->employee->fh_department->d_name ?? '',
                'Designation' => $item->employee->fh_designation->dg_name ?? '',
                'DOJ' => $item->employee->emp_date_of_joining
                    ? Carbon::parse($item->employee->emp_date_of_joining)->format('d-M-Y')
                    : '',
            ];

            // Add earnings
            $earningsData = ProcessedSalaryEarning::where('ps_id', $item->ps_id)
                ->pluck('ps_e_amount', 'ps_earning_type')->toArray();

            foreach ($earningsComponents as $earning) {
                $value = $earningsData[$earning] ?? 0;
                $row[$earning] = $this->roundOffValues ? round($value) : $value;
            }

            // Add deductions
            $deductionsData = ProcessedSalaryDeduction::where('ps_id', $item->ps_id)
                ->pluck('ps_d_amount', 'ps_deduction_type')->toArray();

            foreach ($deductionsComponents as $deduction) {
                $value = $deductionsData[$deduction] ?? 0;
                $row[$deduction] = $this->roundOffValues ? round($value) : $value;
            }

            // Calculate totals
            $grossSalary = $item->ps_monthly_gross ?? 0;
            $totalDeductions = $item->ps_total_deductions ?? 0;
            $netSalary = $item->ps_monthly_net_salary ?? 0;

            if ($this->roundOffValues) {
                $grossSalary = round($grossSalary);
                $totalDeductions = round($totalDeductions);
                $netSalary = round($netSalary);
            }

            $row['Gross Salary'] = $grossSalary;
            $row['Total Deductions'] = $totalDeductions;
            $row['Net Salary'] = $netSalary;

            return $row;
        });
    }

    public function collection()
    {
        // Calculate grand totals
        $grandRow = [];
        foreach ($this->columnHeaders as $header) {
            if (in_array($header, ['S#', 'Emp Code', 'Employee Name', 'Department', 'Designation', 'DOJ'])) {
                $grandRow[$header] = $header === 'S#' ? 'Grand Total' : '';
            } else {
                $sum = $this->data->sum(function ($row) use ($header) {
                    return is_numeric($row[$header] ?? 0) ? $row[$header] : 0;
                });
                $grandRow[$header] = $this->roundOffValues ? round($sum) : $sum;
            }
        }

        return $this->data->push($grandRow);
    }

    public function headings(): array
    {
        return [
            [$this->businessName],
            ['Payroll Register Report - ' . $this->monthName],
            ['Printed on: ' . Carbon::now()->format('d-M-Y h:i A T')],
            $this->roundOffValues ? ['Note: All monetary values are rounded to nearest whole number'] : [''],
            $this->columnHeaders
        ];
    }

    public function title(): string
    {
        return 'Payroll Register';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestColumn = $sheet->getHighestColumn();
                $headerRow = $this->roundOffValues ? 5 : 4;
                $lastRow = $sheet->getHighestRow();

                // Apply styles
                for ($i = 1; $i <= $headerRow - 1; $i++) {
                    $sheet->mergeCells("A{$i}:{$highestColumn}{$i}");
                    $sheet->getStyle("A{$i}")->getFont()->setBold(true);
                }

                // Header row style
                $sheet->getStyle("A{$headerRow}:{$highestColumn}{$headerRow}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('263871');

                $sheet->getStyle("A{$headerRow}:{$highestColumn}{$headerRow}")
                    ->getFont()
                    ->setColor(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE)
                    ->setBold(true);

                // Set column widths
                foreach (range('A', $highestColumn) as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }

                // Grand total row
                $sheet->getStyle("A{$lastRow}:{$highestColumn}{$lastRow}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('D9D9D9');

                $sheet->getStyle("A{$lastRow}:{$highestColumn}{$lastRow}")
                    ->getFont()
                    ->setBold(true);
            },
        ];
    }
}

// ESIC Report Sheet
class ESICReportSheet implements FromCollection, WithHeadings, WithTitle, WithEvents
{
    protected $payrollId;
    protected $businessId;
    protected $businessName;
    protected $monthName;
    protected $user;
    protected $roundOffValues;
    protected $data;
    protected $columnHeaders;

    public function __construct($payrollId, $businessId, $businessName, $monthName, $user, $roundOffValues)
    {
        $this->payrollId = $payrollId;
        $this->businessId = $businessId;
        $this->businessName = $businessName;
        $this->monthName = $monthName;
        $this->user = $user;
        $this->roundOffValues = $roundOffValues;
        $this->prepareData();
    }

    private function prepareData()
    {
        $processedSalary = ProcessedEmployeeSalary::with([
            'employee.fh_gender',
            'employee.fh_department',
            'employee.fh_designation',
        ])
        ->where('ps_payroll_id', $this->payrollId)
        ->get();

        // Filter ESIC eligible employees
        $processedSalary = $processedSalary->filter(function ($item) {
            if (empty($item->employee)) return false;
            if ($item->employee->emp_esic_limit != 120) return false;
            if (empty($item->employee->emp_esic_no)) return false;
            return true;
        })->values();

        $this->columnHeaders = [
            'S#', 'Emp Code', 'ESI No.', 'Employee Name', 'Gender', 'Aadhaar No',
            'DOB', 'DOJ', 'Department', 'Designation', 'Days Worked',
            'Amount for ESI', 'Employee ESI', 'Employer ESI', 'Total ESI'
        ];

        $this->data = $processedSalary->map(function ($item, $index) {
            $monthlyGross = $item->ps_monthly_gross ?? 0;
            $employeeEsic = $monthlyGross * 0.75 / 100;
            $employerEsic = $monthlyGross * 3.25 / 100;
            $totalEsic = $employeeEsic + $employerEsic;

            if ($this->roundOffValues) {
                $monthlyGross = round($monthlyGross);
                $employeeEsic = round($employeeEsic);
                $employerEsic = round($employerEsic);
                $totalEsic = round($totalEsic);
            }

            return [
                'S#' => $index + 1,
                'Emp Code' => $item->employee->emp_code ?? '-',
                'ESI No.' => $item->employee->emp_esic_no ?? '-',
                'Employee Name' => $item->employee->emp_full_name ?? '-',
                'Gender' => $item->employee->fh_gender->m_name ?? '-',
                'Aadhaar No' => $item->employee->emp_aadhaar_no ?? '-',
                'DOB' => $item->employee->emp_dob
                    ? Carbon::parse($item->employee->emp_dob)->format('d-M-Y')
                    : '-',
                'DOJ' => $item->employee->emp_date_of_joining
                    ? Carbon::parse($item->employee->emp_date_of_joining)->format('d-M-Y')
                    : '-',
                'Department' => $item->employee->fh_department->d_name ?? '-',
                'Designation' => $item->employee->fh_designation->dg_name ?? '-',
                'Days Worked' => number_format($item->ps_total_days_worked ?? 0, 2),
                'Amount for ESI' => number_format($monthlyGross, 2),
                'Employee ESI' => number_format($employeeEsic, 2),
                'Employer ESI' => number_format($employerEsic, 2),
                'Total ESI' => number_format($totalEsic, 2),
            ];
        });
    }

    public function collection()
    {
        // Calculate grand totals
        $grandRow = [];
        foreach ($this->columnHeaders as $header) {
            if (in_array($header, ['S#', 'Emp Code', 'ESI No.', 'Employee Name', 'Gender', 'Aadhaar No',
                'DOB', 'DOJ', 'Department', 'Designation'])) {
                $grandRow[$header] = $header === 'S#' ? 'Grand Total' : '';
            } else {
                $sum = $this->data->sum(function ($row) use ($header) {
                    return floatval(str_replace(',', '', $row[$header] ?? '0'));
                });
                $grandRow[$header] = number_format($sum, 2);
            }
        }

        return $this->data->push($grandRow);
    }

    public function headings(): array
    {
        return [
            [$this->businessName],
            ['ESIC Report - ' . $this->monthName],
            ['Printed on: ' . Carbon::now()->format('d-M-Y h:i A T')],
            $this->roundOffValues ? ['Note: All monetary values are rounded to nearest whole number'] : [''],
            $this->columnHeaders
        ];
    }

    public function title(): string
    {
        return 'ESIC Report';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestColumn = $sheet->getHighestColumn();
                $headerRow = $this->roundOffValues ? 5 : 4;
                $lastRow = $sheet->getHighestRow();

                // Apply styles
                for ($i = 1; $i <= $headerRow - 1; $i++) {
                    $sheet->mergeCells("A{$i}:{$highestColumn}{$i}");
                    $sheet->getStyle("A{$i}")->getFont()->setBold(true);
                }

                // Header row style
                $sheet->getStyle("A{$headerRow}:{$highestColumn}{$headerRow}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('263871');

                $sheet->getStyle("A{$headerRow}:{$highestColumn}{$headerRow}")
                    ->getFont()
                    ->setColor(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE)
                    ->setBold(true);

                // Grand total row
                $sheet->getStyle("A{$lastRow}:{$highestColumn}{$lastRow}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('D9D9D9');

                $sheet->getStyle("A{$lastRow}:{$highestColumn}{$lastRow}")
                    ->getFont()
                    ->setBold(true);

                // Set column widths
                $sheet->getColumnDimension('A')->setWidth(8);
                $sheet->getColumnDimension('B')->setWidth(12);
                $sheet->getColumnDimension('C')->setWidth(15);
                $sheet->getColumnDimension('D')->setWidth(25);
                $sheet->getColumnDimension('E')->setWidth(10);
                $sheet->getColumnDimension('F')->setWidth(15);
                $sheet->getColumnDimension('G')->setWidth(12);
                $sheet->getColumnDimension('H')->setWidth(12);
                $sheet->getColumnDimension('I')->setWidth(15);
                $sheet->getColumnDimension('J')->setWidth(15);
                $sheet->getColumnDimension('K')->setWidth(12);
                $sheet->getColumnDimension('L')->setWidth(15);
                $sheet->getColumnDimension('M')->setWidth(15);
                $sheet->getColumnDimension('N')->setWidth(15);
                $sheet->getColumnDimension('O')->setWidth(15);
            },
        ];
    }
}

// PF/EPF Report Sheet
class PFEPFReportSheet implements FromCollection, WithHeadings, WithTitle, WithEvents
{
    protected $payrollId;
    protected $businessId;
    protected $businessName;
    protected $monthName;
    protected $user;
    protected $payroll;
    protected $roundOffValues;
    protected $data;
    protected $columnHeaders;

    public function __construct($payrollId, $businessId, $businessName, $monthName, $user, $payroll, $roundOffValues)
    {
        $this->payrollId = $payrollId;
        $this->businessId = $businessId;
        $this->businessName = $businessName;
        $this->monthName = $monthName;
        $this->user = $user;
        $this->payroll = $payroll;
        $this->roundOffValues = $roundOffValues;
        $this->prepareData();
    }

    private function prepareData()
    {
        $employeesWithPf = PayrollPeriod::join('processed_salaries', 'processed_salaries.ps_payroll_id', '=', 'payroll_periods.pp_id')
            ->join('employee_salaries', 'employee_salaries.es_emp_id', '=', 'processed_salaries.ps_emp_id')
            ->join('employees', 'employees.emp_id', '=', 'processed_salaries.ps_emp_id')
            ->leftJoin('departments', 'departments.d_id', '=', 'employees.emp_d_id')
            ->leftJoin('designations', 'designations.dg_id', '=', 'employees.emp_dg_id')
            ->leftJoin('master_table AS gender', function ($join) {
                $join->on('gender.m_id', '=', 'employees.emp_gender_id')
                    ->where('gender.m_group', '=', 'GENDER');
            })
            ->leftJoin('processed_salary_earnings as basic_earnings', function ($join) {
                $join->on('basic_earnings.ps_id', '=', 'processed_salaries.ps_id')
                    ->where('basic_earnings.ps_earning_type_id', '=', 360);
            })
            ->leftJoin('processed_salary_deductions as epf_deductions', function ($join) {
                $join->on('epf_deductions.ps_id', '=', 'processed_salaries.ps_id')
                    ->where('epf_deductions.ps_deduction_type_id', '=', 351)
                    ->where('epf_deductions.ps_d_category', '=', 'employee');
            })
            ->select(
                'employees.emp_id',
                'employees.emp_code',
                'employees.emp_full_name',
                'employees.emp_pf_universal_ac_no',
                'employees.emp_dob',
                'employees.emp_date_of_joining',
                'employees.emp_pf_no',
                'employees.emp_eps_no',
                'departments.d_name as department',
                'designations.dg_name as designation',
                'gender.m_name as gender',
                'processed_salaries.ps_total_days_worked',
                'processed_salaries.ps_monthly_gross',
                'basic_earnings.ps_e_amount as basic_amount',
                'epf_deductions.ps_d_amount as epf_amount'
            )
            ->where('payroll_periods.pp_id', $this->payrollId)
            ->where('employees.emp_is_pf_enabled', 120)
            ->get()
            ->unique('emp_id')
            ->values();

        $this->columnHeaders = [
            'S#', 'Emp Code', 'PF No.', 'EPS No.', 'UA No.', 'Employee Name',
            'Gender', 'DOB', 'DOJ', 'Department', 'Designation', 'Days Worked',
            'Gross Salary', 'Basic Salary', 'PF Employee', 'PF Employer', 'EPS', 'Total PF'
        ];

        $this->data = $employeesWithPf->map(function ($emp, $index) {
            $basicSalary = $emp->basic_amount ?? 0;
            $epfAmount = $emp->epf_amount ?? 0;
            $grossSalary = $emp->ps_monthly_gross ?? 0;

            // Calculate PF contributions (assuming 12% each)
            $pfEmployee = $basicSalary * 0.12;
            $pfEmployer = $basicSalary * 0.12;
            $eps = $basicSalary * 0.0833; // EPS contribution
            $totalPf = $pfEmployee + $pfEmployer + $eps;

            if ($this->roundOffValues) {
                $basicSalary = round($basicSalary);
                $epfAmount = round($epfAmount);
                $grossSalary = round($grossSalary);
                $pfEmployee = round($pfEmployee);
                $pfEmployer = round($pfEmployer);
                $eps = round($eps);
                $totalPf = round($totalPf);
            }

            return [
                'S#' => $index + 1,
                'Emp Code' => $emp->emp_code ?? '',
                'PF No.' => $emp->emp_pf_no ?? '',
                'EPS No.' => $emp->emp_eps_no ?? '',
                'UA No.' => $emp->emp_pf_universal_ac_no ?? '',
                'Employee Name' => $emp->emp_full_name ?? '',
                'Gender' => $emp->gender ?? '',
                'DOB' => $emp->emp_dob ? Carbon::parse($emp->emp_dob)->format('d-M-Y') : '',
                'DOJ' => $emp->emp_date_of_joining ? Carbon::parse($emp->emp_date_of_joining)->format('d-M-Y') : '',
                'Department' => $emp->department ?? '',
                'Designation' => $emp->designation ?? '',
                'Days Worked' => number_format($emp->ps_total_days_worked ?? 0, 2),
                'Gross Salary' => number_format($grossSalary, 2),
                'Basic Salary' => number_format($basicSalary, 2),
                'PF Employee' => number_format($pfEmployee, 2),
                'PF Employer' => number_format($pfEmployer, 2),
                'EPS' => number_format($eps, 2),
                'Total PF' => number_format($totalPf, 2),
            ];
        });
    }

    public function collection()
    {
        // Calculate grand totals
        $grandRow = [];
        foreach ($this->columnHeaders as $header) {
            if (in_array($header, ['S#', 'Emp Code', 'PF No.', 'EPS No.', 'UA No.', 'Employee Name',
                'Gender', 'DOB', 'DOJ', 'Department', 'Designation'])) {
                $grandRow[$header] = $header === 'S#' ? 'Grand Total' : '';
            } else {
                $sum = $this->data->sum(function ($row) use ($header) {
                    return floatval(str_replace(',', '', $row[$header] ?? '0'));
                });
                $grandRow[$header] = number_format($sum, 2);
            }
        }

        return $this->data->push($grandRow);
    }

    public function headings(): array
    {
        return [
            [$this->businessName],
            ['PF/EPF Report - ' . $this->monthName],
            ['Printed on: ' . Carbon::now()->format('d-M-Y h:i A T')],
            $this->roundOffValues ? ['Note: All monetary values are rounded to nearest whole number'] : [''],
            $this->columnHeaders
        ];
    }

    public function title(): string
    {
        return 'PF/EPF Report';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestColumn = $sheet->getHighestColumn();
                $headerRow = $this->roundOffValues ? 5 : 4;
                $lastRow = $sheet->getHighestRow();

                // Apply styles
                for ($i = 1; $i <= $headerRow - 1; $i++) {
                    $sheet->mergeCells("A{$i}:{$highestColumn}{$i}");
                    $sheet->getStyle("A{$i}")->getFont()->setBold(true);
                }

                // Header row style
                $sheet->getStyle("A{$headerRow}:{$highestColumn}{$headerRow}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('263871');

                $sheet->getStyle("A{$headerRow}:{$highestColumn}{$headerRow}")
                    ->getFont()
                    ->setColor(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE)
                    ->setBold(true);

                // Grand total row
                $sheet->getStyle("A{$lastRow}:{$highestColumn}{$lastRow}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('D9D9D9');

                $sheet->getStyle("A{$lastRow}:{$highestColumn}{$lastRow}")
                    ->getFont()
                    ->setBold(true);

                // Set column widths
                foreach (range('A', $highestColumn) as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }
}

// Letter Head Sheet
class LetterHeadSheet implements FromCollection, WithHeadings, WithTitle, WithEvents
{
    protected $payrollId;
    protected $businessId;
    protected $businessName;
    protected $monthName;
    protected $user;
    protected $business;
    protected $payroll;
    protected $roundOffValues;

    public function __construct($payrollId, $businessId, $businessName, $monthName, $user, $business, $payroll, $roundOffValues)
    {
        $this->payrollId = $payrollId;
        $this->businessId = $businessId;
        $this->businessName = $businessName;
        $this->monthName = $monthName;
        $this->user = $user;
        $this->business = $business;
        $this->payroll = $payroll;
        $this->roundOffValues = $roundOffValues;
    }

    public function collection()
    {
        // Get total salary amount
        $totalSalary = ProcessedEmployeeSalary::where('ps_payroll_id', $this->payrollId)
            ->sum('ps_monthly_net_salary');

        if ($this->roundOffValues) {
            $totalSalary = round($totalSalary);
        }

        // Convert number to words
        $amountInWords = $this->numberToWords(round($totalSalary));

        // Prepare letter content
        $letterContent = [
            ['To,'],
            ['The Branch Manager,'],
            [$this->business->b_bank_name ?? 'Bank Name'],
            [''],
            ['Date: ' . Carbon::now()->format('d/m/Y')],
            [''],
            ['Dear Sir/Madam,'],
            [''],
            ['Kindly process our staff salary whose total amount is Rs.' .
             number_format($totalSalary, 0) . '/- Inwords: ' . $amountInWords .
             ' only. Cheque No 935541. Please do process it at the earliest.'],
            [''],
            [''],
            ['Thanking you,'],
            [''],
            [$this->businessName],
        ];

        return collect($letterContent);
    }

    private function numberToWords($number)
    {
        $ones = [
            0 => 'Zero',
            1 => 'One',
            2 => 'Two',
            3 => 'Three',
            4 => 'Four',
            5 => 'Five',
            6 => 'Six',
            7 => 'Seven',
            8 => 'Eight',
            9 => 'Nine',
            10 => 'Ten',
            11 => 'Eleven',
            12 => 'Twelve',
            13 => 'Thirteen',
            14 => 'Fourteen',
            15 => 'Fifteen',
            16 => 'Sixteen',
            17 => 'Seventeen',
            18 => 'Eighteen',
            19 => 'Nineteen',
        ];

        $tens = [
            2 => 'Twenty',
            3 => 'Thirty',
            4 => 'Forty',
            5 => 'Fifty',
            6 => 'Sixty',
            7 => 'Seventy',
            8 => 'Eighty',
            9 => 'Ninety',
        ];

        if ($number == 0) {
            return $ones[0];
        }

        $words = '';

        // Crore part
        if ($number >= 10000000) {
            $crore = floor($number / 10000000);
            $words .= $this->smallNumberToWords($crore, $ones, $tens) . ' Crore ';
            $number %= 10000000;
        }

        // Lakh part
        if ($number >= 100000) {
            $lakh = floor($number / 100000);
            $words .= $this->smallNumberToWords($lakh, $ones, $tens) . ' Lakh ';
            $number %= 100000;
        }

        // Thousand part
        if ($number >= 1000) {
            $thousand = floor($number / 1000);
            $words .= $this->smallNumberToWords($thousand, $ones, $tens) . ' Thousand ';
            $number %= 1000;
        }

        // Hundred part
        if ($number >= 100) {
            $hundred = floor($number / 100);
            $words .= $this->smallNumberToWords($hundred, $ones, $tens) . ' Hundred ';
            $number %= 100;
        }

        // Tens and ones
        if ($number > 0) {
            if ($words != '') {
                $words .= '';
            }
            $words .= $this->smallNumberToWords($number, $ones, $tens);
        }

        return trim($words);
    }

    private function smallNumberToWords($number, $ones, $tens)
    {
        if ($number < 20) {
            return $ones[$number];
        } else {
            $ten = floor($number / 10);
            $unit = $number % 10;
            $words = $tens[$ten];
            if ($unit > 0) {
                $words .= ' ' . $ones[$unit];
            }
            return $words;
        }
    }

    public function headings(): array
    {
        return [
            ['LETTER HEAD'],
            [$this->businessName],
            ['For Month: ' . $this->monthName],
            ['Printed on: ' . Carbon::now()->format('d-M-Y h:i A T')],
        ];
    }

    public function title(): string
    {
        return 'Letter Head';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Merge header rows
                $sheet->mergeCells('A1:D1');
                $sheet->mergeCells('A2:D2');
                $sheet->mergeCells('A3:D3');
                $sheet->mergeCells('A4:D4');

                // Style header
                $sheet->getStyle('A1:A4')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Style letter content
                $sheet->getStyle('A5:A18')->getFont()->setSize(11);

                // Make amount line bold partially
                $sheet->getStyle('A13')->getFont()->setBold(true);

                // Company name at bottom
                $sheet->getStyle('A17')->getFont()->setBold(true)->setSize(12);

                // Set column width
                $sheet->getColumnDimension('A')->setWidth(80);

                // Add border for the letter
                $sheet->getStyle('A5:A18')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            },
        ];
    }
}
