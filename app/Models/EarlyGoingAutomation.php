<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EarlyGoingAutomation extends Model
{
    // Table name (if not following Laravel's pluralization rule)
    protected $table = 'early_going_automation';

    // Primary key
    protected $primaryKey = 'ega_id';

    // No timestamps (since your table does not have created_at / updated_at)
    public $timestamps = true;

    // Mass assignable fields
    protected $fillable = [
        'ega_b_id',
        'ega_is_penalty_enabled',
        'ega_exit_before',
        'ega_penalty_amount',
        'ega_no_early',
        'ega_days_to_deduct',
    ];

    // Casts for proper data type handling
    protected $casts = [
        'ega_b_id' => 'integer',
        'ega_is_penalty_enabled' => 'boolean',
        'ega_exit_before' => 'datetime:H:i', // Time field
        'ega_penalty_amount' => 'float',
        'ega_no_early' => 'integer',
        'ega_days_to_deduct' => 'float',
    ];
}
