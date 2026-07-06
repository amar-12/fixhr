<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecurringTransaction extends Model
{
   
    protected $table = 'recurring_transactions';
    protected $primaryKey = 'rt_id';  // use new ID column

    public $timestamps = true;

    protected $fillable = [
        'rt_emp_id',          
        'rt_emp_d_id',        
        'rt_b_id',            
        'rt_type',            
        'rt_component_id',  
        'rt_e_amount',
        'rt_d_amount',  
        'rt_amount',          
        'rt_start_month',     
        'rt_end_month',       
        'rt_is_active',      
        'rt_created_by',
    ];

    /* ========================
       RELATIONSHIPS
    ======================== */

    // Employee
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'rt_emp_id', 'emp_id');
    }

    // Department (optional)
    public function department()
    {
        return $this->belongsTo(Department::class, 'rt_emp_d_id', 'd_id');
    }

    // Component (earning/deduction)
    public function component()
    {
        return $this->belongsTo(AdhocComponent::class, 'rt_component_id', 'ac_id');
    }

    public function recurringDetails()
    {
        return $this->hasMany(RecurringTransactionDetail::class, 'rtd_recurring_transaction_id', 'rt_id');
    }

}
