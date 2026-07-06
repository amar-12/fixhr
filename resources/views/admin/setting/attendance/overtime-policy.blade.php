@extends('admin.layout.master')
@section('title', 'Overtime Rules')

@section('css')
    <style>
        .disabled {
            pointer-events: none;
            opacity: 0.5;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
    <style>
        .settings-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .settings-section {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 25px;
            border-left: 4px solid #1877f2;
        }
        .section-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .form-check-label {
            font-weight: 500;
        }

        /* Base switch container */
        .toggle-switch {
          position: relative;
          width: 50px;
          height: 26px;
          appearance: none;
          -webkit-appearance: none;
          background-color: #d1d5db; /* gray-300 */
          outline: none;
          border-radius: 50px;
          transition: background-color 0.3s ease;
          cursor: pointer;
        }

        /* The round knob */
        .toggle-switch::before {
          content: "";
          position: absolute;
          top: 3px;
          left: 3px;
          width: 20px;
          height: 20px;
          background-color: #fff;
          border-radius: 50%;
          transition: transform 0.3s ease;
        }

        /* When checked (active) */
        .toggle-switch:checked {
          background-color: #2563eb; /* Tailwind blue-600 */
        }

        /* Move the knob to the right when checked */
        .toggle-switch:checked::before {
          transform: translateX(24px);
        }

        /* Hover effect */
        .toggle-switch:hover {
          background-color: #93c5fd; /* lighter blue on hover */
        }

        input:checked + .toggle-slider {
            background-color: #1877f2;
        }
        input:checked + .toggle-slider:before {
            transform: translateX(30px);
        }
        .setting-card {
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
            background: #f8f9fa;
        }
        .preview-box {
            background: #f8f9fa;
            border-left: 4px solid #1877f2;
            padding: 15px;
            border-radius: 4px;
        }
        .nav-tabs .nav-link.active {
            color: white;
            border: none;
        }
        .nav-tabs .nav-link {
            border: 1px solid #dee2e6;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endsection

@section('content')
    <div class="p-0 my-3">
        <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
            <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li><a href="{{ url('/admin/settings/attendance') }}">Attendance Settings</a></li>
            <li class="active"><span><b>Overtime Rules</b></span></li>
        </ol>
    </div>

    <div class="settings-container">
        <ul class="nav nav-pills mb-4" id="settingsTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="day-type-tab" data-bs-toggle="tab" data-bs-target="#day-type" type="button" role="tab">Day Type Rules</button>
            </li>
            <!-- <li class="nav-item" role="presentation">
                <button class="nav-link" id="shift-break-tab" data-bs-toggle="tab" data-bs-target="#shift-break" type="button" role="tab">Shift & Break Rules</button>
            </li> -->
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="eligibility-tab" data-bs-toggle="tab" data-bs-target="#eligibility" type="button" role="tab">Eligibility Rules</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="ot-basis-tab" data-bs-toggle="tab" data-bs-target="#ot-basis" type="button" role="tab">OT Basis Rules</button>
            </li>
        </ul>
        <form id="overtime-settings-form" method="POST">
            <div class="settings-section mb-4">
                <div class="setting-card">
                    <div class="form-check form-switch d-flex justify-content-between align-items-center">
                        <div>
                            <label class="form-check-label" for="is_enabled">Enable Overtime Rules</label>
                            <p class="text-muted small mb-0">
                                Turn this off to temporarily disable all overtime rule settings
                            </p>
                        </div>
                        <input class="form-check-input" type="checkbox" id="is_enabled" {{ $overtimeRule?->ot_is_enabled ? 'checked' : '' }}>
                    </div>
                </div>
            </div>
            
            <div class="tab-content" id="settingsTabContent">
                <!-- Day Type Rules Tab -->
                <div class="tab-pane fade show active" id="day-type" role="tabpanel">
                    <div class="row">
                        <div class="settings-section col-md-8">
                            <div class="section-header">
                                <h6><i class="fas fa-calendar-alt me-2"></i>Day Type Rules</h6>
                            </div>
                            
                            <div class="setting-card">
                                <div class="form-check form-switch d-flex justify-content-between align-items-center">
                                    <div>
                                        <label class="form-check-label" for="ot-working-days">
                                            Apply OT on Working Days
                                        </label>
                                        <p class="text-muted small mb-0">Enable overtime calculation for regular working days</p>
                                    </div>
                                    <input class="form-check-input" type="checkbox" id="ot-working-days" {{ $overtimeRule?->ot_working_day == 1 ? 'checked' : '' }}>
                                </div>
                            </div>
                            
                            <div class="setting-card">
                                <div class="form-check form-switch d-flex justify-content-between align-items-center">
                                    <div>
                                        <label class="form-check-label" for="ot-holidays">
                                            Apply OT on Holidays/Week Offs
                                        </label>
                                        <p class="text-muted small mb-0">Enable overtime calculation for holidays and weekly offs</p>
                                    </div>
                                    <input class="form-check-input" type="checkbox" id="ot-holidays" {{ $overtimeRule?->ot_non_working_day == 1 ? 'checked' : '' }}>
                                </div>
                            </div>
                            
                            {{--<div class="setting-card">
                                <div class="mb-3">
                                    <label for="max-comp-off" class="form-label">Max Comp-Off Hours Per Day</label>
                                    <p class="text-muted small mb-2">Maximum hours that can be marked as compensatory off on holidays/week offs</p>
                                    <div class="input-group" style="max-width: 300px;">
                                        <input type="number" class="form-control" id="max-comp-off" value="{{ $overtimeRule?->ot_max_co_per_day ?? 8 }}" min="0" max="24" step="0.5">
                                        <span class="input-group-text">hours</span>
                                    </div>
                                </div>
                            </div>--}}
                        </div>

                        <div class="preview-box col-md-4">
                            <h5><i class="fa fa-info-circle me-1"></i> Policy Preview</h5>
                            <p class="mb-1">On <strong>Working Days</strong>: Normal OT calculation applies</p>
                            <p class="mb-0">On <strong>Holidays/Week Offs</strong>: 
                                <span id="preview-comp-off">Work duration ≤ {{ $overtimeRule?->ot_max_co_per_day ?? 8 }} hours → Comp Off, Work duration > {{ $overtimeRule?->ot_max_co_per_day ?? 8 }} hours → Comp Off + OT</span>
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Shift & Break Rules Tab -->
                {{--<div class="tab-pane fade" id="shift-break" role="tabpanel">
                    <div class="row">
                        <div class="settings-section col-md-8">
                            <div class="section-header">
                                <h6><i class="fas fa-exchange-alt me-2"></i>Shift & Break Rules</h6>
                            </div>
                            
                            <div class="setting-card">
                                <div class="mb-3">
                                    <label class="form-label">Shift Type</label>
                                    @foreach($shift_types as $shift_type)
                                        <div class="form-check">
                                            <input 
                                                class="form-check-input" 
                                                type="radio" 
                                                name="shift-type" 
                                                id="shift-type-{{ $shift_type->m_id }}" 
                                                value="{{ $shift_type->m_id }}" 
                                                {{ $shift_type->m_id == $overtimeRule?->ot_shift_type ? 'checked' : '' }} />
                                            <label class="form-check-label" for="shift-type-{{ $shift_type->m_id }}">
                                                {{ $shift_type->m_name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            
                            <div class="setting-card">
                                <div class="mb-3">
                                    <label class="form-label">Break Type</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="break-type" id="paid-break" {{ $overtimeRule?->ot_break_type == 'paid' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="paid-break">
                                            Paid Break (included in work duration)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="break-type" id="unpaid-break" {{ $overtimeRule?->ot_break_type == 'unpaid' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="unpaid-break">
                                            Unpaid Break (deducted from work duration)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                            
                        <div class="preview-box col-md-4">
                            <h5><i class="fas fa-info-circle me-1"></i> Policy Preview</h5>
                            <p class="mb-1">Scheduled Check-in/Check-out times are used as reference for OT calculation</p>
                            <p class="mb-0">Break time is <span id="preview-break-type">included in</span> work duration calculation</p>
                        </div>
                    </div>
                </div>--}}
                
                <!-- Eligibility Rules Tab -->
                <div class="tab-pane fade" id="eligibility" role="tabpanel">
                    <div class="row">
                        <div class="settings-section col-md-8">
                            <div class="row">
                                <div class="section-header">
                                    <h6><i class="fas fa-user-check me-2"></i>Eligibility Rules</h6>
                                </div>
                                <div class="col-md-6">
                                    <div class="setting-card">
                                        <div class="mb-3">
                                            <label for="required-work-hours" class="form-label">Work Hours Required Before OT Starts</label>
                                            <p class="text-muted small mb-2">Minimum work duration required to qualify for overtime</p>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="required-work-hours" value="{{ $overtimeRule?->ot_min_work_per_day ?? 8 }}" min="0" max="24" step="0.5">
                                                <span class="input-group-text">hours</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="setting-card">
                                        <div class="mb-3">
                                            <label for="min-ot-minutes" class="form-label">Minimum OT Minutes</label>
                                            <p class="text-muted small mb-2">OT duration less than this value will be discarded</p>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="min-ot-minutes" value="{{ $overtimeRule?->ot_min_work_required ?? 30 }}" min="0" max="120">
                                                <span class="input-group-text">minutes</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="setting-card">
                                        <div class="mb-3">
                                            <label for="max-ot-daily" class="form-label">Maximum OT Per Day</label>
                                            <p class="text-muted small mb-2">Daily overtime cap</p>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="max-ot-daily" value="{{ $overtimeRule?->ot_max_work_per_day ?? 4 }}" min="0" max="50" step="0.5">
                                                <span class="input-group-text">hours</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="setting-card">
                                        <div class="mb-3">
                                            <label for="max-ot-monthly" class="form-label">Maximum OT Per Month</label>
                                            <p class="text-muted small mb-2">Monthly overtime cap</p>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="max-ot-monthly" value="{{ $overtimeRule?->ot_max_work_per_month ?? 50 }}" min="0" max="500" step="1">
                                                <span class="input-group-text">hours</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="preview-box col-md-4">
                            <h5><i class="fas fa-info-circle me-1"></i> Policy Preview</h5>
                            <p class="mb-1">OT calculation starts only after <span id="preview-work-hours">{{ $overtimeRule?->ot_min_work_per_day ?? 8 }}</span> hours of work</p>
                            <p class="mb-1">OT less than <span id="preview-min-ot">{{ $overtimeRule?->ot_min_work_required ?? 30 }}</span> minutes is discarded</p>
                            <p class="mb-1">Daily OT capped at <span id="preview-max-daily">{{ $overtimeRule?->ot_max_work_per_day ?? 4 }}</span> hours</p>
                            <p class="mb-0">Monthly OT capped at <span id="preview-max-monthly">{{ $overtimeRule?->ot_max_work_per_month ?? 50 }}</span> hours</p>
                        </div>
                    </div>
                </div>
                
                <!-- OT Basis Rules Tab -->
                <div class="tab-pane fade" id="ot-basis" role="tabpanel">
                    <div class="row">
                        <div class="settings-section col-md-8">
                            <div class="section-header">
                                <h6><i class="fas fa-calculator me-2"></i>OT Basis Rules</h6>
                            </div>
                            
                            <div class="setting-card">
                                <div class="mb-3">
                                    <label class="form-label">OT Calculation Method</label>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="ot-basis" value="in_time" id="in-time-only" {{ $overtimeRule?->ot_calculation_method == 'in_time' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="in-time-only">
                                            <strong>Option A: In-time only</strong>
                                        </label>
                                        <p class="text-muted small mb-0 ms-3">OT = time before scheduled check-in</p>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="ot-basis" value="out_time" id="out-time-only" {{ $overtimeRule?->ot_calculation_method == 'out_time' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="out-time-only">
                                            <strong>Option B: Out-time only</strong>
                                        </label>
                                        <p class="text-muted small mb-0 ms-3">OT = time after scheduled check-out + buffer</p>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="ot-basis" value="both_in_out_time" id="both-in-out" {{ $overtimeRule?->ot_calculation_method == 'both_in_out_time' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="both-in-out">
                                            <strong>Option C: Both in & out</strong>
                                        </label>
                                        <p class="text-muted small mb-0 ms-3">Early OT + Late OT = Total OT</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="setting-card">
                                <div class="mb-3">
                                    <label for="buffer-minutes" class="form-label">Buffer Minutes After Shift Out</label>
                                    <p class="text-muted small mb-2">Grace period after scheduled check-out before OT calculation starts</p>
                                    <div class="input-group" style="max-width: 300px;">
                                        <input type="number" class="form-control" id="buffer-minutes" value="{{ $overtimeRule?->ot_buffer_mins_per_day ?? 15 }}" min="0" max="60">
                                        <span class="input-group-text">minutes</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="preview-box col-md-4">
                            <h5><i class="fas fa-info-circle me-1"></i> Policy Preview</h5>
                            <div id="preview-ot-basis">
                                <p class="mb-1">OT is calculated based on <strong>In-time only</strong></p>
                                <p class="mb-0">Buffer period: <span id="preview-buffer">{{ $overtimeRule?->ot_buffer_mins_per_day ?? 15 }}</span> minutes after shift end</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Approval Rules Tab -->
                <div class="tab-pane fade" id="approval" role="tabpanel">
                    <div class="row">
                        <div class="settings-section col-md-8">
                            <div class="section-header">
                                <h6><i class="fas fa-check-circle me-2"></i>Approval Rules</h6>
                            </div>
                            
                            <div class="setting-card">
                                <div class="form-check form-switch d-flex justify-content-between align-items-center">
                                    <div>
                                        <label class="form-check-label" for="auto-approval">
                                            Auto Approval
                                        </label>
                                        <p class="text-muted small mb-0">OT requests are automatically approved without manager review</p>
                                    </div>
                                    <input class="form-check-input" type="checkbox" id="auto-approval">
                                </div>
                            </div>
                            
                            <div class="setting-card" id="approvers-section">
                                <div class="mb-3">
                                    <label for="approvers" class="form-label">Approvers</label>
                                    <p class="text-muted small mb-2">Managers who can approve overtime requests</p>
                                    <select class="form-select" id="approvers" multiple style="height: 150px;">
                                        <option value="1" selected>John Smith (HR Manager)</option>
                                        <option value="2" selected>Sarah Johnson (Department Head)</option>
                                        <option value="3">Michael Brown (Team Lead)</option>
                                        <option value="4">Emily Davis (Operations Manager)</option>
                                        <option value="5">Robert Wilson (Finance Head)</option>
                                    </select>
                                    <div class="form-text">Hold Ctrl/Cmd to select multiple approvers</div>
                                </div>
                            </div>
                        </div>

                        <div class="preview-box col-md-4">
                            <h5><i class="fas fa-info-circle me-1"></i> Policy Preview</h5>
                            <p class="mb-1" id="preview-auto-approval">OT requests require <strong>manager approval</strong></p>
                            <p class="mb-0" id="preview-approvers">Approvers: <span>John Smith, Sarah Johnson</span></p>
                        </div>
                    </div>
                </div>
            </div>
        
            <div class="d-flex justify-content-end mt-4">
			    <button class="btn btn-primary">
			        <i class="fa fa-save me-1"></i> Save All Settings
			    </button>
			</div>
        </form>
    </div>
@endsection

@section('script')
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Update preview when settings change
    document.addEventListener('DOMContentLoaded', function() {

        const isEnabledToggle = document.getElementById('is_enabled');
        const settingsTabContent = document.getElementById('settingsTabContent');

        function toggleAllFields(enabled) {
            const allInputs = settingsTabContent.querySelectorAll('input, select, textarea, button:not(.nav-link)');
            allInputs.forEach(el => {
                if (el.id !== 'is_enabled') {
                    el.disabled = !enabled;
                }
            });

            const allSections = settingsTabContent.querySelectorAll('.settings-section');
            allSections.forEach(section => {
                if (enabled) {
                    section.classList.remove('disabled');
                } else {
                    section.classList.add('disabled');
                }
            });
        }

        toggleAllFields(isEnabledToggle.checked);

        isEnabledToggle.addEventListener('change', function() {
            toggleAllFields(this.checked);
        });

        // Day Type Rules
        const maxCompOff = document.getElementById('max-comp-off');
        maxCompOff.addEventListener('input', function() {
            document.getElementById('preview-comp-off').textContent = 
                `Work duration ≤ ${this.value} hours → Comp Off, Work duration > ${this.value} hours → Comp Off + OT`;
        });
        
        // Shift & Break Rules
        const breakTypeRadios = document.querySelectorAll('input[name="break-type"]');
        breakTypeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if(this.id === 'paid-break') {
                    document.getElementById('preview-break-type').textContent = 'included in';
                } else {
                    document.getElementById('preview-break-type').textContent = 'deducted from';
                }
            });
        });
        
        // Eligibility Rules
        const requiredWorkHours = document.getElementById('required-work-hours');
        const minOtMinutes = document.getElementById('min-ot-minutes');
        const maxOtDaily = document.getElementById('max-ot-daily');
        const maxOtMonthly = document.getElementById('max-ot-monthly');
        
        requiredWorkHours.addEventListener('input', function() {
            document.getElementById('preview-work-hours').textContent = this.value;
        });
        
        minOtMinutes.addEventListener('input', function() {
            document.getElementById('preview-min-ot').textContent = this.value;
        });
        
        maxOtDaily.addEventListener('input', function() {
            document.getElementById('preview-max-daily').textContent = this.value;
        });
        
        maxOtMonthly.addEventListener('input', function() {
            document.getElementById('preview-max-monthly').textContent = this.value;
        });
        
        // OT Basis Rules
        const otBasisRadios = document.querySelectorAll('input[name="ot-basis"]');
        const bufferMinutes = document.getElementById('buffer-minutes');
        
        otBasisRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                let basisText = '';
                if(this.id === 'in-time-only') {
                    basisText = 'In-time only';
                } else if(this.id === 'out-time-only') {
                    basisText = 'Out-time only';
                } else {
                    basisText = 'Both in & out';
                }
                document.querySelector('#preview-ot-basis p:first-child').innerHTML = 
                    `OT is calculated based on <strong>${basisText}</strong>`;
            });
        });
        
        bufferMinutes.addEventListener('input', function() {
            document.getElementById('preview-buffer').textContent = this.value;
        });
        
        // Approval Rules
        const autoApproval = document.getElementById('auto-approval');
        const approversSection = document.getElementById('approvers-section');
        const approversSelect = document.getElementById('approvers');
        
        autoApproval.addEventListener('change', function() {
            if(this.checked) {
                document.getElementById('preview-auto-approval').innerHTML = 
                    'OT requests are <strong>automatically approved</strong>';
                approversSection.style.opacity = '0.5';
                approversSection.style.pointerEvents = 'none';
            } else {
                document.getElementById('preview-auto-approval').innerHTML = 
                    'OT requests require <strong>manager approval</strong>';
                approversSection.style.opacity = '1';
                approversSection.style.pointerEvents = 'auto';
            }
        });
        
        approversSelect.addEventListener('change', function() {
            const selectedOptions = Array.from(this.selectedOptions).map(option => option.text.split(' (')[0]);
            document.querySelector('#preview-approvers span').textContent = selectedOptions.join(', ');
        });
    });
</script>
<script>
    $(document).ready(function () {
        $('#overtime-settings-form').on('submit', function (e) {
            e.preventDefault();

            let formData = {
                _token: '{{ csrf_token() }}',
                is_enabled: $('#is_enabled').is(':checked') ? 1 : 0,
                ot_working_days: $('#ot-working-days').is(':checked') ? 1 : 0,
                ot_holidays: $('#ot-holidays').is(':checked') ? 1 : 0,
                ot_max_co_per_day: $('#max-comp-off').val(),
                shift_type: $('input[name="shift-type"]:checked').val(),
                break_type: $('input[name="break-type"]:checked').attr('id') === 'paid-break' ? 'paid' : 'unpaid',
                required_work_hours: $('#required-work-hours').val(),
                min_ot_minutes: $('#min-ot-minutes').val(),
                max_ot_daily: $('#max-ot-daily').val(),
                max_ot_monthly: $('#max-ot-monthly').val(),
                ot_basis: $('input[name="ot-basis"]:checked').val(),
                buffer_minutes: $('#buffer-minutes').val(),
            };

            $.ajax({
                url: '{{ route("overtime-policy.store") }}',
                method: 'POST',
                data: formData,
                success: function(response) {
                    Swal.fire({
                        icon: response.success ? 'success' : 'warning',
                        title: response.message || 'Operation completed',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                        background: '#fff',
                        customClass: { popup: 'swal2-toast-custom' }
                    });
                    // ✅ Reload the page after short delay
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                },
                error: function(xhr) {
                    let msg = 'Something went wrong!';
                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        msg = Object.values(xhr.responseJSON.errors).map(e => e[0]).join('<br>');
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        html: msg,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 4000,
                        timerProgressBar: true
                    });
                }
            });
        });
    });
</script>
@endsection
