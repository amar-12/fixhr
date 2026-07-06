<?php

namespace App\Exports\Attendance;

use App\Models\Business;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class WeeklyOffSummaryReport implements FromCollection, WithHeadings, WithColumnWidths, WithEvents
{
    protected $data;
    protected $filters;
    protected $fileName;
    protected $date;

    public function __construct($records, $filters, $fileName, $date)
    {
        $this->data     = $records ?? [];
        $this->filters  = $filters ?? [];
        $this->fileName = 'Weekly Off Present Summary Report';
        $this->date     = $date ?? Carbon::now()->format('d-M-Y');
    }

    /* --------------------------------------------------------------------- *
     *  Collection – build the rows that will be exported
     * --------------------------------------------------------------------- */
    public function collection()
    {
        $employeeSummary = [];

        foreach ($this->data as $item) {
            if (!isset($item->fh_attendance_status?->m_type) || $item->fh_attendance_status->m_type !== 'WOP') {
                continue;
            }

            $empCode = $item->fh_employees_details->emp_code ?? '';
            $empName = $item->fh_employees_details->emp_full_name ?? '';
            $salary  = $item->fh_employees_details->fh_employee_salary ?? null;

            if (!isset($employeeSummary[$empCode])) {
                $employeeSummary[$empCode] = [
                    'Emp Code'      => $empCode,
                    'Emp Name'      => $empName,
                    'Total Days'    => 0,
                    'Work Minutes'  => [],
                    'Dates'         => [],
                    'Salary'        => 0,
                ];
            }

            $employeeSummary[$empCode]['Total Days']++;

            // work duration → minutes
            $workDuration = $item->atd_total_worked_hours ?? '00:00';
            if (is_numeric($workDuration)) {
                $minutes = (int) round($workDuration * 60);
            } else {
                [$h, $m] = explode(':', $workDuration . ':00');
                $minutes = ((int) $h * 60) + (int) $m;
            }
            $employeeSummary[$empCode]['Work Minutes'][] = $minutes;

            $employeeSummary[$empCode]['Dates'][] = Carbon::parse($item->atd_date)->format('d-M-Y');

            if ($salary) {
                $employeeSummary[$empCode]['Salary'] = $this->calculateSalaryForDays(
                    $salary,
                    $employeeSummary[$empCode]['Total Days'],
                    $item->atd_date
                );
            }
        }

        $rows = [];
        foreach ($employeeSummary as $emp) {
            $avgMinutes   = $emp['Work Minutes']
                ? (int) round(array_sum($emp['Work Minutes']) / count($emp['Work Minutes']))
                : 0;
            $avgDuration  = sprintf('%02d:%02d', $avgMinutes / 60, $avgMinutes % 60);
            $wagePerDay   = $emp['Total Days'] ? $emp['Salary'] / $emp['Total Days'] : 0;

            $rows[] = [
                'S#'                => count($rows) + 1,
                'Emp Code'             => $emp['Emp Code'],
                'Emp Name'             => $emp['Emp Name'],
                'Total Weekly Off Days'=> $emp['Total Days'],
                'Average Work Duration'=> $avgDuration,
                'Wage per day (₹)'     => round($wagePerDay, 2),
                'Payable Wage (₹)'     => $emp['Salary'],
                'Dates Attended'       => implode("\n", $emp['Dates']),
            ];
        }

        return collect($rows);
    }

    protected function calculateSalaryForDays($salary, $days, $date = null): float
    {
        if (!$salary) {
            return 0.0;
        }

        $date        = $date ? Carbon::parse($date) : Carbon::now();
        $daysInMonth = $date->daysInMonth;
        $monthly     = $salary->es_monthly_gross ?? 0;

        return round(($monthly / $daysInMonth) * $days, 2);
    }

    public function headings(): array
    {
        return [
            'S#',
            'Emp Code',
            'Emp Name',
            'Total Weekly Off Days',
            'Average Work Duration',
            'Wage per day (₹)',
            'Payable Wage (₹)',
            'Dates Attended',
        ];
    }

    /* --------------------------------------------------------------------- *
     *  Fixed column widths (as per your request)
     * --------------------------------------------------------------------- */
    public function columnWidths(): array
    {
        $headings = $this->headings();

        $widths = [];
        foreach ($headings as $idx => $title) {
            $col = Coordinate::stringFromColumnIndex($idx + 1);

            $widths[$col] = match ($title) {
                'S#', 'Emp Code' => 7,
                default             => 13,
            };
        }

        return $widths;
    }

    /* --------------------------------------------------------------------- *
     *  AfterSheet – all styling + row height + wrap text
     * --------------------------------------------------------------------- */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet     = $event->sheet->getDelegate();
                $headings  = $this->headings();
                $lastCol   = Coordinate::stringFromColumnIndex(count($headings));
                $headingRow = 6;                     // after 5 header rows
                $dataRows   = $this->collection()->count();
                $lastDataRow = $dataRows ? $headingRow + $dataRows : $headingRow;

                $sheet->setShowGridlines(false);

                /* -------------------------- 1. Header (5 rows) -------------------------- */
                $sheet->insertNewRowBefore(1, 5);

                $businessName = $this->data
                    ? Business::where('b_id', $this->data[0]->fh_business->b_id ?? null)->value('b_name')
                    : 'Business Name';

                $printedOn = Carbon::now()->format('d-M-Y h:i A T');

                $sheet->setCellValue('A1', $businessName);
                $sheet->setCellValue('A2', $this->fileName);
                $sheet->setCellValue('A3', $this->date);
                $sheet->setCellValue('A4', "Printed on: {$printedOn}");

                foreach (range(1, 4) as $r) {
                    $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
                    $sheet->getStyle("A{$r}")
                        ->getFont()
                        ->setSize($r <= 2 ? 12 : 11)
                        ->setBold(true);
                    $sheet->getStyle("A{$r}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                /* -------------------------- 2. Heading row -------------------------- */
                $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")
                    ->applyFromArray([
                        'font' => [
                            'bold'  => true,
                            'size'  => 10,
                            'color' => ['rgb' => 'FFFFFF'],
                        ],
                        'fill' => [
                            'fillType'   => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '263871'],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical'   => Alignment::VERTICAL_CENTER,
                            'wrapText'   => true,
                        ],
                    ]);

                $sheet->getRowDimension($headingRow)->setRowHeight(25);

                /* -------------------------- 3. Data rows -------------------------- */
                if ($dataRows) {
                    // borders
                    $sheet->getStyle("A{$headingRow}:{$lastCol}{$lastDataRow}")
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->getColor()->setRGB('D3D3D3');

                    // vertical centre
                    $sheet->getStyle("A" . ($headingRow + 1) . ":{$lastCol}{$lastDataRow}")
                        ->getAlignment()
                        ->setVertical(Alignment::VERTICAL_CENTER);

                    // horizontal centre (default)
                    $sheet->getStyle("A" . ($headingRow + 1) . ":{$lastCol}{$lastDataRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // right-align money columns
                    foreach (['F', 'G'] as $col) {
                        $sheet->getStyle("{$col}" . ($headingRow + 1) . ":{$col}{$lastDataRow}")
                            ->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    }

                    // left-align + wrap for "Dates Attended"
                    $datesCol = Coordinate::stringFromColumnIndex(array_search('Dates Attended', $headings) + 1);
                    $sheet->getStyle("{$datesCol}" . ($headingRow + 1) . ":{$datesCol}{$lastDataRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                        ->setWrapText(true);

                    // **wrap text for EVERY column except S# & Emp Code**
                    $wrapCols = [];
                    foreach ($headings as $idx => $title) {
                        if (!in_array($title, ['S#', 'Emp Code'])) {
                            $wrapCols[] = Coordinate::stringFromColumnIndex($idx + 1);
                        }
                    }
                    foreach ($wrapCols as $col) {
                        $sheet->getStyle("{$col}" . ($headingRow + 1) . ":{$col}{$lastDataRow}")
                            ->getAlignment()
                            ->setWrapText(true);
                    }

                    // font size
                    $sheet->getStyle("A" . ($headingRow + 1) . ":{$lastCol}{$lastDataRow}")
                        ->getFont()
                        ->setSize(8);

                    // alternating background
                    for ($r = $headingRow + 1; $r <= $lastDataRow; $r++) {
                        $bg = (($r - $headingRow) % 2 === 0) ? 'F5F5F5' : 'FFFFFF';
                        $sheet->getStyle("A{$r}:{$lastCol}{$r}")
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB($bg);
                    }

                    // **fixed row height = 25**
                    for ($r = $headingRow + 1; $r <= $lastDataRow; $r++) {
                        $sheet->getRowDimension($r)->setRowHeight(25);
                    }

                    // number format for money
                    foreach (['F', 'G'] as $col) {
                        $sheet->getStyle("{$col}" . ($headingRow + 1) . ":{$col}{$lastDataRow}")
                            ->getNumberFormat()
                            ->setFormatCode('#,##0.00');
                    }
                }

                /* -------------------------- 4. Freeze panes -------------------------- */
                $sheet->freezePane('D7');   // keep S#, Emp Code, Emp Name visible

                /* -------------------------- 5. Page setup -------------------------- */
                $sheet->getPageSetup()->setFitToWidth(1);
            },
        ];
    }
}