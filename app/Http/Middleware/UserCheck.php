<?php


namespace App\Http\Middleware;

use App\Helpers\RolePermissionLogics;
use App\Models\Business;
use Closure;
use Config;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(session('tenant_name') == 'devfixhr_fd_tenant') {
            Config::set('database.connections.mysql_fd_tenant.database', 'devfixhr_fd_tenant');
            DB::purge('mysql_fd_tenant');
            DB::reconnect('mysql_fd_tenant');
            Config::set('database.default', 'mysql_fd_tenant');
        }

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        } else if ($user && url('/dashboard') == $request->url()) {
            return $next($request);
        } else {
            $businessStatus = Business::where('b_id', $user->emp_b_id)->first();
            if ($businessStatus && $businessStatus->b_status == false) {

               return response()->view('subscription.business-deactivated');
            }

            $allMenus = RolePermissionLogics::get_all_menus()->toArray(); // Fetch all menus once and convert to array
            $rolePermissions = RolePermissionLogics::get_admin_role_permission($user->emp_role_id);

            foreach ($rolePermissions as $p_menu) {
                if ($this->checkPermissions($allMenus, $p_menu[0], $p_menu[1], $request, [])) {
                    return $next($request);
                }
            }
            return redirect()->back()->with('denied', "You do not have permission to access this page.");
        }
    }

    private function checkPermissions($allMenus, $menuId, $permissions, $request, $visitedMenus)
    {
        // Prevent revisiting the same menu to avoid infinite loops
        if (in_array($menuId, $visitedMenus)) {
            return false;
        }

        // Mark the current menu as visited
        $visitedMenus[] = $menuId;

        // Fetch the menu details from the array
        $menuDetails = $this->get_menu_detail($allMenus, $menuId);

        foreach ($menuDetails as $menu) {
            if ($this->hasPermission($menu, $permissions, $request)) {
                return true;
            }

            // Recursively check sub-menus
            $subMenuDetails = $this->get_menu_detail($allMenus, ['menu_p_id' => $menu['menu_id']]);
            if (!empty($subMenuDetails)) {
                foreach ($subMenuDetails as $subMenu) {
                    // Pass down the permissions of the parent menu to the child menu
                    if ($this->checkPermissions($allMenus, $subMenu['menu_id'], $permissions, $request, $visitedMenus)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function hasPermission($menu, $permissions, $request)
    {
        $route = $request->route()->uri();

        if (isset($menu['menu_route']) && (trim($menu['menu_route']) === $route)) {
            return $this->matchPermission($menu, $permissions);
        }

        return false;
    }

    private function matchPermission($menu, $permissions)
    {
        switch ($menu['menu_route_type_id']) {
            case 115:
                return isset($permissions->create) && $permissions->create === 'on';
            case 116:
                return isset($permissions->read) && $permissions->read === 'on';
            case 117:
                return isset($permissions->update) && $permissions->update === 'on';
            case 118:
                return isset($permissions->delete) && $permissions->delete === 'on';
            default:
                return false;
        }
    }

    private function get_menu_detail($allMenus, $condition)
    {
        if (is_array($condition)) {
            $filteredMenus = array_filter($allMenus, function ($menu) use ($condition) {
                foreach ($condition as $key => $value) {
                    if ($menu[$key] != $value) {
                        return false;
                    }
                }
                return true;
            });
        } else {
            $filteredMenus = array_filter($allMenus, function ($menu) use ($condition) {
                return $menu['menu_id'] == $condition;
            });
        }

        return array_values($filteredMenus);
    }

}

?>
