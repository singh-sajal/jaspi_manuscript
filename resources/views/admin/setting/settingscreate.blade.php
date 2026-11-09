<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Add New Detail</h5>
            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('admin.settings.store') }}" method="post" id="CreateForm" class="form theme-form"
            enctype="multipart/form-data">
           @include('admin.setting.settingsform')
        </form>
    </div>
</div>
