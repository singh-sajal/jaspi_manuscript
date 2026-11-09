<div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg rounded-4">
        <div class="modal-header">
            <h5 class="modal-title">Editor Rejection Details</h5>
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
            <div class="mb-3">
                <strong>Assigned Editor:</strong>
                <span>{{ $application->assignee->name ?? 'N/A' }}</span>
            </div>
            <hr>
            <div class="mb-0">
                <strong>Reason for Declining:</strong>
                <div class="alert alert-danger mt-2 mb-0">
                    {{ $application->data['handling_editor_reason'] ?? 'No reason provided.' }}
                </div>
            </div>
        </div>

        <div class="modal-footer d-flex justify-content-end">
            <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Close</button>
        </div>
    </div>
</div>
