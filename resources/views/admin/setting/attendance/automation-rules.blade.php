@php    
    use Illuminate\Support\Carbon;
@endphp

@extends('admin.layout.master')

@section('title', 'Automation Rules')

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

        .penalty-rule { 
            border: 1px solid #dee2e6; 
            border-radius: 0.375rem; 
            padding: 1rem; 
            margin-bottom: 1rem; 
            background: #f8f9fa; 
        }
        .remove-rule { 
            margin-top: 1.5rem; 
        }
        .form-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .custom-switch-input:checked ~ .custom-switch-unchecked {
            color: #9ba5ca !important;
        }

        /* When checkbox is UNCHECKED → highlight Impose Penalty */
        .custom-switch-input:not(:checked) ~ .custom-switch-unchecked {
            color: #313e6a !important;
        }

        .dark-mode .custom-switch-input:checked ~ .custom-switch-unchecked {
            color: #7a7ea2 !important;
        }
        
        .dark-mode .custom-switch-input:not(:checked) ~ .custom-switch-unchecked {
            color: #c4c9d6 !important;
        }

        .custom-switch-input:not(:checked) ~ .custom-switch-indicator-lg {
            background: #673AB7 !important;
            border-color: #673AB7 !important;
        }

        #pills-tabContent {
            min-height: 55vh
        }
    </style>
@endsection

