<?php

namespace App\Http\Controllers\Api;

use ChandraHemant\HtkcUtils\ReturnHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\RolePermission\AppMenusResource;
use App\Http\Resources\RolePermission\AppRolesHasPermissionsResource;
use App\Models\AppMenu;
use App\Models\AppRolesHasPermission;
use Illuminate\Support\Facades\Auth;
use DB;

class MenuApiController extends Controller
{
    public function getAllMenus()
    {
        $menus = AppMenu::where('menu_status', 1)->whereNot('menu_id',1)->orderBy('menu_sequence', 'ASC')->get();

        $permissionData = AppRolesHasPermission::where(['rhp_b_id'=> Auth::user()->emp_b_id, 'rhp_role_id'=> Auth::user()->emp_role_id])->pluck('rhp_permissions')->first();
        $hasAttendance = false;
        if($permissionData){
            $hasAttendance = isset(json_decode($permissionData,true)[1]);
        }

        if ($menus) {
            return ReturnHelper::jsonApiReturn([['menus'=>AppMenusResource::collection($menus)->all(),
            'drawer_menu' => config('app_drawer_menu.app_drawer_menu'),
            'has_attendance' => $hasAttendance,
            ]]);
        }
        return response()->json(['result' => [], 'status' => false]);
    }

    public function getRoleWiseMenuPermissions()
    {
        $user = Auth::user();

        $permissions = AppRolesHasPermission::where('rhp_role_id', $user->emp_role_id)->where('rhp_b_id', $user->emp_b_id)->first();
        if ($permissions) {
            return ReturnHelper::jsonApiReturn(AppRolesHasPermissionsResource::collection([$permissions])->all());
        }
        return response()->json(['result' => [], 'status' => false]);
    }
}
