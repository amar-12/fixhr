<?php

namespace App\Http\Resources\Attendance;

use App\Helpers\ApprovalHelper;
use App\Helpers\CentralLogics;
use App\Http\Resources\Approval\Travel\ApprovalLogApiResource;
use App\Http\Resources\MasterTableResource;
use App\Models\ProcessApprover;
use App\Models\AttendanceRecord;
use App\Models\CompOffPolicy;
use App\Models\PolicyHolidayList;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class AttendanceExceptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $approval = ApprovalHelper::checkApproval($this->fh_employee->emp_b_id, $this->ae_module_id, $this->ae_id, $this->fh_employee->emp_d_id, $this->fh_employee->emp_id);
        $nextApproverDetails = ApprovalHelper::getNextApprovalDetails($this->ae_module_id, $this->ae_id, $this->ae_b_id);
        $approversList = ProcessApprover::where(['pa_b_id'=>$this->ae_b_id,'pa_am_id'=>$this->ae_am_id])->pluck('pa_emp_id');
        $attdRecord = AttendanceRecord::where([
            'atd_b_id' => $this->ae_b_id,
            'atd_emp_id' => $this->fh_employee->emp_id,
            'atd_date' => $this->ae_date
        ])->first();

        $co_quantity = 0;

        // Check if current day is a weekly off day
        $isWeeklyOff = CentralLogics::getWeekOffDatesReport($this->fh_employee, null, null, $this->ae_date, $this->ae_date);

        // Check if current day is a holiday
        $isHoliday = PolicyHolidayList::where('phl_b_id', $this->fh_employee->emp_b_id)
            ->whereDate('phl_start_date', '<=', $this->ae_date)
            ->whereDate('phl_end_date', '>=', $this->ae_date)
            ->where('phl_type_id', 205)
            ->exists();

        if ($isWeeklyOff || $isHoliday) {
            $compOffPolicy = CompOffPolicy::with("duration_conditions")
                ->where('cop_b_id', $this->atd_b_id)
                ->where('cop_effective_date', '<=', $this->atd_date)
                ->orderBy('cop_effective_date', 'desc')
                ->first();
            $co_quantity = CentralLogics::getCompOffQuantity($this->ae_total_working, $compOffPolicy->duration_conditions);
        }

        //$isApprover = $approversList->contains(Auth::user()->emp_id);
        return [
            'id'=> $this->ae_id,
            'b_id'=> $this->ae_b_id,
            'date'=> $this->ae_date ? Carbon::parse($this->ae_date)->format('d M, Y') : '',

            'pre_in_time'=> $attdRecord && $attdRecord->atd_check_in_time ? Carbon::parse($attdRecord->atd_check_in_time)->format('h:i A') : '',
            'pre_out_time'=> $attdRecord && $attdRecord->atd_check_out_time ? Carbon::parse($attdRecord->atd_check_out_time)->format('h:i A') : '',

            'in_time'=> $this->ae_in_time ? Carbon::parse($this->ae_in_time)->format('h:i A') : '',
            'out_time'=> $this->ae_out_time ? Carbon::parse($this->ae_out_time)->format('h:i A') : '',
            'ap_id' => $this->ae_ap_id,
            'am_id' => $this->ae_am_id,
            'module_id' => $this->ae_module_id,
            'created_at' => $this->created_at,
            'type_id'=>$this->fh_mispunch_type ? MasterTableResource::collection([$this->fh_mispunch_type]) : [],
            'status'=>$this->fh_approval_status ? MasterTableResource::collection([$this->fh_approval_status]) : [],
            'approval_status_id' => $approval['status_id'] ?? 157,
            'approved_by' => $this->ae_approved_by ? ApprovalHelper::getEmployeename($this->ae_approved_by) : '',
            'total_working'=>$this->ae_total_working ?? 0,
            'reason'=>$this->fh_mispunch_reason ? MasterTableResource::collection([$this->fh_mispunch_reason]):[],
            'custom_reason'=>$this->ae_custom_reason ?? null,
            'approval_log_count' => $approval['approvallog_count'] ?? 0,
            'approval_count' => $approval['approvalcount'] ?? 0,
            'is_request_deletable' => $this->ae_stage_completed == 0,
            'approval_log' => $this->fh_approval_log2 ? ApprovalLogApiResource::collection($this->fh_approval_log2)->all() : [],
            'next_approver_details' => $nextApproverDetails,
            'can_approve' => $nextApproverDetails['approver_id'] == ($request->user()->emp_id ?? 0) ? true : false,
            'emp_id' => $this->fh_employee->emp_id,
            'emp_name' => $this->fh_employee->emp_full_name,
            'emp_code' => $this->fh_employee->emp_code,
            'emp_d_id' => $this->fh_employee->emp_d_id,
            'co_quantity' => $co_quantity ?? 0,
        ];

    }
}
