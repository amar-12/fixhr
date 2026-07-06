<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollLoanInstallment extends Model
{
    protected $table = 'payroll_loan_installments';

    protected $primaryKey = 'pli_id'; // <-- make sure this matches your real PK column name

    public $incrementing = true; // or false if not auto-incrementing
    protected $keyType = 'int';

    // Allow mass assignment on these fields
    protected $fillable = [
        'pli_id',
        'pli_b_id',
        'pli_loan_id',
        'pli_installment_no',
        'pli_month',
        'pli_year',
        'pli_opening_balance', // ✅ NEW
        'pli_principal',       // ✅ principal part
        'pli_interest',        // ✅ interest part
        'pli_amount',          // (Total EMI = principal+interest)
        'pli_rem_bal',
        'pli_due_date',
        'pli_status',
    ];

    // Enable timestamps (created_at, updated_at)
    public $timestamps = true;

    // Define relationship if needed (optional)
    public function loan()
    {
        return $this->belongsTo(PayrollLoanAccount::class, 'pli_loan_id');
    }


    public function month_name()
    {
        return $this->belongsTo(MasterTable::class, 'pli_month', 'm_id');
    }




}
