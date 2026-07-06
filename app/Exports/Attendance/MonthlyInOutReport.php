<?php
namespace App\Exports\Attendance;

use App\Helpers\CentralLogics;
use App\Models\Business;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\MasterTable;
use App\Models\Employee;
use App\Models\PolicyHolidayList;
use App\Models\PolicyWeekOff;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class MonthlyInOutReport implements
    FromCollection,
    WithHeadings,
    WithColumnWidths,
    WithEvents,
    WithCustomStartCell
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
        $this->slug = $slug;
        $this->year = $year;
        $this->month = (int) $month;
        $this->fyStartDate = $fyStartDate;
        $this->fyEndDate = $fyEndDate;
        $this->businessId = $businessId;
        $this->filters = $filters;

        // Fetch all possible statuses from MasterTable for ATTENDANCE_STATUS
        $allStatuses = MasterTable::where('m_group', 'ATTENDANCE_STATUS')
            ->pluck('m_name', 'm_type')
            ->toArray();

        // Define desired order for non-leave statuses
        $desiredOrder = [
            'P' => 'Present',
            'ABS' => 'Absent',
            'HP' => 'Holiday Present',
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

        // Fetch holiday dates for the month
        $holidayDates = PolicyHolidayList::where('phl_b_id', $this->businessId)
            ->where(function ($q) use ($monthStart, $monthEnd) {
                $q->whereBetween('phl_start_date', [$monthStart, $monthEnd])
                  ->orWhereBetween('phl_end_date', [$monthStart, $monthEnd])
                  ->orWhere(function ($subQ) use ($monthStart, $monthEnd) {
                      $subQ->where('phl_start_date', '<=', $monthStart)
                           ->where('phl_end_date', '>=', $monthEnd);
                  });
            })
            ->get()
            ->flatMap(function ($holiday) use ($monthStart, $monthEnd) {
                try {
                    $start = Carbon::parse($holiday->phl_start_date)->startOfDay();
                    $end = $holiday->phl_end_date ? Carbon::parse($holiday->phl_end_date)->endOfDay() : $start->copy()->endOfDay();
                    $start = $start->max($monthStart);
                    $end = $end->min($monthEnd);
                    return $start->lte($end) ? collect(CarbonPeriod::create($start, $end))->map(fn($date) => $date->format('Y-m-d'))->toArray() : [];
                } catch (\Exception $e) {
                    return [];
                }
            })
            ->unique()
            ->values()
            ->toArray();

        // Fetch week-off dates for the month
        $weekOffDates = [];
        $weekOffPolicies = PolicyWeekOff::where('pwo_b_id', $this->businessId)->get();
        foreach ($weekOffPolicies as $policy) {
            $days = $policy->getDays($policy->pwo_day_ids);
            foreach ($days as $day) {
                $date = $monthStart->copy();
                while ($date->lte($monthEnd)) {
                    if ($date->is($day)) {
                        $weekOffDates[] = $date->toDateString();
                    }
                    $date->addDay();
                }
            }
        }
        $weekOffDates = array_unique($weekOffDates);

        // Group records by employee
        $recordsByEmployee = $this->records->groupBy(function ($record) {
            return $record->fh_employees_details->emp_id;
        })->filter(function ($records, $empId) {
            return $empId !== null; // Remove records with null emp_id
        });
        $employees = Employee::whereIn('emp_id', $recordsByEmployee->keys())->get()->keyBy('emp_id');

        $approvedStatusId = MasterTable::where('m_group', 'LEAVE_STATUS')
            ->where('m_name', 'APPROVED')
            ->value('m_id') ?? 157;

        $rows = [];
        $rowNumber = 1;

        foreach ($recordsByEmployee as $empId => $employeeRecords) {
            $employee = $employees->get($empId);
            if (!$employee) {
                continue;
            }

            $empCode = $employee->emp_code ?? 'N/A';
            $empName = $employee->emp_full_name ?? 'N/A';

            // Initialize filter-based column data
            $filterData = [];
            if ($this->filters['department'] ?? false) {
                $filterData['Department'] = $employee->fh_department->d_name ?? 'N/A';
            }
            if ($this->filters['shift'] ?? false) {
                $filterData['Shift'] = $employee->fh_shift_type->pst_name ?? 'N/A';
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

            // Fetch approved leave records for this employee
            $leaveRecords = $employee->leave_requests()
                ->where('lvr_status', $approvedStatusId)
                ->where(function ($q) use ($monthStart, $monthEnd) {
                    $q->whereBetween('lvr_start_date', [$monthStart, $monthEnd])
                      ->orWhereBetween('lvr_end_date', [$monthStart, $monthEnd])
                      ->orWhere(function ($subQ) use ($monthStart, $monthEnd) {
                          $subQ->where('lvr_start_date', '<=', $monthStart)
                               ->where('lvr_end_date', '>=', $monthEnd);
                      });
                })
                ->get();

            // Build status, check-in, and check-out per day
            $checkInPerDay = [];
            $checkOutPerDay = [];
            $statusPerDay = [];
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $dateCarbon = $monthStart->copy()->day($day);
                $date = $dateCarbon->format('Y-m-d');
                $checkIn = 'N/A';
                $checkOut = 'N/A';
                $status = null;
                $isLeave = false;

                // Check for leave on this day
                foreach ($leaveRecords as $leave) {
                    $lStart = Carbon::parse($leave->lvr_start_date);
                    $lEnd = Carbon::parse($leave->lvr_end_date ?? $leave->lvr_start_date);
                    if ($dateCarbon->between($lStart, $lEnd, true)) {
                        $leaveTypeId = $leave->lvr_cat_type_id;
                        $leaveMaster = MasterTable::where('m_id', $leaveTypeId)->first();
                        if ($leaveMaster) {
                            $status = $leaveMaster->m_type;
                            $isLeave = true;
                            $checkIn = $status;
                            $checkOut = $status;
                            break;
                        }
                    }
                }

                if (!$isLeave) {
                    $atd = $attendanceRecords->get($date);
                    if ($atd) {
                        $status = $atd->fh_attendance_status->m_type ?? 'ABS';
                        if (in_array($status, ['P', 'WOP', 'HP', 'HD', 'OD']) || strpos($status, '/') !== false) {
                            $checkIn = $atd->atd_check_in_time ? Carbon::parse($atd->atd_check_in_time)->format('H:i') : 'N/A';
                            $checkOut = $atd->atd_check_out_time ? Carbon::parse($atd->atd_check_out_time)->format('H:i') : 'N/A';
                        } else {
                            $checkIn = $status;
                            $checkOut = $status;
                        }
                    } else {
                        if (in_array($date, $holidayDates)) {
                            $status = 'HO';
                        } elseif (in_array($date, $weekOffDates)) {
                            $status = 'WO';
                        } else {
                            $status = 'ABS';
                        }
                        $checkIn = $status;
                        $checkOut = $status;
                    }
                }

                $checkInPerDay[$day] = $checkIn;
                $checkOutPerDay[$day] = $checkOut;
                $statusPerDay[$day] = $status ?? 'ABS';
            }

            // Calculate attendance counts
            $attendanceCounts = [];
            foreach ($this->statuses as $key => $label) {
                $attendanceCounts[$key] = 0;
            }
            foreach ($statusPerDay as $status) {
                if (strpos($status, '/') !== false) {
                    $statuses = explode('/', $status);
                    foreach ($statuses as $subStatus) {
                        if (isset($attendanceCounts[$subStatus])) {
                            $attendanceCounts[$subStatus] += 0.5;
                        }
                    }
                } elseif (isset($attendanceCounts[$status])) {
                    $attendanceCounts[$status]++;
                }
            }

            // Adjust counts for week-off present
            foreach ($attendanceRecords as $atd) {
                $status = $atd->fh_attendance_status->m_type ?? 'ABS';
                $date = Carbon::parse($atd->atd_date)->format('Y-m-d');
                if ($status === 'P' && in_array($date, $weekOffDates)) {
                    $attendanceCounts['P'] = max(0, ($attendanceCounts['P'] ?? 0) - 1);
                    $attendanceCounts['WOP'] = ($attendanceCounts['WOP'] ?? 0) + 1;
                }
            }

            // Calculate leave counts
            $leaveKeys = array_keys(array_filter($this->statuses, function ($key) {
                return !in_array($key, ['P', 'ABS', 'HP', 'WO', 'WOP', 'MSP', 'HD', 'HO', 'OD']);
            }, ARRAY_FILTER_USE_KEY));
            $leaveCounts = array_fill_keys($leaveKeys, 0);
            foreach ($leaveRecords as $leave) {
                $leaveTypeId = $leave->lvr_cat_type_id;
                $leaveMaster = MasterTable::where('m_id', $leaveTypeId)->first();
                if ($leaveMaster && isset($leaveCounts[$leaveMaster->m_type])) {
                    $lStart = Carbon::parse($leave->lvr_start_date)->startOfDay();
                    $lEnd = Carbon::parse($leave->lvr_end_date ?? $leave->lvr_start_date)->endOfDay();
                    $lStart = $lStart->max($monthStart);
                    $lEnd = $lEnd->min($monthEnd);
                    if ($lStart->lte($lEnd)) {
                        $days = CarbonPeriod::create($lStart, $lEnd)->count();
                        $leaveCounts[$leaveMaster->m_type] += $days;
                    }
                }
            }
            $totalLeave = array_sum($leaveCounts);

            // Build rows as indexed arrays for correct column order
            $checkInRowBase = array_merge(
                [(string) $rowNumber, $empCode, $empName, 'Check-In'],
                array_values($filterData)
            );
            $checkOutRowBase = array_merge(
                ['', '', '', 'Check-Out'],
                array_fill(0, count($filterData), '') // Empty filter columns for merging
            );

            // Check-In Row
            $checkInRow = array_merge($checkInRowBase, array_values($checkInPerDay), ['']); // Day data + Gap column
            foreach ($this->statuses as $key => $label) {
                $checkInRow[] = number_format($attendanceCounts[$key] ?? 0, 2);
            }
            $checkInRow[] = number_format($totalLeave, 2); // Total Leave

            // Check-Out Row
            $checkOutRow = array_merge($checkOutRowBase, array_values($checkOutPerDay), ['']); // Day data + Gap column
            foreach ($this->statuses as $key => $label) {
                $checkOutRow[] = ''; // Empty status columns
            }
            $checkOutRow[] = ''; // Total Leave

            $rows[] = $checkInRow;
            $rows[] = $checkOutRow;

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

        $dayHeadings = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dayHeadings[] = (string) $day;
        }

        // Define filter-based headings
        $filterHeadings = [];
        if ($this->filters['department'] ?? false) {
            $filterHeadings[] = 'Department';
        }
        if ($this->filters['shift'] ?? false) {
            $filterHeadings[] = 'Shift';
        }
        if ($this->filters['designation'] ?? false) {
            $filterHeadings[] = 'Designation';
        }
        if ($this->filters['workMode'] ?? false) {
            $filterHeadings[] = 'Work Mode';
        }
        if ($this->filters['dealership'] ?? false) {
            $filterHeadings[] = 'Dealership';
        }
        if ($this->filters['branch'] ?? false) {
            $filterHeadings[] = 'Branch';
        }
        if ($this->filters['jobStatus'] ?? false) {
            $filterHeadings[] = 'Job Status';
        }
        if ($this->filters['grade'] ?? false) {
            $filterHeadings[] = 'Grade';
        }
        if ($this->filters['checkingMethod'] ?? false) {
            $filterHeadings[] = 'Checking Method';
        }

        return array_merge(
            ['S#', 'Emp Code', 'Emp Name', 'Type'],
            $filterHeadings,
            $dayHeadings,
            [''],
            array_keys($this->statuses),
            ['Total Leave']
        );
    }

   public function columnWidths(): array
{
    $widths = [];
    $headings = $this->headings();
    $collection = $this->collection();
    $daysInMonth = Carbon::createFromDate(null, $this->month, 1)->daysInMonth;
    $filterColumns = array_filter($this->filters, fn($value) => $value);
    $filterColumnCount = count($filterColumns);
    $i = 1;

    foreach ($headings as $index => $heading) {
        $col = Coordinate::stringFromColumnIndex($i);
        $maxLength = strlen($heading);

        // Column indices
        $sIndex = 0;
        $codeIndex = 1;
        $nameIndex = 2;
        $typeIndex = 3;
        $filterStartIndex = 4;
        $filterEndIndex = 4 + $filterColumnCount - 1;
        $dayStartIndex = 4 + $filterColumnCount;
        $dayEndIndex = 4 + $filterColumnCount + $daysInMonth - 1;
        $gapIndex = 4 + $filterColumnCount + $daysInMonth;
        $statusStartIndex = 5 + $filterColumnCount + $daysInMonth;
        $totalLeaveIndex = 5 + $filterColumnCount + $daysInMonth + count($this->statuses);

        // Adjust width based on column type
        if ($index === $nameIndex) {
            $maxLength = max($maxLength, $collection->max(fn($row) => strlen($row[$nameIndex] ?? '')) ?: 10);
            $widths[$col] = min($maxLength, 15);
        } elseif ($index === $codeIndex) {
            $maxLength = max($maxLength, $collection->max(fn($row) => strlen($row[$codeIndex] ?? '')) ?: 10);
            $widths[$col] = min($maxLength + 2, 15);
        } elseif ($index === $typeIndex) {
            $widths[$col] = 12;
        } elseif ($index >= $filterStartIndex && $index <= $filterEndIndex) {
            $maxLength = max($maxLength, $collection->max(fn($row) => strlen($row[$index] ?? '')) ?: 10);
            $widths[$col] = min($maxLength + 2, 15);
        } elseif ($index === $gapIndex) {
            $widths[$col] = 3;
        } elseif ($index === $totalLeaveIndex) {
            $widths[$col] = 5;
        } 
        // Days columns: force width = 7
        elseif ($index >= $dayStartIndex && $index <= $dayEndIndex) {
            $widths[$col] = 7; // Fixed width for all day columns
        } 
        // Summary status columns
        else {
            $maxLength = max($maxLength, $collection->max(fn($row) => strlen($row[$index] ?? '')) ?: 8);
            $widths[$col] = min($maxLength + 2, 5);
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

                if ($this->records->isNotEmpty()) {
                    $b_id = $this->records->first()->fh_business->b_id ?? '';
                    $company = Business::where('b_id', $b_id)->value('b_name') ?? 'N/A';
                }
                $printed = Carbon::now()->format('d-M-Y h:i A T');
                $monthName = Carbon::createFromDate(null, $this->month, 1)->format('F');

                // Set header information
                $sheet->setCellValue('A1', $company);
                $sheet->setCellValue('A2', 'Monthly In/Out Attendance Report');
                $sheet->setCellValue('A3', "For the month of {$monthName}-{$this->year}");
                $sheet->setCellValue('A4', "Printed on: {$printed}");

                // Style header rows
                foreach (range(1, 5) as $row) {
                    $sheet->mergeCells("A{$row}:I{$row}");
                    $sheet->getStyle("A{$row}")
                        ->getFont()
                        ->setSize(11)
                        ->setBold(true);
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                $headingRow = 6;
                $subHeadingRow = 7;
                $rowCount = $this->collection()->count();
                $lastRow = $subHeadingRow + $rowCount;

                // Define column ranges
                $daysInMonth = Carbon::createFromDate(null, $this->month, 1)->daysInMonth;
                $filterColumns = array_filter($this->filters, fn($value) => $value);
                $filterColumnCount = count($filterColumns);
                $dayStartColIndex = 5 + $filterColumnCount;
                $dayStartCol = Coordinate::stringFromColumnIndex($dayStartColIndex);
                $dayEndColIndex = 4 + $filterColumnCount + $daysInMonth;
                $dayEndCol = Coordinate::stringFromColumnIndex($dayEndColIndex);
                $gapColIndex = 5 + $filterColumnCount + $daysInMonth;
                $gapCol = Coordinate::stringFromColumnIndex($gapColIndex);
                $summaryStartColIndex = 6 + $filterColumnCount + $daysInMonth;
                $summaryStartCol = Coordinate::stringFromColumnIndex($summaryStartColIndex);
                $summaryEndColIndex = 6 + $filterColumnCount + $daysInMonth + count($this->statuses);
                $summaryEndCol = Coordinate::stringFromColumnIndex($summaryEndColIndex);

                // Set main column headings
                $mainColumns = ['A' => 'S#', 'B' => 'Emp Code', 'C' => 'Emp Name', 'D' => 'Type'];
                $filterHeadings = [];
                $colIndex = 5;
                if ($this->filters['department'] ?? false) {
                    $col = Coordinate::stringFromColumnIndex($colIndex++);
                    $filterHeadings[$col] = 'Department';
                }
                if ($this->filters['shift'] ?? false) {
                    $col = Coordinate::stringFromColumnIndex($colIndex++);
                    $filterHeadings[$col] = 'Shift';
                }
                if ($this->filters['designation'] ?? false) {
                    $col = Coordinate::stringFromColumnIndex($colIndex++);
                    $filterHeadings[$col] = 'Designation';
                }
                if ($this->filters['workMode'] ?? false) {
                    $col = Coordinate::stringFromColumnIndex($colIndex++);
                    $filterHeadings[$col] = 'Work Mode';
                }
                if ($this->filters['dealership'] ?? false) {
                    $col = Coordinate::stringFromColumnIndex($colIndex++);
                    $filterHeadings[$col] = 'Dealership';
                }
                if ($this->filters['branch'] ?? false) {
                    $col = Coordinate::stringFromColumnIndex($colIndex++);
                    $filterHeadings[$col] = 'Branch';
                }
                if ($this->filters['jobStatus'] ?? false) {
                    $col = Coordinate::stringFromColumnIndex($colIndex++);
                    $filterHeadings[$col] = 'Job Status';
                }
                if ($this->filters['grade'] ?? false) {
                    $col = Coordinate::stringFromColumnIndex($colIndex++);
                    $filterHeadings[$col] = 'Grade';
                }
                if ($this->filters['checkingMethod'] ?? false) {
                    $col = Coordinate::stringFromColumnIndex($colIndex++);
                    $filterHeadings[$col] = 'Checking Method';
                }
                $allMainColumns = array_merge($mainColumns, $filterHeadings);
                foreach ($allMainColumns as $col => $label) {
                    $sheet->mergeCells("{$col}6:{$col}7");
                    $sheet->setCellValue("{$col}6", $label);
                    $sheet->getStyle("{$col}6:{$col}7")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setWrapText(true);
                    $sheet->getStyle("{$col}6:{$col}7")
                        ->getFont()
                        ->setSize(10)
                        ->setBold(true);
                }

                // Set DAYS header
                $sheet->setCellValue("{$dayStartCol}6", 'DAYS');
                $sheet->mergeCells("{$dayStartCol}6:{$dayEndCol}6");
                $sheet->getStyle("{$dayStartCol}6")
                    ->getFont()
                    ->setSize(10)
                    ->setBold(true);
                $sheet->getStyle("{$dayStartCol}6")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setWrapText(true);

                // Set day subheadings with format d M Y (D)
                $fyStartYear = Carbon::parse($this->fyStartDate)->year;
                $fyEndYear = Carbon::parse($this->fyEndDate)->year;
                $yearForMonth = ($this->month >= 4) ? $fyStartYear : $fyEndYear;
                $monthStart = Carbon::create($yearForMonth, $this->month, 1);
                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $dateCarbon = $monthStart->copy()->day($day);
                    $col = Coordinate::stringFromColumnIndex($dayStartColIndex + $day - 1);
                    $sheet->setCellValue("{$col}7", $dateCarbon->format('d M Y (D)'));
                    $sheet->getStyle("{$col}7")
                        ->getFont()
                        ->setSize(9)
                        ->setBold(true);
                    $sheet->getStyle("{$col}7")
                        ->getAlignment()
                        ->setWrapText(true);
                }

                // Set SUMMARY header
                $sheet->setCellValue("{$summaryStartCol}6", 'SUMMARY');
                $sheet->mergeCells("{$summaryStartCol}6:{$summaryEndCol}6");
                $sheet->getStyle("{$summaryStartCol}6")
                    ->getFont()
                    ->setSize(10)
                    ->setBold(true);
                $sheet->getStyle("{$summaryStartCol}6")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setWrapText(true);

                // Set summary subheadings
                $summaryLabels = array_merge(array_keys($this->statuses), ['Total Leave']);
                for ($i = 0; $i < count($summaryLabels); $i++) {
                    $col = Coordinate::stringFromColumnIndex($summaryStartColIndex + $i);
                    $sheet->setCellValue("{$col}7", $summaryLabels[$i]);
                    $sheet->getStyle("{$col}7")
                        ->getFont()
                        ->setSize(9)
                        ->setBold(true);
                    $sheet->getStyle("{$col}7")
                        ->getAlignment()
                        ->setWrapText(true);
                }

                // Apply background color to headers
                $mainHeaderEndCol = Coordinate::stringFromColumnIndex($dayEndColIndex);
                $sheet->getStyle("A6:{$mainHeaderEndCol}6")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('263871');
                $sheet->getStyle("A7:{$mainHeaderEndCol}7")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('263871');
                $sheet->getStyle("A6:{$mainHeaderEndCol}7")->getFont()->getColor()->setRGB('FFFFFF');

                // Freeze panes: rows 1–7 and columns A–D (S#, Emp Code, Emp Name, Type)
                $sheet->freezePane('E8');

                // Apply styles to data
                if ($rowCount > 0) {
                    // Merge cells for S#, Emp Code, Emp Name, and filter columns
                    $currentRow = 8;
                    while ($currentRow <= $lastRow) {
                        $sheet->mergeCells("A{$currentRow}:A" . ($currentRow + 1));
                        $sheet->mergeCells("B{$currentRow}:B" . ($currentRow + 1));
                        $sheet->mergeCells("C{$currentRow}:C" . ($currentRow + 1));
                        // Merge filter columns
                        foreach (array_keys($filterHeadings) as $col) {
                            $colIndex = Coordinate::columnIndexFromString($col) - 1;
                            $sheet->mergeCells("{$col}{$currentRow}:{$col}" . ($currentRow + 1));
                        }
                        $currentRow += 2;
                    }

                    // Apply alignment to merged cells
                    $sheet->getStyle("A8:C{$lastRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);
                    $filterCols = array_keys($filterHeadings);
                    foreach ($filterCols as $col) {
                        $sheet->getStyle("{$col}8:{$col}{$lastRow}")
                            ->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                            ->setVertical(Alignment::VERTICAL_CENTER)
                            ->setWrapText(true);
                    }

                    // Apply borders
                    $mainDataRange = "A6:{$dayEndCol}{$lastRow}";
                    $summaryDataRange = "{$summaryStartCol}6:{$summaryEndCol}{$lastRow}";
                    $sheet->getStyle($mainDataRange)
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->getColor()
                        ->setRGB('D3D3D3');
                    $sheet->getStyle($summaryDataRange)
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->getColor()
                        ->setRGB('D3D3D3');

                    // Apply alignment to other data cells
                    $sheet->getStyle("D8:{$dayEndCol}{$lastRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle("{$summaryStartCol}8:{$summaryEndCol}{$lastRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);

                    // Apply font size
                    $sheet->getStyle("A8:{$summaryEndCol}{$lastRow}")
                        ->getFont()
                        ->setSize(8);

                    // Wrap text for employee names
                    $sheet->getStyle("C8:C{$lastRow}")
                        ->getAlignment()
                        ->setWrapText(true);

                    // Apply alternating background colors
                    $currentRow = 8;
                    $employeeIndex = 1;
                    while ($currentRow <= $lastRow) {
                        $color = ($employeeIndex % 2 == 1) ? 'F5F5F5' : 'FFFFFF';
                        $range = "A{$currentRow}:{$summaryEndCol}" . ($currentRow + 1);
                        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($color);
                        $currentRow += 2;
                        $employeeIndex++;
                    }
                }

                // Set row heights
                $sheet->getRowDimension(6)->setRowHeight(15);
                $sheet->getRowDimension(7)->setRowHeight(45);
                for ($row = 8; $row <= $lastRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(18);
                }

                // Legend
                $legendRow = $lastRow + 2;
                $sheet->setCellValue("A{$legendRow}", 'Abbreviations');
                $sheet->mergeCells("A{$legendRow}:{$summaryEndCol}{$legendRow}");
                $sheet->getStyle("A{$legendRow}")
                    ->getFont()
                    ->setSize(10)
                    ->setBold(true);
                $sheet->getStyle("A{$legendRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $abbreviations = [
                    'P' => 'Present Days',
                    'ABS' => 'Absent Days',
                    'HP' => 'Holiday Present',
                    'WO' => 'Week-off Days',
                    'WOP' => 'Weekly Off Present',
                    'MSP' => 'Missed Punch',
                    'HD' => 'Half Day',
                    'HO' => 'Holidays',
                    'OD' => 'Out Door Duty',
                    'CL' => 'Casual Leave',
                    'PL' => 'Privilege Leave',
                    'SL' => 'Sick Leave',
                    'COFF' => 'Comp Off',
                    'EL' => 'Earned Leave',
                    'ML' => 'Maternity Leave',
                    'MRL' => 'Marriage Leave',
                    'BL' => 'Bereavement Leave',
                    'UPL' => 'Unpaid Leave',
                    'Total Leave' => 'Sum of all leave types taken'
                ];

                $abbrStartRow = $legendRow + 1;
                foreach ($abbreviations as $abbr => $desc) {
                    $row = $abbrStartRow++;
                    $sheet->setCellValue("A{$row}", "{$abbr}: {$desc}");
                    $sheet->mergeCells("A{$row}:{$summaryEndCol}{$row}");
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
        return 'A7';
    }
}