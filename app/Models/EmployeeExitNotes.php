<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeExitNotes extends Model
{
    use HasFactory;

    protected $table = 'employee_exits_notes';

    protected $fillable = [
        'b_id',
        'notes',
        'signature'
    ];

    protected $casts = [
        'notes' => 'string',
        'signature' => 'string'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'emp_id', 'id');
    }
}
