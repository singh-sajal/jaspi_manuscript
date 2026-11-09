@php
    use App\Models\Application;

    // Default route (fallback)
    $route = 'admin.application.file_upload';

    if ($app_id != 'na') {
        $app = Application::find($app_id);

        // If application exists and status is prepublished, change route
        if ($app && $app->status == 'accepted') {
            $route = 'admin.application.formatted_document';
        }
    }
@endphp
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Upload Formatted Proof</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form novalidate action="{{ route($route, ['id' => $id ?? 'na', 'type' => $type, 'app_id' => $app_id]) }}"
            method="post" enctype="multipart/form-data">
            @csrf

            <div class="modal-body px-4 py-3">
                <p class="text-muted mb-3">
                    Upload the formatted proof. The manuscript will be assigned to the technical editor for typesetting review.
                </p>

                <div class="row">
                    <div class="col-12 mb-4">
                        <label for="file_upload"
                            class="form-label fw-semibold {{ $type !== 'Supplimentry File' ? 'required' : '' }}">
                            Formatted Proof <span class="text-primary">(.docx)</span>
                        </label>
                        <input type="file" name="file_upload" id="file_upload" class="form-control shadow-sm"
                            {{ $type !== 'Supplimentry File' ? 'required' : '' }} accept=".docx">
                        <span class="text-danger small" data-error="file_upload"></span>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success">Upload &amp; Assign</button>
            </div>
        </form>
    </div>
</div> 

