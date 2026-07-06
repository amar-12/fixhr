<?php

namespace App\Exports\Attendance;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class LeaveSummaryReport implements FromCollection, WithColumnWidths, WithEvents
{
    protected $data;
    protected $fromdate;
    protected $toDate;

    public function __construct($records, $filter, $fromdate = null, $toDate = null)
    {
        $this->data = $records;
        $this->fromdate = $fromdate;
        $this->toDate = $toDate;
    }

    public function collection()
    {
        $summary = [];
        $finalData = [];

        // === ROW 1: Business Name ===
        $business = $this->data[0]['fh_business']['b_name'] ?? 'Business Name';
        $finalData[] = [$business];

        // === ROW 2: Report Title ===
        $finalData[] = ['Leave Summary Report'];

        // === ROW 3: Date Range ===
        $dateRange = $this->getDateRangeText();
        $finalData[] = [$dateRange];

        // === ROW 4: Printed On ===
        $finalData[] = ['Printed on: ' . Carbon::now()->format('d-M-Y h:i A T')];

        // === ROW 5: Table Headings ===
        $finalData[] = ['S#', 'Emp Code', 'Employee Name', 'Total Leave Days'];

        // === DATA ROWS (from Row 6) ===
        foreach ($this->data as $record) {
            $empCode = $record['fh_employees_details']['emp_code'] ?? '';
            $empName = $record['fh_employees_details']['emp_full_name'] ?? '';
            $totalLeaveDays = (float) ($record['lvr_total_leave_days'] ?? 0);

            $key = $empCode . '||' . $empName;
            $summary[$key] = ($summary[$key] ?? 0) + $totalLeaveDays;
        }

        $serial = 1;
        foreach ($summary as $key => $totalLeave) {
            [$empCode, $empName] = explode('||', $key);
            $finalData[] = [
                $serial++,
                $empCode,
                $empName,
                number_format($totalLeave, 1),
            ];
        }

        return collect($finalData);
    }

    /* ------------------------------------------------------------------ */
    /*  COLUMN WIDTHS – S# = 4, Emp Code = 7, Others = 13               */
    /* ------------------------------------------------------------------ */
    public function columnWidths(): array
    {
        return [
            'A' => 4,   // S#
            'B' => 7,   // Emp Code
            'C' => 13,  // Employee Name
            'D' => 13,  // Total Leave Days
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  AFTER-SHEET – Styling (Header at Row 5)                           */
    /* ------------------------------------------------------------------ */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = 'D';
                $headingRow = 5;  // ← Table header is now on Row 5
                $dataStartRow = 6;
                $dataRows = $this->data->count() > 0 ? count(collect($this->collection())->slice(5)) : 0;
                $lastDataRow = $headingRow + $dataRows;

                $sheet->setShowGridlines(false);

                // === MERGE HEADER ROWS (1–4) ===
                foreach (range(1, 4) as $r) {
                    $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
                    $sheet->getStyle("A{$r}")->getFont()->setSize(11)->setBold(true);
                    $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // === TABLE HEADING (Row 5) ===
                $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")
                    ->getFont()->setSize(10)->setBold(true)->getColor()->setRGB('FFFFFF');
                $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")
                    ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('263871');
                $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);
                $sheet->getRowDimension($headingRow)->setRowHeight(30);

                if ($dataRows > 0) {
                    // === DATA ROWS (Row 6 onwards) ===
                    for ($r = $dataStartRow; $r <= $lastDataRow; $r++) {
                        $sheet->getRowDimension($r)->setRowHeight(25);
                    }

                    // === BORDERS (Heading + Data) ===
                    $sheet->getStyle("A{$headingRow}:{$lastCol}{$lastDataRow}")
                        ->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D3D3D3');

                    // === CENTER + WRAP DATA ===
                    $sheet->getStyle("A{$dataStartRow}:{$lastCol}{$lastDataRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setWrapText(true);

                    // === FONT SIZE ===
                    $sheet->getStyle("A{$dataStartRow}:{$lastCol}{$lastDataRow}")
                        ->getFont()->setSize(9);

                    // === ALTERNATING ROW COLORS ===
                    for ($r = $dataStartRow; $r <= $lastDataRow; $r++) {
                        $bg = (($r - $headingRow) % 2 == 0) ? 'F5F5F5' : 'FFFFFF';
                        $sheet->getStyle("A{$r}:{$lastCol}{$r}")
                            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bg);
                    }
                } else {
                    // Style heading even if no data
                    $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")
                        ->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D3D3D3');
                }

                // === FREEZE PANE BELOW HEADER (Row 5) → Freeze at Row 6 ===
                $sheet->freezePane('A6');
            },
        ];
    }

    private function getDateRangeText(): string
    {
        $fromDate = is_array($this->fromdate) ? ($this->fromdate[0] ?? null) : $this->fromdate;
        $toDate = is_array($this->toDate) ? ($this->toDate[0] ?? null) : $this->toDate;

        $from = $fromDate ? Carbon::parse($fromDate)->format('d-M-Y') : '';
        $to = $toDate ? Carbon::parse($toDate)->format('d-M-Y') : Carbon::now()->format('d-M-Y');

        return $from && $to ? "$from to $to" : ($from ?: $to);
    }
}