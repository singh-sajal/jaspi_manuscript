<div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg border-0 rounded-3">
        <div class="modal-header bg-primary text-white">
            <h5 class="modal-title text-light">
                <i class="bi bi-chat-left-text me-2"></i> Editor in Chief's Remark
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
            <!-- Decision Status Display -->
            @if(isset($application->data['decision']))
                <div class="mb-3">
                    <h6 class="fw-bold text-secondary mb-2">Decision:</h6>
                    @if(strtolower($application->data['decision']) == 'accept')
                        <span class="badge bg-success px-3 py-2 text-uppercase rounded-pill">Accepted</span>
                    @elseif(strtolower($application->data['decision']) == 'reject')
                        <span class="badge bg-danger px-3 py-2 text-uppercase rounded-pill">Rejected</span>
                    @else
                        <span class="badge bg-secondary px-3 py-2 text-uppercase rounded-pill">{{ $application->data['decision'] }}</span>
                    @endif
                </div>
            @endif

            <!-- Remark Display -->
            <div class="p-3 bg-light rounded-3">
                <h6 class="fw-bold text-secondary mb-2">Remark:</h6>
                <p class="mb-0 text-dark">
                    {!! $application->data['chief_response'] ?? 'No remark provided.' !!}
                </p>
            </div>
        </div>
    </div>
</div>
