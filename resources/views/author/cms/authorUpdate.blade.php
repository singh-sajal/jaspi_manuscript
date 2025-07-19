<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Reviewer's remark</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="container col-md-12 mb-5">
            {{-- <div>

                {{ $timeline->data['review_status'] ?? 'No Comment Found !' }}

            </div> --}}
            <div class="mt-3">
                
                {{ $timeline->data['comment'] ?? 'No Comment Found !' }}
            </div>
        </div>
    </div>
</div>
