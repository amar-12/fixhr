<?php
namespace App\Exports\Combined;

use App\Models\Business;
use App\Models\PayrollPeriod;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Illuminate\Support\Facades\DB;

class PFEPFSheet implements FromCollection, WithTitle, WithHeadings, WithEvents, WithCustomStartCell, WithColumnWidths
{
    protected $payrollPeriodId;
    protected $businessId;
    protected $roundOffValues;
    protected $filters;
    protected $data;
    protected $businessName;
    protected $payroll;
    protected $columnHeaders = [];

    public function __construct($payrollPeriodId, $businessId, $roundOffValues, $filters = [])
    {
        $this->payrollPeriodId = $payrollPeriodId;
        $this->businessId = $businessId;
        $this->roundOffValues = $roundOffValues;
        $this->filters = $filters;

        $this->initializeData();
        $this->buildColumnHeaders();
    }

    protected function initializeData()
    {
        $this->payroll = PayrollPeriod::where('pp_b_id', $this->businessId)
            ->where('pp_is_processed', 120)
            ->find($this->payrollPeriodId);

        if (!$this->payroll) {
            $this->data = collect();
            return;
        }

        $this->businessName = Business::where('b_id', $this->businessId)->value('b_name') ?? 'Unknown Business';

        // Fetch data exactly like PFEPFReport component
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
            // DA (362)
            ->leftJoin('processed_salary_earnings as da_earnings', function ($join) {
                $join->on('da_earnings.ps_id', '=', 'processed_salaries.ps_id')
                    ->where('da_earnings.ps_earning_type_id', '=', 362);
            })
            // BASIC (360)
            ->leftJoin('processed_salary_earnings as basic_earnings', function ($join) {
                $join->on('basic_earnings.ps_id', '=', 'processed_salaries.ps_id')
                    ->where('basic_earnings.ps_earning_type_id', '=', 360);
            })
            // Deductions: EPF (351)
            ->leftJoin('processed_salary_deductions as epf_deductions', function ($join) {
                $join->on('epf_deductions.ps_id', '=', 'processed_salaries.ps_id')
                    ->where('epf_deductions.ps_deduction_type_id', '=', 351)
                    ->where('epf_deductions.ps_d_category', '=', 'employee');
            })
            ->select(
                'employees.emp_id', // Include emp_id for deduplication
                'employees.emp_code',
                'employees.emp_fname',
                'employees.emp_full_name',
                'employees.emp_dob',
                'employees.emp_date_of_joining',
                'employees.emp_pf_no',
                'employees.emp_pf_universal_ac_no',
                'employees.emp_eps_no',
                'employees.emp_is_pf_enabled',
                'businesses.b_name as company_name',
                'departments.d_name as department',
                'designations.dg_name as designation',
                'gender.m_name as gender',
                'processed_salaries.*',
                'employee_salaries.*',
                'da_earnings.ps_e_amount as da_amount',
                'basic_earnings.ps_e_amount as basic_amount',
                'epf_deductions.ps_d_amount as epf_amount'
            )
            ->where('payroll_periods.pp_id', $this->payrollPeriodId)
            ->where('employees.emp_is_pf_enabled', 120)
            ->get();


            
            
            $this->data = $employeesWithPf->unique('emp_id')->values();
            
            // dd($this->data);
        if ($this->data->isEmpty()) {
            $this->data = collect();
        }
    }

    protected function buildColumnHeaders()
    {
        // Base columns that are always shown
        $this->columnHeaders = [
            'S#',
            'Emp Code',
            'Employee Name',
            'Company Name',
            'Department',
            'Designation',
            'Gender',
            'Aadhaar No',
            "Father's Name",
            'Date of Birth',
            'Date of Joining',
            'Date of Leaving',
            'Last Working Date',
            'Reason of Leaving',
            'UAN',
            'PF No.',
            'EPS No.',
            'Month Period',
            'Days Worked',
            'Arrear Days',
            'LOP',
            'Gross Salary',
            'Basic + DA',
            'PF Contribution',
            'EPS Contribution',
            'EDLI',
            'Total Deductions',
            'PF',
            'EPS',
            'UA'
        ];
      
    }

