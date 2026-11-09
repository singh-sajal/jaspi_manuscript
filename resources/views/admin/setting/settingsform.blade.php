@csrf
<div class="modal-body">

    <div class="card-body">

        <div class="row mb-3">
            <div class="col">
                <label class="form-label" for="key">Key *</label>
                <input class="form-control" id="key" name="key" required minlength="2" type="text"
                    value="{{ !empty($form->key) ? $form->key : '' }}" placeholder="Enter key">
            </div>
            <div class="col">
                <label class="form-label" for="title">Value *</label>
                <input class="form-control" id="title" name="title" required minlength="2" type="text"
                    value="{{ !empty($form->title) ? $form->title : '' }}" placeholder="Enter title">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label for="description">Description</label>
                <textarea name="description" class="form-control" id="description" cols="30" rows="4">{{ !empty($form->description) ? $form->description : '' }}</textarea>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col">
                <label class="form-label" for="class">Class *</label>
                <input class="form-control" id="class" name="class" minlength="2" type="text"
                    value="{{ !empty($form->class) ? $form->class : '' }}" placeholder="Enter Class">
            </div>
            <div class="col">
                <label class="form-label" for="link">Link *</label>
                <input class="form-control" id="link" name="link" minlength="2" type="text"
                    value="{{ !empty($form->link) ? $form->link : '' }}" placeholder="Enter link">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="img">Image<br></label>
                <input class="form-control" accept="img*" style="width:100%;" type="file" name="img"
                    id="img" style="width:100%;">
            </div>
            <div class="col-md-2">
                <img id="img" class="avatar-sm"
                style="position: absolute; top: 50%; -ms-transform: translateY(-50%);"
                    src="{!! !empty($form->img) ? asset($form->img) : '' !!}" alt="img">
            </div>
            <div class="col-md-4">
                    <label class="form-label" for="alt">Alt *</label>
                    <input class="form-control" id="alt" name="alt" minlength="2" type="text"
                        value="{{ !empty($form->alt) ? $form->alt : '' }}" placeholder="Enter alt">
            </div>
        </div>

        <div class="row">
            <div class="col">
                <div class="mb-3">
                    <button class="btn btn-primary"
                        ref="{{ route('admin.settings.update', !empty($form->id) ? $form->id : '') }}" id="SubmitBtn"
                        type="submit">Submit</button>
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Close</button>
                </div>
            </div>

        </div>
    </div>

</div>
