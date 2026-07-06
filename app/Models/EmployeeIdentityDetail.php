<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeIdentityDetail extends Model
{
    protected $table = 'employee_identity_details';

    protected $primaryKey = 'id';

    protected $fillable = [
        'emp_id',
        'emp_b_id',
        'aadhar_number',
        'driving_license_number',
        'election_card_number',
        'passport_number',
        'pan_number',
        'bank_ac_number',
    ];

    /**
     * Relationship: Employee
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'emp_id', 'emp_id');
    }

    /**
     * Relationship: Business
     */
    public function business()
    {
        return $this->belongsTo(Business::class, 'emp_b_id', 'b_id');
    }
}
