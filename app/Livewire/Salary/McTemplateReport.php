<?php

namespace App\Livewire\Salary;

use App\Models\Business;
use App\Models\Employee;
use App\Models\FinancialYear;
use App\Models\MasterTable;
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
// Excel Concerns
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Carbon\Carbon;

class McTemplateReport extends Component
{
    public $selectedPayrollPeriodId;
    public $searchPayroll = '';
    public $businessId;
    public $selectedFYId;
    public $searchFY = '';
    public $search = '';
    public $selectedEmployeeId;
    public $employeeStatusId = null;

    // Add round off property
    public $roundOffValues = false;

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
    public function selectEmployee($id, $name)
    {
        $this->selectedEmployeeId = $id;
        $this->search = $name;
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
        // Generic mapping of search fields to selected fields
        $mapping = [
            'search' => 'selectedEmployeeId',
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
        $business = \App\Models\Business::where('b_id', $business_id)->select('b_name')->first();
        $businessName = $business?->b_name ?? 'Unknown Business';
        $payrollId = $this->selectedPayrollPeriodId;
        $payroll = \App\Models\PayrollPeriod::where('pp_id', $payrollId)
            ->where('pp_b_id', $business_id)
            ->first();
        if (!$payroll) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'Invalid Payroll Period']);
            return;
        }
        $esicThreshold = \App\Models\StatutoryDeduction::where('std_b_id', $business_id)
            ->where('std_deduction_type_id', 352)
            ->value('std_threshold') ?? 21000;
        $empEsicData = \App\Models\ProcessedEmployeeSalary::with([
            'employee' => fn($q) => $q->select('emp_id', 'emp_full_name', 'emp_esic_no', 'emp_status')
        ])
            ->where('ps_payroll_id', $payrollId)
            ->where('ps_monthly_gross', '<', $esicThreshold)
            ->when($this->selectedEmployeeId, fn($q) => $q->where('ps_emp_id', $this->selectedEmployeeId))
            ->when($this->employeeStatusId, function ($q) {
                $q->whereHas('employee', function ($sq) {

                    if ($this->employeeStatusId === 'resigned') {
                        $sq->whereNotNull('emp_last_working_date');
                    } else {
                        $sq->where('emp_status', $this->employeeStatusId);
                    }
                });
            })
            ->get();
        if ($empEsicData->isEmpty()) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'No records found for the selected criteria.']);
            return;
        }
        $fileName = 'ESIC_Monthly_Contribution_' . $payroll->pp_name . '.xlsx';
        return Excel::download(new class($businessName, $payroll, $empEsicData, $this->roundOffValues) implements WithMultipleSheets {
            protected $businessName, $payroll, $employees, $roundOffValues;
            public function __construct($businessName, $payroll, $employees, $roundOffValues)
            {
                $this->businessName = $businessName;
                $this->payroll = $payroll;
                $this->employees = $employees;
                $this->roundOffValues = $roundOffValues;
            }
            public function sheets(): array
            {
                return [
                    // === SHEET 1: DATA ===
                    new class($this->businessName, $this->payroll, $this->employees, $this->roundOffValues) implements FromCollection, WithHeadings, WithCustomStartCell, WithEvents {
                        protected $businessName, $payroll, $employees, $roundOffValues;
                        public function __construct($businessName, $payroll, $employees, $roundOffValues)
                        {
                            $this->businessName = $businessName;
                            $this->payroll = $payroll;
                            $this->employees = $employees;
                            $this->roundOffValues = $roundOffValues;
                        }

                        public function startCell(): string
                        {
                            // Data starts at row 7 (or 8 if round-off is enabled)
                            return $this->roundOffValues ? 'A7' : 'A6';
                        }

                        public function collection()
                        {
                            return collect($this->employees)->map(function ($salary, $index) {
                                $emp = $salary->employee;
                                $monthlyWages = $salary->ps_esic_monthly_gross ?? 0;

                                // Apply rounding if enabled
                                if ($this->roundOffValues) {
                                    $monthlyWages = round($monthlyWages);
                                }

                                return [
                                    'S#' => $index + 1,
                                    'IP Number (10 Digits)' => $emp->emp_esic_no ?? '',
                                    'IP Name (Only alphabets and space)' => $emp->emp_full_name ?? '',
                                    'No of Days for which wages paid/payable during the month' => $salary->ps_esic_worked_days ?? 0,
                                    'Total Monthly Wages' => $monthlyWages,
                                    'Reason Code for Zero workings days(numeric only: provide 0 for all other reasons- Click on the link for reference)' => '',
                                    'Last Working Day ( Format DD/MM/YYYY or DD-MM-YYYY)' => '',
                                ];
                            });
                        }
                        public function headings(): array
                        {
                            return [
                                'S#',
                                'IP Number (10 Digits)',
                                'IP Name (Only alphabets and space)',
                                'No of Days for which wages paid/payable during the month',
                                'Total Monthly Wages',
                                'Reason Code for Zero workings days(numeric only: provide 0 for all other reasons- Click on the link for reference)',
                                'Last Working Day ( Format DD/MM/YYYY or DD-MM-YYYY)',
                            ];
                        }
                        public function registerEvents(): array
                        {
                            return [
                                AfterSheet::class => function (AfterSheet $event) {
                                    $sheet = $event->sheet->getDelegate();
                                    $headings = $this->headings();
                                    $lastCol = Coordinate::stringFromColumnIndex(count($headings));
                                    $dataRows = $this->collection()->count();

                                    // Set up header section
                                    $sheet->setShowGridlines(false);
                                    $printedOn = Carbon::now()->format('d-M-Y h:i A T');
                                    $reportDate = Carbon::now()->format('d-M-Y');

                                    // Set header values
                                    $sheet->setCellValue('A1', $this->businessName);
                                    $sheet->setCellValue('A2', 'Monthly Contribution Report');
                                    $sheet->setCellValue('A3', 'Payroll Period: ' . ($this->payroll->pp_name ?? 'N/A'));
                                    $sheet->setCellValue('A4', "Printed on: {$printedOn}");
                                    $sheet->setCellValue('A5', "Date: {$reportDate}");

                                    // Determine heading row position based on round-off setting
                                    $headingRow = 6;

                                    // Add round-off info to header if enabled
                                    if ($this->roundOffValues) {
                                        $sheet->setCellValue('A6', "Note: All monetary values are rounded to nearest whole number");
                                        $sheet->mergeCells("A6:{$lastCol}6");
                                        $sheet->getStyle("A6")->getFont()->setSize(10)->setBold(true)->setItalic(true);
                                        $sheet->getStyle("A6")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                                        $headingRow = 7; // Move heading row down by 1
                                    }

                                    // Merge header rows (1-5)
                                    foreach (range(1, 5) as $r) {
                                        $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
                                        $sheet->getStyle("A{$r}")->getFont()->setSize(11)->setBold(true);
                                        $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                                    }

                                    // Set column headings at the correct row
                                    $col = 'A';
                                    foreach ($this->headings() as $heading) {
                                        $sheet->setCellValue($col . $headingRow, $heading);
                                        $col++;
                                    }

                                    // Heading Row styling
                                    $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")
                                        ->getFont()->setSize(10)->setBold(true)->getColor()->setRGB('FFFFFF');
                                    $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")
                                        ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('263871');
                                    $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")
                                        ->getAlignment()
                                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                                        ->setVertical(Alignment::VERTICAL_CENTER)
                                        ->setWrapText(true);
                                    $sheet->getRowDimension($headingRow)->setRowHeight(45);

                                    // Calculate data range
                                    $dataStartRow = $this->roundOffValues ? 8 : 7;
                                    $lastDataRow = $dataStartRow + $dataRows - 1;

                                    if ($dataRows > 0) {
                                        for ($r = $dataStartRow; $r <= $lastDataRow; $r++) {
                                            $sheet->getRowDimension($r)->setRowHeight(25);
                                        }
                                        $sheet->getStyle("A{$headingRow}:{$lastCol}{$lastDataRow}")
                                            ->getBorders()->getAllBorders()
                                            ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D3D3D3');
                                        $sheet->getStyle("A{$dataStartRow}:{$lastCol}{$lastDataRow}")
                                            ->getAlignment()
                                            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                                            ->setVertical(Alignment::VERTICAL_CENTER)
                                            ->setWrapText(true);
                                        $sheet->getStyle("A{$dataStartRow}:{$lastCol}{$lastDataRow}")
                                            ->getFont()->setSize(9);
                                        for ($r = $dataStartRow; $r <= $lastDataRow; $r++) {
                                            $bg = (($r - $dataStartRow) % 2 == 0) ? 'F5F5F5' : 'FFFFFF';
                                            $sheet->getStyle("A{$r}:{$lastCol}{$r}")
                                                ->getFill()->setFillType(Fill::FILL_SOLID)
                                                ->getStartColor()->setRGB($bg);
                                        }
                                    }
                                    // Column Widths
                                    $widths = ['A' => 5, 'B' => 20, 'C' => 30, 'D' => 35, 'E' => 25, 'F' => 60, 'G' => 35];
                                    foreach ($widths as $col => $width) {
                                        $sheet->getColumnDimension($col)->setWidth($width);
                                    }
                                    // Freeze Pane - freeze at the data start row
                                    $sheet->freezePane("A{$dataStartRow}");
                                },
                            ];
                        }
                    },
                    // === SHEET 2: INSTRUCTIONS ===
                    new class($this->roundOffValues) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithTitle, \Maatwebsite\Excel\Concerns\WithEvents {
                        protected $roundOffValues;

                        public function __construct($roundOffValues)
                        {
                            $this->roundOffValues = $roundOffValues;
                        }

                        public function array(): array
                        {
                            $data = [
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
                                ['Doesn\'t Belong To This Employer', 12, 'Leave last working day as blank'],
                                ['Duplicate IP', 13, 'Leave last working day as blank'],
                                [],
                                ['Click Here to Go back to Data Entry Page'],
                                [],
                                ['Instructions to fill in the excel file:'],
                                ['1. Enter the IP number, IP name, No. of Days, Total Monthly Wages, Reason for 0 wages(If Wages \'0\') & Last Working Day( only if employee has left service, Retired, Out of coverage, Expired, Non-Implemented area or Retrenchment. For other reasons, last working day must be left BLANK).'],
                                ['2. Number of days must be a whole number. Fractions should be rounded up to next higher whole number/integer'],
                                ['3. Excel sheet upload will lead to successful transaction only when all the Employees\' (who are currently mapped in the system) details are entered perfectly in the excel sheet'],
                                ['4. Reasons are to be assigned numeric code and date has to be provided as mentioned in the table above'],
                                ['5. Once 0 wages given and last working day is mentioned as in reason codes (2,3,4,5,10) IP will be removed from the employer\'s record. Subsequent months will not have this IP listed under the employer. Last working day should be mentioned only if \'Number of days wages paid/payable\' is \'0\'.'],
                                ['6. In case IP has worked for part of the month(i.e. atleast 1 day wage is paid/payable) and left in between of the month, then last working day shouldn\'t be mentioned.'],
                                ['7. Calculations – IP Contribution and Employer contribution calculation will be automatically done by the system'],
                                ['8. Date column format is dd/mm/yyyy or dd-mm/yyyy. Pad single digit dates with 0. Eg:- 2/5/2010 or 2-May-2010 is NOT acceptable. Correct format is 02/05/2010 or 02-05-2010'],
                                ['9. Excel file should be saved in .xls format (Excel 97-2003)'],
                                ['10a. To convert all columns to text,'],
                                ['      a. Select column A; Click Data in Menu Bar on top; Select Text to Columns ; Click Next (keep default selection of Delimited); Click Next (keep default selection of Tab); Select TEXT; Click FINISH. Excel 97 – 2003 as well have TEXT to COLUMN conversion facility'],
                                ['      b. Repeat the above step for each of the 6 columns. (Columns A – F )'],
                                ['10b. Another method that can be used to text conversion is – copy the column with data and paste it in NOTEPAD. Select the column (in excel) and convert to text. Copy the data back from notepad to excel'],
                                ['11. If problem continues while upload, download a fresh template by clicking \'Sample MC Excel Template\'. Then copy the data area from Step 8a.a – eg: copy Cell A2 to F8 (if there is data in 8 rows); Paste it in cell A2 in the fresh template. Upload it'],
                                ['Note : Kindly turn OFF \'POP UP BLOCKER\' if it is ON in your browser. Follow the steps given to turn off pop up blocker. This is required to upload Monthly contribution, view or print Challan / TIC after uploading the excel'],
                                ['         1.Mozilla Firefox 3.5.11 : From Menu Bar, select Tools → Options → Content → Uncheck (remove tick mark) \'Block Popup Windows\'. Click OK'],
                                ['         2. IE 7.0 : From Menu Bar, select Tools → Pop up Blocker → Turn Off Pop up Blocker ']
                            ];

                            // Add round-off note if enabled
                            if ($this->roundOffValues) {
                                array_splice($data, 17, 0, [
                                    [],
                                    ['IMPORTANT NOTE: All monetary values in the Data sheet are rounded to the nearest whole number.']
                                ]);
                            }

                            return $data;
                        }
                        public function title(): string
                        {
                            return 'Instructions & Reason Codes';
                        }
                        public function registerEvents(): array
                        {
                            return [
                                AfterSheet::class => function (AfterSheet $event) {
                                    $sheet = $event->sheet->getDelegate();
                                    $sheet->getStyle('A1:C1')->applyFromArray([
                                        'font' => ['bold' => true],
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
                                    // Hyperlink
                                    $hyperlinkRow = $this->roundOffValues ? 18 : 16;
                                    $cell = $sheet->getCell('A' . $hyperlinkRow);
                                    $cell->setValue('Click Here to Go back to Data Entry Page');
                                    $cell->getHyperlink()->setUrl("internal:'Sheet 1'!A1");
                                    $sheet->getStyle('A' . $hyperlinkRow)->applyFromArray([
                                        'font' => [
                                            'bold' => true,
                                            'size' => 14,
                                            'color' => ['argb' => Color::COLOR_RED],
                                            'underline' => \PhpOffice\PhpSpreadsheet\Style\Font::UNDERLINE_SINGLE,
                                        ],
                                    ]);

                                    // Style round-off note if enabled
                                    if ($this->roundOffValues) {
                                        $sheet->getStyle('A18')->applyFromArray([
                                            'font' => [
                                                'bold' => true,
                                                'italic' => true,
                                                'color' => ['argb' => Color::COLOR_DARKRED],
                                            ],
                                            'fill' => [
                                                'fillType' => Fill::FILL_SOLID,
                                                'startColor' => ['argb' => 'FFFFCC'],
                                            ],
                                        ]);
                                        $sheet->mergeCells('A18:G18');
                                        $sheet->getStyle('A18')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                                    }

                                    $sheet->getStyle('A' . ($hyperlinkRow + 1))->getFont()->setBold(true);
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
                                    $startRow = $this->roundOffValues ? 20 : 18;
                                    for ($row = $startRow; $row <= ($this->roundOffValues ? 36 : 34); $row++) {
                                        $sheet->mergeCells("A{$row}:G{$row}");
                                        $sheet->getStyle("A{$row}")->applyFromArray([
                                            'font' => ['size' => 11],
                                            'alignment' => [
                                                'wrapText' => true,
                                                'vertical' => Alignment::VERTICAL_TOP,
                                            ],
                                        ]);
                                        $sheet->getRowDimension($row)->setRowHeight(30);
                                    }
                                    $sheet->getColumnDimension('A')->setWidth(45);
                                    $sheet->getColumnDimension('B')->setWidth(10);
                                    $sheet->getColumnDimension('C')->setWidth(90);
                                },
                            ];
                        }
                    },
                ];
            }
        }, $fileName);
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
        return view('livewire.salary.mc-template-report', compact('financialYears', 'payrollPeriods', 'employees', 'employeeStatus'));
    }
}
