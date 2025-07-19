@extends('admin.app')

@section('title', 'Web Query: Contact us page queries')
@section('breadcrumb', 'Web Query: Contact us page queries')
@section('page-title', 'Web Query: Contact us page queries')
@section('breadcrumb-button')
    {{-- <div class="d-flex align-items-center">
        <button class="btn btn-sm btn-primary btn-label actionHandler"
        data-url="{{ route('admin.author.create') }}">
            <i class="ri-add-circle-line label-icon me-1"></i>
            Add New
        </button>
    </div> --}}
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
                        <th data-sortable="true" data-column="name" style="width: 20%">
                            User Info</th>
                        <th data-sortable="true" data-column="subject">Subject</th>
                        {{-- <th data-sortable="true" data-column="phone">Phone</th> --}}
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
        // Getting the search param of serviceId from current request if available

        $options = [
            'selector' => 'Saarni',
            'url' => route('admin.query.index'),
            'moduleName' => 'Consultant List',
        ];
    @endphp
    @include('includes.saarnijs', ['options' => $options])
@endsection
