@php
    $internalReviewScore = $timeline1->data['review_score'] ?? [];
    $internalRemark = $timeline1->data['comment'] ?? null;
    $internalScore = $timeline1->data['score'] ?? null;
    $internalFile = $timeline1->data['file_input'] ?? null;

    $externalReviewScore = $timeline2->data['review_score'] ?? [];
    $externalRemark = $timeline2->data['comment'] ?? null;
    $externalScore = $timeline2->data['score'] ?? null;
    $externalFile = $timeline2->data['file_input'] ?? null;

    $user = Auth::guard('admin')->user();
    $rev_type = $user?->rev_type;
    $isAdmin = $user?->is_admin == 1;
    $isEditorRole = isEditor();

    // rev_type is a static property of the user, not of their relationship to this
    // manuscript. An editor who is also typed as a reviewer would otherwise be
    // blinded to the other slot's review on their own manuscript, so the gate is
    // relationship-aware: admins, publishers and THIS manuscript's handling editor
    // see both reviews, while a reviewer assigned to the other slot stays blinded.
    $isHandlingEditorOfThis = $isEditorOfThisManuscript ?? false;
    $isUserPublisher = isPublisher();

    $canSeeInternal = $isAdmin || $isUserPublisher || $isHandlingEditorOfThis || $rev_type !== 'external';
    $canSeeExternal = $isAdmin || $isUserPublisher || $isHandlingEditorOfThis || $rev_type !== 'internal';
@endphp

