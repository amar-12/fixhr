<?php

namespace App\Http\Controllers\Web\Admin;

use Exception;
use Carbon\Carbon;
use App\Models\Business;
use App\Models\Employee;
use App\Models\MasterTable;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\FinancialYear;
use App\Helpers\CentralLogics;
use App\Models\AdhocComponent;
use App\Models\SalaryAllowance;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\SmHistoryExport;
use App\Models\SalaryPolicySalary;
use App\Models\StatutoryDeduction;
use App\Models\SalaryMasterHistory;
use App\Models\SalaryPayrollPeriod;
use App\Models\SalaryPayrollRecord;
use App\Http\Controllers\Controller;
use App\Models\PayrollMasterSetting;
use App\Models\SalaryEmployeeSalary;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EmployeeSalaryExport;
use App\Helpers\RolePermissionLogics;
use App\Imports\EmployeeSalaryImport;
use Illuminate\Support\Facades\Crypt;
use App\Models\PayrollTemplateEarning;
use App\Models\SalaryEmployeeEarnings;
use Illuminate\Support\Facades\Storage;
use App\Models\SalaryEmployeeDeductions;
use App\Models\SalaryEmployerDeductions;
use App\Exports\SalaryMasterSampleExport;
use Maatwebsite\Excel\Excel as ExcelFormat;
use App\Exports\Salary\EmployeeSalarySampleExport;
use App\Models\AdvanceLoanSetting;
use App\Models\PayslipConfiguration;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;

class EmployeePayrollController extends Controller
{

    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function payroll()
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;
        $salaryPolicies = SalaryPolicySalary::where('ps_b_id', $user->emp_b_id)->count();

        $salaryAllowances = SalaryAllowance::where('sa_b_id', $user->emp_b_id)->count();
        $fincialyear = FinancialYear::where('fy_b_id', $user->emp_b_id)->count();
        $AdhocComponent = AdhocComponent::where('ac_adhoc_business_id', $user->emp_b_id)->count();
        $payroll_cycle = MasterTable::select('m_id', 'm_name')->where('m_group', 'PAYMENT_CYCLE')->get();
        $payrollPolicies = PayrollMasterSetting::where('pms_b_id', $user->emp_b_id)->count();
        $advanceLoanConf = AdvanceLoanSetting::where('als_b_id', $user->emp_b_id)->count();
        $payslipConf = PayslipConfiguration::where('pc_b_id', $user->emp_b_id)->count();

        $payroll_cycle = MasterTable::select('m_id', 'm_name')
            ->where('m_group', 'PAYMENT_CYCLE')
            ->get();

        $existingSetting = PayrollMasterSetting::where('pms_b_id', $businessId)->first();
        $payrollPolicies = $existingSetting ? 1 : 0;

        $fnf_data = Business::where('b_id', $businessId)->first();
        $modules = $fnf_data->b_fnf_modules ?? [];
        $totalapprovaldata = is_array($modules) ? count($modules) : 0;



