<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class EmpQualification extends Model
{


	protected $table = 'emp_qualifications';
	protected $fillable = ['name'];

	public function courseDegrees()
	{
		return $this->hasMany(CourseDegree::class);
	}


	public function academicDetails()
{
    return $this->hasMany(AcademicDetail::class, 'ad_qua_id', 'id');
}

}
