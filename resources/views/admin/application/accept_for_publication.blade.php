<div class="modal-dialog">
    <div class="modal-content border-0 shadow">
        <div class="modal-header bg-light">
            <h5 class="modal-title fw-bold">Accept for Publication</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form action="{{ route('admin.application.acceptForPublication', ['uuid' => $uuid]) }}" method="POST">
            @csrf
            <div class="modal-body p-4">
                <p class="text-muted mb-3">
                    This will mark the manuscript as accepted for publication and assign it back to the publisher
                    to add the DOI and publish it.
                </p>

                <div class="mb-3">
                    <label for="publicationComment" class="form-label fw-semibold">
                        Comment for Publisher <span class="text-muted">(optional)</span>
                    </label>
                    <textarea name="comment" id="publicationComment" rows="4" maxlength="2000" class="form-control"
                        placeholder="Add any final notes for the publisher..."></textarea>
                </div>
            </div>

            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success">
                    <i class="ri-check-line me-1"></i> Accept for Publication
                </button>
            </div>
        </form>
    </div>
</div>
