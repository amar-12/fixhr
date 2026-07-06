<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseDegree extends Model
{
    protected $table = 'emp_course_Degrees';

    protected $fillable = ['name', 'qualification_id'];

    public function qualification()
    {
        return $this->belongsTo(Qualification::class);
    }

    public function specializations()
    {
        return $this->hasMany(Specialization::class);
    }

    public function boards()
    {
        return $this->hasMany(Board::class);
    }

    public function academicDetails()
{
    return $this->hasMany(AcademicDetail::class, 'ad_course_degree', 'id');
}

}
