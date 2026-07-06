<?php

namespace App\Http\Resources\RolePermission;

use App\Models\MasterTable;
use App\Models\ProcessApprover;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AppRolesHasPermissionsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = Auth::user();
        $modules = MasterTable::select('m_id','m_name')->where('m_group', 'MODULE')->get();
        $modulePermissions = [];
        foreach($modules as $module){
            $approver = ProcessApprover::whereHas('fh_approval_module', function($query) use ($module) {
                $query->where('am_module_id', $module->m_id);
            })->where('pa_b_id', $user->emp_b_id)->where('pa_emp_id', $user->emp_id)->first();
            if($approver){
                $modulePermissions[Str::lower($module->m_name)] = true;
            }else{
                $modulePermissions[Str::lower($module->m_name)] = false;
            }
        }
        return [
            'rhp_id' => $this->rhp_id,
            'rhp_b_id' => $this->rhp_b_id,
            'rhp_role_id' => $this->rhp_role_id,
            'rhp_permissions' => json_decode($this->rhp_permissions),
            'rph_modules' => $modulePermissions
        ];
    }
}
