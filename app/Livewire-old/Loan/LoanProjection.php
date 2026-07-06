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

class LoanProjection extends Component
{
    public $slug;
    public $searchEmployee = '';
    public $selectedEmployeeId;
    public $businessId = '';
    public $selectedFromDate = null;
    public $selectedToDate = null;
    public $employeeStatusId = null;
    public $businessName = '';


    public $approvalStatusId = null;
    public function mount($slug)
    {
        $this->slug = $slug;
        $this->businessId = Auth::user()->emp_b_id;
        $this->selectedFromDate = Carbon::now()->toDateString(); // Format: YYYY-MM-DD
        $this->selectedToDate = Carbon::now()->toDateString(); // Format: YYYY-MM-DD

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
    // Validate inputs
    $this->validate([
        'selectedFromDate' => 'required|date',
        'selectedToDate' => 'required|date|after_or_equal:selectedFromDate',
        'selectedEmployeeId' => 'nullable|exists:employees,emp_id',
    ], [
        'selectedFromDate.required' => 'The start date is required.',
        'selectedFromDate.date' => 'The start date must be a valid date.',
        'selectedToDate.required' => 'The end date is required.',
        'selectedToDate.date' => 'The end date must be a valid date.',
        'selectedToDate.after_or_equal' => 'The end date must be on or after the start date.',
        'selectedEmployeeId.exists' => 'The selected employee does not exist.',
    ]);

    try {
        // Parse dates
        $fromDate = Carbon::parse($this->selectedFromDate);
        $toDate = Carbon::parse($this->selectedToDate);
        $fromMonth = $fromDate->month;
        $toMonth = $toDate->month;
        $fromYear = $fromDate->year;
        $toYear = $toDate->year;

        // Build query
        $query = LoanRequest::with([
            'fh_employee:emp_id,emp_code,emp_full_name',
            'fh_payroll_loan_installments' => function ($q) use ($fromMonth, $toMonth, $fromYear, $toYear) {
                if ($fromYear == $toYear) {
                    // Same year: filter by month range
                    $q->whereBetween('pli_month', [$fromMonth, $toMonth])
                      ->where('pli_year', $fromYear);
                } else {
                    // Multi-year: include all months in range for each year
                    $q->where(function ($q) use ($fromMonth, $toMonth, $fromYear, $toYear) {
                        // From month to December for start year
                        $q->where('pli_year', $fromYear)
                          ->where('pli_month', '>=', $fromMonth)
                          ->orWhere(function ($q) use ($toMonth, $toYear) {
                              // January to toMonth for end year
                              $q->where('pli_year', $toYear)
                                ->where('pli_month', '<=', $toMonth);
                          })
                          ->orWhere(function ($q) use ($fromYear, $toYear) {
                              // Full years in between
                              $q->whereBetween('pli_year', [$fromYear + 1, $toYear - 1]);
                          });
                    });
                }
            }
        ])
            ->where('lnr_b_id', $this->businessId)
            ->when($this->approvalStatusId, fn($q) => $q->where('lnr_request_status', $this->approvalStatusId))
            ->when($this->selectedEmployeeId, fn($q) => $q->where('lnr_emp_id', $this->selectedEmployeeId))
            ->whereHas('fh_payroll_loan_installments', function ($q) use ($fromMonth, $toMonth, $fromYear, $toYear) {
                if ($fromYear == $toYear) {
                    $q->whereBetween('pli_month', [$fromMonth, $toMonth])
                      ->where('pli_year', $fromYear);
                } else {
                    $q->where(function ($q) use ($fromMonth, $toMonth, $fromYear, $toYear) {
                        $q->where('pli_year', $fromYear)
                          ->where('pli_month', '>=', $fromMonth)
                          ->orWhere(function ($q) use ($toMonth, $toYear) {
                              $q->where('pli_year', $toYear)
                                ->where('pli_month', '<=', $toMonth);
                          })
                          ->orWhere(function ($q) use ($fromYear, $toYear) {
                              $q->whereBetween('pli_year', [$fromYear + 1, $toYear - 1]);
                          });
                    });
                }
            });

        $result = $query->get();

        // dd($result->toArray());
        if ($result->isEmpty()) {
            session()->flash('error', 'No records found for the selected criteria.');
            return;
        }

        // Export to Excel
        return Excel::download(new LoanProjectionExport($result, $this->businessId), 'loan_register_' . date('d_m_Y_H_i_s') . '.xlsx');
    } catch (\Exception $e) {
        session()->flash('error', 'An error occurred while generating the report: ' . $e->getMessage());
        return;
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
        return view('livewire.loan.loan-projection', compact(
            'employees',
            'employeeStatus',
            'approvalStatus'
        ));
    }
}
