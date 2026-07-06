<?php
namespace App\Livewire\WeeklyPayroll;

use App\Livewire\WeeklyPayroll\Concerns\ResolvesWeeklyPayrollReportPeriod;
use App\Models\AttendanceSummary;
use App\Models\PayrollPeriod;
use App\Models\ProcessedEmployeeSalary;
use App\Models\ProcessedSalaryDeduction;
use App\Models\ProcessedSalaryEarning;
use App\Models\SalaryEmployeeDeductions;
use App\Models\SalaryEmployeeEarnings;
use App\Models\SalaryEmployeeSalary;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
class PayrollRegisterReport extends Component
{
    use ResolvesWeeklyPayrollReportPeriod;

    public $roundOffValues = false;
    public $selectedPayrollPeriodId;
    public $businessId;

        public $selectedPreEarningHeading;
    public $selectedPostEarningHeading;
    public $selectedNetPayHeading;

    public $selectedEmployeeContactsHeading;
    public $selectedEmployeeEarningsHeading = 'Salary Master';
    public $selectedEmployeeDeductionsHeading = '';
    public $selectedDaysInfoHeading = 'Attendance Summary';
    public $selectedEarningHeading = 'Earning Components';
    public $selectedDeductionHeading = 'Deduction Components';

    /** @var int|null */
    public $selectedWeekId;

    public $showButton = true;

    public $buttonStyle = '';

