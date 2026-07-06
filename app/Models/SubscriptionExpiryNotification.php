<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionExpiryNotification extends Model
{
    protected $table = 'subscription_expiry_notifications';
    
    protected $fillable = [
        'subscription_id',
        'days_before',
        'notified_at',
        'dismissed_at',
    ];

    protected $casts = [
        'notified_at' => 'datetime',
        'dismissed_at' => 'datetime',
    ];

    /**
     * Get the subscription that owns the notification.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'subscription_id', 'sub_id');
    }
}