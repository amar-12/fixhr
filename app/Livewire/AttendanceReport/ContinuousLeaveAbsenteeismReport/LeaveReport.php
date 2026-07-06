<?php

namespace App\Livewire\AttendanceReport\ContinuousLeaveAbsenteeismReport;

use App\Exports\Attendance\BalanceLeaveSummery;
use App\Exports\Attendance\ContinuousLeaveReport;
use App\Exports\Attendance\DailyAttendanceLeaveReport as DailyLeaveReport;
use App\Exports\Attendance\LeaveBalanceReport;
use App\Exports\Attendance\LeaveBasicReport;
use App\Exports\Attendance\LeaveSummaryReport;
use App\Models\AttendanceRecord;
use App\Models\Branch;
use App\Models\Business;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use App\Models\Department;
use App\Models\PolicyShiftTiming;
use App\Models\MasterTable;
use App\Models\Dealership;
use App\Models\Designation;
use App\Models\LeaveRequest;
// use App\Models\Grade;
// use App\Models\WorkLocation;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class LeaveReport extends Component
{
    public $search = '',
        $searchLeaveType = '',
        $searchLeaveSegment = '',
        $searchApprovalStatus = '',
        $searchLeaveCategory = '',
        $searchDepartment = '',
        $searchDealer = '',
        $searchDesignation = '';
    public $selectedEmployeeId,
        $selectedAttendanceStatusId,
        $selectedDepartmentId,
        $selectedLeaveTypeId,
        $selectedLeaveSegmentId,
        $selectedLeaveCategoryId,
        $selectedApprovalStatusId,
        $selectedDealerId,
        $selectedDesignationId;
    public $businessId = '';
    //  static fields 
    public $selectedFromDate;
    public $selectedToDate;
    public $employeeStatusFilter = 'all';
    public $slug;
    public $showFilterPanel = false;
    public function mount()
    {
        $this->businessId = Auth::user()->emp_b_id;
        $this->selectedFromDate = Carbon::now()->toDateString();
        $this->selectedToDate = Carbon::parse($this->selectedFromDate)->addDay()->toDateString();
        // $this->slug = $slug;
    }
    public $filters = [
        'department' => false,
        'shift' => false,
        'designation' => false,
        'dealership' => false,
    ];

     public function toggleFilterPanel()
    {
        $this->showFilterPanel = !$this->showFilterPanel;
    }
    public function resetFilters()
    {
        $this->selectedEmployeeId        = null;
        $this->selectedDepartmentId      = null;
        $this->selectedDesignationId     = null;
        $this->selectedDealerId          = null;
        $this->selectedAttendanceStatusId = null;
        $this->search                = '';
        $this->searchDepartment      = '';
        $this->searchDesignation     = '';
        $this->searchDealer          = '';
        // Reset filters to defaults — adjust as needed
        $this->filters = [
            'department' => false,
            'shift' => false,
            'designation' => false,
            'dealership' => false,
        ];
    }
    public function toggleFilter($key, $state)
    {
        if (!array_key_exists($key, $this->filters)) return;
        $this->filters[$key] = filter_var($state, FILTER_VALIDATE_BOOLEAN);
        if (!$this->filters[$key]) {
            match ($key) {
                'department' => [
                    $this->selectedDepartmentId = null,
                    $this->searchDepartment = '',
                ],
                'designation' => [
                    $this->selectedDesignationId = null,
                    $this->searchDesignation = '',
                ],
                'dealership' => [
                    $this->selectedDealerId = null,
                    $this->searchDealer = '',
                ],
                default => null,
            };
        }
    }
    public function selectEmployee($id, $name)
    {
        $this->selectedEmployeeId = $id;
        $this->search = $name;
    }
    public function selectDepartment($id, $name)
    {
        $this->selectedDepartmentId = $id;
        $this->searchDepartment = $name;
    }
    public function selectLeaveType($id, $name)
    {
        $this->selectedLeaveTypeId = $id;
        $this->searchLeaveType = $name;
    }
    public function selectLeaveSegment($id, $name)
    {
        $this->selectedLeaveSegmentId = $id;
        $this->searchLeaveSegment = $name;
    }
    public function selectLeaveCategory($id, $name)
    {
        $this->selectedLeaveCategoryId = $id;
        $this->searchLeaveCategory = $name;
    }
    public function selectApprovalStatus($id, $name)
    {
        $this->selectedApprovalStatusId = $id;
        $this->searchApprovalStatus = $name;
    }
    public function selectDealer($id, $name)
    {
        $this->selectedDealerId = $id;
        $this->searchDealer = $name;
    }
    public function selectDesignation($id, $name)
    {
        $this->selectedDesignationId = $id;
        $this->searchDesignation = $name;
    }
    public function updated($property, $value)
    {
        if ($property === 'employeeStatusFilter') {
            $this->selectedEmployeeId = null;
            $this->search = '';
            return;
        }
        $mapping = [
            'search' => 'selectedEmployeeId',
            'searchDepartment' => 'selectedDepartmentId',
            'searchDesignation' => 'selectedDesignationId',
            'searchDealer' => 'selectedDealerId',
            'searchLeaveType' => 'selectedLeaveTypeId',
            'searchLeaveSegment' => 'selectedLeaveSegmentId',
            'searchLeaveCategory' => 'selectedLeaveCategoryId',
            'searchApprovalStatus' => 'selectedApprovalStatusId',
        ];
        // Apply the generic reset logic
        if (array_key_exists($property, $mapping)) {
            $this->{$mapping[$property]} = null;
        }
    }
    public function generateReport()
    {
        $this->validate([
            'selectedFromDate' => 'required|date',
            'selectedToDate'   => 'required|date|after:selectedFromDate',
            'selectedEmployeeId' => 'nullable|exists:employees,emp_id',
            'selectedLeaveTypeId' => 'nullable|exists:master_table,m_id',
            'selectedLeaveSegmentId' => 'nullable|exists:master_table,m_id',
            'selectedLeaveCategoryId' => 'nullable|exists:master_table,m_id',
            'selectedDepartmentId' => 'nullable|exists:departments,d_id',
            'selectedDealerId' => 'nullable|exists:dealerships,dlr_id',
            'selectedDesignationId' => 'nullable|exists:designations,dg_id',
        ], [
            'selectedFromDate.required' => 'Please select From Date.',
            'selectedToDate.required'   => 'Please select To Date.',
            'selectedToDate.after'      => 'To Date must be at least 1 day after From Date.',
            'selectedEmployeeId.exists' => 'The selected employee does not exist.',
            'selectedLeaveTypeId.exists' => 'The selected leave type does not exist.',
            'selectedLeaveSegmentId.exists' => 'The selected leave segment does not exist.',
            'selectedLeaveCategoryId.exists' => 'The selected leave category does not exist.',
            'selectedDealerId.exists' => 'The selected dealer does not exist.',
            'selectedDesignationId.exists' => 'The selected designation does not exist.',
        ]);
        $fromDate = Carbon::parse($this->selectedFromDate);
        $toDate = Carbon::parse($this->selectedToDate)->endOfDay();
        $fromYear = $fromDate->year;
        $fromMonth = $fromDate->month;
        $toYear = $toDate->year;
        $toMonth = $toDate->month;
        $query = LeaveRequest::with([
            'fh_employees_details' => function ($q) use ($fromYear, $fromMonth, $toYear, $toMonth) {
                $q->with([
                    'fh_department',
                    'fh_designation',
                    'fh_dealership',
                ]);
            },
            'fh_approver',
            'fh_business',
            'fh_leave_cat_type',
            'fh_leave_day_type',
            'fh_leave_day_segment',
            'fh_approval_status'
        ])
            ->where('lvr_b_id', $this->businessId)
            ->whereBetween('lvr_start_date', [$fromDate, $toDate]);
        // Apply filters
        if ($this->selectedDepartmentId) {
            $query->whereHas('fh_employees_details', function ($q) {
                $q->where('emp_d_id', $this->selectedDepartmentId);
            });
        }
        if ($this->selectedEmployeeId) {
            $query->where('lvr_emp_id', $this->selectedEmployeeId);
        }
        if ($this->selectedLeaveTypeId) {
            $query->where('lvr_leave_day_type_id', $this->selectedLeaveTypeId);
        }
        if ($this->selectedLeaveSegmentId) {
            $query->where('lvr_day_segment_id', $this->selectedLeaveSegmentId);
        }
        if ($this->selectedLeaveCategoryId) {
            $query->where('lvr_cat_type_id', $this->selectedLeaveCategoryId);
        }
        if ($this->selectedApprovalStatusId) {
            $query->where('lvr_status', $this->selectedApprovalStatusId);
        }
        if ($this->selectedDealerId) {
            $query->whereHas('fh_employees_details', function ($q) {
                $q->where('emp_dlr_id', $this->selectedDealerId);
            });
        }
        if ($this->selectedDesignationId) {
            $query->whereHas('fh_employees_details', function ($q) {
                $q->where('emp_dg_id', $this->selectedDesignationId);
            });
        }
        $records = $query->get();
        if ($records->isEmpty()) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'No records found for the selected criteria.']);
            return;
        }
        $finalData = [];
        $serial = 1;
        foreach ($records as $record) {
            $startDate = Carbon::parse($record['lvr_start_date']);
            $endDate = Carbon::parse($record['lvr_end_date']);
            if (!$startDate || !$endDate || $endDate->lessThan($startDate)) {
                continue;
            }
            $totalDays = $startDate->diffInDays($endDate) + 1;
            if ($totalDays <= 1) {
                continue; // Skip single-day leaves
            }
            $leaveDates = [];
            for ($i = 0; $i < $totalDays; $i++) {
                $leaveDates[] = $startDate->copy()->addDays($i)->format('d-M-Y');
            }
            $leaveDatesStr = implode("\n", array_map(function ($date) {
                return $date . ',';
            }, $leaveDates));
            $totalLeaveDays = (float) ($record['lvr_total_leave_days'] ?? 0);
            $empCode = $record['fh_employees_details']['emp_code'] ?? '-';
            $empName = $record['fh_employees_details']['emp_full_name'] ?? '-';
            $leaveType = $record['fh_leave_day_type']['m_name'] ?? '';
            $leaveSegment = $record['fh_leave_day_segment']['m_name'] ?? '-';
            $leaveCategory = $record['fh_leave_cat_type']['m_name'] ?? '-';
            $approvalStatus = $record['fh_approval_status']['m_name'] ?? '-';
            $approverName = $record['fh_approver']['emp_full_name'] ?? '-';
            $finalData[] = [
                'S.No'              => $serial++,
                'Emp Code'          => $empCode,
                'Employee Name'     => $empName,
                'Department'        => $record['fh_employees_details']['fh_department']['d_name'] ?? '-',
                'Designation'       => $record['fh_employees_details']['fh_designation']['dg_name'] ?? '-',
                'Dealership'        => $record['fh_employees_details']['fh_dealership']['dlr_name'] ?? '-',
                'Leave Type'        => $leaveType,
                'Leave Segment'     => $leaveSegment,
                'Leave Category'    => $leaveCategory,
                'Leave Reason'      => $record['lvr_reason'],
                'Leave Start Date'  => $startDate->format('d-M-Y'),
                'Leave End Date'    => $endDate->format('d-M-Y'),
                'Leave Dates'       => $leaveDatesStr,
                'Leave Count'       => number_format($totalLeaveDays, 2),
                'Approval Status'   => $approvalStatus,
                'Applied Date'      => Carbon::parse($record['created_at'])->format('d-M-Y'),
                'Approver Name'     => $approverName,
            ];
        }
        if (empty($finalData)) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'No records found for the selected criteria.']);
            return;
        }
        $businessName = Business::where('b_id', $this->businessId)->first()->b_name;
        $fileName = 'LeaveReport_' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new ContinuousLeaveReport($finalData, $this->filters, $fromDate, $toDate, $businessName), $fileName);
    }
    public function render()
    {
        $employees = Employee::where('emp_role_id', '<>', 1)
            ->where('emp_b_id', $this->businessId)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('emp_full_name', 'like', '%' . $this->search . '%')
                        ->orWhere('emp_code', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->employeeStatusFilter === 'active', fn($q) => $q->where('emp_status', 71))
            ->when($this->employeeStatusFilter === 'inactive', fn($q) => $q->where('emp_status', '<>', 71))
            ->limit(100)
            ->get();
        $departments = Department::where('d_b_id', $this->businessId)
            ->when(
                strlen($this->searchDepartment) >= 1 && !$this->selectedDepartmentId,
                fn($q) =>
                $q->where('d_name', 'like', "%{$this->searchDepartment}%")
            )
            ->limit(100)
            ->get();
        $designations = Designation::where('dg_b_id', $this->businessId)
            ->when(
                strlen($this->searchDesignation) >= 1 && !$this->selectedDesignationId,
                fn($q) =>
                $q->where('dg_name', 'like', "%{$this->searchDesignation}%")
            )
            ->limit(100)
            ->get();
        $dealers = Dealership::where('dlr_b_id', $this->businessId)
            ->when(
                strlen($this->searchDealer) >= 1 && !$this->selectedDealerId,
                fn($q) =>
                $q->where('dlr_name', 'like', "%{$this->searchDealer}%")
            )
            ->limit(100)
            ->get();
        $leaveTypes = MasterTable::where('m_group', 'Leave_Type')
            ->when(
                strlen($this->searchLeaveType) >= 1 && !$this->selectedLeaveTypeId,
                fn($q) =>
                $q->where('m_name', 'like', "%{$this->searchLeaveType}%")
            )
            ->limit(100)
            ->get();
        $leaveSegments = MasterTable::where('m_group', 'LEAVE_DAY_SEGMENT')
            ->when(
                strlen($this->searchLeaveSegment) >= 1 && !$this->selectedLeaveSegmentId,
                fn($q) =>
                $q->where('m_name', 'like', "%{$this->searchLeaveSegment}%")
            )
            ->limit(100)
            ->get();
        $leaveCategories = MasterTable::where('m_group', 'LEAVE_CATEGORY')
            ->when(
                strlen($this->searchLeaveCategory) >= 1 && !$this->selectedLeaveCategoryId,
                fn($q) =>
                $q->where('m_name', 'like', "%{$this->searchLeaveCategory}%")
            )
            ->limit(100)
            ->get();
        $approvalStatus = MasterTable::where('m_group', 'APPROVAL_STATUS')->whereIn('m_id', [140, 157, 170])
            ->when(
                strlen($this->searchApprovalStatus) >= 1 && !$this->selectedApprovalStatusId,
                fn($q) =>
                $q->where('m_name', 'like', "%{$this->searchApprovalStatus}%")
            )
            ->limit(100)
            ->get();
        return view('livewire.attendance-report.continuous-leave-absenteeism-report.leave-report', compact(
            'employees',
            'departments',
            'designations',
            'dealers',
            'leaveTypes',
            'leaveSegments',
            'leaveCategories',
            'approvalStatus'
        ));
    }
}
