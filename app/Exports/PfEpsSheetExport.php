<?php
namespace App\Exports;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;
class PfEpsSheetExport implements FromArray, WithHeadings, WithTitle, WithEvents
{
    protected $businessName;
    protected $payroll;
    protected $employees;
    protected $filters;
    protected $visibleColumns = [];
    protected $lastColumn = 'A';
    protected $columnGroups = [
        'pfContribution' => [
            'start' => 'PF Salary',
            'end' => 'VPF',
            'title' => 'Provident Fund Contribution',
        ],
        'epsContribution' => [
            'start' => 'EPS Salary',
            'end' => 'TOTAL EPS',
            'title' => 'EPS Contribution',
        ],
        'edli' => [
            'start' => 'EDLI Salary',
            'end' => 'PF Admin Charges (A/C:2)',
            'title' => 'EDLI',
        ]
    ];
    public function __construct($businessName, $payroll, $employees, $filters)
    {
        $this->businessName = $businessName;
        $this->payroll = $payroll;
        $this->employees = $employees;
        $this->filters = $filters;
    }
    public function array(): array
    {
        $fromDate = Carbon::parse($this->payroll->pp_start_date)->format('d-M-y');
        $toDate = Carbon::parse($this->payroll->pp_end_date)->format('d-M-y');
        $data = [
            [$this->businessName, '', '', 'PF And EPS Summary Report'],
            ['', '', '', "Period: $fromDate To $toDate"],
            ['', '', '', "Printed: " . Carbon::now()->format('d-M-y')],
            [""],
            [""],
        ];
        // Define all columns with their visibility
        $allColumns = [
            'S No.' => true,
            'Emp Code' => $this->filters['employeeName'] ?? false,
            'Employee Name' => $this->filters['employeeName'] ?? false,
            'Company Name' => $this->filters['companyName'] ?? false,
            'Department' => $this->filters['department'] ?? false,
            'Designation' => $this->filters['designation'] ?? false,
            'Gender' => $this->filters['gender'] ?? false,
            'Aadhaar No' => $this->filters['aadhaarNo'] ?? false,
            'Fathers Name' => $this->filters['fathersName'] ?? false,
            'DOB' => $this->filters['dob'] ?? false,
            'DOJ' => $this->filters['doj'] ?? false,
            'DOL' => $this->filters['dol'] ?? false,
            'Last Working Date' => $this->filters['lastWorkingDate'] ?? false,
            'Reason of Leaving' => $this->filters['reasonOfLeaving'] ?? false,
            'PF #' => $this->filters['pf'] ?? false,
            'EPS #' => $this->filters['eps'] ?? false,
            'UA #' => $this->filters['ua'] ?? false,
            'Month / Period' => $this->filters['monthPeriod'] ?? false,
            'Days Worked' => $this->filters['daysWorked'] ?? false,
            'Arrear Days' => $this->filters['arrearDays'] ?? false,
            'LOP' => $this->filters['lop'] ?? false,
            'Gross Salary' => $this->filters['grossSalary'] ?? false,
            'basic + DA or Basic' => $this->filters['basicDa'] ?? false,
            // Provident Fund Contribution group
            'PF Salary' => $this->filters['pfContribution'] ?? false,
            'Arrear Salary' => $this->filters['pfContribution'] ?? false,
            'MPF' => $this->filters['pfContribution'] ?? false,
            'MPF Arrear' => $this->filters['pfContribution'] ?? false,
            'TOTAL MPF' => $this->filters['pfContribution'] ?? false,
            'CPF' => $this->filters['pfContribution'] ?? false,
            'CPF Arrear' => $this->filters['pfContribution'] ?? false,
            'TOTAL CPF' => $this->filters['pfContribution'] ?? false,
            'VPF' => $this->filters['pfContribution'] ?? false,
            // EPS Contribution group
            'EPS Salary' => $this->filters['epsContribution'] ?? false,
            'Arrear Salary' => $this->filters['epsContribution'] ?? false,
            'EPS' => $this->filters['epsContribution'] ?? false,
            'EPS Contribution' => $this->filters['epsContribution'] ?? false,
            'EPS Arrear' => $this->filters['epsContribution'] ?? false,
            'TOTAL EPS' => $this->filters['epsContribution'] ?? false,
            // EDLI group
            'EDLI Salary' => $this->filters['edli'] ?? false,
            'EDLI Arrears Salary' => $this->filters['edli'] ?? false,
            'TOTAL EDLIWAGES' => $this->filters['edli'] ?? false,
            'EDLI Contribution (A/C:21)' => $this->filters['edli'] ?? false,
            'Admin Charges (A/C:22)' => $this->filters['edli'] ?? false,
            'PF Admin Charges (A/C:2)' => $this->filters['edli'] ?? false,
        ];
        // Prepare group heading row (row 6)
        $groupHeadingRow = array_fill(0, count($allColumns), '');
        // Set group headings based on visibility
        foreach ($this->columnGroups as $group => $config) {
            $start = array_search($config['start'], array_keys($allColumns));
            $end = array_search($config['end'], array_keys($allColumns));
            if (($this->filters[$group] ?? false) && $start !== false && $end !== false) {
                $groupHeadingRow[$start] = $config['title'];
            }
        }
        $data[] = $groupHeadingRow;
        // Prepare column headers row (row 7)
        $this->visibleColumns = array_keys(array_filter($allColumns, fn($visible) => $visible));
        $data[] = $this->visibleColumns;
        // Calculate last column based on visible columns
        $this->lastColumn = $this->getColumnLetter(count($this->visibleColumns));
        // Add employee data
        foreach ($this->employees as $index => $employee) {
            // dd($employee);
            $grossSalary = $employee->ps_monthly_gross ?? 0;
            // $daAmount = $employee
            //     ->where('emp_id', $employee->emp_id)
            //     ->where('ps_earning_type_id', 352) // DA
            //     ->pluck('ps_e_amount')
            //     ->first() ?? 0;
            $basic = $employee->basic_amount;
            $da = $employee->da_amount;

            // --- New Logic for PF/EPS base calculation ---
            if (($employee->es_base_salary ?? 0) < 10090) {
                $employeeBasicForPF = ($basic ?? 0) + ($da ?? 0);
            } else {
                $employeeBasicForPF = $basic ?? 0;
            }

            // Apply PF threshold (default 15000 if not provided)
            $pfThreshold = 15000;
            $basicForPF = min($employeeBasicForPF, $pfThreshold);

            $pfContribution = $basicForPF;
            $mpf = round($basicForPF * 0.12, 2);
            $cpf = round($basicForPF * (3.67 / 100), 2);
            $eps = round($basicForPF * (8.33 / 100), 2);
            $edli = round($basicForPF * (0.50 / 100), 2);
            $admin = round($basicForPF * (1.5 / 100), 2);
            $row = [
                (string)($index + 1),
                (string)($employee->emp_code ?? 'N/A'),
                (string)($employee->emp_fname ?? 'N/A'),
                (string)($employee->company_name ?? 'N/A'),
                (string)($employee->department ?? 'N/A'),
                (string)($employee->designation ?? 'N/A'),
                (string)($employee->gender ?? 'N/A'),
                '',
                '',
                $employee->emp_dob ? Carbon::parse($employee->emp_dob)->format('d-M-y') : 'N/A',
                $employee->emp_date_of_joining ? Carbon::parse($employee->emp_date_of_joining)->format('d-M-y') : 'N/A',
                '',
                '',
                '',
                (string)($employee->emp_pf_no ?? 'N/A'),
                (string)($employee->emp_eps_no ?? 'N/A'),
                '',
                (string)$this->payroll->pp_name,
                (string)$employee->ps_workable_days,
                '0',
                (string)$employee->ps_upl_count,
                (string)$grossSalary,
                '',
                // PF Group
                (string)$pfContribution,
                '0',
                (string)$mpf,
                '0',
                (string)$mpf,
                (string)$cpf,
                '0',
                (string)$cpf,
                '0',
                // EPS Group
                $employee->emp_is_eps_enabled ? (string)$grossSalary : '',
                '0',
                $employee->emp_is_eps_enabled ? (string)$grossSalary : '',
                $employee->emp_is_eps_enabled ? (string)$eps : '',
                '0',
                $employee->emp_is_eps_enabled ? (string)$eps : '',
                // EDLI Group
                (string)$edli,
                '0',
                (string)$edli,
                '0',
                '0.000',
                (string)$admin
            ];
            // Filter the row based on visible columns
            $filteredRow = [];
            foreach (array_keys($allColumns) as $i => $column) {
                if ($allColumns[$column]) {
                    $filteredRow[] = $row[$i];
                }
            }
            $data[] = $filteredRow;
        }
        return $data;
    }
    public function headings(): array
    {
        return [];
    }
    public function title(): string
    {
        return 'PF EPS Report';
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                // Set title styles (reduced font sizes)
                $sheet->mergeCells("D1:{$this->lastColumn}1");
                $sheet->getStyle('A1:C3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'name' => 'Arial'],
                    'alignment' => ['horizontal' => 'left', 'vertical' => 'top']
                ]);
                $sheet->getStyle('D1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'name' => 'Arial'],
                    'alignment' => ['horizontal' => 'left', 'vertical' => 'top']
                ]);
                $sheet->mergeCells("D2:{$this->lastColumn}2");
                $sheet->getStyle('D2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'name' => 'Arial'],
                    'alignment' => ['horizontal' => 'left', 'vertical' => 'top']
                ]);
                $sheet->mergeCells("D3:{$this->lastColumn}3");
                $sheet->getStyle('D3')->applyFromArray([
                    'font' => ['size' => 9, 'name' => 'Arial'],
                    'alignment' => ['horizontal' => 'left', 'vertical' => 'top']
                ]);
                // Group heading row (row 6)
                $sheet->getRowDimension(6)->setRowHeight(20);
                $sheet->getStyle("A6:{$this->lastColumn}6")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 8, 'name' => 'Arial'],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
                    'borders' => ['outline' => ['borderStyle' => 'thin']],
                ]);
                // Column headers row (row 7)
                $sheet->getRowDimension(7)->setRowHeight(20);
                $sheet->getStyle("A7:{$this->lastColumn}7")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 8, 'name' => 'Arial'],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['argb' => '000000']]],
                ]);
                // Data rows
                $lastDataRow = 7 + count($this->employees);
                $sheet->getStyle("A8:{$this->lastColumn}{$lastDataRow}")->applyFromArray([
                    'font' => ['size' => 8, 'name' => 'Arial'],
                    'alignment' => ['vertical' => 'center', 'wrapText' => true],
                    'borders' => [
                        'outline' => ['borderStyle' => 'thin', 'color' => ['argb' => '000000']],
                        'inside' => ['borderStyle' => 'thin', 'color' => ['argb' => 'DDDDDD']]
                    ]
                ]);

                   // ✅ Add Total Row
                $totalRow = $lastDataRow + 1;
                $sheet->setCellValue("A{$totalRow}", "TOTAL");

                foreach ($this->visibleColumns as $i => $colName) {
                    $colLetter = $this->getColumnLetter($i + 1);

                    // Text columns skip
                    if (in_array($colName, [
                        'S No.',
                        'Emp Code',
                        'Employee Name',
                        'Company Name',
                        'Department',
                        'Designation',
                        'Gender',
                        'Aadhaar No',
                        'Fathers Name',
                        'DOB',
                        'DOJ',
                        'DOL',
                        'Last Working Date',
                        'Reason of Leaving',
                        'PF #',
                        'EPS #',
                        'UA #',
                        'Month / Period'
                    ])) {
                        continue;
                    }

                    // SUM Formula
                    $sheet->setCellValue(
                        "{$colLetter}{$totalRow}",
                        "=SUM({$colLetter}8:{$colLetter}{$lastDataRow})"
                    );
                }

                // Style Total Row
                $sheet->getStyle("A{$totalRow}:{$this->lastColumn}{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'name' => 'Arial'],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                    'borders' => [
                        'top' => ['borderStyle' => 'thin'],
                        'bottom' => ['borderStyle' => 'double'],
                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => ['argb' => 'FFEFEFEF'],
                    ],
                ]);

                // Set column widths
                $sheet->getColumnDimension('A')->setWidth(8);
                $sheet->getColumnDimension('B')->setWidth(12);
                $sheet->getColumnDimension('C')->setWidth(25);
                $sheet->getColumnDimension('D')->setWidth(25);
                $sheet->getColumnDimension('E')->setWidth(20);
                $sheet->getColumnDimension('F')->setWidth(20);
                $sheet->getColumnDimension('G')->setWidth(12);
                // Apply group merges and styles
                $this->applyGroupMerges($sheet);
            },
        ];
    }
    protected function applyGroupMerges(Worksheet $sheet)
    {
        // First clear any existing merged cells in row 6
        $mergedCells = $sheet->getMergeCells();
        foreach ($mergedCells as $mergeRange) {
            if (str_contains($mergeRange, '6:')) {
                $sheet->unmergeCells($mergeRange);
            }
        }
        foreach ($this->columnGroups as $group => $config) {
            if (!($this->filters[$group] ?? false)) {
                continue;
            }
            $startPos = array_search($config['start'], $this->visibleColumns);
            $endPos = array_search($config['end'], $this->visibleColumns);
            if ($startPos === false || $endPos === false || $endPos < $startPos) {
                continue;
            }
            $startCol = $this->getColumnLetter($startPos + 1);
            $endCol = $this->getColumnLetter($endPos + 1);
            // Merge only if there are multiple columns in this group
            if ($startCol !== $endCol) {
                $sheet->mergeCells("{$startCol}6:{$endCol}6");
            }
            $sheet->setCellValue("{$startCol}6", $config['title']);
        }
    }
    protected function getColumnLetter($index)
    {
        $letters = '';
        while ($index >= 0) {
            $letters = chr(65 + ($index % 26)) . $letters;
            $index = (int)($index / 26) - 1;
        }
        return $letters ?: 'A';
    }
}
