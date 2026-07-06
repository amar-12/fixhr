<?php

namespace App\Exports\Attendance;

use App\Helpers\CentralLogics;
use Carbon\Carbon;
use App\Models\MasterTable;
use App\Models\Employee;
use App\Models\PolicyHolidayList;
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

class AttendanceMonthlyReport implements FromCollection, WithHeadings, WithColumnWidths, WithEvents, WithCustomStartCell
{
    protected $records;
    protected $slug;
    protected $year;
    protected $month;
    protected $statuses;
    protected $fyStartDate;
    protected $fyEndDate;
    protected $businessId;
    protected $filters;
    public function __construct($records, $filters, $slug, $year, $month, $fyStartDate, $fyEndDate, $businessId)
    {
        $this->records = $records;
        $this->filters = $filters;
        $this->slug = $slug;
        $this->year = $year;
        $this->month = (int) $month;
        $this->fyStartDate = $fyStartDate;
        $this->fyEndDate = $fyEndDate;
        $this->businessId = $businessId;
        // Fetch all possible statuses from MasterTable for ATTENDANCE_STATUS
        $allStatuses = MasterTable::where('m_group', 'ATTENDANCE_STATUS')
            ->pluck('m_name', 'm_type')
            ->toArray();
        // Define desired order for non-leave statuses
        $desiredOrder = [
            'P' => 'Present',
            'ABS' => 'Absent',
            'HP' => 'Half Pay',
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
        // Add leave statuses from leave categories
        $allottedLeaveStatuses = MasterTable::where('m_group', 'LEAVE_CATEGORY')
            ->pluck('m_name', 'm_type')
            ->toArray();
        foreach ($allottedLeaveStatuses as $key => $value) {
            $this->statuses[$key] = $value;
        }
    }
    public function collection()
    {
        if ($this->records->isEmpty()) {
            return collect([]);
        }
        $fyStartYear = Carbon::parse($this->fyStartDate)->year;
        $fyEndYear = Carbon::parse($this->fyEndDate)->year;
        $monthNumber = $this->month;
        $yearForMonth = ($monthNumber >= 4) ? $fyStartYear : $fyEndYear;
        $monthStart = Carbon::create($yearForMonth, $monthNumber, 1)->startOfMonth();
        $daysInMonth = $monthStart->daysInMonth;
        $monthEnd = $monthStart->copy()->endOfMonth();
        // Fetch holiday dates
        $holidayDates = PolicyHolidayList::where('phl_b_id', $this->businessId)
            ->where(function ($q) use ($monthStart, $monthEnd) {
                $q->where('phl_start_date', '<=', $monthEnd)
                    ->where('phl_end_date', '>=', $monthStart);
            })
            ->get()
            ->flatMap(function ($holiday) use ($monthStart, $monthEnd) {
                try {
                    $start = Carbon::parse($holiday->phl_start_date);
                    $end = $holiday->phl_end_date ? Carbon::parse($holiday->phl_end_date) : $start;
                    $start = $start->max($monthStart);
                    $end = $end->min($monthEnd);
                    return $start->lte($end) ? collect($start->daysUntil($end))->map(fn($date) => $date->format('Y-m-d'))->toArray() : [];
                } catch (\Exception $e) {
                    return [];
                }
            })
            ->unique()
            ->values()
            ->toArray();
        // Group records by emp_id from fh_employees_details
        $recordsByEmployee = $this->records->groupBy(function ($record) {
            return $record->fh_employees_details ? $record->fh_employees_details->emp_id : null;
        });
        $employees = Employee::whereIn('emp_id', $recordsByEmployee->keys()->filter())->get()->keyBy('emp_id');
        $approvedStatusId = MasterTable::where('m_group', 'LEAVE_STATUS')
            ->where('m_name', 'APPROVED')
            ->value('m_id') ?? 157;
        $rows = [];
        $rowNumber = 0;
        foreach ($recordsByEmployee as $empId => $employeeRecords) {
            $employee = $employees->get($empId);
            if (!$employee && $employeeRecords->first() && isset($employeeRecords->first()->fh_employees_details)) {
                $employee = $employeeRecords->first()->fh_employees_details;
            } elseif (!$employee) {
                continue;
            }
            $empCode = $employee->emp_code ?? 'N/A';
            $empName = $employee->emp_full_name ?? 'N/A';
            // Initialize filter-based column data
            $filterData = [];
            if ($this->filters['shift'] ?? false) {
                $filterData['Shift'] = $employee->fh_shift_type->pst_name ?? 'N/A';
            }
            if ($this->filters['department'] ?? false) {
                $filterData['Department'] = $employee->fh_department->d_name ?? 'N/A';
            }
            if ($this->filters['designation'] ?? false) {
                $filterData['Designation'] = $employee->fh_designation->dg_name ?? 'N/A';
            }
            if ($this->filters['workMode'] ?? false) {
                $workMode = MasterTable::where('m_id', $employeeRecords->first()->fh_attendance_work_mode->m_id)->first();
                $filterData['Work Mode'] = $workMode->m_name ?? 'N/A';
            }
            if ($this->filters['dealership'] ?? false) {
                $filterData['Dealership'] = $employee->fh_dealership->dlr_name ?? 'N/A';
            }
            if ($this->filters['branch'] ?? false) {
                $filterData['Branch'] = $employee->fh_branch->br_name ?? 'N/A';
            }
            if ($this->filters['jobStatus'] ?? false) {
                $jobStatus = MasterTable::where('m_id', $employee->emp_job_status)->first();
                $filterData['Job Status'] = $jobStatus->m_name ?? 'N/A';
            }
            if ($this->filters['grade'] ?? false) {
                $grade = \App\Models\Grade::where('g_id', $employee->emp_grade_id)->first();
                $filterData['Grade'] = $grade->g_name ?? 'N/A';
            }
            if ($this->filters['checkingMethod'] ?? false) {
                $checkingMethod = MasterTable::where('m_id', $employeeRecords->first()->atd_checkin_method_id)->first();
                $filterData['Checking Method'] = $checkingMethod->m_name ?? 'N/A';
            }
            // Filter attendance records for this month
            $attendanceRecords = $employeeRecords->filter(function ($rec) use ($monthStart, $monthEnd) {
                $date = Carbon::parse($rec->atd_date);
                return $date->between($monthStart, $monthEnd);
            })->keyBy(function ($rec) {
                return Carbon::parse($rec->atd_date)->format('Y-m-d');
            });
            // Fetch leave records
            $leaveRecords = $employee->leave_requests()
                ->where('lvr_status', $approvedStatusId)
                ->where(function ($q) use ($monthStart, $monthEnd) {
                    $q->where('lvr_start_date', '<=', $monthEnd)
                        ->where('lvr_end_date', '>=', $monthStart);
                })
                ->get();
            // Get weekly off dates
            $weekOffDates = CentralLogics::getWeekOffDatesReport($employee, null, null, $monthStart, $monthEnd);
            // Define leave keys
            $leaveKeys = array_keys(array_filter($this->statuses, function ($key) {
                return !in_array($key, ['P', 'ABS', 'HP', 'WO', 'WOP', 'MSP', 'HD', 'HO', 'OD']);
            }, ARRAY_FILTER_USE_KEY));
            // Build status per day
            $statusPerDay = [];
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $dateCarbon = $monthStart->copy()->day($day);
                $date = $dateCarbon->format('Y-m-d');
                $status = null;
                $isLeave = false;
                // Check for leave
                foreach ($leaveRecords as $leave) {
                    $lStart = Carbon::parse($leave->lvr_start_date);
                    $lEnd = Carbon::parse($leave->lvr_end_date ?? $leave->lvr_start_date);
                    if ($dateCarbon->between($lStart, $lEnd, true)) {
                        $leaveTypeId = $leave->lvr_cat_type_id;
                        $leaveMaster = MasterTable::where('m_id', $leaveTypeId)->first();
                        if ($leaveMaster) {
                            $status = $leaveMaster->m_type;
                            $isLeave = true;
                            break;
                        }
                    }
                }
                if (!$isLeave) {
                    $atd = $attendanceRecords->get($date);
                    if ($atd) {
                        $status = $atd->fh_attendance_status->m_type ?? '-';
                        if ($status === 'P' && in_array($date, $weekOffDates)) {
                            $status = 'WOP';
                        }
                    } else {
                        if (in_array($date, $holidayDates)) {
                            $status = 'HO';
                        } elseif (in_array($date, $weekOffDates)) {
                            $status = 'WO';
                        } else {
                            $status = 'ABS';
                        }
                    }
                }
                $statusPerDay[$day] = $status ?? '-';
            }
            // Calculate attendance counts
            $attendanceCounts = [
                'P' => 0,
                'HD' => 0,
                'HP' => 0,
                'OT' => 0,
                'Leave' => 0,
                'WO' => 0,
                'WOP' => 0,
                'UPL' => 0,
                'HO' => 0,
                'AB' => 0,
                'LC' => 0,
                'EG' => 0,
                'MSP' => 0
            ];
            foreach ($statusPerDay as $status) {
                if (isset($attendanceCounts[$status])) {
                    $attendanceCounts[$status]++;
                } elseif (in_array($status, $leaveKeys)) {
                    $attendanceCounts['Leave']++;
                }
            }
            // Build row with summary
            $row = [
                'S#' => (string) ($rowNumber + 1),
                'Emp Code' => $empCode,
                'Emp Name' => $empName,
            ];
            $row = array_merge($row, $filterData);
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $row[(string) $day] = $statusPerDay[$day];
            }
            $row[''] = '';
            $row['P'] = (string) $attendanceCounts['P'];
            $row['HD'] = (string) $attendanceCounts['HD'];
            $row['HP'] = (string) $attendanceCounts['HP'];  // Fixed
            $row['OT'] = (string) $attendanceCounts['OT'];
            $row['Leave'] = (string) $attendanceCounts['Leave'];
            $row['WO'] = (string) $attendanceCounts['WO'];
            $row['WOP'] = (string) $attendanceCounts['WOP'];
            $row['UPL'] = (string) $attendanceCounts['UPL'];
            $row['HO'] = (string) $attendanceCounts['HO'];
            $row['AB'] = (string) $attendanceCounts['AB'];
            $row['LC'] = (string) $attendanceCounts['LC'];
            $row['EG'] = (string) $attendanceCounts['EG'];
            $row['MSP'] = (string) $attendanceCounts['MSP'];
            $rows[] = $row;
            $rowNumber++;
        }
        return collect($rows);
    }
    public function headings(): array
    {
        $fyStartYear = Carbon::parse($this->fyStartDate)->year;
        $fyEndYear = Carbon::parse($this->fyEndDate)->year;
        $monthNumber = $this->month;
        $yearForMonth = ($monthNumber >= 4) ? $fyStartYear : $fyEndYear;
        $monthStart = Carbon::create($yearForMonth, $monthNumber, 1);
        $daysInMonth = $monthStart->daysInMonth;
        $dayHeadings = array_map('strval', range(1, $daysInMonth));
        $filterHeadings = [];
        if ($this->filters['department'] ?? false) $filterHeadings[] = 'Department';
        if ($this->filters['shift'] ?? false) $filterHeadings[] = 'Shift';
        if ($this->filters['designation'] ?? false) $filterHeadings[] = 'Designation';
        if ($this->filters['workMode'] ?? false) $filterHeadings[] = 'Work Mode';
        if ($this->filters['dealership'] ?? false) $filterHeadings[] = 'Dealership';
        if ($this->filters['branch'] ?? false) $filterHeadings[] = 'Branch';
        if ($this->filters['jobStatus'] ?? false) $filterHeadings[] = 'Job Status';
        if ($this->filters['grade'] ?? false) $filterHeadings[] = 'Grade';
        if ($this->filters['checkingMethod'] ?? false) $filterHeadings[] = 'Checking Method';
        return array_merge(
            ['S#', 'Emp Code', 'Emp Name'],
            $filterHeadings,
            $dayHeadings,
            [''],
            ['P', 'HD', 'HP', 'OT', 'Leave', 'WO', 'WOP', 'UPL', 'HO', 'AB', 'LC', 'EG', 'MSP']
        );
    }
    public function columnWidths(): array
    {
        $widths = [];
        $headings = $this->headings();
        $i = 1;
        foreach ($headings as $heading) {
            $col = Coordinate::stringFromColumnIndex($i++);
            if ($heading === 'Emp Name' || in_array($heading, ['Department', 'Shift', 'Designation', 'Work Mode', 'Dealership', 'Branch', 'Job Status', 'Grade', 'Checking Method'])) {
                $widths[$col] = 13;
            } elseif ($heading === '') {
                $widths[$col] = 5;
            } else {
                $widths[$col] = 6;
            }
        }
        return $widths;
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->setShowGridlines(false);
                $company = 'Kesar Earth Solutions';
                if ($this->records->isNotEmpty()) {
                    $company = $this->records->first()->fh_business->b_name ?? $company;
                }
                $printed = Carbon::now()->format('d-M-Y h:i A T');
                $monthName = Carbon::createFromDate(null, $this->month, 1)->format('F');
                // Header info
                $sheet->setCellValue('A1', $company);
                $sheet->setCellValue('A2', 'Monthly Basic Attendance Report');
                $sheet->setCellValue('A3', "For the month of {$monthName}-{$this->year}");
                $sheet->setCellValue('A4', "Printed on: {$printed}");
                foreach (range(1, 4) as $row) {
                    $sheet->mergeCells("A{$row}:I{$row}");
                    $sheet->getStyle("A{$row}")
                        ->getFont()->setSize(11)->setBold(true);
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }
                // Column indices
                $headingRow = 6;
                $subHeadingRow = 7;
                $rowCount = $this->collection()->count();
                $lastRow = $subHeadingRow + $rowCount;
                $daysInMonth = Carbon::createFromDate(null, $this->month, 1)->daysInMonth;
                $filterColumnCount = count(array_filter($this->filters, fn($v) => $v));
                $dayStartColIndex = 4 + $filterColumnCount;
                $dayStartCol = Coordinate::stringFromColumnIndex($dayStartColIndex);
                $dayEndColIndex = 3 + $filterColumnCount + $daysInMonth;
                $dayEndCol = Coordinate::stringFromColumnIndex($dayEndColIndex);
                $gapColIndex = 4 + $filterColumnCount + $daysInMonth;
                $gapCol = Coordinate::stringFromColumnIndex($gapColIndex);
                $summaryStartColIndex = 5 + $filterColumnCount + $daysInMonth;
                $summaryStartCol = Coordinate::stringFromColumnIndex($summaryStartColIndex);
                $summaryEndColIndex = 4 + $filterColumnCount + $daysInMonth + 13;
                $summaryEndCol = Coordinate::stringFromColumnIndex($summaryEndColIndex);
                // Main columns
                $mainColumns = ['A' => 'S#', 'B' => 'Emp Code', 'C' => 'Emp Name'];
                $filterHeadings = [];
                $colIndex = 4;
                $filterMap = [
                    'department' => 'Department',
                    'shift' => 'Shift',
                    'designation' => 'Designation',
                    'workMode' => 'Work Mode',
                    'dealership' => 'Dealership',
                    'branch' => 'Branch',
                    'jobStatus' => 'Job Status',
                    'grade' => 'Grade',
                    'checkingMethod' => 'Checking Method'
                ];
                foreach ($filterMap as $key => $label) {
                    if ($this->filters[$key] ?? false) {
                        $col = Coordinate::stringFromColumnIndex($colIndex++);
                        $filterHeadings[$col] = $label;
                    }
                }
                $allMainColumns = array_merge($mainColumns, $filterHeadings);
                foreach ($allMainColumns as $col => $label) {
                    $sheet->mergeCells("{$col}6:{$col}7");
                    $sheet->setCellValue("{$col}6", $label);
                    $style = $sheet->getStyle("{$col}6:{$col}7");
                    $style->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setWrapText(true); // Wrap text in merged header
                    $style->getFont()->setSize(10)->setBold(true);
                }
                // DAYS header
                $sheet->setCellValue("{$dayStartCol}6", 'DAYS');
                $sheet->mergeCells("{$dayStartCol}6:{$dayEndCol}6");
                $daysStyle = $sheet->getStyle("{$dayStartCol}6:{$dayEndCol}6");
                $daysStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $daysStyle->getFont()->setSize(10)->setBold(true);
                // SUMMARY header
                $sheet->setCellValue("{$summaryStartCol}6", 'SUMMARY');
                $sheet->mergeCells("{$summaryStartCol}6:{$summaryEndCol}6");
                $sumStyle = $sheet->getStyle("{$summaryStartCol}6:{$summaryEndCol}6");
                $sumStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sumStyle->getFont()->setSize(10)->setBold(true);
                // Day sub-headers
                $yearForMonth = ($this->month >= 4) ? Carbon::parse($this->fyStartDate)->year : Carbon::parse($this->fyEndDate)->year;
                $mStart = Carbon::create($yearForMonth, $this->month, 1);
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $date = $mStart->copy()->day($d);
                    $col = Coordinate::stringFromColumnIndex($dayStartColIndex + $d - 1);
                    $sheet->setCellValue("{$col}7", $date->format('d M Y (D)'));
                    $style = $sheet->getStyle("{$col}7");
                    $style->getFont()->setSize(9)->setBold(true);
                    $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
                }
                // Summary sub-headers
                $summaryLabels = ['P', 'HD', 'HP', 'OT', 'Leave', 'WO', 'WOP', 'UPL', 'HO', 'AB', 'LC', 'EG', 'MSP'];
                foreach ($summaryLabels as $i => $lbl) {
                    $col = Coordinate::stringFromColumnIndex($summaryStartColIndex + $i);
                    $sheet->setCellValue("{$col}7", $lbl);
                    $style = $sheet->getStyle("{$col}7");
                    $style->getFont()->setSize(9)->setBold(true);
                    $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
                }
                // Row heights
                $sheet->getRowDimension(6)->setRowHeight(25);
                $sheet->getRowDimension(7)->setRowHeight(40);
                for ($r = 8; $r <= $lastRow; $r++) $sheet->getRowDimension($r)->setRowHeight(25);
                // Borders
                if ($rowCount > 0) {
                    $mainRange = "A6:{$dayEndCol}{$lastRow}";
                    $summaryRange = "{$summaryStartCol}6:{$summaryEndCol}{$lastRow}";
                    foreach ([$mainRange, $summaryRange] as $range) {
                        $sheet->getStyle($range)->getBorders()->getAllBorders()
                            ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D3D3D3');
                    }
                    $sheet->getStyle("A8:{$dayEndCol}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle("{$summaryStartCol}8:{$summaryEndCol}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle("A8:{$summaryEndCol}{$lastRow}")->getFont()->setSize(8);
                    foreach (array_merge(['C'], array_keys($filterHeadings)) as $col) {
                        $sheet->getStyle("{$col}8:{$col}{$lastRow}")->getAlignment()->setWrapText(true);
                    }
                }
                $sheet->freezePane('D8');
                // ---------- HEADER BACKGROUND (only DAYS section gets blue) ----------
                $mainHeaderEndCol = $dayEndCol;
                $sheet->getStyle("A6:{$mainHeaderEndCol}6")->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('263871');
                $sheet->getStyle("A7:{$mainHeaderEndCol}7")->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('263871');
                $sheet->getStyle("A6:{$mainHeaderEndCol}7")->getFont()->getColor()->setRGB('FFFFFF');
                // ---------- SUMMARY SECTION – NO BACKGROUND COLOR ----------
                $sheet->getStyle("{$summaryStartCol}6:{$summaryEndCol}6")->getFill()->setFillType(Fill::FILL_NONE);
                $sheet->getStyle("{$summaryStartCol}7:{$summaryEndCol}7")->getFill()->setFillType(Fill::FILL_NONE);
                $sheet->getStyle("{$summaryStartCol}8:{$summaryEndCol}{$lastRow}")->getFill()->setFillType(Fill::FILL_NONE);
                $sheet->getStyle("{$summaryStartCol}6:{$summaryEndCol}{$lastRow}")->getFont()->getColor()->setRGB('000000');
                // Abbreviations
                $abbrRow = $lastRow + 2;
                $sheet->setCellValue("A{$abbrRow}", 'Abbreviations');
                $sheet->mergeCells("A{$abbrRow}:{$summaryEndCol}{$abbrRow}");
                $sheet->getStyle("A{$abbrRow}")->getFont()->setSize(10)->setBold(true);
                $sheet->getStyle("A{$abbrRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $abbreviations = [
                    'P' => 'Present Days',
                    'HD' => 'Half-day',
                    'HP' => 'Holiday Present',
                    'OT' => 'Overtime hours',
                    'Leave' => 'All Monthly Leave',
                    'WO' => 'Week-off days',
                    'WOP' => 'Week-off present',
                    'UPL' => 'Unpaid leave',
                    'HO' => 'Holidays',
                    'AB' => 'Absent Days',
                    'LC' => 'Late Coming',
                    'EG' => 'Early going',
                    'MSP' => 'Missed Punch'
                ];
                $r = $abbrRow + 1;
                foreach ($abbreviations as $abbr => $desc) {
                    $sheet->setCellValue("A{$r}", "{$abbr}: {$desc}");
                    $sheet->mergeCells("A{$r}:{$summaryEndCol}{$r}");
                    $sheet->getStyle("A{$r}")->getFont()->setSize(9);
                    $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    $r++;
                }
            }
        ];
    }
    public function startCell(): string
    {
        return 'A7';
    }
}