    public function mount($payrollId = null, $weekId = null)
    {
        $user = Auth::user();
        $this->businessId = $user->emp_b_id;
        $this->selectedPayrollPeriodId = $payrollId;
        $this->selectedWeekId = $weekId;
    }
    public function generateReport()
    {
        if (! $this->validatePayrollReportRequest([
            'selectedPayrollPeriodId' => 'required|exists:payroll_periods,pp_id',
        ], [
            'selectedPayrollPeriodId.required' => 'Payroll period is required.',
        ])) {
            return;
        }
        if (! $this->resolvePayrollForWeeklyReport()) {
            return;
        }

        $weekEmployeeIds = [];
        if ($this->selectedWeekId) {
            $weekEmployeeIds = AttendanceSummary::query()
                ->where('as_pp_id', $this->selectedPayrollPeriodId)
                ->where('as_week_id', $this->selectedWeekId)
                ->where('as_b_id', $this->businessId)
                ->pluck('as_emp_id')
                ->unique()
                ->values()
                ->toArray();
        }

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
            ->where('ps_payroll_id', $this->selectedPayrollPeriodId)
            ->where('ps_b_id', $this->businessId)
            ->when($this->selectedWeekId, function ($q) use ($weekEmployeeIds) {
                $q->where(function ($weekScoped) use ($weekEmployeeIds) {
                    $weekScoped->where('ps_week_id', $this->selectedWeekId)
                        ->orWhere(function ($legacyWeekScope) use ($weekEmployeeIds) {
                            $legacyWeekScope->where(function ($legacyNullOrZero) {
                                $legacyNullOrZero->whereNull('ps_week_id')
                                    ->orWhere('ps_week_id', 0);
                            });

                            if (! empty($weekEmployeeIds)) {
                                $legacyWeekScope->whereIn('ps_emp_id', $weekEmployeeIds);
                            } else {
                                $legacyWeekScope->whereRaw('1=0');
                            }
                        });
                });
            });

        $processedSalary = $query->get();
        if ($processedSalary->isEmpty()) {
            $this->payrollReportSwalNoData('There is no data to download for the payroll register. No processed salary rows match this selection.');

            return;
        }
        $employeeSalary = SalaryEmployeeSalary::with('fh_employee')
            ->where('es_b_id', $this->businessId)
            ->whereIn('es_emp_id', $processedSalary->pluck('ps_emp_id'))
            ->get();
        $employeeSalaryDeductionComponents = SalaryEmployeeDeductions::with('fh_salary_deduction_type')
            ->whereIn('es_d_emp_id', $employeeSalary->pluck('es_emp_id')->toArray())
            ->get()
            ->pluck('fh_salary_deduction_type.m_name')
            ->unique()
            ->values()
            ->toArray();
        $employeeSalaryEarningComponents = SalaryEmployeeEarnings::with('fh_salary_earning_type')
            ->whereIn('es_e_emp_id', $employeeSalary->pluck('es_emp_id')->toArray())
            ->get()
            ->pluck('fh_salary_earning_type.sa_title')
            ->unique()
            ->values()
            ->toArray();
        $earningsComponents = ProcessedSalaryEarning::whereIn('ps_id', $processedSalary->pluck('ps_id'))
            ->distinct()->pluck('ps_earning_type')->toArray();
        $deductionsComponents = ProcessedSalaryDeduction::whereIn('ps_id', $processedSalary->pluck('ps_id'))
            ->distinct()->pluck('ps_deduction_type')->toArray();
        // Step 1: Build dynamic columns properly
        $basicInfoColumns = [
            'S#',
            'Emp Code',
            'Title',
            'Employee Name',
        ];
        // Conditionally add optional columns based on filters
        if (!empty($this->filter['designation'])) {
            $basicInfoColumns[] = 'Designation';
        }
        if (!empty($this->filter['grade'])) {
            $basicInfoColumns[] = 'Grade';
        }
        if (!empty($this->filter['branch'])) {
            $basicInfoColumns[] = 'Branch';
        }
        if (!empty($this->filter['dealership'])) {
            $basicInfoColumns[] = 'Dealership';
        }
        if (!empty($this->filter['department'])) {
            $basicInfoColumns[] = 'Department';
        }
        // Add personal banking columns to Employee Details
        $basicInfoColumns = array_merge($basicInfoColumns, [
            'Date of Joining',
            'Service Length as on Payroll Date',
            'Payment Mode',
            'Emp Bank Name',
            'Bank IFSC',
            'Account No.'
        ]);
        // Employee Contacts Group - INCLUDES Project and Region
        $employeeContactsColumns = [
            'Project',
            'Region',
            'Personal Mobile',
            'Official Mobile',
            'Personal Email',
            'Work Email',
        ];
        $daysInfoColumns = [
            'Days In Month',
            'Workable Days',
            'Days Worked',
            'Present Days',
            'Late Count',
            'WeekOffs',
            'UPL'
        ];
        $projectNames = cache()->remember('project_names_map_' . auth()->user()->business_id ?? 'global', now()->addHours(6), function () {
            return \App\Models\Project::pluck('ps_name', 'ps_id')->toArray();
        });
        $processedSalary = $processedSalary->transform(function ($item, $key) use (
            $earningsComponents,
            $deductionsComponents,
            $employeeSalaryEarningComponents,
            $employeeSalaryDeductionComponents,
            $employeeSalary,
            $projectNames
        ) {
            $earningsData = ProcessedSalaryEarning::where('ps_id', $item->ps_id)
                ->pluck('ps_e_amount', 'ps_earning_type')->toArray();
            $deductionsData = ProcessedSalaryDeduction::where('ps_id', $item->ps_id)
                ->pluck('ps_d_amount', 'ps_deduction_type')->toArray();
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
            $formattedAccountNo = preg_match('/^\d+$/', $accountNo) ? "'$accountNo" : $accountNo;
            $rowData = [
                'S#'                        => $key + 1,
                'Emp Code'                  => $item->employee->emp_code ?? '',
                'Title'                     => $item->employee->fh_employee_title?->m_name ?? '',
                'Employee Name'             => $item->employee->emp_full_name ?? '',
            ];
            // Conditional columns
            if (!empty($this->filter['designation'])) {
                $rowData['Designation'] = $item->employee->fh_designation?->dg_name ?? '';
            }
            if (!empty($this->filter['grade'])) {
                $rowData['Grade'] = $item->employee->fh_grade?->g_name ?? '';
            }
            if (!empty($this->filter['branch'])) {
                $rowData['Branch'] = $item->employee->fh_branch?->br_name ?? $item->employee->emp_branch_id ?? '';
            }
            if (!empty($this->filter['dealership'])) {
                $rowData['Dealership'] = $item->employee->fh_dealership?->dlr_name ?? '';
            }
            if (!empty($this->filter['department'])) {
                $rowData['Department'] = $item->employee->fh_department?->d_name ?? '';
            }
            // Personal banking columns (now part of Employee Details)
            $rowData['Date of Joining'] = $item->employee->emp_date_of_joining
                ? Carbon::parse($item->employee->emp_date_of_joining)->format('d M, Y')
                : '';
            $rowData['Service Length as on Payroll Date'] = "{$diff->y} y {$diff->m} m {$diff->d} d";
            $rowData['Payment Mode'] = ucfirst($item->employee->emp_paymentmode ?? '');
            $rowData['Emp Bank Name'] = $item->employee->emp_bank_name ?? '';
            $rowData['Bank IFSC'] = $item->employee->emp_bank_ifsc_code ?? '';
            $rowData['Account No.'] = $formattedAccountNo ?? '';
            // Employee Contacts (conditional) - INCLUDES Project and Region
            if ($this->selectedEmployeeContactsHeading) {
                $rowData['Project'] = (function () use ($item, $projectNames) {
                    $ids = $item->employee->emp_project_id;
                    if (empty($ids)) return '';
                    if (is_string($ids)) {
                        $ids = json_decode($ids, true);
                    }
                    if (!is_array($ids)) return '';
                    $names = array_filter(array_map(fn($id) => $projectNames[$id] ?? 'Deleted Project', $ids));
                    return implode(', ', $names);
                })();
                $rowData['Region'] = $item->employee->fh_emp_region?->m_name ?? $item->employee->emp_region_id ?? '';
                $rowData['Personal Mobile'] = $item->employee->emp_phone ?? '';
                $rowData['Official Mobile'] = $item->employee->emp_official_contact ?? '';
                $rowData['Personal Email'] = $item->employee->emp_email ?? '';
                $rowData['Work Email'] = $item->employee->emp_company_email ?? '';
            }
            // Salary Master Earnings (conditional)
            if ($this->selectedEmployeeEarningsHeading) {
                foreach ($employeeSalaryEarningComponents as $comp) {
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
            if ($this->selectedEmployeeDeductionsHeading) {
                foreach ($employeeSalaryDeductionComponents as $comp) {
                    $value = $empSalaryDeductionData[$comp] ?? 0;
                    $rowData['Salary Master_' . $comp] = $this->roundOffValues ? round($value) : $value;
                }
            }
            // Days Info (conditional)
            if ($this->selectedDaysInfoHeading) {
                $rowData['Attendance Summary_Days In Month'] = $item->ps_total_days_in_month;
                $rowData['Attendance Summary_Workable Days'] = $item->ps_total_month_working_days;
                $rowData['Attendance Summary_Days Worked'] = $item->ps_total_days_worked;
                $rowData['Attendance Summary_Present Days'] = $item->ps_present_days;
                $rowData['Attendance Summary_Late Count'] = $item->ps_days_late ?? 0;
                $rowData['Attendance Summary_WeekOffs'] = $item->ps_week_off_count;
                $rowData['Attendance Summary_UPL'] = $item->ps_upl_count ?? 0;
            }
            // Earnings (conditional)
            if ($this->selectedEarningHeading) {
                foreach ($earningsComponents as $comp) {
                    $value = $earningsData[$comp] ?? 0;
                    $rowData['Earnings_' . $comp] = $this->roundOffValues ? round($value) : $value;
                }
                $monthlyCtc = $item->ps_monthly_ctc ?? 0;
                $grossSalary = $item->ps_monthly_gross ?? 0;
                $rowData['Monthly CTC'] = $this->roundOffValues ? round($monthlyCtc) : $monthlyCtc;
                $rowData['Gross Salary'] = $this->roundOffValues ? round($grossSalary) : $grossSalary;
            }
            // Deductions (conditional)
            if ($this->selectedDeductionHeading) {
                foreach ($deductionsComponents as $comp) {
                    $value = $deductionsData[$comp] ?? 0;
                    $rowData['Deductions_' . $comp] = $this->roundOffValues ? round($value) : $value;
                }
            }
            $netPay = $item->ps_monthly_net_salary ?? 0;
            $rowData['Net Pay'] = $this->roundOffValues ? round($netPay) : $netPay;
            return $rowData;
        });
        $user = Auth::user();
        $businessName = $user->fh_business->b_name ?? 'Business';
        $monthName = optional(PayrollPeriod::find($this->selectedPayrollPeriodId))->pp_name ?? 'Month';
        $employeeContactsHeading = $this->selectedEmployeeContactsHeading;
        $employeeSalaryEarningsHeading = $this->selectedEmployeeEarningsHeading;
        $employeeSalaryDeductionHeading = $this->selectedEmployeeDeductionsHeading;
        $earningsHeading = $this->selectedEarningHeading;
        $deductionsHeading = $this->selectedDeductionHeading;
        $daysInfoHeading = $this->selectedDaysInfoHeading;
        return Excel::download(new class(
            $processedSalary,
            $businessName,
            $monthName,
            $employeeContactsHeading,
            $employeeSalaryEarningsHeading,
            $employeeSalaryDeductionHeading,
            $earningsHeading,
            $deductionsHeading,
            $employeeSalaryEarningComponents,
            $employeeSalaryDeductionComponents,
            $earningsComponents,
            $deductionsComponents,
            $basicInfoColumns,
            $employeeContactsColumns,
            $daysInfoColumns,
            $daysInfoHeading,
            $this->roundOffValues // Pass roundOffValues to export class
        ) implements FromCollection, WithHeadings, WithEvents, WithCustomStartCell, WithColumnWidths {
            use Exportable;
            protected $data;
            protected $businessName;
            protected $monthName;
            protected $employeeContactsHeading;
            protected $employeeSalaryEarningsHeading;
            protected $employeeSalaryDeductionHeading;
            protected $earningsHeading;
            protected $deductionsHeading;
            protected $employeeSalaryEarningComponents;
            protected $employeeSalaryDeductionComponents;
            protected $earningsComponents;
            protected $deductionsComponents;
            protected $basicInfoColumns;
            protected $employeeContactsColumns;
            protected $daysInfoColumns;
            protected $daysInfoHeading;
            protected $roundOffValues;
            protected $columnHeaders;
            public function __construct(
                $data,
                $businessName,
                $monthName,
                $employeeContactsHeading,
                $employeeSalaryEarningsHeading,
                $employeeSalaryDeductionHeading,
                $earningsHeading,
                $deductionsHeading,
                $employeeSalaryEarningComponents,
                $employeeSalaryDeductionComponents,
                $earningsComponents,
                $deductionsComponents,
                $basicInfoColumns,
                $employeeContactsColumns,
                $daysInfoColumns,
                $daysInfoHeading,
                $roundOffValues
            ) {
                $this->data = $data;
                $this->businessName = $businessName;
                $this->monthName = $monthName;
                $this->employeeContactsHeading = $employeeContactsHeading;
                $this->employeeSalaryEarningsHeading = $employeeSalaryEarningsHeading;
                $this->employeeSalaryDeductionHeading = $employeeSalaryDeductionHeading;
                $this->earningsHeading = $earningsHeading;
                $this->deductionsHeading = $deductionsHeading;
                $this->employeeSalaryEarningComponents = $employeeSalaryEarningComponents;
                $this->employeeSalaryDeductionComponents = $employeeSalaryDeductionComponents;
                $this->earningsComponents = $earningsComponents;
                $this->deductionsComponents = $deductionsComponents;
                $this->basicInfoColumns = $basicInfoColumns;
                $this->employeeContactsColumns = $employeeContactsColumns;
                $this->daysInfoColumns = $daysInfoColumns;
                $this->daysInfoHeading = $daysInfoHeading;
                $this->roundOffValues = $roundOffValues;
                $this->columnHeaders = $this->buildColumnHeaders();
            }
            protected function buildColumnHeaders()
            {
                $headers = $this->basicInfoColumns;
                // Only add employee contacts if heading is enabled - INCLUDES Project and Region
                if ($this->employeeContactsHeading) {
                    $headers = array_merge($headers, $this->employeeContactsColumns);
                }
                if ($this->employeeSalaryEarningsHeading) {
                    foreach ($this->employeeSalaryEarningComponents as $col) {
                        $headers[] = 'Salary Master_' . $col;
                    }
                    $headers[] = 'Salary Master_Other Allowance';
                    $headers[] = 'Monthly Gross';
                    $headers[] = 'CTC';
                }
                if ($this->employeeSalaryDeductionHeading) {
                    foreach ($this->employeeSalaryDeductionComponents as $col) {
                        $headers[] = 'Salary Master_' . $col;
                    }
                }
                if ($this->daysInfoHeading) {
                    foreach ($this->daysInfoColumns as $col) {
                        $headers[] = 'Attendance Summary_' . $col;
                    }
                }
                if ($this->earningsHeading) {
                    foreach ($this->earningsComponents as $col) {
                        $headers[] = 'Earnings_' . $col;
                    }
                    $headers[] = 'Monthly CTC';
                    $headers[] = 'Gross Salary';
                }
                if ($this->deductionsHeading) {
                    foreach ($this->deductionsComponents as $col) {
                        $headers[] = 'Deductions_' . $col;
                    }
                }
                $headers[] = 'Net Pay';
                return $headers;
            }
            public function collection()
            {
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
                foreach ($headers as $header) {
                    // Updated to handle conditional employee contacts
                    $nonSumColumns = array_merge(
                        $this->basicInfoColumns,
                        $this->employeeContactsHeading ? $this->employeeContactsColumns : []
                    );
                    if (in_array($header, $nonSumColumns)) {
                        $grand[] = $header === 'S#' ? 'Grand Totals' : '';
                        continue;
                    }
                    $sum = $this->data->sum(fn($i) => is_numeric($i[$header] ?? 0) ? (float)$i[$header] : 0);
                    $grand[] = $this->roundOffValues ? round($sum) : $sum;
                }
                $rows->push($grand);
                return $rows;
            }
            public function headings(): array
            {
                $groupRow = ['Employee Details'];
                for ($i = 1; $i < count($this->basicInfoColumns); $i++) {
                    $groupRow[] = '';
                }
                // Only add employee contacts header if enabled - INCLUDES Project and Region
                if ($this->employeeContactsHeading) {
                    $groupRow[] = $this->employeeContactsHeading;
                    for ($i = 1; $i < count($this->employeeContactsColumns); $i++) {
                        $groupRow[] = '';
                    }
                }
                // Calculate positions dynamically based on what's actually included
                $currentPosition = count($this->basicInfoColumns)
                    + ($this->employeeContactsHeading ? count($this->employeeContactsColumns) : 0);
                if ($this->employeeSalaryEarningsHeading) {
                    $cnt = count($this->employeeSalaryEarningComponents) + 3;
                    $groupRow[$currentPosition] = $this->employeeSalaryEarningsHeading;
                    for ($i = 1; $i < $cnt; $i++) {
                        $groupRow[$currentPosition + $i] = '';
                    }
                    $currentPosition += $cnt;
                }
                if ($this->employeeSalaryDeductionHeading) {
                    $cnt = count($this->employeeSalaryDeductionComponents);
                    $groupRow[$currentPosition] = $this->employeeSalaryDeductionHeading;
                    for ($i = 1; $i < $cnt; $i++) {
                        $groupRow[$currentPosition + $i] = '';
                    }
                    $currentPosition += $cnt;
                }
                if ($this->daysInfoHeading) {
                    $cnt = count($this->daysInfoColumns);
                    $groupRow[$currentPosition] = $this->daysInfoHeading;
                    for ($i = 1; $i < $cnt; $i++) {
                        $groupRow[$currentPosition + $i] = '';
                    }
                    $currentPosition += $cnt;
                }
                if ($this->earningsHeading) {
                    $cnt = count($this->earningsComponents) + 2;
                    $groupRow[$currentPosition] = $this->earningsHeading;
                    for ($i = 1; $i < $cnt; $i++) {
                        $groupRow[$currentPosition + $i] = '';
                    }
                    $currentPosition += $cnt;
                }
                if ($this->deductionsHeading) {
                    $cnt = count($this->deductionsComponents);
                    $groupRow[$currentPosition] = $this->deductionsHeading;
                    for ($i = 1; $i < $cnt; $i++) {
                        $groupRow[$currentPosition + $i] = '';
                    }
                    $currentPosition += $cnt;
                }
                $groupRow[$currentPosition] = 'Net Pay';
                // Ensure the group row has the same length as column headers
                while (count($groupRow) < count($this->columnHeaders)) {
                    $groupRow[] = '';
                }
                $clean = array_map(fn($h) => preg_replace('/^(Salary Master_|Attendance Summary_|Earnings_|Deductions_)/', '', $h), $this->columnHeaders);
                return [$groupRow, $clean];
            }
            public function startCell(): string
            {
                return 'A6';
            }
            public function columnWidths(): array
            {
                $widths = [];
                foreach ($this->columnHeaders as $idx => $header) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 1);
                    if ($header === 'S#' || $header === 'Emp Code') {
                        $widths[$colLetter] = 7;
                    } elseif ($header === 'Project' || $header === 'Employee Name' || $header === 'Emp Bank Name') {
                        $widths[$colLetter] = 20; // Wider for project names, employee names, and bank names
                    } elseif ($header === 'Account No.') {
                        $widths[$colLetter] = 18; // Wider for account numbers
                    } else {
                        $widths[$colLetter] = 13;
                    }
                }
                return $widths;
            }
            public function registerEvents(): array
            {
                return [
                    AfterSheet::class => function (AfterSheet $event) {
                        $sheet = $event->sheet->getDelegate();
                        $highestColumn = $sheet->getHighestColumn();
                        $highestRow    = $sheet->getHighestRow();
                        $printed       = Carbon::now()->format('d-M-Y h:i A T');
                        foreach (range(1, 4) as $r) {
                            $sheet->mergeCells("A{$r}:{$highestColumn}{$r}");
                            $sheet->getStyle("A{$r}")->getFont()->setBold(true)->setSize(11);
                            $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                        }
                        $sheet->setCellValue('A1', $this->businessName);
                        $sheet->setCellValue('A2', 'Salary Register');
                        $sheet->setCellValue('A3', "For the month of {$this->monthName}");
                        $sheet->setCellValue('A4', "Printed on: {$printed}");
                        // Add round-off info to header if enabled
                        if ($this->roundOffValues) {
                            $sheet->setCellValue('A5', "Note: All monetary values are rounded to nearest whole number");
                            $sheet->mergeCells("A5:{$highestColumn}5");
                            $sheet->getStyle("A5")->getFont()->setSize(10)->setBold(true)->setItalic(true);
                            $sheet->getStyle("A5")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                        }
                        // Dynamic section calculation that respects conditional headings
                        $currentCol = 1; // Start from column A
                        $sections = [];
                        // Basic Info Section (now INCLUDES personal banking columns)
                        $basicEnd = count($this->basicInfoColumns);
                        $sections[] = ['start' => $currentCol, 'end' => $basicEnd];
                        $currentCol = $basicEnd + 1;
                        // Employee Contacts Section (conditional) - INCLUDES Project and Region
                        if ($this->employeeContactsHeading) {
                            $contactsEnd = $currentCol + count($this->employeeContactsColumns) - 1;
                            $sections[] = ['start' => $currentCol, 'end' => $contactsEnd];
                            $currentCol = $contactsEnd + 1;
                        }
                        // Salary Master Earnings (conditional)
                        if ($this->employeeSalaryEarningsHeading) {
                            $earningsCount = count($this->employeeSalaryEarningComponents) + 3;
                            $earningsEnd = $currentCol + $earningsCount - 1;
                            $sections[] = ['start' => $currentCol, 'end' => $earningsEnd];
                            $currentCol = $earningsEnd + 1;
                        }
                        // Salary Master Deductions (conditional)
                        if ($this->employeeSalaryDeductionHeading) {
                            $deductionsCount = count($this->employeeSalaryDeductionComponents);
                            $deductionsEnd = $currentCol + $deductionsCount - 1;
                            $sections[] = ['start' => $currentCol, 'end' => $deductionsEnd];
                            $currentCol = $deductionsEnd + 1;
                        }
                        // Days Info (conditional)
                        if ($this->daysInfoHeading) {
                            $daysCount = count($this->daysInfoColumns);
                            $daysEnd = $currentCol + $daysCount - 1;
                            $sections[] = ['start' => $currentCol, 'end' => $daysEnd];
                            $currentCol = $daysEnd + 1;
                        }
                        // Earnings (conditional)
                        if ($this->earningsHeading) {
                            $processedEarningsCount = count($this->earningsComponents) + 2;
                            $processedEarningsEnd = $currentCol + $processedEarningsCount - 1;
                            $sections[] = ['start' => $currentCol, 'end' => $processedEarningsEnd];
                            $currentCol = $processedEarningsEnd + 1;
                        }
                        // Deductions (conditional)
                        if ($this->deductionsHeading) {
                            $processedDeductionsCount = count($this->deductionsComponents);
                            $processedDeductionsEnd = $currentCol + $processedDeductionsCount - 1;
                            $sections[] = ['start' => $currentCol, 'end' => $processedDeductionsEnd];
                            $currentCol = $processedDeductionsEnd + 1;
                        }
                        // Net Pay Section
                        $sections[] = ['start' => $currentCol, 'end' => $currentCol];
                        // Apply section merging
                        foreach ($sections as $s) {
                            if ($s['start'] !== $s['end']) {
                                $start = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($s['start']);
                                $end   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($s['end']);
                                $sheet->mergeCells("{$start}6:{$end}6");
                            }
                        }
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
                        $sheet->getStyle("T8:{$highestColumn}{$highestRow}")
                            ->getNumberFormat()
                            ->setFormatCode('#,##0.00;[Red]-#,##0.00');
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
                        $legendStart = $grandRow + 2;
                        $sheet->setCellValue("A{$legendStart}", 'Legend / Notes');
                        $sheet->mergeCells("A{$legendStart}:{$highestColumn}{$legendStart}");
                        $sheet->getStyle("A{$legendStart}")
                            ->getFont()->setBold(true)->setSize(10);
                        $sheet->getStyle("A{$legendStart}")
                            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                        $notes = [
                            'Employee Details: Basic information, joining details, and banking information',
                            'Employee Contacts: Project, Region, Personal & official contact details',
                            'Salary Master: Fixed components defined in employee salary structure',
                            'Attendance Summary: Key attendance metrics for the payroll period',
                            'Earnings: Variable or processed earnings calculated and paid this month',
                            'Deductions: Variable or processed deductions applied this month',
                            'Net Pay: Gross Salary minus total Deductions',
                            'Grand Totals: Sum of all employees for respective numeric columns',
                            'All monetary values are in INR (Indian Rupees)'
                        ];
                        // Add round-off note if enabled
                        if ($this->roundOffValues) {
                            $notes[] = 'Note: All monetary values are rounded to nearest whole number';
                        }
                        $row = $legendStart + 1;
                        foreach ($notes as $note) {
                            $sheet->setCellValue("A{$row}", $note);
                            $sheet->mergeCells("A{$row}:{$highestColumn}{$row}");
                            $sheet->getStyle("A{$row}")->getFont()->setSize(9);
                            $sheet->getStyle("A{$row}")->getAlignment()
                                ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                                ->setWrapText(true);
                            $sheet->getRowDimension($row)->setRowHeight(18);
                            $row++;
                        }
                    }
                ];
            }
        }, 'payroll_report_sheet.xlsx');
    }
    public function render()
    {
        return view('livewire.weekly-payroll.payroll-register-report');
    }
}
