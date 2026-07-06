<?php

namespace App\Http\Resources\Approval\Travel;

use App\Http\Resources\MasterTableResource;
use App\Models\MasterTable;
use App\Models\ProcessApprover;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class ApprovalDeductionLogApiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $deductionDetails = [];
        //$approver = ProcessApprover::where(['pa_am_id'=>$this->dlog_user_id, 'pa_emp_id'=>$this->dlog_user_id, 'pa_status_id'=>$this->log_status])->select('pa_last')->first();'
        if ($this->dlog_additional_info) {
            $decodedData = json_decode($this->dlog_additional_info, true);

            $keys = array_keys($decodedData);
            $values = array_values($decodedData);


            $expenseType = MasterTableResource::collection(MasterTable::whereIn('m_id', $keys)->get());

            if (!empty($expenseType) && !empty($values)) {
                for($i = 0;$i<count($values);$i++){
                    $deductionDetails[] = [
                        'expense_type'=>$expenseType[$i],
                        'deduction_amount'=>$values[$i]
                    ];
                }
            }
        }

        return [
            'name' => $this->fh_employee->emp_fname,
            'role' => $this->fh_role->role_name,
            'date' => $this->updated_at ? Carbon::parse($this->updated_at)->format('d M, Y') : null,
            'is_last' => isset($approver->pa_last)?$approver->pa_last:0,
            'total_deduction_amount'=>$this->dlog_deduction_amount,
            'deduction_details'=>$deductionDetails ? $deductionDetails : null,
		    'deduction_remark'=>$this->dlog_remarks ?? '',
        ];
    }
}
