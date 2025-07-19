<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Mention Updates</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form action="{{ route('author.application.reSubmission', $uuid) }}" method="post" novalidate
            enctype="multipart/form-data" novalidate>
            @csrf

            <div class="card-body">
                <div class="mb-3">
                    {{-- <label for="comment" class="form-label fw-semibold">Comment</label> --}}
                    <textarea name="comment" id="comment" class="form-control" rows="6"
                        placeholder="Mention the updates(in short)"></textarea>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </div>
</div>
