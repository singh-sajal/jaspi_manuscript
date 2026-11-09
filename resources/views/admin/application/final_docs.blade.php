@extends('admin.app')
@section('title', 'New Manuscript Submission Step by Step')
@section('page-title', 'New Manuscript Submission Step by Step')
@section('breadcrumb-button')
    <a class="btn btn-sm btn-secondary" href="{{ url()->previous() }}">
        <i class="ri-arrow-go-back-line"></i>
    </a>
@endsection

@section('css')
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> --}}
    <style>
        body {
            background-color: #f8f9fa;

        }

        .table-dark {
            --bs-table-bg: #dee2e6;
        }

        .submission-wrapper {
            max-width: 1100px;
            margin: 50px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .sidebarr {
            background-color: #f1f3f5 !important;
            padding: 25px;
            border-right: 1px solid #dee2e6;
            min-height: 100%;
            width: 250px;
        }

        .step-item {
            display: flex;
            align-items: flex-start;
            padding: 12px 10px;
            margin-bottom: 10px;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.3s;
        }

        .step-item:hover {
            background-color: #e9ecef;
        }

        .step-icon {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            margin-right: 10px;
            background-color: #dee2e6;
            color: #6c757d;
        }

        .step-item.active .step-icon {
            background-color: #0d6efd;
            color: #fff;
        }

        .step-item.done .step-icon {
            background-color: #198754;
            color: #fff;
        }

        .step-item.pending .step-icon {
            background-color: #dee2e6;
            color: #6c757d;
        }

        .step-item.active {
            background-color: #e7f1ff;
            border-left: 4px solid #0d6efd;
        }

        .step-item.done {
            background-color: #d1e7dd;
            border-left: 4px solid #198754;
        }

        .form-section {
            /* display: none; */
            padding: 30px;
        }

        /* .form-section.active {
                                                                                                                                                                                                                                                                                                                                                                                                                                                display: block;
                                                                                                                                                                                                                                                                                                                                                                                                                                            } */

        .form-navigation {
            text-align: center;
            padding: 20px;
            border-top: 1px solid #dee2e6;
            background-color: #f8f9fa;
        }

        .form-navigation button {
            min-width: 120px;
            margin: 0 10px;
        }


        /* for left sidebar */
        .fixed-layout-row {
            height: 100vh;
            overflow: hidden;
            /* Disables the browser's main scrollbar */
        }

        /* Left Sidebar Styling */
        .sidebar-fixed {
            height: 100%;
            overflow-y: auto;
            /* Scroll internally if menu is too long */
            border-right: 1px solid #dee2e6;
            background-color: #f8f9fa;
            z-index: 10;
        }

        /* Right Content Styling */
        .content-scrollable {
            height: 100%;
            overflow-y: auto;
            /* The scrollbar appears here */
            padding-bottom: 50px;
            /* Extra padding for bottom spacing */
        }
    </style>
    <style>
        .stepPane {
            display: none;
        }

        .stepPane.active {
            display: block !important;
        }
    </style>



@endsection

@section('content')
    <div class="offcanvas offcanvas-end" tabindex="-1" style="z-index: 10000;width:80%" id="FileOffcanvas"
        data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="FileOffcanvasLabel"></div>
    <div class="modal" id="AjaxModal" tabindex="-1"></div>
    @php
        $savedArticles = !empty($app_info) && is_array($app_info->article_type) ? $app_info->article_type : [];
        $articles = [
            'Original article',
            'Systematic Review and Meta-Analysis',
            'Narrative Review',
            'Letter to Editor',
            'Brief Communication',
            'Infectious Diseases Cases/Vignette',
            'Editorial',
            'Stewardship Pearl',
            'Other type',
        ];
        $hasChecked = false;

        $article_checks = ['Plagiarism Check', 'Cross reference Check'];
        $savedArticleChecks = !empty($app_info) && !empty($app_info->article_check) ? $app_info->article_check : [];

        // $app_info= Application::where('uuid', $applicationId)->first() ?? [];

        $currentStatus = $app_info->status ?? '';
        $applicationId = $app_info->application_id ?? '';

        $attachment_categories = [
            'First Page',
            'Cover Letter',
            'Upload Manuscript',
            'Upload ICMJE Declaration',
            'Published with DOI'
            // 'Supporting File',
            // 'Manuscript Image',
        ];

        // if (!empty($app_info)) {
        //     $attachment_categories = $app_info->attachments->pluck('attachment_type')->toArray() ?? [];
        // } else {
        //     $attachment_categories = [
        //         'First Page',
        //         'Cover Letter',
        //         'Upload Manuscript',

        //     ];
        // }

    @endphp
    {{-- @dd($app_info) --}}
    {{-- @dd($attachment_categories) --}}
    {{-- <div class="submission-wrapper"> --}}
    <div class="row g-0 fixed-layout-row">
        @php
            // $isManuscriptSectionCompleted =
            //     !empty($app_info->title) &&
            //     !empty($app_info->author_affiliation) &&
            //     !empty($app_info->author_orcid_id) &&
            //     !empty($app_info->author_saspi_id);

            $isManuscriptSectionCompleted = 'true';

            $isDocumentsUploaded = $isManuscriptSectionCompleted;

            $isArticleInformationSectionCompleted = $isManuscriptSectionCompleted;

            $isCoAuthorSectionCompleted = $isManuscriptSectionCompleted;

            $isReviewSuggestionCompleted = $isManuscriptSectionCompleted;

        @endphp
        <!-- Left Sidebar -->

        <div class="col-md-3 sidebarr sidebar-fixed p-4">
            <h5 class="mb-4">Submission Steps</h5>
            <div id="stepList">
                <div class="step-item pending d-flex align-items-center" data-step="manuscript">
                    <div class="step-icon"><i class="ri-article-line"></i></div>
                    <div>
                        <strong>New Manuscript</strong>

                    </div>
                </div>
                <div class="step-item pending d-flex align-items-center" data-step="documents">
                    <div class="step-icon"><i class="ri-upload-2-line"></i></div>
                    <div>
                        <strong>Upload Document</strong>
                    </div>
                </div>
                <div class="step-item pending d-flex align-items-center" data-step="articleInformation">
                    <div class="step-icon"><i class="ri-article-line"></i></div>
                    <div>
                        <strong>Article Type</strong>
                    </div>
                </div>
                <div class="step-item pending d-flex align-items-center" data-step="coAuthors">
                    <div class="step-icon"><i class="ri-user-shared-line"></i></div>
                    <div>
                        <strong>Co-Authors</strong>
                    </div>
                </div>
                {{-- <div class="step-item pending d-flex align-items-center" data-step="reviewerSuggestions">
                    <div class="step-icon"><i class="ri-team-line"></i></div>
                    <div>
                        <strong>Reviewer Suggestions</strong>
                    </div>
                </div> --}}
                {{-- <div class="step-item pending d-flex align-items-center" data-step="preview">
                    <div class="step-icon"><i class="ri-check-double-line"></i></div>
                    <div>
                        <strong>Final Submission</strong>
                    </div>
                </div> --}}
            </div>
        </div>



        <!-- Right Content -->
        <div class="col-md-9 content-scrollable p-4">
            <div id="manuscriptForm" enctype="multipart/form-data">
                <div id="formContent">

                    {{-- STEP 1 – New Manuscript --}}
                    <div class="stepPane active" id="manuscript" data-is-completed="{{ $isManuscriptSectionCompleted }}">

                        <form action="{{ route('admin.application.store') }}" method="post" enctype="multipart/form-data"
                            novalidate>
                            @csrf
                            @method('POST')
                            <div class="form-section active" data-step="1">

                                <input type="hidden" name="uuid" value="{{ $app_info->uuid ?? '' }}">

                                <!-- Author Selection -->
                                <div class="mb-3">
                                    <label for="author_id" class="form-label required">Enter Author Name</label>

                                    {{-- NAME must be author_uuid to match your store logic --}}
                                    {{-- <select class="form-select" id="author_id" name="author_uuid" required>
                                        <option value="">--Select--</option>

                                        @foreach ($authors as $author)
                                            <option value="{{ $author->uuid }}"
                                                @if (old('author_uuid') == $author->uuid) selected
                                                @elseif(data_get($app_info, 'author_id') == $author->id) selected @endif>
                                                {{ $author->name }} ({{ $author->username }})
                                            </option>
                                        @endforeach

                                    </select> --}}

                                    <input type="text" class="form-control" name="author_name" value="{{ $app_info->author_name ?? '' }}">
                                </div>

                                <div class="mb-3">
                                    <label for="application_id" class="form-label required">Manuscript ID</label>
                                    {{-- @php
                                        // Get the value from old input or database
                                        $rawValue = old('application_id', data_get($app_info, 'application_id'));

                                        // If it looks like 'JASPI/12/2025', extract the number (12)
                                        // Assumes format: TEXT/NUMBER/YEAR
                                        if (Str::contains($rawValue, '/')) {
                                            $parts = explode('/', $rawValue);
                                            $displayValue =
                                                isset($parts[1]) && is_numeric($parts[1]) ? $parts[1] : $rawValue;
                                        } else {
                                            $displayValue = $rawValue;
                                        }
                                    @endphp --}}

                                    <input type="text" class="form-control" id="application_id" name="application_id"
                                        placeholder="Ex: JASPI/23/2025" required value="{{ $app_info->application_id ?? '' }}">
                                </div>


                                <!-- JASPI ID (Visible if Revised Submission) -->
                                {{-- <div class="mb-3" id="jaspi_id_field" style="display:none;">
                                    <label for="jaspi_id" class="form-label">JASPI ID</label>
                                    <select class="form-select" id="jaspi_id" name="jaspi_id">
                                        <option value="{{ $applicationId }}">{{ $applicationId }}</option>
                                    </select>
                                </div> --}}

                                <!-- Title -->
                                <div class="mb-3">
                                    <label for="title" class="form-label required">Title</label>
                                    <input type="text" class="form-control" id="title" name="title"
                                        placeholder="Enter application title" required value="{{ $app_info->title ?? '' }}">
                                </div>

                                <!-- Author Information -->
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="author_affiliation" class="form-label required">Author
                                            Affiliation</label>
                                        <input type="text" class="form-control" id="author_affiliation"
                                            name="author_affiliation" placeholder="Enter author affiliation" required
                                            value="{{ $app_info->author_affiliation ?? '' }}">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="author_orcid_id" class="form-label required">ORCID ID</label>
                                        <input type="number" class="form-control" id="author_orcid_id"
                                            name="author_orcid_id" placeholder="Enter ORCID ID" required
                                            value="{{ $app_info->author_orcid_id ?? '' }}">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="author_saspi_id" class="form-label">SASPI Membership
                                        Number</label>
                                    <input type="text" class="form-control" id="author_saspi_id"
                                        name="author_saspi_id" placeholder="Enter SASPI Membership Number"
                                        value="{{ $app_info->author_saspi_id ?? '' }}">
                                </div>
                                <div class="mb-3">
                                    <label for="doi_number" class="form-label required">DOI Number</label>
                                    <input type="text" class="form-control" id="doi_number"
                                        name="doi_number" placeholder="Enter DOI Number for Published Manuscript"
                                        value="{{ $app_info->published_doi ?? '' }}" required>
                                </div>
                                <div class="d-flex justify-content-around">
                                    {{-- <button type="button" class="btn btn-secondary " disabled>Back</button> --}}
                                    <button type="submit" class="btn btn-primary ">Save & Next</button>
                                </div>
                            </div>


                        </form>
                    </div>
                </div>


                {{-- Step 2 - Upload Documents --}}
                <div class="stepPane mt-5" id="documents" data-is-completed="{{ $isDocumentsUploaded }}">


                    <form action="{{ route('admin.application.documents') }}" method="post"
                        enctype="multipart/form-data" novalidate>
                        @csrf
                        @method('POST')
                        <input type="hidden" name="uuid" value="{{ $app_info->uuid ?? '' }}">

                        {{-- <div class="d-flex justify-content-end">
                            <button class="btn btn-sm btn-secondary mx-5 actionHandler"
                                data-url="{{ route('admin.application.add_document', [
                                    'type' => 'Supporting File',
                                    'app_id' => $app_info->id ?? 'na',
                                ]) }}">
                                <i class="ri-add-circle-line" style="font-size: large"></i> Add Supporting Document
                            </button>
                        </div> --}}

                        <div class="form-section" data-step="2">
                            <table class="table table-bordered table-striped align-middle text-center">
                                <thead class="table-primary">
                                    <tr>
                                        <th>SN</th>
                                        <th>File Name</th>
                                        <th>File Type</th>
                                        <th>Legend</th>
                                        {{-- <th>File Order</th> --}}
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>


                                    @php
                                        $attachments = $app_info->attachments ?? null;
                                        $c = 0;
                                    @endphp
                                    @foreach ($attachment_categories as $key => $category)
                                        @php
                                            if (!empty($attachments)) {
                                                $attachment_row = $attachments
                                                    ->where('attachment_type', $category)
                                                    ->first();
                                            } else {
                                                $attachment_row = null;
                                            }
                                            $c++;
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $category }}</td>
                                            <td>
                                                {{-- <select class="form-select form-select-sm">
                                                    <option value="">Select Type</option>
                                                    <option value="pdf">PDF</option>
                                                    <option value="doc">DOC</option>
                                                    <option value="docx">DOCX</option>
                                                </select> --}}
                                                {{ $attachment_row->file_type ?? 'NA' }}
                                            </td>
                                            <td>{{ $attachment_row->legend ?? 'NA' }}</td>
                                            <td>
                                                @if ($attachment_row != null)
                                                    <!-- View -->
                                                    <span class="actionHandler text-success me-2"
                                                        data-url="{{ route('file.viewframe') }}" data-frame="offcanvas"
                                                        data-file-url="{{ getFileUrl($attachment_row->file_url ?? '') }}"
                                                        data-handler="openFilePreview" title="View File">
                                                        <i class="ri-eye-line fs-5"></i>
                                                    </span>

                                                    <!-- Replace -->
                                                    <a href="#"
                                                        class="actionHandler text-primary mx-2 border-start ps-2 text-decoration-none"
                                                        data-url="{{ route('admin.application.file_upload', [
                                                            'id' => $attachment_row->id ?? 'na',
                                                            'type' => $category,
                                                            'legend' => $attachment_row->legend ?? 'na',
                                                            'app_id' => $app_info->id ?? 'na',
                                                        ]) }}"
                                                        title="Replace File">
                                                        <i class="ri-file-upload-line fs-5"></i>
                                                    </a>

                                                    <!-- Delete -->
                                                    <a href="#"
                                                        class="actionHandler text-danger ms-2 border-start ps-2 text-decoration-none"
                                                        data-action="delete"
                                                        data-url="{{ route('admin.application.file_destroy', [
                                                            'app_id' => $app_info->uuid,
                                                            'id' => $attachment_row->id ?? 'na',
                                                        ]) }}"
                                                        title="Delete File">
                                                        <i class="ri-delete-bin-line fs-5"></i>
                                                    </a>
                                                @else
                                                    <!-- Upload -->
                                                    <a href="#" class="actionHandler text-info text-decoration-none"
                                                        data-url="{{ route('admin.application.file_upload', [
                                                            'id' => 'na',
                                                            'type' => $category,
                                                            'app_id' => $app_info->id ?? 'na',
                                                            'legend' => $attachment_row->legend ?? 'na',
                                                        ]) }}"
                                                        title="Upload File">
                                                        <i class="ri-upload-2-line fs-5"></i>
                                                    </a>
                                                @endif
                                            </td>

                                        </tr>
                                    @endforeach
                                    @php
                                        $attachments = !empty($attachments)
                                            ? $attachments->whereNotIn('attachment_type', $attachment_categories)
                                            : null;
                                    @endphp
                                    @if (!empty($attachments))
                                        @foreach ($attachments as $key => $docs)
                                            <tr>
                                                <td>{{ $loop->iteration + 4 }}</td>
                                                <td>{{ $docs->attachment_type }}</td>
                                                <td>
                                                    {{-- <select class="form-select form-select-sm">
                                                    <option value="">Select Type</option>
                                                    <option value="pdf">PDF</option>
                                                    <option value="doc">DOC</option>
                                                    <option value="docx">DOCX</option>
                                                </select> --}}
                                                    {{ $docs->file_type ?? 'NA' }}
                                                </td>
                                                <td>{{ $docs->legend ?? 'NA' }}</td>
                                                <td>
                                                    {{-- @if ($docs != null) --}}
                                                    <!-- View -->
                                                    <span class="actionHandler text-success me-2"
                                                        data-url="{{ route('file.viewframe') }}" data-frame="offcanvas"
                                                        data-file-url="{{ getFileUrl($docs->file_url ?? '') }}"
                                                        data-handler="openFilePreview" title="View File">
                                                        <i class="ri-eye-line fs-5"></i>
                                                    </span>

                                                    <!-- Replace -->
                                                    <a href="#"
                                                        class="actionHandler text-primary mx-2 border-start ps-2 text-decoration-none"
                                                        data-url="{{ route('admin.application.file_upload', [
                                                            'id' => $docs->id ?? 'na',
                                                            'type' => $docs->attachment_type ?? 'na',
                                                            'legend' => $docs->legend ?? 'na',
                                                            'app_id' => $app_info->id ?? 'na',
                                                        ]) }}"
                                                        title="Replace File">
                                                        <i class="ri-file-upload-line fs-5"></i>
                                                    </a>

                                                    <!-- Delete -->
                                                    <a href="#"
                                                        class="actionHandler text-danger ms-2 border-start ps-2 text-decoration-none"
                                                        data-action="delete"
                                                        data-url="{{ route('admin.application.file_destroy', [
                                                            'app_id' => $app_info->uuid,
                                                            'id' => $docs->id ?? 'na',
                                                        ]) }}"
                                                        title="Delete File">
                                                        <i class="ri-delete-bin-line fs-5"></i>
                                                    </a>
                                                    {{-- @else --}}
                                                    <!-- Upload -->
                                                    {{-- <a href="#"
                                                            class="actionHandler text-info text-decoration-none"
                                                            data-url="{{ route('admin.application.file_upload', [
                                                                'id' => 'na',
                                                                'type' => $category,
                                                                'app_id' => $app_info->id ?? 'na',
                                                                'legend' => $docs->legend ?? 'na',
                                                            ]) }}"
                                                            title="Upload File">
                                                            <i class="ri-upload-2-line fs-5"></i>
                                                        </a> --}}
                                                    {{-- @endif --}}
                                                </td>

                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                            <div class="d-flex justify-content-around mt-2">
                                {{-- <button type="button" class="btn btn-secondary " disabled>Back</button> --}}
                                <button type="submit" class="btn btn-primary ">Save & Next</button>
                            </div>
                        </div>

                    </form>

                </div>

                {{-- STEP 3 – Article Information --}}
                <div class="stepPane" id="articleInformation"
                    data-is-completed="{{ $isArticleInformationSectionCompleted }}">


                    <form action="{{ route('admin.application.articleInformation') }}" method="post"
                        enctype="multipart/form-data" novalidate>
                        @csrf
                        @method('POST')
                        <input type="hidden" name="uuid" value="{{ $app_info->uuid ?? '' }}">

                        <div class="form-section" data-step="3">
                            <div class="row">
                                <!-- Left: Article Type -->
                                <div class="col-12 mb-3">
                                    <div class="card h-100">
                                        <div class="card-header">
                                            <h4 class="card-title mb-0">Article Type</h4>
                                        </div>
                                        <div class="card-body">
                                            <input type="checkbox" name="atLeastOne" id="atLeastOne"
                                                {{ $hasChecked ? '' : 'required' }}
                                                data-error-message="At least one option is required" style="display:none">
                                            <div class="row">
                                                @foreach ($articles as $index => $article)
                                                    <div class="col-6">
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input article-checkbox"
                                                                type="radio" name="article_type"
                                                                value="{{ $article }}"
                                                                id="article_{{ $index }}"
                                                                {{ in_array($article, $savedArticles) ? 'checked' : '' }}
                                                                onchange="toggleRequired()">
                                                            <label class="form-check-label"
                                                                for="article_{{ $index }}">
                                                                {{ $article }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach

                                            </div>

                                            <!-- Other Article Type Input -->
                                            @if (!empty($savedArticles['other_data']))
                                                <div id="other_article_type_container" class="mt-2">
                                                    <input type="text" class="form-control" id="article_type_other"
                                                        name="article_type_other" placeholder="Enter other article type"
                                                        value="{{ $savedArticles['other_data'] }}">
                                                </div>
                                            @else
                                                <div id="other_article_type_container" class="mt-2"
                                                    style="display:none;">
                                                    <input type="text" class="form-control" id="article_type_other"
                                                        name="article_type_other" placeholder="Enter other article type">
                                                </div>
                                            @endif

                                            <span class="text-danger mt-2 d-block" data-error="atLeastOne"></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right: Article Checked -->
                                <div class="col-12 mb-3">
                                    <div class="card h-100">
                                        <div class="card-header">
                                            <h4 class="card-title mb-0">Article Checked</h4>
                                        </div>

                                        <div class="card-body">
                                            <div class="d-flex">
                                                @foreach ($article_checks as $article_check)
                                                    <div class="col form-check">
                                                        <input class="form-check-input article-checkbox" type="checkbox"
                                                            name="article_check[]" value='{{ $article_check }}'
                                                            id='article_{{ $index }}'
                                                            {{ in_array($article_check, $savedArticleChecks) ? 'checked' : '' }}>
                                                        <label class="form-check-label"
                                                            for="article_check{{ $index }}">{{ $article_check }}</label>
                                                    </div>
                                                @endforeach
                                                {{-- <span class="text-danger mt-2" data-error="atLeastOne"></span> --}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Description Section -->
                            <div class="col-12 mb-3">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title mb-0">Description</h4>
                                    </div>
                                    <div class="card-body">
                                        <div id="editor" style="height:230px !important;">
                                            {!! $app_info->description ?? '' !!}
                                        </div>
                                        <input id="description" name="description" type="hidden">
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-around mt-2">
                                {{-- <button type="button" class="btn btn-secondary " disabled>Back</button> --}}
                                <button type="submit" class="btn btn-primary ">Save & Next</button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Step 4 -->
                <div class="stepPane" id="coAuthors" data-is-completed="{{ $isCoAuthorSectionCompleted }}">
                    <form action="{{ route('admin.application.coAuthors') }}" method="post"
                        enctype="multipart/form-data">
                        @csrf
                        @method('POST')
                        <input type="hidden" name="uuid" value="{{ $app_info->uuid ?? '' }}">

                        <div class="form-section" data-step="4">

                            {{-- 1. Top Right "Add" Button --}}
                            <div class="d-flex justify-content-end mb-3">
                                <button type="button" class="btn btn-dark btn-sm actionHandler"
                                    data-url="{{ route('admin.application.co_author', $app_info->uuid ?? '') }}"
                                    title="Add Co-Author"
                                    style="background-color: black; border-color: black; opacity: 1;">
                                    {{-- Added Style Here --}}
                                    <i class="ri-add-line fs-5"></i>
                                </button>
                            </div>

                            {{-- 2. The Table --}}
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead style="background-color: #f8f9fa;">
                                        <tr>
                                            <th class="fw-bold">Name</th>
                                            <th class="fw-bold">Email</th>
                                            <th class="fw-bold">Phone</th>
                                            <th class="fw-bold">Affiliation</th>
                                            <th class="fw-bold">ORCID</th>
                                            <th class="fw-bold">SASPI</th>
                                            <th class="fw-bold text-center" style="width: 100px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white">
                                        @if (empty($app_info->co_author_data))
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">
                                                    No co-authors added yet.
                                                </td>
                                            </tr>
                                        @else
                                            @foreach ($app_info->co_author_data ?? [] as $coAuthor)
                                                <tr id="co-author-{{ $coAuthor['coauthor_id'] }}">

                                                    {{-- Data Columns mapped to your variables --}}
                                                    <td>{{ $coAuthor['coauthor_name'] }}</td>
                                                    <td class="text-muted">{{ $coAuthor['coauthor_email'] }}</td>
                                                    <td>{{ $coAuthor['coauthor_phone'] }}</td>
                                                    <td>{{ $coAuthor['coauthor_affiliation'] ?? '-' }}</td>
                                                    <td>{{ $coAuthor['coauthor_orcid_id'] ?? '-' }}</td>
                                                    <td>{{ $coAuthor['coauthor_saspi_id'] ?? '-' }}</td>

                                                    {{-- Action Buttons --}}
                                                    <td class="text-center">
                                                        <a href="javascript:void(0);" class="text-info me-2 actionHandler"
                                                            data-url="{{ route('admin.application.co_author_update', [
                                                                'uuid' => $app_info->uuid,
                                                                'author_id' => $coAuthor['coauthor_id'],
                                                            ]) }}"
                                                            title="Edit">
                                                            <i class="ri-edit-box-line fs-5"></i>
                                                            {{-- Blue Edit Icon --}}
                                                        </a>

                                                        <a href="javascript:void(0);" class="text-danger actionHandler"
                                                            data-action="delete"
                                                            data-url="{{ route('admin.application.co_author_delete', [
                                                                'uuid' => $app_info->uuid,
                                                                'author_id' => $coAuthor['coauthor_id'],
                                                            ]) }}"
                                                            title="Delete">
                                                            <i class="ri-delete-bin-line fs-5"></i>
                                                            {{-- Red Trash Icon --}}
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                            {{-- 3. Center "Save & Next" Button --}}
                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary px-4">Publish Manuscript</button>
                            </div>

                        </div>
                    </form>
                </div>

                <!-- Step 5 -->
                {{-- <div class="stepPane" id="reviewerSuggestions" data-is-completed="{{ $isReviewSuggestionCompleted }}">


                    <form action="{{ route('admin.application.reviewerSuggestions') }}" method="post"
                        enctype="multipart/form-data">
                        @csrf
                        @method('POST')
                        <input type="hidden" name="uuid" value="{{ $app_info->uuid ?? '' }}">

                        <div class="form-section" data-step="5">
                            <div class="d-flex justify-content-end mb-2">
                                <button class="btn btn-sm btn-secondary actionHandler"
                                    data-url="{{ route('admin.application.reviewer', $app_info->uuid ?? '') }}">
                                    <i class="ri-add-circle-line" style="font-size: large"></i>
                                </button>
                            </div>

                            <div class="container mt-2">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped align-middle">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Title</th>
                                                <th>First Name</th>
                                                <th>Last Name</th>
                                                <th>Email</th>
                                                <th>Institution</th>
                                                <th>Specialization</th>
                                                <th>Reason (Why this person)</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($app_info->data['reviewer_suggestions'] ?? [] as $reviewer)
                                                <tr>
                                                    <td>{{ $reviewer['title'] ?? '-' }}</td>
                                                    <td>{{ $reviewer['first_name'] ?? '-' }}</td>
                                                    <td>{{ $reviewer['last_name'] ?? '-' }}</td>
                                                    <td>{{ $reviewer['email'] ?? '-' }}</td>
                                                    <td>{{ $reviewer['institution'] ?? '-' }}</td>
                                                    <td>{{ $reviewer['specialization'] ?? '-' }}</td>
                                                    <td>{{ $reviewer['reason'] ?? '-' }}</td>


                                                    <td class="text-center">
                                                        <div class="brg-group btn-group-sm">

                                                            <a class="text-info border-end actionHandler px-1"
                                                                data-url="{{ route('admin.application.reviewer_update', [
                                                                    'uuid' => $app_info->uuid,
                                                                    'reviewer_id' => $reviewer['reviewer_id'],
                                                                ]) }}">
                                                                <i class="ri-edit-box-line" style="font-size: large"></i>
                                                            </a>
                                                            <a class="fs-12 text-danger btn-danger actionHandler px-1"
                                                                data-action="delete"
                                                                data-url="{{ route('admin.application.reviewer_delete', [
                                                                    'uuid' => $app_info->uuid,
                                                                    'reviewer_id' => $reviewer['reviewer_id'],
                                                                ]) }}">
                                                                <i class="ri-delete-bin-line"
                                                                    style="font-size: large"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center text-muted">
                                                        No reviewer suggestions added yet.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>

                                    </table>
                                </div>
                            </div>
                            <div class="d-flex justify-content-around mt-2">
                                <button type="submit" class="btn btn-primary ">Save & Next</button>
                            </div>
                        </div>
                    </form>

                </div> --}}

                <!-- Step 6 -->
                {{-- <div class="stepPane" id="preview" data-is-completed="">
                    <form action="{{ route('admin.application.preview') }}" method="post"
                        enctype="multipart/form-data">
                        @csrf
                        @method('POST')
                        <input type="hidden" name="uuid" value="{{ $app_info->uuid ?? '' }}">

                        <div class="form-section" data-step="6">
                            <h4 class="mb-4">Final Review & Submission</h4>

                            @php
                                $allStepsCompleted = false;

                                // Check if the first section is initialized to avoid errors
                                if (isset($isManuscriptSectionCompleted) && $isManuscriptSectionCompleted == 'true') {
                                    // STRUCTURE: 'Step Name' => ['completed' => boolean, 'mandatory' => boolean]
                                    $steps = [
                                        'Step 1: Manuscript' => [
                                            'completed' => $isManuscriptSectionCompleted == 'true',
                                            'mandatory' => true,
                                        ],
                                        'Step 2: File Uploads' => [
                                            'completed' => 'true' ?? false,
                                            'mandatory' => true,
                                        ],
                                        'Step 3: Article Type & Title' => [
                                            'completed' => !empty($app_info->article_type),
                                            'mandatory' => true,
                                        ],
                                        'Step 4: Co-Authors' => [
                                            'completed' => !empty($app_info->co_author_data),
                                            'mandatory' => false, // Set to false for Optional
                                        ],
                                        'Step 5: Reviewer Suggestions' => [
                                            'completed' => !empty($app_info->data['reviewer_suggestions']),
                                            'mandatory' => false, // Set to false for Optional
                                        ],
                                    ];

                                    // CALCULATION: The form is ready IF there are NO mandatory steps that are incomplete.
                                    // We filter the array to find any step where 'mandatory' is true AND 'completed' is false.
                                    $incompleteMandatorySteps = array_filter($steps, function ($step) {
                                        return $step['mandatory'] === true && $step['completed'] === false;
                                    });

                                    $allStepsCompleted = empty($incompleteMandatorySteps);
                                }
                            @endphp

                            @if (isset($isManuscriptSectionCompleted) && $isManuscriptSectionCompleted == 'true')
                                <div class="card mb-4">
                                    <div class="card-header bg-secondary text-white">
                                        <i class="ri-list-check"></i> Submission Summary
                                    </div>
                                    <div class="card-body p-0">
                                        <ul class="list-group list-group-flush">
                                            @foreach ($steps as $stepName => $data)
                                                <li
                                                    class="list-group-item d-flex justify-content-between align-items-center">
                                                    <span class="fw-medium">
                                                        {{ $stepName }}
                                                    </span>

                                                    @if ($data['completed'])
                                                        <span class="badge bg-success rounded-pill">
                                                            <i class="ri-check-double-line"></i> Done
                                                        </span>
                                                    @elseif($data['mandatory'])
                                                        <span class="badge bg-danger rounded-pill">
                                                            <i class="ri-error-warning-line"></i> Pending
                                                        </span>
                                                    @else
                                                        <span class="badge bg-light text-dark border rounded-pill">
                                                            <i class="ri-information-line"></i> Not Mandatory
                                                        </span>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @endif


                            <div class="card mb-4">
                                <div class="card-header bg-dark text-white">
                                    1. Declaration of Conflict of Interest
                                </div>
                                <div class="card-body">

                                    @php
                                        $savedData = $app_info->data ?? [];
                                        $conflictVal = $savedData['conflict_interest'] ?? ''; // 'yes' or 'no'
                                        $copyrightVal = $savedData['copyright_agree'] ?? false; // true or false
                                    @endphp

                                    <p class="mb-2">Are you aware of any potential conflicts of interest between any
                                        of the authors and this journal?</p>

                                    <label class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="conflict_interest"
                                            value="yes" {{ $conflictVal == 'yes' ? 'checked' : '' }}>
                                        <span class="form-check-label">Yes</span>
                                    </label>

                                    <label class="form-check">
                                        <input class="form-check-input" type="radio" name="conflict_interest"
                                            value="no" {{ $conflictVal == 'no' ? 'checked' : '' }}>
                                        <span class="form-check-label">No</span>
                                    </label>
                                </div>
                            </div>

                            <div class="card mb-4">
                                <div class="card-header bg-dark text-white">
                                    2. Copyright and Submission Agreement
                                </div>
                                <div class="card-body">
                                    <p>By proceeding with submission, you confirm that...</p>

                                    <label class="form-check mt-3">
                                        <input class="form-check-input" type="checkbox" name="copyright_agree"
                                            id="copyrightCheck" {{ $copyrightVal ? 'checked' : '' }}>
                                        <span class="form-check-label fw-bold">I Agree</span>
                                    </label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                @if (!empty($app_info) && ($app_info->status == 'incomplete' || $app_info->status == 'revise'))
                                    <button type="submit" class="btn btn-primary" id="finalSubmitBtn"
                                        data-steps-completed="{{ $allStepsCompleted ? 'true' : 'false' }}">
                                        Final Submit
                                    </button>
                                @endif
                            </div>

                        </div>
                    </form>
                </div> --}}
            </div>
        </div>
    </div>
    </div>
    {{-- </div> --}}
