<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class RecruitmentCandidate
 *
 * @property int $rc_id
 * @property bool $rc_is_active
 * @property string|null $rc_name
 * @property string|null $rc_profile
 * @property string $rc_portfolio
 * @property Carbon|null $rc_schedule_date
 * @property string $rc_email
 * @property string $rc_mobile
 * @property string $rc_resume
 * @property string|null $rc_address
 * @property string|null $rc_country
 * @property Carbon|null $rc_dob
 * @property string|null $rc_state
 * @property string|null $rc_city
 * @property string|null $rc_gender
 * @property string|null $rc_source
 * @property bool $rc_start_onboard
 * @property bool $rc_hired
 * @property bool $rc_canceled
 * @property Carbon|null $rc_joining_date
 * @property int|null $rc_sequence
 * @property Carbon|null $rc_probation_end
 * @property string $rc_offer_letter_status
 * @property Carbon|null $rc_last_updated
 * @property int|null $rc_converted_employee_id
 * @property int|null $rc_created_by_id
 * @property int|null $rc_modified_by_id
 * @property int|null $rc_referral_id
 * @property int|null $rc_dg_id
 * @property int|null $rc_recruitment_id
 * @property int|null $rc_stage_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Employee|null $fh_employee
 * @property Designation|null $fh_designation
 * @property Recruitment|null $fh_recruitment
 * @property MasterTable|null $fh_master_table
 *
 * @package App\Models
 */
class RecruitmentCandidate extends Model
{
    protected $table = 'recruitment_candidate';
    protected $primaryKey = 'rc_id';

    protected $casts = [
        'rc_is_active' => 'bool',
        'rc_schedule_date' => 'datetime',
        'rc_dob' => 'datetime',
        'rc_start_onboard' => 'bool',
        'rc_hired' => 'bool',
        'rc_canceled' => 'bool',
        'rc_joining_date' => 'datetime',
        'rc_sequence' => 'int',
        'rc_probation_end' => 'datetime',
        'rc_last_updated' => 'datetime',
        'rc_converted_employee_id' => 'int',
        'rc_created_by_id' => 'int',
        'rc_modified_by_id' => 'int',
        'rc_referral_id' => 'int',
        'rc_dg_id' => 'int',
        'rc_recruitment_id' => 'int',
        'rc_stage_id' => 'int',

    ];

    protected $fillable = [
        'rc_b_id',
        'rc_is_active',
        'rc_name',
        'rc_profile',
        'rc_portfolio',
        'rc_schedule_date',
        'rc_email',
        'rc_mobile',
        'rc_resume',
        'rc_address',
        'rc_country',
        'rc_dob',
        'rc_state',
        'rc_city',
        'rc_zip',
        'rc_gender',
        'rc_source',
        'rc_start_onboard',
        'rc_hired',
        'rc_canceled',
        'rc_joining_date',
        'rc_sequence',
        'rc_probation_end',
        'rc_offer_letter_status',
        'rc_last_updated',
        'rc_converted_employee_id',
        'rc_created_by_id',
        'rc_modified_by_id',
        'rc_referral_id',
        'rc_dg_id',
        'rc_recruitment_id',
        'rc_stage_id',
        'rc_address_local',
        'rc_terms',

    ];

    public function fh_employee()
    {
        return $this->belongsTo(Employee::class, 'rc_referral_id');
    }

    public function fh_designation()
    {
        return $this->belongsTo(Designation::class, 'rc_dg_id');
    }

    public function fh_recruitment()
    {
        return $this->belongsTo(Recruitment::class, 'rc_recruitment_id');
    }

    public function fh_recruitment_stage()
    {
        return $this->belongsTo(RecruitmentStage::class, 'rc_stage_id');
    }

    public function fh_recruitment_candidates()
	{
		return $this->hasMany(RecruitmentCandidate::class, 'rc_recruitment_id');
	}

    public function fh_gender()
	{
		return $this->belongsTo(MasterTable::class, 'rc_gender')->where('m_type', 'gender');
	}

    public function fh_country()
	{
		return $this->belongsTo(Country::class, 'rc_country');
	}

    public function fh_state()
	{
		return $this->belongsTo(State::class, 'rc_state');
	}

    public function fh_recruitment_interviewschedules()
	{
		return $this->hasMany(RecruitmentInterviewschedule::class, 'ris_candidate_id');
	}

    public function fh_recruitment_email_logs()
	{
		return $this->hasMany(RecruitmentEmailLog::class, 'rel_candidate_id');
	}

}
