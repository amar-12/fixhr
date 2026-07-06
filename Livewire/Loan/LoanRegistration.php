<?php

namespace App\Livewire\Loan;

use App\Exports\Loan\LoanRegisterExport;
use App\Exports\Loan\LoanProjectionExport;
use App\Models\Branch;
use App\Models\Dealership;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\FinancialYear;
use App\Models\Grade;
use App\Models\LoanRequest;
use App\Models\MasterTable;
use App\Models\PayrollLoanInstallment;
use App\Models\PolicyShiftTiming;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class LoanRegistration extends Component
{
    public $slug;
    public $searchEmployee = '';
    public $selectedEmployeeId;
    public $businessId = '';
    public $selectedFromDate = null;
    public $selectedToDate = null;
    public $employeeStatusId = null;
    public $businessName = '';

    public $selectedYear;
    public $selectedMonth;

    public $financialYears;
    public $fyStartDate = '';
    public $fyEndDate = '';
    public $months;
    public $year;
    public $approvalStatusId = null;
    public function mount($slug)
    {
        $this->slug = $slug;
        $this->businessId = Auth::user()->emp_b_id;
        $this->financialYears = FinancialYear::where('fy_b_id', $this->businessId)->get();
        $currentYear = $this->financialYears->firstWhere('fy_is_current', 1);
        if ($currentYear) {
            $this->selectedYear = $currentYear->fy_id;
        }
        if ($this->selectedYear) {
            $financialYear = $this->financialYears->where('fy_id', $this->selectedYear)->first();
            $this->fyStartDate = $financialYear->fy_start_date;
            $this->fyEndDate = $financialYear->fy_end_date;
            // Construct year as "YYYY-YYYY" (e.g., "2024-2025")
            $startYear = Carbon::parse($this->fyStartDate)->year;
            $endYear = Carbon::parse($this->fyEndDate)->year;
            $this->year = "{$startYear}-{$endYear}";
            $start = Carbon::parse($this->fyStartDate);
            $end = Carbon::parse($this->fyEndDate);
            $this->months = [];
            while ($start->lessThanOrEqualTo($end)) {
                $this->months[] = $start->format('F');
                $start->addMonth();
            }
            // Set the current month as a number (1-12)
            $currentMonth = Carbon::now()->month;
            $this->selectedMonth = in_array($currentMonth, range(1, 12)) ? $currentMonth : 1;
        } else {
            $this->fyStartDate = '';
            $this->fyEndDate = '';
            $this->year = null;
            $this->months = [];
            $this->selectedMonth = 1;
        }
    }


     public function selectYear($id)
    {
        $this->selectedYear = $id;
        $financialYear = $this->financialYears->where('fy_id', $id)->first();
        if ($financialYear) {
            $this->fyStartDate = $financialYear->fy_start_date;
            $this->fyEndDate = $financialYear->fy_end_date;
            $startYear = Carbon::parse($this->fyStartDate)->year;
            $endYear = Carbon::parse($this->fyEndDate)->year;
            $this->year = "{$startYear}-{$endYear}";
        } else {
            $this->year = null;
            $this->fyStartDate = '';
            $this->fyEndDate = '';
        }
    }
    public function selectEmployee($id, $name)
    {
        $this->selectedEmployeeId = $id;
        $this->searchEmployee = $name;
    }

    public function selectApprovalStatus($id)
    {
        $this->approvalStatusId = $id;
        // dd($this->approvalStatusId);
       
    }

    public function generateReport()
    {
        $this->validate([
            'selectedYear' => 'required|exists:financial_years,fy_id',
            'selectedMonth' => 'required|numeric|between:1,12',
            'selectedEmployeeId' => 'nullable|exists:employees,emp_id',
        ], [
            'selectedYear.required' => 'The financial year field is required.',
            'selectedYear.exists' => 'The selected financial year does not exist.',
            'selectedMonth.required' => 'The month field is required.',
            'selectedMonth.numeric' => 'The month must be a number.',
            'selectedMonth.between' => 'The month must be between 1 and 12.',
            'selectedEmployeeId.exists' => 'The selected employee does not exist.',
        ]);

        // Get financial year start and end dates
        $fyStart = Carbon::parse($this->fyStartDate);
        $fyEnd = Carbon::parse($this->fyEndDate);

        // Determine the selected year based on financial year and month
        $selectedYear = $this->selectedMonth >= $fyStart->month ? $fyStart->year : $fyEnd->year;
        // dd($this->approvalStatusId);
       
        $query = LoanRequest::with([
            'fh_employee:emp_id,emp_code,emp_full_name',
            'fh_payroll_loan_installments' => function ($q) use ($selectedYear) {
                $q->where('pli_month', $this->selectedMonth)
                  ->where('pli_year', $selectedYear);
            }
        ])
            ->where('lnr_b_id', $this->businessId)
            ->when($this->approvalStatusId, fn($q) => $q->where('lnr_request_status', $this->approvalStatusId))
            ->whereMonth('lnr_start_date', $this->selectedMonth)
            ->whereYear('lnr_start_date', $selectedYear)
            ->when($this->selectedEmployeeId, fn($q) => $q->where('lnr_emp_id', $this->selectedEmployeeId))
            ->whereHas('fh_payroll_loan_installments', function ($q) use ($selectedYear) {
                $q->where('pli_month', $this->selectedMonth)
                  ->where('pli_year', $selectedYear);
            });

          

            
       

        $result = $query->get();
            
         if ($result->isEmpty()) {
            session()->flash('error', 'No records found for the selected criteria.');
            return;
        }

        // Debugging: Dump the result
        // dd($result->toArray());
  
        return Excel::download(new LoanRegisterExport($result,$this->businessId), 'loan_register_' . date('d_m_Y_H_i_s') . '.xlsx');
    }

    public function fh_payroll_loan_installments()
    {
        return $this->hasMany(PayrollLoanInstallment::class, 'pli_loan_id', 'lnr_id');
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
        ];
        // Apply the generic reset logic
        if (array_key_exists($property, $mapping)) {
            $this->{$mapping[$property]} = null;
        }
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

        $approvalStatus = MasterTable::where('m_group', 'APPROVAL_STATUS')
            ->limit(100)
            ->select('m_id', 'm_name')
            ->get();

            
        return view('livewire.loan.loan-registration', compact(
            'employees',
            'employeeStatus',
            'approvalStatus'

        ));
    }
}
