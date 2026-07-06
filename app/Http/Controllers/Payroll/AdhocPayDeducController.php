<?php

namespace App\Http\Controllers\Payroll;

use App\Models\Employee;
use App\Models\Department;
use App\Models\MasterTable;
use Illuminate\Http\Request;
use App\Models\FinancialYear;
use App\Models\PayrollPeriod;
use App\Helpers\CentralLogics;
use App\Models\AdhocComponent;
use Illuminate\Support\Carbon;
use App\Models\AdhocTransaction;
use App\Models\PayrollLoanAccount;
use App\Http\Controllers\Controller;
use App\Models\SalaryEmployeeSalary;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\AdhocTransactionDetail;
use App\Models\ProcessedEmployeeSalary;
use App\Exports\AdhocComponentSampleExport;
use App\Imports\AdhocComponentSampleImport;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;

class AdhocPayDeducController extends Controller
{

    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function index(Request $request)
    {
        if ($this->user) {
            $business_id = $this->user->emp_b_id;

            if ($request->ajax()) {
                $dynamicConditions = [
                    [
                        'method' => 'where',
                        'args' => ['at_b_id', $business_id],
                        'relation' => []
                    ],
                    [
                        'method' => 'with',
                        'args' => [
                            'employee', // Already eager loads all necessary fields if needed
                            'employee.fh_department',
                            'employee.fh_designation',
                            'employee.fh_employee_status',
                            'payrollPeriod'
                        ],
                        'relation' => []
                    ],
                    [
                        'method' => 'select',
                        'args' => ['at_id', 'at_b_id', 'at_emp_id', 'at_pp_id', 'at_e_amount', 'at_d_amount', 'created_at'],
                        'relation' => []
                    ],
                    [
                        'method' => 'orderBy',
                        'args' => ['at_id', 'desc'],
                        'relation' => []
                    ]
                ];

                $searchColumns = [
                    'fh_employee.emp_code',
                    'fh_employee.emp_full_name',
                    'fh_department.d_name',
                    'fh_designation.dg_name',
                    'fh_employee_status.m_name',
                    'at_e_amount',
                    'at_d_amount',
                ];



                $list = (new DynamicModelDataTableHelper(
                    eloquentModel: new AdhocTransaction(),
                    dynamicConditions: $dynamicConditions,
                    searchColumns: $searchColumns
                ))->getServerSideDataTable();

                $rowData = [];
                $i = $request->input('start', 0);
                foreach ($list as $val) {
                    $employee = $val->employee;

                    $rowData[] = [
                        ++$i,
                        optional($employee)->emp_code ?? '-',
                        optional($employee)->emp_full_name ?? '-',
                        optional(optional($employee)->fh_department)->d_name ?? '-',
                        optional(optional($employee)->fh_designation)->dg_name ?? '-',
                        optional($val->payrollPeriod)->pp_name ?? '-',
                        optional(optional($employee)->fh_employee_status)->m_name ?? '-',
                        number_format($val->at_e_amount ?? 0, 2),
                        number_format($val->at_d_amount ?? 0, 2),
                        '<div class="btn-list ms-3">
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu p-2" style="min-width: 180px;">
                                    <li>
                                        <a href="' . route('adhoc.edit', ['employee_id' => $val->at_emp_id, 'payroll_period_id' => $val->at_pp_id]) . '"
                                        class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2">
                                            <i class="feather feather-edit"></i> Edit
                                        </a>

                                    </li>
                                </ul>
                            </div>
                        </div>'
                    ];
                }

                $output = [
                    "draw" => intval($request->input('draw')),
                    "recordsTotal" => sizeof($list),
                    "recordsFiltered" => (new DynamicModelDataTableHelper(
                        eloquentModel: new AdhocTransaction(),
                        dynamicConditions: $dynamicConditions
                    ))->countFilteredServerSideDataTable(),
                    "data" => $rowData,
                ];

                return response()->json($output);
            }

            $columns = [
                'S. No.',
                'Emp Code',
                'Employee',
                'Department',
                'Designation',
                'Payroll Period',
                'Status',
                'Earnings',
                'Deductions',
                'Action',
            ];

            $employee = Employee::with('fh_department')
                ->where('emp_b_id', $business_id)
                ->get();

           $departments = $employee->pluck('fh_department')
                ->where('d_b_id', $business_id)
                ->unique('d_id')
                ->filter()
                ->values();

            $type = MasterTable::where('m_group', 'FINANCE')->get();
            $payrollPeriods = PayrollPeriod::select('pp_id', 'pp_name')
                ->where('pp_b_id', $business_id)
                ->orderBy('pp_start_date', 'asc')
                ->get();

            $adhocHeadings = MasterTable::where('m_group', 'PAYROLL_HEADINGS')->get();
            $adhocComponents = AdhocComponent::select('ac_id', 'ac_adhoc_component_name')
                ->orderBy('ac_id')
                ->get();
            $financialYears = FinancialYear::where('fy_b_id', $business_id)->where('fy_is_current', 1)->get();
            // dd($financialYears);
            $existingTransactions = collect();
            if ($request->filled('employee_id') && $request->filled('payroll_period_id')) {
                $existingTransactions = AdhocTransaction::where('at_b_id', $business_id)
                    ->where('at_emp_id', $request->employee_id)
                    ->where('at_pp_id', $request->payroll_period_id)
                    ->get()
                    ->keyBy('at_ac_id');
            }



            return view('admin.payroll.adhoc-payments-deductions', compact(
                'columns',
                'business_id',
                'employee',
                'type',
                'payrollPeriods',
                'adhocHeadings',
                'adhocComponents',
                'departments',
                'financialYears',
                'existingTransactions'
            ));
        } else {
            abort(404);
        }
    }

