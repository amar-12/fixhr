
@extends('admin.layout.master')
@section('title')
    {{$pageTitle}}
@endsection
@section('content')
 @php
    use Carbon\Carbon;
@endphp
    {{-- Breadcrumbs Start --}}
    <div class="breadcrumb-container">
        <x-breadcrumb :breadcrumbs="$breadcrumbs" />
    </div>
    {{-- Breadcrumbs End --}}
    <div class="row">
        <div class="col-lg-8 offset-lg-2 col-md-10 offset-md-1">
            <div class="main-content card-surface shadow-lg">
        
                <!-- Title -->
                <div class="page-header">
                    <h1 class="page-title d-inline-flex align-items-center gap-2">
                        {{$pageTitle}}
                        <span class="status-pill {{$has2FA ? 'status-enabled' : 'status-disabled'}}" aria-live="polite">{{$twoFAStatus}}</span>
                    </h1>
                </div>
               
                <!-- Content layout -->
                    <div class="content-layout">
                        <div class="content-left">
                            <!-- Text content -->
                            <p class="content-text">
                                Instead of waiting for text messages, get verification codes from an authenticator app. It works even if your phone is offline.
                            </p>
                            
                            <p class="content-text">
                                First, download Google Authenticator from the 
                                <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" target="_blank">Google Play Store</a> 
                                or the 
                                <a href="https://apps.apple.com/us/app/google-authenticator/id388497605" target="_blank">iOS App Store</a>.
                            </p>

                            <!-- Setup button -->
                            {{-- <button type="button" class="setup-button" onclick="open2FAModal()">
                                <span class="setup-plus">+</span>
                                Set up authenticator
                            </button> --}}


                            <div class="card-body cta-area">
                                <h5 class="section-title-2fa">Your authenticator</h5>
                                @if($has2FA)
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="d-flex align-items-center">
                                            <span style="font-size:2rem; margin-right: 1rem;">
                                                <i class="fa fa-qrcode"></i>
                                            </span>
                                            <div>
                                                <div><strong>Authenticator</strong></div>
                                                <div class="text-muted" style="font-size:0.95em;">
                                                    @if($setupTime)
                                                        Added on {{ Carbon::parse($setupTime)->format('M d, Y h:i A') }}
                                                    @else
                                                        Added just now
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <form method="POST" action="{{ route('2fa.disable') }}" style="margin:0;" class="js-2fa-disable-form">
                                            @csrf
                                            <button type="submit" class="btn btn-link text-danger p-0" title="Remove">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                    <button class="btn btn-outline-primary btn-modern w-100" onclick="open2FAChangeModal()">
                                        Change authenticator app
                                    </button>
                                @else
                                    <div class="mb-3 text-muted">No authenticator app set up.</div>
                                    <a onclick="open2FAModal()" class="btn btn-primary btn-modern w-100">Set up authenticator app</a>
                                @endif
                            </div>


                        </div>

                        <div class="content-right">
                            <!-- 2FA Security Image -->
                            <div class="security-image-container">
                                <img src="{{ asset('assets/images/2fa-image.png') }}" alt="Two-Factor Authentication Security" class="security-image">
                            </div>
                            
                       
                        </div>
                    </div>
            </div>
        </div>
    </div>

