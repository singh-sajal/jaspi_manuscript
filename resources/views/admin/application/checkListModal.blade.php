<div class="modal fade" id="checklistModal" tabindex="-1" aria-labelledby="checklistModalLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">

    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="checklistModalLabel">
                    <i class="ri-checkbox-multiple-line me-2"></i> Manuscript Submission Checklist
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">

                <!-- Intro -->
                <div class="mb-4">
                    <p>
                        To begin with the submission of your manuscript, please complete the following checklist
                        questions and ensure all requirements are met as per the journal guidelines.
                    </p>
                    <small class="text-muted">* Denotes required field</small>
                </div>

                <!-- FORM START -->
                <form id="checklistForm" action="{{ route('author.application.check_list') }}" method="post" noAjax>
                    @csrf
                    @method('POST')

                    <!-- Reviewer Recommendation -->
                    <div class="card mb-3 shadow-sm">
                        <div class="card-body">
                            <label class="form-label fw-semibold">
                                * You will be requested to recommend a minimum of two reviewers.
                                Would you like to proceed with submitting reviewer recommendations?
                            </label>

                            <div class="mt-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="reviewer_recommendation"
                                        value="yes" required>
                                    <label class="form-check-label">Yes</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Checklist Questions --}}
                    {{-- @foreach (($questions ?? '') as $question => $name) --}}
                        <div class="card mb-3 shadow-sm">
                            <div class="card-body">
                                <label class="form-label fw-semibold">
                                    {{-- {!! $question ?? '' !!} --}}
                                </label>

                                <div class="mt-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name=""
                                            value="Yes" required>
                                        <label class="form-check-label">Yes</label>
                                    </div>

                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name=""
                                            value="No">
                                        <label class="form-check-label">No</label>
                                    </div>

                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name=""
                                            value="NA">
                                        <label class="form-check-label">N/A</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    {{-- @endforeach --}}

                    <!-- Disclaimer -->
                    <div class="card border-warning mt-4">
                        <div class="card-header bg-warning-subtle fw-bold">
                            DISCLAIMER
                        </div>
                        <div class="card-body small">
                            <ol>
                                <li><strong>Authorship Contribution:</strong> All authors have contributed substantially.</li>
                                <li><strong>Approval:</strong> All authors approve the final manuscript.</li>
                                <li><strong>Originality:</strong> The manuscript is not published elsewhere.</li>
                                <li><strong>Data Integrity:</strong> All data are included and verifiable.</li>
                                <li><strong>Conflicts of Interest:</strong> Fully disclosed.</li>
                                <li><strong>Copyright:</strong> CC BY-NC-ND 4.0 License applies.</li>
                                <li><strong>Corresponding Author:</strong> Authorized to act on behalf of all authors.</li>
                                <li><strong>Acknowledgments:</strong> Properly mentioned where applicable.</li>
                            </ol>
                        </div>
                    </div>

                    <!-- Agreement -->
                    <div class="text-center mt-4">
                        <div class="form-check d-inline-block">
                            <input class="form-check-input" type="checkbox" id="agreeDisclaimer">
                            <label class="form-check-label fw-semibold" for="agreeDisclaimer">
                                I have read & agree to the above Disclaimer
                            </label>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer mt-4">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" id="btnSubmit" class="btn btn-primary" disabled>
                            Proceed to Submission
                        </button>
                    </div>

                </form>
                <!-- FORM END -->

            </div>
        </div>
    </div>
</div>
