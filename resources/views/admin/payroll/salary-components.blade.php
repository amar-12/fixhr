@extends('admin.layout.master')
@section('title', 'Salary Allowance')

@section('css')

@endsection
@section('content')

    {{-- Bradcrumbs Start --}}
    <div class="p-0 mt-3">
        <div class="row">
            <div class="col-md-4">
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                    <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li><a href="">Payroll</a></li>
                    <li class="active"><span><b>Components</b></span></li>
                </ol>
            </div>
            <div class="col-md-6"></div>
            <div class="col-md-2">
                <div class="page-rightheader ms-md-auto">
                    <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                        <div class="d-lg-flex d-block ms-auto">
                            <div class="btn-list">
                                <button type="button" class="btn btn-outline-primary" id="addSalaryAllowanceBtn">Add </button>
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
                <h4 class="card-title">Salary Allowance</h4>
            </div>
            <div class="card-body">
                @csrf
                <div class="row">
                    <div class="col-md-1 col-sm-4">
                        <div class="form-group">
                            <p class="form-label">Show entries</p>
                            <select id="customLengthMenu" class="form-select-md p-2 search_test" data-length
                                style="width: 100px">
                                <option value="5" style="width: 100px">5</option>
                                <option value="10" style="width: 100px">10</option>
                                <option value="25" style="width: 100px">25</option>
                                <option value="50" style="width: 100px">50</option>
                                <option value="100" style="width: 100px">100</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <p class="form-label">Search</p>
                            <div class="form-group mb-3">
                                <input type="text" id="searchFilter" placeholder="Search" class="form-control"
                                    data-search />
                            </div>
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

                    <div class="col-md-7 col-sm-4"></div>
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
                </div>

                <div class="table-responsive">
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
    <div class="modal fade" id="salaryAllowanceModal" tabindex="-1" role="dialog" aria-labelledby="salaryAllowanceModal"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="salaryAllowanceModalTitle">Add Attendance Policies</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div id="modalError"></div> <!-- Error message will be displayed here -->

                <form id="salaryAllowanceForm">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="sa_id" id="sa_id">

                        <div class="row mt-2">
                            <div class="col-xl-6">
                                <x-select2 id="earning_type_id" name="earning_type_id" label="Earning Type"
                                    :options="$earning_type_id" placeholder="Select Earning Type" class="required-field"
                                    required />
                            </div>
                            <div class="col-xl-6">
                                <x-select2 id="payroll_heading_id" name="payroll_heading_id" label="Payroll Heading"
                                    :options="$payroll_heading_id" placeholder="Select Payroll Heading"
                                    class="required-field" required />
                            </div>

                            <div class="col-xl-6">
                                <x-input type="text" id="name" name="name" label="Earning Name" placeholder="Earning Name"
                                    required />
                            </div>
                            <div class="col-xl-6">
                                <x-input type="text" id="des" name="des" label="Earning Description"
                                    placeholder="Earning Description" required />
                            </div>

                            <div class="col-xl-6">
                                <x-select2 id="calculation_type" name="calculation_type" label="Calculation Type"
                                    :options="$calculation_type" placeholder="Select Calculation Type"
                                    class="required-field" required />
                            </div>
                            <div class="col-xl-6">
                                <x-input type="text" class="numericInput" id="calculation_value" name="calculation_value"
                                    label="(% or Amount)" placeholder="Calculation Value" required />
                            </div>

                            <div class="col-xl-6">
                                <x-input type="text" class="required-field numericInput" id="sequence_valu"
                                    name="sequence_valu" label='Sequence No.' placeholder="Sequence No." maxlength="4"
                                    required />
                            </div>


                            <div class="col-xl-2">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <div class="form-check form-switch" style="display: flex; align-items: center;">
                                    <input type="hidden" name="status_check" value="0">
                                    <input class="form-check-input" type="checkbox" id="status_check" value="1"
                                        name="status_check" checked
                                        style="display: flex;align-items: center;margin-left: -20px;">
                                </div>
                            </div>

                            {{-- <div class="col-xl-3">
                                <label class="form-label">Show Payslip <span class="text-danger">*</span></label>
                                <div class="form-check form-switch" style="display: flex; align-items: center;">
                                    <input type="hidden" name="payslip_check" value="0"
                                        style="position: absolute; width: 0; height: 0; visibility: hidden;">
                                    <input class="form-check-input" type="checkbox" id="payslip_check" name="payslip_check"
                                        value="1" checked style="margin-left: 0; height: 1.5em; width: 3em;">
                                </div>
                            </div> --}}
                        </div>
                        <span id="error-message" style="color: red; display: none;"></span>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="saveBtn" class="btn btn-outline-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="text-center py-4 bg-light borderm d-none">
        <a class="btn btn-outline-primary" data-bs-target="#modaldemo3" data-bs-toggle="modal" href="#">View Live Demo</a>
    </div>
    {{-- <x-select2 id="pst_ap_id1" name="pst_ap_id1" label="Attendance Policy Type"
    :options="$attendancePolicyType" placeholder="Select Attendance Policy Type"  /> --}}
