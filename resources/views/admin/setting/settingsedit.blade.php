<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalgridLabel">Update Detail</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('admin.settings.update', $form->id) }}" method="POST" id="EditForm" enctype="multipart/form-data"
            class="form theme-form">
            @method('PUT')
            @include('admin.setting.settingsform')
        </form>

    </div>
</div>
