    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="{{ route('admin.application.add_doi', ['uuid' => $application->uuid]) }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="application_uuid" value="{{ $application->uuid }}">

                <div class="modal-header">
                    <h5 class="modal-title" id="doiModalLabel">
                        <i class="ri-links-line me-2"></i>Assign DOI Number
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div id="doiModalAlert" class="d-none"></div>
                    <div class="mb-3">
                        <label for="doiInput" class="form-label fw-semibold">DOI Number</label>
                        <input type="text" class="form-control" name="doi"
                            placeholder="e.g. 10.1234/jaspi.2025.001">
                        <div class="form-text text-muted">Enter the full DOI assigned to this manuscript.</div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-arrow-repeat me-1"></i> Update
                    </button>
                </div>
            </form>

        </div>
    </div>
