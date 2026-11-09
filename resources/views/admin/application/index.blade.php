@extends('admin.app')

@section('title', 'Manuscript List')
@section('breadcrumb', 'Manuscript List')
@section('page-title', 'Manuscript List')
@section('breadcrumb-button')
    <div class="d-flex align-items-center">

    </div>
@endsection
@section('content')
    <div class="offcanvas offcanvas-end" tabindex="-1" style="z-index: 10000;width:80%" id="FileOffcanvas"
        data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="FileOffcanvasLabel"></div>
    <div class="modal" id="AjaxModal" tabindex="-1"></div>
    <div class="card">
        {{-- <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">All Entities</h3>


        </div> --}}
        <div class="card-body" id="Saarni">

            @php
                $adminUser = authUser('admin');
                $isAdminUser = $adminUser && $adminUser->is_admin == 1;
                $activeFilters = ['', 'mine'];
                $closedFilters = ['closed', 'published', 'rejected', 'withdrawn', 'duplicate'];
            @endphp

            @if ($isAdminUser && in_array($type, $activeFilters, true))
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <a href="{{ route('admin.application.index') }}"
                        class="btn btn-sm {{ $type === '' ? 'btn-primary' : 'btn-outline-primary' }}">All</a>
                    <a href="{{ route('admin.application.index', ['type' => 'mine']) }}"
                        class="btn btn-sm {{ $type === 'mine' ? 'btn-primary' : 'btn-outline-primary' }}">My</a>
                </div>
            @elseif ($isAdminUser && in_array($type, $closedFilters, true))
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <a href="{{ route('admin.application.index', ['type' => 'closed']) }}"
                        class="btn btn-sm {{ $type === 'closed' ? 'btn-primary' : 'btn-outline-primary' }}">All</a>
                    <a href="{{ route('admin.application.index', ['type' => 'published']) }}"
                        class="btn btn-sm {{ $type === 'published' ? 'btn-primary' : 'btn-outline-primary' }}">Published</a>
                    <a href="{{ route('admin.application.index', ['type' => 'rejected']) }}"
                        class="btn btn-sm {{ $type === 'rejected' ? 'btn-primary' : 'btn-outline-primary' }}">Rejected</a>
                    <a href="{{ route('admin.application.index', ['type' => 'withdrawn']) }}"
                        class="btn btn-sm {{ $type === 'withdrawn' ? 'btn-primary' : 'btn-outline-primary' }}">Withdrawn</a>
                    <a href="{{ route('admin.application.index', ['type' => 'duplicate']) }}"
                        class="btn btn-sm {{ $type === 'duplicate' ? 'btn-primary' : 'btn-outline-primary' }}">Duplicate</a>
                </div>
            @endif

            <table class="table-bordered w-100 dt-datatable table">
                <thead>
                    <tr>
                        <th>Sr. No.</th>
                        <th data-sortable="true" data-column="application_id">Manuscript Id</th>

                        <th data-sortable="true" data-column="submission_type">Submission Type</th>
                        <th data-column="article_type">Article Type</th>
                        @if (authUser('admin')->is_admin == 1)
                            <th>Uploaded By </th>
                        @endif

                        @if (authUser('admin')->is_admin == 1 && !in_array($type, ['closed', 'published', 'rejected', 'withdrawn', 'duplicate'], true))
                            <th>Assigned to </th>
                        @endif
                        <th data-column="status">Status</th>
                        <th data-sortable="true" data-column="created_at">Created At</th>
                        <th data-sortable="true" data-column="updated_at">Updated At</th>

                        <th>Options</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('javascripts')
    @php
        // Getting the search param of serviceId from current request if available

        $options = [
            'selector' => 'Saarni',
            'url' => route('admin.application.index', ['type' => $type]),
            'moduleName' => 'Application List',
        ];
    @endphp
    @include('includes.saarnijs', ['options' => $options])
    <script src="{{ asset('engines/fileviewer.js') }}"></script>

{{-- Reviewer rejects the task --}}
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
                     //  Calling the API
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
     {{-- Reviewer accepts the task --}}
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
                     //  Calling the API
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


@endsection
