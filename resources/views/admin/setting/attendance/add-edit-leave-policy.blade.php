@extends('admin.layout.master')
@section('title')
    @if (isset($leavePolicy))
        Update Leave Policy
    @else
        Add Leave Policy
    @endif
@endsection

@section('css')
    <style>
        .floating-btn {
            position: absolute;
            top: -15px;
            right: -1115px;
        }

        @media (min-width: 768px) {
            .floating-btn {
                right: -646px;
            }
        }

        @media (min-width: 769px) {
            .floating-btn {
                right: -93%;
            }
        }

        @media (min-width: 1025px) {
            .floating-btn {
                right: -96%;
            }
        }

        @media (min-width: 1441px) {
            .floating-btn {
                right: -97%;
            }
        }

        .card-header {
            margin-left: 14px;
            border-bottom: 1px solid #1b78f1;
        }
    </style>
@endsection

@section('content')
    <div>
        <div class="p-0 pt-md-2">
            <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li><a href="{{ url('admin/settings/attendance/leave-policy') }}">Leave Policy</a></li>
                <li class="active"><span><b>{{ isset($leavePolicy) ? 'Edit Leave Policy' : 'Add Leave Policy' }}</b></span>
                </li>
            </ol>
        </div>
    </div>

    <div class="card shadow mt-5">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Leave Policy Settings</h4>
        </div>
        <div class="card-body">

            <div class="row">
                <div class="col-xl-9 col-lg-8 col-md-12 col-sm-12">

                    <form id="leaveTypePolicyForm" class="form-horizontal">
                        @csrf
                        <div class="my-5">
                            <input type="hidden" name="pl_id" id="pl_id"
                                value="{{ isset($leavePolicy->pl_id) ? $leavePolicy->pl_id : '' }}">

                            <div class="row my-3">
                                {{-- Policy Name --}}
                                <div class="col-xl-6">
                                    <div class="form-group row mb-0 align-items-center">
                                        <label for="pl_name" class="col-md-4 col-form-label mb-0">Leave Policy Name</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" id="pl_name" name="pl_name"
                                                value="{{ isset($leavePolicy->pl_name) ? $leavePolicy->pl_name : '' }}"
                                                placeholder="Leave Policy Name....." required>
                                        </div>
                                    </div>
                                </div>

                                {{-- UPL Check --}}
                                <div class="col-xl-2 col-auto mt-4 mt-xl-0">
                                    <div class="form-group row mb-0 align-items-center">
                                        <label for="pl_upl_applicable" class="col-auto col-form-label mb-0">Is UPL
                                            Applicable</label>
                                        <div class="col-auto pt-2">
                                            <label class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" name="pl_upl_applicable"
                                                    id="pl_upl_applicable"
                                                    {{ isset($leavePolicy->pl_upl_applicable) && $leavePolicy->pl_upl_applicable ? 'checked' : '' }}>
                                                <span class="custom-control-label"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                {{-- Limit Check --}}
                                <div class="col-xl-2 col-auto mt-4 mt-xl-0">
                                    <div class="form-group row mb-0 align-items-center">
                                        <label for="limit_check" class="col-auto col-form-label mb-0">Limit</label>
                                        <div class="col-auto pt-2">
                                            <label class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" name="pl_limit_check"
                                                    id="limit_check" onclick="$('#limit-div').toggleClass('d-none')"
                                                    {{ isset($leavePolicy->pl_limit_check) && $leavePolicy->pl_limit_check ? 'checked' : '' }}>
                                                <span class="custom-control-label"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                {{-- Add Leave Cat Button --}}
                                <div class="col-xl-2 col mt-4 mt-xl-0 d-flex align-items-center justify-content-end">
                                    <button type="button" class="btn btn-outline-primary" id="addLeaveCatBtn"><i
                                            class="fa fa-plus"></i></button>
                                </div>
                            </div>

                            {{-- Limit Days (separate row) --}}
                            <div id="limit-div"
                                class="{{ isset($leavePolicy->pl_limit_check) && $leavePolicy->pl_limit_check ? '' : 'd-none' }} mt-3 form-group row align-items-center">
                                <div class="col-md-6">
                                    <div class="form-group row mb-0 align-items-center">
                                        <label for="pl_limit_after" class="col-md-4 col-form-label mb-0">Post-Applied Leave</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" id="pl_limit_after"
                                                name="pl_limit_after"
                                                value="{{ isset($leavePolicy->pl_limit_after) ? $leavePolicy->pl_limit_after : '' }}"
                                                placeholder="00 Days">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group row mb-0 align-items-center">
                                        <label for="pl_limit_before" class="col-md-4 col-form-label mb-0">Pre-Applied Leave</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control" id="pl_limit_before"
                                                name="pl_limit_before"
                                                value="{{ isset($leavePolicy->pl_limit_before) ? $leavePolicy->pl_limit_before : '' }}"
                                                placeholder="00 Days">
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <h6 class="pb-2">Add Leave Categories</h6>

                        <div id="leaveCategoryRows">

                            @if (isset($leaveTypes) && $leaveTypes->count() > 0)
                                @foreach ($leaveTypes as $index => $type)
                                    <div class="card border">
                                        <div class="card-header row justify-content-end pt-3 me-3">

                                            <input type="hidden" name="lvt_id[]" value="{{ $type->lvt_id }}">

                                            <div class="col-md-auto">
                                                <div class="form-group row mb-0 align-items-center">
                                                    <label class="col-md-8 col-form-label text-end mb-0"
                                                        for="priority_{{ $index }}">Priority</label>
                                                    <div class="col-md-4">
                                                        <input type="number" name="priority[]"
                                                            id="priority_{{ $index }}" class="form-control col-auto"
                                                            min="1" step="1"
                                                            value="{{ $type->lvt_priority }}">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-auto pt-2">
                                                <label class="custom-control custom-checkbox d-inline-block me-3">
                                                    <input type="checkbox" class="custom-control-input" name="sandwich[]"
                                                        id="sandwich_{{ $index }}" value="0"
                                                        {{ $type->lvt_is_sandwich ? 'checked' : '' }}
                                                        onclick="toggleCheckboxHidden(this, 'sandwich', {{ $index }})">
                                                    <input type="hidden" name="hidden_sandwich[]"
                                                        id="hidden_sandwich_{{ $index }}" value="0"
                                                        value="{{ $type->lvt_is_sandwich == 1 ? '1' : 0 }}">
                                                    <span class="custom-control-label pt-1"></span><b>Sandwich Applicable
                                                    </b>
                                                </label>
                                            </div>

                                            <div class="col-md-auto pt-2">
                                                <label class="custom-control custom-checkbox d-inline-block me-3">
                                                    <input class="custom-control-input" type="checkbox" value="1"
                                                        id="lvt_encashable_{{ $index }}" name="lvt_encashable[]"
                                                        {{ isset($type->lvt_encashable) && $type->lvt_encashable ? 'checked' : '' }}
                                                        onclick="toggleCheckboxHidden(this, 'lvt_encashable', {{ $index }})">
                                                    <input type="hidden" name="hidden_lvt_encashable[]"
                                                        id="hidden_lvt_encashable_{{ $index }}"
                                                        value="{{ isset($type->lvt_encashable) && $type->lvt_encashable ? '1' : '0' }}">
                                                    <span class="custom-control-label pt-1"></span><b>Encashable </b>
                                                </label>
                                            </div>

                                            <div class="floating-btn">
                                                <button type="button" class="btn btn-danger removeRow"><i
                                                        class="fa fa-trash removeRow"></i></button>
                                            </div>

                                        </div>

                                        <div class="card-body row leave-category-row mb-3 mt-3">

                                            {{-- Leave Cateogory --}}
                                            <div class="col-xl-2 col-lg-6 col-md-4 col-sm-6">
                                                <label class="form-label" for="category_name_{{ $index }}">Leave
                                                    Category <span class="text-danger">*</span></label>
                                                <select name="category_name[]" id="category_name_{{ $index }}"
                                                    class="form-control custom-select select2 categoryName"
                                                    data-placeholder="Select Category" required
                                                    onchange="toggleEarnedLeaveCheckbox(this, {{ $index }})">
                                                    <option class="text-muted" value="" label="Select Category">
                                                    </option>
                                                    @foreach ($leaveCategory as $item)
                                                        <option value="{{ $item->m_id }}"
                                                            {{ $item->m_id == $type->lvt_cat_type_id ? 'selected' : '' }}>
                                                            {{ $item->m_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            {{-- Leave Cycle --}}
                                            <div class="col-xl-2 col-lg-6 col-md-4 col-sm-6">
                                                <label class="form-label" for="leave_cycle_{{ $index }}">Leave
                                                    Cycle <span class="text-danger">*</span></label>
                                                <select name="leave_cycle[]" id="leave_cycle_{{ $index }}"
                                                    class="form-control select2" data-placeholder="Select Leave Cycle"
                                                    required>
                                                    <option class="text-muted" value="" label="Select Leave Cycle">
                                                    </option>
                                                    @foreach ($leaveCycle as $item)
                                                        <option value="{{ $item->m_id }}"
                                                            {{ $item->m_id == $type->lvt_leave_cycle_id ? 'selected' : '' }}>
                                                            {{ $item->m_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            {{-- Days --}}
                                            <div class="col-xl-2 col-lg-6 col-md-4 col-sm-6">
                                                <label class="form-label" for="days_{{ $index }}">Days <span
                                                        class="text-danger">*</span></label>
                                                <input type="number" name="days[]" id="days_{{ $index }}"
                                                    class="form-control" value="{{ $type->lvt_days_per_year }}"
                                                    min="0.5" step="any" required>
                                            </div>

                                            {{-- Unused Leave Rule --}}
                                            <div class="col-xl-2 col-lg-6 col-md-4 col-sm-6">
                                                <label class="form-label"
                                                    for="unused_leave_rule_{{ $index }}">Unused Leave Rule <span
                                                        class="text-danger">*</span></label>
                                                <select name="unused_leave_rule[]" class="form-control select2"
                                                    data-placeholder="Select Unused Leave Rule" required
                                                    id="unused_leave_rule_{{ $index }}">
                                                    <option class="text-muted" value=""
                                                        label="Select Unused Leave Rule"></option>
                                                    @foreach ($leaveUnused as $item)
                                                        <option value="{{ $item->m_id }}"
                                                            {{ $item->m_id == $type->lvt_unused_leave_rule_id ? 'selected' : '' }}>
                                                            {{ $item->m_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            {{-- Carry Forward Limit --}}
                                            <div class="col-xl-2 col-lg-6 col-md-4 col-sm-6">
                                                <label class="form-label"
                                                    for="carry_forward_limit_{{ $index }}">Carry Forward
                                                    Limit</label>
                                                <input type="number" id="carry_forward_limit_{{ $index }}"
                                                    name="carry_forward_limit[]" class="form-control"
                                                    value="{{ $type->lvt_carry_forward != 0 ? $type->lvt_carry_forward : 0 }}"
                                                    min="0.5" step="any" required>
                                            </div>

                                            {{-- Applicable To --}}
                                            <div class="col-xl-2 col-lg-6 col-md-4 col-sm-6">
                                                <label class="form-label"
                                                    for="applicable_to_{{ $index }}">Applicable To <span
                                                        class="text-danger">*</span></label>
                                                <select name="applicable_to[]" class="form-control select2"
                                                    data-placeholder="Select Applicable To" required
                                                    id="applicable_to_{{ $index }}">
                                                    <option class="text-muted" value=""
                                                        label="Select Applicable To"></option>
                                                    @foreach ($leaveApplicable as $item)
                                                        <option value="{{ $item->m_id }}"
                                                            {{ $item->m_id == $type->lvt_applicable_to_id ? 'selected' : '' }}>
                                                            {{ $item->m_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <!-- Earned Leave Per Period Checkbox -->
                                            <div class="col-xl-2 col-lg-6 col-md-4 col-sm-6 mt-5 earned-leave-checkbox-container"
                                                id="earned-leave-checkbox-container-{{ $index }}"
                                                style="display: {{ $type->lvt_cat_type_id == 209 || $leaveCategory->firstWhere('m_name', 'Earned Leave (EL)')->m_id == $type->lvt_cat_type_id ? 'block' : 'none' }};">
                                                <label class="form-label" for="leave_per_period">Earned Leave Per
                                                    Period</label>
                                                <input type="number" name="leave_per_period[]"
                                                    id="el_per_period_{{ $index }}"
                                                    value="{{ isset($type->lvt_el_per_period) ? $type->lvt_el_per_period : 0 }}"
                                                    class="form-control" min="0.5" step="any">
                                            </div>

                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="card border">
                                    <div class="card-header row justify-content-end pt-3 me-3">

                                        <input type="hidden" hidden value="" name="lvt_id[]">

                                        <div class="col-md-auto">
                                            <div class="form-group row mb-0 align-items-center">
                                                <label class="col-md-8 col-form-label text-end mb-0"
                                                    for="priority_1">Priority</label>
                                                <div class="col-md-4">
                                                    <input type="number" name="priority[]" id="priority_1"
                                                        class="form-control col-auto" min="1" step="1"
                                                        value="1">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-auto pt-2">
                                            <label class="custom-control custom-checkbox d-inline-block me-3">
                                                <input type="checkbox" class="custom-control-input" name="sandwich[]"
                                                    id="sandwich_1" value="0"
                                                    onclick="toggleCheckboxHidden(this, 'sandwich', 1)">
                                                <input type="hidden" name="hidden_sandwich[]" id="hidden_sandwich_1"
                                                    value="0">
                                                <span class="custom-control-label pt-1"></span><b>Sandwich Applicable </b>
                                            </label>
                                        </div>

                                        <div class="col-md-auto pt-2">
                                            <label class="custom-control custom-checkbox d-inline-block me-3">
                                                <input class="custom-control-input" type="checkbox" value="1"
                                                    id="lvt_encashable_1" name="lvt_encashable[]"
                                                    onclick="toggleCheckboxHidden(this, 'lvt_encashable', 1)">
                                                <input type="hidden" name="hidden_lvt_encashable[]"
                                                    id="hidden_lvt_encashable_1" value="0">
                                                <span class="custom-control-label pt-1"></span><b>Encashable </b>
                                            </label>
                                        </div>

                                    </div>

                                    <div class="card-body row leave-category-row mb-3 mt-3">

                                        <input type="hidden" hidden value="" name="lvt_id">

                                        <div class="col-xl-2 col-lg-6 col-md-4 col-sm-6">
                                            <label class="form-label" for="category_name">Leave Category <span
                                                    class="text-danger">*</span></label>
                                            <select name="category_name[]" id="category_name_1"
                                                class="form-control custom-select select2 categoryName"
                                                data-placeholder="Select Category" required
                                                onchange="toggleEarnedLeaveCheckbox(this, 1)">
                                                <option class="text-muted" value="" label="Select Category">
                                                </option>
                                                @foreach ($leaveCategory as $item)
                                                    <option value="{{ $item->m_id }}">{{ $item->m_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-xl-2 col-lg-6 col-md-4 col-sm-6">
                                            <label class="form-label" for="leave_cycle">Leave Cycle <span
                                                    class="text-danger">*</span></label>
                                            <select name="leave_cycle[]" id="leave_cycle_1" class="form-control select2"
                                                data-placeholder="Select Leave Cycle" required>
                                                <option class="text-muted" value="" label="Select Leave Cycle">
                                                </option>
                                                @foreach ($leaveCycle as $item)
                                                    <option value="{{ $item->m_id }}">{{ $item->m_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-xl-2 col-lg-6 col-md-4 col-sm-6">
                                            <label class="form-label" for="days">Days <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" name="days[]" id="days_1" class="form-control"
                                                value="0" min="0.5" step="any" required>
                                        </div>

                                        <div class="col-xl-2 col-lg-6 col-md-4 col-sm-6">
                                            <label class="form-label" for="unused_leave_rule">Unused Leave Rule <span
                                                    class="text-danger">*</span></label>
                                            <select name="unused_leave_rule[]" class="form-control select2"
                                                data-placeholder="Select Unused Leave Rule" required>
                                                <option class="text-muted" value=""
                                                    label="Select Unused Leave Rule"></option>
                                                @foreach ($leaveUnused as $item)
                                                    <option value="{{ $item->m_id }}">{{ $item->m_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-xl-2 col-lg-6 col-md-4 col-sm-6">
                                            <label class="form-label" for="carry_forward_limit">Carry Forward
                                                Limit</label>
                                            <input type="number" name="carry_forward_limit[]" class="form-control"
                                                value="0" min="0.5" step="any" required>
                                        </div>

                                        <div class="col-xl-2 col-lg-6 col-md-4 col-sm-6">
                                            <label class="form-label" for="applicable_to">Applicable To <span
                                                    class="text-danger">*</span></label>
                                            <select name="applicable_to[]" class="form-control select2"
                                                data-placeholder="Select Applicable To" required>
                                                <option class="text-muted" value="" label="Select Applicable To">
                                                </option>
                                                @foreach ($leaveApplicable as $item)
                                                    <option value="{{ $item->m_id }}">{{ $item->m_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Earned Leave Per Period Checkbox -->
                                        <div class="col-xl-2 col-lg-6 col-md-4 col-sm-6 mt-5 earned-leave-checkbox-container"
                                            id="earned-leave-checkbox-container-1" style="display : none">
                                            <label class="form-label" for="leave_per_period">Earned Leave Per
                                                Period</label>
                                            <input type="number" name="leave_per_period[]" id="el_per_period_1"
                                                class="form-control" value="0" min="0.5" step="0.5">
                                        </div>

                                    </div>
                                </div>
                            @endif
                        </div>

                        <input type="hidden" name="deletedLeaveTypes" id="deletedLeaveTypes" value="">

                        <div class="mt-4 text-end">
                            <a href="{{ url('admin/settings/attendance/leave-policy') }}" role="button"
                                class="btn btn-outline-danger me-2 cancel">Cancel</a>
                            <button type="submit" id="saveBtn" class="btn btn-outline-primary">Save</button>
                        </div>
                    </form>

                </div>

                <div class="col-xl-3 col-lg-4 col-md-12 col-sm-12 mt-md-5">
                    <div class="description-div ps-5 pb-5">
                        <h5 class="pt-4">Description -</h5>

                        <p class="text-muted">Use this panel to create or update a leave policy. A leave policy groups one
                            or more leave categories together and defines how each category behaves for employees covered by
                            this policy.</p>

                        <ul class="ms-3 list-style-circle">
                            <li><strong>Leave Policy Name:</strong> A human-friendly name to identify this policy.</li>
                            <li><strong>Is UPL Applicable:</strong> Toggle if Unpaid Leave (UPL) rules should be applied
                                under this policy.</li>
                            <li><strong>Limit:</strong> When enabled, you can restrict when employees may apply for leave
                                using "Apply Leave Before/After" values (in days).</li>
                            <li><strong>Leave Categories:</strong> Add one or more categories (e.g., Sick, Casual, Earned).
                                For each category set:</li>
                            <ul class="ms-3 list-style-circle">
                                <li><strong>Priority:</strong> Integer sequence used to order categories (must be a
                                    contiguous sequence starting at 1; no duplicates).</li>
                                <li><strong>Leave Cycle:</strong> The cycle used to reset or calculate the entitlement
                                    (yearly, monthly, etc.).</li>
                                <li><strong>Days:</strong> Days allocated per cycle.</li>
                                <li><strong>Unused Leave Rule & Carry Forward:</strong> Controls how unused days are handled
                                    and whether carry forward limits apply.</li>
                                <li><strong>Applicable To:</strong> Which employee groups this category applies to.</li>
                                <li><strong>Sandwich Applicable:</strong> When checked, sandwich rule will count intervening
                                    working days as leave.</li>
                                <li><strong>Encashable:</strong> When checked, unused leave can be encashed according to
                                    company rules.</li>
                            </ul>
                            <li><strong>Notes:</strong> Priorities must be unique and form a contiguous sequence (1..N). If
                                you select "Earned Leave (EL)" the Earned Leave Per Period input will appear for that row.
                            </li>
                        </ul>

                        <p class="text-muted mb-0">
                            After making changes click Save. If you remove an existing leave category row, it will be marked for deletion and removed when the policy is saved.
                        </p>

                        <p class="text-muted fw-bold mb-0">
                            Any changes made in the policy will take effect from the upcoming month
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('script')
    <script>
        function toggleEarnedLeaveCheckbox(selectElement, index) {
            let selectedValue = selectElement.value;
            let earnedLeaveCheckboxContainer = document.getElementById(`earned-leave-checkbox-container-${index}`);
            let el_input = document.getElementById(`el_per_period_${index}`);
            // Check if the selected value is 209 or if the selected category is "Earned Leave (EL)"
            if (selectedValue == 209 || selectElement.options[selectElement.selectedIndex].text === 'Earned Leave (EL)') {
                earnedLeaveCheckboxContainer.style.display = 'block';
                // el_input.disabled = false;
            } else {
                earnedLeaveCheckboxContainer.style.display = 'none';
                el_input.value = '';
                // el_input.disabled = true;
            }
            console.log('Earned Leave');
        }

        var number = {{ isset($leaveTypes) ? $leaveTypes->count() + 1 : 2 }};

        $(document).ready(function() {
            // Trigger change event on page load for pre-filled values
            $('.select2[name="unused_leave_rule[]"]').each(function() {
                $(this).trigger('change');
            });
            $('.select2[name="category_name[]"]').each(function() {
                $(this).trigger('change');
            });
            // $('input[name="leave_per_period[]"]').each(function() {
            //     if ($(this).closest('.earned-leave-checkbox-container').css('display') === 'none') {
            //         $(this).prop('disabled', true);
            //     }
            // });
        });

        $(document).on('change', '.select2[name="unused_leave_rule[]"]', function() {
            var selectedValue = $(this).val();
            var carryForwardLimitInput = $(this).closest('.row').find('input[name="carry_forward_limit[]"]');

            if (selectedValue == 222) {
                carryForwardLimitInput.prop('readonly', true).val(0) // Optionally reset to 0 when readonly
                    .css({
                        'background-color': '#eee',
                        'pointer-events': 'none'
                    });
            } else if (selectedValue == 221) {
                carryForwardLimitInput.prop('readonly', false)
                    .css({
                        'background-color': '',
                        'pointer-events': ''
                    });
            }
        });

        // Add new row
        document.getElementById('addLeaveCatBtn').addEventListener('click', function() {

            let allFilled = true;
            const requiredFields = document.querySelectorAll('#leaveCategoryRows .form-control[required]');

            requiredFields.forEach(function(field) {
                if (field.value.trim() === "") {
                    allFilled = false;
                    return;
                }
            });

            if (!allFilled) {
                alert("Please fill in all required fields before adding a new row.");
                return;
            }

            let leaveCategoryOptions = `
                @foreach ($leaveCategory as $item)
                    <option value="{{ $item->m_id }}">{{ $item->m_name }}</option>
                @endforeach
            `;

            // compute next priority: max existing priority values + 1 (or 1 if none)
            let existingPriorities = Array.from(document.querySelectorAll('input[name="priority[]"]')).map(i =>
                parseInt(i.value) || 0);
            let nextPriority = 1;
            if (existingPriorities.length > 0) {
                nextPriority = Math.max(...existingPriorities) + 1;
            }

            let html = `
                <div class="card border">
                    <div class="card-header row justify-content-end pt-3 me-3">

                        <input type="hidden" hidden value="" name="lvt_id[]">

                        <div class="col-md-auto">
                            <div class="form-group row mb-0 align-items-center">
                                <label class="col-md-8 col-form-label text-end mb-0" for="priority_${number}">Priority</label>
                                <div class="col-md-4">
                                    <input type="number" name="priority[]" id="priority_${number}" class="form-control col-auto" min="1" step="1" value="${nextPriority}">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-auto pt-2">
                            <label class="custom-control custom-checkbox d-inline-block me-3">
                                <input type="checkbox" class="custom-control-input" name="sandwich[]" id="sandwich_${number}" value="0" onclick="toggleCheckboxHidden(this, 'sandwich', ${number})">
                                <input type="hidden" name="hidden_sandwich[]" id="hidden_sandwich_${number}" value="0">
                                <span class="custom-control-label pt-1"></span><b>Sandwich Applicable </b>
                            </label>
                        </div>

                        <div class="col-md-auto pt-2">
                            <label class="custom-control custom-checkbox d-inline-block me-3">
                                <input class="custom-control-input" type="checkbox" value="1" id="lvt_encashable_${number}" name="lvt_encashable[]" onclick="toggleCheckboxHidden(this, 'lvt_encashable', ${number})">
                                <input type="hidden" name="hidden_lvt_encashable[]" id="hidden_lvt_encashable_${number}" value="0">
                                <span class="custom-control-label pt-1"></span><b>Encashable </b>
                            </label>
                        </div>

                        <div class="floating-btn">
                            <button type="button" class="btn btn-danger removeRow"><i class="fa fa-trash removeRow"></i></button>
                        </div>

                    </div>
                    
                    <div class="card-body row leave-category-row mb-3 mt-3">

                        <div class="col-xl-2 col-lg-6 col-md-4 col-sm-6">
                            <label class="form-label" for="category_name_${number}">Leave Category <span class="text-danger">*</span></label>
                            <select name="category_name[]" id="category_name_${number}" class="form-control custom-select select2 categoryName" data-placeholder="Select Category" required onchange="toggleEarnedLeaveCheckbox(this, ${number}); checkForDuplicatesAndAlert();">
                                <option class="text-muted" value="" label="Select Category"></option>
                                @foreach ($leaveCategory as $item)
                                    <option value="{{ $item->m_id }}">{{ $item->m_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-xl-2 col-lg-6 col-md-4 col-sm-6">
                            <label class="form-label" for="leave_cycle_${number}">Leave Cycle <span class="text-danger">*</span></label>
                            <select name="leave_cycle[]" id="leave_cycle_${number}" class="form-control select2" data-placeholder="Select Leave Cycle" required>
                                <option class="text-muted" value="" label="Select Leave Cycle"></option>
                                @foreach ($leaveCycle as $item)
                                    <option value="{{ $item->m_id }}">{{ $item->m_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-xl-2 col-lg-6 col-md-4 col-sm-6">
                            <label class="form-label" for="days_${number}">Days <span class="text-danger">*</span></label>
                            <input type="number" name="days[]" id="days_${number}" class="form-control" value="0" min="0.5" step="any" required>
                        </div>

                        <div class="col-xl-2 col-lg-6 col-md-4 col-sm-6">
                            <label class="form-label" for="unused_leave_rule_${number}">Unused Leave Rule <span class="text-danger">*</span></label>
                            <select name="unused_leave_rule[]" id="unused_leave_rule_${number}" class="form-control select2" data-placeholder="Select Unused Leave Rule" required>
                                <option class="text-muted" value="" label="Select Unused Leave Rule"></option>
                                @foreach ($leaveUnused as $item)
                                    <option value="{{ $item->m_id }}">{{ $item->m_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-xl-2 col-lg-6 col-md-4 col-sm-6">
                            <label class="form-label" for="carry_forward_limit_${number}">Carry Forward Limit</label>
                            <input type="number" name="carry_forward_limit[]" class="form-control" value="0" min="0.5" step="any" required>
                        </div>

                        <div class="col-xl-2 col-lg-6 col-md-4 col-sm-6">
                            <label class="form-label" for="applicable_to">Applicable To <span class="text-danger">*</span></label>
                            <select name="applicable_to[]" id="carry_forward_limit_${number}" class="form-control select2" data-placeholder="Select Applicable To" required>
                                <option class="text-muted" value="" label="Select Applicable To"></option>
                                @foreach ($leaveApplicable as $item)
                                    <option value="{{ $item->m_id }}">{{ $item->m_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-xl-2 col-lg-6 col-md-4 col-sm-6 mt-5 earned-leave-checkbox-container" id="earned-leave-checkbox-container-${number}" style="display : none">
                            <label class="form-label" for="leave_per_period">Earned Leave Per Period</label>
                            <input type="number" name="leave_per_period[]" id="el_per_period_${number}" class="form-control" value="0" min="0.5" step="0.5">
                        </div>

                    </div>
                </div>
            `;

            $('#leaveCategoryRows').append(html);
            number++;
            $('.select2').select2();
        });

        function checkForDuplicatesAndAlert() {
            var count = $('.leave-category-row').length;
            var duplicateIndices = [];
            var isDuplicate = false;
            var combinationSet = new Set();

            // Iterate through each row with the .leave-category-row class
            $('.leave-category-row').each(function(index) {
                var elem = $(this).find('.categoryName');
                if (elem.length > 0) { // Check if the element exists
                    var cat = elem.val();
                    console.log(cat);
                    var combination = `${cat}`;

                    if (combinationSet.has(combination)) {
                        isDuplicate = true;
                        duplicateIndices.push(index); // Use index since class-based elements are not unique
                    }
                    combinationSet.add(combination);
                } else {
                    console.log(`Element with class categoryName not found in row ${index + 1}`);
                }
            });

            if (isDuplicate) {
                Swal.fire({
                    icon: 'warning',
                    text: 'Duplicate selection of Category Name on policy found.',
                    timer: 3000,
                });

                duplicateIndices.forEach(function(index) {
                    // Reset the duplicate .categoryName field
                    $('.leave-category-row').eq(index).find('.categoryName').val('').trigger('change');
                });
                return false;
            }
            $('.select2').select2();
            return true;
        }

        // Remove row
        document.getElementById('leaveCategoryRows').addEventListener('click', function(e) {

            if (e.target.classList.contains('removeRow')) {
                const row = e.target.closest('.card');
                const leaveTypeId = row.querySelector('input[name="lvt_id[]"]').value; // Get the leave type ID

                if (leaveTypeId) {
                    // Add the leave type ID to a hidden input or an array to track deletions
                    let deletedLeaveTypes = document.getElementById('deletedLeaveTypes');
                    if (!deletedLeaveTypes.value) {
                        deletedLeaveTypes.value = leaveTypeId;
                    } else {
                        deletedLeaveTypes.value += ',' + leaveTypeId; // Append to the existing IDs
                    }
                }

                if (document.querySelectorAll('.leave-category-row').length > 1) {
                    row.remove();
                } else {
                    alert('You need at least one row.');
                }
            }
        });


        // Create or Update Leave Type
        function submitAjax() {
            $.ajax({
                url: "{{ url('admin/settings/attendance/leave-policy') }}",
                method: "POST",
                data: $('#leaveTypePolicyForm').serialize(),
                beforeSend: function() {
                    $('#saveBtn').attr('disabled', true);
                },
                success: function(response) {
                    if (response.status == true) {
                        $('#leaveTypeModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            text: response.message,
                            timer: 3000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href =
                                "{{ url('admin/settings/attendance/leave-policy') }}";
                        });
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            text: response.message,
                            timer: 3000,
                        });
                    }
                    $('#saveBtn').attr('disabled', false);
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error: ', error);
                    let message = 'Something went wrong!';
                    if (xhr && xhr.responseJSON && xhr.responseJSON.message) message = xhr.responseJSON.message;
                    Swal.fire({
                        icon: 'error',
                        text: message,
                        timer: 3000,
                    });
                    $('#saveBtn').attr('disabled', false);
                }
            });
        }

        $('#leaveTypePolicyForm').on('submit', function(e) {
            e.preventDefault();

            // Validate priorities before submit — do NOT auto-normalize. Ask user to fix if invalid.
            const priorityInputs = Array.from(document.querySelectorAll('input[name="priority[]"]'));
            const n = priorityInputs.length;
            let priorities = priorityInputs.map(i => parseInt(i.value) || 0);

            // If all priority inputs are empty/zero, skip the priority validation entirely
            const allEmptyPriorities = priorities.every(v => v === 0);
            if (allEmptyPriorities) {
                submitAjax();
                return;
            }

            // Clear previous highlights
            priorityInputs.forEach(i => i.classList.remove('border', 'border-danger'));

            // compute counts
            const counts = {};
            priorities.forEach((v, idx) => {
                if (!counts[v]) counts[v] = [];
                counts[v].push(idx);
            });

            const duplicates = Object.keys(counts).filter(k => k > 0 && counts[k].length > 1).map(k => counts[k])
                .flat();

            const expected = Array.from({
                length: n
            }, (_, i) => i + 1);
            const missing = expected.filter(x => !priorities.includes(x));

            const zeros = priorities.map((v, idx) => v <= 0 ? idx : -1).filter(i => i >= 0);

            if (duplicates.length > 0 || missing.length > 0 || zeros.length > 0) {
                // highlight problematic inputs
                duplicates.forEach(i => priorityInputs[i].classList.add('border', 'border-danger'));
                zeros.forEach(i => priorityInputs[i].classList.add('border', 'border-danger'));

                // If missing numbers, highlight none specifically but provide guidance
                let msgParts = [];
                if (duplicates.length > 0) msgParts.push('Duplicate priority values found.');
                if (zeros.length > 0) msgParts.push('Priority values must be positive integers starting from 1.');
                if (missing.length > 0) msgParts.push(
                    'Priority sequence has gaps. Expected a contiguous sequence 1..' + n + '. Missing: ' +
                    missing.join(', '));

                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid priorities',
                    html: msgParts.join('<br>') +
                        '<br><br>Please adjust the Priority fields and submit again.',
                    showConfirmButton: true,
                });
                return; // do not submit
            }

            // All good
            submitAjax();
        });

        // Remove highlights on change so users see updates
        $(document).on('input', 'input[name="priority[]"]', function() {
            $(this).removeClass('border border-danger');
        });

        /**
         * Generic helper to toggle a paired hidden input when a checkbox is clicked.
         * Usage examples:
         *  - inline onclick on generated rows: onclick="toggleCheckboxHidden(this, 'sandwich', 3)"
         *  - programmatic: toggleCheckboxHidden($('#sandwich_2'), 'sandwich', 2)
         *
         * @param {HTMLElement|jQuery|string|number} checkboxEl - The checkbox element or selector or index
         * @param {string} baseName - The base name used for the checkbox/hidden pair (e.g. 'sandwich')
         * @param {number} index - The numeric suffix for the elements (required when passing `this` from inline onclick)
         */
        function toggleCheckboxHidden(checkboxEl, baseName, index) {
            // Normalize to jQuery object
            const $cb = checkboxEl && checkboxEl.jquery ? checkboxEl : $(checkboxEl);

            // If the caller passed only an index and baseName, try to resolve element
            let $checkbox = $cb;
            if (!$checkbox || $checkbox.length === 0) {
                // try selecting by constructed id
                $checkbox = $(`#${baseName}_` + index);
            }

            const hiddenId = `#hidden_${baseName}_` + index;

            if ($checkbox.length && $checkbox.is(':checked')) {
                $(hiddenId).val('1');
            } else {
                // Only set the hidden input to '0' when it exists
                if ($(hiddenId).length) {
                    $(hiddenId).val('0');
                }
            }
        }
    </script>
@endsection
