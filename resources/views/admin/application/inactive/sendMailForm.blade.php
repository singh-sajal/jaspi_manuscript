<div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
        <form method="post" action="{{ route('admin.application.inactive.send_mail', ['uuid' => $application->uuid]) }}">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="ri-mail-send-line me-2 text-warning"></i>Send Reminder to Author
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="alert alert-light border mb-3">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td class="text-muted" style="width: 30%">Manuscript ID</td>
                            <td><strong>{{ $application->application_id ?? '---' }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Title</td>
                            <td>{{ \Illuminate\Support\Str::limit($application->title ?? '---', 60) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Author</td>
                            <td>
                                {{ $application->author->name ?? ($application->author_name ?? '---') }}
                                @if ($application->author?->email)
                                    <div class="fs-12 text-muted">{{ $application->author->email }}</div>
                                @else
                                    <div class="fs-12 text-danger">No email on file &mdash; this will be skipped.</div>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Current status</td>
                            <td>
                                <span class="badge badge-sm badge-rounded bg-warning-subtle text-warning">
                                    {{ \Illuminate\Support\Str::ucfirst(str_replace('_', ' ', (string) $application->status)) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Waiting for</td>
                            <td><strong class="text-warning">{{ $application->waiting_days ?? '---' }} day(s)</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Reminders already sent</td>
                            <td>{{ $application->inactivity_mails_count ?? 0 }}</td>
                        </tr>
                    </table>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
                    <input type="text" name="subject" class="form-control" maxlength="255" required
                        value="Action required: your manuscript {{ $application->application_id ?? '' }} is awaiting your response"
                        placeholder="Action required: your manuscript is awaiting your response">
                    <div class="form-text text-danger" data-error="subject"></div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Message</label>
                    <textarea name="message" rows="5" class="form-control" maxlength="5000"
                        placeholder="Optional. Leave blank to send our standard wording for the manuscript's current status."></textarea>
                    <div class="form-text text-muted">
                        Leave blank to send the default message, which explains what is needed for this status.
                    </div>
                    <div class="form-text text-danger" data-error="message"></div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-warning">
                    <i class="ri-send-plane-line me-1"></i> Send Reminder
                </button>
            </div>
        </form>
    </div>
</div>
