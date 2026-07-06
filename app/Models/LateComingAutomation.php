<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LateComingAutomation extends Model
{
    // Table name (custom, inferred from lca_ prefix)
    protected $table = 'late_coming_automation';

    // Primary key
    protected $primaryKey = 'lca_id';

    // Timestamps are present (created_at, updated_at)
    public $timestamps = true;

    // Mass assignable fields
    protected $fillable = [
        'lca_b_id',
        'lca_is_penalty_enabled',
        'lca_late_till',
        'lca_penalty_amount',
        'lca_no_late',
        'lca_days_to_deduct',
    ];

    // Casts for correct data types
    protected $casts = [
        'lca_b_id' => 'integer',
        'lca_is_penalty_enabled' => 'boolean',
        'lca_late_till' => 'datetime:H:i',   // TIME column
        'lca_penalty_amount' => 'float',
        'lca_no_late' => 'integer',
        'lca_days_to_deduct' => 'float',
    ];
}