    public function edit(Request $request)
    {
        $employeeId = $request->employee_id;
        $payrollPeriodId = $request->payroll_period_id;
        $business_id  = auth()->user()->emp_b_id;

        // Load employee and department
        $employee = Employee::with(['fh_department'])->where('emp_id', $employeeId)->firstOrFail();
        $department = $employee->fh_department;

        // Load financial year & payroll period
        $financialYear = FinancialYear::latest()->first()?->fy_year;
        $payrollPeriod = PayrollPeriod::where('pp_id', $payrollPeriodId)->first();
        $type = 'employee';

        // Load all payroll heading names (for display)
        $adhocHeadings = MasterTable::where('m_group', 'PAYROLL_HEADINGS')->get();

        // Load all components with their heading (used in form)
        $adhocComponents = AdhocComponent::with('payrollHeading')
            ->orderBy('ac_id')
            ->get();

        // 🔁 Group by heading ID so Blade can render headings properly
        $groupedAdhocComponents = $adhocComponents->groupBy('ac_adhoc_heading_id');

        // Load previously saved transactions for this employee and period
        $existingTransactions = AdhocTransactionDetail::with('component')
            ->whereHas('transaction', function ($q) use ($business_id, $employeeId, $payrollPeriodId) {
                $q->where([
                    ['at_b_id',  $business_id],
                    ['at_emp_id', $employeeId],
                    ['at_pp_id', $payrollPeriodId],
                ]);
            })
            ->get()
            ->keyBy('component_id'); // So in Blade: $saved = $existingTransactions[$component->ac_id] ?? null;

        return view('admin.payroll.adhoc-confirmation', compact(
            'business_id',
            'employee',
            'department',
            'type',
            'financialYear',
            'payrollPeriod',
            'adhocComponents',
            'adhocHeadings',
            'existingTransactions',
            'groupedAdhocComponents'
        ));
    }

    public function getAdhocComponents(Request $request)
    {
        $components = AdhocComponent::where('ac_adhoc_heading_id', $request->heading_id)->get(['ac_id', 'ac_adhoc_component_name']);
        return response()->json($components);
    }


    // public function formView(Request $request)
    // {
    //     $type = $request->type;
    //     $department = $request->department_id;
    //     $employee = Employee::find($request->employee_id);
    //     $payrollPeriod = PayrollPeriod::with('financialYear')->where('pp_id', $request->payroll_period_id)->first();
    //     $financialYear = $payrollPeriod->financialYear->fy_year ?? null;
    //     $department = Department::find($request->department_id);
    //     $business_id = $request->business_id;
    //     //   dd($business_id);
    //     $adhocHeadings = AdhocComponent::with('payrollHeading')->get(); // each heading includes components

    //     $groupedAdhocComponents = AdhocComponent::with('payrollHeading') // optional relation
    //         ->where('ac_adhoc_business_id', $business_id)
    //         ->get()
    //         ->groupBy('ac_adhoc_heading_id'); // group all components by heading
    //     // dd($groupedAdhocComponents);

