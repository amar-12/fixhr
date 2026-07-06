<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Feedback extends Model
{

    use HasFactory;

    protected $table = 'feedbacks';
    protected $primaryKey = 'f_id';



    protected $fillable = [
        'f_b_id',
        'f_emp_id',
        'f_title',
        'f_description',
        'f_attachment'
    ];  


 public function employee()
    {
        return $this->belongsTo(Employee::class, 'f_emp_id', 'emp_id');
    }

    public function business()
    {
        return $this->belongsTo(Business::class, 'f_b_id', 'b_id');
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
