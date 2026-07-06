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
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
class DailyAttendanceReport implements
    FromCollection,
    WithHeadings,
    WithColumnWidths,
    WithEvents,
    WithCustomStartCell
{
    protected $data;
    protected $filters;
    public function __construct($records, $filters)
    {
        $this->data = $records;
        $this->filters = $filters;
    }
    public function collection()
    {
        $serialNumber = 1;
        return collect($this->data)->map(function ($item) use (&$serialNumber) {
            // Use object notation for all properties
            $checkIn = !empty($item->atd_check_in_time) ? Carbon::parse($item->atd_check_in_time) : null;
            $checkOut = !empty($item->atd_check_out_time) ? Carbon::parse($item->atd_check_out_time) : null;
            $status = $item->fh_attendance_status->m_type ?? '-';
            // Handle work duration
            $workDuration = $item->atd_total_worked_hours
                ? (is_numeric($item->atd_total_worked_hours)
                    ? sprintf('%02d:%02d', floor($item->atd_total_worked_hours), ($item->atd_total_worked_hours - floor($item->atd_total_worked_hours)) * 60)
                    : (strtotime($item->atd_total_worked_hours) !== false
                        ? (new \DateTime($item->atd_total_worked_hours))->format('H:i')
                        : '-'))
                : '-';
            // Handle overtime duration
            $otDuration = $item->atd_overtime_hours
                ? (is_numeric($item->atd_overtime_hours)
                    ? sprintf('%02d:%02d', floor($item->atd_overtime_hours / 60), $item->atd_overtime_hours % 60)
                    : (strtotime($item->atd_overtime_hours) !== false
                        ? (new \DateTime($item->atd_overtime_hours))->format('H:i')
                        : '-'))
                : '-';
            // Handle late duration
            $lateDuration = $item->atd_late_duration
                ? (is_numeric($item->atd_late_duration)
                    ? sprintf('%02d:%02d', floor($item->atd_late_duration / 60), $item->atd_late_duration % 60)
                    : (strtotime($item->atd_late_duration) !== false
                        ? (new \DateTime($item->atd_late_duration))->format('H:i')
                        : '-'))
                : '-';
            // Handle early exit duration
            $earlyExitDuration = $item->atd_early_exit_duration
                ? (is_numeric($item->atd_early_exit_duration)
                    ? sprintf('%02d:%02d', floor($item->atd_early_exit_duration / 60), $item->atd_early_exit_duration % 60)
                    : (strtotime($item->atd_early_exit_duration) !== false
                        ? (new \DateTime($item->atd_early_exit_duration))->format('H:i')
                        : '-'))
                : '-';
            // Handle shift timing (check if fh_policy_shift_timing exists)
            $shiftStartTime = !empty($item->fh_policy_shift_timing) && !empty($item->fh_policy_shift_timing->pst_start_time)
                ? Carbon::parse($item->fh_policy_shift_timing->pst_start_time)->format('H:i')
                : '-';
            $shiftEndTime = !empty($item->fh_policy_shift_timing) && !empty($item->fh_policy_shift_timing->pst_end_time)
                ? Carbon::parse($item->fh_policy_shift_timing->pst_end_time)->format('H:i')
                : '-';
            // Build row with object notation
            $row = [
                'S#' => $serialNumber++,
                'Emp Code' => $item->fh_employees_details->emp_code ?? '',
                'Emp Name' => $item->fh_employees_details->emp_full_name ?? '',
            ];
            // Conditionally include Branch, Department, and Designation
            if (!empty($this->filters['branch'])) {
                $row['Branch'] = $item->fh_employees_details->fh_branch->br_name ?? 'Unknown Branch';
            }
            if (!empty($this->filters['department'])) {
                $row['Department'] = $item->fh_employees_details->fh_department->d_name ?? '-';
            }
            if (!empty($this->filters['designation'])) {
                $row['Designation'] = $item->fh_employees_details->fh_designation->dg_name ?? '-';
            }
            if (!empty($this->filters['dealership'])) {
                $row['Dealer'] = $item->fh_employees_details->fh_dealership->dlr_name ?? '-';
            }
            $row['Shift'] = $item->fh_policy_shift_timing->pst_name ?? 'Unknown Shift';
            $row['Shift Timing'] = $shiftStartTime . ' - ' . $shiftEndTime;
            $row['Check In'] = $checkIn ? $checkIn->format('H:i') : 'ABS';
            $row['Check Out'] = $checkOut ? $checkOut->format('H:i') : 'ABS';
            if (!empty($this->filters['workMode'])) {
                $row['Work Mode'] = $item->fh_attendance_work_mode->m_name ?? '-';
            }
            $row['Work Duration'] = $workDuration;
            if (!empty($this->filters['grade'])) {
                $row['Grade'] = $item->fh_employees_details->fh_grade->g_name ?? '-';
            }
            $row['OT'] = $otDuration;
            $row['Late Coming'] = $lateDuration;
            $row['Early Going'] = $earlyExitDuration;
            $row['Status'] = $status;
            $row['Remark'] = !empty($item->atd_remark) ? $item->atd_remark : '-';
            return $row;
        });
    }
    public function headings(): array
    {
        $headings = [
            'S#',
            'Emp Code',
            'Emp Name',
        ];
        // Conditionally include Branch, Department, and Designation
        if (!empty($this->filters['branch'])) {
            $headings[] = 'Branch';
        }
        if (!empty($this->filters['department'])) {
            $headings[] = 'Department';
        }
        if (!empty($this->filters['designation'])) {
            $headings[] = 'Designation';
        }
        if (!empty($this->filters['dealership'])) {
            $headings[] = 'Dealer';
        }
        $headings = array_merge($headings, [
            'Shift',
            'Shift Timing',
            'Check In',
            'Check Out',
        ]);
        if (!empty($this->filters['workMode'])) {
            $headings[] = 'Work Mode';
        }
        $headings[] = 'Work Duration';
        if (!empty($this->filters['grade'])) {
            $headings[] = 'Grade';
        }
        $headings = array_merge($headings, [
            'OT',
            'Late Coming',
            'Early Going',
            'Status',
            'Remark',
        ]);
        return $headings;
    }
    public function columnWidths(): array
    {
        $widths = [];
        $headings = $this->headings();
        $collection = $this->collection();
        $i = 1;
        foreach ($headings as $index => $heading) {
            $col = Coordinate::stringFromColumnIndex($i);
            $maxLength = strlen($heading);
            // Column indices
            $sNoIndex = 0;
            $empCodeIndex = 1;
            $empNameIndex = 2;
            $branchIndex = !empty($this->filters['branch']) ? 3 : -1;
            $departmentIndex = !empty($this->filters['department']) ? ($branchIndex >= 0 ? 4 : 3) : -1;
            $designationIndex = !empty($this->filters['designation']) ? ($departmentIndex >= 0 ? ($branchIndex >= 0 ? 5 : 4) : ($branchIndex >= 0 ? 4 : 3)) : -1;
            $dealerIndex = !empty($this->filters['dealership']) ? ($designationIndex >= 0 ? ($departmentIndex >= 0 ? ($branchIndex >= 0 ? 6 : 5) : ($branchIndex >= 0 ? 5 : 4)) : ($departmentIndex >= 0 ? ($branchIndex >= 0 ? 5 : 4) : ($branchIndex >= 0 ? 4 : 3))) : -1;
            $shiftIndex = $dealerIndex >= 0 ? ($designationIndex >= 0 ? ($departmentIndex >= 0 ? ($branchIndex >= 0 ? 7 : 6) : ($branchIndex >= 0 ? 6 : 5)) : ($departmentIndex >= 0 ? ($branchIndex >= 0 ? 6 : 5) : ($branchIndex >= 0 ? 5 : 4))) : ($designationIndex >= 0 ? ($departmentIndex >= 0 ? ($branchIndex >= 0 ? 6 : 5) : ($branchIndex >= 0 ? 5 : 4)) : ($departmentIndex >= 0 ? ($branchIndex >= 0 ? 5 : 4) : ($branchIndex >= 0 ? 4 : 3)));
            $shiftTimingIndex = $shiftIndex + 1;
            $checkInIndex = $shiftTimingIndex + 1;
            $checkOutIndex = $checkInIndex + 1;
            $workModeIndex = !empty($this->filters['workMode']) ? ($checkOutIndex + 1) : -1;
            $workDurationIndex = $workModeIndex >= 0 ? ($checkOutIndex + 2) : ($checkOutIndex + 1);
            $gradeIndex = !empty($this->filters['grade']) ? ($workDurationIndex + 1) : -1;
            $otIndex = $gradeIndex >= 0 ? ($workDurationIndex + 2) : ($workDurationIndex + 1);
            $lateComingIndex = $otIndex + 1;
            $earlyGoingIndex = $otIndex + 2;
            $statusIndex = $otIndex + 3;
            $remarkIndex = $otIndex + 4;
            // Adjust width based on column type
            if ($index === $empNameIndex) {
                $maxLength = max($maxLength, $collection->max(function ($row) use ($empNameIndex) {
                    return strlen($row[$empNameIndex] ?? '');
                }) ?: 15);
                $widths[$col] = min($maxLength, 25);
            } elseif ($index === $empCodeIndex) {
                $maxLength = max($maxLength, $collection->max(function ($row) use ($empCodeIndex) {
                    return strlen($row[$empCodeIndex] ?? '');
                }) ?: 10);
                $widths[$col] = min($maxLength + 2, 15);
            } elseif ($index === $branchIndex && $branchIndex >= 0) {
                $maxLength = max($maxLength, $collection->max(function ($row) use ($branchIndex) {
                    return strlen($row[$branchIndex] ?? '');
                }) ?: 10);
                $widths[$col] = min($maxLength + 2, 20);
            } elseif ($index === $departmentIndex && $departmentIndex >= 0) {
                $maxLength = max($maxLength, $collection->max(function ($row) use ($departmentIndex) {
                    return strlen($row[$departmentIndex] ?? '');
                }) ?: 10);
                $widths[$col] = min($maxLength + 2, 20);
            } elseif ($index === $designationIndex && $designationIndex >= 0) {
                $maxLength = max($maxLength, $collection->max(function ($row) use ($designationIndex) {
                    return strlen($row[$designationIndex] ?? '');
                }) ?: 10);
                $widths[$col] = min($maxLength + 2, 15);
            } elseif ($index === $dealerIndex && $dealerIndex >= 0) {
                $maxLength = max($maxLength, $collection->max(function ($row) use ($dealerIndex) {
                    return strlen($row[$dealerIndex] ?? '');
                }) ?: 10);
                $widths[$col] = min($maxLength + 2, 20);
            } elseif ($index === $shiftIndex) {
                $maxLength = max($maxLength, $collection->max(function ($row) use ($shiftIndex) {
                    return strlen($row[$shiftIndex] ?? '');
                }) ?: 10);
                $widths[$col] = min($maxLength + 2, 18);
            } elseif ($index === $shiftTimingIndex) {
                $widths[$col] = 12;
            } elseif ($index === $checkInIndex || $index === $checkOutIndex) {
                $widths[$col] = 10;
            } elseif ($index === $workModeIndex && $workModeIndex >= 0) {
                $maxLength = max($maxLength, $collection->max(function ($row) use ($workModeIndex) {
                    return strlen($row[$workModeIndex] ?? '');
                }) ?: 10);
                $widths[$col] = min($maxLength + 2, 15);
            } elseif ($index === $workDurationIndex) {
                $widths[$col] = 12;
            } elseif ($index === $gradeIndex && $gradeIndex >= 0) {
                $maxLength = max($maxLength, $collection->max(function ($row) use ($gradeIndex) {
                    return strlen($row[$gradeIndex] ?? '');
                }) ?: 10);
                $widths[$col] = min($maxLength + 2, 15);
            } elseif ($index === $otIndex || $index === $lateComingIndex || $index === $earlyGoingIndex) {
                $widths[$col] = 10;
            } elseif ($index === $statusIndex) {
                $widths[$col] = 10;
            } elseif ($index === $remarkIndex) {
                $maxLength = max($maxLength, $collection->max(function ($row) use ($remarkIndex) {
                    return strlen($row[$remarkIndex] ?? '');
                }) ?: 10);
                $widths[$col] = min($maxLength + 2, 30);
            } else {
                $widths[$col] = 8;
            }
            $i++;
        }
        return $widths;
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->setShowGridlines(false);
                // Set header information
                if ($this->data->isNotEmpty()) {
                    $b_id = $this->data->first()->fh_business->b_id ?? '';
                    $company = Business::where('b_id', $b_id)->value('b_name') ?? 'N/A';
                }
                $reportDate = $this->data->isNotEmpty() && !empty($this->data->first()->atd_date) ? Carbon::parse($this->data->first()->atd_date)->format('d-M-Y') : 'N/A';
                $printedOn = Carbon::now()->format('d-M-Y h:i A T');
                $sheet->setCellValue('A1', $company);
                $sheet->setCellValue('A2', 'Daily Attendance Detail Report');
                $sheet->setCellValue('A3', 'Date: ' . $reportDate);
                $sheet->setCellValue('A4', 'Printed on: ' . $printedOn);
                // Style header rows (1–4)
                foreach (range(1, 4) as $row) {
                    $sheet->mergeCells("A{$row}:I{$row}");
                    $sheet->getStyle("A{$row}")
                        ->getFont()
                        ->setSize($row <= 2 ? 12 : 11)
                        ->setBold(true);
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }
                // Define heading row
                $headingRow = 5;
                $rowCount = $this->collection()->count();
                $lastRow = $headingRow + $rowCount;
                $lastColumnIndex = count($this->headings());
                $lastColumnLetter = Coordinate::stringFromColumnIndex($lastColumnIndex);
                // Style heading row
                $sheet->getStyle("A{$headingRow}:{$lastColumnLetter}{$headingRow}")
                    ->getFont()
                    ->setSize(10)
                    ->setBold(true);
                $sheet->getStyle("A{$headingRow}:{$lastColumnLetter}{$headingRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);
                $sheet->getStyle("A{$headingRow}:{$lastColumnLetter}{$headingRow}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('263871');
                $sheet->getStyle("A{$headingRow}:{$lastColumnLetter}{$headingRow}")
                    ->getFont()->getColor()->setRGB('FFFFFF');
                // Apply styles to data
                if ($rowCount > 0) {
                    // Apply borders
                    $dataRange = "A{$headingRow}:{$lastColumnLetter}{$lastRow}";
                    $sheet->getStyle($dataRange)
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->getColor()
                        ->setRGB('D3D3D3');
                    // Center-align data cells
                    $sheet->getStyle("A" . ($headingRow + 1) . ":{$lastColumnLetter}{$lastRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);
                    // Apply font size
                    $sheet->getStyle("A" . ($headingRow + 1) . ":{$lastColumnLetter}{$lastRow}")
                        ->getFont()
                        ->setSize(8);
                    // Wrap text for Emp Name column
                    $empNameColumn = !empty($this->filters['branch']) ? 'C' : (!empty($this->filters['department']) ? 'C' : (!empty($this->filters['designation']) ? 'C' : 'C'));
                    $sheet->getStyle("{$empNameColumn}" . ($headingRow + 1) . ":{$empNameColumn}{$lastRow}")
                        ->getAlignment()
                        ->setWrapText(true);
                    // Apply alternating background colors
                    for ($row = $headingRow + 1; $row <= $lastRow; $row++) {
                        $color = (($row - $headingRow) % 2 == 1) ? 'F5F5F5' : 'FFFFFF';
                        $sheet->getStyle("A{$row}:{$lastColumnLetter}{$row}")
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB($color);
                    }
                }
                // Freeze panes: rows 1–5 and columns A–C
                $freezeColumn = !empty($this->filters['branch']) ? 'D' : (!empty($this->filters['department']) ? 'D' : (!empty($this->filters['designation']) ? 'D' : 'D'));
                $sheet->freezePane($freezeColumn . '6');
                // Set row heights
                $sheet->getRowDimension($headingRow)->setRowHeight(30);
                for ($row = $headingRow + 1; $row <= $lastRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(30);
                }
                // Add Abbreviations section
                $legendRow = $lastRow + 2;
                $sheet->setCellValue("A{$legendRow}", 'Abbreviations');
                $sheet->mergeCells("A{$legendRow}:{$lastColumnLetter}{$legendRow}");
                $sheet->getStyle("A{$legendRow}")
                    ->getFont()
                    ->setSize(10)
                    ->setBold(true);
                $sheet->getStyle("A{$legendRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $abbreviations = [
                    'P' => 'Present',
                    'ABS' => 'Absent',
                    'WO' => 'Weekly Off',
                    'WOP' => 'Weekly Off Present',
                    'MSP' => 'Missed Punch',
                    'HD' => 'Half Day',
                    'HO' => 'Holiday',
                    'OD' => 'Out Door Duty',
                    'CL' => 'Casual Leave',
                    'PL' => 'Privilege Leave',
                    'SL' => 'Sick Leave',
                    'COFF' => 'Compensatory Off',
                    'EL' => 'Earned Leave',
                    'ML' => 'Maternity Leave',
                    'MRL' => 'Marriage Leave',
                    'BL' => 'Bereavement Leave',
                    'UPL' => 'Unpaid Leave'
                ];
                $abbrStartRow = $legendRow + 1;
                foreach ($abbreviations as $abbr => $desc) {
                    $row = $abbrStartRow++;
                    $sheet->setCellValue("A{$row}", "{$abbr}: {$desc}");
                    $sheet->mergeCells("A{$row}:{$lastColumnLetter}{$row}");
                    $sheet->getStyle("A{$row}")
                        ->getFont()
                        ->setSize(9);
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }
            }
        ];
    }
    public function startCell(): string
    {
        return 'A5';
    }
}
