<?php

namespace App\Livewire\LeaveCalendar;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\MasterTable;
use App\Http\Resources\MasterTableResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\PolicyHolidayList;
use App\Helpers\CentralLogics;


class LeaveCalendar extends Component
{
    // Employee Selection
    public $search = '';
    public $employees = [];
    public $selectedEmployeeId = null;
    public $employee = null;

    //attendance Data
    public $attendanceMap = [];

    // Calendar
    public $currentMonth;
    public $currentYear;

    // Data - Must be plain arrays only (Livewire requirement)
    public $leaveBalanceRaw = [];
    public $leaveCategories = [];
    public $leaveTypes = [];
    public $daySegments = [];
    public $leaveCat = [];
    public $tableRows = [];
    public $calendarDays = [];
    public $selectedLeave = null;
    public $uplLeave = null;

    // Modal
    public $showModal = false;
    public $lvr_leave_day_type_id = null;
    public $lvr_day_segment_id = null;
    public $lvr_cat_type_id = null;
    public $lvr_date = null;
    public $lvr_start_date = null;
    public $lvr_end_date = null;
    public $lvr_reason = null;

    // Flash
    public $flashMsg = null;
    public $flashType = 'success';

    public function mount(): void
    {
        $now = Carbon::now();
        $this->currentMonth = (int) $now->month;
        $this->currentYear  = (int) $now->year;

        $this->leaveTypes  = MasterTable::where('m_group', 'LIKE', '%LEAVE_TYPE%')->get()->toArray();
        $this->daySegments = MasterTable::where('m_group', 'LIKE', '%LEAVE_DAY_SEGMENT%')->get()->toArray();
        $this->leaveCat    = MasterTable::where('m_group', 'LIKE', '%LEAVE_CATEGORY%')->get()->toArray();
    }

    public function updatedSearch()
    {
        $this->employees = Employee::where('emp_role_id', '<>', 1)
            ->where('emp_b_id', Auth::user()->emp_b_id)
            ->where(function ($q) {
                $q->where('emp_full_name', 'like', "%{$this->search}%")
                    ->orWhere('emp_code', 'like', "%{$this->search}%");
            })
            ->limit(30)
            ->get();
    }

    public function selectEmployee($id)
    {
        $this->selectedEmployeeId = $id;
        $this->search             = '';
        $this->employees          = [];
        $this->selectedLeave      = null;
        $this->tableRows          = [];
        $this->calendarDays       = [];
        $this->loadEmployeeData();
    }

    // ── PUBLIC so the blade confirm-delete button can call $wire.deleteLeave()
    //    and so saveLeave / deleteLeave can call it to refresh everything
    public function loadEmployeeData(): void
    {
        $this->employee = Employee::with(['fh_department', 'fh_designation', 'fh_policy_leave'])
            ->where('emp_id', $this->selectedEmployeeId)
            ->where('emp_b_id', Auth::user()->emp_b_id)
            ->first();

        if (!$this->employee) {
            $this->flashMsg  = 'Employee not found or unauthorized.';
            $this->flashType = 'error';
            return;
        }

        // Refresh leave balance (resolves ResourceCollection → plain array)
        $this->leaveBalanceRaw = $this->getCleanLeaveBalance($this->selectedEmployeeId);

        // UPL Leave
        $isUpl = $this->employee->fh_policy_leave?->pl_upl_applicable ?? false;
        if ($isUpl) {
            $uplCollection  = MasterTableResource::collection(MasterTable::where('m_id', 215)->get());
            $this->uplLeave = $uplCollection->first()?->toArray(request()) ?? null;
        } else {
            $this->uplLeave = null;
        }

        $this->buildLeaveCategories();
        $this->refreshCalendarData();
    }

    /**
     * Resolve the API ResourceCollection into a plain PHP array safe for Livewire.
     */
    private function getCleanLeaveBalance(int $employeeId): array
    {
        $apiController = app()->make(\App\Http\Controllers\Api\Attendance\EmployeeLeaveController::class);
        $leaveBalance  = $apiController->getLeaveBalance($employeeId);

        // getLeaveBalance may return a JsonResponse or a plain array
        if ($leaveBalance instanceof \Illuminate\Http\JsonResponse) {
            $leaveBalance = $leaveBalance->getData(true);
        }

        $result = isset($leaveBalance['result'])
            ? json_decode(json_encode($leaveBalance['result']), true)
            : [];

        $clean = [];

        foreach ($result as $item) {
            $catDetail = $item['category_master_detail'][0] ?? [];

            $clean[] = [
                'category_id'     => $catDetail['id']   ?? null,
                'category_name'   => $catDetail['name'] ?? 'Unknown Leave',
                'category_type'   => $catDetail['type'] ?? '',
                'total_alloted'   => (float) ($item['total_alloted_leave']           ?? 0),
                'total_taken'     => (float) ($item['total_taken_leave']             ?? 0),
                'balance'         => (float) ($item['total_balance_remaining_leave'] ?? 0),
                'carried_forward' => (float) ($item['total_carried_forward']         ?? 0),
            ];
        }

        return $clean;
    }

