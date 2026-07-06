<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessBank extends Model
{
    protected $table = 'business_banks';
    protected $primaryKey = 'bb_id';

    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = true;

    // Fields allowed for mass assignment
    protected $fillable = [
        'bb_b_id',

        'bb_account_code',
        'bb_ifsc_code',
        'bb_bank_name',
        'bb_branch_name',
        'bb_micr',
        'bb_branch_code',
        'bb_bank_acc_no',
        'bb_account_type',
        'bb_bank_address',
        'bb_cheque_no',

        'bb_bank_status',
    ];

    // Type casting
    protected $casts = [
        'bb_b_id'        => 'integer',
        'bb_bank_status' => 'boolean',
    ];

    /**
     * Relationship with Business
     */
    public function business()
    {
        return $this->belongsTo(Business::class, 'bb_b_id', 'b_id');
    }
}
