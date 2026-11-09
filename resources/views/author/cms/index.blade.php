@php
    $create_button = App\Models\Setting::where('key','author_application_create_button')->first();
@endphp
{{-- @dd($create_button) --}}
@extends('author.app')

@section('title', 'Manuscript List')
@section('breadcrumb', 'Manuscript List')
@section('page-title', 'Manuscript List')
@section('breadcrumb-button')
    @if ($create_button && $create_button->title === 'true')
        <div class="d-flex align-items-center">
            <a class="btn btn-sm btn-primary btn-label" href="{{ route('author.application.check_list') }}">
                <i class="ri-add-circle-line label-icon me-1"></i>
                New Manuscript
            </a>
        </div>
    @endif
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
                        <th data-sortable="true" data-column="title">Manuscript Id</th>
                        <th>Submission Type</th>
                        <th>Article Type</th>
                        <th>Manuscript Status</th>
                        {{-- <th>Assigned to </th> --}}
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
        $url = request()->fullUrl();
        $options = [
            'selector' => 'Saarni',
            'url' => $url,
            'moduleName' => 'Application List',
        ];
    @endphp
    @include('includes.saarnijs', ['options' => $options])

    <script src="{{ asset('engines/fileviewer.js') }}"></script>
    {{-- Submit confirmation --}}
    <script>
        window.confirmSubmit = (button) => {
            //  console.log('HRLLLL');
            //  return;

            const url = new URL(button.dataset.url);

            swal({
                title: "Are you sure?",
                text: 'If you like you change any detail go back otherwise continue with your submission',
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
                            toastr.error('Unable to submit the application');
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