    //     //   dd($adhocHeadings);
    //     return view('admin.payroll.adhoc-confirmation', [
    //         'type' => $type,
    //         'department' => $department ?? null,
    //         'employee' => $employee ?? null,
    //         'adhocHeadings' => $adhocHeadings,
    //         'payrollPeriod' => $payrollPeriod,
    //         'business_id' => $business_id,
    //         'financialYear' => $financialYear,
    //         'groupedAdhocComponents' => $groupedAdhocComponents,
    //     ]);
    // }



    public function formView(Request $request)
    {
        $type = $request->type;
        $department = $request->department_id;
        $employee = Employee::find($request->employee_id);
        $payrollPeriod = PayrollPeriod::with('financialYear')->where('pp_id', $request->payroll_period_id)->first();
        $financialYear = $payrollPeriod->financialYear->fy_year ?? null;
        $department = Department::find($request->department_id);
        $business_id = $request->business_id;

        // ✅ Global Components (Default ones without business_id)
        $globalComponents = AdhocComponent::with('payrollHeading')
            ->whereNull('ac_adhoc_business_id')
            ->orWhere('ac_adhoc_business_id', 0)
            ->get();

        // ✅ Business Specific Components
        $businessComponents = AdhocComponent::with('payrollHeading')
            ->where('ac_adhoc_business_id', $business_id)
            ->get();

        // ✅ Merge both
        $adhocHeadings = $globalComponents->merge($businessComponents);

        // ✅ Group by heading
        $groupedAdhocComponents = $adhocHeadings->groupBy('ac_adhoc_heading_id');

        return view('admin.payroll.adhoc-confirmation', [
            'type' => $type,
            'department' => $department ?? null,
            'employee' => $employee ?? null,
            'adhocHeadings' => $adhocHeadings,
            'payrollPeriod' => $payrollPeriod,
            'business_id' => $business_id,
            'financialYear' => $financialYear,
            'groupedAdhocComponents' => $groupedAdhocComponents,
        ]);
    }


