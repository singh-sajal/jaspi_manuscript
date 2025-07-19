<div class="modal-dialog modal-dialog-centered ">
    <div class="modal-content shadow-lg rounded-4">
        <div class="modal-header  text-white">
            <h5 class="modal-title">Assign Application</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="{{ route('admin.application.assign', $uuid) }}" method="post" novalidate
            enctype="multipart/form-data">
            @csrf

            <div class="modal-body">
                <label class="form-label fw-semibold">Select from these:</label>
                {{-- <select class="form-select mb-4" id="staff_id" name="staff_id" required>
                    <option value="">--Select--</option>
                    @foreach ($staffs as $staff)
                        <option value="{{ $staff->id }}">
                            {{ $staff->name }}
                        </option>
                    @endforeach
                </select> --}}
                <select class="form-select mb-4" id="staff_id" name="staff_id" required>
                    <option value="">--Select--</option>
                    @foreach ($staffs as $staff)
                        <option value="{{ $staff->id }}" >
                            {{ $staff->name }}
                        </option>
                    @endforeach
                </select>

            </div>

            <div class="modal-footer d-flex justify-content-end">
                <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill">Submit</button>
            </div>
        </form>
    </div>
</div>
