<?php

namespace App\Helpers;

use App\Models\Menu;
use App\Models\Role;
use App\Models\RolesHasPermission;
use App\Models\AppRolesHasPermission;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use ChandraHemant\HtkcUtils\CommonUtils;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class RolePermissionLogics
{
    /* ============================================================
    |  WEB MENUS
     * ============================================================ */

    public static function get_all_menus()
    {
        return Menu::where('menu_status', 1)
            ->orderBy('menu_sequence', 'ASC')
            ->get();
    }

    public static function get_plan_menus()
    {
        $user = Auth::user();
        if (!$user) return collect();

        $subscription = \App\Models\Subscription::where('business_id', $user->emp_b_id)
            ->latest('sub_id')->first();

        if (!$subscription || !$subscription->isActive()) return collect();

        return $subscription->plan->webMenus()->orderBy('menu_sequence', 'ASC')->get();
    }

    /* ============================================================
    |  APP MENUS  (NO AppRole model needed)
     * ============================================================ */

    public static function get_app_menus()
    {
        return \App\Models\AppMenu::where('menu_status', 1)
            ->orderBy('menu_sequence', 'ASC')
            ->get();
    }

    public static function get_plan_app_menus()
    {
        $user = Auth::user();
        if (!$user) return collect();

        $subscription = \App\Models\Subscription::where('business_id', $user->emp_b_id)
            ->latest('sub_id')->first();

        if (!$subscription || !$subscription->isActive()) return collect();

        return $subscription->plan->appMenus()->orderBy('menu_sequence', 'ASC')->get();
    }

    // public static function get_app_menu_groups()
    // {
    //     $menus = self::get_app_menus();
    //     $groups = [];

    //     foreach ($menus as $menu) {
    //         $groups[$menu->menu_group][] = $menu;
    //     }

    //     return $groups;
    // }

    /* ============================================================
    |  ROLE LIST (Used for Web + App)
     * ============================================================ */

    public static function get_role_list(Model $eloquentModel = null)
    {
        $eloquentModel = $eloquentModel ?? new Role();
        $user = Auth::user();

        $dynamicConditions = [
            ['method' => 'where', 'args' => ['role_b_id', null]],
            ['method' => 'orWhere', 'args' => ['role_b_id', $user->emp_b_id]],
        ];

        return CommonUtils::getCustomModelData($eloquentModel, $dynamicConditions);
    }

    /* ============================================================
    |  PERMISSION LOADERS (WEB + APP)
     * ============================================================ */

    public static function get_role_wise_menu_permissions($roleId = null, Model $eloquentModel = null)
    {
        $eloquentModel = $eloquentModel ?? new RolesHasPermission();
        $user = Auth::user();
        $roleId = $roleId ?? $user->emp_role_id;

        $conditions = [
            ['method' => 'where', 'args' => ['rhp_role_id', $roleId]],
            ['method' => 'where', 'args' => ['rhp_b_id', $user->emp_b_id]],
        ];

        return CommonUtils::getCustomModelData($eloquentModel, $conditions, true);
    }

    public static function get_app_role_permissions($roleId = null)
    {
        $user = Auth::user();
        $roleId = $roleId ?? $user->emp_role_id;

        return AppRolesHasPermission::where('rhp_role_id', $roleId)
            ->where('rhp_b_id', $user->emp_b_id)
            ->first();
    }

    // Updated By Jagriti on 22 June 2026
    public static function get_admin_role_permission($roleId){
        $value = [];
        $permission = self::get_role_wise_menu_permissions($roleId);
        if ($permission && isset($permission->rhp_permissions) && $permission->rhp_permissions !== 'null') {
            foreach (json_decode($permission->rhp_permissions) as $data => $v) {
                $value[] = [$data, $v];
            }
        }
        return $value;
    }

    public static function get_admin_app_permission($roleId)
    {
        $value = [];
        $permission = self::get_app_role_permissions($roleId);

        if ($permission && $permission->rhp_permissions) {
            foreach (json_decode($permission->rhp_permissions) as $k => $v) {
                $value[] = [$k, $v];
            }
        }
        return $value;
    }

    /* ============================================================
    |  ROUTE PERMISSION CHECKER (WEB ONLY)
     * ============================================================ */

    public static function check_route_permission($route, $methodType, $menus = '', $rolePermissions = '')
    {
        $user = Auth::user();

        if ($menus == '') {
            $menus = self::get_all_menus()->toArray();
        }
        if ($rolePermissions == '') {
            $rolePermissions = self::get_admin_role_permission($user->emp_role_id);
        }

        $parentMenuId = self::find_parent_menu_id($menus, $route, $methodType);
        if (!$parentMenuId) return false;

        return self::check_permissions_for_route($menus, $parentMenuId, $rolePermissions, $methodType);
    }

    public static function get_menu_detail($menus, $condition)
    {
        if (is_array($condition)) {
            return array_filter($menus, function ($menu) use ($condition) {
                foreach ($condition as $key => $value) {
                    $mVal = is_array($menu) ? ($menu[$key] ?? null) : ($menu->$key ?? null);
                    if ($mVal != $value) {
                        return false;
                    }
                }
                return true;
            });
        }

        return array_filter($menus, function ($menu) use ($condition) {
            $menuId = is_array($menu) ? ($menu['menu_id'] ?? null) : ($menu->menu_id ?? null);
            return $menuId == $condition;
        });
    }


    public static function check_permissions_for_route($menus, $menuId, $rolePermissions, $methodType)
    {
        while ($menuId != 0) {
            foreach ($rolePermissions as $perm) {
                if ($perm[0] == $menuId) {
                    return self::match_permission_for_route($perm[1], $methodType);
                }
            }

            $menuDetails = self::get_menu_detail($menus, $menuId);
            if (empty($menuDetails)) break;

            $menuId = array_values($menuDetails)[0]['menu_p_id'];
        }
        return false;
    }

    public static function match_permission_for_route($permissions, $methodType)
    {
        return match ($methodType) {
            115 => ($permissions->create ?? '') === 'on',
            116 => ($permissions->read ?? '') === 'on',
            117 => ($permissions->update ?? '') === 'on',
            118 => ($permissions->delete ?? '') === 'on',
            default => false,
        };
    }

    public static function find_parent_menu_id($menus, $route, $methodType)
    {
        $filtered = array_filter($menus, fn ($menu) =>
            $menu['menu_route'] == $route &&
            $menu['menu_route_type_id'] == $methodType
        );

        return empty($filtered) ? null : array_values($filtered)[0]['menu_p_id'];
    }

    /* ============================================================
    |  CACHE CLEAR
     * ============================================================ */

    public static function clearSpecificCache()
    {
        Artisan::call('optimize:clear');
    }

    public static function isSuperAdmin()
    {
        $user = Auth::user();
        return $user && $user->emp_role_id == 1;
    }

    public static function businessHasActiveSubscription()
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        $subscription = Subscription::where('business_id', $user->emp_b_id)
            ->latest('sub_id')
            ->first();

        return $subscription && $subscription->isActive();
    }

    public static function get_menu_list(Model $eloquentModel = null, $id = '', $status = 0)
    {
        // Get full menus for correct parent/child structure
        $allMenus = self::get_all_menus();
        $planMenus = self::get_plan_menus()->pluck('menu_id')->toArray();

        return $allMenus->filter(function ($menu) use ($id, $status, $planMenus) {

            // Only show menu if included in the plan OR is a parent of a plan menu
            if (!in_array($menu->menu_id, $planMenus) &&
                !in_array($menu->menu_p_id, $planMenus)) {
                return false;
            }

            // Filter by parent if provided
            if ($id !== '' && ($menu->menu_p_id != $id)) {
                return false;
            }

            // Match sub status
            return ($menu->menu_sub_status == $status);
            // return (
            //     !is_null($menu->menu_sub_status) &&
            //     (int)$menu->menu_sub_status === (int)$status
            // );
        })->sortBy('menu_sequence')->values();
    }

    public static function get_app_menu_list($id = '', $status = 0)
    {
        $allMenus = self::get_app_menus();
        $planMenus = self::get_plan_app_menus()->pluck('menu_id')->toArray();

        return $allMenus->filter(function ($menu) use ($id, $status, $planMenus) {

            if (!in_array($menu->menu_id, $planMenus) &&
                !in_array($menu->menu_p_id, $planMenus)) {
                return false;
            }

            if ($id !== '' && $menu->menu_p_id != $id) {
                return false;
            }

            return ($menu->menu_sub_status == $status);
        })->sortBy('menu_sequence')->values();
    }

    public static function get_app_menu_groups()
    {
        $menus = self::get_plan_app_menus();
        $groups = [];

        foreach ($menus as $menu) {
            $groups[$menu->menu_group][] = $menu;
        }

        return $groups;
    }

    public static function is_active_sidebar_menu($menus, $parentMenuId)
    {
        $currentUri = Route::current()->uri();
        return self::check_uri_match($menus, $parentMenuId, $currentUri, []);
    }
    
    public static function check_uri_match($menus, $menuId, $currentUri, $visitedMenus)
    {
        // Prevent infinite loop
        if (in_array($menuId, $visitedMenus)) {
            return false;
        }

        $visitedMenus[] = $menuId;

        // Fetch menu details
        $menuDetails = array_filter($menus, function ($menu) use ($menuId) {
            return $menu['menu_id'] == $menuId;
        });

        foreach ($menuDetails as $menu) {

            // Match route
            $menuRoute = $menu['menu_route'] ?? '';

            if (
                trim($menuRoute) === $currentUri ||
                trim($menuRoute) === str_replace(['/{id}', '/{id?}'], '', $currentUri)
            ) {
                return true;
            }

            // Check submenus
            $subMenus = array_filter($menus, function ($submenu) use ($menu) {
                return $submenu['menu_p_id'] == $menu['menu_id'];
            });

            foreach ($subMenus as $subMenu) {
                if (self::check_uri_match($menus, $subMenu['menu_id'], $currentUri, $visitedMenus)) {
                    return true;
                }
            }
        }

        return false;
    }

}