    public function title(): string
    {
        return 'PF/EPF Report';
    }

    public function collection()
    {
        if ($this->data->isEmpty()) {
            return collect([['No PF-eligible employees found for the selected payroll period']]);
        }

        $rows = collect();
        $serialNo = 1;

        foreach ($this->data as $item) {
            // dd($item);
            $row = [];



            // Always include S# and Emp Code
            $row['S#'] = $serialNo++;
            $row['Emp Code'] = $item->emp_code ?? '-';
            $row['Employee Name'] = $item->emp_full_name ?? '-';
            $row['Company Name'] = $item->company_name ?? '-';
            $row['Department'] = $item->department ?? '-';
            $row['Designation'] = $item->designation ?? '-';
            $row['Gender'] = $item->gender ?? '-';
            $row['Aadhaar No'] = $item->emp_aadhaar_no ?? '-';
            $row["Father's Name"] = $item->emp_father_name ?? '-';
            $row['Date of Birth'] = $item->emp_dob ? Carbon::parse($item->emp_dob)->format('d M, Y') : '-';
            $row['Date of Joining'] = $item->emp_date_of_joining ? Carbon::parse($item->emp_date_of_joining)->format('d M, Y') : '-';
            $row['Date of Leaving'] = '-';
            $row['Last Working Date'] = $item->emp_last_working_date ? Carbon::parse($item->emp_last_working_date)->format('d M, Y') : '-';
            $row['Reason of Leaving'] = '-';


            $pfAccountNo = $item->emp_pf_universal_ac_no;
            $formattedpfAccountNo = preg_match('/^\d+$/', $pfAccountNo) ? "'$pfAccountNo" : $pfAccountNo;

            // PF-related fields
            $row['UAN'] = $formattedpfAccountNo ?? '-';
            $row['PF No.'] = $item->emp_pf_no ?? '-';
            $row['EPS No.'] = $item->emp_eps_no ?? '-';
            $row['Month Period'] = $this->payroll->pp_name ?? '-';


            $daysWorked = $item->ps_total_days_worked ?? 0;
            $daysInMonth = $item->ps_total_days_in_month ?? 0;
            $lop = $daysInMonth - $daysWorked;

            $row['Days Worked'] = $this->roundOffValues ? round($daysWorked) : number_format($daysWorked, 2, '.', '');
            $row['Arrear Days'] = '0.00';
            $row['LOP'] = $this->roundOffValues ? round($lop) : number_format($lop, 2, '.', '');



            $grossSalary = $item->ps_monthly_gross ?? 0;
            $basicDa = ($item->basic_amount ?? 0) + ($item->da_amount ?? 0);
            $pfAmount = $item->epf_amount ?? 0;
            $epsAmount = $basicDa * 0.0833; // 8.33% of Basic+DA
            $edliAmount = $basicDa * 0.005; // 0.5% of Basic+DA
            $totalDeductions = $pfAmount + $epsAmount + $edliAmount;

            if ($this->roundOffValues) {
                $grossSalary = round($grossSalary);
                $basicDa = round($basicDa);
                $pfAmount = round($pfAmount);
                $epsAmount = round($epsAmount);
                $edliAmount = round($edliAmount);
                $totalDeductions = round($totalDeductions);
            }

            $row['Gross Salary'] = number_format($grossSalary, 2, '.', '');
            $row['Basic + DA'] = number_format($basicDa, 2, '.', '');
            $row['PF Contribution'] = number_format($pfAmount, 2, '.', '');
            $row['EPS Contribution'] = number_format($epsAmount, 2, '.', '');
            $row['EDLI'] = number_format($edliAmount, 2, '.', '');
            $row['Total Deductions'] = number_format($totalDeductions, 2, '.', '');
            $row['PF'] = number_format($pfAmount, 2, '.', '');
            $row['EPS'] = number_format($epsAmount, 2, '.', '');
            $row['UA'] = '0.00';

            $rows->push($row);
        }

        // Calculate grand totals
        if (!$rows->isEmpty()) {
            $grand = [];
            $firstRow = $rows->first();

            foreach (array_keys($firstRow) as $header) {
                if (in_array($header, ['S#', 'Emp Code', 'Employee Name', 'Company Name', 'Department', 'Designation',
                    'Gender', 'Aadhaar No', "Father's Name", 'Date of Birth', 'Date of Joining', 'Date of Leaving',
                    'Last Working Date', 'Reason of Leaving', 'UAN', 'PF No.', 'EPS No.', 'Month Period'])) {
                    $grand[$header] = $header === 'S#' ? 'Grand Totals' : '';
                } else {
                    $sum = $rows->sum(function($row) use ($header) {
                        $value = $row[$header] ?? 0;
                        if (is_string($value)) {
                            $value = floatval(str_replace(',', '', $value));
                        }
                        return $value;
                    });
                    $grand[$header] = number_format($sum, 2, '.', '');
                }
            }
            $rows->push($grand);
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            [$this->businessName],
            ['PF/EPS REPORT FOR THE MONTH OF ' . strtoupper($this->payroll->pp_name ?? '')],
            ['Printed on: ' . Carbon::now()->format('d-M-Y h:i A T')],
            [],
            $this->columnHeaders,
        ];
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function columnWidths(): array
    {
        $widths = [];
        foreach ($this->columnHeaders as $idx => $header) {
            $colLetter = Coordinate::stringFromColumnIndex($idx + 1);

            if (in_array($header, ['S#', 'Emp Code'])) {
                $widths[$colLetter] = 8;
            } elseif (in_array($header, ['Employee Name', 'Company Name', 'Department', 'Designation'])) {
                $widths[$colLetter] = 20;
            } elseif (in_array($header, ['UAN', 'PF No.', 'EPS No.', 'Aadhaar No'])) {
                $widths[$colLetter] = 18;
            } elseif (str_contains($header, 'Date')) {
                $widths[$colLetter] = 15;
            } else {
                $widths[$colLetter] = 14;
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
                $highestRow = $sheet->getHighestRow();
                $dataCols = count($this->columnHeaders);
                $lastCol = Coordinate::stringFromColumnIndex($dataCols);

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
                $sheet->setCellValue('A2', 'PF/EPS REPORT FOR THE MONTH OF ' . strtoupper($this->payroll->pp_name ?? ''));
                $sheet->setCellValue('A3', 'Printed on: ' . Carbon::now()->format('d-M-Y h:i A T'));

                // Add round-off info if enabled
                if ($this->roundOffValues) {
                    $sheet->setCellValue('A4', "Note: All monetary values are rounded to nearest whole number");
                    $sheet->mergeCells("A4:{$highestCol}4");
                    $sheet->getStyle("A4")->getFont()->setSize(10)->setBold(true)->setItalic(true);
                    $sheet->getStyle("A4")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                    $dataStartRow = 7;
                    $dataRange = "A{$dataStartRow}:{$lastCol}{$dataEndRow}";
                }

                /* ==================== HEADER ROW ==================== */
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
                $sheet->getStyle($dataRange)->getFont()->setSize(9);

                // Set row heights
                for ($r = $dataStartRow; $r <= $dataEndRow; $r++) {
                    $sheet->getRowDimension($r)->setRowHeight(22);
                }

                /* ==================== BORDERS ==================== */
                $sheet->getStyle("A{$headerRow}:{$lastCol}{$dataEndRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->getColor()->setRGB('D3D3D3');

                /* ==================== CURRENCY FORMAT ==================== */
                $moneyCols = ['Days Worked', 'Arrear Days', 'LOP', 'Gross Salary', 'Basic + DA',
                             'PF Contribution', 'EPS Contribution', 'EDLI', 'Total Deductions', 'PF', 'EPS', 'UA'];

                foreach ($moneyCols as $col) {
                    $idx = array_search($col, $this->columnHeaders);
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
