<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryHold extends Model
{
    protected $table = 'salary_holds';

    protected $primaryKey = 'sh_id';

    protected $casts = [
        'sh_held_at'     => 'datetime',
        'sh_released_at' => 'datetime',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];


    protected $fillable = [
        'sh_emp_id',
        'sh_b_id',
        'sh_dept_id',
        'sh_pp_id',
        'sh_status',
        'sh_reason',
        'sh_held_by',
        'sh_released_by',
        'sh_held_at',
        'sh_released_at',
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'sh_emp_id');
    }

    public function payrollPeriod()
    {
        return $this->belongsTo(PayrollPeriod::class, 'sh_pp_id');
    }

    public function heldBy()
    {
        return $this->belongsTo(Employee::class, 'sh_held_by');
    }

    public function releasedBy()
    {
        return $this->belongsTo(Employee::class, 'sh_released_by');
    }
}
