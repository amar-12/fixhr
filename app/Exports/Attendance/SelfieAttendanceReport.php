<?php

namespace App\Exports\Attendance;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class SelfieAttendanceReport implements FromCollection, WithHeadings, WithColumnWidths, WithStyles, WithEvents, WithCustomStartCell
{
    protected $data;
    protected $filters;
    protected $punchingMode;
    protected $date;

    public function __construct($records, $filters, $punchingMode,$date)
    {
       
        $this->data = $records;
        $this->filters = $filters;
        $this->punchingMode = $punchingMode;
        $this->date = $date;

    }

    public function collection()
    {
        $serialNumber = 1;

        return collect($this->data)->map(function ($item) use (&$serialNumber) {
            $checkIn = !empty($item->atd_check_in_time) ? Carbon::parse($item->atd_check_in_time) : null;
            $checkOut = !empty($item->atd_check_out_time) ? Carbon::parse($item->atd_check_out_time) : null;
            $status = $item->fh_attendance_status->m_type ?? '-';

            $workDuration = $item->atd_total_worked_hours
                ? (is_numeric($item->atd_total_worked_hours)
                    ? sprintf('%02d:%02d', floor($item->atd_total_worked_hours), ($item->atd_total_worked_hours - floor($item->atd_total_worked_hours)) * 60)
                    : (strtotime($item->atd_total_worked_hours) !== false
                        ? (new \DateTime($item->atd_total_worked_hours))->format('H:i')
                        : '-'))
                : '-';

            $otDuration = $item->atd_overtime_hours
                ? (is_numeric($item->atd_overtime_hours)
                    ? sprintf('%02d:%02d', floor($item->atd_overtime_hours / 60), $item->atd_overtime_hours % 60)
                    : (strtotime($item->atd_overtime_hours) !== false
                        ? (new \DateTime($item->atd_overtime_hours))->format('H:i')
                        : '-'))
                : '-';

            $lateDuration = $item->atd_late_duration
                ? (is_numeric($item->atd_late_duration)
                    ? sprintf('%02d:%02d', floor($item->atd_late_duration / 60), $item->atd_late_duration % 60)
                    : (strtotime($item->atd_late_duration) !== false
                        ? (new \DateTime($item->atd_late_duration))->format('H:i')
                        : '-'))
                : '-';

            $earlyExitDuration = $item->atd_early_exit_duration
                ? (is_numeric($item->atd_early_exit_duration)
                    ? sprintf('%02d:%02d', floor($item->atd_early_exit_duration / 60), $item->atd_early_exit_duration % 60)
                    : (strtotime($item->atd_early_exit_duration) !== false
                        ? (new \DateTime($item->atd_early_exit_duration))->format('H:i')
                        : '-'))
                : '-';

            $shiftStartTime = !empty($item->fh_policy_shift_timing->pst_start_time)
                ? Carbon::parse($item->fh_policy_shift_timing->pst_start_time)->format('H:i')
                : '-';

            $shiftEndTime = !empty($item->fh_policy_shift_timing->pst_end_time)
                ? Carbon::parse($item->fh_policy_shift_timing->pst_end_time)->format('H:i')
                : '-';

            $attendanceDate = Carbon::parse($item->atd_date) ?? '-';

            $punchInPhoto = '-';
            if (!empty($item->atd_punchin_photo)) {
                try {
                    $photos = json_decode($item->atd_punchin_photo, true);
                    $punchInPhoto = (is_array($photos) && !empty($photos)) ? $photos[0] : $item->atd_punchin_photo;
                } catch (\Exception $e) {
                    $punchInPhoto = str_replace('\/', '/', $item->atd_punchin_photo);
                }
            }

            $punchOutPhoto = '-';
            if (!empty($item->atd_punchout_photo)) {
                try {
                    $photos = json_decode($item->atd_punchout_photo, true);
                    $punchOutPhoto = (is_array($photos) && !empty($photos)) ? $photos[0] : $item->atd_punchout_photo;
                } catch (\Exception $e) {
                    $punchOutPhoto = str_replace('\/', '/', $item->atd_punchout_photo);
                }
            }

            $row = [
                'S#' => $serialNumber++,
                'Emp Code' => $item->fh_employees_details->emp_code ?? '',
                'Emp Name' => $item->fh_employees_details->emp_full_name ?? '',
                'Date' => $attendanceDate->format('d-M-y'),
            ];

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
            if (!empty($this->filters['shift'])) {
                $row['Shift'] = $item->fh_policy_shift_timing->pst_name ?? 'Unknown Shift';
            }
            if (!empty($this->filters['shiftTiming'])) {
                $row['Shift Timing'] = $shiftStartTime . ' - ' . $shiftEndTime;
            }
            if (!empty($this->filters['checkInMethod'])) {
                $row['Check In Method'] = $item->fh_attendance_checkin_type->m_name ?? '-';
            }
            if (!empty($this->filters['checkIn'])) {
                $row['Check in'] = $checkIn ? $checkIn->format('H:i') : 'ABS';
            }
            if (!empty($this->filters['checkOut'])) {
                $row['Check out'] = $checkOut ? $checkOut->format('H:i') : 'ABS';
            }
            if (!empty($this->filters['workMode'])) {
                $row['Work Mode'] = $item->fh_attendance_work_mode->m_name ?? '-';
            }
            if (!empty($this->filters['workDuration'])) {
                $row['Work Duration'] = $workDuration;
            }
            if (!empty($this->filters['grade'])) {
                $row['Grade'] = $item->fh_employees_details->fh_grade->g_name ?? '-';
            }
            if (!empty($this->filters['ot'])) {
                $row['OT'] = $otDuration;
            }

            $row['Late Coming'] = $lateDuration;
            $row['Early Going'] = $earlyExitDuration;

            // dd($this->punchingMode);
            if (!empty($this->filters['punchingMode'])) {
                if ($this->punchingMode == 'punchIn' || $this->punchingMode == 'all') {
                    $row['Punch In Photo'] = $punchInPhoto;
                    $row['Punch In Location'] = $item->atd_punchin_location ?? '-';
                    $row['Punch In Longitude'] = $item->atd_longitude_punchin ?? '-';
                    $row['Punch In Latitude'] = $item->atd_latitude_punchin ?? '-';
                }

                if ($this->punchingMode == 'punchOut' || $this->punchingMode == 'all') {
                    $row['Punch Out Photo'] = $punchOutPhoto;
                    $row['Punch Out Location'] = $item->atd_punchout_location ?? '-';
                    $row['Punch Out Longitude'] = $item->atd_longitude_punchout ?? '-';
                    $row['Punch Out Latitude'] = $item->atd_latitude_punchout ?? '-';
                }
            }

            $row['Status'] = $status;
            $row['Remark'] = $item->atd_remark ?? '-';

            return $row;
        });
    }

