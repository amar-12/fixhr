@extends('auth.layout')
@section('content')
    <div class="container">
        <div class="sign-up-container">
            <form method="post" action="{{ route('login.submit') }}" id="otpForm">
                @csrf
                <h1>Sign In</h1>
                <span class="spantag">Use email and OTP for login</span>

                <div class="input-grou" id="emailSection">
                    <div class="icon">
                        <i class="fa fa-envelope"></i>
                    </div>
                    <input type="email" id="email1" placeholder="Email"
                        oninput="this.value = this.value.replace(/[^a-zA-Z0-9._@-]/g, '');">
                </div>
                
                <!-- Error field for email validation -->
                <span class="spantag" style="color: red; display: none;" id="emailError"></span>
                
                <!-- 2FA Section -->
                <div id="twoFASection" hidden>
                    <div class="input-grou">
                        <div class="icon">
                            <i class="fa fa-mobile"></i>
                        </div>
                        <input type="text" id="twoFACode" name="twoFACode" placeholder="Enter 2FA Code" 
                               minlength="6" maxlength="6" oninput="this.value = this.value.replace(/\D/g, '').substring(0, 6);">
                    </div>
                    <span class="spantag" style="color: red; display:none;" id="twoFAError"></span>
                      <button class="form_btn" id="twoFALoginBtn" style="background-color: #1877f2; color:#ebecf0; margin:20px 55px"
                            onclick="verify2FA()" type="button">Verify 2FA</button>
                            
                    <div class="spantag" style="margin-top: 15px; text-align: center;">
                        <a href="#" onclick="tryAnotherMethod()" style="color: #1877f2; text-decoration: none;">Try another method</a>
                    </div>
                </div>

                <input type="text" id="otp" name="otp" placeholder="OTP" minlength="6" maxlength="6"
                    oninput="this.value = this.value.replace(/\D/g, '').substring(0, 10);" hidden>
                <input type="hidden" id="twoFACodeHidden" name="twoFACode">
                <span class="spantag" style="color: red; display: none;" id="emailOtpError"></span>

                <button class="form_btn" id="sendOTPbtn" onclick="checkOTPUser(1)"
                    style="background-color: #1877f2; color:#ebecf0" type="button">Send OTP</button>


                <button class="form_btn" id="otpLoginBtn" style="background-color: #1877f2; color:#ebecf0"
                    onclick="checkOTPUser(2)" type="button" hidden>Log In</button>
                <div class="spantag" id="backToEmailLink" hidden style="margin-top: 15px; text-align: center;">
                    <a href="#" onclick="backToEmail()" style="color: #1877f2; text-decoration: none;">← Back to email</a>
                </div>

                <span class="spantag" id="resendOTPText" hidden>OTP Not Received ? <span class="spantag"
                        style="cursor: pointer; color:blue" onclick="resendOTP()" id="countText">Resend</span></span>
            </form>
        </div>
        <div class="sign-in-container">
            <form method="post" action="{{ route('login.otp') }}" id="pwdForm">
                @csrf
                <h1>Sign In</h1>
                <span class="spantag">Use email and password for login.</span>

                <div class="input-grou">
                    <div class="icon">
                        <i class="fa fa-envelope"></i>
                    </div>
                    <input type="email" id="email2" name="email" placeholder="Email"
                        oninput="this.value = this.value.replace(/[^a-zA-Z0-9._@-]/g, '');" autofocus>
                </div>
                <div class="input-grou">
                    <div class="icon">
                        <i class="fa fa-key"></i>
                    </div>
                    <div class="icon1">
                        <i class="fa fa-eye-slash" style="cursor: pointer" onclick="passwordToggle(this)"></i>
                    </div>
                    <input type="password" id="password" name="password" placeholder="Password" required>
                </div>
                
                <!-- 2FA Section for Password Login -->
                <div id="password2FASection" hidden>
                    <div class="input-grou">
                        <div class="icon">
                            <i class="fa fa-mobile"></i>
                        </div>
                        <input type="text" id="password2FACode" name="password2FACode" placeholder="Enter 2FA Code" 
                               minlength="6" maxlength="6" oninput="this.value = this.value.replace(/\D/g, '').substring(0, 6);">
                    </div>
                  
                </div>
                
                <span class="spantag" style="color: red;" id="pwdError" ></span>
                <span class="spantag" style="color: red;" id="pwdError2" ></span>
                <button class="form_btn" type="submit" style="background-color: #1877f2; color:#ebecf0"
                    id="loginWithPassBtn">Log In</button>
                <span class="spantag"><a href="{{ url('/login/forget-password') }}">Forget password</a></span>
                <span  class="spantag" id="backToPasswordFieldsBtn" hidden style= "cursor: pointer; background-color: #6c757d;color:#fff;padding: 7px 10px;margin-top: 10px;border-radius: 10px;box-shadow: 0 0 10px 0 rgba(0, 0, 0, 0.1);" onclick="backToPasswordFields()">Back</span>
            </form>
        </div>
        <div class="overlay-container">
            <div class="overlay-left">
                <img src="{{ asset('assets/logo/logo_dark.png') }}" alt="logo" height="80">
                <p>Welcome, HR Admin! Securely sign in with your OTP to access your account.</p>
                <button id="signIn" class="overlay_btn">With Password</button>
                <span class="spantag" style="color: white">I don't have any account ? <a href="{{ url('/signup') }}"
                        style="color: yellow">Create</a></span>
            </div>
            <div class="overlay-right">
                <img src="{{ asset('assets/logo/logo_dark.png') }}" alt="logo" height="80">
                <p>Welcome, HR Admin! Sign In with your password to access and manage your team efficiently</p>
                <button id="signUp" class="overlay_btn">With OTP</button>
                <span class="spantag" style="color: white">I don't have any account ? <a href="{{ url('/signup') }}"
                        style="color: yellow">Create</a></span>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <style>
        .error-message {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
            display: none;
            text-align: left;
        }
        
        .error-message.show {
            display: block;
        }
        
        #twoFAError, #pwdError {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
            display: none;
            text-align: left;
        }
    </style>
    <script>
        var emailInput1 = document.getElementById('email1');
        var emailSec = document.getElementById('emailSection');
        var otpInput1 = document.getElementById('otp');
        var proceedOTP = document.getElementById('sendOTPbtn');
        var SignInOTP = document.getElementById('otpLoginBtn');
        var twoFASection = document.getElementById('twoFASection');
        var twoFACodeInput = document.getElementById('twoFACode');

        var emailInput2 = document.getElementById('email2');
        var passwordInput = document.getElementById('password');
        var password2FASection = document.getElementById('password2FASection');
        var password2FACodeInput = document.getElementById('password2FACode');
        var backToPasswordFieldsBtn = document.getElementById('backToPasswordFieldsBtn');
        var emailErrorField = document.getElementById('emailError');
        var emailOtpErrorField = document.getElementById('emailOtpError');
        var twoFAErrorField = document.getElementById('twoFAError');
        
        var pwdErrorField = document.getElementById('pwdError');
        var pwdErrorField2 = document.getElementById('pwdError2');
        var resendOTp = document.getElementById('resendOTPText');
        var countTextField = document.getElementById('countText');

        let timeLeft = 30;
        let selectedAuthMethod = null;
        let userHas2FA = false;

        // Helper function to show errors
        function showError(field, message) {
            if (field) {
                field.textContent = message;
                field.style.display = 'block';
                field.style.color = 'red';
                console.log('Error displayed:', message, 'Field:', field.id, 'Display:', field.style.display);
            } else {
                console.error('Error field not found for message:', message);
            }
        }

        // Helper function to hide errors
        function hideError(field) {
            if (field) {
                field.textContent = '';
                field.style.display = 'none';
            }
        }

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

        $(document).on('click', '#loginWithPassBtn', function() {
            var Email = emailInput2.value.trim();
            var validateMail = String(Email).toLowerCase().match(
                /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|.(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
                );
            var password = passwordInput.value;

            if (Email && password) {
                if (!validateMail) {
                    showError(pwdErrorField, 'Invalid email format.');
                    return false;
                } else if (password.length < 8) {
                    showError(pwdErrorField, 'Password must be at least 8 characters long.');
                    return false;
                } else {
                    hideError(pwdErrorField);

                    // Move the processing state to here, after successful validation
                    $('#loginWithPassBtn').prop('disabled', true);
                    $('#loginWithPassBtn').text('processing...');

                    $.ajax({
                        url: "{{ url('/login/check-password') }}",
                        data: {
                            _token: '{{ csrf_token() }}',
                            email: Email,
                            password: password,
                        },
                        type: "POST",
                        dataType: 'json',
                        success: function(response) {
                            $('#loginWithPassBtn').prop('disabled', false)
                            if (response.status) {
                                // Check if user has 2FA enabled
                                if (response.has2FA) {
                                    userHas2FA = true;
                                    // Hide email and password fields
                                    emailInput2.parentElement.setAttribute('hidden', true);
                                    passwordInput.parentElement.setAttribute('hidden', true);
                                    // Show 2FA section
                                    password2FASection.removeAttribute('hidden');
                                    backToPasswordFieldsBtn.removeAttribute('hidden');
                                    $('#loginWithPassBtn').text('Verify 2FA & Login');
                                    $('#loginWithPassBtn').attr('onclick', 'verifyPassword2FA()');
                                    
                                } else {
                                    // No 2FA required, submit form directly
                                    document.getElementById('pwdForm').submit();
                                }
                            } else {
                                $('#loginWithPassBtn').text('Log In');
                                showError(pwdErrorField, response.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            $('#loginWithPassBtn').text('Log In');
                            $('#loginWithPassBtn').prop('disabled', false)
                            showError(pwdErrorField, 'Network error. Please try again.');
                        }
                    });
                }
            } else {
                if (!Email) {
                    showError(pwdErrorField, 'Email field should not be empty.');
                } else if (!password) {
                    showError(pwdErrorField, 'Password field should not be empty.');
                }
                return false;
            }
        });

        function verifyPassword2FA() {
            var Email = emailInput2.value.trim();
            var password = passwordInput.value;
            var twoFACode = password2FACodeInput.value.trim();
            // console.log(twoFACode);
            
            
            // Clear any previous errors first
            hideError(pwdErrorField);
            
            if (twoFACode === '') {
                console.log('2FA code is empty');
                showError(pwdErrorField2, '2FA code should not be empty.');
                return;
            }
            
            if (twoFACode.length !== 6) {
                showError(pwdErrorField2, '2FA code should be exactly 6 digits.');
                return;
            }
            
            // Validate 2FA code format (only numbers)
            if (!/^\d{6}$/.test(twoFACode)) {
                showError(pwdErrorField2, '2FA code should contain only 6 digits.');
                return;
            }
            
            // All validation passed, hide any errors
            hideError(pwdErrorField2);
            
            $('#loginWithPassBtn').prop('disabled', true);
            $('#loginWithPassBtn').text('Verifying...');
            
            $.ajax({
                url: "{{ url('/login/verify-password-2fa') }}",
                data: {
                    _token: '{{ csrf_token() }}',
                    email: Email,
                    password: password,
                    twoFACode: twoFACode
                },
                type: "POST",
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        // 2FA verified, now submit the form
                        // Add a small delay to ensure everything is processed
                        setTimeout(function() {
                            document.getElementById('pwdForm').submit();
                        }, 100);
                         } else if (response.lockout_seconds) {
                        showLockoutTimer(
                            pwdErrorField2,
                            response.message ? (response.message + '') : '',
                            response.lockout_seconds,
                            [password2FACodeInput, document.getElementById('loginWithPassBtn')],
                            null,
                            'twofaPwdLockout');
                        $('#loginWithPassBtn').text('Verify 2FA & Login');
                    } else {
                        $('#loginWithPassBtn').prop('disabled', false);
                        $('#loginWithPassBtn').text('Verify 2FA & Login');
                        showError(pwdErrorField2, response.message);
                    }
                },
                error: function(xhr, status, error) {
                    $('#loginWithPassBtn').prop('disabled', false);
                    $('#loginWithPassBtn').text('Verify 2FA & Login');
                    showError(pwdErrorField2, 'Network error. Please try again.');
                }
            });
        }

        // Add function to go back to email/password fields
        
        function backToPasswordFields() {
            // Show email and password fields again
            emailInput2.parentElement.removeAttribute('hidden');
            passwordInput.parentElement.removeAttribute('hidden');
            // Hide 2FA section
            password2FASection.setAttribute('hidden', true);
            backToPasswordFieldsBtn.setAttribute('hidden', true);
            // Reset button and clear 2FA input
            $('#loginWithPassBtn').text('Log In');
            $('#loginWithPassBtn').prop('disabled', false);
            $('#loginWithPassBtn').removeAttr('onclick');
            // Clear error message and 2FA input
            hideError(pwdErrorField2);
            password2FACodeInput.value = '';
        }

        // Make function globally accessible
        window.backToPasswordFields = backToPasswordFields;


        function checkOTPUser(parm) {
            var email = emailInput1.value.trim();
            var otp = otpInput1.value.trim();
            
            // Input validation
            if (parm == 1) {
                if (emailInput1.value.trim() === '') {
                    showError(emailErrorField, 'Email field should not be empty.');
                    return;
                }

                // Basic email validation
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    showError(emailErrorField, 'Please enter a valid email address.');
                    return;
                }
                
                hideError(emailErrorField);
                $.ajax({
                    url: "{{ url('/login/check-user') }}",
                    data: {
                        _token: '{{ csrf_token() }}',
                        user: email,
                    },
                    type: "POST",
                    dataType: 'json',
                    beforeSend: function() {
                        $('#sendOTPbtn').prop('disabled', true);
                        $('#sendOTPbtn').text('processing...');
                    },
                    success: function(response) {
                        $('#sendOTPbtn').prop('disabled', false);
                        $('#sendOTPbtn').text('Send OTP');
                        if (response.status) {
                            // Google-style flow: Go directly to 2FA if enabled, otherwise to email OTP
                            emailSec.setAttribute('hidden', true);
                            proceedOTP.setAttribute('hidden', true);
                            
                            if (response.has2FA) {
                                // User has 2FA enabled, show 2FA section directly
                                twoFASection.removeAttribute('hidden');
                                twoFACodeInput.focus();
                                // showError(twoFAErrorField, '');
                            } else {
                                // User doesn't have 2FA, show email OTP
                                otpInput1.removeAttribute('hidden');
                                SignInOTP.removeAttribute('hidden');
                                document.getElementById('backToEmailLink').removeAttribute('hidden');
                                resendOTp.removeAttribute('hidden');
                                resendOTP();
                            }
                        } else {
                            // Show server error message in email error field since email section is still visible
                            showError(emailErrorField, response.message);
                            console.log('Server error:', response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#sendOTPbtn').text('Send OTP');
                        $('#sendOTPbtn').prop('disabled', false);
                        showError(emailErrorField, 'Network error. Please try again.');
                        console.log('AJAX error:', xhr.responseText);
                    }
                });
            } else if (parm == 2) {

                if (otpInput1.value.trim() === '') {
                    showError(emailOtpError, 'OTP field should not be empty.');
                    return;
                }
                
                if (otp.length < 6) {
                    showError(emailOtpError, 'OTP should be 6 digits.');
                    return;
                }
                
                // Validate OTP format (only numbers)
                if (!/^\d{6}$/.test(otp)) {
                        showError(emailOtpError, 'OTP should contain only 6 digits.');
                    return;
                }
                
                hideError(emailOtpError);
                $.ajax({
                    url: "{{ url('/login/check-otp') }}",
                    data: {
                        _token: '{{ csrf_token() }}',
                        otp: otp,
                        user: email
                    },
                    type: "POST",
                    dataType: 'json',
                    beforeSend: function() {
                        $('#otpLoginBtn').prop('disabled', true);
                        $('#otpLoginBtn').text('processing...');
                    },
                    success: function(response) {
                        $('#otpLoginBtn').prop('disabled', false);
                        $('#otpLoginBtn').text('Log In');
                        if (response.status) {
                            // OTP verified, submit the form
                            document.getElementById('otpForm').submit();
                              } else if (response.lockout_seconds) {
                            showLockoutTimer(
                                emailOtpError,
                                response.message ? (response.message + '') : '',
                                response.lockout_seconds,
                                [otpInput1, SignInOTP],
                                null,
                                'otpLockout');
                        } else {
                            showError(emailOtpError, response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#otpLoginBtn').prop('disabled', false);
                        $('#otpLoginBtn').text('Log In');
                        showError(emailOtpError, 'Network error. Please try again.');
                    }
                });
            }
        }

        function resendOTP() {
            // Clear any existing countdown first
            if (typeof countdownInterval !== 'undefined') {
                clearInterval(countdownInterval);
            }
            
            var email = emailInput1.value.trim();
            $.ajax({
                url: "{{ url('login/otp') }}",
                data: {
                    _token: '{{ csrf_token() }}',
                    email: email,
                },
                type: "POST",
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        // Reset countdown variables
                        timeLeft = 30;
                        // Start fresh countdown
                        countdownInterval = setInterval(updateCountdown, 1000);
                    } else {
                        showError(emailOtpError, response.message);
                    }
                },
                error: function(xhr, status, error) {
                    showError(emailOtpError, 'Error sending OTP. Please try again.');
                }
            });
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

        function verify2FA() {
            var email = emailInput1.value.trim();
            var twoFACode = twoFACodeInput.value.trim();
            
            if (twoFACode === '') {
                showError(twoFAErrorField, '2FA code should not be empty.');
                return;
            }
            
            if (twoFACode.length !== 6) {
                showError(twoFAErrorField, '2FA code should be exactly 6 digits.');
                return;
            }
            
            // Validate 2FA code format (only numbers)
            if (!/^\d{6}$/.test(twoFACode)) {
                showError(twoFAErrorField, '2FA code should contain only 6 digits.');
                return;
            }
            
            hideError(twoFAErrorField);
            
            $.ajax({
                url: "{{ url('/login/verify-2fa-public') }}",
                data: {
                    _token: '{{ csrf_token() }}',
                    email: email,
                    twoFACode: twoFACode
                },
                type: "POST",
                dataType: 'json',
                beforeSend: function() {
                    $('#twoFALoginBtn').prop('disabled', true);
                    $('#twoFALoginBtn').text('Verifying...');
                },
                success: function(response) {
                    $('#twoFALoginBtn').prop('disabled', false);
                    if (response.status) {
                        // 2FA verified successfully, now submit the form
                        // Set the 2FA code in the hidden input
                        document.getElementById('twoFACodeHidden').value = twoFACode;
                        
                        // Add a small delay to ensure session is set
                        setTimeout(function() {
                            document.getElementById('otpForm').submit();
                        }, 100);
                             } else if (response.lockout_seconds) {
                        showLockoutTimer(
                            twoFAErrorField,
                            response.message ? (response.message + '') : '',
                            response.lockout_seconds,
                            [twoFACodeInput, document.getElementById('twoFALoginBtn')],
                            null,
                            'twofaPublicLockout');
                        $('#twoFALoginBtn').text('Verify 2FA');
                    } else {
                        $('#twoFALoginBtn').text('Verify 2FA');
                        showError(twoFAErrorField, response.message);
                    }
                },
                error: function(xhr, status, error) {
                    $('#twoFALoginBtn').prop('disabled', false);
                    $('#twoFALoginBtn').text('Verify 2FA');
                    showError(twoFAErrorField, 'Network error. Please try again.');
                }
            });
        }

        function tryAnotherMethod() {
            // Clear any existing countdown
            if (typeof countdownInterval !== 'undefined') {
                clearInterval(countdownInterval);
            }
            
            // Reset countdown variables
            timeLeft = 30;
            countTextField.textContent = 'Resend OTP';
            countTextField.setAttribute('onclick', 'resendOTP()');
            
            // Hide 2FA section
            twoFASection.setAttribute('hidden', true);
            // Show email OTP section
            otpInput1.removeAttribute('hidden');
            SignInOTP.removeAttribute('hidden');
            document.getElementById('backToEmailLink').removeAttribute('hidden');
            resendOTp.removeAttribute('hidden');
            resendOTP();
        }

        function backToEmail() {
            // Clear any existing countdown
            if (typeof countdownInterval !== 'undefined') {
                clearInterval(countdownInterval);
            }
            
            // Reset countdown variables
            timeLeft = 30;
            countTextField.textContent = 'Resend OTP';
            countTextField.setAttribute('onclick', 'resendOTP()');
            
            // Hide all sections
            twoFASection.setAttribute('hidden', true);
            otpInput1.setAttribute('hidden', true);
            SignInOTP.setAttribute('hidden', true);
            document.getElementById('backToEmailLink').setAttribute('hidden', true);
            resendOTp.setAttribute('hidden', true);
            $('#sendOTPbtn').text('Send OTP');
            // Show email section and send OTP button
            emailSec.removeAttribute('hidden');
            proceedOTP.removeAttribute('hidden');
            
            // Clear inputs and errors
            hideError(twoFAErrorField);
            twoFACodeInput.value = '';
            otpInput1.value = '';
        }

        // Make function globally accessible for inline onclick handlers
        window.backToEmail = backToEmail;

        // Add event listeners for back buttons
        $(document).ready(function() {
            $('#backToEmail').on('click', function() {
                backToEmail();
            });
        });



         // --- Countdown Timer for Lockout (OTP/2FA) ---
        let lockoutIntervals = {}; // Store one interval per context (otp, 2fa, pwd2fa)
        let lockoutRemaining = {}; // Track remaining seconds per context

        /**
         * Shows a countdown timer in the error field with the remaining lockout time,
         * disables the input/button, and auto-enables once countdown ends.
         * @param {HTMLElement} field - The error message element
         * @param {string} prefix - Message prefix before timer display
         * @param {number} seconds - Total seconds to count down
         * @param {HTMLElement[]} disableEls - Elements to disable (inputs/buttons)
         * @param {function=} onDone - Callback when timer ends
         * @param {string=} ctx - Optional context to allow independent timers (default: '')
         */
        function showLockoutTimer(field, prefix, seconds, disableEls, onDone, ctx) {
            if (!ctx) ctx = '';
            // If the timer is active and has more or equal seconds, do not reset
            if (lockoutIntervals[ctx] && lockoutRemaining[ctx] && lockoutRemaining[ctx] > seconds) {
                // just update error field contents
                if (field) field.style.display = 'block';
                return;
            }
            // If new seconds is more, clear old interval and update to new duration
            if (lockoutIntervals[ctx]) clearInterval(lockoutIntervals[ctx]);
            let remaining = seconds;
            lockoutRemaining[ctx] = remaining;
            if (field) {
                field.style.display = 'block';
                field.style.color = 'red';
            }
            // disableEls.forEach(el => { if(el) el.disabled = true; });
            function update() {
                let min = Math.floor(remaining / 60);
                let sec = remaining % 60;
                let timerString =
                    (min < 10 ? "0" : "") + min + " min " + (sec < 10 ? "0" : "") + sec + " sec";
                if (field) field.textContent = prefix + timerString;
                lockoutRemaining[ctx] = remaining;
                if (remaining <= 0) {
                    clearInterval(lockoutIntervals[ctx]);
                    delete lockoutRemaining[ctx];
                    if (field) hideError(field);
                    if (onDone) onDone();
                    return;
                }
                remaining--;
            }
            update();
            lockoutIntervals[ctx] = setInterval(update, 1000);
        }
    </script>
@endsection
