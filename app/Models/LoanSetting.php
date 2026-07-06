<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanSetting extends Model
{
    protected $table = 'loan_settings';

    protected $fillable = [
        'ls_id',
        'ls_b_id',
        'ls_limit_type',
        'ls_fixed_limit',
        'ls_percentage_limit',
        'ls_permanent_only',
        'ls_min_employment',
        'ls_enable_interest',
        'ls_interest_rate',
        'ls_interest_type',
        'ls_interest_scope',
        'ls_max_concurrent',
        'ls_min_repayment',
    ];

    protected $casts = [
        'permanent_only' => 'boolean',
        'enable_interest' => 'boolean',
        'fixed_limit' => 'float',
        'percentage_limit' => 'float',
        'interest_rate' => 'float',
    ];

    public function fh_business()
	{
		return $this->belongsTo(Business::class, 'phl_b_id');
	}
}
