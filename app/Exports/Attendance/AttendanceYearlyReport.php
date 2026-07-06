<?php
namespace App\Exports\Attendance;

use App\Helpers\CentralLogics;
use App\Models\Business;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\MasterTable;
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

class AttendanceYearlyReport implements FromCollection, WithHeadings, WithColumnWidths, WithEvents, WithCustomStartCell
{
    protected $records;
    protected $slug;
    protected $year;
    protected $statuses;
    protected $months;
    protected $fyStartDate;
    protected $fyEndDate;

    public function __construct($records, $slug, $year, $fyStartDate, $fyEndDate)
    {
        $this->records = $records;
        $this->slug = $slug;
        $this->year = $year;
        $this->fyStartDate = $fyStartDate;
        $this->fyEndDate = $fyEndDate;

        // Fetch all possible statuses from MasterTable for ATTENDANCE_STATUS
        $allStatuses = MasterTable::where('m_group', 'ATTENDANCE_STATUS')
            ->pluck('m_name', 'm_type')
            ->toArray();

        // Get employee's allotted leave types from policy_leave
        $allottedLeaveTypes = [];
        $allottedLeaveStatuses = [];
        if ($this->records->isNotEmpty()) {
            $employee = $this->records->first()->fh_employees_details;
            if ($employee && $employee->fh_policy_leave) {
                $allottedLeaveTypeIds = $employee->fh_policy_leave->fh_leave_type->pluck('lvt_cat_type_id')->toArray();
                $allottedLeaveStatuses = MasterTable::where('m_group', 'LEAVE_CATEGORY')
                    ->whereIn('m_id', $allottedLeaveTypeIds)
                    ->pluck('m_name', 'm_type')
                    ->toArray();
                $allottedLeaveTypes = array_keys($allottedLeaveStatuses);
            }
        }

        // Define desired order for non-leave statuses
        $desiredOrder = [
            'P' => 'Present',
            'ABS' => 'Absent',
            'WO' => 'Weekly Off',
            'WOP' => 'Weekly Off Present',
            'MSP' => 'Missed Punch',
            'HD' => 'Half Day',
            'HO' => 'Holiday',
            'OD' => 'Out Door Duty',
        ];

        // Build statuses array starting with non-leave statuses
        $this->statuses = [];
        foreach ($desiredOrder as $key => $value) {
            if (isset($allStatuses[$key])) {
                $this->statuses[$key] = $allStatuses[$key];
            }
        }

        // Add allotted leave statuses
        foreach ($allottedLeaveStatuses as $key => $value) {
            $this->statuses[$key] = $value;
        }

        // Generate month sequence based on financial year
        $startDate = Carbon::parse($this->fyStartDate);
        $endDate = Carbon::parse($this->fyEndDate);
        $currentDate = Carbon::now();
        $endLimit = $endDate->greaterThan($currentDate) ? $currentDate : $endDate;
        $monthPeriod = CarbonPeriod::create($startDate->copy()->startOfMonth(), '1 month', $endLimit);
        $months = [];
        foreach ($monthPeriod as $monthDate) {
            $monthName = $monthDate->format('F');
            $months[] = $monthName;
        }

        // Ensure months are unique and sorted chronologically
        $this->months = array_values(array_unique($months));
    }

