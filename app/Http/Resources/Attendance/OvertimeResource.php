<?php

namespace App\Http\Resources\Attendance;

use App\Helpers\ApprovalHelper;
use App\Helpers\CentralLogics;
use App\Http\Resources\Approval\Travel\ApprovalLogApiResource;
use App\Http\Resources\MasterTableResource;
use App\Models\ProcessApprover;
use App\Models\AttendanceRecord;
use App\Models\AttendanceException;
use App\Models\AttendanceLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class OvertimeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $approval = ApprovalHelper::checkApproval($this->fh_employee->emp_b_id, $this->ot_module_id, $this->ot_id, $this->fh_employee->emp_d_id, $this->fh_employee->emp_id);
        $nextApproverDetails = ApprovalHelper::getNextApprovalDetails($this->ot_module_id, $this->ot_id, $this->ot_b_id);
        $approversList = ProcessApprover::where(['pa_b_id'=>$this->ot_b_id,'pa_am_id'=>$this->ot_am_id])->pluck('pa_emp_id');

        if ($this->ot_atd_type == 'msp') {
            $a = AttendanceException::find($this->ot_atd_id);
            if ($a) {
                $inTime  = $a->ae_in_time;
                $outTime = $a->ae_out_time;
                $totalWork = $a->ae_total_working;
                $otHours = null;
            }
        } elseif ($this->ot_atd_type == 'log') {
            $a = AttendanceLog::find($this->ot_atd_id);
            if ($a) {
                $inTime  = $a->al_check_in_time;
                $outTime = $a->al_check_out_time;
                $totalWork = $a->al_total_worked_hours;
                $otHours = CentralLogics::convertHourMins($a->al_overtime_hours);
            }
        } elseif ($this->ot_atd_type == 'rec') {
            $a = AttendanceRecord::find($this->ot_atd_id);
            if ($a) {
                $inTime  = $a->atd_check_in_time;
                $outTime = $a->atd_check_out_time;
                $totalWork = $a->atd_total_worked_hours;
                $otHours = CentralLogics::convertHourMins($a->atd_overtime_hour);
            }
        }

        return [
            'ot_id'=> $this->ot_id,
            'b_id'=> $this->ot_b_id,
            'emp_id' => $this->fh_employee->emp_id,
            'emp_name' => $this->fh_employee->emp_full_name,
            'emp_code' => $this->fh_employee->emp_code,
            'emp_d_id' => $this->fh_employee->emp_d_id,
            'date'=> $this->ot_date ? Carbon::parse($this->ot_date)->format('d M, Y') : '',

            'in_time'=> $inTime ? Carbon::parse($inTime)->format('h:i A') : null,
            'out_time'=> $outTime ? Carbon::parse($outTime)->format('h:i A') : null,
            'total_working'=> (string)$totalWork,
            'ot_hours'=> $otHours,

            'am_id' => $this->ot_am_id,
            'module_id' => $this->ot_module_id,
            'status'=>$this->fh_approval_status ? MasterTableResource::collection([$this->fh_approval_status]) : [],
            'created_at' => $this->created_at,
            'approval_status_id' => $approval['status_id'] ?? 157,
            'approved_by' => $this->ot_approved_by ? ApprovalHelper::getEmployeename($this->ot_approved_by) : '',
            'approval_log_count' => $approval['approvallog_count'] ?? 0,
            'approval_count' => $approval['approvalcount'] ?? 0,
            'is_request_deletable' => $this->ot_stage_completed == 0,
            'approval_log' => $this->fh_plan_approval_log ? ApprovalLogApiResource::collection($this->fh_plan_approval_log)->all() : [],
            'next_approver_details' => $nextApproverDetails,
            'can_approve' => $nextApproverDetails && $nextApproverDetails['approver_id'] == Auth::user()->emp_id,
        ];
    }
}
