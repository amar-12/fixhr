<?php

namespace App\Exports\Attendance;

use App\Models\Business;
use App\Models\MasterTable;
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

class ContinuousAbsentReport implements FromCollection, WithHeadings, WithColumnWidths, WithEvents
{
    protected $data;
    protected $filters;
    protected $fileName;
    protected $dateRange;

    public function __construct($records, $filters, $slug, $dateRange)
    {
        $this->data = $records;
        $this->filters = $filters;
        $this->fileName = ucwords(str_replace(['_', '-'], ' ', $slug)) . ' Report';
        $this->dateRange = $dateRange;
    }

    public function collection()
    {
        $absentStatusId = 203; // ABS
        $grouped = $this->data->groupBy(fn($r) => $r->fh_employees_details->emp_id);
        $collection = collect();
        $serial = 1;

        foreach ($grouped as $empId => $records) {
            $employee = $records->first()->fh_employees_details;
            $absentDates = $records
                ->where('atd_attendance_status', $absentStatusId)
                ->sortBy('atd_date')
                ->pluck('atd_date')
                ->map(fn($d) => Carbon::parse($d)->format('d-M-y'))
                ->values();

            if ($absentDates->isEmpty()) continue;

            $row = [
                'S#' => $serial++,
                'Emp Code' => $employee->emp_code ?? '',
                'Emp Name' => $employee->emp_full_name ?? '',
            ];

            if (!empty($this->filters['branch'])) {
                $row['Branch'] = $employee->fh_branch->br_name ?? '-';
            }
            if (!empty($this->filters['department'])) {
                $row['Department'] = $employee->fh_department->d_name ?? '-';
            }
            if (!empty($this->filters['designation'])) {
                $row['Designation'] = $employee->fh_designation->dg_name ?? '-';
            }
            if (!empty($this->filters['dealership'])) {
                $row['Dealer'] = $employee->fh_dealership->dlr_name ?? '-';
            }
            if (!empty($this->filters['shift'])) {
                $row['Shift'] = $employee->fh_shift_type->pst_name ?? '-';
            }

            $shiftStart = $employee->fh_shift_type->pst_start_time ?? null;
            $shiftEnd = $employee->fh_shift_type->pst_end_time ?? null;
            $row['Shift Timing'] = ($shiftStart && $shiftEnd)
                ? Carbon::parse($shiftStart)->format('H:i') . ' - ' . Carbon::parse($shiftEnd)->format('H:i')
                : '-';

            if (!empty($this->filters['checkingMethod'])) {
                $methodId = $records->first()->atd_checkin_method_id;
                $row['Check In Method'] = $methodId
                    ? MasterTable::where('m_id', $methodId)->value('m_name') ?? '-'
                    : '-';
            }

            if (!empty($this->filters['workMode'])) {
                $row['Work Mode'] = $employee->fh_work_mode->m_name ?? '-';
            }

            if (!empty($this->filters['grade'])) {
                $row['Grade'] = $employee->fh_grade->g_name ?? '-';
            }

            // Join all absent dates with line break
            $row['Absent Dates'] = $absentDates->implode(PHP_EOL);
            $row['Total Days'] = $absentDates->count();
            $row['Status'] = 'ABS';

            $collection->push($row);
        }

        return $collection;
    }

    public function headings(): array
    {
        $headings = ['S#', 'Emp Code', 'Emp Name'];

        if (!empty($this->filters['branch']))       $headings[] = 'Branch';
        if (!empty($this->filters['department']))   $headings[] = 'Department';
        if (!empty($this->filters['designation']))  $headings[] = 'Designation';
        if (!empty($this->filters['dealership']))   $headings[] = 'Dealer';
        if (!empty($this->filters['shift']))        $headings[] = 'Shift';
        $headings[] = 'Shift Timing';
        if (!empty($this->filters['checkingMethod'])) $headings[] = 'Check In Method';
        if (!empty($this->filters['workMode']))     $headings[] = 'Work Mode';
        if (!empty($this->filters['grade']))        $headings[] = 'Grade';

        $headings = array_merge($headings, ['Absent Dates', 'Total Days', 'Status']);

        return $headings;
    }

   public function columnWidths(): array
{
    $headings = $this->headings();
    $widths   = [];

    foreach ($headings as $i => $heading) {
        $col = Coordinate::stringFromColumnIndex($i + 1);

        $widths[$col] = match ($heading) {
            'S#'       => 4,
            'Emp Code' => 7,
            default    => 13,
        };
    }

    return $widths;
}

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

            // === HEADER ===
            $businessName = $this->data->isNotEmpty()
                ? Business::where('b_id', $this->data[0]->fh_business->b_id)->value('b_name') ?? 'Business'
                : 'Business';
            $printedOn = Carbon::now()->format('d-M-Y h:i A T');

            $sheet->setCellValue('A1', $businessName);
            $sheet->setCellValue('A2', $this->fileName);
            $sheet->setCellValue('A3', $this->dateRange);
            $sheet->setCellValue('A4', "Printed on: {$printedOn}");

            foreach (range(1, 4) as $r) {
                $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
                $sheet->getStyle("A{$r}")->getFont()->setSize(11)->setBold(true);
                $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            }

            // === HEADING ROW – Wrap + taller ===
            $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")
                ->getFont()->setSize(10)->setBold(true)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")
                ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('263871');
            $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setWrapText(true); // Wrap long headings

            $sheet->getRowDimension($headingRow)->setRowHeight(30); // Fit wrapped text

            if ($dataRows > 0) {
                // === DATA ROWS – Fixed height 25 ===
                for ($r = $headingRow + 1; $r <= $lastDataRow; $r++) {
                    $sheet->getRowDimension($r)->setRowHeight(25);
                }

                // === BORDERS ===
                $sheet->getStyle("A{$headingRow}:{$lastCol}{$lastDataRow}")
                    ->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D3D3D3');

                // === CENTER + WRAP ALL DATA CELLS ===
                $sheet->getStyle("A" . ($headingRow + 1) . ":{$lastCol}{$lastDataRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true); // Wrap everything

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