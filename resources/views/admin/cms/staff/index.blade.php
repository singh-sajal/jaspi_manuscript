@extends('admin.app')

@section('title', 'Staff')
@section('breadcrumb', 'Staff')
@section('page-title', 'Staff')
@section('breadcrumb-button')
    @can('staff.create')
        <div class="d-flex align-items-center">
            <button class="btn btn-sm btn-primary btn-label actionHandler" data-url="{{ route('admin.staff.create') }}">
                <i class="ri-add-circle-line label-icon me-1"></i>
                Add New
            </button>
        </div>
    @endcan
@endsection
@section('content')

    <div class="modal" id="AjaxModal" tabindex="-1"></div>
    <div class="card">
        {{-- <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">All Entities</h3>


        </div> --}}
        <div class="card-body" id="Saarni">

            <table class="table-bordered w-100 dt-datatable table">
                <thead>
                    <tr>
                        <th data-sortable="true" data-column="name">Staff Id</th>
                        <th>Role</th>
                        <th data-sortable="true" data-column="email">Email</th>
                        <th data-sortable="true" data-column="phone">Phone</th>
                        {{-- <th data-sortable="true" data-column="type">Type</th>
                        <th data-sortable="true" data-column="type">Is Featured</th> --}}

                        <th>Status</th>

                        {{-- <th>2FA Status</th> --}}
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
            'url' => route('admin.staff.index'),
            'moduleName' => 'Consultant List',
        ];
    @endphp
    @include('includes.saarnijs', ['options' => $options])
@endsection
