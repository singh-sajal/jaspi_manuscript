<?php

namespace App\Http\Controllers\Admin\Application;

use App\Mail\InactivityReminderMail;
use App\Models\Application;
use App\Models\ApplicationTimeline;
use App\Models\InactivityMail;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Throwable;

class InactiveApplicationRepository
{
    private NotificationService $notifications;

    public function __construct(NotificationService $notifications)
    {
        $this->notifications = $notifications;
    }

    private function applicationReference(Application $application): string
    {
        return $application->application_id ?: ($application->title ?: 'the manuscript');
    }

    /**
     * Manuscripts sitting with the author for 3+ weeks, with their reminder
     * ledger attached (total reminders sent + last one sent).
     */
    public function index($request)
    {
        $this->authorize();

        $perPage = $request->perPage ?? 50;
        $type = $request->type ?? 'inactive';

        $applications = Application::query()
            ->inactive()
            ->with('author', 'handoverTimeline')
            ->withCount('inactivityMails')
            ->withMax('inactivityMails', 'sent_at');

        $this->search($request, $applications);
        $this->sort($request, $applications);

        $applications = $applications->paginate($perPage);

        if ($request->ajax()) {
            try {
                $datatable = view('admin.application.inactive.datatable', compact('applications'))->render();
                return response()->json([
                    'status' => '200',
                    'msg' => 'Data loaded',
                    'data' => $datatable,
                    'paginationInfo' => getPaginationInfo($applications)
                ], 200);
            } catch (Throwable $th) {
                return throwException($th);
            }
        }

        $stats = $this->stats();

        return view('admin.application.inactive.index', compact('type', 'stats'));
    }

    /**
     * Section-level totals: how many are inactive, how many reminders have
     * gone out in total, and when the last one was sent.
     */
    public function stats()
    {
        $lastMail = InactivityMail::max('sent_at');

        return [
            'inactive'      => Application::query()->inactive()->count(),
            'mails_sent'    => InactivityMail::count(),
            'last_sent_at'  => $lastMail ? \Illuminate\Support\Carbon::parse($lastMail) : null,
        ];
    }

    /**
     * Mail every manuscript owner in the Inactive section. One email per
     * manuscript, so an author with three idle manuscripts receives three
     * emails and each manuscript keeps its own accurate count.
     *
     * Bulk sends deliberately skip the timeline: the InactivityMail ledger
     * already records the send per manuscript, and a single bulk action would
     * otherwise stamp the same entry onto many histories at once.
     */
    public function bulkMail(Request $request)
    {
        $this->authorize();

        $validated = $this->validateMail($request);
        if ($validated === false) {
            return false;
        }

        $inactiveCount = Application::query()->inactive()->count();

        if ($inactiveCount === 0) {
            return errorResponse('There are no inactive manuscripts to mail.');
        }

        $currentAdmin = authUser('admin');
        $sent = 0;
        $skipped = 0;
        $failed = 0;

        Application::query()
            ->inactive()
            ->with('author')
            ->chunkById(100, function ($applications) use ($validated, $currentAdmin, &$sent, &$skipped, &$failed) {
                foreach ($applications as $application) {
                    $outcome = $this->dispatch(
                        $application,
                        $validated['subject'],
                        $validated['message'],
                        InactivityMail::TYPE_BULK,
                        $currentAdmin,
                        false
                    );

                    if ($outcome === 'sent') {
                        $sent++;
                    } elseif ($outcome === 'skipped') {
                        $skipped++;
                    } else {
                        $failed++;
                    }
                }
            });

        $parts = ["{$sent} reminder(s) sent"];
        if ($skipped) {
            $parts[] = "{$skipped} skipped (no author email on file)";
        }
        if ($failed) {
            $parts[] = "{$failed} failed";
        }

        // Redirect so the section totals at the top of the page reflect the
        // send that just happened.
        return successResponse(
            implode(', ', $parts) . '.',
            route('admin.application.index', ['type' => 'inactive'])
        );
    }

    /**
     * Mail a single manuscript owner from that row's action button.
     */
    public function sendMail(Request $request, $uuid)
    {
        $this->authorize();

        $application = Application::where('uuid', $uuid)->with('author')->first();
        if (!$application) {
            return errorResponse('Application not found');
        }

        $validated = $this->validateMail($request);
        if ($validated === false) {
            return false;
        }

        $outcome = $this->dispatch(
            $application,
            $validated['subject'],
            $validated['message'],
            InactivityMail::TYPE_INDIVIDUAL,
            authUser('admin')
        );

        if ($outcome === 'skipped') {
            return errorResponse('This manuscript has no author email on file.');
        }

        if ($outcome === 'failed') {
            return errorResponse('The reminder could not be sent. Please check the mail configuration.');
        }

        return successResponse('Reminder sent to ' . ($application->author->email ?? 'the manuscript owner') . '.');
    }

