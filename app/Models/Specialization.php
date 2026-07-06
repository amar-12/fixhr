<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Specialization extends Model
{
    protected $table = 'emp_specializations';
    protected $fillable = ['name', 'course_degree_id'];

    public function courseDegree()
    {
        return $this->belongsTo(CourseDegree::class);
    }

    public function academicDetails()
{
    return $this->hasMany(AcademicDetail::class, 'ad_specialization', 'id');
}

}
