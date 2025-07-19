<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\Auth\UserAuthController;

// Guest routes
Route::middleware(['guest:web'])->group(function () {
    Route::match(['get', 'post'], 'login', [UserAuthController::class, 'login'])->name('login');
    Route::match(['get', 'post'], '2fa/auth', [UserAuthController::class, 'twoFactorAuthentication'])
        ->name('2fa.login')->middleware('ensureVerification:web');
});


// // Authenticated and protectd routes
Route::middleware(['revalidateSession', 'auth:web'])->group(function () {
    Route::get('logout', [UserAuthController::class, 'logout'])->name('logout');
    Route::get('dashboard', function () {
        return "Hii Welcome" . auth()->user()->name;
    })->name('dashboard');
});
