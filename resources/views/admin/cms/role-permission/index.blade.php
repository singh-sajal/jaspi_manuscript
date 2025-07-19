@extends('admin.app')

@section('title', 'Roles')
@section('breadcrumb', 'Roles')
@section('page-title', 'Roles')
@section('breadcrumb-button')
    <div class="d-flex align-items-center">

        <button class="btn btn-sm btn-primary btn-label actionHandler" data-url="{{ route('admin.roles.create') }}">
            <i class="ri-add-circle-line label-icon me-1"></i>
            Add New
        </button>
    </div>
@endsection
@section('content')


    <div class="card">

        <div class="card-body" id="Saarni">

            <table class="table-bordered w-100 dt-datatable table">
                <thead>
                    <tr>
                        <th data-sortable="true" data-column="name">Role</th>
                        <th>Total Permissions</th>
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
            'url' => route('admin.roles.index'),
            'moduleName' => 'Role List',
        ];
    @endphp
    @include('includes.saarnijs', ['options' => $options])
@endsection
