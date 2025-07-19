<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Change Password</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
            </button>
        </div>

        <form action="{{ route('admin.author.generateCredentials', ['uuid' => $admin->uuid]) }}" novalidate id="CreateForm"
            enctype="multipart/form-data" method="POST">
            @csrf
            <div class="modal-body">
                <div class="row">

                    <div class="col mb-3">
                        <label class="form-label required" for="email">Email:</label>
                        <input type="email" class="form-control bg-light" readonly name="email" id="email"
                            value="{{ $admin->email ?? '' }}" placeholder="Login email" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col mb-3">
                        <label class="form-label required" for="password">Password: </label>

                        <div class="position-relative auth-pass-inputgroup mb-3">
                            <input class="form-control passbox" type="password" required name="password" required
                                placeholder="*********">
                            <button
                                class="btn btn-link position-absolute text-decoration-none text-muted password-addon end-0 top-0 shadow-none"
                                type="button" id="password-addon"><i class="ri-eye-fill align-middle"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" type="submit">Submit</button>

            </div>
        </form>

    </div>
</div>
