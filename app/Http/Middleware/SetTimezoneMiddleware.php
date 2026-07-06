<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetTimezoneMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // if(Auth::check()){
        //     $businesTimeZone = optional(optional(Auth::user()->fh_business)->fh_timezone);
        //     if ($businesTimeZone && $businesTimeZone->zone_code) {
        //         config(['app.timezone' => $businesTimeZone->zone_code]);
        //         date_default_timezone_set($businesTimeZone->zone_code);
        //     }
        // }
        return $next($request); // this for to prevent break of application when error related to timezone
    }

}