    private function buildLeaveCategories(): void
    {
        $this->leaveCategories = [];

        foreach ($this->leaveBalanceRaw as $item) {
            $balance = $item['balance'] ?? 0;

            $this->leaveCategories[] = [
                'id'       => $item['category_id']   ?? null,
                'name'     => $item['category_name'] ?? 'Unknown Leave',
                'balance'  => $balance,
                'disabled' => $balance <= 0,
            ];
        }

        // Add UPL (Unpaid Leave) if policy allows it
        if ($this->uplLeave && is_array($this->uplLeave)) {
            $this->leaveCategories[] = [
                'id'       => $this->uplLeave['id']   ?? 215,
                'name'     => $this->uplLeave['name'] ?? 'Unpaid Leave (UPL)',
                'balance'  => null,   // unlimited
                'disabled' => false,
            ];
        }
    }

    public function refreshCalendarData(): void
    {
        if (!$this->selectedEmployeeId) return;

        $startDate = Carbon::create($this->currentYear, $this->currentMonth, 1)->startOfMonth();
        $endDate   = $startDate->copy()->endOfMonth();

        $this->attendanceData();

        $leaves = LeaveRequest::with(['fh_leave_cat_type', 'fh_approval_status'])
            ->where('lvr_emp_id', $this->selectedEmployeeId)
            ->where('lvr_b_id', Auth::user()->emp_b_id)
            ->where('lvr_stage_completed', 1)
            ->where('lvr_status', '!=', 170)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('lvr_start_date', [$startDate, $endDate])
                    ->orWhereBetween('lvr_end_date', [$startDate, $endDate]);
            })
            ->get();

        $this->tableRows = $leaves->map(function ($leave) {
            return [
                'lvr_id'     => $leave->lvr_id,
                'category'   => $leave->fh_leave_cat_type?->m_name ?? '',
                'from'       => Carbon::parse($leave->lvr_start_date)->format('d-M-y'),
                'to'         => Carbon::parse($leave->lvr_end_date)->format('d-M-y'),
                'days'       => $leave->lvr_total_leave_days,
                'status'     => $leave->fh_approval_status?->m_name ?? 'Approved',
                'reason'     => $leave->lvr_reason ?? '',
                'applied_on' => Carbon::parse($leave->created_at)->format('d-m-Y'),
                'from_raw'   => $leave->lvr_start_date,
            ];
        })->toArray();

        // Auto-select first leave if nothing is selected
        if (empty($this->selectedLeave) && count($this->tableRows) > 0) {
            $this->selectedLeave = $this->tableRows[0];
        }

        // If the currently selected leave is no longer in this month's list, clear it
        if ($this->selectedLeave) {
            $stillExists = collect($this->tableRows)
                ->contains('lvr_id', $this->selectedLeave['lvr_id'] ?? null);

            if (!$stillExists) {
                $this->selectedLeave = count($this->tableRows) > 0 ? $this->tableRows[0] : null;
            }
        }

        $this->buildCalendarGrid($leaves, $startDate);
        $this->attendanceData();
    }

    private function buildCalendarGrid($leaves, $startDate): void
    {
        $daysInMonth  = $startDate->daysInMonth;
        $startWeekDay = $startDate->dayOfWeek;

        $dayMap = [];
        foreach ($leaves as $leave) {
            $s = Carbon::parse($leave->lvr_start_date);
            $e = Carbon::parse($leave->lvr_end_date);
            while ($s->lte($e)) {
                $dateStr = $s->toDateString();
                if (!isset($dayMap[$dateStr])) {
                    $dayMap[$dateStr] = ['leaves' => []];
                }
                $dayMap[$dateStr]['leaves'][] = [
                    'id'          => $leave->lvr_id,
                    'abbr'        => $leave->fh_leave_cat_type?->m_type ?? 'L',
                    'cat'         => $leave->fh_leave_cat_type?->m_name ?? 'Leave',
                    'status'      => $leave->fh_approval_status?->m_name ?? '',
                    'is_full_day' => $leave->lvr_total_leave_days != 0.5,
                    'segment_id'  => $leave->lvr_day_segment_id,   // ← add
                    'segment'     => $leave->fh_leave_day_segment?->m_name ?? null, // ← add (needs relation)
                ];
                $s->addDay();
            }
        }

        $weeks = [];
        $week  = [];

        for ($i = 0; $i < $startWeekDay; $i++) $week[] = null;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($this->currentYear, $this->currentMonth, $day)->toDateString();
            $data = $dayMap[$date] ?? ['leaves' => []];
            $atd  = $this->attendanceMap[$date] ?? null; // ← merge attendance

            $week[] = [
                'day'        => $day,
                'date'       => $date,
                'is_today'   => $date === Carbon::today()->toDateString(),
                'leaves'     => $data['leaves'],
                'attendance' => $atd, // ← pass to blade
            ];

            if (count($week) === 7) {
                $weeks[] = $week;
                $week    = [];
            }
        }

        if (count($week) > 0) {
            while (count($week) < 7) $week[] = null;
            $weeks[] = $week;
        }

        $this->calendarDays = $weeks;
    }

    public function selectLeaveFromCalendar($leaveId): void
    {
        foreach ($this->tableRows as $row) {
            if (($row['lvr_id'] ?? null) == $leaveId) {
                $this->selectedLeave = $row;
                return;
            }
        }
    }

    public function selectLeave($index): void
    {
        $this->selectedLeave = $this->tableRows[$index] ?? null;
    }

    // ── Navigation ────────────────────────────────────────────────────

    public function prevMonth(): void
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth  = $date->month;
        $this->currentYear   = $date->year;
        $this->selectedLeave = null;
        $this->refreshCalendarData();
    }

    public function nextMonth(): void
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth  = $date->month;
        $this->currentYear   = $date->year;
        $this->selectedLeave = null;
        $this->refreshCalendarData();
    }

    public function goToToday(): void
    {
        $this->currentMonth  = Carbon::now()->month;
        $this->currentYear   = Carbon::now()->year;
        $this->selectedLeave = null;
        $this->refreshCalendarData();
    }

    // ── Modal ─────────────────────────────────────────────────────────

    // public function openAddLeave($date = null): void
    // {
    //     $this->resetLeaveForm();

    //     if ($date) {
    //         // ── Guard: block leave on non-applicable attendance days ──
    //         $atd = $this->attendanceMap[$date] ?? null;
    //         if ($atd) {
    //             $code = strtolower($atd['status_code'] ?? '');
    //             $blocked = ['p', 'pre', 'present', 'h', 'hol', 'holiday', 'wo', 'wof', 'weekoff'];
    //             if (in_array($code, $blocked)) {
    //                 $this->dispatch('show-alert', [
    //                     'type'    => 'warning',
    //                     'message' => 'Cannot apply leave on ' . $atd['status'] . ' day (' . \Carbon\Carbon::parse($date)->format('d M Y') . ').',
    //                 ]);
    //                 return; // ← modal never opens
    //             }
    //         }

    //         $this->lvr_date       = $date;
    //         $this->lvr_start_date = $date;
    //         $this->lvr_end_date   = $date;
    //     }

    //     $this->showModal = true;
    // }


    public function openAddLeave($date = null): void
{
    $this->resetLeaveForm();

    if ($date) {
        // ── Guard: block on non-applicable attendance days ──
        $atd = $this->attendanceMap[$date] ?? null;
        if ($atd) {
            $code    = strtolower($atd['status_code'] ?? '');
            $blocked = ['p', 'pre', 'present', 'h', 'hol', 'holiday', 'wo', 'wof', 'weekoff'];
            if (in_array($code, $blocked)) {
                $this->dispatch('show-alert', [
                    'type'    => 'warning',
                    'message' => 'Cannot apply leave on ' . $atd['status'] . ' day (' . \Carbon\Carbon::parse($date)->format('d M Y') . ').',
                ]);
                return;
            }
        }

        // ── Fetch all active leaves covering this date ──
        $leavesOnDate = LeaveRequest::with(['fh_leave_cat_type', 'fh_leave_day_segment'])
            ->where('lvr_emp_id', $this->selectedEmployeeId)
            ->where('lvr_b_id', Auth::user()->emp_b_id)
            ->where('lvr_stage_completed', 1)
            ->where('lvr_status', '!=', 170)
            ->where('lvr_start_date', '<=', $date)
            ->where('lvr_end_date', '>=', $date)
            ->get();

        if ($leavesOnDate->isNotEmpty()) {

            // ── Block: full-day leave already exists ──
            $fullDayLeave = $leavesOnDate->first(function ($leave) use ($date) {
                $start = \Carbon\Carbon::parse($leave->lvr_start_date)->toDateString();
                $end   = \Carbon\Carbon::parse($leave->lvr_end_date)->toDateString();
                return ($start === $date && $end === $date && $leave->lvr_total_leave_days != 0.5);
            });

            if ($fullDayLeave) {
                $catName = $fullDayLeave->fh_leave_cat_type?->m_name ?? 'Leave';
                $this->dispatch('show-alert', [
                    'type'    => 'warning',
                    'message' => 'A full-day ' . $catName . ' is already applied on '
                               . \Carbon\Carbon::parse($date)->format('d M Y')
                               . '. Delete it first to apply a new leave.',
                ]);
                return;
            }

            // ── Collect existing half-day leaves on this exact date ──
            $halfDayLeaves = $leavesOnDate->filter(function ($leave) use ($date) {
                $start = \Carbon\Carbon::parse($leave->lvr_start_date)->toDateString();
                $end   = \Carbon\Carbon::parse($leave->lvr_end_date)->toDateString();
                return ($start === $date && $end === $date && $leave->lvr_total_leave_days == 0.5);
            });

            // ── Block: both half-day slots already used ──
            if ($halfDayLeaves->count() >= 2) {
                $segments = $halfDayLeaves->map(function ($leave) {
                    $seg = $leave->fh_leave_day_segment?->m_name ?? null;
                    $cat = $leave->fh_leave_cat_type?->m_name ?? 'Leave';
                    return $seg ? "{$seg} ({$cat})" : $cat;
                })->implode(' & ');

                $this->dispatch('show-alert', [
                    'type'    => 'warning',
                    'message' => 'Both half-day slots are already used on '
                               . \Carbon\Carbon::parse($date)->format('d M Y')
                               . ': ' . $segments . '.',
                ]);
                return;
            }

            // ── One half-day exists: inform user and pre-select remaining segment ──
            if ($halfDayLeaves->count() === 1) {
                $existing        = $halfDayLeaves->first();
                $takenSegmentId  = $existing->lvr_day_segment_id;
                $takenSegName    = $existing->fh_leave_day_segment?->m_name ?? 'a half-day slot';
                $takenCatName    = $existing->fh_leave_cat_type?->m_name ?? 'Leave';

                // Find the other segment from master table
                $otherSegment = collect($this->daySegments)->first(function ($seg) use ($takenSegmentId) {
                    $segId = is_array($seg) ? ($seg['m_id'] ?? null) : ($seg->m_id ?? null);
                    return $segId && $segId != $takenSegmentId;
                });

                // Pre-select the remaining segment in the modal
                if ($otherSegment) {
                    $this->lvr_day_segment_id = is_array($otherSegment)
                        ? ($otherSegment['m_id'] ?? null)
                        : ($otherSegment->m_id ?? null);

                    $remainingSegName = is_array($otherSegment)
                        ? ($otherSegment['m_name'] ?? 'remaining half')
                        : ($otherSegment->m_name ?? 'remaining half');
                } else {
                    $remainingSegName = 'remaining half';
                }

                $this->dispatch('show-alert', [
                    'type'    => 'info',
                    'message' => \Carbon\Carbon::parse($date)->format('d M Y')
                               . ': ' . $takenSegName . ' already taken as ' . $takenCatName . '. '
                               . 'Applying for ' . $remainingSegName . '.',
                ]);
            }
        }

        $this->lvr_date       = $date;
        $this->lvr_start_date = $date;
        $this->lvr_end_date   = $date;
    }

    $this->showModal = true;
}
    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetLeaveForm();
    }

    private function resetLeaveForm(): void
    {
        $this->lvr_leave_day_type_id = null;
        $this->lvr_day_segment_id    = null;
        $this->lvr_cat_type_id       = null;
        $this->lvr_date              = null;
        $this->lvr_start_date        = null;
        $this->lvr_end_date          = null;
        $this->lvr_reason            = null;
        $this->resetErrorBag();
    }

    // ── Save ──────────────────────────────────────────────────────────

    public function saveLeave(): void
    {
        $this->validate([
            'lvr_leave_day_type_id' => 'required|integer',
            'lvr_cat_type_id'       => 'required|integer',
        ]);

        $start = $this->lvr_start_date ?? $this->lvr_date;
        $end   = $this->lvr_end_date   ?? $this->lvr_date;

        if (!$start) {
            $this->addError('date', 'Please select a start date.');
            return;
        }

        $data = [
            'emp_id'               => $this->selectedEmployeeId,
            'leave_day_type_id'    => $this->lvr_leave_day_type_id,
            'leave_day_segment_id' => $this->lvr_day_segment_id,
            'leave_category_id'    => $this->lvr_cat_type_id,
            'leave_start_date'     => Carbon::parse($start)->format('d M, Y'),
            'leave_end_date'       => Carbon::parse($end)->format('d M, Y'),
            'reason'               => $this->lvr_reason ?? '',
            'lvr_approved_by'      => Auth::user()->emp_id,
            'lvr_status'           => 171,
            'lvr_module_id'        => 250,
            'lvr_stage_completed'  => 1,
        ];

        try {
            $request  = new \Illuminate\Http\Request($data);
            $api      = app(\App\Http\Controllers\Api\Attendance\EmployeeLeaveController::class);
            $response = $api->store($request);

            $body = $response instanceof \Illuminate\Http\JsonResponse
                ? $response->getData(true)
                : (is_array($response) ? $response : []);

            if (!empty($body['status']) || !empty($body['success'])) {
                $this->closeModal();
                $this->loadEmployeeData();

                // ✅ Dispatch toast event
                $this->dispatch('show-alert', [
                    'type'    => 'success',
                    'message' => 'Leave applied successfully!',
                ]);
            } else {
                $this->dispatch('show-alert', [
                    'type'    => 'error',
                    'message' => $body['message'] ?? 'Failed to save leave.',
                ]);
            }
        } catch (\Exception $e) {
            $this->dispatch('show-alert', [
                'type'    => 'error',
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }

    // ── Delete ────────────────────────────────────────────────────────
    // Called by the Alpine confirm dialog: @click="$wire.deleteLeave(confirmId)"

    public function deleteLeave($id): void
    {
        try {
            $api = app(\App\Http\Controllers\Api\Attendance\EmployeeLeaveController::class);
            $api->destroy($id);

            if ($this->selectedLeave && ($this->selectedLeave['lvr_id'] ?? null) == $id) {
                $this->selectedLeave = null;
            }

            // loadEmployeeData re-fetches employee + calls refreshCalendarData
            // refreshCalendarData now calls attendanceData() first, so calendar
            // will show updated attendance (absent days reappear after leave deleted)
            $this->loadEmployeeData();

            $this->dispatch('show-alert', [
                'type'    => 'success',
                'message' => 'Leave deleted successfully!',
            ]);
        } catch (\Exception $e) {
            $this->dispatch('show-alert', [
                'type'    => 'error',
                'message' => 'Failed to delete leave: ' . $e->getMessage(),
            ]);
        }
    }


    public function attendanceData(): void
    {
        if (!$this->employee) return;

        $weekOfDates = CentralLogics::getWeekOffDates(
            $this->employee,
            $this->currentYear,
            $this->currentMonth
        );

        $startDate = Carbon::createFromFormat('Y-m', $this->currentYear . '-' . $this->currentMonth)
            ->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $this->currentYear . '-' . $this->currentMonth)
            ->endOfMonth();

        $holiday_record_exits = PolicyHolidayList::where('phl_b_id', $this->employee->emp_b_id)
            ->where('phl_day_type_id', 201)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('phl_start_date', [$startDate, $endDate])
                    ->orWhereBetween('phl_end_date', [$startDate, $endDate]);
            })->get();

        $holidaysByDate = collect();
        foreach ($holiday_record_exits as $holiday) {
            $start = Carbon::parse($holiday->phl_start_date);
            $end   = Carbon::parse($holiday->phl_end_date);
            while ($start->lte($end)) {
                $holidaysByDate->put($start->toDateString(), $holiday);
                $start->addDay();
            }
        }

        $raw = CentralLogics::newGetMonthlyAttendanceDetails(
            $this->employee,
            $this->currentMonth,
            $this->currentYear,
            $holidaysByDate,
            $weekOfDates
        );

        // Key by date for O(1) look-up in buildCalendarGrid
        $map = [];
        foreach ($raw as $record) {
            if (!empty($record['date'])) {
                $map[$record['date']] = [
                    'status'       => $record['status']       ?? '',
                    'status_code'  => $record['status_code']  ?? '',
                    'statusColor'  => $record['statusColor']  ?? '#94a3b8',
                    'checkInTime'  => $record['checkInTime']  ?? '',
                    'checkOutTime' => $record['checkOutTime'] ?? '',
                    'workingHour'  => $record['workingHour']  ?? '',
                    'late'         => $record['late']         ?? null,
                    'earlyExit'    => $record['earlyExit']    ?? null,
                ];
            }
        }
        $this->attendanceMap = $map;
    }


    public function render()
    {
        return view('livewire.leave-calendar.leave-calendar');
    }
}
