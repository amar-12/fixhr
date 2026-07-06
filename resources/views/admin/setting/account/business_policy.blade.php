@extends('admin.layout.master')
@section('title', 'Regulatory Document')
@section('css')
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
@endsection
@section('content')

    {{-- Breadcrumbs Start --}}
    <div class="p-0 mt-3">
        <div class="row">
            <div class="col-md-4">
                <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                    <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ url('/admin/settings/business') }}">Business Settings </a></li>
                    {{-- <li><a href="{{ url('/business-policy-folder') }}">Regulatory Folder</a></li> --}}
                    <li class="active"><span><b>Regulatory Document</b></span></li>
                </ol>
            </div>
            <div class="col-md-6"></div>
            <div class="col-md-2">
                <div class="page-rightheader ms-md-auto">
                    <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                        <div class="d-lg-flex d-block ms-auto">
                            <div class="btn-list">
                                <button type="button" class="btn btn-outline-primary"
                                    id="addBusinessPolicyBtn">Add</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Breadcrumbs End --}}

    <div class="row mt-5">
        <div class="col-xl-12 col-md-12 col-lg-12">
            <div class="card">

                <div class="card-header border-0">
                    <h4 class="card-title">Regulatory Document</h4>
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




                        <div class="col-sm-5">
                        </div>

                        <div class="col-sm-2" style="margin-top: 10px;">
                            <div id="approval-buttons" class="d-flex justify-content-end gap-3 m-5">
                                <label class="custom-control custom-checkbox-md mx-3 d-flex align-items-center">
                                    Select All &nbsp;&nbsp;
                                    <input type="checkbox" id="selectAll" class="custom-control-input-success"
                                        name="example-checkbox1" value="option1" onclick="selectAllCheckboxes(this)">
                                    <span class="custom-control-label-md success"></span>
                                </label>
                            </div>
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
                                <button class="btn btn-info" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa fa-ellipsis-v"></i>
                                </button>

                                <ul class="dropdown-menu p-2" aria-labelledby="actionDropdown" style="min-width: 220px;">
                                    <li>
                                        <button
                                            class="dropdown-item text-success fw-semibold d-flex align-items-center gap-2"
                                            onclick="downloadSelectedEmployees()">
                                            <i class="fa fa-download"></i> Bulk Download
                                        </button>
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

                    <div class="">
                        <table class="table display table-hover table-vcenter text-wrap border-bottom"
                            id="business-policy-table-dynamic">
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


    <!-- Modal for Regulator Document -->
    <div class="modal fade" id="businessPolicyModal" tabindex="-1" aria-labelledby="businessPolicyModalTitle"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="businessPolicyModalTitle">Add Regulator Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                    <button aria-label="Close" class="btn-close" data-bs-dismiss="modal"><span
                            aria-hidden="true">&times;</span></button>
                </div>

                <form id="businessPolicyForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="bpd_id" id="bpd_id">
                        <input type="hidden" name="user_id" value="{{ $user_id }}">

                        {{-- <div class="row mb-3">
                            <x-input type="text" id="bpd_folder_name" label="Document Folder Name"
                                name="bpd_folder_name" placeholder="Enter Folder name" required astric="*" />
                        </div> --}}


                        <div class="row mb-3">
                            <div class="col-md-6">
                                <x-input type="text" id="bpd_file_name" label="Document Name" name="bpd_file_name"
                                    placeholder="Enter file name" required astric="*" />
                            </div>

                            <div class="col-md-6">
                                <x-input type="text" id="bpd_version" label="Document Version" name="bpd_version"
                                    placeholder="Enter version (max 10 chars)" maxlength="10" required astric="*" />
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <x-input type="date" id="bpd_with_effect_from" label="With Effect From"
                                    name="bpd_with_effect_from" required astric="*" />
                            </div>

                            <div class="col-md-6">
                           <div class="form-group">
                                <label for="bpd_file_path" class="form-label">Upload File <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="bpd_file_path" name="bpd_file_path" accept=".pdf, image/*">
                            </div>

                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="bpd_status" class="form-label">Status <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="bpd_status" name="bpd_status" required>
                                    <option value="1" selected>Active</option>
                                    <option value="0">Inactive</option>

                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" id="saveBtn" class="btn btn-outline-primary">Save Document</button>
                    </div>
                </form>

            </div>
        </div>
    </div>




