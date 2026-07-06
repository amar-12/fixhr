<?php

namespace App\Exports\Attendance;

use App\Models\Business;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class AttendanceRegularizationReport implements FromCollection, WithHeadings, WithColumnWidths, WithEvents, WithCustomStartCell
{
    protected $records;
    protected $filters;
    protected $slug;
    protected $fromDate;
    protected $toDate;
    protected $businessId;
    public function __construct(Collection $records, $filters, $slug, $fromDate, $toDate, $businessId)
    {
        $this->records    = $records;
        $this->filters    = $filters;
        $this->slug       = $slug;
        $this->fromDate   = $fromDate;
        $this->toDate     = $toDate;
        $this->businessId = $businessId;
    }
    /* --------------------------------------------------------------
     *  1. COLLECTION – filter columns after Employee Name
     * -------------------------------------------------------------- */
    public function collection()
    {
        return $this->records->map(function ($row, $index) {
            // dd($row);
            $base = [
                'S#'                => (string) ($index + 1),
                'Employee Code'     => $row['emp_code'],
                'Employee Name'     => $row['emp_name'],
            ];
            /* ---- OPTIONAL FILTER COLUMNS (after Employee Name) ---- */
            if ($this->filters['branch'] ?? false) {
                $base['Branch'] = $row['branch_name'] ?? '-';
            }
            if ($this->filters['department'] ?? false) {
                $base['Department'] = $row['department_name'] ?? '-';
            }
            if ($this->filters['designation'] ?? false) {
                $base['Designation'] = $row['designation_name'] ?? '-';
            }
            if ($this->filters['dealership'] ?? false) {
                $base['Dealership'] = $row['dealership_name'] ?? '-';
            }
            if ($this->filters['shift'] ?? false) {
                $base['Shift'] = $row['shift_name'] ?? '-';
            }
            if ($this->filters['workMode'] ?? false) {
                $base['Work Mode'] = $row['work_mode_name'] ?? '-';
            }
            if ($this->filters['jobStatus'] ?? false) {
                $base['Job Status'] = $row['job_status_name'] ?? '-';
            }
            if ($this->filters['grade'] ?? false) {
                $base['Grade'] = $row['grade_name'] ?? '-';
            }
            /* ---- REST OF THE FIELDS ---- */
            $base['Date']              = $this->formatDateSafe($row['date']);
            $base['Device Check In']   = $this->formatTimeSafe($row['check_in']);
            $base['Device Check Out']  = $this->formatTimeSafe($row['check_out']);
            $base['Updated Check In']  = $this->formatTimeSafe($row['updated_check_in']);
            $base['Updated Check Out'] = $this->formatTimeSafe($row['updated_check_out']);
            $base['Updated Date']      = $this->formatDateSafe($row['updated_date']);
            $base['Updated Time']      = $this->formatTimeSafe($row['updated_time']);
            $base['Updated By']        = $row['updated_by'];
            return $base;
        })->values();
    }
    /* --------------------------------------------------------------
     *  2. HEADINGS – build them dynamically (same order as collection)
     * -------------------------------------------------------------- */
    public function headings(): array
    {
        $headings = [
            'S#',
            'Emp Code',
            'Emp Name',
        ];
        if ($this->filters['branch'] ?? false)      $headings[] = 'Branch';
        if ($this->filters['department'] ?? false)  $headings[] = 'Department';
        if ($this->filters['designation'] ?? false) $headings[] = 'Designation';
        if ($this->filters['dealership'] ?? false)  $headings[] = 'Dealership';
        if ($this->filters['shift'] ?? false)       $headings[] = 'Shift';
        if ($this->filters['workMode'] ?? false)    $headings[] = 'Work Mode';
        if ($this->filters['jobStatus'] ?? false)   $headings[] = 'Job Status';
        if ($this->filters['grade'] ?? false)       $headings[] = 'Grade';
        $headings = array_merge($headings, [
            'Date',
            'Device Check In',
            'Device Check Out',
            'Updated Check In',
            'Updated Check Out',
            'Updated Date',
            'Updated Time',
            'Updated By',
        ]);
        return $headings;
    }
    /* --------------------------------------------------------------
     *  3. COLUMN WIDTHS – also dynamic
     * -------------------------------------------------------------- */
    public function columnWidths(): array
    {
        $widths = [
            'A' => 5,   // S#
            'B' => 8,   // Emp Code
            'C' => 13,  // Emp Name
        ];
        $col = 'D'; // first optional column starts after C (Emp Name)
        $optionalWidths = [
            'branch'      => 12,
            'department'  => 15,
            'designation' => 14,
            'dealership'  => 14,
            'shift'       => 12,
            'workMode'    => 12,
            'jobStatus'   => 12,
            'grade'       => 10,
        ];
        foreach ($optionalWidths as $key => $w) {
            if ($this->filters[$key] ?? false) {
                $widths[$col] = $w;
                $col++;
            }
        }
        // Now add the rest of the static columns
        $widths[$col++] = 9;   // Date
        $widths[$col++] = 9;   // Device Check In
        $widths[$col++] = 9;   // Device Check Out
        $widths[$col++] = 9;   // Updated Check In
        $widths[$col++] = 9;   // Updated Check Out
        $widths[$col++] = 9;   // Updated Date
        $widths[$col++] = 9;   // Updated Time
        $widths[$col++] = 13;  // Updated By
        return $widths;
    }
    /* --------------------------------------------------------------
     *  4. SHEET STYLING – now works with the variable column count
     * -------------------------------------------------------------- */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $sheet->setShowGridlines(false);
                // ---------- 5 rows for top header ----------
                // $sheet->insertNewRowBefore(1, 5);
                // ---------- Company & report info ----------
                $company = Business::where('b_id', $this->businessId)->value('b_name') ?? '';
                $printed = Carbon::now()->format('d-M-Y h:i A T');
                $from    = Carbon::parse($this->fromDate)->format('d-M-Y');
                $to      = Carbon::parse($this->toDate)->format('d-M-Y');
                $sheet->setCellValue('A1', $company);
                $sheet->setCellValue('A2', 'Attendance Regularization Report');
                $sheet->setCellValue('A3', "Period: {$from} to {$to}");
                $sheet->setCellValue('A4', "Printed on: {$printed}");
                foreach (range(1, 4) as $r) {
                    $sheet->mergeCells("A{$r}:{$this->lastColumn()}{$r}");
                    $sheet->getStyle("A{$r}")
                        ->getFont()->setSize(11)->setBold(true);
                    $sheet->getStyle("A{$r}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }
                // ---------- Column indexes ----------
                $headingRow    = 6;
                $subHeadingRow = 7;
                $rowCount      = $this->collection()->count();
                $lastDataRow   = $subHeadingRow + $rowCount;
                $lastCol       = $this->lastColumn();
                // Calculate dynamic column positions
                $extraCols = $this->getExtraColumnsCount();
                $dateCol = Coordinate::stringFromColumnIndex(4 + $extraCols); // after A,B,C + extras
                $deviceInCol = Coordinate::stringFromColumnIndex(5 + $extraCols);
                $deviceOutCol = Coordinate::stringFromColumnIndex(6 + $extraCols);
                $updInCol = Coordinate::stringFromColumnIndex(7 + $extraCols);
                $updOutCol = Coordinate::stringFromColumnIndex(8 + $extraCols);
                $updDateCol = Coordinate::stringFromColumnIndex(9 + $extraCols);
                $updTimeCol = Coordinate::stringFromColumnIndex(10 + $extraCols);
                $updByCol = Coordinate::stringFromColumnIndex(11 + $extraCols);
                // ---------- Main headings (merged where needed) ----------
                $mainCols = [
                    'A' => 'S#',
                    'B' => 'Emp Code',
                    'C' => 'Emp Name',
                ];
                // Add optional filter headings
                $optionalStartCol = 'D';
                $optionalLabels   = [
                    'branch'      => 'Branch',
                    'department'  => 'Department',
                    'designation' => 'Designation',
                    'dealership'  => 'Dealership',
                    'shift'       => 'Shift',
                    'workMode'    => 'Work Mode',
                    'jobStatus'   => 'Job Status',
                    'grade'       => 'Grade',
                ];
                foreach ($optionalLabels as $key => $label) {
                    if ($this->filters[$key] ?? false) {
                        $mainCols[$optionalStartCol] = $label;
                        $optionalStartCol++;
                    }
                }
                // Add remaining static columns
                $mainCols[$dateCol] = 'Date';
                $mainCols[$updTimeCol] = 'Action Time';
                $mainCols[$updByCol] = 'Updated By';
                // Merge the main heading columns
                foreach ($mainCols as $col => $label) {
                    $sheet->mergeCells("{$col}{$headingRow}:{$col}{$subHeadingRow}");
                    $sheet->setCellValue("{$col}{$headingRow}", $label);
                    $sheet->getStyle("{$col}{$headingRow}:{$col}{$subHeadingRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle("{$col}{$headingRow}:{$col}{$subHeadingRow}")
                        ->getFont()->setSize(10)->setBold(true);
                }
                // Device Log block
                $sheet->setCellValue("{$deviceInCol}{$headingRow}", 'Device Log');
                $sheet->mergeCells("{$deviceInCol}{$headingRow}:{$deviceOutCol}{$headingRow}");
                $sheet->getStyle("{$deviceInCol}{$headingRow}")
                    ->getFont()->setSize(10)->setBold(true);
                $sheet->getStyle("{$deviceInCol}{$headingRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                // Updated Log block
                $sheet->setCellValue("{$updInCol}{$headingRow}", 'Updated Log');
                $sheet->mergeCells("{$updInCol}{$headingRow}:{$updDateCol}{$headingRow}");
                $sheet->getStyle("{$updInCol}{$headingRow}")
                    ->getFont()->setSize(10)->setBold(true);
                $sheet->getStyle("{$updInCol}{$headingRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                // Sub-headers
                $subHeaders = [
                    "{$deviceInCol}{$subHeadingRow}" => 'Check In',
                    "{$deviceOutCol}{$subHeadingRow}" => 'Check Out',
                    "{$updInCol}{$subHeadingRow}" => 'Check In',
                    "{$updOutCol}{$subHeadingRow}" => 'Check Out',
                    "{$updDateCol}{$subHeadingRow}" => 'Date',
                    "{$updTimeCol}{$subHeadingRow}" => 'Time',
                ];
                foreach ($subHeaders as $cell => $label) {
                    $sheet->setCellValue($cell, $label);
                    $sheet->getStyle($cell)
                        ->getFont()->setSize(9)->setBold(true);
                    $sheet->getStyle($cell)->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
                // ---------- Header background ----------
                $headerRange = "A{$headingRow}:{$lastCol}{$subHeadingRow}";
                $sheet->getStyle($headerRange)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('263871');
                $sheet->getStyle($headerRange)->getFont()
                    ->getColor()->setRGB('FFFFFF');
                // ---------- Header borders (white lines) ----------
                $sheet->getStyle($headerRange)
                    ->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->getColor()->setRGB('FFFFFF');
                // ---------- Data styling ----------
                if ($rowCount > 0) {
                    $dataRange = "A8:{$lastCol}{$lastDataRow}";
                    $sheet->getStyle($dataRange)
                        ->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->getColor()->setRGB('D3D3D3');
                    $sheet->getStyle("A8:{$lastCol}{$lastDataRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle("A8:{$lastCol}{$lastDataRow}")
                        ->getFont()->setSize(8);
                    // Left-align Employee Name & wrap
                    $sheet->getStyle("C8:C{$lastDataRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                        ->setWrapText(true);
                }
                // ---------- Highlight rows that have an update ----------
                for ($i = 8; $i <= $lastDataRow; $i++) {
                    $updIn  = $sheet->getCell("{$updInCol}{$i}")->getValue();
                    $updOut = $sheet->getCell("{$updOutCol}{$i}")->getValue();
                    if ($updIn !== '-' || $updOut !== '-') {
                        $sheet->getStyle("A{$i}:{$lastCol}{$i}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('FFFFE0');
                    }
                }
                // ---------- Row heights ----------
                $sheet->getRowDimension($headingRow)->setRowHeight(20);
                $sheet->getRowDimension($subHeadingRow)->setRowHeight(25);
                for ($r = 8; $r <= $lastDataRow; $r++) {
                    $sheet->getRowDimension($r)->setRowHeight(25);
                }
                // ---------- Freeze ----------
                $sheet->freezePane('A8');
            },
        ];
    }
    /* -----------------------------------------------------------------
     *  Helper – count extra filter columns
     * ----------------------------------------------------------------- */
    private function getExtraColumnsCount(): int
    {
        $count = 0;
        foreach (['branch', 'department', 'designation', 'dealership', 'shift', 'workMode', 'jobStatus', 'grade'] as $key) {
            if ($this->filters[$key] ?? false) $count++;
        }
        return $count;
    }
    /* -----------------------------------------------------------------
     *  Helper – last column letter (A, B, …, Z, AA, …)
     * ----------------------------------------------------------------- */
    private function lastColumn(): string
    {
        $baseCols = 11; // A-C (S#, Emp Code, Emp Name) + 8 static fields
        $extra    = $this->getExtraColumnsCount();
        return Coordinate::stringFromColumnIndex($baseCols + $extra);
    }
    /* -----------------------------------------------------------------
     *  Safe format helpers
     * ----------------------------------------------------------------- */
    private function formatDateSafe($date)
    {
        if (empty($date) || $date === '-' || $date === null) return '-';
        try {
            return Carbon::parse($date)->format('d-M-Y');
        } catch (\Exception $e) {
            return '-';
        }
    }
    private function formatTimeSafe($time)
    {
        if (empty($time) || $time === '-' || $time === null) return '-';
        try {
            return Carbon::parse($time)->format('h:i A');
        } catch (\Exception $e) {
            return '-';
        }
    }
    public function startCell(): string
    {
        return 'A7'; // ✅ ensures data starts from row 8
    }
}
