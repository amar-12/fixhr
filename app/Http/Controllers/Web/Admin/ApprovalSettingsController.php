<?php

namespace App\Http\Controllers\Web\Admin;

use App\Helpers\RolePermissionLogics;
use App\Http\Controllers\Controller;
use App\Http\Resources\AjaxMasterTableResource;
use App\Models\ApprovalModule;
use App\Models\Employee;
use App\Models\MasterTable;
use App\Models\RuleCriterion;
use Exception;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\ProcessApprover;
use App\Models\ActionUponRejection;
use App\Models\AdvanceLog;
use App\Models\AttendanceException;
use App\Models\AttendanceRecord;
use App\Models\Business;
use App\Models\Department;
use App\Models\EmployeeApprovalMapping;
use App\Models\GatePass;
use App\Models\LeaveRequest;
use App\Models\TadaClaim;
use App\Models\TadaRequestPlan;
use App\Services\ModuleResolver;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\OvertimePolicy;

class ApprovalSettingsController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }
    public function index1(Request $request)
    {
        $approvalFlowType = MasterTable::where('m_group', 'APPROVAL_FLOW_TYPE')->get();
        if ($request->ajax()) {

            $user = Auth::user();
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['am_b_id', $user->emp_b_id]
                ],
                [
                    'method' => 'select',
                    'args' => ['am_id', 'am_name', 'am_exe_on', 'am_status', 'am_description', 'am_module_id', 'updated_at'],
                    'relation' => ['fh_modules:m_id,m_name', 'fh_rule_criteria:rc_id,rc_am_id'],
                ],
                [
                    'method' => 'sortBy',
                    'args' => ['am_id', 'am_name', 'am_description', 'am_module_id', 'am_exe_on', 'am_id', 'updated_at', 'am_status', 'am_id']
                ]
            ];

            // Define search value, columns, and relationships
            $searchColumns = ['am_id', 'am_name', 'am_exe_on', 'am_status', 'am_description', 'am_module_id', 'updated_at'];
            $searchRelationships = [
                'fh_modules' => ['m_name']
            ];

            $list = (new DynamicModelDataTableHelper(eloquentModel: new ApprovalModule(), dynamicConditions: $dynamicConditions, searchColumns: $searchColumns, searchRelationships: $searchRelationships))->getServerSideDataTable();

            $rowData = array();
            $i = 0;

            foreach ($list as $key => $val) {
                $i++;
                $row = array();
                $executionOn = MasterTable::whereIn('m_id', json_decode($val->am_exe_on))->pluck('m_name')->toArray();
                $row[] = $i;
                $row[] = '<a  href="' . route('approval.process.details', Crypt::encryptString($val->am_id)) . '" class="text-primary">' . $val->am_name . '</a>';
                $row[] = $val->am_description;
                $row[] = $val->fh_modules->m_name;
                $row[] = implode(' & ', $executionOn);
                $row[] = count($val->fh_rule_criteria);
                $row[] = '<span class="fs-11 fw-bold">W.E.F. </span><span class="with-effect-from-badge fs-10">' . date('d-M-Y h:i A', strtotime($val->updated_at)) . '</span>';
                $row[] = '<label class="custom-switch ms-auto"><input type="checkbox" class="custom-switch-input" id="toggle-' . $val->am_id . '" data-id="' . $val->am_id . '" name="" value="' . ($val->am_status == 1 ? $val->am_status : '') . '"' . ($val->am_status == 1 ? 'checked' : '') . '><span class="custom-switch-indicator"></span></label>';
                $id = $val->am_id;
                $encryptedId = Crypt::encryptString($id);
                $row[] = '<a class="btn action-btns btn-sm btn-primary" href="' . url('admin/settings/tada-settings/approval-setting', ['id' => $encryptedId]) . '">
                    <i class="feather feather-edit"></i>
                </a>';
                $rowData[] = $row;
            }

            $output = array(
                "draw" => request()->input('draw'),
                "recordsTotal" => sizeof($list),
                "recordsFiltered" => (new DynamicModelDataTableHelper(eloquentModel: new ApprovalModule(), dynamicConditions: $dynamicConditions, searchColumns: $searchColumns, searchRelationships: $searchRelationships))->countFilteredServerSideDataTable(),
                "data" => $rowData,
            );

            return json_encode($output);
        }
        $columns = [
            'S. No.',
            'PROCESS NAME',
            'PROCESS DESCRIPTION',
            'MODULE',
            'EXECUTE ON',
            'RULES',
            'DATE',
            'STATUS',
            'ACTION'
        ];

        return view('admin.setting.approval-settings.approval-list', compact('columns', 'approvalFlowType'));
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $approvalFlowType = MasterTable::where('m_group', 'APPROVAL_FLOW_TYPE')->get();

        $fnfData = Business::where('b_id', $user->emp_b_id)->first();
        $bFnfModules = [];

        if (!empty($fnfData->b_fnf_modules)) {
            $bFnfModules = is_array($fnfData->b_fnf_modules)
                ? $fnfData->b_fnf_modules
                : json_decode($fnfData->b_fnf_modules, true);
        }



        if ($request->ajax()) {

            $staticIds = ["5888", "5889", "5890", "5891"];
            $bFnfModules = array_map('intval', $bFnfModules);
            $allowedStaticIds = array_values(array_intersect($staticIds, $bFnfModules));
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['m_group', 'MODULE']
                ],

                [
                    'method' => 'whereNotIn',
                    'args' => ['m_id', $staticIds]
                ],

                [
                    'method' => 'orWhereIn',
                    'args' => ['m_id', $allowedStaticIds]
                ],
                [
                    'method' => 'select',
                    'args' => [
                        'm_id',
                        'm_group',
                        'm_name',
                        'm_type',
                        'm_description',
                        'm_other'
                    ],
                    'relation' => [
                        'fh_approval_modules:*',
                        'fh_approval_modules2:*'
                    ]
                ],
                [
                    'method' => 'orderBy',
                    'args' => ['m_id', 'asc']
                ]
            ];

            $searchColumns = ['m_id', 'm_group', 'm_name', 'm_type', 'm_description', 'm_other'];
            $searchRelationships = ['fh_approval_modules' => ['am_name']];

            $dynamicHelper = new DynamicModelDataTableHelper(
                eloquentModel: new MasterTable(),
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
                searchRelationships: $searchRelationships
            );

            $list = $dynamicHelper->getServerSideDataTable();

            // Total employees
            $totalEmployee = Employee::where('emp_b_id', $user->emp_b_id)
                ->where('emp_role_id', '<>', 1)
                ->where('emp_status', 71)
                ->get();

            $rowData = [];
            $i = 0;

            foreach ($list as $val) {
                $i++;
                $row = [];
                $row[] = $i;

                // Module Name link
                $row[] = '<a href="' . route('approval.process.details', Crypt::encryptString($val->m_id)) . '" class="text-primary">'
                    . $val->m_name . '</a>';

                // Approval Type
                if ($val->fh_approval_modules()->where('am_b_id', $user->emp_b_id)->first()) {
                    $row[] = 'Hierarchy Wise';
                } elseif ($val->fh_approval_modules2()->where('eam_b_id', $user->emp_b_id)->first()) {
                    $row[] = 'Employee Wise';
                } else {
                    $row[] = '---';
                }

                // Employee Ratio Column
                // if ($val->fh_approval_modules2()->where('eam_b_id', $user->emp_b_id)->first()) {
                //     $eamModuleId = $val->fh_approval_modules2()
                //         ->where('eam_b_id', $user->emp_b_id)
                //         ->pluck('eam_module_id')
                //         ->first();

                //     $approvalFlowEmpCount = EmployeeApprovalMapping::where('eam_b_id', $user->emp_b_id)
                //         ->where('eam_module_id', $eamModuleId)
                //         ->whereHas('fh_employee', function ($query) {
                //             $query->where('emp_status', 71)
                //                 ->where('emp_role_id', '!=', 1);
                //         })
                //         ->count();

                //     $totalEmpCount = $totalEmployee->count();

                //     $row[] = $approvalFlowEmpCount . ' / ' . $totalEmpCount;
                // } else {
                //     $row[] = '---';
                // }


                // Assigned & UnAssigned Columns
                if ($val->fh_approval_modules2()->where('eam_b_id', $user->emp_b_id)->first()) {
                    $eamModuleId = $val->fh_approval_modules2()
                        ->where('eam_b_id', $user->emp_b_id)
                        ->pluck('eam_module_id')
                        ->first();

                    $assignedCount = EmployeeApprovalMapping::where('eam_b_id', $user->emp_b_id)
                        ->where('eam_module_id', $eamModuleId)
                        ->whereHas('fh_employee', function ($query) {
                            $query->where('emp_status', 71)
                                ->where('emp_role_id', '!=', 1);
                        })
                        ->count();

                    $totalEmpCount = $totalEmployee->count();
                    $unAssignedCount = $totalEmpCount - $assignedCount;

                    $row[] = $assignedCount . ' / ' . $totalEmpCount;   // Assigned

                    if ($unAssignedCount > 0) {
                        $row[] = '<a href="javascript:void(0)"
                                            class="text-danger fw-semibold view-unassigned-employees"
                                            data-module-id="' . $eamModuleId . '"
                                            data-module-name="' . e($val->m_name) . '"
                                            data-unassigned="' . $unAssignedCount . '">
                                            ' . $unAssignedCount . '
                                        </a>';
                    } else {
                        $row[] = '<span class="text-muted">0</span>';
                    }
                } else {
                    $row[] = '---'; // Assigned
                    $row[] = '---'; // UnAssigned
                }



                // 3-dot dropdown
                $actionDropdown = '<div class="dropdown">
                    <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="actionDropdownBtn-' . $val->m_id . '">
                        <i class="fa fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu p-2" style="min-width: 180px;">';

                if ($val->fh_approval_modules()->where('am_b_id', $user->emp_b_id)->first()) {
                    $encryptedId = Crypt::encryptString(
                        $val->fh_approval_modules()->where('am_b_id', $user->emp_b_id)->pluck('am_id')->first()
                    );
                    $actionDropdown .= '<li>
                        <a class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2"
                        href="' . url('admin/settings/tada-settings/approval-setting', ['id' => $encryptedId]) . '">
                        <i class="fa fa-edit"></i> Edit
                        </a>
                    </li>';
                    $actionDropdown .= '<li>
                        <button class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2"
                        id="change-approval-flow-btn" data-module="' . $val->m_id . '">
                        <i class="fa fa-refresh"></i> Reset
                        </button>
                    </li>';
                } elseif ($val->fh_approval_modules2()->where('eam_b_id', $user->emp_b_id)->first()) {
                    $encryptedId = Crypt::encryptString($val->m_id);
                    $actionDropdown .= '<li>
                        <a class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2"
                        href="' . route('form.show', ['id' => $encryptedId]) . '">
                        <i class="fa fa-edit"></i> Edit
                        </a>
                    </li>';
                    $actionDropdown .= '<li>
                        <button class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2"
                        id="change-approval-flow-btn" data-module="' . $val->m_id . '">
                        <i class="fa fa-refresh"></i> Reset
                        </button>
                    </li>';
                } else {
                    $actionDropdown .= '<li>
                        <button class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2 create-approval-flow-btn"
                                data-module="' . md5($val->m_id) . '">
                            <i class="fa fa-plus"></i> Create
                        </button>
                    </li>';
                }

                $actionDropdown .= '</ul></div>';
                $row[] = $actionDropdown;

                // Email Template Column (moved to last)
                $row[] = '<a class="btn btn-sm btn-info text-white" href="' . url('/privilege/email-templates?module_id=' . $val->m_id) . '" title="Email Templates">
                    <i class="fa fa-envelope"></i>
                </a>';

                $rowData[] = $row;
            }

            $output = [
                "draw" => request()->input('draw'),
                "recordsTotal" => sizeof($list),
                "recordsFiltered" => $dynamicHelper->countFilteredServerSideDataTable(),
                "data" => $rowData,
            ];

            return response()->json($output);
        }

        // Columns including the new Employee Ratio column      
        $columns = ['S. No.', 'Module Name', 'Approval Type', 'Assigned', 'UnAssigned', 'Action', 'Email Template'];
        $modules = MasterTable::where('m_group', 'MODULE')->pluck('m_name', 'm_id');

        return view('admin.setting.approval-settings.approval-list', compact('columns', 'approvalFlowType', 'modules'));
    }

    public function checkPendingApprovalRequests(Request $request)
    {
        $user = Auth::user();
        $moduleId = $request->input('module_id');
        $resolver = new ModuleResolver();

        $pending = $resolver->getPendingRequests($moduleId, $user->emp_b_id);

        if ($pending === null) {
            return response()->json(['status' => false, 'error' => 'Invalid module']);
        }

        return response()->json([
            'count' => $pending->count(),
            'data'  => $pending,
            'status' => true,
        ]);
    }

    public function resetApprovalFlow(Request $request)
    {
        $user = Auth::user();
        $moduleId = $request->input('module_id');

        // Start the transaction
        DB::beginTransaction();

        try {
            // For Hirarchy Wise Flow
            $approvalModuleRec = ApprovalModule::where('am_module_id', $moduleId)->where('am_b_id', $user->emp_b_id)->first();
            if ($approvalModuleRec) {
                $approvalModuleRec->delete();
            } else {
                // For Employee Wise Flow
                $empApprovalMapping = EmployeeApprovalMapping::where('eam_module_id', $moduleId)->where('eam_b_id', $user->emp_b_id)->get();
                foreach ($empApprovalMapping as $mapping) {
                    $mapping->delete();
                }
            }

            // Commit the transaction
            DB::commit();
        } catch (\Exception $e) {

            // Rollback the transaction on error
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage(),
                'status' => false,
            ], 500);
        }
    }

    public function approvalMappingSampleDownload()
    {
        $fileName = 'approval_mapping_upload_format.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\ApprovalMappingSampleExport, $fileName);
    }

    public function approvalMappingImport(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,csv',
            'module_id' => 'required|integer|exists:master_table,m_id',
        ]);

        // Clear any stale error flags from previous uploads
        session()->forget(['import_errors', 'import_errors_blade']);

        $file = $request->file('import_file');
        $import = new \App\Imports\EmployeeApprovalMappingImport(\Auth::user(), (int) $request->module_id);

        try {
            \Maatwebsite\Excel\Facades\Excel::import($import, $file);
            $errors = $import->getErrorMessages();
            $summary = $import->getSummary();
            // Ensure stale error flags are not shown on success-only runs
            if (empty($errors)) {
                session()->forget(['import_errors', 'import_errors_blade']);
            }

            if (!empty($errors)) {
                session()->put('import_errors', $errors);
                session()->flash('import_errors_blade', $errors);
                return redirect()->back()->with('error', 'Import completed with errors. Imported: ' . $summary['success'] . ', Failed: ' . $summary['failed']);
            }

            return redirect()->back()->with('success', 'Import completed successfully! Imported: ' . $summary['success']);
        } catch (\Exception $e) {
            // On exception, make sure we surface a clear error and do not leave partial success flashes
            session()->forget(['import_errors_blade']);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }



    public function travelApprovalSetting($encryptedId = null)
    {
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
        return view('admin.setting.approval-settings.approval-settings', compact('moduleList', 'rules', 'conditions', 'roles', 'approvalNotify', 'ruleCriteriaData', 'ruleValueConditionOption', 'ruleConditions', 'moduleData', 'aurUserIds', 'exeOn', 'approverMessages', 'departments', 'processApproverData', 'processApproverOptimizedData'));
    }

    public function saveAllMappings(Request $request)
    {
        $user = Auth::user();
        $response = [
            'status_code' => 0,
            'status_text' => '',
            'result' => null
        ];
        $postData = $request->all();
        if ($request->POST_TYPE == 'CHECK_PENDING_APPROVAL') {
            $module = $request->approval_type;

            // Check if the module is valid
            if ($module && in_array($module, [145, 146, 229, 249, 250, 339, 199, 562])) {
                $approvalData = ApprovalModule::where('am_b_id', $user->emp_b_id)
                    ->where('am_id', $request->module_id)
                    ->first();

                if ($approvalData && $request->approverDataUpdatedVersion) {
                    $approverFlow = $request['approverDataUpdatedVersion']['approvalFlow'];

                    foreach ($request['approverDataUpdatedVersion']['approvalData'] as $approverKey => $approverItem) {
                        $departmentValue = $approverItem['departmentSelectValue'] ?? null;

                        // Check for pending requests
                        if ($this->checkPendingRequest($module, $user, $approverKey)) {
                            $approvalDataArray = ($approverKey == 'business')
                                ? $approvalData->fh_process_approvers->toArray()
                                : $approvalData->fh_process_approvers->where('pa_d_id', $approverKey)->values()->toArray();

                            $requestDataArray = $approverItem['approvalRows'];
                            $requestApproverType = $approverItem['approvalOrder'];

                            // Compare arrays and detect differences
                            $differences = $this->validateApprovalData($approvalDataArray, $requestDataArray, $requestApproverType);

                            if (!empty($differences)) {
                                $response = $this->generateResponse(true, $differences);
                                break;
                            } else {
                                $response['status_code'] = 0; // No differences found
                            }
                        }
                    }
                }
            } else {
                $response['status_code'] = 0; // Invalid module
            }
        } else if ($request->POST_TYPE == 'CHECK_DUPLICATE') {
            if ($postData['moduleData']['am_id'] != null) { // update
                $response['status_code'] = 1;
            } else { // Create
                foreach ($postData['ruleCriteriaData']['dynamic'] as $rule) {

                    if ($rule['rc_approval_rule_id'] == 131) { //APPROVAL_RULE=>131=='Request Status'
                        $ruleExist =   RuleCriterion::where(['rc_b_id' => $user->emp_b_id, 'rc_condition_option_id' =>  $rule['rule_value']])->whereHas('fh_approval_module', function ($query) use ($postData) {
                            $query->where('am_module_id', $postData['moduleData']['moduleId']);
                        })->first();
                        if ($ruleExist) {
                            $response['status_code'] = 0;
                            $response['status_text'] = 'Module already exists. Please navigate to the TA & DA approval List to update the module.';
                        } else {
                            $response['status_code'] = 1;
                        }
                    }
                }
            }
        } else {
            DB::beginTransaction();
            try {
                $executionOn =  json_encode(array_map('intval', $postData['moduleData']['executionOn']));
                if ($postData['moduleData']['am_id'] != null) { // update
                    $moduleData = ApprovalModule::where([
                        'am_b_id' => $user->emp_b_id,
                        'am_id' => $postData['moduleData']['am_id'],
                    ])->first();
                    if ($moduleData) {
                        $moduleData->update([
                            'am_module_id' => $postData['moduleData']['moduleId'],
                            'am_name' => $postData['moduleData']['moduleName'],
                            'am_exp_rej_day' => $postData['moduleData']['exp_rej_day'],
                            'am_noti_before_days' => $postData['moduleData']['noti_before_days'],
                            'am_description' => $postData['moduleData']['moduleDescription'],
                            'am_exe_on' => $executionOn,
                        ]);
                    }
                    $response['status_code'] = 1;
                    $response['status_text'] = 'Module Updated Successfully';
                    $response['result'] = ['approvalModuleId' => $request->am_id];
                } else { // Create
                    $moduleData = ApprovalModule::create([
                        'am_b_id' => $user->emp_b_id,
                        'am_module_id' => $postData['moduleData']['moduleId'],
                        'am_name' => $postData['moduleData']['moduleName'],
                        'am_exp_rej_day' => $postData['moduleData']['exp_rej_day'],
                        'am_noti_before_days' => $postData['moduleData']['noti_before_days'],
                        'am_description' => $postData['moduleData']['moduleDescription'],
                        'am_exe_on' => $executionOn,
                    ]);
                    $response['status_code'] = 1;
                }

                // Handle RULE_CRITERIA
                if (isset($postData['ruleCriteriaRemovedRowIds'])) {
                    if (count($postData['ruleCriteriaRemovedRowIds']) > 0) {
                        RuleCriterion::whereIn('rc_id', $postData['ruleCriteriaRemovedRowIds'])
                            ->where('rc_b_id', $user->emp_b_id)
                            ->delete();
                    }
                }
                foreach ($postData['ruleCriteriaData']['dynamic'] as $rule) {
                    RuleCriterion::updateOrCreate(
                        ['rc_id' => $rule['rc_id'] ?? null, 'rc_b_id' => $user->emp_b_id],
                        [
                            'rc_am_id' => $moduleData->am_id,
                            'rc_approval_rule_id' => $rule['rc_approval_rule_id'],
                            'rc_rule_condition_id' => $rule['rc_rule_condition_id'],
                            ($rule['rule_value_type'] == 'custom' ? 'rc_custom_value' : 'rc_condition_option_id') => $rule['rule_value']
                        ]
                    );
                }

                if ($request->approverDataUpdatedVersion) {
                    $approverFlow = $request['approverDataUpdatedVersion']['approvalFlow'];
                    foreach ($request['approverDataUpdatedVersion']['approvalData'] as $approverKey => $approverItem) {
                        $approvalOrder = $approverItem['approvalOrder'];
                        $departmentValue = isset($approverItem['departmentSelectValue']) ? $approverItem['departmentSelectValue'] : null;
                        foreach ($approverItem['approvalRows'] as $rowKey => $rowValue) {
                            $checkLastApproval = $approvalOrder == 'and' && count($approverItem['approvalRows']) == ($rowKey + 1) ? 1 : 0;
                            ProcessApprover::updateOrCreate(
                                [
                                    'pa_b_id' => $user->emp_b_id,
                                    'pa_id' => isset($rowValue['primaryKey']) ? $rowValue['primaryKey'] : null,
                                ],
                                [
                                    'pa_role_id' => $rowValue['role'],
                                    'pa_emp_id' => $rowValue['employee'],
                                    'pa_am_id' => $moduleData->am_id,
                                    'pa_type' => $approvalOrder,
                                    'pa_sequence' => $rowKey + 1,
                                    'pa_status_id' => $rowValue['nextstatus'],
                                    'pa_message' => $rowValue['approvermessage'],
                                    'pa_d_id' => $departmentValue,
                                    'pa_flow' => $approverFlow,
                                    'pa_last' => $checkLastApproval,
                                ]
                            );
                        }
                    }
                    $deleteId = isset($request['approverDataUpdatedVersion']['deletedApproverIds']) ? $request['approverDataUpdatedVersion']['deletedApproverIds'] : [];
                    ProcessApprover::whereIn('pa_id', $deleteId)->delete();
                }

                // Handle Rejection Notify
                $numberArray = array_map('intval', $request->aur_group_ids);
                $processApproverData = ProcessApprover::where('pa_b_id', $user->emp_b_id)
                    ->where('pa_am_id', $moduleData->am_id)
                    ->get();
                ActionUponRejection::where('aur_b_id', $user->emp_b_id)
                    ->where('aur_am_id', $moduleData->am_id)
                    ->delete();
                foreach ($processApproverData as $approverData) {
                    ActionUponRejection::create([
                        'aur_b_id' => $user->emp_b_id,
                        'aur_am_id' => $approverData->pa_am_id,
                        'aur_emp_id' => $approverData->pa_emp_id,
                        'aur_group_ids' => json_encode($numberArray),
                    ]);
                }

                DB::commit();
                if ($request->POST_TYPE == 'FINAL_SUBMIT') {
                    $response['status_code'] = 1;
                    if ($postData['moduleData']['am_id'] != null) {
                        if ($postData['moduleData']['moduleId'] == 145) {
                            $response['status_text'] = 'Travel Approval Updated Successfully';
                        }
                        if ($postData['moduleData']['moduleId'] == 146) {
                            $response['status_text'] = 'Claim Approval Updated Successfully';
                        }
                        if ($postData['moduleData']['moduleId'] == 199) {
                            $response['status_text'] = 'Advance Approval Updated Successfully';
                        }
                        if ($postData['moduleData']['moduleId'] == 229) {
                            $response['status_text'] = 'Mispunch Approval Updated Successfully';
                        }
                        if ($postData['moduleData']['moduleId'] == 249) {
                            $response['status_text'] = 'Attendance Approval Updated Successfully';
                        }
                        if ($postData['moduleData']['moduleId'] == 250) {
                            $response['status_text'] = 'Leave Approval Updated Successfully';
                        }
                        if ($postData['moduleData']['moduleId'] == 339) {
                            $response['status_text'] = 'Gatepass Approval Updated Successfully';
                        }
                        if ($postData['moduleData']['moduleId'] == 562) {
                            $response['status_text'] = 'Overtime Approval Updated Successfully';
                        }
                    } else {
                        if ($postData['moduleData']['moduleId'] == 145) {
                            $response['status_text'] = 'Travel Approval Saved Successfully';
                        }
                        if ($postData['moduleData']['moduleId'] == 146) {
                            $response['status_text'] = 'Claim Approval Saved Successfully';
                        }
                        if ($postData['moduleData']['moduleId'] == 199) {
                            $response['status_text'] = 'Advance Approval Saved Successfully';
                        }
                        if ($postData['moduleData']['moduleId'] == 229) {
                            $response['status_text'] = 'Mispunch Approval Saved Successfully';
                        }
                        if ($postData['moduleData']['moduleId'] == 249) {
                            $response['status_text'] = 'Attendance Approval Saved Successfully';
                        }
                        if ($postData['moduleData']['moduleId'] == 250) {
                            $response['status_text'] = 'Leave Approval Saved Successfully';
                        }
                        if ($postData['moduleData']['moduleId'] == 339) {
                            $response['status_text'] = 'Gatepass Approval Saved Successfully';
                        }
                        if ($postData['moduleData']['moduleId'] == 562) {
                            $response['status_text'] = 'Overtime Approval Saved Successfully';
                        }
                    }
                }
            } catch (\Exception $e) {
                DB::rollBack();
                $response['status_code'] = 0;
                $response['status_text'] = 'An error occurred: ' . $e->getMessage();
            }
        }
        return response()->json($response);
    }

    // Helper function to validate approval data
    private function validateApprovalData($approvalDataArray, $requestDataArray, $requestApproverType)
    {
        $differences = [];
        if (count($approvalDataArray) !== count($requestDataArray)) {
            $differences[] = "Array count mismatch: Model count " . count($approvalDataArray) . ", Request count " . count($requestDataArray);
            return $differences; // Early exit if counts don't match
        }

        foreach ($approvalDataArray as $index => $approver) {
            if (isset($requestDataArray[$index])) {
                $requestApprover = $requestDataArray[$index];

                if ($approver['pa_type'] !== $requestApproverType) {
                    $differences[] = "Approval type mismatch at index $index: Model 'pa_type' is '{$approver['pa_type']}', Request 'approval_type' is '{$requestApproverType}'";
                }

                if ($approver['pa_role_id'] !== (int)$requestApprover['role']) {
                    $differences[] = "Role ID mismatch at index $index: Model 'pa_role_id' is '{$approver['pa_role_id']}', Request 'role_id' is '{$requestApprover['role']}'";
                }
            }
        }
        return $differences;
    }

    // Helper function to check for pending requests based on module type
    private function checkPendingRequest($module, $user, $approverKey)
    {
        // Define an array to map module types to their corresponding models and conditions
        $modules = [
            145 => ['model' => TadaRequestPlan::class, 'business_column' => 'trp_b_id', 'stage_column' => 'trp_stage_completed'],
            146 => ['model' => TadaClaim::class, 'business_column' => 'tc_b_id', 'stage_column' => 'tc_stage_completed'],
            229 => ['model' => AttendanceException::class, 'business_column' => 'ae_b_id', 'stage_column' => 'ae_stage_completed'],
            249 => ['model' => AttendanceRecord::class, 'business_column' => 'atd_b_id', 'stage_column' => 'atd_stage_completed'],
            250 => ['model' => LeaveRequest::class, 'business_column' => 'lvr_b_id', 'stage_column' => 'lvr_stage_completed'],
            339 => ['model' => GatePass::class, 'business_column' => 'gtp_b_id', 'stage_column' => 'gtp_stage_completed'],
            199 => ['model' => AdvanceLog::class, 'business_column' => 'gtp_b_id', 'stage_column' => 'adl_stage_completed'],
            562 => ['model' => OvertimePolicy::class, 'business_column' => 'ot_b_id', 'stage_column' => 'ot_stage_completed'],
        ];

        // Check if the module exists in the mapping array
        if (isset($modules[$module])) {
            // Get the corresponding model and columns for the module
            $moduleConfig = $modules[$module];

            // Build the query dynamically based on the module
            if ($module == 199) {
                $query = $moduleConfig['model']::with('fh_tada_request_plan')->whereHas('fh_tada_request_plan', function ($q) use ($user) {
                    $q->where('trp_b_id', $user->emp_b_id);
                });
            } else {
                $query = $moduleConfig['model']::where($moduleConfig['business_column'], '=', $user->emp_b_id);
            }

            // Apply the approver key condition if necessary
            if ($approverKey && $approverKey != "business") {
                $query->whereHas('fh_employee', function ($query) use ($approverKey) {
                    $query->where('emp_d_id', $approverKey); // Check if emp_d_id matches the approverKey
                });
            }

            // Check if the stage is incomplete and return the result
            return $query->where($moduleConfig['stage_column'], 0)
                ->exists();
        }

        // Return false if the module is not found in the mapping
        return false;
    }

    // Helper function to generate response
    private function generateResponse($pendingRequestCheck)
    {
        if ($pendingRequestCheck) {
            return [
                'status_code' => 1,
                'status_text' => 'First, clear the pending requests, and then change the Approval Order.',
            ];
        }
        return ['status_code' => 0];
    }


    public function getApprovalSetting(Request $request)
    {
        $data = [];
        $user = Auth::user();
        if ($request->REQUEST_TYPE == 'RULE_CONDITION') {
            $data = AjaxMasterTableResource::collection(
                MasterTable::where('m_group', 'RULE_CONDITION')
                    ->whereJsonContains('m_description', (int) $request->ruleId)
                    ->select('m_id', 'm_name', 'm_type')
                    ->get()
            );
        }

        if ($request->REQUEST_TYPE == 'APPROVAL_STATUS') {
            $data = MasterTable::where('m_group', 'APPROVAL_STATUS')
                ->whereJsonContains('m_description', (int) $request->rule_condition_id)
                ->select('m_id', 'm_name')
                ->get();
        }

        if ($request->REQUEST_TYPE == 'GET_EMP_BY_ROLEID') {
            $data = Employee::where([
                'emp_b_id' => $user->emp_b_id,
                'emp_role_id' => $request->roleId
            ]);

            if (($request->dId != "null") && $request->roleId != 1) {
                $data->where('emp_d_id', $request->dId);
            }

            $data = $data->select('emp_id', 'emp_fname', 'emp_mname', 'emp_lname', 'emp_full_name')
                ->get();
        }

        if ($request->REQUEST_TYPE == 'GET_ROLE_BY_DEPARTMENT') {
            $data = Role::with(['fh_employees' => function ($query) use ($request) {
                // Filter employees based on department ID (emp_d_id)
                $query->where('emp_d_id', $request->departmentId)->select('emp_role_id', 'emp_id', 'emp_code');
            }])
                ->whereNull('role_b_id')
                ->orWhere('role_b_id', $user->emp_b_id) // Filter roles based on the business ID
                ->select('role_id', 'role_name') // Select only necessary columns (e.g., role ID and name)
                ->get();
        }

        // Check if data is available and return a proper response
        if (count($data) > 0) {
            return response()->json(['status_code' => 1, 'result' => $data]);
        } else {
            return response()->json(['status_code' => 0, 'result' => null]);
        }
    }


    public function approvalUpdateToggleBox(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:approval_modules,am_id',
            'status' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 400);
        }
        $id = (int) $request->input('id');
        $status =  $request->input('status');

        // Find the item and update the status
        // Retrieve the item with its relationships
        $item = ApprovalModule::with(['fh_rule_criteria', 'fh_process_approvers', 'fh_action_upon_rejections'])
            ->where('am_id', $id)
            ->first();
        // Check if all relationships are present
        if ($item && $item->fh_rule_criteria->isNotEmpty() && $item->fh_process_approvers->isNotEmpty() && $item->fh_action_upon_rejections) {
            // Perform the update
            $item->am_status = $status;
            $item->save();
            return response()->json(['success' => true, 'message' => 'Status has been updated successfully']);
        } else {
            return response()->json(['error' => false, 'message' => 'Status not updated due to missing the ta & da approval setup related data complete the ta & da approval setup']);
        }
    }

    public function approvalProcessDetails($id)
    {
        try {
            $id = Crypt::decryptString($id);
            $processModuleDetails = null;
            $employeeApprovalMapping = [];
            $rejectionReceivers = null;
            $processApproverOptimizedData = [];
            $processModuleDetails = ApprovalModule::with([
                'fh_rule_criteria' => ['fh_approval_rule:m_id,m_name', 'fh_rule_condition:m_id,m_name', 'fh_condition_option:m_id,m_name'],
                'fh_process_approvers' => [
                    'fh_approver_status:m_id,m_name',
                    'fh_employee:emp_id,emp_fname,emp_full_name,emp_email,emp_code',
                    'fh_role:role_id,role_name',
                ],
                'fh_process_approvers.fh_department',
                'fh_action_upon_rejections'
            ])->where('am_module_id', $id)->where('am_b_id', $this->user->emp_b_id)->first();
            $masterName = MasterTable::find($id);
            if ($processModuleDetails) {

                $processApproverData = $processModuleDetails->fh_process_approvers;
                $processApproverOptimizedData = $processApproverData
                    ->groupBy('pa_flow')  // First level of grouping by pa_flow
                    ->map(function ($groupByFlow) {
                        return $groupByFlow->groupBy('pa_d_id')  // Group by pa_d_id within each pa_flow group
                            ->map(function ($groupByDepartment) {
                                return $groupByDepartment->groupBy('pa_type');  // Group by pa_type within each pa_d_id group
                            });
                    });

                $processApproverOptimizedData = $processApproverData
                    ->groupBy('pa_flow')  // First level of grouping by pa_flow
                    ->map(function ($groupByFlow) {
                        return $groupByFlow->groupBy('fh_department.d_name')  // Group by pa_d_id within each pa_flow group
                            ->map(function ($groupByDepartment) {
                                return $groupByDepartment->groupBy('pa_type');  // Group by pa_type within each pa_d_id group
                            });
                    });

                $rejectionReceivers = '';
                if ($processModuleDetails->fh_action_upon_rejections->aur_group_ids) {
                    $rejectionReceiverGroupIds = array_map('intval', json_decode($processModuleDetails->fh_action_upon_rejections->aur_group_ids));
                    $rejectionReceivers = MasterTable::whereIn('m_id', $rejectionReceiverGroupIds)->pluck('m_name')->toArray();
                    $rejectionReceivers = implode(',', $rejectionReceivers);
                }
            } else {
                $employeeApprovalMapping = EmployeeApprovalMapping::with('fh_master_table', 'fh_employee_approver_manager_1', 'fh_employee_approver_manager_2')->where('eam_b_id', $this->user->emp_b_id)->where('eam_module_id', $id)->get();
            }
            return view('admin.setting.approval-settings.approval-details', compact('processModuleDetails', 'rejectionReceivers', 'processApproverOptimizedData', 'employeeApprovalMapping', 'employeeApprovalMapping', 'masterName'));
        } catch (Exception $e) {
            if ('The payload is invalid.' == $e->getMessage()) {
                return redirect()->route('travel.approval.list');
            }
        }
    }


    public function getUnAssignedEmployees(Request $request)
    {
        $user = Auth::user();

        $assignedEmpIds = EmployeeApprovalMapping::where('eam_b_id', $user->emp_b_id)
            ->where('eam_module_id', $request->module_id)
            ->pluck('eam_emp_id')
            ->toArray();

        $employees = Employee::where('emp_b_id', $user->emp_b_id)
            ->where('emp_status', 71)
            ->where('emp_role_id', '!=', 1)
            ->whereNotIn('emp_id', $assignedEmpIds)
            ->select('emp_id', 'emp_code', 'emp_full_name')
            ->orderBy('emp_full_name')
            ->get();

        return response()->json($employees);
    }
}
