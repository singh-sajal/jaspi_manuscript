<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Remark</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form action="{{ route('admin.application.techEditorDecision', $uuid) }}" method="POST">
            @csrf
            <div class="modal-body">
                <!-- Selectable Items -->
                <div class="mb-3">
                    <label class="form-label d-block">Checks</label>
                    <div class="form-check">
                        <input class="form-check-input check-item" type="checkbox" id="plagiarism" name="plagiarism">
                        <label class="form-check-label" for="plagiarism">Plagiarism Check</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input check-item" type="checkbox" id="completeness"
                            name="completeness">
                        <label class="form-check-label" for="completeness">Completeness Check</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input check-item" type="checkbox" id="crossref" name="crossref">
                        <label class="form-check-label" for="crossref">Cross-reference Check</label>
                    </div>
                </div>

                <!-- Radio Buttons -->
                <div class="mb-3">
                    <label class="form-label d-block">Decision</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="decision" id="accept" value="accept"
                            disabled required>
                        <label class="form-check-label" for="accept">Accept</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="decision" id="revise" value="revise">
                        <label class="form-check-label" for="revise">Revise</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="decision" id="out-of-scope" value="out-of-scope">
                        <label class="form-check-label" for="out-of-scope">Out of Scope</label>
                    </div>
                </div>

                <!-- Textarea -->
                <div class="mb-3">
                    <label for="remark" class="form-label">Remark</label>
                    <textarea class="form-control" id="remark" name="remark" rows="4" placeholder="Enter your remark here..."
                        required></textarea>
                </div>
            </div>






            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Save Remark</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

