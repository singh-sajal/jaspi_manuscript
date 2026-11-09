<?php

use App\Http\Controllers\Admin\Application\ApplicationController;
use App\Http\Controllers\Admin\Auth\AdminAuthController;
use App\Http\Controllers\Admin\Author\AuthorController;
use App\Http\Controllers\Admin\Checklist\ChecklistController;
use App\Http\Controllers\Admin\Consultant\ConsultantController;
use App\Http\Controllers\Admin\Dashboard\AdminDashboardController;
use App\Http\Controllers\Admin\InviteReviewer\InviteReviewerController;
use App\Http\Controllers\Admin\Integration\IntegrationController;
use App\Http\Controllers\Admin\Notification\NotificationController;
use App\Http\Controllers\Admin\QuickLink\QuickLinkController;
use App\Http\Controllers\Admin\RolePermission\RoleController;
use App\Http\Controllers\Admin\Service\ServiceController;
use App\Http\Controllers\Admin\WebQuery\QueryController;
use App\Http\Controllers\Admin\Settings\SettingController;
use App\Http\Controllers\Mail\MailController;
use App\Http\Controllers\Staff\StaffController;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Request;

// Guest routes
Route::middleware(['guest:admin'])->group(function () {
    Route::match(['get', 'post'], 'login', [AdminAuthController::class, 'login'])->name('login');
    Route::match(['get', 'post'], '2fa/auth', [AdminAuthController::class, 'twoFactorAuthentication'])
        ->name('2fa.login')->middleware('ensureVerification:admin');

    Route::match(['get', 'post'], 'registration', [AdminAuthController::class, 'registration'])->name('registration');
    Route::match(['get', 'post'], 'userCheck', [AdminAuthController::class, 'userCheck'])->name('userCheck');
    Route::match(['get', 'post'], 'verifyOtp', [AdminAuthController::class, 'verifyOtp'])->name('verifyOtp');

    // -----------------Forget Password--------------------------------------------------
    Route::match(['get', 'post'], 'password/forget', [AdminAuthController::class, 'sendPasswordResetLink'])
        ->name('password.sendresetmail');
    Route::match(['get', 'post'], 'password/reset', [AdminAuthController::class, 'resetPassword'])
        ->name('password.reset');
});

// common routes start
Route::get('application/techEditorReadMore/{uuid}/{timeline_id}', [ApplicationController::class, 'techEditorReadMore'])->name('application.techEditorReadMore');
Route::get('application/readMistake/{uuid}/{timeline_id?}', [ApplicationController::class, 'readMistake'])->name('application.readMistake');
Route::get('application/reviewerReadMore/{uuid}/{timeline_id?}', [ApplicationController::class, 'reviewerReadMore'])->name('application.reviewerReadMore');
// common routes end