<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Reviewer's Remark</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
            @if (!empty($internalReviewScore) && $canSeeInternal)
                <div class="mb-3">
                    <h3><strong>Internal Reviewer</strong></h3>
                </div>

                <div class="mb-3">
                    <h6><strong>Total Score:</strong> {{ $internalScore }}</h6>
                </div>

                @php
                    $canSeeAuthorMsg = $isAdmin || $isEditorRole || $rev_type === 'internal';
                    $canSeeEditorMsg = $isAdmin || $isEditorRole || $rev_type === 'internal';
                    // The admin note belongs to the admin + the reviewer's own slot,
                    // never the handling editor - even if he is typed `internal`.
                    $canSeeAdminMsg = $isAdmin || ($rev_type === 'internal' && !$isHandlingEditorOfThis);
                    // Editors can see file + full review details; admin and the reviewer type also can
                    $canSeeAllDetails = $isAdmin || $isEditorRole || $rev_type === 'internal';
                @endphp

                @if ($canSeeAuthorMsg)
                    <div class="mb-3">
                        <h6><strong>Message to Author</strong></h6>
                        <p>{{ $internalReviewScore[15]['answer'] ?? 'N/A' }}</p>
                    </div>
                @endif

                @if ($canSeeEditorMsg)
                    <div class="mb-3">
                        <h6><strong>Message to Editor</strong></h6>
                        <p>{{ $internalReviewScore[16]['answer'] ?? 'N/A' }}</p>
                    </div>
                @endif

                @if ($canSeeAdminMsg)
                    <div class="mb-3">
                        <h6><strong>Message to Admin</strong></h6>
                        <p>{{ $internalReviewScore[19]['answer'] ?? 'N/A' }}</p>
                    </div>
                @endif

                @if ($canSeeAdminMsg)
                    <div class="mb-3">
                        <h6><strong>Final Remark</strong></h6>
                        <p>{{ $internalRemark ?? 'N/A' }}</p>
                    </div>
                @endif

                @if ($canSeeAllDetails && !empty($internalFile))
                    <div class="mb-3">
                        <h6><strong>Reviewer's File Input</strong></h6>
                        <span class="fs-12 text-dark text-decoration-underline actionHandler px-1 text-center"
                            data-url="{{ route('file.viewframe') }}" data-frame="offcanvas"
                            data-file-url="{{ getFileUrl($internalFile ?? '') }}" data-handler="openFilePreview"
                            target="_blank" title="View Uploaded File">
                            Click to View
                        </span>
                    </div>
                @endif

                <hr>

                @if ($canSeeAllDetails)
                <div class="accordion" id="internalReviewDetailsAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingInternalFullReview">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseInternalFullReview" aria-expanded="false"
                                aria-controls="collapseInternalFullReview">
                                Full Review Details
                            </button>
                        </h2>
                        <div id="collapseInternalFullReview" class="accordion-collapse collapse"
                            aria-labelledby="headingInternalFullReview" data-bs-parent="#internalReviewDetailsAccordion">
                            <div class="accordion-body">
                                <ul class="list-group">
                                    @foreach ($internalReviewScore as $key => $item)
                                        @php
                                            $isAdminNote = (int) $key === 19; // Message to Admin
                                            $allowAdminNote = $isAdmin || ($rev_type === 'internal' && !$isHandlingEditorOfThis);
                                        @endphp
                                        @if ($isAdminNote && !$allowAdminNote)
                                            @continue
                                        @endif
                                        <li class="list-group-item">
                                            <strong>{{ $item['heading'] ?? '' }}</strong><br>
                                            <small>{{ $item['question'] ?? '' }}</small><br>
                                            <span class="text-muted">Answer: {{ $item['answer'] ?? '' }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            @endif

            {{-- FIX 2: Corrected the placement of the closing parenthesis --}}
            @if (!empty($externalReviewScore) && $canSeeExternal)

                {{-- Added margin top if both internal and external render --}}
                <div class="mb-3 @if(!empty($internalReviewScore) && $canSeeInternal) mt-5 @endif">
                    <h3><strong>External Reviewer</strong></h3>
                </div>

                <div class="mb-3">
                    <h6><strong>Total Score:</strong> {{ $externalScore }}</h6>
                </div>

                @php
                    $canSeeAuthorMsg = $isAdmin || $isEditorRole || $rev_type === 'external';
                    $canSeeEditorMsg = $isAdmin || $isEditorRole || $rev_type === 'external';
                    $canSeeAdminMsg = $isAdmin || ($rev_type === 'external' && !$isHandlingEditorOfThis);
                    // Editors can see file + full review details; admin and the reviewer type also can
                    $canSeeAllDetails = $isAdmin || $isEditorRole || $rev_type === 'external';
                @endphp

                @if ($canSeeAuthorMsg)
                    <div class="mb-3">
                        <h6><strong>Message to Author</strong></h6>
                        <p>{{ $externalReviewScore[15]['answer'] ?? 'N/A' }}</p>
                    </div>
                @endif

                @if ($canSeeEditorMsg)
                    <div class="mb-3">
                        <h6><strong>Message to Editor</strong></h6>
                        <p>{{ $externalReviewScore[16]['answer'] ?? 'N/A' }}</p>
                    </div>
                @endif

                @if ($canSeeAdminMsg)
                    <div class="mb-3">
                        <h6><strong>Message to Admin</strong></h6>
                        <p>{{ $externalReviewScore[19]['answer'] ?? 'N/A' }}</p>
                    </div>
                @endif

                @if ($canSeeAdminMsg)
                    <div class="mb-3">
                        <h6><strong>Final Remark</strong></h6>
                        <p>{{ $externalRemark ?? 'N/A' }}</p>
                    </div>
                @endif

                @if ($canSeeAllDetails && !empty($externalFile))
                    <div class="mb-3">
                        <h6><strong>Reviewer's File Input</strong></h6>
                        <span class="fs-12 text-dark text-decoration-underline actionHandler px-1 text-center"
                            data-url="{{ route('file.viewframe') }}" data-frame="offcanvas"
                            data-file-url="{{ getFileUrl($externalFile ?? '') }}" data-handler="openFilePreview"
                            target="_blank" title="View Uploaded File">
                            Click to View
                        </span>
                    </div>
                @endif

                <hr>

                @if ($canSeeAllDetails)
                <div class="accordion" id="externalReviewDetailsAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingExternalFullReview">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseExternalFullReview" aria-expanded="false"
                                aria-controls="collapseExternalFullReview">
                                Full Review Details
                            </button>
                        </h2>
                        <div id="collapseExternalFullReview" class="accordion-collapse collapse"
                            aria-labelledby="headingExternalFullReview" data-bs-parent="#externalReviewDetailsAccordion">
                            <div class="accordion-body">
                                <ul class="list-group">
                                    {{-- Changed variable from $reviewScore to $externalReviewScore to match data --}}
                                    @foreach ($externalReviewScore as $key => $item)
                                        @php
                                            $isAdminNote = (int) $key === 19; // Message to Admin
                                            $allowAdminNote = $isAdmin || ($rev_type === 'external' && !$isHandlingEditorOfThis);
                                        @endphp
                                        @if ($isAdminNote && !$allowAdminNote)
                                            @continue
                                        @endif
                                        <li class="list-group-item">
                                            <strong>{{ $item['heading'] ?? '' }}</strong><br>
                                            <small>{{ $item['question'] ?? '' }}</small><br>
                                            <span class="text-muted">Answer: {{ $item['answer'] ?? '' }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            @endif

        </div>
    </div>
</div>
