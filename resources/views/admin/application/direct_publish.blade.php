{{-- write a modal for direct publish with doi and file input --}}

<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Direct Publish</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form novalidate action="{{ route('admin.application.direct_publish', [
            'uuid' => $uuid,
        ]) }}"
            method="post" enctype="multipart/form-data">
            @csrf

            <div class="modal-body px-4 py-3">
                <div class="row">
                    <div class="col-12">
                        <label for="doi" class="form-label fw-semibold">
                            DOI Number
                        </label>

                        <input type="text" name="doi" class="form-control shadow-sm"
                            placeholder="Enter the DOI number">
                    </div>
                    <div class="col-12 mt-3">
                        <label for="file" class="form-label fw-semibold">
                            Published File
                        </label>

                        <input type="file" name="file_input" class="form-control shadow-sm"
                            placeholder="Upload the published file">
                </div>
            </div>

            <div class="modal-footer d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </div>
</div>
