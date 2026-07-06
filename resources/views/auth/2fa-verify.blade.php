@extends('auth.layout')

@section('content')
<div style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; display: flex; align-items: center; justify-content: center; background: transparent; z-index: 10;">
    <div style="background: #ebecf0; border-radius: 20px; box-shadow: -5px -5px 15px #fff, 5px 5px 15px #babebc; padding: 40px 32px; width: 100%; max-width: 400px; text-align: center;">
        <div style="margin-bottom: 24px;">
            <span class="fa fa-shield" style="font-size: 48px; color: #1877f2; background: #fff; border-radius: 50%; box-shadow: 0 2px 8px #babebc; padding: 16px;"></span>
        </div>
        <h2 style="font-weight: 700; color: #222; margin-bottom: 8px;">Two-Factor Authentication</h2>
        <p style="color: #555; font-size: 15px; margin-bottom: 24px;">Enter the 6-digit code from your authenticator app to continue.</p>
    
        <form method="POST" action="{{ route('2fa.verify') }}" style="display: flex; flex-direction: column; align-items: center;">
            @csrf
            <div class="input-grou" style="width: 100%; margin-bottom: 18px;margin-right: 13px;">
                <span class="fa fa-key icon"></span>
                <input type="text" name="otp" id="otp" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" placeholder="6-digit code" required oninput="validateOTP(this)" onkeypress="return onlyNumbers(event)" style="background: #eee; padding: 16px 16px 16px 38px; border: 0; outline: none; border-radius: 20px; box-shadow: inset 7px 2px 10px #babebc, inset -5px -5px 12px #ebecf0; font-size: 16px; letter-spacing: 3px; text-align: center; font-weight: 600;" />
            </div>
            @if($errors->any())
            <div style="color: #d8000c; border-radius: 10px; padding: 10px 0; margin-bottom: 18px; font-size: 14px;">
                <ul style="margin: 0; padding: 0; list-style: none;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
            <button type="submit" class="form_btn" style="background: #1877f2; color: #fff; border-radius: 20px; border: none; font-size: 15px; font-weight: bold; padding: 12px 0; width: 100%; margin-top: 8px; box-shadow: -3px -3px 8px #fff, 3px 3px 8px #babebc; transition: background 0.2s;">Verify</button>
        </form>
        <div style="margin-top: 18px; color: #888; font-size: 13px;">
            Having trouble? <a href="mailto:support@fixhr.com" style="color: #1877f2; text-decoration: underline;">Contact support</a>
        </div>
    </div>
</div>

<script>
// Function to allow only numbers
function onlyNumbers(event) {
    // Allow: backspace, delete, tab, escape, enter
    if (event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 27 || event.keyCode == 13 ||
        // Allow Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
        (event.keyCode == 65 && event.ctrlKey === true) ||
        (event.keyCode == 67 && event.ctrlKey === true) ||
        (event.keyCode == 86 && event.ctrlKey === true) ||
        (event.keyCode == 88 && event.ctrlKey === true) ||
        // Allow home, end, left, right
        (event.keyCode >= 35 && event.keyCode <= 39)) {
        return;
    }
    // Ensure that it is a number and stop the keypress
    if ((event.shiftKey || (event.keyCode < 48 || event.keyCode > 57)) && (event.keyCode < 96 || event.keyCode > 105)) {
        event.preventDefault();
    }
}

// Function to validate OTP input
function validateOTP(input) {
    // Remove any non-numeric characters
    let value = input.value.replace(/[^0-9]/g, '');
    
    // Limit to 6 digits
    if (value.length > 6) {
        value = value.substring(0, 6);
    }
    
    // Update the input value
    input.value = value;
    
    // Enable/disable submit button based on length
    const submitBtn = document.querySelector('button[type="submit"]');
    if (submitBtn) {
        if (value.length === 6) {
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';
        } else {
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.6';
        }
    }
}

// Initialize validation on page load
document.addEventListener('DOMContentLoaded', function() {
    const otpInput = document.getElementById('otp');
    if (otpInput) {
        validateOTP(otpInput);
    }
});
</script>
@endsection 