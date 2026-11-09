@php
    $internal_reviewer_id = $application->timelines()
        ->where('status', 'assigning reviewers')
        ->where('data->type', 'internal')
        ->orderBy('created_at', 'desc')
        ->value('assigned_to_id');

    $external_reviewer_id = $application->timelines()
        ->where('status', 'assigning reviewers')
        ->where('data->type', 'external')
        ->orderBy('created_at', 'desc')
        ->value('assigned_to_id');
@endphp

<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header ">
            <h5 class="modal-title">Reassign Reviewers</h5>
            <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form action="{{ route('admin.application.re_review', ['uuid' => $application->uuid]) }}" method="post"
            enctype="multipart/form-data" novalidate>
            @method('PUT')
            @csrf

            <div class="modal-body">
                <div class="card border shadow-sm mb-3">
                    <div class="card-body">
                        <label for="comment" class="form-label fw-semibold" required>
                            <i class="bi bi-chat-left-text me-1"></i> Comment
                        </label>
                        <textarea id="comment" name="comment" class="form-control" rows="4" required
                            placeholder="Enter your comment... or type N/A">{{ $application->data['comment'] ?? '' }}</textarea>
                    </div>
                </div>

                <div id="reviewer-section" class="mb-3">
                    <div class="card border shadow-sm">
                        <div class="card-header">
                            Reviewer Assignment
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" required>
                                        <i class="bi bi-person-badge-fill me-1"></i> Internal Reviewer
                                    </label>
                                    <select class="form-select" id="internal_reviewer_id" name="internal_reviewer_id" required>
                                        <option value="">-- Select Internal Reviewer --</option>
                                        @foreach ($staffs->filter(function ($staff) {
                                            return $staff->is_admin == 1 || $staff->rev_type === 'internal';
                                        }) as $staff)
                                            <option value="{{ $staff->id }}"
                                                {{ $internal_reviewer_id == $staff->id ? 'selected' : '' }}>
                                                {{ $staff->name ?? '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="text-{{ $application->accept_in === '1' ? 'success' : ($application->accept_in === '0' ? 'danger' : 'warning') }}">
                                        {{ $application->accept_in === '1' ? 'Accepted' : ($application->accept_in === '0' ? 'Rejected' : 'Pending') }}
                                    </span>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold" required>
                                        <i class="bi bi-person-workspace me-1"></i> External Reviewer
                                    </label>
                                    <select class="form-select" id="external_reviewer_id" name="external_reviewer_id" required>
                                        <option value="">-- Select External Reviewer --</option>
                                        @foreach ($staffs->filter(fn($staff) => $staff->is_admin == 1 || $staff->rev_type === 'external') as $staff)
                                            <option value="{{ $staff->id }}"
                                                {{ $external_reviewer_id == $staff->id ? 'selected' : '' }}>
                                                {{ $staff->name ?? '' }}
                                            </option>
                                        @endforeach
                                        <option value="not"
                                            {{ empty($application->assigned_to_ex_id) && ($application->accept_ex ?? '') != '1' ? 'selected' : '' }}>
                                            Not required
                                        </option>
                                    </select>

                                    <span id="external-status"
                                        class="text-{{ $application->accept_ex === '1' ? 'success' : ($application->accept_ex === '0' ? 'danger' : 'warning') }}
                                        {{ ($application->assigned_to_ex_id ?? '') == 'not' ? 'd-none' : '' }}">
                                        {{ $application->accept_ex === '1' ? 'Accepted' : ($application->accept_ex === '0' ? 'Rejected' : 'Pending') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer d-flex justify-content-end">
                <button type="submit" class="btn btn-success px-4">
                    <i class="bi bi-arrow-repeat me-1"></i> Update
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        function toggleExternalStatus() {
            const externalVal = $("#external_reviewer_id").val();
            const $status = $("#external-status");

            if (externalVal === "not") {
                $status.addClass("d-none");
            } else {
                $status.removeClass("d-none");
            }
        }

        // Run on load
        toggleExternalStatus();

        // Run on change
        $("#external_reviewer_id").on("change", toggleExternalStatus);
    });
</script>
