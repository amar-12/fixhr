<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RemoveContentLength
{
    public function handle(Request $request, Closure $next)
    {
        // remove header
        $request->headers->remove('content-length');

        return $next($request);
    }
}
