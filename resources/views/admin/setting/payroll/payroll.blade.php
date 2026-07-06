@extends('admin.layout.master')
@section('title')
Payroll Settings
@endsection

@section('css')
<style>
    .card-hover {
        transition: all 0.3s ease-in-out;
    }

    .card-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    }

    .modal-content {
        border-radius: 12px;
    }

    .form-select:focus,
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    #payroll_mode_info,
    #include_with_salary_info {
        transition: all 0.3s ease;
        animation: fadeIn 0.3s ease-in;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-5px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
    }

    .form-select-lg {
        padding: 0.75rem 1rem;
        font-size: 1rem;
    }

    .modal-dialog-centered {
        display: flex;
        align-items: center;
        min-height: calc(100% - 1rem);
    }

    .form-switch-lg .form-check-input {
        cursor: pointer;
    }

    .alert {
        border-radius: 0.5rem;
    }

    /* OTP Input Styling */
    #otpInput {
        letter-spacing: 0.5rem;
        font-size: 1.5rem;
        font-weight: bold;
    }

    /* Phone number section styling */
    #phoneNumberSection .input-group-text {
        background-color: #f8f9fa;
        border-color: #dee2e6;
    }

    #phoneNumberSection .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    #togglePhoneSection {
        color: #667eea;
        font-size: 0.875rem;
    }

    #togglePhoneSection:hover {
        color: #764ba2;
    }

    /* Phone input validation states */
    #userPhoneNumber.is-valid {
        border-color: #28a745;
    }

    #userPhoneNumber.is-invalid {
        border-color: #dc3545;
    }

    /* OTP input styling */
    #otpInput {
        letter-spacing: 10px;
        font-size: 1.5rem;
        font-weight: bold;
        border: 2px solid #dee2e6;
        border-radius: 8px;
        transition: all 0.3s;
    }

    #otpInput:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    /* Description alert styling */
    .alert-info-custom {
        border-left: 4px solid #667eea;
        background-color: rgba(102, 126, 234, 0.1);
    }

    .alert-secondary-custom {
        border-left: 4px solid #6c757d;
        background-color: rgba(108, 117, 125, 0.1);
    }

    /* Icon colors */
    .fa-check-circle {
        color: #28a745;
    }

    .fa-times-circle {
        color: #dc3545;
    }

    .fa-hand-paper-o {
        color: #ffc107;
    }

    /* Custom modal styles */
    .modal-lg-custom {
        max-width: 850px;
    }

    /* OTP timer styling */
    .otp-timer {
        font-family: monospace;
        font-weight: bold;
        color: #dc2626;
    }

    /* Phone option cards */
    .phone-option-card {
        border: 2px solid transparent;
        border-radius: 10px;
        padding: 15px;
        cursor: pointer;
        transition: all 0.3s;
        margin-bottom: 10px;
    }

    .phone-option-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .phone-option-card.selected {
        border-color: #667eea;
        background-color: rgba(102, 126, 234, 0.05);
    }

    /* Success animation */
    @keyframes successPulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }

        100% {
            transform: scale(1);
        }
    }

    .success-pulse {
        animation: successPulse 0.5s ease-in-out;
    }
</style>
@endsection

@section('content')
<div class="p-0 my-3">
    <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
        <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
        <li><a href="{{ url('/admin/settings/payroll') }}">Settings</a></li>
        <li class="active"><span><b>Payroll Settings</b></span></li>
    </ol>
</div>

<div>
    <p class="text-muted">Create and Update Your Payroll Settings</p>
</div>

<div class="row row-sm">

    {{-- Business Payroll Policy - Opens Modal --}}
    <div class="col-md-4 mb-3">
        <div class="card custom-card h-100 card-hover" style="cursor: pointer;" data-bs-toggle="modal"
            data-bs-target="#payrollCycleModal">
            <div class="card-body text-center">
                <i class="fa fa-building-o fa-2x mb-2 text-primary"></i>
                <h6 class="mb-1">Payroll Master Settings</h6>
                <p class="text-muted small mb-0">Manage Payroll Cycle & Policy</p>
                <span class="my-auto">{{ $payrollPolicies }} Policies</span>
            </div>
        </div>
    </div>

    {{-- Financial Year --}}
    <div class="col-md-4 mb-3">
        <a href="{{ url('admin/settings/payroll/financial-year') }}" class="text-decoration-none text-dark">
            <div class="card custom-card h-100 card-hover">
                <div class="card-body text-center">
                    <i class="fa fa-calendar fa-2x mb-2 text-success"></i>
                    <h6 class="mb-1">Financial Year</h6>
                    <p class="text-muted small mb-0">Manage Financial Years</p>
                    <span class="my-auto">{{ $fincialyear }} Created</span>
                </div>
            </div>
        </a>
    </div>

    {{-- Salary Allowances --}}
    <div class="col-md-4 mb-3">
        <a href="{{ url('payroll/components') }}" class="text-decoration-none text-dark">
            <div class="card custom-card h-100 card-hover">
                <div class="card-body text-center">
                    <i class="fa fa-money fa-2x mb-2 text-warning"></i>
                    <h6 class="mb-1">Salary Allowances</h6>
                    <p class="text-muted small mb-0">Configure Allowance Components</p>
                    <span class="my-auto">{{ $salaryAllowances }} Created</span>
                </div>
            </div>
        </a>
    </div>

    {{-- Advance / Loan Configuration --}}
    <div class="col-md-4 mb-3">
        <a href="{{ url('admin/settings/payroll/loan-configuration') }}" class="text-decoration-none text-dark">
            <div class="card custom-card h-100 card-hover">
                <div class="card-body text-center">
                    <i class="fa fa-credit-card fa-2x mb-2 text-danger"></i>
                    <h6 class="mb-1">Advance / Loan Configuration</h6>
                    <p class="text-muted small mb-0">Manage Employee Loans & Advances</p>
                    <span class="my-auto">{{ $advanceLoanConf }} Created</span>
                </div>
            </div>
        </a>
    </div>

    {{-- Adhoc Components --}}
    <div class="col-md-4 mb-3">
        <a href="{{ url('admin/settings/payroll/adhoc-components') }}" class="text-decoration-none text-dark">
            <div class="card custom-card h-100 card-hover">
                <div class="card-body text-center">
                    <i class="fa fa-cogs fa-2x mb-2 text-info"></i>
                    <h6 class="mb-1">Adhoc Components</h6>
                    <p class="text-muted small mb-0">Setup Temporary Components</p>
                    <span class="my-auto">{{ $AdhocComponent }} Created</span>
                </div>
            </div>
        </a>
    </div>

    {{-- Configure Payslip --}}
    <div class="col-md-4 mb-3">
        <a href="{{ url('admin/settings/payroll/payslip-configuration') }}" class="text-decoration-none text-dark">
            <div class="card custom-card h-100 card-hover">
                <div class="card-body text-center">
                    <i class="fa fa-file-text fa-2x mb-2 text-primary"></i>
                    <h6 class="mb-1">Configure Payslip</h6>
                    <p class="text-muted small mb-0">Customize Payslip View</p>
                    <span class="my-auto">{{ $payslipConf }} Created</span>
                </div>
            </div>
        </a>
    </div>

    
    {{-- FNF Configuration --}}
    <div class="col-md-4 mb-3">
        <a href="{{ url('admin/employee-exit/configuration') }}" class="text-decoration-none text-dark">
            <div class="card custom-card h-100 card-hover">
                <div class="card-body text-center">
                    <i class="fa fa-cogs fa-2x mb-2 text-primary"></i>
                    <h6 class="mb-1">FNF Configuration</h6>
                    <p class="text-muted small mb-0">Setup FNF Rules</p>
                    <span class="my-auto">{{ $totalapprovaldata ?? 0 }} Created</span>
                </div>
            </div>
        </a>
    </div>