<!-- 2FA Setup Modal -->
<div id="twoFAModal" class="modal-2fa-overlay" style="display: none;" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="modal-2fa-content" role="document">
        <div class="modal-2fa-header">
            <h2 class="modal-2fa-title" id="modalTitle">Set up authenticator app</h2>
            <button type="button" class="modal-2fa-close" onclick="close2FAModal()">&times;</button>
        </div>
        <div class="modal-2fa-body">
            <!-- QR Code View -->
            <div id="qrCodeView">
                <div class="setup-instructions" id="setupInstructions">
                    <p>In the Google Authenticator app tap the <b>(+)</b></p>
                    <p>Choose Scan a QR code</p>
                </div>
                
                <div class="qr-code-container">
                    <div id="qrCodeDisplay"></div>
                </div>
                
                <div class="alternative-link">
                    <a href="#" onclick="showManualEntry()">Can't scan it?</a>
                </div>
            </div>
            
            <!-- Manual Entry View -->
            <div id="manualEntryView" style="display: none;">
                <div class="setup-instructions" id="manualInstructions">
                    <p>1. In the Google Authenticator app tap the <strong>+</strong> then tap <strong>Enter a setup key</strong></p>
                    <p>2. Enter your email address and this key (spaces don't matter):</p>
                    <div class="setup-key">
                        <strong id="setupKeyDisplay"></strong>
                    </div>
                    <p>3. Make sure <strong>Time based</strong> is selected</p>
                    <p>4. Tap <strong>Add</strong> to finish</p>
                </div>
            </div>
            
            <!-- Verification View -->
            <div id="verificationView" style="display: none;">
                <div class="setup-instructions" id="verificationInstructions">
                    <p>Enter the 6-digit code you see in the app</p>
                </div>
                
                <div class="otp-verification">
                    <input type="text" id="otpInput" maxlength="6" placeholder="Enter Code" oninput="handleOTPInput(this)">
                    <div id="otpError" class="error-message" style="display: none;"></div>
                </div>
            </div>
        </div>
        <div class="modal-2fa-footer">
            <button type="button" class="btn-back" onclick="goBack()" style="display: none;">Back</button>
            <button type="button" class="btn-cancel" onclick="close2FAModal()">Cancel</button>
            <button type="button" class="btn-next" onclick="handleNext()">Next</button>
        </div>
    </div>
</div>
@section('css')
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Google Sans', 'Roboto', Arial, sans-serif;
        background-color: #fff;
        color: #202124;
        line-height: 1.5;
    }

    .cta-area{
        margin-top:35px;
    }
    /* Breadcrumb container */
    .breadcrumb-container {
        position: absolute;
        top: 3px;
        left: 16px;
    
        /* z-index: 1000; */
    }

    /* Blue header line */
    .header-line {
        height: 4px;
        background: linear-gradient(90deg, #4285f4, #ea4335, #fbbc04, #4285f4, #34a853, #ea4335);
        width: 100%;
    }

    /* Google Account logo */
    .google-account-logo {
        padding: 16px 24px;
        border-bottom: 1px solid #dadce0;
    }

    .google-logo {
        font-size: 22px;
        font-weight: 400;
    }

    .google-text {
        background: linear-gradient(90deg, #4285f4, #ea4335, #fbbc04, #4285f4, #34a853, #ea4335);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-weight: 500;
    }

    .account-text {
        color: #202124;
        font-weight: 400;
    }

    /* Surfaces */
    .card-surface {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
        border: 1px solid #eef0f3;
    }

    .shadow-lg { box-shadow: 0 12px 32px rgba(0,0,0,0.08) !important; }

    /* Main content */
    .main-content {
        /* max-width: 600px; */
        /* margin: 0 auto; */
        padding: 95px 24px;
        position: relative;
        /* margin-top: 60px; */
    }

    /* Navigation */
    .nav-back {
        display: inline-flex;
        align-items: center;
        color: #5f6368;
        text-decoration: none;
        font-size: 14px;
        margin-bottom: 24px;
        cursor: pointer;
    }

    .nav-back:hover {
        color: #202124;
    }

    .nav-arrow {
        margin-right: 8px;
        font-size: 18px;
    }

    /* Header + Title */
    .page-header {
        display: block;
        margin-bottom: 16px;
    }

    .page-title {
        font-size: 28px;
        font-weight: 600;
        color: #202124;
        margin-bottom: 0;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: .3px;
        border: 1px solid transparent;
        white-space: nowrap;
    }
    .status-enabled { background: #e6f4ea; color: #137333; border-color: #c6e3cf; }
    .status-disabled { background: #fce8e6; color: #c5221f; border-color: #f4c7c3; }

    /* Typography */
    .content-text {
        font-size: 15px;
        color: #1d1e1e;
        margin-bottom: 16px;
        line-height: 1.6;
    }

    .content-text a {
        color: #1a73e8;
        text-decoration: none;
    }

    .content-text a:hover {
        text-decoration: underline;
    }

    /* Setup button */
    .setup-button {
        display: inline-flex;
        align-items: center;
        background: #fff;
        border: 1px solid #dadce0;
        border-radius: 8px;
        padding: 12px 24px;
        color: #1a73e8;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        margin-top: 16px;
    }

    .setup-button:hover {
        background: #f8f9fa;
        border-color: #dadce0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12);
    }

    .setup-plus {
        margin-right: 8px;
        font-size: 18px;
        font-weight: bold;
    }

    /* Layout */
    .content-layout {
        display: flex;
        align-items: flex-start;
        gap: 40px;
        margin-top: 16px;
    }

    .content-left {
        flex: 1;
    }

    .content-right { flex: 0 0 320px; }

    /* Authenticator icon */
    .auth-icon {
        width: 120px;
        height: 120px;
        position: relative;
    }

    .auth-icon-inner {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        border-radius: 12px;
        border: 1px solid #dadce0;
    }

    .auth-symbols {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .auth-symbol {
        width: 24px;
        height: 32px;
        border: 2px solid #5f6368;
        border-radius: 4px;
        position: relative;
    }

    .auth-symbol::before {
        content: '*';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: #5f6368;
        font-size: 16px;
    }

    .auth-symbol::after {
        content: '';
        position: absolute;
        bottom: 4px;
        left: 4px;
        right: 4px;
        height: 1px;
        background: #5f6368;
    }

    .auth-center {
        width: 32px;
        height: 32px;
        position: relative;
    }

    .auth-center-shape {
        width: 100%;
        height: 100%;
        background: linear-gradient(45deg, #4285f4, #ea4335, #fbbc04, #34a853);
        clip-path: polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%);
    }

    .auth-lines {
        position: absolute;
        top: 50%;
        left: -8px;
        transform: translateY(-50%);
        display: flex;
        gap: 2px;
    }

    .auth-line {
        width: 2px;
        height: 8px;
        background: #dadce0;
        border-radius: 1px;
    }

    .section-title-2fa { font-size: 16px; font-weight: 600;margin-top: :15px }

    /* Security Image */
    .security-image-container {
        margin-bottom: 20px;
        text-align: center;
    }

    .security-image {
        max-width: 250px;
        height: auto;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .security-image:hover {
        transform: scale(1.05);
    }

    /* Buttons */
    .btn-modern { border-radius: 10px; padding: 10px 16px; font-weight: 600; }

    /* Modal Styles */
     .modal-2fa-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
    }

    .modal-2fa-content {
        background: white;
        border-radius: 8px;
        max-width: 400px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 10px 30px rgba(2, 6, 23, 0.18);
        border: 1px solid #eef0f3;
    }

    .modal-2fa-header {
        padding: 24px 24px 16px;
        border-bottom: 1px solid #dadce0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-2fa-title {
        font-size: 20px;
        font-weight: 500;
        color: #202124;
        margin: 0;
    }

    .modal-2fa-close {
        background: none;
        border: none;
        font-size: 24px;
        color: #5f6368;
        cursor: pointer;
        padding: 0;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-2fa-close:hover {
        color: #202124;
    }

    .modal-2fa-body {
        padding: 24px;
    } 

    .setup-instructions {
        margin-bottom: 24px;
    }

    .setup-instructions p {
        margin: 8px 0;
        color: #2c3034;
        font-size: 14px;
        line-height: 1.5;
    }

    .setup-key {
        background: #f8f9fa;
        padding: 12px;
        border-radius: 4px;
        margin: 12px 0;
        font-family: monospace;
        font-size: 14px;
        letter-spacing: 1px;
        text-align: center;
        border: 1px solid #dadce0;
    }

    .qr-code-container {
        text-align: center;
        margin-bottom: 24px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .qr-code-container svg {
        max-width: 200px;
        height: auto;
    }

    .alternative-link {
        text-align: center;
        margin-bottom: 16px;
    }

    .alternative-link a {
        color: #1a73e8;
        text-decoration: none;
        font-size: 14px;
    }

    .alternative-link a:hover {
        text-decoration: underline;
    }

    .manual-entry {
        background: #f8f9fa;
        padding: 16px;
        border-radius: 8px;
        margin-bottom: 16px;
    }

    .manual-entry label {
        display: block;
        margin-bottom: 8px;
        font-size: 14px;
        color: #5f6368;
    }

    .manual-entry input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #dadce0;
        border-radius: 4px;
        font-family: monospace;
        font-size: 14px;
        margin-bottom: 8px;
    }

    .manual-entry button {
        background: #1a73e8;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
    }

    .manual-entry button:hover {
        background: #1557b0;
    }

    .otp-verification {
        margin-top: 16px;
    }

    .otp-verification label {
        display: block;
        margin-bottom: 8px;
        font-size: 14px;
        color: #5f6368;
    }

    .otp-verification input {
        width: 100%;
        padding: 12px;
        border: 1px solid #dadce0;
        border-radius: 4px;
        font-size: 16px;
        text-align: center;
        letter-spacing: 2px;
    }

    .otp-verification input:focus {
        outline: none;
        border-color: #1a73e8;
    }

    .error-message {
        color: #d93025;
        font-size: 12px;
        margin-top: 4px;
    }

    .modal-2fa-footer {
        padding: 16px 24px 24px;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }

    .btn-back {
        background: none;
        border: none;
        color: #1a73e8;
        cursor: pointer;
        font-size: 14px;
        padding: 8px 16px;
    }

    .btn-back:hover {
        background: #f8f9fa;
        border-radius: 4px;
    }

    .btn-cancel {
        background: none;
        border: none;
        color: #1a73e8;
        cursor: pointer;
        font-size: 14px;
        padding: 8px 16px;
    }

    .btn-cancel:hover {
        background: #f8f9fa;
        border-radius: 4px;
    }

    .btn-next {
        background: linear-gradient(180deg, #1a73e8, #1557b0);
        color: white;
        border: none;
        padding: 10px 16px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
    }
    .fa-trash{
        font-size:1.25rem !important;
    }

    .btn-next:hover {
        background: #1557b0;
    }

    .btn-next:disabled {
        background: #dadce0;
        cursor: not-allowed;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .content-layout {
            flex-direction: column;
            gap: 20px;
        }
        
        .content-right {
            align-self: center;
        }

        .modal-2fa-content {
            width: 95%;
            margin: 20px;
        }

        .page-title { font-size: 24px; }
        .status-pill { font-size: 11px; padding: 5px 10px; }
        .main-content { padding: 20px 16px; }
    }

    /* Dark mode - app controlled via body.dark-mode */
    .dark-mode { background-color: #0b0f14; color: #e3e6ea; }
    .dark-mode .card-surface { background: #0f141b; border-color: #1f2a37; box-shadow: 0 12px 32px rgba(0,0,0,0.6); }
    .dark-mode .page-title { color: #e3e6ea; }
    .dark-mode .content-text { color: #c2c8d0; }
    .dark-mode .content-text a { color: #8ab4f8; }
    .dark-mode .setup-button { background: #111827; border-color: #2b3645; color: #8ab4f8; }
    .dark-mode .setup-button:hover { background: #10151c; }
    .dark-mode .auth-icon-inner { background: #0f141b; border-color: #1f2a37; }
    .dark-mode .auth-symbol { border-color: #9aa0a6; }
    .dark-mode .auth-symbol::before, .dark-mode .auth-symbol::after { color: #9aa0a6; background: #9aa0a6; }
    .dark-mode .security-image { box-shadow: 0 6px 20px rgba(0,0,0,0.5); }
    .dark-mode .modal-2fa-overlay { background: rgba(0,0,0,0.7); }
    .dark-mode .modal-2fa-content { background: #0f141b; border-color: #1f2a37; box-shadow: 0 20px 50px rgba(0,0,0,0.7); }
    .dark-mode .modal-2fa-header { border-bottom-color: #1f2a37; }
    .dark-mode .modal-2fa-title { color: #e3e6ea; }
    .dark-mode .modal-2fa-close { color: #9aa0a6; }
    .dark-mode .modal-2fa-close:hover { color: #e3e6ea; }
    .dark-mode .setup-instructions p { color: #c2c8d0; }
    .dark-mode .setup-key { background: #0b0f14; border-color: #1f2a37; color: #e3e6ea; }
    .dark-mode .qr-code-container { background: #0b0f14; }
    .dark-mode .alternative-link a { color: #8ab4f8; }
    .dark-mode .manual-entry { background: #0b0f14; }
    .dark-mode .manual-entry label { color: #c2c8d0; }
    .dark-mode .manual-entry input { background: #0f141b; color: #e3e6ea; border-color: #1f2a37; }
    .dark-mode .manual-entry button { background: #2563eb; }
    .dark-mode .otp-verification input { background: #0f141b; color: #e3e6ea; border-color: #1f2a37; }
    .dark-mode .btn-back, .dark-mode .btn-cancel { color: #8ab4f8; }
    .dark-mode .btn-back:hover, .dark-mode .btn-cancel:hover { background: #0b0f14; }
    .dark-mode .btn-next { background: linear-gradient(180deg, #2563eb, #1e40af); }
    .dark-mode .status-enabled { background: rgba(19,115,51,0.15); color: #8fedb8; border-color: rgba(19,115,51,0.35); }
    .dark-mode .status-disabled { background: rgba(197,34,31,0.15); color: #fca5a5; border-color: rgba(197,34,31,0.35); }
    .dark-mode .text-muted { color: #9aa0a6 !important; }
    .dark-mode .btn-outline-primary { color: #8ab4f8; border-color: #294773; }
    .dark-mode .btn-outline-primary:hover { background: #132238; border-color: #315a94; }
    .dark-mode .btn-primary { background: #2563eb; border-color: #1d4ed8; color: #ffffff !important; }
    .dark-mode .btn-primary:hover { background: #1d4ed8; border-color: #1e40af; color: #ffffff !important; }
</style>
@endsection

@endsection

@section('script')
<script>
let currentSecret = '';
let qrCodeData = '';
let currentView = 'qr'; // 'qr', 'manual', 'verification'
let isChangeMode = false; // Track if we're in change mode

// Open 2FA modal for setup
function open2FAModal() {
    isChangeMode = false;
    document.getElementById('modalTitle').textContent = 'Set up authenticator app';
    document.getElementById('setupInstructions').innerHTML = `
        <p>In the Google Authenticator app tap the <b>(+)</b></p>
        <p>Choose Scan a QR code</p>
    `;
    document.getElementById('twoFAModal').style.display = 'flex';
    load2FAData();
}

// Open 2FA modal for change
function open2FAChangeModal() {
    isChangeMode = true;
    document.getElementById('modalTitle').textContent = 'Change authenticator app';
    document.getElementById('setupInstructions').innerHTML = `
        <p>You won’t be able to use your old authenticator app for codes or 2-Step Verification for your Google Account</p>
        <p>In the Google Authenticator app tap the  <b>(+)</b> and choose Scan a QR code</p>
    `;
    document.getElementById('twoFAModal').style.display = 'flex';
    load2FAChangeData();
}

// Close 2FA modal
function close2FAModal() {
    document.getElementById('twoFAModal').style.display = 'none';
    resetModal();
}

// Load 2FA data (QR code and secret) for setup
function load2FAData() {
    console.log('Loading 2FA setup data...');
    fetch('{{ route("2fa.setup") }}', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        
        if (data.success) {
            currentSecret = data.secret;
            qrCodeData = data.qrImage;
            document.getElementById('qrCodeDisplay').innerHTML = data.qrImage;
            
            // Only set manual secret if the element exists
            const manualSecretElement = document.getElementById('manualSecret');
            if (manualSecretElement) {
                manualSecretElement.value = data.secret;
            }
        } else {
            console.error('Server error:', data.message);
            alert('Error loading 2FA data: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading 2FA data: ' + error.message);
    });
}

// Load 2FA data (QR code and secret) for change
function load2FAChangeData() {
    console.log('Loading 2FA change data...');
    fetch('{{ route("2fa.change") }}', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        
        if (data.success) {
            currentSecret = data.secret;
            qrCodeData = data.qrImage;
            document.getElementById('qrCodeDisplay').innerHTML = data.qrImage;
            
            // Only set manual secret if the element exists
            const manualSecretElement = document.getElementById('manualSecret');
            if (manualSecretElement) {
                manualSecretElement.value = data.secret;
            }
        } else {
            console.error('Server error:', data.message);
            alert('Error loading 2FA change data: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading 2FA change data: ' + error.message);
    });
}

// Show manual entry view
function showManualEntry() {
    currentView = 'manual';
    document.getElementById('qrCodeView').style.display = 'none';
    document.getElementById('manualEntryView').style.display = 'block';
    document.getElementById('verificationView').style.display = 'none';
    
    // Update footer buttons
    document.querySelector('.btn-back').style.display = 'inline-block';
    document.querySelector('.btn-next').textContent = 'Next';
    document.querySelector('.btn-next').disabled = false;
    
    // Format and display the setup key
    const formattedKey = formatSetupKey(currentSecret);
    document.getElementById('setupKeyDisplay').textContent = formattedKey;
    
    // Update instructions based on mode
    if (isChangeMode) {
        document.getElementById('manualInstructions').innerHTML = `
         <p>1. In the Google Authenticator app tap the <strong>+</strong> then tap <strong>Enter a setup key</strong></p>
            <p>2. Enter your email address and this key (spaces don't matter):</p>
            <div class="setup-key">
                <strong>${formattedKey}</strong>
            </div>
            <p>3. Make sure <strong>Time based</strong> is selected</p>
            <p>4. Tap <strong>Add</strong> to finish</p>
        `;
    } else {
        document.getElementById('manualInstructions').innerHTML = `
            <p>1. In the Google Authenticator app tap the <strong>+</strong> then tap <strong>Enter a setup key</strong></p>
            <p>2. Enter your email address and this key (spaces don't matter):</p>
            <div class="setup-key">
                <strong>${formattedKey}</strong>
            </div>
            <p>3. Make sure <strong>Time based</strong> is selected</p>
            <p>4. Tap <strong>Add</strong> to finish</p>
        `;
    }
}

// Format setup key with spaces
function formatSetupKey(secret) {
    return secret.match(/.{1,4}/g).join(' ');
}

// Copy secret to clipboard
function copySecret() {
    const secretInput = document.getElementById('manualSecret');
    secretInput.select();
    secretInput.setSelectionRange(0, 99999);
    document.execCommand('copy');
    
    // Show feedback
    const button = event.target;
    const originalText = button.textContent;
    button.textContent = 'Copied!';
    setTimeout(() => {
        button.textContent = originalText;
    }, 2000);
}

// Handle next button click
function handleNext() {
    if (currentView === 'qr') {
        // From QR view, show verification directly
        showVerificationView();
    } else if (currentView === 'manual') {
        // From manual view, show verification
        showVerificationView();
    } else if (currentView === 'verification') {
        // From verification view, verify OTP
        verifyOTP();
    }
}

// Go back to previous view
function goBack() {
    if (currentView === 'manual') {
        // Go back to QR view
        currentView = 'qr';
        document.getElementById('qrCodeView').style.display = 'block';
        document.getElementById('manualEntryView').style.display = 'none';
        document.getElementById('verificationView').style.display = 'none';
        
        // Update footer buttons
        document.querySelector('.btn-back').style.display = 'none';
        document.querySelector('.btn-next').textContent = 'Next';
        document.querySelector('.btn-next').disabled = false;
    } else if (currentView === 'verification') {
        // Go back to QR view (since we skip manual view)
        currentView = 'qr';
        document.getElementById('qrCodeView').style.display = 'block';
        document.getElementById('manualEntryView').style.display = 'none';
        document.getElementById('verificationView').style.display = 'none';
        
        // Update footer buttons
        document.querySelector('.btn-back').style.display = 'none';
        document.querySelector('.btn-next').textContent = 'Next';
        document.querySelector('.btn-next').disabled = false;
    }
}

// Show verification view
function showVerificationView() {
    currentView = 'verification';
    document.getElementById('qrCodeView').style.display = 'none';
    document.getElementById('manualEntryView').style.display = 'none';
    document.getElementById('verificationView').style.display = 'block';
    
    // Update footer buttons
    document.querySelector('.btn-back').style.display = 'inline-block';
    document.querySelector('.btn-next').textContent = 'Verify';
    document.querySelector('.btn-next').disabled = true;
    
    // Update instructions based on mode
    if (isChangeMode) {
        document.getElementById('verificationInstructions').innerHTML = `
            <p>Enter the 6-digit code from your new authenticator app</p>
        `;
    } else {
        document.getElementById('verificationInstructions').innerHTML = `
            <p>Enter the 6-digit code you see in the app</p>
        `;
    }
    
    // Focus on OTP input
    setTimeout(() => {
        document.getElementById('otpInput').focus();
    }, 100);
}

// Verify OTP
function verifyOTP() {
    const otpInput = document.getElementById('otpInput');
    const otp = otpInput.value.trim();
    
    console.log('Verifying OTP:', otp);
    console.log('Current secret:', currentSecret);
    console.log('Is change mode:', isChangeMode);
    
    if (!otp || otp.length !== 6) {
        showOTPError('Please enter a valid 6-digit code.');
        return;
    }
    
    // Disable next button during verification
    const nextBtn = document.querySelector('.btn-next');
    nextBtn.disabled = true;
    nextBtn.textContent = 'Verifying...';
    
    // Determine the endpoint based on mode
    const endpoint = isChangeMode ? '{{ route("2fa.change.submit") }}' : '{{ route("2fa.enable") }}';
    
    // Send verification request
    fetch(endpoint, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            secret: currentSecret,
            otp: otp
        })
    })
    .then(response => {
        console.log('Verification response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Verification response data:', data);
        
        if (data.success) {
            // Success - close modal and show success message
            close2FAModal();
            const successMessage = isChangeMode ? 'Authenticator changed successfully!' : 'Two factor authentication enabled successfully!';
            // Top-end SweetAlert toast
            if (window.Swal) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: successMessage,
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
            } else {
                showSuccessMessage(successMessage);
            }
            // Optionally reload the page to update the UI
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showOTPError(data.message || 'Invalid OTP code. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showOTPError('Network error. Please try again.');
    })
    .finally(() => {
        // Re-enable next button
        const nextBtn = document.querySelector('.btn-next');
        nextBtn.disabled = false;
        nextBtn.textContent = 'Verify';
    });
}

// Show OTP error
function showOTPError(message) {
    const errorDiv = document.getElementById('otpError');
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
}

// Show success message
function showSuccessMessage(message) {
    // Create a temporary success message
    const successDiv = document.createElement('div');
    successDiv.className = 'success-message';
    successDiv.textContent = message;
    successDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #34a853;
        color: white;
        padding: 12px 24px;
        border-radius: 4px;
        z-index: 10001;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    `;
    document.body.appendChild(successDiv);
    
    setTimeout(() => {
        successDiv.remove();
    }, 3000);
}

// Reset modal state
function resetModal() {
    currentView = 'qr';
    document.getElementById('qrCodeView').style.display = 'block';
    document.getElementById('manualEntryView').style.display = 'none';
    document.getElementById('verificationView').style.display = 'none';
    document.getElementById('otpInput').value = '';
    document.getElementById('otpError').style.display = 'none';
    document.querySelector('.btn-back').style.display = 'none';
    document.querySelector('.btn-next').textContent = 'Next';
    document.querySelector('.btn-next').disabled = false;
    currentSecret = '';
    qrCodeData = '';
    isChangeMode = false;
    
    // Reset instructions to default
    document.getElementById('setupInstructions').innerHTML = `
        <p>In the Google Authenticator app tap the +</p>
        <p>Choose Scan a QR code</p>
    `;
    document.getElementById('verificationInstructions').innerHTML = `
        <p>Enter the 6-digit code you see in the app</p>
    `;
}

// Handle OTP input
function handleOTPInput(input) {
    const otp = input.value.trim();
    const nextBtn = document.querySelector('.btn-next');
    
    // Enable/disable next button based on OTP length
    if (otp.length === 6) {
        nextBtn.disabled = false;
    } else {
        nextBtn.disabled = true;
    }
    
    // Clear error when user starts typing
    if (otp.length > 0) {
        document.getElementById('otpError').style.display = 'none';
    }
}

// Initialize modal functionality
document.addEventListener('DOMContentLoaded', function() {
    // SweetAlert delete confirmation for 2FA disable
    document.querySelectorAll('.js-2fa-disable-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Remove authenticator?',
                text: 'You will no longer be able to use codes from this app.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, remove',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    // Close modal when clicking outside
    document.getElementById('twoFAModal').addEventListener('click', function(e) {
        if (e.target === this) {
            close2FAModal();
        }
    });
    
    // Initialize next button as enabled for QR view
    const nextBtn = document.querySelector('.btn-next');
    if (nextBtn) {
        nextBtn.disabled = false;
        nextBtn.textContent = 'Next';
    }
});
</script>
@endsection



{{-- @section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 60vh;">
    <div class="card shadow-sm" style="min-width: 400px; max-width: 500px;">
        <div class="card-body">
            <h5 class="mb-4">Your authenticator</h5>
            @if($has2FA)
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center">
                        <span style="font-size:2rem; margin-right: 1rem;">
                            <i class="fa fa-qrcode"></i>
                        </span>
                        <div>
                            <div><strong>Authenticator</strong></div>
                            <div class="text-muted" style="font-size:0.95em;">
                                @if($setupTime)
                                    Added on {{ Carbon::parse($setupTime)->format('M d, Y h:i A') }}
                                @else
                                    Added just now
                                @endif
                            </div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('2fa.disable') }}" style="margin:0;">
                        @csrf
                        <button type="submit" class="btn btn-link text-danger p-0" title="Remove">
                            <i class="fa fa-trash"></i>
                        </button>
                    </form>
                </div>
                <button class="btn btn-outline-primary w-100" onclick="location.href='{{ route('2fa.change') }}'">
                    Change authenticator app
                </button>
            @else
                <div class="mb-3 text-muted">No authenticator app set up.</div>
                <a href="{{ route('2fa.setup') }}" class="btn btn-primary w-100">Set up authenticator app</a>
            @endif
        </div>
    </div>
</div> --}}