<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;
use App\Notifications\VerificationNotification;

trait HasVerificationCode
{
    protected $codeLength = 4;
    public function resendVerificationCode($request)
    {
        $email = $request->email;
        // Resusing the verification code module for different modules
        $module = $request->module ?? null;
        $uuid = $request->uuid ?? null;
        $key = $module ? "login_verification_code_$request->uuid" : "verification_code";
        Notification::route('mail', $email)
            ->notify(new VerificationNotification($this->generateVerificationCode($this->codeLength, $key), $module));
        return response()->json(['status' => '200', 'msg' => 'Verification code resent successfully'], 200);
    }

    public  function generateVerificationCode($length = 6, $key = 'verification_code')
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $randomString = Str::random($length);
        $shuffledString = str_shuffle($randomString);
        session()->put($key, $shuffledString);
        return $shuffledString;
    }
}
