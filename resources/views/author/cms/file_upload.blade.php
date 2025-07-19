<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Upload file: Add/Update</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form novalidate
            action="{{ route('author.application.file_upload', [
                'id' => $id ?? 'na',
                'type' => $type,
                'app_id' => $app_id,
            ]) }}"
            method="post" enctype="multipart/form-data">
            @csrf

            <div class="modal-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <label for="file_upload "
                            class="form-label {{ $type !== 'Supplimentry File' ? 'required' : '' }}">Upload File :
                            {{ $type ?? '' }}</label>
                        <input type="file" name="file_upload" id="file_upload" class="form-control"
                            {{ $type !== 'Supplimentry File' ? 'required' : '' }} accept=".pdf" data-max-size="10240"
                            data-file-types="pdf">
                    </div>
                    <span class="text-danger" data-error="file_upload"></span>
                </div>

            </div>

            <div class="modal-footer d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </div>
</div>
