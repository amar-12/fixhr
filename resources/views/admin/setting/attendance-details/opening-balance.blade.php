@extends('admin.layout.master')
@section('title')
    Opening Balance
@endsection
@section('css')
<style>
    .emp-id-exists {
        border-color: red;
        color: red;
    }

    .message-exists {
        color: red;
    }

    .star-dot {
        color: red;
    }

    table td {
        padding: 0;
    }
</style>

@endsection

@section('script')
    <script type="text/javascript">
        $(document).ready(function() {
            // Initialize DataTable
            datatable({
                tableId: "opening-balance-table-dynamic",
                url: "{{ route('opening-balance.index') }}",
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
@endsection


@section('content')
    <div>
        <div class="p-0 mt-3">
            <div class="row">
                <div class="col-md-4">
                    <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                        <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                        <li><a class="text-white">Requests</a></li>
                        <li class="active"><span><b>Opening Balance</b></span></li>
                    </ol>
                </div>
                <div class="col-md-5"></div>
                <div class="col-md-3">
                    <div class="page-rightheader ms-md-auto">
                        <div class="d-flex align-items-end flex-wrap my-auto end-content breadcrumb-end">
                            <div class="d-lg-flex d-block ms-auto">
                                <div class="btn-list">
                                    <a id="addOpeningBalance" class="btn btn-outline-primary" data-bs-toggle="modal"
                                        data-bs-target="#createOpeningBalance">Create Opening Balance</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ROW -->
        <div class="row">
            <div class="col-xl-12 col-md-12 col-lg-12">
                <div class="card">
                    <div class="card-header border-0">
                        <h4 class="card-title">Opening Balance </h4>
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

                            <div class="col-sm-6">
                            </div>

                            <div class="col-sm-1">
                                <button class="custom-button w-100" type="button" onclick="toggleFilters()"
                                    style="margin-top: 28px;">
                                    <!-- Custom SVG: 2 horizontal lines with knobs -->
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.5">
                                        <!-- Top slider -->
                                        <line x1="3" y1="8" x2="21" y2="8"
                                            stroke-linecap="round" />
                                        <circle cx="10" cy="8" r="1.5" fill="currentColor" />

                                        <!-- Bottom slider -->
                                        <line x1="3" y1="16" x2="21" y2="16"
                                            stroke-linecap="round" />
                                        <circle cx="16" cy="16" r="1.5" fill="currentColor" />
                                    </svg>
                                    Filters
                                </button>
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
                                    <ul class="dropdown-menu p-2">
                                        <li>
                                            <a href="javascript:void(0)"
                                                class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2"
                                                data-bs-toggle="modal" data-bs-target="#addLeaveBalanceFile">
                                                <i class="las la-file-upload"></i> Upload File
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('opening.balance.downloadExcel') }}"
                                                class="dropdown-item text-success fw-semibold d-flex align-items-center gap-2">
                                                <i class="las la-file-download"></i> Export Format
                                            </a>
                                        </li>
                                    </ul>

                                </div>
                            </div>

                            <div class="row">
                                <div id="filterContainer" style="display: none; margin-bottom: 21px;">
                                    <div class="row">
                                        <div class="col-sm-2">
                                            <label for="branchFilter" class="form-label">Branch</label>
                                            <select id="balance_branchFilter" data-filter class="form-select search-txt filter_border">
                                                <option value="">All</option>
                                                @foreach ($branch as $branchF)
                                                    <option value="{{ $branchF->br_id }}">{{ $branchF->br_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="departmentFilter" class="form-label">Department</label>
                                            <select id="balance_departmentFilter" data-filter class="form-select search-txt filter_border">
                                                <option value="">All</option>
                                                @foreach ($departments as $departmentF)
                                                    <option value="{{ $departmentF->d_id }}">{{ $departmentF->d_name }}
                                                    </option>
                                                @endforeach

                                            </select>
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="designationFilter" class="form-label">Designation</label>
                                            <select id="balance_designationFilter" data-filter class="form-select search-txt filter_border">
                                                <option value="">All</option>
                                                @foreach ($designations as $designationF)
                                                    <option value="{{ $designationF->dg_id }}">{{ $designationF->dg_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="activeFilter" class="form-label">Employee Status</label>
                                            <select id="balance_activeFilter" data-filter class="form-select search-txt filter_border">
                                                <option value="">All</option>
                                                <option value="71">Active</option>
                                                <option value="72">Inactive</option>
                                            </select>
                                        </div>

                                       
                                        <div class="col-sm-2">
                                            <label for="toDate" class="form-label">Month</label>
                                            <input type="month" data-filter id="mt_monthFilter" name="monthFilter"
                                                value="{{ now()->format('Y-m') }}" placeholder="To Date"
                                                class="form-control filter_border" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <script>
                                function toggleFilters() {
                                    const container = document.getElementById('filterContainer');
                                    container.style.display = container.style.display === 'none' ? 'flex' : 'none';
                                }
                            </script>



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
                            @if (session('import_errors_blade'))
                                <div class="alert d-flex align-items-center mt-3">
                                    <p>There were errors in the import. You can download the error file from the link below:</p>
                                    <a href="{{ route('employee.downloadErrorFile') }}" onclick="location.reload()"
                                        class="ms-2 mb-4 btn btn-danger">Download Error File</a>
                                </div>
                            @endif
                        </div>
                        <div class="table-responsive">
                            <table class="table display table-hover table-vcenter text-wrap border-bottom"
                                id="opening-balance-table-dynamic">
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
        {{-- for model file upload strat --}}
        <div class="modal fade" id="addLeaveBalanceFile" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content tx-size-sm">
                    <div class="modal-header border-0">
                        <h4 class="modal-title ms-2" id="modal-title">Upload Opening Balance File</h4>
                        <button aria-label="Close" class="btn-close" data-bs-dismiss="modal"><span
                                aria-hidden="true">&times;</span></button>
                    </div>

                    <form action="{{ route('opening.balance.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="row">

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Upload File :</label>
                                        <input type="file" name="file" id="import_file" class="form-control"
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
                            <button type="reset" class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-outline-primary savebtn" id="saveUptBtn">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="createOpeningBalance" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered " role="document">
                <div class="modal-content tx-size-sm">
                    <div class="modal-header border-0">
                        <h4 class="modal-title ms-2">Opening Balance</h4><button aria-label="Close" class="btn-close"
                            data-bs-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <form id="addOpenBalanceFormId" action="{{ route('opening-balance.add') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="col-lg">
                                <p class="mb-0 pb-0 text-dark fs-13 mt-1 ">Employee <span class="star-dot">*</span></p>
                                <select class="form-control" name="eob_emp_id" required>
                                    <option value="">Select Employee</option>
                                    @foreach ($employees as $emp)
                                        <option value="{{ $emp->emp_id }}">{{ $emp->emp_full_name }} ({{ $emp->emp_code }})</option>
                                    @endforeach
                                </select>

                                @foreach ($leaveTypes as $leaveType)
                                    @php
                                        $leaveCategoryName = $masterLeaveCategory->get($leaveType->lvt_cat_type_id);
                                        $leaveCategoryMap = [ 207 => 'cl', 208 => 'sl', 209 => 'el', 210 => 'ml', 211 => 'pl', 212 => 'mrl', 213 => 'bl'];
                                        $shortCode = $leaveCategoryMap[$leaveType->lvt_cat_type_id] ?? strtolower(str_replace(' ', '_', $leaveCategoryName));
                                    @endphp
                                    <div class="form-group mt-3">
                                        <p class="mb-0 pb-0 text-dark fs-13 mt-1">{{ $leaveCategoryName }} <span class="star-dot">*</span></p>
                                        <input class="form-control numericInput" 
                                            name="eob_{{ $shortCode }}" 
                                            placeholder="{{ $leaveCategoryName }}" 
                                            value="0" min="0" max="30" type="number" step="0.5" required>
                                    </div>
                                @endforeach

                                <!-- <p class="mb-0 pb-0 text-dark fs-13 mt-1 ">Casual Leave(CL) <span class="star-dot">*</span></p>
                                <input class="form-control numericInput" name="eob_cl" placeholder="Casual Leave" value="0" min="0" max="30" type="number" step="0.5" required>

                                <p class="mb-0 pb-0 text-dark fs-13 mt-1 ">Sick Leave(SL) <span class="star-dot">*</span></p>
                                <input class="form-control numericInput" name="eob_sl" placeholder="Sick Leave" value="0" min="0" max="30" type="number" step="0.5" required>

                                <p class="mb-0 pb-0 text-dark fs-13 mt-1 ">Earned Leave(EL)</p>
                                <input class="form-control numericInput" name="eob_el" placeholder="Earned Leave" value="0" min="0" step="0.5" max="30" type="number"> -->
                            </div>
                        </div>
                        <div class="modal-footer d-flex justify-content-end">
                            <button type="reset" class="btn btn-outline-danger  cancel" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-outline-primary savebtn">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Employee Opening Balance Modal -->
        <div class="modal fade" id="openingBalanceModal" tabindex="-1" aria-labelledby="openingBalanceModalLabel" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content" style="max-height: 600px;">
                    <div class="modal-header">
                        <h5 class="modal-title" id="open-bal-his"></h5>
                        <button aria-label="Close" class="btn-close" data-bs-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle mb-0" id="openingBalanceTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>S.No</th>
                                        <th>CL</th>
                                        <th>SL</th>
                                        <th>EL</th>
                                        <th>Total Leave</th>
                                        <th>W.E.F</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="6" class="text-center">Loading...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('addOpenBalanceFormId');
    
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    position: 'top-end',
                    icon: 'success',
                    title: data.message || 'Saved successfully!',
                    toast: true,
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    customClass: {
                        toast: 'swal2-toast-green-glow'
                    },
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer);
                        toast.addEventListener('mouseleave', Swal.resumeTimer);
                    },
                    didClose: () => {
                        let modal = bootstrap.Modal.getInstance(document.getElementById('createOpeningBalance'));
                        if (modal) modal.hide();
                        window.location.reload();
                    }
                });
                form.reset();
            } else {
                Swal.fire({
                    position: 'top-end',
                    icon: 'error',
                    title: data.errors.eob_emp_id || 'Something went wrong!',
                    toast: true,
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer);
                        toast.addEventListener('mouseleave', Swal.resumeTimer);
                    }
                });
            }
        })
        .catch(async (error) => {
            const response = await error.response?.json?.() || {};
            let errorHtml = '';

            if (response?.errors) {
                Object.values(response.errors).forEach(messages => {
                    messages.forEach(msg => errorHtml += `${msg}<br>`);
                });
            }

            Swal.fire({
                position: 'top-end',
                icon: 'error',
                title: 'Validation Errors',
                html: errorHtml || 'Unexpected error occurred.',
                toast: true,
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
        });
    });
});

