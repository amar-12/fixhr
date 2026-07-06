<?php
namespace App\Exports\Combined;

use App\Models\Business;
use App\Models\PayrollPeriod;
use App\Models\ProcessedEmployeeSalary;
use App\Models\ProcessedSalaryDeduction;
use App\Models\ProcessedSalaryEarning;
use App\Models\SalaryEmployeeDeductions;
use App\Models\SalaryEmployeeSalary;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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

class ESICSheet implements FromCollection, WithTitle, WithHeadings, WithEvents, WithCustomStartCell, WithColumnWidths
{
    protected $payrollPeriodId;
    protected $businessId;
    protected $roundOffValues;
    protected $data;
    protected $businessName;
    protected $monthName;
    protected $basicInfoColumns;
    protected $processedSalary;

    public function __construct($payrollPeriodId, $businessId, $roundOffValues)
    {
        $this->payrollPeriodId = $payrollPeriodId;
        $this->businessId = $businessId;
        $this->roundOffValues = $roundOffValues;

        $this->initializeData();
    }

    protected function initializeData()
    {


        /* ------------------------------------------------------------------ */
        /* 4. Columns */
        /* ------------------------------------------------------------------ */
        $this->basicInfoColumns = [
            'S#',
            'Emp Code',
            'ESI No.',
            'Employee Name',
            'Gender',
            'Aadhaar No',
            'DOB',
            'DOJ',
            'DOL',
            'Last Working Date',
            'Department',
            'Designation',
            'Days',
            'Arrear Days',
            'Amount on Which ESI Deducted',
            'Arrear Amount on Which ESI Deducted',
            'ESI',
            'Arrear ESI',
            'Employer Contribution',
            'Arrear Employer Contribution',
            'Total',
            'Arrear Total'
        ];


        $payroll = PayrollPeriod::where('pp_b_id', $this->businessId)
            ->where('pp_is_processed', 120)
            ->find($this->payrollPeriodId);


        if (!$payroll) {
            $this->data = collect();
            return;
        }
        $this->businessName = Business::where('b_id', $this->businessId)->value('b_name') ?? 'Unknown Business';

        // $this->businessName = optional($payroll->business)->b_name ?? 'Business';
        $this->monthName = $payroll->pp_name ?? 'Month';

        /* ------------------------------------------------------------------ */
        /* 1. Base query */
        /* ------------------------------------------------------------------ */
        $query = ProcessedEmployeeSalary::with([
            'employee.fh_gender',
            'employee.fh_department',
            'employee.fh_designation',
            'employee.employeeProjects',
        ])
            ->where('ps_payroll_id', $this->payrollPeriodId);

        $this->processedSalary = $query->get();

        if ($this->processedSalary->isEmpty()) {
            $this->data = collect();
            return;
        }

        /* ------------------------------------------------------------------ */
        /* 2. Pre-load data */
        /* ------------------------------------------------------------------ */
        $psIds   = $this->processedSalary->pluck('ps_id');
        $empIds  = $this->processedSalary->pluck('ps_emp_id');

        $allEarnings = ProcessedSalaryEarning::whereIn('ps_id', $psIds)
            ->get()
            ->groupBy('ps_id')
            ->map(fn($g) => $g->pluck('ps_e_amount', 'ps_earning_type'));

        $allDeductions = ProcessedSalaryDeduction::whereIn('ps_id', $psIds)
            ->get()
            ->groupBy('ps_id')
            ->map(fn($g) => $g->pluck('ps_d_amount', 'ps_deduction_type'));

        $allSalary = SalaryEmployeeSalary::where('es_b_id', $this->businessId)
            ->whereIn('es_emp_id', $empIds)
            ->with('salary_earnings.fh_salary_earning_type')
            ->get()
            ->groupBy('es_emp_id');

        $allSalaryDed = SalaryEmployeeDeductions::with('fh_salary_deduction_type')
            ->whereIn('es_d_emp_id', $empIds)
            ->get()
            ->groupBy('es_d_emp_id')
            ->map(fn($g) => $g->pluck('es_d_amount', 'fh_salary_deduction_type.m_name'));

        $employeeSalary = SalaryEmployeeSalary::with('fh_employee')
            ->where('es_b_id', $this->businessId)
            ->whereIn('es_emp_id', $empIds)
            ->get();

        /* ------------------------------------------------------------------ */
        /* 3. ESI filter - UPDATED TO HANDLE NULL/EMPTY VALUES */
        /* ------------------------------------------------------------------ */
        $threshold = DB::table('statutory_deductions')
            ->where('std_b_id', $this->businessId)
            ->where('std_deduction_type_id', 352)
            ->value('std_threshold');

        $this->processedSalary = $this->processedSalary
            ->filter(function ($item) {
                // 1. Employee record missing
                if (empty($item->employee)) {
                    return false;
                }

                // 2. Check ESIC limit status
                // If emp_esic_limit is null or empty, default to false (not eligible)
                $esicLimit = $item->employee->emp_esic_limit ?? 0;
                if ($esicLimit != 120) {
                    return false;
                }

                // 3. ESI Number must be present AND not empty/null
                // Handle null, empty string, or whitespace-only values
                $esicNo = $item->employee->emp_esic_no ?? '';
                $esicNo = trim($esicNo); // Remove whitespace

                if (empty($esicNo)) {
                    return false; // Filter out if ESI number is empty/null after trimming
                }

                return true;
            })
            ->values();

        if ($this->processedSalary->isEmpty()) {
            $this->data = collect();
            return;
        }

        /* ------------------------------------------------------------------ */
        /* 5. Transform - UPDATED TO HANDLE NULL/EMPTY VALUES */
        /* ------------------------------------------------------------------ */
        $this->data = $this->processedSalary->map(function ($item, $key) use ($employeeSalary) {
            $monthlyGross = $item->ps_monthly_gross ?? 0;

            // Handle null monthly gross
            if (is_null($monthlyGross) || !is_numeric($monthlyGross)) {
                $monthlyGross = 0;
            }

            $employeeEsic = $monthlyGross * 0.75 / 100;
            $employerEsic = $monthlyGross * 3.25 / 100;

            // Apply rounding if enabled
            if ($this->roundOffValues) {
                $monthlyGross = round($monthlyGross);
                $employeeEsic = round($employeeEsic);
                $employerEsic = round($employerEsic);
            }

            // Helper function to safely get employee data with null handling
            $getEmployeeField = function($field, $default = '-') use ($item) {
                if (empty($item->employee)) {
                    return $default;
                }

                $value = data_get($item->employee, $field);

                // Handle null, empty string, or whitespace-only
                if (is_null($value) || (is_string($value) && trim($value) === '')) {
                    return $default;
                }

                // Handle numeric values
                if (is_numeric($value)) {
                    return (string) $value;
                }

                // Handle Carbon/DateTime objects
                if ($value instanceof \DateTime) {
                    return $value->format('d M, Y');
                }

                return (string) $value;
            };

            // Safely get related model data
            $getRelatedField = function($relation, $field, $default = '-') use ($item) {
                if (empty($item->employee) || empty($item->employee->$relation)) {
                    return $default;
                }

                $value = data_get($item->employee->$relation, $field);

                if (is_null($value) || (is_string($value) && trim($value) === '')) {
                    return $default;
                }

                return (string) $value;
            };

            return [
                'S#' => (string) ($key + 1),
                'Emp Code' => $getEmployeeField('emp_code'),
                'ESI No.' => $getEmployeeField('emp_esic_no'), // Now will return '-' if empty/null
                'Employee Name' => $getEmployeeField('emp_full_name'),
                'Gender' => $getRelatedField('fh_gender', 'm_name'),
                'Aadhaar No' => $getEmployeeField('emp_aadhaar_no'),
                'DOB' => $item->employee && $item->employee->emp_dob ?
                       Carbon::parse($item->employee->emp_dob)->format('d M, Y') : '-',
                'DOJ' => $item->employee && $item->employee->emp_date_of_joining ?
                        Carbon::parse($item->employee->emp_date_of_joining)->format('d M, Y') : '-',
                'DOL' => '-',
                'Last Working Date' => $item->employee && $item->employee->emp_last_working_date ?
                                      Carbon::parse($item->employee->emp_last_working_date)->format('d M, Y') : '-',
                'Department' => $getRelatedField('fh_department', 'd_name'),
                'Designation' => $getRelatedField('fh_designation', 'dg_name'),
                'Days' => number_format($item->ps_total_days_worked ?? 0, 2, '.', ''),
                'Arrear Days' => '0.00',
                'Amount on Which ESI Deducted' => number_format($monthlyGross, 2, '.', ''),
                'Arrear Amount on Which ESI Deducted' => '0.00',
                'ESI' => number_format($employeeEsic, 2, '.', ''),
                'Arrear ESI' => '0.00',
                'Employer Contribution' => number_format($employerEsic, 2, '.', ''),
                'Arrear Employer Contribution' => '0.00',
                'Total' => number_format($employeeEsic + $employerEsic, 2, '.', ''),
                'Arrear Total' => '0.00',
            ];
        });
    }