@endsection

@section('javascripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>



    <script>
        function openStepPane(stepItem) {
            const currentUrl = new URL(window.location.href);
            const dataStep = stepItem.dataset.step;

            // Get current and previous step panes to check permissions
            const currentStepPane = document.getElementById(`${dataStep}`);
            const previousStepPane = currentStepPane.previousElementSibling;

            // --- VALIDATION LOGIC (Keep this to prevent jumping ahead illegally) ---
            let canSwitch = false;

            if (previousStepPane) {
                // Allow switch only if previous step is explicitly marked "true"
                // OR if the user is clicking a step that is already "done"
                const isPreviousStepCompleted = previousStepPane.dataset.isCompleted === "true";
                const isTargetStepCompleted = currentStepPane.dataset.isCompleted === "true";

                if (isPreviousStepCompleted || isTargetStepCompleted) {
                    canSwitch = true;
                }
            } else {
                // Always allow switching to the first step
                canSwitch = true;
            }

            if (canSwitch) {
                // 1. UPDATE URL
                currentUrl.searchParams.set('step', dataStep);
                window.history.replaceState({}, '', currentUrl.href);

                // 2. UPDATE CONTENT PANES (Show/Hide)
                document.querySelectorAll(".stepPane").forEach(step => {
                    step.classList.remove('active');
                });
                currentStepPane.classList.add('active');

                // 3. UPDATE SIDEBAR UI (Colors/Classes)
                updateSidebarUI(dataStep);
            }
        }

        // Helper function to refresh the sidebar look
        function updateSidebarUI(activeStepName) {
            const allSidebarItems = document.querySelectorAll(".step-item");

            allSidebarItems.forEach(item => {
                const itemStepName = item.dataset.step;

                // 1. RESET: Remove 'done' so it never turns green
                item.classList.remove('done');
                item.classList.remove('active'); // Remove active initially

                // 2. APPLY LOGIC: Active vs Pending
                if (itemStepName === activeStepName) {
                    // SELECTED: Active, remove Pending
                    item.classList.add('active');
                    item.classList.remove('pending');
                } else {
                    // NOT SELECTED: Pending, remove Active
                    // (This applies pending even if the step is technically complete)
                    item.classList.add('pending');
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Event delegation for sidebar clicks
            document.addEventListener('click', (e) => {
                let target = e.target;
                if (target.matches(".step-item, .step-item *")) {
                    target = target.closest(".step-item");
                    openStepPane(target);
                    return;
                }
            });

            // Handle Initial Load
            const url = new URL(window.location.href);
            const step = url.searchParams.get('step');

            // If URL has a step, load it. Otherwise default to the first one (manuscript)
            if (step) {
                const stepItem = document.querySelector(`.step-item[data-step="${step}"]`);
                if (stepItem) {
                    openStepPane(stepItem);
                }
            } else {
                // Initialize UI for default view (step 1)
                updateSidebarUI('manuscript');
            }
        });
    </script>



    {{-- step 1 --}}
    <script>
        function toggleJaspiIdField() {
            const submissionType = document.getElementById('submission_type').value;
            const jaspiIdField = document.getElementById('jaspi_id_field');
            jaspiIdField.style.display = submissionType === 'revised_submission' ? 'block' : 'none';
            jaspiIdField.querySelector('label').classList.toggle('required', submissionType === 'revised_submission');
            jaspiIdField.querySelector('select').required = submissionType === 'revised_submission';
        }

        function toggleRequired() {
            const checkboxes = document.querySelectorAll('.article-checkbox');
            const hiddenRequired = document.getElementById('atLeastOne');
            const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
            hiddenRequired.required = !anyChecked;
        }

        // Run this when the page loads (important for edit mode)
        document.addEventListener('DOMContentLoaded', toggleRequired);
    </script>
    {{-- step 3 --}}
    <script>
        function toggleRequired() {
            const checkboxes = document.querySelectorAll('.article-checkbox');
            const atLeastOneCheckbox = document.getElementById('atLeastOne');
            const otherContainer = document.getElementById('other_article_type_container');
            const otherCheckbox = Array.from(checkboxes).find(cb => cb.value === 'Other type');
            const isAnyChecked = Array.from(checkboxes).some(cb => cb.checked);

            // Toggle required attribute for atLeastOne
            atLeastOneCheckbox.required = !isAnyChecked;

            // Show/hide the "Other" input field
            if (otherCheckbox && otherCheckbox.checked) {
                otherContainer.style.display = 'block';
                document.getElementById('article_type_other').required = true;
            } else {
                otherContainer.style.display = 'none';
                document.getElementById('article_type_other').required = false;
                document.getElementById('article_type_other').value = '';
            }

            // Update error message
            const errorSpan = document.querySelector('[data-error="atLeastOne"]');
            errorSpan.textContent = isAnyChecked ? '' : atLeastOneCheckbox.dataset.errorMessage;
        }
    </script>

    {{-- for step 6  --}}
    {{-- <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Selectors
            const submitBtn = document.getElementById('finalSubmitBtn');
            const conflictRadios = document.querySelectorAll('input[name="conflict_interest"]');
            const agreeCheckbox = document.getElementById(
                'copyrightCheck'); // Make sure your checkbox has id="copyrightCheck"

            // Debugging: Check if elements exist
            if (!submitBtn) {
                console.error("Submit button not found! Check ID 'finalSubmitBtn'");
                return;
            }
            if (!agreeCheckbox) {
                console.error("Checkbox not found! Check ID 'copyrightCheck'");
                return;
            }

            function toggleSubmit() {
                // 1. Get PHP Status
                const phpStepsDone = submitBtn.getAttribute('data-steps-completed') === 'true';

                // 2. Get User Input Status
                const conflictSelected = Array.from(conflictRadios).some(r => r.checked);
                const agreeChecked = agreeCheckbox.checked;

                console.log("--- Validation Check ---");
                console.log("PHP Steps Completed:", phpStepsDone);
                console.log("Conflict Selected:", conflictSelected);
                console.log("Agree Checked:", agreeChecked);

                // 3. Logic: All must be true
                if (phpStepsDone && conflictSelected && agreeChecked) {
                    submitBtn.disabled = false;
                    submitBtn.title = "Ready to submit";
                    console.log("RESULT: Button Enabled");
                } else {
                    submitBtn.disabled = true;
                    if (!phpStepsDone) submitBtn.title = "Previous steps are incomplete";
                    else submitBtn.title = "Please agree to the declaration";
                    console.log("RESULT: Button Disabled");
                }
            }

            // Listeners
            conflictRadios.forEach(r => r.addEventListener('change', toggleSubmit));
            agreeCheckbox.addEventListener('change', toggleSubmit);

            // Run on load
            toggleSubmit();
        });
    </script> --}}

    <script src="{{ asset('engines/fileviewer.js') }}"></script>


@endsection
