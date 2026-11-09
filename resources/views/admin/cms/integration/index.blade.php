@extends('admin.app')

@section('title', 'Interests')
@section('breadcrumb', 'Interests')
@section('page-title', 'Interests')
@section('breadcrumb-button')
    @can('integration.create')
        <div class="d-flex align-items-center">
            <button class="btn btn-sm btn-primary btn-label actionHandler" data-url="{{ route('admin.integration.create') }}">
                <i class="ri-add-circle-line label-icon me-1"></i>
                Add New
            </button>
        </div>
    @endcan
@endsection
@section('content')

    <div class="modal" id="AjaxModal" tabindex="-1"></div>
    <div class="card">
        <div class="card-body" id="Saarni">

            <table class="table-bordered w-100 dt-datatable table">
                <thead>
                    <tr>
                        <th data-sortable="true" data-column="id" style="width: 15%">#</th>
                        <th data-sortable="true" data-column="name">Name</th>
                        <th data-sortable="true" data-column="created_at">Created At</th>
                        <th style="width: 15%">Options</th>
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
        $options = [
            'selector' => 'Saarni',
            'url' => route('admin.integration.index'),
            'moduleName' => 'Interests List',
        ];
    @endphp
    @include('includes.saarnijs', ['options' => $options])
@endsection