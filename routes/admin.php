<?php

use App\Http\Controllers\Admin\Application\ApplicationController;
use App\Http\Controllers\Admin\Auth\AdminAuthController;
use App\Http\Controllers\Admin\Author\AuthorController;
use App\Http\Controllers\Admin\Checklist\ChecklistController;
use App\Http\Controllers\Admin\Consultant\ConsultantController;
use App\Http\Controllers\Admin\Dashboard\AdminDashboardController;
use App\Http\Controllers\Mail\MailController;
use App\Http\Controllers\Admin\QuickLink\QuickLinkController;
use App\Http\Controllers\Admin\RolePermission\RoleController;
use App\Http\Controllers\Admin\Service\ServiceController;
use App\Http\Controllers\Admin\WebQuery\QueryController;
use App\Http\Controllers\Staff\StaffController;
use Illuminate\Support\Facades\Route;


// Guest routes
Route::middleware(['guest:admin'])->group(function () {
    Route::match(['get', 'post'], 'login', [AdminAuthController::class, 'login'])->name('login');
    Route::match(['get', 'post'], '2fa/auth', [AdminAuthController::class, 'twoFactorAuthentication'])
        ->name('2fa.login')->middleware('ensureVerification:admin');

    // -----------------Forget Password--------------------------------------------------
    Route::match(['get', 'post'], 'password/forget', [AdminAuthController::class, 'sendPasswordResetLink'])
        ->name('password.sendresetmail');
    Route::match(['get', 'post'], 'password/reset', [AdminAuthController::class, 'resetPassword'])
        ->name('password.reset');
});


// // Authenticated and protectd routes
Route::middleware(['revalidateSession', 'auth:admin'])->group(function () {
    Route::get('logout', [AdminAuthController::class, 'logout'])->name('logout');
    Route::get('dashboard', [AdminDashboardController::class, 'dashboard'])->name('dashboard');


    // -====================Admin Profile=============================

    Route::get('profile/{uuid?}', [AdminAuthController::class, 'getProfile'])->name('profile');
    Route::post('profile/update/{uuid?}', [AdminAuthController::class, 'updateProfile'])->name('profile.update');

    // ------------Chnage Password-----------------
    Route::get('change/password', [AdminAuthController::class, 'changePasswordPage'])->name('password.update');
    Route::post('change/password', [AdminAuthController::class, 'changePassword'])->name('password.update');

    // ----------Update Avatar------------------
    Route::post('avatar/update', [AdminAuthController::class, 'updateAvatar'])->name('avatar.update');
    // Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');





    // =====================Author Profile============================
    Route::get('author-profile/{uuid?}', [AdminAuthController::class, 'getAuthorProfile'])->name('getAuthorProfile');
    Route::match(['get', 'post'], 'authorChangePassword/{uuid}', [AdminAuthController::class, 'authorChangePassword'])->name('authorChangePassword');




    // =====================Access Control System===============

    Route::middleware(['role:superadmin'])->group(function () {
        // Role routes
        Route::post('roles/permissions/assign/{roleId}', [RoleController::class, 'assignPermission'])
            ->name('roles.permissions.assign');
        Route::resource('roles', RoleController::class);

        // Permission routes
        Route::match(['get', 'post'], 'permissions', [RoleController::class, 'permissionManager'])->name('permissions.manage');
        Route::delete('permissions/{id}', [RoleController::class, 'destroyPermission'])->name('permissions.destroy');
    });


    // Staff Module ( CRUD ) & Credential Generation
    Route::get('staff/toggleStatus/{uuid}', [StaffController::class, 'toggleStatus'])->name('staff.toggleStatus');
    Route::match(['get', 'post'], 'staff/generateCredentials/{uuid}', [StaffController::class, 'generateCredentials'])->name('staff.generateCredentials');
    Route::resource('staff', StaffController::class);


    // Author Module (CRUD) & Credential Generation
    Route::resource('author', AuthorController::class);
    Route::get('author/toggleStatus/{uuid}', [AuthorController::class, 'toggleStatus'])->name('author.toggleStatus');
    Route::match(['get', 'post'], 'author/generateCredentials/{uuid}', [AuthorController::class, 'generateCredentials'])->name('author.generateCredentials');


    // Author Application Module
    Route::resource('application', ApplicationController::class);

    // Author Application Module
    Route::resource('query', QueryController::class);

    // Co-Author CRUD
    Route::match(['get', 'post'], 'application/co_author/{uuid}', [ApplicationController::class, 'co_author'])->name('application.co_author');
    Route::match(['get', 'post'], 'application/co_author_update/{uuid}/{author_id}', [ApplicationController::class, 'co_author_update'])->name('application.co_author_update');
    Route::delete('application/co_author_delete/{uuid}/{author_id}', [ApplicationController::class, 'co_author_delete'])->name('application.co_author_delete');

    // Documents CRUD
    Route::match(['get', 'post'], 'application/file_upload/{id}/{type}/{app_id}', [ApplicationController::class, 'file_upload'])->name('application.file_upload');
    Route::delete('application/file_delete/{app_id}/{id}', [ApplicationController::class, 'fileDestroy'])->name('application.file_destroy');

    // Assign application
    Route::match(['get', 'post'], 'application/assign/{uuid}', [ApplicationController::class, 'assignApplication'])->name('application.assign');
    Route::match(['get', 'post'], 'application/assignReviewer/{uuid}', [ApplicationController::class, 'assignReviewer'])->name('application.assignReviewer');
    Route::get('application/acceptance/{uuid}', [ApplicationController::class, 'acceptance'])->name('application.acceptance');

    Route::get('application/rejection/{uuid}', [ApplicationController::class, 'rejection'])->name('application.rejection');
    Route::match(['get', 'post'], 'application/reviewed/{uuid}', [ApplicationController::class, 'reviewed'])->name('application.reviewed');
    Route::match(['get', 'post'], 'application/assignPublisher/{uuid}', [ApplicationController::class, 'assignPublisher'])->name('application.assignPublisher');
    Route::get('application/revise/{uuid}', [ApplicationController::class, 'revise'])->name('application.revise');
    Route::get('application/readMistake/{uuid}', [ApplicationController::class, 'readMistake'])->name('application.readMistake');
    Route::get('application/reviewerReadMore/{uuid}', [ApplicationController::class, 'reviewerReadMore'])->name('application.reviewerReadMore');

    Route::match(['get', 'post'], 'application/publish/{uuid}', [ApplicationController::class, 'publish'])->name('application.publish');
    Route::get('application/authorUpdate/{uuid}', [ApplicationController::class, 'authorUpdate'])->name('application.authorUpdate');

    Route::match(['get', 'post'], 'application/reviewScore/{uuid}', [ApplicationController::class, 'reviewScore'])->name('application.reviewScore');
    Route::match(['get', 'post'], 'application/editorDecision/{uuid}', [ApplicationController::class, 'editorDecision'])->name('application.editorDecision');

    // Mail routes
    Route::get('mail/index', [MailController::class, 'index'])->name('mail.index');
    Route::get('get-users-by-role', [MailController::class, 'getUsersByRole'])->name('getUsersByRole');
    Route::post('send-mail', [MailController::class, 'sendMail'])->name('sendMail');
});
