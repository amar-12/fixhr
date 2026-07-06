@extends('admin.layout.master')
@section('title', 'Loan / Advance')

@section('content')
    {{-- Bradcrumbs Start --}}
    <div class="p-0 mt-3">
        <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
            <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
            <li><a href="">Payroll</a></li>
            <li class="active"><span><b>Loan / Advance </b></span></li>
        </ol>
    </div>
    {{-- Bradcrumbs End --}}

    <div class="page-header d-md-flex d-block">
        <div class="page-leftheader">
            <div class="page-title">Loan / Advance </div>
            {{-- <p class="text-muted m-0">{{ count($leavePolicy) }} Active Leave Policy</p> --}}
        </div>
        <div class="page-rightheader ms-md-auto">
            <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                <div class="d-lg-flex d-block ms-auto">
                    <div class="btn-list">
                        <button type="button" class="btn btn-outline-primary" data-bs-target="#loanAdvanceModal"
                            data-bs-toggle="modal" id="addloanAdvanceBtn">Add Loan / Advance</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-12 col-md-12 col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Loan / Advance List</div>
                </div>
                <div class="card-body">

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

                        <div class="col-md-9 col-sm-4"></div>

                        <div class="col-md-2 col-sm-4 pt-5" align="right">
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

                        <div class="table-responsive">
                            <table class="table display table-vcenter text-wrap border-bottom"
                                id="leave-policy-table-dynamic">
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

    <!-- MODAL -->
    <div class="modal fade" id="loanAdvanceModal" tabindex="-1" role="dialog" aria-labelledby="loanAdvanceModal"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loanAdvanceModalTitle">Add Loan / Advance </h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form id="loanAdvanceForm"> @csrf
                    <div class="modal-body">
                        <input type="hidden" name="pla_id" id="pla_id">
                        <div class="row mt-2">
                            <div class="col-md-12 mb-4">
                                <label class="form-label" for="employee">Employee <span
                                        class="text-danger">*</span></label>
                                <div class="d-flex gap-2" style="width: 100%;">
                                    <select name="employee" id="employee" class="form-control custom-select"
                                        style="width: 20%;" data-placeholder="Select Employee" required
                                        onchange="handleStatusChange(this)">
                                        <option class="text-muted" value="" label="Select Employee"></option>
                                        <option value="71">Active</option>
                                        <option value="72">Inactive</option>
                                    </select>

                                    <input type="search" class="form-control" id="search" name="search"
                                        placeholder="Search Employee" disabled style="width: 40%;">
                                    <p id="employeeError" style="color: red; display: none;"></p>
                                    <div id="employeeDropdownContainer" style="width: 20%;">
                                        <p id="employeeError" style="color: red; display: none;"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-6 mb-4">
                                <label class="form-label" for="type">Type <span class="text-danger">*</span></label>
                                <select name="type" id="type" class="form-control custom-select select2"
                                    data-placeholder="Select Type" required>
                                    <option class="text-muted" value="" label="Select Type"></option>
                                    @foreach ($type as $item)
                                        <option value="{{ $item->m_id }}">{{ $item->m_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label" for="amount">Amount <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="amount" id="amount" value="0"
                                    min="0" placeholder="Enter Amount">
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label" for="provided-date">Provided Date <span
                                        class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="provided-date" id="provided-date"
                                    value="">
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label" for="description">Description <span
                                        class="text-danger">*</span></label>
                                <textarea class="form-control" name="description" id="description" rows="1" placeholder="Description"></textarea>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label" for="installment-amount">Installment Amount</label>
                                <input type="number" class="form-control" name="installment-amount"
                                    id="installment-amount" value="0" min="0"
                                    placeholder="Installment Amount">
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label" for="total-installments">Total Installments <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="total-installments"
                                    id="total-installments" value="0" min="0"
                                    placeholder="Total Installments">
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label" for="installment-start">Installment Start Date <span
                                        class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="installment-start"
                                    id="installment-start" value="">
                            </div>
                            {{-- <div class="col-md-6 mb-4 d-flex align-items-center">
                                <div>
                                    <label class="form-label d-block" for="settled">Settled</label>
                                    <input type="checkbox" id="settled" class="ml-2">
                                </div>
                            </div> --}}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="saveBtn" class="btn btn-outline-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- END MODAL -->
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            let selectedEmployeeId = $('#employeeId').val();

            datatable({
                tableId: "leave-policy-table-dynamic",
                url: "{{ route('loans.index') }}",
                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataDateFilter: '[data-date-filter]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]',
                dataStateSave: false
            });
        });

        // $(document).on('change', '#employeeId', function() {
        //     let selectedEmployeeId = $(this).val();
        //     console.log("Selected Employee ID:", selectedEmployeeId);
        // });


        document.addEventListener('DOMContentLoaded', function() {
            // Get references to the input fields
            const amountInput = document.getElementById('amount');
            const installmentAmountInput = document.getElementById('installment-amount');
            const totalInstallmentsInput = document.getElementById('total-installments');

            // Function to calculate total installments
            function calculateTotalInstallments() {
                const amount = parseFloat(amountInput.value) || 0; // Get amount value, default to 0 if empty
                const installmentAmount = parseFloat(installmentAmountInput.value) ||
                    0; // Get installment amount, default to 0 if empty

                if (installmentAmount > 0) {
                    const totalInstallments = Math.ceil(amount / installmentAmount); // Calculate and round up
                    totalInstallmentsInput.value = totalInstallments; // Set the value of total installments
                } else {
                    totalInstallmentsInput.value = 0; // Default to 0 if installment amount is invalid
                }
            }

            // Add event listeners to trigger calculation on input change
            amountInput.addEventListener('input', calculateTotalInstallments);
            installmentAmountInput.addEventListener('input', calculateTotalInstallments);
        });
    </script>

    <script>
        let currentStatus = null;

        function handleStatusChange(select) {
            currentStatus = select.value;
            $('#employeeDropdownContainer').html(''); // Clear previous dropdown
            $('#search').val(''); // Reset search input
            $('#search').prop('disabled', !currentStatus); // Enable/disable search
        }

        function handleEmployeeSearch(query) {
            if (!currentStatus || !query) {
                $('#employeeDropdownContainer').html('');
                return;
            }

            $.ajax({
                url: "{{ route('employee.search') }}",
                type: "POST",
                data: {
                    status: currentStatus,
                    query: query,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.length > 0) {
                        let options =
                            '<select class="form-control" id="employeeId"><option value="">Select Employee</option>';
                        response.forEach(emp => {
                            options += `<option value="${emp.emp_id}">${emp.emp_full_name}</option>`;
                        });
                        options += '</select>';
                        $('#employeeDropdownContainer').html(options);


                    } else {
                        $('#employeeDropdownContainer').html('<div class="text-muted">No employee found</div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error: ', error);
                }
            });
        }

        // Live search
        $('#search').on('keyup', function() {
            const query = $(this).val().trim();
            handleEmployeeSearch(query);
        });
        $(document).on('click', '.edit-loan-advance', function() {
            var pla_id = $(this).data('id');
            var type = $(this).data('type');
            var title = $(this).data('title');
            var employee = $(this).data('employee');
            var amount = $(this).data('amount');
            var provided_date = $(this).data('provided_date');
            var description = $(this).data('description');
            var installment_amount = $(this).data('installment_amount');
            var installments = $(this).data('installments');
            var installment_start_date = $(this).data('installment_start_date');

            if (installment_start_date) {
                installment_start_date = installment_start_date.split(' ')[0];
            }
            if (provided_date) {
                provided_date = provided_date.split(' ')[0];
            }

            $('#pla_id').val(pla_id);
            $('#type').val(type).trigger('change');
            $('#title').val(title);
            $('#employee').val(employee).trigger('change');
            $('#amount').val(amount);
            $('#provided-date').val(provided_date);
            $('#description').val(description);
            $('#installment-amount').val(installment_amount);
            $('#total-installments').val(installments);
            $('#installment-start').val(installment_start_date);

            $('#loanAdvanceModal').modal('show');
            $('#loanAdvanceModalTitle').text('Edit Loan / Advance');
            $('.select2').select2();
        });
    </script>

    <script>
        $(function() {
            // CSRF Token Setup (for all AJAX requests)
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // // Handle Delete Loan Advance
            $(document).on('click', '.deleteloanAdvanceBtn', handleDeleteLoanAdvance);
        });

        $(document).ready(function() {

            // Create or Update Loan Advance
            $('#loanAdvanceForm').on('submit', function(e) {
                e.preventDefault();
                let selectedEmployeeId = $('#employeeId').val();
                let formData = $(this).serializeArray();
                formData.push({
                    name: 'employeeId',
                    value: selectedEmployeeId
                });


                $.ajax({
                    url: "{{ url('payroll/loan/create') }}",
                    method: "POST",
                    data: formData,
                    beforeSend: function() {
                        $('#saveBtn').attr('disabled', true);
                    },
                    success: function(response) {
                        if (response.status == true) {
                            $('#loanAdvanceModal').modal('hide'); // Close the modal
                            Swal.fire({
                                icon: 'success',
                                text: response.message,
                                timer: 3000,
                            });
                            location.reload(); // Reload the page on success
                        }
                        if (response.status == false) {
                            // console.log(response.error);
                            $('#employeeError')
                                .text('Select employee')
                                .show();
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
                        // Log error details for debugging
                        console.error('AJAX Error: ', error);
                        Swal.fire({
                            icon: 'error',
                            text: 'Something went wrong!',
                            timer: 3000,
                        });
                        $('#saveBtn').attr('disabled', false);
                    }
                });

            });

            $(document).on('click', '#addloanAdvanceBtn', function() {
                console.log('this is ', $('#title').val());
                $('#loanAdvanceModalTitle').html('Add Loan Advance');
                $('#title').val('');
                // $('#saveBtn').html('Save');
                $('#pla_id').val('');
                $('#type').val('').trigger('change');
                $('#employee').val('').trigger('change');
                $('#amount').val('');
                $('#provided-date').val('');
                $('#description').val('');
                $('#installment-amount').val('');
                $('#total-installments').val('');
                $('#installment-start').val('');
                $('#addloanAdvanceBtn').modal('show');
                $('.select2').select2();
            });
        });

        // Handle Delete Shift Type
        function handleDeleteLoanAdvance() {
            var id = $(this).data('pla_id');
            Swal.fire({
                title: 'Are you sure?',
                text: 'You will not be able to recover this shift policy!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, keep it'
            }).then((result) => {
                if (result.isConfirmed) {
                    var url = "{{ route('loan-delete', ':id') }}".replace(':id', id);
                    // console.log('this is data',url);
                    // return false;
                    $.ajax({
                        url: url,
                        method: "DELETE",
                        success: function(response) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: response.success,
                                icon: 'success',
                                timer: 3000,
                                timerProgressBar: true,
                                showConfirmButton: false,
                                didClose: () => {
                                    location.reload(); // Reload the page after deletion
                                }
                            });
                        },
                        error: function(xhr) {
                            var errorMessage =
                                'An error occurred while deleting the shift policy. Please try again.';

                            // Check if the response contains an error message from the server
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
    </script>
@endsection
