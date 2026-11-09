@extends('admin.app')

@section('title', 'Invited Reviewers')
@section('breadcrumb', 'Invited Reviewers')
@section('page-title', 'Invited Reviewers')

@section('breadcrumb-button')
    <div class="d-flex align-items-center gap-2 flex-wrap">

        {{-- Export pending as Excel --}}
        {{-- <a href="{{ route('admin.inviteReviewer.export') }}" class="btn btn-sm btn-success btn-label">
            <i class="ri-file-excel-2-line label-icon me-1"></i> Export Pending
        </a> --}}

        {{-- Hidden form — only submitted after JS confirm --}}
        <form id="bulkRemindForm" action="{{ route('admin.inviteReviewer.bulk-invite') }}" method="POST" class="d-none">
            @csrf
        </form>

        <button type="button" id="bulkRemindBtn" class="btn btn-sm btn-warning btn-label">
            <i class="ri-mail-send-line label-icon me-1"></i> Bulk Remind
        </button>

        {{-- Import from Excel --}}
        {{-- <button class="btn btn-sm btn-secondary btn-label" data-bs-toggle="modal" data-bs-target="#importModal">
            <i class="ri-upload-2-line label-icon me-1"></i> Import Excel
        </button> --}}

        {{-- Single invite (opens existing AJAX modal) --}}
        @can('staff.create')
            <button class="btn btn-sm btn-primary btn-label actionHandler" data-url="{{ route('admin.inviteReviewer.store') }}">
                <i class="ri-add-circle-line label-icon me-1"></i> Invite New Reviewer
            </button>
        @endcan

    </div>
@endsection

@section('content')

    {{-- AJAX modal target (used by actionHandler for the invite form) --}}
    <div class="modal fade" id="AjaxModal" tabindex="-1"></div>

    {{-- Import Excel Modal --}}
    <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="ri-upload-2-line me-2 text-secondary"></i>Import Reviewers from Excel
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.inviteReviewer.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info py-2">
                            <i class="ri-information-line me-1"></i>
                            File must have a column header named <code>email</code> in the first row.
                            Already-registered and already-invited emails are skipped automatically.
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Choose File</label>
                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                            <div class="form-text text-muted">
                                Accepted: .xlsx, .xls, .csv &mdash; max 5 MB
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-upload-2-line me-1"></i> Import & Send Invitations
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
                        <th>S No.</th>
                        <th data-sortable="true" data-column="email">Email</th>
                        <th class="text-center">Invitations Sent</th>
                        <th data-sortable="true" data-column="last_invited_at">Last Invited At</th>
                        <th data-sortable="true" data-column="created_at">First Invited At</th>
                        <th>Options</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

@endsection

@section('javascripts')
    @php
        $options = [
            'selector' => 'Saarni',
            'url' => route('admin.inviteReviewer.index'),
            'moduleName' => 'Invited Reviewers',
        ];
    @endphp
    @include('includes.saarnijs', ['options' => $options])

    <script>
        document.getElementById('bulkRemindBtn')?.addEventListener('click', function() {
            if (confirm('Send reminder emails to all eligible pending reviewers? (max 3 invitations each)')) {
                document.getElementById('bulkRemindForm').submit();
            }
        });
    </script>
@endsection
