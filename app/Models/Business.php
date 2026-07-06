<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * class Business
 *
 * @pr  rty int $b_id
 * @property string|null $b_unique_id
 * @property int|null $b_emp_id
 * @property int|null $b_category_id
 * @property int|null $b_type_id
 * @property int|null $b_dashboard_id
 * @property string|null $b_name
 * @property int|null $b_city_id
 * @property int|null $b_state_id
 * @property int|null $b_country_id
 * @property string|null $b_gst_no
 * @property string|null $b_pan_no
 * @property string|null $b_pin_code
 * @property string|null $b_address
 * @property string|null $b_logo
 * @property int|null $b_is_verfied
 * @property int|null $b_status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Admin|null $fh_admin
 * @property MasterTable|null $fh_master_table
 * @property City|null $fh_city
 * @property State|null $fh_state
 * @property Country|null $fh_country
 * @property Collection|Branch[] $fh_branches
 * @property Collection|Department[] $fh_departments
 * @property Collection|Designation[] $fh_designations
 * @property Collection|Employee[] $fh_employees
 * @property Collection|PolicyTadaCategory[] $fh_policy_tada_categories
 * @property Collection|PolicyTadaDailyAllowanceLodging[] $fh_policy_tada_daily_allowance_lodgings
 * @property Collection|PolicyTadaMiscellaneous[] $fh_policy_tada_miscellaneous
 * @property Collection|PolicyTadaTravelAllowance[] $fh_policy_tada_travel_allowances
 * @property Collection|PolicyTadaTravelMode[] $fh_policy_tada_travel_modes
 * @property Collection|PolicyTadaTravelType[] $fh_policy_tada_travel_types
 * @property Collection|Role[] $fh_roles
 *
 * @package App\Models
 */
class   Business extends Model
{
    protected $table = 'businesses';
    protected $primaryKey = 'b_id';

    protected $casts = [
        'b_category_id' => 'int',
        'b_type_id' => 'int',
        'b_city_id' => 'int',
        'b_state_id' => 'int',
        'b_country_id' => 'int',
        'b_is_verified' => 'int',
        'b_status' => 'int',
		'b_fnf_modules' => 'array',
    ];

    protected $fillable = [
        'b_unique_id',
        'b_category_id',
        'b_type_id',
        'b_dashboard_id',
        'b_name',
        'b_city_id',
        'b_state_id',
        'b_country_id',
        'b_gst_no',
        'b_pan_no',
        'b_pin_code',
        'b_address',
        'b_logo',
        'b_is_verified',
        'b_status',
        'b_longitude',
        'b_latitude',
        'b_emp_code',
        'b_emp_code_type',
        'b_currency',
        'is_face_detection_active',
        'b_tag_line',
        'b_bank_name',
        'b_bank_acc_no',
        'b_bank_ifsc',
        'b_bank_address',
        'b_cheque_no',
        'bridge_url',
        'b_fnf_modules',
        'demo_setup_completed',
        'is_notification',
        'is_whatsapp',
        'switch_business_email',
    ];


    public function fh_admin()
    {
        return $this->hasOne(Employee::class, 'emp_b_id')->where('emp_role_id', 1);
    }

    public function fh_business_status()
    {
        return $this->belongsTo(MasterTable::class, 'b_status', 'm_description')->where('m_group', 'STATUS');
    }

    public function fh_business_category()
    {
        return $this->belongsTo(MasterTable::class, 'b_category_id')->where('m_group', 'BUSINESS_CATEGORY');
    }

    public function fh_business_type()
    {
        return $this->belongsTo(MasterTable::class, 'b_type_id')->where('m_group', 'BUSINESS_TYPE');
    }

    public function fh_emp_code_type()
    {
        return $this->belongsTo(MasterTable::class, 'b_emp_code_type')->where('m_group', 'EMP_CODE');
    }

    public function fh_city()
    {
        return $this->belongsTo(City::class, 'b_city_id');
    }

    public function fh_state()
    {
        return $this->belongsTo(State::class, 'b_state_id');
    }

    public function fh_country()
    {
        return $this->belongsTo(Country::class, 'b_country_id');
    }

    public function fh_currency()
    {
        return $this->belongsTo(Country::class, 'b_currency');
    }


