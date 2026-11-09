<div class="modal-dialog modal-lg">
    <div class="modal-content">

        <div class="modal-header">
            <h5 class="modal-title">Add Reviewer Suggestion</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <form action="{{ route('author.application.reviewer', $application->uuid) }}" method="post"
            enctype="multipart/form-data" novalidate>
            @method('POST')
            @csrf

            <div class="modal-body">
                <div class="row g-3">

                    <div class="col-6">
                        <label class="form-label required">First Name</label>
                        <input type="text" name="first_name" id="reviewer_fname" class="form-control"
                            placeholder="Enter first name" required>
                    </div>

                    <div class="col-6">
                        <label class="form-label required">Last Name</label>
                        <input type="text" name="last_name" id="reviewer_lname" class="form-control"
                            placeholder="Enter last name" required>
                    </div>

                    <div class="col-6">
                        <label class="form-label required">Email</label>
                        <input type="email" name="email" id="reviewer_email" class="form-control"
                            placeholder="Enter email address" required>
                    </div>

                    <div class="col-6">
                        <label class="form-label">Institution</label>
                        <input type="text" name="institution" id="reviewer_institution" class="form-control"
                            placeholder="Enter institution">
                    </div>

                    <div class="col-6">
                        <label class="form-label">Specialization</label>
                        <input type="text" name="specialization" id="reviewer_specialization" class="form-control"
                            placeholder="Enter specialization">
                    </div>

                    <div class="col-6">
                        <label class="form-label">Recommended</label>
                        <select class="form-select" name="recommended">
                            <option value="" selected disabled>Select</option>
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="saveReviewerBtn">Add Reviewer</button>
            </div>
        </form>

    </div>
</div>

<script>
    (function() {
        // --- 1. GET ELEMENTS ---
        const rEmailInput = document.getElementById('reviewer_email');
        const rFnameInput = document.getElementById('reviewer_fname');
        const rLnameInput = document.getElementById('reviewer_lname');
        const rInstInput  = document.getElementById('reviewer_institution');
        const rSpecInput  = document.getElementById('reviewer_specialization');

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
                    // Note: accessing res.data.fname because that is what your controller sends
                    if (rFnameInput) rFnameInput.value = res.data.fname || '';
                    if (rLnameInput) rLnameInput.value = res.data.lname || '';

                    // // Map 'Affiliation' from DB to 'Institution' in Reviewer Form
                    // if (rInstInput)  rInstInput.value = res.data.affiliation || '';

                    // // If your DB doesn't have 'specialization', this might stay empty,
                    // // or you can map 'department' to it if available.
                    // if (rSpecInput && res.data.specialization) {
                    //     rSpecInput.value = res.data.specialization;
                    // }
                }
            })
            .catch(err => {
                rEmailInput.style.borderColor = "";
                console.error("Error checking reviewer email:", err);
            });
        }
    })();
</script>
