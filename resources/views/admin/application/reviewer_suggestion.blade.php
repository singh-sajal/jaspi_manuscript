<div class="modal-dialog modal-lg">
    <div class="modal-content">

        <div class="modal-header">
            <h5 class="modal-title">Add Reviewer Suggestion</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form action="{{ route('admin.application.reviewer', $application->uuid) }}" method="post"
            enctype="multipart/form-data" novalidate>
            @method('POST')
            @csrf

            <div class="modal-body">
                <div class="row g-3" id="reviewer-${reviewerIndex}">

                    <div class="col-6">
                        <label class="form-label required">Title</label>
                        <select name="title" class="form-select" required>
                            <option value="">Select Title</option>
                            <option>Dr.</option>
                            <option>Prof.</option>
                            <option>Mr.</option>
                            <option>Ms.</option>
                            <option>Others</option>
                        </select>
                    </div>

                    <div class="col-6">
                        <label class="form-label required">First Name</label>
                        <input type="text" name="first_name" class="form-control" placeholder="Enter first name"
                            required>
                    </div>

                    <div class="col-6">
                        <label class="form-label required">Last Name</label>
                        <input type="text" name="last_name" class="form-control" placeholder="Enter last name"
                            required>
                    </div>

                    <div class="col-6">
                        <label class="form-label required">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="Enter email address"
                            required>
                    </div>

                    <div class="col-6">
                        <label class="form-label">Institution</label>
                        <input type="text" name="institution" class="form-control" placeholder="Enter institution">
                    </div>

                    <div class="col-6">
                        <label class="form-label">Specialization</label>
                        <input type="text" name="specialization" class="form-control"
                            placeholder="Enter specialization">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Reason (Why this person?)</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Explain why this reviewer is suggested"></textarea>
                    </div>
                </div>
            </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary" id="saveReviewerBtn">Add Reviewer</button>
        </div>
        </form>

    </div>
</div>
