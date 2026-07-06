<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilyDetail extends Model
{
    use HasFactory;

    protected $table = 'family_details';
    protected $primaryKey = 'fd_id';

    protected $fillable = [
        'fd_emp_id',
        'fd_b_id',
        'fd_name',
        'fd_relation',
        'fd_dob',
        'fd_dependency',
        'fd_occupation',
        'fd_contact',
    ];

    /**
     * Relationship to Employee
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'fd_emp_id', 'emp_id');
    }

    /**
     * Relationship to Branch
     */
    public function branch()
    {
        return $this->belongsTo( Business::class, 'fd_b_id', 'b_id');
    }
}
