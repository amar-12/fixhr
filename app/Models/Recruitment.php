<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Recruitment
 *
 * @property int $r_id
 * @property int $r_b_id
 * @property int $r_br_id
 * @property int $r_dg_id
 * @property string|null $r_title
 * @property string|null $r_description
 * @property bool $r_is_event_based
 * @property bool $r_closed
 * @property bool $r_is_published
 * @property bool $r_is_active
 * @property int|null $r_vacancy
 * @property Carbon $r_start_date
 * @property Carbon|null $r_end_date
 * @property bool $r_optional_profile_image
 * @property bool $r_optional_resume
 * @property int $r_created_by_id
 * @property int $r_modified_by_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Business $fh_business
 * @property Branch $fh_branch
 * @property Employee $fh_employee
 * @property Designation $fh_designation
 *
 * @package App\Models
 */
class Recruitment extends Model
{
    protected $table = 'recruitment';
    protected $primaryKey = 'r_id';

    protected $casts = [
        'r_b_id' => 'int',
        'r_br_id' => 'int',
        'r_is_event_based' => 'bool',
        'r_closed' => 'bool',
        'r_is_published' => 'bool',
        'r_is_active' => 'bool',
        'r_vacancy' => 'int',
        // 'r_start_date' => 'datetime',
        // 'r_end_date' => 'datetime',
        'r_optional_profile_image' => 'bool',
        'r_optional_resume' => 'bool',
        'r_created_by_id' => 'int',
        'r_modified_by_id' => 'int'
    ];

    protected $fillable = [
        'r_b_id',
        'r_br_id',
        'r_dg_id',
        'r_title',
        'r_description',
        'r_is_event_based',
        'r_closed',
        'r_is_published',
        'r_is_active',
        'r_vacancy',
        'r_start_date',
        'r_end_date',
        'r_optional_profile_image',
        'r_optional_resume',
        'r_created_by_id',
        'r_modified_by_id',
        'r_managers',
        'r_skills',


        'r_job_type',
        'r_education',
        'r_experience',
        'r_salary',
        'r_shift',
        'r_schedule',
        'r_location',
        'r_languages',

        'created_at',
        'updated_at'
    ];

    public function fh_business()
    {
        return $this->belongsTo(Business::class, 'r_b_id');
    }

    public function fh_branch()
    {
        return $this->belongsTo(Branch::class, 'r_br_id');
    }

    public function fh_employee_modified()
    {
        return $this->belongsTo(Employee::class, 'r_modified_by_id');
    }

    public function fh_employee_created()
    {
        return $this->belongsTo(Employee::class, 'r_created_by_id');
    }

    public function fh_recruitment_stages()
	{
		return $this->hasMany(RecruitmentStage::class, 'rsg_recruitment_id')->orderBy('rsg_id', 'asc');
	}

    public function fh_recruitment_candidates()
	{
		return $this->hasMany(RecruitmentCandidate::class, 'rc_recruitment_id');
	}
}
