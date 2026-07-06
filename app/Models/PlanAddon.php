<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanAddon extends Model
{
    protected $fillable = ['plan_id', 'name', 'amount', 'duration', 'is_one_time'];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}