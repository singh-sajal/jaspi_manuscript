<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $guards = [
            'web',
            'admin'
        ];
        $currentGuard = null;
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $currentGuard = $guard;
                break;
            }
        }

        if ($currentGuard != null) {
            if ($currentGuard == 'web') {
                return redirect()->route('author.dashboard');
            }
            return redirect()->route('admin.dashboard');
        }
        return $next($request);
    }
}
