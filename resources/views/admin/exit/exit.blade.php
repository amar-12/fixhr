<?php
use App\Helpers\RolePermissionLogics;

$permission = new RolePermissionLogics();
?>
@extends('admin.layout.master')

@section('title', 'FNF Approval Flow')

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            // Initialize DataTable
            datatable({
                tableId: "approval-table-dynamic",
                url: "{{ route('admin.setting.index') }}",
                dataLength: '[data-length]',
                dataSearch: '[data-search]',
                dataFilter: '[data-filter]',
                dataExport: '[data-export]',
                dataDateFilter: '[data-date-filter]',
                dataShowEntries: '[data-show-entries]',
                dataPagination: '[data-pagination]',
                dataStateSave: false
            });

            // Attach event listeners to dynamically created elements
            $('#approval-table-dynamic').on('change', '.custom-switch-input', function(event) {
                const isChecked = event.target.checked;
                const itemId = event.target.getAttribute('data-id');
                const confirmationMessage = isChecked ? "Are you sure you want to turn on?" :
                    "Are you sure you want to turn off?";

                Swal.fire({
                    icon: 'warning',
                    title: confirmationMessage, // Centering the alert
                    showConfirmButton: true, // Display the confirmation button
                    confirmButtonText: 'Ok', // Customize the button text
                    showCancelButton: true, // Show the cancel button
                    cancelButtonText: 'Cancel', // Customize the cancel button text
                    focusConfirm: true, // Focus the confirmation button by default
                    reverseButtons: true // Reverse the order of buttons
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Call the round method
                        const roundedStatus = round(isChecked ? 1 : 0);
                        // Make AJAX request to update status
                        updateStatus(itemId, roundedStatus);
                    } else {
                        // Revert the checkbox state if the user cancels
                        event.target.checked = !isChecked;
                    }
                });
            });

            function round(status) {
                // Example round function - adjust as necessary
                return Math.round(status);
            }

            function updateStatus(id, status) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                fetch("{{ route('approval-update-toggle-box') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            id: id,
                            status: status
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                position: 'top-end',
                                icon: 'success',
                                title: data.message,
                                toast: true,
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                                didOpen: (toast) => {
                                    toast.addEventListener('mouseenter', Swal.stopTimer);
                                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                                }
                            });
                        } else {
                            Swal.fire({
                                position: 'top-end',
                                icon: 'error',
                                title: data.message,
                                toast: true,
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                                didOpen: (toast) => {
                                    toast.addEventListener('mouseenter', Swal.stopTimer);
                                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                                }
                            });
                            // Revert the checkbox state if the update failed
                            document.querySelector(`#toggle-${id}`).checked = !status;
                        }
                    })
                    .catch(error => {
                        // console.error('Error:', error);
                        Swal.fire({
                            position: 'top-end',
                            icon: 'error',
                            title: 'An error occurred',
                            toast: true,
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                            didOpen: (toast) => {
                                toast.addEventListener('mouseenter', Swal.stopTimer);
                                toast.addEventListener('mouseleave', Swal.resumeTimer);
                            }
                        });
                        // Revert the checkbox state if an error occurred
                        document.querySelector(`#toggle-${id}`).checked = !status;
                    });
            }

            $(document).on('click', '.create-approval-btn', function() {
                var module = $(this).data('module');
                var approvalFlowType = $(this).data('approval-flow-type');
                var url = "{{ route('approval.flow') }}";
                $.ajax({
                    type: 'POST',
                    url: url,
                    data: {
                        module: module,
                        approval_flow_type: approvalFlowType,
                        _token: '{{ csrf_token() }}'

                    },
                    success: function(data) {
                        Swal.fire({
                            position: 'top-end',
                            icon: 'success',
                            title: 'Approval Flow created successfully',
                            toast: true,
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                            didOpen: (toast) => {
                                toast.addEventListener('mouseenter', Swal
                                    .stopTimer);
                                toast.addEventListener('mouseleave', Swal
                                    .resumeTimer);
                            }
                        });
                    },
                })


            });

            $(document).on('click', '.create-approval-flow-btn', function() {
                var module = $(this).data('module');
                $('#moduleId').val(module);
                var approvalFlowType = $(this).data('approval-flow-type');
                $("#normalmodal").modal('show');

            });

            $(document).on('click', '#change-approval-flow-btn', function() {
                var moduleId = $(this).data('module');
                $('#changeFlowModuleId').val(moduleId);
                $("#actionDropdownBtn-" + moduleId).html('<i class="fa fa-spinner fa-spin"></i>');
                $.ajax({
                    type: "POST",
                    url: "{{ route('check.pending.approval.requests') }}",
                    data: {
                        module_id: moduleId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $("#actionDropdownBtn-" + moduleId).html(
                            '<i class="fa fa-ellipsis-v"></i>');
                        if (response.status) {
                            if (response.count > 0) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Pending Approval Requests',
                                    html: `<p>There are ${response.count} pending approval requests for this module. Please resolve them before reseting the approval flow.</p>`,
                                    confirmButtonText: 'OK'
                                });
                            } else {
                                Swal.fire({
                                    title: 'Are you sure?',
                                    text: "This will reset the approval flow for this module.",
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#3085d6',
                                    cancelButtonColor: '#d33',
                                    confirmButtonText: 'Yes, reset it!'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        // Ask for a typed confirmation: user must type exactly "i confirm"
                                        Swal.fire({
                                            title: 'Type "i confirm" to proceed',
                                            input: 'text',
                                            inputPlaceholder: 'Type i confirm',
                                            showCancelButton: true,
                                            confirmButtonText: 'Confirm',
                                            preConfirm: (value) => {
                                                if (value !== 'i confirm') {
                                                    Swal.showValidationMessage(
                                                        'You must type "i confirm" to proceed'
                                                    );
                                                }
                                                return value;
                                            },
                                            didOpen: (popup) => {
                                                // Prevent pasting, dropping, right-click paste and Ctrl/Cmd+V into the input
                                                const input = Swal
                                                    .getInput();
                                                if (!input) return;
                                                // Disable browser autocomplete/autocorrect/spellcheck to avoid suggestions
                                                try {
                                                    input.setAttribute(
                                                        'autocomplete',
                                                        'off');
                                                    input.setAttribute(
                                                        'autocorrect',
                                                        'off');
                                                    input.setAttribute(
                                                        'autocapitalize',
                                                        'off');
                                                    input.setAttribute(
                                                        'spellcheck',
                                                        'false');
                                                    // Give the input a unique name to reduce autofill chances
                                                    input.name =
                                                        'swal-confirm-' +
                                                        Math.random()
                                                        .toString(36).slice(
                                                            2);
                                                    input.autocomplete =
                                                        'off';
                                                } catch (e) {
                                                    // ignore if setting attributes fails in some environments
                                                }
                                                input.addEventListener(
                                                    'paste', (e) => e
                                                    .preventDefault());
                                                input.addEventListener(
                                                    'drop', (e) => e
                                                    .preventDefault());
                                                input.addEventListener(
                                                    'contextmenu', (
                                                        e) => e
                                                    .preventDefault());
                                                input.addEventListener(
                                                    'keydown', (ev) => {
                                                        // Block Ctrl+V / Cmd+V
                                                        if ((ev.ctrlKey ||
                                                                ev
                                                                .metaKey
                                                            ) && (ev
                                                                .key ===
                                                                'v' ||
                                                                ev
                                                                .key ===
                                                                'V')) {
                                                            ev
                                                                .preventDefault();
                                                        }
                                                    });
                                            }
                                        }).then((inputResult) => {
                                            if (inputResult.isConfirmed &&
                                                inputResult.value ===
                                                'i confirm') {
                                                $.ajax({
                                                    type: "POST",
                                                    url: "{{ route('reset.approval.flow') }}",
                                                    data: {
                                                        module_id: moduleId,
                                                        _token: '{{ csrf_token() }}'
                                                    },
                                                    success: function(
                                                        response) {
                                                        // Show a success toast then reload the page
                                                        Swal.fire({
                                                            position: 'top-end',
                                                            icon: 'success',
                                                            title: 'Approval flow has been reset',
                                                            toast: true,
                                                            showConfirmButton: false,
                                                            timer: 1500,
                                                            timerProgressBar: true
                                                        });
                                                        // Give the toast a moment to appear, then reload
                                                        setTimeout(
                                                            function() {
                                                                location
                                                                    .reload();
                                                            },
                                                            1500
                                                        );
                                                    },
                                                    error: function() {
                                                        Swal.fire({
                                                            position: 'top-end',
                                                            icon: 'error',
                                                            title: 'Failed to reset approval flow',
                                                            toast: true,
                                                            showConfirmButton: false,
                                                            timer: 3000,
                                                            timerProgressBar: true
                                                        });
                                                    }
                                                });
                                            }
                                        });
                                    }
                                });
                            }
                        } else {
                            Swal.fire({
                                position: 'top-end',
                                icon: 'error',
                                title: 'An error occurred while checking pending requests. ' +
                                    response.error,
                                toast: true,
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                                didOpen: (toast) => {
                                    toast.addEventListener('mouseenter', Swal
                                        .stopTimer);
                                    toast.addEventListener('mouseleave', Swal
                                        .resumeTimer);
                                }
                            });
                        }
                    }
                });
            });

            // Show centered, beautiful success popup after actions like bulk upload
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    html: `<div style="font-size:1.1em; margin-top:8px;">{{ addslashes(session('success')) }}</div>`,
                    position: 'center',
                    customClass: {
                        popup: 'swal2-show-approval-center'
                    },
                    showConfirmButton: true,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#3085d6',
                    timer: 3200,
                    timerProgressBar: true,
                    didOpen: (popup) => {
                        popup.addEventListener('mouseenter', Swal.stopTimer);
                        popup.addEventListener('mouseleave', Swal.resumeTimer);
                    }
                });
            @endif
        });
    </script>
    <script>
        $(document).on('click', '.view-unassigned-employees', function() {

            let moduleId = $(this).data('module-id');
            let moduleName = $(this).data('module-name');

            $('#moduleName').text(moduleName);
            $('#unAssignedEmployeeTable').html(
                '<tr><td colspan="3" class="text-center">Loading...</td></tr>'
            );

            $('#unAssignedEmployeeModal').modal('show');

            $.ajax({
                url: "{{ route('approval.unassigned.employees') }}",
                type: "GET",
                data: {
                    module_id: moduleId
                },
                success: function(res) {

                    let html = '';

                    if (res.length === 0) {
                        html = '<tr><td colspan="3" class="text-center">No employees found</td></tr>';
                    } else {
                        $.each(res, function(index, emp) {
                            html += `<tr>
                                <td>${index + 1}</td>
                                <td>${emp.emp_code}</td>
                                <td>${emp.emp_full_name}</td>
                            </tr>`;
                        });
                    }

                    $('#unAssignedEmployeeTable').html(html);
                }
            });
        });
    </script>
