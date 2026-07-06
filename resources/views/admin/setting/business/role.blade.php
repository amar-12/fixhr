@extends('admin.layout.master')

@section('title', 'Role Settings')

@section('content')
    <div class="p-0 mt-3">
        <div class="row">
            <div class="col-md-4">
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                    <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ url('admin/settings/account') }}">Account Settings</a></li>
                    <li class="active"><span><b>Role Settings</b></span></li>
                </ol>
            </div>
            <div class="col-md-6"></div>
            <div class="col-md-2">
                <div class="page-rightheader ms-md-auto">
                    <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                        <div class="d-lg-flex d-block ms-auto">
                            <div class="btn-list">
                                <button type="button" class="btn btn-outline-primary" id="createRoleBtn" data-bs-toggle="modal"
                                data-bs-target="#createRoleModal">Create Role</button>

                                <button type="button" class="btn btn-outline-primary" id="createNewUserRoleBtn" data-bs-toggle="modal"
                                data-bs-target="#createNewUserRoleModal">New User Role</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="row mt-5">
        <div class="col-xl-12 col-md-12 col-lg-12">
            <div class="card">
                <div class="card-header border-0">
                    <h4 class="card-title">Role </h4>
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

                            <div class="col-sm-1" style="margin-top: 30px;">
                                <div class="form-group filter_dots">
                                    <button class="btn btn-info" type="button" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="fa fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu p-2" aria-labelledby="actionDropdown"
                                        style="min-width: 220px;">
                                        <li>
                                            <a class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2"
                                                data-bs-toggle="modal" data-bs-target="#roleBulkUpload">
                                                <i class="las la-file-upload"></i> Upload File
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-warning fw-semibold d-flex align-items-center gap-2"
                                                href="{{ route('business.role.downloadExcel') }}">
                                                <i class="las la-file-download"></i> Export Format
                                            </a>
                                        </li>
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
                        <table class="table display table-hover table-vcenter text-wrap border-bottom" id="designation-table-dynamic">
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

    @if (session('import_errors_blade'))
        <div class="alert d-flex align-items-center mt-3">
            <p>There were errors in the import. You can download the error file from the link below:</p>
            <a href="{{ route('employee.downloadErrorFile') }}" onclick="location.reload()"
                class="ms-2 mb-4 btn btn-danger">Download Error File</a>
        </div>
    @endif

    <div class="modal fade" id="roleBulkUpload" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content tx-size-sm">
                <div class="modal-header border-0">
                    <h4 class="modal-title ms-2" id="modal-title">Upload Role</h4>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <form action="{{ route('business.role.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Upload File :</label>
                                    <input type="file" name="import_file" id="import_file" class="form-control"
                                        required accept=".xlsx, .csv">
                                    <br>
                                    <div style="display: flex; align-items: center;">
                                        <p class="fw-bold" style="margin: 0;">Note -</p>
                                        <p style="margin: 0; margin-left: 5px;">Only upload xlsx, csv files</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-end">
                        <button type="reset" class="btn btn-danger cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-outline-primary savebtn" id="saveUptBtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Role Creation Modal --}}
    <div class="modal fade" id="createRoleModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content tx-size-sm">
                <div class="modal-header border-0">
                    <h4 class="modal-title">Create Role</h4>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="addRoleFormId" action="{{ route('add.role') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="role" class="form-label">Role Name <span class="text-dagner">*</span></label>
                            <input id="roleID" name="role" type="text" class="form-control"
                                placeholder="Enter Role Name" required>
                            <span class="text-danger" id="role-error"></span>
                            @if ($errors->has('role'))
                                <span class="text-danger">{{ $errors->first('role') }}</span>
                            @endif
                        </div>
                        <div class="form-group">
                            <label for="description" class="form-label">Role Description <span class="text-dagner">*</span></label>
                            <input id="descriptionID" name="description" type="text" class="form-control"
                                placeholder="Enter Role Description" required>
                            <span class="text-danger" id="description-error"></span>

                            @if ($errors->has('description'))
                                <span class="text-danger">{{ $errors->first('description') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-end">
                        <button type="button" class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-outline-primary savebtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: New User Role -->
    <div class="modal fade" id="createNewUserRoleModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content tx-size-sm">
                <div class="modal-header border-0">
                    <h4 class="modal-title">New User Role</h4>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form id="addNewUserRoleFormId" action="{{ route('admin.newuser.role') }}" method="POST">
                    @csrf
                    <input type="hidden" name="nur_id" id="newUserRoleId">

                    <div class="modal-body">
                        <!-- Role Name -->
                        <div class="form-group mb-3">
                            <label for="roleIDNew" class="form-label">Role Name <span class="text-danger">*</span></label>
                            <input id="roleIDNew" name="role" type="text" class="form-control" placeholder="Enter Role Name" required>
                            <span class="text-danger" id="new-role-error"></span>
                        </div>

                        <!-- Role Description -->
                        <div class="form-group mb-3">
                            <label for="descriptionIDNew" class="form-label">Role Description <span class="text-danger">*</span></label>
                            <input id="descriptionIDNew" name="description" type="text" class="form-control" placeholder="Enter Role Description" required>
                            <span class="text-danger" id="new-description-error"></span>
                        </div>

                        <!-- Email -->
                        <div class="form-group mb-3">
                            <label for="emailID" class="form-label">Email <span class="text-danger">*</span></label>
                            <input id="emailID" name="email" type="email" class="form-control" placeholder="Enter Email Address" required>
                            <span class="text-danger" id="email-error"></span>
                        </div>

                        <!-- Password -->
                        <div class="form-group mb-3">
                            <label for="passwordID" class="form-label">Password <span class="text-danger">*</span></label>
                            <input id="passwordID" name="password" type="password" class="form-control" placeholder="Enter Password" minlength="8" maxlength="20" 
                                   pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,20}$" 
                                   title="Password must be 8-20 characters long, and include at least one uppercase letter, one lowercase letter, one number, and one special character.">
                            <span class="text-danger" id="password-error"></span>
                        </div>
                    </div>

                    <div class="modal-footer d-flex justify-content-end">
                        <button type="button" class="btn btn-outline-danger cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-outline-primary savebtn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Role Modal --}}
    <div class="modal fade" id="updateRoleModal" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Edit Role</h6>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="updateRoleFormId" action="{{ route('update.role') }}">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="editId" name="editid">
                        <div class="form-group">
                            <label for="editRole" class="form-label">Role Name</label>
                            <input id="editRole" name="edit_role_name" type="text" class="form-control"
                                placeholder="Enter Role Name" required>
                            <span class="text-danger" id="role-update-error"></span>

                        </div>
                        <div class="form-group">
                            <label for="editRole" class="form-label">Role Description</label>
                            <input id="editRoleDescription" name="edit_role_description" type="text"
                                class="form-control" placeholder="Enter Role Description" required>
                            <span class="text-danger" id="description-update-error"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-outline-primary savebtn">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Delete Role Modal --}}
    <div class="modal fade" id="deleteRoleModal" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Deletion</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <h4 class="mt-5">Are you sure you want to delete <span id="deletedID" class="text-primary"></span>
                        role?</h4>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Close</button>
                    <form id="deleteRoleFormId" method="POST" action="{{ route('delete.role') }}">
                        @csrf
                        <input type="hidden" name="deleteId" id="deleteId">
                        <button type="submit" class="btn btn-outline-danger ">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script>
        $(document).ready(function() {
            datatable({
                tableId: "designation-table-dynamic",
                url: "{{ route('admin.role') }}",
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

        $('#roleID').on('input', function() {
            $('#role-error').html('');
        });

        $('#descriptionID').on('input', function() {
            $('#description-error').html('');
        });

        $('#editRole').on('input', function() {
            $('#role-update-error').html('');
        });

        $('#editRoleDescription').on('input', function() {
            $('#description-update-error').html('');
        });

        document.getElementById('createRoleBtn').addEventListener('click', function() {
            document.getElementById('addRoleFormId').reset();
            $('#role-error').html('');
        });

        function openEditRole(context) {
            $('#role-update-error').html('');
            const id = context.dataset.id;
            const role_name = context.dataset.role_name;
            const role_description = context.dataset.role_description;
            document.getElementById('editId').value = id;
            document.getElementById('editRole').value = role_name;
            document.getElementById('editRoleDescription').value = role_description;
            new bootstrap.Modal(document.getElementById('updateRoleModal')).show();
        }

        function openDeleteRole(context) {
            const id = context.dataset.id;
            const role_name = context.dataset.role_name;
            document.getElementById('deleteId').value = id;
            document.getElementById('deletedID').textContent = role_name;
            new bootstrap.Modal(document.getElementById('deleteRoleModal')).show();
        }

        $('#addRoleFormId').submit(function(e) {
            e.preventDefault();

            var url = $(this).attr("action");
            let formData = new FormData(this);

            $.ajax({
                type: 'POST',
                url: url,
                data: formData,
                contentType: false,
                processData: false,
                success: (response) => {
                    // alert('Form submitted successfully');
                    if (response.success) {
                        $('#createRoleModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            text: response.success,
                            timer: 3000,
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            text: response.error,
                            timer: 3000,
                        });
                    }
                },
                error: function(response) {
                    var errors = response.responseJSON.errors;
                    // Display error messages below input fields
                    if (errors.role) {
                        $('#role-error').text(errors.role[0]);
                    }
                    if (errors.description) {
                        $('#description-error').text(errors.description[0]);
                    }
                }
            });
        });

        $('#deleteRoleFormId').submit(function(e) {
            e.preventDefault();

            var url = $(this).attr('action');
            let formData = new FormData(this);

            $.ajax({
                type: 'POST',
                url: url,
                data: formData,
                contentType: false,
                processData: false,
                success: (response) => {
                    $('#deleteRoleModal').modal('hide');
                    if (response.success) {
                        $('#deleteRoleModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            text: response.success,
                            timer: 3000,
                            showConfirmButton: false,
                            allowOutsideClick: false,
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            text: response.error,
                            timer: 3000,
                            showConfirmButton: false,
                            allowOutsideClick: false,
                        });
                    }
                },
                error: function(response) {
                    // Handle validation errors and other errors
                    $('#deleteRoleModal').modal('hide');
                    if (response.status === 400 || response.status === 500) {
                        // For 400 errors, display the error message in a SweetAlert
                        Swal.fire({
                            icon: 'error',
                            text: response.responseJSON
                                .error, // Assuming the error message is in 'error'
                            timer: 3000,
                            showConfirmButton: false,
                            allowOutsideClick: false,
                        });
                    } else {
                        // Handle any other unexpected errors
                        Swal.fire({
                            icon: 'error',
                            text: 'Something went wrong. Please try again later.',
                            timer: 3000,
                            showConfirmButton: false,
                            allowOutsideClick: false,
                        });
                    }

                    // Optionally display errors next to form fields
                    var errors = response.responseJSON.errors;
                    if (errors && errors.deleteId) {
                        $('#role-error').text(errors.deleteId[0]);
                    }
                }
            });
        });

        $('#updateRoleFormId').submit(function(e) {
            e.preventDefault();

            var url = $(this).attr("action");
            let formData = new FormData(this);

            $.ajax({
                type: 'POST',
                url: url,
                data: formData,
                contentType: false,
                processData: false,
                success: (response) => {
                    if (response.success) {
                        $('#updateRoleModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            text: response.success,
                            timer: 3000,
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            text: response.error,
                            timer: 3000,
                        });
                    }
                },
                error: function(response) {
                    var errors = response.responseJSON.errors;
                    // Display error messages below input fields
                    if (errors.edit_role_name) {
                        $('#role-update-error').text(errors.edit_role_name[0]);
                    }
                    if (errors.edit_role_description) {
                        $('#description-update-error').text(errors.edit_role_description[0]);
                    }
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Success message
            @if (session('success'))
                Swal.fire({
                    position: 'top-end',
                    icon: 'success',
                    title: '{{ session('success') }}',
                    toast: true,
                    showConfirmButton: false,
                    timer: 5000,
                    timerProgressBar: true,
                    customClass: {
                        toast: 'swal2-toast-green-glow'
                    },
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer);
                        toast.addEventListener('mouseleave', Swal.resumeTimer);
                    }
                });
            @endif

            // Error message
            @if (session('error'))
                Swal.fire({
                    position: 'top-end',
                    icon: 'error',
                    title: '{{ session('error') }}',
                    toast: true,
                    showConfirmButton: false,
                    timer: 5000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer);
                        toast.addEventListener('mouseleave', Swal.resumeTimer);
                    }
                });
            @endif

            // Error messages (if multiple validation errors or custom messages are passed)
            @if ($errors->any())
                let errorMessages = '';
                @foreach ($errors->all() as $error)
                    errorMessages += '{{ $error }}' + '<br>';
                @endforeach
                Swal.fire({
                    position: 'top-end',
                    icon: 'error',
                    title: 'Validation Errors',
                    html: errorMessages, // Display the list of errors
                    toast: true,
                    showConfirmButton: false,
                    timer: 5000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer);
                        toast.addEventListener('mouseleave', Swal.resumeTimer);
                    }
                });
            @endif
        });
    </script>

    <script>
        // Open modal for adding
        function openCreateUserRoleModal() {
            $('#addNewUserRoleFormId')[0].reset(); // Clear form
            $('#newUserRoleId').val(''); // Ensure id is empty
            $('#createNewUserRoleModal .modal-title').text('New User Role');
            $('#createNewUserRoleModal').modal('show');
        }

        // Open modal for editing (pass role object)
        function openEditUserRoleModal(data) {
            $('#newUserRoleId').val(data.nur_id);
            $('#roleIDNew').val(data.nur_name);
            $('#descriptionIDNew').val(data.nur_description);
            $('#emailID').val(data.nur_email);
            $('#passwordID').val('');

            $('#createNewUserRoleModal .modal-title').text('Edit User Role');
            $('#createNewUserRoleModal').modal('show');
        }

        // Handle AJAX form submission
        $('#addNewUserRoleFormId').submit(function (e) {
            e.preventDefault();

            let url = $(this).attr("action");
            let formData = new FormData(this);

            $.ajax({
                type: 'POST',
                url: url,
                data: formData,
                contentType: false,
                processData: false,
                success: (response) => {
                    if (response.status === 'success') {
                        $('#createNewUserRoleModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            timer: 3000
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.error,
                            timer: 3000
                        });
                    }
                },
                error: (xhr) => {
                    const errors = xhr.responseJSON.errors;
                    if (errors) {
                        $('#new-role-error').text(errors.role ? errors.role[0] : '');
                        $('#new-description-error').text(errors.description ? errors.description[0] : '');
                        $('#email-error').text(errors.email ? errors.email[0] : '');
                        $('#password-error').text(errors.password ? errors.password[0] : '');
                    }
                }
            });
        });
    </script>

@endsection
