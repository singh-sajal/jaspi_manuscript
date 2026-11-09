<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Reviewer Suggestion Update</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form
            action="{{ route('author.application.reviewer_update', [
                'uuid' => $uuid,
                'reviewer_id' => $reviewer_data['reviewer_id'],
            ]) }}"
            method="post" enctype="multipart/form-data" novalidate>
            @csrf

            <div class="modal-body">
                <div class="row g-3">

                    <div class="col-6">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" id="edit_reviewer_fname" class="form-control"
                            placeholder="Enter First Name" value="{{ $reviewer_data['first_name'] ?? '' }}" required>
                    </div>

                    <div class="col-6">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" id="edit_reviewer_lname" class="form-control"
                            placeholder="Enter Last Name" value="{{ $reviewer_data['last_name'] ?? '' }}" required>
                    </div>

                    <div class="col-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="edit_reviewer_email" class="form-control"
                            placeholder="Enter Email Address" value="{{ $reviewer_data['email'] ?? '' }}" required>
                    </div>

                    <div class="col-6">
                        <label class="form-label">Institution</label>
                        <input type="text" name="institution" id="edit_reviewer_institution" class="form-control"
                            placeholder="Enter Institution" value="{{ $reviewer_data['institution'] ?? '' }}">
                    </div>

                    <div class="col-6">
                        <label class="form-label">Specialization</label>
                        <input type="text" name="specialization" id="edit_reviewer_specialization" class="form-control"
                            placeholder="Enter Specialization" value="{{ $reviewer_data['specialization'] ?? '' }}">
                    </div>

                    <div class="col-6">
                        <label class="form-label required">Recommended</label>
                        <select class="form-select" name="recommended" required>
                            <option value="" selected disabled>Select</option>
                            <option value="yes"
                                {{ isset($reviewer_data['recommended']) && $reviewer_data['recommended'] == 'yes' ? 'selected' : '' }}>
                                Yes</option>
                            <option value="no"
                                {{ isset($reviewer_data['recommended']) && $reviewer_data['recommended'] == 'no' ? 'selected' : '' }}>
                                No</option>
                        </select>
                    </div>

                </div>
            </div>

            <div class="modal-footer d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>

        </form>
    </div>
</div>

<script>
    (function() {
        // --- 1. GET ELEMENTS (Targeting 'edit_' IDs) ---
        const rEmailInput = document.getElementById('edit_reviewer_email');
        const rFnameInput = document.getElementById('edit_reviewer_fname');
        const rLnameInput = document.getElementById('edit_reviewer_lname');
        const rInstInput  = document.getElementById('edit_reviewer_institution');
        const rSpecInput  = document.getElementById('edit_reviewer_specialization');

        // --- 2. CONFIGURATION ---
        let typingTimer;
        const doneTypingInterval = 500; // 0.5 seconds
        const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;

        // --- 3. LISTEN FOR TYPING ---
        if (rEmailInput) {
            rEmailInput.addEventListener('input', function() {
                clearTimeout(typingTimer);
                const email = this.value;

                // Visual Reset
                rEmailInput.classList.remove('is-valid', 'is-invalid');
                rEmailInput.style.borderColor = "";

                // Wait for user to stop typing
                typingTimer = setTimeout(function() {
                    if (emailRegex.test(email)) {
                        checkReviewerEmail(email);
                    }
                }, doneTypingInterval);
            });
        }

        // --- 4. FETCH LOGIC ---
        function checkReviewerEmail(email) {
            // Show Loading (Blue Border)
            rEmailInput.style.borderColor = "#0d6efd";

            fetch("{{ route('author.check_reviewer_email') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ email: email })
            })
            .then(res => res.json())
            .then(res => {
                rEmailInput.style.borderColor = ""; // Stop Loading

                if (res.status === 'found') {
                    rEmailInput.classList.add('is-valid');

                    // --- MAP DATA TO FIELDS ---
                    if (rFnameInput) rFnameInput.value = res.data.fname || '';
                    if (rLnameInput) rLnameInput.value = res.data.lname || '';

                    // Map 'Affiliation' -> 'Institution'
                    if (rInstInput)  rInstInput.value = res.data.affiliation || '';

                    // Map 'Specialization' if available
                    if (rSpecInput && res.data.specialization) {
                        rSpecInput.value = res.data.specialization;
                    }
                }
            })
            .catch(err => {
                rEmailInput.style.borderColor = "";
                console.error("Error checking reviewer email:", err);
            });
        }
    })();
</script>
