<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckEmployeeStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if the authenticated user's emp_status is 72 (inactive)
        $user = Auth::user();
        if ($user && $user->emp_status === 72) {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['error' => 'User is inactive.'], 403);
        }

        return $next($request);
    }
}