        return view('admin.setting.payroll.payroll', compact('salaryPolicies', 'salaryAllowances', 'fincialyear', 'AdhocComponent', 'payroll_cycle', 'payrollPolicies', 'advanceLoanConf', 'payslipConf', 'existingSetting', 'totalapprovaldata'));
    }

    public function index(Request $request)
    {
        if ($this->user) {
            if ($request->ajax()) {
                $dynamicConditions = [
                    [
                        'method' => 'where',
                        'args' => ['ps_b_id', $this->user->emp_b_id]
                    ],
                    [
                        'method' => 'select',
                        'args' => ['ps_id', 'ps_b_id', 'ps_name', 'ps_description', 'ps_basic_salary_percentage', 'ps_hra_allowance_percentage', 'ps_conveyance_allowance_threshhold', 'ps_medical_allowance_threshhold', 'ps_employee_pf_percentage', 'ps_employer_pf_percentage', 'ps_employee_esic_percentage', 'ps_employer_esic_percentage', 'ps_pf_threshhold', 'ps_esic_threshhold', 'updated_at'],
                        'relation' => ['fh_business:b_id']
                    ],
                    [
                        'method' => 'sortBy',
                        'args' => ['ps_id', 'ps_b_id', 'ps_name', 'ps_description', 'ps_basic_salary_percentage', 'ps_hra_allowance_percentage', 'ps_conveyance_allowance_threshhold', 'ps_medical_allowance_threshhold', 'ps_employee_pf_percentage', 'ps_employer_pf_percentage', 'ps_employee_esic_percentage', 'ps_employer_esic_percentage', 'ps_pf_threshhold', 'ps_esic_threshhold', 'updated_at'],
                    ]
                ];

                $searchColumns = ['ps_b_id', 'ps_b_id', 'ap_name', 'ap_description', 'ap_grace_period_minutes', 'ap_max_daily_working_hours', 'ap_max_overtime_hours', 'ap_overtime_rate', 'ap_late_penalty_rate', 'ap_early_leaving_penalty_rate', 'ap_attendance_bonus_rate', 'ap_holiday_overtime_rate', 'ap_status', 'updated_at', 'ps_b_id'];

                $list = (new DynamicModelDataTableHelper(
                    eloquentModel: new SalaryPolicySalary(),
                    dynamicConditions: $dynamicConditions,
                    searchColumns: $searchColumns,
                ))->getServerSideDataTable();

                $rowData = [];
                $i = 0;
                foreach ($list as $key => $val) {

                    $i++;
                    $row = [];
                    $row[] = $i;
                    $row[] = $val->ps_name;
                    $row[] = $val->ps_description;
                    $row[] = '<span class="fs-11 fw-bold">W.E.F. </span>
                    <span class="with-effect-from-badge fs-10">' . Carbon::parse($val->updated_at)->format('d-M-Y h:i A') . '</span>';
                    $row[] = '<button class="btn btn-sm btn-info edit-policy"
                                        data-id="' . $val->ps_id . '" data-b_id="' . $val->ps_b_id . '" data-name="' . $val->ps_name . '" data-description="' . $val->ps_description . '" data-ps_basic_salary_percentage="' . $val->ps_basic_salary_percentage . '"
                                        data-ps_hra_allowance_percentage="' . $val->ps_hra_allowance_percentage . '" data-ps_conveyance_allowance_threshhold="' . $val->ps_conveyance_allowance_threshhold . '" data-ps_medical_allowance_threshhold="' . $val->ps_medical_allowance_threshhold . '" data-ps_expiration_date="' . $val->ps_expiration_date . '" data-holiday="' . $val->ap_holiday_overtime_rate . '" data-ps_employee_pf_percentage="' . $val->ps_employee_pf_percentage . '" data-ps_employer_pf_percentage="' . $val->ps_employer_pf_percentage . '" data-ps_employee_esic_percentage="' . $val->ps_employee_esic_percentage . '" data-ps_employer_esic_percentage="' . $val->ps_employer_esic_percentage . '" data-ps_pf_threshhold="' . $val->ps_pf_threshhold . '" data-ps_esic_threshhold="' . $val->ps_esic_threshhold . '">
                                        <i class="feather feather-edit"></i>
                                    </button>
                                    <button type="submit" class="btn btn-sm btn-danger delete-policy" data-id="' . $val->ps_id . '"><i class="feather feather-trash"></i>
                                    </button>';

                    $rowData[] = $row;
                }

                $output = [
                    "draw" => $request->input('draw'),
                    "recordsTotal" => sizeof($list),
                    "recordsFiltered" => (new DynamicModelDataTableHelper(
                        eloquentModel: new SalaryPolicySalary(),
                        dynamicConditions: $dynamicConditions,
                    ))->countFilteredServerSideDataTable(),
                    "data" => $rowData,
                ];

                return json_encode($output);
            }

            $columns = [
                'S. No.',
                'Name',
                'Description',
                '',
                'Action',
            ];

            $policies = SalaryPolicySalary::where('ps_b_id', $this->user->emp_b_id)->get();
            return view('admin.setting.payroll.payroll-policies', compact('policies', 'columns'));
        } else {
            abort('404');
        }
    }

    public function store(Request $request)
    {


        if ($this->user) {
            $request->merge([
                'ps_b_id' => $this->user->emp_b_id
            ]);
            $validated = $request->validate([
                'ps_b_id' => 'required|integer',
                'ps_name' => 'required|string|max:255',
                'ps_description' => 'nullable|string',
                'ps_basic_salary_percentage' => 'required|numeric',
                'ps_hra_allowance_percentage' => 'required|numeric',
                'ps_conveyance_allowance_threshhold' => 'required|numeric',
                'ps_medical_allowance_threshhold' => 'required|numeric',
                'ps_employee_pf_percentage' => 'required|numeric',
                'ps_employer_pf_percentage' => 'required|numeric',
                'ps_employee_esic_percentage' => 'required|numeric',
                'ps_employer_esic_percentage' => 'required|numeric',
                'ps_pf_threshhold' => 'required|numeric',
                'ps_esic_threshhold' => 'required|numeric',
            ]);

            SalaryPolicySalary::updateOrCreate(
                ['ps_id' => $request->input('ps_id')],
                $validated
            );
            if ($request->input('ps_id')) {
                return response()->json(['success' => 'Policy updated successfully']);
            } else {
                return response()->json(['success' => 'Policy saved successfully']);
            }
            // return response()->json(['success' => 'Policy saved successfully']);
        } else {
            abort('404');
        }
    }



    public function destroy($id)
    {
        if ($this->user) {
            $data = SalaryPolicySalary::where('ps_id', $id)->delete();
            if ($data) {
                return response()->json(['success' => 'Policy deleted successfully']);
            }
            return response()->json(['error' => 'Policy not deleted']);
        } else {
            abort('404');
        }
    }


    public function emplyeeSalary(Request $request)
    {
        $user = Auth::user();

        $search = request()->input('searchFilter');
        $activeFilter = request()->input('activeFilter');

        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['emp_b_id', $user->emp_b_id]
                ],
                [
                    'method' => 'where',
                    'args' => ['emp_role_id', '!=', 1]
                ],
                [
                    'method' => 'with', // 🔹 Eager load relationships
                    'args' => [
                        'fh_business',
                        'fh_employee_salary',
                        'fh_department:d_id,d_name',
                        'fh_payroll_master_settings'
                    ]
                ],
                [
                    'method' => 'select',
                    'args' => ['emp_id', 'emp_b_id', 'emp_code', 'emp_fname', 'emp_mname', 'emp_lname', 'emp_full_name', 'emp_email', 'emp_date_of_joining', 'emp_phone', 'emp_d_id', 'updated_at'],
                    'relation' => [
                        'fh_business:b_id,b_payment_mode',
                        'fh_employee_salary:es_id,es_emp_id,es_monthly_ctc,es_monthly_gross,updated_at',
                        'fh_department:d_id,d_name',
                        'fh_payroll_master_settings:pms_id,pms_b_id,pms_payroll_cycle',
                    ]
                ],
                [
                    'method' => 'sortBy',
                    'args' => ['emp_id', 'emp_code', 'emp_full_name', 'updated_at', 'emp_date_of_joining'],
                ]
            ];


            if ($activeFilter != '') {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_status', $activeFilter]];
            }

            $searchRelationships = [
                'fh_employee_type' => ['m_name'],
                'fh_branch' => ['br_name'],
                'fh_department' => ['d_name'],
                'fh_work_mode' => ['m_name'],
                'fh_shift_type' => ['pst_name'],
                'fh_employee_status' => ['m_name'],
            ];

            $searchColumns = ['emp_id', 'emp_code', 'emp_fname', 'emp_mname', 'emp_lname', 'emp_full_name', 'emp_email', 'emp_date_of_joining', 'emp_phone', 'updated_at'];

            $searchRelationships = [
                'fh_employee_type' => ['m_name'],
                'fh_branch' => ['br_name'],
                'fh_department' => ['d_name'],
                'fh_work_mode' => ['m_name'],
                'fh_shift_type' => ['pst_name'],
                'fh_employee_status' => ['m_name']
            ];

            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new Employee(),
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
                searchRelationships: $searchRelationships
            ))->getServerSideDataTable();



            $rowData = [];
            $i = 0;
            foreach ($list as $val) {
                $i++;
                $row = [];

                $row[] = $i;
                $row[] = $val->emp_code;
                $row[] = $val->emp_full_name;
                $row[] = $val->fh_department->d_name;
                $row[] = date('d M Y', strtotime($val->emp_date_of_joining));

                $row[] = optional($val->fh_employee_salary)->es_monthly_ctc ?? '-';
                $row[] = optional($val->fh_employee_salary)->es_monthly_gross ?? '-';
                $updatedAt = optional($val->latest_salary_master_history)->wef;
                $row[] = $updatedAt ? $updatedAt->format('d M Y') : '-';

                // Step 1: Get business payment mode
                $businessPaymentMode = optional($val->fh_payroll_master_settings)->pms_payroll_cycle;


                // Step 2: Choose correct route based on payment mode
                if ($businessPaymentMode === 441) {
                    $editUrl = route('employee.salary.master.weekly', ['id' => Crypt::encrypt($val->emp_id)]);
                } elseif ($businessPaymentMode === 439) {
                    $editUrl = route('employee.salary.master.daily', ['id' => Crypt::encrypt($val->emp_id)]);
                } elseif ($businessPaymentMode === 440) {
                    $editUrl = route('employee.addEdit.payroll', ['id' => Crypt::encrypt($val->emp_id)]);
                } else {
                    // ❌ No Payroll Master Setting Found
                    $editUrl = '#';
                }


                // $row[] = '<a class="btn action-btns btn-sm btn-primary" href="' . (RolePermissionLogics::check_route_permission('admin/employee/payroll-add-edit/{id?}', 117) ? $editUrl : '') . '">
                //             <i class="feather feather-edit"></i>
                //         </a>
                //       <button class="btn btn-sm btn-danger delete-shift-type"
                //         data-id="' . $val->emp_id . '"><i class="feather feather-trash"></i>
                //       </button>';

                // $rowData[] = $row;

                $row[] = '
                <div class="btn-list ms-3">
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu p-2" style="min-width: 180px;">
                            <li>
                                <a class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2"
                                href="' . (RolePermissionLogics::check_route_permission('admin/employee/payroll-add-edit/{id?}', 117) && $editUrl !== '#' ? $editUrl : 'javascript:void(0)') . '"
                                ' . ($editUrl === '#' ? 'onclick="showPayrollSettingAlert()"' : '') . '>
                                    <i class="feather feather-edit"></i> Edit
                                </a>
                            </li>
                            <li>
                                <button class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2 delete-shift-type"
                                        data-id="' . $val->emp_id . '" type="button">
                                    <i class="feather feather-trash"></i> Delete
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>';

                $rowData[] = $row;
            }

            $output = [
                "draw" => intval($request->input('draw')),
                "recordsTotal" => sizeof($list),
                "recordsFiltered" => (new DynamicModelDataTableHelper(
                    eloquentModel: new Employee(),
                    dynamicConditions: $dynamicConditions,
                    searchColumns: $searchColumns,
                    searchRelationships: $searchRelationships
                ))->countFilteredServerSideDataTable(),
                "data" => $rowData,
            ];

            return response()->json($output);
        }

        $businessId = $user->emp_b_id;
        $payrollSetting = PayrollMasterSetting::where('pms_b_id', $businessId)->first();

        $missingSettingMessage = null;

        if (!$payrollSetting) {
            $missingSettingMessage = 'Please configure Payroll Master Settings before accessing Employee Payroll.';
        }

        $columns = [
            'S. No.',
            'Emp. ID',
            'Emp. Name',
            'Department',
            'DOJ',
            'CTC Salary',
            'Gross Salary',
            'WEF',
            'Action',
        ];
        $title = 'Employee Salary';
        $financialYears = FinancialYear::where('fy_b_id', $user->emp_b_id)->get();
        $business = Business::where('b_id', $user->emp_b_id)->first();


        return view('admin.employees.salary.index', compact('title', 'businessId', 'business', 'columns', 'financialYears'));
    }

    public function addEditDailyEmployeeSalary($emp_id = null)
    {
        try {
            $user = Auth::user();
            $emp_actual_id = Crypt::decrypt($emp_id);

            // $smhistory = SalaryMasterHistory::where('sm_emp_id', $emp_actual_id)->where('sm_emp_b_id', $user->emp_b_id)->get();
            $smhistory = SalaryMasterHistory::with('financial_years')->where('sm_emp_id', $emp_actual_id)
                ->where('sm_emp_b_id', $user->emp_b_id)
                ->orderBy('sm_id', 'desc')
                ->get();
            $smhistoryLastData = $smhistory->first();
            $employee = Employee::with('fh_designation')->where('emp_id', $emp_actual_id)
                ->firstOrFail();
            $financialYears = FinancialYear::get();

            $employee_list = Employee::where('emp_b_id', $user->emp_b_id)
                ->where('emp_status', 71)
                ->get();

            // Fetch existing salary record
            $emp_salary = SalaryEmployeeSalary::where('es_emp_id', $emp_actual_id)->first();

            $calculation_modes = MasterTable::where('m_group', 'CALCULATION_MODE')
                ->orderBy('m_name', 'desc')
                ->pluck('m_name', 'm_id')
                ->toArray();

            $payroll_structures = SalaryPolicySalary::where('ps_b_id', $user->emp_b_id)->get();
            $business_id = $user->emp_b_id; // Logged-in user ka business_id

            if (!$business_id) {
                return response()->json(['error' => 'Business ID not found'], 404);
            }

            $earnings = SalaryAllowance::where('sa_b_id', $business_id)->orderBy('sa_sequence_valu', 'asc')->get();
            $deductions = StatutoryDeduction::join('master_table', 'statutory_deductions.std_deduction_type_id', '=', 'master_table.m_id')
                ->where('std_b_id', $business_id)
                ->select('statutory_deductions.*', 'master_table.m_name as deduction_type_name')
                ->get();

            $employerDeductions = StatutoryDeduction::join('master_table', 'statutory_deductions.std_deduction_type_id', '=', 'master_table.m_id')
                ->where('std_b_id', $business_id)
                ->select('statutory_deductions.*', 'master_table.m_name as deduction_type_name')
                ->get();
            // dd($deductions);

            $emp_earnings = [];
            $emp_deductions = [];
            $emplyer_deductions = [];

            if ($emp_salary) {
                $title = 'Update Payroll';

                $emp_earnings = SalaryEmployeeEarnings::where('es_e_emp_id', $emp_actual_id)
                    ->pluck('es_e_amount', 'es_e_type_id')
                    ->toArray();

                // $emp_earnings = SalaryEmployeeEarnings::where('es_e_emp_id', $emp_actual_id)->get()->groupBy('es_e_type_id');


                $emp_deductions = SalaryEmployeeDeductions::where('es_d_emp_id', $emp_actual_id)
                    ->pluck('es_d_amount', 'es_d_type_id')
                    ->toArray();

                $emplyer_deductions = SalaryEmployerDeductions::where('employer_sd_emp_id', $emp_actual_id)
                    ->pluck('employer_sd_amount', 'employer_sd_type_id')
                    ->toArray();
            } else {
                $title = 'Add Payroll';
            }



            return view('admin.employees.salary_master.daily-salary-master', compact(
                'title',
                'employee',
                'business_id',
                'earnings',
                'deductions',
                'calculation_modes',
                'payroll_structures',
                'emp_salary',
                'emp_actual_id',
                'employee_list',
                'emp_earnings',
                'employerDeductions',
                'smhistory',
                'smhistoryLastData',
                'financialYears',
                'emplyer_deductions',
                'emp_deductions'
            ));
        } catch (Exception $e) {
            if ('The payload is invalid.' == $e->getMessage()) {
                return redirect()->route('employee.payroll');
            }
        }
    }


    public function addEditEmployeeSalary($emp_id = null)
    {

        // dd($emp_id);
        try {
            $user = Auth::user();
            $emp_actual_id = Crypt::decrypt($emp_id);

            // $smhistory = SalaryMasterHistory::where('sm_emp_id', $emp_actual_id)->where('sm_emp_b_id', $user->emp_b_id)->get();
            $smhistory = SalaryMasterHistory::with('financial_years')->where('sm_emp_id', $emp_actual_id)
                ->where('sm_emp_b_id', $user->emp_b_id)
                ->orderBy('sm_id', 'desc')
                ->get();
            $smhistoryLastData = $smhistory->first();
            $employee = Employee::with('fh_designation')->where('emp_id', $emp_actual_id)
                ->firstOrFail();
            $financialYears = FinancialYear::get();

            $employee_list = Employee::where('emp_b_id', $user->emp_b_id)
                ->where('emp_status', 71)
                ->get();

            // Fetch existing salary record
            $emp_salary = SalaryEmployeeSalary::where('es_emp_id', $emp_actual_id)->first();

            // dd($emp_salary);

            $calculation_modes = MasterTable::where('m_group', 'CALCULATION_MODE')
                ->orderBy('m_name', 'desc')
                ->pluck('m_name', 'm_id')
                ->toArray();

            $payroll_structures = SalaryPolicySalary::where('ps_b_id', $user->emp_b_id)->get();
            $business_id = $user->emp_b_id; // Logged-in user ka business_id

            if (!$business_id) {
                return response()->json(['error' => 'Business ID not found'], 404);
            }

            $earnings = SalaryAllowance::where('sa_b_id', $business_id)->orderBy('sa_sequence_valu', 'asc')->get();
            $deductions = StatutoryDeduction::join('master_table', 'statutory_deductions.std_deduction_type_id', '=', 'master_table.m_id')
                ->where('std_b_id', $business_id)
                ->select('statutory_deductions.*', 'master_table.m_name as deduction_type_name')
                ->get();

            $employerDeductions = StatutoryDeduction::join('master_table', 'statutory_deductions.std_deduction_type_id', '=', 'master_table.m_id')
                ->where('std_b_id', $business_id)
                ->select('statutory_deductions.*', 'master_table.m_name as deduction_type_name')
                ->get();
            // dd($deductions);

            $emp_earnings = [];
            $emp_deductions = [];
            $emplyer_deductions = [];

            if ($emp_salary) {
                $title = 'Update Payroll';

                $emp_earnings = SalaryEmployeeEarnings::where('es_e_emp_id', $emp_actual_id)
                    ->pluck('es_e_amount', 'es_e_type_id')
                    ->toArray();

                // $emp_earnings = SalaryEmployeeEarnings::where('es_e_emp_id', $emp_actual_id)->get()->groupBy('es_e_type_id');


                $emp_deductions = SalaryEmployeeDeductions::where('es_d_emp_id', $emp_actual_id)
                    ->pluck('es_d_amount', 'es_d_type_id')
                    ->toArray();

                $emplyer_deductions = SalaryEmployerDeductions::where('employer_sd_emp_id', $emp_actual_id)
                    ->pluck('employer_sd_amount', 'employer_sd_type_id')
                    ->toArray();
            } else {
                $title = 'Add Payroll';
            }



            return view('admin.employees.salary.add-edit', compact(
                'title',
                'employee',
                'business_id',
                'earnings',
                'deductions',
                'calculation_modes',
                'payroll_structures',
                'emp_salary',
                'emp_actual_id',
                'employee_list',
                'emp_earnings',
                'employerDeductions',
                'smhistory',
                'smhistoryLastData',
                'financialYears',
                'emplyer_deductions',
                'emp_deductions'
            ));
        } catch (Exception $e) {
            if ('The payload is invalid.' == $e->getMessage()) {
                return redirect()->route('employee.payroll');
            }
        }
    }

    // public function saveEmplyeeSalary(Request $request)
    // {
    //     $user = Auth::user();

    //     $baseSalary = 0;
    //     $data = SalaryEmployeeSalary::where('es_b_id', $request->business_id)->where('es_emp_id', $request->emp_id)->first();
    //     $dateInput = $request->wef ? trim($request->wef) : null;
    //     $smHistory = new SalaryMasterHistory();
    //     $smHistory->sm_emp_id = $request->emp_id;
    //     $smHistory->sm_emp_b_id = $request->business_id;
    //     $smHistory->sm_cal_mode = $request->deduction_employer_mode;
    //     $smHistory->sm_is_employer_deduction = $request->payroll_mode;
    //     $smHistory->sm_monthly_ctc = $request->es_monthly_ctc;
    //     $smHistory->sm_annual_ctc = $request->es_annual_ctc;
    //     $smHistory->sm_other_allow = $request->es_rem_allowance;
    //     $smHistory->sm_total_earning = $request->total_employee_earning;
    //     $smHistory->sm_gross_pay = $request->es_monthly_gross;
    //     $smHistory->sm_per_day_wage = $request->es_per_day_wage;
    //     $smHistory->sm_annual_gross = $request->annual_gross;
    //     $smHistory->sm_net_pay = $request->monthly_net_salary;
    //     $smHistory->sm_employee_total_ded = $request->total_employee_deduction;
    //     $smHistory->sm_employer_total_ded = $request->total_employer_deduction;
    //     $smHistory->sm_esic_validation_enabled = $request->esic_validation_enabled ?? 1;
    //     $smHistory->sm_fy_id = $request->filled('financial_year') ? $request->financial_year : null;
    //     $smHistory->sm_remark = $request->remark ?? '';
    //     $smHistory->wef = $dateInput;
    //     if ($request->has('earnings')) {

    //         $earningsData = SalaryAllowance::where('sa_b_id', $request->business_id)->whereIn('sa_id', array_keys($request->earnings))->get();
    //         // Delete any existing matching entry
    //         SalaryEmployeeEarnings::where([
    //             'es_e_b_id' => $request->business_id,
    //             'es_e_emp_id' => $request->emp_id
    //         ])->delete();
    //         foreach ($earningsData as $earnData) {
    //             $amount = $request->earnings[$earnData->sa_id] ?? 0;
    //             if ($earnData->sa_earning_type_id == 360) {
    //                 $baseSalary = $amount;
    //                 $smHistory->sm_basic = $amount;
    //             } elseif ($earnData->sa_earning_type_id == 361) {
    //                 $smHistory->sm_hra = $amount;
    //             } elseif ($earnData->sa_earning_type_id == 362) {
    //                 $smHistory->sm_dear_allow = $amount;
    //             } elseif ($earnData->sa_earning_type_id == 363) {
    //                 $smHistory->sm_conv_allow = $amount;
    //             } elseif ($earnData->sa_earning_type_id == 364) {
    //                 if (Str::contains(strtoupper($earnData->sa_title), 'EDUCATION')) {
    //                     $smHistory->sm_edu_allow = $amount;
    //                 }
    //                 if (Str::contains(strtoupper($earnData->sa_title), 'MEDICAL')) {
    //                     $smHistory->sm_med_allow = $amount;
    //                 }
    //             }
    //             SalaryEmployeeEarnings::create([
    //                 'es_e_b_id' => $request->business_id,
    //                 'es_e_emp_id' => $request->emp_id,
    //                 'es_sa_id' => $earnData->sa_id,
    //                 'es_e_type_id' => $earnData->sa_earning_type_id,
    //                 'es_e_amount' => $amount,
    //                 'es_e_cal_type_id' => $earnData->sa_calculation_type
    //             ]);

    //             // SalaryEmployeeEarnings::updateOrCreate(
    //             //     [
    //             //         'es_e_b_id' => $request->business_id,
    //             //         'es_e_emp_id' => $request->emp_id,
    //             //         'es_e_type_id' => $earnData->sa_earning_type_id
    //             //     ],
    //             //     [
    //             //         'es_e_amount' => $amount,
    //             //         'es_e_cal_type_id' => $earnData->sa_calculation_type
    //             //     ]
    //             // );
    //         }
    //     }

    //     if ($request->has('deductions')) {
    //         $deductionsData = StatutoryDeduction::where('std_b_id', $request->business_id)
    //             ->whereIn('std_id', array_keys($request->deductions))
    //             ->get();
    //         foreach ($deductionsData as $deductData) {
    //             $amount = $request->deductions[$deductData->std_id] ?? 0;
    //             $employerAmount = $request->employerDeductions[$deductData->std_id] ?? 0;
    //             // Handle summary saving
    //             if ($deductData->std_deduction_type_id == 351) {
    //                 $smHistory->sm_employee_epf = $amount;
    //                 $smHistory->sm_employer_epf = $employerAmount;
    //             } elseif ($deductData->std_deduction_type_id == 352) {
    //                 $smHistory->sm_employee_esic = $amount;
    //                 $smHistory->sm_employer_esic = $employerAmount;
    //             } elseif ($deductData->std_deduction_type_id == 357) {
    //                 $smHistory->sm_employee_lwf = $amount;
    //                 $smHistory->sm_employer_lwf = $employerAmount;
    //             }

    //             // Save employee-side deductions
    //             SalaryEmployeeDeductions::updateOrCreate(
    //                 [
    //                     'es_d_b_id' => $request->business_id,
    //                     'es_d_emp_id' => $request->emp_id,
    //                     'es_d_type_id' => $deductData->std_deduction_type_id,
    //                 ],
    //                 [
    //                     'es_d_amount' => $amount,
    //                     'es_d_cal_type_id' => $deductData->std_deduction_type_id,
    //                 ]
    //             );
    //         }
    //     }

    //     if ($request->has('employerDeductions')) {
    //         $employerDeductionsData = StatutoryDeduction::where('std_b_id', $request->business_id)
    //             ->whereIn('std_id', array_keys($request->employerDeductions))
    //             ->get();

    //         foreach ($employerDeductionsData as $deductData) {
    //             $amount = $request->employerDeductions[$deductData->std_id] ?? 0;

    //             // Save employer-side deductions
    //             SalaryEmployerDeductions::updateOrCreate(
    //                 [
    //                     'employer_sd_b_id' => $request->business_id,
    //                     'employer_sd_emp_id' => $request->emp_id,
    //                     'employer_sd_type_id' => $deductData->std_deduction_type_id,
    //                 ],
    //                 [
    //                     'employer_sd_amount' => $amount,
    //                     'employer_sd_cal_type_id' => $deductData->std_deduction_type_id,
    //                 ]
    //             );
    //         }
    //     }


    //     $smHistory->save();
    //     if ($data) {
    //         $data->update([
    //             'es_annual_ctc' => $request->es_annual_ctc,
    //             'es_monthly_ctc' => $request->es_monthly_ctc,
    //             'es_base_salary' => $baseSalary,
    //             'es_perday_salary' => $request->es_per_day_wage,
    //             'es_earnings' => $request->es_monthly_gross,
    //             'es_deductions' => $request->total_employee_deduction,
    //             'es_rem_allowance' => $request->es_rem_allowance,
    //             'es_monthly_gross' => $request->es_monthly_gross,
    //             'es_annual_gross' => $request->annual_gross,
    //             'es_esic_validation_enabled' => $request->esic_validation_enabled ?? 1,
    //             'es_monthly_net_salary' => $request->monthly_net_salary,
    //         ]);
    //     } else {
    //         SalaryEmployeeSalary::create([
    //             'es_b_id' => $request->business_id,
    //             'es_emp_id' => $request->emp_id,
    //             'es_annual_ctc' => $request->es_annual_ctc,
    //             'es_monthly_ctc' => $request->es_monthly_ctc,
    //             'es_base_salary' => $baseSalary,
    //             'es_perday_salary' => $request->es_per_day_wage,
    //             'es_earnings' => $request->es_monthly_gross,
    //             'es_deductions' => $request->total_employee_deduction,
    //             'es_rem_allowance' => $request->es_rem_allowance,
    //             'es_monthly_gross' => $request->es_monthly_gross,
    //             'es_annual_gross' => $request->annual_gross,
    //             'es_esic_validation_enabled' => $request->esic_validation_enabled ?? 1,
    //             'es_monthly_net_salary' => $request->monthly_net_salary,
    //         ]);
    //     }
    //     return redirect()->route('employee.payroll')->with('success', "Salary information saved successfully.");
    // }

    public function saveEmplyeeSalary(Request $request)
    {
        $user = Auth::user();

        $baseSalary = 0;
        $data = SalaryEmployeeSalary::where('es_b_id', $request->business_id)->where('es_emp_id', $request->emp_id)->first();
        $dateInput = $request->wef ? trim($request->wef) : null;
        $smHistory = new SalaryMasterHistory();
        $smHistory->sm_emp_id = $request->emp_id;
        $smHistory->sm_emp_b_id = $request->business_id;
        $smHistory->sm_cal_mode = $request->deduction_employer_mode;
        $smHistory->sm_is_employer_deduction = $request->payroll_mode;
        $smHistory->sm_monthly_ctc = $request->es_monthly_ctc;
        $smHistory->sm_annual_ctc = $request->es_annual_ctc;
        $smHistory->sm_other_allow = $request->es_rem_allowance;
        $smHistory->sm_total_earning = $request->total_employee_earning;
        $smHistory->sm_gross_pay = $request->es_monthly_gross;
        $smHistory->sm_per_day_wage = $request->es_per_day_wage;
        $smHistory->sm_annual_gross = $request->annual_gross;
        $smHistory->sm_net_pay = $request->monthly_net_salary;
        $smHistory->sm_employee_total_ded = $request->total_employee_deduction;
        $smHistory->sm_employer_total_ded = $request->total_employer_deduction;
        $smHistory->sm_esic_validation_enabled = $request->esic_validation_enabled ?? 1;
        $smHistory->sm_pf_validation_enabled = $request->pf_validation_enabled ?? 1;
        $smHistory->sm_fy_id = $request->filled('financial_year') ? $request->financial_year : null;
        $smHistory->sm_remark = $request->remark ?? '';
        $smHistory->wef = $dateInput;
        if ($request->has('earnings')) {
            $earningsData = SalaryAllowance::where('sa_b_id', $request->business_id)->whereIn('sa_id', array_keys($request->earnings))->get();
            // Delete any existing matching entry
            SalaryEmployeeEarnings::where([
                'es_e_b_id' => $request->business_id,
                'es_e_emp_id' => $request->emp_id
            ])->delete();
            foreach ($earningsData as $earnData) {
                $amount = $request->earnings[$earnData->sa_id] ?? 0;
                if ($earnData->sa_earning_type_id == 360) {
                    $baseSalary = $amount;
                    $smHistory->sm_basic = $amount;
                } elseif ($earnData->sa_earning_type_id == 361) {
                    $smHistory->sm_hra = $amount;
                } elseif ($earnData->sa_earning_type_id == 362) {
                    $smHistory->sm_dear_allow = $amount;
                } elseif ($earnData->sa_earning_type_id == 363) {
                    $smHistory->sm_conv_allow = $amount;
                } elseif ($earnData->sa_earning_type_id == 364) {
                    if (Str::contains(strtoupper($earnData->sa_title), 'EDUCATION')) {
                        $smHistory->sm_edu_allow = $amount;
                    }
                    if (Str::contains(strtoupper($earnData->sa_title), 'MEDICAL')) {
                        $smHistory->sm_med_allow = $amount;
                    }
                }
                SalaryEmployeeEarnings::create([
                    'es_e_b_id' => $request->business_id,
                    'es_e_emp_id' => $request->emp_id,
                    'es_sa_id' => $earnData->sa_id,
                    'es_e_type_id' => $earnData->sa_earning_type_id,
                    'es_e_amount' => $amount,
                    'es_e_cal_type_id' => $earnData->sa_calculation_type
                ]);

                // SalaryEmployeeEarnings::updateOrCreate(
                //     [
                //         'es_e_b_id' => $request->business_id,
                //         'es_e_emp_id' => $request->emp_id,
                //         'es_e_type_id' => $earnData->sa_earning_type_id
                //     ],
                //     [
                //         'es_e_amount' => $amount,
                //         'es_e_cal_type_id' => $earnData->sa_calculation_type
                //     ]
                // );
            }
        }

        if ($request->has('deductions')) {
            $deductionsData = StatutoryDeduction::where('std_b_id', $request->business_id)
                ->whereIn('std_id', array_keys($request->deductions))
                ->get();
            foreach ($deductionsData as $deductData) {
                $amount = $request->deductions[$deductData->std_id] ?? 0;
                $employerAmount = $request->employerDeductions[$deductData->std_id] ?? 0;
                // Handle summary saving
                if ($deductData->std_deduction_type_id == 351) {
                    $smHistory->sm_employee_epf = $amount;
                    $smHistory->sm_employer_epf = $employerAmount;
                } elseif ($deductData->std_deduction_type_id == 352) {
                    $smHistory->sm_employee_esic = $amount;
                    $smHistory->sm_employer_esic = $employerAmount;
                } elseif ($deductData->std_deduction_type_id == 357) {
                    $smHistory->sm_employee_lwf = $amount;
                    $smHistory->sm_employer_lwf = $employerAmount;
                }

                // Save employee-side deductions
                SalaryEmployeeDeductions::updateOrCreate(
                    [
                        'es_d_b_id' => $request->business_id,
                        'es_d_emp_id' => $request->emp_id,
                        'es_d_type_id' => $deductData->std_deduction_type_id,
                    ],
                    [
                        'es_d_amount' => $amount,
                        'es_d_cal_type_id' => $deductData->std_deduction_type_id,
                    ]
                );
            }
        }

        if ($request->has('employerDeductions')) {
            $employerDeductionsData = StatutoryDeduction::where('std_b_id', $request->business_id)
                ->whereIn('std_id', array_keys($request->employerDeductions))
                ->get();

            foreach ($employerDeductionsData as $deductData) {
                $amount = $request->employerDeductions[$deductData->std_id] ?? 0;

                // Save employer-side deductions
                SalaryEmployerDeductions::updateOrCreate(
                    [
                        'employer_sd_b_id' => $request->business_id,
                        'employer_sd_emp_id' => $request->emp_id,
                        'employer_sd_type_id' => $deductData->std_deduction_type_id,
                    ],
                    [
                        'employer_sd_amount' => $amount,
                        'employer_sd_cal_type_id' => $deductData->std_deduction_type_id,
                    ]
                );
            }
        }


        $smHistory->save();
        if ($data) {
            $data->update([
                'es_annual_ctc' => $request->es_annual_ctc,
                'es_monthly_ctc' => $request->es_monthly_ctc,
                'es_base_salary' => $baseSalary,
                'es_perday_salary' => $request->es_per_day_wage,
                'es_earnings' => $request->es_monthly_gross,
                'es_deductions' => $request->total_employee_deduction,
                'es_rem_allowance' => $request->es_rem_allowance,
                'es_monthly_gross' => $request->es_monthly_gross,
                'es_annual_gross' => $request->annual_gross,
                'es_esic_validation_enabled' => $request->esic_validation_enabled ?? 1,
                'es_pf_validation_enabled' => $request->pf_validation_enabled ?? 1,
                'es_monthly_net_salary' => $request->monthly_net_salary,
            ]);
        } else {
            SalaryEmployeeSalary::create([
                'es_b_id' => $request->business_id,
                'es_emp_id' => $request->emp_id,
                'es_annual_ctc' => $request->es_annual_ctc,
                'es_monthly_ctc' => $request->es_monthly_ctc,
                'es_base_salary' => $baseSalary,
                'es_perday_salary' => $request->es_per_day_wage,
                'es_earnings' => $request->es_monthly_gross,
                'es_deductions' => $request->total_employee_deduction,
                'es_rem_allowance' => $request->es_rem_allowance,
                'es_monthly_gross' => $request->es_monthly_gross,
                'es_annual_gross' => $request->annual_gross,
                'es_esic_validation_enabled' => $request->esic_validation_enabled ?? 1,
                'es_pf_validation_enabled' => $request->pf_validation_enabled ?? 1,
                'es_monthly_net_salary' => $request->monthly_net_salary,
            ]);
        }
        return redirect()->route('employee.payroll')->with('success', "Salary information saved successfully.");
    }

    public function salaryMasterSampleExport($businessId)
    {
        try {
            // Optional: Automatically pick business ID from logged-in user if not passed
            if (empty($businessId)) {
                $businessId = auth()->user()->emp_b_id ?? null;
            }

            if (!$businessId) {
                return back()->with('error', 'Business ID is missing.');
            }

            $fileName = 'SalaryMasterSample_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

            return Excel::download(new EmployeeSalarySampleExport($businessId), $fileName);
        } catch (\Throwable $e) {
            // Log error for debugging
            \Log::error('Salary Master Sample Export failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Export failed: ' . $e->getMessage());
        }
    }

    private function calculateBasicSalary($monthlyCTC, $basicSalaryPercentage)
    {
        return $monthlyCTC * ($basicSalaryPercentage / 100);
    }



    public function getEarnings()
    {
        $business_id = auth()->user()->business_id; // Logged-in user ka business_id

        if (!$business_id) {
            return response()->json(['error' => 'Business ID not found'], 404);
        }

        $earnings = SalaryAllowance::where('sa_b_id', $business_id)->get();

        return response()->json($earnings);
    }



    public function getPayrollCalculation(Request $request)
    {

        $response = ['status_code' => 0, 'status_text' => '', 'result' => null];

        if ($request->REQUEST_TYPE == 'PAYROLL_CALCULATION') {

            $es_monthly_ctc = $request->es_monthly_ctc;
            $conveyance = $request->conveyance;
            $medicalAllowance = $request->medicalAllowance;
            $specialAllowance = $request->specialAllowance;
            $professional_tax = $request->professional_tax;
            $tds = $request->tds;

            $salaryStructure = SalaryPolicySalary::where('ps_id', $request->input('ps_id'))->first();
            $basicSalaryPercentage = $salaryStructure->ps_basic_salary_percentage / 100;
            $hraPercentage = $salaryStructure->ps_hra_allowance_percentage / 100;


            $basicSalary = $this->calculateBasicSalary($es_monthly_ctc, $salaryStructure->ps_basic_salary_percentage);
            $hraAllowance = $basicSalary * $hraPercentage;

            $monthly_gross = (float) $basicSalary + (float) $hraAllowance + (float) $conveyance + (float) $medicalAllowance + (float) $specialAllowance;

            $pf_employee_contribution = $basicSalary * ($salaryStructure->ps_employee_pf_percentage / 100);
            $pf_employer_contribution = $basicSalary * ($salaryStructure->ps_employer_pf_percentage / 100);


            $employee_esic = $monthly_gross > $salaryStructure->ps_esic_threshhold ? 0 : $monthly_gross * (
                $salaryStructure->ps_employee_esic_percentage / 100);
            $employer_esic = $monthly_gross > $salaryStructure->ps_esic_threshhold ? 0 : $monthly_gross * (
                $salaryStructure->ps_employer_esic_percentage / 100);

            $total_employee_deduction = $pf_employee_contribution + $employee_esic + $professional_tax + $tds;
            $total_employer_deduction = $pf_employer_contribution + $employer_esic;
            $month_net_salary = $monthly_gross - $total_employee_deduction;

            $response['status_code'] = 1;
            $response['status_text'] = 'success';

            $response['result'] = [
                'es_monthly_basic' => $basicSalary,
                'es_hra_allowance' => $hraAllowance,
                'monthly_gross' => $monthly_gross,
                'total_employee_deduction' => $total_employee_deduction,
                'total_employer_deduction' => $total_employer_deduction,
                'pf_employee_contribution' => $pf_employee_contribution,
                'pf_employer_contribution' => $pf_employer_contribution,
                'employee_esic' => $employee_esic,
                'employer_esic' => $employer_esic,
                'monthly_net_salary' => $month_net_salary
            ];
        }
        return response()->json($response);
    }


    public function existDataCheck(Request $request)
    {
        $response = ['status_code' => 0, 'status_text' => 'No data found', 'result' => null];

        if ($request->REQUEST_TYPE == 'CHECK_DATA') {
            $emp_id = $request->employee;

            // Fetch the latest salary data for the employee
            $data = SalaryEmployeeSalary::where('es_emp_id', $emp_id)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($data) {
                $total_employee_deduction = $data->es_employee_pf_contribution + $data->es_employee_esic_contribution + $data->es_professional_tax + $data->es_tds;
                $total_employer_deduction = $data->es_employer_pf_contribution + $data->es_employer_esic_contribution;

                $response = [
                    'status_code' => 1,
                    'status_text' => 'Success',
                    'result' => [
                        'payroll_structure' => $data->es_ps_id,
                        'es_conveyance_allowance' => $data->es_conveyance_allowance,
                        'es_medical_allowance' => $data->es_medical_allowance,
                        'es_special_allowance' => $data->es_special_allowance,
                        'professional_tax' => $data->es_professional_tax,
                        'tds' => $data->es_tds,
                        'es_monthly_ctc' => $data->es_monthly_ctc,
                        'es_monthly_basic' => $data->es_base_salary,
                        'es_hra_allowance' => $data->es_hra_allowance,
                        'monthly_gross' => $data->es_monthly_gross,
                        'total_employee_deduction' => $total_employee_deduction,
                        'total_employer_deduction' => $total_employer_deduction,
                        'pf_employee_contribution' => $data->es_employee_pf_contribution,
                        'pf_employer_contribution' => $data->es_employer_pf_contribution,
                        'employee_esic' => $data->es_employee_esic_contribution,
                        'employer_esic' => $data->es_employer_esic_contribution,
                        'monthly_net_salary' => $data->es_monthly_net_salary,

                    ]
                ];
            }
        }

        return response()->json($response);
    }

    public function getPayrollSlip(Request $request)
    {
        // dd(SalaryPayrollRecord::with('fh_employee.fh_employee_salary')->get());

        $status = request()->input('statusFilter');
        $search = request()->input('searchFilter');
        $search = request()->input('searchFilter');
        if ($this->user) {
            $employeeList = Employee::where('emp_b_id', $this->user->emp_b_id)->select('emp_id', 'emp_full_name')->get();
            if ($request->ajax()) {
                $dynamicConditions = [
                    ['method' => 'where', 'args' => ['pr_b_id', $this->user->emp_b_id]], // Filter by business ID
                    [
                        'method' => 'select',
                        'args' => ['pr_id', 'pr_emp_id', 'pr_pp_id', 'pr_base_salary', 'pr_total_earnings', 'pr_total_deductions', 'pr_net_salary', 'status', 'pr_processed_at', 'pr_year_month'],
                        'relation' => ['fh_employee:emp_id,emp_full_name']
                    ],
                    // You can add more default conditions here if needed.
                ];

                // Apply filters dynamically based on request inputs
                // if ($statusFilter != '') {
                //     $dynamicConditions[] = ['method' => 'where', 'args' => ['status', $statusFilter]];
                // }

                // Date filters
                // if ($fromDateFilter || $toDateFilter) {
                //     $fromDateTime = $fromDateFilter . ' 00:00:00';
                //     $toDateTime = $toDateFilter . ' 23:59:59';

                //     $dynamicConditions[] = [
                //         'method' => 'whereBetween',
                //         'args' => ['pr_processed_at', [$fromDateTime, $toDateTime]]
                //     ];
                // }

                // Define searchable columns and relationships
                $searchColumns = ['pr_base_salary', 'pr_net_salary', 'status', 'pr_processed_at'];
                $searchRelationships = [
                    'fh_employee' => ['emp_fname', 'emp_lname'],
                    // 'fh_payroll_period' => ['period_name'],
                    // 'fh_business' => ['b_name'],
                ];

                // Fetch the data
                $list = (new DynamicModelDataTableHelper(
                    eloquentModel: new SalaryPayrollRecord(),
                    dynamicConditions: $dynamicConditions,
                    searchColumns: $searchColumns,
                    searchRelationships: $searchRelationships
                ))->getServerSideDataTable();

                // Format the data
                $rowData = [];
                $i = 1;
                foreach ($list as $key => $record) {
                    $row = [];
                    $row[] = $i++;
                    $row[] = optional($record->fh_employee)->emp_full_name ?? 'N/A';
                    $row[] = $record->pr_year_month;
                    $row[] = $record->pr_base_salary;
                    $row[] = $record->pr_total_earnings;
                    $row[] = $record->pr_total_deductions;
                    $row[] = $record->pr_net_salary;
                    $row[] = $record->pr_processed_at->format('Y-m-d');
                    // $row[] = '<span class="badge badge-' . ($record->status == 'paid' ? 'success' : 'warning') . '">' . ucfirst($record->status) . '</span>';
                    $row[] =  ucfirst($record->status);

                    $row[] = '<button class="btn btn-sm btn-primary editPaySlipBtn hidden"
                                data-id="' . $record->pr_id . '"
                                data-employee-id="' . $record->pr_emp_id . '"
                                data-month="' . $record->pr_year_month . '">
                                <i class="feather feather-edit"></i>
                            </button>' .
                        '<button class="btn btn-sm btn-danger ms-2 deleteBtn" data-id="' . $record->pr_id . '">
                                <i class="feather feather-trash"></i>
                            </button>' .
                        '<a href="' . route('payslip.download', md5($record->pr_id)) . '"
                                    class="btn btn-sm btn-success ms-2">
                                    <i class="feather feather-download"></i>

                            </a>';
                    // $row[] = '<button class="btn btn-sm btn-danger ms-2 deleteBtn" data-id="' . $record->pr_id . '">
                    //         <i class="feather feather-trash"></i>
                    //     </button>';
                    $row[] = '';

                    $rowData[] = $row;
                }

                // Return the formatted data
                return response()->json([
                    "draw" => request()->input('draw'),
                    "recordsTotal" => sizeof($list),
                    "recordsFiltered" => (new DynamicModelDataTableHelper(eloquentModel: new SalaryPayrollRecord(), dynamicConditions: $dynamicConditions, searchColumns: $searchColumns, searchRelationships: $searchRelationships))->countFilteredServerSideDataTable(),
                    "data" => $rowData,
                ]);
            }


            $columns = [
                'S. No.',
                'Employee Name', // Changed from 'Employee' to 'Employee Name' for clarity
                'Year-Month',    // Changed from 'Month' to 'Year-Month' to match the data being returned
                'Base Salary',   // Changed from 'Gross Pay' to 'Base Salary' to match the data being returned
                'Total Earnings', // Changed from 'Deduction' to 'Total Earnings' to match the data being returned
                'Total Deductions', // Changed from 'Net Pay' to 'Total Deductions' to match the data being returned
                'Net Salary',    // Changed from 'Status' to 'Net Salary' to match the data being returned
                'Processed Date',
                'Status',        // Kept as 'Status' since it matches the data being returned
                'Action',        // Kept as 'Action' since it matches the data being returned
            ];
            return view('admin.setting.payroll.payslip', compact('employeeList', 'columns'));
        } else {
            abort('404');
        }
    }
    public function paySlipStore(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'employee_id' => 'required|exists:employees,emp_id', // Ensure employee exists
                'pay_slip_month' => 'required|date_format:Y-m', // Ensure the month is in the correct format
            ]);

            // Check for duplicate entries
            $duplicate = SalaryPayrollRecord::where('pr_emp_id', $request->employee_id)
                ->where('pr_year_month', $request->pay_slip_month)
                ->first();

            if ($duplicate) {
                return response()->json(['error' => 'Duplicate entry for this employee and month.'], 409);
            }

            // Finding the Employee
            $employee = Employee::find($request->employee_id);
            if (!$employee) {
                return response()->json(['error' => 'Employee not found.'], 404);
            }

            // Extract Year and Month
            [$year, $month] = explode('-', $request->pay_slip_month);

            // Getting Week Off Dates
            $weekOfDates = CentralLogics::getWeekOffDates($employee, (int) $year, (int) $month);

            // Getting Monthly Attendance Summary
            $attendanceDataResult = CentralLogics::getMonthlyAttendanceSummary($employee, $request->pay_slip_month, $weekOfDates);

            // Calculating Worked Days
            $workedDays = $attendanceDataResult['presentCount'] + count($weekOfDates) + $attendanceDataResult['holidayCount'] + $attendanceDataResult['missedPunchCount'] + $attendanceDataResult['leaveCount'];

            // Setting Monthly Net Pay
            $monthly_net_pay = (optional($employee->fh_employee_salary)->es_monthly_ctc ?? 0);

            // Calculating Daily Rate
            $totalDaysInMonth = Carbon::parse($request->pay_slip_month)->daysInMonth;
            $dailyRate  = round($monthly_net_pay / $totalDaysInMonth, 2);

            // Calculating Requested Monthly CTC
            $requestedMonthlyCTC = round($dailyRate * $workedDays, 2);

            // Calculating Employee PF Contribution
            $employeePFContribution = round($requestedMonthlyCTC * 0.06, 2);
            $deductions = $employeePFContribution;

            // Calculating Monthly Gross and Net Salary
            $monthlyGross = round($requestedMonthlyCTC - $employeePFContribution, 2);
            $monthlyNetSalary = round($monthlyGross - $deductions, 2);

            // Save the Salary Payroll Record
            $saveData = SalaryPayrollRecord::create([
                'pr_b_id' => $employee->emp_b_id, // Branch ID
                'pr_emp_id' => $employee->emp_id, // Employee ID
                'pr_year_month' => $year . '-' . $month, // Year and month in 'YYYY-MM' format
                'pr_base_salary' => $monthly_net_pay, // Monthly net pay
                'pr_total_earnings' => $monthlyGross, // Total earnings (gross pay)
                'pr_total_deductions' => $deductions, // Total deductions (PF contribution)
                'pr_net_salary' => $monthlyNetSalary, // Net salary after deductions
                'pr_processed_at' => now(), // Current timestamp
            ]);

            // Check if the record was saved successfully
            if ($saveData) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data saved successfully.',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Data not saved.',
                    'error_code' => 1001, // Example error code
                ]);
            }
        } catch (\Exception $e) {
            // Handle unexpected exceptions
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred.',
                'error_code' => 5000, // Internal server error code
                'error_message' => $e->getMessage(),
            ], 500);
        }
    }

    public function paySlipDestroy(string $id)
    {
        if ($this->user) {
            $result = CentralLogics::dynamicDelete(SalaryPayrollRecord::class, $id);

            if (isset($result['success'])) {
                return response()->json(['success' => $result['success']]);
            } else {
                return response()->json(['error' => $result['error']], 400);
            }
        } else {
            abort(404);
        }
    }

    public function pdfDownload($id)
    {
        // Retrieve the record using the hashed ID
        $record = SalaryPayrollRecord::whereRaw('MD5(pr_id) = ?', [$id])->first();

        // Check if the record exists
        if (!$record) {
            return abort(404, 'Payslip not found.');
        }

        // Pass the required data to the view
        $payslipHtml = view('template.payslips', [
            'employee' => $record->fh_employee, // Assuming relationship with Employee model
            'year' => substr($record->pr_year_month, 0, 4),
            'month' => substr($record->pr_year_month, 5, 2),
            'monthlyNetSalary' => $record->pr_net_salary,
            'monthlyGross' => $record->pr_total_earnings,
            'deductions' => $record->pr_total_deductions,
        ])->render();

        // Generate the PDF
        $pdf = PDF::loadHTML($payslipHtml);

        // Stream the PDF to the browser
        return $pdf->stream('payslip_' . $record->pr_emp_id . '_' . $record->pr_year_month . '.pdf');
    }

    public function exportSalaryMasterHistoryExcel($id)
    {
        $user = Auth::user();
        $data = SalaryMasterHistory::with(['financial_years', 'employees', 'business'])->where([
            'sm_id' => $id
        ])->first();
        $fileName = 'salary_employee' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new SmHistoryExport($data, $user), $fileName);
    }

    public function empOtherAllowance(Request $request)
    {
        $salaryEarn = SalaryEmployeeEarnings::select('es_e_amount')->where('es_sa_id', $request->saId)->where('es_e_emp_id', $request->empActualId)->where('es_e_b_id', $request->businessId)->first();
        return response()->json(['salaryEarn' => $salaryEarn], 200);
    }

    public function employeesSalaryexport()
    {
        $user = Auth::user();
        $business_id = $user->emp_b_id;
        $salary_allowances = SalaryAllowance::where('sa_b_id', $business_id)->get();
        return Excel::download(new EmployeeSalaryExport($salary_allowances), 'employee-salary-export.xlsx');
    }

    public function employeesSalaryImport(Request $request)
    {

        //    dd($request->all());
        $request->validate([
            'financial_year' => 'required',
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new EmployeeSalaryImport($request->financial_year), $request->file('file'));
            return response()->json(['message' => 'Import successful.']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Import failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getDeductionFlags(Request $request)
    {
        $employee = Employee::where('emp_id', $request->employeeId)->first();

        if (!$employee) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Employee not found',
            ]);
        }

        if ($request->type_id == 351) {
            $employee->emp_is_pf_enabled = $request->enabled == 1 ? 120 : 121;
        }

        if ($request->type_id == 352) {
            $employee->emp_esic_limit = $request->enabled == 1 ? 120 : 121;
        }

        $employee->save();
        return response()->json([
            'status' => 'success',
            'value' => $request->enabled,
            'message' => 'Status changed successfully.',
        ]);
    }
}
