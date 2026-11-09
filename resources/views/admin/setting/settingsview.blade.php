@extends('backend.admin.dashboard.master')
@section('title')
    Settings
@endsection
@section('css')
    <!-- quill css -->
    {{-- <link href="{{ asset('backend/assets/libs/quill/quill.core.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('backend/assets/libs/quill/quill.bubble.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('backend/assets/libs/quill/quill.snow.css') }}" rel="stylesheet" type="text/css" /> --}}
@endsection
@section('page-heading')
    All Settings
@endsection
@section('breducrumb')
    {{-- First Item Will be same as the Page Heading --}}
    <li class="breadcrumb-item"><a href="javascript: void(0);">Settings</a></li>
    <li class="breadcrumb-item active">Settings</li>
@endsection
@section('content')
    <div class="modal fade" data-bs-backdrop="static" id="AjaxModal" aria-hidden="true">

    </div>
    <div class="row">

        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title flex-grow-1 mb-0">All Settings</h5>
                    <div class="d-flex" style="padding:0px 10px;">
                        <select class="form-select" id="FilterKeys" name="FilterKeys">
                            <option selected value=0>All Keys</option>
                            @foreach ($keys as $key)
                                <option value='{{ $key->key }}'>{{ $key->key }}</option>
                            @endforeach
                        </select>
                        <button id="ShowModal" class="btn btn-primary btn-sm">Add New Settings</button>
                    </div>
                </div>
                <div class="card-body">
                    <table id="DataTable" class="display table-bordered table" style="width:100%">
                        <thead>
                            <tr>
                                <th>SR No.</th>
                                <th>Title</th>
                                <th>Image</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Key</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('javascripts')
    <script>
        $(document).ready(function() {
            // Setting up the ajax to register Functionality

            $.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            });

            $("#DataTable").DataTable({
                processing: true,
                info: true,
                ajax: "{{ route('admin.settings.index') }}",
                columns: [{
                        // data: 'id'
                        data: "DT_RowIndex",
                    }, {
                        data: "title",
                        searchable: true,
                    },
                    {
                        data: "image",
                        searchable: true,
                    },
                    {
                        data: 'description',
                        searchable: true,
                    },
                    {
                        data: 'status',
                        searchable: true,
                    },
                    {
                        data: 'key',
                        orderable:false,
                        searchable: true,
                    },
                    {
                        data: "actions",
                        orderable: false,
                        searchable: false,
                    },
                ],
            });



            // The Searching API

            // var DataTable = $('#DataTable').DataTable();
            // $("#DataTable_filter.dataTables_filter").append($("#FilterKeys"));

            var categoryIndex = 0;
            $("#DataTable th").each(function(i) {
                if ($($(this)).html() == "Key") {
                    categoryIndex = i;
                    return false;
                }
            });

            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    var selectedItem = $('#FilterKeys').val()
                    var category = data[categoryIndex];
                    if(selectedItem==0)
                    {
                        return true;
                    }
                    if (selectedItem === "" || category.includes(selectedItem)) {
                        return true;
                    }
                    return false;
                }
            );



            // Global Modal Container

            var ModalContainer = $("#AjaxModal");
            var spinner =
                '<span class="spinner-border  spinner-border-sm" role="status" aria-hidden="true" style="color:white;"></span>';
            var processing =
                ' <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="margin-right:10px"></span>' +
                "Saving...";

            // //Using filter
            // $(document).on('submit', '#FilterForm', function(e) {
            //     // alert("I am an alert box!");
            //     table_url="{{ route('settings.index') }}";
            //     // $("#DataTable").DataTable({
            //     //     processing: true,
            //     //     info: true,
            //     //     ajax: "{{ route('settings.index') }}",
            //     //     columns: [{
            //     //             // data: 'id'
            //     //             data: "DT_RowIndex",
            //     //         }, {
            //     //             data: "title",
            //     //             searchable: true,
            //     //         },
            //     //         {
            //     //             data: "image",
            //     //             searchable: true,
            //     //         },
            //     //         {
            //     //             data: 'description',
            //     //             searchable: true,
            //     //         },
            //     //         {
            //     //             data: 'status',
            //     //             searchable: true,
            //     //         },
            //     //         {
            //     //             data: 'key',
            //     //             searchable: true,
            //     //         },
            //     //         {
            //     //             data: "actions",
            //     //             orderable: false,
            //     //             searchable: false,
            //     //         },
            //     //     ],
            //     // })
            // });

            $(document).on('change', '#FilterKeys', function(e) {
                e.preventDefault();


                $('#DataTable').DataTable().draw();
                // var option = $(this).find('option:selected').val();
                // table_url = "{{ route('settings.index', ':key') }}";
                // table_url = table_url.replace(':key', option);
                // console.log(table_url);
                // $('#DataTable').DataTable().ajax.reload();


            });
            // $(document).on('submit', '#FilterForm', function(e) {
            //     e.preventDefault();
            //     var submitbtn = $('#SubmitFilterBtn');
            //     var form = $(this);
            //     $.ajax({
            //         type: "GET",
            //         url: $(form).attr('action'),
            //         data: new FormData(this),
            //         processData: false,
            //         contentType: false,
            //         beforeSend: function() {
            //             $(submitbtn).html(processing);
            //             $(submitbtn).attr('disabled', true);
            //         },
            //         success: function(response) {
            //             $(submitbtn).html('Submit');
            //             $(submitbtn).removeAttr('disabled');
            //             if (response.status == 'fail') {
            //                 $.each(response.errors, function(key, val) {
            //                     toastr["error"](val, "Form Data");
            //                 });
            //             } else {
            //                 //  $(ModalContainer).modal('hide');
            //                 //  toastr["success"](response.msg, "Form Data");
            //                 // $('#DataTable').DataTable().ajax.reload();
            //             }
            //         }
            //     });


            // });

            //Showing Global Modal
            $(document).on('click', '#ShowModal', function(e) {
                e.preventDefault();
                var btn = $(this);
                var html = $(btn).html();
                // alert('hii');
                route = "{{ route('admin.settings.create') }}";
                $.ajax({
                    type: "Get",
                    url: route,
                    dataType: "html",
                    beforeSend: function() {
                        $(btn).attr('disabled', true);
                        $(btn).html(spinner);
                    },
                    success: function(response) {
                        $(btn).html(html);
                        $(btn).removeAttr('disabled');
                        $(ModalContainer).html(response).modal('show');
                    }
                });
            });

            // Insert Operation

            $(document).on('submit', '#CreateForm', function(e) {
                e.preventDefault();
                var submitbtn = $('#SubmitBtn');
                var form = $(this);
                $.ajax({
                    type: "POst",
                    url: $(form).attr('action'),
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $(submitbtn).html(processing);
                        $(submitbtn).attr('disabled', true);
                    },
                    success: function(response) {
                        $(submitbtn).html('Submit');
                        $(submitbtn).removeAttr('disabled');
                        if (response.status == 'fail') {
                            $.each(response.errors, function(key, val) {
                                toastr["error"](val, "Form Data");
                            });
                        } else {
                            $(ModalContainer).modal('hide');
                            toastr["success"](response.msg, "Form Data");
                            $('#DataTable').DataTable().ajax.reload();
                        }
                    }
                });


            });

            $(document).on("click", "button.editbtn", function(e) {
                var btn = $(this);
                e.preventDefault();
                var html = $(btn).html();
                $(this).html(spinner);
                $.ajax({
                    type: "GET",
                    url: $(this).attr("ref"),
                    dataType: "html",
                    beforeSend: function() {},
                    success: function(response) {
                        $(btn).html(html);
                        $(ModalContainer).html(response).modal("show");
                    },
                });
            });

            $(document).on("submit", "#EditForm", function(e) {
                e.preventDefault();
                var submitbtn = $('#SubmitBtn');
                $.ajax({
                    type: "POST",
                    url: $(this).find(submitbtn).attr("ref"),
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $(submitbtn).html(processing);
                    },
                    success: function(response) {
                        $(submitbtn).html("Submit");
                        if (response.status == "fail") {
                            $.each(response.errors, function(key, value) {
                                toastr["error"](value, "Form Data");
                            });
                        } else {
                            // $(this)[0].reset();
                            $(ModalContainer).modal("hide");
                            toastr["success"](response.msg, "Form Data");
                            $('#DataTable').DataTable().ajax.reload(null, false);
                        }
                    },
                });


            });

            $(document).on("click", "button.deletebtn", function(e) {
                var btn = $(this);
                e.preventDefault();
                // Sweet ALert 2 Will not use the old Style
                // Swal.fire will be replaced by swal()
                // and is confirmed will be repladed with willDelete.
                var html = $(btn).html();
                $(this).html(spinner);
                swal({
                    title: "Are you sure?",
                    text: "Your record will deleted permanently!",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        $.ajax({
                            type: "DELETE",
                            url: $(this).attr("ref"),
                            beforeSend: function() {},
                            success: function(response) {
                                $(btn).html(html);
                                if (response.status == "success") {
                                    toastr["success"](response.msg, "Form Data");
                                    $('#DataTable').DataTable().ajax.reload(null,
                                        false);
                                } else {
                                    swal({
                                        title: "Oops..!",
                                        text: response.msg,
                                        icon: "warning",
                                    });
                                }
                            },
                        });
                    } else {
                        $(btn).html(html);
                    }
                });
            });
            // Show Edit Form
            $(document).on("click", "button.viewbtn", function(e) {
                var btn = $(this);
                e.preventDefault();
                var html = $(btn).html();
                $(this).html(spinner);

                $.ajax({
                    type: "GET",
                    url: $(this).attr("ref"),
                    dataType: "html",
                    beforeSend: function() {},
                    success: function(response) {
                        $(btn).html(html);
                        $(ModalContainer).html(response).modal("show");
                    },
                });
            });




        });
    </script>
@endsection
