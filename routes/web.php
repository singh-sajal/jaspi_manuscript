<?php

use App\Http\Controllers\Admin\Auth\AdminAuthController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\VerificationCodeController;
use App\Http\Controllers\Web\HomeController;
use App\Models\Admin;
use App\Models\Application;
use App\Models\ApplicationTimeline;
use App\Models\Author;
use App\Models\Integration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Redirect to the login page route

Route::middleware(['checkIfAuthenticated'])->group(function () {

    Route::get('/', function () {
        return view('home');
    });

    Route::get('home', function () {
        return view('home');
    })->name('home');
});

Route::get('registered-successfully', function () {
    return view('emails.registered-successfully');
})->name('registered-successfully');

Route::get('published-manuscript', [HomeController::class, 'published_index'])->name('published-manuscript');


// Route::get('home', [HomeController::class, 'redirect'])->name('home');

// Route::match(['get', 'post'], 'login', [AdminAuthController::class, 'login'])->name('login');


// Route::get('newhome', function () {
//     return view('newhome');
// })->name('newhome');

Route::get('file/view/frame', FileController::class)->name('file.viewframe');
// Sending verification code to the user in each auth module (admin, user, etc)
Route::get('resend/verification_code', VerificationCodeController::class)
    ->name('verification.resend-code');

Route::post('contact', [HomeController::class, 'contactSave'])->name('web.contact');
Route::get('contact', [HomeController::class, 'contact'])->name('web.contact');

Route::match(['get', 'post'], 'forgot_password', [HomeController::class, 'forgot_password'])->name('forgot_password');


Route::get('submited-applications', function () {
    $application = Application::where('status', '!=', 'incomplete')->get();
    return $application;
});
Route::get('incomplete-applications', function () {
    $application = Application::where('status', 'incomplete')->get();
    return $application;
});
Route::get('published-applications', function () {
    $application = Application::where('status', 'published')->get();
    return $application;
});

Route::get('staffs', function () {
    // Fetch all staff with their roles
    $staffs = Admin::with('roles')->get();
    return response()->json([
        'status' => true,
        'data' => $staffs
    ]);
});

Route::get('seed-author-flow-test', function () {
    $email = 'flowtest@aiims.test';

    $author = Author::where('email', $email)->first();
    if (!$author) {
        $author = Author::create([
            'username'            => 'flowtest',
            'name'                => 'Flow Test Author',
            'lname'               => 'Author',
            'email'               => $email,
            'phone'               => '9999999999',
            'password'            => Hash::make('password123'),
            'gender'              => 'Other',
            'status'              => '1',
            'is_email_verified'   => 1,
            'registration_type'   => 'author',
        ]);
    }

    $items = [];

    $createMs = function ($title, $status, $remark) use ($author, &$items) {
        $application = Application::firstOrCreate(
            ['author_id' => $author->id, 'title' => $title],
            [
                'submission_type'   => 'initial_submission',
                'article_type'      => ['Narrative Review'],
                'title'             => $title,
                'description'       => 'Seeded by seed-author-flow-test - ' . $status,
                'author_affiliation' => 'AIIMS Test',
                'author_id'         => $author->id,
                'author_name'       => $author->name,
                'status'            => $status,
            ]
        );

        ApplicationTimeline::firstOrCreate(
            ['application_id' => $application->id, 'status' => $status],
            [
                'application_id'       => $application->id,
                'status'               => $status,
                'is_visible_to_author' => 1,
                'remark'               => $remark,
                'data'                 => ['comment' => 'Seeded by seed-author-flow-test'],
            ]
        );

        $items[] = ['status' => $status, 'uuid' => $application->uuid, 'title' => $title];
    };

    $createMs('Flow Test - Revise Reply (author action test)', 'revise', 'Manuscript revision is requested');
    $createMs('Flow Test - Proofreading Reply (author action test)', 'proofreading', 'Proofread the manuscript and upload the corrected file.');

    $atRisk = Application::where('status', 'revise')->count();

    return response()->json([
        'status'  => true,
        'author'  => ['email' => $email, 'password' => 'password123', 'id' => $author->id],
        'created' => $items,
        'note'    => 'Log in as the author (email/password above) and open the manuscript to see the Re-submit button (revise) and Upload Proofread File card (proofreading).',
        'total_in_revise' => $atRisk,
    ]);
});

