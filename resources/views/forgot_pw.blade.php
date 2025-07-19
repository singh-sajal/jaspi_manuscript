<div class="modal-dialog">
    <div class="modal-content p-3">
        <div class="modal-header">
            <h6 class="modal-title">Forgot Password </h6>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="contactForm" action="{{ route('forgot_password') }}" novalidate enctype="multipart/form-data"
            method="POST">
            @csrf
            <div class="modal-body">

                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="name">Please Select your role type</label>
                            <div class="mt-0" style="margin-bottom: 5px ! important;">
                                <select class="form-select form-group w-100" required name="role">
                                    <option value="" selected disabled>Choose your role</option>
                                    <option value="admin">Admin</option>
                                    <option value="admin">Editor</option>
                                    <option value="admin">Reviewer</option>
                                    <option value="admin">Publisher</option>
                                    <option value="author">Author</option>
                                </select>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col">
                        <label for="name">Your Email Id</label>
                        <div class="mb-3">
                            <div class="input-group">
                                <i class="fas fa-user"></i>
                                <input type="text" name="username" required class="form-control"
                                    placeholder="User Email Id">
                            </div>
                            <span class="text-danger" data-error="username"></span>
                        </div>
                    </div>

                </div>


            </div>
            <div class="modal-footer">

                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>

    </div>
</div>
