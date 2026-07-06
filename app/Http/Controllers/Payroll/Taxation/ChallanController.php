<?php

namespace App\Http\Controllers\Payroll\Taxation;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\FinancialYear;
use App\Models\FormChallan;
use App\Models\Department;
use App\Models\Designation;
use App\Models\ProcessedEmployeeSalary;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class ChallanController extends Controller
{
    private const FORMS = [
        'FORM16A' => 'Form 16A',
        'FORM16' => 'Form 16',
        'FORM15G' => 'Form 15G',
    ];

    public function index(Request $request)
    {
        $user = Auth::user();
        $businessId = (int) $user->emp_b_id;

        $financialYears = FinancialYear::where('fy_b_id', $businessId)
            ->orderBy('fy_year', 'desc')
            ->get(['fy_id', 'fy_year', 'fy_is_current', 'fy_start_date', 'fy_end_date']);

        $selectedFYId = (int) $request->input('financial_year_id');
        if (! $selectedFYId) {
            $selectedFYId = (int) optional($financialYears->firstWhere('fy_is_current', 1))->fy_id;
        }
        if (! $selectedFYId) {
            $selectedFYId = (int) optional($financialYears->first())->fy_id;
        }

        $selectedFinancialYear = $financialYears->firstWhere('fy_id', $selectedFYId);
        $fyEndDate = null;
        if ($selectedFinancialYear?->fy_end_date) {
            $fyEndDate = Carbon::parse($selectedFinancialYear->fy_end_date)->toDateString();
        } elseif ($selectedFinancialYear?->fy_start_date) {
            $fyEndDate = Carbon::parse($selectedFinancialYear->fy_start_date)->addYear()->subDay()->toDateString();
        }

        $selectedFormKey = (string) ($request->input('form_key') ?: 'FORM16A');
        if (! array_key_exists($selectedFormKey, self::FORMS)) {
            $selectedFormKey = 'FORM16A';
        }

        $selectedDepartmentId = $request->filled('department_id') ? (int) $request->input('department_id') : null;
        $selectedDesignationId = $request->filled('designation_id') ? (int) $request->input('designation_id') : null;

        $departments = Department::where('d_b_id', $businessId)
            ->where('d_status', 1)
            ->orderBy('d_name')
            ->get(['d_id', 'd_name']);

        $designations = Designation::where('dg_b_id', $businessId)
            ->orderBy('dg_name')
            ->get(['dg_id', 'dg_name']);

        $employees = Employee::query()
            ->with(['fh_department:d_id,d_name', 'fh_designation:dg_id,dg_name'])
            ->where('emp_b_id', $businessId)
            ->whereIn('emp_status', [71, 1])
            ->where('emp_role_id', '!=', 1)
            ->when($fyEndDate, function ($q) use ($fyEndDate) {
                $q->whereNotNull('emp_date_of_joining')
                    ->whereDate('emp_date_of_joining', '<=', $fyEndDate);
            })
            ->when($selectedDepartmentId, function ($q) use ($selectedDepartmentId) {
                $q->where('emp_d_id', $selectedDepartmentId);
            })
            ->when($selectedDesignationId, function ($q) use ($selectedDesignationId) {
                $q->where('emp_dg_id', $selectedDesignationId);
            })
            ->orderBy('emp_full_name')
            ->get(['emp_id', 'emp_code', 'emp_full_name', 'emp_d_id', 'emp_dg_id', 'emp_date_of_joining']);

        $eligibleEmployeeIds = $employees->pluck('emp_id');

        $challans = FormChallan::query()
            ->where('f16ac_b_id', $businessId)
            ->where('f16ac_fy_id', $selectedFYId)
            ->where('f16ac_form_key', $selectedFormKey)
            ->whereIn('f16ac_emp_id', $eligibleEmployeeIds)
            ->orderBy('f16ac_emp_id')
            ->orderBy('f16ac_quarter')
            ->get();

        return view('admin.payroll.taxation.challans', [
            'forms' => self::FORMS,
            'selectedFormKey' => $selectedFormKey,
            'financialYears' => $financialYears,
            'selectedFYId' => $selectedFYId,
            'selectedFyStartDate' => $selectedFinancialYear?->fy_start_date,
            'selectedFyEndDate' => $selectedFinancialYear?->fy_end_date,
            'todayDate' => now()->toDateString(),
            'departments' => $departments,
            'designations' => $designations,
            'selectedDepartmentId' => $selectedDepartmentId,
            'selectedDesignationId' => $selectedDesignationId,
            'employees' => $employees,
            'challans' => $challans,
        ]);
    }

    public function quarterSalaryAvailability(Request $request)
    {
        $request->validate([
            'financial_year_id' => 'required|integer|exists:financial_years,fy_id',
            'quarter' => 'required|in:Q1,Q2,Q3,Q4',
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'integer',
        ]);

        $user = Auth::user();
        $businessId = (int) $user->emp_b_id;

        $financialYear = FinancialYear::where('fy_id', (int) $request->financial_year_id)
            ->where('fy_b_id', $businessId)
            ->first();

        if (! $financialYear || ! $financialYear->fy_start_date) {
            return response()->json([
                'exists' => false,
                'message' => 'Financial year dates are not configured.',
            ]);
        }

        $quarter = (string) $request->quarter;
        $bounds = $this->getQuarterBounds($financialYear, $quarter);

        $employeeIds = collect($request->input('employee_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($employeeIds->isEmpty()) {
            $count = ProcessedEmployeeSalary::query()
                ->where('ps_b_id', $businessId)
                ->whereHas('payrollPeriod', function ($query) use ($request, $bounds) {
                    $query->where('pp_fy_id', (int) $request->financial_year_id)
                        ->whereNotNull('pp_start_date')
                        ->whereBetween('pp_start_date', [$bounds['quarterStart'], $bounds['quarterEnd']]);
                })
                ->count();

            if ($count <= 0) {
                return response()->json([
                    'exists' => false,
                    'message' => 'Salary for this quarter is not generated yet. You cannot select this quarter.',
                ]);
            }

            return response()->json(['exists' => true]);
        }

        $validEmployeeIds = Employee::where('emp_b_id', $businessId)
            ->whereIn('emp_id', $employeeIds)
            ->pluck('emp_id')
            ->values();

        $missingEmployees = $this->getEmployeesMissingQuarterSalary(
            $businessId,
            (int) $request->financial_year_id,
            $bounds,
            $validEmployeeIds
        );

        if (! empty($missingEmployees)) {
            $message = count($missingEmployees) === 1
                ? "Salary is not available for {$missingEmployees[0]} in this quarter. You cannot select this quarter."
                : 'Salary is not available in this quarter for: ' . implode(', ', $missingEmployees) . '. You cannot select this quarter.';

            return response()->json([
                'exists' => false,
                'message' => $message,
                'missing_employees' => $missingEmployees,
            ]);
        }

        return response()->json(['exists' => true]);
    }

    public function upsert(Request $request)
    {
        $request->validate([
            'form_key' => 'required|string|max:30',
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'integer',
            'financial_year_id' => 'required|integer|exists:financial_years,fy_id',
            'quarter' => 'required|in:Q1,Q2,Q3,Q4',
            'receipt_no' => 'required|string|max:100',
            'bsr_code' => 'required|string|max:20',
            'challan_date' => 'required|date',
            'challan_serial_no' => 'nullable|string|max:30',
        ]);

        $formKey = (string) $request->form_key;
        if (! array_key_exists($formKey, self::FORMS)) {
            return redirect()->back()->with('denied', 'Invalid form selected.');
        }

        $user = Auth::user();
        $businessId = (int) $user->emp_b_id;

        $financialYear = FinancialYear::where('fy_id', $request->financial_year_id)
            ->where('fy_b_id', $businessId)
            ->firstOrFail();

        $employeeIds = collect($request->input('employee_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if (! $financialYear->fy_start_date) {
            return redirect()->back()->with('denied', 'Financial year start date is not configured.');
        }

        $fyEndDate = $financialYear->fy_end_date
            ? Carbon::parse($financialYear->fy_end_date)->toDateString()
            : Carbon::parse($financialYear->fy_start_date)->addYear()->subDay()->toDateString();

        $validEmployeeIds = Employee::where('emp_b_id', $businessId)
            ->whereIn('emp_id', $employeeIds)
            ->whereNotNull('emp_date_of_joining')
            ->whereDate('emp_date_of_joining', '<=', $fyEndDate)
            ->pluck('emp_id')
            ->values();

        if ($validEmployeeIds->isEmpty()) {
            return redirect()->back()->with('denied', 'Selected employees are not eligible for this financial year based on joining date.');
        }

        if ($validEmployeeIds->count() < $employeeIds->count()) {
            return redirect()->back()->with('denied', 'One or more selected employees joined after the selected financial year and cannot be saved.');
        }

        $quarter = (string) $request->quarter;
        $bounds = $this->getQuarterBounds($financialYear, $quarter);
        $missingEmployees = $this->getEmployeesMissingQuarterSalary(
            $businessId,
            (int) $request->financial_year_id,
            $bounds,
            $validEmployeeIds
        );

        if (! empty($missingEmployees)) {
            $msg = 'Challan cannot be updated because salary for all 3 months is not generated in this quarter for: ' . implode(', ', $missingEmployees);
            return redirect()->back()->with('denied', $msg);
        }

        foreach ($validEmployeeIds as $empId) {
            FormChallan::updateOrCreate(
                [
                    'f16ac_b_id' => $businessId,
                    'f16ac_emp_id' => (int) $empId,
                    'f16ac_fy_id' => (int) $request->financial_year_id,
                    'f16ac_quarter' => (string) $request->quarter,
                    'f16ac_form_key' => $formKey,
                ],
                [
                    'f16ac_receipt_no' => (string) $request->receipt_no,
                    'f16ac_bsr_code' => (string) $request->bsr_code,
                    'f16ac_challan_date' => $request->challan_date,
                    'f16ac_challan_serial_no' => $request->challan_serial_no,
                    'f16ac_created_by' => (int) ($user->emp_id ?? null),
                ]
            );
        }

        return redirect()
            ->back()
            ->with('success', 'Challan details saved successfully.');
    }

    private function getQuarterBounds(FinancialYear $financialYear, string $quarter): array
    {
        $fyStart = Carbon::parse($financialYear->fy_start_date)->startOfDay();
        $qIndex = (int) str_replace('Q', '', $quarter);
        $quarterStart = $fyStart->copy()->addMonths(($qIndex - 1) * 3)->startOfDay();
        $quarterEnd = $quarterStart->copy()->addMonths(3)->subDay()->endOfDay();

        $expectedMonthNums = [
            (int) $quarterStart->copy()->addMonths(0)->format('n'),
            (int) $quarterStart->copy()->addMonths(1)->format('n'),
            (int) $quarterStart->copy()->addMonths(2)->format('n'),
        ];

        return compact('quarterStart', 'quarterEnd', 'expectedMonthNums');
    }

    private function getEmployeesMissingQuarterSalary(
        int $businessId,
        int $financialYearId,
        array $bounds,
        Collection $employeeIds
    ): array {
        $missingEmployees = [];
        $employeeMeta = Employee::where('emp_b_id', $businessId)
            ->whereIn('emp_id', $employeeIds)
            ->get(['emp_id', 'emp_full_name', 'emp_code'])
            ->keyBy('emp_id');

        foreach ($employeeIds as $empId) {
            $rows = ProcessedEmployeeSalary::query()
                ->with(['payrollPeriod'])
                ->where('ps_b_id', $businessId)
                ->where('ps_emp_id', (int) $empId)
                ->whereHas('payrollPeriod', function ($query) use ($financialYearId, $bounds) {
                    $query->where('pp_fy_id', $financialYearId)
                        ->whereNotNull('pp_start_date')
                        ->whereBetween('pp_start_date', [$bounds['quarterStart'], $bounds['quarterEnd']]);
                })
                ->get();

            $monthNums = $rows
                ->map(function ($r) {
                    $d = optional($r->payrollPeriod)->pp_start_date;
                    return $d ? (int) Carbon::parse($d)->format('n') : null;
                })
                ->filter()
                ->unique()
                ->values()
                ->all();

            $missing = array_values(array_diff($bounds['expectedMonthNums'], $monthNums));
            if (! empty($missing)) {
                $meta = $employeeMeta->get((int) $empId);
                $label = ($meta?->emp_full_name ?: ('Emp #' . $empId)) . ($meta?->emp_code ? ' (' . $meta->emp_code . ')' : '');
                $missingEmployees[] = $label;
            }
        }

        return $missingEmployees;
    }
}

