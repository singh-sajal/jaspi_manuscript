<div class="modal-dialog modal-dialog-centered ">
    <div class="modal-content shadow-lg rounded-4">
        <div class="modal-header  text-white">
            <h5 class="modal-title">Assign Publisher</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close">X</button>
        </div>
        <form action="{{ route('admin.application.publish', $uuid) }}" method="post" novalidate
            enctype="multipart/form-data">
            @csrf

            <div class="modal-body">
                <label class="form-label fw-semibold required"><strong> Upload the final script: </strong></label>
                <input type="file" name="final_script" class="form-control " required>
                @error('final_script')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror

                <label class="form-label fw-semibold"> <strong>Comment:</strong></label>
                <textarea class="form-control" rows="7" name="comment"></textarea>


            </div>

            <div class="modal-footer d-flex justify-content-end">
                <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill">Submit</button>
            </div>
        </form>
    </div>
</div>
