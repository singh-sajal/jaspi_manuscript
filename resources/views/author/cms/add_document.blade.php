<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Upload file: Add another Document</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form novalidate
            action="{{ route('author.application.add_document', [
                'type' => $type,
                'app_id' => $app_id,
            ]) }}"
            method="post" enctype="multipart/form-data">
            @csrf

            <div class="modal-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <label for="file_upload "
                            class="form-label {{ $type !== 'Supplimentry File' ? 'required' : '' }}">Upload File</label>

                        <input type="text" name="file_title" id="file_title" class="form-control mt-3"
                            placeholder="File Title: Supporting File, etc" value="" required>

                        <input type="file" name="file_upload" id="file_upload" class="form-control" required
                            accept=".pdf,.docx,.doc" data-max-size="10240" data-file-types="pdf,docx">
                        <span class="text-danger" data-error="file_upload"></span>

                        <input type="text" name="legend" id="legend" class="form-control mt-3"
                            placeholder="Legend" value="">
                    </div>
                </div>

            </div>

            <div class="modal-footer d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </div>
</div>