    /**
     * GET returns the compose form for one manuscript (AJAX modal body).
     */
    public function sendMailForm(Request $request, $uuid)
    {
        $this->authorize();

        $application = Application::where('uuid', $uuid)
            ->with('author', 'handoverTimeline')
            ->withCount('inactivityMails')
            ->first();

        if (!$application) {
            return errorResponse('Application not found');
        }

        return view('admin.application.inactive.sendMailForm', compact('application'));
    }

    // ── Internals ────────────────────────────────────────────────────────────

    /**
     * The sidebar hides this section for non-admins, so guard the endpoints
     * too — the sidebar gate is cosmetic. Mirrors the isSuperAdmin() abort
     * used by change_status().
     */
    private function authorize()
    {
        abort_unless(canViewInactiveApplications(), 403, 'Unauthorized action.');
    }

    private function validateMail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'message' => 'nullable|string|max:5000',
        ], [
            'subject.required' => 'A subject is required.',
        ]);

        if ($validator->fails()) {
            return failureResponse($validator);
        }

        return $validator->validated();
    }

    /**
     * Send one reminder and record it. Returns 'sent', 'skipped' or 'failed'.
     *
     * Note: InactivityReminderMail implements ShouldQueue, so this records a
     * successful *dispatch*. Real SMTP failures surface in failed_jobs.
     *
     * A timeline entry is written only when $logTimeline is true. Bulk sends
     * skip it so a single bulk action cannot flood every manuscript's history
     * with one near-identical entry; the InactivityMail ledger still records
     * each send. Individual sends keep the timeline entry as an audit trail.
     *
     * @return string
     */
    private function dispatch($application, $subject, $message, $type, $currentAdmin, $logTimeline = true)
    {
        $author = $application->author;

        if (!$author || empty($author->email)) {
            return 'skipped';
        }

        $url = route('author.application.show', $application->uuid);

        try {
            Mail::to($author->email)->send(new InactivityReminderMail(
                $application,
                $author->name ?? null,
                $subject,
                $message,
                $url,
                $application->waiting_days
            ));

            $this->notifications->send($author, [
                'category' => 'application',
                'type' => 'application.inactivity-reminder',
                'title' => $subject,
                'message' => $message ?: ('Your manuscript ' . $this->applicationReference($application) . ' is waiting for your action.'),
                'url' => $url,
                'icon' => 'ri-time-line',
                'severity' => 'warning',
            ]);

            InactivityMail::create([
                'application_id' => $application->id,
                'author_id'      => $author->id,
                'author_email'   => $author->email,
                'type'           => $type,
                'subject'        => $subject,
                'message'        => $message,
                'sent_by'        => $currentAdmin?->id,
                'status'         => InactivityMail::STATUS_SENT,
                'sent_at'        => now(),
            ]);

            if ($logTimeline) {
                ApplicationTimeline::create([
                    'application_id'        => $application->id,
                    'status'                => 'inactivity reminder sent',
                    'is_visible_to_author'  => 0,
                    'assigned_to_id'        => $currentAdmin?->id,
                    'remark'                => "Inactivity reminder sent ({$type}) to {$author->email}.",
                    'data'                  => [
                        'comment' => $message,
                        'type'    => $type,
                    ],
                ]);
            }

            return 'sent';
        } catch (\Exception $e) {
            Log::error('Inactivity reminder failed for application ' . $application->id . ': ' . $e->getMessage());

            InactivityMail::create([
                'application_id' => $application->id,
                'author_id'      => $author->id,
                'author_email'   => $author->email,
                'type'           => $type,
                'subject'        => $subject,
                'message'        => $message,
                'sent_by'        => $currentAdmin?->id,
                'status'         => InactivityMail::STATUS_FAILED,
                'error'          => $e->getMessage(),
                'sent_at'        => now(),
            ]);

            return 'failed';
        }
    }

    private function search($request, $query)
    {
        if ($request->filled('search')) {
            $searchTerm = $request->search;

            $query->where(function ($q) use ($searchTerm) {
                $q->where('application_id', 'like', "%{$searchTerm}%")
                    ->orWhere('title', 'like', "%{$searchTerm}%")
                    ->orWhereHas('author', function ($a) use ($searchTerm) {
                        $a->where('name', 'like', "%{$searchTerm}%")
                            ->orWhere('email', 'like', "%{$searchTerm}%");
                    });
            });
        }
    }

    private function sort($request, $query)
    {
        if (!empty($request->sortColumn)) {
            $query->orderBy($request->sortColumn, "{$request->sortDirection}");
        } else {
            $query->orderBy('updated_at', 'desc');
        }
    }
}
