<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Reject Application</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form action="{{ route('admin.application.rejection', [
            'uuid' => $uuid,
        ]) }}"
            method="post" enctype="multipart/form-data" novalidate>
            @csrf

            <div class="card-body">

                <div class="mb-4">
                    <h5 class="mb-2">Submission Review</h5>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="review_status" id="significantRework"
                            value="significant_rework_required">
                        <label class="form-check-label" for="significantRework">
                            The submission is not acceptable in its current form but may be resubmitted after
                            significant rework.
                        </label>
                    </div>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="radio" name="review_status" id="notAcceptable"
                            value="not_acceptable">
                        <label class="form-check-label" for="notAcceptable">
                            The submission does not meet the requirements or standards and will not be considered
                            further.
                        </label>
                    </div>
                    {{-- <div class="form-check mt-2">
                        <label class="form-check-label" for="comment">

                        </label>
                        <input class="form-check-input" type="textarea" name="comment" id="comment"
                            value="not_acceptable">
                    </div> --}}

                </div>
            </div>



            <div class="modal-footer d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </div>
</div>
