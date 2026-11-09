@forelse ($applications as $application)
    @php
        $waitingDays = $application->waiting_days;
        $handedOverAt = $application->handed_over_at;
        $reminders = $application->inactivity_mails_count ?? 0;
        $lastSent = $application->inactivity_mails_max_sent_at ?? null;
    @endphp
    <tr>
        <td>{{ $loop->iteration }}</td>

        <td>
            <a class="d-flex flex-column" href="{{ route('admin.application.show', $application->uuid ?? '') }}">
                <span class="text-dark fw-bold">{{ Str::limit($application->title, 20, '...') }}</span>
                <span class="text-muted">{{ $application->application_id ?? '---' }}</span>
            </a>
        </td>

        <td>
            <div class="d-flex flex-column">
                <span>{{ $application->author->name ?? ($application->author_name ?? '---') }}</span>
                @if ($application->author?->email)
                    <span class="fs-12 text-muted">{{ $application->author->email }}</span>
                @else
                    <span class="fs-12 text-danger">No email on file</span>
                @endif
            </div>
        </td>

        <td>
            <span class="badge badge-sm badge-rounded bg-warning-subtle text-warning">
                {{ Str::ucfirst(str_replace('_', ' ', (string) $application->status)) }}
            </span>
        </td>

        <td>
            @if (!is_null($waitingDays))
                <div class="d-flex flex-column">
                    <span>{{ $handedOverAt ? $handedOverAt->format('d-M-Y') : '---' }}</span>
                    <span class="fs-12 text-warning">waiting {{ $waitingDays }} day(s)</span>
                </div>
            @else
                <span>---</span>
            @endif
        </td>

        <td class="text-center">
            @if ($reminders > 0)
                <span class="badge badge-sm badge-rounded bg-primary-subtle text-primary">{{ $reminders }}</span>
            @else
                <span class="text-muted">0</span>
            @endif
        </td>

        <td>
            @if ($lastSent)
                <span class="fs-12">{{ \Illuminate\Support\Carbon::parse($lastSent)->format('d-M-Y, h:i A') }}</span>
            @else
                <span class="text-muted">Never</span>
            @endif
        </td>

        <td>
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-sm btn-warning actionHandler"
                    data-url="{{ route('admin.application.inactive.send_mail_form', $application->uuid) }}"
                    title="Send a reminder to this manuscript's owner">
                    <i class="ri-mail-send-line"></i>
                </button>
                <a class="btn btn-sm btn-info" href="{{ route('admin.application.show', $application->uuid) }}"
                    title="Open manuscript">
                    <i class="ri-eye-line"></i>
                </a>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="p-4 text-center">No inactive manuscripts. Nothing has been sitting with an author
            for {{ \App\Models\Application::INACTIVE_AFTER_DAYS }} days or more.</td>
    </tr>
@endforelse
