<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="keywords" content="" />
    <meta name="author" content="" />
    <meta name="robots" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="W3crm:Customer Relationship Management Admin Bootstrap 5 Template" />
    <meta property="og:title" content="W3crm:Customer Relationship Management Admin Bootstrap 5 Template" />
    <meta property="og:description" content="W3crm:Customer Relationship Management Admin Bootstrap 5 Template" />
    <meta property="og:image" content="social-image.png" />
    <meta name="format-detection" content="telephone=no" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- PAGE TITLE HERE -->
    <title>ManuScript ADMIN- @yield('title')</title>
    <!-- FAVICONS ICON -->
    <link rel="shortcut icon" type="image/png" href="{{ asset('admin/uploads/logo_2.jpeg') }}" />

    <link href="{{ asset('admin/assets/vendor/bootstrap-select/dist/css/bootstrap-select.min.css') }}"
        rel="stylesheet" />
    <link href="{{ asset('admin/assets/vendor/swiper/css/swiper-bundle.min.css') }}" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet" />
    {{-- Vector maps --}}
    {{-- <link href="{{ asset('admin/assets/vendor/jvmap/jquery-jvectormap.css') }}" rel="stylesheet" /> --}}

    <link href="{{ asset('admin/assets/vendor/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css') }}"
        rel="stylesheet" />

    <!-- tagify-css -->
    <link href="{{ asset('admin/assets/vendor/tagify/dist/tagify.css') }}" rel="stylesheet" />

    <!-- Style css -->
    <link href="{{ asset('admin/assets/css/style.css') }}" rel="stylesheet" />
    <link href="{{ asset('admin/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('admin/assets/css/remixicon.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.css" />

    <link rel="stylesheet" href="{{ asset('admin/assets/vendor/sweet-alert/sweetalert2.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/vendor/sweet-alert/toastr.min.css') }}">
    <link href="{{ asset('admin/assets/vendor/quill/quill.core.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('admin/assets/vendor/quill/quill.bubble.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('admin/assets/vendor/quill/quill.snow.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{ asset('admin/assets/css/datatable.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/css/validator.css') }}">
    <style>
        .deznav .metismenu>li>a i {
            height: 100%;
            margin-right: 0.4125rem;
            margin-top: -0.0875rem;
            font-size: 24px;
        }

        .breadcrumb-item+.breadcrumb-item::before {
            content: "/";
        }

        [data-sidebar-style="full"][data-layout="vertical"] .menu-toggle .deznav .metismenu>li:hover>ul {
            height: auto !important;
            padding: 0.625rem 0;
            z-index: 99999;
            background: #2c2c2c;
        }

        #AjaxModal {
            z-index: 9999;
        }

        .form-control:disabled,
        .form-control[readonly] {
            background: #d3e3f8 !important;
            opacity: 1;
        }

        /* scrollers */
        /* * ::-webkit-scrollbar {
            width: 12px;
        }

        * ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        * ::-webkit-scrollbar-thumb {
            background-color: #bbbbf3;
            border-radius: 10px;
            border: 3px solid #f1f1f1;
        }

        * ::-webkit-scrollbar-thumb:hover {
            background-color: #bbbbf3;
        }

        /* Firefox */
        /* * {
            scrollbar-width: thin;
            scrollbar-color: #d8d8da transparent;
        }  */

        /* When sidebar is collapsed, show short logo */
        .sidebar-collapsed .logo-full {
            display: none !important;
        }

        .sidebar-collapsed .logo-short {
            display: block !important;
        }

        /* When sidebar is expanded, show full logo */
        .logo-short {
            display: none;
        }
    </style>

    @yield('css')
    <style>
        /* Remove blue outline on focus */
        .form-select:focus,
        .btn:focus,
        input:focus,
        select:focus {
            outline: none !important;
            box-shadow: none !important;
            border-color: #6c757d;
            /* Optional: Custom border color */
        }

        /* Optional - Add a more subtle focus effect */
        .form-select:focus,
        select:focus {
            border-color: #6c757d;
        }
    </style>
</head>

