<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Role;
use App\Models\Employee;
use App\Models\Department;
use App\Models\MasterTable;
use Illuminate\Http\Request;
use App\Helpers\CentralLogics;
use App\Helpers\ApprovalHelper;
use App\Models\ApprovalModule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Models\EmployeeApprovalMapping;
use App\Models\EmployeeApprovalStatus;
use App\Models\PolicyHolidayList;
use App\Models\ApprovalExpiryRejectDay;
use Illuminate\Support\Facades\Validator;

class FormController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function index()
    {
        // $id = 744;
        $id = 744;
        $existingRows = [];
        $status = MasterTable::where('m_group', 'APPROVAL_STATUS')
            ->whereNotIn('m_id', [139, 140, 156, 170, 171, 175, 192])
            ->get();
        if ($id) {
            $existingRows = EmployeeApprovalMapping::with('fh_employee_approver_manager_1', 'fh_employee_approver_manager_2')->where('eam_emp_id', $id)->get();
        }
        $approvalModules = MasterTable::where('m_group', 'MODULE')->pluck('m_name', 'm_id')->toArray();
        return view('form', compact('approvalModules', 'existingRows', 'status'));
    }

    public function getEmployees(Request $request)
    {
        $search = $request->input('search');
        $employees = collect([]);
        if ($search) {
            $keywords = explode(' ', $search);
            $employees = Employee::where('emp_b_id', $this->user->emp_b_id)
                ->where(function ($query) use ($keywords) {
                    foreach ($keywords as $word) {
                        $query->where(function ($subQuery) use ($word) {
                            $subQuery->where('emp_full_name', 'like', "%{$word}%")
                                ->orWhere('emp_code', 'like', "%{$word}%");
                        });
                    }
                })
                ->select(
                    'emp_id as id',
                    DB::raw("CASE WHEN emp_code IS NOT NULL THEN CONCAT(emp_code, ' - ', emp_full_name) ELSE emp_full_name END as name")
                )
                ->get();
        }

        return response()->json($employees);
    }

    public function getModules(Request $request)
    {
        $search = $request->input('search');
        $modules = MasterTable::where('m_group', 'MODULE')->where('m_name', 'like', "%{$search}%")
            ->select('m_id', 'm_name')
            ->get();

        return response()->json($modules);
    }

    public function getManagers(Request $request)
    {
        $search = $request->input('search');
        $extra_params = $request->input('extra_params', []);
        // normalize extra params to a safe array of column names
        $cols = [];
        if (is_array($extra_params)) {
            foreach ($extra_params as $c) {
                // basic sanitization: allow only alphanumeric and underscore
                if (is_string($c) && preg_match('/^[A-Za-z0-9_]+$/', $c)) {
                    $cols[] = $c;
                }
            }
        }

        $managers = collect([]);
        if ($search) {
            // Build select list: base columns + any sanitized extra columns
            $baseSelect = [
                'emp_id as id',
                DB::raw("CASE WHEN emp_code IS NOT NULL THEN CONCAT(emp_code, ' - ', emp_full_name) ELSE emp_full_name END as name"),
            ];

            $select = $baseSelect;
            if (!empty($cols)) {
                // merge sanitized column names (they will be quoted by the query builder)
                $select = array_merge($select, $cols);
            }

            $managers = Employee::where('emp_b_id', $this->user->emp_b_id)
                ->where(function ($query) use ($search) { // Group the search conditions
                    $query->where('emp_full_name', 'like', "%{$search}%") // Match emp_full_name
                        ->orWhere('emp_code', 'like', "%{$search}%"); // Or match emp_code
                })
                ->select($select)
                ->get();
        }

        return response()->json($managers);
    }

    public function getEmployeeApprovalFlow($empId, $moduleId)
    {
        $mapping = EmployeeApprovalMapping::with([
            'approvalStatuses.employee_approval_mapping',
            'fh_master_table',
            'approvalStatuses.manager'
        ])
            ->where('eam_emp_id', $empId)
            ->whereRaw('md5(eam_module_id) = ?', [$moduleId])
            ->first();

        if (!$mapping) {
            return response()->json(['message' => 'No approval flow found for this employee.'], 404);
        }

        return response()->json([
            'mapping_id' => $mapping->eam_id,
            'employee_id' => $mapping->eam_emp_id,
            'module' => $mapping->fh_master_table?->m_name,
            'approvers' => $mapping->approvalStatuses->map(function ($approver) {
                return [
                    'manager_id' => $approver->eas_approvel_id,
                    'manager_name' => $approver->manager?->emp_full_name,
                    'status_id' => $approver->eas_approvel_status,
                ];
            }),
        ]);
    }

    public function save_approvers(Request $request)
    {
        $loggedInUserBusinessId = $this->user->emp_b_id;
        $employeeId = $request->approvaer_emp_id; 
        $validated = $request->validate([
            'approvaer_module' => 'required|exists:master_table,m_id',
            'managers' => 'required|array|min:1',
            'managers.*' => 'required|exists:employees,emp_id',
            'statuses' => 'required|array|min:1',
        ]);

        
        $approvalModule = MasterTable::find($validated['approvaer_module']);
        
        $existingMapping = EmployeeApprovalMapping::where('eam_emp_id', $employeeId)
        ->where('eam_module_id', $approvalModule->m_id)
        ->first();

        if ($existingMapping) {
            EmployeeApprovalStatus::where('eas_eam_id', $existingMapping->eam_id)->delete();
            $existingMapping->delete();
        }

        $eam = EmployeeApprovalMapping::create([
            'eam_b_id' => $loggedInUserBusinessId,
            'eam_emp_id' => $employeeId,
            'eam_module_id' => $approvalModule->m_id,
        ]);

        foreach ($validated['managers'] as $index => $managerId) {
            EmployeeApprovalStatus::create([
                'eas_eam_id' => $eam->eam_id,
                'eas_approvel_id' => $managerId,
                'eas_approvel_status' => $validated['statuses'][$index] ?? null,
            ]);
        }

        return redirect()->back()->with('success', 'Approval Flow Saved Successfully!');
    }

      public function saveApproversAjax(Request $request)
    {
        $loggedInUserBusinessId = $this->user->emp_b_id;
        $employeeId = $request->approvaer_emp_id;

        // Validate request
        $validated = $request->validate([
            'approvaer_module' => 'required|exists:master_table,m_id',
            'managers' => 'required|array|min:1',
            'managers.*' => 'required|exists:employees,emp_id',
            'statuses' => 'required|array|min:1',
            'statuses.*' => 'required',
        ]);

        $approvalModule = MasterTable::find($validated['approvaer_module']);
        $existingMapping = EmployeeApprovalMapping::where('eam_emp_id', $employeeId)
            ->where('eam_module_id', $approvalModule->m_id)
            ->first();

        if ($existingMapping) {
            EmployeeApprovalStatus::where('eas_eam_id', $existingMapping->eam_id)->delete();
            $existingMapping->delete();
        }

        $eam = EmployeeApprovalMapping::create([
            'eam_b_id' => $loggedInUserBusinessId,
            'eam_emp_id' => $employeeId,
            'eam_module_id' => $approvalModule->m_id,
        ]);

        foreach ($validated['managers'] as $index => $managerId) {
            EmployeeApprovalStatus::create([
                'eas_eam_id' => $eam->eam_id,
                'eas_approvel_id' => $managerId,
                'eas_approvel_status' => $validated['statuses'][$index] ?? null,
            ]);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Approval Flow Saved Successfully!',
            ]);
        }

        return redirect()->back()->with('success', 'Approval Flow Saved Successfully!');
    }


    public function store(Request $request)
    {
        $loggedInUserBusinessId = $this->user->emp_b_id;

        // ✅ Validation Rules
        $rules = [
            'rows' => 'required|array',
            'rows.*.employee' => 'required|exists:employees,emp_id|same_business_as_user',
            'rows.*.approvers' => 'required|array|min:1',
            'rows.*.approvers.*.manager' => 'required|exists:employees,emp_id|same_business_as_user',
            'rows.*.approvers.*.status' => 'required|exists:master_table,m_id',
        ];

        // ✅ Extend validation to ensure manager belongs to same business
        Validator::extend('same_business_as_user', function ($attribute, $value) use ($loggedInUserBusinessId) {
            $employee = Employee::find($value);
            if (!$employee) {
                return false; // If employee doesn't exist, fail the validation
            }
            return $employee && $employee->emp_b_id == $loggedInUserBusinessId;
        });

        $validated = $request->validate($rules);
        $approvalModule = MasterTable::where(DB::raw('md5(m_id)'), $request->moduleId)->first();
        if (!$approvalModule) {
            return response()->json(['error' => 'Invalid module selected.'], 400);
        }
        // $row = $request->input('rows');
        DB::beginTransaction();

        try {
            foreach ($validated['rows'] as $index => $row) {
                $employeeId = $row['employee'];

                // ✅ Delete existing mapping (if any) for this employee + module
                $existingMapping = EmployeeApprovalMapping::where('eam_emp_id', $employeeId)
                    ->where('eam_module_id', $approvalModule->m_id)
                    ->first();

                if ($existingMapping) {
                    // EmployeeApprovalStatus::where('eas_eam_id', $existingMapping->eam_id)->delete();
                    // $existingMapping->delete();

                    // $message = ApprovalHelper::checkPending($employeeId, $approvalModule->m_id);
                    // if ($message) {
                    //     // return response()->json(['error' => $message], 400);
                    //     return response()->json(['error' => 'Please finish the approval process for this module before updating!'], 500);
                    // }

                    $existingApprovers = EmployeeApprovalStatus::where('eas_eam_id', $existingMapping->eam_id)
                    ->get()
                    ->keyBy('eas_approvel_id');

                    // Update mapping
                    $existingMapping->update([
                        'eam_b_id' => $loggedInUserBusinessId,
                        'eam_emp_id' => $employeeId,
                        'eam_module_id' => $approvalModule->m_id,
                        'eam_approver_manager_1' => null,
                        'eam_approver_manager_2' => null,
                    ]);

                    $eam = $existingMapping;
                } else {
                    // ✅ Create new mapping
                    $eam = EmployeeApprovalMapping::create([
                        'eam_b_id' => $loggedInUserBusinessId,
                        'eam_emp_id' => $employeeId,
                        'eam_module_id' => $approvalModule->m_id,
                        'eam_approver_manager_1' => null,
                        'eam_approver_manager_2' => null,
                    ]);

                    $existingApprovers = collect();
                }

                $newManagerIds = collect($row['approvers'])->pluck('manager')->toArray();
                EmployeeApprovalStatus::where('eas_eam_id', $eam->eam_id)
                    ->whereNotIn('eas_approvel_id', $newManagerIds)
                    ->update(['deleted_at' => now()]);

                foreach ($row['approvers'] as $approver) {
                    $managerId = $approver['manager'];
                    $statusId  = $approver['status'];

                    if ($existingApprovers->has($managerId)) {
                        // Manager already exists → UPDATE
                        $existingApprovers[$managerId]->update([
                            'eas_approvel_status' => $statusId
                        ]);
                    } else {
                        // Not exist → CREATE NEW
                        EmployeeApprovalStatus::create([
                            'eas_eam_id' => $eam->eam_id,
                            'eas_approvel_id' => $managerId,
                            'eas_approvel_status' => $statusId,
                        ]);
                    }
                }

                // ApprovalExpiryRejectDay::updateOrCreate(
                //     [
                //         'aer_b_id' => $this->user->emp_b_id,
                //         'aer_m_id' => $approvalModule->m_id,
                //     ],
                //     [
                //         'aer_day' => $request->exp_rej_day,
                //     ]
                // );
            }

            DB::commit();
            return response()->json(['message' => 'Approval Flow Saved Successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Approval Flow Save Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
            return response()->json(['error' => 'Something went wrong while saving approval flow.'], 500);
        }
    }

    public function saveExpiryDay(Request $request)
    {
        $request->validate([
            'moduleId' => 'required',
            'exp_rej_day' => 'required|numeric|min:1',
        ]);

        try {
            $approvalModule = MasterTable::where(DB::raw('md5(m_id)'), $request->moduleId)->first();
            if (!$approvalModule) {
                return redirect()->back()->with('error', 'Invalid module.');
            }

            ApprovalExpiryRejectDay::updateOrCreate(
                [
                    'aer_b_id' => $this->user->emp_b_id,
                    'aer_m_id' => $approvalModule->m_id,
                ],
                [
                    'aer_day' => $request->exp_rej_day,
                    'aer_noti_day' => $request->noti_day,
                ]
            );
            return redirect()->back()->with('success', 'Expiry Day Saved Successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong.');
        }
    }

    public function approvalFlow(Request $request)
    {
        // Fetch the employee by emp_id (approving_manager1)
        $moduleId = $request->module;
        $approvalFlowTypeId = $request->approval_flow_type;
        
        $unAssignedEmployees = collect();
        $masterDataModule = MasterTable::where(DB::raw('md5(m_id)'), $moduleId)->where('m_group', 'MODULE')->first();
        $user = Auth::user();

         $assignedEmpIds = EmployeeApprovalMapping::where('eam_b_id', $user->emp_b_id)
            ->where('eam_module_id', $masterDataModule)
            ->pluck('eam_emp_id')
            ->toArray();

        // 🔹 get unassigned employees
        $unAssignedEmployees = Employee::where('emp_b_id', $user->emp_b_id)
            ->where('emp_status', 71)
            ->where('emp_role_id', '!=', 1)
            ->whereNotIn('emp_id', $assignedEmpIds)
            ->select('emp_id', 'emp_code', 'emp_full_name')
            ->orderBy('emp_full_name')
            ->get();

        // dd($unAssignedEmployees);

        $masterDataModule = MasterTable::where(DB::raw('md5(m_id)'), $moduleId)->where('m_group', 'MODULE')->first();
        $masterDataApprovalFlowType = MasterTable::where(DB::raw('md5(m_id)'), $approvalFlowTypeId)->first();
        $approvalType = $request->approvalType;
        $status = MasterTable::where('m_group', 'APPROVAL_STATUS')
            ->whereNotIn('m_id', [139, 140, 156, 170, 171, 175, 192])
            ->get();
        if ($request->approvalType == 410) {
            $encryptedId = null;
            $user = Auth::user();
            $moduleList = MasterTable::where('m_group', 'MODULE')->pluck('m_name', 'm_id')->toArray();
            $rules = MasterTable::where('m_group', 'APPROVAL_RULE')->pluck('m_name', 'm_id')->toArray();
            $conditions = MasterTable::where('m_group', 'RULE_CONDITION')->pluck('m_name', 'm_id')->toArray();
            $roles = Role::whereNull('role_b_id')->orWhere('role_b_id', $user->emp_b_id)->pluck('role_name', 'role_id')->toArray();
            $approvalNotify = MasterTable::where('m_group', 'APPROVAL_NOTIFY')->pluck('m_name', 'm_id')->toArray();
            $ruleConditions = MasterTable::where('m_group', 'RULE_CONDITION')->select('m_name', 'm_id', 'm_description', 'm_type')->get();
            $ruleValueConditionOption = MasterTable::where('m_group', 'APPROVAL_STATUS')->select('m_id', 'm_name', 'm_description')->get();
            $exeOn = MasterTable::where('m_group', 'EXECUTION_ON')->pluck('m_name', 'm_id')->toArray();
            $approverMessages = MasterTable::where('m_group', 'APPROVAL_STATUS')->where('m_id', '<>', 140)->pluck('m_name', 'm_id')->toArray();
            $aurUserIds = null;
            $ruleCriteriaData = [];
            $processApproverData = [];
            $moduleData = null;
            $processApproverOptimizedData = collect();


            $allRole = Role::with(['fh_employees' => function ($query) {
                $query->select('emp_id', 'emp_code', 'emp_full_name', 'emp_role_id');
            }])
                ->where(function ($query) use ($user) {
                    $query->whereNull('role_b_id')
                        ->orWhere('role_b_id', $user->emp_b_id);
                })
                ->select('role_name', 'role_id')
                ->get();
            if ($encryptedId) {
                try {
                    $id = Crypt::decryptString($encryptedId);
                    // Now you can use $id as needed
                    $moduleData = ApprovalModule::with('fh_rule_criteria', 'fh_process_approvers', 'fh_action_upon_rejections')->where(['am_b_id' => $user->emp_b_id, 'am_id' => $id])->first();
                    if ($moduleData) {
                        $aurUserIds = isset($moduleData->fh_action_upon_rejections) ?  json_decode($moduleData->fh_action_upon_rejections->aur_group_ids, true) : [];
                        $ruleCriteriaData = $moduleData->fh_rule_criteria ?? [];
                        $processApproverData = $moduleData->fh_process_approvers ?? [];
                        $processApproverOptimizedData = $processApproverData
                            ->groupBy('pa_flow')  // First level of grouping by pa_flow
                            ->map(function ($groupByFlow) {
                                return $groupByFlow->groupBy('pa_d_id')  // Group by pa_d_id within each pa_flow group
                                    ->map(function ($groupByDepartment) {
                                        return $groupByDepartment->groupBy('pa_type');  // Group by pa_type within each pa_d_id group
                                    });
                            });
                    } else {
                        return redirect()->back();
                    }
                } catch (Exception $e) {
                    if ('The payload is invalid.' == $e->getMessage()) {
                        return redirect()->route('travel.approval.list');
                    }
                }
            }

            $departments = Department::where('d_b_id', $user->emp_b_id)->pluck('d_name', 'd_id');
            $moduleId = MasterTable::where(DB::raw('md5(m_id)'), $moduleId)->pluck('m_id')->first();
            return view('admin.setting.approval-settings.approval-settings', compact('moduleList', 'rules', 'conditions', 'roles', 'approvalNotify', 'ruleCriteriaData', 'ruleValueConditionOption', 'ruleConditions', 'moduleData', 'aurUserIds', 'exeOn', 'approverMessages', 'departments', 'processApproverData', 'processApproverOptimizedData', 'approvalType', 'moduleId', 'status','unAssignedEmployees'));
        } else if ($request->approvalType == 411) {
            // $id = 763;
            $existingRows = [];
            // if ($id) {
            //     $existingRows = EmployeeApprovalMapping::with('fh_employee_approver_manager_1', 'fh_employee_approver_manager_2')->where('emp_b_id', $this->user->emp_b_id)->where('eam_module_id', $id)->get();
            // }
            $approvalModules = MasterTable::where('m_group', 'MODULE')->pluck('m_name', 'm_id')->toArray();
            return view('form', compact('approvalModules', 'existingRows', 'moduleId', 'masterDataModule', 'status','unAssignedEmployees'));
        } else {
        }
        return redirect()->route($approvalFlowType->m_other);
    }

    public function show($id)
    {
        $moduleId = null;
        $existingRows = [];
        $masterDataModule = null;
        $unAssignedEmployees = collect(); // 👈 add

        $status = MasterTable::where('m_group', 'APPROVAL_STATUS')
            ->whereNotIn('m_id', [139, 140, 156, 170, 171, 175, 192])
            ->get();

        if ($id) {
            $id = Crypt::decryptString($id);
            $moduleId = md5($id);
            $masterDataModule = MasterTable::find($id);

            $user = Auth::user();

            // 🔹 get assigned employees for this module
            $assignedEmpIds = EmployeeApprovalMapping::where('eam_b_id', $user->emp_b_id)
                ->where('eam_module_id', $id)
                ->pluck('eam_emp_id')
                ->toArray();

            // 🔹 get unassigned employees
            $unAssignedEmployees = Employee::where('emp_b_id', $user->emp_b_id)
                ->where('emp_status', 71)
                ->where('emp_role_id', '!=', 1)
                ->whereNotIn('emp_id', $assignedEmpIds)
                ->select('emp_id', 'emp_code', 'emp_full_name')
                ->orderBy('emp_full_name')
                ->get();
        }

        $approvalModules = MasterTable::where('m_group', 'MODULE')
            ->pluck('m_name', 'm_id')
            ->toArray();

        $appExpRej = ApprovalExpiryRejectDay::where('aer_b_id', $this->user->emp_b_id)
            ->where('aer_m_id', $id)
            ->first();

        return view('form', compact(
            'approvalModules',
            'moduleId',
            'id',
            'masterDataModule',
            'status',
            'appExpRej',
            'unAssignedEmployees'
        ));
    }

    public function getAttendanceCount($employeeId, Request $request)
    {
        // Get logged in user and request data (month and year)
        $user = Auth::user();
        $month = $request->month ?? now()->format('m');  // Default to current month if not provided
        $year = $request->year ?? now()->format('Y');    // Default to current year if not provided

        // Fetch employee details
        $employee = Employee::where('emp_id', $employeeId)->where('emp_b_id', $user->emp_b_id)->first();
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        // Get the number of days in the selected month
        $daysInMonth = Carbon::createFromFormat('Y-m', "{$year}-{$month}")->daysInMonth;

        // Get the week off dates for the employee in the selected month
        $weekOfDates = CentralLogics::getWeekOffDates($employee, $year, $month);


        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $dateRange = $startDate->toPeriod($endDate);

        $holiday_record_exits = PolicyHolidayList::where('phl_b_id', $user->emp_b_id)->where('phl_day_type_id', 201)->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('phl_start_date', [$startDate, $endDate])
                ->orWhereBetween('phl_end_date', [$startDate, $endDate]);
        })->get();
        $holidaysByDate = collect();
        foreach ($holiday_record_exits as $holiday) {
            $start = Carbon::parse($holiday->phl_start_date);
            $end = Carbon::parse($holiday->phl_end_date);

            while ($start->lte($end)) {
                $holidaysByDate->put($start->toDateString(), $holiday);
                $start->addDay();
            }
        }


        $attendanceData = [];

        // Loop through each day of the month to get attendance details
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::createFromFormat('Y-m-d', "{$year}-{$month}-{$day}");
            // Get daily attendance details (this method will need to return the counts for each attendance status)
            $dailyDetails = CentralLogics::newGetMonthlyAttendanceDetails($employee, $month, $year, $holidaysByDate, $weekOfDates);
            $dailyDetails['date'] = $date->format('Y-m-d'); // Add date to the details
            $attendanceData[] = $dailyDetails;
        }

        // Calculate the total attendance count for the month
        $attendanceCount = [
            'presentCount' => array_sum(array_column($attendanceData, 'presentCount')),
            'halfDayCount' => array_sum(array_column($attendanceData, 'halfDayCount')),
            'leaveCount' => array_sum(array_column($attendanceData, 'leaveCount')),
            'absentCount' => array_sum(array_column($attendanceData, 'absentCount')),
            'weekOffCount' => array_sum(array_column($attendanceData, 'weekOffCount')),
        ];

        // Return the attendance data and count
        return response()->json(['attendanceCount' => $attendanceCount, 'attendanceData' => $attendanceData]);
    }

}
