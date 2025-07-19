<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Revise Application</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form action="{{ route('admin.application.revise', ['uuid' => $uuid]) }}" method="post"
            enctype="multipart/form-data" novalidate>
            @csrf

            <div class="card-body">

                <div class="mb-4">
                    <h5 class="mb-2">Request Revisions (Minor Changes)</h5>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="review_status" id="minorChanges"
                            value="approved_small_revisions">
                        <label class="form-check-label" for="minorChanges">
                            Approved with only small revisions needed (e.g., typos, formatting, minor clarifications).
                        </label>
                    </div>
                </div>

                <div class="mb-4">
                    <h5 class="mb-2">Request Revisions (Major Changes)</h5>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="review_status" id="majorChanges"
                            value="substantial_changes_required">
                        <label class="form-check-label" for="majorChanges">
                            Substantial changes are required before the work can be accepted (e.g., missing content,
                            reorganization, factual updates).
                        </label>
                    </div>
                </div>


            </div>



            <div class="modal-footer d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </div>
</div>