@endsection
@section('script')

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    </script>
    <script>
    $(document).ready(function () {
        $('#earning_type_id').change(function () {
            var earningTypeId = $(this).val(); // Get selected earning type ID
            var saId = $('#sa_id').val();
            console.log(saId);

            if (earningTypeId) {
                $.ajax({
                    url: "{{ route('get.earning.name') }}", // Define this route in Laravel
                    type: "GET",
                    data: { earning_type_id: earningTypeId, sa_id: saId },

                    success: function (response) {
                        if (response.success) {
                            $('#name').val(response.earning_name); // Update Earning Name field
                        } else {
                            $('#name').val('');
                        }
                    },
                    error: function () {
                        console.log("Error fetching earning name.");
                    }
                });
            } else {
                $('#name').val('');
            }
        });
    });
</script>
    <script>
        document.getElementById('salaryAllowanceForm').addEventListener('submit', function(event) {
            // Prevent the default form submission
            event.preventDefault();

            // Create a FormData object
            const formData = new FormData(this);

            // Convert checkbox states to 1 or 0
            const checkboxes = ['calculate_basis', 'payslip_check', 'pf_check', 'esic_check', 'tax_check', 'status_check'];
            checkboxes.forEach(name => {
                const el = document.getElementById(name);
                if (el) {
                    formData.set(name, el.checked ? '1' : '0');
                } else {
                    console.warn(`Checkbox with id="${name}" not found in DOM`);
                }
            });

            // Send the data to the server using fetch or XMLHttpRequest
            fetch(this.action, {
                    method: this.method,
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    // Handle success or error
                    console.log(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        });
    </script>
    <script type="text/javascript">
        $(function() {
            // CSRF Token Setup (for all AJAX requests)
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Initialize DataTable
            initializeDatatable();

            // Initialize Select2
            initializeSelect2();

            // Handle Create or Update Shift Type
            $('#salaryAllowanceForm').on('submit', handleSalaryAllowanceSubmit);
            // Handle Delete Salary Allowance
            $(document).on('click', '.delete-salary-allowance', handleDeleteSalaryAllowance);

        });


        // Initialize DataTable
        function initializeDatatable() {
            datatable({
                tableId: "attendance-shift-type-table-dynamic",
                url: "{{ route('payroll.component.list') }}",
                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataDateFilter: '[data-date-filter]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]'
            });
        }


        // Initialize Select2
        function initializeSelect2() {
            $('.select2').select2();

            $('#salaryAllowanceModal').on('shown.bs.modal', function() {
                if (!$(this).data('select2-initialized')) {
                    $('.select2').select2({
                        dropdownParent: $('#salaryAllowanceModal')
                    });
                    $(this).data('select2-initialized', true);
                }
            });
        }


        function handleSalaryAllowanceSubmit(e) {
            e.preventDefault();

            var form = $(this);
            var id = $('#sa_id').val(); // Get salary allowance ID (if updating)

            var isValid = true;

            // Validate required fields
            $('.required-field').each(function() {
                if (!$(this).val()) {
                    $(this).addClass('is-invalid'); // Highlight error
                    isValid = false;
                } else {
                    $(this).removeClass('is-invalid'); // Remove error highlight
                }
            });

            if (!isValid) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Please fill in all required fields!',
                });
                return; // Stop if validation fails
            }

            $.ajax({
                url: "{{ route('payroll.component.create') }}",
                method: "POST",
                data: form.serialize(),
                beforeSend: function() {
                    $('#saveBtn').attr('disabled', true); // Disable button to prevent multiple submissions
                },
                success: function(response) {
                    if (response.status) {
                        $('#salaryAllowanceModal').modal('hide'); // Close the modal before showing success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 3000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload(); // Reload page after success
                        });
                    } else {
                        showDuplicateError(response.message);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 400 && xhr.responseJSON && xhr.responseJSON.message) {
                        showDuplicateError(xhr.responseJSON.message);
                    } else {
                        handleError(xhr);
                    }
                },
                complete: function() {
                    $('#saveBtn').attr('disabled', false); // Re-enable button
                }
            });
        }

        // Function to display duplicate error message
        function showDuplicateError(message) {
            Swal.fire({
                icon: 'warning',
                title: 'Duplicate Entry',
                text: message,
            });
        }



        // Clear errors as the user interacts with input fields
        $('input, select').on('input change', function() {
            const fieldId = $(this).attr('id');
            $('#' + fieldId + '_error').text(''); // Clear the error message
        });


        // Handle error response
        function handleError(xhr) {
            if (xhr.status === 422) { // Validation error
                let errors = xhr.responseJSON.errors;
                showValidationErrors(errors); // Show validation errors
            } else {
                Swal.fire({
                    title: 'Something went wrong!',
                    text: 'Please try again later.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        }

        // Show validation errors
        function showValidationErrors(errors) {
            $('.text-danger').text('');
            $.each(errors, function(key, value) {
                $('#' + key + '_error').text(value[0]);
                $('#' + key + '_error').show();
            });
        }

        // Reset form fields
        function resetForm() {
            // Reset the form itself
            $('#salaryAllowanceForm')[0].reset();

            // Manually reset any fields that require special handling
            $('#sa_id').val('');
            $('#name').val('');
            $('#des').val('');
            $('#calculation_type').val('').trigger('change');
            $('#earning_type_id').val('').trigger('change');
            $('#payroll_heading_id').val('').trigger('change');
            $('#calculation_value').val('');
            $('#sequence_valu').val('');

            // Reset checkboxes
            $('#pf_check').prop('checked', false);
            $('#pf_condition').prop('checked', false);
            $('#esic_check').prop('checked', false);
            $('#calculate_basis').prop('checked', false);
            $('#tax_check').prop('checked', false);
            $('#payslip_check').prop('checked', false);
            $('#status_check').prop('checked', false);

            $('#payslip').val('');

            $('#salaryAllowanceModal').find('input, select').each(function() {
                const fieldId = $(this).attr('id');
                $('#' + fieldId + '_error').text('');
            });
        }



        function handleDeleteSalaryAllowance(event) {
            event.preventDefault(); // Prevent any default behavior
            var id = $(this).data('id'); // Get the ID from the button
            if (!id) {
                console.error("No ID found for deletion.");
                return;
            }

            Swal.fire({
                title: 'Are you sure?',
                text: 'You will not be able to recover this salary allowance!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, keep it'
            }).then((result) => {
                if (result.isConfirmed) {
                    var url = "{{ route('payroll.component.delete', ':id') }}".replace(':id', id);

                    $.ajax({
                        url: url,
                        method: "DELETE", // Correct method to match Laravel route
                        headers: {
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                                'content') // Laravel CSRF protection
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: response.success,
                                icon: 'success',
                                timer: 3000,
                                timerProgressBar: true,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload(); // Reload page after deletion
                            });
                        },
                        error: function(xhr) {
                            var errorMessage =
                                'An error occurred while deleting the salary allowance. Please try again.';
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


        $(document).on('click', '#addSalaryAllowanceBtn', function() {
            handleAddSalaryAllowance();
        });

        function handleAddSalaryAllowance() {
            resetForm();
            $('#salaryAllowanceModalTitle').html('Add Salary Allowance');
            $('#saveBtn').html('Save');
            $('#salaryAllowanceModal').modal('show');
            handleCheckboxChange();
        }

        // Handle Edit Salary Allowance Button
        $(document).on('click', '.edit-salary-allowance', function(e) {
            e.preventDefault();
            $('#salaryAllowanceModalTitle').html('Edit Salary Allowance');
            $('#saveBtn').html('Update');

            var sa_id = $(this).data('id'); // Corrected key
            var title = $(this).data('title');
            var des = $(this).data('dis');
            var calculation_type = $(this).data('calculation_type');
            var sequence_valu = $(this).data('sequence_valu');

            var earning_type_id = $(this).data('earning_type_id');
            var payroll_heading_id = $(this).data('payroll_heading_id');

            var calculation_value = $(this).data('threshold_value');
            var payslip_name = $(this).data('name_in_payslip');
            var payslip_check = $(this).data('payslip_check');
            var pf_check = $(this).data('pf_check');
            var pf_condition = $(this).data('for_pf_condition');
            var esic_check = $(this).data('esic_check');
            var calculate_basis = $(this).data('calculate_basis');
            var tax_check = $(this).data('tax_check');
            var status_check = $(this).data('status_check');
            var calculate_basis = $(this).data('calculate_basis');

            // Fill modal form fields
            $('#sa_id').val(sa_id);
            $('#name').val(title);
            $('#des').val(des);
            $('#calculation_type').val(calculation_type).trigger('change');
            $('#earning_type_id').val(earning_type_id).trigger('change');
            // $('#earning_type_id').val(earning_type_id);
            $('#payroll_heading_id').val(payroll_heading_id).trigger('change');
            $('#calculation_value').val(calculation_value);
            $('#sequence_valu').val(sequence_valu);

            $('#payslip').val(payslip_name);

            // Set checkbox values (true/false)
            $('#pf_check').prop('checked', pf_check == 1);
            $('#payslip_check').prop('checked', payslip_check == 1);
            $('#calculate_basis').prop('checked', calculate_basis == 1);
            $('#esic_check').prop('checked', esic_check == 1);
            $('#calculate_basis').prop('checked', calculate_basis == 1);
            $('#tax_check').prop('checked', tax_check == 1);
            $('#status_check').prop('checked', status_check == 1);

            // Set radio button for pf_condition
            $('input[name="pf_condition"]').prop('checked', false);
            if (pf_condition == 343) {
                $('#pf_343').prop('checked', true);
            } else if (pf_condition == 344) {
                $('#pf_344').prop('checked', true);
            }

            // Show the modal
            $('#salaryAllowanceModal').modal('show');
        });


        // Function to handle checkbox dependencies
        function handleCheckboxChange() {
            // Toggle readonly or disable fields based on conditions
            $('#pf_condition').prop('disabled', !$('#pf_check').prop('checked'));
        }

        // Bind checkbox change event
        $('#payslip_check').on('change', handleCheckboxChange);
        $('#pf_check').on('change', handleCheckboxChange);
        $('#esic_check').on('change', handleCheckboxChange);
        $('#calculate_basis').on('change', handleCheckboxChange);
        $('#tax_check').on('change', handleCheckboxChange);
        $('#status_check').on('change', handleCheckboxChange);

        // Run on page load to set initial state
        $(document).ready(function() {
            handleCheckboxChange(); // Initialize the form fields based on the current checkbox state
        });
    </script>

@endsection
