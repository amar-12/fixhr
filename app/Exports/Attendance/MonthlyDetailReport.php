<?php
namespace App\Exports\Attendance;
use App\Helpers\CentralLogics;
use App\Models\Business;
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
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
class MonthlyDetailReport implements
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
        $this->records     = $records;
        $this->slug        = $slug;
        $this->year        = $year;
        $this->month       = (int) $month;
        $this->fyStartDate = $fyStartDate;
        $this->fyEndDate   = $fyEndDate;
        $this->businessId  = $businessId;
        $this->filters     = $filters;
        // ---------- STATUSES ----------
        $allStatuses = MasterTable::where('m_group', 'ATTENDANCE_STATUS')
            ->pluck('m_name', 'm_type')
            ->toArray();
        $desiredOrder = [
            'P'   => 'Present',
            'ABS' => 'Absent',
            'HP'  => 'Half Pay',
            'WO'  => 'Weekly Off',
            'WOP' => 'Weekly Off Present',
            'MSP' => 'Missed Punch',
            'HD'  => 'Half Day',
            'HO'  => 'Holiday',
            'OD'  => 'Out Door Duty',
        ];
        $this->statuses = [];
        foreach ($desiredOrder as $key => $value) {
            if (isset($allStatuses[$key])) {
                $this->statuses[$key] = $allStatuses[$key];
            }
        }
        // leave categories
        $leaveCats = MasterTable::where('m_group', 'LEAVE_CATEGORY')
            ->pluck('m_name', 'm_type')
            ->toArray();
        $this->statuses = array_merge($this->statuses, $leaveCats);
    }
    /* --------------------------------------------------------------------- */
    /* --------------------------- COLLECTION ------------------------------ */
    /* --------------------------------------------------------------------- */
    public function collection()
    {
        if ($this->records->isEmpty()) {
            return collect([]);
        }
        $fyStartYear = Carbon::parse($this->fyStartDate)->year;
        $fyEndYear   = Carbon::parse($this->fyEndDate)->year;
        $yearForMonth = ($this->month >= 4) ? $fyStartYear : $fyEndYear;
        $monthStart = Carbon::create($yearForMonth, $this->month, 1)->startOfMonth();
        $daysInMonth = $monthStart->daysInMonth;
        $monthEnd    = $monthStart->copy()->endOfMonth();
        // ---------- HOLIDAYS ----------
        $holidayDates = PolicyHolidayList::where('phl_b_id', $this->businessId)
            ->where(function ($q) use ($monthStart, $monthEnd) {
                $q->where('phl_start_date', '<=', $monthEnd)
                    ->where('phl_end_date', '>=', $monthStart);
            })
            ->get()
            ->flatMap(function ($h) use ($monthStart, $monthEnd) {
                try {
                    $s = Carbon::parse($h->phl_start_date);
                    $e = $h->phl_end_date ? Carbon::parse($h->phl_end_date) : $s;
                    $s = $s->max($monthStart);
                    $e = $e->min($monthEnd);
                    return $s->lte($e)
                        ? collect($s->daysUntil($e))->map(fn($d) => $d->format('Y-m-d'))->toArray()
                        : [];
                } catch (\Exception $e) {
                    return [];
                }
            })
            ->unique()
            ->values()
            ->toArray();
        // ---------- GROUP BY EMPLOYEE ----------
        $recordsByEmployee = $this->records
            ->groupBy(fn($r) => $r->fh_employees_details?->emp_id)
            ->filter(fn($v, $k) => $k !== null);
        $employees = Employee::whereIn('emp_id', $recordsByEmployee->keys())
            ->get()
            ->keyBy('emp_id');
        $approvedId = MasterTable::where('m_group', 'LEAVE_STATUS')
            ->where('m_name', 'APPROVED')
            ->value('m_id') ?? 157;
        $rows       = [];
        $rowNumber  = 1;
        foreach ($recordsByEmployee as $empId => $empRecs) {
            $employee = $employees->get($empId)
                ?? ($empRecs->first()->fh_employees_details ?? null);
            if (! $employee) {
                continue;
            }
            $empCode = $employee->emp_code ?? 'N/A';
            $empName = $employee->emp_full_name ?? 'N/A';
            // ---------- FILTER COLUMNS ----------
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
                $wm = MasterTable::where('m_id', $empRecs->first()->fh_attendance_work_mode->m_id)->first();
                $filterData['Work Mode'] = $wm->m_name ?? 'N/A';
            }
            if ($this->filters['dealership'] ?? false) {
                $filterData['Dealership'] = $employee->fh_dealership->dlr_name ?? 'N/A';
            }
            if ($this->filters['branch'] ?? false) {
                $filterData['Branch'] = $employee->fh_branch->br_name ?? 'N/A';
            }
            if ($this->filters['jobStatus'] ?? false) {
                $js = MasterTable::where('m_id', $employee->emp_job_status)->first();
                $filterData['Job Status'] = $js->m_name ?? 'N/A';
            }
            if ($this->filters['grade'] ?? false) {
                $g = \App\Models\Grade::where('g_id', $employee->emp_grade_id)->first();
                $filterData['Grade'] = $g->g_name ?? 'N/A';
            }
            if ($this->filters['checkingMethod'] ?? false) {
                $cm = MasterTable::where('m_id', $empRecs->first()->atd_checkin_method_id)->first();
                $filterData['Checking Method'] = $cm->m_name ?? 'N/A';
            }
            // ---------- ATTENDANCE ----------
            $attendance = $empRecs
                ->filter(fn($r) => Carbon::parse($r->atd_date)->between($monthStart, $monthEnd))
                ->keyBy(fn($r) => Carbon::parse($r->atd_date)->format('Y-m-d'));
            // ---------- APPROVED LEAVES ----------
            $leaves = $employee->leave_requests()
                ->where('lvr_status', $approvedId)
                ->where(fn($q) => $q->where('lvr_start_date', '<=', $monthEnd)
                    ->where('lvr_end_date', '>=', $monthStart))
                ->get();
            // ---------- WEEK-OFF ----------
            $weekOff = CentralLogics::getWeekOffDatesReport($employee, null, null, $monthStart, $monthEnd);
            $leaveKeys = array_keys(array_filter(
                $this->statuses,
                fn($k) => !in_array($k, ['P', 'ABS', 'HP', 'WO', 'WOP', 'MSP', 'HD', 'HO', 'OD']),
                ARRAY_FILTER_USE_KEY
            ));
            $checkIn   = $checkOut = $workHrs = $statusArr = [];
            $totalMins = 0;
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dateC = $monthStart->copy()->day($d);
                $date  = $dateC->format('Y-m-d');
                $ci = $co = $wh = 'N/A';
                $st = null;
                $isLeave = false;
                // ---- LEAVE ----
                foreach ($leaves as $lv) {
                    $ls = Carbon::parse($lv->lvr_start_date);
                    $le = Carbon::parse($lv->lvr_end_date ?? $lv->lvr_start_date);
                    if ($dateC->between($ls, $le, true)) {
                        $lm = MasterTable::where('m_id', $lv->lvr_cat_type_id)->first();
                        if ($lm) {
                            $st = $lm->m_type;
                            $isLeave = true;
                        }
                        break;
                    }
                }
                $atd = $attendance->get($date);
                if (! $isLeave) {
                    if ($atd) {
                        $st = $atd->fh_attendance_status->m_type ?? 'ABS';
                        if ($st === 'P' && in_array($date, $weekOff)) {
                            $st = 'WOP';
                        }
                        if (in_array($st, ['P', 'WOP'])) {
                            $ci = $atd->atd_check_in_time
                                ? Carbon::parse($atd->atd_check_in_time)->format('H:i')
                                : 'N/A';
                            $co = $atd->atd_check_out_time
                                ? Carbon::parse($atd->atd_check_out_time)->format('H:i')
                                : 'N/A';
                            $wh = $this->formatHours($atd->atd_total_worked_hours);
                            if ($wh !== '-' && strpos($wh, ':') !== false) {
                                [$h, $m] = explode(':', $wh);
                                $totalMins += $h * 60 + $m;
                            }
                        } else {
                            $ci = $co = $st;
                        }
                    } else {
                        $st = in_array($date, $holidayDates) ? 'HO'
                            : (in_array($date, $weekOff) ? 'WO' : 'ABS');
                        $ci = $co = $st;
                    }
                } else {
                    $ci = $co = $st;
                }
                $checkIn[$d]   = $ci;
                $checkOut[$d]  = $co;
                $workHrs[$d]   = $wh;
                $statusArr[$d] = $st ?? 'ABS';
            }
            $totalHrs = sprintf('%02d:%02d', floor($totalMins / 60), $totalMins % 60);
            // ---------- COUNTS ----------
            $attCounts = array_fill_keys(array_keys($this->statuses), 0);
            foreach ($statusArr as $s) {
                if (isset($attCounts[$s]) && !in_array($s, $leaveKeys)) {
                    $attCounts[$s]++;
                }
            }
            $leaveCounts = array_fill_keys($leaveKeys, 0);
            foreach ($leaves as $lv) {
                $lm = MasterTable::where('m_id', $lv->lvr_cat_type_id)->first();
                if ($lm && isset($leaveCounts[$lm->m_type])) {
                    $leaveCounts[$lm->m_type] += (float) $lv->lvr_total_leave_days;
                }
            }
            $totalLeave = array_sum($leaveCounts);
            // ---------- BUILD ROWS ----------
            $baseCheckIn  = array_merge([(string)$rowNumber, $empCode, $empName, 'Check-In'], array_values($filterData));
            $baseCheckOut = array_merge([(string)$rowNumber, $empCode, $empName, 'Check-Out'], array_values($filterData));
            $baseWorkHrs  = array_merge(['', '', '', 'Work Hrs'], array_fill(0, count($filterData), ''));
            $checkInRow  = array_merge($baseCheckIn,  array_values($checkIn),  ['']);
            $checkOutRow = array_merge($baseCheckOut, array_values($checkOut), ['']);
            $workHrsRow  = array_merge($baseWorkHrs,  array_values($workHrs),  ['']);
            foreach ($this->statuses as $k => $l) {
                $checkInRow[]  = in_array($k, $leaveKeys)
                    ? number_format($leaveCounts[$k], 2)
                    : (string)($attCounts[$k] ?? 0);
                $checkOutRow[] = '';
                $workHrsRow[]  = '';
            }
            $checkInRow[]  = number_format($totalLeave, 2);
            $checkInRow[]  = '';
            $checkOutRow[] = '';
            $checkOutRow[] = '';
            $workHrsRow[]  = '';
            $workHrsRow[]  = $totalHrs;
            // pad to the exact number of headings (prevents column-shift)
            $expected = count($this->headings());
            $checkInRow  = array_pad($checkInRow,  $expected, '');
            $checkOutRow = array_pad($checkOutRow, $expected, '');
            $workHrsRow  = array_pad($workHrsRow,  $expected, '');
            $rows[] = $checkInRow;
            $rows[] = $checkOutRow;
            $rows[] = $workHrsRow;
            $rowNumber++;
        }
        return collect($rows);
    }
    private function formatHours($val)
    {
        if (! $val) return '-';
        if (is_numeric($val)) {
            return sprintf('%02d:%02d', floor($val), ($val - floor($val)) * 60);
        }
        if (strtotime($val) !== false) {
            return (new \DateTime($val))->format('H:i');
        }
        return '-';
    }
    /* --------------------------------------------------------------------- */
    /* ------------------------------ HEADINGS ----------------------------- */
    /* --------------------------------------------------------------------- */
    public function headings(): array
    {
        $fyStartYear = Carbon::parse($this->fyStartDate)->year;
        $fyEndYear   = Carbon::parse($this->fyEndDate)->year;
        $yearForMonth = ($this->month >= 4) ? $fyStartYear : $fyEndYear;
        $monthStart   = Carbon::create($yearForMonth, $this->month, 1);
        $daysInMonth  = $monthStart->daysInMonth;
        $dayHeadings = array_map('strval', range(1, $daysInMonth));
        $filterHeadings = [];
        $filters = [
            'department'     => 'Department',
            'shift'          => 'Shift',
            'designation'    => 'Designation',
            'workMode'       => 'Work Mode',
            'dealership'     => 'Dealership',
            'branch'         => 'Branch',
            'jobStatus'      => 'Job Status',
            'grade'          => 'Grade',
            'checkingMethod' => 'Checking Method',
        ];
        foreach ($filters as $key => $label) {
            if ($this->filters[$key] ?? false) {
                $filterHeadings[] = $label;
            }
        }
        return array_merge(
            ['S#', 'Emp Code', 'Emp Name', 'Type'],
            $filterHeadings,
            $dayHeadings,
            [''],
            array_keys($this->statuses),
            ['Total Leave', 'Total Work Hrs']
        );
    }
    /* --------------------------------------------------------------------- */
    /* --------------------------- COLUMN WIDTHS -------------------------- */
    /* --------------------------------------------------------------------- */
    public function columnWidths(): array
    {
        $widths = [];
        $headings = $this->headings();
        $collection = $this->collection();
        $daysInMonth = Carbon::createFromDate(null, $this->month, 1)->daysInMonth;
        $filterCount = count(array_filter($this->filters, fn($v) => $v));
        $i = 1;
        foreach ($headings as $idx => $h) {
            $col = Coordinate::stringFromColumnIndex($i);
            $len = strlen($h);
            $nameIdx = 2;
            $codeIdx = 1;
            $typeIdx = 3;
            $filterStart = 4;
            $filterEnd   = 4 + $filterCount - 1;
            $dayStart    = 4 + $filterCount;
            $dayEnd      = 4 + $filterCount + $daysInMonth - 1;
            $gapIdx      = $dayEnd + 1;
            $summaryStart = $gapIdx + 1;
            $totalLeave  = $summaryStart + count($this->statuses);
            $totalWork   = $totalLeave + 1;
            if ($idx === $nameIdx) {
                $len = max($len, $collection->max(fn($r) => strlen($r[$nameIdx] ?? '')) ?: 10);
                $widths[$col] = min($len, 13);
            } elseif ($idx === $codeIdx) {
                $len = max($len, $collection->max(fn($r) => strlen($r[$codeIdx] ?? '')) ?: 10);
                $widths[$col] = min($len + 2, 13);
            } elseif ($idx === $typeIdx) {
                $widths[$col] = 12;
            } elseif ($idx >= $filterStart && $idx <= $filterEnd) {
                $len = max($len, $collection->max(fn($r) => strlen($r[$idx] ?? '')) ?: 10);
                $widths[$col] = min($len + 2, 13);
            } elseif ($idx === $gapIdx) {
                $widths[$col] = 3;
            } elseif ($idx === $totalLeave || $idx === $totalWork) {
                $widths[$col] = 12;
            } elseif ($idx >= $dayStart && $idx <= $dayEnd) {
                $len = max($len, $collection->max(fn($r) => strlen($r[$idx] ?? '')) ?: 5);
                $widths[$col] = min($len + 2, 12);
            } else {
                $len = max($len, $collection->max(fn($r) => strlen($r[$idx] ?? '')) ?: 5);
                $widths[$col] = min($len + 2, 10);
            }
            $i++;
        }
        return $widths;
    }
    /* --------------------------------------------------------------------- */
    /* ------------------------------ EVENTS ------------------------------- */
    /* --------------------------------------------------------------------- */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->setShowGridlines(false);
                // ---------- COMPANY & PRINT INFO ----------
                $company = $this->records->isNotEmpty()
                    ? (Business::where('b_id', $this->records->first()->fh_business->b_id ?? '')->value('b_name') ?? 'N/A')
                    : 'N/A';
                $printed   = Carbon::now()->format('d-M-Y h:i A T');
                $monthName = Carbon::createFromDate(null, $this->month, 1)->format('F');
                $sheet->setCellValue('A1', $company);
                $sheet->setCellValue('A2', 'Monthly Detail Attendance Report');
                $sheet->setCellValue('A3', "For the month of {$monthName}-{$this->year}");
                $sheet->setCellValue('A4', "Printed on: {$printed}");
                foreach (range(1, 4) as $r) {
                    $sheet->mergeCells("A{$r}:I{$r}");
                    $sheet->getStyle("A{$r}")->getFont()->setSize(11)->setBold(true);
                    $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }
                $rowCount = $this->collection()->count();
                if ($rowCount === 0) {
                    return;
                }
                // ---------- COLUMN CALCULATIONS ----------
                $filterCount      = count(array_filter($this->filters, fn($v) => $v));
                $dayStartIdx      = 5 + $filterCount;                               // after S#,Code,Name,Type + filters
                $daysInMonth      = Carbon::createFromDate(null, $this->month, 1)->daysInMonth;
                $dayEndIdx        = $dayStartIdx + $daysInMonth - 1;
                $gapIdx           = $dayEndIdx + 1;
                $summaryStartIdx  = $gapIdx + 1;
                $summaryEndIdx    = $summaryStartIdx + count($this->statuses) + 1;
                $dayStartCol   = Coordinate::stringFromColumnIndex($dayStartIdx);
                $dayEndCol     = Coordinate::stringFromColumnIndex($dayEndIdx);
                $gapCol        = Coordinate::stringFromColumnIndex($gapIdx);
                $summaryStart  = Coordinate::stringFromColumnIndex($summaryStartIdx);
                $summaryEnd    = Coordinate::stringFromColumnIndex($summaryEndIdx);
                // ---------- MAIN HEADERS (merged 6-7) ----------
                $mainCols = ['A' => 'S#', 'B' => 'Emp Code', 'C' => 'Emp Name', 'D' => 'Type'];
                $filterCols = [];
                $cIdx = 5;
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
                        $col = Coordinate::stringFromColumnIndex($cIdx++);
                        $filterCols[$col] = $label;
                    }
                }
                foreach (array_merge($mainCols, $filterCols) as $col => $label) {
                    $sheet->mergeCells("{$col}6:{$col}7");
                    $sheet->setCellValue("{$col}6", $label);
                    $style = $sheet->getStyle("{$col}6:{$col}7");
                    $style->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setWrapText(true);
                    $style->getFont()->setSize(10)->setBold(true);
                }
                // ---------- DAYS ----------
                $sheet->setCellValue("{$dayStartCol}6", 'DAYS');
                $sheet->mergeCells("{$dayStartCol}6:{$dayEndCol}6");
                $daysStyle = $sheet->getStyle("{$dayStartCol}6:{$dayEndCol}6");
                $daysStyle->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);
                $daysStyle->getFont()->setSize(10)->setBold(true)->getColor()->setRGB('FFFFFF');
                $daysStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('263871');
                // day sub-headings (row 7)
                $yearForMonth = ($this->month >= 4)
                    ? Carbon::parse($this->fyStartDate)->year
                    : Carbon::parse($this->fyEndDate)->year;
                $mStart = Carbon::create($yearForMonth, $this->month, 1);
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $date = $mStart->copy()->day($d);
                    $col  = Coordinate::stringFromColumnIndex($dayStartIdx + $d - 1);
                    $sheet->setCellValue("{$col}7", $date->format('d M Y (D)'));
                    $style = $sheet->getStyle("{$col}7");
                    $style->getFont()->setSize(9)->setBold(true);
                    $style->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setWrapText(true);
                }
                // ---------- SUMMARY ----------
                $sheet->setCellValue("{$summaryStart}6", 'SUMMARY');
                $sheet->mergeCells("{$summaryStart}6:{$summaryEnd}6");
                $sumStyle = $sheet->getStyle("{$summaryStart}6:{$summaryEnd}6");
                $sumStyle->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);
                $sumStyle->getFont()->setSize(10)->setBold(true)->getColor()->setRGB('FFFFFF');
                $sumStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('263871');
                // summary sub-headings (row 7)
                $sumLabels = array_merge(array_keys($this->statuses), ['Total Leave', 'Total Work Hrs']);
                foreach ($sumLabels as $i => $lbl) {
                    $col = Coordinate::stringFromColumnIndex($summaryStartIdx + $i);
                    $sheet->setCellValue("{$col}7", $lbl);
                    $style = $sheet->getStyle("{$col}7");
                    $style->getFont()->setSize(9)->setBold(true);
                    $style->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setWrapText(true);
                }
                // ---------- HEADER BACKGROUND (only DAYS section gets blue) ----------
                $headerEndCol = $dayEndCol;  // Last column of the "DAYS" section
                // Apply blue background ONLY to the main header (S#, Code, Name, Type, Filters, DAYS)
                $sheet->getStyle("A6:{$headerEndCol}6")->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('263871');
                $sheet->getStyle("A7:{$headerEndCol}7")->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('263871');
                // White text only on blue cells (DAYS section)
                $sheet->getStyle("A6:{$headerEndCol}7")->getFont()->getColor()->setRGB('FFFFFF');
                // ---------- SUMMARY HEADER (rows 6-7): NO BACKGROUND COLOR ----------
                $summaryEndCol = $summaryEnd; // Last column of SUMMARY
                // Explicitly set NO fill for SUMMARY header (rows 6-7)
                $sheet->getStyle("{$summaryStart}6:{$summaryEndCol}6")->getFill()
                    ->setFillType(Fill::FILL_NONE);
                $sheet->getStyle("{$summaryStart}7:{$summaryEndCol}7")->getFill()
                    ->setFillType(Fill::FILL_NONE);
                // Black text for SUMMARY header (default, but explicit for clarity)
                $sheet->getStyle("{$summaryStart}6:{$summaryEndCol}7")->getFont()->getColor()->setRGB('000000');
                // ---------- SUMMARY DATA CELLS (rows 8+): NO BACKGROUND COLOR ----------
                $lastRow = 7 + $rowCount;
                $sheet->getStyle("{$summaryStart}8:{$summaryEndCol}{$lastRow}")
                    ->getFill()->setFillType(Fill::FILL_NONE);
                // Optional: Ensure no accidental fill from elsewhere
                $sheet->getStyle("{$summaryStart}6:{$summaryEndCol}{$lastRow}")
                    ->getFill()->setFillType(Fill::FILL_NONE);
                // ---------- FREEZE ----------
                $sheet->freezePane('E8');
                // ---------- MERGE EMPLOYEE ROWS ----------
                $lastRow = 7 + $rowCount;
                $curRow  = 8;
                while ($curRow <= $lastRow) {
                    $sheet->mergeCells("A{$curRow}:A" . ($curRow + 2));
                    $sheet->mergeCells("B{$curRow}:B" . ($curRow + 2));
                    $sheet->mergeCells("C{$curRow}:C" . ($curRow + 2));
                    foreach (array_keys($filterCols) as $c) {
                        $sheet->mergeCells("{$c}{$curRow}:{$c}" . ($curRow + 2));
                    }
                    $curRow += 3;
                }
                // ---------- ALIGNMENTS ----------
                $sheet->getStyle("A8:C{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                foreach (array_keys($filterCols) as $c) {
                    $sheet->getStyle("{$c}8:{$c}{$lastRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setWrapText(true);
                }
                $mainRange   = "A6:{$dayEndCol}{$lastRow}";
                $summaryRange = "{$summaryStart}6:{$summaryEnd}{$lastRow}";
                foreach ([$mainRange, $summaryRange] as $rng) {
                    $sheet->getStyle($rng)->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->getColor()->setRGB('D3D3D3');
                }
                $sheet->getStyle("D8:{$dayEndCol}{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("{$summaryStart}8:{$summaryEnd}{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("A8:{$summaryEnd}{$lastRow}")->getFont()->setSize(8);
                $sheet->getStyle("C8:C{$lastRow}")->getAlignment()->setWrapText(true);
                // ---------- ALTERNATING ROW COLORS ----------
                $curRow = 8;
                $empIdx = 1;
                while ($curRow <= $lastRow) {
                    $bg = ($empIdx++ % 2 == 1) ? 'F5F5F5' : 'FFFFFF';
                    $rng = "A{$curRow}:{$summaryEnd}" . ($curRow + 2);
                    $sheet->getStyle($rng)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB($bg);
                    $curRow += 3;
                }
                // ---------- ROW HEIGHTS ----------
                $sheet->getRowDimension(6)->setRowHeight(13);
                $sheet->getRowDimension(7)->setRowHeight(50);
                for ($r = 8; $r <= $lastRow; $r++) {
                    $sheet->getRowDimension($r)->setRowHeight(18);
                }
                // ---------- LEGEND ----------
                $legendRow = $lastRow + 2;
                $sheet->setCellValue("A{$legendRow}", 'Abbreviations');
                $sheet->mergeCells("A{$legendRow}:{$summaryEnd}{$legendRow}");
                $sheet->getStyle("A{$legendRow}")->getFont()->setSize(10)->setBold(true);
                $sheet->getStyle("A{$legendRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $abbr = [
                    'P'   => 'Present Days',
                    'ABS' => 'Absent Days',
                    'HP'  => 'Holiday Present',
                    'WO'  => 'Week-off Days',
                    'WOP' => 'Weekly Off Present',
                    'MSP' => 'Missed Punch',
                    'HD'  => 'Half Day',
                    'HO'  => 'Holidays',
                    'OD'  => 'Out Door Duty',
                    'CL'  => 'Casual Leave',
                    'PL'  => 'Privilege Leave',
                    'SL'  => 'Sick Leave',
                    'COFF' => 'Comp Off',
                    'EL'  => 'Earned Leave',
                    'ML'  => 'Maternity Leave',
                    'MRL' => 'Marriage Leave',
                    'BL'  => 'Bereavement Leave',
                    'Total Leave' => 'Sum of all leave types taken',
                    'Total Work Hrs' => 'Sum of all working hours in the month'
                ];
                $r = $legendRow + 1;
                foreach ($abbr as $k => $v) {
                    $sheet->setCellValue("A{$r}", "{$k}: {$v}");
                    $sheet->mergeCells("A{$r}:{$summaryEnd}{$r}");
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
