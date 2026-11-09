<div class="modal-dialog modal-xl">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Co-Author Update</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form action="{{ route('author.application.co_author_update', ['uuid' => $uuid, 'author_id' => $author_data['coauthor_id']]) }}"
              method="post" enctype="multipart/form-data" novalidate>
            @csrf

            <div class="modal-body">
                <div class="row g-3">

                    <div class="col-4">
                        <label class="form-label required">Email</label>
                        <input type="email" required name="coauthor_email" id="coauthor_email" class="form-control"
                            placeholder="Enter email" value="{{ $author_data['coauthor_email'] ?? '' }}">
                    </div>

                    <div class="col-4">
                        <label class="form-label required">First Name</label>
                        <input type="text" required name="coauthor_name" id="coauthor_name" class="form-control"
                            placeholder="Enter first name" value="{{ $author_data['coauthor_name'] ?? '' }}">
                    </div>

                    <div class="col-4">
                        <label class="form-label required">Last Name</label>
                        <input type="text" required name="coauthor_lname" id="coauthor_lname" class="form-control"
                            placeholder="Enter last name" value="{{ $author_data['coauthor_lname'] ?? '' }}">
                    </div>

                    <div class="col-4">
                        <label class="form-label required">Phone</label>
                        <input type="number" required name="coauthor_phone" id="coauthor_phone" class="form-control"
                            placeholder="Enter phone" value="{{ $author_data['coauthor_phone'] ?? '' }}">
                    </div>

                    <div class="col-4">
                        <label class="form-label required">Department</label>
                        <input type="text" required name="coauthor_department" id="coauthor_department" class="form-control"
                            placeholder="Enter Department" value="{{ $author_data['coauthor_department'] ?? '' }}">
                    </div>

                    <div class="col-4">
                        <label class="form-label required">Organization</label>
                        <input type="text" required name="coauthor_organization" id="coauthor_organization" class="form-control"
                            placeholder="Enter Organization" value="{{ $author_data['coauthor_organization'] ?? '' }}">
                    </div>

                    <div class="col-4">
                        <label class="form-label required">Affiliation</label>
                        <input type="text" class="form-control" name="author_affiliation" id="author_affiliation"
                            placeholder="Enter Affiliation" required value="{{ $author_data['coauthor_affiliation'] ?? '' }}">
                    </div>
                    <div class="col-4">
                        <label class="form-label required">ORCID ID</label>
                        <input type="text" class="form-control" name="author_orcid_id" id="author_orcid_id"
                            placeholder="Enter ORCID" required value="{{ $author_data['coauthor_orcid_id'] ?? '' }}">
                    </div>
                    <div class="col-4">
                        <label class="form-label">SASPI Number</label>
                        <input type="text" class="form-control" name="author_saspi_id" id="author_saspi_id"
                            placeholder="Enter SASPI" value="{{ $author_data['coauthor_saspi_id'] ?? '' }}">
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
                <button type="submit" class="btn btn-primary">Update Co-Author</button>
            </div>
        </form>
    </div>
</div>

<script>
    (function() {
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

        // --- PRE-FILLED VALUES FOR EDIT ---
        const savedCountry = "{{ $author_data['coauthor_country'] ?? '' }}";
        const savedState = "{{ $author_data['coauthor_state'] ?? '' }}";
        const savedCity = "{{ $author_data['coauthor_city'] ?? '' }}";

        let typingTimer;
        const doneTypingInterval = 500;
        const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;

        // Email Autocomplete Logic
        if (emailInput) {
            emailInput.addEventListener('input', function() {
                clearTimeout(typingTimer);
                const email = this.value;
                emailInput.classList.remove('is-valid', 'is-invalid');
                emailInput.style.borderColor = "";

                typingTimer = setTimeout(function() {
                    if (emailRegex.test(email)) { checkEmailInDB(email); }
                }, doneTypingInterval);
            });
        }

        function checkEmailInDB(email) {
            emailInput.style.borderColor = "#0d6efd";
            fetch("{{ route('author.check_email') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                body: JSON.stringify({ email: email })
            }).then(res => res.json()).then(res => {
                emailInput.style.borderColor = "";
                if (res.status === 'found') {
                    emailInput.classList.add('is-valid');
                    if(nameInput) nameInput.value = res.data.name || '';
                    if(lnameInput) lnameInput.value = res.data.lname || '';
                    if(phoneInput) phoneInput.value = res.data.phone || '';
                    if(affInput) affInput.value = res.data.affiliation || '';
                    if(orcidInput) orcidInput.value = res.data.orcid || '';
                    if(saspiInput && res.data.saspi_id) saspiInput.value = res.data.saspi_id;

                    // Cascade Location
                    if(res.data.country_id) {
                        coCountry.value = res.data.country_id;
                        loadStates(res.data.country_id).then(() => {
                            if(res.data.state_id) {
                                coState.value = res.data.state_id;
                                loadCities(res.data.country_id, res.data.state_id).then(() => {
                                    if(res.data.city_id) coCity.value = res.data.city_id;
                                });
                            }
                        });
                    }
                }
            }).catch(e => { emailInput.style.borderColor = ""; });
        }

        // Location Helper Functions
        async function fetchData(url) {
            try { const res = await fetch(url); return await res.json(); } catch(e){ return []; }
        }

        function loadCountries() {
            return fetchData('/author/locations/countries').then(data => {
                data.forEach(item => {
                    if(!coCountry.querySelector(`option[value="${item.id}"]`))
                        coCountry.innerHTML += `<option value="${item.id}">${item.name}</option>`;
                });
            });
        }

        function loadStates(cid) {
            coState.innerHTML = '<option value="" disabled selected>Select State</option>';
            coCity.innerHTML = '<option value="" disabled selected>Select State First</option>';
            coState.disabled = false; coCity.disabled = true;
            return fetchData(`/author/locations/states/${cid}`).then(data => {
                if(data.length > 0) data.forEach(i => coState.innerHTML += `<option value="${i.id}">${i.name}</option>`);
                else coState.innerHTML = '<option disabled>No States</option>';
            });
        }

        function loadCities(cid, sid) {
            coCity.innerHTML = '<option value="" disabled selected>Select City</option>';
            coCity.disabled = false;
            return fetchData(`/author/locations/cities/${cid}/${sid}`).then(data => {
                if(data.length > 0) data.forEach(i => coCity.innerHTML += `<option value="${i.id}">${i.name}</option>`);
                else coCity.innerHTML = '<option disabled>No Cities</option>';
            });
        }

        if(coCountry) coCountry.addEventListener('change', function() { loadStates(this.value); });
        if(coState) coState.addEventListener('change', function() { loadCities(coCountry.value, this.value); });

        // Initialize with Saved Data
        loadCountries().then(() => {
            if(savedCountry) {
                coCountry.value = savedCountry;
                loadStates(savedCountry).then(() => {
                    if(savedState) {
                        coState.value = savedState;
                        loadCities(savedCountry, savedState).then(() => {
                            if(savedCity) coCity.value = savedCity;
                        });
                    }
                });
            }
        });
    })();
</script>
