<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdhocTransactionDetail extends Model
{
    use HasFactory;

    protected $table = 'adhoc_transaction_details';
    protected $primaryKey = 'atd_id';  // ✅ important fix

    protected $fillable = [
        'adhoc_transaction_id',
        'component_id',
        'earning_amount',
        'deduction_amount',
        'remarks',
        'created_at',
        'updated_at',
    ];

    public function transaction()
    {
        return $this->belongsTo(AdhocTransaction::class, 'adhoc_transaction_id');
    }

    public function component()                   // each detail ↔ one component
    {
        return $this->belongsTo(
            AdhocComponent::class,
            'component_id',
            'ac_id'
        )->with('payrollHeading');
    }



}
