<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Decide & Assign</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form action="{{ route('admin.application.editorDecision', ['uuid' => $uuid]) }}" method="post"
            enctype="multipart/form-data" novalidate>
            @csrf

            <div class="modal-body">
                <div class="mb-3">
                    <label for="decision" class="form-label" required>Decision</label>
                    <select id="decision" name="decision" class="form-select" required>
                        <option value="">---Select---</option>
                        <option value="1">Send for Peer Review</option>
                        <option value="2">Send for Review with Editorial Notes</option>
                        <option value="3">Revise Before Review</option>
                        <option value="4">Reject Without Review</option>
                    </select>
                </div>

                <div id="comment-box" class="mb-3">
                    <label for="comment" class="form-label" required>Comment</label>
                    <textarea id="comment" name="comment" class="form-control" rows="4"
                        placeholder="Enter your comment... or type N/A" required></textarea>
                </div>


                <div id="reviewer-section" class="mb-3" style="display: none;">
                    <label class="form-label fw-semibold" required>Select to assign Reviewer:</label>
                    <select class="form-select mb-4" id="staff_id">
                        <option value="">--Select--</option>
                        @foreach ($staffs as $staff)
                            <option value="{{ $staff->id }}">
                                {{ $staff->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="modal-footer d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        function toggleReviewerSection() {
            let val = $("#decision").val();
            const $reviewerSection = $("#reviewer-section");
            const $staffSelect = $("#staff_id");

            if (val === "1" || val === "2") {
                $reviewerSection.show();
                $staffSelect.attr("name", "staff_id");
                $staffSelect.attr("required", true);
            } else {
                $reviewerSection.hide();
                $staffSelect.removeAttr("name");
                $staffSelect.removeAttr("required");
            }
        }

        $("#decision").on("change", toggleReviewerSection);
        toggleReviewerSection(); // run on page load
    });
</script>
