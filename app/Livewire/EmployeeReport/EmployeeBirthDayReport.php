<?php

namespace App\Livewire\EmployeeReport;

use App\Exports\Employee\EmployeeAdditionReport;
use App\Models\Business;
use App\Models\Dealership;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\MasterTable;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeBirthDayReport extends Component
{
    public $searchEmployee = '', $searchDepartment = '', $searchDesignation = '', $searchDealer = '';
    public $selectedEmployeeId = null, $employeeStatusId = 71, $selectedDepartmentId = null, $selectedDesignationId = null, $selectedDealerId = null;
    public $businessId = null;
    public $businessName = null;
    public $selectedFromDate = null;
    public $selectedToDate = null;
    public $slug = null;
    public $sortBy = 'emp_code';
       public $showFilterPanel = false;
    public function mount($slug = null)
    {
        $this->slug = $slug;
        // dd( $this->slug);
        $this->businessId    = Auth::user()->emp_b_id;
        $this->businessName  = Business::where('b_id', $this->businessId)->first()->b_name ?? '';
        $this->selectedFromDate = now()->toDateString();
        $this->selectedToDate   = now()->toDateString();
    }
    public $filters = [
        'department' => false,
        'designation' => false,
        'dealership' => false,
       
    ];
    public $filterFields = [
        'employee' => 'Employee',
        'department' => 'Department',
        'designation' => 'Designation',
        'dealership' => 'Dealership',
    ];

       public function resetFilters()
    {
        // Reset all selected values
        $this->selectedEmployeeId = null;
      
        $this->selectedDepartmentId = null;
       
        $this->selectedDealerId = null;
        
        $this->selectedDesignationId = null;
       
        $this->employeeStatusId = null;
        // Reset search fields
       
        $this->searchDepartment = '';
       
        $this->searchDealer = '';
       
        $this->searchDesignation = '';
       
        // Reset filters to default state
        $this->filters = [
        'department' => false,
        'designation' => false,
        'dealership' => false,
       
    ];
        
    }
    public function toggleFilterPanel()
    {
        $this->showFilterPanel = !$this->showFilterPanel;
    }
    public function selectEmployee($id, $name)
    {
        $this->selectedEmployeeId = $id;
        $this->searchEmployee = $name;
    }
    public function selectDepartment($id, $name)
    {
        $this->selectedDepartmentId = $id;
        $this->searchDepartment = $name;
    }
    public function selectDesignation($id, $name)
    {
        $this->selectedDesignationId = $id;
        $this->searchDesignation = $name;
    }
    public function selectDealer($id, $name)
    {
        $this->selectedDealerId = $id;
        $this->searchDealer = $name;
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
                'employee' => [
                    $this->selectedEmployeeId = null,
                    $this->searchEmployee = '',
                ],
                default => null,
            };
        }
    }

    public function updated($property, $value)
    {
        // Reset selectedEmployeeId and search only when employeeStatusFilter is updated
        if ($property === 'employeeStatusId') {
            $this->selectedEmployeeId = null;
            $this->searchEmployee = '';
            return;
        }
        // Generic mapping of search fields to selected fields
        $mapping = [
            'searchEmployee' => 'selectedEmployeeId',
            'searchDepartment' => 'selectedDepartmentId',
            'searchDesignation' => 'selectedDesignationId',
            'searchDealer' => 'selectedDealerId',
        ];
        // Apply the generic reset logic
        if (array_key_exists($property, $mapping)) {
            $this->{$mapping[$property]} = null;
        }
    }

    public function generateReport()
    {
        // Validation rules
        $this->validate([
            // From date can't be greater than to date
            'selectedFromDate' => 'required|date|before_or_equal:selectedToDate',
            'selectedToDate' => 'required|date',
            'selectedEmployeeId' => 'nullable|exists:employees,emp_id',
            'selectedDepartmentId' => 'nullable|exists:departments,d_id',
            'selectedDealerId' => 'nullable|exists:dealerships,dlr_id',
        ], [
            'selectedFromDate.required' => 'The from date field is required.',
            'selectedFromDate.date' => 'The from date must be a valid date.',
            'selectedToDate.required' => 'The to date field is required.',
            'selectedDate.date' => 'The date must be a valid date.',
            'selectedEmployeeId.exists' => 'The selected employee does not exist.',
            'selectedDepartmentId.exists' => 'The selected department does not exist.',
            'selectedDealerId.exists' => 'The selected dealer does not exist.',
        ]);
        if ($this->slug == 'employee-birthday') {
            $from = Carbon::parse($this->selectedFromDate)->format('m-d-y'); // e.g. 01-01
            $to   = Carbon::parse($this->selectedToDate)->format('m-d-y');   // e.g. 12-31

            // dd($from, $to);
            $query = Employee::with(['fh_department', 'fh_designation', 'fh_dealership'])
                ->where('emp_b_id', $this->businessId)
                ->whereRaw("DATE_FORMAT(emp_dob, '%m-%d') BETWEEN ? AND ?", [$from, $to])
                ->where('emp_role_id', '<>', 1)
                ->when($this->selectedEmployeeId, fn($q, $v) => $q->where('emp_id', $v))
                ->when($this->employeeStatusId, function ($q, $v) {
                    if ($v === 'resigned') {

                        $q->whereNotNull('emp_last_working_date');
                    } else {
                        $q->where('emp_status', $v);
                    }
                })
                ->orderByRaw("DATE_FORMAT(emp_dob, '%m-%d') ASC"); // Sort by month and day
        }
        if ($this->slug == 'employee-joining') {
            // dd($this->selectedFromDate,$this->selectedToDate);
            $query = Employee::with(['fh_department', 'fh_designation', 'fh_dealership'])
                ->where('emp_b_id', $this->businessId)
                ->whereBetween('emp_date_of_joining', [$this->selectedFromDate, $this->selectedToDate])
                ->where('emp_role_id', '<>', 1)
                ->when($this->selectedEmployeeId, fn($q, $v) => $q->where('emp_id', $v))
                ->when($this->employeeStatusId, function ($q, $v) {
                    if ($v === 'resigned') {
                        $q->whereNotNull('emp_last_working_date');
                    } else {
                        $q->where('emp_status', $v);
                    }
                })
                ->orderBy($this->sortBy === 'emp_name' ? 'emp_full_name' : 'emp_code', 'asc');
        }
        if ($this->selectedDepartmentId) {
            $query->whereHas('fh_department', fn($q) => $q->where('d_id', $this->selectedDepartmentId));
        }
        if ($this->selectedDealerId) {
            $query->whereHas('fh_dealership', fn($q) => $q->where('dlr_id', $this->selectedDealerId));
        }
        if ($this->selectedDesignationId) {
            $query->whereHas('fh_designation', fn($q) => $q->where('dg_id', $this->selectedDesignationId));
        }
        // Execute the query and get the records
        $records = $query->select('emp_id', 'emp_full_name', 'emp_code', 'emp_date_of_joining', 'emp_dob', 'emp_d_id', 'emp_dg_id', 'emp_dlr_id')->get();
        if ($records->isEmpty()) {
            $this->dispatch('show-alert', ['type' => 'error', 'message' => 'No records found for the selected criteria.']);
            return;
        }
        $fileName = $this->slug . '-report_' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new EmployeeAdditionReport($this->businessName, $records, $this->slug), $fileName);
    }
    public function render()
    {
        $employees = Employee::where('emp_role_id', '<>', 1)
            ->where('emp_b_id', $this->businessId)
            ->when($this->searchEmployee, function ($query) {
                $query->where(function ($q) {
                    $q->where('emp_full_name', 'like', '%' . $this->searchEmployee . '%')
                        ->orWhere('emp_code', 'like', '%' . $this->searchEmployee . '%');
                });
            })
            ->when($this->employeeStatusId, fn($q) => $q->where('emp_status', $this->employeeStatusId))
            ->limit(100)
            ->get();
        $employeeStatus = MasterTable::where('m_group', 'STATUS')
            ->limit(100)
            ->select('m_id', 'm_name')
            ->get();
        $departments = Department::where('d_b_id', $this->businessId)
            ->when(
                strlen($this->searchDepartment) >= 1 && !$this->selectedDepartmentId,
                fn($q) => $q->where('d_name', 'like', "%{$this->searchDepartment}%")
            )
            ->limit(100)
            ->get();
        $designations = Designation::where('dg_b_id', $this->businessId)
            ->when(
                strlen($this->searchDesignation) >= 1 && !$this->selectedDesignationId,
                fn($q) => $q->where('dg_name', 'like', "%{$this->searchDesignation}%")
            )
            ->limit(100)
            ->get();
        $dealers = Dealership::where('dlr_b_id', $this->businessId)
            ->when(
                strlen($this->searchDealer) >= 1 && !$this->selectedDealerId,
                fn($q) => $q->where('dlr_name', 'like', "%{$this->searchDealer}%")
            )
            ->limit(100)
            ->get();
        return view('livewire.employee-report.employee-birth-day-report', compact(
            'employees',
            'departments',
            'designations',
            'dealers',
            'employeeStatus',
        ));
    }
}
