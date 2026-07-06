<?php

namespace App\Http\Controllers\Payroll;

use App\Helpers\CentralLogics;
use App\Helpers\RolePermissionLogics;
use App\Http\Controllers\Controller;
use App\Models\AttendanceSummary;
use App\Models\Branch;
use App\Models\Country;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeExitRequest;
use App\Models\FinancialYear;
use App\Models\IncomeTaxSlab;
use App\Models\LabourWelfareFundMaster;
use App\Models\LoanRequest;
use App\Models\MasterTable;
use App\Models\PayrollLoanAccount;
use App\Models\PayrollLoanInstallment;
use App\Models\PayrollPeriod;
use App\Models\PayrollTemplate;
use App\Models\PayrollTemplateDeduction;
use App\Models\PayrollTemplateEarning;
use App\Models\Payslip;
use App\Models\PayslipConfiguration;
use App\Models\PolicyTadaCategory;
use App\Models\ProcessedEmployeeSalary;
use App\Models\ProcessedSalaryDeduction;
use App\Models\ProcessedSalaryEarning;
use App\Models\ProfessionalTaxMaster;
use App\Models\ProfessionalTaxSlab;
use App\Models\SalaryAllowance;
use App\Models\SalaryEmployeeEarnings;
use App\Models\SalaryEmployeeSalary;
use App\Models\StatutoryDeduction;
use Barryvdh\DomPDF\Facade\Pdf;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use NumberToWords\NumberToWords;

