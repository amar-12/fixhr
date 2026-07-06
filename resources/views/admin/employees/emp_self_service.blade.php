@extends('admin.layout.master')
@section('title', 'Employee Self Service (ESS) Portal')
@section('css')
<style>
    /* Ensure Select2 search field is visible and interactive */
    .select2-container .select2-search__field {
        display: block !important;
        width: 100% !important;
        pointer-events: auto !important;
        padding: 5px;
    }
    .select2-container--default .select2-search--dropdown {
        display: block !important;
    }
    /* Ensure form elements are not blocked */
    #managerChangeForm select, #managerChangeForm input {
        pointer-events: auto !important;
    }

    .select2.select2-container {
        width: 100% !important;
    }

    #leave_balance_table > thead > tr > th {
        width: 25% !important;
    }

    .daterangepicker .ranges li.active {
        background-color: #0d6efd;
    }

    .daterangepicker td.active,
    .daterangepicker td.active:hover {
        background-color: #0d6efd;
    }

    .restrict_check {
        transform: scale(2.0);
        cursor: pointer;
    }

    .restrict_wrapper {
        display: flex;
        align-items: center;
        gap: 10px; /* adjust as needed */
    }
</style>
@endsection
@section('content')

{{-- Breadcrumbs Start --}}
<div class="p-0 my-1">
    <ol class="breadcrumb breadcrumb-arrow m-0 p-0" style="background: none;">
        <li><a href="{{ url('/dashboard') }}">Dashboard</a></li>
        <li><a class="text-white">Employee</a></li>
        <li class="active"><span><b>Employee Self Service</b></span></li>
    </ol>
</div>
{{-- Breadcrumbs End --}}

