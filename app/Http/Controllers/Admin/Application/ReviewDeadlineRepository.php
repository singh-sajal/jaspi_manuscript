<?php

namespace App\Http\Controllers\Admin\Application;

use App\Mail\ReviewerReminder;
use App\Mail\ReviewerTimeoutNotice;
use App\Models\Admin;
use App\Models\Application;
use App\Models\ApplicationTimeline;
use App\Models\ReviewAssignment;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Review Deadlines: tracks how long each reviewer has been sitting on a
 * manuscript and lets a handling editor send the two manual reminders and
 * notify themselves when a reviewer has timed out.
 *
 * Rows are kept fresh by sync(), which is called from the tab and from the
 * reviewers:sync-deadlines command.
 */
class ReviewDeadlineRepository
{
    public const INTERNAL_REMARKS = ['Assigned to Internal Reviewer', 'Internal Reviewer Reassigned'];
    public const EXTERNAL_REMARKS = ['Assigned to External Reviewer', 'External Reviewer Reassigned'];

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
     * The "Review Deadlines" tab. Lists only genuinely late assignments
     * (reminder #1 due or beyond).
     */
    public function index($request)
    {
        $this->authorize();

        // Keep the ledger in step with the live manuscripts before reading it.
        $this->sync();

        $perPage = $request->perPage ?? 50;
        $type    = $request->type ?? 'review-deadline';
        $user    = authUser('admin');

        $query = $this->scopedOverdueQuery($user)->with(['application', 'reviewer']);

        $this->search($request, $query);
        $this->sort($request, $query);

        $assignments = $query->paginate($perPage);

        if ($request->ajax()) {
            try {
                $datatable = view('admin.application.review-deadline.datatable', compact('assignments'))->render();

                return response()->json([
                    'status'         => '200',
                    'msg'            => 'Data loaded',
                    'data'           => $datatable,
                    'paginationInfo' => getPaginationInfo($assignments),
                ], 200);
            } catch (Throwable $th) {
                return throwException($th);
            }
        }

        $stats = $this->stats($user);

        return view('admin.application.review-deadline.index', compact('type', 'stats'));
    }

    /**
     * One-click manual reminder for a single reviewer. Sends the mail, records
     * it on the row and writes a non-author-visible timeline entry.
     */
    public function sendReminder(Request $request, $id)
    {
        $this->authorize();

        $assignment = ReviewAssignment::with(['application', 'reviewer'])->find($id);

        if (!$assignment) {
            return errorResponse('Review assignment not found.');
        }

        $application = $assignment->application;
        if (!$application) {
            return errorResponse('The manuscript for this review no longer exists.');
        }

        $this->authorizeApplication($application);

        if (!$assignment->isActive()) {
            return errorResponse('This review is no longer awaiting a response.');
        }

        $number = $assignment->nextReminderNumber();
        if ($number === null) {
            return errorResponse('Both reminders have already been sent to this reviewer.');
        }

        $reviewer = $assignment->reviewer;
        if (!$reviewer || empty($reviewer->email)) {
            return errorResponse('This reviewer has no email address on file.');
        }

        $currentAdmin = authUser('admin');
        $url          = route('admin.application.show', $application->uuid);
        $dueAt        = $assignment->nextReminderDueAt();

        try {
            Mail::to($reviewer->email)->send(new ReviewerReminder(
                $application,
                $reviewer,
                $assignment->type,
                $currentAdmin,
                $url,
                $number,
                $dueAt
            ));

            $this->notifications->send($reviewer, [
                'category' => 'review',
                'type' => 'review.reminder',
                'title' => 'Review reminder',
                'message' => 'Reminder ' . $number . ' for manuscript ' . $this->applicationReference($application) . '.',
                'url' => $url,
                'icon' => 'ri-time-line',
                'severity' => 'warning',
            ]);
        } catch (\Exception $e) {
            Log::error('Reviewer reminder failed for assignment ' . $assignment->id . ': ' . $e->getMessage());

            return errorResponse('The reminder could not be sent. Please check the mail configuration.');
        }

        if ($number === 1) {
            $assignment->reminder_1_sent_at = now();
        } else {
            $assignment->reminder_2_sent_at = now();
        }
        $assignment->save();

        ApplicationTimeline::create([
            'application_id'       => $application->id,
            'status'               => 'reviewer reminder sent',
            'assigned_to_id'       => $reviewer->id,
            'assigned_by'          => $currentAdmin?->id,
            'is_visible_to_author' => 0,
            'remark'               => "Reviewer reminder {$number} sent to {$reviewer->email}.",
            'data'                 => [
                'type'              => $assignment->type,
                'reminder_number'   => $number,
                'comment'           => "Reviewer reminder {$number} sent.",
            ],
        ]);

        return successResponse(
            "Reminder {$number} sent to {$reviewer->email}.",
            route('admin.application.index', ['type' => 'review-deadline'])
        );
    }

    /**
     * Notify the handling editor about every reviewer that has passed their
     * timeout deadline (once per assignment). Reviewers are NOT unassigned.
     *
     * @return int number of notifications dispatched
     */
    public function notifyTimeouts(): int
    {
        $this->sync();

        $due = ReviewAssignment::query()
            ->awaitingTimeoutNotice()
            ->with(['application', 'reviewer'])
            ->get();

        $notified = 0;

        foreach ($due as $assignment) {
            $application = $assignment->application;
            if (!$application) {
                continue;
            }

            $editor = $this->handlingEditorFor($application);
            $reviewer = $assignment->reviewer;

            if ($editor && !empty($editor->email)) {
                try {
                    Mail::to($editor->email)->send(new ReviewerTimeoutNotice(
                        $application,
                        $assignment,
                        $editor,
                        route('admin.application.show', $application->uuid)
                    ));
                } catch (\Exception $e) {
                    Log::error('Reviewer timeout notice failed for assignment ' . $assignment->id . ': ' . $e->getMessage());
                }

                $this->notifications->send($editor, [
                    'category' => 'review',
                    'type' => 'review.timeout',
                    'title' => 'Reviewer passed the deadline',
                    'message' => 'A reviewer passed the deadline for manuscript ' . $this->applicationReference($application) . '.',
                    'url' => route('admin.application.show', $application->uuid),
                    'icon' => 'ri-alarm-warning-line',
                    'severity' => 'danger',
                ]);
            }

            ApplicationTimeline::create([
                'application_id'       => $application->id,
                'status'               => 'reviewer timed out',
                'assigned_to_id'       => $assignment->admin_id,
                'assigned_by'          => $editor?->id,
                'is_visible_to_author' => 0,
                'remark'               => 'Reviewer ' . ($reviewer->name ?? $assignment->admin_id) . " passed the review deadline ({$assignment->type}).",
                'data'                 => [
                    'type'         => $assignment->type,
                    'timed_out_at' => optional($assignment->timeout_at)->toDateTimeString(),
                ],
            ]);

            $assignment->timeout_notified_at = now();
            $assignment->save();

            $notified++;
        }

        return $notified;
    }

    // ── Sync ─────────────────────────────────────────────────────────────────

    /**
     * Rebuild the deadline ledger from the assignment timelines. Idempotent:
     * existing rows keep their reminder/timeout timestamps.
     */
    public function sync(): void
    {
        $applications = Application::query()
            ->whereIn('status', Application::REVIEW_ACTIVE_STATUSES)
            ->get();

        $activeIds = $applications->pluck('id')->all();

        // Any active row whose manuscript has left the review stage is closed.
        $close = ReviewAssignment::query()->where('status', ReviewAssignment::STATUS_ACTIVE);
        if (empty($activeIds)) {
            $close->update(['status' => ReviewAssignment::STATUS_CLOSED]);
        } else {
            $close->whereNotIn('application_id', $activeIds)
                ->update(['status' => ReviewAssignment::STATUS_CLOSED]);
        }

        foreach ($applications as $application) {
            $this->syncApplication($application);
        }
    }

    private function syncApplication(Application $application): void
    {
        $slots = [
            ReviewAssignment::TYPE_INTERNAL => [
                'remarks'      => self::INTERNAL_REMARKS,
                'accept'       => $application->accept_in,
                'reviewStatus' => 'internal-reviewer-reviewed',
            ],
            ReviewAssignment::TYPE_EXTERNAL => [
                'remarks'      => self::EXTERNAL_REMARKS,
                'accept'       => $application->accept_ex,
                'reviewStatus' => 'external-reviewer-reviewed',
            ],
        ];

        foreach ($slots as $type => $slot) {
            $timeline = ApplicationTimeline::where('application_id', $application->id)
                ->whereIn('remark', $slot['remarks'])
                ->orderByDesc('id')
                ->first();

            if (!$timeline || !$timeline->assigned_to_id) {
                // No reviewer on record for this slot — close any dangling row.
                ReviewAssignment::where('application_id', $application->id)
                    ->where('type', $type)
                    ->where('status', ReviewAssignment::STATUS_ACTIVE)
                    ->update(['status' => ReviewAssignment::STATUS_CLOSED]);
                continue;
            }

            // How many assignment cycles this slot has had up to this one.
            $cycle = ApplicationTimeline::where('application_id', $application->id)
                ->whereIn('remark', $slot['remarks'])
                ->where('id', '<=', $timeline->id)
                ->count();

            $assignment = ReviewAssignment::firstOrNew(['timeline_id' => $timeline->id]);
            $assignment->application_id = $application->id;
            $assignment->admin_id       = $timeline->assigned_to_id;
            $assignment->type           = $type;
            $assignment->cycle          = max(1, (int) $cycle);
            $assignment->profile        = $assignment->cycle <= 1
                ? ReviewAssignment::PROFILE_LONG
                : ReviewAssignment::PROFILE_SHORT;
            $assignment->assigned_at    = $assignment->assigned_at ?? $timeline->created_at;

            $completed = ApplicationTimeline::where('application_id', $application->id)
                ->where('status', $slot['reviewStatus'])
                ->where('assigned_by', $timeline->assigned_to_id)
                ->where('id', '>', $timeline->id)
                ->exists();

            $declined = $slot['accept'] !== null && (int) $slot['accept'] === 0;

            if ($completed) {
                $assignment->status = ReviewAssignment::STATUS_SUBMITTED;
            } elseif ($declined) {
                $assignment->status = ReviewAssignment::STATUS_DECLINED;
            } else {
                $assignment->status = ReviewAssignment::STATUS_ACTIVE;
            }

            $assignment->save();

            // Older cycles for this slot are no longer the live assignment.
            ReviewAssignment::where('application_id', $application->id)
                ->where('type', $type)
                ->where('status', ReviewAssignment::STATUS_ACTIVE)
                ->where('id', '!=', $assignment->id)
                ->update(['status' => ReviewAssignment::STATUS_CLOSED]);
        }
    }

    // ── Queries / scope ──────────────────────────────────────────────────────

    private function scopedOverdueQuery($user)
    {
        $query = ReviewAssignment::query()
            ->overdue()
            ->whereHas('application', function ($q) {
                $q->whereIn('status', Application::REVIEW_ACTIVE_STATUSES);
            });

        if ($user && $user->is_admin == 1) {
            return $query;
        }

        // Handling editors only see manuscripts they are the handling editor of.
        $editorAppIds = ApplicationTimeline::where('status', 'processing')
            ->where('assigned_to_id', $user?->id)
            ->pluck('application_id')
            ->unique()
            ->all();

        return $query->whereIn('application_id', $editorAppIds ?: [0]);
    }

    public function stats($user): array
    {
        $base = $this->scopedOverdueQuery($user);

        $overdue  = (clone $base)->count();
        $timedOut = (clone $base)->whereNotNull('timeout_at')->where('timeout_at', '<=', now())->count();
        $reminded = (clone $base)->whereNotNull('reminder_1_sent_at')->count();

        return [
            'overdue'   => $overdue,
            'timed_out' => $timedOut,
            'reminded'  => $reminded,
        ];
    }

    /** Overdue count for the sidebar badge (scoped to the user). */
    public static function overdueCountFor($user): int
    {
        return app(self::class)->scopedOverdueQuery($user)->count();
    }

    public function handlingEditorFor(Application $application): ?Admin
    {
        $editorId = ApplicationTimeline::where('application_id', $application->id)
            ->where('status', 'processing')
            ->orderByDesc('id')
            ->value('assigned_to_id');

        return $editorId ? Admin::find($editorId) : null;
    }

    /**
     * Whether the given admin may open the tab at all: admins and handling
     * editors only.
     */
    public static function isHandlingEditor($user): bool
    {
        if (!$user) {
            return false;
        }
        if ($user->is_admin == 1) {
            return true;
        }

        return ApplicationTimeline::where('status', 'processing')
            ->where('assigned_to_id', $user->id)
            ->exists();
    }

    /**
     * The explicit permission keeps roles such as Technical Editor out of the
     * tab even if they happen to be an admin or a handling editor.
     */
    public static function canView($user): bool
    {
        return $user
            && $user->can('application.reviewDeadline')
            && self::isHandlingEditor($user);
    }

    // ── Internals ────────────────────────────────────────────────────────────

    private function authorize(): void
    {
        abort_unless(self::canView(authUser('admin')), 403, 'Unauthorized action.');
    }

    /**
     * Admins may act on anything; a handling editor only on their own manuscripts.
     */
    private function authorizeApplication(Application $application): void
    {
        $user = authUser('admin');

        if ($user && $user->is_admin == 1) {
            return;
        }

        $editor = $this->handlingEditorFor($application);
        abort_unless($editor && (int) $editor->id === (int) $user?->id, 403, 'Unauthorized action.');
    }

    private function search($request, $query)
    {
        if ($request->filled('search')) {
            $searchTerm = $request->search;

            $query->where(function ($q) use ($searchTerm) {
                $q->whereHas('application', function ($a) use ($searchTerm) {
                    $a->where('application_id', 'like', "%{$searchTerm}%")
                        ->orWhere('title', 'like', "%{$searchTerm}%");
                })->orWhereHas('reviewer', function ($r) use ($searchTerm) {
                    $r->where('name', 'like', "%{$searchTerm}%")
                        ->orWhere('email', 'like', "%{$searchTerm}%");
                });
            });
        }
    }

    private function sort($request, $query)
    {
        $allowed = ['assigned_at', 'reminder_1_at', 'reminder_2_at', 'timeout_at', 'status', 'type'];

        if (!empty($request->sortColumn) && in_array($request->sortColumn, $allowed, true)) {
            $query->orderBy($request->sortColumn, $request->sortDirection === 'asc' ? 'asc' : 'desc');
        } else {
            // Oldest (most overdue) first by default.
            $query->orderBy('reminder_1_at', 'asc');
        }
    }
}
