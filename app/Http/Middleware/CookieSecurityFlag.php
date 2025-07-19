<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie as LaravelCookie;

class CookieSecurityFlag
{
    public function handle(Request $request, Closure $next)
    {
        $cookies = $request->cookies->all();

        foreach ($cookies as $name => $cookie) {

            $expiration = now()->addMinutes(config('session.lifetime'))->timestamp;

            $response = $next($request);

            $response->headers->setCookie(
                LaravelCookie::make($name, $cookie, $expiration, '/', 'yourdomain.com', true, true)
            );

        }

        return $next($request);
    }
}
