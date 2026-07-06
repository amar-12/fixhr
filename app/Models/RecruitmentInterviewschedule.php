<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class RecruitmentInterviewschedule
 *
 * @property int $ris_id
 * @property bool $ris_is_active
 * @property Carbon $ris_interview_date
 * @property Carbon $ris_interview_time
 * @property string $ris_description
 * @property bool $ris_completed
 * @property int $ris_candidate_id
 * @property int|null $rc_created_by_id
 * @property int|null $rc_modified_by_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Employee|null $fh_employee
 * @property RecruitmentCandidate $fh_recruitment_candidate
 *
 * @package App\Models
 */
class RecruitmentInterviewschedule extends Model
{
	protected $table = 'recruitment_interviewschedule';
	protected $primaryKey = 'ris_id';

	protected $casts = [
		'ris_is_active' => 'bool',
		'ris_interview_date' => 'datetime',
		'ris_interview_time' => 'datetime',
		'ris_completed' => 'bool',
		'ris_candidate_id' => 'int',
		'ris_created_by_id' => 'int',
		'ris_modified_by_id' => 'int'
	];

	protected $fillable = [
		'ris_is_active',
		'ris_interviewer',
		'ris_interview_date',
		'ris_interview_time',
		'ris_description',
		'ris_completed',
		'ris_candidate_id',
		'ris_created_by_id',
		'ris_modified_by_id'
	];

	public function fh_employee()
	{
		return $this->belongsTo(Employee::class, 'ris_modified_by_id');
	}

	public function fh_recruitment_candidate()
	{
		return $this->belongsTo(RecruitmentCandidate::class, 'ris_candidate_id');
	}

    public function fh_recruitment_created_by()
	{
		return $this->belongsTo(Employee::class, 'ris_created_by_id');
	}

    // public function fh_ris_created_by_id
}
