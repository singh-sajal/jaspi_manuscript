<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Publish Manuscript</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form action="{{ route('admin.application.publish', [
            'uuid' => $uuid,
        ]) }}"
            method="post">
            @csrf

            <div class="modal-body px-4 py-3">
                <p class="text-muted mb-3">
                    Add the DOI and publish the manuscript. The author and reviewers will be notified.
                </p>

                <div class="row">
                    <div class="col-12">
                        <label for="doi" class="form-label fw-semibold">
                            DOI Number <span class="text-danger">*</span>
                        </label>

                        <input type="text" name="doi" class="form-control shadow-sm" required
                            placeholder="Enter the DOI number">
                    </div>
                </div>
            </div>

            <div class="modal-footer d-flex justify-content-end">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success">Publish Manuscript</button>
            </div>
        </form>
    </div>
</div>
