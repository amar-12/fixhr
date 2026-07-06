@extends('admin.layout.master')

@section('title', 'Approval Settings')

@section('content')
    <style>
        .disabled-tab {
            pointer-events: none;
            color: grey;
        }
    </style>
    <input type="hidden" id="ajaxUrl" value="{{ url('/') }}">
    <input type="hidden" name="approvalModuleId" class="form-control" id="approvalModuleId"
        value="{{ isset($moduleData) ? $moduleData->am_id : null }}">
    <input type="hidden" name="ruleCriteriaId" class="form-control" id="ruleCriteriaId">
    <!-- PAGE HEADER -->
    <div class="p-0 mb-4">
        <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
            <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li><a href="{{ url('/admin/settings/tada-settings/approval-list') }}">Privilege</a></li>
            <li class="active"><span><b>Approval Settings</b></span></li>
        </ol>
    </div>
    <div class="page-header d-xl-flex d-block">
        <div class="page-leftheader">
            <div class="page-title">   {{ $moduleList[$moduleData->am_module_id ?? $moduleId] }} Approval Settings</div>
        </div>
    </div>
    <!-- ROW -->
    <div class="row">
        <div class="col-md-12 col-xl-3">
            <div class="card">
                <div class="nav flex-column admisetting-tabs" id="settings-tab" role="tablist" aria-orientation="vertical">
                    <a class="nav-link active disabled-tab" data-bs-toggle="pill" href="#module-setting" role="tab">
                        <i class="nav-icon feather fe fe-settings"></i> Module Settings
                    </a>
                    <a class="nav-link disabled-tab" data-bs-toggle="pill" href="#rule-criteria" role="tab">
                        <i class="nav-icon feather fe fe-aperture"></i> Rule Criteria
                    </a>
                    <a class="nav-link disabled-tab" data-bs-toggle="pill" href="#approver-setting" role="tab">
                        <i class="nav-icon feather fe fe-user"></i> Who Should Approve
                    </a>
                    <a class="nav-link disabled-tab" data-bs-toggle="pill" href="#action-upon-reject" role="tab">
                        <i class="nav-icon feather feather-x"></i> Action Upon Reject
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-12 col-xl-9">
            <div class="tab-content adminsetting-content" id="setting-tabContent">
                <div class="tab-pane fade show active" id="module-setting" role="tabpanel">
                    <div class="card">
                        <form id="moduleSettingForm">
                            <div class="card-header  border-0 px-4">
                                <h4 class="card-title">Module Settings</h4>
                                <input type="hidden" name="am_id" id="am_id"
                                    value="{{ isset($moduleData) ? $moduleData->am_id : null }}">
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label mb-0 mt-2">Module <span
                                                    class="text-red">*</span></label>
                                        </div>
                                        <div class="col-md-9">
                                            {{-- @dd($moduleId); --}}
                                            <select name="module" class="form-select-md search_test custom-heighlight" id="module"
                                                data-placeholder="Select Module" required disabled>
                                                <option value="">Select Module</option>
                                                @foreach ($moduleList as $key => $val)
                                                    <option value="{{ $key }}"
                                                    {{ isset($moduleData) && $moduleData->am_module_id  == $key ? 'selected' : '' }}
                                                    {{ isset($moduleId) && $moduleId == $key ? 'selected' : '' }}>
                                                        {{ $val }}
                                                    </option>
                                                @endforeach
                                            </select>

                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label mb-0 mt-2">Name <span class="text-red">*</span></label>
                                        </div>
                                        <div class="col-md-9">
                                            <input type="text" name="module_name" class="form-control" id="module_name"
                                                maxlength="100" placeholder="Name"
                                                value="{{ isset($moduleData) ? $moduleData->am_name : '' }}">

                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label mb-0 mt-2">Description</label>
                                        </div>
                                        <div class="col-md-9">
                                            <textarea rows="2" name="module_description" class="form-control" id="module_description" maxlength="255">{{ isset($moduleData) ? $moduleData->am_description : '' }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label for="exp_rej_day" class="form-label mb-0 mt-2">Auto reject within days</label>
                                        </div>
                                        <div class="col-md-9">
                                            <input
                                                type="text"
                                                class="form-control numericInput"
                                                id="exp_rej_day" name="moduleData[exp_rej_day]" value="{{ isset($moduleData) ? $moduleData->am_exp_rej_day : '' }}"
                                                placeholder="5">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label for="noti_before_days" class="form-label mb-0 mt-2">Notification before days</label>
                                        </div>
                                        <div class="col-md-9">
                                            <input
                                                type="text"
                                                class="form-control numericInput"
                                                id="noti_before_days" name="moduleData[noti_before_days]" value="{{ isset($moduleData) ? $moduleData->am_noti_before_days : '' }}"
                                                placeholder="4">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <label class="form-label mb-0 mt-2">When to execute <span
                                                    class="text-red">*<span></label>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="custom-controls-stacked d-md-flex">
                                                @foreach ($exeOn as $key => $val)
                                                    <label class="custom-control ">
                                                        <input type="checkbox"
                                                            {{ isset($moduleData) ? (in_array($key, json_decode($moduleData->am_exe_on, true)) ? 'checked' : '') : '' }}
                                                            name="execution_on[]" value="{{ $key }}">
                                                        <span>{{ $val }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer d-flex justify-content-end">
                                <a href="javascript:void(0);" class="btn btn-outline-primary" id="moduleSaveUptBtn">Next</a>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- START THIS IS MY SECTION  --}}
                <div class="tab-pane fade" id="rule-criteria" role="tabpanel">
                    <div class="card">
                        <div class="card-header d-flex">
                            <div>
                                <h4 class="card-title"><span>Rule Criteria</span></h4>
                            </div>
                            {{-- This add rule button is commented for now
                            <div class="ms-auto">
                                <button type="button" name="addRule" id="addRule" class="btn btn-info btn-sm"><i
                                        class="fe fe-plus bold"></i></button>
                            </div> --}}
                        </div>
                        <form id="ruleCriteriaNewForm" action="{{ route('admin.save.approval.setting') }}"
                            method="POST">
                            <div class="card-body pt-0 pb-2 px-3 form-group">
                                @csrf
                                <div class="table-responsive">
                                    <table class="table" id="dynamicTable">
                                        <thead>
                                            <tr>
                                                <th>Apply On: <span class="text-red">*<span></th>
                                                <th>Condition: <span class="text-red">*<span></th>
                                                <th>Rule Value: <span class="text-red">*<span></th>
                                                <th class="text-end " hidden>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($ruleCriteriaData as $index => $row)
                                                <tr data-row-id="{{ $index }}">
                                                    <td>
                                                        <select name="dynamic[{{ $index }}][rc_approval_rule_id]"
                                                            data-row-id="{{ $index }}"
                                                            style="width: 100%; height: 40px; font-size: 16px;"
                                                            class="form-control form-select rule-apply-on">
                                                            <option value="" label="Select Rule"></option>
                                                            @foreach ($rules as $key => $val)
                                                                <option value="{{ $key }}"
                                                                    {{ $key == $row->rc_approval_rule_id ? 'selected' : '' }}>
                                                                    {{ $val }}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="invalid-feedback">This field is required.</div>
                                                    </td>
                                                    <td>
                                                        <select name="dynamic[{{ $index }}][rc_rule_condition_id]"
                                                            data-row-id="{{ $index }}"
                                                            class="form-control form-select rule-condition">
                                                            <option class="text-muted" value=""
                                                                label="Select Condition"></option>
                                                            @foreach ($ruleConditions as $key => $val)
                                                                @php
                                                                    $descriptions = [];
                                                                    if (!is_null($val) && !empty($val->m_description)) {
                                                                        $descriptions = json_decode(
                                                                            $val->m_description,
                                                                            true,
                                                                        );
                                                                        if (json_last_error() !== JSON_ERROR_NONE) {
                                                                            $descriptions = [];
                                                                        }
                                                                    }
                                                                @endphp
                                                                @if (!is_null($val) && in_array($row->rc_approval_rule_id, $descriptions))
                                                                    <option value="{{ $val->m_id }}"
                                                                        data-type="{{ $val->m_type }}"
                                                                        {{ $val->m_id == $row->rc_rule_condition_id ? 'selected' : '' }}>
                                                                        {{ $val->m_name }}
                                                                    </option>
                                                                @endif
                                                            @endforeach

                                                        </select>
                                                        <div class="invalid-feedback">This field is required.</div>
                                                    </td>
                                                    <td class="dynamic-element-cell">
                                                        {{-- @if ($row->rc_rule_condition_id && $row->rc_condition_option_id) --}}
                                                        @php
                                                            $selectedCondition = $ruleConditions->firstWhere(
                                                                'm_id',
                                                                $row->rc_rule_condition_id,
                                                            );
                                                            $descriptions = [];

                                                            if ($selectedCondition) {
                                                                $descriptions = json_decode(
                                                                    $selectedCondition->m_description,
                                                                    true,
                                                                );
                                                                if (json_last_error() !== JSON_ERROR_NONE) {
                                                                    $descriptions = [];
                                                                }
                                                            }
                                                        @endphp
                                                        @if (json_last_error() === JSON_ERROR_NONE && in_array($row->rc_approval_rule_id, $descriptions))
                                                            @if ($selectedCondition->m_type === 'input')
                                                                <input type="number" min="0"
                                                                    name="dynamic[{{ $index }}][rule_value]"
                                                                    class="form-control"
                                                                    value="{{ $row->rc_custom_value }}" />
                                                                <input type="hidden"
                                                                    name="dynamic[{{ $index }}][rule_value_type]"
                                                                    value="custom" class="rule_value_type">
                                                            @elseif ($selectedCondition->m_type === 'select')
                                                                <select name="dynamic[{{ $index }}][rule_value]"
                                                                    class="form-control form-select">
                                                                    <option value="">Select Value</option>
                                                                    @foreach ($ruleValueConditionOption as $item)
                                                                        @php
                                                                            $descriptions2 = json_decode(
                                                                                $item->m_description,
                                                                                true,
                                                                            );
                                                                            if (json_last_error() !== JSON_ERROR_NONE) {
                                                                                $descriptions2 = []; // Initialize $descriptions2 as an empty array if decoding fails
                                                                            }
                                                                        @endphp
                                                                        @if (is_array($descriptions2) && in_array($row->rc_rule_condition_id, $descriptions2))
                                                                            <option value="{{ $item->m_id }}"
                                                                                {{ $item->m_id == $row->rc_condition_option_id ? 'selected' : '' }}>
                                                                                {{ $item->m_name }}
                                                                            </option>
                                                                        @endif
                                                                    @endforeach
                                                                </select>
                                                                <input type="hidden"
                                                                    name="dynamic[{{ $index }}][rule_value_type]"
                                                                    value="master" class="rule_value_type">
                                                            @endif
                                                        @endif
                                                        {{-- @endif --}}
                                                        <div class="invalid-feedback">This field is required.</div>
                                                    </td>
                                                    <input type="hidden" name="dynamic[{{ $index }}][rc_id]"
                                                        value="{{ $row->rc_id }}" />
                                                    <input type="hidden" name="dynamic[{{ $index }}][_delete]"
                                                        value="0" class="delete-marker" />
                                                    <input type="hidden" name="dynamic[{{ $index }}][_index]"
                                                        value="{{ $index }}" />
                                                    @if ($index > 1)
                                                        <td class="text-end">
                                                            <button type="button" class="btn btn-outline-danger  remove-tr btn-sm"
                                                                data-row-id="{{ $index }}"
                                                                data-row-pid="{{ $row->rc_id }}"><i
                                                                    class="feather feather-trash"></i></button>
                                                        </td>
                                                    @endif
                                                    <div class="duplicated-error"></div>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <div class="px-2 text-danger" id="duplicated_id"></div>
                                </div>
                            </div>
                            <div class="card-footer d-flex justify-content-between">
                                <a href="javascript:void(0);" class="btn btn-outline-danger"
                                    onclick="backButton('#rule-criteria', '#module-setting', 1)"
                                    class="backButton">Back</a>
                                <button id="saveAndUpdateRuleCriteriaButton" type="submit"
                                    class="btn btn-outline-primary">Next</button>
                            </div>
                        </form>
                    </div>
                </div>
                {{-- END MY SECTION   --}}

                <div class="tab-pane fade" id="approver-setting" role="tabpanel">
                    <div class="card">
                        <div class="card-header  border-0">
                            <h4 class="card-title">Who Should Approve</h4>
                        </div>
                        <form id="approvalForm">
                            <div class="card-body">
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-md-2 col-lg-2 col-sm-4 col-xs-4">
                                            <label class="form-label">Approval Flow <span class="text-danger">*</span></label>
                                        </div>

                                        <div class="col-md-10 d-flex">
                                            <div class="form-check form-check-inline">
                                                <label class="custom-control custom-radio">
                                                    <input class="form-check-input" type="radio" name="level" id="business"
                                                        value="business" onchange="toggleApprovalForm()">
                                                    <label class="form-check-label" for="business">
                                                        Business
                                                    </label>
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="level" id="department"
                                                    value="department" onchange="toggleApprovalForm()">
                                                <label class="form-check-label" for="department">
                                                    Department
                                                </label>
                                            </div>
                                        </div>

                                        <!-- Container where content will be dynamically added -->
                                        <div id="approval-content" class="mt-4"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer d-flex justify-content-between">
                                <a href="javascript:void(0);" class="btn btn-outline-danger"
                                    onclick="backButton('#approver-setting','#rule-criteria',2)" class="backButton">Back</a>
                                <a href="javascript:void(0);" class="btn btn-outline-primary" id="saveUptApproverBtn">Next</a>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="tab-pane fade" id="action-upon-reject" role="tabpanel">
                    {{-- <form id="actioUponRejectionFrm"> --}}
                    <div class="card">
                        <div class="card-header  border-0">
                            <h4 class="card-title">Action Upon Reject</h4>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-3">
                                        <label class="form-label">Select Notify <span
                                                class="text-red">*</span></label></label>
                                    </div>
                                    <div class="col-md-9">
                                        <select name="approval_notify" id="approval_notify" multiple
                                            class="form-select-md search_test custom-heighlight"
                                            style="width: 100%; height: 40px; font-size: 16px;"
                                            data-placeholder="Select Notify" multiple>
                                            @foreach ($approvalNotify as $key => $value)
                                                <option value="{{ $key }}"
                                                    {{ is_array($aurUserIds) && in_array($key, $aurUserIds) ? 'selected' : '' }}>
                                                    {{ $value }}</option>
                                                {{-- <option value="{{ $key }}" {{ in_array($key, $moduleData->action_upon_rejection->aur_group_ids) ? 'selected' : '' }}>{{ $value }}</option> --}}
                                                {{-- <option value="{{ $key }}"  {{isset($moduleData->action_upon_rejection) ? json_decode0($moduleData->action_upon_rejection->aur_group_ids) }} >{{ $value }}</option> --}}
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-between">
                            <a href="javascript:void(0);" class="btn btn-outline-danger"
                                onclick="backButton('#action-upon-reject', '#approver-setting', 3)"
                                class="backButton">Back</a>
                            <button href="javascript:void(0);" class="btn btn-outline-primary"
                                id="saveUptActionUponRejection">Finish</button>
                        </div>
                    </div>
                    {{-- </form> --}}
                </div>
            </div>
        </div>
    </div>
    <!-- END ROW -->
