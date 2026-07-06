<?php

namespace App\Http\Controllers\Payroll;

use Carbon\Carbon;
use App\Models\Employee;
use App\Models\SalaryHold;
use Illuminate\Http\Request;
use App\Models\PayrollPeriod;
use App\Helpers\CentralLogics;
use App\Models\AttendanceSummary;
use App\Models\PolicyHolidayList;
use App\Http\Controllers\Controller;
use App\Models\ProcessedEmployeeSalary;
use Illuminate\Support\Facades\Auth;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;

class PayrollHoldSalaryController extends Controller {

    protected $user;

    public function __construct() {
        $this->user = Auth::user();
    }


    public function payrollHoldSalaryList(Request $request)
    {
        if ($this->user) {
            $business_id = $this->user->emp_b_id;
            // dd($business_id);

            if ($request->ajax()) {
                $dynamicConditions = [
                    [
                        'method' => 'where',
                        'args' => ['sh_b_id', $business_id],
                        'relation' => []
                    ],
                    [
                        'method' => 'with',
                        'args' => [
                            'employee',
                            'employee.fh_department',
                            'employee.fh_designation',
                            'employee.fh_employee_status',
                            'payrollPeriod',
                            'heldBy',
                            'releasedBy',
                        ],
                        'relation' => []
                    ],
                    [
                        'method' => 'select',
                        'args' => [
                            'sh_id',
                            'sh_emp_id',
                            'sh_dept_id',
                            'sh_pp_id',
                            'sh_status',
                            'sh_reason',
                            'sh_held_by',
                            'sh_released_by',
                            'sh_held_at',
                            'sh_released_at',

                        ],
                        'relation' => []
                    ],
                    [
                        'method' => 'orderBy',
                        'args' => ['sh_id', 'desc'],
                        'relation' => []
                    ]
                ];

                $searchColumns = [
                    'fh_employee.emp_code',
                    'fh_employee.emp_full_name',
                    'fh_department.d_name',
                    'fh_designation.dg_name',
                    'fh_employee_status.m_name',
                    'salary_holds.sh_status',
                    'salary_holds.sh_reason',
                ];

                $list = (new DynamicModelDataTableHelper(
                    eloquentModel: new SalaryHold(),
                    dynamicConditions: $dynamicConditions,
                    searchColumns: $searchColumns
                ))->getServerSideDataTable();

                $rowData = [];
                $i = $request->input('start', 0);
                foreach ($list as $val) {
                    // dd($val);
                    $employee = $val->employee;

                    $rowData[] = [
                        ++$i,
                        optional($employee)->emp_code ?? '-',
                        optional($employee)->emp_full_name ?? '-',
                        optional(optional($employee)->fh_department)->d_name ?? '-',
                        optional(optional($employee)->fh_designation)->dg_name ?? '-',
                        optional($val->payrollPeriod)->pp_name ?? '-',
                        optional(optional($employee)->fh_employee_status)->m_name ?? '-',
                        ucfirst($val->sh_status),
                        $val->sh_reason ?? '-',
                        optional($val->heldBy)->emp_full_name ?? '-',
                        $val->sh_held_at ? \Carbon\Carbon::parse($val->sh_held_at)->format('d-M-Y') : '-',
                        optional($val->releasedBy)->emp_full_name ?? '-',
                        $val->sh_released_at ? \Carbon\Carbon::parse($val->sh_released_at)->format('d-M-Y') : '-',
                       '<div class="btn-list ms-3">
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu p-2" style="min-width: 180px;">
                                    <li>
                                        <a href="' . route('withheld.salary.edit', $val->sh_id) . '"
                                        class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2">
                                            <i class="feather feather-edit"></i> Edit
                                        </a>
                                    </li>
                                    <li>
                                        <button type="button"
                                                class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2 deleteHoldBtn"
                                                data-id="' . $val->sh_id . '">
                                            <i class="feather feather-trash-2"></i> Delete
                                        </button>
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
                        eloquentModel: new SalaryHold(),
                        dynamicConditions: $dynamicConditions
                    ))->countFilteredServerSideDataTable(),
                    "data" => $rowData,
                ];

                return response()->json($output);
            }

            // -------- Page Load (Non-AJAX) --------
            $columns = [
                'S. No.',
                'Emp Code',
                'Employee',
                'Department',
                'Designation',
                'Payroll Period',
                'Status',
                'Hold Status',
                'Reason',
                'Held By',
                'Held At',
                'Released By',
                'Released At',
                'Action',
            ];

            $employeeList = Employee::with('fh_department')
                ->where('emp_b_id', $business_id)
                ->get();

            $departmentList = $employeeList->pluck('fh_department')
                ->unique('d_id')
                ->filter()
                ->values();

            $payroll_periods = PayrollPeriod::where('pp_b_id', $business_id)
                ->orderBy('pp_start_date', 'asc')
                ->get();

            return view('admin.payroll.hold-salary', compact(
                'columns',
                'employeeList',
                'departmentList',
                'payroll_periods'
            ));
        } else {
            abort(404);
        }
    }


    public function storeWithHoldSalary(Request $request)
    {
        $business_id = $this->user->emp_b_id;

        $request->validate([
            'payroll_period_id' => 'required|array',
            'reason'            => 'nullable|string',
            'employee_id'       => 'nullable|integer',
            'department_id'     => 'nullable|integer',
        ]);

        $userId = Auth::id();
        $now    = now();

        try {
            if ($request->filled('employee_id')) {
                $alreadyProcessed = ProcessedEmployeeSalary::where('ps_emp_id', $request->employee_id)
                ->where('ps_payroll_id', $request->payroll_period_id)
                ->exists();

                if ($alreadyProcessed) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Salary already processed for this employee in the selected payroll period. Cannot hold.'
                    ]);
                }


                $alreadyHeld = SalaryHold::where('sh_emp_id', $request->employee_id)
                ->where('sh_pp_id', $request->payroll_period_id)
                ->where('sh_status', 'held')
                ->exists();

                if ($alreadyHeld) {
                    return response()->json([
                        'status' => false,
                        'message' => 'This employee’s salary is already on hold for the selected payroll period.'
                    ]);
                }

                SalaryHold::create([
                    'sh_emp_id'   => $request->employee_id,
                    'sh_b_id' => $business_id,
                    'sh_dept_id'  => null,
                    'sh_pp_id'    => $request->payroll_period_id,
                    'sh_status'   => 'held',
                    'sh_reason'   => $request->reason,
                    'sh_held_by'  => $userId,
                    'sh_held_at'  => $now,
                ]);

            } elseif ($request->filled('department_id')) {
                $employees = Employee::where('emp_d_id', $request->department_id)->pluck('emp_id');

                foreach ($employees as $empId) {
                    $alreadyProcessed = ProcessedEmployeeSalary::where('ps_emp_id', $empId)
                        ->where('ps_payroll_id', $request->payroll_period_id)
                        ->exists();

                    if ($alreadyProcessed) {
                        return response()->json([
                            'status' => false,
                            'message' => 'Salary already processed for one or more employees in this department. Cannot hold.'
                        ]);
                    }

                    SalaryHold::create([
                        'sh_emp_id'   => $empId,
                        'sh_b_id' => $business_id,
                        'sh_dept_id'  => $request->department_id,
                        'sh_pp_id'    => $request->payroll_period_id,
                        'sh_status'   => 'held',
                        'sh_reason'   => $request->reason,
                        'sh_held_by'  => $userId,
                        'sh_held_at'  => $now,
                    ]);
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Salary hold record(s) saved successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => '❌ Something went wrong: ' . $e->getMessage()
            ]);
        }
    }

}

