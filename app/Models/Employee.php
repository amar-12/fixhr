<?php

/**
 * Crated by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\SalaryPolicyTax;

/**
 * class Employee
 *
 * @property int $emp_id
 * @property int $emp_status
 * @property int|null $emp_b_id
 * @property int|null $emp_br_id
 * @property string|null $emp_sap_budget_code
 * @property string|null $emp_code
 * @property string|null $emp_fname
 * @property string|null $emp_mname
 * @property string|null $emp_lname
 * @property int|null $emp_d_id
 * @property int|null $emp_dg_id
 * @property int $emp_role_id
 * @property int|null $emp_type_id
 * @property int|null $emp_contractual_type_id
 * @property string|null $emp_phone
 * @property string|null $emp_email
 * @property string|null $emp_company_email
 * @property Carbon|null $emp_dob
 * @property Carbon|null $emp_date_of_joining
 * @property Carbon|null $emp_group_date_of_joining
 * @property Carbon|null $emp_date_of_gratuity
 * @property Carbon|null $emp_date_of_transfer
 * @property Carbon|null $emp_date_of_expected_confirmation
 * @property Carbon|null $emp_date_of_confirmation
 * @property Carbon|null $emp_date_of_pay_structure
 * @property int|null $emp_probation_period
 * @property int|null $emp_gender_id
 * @property int|null $emp_marital_status_id
 * @property int|null $emp_cast_id
 * @property int|null $emp_blood_group_id
 * @property int|null $emp_gov_doc_type_id
 * @property string|null $emp_gov_doc_type_number
 * @property string|null $emp_nationality
 * @property int|null $emp_religion_id
 * @property string|null $emp_address
 * @property string|null $emp_temporary_pin_code
 * @property string|null $emp_permanent_address
 * @property string|null $emp_permanent_pin_code
 * @property int|null $emp_shift_type_id
 * @property Carbon|null $emp_assign_shift_start_time
 * @property Carbon|null $emp_assign_shift_end_time
 * @property int|null $emp_reporting_manager_id
 * @property string|null $emp_imei_no
 * @property int|null $emp_work_mode_id
 * @property string|null $emp_profile_photo
 * @property int|null $emp_grade_id
 * @property string|null $emp_bank_ifsc_code
 * @property string|null $emp_bank_name
 * @property string|null $emp_bank_branch_name
 * @property string|null $emp_bank_account_no
 * @property string|null $emp_bank_branch_code
 * @property string|null $emp_bank_micr_code
 * @property string|null $emp_bank_address_line1
 * @property string|null $emp_bank_address_line2
 * @property string|null $emp_pf_no
 * @property string|null $emp_pf_trust_code
 * @property string|null $emp_pf_found_member
 * @property string|null $emp_pf_universal_ac_no
 * @property Carbon|null $emp_pf_joining_date
 * @property Carbon|null $emp_pf_leaving_date
 * @property string|null $emp_pr_leaving_reason
 * @property string|null $emp_pf_joining_no
 * @property string|null $emp_lwf_no
 * @property string|null $emp_eps_no
 * @property string|null $emp_esic_no
 * @property Carbon|null $emp_esic_joining_date
 * @property Carbon|null $emp_esic_leaving_date
 * @property string|null $emp_esic_leaving_reason
 * @property string|null $emp_esic_dispensary
 * @property int|null $emp_esic_limit
 * @property int|null $emp_year_of_service
 * @property Carbon|null $emp_retirement_date
 * @property Carbon|null $emp_separation_submit_date
 * @property Carbon|null $emp_expected_leaving_date
 * @property Carbon|null $emp_leaving_date_as_per_notice_period
 * @property int|null $emp_notice_period_req_days
 * @property string|null $emp_leaving_reason
 * @property Carbon|null $emp_leave_date
 * @property int|null $emp_notice_period_serve_days
 * @property Carbon|null $emp_settlement_from_date
 * @property Carbon|null $emp_final_settlement_date
 * @property Carbon|null $emp_exit_interview_date
 * @property Carbon|null $emp_last_working_date
 * @property string|null $emp_remark
 * @property string|null $emp_relative_name
 * @property string|null $emp_relationship
 * @property string|null $emp_relative_phone_no
 * @property string|null $emp_documents_ref_file
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Business|null $fh_business
 * @property MasterTable|null $fh_master_table
 * @property Country|null $fh_country
 * @property City|null $fh_city
 * @property Branch|null $fh_branch
 * @property Employee|null $fh_employee
 * @property Department|null $fh_department
 * @property Designation|null $fh_designation
 * @property Role $fh_role
 * @property Collection|EmployeeQualification[] $fh_employee_qualifications
 * @property Collection|Employee[] $fh_employees
 * @property Collection|OtApprovalStatus[] $fh_ot_approval_status
 *

 * @package App\Models
 */
