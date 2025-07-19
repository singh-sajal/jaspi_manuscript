<div class="card-body">

    <div class="row">
        <div class="form-group col-md-6 my-2">
            <label for="name" class="required">Name</label>
            <input type="text" name="name" class="form-control" required value="{{ $author->name ?? '' }}">
        </div>

        <div class="form-group col-md-6 my-2">
            <label for="email" class="required">Email</label>
            <input type="email" name="email" class="form-control" required value="{{ $author->email ?? '' }}">
        </div>
    </div>

    <div class="row">
        <div class="form-group col-md-6 my-2">
            <label for="phone" class="required">Phone</label>
            <input type="tel" name="phone" class="form-control" minlength="10" maxlength="10" required
                value="{{ $author->phone ?? '' }}">
        </div>

        <div class="form-group col-md-6 my-2">
            <label for="dob" class="required">Date of Birth</label>
            <input type="date" name="dob" class="form-control" required value="{{ $author->dob ?? '' }}">
        </div>
    </div>

    <div class="row">
        <div class="form-group col-md-6 my-2">
            <label for="gender" class="required">Gender</label>
            <select name="gender" class="form-control" required>
                <option value="">Select Gender</option>
                <option value="Male" {{ ($author->gender ?? '') == 'Male' ? 'selected' : '' }}>Male</option>
                <option value="Female" {{ ($author->gender ?? '') == 'Female' ? 'selected' : '' }}>Female</option>
                <option value="Other" {{ ($author->gender ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
            </select>
        </div>

        <div class="form-group col-md-6 my-2">
            <label for="country" class="required">Country</label>
            <input type="text" name="country" class="form-control" required value="{{ $author->country ?? '' }}">
        </div>
    </div>

    <div class="row">
        <div class="form-group col-md-6 my-2">
            <label for="state" class="required">State</label>
            <input type="text" name="state" class="form-control" required value="{{ $author->state ?? '' }}">
        </div>

        <div class="form-group col-md-6 my-2">
            <label for="city" class="required">City</label>
            <input type="text" name="city" class="form-control" required value="{{ $author->city ?? '' }}">
        </div>
    </div>

    <div class="row">
        <div class="form-group col-md-6 my-2">
            <label for="pincode" class="required">Pincode</label>
            <input type="text" name="pincode" class="form-control" required value="{{ $author->pincode ?? '' }}">
        </div>

        <div class="form-group col-md-6 my-2">
            <label for="address" class="required">Address</label>
            <textarea name="address" class="form-control" rows="1" required>{{ $author->address ?? '' }}</textarea>
        </div>
    </div>

    <div class="row">
        <div class="form-group col-md-12 my-2">
            <label class="form-label required" for="avatar">Avatar</label>
            <div class="d-flex align-items-center">
                <input class="form-control flex-grow-1 has-thumbnail" name="avatar" data-max-size="1024"
                    data-file-types="jpg,png" type="file">
                <div class="ms-2">
                    @php
                        $picture = $author->avatar ?? '';
                    @endphp
                    <img class="avatar avatar-xs thumbnail border border-1" alt="avatar"
                        src="{{ asset($picture ?: 'admin/avatar/user-avatar.svg') }}">
                </div>
            </div>
            <span><small>Standard Ratio: 1:1 (500px * 500px)</small></span>
            <span class="text-danger" data-error="avatar"></span>
        </div>
    </div>

</div>