    public function fh_branches()
    {
        return $this->hasMany(Branch::class, 'br_b_id');
    }

    public function fh_departments()
    {
        return $this->hasMany(Department::class, 'd_b_id');
    }

    public function fh_designations()
    {
        return $this->hasMany(Designation::class, 'dg_b_id');
    }

    public function fh_miscellaneous()
    {
        return $this->hasMany(Designation::class, 'mis_b_id');
    }

    public function fh_employees()
    {
        return $this->hasMany(Employee::class, 'emp_b_id');
    }

    public function fh_policy_tada_categories()
    {
        return $this->hasMany(PolicyTadaCategory::class, 'ptc_b_id');
    }

    public function fh_policy_tada_daily_allowance_lodgings()
    {
        return $this->hasMany(PolicyTadaDailyAllowanceLodging::class, 'ptdal_b_id');
    }

    public function fh_policy_tada_miscellaneous()
    {
        return $this->hasMany(PolicyTadaMiscellaneous::class, 'pm_b_id');
    }

    public function fh_policy_tada_travel_allowances()
    {
        return $this->hasMany(PolicyTadaTravelAllowance::class, 'ptta_b_id');
    }

    public function fh_policy_tada_travel_modes()
    {
        return $this->hasMany(PolicyTadaTravelMode::class, 'pttm_b_id');
    }

    public function fh_policy_tada_travel_types()
    {
        return $this->hasMany(PolicyTadaTravelType::class, 'pttt_b_id');
    }

    public function fh_roles()
    {
        return $this->hasMany(Role::class, 'role_b_id');
    }

    public function fh_recruitment_skills()
    {
        return $this->hasMany(RecruitmentSkill::class, 'rs_b_id');
    }

    public function fh_recruitment()
    {
        return $this->hasMany(Recruitment::class, 'r_b_id');
    }

    // Relationship with fh_attendance_policies
    public function fh_attendance_policies()
    {
        return $this->hasMany(PolicyAttendance::class, 'ap_b_id', 'b_id');
    }

    // Relationship with fh_policy_shift_timing
    public function fh_shift_timings()
    {
        return $this->hasMany(PolicyShiftTiming::class, 'pst_b_id', 'b_id');
    }

    // Relationship with fh_policy_holiday_list
    public function fh_holiday_policies()
    {
        return $this->hasMany(PolicyHolidayList::class, 'phl_b_id', 'b_id');
    }

    // Relationship with fh_policy_leave
    public function fh_leave_policies()
    {
        return $this->hasMany(PolicyLeave::class, 'pl_b_id', 'b_id');
    }

    // Relationship with fh_policy_week_off
    public function fh_weekOff_policies()
    {
        return $this->hasMany(PolicyWeekOff::class, 'pwo_b_id', 'b_id');
    }

    public function fh_grades(){
        return $this->hasMany(Grade::class, 'g_b_id', 'b_id');
    }

    public function fh_recruitment_candidates(){
        return $this->hasMany(RecruitmentCandidate::class, 'rc_b_id', 'b_id');
    }

    public function fh_recruitments()
	{
		return $this->hasMany(Recruitment::class, 'r_b_id');
	}

    public function fh_mail_templates()
	{
		return $this->hasMany(MailTemplate::class, 'mt_b_id');
	}

    public function fh_income_tax_slabs()
	{
		return $this->hasMany(IncomeTaxSlab::class, 'its_b_id');
	}

    public function fh_timezone()
    {
        return $this->belongsTo(Timezone::class, 'b_timezone', 'tz_id');
    }

	protected static function booted()
	{
		static::updated(function (Business $business) {

			// Check if status actually changed
			if ($business->wasChanged('b_status') && (int) $business->b_status === 0) {

				Employee::where('emp_b_id', $business->b_id)
					->update([
						'auth_token' => null
					]);
			}
		});
	}

    public function subscription()
    {
        return $this->hasOne(\App\Models\Subscription::class, 'business_id', 'b_id')
            ->latestOfMany('sub_id');
    }

    public function getSwitchEmailsArrayAttribute()
    {
        return $this->switch_business_email
            ? array_map('trim', explode(',', $this->switch_business_email))
            : [];
    }
}
