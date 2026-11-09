<div class="row mt-4">
    <div class="col-12 mb-3">
        <label class="form-label required">Name</label>
        <input type="text" class="form-control" required name="name" value="{{ $admin->name ?? '' }}">
    </div>
    <div class="col-12 col-md-6 mb-3">
        <label class="form-label required">Email</label>
        <input type="email" class="form-control" required readonly name="email" value="{{ $admin->email ?? '' }}">
    </div>

    <div class="col-12 col-md-6 mb-3">
        <label class="form-label required">Phone</label>
        <input type="text" class="form-control" readonly inputmode="numeric" required name="phone" minlength="10"
            maxlength="10" pattern="[0-9]{10}" value="{{ $admin->phone ?? '' }}">
    </div>

    {{-- Add optiosn for gender and dob --}}
    <div class="col-12 col-md-6 mb-3">
        <label class="form-label required" for="gender">Gender</label>
        <select name="gender" id="gender" class="form-select" required>
            <option value="" disabled selected>Select Gender</option>
            <option value="Male" {{ $admin->gender == 'Male' ? 'selected' : '' }}>Male</option>
            <option value="Female" {{ $admin->gender == 'Female' ? 'selected' : '' }}>Female</option>
            <option value="Other" {{ $admin->gender == 'Other' ? 'selected' : '' }}>Other</option>
        </select>
    </div>

    <div class="col-12 col-md-6 mb-3">
        <label class="form-label required">Date of birth</label>
        <input type="date" class="form-control" required name="dob" value="{{ $admin->dob ?? '' }}">
    </div>
    <div class="col-12 col-md-6 mb-3">
        <label class="form-label required">Country</label>
        <input type="text" class="form-control" disabled name="country" value="{{ $admin->country ?? 'India' }}">
    </div>

    <div class="col-12 col-md-6 mb-3">
        <label class="form-label required">State</label>
        <select name="state" id="" required class="form-select">
            <option value="">Select State</option>
            @foreach ($states as $state)
                <option value="{{ $state }}" @if ($admin->state == $state) selected @endif>
                    {{ $state }}</option>
            @endforeach
        </select>

    </div>
    <div class="col-12 mb-3">
        <label class="form-label required">City</label>
        <input type="text" class="form-control" required name="city" value="{{ $admin->city ?? '' }}">
    </div>
    <div class="col-12 mb-3">
        <label class="form-label required">Pincode</label>
        <input type="text" inputmode="numeric" required pattern="[0-9]{6}" minlength="6" maxlength="6"
            class="form-control" name="pincode" value="{{ $admin->pincode ?? '' }}">
    </div>
    <div class="col-12 mb-3">
        <label class="form-label">Address</label>

        <input type="text" class="form-control" required name="address" value="{{ $admin->address ?? '' }}">
    </div>

    @if ((!empty(auth()->user()->role) || authUser('admin')->is_admin == 1) && method_exists($admin, 'hasRole') && $admin->hasRole('Reviewer'))
        <div class="col-12 my-3 border-top pt-3">
            <h6 class="fw-bold">Reviewer Details</h6>
        </div>
        <div class="col-12 mb-3">
            <label class="form-label">Annual/Associate/Life/Foundation member of SASPI?</label>
            <div class="d-flex gap-4 mt-1">
                <label style="font-weight: 400;">
                    <input type="radio" name="saspi_member" value="Yes"
                        {{ ($admin->saspi_member ?? '') == 'Yes' ? 'checked' : '' }}> Yes
                </label>
                <label style="font-weight: 400;">
                    <input type="radio" name="saspi_member" value="No"
                        {{ ($admin->saspi_member ?? '') == 'No' ? 'checked' : '' }}> No
                </label>
            </div>
        </div>
        <div class="col-12 col-md-6 mb-3" id="membershipIdGroup" style="{{ ($admin->saspi_member ?? '') == 'Yes' ? '' : 'display: none;' }}">
            <label class="form-label">Membership ID</label>
            <input type="text" class="form-control" name="membership_id" value="{{ $admin->membership_id ?? '' }}">
        </div>
        <div class="col-12 col-md-6 mb-3">
            <label class="form-label">Designation</label>
            <input type="text" class="form-control" name="designation" value="{{ $admin->designation ?? '' }}">
        </div>
        <div class="col-12 col-md-6 mb-3">
            <label class="form-label">Affiliation (including dept name)</label>
            <input type="text" class="form-control" name="affiliation" value="{{ $admin->affiliation ?? '' }}">
        </div>
        <div class="col-12 col-md-6 mb-3">
            <label class="form-label">Academic and/or Research Experience (in years)</label>
            <input type="number" class="form-control" name="research_experience_years" min="0" step="0.1"
                value="{{ $admin->research_experience_years ?? '' }}">
        </div>
        <div class="col-12 col-md-6 mb-3">
            <label class="form-label">Average number of articles in indexed journals (PubMed/Scopus/DOAJ)</label>
            <input type="number" class="form-control" name="avg_articles_published" min="0" step="0.1"
                value="{{ $admin->avg_articles_published ?? '' }}">
        </div>
        <div class="col-12 col-md-6 mb-3">
            <label class="form-label">h-Index</label>
            <input type="text" class="form-control" name="h_index"
                value="{{ $admin->h_index ?? '' }}">
        </div>
        <div class="col-12 mb-3">
            <label class="form-label">Remarks</label>
            <textarea class="form-control" name="remarks" rows="3">{{ $admin->remarks ?? '' }}</textarea>
        </div>
    @endif

    <div class="col-12 my-3 border-top pt-3">
        <h6 class="fw-bold">Interests</h6>
    </div>
    <div class="col-12 mb-3">
        <div class="d-flex flex-wrap align-items-center gap-4">
            @forelse ($integrations ?? [] as $integration)
                <label style="font-weight: 400;">
                    <input type="checkbox" name="integrations[]" value="{{ $integration->id }}"
                        {{ in_array($integration->id, $adminIntegrations ?? []) ? 'checked' : '' }}>
                    {{ $integration->name }}
                </label>
            @empty
                <span class="text-muted">No interests available yet.</span>
            @endforelse
        </div>
    </div>
</div>