class Employee extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'employees';
    protected $primaryKey = 'emp_id';

    const USER_TOKEN = "userToken";

    protected $hidden = [
        'emp_password',
    ];

    protected $casts = [
        'emp_status' => 'int',
        'emp_b_id' => 'int',
        'emp_br_id' => 'int',
        'emp_d_id' => 'int',
        'emp_dg_id' => 'int',
        'emp_role_id' => 'int',
        'emp_type_id' => 'int',
        'emp_contractual_type_id' => 'int',
        // 'emp_dob' => 'datetime',
        // 'emp_date_of_joining' => 'datetime',
        // 'emp_group_date_of_joining' => 'datetime',
        // 'emp_date_of_gratuity' => 'datetime',
        // 'emp_date_of_transfer' => 'datetime',
        // 'emp_date_of_expected_confirmation' => 'datetime',
        // 'emp_date_of_confirmation' => 'datetime',
        // 'emp_date_of_pay_structure' => 'datetime',
        'emp_probation_period' => 'int',
        'emp_gender_id' => 'int',
        'emp_marital_status_id' => 'int',
        'emp_cast_id' => 'int',
        'emp_blood_group_id' => 'int',
        'emp_gov_doc_type_id' => 'int',
        'emp_religion_id' => 'int',
        'emp_shift_type_id' => 'int',
        // 'emp_assign_shift_start_time' => 'datetime',
        // 'emp_assign_shift_end_time' => 'datetime',
        'emp_reporting_manager_id' => 'int',
        'emp_work_mode_id' => 'int',
        'emp_checkin_method_id' => 'array',
        'emp_grade_id' => 'int',
        // 'emp_pf_joining_date' => 'datetime',
        // 'emp_pf_leaving_date' => 'datetime',
        // 'emp_esic_joining_date' => 'datetime',
        // 'emp_esic_leaving_date' => 'datetime',
        'emp_esic_limit' => 'int',
        'emp_year_of_service' => 'int',
         'emp_offline_status' => 'int',
        // 'emp_retirement_date' => 'datetime',
        // 'emp_separation_submit_date' => 'datetime',
        // 'emp_expected_leaving_date' => 'datetime',
        // 'emp_leaving_date_as_per_notice_period' => 'datetime',
        'emp_notice_period_req_days' => 'int',
        // 'emp_leave_date' => 'datetime',
        'emp_notice_period_serve_days' => 'int',
        'emp_is_wifi_restricted' => 'bool',
        'emp_is_notification_enabled' => 'int',
        'emp_is_reminder_enabled' => 'int',
        // 'emp_settlement_from_date' => 'datetime',
        // 'emp_final_settlement_date' => 'datetime',
        // 'emp_exit_interview_date' => 'datetime',
        // 'emp_last_working_date' => 'datetime' 
        'emp_project_id' => 'array',
        'emp_assets_id' => 'array',
    ];

    protected $fillable = [
        'emp_status',
        'emp_b_id',
        'emp_br_id',
        'emp_sap_budget_code',
        'emp_code',
        'emp_prefix',
        'emp_full_name',
        'emp_fname',
        'emp_mname',
        'emp_lname',
        'emp_d_id',
        'emp_dg_id',
        'emp_role_id',
        'emp_type_id',
        'emp_contractual_type_id',
        'emp_phone',
        'emp_email',
        'emp_company_email',
        'emp_dob',
        'emp_date_of_joining',
        'emp_group_date_of_joining',
        'emp_date_of_gratuity',
        'emp_date_of_transfer',
        'emp_date_of_expected_confirmation',
        'emp_date_of_confirmation',
        'emp_date_of_pay_structure',
        'emp_probation_period',
        'emp_gender_id',
        'emp_marital_status_id',
        'emp_cast_id',
        'emp_blood_group_id',
        'emp_gov_doc_type_id',
        'emp_gov_doc_type_number',
        'emp_nationality',
        'emp_religion_id',
        'emp_address',
        'emp_temporary_pin_code',
        'emp_permanent_address',
        'emp_permanent_pin_code',
        'emp_shift_type_id',
        'emp_assign_shift_start_time',
        'emp_assign_shift_end_time',
        'emp_reporting_manager_id',
        'emp_imei_no',
        'emp_work_mode_id',
        'emp_checkin_method_id',
        'emp_profile_photo',
        'emp_grade_id',
        'emp_bank_ifsc_code',
        'emp_bank_name',
        'emp_bank_branch_name',
        'emp_bank_account_no',
        'emp_bank_branch_code',
        'emp_bank_micr_code',
        'emp_bank_address_line1',
        'emp_bank_address_line2',
        'emp_pf_no',
        'emp_is_pf_enabled',
        'emp_pf_trust_code',
        'emp_pf_found_member',
        'emp_pf_universal_ac_no',
        'emp_vpf_percentage',
        'emp_pf_joining_date',
        'emp_pf_leaving_date',
        'emp_pr_leaving_reason',
        'emp_pf_joining_no',
        'emp_lwf_no',
        'emp_eps_no',
        'emp_esic_no',
        'emp_esic_joining_date',
        'emp_esic_leaving_date',
        'emp_esic_leaving_reason',
        'emp_esic_dispensary',
        'emp_esic_limit',
        'emp_year_of_service',
        'emp_retirement_date',
        'emp_separation_submit_date',
        'emp_expected_leaving_date',
        'emp_leaving_date_as_per_notice_period',
        'emp_notice_period_req_days',
        'emp_leaving_reason',
        'emp_leave_date',
        'emp_notice_period_serve_days',
        'emp_settlement_from_date',
        'emp_final_settlement_date',
        'emp_exit_interview_date',
        'emp_last_working_date',
        'emp_remark',
        'emp_notice_period_day_for_employer',
        'emp_notice_period_day_for_employee',
        'emp_notice_period_shortfall_days',
        'emp_relative_name',
        'emp_relationship',
        'emp_relative_phone_no',
        'emp_documents_ref_file',
        'emp_otp',
        'emp_otp_created_at',
        'emp_auth_token',
        'emp_web_auth_token',
        'emp_bg_auth_token',
        'emp_fcm_token',
        'emp_password',
        'emp_job_status',
        'emp_account_code',
        'emp_supervisor_id',
        'emp_permanent_address',
        'emp_permanent_longitude',
        'emp_permanent_latitude',
        'emp_temporary_address',
        'emp_temporary_longitude',
        'emp_temporary_latitude',
        'emp_tada_settlement_amt',
        'emp_ap_id',
        'emp_pl_id',
        'emp_allow_joining_leave',
        'emp_joining_leave_calc_type',
        'emp_joining_leave_before_date',
        'emp_allow_probation_leave',
        'emp_probation_last_date',
        'emp_pwo_id',
        'emp_attendance_preference',
        'emp_is_geofencing_active',
        'emp_is_geowork_active',
        'emp_assign_geo_branch',
        'emp_offline_status',
        'emp_is_wifi_restricted',
        'emp_is_notification_enabled',
        'emp_is_reminder_enabled',
        'emp_profile_s3_url',
        'emp_rekognition_id',
        'emp_is_temporary_add_same',
        'emp_dlr_id ',

        // Group Insurance fields with 'emp_' prefix
        'emp_group_insured_by',
        'emp_group_insurance_no',
        'emp_group_insurance_start_date',
        'emp_group_insurance_till_date',

        // 🔹 About
        'emp_official_email',
        'emp_official_contact',
        'emp_emergency_contact',
        'emp_emergency_relation',

        // 🔹 Optional Info
        'emp_nationality',
        'emp_religion',
        'emp_category_id',
        'emp_body_mark',


        // 🔹 Bank Details
        'emp_salary_account_code',
        'emp_salary_bank_ifsc_code',
        'emp_salary_bank_name',
        'emp_salary_bank_branch_name',
        'emp_salary_bank_micr_code',
        'emp_salary_bank_branch_code',
        'emp_salary_bank_account_no',

        // 🔹 Joining
        'emp_profit_center',
        'emp_cost_center',
        'emp_region_id',
        'emp_project_id',
        'emp_assets_id',


        // Add Identity
        'emp_aadhar_number',
        'emp_account_number',
        'emp_driving_license_number',
        'emp_voter_id_number',
        'emp_passport_number',
        'emp_pan_number',
        'emp_is_eps_enabled',
        'emp_paymentmode',
        'emp_accountpurpose',
        'emp_drivng_license_valid',
        'emp_passport_valid',


        // Uplode Document
        'emp_aadhar_file',
        'emp_driving_license_file',
        'emp_voter_id_file',
        'emp_passbook_file',
        'emp_passport_file',
        'emp_pan_file',

        // 2FA fields
        'emp_google2fa_secret',
        'emp_google2fa_enabled_at',
        'is_approval_manager',
        'emp_is_whatsapp_enabled',
        'emp_is_device_lock',
        'emp_device_token',
        'emp_is_device_restriction',
        'emp_device_id',
        'emp_remind_me_later',
    ];

    public function routeNotificationForFcm()
    {
        return $this->emp_fcm_token;
    }

    public function fh_employee_category()
    {
        return $this->belongsTo(MasterTable::class, 'emp_category_id', 'm_id')->where('m_group', 'CAST');
    }

    public function fh_project()
    {
        return $this->belongsTo(Project::class, 'emp_project_id');
    }

    public function fh_business()
    {
        return $this->belongsTo(Business::class, 'emp_b_id');
    }

       public function fh_payroll_master_settings()
    {
        return $this->hasOne(PayrollMasterSetting::class, 'pms_b_id', 'emp_b_id');
    }


    public function fh_employee_title()
    {
        return $this->belongsTo(MasterTable::class, 'emp_prefix')->where('m_group', 'PREFIX');
    }


    public function fh_emp_region()
    {
        return $this->belongsTo(MasterTable::class, 'emp_region_id')->where('m_group', 'REGION');
    }

    public function fh_employee_status()
    {
        return $this->belongsTo(MasterTable::class, 'emp_status')->where('m_group', 'STATUS');
    }

    public function fh_employee_reson()
    {
        return $this->belongsTo(MasterTable::class, 'emp_leaving_reason')->where('m_group', 'Leaving');
    }



    public function fh_employee_esic_reson()
    {
        return $this->belongsTo(MasterTable::class, 'emp_esic_leaving_reason')->where('m_group', 'esic');
    }



    public function fh_marital_status()
    {
        return $this->belongsTo(MasterTable::class, 'emp_marital_status_id')->where('m_group', 'MARITAL_STATUS');
    }

    public function fh_cast_category()
    {
        return $this->belongsTo(MasterTable::class, 'emp_cast_id')->where('m_group', 'CAST');
    }

    public function fh_shift_type()
    {
        return $this->belongsTo(PolicyShiftTiming::class, 'emp_shift_type_id');
        // return $this->belongsTo(MasterTable::class, 'emp_shift_type_id')->where('m_group','SHIFT_TYPE');
    }

    public function fh_attendance_policy()
    {
        return $this->belongsTo(PolicyAttendance::class, 'emp_ap_id');
    }

    public function fh_policy_leave()
    {
        return $this->belongsTo(PolicyLeave::class, 'emp_pl_id')->with('fh_leave_type');
    }

    public function fh_gender()
    {
        return $this->belongsTo(MasterTable::class, 'emp_gender_id')->where('m_group', 'GENDER');
    }

    public function fh_contractual_type()
    {
        return $this->belongsTo(MasterTable::class, 'emp_contractual_type_id')->where('m_group', 'CONTRACTUAL_TYPE');
    }

    public function fh_employee_type()
    {
        return $this->belongsTo(MasterTable::class, 'emp_type_id')->where('m_group', 'EMPLOYEE_TYPE');
    }

    public function fh_gov_doc_type()
    {
        return $this->belongsTo(MasterTable::class, 'emp_gov_doc_type_id')->where('m_group', 'GOVT_DOC_TYPE');
    }

    public function fh_work_mode()
    {
        return $this->belongsTo(MasterTable::class, 'emp_work_mode_id')->where('m_group', 'WORK_MODE');
    }

    public function fh_checkin_method()
    {
        return MasterTable::whereIn('m_id', $this->emp_checkin_method_id)->get();
    }


    public function fh_checkin_methods()
    {
        return $this->belongsTo(MasterTable::class, 'emp_checkin_method_id', 'm_id')->where('m_group', 'CHECKIN_METHOD');
    }


    // public function fh_checkin_method()
    // {
    //     return MasterTable::whereIn('m_id', $this->emp_checkin_method_id)->get();  //
    // }

    public function fh_grade()
    {
        return $this->belongsTo(Grade::class, 'emp_grade_id');
    }

    public function fh_blood_group()
    {
        return $this->belongsTo(MasterTable::class, 'emp_blood_group_id')->where('m_group', 'BLOOD_GROUP');
    }

    public function fh_religion()
    {
        return $this->belongsTo(MasterTable::class, 'emp_religion_id')->where('m_group', 'RELIGION');
    }

    public function fh_branch()
    {
        return $this->belongsTo(Branch::class, 'emp_br_id');
    }

    public function fh_department()
    {
        return $this->belongsTo(Department::class, 'emp_d_id');
    }

    public function fh_designation()
    {
        return $this->belongsTo(Designation::class, 'emp_dg_id');
    }

    public function fh_role()
    {
        return $this->belongsTo(Role::class, 'emp_role_id');
    }

    public function fh_employee_qualifications()
    {
        return $this->hasOne(EmployeeQualification::class, 'eq_emp_id');
    }

    public function fh_previous_organization()
    {
        return $this->hasOne(PreviousOrganization::class, 'po_emp_id');
    }

    public function fh_reporting_manager()
    {
        return $this->hasMany(Employee::class, 'emp_reporting_manager_id');
    }


    public function fh_reporting_manager_id()
    {
        return $this->belongsTo(Employee::class, 'emp_supervisor_id');
    }


    public function fh_esic_limit()
    {
        return $this->belongsTo(MasterTable::class, 'emp_esic_limit')->where('m_group', 'YESNO');
    }

    public function fh_pf_master()
    {
        return $this->belongsTo(MasterTable::class, 'emp_is_pf_enabled')->where('m_group', 'YESNO');
    }

    public function fh_job_status()
    {
        return $this->belongsTo(MasterTable::class, 'emp_job_status')->where('m_group', 'JOB_STATUS');
    }

    public function attendance_record()
    {
        return $this->hasMany(AttendanceRecord::class, 'atd_emp_id', 'emp_id');
    }

    public function date_range_attendance_record($start_date = null, $end_date = null)
    {
        // Base relation (attendance records for this employee)
        $relation = $this->hasMany(AttendanceRecord::class, 'atd_emp_id', 'emp_id');

        // If no dates provided, return the unfiltered relation collection
        if (empty($start_date) && empty($end_date)) {
            return $relation->orderBy('atd_date')->get();
        }

        // Normalize dates using Carbon and set bounds to include whole days
        try {
            $start = $start_date ? Carbon::parse($start_date)->startOfDay() : null;
        } catch (\Exception $e) {
            $start = null;
        }

        try {
            $end = $end_date ? Carbon::parse($end_date)->endOfDay() : null;
        } catch (\Exception $e) {
            $end = null;
        }

        // If both parsed and start is after end, swap
        if ($start && $end && $start->gt($end)) {
            $tmp = $start;
            $start = $end;
            $end = $tmp;
        }

        if ($start && $end) {
            return $relation->whereBetween('atd_date', [$start, $end])->orderBy('atd_date')->get();
        }

        if ($start) {
            return $relation->where('atd_date', '>=', $start)->orderBy('atd_date')->get();
        }

        // only end present
        return $relation->where('atd_date', '<=', $end)->orderBy('atd_date')->get();
    }

    public function fh_attendance_summary()
    {
        return $this->hasMany(AttendanceSummary::class, 'as_emp_id', 'emp_id');
    }

    public function fh_employee_salary()
    {
        return $this->hasOne(SalaryEmployeeSalary::class, 'es_emp_id');
    }

    public function fh_employee_deduction_salary()
    {
        return $this->hasOne(SalaryEmployeeDeductions::class, 'es_d_emp_id')->with('fh_salary_deduction_type');
    }

    public function fh_employee_earnings_salary()
    {
        return $this->hasOne(SalaryEmployeeEarnings::class, 'es_e_emp_id')->with('fh_salary_earning_type');
    }

    public function employee_salaries()
    {
        return $this->hasMany(SalaryEmployeeSalary::class, 'es_emp_id', 'emp_id');
    }

    public function leave_requests()
    {
        return $this->hasMany(LeaveRequest::class, 'lvr_emp_id', 'emp_id');
    }

    public function leave_records($start_date, $end_date) {
        // Base relation (attendance records for this employee)
        $relation = $this->hasMany(LeaveRequest::class, 'lvr_emp_id', 'emp_id');

        // If no dates provided, return the unfiltered relation collection
        if (empty($start_date) && empty($end_date)) {
            return $relation->orderBy('lvr_start_date')->get();
        }

        // Normalize dates using Carbon and set bounds to include whole days
        try {
            $start = $start_date ? Carbon::parse($start_date)->startOfDay() : null;
        } catch (\Exception $e) {
            $start = null;
        }

        try {
            $end = $end_date ? Carbon::parse($end_date)->endOfDay() : null;
        } catch (\Exception $e) {
            $end = null;
        }

        // If both parsed and start is after end, swap
        if ($start && $end && $start->gt($end)) {
            $tmp = $start;
            $start = $end;
            $end = $tmp;
        }

        if ($start && $end) {
            return $relation->whereDate('lvr_start_date', '<=', $start_date)->whereDate('lvr_end_date', '>=', $end_date);
        }
    }

    public function fh_week_off_policy()
    {
        return $this->belongsTo(PolicyWeekOff::class, 'emp_pwo_id');
    }

    public function fh_week_off_policy2()
    {
        return $this->belongsTo(PolicyWeekOff::class, 'emp_pwo_id', 'pwo_id');
    }

    public function fh_attendance_preference()
    {
        return $this->belongsTo(MasterTable::class, 'emp_attendance_preference')->where('m_group', 'ATTENDANCE_PREFERENCE');
    }

    public function fh_geofencing()
    {
        return $this->belongsTo(MasterTable::class, 'emp_is_geofencing_active', 'm_description',)->where('m_group', 'STATUS');
    }
    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class, 'lb_emp_id', 'emp_id')->with('fh_leave_type');
    }

    public function compOffBalances()
    {
        return $this->hasMany(CompOffBalance::class, 'cb_emp_id', 'emp_id');
    }

    public function policyTax()
    {
        return $this->belongsTo(SalaryPolicyTax::class, 'emp_pt_id');
    }


    public function fh_status()
    {
        return $this->belongsTo(MasterTable::class, 'emp_job_status');
    }

    public function processedSalaries()
    {
        return $this->hasMany(ProcessedEmployeeSalary::class, 'ps_emp_id', 'emp_id');
    }

    public function latest_salary_master_history()
    {
        return $this->hasOne(SalaryMasterHistory::class, 'sm_emp_id', 'emp_id')->latest('wef');
    }

    public function fh_dealership()
    {
        return $this->belongsTo(Dealership::class, 'emp_dlr_id');
    }

    public function openingBalance()
    {
        return $this->hasOne(EmployeeOpeningBalance::class, 'eob_emp_id', 'emp_id');
    }


    public function uniformItems()
    {
        return $this->hasMany(UniformItem::class, 'uit_emp_id', 'emp_id');
    }

    public function academicDetails()
    {
        return $this->hasMany(AcademicDetail::class, 'ad_emp_id', 'emp_id');
    }

    public function familyDetails()
    {
        return $this->hasMany(FamilyDetail::class, 'fd_emp_id', 'emp_id');
    }


    public function userDevices()
    {
        return $this->hasMany(UserDevice::class, 'ud_emp_id', 'emp_id');
    }

    public function employeeProjects()
    {
        return $this->hasMany(Project::class, 'ps_b_id', 'emp_b_id');
    }

    public function historiesAsEmployee()
    {
        return $this->hasMany(AssetHistory::class, 'employee_id', 'emp_id');
    }

    public function historiesAsUser()
    {
        return $this->hasMany(AssetHistory::class, 'user_id', 'emp_id');
    }

    // Employee.php
    public function fh_assets()
    {
        return $this->hasMany(Asset::class, 'employee_id');
    }

    public function assetTypes()
    {
        return $this->hasManyThrough(
            AssetType::class, // target model
            Asset::class,     // intermediate model
            'employee_id',    // Foreign key on assets table
            'id',             // Foreign key on asset_types table
            'id',             // Local key on employees table
            'asset_type_id'   // Local key on assets table
        );
    }

    public function getAssignedGeoBranchesAttribute()
    {
        // Decode the JSON column into array of IDs
        $branchIds = json_decode($this->emp_assign_geo_branch, true) ?? [];

        // If empty, return empty collection
        if (empty($branchIds)) {
            return collect();
        }

        // Fetch and return the branch records
        return Branch::whereIn('br_id', $branchIds)->get();
    }

    public function fh_ot_approval_status()
    {
        return $this->hasMany(OtApprovalStatus::class, 'ot_emp_id', 'emp_id');
    }
    
    public function exitActions()
    {
        return $this->hasMany(EmployeeExitHistory::class,'eh_action_by','emp_id');
    }

    public function fh_leave_calculation_type()
    {
        return $this->belongsTo(MasterTable::class, 'emp_joining_leave_calc_type')->where('m_group', 'LEAVE_CALCULATION_BY');
    }
}
