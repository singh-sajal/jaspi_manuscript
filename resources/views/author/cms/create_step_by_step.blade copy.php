@extends('author.app')
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

        .sidebar {
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
        /* .fixed-layout-row {
                                                                                                                                                                                                                                    height: 100vh;
                                                                                                                                                                                                                                    overflow: hidden;
                                                                                                                                                                                                                                } */

        /* Left Sidebar Styling */
        .sidebar-fixed {
            height: calc(100vh - 120px);
            /* header + breadcrumb */
            overflow-y: auto;
            border-right: 1px solid #dee2e6;
            background-color: #f8f9fa;
            z-index: 10;

            position: sticky;
            top: 100px;
            /* below header */
            height: calc(100vh - 100px);
        }

        /* Right Content Styling */
        /* .content-scrollable {
                                                                                                                                                                                                                                    height: 100%;
                                                                                                                                                                                                                                    overflow-y: auto;
                                                                                                                                                                                                                                    padding-bottom: 50px;
                                                                                                                                                                                                                                } */
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

        if (!empty($app_info)) {
            $status = $app_info->status;
        } else {
            $status = '';
        }

        // 1. Get the array. If it's null, make it empty.
$savedArticles = !empty($app_info) && is_array($app_info->article_type) ? $app_info->article_type : [];

// 2. Extract the "Other" text
// Logic: If "Other type" is selected, the text is the item in the array that is NOT "Other type".
$otherValue = '';
if (in_array('Other type', $savedArticles)) {
    // Remove "Other type" from the array
    $filtered = array_diff($savedArticles, ['Other type']);
    // The remaining item is your text ("this is text")
    $otherValue = reset($filtered);
}

$articles = [
    'Original article',
    'Systematic Review and Meta-Analysis',
    'Narrative Review',
    'Letter to Editor',
    'Brief Communication',
    'Infectious Diseases Cases/Vignette',
    'Editorial',
    'Other type',
];

$article_checks = ['Plagiarism Check', 'Cross reference Check'];
$savedArticleChecks = !empty($app_info) && !empty($app_info->article_check) ? $app_info->article_check : [];
$currentStatus = $app_info->status ?? '';
$applicationId = $app_info->application_id ?? '';

$attachment_categories = [
    'First Page',
    'Cover Letter',
    // 'Upload Manuscript',
    'Upload ICMJE Declaration',
    // 'Supporting File',
    // 'Manuscript Image',
        ];

    @endphp
    <div class="row g-0">
        @php
            $isManuscriptSectionCompleted =
                !empty($app_info->title) &&
                !empty($app_info->author_affiliation) &&
                !empty($app_info->author_orcid_id) &&
                !empty($app_info->author_saspi_id) &&
                !empty($app_info->article_type);

            $isManuscriptSectionCompleted = $isManuscriptSectionCompleted ? 'true' : 'false';

            $isDocumentsUploaded = $isManuscriptSectionCompleted;

            $isArticleInformationSectionCompleted = $isManuscriptSectionCompleted;

            $isCoAuthorSectionCompleted = $isManuscriptSectionCompleted;

            $isReviewSuggestionCompleted = $isManuscriptSectionCompleted;

        @endphp
        <!-- Left Sidebar -->

        <div class="col-md-3 sidebar sidebar-fixed p-4">
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
                {{-- <div class="step-item pending d-flex align-items-center" data-step="articleInformation">
                    <div class="step-icon"><i class="ri-article-line"></i></div>
                    <div>
                        <strong>Article Type</strong>
                    </div>
                </div> --}}
                <div class="step-item pending d-flex align-items-center" data-step="coAuthors">
                    <div class="step-icon"><i class="ri-user-shared-line"></i></div>
                    <div>
                        <strong>Co-Authors</strong>
                    </div>
                </div>
                <div class="step-item pending d-flex align-items-center" data-step="reviewerSuggestions">
                    <div class="step-icon"><i class="ri-team-line"></i></div>
                    <div>
                        <strong>Reviewer Suggestions</strong>
                    </div>
                </div>
                <div class="step-item pending d-flex align-items-center" data-step="preview">
                    <div class="step-icon"><i class="ri-check-double-line"></i></div>
                    <div>
                        <strong>Final Submission</strong>
                    </div>
                </div>
            </div>
        </div>



        <!-- Right Content -->
        <div class="col-md-9 p-4">
            <div id="manuscriptForm" enctype="multipart/form-data">
                <div id="formContent">

                    {{-- STEP 1 – New Manuscript --}}
                    <div class="stepPane active" id="manuscript">
                        <form id="manuscriptForm" method="POST" action="{{ route('author.application.store') }}"
                            novalidate>
                            @csrf

                            <input type="hidden" name="uuid" value="{{ $app_info->uuid ?? '' }}">
                            <input type="hidden" id="description" name="description">


                            <div class="col-12">
                                <div class="card h-100">

                                    {{-- Header --}}
                                    <div class="card-header bg-secondary text-white">
                                        <i class="ri-file-text-line"></i> Manuscript Details
                                    </div>

                                    {{-- Article Type --}}
                                    <div class="card-body">
                                        <div class="card">
                                            <div class="card-header">
                                                <h4 class="card-title mb-0">Article Type</h4>
                                            </div>

                                            <div class="card-body">
                                                <div class="row">
                                                    @foreach ($articles as $index => $article)
                                                        <div class="col-6 mb-2">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio"
                                                                    name="article_type"
                                                                    id="article_type_{{ $index }}"
                                                                    value="{{ $article }}" {{-- Check if "Other type" or specific article exists in array --}}
                                                                    {{ in_array($article, $savedArticles) ? 'checked' : '' }}
                                                                    required>
                                                                <label class="form-check-label"
                                                                    for="article_type_{{ $index }}">
                                                                    {{ $article }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>

                                                {{-- Other Article Type --}}
                                                <div id="other_article_type_container" class="mt-3"
                                                    style="{{ in_array('Other type', $savedArticles) ? '' : 'display:none;' }}">

                                                    {{-- Use the variable we extracted in PHP --}}
                                                    <input type="text" class="form-control" id="article_type_other"
                                                        name="article_type_other" placeholder="Enter other article type"
                                                        value="{{ $otherValue }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Article Checked --}}
                                    <div class="card-body">
                                        <div class="card">
                                            <div class="card-header">
                                                <h4 class="card-title mb-0">Article Checked</h4>
                                            </div>

                                            <div class="card-body">
                                                <div class="row">
                                                    @foreach ($article_checks as $i => $article_check)
                                                        <div class="col-md-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="article_check[]"
                                                                    id="article_check_{{ $i }}"
                                                                    value="{{ $article_check }}"
                                                                    {{ in_array($article_check, $savedArticleChecks ?? []) ? 'checked' : '' }}>
                                                                <label class="form-check-label"
                                                                    for="article_check_{{ $i }}">
                                                                    {{ $article_check }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    {{-- Basic Info --}}
                                    <div class="card-body">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col mb-3">
                                                        <label class="form-label required">Title</label>
                                                        <input type="text" class="form-control" name="title"
                                                            value="{{ $app_info->title ?? '' }}" required>
                                                    </div>
                                                    <div class="col mb-3">
                                                        <label class="form-label required">Short Title</label>
                                                        <input type="text" class="form-control" name="short_title"
                                                            value="{{ $app_info->short_title ?? '' }}" required
                                                            placeholder="Less than 6 words">
                                                    </div>
                                                </div>

                                                {{-- <div class="row">
                                                    <div class="col-md-4 mb-3">
                                                        <label class="form-label required">Affiliation</label>
                                                        <input type="text" class="form-control"
                                                            name="author_affiliation"
                                                            value="{{ $app_info->author_affiliation ?? '' }}" required>
                                                    </div>

                                                    <div class="col-md-4 mb-3">
                                                        <label class="form-label required">ORCID ID</label>
                                                        <input type="number" class="form-control" name="author_orcid_id"
                                                            value="{{ $app_info->author_orcid_id ?? '' }}" required>
                                                    </div>

                                                    <div class="col-md-4 mb-3">
                                                        <label class="form-label required">SASPI Membership</label>
                                                        <input type="text" class="form-control" name="author_saspi_id"
                                                            value="{{ $app_info->author_saspi_id ?? '' }}" required>
                                                    </div>
                                                </div> --}}

                                                <div class="mb-3">
                                                    <label class="form-label required">Keywords</label>
                                                    <input type="text" class="form-control" name="keywords"
                                                        value="{{ $app_info->keywords ?? '' }}" required
                                                        placeholder="mesh words, exclusion of title words">
                                                </div>
                                            </div>
                                        </div>
                                    </div>




                                    {{-- Description --}}
                                    <div class="card-body">
                                        <div class="card">
                                            <div class="card-header">
                                                <h4 class="card-title mb-0">Abstract</h4>
                                            </div>

                                            <div class="card-body">
                                                <div id="editor" style="height:130px;">
                                                    {!! $app_info->description ?? '' !!}
                                                </div>
                                                <input type="hidden" name="description" id="description">
                                            </div>
                                        </div>
                                    </div>



                                </div>

                                <div class="card h-100">
                                    <div class="card-header bg-secondary text-white">
                                        <i class="ri-file-text-line"></i> Corresponding Author Details
                                    </div>

                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6 mb-3">
                                                <label class="form-label required">First Name</label>
                                                <input type="text" class="form-control" name="author_name"
                                                    value="{{ authUser()->fname ?? '' }}" required readonly>
                                            </div>
                                            <div class="col-6 mb-3">
                                                <label class="form-label required">Last Name</label>
                                                <input type="text" class="form-control" name="author_lname"
                                                    value="{{ authUser()->lname ?? '' }}" required readonly>
                                            </div>
                                            <div class="col-6 mb-3">
                                                <label class="form-label required">Department / Division</label>
                                                <input type="text" class="form-control" name="author_department"
                                                    value="{{ $app_info->author_department ?? '' }}" required>
                                            </div>
                                            <div class="col-6 mb-3">
                                                <label class="form-label required">Institution / Organization name </label>
                                                <input type="text" class="form-control" name="author_organization"
                                                    value="{{ $app_info->author_organization ?? '' }}" required>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label required">Affiliation</label>
                                                <input type="text" class="form-control" name="author_affiliation"
                                                    value="{{ $app_info->author_affiliation ?? '' }}" required>
                                            </div>


                                            <div class="col-md-4 mb-3">
                                                <label class="form-label required">ORCID ID</label>
                                                <input type="text" class="form-control" name="author_orcid_id"
                                                    maxlength="16" pattern="[0-9]{16}"
                                                    title="ORCID ID must be exactly 16 digits"
                                                    value="{{ $app_info->author_orcid_id ?? '' }}" required>
                                            </div>

                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">SASPI Membership</label>
                                                <input type="text" class="form-control" name="author_saspi_id"
                                                    value="{{ $app_info->author_saspi_id ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="container mt-5">
                                            <form>
                                                <div class="row">
                                                    <!-- Country -->
                                                    <div class="col-md-4 mb-3">
                                                        <label class="form-label"
                                                            title="Refresh or Reload the page if not loaded">
                                                            Country <span class="text-muted">(Refresh if not loaded)</span>
                                                        </label>
                                                        <select id="country" class="form-select" name="author_country">
                                                            <option value="" selected disabled>Select Country
                                                            </option>
                                                        </select>
                                                    </div>

                                                    <!-- State -->
                                                    <div class="col-md-4 mb-3">
                                                        <label class="form-label">State</label>
                                                        <select id="state" class="form-select" name="author_state"
                                                            disabled>
                                                            <option value="" selected disabled>Select Country First
                                                            </option>
                                                        </select>
                                                    </div>

                                                    <!-- City -->
                                                    <div class="col-md-4 mb-3">
                                                        <label class="form-label">City</label>
                                                        <select id="city" class="form-select" name="author_city"
                                                            disabled>
                                                            <option value="" selected disabled>Select State First
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>


                                        <script>
                                            document.addEventListener("DOMContentLoaded", function() {
                                                const countrySelect = document.getElementById('country');
                                                const stateSelect = document.getElementById('state');
                                                const citySelect = document.getElementById('city');

                                                // 1. Get the Saved IDs from Laravel (Database or Old Input)
                                                // Change '$user' to whatever variable name you pass to the view (e.g., $author, $profile)
                                                const savedCountryId = "{{ old('author_country', $app_info->author_country ?? '') }}";
                                                const savedStateId = "{{ old('author_state', $app_info->author_state ?? '') }}";
                                                const savedCityId = "{{ old('author_city', $app_info->author_city ?? '') }}";

                                                // --- HELPER FUNCTIONS ---

                                                function loadCountries() {
                                                    return fetch('/author/locations/countries')
                                                        .then(response => response.json())
                                                        .then(data => {
                                                            data.forEach(country => {
                                                                countrySelect.innerHTML +=
                                                                    `<option value="${country.id}">${country.name}</option>`;
                                                            });
                                                        });
                                                }

                                                function loadStates(countryId) {
                                                    // Reset downstream
                                                    stateSelect.innerHTML = '<option value="" selected disabled>Loading...</option>';
                                                    citySelect.innerHTML = '<option value="" selected disabled>Select State First</option>';
                                                    citySelect.disabled = true;

                                                    return fetch(`/author/locations/states/${countryId}`)
                                                        .then(response => response.json())
                                                        .then(data => {
                                                            stateSelect.innerHTML = '<option value="" selected disabled>Select State</option>';
                                                            if (data.length > 0) {
                                                                stateSelect.disabled = false;
                                                                data.forEach(state => {
                                                                    stateSelect.innerHTML +=
                                                                        `<option value="${state.id}">${state.name}</option>`;
                                                                });
                                                            } else {
                                                                stateSelect.innerHTML = '<option value="" disabled>No States Found</option>';
                                                            }
                                                        });
                                                }

                                                function loadCities(countryId, stateId) {
                                                    // Reset downstream
                                                    citySelect.innerHTML = '<option value="" selected disabled>Loading...</option>';

                                                    return fetch(`/author/locations/cities/${countryId}/${stateId}`)
                                                        .then(response => response.json())
                                                        .then(data => {
                                                            citySelect.innerHTML = '<option value="" selected disabled>Select City</option>';
                                                            if (data.length > 0) {
                                                                citySelect.disabled = false;
                                                                data.forEach(city => {
                                                                    citySelect.innerHTML +=
                                                                        `<option value="${city.id}">${city.name}</option>`;
                                                                });
                                                            } else {
                                                                citySelect.innerHTML = '<option value="" disabled>No Cities Found</option>';
                                                            }
                                                        });
                                                }

                                                // --- EVENT LISTENERS (For User Interaction) ---

                                                countrySelect.addEventListener('change', function() {
                                                    loadStates(this.value);
                                                });

                                                stateSelect.addEventListener('change', function() {
                                                    loadCities(countrySelect.value, this.value);
                                                });

                                                // --- INITIALIZATION (For Edit Mode) ---

                                                // 1. Load Countries
                                                loadCountries().then(() => {
                                                    // 2. If we have a saved country, select it
                                                    if (savedCountryId) {
                                                        countrySelect.value = savedCountryId;

                                                        // 3. Load States for that country
                                                        loadStates(savedCountryId).then(() => {
                                                            // 4. If we have a saved state, select it
                                                            if (savedStateId) {
                                                                stateSelect.value = savedStateId;

                                                                // 5. Load Cities for that state
                                                                loadCities(savedCountryId, savedStateId).then(() => {
                                                                    // 6. If we have a saved city, select it
                                                                    if (savedCityId) {
                                                                        citySelect.value = savedCityId;
                                                                    }
                                                                });
                                                            }
                                                        });
                                                    }
                                                });
                                            });
                                        </script>
                                    </div>
                                </div>

                                {{-- Submit --}}
                                <div class="text-center my-4">
                                    <button type="submit" class="btn btn-primary px-5">
                                        Save & Continue
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>







                    {{-- Step 2 - Upload Documents --}}
                    <div class="stepPane " id="documents" data-is-completed="{{ $isDocumentsUploaded }}">


                        <form action="{{ route('author.application.documents') }}" method="post"
                            enctype="multipart/form-data" novalidate>
                            @csrf
                            @method('POST')

                            <input type="hidden" name="uuid" value="{{ $app_info->uuid ?? '' }}">
                            {{-- Heading and Button Section --}}
                            <div class="d-flex justify-content-between align-items-center mb-1 border-bottom pb-2 mx-4">

                                {{-- Title --}}
                                <h3 class="text-primary mb-0">Upload Documents</h3>

                                @if ($status == '' || $status == 'incomplete' || $status == 'revise')
                                    <button class="btn btn-sm btn-secondary d-flex align-items-center gap-1 actionHandler"
                                        data-url="{{ route('author.application.add_document', [
                                            'type' => 'Supporting File',
                                            'app_id' => $app_info->id ?? 'na',
                                        ]) }}">
                                        <i class="ri-add-circle-line" style="font-size: large"></i>
                                        Add Supporting Document
                                    </button>
                                @endif
                            </div>

                            <div class="form-section" data-step="2">
                                <table class="table table-bordered table-striped align-middle text-center">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>SN</th>
                                            <th>File Name</th>
                                            {{-- <th>File Type</th> --}}
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
                                                <td style="white-space: normal; word-wrap: break-word; max-width: 300px;">
                                                    {{ $category }}</td>
                                                {{-- <td>

                                                    {{ $attachment_row->file_type ?? '---' }}
                                                </td> --}}
                                                {{-- Added max-width and text-break to force wrapping --}}
                                                <td style="white-space: normal; word-wrap: break-word; max-width: 300px;">
                                                    {{ $attachment_row->legend ?? '---' }}
                                                    {{-- Note: In the second loop, use $docs->legend instead of $attachment_row->legend --}}
                                                </td>

                                                <td>
                                                    @if ($attachment_row != null)
                                                        <!-- View -->
                                                        <span class="actionHandler text-success me-2"
                                                            data-url="{{ route('file.viewframe') }}"
                                                            data-frame="offcanvas"
                                                            data-file-url="{{ getFileUrl($attachment_row->file_url ?? '') }}"
                                                            data-handler="openFilePreview" title="View File">
                                                            <i class="ri-eye-line fs-5"></i>
                                                        </span>
                                                        @if ($status == '' || $status == 'incomplete' || $status == 'revise')
                                                            <!-- Replace -->
                                                            <a href="#"
                                                                class="actionHandler text-primary mx-2 border-start ps-2 text-decoration-none"
                                                                data-url="{{ route('author.application.file_upload', [
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
                                                                data-url="{{ route('author.application.file_destroy', [
                                                                    'app_id' => $app_info->uuid,
                                                                    'id' => $attachment_row->id ?? 'na',
                                                                ]) }}"
                                                                title="Delete File">
                                                                <i class="ri-delete-bin-line fs-5"></i>
                                                            </a>
                                                        @endif
                                                    @else
                                                        <!-- Upload -->
                                                        <a href="#"
                                                            class="actionHandler text-info text-decoration-none"
                                                            data-url="{{ route('author.application.file_upload', [
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

                                            $manuscripts =
                                                $app_info
                                                    ->attachments()
                                                    ->where('attachment_type', 'Upload Manuscript')
                                                    ->orderBy('created_at', 'desc')
                                                    ->get() ?? [];

                                        @endphp
                                        {{-- @foreach ($manuscripts as $file) --}}
                                        @php
                                            // if (!empty($attachments)) {
                                            //     $attachment_row = $attachments
                                            //         ->where('attachment_type', $category)
                                            //         ->first();
                                            // } else {
                                            //     $attachment_row = null;
                                            // }
                                            $c++;
                                        @endphp
                                        <tr>
                                            <td>4</td>
                                            <td style="white-space: normal; word-wrap: break-word; max-width: 300px;">
                                                Upload Manuscript</td>
                                            {{-- <td>

                                                    {{ $attachment_row->file_type ?? '---' }}
                                                </td> --}}
                                            {{-- Added max-width and text-break to force wrapping --}}
                                            <td style="white-space: normal; word-wrap: break-word; max-width: 300px;">
                                                {{ $file->legend ?? '---' }}
                                                {{-- Note: In the second loop, use $docs->legend instead of $attachment_row->legend --}}
                                            </td>


                                            <td>
                                                @foreach ($manuscripts as $file)
                                                    @if ($file != null)
                                                        <!-- View -->
                                                        <span class="actionHandler text-success me-2"
                                                            data-url="{{ route('file.viewframe') }}"
                                                            data-frame="offcanvas"
                                                            data-file-url="{{ getFileUrl($file->file_url ?? '') }}"
                                                            data-handler="openFilePreview" title="View File">
                                                            <i class="ri-eye-line fs-5"></i>
                                                        </span>
                                                        @if ($status == '' || $status == 'incomplete' || $status == 'revise')
                                                            <!-- Replace -->
                                                            <a href="#"
                                                                class="actionHandler text-primary mx-2 border-start ps-2 text-decoration-none"
                                                                data-url="{{ route('author.application.file_upload', [
                                                                    'id' => $file->id ?? 'na',
                                                                    'type' => 'Upload Manuscript',
                                                                    'legend' => $file->legend ?? 'na',
                                                                    'app_id' => $app_info->id ?? 'na',
                                                                ]) }}"
                                                                title="Replace File">
                                                                <i class="ri-file-upload-line fs-5"></i>
                                                            </a>

                                                            <!-- Delete -->
                                                            <a href="#"
                                                                class="actionHandler text-danger ms-2 border-start ps-2 text-decoration-none"
                                                                data-action="delete"
                                                                data-url="{{ route('author.application.file_destroy', [
                                                                    'app_id' => $app_info->uuid,
                                                                    'id' => $file->id ?? 'na',
                                                                ]) }}"
                                                                title="Delete File">
                                                                <i class="ri-delete-bin-line fs-5"></i>
                                                            </a>
                                                        @endif
                                                    @else
                                                        <!-- Upload -->
                                                        <a href="#"
                                                            class="actionHandler text-info text-decoration-none"
                                                            data-url="{{ route('author.application.file_upload', [
                                                                'id' => 'na',
                                                                'type' => 'Upload Manuscript',
                                                                'app_id' => $app_info->id ?? 'na',
                                                                'legend' => $file->legend ?? 'na',
                                                            ]) }}"
                                                            title="Upload File">
                                                            <i class="ri-upload-2-line fs-5"></i>
                                                        </a>
                                                    @endif
                                                    <br>
                                                @endforeach

                                            </td>

                                        </tr>
                                        {{-- @endforeach --}}


                                        @php
                                            $attachments = !empty($attachments)
                                                ? $attachments->whereNotIn(
                                                    'attachment_type',
                                                    array_merge($attachment_categories, ['Upload Manuscript']),
                                                )
                                                : null;

                                        @endphp
                                        @if (!empty($attachments))
                                            @foreach ($attachments as $key => $docs)
                                                <tr>
                                                    <td>{{ $loop->iteration + $c }}</td>
                                                    <td>{{ $docs->attachment_type }}</td>
                                                    {{-- <td>
                                                        <select class="form-select form-select-sm">
                                                    <option value="">Select Type</option>
                                                    <option value="pdf">PDF</option>
                                                    <option value="doc">DOC</option>
                                                    <option value="docx">DOCX</option>
                                                </select>
                                                        {{ $docs->file_type ?? '---' }}
                                                    </td> --}}
                                                    <td>{{ $docs->legend ?? '---' }}</td>
                                                    <td>
                                                        {{-- @if ($docs != null) --}}
                                                        <!-- View -->
                                                        <span class="actionHandler text-success me-2"
                                                            data-url="{{ route('file.viewframe') }}"
                                                            data-frame="offcanvas"
                                                            data-file-url="{{ getFileUrl($docs->file_url ?? '') }}"
                                                            data-handler="openFilePreview" title="View File">
                                                            <i class="ri-eye-line fs-5"></i>
                                                        </span>
                                                        @if ($status == '' || $status == 'incomplete' || $status == 'revise')
                                                            <!-- Replace -->
                                                            <a href="#"
                                                                class="actionHandler text-primary mx-2 border-start ps-2 text-decoration-none"
                                                                data-url="{{ route('author.application.file_upload', [
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
                                                                data-url="{{ route('author.application.file_destroy', [
                                                                    'app_id' => $app_info->uuid,
                                                                    'id' => $docs->id ?? 'na',
                                                                ]) }}"
                                                                title="Delete File">
                                                                <i class="ri-delete-bin-line fs-5"></i>
                                                            </a>
                                                        @endif
                                                        {{-- @else --}}
                                                        <!-- Upload -->
                                                        {{-- <a href="#"
                                                            class="actionHandler text-info text-decoration-none"
                                                            data-url="{{ route('author.application.file_upload', [
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
                                @if ($status == '' || $status == 'incomplete' || $status == 'revise')
                                    <div class="d-flex justify-content-around mt-2">
                                        {{-- <button type="button" class="btn btn-secondary " disabled>Back</button> --}}
                                        <button type="submit" class="btn btn-primary ">Save & Next</button>
                                    </div>
                                @endif
                            </div>

                        </form>

                    </div>

                    {{-- STEP 3 – Article Information --}}
                    {{-- <div class="stepPane" id="articleInformation"
                        data-is-completed="{{ $isArticleInformationSectionCompleted }}">


                        <form action="{{ route('author.application.articleInformation') }}" method="post"
                            enctype="multipart/form-data" novalidate>
                            @csrf
                            @method('POST')
                            <h3 class="mx-5 text-primary border-bottom pb-2">Article Information</h3>

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
                                                    data-error-message="At least one option is required"
                                                    style="display:none">
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
                                                        <input type="text" class="form-control"
                                                            id="article_type_other" name="article_type_other"
                                                            placeholder="Enter other article type"
                                                            value="{{ $savedArticles['other_data'] }}">
                                                    </div>
                                                @else
                                                    <div id="other_article_type_container" class="mt-2"
                                                        style="display:none;">
                                                        <input type="text" class="form-control"
                                                            id="article_type_other" name="article_type_other"
                                                            placeholder="Enter other article type">
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
                                                            <input class="form-check-input article-checkbox"
                                                                type="checkbox" name="article_check[]"
                                                                value='{{ $article_check }}'
                                                                id='article_{{ $index }}'
                                                                {{ in_array($article_check, $savedArticleChecks) ? 'checked' : '' }}>
                                                            <label class="form-check-label"
                                                                for="article_check{{ $index }}">{{ $article_check }}</label>
                                                        </div>
                                                    @endforeach
                                                    <span class="text-danger mt-2" data-error="atLeastOne"></span>
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
                                            <div id="editor" style="height:130px !important;">
                                                {!! $app_info->description ?? '' !!}
                                            </div>
                                            <input id="description" name="description" type="hidden">
                                        </div>
                                    </div>
                                </div>
                                @if ($status == '' || $status == 'incomplete' || $status == 'revise')
                                    <div class="d-flex justify-content-around mt-2">
                                        <button type="submit" class="btn btn-primary ">Save & Next</button>
                                    </div>
                                @endif
                            </div>
                        </form>
                    </div> --}}

                    <!-- Step 4 -->
                    <div class="stepPane" id="coAuthors" data-is-completed="{{ $isCoAuthorSectionCompleted }}">
                        <form action="{{ route('author.application.coAuthors') }}" method="post"
                            enctype="multipart/form-data">
                            @csrf
                            @method('POST')

                            <input type="hidden" name="uuid" value="{{ $app_info->uuid ?? '' }}">

                            <div class="form-section" data-step="4">

                                <!-- Header + Add Button -->
                                <div
                                    class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2 mx-3">
                                    <h3 class="text-primary m-0">Co-Author Details <span
                                            class="text-muted fs-6">(Optional)</span></h3>
                                    @if ($status == '' || $status == 'incomplete' || $status == 'revise')
                                        <button type="button"
                                            class="btn btn-sm btn-secondary actionHandler d-flex align-items-center justify-content-center"
                                            data-url="{{ route('author.application.co_author', $app_info->uuid ?? '') }}"
                                            title="Add Co-Author">
                                            <i class="ri-add-circle-line" style="font-size: large"></i>
                                        </button>
                                    @endif
                                </div>

                                <!-- Co-Author Table -->
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="fw-bold">Name</th>
                                                <th class="fw-bold">Email</th>
                                                <th class="fw-bold">Phone</th>
                                                <th class="fw-bold">Affiliation</th>
                                                <th class="fw-bold">ORCID</th>
                                                <th class="fw-bold">SASPI</th>
                                                @if ($status == '' || $status == 'incomplete' || $status == 'revise')
                                                    <th class="fw-bold text-center" style="width: 100px;">Actions</th>
                                                @endif
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @if (empty($app_info->co_author_data))
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted py-4">
                                                        No co-authors added yet.
                                                    </td>
                                                </tr>
                                            @else
                                                @foreach ($app_info->co_author_data as $coAuthor)
                                                    <tr id="co-author-{{ $coAuthor['coauthor_id'] }}">
                                                        <td>{{ $coAuthor['coauthor_name'] }}</td>
                                                        <td class="text-muted">{{ $coAuthor['coauthor_email'] }}</td>
                                                        <td>{{ $coAuthor['coauthor_phone'] }}</td>
                                                        <td>{{ $coAuthor['coauthor_affiliation'] ?? '-' }}</td>
                                                        <td>{{ $coAuthor['coauthor_orcid_id'] ?? '-' }}</td>
                                                        <td>{{ $coAuthor['coauthor_saspi_id'] ?? '-' }}</td>
                                                        @if ($status == '' || $status == 'incomplete' || $status == 'revise')
                                                            <td class="text-center">

                                                                <!-- Edit -->
                                                                <a href="javascript:void(0);"
                                                                    class="text-info me-2 actionHandler"
                                                                    data-url="{{ route('author.application.co_author_update', [
                                                                        'uuid' => $app_info->uuid,
                                                                        'author_id' => $coAuthor['coauthor_id'],
                                                                    ]) }}"
                                                                    title="Edit">
                                                                    <i class="ri-edit-box-line fs-5"></i>
                                                                </a>

                                                                <!-- Delete -->
                                                                <a href="javascript:void(0);"
                                                                    class="text-danger actionHandler" data-action="delete"
                                                                    data-url="{{ route('author.application.co_author_delete', [
                                                                        'uuid' => $app_info->uuid,
                                                                        'author_id' => $coAuthor['coauthor_id'],
                                                                    ]) }}"
                                                                    title="Delete">
                                                                    <i class="ri-delete-bin-line fs-5"></i>
                                                                </a>

                                                            </td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Save & Next -->
                                @if ($status == '' || $status == 'incomplete' || $status == 'revise')
                                    <div class="text-center mt-4">
                                        <button type="submit" class="btn btn-primary px-4">Save & Next</button>
                                    </div>
                                @endif
                            </div>

                        </form>
                    </div>

                    <!-- Step 5 -->
                    <div class="stepPane" id="reviewerSuggestions"
                        data-is-completed="{{ $isReviewSuggestionCompleted }}">

                        <form action="{{ route('author.application.reviewerSuggestions') }}" method="post"
                            enctype="multipart/form-data">
                            @csrf
                            @method('POST')


                            <input type="hidden" name="uuid" value="{{ $app_info->uuid ?? '' }}">

                            <div class="form-section" data-step="5">

                                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                    <h3 class="text-primary m-0">Reviewer Suggestions <span
                                            class="text-muted fs-6">(Optional)</span></h3>
                                    @if ($status == '' || $status == 'incomplete' || $status == 'revise')
                                        <button type="button"
                                            class="btn btn-secondary btn-sm actionHandler d-flex align-items-center gap-1"
                                            data-url="{{ route('author.application.reviewer', $app_info->uuid ?? '') }}"
                                            title="Add Reviewer Suggestion">
                                            <i class="ri-add-line fs-5"></i>
                                            Add
                                        </button>
                                    @endif
                                </div>


                                <!-- Reviewer Table -->
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle">
                                        <thead class="bg-light">
                                            <tr>
                                                {{-- <th>Title</th> --}}
                                                <th>Name</th>
                                                {{-- <th>Last Name</th> --}}
                                                <th>Email</th>
                                                <th>Institution</th>
                                                <th>Specialization</th>
                                                <th>Recommendation</th>
                                                @if ($status == '' || $status == 'incomplete' || $status == 'revise')
                                                    <th class="text-center" style="width: 100px;">Actions</th>
                                                @endif
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @forelse ($app_info->data['reviewer_suggestions'] ?? [] as $reviewer)
                                                <tr>
                                                    {{-- <td>{{ $reviewer['title'] ?? '-' }}</td> --}}
                                                    <td>{{ $reviewer['first_name'] . ' ' . $reviewer['last_name'] ?? '-' }}
                                                    </td>
                                                    {{-- <td>{{ $reviewer['last_name'] ?? '-' }}</td> --}}
                                                    <td class="text-muted">{{ $reviewer['email'] ?? '-' }}</td>
                                                    <td>{{ $reviewer['institution'] ?? '-' }}</td>
                                                    <td>{{ $reviewer['specialization'] ?? '-' }}</td>
                                                    <td>{{ isset($reviewer['recommended']) ? ($reviewer['recommended'] == 'yes' ? 'Recommended' : 'Not Recommended') : '-' }}
                                                    </td>
                                                    @if ($status == '' || $status == 'incomplete' || $status == 'revise')
                                                        <td class="text-center">
                                                            <div class="btn-group">

                                                                <!-- Edit -->
                                                                <a href="javascript:void(0);"
                                                                    class="text-info me-2 actionHandler"
                                                                    data-url="{{ route('author.application.reviewer_update', [
                                                                        'uuid' => $app_info->uuid,
                                                                        'reviewer_id' => $reviewer['reviewer_id'],
                                                                    ]) }}"
                                                                    title="Edit">
                                                                    <i class="ri-edit-box-line fs-5"></i>
                                                                </a>

                                                                <!-- Delete -->
                                                                <a href="javascript:void(0);"
                                                                    class="text-danger actionHandler" data-action="delete"
                                                                    data-url="{{ route('author.application.reviewer_delete', [
                                                                        'uuid' => $app_info->uuid,
                                                                        'reviewer_id' => $reviewer['reviewer_id'],
                                                                    ]) }}"
                                                                    title="Delete">
                                                                    <i class="ri-delete-bin-line fs-5"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="text-center text-muted py-4">
                                                        No reviewer suggestions added yet.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>

                                    </table>
                                </div>

                                <!-- Save Button -->
                                @if ($status == '' || $status == 'incomplete' || $status == 'revise')
                                    <div class="text-center mt-4">
                                        <button type="submit" class="btn btn-primary px-4">
                                            Save & Next
                                        </button>
                                    </div>
                                @endif

                            </div>
                        </form>
                    </div>


                    <!-- Step 6 -->
                    <div class="stepPane" id="preview" data-is-completed="">
                        <form action="{{ route('author.application.preview') }}" method="post"
                            enctype="multipart/form-data">
                            @csrf
                            @method('POST')
                            <input type="hidden" name="uuid" value="{{ $app_info->uuid ?? '' }}">

                            <div class="form-section" data-step="6">
                                <h3 class="mb-4 text-primary border-bottom pb-2">Final Review & Submission</h3>

                                {{-- STEP SUMMARY SECTION --}}
                                @php
                                    $allStepsCompleted = false;

                                    // Check if the first section is initialized to avoid errors
                                    if (
                                        isset($isManuscriptSectionCompleted) &&
                                        $isManuscriptSectionCompleted == 'true'
                                    ) {
                                        // STRUCTURE: 'Step Name' => ['completed' => boolean, 'mandatory' => boolean]
                                        $steps = [
                                            'Step 1: Manuscript' => [
                                                'completed' => $isManuscriptSectionCompleted == 'true',
                                                'mandatory' => true,
                                            ],
                                            'Step 2: File Uploads' => [
                                                'completed' => $app_info->isReadyToSubmit() ?? false,
                                                'mandatory' => true,
                                            ],
                                            // 'Step 3: Article Type & Title' => [
                                            //     'completed' => !empty($app_info->article_type),
                                            //     'mandatory' => true,
                                            // ],
                                            'Step 3: Co-Authors' => [
                                                'completed' => !empty($app_info->co_author_data),
                                                'mandatory' => false,
                                            ],
                                            'Step 4: Reviewer Suggestions' => [
                                                'completed' => !empty($app_info->data['reviewer_suggestions']),
                                                'mandatory' => false,
                                            ],
                                        ];

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
                                                        <span class="fw-medium">{{ $stepName }}</span>
                                                        @if ($data['completed'])
                                                            <span class="badge bg-success rounded-pill"><i
                                                                    class="ri-check-double-line"></i> Done</span>
                                                        @elseif($data['mandatory'])
                                                            <span class="badge bg-danger rounded-pill"><i
                                                                    class="ri-error-warning-line"></i> Pending</span>
                                                        @else
                                                            <span class="badge bg-light text-dark border rounded-pill"><i
                                                                    class="ri-information-line"></i> Not Mandatory</span>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                @endif

                                {{-- 1. Declaration of Conflict of Interest --}}
                                <div class="card mb-4">
                                    <div class="card-header bg-dark text-white">
                                        1. Declaration of Conflict of Interest
                                    </div>
                                    <div class="card-body">

                                        {{-- STRICT DATA EXTRACTION --}}
                                        @php
                                            // 1. Get raw data
                                            $rawData = $app_info->data ?? [];

                                            // 2. FORCE conversion to array to prevent "offset of type string" error
                                            if (is_string($rawData)) {
                                                $decoded = json_decode($rawData, true);
                                                // If decode worked and result is array, use it. Otherwise empty array.
                                                $savedData = is_array($decoded) ? $decoded : [];
                                            } elseif (is_array($rawData)) {
                                                $savedData = $rawData;
                                            } else {
                                                $savedData = [];
                                            }

                                            // 3. Safely access keys
                                            $conflictVal = $savedData['conflict_interest'] ?? '';
                                            $copyrightVal = $savedData['copyright_agree'] ?? false;
                                            $conflictDetails = $savedData['conflict_interest_details'] ?? '';
                                        @endphp

                                        <p class="mb-2">Are you aware of any potential conflicts of interest between any
                                            of the authors and this journal?</p>

                                        {{-- OPTION: YES --}}
                                        <label class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="conflict_interest"
                                                value="yes" id="conflictYes"
                                                {{ $conflictVal == 'yes' ? 'checked' : '' }}>
                                            <span class="form-check-label">Yes</span>
                                        </label>

                                        {{-- HIDDEN TEXT AREA (Only visible if Yes) --}}
                                        <div id="conflictDetails" class="ms-4 mb-3"
                                            style="{{ $conflictVal == 'yes' ? '' : 'display:none;' }}">
                                            <label class="form-label text-muted small">Please specify details:</label>
                                            <textarea class="form-control" name="conflict_interest_details" rows="2"
                                                placeholder="Describe the conflict...">{{ $conflictDetails }}</textarea>
                                        </div>

                                        {{-- OPTION: NO --}}
                                        <label class="form-check">
                                            <input class="form-check-input" type="radio" name="conflict_interest"
                                                value="no" id="conflictNo"
                                                {{ $conflictVal == 'no' ? 'checked' : '' }}>
                                            <span class="form-check-label">No</span>
                                        </label>
                                    </div>
                                </div>

                                {{-- 2. Copyright Agreement --}}
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

                                {{-- Submit Button --}}
                                <div class="d-flex justify-content-end">
                                    @if (!empty($app_info) && ($app_info->status == 'incomplete' || $app_info->status == 'revise'))
                                        <button type="button" class="btn btn-primary actionHandler" id="finalSubmitBtn"
                                            data-steps-completed="{{ $allStepsCompleted ? 'true' : 'false' }}" disabled
                                            data-url="{{ route('author.application.preview') }}"
                                            data-handler="finalSubmit">
                                            Final Submit
                                        </button>
                                        {{-- <button class="btn btn-group-sm btn-sm btn-success actionHandler"
                                            data-url="{{ route('admin.application.rev_accept_action', $application->uuid) }}"
                                            data-handler="confirmAccept">
                                            Accept
                                        </button> --}}
                                    @endif
                                </div>

                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
        document.addEventListener('DOMContentLoaded', function() {

            const form = document.getElementById('manuscriptForm');
            const editor = document.getElementById('editor');
            const descriptionInput = document.getElementById('description');
            const otherContainer = document.getElementById('other_article_type_container');
            const otherInput = document.getElementById('article_type_other');

            function toggleOtherArticle() {
                const selected = document.querySelector('input[name="article_type"]:checked');

                if (selected && selected.value === 'Other type') {
                    otherContainer.style.display = 'block';
                    otherInput.required = true;
                } else {
                    otherContainer.style.display = 'none';
                    otherInput.required = false;
                    otherInput.value = '';
                }
            }

            // Initial state (edit mode)
            toggleOtherArticle();

            // Listen for radio change
            document.querySelectorAll('input[name="article_type"]').forEach(radio => {
                radio.addEventListener('change', toggleOtherArticle);
            });

            // Sync editor content before submit
            form.addEventListener('submit', function() {
                descriptionInput.value = editor.innerHTML.trim();
            });

        });
    </script>






    {{-- for step 6  --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // --- Selectors ---
            const submitBtn = document.getElementById('finalSubmitBtn');
            const conflictRadios = document.querySelectorAll('input[name="conflict_interest"]');
            const agreeCheckbox = document.getElementById('copyrightCheck');

            // Selectors for the Conflict Details Box
            const conflictDetailsDiv = document.getElementById('conflictDetails');
            const conflictInput = conflictDetailsDiv ? conflictDetailsDiv.querySelector('textarea') : null;

            // --- Safety Check ---
            if (!submitBtn) {
                console.error("Submit button not found! Check ID 'finalSubmitBtn'");
                return;
            }

            // --- FUNCTION 1: Toggle Visibility of Text Area ---
            function toggleConflictInput() {
                if (!conflictDetailsDiv) return;

                // Check if "Yes" is currently checked
                const yesRadio = document.getElementById('conflictYes');
                const isYes = yesRadio && yesRadio.checked;

                if (isYes) {
                    conflictDetailsDiv.style.display = 'block';
                    if (conflictInput) conflictInput.required = true; // Make text required if Yes
                } else {
                    conflictDetailsDiv.style.display = 'none';
                    if (conflictInput) conflictInput.required = false; // Not required if Hidden
                }
            }

            // --- FUNCTION 2: Toggle Final Submit Button ---
            function toggleSubmit() {
                // 1. Get PHP Status
                const phpStepsDone = submitBtn.getAttribute('data-steps-completed') === 'true';

                // 2. Get User Input Status
                const conflictSelected = Array.from(conflictRadios).some(r => r.checked);

                // Safety check for checkbox existence
                const agreeChecked = agreeCheckbox ? agreeCheckbox.checked : false;

                // 3. Logic: All must be true
                if (phpStepsDone && conflictSelected && agreeChecked) {
                    submitBtn.disabled = false;
                    submitBtn.title = "Ready to submit";
                    submitBtn.classList.remove('btn-secondary');
                    submitBtn.classList.add('btn-primary');
                } else {
                    submitBtn.disabled = true;
                    if (!phpStepsDone) submitBtn.title = "Previous steps are incomplete";
                    else submitBtn.title = "Please agree to the declaration";
                }
            }

            // --- Event Listeners ---
            conflictRadios.forEach(r => {
                r.addEventListener('change', () => {
                    toggleConflictInput();
                    toggleSubmit();
                });
            });

            if (agreeCheckbox) {
                agreeCheckbox.addEventListener('change', toggleSubmit);
            }

            // --- Run on Page Load ---
            toggleConflictInput();
            toggleSubmit();
        });
    </script>

    <script src="{{ asset('engines/fileviewer.js') }}"></script>

    <script>
        window.finalSubmit = (button) => {

            const url = new URL(button.dataset.url);
            const parentForm = button.closest('form');
            const formData = new FormData(parentForm);
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
                    //  Calling the API
                    fetch(url, {
                            method: "POST",
                            headers: {
                                ...settings.headers,
                                Accept: "text/html, application/json",
                            },
                            body: formData
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
                            toastr.error('Your request could not be processed');
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
