<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Review Application</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form action="{{ route('admin.application.reviewed', ['uuid' => $uuid]) }}" method="post"
            novalidate enctype="multipart/form-data" novalidate>
            @csrf

            <div class="card-body">

                <div class="row">
                    <h5>Please select one of the following options</h5>
                    <div class="col-6">

                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="review_status" id="accept"
                                value="Accept" style="border-color: #bababa;">
                            <label class="form-check-label" for="accept">
                                <h5>Accept</h5>
                                -The submission is approved as-is, with no changes required.
                            </label>
                        </div>
                    </div>

                    <div class="col-6">

                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="review_status" id="minorChanges"
                                value="Accept with Minor Changes" required  style="border-color: #bababa;">
                            <label class="form-check-label" for="minorChanges">
                                <h5>Accept with Minor Changes</h5>
                                -Approved with only small revisions needed
                                <b><i class="ri-information-line"
                                        title="e.g., typos, formatting, minor clarifications."></i>
                                </b>
                            </label>
                        </div>
                    </div>

                    <div class="col-6">

                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="review_status" id="majorChanges"
                                value="Request Revisions with Major Changes" required  style="border-color: #bababa;">
                            <label class="form-check-label" for="majorChanges">
                                <h5>Request Revisions (Major Changes)</h5>
                                -Substantial changes are required before the work can be accepted
                                <b><i class="ri-information-line"
                                        title="e.g.: missing content, reorganization, factual updates"></i>
                                </b>
                            </label>
                        </div>
                    </div>

                    <div class="col-6">

                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="review_status"
                                id="rejectWithSuggestions" value="Reject with Suggestions" required  style="border-color: #bababa;">

                            <label class="form-check-label" for="rejectWithSuggestions">
                                <h5>Reject with Suggestions</h5>
                                -The submission is not acceptable in its current form but may be resubmitted after
                                significant rework.
                            </label>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="review_status" id="reject"
                                value="Reject" required  style="border-color: #bababa;">

                            <label class="form-check-label" for="reject">
                                <h5>Reject</h5>
                                -The submission does not meet the requirements or standards and will not be considered
                                further.
                            </label>
                        </div>
                    </div>
                    <span data-error="review_status" class="text-danger mt-2"></span>
                    <div class="form-group col-12 my-2">
                        <label for="name" class=""><h5>Remark</h5></label>
                        <textarea name="remark" class="form-control" cols="80" rows="10"></textarea>
                    </div>
                </div>

            </div>



            <div class="modal-footer d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </div>
</div>
