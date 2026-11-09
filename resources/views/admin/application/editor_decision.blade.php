<style>
    #reviewer-section .card {
        border-radius: 0.5rem;
    }

    #reviewer-section label {
        font-size: 0.95rem;
    }
</style>
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Decide & Assign</h5>
            <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form action="{{ route('admin.application.editorDecision', ['uuid' => $application->uuid]) }}" method="post"
            enctype="multipart/form-data" novalidate>
            @csrf

            <div class="modal-body">
                <!-- Decision Section -->
                <div class="card border shadow-sm mb-3">
                    <div class="card-body">
                        <label for="decision" class="form-label fw-semibold" required>
                            <i class="bi bi-check2-square me-1"></i> Decision
                        </label>
                        <select id="decision" name="decision" class="form-select" required>
                            <option value="">--- Select ---</option>
                            <option value="1">Send for Peer Review</option>
                            <option value="2">Send for Review with Editorial Notes</option>
                            <option value="3">Revise Before Review</option>
                            <option value="4">Reject Without Review</option>
                            <option value="5">Accept without further revision</option>
                        </select>
                    </div>
                </div>
                {{-- @dd($staffs->where('rev_type','external')); --}}

                <!-- Comment Section -->
                <div class="card border shadow-sm mb-3">
                    <div class="card-body">
                        <label for="comment" class="form-label fw-semibold" required>
                            <i class="bi bi-chat-left-text me-1"></i> Comment
                        </label>
                        <textarea id="comment" name="comment" class="form-control" rows="4" required
                            placeholder="Enter your comment... or type N/A"></textarea>
                    </div>
                </div>

                <!-- Reviewer Section -->
                <div id="reviewer-section" class="mb-3 d-none">
                    <div class="card border shadow-sm">
                        <div class="card-header  fw-semibold">
                            {{-- <i class="bi bi-people-fill me-1"></i> --}} Reviewer Assignment
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-funnel me-1"></i> Filter by Interest
                                </label>
                                <select class="form-select" id="integration_filter">
                                    <option value="">-- All Interests --</option>
                                    @foreach ($integrations as $integration)
                                        <option value="{{ $integration->id }}">{{ $integration->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row g-4">
                                <!-- Internal Reviewer -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" required>
                                        <i class="bi bi-person-badge-fill me-1"></i> Internal Reviewer
                                    </label>
                                    <div class="input-group mb-2">
                                        <input type="text" id="search_internal_reviewer" class="form-control" placeholder="Search internal reviewer...">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    </div>
                                    <select class="form-select" id="internal_reviewer_id" name="">
                                        <option value="">-- Select Internal Reviewer --</option>
                                        {{-- @if (isSuperAdmin())
                                            {
                                            <option value="{{ $admin->id ?? '' }}">{{ $admin->name ?? '' }}</option>
                                            }
                                        @endif --}}
                                        @foreach ($staffs->filter(function ($staff) {
        return $staff->is_admin == 1 || $staff->rev_type === 'internal';
    }) as $staff)
                                            <option value="{{ $staff->id }}" class="reviewer-option"
                                                data-integrations="{{ $staff->integrations->pluck('id')->implode(',') }}">{{ $staff->name ?? '' }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- External Reviewer -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-person-badge-fill me-1"></i> External Reviewer
                                    </label>
                                    <div class="input-group mb-2">
                                        <input type="text" id="search_external_reviewer" class="form-control" placeholder="Search external reviewer...">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    </div>
                                    <select class="form-select" id="external_reviewer_id" name="">
                                        <option value="">-- Select External Reviewer --</option>
                                        @foreach ($staffs->filter(function ($staff) {
        return $staff->is_admin == 1 || $staff->rev_type === 'external';
    }) as $staff)
                                            <option value="{{ $staff->id }}" class="reviewer-option"
                                                data-integrations="{{ $staff->integrations->pluck('id')->implode(',') }}">{{ $staff->name ?? '' }}</option>
                                        @endforeach
                                        @if (($application->accept_ex ?? '') != 1)
                                            <option value="not">Not required</option>
                                        @endif
                                    </select>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer d-flex justify-content-end">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-send-fill me-1"></i> Submit
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        function toggleReviewerSection() {
            let val = $("#decision").val();
            const $reviewerSection = $("#reviewer-section");
            const $internalSelect = $("#internal_reviewer_id");
            const $externalSelect = $("#external_reviewer_id");

            if (val === "1" || val === "2") {
                $reviewerSection.removeClass("d-none").addClass("d-block");
                $internalSelect.attr("name", "internal_reviewer_id").attr("required", true);
                $externalSelect.attr("name", "external_reviewer_id").attr("required", true);
            } else {
                $reviewerSection.removeClass("d-block").addClass("d-none");
                $internalSelect.removeAttr("name required");
                $externalSelect.removeAttr("name required");
            }
        }

        $("#decision").on("change", toggleReviewerSection);
        toggleReviewerSection(); // run on page load
    });
</script>

<script>
    // Simple search filter for reviewer dropdowns
    $(document).ready(function () {
        function filterOptions(searchInputId, selectId) {
            $("#" + searchInputId).on("keyup", function () {
                var value = $(this).val().toLowerCase();
                $("#" + selectId + " option").filter(function () {
                    // keep the first placeholder option always visible
                    if ($(this).val() === "") return true;
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });
        }

        filterOptions("search_internal_reviewer", "internal_reviewer_id");
        filterOptions("search_external_reviewer", "external_reviewer_id");
    });
</script>

<script>
    // Integration filter for reviewer dropdowns
    $(document).ready(function () {
        function applyIntegrationFilter() {
            var selected = $("#integration_filter").val();

            $(".reviewer-option").each(function () {
                if (!selected) {
                    $(this).toggle(true);
                    return;
                }

                var ids = ($(this).data("integrations") || "").toString().split(",").map(function (i) {
                    return i.trim();
                });

                $(this).toggle(ids.indexOf(selected) > -1);
            });
        }

        applyIntegrationFilter();

        $("#integration_filter").on("change", applyIntegrationFilter);
    });
</script>
