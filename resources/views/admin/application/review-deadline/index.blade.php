@extends('admin.app')

@section('title', 'Review Deadlines')
@section('breadcrumb', 'Review Deadlines')
@section('page-title', 'Review Deadlines')

@section('breadcrumb-button')
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="badge badge-sm badge-rounded bg-warning-subtle text-warning">
            Overdue: {{ $stats['overdue'] }}
        </span>
        <span class="badge badge-sm badge-rounded bg-danger-subtle text-danger">
            Timed out: {{ $stats['timed_out'] }}
        </span>
        <span class="badge badge-sm badge-rounded bg-secondary-subtle text-secondary">
            Reminders sent: {{ $stats['reminded'] }}
        </span>
    </div>
@endsection

@section('content')
    <div class="offcanvas offcanvas-end" tabindex="-1" style="z-index: 10000;width:80%" id="FileOffcanvas"
        data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="FileOffcanvasLabel"></div>
    <div class="modal" id="AjaxModal" tabindex="-1"></div>

    <div class="card">
        <div class="card-body" id="Saarni">
            <div class="alert alert-light border py-2 mb-3">
                <i class="ri-information-line me-1"></i>
                Only reviewers who are at their <strong>first reminder deadline or later</strong> are listed here.
                Reminders are sent manually &mdash; one click per reviewer. When a reviewer passes their timeout
                deadline, the handling editor is notified automatically. Reviewers are never unassigned automatically.
            </div>

            <table class="table-bordered w-100 dt-datatable table">
                <thead>
                    <tr>
                        <th>Sr. No.</th>
                        <th>Manuscript</th>
                        <th>Reviewer</th>
                        <th data-sortable="true" data-column="type">Type</th>
                        <th data-sortable="true" data-column="assigned_at">Assigned On</th>
                        <th>Days Elapsed</th>
                        <th data-sortable="true" data-column="reminder_1_at">Next Deadline</th>
                        <th>Stage</th>
                        <th class="text-center">Reminders Sent</th>
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
            'moduleName' => 'Review Deadlines',
        ];
    @endphp
    @include('includes.saarnijs', ['options' => $options])
    <script src="{{ asset('engines/fileviewer.js') }}"></script>

    {{-- One-click manual reminder --}}
    <script>
        window.sendReviewerReminder = (button) => {
            const url = button.dataset.url;
            const reviewer = button.dataset.reviewer;
            const number = button.dataset.reminder;

            swal({
                title: "Send reminder?",
                text: `Reminder #${number} will be emailed to ${reviewer}.`,
                icon: "warning",
                buttons: true,
            }).then((confirmed) => {
                if (!confirmed) {
                    return;
                }

                const originalHTML = button.innerHTML;
                button.disabled = true;
                button.innerHTML = settings.spinner;

                fetch(url, {
                        method: "POST",
                        headers: {
                            ...settings.headers,
                            Accept: "application/json",
                        },
                    })
                    .then((response) => response.json())
                    .then((data) => {
                        if (data.status == '200') {
                            toastr.success(data.msg);
                            if (window.saarni && typeof window.saarni.ajaxReload === 'function') {
                                window.saarni.ajaxReload();
                            } else if (data.redirect) {
                                window.location.href = data.redirect;
                            }
                            return;
                        }
                        toastr.error(data.msg || 'Something went wrong');
                    })
                    .catch((error) => {
                        toastr.error(error);
                    })
                    .finally(() => {
                        button.disabled = false;
                        button.innerHTML = originalHTML;
                    });
            });
        };
    </script>
@endsection