Route::get('reviewers', function () {
    // Fetch all reviewers (internal or external) with their details
    $reviewers = Admin::role('Reviewer')->get(['name', 'email', 'phone', 'rev_type']);
    return response()->json([
        'status' => true,
        'data' => $reviewers
    ]);
});

Route::get('reviewer-performance', function () {
    // Per-reviewer performance: total assigned, work done, not responded
    $reviewers = Admin::role('Reviewer')->get(['id', 'name', 'rev_type']);

    $marks = [
        'internal' => ['Assigned to Internal Reviewer', 'Internal Reviewer Reassigned'],
        'external' => ['Assigned to External Reviewer', 'External Reviewer Reassigned'],
    ];
    $doneStatuses = [
        'internal' => ['internal-reviewer-reviewed'],
        'external' => ['external-reviewer-reviewed'],
    ];

    $report = $reviewers->map(function ($reviewer) use ($marks, $doneStatuses) {
        $isInternal = $reviewer->rev_type === 'internal';
        $assignmentMarks = $isInternal ? $marks['internal'] : (in_array($reviewer->rev_type, ['external'], true) ? $marks['external'] : array_merge($marks['internal'], $marks['external']));
        $statuses = $isInternal ? $doneStatuses['internal'] : (in_array($reviewer->rev_type, ['external'], true) ? $doneStatuses['external'] : array_merge($doneStatuses['internal'], $doneStatuses['external']));

        $assignedIds = ApplicationTimeline::whereIn('remark', $assignmentMarks)
            ->where('assigned_to_id', $reviewer->id)
            ->distinct()
            ->pluck('application_id');

        $doneIds = ApplicationTimeline::whereIn('status', $statuses)
            ->where('assigned_by', $reviewer->id)
            ->distinct()
            ->pluck('application_id');

        $legacyIds = ApplicationTimeline::whereIn('status', $statuses)
            ->whereNull('assigned_by')
            ->distinct()
            ->pluck('application_id')
            ->filter(function ($appId) use ($reviewer, $assignmentMarks) {
                return ApplicationTimeline::where('application_id', $appId)
                    ->whereIn('remark', $assignmentMarks)
                    ->where('assigned_to_id', $reviewer->id)
                    ->exists();
            });

        $doneIds = $doneIds->merge($legacyIds)->unique();
        $assigned = $assignedIds->count();
        $done = $doneIds->count();

        return [
            'name' => $reviewer->name,
            'rev_type' => $reviewer->rev_type ?? '-',
            'total_assigned' => $assigned,
            'work_done' => $done,
            'not_responded' => max(0, $assigned - $done),
        ];
    })->values();

    return response()->json([
        'status' => true,
        'data' => $report,
    ]);
});

Route::get('seed-reviewer-interest', function () {
    $interest = Integration::firstOrCreate(['name' => 'Community/Clinical Stewardship']);

    $reviewers = Admin::role('Reviewer')->get(['id', 'name']);

    $now = now();
    $rows = $reviewers->map(fn ($reviewer) => [
        'admin_id'       => $reviewer->id,
        'integration_id' => $interest->id,
        'created_at'     => $now,
        'updated_at'     => $now,
    ])->all();

    DB::table('admin_integration')->insertOrIgnore($rows);

    $fresh = DB::table('admin_integration')
        ->join('admins', 'admins.id', '=', 'admin_integration.admin_id')
        ->where('admin_integration.integration_id', $interest->id)
        ->pluck('admins.name', 'admins.id')
        ->values();

    return response()->json([
        'status'          => true,
        'interest'        => ['id' => $interest->id, 'name' => $interest->name],
        'linked_count'    => $fresh->count(),
        'linked_reviewers'=> $fresh,
    ]);
});

Route::get('seed-handling-editor-decision', function () {
    $total = Application::count();
    $updated = 0;

    Application::query()->chunkById(100, function ($applications) use (&$updated) {
        foreach ($applications as $application) {
            $data = $application->data ?? [];
            $data['handling_editor_decision'] = 1;
            $data['handling_editor_reason'] = null;

            $application->update(['data' => $data]);
            $updated++;
        }
    });

    return response()->json([
        'status'       => true,
        'rows_updated' => $updated,
        'all_rows'     => $total,
        'message'      => 'Set handling_editor_decision=1 and handling_editor_reason=null in all application data.',
    ]);
});


