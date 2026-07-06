@extends('auth.admin.authlayout.master_simple')
@section('title', 'Business Create')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/create_business.css') }}">
@endsection

@section('script')
    @php
        $pageData = [
            'csrf' => csrf_token(),
            'routes' => [
                'validateGst' => route('validate.gst'),
                'saveBusiness' => route('save.business'),
                'accountSettings' => route('account.settings'),
                'demo_setup' => route('demo.setup.start'),
                'verify_phone_no' => route('verify.phone'),
                'verify_otp' => route('verify.otp'),
            ],
        ];
    @endphp

    <script>
        window.pageData = @json($pageData);
    </script>

    <script async
        src="https://maps.googleapis.com/maps/api/js?key={{ config('credentials')['MAP_API_KEY'] }}&loading=async&libraries=places&callback=initMap">
    </script>
    <script src="{{ asset('assets/plugins/fileupload/js/dropify.js') }}"></script>
    <script src="{{ asset('assets/js/create_business.js') }}"></script>
@endsection

@section('content')

    <!-- Navbar -->
    <nav class="navbar sticky-top">
        <div class="container-fluid px-4 px-lg-5">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <div class="d-flex align-items-center">
                    <img class="ms-1" src="{{ asset('frontend/assets/images/black-logo.png') }}" alt="Fix Hr">
                </div>
            </a>

            <div class="d-flex align-items-center gap-3">
                <span class="text-secondary fw-medium fs-6 d-none d-sm-inline" style="font-size: 0.875rem;">
                    Already have an account?
                </span>
                <a href="{{ route('login') }}" class="btn text-primary fw-bold sign-in-btn">
                    Sign In
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container-fluid py-5 px-5">

        <!-- Page Header -->
        <div class="mb-5 px-lg-4">
            <h1 class="fw-bold fs-1 mb-2">Register Business</h1>
            <p class="text-secondary fs-5">
                Complete your profile to access the professional HR dashboard.
            </p>
        </div>

        <!-- Form Card -->
        <div class="main-card mx-lg-4">

            <!-- Form Content -->
            <form class="p-5" id="business-form" action="{{ route('save.business') }}" method="POST"
                enctype="multipart/form-data">
                @csrf

                <!-- Section 0: Validate Phone Number -->
                <div class="mb-5">
                    <div class="section-header">
                        <h3 class="section-title pb-0">
                            Contact Verification
                        </h3>
                        <p class="section-desc">Verify your mobile number.</p>
                    </div>

                    <div class="row g-4">

                        <!-- Phone -->
                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <label class="form-label-custom">Primary Contact <span class="required-star">*</span></label>
                            <div class="input-wrapper">
                                <i class="fa fa-phone input-icon fa-lg"></i>
                                <input type="tel" class="form-control form-control-custom" id="business_phone_number"
                                    name="business_phone_number" placeholder="+91 98765 43210" maxlength="10"
                                    onkeypress="return numericOnly(event)" required>
                            </div>
                            <span class="text-danger"></span>
                        </div>

                        {{-- Enter OTP --}}
                        <div class="col-md-6 col-lg-4 col-xl-3" style="display: none" id="enter-otp-div">
                            <label class="form-label-custom">Enter OTP <span class="required-star">*</span></label>
                            <div class="input-wrapper gap-2">
                                <i class="fa fa-comment-dots input-icon fa-lg"></i>
                                <input type="tel" class="form-control form-control-custom" id="otp"
                                    name="otp" placeholder="******" maxlength="6"
                                    onkeypress="return numericOnly(event)" required>
                            </div>
                        </div>

                        {{-- Send OTP --}}
                        <div class="col-md-6 col-lg-4 col-xl-3 d-flex align-items-end" id="send-otp-div">
                            <div>
                                <button type="button" class="btn btn-primary-custom d-flex align-items-center gap-2"
                                    id="sendOTPBtn">
                                    Send OTP <i class="fa fa-paper-plane small fa-lg"></i>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Section 1: Business Details -->
                <div class="mb-5">
                    <div class="section-header">
                        <h3 class="section-title pb-0">
                            Business Details
                        </h3>
                        <p class="section-desc">Legal information about your organization.</p>
                    </div>

                    <div class="row g-4">

                        <!-- GSTIN -->
                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <label class="form-label-custom">GSTIN <span class="required-star">*</span></label>
                            <div class="input-wrapper">
                                <i class="far fa-file-alt input-icon fa-lg"></i>
                                <input type="text" class="form-control form-control-custom text-uppercase"
                                    id="business_gstin_number" name="business_gstin_number" placeholder="22AAAAA0000A1Z5"
                                    maxlength="15" required>
                            </div>
                            <span class="text-danger" id="business_gstin_number_error"></span>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <label class="form-label-custom">Official Email <span class="required-star">*</span></label>
                            <div class="input-wrapper">
                                <i class="fa fa-envelope input-icon fa-lg"></i>
                                <input type="email" class="form-control form-control-custom" id="business_email"
                                    name="business_email" placeholder="admin@company.com"
                                    value="{{ Session()->get('firstEmail') }}" disabled required autocomplete="username">
                            </div>
                            <span class="text-danger"></span>
                        </div>

                        <!-- Business Category -->
                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <label class="form-label-custom">Business Category <span class="required-star">*</span></label>
                            <div class="input-wrapper">
                                <i class="far fa-building input-icon fa-lg"></i>
                                <select class="form-select select2" id="business_category" name="business_category"
                                    required>
                                    <option selected disabled value="">Select Category</option>
                                    @foreach ($businessCat as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <span class="text-danger-select2 text-danger"></span>
                        </div>

                        <!-- Business Type -->
                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <label class="form-label-custom">Business Structure <span
                                    class="required-star">*</span></label>
                            <div class="input-wrapper">
                                <i class="fa fa-file-alt input-icon fa-lg"></i>
                                <select class="form-select select2" id="business_type" name="business_type" required>
                                    <option selected disabled value="">Select Type</option>
                                    @foreach ($businessType as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <span class="text-danger-select2 text-danger"></span>
                        </div>

                        <!-- Business Name -->
                        <div class="col-md-12 col-lg-8 col-xl-6">
                            <label class="form-label-custom">Registered Business Name <span
                                    class="required-star">*</span></label>
                            <div class="input-wrapper">
                                <i class="fa fa-building input-icon fa-lg"></i>
                                <input type="text" class="form-control form-control-custom" id="business_name"
                                    name="business_name" placeholder="e.g. Acme Solutions Pvt. Ltd." required>
                            </div>
                            <span class="text-danger"></span>
                        </div>

                        <!-- Administrator -->
                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <label class="form-label-custom">Administrator Name <span
                                    class="required-star">*</span></label>
                            <div class="input-wrapper">
                                <i class="fa fa-user input-icon fa-lg"></i>
                                <input type="text" class="form-control form-control-custom" id="owner_name"
                                    name="owner_name" placeholder="Full Name" maxlength="225" required>
                            </div>
                            <span class="text-danger"></span>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Security -->
                <div class="mb-5">
                    <div class="section-header">
                        <h3 class="section-title pb-0">Account Security</h3>
                        <p class="section-desc">Set up your primary administrator credentials.</p>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <label class="form-label-custom">Password <span class="required-star">*</span></label>
                            <div class="input-wrapper">
                                <i class="fa fa-lock input-icon fa-lg"></i>
                                <input type="password" class="form-control form-control-custom" id="business_password"
                                    name="business_password" placeholder="••••••••" maxlength="16"
                                    autocomplete="new-password" required>
                                <div class="password-toggle-icon" data-toggle="#business_password">
                                    <i class="fa fa-eye-slash fa-lg"></i>
                                </div>
                            </div>
                            <span class="text-danger" id="business_password_error"></span>
                        </div>
                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <label class="form-label-custom">Confirm Password <span class="required-star">*</span></label>
                            <div class="input-wrapper">
                                <i class="fa fa-lock input-icon fa-lg"></i>
                                <input type="password" class="form-control form-control-custom"
                                    id="business_confirm_password" name="business_confirm_password"
                                    placeholder="••••••••" maxlength="16" autocomplete="new-password" required>
                                <div class="password-toggle-icon" data-toggle="#business_confirm_password">
                                    <i class="fa fa-eye-slash fa-lg"></i>
                                </div>
                                <span class="text-danger"></span>
                            </div>
                            <span class="text-danger" id="business_confirm_password_error"></span>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Location -->
                <div class="mb-5">
                    <div class="section-header">
                        <h3 class="section-title pb-0">Location & Premises</h3>
                        <p class="section-desc">Pin your primary office location.</p>
                    </div>

                    <div class="row g-4">
                        <!-- Location Inputs Left Side -->
                        <div class="col-xl-6">
                            <div class="mb-4">
                                <label class="form-label-custom">Search Address <span
                                        class="required-star">*</span></label>
                                <div class="input-wrapper">
                                    <i class="fa fa-search input-icon fa-lg"></i>
                                    <input type="text" class="form-control form-control-custom"
                                        id="businessAddressSearchInput" name="business_address_location"
                                        placeholder="Start typing address..." required>
                                </div>
                                <span class="text-danger" id="business_address_location_error"></span>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label-custom">Latitude</label>
                                    <div class="input-wrapper">
                                        <i class="fa fa-globe input-icon fa-lg"></i>
                                        <input type="text" class="form-control form-control-custom"
                                            id="business_address_latitude" name="business_address_latitude"
                                            placeholder="0.0000" readonly>
                                    </div>
                                    <span class="text-danger" id="business_address_latitude_error"></span>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label-custom">Longitude</label>
                                    <div class="input-wrapper">
                                        <i class="fa fa-globe input-icon fa-lg"></i>
                                        <input type="text" class="form-control form-control-custom"
                                            id="business_address_longitude" name="business_address_longitude"
                                            placeholder="0.0000" readonly>
                                    </div>
                                    <span class="text-danger" id="business_address_longitude_error"></span>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label-custom">Postal Code <span
                                            class="required-star">*</span></label>
                                    <div class="input-wrapper">
                                        <i class="fa fa-map-marker-alt input-icon fa-lg"></i>
                                        <input type="text" class="form-control form-control-custom"
                                            id="business_zip_code" name="business_zip_code" placeholder="110001"
                                            maxlength="6" oninput="validatePositiveNumber(this)"
                                            onkeypress="return numericOnly(event)" required>
                                    </div>
                                    <span class="text-danger" id="business_zip_code_error"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Map Preview Right Side -->
                        <div class="col-xl-6">
                            <div class="m-1" id="map"></div>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Brand Assets -->
                <div class="mb-4">
                    <div class="section-header">
                        <h3 class="section-title">Brand Assets</h3>
                        <p class="section-desc">Upload your company logo for the dashboard.</p>
                    </div>

                    <input type="file" name="image" id="image" accept=".jpg, .png, image/jpeg, image/png" data-allowed-file-extensions="jpg png jpeg"
                        data-height="220">
                    <span class="text-danger" id="image_error"></span>
                </div>

                <!-- Footer Actions -->
                <div class="d-flex justify-content-end align-items-center gap-3 pt-4 border-top mt-5">
                    <button type="button" class="btn btn-outline-custom">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom d-flex align-items-center gap-2" id="submitBtn">
                        Create Account <i class="fa fa-chevron-right small fa-lg"></i>
                    </button>
                </div>

            </form>
        </div>

        <!-- Footer -->
        <footer class="text-center mt-5 mb-4 text-secondary small">
            <p>&copy; 2024 Fix HR Solutions. All rights reserved.</p>
        </footer>

    </main>
@endsection
