<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Add New Role</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
            </button>
        </div>
        <form action="{{ route('admin.roles.store') }}" novalidate enctype="multipart/form-data" method="POST">
            @csrf
            <div class="modal-body">
                <div class="row">

                    <div class="col mb-3">
                        <label class="form-label required" for="name">Role Name:</label>
                        <input type="text" class="form-control" name="name" value="{{ $role->name ?? '' }}"
                            placeholder="Enter Role Name" required>
                    </div>

                </div>



            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" type="submit">Submit</button>

            </div>
        </form>
    </div>
</div>