</div>


{{-- Payroll Master Settings Modal --}}
<div class="modal fade" id="payrollCycleModal" tabindex="-1" aria-labelledby="payrollCycleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            {{-- Standard Header --}}
            <div class="modal-header bg-light">
                <div>
                    <h5 class="modal-title" id="payrollCycleModalLabel">
                        <i class="fa fa-gears me-2 text-primary"></i>Payroll Master Settings
                    </h5>
                    <p class="text-muted small mb-0">Configure your payroll system settings</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            @php $isLocked = isset($existingSetting) && $existingSetting->pms_is_locked; @endphp

            <form id="payrollCycleForm" method="POST" action="{{ route('payroll.setting.storeOrUpdate') }}">
                @csrf

                <div class="modal-body">

                    @php
                    $payrollPhone = isset($existingSetting) && $existingSetting->pms_phone
                    ? $existingSetting->pms_phone
                    : null;
                    @endphp

                    <input type="hidden" id="payrollPhoneNumber" value="{{ $payrollPhone ?? '' }}">

                    {{-- Lock Status Alert --}}
                    @if ($isLocked)
                    <div class="alert alert-warning border-warning d-flex align-items-center mb-4" role="alert">
                        <i class="fa fa-lock me-2"></i>
                        <div>
                            <strong>Settings Locked:</strong> Please unlock using OTP to make changes.
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-warning ms-auto" id="unlockSettingsBtn">
                            <i class="fa fa-unlock me-1"></i>Unlock Now
                        </button>
                    </div>
                    @endif

                    <fieldset @if ($isLocked) disabled @endif>
                        <div class="row">
                           {{-- Payroll Cycle --}}
                            <div class="col-md-6 mb-3">
                                <label for="payroll_cycle" class="form-label">
                                    <i class="fa fa-refresh me-1 text-info"></i>Payroll Cycle <span class="text-danger">*</span>
                                </label>
                                <select name="payroll_cycle" id="payroll_cycle" class="form-select" required>
                                    <option value="">Select Cycle...</option>
                                    @foreach ($payroll_cycle as $cycle)
                                    <option value="{{ $cycle->m_id }}" @if (isset($existingSetting) &&
                                        $existingSetting->pms_payroll_cycle == $cycle->m_id) selected @endif>
                                        {{ $cycle->m_name }}
                                    </option>
                                    @endforeach
                                </select>
                                <div class="form-text text-muted small">
                                    <i class="fa fa-info-circle"></i> Select how frequently payroll should be processed.
                                </div>
                            </div>



                            {{-- Payroll Mode --}}
                            <div class="col-md-6 mb-3">
                                <label for="payroll_mode" class="form-label">
                                    <i class="fa fa-cogs me-1 text-info"></i>Payroll Mode <span class="text-danger">*</span>
                                </label>
                                <select name="payroll_mode" id="payroll_mode" class="form-select" required>
                                    <option value="">Select Mode...</option>
                                    <option value="manual" @if (isset($existingSetting) && $existingSetting->
                                        pms_payroll_mode == 'manual') selected @endif>
                                        <i class="fa fa-hand-paper-o me-1"></i> Manual Processing
                                    </option>
                                    <option value="auto" @if (isset($existingSetting) && $existingSetting->
                                        pms_payroll_mode == 'auto') selected @endif>
                                        <i class="fa fa-bolt me-1"></i> Automatic Processing
                                    </option>
                                </select>
                            </div>

                            {{-- Mode Info Alert --}}
                            <div class="col-12 mb-3">
                                <div class="alert alert-secondary-custom py-2 px-3 small mb-0" id="payroll_mode_info">
                                    <i class="fa fa-info-circle me-1"></i> Please select a mode to see details.
                                </div>
                            </div>

                            {{-- Salary Component Inclusion/Exclusion --}}
                            <div class="col-md-6 mb-3">
                                <label for="include_with_salary" class="form-label">
                                    <i class="fa fa-calculator me-1 text-info"></i>Include with Salary (If WeekOff Unpaid) <span class="text-danger">*</span>
                                </label>
                                <select name="include_with_salary" id="include_with_salary" class="form-select" required>
                                    <option value="">Select Option...</option>
                                    <option value="1" @if (isset($existingSetting) && $existingSetting->
                                        pms_include_with_salary == '1') selected @endif>
                                        <i class="fa fa-check-circle me-1 text-success"></i> Include with Salary
                                    </option>
                                    <option value="0" @if (isset($existingSetting) && $existingSetting->
                                        pms_include_with_salary == '0') selected @endif>
                                        <i class="fa fa-times-circle me-1 text-danger"></i> Exclude from Salary
                                    </option>
                                </select>
                            </div>

                            {{-- Year Type --}}
                            <div class="col-md-6 mb-3">
                                <label for="year_type" class="form-label">
                                    <i class="fa fa-calendar-check-o me-1 text-info"></i>Year Type <span class="text-danger">*</span>
                                </label>
                                <select name="year_type" id="year_type" class="form-select" required>
                                    <option value="">Select Year Type...</option>
                                    <option value="0" @if (isset($existingSetting) && $existingSetting->pms_year_type == '0') selected @endif>
                                        <i class="fa fa-sun-o me-1 text-warning"></i> Calendar Year (Jan - Dec)
                                    </option>
                                    <option value="1" @if (isset($existingSetting) && $existingSetting->pms_year_type == '1') selected @endif>
                                        <i class="fa fa-line-chart me-1 text-success"></i> Financial Year (Apr - Mar)
                                    </option>
                                </select>
                            </div>

                            {{-- Include with Salary Info Alert --}}
                            <div class="col-12 mb-3">
                                <div class="alert alert-secondary-custom py-2 px-3 small mb-0" id="include_with_salary_info">
                                    <i class="fa fa-info-circle me-1"></i> Please select an option to see details.
                                </div>
                            </div>

                            {{-- User Mobile Number (if available) --}}
                            @php
                            $userMobile = isset($existingSetting) && $existingSetting->pms_phone
                                ? $existingSetting->pms_phone
                                : (auth()->user()->emp_phone ?? auth()->user()->phone ?? null);
                            @endphp

                            {{-- Include TA/DA --}}
                            <div class="col-md-6 mb-3">
                                <label for="include_tada" class="form-label">
                                    <i class="fa fa-money me-1 text-success"></i>Include TA/DA in Salary Slip
                                    <span class="text-danger">*</span>
                                </label>
                                <select name="include_tada" id="include_tada" class="form-select" required>
                                    <option value="">Select Option...</option>
                                    <option value="1"
                                        @if (isset($existingSetting) && $existingSetting->include_tada == '1') selected @endif>
                                        Yes (Include TA/DA)
                                    </option>
                                    <option value="0"
                                        @if (isset($existingSetting) && $existingSetting->include_tada == '0') selected @endif>
                                        No (Exclude TA/DA)
                                    </option>
                                </select>
                            </div>

                            {{-- TA/DA Info Alert --}}
                            <div class="col-12 mb-3">
                                <div class="alert alert-secondary-custom py-2 px-3 small mb-0" id="include_tada_info">
                                    <i class="fa fa-info-circle me-1"></i> Please select an option to see details.
                                </div>
                            </div>

                            {{-- Hidden field for JavaScript --}}
                            <input type="hidden" id="payrollPhoneNumber" value="{{ $payrollPhone ?? '' }}">

                            {{-- Enter Phone Number Section --}}
                            <div class="col-md-12 mb-3">
                                <div class="card border" id="phoneSectionCard">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="fw-bold mb-0">
                                                <i class="fa fa-mobile me-2 text-primary"></i>OTP Delivery Options
                                            </h6>
                                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none"
                                                id="togglePhoneSection">
                                                <i class="fa fa-plus me-1"></i> Change Phone Number
                                            </button>
                                        </div>

                                        <div id="phoneNumberSection" style="display: none; margin-top: 15px;">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <label class="form-label small text-muted">
                                                        <i class="fa fa-phone me-1"></i>Enter phone number to receive OTP
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">
                                                            <i class="fa fa-flag text-muted"></i> +91
                                                        </span>
                                                        <input type="tel" id="userPhoneNumber" class="form-control"
                                                            placeholder="Enter 10-digit mobile number" maxlength="10"
                                                            value="{{ $userMobile ?? '' }}">
                                                    </div>
                                                    <div class="form-text text-muted small">
                                                        <i class="fa fa-info-circle"></i> Enter a different mobile number to receive OTP via SMS
                                                    </div>
                                                </div>
                                                <div class="col-md-4 d-flex align-items-end">
                                                    <button type="button" id="savePhoneNumber" class="btn btn-primary w-100">
                                                        <i class="fa fa-save me-1"></i> Save Number
                                                    </button>
                                                </div>
                                            </div>

                                            <div id="phoneSaveStatus" class="mt-2" style="display: none;">
                                                <!-- Status will be shown here -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($userMobile)
                            <div class="col-md-12 mb-3">
                                <div class="alert alert-info d-flex align-items-center py-2 px-3 small">
                                    <i class="fa fa-mobile me-2 fs-5"></i>
                                    <div class="flex-grow-1">
                                        OTP will be sent to your registered mobile:
                                        <span class="fw-bold" id="phoneDisplaySpan">{{ substr($userMobile, 0, 3) }}****{{ substr($userMobile, -3) }}</span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-info" id="changeMobileBtn">
                                        <i class="fa fa-pencil me-1"></i>Change
                                    </button>
                                </div>
                            </div>
                            @endif
                        </div>
                    </fieldset>

                    {{-- Lock Settings Section --}}
                    <div class="card bg-light border mt-3">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="fw-bold mb-1">
                                        <i class="fa fa-shield me-2 text-warning"></i>Security Settings
                                    </h6>
                                    <p class="text-muted small mb-0">
                                        Lock settings to prevent unauthorized changes.
                                    </p>
                                </div>

                                <div class="d-flex align-items-center">
                                    <div class="form-check form-switch me-3">
                                        <input class="form-check-input" type="checkbox" id="lock_payroll"
                                            name="lock_payroll" {{ $isLocked ? 'checked' : '' }}
                                            style="cursor: pointer; width: 2.5em; height: 1.25em;">
                                    </div>
                                    <span id="lockStatusBadge"
                                        class="badge {{ $isLocked ? 'bg-danger' : 'bg-success' }}">
                                        <i class="fa {{ $isLocked ? 'fa-lock' : 'fa-unlock' }} me-1"></i>
                                        {{ $isLocked ? 'Locked' : 'Unlocked' }}
                                    </span>
                                </div>
                            </div>

                            @if($isLocked)
                            <div class="mt-3">
                                <div class="alert alert-light border d-flex align-items-center py-2 small">
                                    <i class="fa fa-info-circle me-2 text-info"></i>
                                    <div class="flex-grow-1">
                                        <strong>Settings locked by:</strong> {{ $existingSetting->lockedBy->name ?? 'Admin' }}<br>
                                        <strong>Locked on:</strong> {{ $existingSetting->updated_at->format('d M Y, h:i A') }}
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                </div>

                {{-- Standard Footer --}}
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fa fa-times me-1"></i>
                        {{ is_null($existingSetting) ? 'Cancel' : 'Close' }}
                    </button>
                    @if (!$isLocked)
                    <button type="submit" class="btn btn-outline-primary" id="submitPayrollForm">
                        <i class="fa fa-save me-1"></i>
                        {{ is_null($existingSetting) ? 'Save Settings' : 'Update Settings' }}
                    </button>
                    @else
                    <button type="button" class="btn btn-outline-warning" id="unlockToEditBtn">
                        <i class="fa fa-unlock me-1"></i> Unlock to Edit
                    </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
{{-- Phone Number Selection Modal --}}
<div class="modal fade" id="phoneSelectionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title">
                    <i class="fa fa-mobile me-2 text-primary"></i>Confirm Phone Number
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="mb-3">
                        <div class="phone-icon mx-auto"
                            style="width: 80px; height: 80px; background: #dbeafe; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fa fa-mobile fa-2x text-primary"></i>
                        </div>
                    </div>

                    <h6 class="mb-2">Send OTP to:</h6>
                    <div class="phone-display-card bg-light border rounded p-3 mb-3">
                        <p class="mb-1 fw-bold fs-5" id="displayPhoneNumber"></p>
                        <small class="text-muted">
                            <i class="fa fa-user me-1"></i>Your registered phone number
                        </small>
                    </div>

                    <p class="text-muted small">
                        <i class="fa fa-info-circle me-1"></i>
                        OTP will be sent to this number for verification
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fa fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="sendOtpBtn">
                    <i class="fa fa-paper-plane me-1"></i> Send OTP
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Enter Different Number Modal (Updated with simple design) --}}
<div class="modal fade" id="differentNumberModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title">
                    <i class="fa fa-pencil me-2 text-primary"></i>Enter Phone Number
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <div class="text-center mb-3">
                        <i class="fa fa-mobile fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Enter phone number to receive OTP</p>
                    </div>

                    <div class="input-group input-group-lg mb-3">
                        <span class="input-group-text bg-light">
                            <i class="fa fa-flag text-muted"></i> +91
                        </span>
                        <input type="tel" id="differentPhoneInput" class="form-control"
                            placeholder="Enter 10-digit number" maxlength="10">
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="updatePhoneCheckbox" checked>
                        <label class="form-check-label text-muted" for="updatePhoneCheckbox">
                            <i class="fa fa-save me-1"></i>
                            Save this number for future use
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fa fa-arrow-left me-1"></i> Back
                </button>
                <button type="button" class="btn btn-primary" id="confirmDifferentNumberBtn">
                    <i class="fa fa-paper-plane me-1"></i> Send OTP
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Update Phone Confirmation Modal --}}
<div class="modal fade" id="updatePhoneModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title">
                    <i class="fa fa-save me-2 text-primary"></i>Update Phone Number?
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fa fa-question-circle fa-3x text-warning mb-3"></i>
                    <p class="mb-2">Do you want to update your registered phone number to:</p>
                    <h5 id="updatePhoneNumberDisplay" class="fw-bold text-primary"></h5>
                    <p class="text-muted small mt-2">This number will be saved and used for future OTP verifications</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="skipUpdateBtn">
                    <i class="fa fa-times me-1"></i> Skip Update
                </button>
                <button type="button" class="btn btn-primary" id="confirmUpdateBtn">
                    <i class="fa fa-save me-1"></i> Update & Continue
                </button>
            </div>
        </div>
    </div>
