<div class="offcanvas-header">
    <h5 class="offcanvas-title" id="FileOffcanvasLabel">File Viewer</h5>
    <div class="d-flex align-items-center">
        <button class="btn btn-sm btn-outline-primary me-2" id="zoom-in-btn">+</button>
        <button class="btn btn-sm btn-outline-primary me-2" id="zoom-out-btn">−</button>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
</div>
<div class="offcanvas-body p-1" style="max-height: 100vh;" data-simplebar>
    <iframe id="fileViewerIframe" src="{{ $fileUrl }}" frameborder="0"
        style="width: 100%; height: 110vh; display: block; transform: scale(1); transform-origin: top left;"></iframe>
</div>
