<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Mail\MailController;
use App\Http\Controllers\Author\Auth\AuthorAuthController;
use App\Http\Controllers\Author\Application\ApplicationController;
use App\Http\Controllers\Author\Dashboard\AuthorDashboardController;
use App\Http\Controllers\Author\Notification\AuthorNotificationController;


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
Route::middleware(['revalidateSession', 'auth:web', 'active.account'])->group(function () {
    Route::get('logout', [AuthorAuthController::class, 'logout'])->name('logout');
    Route::get('dashboard', [AuthorDashboardController::class, 'dashboard'])->name('dashboard');

    // In-app notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [AuthorNotificationController::class, 'index'])->name('index');
        Route::get('latest', [AuthorNotificationController::class, 'latest'])->name('latest');
        Route::get('unread-count', [AuthorNotificationController::class, 'unreadCount'])->name('unread-count');
        Route::post('read-all', [AuthorNotificationController::class, 'markAllAsRead'])->name('read-all');
        Route::delete('clear-read', [AuthorNotificationController::class, 'clearRead'])->name('clear-read');
        Route::get('{notification}/open', [AuthorNotificationController::class, 'open'])->name('open');
        Route::post('{notification}/read', [AuthorNotificationController::class, 'markAsRead'])->name('read');
        Route::delete('{notification}', [AuthorNotificationController::class, 'destroy'])->name('destroy');
    });
    Route::post('/cookie-consent', [AuthorAuthController::class, 'store'])->name('cookie.consent');

    // -====================Admin Profile=============================

    Route::get('profile/{uuid?}', [AuthorAuthController::class, 'getProfile'])->name('profile');
    Route::post('profile/update/{uuid?}', [AuthorAuthController::class, 'updateProfile'])->name('profile.update');

    // ------------Chnage Password-----------------
    Route::get('change/password', [AuthorAuthController::class, 'changePasswordPage'])->name('password.update');
    Route::post('change/password', [AuthorAuthController::class, 'changePassword'])->name('password.update');

    // ----------Update Avatar------------------
    Route::post('avatar/update', [AuthorAuthController::class, 'updateAvatar'])->name('avatar.update');

    // -------------Author Application Module------------------
    Route::match(['get', 'post'], 'application/co_author/{uuid}', [ApplicationController::class, 'co_author'])->name('application.co_author');
    Route::match(['get', 'post'], 'application/co_author_update/{uuid}/{author_id}', [ApplicationController::class, 'co_author_update'])->name('application.co_author_update');
    Route::delete('application/co_author_delete/{uuid}/{author_id}', [ApplicationController::class, 'co_author_delete'])->name('application.co_author_delete');

    Route::match(['get', 'post'], 'application/check_list', [ApplicationController::class, 'check_list'])->name('application.check_list');
    Route::resource('application', ApplicationController::class);

    Route::match(['get', 'post'], 'application/file_upload/{id}/{type?}/{app_id?}/{legend?}', [ApplicationController::class, 'file_upload'])->name('application.file_upload');
    Route::delete('application/file_delete/{app_id}/{id}', [ApplicationController::class, 'fileDestroy'])->name('application.file_destroy');
    Route::get('application/toggleStatus/{uuid}', [ApplicationController::class, 'toggleStatus'])->name('application.toggleStatus');

    // Add Another document single route
    Route::match(['get', 'post'], 'application/add_document/{type?}/{app_id?}', [ApplicationController::class, 'add_document'])->name('application.add_document');

    Route::get('application/readMistake/{uuid}/{timeline_id?}', [ApplicationController::class, 'reaverifydMistake'])->name('application.readMistake');
    Route::get('application/reviewerReadMore/{uuid}/{timeline_id?}', [ApplicationController::class, 'reviewerReadMore'])->name('application.reviewerReadMore');
    Route::get('application/techEditorReadmore/{uuid}/{timeline_id}', [ApplicationController::class, 'techEditorReadmore'])->name('application.techEditorReadmore');
    Route::match(['get', 'post'], 'application/reSubmission/{uuid}', [ApplicationController::class, 'reSubmission'])->name('application.reSubmission');
    Route::get('application/authorUpdate/{uuid}/{timeline_id?}', [ApplicationController::class, 'authorUpdate'])->name('application.authorUpdate');

    // reviewer suggestion routes
    Route::match(['get', 'post'], 'application/reviewer/{uuid}', [ApplicationController::class, 'reviewer'])->name('application.reviewer');
    Route::match(['get', 'post'], 'application/reviewer_update/{uuid}/{reviewer_id}', [ApplicationController::class, 'reviewer_update'])->name('application.reviewer_update');
    Route::delete('application/reviewer_delete/{uuid}/{reviewer_id}', [ApplicationController::class, 'reviewer_delete'])->name('application.reviewer_delete');


    // save & next routes
    Route::post('application/documents', [ApplicationController::class, 'documents'])->name('application.documents');
    Route::post('application/articleInformation', [ApplicationController::class, 'articleInformation'])->name('application.articleInformation');
    Route::post('application/coAuthors', [ApplicationController::class, 'coAuthors'])->name('application.coAuthors');
    Route::post('application/reviewerSuggestions', [ApplicationController::class, 'reviewerSuggestions'])->name('application.reviewerSuggestions');
    Route::post('application/preview', [ApplicationController::class, 'preview'])->name('application.preview');


    // Mail routes
    Route::get('help-and-support', [MailController::class, 'index'])->name('help-and-support');
    Route::get('get-users-by-role', [MailController::class, 'getUsersByRole'])->name('getUsersByRole');
    Route::post('send-mail', [MailController::class, 'sendMail'])->name('sendMail');



    Route::get('locations/countries', [ApplicationController::class, 'getCountries']);
    Route::get('locations/states/{countryId}', [ApplicationController::class, 'getStates']);
    Route::get('locations/cities/{countryId}/{stateId}', [ApplicationController::class, 'getCities']);

    Route::post('check-email', [ApplicationController::class, 'checkAuthorEmail'])->name('check_email');
    Route::post('check-reviewer-email', [ApplicationController::class, 'checkReviewerEmail'])->name('check_reviewer_email');


    // author submitting corrected proof file
    Route::match(['get', 'post'], 'application/corrected_proof/{id?}/{type?}/{app_id?}/{legend?}', [ApplicationController::class, 'corrected_proof'])->name('application.corrected_proof');
});