@endsection

@section('content')
    <!-- MODAL -->
    <div class="modal fade" id="normalmodal" tabindex="-1" role="dialog" aria-labelledby="normalmodal" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="normalmodal1">Create Approval Flow</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form action="{{ route('approval.flow') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="module" id="moduleId">
                        <input type="hidden" name="request" id="requestId">
                        <label for="approvalType">Approval Type</label>
                        <select name="approvalType" id="approvalType" class="form-control" required>
                            <option value="" selected>Select Approval Type</option>
                            @foreach ($approvalFlowType as $item)
                                <option value="{{ $item->m_id }}">{{ $item->m_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-outline-danger" data-bs-dismiss="modal">Close</a>
                        <button class="btn btn-outline-primary" type="submit">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- END MODAL -->
    <!-- UPLOAD BULK EMPLOYEE-WISE APPROVALS MODAL -->
    <div class="modal fade" id="uploadBulkApprovalModal" tabindex="-1" role="dialog"
        aria-labelledby="uploadBulkApprovalModal" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Employee-wise Approvals</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <form action="{{ route('approval.mapping.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label mb-1">Module</label>
                            <select name="module_id" class="form-select" required>
                                <option value="">Select Module</option>
                                @foreach ($modules as $mId => $mName)
                                    <option value="{{ $mId }}">{{ $mName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label mb-1">Upload Excel</label>
                            <input type="file" name="import_file" class="form-control" accept=".xlsx,.csv" required />
                            <small class="text-muted">Only .xlsx, .csv files</small>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('approval.mapping.sample') }}" target="_blank"
                                class="btn btn-outline-primary">
                                <i class="fa fa-download me-2"></i>Download Sample Format
                            </a>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-outline-danger" data-bs-dismiss="modal">Cancel</a>
                        <button class="btn btn-outline-success" type="submit">
                            <i class="fa fa-upload me-2"></i>Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- END UPLOAD BULK EMPLOYEE-WISE APPROVALS MODAL -->
    <div>

        {{-- Bradcrumbs Start --}}
        <div class="p-0 mt-3">
            <div class="row">
                <div class="col-md-4">
                    <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                        <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                        <li><a href="{{ url('/admin/settings/tada-settings/approval-list') }}">Privilege</a></li>
                        <li class="active"><span><b>FNF Approval Flow</b></span></li>
                    </ol>
                </div>
            </div>
        </div>
        {{-- Bradcrumbs End --}}



        <div class="row mt-5">
            <div class="col-xl-12 col-md-12 col-lg-12">
                <div class="card">

                    <div class="card-header border-0">
                        <h4 class="card-title">FNF Approval Flow</h4>
                    </div>

                    <div class="card-body">
                        @if (session()->has('import_errors_blade'))
                            <div class="alert alert-warning d-flex align-items-center justify-content-between"
                                role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="fa fa-info-circle me-2"></i>
                                    <span>There were errors in the import. You can download the error file from the link
                                        below:</span>
                                </div>
                                <a href="{{ route('employee.downloadErrorFile') }}" onclick="location.reload()"
                                    class="btn btn-danger btn-sm ms-3">Download Error File</a>
                            </div>
                        @endif
                        @csrf
                        <div class="row">
                            <div class="col-sm-1">
                                <div class="form-group">
                                    <p class="form-label">Show entries</p>
                                    <select id="customLengthMenu" class="form-select-md p-2 search_test"
                                        style="width: 100%" data-length>
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

                            <div class="col-sm-6">
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

                            <div class="col-sm-1"
                                style="padding-left: 1px;padding-right: 1px;height: 10px;margin-top: 31px;margin-left: 25px;">
                                <div class="form-group dropdown">
                                    <button class="custom-button dropdown-toggle" type="button" id="uploadBulkDropdown"
                                        data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false">
                                        <i class="fa fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-import" aria-labelledby="uploadBulkDropdown">
                                        <li>
                                            {{-- <button class="dropdown-item" type="button" data-bs-toggle="modal"
                                                data-bs-target="#uploadBulkApprovalModal">
                                                <i class="fa fa-upload me-2"></i> Upload Bulk Approvals
                                            </button> --}}

                                            <button class="dropdown-item" type="button" data-bs-toggle="modal"
                                                data-bs-target="#uplode_images">
                                                <i class="fa fa-upload me-2"></i> Uploads singature
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


                                .dropdown-menu-import {
                                    font-size: 14px;
                                    min-width: 210px;
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
                                id="approval-table-dynamic">
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


    <div class="modal fade" id="unAssignedEmployeeModal" tabindex="-1">
        <div class="modal-dialog modal-lg ">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        Unassigned Employees - <span id="moduleName"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Emp Code</th>
                                <th>Employee Name</th>
                            </tr>
                        </thead>
                        <tbody id="unAssignedEmployeeTable" class="datatable">
                            <tr>
                                <td colspan="3" class="text-center">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Signature Modal -->
    <div class="modal fade" id="uplode_images" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Upload Signature</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form action="{{ route('upload.signature') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="modal-body">

                        <!-- Description -->
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Enter description"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Upload Signature</label>
                            <input type="file" name="signature" id="signatureInput" class="form-control"
                                accept="image/*" required>
                            <small class="text-muted">Max Size: 20 KB</small>
                            <div class="text-danger" id="signatureError"></div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Upload</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <script>
        document.getElementById('signatureInput').addEventListener('change', function() {

            const file = this.files[0];
            const maxSize = 20 * 1024; // 20 KB
            const errorDiv = document.getElementById('signatureError');

            errorDiv.innerHTML = '';

            if (file) {

                if (file.size > maxSize) {
                    errorDiv.innerHTML = "File size must be less than 20 KB.";
                    this.value = ""; // reset input
                }

            }

        });
    </script>

    <script>
        $(document).on('click', '.toggleStatusBtn', function() {
            const id = $(this).data('id');

            $.post(`/admin/employee-exit/toggle-status/${id}`, {
                    _token: $('meta[name="csrf-token"]').attr('content')
                })
                .done(function(res) {
                    if (res.status) {
                        Swal.fire('Success!', res.message, 'success');
                    } else {
                        Swal.fire('Error!', res.message, 'error');
                    }

                    $('#mail-template-table').DataTable().ajax.reload(null, false);
                })
                .fail(function(xhr) {
                    let msg = 'Something went wrong!';

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }

                    Swal.fire('Error!', msg, 'error');
                });
        });
    </script>
@endsection
