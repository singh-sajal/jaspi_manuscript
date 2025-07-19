@csrf


<div class="row">
    <div class="input-group mb-3">
        <label class="input-group-text" for="name"> Role*</label>
        <input class="form-control" id="name" name="name" type="text" pattern="[a-zA-Z ]+(?:-[a-zA-Z ]+)*"
            title="Name can not have numbers and special characters" value="{{ !empty($role->name) ? $role->name : '' }}"
            required placeholder="Role Name">
        <span class="text-danger" data-error="name"></span>
    </div>

</div>

@php
    $i = 0;
@endphp



<div class="row gx-2 gy-2 mb-3">

    @foreach ($categories as $category => $permissions)
        <div class="col-3 mb-3">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center text-white" style="background: #3c566f;">
                    <input class="form-check-input master-checkbox m-0" data-category="{{ $category }}" type="checkbox" ">
                    <h6 class="flex-grow-1 font-12 mx-1 text-white my-0">{{ ucfirst($category) }}</h6>
                </div>
                <div class="card-body">
                     @foreach ($permissions as $permission)
                    <div class="form-check" data-category="{{ $category }}">
                        <input class="form-check-input permission-checkbox" id="permission_{{ $permission->id }}"
                            name="permission[]" type="checkbox" value="{{ $permission->id }}"
                            @if (!empty($role_permissions) && $role_permissions->contains($permission->id)) checked @endif>
                        @php
                            $lastDotPosition = strrpos($permission->name, '.');
                            $action =
                                $lastDotPosition !== false
                                    ? substr($permission->name, $lastDotPosition + 1)
                                    : $permission->name;
                        @endphp
                        <label class="form-check-label small text-dark"
                            for="permission_{{ $permission->id }}">{{ ucfirst($action) }}</label>
                    </div>
    @endforeach

</div>

</div>
</div>
@endforeach
</div>

<hr>
<div class="row">
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-primary" type="submit">Submit</button>
    </div>

</div>
