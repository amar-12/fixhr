<?php

namespace App\Http\Controllers\Web\Admin;

use App\Exports\SampleLeaveOpeningBalanceExport;
use App\Exports\EmployeeFieldSampleExport;
use App\Http\Controllers\Controller;
use App\Imports\LeaveOpeningImport;
use App\Imports\EmployeeFieldImport;
use Illuminate\Support\Facades\Validator;
use App\Models\EmployeeApprovalStatus;
use App\Models\Employee;
use App\Models\EmployeeManagerLog;
use App\Models\LeaveOpeningBalance;
use App\Models\LeaveBalance;
use App\Models\MasterTable;
use App\Helpers\CentralLogics;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeSelfServiceController extends Controller
{
    public function index()
    {
        $attendanceStatus = MasterTable::where('m_group', 'ATTENDANCE_STATUS')->get();
        return view('admin.employees.emp_self_service', compact('attendanceStatus'));
    }

    public function changeEmployeeManager(Request $request)
    {
        $formType = $request->input('form_type');
        $wefDate = $request->wefDate ? Carbon::parse($request->wefDate)->toDateString() : now()->toDateString();

        if ($formType === 'REPORTING_MANAGER') {
            // Validation
            $validator = Validator::make($request->all(), [
                'current_reporting_manager' => 'required|int',
                'new_reporting_manager'     => 'required|int|different:current_reporting_manager',
                // 'wefDate'                   => 'required|date',
                'reason'                    => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'form'    => 'REPORTING_MANAGER',
                    'message' => 'Validation failed',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $empReportingManager = Employee::where('emp_supervisor_id', $request->current_reporting_manager);

            if (!$empReportingManager->exists()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Reporting Manager does not exist.',
                ], 200);
            }

            $empManagerLog = EmployeeManagerLog::where('eml_form_type', $formType)
                ->where('eml_old_manager_id', $request->current_reporting_manager)
                ->where('eml_new_manager_id', $request->new_reporting_manager)
                ->whereDate('eml_wef_date', $request->wefDate)
                ->exists();

            if ($empManagerLog) {
                return response()->json([
                    'status'  => false,
                    'message' => 'This manager change request already exists.',
                ], 409);
            }
            EmployeeManagerLog::create([
                'eml_form_type'      => $formType,
                'eml_old_manager_id' => $request->current_reporting_manager,
                'eml_new_manager_id' => $request->new_reporting_manager,
                'eml_wef_date'       => $request->wefDate ?? date('Y-m-d'),
                'eml_reason'         => $request->reason,
            ]);

            if ($wefDate <= now()->toDateString()) {
                $empReportingManager->update([
                    'emp_supervisor_id' => $request->new_reporting_manager,
                ]);
            }

            return response()->json([
                'status'  => true,
                'form'    => 'REPORTING_MANAGER',
                'message' => 'Reporting Manager updated successfully',
            ], 200);
        } elseif ($formType === 'APPROVAL_MANAGER') {
            // Validation
            $validator = Validator::make($request->all(), [
                'current_approval_manager' => 'required|int',
                'new_approval_manager'     => 'required|int|different:current_approval_manager',
                // 'wefDate'                  => 'required|date',
                'reason'                   => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'form'    => 'APPROVAL_MANAGER',
                    'message' => 'Validation failed',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $empApprovalData = EmployeeApprovalStatus::where('eas_approvel_id', $request->current_approval_manager);

            if (!$empApprovalData->exists()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Approval Manager does not exist.',
                ], 200);
            }

            $empAManagerLog = EmployeeManagerLog::where('eml_form_type', $formType)
                ->where('eml_old_manager_id', $request->current_approval_manager)
                ->where('eml_new_manager_id', $request->new_approval_manager)
                ->whereDate('eml_wef_date', $request->wefDate)
                ->exists();

            if ($empAManagerLog) {
                return response()->json([
                    'status'  => false,
                    'message' => 'This manager change request already exists.',
                ], 409);
            }

            EmployeeManagerLog::create([
                'eml_form_type'      => $formType,
                'eml_old_manager_id' => $request->current_approval_manager,
                'eml_new_manager_id' => $request->new_approval_manager,
                'eml_wef_date'       => $request->wefDate ?? date('Y-m-d'),
                'eml_reason'         => $request->reason,
            ]);

            if ($wefDate <= now()->toDateString()) {
                $empApprovalData->update([
                    'eas_approvel_id' => $request->new_approval_manager,
                ]);
            }

            return response()->json([
                'status'  => true,
                'form'    => 'APPROVAL_MANAGER',
                'message' => 'Approval Manager updated successfully',
            ], 200);
        }

        return response()->json([
            'status'  => false,
            'message' => 'Invalid form type',
        ], 400);
    }

    public function getEmployeeApproval(Request $request)
    {
        $approvalId = null;

        if ($request->form_type === 'REPORTING_MANAGER') {
            $approvalId = $request->current_reporting_manager;
        } elseif ($request->form_type === 'APPROVAL_MANAGER') {
            $approvalId = $request->current_approval_manager;
        }

        if (!$approvalId) {
            return response()->json([
                'status'  => false,
                'message' => 'Manager is required'
            ], 422);
        }

        $empApprovalData = EmployeeApprovalStatus::where('eas_approvel_id', $approvalId)->get();

        if ($empApprovalData->isEmpty()) {
            return response()->json([
                'status'  => false,
                'message' => 'Manager does not exist.'
            ], 200);
        }
    }

    public function getLeaveBalance(Request $request)
    {
        // Fetch employee and ensure same business
		$employee = Employee::where('emp_id', $request->employee_id)->where('emp_b_id', Auth::user()->emp_b_id)->first();
		if (!$employee) {
			return response()->json(['message' => 'Employee not found or unauthorized'], 404);
		}

		$apiController = app()->make(\App\Http\Controllers\Api\Attendance\EmployeeLeaveController::class);
		$leaveBalance = $apiController->getLeaveBalance($employee->emp_id);
        return response()->json($leaveBalance);
    }

    function updateLeaveBalance(Request $request) 
    {
        // Validate request format
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|integer',
            'leave_balances' => 'required|array|min:1',
            'leave_balances.*.leave_type_id' => 'required|integer',
            'leave_balances.*.updated_balance' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check employee exists and belongs to same business
        $employee = Employee::where('emp_id', $request->employee_id)
            ->where('emp_b_id', Auth::user()->emp_b_id)
            ->first();

        if (!$employee) {
            return response()->json(['message' => 'Employee not found or unauthorized'], 404);
        }

        try {
            DB::beginTransaction();

            foreach ($request->leave_balances as $balance) {
                // Get current leave balance if exists
                $currentBalance = LeaveBalance::where([
                    'lb_emp_id' => $employee->emp_id,
                    'lb_b_id' => $employee->emp_b_id,
                    'lb_cat_type_id' => $balance['leave_type_id'],
                    'lb_month' => now()->month,
                    'lb_year' => now()->year
                ])->first();

                $previousBalance = $currentBalance ? $currentBalance->lb_balance_remaining_leave : 0;

                // Calculate new totals
                $totalBalance = $previousBalance + $balance['updated_balance'];

                // Create Leave Opening Balance record
                LeaveOpeningBalance::create([
                    'lob_b_id' => $employee->emp_b_id,
                    'lob_emp_id' => $employee->emp_id,
                    'lob_leave_type_id' => $balance['leave_type_id'],
                    'lob_previous' => $previousBalance,
                    'lob_updated' => $balance['updated_balance'],
                    'lob_total' => $totalBalance,
                    'updated_by' => Auth::id()
                ]);

                // Update or create LeaveBalance
                $updateData = [
                    'lb_b_id' => $employee->emp_b_id,
                    'lb_emp_id' => $employee->emp_id,
                    'lb_cat_type_id' => $balance['leave_type_id'],
                    'lb_month' => now()->month,
                    'lb_year' => now()->year,
                    'lb_alloted_leave' => ($currentBalance ? $currentBalance->lb_alloted_leave : 0) + $balance['updated_balance'],
                    'lb_balance_remaining_leave' => ($currentBalance ? $currentBalance->lb_balance_remaining_leave : 0) + $balance['updated_balance'],
                ];

                if ($currentBalance) {
                    $currentBalance->update($updateData);
                } else {
                    LeaveBalance::create($updateData);
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Leave balances updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to update leave balances',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function leaveOpeningExport()
    {
        return Excel::download(new SampleLeaveOpeningBalanceExport(Auth::user()), 'leave_opening_balance_upload_format.xlsx');
    }

    public function leaveOpeningImport(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,csv',
        ]);

        $file = $request->file('import_file');
        $import = new LeaveOpeningImport(Auth::user());

        try {
            // Attempt the import
            Excel::import($import, $file);

            $errors = $import->getErrorMessages();
            $summary = $import->getSummary();

            if (!empty($errors)) {
                session()->put('import_errors', $errors);
                session()->flash('import_errors_blade', $errors);
                return redirect()->back()->with('error', 'Import failed! Please check the errors.');
            }

            // If no errors, redirect with success message
            return redirect()->back()->with('success', "Import completed successfully! Imported: {$summary['success']}, Skipped: {$summary['skipped']}");
            // return redirect()->back()->with('success', 'Import completed successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function missedPunchExport(Request $request)
    {
        $user = Auth::user();

        // FIXED: Correct date format parsing
        try {
            $startDate = Carbon::createFromFormat('d/m/Y', $request->start_date)->startOfDay();
            $endDate   = Carbon::createFromFormat('d/m/Y', $request->end_date)->endOfDay();
        } catch (\Exception $e) {
            return back()->with('error', 'Invalid date format. Please use dd/mm/yyyy.');
        }

        $attendanceStatus = $request->attendanceStatus;
        $statusName = MasterTable::where('m_id', $attendanceStatus)->value('m_name');

        // Get employees
        $employees = Employee::where('emp_b_id', $user->emp_b_id)
            ->where('emp_status', 71)
            ->where('emp_role_id', '!=', 1)
            ->get();

        $exportData = [];
        $i = 1;

        foreach ($employees as $employee) {

            $attendanceData = CentralLogics::newGetMonthlyAttendanceDetails(
                $employee,
                $startDate->format('m'),
                $startDate->format('Y'),
                collect(),
                []
            );

            foreach ($attendanceData as $record) {

                // FIX: record date is already database format Y-m-d
                $recordDate = Carbon::parse($record["date"]);

                if ($recordDate->lt($startDate) || $recordDate->gt($endDate)) {
                    continue;
                }

                if ($attendanceStatus && $record["status_id"] != $attendanceStatus) {
                    continue;
                }

                if ($employee->emp_date_of_joining && 
                    $recordDate->lt(Carbon::parse($employee->emp_date_of_joining))) {
                    continue;
                }

                if ($employee->emp_last_working_date && 
                    $recordDate->gt(Carbon::parse($employee->emp_last_working_date))) {
                    continue;
                }

                $exportData[] = [
                    $i++,
                    $employee->emp_code,
                    $employee->emp_full_name,
                    $recordDate->format('d/m/Y'),
                    $recordDate->format('d/m/Y'),
                    $record["checkInTime"]  ? Carbon::parse($record["checkInTime"])->format('H:i') : '',
                    $record["checkOutTime"] ? Carbon::parse($record["checkOutTime"])->format('H:i') : '',
                    $record["attendance_remark"],
                    $statusName ?? '',
                ];
            }
        }

        return Excel::download(
            new \App\Exports\MonthlyAttendanceStatusExport($exportData),
            'missed_punch_export.xlsx'
        );
    }

    public function downloadSampleField()
    {
        return Excel::download(new EmployeeFieldSampleExport(), 'field_sample_format.xlsx');
    }

    public function employeeFieldImport(Request $request)
    {
        $request->validate([
            'update_field' => 'required|in:emp_phone,emp_cost_center,emp_profit_center,emp_sap_budget_code',
            'import_file'  => 'required|file|mimes:xlsx,csv',
        ]);

        $file  = $request->file('import_file');
        $field = $request->input('update_field');

        $import = new EmployeeFieldImport($field);

        try {
            Excel::import($import, $file);

            $errors  = $import->getErrorMessages();
            $summary = $import->getSummary();

            if (!empty($errors)) {
                session()->put('import_errors', $errors);
                session()->flash('import_errors_blade', $errors);

                return response()->json([
                    'status'  => false,
                    'message' => 'Import completed with errors. Check the error list.',
                    'errors'  => $errors,
                    'summary' => $summary
                ], 422);
            }

            return response()->json([
                'status'  => true,
                'message' => "Import completed successfully! Imported: {$summary['success']}, Skipped: {$summary['skipped']}",
                'summary' => $summary
            ]);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            // Optional: handle excel validation errors nicely
            $failures = $e->failures();
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed in excel file.',
                'errors'  => $failures->map(fn($f) => [
                    'row' => $f->row(),
                    'attribute' => $f->attribute(),
                    'errors' => $f->errors()
                ])->toArray()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage() ?: 'Server error during import'
            ], 500);
        }
    }

    public function saveDeviceRestriction(Request $request)
    {
        $request->validate([
            'emp_id' => 'required|exists:employees,emp_id',
            'single_device_restriction' => 'required|in:0,1'
        ]);

        try {
            $employee = Employee::findOrFail($request->emp_id);

            $employee->emp_is_device_restriction = $request->single_device_restriction;
            $employee->save();

            return response()->json([
                'status' => true,
                'message' => 'Device restriction updated successfully'
            ]);

        } catch (\Exception $e) {

            Log::error($e);

            return response()->json([
                'status' => false,
                'message' => 'Device restriction not updated'
            ]);
        }
    }

    public function saveDeviceRestrictionAll(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'single_device_restriction' => 'required|in:0,1'
        ]);

        try {
            Employee::where('emp_status', 71)->where('emp_b_id', $user->emp_b_id)
                ->update([
                    'emp_is_device_restriction' => $request->single_device_restriction
                ]);

            return response()->json([
                'status' => true,
                'message' => 'All employees updated successfully'
            ]);

        } catch (\Exception $e) {

            Log::error($e);

            return response()->json([
                'status' => false,
                'message' => 'Not updated'
            ]);
        }
    }

    public function getDeviceRestriction(Request $request)
    {
        try {
            $request->validate([
                'emp_id' => 'required|exists:employees,emp_id'
            ]);
            $employee = Employee::findOrFail($request->emp_id);
            return response()->json([
                'status' => 1,
                'emp_is_device_restriction' => $employee->emp_is_device_restriction ?? 0
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json([
                'status' => 0,
                'emp_is_device_restriction' => 0,
                'message' => 'Failed to fetch data'
            ]);
        }
    }
}
