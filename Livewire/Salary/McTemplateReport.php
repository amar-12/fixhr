<?php

namespace App\Livewire\Salary;

use App\Models\Business;
use App\Models\FinancialYear;
use App\Models\PayrollPeriod;
use App\Models\ProcessedEmployeeSalary;
use App\Models\StatutoryDeduction;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Hyperlink;


class McTemplateReport extends Component
{
    public $selectedPayrollPeriodId;
    public $searchPayroll = '';
    public $businessId;
    public $selectedFYId;
    public $searchFY = '';
    public function mount()
    {
        $user = Auth::user();
        $this->businessId = $user->emp_b_id;
        $this->selectedFYId = FinancialYear::where('fy_b_id', $user->emp_b_id)->where('fy_is_current', 1)->first();
        $this->selectFY($this->selectedFYId->fy_id, $this->selectedFYId->fy_year);
        // dd($this->searchFY);
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
        // Generic mapping of search fields to selected fields
        $mapping = [
            'searchPayroll' => 'selectedPayrollPeriodId',
            'searchFY' => 'selectedFYId',
        ];
        // Apply the generic reset logic
        if (array_key_exists($property, $mapping)) {
            $this->{$mapping[$property]} = null;
        }
    }

    public function generateReport()
    {

         $this->validate([
            'selectedFYId' => 'required|exists:financial_years,fy_id',
            'selectedPayrollPeriodId' => 'required|exists:payroll_periods,pp_id',

        ], [
            'selectedFYId.required' => 'Financial year is required.',
            'selectedPayrollPeriodId.required' => 'Payroll period is required.',
        ]);

      
        $user = Auth::user();
        $business_id = $user->emp_b_id;
        $business = Business::where('b_id', $business_id)->select('b_name')->first();
        $businessName = $business ? $business->b_name : 'Unknown Business';
        $payrollId = $this->selectedPayrollPeriodId;
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
            ->where('ps_monthly_gross', '<', $esicThreshold)
            ->get();

            if ($empEsicData->isEmpty()) {
                return back()->with('error', 'No records found for the selected payroll period.');
            }
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
                            // $sheet->getCell('A16')->setHyperlink(
                            //     new Hyperlink("#'Sheet 1'!A1", 'Click Here to Go back to Data Entry Page')
                            // );
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

    public function render()
    {
        $financialYears = FinancialYear::where('fy_b_id', $this->businessId)->when($this->searchFY, function ($q) {
            $q->where('fy_year', 'like', "%{$this->searchFY}%");
        })->get();
        $payrollPeriods = PayrollPeriod::where('pp_b_id', $this->businessId)
            ->when($this->selectedFYId, function ($q) {
                $q->where('pp_fy_id', $this->selectedFYId);
            })
            ->when(
                strlen($this->searchPayroll) >= 1 && !$this->selectedPayrollPeriodId,
                fn($q) => $q->where('pp_name', 'like', "%{$this->searchPayroll}%")
            )
            ->limit(100)
            ->get();
        return view('livewire.salary.mc-template-report', compact('financialYears', 'payrollPeriods'));
    }
}
