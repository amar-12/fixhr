<?php

namespace App\Http\Resources\Attendance;

use App\Helpers\ApprovalHelper;
use App\Helpers\CentralLogics;
use App\Http\Resources\Approval\Travel\ApprovalLogApiResource;
use App\Http\Resources\MasterTableResource;
use App\Models\CompOffPolicy;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceOutDoorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $approval = ApprovalHelper::checkApproval(
            $this->fh_employee->emp_b_id,
            $this->atd_od_module_id,
            $this->atd_od_id,
            $this->fh_employee->emp_d_id,
            $this->atd_od_emp_id
        );

        // dd($this->fh_employee->emp_b_id, $this->atd_od_module_id, $this->atd_od_id, $this->fh_employee->emp_d_id, $this->atd_od_emp_id, $approval);

        $nextApproverDetails = ApprovalHelper::getNextApprovalDetails($this->atd_od_module_id, $this->atd_od_id, $this->atd_od_b_id);

        // dd($this->atd_od_module_id, $this->atd_od_id, $this->atd_od_b_id, $nextApproverDetails);

        $co_quantity = 0;

        if ($this->atd_od_attendance_status == 319 || $this->atd_od_attendance_status == 320) {
            $workHrs = $this->atd_od_check_in_time && $this->atd_od_check_out_time
                ? (Carbon::parse($this->atd_od_check_in_time)->diffInHours(Carbon::parse($this->atd_od_check_out_time)))
                : 0;

            $compOffPolicy = CompOffPolicy::with("duration_conditions")
                ->where('cop_b_id', $this->atd_od_b_id)
                ->where('cop_effective_date', '<=', $this->atd_od_date)
                ->orderBy('cop_effective_date', 'desc')
                ->first();

            $co_quantity = $compOffPolicy ? CentralLogics::getCompOffQuantity($workHrs, $compOffPolicy->duration_conditions) : null;
        }

        return [
            'b_id' => $this->atd_od_b_id,
            'emp_id' => $this->atd_od_emp_id,
            'atd_device_id' => $this->atd_od_device_id,
            'date' => $this->atd_od_date ? Carbon::parse($this->atd_od_date)->format('d M, Y') : '',
            'check_in_time' => $this->atd_od_check_in_time ? Carbon::parse($this->atd_od_check_in_time)->format('h:i A') : '',
            'check_out_time' => $this->atd_od_check_out_time ? Carbon::parse($this->atd_od_check_out_time)->format('h:i A') : '',
            'total_worked_hours' => !empty($this->atd_od_total_working_hours) ? (int)substr($this->atd_od_total_working_hours, 0, 2) . '.' . substr($this->atd_od_total_working_hours, 3, 2) : '',
            'is_late' => $this->atd_od_is_late,
            'late_duration' => $this->atd_od_late_duration ? number_format($this->atd_od_late_duration,2): '',
            'is_overtime' => $this->atd_od_is_overtime ?? 0,
            'overtime_hours' => $this->atd_od_overtime_hours ? number_format($this->atd_od_overtime_hours,2) : '0',
            'attendance_status' => $this->fh_attendance_status ? MasterTableResource::collection([$this->fh_attendance_status]) : [],
            'atd_checkin_type' => $this->fh_attendance_checkin_type ? MasterTableResource::collection([$this->fh_attendance_checkin_type]) : [],
            'atd_work_mode_type' => $this->fh_attendance_work_mode ? MasterTableResource::collection([$this->fh_attendance_work_mode]) : [],
            'atd_segments' => json_decode($this->atd_od_segments,true) ? [json_decode($this->atd_od_segments,true)] : [],
            'punchin_photo' => $this->atd_od_punchin_photo ? json_decode($this->atd_od_punchin_photo, true) : [],
            'punchout_photo' => $this->atd_od_punchout_photo ? json_decode($this->atd_od_punchout_photo, true) : [],
            'punchin_location' => $this->atd_od_punchin_location ?? '',
            'punchout_location' => $this->atd_od_punchout_location ?? '',
            'longitude_punchin' => $this->atd_od_longitude_punchin ?? '',
            'latitude_punchin' => $this->atd_od_latitude_punchin ?? '',
            'longitude_punchout' => $this->atd_od_longitude_punchout ?? '',
            'latitude_punchout' => $this->atd_od_latitude_punchout ?? '',
            'emp_name' => $this->fh_employee->emp_full_name ?? '',
            'emp_code' => $this->fh_employee->emp_code ?? '',
            'emp_d_id' => $this->fh_employee->emp_d_id ?? '',
            'atd_id' => $this->atd_od_id ?? 0,
            'applied_date' => $this->created_at ? Carbon::parse($this->created_at)->format('d M, Y') : null,
            'request_date' => $this->atd_od_date ? Carbon::parse($this->atd_od_date)->format('d M, Y') : null,
            'atd_am_id' => $this->atd_od_am_id ?? 0,
            'atd_module_id' => $this->atd_od_module_id ?? 0,
            'atd_status' => $this->fh_approval_status ? MasterTableResource::collection([$this->fh_approval_status]) : [],
            'approval_status_id' => $approval['status_id'] ?? 157,
            'can_approve' => $nextApproverDetails['approver_id'] == ($request->user()->emp_id ?? 0) ? true : false,
            'approved_by' => $this->atd_od_approved_by ? ApprovalHelper::getEmployeename($this->atd_od_approved_by) : '',
            'atd_approval_log_count' => $approval['approvallog_count'] ?? 0,
            'atd_approval_count' => $approval['approvalcount'] ?? 0,
            'is_request_deletable' => 0,
            'atd_approval_log' => $this->fh_plan_approval_log ? ApprovalLogApiResource::collection($this->fh_plan_approval_log)->all() : [],
            'atd_next_approver_details' => $nextApproverDetails,
            'co_quantity' => (string) $co_quantity ?? 0
        ];
    }
}