    public function title(): string
    {
        return 'ESIC Report';
    }

    public function collection()
    {


        if ($this->data->isEmpty()) {

            $emptyRow = collect($this->basicInfoColumns)->mapWithKeys(function ($col, $index) {
                return [$col => $index === 0 ? 'No ESI-eligible employees found for the selected payroll period' : ''];
            })->toArray();

            return collect([$emptyRow]);
        }


        $rows = collect($this->data);

        // Calculate grand totals
        $grand = collect($this->basicInfoColumns)->mapWithKeys(function ($col) use ($rows) {
            if (in_array($col, [
                'S#',
                'Emp Code',
                'ESI No.',
                'Employee Name',
                'Gender',
                'Aadhaar No',
                'DOB',
                'DOJ',
                'DOL',
                'Last Working Date',
                'Department',
                'Designation'
            ])) {
                return [$col => $col === 'S#' ? 'Grand Totals' : ''];
            }

            $sum = $rows->sum(function($r) use ($col) {
                $value = $r[$col] ?? '0';
                // Remove commas and convert to float
                return floatval(str_replace(',', '', $value));
            });

            $sum = $this->roundOffValues ? round($sum) : $sum;
            return [$col => $sum > 0 ? number_format($sum, 2, '.', '') : ''];
        })->all();

        $rows->push($grand);
        return $rows;
    }

