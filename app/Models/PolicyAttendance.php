<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * class AttendancePolicy
 *
 * @property int $ap_id
 * @property int|null $ap_b_id
 * @property string $ap_name
 * @property string|null $ap_mark_absent_check
 * @property string|null $ap_description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Business|null $business
 * @property Collection|FhAttendanceException[] $attendance_exceptions
 * @property Collection|FhLeaveRequest[] $leave_requests
 * @property Collection|FhPolicyAttendanceBonus[] $policy_attendance_bonuses
 * @property Collection|FhPolicyHolidayList[] $policy_holiday_lists
 * @property Collection|FhPolicyLatePenalty[] $policy_late_penalties
 * @property Collection|FhPolicyLeave[] $policy_leaves
 * @property Collection|FhPolicyOvertimeRule[] $policy_overtime_rules
 * @property Collection|FhPolicyShiftTiming[] $policy_shift_timings
 *
 * @package App\Models
 */
class PolicyAttendance extends Model
{
    protected $table = 'policy_attendances';
    protected $primaryKey = 'ap_id';

    protected $casts = [
        'ap_b_id' => 'int',
    ];

    protected $fillable = [
        'ap_b_id',
        'ap_name',
        'ap_description',
        'ap_checkin_method_ids',
        'ap_mark_absent_check',
        'ap_punch_duration',
        'ap_is_selfie_restricted',
    ];

    public function fh_business()
    {
        return $this->belongsTo(Business::class, 'ap_b_id');
    }

    public function fh_check_in_method()
    {
        // Decode the stored IDs and get the matching records from the MasterTable
        return MasterTable::whereIn('m_id', json_decode($this->ap_checkin_method_ids))->get();
    }
}