@section('script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        var empIDs = [];

        // Individual checkbox handler
        function selectCheckboxUpdate(checkbox) {
            const empId = checkbox.value;
            const isChecked = $(checkbox).is(':checked');

            if (isChecked) {
                if (!empIDs.includes(empId)) {
                    empIDs.push(empId);
                }
            } else {
                empIDs = empIDs.filter(id => id != empId);
            }

            // Optional: Uncheck "Select All" if any checkbox is unchecked
            if (!isChecked) {
                $('#selectAll').prop('checked', false);
            }
        }

        // Select All handler
        function selectAllCheckboxes(element) {
            const isChecked = $(element).is(':checked');
            empIDs = []; // Reset

            $('.select-checkbox').each(function() {
                $(this).prop('checked', isChecked); // Check/uncheck
                const empId = $(this).val();
                if (isChecked) {
                    empIDs.push(empId);
                }
            });
        }

        // Bulk download logic
        function downloadSelectedEmployees() {
            if (!empIDs.length) {
                Swal.fire({
                    title: 'No Selection',
                    text: 'Please select at least one employee.',
                    icon: 'warning'
                });
                return;
            }

            $.ajax({
                url: '{{ route('documents.bulk_download') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    emp_ids: empIDs
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    const blob = new Blob([response], {
                        type: 'application/zip'
                    });
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = 'employee_files.zip';
                    link.click();
                    URL.revokeObjectURL(url);
                },
                error: function() {
                    Swal.fire({
                        title: 'Error',
                        text: 'Failed to download ZIP file.',
                        icon: 'error'
                    });
                }
            });
        }
    </script>

    <script>
        $(document).ready(function() {
            // Setup CSRF
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Init DataTable
            datatable({
                tableId: "business-policy-table-dynamic",
                url: "{{ url('admin/settings/regulatory/document', ['id' => $user_id]) }}",
                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataDateFilter: '[data-date-filter]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]',
                dataStateSave: false
            });


            // Open modal for Add
            $('#addBusinessPolicyBtn').on('click', function() {
                $('#businessPolicyForm')[0].reset();
                $('#bpd_id').val('');
                $('#bpd_folder_name').val('');
                $('#bpd_version').val('');
                $('#bpd_file_name').val('');
                $('#bpd_status').val('');
                $('#businessPolicyModalTitle').text('Add Regulator Document');
                $('#saveBtn').text('Save');
                $('#businessPolicyModal').modal('show');
            });


            // Submit form
            $('#businessPolicyForm').on('submit', function(e) {
                e.preventDefault();
                $('#saveBtn').prop('disabled', true);

                let formData = new FormData(this);

                $.ajax({
                    url: "{{ route('documents.store') }}", 
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#saveBtn').prop('disabled', false);
                        $('#businessPolicyModal').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message || 'Document saved successfully.',
                        });

                        $('#businessPolicyForm')[0].reset(); // optional: reset form
                        $('#business-policy-table-dynamic').DataTable().ajax
                            .reload(); // reload table
                    },
                    error: function(xhr) {
                        $('#saveBtn').prop('disabled', false);

                        // Fallback error message
                        let message = 'An unexpected error occurred.';

                        // Laravel validation errors
                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            const errors = xhr.responseJSON.errors;
                            message = Object.values(errors)
                                .map(err => `<p>${err[0]}</p>`)
                                .join('');
                        } else if (xhr.responseJSON?.message) {
                            message = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            html: message
                        });
                    }
                });
            });



            // Edit
                $(document).on('click', '.edit-document', function () {
                    const data = $(this).data();

                    $('#bpd_id').val(data.id);
                    $('#bpd_folder_name').val(data.folder);
                    $('#bpd_version').val(data.version);
                    $('#bpd_file_name').val(data.file_name);
                    const date = new Date(data.with_effect_from);
                    const formattedDate = date.toISOString().split('T')[0];
                    $('#bpd_with_effect_from').val(formattedDate);
                    $('#bpd_status').val(data.status);
                    $('#existingFileView').text(data.file_path || 'No file uploaded');
                    $('#businessPolicyModalTitle').text('Update Regulator Document');
                    $('#saveBtn').text('Update');
                    $('#businessPolicyModal').modal('show');
                });

            // Delete
            $(document).on('click', '.delete-document', function() {
                let id = $(this).data('id');

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This document will be deleted permanently.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let url = "{{ route('documents.destroy', ':id') }}".replace(':id',
                            id);

                        $.ajax({
                            url: url,
                            method: 'DELETE',
                            success: function(response) {
                                Swal.fire('Deleted!', response.success, 'success');
                                $('#business-policy-table-dynamic').DataTable().ajax
                                    .reload();
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
@endsection
