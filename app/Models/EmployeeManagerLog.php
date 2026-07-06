<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeManagerLog extends Model
{
    use HasFactory;

    protected $table = 'employee_manager_logs';
    protected $primaryKey = 'eml_id';

    protected $fillable = [
        'eml_b_id',
        'eml_form_type',
        'eml_old_manager_id',
        'eml_new_manager_id',
        'eml_wef_date',
        'eml_reason',
        'eml_applied'
    ];
}
