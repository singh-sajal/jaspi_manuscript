<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Co-Author Add/Update</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form action="{{ route('author.application.co_author', $application->uuid) }}" method="post"
            enctype="multipart/form-data" novalidate>
            @csrf

            <div class="modal-body">
                <div class="row g-3" id="co-author-${coAuthorIndex}">
                    <div class="col-6">
                        <label for="coauthor_name" class="form-label required">Co-Author Name</label>
                        <input type="text" required name="coauthor_name" id="coauthor_name" class="form-control"
                            placeholder="Enter full name" required>
                    </div>

                    <div class="col-6">
                        <label for="coauthor_email" class="form-label required">Co-Author Email</label>
                        <input type="email" required name="coauthor_email" id="coauthor_email" class="form-control"
                            placeholder="Enter email address" required>
                    </div>

                    <div class="col-6">
                        <label for="coauthor_phone" class="form-label required">Co-Author Phone</label>
                        <input type="text" required name="coauthor_phone" id="coauthor_phone" class="form-control"
                            placeholder="Enter phone number" required>
                    </div>


                    <div class="col-6">
                        <label for="author_affiliation" class="form-label required">Co-Author Affiliation</label>
                        <input type="text" class="form-control" id="author_affiliation" name="author_affiliation"
                            placeholder="Enter author affiliation" required
                            value="{{ $app_info->author_affiliation ?? '' }}">
                    </div>
                    <div class="col-6">
                        <label for="author_orcid_id" class="form-label required">Co-ORCID ID</label>
                        <input type="text" class="form-control" id="author_orcid_id" name="author_orcid_id"
                            placeholder="Enter ORCID ID" required value="{{ $app_info->author_orcid_id ?? '' }}">
                    </div>
                    <div class="col-6">
                        <label for="author_saspi_id" class="form-label">Co-SASPI ID</label>
                        <input type="text" class="form-control" id="author_saspi_id" name="author_saspi_id"
                            placeholder="Enter SASPI ID" value="{{ $app_info->author_saspi_id ?? '' }}">
                    </div>
                </div>
            </div>

            <div class="modal-footer d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
    </div>
</div>
