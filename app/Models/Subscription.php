<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use App\Models\Module;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    protected $table = 'subscriptions';
    protected $primaryKey = 'sub_id';
    protected $fillable = [
        'business_id',
        'plan_id',
        'start_date',
        'end_date',
        'trial_ends_at',
        'status',
        'payment_status',
        'price_slab_id',
        'price_per_user',
        'billing_cycle',
    ];
    protected $casts = [
        'start_date'    => 'datetime',
        'end_date'      => 'datetime',
        'trial_ends_at' => 'datetime',
    ];
    /* -------------------------
     | Relations
     * ------------------------- */
    public function business(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Business::class, 'business_id', 'b_id');
    }
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'plan_id');
    }
    /* -------------------------
     | Subscription state helpers
     * ------------------------- */
    /**
     * Determine whether the subscription is active.
     *
     * Rules applied (in order):
     * 1. If trial/demo period present (trial_ends_at) and status indicates trial/demo => active only while trial not passed.
     * 2. If end_date is present => active while now <= end_date and status is acceptable and payment not failed.
     * 3. If no end_date => fall back to status + payment_status.
     *
     * Accepts common status values: 'active', 'trial', 'demo', numeric 1.
     * Treats payment_status 'failed' as inactive.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        $now = Carbon::now();

        // Normalize status for safe comparisons
        $status = strtolower((string) ($this->status ?? ''));

        // Trial / Demo logic
        if ($this->trial_ends_at && in_array($status, ['demo', 'trial'])) {
            $trialEnds = Carbon::parse($this->trial_ends_at);

            $active = $now->lte($trialEnds);

            if (! $active) {
                Log::info('Subscription trial/demo expired', [
                    'sub_id' => $this->getKey(),
                    'trial_ends_at' => $this->trial_ends_at,
                    'now' => $now->toDateTimeString(),
                ]);
            }

            return $active;
        }

        // If end_date exists, check validity
        if ($this->end_date) {
            $end = Carbon::parse($this->end_date);

            $statusOk = in_array($status, ['active', '1', 'paid', 'demo', 'trial']);
            $active = $now->lte($end) && $statusOk;

            if (! $active) {
                Log::info('Subscription inactive by date or status', [
                    'sub_id' => $this->getKey(),
                    'end_date' => $this->end_date,
                    'now' => $now->toDateTimeString(),
                    'status' => $this->status,
                ]);
            }

            return $active;
        }

        // Fallback: only status-based
        $fallbackActive = in_array($status, ['active', '1', 'paid']);

        if (! $fallbackActive) {
            Log::info('Subscription fallback inactive', [
                'sub_id' => $this->getKey(),
                'status' => $this->status,
            ]);
        }

        return $fallbackActive;
    }

    /**
     * Returns true when subscription is expired (i.e., not active and end_date is past).
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        $now = Carbon::now();
        if ($this->end_date) {
            return Carbon::parse($this->end_date)->lt($now);
        }
        // If no end_date but status/payment indicate inactive — consider expired.
        return ! $this->isActive();
    }
    /**
     * Check if subscription is currently in trial/demo period.
     *
     * @return bool
     */
    public function isInTrial(): bool
    {
        if (! $this->trial_ends_at) {
            return false;
        }
        $now = Carbon::now();
        return $now->lte(Carbon::parse($this->trial_ends_at));
    }
    /**
     * Days left until subscription end (returns null if no end_date).
     *
     * @return int|null
     */
    public function daysLeft(): ?int
    {
        if (! $this->end_date) {
            return null;
        }
        $now = Carbon::now();
        $end = Carbon::parse($this->end_date);
        if ($end->lt($now)) {
            return 0;
        }
        return $now->diffInDays($end);
    }
    /* -------------------------
     | Boot & syncing helpers
     * ------------------------- */
    protected static function booted()
    {
        static::created(function ($subscription) {
            self::syncModules($subscription);
        });
        static::updated(function ($subscription) {
            if ($subscription->wasChanged('plan_id')) {
                self::syncModules($subscription, true);
            }
        });
    }
    /**
     * Sync plan's modules to the business when subscription is created/changed.
     *
     * @param self $subscription
     * @param bool $clearOld
     * @return void
     */
    protected static function syncModules(self $subscription, bool $clearOld = false): void
    {
        try {
            // refresh relations to be safe
            $subscription = $subscription->fresh(['business', 'plan']);
            if (! $subscription) {
                Log::warning('Subscription not found for syncModules', [
                    'subscription_id' => $subscription->sub_id ?? null,
                ]);
                return;
            }
            $business = $subscription->business;
            if (! $business) {
                Log::warning('Subscription has no business to sync modules for', [
                    'subscription_id' => $subscription->sub_id ?? null,
                    'business_id' => $subscription->business_id ?? null,
                ]);
                return;
            }
            $plan = $subscription->plan;
            if (! $plan) {
                Log::warning('Subscription has no plan to sync modules for', [
                    'subscription_id' => $subscription->sub_id ?? null,
                    'plan_id' => $subscription->plan_id ?? null,
                ]);
                return;
            }
            $modules = $plan->included_modules ?? [];
            if ($clearOld) {
                if (method_exists($business, 'business_module_accesses')) {
                    $business->business_module_accesses()->delete();
                } else {
                    Log::warning('Business model missing business_module_accesses relation', [
                        'business_id' => $business->getKey(),
                    ]);
                }
            }
            foreach ($modules as $code) {
                $module = Module::where('mdl_code', $code)->first();
                if (! $module) {
                    Log::warning('Module code not found while syncing subscription', [
                        'subscription_id' => $subscription->sub_id ?? null,
                        'module_code' => $code,
                    ]);
                    continue;
                }
                if (! method_exists($business, 'business_module_accesses')) {
                    Log::error('Cannot assign module, business relation not found', [
                        'business_id' => $business->getKey(),
                        'module_id' => $module->mdl_id,
                    ]);
                    continue;
                }
                $business->business_module_accesses()->updateOrCreate(
                    ['bma_mdl_id' => $module->mdl_id],
                    ['bma_access' => true]
                );
            }
        } catch (\Exception $e) {
            Log::error('Error syncing modules for subscription', [
                'subscription_id' => $subscription->sub_id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }
    /* -------------------------
     | Utility
     * ------------------------- */
    /**
     * Return current monthly amount for plan (keeps your original helper)
     */
    public function getCurrentMonthlyAmount(): float
    {
        if (! $this->business || ! $this->plan) {
            return 0.0;
        }
        $employeeCount = $this->business->employees()->where('emp_status', 1)->count();
        $perUserPrice = $this->plan->getPriceForEmployees($employeeCount);
        return round($perUserPrice * $employeeCount, 2);
    }
    /**
     * Get the expiry notifications for the subscription.
     */
    public function expiryNotifications(): HasMany
    {
        return $this->hasMany(SubscriptionExpiryNotification::class, 'subscription_id', 'sub_id');
    }
    /**
     * Check if subscription is expiring soon (5 days or less)
     */
    public function isExpiringSoon(): bool
    {
        if (!$this->end_date) {
            return false;
        }
        $daysLeft = $this->daysLeft();
        return $daysLeft !== null && $daysLeft <= 5 && $daysLeft > 0;
    }
    /**
     * Get formatted expiry message
     */
    public function getExpiryMessage(): string
    {
        $daysLeft = $this->daysLeft();
        if ($daysLeft === 0) {
            return "Your subscription expires today!";
        } elseif ($daysLeft === 1) {
            return "Your subscription expires tomorrow!";
        } else {
            return "Your subscription expires in {$daysLeft} days.";
        }
    }
}
