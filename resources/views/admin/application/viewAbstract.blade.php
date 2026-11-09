<div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content shadow-lg rounded-4">
        <div class="modal-header">
            <h5 class="modal-title">Manuscript Details</h5>
            <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
            <div class="mb-3">
                <strong>Manuscript ID:</strong>
                <span>{{ $application->application_id ?? 'N/A' }}</span>
            </div>
            <div class="mb-3">
                <strong>Title:</strong>
                <div class="mt-1">{{ $application->title ?? 'N/A' }}</div>
            </div>
            <hr>
            <div class="mb-0">
                <strong>Abstract:</strong>
                <div class="mt-2 p-3 border rounded bg-light" style="max-height: 400px; overflow-y: auto;">
                    {!! $application->description ?? '<em class="text-muted">No abstract available.</em>' !!}
                </div>
            </div>
        </div>

        <div class="modal-footer d-flex justify-content-end">
            <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Close</button>
        </div>
    </div>
</div>
