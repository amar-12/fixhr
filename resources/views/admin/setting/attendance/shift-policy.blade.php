@extends('admin.layout.master')
@section('title', 'Shift Policy')

@section('content')
    <div class="p-0 mt-3">
        <div class="row">
            <div class="col-md-4">
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                    <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ url('/admin/settings/attendance') }}">Attendance Settings</a></li>
                    <li class="active"><span><b>Shift Policy</b></span></li>
                </ol>
            </div>
            <div class="col-md-6"></div>
            <div class="col-md-2">
                <div class="page-rightheader ms-md-auto">
                    <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                        <div class="d-lg-flex d-block ms-auto">
                            <div class="btn-list">
                                <x-button type="button" class="btn btn-outline-primary create-button" data-bs-toggle="modal"
                                data-bs-target="#createShiftModal" data-title="Create Shift">
                                Create Shift
                            </x-button>                          </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-modal id="createShiftModal" title="Create Shift" formId="createShiftForm" action="{{ route('shift-policy.store') }}"
        method="POST" enctype="multipart/form-data" size="modal-xl" submitButtonText="Save Shift"
        submitButtonId="saveShiftButton">

        {{-- <!-- Form fields --> --}}
        <!-- Break Is Select -->
        <x-select id="shiftTypeSelect" name="shiftTypeSelect" class="sumo_search" label="Shift Type" :options="$shiftType ?? []"
            required />

        <!-- Fixed Shift Form -->
        <div id="fixedShiftForm" class="shift-form d-none">
            <h5>Fixed Shift Details</h5>
            <div class="row">
                <input type="hidden" id="fixedShiftId" name="fixedShiftId">

                <x-input type="text" id="fixedShiftName" label="Shift Name" name="fixedShiftName"
                    placeholder="Enter Shift Name" astric="*" />

                <div class="row">
                    <div class="form-group col-xl">
                        <x-input type="time" id="fixedStartTime" label="Start Time" name="fixedStartTime"
                            placeholder="Enter Start Time" astric="*" />

                    </div>
                    <div class="form-group col-xl">
                        <x-input type="time" id="fixedEndTime" label="End Time" name="fixedEndTime"
                            placeholder="Enter End Time" astric="*" />

                    </div>
                    <div class="form-group col-xl">
                        <x-input type="number" id="fixedBreakMin" label="Break Minutes" name="fixedBreakMin" min="0"
                            placeholder="Enter Break Minutes" astric="*" />

                    </div>


                    <div class="form-group col-xl">
                        <x-select id="fixedBreakIs" name="fixedBreakIs" class="sumo_search" label="Break Is"
                            :options="$breakType ?? []" required />

                    </div>
                    <div class="form-group col-xl">
                        <x-input type="number" id="fixedPunchBegin" label="Punch Begin Before (minutes)"
                            name="fixedPunchBegin" min="0" placeholder="Enter Punch Begin Before (minutes)" />
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-xl">
                        <x-input type="number" id="fixedPunchEnd" label="Punch End After (minutes)" name="fixedPunchEnd"
                            min="0" placeholder="Enter Punch End After (minutes)" />
                    </div>
                    <div class="form-group col-xl">
                        <x-input type="number" id="fixedGraceTime" label="Grace Time (minutes)" name="fixedGraceTime"
                            min="0" placeholder="Enter Grace Time (minutes)" />
                    </div>
                    <div class="form-group col-xl">
                        <x-select id="fixedPartialDayOn" name="fixedPartialDayOn" class="sumo_search" label="Partial Day On"
                            :options="$partialDayOn ?? []" />
                    </div>
                    <div class="form-group col-xl">
                        <x-input type="time" id="fixedBeginsAt" label="Begins At" name="fixedBeginsAt" min="0"
                            placeholder="Enter Begins At" />
                    </div>
                    <div class="form-group col-xl">
                        <x-input type="time" id="fixedEndsAt" label="Ends At" name="fixedEndsAt" min="0"
                            placeholder="Enter Ends At" />
                    </div>
                </div>

            </div>
        </div>


        <!-- Rotational Shift Form -->
        <div id="rotationalShiftForm" class="shift-form d-none">
            <h5>Rotational Shift Details</h5>
            <div class="form-group">
                <x-input type="text" id="rotationalShiftName" label="Rotational Shift Name"
                    name="rotationalShiftName" placeholder="Enter Rotational Shift Name" astric="*" />
            </div>
            <input type="hidden" id="deletedItems" name="deletedItems[]">
            <div id="rotationalShiftContainer">
                <!-- Dynamic fields will be appended here -->
            </div>
            <button type="button" class="btn btn-outline-primary mt-3" onclick="addRotationalShift()">Add New Shift</button>
        </div>


        <!-- Open Shift Placeholder -->
        <div id="openShiftForm" class="shift-form d-none">
            <h5>Open Shift Details</h5>
            <p>No additional information required for Open Shift.</p>
            <div id="openShiftGroup" class="border p-3 mb-3 position-relative">
                <input type="hidden" name="openShiftId" id="openShiftId">
                <div class="form-group">
                    <x-input type="text" id="openShiftName" label="Shift Name" name="openShiftName"
                        placeholder="Enter Shift Name" astric="*" />
                </div>
                <div class="row">
                    <div class="form-group col-xl">
                        <x-input type="number" id="openHours" label="Hours" name="openHours" min="0"
                            placeholder="Enter Hours" astric="*" />
                    </div>
                    <div class="form-group col-xl">
                        <x-input type="number" id="openMinutes" label="Minutes" name="openMinutes" min="0"
                            placeholder="Enter Minutes" astric="*" />
                    </div>
                    <div class="form-group col-xl">
                        <x-input type="number" id="openBreakMin" label="Break (Minutes)" name="openBreakMin"
                            min="0" placeholder="Enter Break (Minutes)" astric="*" />
                    </div>
                    <div class="form-group col-xl">
                        <x-select id="openBreakIs" name="openBreakIs" class="sumo_search" label="Break Is"
                            :options="$breakType ?? []" required />
                    </div>
                    <div class="form-group col-xl">
                        <x-input type="number" id="openPunchBeginBefore" label="Punch Begin Before (minutes)"
                            name="openPunchBeginBefore" min="0"
                            placeholder="Enter Punch Begin Before (minutes)" />
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-xl">
                        <x-input type="number" id="openPunchBeginAfter" label="Punch Begin After (minutes)"
                            name="openPunchBeginAfter" min="0" placeholder="Enter Punch Begin After (minutes)" />
                    </div>
                    <div class="form-group col-xl">
                        <x-input type="number" id="openGraceTime" label="Grace Time (minutes)" name="openGraceTime"
                            min="0" placeholder="Enter Grace Time (minutes)" />
                    </div>
                    <div class="form-group col-xl">
                        <x-select id="openPartialDayOn" name="openPartialDayOn" class="sumo_search"
                            label="Partial Day On" :options="$partialDayOn ?? []" />
                    </div>
                    <div class="form-group col-xl">
                        <x-input type="time" id="openBeginsAt" label="Begins At" name="openBeginsAt"
                            placeholder="Enter Begins At" />
                    </div>
                    <div class="form-group col-xl">
                        <x-input type="time" id="openEndsAt" label="Ends At" name="openEndsAt"
                            placeholder="Enter Ends At" />
                    </div>
                </div>
            </div>
        </div>
    </x-modal>


    <div class="row mt-5">
        <div class="col-xl-12 col-md-12 col-lg-12">
            <div class="card">
                <div class="card-header border-0 px-4">
                    <h4 class="card-title">Shift Policy List</h4>
                </div>
                <div class="card-body">
                    @csrf
                    <div class="row">
                        <div class="col-md-1 col-sm-4">
                            <div class="form-group">
                                <p class="form-label">Show entries</p>
                                <select id="customLengthMenu" class="form-select-md p-2 sumo_search" data-length
                                    style="width: 100px">
                                    <option value="5" style="width: 100px">5</option>
                                    <option value="10" style="width: 100px">10</option>
                                    <option value="25" style="width: 100px">25</option>
                                    <option value="50" style="width: 100px">50</option>
                                    <option value="100" style="width: 100px">100</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-1 col-sm-4 pt-5 mt-1" align="right">
                            <div class="btn-group">
                                <button class="btn btn-outline-danger dropdown-toggle" type="button" id="defaultDropdown"
                                    data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false">
                                    Export As
                                </button>
                                <ul class="dropdown-menu dropdown-menu-export" aria-labelledby="defaultDropdown">
                                    <li><a class="dropdown-item" href="#" data-export="csv">CSV</a></li>
                                    <li><a class="dropdown-item" href="#" data-export="excel">Excel</a></li>
                                    <li><a class="dropdown-item" href="#" data-export="pdf">PDF</a></li>
                                    <li><a class="dropdown-item" href="#" data-export="copy">Copy</a></li>
                                    <li><a class="dropdown-item" href="#" data-export="print">Print</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-8 col-sm-4"></div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <p class="form-label">Search</p>
                                <div class="form-group mb-3">
                                    <input type="text" id="searchFilter" placeholder="Search" class="form-control"
                                        data-search />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table display table-hover table-vcenter text-wrap border-bottom" id="shift-policy-table">
                            <thead>
                                <tr>
                                    @foreach ($columns as $column)
                                        <th style="font-size: 13px">{{ $column }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <div class="row mt-5">
                        <div class="col-sm-6">
                            <div id="custom-show-entries" data-show-entries></div>
                        </div>
                        <div class="col-sm-6 d-flex justify-content-end">
                            <ul data-pagination class="custom-pagination"></ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('assets/js/ajax-handler.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            datatable({
                tableId: "shift-policy-table",
                url: "{{ route('attendance-shift-type.index') }}",
                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataDateFilter: '[data-date-filter]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]'
            });
        });

        // Delegate the input event from a static parent (like the document or a container)
        $(document).on('input', 'input[name="rotationalItemShiftName[]"]', function() {
            // Find the nearest .text-danger span element inside the same container
            var errorSpan = $(this).closest('div').find('.text-danger');

            // Check if the input value is not empty
            if ($(this).val() !== '') {
                // Hide the error message and clear its text
                errorSpan.text('').hide();
            } else {
                // Show the error message and set its text
                // errorSpan.text('The shift name is required').show();
            }
        });

        $(document).on('input', 'input[name="rotationalItemStartTime[]"]', function() {
            // Find the nearest .text-danger span element inside the same container
            var errorSpan = $(this).closest('div').find('.text-danger');

            // Check if the input value is not empty
            if ($(this).val() !== '') {
                // Hide the error message and clear its text
                errorSpan.text('').hide();
            } else {
                // Show the error message and set its text
                // errorSpan.text('The shift name is required').show();
            }
        });

        $(document).on('input', 'input[name="rotationalItemEndTime[]"]', function() {
            // Find the nearest .text-danger span element inside the same container
            var errorSpan = $(this).closest('div').find('.text-danger');

            // Check if the input value is not empty
            if ($(this).val() !== '') {
                // Hide the error message and clear its text
                errorSpan.text('').hide();
            } else {
                // Show the error message and set its text
                // errorSpan.text('The shift name is required').show();
            }
        });


        $(document).on('input', 'input[name="rotationalItemBreakMin[]"]', function() {

            // Find the nearest .text-danger span element inside the same container
            var errorSpan = $(this).closest('div').find('.text-danger');

            // Check if the input value is not empty
            if ($(this).val() !== '') {
                // Hide the error message and clear its text
                errorSpan.text('').hide();
            } else {
                // Show the error message and set its text
                // errorSpan.text('The shift name is required').show();
            }
        });

        $(document).on('input', 'input[name="rotationalItemBreakIs[]"]', function() {
            // Find the nearest .text-danger span element inside the same container
            var errorSpan = $(this).closest('div').find('.text-danger');

            // Check if the input value is not empty
            if ($(this).val() !== '') {
                // Hide the error message and clear its text
                errorSpan.text('').hide();
            } else {
                // Show the error message and set its text
                // errorSpan.text('The shift name is required').show();
            }
        });

        // JavaScript to handle the form toggle
        const shiftTypeSelect = document.getElementById("shiftTypeSelect");
        const fixedShiftForm = document.getElementById("fixedShiftForm");
        const rotationalShiftForm = document.getElementById("rotationalShiftForm");
        const openShiftForm = document.getElementById("openShiftForm");

        shiftTypeSelect.addEventListener("change", (event) => {
            // Hide all forms
            fixedShiftForm.classList.add("d-none");
            rotationalShiftForm.classList.add("d-none");
            openShiftForm.classList.add("d-none");

            // Show selected form
            const selectedValue = event.target.value;
            if (selectedValue == 244) {
                fixedShiftForm.classList.remove("d-none");
            } else if (selectedValue == 245) {
                rotationalShiftForm.classList.remove("d-none");
                addRotationalShift();
            } else if (selectedValue == 246) {
                openShiftForm.classList.remove("d-none");
            }
        });

        // Add Rotational Field Logic
        function addRotationalShift(value = null) {
            const rotationalShiftContainer = document.getElementById("rotationalShiftContainer");
            // Create a unique ID for the new shift group
            const uniqueId = Date.now();
            const rowIndex = rotationalShiftContainer.querySelectorAll('.border').length;
            const newShift = `
                <div id="shiftGroup-${uniqueId}" class="border p-3 mb-3 position-relative">
                    <input type="hidden" id="rotationalItemShiftId-${uniqueId}" value="${value?value.aspi_id:''}" name="rotationalItemShiftId[]"/>
                    <button type="button" class="btn btn-outline-danger  btn-sm position-absolute" style="top: 10px; right: 10px;" onclick="removeRotationalShift(${uniqueId})">Remove</button>

                    <div class="form-group">
                        <label for="rotationalItemShiftName-${uniqueId}">Shift Name <span style="color:red">*</span></label>
                        <input type="text" class="form-control" id="rotationalItemShiftName-${uniqueId}" name="rotationalItemShiftName[]" placeholder="Enter Shift Name" value="${value?value.aspi_shift_name:''}" />
                         <span id="rotationalItemShiftName_${rowIndex}_error" class="text-danger"></span>
                    </div>
                    <div class="row">
                    <div class="form-group col-xl">
                        <label for="rotationalItemStartTime-${uniqueId}">Start Time <span style="color:red">*</span></label>
                        <input type="time" class="form-control" id="rotationalItemStartTime-${uniqueId}" name="rotationalItemStartTime[]" value="${value?value.aspi_shift_start:''}" />
                         <span id="rotationalItemStartTime_${rowIndex}_error" class="text-danger"></span>
                    </div>
                    <div class="form-group col-xl">
                        <label for="rotationalItemEndTime-${uniqueId}">End Time <span style="color:red">*</span></label>
                        <input type="time" class="form-control" id="rotationalItemEndTime-${uniqueId}" name="rotationalItemEndTime[]" value="${value?value.aspi_shift_end:''}" />
                         <span id="rotationalItemEndTime_${rowIndex}_error" class="text-danger"></span>
                    </div>
                    <div class="form-group col-xl">
                        <label for="rotationalItemBreakMin-${uniqueId}">Break (Minutes) <span style="color:red">*</span></label>
                        <input type="number" class="form-control" id="rotationalItemBreakMin-${uniqueId}" name="rotationalItemBreakMin[]" value="${value?value.aspi_break_minute:''}" />
                         <span id="rotationalItemBreakMin_${rowIndex}_error" class="text-danger"></span>
                    </div>
                    <div class="form-group col-xl">
                        <label for="rotationalItemBreakIs-${uniqueId}">Break Is <span style="color:red">*</span></label>
                        <select class="form-control sumo_search" id="rotationalItemBreakIs-${uniqueId}" name="rotationalItemBreakIs[]">
                            <option value="" selected disabled>Select Break Type</option>
                            @foreach ($breakType as $key => $item)
                                <option value="{{ $key }}">{{ $item }}</option>
                            @endforeach
                        </select>
                         <span id="rotationalItemBreakIs_${rowIndex}_error" class="text-danger"></span>
                    </div>
                    <div class="form-group col-xl">
                        <label for="rotationalItemPunchBeginBefore-${uniqueId}">Punch Begin Before (minutes)</label>
                        <input type="number" class="form-control" id="rotationalItemPunchBeginBefore-${uniqueId}" name="rotationalItemPunchBeginBefore[]" value="${value?value.aspi_punch_begin_before:''}" />
                         <span id="rotationalItemPunchBeginBefore_${rowIndex}_error" class="text-danger"></span>
                    </div>
                    </div>
                    <div class="row">
                    <div class="form-group col-xl">
                        <label for="rotationalItemPunchBeginAfter-${uniqueId}">Punch Begin After (minutes)</label>
                        <input type="number" class="form-control" id="rotationalItemPunchBeginAfter-${uniqueId}" name="rotationalItemPunchBeginAfter[]" value="${value?value.aspi_punch_end_after:''}" />
                         <span id="rotationalItemPunchBeginAfter_${rowIndex}_error" class="text-danger"></span>
                    </div>
                    <div class="form-group col-xl">
                        <label for="rotationalItemGraceTime-${uniqueId}">Grace Time (minutes)</label>
                        <input type="number" class="form-control" id="rotationalItemGraceTime-${uniqueId}" name="rotationalItemGraceTime[]" value="${value?value.aspi_grace_time:''}" />
                         <span id="rotationalItemGraceTime_${rowIndex}_error" class="text-danger"></span>
                    </div>
                    <div class="form-group col-xl">
                        <label for="rotationalItemPartialDayOn-${uniqueId}">Partial Day On</label>
                        <select class="form-control sumo_search" id="rotationalItemPartialDayOn-${uniqueId}" name="rotationalItemPartialDayOn[]">
                            <option value="" selected disabled>Select Partial Day</option>
                            @foreach ($partialDayOn as $key => $item)
                                    <option value="{{ $key }}">{{ $item }}</option>
                            @endforeach
                        </select>
                         <span id="rotationalItemPartialDayOn_${rowIndex}_error" class="text-danger"></span>
                    </div>
                    <div class="form-group col-xl">
                        <label for="rotationalItemBeginsAt-${uniqueId}">Begins At</label>
                        <input type="time" class="form-control" id="rotationalItemBeginsAt-${uniqueId}" name="rotationalItemBeginsAt[]" value="${value?value.aspi_begins_at:''}" />
                         <span id="rotationalItemBeginsAt_${rowIndex}_error" class="text-danger"></span>
                    </div>
                    <div class="form-group col-xl">
                        <label for="rotationalItemEndsAt-${uniqueId}">Ends At</label>
                        <input type="time" class="form-control" id="rotationalItemEndsAt-${uniqueId}" name="rotationalItemEndsAt[]" value="${value?value.aspi_end_at:''}" />
                         <span id="rotationalItemEndsAt_${rowIndex}_error" class="text-danger"></span>
                    </div>
                    </div>
                </div>
            `;

            rotationalShiftContainer.insertAdjacentHTML("beforeend", newShift);

            $('.sumo_search').SumoSelect({
                search: true,
                searchText: 'Enter here.'
            }); // Initialize SumoSelect

            if (value) {
                var temp = `rotationalItemBreakIs-${uniqueId}`;
                var field = $(`#${temp}`); // Use jQuery to get the element

                if (typeof value.aspi_break_type === 'string') {
                    value.aspi_break_type = JSON.parse(value
                        .aspi_break_type); // Convert string to array if it's a JSON string
                }

                // Ensure it's an array
                if (!Array.isArray(value.aspi_break_type)) {
                    value.aspi_break_type = value.aspi_break_type ? [value.aspi_break_type] :
                []; // If it's not an array, convert it to one
                }

                // Iterate over the value.aspi_break_type and select them in SumoSelect
                $.each(value.aspi_break_type, function(index, value1) {
                    field[0].sumo.selectItem(String(value1)); // Use the `sumo` instance from SumoSelect
                });

                // Optionally trigger the change event if required
                field.trigger('change');
            }
            if (value) {
                var temp = `rotationalItemPartialDayOn-${uniqueId}`;
                var field = $(`#${temp}`); // Use jQuery to get the element

                if (typeof value.aspi_partial_day_on === 'string') {
                    value.aspi_partial_day_on = JSON.parse(value
                        .aspi_partial_day_on); // Convert string to array if it's a JSON string
                }

                // Ensure it's an array
                if (!Array.isArray(value.aspi_partial_day_on)) {
                    value.aspi_partial_day_on = value.aspi_partial_day_on ? [value.aspi_partial_day_on] :
                []; // If it's not an array, convert it to one
                }

                // Iterate over the value.aspi_partial_day_on and select them in SumoSelect
                $.each(value.aspi_partial_day_on, function(index, value1) {
                    field[0].sumo.selectItem(String(value1)); // Use the `sumo` instance from SumoSelect
                });

                // Optionally trigger the change event if required
                field.trigger('change');
            }

        }

        function removeRotationalShift(uniqueId) {
            const shiftGroup = document.getElementById(`shiftGroup-${uniqueId}`);
            const shiftItemIdElement = document.getElementById(`rotationalItemShiftId-${uniqueId}`);
            const deletedItemsInput = document.getElementById('deletedItems');
            // Ensure the shiftItemId and deletedItemsInput exist
            if (shiftItemIdElement && deletedItemsInput) {
                const shiftItemId = shiftItemIdElement.value;
                // Add the shiftItemId to the deletedItems hidden input
                let currentDeletedItems = deletedItemsInput.value ? deletedItemsInput.value.split(',') : [];
                if (shiftItemId) {
                    currentDeletedItems.push(shiftItemId);
                }
                deletedItemsInput.value = currentDeletedItems.join(',');
            }

            // Remove the shift group from the DOM
            if (shiftGroup) {
                shiftGroup.remove();
            }

            // Re-index remaining error spans
            const errorSpans = document.querySelectorAll('.text-danger');
            errorSpans.forEach((span, index) => {
                const currentId = span.id.split('_')[0];
                span.id = `${currentId}_${index}_error`; // Re-assign unique IDs based on the new index
            });
        }


        // Handle the visibility of the rotational shift form
        shiftTypeSelect.addEventListener("change", (event) => {
            fixedShiftForm.classList.add("d-none");
            rotationalShiftForm.classList.add("d-none");
            openShiftForm.classList.add("d-none");

            const selectedValue = event.target.value;
            if (selectedValue == 244) {
                fixedShiftForm.classList.remove("d-none");
            } else if (selectedValue == 245) {
                rotationalShiftForm.classList.remove("d-none");
            } else if (selectedValue == 246) {
                openShiftForm.classList.remove("d-none");
            }
        });

        $(document).on('click', '.rotational-edit-btn', function(e) {
            e.preventDefault(); // Prevent default action
            $('#rotationalShiftContainer').empty();
            let rotationalData = $(this).data('rotational-data'); // Retrieve the data object
            // Check if rotationalData is an object
            if (typeof rotationalData === 'object') {
                // Call the function to add a new shift container
                if (rotationalData && typeof rotationalData === 'object') {
                    // Convert the object to an array of [key, value] pairs and iterate with forEach
                    Object.entries(rotationalData).forEach(([key, value]) => {
                        // Add a rotational shift (ensure this function works properly)
                        addRotationalShift(value);
                    });
                } else {
                    console.error("Invalid rotationalData:", rotationalData);
                }
            } else {
                console.error("rotationalData is not an object:", rotationalData);
            }
        });
    </script>
@endsection
