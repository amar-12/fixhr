<?php

namespace App\Exports\Attendance;

use App\Models\Business;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class MisspunchRegularizationReport implements FromCollection, WithHeadings, WithColumnWidths, WithEvents, WithCustomStartCell
{
    protected $records;
    protected $filters;
    protected $slug;
    protected $fromDate;
    protected $toDate;
    protected $businessId;

    public function __construct($records, $filters, $slug, $fromDate, $toDate, $businessId)
    {
        $this->records = collect($records);
        $this->filters = $filters;
        $this->slug = $slug;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->businessId = $businessId;
    }

    /** Prepare the collection data */
    public function collection()
    {
        return $this->records->map(function ($row, $index) {
            $base = [
                'S#' => (string) ($index + 1),
                'Employee Name' => $row['emp_name'],
                'Employee Code' => $row['emp_code'],
            ];

            // Add filter columns only when enabled
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
            if ($this->filters['grade'] ?? false) {
                $base['Grade'] = $row['grade_name'] ?? '-';
            }
            if ($this->filters['jobStatus'] ?? false) {
                $base['Job Status'] = $row['job_status_name'] ?? '-';
            }

            // Add the rest of the columns (Updated Log added)
            $base = array_merge($base, [
                'Date' => $this->formatDateSafe($row['date']),
                'Actual Check In' => $this->formatTimeSafe($row['actual_check_in']),
                'Actual Check Out' => $this->formatTimeSafe($row['actual_check_out']),
                'Exception Check In' => $this->formatTimeSafe($row['exception_check_in']),
                'Exception Check Out' => $this->formatTimeSafe($row['exception_check_out']),
                'Updated Check In' => $this->formatTimeSafe($row['updated_check_in'] ?? '-'),   // NEW
                'Updated Check Out' => $this->formatTimeSafe($row['updated_check_out'] ?? '-'), // NEW
                'Working Hour' => $row['working_hour'],
                'Type' => $row['type'],
                'Reason' => $row['reason'],
                'Approval Status' => $this->parseApprovalStatus($row['approval_status']),
                'Approval Log' => $row['approval_log'],
                'Submitted To' => $row['submitted_to'],
                'Applied Date' => $this->formatDateSafe($row['applied_date']),
            ]);

            return $base;
        })->values();
    }

    private function formatDateSafe($date)
    {
        if (empty($date) || $date === '-' || $date === null) {
            return '-';
        }
        try {
            return Carbon::parse($date)->format('d-M-Y');
        } catch (\Exception $e) {
            return '-';
        }
    }

    private function formatTimeSafe($time)
    {
        if (empty($time) || $time === '-' || $time === null) {
            return '-';
        }
        try {
            return Carbon::parse($time)->format('h:i A');
        } catch (\Exception $e) {
            return '-';
        }
    }

    private function parseApprovalStatus($status)
    {
        if (empty($status) || $status === '-' || $status === null) {
            return '-';
        }
        $decodedStatus = html_entity_decode($status, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $cleanStatus = trim(strip_tags($decodedStatus));
        $cleanStatus = preg_replace('/\s+/', ' ', $cleanStatus);
        return $cleanStatus === '' ? '-' : $cleanStatus;
    }

    /** Define column headings */
    public function headings(): array
    {
        $headings = [
            'S#',
            'Employee Name',
            'Employee Code',
        ];

        // Add filter columns only when enabled
        if ($this->filters['branch'] ?? false)      $headings[] = 'Branch';
        if ($this->filters['department'] ?? false)  $headings[] = 'Department';
        if ($this->filters['designation'] ?? false) $headings[] = 'Designation';
        if ($this->filters['dealership'] ?? false)  $headings[] = 'Dealership';
        if ($this->filters['shift'] ?? false)       $headings[] = 'Shift';
        if ($this->filters['workMode'] ?? false)    $headings[] = 'Work Mode';
        if ($this->filters['grade'] ?? false)       $headings[] = 'Grade';
        if ($this->filters['jobStatus'] ?? false)   $headings[] = 'Job Status';

        // Add the rest of the columns (Updated Log added)
        $headings = array_merge($headings, [
            'Date',
            'Actual Check In',
            'Actual Check Out',
            'Exception Check In',
            'Exception Check Out',
            'Updated Check In',      // NEW
            'Updated Check Out',     // NEW
            'Working Hour',
            'Type',
            'Reason',
            'Approval Status',
            'Approval Log',
            'Submitted To',
            'Applied Date',
        ]);

        return $headings;
    }

    /** Define column widths */
    public function columnWidths(): array
    {
        $widths = [
            'A' => 8,   // S#
            'B' => 25,  // Employee Name
            'C' => 12,  // Employee Code
        ];

        $col = 'D'; // Start filter columns after Employee Code
        $optionalWidths = [
            'branch'      => 12,
            'department'  => 12,
            'designation' => 12,
            'dealership'  => 12,
            'shift'       => 12,
            'workMode'    => 12,
            'grade'       => 12,
            'jobStatus'   => 12,
        ];

        foreach ($optionalWidths as $key => $width) {
            if ($this->filters[$key] ?? false) {
                $widths[$col] = $width;
                $col++;
            }
        }

        // Add widths for remaining columns (Updated Log added)
        $remainingColumns = [
            $col++ => 12, // Date
            $col++ => 12, // Actual Check In
            $col++ => 12, // Actual Check Out
            $col++ => 12, // Exception Check In
            $col++ => 12, // Exception Check Out
            $col++ => 12, // Updated Check In     ← NEW
            $col++ => 12, // Updated Check Out    ← NEW
            $col++ => 12, // Working Hour
            $col++ => 12, // Type
            $col++ => 20, // Reason
            $col++ => 12, // Approval Status
            $col++ => 20, // Approval Log
            $col++ => 20, // Submitted To
            $col   => 12, // Applied Date
        ];

        return array_merge($widths, $remainingColumns);
    }

    /** Format Excel sheet */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->setShowGridlines(false);

                // Company name and report details
                $company = Business::where('b_id', $this->businessId)->first()['b_name'] ?? '';
                $printed = Carbon::now()->format('d-M-Y h:i A T');
                $fromDate = Carbon::parse($this->fromDate)->format('d-M-Y');
                $toDate = Carbon::parse($this->toDate)->format('d-M-Y');

                // Set header rows
                $sheet->setCellValue('A1', $company);
                $sheet->setCellValue('A2', 'Missed Punch Regularization Report');
                $sheet->setCellValue('A3', "Period: {$fromDate} to {$toDate}");
                $sheet->setCellValue('A4', "Printed on: {$printed}");
                
                $lastCol = $this->lastColumn();
                for ($row = 1; $row <= 4; $row++) {
                    $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
                    $sheet->getStyle("A{$row}")
                        ->getFont()
                        ->setSize(11)
                        ->setBold(true);
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // Define column ranges
                $headingRow = 6;
                $subHeadingRow = 7;
                $rowCount = $this->collection()->count();
                $lastDataRow = $subHeadingRow + $rowCount;

                // Calculate column positions
                $filterColCount = 0;
                $optionalFilters = ['branch', 'department', 'designation', 'dealership', 'shift', 'workMode', 'grade', 'jobStatus'];
                foreach ($optionalFilters as $key) {
                    if ($this->filters[$key] ?? false) $filterColCount++;
                }

                $dateCol = Coordinate::stringFromColumnIndex(4 + $filterColCount);
                $actualLogStartCol = Coordinate::stringFromColumnIndex(Coordinate::columnIndexFromString($dateCol) + 1);
                $actualLogEndCol = Coordinate::stringFromColumnIndex(Coordinate::columnIndexFromString($actualLogStartCol) + 1);
                $exceptionLogStartCol = Coordinate::stringFromColumnIndex(Coordinate::columnIndexFromString($actualLogEndCol) + 1);
                $exceptionLogEndCol = Coordinate::stringFromColumnIndex(Coordinate::columnIndexFromString($exceptionLogStartCol) + 1);
                
                // NEW: Updated Log columns
                $updatedLogStartCol = Coordinate::stringFromColumnIndex(Coordinate::columnIndexFromString($exceptionLogEndCol) + 1);
                $updatedLogEndCol = Coordinate::stringFromColumnIndex(Coordinate::columnIndexFromString($updatedLogStartCol) + 1);
                
                $workingHourCol = Coordinate::stringFromColumnIndex(Coordinate::columnIndexFromString($updatedLogEndCol) + 1);

                // Set main column headings (merged cells)
                $mainColumns = [
                    'A' => 'S#',
                    'B' => 'Employee Name',
                    'C' => 'Employee Code',
                ];

                foreach ($mainColumns as $col => $label) {
                    $sheet->mergeCells("{$col}{$headingRow}:{$col}{$subHeadingRow}");
                    $sheet->setCellValue("{$col}{$headingRow}", $label);
                    $sheet->getStyle("{$col}{$headingRow}:{$col}{$subHeadingRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle("{$col}{$headingRow}:{$col}{$subHeadingRow}")
                        ->getFont()
                        ->setSize(10)
                        ->setBold(true);
                }

                // Date column
                $sheet->mergeCells("{$dateCol}{$headingRow}:{$dateCol}{$subHeadingRow}");
                $sheet->setCellValue("{$dateCol}{$headingRow}", "Date");
                $sheet->getStyle("{$dateCol}{$headingRow}:{$dateCol}{$subHeadingRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("{$dateCol}{$headingRow}:{$dateCol}{$subHeadingRow}")
                    ->getFont()
                    ->setSize(10)
                    ->setBold(true);

                // Set Actual Log header
                $sheet->setCellValue("{$actualLogStartCol}{$headingRow}", 'Actual Log');
                $sheet->mergeCells("{$actualLogStartCol}{$headingRow}:{$actualLogEndCol}{$headingRow}");
                $sheet->getStyle("{$actualLogStartCol}{$headingRow}")
                    ->getFont()
                    ->setSize(10)
                    ->setBold(true);
                $sheet->getStyle("{$actualLogStartCol}{$headingRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Set Exception Log header
                $sheet->setCellValue("{$exceptionLogStartCol}{$headingRow}", 'Exception Log');
                $sheet->mergeCells("{$exceptionLogStartCol}{$headingRow}:{$exceptionLogEndCol}{$headingRow}");
                $sheet->getStyle("{$exceptionLogStartCol}{$headingRow}")
                    ->getFont()
                    ->setSize(10)
                    ->setBold(true);
                $sheet->getStyle("{$exceptionLogStartCol}{$headingRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // NEW: Set Updated Log header
                $sheet->setCellValue("{$updatedLogStartCol}{$headingRow}", 'Updated Log');
                $sheet->mergeCells("{$updatedLogStartCol}{$headingRow}:{$updatedLogEndCol}{$headingRow}");
                $sheet->getStyle("{$updatedLogStartCol}{$headingRow}")
                    ->getFont()
                    ->setSize(10)
                    ->setBold(true);
                $sheet->getStyle("{$updatedLogStartCol}{$headingRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Set sub-headers
                $subHeaders = [
                    $actualLogStartCol . '7' => 'Check In',
                    $actualLogEndCol . '7' => 'Check Out',
                    $exceptionLogStartCol . '7' => 'Check In',
                    $exceptionLogEndCol . '7' => 'Check Out',
                    $updatedLogStartCol . '7' => 'Check In',     // NEW
                    $updatedLogEndCol . '7' => 'Check Out',      // NEW
                ];

                foreach ($subHeaders as $cell => $label) {
                    $sheet->setCellValue($cell, $label);
                    $sheet->getStyle($cell)
                        ->getFont()
                        ->setSize(9)
                        ->setBold(true);
                    $sheet->getStyle($cell)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // Set remaining main columns
                $remainingColumns = [
                    $workingHourCol => 'Working Hour',
                    Coordinate::stringFromColumnIndex(Coordinate::columnIndexFromString($workingHourCol) + 1) => 'Type',
                    Coordinate::stringFromColumnIndex(Coordinate::columnIndexFromString($workingHourCol) + 2) => 'Reason',
                    Coordinate::stringFromColumnIndex(Coordinate::columnIndexFromString($workingHourCol) + 3) => 'Approval Status',
                    Coordinate::stringFromColumnIndex(Coordinate::columnIndexFromString($workingHourCol) + 4) => 'Approval Log',
                    Coordinate::stringFromColumnIndex(Coordinate::columnIndexFromString($workingHourCol) + 5) => 'Submitted To',
                    Coordinate::stringFromColumnIndex(Coordinate::columnIndexFromString($workingHourCol) + 6) => 'Applied Date',
                ];

                foreach ($remainingColumns as $col => $label) {
                    $sheet->mergeCells("{$col}{$headingRow}:{$col}{$subHeadingRow}");
                    $sheet->setCellValue("{$col}{$headingRow}", $label);
                    $sheet->getStyle("{$col}{$headingRow}:{$col}{$subHeadingRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle("{$col}{$headingRow}:{$col}{$subHeadingRow}")
                        ->getFont()
                        ->setSize(10)
                        ->setBold(true);
                }

                // ---------- OPTIONAL FILTER HEADINGS ----------
                $optionalStartCol = 'D';
                $optionalLabels = [
                    'branch'      => 'Branch',
                    'department'  => 'Department',
                    'designation' => 'Designation',
                    'dealership'  => 'Dealership',
                    'shift'       => 'Shift',
                    'workMode'    => 'Work Mode',
                    'grade'       => 'Grade',
                    'jobStatus'   => 'Job Status',
                ];

                foreach ($optionalLabels as $key => $label) {
                    if ($this->filters[$key] ?? false) {
                        $sheet->mergeCells("{$optionalStartCol}{$headingRow}:{$optionalStartCol}{$subHeadingRow}");
                        $sheet->setCellValue("{$optionalStartCol}{$headingRow}", $label);
                        $sheet->getStyle("{$optionalStartCol}{$headingRow}:{$optionalStartCol}{$subHeadingRow}")
                            ->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                            ->setVertical(Alignment::VERTICAL_CENTER);
                        $sheet->getStyle("{$optionalStartCol}{$headingRow}:{$optionalStartCol}{$subHeadingRow}")
                            ->getFont()
                            ->setSize(10)
                            ->setBold(true);
                        $optionalStartCol++;
                    }
                }

                // Apply background color #263871 and white font to headers
                $headerRange = "A{$headingRow}:{$lastCol}{$subHeadingRow}";
                $sheet->getStyle($headerRange)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('263871');
                $sheet->getStyle($headerRange)->getFont()->getColor()->setRGB('FFFFFF');

                // ---------- HEADER BORDERS (Thin White Lines) ----------
                $headerBorderStyle = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'FFFFFF'],
                        ],
                    ],
                ];
                $sheet->getStyle($headerRange)->applyFromArray($headerBorderStyle);

                // Apply borders and center-align data cells
                if ($rowCount > 0) {
                    $dataRange = "A8:{$lastCol}{$lastDataRow}";
                    $sheet->getStyle($dataRange)
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->getColor()
                        ->setRGB('D3D3D3');
                    $sheet->getStyle("A8:{$lastCol}{$lastDataRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle("A8:{$lastCol}{$lastDataRow}")
                        ->getFont()
                        ->setSize(8);

                    // Left-align and wrap text for specific columns
                    $sheet->getStyle("B8:B{$lastDataRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                        ->setWrapText(true);
                    
                    $reasonCol = Coordinate::stringFromColumnIndex(Coordinate::columnIndexFromString($workingHourCol) + 2);
                    $sheet->getStyle("{$reasonCol}8:{$reasonCol}{$lastDataRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                        ->setWrapText(true);

                    $approvalLogCol = Coordinate::stringFromColumnIndex(Coordinate::columnIndexFromString($workingHourCol) + 4);
                    $sheet->getStyle("{$approvalLogCol}8:{$approvalLogCol}{$lastDataRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                        ->setWrapText(true);

                    $submittedToCol = Coordinate::stringFromColumnIndex(Coordinate::columnIndexFromString($workingHourCol) + 5);
                    $sheet->getStyle("{$submittedToCol}8:{$submittedToCol}{$lastDataRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                        ->setWrapText(true);
                }

                // Highlighting Logic - Updated Log (Green) takes priority
                for ($i = 8; $i <= $lastDataRow; $i++) {
                    $updatedCheckIn = $sheet->getCell("{$updatedLogStartCol}{$i}")->getValue();
                    $updatedCheckOut = $sheet->getCell("{$updatedLogEndCol}{$i}")->getValue();
                    $exceptionCheckIn = $sheet->getCell("{$exceptionLogStartCol}{$i}")->getValue();
                    $exceptionCheckOut = $sheet->getCell("{$exceptionLogEndCol}{$i}")->getValue();

                    if ($updatedCheckIn !== '-' || $updatedCheckOut !== '-') {
                        // Updated Log exists → Light Green
                        $sheet->getStyle("A{$i}:{$lastCol}{$i}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('E6FFE6');
                    } elseif ($exceptionCheckIn !== '-' || $exceptionCheckOut !== '-') {
                        // Only Exception Log → Light Yellow (as before)
                        $sheet->getStyle("A{$i}:{$lastCol}{$i}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('FFFFE0');
                    }
                }

                // Adjust row heights
                $sheet->getRowDimension($headingRow)->setRowHeight(20);
                $sheet->getRowDimension($subHeadingRow)->setRowHeight(20);
                for ($row = 8; $row <= $lastDataRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(18);
                }

                // Freeze header rows
                $sheet->freezePane('A8');
            },
        ];
    }

    /** Helper method to get last column */
    private function lastColumn(): string
    {
        $baseCols = 3; // A-C (S#, Employee Name, Employee Code)
        $extra = 0;
        $optionalFilters = ['branch', 'department', 'designation', 'dealership', 'shift', 'workMode', 'grade', 'jobStatus'];
        
        foreach ($optionalFilters as $key) {
            if ($this->filters[$key] ?? false) $extra++;
        }

        // Updated: +2 more columns for Updated Log
        return Coordinate::stringFromColumnIndex($baseCols + $extra + 14); // 12 old + 2 new = 14 fixed after filters
    }

    public function startCell(): string
    {
        return 'A7';
    }
}