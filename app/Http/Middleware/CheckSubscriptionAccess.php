<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Subscription;
use App\Models\Employee;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Super admin bypasses subscription check
        if (!$user || empty($user->emp_b_id)) {
            return $next($request);
        }

        // Fetch the latest subscription for this business
        $subscription = Subscription::where('business_id', $user->emp_b_id)
            ->latest()
            ->first();

        // Determine if the subscription is truly active
        $hasActiveSubscription = $subscription &&
            in_array($subscription->status, ['active', 'demo']) &&
            (is_null($subscription->end_date) || $subscription->end_date >= now());

        if (!$hasActiveSubscription) {
            // Determine the blocking reason/status to display
            $status = $this->resolveStatus($subscription);

            if ($request->ajax() || $request->wantsJson()) {
                $admin = Employee::select('emp_phone', 'emp_email')
                    ->where('emp_b_id', $user->emp_b_id)
                    ->where('emp_role_id', 1)
                    ->first();

                return response()->json([
                    'emp_email'  => $admin?->emp_email,
                    'emp_phone'  => $admin?->emp_phone,
                    'end_date'   => $subscription?->end_date?->format('d F Y'),
                    'status'     => $status,
                    'error'      => 'No active subscription found. Please contact your administrator.',
                ], 423);
            }

            return response()->view('subscription.access-denied', [
                'status'       => $status,
                'subscription' => $subscription,
            ], 423);
        }

        return $next($request);
    }

    /**
     * Resolve a normalised status string for display purposes.
     * Handles expired-by-date even when the DB status is still "active".
     */
    private function resolveStatus(?Subscription $subscription): string
    {
        if (!$subscription) {
            return 'no_subscription';
        }

        // Expired by date takes priority over the stored status
        if (
            $subscription->end_date &&
            $subscription->end_date < now() &&
            in_array($subscription->status, ['active', 'demo'])
        ) {
            return 'expired';
        }

        return match ($subscription->status) {
            'active', 'demo' => 'expired',       // active but end_date passed (fallback)
            'suspended'      => 'suspended',
            'deactivated'    => 'deactivated',
            'cancelled'      => 'cancelled',
            'expired'        => 'expired',
            default          => 'expired',
        };
    }
}