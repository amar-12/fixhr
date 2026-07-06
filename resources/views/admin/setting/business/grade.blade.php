@extends('admin.layout.master')

@section('title', 'Grade Settings')

@section('content')
    <div class="p-0 mt-3">
        <div class="row">
            <div class="col-md-4">
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                    <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ url('admin/settings/account') }}">Account Settings</a></li>
                    <li class="active"><span><b>Grade Settings</b></span></li>
                </ol>
            </div>
            <div class="col-md-6"></div>
            <div class="col-md-2">
                <div class="page-rightheader ms-md-auto">
                    <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                        <div class="d-lg-flex d-block ms-auto">
                            <div class="btn-list">
                                <button type="button" class="btn btn-outline-primary" id="createGradeBtn" data-bs-toggle="modal"
                                data-bs-target="#createGradeModal">Create Grade</button>
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
                    <h4 class="card-title">Grade </h4>
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
                                                data-bs-toggle="modal" data-bs-target="#gradeBulkUpload">
                                                <i class="las la-file-upload"></i> Upload File
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-warning fw-semibold d-flex align-items-center gap-2"
                                                href="{{ route('business.grade.downloadExcel') }}">
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

    <div class="modal fade" id="gradeBulkUpload" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content tx-size-sm">
                <div class="modal-header border-0">
                    <h4 class="modal-title ms-2" id="modal-title">Upload Grade</h4>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal"><span
                            aria-hidden="true">&times;</span></button>
                </div>
                <form action="{{ route('business.grade.import') }}" method="POST" enctype="multipart/form-data">
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

    {{-- Grade Creation Modal --}}
    <div class="modal fade" id="createGradeModal" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content tx-size-sm">
                <div class="modal-header border-0">
                    <h4 class="modal-title">Create Grade</h4>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('add.grade') }}" id="addGradeFormId">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="grade" class="form-label">Grade Name <span class="text-dagner">*</span></label>
                            <input id="grade" name="grade" type="text" class="form-control"
                                placeholder="Enter Grade Name" required>
                            <span class="text-danger" id="grade-error"></span>
                            <p class="mb-0 pb-0 text-muted fs-12 mt-5">By continuing you agree to <a href="#"
                                    class="text-primary">Terms & Conditions</a></p>
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

    {{-- Edit Grade Modal --}}
    <div class="modal fade" id="updateGradeModal" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-demo">
                <div class="modal-header">
                    <h6 class="modal-title">Edit Grade</h6>
                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="updateGradeFormId" action="{{ route('update.grade') }}">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="editId" name="editid">
                        <div class="form-group">
                            <label for="editGrade" class="form-label">Grade Name</label>
                            <input id="editGrade" name="edit_grade_name" type="text" class="form-control"
                                placeholder="Enter Grade Name" required>
                            <span class="text-danger" id="update-grade-error"></span>
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

    {{-- Delete Grade Modal --}}
    <div class="modal fade" id="deleteGradeModal" data-bs-backdrop="static">
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
                        grade?</h4>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Close</button>
                    <form method="POST" action="{{ route('delete.grade') }}">
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
                url: "{{ route('admin.grade') }}",
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


        $('#grade').on('input', function() {
            $('#grade-error').html('');
        });
        $('#editGrade').on('input', function() {
            $('#update-grade-error').html('');
        });

        document.getElementById('createGradeBtn').addEventListener('click', function() {
            document.getElementById('addGradeFormId').reset();
            $('#grade-error').html('');
        });

        function openEditGrade(context) {
            $('#update-grade-error').html();
            const id = context.dataset.id;
            const g_name = context.dataset.g_name;
            document.getElementById('editId').value = id;
            document.getElementById('editGrade').value = g_name;
            new bootstrap.Modal(document.getElementById('updateGradeModal')).show();
        }

        function openDeleteGrade(context) {
            const id = context.dataset.id;
            const g_name = context.dataset.g_name;
            document.getElementById('deleteId').value = id;
            document.getElementById('deletedID').textContent = g_name;
            new bootstrap.Modal(document.getElementById('deleteGradeModal')).show();
        }

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

        $('#addGradeFormId').submit(function(e) {
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
                        $('#createGradeModal').modal('hide');
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
                    if (errors.grade) {
                        $('#grade-error').text(errors.grade[0]);
                    }

                }
            });
        });
        $('#updateGradeFormId').submit(function(e) {
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
                        $('#updateGradeModal').modal('hide');
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
                    location.reload();
                },
                error: function(response) {
                    var errors = response.responseJSON.errors;
                    // Display error messages below input fields
                    if (errors.edit_grade_name) {
                        $('#update-grade-error').text(errors.edit_grade_name[0]);
                    }
                }
            });
        });
    </script>
@endsection
