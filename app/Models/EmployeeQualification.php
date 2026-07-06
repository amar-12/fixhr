<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * class EmployeeQualification
 *
 * @property int $eq_id
 * @property int|null $eq_emp_id
 * @property int|null $eq_qualification_id
 * @property int|null $eq_stream_id
 * @property int|null $eq_course_type_id
 * @property string|null $eq_specialization
 * @property string|null $eq_course_nature
 * @property string|null $eq_qualification_status
 * @property string|null $eq_institution_name
 * @property string|null $eq_university_name
 * @property Carbon|null $eq_edu_from_date
 * @property Carbon|null $eq_edu_to_date
 * @property Carbon|null $eq_passing_date
 * @property string|null $eq_percentage
 * @property string|null $eq_edu_grade
 * @property string|null $eq_duration
 * @property int|null $eq_temp_country
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Employee|null $fh_employee
 * @property Qualification|null $fh_qualification
 * @property Stream|null $fh_stream
 *
 * @package App\Models
 */
class EmployeeQualification extends Model
{
	protected $table = 'employee_qualifications';
	protected $primaryKey = 'eq_id';

	protected $casts = [
		'eq_emp_id' => 'int',
		'eq_qualification_id' => 'int',
		'eq_stream_id' => 'int',
		'eq_course_type_id' => 'int',
		// 'eq_edu_from_date' => 'datetime',
		// 'eq_edu_to_date' => 'datetime',
		// 'eq_passing_date' => 'datetime',
		'eq_temp_country' => 'int'
	];

	protected $fillable = [
		'eq_emp_id',
		'eq_qualification_id',
		'eq_stream_id',
		'eq_course_type_id',
		'eq_specialization',
		'eq_course_nature',
		'eq_qualification_status',
		'eq_institution_name',
		'eq_university_name',
		'eq_edu_from_date',
		'eq_edu_to_date',
		'eq_passing_date',
		'eq_percentage',
		'eq_edu_grade',
		'eq_duration',
        'eq_year',
		'eq_temp_country'
	];

	public function fh_employee()
	{
		return $this->belongsTo(Employee::class, 'eq_emp_id');
	}

	public function fh_qualification()
	{
		return $this->belongsTo(MasterTable::class, 'eq_qualification_id')->where('m_group', 'QUALIFICATIONS');
	}

	public function fh_stream()
	{
		return $this->belongsTo(Stream::class, 'eq_stream_id');
	}
}
