<?php

namespace App\Http\Resources;

use App\Http\Resources\MasterTableResource;
use App\Models\LeaveBalance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class LeaveTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = Auth::user();

        $leaveTypePolicy = $user->fh_policy_leave->fh_leave_type ?? collect();

        // Build a mapping of leave category id => lvt_priority for quick lookup
        $leaveTypePriorities = collect($leaveTypePolicy)->pluck('lvt_priority', 'lvt_cat_type_id');

        $balance = LeaveBalance::where('lb_emp_id', $user->emp_id)
        ->selectRaw('lb_cat_type_id,lb_alloted_leave as total_alloted_leave,lb_taken_leave as total_taken_leave,lb_balance_remaining_leave as total_balance_remaining_leave')
        ->where('lb_cat_type_id', $this->lvt_cat_type_id)
            ->where('lb_year', now()->year)
            ->where('lb_month', now()->month)
            ->get();

        // Attach lvt_priority to each leave balance row using the mapping
        $balance->transform(function ($lb) use ($leaveTypePriorities) {
            $lb->lvt_priority = $leaveTypePriorities->get($lb->lb_cat_type_id) ?? null;
            return $lb;
        });

        return [
            'lvt_id' => $this->lvt_id ?? 0,
            'cat_type_id' => $this->fh_leave_cat_type ? MasterTableResource::collection([$this->fh_leave_cat_type]) : [],
            'leave_balance_details' => $balance ? LeaveBalanceResource::collection($balance)
                : [[
                    'category_master_detail' => [],
                    'total_alloted_leave' => 0,
                    'total_taken_leave' => 0,
                    'total_balance_remaining_leave' => 0,
                ]],
        ];

    }
    
}
