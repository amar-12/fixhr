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

class WeeklyOffPolicyReport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithEvents
{
    protected string $businessName;
    protected Collection $records;
    protected ?string $date;
    protected string $fileName = 'Weekly Off Report';
    protected array $rows = [];
    protected array $mergeInfo = [];

    public function __construct(string $businessName, Collection $records, ?string $date = null)
    {
        $this->businessName = $businessName ?: 'Business';
        $this->records = $records ?? collect([]);
        $this->date = $date;
        $this->buildRows();
    }

    protected function buildRows(): void
    {
        $grouped = $this->records
            ->sortBy('date')
            ->groupBy(fn ($r) => Carbon::parse($r->date)->format('Y-m'));

        $rowIndex = 0;
        foreach ($grouped as $ym => $items) {
            $monthName = Carbon::createFromFormat('Y-m', $ym)->format('F');
            $count = $items->count();
            $start = $rowIndex + 1;

            foreach ($items as $rec) {
                $rowIndex++;
                $this->rows[] = [
                    'date'     => $rec->date,
                    'day_name' => $rec->day_name,
                    'month'    => $monthName,
                    'count'    => $count,
                ];
            }

            $end = $rowIndex;
            $this->mergeInfo[] = [
                'month' => $monthName,
                'start' => $start,
                'end'   => $end,
            ];
        }
    }

    public function collection()
    {
        return collect($this->rows);
    }

    public function headings(): array
    {
        return ['S#', 'Date', 'Day', 'Month', 'Count'];
    }

    public function map($row): array
    {
        static $serial = 0;
        $serial++;

        return [
            $serial,
            Carbon::parse($row['date'])->format('d-M-Y'),
            $row['day_name'] ?? '-',
            $row['month'],
            $row['count'],
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
                $headingRow  = 6;
                $dataRows    = $this->rows ? count($this->rows) : 0;
                $lastDataRow = $headingRow + $dataRows;

                // Insert 5 rows at top
                $sheet->insertNewRowBefore(1, 5);
                $sheet->setShowGridlines(false);

                // === HEADER CONTENT ===
                $printedOn = Carbon::now()->format('d-M-Y h:i A T');

                $sheet->setCellValue('A1', $this->businessName);
                $sheet->setCellValue('A2', $this->fileName);
                $sheet->setCellValue('A3', 'Date: ' . ($this->date ?? 'All Weekly Offs'));
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

                    // === MERGE Month & Count per group ===
                    foreach ($this->mergeInfo as $block) {
                        $startRow = $headingRow + $block['start'];
                        $endRow   = $headingRow + $block['end'];

                        if ($endRow >= $startRow) {
                            // Merge Month (Column D) and Count (Column E)
                            $sheet->mergeCells("D{$startRow}:D{$endRow}");
                            $sheet->mergeCells("E{$startRow}:E{$endRow}");

                            // Center align merged cells
                            $sheet->getStyle("D{$startRow}:D{$endRow}")
                                ->getAlignment()
                                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                                ->setVertical(Alignment::VERTICAL_CENTER);

                            $sheet->getStyle("E{$startRow}:E{$endRow}")
                                ->getAlignment()
                                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                                ->setVertical(Alignment::VERTICAL_CENTER);
                        }
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