<div class="container my-5">

    @if (session('import_errors_blade'))
        <div class="alert d-flex align-items-center mt-3">
            <p>There were errors in the import. You can download the error file from the link below:</p>
            <a href="{{ route('employee.downloadErrorFile') }}"
                class="ms-2 mb-4 btn btn-danger">Download Error File</a>
        </div>
    @endif

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills border-bottom-0 mb-0" id="essTab" role="tablist">
        <li class="nav-item text" role="presentation">
            <button class="nav-link fs-15 py-3 active" id="cng-mngr-tab" data-bs-toggle="tab" data-bs-target="#change_manager" type="button" role="tab">
                <i class="fa fa-user-plus me-2"></i>Change Manager
            </button>
        </li>

        <li class="nav-item text" role="presentation">
            <button class="nav-link fs-15 py-3" id="leave-tab" data-bs-toggle="tab" data-bs-target="#leaveManagement" type="button" role="tab">
                <i class="fa fa-calendar me-2"></i>Leave Management
            </button>
        </li>
        <li class="nav-item text" role="presentation">
            <button class="nav-link fs-15 py-3" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#attendancemgmt" type="button" role="tab">
                <i class="fa fa-money me-2"></i>Attendance Management
            </button>
        </li>
        <li class="nav-item text" role="presentation">
            <button class="nav-link fs-15 py-3" id="employee-tab" data-bs-toggle="tab" data-bs-target="#employeemgmt" type="button" role="tab">
                <i class="fa fa-user me-2"></i>Employee Management
            </button>
        </li>
        {{-- <li class="nav-item text" role="presentation">
            <button class="nav-link fs-15 py-3" id="payroll-tab" data-bs-toggle="tab" data-bs-target="#payroll" type="button" role="tab">
                <i class="fa fa-money me-2"></i>Payroll
            </button>
        </li>
        <li class="nav-item text" role="presentation">
            <button class="nav-link fs-15 py-3" id="trevel-tab" data-bs-toggle="tab" data-bs-target="#trevel" type="button" role="tab">
                <i class="fa fa-motorcycle me-2"></i>Travel Management
            </button>
        </li>
        <li class="nav-item text" role="presentation">
            <button class="nav-link fs-15 py-3" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab">
                <i class="fa fa-file me-2"></i>Download Documents
            </button>
        </li> --}}
    </ul>

    <!-- Tab Content -->
    <div class="tab-content bg-white border border-top-0 rounded-bottom shadow-sm p-4" id="essTabContent">
        
        <!-- Change Manager Tab -->
        <div class="tab-pane fade show active" id="change_manager" role="tabpanel">
            <div class="container mt-5">
                <div class="card">
                    <div class="card-body">
                        <div class="row flex-nowrap overflow-auto">
                            <div class="col-md-3 px-2">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input toggle-switch" type="checkbox" id="toggle1">
                                    </div>
                                </div>
                                <form id="managerChangeForm1" class="manager-form">
                                    <input type="hidden" name="form_type" value="REPORTING_MANAGER">
                                    <div class="mb-3">
                                        <label for="currentManager1" class="form-label fw-semibold text-dark">
                                            Current Reporting Manager
                                        </label>
                                        <select id="current_reporting_manager" name="current_reporting_manager" class="form-select" required></select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="newManager1" class="form-label fw-semibold text-dark">
                                            New Reporting Manager
                                        </label>
                                        <select id="new_reporting_manager" name="new_reporting_manager" class="form-select" required></select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="wefDate1" class="form-label fw-semibold text-dark">
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            WEF
                                        </label>
                                        <input type="date" class="form-control border-1" id="wefDate1" name="wefDate">
                                    </div>
                                    <div class="mb-3">
                                        <label for="reason1" class="form-label fw-semibold text-dark">
                                            <i class="fas fa-comment-alt me-1"></i>
                                            Reason
                                        </label>
                                        <input type="text" class="form-control border-1" id="reason1" name="reason" required>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-3 px-2">
                            </div>
                            <div class="col-md-3 px-2">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input toggle-switch" type="checkbox" id="toggle2">
                                    </div>
                                </div>
                                <form id="managerChangeForm2" class="manager-form">
                                    <input type="hidden" name="form_type" value="APPROVAL_MANAGER">
                                    <div class="mb-3">
                                        <label for="current_approval_manager" class="form-label fw-semibold text-dark">
                                            Current Approval Manager
                                        </label>
                                        <select id="current_approval_manager" name="current_approval_manager" class="form-select" required>
                                            <option value="">Select Manager</option>
                                            <option value="manager1">Manager 1</option>
                                            <option value="manager2">Manager 2</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="new_approval_manager" class="form-label fw-semibold text-dark">
                                            New Approval Manager
                                        </label>
                                        <select id="new_approval_manager" name="new_approval_manager" class="form-select" required>
                                            <option value="">Select Manager</option>
                                            <option value="manager3">Manager 3</option>
                                            <option value="manager4">Manager 4</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="wefDate2" class="form-label fw-semibold text-dark">
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            WEF
                                        </label>
                                        <input type="date" class="form-control border-1" id="wefDate2" name="wefDate">
                                    </div>
                                    <div class="mb-3">
                                        <label for="reason2" class="form-label fw-semibold text-dark">
                                            <i class="fas fa-comment-alt me-1"></i>
                                            Reason
                                        </label>
                                        <input type="text" class="form-control border-1" id="reason2" name="reason" required>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="d-flex justify-content-end flex-wrap">
                                    <button type="button" class="btn btn-primary" id="submitBtn">
                                        <i class="fa fa-paper-plane me-1"></i>
                                        Submit Request
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Leave Management Tab --}}
        <div class="tab-pane" id="leaveManagement" role="tabpanel">
            
            {{-- Search Employee --}}
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="lb_employee_id" class="form-label">Employee</label>
                        <select class="form-select" id="lb_employee_id" name="lb_employee_id"
                            data-placeholder="Select Employee" onchange="getEmployeeLeaveBalance(this.value);">
                            <option value="">-----Select-----</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-9 d-flex justify-content-end">
                    <div class="dropdown dropstart">
                        <button class="btn btn-info" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item text-primary" href="{{ route('leave.opening.sample.export') }}"><i class="las la-file-download"></i> Export Sample</a></li>
                            <li><a class="dropdown-item text-success" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#LeaveOpeningBalBulkUpload"><i class="las la-file-upload"></i> Import Data</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <table class="table table-bordered card-table table-vcenter text-nowrap" id="leave_balance_table">
                <thead>
                    <tr>
                        <th>Leave Type</th>
                        <th class="text-end">Current Balance</th>
                        <th class="text-end">Opening Balance</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody class="fs-14" id="leave_balance_tbody">
                    <tr>
                        <td>Leave Type</td>
                        <td><input type="text" class="form-control"></td>
                        <td><input type="text" class="form-control"></td>
                        <td><input type="text" class="form-control"></td>
                    </tr>
                </tbody>
            </table>

            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-end flex-wrap">
                        <button type="button" class="btn btn-primary" id="submitLeaveBalanceBtn" disabled onclick="submitLeaveBalance()">
                            <i class="fa fa-paper-plane me-1"></i>
                            Save Leave Balance
                        </button>
                    </div>
                </div>
            </div>
            
        </div>

        {{-- Attendance Management --}}
        <div class="tab-pane" id="attendancemgmt" role="tabpanel">
            <form id="missedPunchForm">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <p class="form-label">Applied Date Range</p>
                            <div class="input-group mb-3 shadow-sm" style="border-radius: 50px; overflow: hidden;">
                                <span class="input-group-text bg-primary-subtle text-primary border-0"
                                      style="border-radius: 50px 0 0 50px; padding: 0.5rem 1rem;">
                                    <i class="las la-calendar-alt fs-5"></i>
                                </span>
                                <input type="text" id="fromDate" name="fromDate" 
                                       class="form-control border-0" autocomplete="off"
                                       placeholder="Select date range"
                                       style="border-radius: 0 50px 50px 0;">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-9 d-flex align-items-end gap-2">
                        <button type="button" class="btn btn-primary" style="height: 35px;" data-bs-toggle="modal" data-bs-target="#exportEmpAttendanceData">
                            <i class="las la-file-download"></i> Export Missed Punch
                        </button>
                        <button type="button" id="importBtn"
                                class="btn btn-success" style="height: 35px;" data-bs-toggle="modal" data-bs-target="#monthlyAttendanceBulkUpload">
                            <i class="las la-file-upload"></i> Import Missed Punch
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Employee Management --}}
        <div class="tab-pane fade" id="employeemgmt" role="tabpanel">
            <div class="row align-items-end g-3">

                <!-- Select Field -->
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Select Field</label>
                    <select class="form-select" id="update_field" name="update_field">
                        <option value="">----- Select -----</option>
                        <option value="emp_phone">Mobile No</option>
                        <option value="emp_cost_center">Cost Center</option>
                        <option value="emp_profit_center">Profit Center</option>
                        <option value="emp_sap_budget_code">Budget Code (SAP)</option>
                    </select>
                </div>

                <!-- Upload File -->
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Upload File</label>
                    <input type="file"
                           class="form-control"
                           id="upload_file"
                           name="upload_file"
                           accept=".xls,.xlsx,.csv">
                </div>

                <!-- Export Sample -->
                <div class="col-md-3 text-md-end">
                    <label class="form-label d-none d-md-block">&nbsp;</label>

                    <a href="{{ route('employee.field.sample') }}"
                       class="btn btn-outline-primary w-100">
                        <i class="las la-file-download me-1"></i> Export Sample
                    </a>
                </div>

                <!-- Save -->
                <div class="col-md-3 text-md-end">
                    <label class="form-label d-none d-md-block">&nbsp;</label>
                    <button type="button"
                            class="btn btn-primary w-100"
                            id="submitUploadField">
                        <i class="fa fa-paper-plane me-1"></i> Save
                    </button>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card shadow-sm border-0" style="border-radius: 12px;">
                        <div class="card-header bg-light fw-bold text-center" style="font-size: 14px;padding-left: 250px;padding-top: 10px;">
                            Device Restriction
                        </div>
                        <div class="card-body">
                            <!-- Employee Search -->
                            <div class="mb-3">
                                <label class="form-label">Employee</label>
                                <select class="form-select" id="device_employee_id" name="device_employee_id"
                                    data-placeholder="Select Employee">
                                    <option value="">-----Select-----</option>
                                </select>
                            </div>
                            <div class="form-check mb-3 restrict_wrapper">
                                <input class="form-check-input restrict_check" type="checkbox" id="single_device_restriction">
                                <label class="form-check-label" for="single_device_restriction">
                                    Allow Single Device Restriction
                                </label>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-outline-primary resetSingle" disabled>Update Restriction (Employee)</button>
                                <button type="button" class="btn btn-outline-success resetAll">Update Restriction (All)</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="exportEmpAttendanceData" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content tx-size-sm">
            <div class="modal-header">
                <h5 class="modal-title">Export Employee Attendance Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="attendance_status" class="form-label">Select Status</label>
                    <select class="form-select" id="attendance_status">
                        <option value="">Select</option>
                        @foreach ($attendanceStatus as $key => $status)
                            @if ($status['m_id'] != 323)
                                <option value="{{ $status['m_id'] }}">{{ $status['m_name'] }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-outline-primary" id="exportBtn">Export</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="monthlyAttendanceBulkUpload" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content tx-size-sm">
            <div class="modal-header border-0">
                <h4 class="modal-title ms-2" id="modal-title">Upload Monthly Attendance</h4>
                <button aria-label="Close" class="btn-close" data-bs-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('monthly.attendance.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-label">Upload File:</label>
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

{{-- Modals Start --}}
<div class="modal fade" id="LeaveOpeningBalBulkUpload" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content tx-size-sm">
            <div class="modal-header border-0">
                <h4 class="modal-title ms-2" id="modal-title">Upload Leave Balance</h4>
                <button aria-label="Close" class="btn-close" data-bs-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('leave.opening.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="form-label">Upload File:</label>
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
{{-- Modals End --}}

@endsection
@section('script')

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ asset('assets/js/common_select2.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.30.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker@3.1/daterangepicker.min.js"></script>

<script>
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

    $(document).ready(function() {
        setTimeout(function(){
            // Example: initialize Current Manager
            initManagerSelect2('#current_reporting_manager', 'Search current manager...');
            initManagerSelect2('#new_reporting_manager', 'Search current manager...');
            initManagerSelect2('#current_approval_manager', 'Search current manager...');
            initManagerSelect2('#new_approval_manager', 'Search new manager...');
            initManagerSelect2('#lb_employee_id', 'Search an Employee...');
            initManagerSelect2('#device_employee_id', 'Search an Employee...');
        }, 200);

        $('#fromDate').daterangepicker({
            autoUpdateInput: false,
            locale: {
                format: 'DD/MM/YYYY',
                separator: ' - ',
                cancelLabel: 'Clear'
            },
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        });

        $('#fromDate').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
        });

        $('#fromDate').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });

        $("#exportBtn").on("click", function (e) {
            e.preventDefault();
            let dateRange = $('#fromDate').val();
            let attendanceStatus = $('#attendance_status').val();
            var dates = dateRange.split(" - ");
            var startDate = dates[0];
            var endDate = dates[1];

            if (!dateRange) {
                return Swal.fire("Required", "Please select date range", "warning");
            }

            // Build query string
            let params = $.param({
                attendanceStatus: attendanceStatus,
                startDate: startDate,
                endDate: endDate,
                _token: '{{ csrf_token() }}'
            });

            // Use window.location to trigger file download
            window.location.href = "{{ route('monthly.attendance.status.download') }}?" + params;

            // Optionally, close the modal
            $('#exportEmpAttendanceData').modal('hide');
            
        });

        /*$('#exportBtn').on('click', function () {

            let dateRange = $('#fromDate').val();
            let attendanceStatus = $('#attendance_status').val();

            if (!dateRange) {
                return Swal.fire("Required", "Please select date range", "warning");
            }

            var dates = dateRange.split(" - ");
            var startDate = dates[0];
            var endDate = dates[1];
            $.ajax({
                type: "GET",
                url: "{{ route('missed.punch.export') }}",
                data: {
                    attendanceStatus: attendanceStatus,
                    start_date: startDate,
                    end_date: endDate
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function (response) {
                    var a = document.createElement('a');
                    var url = window.URL.createObjectURL(response);

                    a.href = url;
                    a.download = "missed_punch_export.xlsx";
                    a.click();

                    window.URL.revokeObjectURL(url);

                    Swal.fire("Success", "Export generated!", "success");
                },
                error: function () {
                    Swal.fire("Error", "Unable to export!", "error");
                }
            });
        });*/
    });

    function setTotal(ele) {
        let row = $(ele).closest('tr');
        let current_balance = parseFloat(row.find('input[id$="_current"]').val()) || 0;
        let updated_balance = parseFloat(row.find('input[id$="_updated"]').val()) || 0;
        let total = current_balance + updated_balance;
        row.find('input[id$="_total"]').val(total.toFixed(2));
    }

    function getEmployeeLeaveBalance(emp_id) {
        if(emp_id) {
            // AJAX call to fetch and display leave balance
            $.ajax({
               type: "POST",
               url: "{{ route('get.leave.balance') }}",
                data: {
                    employee_id: emp_id,
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.status) {
                        $('#leave_balance_tbody').empty();
                        response.result.forEach(function(leave) {
                            const row = `
                                <tr>
                                    <td>${leave.category_master_detail[0].name}</td>
                                    <td><input type="text" class="form-control text-end" id="leave_balance_${leave.category_master_detail[0].id}_current" value="${leave.total_balance_remaining_leave}" readonly></td>
                                    <td><input type="text" class="form-control text-end" id="leave_balance_${leave.category_master_detail[0].id}_updated" value="0" onkeyup="setTotal(this)" data-leave-id=${leave.category_master_detail[0].id}></td>
                                    <td><input type="text" class="form-control text-end" id="leave_balance_${leave.category_master_detail[0].id}_total" value="${leave.total_balance_remaining_leave}" readonly></td>
                                </tr>
                            `;
                            $('#leave_balance_tbody').append(row);
                        })
                        $("#submitLeaveBalanceBtn").attr("disabled", false);
                    }
                }
            });
        } else {
            $('#leave_balance_tbody').empty();
            const row = `
                <tr>
                    <td>Leave Type</td>
                    <td><input type="text" class="form-control text-end" readonly></td>
                    <td><input type="text" class="form-control text-end"></td>
                    <td><input type="text" class="form-control text-end" readonly></td>
                </tr>
            `;
            $('#leave_balance_tbody').append(row);
            $("#submitLeaveBalanceBtn").attr("disabled", true);
        }
    }

    function submitLeaveBalance() {
        let emp_id = $('#lb_employee_id').val();
        if (Number(emp_id) > 0) {
            // Collect leave balance data
            let leave_balances = [];
            $('#leave_balance_tbody tr').each(function() {
                let leave_type_id = $(this).find('input[id$="_updated"]').data('leave-id');
                let updated_balance = parseFloat($(this).find('input[id$="_updated"]').val()) || 0;
                if (updated_balance !== 0) {
                    leave_balances.push({
                        leave_type_id: leave_type_id,
                        updated_balance: updated_balance
                    });
                }
            });

            if (leave_balances.length > 0) {
                // AJAX call to submit leave balance updates
                $.ajax({
                   type: "POST",
                   url: "{{ route('update.leave.balance') }}",
                    data: {
                        employee_id: emp_id,
                        leave_balances: leave_balances,
                        _token: "{{ csrf_token() }}",
                    },
                    success: function(response) {
                        if (response.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: 'Leave balances updated successfully.'
                            });
                            getEmployeeLeaveBalance(emp_id); // Refresh the balance display
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Update Failed',
                                text: 'Error updating leave balances: ' + response.message
                            });
                        }
                    }
                });
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Changes',
                    text: 'No changes to submit.'
                });
            }
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Employee Required',
                text: 'Please select an employee.'
            });
        }
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggles = document.querySelectorAll('.toggle-switch');
        const forms = document.querySelectorAll('.manager-form');
        const submitBtn = document.getElementById('submitBtn');
        const cancelBtn = document.getElementById('cancelBtn');

        // Function to toggle form state based on its own toggle
        function updateFormState(toggle, form) {
            if (toggle.checked) {
                form.classList.remove('opacity-50', 'pe-none');
                form.querySelectorAll('input, select').forEach(input => {
                    input.disabled = false;
                });
            } else {
                form.classList.add('opacity-50', 'pe-none');
                form.querySelectorAll('input, select').forEach(input => {
                    input.disabled = true;
                });
            }
        }

        // Initialize form states and add toggle event listeners
        toggles.forEach((toggle, index) => {
            const form = forms[index];
            updateFormState(toggle, form); // Set initial state
            toggle.addEventListener('change', () => {
                updateFormState(toggle, form);
            });
        });

        // Add CSRF token to all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        submitBtn.addEventListener('click', (e) => {
            e.preventDefault();
            let submittedForms = 0;
            let successCount = 0;
            let errorCount = 0;

            forms.forEach((form, index) => {
                const toggle = toggles[index];

                if (toggle.checked) {
                    submittedForms++;
                    // Validate form
                    if (!form.checkValidity()) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Missing Fields',
                            text: `Please fill all required fields in form ${index + 1}`,
                            confirmButtonText: 'OK'
                        });
                        return;
                    }

                    // Serialize form data
                    const formData = $(form).serialize();

                    // AJAX Call for this form
                    $.ajax({
                        url: "{{ route('change.employee.manager') }}",
                        method: 'POST',
                        data: formData,
                        success: function (response) {
                            Swal.fire({
                                icon: response.status ? 'success' : 'error',
                                title: response.status ? 'Success' : 'Error',
                                text: response.message,
                            });
                            if (response.status) {
                                successCount++;
                            } else {
                                errorCount++;
                            }
                        },
                        error: function (xhr) {
                            const errorMessage = xhr.responseJSON?.message || 'Something went wrong!';
                            const errors = xhr.responseJSON?.errors
                                ? Object.values(xhr.responseJSON.errors).flat().join(', ')
                                : '';
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: errors ? `${errorMessage}: ${errors}` : errorMessage,
                                confirmButtonText: 'OK'
                            });
                            errorCount++;
                        },
                        // complete: function () {
                        //     // Handle completion of all requests
                        //     if (successCount + errorCount === submittedForms) {
                        //         Swal.fire({
                        //             icon: successCount > 0 ? 'success' : 'error',
                        //             title: 'Submission Complete',
                        //             text: `Processed ${successCount} form(s) successfully, ${errorCount} form(s) failed.`,
                        //         });
                        //     }
                        // },
                    });
                }
            });

            if (submittedForms === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Forms Selected',
                    text: 'Please enable at least one form toggle to submit.',
                    confirmButtonText: 'OK'
                });
            }
        });
    });
