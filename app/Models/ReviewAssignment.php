<?php

namespace App\Models;

use App\Models\Admin;
use App\Models\Application;
use App\Models\ApplicationTimeline;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * A single reviewer assignment cycle and its cumulative deadline schedule.
 *
 * @see database/migrations/2026_10_15_000000_create_review_assignments_table.php
 */
class ReviewAssignment extends Model
{
    use HasFactory, HasUuid;

    public const TYPE_INTERNAL = 'internal';
    public const TYPE_EXTERNAL = 'external';

    public const PROFILE_LONG  = 'long';
    public const PROFILE_SHORT = 'short';

    /**
     * Cumulative day offsets from the assignment date.
     * [0] = first reminder, [1] = second reminder, [2] = timeout deadline.
     *
     * The first reviewer for a slot gets the long window; every replacement
     * reviewer gets the short one. Hard-coded on purpose (not configurable).
     */
    public const PROFILE_DAYS = [
        self::PROFILE_LONG  => [14, 7, 3],
        self::PROFILE_SHORT => [7, 3, 2],
    ];

    public const STATUS_ACTIVE    = 'active';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_DECLINED  = 'declined';
    public const STATUS_CLOSED    = 'closed';

    protected $guarded = ['id'];

    protected $casts = [
        'cycle'                => 'integer',
        'assigned_at'          => 'datetime',
        'reminder_1_at'        => 'datetime',
        'reminder_2_at'        => 'datetime',
        'timeout_at'           => 'datetime',
        'reminder_1_sent_at'   => 'datetime',
        'reminder_2_sent_at'   => 'datetime',
        'timeout_notified_at'  => 'datetime',
    ];

    protected static function booted()
    {
        // Keep the derived deadlines in step with assigned_at + profile.
        static::saving(function (ReviewAssignment $assignment) {
            $assignment->refreshDeadlines();
        });
    }

    // ── Relations ────────────────────────────────────────────────────────────

    public function application()
    {
        return $this->belongsTo(Application::class, 'application_id', 'id');
    }

    public function reviewer()
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'id');
    }

    public function timeline()
    {
        return $this->belongsTo(ApplicationTimeline::class, 'timeline_id', 'id');
    }

    // ── Schedule ─────────────────────────────────────────────────────────────

    public function profileDays(): array
    {
        return self::PROFILE_DAYS[$this->profile] ?? self::PROFILE_DAYS[self::PROFILE_LONG];
    }

    /**
     * Recompute reminder_1_at / reminder_2_at / timeout_at from assigned_at.
     */
    public function refreshDeadlines(): void
    {
        $base = $this->assigned_at ? Carbon::parse($this->assigned_at) : now();
        $this->assigned_at = $this->assigned_at ?? $base;

        [$first, $second, $third] = $this->profileDays();

        $this->reminder_1_at = $base->copy()->addDays($first);
        $this->reminder_2_at = $base->copy()->addDays($first + $second);
        $this->timeout_at    = $base->copy()->addDays($first + $second + $third);
    }

    // ── State helpers ────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isTimedOut(): bool
    {
        return $this->timeout_at && now()->greaterThanOrEqualTo($this->timeout_at);
    }

    /** The next reminder still to be sent (1, 2) or null when both are done. */
    public function nextReminderNumber(): ?int
    {
        if ($this->reminder_1_sent_at === null) {
            return 1;
        }
        if ($this->reminder_2_sent_at === null) {
            return 2;
        }

        return null;
    }

    public function nextReminderDueAt(): ?Carbon
    {
        return match ($this->nextReminderNumber()) {
            1       => $this->reminder_1_at,
            2       => $this->reminder_2_at,
            default => null,
        };
    }

    public function isReminderDue(): bool
    {
        $due = $this->nextReminderDueAt();

        return $due !== null && now()->greaterThanOrEqualTo($due);
    }

    public function daysElapsed(): int
    {
        if (!$this->assigned_at) {
            return 0;
        }

        return max(0, (int) floor((now()->getTimestamp() - $this->assigned_at->getTimestamp()) / 86400));
    }

    /** Human label for the row's current escalation stage. */
    public function stage(): string
    {
        if (!$this->isActive()) {
            return ucfirst($this->status);
        }
        if ($this->isTimedOut()) {
            return 'Timed out';
        }
        if ($this->isReminderDue()) {
            return 'Reminder ' . $this->nextReminderNumber() . ' due';
        }
        if ($this->reminder_2_sent_at) {
            return 'Reminder 2 sent';
        }
        if ($this->reminder_1_sent_at) {
            return 'Reminder 1 sent';
        }

        return 'Waiting';
    }

    public function stageBadgeClass(): string
    {
        return match ($this->stage()) {
            'Timed out'                            => 'bg-danger-subtle text-danger',
            'Reminder 1 due', 'Reminder 2 due'     => 'bg-warning-subtle text-warning',
            'Reminder 1 sent', 'Reminder 2 sent'   => 'bg-info-subtle text-info',
            default                                => 'bg-secondary-subtle text-secondary',
        };
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Genuinely late: the first reminder is due (or already overdue), the
     * reviewer has not acted and their slot has not moved on.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->whereNotNull('reminder_1_at')
            ->where('reminder_1_at', '<=', now());
    }

    /** Timed out and the handling editor has not been told yet. */
    public function scopeAwaitingTimeoutNotice($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->whereNotNull('timeout_at')
            ->where('timeout_at', '<=', now())
            ->whereNull('timeout_notified_at');
    }
}
