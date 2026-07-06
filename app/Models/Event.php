<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'events';
    protected $primaryKey = 'ev_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
    	'ev_emp_id',
        'ev_b_id',
        'ev_title',
        'ev_description',
        'ev_images',
    ];
    protected $dates = ['deleted_at'];

    // Accessor to get images as array
    public function getImagesArrayAttribute()
    {
        return $this->images ? explode(',', $this->images) : [];
    }

    // Employee relation
    public function fh_employee()
    {
        return $this->belongsTo(Employee::class, 'ev_emp_id', 'emp_id');
    }

    // Business relation
    public function fh_business()
    {
        return $this->belongsTo(Business::class, 'ev_b_id', 'b_id');
    }
}
