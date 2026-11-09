<style>
    #deny-reason-section {
        transition: all 0.3s ease;
    }
</style>
<div class="modal-dialog modal-lg">
    <div class="modal-content shadow-lg rounded-4">
        <div class="modal-header">
            <h5 class="modal-title">Accept or Deny Review Assignment</h5>
            <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form action="{{ route('admin.application.rev_decision', ['uuid' => $application->uuid]) }}" method="post"
            enctype="multipart/form-data" novalidate>
            @csrf

            <div class="modal-body">
                <div class="alert alert-info mb-3">
                    <strong>Manuscript ID:</strong> {{ $application->application_id }}<br>
                    <strong>Title:</strong> {{ $application->title ?? 'N/A' }}<br>
                    @if (!empty($application->description))
                        <span class="fs-12 text-decoration-underline actionHandler cursor-pointer"
                            data-url="{{ route('admin.application.viewAbstract', $application->uuid) }}">
                            View Abstract
                        </span>
                    @endif
                </div>

                <!-- Decision Section -->
                <div class="card border shadow-sm mb-3">
                    <div class="card-body">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-check2-square me-1"></i> Your Decision
                        </label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="decision" id="acceptDecision"
                                value="1" required>
                            <label class="form-check-label fw-semibold" for="acceptDecision">
                                Accept - I will review this manuscript
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="decision" id="denyDecision"
                                value="0" required>
                            <label class="form-check-label fw-semibold text-danger" for="denyDecision">
                                Deny - I am unable to review this manuscript
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Reason Section (shown only on Deny) -->
                <div id="deny-reason-section" class="card border shadow-sm mb-3 d-none">
                    <div class="card-body">
                        <label for="reason" class="form-label fw-semibold">
                            <i class="bi bi-chat-left-text me-1"></i> Reason for Denying
                        </label>
                        <textarea id="reason" name="reason" class="form-control" rows="4"
                            placeholder="Please explain why you are declining this review assignment..."></textarea>
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
        const $denySection = $('#deny-reason-section');
        const $reason = $('#reason');

        $('input[name="decision"]').on('change', function() {
            const isDeny = $(this).val() === '0';
            if (isDeny) {
                $denySection.removeClass('d-none').addClass('d-block');
                $reason.attr('required', true);
            } else {
                $denySection.removeClass('d-block').addClass('d-none');
                $reason.removeAttr('required');
            }
        });
    });
</script>