<!DOCTYPE html>
<html lang="en" class="h-100">



<head>
    <!-- Title -->
    <title>Manuscript-@yield('title')</title>

    <!-- Meta -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="author" content="DexignZone">
    <meta name="robots" content="index, follow">
    <meta name="format-detection" content="telephone=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- FAVICONS ICON -->
    <link rel="shortcut icon" type="image/png" href="images/favicon.png">

    <!-- MOBILE SPECIFIC -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="{{ asset('admin/assets/vendor/bootstrap-select/dist/css/bootstrap-select.min.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/assets/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/assets/css/remixicon.css') }}" rel="stylesheet" type="text/css" />

    <link rel="stylesheet" href="{{ asset('admin/assets/vendor/sweet-alert/sweetalert2.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/vendor/sweet-alert/toastr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/css/validator.css') }}">
</head>

<body class="vh-100" style="background:#d9e4f3">
    <div class="authincation h-100">
        <div class="container-fluid h-100">
            <div class="row h-100">
                <div class="col-lg-6 col-md-12 col-sm-12 align-self-center mx-auto">
                    @yield('content')
                </div>
                <div class="col-xl-6 col-lg-6">
                    <div class="pages-left h-100">
                        <div class="login-content">
                            <a href="#"><img src="{{ asset('logo-dark.png') }}" class="logo-dark img-fluid mb-1"
                                    style="width: 200px" alt=""></a>


                            <p>Crafted for those who write to leave a mark.</p>
                        </div>
                        <div class="login-media mt-5 text-center">
                            <img src="{{ asset('admin/assets/images/login.png') }}" alt="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--**********************************
 Scripts
***********************************-->
    <!-- Required vendors -->
    <script src="{{ asset('admin/assets/vendor/global/global.min.js') }}"></script>
    <script src="{{ asset('admin/assets/vendor/bootstrap-select/dist/js/bootstrap-select.min.js') }}"></script>
    <script src="{{ asset('admin/assets/vendor/sweet-alert/toastr.min.js') }}"></script>
    <script src="{{ asset('admin/assets/vendor/sweet-alert/sweetalert.min.js') }}"></script>
    <script src="{{ asset('admin/assets/js/ajax-engine-1.0.js') }}"></script>
    @yield('javascripts')
</body>



</html>
