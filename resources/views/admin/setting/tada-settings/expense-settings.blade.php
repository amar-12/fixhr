@extends('admin.layout.master')

@section('title', 'Expense Settings')

@section('css')
    <style>
        .disable-alt {
            background-color: #eee !important;
            pointer-events: none !important;
        }

        .select2-container.read-only .select2-selection {
            pointer-events: none;
            background-color: #eee;
        }
    </style>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            datatable({
                tableId: "policy-category-table-dynamic",
                url: "{{ route('get.expense-setting') }}",
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

        function openAddExpenseDetails() {
            $('#editId').val('');
            $('#code').val('');
            $('#name').val('');
            $('#expense_type option').removeAttr('selected');
            $('#modal-title').html('Add Expense');

            // Reset and trigger change for Select2
            $('#expense_type').val(null).trigger('change');
            $('.select2').select2();
        }

        function openEditExpenseDetails(e) {
            // Unselect all items in the role select dropdown
            // $('select.sumo_search')[0].sumo.unSelectAll();
            // Set the modal title
            $('#modal-title').html('Edit Expense');

            // Get data attributes from the clicked element
            var id = $(e).data('id');
            var code = $(e).data('code');
            var name = $(e).data('name');
            var expense_type = $(e).data('type_id');
            var expense_type_name = $(e).data('type_name');
            var isFixed = $(e).data('is_fixed');
            var fixed_amount = $(e).data('fixed_amount');

            $('#editId').val(id);
            $('#code').val(code);
            $('#name').val(name);
            $('#expense_type').val(expense_type).trigger('change');
            $('#isFixed').prop('checked', isFixed);
            $('#fixedAmount').val(fixed_amount);

            if (isFixed) {
                $('#fixedAmountContainer').css('display', 'block');
                $('#fixedAmount').prop('required', true);
                $('#hidden_isFixed').val('1');
            } else {
                $('#fixedAmountContainer').css('display', 'none');
                $('#fixedAmount').prop('required', false);
                $('#fixedAmount').val(0);
                $('#hidden_isFixed').val('0');
            }
            // Optional: If you're using another select2 dropdown
            $('.select2').select2();
        }
    </script>
@endsection

@section('content')
{{-- Bradcrumbs Start --}}
<div class="p-0 mt-3">
    <div class="row">
        <div class="col-md-4">
            <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li><a href="{{ url('admin/settings/tada-settings') }}">TA & DA Settings</a></li>
                <li class="active"><span><b>Expense Settings</b></span></li>
            </ol>
        </div>
        <div class="col-md-6"></div>
        <div class="col-md-2">
            <div class="page-rightheader ms-md-auto">
                <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                    <div class="d-lg-flex d-block ms-auto">
                        <div class="btn-list">
                            <a class="btn btn-outline-primary" data-bs-toggle="modal" onclick="openAddExpenseDetails();"
                            data-bs-target="#addExpenseDetailsModal">Add Expense</a>                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- Bradcrumbs End --}}



    <div class="card mt-5">
        <div class="card-header d-flex">
            <div>
                <h4 class="card-title"><span>Expense Settings</span></h4>
            </div>
            {{-- <div class="ms-auto">
                <button class="btn text-white btn-info btn-sm" id="addPolicyCategoryFieldBtn"><i class="fe fe-plus bold"></i></button>
            </div> --}}
        </div>

        <div class="card-body">

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

                <div class="table-responsive">
                    <table class="table display table-hover table-vcenter text-wrap border-bottom" id="policy-category-table-dynamic">
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

    <div class="modal fade" id="addExpenseDetailsModal" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content tx-size-sm">
                <div class="modal-header border-0">
                    <h4 class="modal-title ms-2" id="modal-title">Add Expense</h4>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <form id="addExpenseDetailsForm" method="POST">@csrf
                    <div class="modal-body">
                        <div class="row">
                            <input type="text" id="editId" name="editExpenseDetails" hidden>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label"> Expense Type <span class="text-danger">*</span></label>
                                    <select name="expense_type" id="expense_type"
                                        class="form-control custom-select select2 expense_type"
                                        data-placeholder="Select Expense Type" required>
                                        <option label="Select Expense Type"></option>
                                        @foreach ($expense as $expense_type)
                                            <option value="{{ $expense_type->m_id }}">{{ $expense_type->m_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Code <span class="text-danger">*</span></label>
                                    <input type="number" name="code" id="code" class="form-control CategoryName"
                                        placeholder="Enter Code" required min="0"
                                        oninput="this.value = this.value < 0 ? 0 : this.value; this.value = this.value.slice(0, 10);">
                                </div>
                            </div>

                            <div class="col-md-5">
                                <div class="form-group">
                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name"
                                        class="form-control CategoryName" value="" placeholder="Enter Name"
                                        required maxlength="255" oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '')">
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label">Is Fixed Amount</label>
                                    <input type="checkbox" id="isFixed" name="is_fixed" class="form-check-input" value="1" onclick="toggleFixedAmount()">
                                    <input type="hidden" name="is_fixed" id="hidden_isFixed" value="0">
                                </div>
                            </div>

                            <div class="col-md-5" id="fixedAmountContainer" style="display: none;">
                                <div class="form-group">
                                    <label class="form-label">Fixed Amount</label>
                                    <input type="text" name="fixed_amount" id="fixedAmount"
                                        class="form-control" placeholder="Enter Fixed Amount" value="0"
                                        maxlength="9" min="0.01" oninput=" validateFloatInput(this);">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-end">
                        <button type="reset" class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-outline-primary savebtn" id="saveBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        $('#addExpenseDetailsForm').submit(function(event) {
            event.preventDefault();

            if (!$('#isFixed').is(':checked')) {
                // Set the value of the checkbox to 0 if unchecked
                $('#isFixed').val('0');
            } else {
                // Set the value to 1 if checked
                $('#isFixed').val('1');
            }
            let data = new FormData(this);

            $.ajax({
                url: '{{ route('store.update.expense-setting') }}',
                method: 'POST',
                data: data,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    $('#saveBtn').attr('disabled', 'disabled');
                },
                success: function(response) {
                    if (response.status) {
                        Swal.fire({
                            icon: 'success',
                            text: response.message,
                            timer: 3000,
                            showConfirmButton: false
                        });
                        $('#addExpenseDetailsModal').modal('hide');
                        // Optionally, reload the table or page to reflect changes
                        window.location.href = '{{ route('get.expense-setting') }}';
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            text: response.message,
                            timer: 3000,
                            showConfirmButton: false
                        });
                    }
                    $('#saveBtn').attr('disabled', false);
                },
                error: function(xhr, status, error) {
                    var response = JSON.parse(xhr.responseText);
                    var errorMessage = response.errors ? response.errors.join('\n') :
                        'An error occurred';
                    Swal.fire({
                        icon: 'error',
                        text: errorMessage,
                        timer: 3000,
                        showConfirmButton: false
                    });
                    $('#saveBtn').attr('disabled', false);
                }
            });
        });

        function validateFloatInput(input) {
            // Ensure the input only contains valid float numbers up to 2 decimal places
            input.value = input.value.match(/^\d*(\.\d{0,2})?$/) ? input.value : input.value.slice(0, -1);
        }

        function toggleFixedAmount() {
            const fixedAmountContainer = $('#fixedAmountContainer');
            const isFixedCheckbox = $('#isFixed');
            const fixedAmountInput = $('#fixedAmount');

            if (isFixedCheckbox.is(':checked')) {
                fixedAmountContainer.css('display', 'block');
                fixedAmountInput.prop('required', true);
                $('#hidden_isFixed').val('1');
            } else {
                fixedAmountContainer.css('display', 'none');
                fixedAmountInput.prop('required', false);
                fixedAmountInput.val(0);
                $('#isFixed').val(0);
                $('#hidden_isFixed').val('0');
            }
        }

        $(document).ready(function() {
            // Initialize Select2 globally for elements with the class 'select2'
            $('.select2').select2();

            // Reinitialize Select2 when the modal is shown
            $('#addExpenseDetailsModal').on('shown.bs.modal', function() {
                // Destroy existing Select2 instance if it exists
                $('.select2').each(function() {
                    if ($(this).data('select2')) {
                        $(this).select2('destroy');
                    }
                });

                // Initialize Select2 again within the modal
                $('.select2').select2({
                    dropdownParent: $('#addExpenseDetailsModal')
                });
            });
        });


        $(function() {
            // CSRF Token Setup (for all AJAX requests)
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Handle Delete Salary Allowance
            $(document).on('click', '.delete-expense-setting', handleDeleteExpenseSetting);

        });


        // Handle Delete Shift Type
        function handleDeleteExpenseSetting() {
            var id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: 'You will not be able to recover this salary allowance!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, keep it'
            }).then((result) => {
                if (result.isConfirmed) {
                    var url = "{{ route('delete.expense-setting', ':id') }}".replace(':id', id);
                    $.ajax({
                        url: url,
                        method: "POST",
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
                                'An error occurred while deleting the salary allowance. Please try again.';

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
