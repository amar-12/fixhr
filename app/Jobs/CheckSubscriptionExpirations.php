<?php

namespace App\Jobs;

use App\Models\Subscription;
use App\Models\Business;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CheckSubscriptionExpirations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Log::info('🔔 Subscription expiration check job started');
        
        $today = now()->startOfDay();
        $fiveDaysFromNow = now()->addDays(5)->startOfDay();
        
        // Process subscriptions that will expire in 5 days
        $this->processExpiringSubscriptions($today, $fiveDaysFromNow);
        
        // Process subscriptions that have expired today
        $this->processExpiredSubscriptions($today);
        
        Log::info('✅ Subscription expiration check job completed');
    }

    private function processExpiringSubscriptions($today, $fiveDaysFromNow): void
    {
        $expiringSubscriptions = Subscription::whereIn('status', ['active', 'demo'])
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<=', $fiveDaysFromNow)
            ->whereDate('end_date', '>', $today)
            ->with(['business' => function ($query) {
                $query->select('b_id', 'b_name', 'b_unique_id');
            }, 'plan'])
            ->get();

        foreach ($expiringSubscriptions as $subscription) {
            try {
                $daysLeft = $today->diffInDays($subscription->end_date);
                
                // Create notification if not already exists for today
                $exists = $subscription->expiryNotifications()
                    ->whereDate('notified_at', $today)
                    ->where('days_before', $daysLeft)
                    ->exists();
                
                if (!$exists) {
                    $subscription->expiryNotifications()->create([
                        'days_before' => $daysLeft,
                        'notified_at' => $today,
                    ]);

                    Log::info('📅 Subscription expiring soon notification created', [
                        'subscription_id' => $subscription->sub_id,
                        'business' => $subscription->business->b_name,
                        'end_date' => $subscription->end_date->format('Y-m-d'),
                        'days_left' => $daysLeft,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('❌ Failed to create expiry notification', [
                    'subscription_id' => $subscription->sub_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function processExpiredSubscriptions($today): void
    {
        $expiredSubscriptions = Subscription::whereIn('status', ['active', 'demo'])
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<', $today)
            ->get();

        foreach ($expiredSubscriptions as $subscription) {
            DB::transaction(function () use ($subscription) {
                try {
                    // Update subscription status
                    $subscription->update([
                        'status' => 'deactivated',
                        'deactivated_at' => now(),
                    ]);

                    Log::info('🛑 Subscription deactivated due to expiry', [
                        'subscription_id' => $subscription->sub_id,
                        'business_id' => $subscription->business_id,
                        'end_date' => $subscription->end_date->format('Y-m-d'),
                    ]);

                    // Optional: You can add email notification here
                    // $this->sendExpiryEmail($subscription);

                } catch (\Exception $e) {
                    Log::error('❌ Failed to deactivate expired subscription', [
                        'subscription_id' => $subscription->sub_id,
                        'error' => $e->getMessage(),
                    ]);
                    throw $e;
                }
            });
        }
    }
}