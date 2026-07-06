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

class AttendanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
		$approval = ApprovalHelper::checkApproval($this->fh_employee->emp_b_id, $this->atd_module_id, $this->atd_id, $this->fh_employee->emp_d_id, $this->atd_emp_id);
        $nextApproverDetails = ApprovalHelper::getNextApprovalDetails($this->atd_module_id, $this->atd_id, $this->atd_b_id);
        $co_quantity = 0;

        if ($this->atd_attendance_status == 319 || $this->atd_attendance_status == 320) {
            $workHrs = $this->atd_check_in_time && $this->atd_check_out_time ? (Carbon::parse($this->atd_check_in_time)->diffInHours(Carbon::parse($this->atd_check_out_time))) : 0;
            $compOffPolicy = CompOffPolicy::with("duration_conditions")
                ->where('cop_b_id', $this->atd_b_id)
                ->where('cop_effective_date', '<=', $this->atd_date)
                ->orderBy('cop_effective_date', 'desc')
            ->first();
            $co_quantity = $compOffPolicy ? CentralLogics::getCompOffQuantity($workHrs, $compOffPolicy->duration_conditions) : null;
        }

        return [
            'b_id' => $this->al_b_id ?? $this->atd_b_id,
            'emp_id' => $this->al_emp_id ?? $this->atd_emp_id,
            'atd_device_id' => $this->atd_device_id ?? null,
            'date' => optional(Carbon::parse($this->al_date ?? $this->atd_date))->format('d M, Y'),
            'check_in_time' => ($this->al_check_in_time ?? $this->atd_check_in_time)
            ? Carbon::parse($this->al_check_in_time ?? $this->atd_check_in_time)->format('h:i A')
            : '',
            'check_out_time' => ($this->al_check_out_time ?? $this->atd_check_out_time)
            ? Carbon::parse($this->al_check_out_time ?? $this->atd_check_out_time)->format('h:i A')
            : '',
            'total_worked_hours' => ($this->al_total_worked_hours ?? $this->atd_total_worked_hours) ? number_format(($this->al_total_worked_hours ?? $this->atd_total_worked_hours), 2) : '',
            'is_late' => $this->al_is_late ?? $this->atd_is_late,
            'late_duration' => ($this->al_late_duration ?? $this->atd_late_duration) ? number_format(($this->al_late_duration ?? $this->atd_late_duration), 2) : '',
            'is_absent' => $this->al_is_absent ?? $this->atd_is_absent,
            'is_overtime' => $this->al_is_overtime ?? $this->atd_is_overtime,
            'overtime_hours' => ($this->al_overtime_hours ?? $this->atd_overtime_hours) ? number_format(($this->al_overtime_hours ?? $this->atd_overtime_hours), 2) : '0',
            'attendance_status' => $this->fh_attendance_status ? MasterTableResource::collection([$this->fh_attendance_status]) : [],
            'atd_checkin_type' => $this->fh_attendance_checkin_type ? MasterTableResource::collection([$this->fh_attendance_checkin_type]) : [],
            'atd_work_mode_type' => $this->fh_attendance_work_mode ? MasterTableResource::collection([$this->fh_attendance_work_mode]) : [],
            'atd_segments' => json_decode($this->atd_segments,true) ? [json_decode($this->atd_segments,true)] : [],
            'punchin_photo' => $this->atd_punchin_photo ? json_decode($this->atd_punchin_photo, true) : [],
            'punchout_photo' => $this->atd_punchout_photo ? json_decode($this->atd_punchout_photo, true) : [],
            'punchin_location' => $this->atd_punchin_location ?? '',
            'punchout_location' => $this->atd_punchout_location ?? '',
            'longitude_punchin' => $this->atd_longitude_punchin ?? '',
            'latitude_punchin' => $this->atd_latitude_punchin ?? '',
            'longitude_punchout' => $this->atd_longitude_punchout ?? '',
            'latitude_punchout' => $this->atd_latitude_punchout ?? '',
			'emp_name' => $this->fh_employee->emp_full_name ?? '',
			'emp_code' => $this->fh_employee->emp_code ?? '',
			'emp_d_id' => $this->fh_employee->emp_d_id ?? '',
			'atd_id' => $this->atd_id ?? 0,
			'applied_date' => $this->created_at ? Carbon::parse($this->created_at)->format('d M, Y') : null,
			'request_date' => $this->atd_date ? Carbon::parse($this->atd_date)->format('d M, Y') : null,
			'atd_am_id' => $this->atd_am_id ?? 0,
			'atd_module_id' => $this->atd_module_id ?? 0,
			'atd_status' => $this->fh_approval_status ? MasterTableResource::collection([$this->fh_approval_status]) : [],
            'approval_status_id' => $approval['status_id'] ?? 157,
            'can_approve' => $nextApproverDetails['approver_id'] == ($request->user()->emp_id ?? 0) ? true : false,
            'approved_by' => '',
			'atd_approval_log_count' => $approval['approvallog_count'] ?? 0,
			'atd_approval_count' => $approval['approvalcount'] ?? 0,
			'is_request_deletable' => 0,
			'atd_approval_log' => $this->fh_plan_approval_log ? ApprovalLogApiResource::collection($this->fh_plan_approval_log)->all() : [],
			'atd_next_approver_details' => $nextApproverDetails,
            'co_quantity' => (string) $co_quantity ?? 0
        ];
    }
}
