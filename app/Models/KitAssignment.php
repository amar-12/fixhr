<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KitAssignment extends Model
{
    use HasFactory;

    protected $table = 'kit_assignments'; // Updated table name

    protected $fillable = [
        'b_id',
        'kit_id',
        'assigned_by',
        'assigned_to',
        'assigned_qty',
        'per_unit_price',
        'total_payable_amount',
        'assigned_date',
        'return_date',
        'return_qty',
        'lost_qty',
        'assigned_data',
        'return_condition',
        'status',
        'lost_reason',
        'lost_cost',
        'lost_remark',
        'remarks',
    ];


    public function fh_kit()
    {
        return $this->belongsTo(KitStock::class, 'kit_id', 'id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(Employee::class, 'assigned_to', 'emp_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(Employee::class, 'assigned_by', 'emp_id');
    }

    public function logs()
    {
        return $this->hasMany(KitLog::class, 'reference_id');
    }
}
