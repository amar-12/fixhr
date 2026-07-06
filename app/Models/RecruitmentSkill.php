<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class RecruitmentSkill
 *
 * @property int $rs_id
 * @property string $rs_title
 * @property int $rs_b_id
 * @property int $rs_created_by_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Business $fh_business
 * @property Employee $fh_employee
 *
 * @package App\Models
 */
class RecruitmentSkill extends Model
{
	protected $table = 'recruitment_skills';
	protected $primaryKey = 'rs_id';

	protected $casts = [
		'rs_b_id' => 'int',
		'rs_created_by_id' => 'int'
	];

	protected $fillable = [
		'rs_title',
		'rs_b_id',
		'rs_created_by_id'
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'rs_b_id');
	}

	public function fh_employee()
	{
		return $this->belongsTo(Employee::class, 'rs_created_by_id');
	}
}
