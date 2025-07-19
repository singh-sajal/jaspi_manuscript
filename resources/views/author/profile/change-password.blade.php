<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Update Password</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form action="{{ route('author.password.update') }}" novalidate enctype="multipart/form-data" method="POST"
            autocomplete="off">
            @csrf
            <div class="modal-body">

                <div class="row">
                    <div class="col-12 mb-3">
                        <label for="password" class="form-label required">Current Password</label>
                        <input class="form-control" type="password" required name="old_password"
                            placeholder="*********">

                    </div>
                    <div class="col-12 mb-3">
                        <label for="password" class="form-label required">New Password</label>
                        <div class="position-relative auth-pass-inputgroup">
                            <input class="form-control passbox" type="password" required name="password"
                                placeholder="*********">
                            <button
                                class="btn btn-link position-absolute text-decoration-none text-muted password-addon end-0 top-0 shadow-none"
                                type="button" id="password-addon"><i class="ri-eye-fill align-middle"></i></button>

                        </div>

                    </div>
                    <div class="col-12 mb-3">
                        <label for="password_confirmation" class="form-label required">Confirm New Password</label>
                        <div class="position-relative auth-pass-inputgroup mb-3">
                            <input class="form-control passbox" type="password" required name="password_confirmation"
                                placeholder="*********">
                            <button
                                class="btn btn-link position-absolute text-decoration-none text-muted password-addon end-0 top-0 shadow-none"
                                type="button" id="password-addon"><i class="ri-eye-fill"></i></button>


                        </div>

                    </div>
                </div>


            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary btn-sm">Submit</button>
            </div>
        </form>
    </div>

</div>
