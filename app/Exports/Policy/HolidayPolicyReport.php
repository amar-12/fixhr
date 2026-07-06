<?php

namespace App\Exports\Policy;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class HolidayPolicyReport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithEvents
{
    protected $businessName;
    protected $records;
    protected $date;
    protected $fileName;

    public function __construct(string $businessName, Collection $records, ?string $date = null)
    {
        $this->businessName = $businessName ?: 'Business';
        $this->records = $records ?? collect([]);
        $this->date = $date;
        $this->fileName = 'Holiday Report';
    }

    public function collection()
    {
        return $this->records;
    }

    public function headings(): array
    {
        return [
            'S#',
            'Holiday Type',
            'Holiday Name',
            'Start Date',
            'End Date',
            'Total Days',
        ];
    }

    public function map($record): array
    {
        static $serial = 0;
        $serial++;

        return [
            $serial,
            $record->fh_master_table->m_name ?? '-',
            $record->phl_name ?? '-',
            $record->phl_start_date
                ? Carbon::parse($record->phl_start_date)->format('d-M-Y')
                : '-',
            $record->phl_end_date
                ? Carbon::parse($record->phl_end_date)->format('d-M-Y')
                : '-',
            $record->total_days ?? '-',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  COLUMN WIDTHS – S# = 4, Others = 13                               */
    /* ------------------------------------------------------------------ */
    public function columnWidths(): array
    {
        $headings = $this->headings();
        $widths = [];

        foreach ($headings as $i => $heading) {
            $col = Coordinate::stringFromColumnIndex($i + 1);
            $widths[$col] = $heading === 'S#' ? 4 : 13;
        }

        return $widths;
    }

    /* ------------------------------------------------------------------ */
    /*  AFTER-SHEET – Full styling (same as GatepassReport)               */
    /* ------------------------------------------------------------------ */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet       = $event->sheet->getDelegate();
                $headings    = $this->headings();
                $lastCol     = Coordinate::stringFromColumnIndex(count($headings));
                $headingRow  = 6; // After 5 header rows
                $dataRows    = $this->records->count();
                $lastDataRow = $headingRow + $dataRows;

                // Insert 5 rows at top
                $sheet->insertNewRowBefore(1, 5);
                $sheet->setShowGridlines(false);

                // === HEADER CONTENT ===
                $printedOn = Carbon::now()->format('d-M-Y h:i A T');

                $sheet->setCellValue('A1', $this->businessName);
                $sheet->setCellValue('A2', $this->fileName);
                $sheet->setCellValue('A3', 'Date: ' . ($this->date ?? 'All Holidays'));
                $sheet->setCellValue('A4', "Printed on: {$printedOn}");

                foreach (range(1, 4) as $r) {
                    $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
                    $sheet->getStyle("A{$r}")->getFont()->setSize(11)->setBold(true);
                    $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // Empty row 5
                $sheet->mergeCells("A5:{$lastCol}5");

                // === HEADING ROW (Row 6) ===
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
                    // === DATA ROWS – Fixed height 25 ===
                    for ($r = $headingRow + 1; $r <= $lastDataRow; $r++) {
                        $sheet->getRowDimension($r)->setRowHeight(25);
                    }

                    // === BORDERS ===
                    $sheet->getStyle("A{$headingRow}:{$lastCol}{$lastDataRow}")
                        ->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D3D3D3');

                    // === CENTER + WRAP ALL DATA ===
                    $sheet->getStyle("A" . ($headingRow + 1) . ":{$lastCol}{$lastDataRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setWrapText(true);

                    // === FONT SIZE ===
                    $sheet->getStyle("A" . ($headingRow + 1) . ":{$lastCol}{$lastDataRow}")
                        ->getFont()->setSize(9);

                    // === ALTERNATING ROWS ===
                    for ($r = $headingRow + 1; $r <= $lastDataRow; $r++) {
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

                // === FREEZE PANE ===
                $sheet->freezePane('D7');
            },
        ];
    }
}