    public function headings(): array
    {
        return [
            [$this->businessName],
            ['ESI REPORT FOR THE MONTH OF ' . strtoupper($this->monthName)],
            ['Printed on: ' . Carbon::now()->format('d-M-Y h:i A T')],
            array_fill(0, count($this->basicInfoColumns), ''),
            $this->basicInfoColumns,
        ];
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function columnWidths(): array
    {
        $widths = [];
        foreach ($this->basicInfoColumns as $idx => $header) {
            $colLetter = Coordinate::stringFromColumnIndex($idx + 1);
            if (in_array($header, ['S#', 'Emp Code'])) {
                $widths[$colLetter] = 7;
            } elseif (in_array($header, ['Employee Name', 'Department', 'Designation'])) {
                $widths[$colLetter] = 20;
            } elseif ($header === 'ESI No.') {
                $widths[$colLetter] = 15;
            } else {
                $widths[$colLetter] = 13;
            }
        }
        return $widths;
    }

    public function registerEvents(): array
    {
        // if ($this->data->isEmpty()) {
        //     return [];
        // }

        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $dataCols = count($this->basicInfoColumns);
                $lastCol = Coordinate::stringFromColumnIndex($dataCols);

                // Adjust for headers (we have 5 header rows)
                $dataStartRow = 6;
                $dataEndRow = $highestRow;
                $dataRange = "A{$dataStartRow}:{$lastCol}{$dataEndRow}";

                /* ==================== TITLE (1-3) ==================== */
                $highestCol = $sheet->getHighestColumn();
                foreach (range(1, 3) as $r) {
                    $sheet->mergeCells("A{$r}:{$highestCol}{$r}");
                    $sheet->getStyle("A{$r}")->getFont()->setBold(true)->setSize(11);
                    $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                $sheet->setCellValue('A1', $this->businessName);
                $sheet->setCellValue('A2', 'ESI REPORT FOR THE MONTH OF ' . strtoupper($this->monthName));
                $sheet->setCellValue('A3', 'Printed on: ' . Carbon::now()->format('d-M-Y h:i A T'));

                // Add round-off info to header if enabled
                if ($this->roundOffValues) {
                    $sheet->setCellValue('A4', "Note: All monetary values are rounded to nearest whole number");
                    $sheet->mergeCells("A4:{$highestCol}4");
                    $sheet->getStyle("A4")->getFont()->setSize(10)->setBold(true)->setItalic(true);
                    $sheet->getStyle("A4")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                    // Adjust row numbers for data start
                    $dataStartRow = 7;
                    $dataRange = "A{$dataStartRow}:{$lastCol}{$dataEndRow}";
                }

                /* ==================== HEADER ROW (5) ==================== */
                $headerRow = $this->roundOffValues ? 6 : 5;

                $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")
                    ->getFill()->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('263871');
                $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")
                    ->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('FFFFFF');
                $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);

                $sheet->setShowGridlines(false);

                /* ==================== DATA STYLE ==================== */
                $sheet->getStyle($dataRange)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setWrapText(true);
                $sheet->getStyle($dataRange)->getFont()->setSize(8);

                // Set row heights
                for ($r = $dataStartRow; $r <= $dataEndRow; $r++) {
                    $sheet->getRowDimension($r)->setRowHeight(25);
                }

                /* ==================== BORDERS ==================== */
                $sheet->getStyle("A{$headerRow}:{$lastCol}{$dataEndRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->getColor()->setRGB('D3D3D3');


                if ($this->data->isEmpty()) {

                    // Merge message row
                    $sheet->mergeCells("A{$dataStartRow}:{$lastCol}{$dataStartRow}");

                    // Center align
                    $sheet->getStyle("A{$dataStartRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);

                    // Bold message
                    $sheet->getStyle("A{$dataStartRow}")
                        ->getFont()
                        ->setBold(true)
                        ->setSize(10);

                    // Apply border for structure
                    $sheet->getStyle("A{$headerRow}:{$lastCol}{$dataStartRow}")
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN);

                    // Set row height
                    $sheet->getRowDimension($dataStartRow)->setRowHeight(25);

                    return; // ✅ NOW safe (after styling)
                }

                /* ==================== CURRENCY FORMAT ==================== */
                $moneyCols = [
                    'Days',
                    'Arrear Days',
                    'Amount on Which ESI Deducted',
                    'Arrear Amount on Which ESI Deducted',
                    'ESI',
                    'Arrear ESI',
                    'Employer Contribution',
                    'Arrear Employer Contribution',
                    'Total',
                    'Arrear Total'
                ];

                foreach ($moneyCols as $col) {
                    $idx = array_search($col, $this->basicInfoColumns);
                    if ($idx !== false) {
                        $letter = Coordinate::stringFromColumnIndex($idx + 1);
                        $sheet->getStyle("{$letter}{$dataStartRow}:{$letter}{$dataEndRow}")
                            ->getNumberFormat()
                            ->setFormatCode('#,##0.00');
                    }
                }

                /* ==================== ALTERNATING ROWS ==================== */
                $numRows = $this->data->count();
                for ($i = 0; $i < $numRows; $i++) {
                    $row = $dataStartRow + $i;
                    $color = $i % 2 === 0 ? 'F5F5F5' : 'FFFFFF';
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB($color);
                }

                /* ==================== GRAND TOTAL ==================== */
                $grandRow = $dataStartRow + $numRows;
                $sheet->getStyle("A{$grandRow}:{$lastCol}{$grandRow}")
                    ->getFont()->setBold(true)->setSize(9);
                $sheet->getStyle("A{$grandRow}:{$lastCol}{$grandRow}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FFD9D9D9');
                $sheet->getStyle("A{$grandRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                /* ==================== ROW HEIGHTS ==================== */
                foreach (range(1, 3) as $r) {
                    $sheet->getRowDimension($r)->setRowHeight(20);
                }
                $sheet->getRowDimension(4)->setRowHeight(15);
                $sheet->getRowDimension($headerRow)->setRowHeight(30);

                /* ==================== FREEZE PANE ==================== */
                $freezeCell = "A" . ($dataStartRow + 1);
                $sheet->freezePane($freezeCell);
            },
        ];
    }
}
