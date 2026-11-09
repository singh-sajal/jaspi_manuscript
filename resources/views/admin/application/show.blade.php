@extends('admin.app')
@section('title', 'Manuscript Submission Details')
@section('breadcrumb', 'Manuscript Submission Details')
@section('page-title')
    Manuscript Submission Details:
@endsection

@section('breadcrumb-button')
    @php
        $currentAdmin = authUser('admin');
        $isAssignedTechnicalEditor =
            isTechnicalEditor() && (int) $application->assigned_to_id === (int) $currentAdmin->id;
        $isAssignedPublisher = isPublisher() && (int) $application->assigned_to_id === (int) $currentAdmin->id;
    @endphp

    {{-- for tech editor decision --}}
    @if (authUser('admin')->can('application.techEditorDecision') &&
            ($application->status === 'submitted' || $application->status == 'resubmitted') &&
            $techApprovedCount == 0)
        <div>
            <button class="btn btn-group-sm btn-sm btn-success actionHandler"
                data-url="{{ route('admin.application.techEditorDecision', $application->uuid) }}">Decision</button>
        </div>
    @endif

    {{-- for internal reviewers and admins --}}
    @if (authUser('admin')->can('application.reviewScore'))
        @if (
            (authUser('admin')->is_admin == 1 && $rev_type === 'internal') ||
                $application->assigned_to_id == authUser('admin')->id)
            @if (
                ($application->status === 'under review' ||
                    $application->status === 'assigning reviewers' ||
                    $application->status === 're-review') &&
                    $application->accept_in == 1 &&
                    $application->status_ex != 1 &&
                    $application->status_ex != 3)
                <span class="btn btn-success btn-sm actionHandler"
                    data-url="{{ route('admin.application.reviewScore', $application->uuid) }}">
                    Score Application
                </span>
            @endif
        @endif
    @endif

    {{-- for external reviewers --}}
    @if (authUser('admin')->can('application.reviewScore'))
        @if (
            $rev_type === 'external' &&
                ($application->status === 'under review' ||
                    $application->status === 'assigning reviewers' ||
                    $application->status === 're-review') &&
                $application->accept_ex == 1 &&
                $application->status_ex != 2 &&
                $application->status_ex != 3)
            <span class="btn btn-success btn-sm actionHandler"
                data-url="{{ route('admin.application.reviewScore', $application->uuid) }}">
                Score Application
            </span>
        @endif
    @endif

    {{-- Upload formatted document --}}
    @if (
        ($currentAdmin->is_admin == 1 || $isAssignedPublisher) &&
            $application->status == 'accepted')
        <button class="btn btn-success btn-sm actionHandler"
            data-url="{{ route('admin.application.formatted_document', $application->id) }}">
            Upload formatted document
        </button>
    @endif

    {{-- for handling editor: accept/reject the assignment (only before a decision is made) --}}
    @if (
        $application->status === 'processing' &&
            $isEditorOfThisManuscript &&
            $handlingEditorDecision === null)
        <button class="btn btn-warning btn-sm actionHandler"
            data-url="{{ route('admin.application.handlingEditorDecision', $application->uuid) }}">
            Accept / Reject Assignment
        </button>
    @endif

    {{-- for handling editor: make a decision (only after accepting the assignment) --}}
    @if (
        $application->status === 'processing' &&
            $isEditorOfThisManuscript &&
            (int) $handlingEditorDecision === 1)
        <button class="btn btn-success btn-sm actionHandler"
            data-url="{{ route('admin.application.editorDecision', $application->uuid) }}">
            Make a decision
        </button>
    @endif

    {{-- for non-assigned admins on processing (they can always see the make a decision button) --}}
    @if (
        authUser('admin')->is_admin == 1 &&
            !$isEditorOfThisManuscript &&
            $application->status === 'processing')
        <button class="btn btn-success btn-sm actionHandler"
            data-url="{{ route('admin.application.editorDecision', $application->uuid) }}">
            Make a decision
        </button>
    @endif

    {{-- for handling editor (Edit Decision) --}}
    @if (
        (authUser('admin')->is_admin == 1 ||
            (authUser('admin')->can('application.editEditorDecision') &&
                optional($editor)->assigned_to_id == authUser('admin')->id)) &&
            ($application->status === 'assigning reviewers' ||
                $application->status === 'under review' ||
                $application->status === 're-review'))
        <button class="btn btn-warning btn-sm actionHandler"
            data-url="{{ route('admin.application.editEditorDecision', $application->uuid) }}">
            Edit decision
        </button>
    @endif

    {{-- for internal reviewer accept or deny --}}
    @if (
        (authUser('admin')->can('application.rev_accept_action') ||
            authUser('admin')->can('application.rev_denied_action')) &&
            $user->rev_type === 'internal' &&
            $application->accept_in === null &&
            ($application->status === 'assigning reviewers' || $application->status === 're-review') &&
            $application->assigned_to_id == authUser('admin')->id)
        <div class="d-flex gap-1 align-items-center">
            <button class="btn btn-group-sm btn-sm btn-success actionHandler"
                data-url="{{ route('admin.application.rev_decision', $application->uuid) }}">
                Accept / Deny
            </button>
        </div>
    @endif

    {{-- for external reviewer accept or deny --}}
    @if (
        (authUser('admin')->can('application.rev_accept_action') ||
            authUser('admin')->can('application.rev_denied_action')) &&
            $user->rev_type === 'external' &&
            $application->accept_ex === null &&
            ($application->status === 'assigning reviewers' || $application->status === 're-review') &&
            $application->assigned_to_ex_id == authUser('admin')->id)
        <div class="d-flex gap-1 align-items-center">
            <button class="btn btn-group-sm btn-sm btn-success actionHandler"
                data-url="{{ route('admin.application.rev_decision', $application->uuid) }}">
                Accept / Deny
            </button>
        </div>
    @endif

    {{-- for assistant editor to final accept/revise/reject the application --}}
    @if (
        authUser('admin')->can('application.assistantEditorFinalDecision') &&
            ((($application->status === 'editor-accepts' || $application->status === 'editor-rejects') &&
                data_get($application->data, 'status') == 'redirected to assistant editor') ||
                $application->status === 'editor-revise' ||
                $application->status === 'chief-response'))
        <div>
            <button type="button" class="btn btn-group-sm btn-sm btn-success actionHandler"
                data-url="{{ route('admin.application.assistant_editor_decision', ['uuid' => $application->uuid, 'action' => 'accept']) }}"
                title="Accept">
                Accept
            </button>
            |
            <button type="button" class="btn btn-group-sm btn-sm btn-warning actionHandler"
                data-url="{{ route('admin.application.assistant_editor_decision', ['uuid' => $application->uuid, 'action' => 'revise']) }}"
                title="Request Revision">
                Request Revision
            </button>
            |
            <button type="button" class="btn btn-group-sm btn-sm btn-danger actionHandler"
                data-url="{{ route('admin.application.assistant_editor_decision', ['uuid' => $application->uuid, 'action' => 'reject']) }}"
                title="Reject">
                Reject
            </button>
        </div>
    @endif

    {{-- for assistant editor can see chief's decision --}}
    {{-- @if (authUser('admin')->can('application.assistantEditorFinalDecision') &&
            ($application->status === 'editor-accepts' || $application->status === 'editor-rejects') &&
            data_get($application->data, 'status') == 'redirected to assistant editor')
        <div>
            <button type="button" class="btn btn-group-sm btn-sm btn-success actionHandler"
                data-url="{{ route('admin.application.seeChiefDecision', ['uuid' => $application->uuid, 'action' => 'accept']) }}"
                title="See Chief's Decision">
                See Chief's Decision
            </button>
        </div>
    @endif --}}

    @if ($isPreAccepted && $application->published_doi == null && authUser('admin')->can('application.add_doi'))
        <div>
            <button type="button" class="btn btn-group-sm btn-sm btn-success actionHandler"
                data-url="{{ route('admin.application.add_doi', ['uuid' => $application->uuid]) }}" title="Add DOI Number">
                Add DOI Number
            </button>
        </div>
    @endif

    {{-- The send → chief response cycle can repeat: the button is available
         whenever a request is not already awaiting the chief's response. --}}
    @if (authUser('admin')->can('application.assistantToChiefForApproval') &&
            in_array($application->status, ['editor-accepts', 'editor-rejects']) &&
            data_get($application->data, 'status') != 'sended to chief approval')
        <div>
            <button type="submit" class="btn btn-group-sm btn-sm btn-info actionHandler"
                data-url="{{ route('admin.application.assistantToChiefForApproval', $application->uuid) }}"
                title="Sending the Manuscript to the Editor in Chief for final approval" data-handler="confirmSendToChief">
                Send for final approval
            </button>
        </div>
    @endif

    @if (authUser('admin')->can('application.chiefToAssistanResponse') &&
            in_array($application->status, ['chief-review', 'chief-approval', 'editor-accepts', 'editor-rejects']) &&
            data_get($application->data, 'status') == 'sended to chief approval')
        <div>
            <button type="button" class="btn btn-group-sm btn-sm btn-info actionHandler"
                data-url="{{ route('admin.application.chiefToAssistanResponse', ['uuid' => $application->uuid]) }}"
                title="Permission to Accept/Reject/Revise the Manuscript">
                {{-- Permit --}}
                Decision
            </button>
        </div>
    @endif

    {{-- for handling editors to accept/reject/revise (all forwarded to assistant editor) --}}
    @if (
        (authUser('admin')->is_admin == 1 && $application->status === 'reviewed') ||
            ((authUser('admin')->can('application.acceptance') ||
                authUser('admin')->can('application.revise') ||
                authUser('admin')->can('application.rejection')) &&
                $application->status === 'reviewed' &&
                optional($editor)->assigned_to_id == authUser('admin')->id))
        <div title="{{ authUser('admin')->is_admin == 1 ? 'for editor accept/revise/reject' : '' }}">
            <button type="button" class="btn btn-group-sm btn-sm btn-success actionHandler"
                data-url="{{ route('admin.application.editor_final_decision', ['uuid' => $application->uuid, 'action' => 'accept']) }}">
                Accept
            </button>
            |
            <button type="button" class="btn btn-group-sm btn-sm btn-warning actionHandler"
                data-url="{{ route('admin.application.editor_final_decision', ['uuid' => $application->uuid, 'action' => 'revise']) }}">
                Request Revision
            </button>
            |
            <button type="button" class="btn btn-group-sm btn-sm btn-danger actionHandler"
                data-url="{{ route('admin.application.editor_final_decision', ['uuid' => $application->uuid, 'action' => 'reject']) }}">
                Reject
            </button>
        </div>
    @endif

    {{-- for handling editors (send for re-review) --}}
    @if (
        (authUser('admin')->is_admin == 1 && $application->status === 'reviewed') ||
            ((authUser('admin')->can('application.acceptance') ||
                authUser('admin')->can('application.revise') ||
                authUser('admin')->can('application.rejection')) &&
                $application->status === 'reviewed' &&
                optional($editor)->assigned_to_id == authUser('admin')->id))
        <div title="send for re-review">
            <button type="button" class="btn btn-group-sm btn-sm btn-info actionHandler"
                data-url="{{ route('admin.application.re_review', ['uuid' => $application->uuid]) }}">
                Send for Re-Review
            </button>
        </div>
    @endif

    {{-- for handling editors (force mark as reviewed when one reviewer is stuck/declined) --}}
    @if (
        (authUser('admin')->is_admin == 1 &&
            ($application->status === 'under review' ||
                $application->status === 're-review' ||
                $application->status === 'assigning reviewers') &&
            ($application->status_ex ?? 0) >= 1) ||
            (((authUser('admin')->can('application.acceptance') ||
                authUser('admin')->can('application.revise') ||
                authUser('admin')->can('application.rejection')) &&
                ($application->status === 'under review' ||
                    $application->status === 're-review' ||
                    $application->status === 'assigning reviewers') &&
                optional($editor)->assigned_to_id == authUser('admin')->id) &&
                ($application->status_ex ?? 0) >= 1))
        {{-- <div title="force review completion">
            <button type="button" class="btn btn-group-sm btn-sm btn-outline-success actionHandler"
                data-url="{{ route('admin.application.reviewed', ['uuid' => $application->uuid]) }}">
                Mark as Reviewed
            </button>
        </div> --}}
    @endif

    @if (
        (authUser('admin')->is_admin == 1 &&
            ($application->status === 'under review' ||
                $application->status === 're-review' ||
                $application->status === 'assigning reviewers')) ||
            (($application->status === 'under review' ||
                $application->status === 're-review' ||
                $application->status === 'assigning reviewers') &&
                optional($editor)->assigned_to_id == authUser('admin')->id))
        <div title="remind reviewers">
            <button type="button" class="btn btn-group-sm btn-sm btn-info actionHandler"
                data-url="{{ route('admin.application.remind_reviewers', ['uuid' => $application->uuid]) }}">
                Remind Reviewers
            </button>
        </div>
    @endif

    {{-- Technical editor: send the formatted proof to the author, then either
         accept the corrected proof or send it back for more corrections. --}}
    @if (
        $isAssignedTechnicalEditor &&
            $application->status === 'author corrections submitted')
        <div>
            <button type="button" class="btn btn-group-sm btn-sm btn-success actionHandler"
                title="Accept this manuscript for publication"
                data-url="{{ route('admin.application.acceptForPublication', ['uuid' => $application->uuid]) }}">
                Accept for Publication
            </button>
        </div>
    @endif

    @if (
        ($isAssignedTechnicalEditor || $currentAdmin->is_admin == 1) &&
            in_array($application->status, ['typesetting review', 'author corrections submitted'], true))
        <div title="Send for proofreading">
            <button type="button" class="btn btn-group-sm btn-sm btn-info actionHandler"
                data-url="{{ route('admin.application.send_for_proofreading', ['uuid' => $application->uuid]) }}">
                {{ $application->status === 'typesetting review' ? 'Send for Proofreading' : 'Send again for proofreading' }}
            </button>
        </div>
    @endif

    {{-- Publisher: final DOI and publication step. --}}
    @if ($isAssignedPublisher && $application->status === 'accepted for publication')
        <div title="Add the DOI and publish the manuscript">
            <button type="button" class="btn btn-group-sm btn-sm btn-success actionHandler"
                data-url="{{ route('admin.application.publish', ['uuid' => $application->uuid]) }}">
                Add DOI &amp; Publish
            </button>
        </div>
    @endif

    @if (authUser('admin')->can('application.assignPublisher') && $application->status === 'pre-accepted')
        <span class="btn btn-success btn-sm actionHandler"
            data-url="{{ route('admin.application.assignPublisher', $application->uuid) }}">
            Assign Publisher
        </span>
    @endif

    <div class="d-flex align-items-center justify-content-end">
        @if (authUser('admin')->can('application.edit') &&
                ($application->status == 'submitted' || $application->status == 'processing'))
            <a class="btn btn-sm btn-dark btn-label" href="{{ route('admin.application.edit', $application->uuid) }}">
                <i class="ri-edit-box-line" style=""></i>
            </a>
        @endif

        <a class="btn btn-sm btn-primary" href="{{ route('admin.application.index') }}">
            <i class="ri-arrow-go-back-line"></i>
        </a>
    </div>
@endsection

@section('css')
    <style>
        .file-box {
            border: 1px solid #ccc;
            padding: 15px;
            text-align: center;
            border-radius: 8px;
        }

        .file-icon {
            font-size: 40px;
            margin-bottom: 10px;
        }
    </style>
@endsection


@if ($canViewContent)
    @section('content')
        <div class="offcanvas offcanvas-end" tabindex="-1" style="z-index: 10000;width:80%" id="FileOffcanvas"
            data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="FileOffcanvasLabel"></div>

        <div class="card">
            <div class="card-body">

                <div class="row">
                    <div class="col-7">
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h4 class="card-title">#{{ $application->application_id }}</h4>
                                <span class="badge badge-sm bg-dark">{{ ['chief-review' => 'Chief Review', 'chief-response' => 'Chief Response', 'chief-approval' => 'Chief Approval'][$application->status] ?? ucfirst($application->status) }}</span>
                            </div>
                            <div class="card-body">
                                <div class="text-muted w-100 table-responsive">
                                    <div class="table-card">
                                        <table class="w-100 mb-0 table">
                                            <tbody>
                                                <tr>
                                                    <td class="fw-semibold">Title</td>
                                                    <td>{{ $application->title ?? 'N/A' }}</td>
                                                </tr>

                                                {{-- HIDE THIS ENTIRE SECTION FROM STRICT REVIEWERS --}}
                                                @if (!$isStrictReviewer)
                                                    <tr>
                                                        <td class="fw-semibold">Submission Type</td>
                                                        <td>{{ !empty($application->submission_type)
                                                            ? ($application->submission_type == 'new_submission'
                                                                ? 'New Submission'
                                                                : 'Revised Submission')
                                                            : 'N/A' }}
                                                        </td>
                                                    </tr>

                                                    @if ($canViewSensitiveMeta)
                                                        <tr>
                                                            <td class="fw-semibold">Author</td>
                                                            <td>{{ $application->data['name'] ?? ($application->author->name ?? 'N/A') }}
                                                                {{ $application->data['lname'] ?? ($application->author->lname ?? 'N/A') }}
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="fw-semibold">Author Affiliation</td>
                                                            <td>{{ $application->author_affiliation ?? 'N/A' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="fw-semibold">ORCID ID</td>
                                                            <td>{{ $application->author_orcid_id ?? 'N/A' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="fw-semibold">SASPI ID</td>
                                                            <td>{{ $application->author_saspi_id ?? 'N/A' }}</td>
                                                        </tr>
                                                    @endif

                                                    <tr>
                                                        <td class="fw-semibold">Article Type</td>
                                                        <td>{{ !empty($application->article_type) ? implode(',', $application->article_type ?? []) : 'N/A' }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-semibold">Article Check</td>
                                                        <td>
                                                            @php
                                                                $article_checks = ['Cross reference Check'];
                                                                $savedArticleChecks = $application->article_check ?? [];

                                                                foreach ($article_checks as $check) {
                                                                    echo $check .
                                                                        ': ' .
                                                                        (in_array($check, $savedArticleChecks)
                                                                            ? 'yes'
                                                                            : 'no') .
                                                                        ', &nbsp;';
                                                                }
                                                            @endphp
                                                        </td>
                                                    </tr>
                                                @endif

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($application->status !== 'incomplete')
                        <div class="col-5">
                            {{-- Timeline Column --}}
                            <div class="card">
                                <div class="mt-3">
                                    <div id="DZ_W_TimeLine02" class="widget-timeline dz-scroll style-1 height370 p-3">
                                        <ul class="timeline">
                                            @php
                                                $colors = [
                                                    'primary',
                                                    'info',
                                                    'success',
                                                    'danger',
                                                    'warning',
                                                    'purple',
                                                    'secondary',
                                                ];

                                                $editorStatuses = [
                                                    'editor-accepts',
                                                    'editor-revise',
                                                    'editor-rejects',
                                                    'assigning reviewers',
                                                    'under review',
                                                    're-review',
                                                    'reviewed',
                                                ];
                                                $reviewerStatuses = [
                                                    'internal-reviewer-accepts',
                                                    'internal-reviewer-revise',
                                                    'internal-reviewer-rejects',
                                                    'internal-reviewer-reviewed',
                                                    'external-reviewer-accepts',
                                                    'external-reviewer-revise',
                                                    'external-reviewer-rejects',
                                                    'external-reviewer-reviewed',
                                                ];
                                                $techEditorStatuses = ['revise', 'out of scope', 'proofreading'];
                                                $publicationStatuses = ['accepted for publication'];
                                                $assistantEditorStatuses = ['rejected', 'pre-accepted'];
                                                // The send → chief response cycle can repeat, so each
                                                // round's chief timeline entries are shown with their
                                                // handler.
                                                $chiefStatuses = ['chief-review', 'chief-response'];

                                                $noAdminStatuses = array_merge(
                                                    $editorStatuses,
                                                    $reviewerStatuses,
                                                    $techEditorStatuses,
                                                    $chiefStatuses,
                                                );

                                                $reversedHandlerStatuses = [
                                                    'external-reviewer-reviewed',
                                                    'internal-reviewer-reviewed',
                                                ];
                                            @endphp

                                            @forelse ($application->timelines as $timeline)
                                            {{-- @if($timeline->status === 'processing')
                                                @dump($timeline)
                                            @endif --}}
                                                <li class="fade-in">
                                                    <div class="timeline-badge {{ $colors[$loop->index % 7] }}"></div>

                                                    <a class="timeline-panel text-muted">
                                                        <span>{{ $timeline->created_at->format('d-m-Y h:i A') }}
                                                            ({{ $timeline->created_at->diffForHumans() }})
                                                        </span>
                                                        <h6 class="mb-0">Status: <strong
                                                                class="text-primary">{{ ucfirst($timeline->status) }}</strong>
                                                        </h6>

                                                        <p class="mb-0">Remark: {{ $timeline->remark }}
                                                            @if (in_array($timeline->status, $editorStatuses))
                                                                <span class="text-primary actionHandler"
                                                                    data-url="{{ route('admin.application.readMistake', ['uuid' => $application->uuid, 'timeline_id' => $timeline->id]) }}"
                                                                    style="cursor: pointer"><strong>read
                                                                        more</strong></span>
                                                            @elseif (in_array($timeline->status, $reviewerStatuses))
                                                                <span class="text-primary actionHandler"
                                                                    data-url="{{ route('admin.application.reviewerReadMore', ['uuid' => $application->uuid, 'timeline_id' => $timeline->id]) }}"
                                                                    style="cursor: pointer"><strong>read
                                                                        more</strong></span>
                                                            @elseif (in_array($timeline->status, $techEditorStatuses) && !empty($timeline->data))
                                                                <span class="text-primary actionHandler"
                                                                    data-url="{{ route('admin.application.techEditorReadMore', ['uuid' => $application->uuid, 'timeline_id' => $timeline->id]) }}"
                                                                    style="cursor: pointer"><strong>read
                                                                        more</strong></span>
                                                            @elseif (in_array($timeline->status, $publicationStatuses) && !empty($timeline->data))
                                                                <span class="text-primary actionHandler"
                                                                    data-url="{{ route('admin.application.techEditorReadMore', ['uuid' => $application->uuid, 'timeline_id' => $timeline->id]) }}"
                                                                    style="cursor: pointer"><strong>read
                                                                        more</strong></span>
                                                            @elseif (in_array($timeline->status, $assistantEditorStatuses) && !empty($timeline->data))
                                                                <span class="text-primary actionHandler"
                                                                    data-url="{{ route('admin.application.techEditorReadMore', ['uuid' => $application->uuid, 'timeline_id' => $timeline->id]) }}"
                                                                    style="cursor: pointer"><strong>read
                                                                        more</strong></span>
                                                            @elseif ($timeline->status === 'chief-response' && !empty($timeline->data))
                                                                <span class="text-primary actionHandler"
                                                                    data-url="{{ route('admin.application.chiefReadMore', ['uuid' => $application->uuid, 'timeline_id' => $timeline->id]) }}"
                                                                    style="cursor: pointer"><strong>read
                                                                        more</strong></span>
                                                            @elseif ($timeline->status === 'processing' && !empty($timeline->data))
                                                                <span class="text-primary actionHandler"
                                                                    data-url="{{ route('admin.application.authorUpdate', ['uuid' => $application->uuid, 'timeline_id' => $timeline->id]) }}"
                                                                    style="cursor: pointer"><strong>read
                                                                        more</strong></span>
                                                            @endif
                                                        </p>

                                                        @php
                                                            $isProcessingWithData =
                                                                $timeline->status === 'processing' &&
                                                                !empty($timeline->data);
                                                        @endphp

                                                        @if (authUser('admin')->is_admin == 1)
                                                            @if (in_array($timeline->status, $publicationStatuses))
                                                                {{-- This hand-off records the technical editor
                                                                     as the handler and the publisher as the new
                                                                     assignee. --}}
                                                                @if ($timeline->assignedBy)
                                                                    <p class="mb-0">Handled by:
                                                                        <strong>{{ $timeline->assignedBy->name }}</strong>
                                                                    </p>
                                                                @endif
                                                                @if ($timeline->assignee)
                                                                    <p class="mb-0">Assigned to:
                                                                        <strong>{{ $timeline->assignee->name }}</strong>
                                                                    </p>
                                                                @endif
                                                            @elseif (in_array($timeline->status, $reversedHandlerStatuses))
                                                                {{-- Reversed pairing for these two statuses --}}
                                                                @if ($timeline->assignedBy)
                                                                    <p class="mb-0">Handled by:
                                                                        <strong>{{ $timeline->assignedBy->name }}</strong>
                                                                    </p>
                                                                @endif
                                                                {{-- @if ($timeline->assignee)
                                                                    <p class="mb-0">Assigned by:
                                                                        <strong>{{ $timeline->assignee->name }}</strong>
                                                                    </p>
                                                                @endif --}}
                                                            @else
                                                                @if ($timeline->status === 'processing' ||
                                                                        in_array($timeline->status, $noAdminStatuses))
                                                                    @if ($timeline->assignee)
                                                                        <p class="mb-0">Handled by:
                                                                            <strong>{{ $timeline->assignee->name }}</strong>
                                                                        </p>
                                                                    @endif
                                                                @endif
                                                                @if ($timeline->assigned_by && $timeline->assignedBy)
                                                                    <p class="mb-0">Assigned by:
                                                                        <strong>{{ $timeline->assignedBy->name }}</strong>
                                                                    </p>
                                                                @endif
                                                            @endif
                                                        @endif
                                                    </a>
                                                </li>
                                            @empty
                                                <p>Timeline not available</p>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($canViewSensitiveMeta)
                        <div class="{{ $application->status == 'incomplete' ? 'col-5' : 'col-12' }}">
                            <div class="card h-100">
                                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                                    <h5 class="card-title mb-0">Co-Authors</h5>
                                </div>
                                <div class="card-body p-0" id="co-author-container">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-striped align-middle mb-0">
                                            <thead class="table-light text-muted">
                                                <tr>
                                                    <th scope="col" class="ps-3">Name</th>
                                                    <th scope="col">Email</th>
                                                    <th scope="col">Phone</th>
                                                    <th scope="col">Affiliation</th>
                                                    <th scope="col">Orcid ID</th>
                                                    <th scope="col">Saspi ID</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($application->co_author_data ?? [] as $coAuthor)
                                                    <tr id="co-author-{{ $coAuthor['coauthor_id'] }}">
                                                        <td class="ps-3 fw-medium">{{ $coAuthor['coauthor_name'] }}
                                                        </td>
                                                        <td><span
                                                                class="text-nowrap font-monospace text-primary">{{ $coAuthor['coauthor_email'] }}</span>
                                                        </td>
                                                        <td>{{ $coAuthor['coauthor_phone'] }}</td>
                                                        <td>{{ $coAuthor['coauthor_affiliation'] ?? 'N/A' }}</td>
                                                        <td>{{ $coAuthor['coauthor_orcid_id'] ?? 'N/A' }}</td>
                                                        <td>{{ $coAuthor['coauthor_saspi_id'] ?? 'N/A' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="7" class="text-center py-4 text-muted">
                                                            <div class="d-flex flex-column align-items-center">
                                                                <i class="ri-user-unfollow-line fs-24 mb-2 opacity-50"></i>
                                                                <span>No Co-Authors Added.</span>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                        <div class="col-12 mt-3">
                            <div class="card h-100">
                                <div class="card-header d-flex align-items-center justify-content-between py-3">
                                    <h5 class="card-title mb-0">Reviewer Suggestions</h5>
                                    @if ($canViewSensitiveMeta && ($application->status == 'incomplete' || $application->status == 'revise'))
                                        <button
                                            class="btn btn-sm btn-primary actionHandler d-flex align-items-center gap-1"
                                            data-url="{{ route('admin.application.co_author', $application->uuid) }}">
                                            <i class="ri-add-circle-line fs-16"></i> <span>Add Reviewer</span>
                                        </button>
                                    @endif
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-striped align-middle mb-0">
                                            <thead class="table-light text-muted">
                                                <tr>
                                                    <th scope="col">Name</th>
                                                    <th scope="col">Email</th>
                                                    <th scope="col">Institution</th>
                                                    <th scope="col">Specialization</th>
                                                    <th scope="col" style="width: 25%;">Reason</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($application->data['reviewer_suggestions'] ?? [] as $reviewer)
                                                    <tr>
                                                        <td class="fw-medium text-nowrap">
                                                            {{ $reviewer['first_name'] . ' ' . $reviewer['last_name'] ?? '-' }}
                                                        </td>
                                                        <td><span
                                                                class="text-nowrap font-monospace text-primary">{{ $reviewer['email'] ?? '-' }}</span>
                                                        </td>
                                                        <td>{{ $reviewer['institution'] ?? '-' }}</td>
                                                        <td><span
                                                                class="text-dark">{{ $reviewer['specialization'] ?? '-' }}</span>
                                                        </td>
                                                        <td>{{ $reviewer['recommended'] ?? '-' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="8" class="text-center py-4 text-muted">
                                                            <div class="d-flex flex-column align-items-center">
                                                                <i class="ri-user-add-line fs-24 mb-2 opacity-50"></i>
                                                                <span>No reviewer suggestions added yet.</span>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                    {{-- HIDE THE ABSTRACT FROM REVIEWERS --}}
                    @if (!$isStrictReviewer)
                        <div class="col-12">
                            <div class="card-body">
                                <p class="fw-semibold">Abstract</p>
                                <div>{!! $application->description ?? '' !!}</div>
                            </div>
                        </div>
                    @endif

                    <div class="col-12">
                        <div class="card mt-2">
                            <div class="card-header">
                                <h4 class="card-title">Attachments</h4>
                            </div>

                            @php
                                $attachments = $application->attachments ?? collect();
                                $manuscripts = $attachments
                                    ->where('attachment_type', 'Upload Manuscript')
                                    ->sortByDesc('created_at');
                                $otherAttachments = $attachments->whereNotIn('attachment_type', [
                                    'Upload Manuscript',
                                    'Rebuttal Letter',
                                    'Highlighted Manuscript',
                                ]);
                                $otherCategories = $otherAttachments->pluck('attachment_type')->unique();
                                $latestManuscript = $manuscripts->first();
                                $rebuttalFiles = $attachments
                                    ->where('attachment_type', 'Rebuttal Letter')
                                    ->sortByDesc('created_at');
                                $highlightedFiles = $attachments
                                    ->where('attachment_type', 'Highlighted Manuscript')
                                    ->sortByDesc('created_at');
                            @endphp

                            {{-- HIDE OTHER ATTACHMENTS FROM REVIEWERS --}}
                            @if (!$isStrictReviewer)
                                @if (isEditor() && $user->is_admin != 1)
                                    @php
                                        $firstPageFile = $otherAttachments
                                            ->where('attachment_type', 'First Page')
                                            ->sortByDesc('created_at')
                                            ->first();
                                    @endphp
                                    <div class="card-body">
                                        <div class="d-flex align-items-start gap-2 flex-wrap">
                                            <div class="card flex-grow-1" style="min-width:260px">
                                                <div class="card-header d-flex align-items-center">
                                                    <div class="d-flex align-items-center justify-content-center bg-dark rounded-circle"
                                                        style="height:1.3rem;width:1.3rem;font-size:10px;">
                                                        <span class="text-white">1</span>
                                                    </div>
                                                    <h4 class="card-title flex-grow-1 ms-1" style="font-size:12px;">
                                                        First Page</h4>
                                                </div>
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">
                                                        <span
                                                            class="avatar avatar-lg {{ $firstPageFile ? 'bg-success' : 'bg-danger' }} rounded-circle d-flex align-items-center justify-content-center">
                                                            <i
                                                                class="{{ $firstPageFile ? 'ri-check-line' : 'ri-file-2-line' }} fs-20 text-white"></i>
                                                        </span>
                                                        <div class="d-flex flex-column border-start ms-2 px-2">
                                                            @if ($firstPageFile)
                                                                <p class="fw-semibold fs-12 mb-0 ms-1">
                                                                    <i>Uploaded On:
                                                                        {{ $firstPageFile->created_at->format('d-m-Y') }}</i>
                                                                </p>
                                                                @if (!empty($firstPageFile->legend))
                                                                    <div class="ms-1 mt-1">
                                                                        <strong>Legend:</strong>
                                                                        <span
                                                                            class="fs-12 text-muted">{{ Str::limit($firstPageFile->legend, 25) }}</span>
                                                                        @if (strlen($firstPageFile->legend) >= 30)
                                                                            <a href="#"
                                                                                class="fs-11 text-primary text-decoration-underline"
                                                                                data-bs-toggle="modal"
                                                                                data-bs-target="#legendModal{{ $firstPageFile->id }}">Read
                                                                                More</a>
                                                                        @endif
                                                                    </div>
                                                                @endif
                                                                <div class="d-flex gap-1 mt-1">
                                                                    <span
                                                                        class="fs-12 text-dark text-decoration-underline actionHandler px-1"
                                                                        data-url="{{ route('file.viewframe') }}"
                                                                        data-frame="offcanvas"
                                                                        data-file-url="{{ getFileUrl($firstPageFile->file_url) }}"
                                                                        data-handler="openFilePreview">View</span>
                                                                </div>
                                                            @else
                                                                <p class="mb-0 text-muted ms-1">First page not
                                                                    uploaded.</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if ($user->is_admin == 1 || isPublisher())
                                    <div class="card-body">
                                        <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">
                                            @php $counter = 1; @endphp
                                            @foreach ($otherCategories as $category)
                                                @php
                                                    $file = $otherAttachments
                                                        ->where('attachment_type', $category)
                                                        ->sortByDesc('created_at')
                                                        ->first();
                                                    $isAdmin = $user->is_admin ? true : false;
                                                    $isPublisher = isPublisher();
                                                    $isFormattedProof = $category === 'Formatted Proof';
                                                    $canSee = $isAdmin || ($isPublisher && $isFormattedProof);
                                                @endphp
                                                @if ($canSee)
                                                    <div class="card flex-grow-1" style="min-width:260px">
                                                        <div class="card-header d-flex align-items-center">
                                                            <div class="d-flex align-items-center justify-content-center bg-dark rounded-circle"
                                                                style="height:1.3rem;width:1.3rem;font-size:10px;">
                                                                <span class="text-white">{{ $counter++ }}</span>
                                                            </div>
                                                            <h4 class="card-title flex-grow-1 ms-1"
                                                                style="font-size:12px;">{{ $category }}<span
                                                                    class="required"></span></h4>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="d-flex align-items-center">
                                                                <span
                                                                    class="avatar avatar-lg {{ $file ? 'bg-success' : 'bg-danger' }} rounded-circle d-flex align-items-center justify-content-center">
                                                                    <i
                                                                        class="{{ $file ? 'ri-check-line' : 'ri-file-2-line' }} fs-20 text-white"></i>
                                                                </span>
                                                                <div class="d-flex flex-column border-start ms-2 px-2">
                                                                    @if ($file)
                                                                        <p class="fw-semibold fs-12 mb-0 ms-1">
                                                                            <i>Uploaded On:
                                                                                {{ $file->created_at->format('d-m-Y') }}</i>
                                                                        </p>
                                                                        @if (!empty($file->legend))
                                                                            <div class="ms-1 mt-1">
                                                                                <strong>Legend:</strong>
                                                                                <span
                                                                                    class="fs-12 text-muted">{{ Str::limit($file->legend, 25) }}</span>
                                                                                @if (strlen($file->legend) >= 30)
                                                                                    <a href="#"
                                                                                        class="fs-11 text-primary text-decoration-underline"
                                                                                        data-bs-toggle="modal"
                                                                                        data-bs-target="#legendModal{{ $file->id }}">Read
                                                                                        More</a>
                                                                                @endif
                                                                            </div>
                                                                        @endif
                                                                        <div class="d-flex gap-1 mt-1">
                                                                            <span
                                                                                class="fs-12 text-dark text-decoration-underline actionHandler px-1"
                                                                                data-url="{{ route('file.viewframe') }}"
                                                                                data-frame="offcanvas"
                                                                                data-file-url="{{ getFileUrl($file->file_url) }}"
                                                                                data-handler="openFilePreview">View</span>
                                                                            @php
                                                                                $isEditableStatus =
                                                                                    $application->status ==
                                                                                        'incomplete' ||
                                                                                    $application->status == 'revise' ||
                                                                                    $application->status ==
                                                                                        'editor-revise';
                                                                            @endphp
                                                                            @if ($isEditableStatus)
                                                                                <a href="#"
                                                                                    class="actionHandler fs-12 text-info text-decoration-underline border-start px-1"
                                                                                    data-url="{{ route('admin.application.file_upload', ['id' => $file->id, 'type' => $category, 'app_id' => $application->id]) }}">Replace</a>
                                                                            @endif
                                                                            @if ($application->status == 'incomplete')
                                                                                <a href="#"
                                                                                    class="fs-12 text-danger actionHandler border-start px-1"
                                                                                    data-action="delete"
                                                                                    data-url="{{ route('admin.application.file_destroy', ['app_id' => $application->uuid, 'id' => $file->id]) }}"><i
                                                                                        class="ri-delete-bin-line"></i></a>
                                                                            @endif
                                                                        </div>
                                                                    @else
                                                                        <p class="fw-semibold fs-12 mb-0">No File</p>
                                                                        @if ($application->status == 'incomplete' || $application->status == 'revise')
                                                                            <a href="#"
                                                                                class="actionHandler fs-12 text-info text-decoration-underline"
                                                                                data-url="{{ route('admin.application.file_upload', ['id' => 'na', 'type' => $category, 'app_id' => $application->id]) }}">Upload</a>
                                                                        @endif
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endif

                            {{-- EVERYONE SEES THIS MANUSCRIPT ROW --}}
                            <div class="card-body border-top">
                                <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">
                                    @php $counter = 1; @endphp
                                    @foreach ($manuscripts as $file)
                                        <div class="card flex-grow-1" style="min-width:260px">
                                            <div class="card-header d-flex align-items-center">
                                                <div class="d-flex align-items-center justify-content-center bg-dark rounded-circle"
                                                    style="height:1.3rem;width:1.3rem;font-size:10px;">
                                                    <span class="text-white">{{ $counter++ }}</span>
                                                </div>
                                                <h4 class="card-title flex-grow-1 ms-1" style="font-size:12px;">
                                                    Manuscript<span class="required"></span>
                                                    @if ($file->id === $latestManuscript->id)
                                                        <span class="badge bg-success ms-1"
                                                            style="font-size:9px;">Latest</span>
                                                    @else
                                                        <span class="badge bg-secondary ms-1"
                                                            style="font-size:9px;">History</span>
                                                    @endif
                                                </h4>
                                            </div>
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <span
                                                        class="avatar avatar-lg bg-success rounded-circle d-flex align-items-center justify-content-center">
                                                        <i class="ri-check-line fs-20 text-white"></i>
                                                    </span>
                                                    <div class="d-flex flex-column border-start ms-2 px-2">
                                                        <p class="fw-semibold fs-12 mb-0 ms-1">
                                                            <i>Uploaded On:
                                                                {{ $file->created_at->format('d-m-Y') }}</i>
                                                        </p>
                                                        <div class="d-flex gap-1">
                                                            <span
                                                                class="fs-12 text-dark text-decoration-underline actionHandler px-1 cursor-pointer"
                                                                data-url="{{ route('file.viewframe') }}"
                                                                data-frame="offcanvas"
                                                                data-file-url="{{ getFileUrl($file->file_url) }}"
                                                                data-handler="openFilePreview">View</span>
                                                            @if ($file->id === $latestManuscript->id)
                                                                <a href="#"
                                                                    class="actionHandler fs-12 text-info text-decoration-underline border-start px-1"
                                                                    data-url="{{ route('admin.application.file_upload', ['id' => $file->id, 'type' => 'Upload Manuscript', 'app_id' => $application->id]) }}">Replace</a>
                                                            @endif
                                                            <a href="#"
                                                                class="fs-12 text-danger actionHandler border-start px-1"
                                                                data-action="delete"
                                                                data-url="{{ route('admin.application.file_destroy', ['app_id' => $application->uuid, 'id' => $file->id]) }}"><i
                                                                    class="ri-delete-bin-line"></i></a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- EVERYONE SEES REBUTTAL LETTER & HIGHLIGHTED MANUSCRIPT --}}
                            @foreach (['Rebuttal Letter' => $rebuttalFiles, 'Highlighted Manuscript' => $highlightedFiles] as $revisionDocType => $revisionFiles)
                                <div class="card-body border-top">
                                    <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">
                                        @php $counter = 1; @endphp
                                        @forelse ($revisionFiles as $file)
                                            <div class="card flex-grow-1" style="min-width:260px">
                                                <div class="card-header d-flex align-items-center">
                                                    <div class="d-flex align-items-center justify-content-center bg-dark rounded-circle"
                                                        style="height:1.3rem;width:1.3rem;font-size:10px;">
                                                        <span class="text-white">{{ $counter++ }}</span>
                                                    </div>
                                                    <h4 class="card-title flex-grow-1 ms-1 mb-0" style="font-size:12px;">
                                                        {{ $revisionDocType }}<span class="required"></span>
                                                        @if (optional($revisionFiles->first())->id === $file->id)
                                                            <span class="badge bg-success ms-1"
                                                                style="font-size:9px;">Latest</span>
                                                        @else
                                                            <span class="badge bg-secondary ms-1"
                                                                style="font-size:9px;">History</span>
                                                        @endif
                                                    </h4>
                                                </div>
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">
                                                        <span
                                                            class="avatar avatar-lg bg-success rounded-circle d-flex align-items-center justify-content-center">
                                                            <i class="ri-check-line fs-20 text-white"></i>
                                                        </span>
                                                        <div class="d-flex flex-column border-start ms-2 px-2">
                                                            <p class="fw-semibold fs-12 mb-0 ms-1">
                                                                <i>Uploaded On:
                                                                    {{ $file->created_at->format('d-m-Y') }}</i>
                                                            </p>
                                                            <div class="d-flex gap-1 mt-1">
                                                                <span
                                                                    class="fs-12 text-dark text-decoration-underline actionHandler px-1 cursor-pointer"
                                                                    data-url="{{ route('file.viewframe') }}"
                                                                    data-frame="offcanvas"
                                                                    data-file-url="{{ getFileUrl($file->file_url) }}"
                                                                    data-handler="openFilePreview">View</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="card flex-grow-1" style="min-width:260px">
                                                <div class="card-header d-flex align-items-center">
                                                    <div class="d-flex align-items-center justify-content-center bg-dark rounded-circle"
                                                        style="height:1.3rem;width:1.3rem;font-size:10px;">
                                                        <span class="text-white">1</span>
                                                    </div>
                                                    <h4 class="card-title flex-grow-1 ms-1 mb-0" style="font-size:12px;">
                                                        {{ $revisionDocType }}<span class="required"></span>
                                                    </h4>
                                                </div>
                                                <div class="card-body">
                                                    <p class="fw-semibold fs-12 mb-0 text-muted">No file uploaded.</p>
                                                </div>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach

                            {{-- HIDE CORRECTED PROOFS FROM REVIEWERS --}}
                            @if (!$isStrictReviewer)
                                <div class="card-body border-top">
                                    <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">
                                        @php
                                            $counter = 1;
                                            $latestManuscript = $correctedFiles->first();
                                        @endphp
                                        @foreach ($correctedFiles as $file)
                                            <div class="card flex-grow-1" style="min-width:260px">
                                                <div class="card-header d-flex align-items-center">
                                                    <div class="d-flex align-items-center justify-content-center bg-dark rounded-circle"
                                                        style="height:1.3rem;width:1.3rem;font-size:10px;">
                                                        <span class="text-white">{{ $counter++ }}</span>
                                                    </div>
                                                    <h4 class="card-title flex-grow-1 ms-1 mb-0" style="font-size:12px;">
                                                        Corrected File<span class="required"></span>
                                                        @if (optional($latestManuscript)->id === $file->id)
                                                            <span class="badge bg-success ms-1"
                                                                style="font-size:9px;">Latest</span>
                                                        @else
                                                            <span class="badge bg-secondary ms-1"
                                                                style="font-size:9px;">History</span>
                                                        @endif
                                                    </h4>
                                                </div>
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">
                                                        <span
                                                            class="avatar avatar-lg bg-success rounded-circle d-flex align-items-center justify-content-center">
                                                            <i class="ri-check-line fs-20 text-white"></i>
                                                        </span>
                                                        <div class="d-flex flex-column border-start ms-2 px-2">
                                                            <p class="fw-semibold fs-12 mb-0 ms-1">
                                                                <i>Uploaded On:
                                                                    {{ $file->created_at->format('d-m-Y') }}</i>
                                                            </p>
                                                            <div class="d-flex gap-1 mt-1">
                                                                <span
                                                                    class="fs-12 text-dark text-decoration-underline actionHandler px-1 cursor-pointer"
                                                                    data-url="{{ route('file.viewframe') }}"
                                                                    data-frame="offcanvas"
                                                                    data-file-url="{{ getFileUrl($file->file_url) }}"
                                                                    data-handler="openFilePreview">View</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection
@else
    @section('content')
        @if ($isEditorOfThisManuscript && $handlingEditorDecision === null && $application->status === 'processing')
            {{-- Editor has been assigned but has not yet decided --}}
            <div class="card mx-auto" style="max-width: 560px;">
                <div class="card-body text-center p-5">
                    <div class="mb-3">
                        <span class="avatar avatar-xl bg-warning-subtle rounded-circle d-inline-flex align-items-center justify-content-center">
                            <i class="bi bi-question-circle fs-1 text-warning"></i>
                        </span>
                    </div>
                    <h4 class="card-title mb-2">New Manuscript Assignment</h4>
                    <p class="text-muted mb-1"><strong>Manuscript ID:</strong> {{ $application->application_id ?? 'N/A' }}</p>
                    <p class="text-muted mb-3"><strong>Title:</strong> {{ Str::limit($application->title ?? 'N/A', 60) }}</p>
                    <hr>
                    <p class="text-muted mb-4">
                        You have been assigned to handle this manuscript.
                        Please <strong>accept or decline</strong> this assignment to proceed.
                    </p>
                    <button class="btn btn-warning actionHandler"
                        data-url="{{ route('admin.application.handlingEditorDecision', $application->uuid) }}">
                        Accept / Reject Assignment
                    </button>
                </div>
            </div>
        @elseif ($isEditorOfThisManuscript && $handlingEditorDecision === 0)
            {{-- Editor already declined --}}
            <div class="card mx-auto" style="max-width: 560px;">
                <div class="card-body text-center p-5">
                    <div class="mb-3">
                        <span class="avatar avatar-xl bg-danger-subtle rounded-circle d-inline-flex align-items-center justify-content-center">
                            <i class="bi bi-x-circle fs-1 text-danger"></i>
                        </span>
                    </div>
                    <h4 class="card-title mb-2">Assignment Declined</h4>
                    <p class="text-muted mb-1"><strong>Manuscript ID:</strong> {{ $application->application_id ?? 'N/A' }}</p>
                    <p class="text-muted mb-3"><strong>Title:</strong> {{ Str::limit($application->title ?? 'N/A', 60) }}</p>
                    <hr>
                    <p class="text-muted mb-2">You have declined to work on this assignment.</p>
                    @if (!empty($handlingEditorReason))
                        <div class="alert alert-danger text-start mt-3 mb-0">
                            <strong>Your Reason:</strong> {{ $handlingEditorReason }}
                        </div>
                    @endif
                </div>
            </div>
        @else
            {{-- Default message for reviewers or other users without permission --}}
            <div class="alert alert-warning text-center">
                <h4>Action Required</h4>
                <p class="mb-2">
                    You cannot access this manuscript for one of the following reasons:
                </p>
                <ol class="text-start mb-0">
                    <li class="mb-2">
                        <strong>Review Request Pending:</strong> Please accept the review request to access the
                        manuscript. If you do not wish to review it, please reject the request with a reason.
                    </li>
                    <li>
                        <strong>Reassigned Manuscript:</strong> This manuscript has been assigned to another user,
                        so access is no longer available to you.
                    </li>
                </ol>
            </div>
        @endif
    @endsection
@endif

@section('javascripts')
    <script>
        window.confirmAccept = (button) => {
            const url = new URL(button.dataset.url);
            swal({
                title: "Are you sure?",
                text: 'This action can not be reversed!',
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    const originalHTML = button.innerHTML;
                    button.disabled = true;
                    button.innerHTML = settings.spinner;
                    fetch(url, {
                            method: "GET",
                            headers: {
                                ...settings.headers,
                                Accept: "text/html, application/json",
                            },
                        })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.status == '200') {
                                toastr.success(data.msg);
                                if (data.redirect) {
                                    window.location.href = data.redirect;
                                    return;
                                }
                                return;
                            }
                            toastr.error('Unable to accept application');
                        })
                        .catch((error) => {
                            toastr.error(error);
                        }).finally(() => {
                            button.disabled = false;
                            button.innerHTML = originalHTML;
                        });
                } else {
                    return;
                }
            });
        }
    </script>

    <script>
        window.reviseApplication = (button) => {
            const url = new URL(button.dataset.url);
            swal({
                title: "Are you sure?",
                text: 'This action can not be reversed!',
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    const originalHTML = button.innerHTML;
                    button.disabled = true;
                    button.innerHTML = settings.spinner;
                    fetch(url, {
                            method: "GET",
                            headers: {
                                ...settings.headers,
                                Accept: "text/html, application/json",
                            },
                        })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.status == '200') {
                                toastr.success(data.msg);
                                if (data.redirect) {
                                    window.location.href = data.redirect;
                                    return;
                                }
                                return;
                            }
                            toastr.error('Unable to revise application');
                        })
                        .catch((error) => {
                            toastr.error(error);
                        }).finally(() => {
                            button.disabled = false;
                            button.innerHTML = originalHTML;
                        });
                } else {
                    return;
                }
            });
        }
    </script>

    <script>
        window.confirmReject = (button) => {
            const url = new URL(button.dataset.url);
            swal({
                title: "Are you sure?",
                text: 'This action can not be reversed!',
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    const originalHTML = button.innerHTML;
                    button.disabled = true;
                    button.innerHTML = settings.spinner;
                    fetch(url, {
                            method: "GET",
                            headers: {
                                ...settings.headers,
                                Accept: "text/html, application/json",
                            },
                        })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.status == '200') {
                                toastr.success(data.msg);
                                if (data.redirect) {
                                    window.location.href = data.redirect;
                                    return;
                                }
                                return;
                            }
                            toastr.error('Unable to revise application');
                        })
                        .catch((error) => {
                            toastr.error(error);
                        }).finally(() => {
                            button.disabled = false;
                            button.innerHTML = originalHTML;
                        });
                } else {
                    return;
                }
            });
        }
    </script>

    <script src="{{ asset('engines/fileviewer.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.querySelector('.modal');
            if (modal) {
                modal.addEventListener('shown.bs.modal', initializeFormListeners);
            } else {
                initializeFormListeners();
            }
        });

        function displayoption() {
            document.getElementById('question-7').style.display = 'block';
            const q20Radios = document.querySelectorAll('input[name="q20"]');
            q20Radios.forEach(radio => radio.setAttribute('required', 'required'));
        }

        function displayoption2() {
            document.getElementById('question-7').style.display = 'none';
            const q20Radios = document.querySelectorAll('input[name="q20"]');
            q20Radios.forEach(radio => {
                radio.removeAttribute('required');
                radio.checked = false;
            });
            document.getElementById('q20_hidden').value = '';
            updateQ8Selection();
        }

        document.addEventListener('DOMContentLoaded', function() {
            initializeQ14Logic();
        });

        function initializeQ14Logic() {
            const q14Radios = document.querySelectorAll('input[name="q14"]');
            const q14Textarea = document.getElementById('q14-textarea');
            const q14TextInput = document.getElementById('question-14');
            const q14Hidden = document.getElementById('q14_hidden');

            q14Radios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'yes') {
                        q14Textarea.style.display = 'block';
                        q14Hidden.value = 'yes (' + (q14TextInput.value.trim() || '') + ')';
                    } else {
                        q14Textarea.style.display = 'none';
                        q14TextInput.value = '';
                        q14Hidden.value = 'no';
                    }
                });
            });

            q14TextInput?.addEventListener('input', function() {
                const selected = document.querySelector('input[name="q14"]:checked')?.value;
                if (selected === 'yes') {
                    q14Hidden.value = 'yes (' + this.value.trim() + ')';
                }
            });
        }

        function initializeFormListeners() {
            try {
                function updateHiddenInput(questionNumber) {
                    const radios = document.querySelectorAll(`input[name="q${questionNumber}"]`);
                    const hiddenInput = document.getElementById(`q${questionNumber}_hidden`);
                    if (!hiddenInput) return;
                    radios.forEach(radio => {
                        radio.addEventListener('change', function() {
                            hiddenInput.value = this.value;
                            if (questionNumber >= 1 && questionNumber <= 6) {
                                updateQ8Selection();
                            } else if (questionNumber === 14) {
                                const q14Textarea = document.getElementById('q14-textarea');
                                if (this.value === 'yes') {
                                    q14Textarea.style.display = 'block';
                                    document.getElementById('question-14').setAttribute('required',
                                        'required');
                                } else {
                                    q14Textarea.style.display = 'none';
                                    document.getElementById('question-14').removeAttribute('required');
                                    document.getElementById('question-14').value = '';
                                }
                            }
                        });
                    });
                }

                function updateTextInput(questionNumber) {
                    const textarea = document.getElementById(`question-${questionNumber}`);
                    const hiddenInput = document.getElementById(`q${questionNumber}_hidden`);
                    if (!textarea || !hiddenInput) return;
                    textarea.addEventListener('input', function() {
                        hiddenInput.value = this.value;
                    });
                }

                function updateQ7HiddenInput() {
                    const q7Radios = document.querySelectorAll('input[name="q7"]');
                    const q7Hidden = document.getElementById('q7_hidden');
                    const q20Radios = document.querySelectorAll('input[name="q20"]');
                    const q20Hidden = document.getElementById('q20_hidden');

                    q7Radios.forEach(radio => {
                        radio.addEventListener('change', function() {
                            q7Hidden.value = this.value;
                            if (this.value === 'no') {
                                q20Hidden.value = '';
                                q20Radios.forEach(r => {
                                    r.removeAttribute('required');
                                    r.checked = false;
                                });
                            } else {
                                q20Radios.forEach(r => r.setAttribute('required', 'required'));
                            }
                            updateQ8Selection();
                        });
                    });

                    q20Radios.forEach(radio => {
                        radio.addEventListener('change', function() {
                            q20Hidden.value = this.value;
                            updateQ8Selection();
                        });
                    });
                }

                const recommendationField =
                    '{{ (function () use ($application) {
                        $types = $application->article_type ?? [];
                        if (is_string($types)) {
                            $decoded = json_decode($types, true);
                            $types = json_last_error() === JSON_ERROR_NONE ? $decoded : [$types];
                        }
                        if (!is_array($types)) {
                            $types = [$types];
                        }
                        $primary = trim($types[0] ?? '');
                        $groupA = [
                            'Original Articles',
                            'Original Article',
                            'Systematic Review and Meta-Analysis',
                            'Brief Communications',
                            'Brief Communication',
                        ];
                        $groupB = [
                            'Infectious Diseases Cases/Vignettes',
                            'Infectious Diseases Cases/Vignette',
                            'Narrative Reviews',
                            'Narrative Review',
                        ];
                        if (in_array($primary, $groupA, true)) {
                            return 'q17';
                        }
                        if (in_array($primary, $groupB, true)) {
                            return 'q18';
                        }
                        return 'q8';
                    })() }}';

                const recommendationConfig = {
                    q8: {
                        section: document.getElementById('question-8-section'),
                        radios: document.querySelectorAll('input[name="q8"]'),
                        hidden: document.getElementById('q8_hidden'),
                    },
                    q17: {
                        section: document.getElementById('question-17-section'),
                        radios: document.querySelectorAll('input[name="q17"]'),
                        hidden: document.getElementById('q17_hidden'),
                    },
                    q18: {
                        section: document.getElementById('question-18-section'),
                        radios: document.querySelectorAll('input[name="q18"]'),
                        hidden: document.getElementById('q18_hidden'),
                    },
                };

                function toggleRecommendationSections() {
                    Object.entries(recommendationConfig).forEach(([key, cfg]) => {
                        const isActive = key === recommendationField;
                        cfg.section?.classList.toggle('d-none', !isActive);
                        cfg.radios.forEach(radio => {
                            radio.disabled = !isActive;
                            radio.required = isActive;
                            if (!isActive) {
                                radio.checked = false;
                            }
                        });
                        if (!isActive && cfg.hidden) {
                            cfg.hidden.value = '';
                        }
                    });
                }

                function applyRecommendationValue(value) {
                    const cfg = recommendationConfig[recommendationField];
                    if (!cfg) return;

                    cfg.radios.forEach(radio => {
                        radio.checked = radio.value === value;
                        radio.disabled = radio.value !== value;
                    });
                    if (cfg.hidden) {
                        cfg.hidden.value = value;
                    }
                }

                function updateQ8Selection() {
                    let sum = 0;
                    for (let i = 1; i <= 6; i++) {
                        const hiddenInput = document.getElementById(`q${i}_hidden`);
                        if (hiddenInput.value) {
                            sum += parseInt(hiddenInput.value, 10) || 0;
                        }
                    }
                    const q7Hidden = document.getElementById('q7_hidden');
                    const q20Hidden = document.getElementById('q20_hidden');
                    if (q7Hidden.value === 'yes' && q20Hidden.value) {
                        sum += parseInt(q20Hidden.value, 10) || 0;
                    }

                    let selectedValue = 'reject';
                    if (sum >= 28 && sum <= 35) {
                        selectedValue = 'accept';
                    } else if (sum >= 21 && sum <= 27) {
                        selectedValue = 'minor';
                    } else if (sum >= 14 && sum <= 20) {
                        selectedValue = 'major';
                    }

                    applyRecommendationValue(selectedValue);
                }

                for (let i = 1; i <= 18; i++) {
                    if (i !== 15 && i !== 16 && i !== 19) {
                        updateHiddenInput(i);
                    }
                }

                updateTextInput(15);
                updateTextInput(16);
                updateTextInput(19);

                updateQ7HiddenInput();
                toggleRecommendationSections();

                document.querySelector('form').addEventListener('submit', function(event) {
                    let isValid = true;
                    const errorElement = document.querySelector('[data-error="review_status"]');
                    errorElement.textContent = '';

                    for (let i = 1; i <= 18; i++) {
                        if (i !== 15 && i !== 16 && i !== 19) {
                            const radios = document.querySelectorAll(`input[name="q${i}"]:checked`);
                            const hiddenInput = document.getElementById(`q${i}_hidden`);
                            if (!radios.length && hiddenInput) {
                                isValid = false;
                                errorElement.textContent = `Please answer question ${i}.`;
                                break;
                            } else if (radios.length && hiddenInput) {
                                hiddenInput.value = radios[0].value;
                            }
                        }
                    }

                    const q7Hidden = document.getElementById('q7_hidden');
                    const q20Radios = document.querySelectorAll('input[name="q20"]:checked');
                    const q20Hidden = document.getElementById('q20_hidden');
                    if (q7Hidden.value === 'yes' && !q20Radios.length) {
                        isValid = false;
                        errorElement.textContent = 'Please answer question 7 (Methodology Validity) sub-question.';
                    } else if (q20Radios.length && q20Hidden) {
                        q20Hidden.value = q20Radios[0].value;
                    }

                    for (let i of [15, 16, 19]) {
                        const textarea = document.getElementById(`question-${i}`);
                        const hiddenInput = document.getElementById(`q${i}_hidden`);
                        if (textarea && !textarea.value.trim()) {
                            isValid = false;
                            errorElement.textContent = `Please provide input for question ${i}.`;
                            break;
                        } else if (textarea && hiddenInput) {
                            hiddenInput.value = textarea.value.trim();
                        }
                    }

                    updateQ8Selection();
                    if (!isValid) {
                        event.preventDefault();
                    }
                });
            } catch (error) {
                console.error('Error initializing form listeners:', error);
            }
        }
    </script>

    <script>
        function displayoption() {
            document.getElementById('question-7').style.display = 'block';
        }

        function displayoption2() {
            document.getElementById('question-7').style.display = 'none';
            document.querySelectorAll('input[name="q20"]').forEach((input) => {
                input.checked = false;
            });
        }

        function updateScore() {
            let totalScore = 0;
            const questions = ['q1', 'q2', 'q3', 'q4', 'q5', 'q6', 'q20'];
            questions.forEach((q) => {
                const selected = document.querySelector(`input[name="${q}"]:checked`);
                if (selected) {
                    totalScore += parseInt(selected.value) || 0;
                }
            });
            document.getElementById('totalScore').textContent = totalScore;

            const q8Radios = document.querySelectorAll('input[name="q8"]');
            q8Radios.forEach(radio => radio.disabled = false);
            if (totalScore >= 28) {
                document.getElementById('q8-accept').checked = true;
            } else if (totalScore >= 21) {
                document.getElementById('q8-minor').checked = true;
            } else if (totalScore >= 14) {
                document.getElementById('q8-major').checked = true;
            } else {
                document.getElementById('q8-reject').checked = true;
            }
        }

        document.querySelectorAll(
            'input[type="radio"][name="q1"], input[type="radio"][name="q2"], input[type="radio"][name="q3"], input[type="radio"][name="q4"], input[type="radio"][name="q5"], input[type="radio"][name="q6"], input[type="radio"][name="q20"]'
        ).forEach((input) => {
            input.addEventListener('change', updateScore);
        });

        updateScore();
    </script>

    <script>
        window.confirmDenied = (button) => {
            const url = new URL(button.dataset.url);
            swal({
                title: "Are you sure?",
                text: 'This action can not be reversed!',
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    const originalHTML = button.innerHTML;
                    button.disabled = true;
                    button.innerHTML = settings.spinner;
                    fetch(url, {
                            method: "GET",
                            headers: {
                                ...settings.headers,
                                Accept: "text/html, application/json",
                            },
                        })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.status == '200') {
                                toastr.success(data.msg);
                                if (data.redirect) {
                                    window.location.href = data.redirect;
                                    return;
                                }
                                return;
                            }
                            toastr.error('You accepted to work on this application');
                        })
                        .catch((error) => {
                            toastr.error(error);
                        }).finally(() => {
                            button.disabled = false;
                            button.innerHTML = originalHTML;
                        });
                } else {
                    return;
                }
            });
        }
    </script>

    <script>
        window.confirmAccept = (button) => {
            const url = new URL(button.dataset.url);
            swal({
                title: "Are you sure?",
                text: 'This action can not be reversed!',
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    const originalHTML = button.innerHTML;
                    button.disabled = true;
                    button.innerHTML = settings.spinner;
                    fetch(url, {
                            method: "GET",
                            headers: {
                                ...settings.headers,
                                Accept: "text/html, application/json",
                            },
                        })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.status == '200') {
                                toastr.success(data.msg);
                                if (data.redirect) {
                                    window.location.href = data.redirect;
                                    return;
                                }
                                return;
                            }
                            toastr.error('You accepted to work on this application');
                        })
                        .catch((error) => {
                            toastr.error(error);
                        }).finally(() => {
                            button.disabled = false;
                            button.innerHTML = originalHTML;
                        });
                } else {
                    return;
                }
            });
        }
    </script>

    <script>
        document.addEventListener('shown.bs.modal', (event) => {
            const checkboxes = event.target.querySelectorAll('.check-item');
            const acceptRadio = event.target.querySelector('#accept');
            const reviseRadio = event.target.querySelector('#revise');

            checkboxes.forEach(cb => {
                cb.addEventListener('change', () => {
                    const allChecked = Array.from(checkboxes).every(item => item.checked);
                    if (allChecked) {
                        acceptRadio.disabled = false;
                    } else {
                        acceptRadio.disabled = true;
                        acceptRadio.checked = false;
                    }
                });
            });
        })
    </script>

    <script>
        window.confirmSendToChief = (button) => {
            const url = new URL(button.dataset.url);
            swal({
                title: "Are you sure?",
                text: 'This action can not be reversed!',
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    const originalHTML = button.innerHTML;
                    button.disabled = true;
                    button.innerHTML = settings.spinner;
                    fetch(url, {
                            method: "GET",
                            headers: {
                                ...settings.headers,
                                Accept: "text/html, application/json",
                            },
                        })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.status == '200') {
                                toastr.success(data.msg);
                                if (data.redirect) {
                                    window.location.href = data.redirect;
                                    return;
                                }
                                return;
                            }
                            toastr.error('Unable to send application for final approval');
                        })
                        .catch((error) => {
                            toastr.error(error);
                        }).finally(() => {
                            button.disabled = false;
                            button.innerHTML = originalHTML;
                        });
                } else {
                    return;
                }
            });
        }
    </script>
@endsection
