@forelse ($assignments as $assignment)
    @php
        $application = $assignment->application;
        $reviewer = $assignment->reviewer;
        $number = $assignment->nextReminderNumber();
        $nextDue = $assignment->nextReminderDueAt() ?? $assignment->timeout_at;
        $remindersSent = ($assignment->reminder_1_sent_at ? 1 : 0) + ($assignment->reminder_2_sent_at ? 1 : 0);
    @endphp
    <tr>
        <td>{{ $loop->iteration }}</td>

        <td>
            @if ($application)
                <a class="d-flex flex-column" href="{{ route('admin.application.show', $application->uuid ?? '') }}">
                    <span class="text-dark fw-bold">{{ Str::limit($application->title, 24, '...') }}</span>
                    <span class="text-muted">{{ $application->application_id ?? '---' }}</span>
                </a>
            @else
                <span class="text-muted">---</span>
            @endif
        </td>

        <td>
            <div class="d-flex flex-column">
                <span>{{ $reviewer->name ?? '---' }}</span>
                @if ($reviewer?->email)
                    <span class="fs-12 text-muted">{{ $reviewer->email }}</span>
                @else
                    <span class="fs-12 text-danger">No email on file</span>
                @endif
            </div>
        </td>

        <td>
            <span class="badge badge-sm badge-rounded {{ $assignment->type === 'external' ? 'bg-primary-subtle text-primary' : 'bg-info-subtle text-info' }}">
                {{ Str::ucfirst($assignment->type) }}
            </span>
            @if ($assignment->cycle > 1)
                <span class="fs-12 text-muted d-block">Replacement #{{ $assignment->cycle - 1 }}</span>
            @endif
        </td>

        <td>
            @if ($assignment->assigned_at)
                <span>{{ $assignment->assigned_at->format('d-M-Y') }}</span>
            @else
                <span class="text-muted">---</span>
            @endif
        </td>

        <td>
            <span class="badge badge-sm badge-rounded bg-secondary-subtle text-secondary">
                {{ $assignment->daysElapsed() }} day(s)
            </span>
        </td>

        <td>
            @if ($nextDue)
                <div class="d-flex flex-column">
                    <span>{{ \Illuminate\Support\Carbon::parse($nextDue)->format('d-M-Y') }}</span>
                    <span class="fs-12 text-muted">
                        {{ $number ? 'Reminder ' . $number : 'Timeout' }}
                    </span>
                </div>
            @else
                <span class="text-muted">---</span>
            @endif
        </td>

        <td>
            <span class="badge badge-sm badge-rounded {{ $assignment->stageBadgeClass() }}">
                {{ $assignment->stage() }}
            </span>
        </td>

        <td class="text-center">
            @if ($remindersSent > 0)
                <span class="badge badge-sm badge-rounded bg-primary-subtle text-primary">{{ $remindersSent }}/2</span>
            @else
                <span class="text-muted">0/2</span>
            @endif
        </td>

        <td>
            <div class="btn-group btn-group-sm">
                @if ($number && $reviewer?->email)
                    <button type="button" class="btn btn-sm btn-warning actionHandler"
                        data-handler="sendReviewerReminder"
                        data-url="{{ route('admin.application.review_deadline.remind', $assignment->id) }}"
                        data-reviewer="{{ $reviewer->email }}"
                        data-reminder="{{ $number }}"
                        title="Send reminder {{ $number }} to this reviewer">
                        <i class="ri-mail-send-line"></i>
                    </button>
                @else
                    <button type="button" class="btn btn-sm btn-light" disabled
                        title="{{ $reviewer?->email ? 'Both reminders already sent' : 'No email on file' }}">
                        <i class="ri-mail-send-line"></i>
                    </button>
                @endif

                @if ($application)
                    <a class="btn btn-sm btn-info" href="{{ route('admin.application.show', $application->uuid) }}"
                        title="Open manuscript">
                        <i class="ri-eye-line"></i>
                    </a>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="10" class="p-4 text-center">
            No overdue reviews. Nothing appears here until a reviewer passes their first reminder deadline.
        </td>
    </tr>
@endforelse
