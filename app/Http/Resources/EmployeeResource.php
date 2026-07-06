<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use App\Models\Branch;
use App\Models\Announcement;
use Illuminate\Http\Request;
use App\Models\PolicyTadaCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Policy\TravelCategoryResource;
use App\Http\Resources\Policy\PolicyShiftTimeResource;
use App\Models\AppNotification;
use App\Helpers\ShiftResolver;
use App\Http\Controllers\Api\Employee\EmployeeApiController;

class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = Auth::user();

        $policyCategory = PolicyTadaCategory::where([
            'ptc_b_id' => $user->emp_b_id,
            'ptc_d_id' => $user->emp_d_id,
            'ptc_grade_id' => $user->emp_grade_id
        ])->whereJsonContains('ptc_dg_id', $user->emp_dg_id)->first();

        $announcementQuery = Announcement::where('ann_b_id', $user->emp_b_id)->where('ann_is_read', 0);

        if ($user->emp_role_id != 1) {
            $announcementQuery->where(function ($q) use ($user) {
                $q->where('ann_role_id', $user->emp_role_id)
                    ->orWhere('ann_role_id', 0);
            });
        }

        $announcementCount = $announcementQuery->count();

        $joiningDate = Carbon::parse($this->emp_date_of_joining);
        $currentDate = Carbon::now();
        $diff = $joiningDate->diff($currentDate);

        $reminderStatus = 2;
        if ($this->emp_role_id != 1) {
            $reminderStatus = $this->emp_is_reminder_enabled ?? '';
        }

        return [
            'employee_id' => $this->emp_id ?? 0,
            'employee_business_id' => $this->emp_b_id ?? 0,
            'employee_policy' => $policyCategory->ptc_name ?? '',
            'notification_status' => $this->emp_is_notification_enabled ?? 1,
            'reminder_status' => $reminderStatus,
            'reminder_show_status' => EmployeeApiController::approvalReminderToggleVisible()['show_toggle'],
            'notification_unread_count' => AppNotification::where('user_id', $this->emp_id)->where('is_read', false)->count(),
            'employee_offline_status' => $this->emp_offline_status ?? 0,
            'emp_attendance_preference' => $this->emp_attendance_preference ?? 367,
            'announcement_count' => $announcementCount,
            'is_device_lock' => $this->emp_is_device_lock ?? 0,
            'device_token' => $this->emp_device_token ?? null,
            'employee_branch' => $this->fh_branch ? new BranchResource($this->fh_branch) : [],
            'employee_geo_branch' => BranchGeoResource::collection($this->assigned_geo_branches) ?? [],
            'employee_department' => $this->fh_department ? new DepartmentResource($this->fh_department) : [],
            'employee_designation' => $this->fh_designation ? new DesignationResource($this->fh_designation) : [],
            'employee_role' => $this->fh_role ? new RoleApiResource($this->fh_role) : [],
            'employee_grade' => $this->fh_grade ? new GradeResource($this->fh_grade) : [],
            'employee_email' => $this->emp_email ?? '',
            'employee_phone' => $this->emp_phone ?? '',
            'employee_full_name' => $this->emp_full_name ?? '',
            'employee_budget_code' => $this->emp_sap_budget_code ?? '',
            'employee_account_code' => $this->emp_account_code ?? '',
            'employee_code' => $this->emp_code ?? '',
            'employee_work_mode' => $this->fh_work_mode ? MasterTableResource::collection([$this->fh_work_mode]) : [],
            'employee_checkin_method' => $this->emp_checkin_method_id ? MasterTableResource::collection($this->fh_checkin_method()) : [],

            // 'shift_details' => $this->fh_shift_type() ? PolicyShiftTimeResource::collection($this->fh_shift_type()->get()) : [],

            'shift_details' => $this->getAutoAssignedShift($user, $currentDate->format('Y-m-d')),

            'employee_gender' => $this->fh_gender ? new MasterTableResource($this->fh_gender) : null,
            'employee_marital_status' => $this->fh_marital_status ? new MasterTableResource($this->fh_marital_status) : null,
            'employee_permanent_address' => $this->emp_permanent_address ?? '',
            'employee_permanent_pin_code' => $this->emp_permanent_pin_code ?? '',
            'employee_temporary_address' => $this->emp_temporary_address ?? '',
            'employee_temporary_pin_code' => $this->emp_temporary_pin_code ?? '',
            'employee_shift_type' => $this->fh_shift_type ? ['shift_name' => $this->fh_shift_type->pst_name, 'shift_start_time' => Carbon::parse($this->fh_shift_type->pst_start_time)->format('h:i A'), 'shift_end_time' => Carbon::parse($this->fh_shift_type->pst_end_time)->format('h:i A')] : null,
            'profile_photo' => $this->emp_profile_photo ? $this->emp_profile_photo :  '',
            'employee_is_geofencing_active' => $this->emp_is_geofencing_active,
            'employee_geo_work' => (bool)$this->emp_is_geowork_active,
            'emp_is_wifi_restricted' => $this->emp_is_wifi_restricted,
            'employee_date_of_joining' => $this->emp_date_of_joining ? Carbon::parse($this->emp_date_of_joining)->format('d M, Y') : '',
            'service_length' => "{$diff->y} y {$diff->m} m {$diff->d} d",
            'employee_business' => $this->fh_business ? ['b_logo' => $this->fh_business->b_logo] : null,
            'emp_notice_period_day_for_employee' => (string) ($this->emp_notice_period_day_for_employee ?? ''),

            'group_insurance' => [
                'insured_by' => $this->emp_group_insured_by ?? "0",
                'insurance_no' => $this->emp_group_insurance_no ?? "0",
                'start_date' => $this->emp_group_insurance_start_date ?? "0",
                'till_date' => $this->emp_group_insurance_till_date ?? "0",
            ],
            'esic' => [
                'limit' => $this->fh_esic_limit->m_name ?? "0",
                'esic_no' => $this->emp_esic_no ?? "0",
                'esic_dispensary' => $this->emp_esic_dispensary ?? "0",
            ],
            'pf' => [
                'is_enabled' => $this->fh_pf_master->m_name ?? "0",
                'pf_no' => $this->emp_pf_no ?? "0",
                'pf_uan' => $this->emp_pf_universal_ac_no ?? "0",
            ],
            'device_id' => [
                'reporting_manager' => $this->fh_reporting_manager_id->emp_full_name ?? "0",
                'device_status' => $this->userDevices->sortByDesc('updated_at')->first()->ud_status ?? '',
            ],

            'uniform_items' => $this->uniformItems->map(function ($item) {
                return [
                    'material'     => (string) optional($item->material)->m_name ?? $item->uit_material_id,
                    'description'  => optional($item->outfit)->m_name ?? $item->uit_description_id,
                    'color'        => $item->uit_color,
                    'size'         => optional($item->size)->m_name ?? $item->uit_size,
                    'price'        => $item->uit_price,
                    'quantity'     => $item->uit_quantity,
                    'payable'      => $item->uit_payable,
                    'issued_date'  => $item->uit_issues_date,
                ];
            })->toArray(),

            'academic_details' => $this->academicDetails->map(function ($item) {
                return [
                    'qualification'     => optional($item->qualification)->name ?? $item->ad_qua_id,
                    'course_degree'     => optional($item->courseDegree)->name ?? $item->ad_course_degree,
                    'specialization'    => optional($item->specialization)->name ?? $item->ad_specialization,
                    'university_board'  => optional($item->board)->name ?? $item->ad_university_board,
                    'institute_name'    => $item->ad_institute_name,
                    'year_of_passing'   => $item->ad_year_of_passing,
                    'marks_type'        => $item->ad_marks_type,
                    'marks_obtained'    => $item->ad_marks_obtained,
                ];
            })->toArray(),


            'family_details' => $this->familyDetails->map(function ($item) {
                return [
                    'name' => $item->fd_name,
                    'relation' => $item->fd_relation,
                    'dob' => $item->fd_dob ? \Carbon\Carbon::parse($item->fd_dob)->format('Y-m-d') : null,
                    'dependency' => $item->fd_dependency,
                    'occupation' => $item->fd_occupation,
                    'contact' => $item->fd_contact,
                ];
            })->toArray(),

            'assets' => $this->fh_assets->map(function ($asset) {
                return [
                    'asset_tag'     => $asset->asset_tag,
                    'name'          => optional($asset->assetType)->name ?? null,
                    'brand'         => optional($asset->assetType->brand)->br_name ?? null,
                    'category'      => optional($asset->assetType->category)->ac_name ?? null,
                    'mac_address'   => $asset->mac_address ?? null,
                    'assigned_date' => $asset->assigned_at ? \Carbon\Carbon::parse($asset->assigned_at)->format('Y-m-d') : null,
                    'assigned_by'   => optional($asset->assignedBy)->emp_full_name ?? null,
                    'serial_number'     => $asset->serial_number,
                    'model_number'     => $asset->model_number,
                    'status'     => $asset->status,
                ];
            })->toArray(),
        ];
    }

    

    protected function getAutoAssignedShift($employee, $date)
    {
        $resolvedShift = ShiftResolver::resolveEmployeeShiftforUser($employee, $date);
        $shift = $resolvedShift->shift ?? PolicyShiftTiming::find($employee->emp_shift_type_id);
        
        if ($shift) {
            return PolicyShiftTimeResource::collection(collect([$shift]));
        }
        
        return PolicyShiftTimeResource::collection([]);
    }
}
