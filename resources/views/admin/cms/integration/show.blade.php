<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Interest Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="card-body">
            <p><strong>Name:</strong> {{ $integration->name ?? '' }}</p>
            <p><strong>Created At:</strong> {{ $integration->created_at->format('d-M-Y, h:i') }}</p>
        </div>
    </div>
</div>