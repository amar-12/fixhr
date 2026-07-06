<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class RecruitmentEmailLog
 *
 * @property int $rel_id
 * @property int $rel_b_id
 * @property int $rel_candidate_id
 * @property int $rel_mail_template_id
 * @property string $rel_subject
 * @property string $rel_body
 * @property string $rel_from_email
 * @property string $rel_to
 * @property bool $rel_status
 * @property string|null $rel_attachment
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Business $fh_business
 * @property RecruitmentCandidate $fh_recruitment_candidate
 * @property MailTemplate $fh_mail_template
 *
 * @package App\Models
 */
class RecruitmentEmailLog extends Model
{
	protected $table = 'recruitment_email_log';
	protected $primaryKey = 'rel_id';

	protected $casts = [
		'rel_b_id' => 'int',
		'rel_candidate_id' => 'int',
		'rel_mail_template_id' => 'int',
		'rel_status' => 'bool'
	];

	protected $fillable = [
		'rel_b_id',
		'rel_candidate_id',
		'rel_mail_template_id',
		'rel_subject',
		'rel_body',
		'rel_from_email',
		'rel_to',
		'rel_status',
		'rel_attachment'
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'rel_b_id');
	}

	public function fh_recruitment_candidate()
	{
		return $this->belongsTo(RecruitmentCandidate::class, 'rel_candidate_id');
	}

	public function fh_mail_template()
	{
		return $this->belongsTo(MailTemplate::class, 'rel_mail_template_id');
	}
}
