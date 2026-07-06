<?php

namespace App\Http\Middleware;

use App\Models\UserActivity;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LogUserActivity
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $activity = new UserActivity();
            $activity->ua_b_id = Auth::user()->emp_b_id;
            $activity->ua_emp_id = Auth::user()->emp_id;
            $activity->ua_activity = $request->getPathInfo();
            $activity->ua_ip_address = $request->ip();
            $activity->save();
        }

        return $next($request);
    }

}
