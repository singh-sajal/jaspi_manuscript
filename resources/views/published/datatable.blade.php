@forelse ($applications as $application)
    @php
        // use App\Models\ApplicationTimeline;

        $timeline = \App\Models\ApplicationTimeline::where('application_id', $application->id)
            ->whereIn('status', ['submitted', 'resubmitted'])
            ->orderBy('id', 'desc')
            ->first();

        // Find the specific attachment from the collection
        // Get the URL if the document exists, otherwise return an empty string
        $cover_letter_Doc = $application->attachments->firstWhere('attachment_type', 'Cover Letter');
        $cover_letter_Url = $cover_letter_Doc ? getFileUrl($cover_letter_Doc->file_url) : '';

        $first_page_Doc = $application->attachments->firstWhere('attachment_type', 'First Page');
        $first_page_Url = $first_page_Doc ? getFileUrl($first_page_Doc->file_url) : '';

        $declaration_Doc = $application->attachments->firstWhere('attachment_type', 'Upload ICMJE Declaration');
        $declaration_Url = $declaration_Doc ? getFileUrl($declaration_Doc->file_url) : '';

        $manuscript_Doc = $application->attachments->firstWhere('attachment_type', 'Upload Manuscript');
        $manuscript_Url = $manuscript_Doc ? getFileUrl($manuscript_Doc->file_url) : '';

        $published_Doc = $application->attachments->firstWhere('attachment_type', 'Published with DOI');
        $published_Url = $published_Doc ? getFileUrl($published_Doc->file_url) : '';

    @endphp
    <tr>
        <td>{{ $loop->iteration }}</td>
        <td>
            <div class="d-flex align-items-center">


                <a class="d-flex flex-column" href="{{ route('admin.application.show', $application->uuid ?? '') }}">
                    <span class="text-dark fw-bold">{{ Str::limit($application->title, 20, '...') }}</span>
                    <span class="text-muted">{{ $application->application_id ?? '---' }}</span>
                </a>
            </div>
        </td>

        <td>
            <div class="ms-2 my-3">
                    <span class="fs-12 text-dark text-decoration-underline actionHandler px-1 text-center"
                        data-url="{{ route('file.viewframe') }}" data-frame="offcanvas"
                        data-file-url="{{ $first_page_Url }}" data-handler="openFilePreview" target="_blank"
                        title="View Uploaded File">
                        First Page
                    </span>
                </div>
        </td>
        <td>
            <div class="ms-2 my-3">
                    <span class="fs-12 text-dark text-decoration-underline actionHandler px-1 text-center"
                        data-url="{{ route('file.viewframe') }}" data-frame="offcanvas"
                        data-file-url="{{ $declaration_Url }}" data-handler="openFilePreview" target="_blank"
                        title="View Uploaded File">
                        ICJME Declaration
                    </span>
                </div>
        </td>
        <td>
            <div class="ms-2 my-3">
                    <span class="fs-12 text-dark text-decoration-underline actionHandler px-1 text-center"
                        data-url="{{ route('file.viewframe') }}" data-frame="offcanvas"
                        data-file-url="{{ $cover_letter_Url }}" data-handler="openFilePreview" target="_blank"
                        title="View Uploaded File">
                        Cover Letter
                    </span>
                </div>
        </td>
        <td>
            <div class="ms-2 my-3">
                    <span class="fs-12 text-dark text-decoration-underline actionHandler px-1 text-center"
                        data-url="{{ route('file.viewframe') }}" data-frame="offcanvas"
                        data-file-url="{{ $manuscript_Url }}" data-handler="openFilePreview" target="_blank"
                        title="View Uploaded File">
                        Manuscript
                    </span>
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
        <td>
                <div class="d-flex align-items-center">
                    <span>{{ $application->author->name ?? ($application->author_name ?? '---') }}</span>
                    <i class="ri-checkbox-circle-fill text-info ms-1"></i>
                </div>
            </td>
            <td>
            <div class="ms-2 my-3">
                @php $doiUrl = doiLinkUrl($application->published_doi); @endphp
                @if ($doiUrl)
                    <a href="{{ $doiUrl }}" target="_blank" rel="noopener noreferrer"
                        class="text-primary text-decoration-underline"
                        onclick="window.open('{{ $doiUrl }}', '_blank').focus(); return false;">
                        {{ $application->published_doi }}
                    </a>
                @elseif ($application->published_doi)
                    {{-- Stored value is not a DOI, so it is not rendered as a link. --}}
                    <span>{{ $application->published_doi }}</span>
                @else
                    <span>---</span>
                @endif
                    <span class="fs-12 text-dark text-decoration-underline actionHandler px-1 text-center"
                        data-url="{{ route('file.viewframe') }}" data-frame="offcanvas"
                        data-file-url="{{ $published_Url }}" data-handler="openFilePreview" target="_blank"
                        title="View Uploaded File">
                        <i class="ri-eye-line"></i>
                    </span>
                </div>
        </td>
        {{-- <td>
            @php
                if ($application->submission_type == 'new_submission') {
                    $record = 'New Submission';
                } else {
                    $record = 'Revised Submission';
                }

            @endphp
            <div class="d-flex align-items-center">
                <span>{{ $record ?? '---' }}</span>
            </div>
        </td> --}}
        {{-- <td>
            <div class="d-flex align-items-center">
                <ul>
                    @foreach ($application->article_type ?? [] as $articalType)
                        <li>-{{ $articalType }}</li>
                    @endforeach
                </ul>
            </div>
        </td> --}}
        {{-- @if (authUser('admin')->is_admin == 1) --}}

        {{-- @endif --}}
        {{-- @if (authUser('admin')->is_admin == 1 || isEditor())
            <td>
                <div class="d-flex align-items-center {{ authUser('admin')->can('application.assign') && $application->status == 'technical checks approved' ? 'actionHandler' : '' }}"
                    data-url="{{ route('admin.application.assign', $application->uuid) }}">

                    <img class="avatar avatar-sm me-2"
                        src="{{ asset($application->assignee->avatar ?? 'admin/assets/images/avatar/1.png') }}"
                        alt="avatar">


                    <span class="text-dark fw-bold">{{ $application->assignee->name ?? '---' }}</span>



                </div>
            </td>
        @endif --}}

        {{-- <td>
            <span class="badge badge-sm badge-rounded badge-{{ $statusColorMap[$application->status] ?? 'primary' }}">
                {{ Str::ucfirst($application->status ?? '---') }}
            </span>
            @if ($application->status == 'published')
                <div class="ms-2 my-3">
                    <span class="fs-12 text-dark text-decoration-underline actionHandler px-1 text-center"
                        data-url="{{ route('file.viewframe') }}" data-frame="offcanvas"
                        data-file-url="{{ getFileUrl($application->data['file_url'] ?? '') }}"
                        data-handler="openFilePreview" target="_blank" title="View Uploaded File">
                        View
                    </span>
                </div>
            @endif
            @if ($application->status == 'published')
                @php
                    $publishedDoc = $application->attachments->firstWhere('attachment_type', 'Published with DOI');
                    $docUrl = $publishedDoc ? getFileUrl($publishedDoc->file_url) : '';
                @endphp

                <div class="ms-2 my-3">
                    <span class="fs-12 text-dark text-decoration-underline actionHandler px-1 text-center"
                        data-url="{{ route('file.viewframe') }}" data-frame="offcanvas"
                        data-file-url="{{ $docUrl }}" data-handler="openFilePreview" target="_blank"
                        title="View Uploaded File">
                        View
                    </span>
                </div>
            @endif
        </td> --}}
        {{-- <td>{{ $application->created_at->format('d-M-Y, h:i') }}</td> --}}

        {{-- <td>
            <div class="btn-group btn-group-sm">
                <button class="btn btn-sm btn-danger actionHandler"
                    {{ isSuperAdmin() ? ($application->status != 'submitted' ? 'disabled' : '') : 'disabled' }}
                    data-action="delete" data-url="{{ route('admin.application.destroy', $application->uuid) }}"> <i
                        class="ri-delete-bin-line"></i></button>
            </div>
        </td> --}}

    </tr>
@empty
    <tr>
        <td colspan="9" class="p-4 text-center">No Data Available !</td>
    </tr>
@endforelse
