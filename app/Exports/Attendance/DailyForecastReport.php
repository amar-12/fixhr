<?php

namespace App\Exports\Attendance;

use App\Models\Employee;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class DailyForecastReport implements
    FromCollection,
    WithHeadings,
    WithColumnWidths,
    WithEvents,
    WithCustomStartCell
{
    protected $records;
    protected $filters;
    protected $fromDate;
    protected $toDate;

    public function __construct($records, $filters, $fromDate, $toDate)
    {
        $this->records  = $records;
        $this->filters  = $filters;
        $this->fromDate = $fromDate;
        $this->toDate   = $toDate;
    }

    /* =========================================================
       DATA
    ========================================================= */

    public function collection()
    {
        $rows = [];
        $serial = 1;
        $shownDates = [];

        $totalEmployees = Employee::where(
            'emp_b_id',
            auth()->user()->emp_b_id
        )->count();

        foreach ($this->records as $leave) {

            $period = CarbonPeriod::create(
                $leave->lvr_start_date,
                $leave->lvr_end_date
            );

            foreach ($period as $date) {

                $dateKey = $date->toDateString();
                $emp = $leave->fh_employees_details;

                // daily counts
                $approved = $this->records->filter(function ($l) use ($date) {
                    return $date->between(
                        Carbon::parse($l->lvr_start_date),
                        Carbon::parse($l->lvr_end_date)
                    ) && $l->lvr_status == 157;
                })->count();

                $pending = $this->records->filter(function ($l) use ($date) {
                    return $date->between(
                        Carbon::parse($l->lvr_start_date),
                        Carbon::parse($l->lvr_end_date)
                    ) && $l->lvr_status == 140;
                })->count();

                // ✅ show totals only once per date
                $showTotals = !in_array($dateKey, $shownDates);

                if ($showTotals) {
                    $shownDates[] = $dateKey;
                }

                $rows[] = [
                    $serial,
                    $emp->emp_code ?? '',
                    $emp->emp_full_name ?? '',
                    $emp->fh_department->d_name ?? '',
                    $emp->fh_designation->dg_name ?? '',
                    $leave->fh_leave_day_type->m_name ?? '',
                    $leave->fh_leave_cat_type->m_name ?? '',
                    Carbon::parse($leave->created_at)->format('d-M-Y'),
                    $date->format('d-M-Y'),
                    $date->format('d-M-Y'),
                    $leave->lvr_total_leave_days >= 1 ? 1 : 0.5,
                    $leave->lvr_status == 157 ? 'Approved' : 'Requested',

                    // 👇 totals only first row of that date
                    $showTotals ? $totalEmployees : '',
                    $showTotals ? $approved : '',
                    $showTotals ? $pending : '',
                    $showTotals ? $totalEmployees - $approved : '',
                    $showTotals ? $totalEmployees - ($approved + $pending) : '',
                ];

                $serial++;
            }
        }

        // ✅ sort by date
        usort($rows, fn($a, $b) =>
            strtotime($a[8]) <=> strtotime($b[8])
        );

        return collect($rows);
    }

    /* =========================================================
       HEADINGS
    ========================================================= */

    public function headings(): array
    {
        return [
            'S#',
            'Emp Code',
            'Employee Name',
            'Department',
            'Designation',
            'Leave Type',
            'Leave Category',
            'Applied Date',
            'Leave Start Date',
            'Leave End Date',
            'Leave Count',
            'Approval Status',
            'Total Employees',
            'Approved',
            'Pending',
            'Remaining (Strict)',
            'Remaining (Planning)',
        ];
    }

    public function startCell(): string
    {
        return 'A6';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 10,
            'C' => 20,
            'D' => 20,
            'E' => 18,
            'F' => 12,
            'G' => 18,
            'H' => 18,
            'I' => 14,
            'J' => 14,
            'K' => 12,
            'L' => 14,
            'M' => 15,
            'N' => 10,
            'O' => 10,
            'P' => 18,
            'Q' => 18,
        ];
    }

    /* =========================================================
       STYLING
    ========================================================= */

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();
                $lastCol = Coordinate::stringFromColumnIndex(count($this->headings()));

                $headingRow = 6;
                $dataStart  = 7;
                $dataCount  = $this->collection()->count();
                $lastRow    = $dataStart + $dataCount - 1;

                $sheet->setShowGridlines(false);

                /* ===== HEADER ===== */

                $businessName = $this->records[0]['fh_business']['b_name'] ?? 'Business Name';

                $from = Carbon::parse($this->fromDate)->format('d-M-Y');
                $to   = Carbon::parse($this->toDate)->format('d-M-Y');

                $sheet->setCellValue('A1', $businessName);
                $sheet->setCellValue('A2', 'Attendance Forecast Report');
                $sheet->setCellValue('A3', "Period: $from to $to");
                $sheet->setCellValue('A4', 'Printed on: '.Carbon::now()->format('d-M-Y h:i A'));

                foreach (range(1,4) as $r) {
                    $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
                    $sheet->getStyle("A{$r}")
                        ->getFont()->setBold(true)->setSize(11);
                }

                /* ===== TABLE HEADER ===== */

                $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")
                    ->getFont()->setBold(true)
                    ->getColor()->setRGB('FFFFFF');

                $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")
                    ->getFill()->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('263871');

                $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                /* ===== DATA STYLE ===== */

                if ($dataCount > 0) {

                    $sheet->getStyle("A{$headingRow}:{$lastCol}{$lastRow}")
                        ->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->getColor()->setRGB('D3D3D3');

                    for ($r=$dataStart;$r<=$lastRow;$r++) {
                        $bg = ($r % 2 == 0) ? 'F5F5F5' : 'FFFFFF';
                        $sheet->getStyle("A{$r}:{$lastCol}{$r}")
                            ->getFill()->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB($bg);
                    }

                    $sheet->freezePane('A7');

                    /* ===== MERGE TOTALS DATE-WISE ===== */

                    $currentDate = null;
                    $mergeStartRow = null;

                    for ($row = $dataStart; $row <= $lastRow; $row++) {

                        $date = $sheet->getCell("I$row")->getValue();

                        if ($currentDate !== $date) {

                            if ($mergeStartRow && $row - 1 > $mergeStartRow) {

                                foreach (['M','N','O','P','Q'] as $col) {

                                    $range = "$col$mergeStartRow:$col" . ($row - 1);

                                    $sheet->mergeCells($range);

                                    $sheet->getStyle($range)->applyFromArray([
                                        'alignment' => [
                                            'vertical' => Alignment::VERTICAL_CENTER,
                                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                                        ],
                                    ]);
                                }
                            }

                            $currentDate = $date;
                            $mergeStartRow = $row;
                        }
                    }

                    if ($mergeStartRow && $lastRow > $mergeStartRow) {

                        foreach (['M','N','O','P','Q'] as $col) {

                            $range = "$col$mergeStartRow:$col$lastRow";

                            $sheet->mergeCells($range);

                            $sheet->getStyle($range)->applyFromArray([
                                'alignment' => [
                                    'vertical' => Alignment::VERTICAL_CENTER,
                                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                                ],
                            ]);
                        }
                    }

                    $sheet->getStyle("M{$dataStart}:Q{$lastRow}")
                          ->getAlignment()
                          ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                          ->setVertical(Alignment::VERTICAL_CENTER);

                    $sheet->getDefaultRowDimension()->setRowHeight(-1);
                }
            }
        ];
    }
}