</div>

{{-- OTP Verification Modal --}}
<div class="modal fade" id="otpModal" tabindex="-1" aria-labelledby="otpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="otpModalLabel">
                    <i class="fa fa-shield text-primary me-2"></i>OTP Verification
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <div class="mb-2">
                        <i class="fa fa-mobile fa-3x text-muted"></i>
                    </div>
                    <p class="small text-muted mb-1" id="otpPhoneNumber"></p>
                    <p class="small text-muted">Enter the 6-digit OTP sent to your mobile</p>
                </div>

                <input type="text" id="otpInput" class="form-control form-control-lg text-center mb-3 otp-input"
                    placeholder="Enter OTP" maxlength="6" pattern="[0-9]{6}">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="small">
                        <span id="otpTimer" class="otp-timer">02:00</span>
                    </div>
                    <button type="button" class="btn btn-link btn-sm p-0" id="resendOtpBtn" disabled>
                        <i class="fa fa-refresh me-1"></i> Resend OTP
                    </button>
                </div>

                <button type="button" id="verifyOtpBtn" class="btn btn-primary w-100">
                    <i class="fa fa-check me-2"></i>Verify & Continue
                </button>

                <div id="otpError" class="alert alert-danger mt-2 py-2 px-3 small" style="display: none;"></div>
            </div>
        </div>
    </div>
