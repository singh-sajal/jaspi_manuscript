<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Reviewer Suggestion Update</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form
            action="{{ route('admin.application.reviewer_update', [
                'uuid' => $uuid,
                'reviewer_id' => $reviewer_data['reviewer_id'],
            ]) }}"
            method="post" enctype="multipart/form-data" novalidate>
            @csrf

            <div class="modal-body">
                <div class="row g-3">

                    <div class="col-6">
                        <label class="form-label required">Title</label>
                        <select name="title" class="form-select" required>
                            <option value="">Select Title</option>

                            <option value="Dr."
                                {{ isset($reviewer_data['title']) && $reviewer_data['title'] == 'Dr.' ? 'selected' : '' }}>
                                Dr.</option>
                            <option value="Prof."
                                {{ isset($reviewer_data['title']) && $reviewer_data['title'] == 'Prof.' ? 'selected' : '' }}>
                                Prof.</option>
                            <option value="Mr."
                                {{ isset($reviewer_data['title']) && $reviewer_data['title'] == 'Mr.' ? 'selected' : '' }}>
                                Mr.</option>
                            <option value="Ms."
                                {{ isset($reviewer_data['title']) && $reviewer_data['title'] == 'Ms.' ? 'selected' : '' }}>
                                Ms.</option>
                            <option value="Others"
                                {{ isset($reviewer_data['title']) && $reviewer_data['title'] == 'Ms.' ? 'selected' : '' }}>
                                Ms.</option>
                            <option value="Others"
                                {{ isset($reviewer_data['title']) && $reviewer_data['title'] == 'Others' ? 'selected' : '' }}>
                                Others</option>

                        </select>
                    </div>


                    <div class="col-6">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-control" placeholder="Enter First Name"
                            value="{{ $reviewer_data['first_name'] ?? '' }}" required>
                    </div>

                    <div class="col-6">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control" placeholder="Enter Last Name"
                            value="{{ $reviewer_data['last_name'] ?? '' }}" required>
                    </div>

                    <div class="col-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="Enter Email Address"
                            value="{{ $reviewer_data['email'] ?? '' }}" required>
                    </div>

                    <div class="col-6">
                        <label class="form-label">Institution</label>
                        <input type="text" name="institution" class="form-control" placeholder="Enter Institution"
                            value="{{ $reviewer_data['institution'] ?? '' }}">
                    </div>

                    <div class="col-6">
                        <label class="form-label">Specialization</label>
                        <input type="text" name="specialization" class="form-control"
                            placeholder="Enter Specialization" value="{{ $reviewer_data['specialization'] ?? '' }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Reason (Why this person?)</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Provide short justification">{{ $reviewer_data['reason'] ?? '' }}</textarea>
                    </div>

                </div>
            </div>

            <div class="modal-footer d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>

        </form>
    </div>
</div>
