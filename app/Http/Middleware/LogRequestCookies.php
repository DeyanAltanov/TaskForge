<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogRequestCookies
{
    public function handle(Request $request, Closure $next)
    {
        Log::channel('taskforge')->debug('🍪 Incoming Request Cookies', [
            'cookies' => $request->cookies->all(),
            'session_id' => session()->getId(),
        ]);

        return $next($request);
    }
}