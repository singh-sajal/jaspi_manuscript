<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Upload file: Add/Update</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form novalidate
            action="{{ route('admin.application.file_upload', [
                'id' => $id ?? 'na',
                'type' => $type,
                'app_id' => $app_id,
                'legend' => $legend,
            ]) }}"
            method="post" enctype="multipart/form-data">
            @csrf

            <div class="modal-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <label for="file_upload "
                            class="form-label {{ $type !== 'Supplimentry File' ? 'required' : '' }}">Upload File :
                            {{ $type ?? '' }}</label>
                        @if ($type == 'Upload ICMJE Declaration')
                            <input type="file" name="file_upload" id="file_upload" class="form-control"
                                {{ $type !== 'Supplimentry File' ? 'required' : '' }} accept=".pdf"
                                data-max-size="10240" data-file-types="pdf">
                        @elseif ($type == 'Upload Manuscript')
                            <input type="file" name="file_upload" id="file_upload" class="form-control"
                                {{ $type !== 'Supplimentry File' ? 'required' : '' }} accept=".docx"
                                data-max-size="10240" data-file-types="docx">
                        @else
                            <input type="file" name="file_upload" id="file_upload" class="form-control"
                                {{ $type !== 'Supplimentry File' ? 'required' : '' }} accept=".pdf,.docx,.doc"
                                data-max-size="10240" data-file-types="pdf,docx">
                        @endif
                    </div>
                    <span class="text-danger" data-error="file_upload"></span>
                    <input type="text" name="legend" id="legend" class="form-control mt-3" placeholder="Legend"
                        value="{{ $legend == 'na' ? '' : $legend }}">

                </div>

            </div>

            <div class="modal-footer d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </div>
</div>
