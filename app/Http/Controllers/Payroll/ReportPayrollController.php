<?php
namespace App\Http\Controllers\Payroll;
use App\Models\Grade;
use App\Models\Branch;
use App\Models\Business;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Designation;
use App\Models\MasterTable;
use Illuminate\Http\Request;
use App\Models\FinancialYear;
use App\Models\PayrollPeriod;
use App\Models\SalaryAllowance;
use App\Exports\BankSheetExport;
use App\Exports\PfEpsSheetExport;
use App\Models\StatutoryDeduction;
use Illuminate\Support\Facades\DB;
use App\Exports\PayrollSheetExport;
use App\Http\Controllers\Controller;
use App\Models\SalaryEmployeeSalary;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\ProcessedSalaryEarning;
use App\Models\ProcessedEmployeeSalary;
use App\Models\ProcessedSalaryDeduction;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\Hyperlink;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Http\Controllers\Payroll\PayrollController;
class ReportPayrollController extends Controller
{
    protected $payrollController;
    public function __construct(PayrollController $payrollController)
    {
        $this->payrollController = $payrollController;
    }
    public function exportPayrollReport(Request $request)
    {
        $user = Auth::user();
        $business_id = $user->emp_b_id;
        // Fetch and process data as before (keeping your current data fetch logic)
        $employee_salaries = DB::table('employee_salaries')
            ->join('employees', 'employees.emp_id', '=', 'employee_salaries.es_emp_id')
            ->where('es_b_id', $business_id)->where('is_super_admin', 0)
            ->select('employee_salaries.*', 'employees.emp_full_name')
            ->get();
        $salaries = $this->payrollController->getMonthlySalary($business_id, $employee_salaries);
        // ✅ CHECK IF SALARIES EXIST
        if (!$salaries || empty($salaries)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No salary data found for the selected month.',
            ]);
        }
        $earnings = DB::table('salary_allowances')
            ->join('master_table', 'salary_allowances.sa_earning_type_id', '=', 'master_table.m_id')
            ->where('salary_allowances.sa_b_id', $business_id)
            ->select('salary_allowances.sa_earning_type_id', 'master_table.m_name')
            ->get();
        $deductions = DB::table('statutory_deductions')
            ->join('master_table', 'statutory_deductions.std_deduction_type_id', '=', 'master_table.m_id')
            ->where('statutory_deductions.std_b_id', $business_id)
            ->select('statutory_deductions.std_deduction_type_id', 'master_table.m_name')
            ->get();
        $earningTypes = [];
        $deductionTypes = [];
        foreach ($earnings as $earning) {
            $earningTypes[$earning->sa_earning_type_id] = $earning->m_name;
        }
        foreach ($deductions as $deduction) {
            $deductionTypes[$deduction->std_deduction_type_id] = $deduction->m_name;
        }
        ksort($earningTypes);
        ksort($deductionTypes);
        $payrollData = [];
        $serialNo = 1;
        $grandTotals = [
            'Net Salary' => 0,
            'Gross Salary' => 0,
            'Total Earnings' => 0,
            'Total Employee Deductions' => 0,
            'Total Employer Deductions' => 0,
        ];
        // dd($salaries);
        foreach ($salaries as $salary) {
            $businessName = $salary['business_name'];
            $monthName = $salary['month_name'];
            $branchName = $salary['branch_name'];
            $row = [
                'S#' => $serialNo++,
                'Emp Code' => $salary['emp_code'],
                'Name' => $salary['employee_full_name'],
                'Days in Month' => $salary['total_days_in_month'],
                'Workable Days' => $salary['workable_days'],
                'Days Worked' => $salary['total_days_worked'],
                'Present Days' => $salary['present_days'],
                'WeekOffs' => $salary['week_off_count'],
                'Basic Salary' => $salary['basic_salary'],
                'Monthly Salary' => $salary['monthly_salary'],
                'Per Day Salary' => $salary['per_day_salary'],
                'Worked Days Salary' => $salary['worked_days_salary'],
            ];
            foreach ($earningTypes as $key => $name) {
                $row[$name] = $salary['earnings_breakdown'][$key] ?? 0;
            }
            $row['Total Earnings'] = $salary['total_earnings'];
            $row['Total Employee Deductions'] = $salary['total_employee_deductions'];
            $row['Total Employer Deductions'] = $salary['total_employer_deductions'];
            foreach ($deductionTypes as $key => $name) {
                $row["$name (Employee)"] = $salary['deductions_breakdown'][$key]['employee'] ?? 0;
                $row["$name (Employer)"] = $salary['deductions_breakdown'][$key]['employer'] ?? 0;
            }
            $row['Gross Salary'] = $salary['gross_salary'];
            $row['Net Salary'] = $salary['net_salary'];
            // ✅ Summing
            $grandTotals['Net Salary'] += floatval(str_replace(',', '', $salary['net_salary']));
            $grandTotals['Gross Salary'] += floatval(str_replace(',', '', $salary['gross_salary']));
            $grandTotals['Total Earnings'] += floatval(str_replace(',', '', $salary['total_earnings']));
            $grandTotals['Total Employee Deductions'] += floatval(str_replace(',', '', $salary['total_employee_deductions']));
            $grandTotals['Total Employer Deductions'] += floatval(str_replace(',', '', $salary['total_employer_deductions']));
            $payrollData[] = $row;
        }
        $totalRow = ['S#' => '', 'Emp Code' => '', 'Name' => 'Grand Total'];
        foreach (array_keys($payrollData[0]) as $key) {
            if (isset($grandTotals[$key])) {
                $totalRow[$key] = number_format($grandTotals[$key], 2); // Format total value
            } elseif (!isset($totalRow[$key])) {
                $totalRow[$key] = ''; // Empty for other columns
            }
        }
        $payrollData[] = $totalRow; // Add to data
        // EXPORT with proper heading rows
        return Excel::download(new class($payrollData, $earningTypes, $deductionTypes, $businessName, $monthName, $branchName) implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize {
            protected $data, $earningTypes, $deductionTypes, $businessName, $monthName, $branchName;
            public function __construct($data, $earningTypes, $deductionTypes, $businessName, $monthName, $branchName)
            {
                $this->data = $data;
                $this->earningTypes = $earningTypes;
                $this->deductionTypes = $deductionTypes;
                $this->businessName = $businessName;
                $this->monthName = $monthName;
                $this->branchName = $branchName;
            }
            public function collection()
            {
                return collect($this->data);
            }
            public function headings(): array
            {
                $mainHeadings = [
                    [$this->businessName], // Row 1: Business Name
                    ["SALARY DETAIL FOR THE MONTH OF " . strtoupper($this->monthName)], // Row 2: Month
                    [$this->branchName],
                    [''],
                ];
                $columns = [
                    'S#',
                    'Emp Code',
                    'Name',
                    'Days in Month',
                    'Workable Days',
                    'Days Worked',
                    'Present Days',
                    'WeekOffs',
                    'Basic Salary',
                    'Monthly Salary',
                    'Per Day Salary',
                    'Worked Days Salary',
                ];
                foreach ($this->earningTypes as $name)
                    $columns[] = $name;
                $columns[] = 'Total Earnings';
                $columns[] = 'Total Employee Deductions';
                $columns[] = 'Total Employer Deductions';
                foreach ($this->deductionTypes as $name) {
                    $columns[] = "$name (Employee)";
                    $columns[] = "$name (Employer)";
                }
                $columns[] = 'Gross Salary';
                $columns[] = 'Net Salary';
                return array_merge($mainHeadings, [$columns]);
            }
            public function styles(Worksheet $sheet)
            {
                $totalRowNumber = 5 + count($this->data);
                // Bold and center company name and headings
                $sheet->mergeCells('A1:E1');
                $sheet->mergeCells('A2:E2');
                $sheet->mergeCells('A3:E3');
                return [
                    1 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => 'center']],
                    2 => ['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => 'center']],
                    3 => ['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => 'center']],
                    4 => ['font' => ['bold' => true]],
                    5 => ['font' => ['bold' => true]],
                    $totalRowNumber => ['font' => ['bold' => true]],
                ];
            }
        }, 'payroll_report.xlsx');
    }
    public function bankSheetExport()
    {
        $user = Auth::user();
        $branch = Branch::where('br_b_id', $user->emp_b_id)->get();
        $department = Department::where('d_b_id', $user->emp_b_id)->get();
        $designation = Designation::where('dg_b_id', $user->emp_b_id)->get();
        $grade = Grade::where('g_b_id', $user->emp_b_id)->get();
        $status = MasterTable::where('m_group', 'STATUS')->get();
        $payrollPeriods = PayrollPeriod::orderBy('pp_start_date', 'desc')->where('pp_b_id', $user->emp_b_id)->get();

        return view('admin.employees.salary.bank_sheet_export', compact('payrollPeriods', 'user', 'branch', 'department', 'designation', 'grade', 'status'));
    }
    public function payrollReportSheet()
    {
        $user = Auth::user();
        $business_id = $user->emp_b_id;
        $branch = Branch::where('br_b_id', $user->emp_b_id)->get();
        $department = Department::where('d_b_id', $user->emp_b_id)->get();
        $designation = Designation::where('dg_b_id', $user->emp_b_id)->get();
        $grade = Grade::where('g_b_id', $user->emp_b_id)->get();
        $status = MasterTable::where('m_group', 'STATUS')->get();
        $payrollPeriods = PayrollPeriod::where('pp_b_id', $business_id)->orderBy('created_at', 'desc')->get();
        // Initialize to prevent undefined error
        $processedSalaries = collect();
        $preEarningColumns = [
            'emp_status' => 'Emp Status',
            'emp_code' => 'Emp Code',
            'emp_full_name' => 'Employee Name',
            'days_in_month' => 'Days In Month',
            'workable_days' => 'Workable Days',
            'days_worked' => 'Days Worked',
            'present_days' => 'Present Days',
            'weekoffs' => 'WeekOffs',
            'upl' => 'UPL'
        ];
        // Earnings and Deductions will be empty initially (before filter)
        $earningComponents = SalaryAllowance::where('sa_b_id', $business_id)
            ->select('sa_earning_type_id', 'sa_title')
            ->distinct('sa_earning_type_id')
            ->get()
            ->pluck('sa_title', 'sa_earning_type_id')
            ->toArray();
        // dd($earningComponents);
        $postEarningColumns = [
            'total_earning' => 'Total Earnings',
            'gross_salary' => 'Gross Salary',
        ];
        $deductions = StatutoryDeduction::with('fh_deduction_type')
            ->where('std_b_id', $business_id)
            ->where('std_status', 1)
            ->get()
            ->filter(fn($row) => $row->fh_deduction_type) // Avoid null relation
            ->mapWithKeys(fn($row) => [
                $row->fh_deduction_type->m_name => $row->fh_deduction_type->m_name
            ])
            ->unique()
            ->toArray();
        $postDeductionColumns = ['net_pay' => 'Net Pay'];
        $exportableFields = array_merge(
            $preEarningColumns,
            $earningComponents,
            $postEarningColumns,
            $deductions,
            $postDeductionColumns
        );
        return view('admin.employees.salary.payroll_sheet_export', compact(
            'user',
            'branch',
            'department',
            'designation',
            'grade',
            'status',
            'payrollPeriods',
            'exportableFields' // 👈 pass this to the view
        ));
    }
    public function payrollReport()
    {
        return view('admin.employees.salary.payroll_sheet');
    }


    /**
     * Export ESIC report
     */

    public function esicReport()
    {
        // dd(1);
       return view('admin.employees.salary.esic_report');
    }


    public function adhocReport()
    {
        // dd(1);
       return view('admin.employees.salary.adhoc_report');
    }


    public function payrollSheetReportExport(Request $request)
    {
        $user = Auth::user();
        $business_id = $user->emp_b_id;
        $business = Business::where('b_id', $business_id)->select('b_name')->first();
        $businessName = $business ? $business->b_name : 'Unknown Business';
        $payrollId = $request->payroll_period;
        $payroll = PayrollPeriod::where('pp_id', $payrollId)->first();
        if (!$payroll) {
            return response()->json([
                'success' => false,
                'message' => 'Payroll period not found.'
            ], 404);
        }
        $monthName = $payroll->pp_name;
        $mainHeading = "Salary Master";
        $earningsHeading = "Earnings";
        $deductionsHeading = "Deductions";
        // Fetch salary data with ps_id
        $processedSalary = ProcessedEmployeeSalary::with('employee', 'employee.fh_employee_status')
            ->where('ps_b_id', $business_id)
            ->where('ps_payroll_id', $payrollId)
            ->get();
        // dd($processedSalary);
        if ($processedSalary->isEmpty()) {
            return redirect()
                ->back()
                ->with('swal', [
                    'icon' => 'warning',
                    'title' => 'No Data Found',
                    'text' => 'No processed salaries found for the selected payroll period.',
                ]);
        }
        // Fetch unique earnings & deductions headings from DB
        $earningsComponents = ProcessedSalaryEarning::whereIn('ps_id', $processedSalary->pluck('ps_id'))
            ->distinct()
            ->pluck('ps_earning_type')
            ->toArray();
        $deductionsComponents = ProcessedSalaryDeduction::whereIn('ps_id', $processedSalary->pluck('ps_id'))
            ->distinct()
            ->pluck('ps_deduction_type')
            ->toArray();
        // Define column order
        $preEarningColumns = [
            'S No.',
            'Emp Status',
            'Emp Code',
            'Employee Name',
            'Days In Month',
            'Workable Days',
            'Days Worked',
            'Present Days',
            'WeekOffs',
            'UPL'
        ];
        $postEarningColumns = ['Total Earnings', 'Gross Salary'];
        $postDeductionColumns = ['Net Pay'];
        // Transform salary data with earnings & deductions
        $processedSalary->transform(function ($item, $key) use ($earningsComponents, $deductionsComponents) {
            $earningsData = ProcessedSalaryEarning::where('ps_id', $item->ps_id)
                ->pluck('ps_e_amount', 'ps_earning_type')
                ->toArray();
            $deductionsData = ProcessedSalaryDeduction::where('ps_id', $item->ps_id)
                ->pluck('ps_d_amount', 'ps_deduction_type')
                ->toArray();
            $rowData = [
                'S No.' => $key + 1,
                'Emp Status' => $item->employee->fh_employee_status->m_name,
                'Emp Code' => $item->employee->emp_code,
                'Employee Name' => $item->employee->emp_full_name,
                'Days In Month' => $item->ps_total_days_in_month,
                'Workable Days' => $item->ps_total_month_working_days,
                'Days Worked' => $item->ps_total_days_worked,
                'Present Days' => $item->ps_present_days,
                'WeekOffs' => $item->ps_week_off_count,
                'UPL' => $item->ps_upl_count ?? 0,
            ];
            // Add earnings dynamically
            foreach ($earningsComponents as $earning) {
                $rowData[$earning] = $earningsData[$earning] ?? 0;
            }
            $rowData['Total Earnings'] = $item->ps_earnings ?? 0;
            $rowData['Gross Salary'] = $item->ps_worked_days_salary ?? 0;
            // $rowData['Gross Salary'] = ($item->ps_worked_days_salary ?? 0) - ($item->ps_employer_deductions ?? 0);
            // Add deductions dynamically
            foreach ($deductionsComponents as $deduction) {
                $rowData[$deduction] = $deductionsData[$deduction] ?? 0;
            }
            // $rowData['Net Pay'] = $item->ps_monthly_net_salary ?? 0;
            $rowData['Net Pay'] = ($rowData['Gross Salary'] ?? 0) - ($item->ps_employee_deductions ?? 0);
            // dd($rowData);
            return $rowData;
        });
        return Excel::download(new class($processedSalary, $businessName, $monthName, $mainHeading, $earningsHeading, $deductionsHeading, $earningsComponents, $deductionsComponents, $preEarningColumns, $postEarningColumns, $postDeductionColumns) implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles {
            protected $data, $businessName, $monthName, $mainHeading, $earningsHeading, $deductionsHeading, $earningsComponents, $deductionsComponents, $preEarningColumns, $postEarningColumns, $postDeductionColumns;
            public function __construct($data, $businessName, $monthName, $mainHeading, $earningsHeading, $deductionsHeading, $earningsComponents, $deductionsComponents, $preEarningColumns, $postEarningColumns, $postDeductionColumns)
            {
                $this->data = $data;
                $this->businessName = $businessName;
                $this->monthName = $monthName;
                $this->mainHeading = $mainHeading;
                $this->earningsHeading = $earningsHeading;
                $this->deductionsHeading = $deductionsHeading;
                $this->earningsComponents = $earningsComponents;
                $this->deductionsComponents = $deductionsComponents;
                $this->preEarningColumns = $preEarningColumns;
                $this->postEarningColumns = $postEarningColumns;
                $this->postDeductionColumns = $postDeductionColumns;
            }
            public function collection()
            {
                $data = collect($this->data);
                // Initialize Grand Totals with all columns
                $grandTotals = [
                    'S No.' => 'Grand Total',
                    'Emp Status' => '',
                    'Emp Code' => '',
                    'Employee Name' => '',
                ];
                // Summary Columns
                $summaryColumns = ['Days In Month', 'Workable Days', 'Days Worked', 'Present Days', 'WeekOffs', 'UPL'];
                foreach ($summaryColumns as $col) {
                    $grandTotals[$col] = $data->sum($col);
                }
                // Earnings Columns (Dynamic)
                foreach ($this->earningsComponents as $earning) {
                    $grandTotals[$earning] = $data->sum($earning);
                }
                // Post Earnings Columns (Total Earnings, Gross Salary)
                foreach ($this->postEarningColumns as $postEarning) {
                    $grandTotals[$postEarning] = $data->sum($postEarning);
                }
                // Deductions Columns (Dynamic)
                foreach ($this->deductionsComponents as $deduction) {
                    $grandTotals[$deduction] = $data->sum($deduction);
                }
                // Post Deduction Columns (Net Pay)
                foreach ($this->postDeductionColumns as $postDeduction) {
                    $grandTotals[$postDeduction] = $data->sum($postDeduction);
                }
                $data->push($grandTotals);
                return $data;
            }
            public function headings(): array
            {
                $summaryColumnsCount = 6; // Fixed number of summary columns
                $earningsCount = count($this->earningsComponents);
                $deductionsCount = count($this->deductionsComponents);
                $postEarningsCount = count($this->postEarningColumns); // New columns after earnings
                return [
                    [$this->businessName], // Row 1: Business Name
                    ["SALARY DETAIL FOR THE MONTH OF " . strtoupper($this->monthName)], // Row 2: Month Name
                    [$this->mainHeading], // Row 3: "Salary Master"
                    // Row 4: Merged Headings
                    array_merge(
                        ['', '', ''],
                        ['Summary'],
                        array_fill(0, $summaryColumnsCount - 1, ''), // Summary Header
                        ['Earning Components'],
                        array_fill(0, $earningsCount - 1, ''), // Earnings Header
                        [''],
                        array_fill(0, $postEarningsCount - 1, ''), // Post Earnings Header
                        ['Deduction Components'],
                        array_fill(0, $deductionsCount - 1, ''), // Deductions Header
                        // ['Net Pay'] // Single merged column after deductions
                    ),
                    // Row 5: Column Names
                    array_merge(
                        ['S No.', 'Emp Status', 'Emp Code', 'Employee Name'],
                        ['Days In Month', 'Workable Days', 'Days Worked', 'Present Days', 'WeekOffs', 'UPL'], // Summary Columns
                        $this->earningsComponents, // Earnings Columns
                        $this->postEarningColumns, // Post Earnings Columns
                        $this->deductionsComponents, // Deductions Columns
                        ['Net Pay'] // New column after deductions
                    )
                ];
            }
            public function styles(Worksheet $sheet)
            {
                // Helper function to safely merge only when range is more than one cell
                $safeMerge = function ($sheet, $start, $end) {
                    if ($start !== $end) {
                        $sheet->mergeCells("$start:$end");
                    }
                };
                $summaryStart = 'D4';
                $summaryEnd = chr(ord('D') + 5) . '4'; // Merge for 6 summary columns
                $earningsStart = chr(ord('D') + 6) . '4';
                $earningsEnd = chr(ord($earningsStart[0]) + count($this->earningsComponents) - 1) . '4';
                $postEarningsStart = chr(ord($earningsEnd[0]) + 1) . '4';
                $postEarningsEnd = chr(ord($postEarningsStart[0]) + count($this->postEarningColumns) - 1) . '4';
                $deductionsStart = chr(ord($postEarningsEnd[0]) + 1) . '4';
                $deductionsEnd = chr(ord($deductionsStart[0]) + count($this->deductionsComponents) - 1) . '4';
                $netPayStartCol = chr(ord($deductionsEnd[0]) + 1);
                $netPayStart = $netPayStartCol . '4';
                $netPayEnd = $netPayStartCol . '4'; // Only one column for Net Pay
                // Safely merge sections
                $safeMerge($sheet, $summaryStart, $summaryEnd);
                $safeMerge($sheet, $earningsStart, $earningsEnd);
                $safeMerge($sheet, $postEarningsStart, $postEarningsEnd);
                $safeMerge($sheet, $deductionsStart, $deductionsEnd);
                $safeMerge($sheet, $netPayStart, $netPayEnd); // Fix applied here
                $sheet->freezePane('D6');
                $sheet->mergeCells('A1:D1');
                $sheet->mergeCells('A2:D2');
                $sheet->mergeCells('A3:D3');
                $sheet->getStyle('A1:C3')->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);
                $sheet->getStyle('A1:C3')->getFont()->setBold(true);
                $totalRows = $sheet->getHighestRow();
                $highestColumn = Coordinate::stringFromColumnIndex(
                    Coordinate::columnIndexFromString($sheet->getHighestColumn())
                );
                $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $cell = Coordinate::stringFromColumnIndex($col) . '5';
                    $value = $sheet->getCell($cell)->getValue();
                    if (strpos($value, ' ') !== false) {
                        $formattedValue = wordwrap($value, 10, "\n", true);
                        $sheet->setCellValue($cell, $formattedValue);
                        $sheet->getStyle($cell)->getAlignment()->setWrapText(true);
                    }
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
                }
                $sheet->getStyle('A5:' . $highestColumn . '5')
                    ->getAlignment()
                    ->setWrapText(true)
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $columnLetter = Coordinate::stringFromColumnIndex($col);
                    $sheet->getColumnDimension($columnLetter)->setWidth(12);
                    $sheet->getStyle($columnLetter . '5')->getAlignment()->setWrapText(true);
                }
                $sheet->getRowDimension(1)->setRowHeight(20);
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getRowDimension(3)->setRowHeight(20);
                $sheet->getRowDimension(5)->setRowHeight(50);
                return [
                    1 => ['font' => ['bold' => true, 'size' => 11], 'alignment' => ['horizontal' => 'center']],
                    2 => ['font' => ['bold' => true, 'size' => 10], 'alignment' => ['horizontal' => 'center']],
                    3 => ['font' => ['bold' => true, 'size' => 10], 'alignment' => ['horizontal' => 'center']],
                    4 => [
                        'font' => ['bold' => true, 'size' => 10],
                        'alignment' => ['horizontal' => 'center'],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                            ],
                        ],
                    ],
                    5 => [
                        'font' => ['bold' => true, 'size' => 8],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                            ],
                        ],
                    ],
                    $totalRows => ['font' => ['bold' => true, 'size' => 8]],
                ];
            }
        }, 'payroll_report_sheet.xlsx');
    }
    public function pfEpsReportSheet()
    {
        $user = Auth::user();
        $branch = Branch::where('br_b_id', $user->emp_b_id)->get();
        $department = Department::where('d_b_id', $user->emp_b_id)->get();
        $designation = Designation::where('dg_b_id', $user->emp_b_id)->get();
        $grade = Grade::where('g_b_id', $user->emp_b_id)->get();
        $status = MasterTable::where('m_group', 'STATUS')->get();
        $payrollPeriods = PayrollPeriod::orderBy('created_at', 'desc')->where('pp_b_id', $user->emp_b_id)->get();

        return view('admin.payroll.pf_eps_report_export');
    }
    public function pfEpsSheetReportExport(Request $request)
    {
        return view('admin.payroll.pf_eps_report_export', compact('user', 'branch', 'department', 'designation', 'grade', 'status', 'payrollPeriods'));
    }
    public function exportPayrollSheet(Request $request)
    {
        $user = Auth::user();
        $business_id = $user->emp_b_id;
        $payrollId = $request->payroll_period;
        $payroll = PayrollPeriod::find($payrollId);
        if (!$payroll) {
            return back()->with('error', 'Invalid Payroll Period');
        }
        $businessName = Business::where('b_id', $business_id)->value('b_name') ?? 'Unknown Business';
        $monthName = $payroll->pp_name;
        $mainHeading = "Salary Master";
        $earningsHeading = "Earnings";
        $deductionsHeading = "Deductions";
        $processedSalary = ProcessedEmployeeSalary::with('employee', 'employee.fh_employee_status')
            ->where('ps_b_id', $business_id)
            ->where('ps_payroll_id', $payrollId)
            ->get();
        if ($processedSalary->isEmpty()) {
            return back()->with('error', 'No processed salaries found for the selected payroll period.');
        }
        $earningsComponents = ProcessedSalaryEarning::whereIn('ps_id', $processedSalary->pluck('ps_id'))
            ->distinct()
            ->pluck('ps_earning_type')
            ->toArray();
        $deductionsComponents = ProcessedSalaryDeduction::whereIn('ps_id', $processedSalary->pluck('ps_id'))
            ->distinct()
            ->pluck('ps_deduction_type')
            ->toArray();
        $preEarningColumns = [
            'S No.',
            'Emp Status',
            'Emp Code',
            'Employee Name',
            'Days In Month',
            'Workable Days',
            'Days Worked',
            'Present Days',
            'WeekOffs',
            'UPL'
        ];
        $postEarningColumns = ['Total Earnings', 'Gross Salary'];
        $postDeductionColumns = ['Net Pay'];
        $processedSalary->transform(function ($item, $key) use ($earningsComponents, $deductionsComponents) {
            $earningsData = ProcessedSalaryEarning::where('ps_id', $item->ps_id)
                ->pluck('ps_e_amount', 'ps_earning_type')->toArray();
            $deductionsData = ProcessedSalaryDeduction::where('ps_id', $item->ps_id)
                ->pluck('ps_d_amount', 'ps_deduction_type')->toArray();
            $rowData = [
                'S No.' => $key + 1,
                'Emp Status' => $item->employee->fh_employee_status->m_name,
                'Emp Code' => $item->employee->emp_code,
                'Employee Name' => $item->employee->emp_full_name,
                'Days In Month' => $item->ps_total_days_in_month,
                'Workable Days' => $item->ps_total_month_working_days,
                'Days Worked' => $item->ps_total_days_worked,
                'Present Days' => $item->ps_present_days,
                'WeekOffs' => $item->ps_week_off_count,
                'UPL' => $item->ps_upl_count ?? 0,
            ];
            foreach ($earningsComponents as $earning) {
                $rowData[$earning] = $earningsData[$earning] ?? 0;
            }
            $rowData['Total Earnings'] = $item->ps_earnings ?? 0;
            $rowData['Gross Salary'] = ($item->ps_worked_days_salary ?? 0) - ($item->ps_employer_deductions ?? 0);
            foreach ($deductionsComponents as $deduction) {
                $rowData[$deduction] = $deductionsData[$deduction] ?? 0;
            }
            $rowData['Net Pay'] = ($rowData['Gross Salary'] ?? 0) - ($item->ps_employee_deductions ?? 0);
            return $rowData;
        });
        return Excel::download(
            new PayrollSheetExport(
                $processedSalary,
                $businessName,
                $monthName,
                $mainHeading,
                $earningsHeading,
                $deductionsHeading,
                $earningsComponents,
                $deductionsComponents,
                $preEarningColumns,
                $postEarningColumns,
                $postDeductionColumns
            ),
            'payroll_report_sheet.xlsx'
        );
    }
    public function mcTempReport()
    {
        $user = Auth::user();
        $branch = Branch::where('br_b_id', $user->emp_b_id)->get();
        $department = Department::where('d_b_id', $user->emp_b_id)->get();
        $designation = Designation::where('dg_b_id', $user->emp_b_id)->get();
        $grade = Grade::where('g_b_id', $user->emp_b_id)->get();
        $status = MasterTable::where('m_group', 'STATUS')->get();
        // $payrollPeriods = PayrollPeriod::orderBy('created_at', 'desc')->get();
        $finacial = FinancialYear::orderBy('fy_id', 'desc')->where('fy_b_id', $user->emp_b_id)->get();
        $payrollPeriods = PayrollPeriod::orderBy('created_at', 'desc')->where('pp_b_id', $user->emp_b_id)->get();
        // dd($finacial);
        return view('admin.payroll.mc_template', compact('user', 'branch', 'department', 'designation', 'grade', 'status', 'payrollPeriods', 'finacial'));
    }
    public function getPayrollPeriodsByFY(Request $request)
    {
        $user = Auth::user();
        $payrollPeriods = PayrollPeriod::where('pp_fy_id', $request->fy_id)
            ->where('pp_b_id', $user->emp_b_id)
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($payrollPeriods);
    }
    public function mcTempReportExport(Request $request)
    {
        $user = Auth::user();
        $business_id = $user->emp_b_id;
        $business = Business::where('b_id', $business_id)->select('b_name')->first();
        $businessName = $business ? $business->b_name : 'Unknown Business';
        $payrollId = $request->payroll_period;
        $payroll = PayrollPeriod::where('pp_id', $payrollId)->where('pp_b_id', $business_id)->first();
        if (!$payroll) {
            return back()->with('error', 'Invalid Payroll Period');
        }
        // Fetch employees involved in payroll processing
        $employeesWithPf = PayrollPeriod::join('processed_salaries', 'processed_salaries.ps_payroll_id', '=', 'payroll_periods.pp_id')
            ->join('employee_salaries', 'employee_salaries.es_emp_id', '=', 'processed_salaries.ps_emp_id')
            ->join('employees', 'employees.emp_id', '=', 'processed_salaries.ps_emp_id')
            ->leftJoin('departments', 'departments.d_id', '=', 'employees.emp_d_id')
            ->leftJoin('businesses', 'businesses.b_id', '=', 'employees.emp_b_id')
            ->leftJoin('designations', 'designations.dg_id', '=', 'employees.emp_dg_id')
            ->leftJoin('master_table AS gender', function ($join) {
                $join->on('gender.m_id', '=', 'employees.emp_gender_id')
                    ->where('gender.m_group', '=', 'GENDER');
            })
            ->select(
                'employees.emp_code',
                'employees.emp_fname',
                'businesses.b_name as company_name',
                'departments.d_name as department',
                'designations.dg_name as designation',
                'gender.m_name as gender',
                'processed_salaries.*',
                'employee_salaries.*'
            )
            ->where('payroll_periods.pp_id', $payrollId)
            ->get();
        if ($employeesWithPf->isEmpty()) {
            return back()->with('error', 'No employees found for the selected payroll period.');
        }
        $esicThreshold = StatutoryDeduction::select('std_threshold')
            ->where('std_b_id', $business_id)
            ->where('std_deduction_type_id', 352)
            ->value('std_threshold');
        $empEsicData = ProcessedEmployeeSalary::with([
            'employee' => function ($q) {
                $q->select('emp_id', 'emp_full_name', 'emp_esic_no');
            }
        ])
            ->where('ps_payroll_id', $payrollId)
            ->where('ps_monthly_gross', '<', $esicThreshold) // ✅ Filter by threshold
            ->get();
        return Excel::download(new class($businessName, $payroll, $empEsicData) implements \Maatwebsite\Excel\Concerns\WithMultipleSheets {
            protected $business, $payroll, $employees;
            public function __construct($business, $payroll, $employees)
            {
                $this->business = $business;
                $this->payroll = $payroll;
                $this->employees = $employees;
            }
            public function sheets(): array
            {
                return [
                    new class($this->business, $this->payroll, $this->employees) implements
                        \Maatwebsite\Excel\Concerns\FromArray,
                        \Maatwebsite\Excel\Concerns\WithHeadings,
                        \Maatwebsite\Excel\Concerns\WithTitle,
                        \Maatwebsite\Excel\Concerns\WithStyles {
                        protected $business, $payroll, $employees;
                        public function __construct($business, $payroll, $employees)
                        {
                            $this->business = $business;
                            $this->payroll = $payroll;
                            $this->employees = $employees;
                        }
                        public function array(): array
                        {
                            $data = [];
                            foreach ($this->employees as $salary) {
                                $emp = $salary->employee;
                                $data[] = [
                                    $emp->emp_esic_no ?? '',
                                    $emp->emp_full_name ?? '',
                                    $salary->ps_esic_worked_days ?? 0,
                                    $salary->ps_esic_monthly_gross ?? 0,
                                    '',
                                    '',
                                ];
                            }
                            return $data;
                        }
                        public function headings(): array
                        {
                            return [
                                'IP Number (10 Digits)',
                                'IP Name (Only alphabets and space)',
                                'No of Days for which wages paid/payable during the month',
                                'Total Monthly Wages',
                                'Reason Code for Zero workings days(numeric only: provide 0 for all other reasons- Click on the link for reference)',
                                'Last Working Day ( Format DD/MM/YYYY or DD-MM-YYYY)',
                            ];
                        }
                        public function title(): string
                        {
                            return 'Sheet 1';
                        }
                        public function styles(Worksheet $sheet)
                        {
                            $sheet->getColumnDimension('A')->setWidth(20);
                            $sheet->getColumnDimension('B')->setWidth(30);
                            $sheet->getColumnDimension('C')->setWidth(35);
                            $sheet->getColumnDimension('D')->setWidth(25);
                            $sheet->getColumnDimension('E')->setWidth(60);
                            $sheet->getColumnDimension('F')->setWidth(35);
                            $sheet->getStyle('A1:F1')->applyFromArray([
                                'font' => [
                                    'bold' => true,
                                    'color' => ['argb' => Color::COLOR_RED],
                                    'size' => 11,
                                ],
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['argb' => 'CCFFFF'],
                                ],
                                'alignment' => [
                                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                                    'vertical' => Alignment::VERTICAL_CENTER,
                                    'wrapText' => true,
                                ],
                                'borders' => [
                                    'allBorders' => [
                                        'borderStyle' => Border::BORDER_THIN,
                                        'color' => ['argb' => '000000'],
                                    ],
                                ],
                            ]);
                            $sheet->getStyle('E1')->getFont()->getColor()->setARGB(Color::COLOR_BLACK);
                            $sheet->getStyle('F1')->getFont()->getColor()->setARGB(Color::COLOR_RED);
                            $sheet->getStyle('A2:F100')->applyFromArray([
                                'alignment' => [
                                    'vertical' => Alignment::VERTICAL_CENTER,
                                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                                    'wrapText' => true,
                                ],
                                'borders' => [
                                    'allBorders' => [
                                        'borderStyle' => Border::BORDER_THIN,
                                        'color' => ['argb' => '000000'],
                                    ],
                                ],
                            ]);
                        }
                    },
                    new class implements
                        \Maatwebsite\Excel\Concerns\FromArray,
                        \Maatwebsite\Excel\Concerns\WithTitle,
                        \Maatwebsite\Excel\Concerns\WithStyles,
                        \Maatwebsite\Excel\Concerns\WithColumnWidths {
                        public function array(): array
                        {
                            return [
                                ['Reason', 'Code', 'Note'],
                                ['Without Reason', '0', 'Leave last working day as blank'],
                                ['On Leave', 1, 'Leave last working day as blank'],
                                ['Left Service', 2, 'Provide last working day (dd/mm/yyyy). IP will not appear from next wage period'],
                                ['Retired', 3, 'Provide last working day (dd/mm/yyyy). IP will not appear from next wage period'],
                                ['Out of Coverage', 4, 'Only for April wages. IP continues otherwise'],
                                ['Expired', 5, 'Provide last working day (dd/mm/yyyy). IP will not appear from next wage period'],
                                ['Non Implemented area', 6, 'Leave last working day as blank'],
                                ['Compliance by Immediate Effect', 7, 'Leave last working day as blank'],
                                ['Suspension of work', 8, 'Leave last working day as blank'],
                                ['Strike/Lockout', 9, 'Leave last working day as blank'],
                                ['Retrenchment of work', 10, 'Provide last working day (dd/mm/yyyy). IP will not appear from next wage period'],
                                ['No Work', 11, 'Leave last working day as blank'],
                                ['Doesn’t Belong To This Employer', 12, 'Leave last working day as blank'],
                                ['Duplicate IP', 13, 'Leave last working day as blank'],
                                [],
                                ['Click Here to Go back to Data Entry Page'],
                                [],
                                ['Instructions to fill in the excel file:'],
                                ['1. Enter the IP number,  IP name, No. of Days, Total Monthly Wages, Reason for 0 wages(If Wages ‘0’) & Last Working Day( only if employee
                                    has left service, Retired, Out of coverage, Expired, Non-Implemented area or Retrenchment. For other reasons,  last working day  must be left  BLANK).'],
                                ['2. Number of days must me a whole number.  Fractions should be rounded up to next higher whole number/integer'],
                                ['3. Excel sheet upload will lead to successful transaction only when all the Employees’ (who are currently mapped in the system) details
                                      are entered perfectly in the excel sheet'],
                                ['4. Reasons are to be assigned numeric code  and date has to be provided as mentioned in the table above'],
                                ["5. Once  0 wages given and last working day is mentioned as in reason codes (2,3,4,5,10)  IP will be removed from the employer’s record. Subsequent months will not have this IP listed under the employer. Last working day should be mentioned only if 'Number of days wages paid/payable' is '0'."],
                                ['6. In case IP has worked for part of the month(i.e. atleast 1 day wage is paid/payable) and left in between of the month, then last working day shouldn’t be mentioned.'],
                                ['7. Calculations – IP Contribution and Employer contribution calculation will be automatically done by the system'],
                                ['8. Date  column format is  dd/mm/yyyy or dd-mm-yyyy.  Pad single digit dates with 0.  Eg:- 2/5/2010  or  2-May-2010 is NOT acceptable.  Correct format  is 02/05/2010
                                     or 02-05-2010'],
                                ['9. Excel file should be saved in .xls format (Excel 97-2003)'],
                                ['10a. To convert  all columns to text,'],
                                ['      a.  Select column A; Click Data in Menu Bar on top;  Select Text to Columns ; Click Next (keep default selection of Delimited);  Click Next (keep default selection of Tab); Select  TEXT;  Click FINISH.  Excel 97 – 2003 as well have TEXT to COLUMN  conversion facility'],
                                ['      b.  Repeat the above step for each of the 6 columns. (Columns A – F )'],
                                ['10b.   Another method that can be used to text conversion is – copy the column with data and paste it in NOTEPAD.  Select the column (in excel) and convert to text. Copy the data back from notepad to excel'],
                                ["11.   If problem continues while upload,  download a fresh template by clicking 'Sample MC Excel Template'. Then copy the data area from Step 8a.a – eg:  copy Cell A2 to F8 (if there is data in 8 rows); Paste it in cell A2 in the fresh template. Upload it "],
                                ['Note :   Kindly turn  OFF   ‘POP UP BLOCKER’  if it is ON in your  browser.  Follow the steps given to turn off  pop up blocker . This  is required to  upload Monthly contribution,  view or print  Challan /  TIC after uploading the excel   '],
                                ['         1.Mozilla Firefox  3.5.11 :  From Menu Bar, select   Tools à Options à Content à Uncheck (remove tick mark) ‘Block Popup Windows’.   Click OK'],
                                ['         2.  IE 7.0  :     From Menu Bar, select  Tools à Pop up Blocker à Turn Off Pop up Blocker ']
                            ];
                        }
                        public function title(): string
                        {
                            return 'Instructions & Reason Codes';
                        }
                        public function styles(Worksheet $sheet)
                        {
                            // $sheet->getStyle('A1:C1')->getFont()->setBold(true);
                            $sheet->getStyle('A1:C1')->applyFromArray([
                                'font' => [
                                    'bold' => true,
                                ],
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['argb' => 'CCFFFF'], // light cyan
                                ],
                                'alignment' => [
                                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                                    'vertical' => Alignment::VERTICAL_CENTER,
                                    'wrapText' => true,
                                ],
                                'borders' => [
                                    'allBorders' => [
                                        'borderStyle' => Border::BORDER_THIN,
                                        'color' => ['argb' => '000000'],
                                    ],
                                ],
                            ]);
                            // $sheet->setCellValue('A16', 'Click Here to Go back to Data Entry Page');
                            // $sheet->getCell('A16')->getHyperlink()->setUrl("internal:'Sheet1'!A1");
                            // $sheet->setCellValue('A16', 'Click Here to Go back to Data Entry Page');
                            $sheet->getCell('A16')->setHyperlink(
                                new Hyperlink("#'Sheet 1'!A1", 'Click Here to Go back to Data Entry Page')
                            );
                            // Optional: Style it like a clickable link (blue and underlined)
                            $sheet->getStyle('A16')->applyFromArray([
                                'font' => [
                                    'bold' => true,
                                    'size' => 14,
                                    'color' => ['argb' => \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED],
                                    'underline' => \PhpOffice\PhpSpreadsheet\Style\Font::UNDERLINE_SINGLE,
                                ],
                            ]);
                            $sheet->getStyle('A17')->getFont()->setBold(true);
                            for ($row = 1; $row <= 15; $row++) {
                                $sheet->getStyle("A{$row}:C{$row}")->applyFromArray([
                                    'borders' => [
                                        'allBorders' => [
                                            'borderStyle' => Border::BORDER_THIN,
                                            'color' => ['argb' => '000000'],
                                        ],
                                    ],
                                    'alignment' => [
                                        'vertical' => Alignment::VERTICAL_CENTER,
                                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                                        'wrapText' => true,
                                    ],
                                ]);
                            }
                            for ($row = 18; $row <= 34; $row++) {
                                $sheet->mergeCells("A{$row}:G{$row}");
                                $sheet->getStyle("A{$row}")->applyFromArray([
                                    'font' => [
                                        'size' => 11, // Set smaller font size
                                    ],
                                    'alignment' => [
                                        'wrapText' => true,
                                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                                    ],
                                ]);
                                $sheet->getRowDimension($row)->setRowHeight(30);
                            }
                        }
                        public function columnWidths(): array
                        {
                            return [
                                'A' => 45,
                                'B' => 10,
                                'C' => 90,
                            ];
                        }
                    },
                ];
            }
        }, 'MC_Template.xlsx');
    }
    public function bankSheetReportExport(Request $request)
    {
        $user = Auth::user();
        $selectedFields = $request->input('columns', []);
        $fields = $request->input('fields', []);
        $payrollPeriodId = $request->input('payroll_period');
        $amountCheck = 0;
        // Validate payroll period
        if (!$payrollPeriodId) {
            return redirect()->back()->with('error', 'Please select a Payroll Period.');
        }
        $payroll = PayrollPeriod::find($payrollPeriodId);
        if (!$payroll) {
            return redirect()->back()->with('error', 'Invalid Payroll Period selected.');
        }
        if ($payroll->pp_is_processed == 121) {
            return redirect()->back()->with([
                'swal' => true,
                'swal.icon' => 'warning',
                'swal.title' => 'Not Processed!',
                'swal.text' => 'Salaries are not processed for the selected Payroll Period.',
            ]);
        }
        // Validate field selection
        if (empty($selectedFields) && empty($fields)) {
            return redirect()->back()->with('error', 'Please select at least one field to export.');
        }
        // Fetch data
        // $data = Employee::with([
        //     'employee_salaries' => function ($query) use ($fields, $payrollPeriodId, $user) {
        //         $query->select(array_merge(['es_emp_id'], $fields))
        //             ->where('es_b_id', $user->emp_b_id);
        //     },
        //     'processedSalaries' => function ($query) use ($payrollPeriodId) {
        //         $query->where('ps_payroll_id', $payrollPeriodId);
        //     }
        // ])
        //     ->where([
        //         'emp_b_id' => $user->emp_b_id,
        //         'emp_status' => 71,
        //     ])
        //     ->whereHas('employee_salaries', function ($query) use ($payrollPeriodId, $user) {
        //         $query->where('es_b_id', $user->emp_b_id);
        //     })
        //     ->select(array_merge(['emp_id'], $selectedFields))
        //     ->get();
        // $data = Employee::with([
        //     'processedSalaries' => function ($q) use ($payrollPeriodId) {
        //         $q->where('ps_payroll_id', $payrollPeriodId);
        //     }
        // ])
        //     ->where('emp_b_id', $user->emp_b_id)
        //     ->where('emp_status', 71)
        //     ->get();


        $data = Employee::with([
            'processedSalaries' => function ($q) use ($payrollPeriodId) {
                $q->where('ps_payroll_id', $payrollPeriodId);
            }
        ])
            ->whereHas('processedSalaries', function ($q) use ($payrollPeriodId) {
                $q->where('ps_payroll_id', $payrollPeriodId);
            })
            ->where('emp_b_id', $user->emp_b_id)
            ->where('emp_status', 71)
            ->get();

        // Flag for amount field
        if (in_array('es_monthly_ctc', $fields)) {
            $amountCheck = 1;
        }
        $business = \DB::table('businesses')->where('b_id', $user->emp_b_id)->first();
        $fileName = 'BankSheetReport_' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(
            new BankSheetExport($data, $user, $selectedFields, $amountCheck, $payroll->pp_b_id, $business, $payroll->pp_name),
            $fileName
        );
    }
}
