<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use App\Models\Admin;
use App\Models\Author;
use App\Models\Consultant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class EnsureVerification
{
    public function handle(Request $request, Closure $next, $guard)
    {
        // Check if the guard is valid
        if (!in_array($guard, ['admin', 'web'])) {
            return redirect()->route('admin.login')->withErrors(['error' => 'Invalid guard type.']);
        }

        // Determine the model based on the guard
        $modelClass = $this->getModelClassForGuard($guard);
        $redirectUrl = $this->getRedirectUrlForGuard($guard);
        $user = $modelClass::where('uuid', $request->uuid)->first();

        if (!$user) {
            return redirect()->route($redirectUrl)->withErrors(['error' => 'User not found.']);
        }

        // Check if the verification code exists in the session
        $sessionKey = "login_verification_code_{$user->uuid}";
        $verificationCode = session()->get($sessionKey);

        if (!$verificationCode) {
            return redirect()->route($redirectUrl)->withErrors(['error' => 'Verification code has expired or is invalid.']);
        }

        return $next($request);
    }

    /**
     * Get the model class based on the guard.
     *
     * @param string $guard
     * @return string|null
     */
    protected function getModelClassForGuard($guard)
    {
        switch ($guard) {
            case 'admin':
                return Admin::class;
            case 'web':
                return Author::class;
            default:
                return null;
        }
    }

    /**
     * Get the redirect URL based on the guard.
     *
     * @param string $guard
     * @return string
     */
    protected function getRedirectUrlForGuard($guard)
    {
        switch ($guard) {
            case 'admin':
                return 'admin.login';
            case 'web':
                return 'author.login';
            default:
                return 'admin.login';
        }
    }
}
