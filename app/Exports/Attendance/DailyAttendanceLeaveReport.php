<?php

namespace App\Exports\Attendance;

use Carbon\Carbon;
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

class DailyAttendanceLeaveReport implements 
    FromCollection, 
    WithHeadings, 
    WithColumnWidths, 
    WithEvents, 
    WithCustomStartCell
{
    protected $data;
    protected $fromdate;
    protected $toDate;

    public function __construct($records, $filter = null, $fromdate = null, $toDate = null)
    {
        $this->data = $records;
        $this->fromdate = $fromdate;
        $this->toDate = $toDate;
    }

    public function collection()
    {
        $finalData = [];
        $serial = 1;

        foreach ($this->data as $record) {
            // $record->lvr_emp_id == 800 ? dd($record) : null;
            // dd($record->fh_approval_log2->first()->created_at);
          
            $startDate = Carbon::parse($record['lvr_start_date']);
            $endDate = Carbon::parse($record['lvr_end_date']);

            if (!$startDate || !$endDate || $endDate->lessThan($startDate)) {
                continue;
            }

            $totalDays = $endDate->diffInDays($startDate) + 1;
            $totalLeaveDays = (float) ($record['lvr_total_leave_days'] ?? 0);
            $leavePerDay = $totalDays > 0 ? $totalLeaveDays / $totalDays : 0;

            $empCode = $record['fh_employees_details']['emp_code'] ?? '';
            $empName = $record['fh_employees_details']['emp_full_name'] ?? '';
            $leaveType = $record['fh_leave_day_type']['m_name'] ?? '';
            $leaveSegment = $record['fh_leave_day_segment']['m_name'] ?? '';
            $leaveCategory = $record['fh_leave_cat_type']['m_name'] ?? '';
            $approvalStatus = $record['fh_approval_status']['m_name'] ?? '-';
            $approvalName = $record['fh_approver']['emp_full_name'] ?? '-';
            $leaveBalances = $record['fh_employees_details']['leaveBalances'] ?? [];
            $currentLeaveBalance = 0;
            $carriedForward = 0;
            $approvedDate =  optional($record->fh_approval_log2->last())->created_at;

           
            foreach ($leaveBalances as $balance) {
               
                if ($balance['lb_cat_type_id'] == $record['lvr_cat_type_id']) {
                    $currentLeaveBalance = $balance['lb_balance_remaining_leave'] ?? 0;
                    $carriedForward = $balance['lb_carried_forward'] ?? 0;
                    break;
                }
            }

            for ($i = 0; $i < $totalDays; $i++) {
                $finalData[] = [
                    $serial++,
                    $empCode,
                    $empName,
                    $record['fh_employees_details']['fh_department']['d_name'] ?? '-',
                    $record['fh_employees_details']['fh_designation']['dg_name'] ?? '-',
                    $record['fh_employees_details']['fh_dealership']['dlr_name'] ?? '-',
                    $leaveType,
                    $leaveSegment,
                    $leaveCategory,
                    $record['lvr_reason'] ?? '-',
                    $startDate->format('d-M-Y'),
                    $endDate->format('d-M-Y'),
                    number_format($leavePerDay, 2),
                    number_format($currentLeaveBalance, 2),
                    number_format($carriedForward, 2),
                    $approvalStatus,
                    $approvalName,
                    Carbon::parse($record['created_at'])->format('d-M-Y H:i'),
                    $approvedDate != null ? Carbon::parse($approvedDate)->format('d-M-Y H:i') : '-',
                ];
            }
        }
        return collect($finalData);


    }

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
            'Leave Count',
            'Current Balance',
            'Carried Forward',
            'Approval Status',
            'Approver Name',
            'Applied Date',
            'Approved Date'
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  START CELL – Data starts at A6                                    */
    /* ------------------------------------------------------------------ */
    public function startCell(): string
    {
        return 'A6';
    }

    /* ------------------------------------------------------------------ */
    /*  COLUMN WIDTHS – S# = 4, Emp Code = 7, Others = 13               */
    /* ------------------------------------------------------------------ */
    public function columnWidths(): array
    {
        $headings = $this->headings();
        $widths = [];

        foreach ($headings as $i => $heading) {
            $col = Coordinate::stringFromColumnIndex($i + 1);
            $widths[$col] = match ($heading) {
                'S#'      => 7,
                'Emp Code'  => 7,
                default     => 13,
            };
        }

        return $widths;
    }

    /* ------------------------------------------------------------------ */
    /*  AFTER-SHEET – Full styling (Header at Row 6, Data at Row 7)       */
    /* ------------------------------------------------------------------ */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $headings = $this->headings();
                $lastCol = Coordinate::stringFromColumnIndex(count($headings)); // Q
                $headingRow = 6;     // Table header on Row 6
                $dataStartRow = 7;   // Data starts at Row 7 (per startCell)
                $dataRows = $this->collection()->count();
                $lastDataRow = $dataStartRow + $dataRows - 1;

                $sheet->setShowGridlines(false);

                // === HEADER ROWS (1–4) ===
                $businessName = $this->data[0]['fh_business']['b_name'] ?? 'Business Name';
                $fromDate = is_array($this->fromdate) ? ($this->fromdate[0] ?? null) : $this->fromdate;
                $toDate = is_array($this->toDate) ? ($this->toDate[0] ?? null) : $this->toDate;

                $fromFormatted = $fromDate ? Carbon::parse($fromDate)->format('d-M-Y') : '';
                $toFormatted = $toDate ? Carbon::parse($toDate)->format('d-M-Y') : Carbon::now()->format('d-M-Y');
                $reportDate = trim($fromFormatted . ' to ' . $toFormatted, ' to');
                $printedOn = Carbon::now()->format('d-M-Y h:i A T');

                $sheet->setCellValue('A1', $businessName);
                $sheet->setCellValue('A2', 'Daily Attendance Detail Report');
                $sheet->setCellValue('A3', 'Date: ' . $reportDate);
                $sheet->setCellValue('A4', "Printed on: {$printedOn}");

                foreach (range(1, 4) as $r) {
                    $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
                    $sheet->getStyle("A{$r}")->getFont()->setSize(11)->setBold(true);
                    $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // === EMPTY ROW 5 (spacing) ===
                $sheet->mergeCells("A5:{$lastCol}5");

                // === TABLE HEADING (Row 6) ===
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
                    // === DATA ROWS (Row 7 onwards) ===
                    for ($r = $dataStartRow; $r <= $lastDataRow; $r++) {
                        $sheet->getRowDimension($r)->setRowHeight(25);
                    }

                    // === BORDERS ===
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

                    // === ALTERNATING ROWS ===
                    for ($r = $dataStartRow; $r <= $lastDataRow; $r++) {
                        $bg = (($r - $headingRow) % 2 == 1) ? 'F5F5F5' : 'FFFFFF'; // Start with white
                        $sheet->getStyle("A{$r}:{$lastCol}{$r}")
                            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bg);
                    }
                } else {
                    $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")
                        ->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D3D3D3');
                }

                // === FREEZE PANE BELOW HEADER (Row 6) → Freeze at Row 7 ===
                $sheet->freezePane('A7');
            },
        ];
    }
}