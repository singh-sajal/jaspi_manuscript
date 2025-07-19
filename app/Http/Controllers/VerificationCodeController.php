<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\HasVerificationCode;

class VerificationCodeController extends Controller
{
    use HasVerificationCode;

    //  Make invoke method to generate verification code
    public function __invoke(Request $request)
    {
        return $this->resendVerificationCode($request);
    }
}
