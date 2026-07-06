@extends('auth/layout')
@section('content')
    <div class="container">
        <div class="sign-up-container">
            <form method="post" action="{{ route('login.otp') }}" id="pwdForm">
                @csrf
                <h1>Reset Password</h1>
                <span class="spantag">Enter password and confirm password.</span>
                <div class="input-grou">
                    <div class="icon">
                        <i class="fa fa-key"></i>
                    </div>
                    <div class="icon1">
                        <i class="fa fa-eye-slash" style="cursor: pointer" onclick="passwordToggle(this)"></i>
                    </div>
                    <input type="password" id="password" name="password" placeholder="Password">
                </div>
                <div class="input-grou">
                    <div class="icon">
                        <i class="fa fa-key"></i>
                    </div>
                    <div class="icon1">
                        <i class="fa fa-eye-slash" style="cursor: pointer" onclick="passwordToggle1(this)"></i>
                    </div>
                    <input type="password" id="cpassword" name="email" placeholder="Confirm Password">
                </div>

                <span class="spantag" style="color: red" id="pwdError"></span>
                <button class="form_btn" type="button" style="background-color: #1877f2; color:#ebecf0"
                    onclick="resetPassword()">Reset</button>
                {{-- <span class="spantag"><a href="#">Forget your password</a></span> --}}
            </form>
        </div>
        <div class="sign-in-container">
            <form method="post" action="{{ route('login.submit') }}" id="otpForm">
                @csrf
                <h1>Forget Password</h1>
                <span class="spantag">Verify email and get otp</span>
                {{-- <input type="email" id="email1" placeholder="Email"
                oninput="this.value = this.value.replace(/[^a-zA-Z0-9._@-]/g, '');"> --}}
                <div class="input-grou" id="emailSection">
                    <div class="icon">
                        <i class="fa fa-envelope"></i>
                    </div>
                    <input type="email" id="email1" placeholder="Email"
                        oninput="this.value = this.value.replace(/[^a-zA-Z0-9._@-]/g, '');">
                </div>
                <input type="text" id="otp" name="otp" placeholder="OTP" minlength="6" maxlength="6"
                    oninput="this.value = this.value.replace(/\D/g, '').substring(0, 10);" hidden>
                <span class="spantag" style="color: red" id="otpError"></span>
                <button class="form_btn" id="sendOTPbtn" onclick="checkOTPUser(1)"
                    style="background-color: #1877f2; color:#ebecf0" type="button">Send OTP</button>
                <button class="form_btn" id="otpLoginBtn" onclick="checkOTPUser(2)"
                    style="background-color: #1877f2; color:#ebecf0" type="button" hidden>Proceed</button>
                <span class="spantag" id="resendOTPText" hidden>OTP Not Received ? <span class="spantag" style="cursor: pointer; color:blue"
                        onclick="resendOTP()" id="countText">Resend</span></span>
            </form>
        </div>
        <div class="overlay-container">
            <div class="overlay-left">
                <img src="{{ asset('assets/logo/logo_dark.png') }}" alt="logo" height="80">
                <p>To keep connected with us please login with your personal information</p>
                {{-- <button id="signIn" class="overlay_btn">With Password</button> --}}
            </div>
            <div class="overlay-right">
                <img src="{{ asset('assets/logo/logo_dark.png') }}" alt="logo" height="80">
                <p>Enter your personal information and start connect with us</p>
                {{-- <button id="signUp" class="overlay_btn">With OTP</button> --}}
            </div>
        </div>
    </div>
    <script>
        var emailInput1 = document.getElementById('email1');
        var emailSec = document.getElementById('emailSection');
        var otpInput1 = document.getElementById('otp');
        var proceedOTP = document.getElementById('sendOTPbtn');
        var SignInOTP = document.getElementById('otpLoginBtn');

        var passwordInput = document.getElementById('password');
        var cpasswordInput = document.getElementById('cpassword');
        var otpErrorField = document.getElementById('otpError');
        var pwdErrorField = document.getElementById('pwdError');
        var resendOTp = document.getElementById('resendOTPText');
        var countTextField = document.getElementById('countText');

        let timeLeft = 30;

        function passwordToggle(e) {
            var icon = 'fa fa-eye-slash';

            if (e.classList.value != icon) {
                e.classList.value = icon;
                passwordInput.type = 'password';
            } else {
                e.classList.value = 'fa fa-eye';
                passwordInput.type = 'text';
            }
        }

        function passwordToggle1(e) {
            var icon = 'fa fa-eye-slash';

            if (e.classList.value != icon) {
                e.classList.value = icon;
                cpasswordInput.type = 'password';
            } else {
                e.classList.value = 'fa fa-eye';
                cpasswordInput.type = 'text';
            }
        }

        function resetPassword() {
            var email = emailInput1.value.trim();
            var password = passwordInput.value;
            var cpassword = cpasswordInput.value;

            if (!password) {
                pwdErrorField.innerHTML = 'Error: Password field can not be empty.';
            } else if (!cpassword) {
                pwdErrorField.innerHTML = 'Error: Confirm password field can not be empty.';
            } else if (password.length < 8) {
                pwdErrorField.innerHTML = 'Error: Password must be more than 8 character.';
            } else if (cpassword.length < 8) {
                pwdErrorField.innerHTML = 'Error: Password not maching.';
            } else if (password != cpassword) {
                pwdErrorField.innerHTML = 'Error: Password not maching.';
            } else {
                pwdErrorField.innerHTML = '';
                $.ajax({
                    url: "{{ url('/login/reset-password') }}",
                    data: {
                        _token: '{{ csrf_token() }}',
                        email: email,
                        confirm: cpassword,
                        password: password,
                    },
                    type: "POST",
                    dataType: 'json',
                    success: function(response) {
                        if (response.status) {
                            Swal.fire({
                                icon: 'success',
                                // title: 'Succes',
                                text: 'Password reset successfully.',
                                timer: 3000,
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed',
                                text: 'Password reset failed.',
                                timer: 3000,
                            });
                        }
                        setTimeout(() => {
                            window.location.href = "{{ url('/login') }}";
                        }, 2500);
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            }
        }

        function checkOTPUser(parm) {
            var email = emailInput1.value.trim();
            var otp = otpInput1.value.trim();

            if (parm == 1) {
                if (emailInput1.value.trim() !== '') {
                    otpErrorField.innerHTML = '';

                    $.ajax({
                        url: "{{ url('/login/check-user') }}",
                        data: {
                            _token: '{{ csrf_token() }}',
                            user: email,
                        },
                        type: "POST",
                        dataType: 'json',
                        success: function(response) {
                            if (response.status) {
                                emailSec.setAttribute('hidden', true);
                                proceedOTP.setAttribute('hidden', true);
                                otpInput1.removeAttribute('hidden');
                                SignInOTP.removeAttribute('hidden');
                                resendOTp.removeAttribute('hidden');
                                resendOTP();
                            } else {
                                otpErrorField.innerHTML = 'Error: Invalid user.';
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error(xhr.responseText);
                        }
                    });
                } else {
                    otpErrorField.innerHTML = 'Error: This field should not be empty.';
                }
            } else if (parm == 2) {

                if (otpInput1.value.trim() !== '') {
                    otpErrorField.innerHTML = '';
                    if (otp.length < 6) {
                        otpErrorField.innerHTML = 'Error: OTP should be 6 digits.';
                    } else {
                        $.ajax({
                            url: "{{ url('/login/check-otp') }}",
                            data: {
                                _token: '{{ csrf_token() }}',
                                otp: otp,
                                user: email
                            },
                            type: "POST",
                            dataType: 'json',
                            success: function(response) {
                                if (response.status) {
                                    container.classList.add("right-panel-active");
                                } else {
                                    otpErrorField.innerHTML = 'Error: Invalid OTP.';
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error(xhr.responseText);
                            }
                        });
                    }

                } else {
                    otpErrorField.innerHTML = 'Error: This field should not be empty.';
                }
            }

        }

        function resendOTP() {
            var email = emailInput1.value.trim();
            $.ajax({
                url: "{{ url('login/otp') }}",
                data: {
                    _token: '{{ csrf_token() }}',
                    email: email,
                },
                type: "POST",
                dataType: 'json',
                success: function(data) {
                    Swal.fire({
                        icon: 'success',
                        // title: 'Succes',
                        text: 'OTP sent successfully.',
                        timer: 3000,
                    });
                }
            });
            countdownInterval = setInterval(updateCountdown, 1000);
        }

        function updateCountdown() {
            countTextField.removeAttribute('onclick');
            countTextField.textContent = 'Wait for ' + timeLeft + ' Sec';

            if (timeLeft > 0) {
                timeLeft--;
            } else {
                countTextField.textContent = 'Resend OTP ';
                timeLeft = 30;
                clearInterval(countdownInterval); // Clear the interval after a single round
                countTextField.setAttribute('onclick', 'resendOTP()');
            }
        }
    </script>
@endsection
