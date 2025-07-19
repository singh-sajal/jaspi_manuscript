<div class="modal-dialog modal-lg">
    <div class="modal-content shadow-sm">
        <div class="modal-header bg-light">
            <h5 class="modal-title">Query Detail</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body" style="font-family: 'Segoe UI', sans-serif;">
            <div class="mb-3">
                <div><strong>Name:</strong> {{ $row->name }}</div>
                <div><strong>Phone:</strong> {{ $row->phone }}</div>
                <div><strong>Email:</strong> >{{ $row->email }}</div>
                <div><strong>Date:</strong> {{ $row->created_at }}</div>
            </div>
            <hr>
            <div class="mb-3">
                <div><strong>Subject:</strong> {{ $row->subject }}</div>
            </div>
            <hr>

            <div class="mt-3">
                <p class="mb-1"><strong>Message:</strong></p>
                <div class="p-3 bg-light border rounded" style="white-space: pre-wrap;">
                    {{ $row->description }}
                </div>
            </div>
        </div>
    </div>
</div>
