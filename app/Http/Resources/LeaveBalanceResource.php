<?php

namespace App\Http\Resources;

use App\Models\MasterTable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveBalanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'category_master_detail' => $this->lb_cat_type_id ? MasterTableResource::collection([MasterTable::find($this->lb_cat_type_id)]) : [],
            'total_alloted_leave' => $this->total_alloted_leave ?? '0',
            'total_taken_leave' => $this->total_taken_leave ?? '0',
            'total_balance_remaining_leave' => $this->total_balance_remaining_leave ?? '0',
            'total_carried_forward' => $this->total_carried_forward ?? '0',
            'priority' => (int) $this->lvt_priority ?? '0',
        ];
    }
}