@section('content')

    {{-- Breadcrumb Start --}}
    <div class="card mt-3 mb-2">
        <div class="card-header d-flex justify-content-between p-4">
            <div>
                <h4 class="text-primary">Automation Rules Settings</h4>
                <ol class="breadcrumb1 breadcrumb1-bg-none m-0 p-0 fs-14">
                    <li class="breadcrumb-item1"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item1"><a href="{{ url('/admin/settings/attendance') }}">Attendance Settings</a>
                    </li>
                    <li class="breadcrumb-item1 active"><span><b>Automation Rules</b></span></li>
                </ol>
            </div>
            <div>
                {{-- you can add any button or any element here --}}
            </div>
        </div>
    </div>
    {{-- Breadcrumb End --}}

    {{-- Settings Tab Start --}}
    {{-- Nav --}}
    <ul class="nav nav-pills my-3" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home" type="button" role="tab" aria-controls="pills-home" aria-selected="true"><span class="fs-15"><i class="fa fa-sign-in"></i> Late Coming Automation Rule</span></button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-profile" type="button" role="tab" aria-controls="pills-profile" aria-selected="false"><span class="fs-15"><i class="fa fa-sign-out"></i> Early Going Automation Rule</span></button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-contact-tab" data-bs-toggle="pill" data-bs-target="#pills-contact" type="button" role="tab" aria-controls="pills-contact" aria-selected="false"><span class="fs-15"><i class="fa fa-exclamation-circle"></i> Missed Punch Automation Rule</span></button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-disabled-tab" data-bs-toggle="pill" data-bs-target="#pills-disabled" type="button" role="tab" aria-controls="pills-disabled" aria-selected="false"><span class="fs-15"><i class="fa fa-id-card-o"></i> Gate Pass Automation Rule</span></button>
        </li>
    </ul>

    {{-- Content --}}
    <div class="tab-content card card-body p-5" id="pills-tabContent">
        {{-- Late Settings --}}
        <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab" tabindex="0">

            <h5 class="pb-4">Deduct salary for n day(s) or impose a penalty.</h5>

            <form action="{{ route('late.early.automation.rule.save') }}" method="post" id="lateComingForm">
                @csrf
                <input type="hidden" name="rule_type" value="414">
                {{-- Toggle --}}
                <div class="row my-3">
                    <div class="col-12 ps-1">
                        <div class="form-group">
                            <label class="custom-switch">
                                <input type="checkbox" name="late_coming_toggle" class="custom-switch-input stc" @if ($lateToggle == 0) checked @endif>
                                <span class="custom-switch-description custom-switch-unchecked me-2">Impose Penalty</span>
                                <span class="custom-switch-indicator custom-switch-indicator-lg"></span>
                                <span class="custom-switch-description me-2">Salary Deduction</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Penalty Section --}}
                <div class="mt-3 penalty-div">
                    <h6>Upon continuous late coming during the month, impose penalty</h6>

                    <table class="table card-table table-vcenter text-nowrap table-primary mb-0">
                        <thead>
                            <tr>
                                <th style="width: 45% !important">Max Late coming limit</th>
                                <th style="width: 45% !important">Penalty to impose</th>
                                <th class="text-center"><button class="btn btn-outline-primary add-row-btn" type="button"><i class="fa fa-plus"></i></button></th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($latePenaltyRule->isEmpty())
                                <!-- <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div>If late till</div> <div><input class="form-control time_format_24hrs" type="text" placeholder="HH:MM" name="late_till[]"></div> <div>then</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div>Amount to deduct is ₹</div> <div><input class="form-control" type="number" min="0" value="100" name="lc_penalty_amount[]"></div>
                                        </div>
                                    </td>
                                        <td class="text-center">
                                        <button class="btn btn-outline-danger remove-row-btn" type="button"><i class="fa fa-trash"></i></button>
                                    </td>
                                </tr> -->
                            @else
                                @foreach($latePenaltyRule as $rule)
                                    @if (count($rule) > 0)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <div>If late till</div> <div><input class="form-control time_format_24hrs" type="text" placeholder="HH:MM" name="late_till[]" value="{{ $rule['lca_late_till']->format('H:i') }}"></div> <div>then</div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <div>Amount to deduct is ₹</div> <div><input class="form-control" type="number" min="0" value="{{ $rule['lca_penalty_amount'] }}" name="lc_penalty_amount[]"></div>
                                                </div>
                                            </td>
                                                <td class="text-center">
                                                <button class="btn btn-outline-danger remove-row-btn" type="button"><i class="fa fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                    <template class="row-template">
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div>If late till</div>
                                    <div><input class="form-control time_format_24hrs" type="text" placeholder="HH:MM" name="late_till[]"></div>
                                    <div>then</div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div>Amount to deduct is ₹</div>
                                    <div><input class="form-control" type="number" min="0" name="lc_penalty_amount[]"></div>
                                </div>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-outline-danger remove-row-btn" type="button"><i class="fa fa-trash"></i></button>
                            </td>
                        </tr>
                    </template>
                </div>

                {{-- Salary Deduction Section --}}
                <div class="mt-3 salary-deduction-div" style="display: none;">
                    <h6>Upon continuous late coming during the month, deduct salary</h6>

                    <table class="table card-table table-vcenter text-nowrap table-primary mb-0">
                        <thead>
                            <tr>
                                <th style="width: 45% !important">On number of late comings during the month</th>
                                <th style="width: 45% !important">No of day(s) of salary to deduct</th>
                                <th class="text-center"><button class="btn btn-outline-primary add-row-btn" type="button"><i class="fa fa-plus"></i></button></th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($lateSalaryRule->isEmpty())
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div>If number of late comings during month is upto</div> <div><input class="form-control" type="number" min="1" step="1" value="1" name="no_late[]"></div> <div>then</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div><input class="form-control" type="number" value="0.10" step="0.01" name="lc_days_to_deduct[]"></div> <div>Day(s) of salary to deduct</div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-outline-danger remove-row-btn" type="button"><i class="fa fa-trash"></i></button>
                                    </td>
                                </tr>
                            @else
                                @foreach ($lateSalaryRule as $rule)
                                    @if (count($rule) > 0)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <div>If number of late comings during month is upto</div> <div><input class="form-control" type="number" min="1" step="1" value="{{ $rule['lca_no_late'] }}" name="no_late[]"></div> <div>then</div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <div><input class="form-control" type="number" value="{{ $rule['lca_days_to_deduct'] }}" step="0.01" name="lc_days_to_deduct[]"></div> <div>Day(s) of salary to deduct</div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-outline-danger remove-row-btn" type="button"><i class="fa fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                    <template class="row-template">
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div>If number of late comings during month is upto</div>
                                    <div><input class="form-control" type="number" min="1" step="1" value="1" name="no_late[]"></div>
                                    <div>then</div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div><input class="form-control" type="number" step="0.01" name="lc_days_to_deduct[]"></div>
                                    <div>Day(s) of salary to deduct</div>
                                </div>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-outline-danger remove-row-btn" type="button"><i class="fa fa-trash"></i></button>
                            </td>
                        </tr>
                    </template>
                </div>

                {{-- Save all rules --}}
                <div class="row">
                    <div class="col-12 text-end mt-4">
                        <button type="submit" class="btn btn-outline-primary" id="saveLateComingRuleBtn">Save Late Coming Rule</button>
                    </div>
                </div>
            </form>

        </div>

        {{-- Early Setting --}}
        <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab" tabindex="1">
            
            <h5 class="pb-4">Deduct salary for n day(s) or impose a penalty.</h5>

            <form action="{{ route('late.early.automation.rule.save') }}" method="post" id="earlyGoingForm">
                @csrf
                <input type="hidden" name="rule_type" value="415">
                {{-- Toggle --}}
                <div class="row my-3">
                    <div class="col-12 ps-1">
                        <div class="form-group">
                            <label class="custom-switch">
                                <input type="checkbox" name="early_going_toggle" class="custom-switch-input stc" @if ($earlyToggle == 0) checked @endif>
                                <span class="custom-switch-description custom-switch-unchecked me-2">Impose Penalty</span>
                                <span class="custom-switch-indicator custom-switch-indicator-lg"></span>
                                <span class="custom-switch-description me-2">Salary Deduction</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Penalty Section --}}
                <div class="penalty-div">
                    <h6>Upon continuous early going during the month, impose penalty</h6>

                    <table class="table card-table table-vcenter text-nowrap table-primary mb-0">
                        <thead>
                            <tr>
                                <th style="width: 45% !important">Minimum early going limit</th>
                                <th style="width: 45% !important">Penalty to impose</th>
                                <th class="text-center"><button class="btn btn-outline-primary add-row-btn" type="button"><i class="fa fa-plus"></i></button></th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($earlyPenaltyRule->isEmpty())
                                <!-- <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div>If early exit before</div> <div><input class="form-control time_format_24hrs" type="text" name="exit_before[]" placeholder="HH:MM"></div> <div>then</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div>Amount to deduct is ₹</div> <div><input class="form-control" type="number" min="0" name="eg_penalty_amount[]" value="100"></div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-outline-danger remove-row-btn" type="button"><i class="fa fa-trash"></i></button>
                                    </td>
                                </tr> -->
                            @else
                                @foreach ($earlyPenaltyRule as $rule)
                                    @if (count($rule) > 0)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <div>If early exit before</div> <div><input class="form-control time_format_24hrs" type="text" name="exit_before[]" placeholder="HH:MM" value="{{ $rule['ega_exit_before']->format('H:i') }}"></div> <div>then</div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <div>Amount to deduct is ₹</div> <div><input class="form-control" type="number" min="0" name="eg_penalty_amount[]" value="{{ $rule['ega_penalty_amount'] }}"></div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-outline-danger remove-row-btn" type="button"><i class="fa fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                    <template class="row-template">
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div>If early exit before</div> <div><input class="form-control time_format_24hrs" type="text" name="exit_before[]" placeholder="HH:MM"></div> <div>then</div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div>Amount to deduct is ₹</div> <div><input class="form-control" type="number" min="0" name="eg_penalty_amount[]"></div>
                                </div>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-outline-danger remove-row-btn" type="button"><i class="fa fa-trash"></i></button>
                            </td>
                        </tr>
                    </template>
                </div>

                {{-- Salary Deduction Section --}}
                <div class="mt-5 salary-deduction-div" style="display: none;">
                    <h6>Upon continuous early going during the month, deduct salary</h6>

                    <table class="table card-table table-vcenter text-nowrap table-primary mb-0">
                        <thead>
                            <tr>
                                <th style="width: 45% !important">On number of early goings during the month</th>
                                <th style="width: 45% !important">No of day(s) of salary to deduct</th>
                                <th class="text-center"><button class="btn btn-outline-primary add-row-btn" type="button"><i class="fa fa-plus"></i></button></th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($earlySalaryRule->isEmpty())
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div>If number of early goings during month is upto</div> <div><input class="form-control" type="number" name="no_early[]" min="1" step="1" value="1"></div> <div>then</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div><input class="form-control" type="number" name="ega_days_to_deduct[]" min="0" step="0.01" value="0.10"></div> <div>Day(s) of salary to deduct</div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-outline-danger remove-row-btn" type="button"><i class="fa fa-trash"></i></button>
                                    </td>
                                </tr>
                            @else
                                @foreach ($earlySalaryRule as $rule)
                                    @if (count($rule) > 0)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <div>If number of early goings during month is upto</div> <div><input class="form-control" type="number" name="no_early[]" min="1" step="1" value="{{ $rule['ega_no_early'] }}"></div> <div>then</div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    <div><input class="form-control" type="number" name="ega_days_to_deduct[]" min="0" step="0.01" value="{{ $rule['ega_days_to_deduct'] }}"></div> <div>Day(s) of salary to deduct</div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-outline-danger remove-row-btn" type="button"><i class="fa fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                    <template class="row-template">
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div>If number of early goings during month is upto</div>
                                    <div><input class="form-control" type="number" name="no_early[]" min="1" step="1" value="1"></div>
                                    <div>then</div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div><input class="form-control" type="number" name="ega_days_to_deduct[]" min="0" step="0.01"></div>
                                    <div>Day(s) of salary to deduct</div>
                                </div>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-outline-danger remove-row-btn" type="button"><i class="fa fa-trash"></i></button>
                            </td>
                        </tr>
                    </template>
                </div>

                {{-- Save all rules --}}
                <div class="row">
                    <div class="col-12 text-end mt-4">
                        <button type="submit" class="btn btn-outline-primary" id="saveEarlyGoingRuleBtn">Save Early Going Rule</button>
                    </div>
                </div>
            </form>

        </div>

        {{-- MSP Setting --}}
        <div class="tab-pane fade" id="pills-contact" role="tabpanel" aria-labelledby="pills-contact-tab" tabindex="2">

            <div class="card-body">

                <div class="row">
                    {{-- Toggle --}}
                    <div class="col-12 ps-1">
                        <div class="form-group">
                            <label class="custom-switch">
                                <input type="checkbox" name="417_toggle" class="custom-switch-input toggle-form" @if ($rules[417]['ar_is_enabled'] ?? '' == 1) checked @endif>
                                <span class="custom-switch-description me-2">Missed Punch Rule</span>
                                <span class="custom-switch-indicator custom-switch-indicator-lg"></span>
                            </label>
                        </div>
                    </div>

                    <form class="disabled row" id="mspForm">

                        <input type="hidden" name="rule_type_id" value="417">

                        <div class="row">
                            <div class=" col-xl-3 col-md-6 mb-3">
                                <label class="form-label">Occurrence Limit <span class="text-danger">*</span></label>
                                <input type="number" class="form-control numericInput" placeholder="Set occurrence limit"
                                    name="max_occurrences" value="{{ $rules[417]['ar_occurrences'] ?? '' }}" disabled required>
                            </div>
                        </div>

                        <div class="row">
                            <div class=" col-xl-3 col-md-6 mb-3">
                                <label class="form-label">Apply Before Day <span class="text-danger">*</span></label>
                                <input type="text" class="form-control numericInput" name="apply_before_day"
                                    value="{{ $rules[417]['ar_apply_before_day'] ?? '' }}" disabled required>
                            </div>
                        </div>

                        <div class="row">
                            <div class=" col-xl-3 col-md-6 mb-3">
                                <label class="form-label">Both Time Count <span class="text-danger">*</span></label>
                                <input type="number" class="form-control numericInput" name="both_time_count" id="both_time_count" 
                                    value="{{ $rules[417]['ar_both_time_count'] ?? '1' }}" min="1" max="2" disabled required>
                            </div>
                        </div>
                    </form>

                    {{-- Save all rules --}}
                    <div class="row">
                        <div class="col-12 mt-4">
                            <button type="button" class="btn btn-outline-primary" id="saveMSPRuleBtn">Save Missed Punch Rule</button>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        {{-- Gate Pass Setting --}}
        <div class="tab-pane fade" id="pills-disabled" role="tabpanel" aria-labelledby="pills-disabled-tab" tabindex="3">
            
            <div class="card-body">

                <div class="row">
                    {{-- Toggle --}}
                    <div class="col-12 ps-1">
                        <div class="form-group">
                            <label class="custom-switch">
                                <input type="checkbox" name="418_toggle" class="custom-switch-input toggle-form Late_Coming_Automation"  @if ($rules[418]['ar_is_enabled'] ?? '' == 1) checked @endif>
                                <span class="custom-switch-description me-2">Gate Pass Rule</span>
                                <span class="custom-switch-indicator custom-switch-indicator-lg"></span>
                            </label>
                        </div>
                    </div>

                    <form class="disabled row" id="gatePassForm">
                        @csrf
                        <input type="hidden" name="rule_type_id" value="418">

                        <div class="row">
                            <div class="col-3">
                                <label class="form-label">
                                    Occurrence Limit <span class="text-danger">*</span>
                                </label>
                                <input type="number"
                                    class="form-control numericInput"
                                    placeholder="Set occurrence limit"
                                    name="max_occurrences"
                                    value="{{ $rules[418]['ar_occurrences'] ?? '' }}"
                                    disabled
                                    required>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-12">
                                <label class="custom-switch">
                                    <input type="checkbox"
                                        name="418_apply_gatepass_checkout"
                                        class="custom-switch-input"
                                        @if (($rules[418]['ar_apply_gatepass_checkout'] ?? 0) == 1) checked @endif>

                                    <span class="custom-switch-description me-2">
                                        Sync Checkout with GatePass
                                    </span>

                                    <span class="custom-switch-indicator custom-switch-indicator-lg"></span>
                                </label>
                            </div>
                        </div>
                    </form>

                    {{-- Save all rules --}}
                    <div class="row">
                        <div class="col-12 mt-4">
                            <button type="button" class="btn btn-outline-primary" id="saveGPRuleBtn">Save Gate Pass Rule</button>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
    {{-- Settings Tab End --}}

