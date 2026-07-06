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
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ContinuousLeaveReport implements FromCollection, WithHeadings, WithColumnWidths, WithEvents
{
    protected $data;
    protected $fromdate;
    protected $toDate;
    protected $businessName;
    protected $fileName = 'Continuous Leave Report';

    public function __construct($records, $fromdate = null, $toDate = null, $businessName = null)
    {
        $this->data        = $records;
        $this->fromdate    = $fromdate;
        $this->toDate      = $toDate;
        $this->businessName = $businessName;
    }

    /* ------------------------------------------------------------------ */
    /*  COLLECTION – the raw rows that will be exported                    */
    /* ------------------------------------------------------------------ */
    public function collection()
    {
        // The controller already passes a Collection with the exact columns
        // you need for the export, so we just return it.
        return collect($this->data);
    }

    /* ------------------------------------------------------------------ */
    /*  HEADINGS – same order as the data rows                             */
    /* ------------------------------------------------------------------ */
    public function headings(): array
    {
        return [
            'S#',
            'Emp Code',
            'Employee Name',
            'Department',
            'Designation',
            'Dealership',
            'Leave Type',
            'Leave Segment',
            'Leave Category',
            'Leave Reason',
            'Leave Start Date',
            'Leave End Date',
            'Leave Dates',
            'Leave Count',
            'Approval Status',
            'Applied Date',
            'Approver Name',
        ];
    }

    /* ------------------------------------------------------------------ */
/*  COLUMN WIDTHS – S# & Emp Code = 4, Others = 13                  */
/* ------------------------------------------------------------------ */
public function columnWidths(): array
{
    $headings = $this->headings();
    $widths   = [];

    foreach ($headings as $i => $heading) {
        $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);

        $widths[$col] = match ($heading) {
            'S#'     => 4,
            'Emp Code' => 7,
            default    => 13,
        };
    }

    return $widths;
}

/* ------------------------------------------------------------------ */
/*  AFTER-SHEET – Wrap headings + fixed row height 25                 */
/* ------------------------------------------------------------------ */
public function registerEvents(): array
{
    return [
        AfterSheet::class => function (AfterSheet $event) {
            $sheet       = $event->sheet->getDelegate();
            $headings    = $this->headings();
            $lastCol     = Coordinate::stringFromColumnIndex(count($headings));
            $headingRow  = 6;
            $dataRows    = $this->collection()->count();
            $lastDataRow = $headingRow + $dataRows;

            $sheet->insertNewRowBefore(1, 5);
            $sheet->setShowGridlines(false);

            // === HEADER CONTENT ===
            $businessName = $this->businessName ?? 'Business';
            $printedOn    = Carbon::now()->format('d-M-Y h:i A T');
            $from = is_array($this->fromdate) ? ($this->fromdate[0] ?? null) : $this->fromdate;
            $to   = is_array($this->toDate)   ? ($this->toDate[0] ?? null)   : $this->toDate;
            $fromFmt = $from ? Carbon::parse($from)->format('d-M-Y') : '';
            $toFmt   = $to   ? Carbon::parse($to)->format('d-M-Y')   : Carbon::now()->format('d-M-Y');
            $dateRange = trim("{$fromFmt} to {$toFmt}", ' to');

            $sheet->setCellValue('A1', $businessName);
            $sheet->setCellValue('A2', $this->fileName);
            $sheet->setCellValue('A3', $dateRange);
            $sheet->setCellValue('A4', "Printed on: {$printedOn}");

            foreach (range(1, 4) as $r) {
                $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
                $sheet->getStyle("A{$r}")->getFont()->setSize(11)->setBold(true);
                $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            }

            // === HEADING ROW – Wrap text + center + taller if needed ===
            $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")
                ->getFont()->setSize(10)->setBold(true)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")
                ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('263871');
            $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setWrapText(true); // Headings wrap if too long

            $sheet->getRowDimension($headingRow)->setRowHeight(30); // Taller to fit wrapped headings

            if ($dataRows > 0) {
                // === DATA ROWS – Fixed height 25 ===
                for ($r = $headingRow + 1; $r <= $lastDataRow; $r++) {
                    $sheet->getRowDimension($r)->setRowHeight(25);
                }

                // === BORDERS ===
                $sheet->getStyle("A{$headingRow}:{$lastCol}{$lastDataRow}")
                    ->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D3D3D3');

                // === CENTER + WRAP ON ALL DATA CELLS ===
                $sheet->getStyle("A" . ($headingRow + 1) . ":{$lastCol}{$lastDataRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true); // All data wraps

                // === FONT SIZE ===
                $sheet->getStyle("A" . ($headingRow + 1) . ":{$lastCol}{$lastDataRow}")
                    ->getFont()->setSize(9);

                // === ALTERNATING ROWS ===
                for ($r = $headingRow + 1; $r <= $lastDataRow; $r++) {
                    $bg = (($r - $headingRow) % 2 == 0) ? 'F5F5F5' : 'FFFFFF';
                    $sheet->getStyle("A{$r}:{$lastCol}{$r}")
                        ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bg);
                }
            }

            // === FREEZE PANE ===
            $sheet->freezePane('D7');
        },
    ];
}
}