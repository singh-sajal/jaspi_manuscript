<div class="modal-dialog modal-xl">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">File Viewer</h5>
            <div class="d-flex align-items-center me-3">
                <button class="btn btn-sm btn-outline-primary me-2" id="zoom-in-btn">+</button>
                <button class="btn btn-sm btn-outline-primary" id="zoom-out-btn">−</button>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
        </div>
        <div class="modal-body p-1" style="max-height: 80vh;" data-simplebar>
            {{-- Add IFrame here --}}
            <iframe id="fileViewerIframe" src="{{ $fileUrl }}" frameborder="0" id="fileViewerIframe"
                style="width: 100%; height: 110vh; display: block; transform: scale(1); transform-origin: top left;"></iframe>
        </div>
    </div>
</div>
