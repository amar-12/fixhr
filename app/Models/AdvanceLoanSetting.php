<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvanceLoanSetting extends Model
{
    protected $table = 'advance_loan_settings';

    protected $primaryKey = 'als_id';
    public $incrementing = true;
    protected $keyType = 'int';

    // Timestamps
    public $timestamps = true;

    protected $fillable = [
        'als_b_id',
        'als_loan_advance_name',
        'als_limit_type',
        'als_fixed_limit',
        'als_percentage_limit',
        'als_permanent_only',
        'als_min_employment',
        'als_enable_age_criteria',
        'als_max_age',

        // ✅ Main Interest Settings
        'als_apply_interest',
        'als_interest_rate',
        'als_interest_scope',

        // ✅ Extra Interest Rules
        'als_interest_exceed_installments',
        'als_interest_exceed_rate',

        'als_interest_if_loan_multiplier',
        'als_interest_if_loan_months',
        'als_interest_if_loan_rate',

        'als_interest_if_loan_greater_multiplier',
        'als_interest_if_loan_greater_months',
        'als_interest_if_loan_greater_rate',

        // Other Restrictions
        'als_max_concurrent',
        'als_min_repayment',

        // Status
        'als_status',
    ];

    public function fh_business()
    {
        return $this->belongsTo(Business::class, 'als_b_id', 'b_id');
    }

     public function interestRules()
    {
        return $this->hasMany(AdvanceLoanInterestRule::class, 'alir_als_id', 'als_id');
    }
}
