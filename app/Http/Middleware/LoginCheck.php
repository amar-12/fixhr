<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class LoginCheck
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
	 */
	public function handle(Request $request, Closure $next): Response
	{

		$business = Auth::user()?->fh_business;

		if (
			$business &&
			!$business->demo_setup_completed &&
			!$request->routeIs('demo.setup.*')
		) {
			return redirect()->route('demo.setup.start');
		}
		
		if (Auth::check()) {
			return redirect('/dashboard');
		}
		return $next($request);
	}
}
