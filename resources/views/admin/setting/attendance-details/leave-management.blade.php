@extends('admin.layout.master')
@section('title')
    Leave Management
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

    /* #btnXyz:hover {
        color: #fff
    } */

    table td {
        padding: 0;
    }


    #daily-attendance-table-dynamic tbody tr:hover {
        background-color: rgb(236, 236, 236);
        /* light gray background */
        transition: background-color 0.2s ease-in-out;
        cursor: pointer;
    }
</style>

@endsection

@section('script')
    <script type="text/javascript">
        $(document).ready(function() {
            // Initialize DataTable
            datatable({
                tableId: "daily-attendance-table-dynamic",
                url: "{{ route('leave.leave-management.index') }}",
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
        <div class=" p-0 pb-4">
            <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
                <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li><a class="text-white">Leave</a></li>
                <li class="active"><span><b>Leave Management</b></span></li>
            </ol>
        </div>

        <!-- ROW -->
        <div class="row">
            <div class="col-xl-12 col-md-12 col-lg-12">
                <div class="card">
                    <div class="card-header border-0">
                        <h4 class="card-title">Leave Management List </h4>
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
                                            <a href="{{ route('leave.downloadExcel') }}"
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
                                            <select id="branchFilter" data-filter class="form-select search-txt filter_border">
                                                <option value="">All</option>
                                                @foreach ($branch as $branchF)
                                                    <option value="{{ $branchF->br_id }}">{{ $branchF->br_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="departmentFilter" class="form-label">Department</label>
                                            <select id="departmentFilter" data-filter class="form-select search-txt filter_border">
                                                <option value="">All</option>
                                                @foreach ($departments as $departmentF)
                                                    <option value="{{ $departmentF->d_id }}">{{ $departmentF->d_name }}
                                                    </option>
                                                @endforeach

                                            </select>
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="designationFilter" class="form-label">Designation</label>
                                            <select id="designationFilter" data-filter class="form-select search-txt filter_border">
                                                <option value="">All</option>
                                                @foreach ($designations as $designationF)
                                                    <option value="{{ $designationF->dg_id }}">{{ $designationF->dg_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="activeFilter" class="form-label">Employee Status</label>
                                            <select id="activeFilter" data-filter class="form-select search-txt filter_border">
                                                <option value="">All</option>
                                                <option value="71">Active</option>
                                                <option value="72">Inactive</option>
                                            </select>
                                        </div>

                                        <div class="col-sm-2">
                                            <label for="toDate" class="form-label">Date</label>
                                            <span class="bg-light border-0 rounded-start-4">
                                            </span>
                                            <input type="month" id="toDate" placeholder="To Date" class="form-control filter_border" data-date-filter="to-date" value="{{ now()->format('Y-m') }}" />
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
                        </div>
                        
                        <div class="table-responsive">
                            {{-- <table class="table display table-vcenter text-wrap border-bottom" id="daily-attendance-table-dynamic"> --}}
                            <table class="table display table-vcenter text-nowrap table-bordered border-bottom no-footer" id="daily-attendance-table-dynamic">  
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
        @if (session('leave_import_errors_blade'))
            <div class="alert d-flex align-items-center mt-3">
                <p>There were errors in the import. You can download the error file from the link below:</p>
                <a href="{{ route('leave.downloadErrorFile') }}" onclick="location.reload()"
                    class="ms-2 mb-4 btn btn-outline-danger ">Download Error File</a>

            </div>
        @endif
        {{-- for model file upload strat --}}
        <div class="modal fade" id="addLeaveBalanceFile" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content tx-size-sm">
                    <div class="modal-header border-0">
                        <h4 class="modal-title ms-2" id="modal-title">Upload Leave Balance File</h4>
                        <button aria-label="Close" class="btn-close" data-bs-dismiss="modal"><span
                                aria-hidden="true">&times;</span></button>
                    </div>

                    <form action="{{ route('leave.import') }}" method="POST" enctype="multipart/form-data">
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

    </div>

    <!-- Apply Leave Modal -->
    <div class="modal fade" id="applyLeaveModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Apply Leave for <span id="employeeName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div>
                                <div class="card-body">
                                    <form id="leaveApplicationForm">
                                    @csrf
                                    <div class="row">
                                          <input type="hidden" id="employeeId" name="employee_id">
                                           <input type="hidden" id="" name="extra_days">
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label>Select Leave Type <span class="text-danger">*</span></label>
                                                    <select id="leaveTypeSelect" name="leave_day_type_id" class="form-control select2" required>
                                                        <option value="">Select Leave Type</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label>Leave Categories <span class="text-danger">*</span></label>
                                                    <select id="leaveCategorySelect" name="leave_category_id" class="form-control select2" required>
                                                        <option value="">Select Leave Category</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Full Day Fields -->
                                            <div id="full-day-fields" class="col-12 d-none">
                                                <div class="row">
                                                    <div class="col-lg-6">
                                                        <div class="form-group">
                                                            <label>From Date <span class="text-danger">*</span></label>
                                                            <input type="date" name="leave_start_date" class="form-control">
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <div class="form-group">
                                                            <label>To Date <span class="text-danger">*</span></label>
                                                            <input type="date" name="leave_end_date" class="form-control">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Half Day Fields -->
                                            <div id="half-day-fields" class="col-12 d-none">
                                                <div class="row">
                                                    <div class="col-lg-6">
                                                        <div class="form-group">
                                                            <label>Day Segment <span class="text-danger">*</span></label>
                                                            <select id="daySegmentSelect" name="leave_day_segment_id" class="form-control select2">
                                                                <option value="">Select Segment</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                     <div class="col-lg-6">
                                                        <div class="form-group">
                                                            <label>Date <span class="text-danger">*</span></label>
                                                            <input type="date" name="leave_start_date1" class="form-control">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <!-- Shared Fields -->
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    <label>Reason <span class="text-danger">*</span></label>
                                                    <textarea name="reason" class="form-control" rows="3" placeholder="Reason" required></textarea>
                                                </div>
                                            </div>

                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    <label>Attachment (if any)</label>
                                                    <input type="file" name="leave_document" class="form-control" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                                                    <small class="text-muted">Max 2MB. Allowed: JPG, PNG, PDF, DOC, DOCX</small>
                                                </div>
                                            </div>

                                     </div>
                                    </form>
                                </div>
                                <div class="card-footer">
                                    <div class="d-flex justify-content-end">
                                        <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
                                        <button type="button" class="btn btn-primary" id="submitLeaveApplication">Apply</button>
                                    </div>
                                </div>
                            </div>
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
    $(document).on('click', '.openBtn', function() {
        // $('#employeeModal').modal('show');
        // // var empId = $(this).data('id');
        // var empName = $(this).data('name');
        // var empCode = $(this).data('code');
        // var empBirth = $(this).data('birth');
        // var empRole = $(this).data('role');
        // var empType = $(this).data('type');
        // var empBranch = $(this).data('branch');
        // var empDepartment = $(this).data('department');
        // var empPhone = $(this).data('phone');
        // var empEmail = $(this).data('email');
        // var empDesignation = $(this).data('designation');
        // var empGrade = $(this).data('grade');
        // var empGender = $(this).data('gender');
        // var empPolicy = $(this).data('policy');
        // var empMarital = $(this).data('marital');
        // var empJoining = $(this).data('joining');
        // var empStatus = $(this).data('status'); //data-status="pennddi"
        // var empMode = $(this).data('mode');
        // var empShift = $(this).data('shift');
        // var empProfile = $(this).data('profile');

        // let baseUrl = '{{ asset('uploads/employee_profile') }}';
        // let profileImageUrl = empProfile ? baseUrl + '/' + empProfile : '{{ asset('assets/imgs/user.png') }}';
        // // console.log(profileImageUrl);

        // $('#empName').text(empName);
        // $('#empCode').text(empCode);
        // $('#empRole').text(empRole);
        // $('#empBirth').text(empBirth);
        // $('#empType').text(empType);
        // $('#empBranch').text(empBranch);
        // $('#empDepartment').text(empDepartment);
        // $('#empPhone').text(empPhone);
        // $('#empEmail').text(empEmail);
        // $('#empDesignation').text(empDesignation);
        // $('#empGrade').text(empGrade);
        // $('#empGender').text(empGender);
        // $('#empPolicy').text(empPolicy);
        // $('#empMarital').text(empMarital);
        // $('#empJoining').text(empJoining);
        // $('#empStatus').text(empStatus);
        // $('#empMode').text(empMode);
        // $('#empShift').text(empShift);
        // $('#empAvtar').css('background-image', 'url(' + profileImageUrl + ')');
    });


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
                html: errorMessages,
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
    $(document).ready(function () {
        $('#customFile').on('change', function () {
            const file = this.files[0];
            const errorElem = $('#fileError');
            const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            const maxSize = 2 * 1024 * 1024; // 2MB

            if (file) {
                if (file.size > maxSize || !allowedTypes.includes(file.type)) {
                    errorElem.removeClass('d-none').text('File too large or invalid type');
                    $(this).val('');
                } else {
                    errorElem.addClass('d-none').text('');
                }
            }
        });
    });
</script>
<script>
$(document).ready(function() {
    $('.select2').select2();

    $(document).on('click', '.apply-leave-btn', function() {
        const empId = $(this).data('emp-id');
        const empName = $(this).data('emp-name');

        $('#employeeId').val(empId);
        $('#employeeName').text(empName);

        resetLeaveForm();

        fetchLeaveOptions(empId);
        
        $('#applyLeaveModal').modal('show');
    });

    $(document).on('change', '#leaveTypeSelect', function() {
        handleLeaveTypeChange($(this).val());
    });
});

function resetLeaveForm() {
    $('#leaveTypeSelect').empty().append('<option value="">Loading...</option>');
    $('#leaveCategorySelect').empty().append('<option value="">Loading...</option>');
    $('#daySegmentSelect').empty().append('<option value="">Loading...</option>');
    
    $('#full-day-fields, #half-day-fields').addClass('d-none');
    
    $('input[name="from_date"], input[name="to_date"], input[name="half_day_date"]').val('');
    
    // Reset select2 if initialized
    if ($('#leaveTypeSelect').hasClass('select2-hidden-accessible')) {
        $('#leaveTypeSelect, #leaveCategorySelect, #daySegmentSelect').val('').trigger('change');
    }
}

function fetchLeaveOptions(employeeId) {
    $.ajax({
        url: '{{ route("leave.get-employee-leave-options") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            employee_id: employeeId
        },
        success: function(response) {
            if (response && response.result && response.result.length > 0) {
                populateLeaveOptions(response.result[0]);
            } else {
                showError("No leave options available for this employee.");
            }
        },
        error: function(xhr) {
            showError("Failed to load leave options. Please try again.");
            console.error("Error fetching leave options:", xhr.responseText);
        }
    });
}

function populateLeaveOptions(data) {
    // Populate Leave Type (Full Day, Half Day)
    const $leaveType = $('#leaveTypeSelect').empty().append('<option value="">Select Leave Type</option>');
    if (data.leave_day_type && data.leave_day_type.length) {
        $.each(data.leave_day_type, function(i, type) {
            $leaveType.append($('<option>', {
                value: type.id,
                text: type.name
            }));
        });
    }
    $leaveType.select2();

    // Populate Leave Categories
    const $leaveCategory = $('#leaveCategorySelect').empty().append('<option value="">Select Leave Category</option>');
    if (data.leave_type && data.leave_type.length) {
        $.each(data.leave_type, function(i, lt) {
            if (lt.cat_type_id && lt.cat_type_id.length) {
                const category = lt.cat_type_id[0];
                $leaveCategory.append($('<option>', {
                    value: category.id,
                    text: category.name
                }));
            }
        });
    }
    $leaveCategory.select2();

    // Populate Day Segments
    const $daySegment = $('#daySegmentSelect').empty().append('<option value="">Select Segment</option>');
    if (data.leave_day_segment && data.leave_day_segment.length) {
        $.each(data.leave_day_segment, function(i, seg) {
            $daySegment.append($('<option>', {
                value: seg.id,
                text: seg.name
            }));
        });
    }
    $daySegment.select2();
}

function handleLeaveTypeChange(selectedType) {
    // Hide all fields first
    $('#full-day-fields, #half-day-fields').addClass('d-none');
    
    // Clear all fields when changing type
    $('input[name="from_date"], input[name="to_date"], input[name="half_day_date"]').val('');
    $('#daySegmentSelect').val('').trigger('change');

    // Show relevant fields based on selection
   /* if (selectedType === "Full Day") {
        $('#full-day-fields').removeClass('d-none');
    } else if (selectedType === "Half Day") {
        $('#half-day-fields').removeClass('d-none');
    }  */

    if (selectedType === "201") {
        $('#full-day-fields').removeClass('d-none');
    } else if (selectedType === "202") {
        $('#half-day-fields').removeClass('d-none');
    }
}

function showError(message) {
    // You might want to implement a proper error display mechanism
    console.error(message);
    alert(message); // Temporary - replace with your UI error display
}
/*$(document).on('click', '#submitLeaveApplication', function () {
    const form = $('#leaveApplicationForm')[0];

    if (!form.checkValidity()) {
        // Show browser validation messages
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);

    $.ajax({
        url: '{{ route("leave.leave-apply") }}',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
            if (response.success) {
                $('#applyLeaveModal').modal('hide');
                showSuccessToast(response.message || 'Leave applied successfully!');
            } else {
                showError(response.message || 'Something went wrong. Please try again.');
            }
        },
        error: function (xhr) {
            let message = "An error occurred.";
            if (xhr.responseJSON?.message) {
                message = xhr.responseJSON.message;
            }
            showError(message);
        }
    });
});*/
$(document).on('click', '#submitLeaveApplication', function () {
    const form = $('#leaveApplicationForm')[0];

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content')); // Fix here

    $.ajax({
        url: '{{ route("leave.leave-apply") }}',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
            if (response.status) {
                $('#applyLeaveModal').modal('hide');
                showSuccessToast(response.message || 'Leave applied successfully!');
            } else {
                showError(response.message || 'Something went wrong. Please try again.');
            }
        },
        error: function (xhr) {
            let message = "An error occurred.";
            if (xhr.responseJSON?.message) {
                message = xhr.responseJSON.message;
            }
            showError(message);
        }
    });
});

function showSuccessToast(message) {
    Swal.fire({
        position: 'top-end',
        icon: 'success',
        title: message,
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
            location.reload();
        }
    });
}

function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: message,
        position: 'top-end',
        toast: true,
        showConfirmButton: false,
        timer: 3000
    });
}
</script>