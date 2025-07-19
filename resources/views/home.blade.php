<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Welcome to JASPI</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Fonts & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('admin/assets/css/remixicon.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('admin/assets/vendor/sweet-alert/toastr.min.css') }}">

    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            background: linear-gradient(270deg, #dfe9f3, #fefefe, #e8f0ff);
            background-size: 600% 600%;
            animation: gradientBG 15s ease infinite;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        @keyframes gradientBG {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .main-content {
            width: 90%;
            padding: 40px 25px;
            z-index: 2;
        }

        .quote-section {
            font-size: 1.6rem;
            font-weight: 600;
            color: #2c3e50;
            text-align: center;
            transition: opacity 0.3s ease-in-out;
        }

        .role-option {
            transition: transform 0.3s ease, background-color 0.3s ease;
            border: 2px solid #dee2e6;
            padding: 20px;
            border-radius: 15px;
            width: 140px;
            background-color: #f9f9f9;
            text-decoration: none;
            color: #000;
        }

        .role-option:hover {
            transform: translateY(-5px);
            background-color: #eaf0ff;
            border-color: #b0c7ff;
        }

        .role-option i {
            font-size: 32px;
            color: #4a69bd;
        }

        .role-option .fw-semibold {
            margin-top: 10px;
            font-size: 1.05rem;
        }

        .particle {
            position: absolute;
            background-color: rgba(0, 0, 0, 0.05);
            border-radius: 50%;
            animation: float 20s infinite ease-in-out;
            z-index: 0;
        }

        @keyframes float {
            0% {
                transform: translateY(0) scale(1);
                opacity: 0.4;
            }

            50% {
                transform: translateY(-100px) scale(1.2);
                opacity: 0.8;
            }

            100% {
                transform: translateY(0) scale(1);
                opacity: 0.4;
            }
        }

        @media (max-width: 768px) {
            .quote-section {
                font-size: 1.3rem;
            }

            .role-option {
                width: 100px;
                padding: 15px;
            }
        }

        #continueBtn {
            padding: 8px 24px;
            font-size: 1rem;
            border-radius: 8px;
        }

        .contact-us-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 1000;
            padding: 10px 20px;
            font-size: 1rem;
            border-radius: 8px;
        }

        .modal-content .form-group {
            margin-bottom: 8px;
        }

        .modal-content label {
            display: block;
            margin-bottom: 3px;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .modal-content input,
        .modal-content textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        .modal-content textarea {
            resize: vertical;
            min-height: 80px;
        }

        .modal-content button[type="submit"] {
            width: 100%;
            padding: 8px;
            background-color: #4a69bd;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .modal-content button[type="submit"]:hover {
            background-color: #3b5499;
        }

        .static-content {
            max-width: 50%;
            padding-right: 20px;
        }

        .static-content h2 {
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        .static-content p {
            font-size: 1rem;
            color: #34495e;
            line-height: 1.6;
        }

        .login-section {
            max-width: 40%;
            padding-left: 20px;
            border-left: 2px solid #dee2e6;
        }

        body {
            padding-top: 70px;
        }
    </style>
</head>

<body>
    <header class="bg-white shadow-sm py-3 px-4 w-100 position-fixed top-0 start-0" style="z-index: 1030;">
        <div class="d-flex justify-content-between align-items-center">
            <a href="{{ url('/') }}" class="d-flex align-items-center text-decoration-none">
                <img src="{{ asset('web/logo.jpeg') }}" alt="Logo" style="height: 50px;">
                {{-- <span class="ms-2 fw-bold text-dark">JASPI</span> --}}
            </a>
            <button class="btn btn-primary contact-us-btn actionHandler" data-url="{{ route('web.contact') }}">
                Contact Us
            </button>
        </div>
    </header>

    <div class="modal" id="AjaxModal" data-bs-backdrop="static" tabindex="-1"></div>


    <svg style="position: absolute; top: -50px; left: -50px; z-index: 0;" width="400" height="400"
        viewBox="0 0 200 200">
        <path fill="#d0e2ff"
            d="
        M43.2,-70.6C54.1,-63.7,59.2,-45.8,63.3,-30.4C67.4,-15.1,70.4,-2.5,66.8,9.3C63.1,21.2,52.7,32.2,41.5,41.7C30.3,51.3,18.1,59.3,3.2,62.5C-11.7,65.7,-23.4,64.2,-33.9,58.3C-44.3,52.3,-53.4,41.9,-60.5,29.7C-67.6,17.5,-72.8,3.5,-69.6,-8.5C-66.5,-20.4,-54.9,-30.2,-43.8,-38.4C-32.6,-46.5,-21.9,-53,-8.3,-62.1C5.2,-71.3,26.1,-82.4,43.2,-70.6Z"
            transform="translate(100 100)" />
    </svg>
    <div class="particle" style="width: 60px; height: 60px; top: 80%; left: 10%;"></div>
    <div class="particle" style="width: 40px; height: 40px; top: 20%; left: 80%; animation-delay: 3s;"></div>
    <div class="particle" style="width: 20px; height: 20px; top: 50%; left: 50%; animation-delay: 6s;"></div>

    <div class="main-content">
        <div class="d-flex align-items-center">
            <div class="static-content">
                <h5>Welcome to JASPI- Journal of Antimicrobial Stewardship Practices and Infectious Diseases.</h5>
                <hr> <br>
                <div class="row">
                    <div class="col-4">
                        <img class="img-fluid rounded" src="{{ asset('web/updates.jpeg') }}"
                            style="border: 1px solid #b0acac;">
                    </div>
                    <div class="col-8">
                        {{-- <h5 class="fw-bold text-primary mb-3">
                            Explore the Journal
                        </h5>
                        <p class="text-muted mb-3">
                            Journal of Antimicrobial Stewardship Practices and Infectious Diseases.
                        </p> --}}
                        <ul class="list-unstyled">
                            <br>
                            <li class="mb-2">
                                <i class="ri-earth-line me-2 text-primary"></i>
                                <a href="https://jaspi.saspi.in" class="text-decoration-none text-muted fw-medium"
                                    target="_blank">
                                    Visit our website
                                </a>
                            </li>
                            <li class="mb-2">
                                <i class="ri-file-list-3-line me-2 text-primary"></i>
                                <a href="https://jaspi.saspi.in/publications/"
                                    class="text-decoration-none  text-muted  fw-medium" target="_blank">
                                    Current Issue
                                </a>
                            </li>
                            <li class="mb-2">
                                <i class="ri-edit-line me-2 text-primary"></i>
                                <a href="https://jaspi.saspi.in/authors-instructions_jaspi/"
                                    class="text-decoration-none  text-muted  fw-medium" target="_blank">
                                    Instructions to Author
                                </a>
                            </li>
                            <li class="mb-2">
                                <i class="ri-information-line me-2 text-primary"></i>
                                <a href="https://jaspi.saspi.in/" class="text-decoration-none  text-muted  fw-medium"
                                    target="_blank">
                                    About the Journal
                                </a>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>
            <div class="login-section text-center">
                <h4 class="fw-bold">Sign In</h4>
                <br>
                <h5 class="text-muted">Please select your role to continue</h5>

                <!-- Role Selector -->
                <div class="mt-0" style="margin-bottom: 5px ! important;">
                    <select id="loginSelector" class="form-select w-100">
                        <option selected disabled>Choose your role</option>
                        <option value="{{ route('admin.login') }}">Admin</option>
                        <option value="{{ route('admin.login') }}">Editor</option>
                        <option value="{{ route('admin.login') }}">Reviewer</option>
                        <option value="{{ route('admin.login') }}">Publisher</option>
                        <option value="{{ route('author.login') }}">Author</option>
                    </select>
                </div>

                <!-- Login Form -->
                <div class="mt-4">
                    <form class="login-form p-0" id="roleLoginForm" method="POST" novalidate>
                        @csrf

                        <div class="mb-3">
                            <div class="input-group">
                                <i class="fas fa-user"></i>
                                <input type="text" name="username" required class="form-control"
                                    placeholder="User Email Id">
                            </div>
                            <span class="text-danger" data-error="username"></span>
                        </div>


                        <div class="mb-1">
                            <div class="input-group">
                                <i class="fas fa-lock"></i>
                                <div class="position-relative auth-pass-inputgroup w-100">
                                    <input type="password" class="form-control password-input passbox pe-5"
                                        placeholder="Your password" required="" name="password"
                                        id="password-input">
                                    <button
                                        class="btn h-100 position-absolute text-decoration-none text-muted password-addon end-0 top-0 me-1"
                                        type="button" id="password-addon">
                                        <i class="ri-eye-fill align-middle"></i>
                                    </button>
                                </div>
                            </div>
                            <span class="text-danger" data-error="password"></span>
                        </div>





                        <button type="submit" class="btn btn-primary mt-3 w-100" id="loginBtn"
                            disabled>Login</button>
                    </form>
                    <!-- Forgot Password Link -->
                    <div class="text-end mt-3">
                        <button class="text-decoration-none text-muted fw-medium actionHandler"
                            data-url="{{ route('forgot_password') }}" style="border: none; background: none;">Forgot
                            Password?</button>
                    </div>

                </div>

                <!-- Register Link -->
                <div class="mt-5">
                    <a href="{{ route('author.userCheck') }}" class="btn btn-outline-secondary w-100">Register as an
                        Author</a>
                </div>
                {{-- <div class="mt-4">
                    <div id="quoteBox" class="quote-section">
                        Write your legacy.
                    </div>
                </div> --}}
            </div>
        </div>
    </div>
    <script src="{{ asset('admin/assets/vendor/global/global.min.js') }}"></script>
    <script src="{{ asset('admin/assets/vendor/jquery/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('admin/assets/vendor/sweet-alert/toastr.min.js') }}"></script>
    <script src="{{ asset('admin/assets/js/ajax-engine-1.0.js') }}"></script>


    <script src="{{ asset('admin/assets/vendor/bootstrap-select/dist/js/bootstrap-select.min.js') }}"></script>
    <script src="{{ asset('admin/assets/vendor/sweet-alert/sweetalert.min.js') }}"></script>


    {{-- <script>
        const quotes = [
            "Write your legacy.",
            "Publish with purpose.",
            "Every story starts here.",
            "Where ideas become reality."
        ];
        let quoteIndex = 0;
        const quoteBox = document.getElementById("quoteBox");

        setInterval(() => {
            quoteIndex = (quoteIndex + 1) % quotes.length;
            quoteBox.style.opacity = 0;
            setTimeout(() => {
                quoteBox.textContent = quotes[quoteIndex];
                quoteBox.style.opacity = 1;
            }, 300);
        }, 3000);
    </script> --}}
    <script>
        const selector = document.getElementById('loginSelector');
        const form = document.getElementById('roleLoginForm');
        const loginBtn = document.getElementById('loginBtn');

        selector.addEventListener('change', function() {
            const route = this.value;
            if (route) {
                form.action = route;
                loginBtn.disabled = false;
            } else {
                form.action = '';
                loginBtn.disabled = true;
            }
        });

    //     // Toggle password visibility
    //     const togglePassword = document.getElementById('password-addon');
    //     const passwordInput = document.getElementById('password-input');
    //     togglePassword.addEventListener('click', function() {
    //         const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    //         passwordInput.setAttribute('type', type);
    //         this.querySelector('i').classList.toggle('ri-eye-fill');
    //         this.querySelector('i').classList.toggle('ri-eye-off-fill');
    //     });
    // </script>
</body>

</html>
