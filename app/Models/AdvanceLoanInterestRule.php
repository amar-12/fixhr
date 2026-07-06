<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdvanceLoanInterestRule extends Model
{
    use HasFactory;

    protected $table = 'advance_loan_interest_rules';
    protected $primaryKey = 'alir_id';
    public $timestamps = true;

    protected $fillable = [
        'alir_b_id',
        'alir_als_id',
        'alir_rule_type',
        'alir_param1',
        'alir_param2',
        'alir_rule_rate'
    ];

    public function loanSetting()
    {
        // ✅ relation corrected: use alir_als_id as FK
        return $this->belongsTo(AdvanceLoanSetting::class, 'alir_als_id', 'als_id');
    }
}