<body data-typography="poppins" data-theme-version="light" data-layout="vertical" data-nav-headerbg="black" ">
    <!--**********************************
        Main wrapper start
    ***********************************-->
    <div id="main-wrapper">

        @include('includes.branding-admin')

        @include('includes.sidecanvas')

        @include('includes.header')

        @include('admin.sidebar')


        <div class="content-body">
            <!-- row -->
            <div class="page-titles align-items-center">
                <ol class="breadcrumb">
                    <li>
                        <h5 class="bc-title">@yield('page-title')</h5>
                    </li>
                    {{-- <li class="breadcrumb-item">
                        <a href="javascript:void(0)">
                            App
                        </a>
                    </li>
                    <li class="breadcrumb-item active"><a href="javascript:void(0)">@yield('breadcrumb')</a></li> --}}
                </ol>
                @yield('breadcrumb-button')

            </div>
            <div class="modal" id="AjaxModal" tabindex="-1"></div>
            @yield('content')
        </div>

        @include('includes.footer')


    </div>

    <!-- Required vendors -->
    <script src="{{ asset('admin/assets/vendor/jquery/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('admin/assets/vendor/global/global.min.js') }}"></script>
    <script src="{{ asset('admin/assets/vendor/chart.js/Chart.bundle.min.js') }}"></script>
    <script src="{{ asset('admin/assets/vendor/bootstrap-select/dist/js/bootstrap-select.min.js') }}"></script>
    <script src="{{ asset('admin/assets/vendor/apexchart/apexchart.js') }}"></script>

    <!-- Dashboard 1 -->
    <script src="{{ asset('admin/assets/js/dashboard/dashboard-1.js') }}"></script>
    <script src="{{ asset('admin/assets/vendor/draggable/draggable.js') }}"></script>

    <!-- tagify -->
    <script src="{{ asset('admin/assets/vendor/tagify/dist/tagify.js') }}"></script>


    <!-- Apex Chart -->

    <script src="{{ asset('admin/assets/vendor/bootstrap-datetimepicker/js/moment.js') }}"></script>
    <script src="{{ asset('admin/assets/vendor/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js') }}"></script>

    <!-- Vectormap -->
    {{-- <script src="vendor/jqvmap/js/jquery.vmap.min.js"></script>
    <script src="vendor/jqvmap/js/jquery.vmap.world.js"></script>
    <script src="vendor/jqvmap/js/jquery.vmap.usa.js"></script> --}}

    <script src="{{ asset('admin/assets/js/custom.js') }}"></script>
    <script src="{{ asset('admin/assets/js/deznav-init.js') }}"></script>
    {{-- <script src="{{ asset('admin/assets/js/demo.js') }}"></script> --}}
    {{-- <script src="{{ asset('admin/assets/js/styleSwitcher.js') }}"></script> --}}
    <script src="{{ asset('admin/assets/vendor/quill/quill.min.js') }}"></script>
       <script src="{{ asset('admin/assets/vendor/sweet-alert/toastr.min.js') }}"></script>
    <script src="{{ asset('admin/assets/vendor/sweet-alert/sweetalert.min.js') }}"></script>
    <script src="{{ asset('admin/assets/js/ajax-engine-1.0.js') }}"></script>

    <script src="{{ asset('admin/assets/js/pdfmake.min.js') }}"></script>
    <script src="{{ asset('admin/assets/js/vfs_fonts.js') }}"></script>
    <script src="{{ asset('admin/assets/js/saarni.min.js') }}"></script>
    {{-- <script>
        window.addEventListener(" pageshow", function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script> --}}

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            setTimeout(() => {

                const tableContainer = document.querySelector(".dt-container-inner");

                let isDragging = false;
                let startX, startY, scrollLeft, scrollTop;
                if (tableContainer) {

                    // Mouse down event
                    tableContainer.addEventListener("mousedown", (e) => {
                        console.log(e);

                        isDragging = true;
                        startX = e.pageX - tableContainer.offsetLeft;
                        startY = e.pageY - tableContainer.offsetTop;
                        scrollLeft = tableContainer.scrollLeft;
                        scrollTop = tableContainer.scrollTop;
                        tableContainer.style.cursor =
                            "grabbing"; // Change cursor to indicate dragging
                        tableContainer.style.userSelect = "none"; // Disable text selection
                    });

                    // Mouse move event
                    tableContainer.addEventListener("mousemove", (e) => {
                        if (!isDragging) return; // Exit if not dragging
                        const x = e.pageX - tableContainer.offsetLeft;
                        const y = e.pageY - tableContainer.offsetTop;
                        const walkX = x - startX; // Horizontal movement
                        const walkY = y - startY; // Vertical movement
                        tableContainer.scrollLeft = scrollLeft - walkX;
                        tableContainer.scrollTop = scrollTop - walkY;
                    });

                    // Mouse up or leave event
                    tableContainer.addEventListener("mouseup", () => {
                        isDragging = false;
                        tableContainer.style.cursor = "default"; // Reset cursor
                        tableContainer.style.userSelect = "auto"; // Re-enable text selection
                    });

                    tableContainer.addEventListener("mouseleave", () => {
                        isDragging = false; // Stop dragging if mouse leaves container
                    });
                }

            }, 3000);
        });
    </script>

    {{-- For logo and short_logo --}}
    <script>
        document.querySelector('.nav-control').addEventListener('click', function() {
            document.body.classList.toggle('sidebar-collapsed');
        });
    </script>
    @yield('javascripts') </body>

</html>
