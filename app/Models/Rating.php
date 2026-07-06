<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rating extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ratings';
    protected $primaryKey = 'rat_id';

    protected $fillable = [
        'rat_b_id',
        'rat_emp_id',
        'rat_type',
        'rat_count',
        'rat_remark',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function fh_business()
    {
        return $this->belongsTo(Business::class, 'rat_b_id');
    }

    public function fh_employee()
    {
        return $this->belongsTo(Employee::class, 'rat_emp_id');
    }
}
