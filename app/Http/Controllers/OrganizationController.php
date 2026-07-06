<?php

namespace App\Http\Controllers;

use App\Helpers\CentralLogics;
use App\Models\Business;
use App\Models\BusinessModuleAccess;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Menu;
use App\Models\MenuModule;
use App\Models\Module;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrganizationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     $breadcrumbs = CentralLogics::getBreadcrumbs();
    //     $pageTitle = 'Organizations';
    //     $columns = ['S.No.','Organization Name', 'Address', 'Organization Status', 'Action'];
    //     $organizations = Business::all();
    //     return view("admin.organizations.index",compact('columns','breadcrumbs','pageTitle','organizations'));
    // }

    public function index(Request $request)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;
        $breadcrumbs = CentralLogics::getBreadcrumbs();
        $pageTitle = 'Organizations';
        $organizations = Business::all();

        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'select',
                    'args' => ['b_id', 'b_name', 'b_address', 'b_status', 'updated_at', 'created_at'],
                    'relation' => []

                ],
                [
                    'method' => 'sortBy',
                    'args' => ['b_id', 'b_name', 'b_address', 'b_status', 'updated_at', 'created_at'],
                ]
            ];

            // Additional filter logic...
            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new Business(),
                dynamicConditions: $dynamicConditions,
                searchColumns: ['b_id', 'b_name', 'b_address', 'b_status', 'updated_at', 'created_at']
            ))->getServerSideDataTable();

            $rowData = [];
            $i = 1;
            foreach ($list as $key => $val) {
                $row = [];
                $row[] = $i++;
                $row[] = $val->b_name;
                $row[] = $val->b_address;
                $row[] = '<span class="badge ' . ($val->b_status == 1 ? 'bg-success' : 'bg-danger') . '">' .
                    ($val->b_status == 1 ? 'Active' : 'Inactive') .
                    '</span>';

                // Action column - Edit button
                $row[] = '<a href="' . route('organizations.show', ['organization' => md5($val->b_id)]) . '"
                class="btn btn-sm btn-primary">
                 <i class="fa fa-edit"></i>
                  </a>';

                $rowData[] = $row;
            }
            $output = [
                "draw" => intval($request->input('draw')),
                "recordsTotal" => sizeof($list),
                "recordsFiltered" => (new DynamicModelDataTableHelper(
                    eloquentModel: new Business(),
                    dynamicConditions: $dynamicConditions
                ))->countFilteredServerSideDataTable(),
                "data" => $rowData,
            ];

            return json_encode($output);
        }

        // View
        $columns = [
            'S. No.',
            'Organization Name',
            'Address',
            'Organization Status',
            'Action',
        ];

        return view('admin.organizations.index', compact('columns', 'breadcrumbs', 'pageTitle', 'organizations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $breadcrumbs = CentralLogics::getBreadcrumbs();
        $pageTitle = 'Show Organization';
        $business = Business::whereRaw('MD5(b_id) = ?', [$id])->first();
        return view("admin.organizations.cards", compact('breadcrumbs', 'pageTitle', 'business'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $breadcrumbs = CentralLogics::getBreadcrumbs();

        $pageTitle = 'Edit Organization';
        $business = Business::whereRaw('MD5(b_id) = ?', [$id])->first();
        $modules = Module::all();
        $accessData = BusinessModuleAccess::where('bma_b_id', $business->b_id)->get();
        return view("admin.organizations.edit", compact('breadcrumbs', 'pageTitle', 'business', 'modules', 'accessData'));
    }

    public function organizationEmployeeDetails(Request $request, string $id)
    {
        $user = Auth::user();
        $breadcrumbs = CentralLogics::getBreadcrumbs();
        $pageTitle = 'All Employees';
        $orgBusiness = Business::whereRaw('MD5(b_id) = ?', [$id])->first();
        $employees = Employee::where('emp_b_id', $orgBusiness->b_id)->get();

        if ($request->ajax()) {
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['emp_b_id', $orgBusiness->b_id]
                ],
                [
                    'method' => 'whereNot',
                    'args' => ['emp_role_id', 1]
                ],
                [
                    'method' => 'select',
                    'args' => ['emp_id', 'emp_b_id', 'emp_full_name', 'emp_code', 'emp_role_id', 'emp_dg_id', 'emp_password', 'emp_status', 'created_at'],
                    'relation' => ['fh_designation:dg_id,dg_name', 'fh_employee_status:m_id,m_name', 'fh_role:role_id,role_name']

                ],
                [
                    'method' => 'sortBy',
                    'args' => ['emp_id', 'emp_b_id', 'emp_full_name', 'emp_code', 'emp_role_id', 'emp_dg_id', 'emp_password', 'emp_status', 'created_at'],
                ]
            ];

            // Define search value, columns, and relationships
            $searchColumns = ['emp_id', 'created_at', 'updated_at', 'emp_code', 'emp_full_name'];
            $searchRelationships = [
                'fh_designation' => ['dg_name'],
                'fh_role' => ['role_name'],
                'fh_employee_status' => ['m_name'],
            ];

            // Additional filter logic...
            $list = (new DynamicModelDataTableHelper(
                eloquentModel: new Employee(),
                dynamicConditions: $dynamicConditions,
                searchColumns: $searchColumns,
                searchRelationships: $searchRelationships
            ))->getServerSideDataTable();

            $rowData = [];
            $i = 1;
            foreach ($list as $key => $val) {
                $row = [];
                $row[] = $i++;
                $row[] = $val->emp_code;
                $row[] = $val->emp_full_name;
                $row[] = $val->fh_role?->role_name;
                $row[] = $val->fh_designation?->dg_name;
                if ($val->emp_status === 71) {
                    $row[] = '<span class="badge badge-success">' . $val->fh_employee_status->m_name . '</span>';
                } else if ($val->emp_status === 72) {
                    $row[] = '<span class="badge badge-danger">' . $val->fh_employee_status->m_name . '</span>';
                } else {
                    $row[] = '<span class="badge badge-warning"> --- </span>';
                }

                // <!-- Action column - Edit button -->
                $row[] = '<button class="btn action-btns btn-sm btn-primary" onclick="openEditPassword(this)"
                                    data-id="' . $val->emp_id . '" data-emp_full_name="' . $val->emp_full_name . '"
                                    data-emp_password="' . $val->emp_password . '">
                                    <i class="feather feather-edit"></i>
                                </button>';


                $rowData[] = $row;
            }
            $output = [
                "draw" => intval($request->input('draw')),
                "recordsTotal" => sizeof($list),
                "recordsFiltered" => (new DynamicModelDataTableHelper(
                    eloquentModel: new Employee(),
                    dynamicConditions: $dynamicConditions
                ))->countFilteredServerSideDataTable(),
                "data" => $rowData,
            ];

            return json_encode($output);
        }

        // View
        $columns = [
            'S. No.',
            'Employee Code',
            'Employee Name',
            'Role',
            'Designation',
            'Status',
            'Action',
        ];

        return view('admin.organizations.employee', compact('columns', 'breadcrumbs', 'pageTitle', 'employees'));
    }


    public function updateEmployeePassword(Request $request)
    {
        $request->validate([
            'new_password' => 'required|min:6', // Ensure password has at least 6 characters
        ]);

        $employee = Employee::find($request->emp_id);

        if ($employee) {
            $employee->emp_password = bcrypt($request->new_password); // Use correct column name

            if ($employee->save()) {
                return response()->json(['status' => 'success', 'message' => 'Password updated successfully!']);
            }

            return response()->json(['status' => 'error', 'message' => 'Failed to update password. Please try again.']);
        }

        return response()->json(['status' => 'error', 'message' => 'Employee not found. Please try again.']);
    }




    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        try {
            $request->validate([
                'business_id' => 'required|exists:businesses,b_id',
                'module' => 'nullable|array', // Expect an array of modules
                'module.*' => 'integer|exists:modules,mdl_id', // Each module ID must exist
            ]);

            $businessId = $request->input('business_id');
            $modules = $request->input('module', []);

            foreach ($modules as $moduleId => $value) {
                // Check if the record already exists
                $access = BusinessModuleAccess::where('bma_b_id', $businessId)
                    ->where('bma_mdl_id', $moduleId)
                    ->first();

                if ($access) {
                    // Update existing record
                    $access->bma_access = 1; // Checkbox is checked
                    $access->save();
                } else {
                    // Create a new record if it doesn't exist
                    BusinessModuleAccess::create([
                        'bma_b_id' => $businessId,
                        'bma_mdl_id' => $moduleId,
                        'bma_access' => 1, // Checkbox is checked
                    ]);
                }
            }

            // Handle unchecked modules (set `bma_access` to 0)
            $allModuleIds = Module::pluck('mdl_id')->toArray(); // Get all module IDs
            $uncheckedModules = array_diff($allModuleIds, array_keys($modules));

            foreach ($uncheckedModules as $moduleId) {
                $access = BusinessModuleAccess::where('bma_b_id', $businessId)
                    ->where('bma_mdl_id', $moduleId)
                    ->first();

                if ($access) {
                    $access->bma_access = 0; // Checkbox is unchecked
                    $access->save();
                }
            }

            return redirect()->back()->with('success', 'Permissions updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error updating permissions: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong. Please try again later.');
        }
    }


    public function getSubModule(Request $request, string $id)
    {
        $businessId = $request->input('business_id');

        if (!$businessId) {
            return response()->json(['error' => 'Business not found'], 404);
        }
        //****** for checked sub modules
        $accessData = BusinessModuleAccess::where('bma_b_id', $businessId)->get();
        $moduleIds = $accessData->pluck('bma_mdl_id');
        $menuModules = MenuModule::whereIn('mm_mdl_id', $moduleIds)->get();
        $menuIds = $menuModules->pluck('mm_menu_id');
        $checkedMenus = Menu::whereIn('menu_id', $menuIds)->get(); // Menus that are already saved
        //****** get all sub modules
        $menus = Menu::where('menu_mdl_id', $id)->where('menu_sub_status', 0)->get();

        return response()->json([
            'menus' => $menus,
            'checked_menus' => $checkedMenus->pluck('menu_id')
        ]);
    }


    // public function saveSubModule(Request $request)
    // {
    //     $submodules = $request->input('submodule', []);
    //     $mainModule = $request->input('mainModuleID');
    //     $existingEntries = [];

    //     foreach ($submodules as $submoduleId => $value) {
    //         if ($value == 1) {
    //             // Check if the menu module already exists
    //             $menuModule = MenuModule::where('mm_menu_id', $submoduleId)
    //                                     ->where('mm_mdl_id', $mainModule)
    //                                     ->first();

    //             if ($menuModule) {
    //                 // If exists, update instead of skipping
    //                 $menuModule->update([
    //                     'mm_menu_id' => $submoduleId,
    //                     'mm_mdl_id' => $mainModule,
    //                 ]);
    //                 $existingEntries[] = $submoduleId;
    //             } else {
    //                 // Create a new menu module entry
    //                 MenuModule::create([
    //                     'mm_menu_id' => $submoduleId,
    //                     'mm_mdl_id' => $mainModule,
    //                 ]);
    //             }

    //             // Fetch parent menu where menu_sub_status = 1
    //             $parentMenu = Menu::where('menu_p_id', $submoduleId)->where('menu_sub_status', 1)->first();
    //             if ($parentMenu) {
    //                 MenuModule::updateOrCreate(
    //                     ['mm_menu_id' => $parentMenu->menu_id, 'mm_mdl_id' => $mainModule]
    //                 );

    //                 // Fetch sub-parent menu
    //                 $subParentMenu = Menu::where('menu_p_id', $parentMenu->menu_id)->where('menu_sub_status', 1)->first();
    //                 if ($subParentMenu) {
    //                     MenuModule::updateOrCreate(
    //                         ['mm_menu_id' => $subParentMenu->menu_id, 'mm_mdl_id' => $mainModule]
    //                     );
    //                 }
    //             }
    //         } else {
    //             // If value is 0 or else, delete existing entry
    //             MenuModule::where('mm_menu_id', $submoduleId)
    //                 ->where('mm_mdl_id', $mainModule)
    //                 ->delete();
    //         }
    //     }

    //     // Return message based on existing entries
    //     if (!empty($existingEntries)) {
    //         return redirect()->back()->with('warning', 'Some menus were updated successfully.');
    //     }

    //     return redirect()->back()->with('success', 'Sub-modules saved and updated successfully!');
    // }


    public function saveSubModule(Request $request)
    {
        $submodules = $request->input('submodule', []); // Checked submodules
        $mainModule = $request->input('mainModuleID');

        // Get all existing submodules for this module
        $existingEntries = MenuModule::where('mm_mdl_id', $mainModule)->pluck('mm_menu_id')->toArray();

        $newEntries = [];
        $parentEntries = []; // To track stored parent menus

        foreach ($submodules as $submoduleId => $value) {
            if ($value == 1) {
                // Store submodule
                MenuModule::updateOrCreate([
                    'mm_menu_id' => $submoduleId,
                    'mm_mdl_id' => $mainModule
                ]);

                $newEntries[] = $submoduleId;

                // Fetch and store parent menu
                $parentMenu = Menu::where('menu_p_id', $submoduleId)->where('menu_sub_status', 1)->first();
                if ($parentMenu) {
                    MenuModule::updateOrCreate([
                        'mm_menu_id' => $parentMenu->menu_id,
                        'mm_mdl_id' => $mainModule
                    ]);

                    $parentEntries[] = $parentMenu->menu_id;

                    // Fetch and store sub-parent menu
                    $subParentMenu = Menu::where('menu_p_id', $parentMenu->menu_id)->where('menu_sub_status', 1)->first();
                    if ($subParentMenu) {
                        MenuModule::updateOrCreate([
                            'mm_menu_id' => $subParentMenu->menu_id,
                            'mm_mdl_id' => $mainModule
                        ]);

                        $parentEntries[] = $subParentMenu->menu_id;
                    }
                }
            }
        }

        // Find removed submodules (unchecked ones)
        $removedEntries = array_diff($existingEntries, $newEntries);

        if (!empty($removedEntries)) {
            // Remove unchecked submodules
            MenuModule::where('mm_mdl_id', $mainModule)
                ->whereIn('mm_menu_id', $removedEntries)
                ->delete();

            // Also remove parentMenu and subParentMenu if they are no longer needed
            foreach ($removedEntries as $removedSubmodule) {
                // Find the parent menu of the removed submodule
                $parentMenu = Menu::where('menu_p_id', $removedSubmodule)->where('menu_sub_status', 1)->first();
                if ($parentMenu) {
                    // Check if the parent menu still has any active children
                    $hasChildren = MenuModule::where('mm_mdl_id', $mainModule)
                        ->where('mm_menu_id', $parentMenu->menu_id)
                        ->exists();

                    if (!$hasChildren) {
                        // Remove parent menu
                        MenuModule::where('mm_menu_id', $parentMenu->menu_id)
                            ->where('mm_mdl_id', $mainModule)
                            ->delete();
                    }

                    // Find the sub-parent menu
                    $subParentMenu = Menu::where('menu_p_id', $parentMenu->menu_id)->where('menu_sub_status', 1)->first();
                    if ($subParentMenu) {
                        // Check if the sub-parent menu still has any active children
                        $hasSubChildren = MenuModule::where('mm_mdl_id', $mainModule)
                            ->where('mm_menu_id', $subParentMenu->menu_id)
                            ->exists();

                        if (!$hasSubChildren) {
                            // Remove sub-parent menu
                            MenuModule::where('mm_menu_id', $subParentMenu->menu_id)
                                ->where('mm_mdl_id', $mainModule)
                                ->delete();
                        }
                    }
                }
            }
        }

        return redirect()->back()->with('success', 'Sub-modules saved and updated successfully!');
    }

    public function copySettings(string $id)
    {
        $breadcrumbs = CentralLogics::getBreadcrumbs();
        $pageTitle = 'Copy Settings';

        // Fetch the selected business using MD5 hash
        $business = Business::whereRaw('MD5(b_id) = ?', [$id])->first();
        // Fetch all businesses except the current one
        $businesses = Business::where('b_id', '!=', $business->b_id)->get();
        // Dump all business IDs
        return view("admin.organizations.copySettings", compact('breadcrumbs', 'pageTitle', 'business', 'businesses'));
    }

    public function copySettingsProcess(Request $request)
    {
        $request->validate([
            'source_business' => 'required|exists:businesses,b_id',
            'target_business' => 'required|exists:businesses,b_id',
            'settings' => 'required|array',
        ]);

        // Retrieve businesses
        $sourceBusinessId = $request->input('source_business');
        $targetBusinessId = $request->input('target_business');

        // Loop through selected settings
        foreach ($request->settings as $setting) {
            switch ($setting) {

                    // 1st Case:-
                case 'attendance':

                        $oldApIds = DB::table('policy_attendances')
                            ->where('ap_b_id', $targetBusinessId)
                            ->pluck('ap_id')
                            ->toArray(); // Convert collection to array

                        // Insert new records in fh_policy_attendances
                        DB::insert("
                        INSERT INTO fh_policy_attendances (
                            ap_b_id, ap_name, ap_description, ap_grace_period_minutes,
                            ap_max_daily_working_hours, ap_max_overtime_hours, ap_overtime_requires_approval,
                            ap_overtime_rate, ap_late_penalty_rate, ap_early_leaving_penalty_rate,
                            ap_attendance_bonus_rate, ap_holiday_overtime_rate, ap_effective_date,
                            ap_expiration_date, ap_attendance_regularization, ap_limit_day,
                            ap_mispunch_regularization, ap_mispunch_limit_day, ap_status,
                            ap_checkin_method_ids
                        )
                        SELECT ?, ap_name, ap_description, ap_grace_period_minutes,
                            ap_max_daily_working_hours, ap_max_overtime_hours, ap_overtime_requires_approval,
                            ap_overtime_rate, ap_late_penalty_rate, ap_early_leaving_penalty_rate,
                            ap_attendance_bonus_rate, ap_holiday_overtime_rate, ap_effective_date,
                            ap_expiration_date, ap_attendance_regularization, ap_limit_day,
                            ap_mispunch_regularization, ap_mispunch_limit_day, ap_status,
                            ap_checkin_method_ids
                        FROM fh_policy_attendances
                        WHERE ap_b_id = ?
                    ", [$sourceBusinessId, $targetBusinessId]);

                        // Retrieve new ap_id values
                        $newApIds = DB::table('policy_attendances')
                            ->where('ap_id', '>=', DB::getPdo()->lastInsertId())
                            ->orderBy('ap_id')
                            ->limit(count($oldApIds))
                            ->pluck('ap_id')
                            ->toArray();

                        // Ensure correct mapping between old and new ap_id
                        $apIdMapping = array_combine($oldApIds, $newApIds);

                        // Fetch shift timings where pst_ap_id is in oldApIds
                        $shiftTimings = DB::table('policy_shift_timings')
                            ->whereIn('pst_ap_id', $oldApIds)
                            ->where('pst_b_id', $targetBusinessId)
                            ->get();

                        // Prepare data for batch insert
                        $shiftInsertData = [];

                        foreach ($shiftTimings as $shift) {
                            if (isset($apIdMapping[$shift->pst_ap_id])) {
                                $shiftInsertData[] = [
                                    'pst_b_id' => $sourceBusinessId,
                                    'pst_ap_id' => $apIdMapping[$shift->pst_ap_id], // Replace with new ap_id
                                    'pst_type_id' => $shift->pst_type_id,
                                    'pst_name' => $shift->pst_name,
                                    'pst_start_time' => $shift->pst_start_time,
                                    'pst_end_time' => $shift->pst_end_time,
                                    'pst_break_duration_minutes' => $shift->pst_break_duration_minutes,
                                    'pst_is_break_paid' => $shift->pst_is_break_paid,
                                    'pst_allow_break' => $shift->pst_allow_break,
                                    'pst_break_begin_time' => $shift->pst_break_begin_time,
                                    'pst_break_end_time' => $shift->pst_break_end_time,
                                    'pst_allow_punch_begin_before' => $shift->pst_allow_punch_begin_before,
                                    'pst_mins_punch_begin_before' => $shift->pst_mins_punch_begin_before,
                                    'pst_allow_punch_end_after' => $shift->pst_allow_punch_end_after,
                                    'pst_mins_punch_end_after' => $shift->pst_mins_punch_end_after,
                                    'pst_allow_grace_time' => $shift->pst_allow_grace_time,
                                    'pst_grace_time' => $shift->pst_grace_time,
                                    'pst_allow_partial_day' => $shift->pst_allow_partial_day,
                                    'pst_partial_day_type_id' => $shift->pst_partial_day_type_id,
                                    'pst_partial_day_begin_time' => $shift->pst_partial_day_begin_time,
                                    'pst_partial_day_end_time' => $shift->pst_partial_day_end_time,
                                ];
                            }
                        }

                        // Perform batch insert only if there's data
                        if (!empty($shiftInsertData)) {
                            DB::table('policy_shift_timings')->insert($shiftInsertData);  // insert data to fh_policy_shift_timings
                        }

                    // Insert new records in fh_policy_holiday_list
                        DB::insert("
                        INSERT INTO fh_policy_holiday_list  (
                            `phl_b_id`,
                            `phl_type_id`,
                            `phl_name`,
                            `phl_start_date`,
                            `phl_end_date`
                        )
                        SELECT ?, `phl_type_id`,
                           `phl_name`,
                           `phl_start_date`,
                           `phl_end_date`
                         FROM fh_policy_holiday_list
                         WHERE phl_b_id = ?
                    ", [$sourceBusinessId, $targetBusinessId]);


                    //  Insert new records in fh_policy_week_off
                    DB::insert("
                    INSERT INTO fh_policy_week_off  (
                           `pwo_b_id`,
                            `pwo_name`,
                            `pwo_day_ids`,
                            `pwo_recurrence_day_ids`
                    )
                    SELECT ?, `pwo_name`,
                          `pwo_day_ids`,
                          `pwo_recurrence_day_ids`
                        FROM fh_policy_week_off
                        WHERE pwo_b_id = ?
                ", [$sourceBusinessId, $targetBusinessId]);


                   // Insert new records in fh_policy_leaves

                   $oldPlIds = DB::table('policy_leaves')
                            ->where('pl_b_id', $targetBusinessId)
                            ->pluck('pl_id')
                            ->toArray(); // Convert collection to array
                    DB::insert("
                    INSERT INTO fh_policy_leaves  (
                         `pl_b_id`,
                         `pl_name`,
                         `pl_effective_date`,
                         `pl_expire_date`
                    )
                    SELECT ?, `pl_name`,
                         `pl_effective_date`,
                        `pl_expire_date`
                      FROM fh_policy_leaves
                      WHERE pl_b_id = ?
                ", [$sourceBusinessId, $targetBusinessId]);

                     // Retrieve new ap_id values
                        $newPlIds = DB::table('policy_leaves')
                            ->where('pl_id', '>=', DB::getPdo()->lastInsertId())
                            ->orderBy('pl_id')
                            ->limit(count($oldPlIds))
                            ->pluck('pl_id')
                            ->toArray();
                        // Ensure correct mapping between old and new ap_id
                        $plIdMapping = array_combine($oldPlIds, $newPlIds);

                        // Fetch shift timings where pst_ap_id is in oldPlIds
                        $leaveTypes = DB::table('leave_types')
                            ->whereIn('lvt_pl_id', $oldPlIds)
                            ->get();
                        // Prepare data for batch insert
                        $leaveTypesData = [];

                        foreach ($leaveTypes as $leave) {
                            if (isset($plIdMapping[$leave->lvt_pl_id])) {
                                $leaveTypesData[] = [
                                    'lvt_pl_id' => $plIdMapping[$leave->lvt_pl_id], // Replace with new pl_id
                                    'lvt_cat_type_id' => $leave->lvt_cat_type_id,
                                    'lvt_leave_cycle_id' => $leave->lvt_leave_cycle_id,
                                    'lvt_days_per_year' => $leave->lvt_days_per_year,
                                    'lvt_unused_leave_rule_id' => $leave->lvt_unused_leave_rule_id,
                                    'lvt_leave_accrual_rate' => $leave->lvt_leave_accrual_rate,
                                    'lvt_carry_forward' => $leave->lvt_carry_forward,
                                    'lvt_applicable_to_id' => $leave->lvt_applicable_to_id,
                                    'lvt_is_sandwich' => $leave->lvt_is_sandwich,
                                ];
                            }
                        }

                        // Perform batch insert only if there's data
                        if (!empty($leaveTypesData)) {
                            DB::table('leave_types')->insert($leaveTypesData);  // insert data into fh_leave_types
                        }

                    break;



                    // Add more cases as needed...
            }
        }

        return redirect()->back()->with('success', 'Settings copied successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
