<?php

namespace App\Models;

use App\Models\Author;
use App\Traits\HasUuid;
use App\Models\ApplicationTimeline;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Application extends Model
{
    use HasFactory, HasUuid;
    protected $guarded = ['id'];

    /**
     * Statuses where the manuscript is sitting with the author, waiting on
     * the author to act. Both are set together with a matching
     * application_timelines row, which is what the inactivity clock reads.
     */
    public const INACTIVE_STATUSES = ['revise', 'proofreading'];

    /** A manuscript is "inactive" once the author has had this long to act. */
    public const INACTIVE_AFTER_DAYS = 21;

    /**
     * Statuses where a manuscript is out with its reviewers. The deadline
     * tracker only watches manuscripts in one of these states.
     */
    public const REVIEW_ACTIVE_STATUSES = ['assigning reviewers', 'under review', 're-review'];

    /** Terminal manuscript statuses shown in the Closed tab. */
    public const CLOSED_STATUSES = ['rejected', 'published', 'withdrawn', 'duplicate'];

    /**
     * The technical editor has signed off on the author's corrected proof.
     * The manuscript now waits on the publisher to add the DOI and publish it.
     */
    public const ACCEPTED_FOR_PUBLICATION_STATUS = 'accepted for publication';

    /** Timeline rows proving a reviewer finished their review. */
    public const REVIEW_COMPLETED_STATUSES = [
        'internal-reviewer-reviewed',
        'external-reviewer-reviewed',
    ];

    /** Timeline rows proving a handling editor finished their decision. */
    public const EDITOR_COMPLETED_STATUSES = [
        'editor-accepts',
        'editor-revise',
        'editor-rejects',
    ];

    /** Timeline rows proving an admin finished a decision-style role step. */
    public const ADMIN_COMPLETED_STATUSES = [
        'technical checks approved',
        'pre-accepted',
        'rejected',
        'revise',
        'out of scope',
        'chief-response',
        self::ACCEPTED_FOR_PUBLICATION_STATUS,
    ];

    /** Timeline rows where the acting admin is recorded as the assignee. */
    public const ADMIN_ASSIGNEE_COMPLETED_STATUSES = [
        'typesetting review',
        'published',
    ];

    protected $casts = [
        'article_type' => 'array',
        'co_author_data' => 'array',
        'data' => 'array',
        'article_check' => 'array',
    ];

    public function scopeUuid($query, $uuid)
    {
        return $query->where('uuid', $uuid);
    }

    public function author()
    {
        return $this->belongsTo(Author::class, 'author_id', 'id');
    }

    // public function getFileSizeMbAttribute()
    // {
    //     return round($this->file_size / (1024 * 1024), 2) . ' MB';
    // }

    public function timelines()
    {
        return $this->hasMany(ApplicationTimeline::class, 'application_id', 'id')->orderBy('created_at', 'desc');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'application_id', 'id');
    }

    public function isReadyToSubmit()
    {
        $requiredKeys = collect(["First Page", "Cover Letter", "Upload Manuscript", "Upload ICMJE Declaration", "Plagiarism & AI Check Report"]);

        $attachmentKeys = $this->attachments()->pluck('attachment_type')->toArray();

        return $requiredKeys->diff($attachmentKeys)->isEmpty();
    }

    public function assignee()
    {
        return $this->belongsTo(Admin::class, 'assigned_to_id', 'id');
    }

    public function externalAssignee()
    {
        return $this->belongsTo(Admin::class, 'assigned_to_ex_id', 'id');
    }

    public function inactivityMails()
    {
        return $this->hasMany(InactivityMail::class, 'application_id', 'id');
    }

    public function reviewAssignments()
    {
        return $this->hasMany(ReviewAssignment::class, 'application_id', 'id');
    }

    /**
     * The timeline row marking the most recent hand-off to the author.
     * Eager-load with ->with('handoverTimeline') to avoid N+1.
     */
    public function handoverTimeline()
    {
        return $this->hasOne(ApplicationTimeline::class, 'application_id', 'id')
            ->whereIn('status', self::INACTIVE_STATUSES)
            ->latestOfMany('created_at');
    }

    /**
     * Manuscripts that are sitting with the author and have been ignored for
     * at least INACTIVE_AFTER_DAYS (3 weeks).
     *
     * The clock is the newest application_timelines row whose status matches
     * the application's current status, i.e. the exact moment the manuscript
     * was handed over. That deliberately ignores applications.updated_at,
     * which any unrelated admin edit (adding a DOI, reassigning, editing
     * metadata) would bump and thereby silently reset.
     *
     * Membership is self-clearing: as soon as the author acts, the status
     * moves to 'resubmitted' (or a corrected proof is uploaded) and the
     * manuscript drops out of this scope. No scheduled job required.
     */
    public function scopeInactive($query, ?int $days = null)
    {
        $cutoff = now()
            ->subDays($days ?? self::INACTIVE_AFTER_DAYS)
            ->format('Y-m-d H:i:s');

        $statuses = self::INACTIVE_STATUSES;

        return $query->whereIn('status', $statuses)
            ->where(function ($q) use ($statuses, $cutoff) {
                $q->where(function ($matched) use ($statuses, $cutoff) {
                    $matched->whereIn('id', function ($sub) use ($statuses, $cutoff) {
                        $sub->select('application_id')
                            ->from('application_timelines')
                            ->whereColumn('application_timelines.application_id', 'applications.id')
                            ->whereIn('application_timelines.status', $statuses)
                            ->groupBy('application_id')
                            ->havingRaw('MAX(created_at) <= ?', [$cutoff]);
                    });
                })->orWhere(function ($fallback) use ($statuses, $cutoff) {
                    // Legacy rows recorded before the timeline convention.
                    $fallback->whereNotExists(function ($sub) use ($statuses) {
                        $sub->selectRaw('1')
                            ->from('application_timelines')
                            ->whereColumn('application_timelines.application_id', 'applications.id')
                            ->whereIn('application_timelines.status', $statuses);
                    })->where('applications.updated_at', '<=', $cutoff);
                });
            });
    }

    /**
     * When the manuscript was last handed to the author. Null when it has
     * never been in an author-waiting status, or has no timeline row for it.
     */
    public function getHandedOverAtAttribute()
    {
        $timeline = $this->relationLoaded('handoverTimeline')
            ? $this->getRelation('handoverTimeline')
            : $this->handoverTimeline()->first();

        return $timeline?->created_at;
    }

    /**
     * How long the manuscript has been waiting on the author, in whole days.
     * Null when the manuscript is not in an author-waiting status.
     */
    public function getWaitingDaysAttribute()
    {
        if (!in_array($this->status, self::INACTIVE_STATUSES, true)) {
            return null;
        }

        $since = $this->handed_over_at ?? $this->updated_at;

        if (!$since) {
            return null;
        }

        // Whole elapsed 24h periods. Done with raw timestamps rather than
        // diffInDays() because that helper's return sign flipped between
        // Carbon 2 and 3, which silently zeroed this out.
        return max(0, (int) floor((now()->getTimestamp() - $since->getTimestamp()) / 86400));
    }

    /**
     * Manuscripts the staff member has touched in any role. The pointer
     * columns cover the current assignee; the timeline columns keep the
     * manuscript visible after a later workflow step clears the pointer.
     */
    public function scopeInvolvedWith($query, $admin)
    {
        $userId = $admin->id ?? null;

        if (!$userId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function ($q) use ($userId) {
            $q->where('assigned_to_id', $userId)
                ->orWhere('assigned_to_ex_id', $userId)
                ->orWhereHas('timelines', function ($timeline) use ($userId) {
                    $timeline->where(function ($inner) use ($userId) {
                        $inner->where('assigned_to_id', $userId)
                            ->orWhere('assigned_by', $userId);
                    });
                });
        });
    }

    /**
     * Work the person has actually finished:
     * - a reviewer submitted a review,
     * - a handling editor recorded the final decision,
     * - an admin completed a technical/assistant/chief step, or
     * - a publisher completed typesetting/publishing.
     *
     * Published manuscripts belong only to the Closed tab, even when the same
     * person also performed a completed role step earlier in the workflow.
     *
     * If the same person is also the handling editor, a submitted review is
     * not enough: their editor decision must exist first.
     */
    public function scopeCompletedFor($query, $admin)
    {
        $userId = $admin->id ?? null;

        if (!$userId) {
            return $query->whereRaw('1 = 0');
        }

        $reviewStatuses = self::REVIEW_COMPLETED_STATUSES;
        $editorStatuses = self::EDITOR_COMPLETED_STATUSES;
        $adminStatuses = self::ADMIN_COMPLETED_STATUSES;
        $assigneeStatuses = self::ADMIN_ASSIGNEE_COMPLETED_STATUSES;
        $isPublisher = method_exists($admin, 'hasRole') && $admin->hasRole('Publisher');

        $query->where('status', '!=', 'published');

        // A publisher's finished task should leave Completed as soon as the
        // manuscript comes back to them for the next action (for example
        // publication after typesetting). Otherwise the same manuscript shows
        // in both Active and Completed.
        if ($isPublisher) {
            $query->where(function ($assigned) use ($userId) {
                $assigned->whereNull('assigned_to_id')
                    ->orWhere('assigned_to_id', '!=', $userId);
            });
        }

        return $query->where(function ($q) use ($userId, $reviewStatuses, $editorStatuses, $adminStatuses, $assigneeStatuses) {
            $q->whereHas('timelines', function ($timeline) use ($userId, $editorStatuses) {
                $timeline->whereIn('status', $editorStatuses)
                    ->where('assigned_by', $userId);
            })->orWhere(function ($reviewed) use ($userId, $reviewStatuses) {
                $reviewed->whereHas('timelines', function ($timeline) use ($userId, $reviewStatuses) {
                    $timeline->whereIn('status', $reviewStatuses)
                        ->where('assigned_by', $userId);
                })->whereDoesntHave('timelines', function ($timeline) use ($userId) {
                    $timeline->where('status', 'processing')
                        ->where('assigned_to_id', $userId);
                });
            })->orWhereHas('timelines', function ($timeline) use ($userId, $adminStatuses) {
                $timeline->whereIn('status', $adminStatuses)
                    ->where('assigned_by', $userId);
            })->orWhereHas('timelines', function ($timeline) use ($userId, $assigneeStatuses) {
                $timeline->whereIn('status', $assigneeStatuses)
                    ->where('assigned_to_id', $userId);
            });
        })->involvedWith($admin);
    }

    /**
     * The staff member's live queue. Anything already completed or closed is
     * deliberately absent. Published manuscripts belong to Closed for
     * non-admin staff, while pre-accepted/accepted leave the active queue.
     */
    public function scopeActiveFor($query, $admin)
    {
        $userId = $admin->id ?? null;

        if (!$userId) {
            return $query->whereRaw('1 = 0');
        }

        // Publishers only own the manuscript while it is assigned to them.
        // Timeline history from an earlier typesetting step must not keep the
        // manuscript in their Active list after it has moved on.
        if (method_exists($admin, 'hasRole') && $admin->hasRole('Publisher')) {
            return $query->where('assigned_to_id', $userId)
                ->whereNotIn('status', ['incomplete', 'published', 'rejected', 'withdrawn', 'duplicate']);
        }

        $reviewStatuses = self::REVIEW_COMPLETED_STATUSES;
        $editorStatuses = self::EDITOR_COMPLETED_STATUSES;
        $acceptedForPublication = self::ACCEPTED_FOR_PUBLICATION_STATUS;

        return $query->whereNotIn('status', [
            'incomplete',
            'published',
            'rejected',
            'withdrawn',
            'duplicate',
            'pre-accepted',
            'accepted',
        ])->whereDoesntHave('timelines', function ($timeline) use ($userId, $acceptedForPublication) {
            // Once the technical editor accepts, the task belongs to the
            // publisher. The acceptance row remains in the tech editor's
            // Completed tab because it records work they performed.
            $timeline->where('status', $acceptedForPublication)
                ->where('assigned_by', $userId);
        })->where(function ($q) use ($userId, $reviewStatuses, $editorStatuses) {
            $q->whereDoesntHave('timelines', function ($timeline) use ($userId, $editorStatuses) {
                $timeline->whereIn('status', $editorStatuses)
                    ->where('assigned_by', $userId);
            })->where(function ($remaining) use ($userId, $reviewStatuses) {
                $remaining->whereDoesntHave('timelines', function ($timeline) use ($userId, $reviewStatuses) {
                    $timeline->whereIn('status', $reviewStatuses)
                        ->where('assigned_by', $userId);
                })->orWhereHas('timelines', function ($timeline) use ($userId) {
                    $timeline->where('status', 'processing')
                        ->where('assigned_to_id', $userId);
                });
            });
        })->involvedWith($admin);
    }

    /**
     * Terminal manuscripts. Admins see every terminal manuscript; staff see
     * only terminal manuscripts they handled in some role.
     */
    public function scopeClosedFor($query, $admin)
    {
        $query->whereIn('status', self::CLOSED_STATUSES);

        if (($admin->is_admin ?? 0) == 1) {
            return $query;
        }

        return $query->involvedWith($admin);
    }

    /**
     * Personal queue shown to admin roles. Superadmins get the chief-approval
     * queue; every other admin sees only manuscripts whose assigned_to_id is
     * their own id.
     */
    public function scopeMyManuscripts($query, $admin)
    {
        $userId = $admin->id ?? null;

        if (!$userId) {
            return $query->whereRaw('1 = 0');
        }

        // The superadmin's queue is the chief-approval queue only. They should
        // not see manuscripts merely because they happen to be assigned to
        // them, otherwise the queue no longer means "needs my approval".
        if (method_exists($admin, 'hasRole') && $admin->hasRole('superadmin')) {
            return $query->pendingFor($admin);
        }

        // Every other admin sees only manuscripts directly assigned to them.
        // Pending-work rules and timeline history belong in the other tabs.
        return $query->whereNotIn('status', ['incomplete', 'published', 'rejected', 'withdrawn', 'duplicate'])
            ->where('assigned_to_id', $userId);
    }

    public function scopePendingFor($query, $admin)
    {
        $userId = $admin->id ?? null;
        $isAdmin = $admin->is_admin == 1;
        $revType = $admin->rev_type ?? null;
        $isSuperAdmin = method_exists($admin, 'hasRole') && $admin->hasRole('superadmin');

        $query->whereNotIn('status', ['incomplete', 'published', 'rejected', 'withdrawn', 'duplicate']);

        if ($revType === 'external') {
            $query->where('assigned_to_ex_id', $userId);
        } elseif (!$isAdmin) {
            $query->where(function ($q) use ($userId) {
                $q->where('assigned_to_id', $userId)
                    ->orWhereHas('timelines', function ($t) use ($userId) {
                        $t->where('assigned_to_id', $userId);
                    });
            });
        }

        $query->where(function ($q) use ($admin, $userId, $isSuperAdmin, $revType) {

            // SUPERADMIN / CHIEF: only chief decisions pending
            if ($isSuperAdmin) {
                $q->where(function ($sub) {
                    $sub->whereIn('status', ['chief-review', 'chief-approval', 'editor-accepts', 'editor-rejects', 'editor-revise'])
                        ->where('data->status', 'sended to chief approval');
                });
                return;
            }

            $isHandlingEditor = function ($sub) use ($userId) {
                $sub->whereHas('timelines', function ($t) use ($userId) {
                    $t->where('status', 'processing')->where('assigned_to_id', $userId);
                });
            };

            $nullOrNotIn = function ($column, array $values) {
                return function ($sub) use ($column, $values) {
                    $sub->whereNull($column)->orWhereNotIn($column, $values);
                };
            };

            $q->orWhere(function ($sub) use ($isHandlingEditor) {
                $sub->where('status', 'processing')
                    ->whereNull('data->handling_editor_decision')
                    ->where($isHandlingEditor);
            })->orWhere(function ($sub) use ($isHandlingEditor) {
                $sub->where('status', 'processing')
                    ->where('data->handling_editor_decision', 1)
                    ->where($isHandlingEditor);
            })->orWhere(function ($sub) use ($isHandlingEditor) {
                $sub->whereIn('status', ['assigning reviewers', 'under review', 're-review'])
                    ->where($isHandlingEditor);
            })->orWhere(function ($sub) use ($isHandlingEditor) {
                $sub->where('status', 'reviewed')
                    ->where($isHandlingEditor);
            });

            if ($revType === 'internal') {
                $q->orWhere(function ($sub) use ($userId, $nullOrNotIn) {
                    $sub->whereIn('status', ['assigning reviewers', 're-review'])
                        ->whereNull('accept_in')
                        ->where('assigned_to_id', $userId);
                })->orWhere(function ($sub) use ($userId, $nullOrNotIn) {
                    $sub->whereIn('status', ['assigning reviewers', 'under review', 're-review'])
                        ->where('accept_in', 1)
                        ->where($nullOrNotIn('status_ex', [1, 3]))
                        ->where('assigned_to_id', $userId);
                });
            }

            if ($revType === 'external') {
                $q->orWhere(function ($sub) use ($userId) {
                    $sub->whereIn('status', ['assigning reviewers', 're-review'])
                        ->whereNull('accept_ex')
                        ->where('assigned_to_ex_id', $userId);
                })->orWhere(function ($sub) use ($userId, $nullOrNotIn) {
                    $sub->whereIn('status', ['assigning reviewers', 'under review', 're-review'])
                        ->where('accept_ex', 1)
                        ->where($nullOrNotIn('status_ex', [2, 3]))
                        ->where('assigned_to_ex_id', $userId);
                });
            }

            if ($admin->can('application.techEditorDecision')) {
                $q->orWhere(function ($sub) {
                    $sub->whereIn('status', ['submitted', 'resubmitted'])
                        ->whereDoesntHave('timelines', function ($t) {
                            $t->where('status', 'technical checks approved');
                        });
                });
            }

            if ($admin->can('application.assistantEditorFinalDecision')) {
                $q->orWhere(function ($sub) {
                    $sub->where(function ($in) {
                        $in->whereIn('status', ['editor-accepts', 'editor-rejects'])
                            ->where('data->status', 'redirected to assistant editor');
                    })->orWhere('status', 'editor-revise')
                        ->orWhere('status', 'chief-response');
                });
            }

            if ($admin->can('application.assistantToChiefForApproval')) {
                $q->orWhere(function ($sub) {
                    $sub->whereIn('status', ['editor-accepts', 'editor-rejects'])
                        ->where(function ($in) {
                            $in->whereNull('data->status')
                                ->orWhereNotIn('data->status', ['sended to chief approval', 'redirected to assistant editor']);
                        });
                });
            }

            if ($admin->can('application.chiefToAssistanResponse')) {
                $q->orWhere(function ($sub) {
                    $sub->whereIn('status', ['chief-review', 'chief-approval', 'editor-accepts', 'editor-rejects'])
                        ->where('data->status', 'sended to chief approval');
                });
            }

            if ($admin->can('application.publish')) {
                $q->orWhere(function ($sub) use ($userId) {
                    $sub->where('status', 'accepted')
                        ->whereHas('timelines', function ($t) use ($userId) {
                            $t->where('status', 'accepted')->where('assigned_to_id', $userId);
                        });
                });
            }

            if ($admin->can('application.add_doi')) {
                $q->orWhere(function ($sub) {
                    $sub->whereNull('published_doi')
                        ->whereHas('timelines', function ($t) {
                            $t->where('status', 'accepted');
                        });
                });
            }
        });

        return $query;
    }
}
