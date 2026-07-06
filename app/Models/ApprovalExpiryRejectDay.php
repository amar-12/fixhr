<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalExpiryRejectDay extends Model
{
    protected $table = 'approval_expiry_reject_day';

    protected $primaryKey = 'aer_id';

    public $timestamps = true;

    protected $fillable = [
        'aer_b_id',
        'aer_m_id',
        'aer_day',
        'aer_noti_day',
    ];

    /**
     * Optional: If you want casting
     */
    protected $casts = [
        'aer_b_id' => 'integer',
        'aer_m_id' => 'integer',
        'aer_day'  => 'integer',
        'aer_noti_day'  => 'integer',
    ];

    /**
     * Optional: Relationships (if needed)
     */

    // Business Relation
    public function business()
    {
        return $this->belongsTo(Business::class, 'aer_b_id');
    }

    // Module Relation
    public function module()
    {
        return $this->belongsTo(MasterTable::class, 'aer_m_id');
    }
}
