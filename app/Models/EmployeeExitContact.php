<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeExitContact extends Model
{
    use HasFactory;

    protected $table = 'employee_exit_contacts';

    protected $fillable = [
        'ee_role_id',
        'ee_emp_id',
        'ee_b_id',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'ee_role_id', 'role_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'ee_emp_id', 'emp_id');
    }

    public function business()
    {
        return $this->belongsTo(Business::class, 'ee_b_id', 'b_id');
    }
}