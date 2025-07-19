<div class="card-body">

    <div class="form-group my-2">
        <label for="name" class="required">Name</label>
        <input type="text" name="name" class="form-control" required value="{{ $record->name ?? '' }}">
    </div>

    <div class="form-group my-2">
        <label for="email" class="required">Email</label>
        <input type="email" name="email" class="form-control" required value="{{ $record->email ?? '' }}">
    
    </div>

    <div class="form-group my-2">
        <label for="phone" class="required">Phone</label>
        <input type="tel" name="phone" class="form-control" minlength="10" maxlength="10" required
            value="{{ $record->phone ?? '' }}">
    </div>

    <div class=" form-group col-12 my-2">
        <label class="form-label required" for="avatar">Avatar</label>
        <div class="d-flex align-items-center">

            <input class="form-control flex-grow-1 has-thumbnail" name="avatar" data-max-size="1024"
                data-file-types="jpg,png" placeholder="" type="file">

            <div class="ml-2">
                @php
                    $picture = $record->avatar ?? '';
                @endphp
                <img class="avatar avatar-xs thumbnail border border-1" alt="avatar"
                    src="{{ asset($picture ?? 'admin/avatar/user-avatar.svg') }}">
            </div>
        </div>
        <span><small> Standard Ratio: 1:1 (500px * 500px)</small></span>
        <span class="text-danger" data-error="avatar"></span>
    </div>

    <div class="form-group my-2">
        <label for="role" class="required">Role</label>
        <select name="role_id" class="form-control" required>
            <option value="">-- Select Role --</option>
            @foreach ($roles as $role)
                <option value="{{ $role->name }}"
                    {{ !empty($staffRoles) && in_array($role->id, $staffRoles) ? 'selected' : '' }}>
                    {{ ucfirst($role->name) }}
                </option>
            @endforeach
        </select>
    </div>

</div>
