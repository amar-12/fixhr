<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenaltyLog extends Model
{
    use HasFactory;

    protected $table = 'penalty_log';
    protected $primaryKey = 'pl_id';
    public $timestamps = true;

    protected $fillable = [
        'pl_b_id',
        'pl_ar_id',
        'pl_emp_salary',
        'pl_late_time1',
        'pl_late_amount1',
        'pl_late_time2',
        'pl_late_amount2',
    ];

    protected $casts = [
        'pl_late_time1' => 'datetime',
        'pl_late_time2' => 'datetime',
        'pl_emp_salary' => 'decimal:2',
        'pl_late_amount1' => 'decimal:2',
        'pl_late_amount2' => 'decimal:2',
    ];

    /**
     * Example relationships
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'pl_b_id', 'id');
    }

    public function attendanceRecord()
    {
        return $this->belongsTo(AttendanceRecord::class, 'pl_ar_id', 'id');
    }
}