@endsection

@section('script')

<script>
    function allowDecimalInput(event) {
        let input = event.target;
        input.value = input.value.replace(/[^0-9.]/g, '');
        if ((input.value.match(/\./g) || []).length > 1) {
            input.value = input.value.replace(/\.+$/, '');
        }
    }

    document.querySelectorAll(".numericInput").forEach(input => {
        input.addEventListener("input", allowDecimalInput);
    });

    // SINGLE $(document).ready() - Yehi ek baar use karein
    $(document).ready(function () {

        // Initial setup for all toggle forms
        $(".toggle-form").each(function () {
            handleToggleState($(this));
        });

        // Event listener for checkbox changes - EK HI EVENT LISTENER
        $(".toggle-form").on("change", function () {
            handleToggleState($(this));
        });

        /**
         * Function to handle toggle state and form enable/disable logic
         */
        function handleToggleState(toggleElement) {
            let isChecked = toggleElement.is(":checked");
            let form = toggleElement.closest(".row").find("form");

            // Form enable/disable
            form.toggleClass("disabled", !isChecked).find("input, select").prop("disabled", !isChecked);
        }

        // Both Time Count validation
        $('#both_time_count').on('keyup change', function () {
            const value = $(this).val().trim();
            if (value !== '1' && value !== '2') {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Value',
                    text: 'Only values 1 or 2 are allowed.',
                });
                $(this).val('1');
            }
        });

        // Toggle between Penalty and Salary Deduction sections (scoped per tab pane)
        // Use fade animation for smoother UX
        $('input.custom-switch-input').on('change input', function () {
            const checked = $(this).is(':checked');
            const $pane = $(this).closest('.tab-pane');
            const $salary = $pane.find('.salary-deduction-div');
            const $penalty = $pane.find('.penalty-div');

            if (checked) {
                $penalty.stop(true, true).fadeOut(180, function() {
                    $salary.stop(true, true).fadeIn(180);
                });
            } else {
                $salary.stop(true, true).fadeOut(180, function() {
                    $penalty.stop(true, true).fadeIn(180);
                });
            }

            // update table background inside this pane
            $pane.find('.table-primary').css('background-color', checked ? '#3366ff1a' : '#673ab71a');
        });

        // Initialize penalty/salary sections based on current checkbox state (per pane)
        $('input.custom-switch-input').each(function() {
            const checked = $(this).is(':checked');
            const $pane = $(this).closest('.tab-pane');
            // Initialize without animation
            $pane.find('.salary-deduction-div').toggle(checked);
            $pane.find('.penalty-div').toggle(!checked);
            $pane.find('.table-primary').css('background-color', checked ? '#3366ff1a' : '#673ab71a');
        });

        // delegated add/remove row handlers
        // When plus button clicked
        $(document).on('click', '.add-row-btn', function(e) {
            e.preventDefault();
            const $btn = $(this);
            // Find the closest table (works if button is in thead as in your markup)
            const $table = $btn.closest('table');
            const $tbody = $table.find('tbody').first();

            // Validation: Check if all inputs in the last row are filled
            const $lastRow = $tbody.find('tr').last();
            const $lastRowInputs = $lastRow.find('input[type="text"], input[type="number"], select');
            let allFilled = true;

            $lastRowInputs.each(function() {
                const value = $(this).val().trim();
                if (!value) {
                    allFilled = false;
                    return false; // Break the loop
                }
            });

            // Show error if last row is not completely filled
            if (!allFilled) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Incomplete Row',
                    text: 'Please fill all fields in the current row before adding a new row.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
                return;
            }

            // Find a <template> for this table. Place it next to the table or inside it.
            // Prefer per-table template: <template class="row-template"> ... </template>
            // If none, you can fallback to cloning the last row.
            const $template = $table.next('template.row-template').length
            ? $table.next('template.row-template')
            : $table.find('template.row-template').first();

            let $newRow;
            if ($template.length) {
                // Use native template cloning
                const html = $template.prop('content') ? $template.prop('content') : $template.html();
                // Convert DocumentFragment to string if needed
                let rowHtml = $template.prop('content') ? new XMLSerializer().serializeToString($template.prop('content')) : $template.html();

                // Simpler: get markup from template.innerHTML via jQuery:
                rowHtml = $template.html();

                // Keep a per-table counter to make unique input names
                const counter = ($table.data('row-index') || 0) + 1;
                $table.data('row-index', counter);

                // Replace placeholders if used
                rowHtml = rowHtml.replace(/__IDX__/g, counter);

                // Create jQuery row
                $newRow = $(rowHtml);
            } else {
                // Fallback: clone last row (preferably a sampleRow with class .sample-row that isn't submitted)
                const $last = $tbody.find('tr').last();
                $newRow = $last.clone(true, true);
                // Clear values
                $newRow.find('input').val('');
            }

            $tbody.append($newRow);
            timeFormat24Hr();

        });

        // Remove row
        $(document).on('click', '.remove-row-btn', function(e) {
            e.preventDefault();
            const $row = $(this).closest('tr');
            $row.remove();
        });
    });

    // Save a single rule (MSP or Gate Pass) instead of all at once
    function saveRuleForm($form, $btn) {
        const ruleId = $form.find("input[name=rule_type_id]").val();
        // Toggles sit outside the form, so locate by the generated name instead of within the form
        const toggle = $(`input[name="${ruleId}_toggle"]`).first();
        const isChecked = toggle.is(":checked");
        let valid = true;
        console.log('toggle:', toggle, 'isChecked:', isChecked, 'form:', $form);

        if (isChecked) {
            // Validate required fields
            $form.find("input:required, select:required").each(function () {
                if (!$(this).val()) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Please fill in all required fields.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                    $(this).focus();
                    valid = false;
                    return false;
                }
            });
            if (!valid) return;

            // Validate min/max constraints
            $form.find("input[type='number']").each(function () {
                const value = $(this).val();
                const min = $(this).attr('min') ?? 1;
                const max = $(this).attr('max') ?? 100;

                if (value !== undefined && value !== '') {
                    const numValue = parseFloat(value);

                    if (min !== undefined && numValue < parseFloat(min)) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Invalid Value',
                            text: `Value must be at least ${min}.`,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });
                        $(this).focus();
                        valid = false;
                        return false;
                    }

                    if (max !== undefined && numValue > parseFloat(max)) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Invalid Value',
                            text: `Value cannot exceed ${max}.`,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });
                        $(this).focus();
                        valid = false;
                        return false;
                    }
                }
            });
            if (!valid) return;
        }

        const payload = [{
            rule_type_id: ruleId,
            is_enabled: isChecked ? 1 : 0,
            max_occurrences: isChecked ? ($form.find("input[name=max_occurrences]").val() || null) : null,
            apply_before_day: isChecked ? ($form.find("input[name=apply_before_day]").val() || null) : null,
            both_time_count: ruleId === "417"
                ? ($form.find("input[name=both_time_count]").val() || 1)
                : null,
        }];

        // If this is Gate Pass rule (418), include the apply_gatepass_checkout flag
        if (ruleId === "418") {
            const gpApply = $(`input[name="${ruleId}_apply_gatepass_checkout"]`).is(":checked") ? 1 : 0;
            payload[0].apply_gatepass_checkout = gpApply;
        }
        console.log(payload);

        $btn.prop("disabled", true).text("Saving...");
        $.ajax({
            url: '{{ route('automation-rules.store') }}',
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                rules: payload
            },
            success: function (response) {
                Swal.fire({
                    icon: response.status ? 'success' : 'warning',
                    title: response.message,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error saving rule.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            },
            complete: function () {
                $btn.prop("disabled", false).text($btn.data("default-text"));
            }
        });
    }

    // Hook MSP form submit
    $("#saveMSPRuleBtn").click(function (e) {
        e.preventDefault();
        const $btn = $(this).data("default-text", "Save Missed Punch Rule");
        const $form = $("#mspForm");
        saveRuleForm($form, $btn);
    });

    // Hook Gate Pass form submit
    $("#saveGPRuleBtn").click(function (e) {
        e.preventDefault();
        const $btn = $(this).data("default-text", "Save Gate Pass Rule");
        const $form = $("#gatePassForm");
        saveRuleForm($form, $btn);
    });

    $("#lateComingForm").submit(function(e) {
        e.preventDefault(); // Prevent default form submission

        let toggle = $("input[name='late_coming_toggle']");
        if (!toggle.is(':checked')) {
            let lateTillArr = $("input[name='late_till[]']");
            let lc_penaltyAmountArr = $("input[name='lc_penalty_amount[]']");
            for (let i = 0; i < lateTillArr.length; i++) {
                if ($(lateTillArr[i]).val().trim() === '' || $(lc_penaltyAmountArr[i]).val().trim() === '') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Please fill all Late Till and Penalty Amount fields.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 5000,
                        timerProgressBar: true
                    });
                    return; // Exit the function if validation fails
                }
            }
        } else {
            let lateNoArr = $("input[name='no_late[]']");
            let lc_daysToDeductArr = $("input[name='lc_days_to_deduct[]']");
            for (let i = 0; i < lateNoArr.length; i++) {
                if ($(lateNoArr[i]).val().trim() === '' || $(lc_daysToDeductArr[i]).val().trim() === '') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Please fill all No of Late and Days to Deduct fields.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 5000,
                        timerProgressBar: true
                    });
                    return; // Exit the function if validation fails
                }
            }
        }

        var form = $(this);
        var url = form.attr('action');
        $("#saveLateComingRuleBtn").prop("disabled", true);
        $("#saveLateComingRuleBtn").text("Saving...");

        $.ajax({
            type: "POST",
            url: url,
            data: form.serialize(), // Serialize form data
            success: function(response) {
                Swal.fire({
                    icon: response.status ? 'success' : 'warning',
                    title: response.message,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'An error occurred while saving the rule.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            }
        });
        
        $("#saveLateComingRuleBtn").prop("disabled", false);
        $("#saveLateComingRuleBtn").text("Save Late Coming Rule");
    });

    $("#earlyGoingForm").submit(function(e) {
        e.preventDefault(); // Prevent default form submission

        let toggle = $("input[name='early_going_toggle']");
        if (!toggle.is(':checked')) {
            let exitBeforeArr = $("input[name='exit_before[]']");
            let egPenaltyAmountArr = $("input[name='eg_penalty_amount[]']");
            for (let i = 0; i < exitBeforeArr.length; i++) {
                if ($(exitBeforeArr[i]).val().trim() === '' || $(egPenaltyAmountArr[i]).val().trim() === '') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Please fill all Exit Before and Penalty Amount fields.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 5000,
                        timerProgressBar: true
                    });
                    return; // Exit the function if validation fails
                }
            }
        } else {
            let earlyNoArr = $("input[name='no_early[]']");
            let egdaysToDeductArr = $("input[name='ega_days_to_deduct[]']");
            for (let i = 0; i < earlyNoArr.length; i++) {
                if ($(earlyNoArr[i]).val().trim() === '' || $(egdaysToDeductArr[i]).val().trim() === '') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Please fill all No of early exits and Days to Deduct fields.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 5000,
                        timerProgressBar: true
                    });
                    return; // Exit the function if validation fails
                }
            }
        }

        var form = $(this);
        var url = form.attr('action');

        $("#saveEarlyGoingRuleBtn").prop("disabled", true);
        $("#saveEarlyGoingRuleBtn").text("Saving...");
        $.ajax({
            type: "POST",
            url: url,
            data: form.serialize(), // Serialize form data
            success: function(response) {
                Swal.fire({
                    icon: response.status ? 'success' : 'warning',
                    title: response.message,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'An error occurred while saving the rule.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            }
        });

        $("#saveEarlyGoingRuleBtn").prop("disabled", false);
        $("#saveEarlyGoingRuleBtn").text("Save Early Going Rule");
    });
</script>

@endsection
