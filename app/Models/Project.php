<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'projects_setup';
    protected $primaryKey = 'ps_id';

    protected $fillable = [
        'ps_b_id',
        'ps_name',
        'ps_description',
    ];
}
