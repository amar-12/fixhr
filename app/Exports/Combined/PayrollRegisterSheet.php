<?php

namespace App\Exports\Combined;

use App\Models\Business;
use App\Models\PayrollPeriod;
use App\Models\ProcessedEmployeeSalary;
use App\Models\ProcessedSalaryDeduction;
use App\Models\ProcessedSalaryEarning;
use App\Models\SalaryEmployeeDeductions;
use App\Models\SalaryEmployeeEarnings;
use App\Models\SalaryEmployeeSalary;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PayrollRegisterSheet implements FromCollection, WithTitle, WithHeadings, WithEvents, WithCustomStartCell, WithColumnWidths
{
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
    protected $data;
    protected $columnHeaders;
    protected $employeeSalaryEarningComponents = [];
    protected $employeeSalaryDeductionComponents = [];
    protected $earningsComponents = [];
    protected $deductionsComponents = [];
    protected $basicInfoColumns = [];
    protected $employeeContactsColumns = [];
    protected $daysInfoColumns = [];
    protected $businessName;
    protected $monthName;
    protected $filter = [];


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

        $this->initializeData();
        $this->buildColumnHeaders();
    }


    protected function initializeData()
    {
        $payroll = PayrollPeriod::where('pp_b_id', $this->businessId)
            ->where('pp_is_processed', 120)
            ->find($this->payrollPeriodId);

        if (!$payroll) {
            $this->data = collect();
            return;
        }

        $this->businessName = Business::where('b_id', $this->businessId)->value('b_name') ?? 'Unknown Business';
        $this->monthName = $payroll->pp_name ?? 'Month';

        $query = ProcessedEmployeeSalary::with(
            'employee.fh_employee_status',
            'employee.fh_department',
            'employee.fh_designation',
            'employee.fh_grade',
            'employee.fh_branch',
            'earnings',
            'deductions',
            'employee.employeeProjects'
        )
            ->where('ps_payroll_id', $this->payrollPeriodId);

        $processedSalary = $query->get();

        if ($processedSalary->isEmpty()) {
            $this->data = collect();
            return;
        }

        $employeeSalary = SalaryEmployeeSalary::with('fh_employee')
            ->where('es_b_id', $this->businessId)
            ->whereIn('es_emp_id', $processedSalary->pluck('ps_emp_id'))
            ->get();

        $this->employeeSalaryDeductionComponents = SalaryEmployeeDeductions::with('fh_salary_deduction_type')
            ->whereIn('es_d_emp_id', $employeeSalary->pluck('es_emp_id')->toArray())
            ->get()
            ->pluck('fh_salary_deduction_type.m_name')
            ->unique()
            ->values()
            ->toArray();

        $this->employeeSalaryEarningComponents = SalaryEmployeeEarnings::with('fh_salary_earning_type')
            ->whereIn('es_e_emp_id', $employeeSalary->pluck('es_emp_id')->toArray())
            ->get()
            ->pluck('fh_salary_earning_type.sa_title')
            ->unique()
            ->values()
            ->toArray();

        $this->earningsComponents = ProcessedSalaryEarning::whereIn('ps_id', $processedSalary->pluck('ps_id'))
            ->distinct()
            ->pluck('ps_earning_type')
            ->toArray();

        $this->deductionsComponents = ProcessedSalaryDeduction::whereIn('ps_id', $processedSalary->pluck('ps_id'))
            ->distinct()
            ->pluck('ps_deduction_type')
            ->toArray();

        // Build basic info columns
        $this->basicInfoColumns = [
            'S#',
            'Emp Code',
            'Title',
            'Employee Name',
        ];

        if (!empty($this->filters['designation'])) {
            $this->basicInfoColumns[] = 'Designation';
        }
        if (!empty($this->filters['grade'])) {
            $this->basicInfoColumns[] = 'Grade';
        }
        if (!empty($this->filters['branch'])) {
            $this->basicInfoColumns[] = 'Branch';
        }
        if (!empty($this->filters['dealership'])) {
            $this->basicInfoColumns[] = 'Dealership';
        }
        if (!empty($this->filters['department'])) {
            $this->basicInfoColumns[] = 'Department';
        }

        $this->basicInfoColumns = array_merge($this->basicInfoColumns, [
            'Date of Joining',
            'Service Length as on Payroll Date',
            'Payment Mode',
            'Emp Bank Name',
            'Bank IFSC',
            'Account No.'
        ]);

        $this->employeeContactsColumns = [
            'Project',
            'Region',
            'Personal Mobile',
            'Official Mobile',
            'Personal Email',
            'Work Email',
        ];

        $this->daysInfoColumns = [
            'Days In Month',
            'Workable Days',
            'Days Worked',
            'Present Days',
            'Late Count',
            'WeekOffs',
            'UPL'
        ];

        $projectNames = Cache::remember('project_names_map_' . $this->businessId, now()->addHours(6), function () {
            return \App\Models\Project::pluck('ps_name', 'ps_id')->toArray();
        });

        $this->data = $processedSalary->transform(function ($item, $key) use ($employeeSalary, $projectNames) {
            $earningsData = ProcessedSalaryEarning::where('ps_id', $item->ps_id)
                ->pluck('ps_e_amount', 'ps_earning_type')
                ->toArray();
            $deductionsData = ProcessedSalaryDeduction::where('ps_id', $item->ps_id)
                ->pluck('ps_d_amount', 'ps_deduction_type')
                ->toArray();

            $salaryRecords = SalaryEmployeeSalary::where('es_emp_id', $item->ps_emp_id)
                ->where('es_b_id', $item->ps_b_id)
                ->with('salary_earnings.fh_salary_earning_type')
                ->get();

            $empSalaryEarningData = [];
            foreach ($salaryRecords as $record) {
                foreach ($record->salary_earnings as $earning) {
                    $title = optional($earning->fh_salary_earning_type)->sa_title;
                    $amount = $earning->es_e_amount;
                    if ($title) {
                        $empSalaryEarningData[$title] = $amount;
                    }
                }
            }

            $empSalaryDeductionData = SalaryEmployeeDeductions::where('es_d_emp_id', $item->ps_emp_id)
                ->with('fh_salary_deduction_type')
                ->get()
                ->pluck('es_d_amount', 'fh_salary_deduction_type.m_name')
                ->toArray();

            $joiningDate = Carbon::parse($item->employee->emp_date_of_joining);
            $currentDate = Carbon::now();
            $diff = $joiningDate->diff($currentDate);
            $accountNo = $item->employee->emp_bank_account_no;
            $formattedAccountNo = preg_match('/^\d+$/', $accountNo) ? "'" . $accountNo : $accountNo;

            $rowData = [
                'S#' => $key + 1,
                'Emp Code' => $item->employee->emp_code ?? '',
                'Title' => $item->employee->fh_employee_title?->m_name ?? '',
                'Employee Name' => $item->employee->emp_full_name ?? '',
            ];

            // Conditional columns
            if (!empty($this->filters['designation'])) {
                $rowData['Designation'] = $item->employee->fh_designation?->dg_name ?? '';
            }
            if (!empty($this->filters['grade'])) {
                $rowData['Grade'] = $item->employee->fh_grade?->g_name ?? '';
            }
            if (!empty($this->filters['branch'])) {
                $rowData['Branch'] = $item->employee->fh_branch?->br_name ?? $item->employee->emp_branch_id ?? '';
            }
            if (!empty($this->filters['dealership'])) {
                $rowData['Dealership'] = $item->employee->fh_dealership?->dlr_name ?? '';
            }
            if (!empty($this->filters['department'])) {
                $rowData['Department'] = $item->employee->fh_department?->d_name ?? '';
            }

            $rowData['Date of Joining'] = $item->employee->emp_date_of_joining
                ? Carbon::parse($item->employee->emp_date_of_joining)->format('d M, Y')
                : '';
            $rowData['Service Length as on Payroll Date'] = "{$diff->y} y {$diff->m} m {$diff->d} d";
            $rowData['Payment Mode'] = $item->employee->emp_paymentmode ?? '';
            $rowData['Emp Bank Name'] = $item->employee->emp_bank_name ?? '';
            $rowData['Bank IFSC'] = $item->employee->emp_bank_ifsc_code ?? '';
            $rowData['Account No.'] = $formattedAccountNo ?? '';

            // Employee Contacts (conditional)
            if ($this->employeeContactsHeading) {
                $rowData['Project'] = (function () use ($item, $projectNames) {
                    $ids = $item->employee->emp_project_id;
                    if (empty($ids)) return '';
                    if (is_string($ids)) {
                        $ids = json_decode($ids, true);
                    }
                    if (!is_array($ids)) return '';
                    $names = array_filter(array_map(function($id) use ($projectNames) {
                        return $projectNames[$id] ?? 'Deleted Project';
                    }, $ids));
                    return implode(', ', $names);
                })();
                $rowData['Region'] = $item->employee->fh_emp_region?->m_name ?? $item->employee->emp_region_id ?? '';
                $rowData['Personal Mobile'] = $item->employee->emp_phone ?? '';
                $rowData['Official Mobile'] = $item->employee->emp_official_contact ?? '';
                $rowData['Personal Email'] = $item->employee->emp_email ?? '';
                $rowData['Work Email'] = $item->employee->emp_company_email ?? '';
            }

            // Salary Master Earnings (conditional)
            if ($this->employeeEarningsHeading) {
                foreach ($this->employeeSalaryEarningComponents as $comp) {
                    $value = $empSalaryEarningData[$comp] ?? 0;
                    $rowData['Salary Master_' . $comp] = $this->roundOffValues ? round($value) : $value;
                }
                $otherAllowance = $employeeSalary->where('es_emp_id', $item->ps_emp_id)->first()->es_rem_allowance ?? 0;
                $monthlyGross = $employeeSalary->where('es_emp_id', $item->ps_emp_id)->first()->es_monthly_gross ?? 0;
                $ctc = $employeeSalary->where('es_emp_id', $item->ps_emp_id)->first()->es_monthly_ctc ?? 0;
                $rowData['Salary Master_Other Allowance'] = $this->roundOffValues ? round($otherAllowance) : $otherAllowance;
                $rowData['Monthly Gross'] = $this->roundOffValues ? round($monthlyGross) : $monthlyGross;
                $rowData['CTC'] = $this->roundOffValues ? round($ctc) : $ctc;
            }

            // Salary Master Deductions (conditional)
            if ($this->employeeDeductionsHeading) {
                foreach ($this->employeeSalaryDeductionComponents as $comp) {
                    $value = $empSalaryDeductionData[$comp] ?? 0;
                    $rowData['Salary Master_' . $comp] = $this->roundOffValues ? round($value) : $value;
                }
            }

            // Days Info (conditional)
            if ($this->daysInfoHeading) {
                $rowData['Attendance Summary_Days In Month'] = $item->ps_total_days_in_month;
                $rowData['Attendance Summary_Workable Days'] = $item->ps_total_month_working_days;
                $rowData['Attendance Summary_Days Worked'] = $item->ps_total_days_worked;
                $rowData['Attendance Summary_Present Days'] = $item->ps_present_days;
                $rowData['Attendance Summary_Late Count'] = $item->ps_days_late ?? 0;
                $rowData['Attendance Summary_WeekOffs'] = $item->ps_week_off_count;
                $rowData['Attendance Summary_UPL'] = $item->ps_upl_count ?? 0;
            }

            // Earnings (conditional)
            if ($this->earningsHeading) {
                foreach ($this->earningsComponents as $comp) {
                    $value = $earningsData[$comp] ?? 0;
                    $rowData['Earnings_' . $comp] = $this->roundOffValues ? round($value) : $value;
                }
                $monthlyCtc = $item->ps_monthly_ctc ?? 0;
                $grossSalary = $item->ps_monthly_gross ?? 0;
                $rowData['Monthly CTC'] = $this->roundOffValues ? round($monthlyCtc) : $monthlyCtc;
                $rowData['Gross Salary'] = $this->roundOffValues ? round($grossSalary) : $grossSalary;
            }

            // Deductions (conditional)
            if ($this->deductionsHeading) {
                foreach ($this->deductionsComponents as $comp) {
                    $value = $deductionsData[$comp] ?? 0;
                    $rowData['Deductions_' . $comp] = $this->roundOffValues ? round($value) : $value;
                }
            }

            $netPay = $item->ps_monthly_net_salary ?? 0;
            $rowData['Net Pay'] = $this->roundOffValues ? round($netPay) : $netPay;

            return $rowData;
        });
    }

    protected function buildColumnHeaders()
    {
        // Ensure basicInfoColumns is an array
        if (!is_array($this->basicInfoColumns)) {
            $this->basicInfoColumns = [];
        }

        $headers = $this->basicInfoColumns;

        if ($this->employeeContactsHeading) {
            if (is_array($this->employeeContactsColumns)) {
                $headers = array_merge($headers, $this->employeeContactsColumns);
            }
        }

        if ($this->employeeEarningsHeading) {
            if (is_array($this->employeeSalaryEarningComponents)) {
                foreach ($this->employeeSalaryEarningComponents as $col) {
                    $headers[] = 'Salary Master_' . $col;
                }
            }
            $headers[] = 'Salary Master_Other Allowance';
            $headers[] = 'Monthly Gross';
            $headers[] = 'CTC';
        }

        if ($this->employeeDeductionsHeading) {
            if (is_array($this->employeeSalaryDeductionComponents)) {
                foreach ($this->employeeSalaryDeductionComponents as $col) {
                    $headers[] = 'Salary Master_' . $col;
                }
            }
        }

        if ($this->daysInfoHeading) {
            if (is_array($this->daysInfoColumns)) {
                foreach ($this->daysInfoColumns as $col) {
                    $headers[] = 'Attendance Summary_' . $col;
                }
            }
        }

        if ($this->earningsHeading) {
            if (is_array($this->earningsComponents)) {
                foreach ($this->earningsComponents as $col) {
                    $headers[] = 'Earnings_' . $col;
                }
            }
            $headers[] = 'Monthly CTC';
            $headers[] = 'Gross Salary';
        }

        if ($this->deductionsHeading) {
            if (is_array($this->deductionsComponents)) {
                foreach ($this->deductionsComponents as $col) {
                    $headers[] = 'Deductions_' . $col;
                }
            }
        }

        $headers[] = 'Net Pay';
        $this->columnHeaders = $headers;
    }

    public function title(): string
    {
        return 'Payroll Register';
    }

    public function collection()
    {
        if ($this->data->isEmpty()) {
            return collect([['No data available for the selected payroll period']]);
        }

        $headers = $this->columnHeaders;
        $rows = collect();

        foreach ($this->data as $item) {
            $row = [];
            foreach ($headers as $header) {
                $row[] = $item[$header] ?? 0;
            }
            $rows->push($row);
        }

        $grand = [];
        $nonSumColumns = $this->basicInfoColumns;

        if ($this->employeeContactsHeading) {
            $nonSumColumns = array_merge($nonSumColumns, $this->employeeContactsColumns);
        }

        foreach ($headers as $header) {
            if (in_array($header, $nonSumColumns)) {
                $grand[] = $header === 'S#' ? 'Grand Totals' : '';
                continue;
            }
            $sum = $this->data->sum(function($i) use ($header) {
                return is_numeric($i[$header] ?? 0) ? (float)$i[$header] : 0;
            });
            $grand[] = $this->roundOffValues ? round($sum) : $sum;
        }

        $rows->push($grand);
        return $rows;
    }

    public function headings(): array
    {
        if ($this->data->isEmpty()) {
            return [['No Data']];
        }

        // Ensure column headers are built
        if (empty($this->columnHeaders)) {
            $this->buildColumnHeaders();
        }

        // Initialize groupRow with first column
        $groupRow = ['Employee Details'];
        
        // Fill remaining positions for basic info columns
        $basicInfoCount = is_array($this->basicInfoColumns) ? count($this->basicInfoColumns) : 0;
        for ($i = 1; $i < $basicInfoCount; $i++) {
            $groupRow[] = '';
        }

        $currentPosition = $basicInfoCount;

        // Employee Contacts Section
        if ($this->employeeContactsHeading) {
            $contactsCount = is_array($this->employeeContactsColumns) ? count($this->employeeContactsColumns) : 0;
            
            // Ensure array has enough elements
            while (count($groupRow) <= $currentPosition) {
                $groupRow[] = '';
            }
            $groupRow[$currentPosition] = $this->employeeContactsHeading;
            
            for ($i = 1; $i < $contactsCount; $i++) {
                $pos = $currentPosition + $i;
                while (count($groupRow) <= $pos) {
                    $groupRow[] = '';
                }
                $groupRow[$pos] = '';
            }
            $currentPosition += $contactsCount;
        }

        // Employee Earnings Section
        if ($this->employeeEarningsHeading) {
            $earningsCount = (is_array($this->employeeSalaryEarningComponents) ? count($this->employeeSalaryEarningComponents) : 0) + 3;
            
            while (count($groupRow) <= $currentPosition) {
                $groupRow[] = '';
            }
            $groupRow[$currentPosition] = $this->employeeEarningsHeading;
            
            for ($i = 1; $i < $earningsCount; $i++) {
                $pos = $currentPosition + $i;
                while (count($groupRow) <= $pos) {
                    $groupRow[] = '';
                }
                $groupRow[$pos] = '';
            }
            $currentPosition += $earningsCount;
        }

        // Employee Deductions Section
        if ($this->employeeDeductionsHeading) {
            $deductionsCount = is_array($this->employeeSalaryDeductionComponents) ? count($this->employeeSalaryDeductionComponents) : 0;
            
            if ($deductionsCount > 0) {
                while (count($groupRow) <= $currentPosition) {
                    $groupRow[] = '';
                }
                $groupRow[$currentPosition] = $this->employeeDeductionsHeading;
                
                for ($i = 1; $i < $deductionsCount; $i++) {
                    $pos = $currentPosition + $i;
                    while (count($groupRow) <= $pos) {
                        $groupRow[] = '';
                    }
                    $groupRow[$pos] = '';
                }
                $currentPosition += $deductionsCount;
            }
        }

        // Days Info Section
        if ($this->daysInfoHeading) {
            $daysCount = is_array($this->daysInfoColumns) ? count($this->daysInfoColumns) : 0;
            
            while (count($groupRow) <= $currentPosition) {
                $groupRow[] = '';
            }
            $groupRow[$currentPosition] = $this->daysInfoHeading;
            
            for ($i = 1; $i < $daysCount; $i++) {
                $pos = $currentPosition + $i;
                while (count($groupRow) <= $pos) {
                    $groupRow[] = '';
                }
                $groupRow[$pos] = '';
            }
            $currentPosition += $daysCount;
        }

        // Earnings Section (Processed)
        if ($this->earningsHeading) {
            $processedEarningsCount = (is_array($this->earningsComponents) ? count($this->earningsComponents) : 0) + 2;
            
            while (count($groupRow) <= $currentPosition) {
                $groupRow[] = '';
            }
            $groupRow[$currentPosition] = $this->earningsHeading;
            
            for ($i = 1; $i < $processedEarningsCount; $i++) {
                $pos = $currentPosition + $i;
                while (count($groupRow) <= $pos) {
                    $groupRow[] = '';
                }
                $groupRow[$pos] = '';
            }
            $currentPosition += $processedEarningsCount;
        }

        // Deductions Section (Processed)
        if ($this->deductionsHeading) {
            $processedDeductionsCount = is_array($this->deductionsComponents) ? count($this->deductionsComponents) : 0;
            
            if ($processedDeductionsCount > 0) {
                while (count($groupRow) <= $currentPosition) {
                    $groupRow[] = '';
                }
                $groupRow[$currentPosition] = $this->deductionsHeading;
                
                for ($i = 1; $i < $processedDeductionsCount; $i++) {
                    $pos = $currentPosition + $i;
                    while (count($groupRow) <= $pos) {
                        $groupRow[] = '';
                    }
                    $groupRow[$pos] = '';
                }
                $currentPosition += $processedDeductionsCount;
            }
        }

        // Net Pay Section
        while (count($groupRow) <= $currentPosition) {
            $groupRow[] = '';
        }
        $groupRow[$currentPosition] = 'Net Pay';

        // Ensure groupRow has the same number of elements as columnHeaders
        $columnHeadersCount = is_array($this->columnHeaders) ? count($this->columnHeaders) : 0;
        $groupRowCount = is_array($groupRow) ? count($groupRow) : 0;
        
        while ($groupRowCount < $columnHeadersCount) {
            $groupRow[] = '';
            $groupRowCount++;
        }

        // Clean headers for second row
        $clean = [];
        if (is_array($this->columnHeaders)) {
            $clean = array_map(function($h) {
                return preg_replace('/^(Salary Master_|Attendance Summary_|Earnings_|Deductions_)/', '', $h);
            }, $this->columnHeaders);
        }

        return [$groupRow, $clean];
    }

    public function startCell(): string
    {
        return 'A6';
    }

    public function columnWidths(): array
    {
        $widths = [];
        if (is_array($this->columnHeaders)) {
            foreach ($this->columnHeaders as $idx => $header) {
                $colLetter = Coordinate::stringFromColumnIndex($idx + 1);
                if ($header === 'S#' || $header === 'Emp Code') {
                    $widths[$colLetter] = 7;
                } elseif (str_contains($header, 'Project') || str_contains($header, 'Employee Name') || str_contains($header, 'Bank Name')) {
                    $widths[$colLetter] = 20;
                } elseif (str_contains($header, 'Account No.')) {
                    $widths[$colLetter] = 18;
                } else {
                    $widths[$colLetter] = 13;
                }
            }
        }
        return $widths;
    }

    public function registerEvents(): array
    {
        if ($this->data->isEmpty()) {
            return [];
        }

        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestColumn = $sheet->getHighestColumn();
                $highestRow = $sheet->getHighestRow();
                $printed = Carbon::now()->format('d-M-Y h:i A T');

                // Header styling
                foreach (range(1, 4) as $r) {
                    $sheet->mergeCells("A{$r}:{$highestColumn}{$r}");
                    $sheet->getStyle("A{$r}")->getFont()->setBold(true)->setSize(11);
                    $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                $sheet->setCellValue('A1', $this->businessName);
                $sheet->setCellValue('A2', 'Salary Register');
                $sheet->setCellValue('A3', "For the month of {$this->monthName}");
                $sheet->setCellValue('A4', "Printed on: {$printed}");

                if ($this->roundOffValues) {
                    $sheet->setCellValue('A5', "Note: All monetary values are rounded to nearest whole number");
                    $sheet->mergeCells("A5:{$highestColumn}5");
                    $sheet->getStyle("A5")->getFont()->setSize(10)->setBold(true)->setItalic(true);
                    $sheet->getStyle("A5")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // Section merging logic
                $currentCol = 1;
                $sections = [];

                $basicEnd = is_array($this->basicInfoColumns) ? count($this->basicInfoColumns) : 0;
                $sections[] = ['start' => $currentCol, 'end' => $basicEnd];
                $currentCol = $basicEnd + 1;

                if ($this->employeeContactsHeading) {
                    $contactsCount = is_array($this->employeeContactsColumns) ? count($this->employeeContactsColumns) : 0;
                    $contactsEnd = $currentCol + $contactsCount - 1;
                    $sections[] = ['start' => $currentCol, 'end' => $contactsEnd];
                    $currentCol = $contactsEnd + 1;
                }

                if ($this->employeeEarningsHeading) {
                    $earningsCount = (is_array($this->employeeSalaryEarningComponents) ? count($this->employeeSalaryEarningComponents) : 0) + 3;
                    $earningsEnd = $currentCol + $earningsCount - 1;
                    $sections[] = ['start' => $currentCol, 'end' => $earningsEnd];
                    $currentCol = $earningsEnd + 1;
                }

                if ($this->employeeDeductionsHeading) {
                    $deductionsCount = is_array($this->employeeSalaryDeductionComponents) ? count($this->employeeSalaryDeductionComponents) : 0;
                    if ($deductionsCount > 0) {
                        $deductionsEnd = $currentCol + $deductionsCount - 1;
                        $sections[] = ['start' => $currentCol, 'end' => $deductionsEnd];
                        $currentCol = $deductionsEnd + 1;
                    }
                }

                if ($this->daysInfoHeading) {
                    $daysCount = is_array($this->daysInfoColumns) ? count($this->daysInfoColumns) : 0;
                    $daysEnd = $currentCol + $daysCount - 1;
                    $sections[] = ['start' => $currentCol, 'end' => $daysEnd];
                    $currentCol = $daysEnd + 1;
                }

                if ($this->earningsHeading) {
                    $processedEarningsCount = (is_array($this->earningsComponents) ? count($this->earningsComponents) : 0) + 2;
                    $processedEarningsEnd = $currentCol + $processedEarningsCount - 1;
                    $sections[] = ['start' => $currentCol, 'end' => $processedEarningsEnd];
                    $currentCol = $processedEarningsEnd + 1;
                }

                if ($this->deductionsHeading) {
                    $processedDeductionsCount = is_array($this->deductionsComponents) ? count($this->deductionsComponents) : 0;
                    if ($processedDeductionsCount > 0) {
                        $processedDeductionsEnd = $currentCol + $processedDeductionsCount - 1;
                        $sections[] = ['start' => $currentCol, 'end' => $processedDeductionsEnd];
                        $currentCol = $processedDeductionsEnd + 1;
                    }
                }

                $sections[] = ['start' => $currentCol, 'end' => $currentCol];

                foreach ($sections as $s) {
                    if ($s['start'] !== $s['end']) {
                        $start = Coordinate::stringFromColumnIndex($s['start']);
                        $end = Coordinate::stringFromColumnIndex($s['end']);
                        $sheet->mergeCells("{$start}6:{$end}6");
                    }
                }

                // Apply styles
                $sheet->getStyle("A6:{$highestColumn}7")
                    ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('263871');
                $sheet->getStyle("A6:{$highestColumn}7")
                    ->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('FFFFFF');
                $sheet->getStyle("A6:{$highestColumn}7")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);

                $sheet->freezePane('E8');
                $sheet->setShowGridlines(false);

                $dataRange = "A8:{$highestColumn}{$highestRow}";
                $sheet->getStyle($dataRange)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setWrapText(true);
                $sheet->getStyle($dataRange)->getFont()->setSize(8);

                for ($r = 8; $r <= $highestRow; $r++) {
                    $sheet->getRowDimension($r)->setRowHeight(25);
                }

                $sheet->getStyle("A6:{$highestColumn}{$highestRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->getColor()->setRGB('D3D3D3');

                $numEmp = $this->data->count();
                for ($i = 0; $i < $numEmp; $i++) {
                    $row = 8 + $i;
                    $color = $i % 2 === 0 ? 'F5F5F5' : 'FFFFFF';
                    $sheet->getStyle("A{$row}:{$highestColumn}{$row}")
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB($color);
                }

                $grandRow = 8 + $numEmp;
                $sheet->getStyle("A{$grandRow}:{$highestColumn}{$grandRow}")
                    ->getFont()->setBold(true)->setSize(9);
                $sheet->getStyle("A{$grandRow}:{$highestColumn}{$grandRow}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('FFD9D9D9');
                $sheet->getStyle("A{$grandRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);

                foreach (range(1, 4) as $r) {
                    $sheet->getRowDimension($r)->setRowHeight(20);
                }
                $sheet->getRowDimension(6)->setRowHeight(15);
                $sheet->getRowDimension(7)->setRowHeight(30);
            }
        ];
    }
}