// // Authenticated and protectd routes
Route::middleware(['revalidateSession', 'auth:admin', 'active.account'])->group(function () {

    // change status to anything by superadmin
    Route::match(['get', 'post'], '/application/change_status', [ApplicationController::class, 'change_status'])->name('application.change_status');

    Route::get('logout', [AdminAuthController::class, 'logout'])->name('logout');
    Route::get('dashboard', [AdminDashboardController::class, 'dashboard'])->name('dashboard');

    // In-app notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('latest', [NotificationController::class, 'latest'])->name('latest');
        Route::get('unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::post('read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
        Route::delete('clear-read', [NotificationController::class, 'clearRead'])->name('clear-read');
        Route::get('{notification}/open', [NotificationController::class, 'open'])->name('open');
        Route::post('{notification}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::delete('{notification}', [NotificationController::class, 'destroy'])->name('destroy');
    });
    Route::get('dashboard/monthly-chart', [AdminDashboardController::class, 'monthlyChart'])->name('dashboard.monthly-chart');
    Route::get('dashboard/weekly-chart', [AdminDashboardController::class, 'weeklyChart'])->name('dashboard.weekly-chart');
    Route::post('/cookie-consent', [AdminAuthController::class, 'store'])->name('cookie.consent');


    Route::resource('settings', SettingController::class);



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
    Route::post('author-profile/update/{uuid?}', [AdminAuthController::class, 'updateAuthorProfile'])->name('authorProfile.update');
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

    // Integration Module ( CRUD )
    Route::resource('integration', IntegrationController::class);


    // Author Module (CRUD) & Credential Generation
    Route::resource('author', AuthorController::class);
    Route::get('author/toggleStatus/{uuid}', [AuthorController::class, 'toggleStatus'])->name('author.toggleStatus');
    Route::match(['get', 'post'], 'author/generateCredentials/{uuid}', [AuthorController::class, 'generateCredentials'])->name('author.generateCredentials');

    // Route::get('application/final_docs/', [ApplicationController::class, 'final_docs'])->name('application.final_doc');



    // Author Application Module
    Route::resource('query', QueryController::class);

    // Co-Author CRUD
    Route::match(['get', 'post'], 'application/co_author/{uuid}', [ApplicationController::class, 'co_author'])->name('application.co_author');
    Route::match(['get', 'post'], 'application/co_author_update/{uuid}/{author_id}', [ApplicationController::class, 'co_author_update'])->name('application.co_author_update');
    Route::delete('application/co_author_delete/{uuid}/{author_id}', [ApplicationController::class, 'co_author_delete'])->name('application.co_author_delete');

    // Documents CRUD
    Route::match(['get', 'post'], 'application/file_upload/{id}/{type}/{app_id?}/{legend?}', [ApplicationController::class, 'file_upload'])->name('application.file_upload');
    Route::delete('application/file_delete/{app_id}/{id}', [ApplicationController::class, 'fileDestroy'])->name('application.file_destroy');

    // Assign application
    Route::match(['get', 'post'], 'application/assign/{uuid}', [ApplicationController::class, 'assignApplication'])->name('application.assign');
    Route::match(['get', 'post'], 'application/handlingEditorDecision/{uuid}', [ApplicationController::class, 'handlingEditorDecision'])->name('application.handlingEditorDecision');
    Route::get('application/handlingEditorRejection/{uuid}', [ApplicationController::class, 'handlingEditorRejection'])->name('application.handlingEditorRejection');
    Route::get('application/viewAbstract/{uuid}', [ApplicationController::class, 'viewAbstract'])->name('application.viewAbstract');
    Route::match(['get', 'post'], 'application/assignReviewer/{uuid}', [ApplicationController::class, 'assignReviewer'])->name('application.assignReviewer');


    // these buttons will activate after score application
    // Route::get('application/editor_accepts/{uuid}', [ApplicationController::class, 'editor_accepts'])->name('application.editor_accepts');
    // Route::get('application/editor_revise/{uuid}', [ApplicationController::class, 'editor_revise'])->name('application.editor_revise');
    // Route::get('application/editor_rejects/{uuid}', [ApplicationController::class, 'editor_rejects'])->name('application.editor_rejects');
    Route::match(['get', 'post'], 'application/editor_final_decision/{uuid}/{action?}', [ApplicationController::class, 'editor_final_decision'])->name('application.editor_final_decision');


    Route::match(['get', 'put'], 'application/re_review/{uuid}', [ApplicationController::class, 're_review'])->name('application.re_review');

    Route::match(['get', 'post'], 'application/application.remind_reviewers/{uuid}', [ApplicationController::class, 'remindReviewers'])->name('application.remind_reviewers');



    // these buttons shows to admin role when editor accepts or rejects or revise the application for the final chance.
    // Route::get('application/acceptance/{uuid}', [ApplicationController::class, 'acceptance'])->name('application.acceptance');
    // Route::get('application/rejection/{uuid}', [ApplicationController::class, 'rejection'])->name('application.rejection');
    // Route::get('application/revise/{uuid}', [ApplicationController::class, 'revise'])->name('application.revise');
    Route::match(['get', 'post'], 'application/assistantToChiefForApproval/{uuid}', [ApplicationController::class, 'assistantToChiefForApproval'])->name('application.assistantToChiefForApproval');
    Route::match(['get', 'post'], 'application/chiefToAssistanResponse/{uuid}', [ApplicationController::class, 'chiefToAssistanResponse'])->name('application.chiefToAssistanResponse');
    Route::match(['get', 'post'], 'application/seeChiefDecision/{uuid}', [ApplicationController::class, 'seeChiefDecision'])->name('application.seeChiefDecision');
    Route::get('application/chiefReadMore/{uuid}/{timeline_id?}', [ApplicationController::class, 'chiefReadMore'])->name('application.chiefReadMore');
    Route::match(['get', 'post'], 'application/assistant_editor_decision/{uuid}/{action?}', [ApplicationController::class, 'assistant_editor_decision'])->name('application.assistant_editor_decision');



    Route::match(['get', 'post'], 'application/reviewed/{uuid}', [ApplicationController::class, 'reviewed'])->name('application.reviewed');
    Route::match(['get', 'post'], 'application/assignPublisher/{uuid}', [ApplicationController::class, 'assignPublisher'])->name('application.assignPublisher');



    Route::match(['get', 'post'], 'application/techEditorDecision/{uuid}', [ApplicationController::class, 'techEditorDecision'])->name('application.techEditorDecision');



    Route::match(['get', 'post'], 'application/formatted_document/{app_id}/{type?}', [ApplicationController::class, 'formatted_document'])->name('application.formatted_document');
    Route::match(['get', 'post'], 'application/send_for_proofreading/{uuid}', [ApplicationController::class, 'send_for_proofreading'])->name('application.send_for_proofreading');
    Route::match(['get', 'post'], 'application/accept_for_publication/{uuid}', [ApplicationController::class, 'acceptForPublication'])->name('application.acceptForPublication');


    Route::get('application/authorUpdate/{uuid}/{timeline_id?}', [ApplicationController::class, 'authorUpdate'])->name('application.authorUpdate');

    Route::match(['get', 'post'], 'application/reviewScore/{uuid}', [ApplicationController::class, 'reviewScore'])->name('application.reviewScore');
    Route::match(['get', 'post'], 'application/editorDecision/{uuid}', [ApplicationController::class, 'editorDecision'])->name('application.editorDecision');
    Route::match(['get', 'put'], 'application/editEditorDecision/{uuid}', [ApplicationController::class, 'editEditorDecision'])->name('application.editEditorDecision');

    // reviewers action to the manuscript
    Route::match(['get', 'post'], 'application/rev_decision/{uuid}', [ApplicationController::class, 'rev_decision'])->name('application.rev_decision');
    Route::get('application/reviewerRejection/{uuid}/{type?}', [ApplicationController::class, 'reviewerRejection'])->name('application.reviewerRejection');


    // Mail routes
    Route::get('mail/index', [MailController::class, 'index'])->name('mail.index');
    Route::get('get-users-by-role', [MailController::class, 'getUsersByRole'])->name('getUsersByRole');
    Route::post('send-mail', [MailController::class, 'sendMail'])->name('sendMail');


    // invite Reviewer
    Route::prefix('inviteReviewer')->name('inviteReviewer.')->group(function () {
        Route::get('/',                         [InviteReviewerController::class, 'index'])->name('index');
        Route::match(['get', 'post'], 'create', [InviteReviewerController::class, 'store'])->name('store');
        Route::post('resend/{id}',              [InviteReviewerController::class, 'resend'])->name('resend');
        Route::delete('destroy/{id}',           [InviteReviewerController::class, 'destroy'])->name('destroy');
        Route::post('import',                   [InviteReviewerController::class, 'import'])->name('import');
        Route::post('bulk-invite',              [InviteReviewerController::class, 'bulkInvite'])->name('bulk-invite');
        Route::get('export',                    [InviteReviewerController::class, 'export'])->name('export');
        Route::post('send-bulk',                [InviteReviewerController::class, 'sendBulk'])->name('send-bulk'); // 👈 new
    });


    // reviewer suggestion routes
    Route::match(['get', 'post'], 'application/reviewer/{uuid}', [ApplicationController::class, 'reviewer'])->name('application.reviewer');
    Route::match(['get', 'post'], 'application/reviewer_update/{uuid}/{reviewer_id}', [ApplicationController::class, 'reviewer_update'])->name('application.reviewer_update');
    Route::delete('application/reviewer_delete/{uuid}/{reviewer_id}', [ApplicationController::class, 'reviewer_delete'])->name('application.reviewer_delete');


    // save & next routes
    Route::post('application/documents', [ApplicationController::class, 'documents'])->name('application.documents');
    Route::post('application/articleInformation', [ApplicationController::class, 'articleInformation'])->name('application.articleInformation');
    Route::post('application/coAuthors', [ApplicationController::class, 'coAuthors'])->name('application.coAuthors');
    // Route::post('application/preview', [ApplicationController::class, 'preview'])->name('application.preview');


    // show check list
    Route::match(['get', 'put'], 'application/showCheckList/{uuid}', [ApplicationController::class, 'showCheckList'])->name('application.showCheckList');


    // last accept and publish routes
    // Route::match(['get', 'post', 'application/publish/{uuid}', [ApplicationController::class, 'publish']])->name('application.publish');
    Route::match(['get', 'post'], '/application/publish/{uuid}', [ApplicationController::class, 'publish'])->name('application.publish');

    // direct publish shortcut route
    Route::match(['get', 'post'], '/application/direct_publish/{uuid}', [ApplicationController::class, 'direct_publish'])->name('application.direct_publish');


    // add doi number after accepted
    Route::match(['get', 'put'], '/application/add_doi/{uuid}', [ApplicationController::class, 'add_doi'])->name('application.add_doi');
















    // Inactive manuscripts (assigned to the author, 3+ weeks with no action).
    // Registered before the application resource route so the literal
    // 'inactive' segments are not swallowed by application/{application}.
    Route::get('application/inactive', [ApplicationController::class, 'index'])->name('application.inactive');
    Route::post('application/inactive/bulk-mail', [ApplicationController::class, 'inactiveBulkMail'])->name('application.inactive.bulk_mail');
    Route::get('application/inactive/{uuid}/mail', [ApplicationController::class, 'inactiveSendMailForm'])->name('application.inactive.send_mail_form');
    Route::post('application/inactive/{uuid}/mail', [ApplicationController::class, 'inactiveSendMail'])->name('application.inactive.send_mail');

    // Review Deadlines (per-reviewer reminder tracking). Admins + handling editors.
    // Registered before the resource route for the same reason as 'inactive'.
    Route::get('application/review-deadline', [ApplicationController::class, 'index'])->name('application.review_deadline');
    Route::post('application/review-deadline/{id}/remind', [ApplicationController::class, 'sendReviewerReminder'])->name('application.review_deadline.remind');


    // Author Application Module
    Route::resource('application', ApplicationController::class);
});
