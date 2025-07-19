<div class="row mt-4">
    <div class="col-12 mb-3">
        <label class="form-label required">Name</label>
        <input type="text" class="form-control" required name="name" value="{{ $author->name ?? '' }}">
    </div>
    <div class="col-12 col-md-6 mb-3">
        <label class="form-label required">Email</label>
        <input type="email" class="form-control" required readonly name="email" value="{{ $author->email ?? '' }}">
    </div>

    <div class="col-12 col-md-6 mb-3">
        <label class="form-label required">Phone</label>
        <input type="text" class="form-control" readonly inputmode="numeric" required name="phone" minlength="10"
            maxlength="10" pattern="[0-9]{10}" value="{{ $author->phone ?? '' }}">
    </div>

    {{-- Add optiosn for gender and dob --}}
    <div class="col-12 col-md-6 mb-3">
        <label class="form-label required" for="gender">Gender</label>
        <select name="gender" id="gender" class="form-select" required>
            <option value="" disabled selected>Select Gender</option>
            <option value="Male" {{ $author->gender == 'Male' ? 'selected' : '' }}>Male</option>
            <option value="Female" {{ $author->gender == 'Female' ? 'selected' : '' }}>Female</option>
            <option value="Other" {{ $author->gender == 'Other' ? 'selected' : '' }}>Other</option>
        </select>
    </div>

    <div class="col-12 col-md-6 mb-3">
        <label class="form-label required">Date of birth</label>
        <input type="date" class="form-control" required name="dob" value="{{ $author->dob ?? '' }}">
    </div>
    <div class="col-12 col-md-6 mb-3">
        <label class="form-label required">Country</label>
        <input type="text" class="form-control" disabled name="country" value="{{ $author->country ?? 'India' }}">
    </div>

    <div class="col-12 col-md-6 mb-3">
        <label class="form-label required">State</label>
        <select name="state" id="" required class="form-select">
            <option value="">Select State</option>
            @foreach ($states as $state)
                <option value="{{ $state }}" @if ($author->state == $state) selected @endif>
                    {{ $state }}</option>
            @endforeach
        </select>

    </div>
    <div class="col-12 mb-3">
        <label class="form-label required">City</label>
        <input type="text" class="form-control" required name="city" value="{{ $author->city ?? '' }}">
    </div>
    <div class="col-12 mb-3">
        <label class="form-label required">Pincode</label>
        <input type="text" inputmode="numeric" required pattern="[0-9]{6}" minlength="6" maxlength="6"
            class="form-control" name="pincode" value="{{ $author->pincode ?? '' }}">
    </div>
    <div class="col-12 mb-3">
        <label class="form-label">Address</label>

        <input type="text" class="form-control" required name="address" value="{{ $author->address ?? '' }}">
    </div>
</div>
