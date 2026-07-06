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

class MonitoringReport implements FromCollection, WithHeadings, WithColumnWidths, WithEvents
{
    protected $data;
    protected $filters;
    protected $fileName;
    protected $date;

    public function __construct($records, $filters, $fileName, $date)
    {
        $this->data = $records;

        // dd($this->data);
        $this->filters = $filters;
        $this->fileName = ucwords(str_replace(['_', '-'], ' ', $fileName)) . ' Report';
        $this->date = $date;
    }

    protected function calculateDailySalary($employee, $date)
    {
        $salary = $employee->fh_employee_salary ?? null;
        if (!$salary) {
            return 0;
        }
        $date = Carbon::parse($date);
        $daysInMonth = $date->daysInMonth;
        $monthlySalary = $salary->es_monthly_gross ??
            ($salary->es_basic_salary + $salary->es_hra + $salary->es_other_allowances);
        return round(($monthlySalary / $daysInMonth), 2);
    }

    public function collection()
    {
        $serialNumber = 1;
        $totalSalary = 0;
        $collection = collect($this->data)->map(function ($item) use (&$serialNumber, &$totalSalary) {
            $dailySalary = $this->calculateDailySalary(
                $item->fh_employees_details,
                $item->atd_date
            );
            $totalSalary += $dailySalary;
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
            $row = [
                'S#' => $serialNumber++,
                'Emp Code' => $item->fh_employees_details->emp_code ?? '',
                'Emp Name' => $item->fh_employees_details->emp_full_name ?? '',
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
            $row['Shift Timing'] = $shiftStartTime . ' - ' . $shiftEndTime;
          
            if (!empty($this->filters['checkingMethod'])) {
                $row['Check In Method'] = $item->atd_checkin_method_id ? MasterTable::where('m_id', $item->atd_checkin_method_id)->first()->m_name : '-';
            }
            $row['Date'] = $attendanceDate->format('d-M-Y');
            $row['Check in'] = $checkIn ? $checkIn->format('H:i') : '-';
            $row['Check out'] = $checkOut ? $checkOut->format('H:i') : '-';
            if (!empty($this->filters['workMode'])) {
                $row['Work Mode'] = $item->fh_attendance_work_mode->m_name ?? '-';
            }
            $row['Work Duration'] = $workDuration;
            if (!empty($this->filters['grade'])) {
                $row['Grade'] = $item->fh_employees_details->fh_grade->g_name ?? '-';
            }
            $row['OT Duration'] = $otDuration;
            if ($this->fileName !== 'Late Coming Report') {
                $row['Early Going'] = $earlyExitDuration;
            }
            if ($this->fileName !== 'Early Going Report') {
                $row['Late Coming'] = $lateDuration;
            }
            if ($this->fileName == 'Weekly Off Present Detailed Report') {
                $row['Payment'] = $dailySalary;
            }
            $row['Status'] = $status;
            $row['Remark'] = !empty($item->atd_remark) ? $item->atd_remark : '-';
            if ($this->fileName == 'Missed Punch') {
                $exception = $item->attendance_exceptions[0] ?? null;
                $row['AE Approved By'] = $exception->ae_approved_by ?? '-';
                $row['AE In Time'] = $exception->ae_in_time ?? '-';
                $row['AE Out Time'] = $exception->ae_out_time ?? '-';
                $row['AE Total Working'] = $exception->ae_total_working ?? '-';
                $row['AE Reason ID'] = $exception->ae_reason_id ?? '-';
                $row['AE Custom Reason'] = $exception->ae_custom_reason ?? '-';
                $row['AE AM ID'] = $exception->ae_am_id ?? '-';
                $row['AE Status'] = $exception->ae_status ?? '-';
                $row['AE Module ID'] = $exception->ae_module_id ?? '-';
                $row['AE Next Approver'] = $exception->ae_next_approver ?? '-';
                $row['AE Stage Completed'] = $exception->ae_stage_completed ?? '-';
            }
            return $row;
        });
        if ($this->fileName == 'Weekly Off Present Detailed Report') {
            if ($collection->isNotEmpty()) {
                $totalRow = array_fill(0, count($this->headings()), '');
                $totalRow[0] = 'Total';
                $paymentColIndex = array_search('Payment (₹)', $this->headings());
                $totalRow[$paymentColIndex] = $totalSalary;
                $collection->push($totalRow);
            }
        }
        return $collection;
    }

    public function headings(): array
    {
        $headings = [
            'S#',
            'Emp Code',
            'Emp Name',
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
        $headings[] = 'Shift Timing';
        if (!empty($this->filters['checkingMethod'])) {
            $headings[] = 'Check In Method';
        }
        $headings = array_merge($headings, [
            'Date',
            'Check in',
            'Check out',
        ]);
        if (!empty($this->filters['workMode'])) {
            $headings[] = 'Work Mode';
        }
        $headings[] = 'Work Duration';
        if (!empty($this->filters['grade'])) {
            $headings[] = 'Grade';
        }
        $headings = array_merge($headings, [
            'OT Duration',
        ]);
        if ($this->fileName !== 'Late Coming Report') {
            $headings[] = 'Early Going';
        }
        if ($this->fileName !== 'Early Going Report') {
            $headings[] = 'Late Coming';
        }
        if ($this->fileName == 'Weekly Off Present Detailed Report') {
            $headings[] = 'Payment (₹)';
        }
        $headings = array_merge($headings, [
            'Status',
            'Remark',
        ]);
        if ($this->fileName === 'Missed Punch') {
            $headings = array_merge($headings, [
                'AE Approved By',
                'AE In Time',
                'AE Out Time',
                'AE Total Working',
                'AE Reason ID',
                'AE Custom Reason',
                'AE AM ID',
                'AE Status',
                'AE Module ID',
                'AE Next Approver',
                'AE Stage Completed',
            ]);
        }
        return $headings;
    }

    public function columnWidths(): array
    {
        $columns = [];
        $headings = $this->headings();
        $collection = $this->collection();
        $excelColumnLetters = [];
        for ($i = 0; $i < count($headings); $i++) {
            $excelColumnLetters[] = Coordinate::stringFromColumnIndex($i + 1);
        }
        foreach ($excelColumnLetters as $key => $column) {
            $maxLength = strlen($headings[$key]);
            $maxLength = max($maxLength, $collection->max(function ($row) use ($key) {
                return strlen($row[$key] ?? '');
            }) ?: 10);
            $columns[$column] = match ($headings[$key]) {
                'S#' => min($maxLength + 2, 8),
                'Emp Code' => min($maxLength + 2, 13),
                'Emp Name' => min($maxLength + 2, 13),
                'Branch', 'Department', 'Designation', 'Dealer' => min($maxLength + 2, 13),
                'Shift' => min($maxLength + 2, 18),
                'Shift Timing' => min($maxLength + 2, 13),
                'Check In Method' => min($maxLength + 2, 13),
                'Date' => min($maxLength + 2, 12),
                'Check in', 'Check out' => min($maxLength + 2, 13),
                'Work Mode' => min($maxLength + 2, 13),
                'Work Duration', 'OT Duration', 'Late Coming', 'Early Going' => min($maxLength + 2, 13),
                'Grade' => min($maxLength + 2, 13),
                'Payment (₹)' => min($maxLength + 2, 13),
                'Status' => min($maxLength + 2, 12),
                'Remark' => min($maxLength + 2, 20),
                'AE Approved By', 'AE In Time', 'AE Out Time', 'AE Total Working',
                'AE Reason ID', 'AE AM ID', 'AE Status', 'AE Module ID',
                'AE Next Approver', 'AE Stage Completed' => min($maxLength + 2, 13),
                'AE Custom Reason' => min($maxLength + 2, 13),
                default => min($maxLength + 2, 12),
            };
        }
        return $columns;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->setShowGridlines(false);

                // Insert header rows
                $sheet->insertNewRowBefore(1, 5);
                $businessName = !empty($this->data) && !empty($this->data[0]->fh_business->b_id)
                    ? Business::where('b_id', $this->data[0]->fh_business->b_id)->value('b_name') ?? 'Business Name'
                    : 'Business Name';
                $printedOn = Carbon::now()->format('d-M-Y h:i A T');
                

                // Set header information
                $sheet->setCellValue('A1', $businessName);
                $sheet->setCellValue('A2', $this->fileName);
                $sheet->setCellValue('A3', $this->date);
                $sheet->setCellValue('A4', "Printed on: {$printedOn}");

                // Merge header cells (A1:I1, A2:I2, etc.)
                $lastColumnIndex = count($this->headings());
                $lastColumnLetter = Coordinate::stringFromColumnIndex($lastColumnIndex);
                foreach (range(1, 4) as $row) {
                    $sheet->mergeCells("A{$row}:{$lastColumnLetter}{$row}");
                    $sheet->getStyle("A{$row}")
                        ->getFont()
                        ->setSize(11)
                        ->setBold(true);
                    $sheet->getStyle("A{$row}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // Style the heading row
                $headingRow = 6;
                $sheet->getStyle("A{$headingRow}:{$lastColumnLetter}{$headingRow}")
                    ->getFont()
                    ->setSize(10)
                    ->setBold(true)
                    ->getColor()->setRGB('FFFFFF');
                $sheet->getStyle("A{$headingRow}:{$lastColumnLetter}{$headingRow}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('263871');
                $sheet->getStyle("A{$headingRow}:{$lastColumnLetter}{$headingRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);

                // Set row heights
                $sheet->getRowDimension(6)->setRowHeight(20);
                $dataRowCount = $this->collection()->count();
                $dataRowEnd = $dataRowCount > 0 ? (6 + $dataRowCount) : 6;
                for ($row = 7; $row <= $dataRowEnd; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(25);
                }

                if ($dataRowCount > 0) {
                    // Apply borders to data cells
                    $sheet->getStyle("A6:{$lastColumnLetter}{$dataRowEnd}")
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->getColor()
                        ->setRGB('D3D3D3');

                    // Center align all data cells
                    $sheet->getStyle("A7:{$lastColumnLetter}{$dataRowEnd}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);

                    // Apply wrap text to text-heavy columns
                    $textHeavyColumns = ['C']; // Emp Name
                    foreach (['Branch', 'Department', 'Designation', 'Dealer', 'Shift', 'Work Mode', 'Grade', 'Remark', 'AE Custom Reason'] as $header) {
                        $index = array_search($header, $this->headings());
                        if ($index !== false) {
                            $textHeavyColumns[] = Coordinate::stringFromColumnIndex($index + 1);
                        }
                    }
                    foreach ($textHeavyColumns as $col) {
                        $sheet->getStyle("{$col}7:{$col}{$dataRowEnd}")
                            ->getAlignment()
                            ->setWrapText(true);
                    }

                    // Set font size for data cells
                    $sheet->getStyle("A7:{$lastColumnLetter}{$dataRowEnd}")
                        ->getFont()
                        ->setSize(8);

                    // Apply alternating background colors for rows
                    for ($row = 7; $row <= $dataRowEnd; $row++) {
                        $color = (($row - 7) % 2 == 0) ? 'F5F5F5' : 'FFFFFF';
                        $sheet->getStyle("A{$row}:{$lastColumnLetter}{$row}")
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB($color);
                    }

                    // Style the total row (if exists for Weekly Off Present Detailed Report)
                    if ($this->fileName == 'Weekly Off Present Detailed Report') {
                        $totalRow = $dataRowEnd;
                        $sheet->getStyle("A{$totalRow}:{$lastColumnLetter}{$totalRow}")
                            ->getFont()
                            ->setBold(true);
                    }

                    // Format payment column (if exists)
                    if ($this->fileName == 'Weekly Off Present Detailed Report') {
                        foreach ($this->headings() as $index => $heading) {
                            if ($heading === 'Payment (₹)') {
                                $paymentCol = Coordinate::stringFromColumnIndex($index + 1);
                                $sheet->getStyle("{$paymentCol}7:{$paymentCol}{$dataRowEnd}")
                                    ->getNumberFormat()
                                    ->setFormatCode('#,##0.00');
                                break;
                            }
                        }
                    }
                }

                // Freeze panes: columns A-C (S#, Emp Code, Emp Name)
                $sheet->freezePane('D7');

                // Add legend
                // $legendRow = $dataRowEnd + 2;
                // // $sheet->setCellValue("A{$legendRow}", 'Abbreviations');
                // $sheet->mergeCells("A{$legendRow}:{$lastColumnLetter}{$legendRow}");
                // $sheet->getStyle("A{$legendRow}")
                //     ->getFont()
                //     ->setSize(10)
                //     ->setBold(true);
                // $sheet->getStyle("A{$legendRow}")
                //     ->getAlignment()
                //     ->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // $abbreviations = [
                //     'P' => 'Present Days',
                //     'ABS' => 'Absent Days',
                //     'WO' => 'Week-off Days',
                //     'WOP' => 'Weekly Off Present',
                //     'MSP' => 'Missed Punch',
                //     'HD' => 'Half Day',
                //     'HO' => 'Holidays',
                //     'OD' => 'Out Door Duty',
                // ];
                // $abbrStartRow = $legendRow + 1;
                // foreach ($abbreviations as $abbr => $desc) {
                //     $row = $abbrStartRow++;
                //     $sheet->setCellValue("A{$row}", "{$abbr}: {$desc}");
                //     $sheet->mergeCells("A{$row}:{$lastColumnLetter}{$row}");
                //     $sheet->getStyle("A{$row}")
                //         ->getFont()
                //         ->setSize(9);
                //     $sheet->getStyle("A{$row}")
                //         ->getAlignment()
                //         ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                // }
            },
        ];
    }
}