    public function collection()
    {
        // Group attendance records by month
        $groupedAttendance = $this->records->groupBy(function ($rec) {
            return Carbon::parse($rec->atd_date)->format('F');
        });

        // Fetch leave records from leave_requests
        $employee = $this->records->isNotEmpty() ? $this->records->first()->fh_employees_details : null;
        $leaveRecords = collect([]);
        if ($employee && method_exists($employee, 'leave_requests')) {
            $approvedStatusId = MasterTable::where('m_group', 'LEAVE_STATUS')
                ->where('m_name', 'APPROVED')
                ->value('m_id') ?? 157;
            $leaveRecords = $employee->leave_requests()
                ->where('lvr_status', $approvedStatusId)
                ->whereBetween('created_at', [$this->fyStartDate, $this->fyEndDate])
                ->get()
                ->groupBy(function ($leave) {
                    return Carbon::parse($leave->lvr_start_date)->format('F');
                });
        }

        // Get weekly off dates using the helper function
        $weekOffDates = [];
        if ($employee) {
            $weekOffDates = CentralLogics::getWeekOffDatesReport($employee, null, null, $this->fyStartDate, $this->fyEndDate);
            // Group weekly off dates by month
            $weekOffByMonth = [];
            foreach ($weekOffDates as $date) {
                $month = Carbon::parse($date)->format('F');
                $weekOffByMonth[$month] = ($weekOffByMonth[$month] ?? 0) + 1;
            }
        }

        $data = [];
        $leaveKeys = array_keys(array_filter($this->statuses, function ($key) {
            return in_array($key, array_keys($this->statuses)) &&
                !in_array($key, ['P', 'ABS','WO', 'WOP', 'MSP', 'HD', 'HO', 'OD']);
        }, ARRAY_FILTER_USE_KEY));

        foreach ($this->months as $month) {
            // Initialize row with default "0" (string) for all statuses
            $row = ['Month' => $month];
            foreach ($this->statuses as $key => $label) {
                $row[$key] = "0"; // Explicitly set string "0" for all statuses
            }
            $row['Total Leave'] = "0"; // Explicitly set string "0" for Total Leave

            // Set weekly off count for the month
            $row['WO'] = isset($weekOffByMonth[$month]) ? (string) $weekOffByMonth[$month] : "0";

            // Get attendance records for the month
            $attendanceRows = $groupedAttendance->get($month, collect([]));
            $attendanceCounts = [];
            foreach ($attendanceRows as $rec) {
                $statusType = $rec->fh_attendance_status->m_type ?? null;
                $attendanceDate = Carbon::parse($rec->atd_date)->format('Y-m-d');
                // Check if the date is a weekly off
                if ($statusType === 'P' && in_array($attendanceDate, $weekOffDates)) {
                    $statusType = 'WOP'; // Override to Weekly Off Present
                }
                if ($statusType && !in_array($statusType, $leaveKeys) && isset($this->statuses[$statusType])) {
                    $attendanceCounts[$statusType] = ($attendanceCounts[$statusType] ?? 0) + 1;
                }
            }

            // Get leave records for the month
            $leaveRows = $leaveRecords->get($month, collect([]));
            $leaveCounts = [];
            foreach ($leaveRows as $leave) {
                $leaveTypeId = $leave->lvr_cat_type_id;
                $leaveStatus = MasterTable::where('m_group', 'LEAVE_CATEGORY')
                    ->where('m_id', $leaveTypeId)
                    ->first();
                if ($leaveStatus && isset($this->statuses[$leaveStatus->m_type])) {
                    $leaveDays = (float) $leave->lvr_total_leave_days;
                    $leaveCounts[$leaveStatus->m_type] = ($leaveCounts[$leaveStatus->m_type] ?? 0) + $leaveDays;
                }
            }

            // Update row with attendance counts
            foreach ($this->statuses as $key => $label) {
                if (!in_array($key, $leaveKeys) && $key !== 'WO') {
                    $row[$key] = isset($attendanceCounts[$key]) ? (string) $attendanceCounts[$key] : "0";
                }
            }

            // Update row with leave counts
            $totalLeave = 0;
            foreach ($this->statuses as $key => $label) {
                if (in_array($key, $leaveKeys)) {
                    $row[$key] = isset($leaveCounts[$key]) ? (string) $leaveCounts[$key] : "0";
                    $totalLeave += isset($leaveCounts[$key]) ? (float) $leaveCounts[$key] : 0;
                }
            }
            $row['Total Leave'] = (string) $totalLeave; // Convert total to string

            $data[] = $row;
        }

        return collect($data);
    }

    public function headings(): array
    {
        return array_merge(['Month'], array_keys($this->statuses), ['Total Leave']);
    }

