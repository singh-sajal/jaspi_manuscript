<?php

namespace App\Http\Controllers\Admin\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Auth\AdminAuthRequest;
use App\Http\Controllers\Admin\Profile\ProfileRequest;
use App\Http\Controllers\Admin\Auth\AdminAuthRepository;
use App\Http\Controllers\Admin\Auth\UpdatePasswordRequest;

class AdminAuthController extends Controller
{
    protected $repository;

    public function __construct(AdminAuthRepository $repository)
    {
        $this->repository = $repository;
    }
    public function login(AdminAuthRequest $request)
    {
        return $this->repository->login($request);
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
    public function sendPasswordResetLink(AdminForgetPasswordRequest $request)
    {
        return $this->repository->sendPasswordResetLink($request);
    }

    public function resetPassword(AdminResetPasswordRequest $request)
    {
        return $this->repository->resetPassword($request);
    }

    // ---------------------Admin Profile Manager--------

    public function getProfile($uuid = null)
    {
        return $this->repository->getProfile($uuid);
    }


    public function updateProfile(AdminProfileRequest $request, $uuid = null)
    {
        return $this->repository->updateProfile($request, $uuid);
    }


    // -----------------Chnage Password ------------

    public function changePasswordPage()
    {
        return $this->repository->changePasswordPage();
    }
    public function changePassword(AdminChangePasswordRequest $request)
    {
        return $this->repository->changePassword($request);
    }

    // --------------Update Avatar-------------

    public function updateAvatar(AdminAvatarRequest $request)
    {
        return $this->repository->updateAvatar($request);
    }


    public function getAuthorProfile($uuid = null)
    {
        return $this->repository->getAuthorProfile($uuid);
    }

    public function authorChangePassword(AdminChangePasswordRequest $request, $uuid)
    {
        return $this->repository->authorChangePassword($request, $uuid);
    }
}
