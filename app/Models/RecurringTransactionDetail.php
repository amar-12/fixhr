<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RecurringTransactionDetail extends Model
{
    use HasFactory;

    protected $table = 'recurring_transaction_details';
    protected $primaryKey = 'rtd_id';  // Primary Key

    protected $fillable = [
        'rtd_recurring_transaction_id',
        'rtd_component_id',
        'rtd_earning_amount',
        'rtd_deduction_amount',
        'rtd_remarks',
        'created_at',
        'updated_at',
    ];

    /**
     * Relationship: Detail belongs to main Recurring Transaction
     */
    public function transaction()
    {
        return $this->belongsTo(
            RecurringTransaction::class,
            'rtd_recurring_transaction_id',
            'rt_id'
        );
    }

    /**
     * Relationship: Detail belongs to one component
     */
    public function component()
    {
        return $this->belongsTo(
            AdhocComponent::class, // or AdhocComponent if same
            'rtd_component_id',
            'ac_id' // change according to your component table PK
        );
    }

    public function recurringTransaction()
    {
        return $this->belongsTo(RecurringTransaction::class, 'rtd_recurring_transaction_id', 'rt_id');
    }
    
}