</div>

{{-- Success Modal --}}
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0">
            <div class="modal-body text-center p-5">
                <div class="mb-3">
                    <div class="success-icon mx-auto"
                        style="width: 80px; height: 80px; background: #d1fae5; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fa fa-check fa-2x text-success"></i>
                    </div>
                </div>
                <h5 class="mb-2" id="successTitle">Success!</h5>
                <p class="text-muted small mb-4" id="successMessage">Operation completed successfully</p>
                <button type="button" class="btn btn-outline-success w-100" data-bs-dismiss="modal">
                    Continue
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Global variables
        let currentAction = '';
        let currentCheckbox = null;
        let currentOriginalState = null;
        let selectedPhoneNumber = '';
        let otpTimerInterval = null;
        let otpTimeLeft = 120;
        let isDifferentNumber = false;

        window.updatedUserPhone = null;

        // ============================================
        // PAYROLL CYCLE DESCRIPTION
        // ============================================

        function updatePayrollCycleInfo(cycleId) {
            const info = $('#payroll_mode_info');  // Changed from payroll_mode_info to payroll_cycle_info

            // Get the selected option text
            const selectedOption = $('#payroll_cycle option:selected');
            const cycleName = selectedOption.text();
            const cycleValue = selectedOption.val();

            if (cycleValue && cycleValue !== '') {
                info.removeClass('alert-secondary-custom').addClass('alert-info-custom');

                let cycleDescription = '';
                let cycleIcon = '';
                let cycleColor = '';

                if (cycleName.toLowerCase().includes('monthly')) {
                    cycleIcon = 'fa-calendar-alt';
                    cycleColor = 'text-primary';
                    cycleDescription = 'Payroll processed once every month. Salaries are calculated and disbursed on a monthly basis.';
                } else if (cycleName.toLowerCase().includes('weekly')) {
                    cycleIcon = 'fa-calendar-week';
                    cycleColor = 'text-info';
                    cycleDescription = 'Payroll processed every week. Ideal for organizations with weekly payment schedules.';
                } else if (cycleName.toLowerCase().includes('bi-weekly')) {
                    cycleIcon = 'fa-calendar-alt';
                    cycleColor = 'text-success';
                    cycleDescription = 'Payroll processed every two weeks. 26 pay periods per year.';
                } else if (cycleName.toLowerCase().includes('semi-monthly')) {
                    cycleIcon = 'fa-calendar-check';
                    cycleColor = 'text-warning';
                    cycleDescription = 'Payroll processed twice a month (usually on 15th and last day). 24 pay periods per year.';
                } else {
                    cycleIcon = 'fa-info-circle';
                    cycleColor = 'text-secondary';
                    cycleDescription = 'Selected payroll cycle determines how frequently payroll will be processed and employees will be paid.';
                }

                info.html(`
                    <i class="fa ${cycleIcon} me-2 ${cycleColor}"></i>
                    <span class="small">
                        <strong>${cycleName}:</strong> ${cycleDescription}
                        <br><small class="text-muted">This cycle will be used for all payroll calculations and payment schedules.</small>
                    </span>
                `);
            } else {
                info.removeClass('alert-info-custom').addClass('alert-secondary-custom');
                info.html(`
                    <i class="fa fa-info-circle me-2"></i>
                    <span class="small">
                        Please select a payroll cycle to see details about the payment frequency and schedule.
                    </span>
                `);
            }
        }


        // Initialize Payroll Cycle Info
        const payrollCycleSelect = $('#payroll_cycle');
        if (payrollCycleSelect.length) {
            updatePayrollCycleInfo(payrollCycleSelect.val());
            payrollCycleSelect.on('change', function() {
                updatePayrollCycleInfo($(this).val());
            });
        }

        // ============================================
        // YEAR TYPE DESCRIPTION
        // ============================================

        function updateYearTypeInfo(yearType) {
            const info = $('#year_type_info');

            if (yearType === '0') {
                info.removeClass('alert-secondary-custom').addClass('alert-info-custom');
                info.html(`
                    <i class="fa fa-sun-o me-2 text-warning"></i>
                    <span class="small">
                        <strong>Calendar Year (Jan - Dec):</strong> Financial calculations follow the standard calendar year from January 1st to December 31st.
                        <br><small class="text-muted">Tax calculations, reports, and annual summaries will be based on calendar year periods.</small>
                    </span>
                `);
            } else if (yearType === '1') {
                info.removeClass('alert-secondary-custom').addClass('alert-info-custom');
                info.html(`
                    <i class="fa fa-line-chart me-2 text-success"></i>
                    <span class="small">
                        <strong>Financial Year (Apr - Mar):</strong> Financial calculations follow the Indian financial year from April 1st to March 31st.
                        <br><small class="text-muted">Tax calculations (IT, TDS), statutory compliance, and annual reports will be based on financial year periods.</small>
                    </span>
                `);
            } else {
                info.removeClass('alert-info-custom').addClass('alert-secondary-custom');
                info.html(`
                    <i class="fa fa-info-circle me-2"></i>
                    <span class="small">
                        Please select a year type to see details about the financial period calculations.
                    </span>
                `);
            }
        }



        // ============================================
        // TA/DA DESCRIPTION (Same style as payroll mode)
        // ============================================

        function updateTadaInfo(value) {
            const info = $('#include_tada_info');

            if (value === '1') {
                info.removeClass('alert-secondary-custom').addClass('alert-info-custom');
                info.html(`
                    <i class="fa fa-check-circle me-2 text-success"></i>
                    <span class="small">
                        <strong>TA/DA Included:</strong> TA/DA will be added to <strong>Net Pay</strong> and will be disbursed with salary.
                        This amount will be included in the total salary calculation and subject to applicable deductions.
                    </span>
                `);
            } else if (value === '0') {
                info.removeClass('alert-secondary-custom').addClass('alert-info-custom');
                info.html(`
                    <i class="fa fa-times-circle me-2 text-danger"></i>
                    <span class="small">
                        <strong>TA/DA Excluded:</strong> TA/DA will NOT be included in Net Pay and will not be disbursed with salary.
                        This component will be excluded from the salary calculation and statutory deductions.
                    </span>
                `);
            } else {
                info.removeClass('alert-info-custom').addClass('alert-secondary-custom');
                info.html(`
                    <i class="fa fa-info-circle me-2"></i>
                    <span class="small">
                        Please select whether to include or exclude TA/DA from salary calculation.
                    </span>
                `);
            }
        }

        // Initialize TA/DA info on page load - PASS THE CURRENT VALUE
        const tadaSelect = $('#include_tada');
        if (tadaSelect.length) {
            // Get the current value and pass it to the function
            const currentTadaValue = tadaSelect.val();
            updateTadaInfo(currentTadaValue);

            // Add change event listener
            tadaSelect.on('change', function() {
                updateTadaInfo($(this).val());
            });
        }
        // ============================================
        // MODAL EVENT HANDLERS
        // ============================================

        formatPhoneInput();

        // Initialize modal
        $('#payrollCycleModal').on('shown.bs.modal', function() {
            updatePayrollModeInfo($('#payroll_mode').val());
            updateIncludeWithSalaryInfo($('#include_with_salary').val());
            formatPhoneInput();
        });

        const initialPhone = $('#payrollPhoneNumber').val();
        if (initialPhone) {
            const maskedPhone = initialPhone.substring(0, 3) + '****' + initialPhone.substring(7);
            $('#phoneDisplaySpan').text(maskedPhone);
        }

        // Clear data when modal closes
        $('#payrollCycleModal').on('hidden.bs.modal', function() {
            $('#phoneNumberSection').hide();
            $('#togglePhoneSection i').removeClass('fa-minus').addClass('fa-plus');
            $('#togglePhoneSection').html('<i class="fa fa-plus me-1"></i> Change Phone Number');
        });

        // ============================================
        // PAYROLL MODE DESCRIPTION
        // ============================================

        function updatePayrollModeInfo(mode) {
            const info = $('#payroll_mode_info');
            if (mode === 'auto') {
                info.removeClass('alert-secondary-custom').addClass('alert-info-custom');
                info.html(`
                    <i class="fa fa-bolt me-2 text-success"></i>
                    <span class="small">
                        <strong>Automatic Mode:</strong> Salary components calculated automatically based on predefined formulas.
                        System applies configured business rules automatically.
                    </span>
                `);
            } else if (mode === 'manual') {
                info.removeClass('alert-secondary-custom').addClass('alert-info-custom');
                info.html(`
                    <i class="fa fa-hand-paper-o me-2 text-warning"></i>
                    <span class="small">
                        <strong>Manual Mode:</strong> No predefined formulas. Salaries defined manually for each employee.
                        Complete control over calculations.
                    </span>
                `);
            } else {
                info.removeClass('alert-info-custom').addClass('alert-secondary-custom');
                info.html(`
                    <i class="fa fa-info-circle me-2"></i>
                    <span class="small">
                        Please select a payroll mode to see more details.
                    </span>
                `);
            }
        }

        // ============================================
        // INCLUDE WITH SALARY DESCRIPTION
        // ============================================

        function updateIncludeWithSalaryInfo(value) {
            const infoElement = $('#include_with_salary_info');
            if (value === '1') {
                infoElement.removeClass('alert-secondary-custom').addClass('alert-info-custom');
                infoElement.html(`
                    <i class="fa fa-check-circle me-2 text-success"></i>
                    <span class="small">
                        <strong>Include with Salary:</strong> Components included in gross salary calculation.
                        Subject to tax calculations and statutory deductions.
                    </span>
                `);
            } else if (value === '0') {
                infoElement.removeClass('alert-secondary-custom').addClass('alert-info-custom');
                infoElement.html(`
                    <i class="fa fa-times-circle me-2 text-danger"></i>
                    <span class="small">
                        <strong>Exclude from Salary:</strong> Components excluded from gross salary.
                        Treated separately; may not be subject to standard tax calculations.
                    </span>
                `);
            } else {
                infoElement.removeClass('alert-info-custom').addClass('alert-secondary-custom');
                infoElement.html(`
                    <i class="fa fa-info-circle me-2"></i>
                    <span class="small">
                        Please select whether to include or exclude components from salary calculation.
                    </span>
                `);
            }
        }

        // Event handlers
        $(document).on('change', '#payroll_mode', function() {
            updatePayrollModeInfo($(this).val());
        });

        $(document).on('change', '#include_with_salary', function() {
            updateIncludeWithSalaryInfo($(this).val());
        });

        // ============================================
        // PHONE NUMBER MANAGEMENT - SAVE BUTTON
        // ============================================

        // Toggle phone number section
        $(document).on('click', '#togglePhoneSection', function() {
            const section = $('#phoneNumberSection');
            const icon = $(this).find('i');

            if (section.is(':visible')) {
                section.slideUp(300);
                icon.removeClass('fa-minus').addClass('fa-plus');
                $(this).html('<i class="fa fa-plus me-1"></i> Change Phone Number');
            } else {
                section.slideDown(300);
                icon.removeClass('fa-plus').addClass('fa-minus');
                $(this).html('<i class="fa fa-minus me-1"></i> Hide Phone Number');
            }
        });

        // Change mobile button
        $(document).on('click', '#changeMobileBtn', function() {
            $('#phoneNumberSection').slideDown(300);
            $('#togglePhoneSection i').removeClass('fa-plus').addClass('fa-minus');
            $('#togglePhoneSection').html('<i class="fa fa-minus me-1"></i> Hide Phone Number');
        });

        // Format phone input
        function formatPhoneInput() {
            const phoneInput = $('#userPhoneNumber');
            let phoneValue = phoneInput.val().replace(/\D/g, '');
            if (phoneValue.length > 10) {
                phoneValue = phoneValue.substring(0, 10);
            }
            phoneInput.val(phoneValue);
        }

        // Auto-format phone number input
        $('#userPhoneNumber').on('input', function() {
            let value = $(this).val().replace(/\D/g, '');
            if (value.length > 10) {
                value = value.substring(0, 10);
            }
            $(this).val(value);

            if (value.length === 10) {
                $(this).removeClass('is-invalid').addClass('is-valid');
            } else if (value.length > 0) {
                $(this).removeClass('is-valid').addClass('is-invalid');
            } else {
                $(this).removeClass('is-valid is-invalid');
            }
        });

        // Save phone number button
        // Save phone number button
        // Save phone number button - Updated with immediate UI update
        $(document).on('click', '#savePhoneNumber', function() {
            const phoneNumber = $('#userPhoneNumber').val().trim();
            const btn = $(this);

            // Validation
            if (!phoneNumber) {
                showPhoneStatus('error', 'Please enter phone number');
                return;
            }

            if (phoneNumber.length !== 10 || !/^\d+$/.test(phoneNumber)) {
                showPhoneStatus('error', 'Please enter valid 10-digit phone number');
                return;
            }

            // Disable button and show loading
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Saving...');

            // Send AJAX request to save phone number
            $.ajax({
                url: "{{ route('payroll.setting.updatePhone') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    phone_number: phoneNumber
                },
                success: function(response) {
                    if (response.status) {
                        // ✅ IMMEDIATELY update all phone displays WITHOUT RELOAD
                        updateAllPhoneDisplays(phoneNumber);

                        // Update global variable
                        window.updatedUserPhone = phoneNumber;

                        // Show success status
                        showPhoneStatus('success', 'Phone number saved successfully!');
                        btn.html('<i class="fa fa-check me-1"></i> Saved');

                        // Hide section after 2 seconds
                        setTimeout(() => {
                            $('#phoneNumberSection').slideUp(300);
                            $('#togglePhoneSection i').removeClass('fa-minus').addClass('fa-plus');
                            $('#togglePhoneSection').html('<i class="fa fa-plus me-1"></i> Change Phone Number');
                            btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save Number');

                            // Auto-hide success message after 3 seconds
                            setTimeout(() => {
                                $('#phoneSaveStatus').slideUp(300);
                            }, 3000);
                        }, 2000);
                    } else {
                        showPhoneStatus('error', response.message || 'Failed to save phone number');
                        btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save Number');
                    }
                },
                error: function(xhr) {
                    showPhoneStatus('error', 'Server error. Please try again.');
                    btn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save Number');
                }
            });
        });

        // ============================================
        // UPDATE ALL PHONE DISPLAYS WITHOUT RELOAD
        // ============================================

        function updateAllPhoneDisplays(phoneNumber) {
            const maskedPhone = phoneNumber.substring(0, 3) + '****' + phoneNumber.substring(7);

            // 1. Update the main alert display
            $('#phoneDisplaySpan').text(maskedPhone);

            // 2. Update the form input field
            $('#userPhoneNumber').val(phoneNumber);

            // 3. Update hidden field for JS use
            $('#payrollPhoneNumber').val(phoneNumber);

            // 4. Update any SweetAlert dialogs that might be open
            if (window.swal && window.swal.getHtmlContainer) {
                const currentDialog = window.swal.getHtmlContainer();
                if (currentDialog) {
                    const phoneTexts = currentDialog.querySelectorAll('*');
                    phoneTexts.forEach(element => {
                        if (element.textContent.includes('OTP will be sent to')) {
                            element.textContent = `OTP will be sent to: ${maskedPhone}`;
                        }
                    });
                }
            }

            // 5. Update the phone icon alert message
            const alertHtml = `
                <i class="fa fa-mobile me-2 fs-5"></i>
                <div class="flex-grow-1">
                    OTP will be sent to your registered mobile:
                    <span class="fw-bold" id="phoneDisplaySpan">${maskedPhone}</span>
                </div>
                <button type="button" class="btn btn-sm btn-outline-info" id="changeMobileBtn">
                    <i class="fa fa-pencil me-1"></i>Change
                </button>
            `;

            $('.alert-info').html(alertHtml);

            // 6. Re-attach event listener to new change button
            $('#changeMobileBtn').off('click').on('click', function() {
                $('#phoneNumberSection').slideDown(300);
                $('#togglePhoneSection i').removeClass('fa-plus').addClass('fa-minus');
                $('#togglePhoneSection').html('<i class="fa fa-minus me-1"></i> Hide Phone Number');
            });

            console.log('Phone displays updated to:', maskedPhone);
        }

        // Helper function to show status
        function showPhoneStatus(type, message) {
            const statusDiv = $('#phoneSaveStatus');
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';

            statusDiv.html(`
                <div class="alert ${alertClass} d-flex align-items-center py-2 px-3 small mb-0">
                    <i class="fa ${icon} me-2"></i>
                    <div>${message}</div>
                </div>
            `).slideDown(300);

            // Auto hide after 5 seconds for success messages
            if (type === 'success') {
                setTimeout(() => {
                    statusDiv.slideUp(300);
                }, 5000);
            }
        }

        // ============================================
        // LOCK/UNLOCK HANDLER
        // ============================================

        // ============================================
        // SIMPLIFIED LOCK/UNLOCK HANDLER - MINIMUM CHANGE VERSION
        // ============================================

        // ============================================
        // LOCK/UNLOCK HANDLER
        // ============================================

        $(document).on('change', '#lock_payroll', function() {
            const checkbox = $(this);
            const isLocked = checkbox.is(':checked');
            const action = isLocked ? 'lock' : 'unlock';

            // Store current state
            currentAction = action;
            currentCheckbox = checkbox;
            currentOriginalState = isLocked;

            // Revert checkbox temporarily
            checkbox.prop('checked', !isLocked);

            // ✅ Get phone number - Priority: updatedPhone > payrollPhone > userPhone
            let userPhone = window.updatedUserPhone || $('#payrollPhoneNumber').val() || "{{ auth()->user()->emp_phone ?? '' }}";

            // Direct OTP send without modals
            Swal.fire({
                title: `${action.toUpperCase()} Settings`,
                html: `
                    <div style="text-align: center; padding: 15px;">
                        <div style="width: 70px; height: 70px; background: ${action === 'lock' ? '#fee2e2' : '#d1fae5'};
                            border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 15px;">
                            <i class="fa ${action === 'lock' ? 'fa-lock' : 'fa-unlock'}"
                            style="font-size: 28px; color: ${action === 'lock' ? '#dc2626' : '#059669'};"></i>
                        </div>
                        <h5 style="color: #1f2937; margin-bottom: 10px;">${action === 'lock' ? 'Lock' : 'Unlock'} Settings</h5>
                        <p style="color: #6b7280; font-size: 14px; margin-bottom: 5px;">
                            ${userPhone ? `OTP will be sent to: ${userPhone.substring(0, 3)}****${userPhone.substring(7)}` : 'Please enter phone number to receive OTP'}
                        </p>
                        <p style="color: #9ca3af; font-size: 13px;">
                            ${action === 'lock' ? 'Locking will prevent unauthorized changes.' : 'Unlocking will allow you to modify settings.'}
                        </p>
                    </div>
                `,
                icon: false,
                showCancelButton: true,
                confirmButtonText: `Send OTP`,
                cancelButtonText: 'Cancel',
                confirmButtonColor: action === 'lock' ? '#dc2626' : '#059669',
                cancelButtonColor: '#6b7280',
            }).then((result) => {
                if (result.isConfirmed) {
                    if (userPhone) {
                        // Directly send OTP
                        sendOtpRequest(action, userPhone);
                    } else {
                        // No phone number, show enter number modal
                        $('#differentNumberModal').modal('show');
                    }
                } else {
                    // Cancelled - show message
                    Swal.fire({
                        icon: 'info',
                        title: 'Cancelled',
                        text: 'Operation cancelled',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
        });
        // Helper function to check if number should be updated
        function checkIfShouldUpdate(currentPhone, savedPhone, formPhone) {
            // If we have form phone and it's different from saved
            if (formPhone && formPhone.length === 10) {
                // Check against the updated phone if available
                const comparePhone = window.updatedUserPhone || savedPhone;
                return formPhone !== comparePhone;
            }
            return false;
        }

        // ============================================
        // DIFFERENT NUMBER MODAL WITH UPDATE OPTION
        // ============================================

        function showDifferentNumberModal(action, savedPhone) {
            const modal = $('#differentNumberModal');

            // Clear previous input
            $('#differentPhoneInput').val('').removeClass('is-valid is-invalid');

            // Phone input validation
            $('#differentPhoneInput').on('input', function() {
                let value = $(this).val().replace(/\D/g, '');
                if (value.length > 10) {
                    value = value.substring(0, 10);
                }
                $(this).val(value);

                if (value.length === 10) {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                } else if (value.length > 0) {
                    $(this).removeClass('is-valid').addClass('is-invalid');
                } else {
                    $(this).removeClass('is-valid is-invalid');
                }
            });

            $('#confirmDifferentNumberBtn').off('click').on('click', function() {
                const phoneNumber = $('#differentPhoneInput').val().trim();
                const shouldUpdate = $('#updatePhoneCheckbox').is(':checked');

                // Validation
                if (!phoneNumber) {
                    $('#differentPhoneInput').addClass('is-invalid');
                    Swal.fire({
                        icon: 'error',
                        title: 'Missing Number',
                        text: 'Please enter phone number',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                if (phoneNumber.length !== 10 || !/^\d+$/.test(phoneNumber)) {
                    $('#differentPhoneInput').addClass('is-invalid');
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Number',
                        text: 'Please enter valid 10-digit phone number',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                selectedPhoneNumber = phoneNumber;
                modal.modal('hide');

                if (shouldUpdate) {
                    // Update phone number first, then send OTP
                    updatePhoneNumber(phoneNumber, action);
                } else {
                    // Just send OTP without updating
                    sendOtpRequest(action, phoneNumber);
                }
            });

            modal.modal('show');
        }



        function showDifferentNumberError(message) {
            // Remove existing error
            $('.different-number-error').remove();

            // Add error message
            const errorHtml = `
                <div class="alert alert-danger py-2 px-3 small mt-2 different-number-error" role="alert">
                    <i class="fa fa-exclamation-circle me-2"></i>
                    ${message}
                </div>
            `;

            $('#differentPhoneInput').after(errorHtml);
        }

        // ============================================
        // UPDATE PHONE CONFIRMATION MODAL
        // ============================================

     function showUpdatePhoneConfirmation(phoneNumber, action) {
        const modal = $('#updatePhoneModal');
        const userPhone = "{{ auth()->user()->emp_phone ?? '' }}";

        let modalContent = '';

        if (userPhone) {
            // Have existing saved number
            modalContent = `
                <div class="text-center mb-3">
                    <div class="mb-3">
                        <div class="update-icon mx-auto" style="width: 80px; height: 80px; background: #fef3c7; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fa fa-exchange-alt fa-2x text-warning"></i>
                        </div>
                    </div>
                    <h5 class="mb-3">Update Phone Number?</h5>

                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <div class="row text-start">
                                <div class="col-6">
                                    <p class="small text-muted mb-1">Current Number</p>
                                    <p class="fw-bold text-danger">${userPhone}</p>
                                </div>
                                <div class="col-6">
                                    <p class="small text-muted mb-1">New Number</p>
                                    <p class="fw-bold text-success">${phoneNumber}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="text-muted small">
                        <i class="fa fa-info-circle me-1"></i>
                        This number will be saved and used for all future OTP verifications.
                    </p>
                </div>
            `;
        } else {
            // No existing number - first time setup
            modalContent = `
                <div class="text-center mb-3">
                    <div class="mb-3">
                        <div class="update-icon mx-auto" style="width: 80px; height: 80px; background: #d1fae5; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fa fa-save fa-2x text-success"></i>
                        </div>
                    </div>
                    <h5 class="mb-3">Save Phone Number?</h5>

                    <div class="mb-4">
                        <p class="fw-bold text-primary fs-5">${phoneNumber}</p>
                    </div>

                    <p class="text-muted small">
                        <i class="fa fa-info-circle me-1"></i>
                        This number will be saved as your registered phone number for all future OTP verifications.
                    </p>
                </div>
            `;
        }

        modal.find('.modal-body').html(modalContent);

        // Update button text based on context
        if (userPhone) {
            modal.find('#confirmUpdateBtn').html('<i class="fa fa-sync-alt me-1"></i> Update & Continue');
        } else {
            modal.find('#confirmUpdateBtn').html('<i class="fa fa-save me-1"></i> Save & Continue');
        }

        // Set up confirm button
        $('#confirmUpdateBtn').off('click').on('click', function() {
            modal.modal('hide');
            updatePhoneNumber(phoneNumber, action);
        });

        // Set up skip button
        $('#skipUpdateBtn').off('click').on('click', function() {
            modal.modal('hide');
            // Send OTP without updating phone number
            sendOtpRequest(action, phoneNumber);
        });

        modal.modal('show');
    }

        // ============================================
        // UPDATE PHONE NUMBER FUNCTION
        // ============================================

       // ============================================
// UPDATE PHONE NUMBER FUNCTION
// ============================================

// updatePhoneNumber function में success callback में बदलाव
function updatePhoneNumber(phoneNumber, action) {
    Swal.fire({
        title: 'Updating Phone Number...',
        text: 'Please wait while we update your phone number',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        url: "{{ route('payroll.setting.updatePhone') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            phone_number: phoneNumber
        },
        success: function(response) {
            Swal.close();
            if (response.status) {
                // ✅ Update phone displays
                updateAllPhoneDisplays(phoneNumber);
                window.updatedUserPhone = phoneNumber;

                // ✅ तुरंत page reload करें
                Swal.fire({
                    icon: 'success',
                    title: 'Phone Number Updated!',
                    text: 'Your phone number has been updated. Page will reload...',
                    timer: 1000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload(); // ✅ Page reload
                });
            } else {
                // OTP भेजें भले ही update fail हो
                sendOtpRequest(action, phoneNumber);
            }
        },
        error: function() {
            Swal.close();
            // फिर भी OTP send करें
            sendOtpRequest(action, phoneNumber);
        }
    });
}


// ============================================
// UPDATE PHONE DISPLAY IN UI WITHOUT RELOAD
// ============================================

function updatePhoneDisplayInUI(newPhoneNumber, maskedPhone) {
    // 1. Update the alert display
    if ($('.alert-info .fw-bold').length > 0) {
        $('.alert-info .fw-bold').text(maskedPhone);
    }

    // 2. Update the form input field
    $('#userPhoneNumber').val(newPhoneNumber);

    // 3. Update the modal display if open
    $('#displayPhoneNumber').text(newPhoneNumber);

    // 4. Update the confirmation dialog text
    const userPhoneEl = $('p:contains("OTP will be sent to")');
    if (userPhoneEl.length) {
        userPhoneEl.text(`OTP will be sent to: ${maskedPhone}`);
    }
}

        // ============================================
        // OTP MANAGEMENT
        // ============================================

        function sendOtpRequest(action, phoneNumber) {
            // Show loading
            Swal.fire({
                title: 'Sending OTP...',
                text: 'Please wait while we send OTP to your mobile',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '/admin/settings/payroll/send-otp',
                method: 'POST',
                data: {
                    phone: phoneNumber,
                    action: action,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.close();
                    if (response.status) {
                        showOtpModal(action, phoneNumber);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed to Send OTP',
                            text: response.message || 'Please try again later.',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function() {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to send OTP. Please check your connection and try again.',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }

        function showOtpModal(action, phoneNumber) {
            const modal = $('#otpModal');
            const maskedPhone = phoneNumber.substring(0, 3) + '****' + phoneNumber.substring(7);

            $('#otpPhoneNumber').text(`OTP sent to: ${maskedPhone}`);
            $('#otpInput').val('');
            $('#otpError').hide();

            // Start timer
            startOtpTimer();

            // Set up verify button
            $('#verifyOtpBtn').off('click').on('click', function() {
                verifyOtp(action, phoneNumber);
            });

            // Set up resend button
            $('#resendOtpBtn').off('click').on('click', function() {
                if (!$(this).prop('disabled')) {
                    sendOtpRequest(action, phoneNumber);
                }
            });

            modal.modal('show');
        }

        function startOtpTimer() {
            otpTimeLeft = 120;
            clearInterval(otpTimerInterval);

            $('#otpTimer').text(formatTime(otpTimeLeft));
            $('#resendOtpBtn').prop('disabled', true);

            otpTimerInterval = setInterval(function() {
                otpTimeLeft--;
                $('#otpTimer').text(formatTime(otpTimeLeft));

                if (otpTimeLeft <= 0) {
                    clearInterval(otpTimerInterval);
                    $('#resendOtpBtn').prop('disabled', false);
                }
            }, 1000);
        }

        function formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = seconds % 60;
            return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        }

    function verifyOtp(action, phoneNumber) {
    const otp = $('#otpInput').val().trim();

    if (!otp || otp.length !== 6) {
        $('#otpError').text('Please enter valid 6-digit OTP').show();
        return;
    }

    $('#verifyOtpBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-2"></i> Verifying...');
    $('#otpError').hide();

    $.ajax({
        url: "{{ route('payroll.setting.verifyOtp') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            otp: otp,
            action: action, // ✅ action parameter add करें
        },
        success: function(response) {
            if (response.status) {
                clearInterval(otpTimerInterval);
                $('#otpModal').modal('hide');

                // ✅ OTP verify होने के बाद ही UI update करें
                if (currentCheckbox) {
                    // Checkbox को desired state में set करें
                    currentCheckbox.prop('checked', action === 'lock');

                    // Update badge
                    const badge = $('#lockStatusBadge');
                    badge.removeClass('bg-danger bg-success')
                        .addClass(action === 'lock' ? 'bg-danger' : 'bg-success');
                    badge.html('<i class="fa ' + (action === 'lock' ? 'fa-lock' : 'fa-unlock') + ' me-1"></i>' +
                        (action === 'lock' ? 'Locked' : 'Unlocked'));

                    // Enable/disable form fields based on lock state
                    toggleFormFields(action === 'lock');

                    // Show success message
                    showSuccessMessage(action);

                    // Reload page after 2 seconds to sync with database
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                }
            } else {
                // ❌ OTP verify नहीं हुआ
                $('#otpError').text(response.message || 'Invalid OTP. Please try again.').show();
                $('#verifyOtpBtn').prop('disabled', false).html('<i class="fa fa-check me-2"></i>Verify & Continue');

                // ✅ Checkbox को revert करें (original state में)
                if (currentCheckbox) {
                    currentCheckbox.prop('checked', !(action === 'lock'));
                }
            }
        },
        error: function() {
            $('#otpError').text('Verification failed. Please try again.').show();
            $('#verifyOtpBtn').prop('disabled', false).html('<i class="fa fa-check me-2"></i>Verify & Continue');

            // ✅ Error होने पर checkbox revert करें
            if (currentCheckbox) {
                currentCheckbox.prop('checked', !(action === 'lock'));
            }
        }
    });
}

        // Helper function to toggle form fields
        function toggleFormFields(isLocked) {
            if (isLocked) {
                // Locked - disable form fields
                $('#payroll_cycle, #payroll_mode, #include_with_salary, #year_type').prop('disabled', true);
                $('#submitPayrollForm').prop('disabled', true).addClass('disabled');
            } else {
                // Unlocked - enable form fields
                $('#payroll_cycle, #payroll_mode, #include_with_salary, #year_type').prop('disabled', false);
                $('#submitPayrollForm').prop('disabled', false).removeClass('disabled');
            }
        }

        // ============================================
        // SUCCESS & CANCELLATION MESSAGES
        // ============================================

        function showSuccessMessage(action) {
            $('#successTitle').text(`${action === 'lock' ? 'Locked' : 'Unlocked'} Successfully!`);
            $('#successMessage').text(`Payroll settings have been ${action}ed successfully.`);
            $('#successModal').modal('show');

            // Reload after 2 seconds to update UI
            setTimeout(() => {
                location.reload();
            }, 2000);
        }

        function showCancelledMessage(action) {
            Swal.fire({
                icon: 'info',
                title: 'Cancelled',
                text: `${action.charAt(0).toUpperCase() + action.slice(1)} operation cancelled`,
                timer: 1500,
                showConfirmButton: false
            });
        }

        // ============================================
        // FORM SUBMISSION
        // ============================================

        $('#payrollCycleForm').on('submit', function(e) {
            e.preventDefault();

            // Validate required fields
            const requiredFields = ['payroll_cycle', 'payroll_mode', 'year_type', 'include_with_salary','include_tada'];
            let missingFields = [];

            requiredFields.forEach(field => {
                if (!$(`[name="${field}"]`).val()) {
                    const readableName = field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    missingFields.push(readableName);
                }
            });

            if (missingFields.length > 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Required Fields Missing',
                    html: `Please fill in:<br><strong>${missingFields.join(', ')}</strong>`,
                    confirmButtonText: 'OK'
                });
                return;
            }

            // Submit form
            const submitBtn = $('#submitPayrollForm');
            submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-2"></i> Saving...');

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            $('#payrollCycleModal').modal('hide');
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message,
                            confirmButtonText: 'OK'
                        });
                        submitBtn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save Settings');
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Something went wrong. Please try again.',
                        confirmButtonText: 'OK'
                    });
                    submitBtn.prop('disabled', false).html('<i class="fa fa-save me-1"></i> Save Settings');
                }
            });
        });

        // ============================================
        // UNLOCK BUTTONS
        // ============================================

        $(document).on('click', '#unlockSettingsBtn, #unlockToEditBtn', function() {
            $('#lock_payroll').trigger('change');
        });

        // ============================================
        // INITIALIZE
        // ============================================

        // Initialize phone input
        formatPhoneInput();
    });
</script>
@endsection
