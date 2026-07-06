<?php

namespace App\Http\Controllers\Web\Admin\AttendanceDetails;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\PolicyLeave;
use App\Models\LeaveType;
use App\Models\MasterTable;
use App\Models\EmployeeOpeningBalance;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OpeningBalanceSampleExport;
use App\Imports\OpeningBalanceImport;

class OpeningBalanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        if ($request->ajax() && $request->has('modal_emp_id')) {
            $empId = $request->input('modal_emp_id');

            $records = EmployeeOpeningBalance::where('eob_emp_id', $empId)
                ->orderByDesc('created_at')
                ->get(['eob_emp_id','eob_cl', 'eob_sl', 'eob_el', 'eob_total_leave', 'created_at'])
                ->map(function ($item) {
                    return [
                        'emp_name' => $item->employee->emp_full_name. ' '. $item->employee->emp_code,
                        'eob_cl' => $item->eob_cl,
                        'eob_sl' => $item->eob_sl,
                        'eob_el' => $item->eob_el,
                        'eob_total_leave' => $item->eob_total_leave,
                        'created_at' => $item->created_at->format('d-M-Y h:i A'),
                    ];
                });

            return response()->json($records);
        }

        $user = Auth::user();
        $businessId = $user->emp_b_id;

        $branch = Branch::whereNull('br_b_id')->orWhere('br_b_id', $businessId)->get();
        $departments = Department::whereNull('d_b_id')->orWhere('d_b_id', $businessId)->get();
        $designations = Designation::whereNull('dg_b_id')->orWhere('dg_b_id', $businessId)->get();

        $branchFilter = $request->input('balance_branchFilter');
        $departmentFilter = $request->input('balance_departmentFilter');
        $designationFilter = $request->input('balance_designationFilter');
        $monthFilter = $request->input('mt_monthFilter');
        $activeFilter = $request->input('balance_activeFilter');

        $activeLeaveTypeIds = [207, 208, 209];
        // $masterLeaveCategory = MasterTable::whereIn('m_id', $activeLeaveTypeIds)->pluck('m_name', 'm_id');
        $masterLeaveCategory = MasterTable::where('m_group', 'LEAVE_CATEGORY')->pluck('m_name', 'm_id', 'm_type');
        $leavePolicy = PolicyLeave::where('pl_b_id', $user->emp_b_id)->first();
        $leaveTypes = LeaveType::where('lvt_pl_id', $leavePolicy->pl_id)->whereIn('lvt_cat_type_id', $masterLeaveCategory->keys())->get();

        $employees = Employee::where('emp_b_id', $businessId)
            ->where('emp_status', 71)
            ->where('emp_role_id', '!=', 1)
            ->get();

        $columns = [
            'S. No.',
            'Emp. Name',
            'Emp. Code',
        ];

        foreach ($masterLeaveCategory as $key => $name) {
            $columns[$key] = $name;
        }

        $columns[] = 'Total leave';
        $columns[] = 'W.E.F';
        $columns[] = 'Action';

        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['eob_b_id', $businessId]
                ],
                [
                    'method' => 'whereRaw',
                    'args' => [
                        'id = (
                            SELECT MAX(eob2.id)
                            FROM fh_opening_balances as eob2
                            WHERE eob2.eob_emp_id = fh_opening_balances.eob_emp_id
                        )'
                    ],
                    'relation' => []
                ],
                [
                    'method' => 'select',
                    'args' => [
                        'id',
                        'eob_emp_id',
                        'eob_cl',
                        'eob_sl',
                        'eob_el',
                        'eob_total_leave',
                        'created_at',
                        'updated_at'
                    ],
                    'relation' => [
                        'employee:emp_id,emp_fname,emp_mname,emp_lname,emp_code,emp_br_id,emp_d_id,emp_dg_id,emp_date_of_joining,emp_status'
                    ]
                ]
            ];

            // Optional filters
            if (!empty($branchFilter)) {
                $dynamicConditions[] = [
                    'method' => 'whereHas',
                    'args' => ['employee', function ($q) use ($branchFilter) {
                        $q->where('emp_br_id', $branchFilter);
                    }]
                ];
            }

            if (!empty($departmentFilter)) {
                $dynamicConditions[] = [
                    'method' => 'whereHas',
                    'args' => ['employee', function ($q) use ($departmentFilter) {
                        $q->where('emp_d_id', $departmentFilter);
                    }]
                ];
            }

            if (!empty($designationFilter)) {
                $dynamicConditions[] = [
                    'method' => 'whereHas',
                    'args' => ['employee', function ($q) use ($designationFilter) {
                        $q->where('emp_dg_id', $designationFilter);
                    }]
                ];
            }

            if ($activeFilter !== null && $activeFilter !== '') {
                $dynamicConditions[] = [
                    'method' => 'whereHas',
                    'args' => ['employee', function ($q) use ($activeFilter) {
                        $q->where('emp_status', $activeFilter);
                    }]
                ];
            }

            // Get filtered data with latest record per employee
            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new EmployeeOpeningBalance(),
                dynamicConditions: $dynamicConditions,
                searchColumns: ['eob_emp_id'],
                searchRelationships: [
                    'employee' => ['emp_fname', 'emp_lname', 'emp_mname', 'emp_code']
                ]
            ))->getServerSideDataTable();

            $rowData = [];
            $i = 1;

            foreach ($list as $balance) {
                $employee = $balance->employee;
                $row = [];
                $row[] = $i++;
                $row[] = $employee ? trim("{$employee->emp_fname} {$employee->emp_mname} {$employee->emp_lname}") : '-';
                $row[] = $employee->emp_code ?? '-';

                foreach ($masterLeaveCategory as $key => $name) {
                    if ($key == 207) {
                        $row[] = $balance->eob_cl ?? 0;
                    } elseif ($key == 208) {
                        $row[] = $balance->eob_sl ?? 0;
                    } elseif ($key == 209) {
                        $row[] = $balance->eob_el ?? 0;
                    } else {
                        $row[] = '-';
                    }
                }

                $row[] = $balance->eob_total_leave ?? 0;
                $row[] = !empty($balance->created_at) ? $balance->created_at->format('d-M-Y h:i A') : '';

                $row[] = '
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light" type="button" id="dropdownMenu' . $employee->emp_id . '" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenu' . $employee->emp_id . '">
                            <li>
                                <button class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2 openBtn"
                                    data-id="' . $employee->emp_id . '">
                                    <i class="feather feather-eye"></i> View
                                </button>
                            </li>
                            <li>
                                <button class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2 deleteAllOpeningBalance"
                                    data-id="' . $employee->emp_id . '">
                                    <i class="feather feather-trash"></i> Delete
                                </button>
                            </li>
                        </ul>
                    </div>';

                $rowData[] = $row;
            }

            return response()->json([
                "draw" => intval($request->input('draw')),
                "recordsTotal" => $list->count(),
                "recordsFiltered" => $list->count(), // Already filtered to latest per employee
                "data" => $rowData,
            ]);
        }

        return view('admin.setting.attendance-details.opening-balance', compact(
            'departments',
            'designations',
            'branch',
            'employees',
            'columns',
            'leavePolicy',
            'leaveTypes',
            'masterLeaveCategory'
        ));
    }


    public function add(Request $request)
    {
        $user = Auth::user();

        try {
            // Step 1: Validate
            $validator = Validator::make($request->all(), [
                'eob_emp_id' => ['required', 'integer', 'exists:employees,emp_id'],
                'eob_cl' => ['required', 'numeric', 'min:0', 'max:30', 'regex:/^\d+(\.\d{1,2})?$/'],
                'eob_sl' => ['required', 'numeric', 'min:0', 'max:30', 'regex:/^\d+(\.\d{1,2})?$/'],
                'eob_el' => ['nullable', 'numeric', 'min:0', 'max:30', 'regex:/^\d+(\.\d{1,2})?$/']
            ], [
                'eob_emp_id.required' => 'Please select an employee.',
                'eob_emp_id.exists' => 'Selected employee does not exist.',
                'eob_cl.required' => 'Casual Leave is required.',
                'eob_cl.numeric' => 'Casual Leave must be a valid number.',
                'eob_cl.min' => 'Casual Leave cannot be negative.',
                'eob_cl.max' => 'Casual Leave cannot exceed 30 days.',
                'eob_cl.regex' => 'Casual Leave can have maximum 2 decimal places.',
                'eob_sl.required' => 'Sick Leave is required.',
                'eob_sl.numeric' => 'Sick Leave must be a valid number.',
                'eob_sl.min' => 'Sick Leave cannot be negative.',
                'eob_sl.max' => 'Sick Leave cannot exceed 30 days.',
                'eob_sl.regex' => 'Sick Leave can have maximum 2 decimal places.',
                'eob_el.min' => 'Earned Leave cannot be negative.',
                'eob_el.max' => 'Earned Leave cannot exceed 30 days.',
                'eob_el.regex' => 'Earned Leave can have maximum 2 decimal places.',
            ]);

            // Custom validation logic
            $validator->after(function ($validator) use ($request) {
                $cl = floatval($request->eob_cl);
                $sl = floatval($request->eob_sl);
                $el = floatval($request->eob_el ?? 0);

                if ($cl == 0 && $sl == 0 && $el == 0) {
                    $validator->errors()->add('eob_cl', 'At least one leave balance must be greater than 0.');
                }

                if (($cl + $sl + $el) > 90) {
                    $validator->errors()->add('eob_cl', 'Total leave balance cannot exceed 90 days.');
                }

                $employee = Employee::find($request->eob_emp_id);
                if ($employee && $employee->emp_status != 71) {
                    $validator->errors()->add('eob_emp_id', 'Opening balance can only be set for active employees.');
                }
            });

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Step 2: Get values
            $empId = $request->eob_emp_id;
            $year = now()->year;
            $month = now()->month;
            $cl = floatval($request->eob_cl);
            $sl = floatval($request->eob_sl);
            $el = floatval($request->eob_el ?? 0);

            \DB::beginTransaction();

            // Get last inserted opening balance
            $lastOpeningBalance = EmployeeOpeningBalance::where('eob_emp_id', $empId)->latest()->first();

            // Step 3: Fetch previous opening balance
            $lastOpening = EmployeeOpeningBalance::where('eob_emp_id', $empId)->latest()->first();

            $previousLeaves = [
                207 => $lastOpening->eob_cl ?? 0,
                208 => $lastOpening->eob_sl ?? 0,
                209 => $lastOpening->eob_el ?? 0,
            ];

            $newLeaves = [
                207 => $cl,
                208 => $sl,
                209 => $el,
            ];

            // Step 4: Update Leave Balance (subtract old, add new)
            foreach ($newLeaves as $catId => $newValue) {
                $oldValue = $previousLeaves[$catId];

                $leaveBalance = LeaveBalance::firstOrNew([
                    'lb_b_id' => $user->emp_b_id,
                    'lb_emp_id' => $empId,
                    'lb_cat_type_id' => $catId,
                    'lb_year' => $year,
                    'lb_month' => $month,
                ]);

                $leaveBalance->lb_alloted_leave = max(($leaveBalance->lb_alloted_leave ?? 0) - $oldValue + $newValue, 0);
                $leaveBalance->lb_balance_remaining_leave = max(($leaveBalance->lb_balance_remaining_leave ?? 0) - $oldValue + $newValue, 0);

                $taken = $leaveBalance->lb_taken_leave ?? 0;
                // $leaveBalance->lb_carried_forward = max($leaveBalance->lb_balance_remaining_leave - $taken, 0);

                $leaveBalance->save();
            }

            // Step 5: Save new opening balance
            $openingBalance = new EmployeeOpeningBalance();
            $openingBalance->eob_emp_id = $empId;
            $openingBalance->eob_b_id = $user->emp_b_id;
            $openingBalance->eob_cl = $cl;
            $openingBalance->eob_sl = $sl;
            $openingBalance->eob_el = $el;
            $openingBalance->created_at = now();
            $openingBalance->updated_at = now();
            $openingBalance->save();

            \DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Opening balance saved successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving the opening balance.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function import(Request $request)
    {
        // Validate incoming request data
        $request->validate([
            'file' => 'required|mimes:xlsx,csv|max:2048', // ✅ Added file type validation
        ]);
        //    dd($request->all());

        $file = $request->file('file');
        $opBalanceImport = new OpeningBalanceImport(Auth::user());

        try {
            // Attempt the import
            Excel::import($opBalanceImport, $file);
            $errorMessages = $opBalanceImport->getErrorMessages();

            if (!empty(array_filter($errorMessages))) {
                dd($errorMessages, count($errorMessages));
                session()->put('import_errors', $errorMessages);
                session()->flash('import_errors_blade', $errorMessages);
                return redirect()->back()->with('error', 'Import failed! Please check the errors.');
            }

            // ✅ If no errors, return success
            return redirect()->back()->with('success', 'Import completed successfully!');
        } catch (\Exception $e) {
            // ✅ Return user-friendly error message
            return redirect()->back()->with('error', 'Failed to import. Please check the file format and try again.');
        }
    }

    public function downloadSampleExcel()
    {
        return Excel::download(new OpeningBalanceSampleExport, 'opening_balance_upload_format.xlsx');
    }

    public function deleteEmpOpenBalance($emp_id) {
        EmployeeOpeningBalance::where('eob_emp_id', $emp_id)->delete();
        return response()->json(['success' => true]);
    }
}
