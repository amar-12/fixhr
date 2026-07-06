<?php

namespace App\Http\Controllers\Web\Admin\RoleSettings;

use App\Http\Controllers\Controller;
use App\Models\AppMenu;
use App\Models\AppRolesHasPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppMenusApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $role = AppRolesHasPermission::where(json_decode('rhp_permissions'))->get(['rhp_permissions']);
        return response()->json(['result' => [$role], 'status' => false]);
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
        $user = Auth::user();

        $roles = AppRolesHasPermission::where('rhp_b_id', $user->emp_b_id)->where('rhp_role_id', $user->emp_role_id)->get(['rhp_permissions']);

        $responseData = [];

        foreach ($roles as $role) {
            $appPermissions = json_decode($role->rhp_permissions, true);
            if (is_array($appPermissions)) {
                foreach ($appPermissions as $key => $value) {
                    $data = AppMenu::where('menu_p_id',$key)->get(['menu_id','menu_p_id','menu_route_type_id']);
                    $responseData[] = [
                        'key' => $data,
                        // 'value' => $value
                    ];
                }
            }
        }

        // if (!empty($data)) {
        //     return response()->json(['result' => $data, 'status' => true]);
        // }

        return response()->json(['result' =>[$responseData], 'status' => false]);
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
        //
    }
}
