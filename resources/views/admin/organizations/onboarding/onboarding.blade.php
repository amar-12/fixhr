@extends('auth.admin.authlayout.master_simple')
@section('title', 'Quick Setup')

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/onboarding.css') }}">
@endsection

@section('content')
    <div class="wizard-container" id="wizard">
        <!-- Header -->
        <div class="wizard-header">
            <div class="wizard-logo">
                <i class="fas fa-rocket"></i>
            </div>
            <h1 class="wizard-title">Welcome to FIX HR!</h1>
            <p class="wizard-subtitle">Let's set up your HRMS in just a few simple steps</p>
        </div>

        <!-- Progress Steps -->
        <div class="progress-container">
            <div class="steps-indicator">
                <div class="progress-line" id="progressLine" style="width: 0%"></div>

                <div class="step active" data-step="1">
                    <div class="step-circle">
                        <i class="fas fa-forward"></i>
                    </div>
                    <span class="step-label">Let's Start</span>
                </div>

                <div class="step" data-step="2">
                    <div class="step-circle">
                        <i class="fas fa-id-badge"></i>
                    </div>
                    <span class="step-label">Employee Code</span>
                </div>

                <div class="step" data-step="3">
                    <div class="step-circle">
                        <i class="fas fa-clock"></i>
                    </div>
                    <span class="step-label">Shift Policy</span>
                </div>

                <div class="step" data-step="4">
                    <div class="step-circle">
                        <i class="fas fa-calendar-week"></i>
                    </div>
                    <span class="step-label">Weekly Policy</span>
                </div>

                <div class="step" data-step="5">
                    <div class="step-circle">
                        <i class="fas fa-umbrella-beach"></i>
                    </div>
                    <span class="step-label">Leave Policy</span>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="wizard-content">
            <!-- Step 1: Intro -->
            <div class="step-content active" id="step1">
                <div class="content-header">
                    <div class="content-icon">
                        <i class="fas fa-forward"></i>
                    </div>
                    <h2 class="content-title">Let's Setup Your Business Settings</h2>
                    <p class="content-description">Answer simple questions to get started or skip to setup defaults</p>
                </div>
            </div>

            <!-- Step 2: Employee Code -->
            <div class="step-content" id="step2">

                <div class="row g-3">

                    {{-- Header --}}
                    <div class="col-12">

                        <div class="content-header">
                            <div class="content-icon">
                                <i class="fas fa-id-badge"></i>
                            </div>
                            <h2 class="content-title">Generate Employee Code</h2>
                            <p class="content-description">Choose how you want to generate employee code</p>
                        </div>

                    </div>

                    <div class="col-12 col-md-12 col-lg-12">

                        <form action="" method="post" id="empCodeForm">

                            <div class="row g-3">

                                {{-- Manual --}}
                                <div class="col-12 col-md-6 col-lg-6">
                                    <div class="radio-card" onclick="selectCodeType('manual')">
                                        <input type="radio" name="b_emp_code_type" id="manual" value="191">
                                        <div class="radio-card-icon">
                                            <i class="fas fa-keyboard"></i>
                                        </div>
                                        <div class="radio-card-title">Manual Entry</div>
                                        <div class="radio-card-description">Enter employee codes manually for each employee
                                        </div>
                                    </div>
                                </div>

                                {{-- Auto --}}
                                <div class="col-12 col-md-6 col-lg-6">
                                    <div class="radio-card" onclick="selectCodeType('auto')">
                                        <input type="radio" name="b_emp_code_type" id="auto" value="190">
                                        <div class="radio-card-icon">
                                            <i class="fas fa-magic"></i>
                                        </div>
                                        <div class="radio-card-title">Auto Generate</div>
                                        <div class="radio-card-description">Automatically generate sequential employee codes
                                        </div>
                                    </div>
                                </div>

                                {{-- Prefix --}}
                                <div class="col-12 col-md-12 col-lg-4" id="prefixSection" style="display: none;">
                                    <div class="form-section">
                                        <label class="form-label">
                                            <i class="fas fa-tag"></i>
                                            Employee Code Prefix
                                        </label>
                                        <div class="input-with-icon">
                                            <i class="fas fa-hashtag input-icon"></i>
                                            <input type="text" class="form-control" placeholder="e.g., EMP, FXH, FIXHR"
                                                id="b_emp_code" name="b_emp_code">
                                        </div>
                                        <div class="info-box">
                                            <i class="fas fa-info-circle"></i>
                                            <div class="info-box-content">
                                                <strong>Example:</strong> If you enter "EMP", employee codes will be
                                                generated
                                                as EMP001,
                                                EMP002, EMP003, etc.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </form>

                    </div>

                </div>
            </div>

            <!-- Step 3: Shift Policy -->
            <div class="step-content" id="step3">

                <form action="" id="shiftPolicyForm" method="POST">

                    <div class="row g-3">

                        {{-- Header --}}
                        <div class="col-12">

                            <div class="content-header">
                                <div class="content-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <h2 class="content-title">Shift Configuration</h2>
                                <p class="content-description">Define your standard shift timings and policies</p>
                            </div>

                        </div>

                        {{-- Shift Type --}}
                        <div class="col-12 col-md-6 col-lg-6 col-xl-3">

                            <div class="form-section h-fill">
                                <label class="form-label">
                                    <i class="fas fa-user-clock"></i>
                                    Shift Type
                                </label>
                                <select class="form-select select2" id="shiftType" name="shift_type_id">
                                    <option value="">Select Shift Type</option>
                                    @foreach ($shiftType as $shift)
                                        <option value="{{ $shift->m_id }}"
                                            @if ($shift->m_id == 244) selected @endif>
                                            {{ $shift->m_name }}</option>
                                    @endforeach
                                </select>
                                <div class="info-box">
                                    <i class="fas fa-info-circle"></i>
                                    <div class="info-box-content">
                                        You can make more shifts later.
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{-- Shift Timings --}}
                        <div class="col-12 col-md-6 col-lg-6 col-xl-4">

                            <div class="form-section h-fill">
                                <label class="form-label">
                                    <i class="fas fa-business-time"></i>
                                    Shift Timings
                                </label>
                                <div class="time-inputs">
                                    <div>
                                        <label class="form-label" style="font-size: 0.85rem;">Start Time</label>
                                        <input type="text" class="form-control time_format_24hrs" id="startTime"
                                            placeholder="HH:MM" name="start_time" value="10:00">
                                    </div>
                                    <div>
                                        <label class="form-label" style="font-size: 0.85rem;">End Time</label>
                                        <input type="text" class="form-control time_format_24hrs" id="endTime"
                                            placeholder="HH:MM" name="end_time" value="18:30">
                                    </div>
                                </div>
                                <div class="info-box">
                                    <i class="fas fa-info-circle"></i>
                                    <div class="info-box-content">
                                        Time should be in 24-hour format.
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{-- Grace --}}
                        <div class="col-12 col-md-6 col-lg-6 col-xl-3">

                            <div class="form-section h-fill">
                                <label class="form-label">
                                    <i class="fas fa-hourglass-half"></i>
                                    Grace Period (minutes)
                                </label>
                                <input type="number" class="form-control" placeholder="e.g., 15" min="0"
                                    id="gracePeriod" name="grace_minutes">
                                <div class="info-box">
                                    <i class="fas fa-info-circle"></i>
                                    <div class="info-box-content">
                                        Grace period allows employees to check-in late without being marked late.
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{-- Break --}}
                        <div class="col-12 col-md-6 col-lg-6 col-xl-2">

                            <div class="form-section h-fill">
                                <label class="form-label">
                                    <i class="fas fa-coffee"></i>
                                    Break Duration (minutes)
                                </label>
                                <input type="number" class="form-control" placeholder="e.g., 60" min="0"
                                    id="breakDuration" name="break_duration">
                            </div>

                        </div>

                    </div>

                </form>

            </div>

            <!-- Step 4: Weekly Policy -->
            <div class="step-content" id="step4">

                <form action="" method="post" id="weekOffPolicyForm">

                    <div class="row g-4">

                        {{-- Header --}}
                        <div class="col-12">

                            <div class="content-header">
                                <div class="content-icon">
                                    <i class="fas fa-calendar-week"></i>
                                </div>
                                <h2 class="content-title">Weekly Off Configuration</h2>
                                <p class="content-description">Select your weekly off days and payment policy</p>
                            </div>

                        </div>

                        <div class="col-12">

                            <div class="form-section">

                                <div class="row mb-4 align-items-center">

                                    <label class="col-8 form-label mb-0">
                                        <i class="fas fa-calendar-day"></i>
                                        Select Week Off Days
                                    </label>

                                    {{-- Paid/Unpaid --}}
                                    <div class="col-4 d-flex justify-content-end gap-3 align-items-center">
                                        <label for="paidWeekOff" class="mb-0">
                                            <strong>Paid Week Off</strong>
                                        </label>
                                        <label class="switch mb-0">
                                            <input type="checkbox" id="paidWeekOff" checked name="paidWeekOff">
                                            <span class="slider"></span>
                                        </label>
                                        <i class="fas fa-circle-info" data-bs-toggle="tooltip"
                                            data-bs-title="Disable if week off days should be unpaid"
                                            data-bs-custom-class="custom-tooltip"></i>
                                    </div>

                                </div>

                                {{-- Weekly Days --}}
                                <div class="seven-cols">

                                    <div>
                                        <div class="checkbox-card" onclick="toggleDay(this, 'monday')">
                                            <input type="checkbox" id="monday" value="328" name="dayIds[]">
                                            <div class="day-checkbox">
                                                <label for="monday">
                                                    <i class="fas fa-calendar"></i>
                                                    Monday
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="checkbox-card" onclick="toggleDay(this, 'tuesday')">
                                            <input type="checkbox" id="tuesday" value="329" name="dayIds[]">
                                            <div class="day-checkbox">
                                                <label for="tuesday">
                                                    <i class="fas fa-calendar"></i>
                                                    Tuesday
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="checkbox-card" onclick="toggleDay(this, 'wednesday')">
                                            <input type="checkbox" id="wednesday" value="330" name="dayIds[]">
                                            <div class="day-checkbox">
                                                <label for="wednesday">
                                                    <i class="fas fa-calendar"></i>
                                                    Wednesday
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="checkbox-card" onclick="toggleDay(this, 'thursday')">
                                            <input type="checkbox" id="thursday" value="331" name="dayIds[]">
                                            <div class="day-checkbox">
                                                <label for="thursday">
                                                    <i class="fas fa-calendar"></i>
                                                    Thursday
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="checkbox-card" onclick="toggleDay(this, 'friday')">
                                            <input type="checkbox" id="friday" value="332" name="dayIds[]">
                                            <div class="day-checkbox">
                                                <label for="friday">
                                                    <i class="fas fa-calendar"></i>
                                                    Friday
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="checkbox-card" onclick="toggleDay(this, 'saturday')">
                                            <input type="checkbox" id="saturday" value="333" name="dayIds[]">
                                            <div class="day-checkbox">
                                                <label for="saturday">
                                                    <i class="fas fa-calendar"></i>
                                                    Saturday
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="checkbox-card" onclick="toggleDay(this, 'sunday')">
                                            <input type="checkbox" id="sunday" value="327" name="dayIds[]">
                                            <div class="day-checkbox">
                                                <label for="sunday">
                                                    <i class="fas fa-calendar"></i>
                                                    Sunday
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </form>

            </div>

            <!-- Step 5: Leave Policy -->
            <div class="step-content" id="step5">

                <form action="" method="post" id="leavePolicyForm">

                    <div class="row g-4">

                        {{-- Header --}}
                        <div class="col-12 col-md-4 col-lg-4">

                            <div class="content-header">
                                <div class="content-icon">
                                    <i class="fas fa-umbrella-beach"></i>
                                </div>
                                <h2 class="content-title">Leave Policy Setup</h2>
                                <p class="content-description">Configure the types of leave available to your employees</p>
                            </div>

                        </div>

                        {{-- Leave Categories --}}
                        <div class="col-12 col-md-8 col-lg-8">

                            <div class="form-section" id="leaveTypesContainer">
                                <label class="form-label">
                                    <i class="fas fa-list"></i>
                                    Leave Types
                                </label>

                                <div class="leave-input-group" id="leave1">
                                    <div>
                                        <select class="form-select" name="leaveCatIds[]">
                                            <option value="">Select Leave Type</option>
                                            @foreach ($leaveCats as $cat)
                                                <option value="{{ $cat->m_id }}">{{ $cat->m_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <input type="number" name="leave_days[]" class="form-control"
                                            placeholder="Days per month" min="0" step="0.5">
                                    </div>
                                    <div class="d-flex align-items-center h-fill">
                                        <button class="delete-leave-btn" onclick="removeLeave('leave1')" disabled type="button">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>

                                <button class="add-leave-btn" type="button" onclick="addLeaveType()">
                                    <i class="fas fa-plus-circle"></i>
                                    Add Another Leave Type
                                </button>
                            </div>

                        </div>

                        {{-- Info --}}
                        <div class="col-12">

                            <div class="info-box">
                                <i class="fas fa-lightbulb"></i>
                                <div class="info-box-content">
                                    <strong>Tip:</strong> You can always modify these settings later from the Settings
                                    panel.
                                    These are
                                    just the default leave types for your organization.
                                </div>
                            </div>

                        </div>

                    </div>

                </form>

            </div>
        </div>

        <!-- Actions -->
        <div class="wizard-actions">
            <div>
                <button class="btn-wizard btn-back" id="btnBack" onclick="previousStep()" style="display: none;">
                    <i class="fas fa-arrow-left"></i>
                    Back
                </button>
            </div>
            <div class="d-flex gap-2">
                <button class="btn-wizard btn-skip" id="btnSkip" onclick="skipStep()">
                    Skip for now
                </button>
                <button class="btn-wizard btn-next" id="btnNext" onclick="nextStep()">
                    Next
                    <i class="fas fa-arrow-right"></i>
                </button>
                <button class="btn-wizard btn-finish" id="btnFinish" style="display: none;">
                    Finish Setup
                    <i class="fas fa-check"></i>
                </button>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @php
        $pageData = [
            'csrf' => csrf_token(),
            'routes' => [
                'submitUrl' => route('demo.setup.store'),
                'accountSettings' => route('account.settings'),
            ],
            'leaveCats' => $leaveCats,
            'catCounts' => count($leaveCats),
        ];
    @endphp

    <script>
        window.pageData = @json($pageData);
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/onboarding.js') }}"></script>
@endsection
