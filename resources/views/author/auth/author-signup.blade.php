<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Author Signup')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Fonts & Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('admin/assets/vendor/sweet-alert/sweetalert2.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/assets/vendor/sweet-alert/toastr.min.css') }}">

    <!-- Local Styles -->
    <link href="{{ asset('admin/assets/css/style.css') }}" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background-color: #f5f7fb;
        }

        .container-fluid {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-box {
            display: flex;
            width: 100%;
            max-width: 1200px;
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }

        .auth-left {
            flex: 1;
            background: url('{{ asset('web/illustrations/diamond-sunset.svg') }}') no-repeat center;
            background-size: cover;
        }

        .auth-right {
            flex: 1.2;
            padding: 40px;
            background-color: #ffffff;
        }

        .logo {
            text-align: center;
            margin-bottom: 25px;
        }

        .logo img {
            height: 60px;
            border-radius: 50%;
            border: 2px solid #4d87d3;
        }

        h3 {
            font-weight: 600;
            margin-bottom: 20px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 16px;
            position: relative;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 14px 12px 40px;
            border-radius: 8px;
            border: 1px solid #ccc;
            transition: 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #4d87d3;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.15);
        }

        .form-group i {
            position: absolute;
            top: 50%;
            left: 14px;
            transform: translateY(-50%);
            color: #888;
        }

        .btn-primary {
            background-color: #4d87d3;
            border: none;
            padding: 12px;
            width: 100%;
            font-weight: 500;
            border-radius: 8px;
            transition: 0.3s;
        }

        .btn-primary:hover {
            background-color: #336db9;
        }

        .auth-footer {
            margin-top: 15px;
            text-align: center;
            font-size: 14px;
        }

        .auth-footer a {
            color: #4d87d3;
            text-decoration: none;
        }

        .auth-left h2,
        .auth-left p {
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.3);
        }

        @media(max-width: 768px) {
            .auth-box {
                flex-direction: column;
            }

            .auth-left {
                height: 200px;
            }

            .auth-right {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="auth-box">
            <!-- Left Illustration -->
            <div class="auth-left d-none d-md-flex flex-column justify-content-center align-items-center text-white p-4"
                style="background: url('{{ asset('web/illustrations/diamond-sunset.svg') }}') no-repeat center; background-size: cover;">
                <div class="text-center">
                    <h2 style="font-weight: 600;">Welcome to JASPI</h2>
                    <p style="font-size: 15px; margin-top: 10px; max-width: 300px;">
                        Journal of Antimicrobial Stewardship Practices and Infectious Diseases.
                    </p>
                    <img src="{{ asset('signup.svg') }}" alt="Writing Illustration" style="max-width: 280px;">


                </div>
            </div>

            <!-- Right Form -->
            <div class="auth-right">
                <div class="logo">
                    <img src="{{ asset('web/logo.jpeg') }}" alt="Logo">
                </div>
                <h3>New Author Signup</h3>

                <form action="{{ route('author.registration') }}" method="POST" enctype="multipart/form-data"
                    novalidate>
                    @csrf
                    <div class="form-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="name" placeholder="Author Name" required>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <i class="fas fa-phone"></i>
                                <input type="number" name="phone" value="{{ $phone ?? '' }}" placeholder="Phone"
                                    readonly>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <i class="fas fa-envelope"></i>
                                <input type="email" name="email" value="{{ $email ?? '' }}" placeholder="Email"
                                    readonly>
                            </div>
                        </div>



                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <i class="fas fa-calendar"></i>
                                <input type="date" name="dob" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <i class="fas fa-venus-mars"></i>
                                <select name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>



                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <i class="fas fa-globe"></i>
                                <input type="text" name="country" placeholder="Country" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <i class="fas fa-flag"></i>
                                <input type="text" name="state" placeholder="State" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <i class="fas fa-city"></i>
                                <input type="text" name="city" placeholder="City" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <i class="fas fa-map-pin"></i>
                                <input type="number" name="pincode" placeholder="Pin Code" required>
                            </div>
                        </div>
                    </div>




                    <div class="form-group">
                        <i class="fas fa-map-marker-alt"></i>
                        <input type="text" name="address" placeholder="Address" required>
                    </div>

                    <div class="form-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" placeholder="Password" required>
                    </div>


                    <label class="d-flex gap-2 mb-3">
                        <input type="checkbox" name="terms" required>
                        I accept <a href="#">terms & conditions</a>
                    </label>

                    <button type="submit" class="btn btn-primary">Register</button>

                    <div class="auth-footer mt-3">
                        Already registered? <a href="{{ route('author.login') }}">Login</a><br>
                        Or return to <a href="{{ route('home') }}">Home</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="{{ asset('admin/assets/vendor/jquery/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('admin/assets/vendor/sweet-alert/toastr.min.js') }}"></script>
    <script src="{{ asset('admin/assets/vendor/sweet-alert/sweetalert.min.js') }}"></script>
    <script src="{{ asset('admin/assets/js/ajax-engine-1.0.js') }}"></script>
</body>

</html>
