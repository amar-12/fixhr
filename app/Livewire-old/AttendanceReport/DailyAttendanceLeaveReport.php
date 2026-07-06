<?php
namespace App\Livewire\AttendanceReport;
use App\Exports\Attendance\BalanceLeaveSummery;
use App\Exports\Attendance\DailyAttendanceLeaveReport as DailyLeaveReport;
use App\Exports\Attendance\LeaveBalanceReport;
use App\Exports\Attendance\LeaveBasicReport;
use App\Exports\Attendance\LeaveSummaryReport;
use App\Models\AttendanceRecord;
use App\Models\Branch;
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
class DailyAttendanceLeaveReport extends Component
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
    public function mount($slug)
    {
        $this->businessId = Auth::user()->emp_b_id;
        $this->selectedFromDate = Carbon::now()->toDateString(); // Format: YYYY-MM-DD
        $this->selectedToDate = Carbon::now()->toDateString(); // Format: YYYY-MM-DD
        $this->slug = $slug;
    }
    public $filters = [
        'department' => false,
        'designation' => false,
        'dealership' => false,
    ];
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
            'selectedToDate' => 'required|date|after_or_equal:selectedFromDate',
            'selectedEmployeeId' => 'nullable|exists:employees,emp_id',
            'selectedLeaveTypeId' => 'nullable|exists:master_table,m_id',
            'selectedLeaveSegmentId' => 'nullable|exists:master_table,m_id',
            'selectedLeaveCategoryId' => 'nullable|exists:master_table,m_id',
            'selectedDepartmentId' => 'nullable|exists:departments,d_id',
            'selectedDealerId' => 'nullable|exists:dealerships,dlr_id',
            'selectedDesignationId' => 'nullable|exists:designations,dg_id',
        ], [
            'selectedFromDate.required' => 'The from date is required.',
            'selectedToDate.required' => 'The to date is required.',
            'selectedToDate.after_or_equal' => 'The to date must be a date after or equal to the from date.',
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
                    'leaveBalances' => function ($q2) use ($fromYear, $fromMonth, $toYear, $toMonth) {
                        $q2->where(function ($query) use ($fromYear, $fromMonth, $toYear, $toMonth) {
                            $query->where(function ($q) use ($fromYear, $fromMonth, $toYear, $toMonth) {
                                $q->where('lb_year', $fromYear)
                                    ->where('lb_month', '>=', $fromMonth);
                            });
                            if ($fromYear != $toYear) {
                                $query->orWhere(function ($q) use ($fromYear, $toYear, $toMonth) {
                                    $q->where('lb_year', $toYear)
                                        ->where('lb_month', '<=', $toMonth);
                                });
                                $query->orWhere(function ($q) use ($fromYear, $toYear) {
                                    $q->whereBetween('lb_year', [$fromYear + 1, $toYear - 1]);
                                });
                            } else {
                                $query->where(function ($q) use ($fromYear, $fromMonth, $toMonth) {
                                    $q->where('lb_year', $fromYear)
                                        ->whereBetween('lb_month', [$fromMonth, $toMonth]);
                                });
                            }
                        });
                    }
                ]);
            },
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
        //    dd($records->toArray());
        if ($records->isEmpty()) {
            session()->flash('error', 'No records found for the selected criteria.');
            return;
        }
        $fileName = 'LeaveReport_' . now()->format('Y-m-d') . '.xlsx';
        if ($this->slug == 'summery') {
            return Excel::download(new LeaveSummaryReport($records, $this->filters, $fromDate, $toDate), $fileName);
        } else if ($this->slug == 'deatails') {
            return Excel::download(new DailyLeaveReport($records, $this->filters, $fromDate, $toDate), $fileName);
        } else if ($this->slug == 'balance') {
            return Excel::download(new LeaveBalanceReport($records, $this->filters, $fromDate, $toDate), $fileName);
        }
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
        return view('livewire.attendance-report.daily-attendance-leave-report', compact(
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
