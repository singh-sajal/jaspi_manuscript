<div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow">
        <div class="modal-header bg-light">
            <h5 class="modal-title fw-bold" id="requestProofreadModalLabel">Send to Author for Proofread</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form action="{{ route('admin.application.send_for_proofreading', $uuid) }}" method="POST">
            @csrf
            <div class="modal-body p-4">
                <p class="text-muted mb-3">
                    This will notify the author that they need to review the formatted proof and submit any corrections.
                </p>
                <div class="mb-3">
                    <label for="adminComment" class="form-label fw-semibold">Instructions / Comments for Author <span
                            class="text-danger">*</span></label>
                    {{-- <textarea class="form-control" name="comment" rows="4" required
                        placeholder="E.g., Please review the formatted galley proof and upload your corrections..."></textarea> --}}
                    <div id="editor" style="height:130px;">
                        {{-- {!! $app_info->description ?? '' !!} --}}
                    </div>
                    <input type="hidden" name="description" id="description">
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i> Send Request</button>
            </div>
        </form>
    </div>
</div>
