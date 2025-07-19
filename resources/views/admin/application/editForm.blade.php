@extends('admin.app')
@section('title', 'Application')
@section('page-title', 'Update Application')
@section('breadcrumb-button')
    <a class="btn btn-sm btn-secondary" href="{{ url()->previous() }}">
        {{-- go back icon remix --}}
        <i class="ri-arrow-go-back-line"></i>
    </a>
@endsection
@section('css')

@endsection
@section('content')
    <div class="card">
        <form action="{{ route('admin.application.update', $application->uuid) }}" method="post" novalidate
            enctype="multipart/form-data">
            @csrf
            @method('put')


            @php
                $savedArticles =
                    !empty($application) && is_array($application->article_type) ? $application->article_type : [];
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
                $hasChecked = false;

                $article_checks = ['Plagiarism Check', 'Cross reference Check'];
                $savedArticleChecks =
                    !empty($app_info) && !empty($app_info->article_check) ? $app_info->article_check : [];
            @endphp

            <div class="card-body">

                <div class="row">
                    <div class="col-7">
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="submission_type" class="form-label required">Submission Type</label>
                                    <select class="form-select form-control" id="submission_type" name="submission_type"
                                        onchange="toggleJaspiIdField()" required>
                                        <option value="">--Select--</option>
                                        <option value="new_submission"
                                            {{ ($application->submission_type ?? '') == 'new_submission' ? ' selected' : '' }}>
                                            New Submission</option>
                                        <option value="revised_submission"
                                            {{ ($application->submission_type ?? '') == 'revised_submission' ? ' selected' : '' }}>
                                            Revised Submission</option>
                                    </select>

                                </div>
                                {{-- <div class="mb-3" id="jaspi_id_field" style="display:none;">
                                    <label for="jaspi_id" class="form-label">JASPI ID</label>
                                    <select class="form-select form-control" id="jaspi_id" name="jaspi_id">
                                        <option value="">Select JASPI ID</option>
                                        @foreach ($applications as $application)
                                            <option value="{{ $application->application_id }}">
                                                {{ $application->application_id }}
                                                _
                                                {{ $application->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div> --}}
                                <div class="mb-3">
                                    <label for="title" class="form-label required">Title</label>
                                    <input type="text" class="form-control" id="title" name="title"
                                        placeholder="Enter application title" required
                                        value="{{ $application->title ?? '' }}">
                                </div>
                                <div class="mb-3">
                                    <label for="author_affiliation" class="form-label required">Author Affiliation</label>
                                    <input type="text" class="form-control" id="author_affiliation"
                                        name="author_affiliation" placeholder="Enter author affiliation" required
                                        value="{{ $application->author_affiliation ?? '' }}">
                                </div>
                                <div class="mb-3">
                                    <label for="author_orcid_id" class="form-label required">ORCID ID</label>
                                    <input type="text" class="form-control" id="author_orcid_id" name="author_orcid_id"
                                        placeholder="Enter ORCID ID" required
                                        value="{{ $application->author_orcid_id ?? '' }}">
                                </div>
                                <div class="mb-3">
                                    <label for="author_saspi_id" class="form-label required">SASPI ID</label>
                                    <input type="text" class="form-control" id="author_saspi_id" name="author_saspi_id"
                                        placeholder="Enter SASPI ID" required
                                        value="{{ $application->author_saspi_id ?? '' }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-5">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Article Type <span class="required text-danger"></span></h4>
                            </div>
                            <div class="card-body">
                                <input type="checkbox" name="atLeastOne" id="atLeastOne" {{ $hasChecked ? '' : 'required' }}
                                    data-error-message="At least one option is required" style="display:none">
                                @foreach ($articles as $index => $article)
                                    <div class="col form-check mb-2">
                                        <input class="form-check-input article-checkbox" type="checkbox"
                                            name="article_type[]" value='{{ $article }}'
                                            id='article_{{ $index }}'
                                            {{ in_array($article, $savedArticles) ? 'checked' : '' }}
                                            onchange="toggleRequired()">
                                        <label class="form-check-label"
                                            for="article_{{ $index }}">{{ $article }}</label>
                                    </div>
                                @endforeach
                                @if (array_key_exists('other_data', $savedArticles))
                                    <div id="other_article_type_container">
                                        <input type="text" class="form-control" id="article_type_other"
                                            name="article_type_other" placeholder="Enter other article type"
                                            value="{{ $savedArticles['other_data'] ?? '' }}">
                                    @else
                                        <div id="other_article_type_container" style="display: none;">
                                            <input type="text" class="form-control" id="article_type_other"
                                                name="article_type_other" placeholder="Enter other article type"
                                                value="">
                                @endif
                            </div>
                            <span class="text-danger mt-2" data-error="atLeastOne"></span>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Article Checked <span class="text-danger"></span></h4>
                        </div>
                        <div class="card-body">
                            <div class="d-flex">
                                @foreach ($article_checks as $index => $article_check)
                                    <div class="col form-check">
                                        <input class="form-check-input article-checkbox" type="checkbox"
                                            name="article_check[]" value='{{ $article_check }}'
                                            id='article_{{ $index }}'
                                            {{ in_array($article_check, $savedArticleChecks) ? 'checked' : '' }}>
                                        <label class="form-check-label"
                                            for="article_{{ $index }}">{{ $article_check }}</label>
                                    </div>
                                @endforeach

                                {{-- <span class="text-danger mt-2" data-error="atLeastOne"></span> --}}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 mb-3">


                    <label for="description" class="form-label required">Description</label>
                    <div id="editor" style="height:230px !important;">
                        {!! $application->description ?? '' !!}
                    </div>
                    <input id="description" name="description" type="text" class="d-none" required></input>

                </div>
                <span class="text-danger mt-1" data-error="description"></span>
            </div>
    </div>



    <div class="card-footer d-flex justify-content-end">
        <button type="submit" class="btn btn-primary">Submit</button>
    </div>
    </form>
    </div>
@endsection
@section('javascripts')
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
        document.addEventListener('DOMContentLoaded', () => {
            toggleRequired();
            toggleJaspiIdField();
        });
    </script>
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



@endsection