$(document).on('click', '.openBtn', function () {
    const empId = $(this).data('id');

    // Clear and show loading
    $('#openingBalanceTable tbody').html('<tr><td colspan="6" class="text-center">Loading...</td></tr>');
    $('#openingBalanceModal').modal('show');

    $.ajax({
        url: window.location.href,
        method: 'GET',
        data: {
            modal_emp_id: empId
        },
        success: function (response) {
            let rows = '';

            if (response.length > 0) {
                // Set header using the first item (assuming all records belong to same emp)
                $('#open-bal-his').text(`Opening Balance History (${response[0].emp_name})`);

                response.forEach((item, index) => {
                    rows += `<tr>
                        <td>${index + 1}</td>
                        <td>${item.eob_cl}</td>
                        <td>${item.eob_sl}</td>
                        <td>${item.eob_el}</td>
                        <td>${item.eob_total_leave}</td>
                        <td>${item.created_at}</td>
                    </tr>`;
                });
            } else {
                $('#open-bal-his').text('Opening Balance History');
                rows = `<tr><td colspan="6" class="text-center">No records found.</td></tr>`;
            }

            $('#openingBalanceTable tbody').html(rows);
        },
        error: function () {
            $('#openingBalanceTable tbody').html('<tr><td colspan="6" class="text-danger text-center">Error loading data.</td></tr>');
        }
    });
});

