<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="reminderModalLabel">Send Reminder to Reviewers</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form action="{{ route('admin.application.remind_reviewers', ['uuid' => $application->uuid]) }}" method="POST">
            @csrf
            <div class="modal-body">
                <p class="text-muted mb-4">Select the reviewers you want to send a reminder notification to.</p>

                @if ($application->assigned_to_id)
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="reviewer_ids[]"
                            value="{{ $application->assigned_to_id }}" id="internalReviewer" checked>
                        <label class="form-check-label fw-semibold" for="internalReviewer">
                            Internal Reviewer
                        </label>
                    </div>
                @endif

                @if ($application->assigned_to_ex_id && $application->assigned_to_ex_id !== 'not')
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="reviewer_ids[]"
                            value="{{ $application->assigned_to_ex_id }}" id="externalReviewer" checked>
                        <label class="form-check-label fw-semibold" for="externalReviewer">
                            External Reviewer
                        </label>
                    </div>
                @endif

            <span class="text-danger" data-error="reviewer_ids[]"></span>

                @if (!$application->assigned_to_id && (!$application->assigned_to_ex_id || $application->assigned_to_ex_id === 'not'))
                    <div class="alert alert-warning">
                        No reviewers are currently assigned to this manuscript.
                    </div>
                @endif
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"
                    {{ !$application->assigned_to_id && (!$application->assigned_to_ex_id || $application->assigned_to_ex_id === 'not') ? 'disabled' : '' }}>
                    <i class="bi bi-send me-1"></i> Send Reminders
                </button>
            </div>
        </form>
    </div>
</div>
