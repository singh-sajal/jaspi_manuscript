@extends('includes.authlayout')

@section('content')
    <div class="login-form">
        <div class="text-start">
            <h3 class="title">Two Step Verification</h3>
            <p>Enter Verification code to Continue</span></p>
        </div>
        <form action="{{ route('admin.2fa.login', ['uuid' => $user->uuid, 'callback_hash' => $callback_hash]) }}"
            method="POST" autocomplete="off" novalidate>
            @csrf
            <div class="col-12">
                <div class="p-lg-5 p-4">
                    <div class="mb-4">
                        <div class="avatar-lg mx-auto text-center">
                            <div class="avatar bg-light text-primary display-5 rounded-circle shadow">
                                <i class="ri-mail-line"></i>
                            </div>
                        </div>
                    </div>
                    <div class="text-muted mx-lg-3 text-center">
                        <h4 class="">Verify Your Account</h4>
                        <p>Please enter the 4 digit code sent to <span class="fw-semibold">{{ $user->email ?? '' }}</span>
                        </p>
                    </div>

                    <div class="mt-4">

                        <div class="row">
                            <div class="col-3">
                                <div class="mb-3">
                                    <label for="digit1-input" class="visually-hidden">Digit 1</label>
                                    <input type="text" required
                                        class="form-control form-control-lg bg-light border-primary otp-input text-center"
                                        onkeyup="moveToNext(1, event)" maxlength="1" id="digit1-input" autocomplete="off"
                                        name="otp[]">
                                </div>
                            </div>

                            <div class="col-3">
                                <div class="mb-3">
                                    <label for="digit2-input" class="visually-hidden">Digit 2</label>
                                    <input type="text" required
                                        class="form-control form-control-lg bg-light border-primary otp-input text-center"
                                        onkeyup="moveToNext(2, event)" maxlength="1" id="digit2-input" name="otp[]">
                                </div>
                            </div><!-- end col -->

                            <div class="col-3">
                                <div class="mb-3">
                                    <label for="digit3-input" class="visually-hidden">Digit 3</label>
                                    <input type="text" required
                                        class="form-control form-control-lg bg-light border-primary otp-input text-center"
                                        onkeyup="moveToNext(3, event)" maxlength="1" id="digit3-input" name="otp[]">
                                </div>
                            </div><!-- end col -->

                            <div class="col-3">
                                <div class="mb-3">
                                    <label for="digit4-input" class="visually-hidden">Digit 4</label>
                                    <input type="text" required
                                        class="form-control form-control-lg bg-light border-primary otp-input text-center"
                                        onkeyup="moveToNext(4, event)" maxlength="1" id="digit4-input" name="otp[]">
                                </div>
                            </div><!-- end col -->
                        </div>
                        <span class="text-danger mt-1" data-error="otp[]"></span>
                        <div class="mt-4">
                            <button class="btn btn-primary w-100" type="submit">Login</button>
                        </div>



                    </div>


                </div>
            </div>

        </form>
        <p class="mb-3 mt-3 text-center">Did not receive code?

            <a href="#"
                data-url="{{ route('verification.resend-code', ['email' => $user->email, 'uuid' => $user->uuid, 'module' => 'login']) }}"
                data-handler="resendVerificationCode"
                class="fw-semibold text-primary text-decoration-underline actionHandler">
                press here</a> or go back to <a href="{{ route('admin.login') }}"
                class="fw-semibold text-primary text-decoration-underline">
                Login</a> Page
        </p>
    </div>
@endsection
@section('javascripts')
    <script>
        function getInputElement(e) {
            return document.getElementById("digit" + e + "-input");
        }

        function moveToNext(e, t) {
            t = t.which || t.keyCode;

            // Handle if the user enters a single character in the input
            if (1 === getInputElement(e).value.length) {
                // If the current field is not the last one, move focus to the next input field
                if (e < 4) {
                    getInputElement(e + 1).focus();
                } else {
                    getInputElement(e).blur();
                    console.log("Submit code");
                }
            }

            // Handle backspace (moving focus to the previous field)
            if (8 === t && e > 1) {
                getInputElement(e - 1).focus();
            }
        }

        function handlePaste(e) {
            let pastedData = e.clipboardData.getData('text');
            let otpFields = document.querySelectorAll('.otp-input');

            // Ensure that pasted data fills all the input fields
            let index = 0;
            let lastFocused = null; // Variable to store the last focused field

            for (let i = 0; i < otpFields.length; i++) {
                if (index < pastedData.length) {
                    otpFields[i].value = pastedData[index];
                    index++;
                    lastFocused = otpFields[i]; // Update the last focused field
                }
            }

            // After filling, focus on the last field that was filled
            if (lastFocused) {
                lastFocused.focus();
            }
        }

        // Add event listener for the paste event
        document.querySelectorAll('.otp-input').forEach(input => {
            input.addEventListener('paste', handlePaste);
        });


        window.resendVerificationCode = (button) => {
            const url = button.dataset.url;
            const originalHTML = button.innerHTML;
            button.disabled = true;
            button.innerHTML = 'Sending...';
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
                        return;
                    }
                    toastr.error('Unable to resend code');
                })
                .catch((error) => {
                    toastr.error(error);
                }).finally(() => {
                    button.disabled = false;
                    button.innerHTML = originalHTML;
                });
        }
    </script>
@endsection
