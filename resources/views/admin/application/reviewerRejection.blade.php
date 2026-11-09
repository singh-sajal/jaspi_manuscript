@php
    $type = $type ?? 'internal';

    $internalDeclined = ((string) ($application->accept_in ?? '')) === '0';
    $externalDeclined = ((string) ($application->accept_ex ?? '')) === '0';

    $internalReason = data_get($application->data, 'internal_reviewer_reason')
        ?? ($internalDeclined ? data_get($application->data, 'reviewer_reason') : null);
    $externalReason = data_get($application->data, 'external_reviewer_reason')
        ?? ($externalDeclined ? data_get($application->data, 'reviewer_reason') : null);

    $rejections = [];

    if ($internalDeclined) {
        $rejections['internal'] = [
            'label'    => 'Internal Reviewer',
            'reviewer' => $application->assignee->name ?? 'N/A',
            'reason'   => $internalReason,
        ];
    }

    if ($externalDeclined) {
        $rejections['external'] = [
            'label'    => 'External Reviewer',
            'reviewer' => $application->externalAssignee->name ?? 'N/A',
            'reason'   => $externalReason,
        ];
    }

    /* Show the badge that was clicked first, then the other one for full context. */
    if ($type === 'external' && isset($rejections['external'], $rejections['internal'])) {
        $rejections = ['external' => $rejections['external'], 'internal' => $rejections['internal']];
    }
@endphp

<div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg rounded-4">
        <div class="modal-header">
            <h5 class="modal-title">
                {{ $type === 'external' ? 'External Reviewer Rejection Details' : 'Internal Reviewer Rejection Details' }}
            </h5>
            <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
            <div class="mb-3">
                <strong>Manuscript ID:</strong>
                <span>{{ $application->application_id ?? 'N/A' }}</span>
            </div>
            <div class="mb-3">
                <strong>Title:</strong>
                <span>{{ Str::limit($application->title ?? 'N/A', 60) }}</span>
            </div>

            <hr>

            @forelse ($rejections as $rejection)
                <div class="mb-3">
                    <strong>{{ $rejection['label'] }}:</strong>
                    <span>{{ $rejection['reviewer'] }}</span>
                </div>
                <div class="mb-3">
                    <strong>Reason for Declining ({{ $rejection['label'] }}):</strong>
                    <div class="alert alert-danger mt-2 mb-0" style="white-space: pre-line;">{{ $rejection['reason'] ?? 'No reason provided.' }}</div>
                </div>
                @if (! $loop->last)
                    <hr>
                @endif
            @empty
                <div class="mb-0">
                    <strong>Reason for Declining:</strong>
                    <div class="alert alert-danger mt-2 mb-0">
                        {{ data_get($application->data, 'reviewer_reason') ?? 'No reason provided.' }}
                    </div>
                </div>
            @endforelse
        </div>

        <div class="modal-footer d-flex justify-content-end">
            <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Close</button>
        </div>
    </div>
</div>