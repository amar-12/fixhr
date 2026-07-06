<?php

namespace App\Exports\Salary;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
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

    // Define columns that are NOT part of any group (independent)
    protected $independentColumns = [
        'S No.', 'Emp Code', 'Employee Name', 'Company Name', 'Department', 'Designation',
        'Gender', 'Aadhaar No', 'Fathers Name', 'DOB', 'DOJ', 'DOL', 'Last Working Date',
        'Reason of Leaving', 'PF #', 'EPS #', 'UA #', 'Month / Period', 'Days Worked',
        'Arrear Days', 'LOP', 'Gross Salary', 'basic + DA or Basic'
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
            'PF Salary' => $this->filters['pfContribution'] ?? false,
            'Arrear Salary' => $this->filters['pfContribution'] ?? false,
            'MPF' => $this->filters['pfContribution'] ?? false,
            'MPF Arrear' => $this->filters['pfContribution'] ?? false,
            'TOTAL MPF' => $this->filters['pfContribution'] ?? false,
            'CPF' => $this->filters['pfContribution'] ?? false,
            'CPF Arrear' => $this->filters['pfContribution'] ?? false,
            'TOTAL CPF' => $this->filters['pfContribution'] ?? false,
            'VPF' => $this->filters['pfContribution'] ?? false,
            'EPS Salary' => $this->filters['epsContribution'] ?? false,
            'Arrear Salary' => $this->filters['epsContribution'] ?? false,
            'EPS' => $this->filters['epsContribution'] ?? false,
            'EPS Contribution' => $this->filters['epsContribution'] ?? false,
            'EPS Arrear' => $this->filters['epsContribution'] ?? false,
            'TOTAL EPS' => $this->filters['epsContribution'] ?? false,
            'EDLI Salary' => $this->filters['edli'] ?? false,
            'EDLI Arrears Salary' => $this->filters['edli'] ?? false,
            'TOTAL EDLIWAGES' => $this->filters['edli'] ?? false,
            'EDLI Contribution (A/C:21)' => $this->filters['edli'] ?? false,
            'Admin Charges (A/C:22)' => $this->filters['edli'] ?? false,
            'PF Admin Charges (A/C:2)' => $this->filters['edli'] ?? false,
        ];

        // === GROUP HEADER ROW (Row 6) - Only for grouped columns ===
        $groupHeadingRow = array_fill(0, count($allColumns), '');
        foreach ($this->columnGroups as $group => $config) {
            $start = array_search($config['start'], array_keys($allColumns));
            $end = array_search($config['end'], array_keys($allColumns));
            if (($this->filters[$group] ?? false) && $start !== false && $end !== false) {
                $groupHeadingRow[$start] = $config['title'];
            }
        }
        $data[] = $groupHeadingRow;

        // === COLUMN HEADERS (Row 7) ===
        $this->visibleColumns = array_keys(array_filter($allColumns, fn($v) => $v));
        $data[] = $this->visibleColumns;

        $this->lastColumn = $this->getColumnLetter(count($this->visibleColumns));

        // === EMPLOYEE DATA ===
        foreach ($this->employees as $index => $employee) {
            $grossSalary = $employee->ps_monthly_gross ?? 0;
            $basic = $employee->basic_amount;
            $da = $employee->da_amount;

            if (($employee->es_base_salary ?? 0) < 10090) {
                $employeeBasicForPF = ($basic ?? 0) + ($da ?? 0);
            } else {
                $employeeBasicForPF = $basic ?? 0;
            }
            $pfThreshold = 15000;
            $basicForPF = min($employeeBasicForPF, $pfThreshold);
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
                (string)$basicForPF,
                '0',
                (string)$mpf,
                '0',
                (string)$mpf,
                (string)$cpf,
                '0',
                (string)$cpf,
                '0',
                $employee->emp_is_eps_enabled ? (string)$grossSalary : '',
                '0',
                $employee->emp_is_eps_enabled ? (string)$grossSalary : '',
                $employee->emp_is_eps_enabled ? (string)$eps : '',
                '0',
                $employee->emp_is_eps_enabled ? (string)$eps : '',
                (string)$edli,
                '0',
                (string)$edli,
                '0',
                '0.000',
                (string)$admin
            ];

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

    public function headings(): array { return []; }
    public function title(): string { return 'PF EPS Report'; }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->setShowGridlines(false);

                $lastDataRow = 7 + count($this->employees);
                $totalRow = $lastDataRow + 1;
                $abbrStartRow = $totalRow + 2;

                // === HEADER (1–4) ===
                $sheet->setCellValue('A1', $this->businessName);
                $sheet->setCellValue('A2', 'PF And EPS Summary Report');
                $fromDate = Carbon::parse($this->payroll->pp_start_date)->format('d-M-Y');
                $toDate = Carbon::parse($this->payroll->pp_end_date)->format('d-M-Y');
                $sheet->setCellValue('A3', "Period: $fromDate To $toDate");
                $sheet->setCellValue('A4', "Printed: " . Carbon::now()->format('d-M-Y h:i A T'));

                foreach (range(1, 4) as $row) {
                    $sheet->mergeCells("A{$row}:{$this->lastColumn}{$row}");
                    $sheet->getStyle("A{$row}")->getFont()->setSize(11)->setBold(true);
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // === ROW HEIGHTS ===
                foreach ([6, 7] as $r) $sheet->getRowDimension($r)->setRowHeight(25);
                for ($r = 8; $r <= $lastDataRow; $r++) $sheet->getRowDimension($r)->setRowHeight(25);
                $sheet->getRowDimension($totalRow)->setRowHeight(25);

                // === MERGE INDEPENDENT COLUMNS (A6:A7, B6:B7, etc.) ===
                foreach ($this->visibleColumns as $i => $colName) {
                    if (in_array($colName, $this->independentColumns)) {
                        $colLetter = $this->getColumnLetter($i + 1);
                        $sheet->mergeCells("{$colLetter}6:{$colLetter}7");
                        $sheet->setCellValue("{$colLetter}6", $colName);
                    }
                }

                // === APPLY GROUP MERGES (Only Row 6) ===
                $this->applyGroupMerges($sheet);

                // === STYLE: GROUP HEADER (Row 6) - BLUE + WHITE TEXT + WHITE BORDERS ===
                $sheet->getStyle("A6:{$this->lastColumn}6")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '263871']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
                ]);

                // === STYLE: SUB HEADER (Row 7) - SAME AS ROW 6 ===
                $sheet->getStyle("A7:{$this->lastColumn}7")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '263871']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
                ]);

                // === DATA ROWS ===
                $dataRange = "A8:{$this->lastColumn}{$lastDataRow}";
                $sheet->getStyle($dataRange)->applyFromArray([
                    'font' => ['size' => 8],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D3D3D3']]],
                ]);

                // Wrap text except S No. & Emp Code
                foreach ($this->visibleColumns as $i => $col) {
                    $colLetter = $this->getColumnLetter($i + 1);
                    if (!in_array($col, ['S No.', 'Emp Code'])) {
                        $sheet->getStyle("{$colLetter}8:{$colLetter}{$lastDataRow}")->getAlignment()->setWrapText(true);
                    }
                }

                // === TOTAL ROW ===
                $sheet->setCellValue("A{$totalRow}", 'TOTAL');
                foreach ($this->visibleColumns as $i => $colName) {
                    $colLetter = $this->getColumnLetter($i + 1);
                    if (in_array($colName, $this->independentColumns)) {
                        continue;
                    }
                    $sheet->setCellValue("{$colLetter}{$totalRow}", "=SUM({$colLetter}8:{$colLetter}{$lastDataRow})");
                }

                $sheet->getStyle("A{$totalRow}:{$this->lastColumn}{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => [
                        'top' => ['borderStyle' => Border::BORDER_THIN],
                        'bottom' => ['borderStyle' => Border::BORDER_DOUBLE],
                    ],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EFEFEF']],
                ]);

                // === COLUMN WIDTHS ===
                foreach ($this->visibleColumns as $i => $col) {
                    $colLetter = $this->getColumnLetter($i + 1);
                    $width = in_array($col, ['S No.', 'Emp Code']) ? 7 : 13;
                    $sheet->getColumnDimension($colLetter)->setWidth($width);
                }

                // === FREEZE PANE ===
                $sheet->freezePane('A8');

                // === ABBREVIATIONS ===
                $sheet->setCellValue("A{$abbrStartRow}", 'Abbreviations');
                $sheet->mergeCells("A{$abbrStartRow}:{$this->lastColumn}{$abbrStartRow}");
                $sheet->getStyle("A{$abbrStartRow}")->getFont()->setSize(10)->setBold(true);
                $sheet->getStyle("A{$abbrStartRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $abbreviations = [
                    'MPF' => 'Member Provident Fund',
                    'CPF' => 'Company Provident Fund',
                    'VPF' => 'Voluntary Provident Fund',
                    'EPS' => 'Employee Pension Scheme',
                    'EDLI' => 'Employee Deposit Linked Insurance',
                    'LOP' => 'Loss of Pay',
                ];
                $r = $abbrStartRow + 1;
                foreach ($abbreviations as $abbr => $desc) {
                    $sheet->setCellValue("A{$r}", "{$abbr}: {$desc}");
                    $sheet->mergeCells("A{$r}:{$this->lastColumn}{$r}");
                    $sheet->getStyle("A{$r}")->getFont()->setSize(9);
                    $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    $r++;
                }
            },
        ];
    }

    protected function applyGroupMerges(Worksheet $sheet)
    {
        // Clear previous merges in row 6
        $merged = $sheet->getMergeCells();
        foreach ($merged as $range) {
            if (str_contains($range, ':6')) {
                $sheet->unmergeCells($range);
            }
        }

        foreach ($this->columnGroups as $group => $config) {
            if (!($this->filters[$group] ?? false)) continue;

            $startPos = array_search($config['start'], $this->visibleColumns);
            $endPos = array_search($config['end'], $this->visibleColumns);
            if ($startPos === false || $endPos === false || $endPos < $startPos) continue;

            $startCol = $this->getColumnLetter($startPos + 1);
            $endCol = $this->getColumnLetter($endPos + 1);

            if ($startCol !== $endCol) {
                $sheet->mergeCells("{$startCol}6:{$endCol}6");
            }
            $sheet->setCellValue("{$startCol}6", $config['title']);
        }
    }

    protected function getColumnLetter($index)
    {
        $letter = '';
        while ($index > 0) {
            $index--;
            $letter = chr(65 + ($index % 26)) . $letter;
            $index = intval($index / 26);
        }
        return $letter ?: 'A';
    }
}