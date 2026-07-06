@extends('admin.layout.master')
@section('title', 'Weekly Policy')
@section('css')

@endsection
@section('content')
    {{-- Bradcrumbs Start --}}
    <div class="p-0 mt-3">
        <div class="row">
            <div class="col-md-4">
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                    <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ url('/admin/settings/attendance') }}">Attendance Settings</a></li>
                    <li class="active"><span><b>Weekly Policy</b></span></li>
                </ol>
            </div>
            <div class="col-md-6"></div>
            <div class="col-md-2">
                <div class="page-rightheader ms-md-auto">
                    <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                        <div class="d-lg-flex d-block ms-auto">
                            <div class="btn-list">
                                <button type="button" class="btn btn-outline-primary" id="addShiftTypeBtn">Add weekly
                                    policy</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Bradcrumbs End --}}

    <div class="row mt-5">
        <div class="col-xl-12 col-md-12 col-lg-12">
            <div class="card">
                <div class="card-header border-0">
                    <h4 class="card-title">Weekly Policy</h4>
                </div>
                <div class="card-body">
                    @csrf
                    <div class="row">
                        <div class="col-sm-1">
                            <div class="form-group">
                                <p class="form-label">Show entries</p>
                                <select id="customLengthMenu" class="form-select-md p-2 search_test" style="width: 100%"
                                    data-length>
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>


                        <div class="col-sm-2">
                            <div class="form-group">
                                <p class="form-label">Search</p>
                                <input type="text" id="searchFilter" placeholder="Search" class="form-control"
                                    data-search />
                            </div>
                        </div>

                        <div class="col-sm-7">
                        </div>


                        <div class="col-sm-1"
                            style=" padding-left: 1px;  padding-right: 1px; height: 10px; margin-top: 28px;    ">
                            <div class="form-group dropdown">
                                <button class="export-button dropdown-toggle" type="button" id="defaultDropdown"
                                    data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false">
                                    <i class="fa fa-download me-2"></i> Export As
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



                        <style>
                            .export-button {
                                display: flex;
                                align-items: center;
                                gap: 6px;
                                background-color: white;
                                border: 1px solid #ddd;
                                border-radius: 999px;
                                padding: 8px 14px;
                                font-size: 14px;
                                cursor: pointer;
                                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
                                transition: background-color 0.2s ease, box-shadow 0.2s ease;
                            }

                            .export-button:hover {
                                background-color: #f1f1f1;
                                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                            }

                            .dropdown-menu-export {
                                font-size: 14px;
                                min-width: 140px;
                            }

                            .dropdown-menu-export .dropdown-item:hover {
                                background-color: #f8f9fa;
                            }

                            .custom-button {
                                display: flex;
                                align-items: center;
                                gap: 6px;
                                background-color: white;
                                border: 1px solid #ddd;
                                border-radius: 999px;
                                padding: 8px 14px;
                                font-size: 14px;
                                cursor: pointer;
                                box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
                                transition: background-color 0.2s ease, box-shadow 0.2s ease;
                            }

                            .custom-button:hover {
                                background-color: #f1f1f1;
                                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                            }

                            .custom-button svg {
                                width: 16px;
                                height: 16px;
                            }
                        </style>

                    </div>

                    <div>
                        <table class="table display table-hover table-vcenter text-wrap border-bottom"
                            id="attendance-shift-type-table-dynamic">
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

    <!-- MODAL -->
    <div class="modal fade" id="weeklyPolicyModal" tabindex="-1" role="dialog" aria-labelledby="weeklyPolicyModal"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="weeklyPolicyModalTitle"></h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">x</span>
                    </button>
                </div>
                <form id="weeklyPolicyForm">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="pst_id" id="pst_id">

                        <div class="row">
                            <!-- Weekly Off Policy Name Input -->
                            <x-input type="text" id="pwo_name" name="pwo_name" label="Weekly Off Policy Name"
                                astric="*" placeholder="Weekly Off Policy Name" maxlength="255"
                                oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')" />
                        </div>

                        <input type="hidden" id="pwo_id" name="pwo_id">

                        <!-- Table for Weekdays and Weekend Checkboxes -->
                        <table class="table table-bordered">
                            <thead>
                                <tr style="text-align: center">
                                    <th>Week Day</th>
                                    <th>Is Unpaid</th>
                                    <th>Select All</th>
                                    @foreach ($recurrenceDay as $key1 => $item1)
                                        <th>{{ $item1 }} Week</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($weekDay as $key => $item)
                                    <tr style="text-align: center" id="row_{{ $key }}">
                                        <td>
                                            <label class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input weekday-checkbox"
                                                    data-weekday="{{ $key }}">
                                                <span class="custom-control-label">{{ $item }}</span>
                                            </label>
                                        </td>
                                        <td><input type="checkbox" name="is_unpaid[{{ $key }}]"
                                                class="is-unpaid-{{ $key }}" disabled
                                                value="{{ $key }}"></td>
                                        <td>
                                            <input type="checkbox" class="weekday-select-all-checkbox"
                                                data-weekday-select-all="{{ $key }}" disabled>
                                        </td>
                                        @foreach ($recurrenceDay as $key1 => $item1)
                                            <td>
                                                <input type="checkbox"
                                                    name="week_off[{{ $key }}][{{ $key1 }}]"
                                                    class="weekend-checkbox-{{ $key }}"
                                                    value="{{ $key1 }}" disabled>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger  cancel"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="saveBtn" class="btn btn-outline-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(function() {
            // CSRF Token Setup (for all AJAX requests)
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Initialize DataTable
            initializeDatatable();

            // Handle Delete Shift Type
            $(document).on('click', '.delete-weekly-policy', handleDeleteShiftType);

            // Handle Add Shift Type Button
            $(document).on('click', '#addShiftTypeBtn', handleAddShiftType);

            // Handle Edit Shift Type Button
            $(document).on('click', '.edit-weekly-policy', handleWeeklyPolicy);
        });

        // Initialize DataTable
        function initializeDatatable() {
            datatable({
                tableId: "attendance-shift-type-table-dynamic",
                url: "{{ route('weekly-policy.index') }}",
                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataDateFilter: '[data-date-filter]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]'
            });
        }

        document.addEventListener('DOMContentLoaded', function() {

            const weekdayCheckboxes = document.querySelectorAll('.weekday-checkbox');
            const selectAllCheckboxes = document.querySelectorAll('.weekday-select-all-checkbox');

            // Function to handle the main weekday checkbox logic
            weekdayCheckboxes.forEach(function(weekdayCheckbox) {
                weekdayCheckbox.addEventListener('change', function() {
                    const weekdayKey = this.getAttribute('data-weekday');
                    const selectAllCheckbox = document.querySelector(
                        `.weekday-select-all-checkbox[data-weekday-select-all="${weekdayKey}"]`);
                    const weekendCheckboxes = document.querySelectorAll(
                        `.weekend-checkbox-${weekdayKey}`);
                    const isUnpaidCheckboxes = document.querySelectorAll(
                    `.is-unpaid-${weekdayKey}`);

                    // Enable or disable "Select All" and weekend checkboxes
                    selectAllCheckbox.disabled = !this.checked;
                    weekendCheckboxes.forEach(function(checkbox) {
                        checkbox.disabled = !weekdayCheckbox.checked;
                        checkbox.checked = false;
                        // checkbox.checked = weekdayCheckbox
                        // .checked; // Synchronize all weekend checkboxes
                    });
                    isUnpaidCheckboxes.forEach(function(checkbox) {
                        checkbox.disabled = !weekdayCheckbox.checked;
                        checkbox.checked = false;
                    })

                    // Uncheck "Select All" if the main weekday checkbox is unchecked
                    if (!this.checked) {
                        selectAllCheckbox.checked = false;
                    }
                });
            });

            // Function to handle "Select All" logic for each row
            selectAllCheckboxes.forEach(function(selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    const weekdayKey = this.getAttribute('data-weekday-select-all');
                    const weekendCheckboxes = document.querySelectorAll(
                        `.weekend-checkbox-${weekdayKey}`);

                    // Toggle all weekend checkboxes in the row based on "Select All"
                    weekendCheckboxes.forEach(function(checkbox) {
                        checkbox.checked = selectAllCheckbox.checked;
                    });
                });
            });

            // Handle individual weekend checkbox logic to uncheck "Select All" if any checkbox is unchecked
            const weekendCheckboxes = document.querySelectorAll('input[class^="weekend-checkbox-"]');
            weekendCheckboxes.forEach(function(weekendCheckbox) {
                weekendCheckbox.addEventListener('change', function() {
                    const weekdayKey = this.className.match(/weekend-checkbox-(\d+)/)[
                        1]; // Extract weekday key from class name
                    const selectAllCheckbox = document.querySelector(
                        `.weekday-select-all-checkbox[data-weekday-select-all="${weekdayKey}"]`);
                    const weekendCheckboxesInRow = document.querySelectorAll(
                        `.weekend-checkbox-${weekdayKey}`);

                    // Check if all weekend checkboxes are checked
                    const allChecked = Array.from(weekendCheckboxesInRow).every(function(checkbox) {
                        return checkbox.checked;
                    });

                    // Update the "Select All" checkbox state
                    selectAllCheckbox.checked = allChecked;
                });
            });

            const form = document.getElementById('weeklyPolicyForm');

            document.getElementById('weeklyPolicyForm').addEventListener('submit', function(event) {
                event.preventDefault(); // Prevent default form submission

                // Get form fields
                const pwoName = document.getElementById('pwo_name').value.trim();
                const weekdayCheckboxes = document.querySelectorAll('.weekday-checkbox');

                // Validation 1: Check if 'pwo_name' is empty
                if (pwoName === '') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Validation Error',
                        text: 'Please enter the Weekly Off Policy Name.'
                    });
                    return;
                }

                // Validation 2: Check if at least one weekday checkbox is selected
                let isWeekdaySelected = false;
                weekdayCheckboxes.forEach(function(checkbox) {
                    if (checkbox.checked) {
                        isWeekdaySelected = true;
                    }
                });

                // if (!isWeekdaySelected) {
                //     Swal.fire({
                //         icon: 'warning',
                //         title: 'Validation Error',
                //         text: 'Please select at least one weekday.'
                //     });
                //     return;
                // }

                // Validation 3: For each selected weekday, ensure at least one weekend checkbox is checked
                let isWeekendValid = true;

                weekdayCheckboxes.forEach(function(checkbox) {
                    if (checkbox.checked) {
                        const weekdayKey = checkbox.getAttribute('data-weekday');
                        const weekendCheckboxes = document.querySelectorAll(
                            `.weekend-checkbox-${weekdayKey}`);

                        let isRowValid = false;
                        weekendCheckboxes.forEach(function(weekendCheckbox) {
                            if (weekendCheckbox.checked) {
                                isRowValid = true;
                            }
                        });

                        // If no weekend checkbox is selected in this row, mark as invalid
                        if (!isRowValid) {
                            isWeekendValid = false;
                            Swal.fire({
                                icon: 'warning',
                                title: 'Validation Error',
                                text: `Please select at least one weekend for the selected week-day: ${checkbox.nextElementSibling.innerText}`
                            });
                        }
                    }
                });

                if (!isWeekendValid) {
                    return;
                }

                // Disable the submit button after the first click
                const saveButton = document.getElementById('saveBtn');
                saveButton.disabled = true;
                saveButton.innerHTML = 'Processing...';

                // If all validations pass, proceed with form submission
                const formData = new FormData(this); // Collect form data

                fetch('{{ route('weekly-policy.store') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Policy saved successfully.',
                                timer: 3000,
                                timerProgressBar: true,
                                showConfirmButton: false,
                                didClose: () => location.reload() // Reload to reflect changes
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: data.message || 'An unexpected error occurred.'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'An error occurred while saving the policy.'
                        });
                    });
            });

        });


        // Handle Delete Shift Type
        function handleDeleteShiftType() {
            var id = $(this).data('id');
            // alert('id', id);
            Swal.fire({
                title: 'Are you sure?',
                text: 'You will not be able to recover this weekly policy!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, keep it'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Generate the correct URL dynamically
                    var url = "{{ route('weekly-policy.destroy', ':id') }}".replace(':id', id);
                    $.ajax({
                        url: url,
                        method: "DELETE",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: response.success,
                                icon: 'success',
                                timer: 3000,
                                timerProgressBar: true,
                                showConfirmButton: false,
                                didClose: () => location.reload()
                            });
                        },
                        error: function(xhr) {
                            var errorMessage = 'An error occurred while deleting. Please try again.';
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                errorMessage = xhr.responseJSON.error;
                            }

                            Swal.fire({
                                title: 'Error!',
                                text: errorMessage,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    });

                }

            });
        }


        // Handle Add Shift Type
        function handleAddShiftType() {
            resetForm();
            $('#weeklyPolicyModalTitle').html('Add Weekly Policy');
            $('#saveBtn').html('Save');
            $('#weeklyPolicyModal').modal('show');
        }

        // Handle Edit Shift Type
        function handleWeeklyPolicy() {
            // Show the modal
            resetForm();
            $('#weeklyPolicyModal').modal('show');
            $('#weeklyPolicyModalTitle').html('Update Weekly Policy');

            const weeklyPolicyData = $(this).data('weekly');

            // Parse the recurrence_day_ids string into a JavaScript object
            const recurrenceDayIds = JSON.parse(weeklyPolicyData.recurrence_day_ids);
            const pwo_is_unpaid = JSON.parse(weeklyPolicyData.pwo_is_unpaid);
            document.getElementById('pwo_name').value = weeklyPolicyData.name;
            document.getElementById('pwo_id').value = weeklyPolicyData.id;
            console.log(pwo_is_unpaid);

            // Iterate over the recurrenceDayIds object
            Object.keys(recurrenceDayIds).forEach(key => {

                // Select the table row (tr) for the current weekday using the key
                const tr = document.getElementById(`row_${key}`);

                if (tr) {
                    // Select the main checkbox and 'select all' checkbox for the current key
                    const checkbox = tr.querySelector(`[data-weekday="${key}"]`);
                    const checkboxSelectAll = tr.querySelector(`[data-weekday-select-all="${key}"]`);

                    // Enable the 'select all' checkbox
                    if (checkboxSelectAll) {
                        checkboxSelectAll.disabled = false;
                    }

                    // If the main checkbox exists, check it and trigger the click event
                    if (checkbox) {
                        checkbox.checked = true;
                        checkbox.dispatchEvent(new Event('click'));
                    }

                    // Get the weekend checkboxes for the current row (e.g., Monday, Tuesday, etc.)
                    const weekendCheckboxSelectAll = tr.querySelectorAll(`.weekend-checkbox-${key}`);
                    weekendCheckboxSelectAll.forEach(childCheckbox => {
                        childCheckbox.disabled = false; // Enable all weekend checkboxes for this row
                    });

                    const isUnpaidCheckbox = tr.querySelectorAll(`.is-unpaid-${key}`);
                    isUnpaidCheckbox.forEach(checkbox => {
                        checkbox.disabled = false;
                        pwo_is_unpaid.includes(parseInt(key)) ? checkbox.checked = true : null;
                    });

                    // Get the associated array of day IDs (e.g., [334, 335, 336])
                    const elements = recurrenceDayIds[key];

                    // Iterate over the elements in the array and update the checkboxes
                    elements.forEach(element => {
                        // Get the checkbox for each specific day
                        const childCheckbox = document.querySelector(
                            `[name="week_off[${key}][${element}]"]`);

                        if (childCheckbox) {
                            // Set the checkbox to checked and enable it
                            childCheckbox.checked = true;
                            childCheckbox.disabled = false; // Enable it
                            childCheckbox.dispatchEvent(new Event('click')); // Simulate a click if needed
                        }
                    });
                    const allChecked = Array.from(weekendCheckboxSelectAll).every(checkbox => checkbox.checked);
                    if (allChecked) {
                        checkboxSelectAll.checked = true; // Check the 'select all' checkbox for this row
                    }
                }
            });

        }


        function resetForm() {
            // Reset text inputs
            document.getElementById('pst_id').value = '';
            document.getElementById('pwo_name').value = '';
            document.getElementById('pwo_id').value = '';

            // Uncheck all weekday checkboxes
            const weekdayCheckboxes = document.querySelectorAll('.weekday-checkbox');
            weekdayCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });

            // Uncheck all 'Select All' checkboxes and weekend checkboxes
            const selectAllCheckboxes = document.querySelectorAll('.weekday-select-all-checkbox');
            const weekendCheckboxes = document.querySelectorAll(
                '[class^="weekend-checkbox-"]'); // Select all weekend checkboxes
            const isUnpaidCheckbox = document.querySelectorAll(
                '[class^="is-unpaid-"]'); // Select all weekend checkboxes

            selectAllCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
                checkbox.disabled = true; // Ensure they remain disabled if needed
            });

            weekendCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
                checkbox.disabled = true;
            });

            isUnpaidCheckbox.forEach(checkbox => {
                checkbox.checked = false;
                checkbox.disabled = true;
            });
        }
    </script>
@endsection
