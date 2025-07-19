<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">

    <link href="{{ asset('admin/assets/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/assets/css/remixicon.css') }}" rel="stylesheet" type="text/css" />

    <link rel="stylesheet" href="{{ asset('admin/assets/vendor/sweet-alert/sweetalert2.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/vendor/sweet-alert/toastr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/css/validator.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/css/authmodule.css') }}">
    <link rel="stylesheet" href="style.css">
    <style>
        .input-group {
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #454545;
            opacity: 0.7;
            z-index: 10;
        }


        .auth-link-bottom {
            margin-top: 20px;
            color: #5f0000;
            font-size: 14px;
            text-decoration: underline;
            cursor: pointer;
            text-align: center;

        }



        .form-control {
            box-shadow: 0px 0px 3px #e5e5e5;
            outline: 1px solid #454545;
        }

        .login-wrapper .login-content .digit-group {
            width: 70%;
        }

        .login-wrapper .login-content form {
            width: 100%;
        }

        input.otp-digit {

            width: 60px !important;
            height: 60px !important;
            line-height: 40px;
            font-size: 20px;
            padding-left: 20px !important;


        }
    </style>
    @yield('css')
</head>

<body>

    <div class="main-wrapper">
        <div class="account-content"
            style="background-image: url({{ asset('web/illustrations/diamond-sunset.svg') }});">
            <div class="login-wrapper login-new" style="background:#f0f8ff7d;">
                <div class="row w-100">
                    <div class="col-lg-4 mx-auto">
                        <div class="login-content h-100 card p-3 shadow shadow-sm">
                            <div class="digit-group w-100">
                                <div class="logo mb-3 text-center">
                                    <img src="{{ asset('web/logo.jpeg') }}" class="img-fluid rounded bg-primary"
                                        style="width: 50px;height:50px" alt="">
                                </div>
                                <div class="login-container">
                                    <div id="loginForm">
                                        <p class="small text-center">
                                            New Author verification
                                        </p>
                                        <h5 class="text-uppercase text-center pb-3">
                                            Enter your phone number & email address </h5>
                                            <form class="login-form p-0" action="{{ route('author.userCheck') }}"
                                                method="post" novalidate>
                                                <div class="mb-3">
                                                    <div class="input-group">
                                                        <i class="fas fa-phone"></i>
                                                        <input type="number" name="phone" class="form-control"
                                                            placeholder="Your phone number" required 
                                                            minlength="10" maxlength="10">
                                                    </div>
                                                   <span class="text-danger" style="text-align: left; display: block;" data-error="phone"></span>
                                                </div>
                                                <div class="mb-3">
                                                    <div class="input-group">
                                                        <i class="fas fa-envelope"></i>
                                                        <input type="text" name="email" class="form-control"
                                                            placeholder="Your email id" required>
                                                    </div>
                                                    <span class="text-danger" style="text-align: left; display: block;" data-error="email"></span>

                                                </div>


                                                <button type="submit" class="btn btn-primary w-100">Submit</button>


                                                <div class="mb-1">
                                                    <div class="auth-link-bottom">
                                                        Return to <b> <a href="{{ route('home') }}">Home</a></b>
                                                    </div>
                                                    <span class="text-danger" data-error="terms"></span>
                                                </div>

                                            </form>
                                    </div>


                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-center align-items-center copyright-text my-4 text-white">
                            <p>Copyright &copy; {{ date('Y') }} Manuscript</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Required vendors -->
    <script src="{{ asset('admin/assets/vendor/global/global.min.js') }}"></script>
    <script src="{{ asset('admin/assets/vendor/bootstrap-select/dist/js/bootstrap-select.min.js') }}"></script>
    <script src="{{ asset('admin/assets/vendor/sweet-alert/toastr.min.js') }}"></script>
    <script src="{{ asset('admin/assets/vendor/sweet-alert/sweetalert.min.js') }}"></script>
    <script src="{{ asset('admin/assets/js/ajax-engine-1.0.js') }}"></script>



</body>

</html>
