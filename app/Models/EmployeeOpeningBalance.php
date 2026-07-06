<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeOpeningBalance extends Model
{
    // Explicit table name if it doesn't follow Laravel naming convention
    protected $table = 'opening_balances';

    // Timestamps are enabled
    public $timestamps = true;

    // Mass assignable fields
    protected $fillable = [
        'eob_emp_id',
        'eob_b_id',
        'eob_cl',
        'eob_sl',
        'eob_el',
    ];

    /**
     * Get the employee who owns this opening balance.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'eob_emp_id', 'emp_id');
    }

    /**
     * Get the business this opening balance belongs to (optional).
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'eob_b_id', 'b_id');
    }

    /**
     * Accessor to get total leave (only if not using generated column).
     */
    public function getTotalLeaveAttribute(): int
    {
        return (int)($this->eob_cl + $this->eob_sl + $this->eob_el);
    }
}
