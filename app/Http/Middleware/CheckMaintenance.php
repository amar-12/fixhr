<?php

namespace App\Http\Middleware;

use App\Models\Maintenance;
use Closure;

class CheckMaintenance
{
    public function handle($request, Closure $next)
    {
        $maintenance = Maintenance::find(1);

        if ($maintenance && $maintenance->status) {

            // auto turn off when time is finished
            if ($maintenance->end_at && now()->greaterThanOrEqualTo($maintenance->end_at)) {

                $maintenance->update([
                    'status' => false,
                    'end_at' => null,
                ]);

                return $next($request);
            }

            // allow admin area if you want
            if ($request->is('admin*')) {
                return $next($request);
            }

            return response()->view('subscription.maintenance', [
                'endAt' => $maintenance->end_at,
            ]);
        }

        return $next($request);
    }
}
