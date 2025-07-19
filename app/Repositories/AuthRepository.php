<?php

namespace App\Repositories;

use Throwable;
use App\Models\User;
use Illuminate\Support\Str;
use App\Traits\HasIpBlocker;
use App\Traits\HasVerificationCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Testing\Fluent\Concerns\Has;
use Illuminate\Support\Facades\Notification;
use App\Notifications\VerificationNotification;

abstract class AuthRepository
{
    use HasIpBlocker, HasVerificationCode;
    protected $model = User::class;
    protected $guard = 'web';
    protected $maxAttempts = 5;
    protected $_2FaView = 'user.auth.2fa';
    protected $_2FaCallbackUrl = 'user.2fa.login';
    protected $loginSuccessRedirectUrl = 'user.dashboard';
    protected $loginUrl = 'login';
    protected $codeLength = 4;


    public function __construct()
    {
        // Constructor
    }



    protected function processLoginRequest($request)
    {
        try {
            $ipAddress = $request->ip();

            $field = filter_var($request->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
            $credentials[$field] = $request->username;
            $credentials['password'] = $request->password;
            $user = $this->model::where("$field", $request->username)->first();

            // Checkpoint 1- for too many attempts
            if (!$this->canAttemptLogin($ipAddress)) {
                return errorResponse('Too many login attempts. Your IP has been blocked for 15 minutes. Try again later');
            }

            if (!$user) {
                return errorResponse('User Not Found');
            }

            // Checkpoint 3- If the user is not active or organization is not allowed to login

            if ($user->status == '0') {
                return errorResponse('Login not Permitted ! Contact Administrator');
            }

            // Checkpoint 4- Validating the credentials do not attempt to login rather validate the credentials
            if (!Auth::guard($this->guard)->validate($credentials)) {
                // $this->incrementLoginAttempts($ipAddress);
                return errorResponse('Opps! your credentials do no match our records');
            }
            // We have to magage both functionalities here for 2FA
            if (($user->is_twofactor_enabled)) {
                $verificationCode = $this->generateVerificationCode($this->codeLength, "login_verification_code_$user->uuid");
                Notification::route('mail', $user->email)
                    ->notify(new VerificationNotification($verificationCode, 'login'));

                return response()->json([
                    'status' => '200',
                    'msg' => 'Verification Code Sent Successfully.',
                    'redirect' => route($this->_2FaCallbackUrl, [
                        'uuid' => $user->uuid,
                        'callback_hash' => encrypt($request->password),
                    ])
                ], 200);
            }

            // Login user directly if two factor auth is not enabled
            return $this->attemptLogin($ipAddress, $credentials, $user->uuid);
        } catch (Throwable $th) {
            return response()->json([
                'status' => '500',
                'msg' => 'An exception occurred',
                'exception' => $th->getMessage()
            ], 500);
        }
    }


    public function processTwoFactorLoginRequest($request)
    {
        $user = $this->model::where('uuid', $request->uuid)->first();
        if ($request->isMethod('GET')) {
            $callback_hash = $request->callback_hash;
            return view($this->_2FaView, compact('user', 'callback_hash'));
        }

        if ($request->isMethod('POST')) {

            $validator = Validator::make(
                $request->all(),
                [
                    'otp' => 'required|array|size:4', // Ensures 'otp' is an array with exactly 4 elements
                    'otp.*' => 'required|string|max:1', // Each element must be a single character (string)
                ],
                [
                    'otp.required' => 'The verification code is required.',
                    'otp.size' => 'The verification code must consist of exactly 4 characters.',
                    'otp.*.required' => 'Please enter a valid verification code.',
                    'otp.*.max' => 'Each part of the verification code must not exceed 1 character.',
                ]
            );

            // Otp is being received as an array need to parse it into simple string

            if ($validator->fails()) {
                return response()->json(['status' => "400", 'errors' => $validator->errors()]);
            }
            try {
                $sessionKey = "login_verification_code_{$user->uuid}";
                $verificationCode = session()->get($sessionKey);
                $otp = implode('', $request->otp);
                $ipAddress = $request->ip();
                $credentials = [
                    'email' => $user->email,
                    'password' => decrypt($request->callback_hash),
                ];
                if ($verificationCode == $otp) {
                    return $this->attemptLogin($ipAddress, $credentials, $user->uuid);
                }
                return response()->json([
                    'status' => "500",
                    'msg' => 'Invalid Verification Code'
                ], 500);
            } catch (Throwable $th) {
                return response()->json([
                    'status' => '500',
                    'msg' => 'An exception occurred',
                    'exception' => $th->getMessage()
                ], 500);
            }
        }
        abort(403, 'Method Not Allowed');
    }
    private function attemptLogin($ipAddress, $credentials, $uuid)
    {
        try {
            if (Auth::guard($this->guard)->attempt($credentials)) {
                Cache::forget("login_attempts:$ipAddress");
                Cache::forget("blocked_ip:$ipAddress");
                session()->forget("login_verification_code_$uuid");
                // Laoding the plan features into cache for easy access...
                return successResponse('Logged in successfully', route($this->loginSuccessRedirectUrl));
            }
            return errorResponse('Something went wrong');
        } catch (Throwable $th) {
            return throwException($th);
        }
    }

    public function logout()
    {
        Auth::guard($this->guard)->logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect()->route('home');
    }
}
