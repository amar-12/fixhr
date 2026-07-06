<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\AdhocComponent;
use App\Models\AdhocTransaction;
use App\Models\Department;
use App\Models\Employee;
use App\Models\FinancialYear;
use App\Models\MasterTable;
use App\Models\PayrollPeriod;
use App\Models\RecurringTransaction;
use App\Models\RecurringTransactionDetail;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class RecurringPayDeducController extends Controller
{
     protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function index(Request $request)
    {
        if (!$this->user) {
            abort(404);
        }

        $business_id = $this->user->emp_b_id;

        // AJAX Request for DataTable
        if ($request->ajax()) {

            $dynamicConditions = [
                [
                    'method' => 'join',
                    'args' => ['employees', 'employees.emp_id', '=', 'recurring_transactions.rt_emp_id'],
                    'relation' => []
                ],
                [
                    'method' => 'leftJoin',
                    'args' => ['departments', 'departments.d_id', '=', 'recurring_transactions.rt_emp_d_id'],
                    'relation' => []
                ],
                [
                    'method' => 'leftJoin',
                    'args' => ['designations', 'designations.dg_id', '=', 'employees.emp_dg_id'],
                    'relation' => []
                ],
                [
                    'method' => 'where',
                    'args' => ['recurring_transactions.rt_b_id', $business_id],
                    'relation' => []
                ],
                [
                    'method' => 'select',
                    'args' => [
                        'recurring_transactions.*',
                        'employees.emp_code',
                        'employees.emp_full_name',
                        'departments.d_name as department_name',
                        'designations.dg_name as designation_name'
                    ],
                    'relation' => []
                ],
                [
                    'method' => 'orderBy',
                    'args' => ['recurring_transactions.rt_id', 'desc'],
                    'relation' => []
                ]
            ];

            $searchColumns = [
                'employees.emp_code',
                'employees.emp_full_name',
                'departments.d_name',
                'designations.dg_name'
            ];

            $dataHelper = new DynamicModelDataTableHelper(
                eloquentModel: new RecurringTransaction(),
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns
            );

            $list = $dataHelper->getServerSideDataTable();
            $rowData = [];
            $i = $request->input('start', 0);

            foreach ($list as $val) {
                $employee = $val->employee;
                $monthRange = Carbon::parse($val->rt_start_month)->format('M Y') . ' - ' . Carbon::parse($val->rt_end_month)->format('M Y');


                $rowData[] = [
                    ++$i,
                    optional($employee)->emp_code ?? '-',
                    optional($employee)->emp_full_name ?? '-',
                    optional(optional($employee)->fh_department)->d_name ?? '-',
                    optional(optional($employee)->fh_designation)->dg_name ?? '-',
                     $monthRange,
                    number_format($val->rt_e_amount ?? 0, 2),
                    number_format($val->rt_d_amount ?? 0, 2),
                    '<div class="btn-list ms-3">
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu p-2" style="min-width: 180px;">
                            <li>
                                <a href="' . route('recurring.edit', ['employee_id' => $val->rt_emp_id, 'payroll_period_id' => $val->rd_id]) . '"
                                class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2">
                                    <i class="feather feather-edit"></i> Edit
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>'
                ];
            }

            return response()->json([
                "draw" => intval($request->input('draw')),
                "recordsTotal" => sizeof($list),
                "recordsFiltered" => (new DynamicModelDataTableHelper(
                    eloquentModel: new RecurringTransaction(),
                    dynamicConditions: $dynamicConditions
                ))->countFilteredServerSideDataTable(),
                "data" => $rowData,
            ]);
        }

        // Blade View data
        $columns = [
            'S. No.',
            'Emp Code',
            'Employee',
            'Department',
            'Designation',
            'Month Range',  
            'Earnings',
            'Deductions',
            'Action',
        ];

        $employee = Employee::with('fh_department', 'fh_designation')
            ->where('emp_b_id', $business_id)
            ->get();

        $departments = $employee->pluck('fh_department')
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
        $existingTransactions = collect(); // No adhoc transactions required here

        return view('admin.payroll.recurring-payments-deductions', compact(
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
    }


    public function editRecurringTransaction(Request $request)
    {
        $employeeId     = $request->employee_id;

        $recurring = RecurringTransaction::where('rt_emp_id',$employeeId)->first();
        $startMonth = $recurring->rt_start_month; // stored as Y-m format?
        $endMonth   = $recurring->rt_end_month;   // same format
        
        // If stored as full date, convert to Y-m
        if ($startMonth) {
            $startMonth = \Carbon\Carbon::parse($startMonth)->format('Y-m');
        }
        if ($endMonth) {
            $endMonth = \Carbon\Carbon::parse($endMonth)->format('Y-m');
        }

        $payrollPeriodId = $request->payroll_period_id;
        $business_id    = auth()->user()->emp_b_id;
        // Load employee & department
        $employee   = Employee::with(['fh_department'])->where('emp_id', $employeeId)->firstOrFail();
        $department = $employee->fh_department;

        // Load financial year & payroll period
        $financialYear = FinancialYear::latest()->first()?->fy_year;
        $payrollPeriod = PayrollPeriod::find($payrollPeriodId);
        $type          = 'employee';

        // Load headings (display purpose)
        $recurringHeadings = MasterTable::where('m_group', 'PAYROLL_HEADINGS')->get();

        // Load all Recurring Components
        $recurringComponents = AdhocComponent::with('payrollHeading')
            ->where('ac_adhoc_business_id', $business_id)
            ->orderBy('ac_id')
            ->get();

        // 🔁 Group by heading ID for proper Blade display
        $groupedRecurringComponents = $recurringComponents->groupBy('ac_adhoc_heading_id');

        $recurringTransaction = RecurringTransaction::where([
            ['rt_b_id',  $business_id],
            ['rt_emp_id', $employeeId],
        ])
        ->first();


        // Components
        $globalComponents = AdhocComponent::with('payrollHeading')
            ->where(function ($query) {
                $query->whereNull('ac_adhoc_business_id')
                    ->orWhere('ac_adhoc_business_id', 0);
            })
            ->get();

        $businessComponents = AdhocComponent::with('payrollHeading')
            ->where('ac_adhoc_business_id', $business_id)
            ->get();

        $adhocHeadings = $globalComponents->merge($businessComponents);
        $groupedAdhocComponents = $adhocHeadings->groupBy('ac_adhoc_heading_id');


    
        // Load existing Recurring Transactions (saved earlier)
        $existingTransactions = RecurringTransactionDetail::with('component')
            ->whereHas('recurringTransaction', function ($q) use ($business_id, $employeeId) {
                $q->where([
                    ['rt_b_id',  $business_id],
                    ['rt_emp_id', $employeeId],    
                ]);
            })
            ->get()
            ->keyBy('rtd_component_id');


        return view('admin.payroll.recurring-form', compact(
            'business_id',
            'employee',
            'department',
            'type',
            'startMonth',
            'endMonth',
            'financialYear',
            'payrollPeriod',
            'recurringComponents',
            'groupedAdhocComponents',
            'recurringHeadings',
            'existingTransactions',
            'groupedRecurringComponents'
        ));
    }




    public function recurringformView(Request $request)
    {
        $type           = $request->type;
        $employee       = Employee::find($request->employee_id);
        $business_id    = $request->business_id;
        $department     = Department::find($request->department_id);
        $startMonth     = $request->start_month;
        $endMonth       = $request->end_month;

        // Payroll Period
        $payrollPeriod = PayrollPeriod::with('financialYear')
            ->where('pp_id', $request->payroll_period_id)
            ->first();

        // Current Financial Year
        $financialYears = FinancialYear::where('fy_b_id', $business_id)
            ->where('fy_is_current', 1)
            ->get();

        // Components
        $globalComponents = AdhocComponent::with('payrollHeading')
            ->where(function ($query) {
                $query->whereNull('ac_adhoc_business_id')
                    ->orWhere('ac_adhoc_business_id', 0);
            })
            ->get();

        $businessComponents = AdhocComponent::with('payrollHeading')
            ->where('ac_adhoc_business_id', $business_id)
            ->get();

        $adhocHeadings = $globalComponents->merge($businessComponents);
        $groupedAdhocComponents = $adhocHeadings->groupBy('ac_adhoc_heading_id');

        /**
         * 🔹 Existing recurring transaction logic
         * If recurring transaction MONTH range falls within selected month range
         */
        $existingData = RecurringTransactionDetail::select(
            'rtd_component_id',
            'rtd_earning_amount as earning_amount',
            'rtd_deduction_amount as deduction_amount',
            'rtd_remarks as remarks',
            'recurring_transactions.rt_start_month'
        )
            ->join('recurring_transactions', 'recurring_transactions.rt_id', '=', 'recurring_transaction_details.rtd_recurring_transaction_id')
            ->where('recurring_transactions.rt_emp_id', $employee->emp_id)
            ->where('recurring_transactions.rt_b_id', $business_id)
            ->whereBetween('recurring_transactions.rt_start_month', [$startMonth, $endMonth]) // ✔ Month range check
            ->get()
            ->keyBy('rtd_component_id');

        return view('admin.payroll.recurring-form', [
            'type' => $type,
            'department' => $department,
            'employee' => $employee,
            'adhocHeadings' => $adhocHeadings,
            'payrollPeriod' => $payrollPeriod,
            'business_id' => $business_id,
            'financialYears' => $financialYears,
            'groupedAdhocComponents' => $groupedAdhocComponents,
            'startMonth' => $startMonth,
            'endMonth' => $endMonth,
            'existingTransactions' => $existingData, // 🟢 Pass to view
        ]);
    }

    public function storeRecurringTransaction(Request $request)
    {
        $businessId   = $request->business_id;
        $startMonth   = $request->start_month;
        $endMonth     = $request->end_month;
        $employeeId   = $request->employee_id;
        $departmentId = $request->department_id;

        $earningInputs   = $request->earning ?? [];
        $deductionInputs = $request->deduction ?? [];
        $remarksInputs   = $request->remarks ?? [];

        // Fetch employees
        $employees = collect();
        if ($departmentId) {
            $employees = Employee::where('emp_d_id', $departmentId)->get();
        } elseif ($employeeId) {
            $employees = Employee::where('emp_id', $employeeId)->get();
        }

        foreach ($employees as $employee) {
            $totalEarnings   = 0;
            $totalDeductions = 0;

            // 🔍 Check existing record by emp + business only
            $recurringTransaction = RecurringTransaction::where([
                ['rt_b_id',  $businessId],
                ['rt_emp_id', $employee->emp_id],
            ])->first();

            // 🆕 If new → create
            if (!$recurringTransaction) {
                $recurringTransaction = RecurringTransaction::create([
                    'rt_b_id'        => $businessId,
                    'rt_emp_id'      => $employee->emp_id,
                    'rt_emp_d_id'    => $employee->emp_d_id,
                    'rt_start_month' => $startMonth,
                    'rt_end_month'   => $endMonth,
                    'rt_created_by'  => auth()->id(),
                ]);
            } else {
                // 🔁 If exists → update months only
                $recurringTransaction->update([
                    'rt_start_month' => $startMonth,
                    'rt_end_month'   => $endMonth,
                ]);

                // 🧹 Clear old component entries
                RecurringTransactionDetail::where('rtd_recurring_transaction_id', $recurringTransaction->rt_id)->delete();
            }

            // 🔄 Loop through earnings/deductions
            foreach ($earningInputs as $componentId => $earningAmount) {
                $earningAmount   = floatval($earningAmount);
                $deductionAmount = floatval($deductionInputs[$componentId] ?? 0);

                if ($earningAmount == 0 && $deductionAmount == 0) {
                    continue;
                }

                RecurringTransactionDetail::create([
                    'rtd_recurring_transaction_id' => $recurringTransaction->rt_id,
                    'rtd_component_id'             => $componentId,
                    'rtd_earning_amount'           => $earningAmount,
                    'rtd_deduction_amount'         => $deductionAmount,
                    'rtd_remarks'                  => $remarksInputs[$componentId] ?? null,
                    'created_at'                   => now(),
                    'updated_at'                   => now(),
                ]);

                $totalEarnings   += $earningAmount;
                $totalDeductions += $deductionAmount;
            }

            // 📌 Update total values only at the end
            $recurringTransaction->update([
                'rt_e_amount' => $totalEarnings,
                'rt_d_amount' => $totalDeductions,
            ]);
        }

        return redirect()->route('recurring.index')->with('success', 'Recurring entries saved successfully.');
    }



}
