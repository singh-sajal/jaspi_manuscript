<div class="kpi-container">
    @foreach ($categories as $category => $permissions)
    <div class="card h-100">
        <div class="card-header d-flex align-items-center bg-primary text-white">
            <input class="form-check-input master-checkbox m-0" data-category="{{ $category }}" type="checkbox" ">
                 <h6 class=" flex-grow-1 font-12 mx-1 my-0 text-white">{{ ucfirst($category) }}</h6>
        </div>


        <div class="card-body">


            @foreach ($permissions as $permission)
            @php
            $lastDotPosition = strrpos($permission->name, '.');
            $action =
            $lastDotPosition !== false
            ? substr($permission->name, $lastDotPosition + 1)
            : $permission->name;
            @endphp
            <div class="form-check" data-category="{{ $category }}">
                <input class="form-check-input permission-checkbox" id="permission_{{ $permission->id }}"
                    name="permission[]" type="checkbox" value="{{ $permission->id }}" {{ !empty($role_permissions) &&
                    $role_permissions->contains($permission->id) ? 'checked' : '' }}>

                <label class="form-check-label small text-dark" for="permission_{{ $permission->id }}">{{
                    ucfirst($action) }}</label>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>
