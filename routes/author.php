<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Mail\MailController;
use App\Http\Controllers\Author\Auth\AuthorAuthController;
use App\Http\Controllers\Author\Application\ApplicationController;
use App\Http\Controllers\Author\Dashboard\AuthorDashboardController;


// Guest routes
Route::middleware(['guest:web'])->group(function () {
    Route::match(['get', 'post'], 'login', [AuthorAuthController::class, 'login'])->name('login');
    Route::match(['get', 'post'], 'registration', [AuthorAuthController::class, 'registration'])->name('registration');
    Route::get('registrationDone', [AuthorAuthController::class, 'registrationDone'])->name('registrationDone');
    Route::match(['get', 'post'], '2fa/auth', [AuthorAuthController::class, 'twoFactorAuthentication'])
        ->name('2fa.login')->middleware('ensureVerification:consultant');
    // -----------------Forget Password--------------------------------------------------
    Route::match(['get', 'post'], 'password/forget', [AuthorAuthController::class, 'sendPasswordResetLink'])
        ->name('password.sendresetmail');
    Route::match(['get', 'post'], 'password/reset', [AuthorAuthController::class, 'resetPassword'])
        ->name('password.reset');
    //test mail
    Route::get('testMail', [AuthorAuthController::class, 'testMail'])->name('testMail');
    Route::match(['get', 'post'], 'veifyEmail', [AuthorAuthController::class, 'veifyEmail'])->name('veifyEmail');
    Route::match(['get', 'post'], 'verifyOtp', [AuthorAuthController::class, 'verifyOtp'])->name('verifyOtp');
    Route::match(['get', 'post'], 'userCheck', [AuthorAuthController::class, 'userCheck'])->name('userCheck');
});


// // Authenticated and protectd routes
Route::middleware(['revalidateSession', 'auth:web'])->group(function () {
    Route::get('logout', [AuthorAuthController::class, 'logout'])->name('logout');
    Route::get('dashboard', [AuthorDashboardController::class, 'dashboard'])->name('dashboard');
    // -====================Admin Profile=============================

    Route::get('profile/{uuid?}', [AuthorAuthController::class, 'getProfile'])->name('profile');
    Route::post('profile/update/{uuid?}', [AuthorAuthController::class, 'updateProfile'])->name('profile.update');

    // ------------Chnage Password-----------------
    Route::get('change/password', [AuthorAuthController::class, 'changePasswordPage'])->name('password.update');
    Route::post('change/password', [AuthorAuthController::class, 'changePassword'])->name('password.update');

    // ----------Update Avatar------------------
    Route::post('avatar/update', [AuthorAuthController::class, 'updateAvatar'])->name('avatar.update');

    // -------------Author Application Module------------------
    Route::resource('application', ApplicationController::class);
    Route::match(['get', 'post'], 'application/co_author/{uuid}', [ApplicationController::class, 'co_author'])->name('application.co_author');
    Route::match(['get', 'post'], 'application/co_author_update/{uuid}/{author_id}', [ApplicationController::class, 'co_author_update'])->name('application.co_author_update');
    Route::delete('application/co_author_delete/{uuid}/{author_id}', [ApplicationController::class, 'co_author_delete'])->name('application.co_author_delete');

    Route::match(['get', 'post'], 'application/file_upload/{id}/{type}/{app_id}', [ApplicationController::class, 'file_upload'])->name('application.file_upload');
    Route::delete('application/file_delete/{app_id}/{id}', [ApplicationController::class, 'fileDestroy'])->name('application.file_destroy');
    Route::get('application/toggleStatus/{uuid}', [ApplicationController::class, 'toggleStatus'])->name('application.toggleStatus');

    Route::get('application/readMistake/{uuid}', [ApplicationController::class, 'readMistake'])->name('application.readMistake');
    Route::get('application/reviewerReadMore/{uuid}', [ApplicationController::class, 'reviewerReadMore'])->name('application.reviewerReadMore');
    Route::match(['get', 'post'], 'application/reSubmission/{uuid}', [ApplicationController::class, 'reSubmission'])->name('application.reSubmission');
    Route::get('application/authorUpdate/{uuid}', [ApplicationController::class, 'authorUpdate'])->name('application.authorUpdate');


    // Mail routes
    Route::get('help-and-support', [MailController::class, 'index'])->name('help-and-support');
    Route::get('get-users-by-role', [MailController::class, 'getUsersByRole'])->name('getUsersByRole');
    Route::post('send-mail', [MailController::class, 'sendMail'])->name('sendMail');
});
