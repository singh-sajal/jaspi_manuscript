<?php

namespace App\Http\Controllers\Author\Auth;


use App\Mail\TestMail;
use App\Http\Controllers\Admin\Auth\UpdatePasswordRequest;

use App\Http\Controllers\Admin\Profile\ProfileRequest;
use App\Http\Controllers\Author\Auth\AuthorAuthRepository;
use App\Http\Controllers\Author\Auth\AuthorAuthRequest;
use App\Http\Controllers\Author\Auth\AuthorAvatarRequest;
use App\Http\Controllers\Author\Auth\AuthorChangePasswordRequest;
use App\Http\Controllers\Author\Auth\AuthorForgetPasswordRequest;
use App\Http\Controllers\Author\Auth\AuthorProfileRequest;
use App\Http\Controllers\Author\Auth\AuthorResetPasswordRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;



class AuthorAuthController extends Controller
{
    protected $repository;

    public function __construct(AuthorAuthRepository $repository)
    {
        $this->repository = $repository;
    }
    public function login(AuthorAuthRequest $request)
    {
        return $this->repository->login($request);
    }

    public function registration(Request $request)
    {
        return $this->repository->register($request);
    }

    public function userCheck(Request $request)
    {
        return $this->repository->userCheck($request);
    }
    public function verifyOtp(Request $request)
    {
        return $this->repository->verifyOtp($request);
    }
    public function testMail()
    {
        try {
            Mail::to('msnegi1982@gmail.com')->send(new TestMail());
            return 'Email sent!';
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function registrationDone(AuthorAuthRequest $request)
    {
        return $this->repository->registrationDone($request);
    }

    public function twoFactorAuthentication(Request $request)
    {
        return $this->repository->processTwoFactorLoginRequest($request);
    }

    public function logout()
    {
        return $this->repository->logout();
    }
    // ----------------Forget Passwor manager---------------
    public function sendPasswordResetLink(AuthorForgetPasswordRequest $request)
    {
        return $this->repository->sendPasswordResetLink($request);
    }

    public function resetPassword(AuthorResetPasswordRequest $request)
    {
        return $this->repository->resetPassword($request);
    }

    // ---------------------Admin Profile Manager--------

    public function getProfile($uuid = null)
    {
        return $this->repository->getProfile($uuid);
    }


    public function updateProfile(AuthorProfileRequest $request, $uuid = null)
    {
        return $this->repository->updateProfile($request, $uuid);
    }


    // -----------------Chnage Password ------------

    public function changePasswordPage()
    {
        return $this->repository->changePasswordPage();
    }
    public function changePassword(AuthorChangePasswordRequest $request)
    {
        return $this->repository->changePassword($request);
    }

    // --------------Update Avatar-------------

    public function updateAvatar(AuthorAvatarRequest $request)
    {
        return $this->repository->updateAvatar($request);
    }
}