    public function storeAdhoc(Request $request)
    {

        // dd($request);
        $business_id = $request->business_id;
        $payrollPeriodId = $request->payroll_period_id;
        $employeeId = $request->employee_id;
        $departmentId = $request->department_id;

        $earningInputs = $request->earning;
        $deductionInputs = $request->deduction;
        $remarksinput = $request->remarks;
        // dd($earningInputs);

        // Determine whether to handle a single employee or a department
        $employees = [];

        if ($departmentId) {
            // Fetch all employees in the selected department
            $employees = Employee::where('emp_d_id', $departmentId)->get();
        } elseif ($employeeId) {
            // Only one employee is selected
            $employees = Employee::where('emp_id', $employeeId)->get();
        }

        foreach ($employees as $employee) {
            $totalEarnings = 0;
            $totalDeductions = 0;

            // Check if AdhocTransaction already exists for this employee in this payroll period
            $adhocTransaction = AdhocTransaction::where('at_b_id', $business_id)
                ->where('at_emp_id', $employee->emp_id)
                ->where('at_pp_id', $payrollPeriodId)
                ->first();

            if ($adhocTransaction) {
                // If exists, update timestamps and reset amounts
                $adhocTransaction->update([
                    'updated_at' => now(),
                    'at_e_amount' => 0,
                    'at_d_amount' => 0,
                ]);

                // Delete old details (assuming full overwrite)
                AdhocTransactionDetail::where('adhoc_transaction_id', $adhocTransaction->at_id)->delete();
            } else {
                // If not exists, create new
                $adhocTransaction = AdhocTransaction::create([
                    'at_b_id' => $business_id,
                    'at_emp_id' => $employee->emp_id,
                    'at_emp_d_id' => $employee->emp_d_id,
                    'at_pp_id' => $payrollPeriodId,
                    'at_e_amount' => 0,
                    'at_d_amount' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Loop through components and create detail records
            foreach ($earningInputs as $componentId => $earningAmount) {
                $earningAmount = floatval($earningAmount);
                $deductionAmount = floatval($deductionInputs[$componentId] ?? 0);
                $remark = $remarksinput[intval($componentId)] ?? null;

                if ($earningAmount == 0 && $deductionAmount == 0) {
                    continue;
                }

                AdhocTransactionDetail::create([
                    'adhoc_transaction_id' => $adhocTransaction->at_id,
                    'component_id' => $componentId,
                    'earning_amount' => $earningAmount,
                    'deduction_amount' => $deductionAmount,
                    'remarks' => $remark,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $totalEarnings += $earningAmount;
                $totalDeductions += $deductionAmount;
            }

            // Update totals
            $adhocTransaction->update([
                'at_e_amount' => $totalEarnings,
                'at_d_amount' => $totalDeductions,
                'updated_at' => now(),
            ]);
        }


        return redirect()->route('adhoc.index')->with('success', 'Adhoc entries saved successfully.');
    }


    public function searchByName(Request $request)
    {
        $query = $request->get('q');
        $employees = Employee::where('emp_full_name', 'like', "%$query%")
            ->select('emp_id', 'emp_full_name', 'emp_code')
            ->limit(10)
            ->get();
        return response()->json($employees);
    }

    public function searchByCode(Request $request)
    {
        $code = $request->get('emp_code');
        $employee = Employee::where('emp_code', $code)
            ->select('emp_id', 'emp_full_name', 'emp_code')
            ->first();
        return response()->json($employee);
    }


    public function searchByAll(Request $request)
    {
        $query = $request->input('q'); // Search term
        $status = $request->input('status'); // Optional status

        $employees = Employee::select('emp_id', 'emp_full_name', 'emp_fname', 'emp_mname', 'emp_lname', 'emp_code')
            ->where('emp_b_id', $this->user->emp_b_id) // Filter by business ID
            ->where('emp_status', 71)
            ->where(function ($qBuilder) use ($query) {
                $qBuilder->where('emp_full_name', 'like', '%' . $query . '%')
                    ->orWhere('emp_fname', 'like', '%' . $query . '%')
                    ->orWhere('emp_mname', 'like', '%' . $query . '%')
                    ->orWhere('emp_lname', 'like', '%' . $query . '%')
                    ->orWhere('emp_code', 'like', '%' . $query . '%')
                    ->orWhereRaw("CONCAT(emp_fname, ' ', emp_lname) LIKE ?", ["%{$query}%"]);
            });

        // Optional status filter
        if (!empty($status)) {
            $employees->where('emp_status', $status);
        }

        return response()->json($employees->get());
    }

    public function exportSampleAdhocComponent()
    {

        $user = Auth::user();
        $businessId = $user->emp_b_id;
        $componentsExist = \App\Models\AdhocComponent::where('ac_adhoc_business_id', $businessId)->exists();
        // dd($componentsExist);

        if (!$componentsExist) {
            return redirect()->back()->with('error', 'No Adhoc Components found.');
        }

        $fileName = 'AdhocComponent_Export_' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new AdhocComponentSampleExport($businessId), $fileName);
    }


    public function adhocComponentsImport(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls'
        ]);

        try {
            Excel::import(new AdhocComponentSampleImport(auth()->user()->emp_b_id), $request->file('file'));
            return back()->with('success', 'Adhoc Components Imported Successfully.');
        } catch (\Exception $e) {
            return response()->json(['message' => 'Import failed', 'error' => $e->getMessage()], 500);
        }
    }


    public function checkSalaryProcessed(Request $request)
    {
        try {
            $payrollPeriodId = $request->payroll_period_id;
            $empIds = [];

            // agar employee select hai
            if ($request->type === 'employee' && $request->employee_id) {
                $empIds[] = $request->employee_id;
            }

            // agar department select hai
            if ($request->type === 'department' && $request->department_id) {
                $empIds = Employee::where('emp_b_id', $request->business_id)
                    ->where('emp_d_id', $request->department_id)
                    ->pluck('emp_id')
                    ->toArray();
            }

            if (empty($empIds)) {
                return response()->json([
                    'status' => false,
                    'message' => 'No employees found for this selection.'
                ]);
            }

            $exists = ProcessedEmployeeSalary::whereIn('ps_emp_id', $empIds)
                ->where('ps_payroll_id', $payrollPeriodId)
                ->exists();

            if ($exists) {
                return response()->json([
                    'status' => false,
                    'message' => 'Salary has already been processed for the selected payroll period.'
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Allowed to continue.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
}
