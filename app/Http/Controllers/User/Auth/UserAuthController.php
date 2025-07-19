<?php

namespace App\Http\Controllers\User\Auth;



use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Controllers\User\Auth\UserAuthRequest;
use App\Http\Controllers\Admin\Profile\ProfileRequest;
use App\Http\Controllers\User\Auth\UserAuthRepository;
use App\Http\Controllers\Admin\Auth\UpdatePasswordRequest;

class UserAuthController extends Controller
{
    protected $repository;

    public function __construct(UserAuthRepository $repository)
    {
        $this->repository = $repository;
    }
    public function login(UserAuthRequest $request)
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
}
