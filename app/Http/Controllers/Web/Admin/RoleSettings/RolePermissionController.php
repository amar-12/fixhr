<?php

namespace App\Http\Controllers\Web\Admin\RoleSettings;

use App\Helpers\RolePermissionLogics;
use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Role;
use App\Models\RolesHasPermission;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;

class RolePermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $user = Auth::user();
            $dynamicConditions = [
                [
                    'method' => 'where',
                    'args' => ['rhp_b_id', $user->emp_b_id]
                ],
                [
                    'method' => 'select',
                    'args' => ['rhp_id', 'rhp_b_id', 'rhp_role_id', 'rhp_permissions', 'updated_at'],
                    'relation' => ['fh_business:b_id,b_name', 'fh_role:role_id,role_name'],
                ],
                [
                    'method' => 'sortBy',
                    'args' => ['rhp_id', 'rhp_role_id', 'rhp_b_id', 'updated_at', 'rhp_id']
                ]
            ];

            // Define search value, columns, and relationships
            $searchColumns = ['rhp_id', 'created_at', 'updated_at', 'rhp_b_id', 'rhp_role_id'];
            $searchRelationships = [
                'fh_role' => ['role_name']
            ];

            $list = (new DynamicModelDataTableHelper(eloquentModel: new RolesHasPermission(), dynamicConditions: $dynamicConditions, searchColumns: $searchColumns, searchRelationships: $searchRelationships))->getServerSideDataTable();

            $rowData = array();
            $i = 0;
            foreach ($list as $key => $val) {
                $i++;
                $row = array();

                $row[] = $i;
                $row[] = $val->fh_role->role_name;

                $permissions = json_decode($val->rhp_permissions, true);
                $permissionStrings = [];
                if ($permissions && count($permissions) > 0) {
                    foreach ($permissions as $menuId => $menuPermissions) {
                        // Fetch the menu name using the menuId
                        $menuName = Menu::find($menuId)->menu_name ?? 'Unknown Menu'; // Adjust the Menu model as needed

                        // Check if all permissions ('create', 'read', 'update', 'delete') are 'on'
                        if (
                            isset($menuPermissions['create']) && $menuPermissions['create'] === 'on' &&
                            isset($menuPermissions['read']) && $menuPermissions['read'] === 'on' &&
                            isset($menuPermissions['update']) && $menuPermissions['update'] === 'on' &&
                            isset($menuPermissions['delete']) && $menuPermissions['delete'] === 'on'
                        ) {
                            $permissionStrings[] = '<span class="tag tag-rounded m-1">' . $menuName . ' All</span>';
                        } else {
                            // Add individual permissions only if 'All' is not already added
                            if (!in_array('<span class="tag tag-rounded m-1">' . $menuName . ' All</span>', $permissionStrings)) {
                                if (isset($menuPermissions['create']) && $menuPermissions['create'] === 'on') {
                                    $permissionStrings[] = '<span class="tag tag-rounded m-1">' . $menuName . ' Create</span>';
                                }
                                if (isset($menuPermissions['read']) && $menuPermissions['read'] === 'on') {
                                    $permissionStrings[] = '<span class="tag tag-rounded m-1">' . $menuName . ' Read</span>';
                                }
                                if (isset($menuPermissions['update']) && $menuPermissions['update'] === 'on') {
                                    $permissionStrings[] = '<span class="tag tag-rounded m-1">' . $menuName . ' Update</span>';
                                }
                                if (isset($menuPermissions['delete']) && $menuPermissions['delete'] === 'on') {
                                    $permissionStrings[] = '<span class="tag tag-rounded m-1">' . $menuName . ' Delete</span>';
                                }
                            }
                        }
                    }
                }
                // Combine all permission tags for this row
                $permissionsFormatted = (count($permissionStrings) > 0) ? implode(' ', $permissionStrings) : 'No Permissions';

                $row[] = $permissionsFormatted;
                $row[] = '<span class="fs-11 fw-bold">W.E.F. </span><span class="with-effect-from-badge fs-10">' . date('d-M-Y h:i A', strtotime($val->updated_at)) . '</span>';
                $editUrl = '';
                // $row[] = '<a class="btn btn-outline-danger  btn-icon btn-sm" href="javascript:void(0);" onclick="ItemDeleteModel(this)" data-id=' . $val->id . ' data-rolename=' . $val->fh_role->role_name . ' data-bs-toggle="modal" data-bs-target="#deleteConfirmationModal"> <i class="feather feather-trash-2" data-bs-toggle="tooltip" data-original-title="Delete"></i></a>';
                    $row[] = $val->rhp_role_id != 1 ? '
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu p-2" style="min-width: 160px;">
                            <li>
                                <button class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2 delete-role-permission"
                                        type="button"
                                        data-id="' . $val->rhp_id . '"
                                        data-rolename="' . htmlspecialchars($val->fh_role->role_name, ENT_QUOTES, 'UTF-8') . '">
                                    <i class="feather feather-trash"></i> Delete
                                </button>
                            </li>
                        </ul>
                    </div>' : '';

                $rowData[] = $row;

            }

            $output = array(
                "draw" => request()->input('draw'),
                "recordsTotal" => sizeof($list),
                "recordsFiltered" => (new DynamicModelDataTableHelper(eloquentModel: new RolesHasPermission(), dynamicConditions: $dynamicConditions, searchColumns: $searchColumns, searchRelationships: $searchRelationships))->countFilteredServerSideDataTable(),
                "data" => $rowData,
            );

            return json_encode($output);
        }
        $columns = [
            'S. No.',
            'Role Name',
            'Permissions',
            'Date',
            'Action'
        ];

        return view('admin.setting.role-permissions.permission-list', compact('columns'));
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

    // public function store(Request $request)
    // {
    
    //     $user = Auth::user();

    //     $targetRole = Role::find($request->role_id); // Assuming you have a Role model

    //     if (!$targetRole) {
    //         return response()->json(['error' => 'Target role not found.'], 404);
    //     }

    //     // Prevent non-super admins from editing super admin or their own role
    //     if ($user->fh_role->role_name != 'Super Admin') {
    //         if ($targetRole->role_name === 'Super Admin' || $user->emp_role_id == $request->role_id) {
    //             return response()->json(['error' => 'Only authorized users can update this role\'s permissions.'], 403);
    //         }
    //     }

    //     // Early return if it's a check-only request
    //     if ($request->has('check_only')) {
    //         return response()->json(['ok' => true], 200);
    //     }
    //     $validate = Validator::make($request->all(), [
    //         'role_id.*' => 'required',
    //         'permissions.*' => 'required'
    //     ]);

    //     if ($validate->fails()) {
    //         $errors = implode(', ', $validate->errors()->all());
    //         return response()->json(['error' => $errors], 422);
    //     }

    //     RolePermissionLogics::clearSpecificCache();

    //     $data = [
    //         'rhp_permissions' => json_encode($request->permissions),
    //     ];

    //     $save = RolesHasPermission::updateOrCreate([
    //         'rhp_b_id' => $user->emp_b_id,
    //         'rhp_role_id' => $request->role_id,
    //     ], $data);

    //     if (!$save) {
    //         return response()->json(['error' => 'Failed to save role permissions.'], 500);
    //     }

    //     $message = $save->wasRecentlyCreated
    //         ? 'Role permission saved successfully.'
    //         : 'Role permission updated successfully.';

    //     return response()->json(['success' => $message]);
    // }

    public function store(Request $request)
{
    $user = Auth::user();

    $targetRole = Role::find($request->role_id);
    if (!$targetRole) {
        return response()->json(['error' => 'Target role not found.'], 404);
    }

    // Block non-super admin from editing super-admin or themselves
    if ($user->fh_role->role_name != 'Super Admin') {
        if ($targetRole->role_name === 'Super Admin' || $user->emp_role_id == $request->role_id) {
            return response()->json(['error' => 'Only authorized users can update this role\'s permissions.'], 403);
        }
    }

    if ($request->has('check_only')) {
        return response()->json(['ok' => true], 200);
    }

    $validate = Validator::make($request->all(), [
        'role_id' => 'required',
        'permissions' => 'required|array'
    ]);

    if ($validate->fails()) {
        $errors = implode(', ', $validate->errors()->all());
        return response()->json(['error' => $errors], 422);
    }

    // Get submitted permissions (menuId => perms)
    $permissions = $request->permissions;

    // Ensure it's array (should be), defensive
    if (!is_array($permissions)) {
        $permissions = (array)$permissions;
    }

    // AUTO-ADD: If a parent (menu_route == '#') was selected, include all its children and grandchildren
    foreach ($permissions as $menuId => $perm) {
        $menu = Menu::find($menuId);
        if (!$menu) continue;

        // If parent placeholder (panel) selected, add its children
        if ($menu->menu_route == '#') {
            $children = Menu::where('menu_p_id', $menuId)->get();
            foreach ($children as $child) {
                if (!isset($permissions[$child->menu_id])) {
                    $permissions[$child->menu_id] = [
                        "create" => "on",
                        "read"   => "on",
                        "update" => "on",
                        "delete" => "on",
                    ];
                }
                // also add grandchildren
                $grandchildren = Menu::where('menu_p_id', $child->menu_id)->get();
                foreach ($grandchildren as $gc) {
                    if (!isset($permissions[$gc->menu_id])) {
                        $permissions[$gc->menu_id] = [
                            "create" => "on",
                            "read"   => "on",
                            "update" => "on",
                            "delete" => "on",
                        ];
                    }
                }
            }
        }
    }

    RolePermissionLogics::clearSpecificCache();

    $data = [
        'rhp_permissions' => json_encode($permissions),
    ];

    $save = RolesHasPermission::updateOrCreate([
        'rhp_b_id' => $user->emp_b_id,
        'rhp_role_id' => $request->role_id,
    ], $data);

    if (!$save) {
        return response()->json(['error' => 'Failed to save role permissions.'], 500);
    }

    $message = $save->wasRecentlyCreated ? 'Role permission saved successfully.' : 'Role permission updated successfully.';

    return response()->json(['success' => $message]);
}


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = Auth::user();
        if ($user) {
            $delete = RolesHasPermission::where('rhp_b_id', $user->emp_b_id)->where('rhp_id', $id)->delete();
            if ($delete) {
                return response()->json(['success' => 'Permission deleted successfully']);
            }
            abort('404');
        } else {
            abort('404');
        }
    }

    public function getRolePermissions($roleId)
    {
        $permissions = RolePermissionLogics::get_role_wise_menu_permissions($roleId);
        if ($permissions) {
            return response()->json(['permissions' => json_decode($permissions->rhp_permissions, true)]);
        }
        return response()->json(['permissions' => []]);
    }
}
