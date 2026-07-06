<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveOpeningBalance extends Model
{
    protected $table = 'leave_opening_balance';
    protected $primaryKey = 'lob_id';

    protected $casts = [
        'lob_b_id' => 'int',
        'lob_emp_id' => 'int',
        'lob_leave_type_id' => 'int',
        'lob_previous' => 'decimal:2',
        'lob_updated' => 'decimal:2',
        'lob_total' => 'decimal:2',
        'updated_by' => 'int',
    ];

    protected $fillable = [
        'lob_b_id',
        'lob_emp_id',
        'lob_leave_type_id',
        'lob_previous',
        'lob_updated',
        'lob_total',
        'updated_by',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'lob_emp_id', 'emp_id');
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'lob_leave_type_id', 'lt_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(Employee::class, 'updated_by', 'emp_id');
    }

    public function business()
    {
        return $this->belongsTo(Business::class, 'lob_b_id', 'b_id');
    }
}
