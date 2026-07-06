<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicDetail extends Model
{
    protected $table = 'academic_details';

    protected $primaryKey = 'ad_id';


    protected $fillable = [
        'ad_emp_id',
        'ad_b_id',
        'ad_issued_by',
        'ad_qua_id',
        'ad_course_degree',
        'ad_specialization',
        'ad_university_board',
        'ad_institute_name',
        'ad_year_of_passing',
        'ad_marks_type',
        'ad_marks_obtained',
        'ad_document_upload',
        'ad_status',
        'created_at',
        'updated_at',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'ad_emp_id', 'emp_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'ad_b_id');
    }


    public function board()
    {
        return $this->belongsTo(Board::class, 'ad_university_board', 'id');
    }

    public function qualification()
    {
        return $this->belongsTo(EmpQualification::class, 'ad_qua_id', 'id');
    }

    public function courseDegree()
    {
        return $this->belongsTo(CourseDegree::class, 'ad_course_degree', 'id');
    }

    public function specialization()
    {
        return $this->belongsTo(Specialization::class, 'ad_specialization', 'id');
    }
}
