<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdhocTransaction extends Model
{
     protected $table = 'adhoc_transactions';

    protected $primaryKey = 'at_id';

    public $timestamps = true;

    protected $fillable = [
        'at_b_id',
        'at_emp_id',
        'at_emp_d_id',
        'at_pp_id',
        'at_e_amount',
        'at_d_amount',
    ];

    // Relationships (optional examples if applicable)
    public function payrollPeriod()
    {
        return $this->belongsTo(PayrollPeriod::class, 'at_pp_id', 'pp_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'at_emp_id', 'emp_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'at_emp_d_id', 'd_id');
    }

    public function adhocComponent()
    {
        return $this->belongsTo(AdhocComponent::class, 'at_b_id', 'ac_adhoc_business_id');
    }

    public function transaction_details()                     // one header → many detail rows
    {
        return $this->hasMany(
            AdhocTransactionDetail::class,
            'adhoc_transaction_id',
            'at_id'
        )->with('component');
    }
}
