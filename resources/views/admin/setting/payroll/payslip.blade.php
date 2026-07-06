@extends('admin.layout.master')

@section('title', 'Payslips')

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.3/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('content')
    <div>
        <div class="p-0 pb-4">
            <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="active"><span><b>Payslips</b></span></li>
            </ol>
        </div>

        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header border-0">
                        <h4 class="card-title">Payslip</h4>
                        <div class="page-rightheader ms-auto">
                            <div class="align-items-end flex-wrap my-auto right-content breadcrumb-right">
                                <div class="d-flex">
                                    <button type="button" class="btn btn-outline-primary" id="createPaySlipBtn">Create
                                        Payslip</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            @foreach (['Employee Status' => 'activeFilter', 'Employee Name' => 'employeeFilter', 'Month' => 'monthFilter'] as $label => $id)
                                <div class="col-md">
                                    <div class="form-group">
                                        <p class="form-label">{{ $label }}</p>
                                        @if ($id === 'activeFilter')
                                            <select id="{{ $id }}"
                                                class="form-select-md p-2 search_test custom-heighlight" data-filter>
                                                <option value="">All</option>
                                                <option value="1">Active</option>
                                                <option value="0">Inactive</option>
                                            </select>
                                        @elseif ($id === 'employeeFilter')
                                            <select id="{{ $id }}"
                                                class="form-select-md p-2 search_test custom-heighlight" data-filter>
                                                @foreach ($employeeList as $emp)
                                                    <option value="{{ $emp->emp_id }}">{{ $emp->emp_full_name }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <input type="month" data-filter id="{{ $id }}"
                                                name="{{ $id }}" value="{{ now()->format('Y-m') }}"
                                                class="form-control" />
                                        @endif
                                    </div>
                                </div>
                            @endforeach

                            <div class="col-md">
                                <div class="form-group">
                                    <p class="form-label">Search</p>
                                    <input type="text" id="searchFilter" placeholder="Search" class="form-control"
                                        data-search />
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-1 col-sm-4">
                                <div class="form-group">
                                    <p class="form-label">Show entries</p>
                                    <select id="customLengthMenu" class="form-select-md p-2 search_test"
                                        style="width: 100px" data-length>
                                        @foreach ([5, 10, 25, 50, 100] as $value)
                                            <option value="{{ $value }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-9 col-sm-4"></div>

                            <div class="col-md-2 col-sm-4 pt-5 text-end">
                                <div class="btn-group">
                                    <button class="btn btn-outline-danger dropdown-toggle" type="button" id="defaultDropdown"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        Export As
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-export" aria-labelledby="defaultDropdown">
                                        @foreach (['csv', 'excel', 'pdf', 'copy', 'print'] as $format)
                                            <li><a class="dropdown-item" href="#"
                                                    data-export="{{ $format }}">{{ strtoupper($format) }}</a></li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table display table-vcenter text-wrap border-bottom" id="payslip-table-dynamic">
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
    </div>

    <div class="modal fade" id="createPaySlip" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content tx-size-sm">
                <div class="modal-header border-0">
                    <h4 class="modal-title" id="modalTitle">Create Payslip</h4>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" id="addTUpdatePaySlip">
                    @csrf
                    <input type="hidden" name="payslip_id" id="payslip_id"> <!-- Hidden field for payslip ID -->
                    <div class="modal-body">
                        <select name="employee_id" id="employee_id" class="form-control form-select paySlip select2"
                            required>
                            <option value="">Select Employee Name</option>
                            @foreach ($employeeList as $employee)
                                <option value="{{ $employee->emp_id }}">
                                    {{ $employee->emp_full_name }}</option>
                            @endforeach
                        </select>

                        <label for="month" class="form-label mb-1 mt-3">Select Month <span
                                class="text-red">*</span></label>
                        <input type="month" id="pay_slip_month" name="pay_slip_month"
                            value="{{ now()->format('Y-m') }}" class="form-control" required />
                    </div>
                    <div class="modal-footer d-flex justify-content-end mt-5">
                        <button type="button" class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-outline-primary saveUptBtn" id="saveUptBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script>
        // Show modal for creating a new payslip
        $(document).on('click', '#createPaySlipBtn', function() {
            $('#modalTitle').text('Create Payslip');
            $('#addTUpdatePaySlip')[0].reset(); // Clear the form
            $('#payslip_id').val(''); // Clear the hidden ID field
            $('#createPaySlip').modal('show');

        });

        // Show modal for editing an existing payslip
        $(document).on('click', '.editPaySlipBtn', function() {
            var payslipId = $(this).data('id'); // Get the payslip ID
            var employeeId = $(this).data('employee-id'); // Get the employee ID
            var paySlipMonth = $(this).data('month'); // Get the payslip month

            // Populate the modal fields with the fetched data
            $('#payslip_id').val(payslipId); // Set the payslip ID
            $('#employee_id').val(employeeId); // Set the employee ID
            $('#pay_slip_month').val(paySlipMonth); // Set the payslip month

            // Change the modal title to 'Edit Payslip'
            $('#modalTitle').text('Edit Payslip');
            $('#createPaySlip').modal('show'); // Show the modal
        });

        // Handle form submission for both create and edit
        $('#addTUpdatePaySlip').on('submit', function(e) {
            e.preventDefault();


            $.ajax({
                url: '{{ route('store.payroll.slip') }}',
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message,
                        timer: 3000,
                        showConfirmButton: false
                    });
                    $('#createPaySlip').modal('hide'); // Hide the modal
                    // Optionally refresh the data table or reload the page
                    location.reload(); // Reload the page to see the changes
                },
                error: function(xhr) {
                    // Show the error message from the server response
                    let errorMessage = xhr.responseJSON.message ||
                        'An error occurred while saving the payslip.';

                    // If there's a more specific error message, use it
                    if (xhr.responseJSON.error_message) {
                        errorMessage = xhr.responseJSON.error_message;
                    }

                    // If the error is a duplicate entry (409 Conflict)
                    if (xhr.status === 409) {
                        errorMessage =
                            'A duplicate entry was found for this employee and month. Please check the data and try again.';
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage,
                    });
                }
            });
        });

        // Handle Delete Shift Type
        function handleDeletePaySlip() {
            var id = $(this).data('id');
            // alert('id', id);
            Swal.fire({
                title: 'Are you sure?',
                text: 'You will not be able to recover this Payslip!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, keep it'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Generate the correct URL dynamically
                    var url = "{{ route('destroy.payroll.slip', ':id') }}".replace(':id', id);
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
                            var errorMessage =
                                'An error occurred while deleting. Please try again.';
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

        // Initialize DataTable
        function initializeDatatable() {
            datatable({
                tableId: "payslip-table-dynamic",
                url: "{{ route('payroll.payslips') }}",
                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataDateFilter: '[data-date-filter]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]',
                dataStateSave: true
            });
        }

        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            initializeDatatable();
            $(document).on('click', '.deleteBtn', handleDeletePaySlip);
            $('.select2').select2(); // Initialize select2
        });

        $('#createPaySlip').on('shown.bs.modal', function() {
            if (!$(this).data('select2-initialized')) {
                $('.select2').select2({
                    dropdownParent: $('#createPaySlip')
                });
                $(this).data('select2-initialized', true);
            }
        });
    </script>
@endsection
