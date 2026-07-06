<?php

namespace App\Http\Controllers\Payroll\Taxation;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\FinancialYear;
use App\Models\ProcessedEmployeeSalary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FormGenerationController extends Controller
{
    public const REPORT_SLUG_MAP = [
        'form-16a' => 'form16a',
        'form-16' => 'form16',
        'form-15g' => 'form15g',
    ];

    public const FORM_TABS = [
        'form16a' => [
            'key' => 'form16a',
            'label' => 'Form 16A',
            'description' => 'Tax deduction certificate for non-salary income',
        ],
        'form16' => [
            'key' => 'form16',
            'label' => 'Form 16',
            'description' => 'Annual salary tax certificate (Part A & B)',
        ],
        'form15g' => [
            'key' => 'form15g',
            'label' => 'Form 15G',
            'description' => 'Declaration for non-deduction of TDS on income',
        ],
    ];

    public function index(Request $request)
    {
        $viewData = $this->buildViewData($request);

        return view('admin.payroll.taxation.form-generation', $viewData);
    }

    public function report(Request $request, string $slug)
    {
        if (! array_key_exists($slug, self::REPORT_SLUG_MAP)) {
            abort(404);
        }

        $request->merge(['form' => self::REPORT_SLUG_MAP[$slug]]);
        $viewData = $this->buildViewData($request);
        $activeForm = $viewData['activeForm'];

        return view('admin.setting.reports.tax-form-report', array_merge($viewData, [
            'slug' => $slug,
            'reportTitle' => self::FORM_TABS[$activeForm]['label'] ?? 'Tax Form Report',
            'formReloadUrl' => route('tax.form.report', ['slug' => $slug]),
        ]));
    }

    private function buildViewData(Request $request): array
    {
        $user = Auth::user();
        $businessId = (int) $user->emp_b_id;

        $financialYears = FinancialYear::where('fy_b_id', $businessId)
            ->orderBy('fy_year', 'desc')
            ->get(['fy_id', 'fy_year', 'fy_is_current']);

        $selectedFYId = (int) $request->input('financial_year_id');
        if (! $selectedFYId) {
            $selectedFYId = (int) optional($financialYears->firstWhere('fy_is_current', 1))->fy_id;
        }
        if (! $selectedFYId) {
            $selectedFYId = (int) optional($financialYears->first())->fy_id;
        }

        $activeForm = (string) $request->input('form', 'form16a');
        if (! array_key_exists($activeForm, self::FORM_TABS)) {
            $activeForm = 'form16a';
        }

        $departments = Department::where('d_b_id', $businessId)
            ->where('d_status', 1)
            ->orderBy('d_name')
            ->get(['d_id', 'd_name']);

        $processedEmployeeIds = ProcessedEmployeeSalary::query()
            ->where('ps_b_id', $businessId)
            ->whereHas('payrollPeriod', function ($query) use ($selectedFYId) {
                $query->where('pp_fy_id', $selectedFYId);
            })
            ->pluck('ps_emp_id')
            ->unique()
            ->values();

        $employees = Employee::query()
            ->with(['fh_designation:dg_id,dg_name', 'fh_department:d_id,d_name', 'fh_branch:br_id,br_name'])
            ->where('emp_b_id', $businessId)
            ->whereIn('emp_status', [71, 72, 1])
            ->where('emp_role_id', '!=', 1)
            ->whereIn('emp_id', $processedEmployeeIds)
            ->orderBy('emp_full_name')
            ->get([
                'emp_id',
                'emp_code',
                'emp_full_name',
                'emp_pan_number',
                'emp_d_id',
                'emp_dg_id',
                'emp_br_id',
                'emp_status',
            ])
            ->map(function ($employee) {
                return [
                    'id' => $employee->emp_id,
                    'code' => $employee->emp_code ?: ('EMP' . $employee->emp_id),
                    'name' => $employee->emp_full_name ?: ('Employee #' . $employee->emp_id),
                    'designation' => optional($employee->fh_designation)->dg_name ?: 'N/A',
                    'department' => optional($employee->fh_department)->d_name ?: 'N/A',
                    'department_id' => (int) ($employee->emp_d_id ?? 0),
                    'branch' => optional($employee->fh_branch)->br_name ?: 'N/A',
                    'pan' => $employee->emp_pan_number ?: 'N/A',
                    'emp_status' => (int) ($employee->emp_status ?? 0),
                ];
            });

        return [
            'employees' => $employees,
            'financialYears' => $financialYears,
            'selectedFYId' => $selectedFYId,
            'departments' => $departments,
            'formTabs' => self::FORM_TABS,
            'activeForm' => $activeForm,
        ];
    }
}