</script>

<script>
    document.getElementById('submitUploadField').addEventListener('click', function () {

        const field = document.getElementById('update_field').value;
        const fileInput = document.getElementById('upload_file');
        const button = this;

        if (!field) {
            showToast('error', 'Please select a field');
            return;
        }

        if (!fileInput.files.length) {
            showToast('error', 'Please upload a file');
            return;
        }

        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        formData.append('update_field', field);
        formData.append('import_file', fileInput.files[0]);

        button.disabled = true;
        button.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Uploading...';

        fetch("{{ route('employee.field.import') }}", {
            method: 'POST',
            body: formData
        })
        .then(async response => {
            const data = await response.json();

            button.disabled = false;
            button.innerHTML = '<i class="fa fa-paper-plane me-1"></i> Save';

            if (!response.ok) {
                if (data.errors) {
                    let msg = Object.values(data.errors).flat().join('<br>');
                    showToast('error', msg);
                } else {
                    showToast('error', data.message || 'Import failed');
                }
                return;
            }

            if (data.status === true) {
                showToast('success', data.message);
                fileInput.value = '';
            } else {
                showToast('error', data.message || 'Import failed');
            }
        })
        .catch(() => {
            button.disabled = false;
            button.innerHTML = '<i class="fa fa-paper-plane me-1"></i> Save';
            showToast('error', 'Something went wrong. Please try again.');
        });
    });

    /* SweetAlert Toast */
    function showToast(type, message) {
        Swal.fire({
            position: 'top-end',
            icon: type,
            title: message,
            toast: true,
            showConfirmButton: false,
            timer: 5000,
            timerProgressBar: true,
            didOpen: toast => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
    }
</script>

<script>
    $(document).ready(function () {
        function showToast(message, type = 'success') {
            Swal.fire({
                position: 'top-end',
                icon: type,
                title: message,
                toast: true,
                showConfirmButton: false,
                timer: 3000
            });
        }

        $('#device_employee_id').on('change', function () {
            let empId = $(this).val();
            $('.resetSingle').prop('disabled', !empId);
            if (!empId) {
                $('#single_device_restriction').prop('checked', false);
                return;
            }

            $.ajax({
                url: "{{ route('employee.device.restriction.get') }}",
                type: "GET",
                data: {
                    emp_id: empId
                },
                success: function (res) {
                    $('#single_device_restriction')
                        .prop('checked', res.emp_is_device_restriction == 1);

                },
                error: function () {
                    showToast('Failed to load data', 'error');
                }
            });
        });

        // Employee change
        $('.resetSingle').on('click', function () {
            let empId = $('#device_employee_id').val();
            let isRestricted = $('#single_device_restriction').is(':checked') ? 1 : 0;

            if (!empId) {
                showToast('Please select employee', 'warning');
                return;
            }

            $.ajax({
                url: "{{ route('employee.device.restriction.save') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    emp_id: empId,
                    single_device_restriction: isRestricted
                },
                success: function (res) {
                    if (res.status) {
                        showToast('Reset successful', 'success');
                    } else {
                        showToast('Reset failed', 'warning');
                    }
                },
                error: function () {
                    showToast('Reset failed.', 'error');
                }
            });
        });

        // ✅ Reset Single → DB se reload
        $('.resetAll').on('click', function () {
            let isRestricted = $('#single_device_restriction').is(':checked') ? 1 : 0;
                $.ajax({
                url: "{{ route('employee.device.restriction.saveAll') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    single_device_restriction: isRestricted
                },
                success: function (res) {
                    if (res.status) {
                        showToast('All fields reset', 'success');
                    } else {
                        showToast('All reset failed', 'error');
                    }
                }
            });
        });
    });
</script>

@endsection