    public function columnWidths(): array
    {
        $widths = [];
        $i = 1;
        foreach (array_merge(['Month'], $this->statuses, ['Total Leave']) as $key => $val) {
            $col = Coordinate::stringFromColumnIndex($i++);
            $widths[$col] = ($key === 'Month') ? 15 : 9;
        }
        return $widths;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->setShowGridlines(false); // Disable default Excel gridlines

                // Extract company and employee details
                
                $employee = 'N/A';
                $empCode = 'N/A';
                $dept = '-';
                if ($this->records->isNotEmpty()) {
                    $first = $this->records->first();
                    $company = $first->fh_business->b_id ? 
                    Business::where('b_id', $first->fh_business->b_id)->value('b_name') : 'N/A';
                    $employee = $first->fh_employees_details->emp_full_name ?? $employee;
                    $empCode = $first->fh_employees_details->emp_code ?? $empCode;
                    $dept = $first->fh_employees_details->fh_department->d_name ?? $dept;
                }

                // Set header rows (first 7 lines)
                $printed = Carbon::now()->format('d-M-Y h:i A T');
                $start = Carbon::parse($this->fyStartDate)->format('d-M-Y');
                $end = Carbon::parse($this->fyEndDate)->format('d-M-Y');
                $sheet->setCellValue('A1', 'Yearly Attendance Summary Report');
                $sheet->setCellValue('A2', $company);
                $sheet->setCellValue('A3', "Financial Year: {$this->year} ({$start} to {$end})");
                $sheet->setCellValue('A4', "Employee Name: {$employee}");
                $sheet->setCellValue('A5', "Employee Code: {$empCode}");
                $sheet->setCellValue('A6', "Department: {$dept}");
                $sheet->setCellValue('A7', "Printed on: {$printed}");

                // Style header rows
                foreach (range(1, 7) as $row) {
                    $sheet->mergeCells("A{$row}:I{$row}");
                    $sheet->getStyle("A{$row}")
                        ->getFont()
                        ->setSize(11)
                        ->setBold(true);
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // Define table ranges (data starts at A7 as per startCell)
                $headingRow = 8; // Header row is now at A7
                $rowCount = $this->collection()->count();
                $lastRow = $headingRow + $rowCount;
                $lastCol = Coordinate::stringFromColumnIndex(count($this->headings()));

                // Style header row
                $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")
                    ->getFont()
                    ->setSize(10)
                    ->setBold(true);
                $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('263871');
                $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")
                    ->getFont()
                    ->getColor()
                    ->setRGB('FFFFFF');

                // Style data cells
                if ($rowCount > 0) {
                    $sheet->getStyle("A".($headingRow+1).":{$lastCol}{$lastRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle("A".($headingRow+1).":{$lastCol}{$lastRow}")
                        ->getFont()
                        ->setSize(8);
                    $sheet->getStyle("A".($headingRow+1).":{$lastCol}{$lastRow}")
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->getColor()
                        ->setRGB('D3D3D3');
                    // Enable wrap text for Month column
                    $sheet->getStyle("A".($headingRow+1).":A{$lastRow}")
                        ->getAlignment()
                        ->setWrapText(true);
                }

                // Set row heights
                $sheet->getRowDimension($headingRow)->setRowHeight(20);
                for ($row = $headingRow + 1; $row <= $lastRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(18);
                }

                // Freeze panes: first column and header rows
                // $sheet->freezePane('B'.($headingRow+1));

                // Abbreviations section
                $abbrRow = $lastRow + 2;
                $sheet->setCellValue("A{$abbrRow}", 'Abbreviations');
                $sheet->getStyle("A{$abbrRow}")
                    ->getFont()
                    ->setSize(10)
                    ->setBold(true);
                $sheet->getStyle("A{$abbrRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // Define abbreviations
                $abbreviations = [
                    'P' => 'Present Days',
                    'ABS' => 'Absent Days',
                    'WO' => 'Weekly Off Days',
                    'WOP' => 'Weekly Off Present',
                    'MSP' => 'Missed Punch',
                    'HD' => 'Half Day',
                    'HO' => 'Holidays',
                    'OD' => 'Out Door Duty',
                    'CL' => 'Casual Leave',
                    'SL' => 'Sick Leave',
                    'ML' => 'Maternity Leave',
                    'PL' => 'Privilege Leave',
                    'Total Leave' => 'Sum of all leave types taken'
                ];

                // Add abbreviations for relevant statuses
                $abbrStartRow = $abbrRow + 1;
                foreach (array_merge($this->statuses, ['Total Leave' => 'Total Leave']) as $key => $label) {
                    if (isset($abbreviations[$key])) {
                        $sheet->setCellValue("A{$abbrStartRow}", "{$key}: {$abbreviations[$key]}");
                        $sheet->getStyle("A{$abbrStartRow}")
                            ->getFont()
                            ->setSize(9);
                        $sheet->getStyle("A{$abbrStartRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                        $abbrStartRow++;
                    }
                }

                // Apply borders to abbreviations section
                // $sheet->getStyle("A{$abbrRow}:A".($abbrStartRow-1))
                //     ->getBorders()
                //     ->getAllBorders()
                //     // ->setBorderStyle(Border::BORDER_THIN)
                //     ->getColor()
                //     ->setRGB('D3D3D3');

                // Summary analysis section
                $summaryRow = $abbrStartRow + 1;
                $sheet->setCellValue("A{$summaryRow}", 'Summary Analysis');
                $sheet->getStyle("A{$summaryRow}")
                    ->getFont()
                    ->setSize(10)
                    ->setBold(true);
                $sheet->getStyle("A{$summaryRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $totalPresent = '0';
                $totalAbsent = '0';
                $totalLeave = '0';
                $totalWeeklyOff = '0';
                $highAbsMonths = [];
                $leaveKeys = array_keys(array_filter($this->statuses, function ($key) {
                    return in_array($key, array_keys($this->statuses)) &&
                        !in_array($key, ['P', 'ABS', 'WO', 'WOP', 'MSP', 'HD', 'HO', 'OD']);
                }, ARRAY_FILTER_USE_KEY));
                $leaveTotals = array_fill_keys($leaveKeys, '0');

                foreach ($this->collection() as $dataRow) {
                    $totalPresent += $dataRow['P'] ?? '0';
                    $totalAbsent += $dataRow['ABS'] ?? '0';
                    $totalWeeklyOff += $dataRow['WO'] ?? '0';
                    foreach ($leaveKeys as $type) {
                        $total = $dataRow[$type] ?? '0';
                        $leaveTotals[$type] += (float) $total;
                        $totalLeave += (float) $total;
                    }
                    if (($dataRow['ABS'] ?? '0') > 10) {
                        $highAbsMonths[] = "{$dataRow['Month']} ({$dataRow['ABS']} days)";
                    }
                }

                $summaryRow++;
                $sheet->setCellValue("A{$summaryRow}", "Employee {$employee} (Code: {$empCode}) from {$dept} shows attendance summary.");
                $sheet->getStyle("A{$summaryRow}")->getFont()->setSize(9);
                $sheet->getStyle("A{$summaryRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $summaryRow++;

                if ($highAbsMonths) {
                    $sheet->setCellValue("A{$summaryRow}", "High absence in: " . implode(', ', $highAbsMonths));
                    $sheet->getStyle("A{$summaryRow}")->getFont()->setSize(9);
                    $sheet->getStyle("A{$summaryRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    $summaryRow++;
                }

                foreach ($leaveTotals as $type => $count) {
                    if ($count > '0') {
                        $desc = $abbreviations[$type] ?? $type;
                        $sheet->setCellValue("A{$summaryRow}", "Total {$desc}: {$count} days");
                        $sheet->getStyle("A{$summaryRow}")->getFont()->setSize(9);
                        $sheet->getStyle("A{$summaryRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                        $summaryRow++;
                    }
                }

                $sheet->setCellValue("A{$summaryRow}", "Total leave days: {$totalLeave} over " . count($this->months) . " months");
                $sheet->getStyle("A{$summaryRow}")->getFont()->setSize(9);
                $sheet->getStyle("A{$summaryRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $summaryRow++;

                $sheet->setCellValue("A{$summaryRow}", "Total present days: {$totalPresent}");
                $sheet->getStyle("A{$summaryRow}")->getFont()->setSize(9);
                $sheet->getStyle("A{$summaryRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $summaryRow++;

                $sheet->setCellValue("A{$summaryRow}", "Total weekly off days: {$totalWeeklyOff}");
                $sheet->getStyle("A{$summaryRow}")->getFont()->setSize(9);
                $sheet->getStyle("A{$summaryRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // Apply borders to summary analysis section
                // $sheet->getStyle("A".($abbrStartRow+1).":A{$summaryRow}")
                //     ->getBorders()
                //     ->getAllBorders()
                //     ->setBorderStyle(Border::BORDER_THIN)
                //     ->getColor()
                //     ->setRGB('D3D3D3');
            }
        ];
    }

    public function startCell(): string
    {
        return 'A8';
    }
}
?>