@endsection
@section('script')

    <script>

        const expRejInput = document.getElementById('exp_rej_day');
        const notificationInput = document.getElementById('noti_before_days');

        // exp_rej_day validation
        expRejInput.addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value !== '') {
                let num = parseInt(this.value);
                // max 180
                if (num > 180) {
                    this.value = 180;
                }
            }
            validateNotificationDays();
        });

        // noti_before_days validation
        notificationInput.addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '');
            validateNotificationDays();
        });

        // Validate notification days
        function validateNotificationDays() {
            let expDays = parseInt(expRejInput.value) || 0;
            let notificationDays = parseInt(notificationInput.value) || 0;

            // noti_before_days should be smaller than exp_rej_day
            if (notificationDays >= expDays && expDays > 0) {
                notificationInput.value = expDays - 1;
            }
        }

        // Min validation on blur for exp_rej_day
        expRejInput.addEventListener('blur', function () {
            let value = this.value.trim();
            if (value === '') return;
            let num = parseInt(value);

            // minimum 2
            if (num < 2) {
                this.value = 2;
            }
            validateNotificationDays();
        });

        // Min validation on blur for noti_before_days
        notificationInput.addEventListener('blur', function () {
            let value = this.value.trim();
            if (value === '') return;
            let num = parseInt(value);

            // minimum 1
            if (num < 1) {
                this.value = 1;
            }
            validateNotificationDays();
        });


        $('.search_test').SumoSelect({
    search: true,
    searchText: 'Enter here.'
  }); //transfer

        let savedData = {
            business: {
                layout: '',
                values: {},
                radioSelections: {},
                rowdata: {}
            },
            department: {
                layout: '',
                values: {},
                radioSelections: {},
                rowdata: {}
            }
        }

        const departmentObj = @json($departments);
        const departments = Object.values(departmentObj)
        let approverMessagesData = @json($approverMessages);
        let roles = @json($roles);
        let uniqueRowCounter = 0; // To keep track of unique row IDs globally
        // Define the savedStates object to store layout states
        const savedStates = {};
        let deletedApproverIds = [];
        let selectedDepartments = new Set(); // To track selected departments

        const processApproverOptimizedData = @json($processApproverOptimizedData);

        // var approvalRoles = @json($moduleData ? $moduleData->fh_process_approvers : []);
        var approverMessages = @json($approverMessages);
        window.ruleCriteriaCount = {{ count($ruleCriteriaData) }};
        window.rulesData = @json($ruleCriteriaData);
        window.rulesDataCount = {{ count($ruleCriteriaData) }};

        window.rulesOptions = `{!! collect($rules)->map(fn($val, $key) => "<option value=\"{$key}\">{$val}</option>")->implode('') !!}`;
        window.approvalSettingUrl = '{{ route('admin.get.approval.setting') }}';

        $('.disabled-tab').click(function(event) {
            event.preventDefault();
            event.stopPropagation();
            alert("This tab is disabled.");
        });
    </script>
    <script src="{{ asset('assets/js/approval-settings/approval-settings.js') }}"></script>
@endsection
{{-- saveApprovalSetting --}}
