@php
    $user = authUser('admin');
    $hideAssigneeColumn = in_array(request('type'), ['closed', 'published', 'rejected', 'withdrawn', 'duplicate'], true);
@endphp
@forelse ($applications as $application)
    @php
        // use App\Models\ApplicationTimeline;

        $timeline = \App\Models\ApplicationTimeline::where('application_id', $application->id)
            ->whereIn('status', ['submitted', 'resubmitted'])
            ->orderBy('id', 'desc')
            ->first();
    @endphp
    <tr>
        <td>{{ $loop->iteration }}</td>
        <td>
            <div class="d-flex flex-column">
                <a class="d-flex flex-column" href="{{ route('admin.application.show', $application->uuid ?? '') }}">
                    <span class="text-dark fw-bold">{{ Str::limit($application->title, 20, '...') }}</span>
                    <span class="text-muted">{{ $application->application_id ?? '---' }}</span>
                </a>
                @if (!empty($application->description))
                    <span class="fs-12 text-primary text-decoration-underline actionHandler mt-1 cursor-pointer"
                        data-url="{{ route('admin.application.viewAbstract', $application->uuid) }}">
                        View Abstract
                    </span>
                @endif
            </div>
        </td>
        <td>
            @php
                if ($application->submission_type == 'new_submission') {
                    $record = 'New Submission';
                } else {
                    $record = 'Revised Submission';
                }

            @endphp
            <div class="d-flex align-items-center">
                <span>{{ $record ?? '---' }}</span>
                {{-- <i class="ri-checkbox-circle-fill text-info ms-1"></i> --}}
            </div>
        </td>
        <td>
            <div class="d-flex align-items-center">
                <ul>
                    @foreach ($application->article_type ?? [] as $articalType)
                        <li>-{{ $articalType }}</li>
                    @endforeach
                </ul>
            </div>
        </td>
        @if (authUser('admin')->is_admin == 1)
            <td>
                <div class="d-flex align-items-center">
                    <span>{{ $application->author->name ?? '---' }}</span>
                    <i class="ri-checkbox-circle-fill text-info ms-1"></i>
                </div>
            </td>
        @endif
        @if (authUser('admin')->is_admin == 1 && !$hideAssigneeColumn)
            @php
                $canAssign = authUser('admin')->can('application.assign') &&
                    in_array($application->status, ['technical checks approved', 'out of scope', 'resubmitted']);
                $canReassign = $application->status === 'processing' &&
                    data_get($application->data, 'handling_editor_decision') === 0;

                $isReviewerTask = in_array($application->status, ['assigning reviewers', 'under review']);

                // The external reviewer is only relevant while the manuscript is actively
                // under review. After review (e.g. status 'reviewed') only the editor shows.
                $isReviewPhase = in_array($application->status, ['assigning reviewers', 'under review', 're-review'], true);

                $assigneeRole = fn($admin) => $admin ? (collect($admin->getRoleNames())
                    ->filter(function ($role) use ($isReviewerTask) {
                        $isReviewer = Str::lower($role) === 'reviewer';
                        return $isReviewerTask ? $isReviewer : !$isReviewer;
                    })
                    ->map(fn($role) => Str::title(str_replace('-', ' ', $role)))
                    ->filter()
                    ->implode(', ') ?: null) : null;

                $assigneeList = [];

                if (in_array($application->status, ['revise', 'proofreading'], true)) {
                    $assigneeList[] = ['user' => $application->author, 'role' => 'Author', 'type' => null];
                } elseif (in_array($application->status, ['submitted', 'resubmitted'], true)) {
                    $hasTechChecksApproved = \App\Models\ApplicationTimeline::where('application_id', $application->id)
                        ->where('status', 'technical checks approved')
                        ->exists();

                    $roleName = $hasTechChecksApproved ? 'assitantEditor' : 'technicalEditor';

                    $assigneeList[] = [
                        'user' => \App\Models\Admin::whereHas('roles', function ($q) use ($roleName) {
                            $q->where('name', $roleName);
                        })->first(),
                        'role' => $hasTechChecksApproved ? 'Assistant Editor' : 'Technical Editor',
                        'type' => null,
                    ];
                } elseif ($application->status === 'technical checks approved') {
                    $assigneeList[] = [
                        'user' => \App\Models\Admin::whereHas('roles', function ($q) {
                            $q->where('name', 'assitantEditor');
                        })->first(),
                        'role' => 'Assistant Editor',
                        'type' => null,
                    ];
                } else {
                    if ($application->assignee) {
                        $assigneeList[] = [
                            'user' => $application->assignee,
                            'role' => $assigneeRole($application->assignee),
                            'type' => 'internal',
                        ];
                    }
                    if ($isReviewPhase && $application->assigned_to_ex_id && $application->assigned_to_ex_id !== 'not') {
                        $externalUser = \App\Models\Admin::find($application->assigned_to_ex_id);
                        if ($externalUser) {
                            $assigneeList[] = [
                                'user' => $externalUser,
                                'role' => $assigneeRole($externalUser),
                                'type' => 'external',
                            ];
                        }
                    }
                }

                $primaryAssignee = $assigneeList[0]['user'] ?? null;
            @endphp
            <td style="white-space: normal;">
                <div class="d-flex align-items-center {{ ($canAssign || $canReassign) ? 'actionHandler' : '' }}"
                    @if ($canAssign || $canReassign) data-url="{{ route('admin.application.assign', $application->uuid) }}" @endif>
                    <img class="avatar avatar-sm me-2"
                        src="{{ asset($primaryAssignee->avatar ?? 'admin/assets/images/avatar/1.png') }}"
                        alt="avatar">

                    <div class="d-flex flex-column">
                        @forelse ($assigneeList as $assignee)
                            @php
                                $assigneeUser = $assignee['user'] ?? null;
                                $assigneeInactive = $assigneeUser && !filter_var($assigneeUser->status, FILTER_VALIDATE_BOOLEAN);
                                $assigneeIsReviewer = Str::contains($assignee['role'] ?? '', 'Reviewer');
                                $assigneeIsAuthor = ($assignee['role'] ?? null) === 'Author';
                                $reviewerType = $assigneeIsReviewer ? ($assigneeUser?->rev_type ?? $assignee['type'] ?? null) : null;

                                if ($assigneeIsReviewer) {
                                    $reviewerLabel = in_array($reviewerType, ['internal', 'external'], true)
                                        ? Str::lower($reviewerType) . ' reviewer'
                                        : 'reviewer';
                                    $inactiveAssigneeMessage = 'Inactive ' . $reviewerLabel . '. Please select different staff.';
                                } elseif ($assigneeIsAuthor) {
                                    $inactiveAssigneeMessage = 'Inactive author account.';
                                } else {
                                    $inactiveAssigneeMessage = 'Inactive staff. Please select different staff.';
                                }
                            @endphp
                            <a class="d-flex flex-column">
                                <span class="{{ $assigneeInactive ? 'text-danger' : 'text-dark' }} fw-bold">{{ $assigneeUser->name ?? '---' }}</span>
                                @if (!empty($assignee['role']))
                                    <span class="text-muted">{{ $assignee['role'] }}{{ ($assignee['type'] && Str::contains($assignee['role'], 'Reviewer')) ? '(' . $assignee['type'] . ')' : '' }}</span>
                                @endif
                                @if ($assigneeInactive)
                                    <span class="d-block text-danger fs-12 fw-semibold"
                                        style="white-space: normal; overflow-wrap: anywhere;">{{ $inactiveAssigneeMessage }}</span>
                                @endif
                            </a>
                        @empty
                            <span class="text-dark fw-bold">---</span>
                        @endforelse
                    </div>
                </div>
            </td>
        @endif


        <td>



            <span class="badge badge-sm badge-rounded badge-{{ $statusColorMap[$application->status] ?? 'primary' }}">
                {{ ['chief-review' => 'Chief Review', 'chief-response' => 'Chief Response', 'chief-approval' => 'Chief Approval'][$application->status] ?? Str::ucfirst($application->status ?? '---') }}
            </span>
            @if ( (isEditor('admin') || authUser('admin')->is_admin == 1) && (($application->accept_in ?? '') == '0' || ($application->accept_ex ?? '') == '0'))
                <div class="mt-1">
                    @if (($application->accept_in ?? '') == '0')
                        <span class="badge bg-danger actionHandler cursor-pointer"
                            data-url="{{ route('admin.application.reviewerRejection', [$application->uuid, 'internal']) }}">
                            Internal Reviewer Declined
                        </span>
                    @endif
                    @if (($application->accept_ex ?? '') == '0')
                        <span class="badge bg-danger actionHandler cursor-pointer"
                            data-url="{{ route('admin.application.reviewerRejection', [$application->uuid, 'external']) }}">
                            External Reviewer Declined
                        </span>
                    @endif
                </div>
            @endif
            @if (
                authUser('admin')->is_admin == 1 &&
                    $application->status === 'processing' &&
                    data_get($application->data, 'handling_editor_decision') === 0)
                <div class="mt-1">
                    <span class="badge bg-danger actionHandler cursor-pointer"
                        data-url="{{ route('admin.application.handlingEditorRejection', $application->uuid) }}">
                        Editor Rejected
                    </span>
                </div>
            @endif
            {{-- @if ($application->status == 'published')
                <div class="ms-2 my-3">
                    <span class="fs-12 text-dark text-decoration-underline actionHandler px-1 text-center"
                        data-url="{{ route('file.viewframe') }}" data-frame="offcanvas"
                        data-file-url="{{ getFileUrl($application->data['file_url'] ?? '') }}"
                        data-handler="openFilePreview" target="_blank" title="View Uploaded File">
                        View
                    </span>
                </div>
            @endif --}}
            @if ($application->status == 'published')
                @php
                    // The published manuscript's view option should open the
                    // latest corrected proof. Fall back to the direct-publish
                    // document for older records that do not have one.
                    $correctedProof = $application->attachments
                        ->where('attachment_type', 'Corrected Proof')
                        ->sortByDesc('created_at')
                        ->first();

                    $publishedDoc = $application->attachments->firstWhere('attachment_type', 'Published with DOI');

                    $viewDoc = $correctedProof ?? $publishedDoc;
                    $docUrl = $viewDoc ? getFileUrl($viewDoc->file_url) : '';
                @endphp
                {{-- @dd($docUrl) --}}

                <div class="ms-2 my-3">
                    <span class="fs-12 text-dark text-decoration-underline actionHandler px-1 text-center"
                        data-url="{{ route('file.viewframe') }}" data-frame="offcanvas"
                        data-file-url="{{ $docUrl }}" data-handler="openFilePreview" target="_blank"
                        title="View Latest Corrected Proof">
                        View
                    </span>
                </div>
            @endif
        </td>
        <td>{{ $application->created_at->format('d-M-Y, h:i') }}</td>
        <td>{{ $application->updated_at->format('d-M-Y, h:i') }}</td>

        <td>
            <div class="btn-group btn-group-sm">
                <div class="d-flex gap-1">
                    @if (authUser('admin')->email == 'akanksha.itconsultant@gmail.com' &&
                            !in_array($application->status, ['incomplete', 'published']) &&
                            authUser('admin')->id == 1)
                        <button type="button" class="btn btn-sm btn-success actionHandler"
                            data-url="{{ route('admin.application.direct_publish', ['uuid' => $application->uuid]) }}"
                            title="Publish Manuscript">
                            Publish
                        </button>
                    @endif
                    @if (
                        $application->status === 'processing' &&
                            $application->assigned_to_id == authUser('admin')->id &&
                            data_get($application->data, 'handling_editor_decision') === null)
                        <button type="button" class="btn btn-sm btn-warning actionHandler"
                            data-url="{{ route('admin.application.handlingEditorDecision', ['uuid' => $application->uuid]) }}"
                            title="Accept or Reject this assignment">
                            Accept / Reject
                        </button>
                    @endif
                    @if (authUser('admin')->can('application.chiefToAssistanResponse') &&
                            in_array($application->status, ['chief-review', 'chief-approval', 'editor-accepts', 'editor-rejects']) &&
                            data_get($application->data, 'status') == 'sended to chief approval')
                        <div>
                            <button type="button" class="btn btn-group-sm btn-sm btn-info actionHandler"
                                data-url="{{ route('admin.application.chiefToAssistanResponse', ['uuid' => $application->uuid]) }}"
                                title="Permission to Accept/Reject/Revise the Manuscript">
                                Permit
                            </button>
                        </div>
                    @endif
                    @if (
                        (authUser('admin')->can('application.rev_accept_action') ||
                            authUser('admin')->can('application.rev_denied_action')) &&
                            $user->rev_type === 'internal' &&
                            $application->accept_in === null &&
                            ($application->status === 'assigning reviewers' || $application->status === 're-review') &&
                            $application->assigned_to_id == authUser('admin')->id)
                        <div class="d-flex gap-1 align-items-center">
                            <button class="btn btn-success btn-sm actionHandler"
                                data-url="{{ route('admin.application.rev_decision', $application->uuid) }}">
                                Accept / Deny
                            </button>
                        </div>
                    @elseif (
                        (authUser('admin')->can('application.rev_accept_action') ||
                            authUser('admin')->can('application.rev_denied_action')) &&
                            $user->rev_type === 'external' &&
                            $application->accept_ex === null &&
                            ($application->status === 'assigning reviewers' || $application->status === 're-review') &&
                            $application->assigned_to_ex_id == authUser('admin')->id)
                        <div class="d-flex gap-1 align-items-center">
                            <button class="btn btn-success btn-sm actionHandler"
                                data-url="{{ route('admin.application.rev_decision', $application->uuid) }}">
                                Accept / Deny
                            </button>
                        </div>
                    @else
                        <button class="btn btn-sm btn-danger actionHandler"
                            {{ isSuperAdmin() ? ($application->status != 'submitted' ? 'disabled' : '') : 'disabled' }}
                            data-action="delete"
                            data-url="{{ route('admin.application.destroy', $application->uuid) }}">
                            <i class="ri-delete-bin-line"></i></button>
                    @endif
                </div>
            </div>

        </td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="p-4 text-center">No Data Available !</td>
    </tr>
@endforelse