$(document).on('click', '.deleteAllOpeningBalance', function () {
    const empId = $(this).data('id');
    const deleteUrl = "{{ route('opening.balance.delete', ':id') }}".replace(':id', empId);

    Swal.fire({
        title: "Are you sure?",
        text: "This will permanently delete all opening balance records for this employee.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Yes, delete it!"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: deleteUrl,
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (response.success) {
                        Swal.fire("Deleted!", "Opening balance records have been deleted.", "success")
                            .then(() => {
                                location.reload(); // or refresh DataTable only
                            });
                    } else {
                        Swal.fire("Error", "Failed to delete the data.", "error");
                    }
                },
                error: function () {
                    Swal.fire("Error", "Something went wrong on the server.", "error");
                }
            });
        }
    });
});


// Set all input field value 0 functionality by class
$(document).ready(function () {
    $(".numericInput").on("focus", function () {
        if ($(this).val() === "0") {
            $(this).val("");
        }
    });
    $(".numericInput").on("blur", function () {
        if ($.trim($(this).val()) === "") {
            $(this).val("0");
        }
    });
});
</script>

<script>

    //excel upload file sweet alert
    document.addEventListener('DOMContentLoaded', function() {
        @if (session('success'))
            Swal.fire({
                position: 'top-end',
                icon: 'success',
                title: '{{ session('success') }}',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
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

        @if (session('error'))
            Swal.fire({
                position: 'top-end',
                icon: 'error',
                title: '{{ session('error') }}',
                toast: true,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
        @endif

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