class PayrollController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function index(Request $request)
    {
        $emp_b_id = auth()->user()->emp_b_id;

        $YearId = $request->financial_year ?? FinancialYear::where('fy_b_id', $emp_b_id)
            ->where('fy_is_current', 1)
            ->orderByDesc('fy_year')
            ->value('fy_id');

        $monthId = $request->month ?? date('n');

        // Masters
        $years = FinancialYear::where('fy_b_id', $emp_b_id)->orderByDesc('fy_year')->get();
        $months = MasterTable::where('m_group', 'MONTH')->get();

        // Payroll period
        $payrollPeriod = PayrollPeriod::where('pp_b_id', $emp_b_id)
            ->where('pp_fy_id', $YearId)
            ->where('pp_month_id', $monthId)
            ->first();

        $payrollperiodsid = $payrollPeriod ? $payrollPeriod->pp_id : 0;

        // Total active employees (excluding admin)
        $totalemployees = Employee::where('emp_b_id', $emp_b_id)
            ->where('emp_status', 71)
            ->whereNotIn('emp_role_id', [1])
            ->count();

        // Processed salaries
        $processed_salaries = ProcessedEmployeeSalary::where('ps_b_id', $emp_b_id)
            ->where('ps_payroll_id', $payrollperiodsid)
            ->get();

        $esic_employee_total = 0;
        $esic_employer_total = 0;
        $pf_employee_total = 0;
        $pf_employer_total = 0;

        $statutoryTotals = ProcessedSalaryDeduction::whereHas('processedSalary', function ($q) use ($emp_b_id, $payrollperiodsid) {
            $q->where('ps_b_id', $emp_b_id)
                ->where('ps_payroll_id', $payrollperiodsid);
        })
            ->whereIn('ps_deduction_type_id', [351, 352])
            ->select(
                'ps_deduction_type_id',
                'ps_d_category',
                \Illuminate\Support\Facades\DB::raw('SUM(CAST(ps_d_amount AS DECIMAL(12,2))) as total_amount')
            )
            ->groupBy('ps_deduction_type_id', 'ps_d_category')
            ->get();

        foreach ($statutoryTotals as $row) {
            if ($row->ps_deduction_type_id == 352) {
                $row->ps_d_category === 'employee'
                    ? $esic_employee_total = $row->total_amount
                    : $esic_employer_total = $row->total_amount;
            }

            if ($row->ps_deduction_type_id == 351) {
                $row->ps_d_category === 'employer'
                    ? $pf_employee_total = $row->total_amount
                    : $pf_employer_total = $row->total_amount;
            }
        }

        $employeesalaries = SalaryEmployeeSalary::where('es_b_id', $emp_b_id)
            ->select('es_emp_id', 'es_monthly_net_salary')
            ->distinct()
            ->get();

        $paidamount = $processed_salaries->sum('ps_monthly_salary');
        $totalpayableamount = $employeesalaries->sum('es_monthly_net_salary');
        $unpaidamount = $totalpayableamount - $paidamount;

        $paidemployee = $processed_salaries->count();
        $unpaidemployee = $totalemployees - $paidemployee;

        $currentStart = $payrollPeriod->pp_start_date ?? null;
        $currentEnd = $payrollPeriod->pp_end_date ?? null;

        // dd($currentStart, $currentEnd);

        // 7. F&F Status
        $totalExits = EmployeeExitRequest::where('er_b_id', $emp_b_id)->whereBetween('created_at', [$currentStart, $currentEnd])->count();
        $relieved = EmployeeExitRequest::where('er_b_id', $emp_b_id)->whereBetween('created_at', [$currentStart, $currentEnd])->where('er_overall_status', 'RELIEVED')->count();
        $pending = $totalExits - $relieved;

        // 9. Advance Management
        $loanIds = LoanRequest::where('lnr_b_id', $emp_b_id)->where('lnr_stage_completed', 1)->whereBetween('created_at', [$currentStart, $currentEnd])->pluck('lnr_id');
        $totalApprovedLoans = $loanIds->count();

        $totalApprovedAmount = LoanRequest::whereIn('lnr_id', $loanIds)->where('lnr_stage_completed', 1)->whereBetween('created_at', [$currentStart, $currentEnd])->sum('lnr_requested_amount');
        $totalpayback = PayrollLoanInstallment::where('pli_b_id', $emp_b_id)->whereIn('pli_loan_id', $loanIds)->where('pli_status', 'paid')->sum('pli_rem_bal');
        $totalOutstandingAmount = $totalApprovedAmount - $totalpayback;

        // Late Contribution
        $lateDeductions = ProcessedSalaryDeduction::whereHas('processedSalary', function ($q) use ($emp_b_id, $payrollperiodsid) {
            $q->where('ps_b_id', $emp_b_id)
                ->where('ps_payroll_id', $payrollperiodsid);
        })
            ->where('ps_deduction_type_id', 444) // Late deduction type
            ->select(
                'ps_d_category',
                DB::raw('SUM(CAST(ps_d_amount AS DECIMAL(12,2))) as total_amount')
            )
            ->groupBy('ps_d_category')
            ->get();

        // Initialize totals
        $late_employee_total = 0;
        $late_employer_total = 0;

        foreach ($lateDeductions as $row) {
            if ($row->ps_d_category === 'employee') {
                $late_employee_total = $row->total_amount;
            } elseif ($row->ps_d_category === 'employer') {
                $late_employer_total = $row->total_amount;
            }
        }

        // over time calculation
        $overtimeTotal = ProcessedSalaryEarning::whereHas('processedSalary', function ($q) use ($emp_b_id, $payrollperiodsid) {
            $q->where('ps_b_id', $emp_b_id)
                ->where('ps_payroll_id', $payrollperiodsid);
        })
            ->where('ps_earning_type_id', 'Overtime') // Only Overtime
            ->select(DB::raw('SUM(CAST(ps_e_amount AS DECIMAL(12,2))) as total_amount'))
            ->first();

        $overtime_total_amount = $overtimeTotal->total_amount ?? 0;

        return view('admin.payroll.dashboard', compact(
            'years',
            'months',
            'YearId',
            'monthId',
            'totalemployees',
            'paidemployee',
            'unpaidemployee',
            'paidamount',
            'unpaidamount',
            'esic_employee_total',
            'esic_employer_total',
            'pf_employee_total',
            'pf_employer_total',
            'relieved',
            'loanIds',
            'totalApprovedLoans',
            'totalApprovedAmount',
            'totalpayback',
            'totalOutstandingAmount',
            'pending',
            'late_employee_total',
            'overtime_total_amount',
            'late_employer_total'
        ));
    }

    public function payrollComponentList(Request $request)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;
        $masterData = MasterTable::whereIn('m_group', ['ALLOWANCE_CAL_TYPE', 'PAYROLL_EARNING', 'PAYROLL_HEADINGS'])->get()->groupBy('m_group');
        $otherAllowanceData = MasterTable::where('m_id', 364)->first();
        $calculation_type = $masterData->get('ALLOWANCE_CAL_TYPE', collect())->pluck('m_name', 'm_id')->toArray();
        $name = SalaryAllowance::where('sa_b_id', $businessId)->get();
        // $totalThresholdSum = SalaryAllowance::where( 'sa_b_id', $businessId )->sum( 'sa_threshold_value' );
        $earnings = SalaryAllowance::where('sa_b_id', $businessId)->get();
        $earning_type_id = $masterData->get('PAYROLL_EARNING', collect())->pluck('m_name', 'm_id')->toArray();
        $payroll_heading_id = $masterData->get('PAYROLL_HEADINGS', collect())->pluck('m_name', 'm_id')->toArray();

        // Step 1: Identify Basic Salary ( 50% )
        $basicSalaryComponent = $earnings->where('sa_earning_type_id', 360)->first();
        $basicSalaryThreshold = $basicSalaryComponent ? 50 : 0;
        // Basic Salary ( 50% )

        $data = SalaryEmployeeEarnings::all();
        $existingEarningIds = $data->pluck('es_sa_id')->toArray();

        // Step 2: Calculate Other Allowances ( Sum of Threshold % )
        $otherAllowancesSum = 0;
        foreach ($earnings as $earning) {
            if ($earning->sa_earning_type_id != 360) {
                // Ignore Basic Salary
                $otherAllowancesSum += $earning->sa_threshold_value;
            }
        }

        // Step 3: Validation - Other Allowances should not exceed 100%
        if ($otherAllowancesSum > 100) {
            $otherAllowancesSum = 100;
            // Cap at 100%
        }

        // **Final Output**
        $totalThresholdSum = $basicSalaryThreshold + $otherAllowancesSum;

        // return [
        //     'basic_salary_percentage' => $basicSalaryThreshold,
        //     'other_allowances_percentage' => $otherAllowancesSum,
        //     'total_percentage' => $totalThresholdSum, // Should be max 150% ( 50% Basic + 100% Other )
        // ];

        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['sa_b_id', $businessId],
                ],
                [
                    'method' => 'select',
                    'args' => ['sa_id', 'sa_b_id', 'sa_title', 'sa_description', 'sa_calculation_type', 'sa_earning_type_id', 'sa_payroll_heading_id', 'sa_threshold_value', 'sa_consider_for_pf', 'sa_consider_for_pf_condition', 'sa_consider_for_esic', 'sa_calculate_on_prorata_basis', 'sa_is_taxable', 'sa_name_in_payslip', 'sa_show_in_payslip', 'sa_sequence_valu', 'sa_is_active', 'sa_updated_at', 'sa_created_at'],
                    'relation' => ['fh_allowance_calculation_type:m_id,m_name', 'fh_business:b_id', 'fh_earning_type:m_id,m_name', 'fh_payroll_heading:m_id,m_name'],

                ],
                [
                    'method' => 'sortBy',
                    'args' => ['sa_id', 'sa_b_id', 'sa_title', 'sa_description', 'sa_calculation_type', 'sa_threshold_value', 'sa_consider_for_pf', 'sa_consider_for_pf_condition', 'sa_consider_for_esic', 'sa_calculate_on_prorata_basis', 'sa_is_taxable', 'sa_name_in_payslip', 'sa_show_in_payslip', 'sa_sequence_valu', 'sa_is_active', 'sa_updated_at', 'sa_created_at'],
                ],
            ];

            // Additional filter logic...
            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new SalaryAllowance,
                dynamicConditions: $dynamicConditions,
                searchColumns: ['sa_id', 'sa_b_id', 'sa_title', 'sa_description', 'sa_calculation_type', 'sa_threshold_value', 'sa_consider_for_pf', 'sa_consider_for_pf_condition', 'sa_consider_for_esic', 'sa_calculate_on_prorata_basis', 'sa_is_taxable', 'sa_name_in_payslip', 'sa_show_in_payslip', 'sa_is_active', 'sa_updated_at', 'sa_created_at'],
                searchRelationships: [
                    'fh_allowance_calculation_type' => ['m_id', 'm_group', 'm_name'],
                ]
            ))->getServerSideDataTable();

            $rowData = [];
            $i = 1;

            $row = [];
            $row[] = $i++;
            $row[] = $otherAllowanceData->m_name;
            $row[] = $otherAllowanceData->m_name;
            $row[] = '';
            // $row[] = $val->sa_name_in_payslip;
            $row[] = 'Active';

            $row[] = '
                <div class="btn-list ms-3">

                </div>';
            $rowData[] = $row;

            // $rowData = [];
            // $i = 1;
            foreach ($list as $key => $val) {
                $row = [];
                $row[] = $i++;
                $row[] = $val->sa_title;
                $row[] = $val->sa_description;
                $row[] = $val->sa_sequence_valu;
                $row[] = isset($val->sa_is_active) && $val->sa_is_active == 1 ? 'Active' : 'Inactive';

                // Check if delete button should be disabled
                $disableDelete = in_array($val->sa_id, $existingEarningIds);

                $editUrl = 'javascript:void(0)'; // Or build your URL as needed

                $row[] = '
                    <div class="btn-list ms-3">
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-ellipsis-v"></i> <!-- Three-dot icon -->
                            </button>
                            <ul class="dropdown-menu p-2" style="min-width: 180px;">
                                <li>
                                    <a class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2 edit-salary-allowance"
                                        href="'.$editUrl.'"
                                        data-bs-target="#salaryAllowanceModal" data-bs-toggle="modal"
                                        data-id="'.$val->sa_id.'" data-b_id="'.$val->sa_b_id.'"
                                        data-title="'.$val->sa_title.'" data-dis="'.$val->sa_description.'"
                                        data-earning_type_id="'.$val->sa_earning_type_id.'" data-payroll_heading_id="'.$val->sa_payroll_heading_id.'"
                                        data-calculation_type="'.$val->sa_calculation_type.'" data-threshold_value="'.$val->sa_threshold_value.'"
                                        data-pf_check="'.$val->sa_consider_for_pf.'" data-for_pf_condition="'.$val->sa_consider_for_pf_condition.'"
                                        data-esic_check="'.$val->sa_consider_for_esic.'" data-calculate_basis="'.$val->sa_calculate_on_prorata_basis.'"
                                        data-tax_check="'.$val->sa_is_taxable.'" data-name_in_payslip="'.$val->sa_name_in_payslip.'"
                                        data-sequence_valu="'.$val->sa_sequence_valu.
                    '" data-payslip_check="'.$val->sa_show_in_payslip.'" data-status_check="'.$val->sa_is_active.'">
                                        <i class="feather feather-edit"></i> Edit
                                    </a>
                                </li>
                                <li>
                                    '.($disableDelete
                        ? '<button class="dropdown-item text-muted fw-semibold d-flex align-items-center gap-2" disabled title="Cannot delete: already assigned">
                                                <i class="feather feather-trash"></i> Delete
                                           </button>'
                        : '<button class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2 delete-salary-allowance"
                                                    data-id="'.$val->sa_id.'" type="button">
                                                <i class="feather feather-trash"></i> Delete
                                           </button>').'
                                </li>
                            </ul>
                        </div>
                    </div>';

                $rowData[] = $row;
            }

            $output = [
                'draw' => intval($request->input('draw')),
                'recordsTotal' => count($list),
                'recordsFiltered' => (new DynamicModelDataTableHelper(
                    eloquentModel: new SalaryAllowance,
                    dynamicConditions: $dynamicConditions
                ))->countFilteredServerSideDataTable(),
                'data' => $rowData,
            ];

            return json_encode($output);
        }

        // View
        $columns = [
            'S. No.',
            'components',
            'Description',
            'Sequence No.',
            // 'Payslip Name',
            'Status',
            'Action',
        ];

        return view('admin.payroll.salary-components', compact('otherAllowancesSum', 'earning_type_id', 'payroll_heading_id', 'calculation_type', 'columns'));
    }

    public function getEarningName(Request $request)
    {
        $earningTypeId = $request->earning_type_id;
        $currentSaId = $request->sa_id;

        if ($earningTypeId == 364) {
            // $existing = SalaryAllowance::where('sa_earning_type_id', 364)
            //     ->when($currentSaId, function ($query) use ($currentSaId) {
            //         return $query->where('sa_id', '!=', $currentSaId);
            //     })
            //     ->exists();

            $existing = SalaryAllowance::where('sa_earning_type_id', 364)
                ->where('sa_id', $currentSaId)->first();

            if ($existing) {
                return response()->json(['success' => true, 'earning_name' => $existing->sa_title]);
            }
        }

        $earning = SalaryAllowance::where('sa_earning_type_id', $earningTypeId)->first();

        if (! $earning && $earningTypeId != 364) {
            $earning = MasterTable::where('m_id', $earningTypeId)->first();
        }

        if ($earning) {
            return response()->json([
                'success' => true,
                'earning_name' => $earning->sa_title ?? $earning->m_title ?? '',
            ]);
        } else {
            return response()->json(['success' => false, 'earning_name' => '']);
        }
    }

    public function createPayrollComponent(Request $request)
    {
        // dd( $request->all() );
        $user = Auth::user();
        $businessId = $user->emp_b_id;
        $sa_id = $request->sa_id;
        $newThresholdValue = $request->calculation_value;
        $earningTypeId = $request->earning_type_id; // Identify if it's Basic Salary or Other Allowance
        $payrollHeadingId = $request->payroll_heading_id;

        $restrictedIds = [360, 361, 362, 363];

        $otherAllowValue = strtolower(str_replace(' ', '_', $request->name));
        if ($otherAllowValue == 'other_allowance' || $otherAllowValue == 'otherallowance') {
            return response()->json([
                'status' => false,
                'message' => 'Other allowance name already exists',
            ], 400);
        }

        $sequenceValue = trim($request->sequence_valu);
        if ($sequenceValue) {
            $sequenceDuplicate = SalaryAllowance::where('sa_b_id', $businessId)
                ->where('sa_sequence_valu', $sequenceValue)
                ->when($sa_id, fn ($query) => $query->where('sa_id', '!=', $sa_id))
                ->exists();

            if ($sequenceDuplicate) {
                return response()->json([
                    'status' => false,
                    'message' => 'The sequence value already exists. Please choose another one.',
                ], 400);
            }
        }

        if (! $sa_id) {
            if (in_array($earningTypeId, $restrictedIds)) {
                $duplicate = SalaryAllowance::where('sa_b_id', $businessId)->where('sa_earning_type_id', $earningTypeId)->exists();
            } else {
                $duplicate = SalaryAllowance::where('sa_b_id', $businessId)->where('sa_earning_type_id', $earningTypeId)->where('sa_title', $request->name)->exists();
            }
            if ($duplicate) {
                return response()->json([
                    'status' => false,
                    'message' => 'This earning type already exists!',
                ], 400);
            }
        }
        // ✅ Check for duplicate `earning_type_id` when UPDATING
        if ($sa_id) {
            if (in_array($earningTypeId, $restrictedIds)) {
                $duplicate = SalaryAllowance::where('sa_b_id', $businessId)
                    ->where('sa_earning_type_id', $earningTypeId)
                    ->where('sa_id', '!=', $sa_id)
                    ->exists();
            } else {
                $duplicate = SalaryAllowance::where('sa_b_id', $businessId)
                    ->where('sa_earning_type_id', $earningTypeId)
                    ->where('sa_title', $request->name)
                    ->where('sa_id', '!=', $sa_id)
                    ->exists();
            }
            if ($duplicate) {
                return response()->json([
                    'status' => false,
                    'message' => 'This earning type already exists and cannot be duplicated!',
                ], 400);
            }
        }

        // Save Allowance
        $save = SalaryAllowance::updateOrCreate(
            ['sa_id' => $sa_id],
            [
                'sa_b_id' => $businessId,
                'sa_title' => $request->name,
                'sa_description' => $request->des,
                'sa_calculation_type' => $request->calculation_type,
                'sa_threshold_value' => $newThresholdValue,
                'sa_earning_type_id' => $earningTypeId, // Saving Earning Type
                'sa_payroll_heading_id' => $payrollHeadingId,
                'sa_consider_for_pf' => $request->pf_check,
                'sa_consider_for_pf_condition' => $request->pf_condition,
                'sa_consider_for_esic' => $request->esic_check,
                'sa_calculate_on_prorata_basis' => $request->calculate_basis,
                'sa_is_taxable' => $request->tax_check,
                'sa_name_in_payslip' => $request->payslip,
                'sa_show_in_payslip' => $request->payslip_check,
                'sa_sequence_valu' => $request->sequence_valu,
                'sa_is_active' => $request->status_check,
            ]
        );
        // dd($save);

        if ($save) {
            $message = $sa_id ? 'Salary allowance updated successfully!' : 'Salary allowance created successfully!';

            return response()->json(['status' => true, 'message' => $message]);
        } else {
            return response()->json(['status' => false, 'message' => 'Failed to save Salary allowance.']);
        }
    }

    public function deletePayrollComponent(string $id)
    {
        $result = CentralLogics::dynamicDelete(SalaryAllowance::class, $id);

        // dd($result);

        if (! is_array($result)) {
            return response()->json(['error' => 'Unexpected error occurred'], 500);
        }

        if (isset($result['success'])) {
            return response()->json(['success' => 'Salary allowance deleted successfully!'], 200);
        }

        return response()->json(['error' => $result['error'] ?? 'Unable to delete salary allowance'], 400);
    }

    public function salaryMaster(Request $request)
    {
        $user = Auth::user();
        $branchFilter = request()->input('branchFilter');
        $activeFilter = request()->input('activeFilter');
        $designationFilter = request()->input('designationFilter');
        $departmentFilter = request()->input('departmentFilter');
        $gradeFilter = request()->input('gradeFilter');
        $fromDateFilter = request()->input('fromDate');
        $toDateFilter = request()->input('toDate');

        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['emp_b_id', $this->user->emp_b_id],
                ],
                [
                    'method' => 'whereNot',
                    'args' => ['emp_role_id', 1],
                ],
                [
                    'method' => 'select',
                    'args' => ['emp_id', 'emp_b_id', 'emp_fname', 'emp_mname', 'emp_lname', 'emp_full_name', 'emp_code', 'emp_role_id', 'emp_email', 'emp_type_id', 'emp_br_id', 'emp_d_id', 'emp_dg_id', 'emp_dob', 'emp_date_of_joining', 'emp_phone', 'emp_work_mode_id', 'emp_shift_type_id', 'emp_status', 'emp_grade_id', 'emp_gender_id', 'emp_date_of_joining', 'emp_marital_status_id', 'emp_profile_photo', 'created_at'],
                    'relation' => ['fh_employee_type:m_id, m_name', 'fh_branch:br_id, br_name', 'fh_gender:m_id, m_name', 'fh_designation:dg_id, dg_name', 'fh_department:d_id, d_name', 'fh_work_mode:m_id, m_name', 'fh_shift_type:pst_id, pst_name', 'fh_employee_status:m_id, m_name', 'fh_marital_status:m_id, m_name', 'fh_grade:g_id, g_name, g_b_id', 'fh_role:role_id, role_b_id, role_name, role_description'],
                ],
                [
                    'method' => 'sortBy',
                    'args' => ['emp_id', 'emp_full_name', 'emp_code', 'emp_type_id', 'emp_br_id', 'emp_d_id', 'emp_date_of_joining', 'emp_phone', 'emp_work_mode_id', 'emp_shift_type_id', 'emp_status'],
                ],
            ];

            // Filter conditions
            if ($branchFilter != '') {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_br_id', $branchFilter]];
            }

            if ($activeFilter != '') {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_status', $activeFilter]];
            }

            if ($designationFilter != '') {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_dg_id', $designationFilter]];
            }

            if ($departmentFilter != '') {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_d_id', $departmentFilter]];
            }

            if ($departmentFilter != '') {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_d_id', $departmentFilter]];
            }
            if ($gradeFilter != '') {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_grade_id', $gradeFilter]];
            }

            if ($fromDateFilter || $toDateFilter) {
                if ($fromDateFilter && $toDateFilter) {
                    if ($fromDateFilter === $toDateFilter) {
                        // Apply filtering for the exact same date
                        $dynamicConditions[] = [
                            'method' => 'whereDate',
                            'args' => ['created_at', $fromDateFilter],
                        ];
                    } else {
                        // Ensure full-day coverage for both from_date and to_date
                        $fromDateTime = $fromDateFilter.' 00:00:00';
                        $toDateTime = $toDateFilter.' 23:59:59';

                        // Apply date filtering if both dates are provided and they are different
                        $dynamicConditions[] = [
                            'method' => 'whereBetween',
                            'args' => ['created_at', [$fromDateTime, $toDateTime]],
                        ];
                    }
                } elseif ($fromDateFilter) {
                    // Apply filtering from the start date onwards if only from_date is provided
                    $fromDateTime = $fromDateFilter.' 00:00:00';
                    $dynamicConditions[] = [
                        'method' => 'where',
                        'args' => ['created_at', ' >= ', $fromDateTime],
                    ];
                } elseif ($toDateFilter) {
                    // Apply filtering up to the end date if only to_date is provided
                    $toDateTime = $toDateFilter.' 23:59:59';
                    $dynamicConditions[] = [
                        'method' => 'where',
                        'args' => ['created_at', ' <= ', $toDateTime],
                    ];
                }
            }

            // Define search value, columns, and relationships
            $searchColumns = ['emp_id', 'created_at', 'updated_at', 'emp_code', 'emp_fname', 'emp_mname', 'emp_lname', 'emp_full_name', 'emp_email', 'emp_date_of_joining', 'emp_phone'];
            $searchRelationships = [
                'fh_employee_type' => ['m_name'],
                'fh_branch' => ['br_name'],
                'fh_department' => ['d_name'],
                'fh_work_mode' => ['m_name'],
                'fh_shift_type' => ['pst_name'],
                'fh_employee_status' => ['m_name'],
            ];

            $list = (new DynamicModelDataTableHelper(eloquentModel: new Employee, dynamicConditions: $dynamicConditions, searchColumns: $searchColumns, searchRelationships: $searchRelationships))->getServerSideDataTable();

            $rowData = [];
            $i = 1;
            foreach ($list as $key => $val) {
                $policyCategory = PolicyTadaCategory::where(['ptc_b_id' => $val->emp_b_id, 'ptc_d_id' => $val->emp_d_id, 'ptc_grade_id' => $val->emp_grade_id])->whereJsonContains('ptc_dg_id', $val->emp_dg_id)->first();
                $dataPolicy = $policyCategory->ptc_name ?? 'N/A';
                $row[] = '<button class="btn action-btns btn-sm btn-primary openBtn"
                data-id="'.$val->emp_id.'"
                data-name="'.$val->emp_fname.' '.$val->emp_mname.' '.$val->emp_lname.'"
                data-code="'.$val->emp_code.'"
                data-type="'.optional($val->fh_employee_type)->m_name.'"
                data-branch="'.optional($val->fh_branch)->br_name.'"
                data-department="'.optional($val->fh_department)->d_name.'"
                data-phone="'.$val->emp_phone.'"
                data-email="'.$val->emp_email.'"
                data-designation="'.optional($val->fh_designation)->dg_name.'"
                data-grade="'.optional($val->fh_grade)->g_name.'"
                data-gender="'.optional($val->fh_gender)->m_name.'"
                data-role="'.optional($val->fh_role)->role_name.'"
                data-birth="'.$val->emp_dob.'"
                data-marital="'.optional($val->fh_marital_status)->m_name.'"
                data-joining="'.$val->emp_date_of_joining.'"
                data-status="'.optional($val->fh_employee_status)->m_name.'"
                data-mode="'.optional($val->fh_work_mode)->m_name.'"
                data-shift="'.optional($val->fh_shift_type)->pst_name.'"
                data-profile="'.$val->emp_profile_photo.'"
                data-policy="'.$dataPolicy.'">
                <i class="feather feather-eye"></i>
            </button>';

                // Edit Button
                $editUrl = route('update.employee', md5($val->emp_id));
                if ($val->emp_id != $this->user->emp_id) {
                    $row[] .= '<a class="btn action-btns btn-sm btn-primary" href="'.
                        (RolePermissionLogics::check_route_permission('admin/employee/form/{id}', 117) ? $editUrl : '#').'">
                    <i class="feather feather-edit"></i>
                </a>';
                }

                // Append to row data
                $rowData[] = $row;
            }

            $output = [
                'draw' => request()->input('draw'),
                'recordsTotal' => count($list),
                'recordsFiltered' => (new DynamicModelDataTableHelper(eloquentModel: new Employee, dynamicConditions: $dynamicConditions, searchColumns: $searchColumns, searchRelationships: $searchRelationships))->countFilteredServerSideDataTable(),
                'data' => $rowData,
            ];

            return json_encode($output);
        } else {
            $business = $this->user->fh_business()
                ->with(
                    'fh_branches:br_id, br_b_id, br_name',
                    'fh_departments:d_id, d_b_id, d_name',
                    'fh_designations:dg_id, dg_name, dg_b_id',
                    'fh_employees:*'
                )
                ->where('b_id', $this->user->emp_b_id)
                ->get();

            $businessId = $this->user->emp_b_id;
            $currentMonth = now()->month;
            $allEmployeeCount = $business->first()->fh_employees()->where('emp_b_id', $businessId)->where('emp_role_id', '<>', 1)->count();
            $genderCounts = $business->first()->fh_employees()->selectRaw('count( * ) as count, emp_gender_id')
                ->where('emp_b_id', $businessId)
                ->groupBy('emp_gender_id')
                ->pluck('count', 'emp_gender_id');
            $maleEmployeesCount = $genderCounts[33] ?? 0;
            $femaleEmployeesCount = $genderCounts[34] ?? 0;
            $newEmployeesCount = $business->first()->fh_employees()->where('emp_b_id', $businessId)
                ->whereMonth('emp_date_of_joining', $currentMonth)
                ->count();

            $branches = $business->first()->fh_branches->pluck('br_name', 'br_id')->toArray();
            $departments = $business->first()->fh_departments->pluck('d_name', 'd_id')->toArray();
            $designations = $business->first()->fh_designations->pluck('dg_name', 'dg_id')->toArray();

            // $branches = Branch::where('br_b_id', $user->emp_b_id)->pluck('br_name', 'br_id')->toArray();
            // $departments = Department::where('d_b_id', $user->emp_b_id)->pluck('d_name', 'd_id')->toArray();
            // $designations = Designation::where('dg_b_id', $user->emp_b_id)->pluck('dg_name', 'dg_id')->toArray();
            $countries = Country::pluck('c_name', 'c_id')->toArray();
            $groups = [
                'EMPLOYEE_TYPE',
                'CONTRACTUAL_TYPE',
                'GENDER',
                'MARITAL_STATUS',
                'RELIGION',
                'CAST',
                'BLOOD_GROUP',
                'GOVT_DOC_TYPE',
                'WORK_MODE',
            ];

            $data = MasterTable::whereIn('m_group', $groups)
                ->get(['m_group', 'm_name', 'm_id'])
                ->groupBy('m_group')
                ->map(function ($items) {
                    return $items->pluck('m_name', 'm_id')->toArray();
                });

            $getEmpType = $data['EMPLOYEE_TYPE'] ?? [];
            $getContractualType = $data['CONTRACTUAL_TYPE'] ?? [];
            $genders = $data['GENDER'] ?? [];
            $maritalStatuses = $data['MARITAL_STATUS'] ?? [];
            $religions = $data['RELIGION'] ?? [];
            $casts = $data['CAST'] ?? [];
            $bloodGroups = $data['BLOOD_GROUP'] ?? [];
            $govtIds = $data['GOVT_DOC_TYPE'] ?? [];
            $attendanceMethod = $data['WORK_MODE'] ?? [];
        }

        $columns = [
            'S. No.',
            'Employee Name',
            'Employee Code',
            'Employee Type',
            'Branch',
            'Department',
            'Joining Date',
            'Phone Number',
            'Attendance Method',
            'Shift Type',
            'Status',
            'Action',
        ];

        return view('admin.employees.salary_master.index', compact('allEmployeeCount', 'maleEmployeesCount', 'femaleEmployeesCount', 'newEmployeesCount', 'getEmpType', 'getContractualType', 'genders', 'maritalStatuses', 'religions', 'casts', 'bloodGroups', 'govtIds', 'branches', 'departments', 'designations', 'attendanceMethod', 'countries', 'columns'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function payrollTemplateList()
    {
        $breadcrumbs = CentralLogics::getBreadcrumbs();
        $pageTitle = 'Payroll Template';
        $columns = ['Template Name', 'Description', 'Status'];
        $allowances = SalaryAllowance::where('sa_b_id', Auth::user()->emp_b_id)->where('sa_is_active', 1)->get();
        // $deductions = StatutoryDeduction::with(['fh_deduction_type:m_id, m_name','fh_deduction_cycle:m_id, m_name'])->where('std_b_id',Auth::user()->emp_b_id)->get();
        $deductions = StatutoryDeduction::where('std_b_id', Auth::user()->emp_b_id)->where('std_status', 1)->get();
        // foreach ($deductions as $deduction){
        //     dd($deduction->fh_deduction_cycle->m_name,$deduction->fh_deduction_type->m_name);
        // }

        return view('admin.payroll.template', compact('breadcrumbs', 'pageTitle', 'columns', 'allowances', 'deductions'));
    }

    public function addEditTemplate()
    {
        $breadcrumbs = CentralLogics::getBreadcrumbs();
        $pageTitle = 'Add Template';

        return view('admin.payroll.add-edit', compact('breadcrumbs', 'pageTitle'));
    }

    public function storePayrollTemplate(Request $request)
    {
        DB::beginTransaction(); // Transaction Start

        try {
            $payrollTemplate = PayrollTemplate::create([
                'pt_temp_name' => $request->template_name,
                'pt_temp_description' => $request->description,
                'pt_an_ctc' => $request->annual_ctc,
                'pt_m_ctc' => $request->monthly_ctc,
                'pt_component_type' => $request->componentType,
                'pt_component_list_id' => $request->componentList,
                'pt_total_earning' => $request->total_earnings,
                'pt_total_deduction' => $request->total_deductions,
                'pt_gross_salary' => $request->total_gross_salary,
            ]);
            $templateId = $payrollTemplate->pt_id;

            $earningsData = json_decode($request->earnings_data, true);

            if (! empty($earningsData)) {
                foreach ($earningsData as $earning) {
                    PayrollTemplateEarning::create([
                        'pt_temp_id' => $templateId,
                        'pt_earn_type_id' => $earning['id'],

                    ]);
                }
            }

            $deductionsData = json_decode($request->deductions_data, true);
            if (! empty($deductionsData)) {
                foreach ($deductionsData as $deduction) {
                    PayrollTemplateDeduction::create([
                        'pt_temp_id' => $templateId,
                        'pt_deduc_type_id' => $deduction['id'],

                    ]);
                }
            }

            DB::commit();

            return response()->json(['status' => true, 'message' => 'Template created successfully', 'template_id' => $templateId]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getComponentForTemplate(Request $request, $type)
    {

        try {

            $result = ['status' => false, 'data' => null, 'message' => ''];

            if ($type == 'EARNINGS') {
                $allowances = SalaryAllowance::with('fh_allowance_calculation_type')->where('sa_b_id', Auth::user()->emp_b_id)->where('sa_is_active', 1);
                $array = explode(', ', $request->earningRowIds);
                $allowances = $request->earningRowIds ? $allowances->whereNotIn('sa_id', $array)->get() : $allowances->get();

                if (count($allowances)) {
                    $result['status'] = true;
                    $result['data'] = $allowances;
                    $result['message'] = 'allowance record found.';
                }
            }
            if ($type == 'DEDUCTIONS') {
                $array = explode(', ', $request->deductionRowIds);
                $deductions = StatutoryDeduction::with(['fh_deduction_type', 'fh_deduction_cycle'])->where('std_b_id', Auth::user()->emp_b_id)->where('std_status', 1);
                $deductions = $request->deductionRowIds ? $deductions->whereNotIn('std_id', $array)->get() : $deductions->get();
                if (count($deductions)) {
                    $result['status'] = true;
                    $result['data'] = $deductions;
                    $result['message'] = 'deductions record found.';
                }
            }
        } catch (Exception $e) {
            $result['status'] = false;
            $result['data'] = null;
            $result['message'] = $e->getMessage();
        }

        return response()->json($result);
    }

    public function payrollDeductionList()
    {
        $user = Auth::user();

        $breadcrumbs = CentralLogics::getBreadcrumbs();
        $pageTitle = 'Statutory Deductions';
        $masterData = MasterTable::whereIn('m_group', ['DEDUCTION_CYCLE', 'YESNO', 'STATUTORY_DEDUCTION'])->get();

        $sine = (Country::where('c_id', $user->fh_business->b_currency)->pluck('c_currency_symbol')->first());

        // dd($sine);

        $deductionCycle = $masterData->where('m_group', 'DEDUCTION_CYCLE')->pluck('m_name', 'm_id')->toArray();
        $yesOrNo = $masterData->where('m_group', 'YESNO')->pluck('m_name', 'm_id')->toArray();
        $statutoryDeduction = $masterData->where('m_group', 'STATUTORY_DEDUCTION')->pluck('m_name', 'm_id')->toArray();

        $statutoryDeductionData = StatutoryDeduction::where('std_b_id', $this->user->emp_b_id)->get();
        $taxSlabs = ProfessionalTaxSlab::where('pts_b_id', $this->user->emp_b_id)->get();
        $professionalTaxMaster = ProfessionalTaxMaster::with([
            'state:s_id,s_name',
            'cycle:m_id,m_name',
            'gender:m_id,m_name',

        ])->get();

        $labourWelfareMasters = LabourWelfareFundMaster::with(['state', 'cycle'])->get();

        $business = $this->user->fh_business()
            ->with('fh_branches', 'fh_branches.fh_professional_tax_slabs', 'fh_branches.fh_state', 'fh_income_tax_slabs') // Eager load branches
            ->first();

        $branches = $business ? $business->fh_branches : collect(); // Ensure it's a collection
        $incomeTaxSlabs = $business ? $business->fh_income_tax_slabs()->paginate(10) : collect();

        return view('admin.payroll.test', compact('breadcrumbs', 'pageTitle', 'deductionCycle', 'labourWelfareMasters', 'professionalTaxMaster', 'statutoryDeduction', 'statutoryDeductionData', 'taxSlabs', 'branches', 'incomeTaxSlabs', 'sine'));
    }

    public function createOrUpdatePayrollDeduction(Request $request)
    {
        if ($request->std_deduction_type_id == 353) {
            $title = $request->title;
            $request->validate([
                'std_deduction_type_id' => 'required|integer',
                'title' => 'required|string|max:255',
                'pts_income_from' => 'required|numeric|min:0',
                'pts_income_to' => 'required|numeric|min:0|gte:pts_income_from',
                'pts_tax_amount' => 'nullable|numeric|min:0',
            ], [
                'std_deduction_type_id.required' => 'The deduction type is required.',
                'std_deduction_type_id.integer' => 'The deduction type must be a valid integer.',

                'title.required' => 'The title field is required.',
                'title.string' => 'The title must be a valid string.',
                'title.max' => 'The title cannot exceed 255 characters.',

                'pts_income_from.required' => 'The "Income From" field is required.',
                'pts_income_from.numeric' => 'The "Income From" must be a valid number.',
                'pts_income_from.min' => 'The "Income From" must be at least 0.',

                'pts_income_to.required' => 'The "Income To" field is required.',
                'pts_income_to.numeric' => 'The "Income To" must be a valid number.',
                'pts_income_to.min' => 'The "Income To" must be at least 0.',
                'pts_income_to.gte' => 'The "Income To" must be greater than or equal to "Income From".',

                'pts_tax_amount.numeric' => 'The tax amount must be a valid number.',
                'pts_tax_amount.min' => 'The tax amount cannot be negative.',
            ]);

            $brId = $request->brId;
            if ($brId) {
                $brId = Crypt::decrypt($brId);
            }
            $branch = Branch::with('fh_professional_tax_slabs')->find($brId);
            if (! $branch) {
                return response()->json([
                    'status' => 'failed',
                    'message' => $title.' Deduction record not found!',
                ], 404);
            }
            $branch->fh_professional_tax_slabs()->updateOrCreate(['pts_id' => $branch->fh_professional_tax_slabs->pts_id ?? null], [
                'pts_b_id' => $this->user->emp_b_id,
                'pts_income_from' => $request->pts_income_from,
                'pts_income_to' => $request->pts_income_to,
                'pts_tax_amount' => $request->pts_tax_amount,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => $title.' Deduction updated successfully!',
                // 'data' => $payrollDeduction
            ], 201);
        } elseif ($request->std_deduction_type_id == 354) {
            $request->validate([
                'its_income_from' => 'required|numeric|min:0',
                'its_income_to' => 'required|numeric|min:0',
                'its_tax_rate' => 'required|numeric|min:0|max:100',
            ]);
            if ($request->its_id) {
                $id = Crypt::decrypt($request->its_id);
            }
            $statutoryDeduction = IncomeTaxSlab::updateOrCreate([
                'its_id' => $id ?? null,
                'its_b_id' => $this->user->emp_b_id,
            ], [
                'its_income_from' => $request->its_income_from,
                'its_income_to' => $request->its_income_to,
                'its_tax_rate' => $request->its_tax_rate,
            ]);

            if ($statutoryDeduction->wasRecentlyCreated) {
                $message = 'TDS Deduction created successfully!';
            } else {
                $message = 'TDS Deduction updated successfully!';
            }

            return response()->json([
                'status' => 'success',
                'message' => $message,
                // 'data' => $statutoryDeduction
            ], 201);
        } else {

            // Validate the incoming data
            $validator = Validator::make($request->all(), [
                'std_deduction_cycle_id' => 'required|exists:master_table,m_id',
                'std_deduction_type_id' => 'required|exists:master_table,m_id',
                'std_employee_contri_rate_amount' => 'required|numeric|min:0',
                'std_employer_contri_rate_amount' => 'required|numeric|min:0',
                // 'std_status' => 'nullable|boolean',
                // 'std_threshold' =>'required|numeric|min:0',
                // Add any additional validation rules as needed
            ], [
                'std_deduction_cycle_id.exists' => 'Deduction cycle not found.',
                'std_deduction_type_id.exists' => 'Deduction type not found.',
                'std_deduction_cycle_id.required' => 'Deduction cycle id field is required.',

                'std_employee_contri_rate_amount.required' => 'Employee contribution rate amount is required.',
                'std_employee_contri_rate_amount.numeric' => 'Employee contribution rate amount must be a number.',
                'std_employee_contri_rate_amount.min' => 'Employee contribution rate amount must be greater than or equal to 0.',
                'std_employer_contri_rate_amount.required' => 'Employer contribution rate amount is required.',
                'std_employer_contri_rate_amount.numeric' => 'Employer contribution rate amount must be a number.',
                'std_employer_contri_rate_amount.min' => 'Employer contribution rate amount must be greater than or equal to 0.',
                'std_threshold.required' => 'Threshold field is required.',
                'std_status.boolean' => 'Status must be a boolean value.',
                // Add any additional validation rule messages as needed

            ]);
            // Check if validation fails
            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $customErrors = [];
                // Modify errors to append '-{key}' to the field names
                foreach ($errors as $key => $messages) {
                    // Modify the error key to include '-{key}' format
                    $newKey = $key.'-'.$request->std_deduction_type_id;
                    // Example: std_employee_contri_rate_amount-std_employee_contri_rate_amount
                    $customErrors[$newKey] = $messages;
                    // Store in custom format
                }

                // Return the custom error response
                return response()->json([
                    'errors' => $customErrors,
                ], 422);
                // 422 is Unprocessable Entity
            }
            $title = $request->title;
            // Validation passed, proceed with saving the data
            $request->std_status = $request->std_status ? ($request->std_status == 'on' ? 1 : 0) : 0;
            // Check if an existing record needs to be updated or if a new one should be created
            if ($request->has('std_id') && $request->std_id) {
                // Update existing record
                $payrollDeduction = StatutoryDeduction::find($request->std_id);

                if (! $payrollDeduction) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => $title.' Deduction record not found!',
                    ], 404);
                }

                // Update the record
                $payrollDeduction->update([
                    'std_b_id' => $this->user->emp_b_id,
                    'std_deduction_type_id' => $request->std_deduction_type_id,
                    'std_deduction_cycle_id' => $request->std_deduction_cycle_id,
                    'std_employee_contri_rate_amount' => $request->std_employee_contri_rate_amount,
                    'std_employer_contri_rate_amount' => $request->std_employer_contri_rate_amount,
                    'std_status' => $request->std_status ?? 0, // Set the default to 0 if not provided
                    'std_threshold' => $request->std_threshold,
                    // Add any other fields that need to be updated
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => $title.' Deduction updated successfully!',
                    // 'data' => $payrollDeduction
                ], 201);
            } else {
                // Create a new record
                $payrollDeduction = StatutoryDeduction::create([
                    'std_b_id' => $this->user->emp_b_id,
                    'std_deduction_type_id' => $request->std_deduction_type_id,
                    'std_deduction_cycle_id' => $request->std_deduction_cycle_id,
                    'std_employee_contri_rate_amount' => $request->std_employee_contri_rate_amount,
                    'std_employer_contri_rate_amount' => $request->std_employer_contri_rate_amount,
                    'std_status' => $request->std_status ?? 0, // Set the default to 0 if not provided
                    'std_threshold' => $request->std_threshold,
                    // Add any other fields for the creation
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Payroll Deduction created successfully!',
                    // 'data' => $payrollDeduction
                ], 201);
            }
        }
    }

    // public function payrollPayslip(Request $request)
    // {
    //     $breadcrumbs = CentralLogics::getBreadcrumbs();
    //     $pageTitle = 'Payslips';
    //     $businessId = $this->user->emp_b_id;
    //     $branchId = $this->user->emp_br_id;

    //     $searchTerm = $request->input('search');
    //     $payrollPeriod = $request->input('payroll_period');
    //     $departmentId = $request->input('department');
    //     $designationId = $request->input('designation');

    //     $employeeList = Employee::where('emp_b_id', $businessId)
    //         ->select('emp_id', 'emp_full_name')
    //         ->get();

    //     $departmentList = Department::where('d_b_id', $businessId)
    //         ->select('d_id', 'd_name')
    //         ->get();

    //     // Assuming you have a Designation model
    //     $designationList = Designation::where('dg_b_id', $businessId)
    //         ->select('dg_id', 'dg_name')
    //         ->get();

    //     $month = $request->month ?? now()->format('m');
    //     $year = $request->year ?? now()->format('Y');
    //     $frozenMonths = AttendanceSummary::pluck('as_year_month')->toArray();

    //     $processedSalariesQuery = ProcessedEmployeeSalary::with([
    //         'employee.fh_department',
    //         'employee.fh_designation',
    //         'payrollPeriod'
    //     ])->where('ps_b_id', $businessId);

    //     // 🔍 Apply search filters
    //     if ($searchTerm) {
    //         $processedSalariesQuery->whereHas('employee', function ($query) use ($searchTerm) {
    //             $query->where('emp_full_name', 'like', "%{$searchTerm}%")
    //                 ->orWhere('emp_code', 'like', "%{$searchTerm}%");
    //         })->orWhereHas('payrollPeriod', function ($query) use ($searchTerm) {
    //             $query->where('pp_name', 'like', "%{$searchTerm}%");
    //         });
    //     }

    //     // 📅 Filter by payroll period
    //     if ($payrollPeriod) {
    //         $processedSalariesQuery->where('ps_payroll_id', $payrollPeriod);
    //     }

    //     // 🏢 Filter by department
    //     if ($departmentId) {
    //         $processedSalariesQuery->whereHas('employee.fh_department', function ($query) use ($departmentId) {
    //             $query->where('d_id', $departmentId);
    //         });
    //     }

    //     // 👤 Filter by designation
    //     if ($designationId) {
    //         $processedSalariesQuery->whereHas('employee.fh_designation', function ($query) use ($designationId) {
    //             $query->where('dg_id', $designationId);
    //         });
    //     }

    //     $processed_salaries = $processedSalariesQuery->get();

    //     // dd($processed_salaries);

    //     $payroll_periods = PayrollPeriod::all();

    //     return view('admin.payroll.payslip', compact(
    //         'breadcrumbs',
    //         'payroll_periods',
    //         'processed_salaries',
    //         'month',
    //         'year',
    //         'branchId',
    //         'pageTitle',
    //         'employeeList',
    //         'departmentList',
    //         'businessId',
    //         'frozenMonths',
    //         'designationList'  // Pass the designation list to the view
    //     ));
    // }
    /**
     * Get payroll periods for selected financial year
     */
    public function getPayrollPeriods(Request $request)
    {
        try {
            $fyId = $request->fy_id;
            $businessId = Auth::user()->emp_b_id;

            if (! $fyId) {
                return response()->json(['error' => 'Financial year ID required'], 400);
            }

            $periods = PayrollPeriod::where('pp_b_id', $businessId)
                ->where('pp_fy_id', $fyId)
                ->select('pp_id', 'pp_name', 'pp_start_date', 'pp_end_date')
                ->orderBy('pp_start_date', 'desc')
                ->get();

            // Add payslip generated status (optional)
            foreach ($periods as $period) {
                $period->payslip_generated = ProcessedEmployeeSalary::where('ps_payroll_id', $period->pp_id)->exists();
            }

            return response()->json($periods);

        } catch (\Exception $e) {
            \Log::error('Error in getPayrollPeriods: '.$e->getMessage());

            return response()->json(['error' => 'Failed to load periods'], 500);
        }
    }

    public function payrollPayslip(Request $request)
    {
        $user = Auth::user();
        $roleId = $user->emp_role_id;

        if ($request->ajax()) {
            $search = $request->input('search.value');  // Note: DataTables sends search as 'search.value'
            $periodId = $request->input('period_id');
            $departmentId = $request->input('department');
            $designationId = $request->input('designation');
            $financial_year = $request->input('financial_year');
            $employeeStatus = $request->input('employee_status');

            // Log search for debugging
            \Log::info('Search term: '.$search);

            // Base conditions
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['ps_b_id', $user->emp_b_id],
                ],
                [
                    'method' => 'with',
                    'args' => [
                        'employee.fh_department:d_id,d_name',
                        'employee.fh_designation:dg_id,dg_name',
                        'payrollPeriod:pp_id,pp_name,pp_fy_id,pp_start_date,pp_end_date',
                    ],
                ],
            ];

            // Only show data if period_id is selected
            if (! empty($periodId)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['ps_payroll_id', $periodId]];
            } else {
                return response()->json([
                    'draw' => intval($request->input('draw')),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                ]);
            }

            // Apply filters - these are separate from search
            if (! empty($designationId)) {
                $dynamicConditions[] = [
                    'method' => 'whereRelation',
                    'parentMethod' => 'whereHas',
                    'childMethod' => 'where',
                    'args' => ['emp_dg_id', $designationId],
                    'relation' => 'employee',
                ];
            }

            if (! empty($departmentId)) {
                $dynamicConditions[] = [
                    'method' => 'whereRelation',
                    'parentMethod' => 'whereHas',
                    'childMethod' => 'where',
                    'args' => ['emp_d_id', $departmentId],
                    'relation' => 'employee',
                ];
            }

            if (! empty($employeeStatus)) {
                $dynamicConditions[] = [
                    'method' => 'whereRelation',
                    'parentMethod' => 'whereHas',
                    'childMethod' => 'where',
                    'args' => ['emp_status', $employeeStatus],
                    'relation' => 'employee',
                ];
            }

            if (! empty($financial_year)) {
                $dynamicConditions[] = [
                    'method' => 'whereRelation',
                    'parentMethod' => 'whereHas',
                    'childMethod' => 'where',
                    'args' => ['pp_fy_id', $financial_year],
                    'relation' => 'payrollPeriod',
                ];
            }

            // Search columns configuration - The package will automatically search in these
            $searchColumns = ['ps_id'];  // Keep this
            $searchRelationships = [
                'employee' => ['emp_full_name', 'emp_code'],  // Search in employee name and code
                'payrollPeriod' => ['pp_name'],  // Search in payroll period name
            ];

            // REMOVE the manual search condition - the package handles it through searchRelationships
            // Don't add the Closure-based where condition

            // Create helper - package automatically search apply karega
            $dataTableHelper = new DynamicModelDataTableHelper(
                eloquentModel: new ProcessedEmployeeSalary,
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
                searchRelationships: $searchRelationships
            );

            // Get data
            $list = $dataTableHelper->getServerSideDataTable();
            $rowData = [];
            $i = $request->input('start', 0);

            foreach ($list as $item) {
                // Role-based access check
                if ($roleId === 1 || $user->emp_id == $item->ps_emp_id) {
                    $i++;

                    // Data columns
                    $row = [
                        $item->employee->emp_code ?? '-',
                        $item->employee->emp_full_name ?? '-',
                        $item->employee->fh_department->d_name ?? '-',
                        $item->employee->fh_designation->dg_name ?? '-',
                        $item->payrollPeriod->pp_name ?? '-',
                        '₹ '.number_format($item->ps_monthly_net_salary, 2),
                        optional($item->created_at)->format('d M Y'),
                    ];

                    // Action buttons (mode-aware): weekly rows use weekly download route.
                    $isWeeklyPayslipRow = ! empty($item->ps_week_id)
                        || (int) ($item->payrollPeriod->pp_type_id ?? 0) === 441;

                    $viewUrl = $roleId === 1
                        ? route('salary.viewPayslip2', $item->ps_id)
                        : route('view.employee.payslip', $item->ps_id);

                    $downloadUrl = $isWeeklyPayslipRow
                        ? route('payroll.weekly.downloadPayslip', $item->ps_id)
                        : route('salary.downloadPayslip2', $item->ps_id);
                    $send_email = route('salary.send_email', $item->ps_id);

                    $actionButtons = '
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fa fa-cog"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="'.$viewUrl.'" target="_blank">
                                <i class="fa fa-eye text-info me-2"></i> View
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="'.$downloadUrl.'">
                                <i class="fa fa-download text-primary me-2"></i> Download
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item send-mail-btn" href="'.$send_email.'" data-file="'.$downloadUrl.'">
                                <i class="fa fa-envelope text-success me-2"></i> Send Mail
                            </a>
                        </li>
                    </ul>
                </div>';

                    $row[] = $actionButtons;
                    $row[] = '<input type="checkbox" class="form-check-input testClass" value="'.$item->ps_id.'" onchange="selectCheckboxUpdate(this)">';

                    $rowData[] = $row;
                }
            }

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $dataTableHelper->countFilteredServerSideDataTable(),
                'recordsFiltered' => $dataTableHelper->countFilteredServerSideDataTable(),
                'data' => $rowData,
            ]);
        }

        $pageTitle = 'Payslips';
        $businessId = $user->emp_b_id;

        $breadcrumbs = CentralLogics::getBreadcrumbs();
        $departmentList = Department::where('d_b_id', $businessId)->select('d_id', 'd_name')->get();
        $designationList = Designation::where('dg_b_id', $businessId)->select('dg_id', 'dg_name')->get();
        $financial_year = FinancialYear::where('fy_b_id', $businessId)->get();
        $emp_status = MasterTable::where('m_group', 'STATUS')->get();

        $columns = [
            'Emp. Code',
            'Emp. Name',
            'Department',
            'Designation',
            'Payroll Period',
            'Net Salary',
            'Created At',
            'Action',
        ];

        return view('admin.payroll.payslip', compact(
            'breadcrumbs',
            'pageTitle',
            'departmentList',
            'businessId',
            'columns',
            'financial_year',
            'emp_status',
            'designationList',
        ));
    }

    public function getPayslipData()
    {
        dd('hello');
    }

    // public function send_email($id)
    // {
    //     try {
    //         $processedSalary = ProcessedEmployeeSalary::findOrFail($id);
    //         $filename = $processedSalary->ps_payslip_url;
    //         $employee = Employee::with(['fh_department', 'fh_designation', 'fh_business', 'fh_branch'])
    //             ->findOrFail($processedSalary->ps_emp_id);

    //          $logoPath = $employee->fh_business->b_logo ?? null;

    //         $payrollPeriod = PayrollPeriod::where('pp_id', $processedSalary->ps_payroll_id)->firstOrFail();
    //         $payrollName = $payrollPeriod->pp_name;
    //         $empCode = $employee->emp_code;
    //         $financialYear = FinancialYear::where('fy_id', $payrollPeriod->pp_fy_id)->firstOrFail();

    //         $employeeFullName = $employee->emp_full_name;
    //         $managerEmail = $employee->emp_email;

    //         if (!filter_var($managerEmail, FILTER_VALIDATE_EMAIL)) {
    //             return response()->json(['error' => 'Invalid recipient email address.'], 400);
    //         }

    //         Mail::send('emails.payslip', [
    //             'processedSalary' => $processedSalary,
    //             'employee' => $employee,
    //             'period' => $payrollPeriod,
    //             'financialYear' => $financialYear,
    //             'logoPath' => $logoPath,
    //         ], function ($mail) use ($managerEmail, $employeeFullName, $filename, $payrollName, $empCode) {
    //             $mail->to($managerEmail)
    //                 ->subject("Payslip for {$payrollName}_{$employeeFullName}_{$empCode}")
    //                 ->attach($filename, [
    //                     'as' => 'Payslip.pdf',
    //                     'mime' => 'application/pdf',
    //                 ]);
    //         });

    //         return response()->json([
    //             'summary' => "Email sent successfully with payslip attached.",
    //         ]);

    //     } catch (\Exception $e) {
    //         // Log the error for debugging if needed
    //         Log::error('Payslip email error: ' . $e->getMessage());

    //         return response()->json([
    //             'error' => 'An error occurred while sending the email: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function send_email($id)
    {
        try {
            $processedSalary = ProcessedEmployeeSalary::findOrFail($id);

            $employee = Employee::with(['fh_department', 'fh_designation', 'fh_business', 'fh_branch'])
                ->findOrFail($processedSalary->ps_emp_id);

            $logoPath = $employee->fh_business->b_logo ?? null;
            $payrollPeriod = PayrollPeriod::findOrFail($processedSalary->ps_payroll_id);
            $payrollName = $payrollPeriod->pp_name;
            $empCode = $employee->emp_code;
            $financialYear = FinancialYear::findOrFail($payrollPeriod->pp_fy_id);
            $employeeFullName = $employee->emp_full_name;
            $managerEmail = $employee->emp_email;

            if (! filter_var($managerEmail, FILTER_VALIDATE_EMAIL)) {
                return response()->json(['error' => 'Invalid recipient email address.'], 400);
            }

            // Load PDF from the same Blade as viewPayslip2
            $user = auth()->user();
            $authUserId = $processedSalary->ps_generated_by;
            $authUser = Employee::find($authUserId)?->emp_full_name ?? 'System';

            // Convert amount to words
            $numberToWords = new NumberToWords;
            $numberTransformer = $numberToWords->getNumberTransformer('en');
            $net_salary = number_format((float) str_replace(',', '', $processedSalary->ps_monthly_net_salary), 2, '.', '');
            [$integerPart, $decimalPart] = explode('.', $net_salary) + [0, 0];
            $net_salary_words = ucfirst($numberTransformer->toWords((int) $integerPart)).' rupees';
            if ((int) $decimalPart > 0) {
                $net_salary_words .= ' and '.$numberTransformer->toWords((int) $decimalPart).' paise';
            }
            $net_salary_words .= ' only';

            $config = PayslipConfiguration::where('pc_b_id', $user->emp_b_id)->latest()->first();
            $payslipOptions = [
                'show_employee_code' => $config->pc_show_employee_code ?? true,
                'show_employee_name' => $config->pc_show_employee_name ?? true,
                'show_department' => $config->pc_show_department ?? true,
                'show_designation' => $config->pc_show_designation ?? true,
                'show_branch' => $config->pc_show_branch ?? true,
                'show_bank_details' => $config->pc_show_bank_details ?? true,
                'show_doj' => $config->pc_show_doj ?? true,
                'show_month' => $config->pc_show_month ?? true,
                'show_month_days' => $config->pc_show_month_days ?? true,
                'show_salary_days' => $config->pc_show_salary_days ?? true,
                'show_present_days' => $config->pc_show_present_days ?? true,
                'show_lwp_days' => $config->pc_show_lwp_days ?? true,
                'show_ip_uan' => $config->pc_show_ip_uan ?? true,
                'include_earnings' => $config->pc_show_earnings_breakdown ?? true,
                'include_employee_deduction' => $config->pc_show_employee_deductions_breakdown ?? true,
                'include_employer_deduction' => $config->pc_show_employer_deductions_breakdown ?? true,
                'include_ctc' => $config->pc_show_total_ctc ?? true,
                'show_signature' => $config->pc_show_signature ?? true,
                'show_disclaimer' => $config->pc_show_disclaimer ?? true,
                'show_net_salary_words' => $config->pc_show_net_salary_in_words ?? true,
                'round_off_net_salary' => $config->pc_round_off_net_salary ?? true,
            ];

            $data = [
                'employee' => $employee,
                'processedSalary' => $processedSalary,
                'net_salary_words' => $net_salary_words,
                'logoPath' => $logoPath,
                'payroll_period' => $payrollPeriod,
                'authUser' => $authUser,
                'payslipOptions' => $payslipOptions,
            ];

            $pdf = Pdf::loadView('admin.employees.salary.monthly_salary_template_two', $data);
            $pdfContent = $pdf->output(); // Get PDF content to attach

            Mail::send([], [], function ($mail) use ($managerEmail, $employeeFullName, $pdfContent, $payrollName, $empCode) {
                $mail->to($managerEmail)
                    ->subject("Payslip for {$payrollName}_{$employeeFullName}_{$empCode}")
                    ->attachData($pdfContent, 'Payslip.pdf', [
                        'mime' => 'application/pdf',
                    ])
                    ->setBody('Please find attached your payslip.', 'text/html');
            });

            return response()->json([
                'summary' => 'Email sent successfully with payslip attached.',
            ]);
        } catch (\Exception $e) {
            Log::error('Payslip email error: '.$e->getMessage());

            return response()->json([
                'error' => 'An error occurred while sending the email: '.$e->getMessage(),
            ], 500);
        }
    }

    public function sendBulkEmail(Request $request)
    {
        $encryptedIds = $request->input('emp_ids', []);
        $intIds = array_filter(array_map('intval', $encryptedIds));

        $batchSize = 10;
        $successCount = 0;
        $statusList = [];

        $chunks = array_chunk($intIds, $batchSize);

        foreach ($chunks as $chunkIndex => $chunk) {
            foreach ($chunk as $id) {
                $managerEmail = null;
                try {
                    $processedSalary = ProcessedEmployeeSalary::findOrFail($id);
                    $filename = $processedSalary->ps_payslip_url;

                    if (! file_exists($filename)) {
                        $filename = storage_path('app/'.ltrim($processedSalary->ps_payslip_url, '/'));
                    }

                    $employee = Employee::with(['fh_department', 'fh_designation', 'fh_business', 'fh_branch'])
                        ->findOrFail($processedSalary->ps_emp_id);

                    $payrollPeriod = PayrollPeriod::findOrFail($processedSalary->ps_payroll_id);
                    $financialYear = FinancialYear::findOrFail($payrollPeriod->pp_fy_id);

                    $employeeFullName = $employee->emp_full_name;
                    $managerEmail = $employee->emp_email;

                    if (! filter_var($managerEmail, FILTER_VALIDATE_EMAIL)) {
                        $statusList[] = [
                            'employee' => $employeeFullName,
                            'email' => $managerEmail,
                            'status' => 'Failed',
                            'reason' => 'Invalid email address',
                        ];

                        continue;
                    }

                    if (! file_exists($filename)) {
                        $statusList[] = [
                            'employee' => $employeeFullName,
                            'email' => $managerEmail,
                            'status' => 'Failed',
                            'reason' => 'Payslip file not found',
                        ];

                        continue;
                    }

                    Mail::send('emails.payslip', [
                        'processedSalary' => $processedSalary,
                        'employee' => $employee,
                        'period' => $payrollPeriod,
                        'financialYear' => $financialYear,
                    ], function ($mail) use ($managerEmail, $employeeFullName, $filename) {
                        $mail->to($managerEmail)
                            ->subject("Payslip for {$employeeFullName}")
                            ->attach($filename, [
                                'as' => 'Payslip.pdf',
                                'mime' => 'application/pdf',
                            ]);
                    });

                    if (count(Mail::failures()) > 0) {
                        $statusList[] = [
                            'employee' => $employeeFullName,
                            'email' => $managerEmail,
                            'status' => 'Failed',
                            'reason' => 'Mail sending failed (Mail::failures)',
                        ];

                        continue;
                    }

                    $successCount++;
                    $statusList[] = [
                        'employee' => $employeeFullName,
                        'email' => $managerEmail,
                        'status' => 'Success',
                    ];
                } catch (\Exception $e) {
                    $statusList[] = [
                        'employee_id' => $id,
                        'email' => $managerEmail ?? 'N/A',
                        'status' => 'Failed',
                        'reason' => $e->getMessage(),
                    ];
                }
            }

            if ($chunkIndex < count($chunks) - 1) {
                sleep(3);
            }
        }

        return response()->json([
            'summary' => "{$successCount} emails sent successfully.",
            'details' => $statusList,
        ]);
    }

    public function payrollPayRun()
    {
        $breadcrumbs = CentralLogics::getBreadcrumbs();
        $pageTitle = 'Pay Runs';

        $user = Auth::user();
        $business_id = $user->emp_b_id;

        $employee_salaries = SalaryEmployeeSalary::with(['fh_employee', 'salary_earnings'])
            ->where('es_b_id', $business_id)
            ->get();

        $generate_salary = $this->getMonthlySalary($business_id, $employee_salaries);

        foreach ($employee_salaries as $employee) {
            $salaryData = collect($generate_salary)->firstWhere('employee_id', (int) $employee->es_emp_id);
            if ($salaryData) {
                $employee->total_days_in_month = $salaryData['total_days_in_month'];
                $employee->total_month_working_days = $salaryData['total_month_working_days'] ?? 0;
                $employee->totalEarnings = $salaryData['totalEarnings'] ?? 0;
                $employee->monthly_salary = $salaryData['monthly_salary'] ?? 0;

                // $employee->deductions = $salaryData[ 'pf' ] + $salaryData[ 'esic' ] + $salaryData[ 'professional_tax' ];
                // $employee->net_pay = $salaryData[ 'net_salary' ];
            } else {
                $employee->total_days_in_month = 0;
                $employee->total_month_working_days = 0;
                $employee->totalEarnings = 0;
                $employee->monthly_salary = 0;
            }
        }

        return view('admin.payroll.pay-runs', compact('breadcrumbs', 'pageTitle', 'employee_salaries'));
    }

    public function getMonthlySalary($business_id, $employee_salaries)
    {
        $user = Auth::user();
        $business_id = $user->emp_b_id;
        // $employees = Employee::where( 'emp_b_id', $business_id )->where( 'emp_id', 787 )->get();
        $employees = Employee::where('emp_b_id', $business_id)->where('is_super_admin', 0)->get();

        $month = $request->month ?? now()->format('m');

        $year = $request->year ?? now()->format('Y');

        // $monthFilter = Carbon::now()->format( 'Y-m' );

        // $month = $request->month ?? '11';
        // // Default November
        // $year = $request->year ?? '2024';
        // Default 2024

        $monthFilter = Carbon::createFromFormat('Y-m', "$year-$month")->format('Y-m');

        $salaries = [];
        foreach ($employees as $employee) {
            $employeeSalary = $employee_salaries->where('es_emp_id', $employee->emp_id)->first();
            $weekOfDates = CentralLogics::getWeekOffDates($employee, $year, $month);

            $salaries[$employee->emp_id] = $this->calculateMonthlySalary($employee, $monthFilter, $employeeSalary, $business_id);
        }

        return $salaries;
    }

    public function calculateMonthlySalary($employee, $monthFilter, $employeeSalary, $business_id)
    {

        $business = DB::table('businesses')->where('b_id', $business_id)->first();
        $business_name = $business->b_name ?? 'null';
        $branch = DB::table('branches')
            ->where('br_id', $employee->emp_br_id)
            ->first();
        $branch_name = $branch->br_name ?? 'null';

        $stateId = $branch->br_s_id ?? null; // Branch ki state ID
        $state = DB::table('states')->where('s_id', $stateId)->first();

        $monthName = Carbon::parse($monthFilter)->format('F Y');

        // Fetch attendance summary from database
        $attendanceSummary = DB::table('attendance_summaries')
            ->where('as_emp_id', $employee->emp_id)
            ->where('as_year_month', $monthFilter)
            ->first();

        if (! $attendanceSummary) {
            return [
                'error' => 'Attendance summary not found for employee ID '.$employee->emp_id,
            ];
        }

        if (! $attendanceSummary) {
            $presentCount = 0;
            $leaveCount = 0;
            $holidayCount = 0;
            $weekOffCount = 0;
            $absentCount = 0;
            $overtimeCount = 0;
        } else {
            $presentCount = $attendanceSummary->as_total_present ?? 0;
            $leaveCount = $attendanceSummary->as_total_leave ?? 0;
            $holidayCount = $attendanceSummary->as_total_holiday ?? 0;
            $weekOffCount = $attendanceSummary->as_total_weekoff ?? 0;
            $absentCount = $attendanceSummary->as_total_absent ?? 0;
            $overtimeCount = $attendanceSummary->as_total_overtime_hours ?? 0;
        }

        $totalDaysInMonth = Carbon::parse($monthFilter)->daysInMonth;
        $totalMonthWorkingDays = max($totalDaysInMonth - $weekOffCount, 1);
        $totalDaysWorked = $presentCount + $weekOffCount + $holidayCount;
        $workableDays = $totalDaysInMonth;

        // Monthly Salary Calculation
        $monthlySalary = round($employeeSalary->es_monthly_ctc ?? 0, 2);
        $perDaySalary = round($monthlySalary / $workableDays, 2);
        $workedDaysSalary = round($perDaySalary * $totalDaysWorked, 2);
        // Fetch Earnings and Deductions
        $earnings = DB::table('salary_allowances')->where('sa_b_id', $employee->emp_b_id)->get();
        $deductions = DB::table('statutory_deductions')->where('std_b_id', $employee->emp_b_id)->get();

        // Basic Salary Calculation
        $basicEarning = collect($earnings)->firstWhere('sa_earning_type_id', 360);
        $basicPercentage = $basicEarning->sa_threshold_value ?? 0;
        $basicSalary = round($workedDaysSalary * ($basicPercentage / 100), 2);

        // Fetch employee earnings from employee_salary_earnings table
        $employeeEarnings = DB::table('employee_salaries_earnings')
            ->where('es_e_emp_id', $employee->emp_id)
            ->get()
            ->keyBy('sa_earning_type_id'); // Index earnings by earning type ID for quick lookup

        // Fetch salary allowances based on business ID only
        $earnings = DB::table('salary_allowances as sa')
            ->join('employees as e', 'e.emp_b_id', '=', 'sa.sa_b_id') // Join employees to match business ID
            ->leftJoin('employee_salaries_earnings as ese', function ($join) {
                $join->on('ese.es_e_emp_id', '=', 'e.emp_id')
                    ->on('ese.es_e_cal_type_id', '=', 'sa.sa_calculation_type'); // Match calculation type
            })
            ->leftJoin('employee_salaries as es', 'es.es_emp_id', '=', 'e.emp_id') // Get salary details
            ->where('sa.sa_b_id', $employee->emp_b_id)
            ->where('e.emp_id', $employee->emp_id)
            ->select('sa.*', 'sa.sa_threshold_value as ese_threshold', 'es.es_monthly_ctc', 'ese.es_e_amount as flat_amount')
            ->get();

        // dd($earnings);

        $totalAllowances = 0;
        $allowancesBreakdown = [];

        foreach ($earnings as $earning) {
            $allowanceAmount = 0;

            if ($earning->sa_calculation_type == 346) {
                // Allowance based on % of CTC
                $allowanceAmount = round($basicSalary);
            } elseif ($earning->sa_calculation_type == 347) {
                // Allowance based on % of Basic
                $allowanceAmount = round($basicSalary * ((float) $earning->sa_threshold_value / 100), 2);
            } elseif ($earning->sa_calculation_type == 348) {
                // Flat amount allowance
                $allowanceAmount = round($earning->flat_amount ?? 0, 2);
            }

            // Ensure array key exists and accumulate allowanceAmount
            if (! isset($allowancesBreakdown[$earning->sa_earning_type_id])) {
                $allowancesBreakdown[$earning->sa_earning_type_id] = 0;
            }

            $allowancesBreakdown[$earning->sa_earning_type_id] = number_format($allowanceAmount, 2);

            // Convert and sum up the values properly
            $totalAllowances = array_sum(array_map(fn ($value) => (float) str_replace(',', '', $value), $allowancesBreakdown));
        }

        // Calculate Remaining Allowance
        $otherAllowance = $workedDaysSalary - $totalAllowances;
        $allowancesBreakdown['Remaining'] = number_format($otherAllowance, 2);

        // ✅ **Now add "Remaining" to the total**
        $totalAllowances += $otherAllowance;
        $grossSalary = $totalAllowances;
        // Debugging Output
        // dd($allowancesBreakdown, $totalAllowances, $workedDaysSalary);

        $isPfEnabled = ($employee->emp_is_pf_enabled == 1);

        $totalEmployeeDeductions = 0;
        $totalEmployerDeductions = 0;
        $deductionsBreakdown = [];
        $professionalTax = 0;

        foreach ($deductions as $deduction) {
            $deductionAmountEmployee = 0;
            $deductionAmountEmployer = 0;

            // Skip PF deduction if employee is not enabled for PF
            if ($deduction->std_deduction_type_id == 351 && $employee->emp_is_pf_enabled == 0) {
                continue;
            }

            if ($deduction->std_deduction_type_id == 351) { // EPF
                $basicForPF = min($basicSalary, 15000);         // EPF Wage Limit Applied

                // Employee's EPF Contribution (12%)
                $deductionAmountEmployee = round($basicForPF * (12 / 100), 2);

                // Employer's EPS Contribution (8.33%)
                $epsAmount = round($basicForPF * (8.33 / 100), 2);

                // Employer's EPF Contribution (3.67%)
                $epfAmount = round($basicForPF * (3.67 / 100), 2);

                // Employer's EDLIS Contribution (0.50%)
                $edlisAmount = round($basicForPF * (0.50 / 100), 2);

                // EPF Admin Charges (0.50%)
                $epfAdminAmount = round($basicForPF * (0.50 / 100), 2);

                // EDLIS Admin Charges (0.01%)
                $edlisAdminAmount = round($basicForPF * (0.01 / 100), 2);

                // Store all under 351
                $deductionsBreakdown[351] = [
                    'Employee Contribution (12%)' => $deductionAmountEmployee,
                    'Employer EPS (8.33%)' => $epsAmount,
                    'Employer EPF (3.67%)' => $epfAmount,
                    'Employer EDLIS (0.50%)' => $edlisAmount,
                    'EPF Admin (0.50%)' => $epfAdminAmount,
                    'EDLIS Admin (0.01%)' => $edlisAdminAmount,
                ];

                // Add to totals
                $totalEmployeeDeductions += $deductionAmountEmployee;
                $totalEmployerDeductions += ($epsAmount + $epfAmount + $edlisAmount + $epfAdminAmount + $edlisAdminAmount);
            }

            // Other deductions like ESIC
            if ($deduction->std_deduction_type_id == 352 && $basicSalary <= 21000) {
                $deductionAmountEmployee = round($basicSalary * (0.75 / 100), 2);
                $deductionAmountEmployer = round($basicSalary * (3.25 / 100), 2);

                $deductionsBreakdown[352] = [
                    'Employee Contribution (0.75%)' => $deductionAmountEmployee,
                    'Employer Contribution (3.25%)' => $deductionAmountEmployer,
                ];

                $totalEmployeeDeductions += $deductionAmountEmployee;
                $totalEmployerDeductions += $deductionAmountEmployer;
            }
        }

        // **Apply Professional Tax based on stateId (independently)**
        $professionalTax = 0;
        if ($stateId) {
            $taxSlab = DB::table('professional_tax_master')
                ->where('ptm_s_id', $stateId)
                ->where('ptm_income_from', '<=', $grossSalary)
                ->where(function ($query) use ($grossSalary) {
                    $query->where('ptm_income_to', '>=', $grossSalary)
                        ->orWhereNull('ptm_income_to');
                })
                ->first();

            $professionalTax = $taxSlab ? $taxSlab->ptm_tax_amount : 0;
        }

        // Store PT under deductions separately (Type 354 or another appropriate type)
        $deductionsBreakdown[353] = [
            'Employee Contribution' => number_format($professionalTax, 2),
            'Employer Contribution' => '0.00',
        ];
        $deductionsBreakdown[353]['Professional Tax'] = number_format($professionalTax, 2);
        $totalEmployeeDeductions += $professionalTax;

        // LWF Calculation
        $lwfEmployee = 0;
        $lwfEmployer = 0;
        $currentMonth = Carbon::now()->month;

        if ($stateId) {
            $lwfData = DB::table('labour_welfare_fund_master')
                ->where('state_id', $stateId)
                ->first();

            if ($lwfData) {
                if ($lwfData->cycle_id == 355) {
                    $lwfEmployee = $lwfData->lwf_employee_contri;
                    $lwfEmployer = $lwfData->lwf_employer_contri;
                } elseif ($lwfData->cycle_id == 356 && in_array($currentMonth, [6, 12])) {
                    $lwfEmployee = $lwfData->lwf_employee_contri;
                    $lwfEmployer = $lwfData->lwf_employer_contri;
                } elseif ($lwfData->cycle_id == 4162 && $currentMonth == 12) {
                    $lwfEmployee = $lwfData->lwf_employee_contri;
                    $lwfEmployer = $lwfData->lwf_employer_contri;
                }
            }
        }

        // **Ensure 354 exists with default 0.00 if not present**
        if (! isset($salaryDetails['deductions_breakdown'][354])) {
            $salaryDetails['deductions_breakdown'][354] = [
                'employee' => ['Employee Contribution' => number_format($lwfEmployee, 2)],
                'employer' => ['Employer Contribution' => number_format($lwfEmployer, 2)],
            ];
        }

        // Ensure Totals Include 0 If No LWF
        $totalEmployeeDeductions += $lwfEmployee;
        $totalEmployerDeductions += $lwfEmployer;

        $formattedDeductionsBreakdown = [];

        foreach ($deductionsBreakdown as $type => $deductions) {
            if (! isset($formattedDeductionsBreakdown[$type])) {
                $formattedDeductionsBreakdown[$type] = [
                    'employee' => [],
                    'employer' => [],
                ];
            }

            foreach ($deductions as $key => $value) {
                if (strpos(strtolower($key), 'employee') !== false) {
                    $formattedDeductionsBreakdown[$type]['employee'][$key] = number_format($value, 2);
                } elseif (strpos(strtolower($key), 'employer') !== false) {
                    $formattedDeductionsBreakdown[$type]['employer'][$key] = number_format($value, 2);
                }
            }

            // **Ensure Default 0.00 if missing**
            if (empty($formattedDeductionsBreakdown[$type]['employee'])) {
                $formattedDeductionsBreakdown[$type]['employee']['Employee Contribution'] = '0.00';
            }
            if (empty($formattedDeductionsBreakdown[$type]['employer'])) {
                $formattedDeductionsBreakdown[$type]['employer']['Employer Contribution'] = '0.00';
            }
        }

        // // Final Output
        // return [
        //     'deductions_breakdown' => $formattedDeductionsBreakdown,
        //     'total_employee_deductions' => number_format($totalEmployeeDeductions, 2),
        //     'total_employer_deductions' => number_format($totalEmployerDeductions, 2),
        // ];

        $grossSalary = round($basicSalary + $totalAllowances, 2);
        $netSalary = round($totalAllowances - $totalEmployeeDeductions, 2);

        return [
            'branch_name' => $branch_name,
            'month' => $monthFilter,
            'month_name' => $monthName,
            'business_name' => $business_name,
            'total_days_in_month' => $totalDaysInMonth,
            'week_off_count' => $weekOffCount,
            'total_month_working_days' => $totalMonthWorkingDays,
            'total_days_worked' => $totalDaysWorked,
            'present_days' => $presentCount,
            'workable_days' => $workableDays,
            'monthly_salary' => number_format($monthlySalary, 2),
            'per_day_salary' => number_format($perDaySalary, 2),
            'worked_days_salary' => number_format($workedDaysSalary, 2),
            'employee_id' => $employee->emp_id,
            'emp_code' => $employee->emp_code,
            'employee_full_name' => $employee->emp_full_name,
            'basic_salary' => number_format($basicSalary, 2),
            'total_earnings' => number_format($totalAllowances, 2),
            'earnings_breakdown' => $allowancesBreakdown,
            'deductions_breakdown' => $formattedDeductionsBreakdown,
            'total_employee_deductions' => number_format($totalEmployeeDeductions, 2),
            'total_employer_deductions' => number_format($totalEmployerDeductions, 2),
            'gross_salary' => number_format($grossSalary, 2),
            'professionalTax' => $professionalTax,
            'net_salary' => number_format($netSalary, 2),
        ];
    }

    // private function calculateLWF( $basicSalary, $business_id )
    // {
    //     dd( $business_id );
    //     // ✅ Step 1: Fetch LWF Settings for the Business
    //     $lwf = DB::table( 'labour_welfare_funds' )
    //         ->where( 'status', 1 ) // Ensure LWF is enabled
    //         ->where( 'lwf_b_id', $business_id ) // Filter by business ID
    //         ->first();

    //     // Initialize LWF Contributions
    //     $lwfEmployeeContribution = 0;
    //     $lwfEmployerContribution = 0;

    //     // ✅ Step 2: Calculate LWF Contributions ( if applicable )
    //     if ( $lwf && $basicSalary >= $lwf->threshold ) {
    // Apply LWF only if salary meets threshold
    //         $lwfEmployeeContribution = ( $basicSalary * ( $lwf->employee_contribution_rate / 100 ) );
    //         $lwfEmployerContribution = ( $basicSalary * ( $lwf->employer_contribution_rate / 100 ) );
    //     }

    //     return [
    //         'employee' => $lwfEmployeeContribution,
    //         'employer' => $lwfEmployerContribution
    // ];
    // }

    public function calculateProfessionalTax($monthlySalary, $branch_id, $business_id)
    {
        return ProfessionalTaxSlab::where('pts_br_id', $branch_id)
            ->where('pts_b_id', $business_id)
            ->where('pts_income_from', '<=', $monthlySalary)
            ->where('pts_income_to', '>=', $monthlySalary)
            ->value('pts_tax_amount') ?? 0;
    }

    public function calculateTDS($grossSalary, $business_id)
    {
        $tdsSlabs = DB::table('fh_tds_tax_slabs')
            ->where('tds_b_id', $business_id)
            ->orderBy('tds_income_from', 'asc')
            ->get();

        $taxableIncome = $grossSalary - 50000;
        // Standard Deduction
        if ($taxableIncome <= 0) {
            return 0;
        }

        // No tax if below standard deduction

        $tdsAmount = 0;

        foreach ($tdsSlabs as $slab) {
            $minIncome = $slab->tds_income_from;
            $maxIncome = $slab->tds_income_to;
            $taxPercentage = $slab->tds_tax_percentage;

            // Determine taxable amount in this slab
            if ($taxableIncome > $minIncome) {
                $incomeInThisSlab = min($taxableIncome, $maxIncome) - $minIncome;
                $taxInThisSlab = ($incomeInThisSlab * $taxPercentage) / 100;
                $tdsAmount += $taxInThisSlab;
            }
        }

        return round($tdsAmount, 2);
    }

    public function loan(Request $request)
    {
        $user = Auth::user();
        $breadcrumbs = CentralLogics::getBreadcrumbs();
        $pageTitle = 'Loan/Advance';

        if ($request->ajax()) {
            // Define dynamic conditions for filtering
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['lnr_b_id', $user->emp_b_id],
                ],
                [
                    'method' => 'select',
                    'args' => [
                        'id',
                        'lnr_b_id',
                        'lnr_emp_id',
                        'lnr_advance_type',
                        'lnr_request_subject',
                        'lnr_requested_amount',
                        'lnr_start_date',
                        'lnr_installments',
                        'lnr_installment_amount',
                        'lnr_description',
                    ],
                    'relation' => [
                        'fh_master_type:m_id,m_name',
                        'fh_employee:emp_id,emp_full_name',
                    ],
                ],
                [
                    'method' => 'sortBy',
                    'args' => ['id', 'lnr_emp_id', 'lnr_advance_type', 'lnr_request_subject', 'lnr_requested_amount', 'lnr_start_date'],
                ],
            ];

            // Specify searchable columns
            $searchColumns = ['lnr_advance_type', 'lnr_emp_id', 'lnr_request_subject', 'lnr_requested_amount', 'lnr_start_date'];
            $searchRelationships = [
                'fh_master_type' => ['m_id', 'm_name'],
                'fh_employee' => ['emp_id', 'emp_full_name'],
            ];

            // Fetch data for server-side processing
            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new LoanRequest,
                dynamicConditions: $dynamicConditions,
            ))->getServerSideDataTable();

            $rowData = [];
            $i = 0;

            foreach ($list as $key => $val) {
                $i++;
                $row = [];
                $row[] = $i;                                                                                  // S. No.
                $row[] = $val->fh_employee ? $val->fh_employee->emp_full_name : '-';                          // Employee
                $row[] = $val->fh_master_type ? $val->fh_master_type->m_name : '-';                           // Type
                $row[] = $val->lnr_request_subject ?? '-';                                                    // Title/Subject
                $row[] = $val->lnr_requested_amount ? number_format($val->lnr_requested_amount, 2) : '-';     // Requested Amount
                $row[] = $val->lnr_start_date ? Carbon::parse($val->lnr_start_date)->format('d-M-Y') : '-';   // Start Date
                $row[] = $val->lnr_installments ?? '-';                                                       // Installments
                $row[] = $val->lnr_installment_amount ? number_format($val->lnr_installment_amount, 2) : '-'; // Installment Amount

                // Action buttons
                $row[] = '<a class="btn btn-sm btn-info edit-loan-advance"
                        data-bs-target="#loanAdvanceModal" data-bs-toggle="modal"
                        data-id="'.$val->id.'"
                        data-type="'.$val->lnr_advance_type.'"
                        data-title="'.$val->lnr_request_subject.'"
                        data-employee="'.$val->lnr_emp_id.'"
                        data-amount="'.$val->lnr_requested_amount.'"
                        data-start_date="'.$val->lnr_start_date.'"
                        data-description="'.$val->lnr_description.'"
                        data-installment_amount="'.$val->lnr_installment_amount.'"
                        data-installments="'.$val->lnr_installments.'">
                        <i class="feather feather-edit"></i>
                    </a>
                    <button class="btn btn-sm btn-danger deleteloanAdvanceBtn" data-id="'.$val->id.'">
                        <i class="feather feather-trash"></i>
                    </button>';

                $rowData[] = $row;
            }

            $output = [
                'draw' => $request->input('draw'),
                'recordsTotal' => count($list),
                'recordsFiltered' => (new DynamicModelDataTableHelper(
                    eloquentModel: new LoanRequest,
                    dynamicConditions: $dynamicConditions,
                ))->countFilteredServerSideDataTable(),
                'data' => $rowData,
            ];

            return response()->json($output);
        }

        // Define table columns for the view
        $columns = [
            'S. No.',
            'Employee',
            'Type',
            'Subject',
            'Requested Amount',
            'Start Date',
            'Installments',
            'Installment Amount',
            'Action',
        ];

        $employees = Employee::where('emp_b_id', $user->emp_b_id)->where('emp_role_id', '!=', 1)->get();
        $type = MasterTable::where('m_group', 'FINANCE')->get();

        return view('admin.payroll.loans', compact('columns', 'breadcrumbs', 'pageTitle', 'employees', 'type'));
    }

    public function saveLoanAdvance(Request $request)
    {
        $request->validate([
            'lnr_emp_id' => 'required|exists:fh_employees,emp_id',
            'lnr_advance_type' => 'required',
            'request_subject' => 'required|string',
            'requested_amount' => 'required|numeric|min:0',
            'installment_amount' => 'nullable|numeric|min:0',
            'installments' => 'required|numeric|min:1',
            'loan_start_date' => 'required|date',
        ]);

        DB::beginTransaction();

        try {
            // Check if editing existing loan or creating new
            if ($request->pla_id) {
                $loan = LoanRequest::findOrFail($request->pla_id);
                $loan->update([
                    'lnr_advance_type' => $request->lnr_advance_type,
                    'lnr_amount' => $request->requested_amount,
                    'lnr_start_date' => $request->loan_start_date,
                    'lnr_subject' => $request->request_subject,
                    'lnr_description' => $request->description,
                    'lnr_reason' => $request->reason,
                ]);

                // Delete existing installments, we'll recreate them
                PayrollLoanInstallment::where('pli_loan_id', $loan->lnr_id)->delete();
            } else {
                $loan = LoanRequest::create([
                    'lnr_emp_id' => $request->lnr_emp_id,
                    'lnr_b_id' => auth()->user()->emp_b_id,
                    'lnr_advance_type' => $request->lnr_advance_type,
                    'lnr_amount' => $request->requested_amount,
                    'lnr_start_date' => $request->loan_start_date,
                    'lnr_subject' => $request->request_subject,
                    'lnr_description' => $request->description,
                    'lnr_reason' => $request->reason,
                    'lnr_status' => 'pending', // default pending
                ]);
            }

            // Calculate installments dynamically
            $installmentAmount = $request->installment_amount ?? 0;
            $remainingInstallments = $request->remaining_installments ?? $request->installments;
            $openingBalance = $request->requested_amount - ($request->already_paid_amount ?? 0);

            $startDate = Carbon::parse($request->loan_start_date)->startOfMonth();

            for ($i = 1; $i <= $remainingInstallments; $i++) {
                $dueDate = $startDate->copy()->addMonths($i - 1);
                $amount = $installmentAmount > 0 ? min($installmentAmount, $openingBalance) : 0;
                $remainingBalance = $openingBalance - $amount;

                PayrollLoanInstallment::create([
                    'pli_loan_id' => $loan->lnr_id,
                    'pli_b_id' => $loan->lnr_b_id,
                    'pli_installment_no' => $i,
                    'pli_opening_balance' => $openingBalance,
                    'pli_amount' => $amount,
                    'pli_principal' => $amount,
                    'pli_interest' => 0,
                    'pli_rem_bal' => $remainingBalance,
                    'pli_due_date' => $dueDate->format('Y-m-d'),
                    'pli_month' => $dueDate->month,
                    'pli_year' => $dueDate->year,
                    'pli_status' => 'pending',
                ]);

                $openingBalance = $remainingBalance;
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Loan / Advance saved successfully!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: '.$e->getMessage(),
            ], 500);
        }
    }

    public function createOrUpdateLoan(Request $request)
    {

        // Validate the incoming request
        $validatedData = $request->validate([
            'type' => 'required|integer|exists:master_table,m_id',
            // 'title' => 'required|string|max:255',
            'employee' => 'required|integer|exists:employees,emp_id',
            'amount' => 'required|numeric|min:0',
            'provided-date' => 'required|date',
            'description' => 'nullable|string|max:500',
            'installment-amount' => 'required|numeric|min:0',
            'total-installments' => 'required|integer|min:1',
            'installment-start' => 'required|date',
        ]);

        $user = Auth::user();

        // Use updateOrCreate to either update or create a loan
        $loan = PayrollLoanAccount::updateOrCreate(
            ['id' => $request->input('pla_id')],
            [
                'is_active' => $request->input('pla_id') ? 1 : 0,
                'type' => $validatedData['type'],
                // 'title' => $validatedData['title'],
                'pla_emp_id' => $validatedData['employee'],
                'pla_b_id' => $user->emp_b_id,
                'loan_amount' => $validatedData['amount'],
                'provided_date' => $validatedData['provided-date'],
                'description' => $validatedData['description'],
                'installment_amount' => $validatedData['installment-amount'],
                'installments' => $validatedData['total-installments'],
                'installment_start_date' => $validatedData['installment-start'],
            ]
        );

        // Return response
        return response()->json([
            'status' => true,
            'message' => $loan->wasRecentlyCreated ? 'Loan created successfully!' : 'Loan updated successfully!',
            'loan' => $loan,
        ]);
    }

    public function deleteLoan($id)
    {
        $user = Auth::user();
        if ($user) {
            PayrollLoanAccount::destroy($id);

            return response()->json(['success' => 'Loan / Advance deleted successfully']);
        } else {
            abort('404');
        }
    }

    public function destroy(string $id)
    {
        if ($this->user) {
            $id = Crypt::decrypt($id);
            $result = CentralLogics::dynamicDelete(IncomeTaxSlab::class, $id);

            if (isset($result['success'])) {
                return response()->json(['success' => $result['success'], 'message' => 'TDS has been deleted successfully']);
            } else {
                return response()->json(['error' => $result['error']], 400);
            }
        } else {
            abort(404);
        }
    }

    public function searchEmployee(Request $request)
    {
        $status = $request->input('status');
        $query = $request->input('query');

        $employees = Employee::select('emp_id', 'emp_full_name')
            ->where('emp_b_id', $this->user->emp_b_id)
            ->where('emp_status', $status)
            ->where('emp_full_name', 'like', '%'.$query.'%')
            ->orWhere('emp_code', 'like', '%'.$query.'%')
            ->get();

        // dd($employees);
        return response()->json($employees);
    }

    public function getEmployeeLoanBalance()
    {
        dd('hi');
    }
}
