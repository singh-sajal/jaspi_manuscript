<div class="modal-dialog modal-xl">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Co-Author Add/Update</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form action="{{ route('author.application.co_author', $application->uuid) }}" method="post"
            enctype="multipart/form-data" novalidate>
            @csrf

            {{-- <div class="modal-body">
                <div class="row g-3" id="co-author-${coAuthorIndex}">
                    <div class="col-6">
                        <label for="coauthor_name" class="form-label required">Co-Author First Name</label>
                        <input type="text" required name="coauthor_name" id="coauthor_name" class="form-control"
                            placeholder="Enter first name" required>
                    </div>

                    <div class="col-6">
                        <label for="coauthor_lname" class="form-label required">Co-Author First Last Name</label>
                        <input type="text" required name="coauthor_lname" id="coauthor_lname" class="form-control"
                            placeholder="Enter last name" required>
                    </div>

                    <div class="col-6">
                        <label for="coauthor_email" class="form-label required">Co-Author Email</label>
                        <input type="email" required name="coauthor_email" id="coauthor_email" class="form-control"
                            placeholder="Enter email address" required>
                    </div>

                    <div class="col-6">
                        <label for="coauthor_phone" class="form-label required">Co-Author Phone</label>
                        <input type="number" required name="coauthor_phone" id="coauthor_phone" class="form-control"
                            placeholder="Enter phone number" required>
                    </div>

                    <div class="col-6">
                        <label for="coauthor_department" class="form-label required">Co-Author Department</label>
                        <input type="number" required name="coauthor_department" id="coauthor_department" class="form-control"
                            placeholder="Enter Co-Authors Department" required>
                    </div>

                    <div class="col-6">
                        <label for="coauthor_organization" class="form-label required">Co-Author Organization</label>
                        <input type="number" required name="coauthor_organization" id="coauthor_organization" class="form-control"
                            placeholder="Enter Co-Authors Organization" required>
                    </div>


                    <div class="col-6">
                        <label for="author_affiliation" class="form-label required">Co-Author Affiliation</label>
                        <input type="text" class="form-control" id="author_affiliation" name="author_affiliation"
                            placeholder="Enter Co-Author Affiliation" required
                            value="{{ $app_info->author_affiliation ?? '' }}">
                    </div>
                    <div class="col-6">
                        <label for="author_orcid_id" class="form-label required">Co-Author ORCID ID</label>
                        <input type="text" class="form-control" id="author_orcid_id" name="author_orcid_id"
                            placeholder="Enter Co-Author ORCID ID" required
                            value="{{ $app_info->author_orcid_id ?? '' }}">
                    </div>
                    <div class="col-6">
                        <label for="author_saspi_id" class="form-label">Co-Author SASPI Membership Number</label>
                        <input type="text" class="form-control" id="author_saspi_id" name="author_saspi_id"
                            placeholder="Enter Co-Author SASPI Membership Number"
                            value="{{ $app_info->author_saspi_id ?? '' }}">
                    </div>

                    <div class="col-4">
                        <label class="form-label required">Country</label>
                        <select name="coauthor_country" id="co_country" class="form-select" required>
                            <option value="" selected disabled>Select Country</option>
                        </select>
                    </div>

                    <div class="col-4">
                        <label class="form-label required">State</label>
                        <select name="coauthor_state" id="co_state" class="form-select" disabled required>
                            <option value="" selected disabled>Select Country First</option>
                        </select>
                    </div>

                    <div class="col-4">
                        <label class="form-label required">City</label>
                        <select name="coauthor_city" id="co_city" class="form-select" disabled required>
                            <option value="" selected disabled>Select State First</option>
                        </select>
                    </div>
                </div>
            </div> --}}
            <div class="modal-body">
                <div class="row g-3" id="co-author-${coAuthorIndex}">
                    <div class="col-4">
                        <label for="coauthor_email" class="form-label required">Co-Author Email</label>
                        <input type="email" required name="coauthor_email" id="coauthor_email" class="form-control"
                            placeholder="Enter email address">
                    </div>

                    <div class="col-4">
                        <label for="coauthor_name" class="form-label required">Co-Author First Name</label>
                        <input type="text" required name="coauthor_name" id="coauthor_name" class="form-control"
                            placeholder="Enter first name">
                    </div>

                    <div class="col-4">
                        <label for="coauthor_lname" class="form-label required">Co-Author Last Name</label>
                        <input type="text" required name="coauthor_lname" id="coauthor_lname" class="form-control"
                            placeholder="Enter last name">
                    </div>

                    <div class="col-4">
                        <label for="coauthor_phone" class="form-label required">Co-Author Phone</label>
                        <input type="number" required name="coauthor_phone" id="coauthor_phone" class="form-control"
                            placeholder="Enter phone number">
                    </div>

                    <div class="col-4">
                        <label for="coauthor_department" class="form-label required">Co-Author Department</label>
                        <input type="text" required name="coauthor_department" id="coauthor_department"
                            class="form-control" placeholder="Enter Co-Authors Department">
                    </div>

                    <div class="col-4">
                        <label for="coauthor_organization" class="form-label required">Co-Author Organization</label>
                        <input type="text" required name="coauthor_organization" id="coauthor_organization"
                            class="form-control" placeholder="Enter Co-Authors Organization">
                    </div>

                    <div class="col-4">
                        <label for="author_affiliation" class="form-label required">Co-Author Affiliation</label>
                        <input type="text" class="form-control" id="author_affiliation" name="author_affiliation"
                            placeholder="Enter Co-Author Affiliation" required
                            value="{{ $app_info->author_affiliation ?? '' }}">
                    </div>

                    <div class="col-4">
                        <label for="author_orcid_id" class="form-label required">Co-Author ORCID ID</label>
                        <input type="text" class="form-control" id="author_orcid_id" name="author_orcid_id"
                            placeholder="Enter Co-Author ORCID ID" required maxlength="16" pattern="[0-9]{16}"
                            title="ORCID ID must be exactly 16 digits" placeholder="ORCID ID must be exactly 16 digits" value="{{ $app_info->author_orcid_id ?? '' }}">
                    </div>

                    <div class="col-4">
                        <label for="author_saspi_id" class="form-label">Co-Author SASPI Membership Number</label>
                        <input type="text" class="form-control" id="author_saspi_id" name="author_saspi_id"
                            placeholder="Enter Co-Author SASPI Number" value="{{ $app_info->author_saspi_id ?? '' }}">
                    </div>

                    <div class="col-4">
                        <label class="form-label required">Country</label>
                        <select name="coauthor_country" id="co_country" class="form-select" required>
                            <option value="" selected disabled>Select Country</option>
                        </select>
                    </div>

                    <div class="col-4">
                        <label class="form-label required">State</label>
                        <select name="coauthor_state" id="co_state" class="form-select" disabled required>
                            <option value="" selected disabled>Select Country First</option>
                        </select>
                    </div>

                    <div class="col-4">
                        <label class="form-label required">City</label>
                        <select name="coauthor_city" id="co_city" class="form-select" disabled required>
                            <option value="" selected disabled>Select State First</option>
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
        // --- 1. DOM ELEMENTS ---
        const emailInput = document.getElementById('coauthor_email');
        const nameInput = document.getElementById('coauthor_name');
        const lnameInput = document.getElementById('coauthor_lname');
        const phoneInput = document.getElementById('coauthor_phone');
        const affInput = document.getElementById('author_affiliation');
        const orcidInput = document.getElementById('author_orcid_id');
        const saspiInput = document.getElementById('author_saspi_id');

        const coCountry = document.getElementById('co_country');
        const coState = document.getElementById('co_state');
        const coCity = document.getElementById('co_city');

        // --- 2. PRE-FILLED VALUES (For Edit Mode) ---
        // These pull from your Blade variables. If null, they become empty strings.
        const savedCountry = "{{ $author_data['coauthor_country'] ?? '' }}";
        const savedState = "{{ $author_data['coauthor_state'] ?? '' }}";
        const savedCity = "{{ $author_data['coauthor_city'] ?? '' }}";

        // --- 3. STATE VARIABLES ---
        let typingTimer;
        const doneTypingInterval = 500; // 0.5 seconds
        const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;

        // ==========================================
        //  EMAIL LOGIC
        // ==========================================
        if (emailInput) {
            emailInput.addEventListener('input', function() {
                clearTimeout(typingTimer);
                const email = this.value;

                // Reset visual cues
                emailInput.classList.remove('is-valid', 'is-invalid');
                emailInput.style.borderColor = "";

                typingTimer = setTimeout(function() {
                    if (emailRegex.test(email)) {
                        checkEmailInDB(email);
                    }
                }, doneTypingInterval);
            });
        }

        function checkEmailInDB(email) {
            // Loading indicator
            emailInput.style.borderColor = "#0d6efd";

            fetch("{{ route('author.check_email') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        email: email
                    })
                })
                .then(res => res.json())
                .then(res => {
                    emailInput.style.borderColor = ""; // Remove loading indicator

                    if (res.status === 'found') {
                        emailInput.classList.add('is-valid');

                        // --- FILL TEXT FIELDS ---
                        // Using 'name' and 'lname' to match your Controller
                        if (nameInput) nameInput.value = res.data.name || '';
                        if (lnameInput) lnameInput.value = res.data.lname || '';
                        if (phoneInput) phoneInput.value = res.data.phone || '';
                        if (affInput) affInput.value = res.data.affiliation || '';
                        if (orcidInput) orcidInput.value = res.data.orcid || '';
                        if (saspiInput && res.data.saspi_id) saspiInput.value = res.data.saspi_id;

                        // --- FILL LOCATION (Cascade) ---
                        if (res.data.country_id) {
                            coCountry.value = res.data.country_id;

                            // Load States
                            loadStates(res.data.country_id).then(() => {
                                if (res.data.state_id) {
                                    coState.value = res.data.state_id;

                                    // Load Cities
                                    loadCities(res.data.country_id, res.data.state_id).then(() => {
                                        if (res.data.city_id) coCity.value = res.data.city_id;
                                    });
                                }
                            });
                        }
                    } else {
                        // Optional: Code to handle if email is valid but not in DB (e.g., clear fields)
                        // console.log('New user');
                    }
                })
                .catch(err => {
                    emailInput.style.borderColor = "";
                    console.error("Error fetching author data:", err);
                });
        }

        // ==========================================
        //  LOCATION LOGIC
        // ==========================================

        async function fetchData(url) {
            try {
                const res = await fetch(url);
                return await res.json();
            } catch (error) {
                console.error("Location fetch error:", error);
                return [];
            }
        }

        function loadCountries() {
            return fetchData('/author/locations/countries').then(data => {
                data.forEach(item => {
                    // Prevent duplicates
                    if (!coCountry.querySelector(`option[value="${item.id}"]`)) {
                        coCountry.innerHTML += `<option value="${item.id}">${item.name}</option>`;
                    }
                });
            });
        }

        function loadStates(countryId) {
            // Reset downstream dropdowns
            coState.innerHTML = '<option value="" disabled selected>Loading...</option>';
            coCity.innerHTML = '<option value="" disabled selected>Select State First</option>';
            coCity.disabled = true;
            coState.disabled = true;

            return fetchData(`/author/locations/states/${countryId}`).then(data => {
                coState.innerHTML = '<option value="" disabled selected>Select State</option>';
                if (data.length > 0) {
                    coState.disabled = false;
                    data.forEach(item => {
                        coState.innerHTML += `<option value="${item.id}">${item.name}</option>`;
                    });
                } else {
                    coState.innerHTML = '<option value="" disabled>No States Found</option>';
                }
            });
        }

        function loadCities(countryId, stateId) {
            // Reset downstream dropdown
            coCity.innerHTML = '<option value="" disabled selected>Loading...</option>';
            coCity.disabled = true;

            return fetchData(`/author/locations/cities/${countryId}/${stateId}`).then(data => {
                coCity.innerHTML = '<option value="" disabled selected>Select City</option>';
                if (data.length > 0) {
                    coCity.disabled = false;
                    data.forEach(item => {
                        coCity.innerHTML += `<option value="${item.id}">${item.name}</option>`;
                    });
                } else {
                    coCity.innerHTML = '<option value="" disabled>No Cities Found</option>';
                }
            });
        }

        // --- Event Listeners for Manual Selection ---
        if (coCountry) {
            coCountry.addEventListener('change', function() {
                loadStates(this.value);
            });
        }

        if (coState) {
            coState.addEventListener('change', function() {
                loadCities(coCountry.value, this.value);
            });
        }

        // ==========================================
        //  INITIALIZATION
        // ==========================================
        loadCountries().then(() => {
            // Handle Edit Mode Pre-fill
            if (savedCountry) {
                coCountry.value = savedCountry;
                loadStates(savedCountry).then(() => {
                    if (savedState) {
                        coState.value = savedState;
                        loadCities(savedCountry, savedState).then(() => {
                            if (savedCity) coCity.value = savedCity;
                        });
                    }
                });
            }
        });

    })();
</script>
