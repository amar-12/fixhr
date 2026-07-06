<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class TwoFactorMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if ($user && !empty($user->emp_google2fa_secret) && !empty($user->emp_google2fa_enabled_at)) {
            if (!Session::get('2fa_passed') && !$this->is2faRoute($request)) {
                return redirect()->route('2fa.verify.form');
            }
        }
        return $next($request);
    }

    private function is2faRoute(Request $request)
    {
        $routeNames = [
            '2fa.verify.form',
            '2fa.verify',
            '2fa.setup',
            '2fa.enable',
        ];
        return in_array($request->route()->getName(), $routeNames);
    }
} 