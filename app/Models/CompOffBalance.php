<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompOffBalance extends Model
{
    protected $table = 'compoff_balance';
    protected $primaryKey = 'cb_id';

    protected $casts = [
        'cb_b_id' => 'int',
        'cb_emp_id' => 'int',
        'cb_year' => 'int',
        'cb_month' => 'int',
        'cb_alloted' => 'decimal:2',
        'cb_taken' => 'decimal:2',
        'cb_expired' => 'decimal:2',
        'cb_balance_remaining' => 'decimal:2',
        'cb_carried_forward' => 'decimal:2',
        'cb_is_expiry' => 'boolean',
    ];

    protected $fillable = [
        'cb_b_id',
        'cb_emp_id',
        'cb_year',
        'cb_month',
        'cb_alloted',
        'cb_taken',
        'cb_expired',
        'cb_balance_remaining',
        'cb_carried_forward',
        'cb_is_expiry',
    ];

    public function fh_business()
    {
        return $this->belongsTo(Business::class, 'cb_b_id');
    }

    public function fh_employee()
    {
        return $this->belongsTo(Employee::class, 'cb_emp_id');
    }
}
