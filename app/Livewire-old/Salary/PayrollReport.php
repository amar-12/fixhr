<?php

namespace App\Livewire\Salary;

use Livewire\Component;
use App\Models\Business;
use App\Models\Employee;
use App\Models\FinancialYear;
use App\Models\MasterTable;
use App\Models\PayrollPeriod;
use Illuminate\Support\Carbon;
use App\Models\SalaryEmployeeSalary;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\ProcessedSalaryEarning;
use App\Models\SalaryEmployeeEarnings;
use App\Models\ProcessedEmployeeSalary;
use App\Models\ProcessedSalaryDeduction;
use App\Models\SalaryEmployeeDeductions;
// Excel Concerns
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

class PayrollReport extends Component
{
    public $search = '';
    public $selectedEmployeeId;
    public $employeeStatusId = null;
    public $selectedPayrollPeriodId;
    public $searchPayroll = '';
    public $businessId;
    public $selectedFYId;
    public $searchFY = '';
    public $paymentMode = '';
    public $selectedEarningHeading;
    public $selectedDeductionHeading;
    public $selectedDaysInfoHeading;
    public $selectedEmployeeEarningsHeading;
    public $selectedEmployeeDeductionsHeading;
    public $selectedPreEarningHeading;
    public $selectedPostEarningHeading;
    public $selectedNetPayHeading;
    public $filters = [
        'employeeEarnings' => false,
        'employeeDeductions' => false,
        'daysInfo' => false,
        'earningComponents' => false,
        'deductionComponents' => false,
    ];
    public $preventCollapse = false;
    public function mount()
    {
        $user = Auth::user();
        $this->businessId = $user->emp_b_id;
        $currentFY = FinancialYear::where('fy_b_id', $user->emp_b_id)
            ->where('fy_is_current', 1)
            ->first();
        if ($currentFY) {
            $this->selectFY($currentFY->fy_id, $currentFY->fy_year);
        }
    }
    public function selectEmployee($id, $name)
    {
        $this->selectedEmployeeId = $id;
        $this->search = $name;
    }
    public function selectPayrollPeriod($id, $name)
    {
        $this->selectedPayrollPeriodId = $id;
        $this->searchPayroll = $name;
    }
    public function selectFY($id, $name)
    {
        $this->selectedFYId = $id;
        $this->searchFY = $name;
    }
    public function updated($property, $value)
    {
        if ($property === 'searchFY') {
            $this->selectedPayrollPeriodId = null;
            $this->selectedFYId = null;
            $this->searchPayroll = '';
            return;
        }
        if ($property === 'employeeStatusId') {
            $this->selectedEmployeeId = null;
            $this->search = '';
            return;
        }
        $mapping = [
            'search' => 'selectedEmployeeId',
            'searchPayroll' => 'selectedPayrollPeriodId',
            'searchFY' => 'selectedFYId',
        ];
        if (array_key_exists($property, $mapping)) {
            $this->{$mapping[$property]} = null;
        }
    }
    public function toggleFilter($key, $state)
    {
        if (!array_key_exists($key, $this->filters)) return;
        $this->filters[$key] = filter_var($state, FILTER_VALIDATE_BOOLEAN);
        if ($this->filters[$key]) {
            match ($key) {
                'employeeEarnings' => $this->selectedEmployeeEarningsHeading = 'Salary Master',
                'employeeDeductions' => $this->selectedEmployeeDeductionsHeading = '',
                'daysInfo' => $this->selectedDaysInfoHeading = 'Attendance Summary',
                'earningComponents' => $this->selectedEarningHeading = 'Earning Components',
                'deductionComponents' => $this->selectedDeductionHeading = 'Deduction Components',
                default => null,
            };
        } else {
            match ($key) {
                'employeeEarnings' => $this->selectedEmployeeEarningsHeading = null,
                'employeeDeductions' => $this->selectedEmployeeDeductionsHeading = null,
                'daysInfo' => $this->selectedDaysInfoHeading = null,
                'earningComponents' => $this->selectedEarningHeading = null,
                'deductionComponents' => $this->selectedDeductionHeading = null,
                'preEarning' => $this->selectedPreEarningHeading = null,
                'postEarning' => $this->selectedPostEarningHeading = null,
                'netPay' => $this->selectedNetPayHeading = null,
                default => null,
            };
        }
        $this->updatePreventCollapse();
    }
    public function updatePreventCollapse()
    {
        $this->preventCollapse = in_array(true, $this->filters, true);
    }
    public function render()
    {
        $financialYears = FinancialYear::where('fy_b_id', $this->businessId)
            ->when($this->searchFY, fn($q) => $q->where('fy_year', 'like', "%{$this->searchFY}%"))
            ->get();
        $payrollPeriods = PayrollPeriod::where('pp_b_id', $this->businessId)
            ->when($this->selectedFYId, fn($q) => $q->where('pp_fy_id', $this->selectedFYId))
            ->when(
                strlen($this->searchPayroll) >= 1 && !$this->selectedPayrollPeriodId,
                fn($q) => $q->where('pp_name', 'like', "%{$this->searchPayroll}%")
            )
            ->limit(100)
            ->get();
        $employees = collect();
        if (!empty($this->search)) {
            $employees = Employee::where('emp_role_id', '<>', 1)
                ->where('emp_b_id', $this->businessId)
                ->where(fn($q) => $q->where('emp_full_name', 'like', "%{$this->search}%")
                    ->orWhere('emp_code', 'like', "%{$this->search}%"))
                ->when($this->employeeStatusId, fn($q) => $q->where('emp_status', $this->employeeStatusId))
                ->limit(100)
                ->get();
        }
        $employeeStatus = MasterTable::where('m_group', 'STATUS')
            ->limit(100)
            ->select('m_id', 'm_name')
            ->get();
        return view('livewire.salary.payroll-report', compact(
            'financialYears',
            'payrollPeriods',
            'employees',
            'employeeStatus'
        ));
    }
    public function generateReport()
    {
        $this->validate([
            'selectedFYId' => 'required|exists:financial_years,fy_id',
            'selectedPayrollPeriodId' => 'required|exists:payroll_periods,pp_id',
            'paymentMode' => 'nullable|in:bank,cash,cheque',
        ], [
            'selectedFYId.required' => 'Financial year is required.',
            'selectedPayrollPeriodId.required' => 'Payroll period is required.',
        ]);
        $payroll = PayrollPeriod::where('pp_b_id', $this->businessId)
            ->where('pp_is_processed', 120)
            ->find($this->selectedPayrollPeriodId);
        if (!$payroll) {
            return back()->with('error', 'Invalid Payroll Period');
        }
        $query = ProcessedEmployeeSalary::with(
            'employee.fh_employee_status',
            'earnings',
            'deductions',
            'employee.employeeProjects'
        )
            ->where('ps_payroll_id', $this->selectedPayrollPeriodId)
            ->when($this->employeeStatusId, function ($q) {
                $q->whereHas('employee', function ($subQuery) {
                    $subQuery->where('emp_status', $this->employeeStatusId);
                });
            });
        if ($this->paymentMode) {
            $query->whereHas('employee', fn($q) => $q->where('emp_paymentmode', $this->paymentMode));
        }
        // if($this->employeeStatusId){
        //     $query->where('ps_employee_status', $this->employeeStatusId);
        // }
        if ($this->selectedEmployeeId) {
            $query->where('ps_emp_id', $this->selectedEmployeeId);
        }
        $processedSalary = $query->get();
        // dd($processedSalary);
        if ($processedSalary->isEmpty()) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'No records found for the selected criteria.']);
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
        $basicInfoColumns = [
            'S#',
            'Emp Code',
            'Title',
            'Employee Name',
            'Project',
            'Region',
            'Designation',
            'Grade',
            'Personal Email',
            'Personal Mobile',
            'Official Mobile',
            'Work Email',
            'Date of Joining',
            'Service Length as on Payroll Date',
            'Payment Mode',
            'Emp Bank Name',
            'Bank IFSC',
            'Account No.'
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
        $processedSalary = $processedSalary->transform(function ($item, $key) use (
            $earningsComponents,
            $deductionsComponents,
            $employeeSalaryEarningComponents,
            $employeeSalaryDeductionComponents,
            $daysInfoColumns,
            $employeeSalary
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
                'S#' => $key + 1,
                'Title' => $item->employee->fh_employee_title->m_name ?? '',
                'Emp Code' => $item->employee->emp_code,
                'Employee Name' => $item->employee->emp_full_name,
                'Project' => $item->employee->employeeProjects->pluck('ps_name')->implode(', '),
                'Region' => $item->employee->emp_region_id ?? '',
                'Designation' => $item->employee->fh_designation->dg_name ?? '',
                'Grade' => $item->employee->fh_grade->g_name ?? '',
                'Personal Email' => $item->employee->emp_email ?? '',
                'Personal Mobile' => $item->employee->emp_phone ?? '',
                'Official Mobile' => $item->employee->emp_official_contact ?? '',
                'Work Email' => $item->employee->emp_company_email ?? '',
                'Date of Joining' => $item->employee->emp_date_of_joining ? Carbon::parse($item->employee->emp_date_of_joining)->format('d M, Y') : '',
                'Service Length as on Payroll Date' => "{$diff->y} y {$diff->m} m {$diff->d} d",
                'Payment Mode' => $item->employee->emp_paymentmode ?? '',
                'Emp Bank Name' => $item->employee->emp_bank_name ?? '',
                'Bank IFSC' => $item->employee->emp_bank_ifsc_code ?? '',
                'Account No.' => $formattedAccountNo ?? ''
            ];
            foreach ($employeeSalaryEarningComponents as $comp) {
                $rowData['Salary Master_' . $comp] = $empSalaryEarningData[$comp] ?? 0;
            }
            $rowData['Salary Master_Other Allowance'] = $employeeSalary->where('es_emp_id', $item->ps_emp_id)->first()->es_rem_allowance ?? 0;
            $rowData['Monthly Gross'] = $employeeSalary->where('es_emp_id', $item->ps_emp_id)->first()->es_monthly_gross ?? 0;
            $rowData['CTC'] = $employeeSalary->where('es_emp_id', $item->ps_emp_id)->first()->es_monthly_ctc ?? 0;
            foreach ($employeeSalaryDeductionComponents as $comp) {
                $rowData['Salary Master_' . $comp] = $empSalaryDeductionData[$comp] ?? 0;
            }
            $rowData['Attendance Summary_Days In Month'] = $item->ps_total_days_in_month;
            $rowData['Attendance Summary_Workable Days'] = $item->ps_total_month_working_days;
            $rowData['Attendance Summary_Days Worked'] = $item->ps_total_days_worked;
            $rowData['Attendance Summary_Present Days'] = $item->ps_present_days;
            $rowData['Attendance Summary_Late Count'] = $item->ps_days_late ?? 0;
            $rowData['Attendance Summary_WeekOffs'] = $item->ps_week_off_count;
            $rowData['Attendance Summary_UPL'] = $item->ps_upl_count ?? 0;
            foreach ($earningsComponents as $comp) {
                $rowData['Earnings_' . $comp] = $earningsData[$comp] ?? 0;
            }
            $rowData['Monthly CTC'] = $item->ps_monthly_ctc ?? 0;
            $rowData['Gross Salary'] = $item->ps_monthly_gross ?? 0;
            foreach ($deductionsComponents as $comp) {
                $rowData['Deductions_' . $comp] = $deductionsData[$comp] ?? 0;
            }
            $rowData['Net Pay'] = $item->ps_monthly_net_salary ?? 0;
            return $rowData;
        });
        $user = Auth::user();
        $businessName = $user->fh_business->b_name ?? 'Business';
        $monthName = optional(PayrollPeriod::find($this->selectedPayrollPeriodId))->pp_name ?? 'Month';
        $employeeSalaryEarningsHeading = $this->selectedEmployeeEarningsHeading;
        $employeeSalaryDeductionHeading = $this->selectedEmployeeDeductionsHeading;
        $earningsHeading = $this->selectedEarningHeading;
        $deductionsHeading = $this->selectedDeductionHeading;
        $daysInfoHeading = $this->selectedDaysInfoHeading;
        return Excel::download(new class(
            $processedSalary,
            $businessName,
            $monthName,
            $employeeSalaryEarningsHeading,
            $employeeSalaryDeductionHeading,
            $earningsHeading,
            $deductionsHeading,
            $employeeSalaryEarningComponents,
            $employeeSalaryDeductionComponents,
            $earningsComponents,
            $deductionsComponents,
            $basicInfoColumns,
            $daysInfoColumns,
            $daysInfoHeading
        ) implements FromCollection, WithHeadings, WithEvents, WithCustomStartCell, WithColumnWidths {
            use Exportable;
            protected $data;
            protected $businessName;
            protected $monthName;
            protected $employeeSalaryEarningsHeading;
            protected $employeeSalaryDeductionHeading;
            protected $earningsHeading;
            protected $deductionsHeading;
            protected $employeeSalaryEarningComponents;
            protected $employeeSalaryDeductionComponents;
            protected $earningsComponents;
            protected $deductionsComponents;
            protected $basicInfoColumns;
            protected $daysInfoColumns;
            protected $daysInfoHeading;
            protected $columnHeaders;
            protected $basicColumnCount = 18; // 0-based index → 18 columns (S# … Account No.)
            public function __construct(
                $data,
                $businessName,
                $monthName,
                $employeeSalaryEarningsHeading,
                $employeeSalaryDeductionHeading,
                $earningsHeading,
                $deductionsHeading,
                $employeeSalaryEarningComponents,
                $employeeSalaryDeductionComponents,
                $earningsComponents,
                $deductionsComponents,
                $basicInfoColumns,
                $daysInfoColumns,
                $daysInfoHeading
            ) {
                $this->data = $data;
                $this->businessName = $businessName;
                $this->monthName = $monthName;
                $this->employeeSalaryEarningsHeading = $employeeSalaryEarningsHeading;
                $this->employeeSalaryDeductionHeading = $employeeSalaryDeductionHeading;
                $this->earningsHeading = $earningsHeading;
                $this->deductionsHeading = $deductionsHeading;
                $this->employeeSalaryEarningComponents = $employeeSalaryEarningComponents;
                $this->employeeSalaryDeductionComponents = $employeeSalaryDeductionComponents;
                $this->earningsComponents = $earningsComponents;
                $this->deductionsComponents = $deductionsComponents;
                $this->basicInfoColumns = $basicInfoColumns;
                $this->daysInfoColumns = $daysInfoColumns;
                $this->daysInfoHeading = $daysInfoHeading;
                $this->columnHeaders = $this->buildColumnHeaders();
            }
            protected function buildColumnHeaders()
            {
                $headers = $this->basicInfoColumns;
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
                // Grand Total Row
                $grand = [];
                foreach ($headers as $header) {
                    if (in_array($header, $this->basicInfoColumns)) {
                        $grand[] = $header === 'S#' ? 'Grand Totals' : '';
                        continue;
                    }
                    $sum = $this->data->sum(fn($i) => is_numeric($i[$header] ?? 0) ? (float)$i[$header] : 0);
                    $grand[] = $sum;
                }
                $rows->push($grand);
                return $rows;
            }
            public function headings(): array
            {
                $groupRow = ['Employee Details'];
                for ($i = 1; $i < $this->basicColumnCount; $i++) $groupRow[] = '';
                if ($this->employeeSalaryEarningsHeading) {
                    $cnt = count($this->employeeSalaryEarningComponents) + 3;
                    $groupRow[] = $this->employeeSalaryEarningsHeading;
                    for ($i = 1; $i < $cnt; $i++) $groupRow[] = '';
                }
                if ($this->employeeSalaryDeductionHeading) {
                    $cnt = count($this->employeeSalaryDeductionComponents);
                    $groupRow[] = $this->employeeSalaryDeductionHeading;
                    for ($i = 1; $i < $cnt; $i++) $groupRow[] = '';
                }
                if ($this->daysInfoHeading) {
                    $groupRow[] = $this->daysInfoHeading;
                    for ($i = 1; $i < count($this->daysInfoColumns); $i++) $groupRow[] = '';
                }
                if ($this->earningsHeading) {
                    $cnt = count($this->earningsComponents) + 2;
                    $groupRow[] = $this->earningsHeading;
                    for ($i = 1; $i < $cnt; $i++) $groupRow[] = '';
                }
                if ($this->deductionsHeading) {
                    $cnt = count($this->deductionsComponents);
                    $groupRow[] = $this->deductionsHeading;
                    for ($i = 1; $i < $cnt; $i++) $groupRow[] = '';
                }
                $groupRow[] = 'Net Pay';
                $clean = array_map(fn($h) => preg_replace('/^(Salary Master_|Attendance Summary_|Earnings_|Deductions_)/', '', $h), $this->columnHeaders);
                return [$groupRow, $clean];
            }
            public function startCell(): string
            {
                return 'A6';
            }
            /* --------------------------------------------------------------- */
            /*  NEW: Fixed column widths (13) + special for S# & Emp Code   */
            /* --------------------------------------------------------------- */
            public function columnWidths(): array
            {
                $widths = [];
                foreach ($this->columnHeaders as $idx => $header) {
                    $colLetter = Coordinate::stringFromColumnIndex($idx + 1);
                    if ($header === 'S#' || $header === 'Emp Code') {
                        $widths[$colLetter] = 7;
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
                        $sheet->setShowGridlines(false);
                        $highestColumn = $sheet->getHighestColumn();
                        $highestRow    = $sheet->getHighestRow();
                        $printed       = Carbon::now()->format('d-M-Y h:i A T');
                        /* ------------------- Top 4 rows (company info) ------------------- */
                        foreach (range(1, 4) as $r) {
                            $sheet->mergeCells("A{$r}:{$highestColumn}{$r}");
                            $sheet->getStyle("A{$r}")->getFont()->setBold(true)->setSize(11);
                            $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                        }
                        $sheet->setCellValue('A1', $this->businessName);
                        $sheet->setCellValue('A2', 'Salary Register');
                        $sheet->setCellValue('A3', "For the month of {$this->monthName}");
                        $sheet->setCellValue('A4', "Printed on: {$printed}");
                        /* ------------------- Group header merges (row 6) ------------------- */
                        $sections = [['start' => 1, 'end' => $this->basicColumnCount]];
                        $col = $this->basicColumnCount + 1;
                        if ($this->employeeSalaryEarningsHeading) {
                            $c = count($this->employeeSalaryEarningComponents) + 3;
                            $sections[] = ['start' => $col, 'end' => $col + $c - 1];
                            $col += $c;
                        }
                        if ($this->employeeSalaryDeductionHeading) {
                            $c = count($this->employeeSalaryDeductionComponents);
                            $sections[] = ['start' => $col, 'end' => $col + $c - 1];
                            $col += $c;
                        }
                        if ($this->daysInfoHeading) {
                            $sections[] = ['start' => $col, 'end' => $col + 6];
                            $col += 7;
                        }
                        if ($this->earningsHeading) {
                            $c = count($this->earningsComponents) + 2;
                            $sections[] = ['start' => $col, 'end' => $col + $c - 1];
                            $col += $c;
                        }
                        if ($this->deductionsHeading) {
                            $c = count($this->deductionsComponents);
                            $sections[] = ['start' => $col, 'end' => $col + $c - 1];
                            $col += $c;
                        }
                        $sections[] = ['start' => $col, 'end' => $col];
                        foreach ($sections as $s) {
                            if ($s['start'] !== $s['end']) {
                                $start = Coordinate::stringFromColumnIndex($s['start']);
                                $end   = Coordinate::stringFromColumnIndex($s['end']);
                                $sheet->mergeCells("{$start}6:{$end}6");
                            }
                        }
                        /* ------------------- Header styling (rows 6-7) ------------------- */
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
                        /* ------------------- Data rows (wrap + height 25) ------------------- */
                        $dataRange = "A8:{$highestColumn}{$highestRow}";
                        $sheet->getStyle($dataRange)
                            ->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                            ->setWrapText(true);
                        $sheet->getStyle($dataRange)->getFont()->setSize(8);
                        // Row height = 25 for every data row (including grand total)
                        for ($r = 8; $r <= $highestRow; $r++) {
                            $sheet->getRowDimension($r)->setRowHeight(25);
                        }
                        /* ------------------- Borders ------------------- */
                        $sheet->getStyle("A6:{$highestColumn}{$highestRow}")
                            ->getBorders()
                            ->getAllBorders()
                            ->setBorderStyle(Border::BORDER_THIN)
                            ->getColor()->setRGB('D3D3D3');
                        /* ------------------- Number format for monetary columns ------------------- */
                        $sheet->getStyle("T8:{$highestColumn}{$highestRow}")
                            ->getNumberFormat()
                            ->setFormatCode('#,##0.00;[Red]-#,##0.00');
                        /* ------------------- Alternating row colours ------------------- */
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
                        /* ------------------- Grand total row ------------------- */
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
                        /* ------------------- Header row heights ------------------- */
                        foreach (range(1, 4) as $r) $sheet->getRowDimension($r)->setRowHeight(20);
                        $sheet->getRowDimension(6)->setRowHeight(15);
                        $sheet->getRowDimension(7)->setRowHeight(30);
                        /* ------------------- Legend / Notes ------------------- */
                        $legendStart = $grandRow + 2;
                        $sheet->setCellValue("A{$legendStart}", 'Legend / Notes');
                        $sheet->mergeCells("A{$legendStart}:{$highestColumn}{$legendStart}");
                        $sheet->getStyle("A{$legendStart}")
                            ->getFont()->setBold(true)->setSize(10);
                        $sheet->getStyle("A{$legendStart}")
                            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                        $notes = [
                            'Employee Details: Basic information about the employee',
                            'Salary Master: Fixed components defined in employee salary structure',
                            'Attendance Summary: Key attendance metrics for the payroll period',
                            'Earnings: Variable or processed earnings calculated and paid this month',
                            'Deductions: Variable or processed deductions applied this month',
                            'Net Pay: Gross Salary minus total Deductions',
                            'Grand Totals: Sum of all employees for respective numeric columns',
                            'All monetary values are in INR (Indian Rupees)'
                        ];
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
}
