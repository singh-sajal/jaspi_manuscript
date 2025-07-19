@extends('author.app')
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
        <form action="{{ route('author.application.update', $app_info->uuid) }}" method="post" novalidate
            enctype="multipart/form-data">
            @csrf
            @method('put')


            @include('author.cms.form')


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
