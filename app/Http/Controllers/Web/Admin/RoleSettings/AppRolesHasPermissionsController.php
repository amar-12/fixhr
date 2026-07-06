<?php

namespace App\Http\Controllers\Web\Admin\RoleSettings;

use App\Helpers\CentralLogics;
use App\Helpers\RolePermissionLogics;
use App\Http\Controllers\Controller;
use App\Models\AppMenu;
use App\Models\AppRolesHasPermission;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;

class AppRolesHasPermissionsController extends Controller
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

                $list = (new DynamicModelDataTableHelper(eloquentModel: new AppRolesHasPermission(), dynamicConditions: $dynamicConditions, searchColumns: $searchColumns, searchRelationships: $searchRelationships))->getServerSideDataTable();

                $rowData = array();
                $i = 0;
                foreach ($list as $key => $val) {
                    $i++;
                    $row = array();

                    $row[] = $i;
                    $row[] = $val->fh_role->role_name;

                    $permissions = json_decode($val->rhp_permissions, true);
                    $permissionStrings = [];

                    foreach ($permissions as $menuId => $menuPermissions) {
                        // Fetch the menu name using the menuId
                        $menuName = AppMenu::find($menuId)->menu_name ?? 'Unknown Menu'; // Adjust the Menu model as needed

                        // Check if all permissions ('create', 'read', 'update', 'delete') are 'on'
                        if (isset($menuPermissions['create']) && $menuPermissions['create'] === 'on' &&
                            isset($menuPermissions['read']) && $menuPermissions['read'] === 'on' &&
                            isset($menuPermissions['update']) && $menuPermissions['update'] === 'on' &&
                            isset($menuPermissions['delete']) && $menuPermissions['delete'] === 'on') {
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

                    // Combine all permission tags for this row
                    $permissionsFormatted = implode(' ', $permissionStrings);

                    $row[] = $permissionsFormatted;
                    $row[] = '<span class="fs-11 fw-bold">W.E.F. </span><span class="with-effect-from-badge fs-10">' . date('d-M-Y h:i A', strtotime($val->updated_at)) . '</span>';
                    $editUrl = '';

                    $row[] = '
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu p-2" style="min-width: 160px;">
                                <li>
                                    <button class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2 delete-role-permission"
                                            type="button"
                                            onclick="ItemDeleteModel(this)"
                                            data-id="' . $val->rhp_id . '"
                                            data-rolename="' . htmlspecialchars($val->fh_role->role_name, ENT_QUOTES, 'UTF-8') . '">
                                        <i class="feather feather-trash-2"></i> Delete
                                    </button>
                                </li>
                            </ul>
                        </div>';

                    $rowData[] = $row;
                }

                $output = array(
                    "draw" => request()->input('draw'),
                    "recordsTotal" => sizeof($list),
                    "recordsFiltered" => (new DynamicModelDataTableHelper(eloquentModel: new AppRolesHasPermission(), dynamicConditions: $dynamicConditions, searchColumns: $searchColumns, searchRelationships: $searchRelationships))->countFilteredServerSideDataTable(),
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

            return view('admin.setting.role-permissions.app-permission-list', compact('columns'));
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
            $user = Auth::user();
            $validate = Validator::make($request->all(), [
                'role_id.*'  => 'required',
                'appPermissions.*'  => 'required'
            ]);

            if($validate->fails()) {
                return response()->json([
                    'error'  => $validate->errors()->all()
                ]);
            }

            $data = [
                'rhp_permissions' => json_encode($request->appPermissions),
            ];

            $save = AppRolesHasPermission::updateOrCreate([
                'rhp_b_id' => $user->emp_b_id,
                'rhp_role_id' => $request->role_id,
            ],$data);

            if(!$save) {
                Alert::error('', "Failed to save role permissions.")->autoClose(3000);
                return response()->json(['error' => 'Failed to save policy categories.']);
            }
            Alert::success('', "Role permission saved successfully.")->autoClose(3000);
            return redirect()->back();
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
                $result = CentralLogics::dynamicDelete(AppRolesHasPermission::class, $id);

                if (isset($result['success'])) {
                    return response()->json(['success' => $result['success']]);
                } else {
                    return response()->json(['error' => $result['error']], 400);
                }
            } else {
                abort(404);
            }
        }

        public function getRolePermissions($roleId)
        {
            $permissions = RolePermissionLogics::get_role_wise_menu_permissions($roleId, new AppRolesHasPermission());

            if($permissions){
                return response()->json(['appPermissions' => json_decode($permissions->rhp_permissions, true)]);
            }
            return response()->json(['appPermissions' => []]);
        }
    }
