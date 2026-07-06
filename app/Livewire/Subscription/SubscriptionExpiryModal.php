<?php

namespace App\Livewire\Subscription;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Subscription;
use Carbon\Carbon;

class SubscriptionExpiryModal extends Component
{
    public $showModal = false;
    public $subscription = null;
    public $daysLeft = 0;
    public $endDate = '';
    public $planName = '';
    public $notificationType = 'info'; // info, warning, danger
    public $businessName = '';
    public $progressPercentage = 100;
    public $showRenewButton = true;
    
    // Modal states
    public $isClosing = false;
    
    // Countdown
    public $countdownSeconds = 30;

    public function mount()
    {
        $this->checkSubscriptionExpiry();
    }

    public function checkSubscriptionExpiry()
    {
        $user = Auth::user();
        
        if (!$user || empty($user->emp_b_id)) {
            return;
        }

        // Get active subscription for the business
        $subscription = Subscription::where('business_id', $user->emp_b_id)
            ->whereIn('status', ['active', 'demo'])
            ->whereNotNull('end_date')
            ->where('end_date', '>=', now()->startOfDay())
            ->with(['expiryNotifications' => function ($query) {
                $query->whereNull('dismissed_at')
                      ->orderBy('created_at', 'desc')
                      ->limit(1);
            }])
            ->with(['plan', 'business'])
            ->first();

        if (!$subscription) {
            return;
        }

        $daysLeft = now()->startOfDay()->diffInDays($subscription->end_date);
        
        // Only show if 5 days or less remaining
        if ($daysLeft <= 5) {
            $this->subscription = $subscription;
            $this->daysLeft = $daysLeft;
            $this->endDate = $subscription->end_date->format('F j, Y');
            $this->planName = $subscription->plan->name ?? 'Your Plan';
            $this->businessName = $subscription->business->b_name ?? 'Your Business';
            $this->notificationType = $this->getNotificationType($daysLeft);
            $this->progressPercentage = max(10, min(100, (5 - $daysLeft) / 5 * 100));
            $this->showRenewButton = $daysLeft <= 3;
            
            // Check if we should show notification (not dismissed today)
            $shouldShow = $this->shouldShowNotification($subscription, $daysLeft);
            
            if ($shouldShow) {
                $this->showModal = true;
            }
        }
    }

    private function getNotificationType($daysLeft): string
    {
        if ($daysLeft === 0) {
            return 'danger'; // Expiring today - RED
        } elseif ($daysLeft === 1) {
            return 'danger'; // Expiring tomorrow - RED
        } elseif ($daysLeft <= 3) {
            return 'warning'; // Expiring soon - ORANGE
        } else {
            return 'info'; // Expiring in 4-5 days - BLUE
        }
    }

    private function shouldShowNotification($subscription, $daysLeft): bool
    {
        // Check if notification was already dismissed today
        $today = now()->startOfDay();
        $dismissedToday = $subscription->expiryNotifications()
            ->whereNotNull('dismissed_at')
            ->whereDate('dismissed_at', $today)
            ->exists();
        
        return !$dismissedToday;
    }

    public function closeModal()
    {
        $this->isClosing = true;
        
        // Mark as dismissed if we have a subscription
        if ($this->subscription) {
            // Find the latest undismissed notification
            $notification = $this->subscription->expiryNotifications()
                ->whereNull('dismissed_at')
                ->orderBy('created_at', 'desc')
                ->first();
                
            if ($notification) {
                $notification->update(['dismissed_at' => now()]);
            }
        }
        
        $this->showModal = false;
        $this->isClosing = false;
    }

    public function renewNow()
    {
        $this->closeModal();
        
        // Redirect to renewal page
        return redirect()->route('subscription.renew', ['id' => $this->subscription->sub_id]);
    }

    public function remindLater()
    {
        // Create or update notification record for later reminder
        if ($this->subscription) {
            $notification = $this->subscription->expiryNotifications()
                ->whereNull('dismissed_at')
                ->orderBy('created_at', 'desc')
                ->first();
            
            if ($notification) {
                $notification->update([
                    'dismissed_at' => now()->addHours(12), // Remind again in 12 hours
                ]);
            }
        }
        
        $this->closeModal();
    }

    public function contactSupport()
    {
        $this->closeModal();
        $this->dispatch('show-contact-support');
    }

    public function getNotificationTitle(): string
    {
        if ($this->daysLeft === 0) {
            return '⚠️ Subscription Expires Today!';
        } elseif ($this->daysLeft === 1) {
            return '⚠️ Subscription Expires Tomorrow!';
        } elseif ($this->daysLeft <= 3) {
            return '⚠️ Subscription Expiring Soon';
        } else {
            return 'ℹ️ Subscription Expiry Notice';
        }
    }

    public function getNotificationMessage(): string
    {
        if ($this->daysLeft === 0) {
            return "Your <strong>{$this->planName}</strong> subscription for <strong>{$this->businessName}</strong> expires <strong>today</strong>. Renew immediately to avoid service interruption.";
        } elseif ($this->daysLeft === 1) {
            return "Your <strong>{$this->planName}</strong> subscription for <strong>{$this->businessName}</strong> expires <strong>tomorrow</strong>. Renew now to continue uninterrupted service.";
        } elseif ($this->daysLeft <= 3) {
            return "Your <strong>{$this->planName}</strong> subscription for <strong>{$this->businessName}</strong> will expire in <strong>{$this->daysLeft} days</strong>. Renew to avoid service disruption.";
        } else {
            return "Your <strong>{$this->planName}</strong> subscription for <strong>{$this->businessName}</strong> will expire on <strong>{$this->endDate}</strong> ({$this->daysLeft} days remaining).";
        }
    }

    public function getModalStyles(): array
    {
        $styles = [
            'info' => [
                'header_bg' => 'bg-primary bg-opacity-10',
                'header_text' => 'text-primary',
                'border' => 'border-primary',
                'icon' => 'bi-info-circle-fill text-primary',
                'button' => 'btn-primary',
                'progress' => 'bg-primary',
            ],
            'warning' => [
                'header_bg' => 'bg-warning bg-opacity-10',
                'header_text' => 'text-warning',
                'border' => 'border-warning',
                'icon' => 'bi-exclamation-triangle-fill text-warning',
                'button' => 'btn-warning',
                'progress' => 'bg-warning',
            ],
            'danger' => [
                'header_bg' => 'bg-danger bg-opacity-10',
                'header_text' => 'text-danger',
                'border' => 'border-danger',
                'icon' => 'bi-exclamation-octagon-fill text-danger',
                'button' => 'btn-danger',
                'progress' => 'bg-danger',
            ],
        ];
        
        return $styles[$this->notificationType] ?? $styles['info'];
    }

    public function render()
    {
        return view('livewire.subscription.subscription-expiry-modal');
    }
}