@extends('admin.app')

@section('title', 'Inactive Manuscripts')
@section('breadcrumb', 'Inactive Manuscripts')
@section('page-title', 'Inactive Manuscripts')

@section('breadcrumb-button')
    <div class="d-flex align-items-center gap-2 flex-wrap">

        {{-- Section totals: how many are idle, how many reminders have gone
             out in total, and when the last one went out. --}}
        <span class="badge badge-sm badge-rounded bg-secondary-subtle text-secondary">
            Inactive: {{ $stats['inactive'] }}
        </span>
        <span class="badge badge-sm badge-rounded bg-secondary-subtle text-secondary">
            Reminders sent: {{ $stats['mails_sent'] }}
        </span>
        <span class="badge badge-sm badge-rounded bg-secondary-subtle text-secondary">
            Last mail sent:
            {{ $stats['last_sent_at'] ? $stats['last_sent_at']->format('d-M-Y, h:i A') : 'Never' }}
        </span>

        <button type="button" class="btn btn-sm btn-warning btn-label" data-bs-toggle="modal"
            data-bs-target="#bulkInactiveMailModal">
            <i class="ri-mail-send-line label-icon me-1"></i> Send Bulk Reminder
        </button>

    </div>
@endsection

@section('content')
    <div class="offcanvas offcanvas-end" tabindex="-1" style="z-index: 10000;width:80%" id="FileOffcanvas"
        data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="FileOffcanvasLabel"></div>

    {{-- Target for the per-row "Send Mail" actionHandler --}}
    <div class="modal" id="AjaxModal" tabindex="-1"></div>

    {{-- Bulk mail composer: one email per inactive manuscript owner --}}
    <div class="modal fade" id="bulkInactiveMailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="post" action="{{ route('admin.application.inactive.bulk_mail') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">
                            <i class="ri-mail-send-line me-2 text-warning"></i>Bulk Reminder &mdash; Inactive Manuscripts
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning py-2">
                            <i class="ri-information-line me-1"></i>
                            This emails <strong>{{ $stats['inactive'] }}</strong> manuscript owner(s). One email is sent
                            per manuscript, so an author with several idle manuscripts receives one email for each.
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
                            <input type="text" name="subject" class="form-control" maxlength="255" required
                                value="Action required: your manuscript is awaiting your response"
                                placeholder="Action required: your manuscript is awaiting your response">
                            <div class="form-text text-danger" data-error="subject"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Message</label>
                            <textarea name="message" rows="5" class="form-control" maxlength="5000"
                                placeholder="Optional. Leave blank to send our standard wording for the manuscript's current status."></textarea>
                            <div class="form-text text-muted">
                                Leave blank to send the default message, which explains what is needed for the
                                manuscript's current status.
                            </div>
                            <div class="form-text text-danger" data-error="message"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning"
                            onclick="return confirm('Send a reminder to all {{ $stats['inactive'] }} inactive manuscript owner(s)?');">
                            <i class="ri-send-plane-line me-1"></i> Send
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body" id="Saarni">

            <table class="table-bordered w-100 dt-datatable table">
                <thead>
                    <tr>
                        <th>Sr. No.</th>
                        <th data-sortable="true" data-column="application_id">Manuscript Id</th>
                        <th>Author</th>
                        <th data-sortable="true" data-column="status">Status</th>
                        <th>Awaiting Author Since</th>
                        <th class="text-center">Reminders Sent</th>
                        <th>Last Mail Sent</th>
                        <th>Options</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('javascripts')
    @php
        $options = [
            'selector' => 'Saarni',
            'url' => route('admin.application.index', ['type' => $type]),
            'moduleName' => 'Inactive Manuscripts',
        ];
    @endphp
    @include('includes.saarnijs', ['options' => $options])
    <script src="{{ asset('engines/fileviewer.js') }}"></script>
@endsection
