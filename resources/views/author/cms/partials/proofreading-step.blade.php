@php
    $proofreadingTimeline = $authorTimelines
        ->where('status', 'proofreading')
        ->sortByDesc('created_at')
        ->first();

    $latestManuscriptFile = $manuScriptFiles->first();
    $latestCorrectedFile = $correctedFiles->first();

    $proofreadingComment = $proofreadingTimeline->data['comment'] ?? null;
    $proofreadingCommentText = $proofreadingComment ? trim(strip_tags($proofreadingComment)) : null;
@endphp

<div class="col-12 mb-3" id="current-step">
    <div class="card border-primary shadow-sm">
        <div class="card-header bg-primary d-flex align-items-center justify-content-between flex-wrap gap-2">
            <h4 class="card-title text-white mb-0">
                <i class="ri-file-text-line me-1"></i> Current Step: Proofreading
            </h4>
            <span class="badge bg-white text-primary">Action Required</span>
        </div>

        <div class="card-body">
            <div class="row g-4 align-items-start">
                <div class="col-lg-7">
                    <p class="mb-3">
                        The editorial office has sent your manuscript for proofreading. Review the editor's
                        comment and upload the corrected file.
                    </p>

                    @if ($proofreadingTimeline)
                        <div class="alert alert-light border mb-0">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-1">
                                <strong class="text-dark">Editor comment</strong>

                                @if (!empty($proofreadingComment))
                                    <button type="button" class="btn btn-link btn-sm p-0 actionHandler"
                                        data-url="{{ route('admin.application.techEditorReadMore', [
                                            'uuid' => $application->uuid,
                                            'timeline_id' => $proofreadingTimeline->id,
                                        ]) }}">
                                        Read full comment
                                    </button>
                                @endif
                            </div>

                            @if ($proofreadingCommentText)
                                <p class="mb-0 text-muted">
                                    {{ \Illuminate\Support\Str::limit($proofreadingCommentText, 240) }}
                                </p>
                            @else
                                <p class="mb-0 text-muted">No additional comment was provided.</p>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="col-lg-5">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary actionHandler"
                            data-url="{{ route('author.application.corrected_proof', [
                                'id' => 'na',
                                'type' => 'Corrected Proof',
                                'app_id' => $application->id,
                                'legend' => 'na',
                            ]) }}">
                            <i class="ri-upload-cloud-line me-1"></i> Upload corrected proof
                        </button>

                        @if ($latestManuscriptFile || $latestCorrectedFile)
                            <div class="d-flex flex-wrap gap-2">
                                @if ($latestManuscriptFile)
                                    <button type="button"
                                        class="btn btn-outline-secondary btn-sm actionHandler"
                                        data-url="{{ route('file.viewframe') }}"
                                        data-frame="offcanvas"
                                        data-handler="openFilePreview"
                                        data-file-url="{{ getFileUrl($latestManuscriptFile->file_url) }}">
                                        <i class="ri-eye-line me-1"></i> View manuscript
                                    </button>
                                @endif

                                @if ($latestCorrectedFile)
                                    <button type="button"
                                        class="btn btn-outline-success btn-sm actionHandler"
                                        data-url="{{ route('file.viewframe') }}"
                                        data-frame="offcanvas"
                                        data-handler="openFilePreview"
                                        data-file-url="{{ getFileUrl($latestCorrectedFile->file_url) }}">
                                        <i class="ri-eye-line me-1"></i> View previous corrected proof
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-2">
                        @if ($latestManuscriptFile)
                            <small class="text-muted">
                                Latest manuscript uploaded
                                {{ $latestManuscriptFile->created_at->format('d-m-Y') }}
                            </small>
                        @endif

                        <a href="#attachments" class="small text-decoration-underline">View all attachments</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
