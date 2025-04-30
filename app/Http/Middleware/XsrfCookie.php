<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

class XsrfCookie
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $token = $request->session()->token();

        $response->headers->setCookie(
            new Cookie('XSRF-TOKEN', $token, 0, '/', 'taskforge.local', false, false)
        );

        return $response;
    }
}