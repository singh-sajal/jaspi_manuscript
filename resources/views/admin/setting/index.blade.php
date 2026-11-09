@extends('admin.app')

@section('title', 'Setting')
@section('breducrumb')
    {{-- First Item Will be same as the Page Heading --}}
    <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
    <li class="breadcrumb-item active">Setting List</li>
@endsection
@section('page-heading', 'Setting')

@section('content')
{{--
    <div class="offcanvas offcanvas-end" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
        <div class="offcanvas-header" style="box-shadow: 1px 1px 15px #ede8e8;">
            <h3 id="offcanvasRightLabel"> Language Course Syllabus</h3>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body" id="CourseContent" style="padding:20px;">
        </div>
    </div> --}}

    {{-- <div class="modal fade" id="AjaxModal" tabindex="-1" aria-hidden="true"></div> --}}
    {{-- <div class="modal" id="AjaxModal" tabindex="-1"></div> --}}

    {{-- <div class="modal fade" id="AjaxModal2" tabindex="-1" aria-hidden="true"></div> --}}

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">All Students</h5>
            <div>
                {{-- <button data-url="{{ route('setting.create') }}" class="btn btn-primary actionHandler">Add New
                    Students</button> --}}
                <button class="btn btn-sm btn-primary btn-label actionHandler" data-url="{{ route('admin.settings.create') }}">
                    <i class="ri-add-circle-line label-icon me-1"></i>
                    Add New Setting
                </button>
            </div>
        </div>

        <div class="card-body" id="Saarni">
            <table  class="table-bordered w-100 dt-datatable table">
                <thead>
                    <tr>
                        <th>S.No</th>

                        <th data-sortable="true" data-column="title">Title</th>

                        {{-- <th>Father Name</th> --}}

                        <th>Image</th>

                        <th>Description</th>

                        {{-- <th data-sortable="true" data-column="phone">Status</th> --}}

                        <th>Key</th>

                        <th>Action</th>
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
            'url' => route('admin.settings.index'),
            'moduleName' => 'Consultant List',
        ];
    @endphp
    @include('includes.saarnijs', ['options' => $options])


@endsection
