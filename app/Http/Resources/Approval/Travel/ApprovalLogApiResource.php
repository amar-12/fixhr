<?php

namespace App\Http\Resources\Approval\Travel;

use App\Models\ProcessApprover;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApprovalLogApiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $approver = ProcessApprover::where(['pa_am_id'=>$this->log_am_id, 'pa_emp_id'=>$this->log_user_id, 'pa_status_id'=>$this->log_status])->select('pa_last')->first();
        return [
            'name' => $this->fh_employee->emp_fname,
            'role' => $this->fh_role->role_name,
            'date' => $this->updated_at,
            'is_last' => isset($approver->pa_last)?$approver->pa_last:0,
            'log_other' => $this->log_other,
            'app_rej_remark' => $this->log_description,
        ];
    }
}
