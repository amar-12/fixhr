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

class LeaveBalanceReport implements 
    FromCollection, 
    WithHeadings, 
    WithColumnWidths, 
    WithEvents, 
     WithCustomStartCell
{
    protected $data, $fromDate, $toDate, $filters, $leaveCategories = [];

    public function __construct($records, $filters = null, $fromDate = null, $toDate = null)
    {
        $this->data = $records;
        $this->fromDate = $fromDate ? Carbon::parse($fromDate) : null;
        $this->toDate = $toDate ? Carbon::parse($toDate) : null;
        $this->filters = $filters;
        $this->extractLeaveCategories();
    }

    protected function extractLeaveCategories()
    {
        $categories = [];

        foreach ($this->data as $record) {
            if (isset($record['fh_leave_cat_type'])) {
                $cat = $record['fh_leave_cat_type'];
                $categories[$cat['m_id']] = [
                    'm_id' => $cat['m_id'],
                    'name' => $cat['m_name'],
                    'type' => $cat['m_type'] ?? substr($cat['m_name'], 0, 2),
                    'color' => $this->extractColor($cat['m_other'] ?? '')
                ];
            }

            if (isset($record['fh_employees_details']['leaveBalances'])) {
                foreach ($record['fh_employees_details']['leaveBalances'] as $balance) {
                    if (isset($balance['fh_leave_type'])) {
                        $cat = $balance['fh_leave_type'];
                        $categories[$cat['m_id']] = [
                            'm_id' => $cat['m_id'],
                            'name' => $cat['m_name'],
                            'type' => $cat['m_type'] ?? substr($cat['m_name'], 0, 2),
                            'color' => $this->extractColor($cat['m_other'] ?? '')
                        ];
                    }
                }
            }
        }

        $this->leaveCategories = array_values($categories);
    }

    protected function extractColor($mOther)
    {
        $data = json_decode($mOther, true);
        return $data['color'] ?? 'EEEEEE'; // fallback
    }

    /* ------------------------------------------------------------------ */
    /*  START CELL – Data starts at A6                                    */
    /* ------------------------------------------------------------------ */
    public function startCell(): string
    {
        return 'A6';
    }

    public function collection()
    {
        $finalData = [];
        $serial = 1;

        $fromMonth = $this->fromDate?->startOfMonth();
        $toMonth = $this->toDate?->endOfMonth();

        if (!$fromMonth || !$toMonth) return collect();

        $months = [];
        $current = $fromMonth->copy();
        while ($current <= $toMonth) {
            $months[] = [
                'month' => $current->month,
                'year' => $current->year,
                'label' => $current->format('M-Y'),
            ];
            $current->addMonth();
        }

        $employees = [];
        foreach ($this->data as $record) {
            $empId = $record['lvr_emp_id'];
            if (!isset($employees[$empId])) {
                $employees[$empId] = $record;
            }
        }

        foreach ($employees as $record) {
            $emp = $record['fh_employees_details'];
            $empId = $record['lvr_emp_id'];

            foreach ($months as $month) {
                $row = [
                    $serial++,
                    $emp['emp_code'] ?? '-',
                    $emp['emp_full_name'] ?? '-',
                    $emp['fh_department']['d_name'] ?? '-',
                    $emp['fh_designation']['dg_name'] ?? '-',
                    $emp['fh_dealership']['dlr_name'] ?? '-',
                    isset($emp['emp_date_of_joining']) ? Carbon::parse($emp['emp_date_of_joining'])->format('d-M-Y') : '-',
                    $month['label'],
                ];

                $monthlyBalances = [];
                foreach ($this->leaveCategories as $cat) {
                    $monthlyBalances[$cat['m_id']] = [
                        'opening' => 0,
                        'used' => 0,
                        'remaining' => 0,
                        'carried_forward' => 0,
                    ];
                }

                if (!empty($emp['leaveBalances'])) {
                    foreach ($emp['leaveBalances'] as $balance) {
                        $balanceMonth = (int) $balance['lb_month'];
                        $balanceYear = (int) $balance['lb_year'];

                        if ($balanceMonth === $month['month'] && $balanceYear === $month['year']) {
                            $cat = $balance['fh_leave_type'] ?? null;
                            if (!$cat) continue;

                            $catId = $cat['m_id'];
                            if (!isset($monthlyBalances[$catId])) continue;

                            $monthlyBalances[$catId]['opening'] += (float) ($balance['lb_alloted_leave'] ?? 0);
                            $monthlyBalances[$catId]['carried_forward'] += (float) ($balance['lb_carried_forward'] ?? 0);
                            $monthlyBalances[$catId]['used'] += (float) ($balance['lb_taken_leave'] ?? 0);
                            $monthlyBalances[$catId]['remaining'] = max(
                                ($monthlyBalances[$catId]['opening'] + $monthlyBalances[$catId]['carried_forward']) - 
                                $monthlyBalances[$catId]['used'],
                                0
                            );
                        }
                    }
                }

                foreach ($this->leaveCategories as $cat) {
                    $type = $cat['type'];
                    $balance = $monthlyBalances[$cat['m_id']];

                    if ($type === 'UPL') {
                        $row[] = $balance['used'];
                    } else {
                        $row[] = $balance['opening'] + $balance['carried_forward'];
                        $row[] = $balance['used'];
                        $row[] = $balance['remaining'];
                    }
                }

                $finalData[] = $row;
            }
        }

        return collect($finalData);
    }

    public function headings(): array
    {
        return []; // Manual in registerEvents
    }

    /* ------------------------------------------------------------------ */
    /*  COLUMN WIDTHS – S# = 4, Emp Code = 7, Others = 13               */
    /* ------------------------------------------------------------------ */
    public function columnWidths(): array
    {
        $widths = [
            'A' => 4,   // S#
            'B' => 7,   // Emp Code
            'C' => 13,  // Employee Name
            'D' => 13,  // Department
            'E' => 13,  // Designation
            'F' => 13,  // Dealership
            'G' => 13,  // Date of Joining
            'H' => 13,  // Month
        ];

        $colIndex = 9;
        foreach ($this->leaveCategories as $cat) {
            if ($cat['type'] === 'UPL') {
                $col = Coordinate::stringFromColumnIndex($colIndex++);
                $widths[$col] = 13;
            } else {
                for ($i = 0; $i < 3; $i++) {
                    $col = Coordinate::stringFromColumnIndex($colIndex++);
                    $widths[$col] = 13;
                }
            }
        }

        return $widths;
    }

    /* ------------------------------------------------------------------ */
    /*  AFTER-SHEET – Styling (Data starts at A6)                         */
    /* ------------------------------------------------------------------ */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = $sheet->getHighestColumn();
                $mainHeaderRow = 4;
                $subHeaderRow = 5;
                $dataStartRow = 6;
                $dataRows = $this->collection()->count();
                $lastDataRow = $dataStartRow + $dataRows - 1;

                $sheet->setShowGridlines(false);

                // === HEADER ROWS (1–3) ===
                $businessName = $this->data[0]['fh_business']['b_name'] ?? 'Business Name';
                $fromFormatted = $this->fromDate?->format('F Y') ?? '';
                $toFormatted = $this->toDate?->format('F Y') ?? '';
                $period = $fromFormatted && $toFormatted ? "$fromFormatted to $toFormatted" : ($fromFormatted ?: $toFormatted);
                $printedOn = Carbon::now()->format('d-M-Y h:i A T');

                $sheet->setCellValue('A1', $businessName);
                $sheet->setCellValue('A2', 'Employee Leave Balance Report');
                $sheet->setCellValue('A3', 'Period: ' . $period . ' | Printed on: ' . $printedOn);

                foreach (range(1, 3) as $r) {
                    $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
                    $sheet->getStyle("A{$r}")->getFont()->setSize(11)->setBold(true);
                    $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // === MAIN HEADERS (Row 4) ===
                $basicHeaders = ['S#', 'Emp Code', 'Employee Name', 'Department', 'Designation', 'Dealership', 'Date of Joining', 'Month'];
                foreach ($basicHeaders as $i => $header) {
                    $col = Coordinate::stringFromColumnIndex($i + 1);
                    $sheet->setCellValue("{$col}{$mainHeaderRow}", $header);
                    $sheet->mergeCells("{$col}{$mainHeaderRow}:{$col}{$subHeaderRow}");
                }

                $startColIndex = count($basicHeaders) + 1;
                foreach ($this->leaveCategories as $cat) {
                    $colStart = Coordinate::stringFromColumnIndex($startColIndex);
                    $colorHex = str_replace('#', '', $cat['color']);

                    if ($cat['type'] === 'UPL') {
                        $sheet->mergeCells("{$colStart}{$mainHeaderRow}:{$colStart}{$subHeaderRow}");
                        $sheet->setCellValue("{$colStart}{$mainHeaderRow}", $cat['name']);
                        $startColIndex++;
                    } else {
                        $colEnd = Coordinate::stringFromColumnIndex($startColIndex + 2);
                        $sheet->mergeCells("{$colStart}{$mainHeaderRow}:{$colEnd}{$mainHeaderRow}");
                        $sheet->setCellValue("{$colStart}{$mainHeaderRow}", $cat['name']);
                        $subHeaders = ['Opening', 'Used', 'Remaining'];
                        foreach ($subHeaders as $i => $sub) {
                            $sheet->setCellValueByColumnAndRow($startColIndex + $i, $subHeaderRow, $sub);
                        }
                        $startColIndex += 3;
                    }

                    // === APPLY ORIGINAL CATEGORY COLOR ===
                    $range = $cat['type'] === 'UPL'
                        ? "{$colStart}{$mainHeaderRow}:{$colStart}{$subHeaderRow}"
                        : "{$colStart}{$mainHeaderRow}:{$colEnd}{$subHeaderRow}";

                    $sheet->getStyle($range)->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => $colorHex],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);
                }

                // === BASIC HEADER STYLE (Rows 4–5) ===
                $basicEndCol = Coordinate::stringFromColumnIndex(count($basicHeaders));
                $sheet->getStyle("A{$mainHeaderRow}:{$basicEndCol}{$subHeaderRow}")
                    ->getFont()->setSize(10)->setBold(true)->getColor()->setRGB('FFFFFF');
                $sheet->getStyle("A{$mainHeaderRow}:{$basicEndCol}{$subHeaderRow}")
                    ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('263871');
                $sheet->getStyle("A{$mainHeaderRow}:{$basicEndCol}{$subHeaderRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);

                $sheet->getRowDimension($mainHeaderRow)->setRowHeight(30);
                $sheet->getRowDimension($subHeaderRow)->setRowHeight(25);

                if ($dataRows > 0) {
                    for ($r = $dataStartRow; $r <= $lastDataRow; $r++) {
                        $sheet->getRowDimension($r)->setRowHeight(25);
                    }

                    $sheet->getStyle("A{$mainHeaderRow}:{$lastCol}{$lastDataRow}")
                        ->getBorders()->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D3D3D3');

                    $sheet->getStyle("A{$dataStartRow}:{$lastCol}{$lastDataRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setWrapText(true);

                    $sheet->getStyle("A{$dataStartRow}:{$lastCol}{$lastDataRow}")
                        ->getFont()->setSize(9);

                    for ($r = $dataStartRow; $r <= $lastDataRow; $r++) {
                        $bg = (($r - $dataStartRow) % 2 == 0) ? 'F5F5F5' : 'FFFFFF';
                        $sheet->getStyle("A{$r}:{$lastCol}{$r}")
                            ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bg);
                    }
                }

                // === FREEZE PANE BELOW SUB-HEADERS (Row 5) → Freeze at Row 6 ===
                $sheet->freezePane('A6');
            },
        ];
    }
}