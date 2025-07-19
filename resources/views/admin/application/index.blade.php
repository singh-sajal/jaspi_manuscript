@extends('admin.app')

@section('title', 'Manu Script List')
@section('breadcrumb', 'Manu Script List')
@section('page-title', 'Manu Script List')
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

            <table class="table-bordered w-100 dt-datatable table">
                <thead>
                    <tr>
                        <th data-sortable="true" data-column="title">Manu Script Id</th>

                        <th data-sortable="true" data-column="title">Submission Type</th>
                        <th data-sortable="true" data-column="title">Article Type</th>
                        @if (isSuperAdmin())
                            <th>Uploaded By </th>
                        @endif

                        <th>Assigned to </th>
                        <th data-sortable="true" data-column="status">Status</th>
                        <th data-sortable="true" data-column="created_at">Created At</th>

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

@endsection
