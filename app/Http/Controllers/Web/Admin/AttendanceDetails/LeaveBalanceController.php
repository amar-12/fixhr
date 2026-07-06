<?php

namespace App\Http\Controllers\Web\Admin\AttendanceDetails;

use App\Exports\ErrorExport;
use App\Http\Controllers\Controller;
use App\Imports\LeaveBalanceImport;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\MasterTable;
use Carbon\Carbon;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class LeaveBalanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;

        $branch = Branch::whereNull('br_b_id')->orWhere('br_b_id', $businessId)->get();
        $departments = Department::whereNull('d_b_id')->orWhere('d_b_id', $businessId)->get();
        $designations = Designation::whereNull('dg_b_id')->orWhere('dg_b_id', $businessId)->get();

        $branchFilter = $request->input('balance_branchFilter');
        $departmentFilter = $request->input('balance_departmentFilter');
        $designationFilter = $request->input('balance_designationFilter');
        $activeFilter = $request->input('balance_activeFilter');
        $monthFilter = $request->input('mt_monthFilter'); // format: YYYY-MM

        // Handle year and month extraction
        if (!empty($monthFilter) && str_contains($monthFilter, '-')) {
            [$year, $month] = explode('-', $monthFilter);
        } else {
            $year = null;
            $month = null;
        }

        // Get active leave types for the business
        $activeLeaveTypeIds = LeaveBalance::where('lb_b_id', $businessId)
            ->distinct()
            ->pluck('lb_cat_type_id');

        $masterLeaveCategory = MasterTable::whereIn('m_id', $activeLeaveTypeIds)
            ->pluck('m_name', 'm_id');

        // Prepare columns dynamically
        $columns = [
            'S. No.',
            'Emp. Name',
            'Emp. Code',
        ];
        foreach ($masterLeaveCategory as $key => $name) {
            $columns[$key] = $name;
        }
        $columns[] = 'Action'; // Add action column

        if ($request->ajax()) {
            $dynamicConditions = [
                ['method' => 'where', 'args' => ['emp_b_id', $businessId]],
                ['method' => 'where', 'args' => ['emp_role_id', '!=', '1']],
                [
                    'method' => 'with',
                    'args'   => ['leaveBalances']
                ],
            ];

            if (!empty($branchFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_br_id', $branchFilter]];
            }
            if (!empty($departmentFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_d_id', $departmentFilter]];
            }
            if (!empty($designationFilter)) {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_dg_id', $designationFilter]];
            }
            if ($activeFilter != '') {
                $dynamicConditions[] = ['method' => 'where', 'args' => ['emp_status', $activeFilter]];
            }

            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new Employee(),
                dynamicConditions: $dynamicConditions,
                searchColumns: ['emp_id', 'emp_fname', 'emp_mname', 'emp_lname', 'emp_code'],
                searchRelationships: [
                    'leaveBalances' => ['lb_cat_type_id', 'lb_year', 'lb_month', 'lb_balance_remaining_leave']
                ]
            ))->getServerSideDataTable();

            $rowData = [];
            $i = 1;

            foreach ($list as $employee) {
                $row = [];
                $row[] = $i++;
                $row[] = trim("{$employee->emp_fname} {$employee->emp_mname} {$employee->emp_lname}");
                $row[] = $employee->emp_code;

                // Initialize leave data with default value '-'
                $leaveData = [];
                foreach ($masterLeaveCategory as $key => $name) {
                    $leaveData[$key] = '-';
                }

                // Fill leave balances
                foreach ($employee->leaveBalances as $leave) {
                    if (!is_null($month) && !is_null($year)) {
                        if ($leave->lb_year == $year && $leave->lb_month == $month) {
                            if (isset($leaveData[$leave->lb_cat_type_id])) {
                                $leaveData[$leave->lb_cat_type_id] = $leave->lb_balance_remaining_leave;
                            }
                        }
                    } else {
                        if (isset($leaveData[$leave->lb_cat_type_id])) {
                            $leaveData[$leave->lb_cat_type_id] = $leave->lb_balance_remaining_leave;
                        }
                    }
                }

                // Append leave balances dynamically
                foreach ($masterLeaveCategory as $key => $name) {
                    $row[] = $leaveData[$key];
                }

                // Action dropdown
                $casualLeave = $employee->leaveBalances->where('lb_cat_type_id', 207)
                    ->where('lb_year', $year ?? $employee->leaveBalances->first()->lb_year ?? 0)
                    ->where('lb_month', $month ?? $employee->leaveBalances->first()->lb_month ?? 0)
                    ->sum('lb_balance_remaining_leave');

                $sickLeave = $employee->leaveBalances->where('lb_cat_type_id', 208)
                    ->where('lb_year', $year ?? $employee->leaveBalances->first()->lb_year ?? 0)
                    ->where('lb_month', $month ?? $employee->leaveBalances->first()->lb_month ?? 0)
                    ->sum('lb_balance_remaining_leave');

                $totalBalance = $employee->leaveBalances
                    ->when($year && $month, fn($q) => $q->where('lb_year', $year)->where('lb_month', $month))
                    ->sum('lb_balance_remaining_leave');

                $row[] = '
                <div class="btn-list ms-3">
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu p-2" style="min-width: 180px;">
                            <li>
                                <button class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2 view-balance"
                                    type="button"
                                    data-id="' . $employee->emp_id . '"
                                    data-casual="' . $casualLeave . '"
                                    data-sick="' . $sickLeave . '"
                                    data-total="' . $totalBalance . '">
                                    <i class="feather feather-eye"></i> View
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>';

                $rowData[] = $row;
            }

            $output = [
                "draw" => intval($request->input('draw')),
                "recordsTotal" => $list->count(),
                "recordsFiltered" => (new DynamicModelDataTableHelper(
                    eloquentModel: new Employee(),
                    dynamicConditions: $dynamicConditions
                ))->countFilteredServerSideDataTable(),
                "data" => $rowData,
            ];

            return response()->json($output);
        }

        return view('admin.setting.attendance-details.leave-balance', compact(
            'departments',
            'designations',
            'branch',
            'columns'
        ));
    }

    public function import(Request $request)
    {
        // Validate incoming request data
        $request->validate([
            'file' => 'required|mimes:xlsx,csv|max:2048', // ✅ Added file type validation
        ]);
        //    dd($request->all());

        $file = $request->file('file'); // ✅ Ensure correct input name
        $LeaveBalanceImport = new LeaveBalanceImport(Auth::user());

        try {
            // Attempt the import
            Excel::import($LeaveBalanceImport, $file);

            // Get error messages from the import process
            $errorMessages = $LeaveBalanceImport->getErrorMessages();

            if (!empty($errorMessages)) {
                // ✅ Flash import errors to the session
                session()->put('leave_import_errors', $errorMessages);
                session()->flash('leave_import_errors_blade', $errorMessages);
                return redirect()->back()->with('error', 'Import failed! Please check the errors.');
            }

            // ✅ If no errors, return success
            return redirect()->back()->with('success', 'Import completed successfully!');
        } catch (\Exception $e) {
            // ✅ Return user-friendly error message
            return redirect()->back()->with('error', 'Failed to import. Please check the file format and try again.');
        }
    }

    public function leaveBalance()
    {
        $users = leaveBalance::get();

        return view('leave-balance', compact('leave-balance'));
    }

    public function downloadSampleExcel()
    {
        // $filename = "employee-sheet.xlsx";
        // return Excel::download(new EmployeeExport(), $filename);
        $filePath = public_path('upload_sample/leave-balance-request-bulk-upload.csv');
        dd($filePath);
        // Check if the file exists
        if (file_exists($filePath)) {
            // Return the file as a download response
            return response()->download($filePath);
        } else {
            // If the file doesn't exist, return a 404 error
            return abort(404, 'File not found');
        }
    }

    public function downloadErrorFile()
    {
        if (!session()->has('leave_import_errors')) {
            return redirect()->back();
            return redirect()->route('employee.import')->with('error', 'No error file found!');
        }

        $errorMessages = session()->get('leave_import_errors');
        session()->forget('import_errors_blade');
        session()->forget('leave_import_errors');
        // Return the Excel download with the error messages
        return Excel::download(new ErrorExport($errorMessages), 'import_errors.xlsx');

        // Get the error messages from the session
        $errorMessages = session()->get('import_errors');
        session()->forget('import_errors_blade');
        session()->forget('import_errors');
        // Return the Excel download with the error messages
        return Excel::download(new ErrorExport($errorMessages), 'import_errors.xlsx');
    }
}
