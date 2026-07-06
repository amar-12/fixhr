@extends('admin.layout.master')
@section('title')
    {{ $pageTitle }}
@endsection
@section('css')
@endsection


<style>
    .emp-id-exists {
        border-color: red;
        color: red;
    }

    .message-exists {
        color: red;
    }

    /* #btnXyz:hover {
        color: #fff
    } */

    table td {
        padding: 0;
    }
</style>


@section('content')
    <x-breadcrumb :breadcrumbs="$breadcrumbs" />
    <div class="mt-5">

        <!-- ROW -->
        <div class="row mt-5">
            <div class="col-xl-12 col-md-12 col-lg-12">
                <div class="card">
                    <div class="card-header border-0">
                        <h4 class="card-title">Employee List</h4>
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
                            <table class="table display table-vcenter text-wrap border-bottom" id="employee-table-dynamic">
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

        <!-- Modal -->
        <div class="modal fade" id="editPasswordModal" data-bs-backdrop="static" tabindex="-1"
            aria-labelledby="editPasswordModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content modal-content-demo">
                    <div class="modal-header">
                        <h6 class="modal-title">Update Employee Password</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="updatePasswordForm">
                        @csrf
                        <input type="hidden" id="empId" name="emp_id">
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="empName" class="form-label">Employee Name</label>
                                <input type="text" class="form-control" id="empName" readonly>
                            </div>

                            <div class="form-group position-relative">
                                <label for="newPassword" class="form-label">New Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="newPassword" name="new_password"
                                        pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@$!%*?&]).{8,}"
                                        title="Must contain at least one number, one uppercase and lowercase letter, one special character, and be at least 8 characters long."
                                        required>

                                    <button type="button" class="btn btn-outline-primary" id="togglePassword">
                                        <i class="fa fa-eye" id="eyeIcon"></i>
                                    </button>
                                </div>
                            </div>


                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-outline-primary savebtn">Update Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>




    </div>
@endsection
<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize DataTable
        datatable({
            tableId: "employee-table-dynamic",
            url: "{{ url()->full() }}",
            dataLength: '[data-length]',
            dataSearch: '[data-search]',
            dataFilter: '[data-filter]',
            dataExport: '[data-export]',
            dataDateFilter: '[data-date-filter]',
            dataShowEntries: '[data-show-entries]',
            dataPagination: '[data-pagination]',
            dataStateSave: true
        });
    });
</script>
<!-- JavaScript to handle modal data -->
<script>
    function openEditPassword(context) {
        $('#empname-update-error').html('');
        const id = context.dataset.id;
        const emp_full_name = context.dataset.emp_full_name;
        document.getElementById('empId').value = id;
        document.getElementById('empName').value = emp_full_name;
        new bootstrap.Modal(document.getElementById('editPasswordModal')).show();
    }
</script>

<script>
    $(document).ready(function() {
        $('#updatePasswordForm').submit(function(e) {
            e.preventDefault(); // Prevent form from submitting normally

            let formData = $(this).serialize(); // Serialize form data

            $.ajax({
                url: "{{ route('update.employee.password') }}",
                type: "POST",
                data: formData,
                dataType: "json",
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            position: 'center',
                            showConfirmButton: true, // OK button will be visible
                            allowOutsideClick: false // Prevent closing by clicking outside
                        }).then(() => {
                            $('#editPasswordModal').modal(
                            'hide'); // Close modal after clicking OK
                            $('#updatePasswordForm')[0]
                        .reset(); // Reset form fields
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message,
                            position: 'center',
                            showConfirmButton: true, // OK button required
                            allowOutsideClick: false
                        });
                    }
                },
                error: function(xhr) {
                    let errors = xhr.responseJSON?.errors;
                    if (errors) {
                        let errorMsg = Object.values(errors).flat().join('\n');
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error!',
                            text: errorMsg,
                            position: 'center',
                            showConfirmButton: true, // OK button required
                            allowOutsideClick: false
                        });
                    }
                }
            });
        });
    });
</script>

<script>
    $(document).ready(function() {
        $('#togglePassword').click(function() {
            let passwordField = $('#newPassword');
            let eyeIcon = $('#eyeIcon');

            if (passwordField.attr('type') === 'password') {
                passwordField.attr('type', 'text');
                eyeIcon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                passwordField.attr('type', 'password');
                eyeIcon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });
    });
</script>
