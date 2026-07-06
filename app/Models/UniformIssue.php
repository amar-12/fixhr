<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UniformIssue extends Model
{
    use HasFactory;

    protected $table = 'uniform_issues';
    protected $primaryKey = 'ui_id';

    protected $fillable = [
        'ui_issued_by',
        'ui_b_id',
        'ui_total',
        'ui_payable',
        'ui_waived',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'ui_issued_by', 'emp_id');
    }

    public function items()
    {
        return $this->hasMany(UniformItem::class, 'uit_issue_id', 'ui_id');
    }

}
