<div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg border-0 rounded-3">
        <div class="modal-header bg-primary text-white">
            <h5 class="modal-title text-light">
<i class="bi bi-chat-left-text me-2"></i>
                {{ !empty($isAssistantEditor) ? "Assistant Editor's Remark" : "Technical Editor's Remark" }}
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
            <div class="p-3 bg-light rounded-3">
                <h6 class="fw-bold text-secondary mb-2">Remark:</h6>
                <p class="mb-0 text-dark">
                    {!! $timeline->data['comment'] ?? 'No remark provided.' !!}
                </p>
            </div>
        </div>
    </div>
</div>
