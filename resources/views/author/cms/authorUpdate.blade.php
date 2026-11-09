<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">{{ array_key_exists('handling_editor_decision', $timeline->data ?? []) ? "Editor's decision" : "Reviewer's remark" }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="container col-md-12 mb-5">
            @if (array_key_exists('handling_editor_decision', $timeline->data ?? []))
                <div class="mt-3">
                    Decision: Handling Editor
                    {{ (int) ($timeline->data['handling_editor_decision'] ?? 0) === 1 ? 'Accepted' : 'Declined' }} the
                    Assignment
                </div>
                <div class="mt-3">
                    Reason: {{ $timeline->data['handling_editor_reason'] ?? 'No reason provided' }}
                </div>
            @else
                <div class="mt-3">
                    {{ $timeline->data['comment'] ?? 'No Comment Found !' }}
                </div>
            @endif
        </div>
    </div>
</div>