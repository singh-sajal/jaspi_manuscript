<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAccountIsActive
{
    /**
     * Guards that can hold an authenticated account.
     *
     * @var array<int, string>
     */
    protected $guards = [
        'web',
        'admin',
    ];

    /**
     * Log out an already authenticated account as soon as it has been disabled.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        foreach ($this->guards as $guard) {
            $user = Auth::guard($guard)->user();

            if (!$user || $this->isActive($user)) {
                continue;
            }

            Auth::guard($guard)->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = 'Your account has been disabled. Please contact the administrator.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'status' => '403',
                    'msg' => $message,
                ], 403);
            }

            $loginRoute = $guard === 'admin' ? 'admin.login' : 'author.login';

            return redirect()->route($loginRoute)->withErrors(['account' => $message]);
        }

        return $next($request);
    }

    /**
     * Determine whether the account's status flag is enabled.
     *
     * @param  mixed  $user
     * @return bool
     */
    protected function isActive($user)
    {
        return filter_var($user->status, FILTER_VALIDATE_BOOLEAN);
    }
}