    public function headings(): array
    {
        $headings = [
            'S#',
            'Emp Code',
            'Emp Name',
            'Date',
        ];

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
        if (!empty($this->filters['shift'])) {
            $headings[] = 'Shift';
        }
        if (!empty($this->filters['shiftTiming'])) {
            $headings[] = 'Shift Timing';
        }
        if (!empty($this->filters['checkInMethod'])) {
            $headings[] = 'Check In Method';
        }
        if (!empty($this->filters['checkIn'])) {
            $headings[] = 'Check in';
        }
        if (!empty($this->filters['checkOut'])) {
            $headings[] = 'Check out';
        }
        if (!empty($this->filters['workMode'])) {
            $headings[] = 'Work Mode';
        }
        if (!empty($this->filters['workDuration'])) {
            $headings[] = 'Work Duration';
        }
        if (!empty($this->filters['grade'])) {
            $headings[] = 'Grade';
        }
        if (!empty($this->filters['ot'])) {
            $headings[] = 'OT';
        }
        $headings[] = 'Late Coming';
        $headings[] = 'Early Going';
        if (!empty($this->filters['punchingMode'])) {
            if ($this->punchingMode == 'punchIn' || $this->punchingMode == 'all') {
                $headings[] = 'Punch In Photo';
                $headings[] = 'Punch In Location';
                $headings[] = 'Punch In Longitude';
                $headings[] = 'Punch In Latitude';
            }
            if ($this->punchingMode == 'punchOut' || $this->punchingMode == 'all') {
                $headings[] = 'Punch Out Photo';
                $headings[] = 'Punch Out Location';
                $headings[] = 'Punch Out Longitude';
                $headings[] = 'Punch Out Latitude';
            }
        }

        $headings[] = 'Status';
        $headings[] = 'Remark';

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

            $sNoIndex = array_search('S#', $headings);
            $empCodeIndex = array_search('Emp Code', $headings);
            $empNameIndex = array_search('Emp Name', $headings);
            $dateIndex = array_search('Date', $headings);
            $branchIndex = array_search('Branch', $headings);
            $departmentIndex = array_search('Department', $headings);
            $designationIndex = array_search('Designation', $headings);
            $dealerIndex = array_search('Dealer', $headings);
            $shiftIndex = array_search('Shift', $headings);
            $shiftTimingIndex = array_search('Shift Timing', $headings);
            $checkInMethodIndex = array_search('Check In Method', $headings);
            $checkInIndex = array_search('Check in', $headings);
            $checkOutIndex = array_search('Check out', $headings);
            $workModeIndex = array_search('Work Mode', $headings);
            $workDurationIndex = array_search('Work Duration', $headings);
            $gradeIndex = array_search('Grade', $headings);
            $otIndex = array_search('OT', $headings);
            $lateComingIndex = array_search('Late Coming', $headings);
            $earlyGoingIndex = array_search('Early Going', $headings);
            $punchInPhotoIndex = array_search('Punch In Photo', $headings);
            $punchInLocationIndex = array_search('Punch In Location', $headings);
            $punchInLongitudeIndex = array_search('Punch In Longitude', $headings);
            $punchInLatitudeIndex = array_search('Punch In Latitude', $headings);
            $punchOutPhotoIndex = array_search('Punch Out Photo', $headings);
            $punchOutLocationIndex = array_search('Punch Out Location', $headings);
            $punchOutLongitudeIndex = array_search('Punch Out Longitude', $headings);
            $punchOutLatitudeIndex = array_search('Punch Out Latitude', $headings);
            $statusIndex = array_search('Status', $headings);
            $remarkIndex = array_search('Remark', $headings);

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
            } elseif ($index === $dateIndex) {
                $maxLength = max($maxLength, $collection->max(function ($row) use ($dateIndex) {
                    return strlen($row[$dateIndex] ?? '');
                }) ?: 10);
                $widths[$col] = min($maxLength + 2, 12);
            } elseif ($index === $branchIndex && $branchIndex !== false) {
                $maxLength = max($maxLength, $collection->max(function ($row) use ($branchIndex) {
                    return strlen($row[$branchIndex] ?? '');
                }) ?: 10);
                $widths[$col] = min($maxLength + 2, 20);
            } elseif ($index === $departmentIndex && $departmentIndex !== false) {
                $maxLength = max($maxLength, $collection->max(function ($row) use ($departmentIndex) {
                    return strlen($row[$departmentIndex] ?? '');
                }) ?: 10);
                $widths[$col] = min($maxLength + 2, 20);
            } elseif ($index === $designationIndex && $designationIndex !== false) {
                $maxLength = max($maxLength, $collection->max(function ($row) use ($designationIndex) {
                    return strlen($row[$designationIndex] ?? '');
                }) ?: 10);
                $widths[$col] = min($maxLength + 2, 15);
            } elseif ($index === $dealerIndex && $dealerIndex !== false) {
                $maxLength = max($maxLength, $collection->max(function ($row) use ($dealerIndex) {
                    return strlen($row[$dealerIndex] ?? '');
                }) ?: 10);
                $widths[$col] = min($maxLength + 2, 20);
            } elseif ($index === $shiftIndex && $shiftIndex !== false) {
                $maxLength = max($maxLength, $collection->max(function ($row) use ($shiftIndex) {
                    return strlen($row[$shiftIndex] ?? '');
                }) ?: 10);
                $widths[$col] = min($maxLength + 2, 18);
            } elseif ($index === $shiftTimingIndex && $shiftTimingIndex !== false) {
                $widths[$col] = 12;
            } elseif ($index === $checkInMethodIndex && $checkInMethodIndex !== false) {
                $maxLength = max($maxLength, $collection->max(function ($row) use ($checkInMethodIndex) {
                    return strlen($row[$checkInMethodIndex] ?? '');
                }) ?: 10);
                $widths[$col] = min($maxLength + 2, 15);
            } elseif ($index === $checkInIndex && $checkInIndex !== false) {
                $widths[$col] = 10;
            } elseif ($index === $checkOutIndex && $checkOutIndex !== false) {
                $widths[$col] = 10;
            } elseif ($index === $workModeIndex && $workModeIndex !== false) {
                $maxLength = max($maxLength, $collection->max(function ($row) use ($workModeIndex) {
                    return strlen($row[$workModeIndex] ?? '');
                }) ?: 10);
                $widths[$col] = min($maxLength + 2, 15);
            } elseif ($index === $workDurationIndex && $workDurationIndex !== false) {
                $widths[$col] = 12;
            } elseif ($index === $gradeIndex && $gradeIndex !== false) {
                $maxLength = max($maxLength, $collection->max(function ($row) use ($gradeIndex) {
                    return strlen($row[$gradeIndex] ?? '');
                }) ?: 10);
                $widths[$col] = min($maxLength + 2, 15);
            } elseif ($index === $otIndex && $otIndex !== false) {
                $widths[$col] = 10;
            } elseif ($index === $lateComingIndex) {
                $widths[$col] = 10;
            } elseif ($index === $earlyGoingIndex) {
                $widths[$col] = 10;
            } elseif ($index === $punchInPhotoIndex && $punchInPhotoIndex !== false) {
                $widths[$col] = 15;
            } elseif ($index === $punchInLocationIndex && $punchInLocationIndex !== false) {
                $maxLength = max($maxLength, $collection->max(function ($row) use ($punchInLocationIndex) {
                    return strlen($row[$punchInLocationIndex] ?? '');
                }) ?: 10);
                $widths[$col] = min($maxLength + 2, 25);
            } elseif ($index === $punchInLongitudeIndex && $punchInLongitudeIndex !== false) {
                $widths[$col] = 12;
            } elseif ($index === $punchInLatitudeIndex && $punchInLatitudeIndex !== false) {
                $widths[$col] = 12;
            } elseif ($index === $punchOutPhotoIndex && $punchOutPhotoIndex !== false) {
                $widths[$col] = 15;
            } elseif ($index === $punchOutLocationIndex && $punchOutLocationIndex !== false) {
                $maxLength = max($maxLength, $collection->max(function ($row) use ($punchOutLocationIndex) {
                    return strlen($row[$punchOutLocationIndex] ?? '');
                }) ?: 10);
                $widths[$col] = min($maxLength + 2, 25);
            } elseif ($index === $punchOutLongitudeIndex && $punchOutLongitudeIndex !== false) {
                $widths[$col] = 12;
            } elseif ($index === $punchOutLatitudeIndex && $punchOutLatitudeIndex !== false) {
                $widths[$col] = 12;
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

    public function styles(Worksheet $sheet)
    {
        $headingRow = 5;
        $dataRowCount = $this->collection()->count();
        $dataRowEnd = $dataRowCount > 0 ? ($headingRow + $dataRowCount) : $headingRow;
        $lastColumnIndex = count($this->headings());
        $lastColumnLetter = Coordinate::stringFromColumnIndex($lastColumnIndex);

        // Style header rows (1–4)
        foreach (range(1, 4) as $row) {
            $sheet->mergeCells("A{$row}:{$lastColumnLetter}{$row}");
            $sheet->getStyle("A{$row}")
                ->getFont()
                ->setSize($row <= 2 ? 12 : 11)
                ->setBold(true);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }

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

        // Apply styles to data rows
        if ($dataRowCount > 0) {
            $sheet->getStyle("A" . ($headingRow + 1) . ":{$lastColumnLetter}{$dataRowEnd}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("A" . ($headingRow + 1) . ":{$lastColumnLetter}{$dataRowEnd}")
                ->getFont()
                ->setSize(8);
            $sheet->getStyle("C" . ($headingRow + 1) . ":C{$dataRowEnd}")
                ->getAlignment()
                ->setWrapText(true);
        }

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->setShowGridlines(false);

                // Set header information
                $businessName = $this->data->isNotEmpty() ? ($this->data->first()->fh_business->b_name ?? 'Business Name') : 'Business Name';

               
                $reportDate = $this->date ?? 'N/A';

               
                $printedOn = Carbon::now()->format('d-M-Y h:i A T');

                $sheet->setCellValue('A1', $businessName);
                $sheet->setCellValue('A2', 'Selfie Attendance Report');
                $sheet->setCellValue('A3', 'Date: ' . $reportDate);
                $sheet->setCellValue('A4', 'Printed on: ' . $printedOn);

                // Merge header cells
                $lastColumnIndex = count($this->headings());
                $lastColumnLetter = Coordinate::stringFromColumnIndex($lastColumnIndex);
                foreach (range(1, 4) as $row) {
                    $sheet->mergeCells("A{$row}:{$lastColumnLetter}{$row}");
                }

                // Define heading and data rows
                $headingRow = 5;
                $rowCount = $this->collection()->count();
                $lastRow = $headingRow + $rowCount;

                // Apply borders and alternating colors to data range
                if ($rowCount > 0) {
                    $dataRange = "A{$headingRow}:{$lastColumnLetter}{$lastRow}";
                    $sheet->getStyle($dataRange)
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->getColor()
                        ->setRGB('D3D3D3');

                    for ($row = $headingRow + 1; $row <= $lastRow; $row++) {
                        $color = (($row - $headingRow) % 2 == 1) ? 'F5F5F5' : 'FFFFFF';
                        $sheet->getStyle("A{$row}:{$lastColumnLetter}{$row}")
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB($color);
                    }
                }

                // Set row heights
                $sheet->getRowDimension($headingRow)->setRowHeight(20);
                for ($row = $headingRow + 1; $row <= $lastRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(60); // Match original for images
                }

                // Handle Punch In/Out Photo Columns
                $headings = $this->headings();
                $punchInPhotoColumn = array_search('Punch In Photo', $headings);
                $punchOutPhotoColumn = array_search('Punch Out Photo', $headings);

                $punchInPhotoColumnLetter = $punchInPhotoColumn !== false
                    ? Coordinate::stringFromColumnIndex($punchInPhotoColumn + 1)
                    : false;
                $punchOutPhotoColumnLetter = $punchOutPhotoColumn !== false
                    ? Coordinate::stringFromColumnIndex($punchOutPhotoColumn + 1)
                    : false;

                foreach ($this->collection() as $index => $row) {
                    $rowNumber = 6 + $index;

                    // Punch In Photo
                    if (
                        $punchInPhotoColumnLetter &&
                        isset($row['Punch In Photo']) &&
                        $row['Punch In Photo'] !== '-' &&
                        filter_var($row['Punch In Photo'], FILTER_VALIDATE_URL)
                    ) {
                        try {
                            $tempFile = tempnam(sys_get_temp_dir(), 'punchin_');
                            file_put_contents($tempFile, file_get_contents($row['Punch In Photo']));

                            $drawing = new Drawing();
                            $drawing->setPath($tempFile);
                            $drawing->setHeight(50);
                            $drawing->setWidth(50);
                            $drawing->setCoordinates("{$punchInPhotoColumnLetter}{$rowNumber}");
                            $drawing->setOffsetX(2);
                            $drawing->setOffsetY(2);
                            $drawing->setWorksheet($sheet);

                            $sheet->setCellValue("{$punchInPhotoColumnLetter}{$rowNumber}", '');
                        } catch (\Exception $e) {
                            // Ignore image load errors
                        }
                    }

                    // Punch Out Photo
                    if (
                        $punchOutPhotoColumnLetter &&
                        isset($row['Punch Out Photo']) &&
                        $row['Punch Out Photo'] !== '-' &&
                        filter_var($row['Punch Out Photo'], FILTER_VALIDATE_URL)
                    ) {
                        try {
                            $tempFile = tempnam(sys_get_temp_dir(), 'punchout_');
                            file_put_contents($tempFile, file_get_contents($row['Punch Out Photo']));

                            $drawing = new Drawing();
                            $drawing->setPath($tempFile);
                            $drawing->setHeight(50);
                            $drawing->setWidth(50);
                            $drawing->setCoordinates("{$punchOutPhotoColumnLetter}{$rowNumber}");
                            $drawing->setOffsetX(2);
                            $drawing->setOffsetY(2);
                            $drawing->setWorksheet($sheet);

                            $sheet->setCellValue("{$punchOutPhotoColumnLetter}{$rowNumber}", '');
                        } catch (\Exception $e) {
                            // Ignore image load errors
                        }
                    }
                }

                // Freeze panes
                $sheet->freezePane('D6');

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
            },
        ];
    }

    public function startCell(): string
    {
        return 'A5';
    }
}